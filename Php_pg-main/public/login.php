<?php
// public/login.php
require_once __DIR__ . '/../src/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (try_login($username, $password)) {
        // go back where user wanted or home
        $dest = $_SESSION['after_login'] ?? '/index.php';
        unset($_SESSION['after_login']);
        header('Location: ' . $dest);
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="/assets/app.css">
  <style>
    body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #0f172a; color: #e2e8f0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
    .card { background: #111827; padding: 28px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.25); width: 100%; max-width: 420px; }
    label { display:block; margin: 12px 0 6px; font-weight: 600; }
    input { width:100%; padding:12px 14px; border-radius:10px; border:1px solid #374151; background:#0b1220; color:#e5e7eb; }
    button { margin-top:16px; width:100%; padding:12px 14px; border-radius:10px; border:0; background:#2563eb; color:white; font-weight:700; cursor: pointer; }
    .error { background:#7f1d1d; color:#fecaca; padding:10px 12px; border-radius:8px; margin:8px 0 0; }
    .hint { margin-top:12px; font-size:12px; color:#9ca3af; }
    .brand { font-size: 22px; font-weight: 800; letter-spacing: .3px; margin-bottom: 8px; }
  </style>
</head>
<body>
  <form method="post" class="card" autocomplete="on">
    <div class="brand">Proposal Admin</div>
    <h1 style="margin:0 0 16px; font-size: 18px;">Sign in</h1>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <label for="username">Username</label>
    <input id="username" name="username" autocomplete="username" required>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <button type="submit">Sign in</button>
    
  </form>
</body>
</html>
