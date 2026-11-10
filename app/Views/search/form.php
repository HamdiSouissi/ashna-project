<?php use App\Core\Csrf; ?>
<div class="card">
  <h1>🔎 <?= e(t('search')) ?></h1>
  <form id="search-form">
    <?= Csrf::field() ?>
    <div class="form-row">
      <label class="grow"><?= e(t('city_or_zone')) ?>
        <input type="text" name="ville" id="ville-input" value="<?= e($prefillVille) ?>" required autocomplete="off" placeholder="Tunis…">
        <datalist id="ville-suggestions"></datalist>
      </label>
      <label><?= e(t('category')) ?>
        <select name="categorie" required>
          <?php foreach ($categories as $key => $cat): ?>
            <option value="<?= e($key) ?>" <?= $prefillCat === $key ? 'selected' : '' ?>><?= e($cat['icon'] . ' ' . $cat['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label class="check">
      <input type="checkbox" name="include_social" value="1">
      <?= e(t('include_social_only')) ?>
    </label>
    <button type="submit" class="btn btn-primary" id="search-btn">▶ <?= e(t('search_btn')) ?></button>
    <p class="muted small"><?= e(t('api_courtesy_note')) ?></p>
  </form>
</div>

<div id="search-progress-wrap" class="card hidden">
  <div class="progress-head">
    <span id="progress-label"><?= e(t('searching')) ?></span>
    <span id="progress-count"></span>
  </div>
  <div class="progress"><div class="progress-fill" id="progress-fill"></div></div>
  <div id="search-error" class="flash flash-error hidden"></div>
</div>

<div id="search-live" class="card hidden">
  <h2 id="live-title"><?= e(t('searching')) ?></h2>
  <div class="table-wrap">
    <table class="table">
      <thead><tr>
        <th><?= e(t('name')) ?></th><th><?= e(t('phone')) ?></th><th><?= e(t('address')) ?></th>
        <th><?= e(t('status')) ?></th><th><?= e(t('score')) ?></th>
      </tr></thead>
      <tbody id="live-rows"></tbody>
    </table>
  </div>
</div>

<div id="search-done" class="card hidden">
  <p>✅ <?= e(t('done_searching')) ?> — <span id="done-count"></span></p>
  <a class="btn btn-primary" id="results-link" href="#"><?= e(t('view_results')) ?></a>
  <a class="btn" href="<?= e(url('/map')) ?>"><?= e(t('view_map')) ?></a>
</div>
