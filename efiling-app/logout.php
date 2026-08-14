<?php
require_once __DIR__ . '/includes/bootstrap.php';
$u = current_user();
if ($u) {
    log_activity((int)$u['id'], 'logout', 'Logout');
}
logout_user();
redirect('/login.php');
