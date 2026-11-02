<?php
/** Shared, read-only reporting helpers. */
require_once __DIR__ . '/security.php';

function hms_analytics_table_exists(mysqli $connection, string $table): bool
{
    static $allowed = ['medical_records', 'prescription_headers', 'laboratory_requests', 'billing_invoices', 'billing_payments'];
    if (!in_array($table, $allowed, true)) {
        return false;
    }
    $result = hms_db_query($connection, 'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1', [$table]);
    return $result && $result->num_rows === 1;
}

function hms_analytics_date(string $value): ?string
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : null;
}

function hms_analytics_range(array $input): array
{
    $defaultTo = date('Y-m-d');
    $defaultFrom = date('Y-m-d', strtotime('-29 days'));
    $from = hms_analytics_date((string)($input['from'] ?? '')) ?: $defaultFrom;
    $to = hms_analytics_date((string)($input['to'] ?? '')) ?: $defaultTo;
    if ($from > $to || $from < '2000-01-01' || $to > date('Y-m-d', strtotime('+1 day'))) {
        return [$defaultFrom, $defaultTo, 'Choose a valid date range. Showing the last 30 days instead.'];
    }
    return [$from, $to, ''];
}

function hms_csv_value($value): string
{
    $value = (string)($value ?? '');
    return preg_match('/^[=+\-@]/', ltrim($value)) ? "'" . $value : $value;
}
