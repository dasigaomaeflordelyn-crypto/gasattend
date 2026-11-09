<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';

$error = '';
$done = false;
try {
    $count = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        $done = true;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $name = trim((string)($_POST['full_name'] ?? ''));
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if ($name === '' || $username === '' || strlen($password) < 10) {
            $error = 'Enter a name, username, and password of at least 10 characters.';
        } else {
            $stmt = db()->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $name, 'admin']);
            $done = true;
        }
    }
} catch (Throwable $e) {
    $error = 'Could not connect to the database. Import database.sql and check config.php.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>GASATTEND Setup</title><link rel="stylesheet" href="style.css"></head>
<body class="auth-body"><main class="auth-card">
<div class="brand-mark">GA</div><h1>GASATTEND setup</h1>
<?php if ($done): ?>
<p class="notice">An account already exists, or setup is complete. Continue to <a href="login.php">login</a>.</p>
<p class="muted">For security, delete <code>setup_admin.php</code> after creating the first account.</p>
<?php else: ?>
<p class="muted">Create the first administrator account. This page works only before any user account exists.</p>
<?php if ($error): ?><p class="alert"><?=h($error)?></p><?php endif; ?>
<form method="post" class="stack">
<input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
<label>Full name<input name="full_name" required maxlength="120"></label>
<label>Username<input name="username" required maxlength="60" autocomplete="username"></label>
<label>Password (minimum 10 characters)<input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
<button class="btn primary" type="submit">Create administrator</button>
</form>
<?php endif; ?>
</main></body></html>
