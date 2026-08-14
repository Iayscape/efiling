<?php
$__u = $u ?? current_user();
$__themeVars = null;
if ($__u && !empty($__u['theme_vars'])) {
    $__themeVars = json_decode($__u['theme_vars'], true);
} elseif (!empty($_COOKIE['theme_vars'])) {
    $__themeVars = json_decode($_COOKIE['theme_vars'], true);
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/dashboard.css">
<?php if ($__themeVars && is_array($__themeVars)): ?>
<style>:root{<?php foreach ($__themeVars as $k => $v) { echo e($k) . ':' . e($v) . ';'; } ?>}</style>
<?php endif; ?>
</head>
<body>
<script>window.CSRF_TOKEN = "<?= csrf_token() ?>";</script>
<div class="app-shell">
