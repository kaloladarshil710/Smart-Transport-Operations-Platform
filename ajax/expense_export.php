<?php
/** Expense CSV export. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';requireAuth();enforceModuleAccess('expenses');header('Content-Type:text/csv');header('Content-Disposition:attachment; filename="expenses.csv"');$o=fopen('php://output','w');fputcsv($o,['Title','Type','Amount','Date']);foreach(getDb()->query('SELECT title,expense_type,amount,expense_date FROM expenses') as $r)fputcsv($o,$r);exit;
