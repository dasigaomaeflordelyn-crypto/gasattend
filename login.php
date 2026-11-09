<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';
if (logged_in()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    try {
        $stmt = db()->prepare('SELECT id, username, password_hash, full_name, role FROM users WHERE username = ? AND active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php'); exit;
        }
        $error = 'Incorrect username or password.';
    } catch (Throwable $e) {
        $error = 'Cannot connect to the database. Check setup instructions and config.php.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login | GASATTEND</title><link rel="stylesheet" href="style.css"></head>
<body class="auth-body"><main class="auth-card">
<div class="brand-mark">GA</div><h1>Welcome to GASATTEND</h1><p class="muted">Employee attendance monitoring and reporting</p>
<?php if ($error): ?><p class="alert"><?=h($error)?></p><?php endif; ?>
<form method="post" class="stack">
<input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
<label>Username<input name="username" required autocomplete="username" autofocus></label>
<label>Password<input type="password" name="password" required autocomplete="current-password"></label>
<button class="btn primary" type="submit">Log in</button>
</form><p class="muted small">First time? Complete the setup steps in README.txt.</p>
</main></body></html>
