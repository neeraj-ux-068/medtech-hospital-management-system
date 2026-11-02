<?php
include('include/config.php');
hms_start_session();
$role = $_SESSION['role'] ?? null;
if ($role === 'patient' && (int)($_SESSION['id'] ?? 0) > 0) {
    hms_db_query($con, 'UPDATE userlog SET logout = ? WHERE uid = ? AND logout IS NULL ORDER BY id DESC LIMIT 1', [date('d-m-Y h:i:s A'), (int)$_SESSION['id']]);
}
hms_logout('patient');
