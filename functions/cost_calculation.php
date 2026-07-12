<?php
/** Operational-cost calculations shared by reports and analytics. */
declare(strict_types=1);function operationalCostSummary():array{return getDb()->query('SELECT (SELECT COALESCE(SUM(cost),0) FROM fuel_logs WHERE deleted_at IS NULL) fuel_cost,(SELECT COALESCE(SUM(cost),0) FROM maintenance_logs WHERE deleted_at IS NULL) maintenance_cost,(SELECT COALESCE(SUM(amount),0) FROM expenses WHERE deleted_at IS NULL) expense_cost')->fetch()?:[];}
