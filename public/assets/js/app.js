// Utilitaires communs
function csrfToken() {
  return document.querySelector('meta[name=csrf]').content;
}

async function post(path, data) {
  const body = new FormData();
  Object.entries(data).forEach(([k, v]) => body.append(k, v));
  body.append('csrf', csrfToken());
  const res = await fetch(LF_BASE + path, { method: 'POST', body });
  const json = await res.json().catch(() => ({}));
  if (!res.ok && !json.error) json.error = 'HTTP ' + res.status;
  return json;
}

// Theme sombre (persiste en cookie)
const themeBtn = document.getElementById('theme-toggle');
if (themeBtn) {
  themeBtn.addEventListener('click', () => {
    const html = document.documentElement;
    const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
    html.dataset.theme = next;
    document.cookie = 'lf_theme=' + next + ';path=/;max-age=31536000;samesite=lax';
    location.reload();
  });
}

// Selection groupee + actions bulk (page Prospects)
const checkAll = document.getElementById('check-all');
if (checkAll) {
  const rows = () => Array.from(document.querySelectorAll('.row-check'));
  const updateCount = () => {
    const n = rows().filter(c => c.checked).length;
    const el = document.getElementById('bulk-count');
    if (el) el.textContent = n ? n + ' ' + LF_T.selected_count : '';
  };
  checkAll.addEventListener('change', () => rows().forEach(c => (c.checked = checkAll.checked)));
  document.addEventListener('change', e => { if (e.target.classList.contains('row-check')) updateCount(); });

  const ids = () => rows().filter(c => c.checked).map(c => c.value);

  document.getElementById('bulk-status-btn')?.addEventListener('click', async () => {
    const status = document.getElementById('bulk-status').value;
    if (!status || ids().length === 0) return alert(LF_T.none_selected);
    const r = await post('/api/prospect/bulk', { action: status, 'ids[]': ids() });
    if (r.error) return alert(r.error);
    location.reload();
  });

  document.getElementById('bulk-delete-btn')?.addEventListener('click', async () => {
    if (ids().length === 0) return alert(LF_T.none_selected);
    if (!confirm(LF_T.confirm_bulk_delete)) return;
    const r = await post('/api/prospect/bulk', { action: 'delete', 'ids[]': ids() });
    if (r.error) return alert(r.error);
    location.reload();
  });
}

// Export : "tout selectionner" des colonnes
const colMaster = document.querySelector('.col-check');
if (colMaster) {
  colMaster.addEventListener('change', () =>
    document.querySelectorAll('.col-item').forEach(c => (c.checked = colMaster.checked)));
}
