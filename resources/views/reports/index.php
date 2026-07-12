<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Reports') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
    <aside class="sidebar">
        <h2>TransitOps</h2>
        <nav>
            <a href="/dashboard">Dashboard</a>
            <a href="/vehicles">Vehicles</a>
            <a href="/drivers">Drivers</a>
            <a href="/trips">Trips</a>
            <a href="/reports" class="active">Reports</a>
            <a href="/logout">Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <h1>Reports</h1>
        </header>
        <div class="panel">
            <ul>
                <li>Vehicles: <?= App\Core\View::escape((string) count($reports['vehicle'] ?? [])) ?></li>
                <li>Drivers: <?= App\Core\View::escape((string) count($reports['driver'] ?? [])) ?></li>
                <li>Trips: <?= App\Core\View::escape((string) count($reports['trip'] ?? [])) ?></li>
                <li>Expenses: <?= App\Core\View::escape((string) count($reports['expense'] ?? [])) ?></li>
                <li>Fuel Logs: <?= App\Core\View::escape((string) count($reports['fuel'] ?? [])) ?></li>
                <li>Maintenance: <?= App\Core\View::escape((string) count($reports['maintenance'] ?? [])) ?></li>
            </ul>
        </div>
    </main>
</body>
</html>
