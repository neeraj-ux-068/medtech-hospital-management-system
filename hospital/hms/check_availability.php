<?php
require_once 'include/config.php';
header('Content-Type: application/json; charset=utf-8');
$email = filter_var(hms_post_string('email', 254), FILTER_VALIDATE_EMAIL);
if (!$email) { http_response_code(422); echo json_encode(['available'=>false,'message'=>'Enter a valid email address.']); exit; }
try {
    $result = hms_db_query($con, 'SELECT id FROM users WHERE email = ? LIMIT 1', [$email]);
    $available = $result && $result->num_rows === 0;
    echo json_encode(['available'=>$available,'message'=>$available ? 'Email available for registration.' : 'An account already exists for this email address.']);
} catch (Throwable $exception) {
    error_log('HMS registration availability check failed: ' . $exception->getMessage());
    http_response_code(503);
    echo json_encode(['available'=>false,'message'=>'Email availability cannot be checked right now.']);
}
