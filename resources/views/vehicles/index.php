<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Vehicles') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
    <aside class="sidebar">
        <h2>TransitOps</h2>
        <nav>
            <a href="/dashboard">Dashboard</a>
            <a href="/vehicles" class="active">Vehicles</a>
            <a href="/drivers">Drivers</a>
            <a href="/trips">Trips</a>
            <a href="/reports">Reports</a>
            <a href="/logout">Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <h1>Vehicles</h1>
            <a href="/vehicles/create" class="button">New Vehicle</a>
        </header>
        <div class="panel">
            <table>
                <thead><tr><th>Registration</th><th>Name</th><th>Status</th><th>Capacity</th></tr></thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><?= App\Core\View::escape($vehicle['registration_number'] ?? '') ?></td>
                            <td><?= App\Core\View::escape($vehicle['vehicle_name'] ?? '') ?></td>
                            <td><?= App\Core\View::escape($vehicle['status'] ?? '') ?></td>
                            <td><?= App\Core\View::escape((string) ($vehicle['load_capacity'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
