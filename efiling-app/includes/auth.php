<?php

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    $cache = $u ?: null;
    return $cache;
}

function require_login(): array {
    $u = current_user();
    if (!$u) {
        redirect('/login.php');
    }
    return $u;
}

function require_role(array $roles): array {
    $u = require_login();
    if (!in_array($u['role'], $roles, true)) {
        http_response_code(403);
        die('Anda tidak memiliki akses ke halaman ini.');
    }
    return $u;
}

function login_identifier(string $email): string {
    return ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ':' . mb_strtolower($email);
}

function is_locked_out(string $identifier): bool {
    $stmt = db()->prepare('SELECT locked_until FROM login_attempts WHERE identifier = ?');
    $stmt->execute([$identifier]);
    $row = $stmt->fetch();
    return $row && $row['locked_until'] && strtotime($row['locked_until']) > time();
}

function record_failed_attempt(string $identifier): void {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT attempts FROM login_attempts WHERE identifier = ?');
    $stmt->execute([$identifier]);
    $row = $stmt->fetch();
    if ($row) {
        $attempts = (int)$row['attempts'] + 1;
        $locked = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
        $pdo->prepare('UPDATE login_attempts SET attempts = ?, locked_until = ?, updated_at = NOW() WHERE identifier = ?')
            ->execute([$attempts, $locked, $identifier]);
    } else {
        $pdo->prepare('INSERT INTO login_attempts (identifier, attempts, updated_at) VALUES (?, 1, NOW())')->execute([$identifier]);
    }
}

function clear_attempts(string $identifier): void {
    db()->prepare('DELETE FROM login_attempts WHERE identifier = ?')->execute([$identifier]);
}

function log_activity(?int $user_id, string $action, string $desc = ''): void {
    db()->prepare('INSERT INTO activity_log (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())')
        ->execute([$user_id, $action, $desc, $_SERVER['REMOTE_ADDR'] ?? '']);
}
