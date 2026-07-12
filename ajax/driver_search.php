<?php
/** JSON autocomplete search for driver assignment controls. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';requireAuth();enforceModuleAccess('drivers');$q='%'.trim((string)($_GET['q']??'')).'%';$statement=getDb()->prepare('SELECT id,full_name,employee_id,license_number,status FROM drivers WHERE deleted_at IS NULL AND status="Available" AND (full_name LIKE ? OR employee_id LIKE ? OR license_number LIKE ?) LIMIT 10');$statement->execute([$q,$q,$q]);jsonResponse(['ok'=>true,'drivers'=>$statement->fetchAll()]);
