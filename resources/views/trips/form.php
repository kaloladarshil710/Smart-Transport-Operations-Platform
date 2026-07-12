<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Trip Form') ?></title>
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
            <h1>New Trip</h1>
        </header>
        <div class="panel">
            <form method="post" action="/trips">
                <div class="form-grid">
                    <label>Source</label><input name="source" required>
                    <label>Destination</label><input name="destination" required>
                    <label>Vehicle</label><select name="vehicle_id">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?= App\Core\View::escape((string) $vehicle['id']) ?>"><?= App\Core\View::escape($vehicle['registration_number'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Driver</label><select name="driver_id">
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?= App\Core\View::escape((string) $driver['id']) ?>"><?= App\Core\View::escape($driver['full_name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Cargo Weight</label><input type="number" name="cargo_weight" required>
                    <label>Distance</label><input type="number" name="distance" required>
                    <label>Revenue</label><input type="number" step="0.01" name="revenue" required>
                    <label>Start Odometer</label><input type="number" name="start_odometer" required>
                    <label>Fuel Used</label><input type="number" step="0.01" name="fuel_used" required>
                </div>
                <button type="submit">Save Trip</button>
            </form>
        </div>
    </main>
</body>
</html>
