<?php
/**
 * Enterprise report data functions for TransitOps.
 *
 * @package TransitOps
 * @subpackage Reports
 */
declare(strict_types=1);

require_once __DIR__ . '/roi_functions.php';
require_once __DIR__ . '/cost_calculation.php';

/**
 * Get vehicle report data.
 */
function getVehicleReport(int $vehicleId): array
{
    $stmt = getDb()->prepare("
        SELECT v.*, vt.name AS vehicle_type, r.name AS region_name,
               (SELECT COUNT(*) FROM trips WHERE vehicle_id=v.id AND deleted_at IS NULL) AS total_trips,
               (SELECT COUNT(*) FROM trips WHERE vehicle_id=v.id AND status='Completed' AND deleted_at IS NULL) AS completed_trips,
               (SELECT COALESCE(SUM(revenue),0) FROM trips WHERE vehicle_id=v.id AND status='Completed' AND deleted_at IS NULL) AS total_revenue,
               (SELECT COALESCE(SUM(actual_distance_km),0) FROM trips WHERE vehicle_id=v.id AND status='Completed' AND deleted_at IS NULL) AS total_distance,
               (SELECT COALESCE(SUM(cost),0) FROM fuel_logs WHERE vehicle_id=v.id AND deleted_at IS NULL) AS fuel_cost,
               (SELECT COALESCE(SUM(cost),0) FROM maintenance_logs WHERE vehicle_id=v.id AND deleted_at IS NULL) AS maintenance_cost,
               (SELECT COALESCE(SUM(amount),0) FROM expenses WHERE vehicle_id=v.id AND deleted_at IS NULL) AS expense_cost
        FROM vehicles v
        JOIN vehicle_types vt ON vt.id=v.vehicle_type_id
        JOIN regions r ON r.id=v.region_id
        WHERE v.id=? AND v.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$vehicleId]);
    $data = $stmt->fetch() ?: [];
    $cost = (float)($data['fuel_cost']??0)+(float)($data['maintenance_cost']??0)+(float)($data['expense_cost']??0);
    $data['total_cost'] = $cost;
    $data['net_revenue'] = (float)($data['total_revenue']??0) - $cost;
    $data['roi'] = $cost > 0 ? round(((float)($data['total_revenue']??0)-$cost)/max(1,(float)($data['purchase_cost']??1))*100, 2) : 0;
    return $data;
}

/**
 * Get driver report data.
 */
function getDriverReport(int $driverId): array
{
    $stmt = getDb()->prepare("
        SELECT d.*, r.name AS region_name,
               (SELECT COUNT(*) FROM trips WHERE driver_id=d.id AND deleted_at IS NULL) AS total_trips,
               (SELECT COUNT(*) FROM trips WHERE driver_id=d.id AND status='Completed' AND deleted_at IS NULL) AS completed_trips,
               (SELECT COUNT(*) FROM trips WHERE driver_id=d.id AND status='Cancelled' AND deleted_at IS NULL) AS cancelled_trips,
               (SELECT COALESCE(SUM(revenue),0) FROM trips WHERE driver_id=d.id AND status='Completed' AND deleted_at IS NULL) AS total_revenue,
               (SELECT COALESCE(SUM(actual_distance_km),0) FROM trips WHERE driver_id=d.id AND status='Completed' AND deleted_at IS NULL) AS total_distance,
               (SELECT COALESCE(AVG(fuel_efficiency),0) FROM vehicles WHERE id IN (SELECT vehicle_id FROM trips WHERE driver_id=d.id AND deleted_at IS NULL)) AS avg_fuel_efficiency
        FROM drivers d
        JOIN regions r ON r.id=d.region_id
        WHERE d.id=? AND d.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$driverId]);
    return $stmt->fetch() ?: [];
}

/**
 * Get revenue report.
 */
