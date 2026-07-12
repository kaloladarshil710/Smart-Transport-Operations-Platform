<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Trips') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
    <aside class="sidebar">
        <h2>TransitOps</h2>
        <nav>
            <a href="/dashboard">Dashboard</a>
            <a href="/vehicles">Vehicles</a>
            <a href="/drivers">Drivers</a>
            <a href="/trips" class="active">Trips</a>
            <a href="/reports">Reports</a>
            <a href="/logout">Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <h1>Trips</h1>
            <a href="/trips/create" class="button">New Trip</a>
        </header>
        <div class="panel">
            <table>
                <thead><tr><th>Route</th><th>Vehicle</th><th>Driver</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                        <tr>
                            <td><?= App\Core\View::escape($trip['source'] ?? '') ?> → <?= App\Core\View::escape($trip['destination'] ?? '') ?></td>
                            <td><?= App\Core\View::escape($trip['registration_number'] ?? '') ?></td>
                            <td><?= App\Core\View::escape($trip['full_name'] ?? '') ?></td>
                            <td><?= App\Core\View::escape($trip['status'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
