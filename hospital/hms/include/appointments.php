<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/notifications.php';

function hms_appointment_statuses(): array
{
    return ['pending', 'confirmed', 'completed', 'cancelled', 'rejected'];
}

function hms_appointment_status_label(string $status): string
{
    $labels = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'rejected' => 'Rejected',
    ];
    return $labels[$status] ?? 'Unknown';
}

function hms_appointment_status_badge(string $status): string
{
    $class = in_array($status, hms_appointment_statuses(), true) ? $status : 'unknown';
    return '<span class="hms-status hms-status-' . hms_h($class) . '">' . hms_h(hms_appointment_status_label($status)) . '</span>';
}

function hms_appointment_event(mysqli $connection, int $appointmentId, string $actorRole, ?int $actorId, string $eventType, ?string $fromStatus = null, ?string $toStatus = null, ?string $oldDate = null, ?string $oldTime = null, ?string $newDate = null, ?string $newTime = null, ?string $notes = null): void
{
    $oldTime = $oldTime !== null && ($parsed = hms_appointment_time($oldTime)) ? $parsed->format('H:i:s') : null;
    $newTime = $newTime !== null && ($parsed = hms_appointment_time($newTime)) ? $parsed->format('H:i:s') : null;
    hms_db_query($connection, 'INSERT INTO appointment_events (appointmentId, actorRole, actorId, eventType, fromStatus, toStatus, oldDate, oldTime, newDate, newTime, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$appointmentId, $actorRole, $actorId, $eventType, $fromStatus, $toStatus, $oldDate, $oldTime, $newDate, $newTime, $notes]);
}

function hms_appointment_date(string $date): ?DateTimeImmutable
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date ? $parsed : null;
}

function hms_appointment_time(string $time): ?DateTimeImmutable
{
    $time = strtoupper(trim($time));
    foreach (['!g:i A', '!h:i A', '!H:i'] as $format) {
        $parsed = DateTimeImmutable::createFromFormat($format, $time);
        if ($parsed && $parsed->format(substr($format, 1)) === $time) {
            return $parsed;
        }
    }
    return null;
}

function hms_slot_value(DateTimeImmutable $time): string
{
    return $time->format('g:i A');
}

/** Build available appointment slots from doctor rules and current reservations. */
function hms_available_slots(mysqli $connection, int $doctorId, string $date, int $excludeAppointmentId = 0): array
{
    $day = hms_appointment_date($date);
    if (!$day || $day < new DateTimeImmutable('today') || $doctorId < 1) {
        return [];
    }

    $blocked = hms_db_query($connection, 'SELECT id FROM doctor_unavailable_dates WHERE doctorId = ? AND unavailableDate = ? LIMIT 1', [$doctorId, $date]);
    if (!$blocked || $blocked->num_rows > 0) {
        return [];
    }

    $rules = hms_db_query($connection, 'SELECT startTime, endTime, slotMinutes, maxPerSlot FROM doctor_availability WHERE doctorId = ? AND weekday = ? AND isActive = 1 ORDER BY startTime', [$doctorId, (int)$day->format('N')]);
    if (!$rules) {
        return [];
    }

    $available = [];
    $now = new DateTimeImmutable();
    while ($rule = $rules->fetch_assoc()) {
        $minutes = max(1, (int)$rule['slotMinutes']);
        $capacity = max(1, (int)$rule['maxPerSlot']);
        $cursor = DateTimeImmutable::createFromFormat('!H:i:s', (string)$rule['startTime']);
        $end = DateTimeImmutable::createFromFormat('!H:i:s', (string)$rule['endTime']);
        if (!$cursor || !$end || $end <= $cursor) {
            continue;
        }
        while ($cursor->modify('+' . $minutes . ' minutes') <= $end) {
            $slot = hms_slot_value($cursor);
            $slotDateTime = new DateTimeImmutable($date . ' ' . $cursor->format('H:i:s'));
            if ($slotDateTime >= $now) {
                $reservation = hms_db_query($connection, "SELECT ((SELECT COUNT(*) FROM appointment WHERE doctorId = ? AND appointmentDate = ? AND UPPER(TRIM(appointmentTime)) = ? AND status IN ('pending','confirmed') AND id <> ?) + (SELECT COUNT(*) FROM appointment_reschedule_requests r JOIN appointment a ON a.id = r.appointmentId WHERE a.doctorId = ? AND a.status = 'confirmed' AND r.status = 'pending' AND r.requestedDate = ? AND UPPER(TIME_FORMAT(r.requestedTime, '%l:%i %p')) = ? AND a.id <> ?)) AS total", [$doctorId, $date, strtoupper($slot), $excludeAppointmentId, $doctorId, $date, strtoupper($slot), $excludeAppointmentId]);
                $row = $reservation ? $reservation->fetch_assoc() : [];
                if ((int)($row['total'] ?? 0) < $capacity) {
                    $available[$slot] = ['value' => $slot, 'label' => $slot];
                }
            }
            $cursor = $cursor->modify('+' . $minutes . ' minutes');
        }
    }
    return array_values($available);
}