function getRevenueReport(string $dateFrom, string $dateTo): array
{
    $stmt = getDb()->prepare("
        SELECT COALESCE(SUM(revenue),0) AS total_revenue,
               COUNT(*) AS total_trips,
               COALESCE(AVG(revenue),0) AS avg_revenue,
               MAX(revenue) AS max_revenue,
               MIN(revenue) AS min_revenue
        FROM trips
        WHERE status='Completed' AND start_date>=? AND start_date<=? AND deleted_at IS NULL
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $summary = $stmt->fetch() ?: [];

    $detail = getDb()->prepare("
        SELECT t.trip_number, t.revenue, t.actual_distance_km, t.start_date,
               v.registration_number, d.full_name AS driver_name
        FROM trips t
        JOIN vehicles v ON v.id=t.vehicle_id
        JOIN drivers d ON d.id=t.driver_id
        WHERE t.status='Completed' AND t.start_date>=? AND t.start_date<=? AND t.deleted_at IS NULL
        ORDER BY t.revenue DESC
        LIMIT 100
    ");
    $detail->execute([$dateFrom, $dateTo]);

    return ['summary' => $summary, 'details' => $detail->fetchAll()];
}

/**
 * Get profit report data.
 */
function getProfitReport(string $dateFrom, string $dateTo): array
{
    $revenue = getDb()->prepare("SELECT COALESCE(SUM(revenue),0) FROM trips WHERE status='Completed' AND start_date>=? AND start_date<=? AND deleted_at IS NULL");
    $revenue->execute([$dateFrom, $dateTo]);
    $totalRevenue = (float)$revenue->fetchColumn();

    $fuelCost = getDb()->prepare("SELECT COALESCE(SUM(cost),0) FROM fuel_logs WHERE logged_date>=? AND logged_date<=? AND deleted_at IS NULL");
    $fuelCost->execute([$dateFrom, $dateTo]);
    $totalFuel = (float)$fuelCost->fetchColumn();

    $maintCost = getDb()->prepare("SELECT COALESCE(SUM(cost),0) FROM maintenance_logs WHERE scheduled_date>=? AND scheduled_date<=? AND deleted_at IS NULL");
    $maintCost->execute([$dateFrom, $dateTo]);
    $totalMaint = (float)$maintCost->fetchColumn();

    $expCost = getDb()->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date>=? AND expense_date<=? AND deleted_at IS NULL");
    $expCost->execute([$dateFrom, $dateTo]);
    $totalExpense = (float)$expCost->fetchColumn();

    $totalCost = $totalFuel + $totalMaint + $totalExpense;
    $netProfit = $totalRevenue - $totalCost;
    $margin = $totalRevenue > 0 ? round($netProfit / $totalRevenue * 100, 2) : 0;

    return [
        'total_revenue' => $totalRevenue,
        'fuel_cost' => $totalFuel,
        'maintenance_cost' => $totalMaint,
        'expense_cost' => $totalExpense,
        'total_cost' => $totalCost,
        'net_profit' => $netProfit,
        'profit_margin' => $margin,
    ];
}

/**
 * Get fleet utilization report data.
 */
function getFleetUtilizationReport(): array
{
    $total = getDb()->query("SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NULL")->fetchColumn();
    $available = getDb()->query("SELECT COUNT(*) FROM vehicles WHERE status='Available' AND deleted_at IS NULL")->fetchColumn();
    $onTrip = getDb()->query("SELECT COUNT(*) FROM vehicles WHERE status='On Trip' AND deleted_at IS NULL")->fetchColumn();
    $inShop = getDb()->query("SELECT COUNT(*) FROM vehicles WHERE status='In Shop' AND deleted_at IS NULL")->fetchColumn();
    $retired = getDb()->query("SELECT COUNT(*) FROM vehicles WHERE status='Retired' AND deleted_at IS NULL")->fetchColumn();

    return [
        'total' => (int)$total,
        'available' => (int)$available,
        'on_trip' => (int)$onTrip,
        'in_shop' => (int)$inShop,
        'retired' => (int)$retired,
        'utilization' => $total > 0 ? round((int)$onTrip / (int)$total * 100, 2) : 0,
    ];
}

/**
 * Get monthly report comparison data.
 */
function getMonthlyComparison(int $months = 12): array
{
    return getDb()->query("
        SELECT DATE_FORMAT(start_date,'%Y-%m') AS month,
               DATE_FORMAT(start_date,'%b %Y') AS label,
               COUNT(*) AS total_trips,
               SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS completed,
               SUM(CASE WHEN status='Cancelled' THEN 1 ELSE 0 END) AS cancelled,
               COALESCE(SUM(CASE WHEN status='Completed' THEN revenue ELSE 0 END),0) AS revenue,
               COALESCE(AVG(CASE WHEN status='Completed' THEN actual_distance_km ELSE NULL END),0) AS avg_distance
        FROM trips
        WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH) AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(start_date,'%Y-%m'), DATE_FORMAT(start_date,'%b %Y')
        ORDER BY month ASC
    ")->fetchAll();
}

/**
 * Generate report summary KPIs.
 */
function getReportSummaryKpis(): array
{
    return [
        'total_vehicles' => getDb()->query("SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NULL")->fetchColumn(),
        'total_drivers' => getDb()->query("SELECT COUNT(*) FROM drivers WHERE deleted_at IS NULL")->fetchColumn(),
        'total_trips' => getDb()->query("SELECT COUNT(*) FROM trips WHERE deleted_at IS NULL")->fetchColumn(),
        'completed_trips' => getDb()->query("SELECT COUNT(*) FROM trips WHERE status='Completed' AND deleted_at IS NULL")->fetchColumn(),
        'total_revenue' => getDb()->query("SELECT COALESCE(SUM(revenue),0) FROM trips WHERE status='Completed' AND deleted_at IS NULL")->fetchColumn(),
        'total_fuel_cost' => getDb()->query("SELECT COALESCE(SUM(cost),0) FROM fuel_logs WHERE deleted_at IS NULL")->fetchColumn(),
        'total_maintenance_cost' => getDb()->query("SELECT COALESCE(SUM(cost),0) FROM maintenance_logs WHERE deleted_at IS NULL")->fetchColumn(),
        'total_expenses' => getDb()->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE deleted_at IS NULL")->fetchColumn(),
        'available_vehicles' => getDb()->query("SELECT COUNT(*) FROM vehicles WHERE status='Available' AND deleted_at IS NULL")->fetchColumn(),
        'available_drivers' => getDb()->query("SELECT COUNT(*) FROM drivers WHERE status='Available' AND deleted_at IS NULL")->fetchColumn(),
    ];
}
