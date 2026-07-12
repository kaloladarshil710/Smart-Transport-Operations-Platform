<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Vehicle Form') ?></title>
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
            <h1>New Vehicle</h1>
        </header>
        <div class="panel">
            <form method="post" action="/vehicles">
                <div class="form-grid">
                    <label>Registration</label><input name="registration_number" required>
                    <label>Name</label><input name="vehicle_name" required>
                    <label>Model</label><input name="model" required>
                    <label>Manufacturer</label><input name="manufacturer" required>
                    <label>Vehicle Type</label><input name="vehicle_type" required>
                    <label>Capacity</label><input type="number" name="capacity" required>
                    <label>Odometer</label><input type="number" name="odometer" required>
                    <label>Purchase Cost</label><input type="number" step="0.01" name="purchase_cost" required>
                    <label>Purchase Date</label><input type="date" name="purchase_date" required>
                    <label>Insurance Expiry</label><input type="date" name="insurance_expiry" required>
                    <label>Fitness Expiry</label><input type="date" name="fitness_expiry" required>
                    <label>RC Expiry</label><input type="date" name="rc_expiry" required>
                    <label>Status</label><select name="status"><option>Available</option><option>Maintenance</option><option>Retired</option></select>
                </div>
                <button type="submit">Save Vehicle</button>
            </form>
        </div>
    </main>
</body>
</html>
