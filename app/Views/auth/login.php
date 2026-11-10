<?php use App\Core\Csrf; use App\Models\User; ?>
<div class="auth-card card">
  <h1 class="auth-title">🎯 <?= e(t('app_name')) ?></h1>
  <form method="post" action="<?= e(url('/login')) ?>">
    <?= Csrf::field() ?>
    <label><?= e(t('email')) ?>
      <input type="email" name="email" required autofocus autocomplete="username">
    </label>
    <label><?= e(t('password')) ?>
      <input type="password" name="password" required autocomplete="current-password">
    </label>
    <button type="submit" class="btn btn-primary btn-block"><?= e(t('sign_in')) ?></button>
  </form>
  <div class="auth-links">
    <a href="<?= e(url('/lang/' . (\App\Core\Lang::current() === 'fr' ? 'ar' : 'fr'))) ?>">🌐 <?= e(t('lang_switch')) ?></a>
    <?php if (User::count() === 0): ?>
      <a href="<?= e(url('/install')) ?>">⚙️ <?= e(t('install_title')) ?></a>
    <?php endif; ?>
  </div>
</div>
