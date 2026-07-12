<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Core\View;
use App\Support\Auth;

final class ReportController
{
    public function index(): Response
    {
        Auth::requireLogin();
        $reports = [
            'vehicle' => Database::fetchAll('SELECT * FROM vehicles ORDER BY created_at DESC'),
            'driver' => Database::fetchAll('SELECT * FROM drivers ORDER BY created_at DESC'),
            'trip' => Database::fetchAll('SELECT * FROM trips ORDER BY created_at DESC'),
            'expense' => Database::fetchAll('SELECT * FROM expenses ORDER BY created_at DESC'),
            'fuel' => Database::fetchAll('SELECT * FROM fuel_logs ORDER BY created_at DESC'),
            'maintenance' => Database::fetchAll('SELECT * FROM maintenance ORDER BY created_at DESC'),
        ];

        return Response::html(View::render('reports.index', ['title' => 'Reports', 'reports' => $reports, 'user' => Auth::user()]));
    }
}
