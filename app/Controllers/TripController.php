<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Core\View;
use App\Support\Auth;

final class TripController
{
    public function index(): Response
    {
        Auth::requireLogin();
        $trips = Database::fetchAll('SELECT t.*, v.registration_number, d.full_name FROM trips t JOIN vehicles v ON v.id = t.vehicle_id JOIN drivers d ON d.id = t.driver_id ORDER BY t.created_at DESC');

        return Response::html(View::render('trips.index', ['title' => 'Trips', 'trips' => $trips, 'user' => Auth::user()]));
    }

    public function create(): Response
    {
        Auth::requireLogin();
        $vehicles = Database::fetchAll('SELECT * FROM vehicles WHERE status = "Available"');
        $drivers = Database::fetchAll('SELECT * FROM drivers WHERE status = "Available"');

        return Response::html(View::render('trips.form', ['title' => 'New Trip', 'vehicles' => $vehicles, 'drivers' => $drivers, 'user' => Auth::user()]));
    }

    public function store(): Response
    {
        Auth::requireLogin();
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $driverId = (int) ($_POST['driver_id'] ?? 0);
        $cargoWeight = (float) ($_POST['cargo_weight'] ?? 0);
        $distance = (float) ($_POST['distance'] ?? 0);
        $revenue = (float) ($_POST['revenue'] ?? 0);
        $startOdometer = (int) ($_POST['start_odometer'] ?? 0);
        $fuelUsed = (float) ($_POST['fuel_used'] ?? 0);

        $vehicle = Database::fetchOne('SELECT * FROM vehicles WHERE id = ?', [$vehicleId]);
        $driver = Database::fetchOne('SELECT * FROM drivers WHERE id = ?', [$driverId]);

        if ($vehicle === null || $driver === null) {
            return Response::redirect('/trips/create');
        }

        Database::execute('INSERT INTO trips (trip_number, vehicle_id, driver_id, source, destination, cargo_weight, distance_km, revenue, start_odometer, end_odometer, fuel_used, status, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "Draft", 1, 1)', [
            'TRP-' . date('YmdHis'),
            $vehicleId,
            $driverId,
            trim((string) ($_POST['source'] ?? '')),
            trim((string) ($_POST['destination'] ?? '')),
            $cargoWeight,
            $distance,
            $revenue,
            $startOdometer,
            $startOdometer + $distance,
            $fuelUsed,
        ]);

        Database::execute('UPDATE vehicles SET status = "On Trip" WHERE id = ?', [$vehicleId]);
        Database::execute('UPDATE drivers SET status = "On Trip" WHERE id = ?', [$driverId]);

        return Response::redirect('/trips');
    }
}