/** Serialize same-doctor/same-slot bookings even when two requests arrive together. */
function hms_create_appointment(mysqli $connection, int $userId, int $doctorId, string $date, string $time, string $reason): array
{
    $parsedDate = hms_appointment_date($date);
    $parsedTime = hms_appointment_time($time);
    $reason = trim($reason);
    if ($userId < 1 || $doctorId < 1 || !$parsedDate || !$parsedTime || $reason === '' || strlen($reason) > 4000) {
        return ['ok' => false, 'message' => 'Enter a valid doctor, future date, available time, and visit reason.'];
    }

    $timeValue = hms_slot_value($parsedTime);
    $lockName = 'hms:appt:' . $doctorId . ':' . $date . ':' . str_replace([':', ' '], '', $timeValue);
    $lockResult = hms_db_query($connection, 'SELECT GET_LOCK(?, 5) AS acquired', [$lockName]);
    $lock = $lockResult ? $lockResult->fetch_assoc() : [];
    if ((int)($lock['acquired'] ?? 0) !== 1) {
        return ['ok' => false, 'message' => 'That time is being booked right now. Please choose another slot.'];
    }

    $transaction = false;
    try {
        $doctorResult = hms_db_query($connection, 'SELECT id, specilization, docFees FROM doctors WHERE id = ? LIMIT 1', [$doctorId]);
        $doctor = $doctorResult ? $doctorResult->fetch_assoc() : null;
        if (!$doctor || !in_array($timeValue, array_column(hms_available_slots($connection, $doctorId, $date), 'value'), true)) {
            return ['ok' => false, 'message' => 'That doctor has no available appointment at the selected time. Please choose another slot.'];
        }

        $connection->begin_transaction();
        $transaction = true;
        hms_db_query($connection, "INSERT INTO appointment (doctorSpecialization, doctorId, userId, consultancyFees, appointmentDate, appointmentTime, userStatus, doctorStatus, status, visitReason) VALUES (?, ?, ?, ?, ?, ?, 1, 1, 'pending', ?)", [(string)$doctor['specilization'], $doctorId, $userId, (int)$doctor['docFees'], $date, $timeValue, $reason]);
        $appointmentId = (int)$connection->insert_id;
        hms_appointment_event($connection, $appointmentId, 'patient', $userId, 'booked', null, 'pending', null, null, $date, $timeValue, $reason);
        hms_notify($connection, 'patient', $userId, 'Appointment booked', 'Your appointment request is awaiting confirmation.', 'appointment', $appointmentId, 'appointment-details.php?id='.$appointmentId, 'appointment-booked:'.$appointmentId);
        hms_notify_admins($connection, 'Appointment booked', 'A new appointment request needs review.', 'appointment', $appointmentId, 'appointment-details.php?id='.$appointmentId, 'appointment-booked:'.$appointmentId);
        $connection->commit();
        $transaction = false;
        return ['ok' => true, 'appointment_id' => $appointmentId, 'message' => 'Appointment request sent. Your doctor will confirm the visit.'];
    } catch (Throwable $exception) {
        if ($transaction) {
            $connection->rollback();
        }
        error_log('HMS appointment booking failed: ' . $exception->getMessage());
        return ['ok' => false, 'message' => 'We could not save your appointment. Please try again.'];
    } finally {
        hms_db_query($connection, 'SELECT RELEASE_LOCK(?) AS released', [$lockName]);
    }
}

