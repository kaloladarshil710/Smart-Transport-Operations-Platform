<?php
/** License expiry status endpoint for dashboard alert widgets. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';require_once ROOT_PATH.'/functions/driver_functions.php';requireAuth();$drivers=getDb()->query('SELECT id,full_name,license_expiry FROM drivers WHERE deleted_at IS NULL AND license_expiry<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) ORDER BY license_expiry')->fetchAll();foreach($drivers as &$driver)$driver['license_status']=driverLicenseStatus($driver['license_expiry']);jsonResponse(['ok'=>true,'alerts'=>$drivers]);
