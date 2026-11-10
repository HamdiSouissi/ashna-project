<?php
use App\Core\Csrf;
use App\Core\Lang;

if (!defined('LF_CONFIG')) exit;
$standalone = !empty($standalone);
$title = $title ?? t('app_name');
$theme = $_COOKIE['lf_theme'] ?? 'light';
?><!doctype html>
<html lang="<?= e(Lang::current()) ?>" dir="<?= e(Lang::dir()) ?>" data-theme="<?= e($theme) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e(Csrf::token()) ?>">
<title><?= e($title) ?> · <?= e(t('app_name')) ?></title>
<link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
<?php if (!empty($leaflet)): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<?php endif; ?>
</head>
<body class="<?= $standalone ? 'auth-body' : 'app-body' ?>">
<?php if (!$standalone) require dirname(__DIR__) . '/layout/sidebar.php'; ?>
<main class="<?= $standalone ? 'auth-main' : 'main' ?>">
<?php foreach (take_flashes() as $f): ?>
  <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
