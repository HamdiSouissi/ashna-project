</main>
<script>
const LF_BASE = <?= json_encode(url(''), JSON_UNESCAPED_SLASHES) ?>;
const LF_T = <?= json_encode(\App\Core\Lang::export(), JSON_UNESCAPED_UNICODE) ?>;
const LF_LANG = <?= json_encode(\App\Core\Lang::current()) ?>;
</script>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<?php if (!empty($leaflet)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= e(url('assets/js/map.js')) ?>"></script>
<?php endif; ?>
<?php foreach (($scripts ?? []) as $s): ?>
<script src="<?= e(url($s)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
