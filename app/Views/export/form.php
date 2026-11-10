<?php use App\Core\Csrf; ?>
<div class="card">
  <h1>⬇ <?= e(t('export_csv')) ?></h1>
  <p class="muted"><?= e(t('export_hint')) ?></p>
  <form method="post" action="<?= e(url('/export')) ?>">
    <?= Csrf::field() ?>
    <input type="hidden" name="filters" value="<?= e($query) ?>">
    <h2><?= e(t('choose_columns')) ?></h2>
    <div class="columns-grid">
      <label class="check"><input type="checkbox" class="col-check" checked> <strong><?= e(t('select_all')) ?></strong></label>
      <?php foreach ($columns as $col => $label): ?>
        <label class="check">
          <input type="checkbox" name="columns[<?= e($col) ?>]" value="1" class="col-item" checked> <?= e($label) ?>
        </label>
      <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary">⬇ <?= e(t('export')) ?></button>
  </form>
</div>
