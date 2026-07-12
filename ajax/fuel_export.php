<?php
/** Fuel CSV export. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';requireAuth();enforceModuleAccess('fuel');header('Content-Type:text/csv');header('Content-Disposition:attachment; filename="fuel.csv"');$o=fopen('php://output','w');fputcsv($o,['Vehicle','Liters','Unit price','Cost','Date']);foreach(getDb()->query('SELECT v.registration_number,f.quantity_liters,f.price_per_liter,f.cost,f.logged_date FROM fuel_logs f JOIN vehicles v ON v.id=f.vehicle_id') as $r)fputcsv($o,$r);exit;
