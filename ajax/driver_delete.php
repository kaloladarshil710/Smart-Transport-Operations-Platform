<?php
/** AJAX soft-delete endpoint. */
require_once __DIR__.'/../config/config.php';requireAuth();enforceModuleAccess('drivers');requireCsrfFromPost();getDb()->prepare('UPDATE drivers SET deleted_at=NOW(),status="Inactive" WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);jsonResponse(['ok'=>true]);
