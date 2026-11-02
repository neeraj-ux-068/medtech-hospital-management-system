<?php
require_once __DIR__ . '/security.php';

function hms_notifications_ready(mysqli $connection): bool
{
    $result = hms_db_query($connection, "SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='notifications' LIMIT 1");
    return $result && $result->num_rows === 1;
}

function hms_notify(mysqli $connection, string $role, int $recipientId, string $type, string $message, ?string $relatedType = null, ?int $relatedId = null, ?string $link = null, ?string $eventKey = null): void
{
    if (!hms_notifications_ready($connection) || !in_array($role, ['patient', 'doctor', 'admin'], true) || $recipientId < 1) return;
    $message = trim($message);
    if ($message === '' || strlen($message) > 500 || strlen($type) > 60 || ($link !== null && (strlen($link) > 255 || str_contains($link, '://') || str_starts_with($link, '/')))) return;
    hms_db_query($connection, 'INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key) VALUES(?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE id=id', [$role, $recipientId, $type, $message, $relatedType, $relatedId, $link, $eventKey]);
}

function hms_notify_admins(mysqli $connection, string $type, string $message, ?string $relatedType = null, ?int $relatedId = null, ?string $link = null, ?string $eventKey = null): void
{
    if (!hms_notifications_ready($connection)) return;
    $admins = hms_db_query($connection, 'SELECT id FROM admin');
    while ($admin = $admins->fetch_assoc()) hms_notify($connection, 'admin', (int)$admin['id'], $type, $message, $relatedType, $relatedId, $link, $eventKey === null ? null : $eventKey . ':a' . (int)$admin['id']);
}

function hms_unread_notification_count(mysqli $connection, string $role, int $recipientId): int
{
    if (!hms_notifications_ready($connection)) return 0;
    $row = hms_db_query($connection, 'SELECT COUNT(*) total FROM notifications WHERE recipient_role=? AND recipient_id=? AND read_at IS NULL', [$role, $recipientId])->fetch_assoc();
    return (int)($row['total'] ?? 0);
}

function hms_notification_link(array $notification, string $role): string
{
    $base = hms_base_url() . '/hms/';
    $id = (int)($notification['related_id'] ?? 0);
    $type = (string)($notification['related_type'] ?? '');
    if ($type === 'appointment' && $id > 0) return $base . 'appointment-details.php?id=' . $id;
    if ($type === 'prescription' && $id > 0) return $base . 'prescription-details.php?id=' . $id;
    if ($type === 'laboratory_request') return $base . ($role === 'admin' ? 'admin/laboratory.php' : ($role === 'doctor' ? 'doctor/laboratory.php' : 'laboratory.php'));
    if ($type === 'invoice' && $id > 0) return $base . ($role === 'admin' ? 'admin/invoice.php?id=' : 'invoice.php?id=') . $id;
    return '';
}