function hms_allowed_appointment_transition(string $role, string $from, string $to): bool
{
    if (!in_array($to, hms_appointment_statuses(), true)) {
        return false;
    }
    $transitions = [
        'doctor' => ['pending' => ['confirmed', 'rejected'], 'confirmed' => ['completed', 'cancelled']],
        'patient' => ['pending' => ['cancelled'], 'confirmed' => ['cancelled']],
        'admin' => ['pending' => ['confirmed', 'cancelled', 'rejected'], 'confirmed' => ['completed', 'cancelled']],
    ];
    return in_array($to, $transitions[$role][$from] ?? [], true);
}

function hms_set_appointment_status(mysqli $connection, int $appointmentId, string $role, int $actorId, string $status, ?string $notes = null): array
{
    if (!in_array($status, hms_appointment_statuses(), true)) {
        return ['ok' => false, 'message' => 'Choose a valid appointment status.'];
    }
    $connection->begin_transaction();
    try {
        $sql = 'SELECT id, doctorId, userId, appointmentDate, appointmentTime, status FROM appointment WHERE id = ?';
        $params = [$appointmentId];
        if ($role === 'patient') {
            $sql .= ' AND userId = ?';
            $params[] = $actorId;
        } elseif ($role === 'doctor') {
            $sql .= ' AND doctorId = ?';
            $params[] = $actorId;
        }
        $sql .= ' LIMIT 1 FOR UPDATE';
        $result = hms_db_query($connection, $sql, $params);
        $appointment = $result ? $result->fetch_assoc() : null;
        if (!$appointment || !hms_allowed_appointment_transition($role, (string)$appointment['status'], $status)) {
            $connection->rollback();
            return ['ok' => false, 'message' => 'This appointment cannot be changed by your account.'];
        }
        $scheduledDate = hms_appointment_date((string)$appointment['appointmentDate']);
        $scheduledTime = hms_appointment_time((string)$appointment['appointmentTime']);
        $scheduledAt = $scheduledDate && $scheduledTime ? new DateTimeImmutable($scheduledDate->format('Y-m-d') . ' ' . $scheduledTime->format('H:i:s')) : null;
        if (!$scheduledAt || (in_array($status, ['cancelled', 'rejected'], true) && $scheduledAt <= new DateTimeImmutable())) {
            $connection->rollback();
            return ['ok' => false, 'message' => 'The appointment date or time has passed and cannot be changed this way.'];
        }
        if ($status === 'completed' && $scheduledAt > new DateTimeImmutable()) {
            $connection->rollback();
            return ['ok' => false, 'message' => 'An upcoming appointment cannot be marked completed.'];
        }
        $userStatus = 1;
        $doctorStatus = 1;
        if ($status === 'cancelled') {
            if ($role === 'patient') {
                $userStatus = 0;
            } elseif ($role === 'doctor') {
                $doctorStatus = 0;
            } else {
                $userStatus = 0;
                $doctorStatus = 0;
            }
        } elseif ($status === 'rejected') {
            $doctorStatus = 0;
        }
        hms_db_query($connection, 'UPDATE appointment SET status = ?, userStatus = ?, doctorStatus = ?, doctorNotes = CASE WHEN ? IS NULL THEN doctorNotes ELSE ? END WHERE id = ?', [$status, $userStatus, $doctorStatus, $notes, $notes, $appointmentId]);
        hms_appointment_event($connection, $appointmentId, $role, $actorId, 'status_changed', (string)$appointment['status'], $status, (string)$appointment['appointmentDate'], (string)$appointment['appointmentTime'], (string)$appointment['appointmentDate'], (string)$appointment['appointmentTime'], $notes);
        hms_notify($connection, 'patient', (int)$appointment['userId'], 'Appointment '.hms_appointment_status_label($status), 'Your appointment status is now '.strtolower(hms_appointment_status_label($status)).'.', 'appointment', $appointmentId, 'appointment-details.php?id='.$appointmentId, 'appointment-status:'.$appointmentId.':'.$status);
        if ($role === 'patient') hms_notify($connection, 'doctor', (int)$appointment['doctorId'], 'Appointment cancelled', 'A patient cancelled an appointment.', 'appointment', $appointmentId, 'appointment-details.php?id='.$appointmentId, 'appointment-cancelled:'.$appointmentId);
        if (in_array($status, ['cancelled', 'rejected'], true)) {
            hms_db_query($connection, "UPDATE appointment_reschedule_requests SET status = 'cancelled', reviewedByRole = ?, reviewedById = ?, reviewNote = 'Appointment status changed before the request was reviewed.', reviewedAt = NOW() WHERE appointmentId = ? AND status = 'pending'", [$role, $actorId, $appointmentId]);
        }
        $connection->commit();
        return ['ok' => true, 'message' => 'Appointment status updated to ' . hms_appointment_status_label($status) . '.'];
    } catch (Throwable $exception) {
        $connection->rollback();
        error_log('HMS appointment status update failed: ' . $exception->getMessage());
        return ['ok' => false, 'message' => 'We could not update the appointment. Please try again.'];
    }
}

