<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Drivers') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
    <aside class="sidebar">
        <h2>TransitOps</h2>
        <nav>
            <a href="/dashboard">Dashboard</a>
            <a href="/vehicles">Vehicles</a>
            <a href="/drivers" class="active">Drivers</a>
            <a href="/trips">Trips</a>
            <a href="/reports">Reports</a>
            <a href="/logout">Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <h1>Drivers</h1>
            <a href="/drivers/create" class="button">New Driver</a>
        </header>
        <div class="panel">
            <table>
                <thead><tr><th>Name</th><th>License</th><th>Status</th><th>Safety</th></tr></thead>
                <tbody>
                    <?php foreach ($drivers as $driver): ?>
                        <tr>
                            <td><?= App\Core\View::escape($driver['driver_name'] ?? '') ?></td>
                            <td><?= App\Core\View::escape($driver['license_number'] ?? '') ?></td>
                            <td><?= App\Core\View::escape($driver['status'] ?? '') ?></td>
                            <td><?= App\Core\View::escape((string) ($driver['safety_score'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
