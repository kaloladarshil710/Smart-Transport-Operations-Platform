<?php
/**
 * Notification helpers for the administration module.
 */
declare(strict_types=1);

function getNotificationsForUser(int $userId, int $limit = 50): array
{
    $stmt = getDb()->prepare('SELECT id, title, message, priority, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT ?');
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function markNotificationRead(int $id): void
{
    getDb()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?')->execute([$id]);
}

function deleteNotification(int $id): void
{
    getDb()->prepare('DELETE FROM notifications WHERE id = ?')->execute([$id]);
}