function hms_request_appointment_reschedule(mysqli $connection, int $appointmentId, int $patientId, string $requestedDate, string $requestedTime, string $note = ''): array
{
    $date = hms_appointment_date($requestedDate);
    $time = hms_appointment_time($requestedTime);
    if (!$date || !$time || (trim($note) !== '' && strlen($note) > 4000)) {
        return ['ok' => false, 'message' => 'Choose a valid future date, available time, and a short note.'];
    }
    $slot = hms_slot_value($time);
    $owner = hms_db_query($connection, 'SELECT doctorId FROM appointment WHERE id = ? AND userId = ? LIMIT 1', [$appointmentId, $patientId]);
    $ownerRow = $owner ? $owner->fetch_assoc() : null;
    if (!$ownerRow) return ['ok' => false, 'message' => 'Appointment not found.'];
    $lockName = 'hms:appt:' . (int)$ownerRow['doctorId'] . ':' . $requestedDate . ':' . str_replace([':', ' '], '', $slot);
    $lockResult = hms_db_query($connection, 'SELECT GET_LOCK(?, 5) AS acquired', [$lockName]);
    $lock = $lockResult ? $lockResult->fetch_assoc() : [];
    if ((int)($lock['acquired'] ?? 0) !== 1) {
        return ['ok' => false, 'message' => 'That time is currently being requested. Please try another slot.'];
    }
    $transaction = false;
    try {
        $connection->begin_transaction();
        $transaction = true;
        $result = hms_db_query($connection, "SELECT id, doctorId, userId, appointmentDate, appointmentTime, status FROM appointment WHERE id = ? AND userId = ? LIMIT 1 FOR UPDATE", [$appointmentId, $patientId]);
        $appointment = $result ? $result->fetch_assoc() : null;
        $oldDate = $appointment ? hms_appointment_date((string)$appointment['appointmentDate']) : null;
        $oldTime = $appointment ? hms_appointment_time((string)$appointment['appointmentTime']) : null;
        $oldAt = $oldDate && $oldTime ? new DateTimeImmutable($oldDate->format('Y-m-d') . ' ' . $oldTime->format('H:i:s')) : null;
        if (!$appointment || $appointment['status'] !== 'confirmed' || !$oldAt || $oldAt <= new DateTimeImmutable()) {
            $connection->rollback();
            return ['ok' => false, 'message' => 'Only upcoming confirmed appointments can be rescheduled.'];
        }
        $pending = hms_db_query($connection, "SELECT id FROM appointment_reschedule_requests WHERE appointmentId = ? AND status = 'pending' LIMIT 1", [$appointmentId]);
        if ($pending && $pending->num_rows > 0) {
            $connection->rollback();
            return ['ok' => false, 'message' => 'A reschedule request is already awaiting review.'];
        }
        $available = array_column(hms_available_slots($connection, (int)$appointment['doctorId'], $requestedDate, $appointmentId), 'value');
        if (!in_array($slot, $available, true)) {
            $connection->rollback();
            return ['ok' => false, 'message' => 'That time is no longer available. Please choose another slot.'];
        }
        hms_db_query($connection, "INSERT INTO appointment_reschedule_requests (appointmentId, requestedByUserId, requestedDate, requestedTime, status, requestNote) VALUES (?, ?, ?, ?, 'pending', ?)", [$appointmentId, $patientId, $requestedDate, $time->format('H:i:s'), trim($note) !== '' ? trim($note) : null]);
        hms_appointment_event($connection, $appointmentId, 'patient', $patientId, 'reschedule_requested', 'confirmed', 'confirmed', (string)$appointment['appointmentDate'], (string)$appointment['appointmentTime'], $requestedDate, $slot, $note);
        hms_notify($connection, 'doctor', (int)$appointment['doctorId'], 'Reschedule request', 'A patient requested a new appointment time.', 'appointment', $appointmentId, 'appointment-details.php?id='.$appointmentId, 'appointment-reschedule-request:'.$appointmentId);
        hms_notify_admins($connection, 'Reschedule request', 'An appointment reschedule request is awaiting review.', 'appointment', $appointmentId, 'appointment-details.php?id='.$appointmentId, 'appointment-reschedule-request:'.$appointmentId);
        $connection->commit();
        $transaction = false;
        return ['ok' => true, 'message' => 'Your reschedule request was sent for review.'];
    } catch (Throwable $exception) {
        if ($transaction) $connection->rollback();
        error_log('HMS appointment reschedule request failed: ' . $exception->getMessage());
        return ['ok' => false, 'message' => 'We could not send the reschedule request. Please try again.'];
    } finally {
        hms_db_query($connection, 'SELECT RELEASE_LOCK(?) AS released', [$lockName]);
    }
}

