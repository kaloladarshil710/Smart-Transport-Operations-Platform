-- Dashboard-specific views for TransitOps Enterprise Dashboard
-- Run: mysql -u root transitops < database/dashboard_views.sql

USE transitops;

-- Main KPI summary with all 21 KPIs in one query
CREATE OR REPLACE VIEW vw_kpi_summary AS
SELECT
  (SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NULL) AS total_vehicles,
  (SELECT COUNT(*) FROM vehicles WHERE status = 'Available' AND deleted_at IS NULL) AS available_vehicles,
  (SELECT COUNT(*) FROM vehicles WHERE status = 'On Trip' AND deleted_at IS NULL) AS vehicles_on_trip,
  (SELECT COUNT(*) FROM vehicles WHERE status = 'In Shop' AND deleted_at IS NULL) AS vehicles_in_maintenance,
  (SELECT COUNT(*) FROM vehicles WHERE status = 'Retired' AND deleted_at IS NULL) AS retired_vehicles,
  (SELECT COUNT(*) FROM drivers WHERE deleted_at IS NULL) AS total_drivers,
  (SELECT COUNT(*) FROM drivers WHERE status = 'Available' AND deleted_at IS NULL) AS available_drivers,
  (SELECT COUNT(*) FROM drivers WHERE status = 'On Trip' AND deleted_at IS NULL) AS drivers_on_trip,
  (SELECT COUNT(*) FROM drivers WHERE status = 'Suspended' AND deleted_at IS NULL) AS suspended_drivers,
  (SELECT COUNT(*) FROM trips WHERE DATE(start_date) = CURDATE() AND deleted_at IS NULL) AS trips_today,
  (SELECT COUNT(*) FROM trips WHERE status IN ('Draft','Dispatched','In Progress') AND deleted_at IS NULL) AS active_trips,
  (SELECT COUNT(*) FROM trips WHERE status = 'Completed' AND deleted_at IS NULL) AS completed_trips,
  (SELECT COUNT(*) FROM trips WHERE status = 'Cancelled' AND deleted_at IS NULL) AS cancelled_trips,
  ROUND(
    (SELECT COUNT(*) FROM vehicles WHERE status = 'On Trip' AND deleted_at IS NULL) * 100.0 /
    NULLIF((SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NULL), 0)
  , 2) AS fleet_utilization,
  ROUND(
    (SELECT COALESCE(SUM(t.actual_distance_km), 0) FROM trips t WHERE t.deleted_at IS NULL)
    /
    NULLIF((SELECT COALESCE(SUM(f.quantity_liters), 0) FROM fuel_logs f WHERE f.deleted_at IS NULL), 0)
  , 2) AS fuel_efficiency,
  (SELECT COALESCE(SUM(cost), 0) FROM fuel_logs WHERE MONTH(logged_date) = MONTH(CURDATE()) AND YEAR(logged_date) = YEAR(CURDATE()) AND deleted_at IS NULL) AS fuel_cost,
  (SELECT COALESCE(SUM(cost), 0) FROM maintenance_logs WHERE MONTH(scheduled_date) = MONTH(CURDATE()) AND YEAR(scheduled_date) = YEAR(CURDATE()) AND deleted_at IS NULL) AS maintenance_cost,
  (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE()) AND deleted_at IS NULL) AS operational_cost,
  (SELECT COALESCE(SUM(revenue), 0) FROM trips WHERE status = 'Completed' AND deleted_at IS NULL) AS total_revenue,
  ROUND(
    (
      (SELECT COALESCE(SUM(revenue), 0) FROM trips WHERE status = 'Completed' AND deleted_at IS NULL)
      -
      (SELECT COALESCE(SUM(cost), 0) FROM fuel_logs WHERE deleted_at IS NULL)
      -
      (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE deleted_at IS NULL)
    ) * 100.0 /
    NULLIF((SELECT COALESCE(SUM(purchase_cost), 0) FROM vehicles WHERE deleted_at IS NULL), 0)
  , 2) AS vehicle_roi;

