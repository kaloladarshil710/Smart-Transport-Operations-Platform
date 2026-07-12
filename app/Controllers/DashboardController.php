<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Core\View;
use App\Support\Auth;

final class DashboardController
{
    public function show(): Response
    {
        Auth::requireLogin();

        $stats = [
            'vehicles' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM vehicles')['total'],
            'available_vehicles' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM vehicles WHERE status = "Available"')['total'],
            'maintenance_vehicles' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM vehicles WHERE status = "In Shop"')['total'],
            'retired_vehicles' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM vehicles WHERE status = "Retired"')['total'],
            'drivers' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM drivers')['total'],
            'available_drivers' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM drivers WHERE status = "Available"')['total'],
            'on_trip_drivers' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM drivers WHERE status = "On Trip"')['total'],
            'active_trips' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM trips WHERE status IN ("Dispatched", "Draft")')['total'],
            'completed_trips' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM trips WHERE status = "Completed"')['total'],
            'pending_trips' => (int) Database::fetchOne('SELECT COUNT(*) AS total FROM trips WHERE status = "Draft"')['total'],
            'fuel_cost' => (float) Database::fetchOne('SELECT COALESCE(SUM(cost),0) AS total FROM fuel_logs')['total'],
            'maintenance_cost' => (float) Database::fetchOne('SELECT COALESCE(SUM(cost),0) AS total FROM maintenance_logs')['total'],
            'operational_cost' => (float) Database::fetchOne('SELECT COALESCE(SUM(expense_amount),0) AS total FROM expenses')['total'],
        ];

        $recentTrips = Database::fetchAll('SELECT t.id, t.source, t.destination, t.status, t.created_at, v.registration_number, d.driver_name AS full_name FROM trips t JOIN vehicles v ON v.id = t.vehicle_id JOIN drivers d ON d.id = t.driver_id ORDER BY t.created_at DESC LIMIT 8');
        $alerts = Database::fetchAll('SELECT message FROM notifications ORDER BY created_at DESC LIMIT 10');

        return Response::html(View::render('dashboard.index', [
            'title' => 'Dashboard',
            'user' => Auth::user(),
            'stats' => $stats,
            'recentTrips' => $recentTrips,
            'alerts' => $alerts,
        ]));
    }
}
