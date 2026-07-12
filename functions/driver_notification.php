<?php
/** Compliance notifications for driver license and safety events. */
declare(strict_types=1);
function notifyDriverCompliance(array $driver): void
{
    $license=driverLicenseStatus((string)$driver['license_expiry']); if ($license!=='Valid') getDb()->prepare('INSERT INTO notifications (user_id,title,message,priority) VALUES (NULL,?,?,?)')->execute(['Driver license '.$license,$driver['full_name'].' license expires on '.$driver['license_expiry'],$license==='Expired'?'High':'Normal']);
    if ((float)($driver['safety_score'] ?? 100)<60) getDb()->prepare('INSERT INTO notifications (user_id,title,message,priority) VALUES (NULL,?,?,?)')->execute(['Low driver safety score',$driver['full_name'].' safety score requires review.','High']);
}