function hms_review_appointment_reschedule(mysqli $connection, int $requestId, string $role, int $actorId, bool $approve, string $reviewNote = ''): array
{
    if (!in_array($role, ['doctor', 'admin'], true) || $requestId < 1) {
        return ['ok' => false, 'message' => 'You cannot review this reschedule request.'];
    }
    $connection->begin_transaction();
    $transaction = true;
    $lockName = null;
    try {
        $sql = 'SELECT r.id AS requestId, r.requestedDate, r.requestedTime, r.status AS requestStatus, a.id, a.doctorId, a.userId, a.appointmentDate, a.appointmentTime, a.status FROM appointment_reschedule_requests r JOIN appointment a ON a.id = r.appointmentId WHERE r.id = ?';
        $params = [$requestId];
        if ($role === 'doctor') {
            $sql .= ' AND a.doctorId = ?';
            $params[] = $actorId;
        }
        $sql .= ' LIMIT 1 FOR UPDATE';
        $result = hms_db_query($connection, $sql, $params);
        $request = $result ? $result->fetch_assoc() : null;
        if (!$request || $request['requestStatus'] !== 'pending' || $request['status'] !== 'confirmed') {
            $connection->rollback();
            return ['ok' => false, 'message' => 'This reschedule request is no longer awaiting review.'];
        }
        if ($approve) {
            $newTime = DateTimeImmutable::createFromFormat('!H:i:s', (string)$request['requestedTime']);
            $slot = $newTime ? hms_slot_value($newTime) : '';
            $lockName = 'hms:appt:' . (int)$request['doctorId'] . ':' . $request['requestedDate'] . ':' . str_replace([':', ' '], '', $slot);
            $lockResult = hms_db_query($connection, 'SELECT GET_LOCK(?, 5) AS acquired', [$lockName]);
            $lock = $lockResult ? $lockResult->fetch_assoc() : [];
            if ((int)($lock['acquired'] ?? 0) !== 1) {
                $connection->rollback();
                return ['ok' => false, 'message' => 'The new time is currently being booked. Retry the request.'];
            }
            if (!$newTime || !in_array($slot, array_column(hms_available_slots($connection, (int)$request['doctorId'], (string)$request['requestedDate'], (int)$request['id']), 'value'), true)) {
                hms_db_query($connection, "UPDATE appointment_reschedule_requests SET status = 'rejected', reviewedByRole = ?, reviewedById = ?, reviewNote = 'Requested slot was no longer available.', reviewedAt = NOW() WHERE id = ?", [$role, $actorId, $requestId]);
                hms_appointment_event($connection, (int)$request['id'], $role, $actorId, 'reschedule_rejected', 'confirmed', 'confirmed', (string)$request['appointmentDate'], (string)$request['appointmentTime'], (string)$request['requestedDate'], $slot, 'Requested slot was no longer available.');
                $connection->commit();
                $transaction = false;
                return ['ok' => false, 'message' => 'The requested time is no longer available; the request was declined.'];
            }
            $oldDate = (string)$request['appointmentDate'];
            $oldTime = (string)$request['appointmentTime'];
            $newDate = (string)$request['requestedDate'];
            hms_db_query($connection, 'UPDATE appointment SET appointmentDate = ?, appointmentTime = ?, status = \'confirmed\', userStatus = 1, doctorStatus = 1 WHERE id = ?', [$newDate, $slot, (int)$request['id']]);
            hms_db_query($connection, "UPDATE appointment_reschedule_requests SET status = 'approved', reviewedByRole = ?, reviewedById = ?, reviewNote = ?, reviewedAt = NOW() WHERE id = ?", [$role, $actorId, trim($reviewNote) !== '' ? trim($reviewNote) : null, $requestId]);
            hms_appointment_event($connection, (int)$request['id'], $role, $actorId, 'rescheduled', 'confirmed', 'confirmed', $oldDate, $oldTime, $newDate, $slot, $reviewNote);
            hms_notify($connection, 'patient', (int)$request['userId'], 'Appointment rescheduled', 'Your appointment reschedule request was approved.', 'appointment', (int)$request['id'], 'appointment-details.php?id='.(int)$request['id'], 'appointment-rescheduled:'.(int)$request['id'].':'.$newDate.':'.$slot);
        } else {
            hms_db_query($connection, "UPDATE appointment_reschedule_requests SET status = 'rejected', reviewedByRole = ?, reviewedById = ?, reviewNote = ?, reviewedAt = NOW() WHERE id = ?", [$role, $actorId, trim($reviewNote) !== '' ? trim($reviewNote) : null, $requestId]);
            hms_appointment_event($connection, (int)$request['id'], $role, $actorId, 'reschedule_rejected', 'confirmed', 'confirmed', (string)$request['appointmentDate'], (string)$request['appointmentTime'], (string)$request['requestedDate'], (string)$request['requestedTime'], $reviewNote);
            hms_notify($connection, 'patient', (int)$request['userId'], 'Reschedule request declined', 'Your appointment time remains unchanged.', 'appointment', (int)$request['id'], 'appointment-details.php?id='.(int)$request['id'], 'appointment-reschedule-declined:'.(int)$request['id']);
        }
        $connection->commit();
        $transaction = false;
        return ['ok' => true, 'message' => $approve ? 'Reschedule request approved.' : 'Reschedule request declined.'];
    } catch (Throwable $exception) {
        if ($transaction) $connection->rollback();
        error_log('HMS appointment reschedule review failed: ' . $exception->getMessage());
        return ['ok' => false, 'message' => 'We could not review the reschedule request. Please try again.'];
    } finally {
        if ($lockName !== null) hms_db_query($connection, 'SELECT RELEASE_LOCK(?) AS released', [$lockName]);
    }
}
