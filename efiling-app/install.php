<?php
declare(strict_types=1);
session_start();

$configPath = __DIR__ . '/config.php';
$step = 'form';
$error = null;

if (file_exists($configPath) && !isset($_GET['force'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $adminName = trim($_POST['admin_name'] ?? 'Administrator');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';

    if ($dbHost === '' || $dbName === '' || $dbUser === '' || $adminEmail === '' || strlen($adminPass) < 8) {
        $error = 'Lengkapi semua data. Password admin minimal 8 karakter.';
    } else {
        try {
            $pdo = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8mb4', $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
            $pdo->exec($schema);

            $existing = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $existing->execute([$adminEmail]);
            if (!$existing->fetch()) {
                $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)')
                    ->execute([$adminName, $adminEmail, $hash, 'admin']);
            }

            $configContent = "<?php\n" .
                "define('DB_HOST', " . var_export($dbHost, true) . ");\n" .
                "define('DB_NAME', " . var_export($dbName, true) . ");\n" .
                "define('DB_USER', " . var_export($dbUser, true) . ");\n" .
                "define('DB_PASS', " . var_export($dbPass, true) . ");\n" .
                "define('APP_NAME', 'Barito Media Group - E-Filing');\n" .
                "define('APP_ENV', 'production');\n";
            file_put_contents($configPath, $configContent);

            $step = 'done';
        } catch (Throwable $ex) {
            $error = 'Gagal terhubung/menyiapkan database: ' . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Instalasi E-Filing Barito Media Group</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:'Segoe UI',Arial,sans-serif;background:#0f172a;color:#e2e8f0;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;padding:2rem}
.box{background:#141c2e;border:1px solid #253048;border-radius:12px;padding:2.5rem;max-width:480px;width:100%}
h1{font-size:1.4rem;margin-bottom:.25rem}
p.sub{color:#94a3b8;margin-top:0;margin-bottom:1.5rem;font-size:.9rem}
label{display:block;font-size:.8rem;color:#94a3b8;margin:1rem 0 .3rem}
input{width:100%;padding:.65rem .8rem;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#e2e8f0;box-sizing:border-box}
button{margin-top:1.5rem;width:100%;padding:.75rem;border-radius:6px;border:none;background:#2563eb;color:#fff;font-weight:600;cursor:pointer}
.error{background:#450a0a;border:1px solid #7f1d1d;color:#fecaca;padding:.75rem;border-radius:6px;margin-bottom:1rem;font-size:.85rem}
.success{background:#052e16;border:1px solid #166534;color:#bbf7d0;padding:1rem;border-radius:6px;font-size:.9rem}
a{color:#60a5fa}
fieldset{border:1px solid #253048;border-radius:8px;margin-top:1rem}
legend{padding:0 .5rem;color:#94a3b8;font-size:.8rem}
</style>
</head>
<body>
<div class="box">
<h1>Instalasi Awal</h1>
<p class="sub">Isi data database (buat lebih dulu di phpMyAdmin/cPanel) dan akun admin pertama.</p>
<?php if ($error): ?><div class="error" data-testid="install-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($step === 'done'): ?>
  <div class="success" data-testid="install-success">
    Instalasi berhasil! Database & tabel sudah dibuat, akun admin sudah aktif.<br><br>
    <a href="/login.php" data-testid="install-login-link">Masuk ke halaman Login &rarr;</a>
  </div>
<?php else: ?>
<form method="post" data-testid="install-form">
  <fieldset>
    <legend>Database</legend>
    <label>Host Database</label>
    <input type="text" name="db_host" value="localhost" required data-testid="install-db-host">
    <label>Nama Database</label>
    <input type="text" name="db_name" required data-testid="install-db-name">
    <label>User Database</label>
    <input type="text" name="db_user" required data-testid="install-db-user">
    <label>Password Database</label>
    <input type="password" name="db_pass" data-testid="install-db-pass">
  </fieldset>
  <fieldset>
    <legend>Akun Admin</legend>
    <label>Nama Lengkap</label>
    <input type="text" name="admin_name" required data-testid="install-admin-name">
    <label>Email Login</label>
    <input type="email" name="admin_email" required data-testid="install-admin-email">
    <label>Password (min. 8 karakter)</label>
    <input type="password" name="admin_pass" minlength="8" required data-testid="install-admin-pass">
  </fieldset>
  <button type="submit" data-testid="install-submit-btn">Pasang Aplikasi</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
