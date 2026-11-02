<?php
require_once __DIR__ . '/include/config.php';
hms_start_session();
hms_require_role('patient');
require_once __DIR__ . '/include/appointments.php';
header('Content-Type: application/json; charset=utf-8');
$doctorId = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT) ?: 0;
$date = is_string($_GET['date'] ?? null) ? $_GET['date'] : '';
$exclude = filter_input(INPUT_GET, 'exclude_id', FILTER_VALIDATE_INT) ?: 0;
if ($exclude) {
    $owned = hms_db_query($con, 'SELECT id FROM appointment WHERE id = ? AND userId = ?', [$exclude, (int)$_SESSION['id']]);
    if (!$owned || !$owned->num_rows) { http_response_code(403); echo json_encode(['slots'=>[]]); exit; }
}
try { echo json_encode(['slots'=>hms_available_slots($con, $doctorId, $date, $exclude)]); }
catch (Throwable $e) { error_log('HMS slot lookup failed: '.$e->getMessage()); http_response_code(500); echo json_encode(['slots'=>[]]); }
