<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
    <aside class="sidebar">
        <h2>TransitOps</h2>
        <nav>
            <a href="/dashboard" class="active">Dashboard</a>
            <a href="/vehicles">Vehicles</a>
            <a href="/drivers">Drivers</a>
            <a href="/trips">Trips</a>
            <a href="/reports">Reports</a>
            <a href="/logout">Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <h1>Fleet Operations Dashboard</h1>
            <div>Welcome, <?= App\Core\View::escape($user['name'] ?? 'User') ?></div>
        </header>
        <section class="kpi-grid">
            <?php foreach ($stats as $label => $value): ?>
                <div class="card">
                    <h3><?= App\Core\View::escape((string) $label) ?></h3>
                    <p><?= App\Core\View::escape((string) $value) ?></p>
                </div>
            <?php endforeach; ?>
        </section>
        <section class="panel-grid">
            <div class="panel">
                <h3>Recent Trips</h3>
                <ul>
                    <?php foreach ($recentTrips as $trip): ?>
                        <li><?= App\Core\View::escape($trip['source'] ?? '') ?> → <?= App\Core\View::escape($trip['destination'] ?? '') ?> · <?= App\Core\View::escape($trip['registration_number'] ?? '') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="panel">
                <h3>Notifications</h3>
                <ul>
                    <?php foreach ($alerts as $alert): ?>
                        <li><?= App\Core\View::escape($alert['message'] ?? '') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </main>
</body>
</html>
