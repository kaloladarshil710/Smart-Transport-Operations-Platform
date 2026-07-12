<?php
/**
 * Role and permission management helpers.
 */
declare(strict_types=1);

function getRolesForAdmin(): array
{
    $stmt = getDb()->query('SELECT id, name, display_name, status FROM roles ORDER BY id ASC');
    return $stmt->fetchAll();
}

function getRoleById(int $id): ?array
{
    $stmt = getDb()->prepare('SELECT id, name, display_name, status FROM roles WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getPermissionsList(): array
{
    $stmt = getDb()->query('SELECT id, name FROM permissions ORDER BY id ASC');
    return $stmt->fetchAll();
}

function getRolePermissions(int $roleId): array
{
    $stmt = getDb()->prepare('SELECT p.name FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ?');
    $stmt->execute([$roleId]);
    return array_column($stmt->fetchAll(), 'name');
}

function saveRole(array $input, ?int $id = null): array
{
    $name = strtolower(trim((string)($input['name'] ?? '')));
    $displayName = trim((string)($input['display_name'] ?? ''));
    $status = trim((string)($input['status'] ?? 'Active'));
    if ($name === '' || $displayName === '') {
        return ['success' => false, 'message' => 'Role name and display name are required.'];
    }
    if ($id === null) {
        $stmt = getDb()->prepare('INSERT INTO roles (name, display_name, status) VALUES (?, ?, ?)');
        $stmt->execute([$name, $displayName, $status]);
        return ['success' => true, 'message' => 'Role created successfully.', 'role_id' => (int)getDb()->lastInsertId()];
    }
    $stmt = getDb()->prepare('UPDATE roles SET name = ?, display_name = ?, status = ? WHERE id = ?');
    $stmt->execute([$name, $displayName, $status, $id]);
    return ['success' => true, 'message' => 'Role updated successfully.'];
}

function saveRolePermissions(int $roleId, array $permissionNames): void
{
    getDb()->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$roleId]);
    if ($permissionNames === []) {
        return;
    }
    $placeholders = implode(',', array_fill(0, count($permissionNames), '?'));
    $stmt = getDb()->prepare('SELECT id FROM permissions WHERE name IN (' . $placeholders . ')');
    $stmt->execute($permissionNames);
    $permissionIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));
    if ($permissionIds === []) {
        return;
    }
    $insert = getDb()->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
    foreach ($permissionIds as $permissionId) {
        $insert->execute([$roleId, $permissionId]);
    }
}
