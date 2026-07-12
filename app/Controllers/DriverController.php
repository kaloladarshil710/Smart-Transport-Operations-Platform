<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Core\View;
use App\Support\Auth;

final class DriverController
{
    public function index(): Response
    {
        Auth::requireLogin();
        $drivers = Database::fetchAll('SELECT * FROM drivers ORDER BY created_at DESC');

        return Response::html(View::render('drivers.index', ['title' => 'Drivers', 'drivers' => $drivers, 'user' => Auth::user()]));
    }

    public function create(): Response
    {
        Auth::requireLogin();

        return Response::html(View::render('drivers.form', ['title' => 'New Driver', 'driver' => null, 'user' => Auth::user()]));
    }

    public function store(): Response
    {
        Auth::requireLogin();
        $data = [
            trim((string) ($_POST['full_name'] ?? '')),
            trim((string) ($_POST['license_number'] ?? '')),
            trim((string) ($_POST['license_category'] ?? '')),
            trim((string) ($_POST['license_expiry'] ?? date('Y-m-d'))),
            (float) ($_POST['experience_years'] ?? 0),
            trim((string) ($_POST['phone'] ?? '')),
            trim((string) ($_POST['email'] ?? '')),
            trim((string) ($_POST['address'] ?? '')),
            trim((string) ($_POST['emergency_contact'] ?? '')),
            (float) ($_POST['safety_score'] ?? 0),
            trim((string) ($_POST['status'] ?? 'Available')),
            1,
            1,
        ];

        Database::execute('INSERT INTO drivers (driver_name, license_number, license_category, license_expiry_date, experience_years, phone, email, address, emergency_contact, safety_score, status, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $data);

        return Response::redirect('/drivers');
    }
}
