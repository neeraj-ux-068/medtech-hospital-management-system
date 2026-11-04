<?php
/** Isolated API v1 front controller. Legacy page routes are not included here. */

require_once __DIR__ . '/src/http.php';
require_once __DIR__ . '/src/auth.php';

$requestId = hms_api_request_id();
$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) ? $requestPath : '/';
$apiBasePath = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/'))), '/');

if (!empty($_SERVER['PATH_INFO'])) {
    $route = '/' . ltrim((string)$_SERVER['PATH_INFO'], '/');
} elseif ($apiBasePath !== '' && str_starts_with($requestPath, $apiBasePath . '/')) {
    $route = substr($requestPath, strlen($apiBasePath));
} elseif (str_ends_with($requestPath, '/index.php')) {
    $route = '/';
} else {
    $route = $requestPath;
}
$route = rtrim($route, '/') ?: '/';

if ($route !== '/auth/session') {
    hms_api_error('not_found', 'The requested API route was not found.', 404, $requestId);
}
if ($method !== 'GET') {
    header('Allow: GET');
    hms_api_error('method_not_allowed', 'This API route only accepts GET.', 405, $requestId);
}

// Reuse the established cookie/session configuration; do not use the legacy
// hms_require_role(), which redirects unauthenticated page requests to HTML.
require_once __DIR__ . '/../../include/security.php';
hms_start_session();
$authorization = hms_api_authorize_session($_SESSION, ['patient', 'doctor', 'admin']);

if ($authorization['status'] === 401) {
    hms_api_error('unauthenticated', 'Authentication is required.', 401, $requestId);
}
if ($authorization['status'] === 403) {
    hms_api_error('forbidden', 'This account is not permitted to use the API.', 403, $requestId);
}

hms_api_success([
    'authenticated' => true,
    'principal' => $authorization['principal'],
], $requestId);
