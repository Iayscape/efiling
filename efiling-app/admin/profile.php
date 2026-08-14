<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '') {
        $error = 'Nama tidak boleh kosong.';
    } elseif ($password !== '' && strlen($password) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } else {
        if ($password !== '') {
            db()->prepare('UPDATE users SET name=?, password_hash=? WHERE id=?')
                ->execute([$name, password_hash($password, PASSWORD_BCRYPT), $u['id']]);
        } else {
            db()->prepare('UPDATE users SET name=? WHERE id=?')->execute([$name, $u['id']]);
        }
        log_activity((int)$u['id'], 'update_profile', 'Profil diperbarui');
        flash_set('success', 'Profil berhasil diperbarui.');
        redirect('/admin/profile.php');
    }
}

$pageTitle = 'Profil Saya';
$activeNav = 'profile';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Profil Saya</h1></div>
<div class="card" style="max-width:480px">
  <?php if ($error): ?><div class="alert alert-error" data-testid="profile-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-testid="profile-form">
    <?= csrf_field() ?>
    <div class="field"><label>Nama</label><input type="text" name="name" value="<?= e($u['name']) ?>" required data-testid="profile-name-input"></div>
    <div class="field"><label>Email</label><input type="email" value="<?= e($u['email']) ?>" disabled></div>
    <div class="field"><label>Password Baru (opsional, min. 8 karakter)</label><input type="password" name="password" minlength="8" data-testid="profile-password-input"></div>
    <button class="btn btn-primary" type="submit" data-testid="profile-save-btn">Simpan</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
