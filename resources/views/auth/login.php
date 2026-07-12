<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Core\View::escape($title ?? 'Login') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <div class="auth-card">
            <h1>TransitOps</h1>
            <p>Secure fleet operations platform</p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= App\Core\View::escape($error) ?></div>
            <?php endif; ?>
            <form method="post" action="/login">
                <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf ?? '') ?>">
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <button type="submit">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
