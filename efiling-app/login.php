<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('/admin/dashboard.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $identifier = login_identifier($email);

    if (is_locked_out($identifier)) {
        $error = 'Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.';
    } elseif ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            clear_attempts($identifier);
            login_user($user);
            log_activity((int)$user['id'], 'login', 'Login berhasil');
            redirect('/admin/dashboard.php');
        } else {
            record_failed_attempt($identifier);
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/homepage.css">
</head>
<body class="login-body">
<div class="login-card" data-testid="login-card">
  <div class="login-brand">
    <span class="login-brand-mark">BMG</span>
    <h1>Masuk E-Filing</h1>
    <p>Barito Media Group &mdash; Arsip Digital</p>
  </div>
  <?php if ($error): ?><div class="alert-error" data-testid="login-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-testid="login-form">
    <?= csrf_field() ?>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required autofocus data-testid="login-email-input">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required data-testid="login-password-input">
    <button type="submit" data-testid="login-submit-btn">Masuk</button>
  </form>
  <a class="login-back" href="/" data-testid="login-back-home">&larr; Kembali ke Beranda</a>
</div>
</body>
</html>
