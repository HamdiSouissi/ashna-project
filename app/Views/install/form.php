<?php use App\Core\Csrf; ?>
<div class="auth-card card">
  <h1 class="auth-title">⚙️ <?= e(t('install_title')) ?></h1>
  <p class="muted"><?= e(t('install_desc')) ?></p>
  <form method="post" action="<?= e(url('/install')) ?>">
    <?= Csrf::field() ?>
    <label><?= e(t('install_nom')) ?>
      <input type="text" name="nom" required autofocus>
    </label>
    <label><?= e(t('email')) ?>
      <input type="email" name="email" required>
    </label>
    <label><?= e(t('password')) ?> (≥ 8)
      <input type="password" name="password" minlength="8" required>
    </label>
    <button type="submit" class="btn btn-primary btn-block"><?= e(t('install_create')) ?></button>
  </form>
</div>
