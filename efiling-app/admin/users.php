<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'staff';
        $password = $_POST['password'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        if ($name === '' || $email === '' || !in_array($role, ['admin', 'staff'], true)) {
            flash_set('error', 'Lengkapi data dengan benar.');
        } elseif ($id === 0 && strlen($password) < 8) {
            flash_set('error', 'Password minimal 8 karakter untuk pengguna baru.');
        } else {
            if ($id > 0) {
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        flash_set('error', 'Password minimal 8 karakter.');
                        redirect('/admin/users.php');
                    }
                    db()->prepare('UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?')
                        ->execute([$name, $email, $role, password_hash($password, PASSWORD_BCRYPT), $id]);
                } else {
                    db()->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?')->execute([$name, $email, $role, $id]);
                }
                log_activity((int)$u['id'], 'update_user', $email);
            } else {
                try {
                    db()->prepare('INSERT INTO users (name,email,role,password_hash) VALUES (?,?,?,?)')
                        ->execute([$name, $email, $role, password_hash($password, PASSWORD_BCRYPT)]);
                    log_activity((int)$u['id'], 'create_user', $email);
                } catch (PDOException $ex) {
                    flash_set('error', 'Email sudah digunakan.');
                    redirect('/admin/users.php');
                }
            }
            flash_set('success', 'Data pengguna tersimpan.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id !== (int)$u['id']) {
            db()->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([$id]);
            flash_set('success', 'Pengguna dinonaktifkan.');
        } else {
            flash_set('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }
    }
    redirect('/admin/users.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRow = $stmt->fetch();
}
$rows = db()->query('SELECT * FROM users WHERE is_active = 1 ORDER BY name ASC')->fetchAll();

$pageTitle = 'Pengguna';
$activeNav = 'users';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Manajemen Pengguna</h1></div>

<div class="card">
  <h3 style="margin-top:0;font-size:1rem"><?= $editRow ? 'Ubah Pengguna' : 'Tambah Pengguna Baru' ?></h3>
  <form method="post" data-testid="user-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($editRow['id'] ?? '') ?>">
    <div class="form-grid">
      <div class="field"><label>Nama</label><input type="text" name="name" required value="<?= e($editRow['name'] ?? '') ?>" data-testid="user-name-input"></div>
      <div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($editRow['email'] ?? '') ?>" data-testid="user-email-input"></div>
      <div class="field"><label>Role</label>
        <select name="role" data-testid="user-role-input">
          <option value="staff" <?= ($editRow['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
          <option value="admin" <?= ($editRow['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
      </div>
      <div class="field"><label>Password <?= $editRow ? '(kosongkan jika tidak diubah)' : '' ?></label><input type="password" name="password" minlength="8" data-testid="user-password-input"></div>
    </div>
    <button class="btn btn-primary" type="submit" data-testid="user-save-btn"><?= $editRow ? 'Simpan Perubahan' : 'Tambah Pengguna' ?></button>
    <?php if ($editRow): ?><a href="/admin/users.php" class="btn" data-testid="user-cancel-edit">Batal</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
  <table>
    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr data-testid="user-row-<?= (int)$r['id'] ?>">
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['email']) ?></td>
        <td><span class="badge-role"><?= e($r['role']) ?></span></td>
        <td>
          <a class="btn btn-sm" href="/admin/users.php?edit=<?= (int)$r['id'] ?>" data-testid="user-edit-<?= (int)$r['id'] ?>">Ubah</a>
          <?php if ((int)$r['id'] !== (int)$u['id']): ?>
          <form method="post" style="display:inline" data-confirm="Nonaktifkan pengguna ini?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit" data-testid="user-delete-<?= (int)$r['id'] ?>">Nonaktifkan</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
