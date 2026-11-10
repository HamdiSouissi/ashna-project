<?php
use App\Core\Auth;
use App\Core\Lang;

$nav = [
    ['/',            '📊', t('dashboard')],
    ['/search',      '🔎', t('search')],
    ['/prospects',   '📋', t('prospects')],
    ['/map',         '🗺️', t('map')],
    ['/pipeline',    '📊', t('pipeline')],
    ['/history',     '🕘', t('history')],
];
$cur = current_path();
?>
<aside class="sidebar" id="sidebar">
  <div class="brand">🎯 <span><?= e(t('app_name')) ?></span></div>
  <nav>
    <?php foreach ($nav as [$path, $icon, $label]): ?>
      <a href="<?= e(url($path)) ?>" class="<?= str_starts_with($cur, $path === '/' ? '/' : $path) && ($path !== '/' || $cur === '/') ? 'active' : '' ?>">
        <span class="ico"><?= e($icon) ?></span> <?= e($label) ?>
      </a>
    <?php endforeach; ?>
    <?php if (Auth::isAdmin()): ?>
      <a href="<?= e(url('/settings')) ?>" class="<?= str_starts_with($cur, '/settings') ? 'active' : '' ?>">
        <span class="ico">⚙️</span> <?= e(t('settings')) ?>
      </a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= e(url('/lang/' . (Lang::current() === 'fr' ? 'ar' : 'fr'))) ?>">🌐 <?= e(t('lang_switch')) ?></a>
    <a href="<?= e(url('/logout')) ?>">↩︎ <?= e(t('logout')) ?></a>
    <button type="button" id="theme-toggle" class="link-btn">
      <?= $theme === 'dark' ? '☀️ ' . e(t('light_mode')) : '🌙 ' . e(t('dark_mode')) ?>
    </button>
  </div>
</aside>
