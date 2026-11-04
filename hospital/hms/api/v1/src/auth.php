<?php
/** API-only adapters for the existing HMS PHP session. */

function hms_api_session_principal(array $session): ?array
{
    $role = $session['role'] ?? null;
    $id = filter_var($session['id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if (!is_string($role) || $id === false || $id === null) {
        return null;
    }

    return ['role' => $role, 'id' => $id];
}

function hms_api_role_allowed(array $principal, array $allowedRoles): bool
{
    $knownRoles = ['patient', 'doctor', 'admin'];
    return in_array($principal['role'] ?? null, $knownRoles, true)
        && in_array($principal['role'], $allowedRoles, true);
}

/** Resolve API authentication without redirecting or reading account data. */
function hms_api_authorize_session(array $session, array $allowedRoles): array
{
    $principal = hms_api_session_principal($session);
    if ($principal === null) {
        return ['status' => 401, 'principal' => null];
    }
    if (!hms_api_role_allowed($principal, $allowedRoles)) {
        return ['status' => 403, 'principal' => null];
    }
    return ['status' => 200, 'principal' => $principal];
}

/**
 * Future state-changing routes must compare the supplied X-CSRF-Token header
 * with the token created by hms_login() in the existing PHP session.
 * This API foundation intentionally registers no write routes.
 */
function hms_api_csrf_valid(array $session, string $providedToken): bool
{
    $expectedToken = $session['_csrf_token'] ?? null;
    return is_string($expectedToken)
        && $expectedToken !== ''
        && $providedToken !== ''
        && hash_equals($expectedToken, $providedToken);
}
