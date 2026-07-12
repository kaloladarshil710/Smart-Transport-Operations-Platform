<?php
/**
 * Dashboard data functions for TransitOps Enterprise Dashboard.
 *
 * @package TransitOps
 * @subpackage Dashboard
 */
declare(strict_types=1);

/**
 * Fetch complete KPI summary from the database.
 *
 * @return array<string, int|float>
 */
function getKpiSummary(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    try {
        $stmt = getDb()->query('SELECT * FROM vw_kpi_summary');
        $kpi = $stmt->fetch();

        if (!$kpi) {
            $kpi = getDefaultKpiValues();
        }

        $cache = $kpi;
        return $kpi;
    } catch (Throwable $e) {
        error_log('Dashboard KPI fetch error: ' . $e->getMessage());
        return getDefaultKpiValues();
    }
}

/**
 * Default KPI values for empty datasets.
 *
 * @return array<string, int|float>
 */
function getDefaultKpiValues(): array
{
    return [
        'total_vehicles' => 0,
        'available_vehicles' => 0,
        'vehicles_on_trip' => 0,
        'vehicles_in_maintenance' => 0,
        'retired_vehicles' => 0,
        'total_drivers' => 0,
        'available_drivers' => 0,
        'drivers_on_trip' => 0,
        'suspended_drivers' => 0,
        'trips_today' => 0,
        'active_trips' => 0,
        'completed_trips' => 0,
        'cancelled_trips' => 0,
        'fleet_utilization' => 0,
        'fuel_efficiency' => 0,
        'fuel_cost' => 0.00,
        'maintenance_cost' => 0.00,
        'operational_cost' => 0.00,
        'total_revenue' => 0.00,
        'vehicle_roi' => 0,
    ];
}

/**
 * Get vehicle status distribution.
 *
 * @return array<int, array{label: string, count: int, color: string, icon: string}>
 */
