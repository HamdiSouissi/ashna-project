<div class="card">
  <h1>🗺️ <?= e(t('map')) ?></h1>
  <div id="map"></div>
  <div class="map-legend">
    <?php foreach (\App\Models\Prospect::STATUTS as $s): ?>
      <span class="legend-item"><i class="dot st-<?= e($s) ?>"></i><?= e(t('st_' . $s)) ?></span>
    <?php endforeach; ?>
  </div>
</div>
