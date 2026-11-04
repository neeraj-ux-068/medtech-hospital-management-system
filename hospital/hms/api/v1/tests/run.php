<?php
/** Synthetic-session unit checks; no database connection or patient data. */

require_once __DIR__ . '/../src/auth.php';

$checks = 0;
$assert = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

foreach (['patient', 'doctor', 'admin'] as $role) {
    $principal = hms_api_session_principal(['role' => $role, 'id' => '42']);
    $assert($principal === ['role' => $role, 'id' => 42], "valid {$role} session is recognized");
    $assert(hms_api_role_allowed($principal, [$role]), "{$role} role authorization succeeds");
    $assert(hms_api_authorize_session(['role' => $role, 'id' => 42], ['patient', 'doctor', 'admin'])['status'] === 200, "{$role} session endpoint authorization succeeds");
}

$assert(hms_api_session_principal([]) === null, 'missing session is unauthenticated');
$assert(hms_api_session_principal(['role' => 'patient', 'id' => '0']) === null, 'zero account ID is rejected');
$assert(hms_api_session_principal(['role' => 'patient', 'id' => 'not-an-id']) === null, 'invalid account ID is rejected');
$assert(!hms_api_role_allowed(['role' => 'unknown', 'id' => 42], ['patient', 'doctor', 'admin']), 'unknown role is forbidden');
$assert(!hms_api_role_allowed(['role' => 'patient', 'id' => 42], ['admin']), 'role mismatch is forbidden');
$assert(hms_api_authorize_session([], ['patient', 'doctor', 'admin'])['status'] === 401, 'missing session maps to HTTP 401');
$assert(hms_api_authorize_session(['role' => 'unknown', 'id' => 42], ['patient', 'doctor', 'admin'])['status'] === 403, 'unsupported role maps to HTTP 403');

$session = ['_csrf_token' => str_repeat('a', 64)];
$assert(hms_api_csrf_valid($session, str_repeat('a', 64)), 'matching future CSRF token is accepted');
$assert(!hms_api_csrf_valid($session, str_repeat('b', 64)), 'mismatched CSRF token is rejected');
$assert(!hms_api_csrf_valid([], str_repeat('a', 64)), 'missing CSRF session token is rejected');
$assert(!hms_api_csrf_valid($session, ''), 'missing CSRF request token is rejected');

fwrite(STDOUT, "PASS: {$checks} synthetic-session checks\n");
