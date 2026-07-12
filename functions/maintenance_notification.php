<?php
/** Maintenance workflow notifications. */
declare(strict_types=1);function notifyMaintenance(int $id,string $event):void{$m=getMaintenanceById($id);if(!$m)return;getDb()->prepare('INSERT INTO notifications (user_id,title,message,priority) VALUES(NULL,?,?,?)')->execute(['Maintenance '.$event,$m['registration_number'].' · '.$m['maintenance_type'],in_array($event,['started','overdue'],true)?'High':'Normal']);}
