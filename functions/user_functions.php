<?php
/**
 * User management helpers for the admin module.
 */
declare(strict_types=1);

function generateUuid(): string
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

function getUsersForAdmin(int $limit = 50): array
{
    $stmt = getDb()->prepare('SELECT id, uuid, full_name, email, role, phone, status, last_login, created_at FROM users WHERE deleted_at IS NULL ORDER BY id DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUserById(int $id): ?array
{
    $stmt = getDb()->prepare('SELECT id, uuid, full_name, email, role, phone, status, address, last_login, created_at FROM users WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function saveUser(array $input, ?int $id = null): array
{
    $fullName = trim((string)($input['full_name'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $role = trim((string)($input['role'] ?? 'viewer'));
    $status = trim((string)($input['status'] ?? 'Active'));
    $phone = trim((string)($input['phone'] ?? ''));
    $address = trim((string)($input['address'] ?? ''));
    $password = trim((string)($input['password'] ?? ''));

    if ($fullName === '' || $email === '' || $role === '') {
        return ['success' => false, 'message' => 'Name, email and role are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please provide a valid email address.'];
    }

    if ($id === null) {
        if ($password === '') {
            return ['success' => false, 'message' => 'Password is required for new users.'];
        }
        $stmt = getDb()->prepare('SELECT id FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'A user with that email already exists.'];
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $uuid = generateUuid();
        $stmt = getDb()->prepare('INSERT INTO users (uuid, full_name, email, password_hash, role, phone, address, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$uuid, $fullName, $email, $hash, $role, $phone, $address, $status, currentUser()['id'] ?? null]);
        $userId = (int)getDb()->lastInsertId();
        logActivity('User added', $fullName . ' was created');
        logAudit('users', 'create', null, ['id' => $userId, 'full_name' => $fullName, 'email' => $email], $uuid);
        return ['success' => true, 'message' => 'User created successfully.', 'user_id' => $userId];
    }

    $before = getUserById($id);
    $stmt = getDb()->prepare('UPDATE users SET full_name = ?, email = ?, role = ?, phone = ?, address = ?, status = ? WHERE id = ?');
    $stmt->execute([$fullName, $email, $role, $phone, $address, $status, $id]);
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        getDb()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $id]);
    }
    logActivity('User updated', $fullName . ' was updated');
    logAudit('users', 'update', $before, getUserById($id), $before['uuid'] ?? null);
    return ['success' => true, 'message' => 'User updated successfully.'];
}

function deleteUser(int $id): array
{
    $user = getUserById($id);
    if (!$user) {
        return ['success' => false, 'message' => 'User not found.'];
    }
    getDb()->prepare('UPDATE users SET deleted_at = NOW() WHERE id = ?')->execute([$id]);
    logActivity('User deleted', $user['full_name'] . ' was removed');
    logAudit('users', 'delete', $user, null, $user['uuid'] ?? null);
    return ['success' => true, 'message' => 'User deleted successfully.'];
}

function toggleUserStatus(int $id, string $status): array
{
    $user = getUserById($id);
    if (!$user) {
        return ['success' => false, 'message' => 'User not found.'];
    }
    getDb()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
    logActivity('User status changed', $user['full_name'] . ' marked as ' . $status);
    logAudit('users', 'status', $user, ['status' => $status], $user['uuid'] ?? null);
    return ['success' => true, 'message' => 'User status updated.'];
}

function resetUserPassword(int $id, string $password): array
{
    if ($password === '') {
        return ['success' => false, 'message' => 'Password cannot be empty.'];
    }
    $user = getUserById($id);
    if (!$user) {
        return ['success' => false, 'message' => 'User not found.'];
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    getDb()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $id]);
    logActivity('Password reset', 'Password reset for ' . $user['full_name']);
    return ['success' => true, 'message' => 'Password reset successfully.'];
}
