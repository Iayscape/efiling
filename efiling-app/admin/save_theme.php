<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $themeId = trim($_POST['theme_id'] ?? '');
    $themeVars = $_POST['theme_vars'] ?? '';
    json_decode($themeVars, true);
    if ($themeId !== '' && json_last_error() === JSON_ERROR_NONE) {
        db()->prepare('UPDATE users SET theme_id = ?, theme_vars = ? WHERE id = ?')
            ->execute([$themeId, $themeVars, $u['id']]);
        setcookie('theme_vars', $themeVars, time() + 31536000, '/');
    }
}
echo 'ok';
