<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Driver Form') ?></title>
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
            <h1>New Driver</h1>
        </header>
        <div class="panel">
            <form method="post" action="/drivers">
                <div class="form-grid">
                    <label>Name</label><input name="full_name" required>
                    <label>License Number</label><input name="license_number" required>
                    <label>License Category</label><input name="license_category" required>
                    <label>License Expiry</label><input type="date" name="license_expiry" required>
                    <label>Phone</label><input name="phone" required>
                    <label>Address</label><input name="address" required>
                    <label>Emergency Contact</label><input name="emergency_contact" required>
                    <label>Experience Years</label><input type="number" name="experience_years" required>
                    <label>Safety Score</label><input type="number" name="safety_score" required>
                    <label>Status</label><select name="status"><option>Available</option><option>On Trip</option><option>Suspended</option></select>
                </div>
                <button type="submit">Save Driver</button>
            </form>
        </div>
    </main>
</body>
</html>