-- Monthly trip statistics (last 12 months)
CREATE OR REPLACE VIEW vw_monthly_trips AS
SELECT
  DATE_FORMAT(start_date, '%Y-%m') AS month_label,
  DATE_FORMAT(start_date, '%b %Y') AS month_display,
  COUNT(*) AS total,
  SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed,
  SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled,
  SUM(CASE WHEN status IN ('Draft','Dispatched','In Progress') THEN 1 ELSE 0 END) AS active
FROM trips
WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND deleted_at IS NULL
GROUP BY DATE_FORMAT(start_date, '%Y-%m'), DATE_FORMAT(start_date, '%b %Y')
ORDER BY month_label ASC;

-- Monthly revenue (last 12 months)
CREATE OR REPLACE VIEW vw_monthly_revenue AS
SELECT
  DATE_FORMAT(start_date, '%Y-%m') AS month_label,
  DATE_FORMAT(start_date, '%b %Y') AS month_display,
  COALESCE(SUM(revenue), 0) AS revenue
FROM trips
WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND status = 'Completed' AND deleted_at IS NULL
GROUP BY DATE_FORMAT(start_date, '%Y-%m'), DATE_FORMAT(start_date, '%b %Y')
ORDER BY month_label ASC;

-- Monthly expenses (last 12 months)
CREATE OR REPLACE VIEW vw_monthly_expenses AS
SELECT
  DATE_FORMAT(expense_date, '%Y-%m') AS month_label,
  DATE_FORMAT(expense_date, '%b %Y') AS month_display,
  COALESCE(SUM(amount), 0) AS total_expense
FROM expenses
WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND deleted_at IS NULL
GROUP BY DATE_FORMAT(expense_date, '%Y-%m'), DATE_FORMAT(expense_date, '%b %Y')
ORDER BY month_label ASC;

-- Monthly fuel consumption (last 12 months)
CREATE OR REPLACE VIEW vw_monthly_fuel AS
SELECT
  DATE_FORMAT(logged_date, '%Y-%m') AS month_label,
  DATE_FORMAT(logged_date, '%b %Y') AS month_display,
  COALESCE(SUM(quantity_liters), 0) AS total_liters,
  COALESCE(SUM(cost), 0) AS total_cost
FROM fuel_logs
WHERE logged_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND deleted_at IS NULL
GROUP BY DATE_FORMAT(logged_date, '%Y-%m'), DATE_FORMAT(logged_date, '%b %Y')
ORDER BY month_label ASC;

-- Monthly maintenance cost (last 12 months)
CREATE OR REPLACE VIEW vw_monthly_maintenance AS
SELECT
  DATE_FORMAT(scheduled_date, '%Y-%m') AS month_label,
  DATE_FORMAT(scheduled_date, '%b %Y') AS month_display,
  COALESCE(SUM(cost), 0) AS total_cost
FROM maintenance_logs
WHERE scheduled_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND deleted_at IS NULL
GROUP BY DATE_FORMAT(scheduled_date, '%Y-%m'), DATE_FORMAT(scheduled_date, '%b %Y')
ORDER BY month_label ASC;

-- Top 10 vehicles by revenue
CREATE OR REPLACE VIEW vw_top_vehicles AS
SELECT
  v.id,
  v.registration_number,
  COALESCE(v.vehicle_name, CONCAT(v.make, ' ', v.model)) AS vehicle_name,
  v.status,
  COUNT(t.id) AS trip_count,
  COALESCE(SUM(t.revenue), 0) AS total_revenue,
  COALESCE(SUM(t.actual_distance_km), 0) AS total_distance
FROM vehicles v
LEFT JOIN trips t ON t.vehicle_id = v.id AND t.status = 'Completed' AND t.deleted_at IS NULL
WHERE v.deleted_at IS NULL
GROUP BY v.id
ORDER BY total_revenue DESC
LIMIT 10;

-- Top 10 drivers by revenue
CREATE OR REPLACE VIEW vw_top_drivers AS
SELECT
  d.id,
  d.full_name,
  d.phone,
  d.status,
  d.safety_score,
  COUNT(t.id) AS trip_count,
  COALESCE(SUM(t.revenue), 0) AS total_revenue
