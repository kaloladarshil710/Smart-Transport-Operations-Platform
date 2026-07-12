<?php
/** Returns upcoming and overdue maintenance for dashboards and reminders. */
declare(strict_types=1);function scheduledMaintenanceAlerts():array{return getDb()->query('SELECT m.*,v.registration_number FROM maintenance_logs m JOIN vehicles v ON v.id=m.vehicle_id WHERE m.status IN ("Scheduled","In Progress") AND m.deleted_at IS NULL AND m.scheduled_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) ORDER BY m.scheduled_date')->fetchAll();}
