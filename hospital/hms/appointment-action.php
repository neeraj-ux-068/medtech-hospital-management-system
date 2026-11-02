<?php
require_once __DIR__ . '/include/config.php';
hms_start_session();
require_once __DIR__ . '/include/appointments.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
$role = (string)($_SESSION['role'] ?? '');
if (!in_array($role, ['patient','doctor','admin'], true)) { http_response_code(403); exit('Sign in required.'); }
hms_require_role($role);
hms_verify_csrf();
$actor = (int)$_SESSION['id'];
$action = hms_post_string('action', 40);
try {
    if ($action === 'status') $result = hms_set_appointment_status($con, (int)($_POST['appointment_id'] ?? 0), $role, $actor, hms_post_string('status', 20), hms_post_string('notes', 1000) ?: null);
    elseif ($action === 'reschedule') $result = $role === 'patient' ? hms_request_appointment_reschedule($con, (int)($_POST['appointment_id'] ?? 0), $actor, hms_post_string('date', 10), hms_post_string('time', 20), hms_post_string('notes', 1000)) : ['ok'=>false,'message'=>'Only patients can request a reschedule.'];
    elseif ($action === 'review') $result = hms_review_appointment_reschedule($con, (int)($_POST['request_id'] ?? 0), $role, $actor, hms_post_string('decision', 20) === 'approve', hms_post_string('notes', 1000));
    else $result = ['ok'=>false,'message'=>'Unknown appointment action.'];
} catch (Throwable $e) { error_log('HMS appointment action failed: '.$e->getMessage()); $result=['ok'=>false,'message'=>'The request could not be completed.']; }
$_SESSION['msg'] = $result['message'];
$id = (int)($_POST['appointment_id'] ?? 0);
$target = $role === 'admin' ? 'admin/appointment-history.php' : ($role === 'doctor' ? 'doctor/appointment-history.php' : 'appointment-history.php');
// Appointment details is a shared role-aware page at the HMS root. Keeping this
// route absolute relative to this handler avoids redirecting admins or doctors to
// nonexistent role-specific detail pages.
if ($id > 0 && isset($_POST['return_details'])) $target = 'appointment-details.php?id='.$id;
header('Location: '.$target); exit;
