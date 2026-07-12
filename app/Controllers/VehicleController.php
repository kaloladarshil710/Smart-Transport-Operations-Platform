<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Core\View;
use App\Support\Auth;

final class VehicleController
{
    public function index(): Response
    {
        Auth::requireLogin();
        $vehicles = Database::fetchAll('SELECT * FROM vehicles ORDER BY created_at DESC');

        return Response::html(View::render('vehicles.index', ['title' => 'Vehicles', 'vehicles' => $vehicles, 'user' => Auth::user()]));
    }

    public function create(): Response
    {
        Auth::requireLogin();

        return Response::html(View::render('vehicles.form', ['title' => 'New Vehicle', 'vehicle' => null, 'user' => Auth::user()]));
    }

    public function store(): Response
    {
        Auth::requireLogin();
        $data = [
            trim((string) ($_POST['registration_number'] ?? '')),
            trim((string) ($_POST['vehicle_name'] ?? '')),
            trim((string) ($_POST['model'] ?? '')),
            trim((string) ($_POST['manufacturer'] ?? '')),
            trim((string) ($_POST['vehicle_type'] ?? '')),
            trim((string) ($_POST['fuel_type'] ?? 'CNG')),
            (float) ($_POST['capacity'] ?? 0),
            trim((string) ($_POST['purchase_date'] ?? date('Y-m-d'))),
            (float) ($_POST['purchase_cost'] ?? 0),
            trim((string) ($_POST['insurance_expiry'] ?? date('Y-m-d'))),
            trim((string) ($_POST['fitness_expiry'] ?? date('Y-m-d'))),
            trim((string) ($_POST['pollution_expiry'] ?? date('Y-m-d'))),
            (float) ($_POST['odometer'] ?? 0),
            trim((string) ($_POST['status'] ?? 'Available')),
            1,
            1,
        ];

        Database::execute('INSERT INTO vehicles (registration_number, vehicle_name, model, manufacturer, vehicle_type, fuel_type, load_capacity, purchase_date, purchase_cost, insurance_expiry, fitness_expiry, pollution_expiry, odometer, status, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $data);

        return Response::redirect('/vehicles');
    }
}