function getVehicleStatusDistribution(): array
{
    try {
        $stmt = getDb()->query("
            SELECT status, COUNT(*) AS cnt
            FROM vehicles
            WHERE deleted_at IS NULL
            GROUP BY status
        ");
        $rows = $stmt->fetchAll();

        $statusMap = [
            'Available' => ['label' => 'Available', 'color' => '#22c55e', 'icon' => 'fa-check-circle'],
            'On Trip' => ['label' => 'On Trip', 'color' => '#2563eb', 'icon' => 'fa-route'],
            'In Shop' => ['label' => 'Maintenance', 'color' => '#f59e0b', 'icon' => 'fa-wrench'],
            'Retired' => ['label' => 'Retired', 'color' => '#64748b', 'icon' => 'fa-archive'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $status = $row['status'];
            if (isset($statusMap[$status])) {
                $result[] = [
                    'label' => $statusMap[$status]['label'],
                    'count' => (int) $row['cnt'],
                    'color' => $statusMap[$status]['color'],
                    'icon' => $statusMap[$status]['icon'],
                ];
            }
        }

        // Ensure all statuses are represented
        $foundLabels = array_column($result, 'label');
        foreach ($statusMap as $key => $info) {
            if (!in_array($info['label'], $foundLabels, true)) {
                $result[] = [
                    'label' => $info['label'],
                    'count' => 0,
                    'color' => $info['color'],
                    'icon' => $info['icon'],
                ];
            }
        }

        return $result;
    } catch (Throwable $e) {
        error_log('Vehicle status fetch error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get driver status distribution.
 *
 * @return array<int, array{label: string, count: int, color: string, icon: string}>
 */
function getDriverStatusDistribution(): array
{
    try {
        $stmt = getDb()->query("
            SELECT status, COUNT(*) AS cnt
            FROM drivers
            WHERE deleted_at IS NULL
            GROUP BY status
        ");
        $rows = $stmt->fetchAll();

        $statusMap = [
            'Available' => ['label' => 'Available', 'color' => '#22c55e', 'icon' => 'fa-check-circle'],
            'On Trip' => ['label' => 'On Trip', 'color' => '#2563eb', 'icon' => 'fa-route'],
            'Off Duty' => ['label' => 'Off Duty', 'color' => '#8b5cf6', 'icon' => 'fa-clock-o'],
            'Suspended' => ['label' => 'Suspended', 'color' => '#ef4444', 'icon' => 'fa-ban'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $status = $row['status'];
            if (isset($statusMap[$status])) {
                $result[] = [
                    'label' => $statusMap[$status]['label'],
                    'count' => (int) $row['cnt'],
                    'color' => $statusMap[$status]['color'],
                    'icon' => $statusMap[$status]['icon'],
                ];
            }
        }

        $foundLabels = array_column($result, 'label');
        foreach ($statusMap as $key => $info) {
            if (!in_array($info['label'], $foundLabels, true)) {
                $result[] = [
                    'label' => $info['label'],
                    'count' => 0,
                    'color' => $info['color'],
                    'icon' => $info['icon'],
                ];
            }
        }

        return $result;
    } catch (Throwable $e) {
        error_log('Driver status fetch error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get recent trips.
 *
 * @param int $limit Number of trips to fetch
 * @return array<int, array>
 */
function getRecentTrips(int $limit = 10): array
{
    try {
        $stmt = getDb()->prepare("
            SELECT
                t.id,
                t.trip_number,
                COALESCE(v.vehicle_name, CONCAT(v.make, ' ', v.model)) AS vehicle_name,
                v.registration_number,
                d.full_name AS driver_name,
                t.origin,
                t.destination,
                t.status,
                t.dispatch_at,
                t.created_at
            FROM trips t
            JOIN vehicles v ON v.id = t.vehicle_id
            JOIN drivers d ON d.id = t.driver_id
            WHERE t.deleted_at IS NULL
            ORDER BY t.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Recent trips fetch error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get recent activities.
 *
 * @param int $limit Number of activities to fetch
 * @return array<int, array>
 */
function getRecentActivities(int $limit = 15): array
{
    try {
        $stmt = getDb()->prepare("
            SELECT
                a.id,
                a.action,
                a.description,
                a.created_at,
                COALESCE(u.full_name, 'System') AS user_name
            FROM activity_logs a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Recent activities fetch error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get maintenance alerts.
 *
 * @return array<string, int>
 */
function getMaintenanceAlerts(): array
{
    try {
        $stmt = getDb()->query("
            SELECT
                SUM(CASE WHEN status IN ('Pending','In Progress') AND scheduled_date <= CURDATE() THEN 1 ELSE 0 END) AS in_shop,
                SUM(CASE WHEN status = 'Pending' AND scheduled_date > CURDATE() AND scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS upcoming,
                SUM(CASE WHEN status IN ('Pending','In Progress') AND scheduled_date < CURDATE() THEN 1 ELSE 0 END) AS overdue
            FROM maintenance_logs
            WHERE deleted_at IS NULL
        ");
        $alerts = $stmt->fetch();

        return [
            'in_shop' => (int) ($alerts['in_shop'] ?? 0),
            'upcoming' => (int) ($alerts['upcoming'] ?? 0),
            'overdue' => (int) ($alerts['overdue'] ?? 0),
        ];
    } catch (Throwable $e) {
        error_log('Maintenance alerts fetch error: ' . $e->getMessage());
        return ['in_shop' => 0, 'upcoming' => 0, 'overdue' => 0];
    }
}

/**
 * Get vehicle document alerts (insurance, fitness, permit).
 *
 * @return array<string, int>
 */
function getVehicleDocumentAlerts(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_vehicle_document_alerts');
        $alerts = $stmt->fetch();

        if (!$alerts) {
            return [
                'insurance_expired' => 0,
                'insurance_expiring' => 0,
                'fitness_expired' => 0,
                'fitness_expiring' => 0,
                'permit_expired' => 0,
                'permit_expiring' => 0,
            ];
        }

        return [
            'insurance_expired' => (int) ($alerts['insurance_expired'] ?? 0),
            'insurance_expiring' => (int) ($alerts['insurance_expiring'] ?? 0),
            'fitness_expired' => (int) ($alerts['fitness_expired'] ?? 0),
            'fitness_expiring' => (int) ($alerts['fitness_expiring'] ?? 0),
            'permit_expired' => (int) ($alerts['permit_expired'] ?? 0),
            'permit_expiring' => (int) ($alerts['permit_expiring'] ?? 0),
        ];
    } catch (Throwable $e) {
        error_log('Vehicle document alerts fetch error: ' . $e->getMessage());
        return [
            'insurance_expired' => 0,
            'insurance_expiring' => 0,
            'fitness_expired' => 0,
            'fitness_expiring' => 0,
            'permit_expired' => 0,
            'permit_expiring' => 0,
        ];
    }
}

/**
 * Get license alerts.
 *
 * @return array<string, int>
 */
function getLicenseAlerts(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_license_alerts');
        $alerts = $stmt->fetch();

        if (!$alerts) {
            return ['expired' => 0, 'expiring_7_days' => 0, 'expiring_15_days' => 0, 'expiring_30_days' => 0];
        }

        return [
            'expired' => (int) ($alerts['expired'] ?? 0),
            'expiring_7_days' => (int) ($alerts['expiring_7_days'] ?? 0),
            'expiring_15_days' => (int) ($alerts['expiring_15_days'] ?? 0),
            'expiring_30_days' => (int) ($alerts['expiring_30_days'] ?? 0),
        ];
    } catch (Throwable $e) {
        error_log('License alerts fetch error: ' . $e->getMessage());
        return ['expired' => 0, 'expiring_7_days' => 0, 'expiring_15_days' => 0, 'expiring_30_days' => 0];
    }
}

/**
 * Get unread notification count for current user.
 *
 * @return int
 */
function getUnreadNotificationCount(): int
{
    try {
        $userId = (int) (currentUser()['id'] ?? 0);
        $stmt = getDb()->prepare("
            SELECT COUNT(*) AS cnt
            FROM notifications
            WHERE is_read = 0 AND deleted_at IS NULL
            AND (user_id IS NULL OR user_id = ?)
        ");
        $stmt->execute([$userId]);
        return (int) ($stmt->fetch()['cnt'] ?? 0);
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Get recent notifications for current user.
 *
 * @param int $limit
 * @return array<int, array>
 */
function getRecentNotifications(int $limit = 8): array
{
    try {
        $userId = (int) (currentUser()['id'] ?? 0);
        $stmt = getDb()->prepare("
            SELECT id, title, message, priority, is_read, created_at
            FROM notifications
            WHERE deleted_at IS NULL
            AND (user_id IS NULL OR user_id = ?)
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get monthly trip data for charts.
 *
 * @return array
 */
function getMonthlyTrips(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_monthly_trips');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get monthly revenue data for charts.
 *
 * @return array
 */
function getMonthlyRevenue(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_monthly_revenue');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get monthly expenses data for charts.
 *
 * @return array
 */
function getMonthlyExpenses(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_monthly_expenses');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get monthly fuel consumption data for charts.
 *
 * @return array
 */
function getMonthlyFuel(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_monthly_fuel');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get monthly maintenance cost data for charts.
 *
 * @return array
 */
function getMonthlyMaintenance(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_monthly_maintenance');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get revenue vs expense data for charts.
 *
 * @return array
 */
function getRevenueVsExpense(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_revenue_vs_expense');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get fleet utilization trend data for charts.
 *
 * @return array
 */
function getFleetUtilizationTrend(): array
{
    try {
        $stmt = getDb()->query('SELECT * FROM vw_fleet_utilization_trend');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get top vehicles by revenue for charts.
 *
 * @param int $limit
 * @return array
 */
function getTopVehicles(int $limit = 10): array
{
    try {
        $stmt = getDb()->prepare('SELECT * FROM vw_top_vehicles LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get top drivers by revenue for charts.
 *
 * @param int $limit
 * @return array
 */
function getTopDrivers(int $limit = 10): array
{
    try {
        $stmt = getDb()->prepare('SELECT * FROM vw_top_drivers LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get all dashboard data as a single JSON-serializable array (for AJAX refresh).
 *
 * @return array
 */
function getDashboardJsonData(): array
{
    $kpi = getKpiSummary();

    return [
        'kpi' => $kpi,
        'vehicle_status' => getVehicleStatusDistribution(),
        'driver_status' => getDriverStatusDistribution(),
        'recent_trips' => formatTripsForJson(getRecentTrips(10)),
        'activities' => formatActivitiesForJson(getRecentActivities(15)),
        'maintenance_alerts' => getMaintenanceAlerts(),
        'document_alerts' => getVehicleDocumentAlerts(),
        'license_alerts' => getLicenseAlerts(),
        'unread_notifications' => getUnreadNotificationCount(),
        'notifications' => formatNotificationsForJson(getRecentNotifications(8)),
        'chart_monthly_trips' => getMonthlyTrips(),
        'chart_monthly_revenue' => getMonthlyRevenue(),
        'chart_monthly_expenses' => getMonthlyExpenses(),
        'chart_monthly_fuel' => getMonthlyFuel(),
        'chart_monthly_maintenance' => getMonthlyMaintenance(),
        'chart_revenue_vs_expense' => getRevenueVsExpense(),
        'chart_fleet_utilization' => getFleetUtilizationTrend(),
        'chart_top_vehicles' => getTopVehicles(),
        'chart_top_drivers' => getTopDrivers(),
        'timestamp' => date('Y-m-d H:i:s'),
    ];
}

/**
 * Format trips for JSON response.
 *
 * @param array $trips
 * @return array
 */
function formatTripsForJson(array $trips): array
{
    return array_map(function ($trip) {
        return [
            'id' => (int) $trip['id'],
            'trip_number' => $trip['trip_number'],
            'vehicle' => $trip['vehicle_name'] ?? $trip['registration_number'],
            'driver' => $trip['driver_name'],
            'origin' => $trip['origin'],
            'destination' => $trip['destination'],
            'status' => $trip['status'],
            'dispatch_time' => $trip['dispatch_at'] ? date('d M Y H:i', strtotime($trip['dispatch_at'])) : '—',
        ];
    }, $trips);
}

/**
 * Format activities for JSON response.
 *
 * @param array $activities
 * @return array
 */
function formatActivitiesForJson(array $activities): array
{
    return array_map(function ($a) {
        return [
            'id' => (int) $a['id'],
            'action' => $a['action'],
            'description' => $a['description'],
            'user' => $a['user_name'] ?? 'System',
            'time' => date('d M Y H:i', strtotime($a['created_at'])),
        ];
    }, $activities);
}

/**
 * Format notifications for JSON response.
 *
 * @param array $notifications
 * @return array
 */
function formatNotificationsForJson(array $notifications): array
{
    return array_map(function ($n) {
        return [
            'id' => (int) $n['id'],
            'title' => $n['title'],
            'message' => $n['message'],
            'priority' => $n['priority'],
            'is_read' => (bool) $n['is_read'],
            'time' => date('d M Y H:i', strtotime($n['created_at'])),
        ];
    }, $notifications);
}

/**
 * Get dashboard widgets based on user role.
 *
 * @return array<string, bool>
 */
function getDashboardWidgetsByRole(): array
{
    $role = currentUser()['role'] ?? 'viewer';

    $widgets = [
        'kpi_vehicles' => false,
        'kpi_drivers' => false,
        'kpi_trips' => false,
        'kpi_finance' => false,
        'vehicle_status' => false,
        'driver_status' => false,
        'recent_trips' => false,
        'activities' => false,
        'maintenance_alerts' => false,
        'license_alerts' => false,
        'notifications_panel' => false,
        'chart_trips' => false,
        'chart_revenue' => false,
        'chart_expenses' => false,
        'chart_fuel' => false,
        'chart_maintenance' => false,
        'chart_utilization' => false,
        'chart_revenue_vs_expense' => false,
        'chart_roi' => false,
        'chart_top_vehicles' => false,
        'chart_top_drivers' => false,
    ];

    switch ($role) {
        case 'admin':
            foreach (array_keys($widgets) as $key) {
                $widgets[$key] = true;
            }
            break;

        case 'fleet_manager':
            $widgets['kpi_vehicles'] = true;
            $widgets['kpi_drivers'] = true;
            $widgets['kpi_trips'] = true;
            $widgets['kpi_finance'] = true;
            $widgets['vehicle_status'] = true;
            $widgets['driver_status'] = true;
            $widgets['recent_trips'] = true;
            $widgets['activities'] = true;
            $widgets['maintenance_alerts'] = true;
            $widgets['license_alerts'] = true;
            $widgets['notifications_panel'] = true;
            $widgets['chart_trips'] = true;
            $widgets['chart_fuel'] = true;
            $widgets['chart_maintenance'] = true;
            $widgets['chart_utilization'] = true;
            break;

        case 'dispatcher':
            $widgets['kpi_vehicles'] = true;
            $widgets['kpi_drivers'] = true;
            $widgets['kpi_trips'] = true;
            $widgets['vehicle_status'] = true;
            $widgets['driver_status'] = true;
            $widgets['recent_trips'] = true;
            $widgets['activities'] = true;
            $widgets['notifications_panel'] = true;
            $widgets['chart_trips'] = true;
            $widgets['chart_fleet_utilization'] = true;
            break;

        case 'safety_officer':
            $widgets['kpi_drivers'] = true;
            $widgets['kpi_trips'] = true;
            $widgets['driver_status'] = true;
            $widgets['activities'] = true;
            $widgets['maintenance_alerts'] = true;
            $widgets['license_alerts'] = true;
            $widgets['notifications_panel'] = true;
            $widgets['chart_maintenance'] = true;
            break;

        case 'financial_analyst':
            $widgets['kpi_finance'] = true;
            $widgets['chart_revenue'] = true;
            $widgets['chart_expenses'] = true;
            $widgets['chart_fuel'] = true;
            $widgets['chart_maintenance'] = true;
            $widgets['chart_revenue_vs_expense'] = true;
            $widgets['chart_roi'] = true;
            $widgets['chart_top_vehicles'] = true;
            $widgets['chart_top_drivers'] = true;
            $widgets['activities'] = true;
            $widgets['notifications_panel'] = true;
            break;

        default:
            // viewer and others see limited widgets
            $widgets['kpi_vehicles'] = true;
            $widgets['kpi_drivers'] = true;
            $widgets['kpi_trips'] = true;
            $widgets['vehicle_status'] = true;
            $widgets['driver_status'] = true;
            $widgets['recent_trips'] = true;
            break;
    }

    return $widgets;
}
