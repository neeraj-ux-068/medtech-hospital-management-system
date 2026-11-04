<?php
/** Minimal JSON transport helpers for /api/v1. */

function hms_api_request_id(): string
{
    return bin2hex(random_bytes(16));
}

function hms_api_headers(string $requestId): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Request-ID: ' . $requestId);
    header('X-Content-Type-Options: nosniff');
}

function hms_api_json(array $body, int $statusCode, string $requestId): void
{
    http_response_code($statusCode);
    hms_api_headers($requestId);

    try {
        echo json_encode($body, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (JsonException $exception) {
        http_response_code(500);
        echo '{"error":{"code":"internal_error","message":"The request could not be completed."},"meta":{"requestId":"'
            . preg_replace('/[^a-f0-9]/', '', $requestId) . '"}}';
    }
    exit;
}

function hms_api_success(array $data, string $requestId, int $statusCode = 200): void
{
    hms_api_json([
        'data' => $data,
        'meta' => ['requestId' => $requestId],
    ], $statusCode, $requestId);
}

function hms_api_error(
    string $code,
    string $message,
    int $statusCode,
    string $requestId
): void {
    hms_api_json([
        'error' => ['code' => $code, 'message' => $message],
        'meta' => ['requestId' => $requestId],
    ], $statusCode, $requestId);
}
