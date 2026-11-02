<?php
// Run from Windows Task Scheduler through the PHP CLI. It creates in-app reminders only.
require_once dirname(__DIR__) . '/include/config.php';
require_once dirname(__DIR__) . '/include/notifications.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only\n"); }
if (!hms_notifications_ready($con)) exit("Notifications migration is not installed.\n");
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$rows = hms_db_query($con, "SELECT id,userId,appointmentTime FROM appointment WHERE appointmentDate=? AND status='confirmed'", [$tomorrow]);
while ($row = $rows->fetch_assoc()) hms_notify($con, 'patient', (int)$row['userId'], 'Appointment reminder', 'You have a confirmed appointment tomorrow at '.(string)$row['appointmentTime'].'.', 'appointment', (int)$row['id'], 'appointment-details.php?id='.(int)$row['id'], 'appointment-reminder:'.(int)$row['id'].':'.$tomorrow);
echo "Appointment reminders processed for {$tomorrow}.\n";