FROM drivers d
LEFT JOIN trips t ON t.driver_id = d.id AND t.status = 'Completed' AND t.deleted_at IS NULL
WHERE d.deleted_at IS NULL
GROUP BY d.id
ORDER BY total_revenue DESC
LIMIT 10;

-- Recent activities (last 20)
CREATE OR REPLACE VIEW vw_recent_activities AS
SELECT id, action, description, created_at
FROM activity_logs
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY created_at DESC
LIMIT 20;

-- License expiry summary
CREATE OR REPLACE VIEW vw_license_alerts AS
SELECT
  COUNT(*) AS total_drivers,
  SUM(CASE WHEN license_expiry < CURDATE() THEN 1 ELSE 0 END) AS expired,
  SUM(CASE WHEN license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS expiring_7_days,
  SUM(CASE WHEN license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 1 ELSE 0 END) AS expiring_15_days,
  SUM(CASE WHEN license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS expiring_30_days
FROM drivers
WHERE deleted_at IS NULL;

-- Vehicle insurance & fitness alerts
CREATE OR REPLACE VIEW vw_vehicle_document_alerts AS
SELECT
  COUNT(*) AS total_vehicles,
  SUM(CASE WHEN insurance_expiry IS NOT NULL AND insurance_expiry < CURDATE() THEN 1 ELSE 0 END) AS insurance_expired,
  SUM(CASE WHEN insurance_expiry IS NOT NULL AND insurance_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS insurance_expiring,
  SUM(CASE WHEN fitness_expiry IS NOT NULL AND fitness_expiry < CURDATE() THEN 1 ELSE 0 END) AS fitness_expired,
  SUM(CASE WHEN fitness_expiry IS NOT NULL AND fitness_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS fitness_expiring,
  SUM(CASE WHEN permit_expiry IS NOT NULL AND permit_expiry < CURDATE() THEN 1 ELSE 0 END) AS permit_expired,
  SUM(CASE WHEN permit_expiry IS NOT NULL AND permit_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS permit_expiring
FROM vehicles
WHERE deleted_at IS NULL;

-- Revenue vs Expense summary by month
CREATE OR REPLACE VIEW vw_revenue_vs_expense AS
SELECT
  COALESCE(r.month_label, e.month_label) AS month_label,
  COALESCE(r.month_display, e.month_display) AS month_display,
  COALESCE(r.revenue, 0) AS revenue,
  COALESCE(e.total_expense, 0) AS total_expense,
  COALESCE(r.revenue, 0) - COALESCE(e.total_expense, 0) AS profit
FROM vw_monthly_revenue r
LEFT JOIN vw_monthly_expenses e ON r.month_label = e.month_label
UNION
SELECT
  COALESCE(r.month_label, e.month_label),
  COALESCE(r.month_display, e.month_display),
  COALESCE(r.revenue, 0),
  COALESCE(e.total_expense, 0),
  COALESCE(r.revenue, 0) - COALESCE(e.total_expense, 0)
FROM vw_monthly_expenses e
LEFT JOIN vw_monthly_revenue r ON r.month_label = e.month_label
ORDER BY month_label ASC;

-- Fleet utilization trend (last 12 months)
CREATE OR REPLACE VIEW vw_fleet_utilization_trend AS
SELECT
  DATE_FORMAT(t.start_date, '%Y-%m') AS month_label,
  DATE_FORMAT(t.start_date, '%b %Y') AS month_display,
  COUNT(DISTINCT t.vehicle_id) AS active_vehicles,
  (SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NULL) AS total_vehicles,
  ROUND(COUNT(DISTINCT t.vehicle_id) * 100.0 / NULLIF((SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NULL), 0), 2) AS utilization_percent
FROM trips t
WHERE t.start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND t.status IN ('Dispatched','Completed','In Progress') AND t.deleted_at IS NULL
GROUP BY DATE_FORMAT(t.start_date, '%Y-%m'), DATE_FORMAT(t.start_date, '%b %Y')
ORDER BY month_label ASC;
