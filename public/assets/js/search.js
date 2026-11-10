(function () {
  const form = document.getElementById('search-form');
  if (!form) return;
  const input = document.getElementById('ville-input');
  const datalist = document.getElementById('ville-suggestions');
  const btn = document.getElementById('search-btn');
  const wrap = document.getElementById('search-progress-wrap');
  const fill = document.getElementById('progress-fill');
  const label = document.getElementById('progress-label');
  const count = document.getElementById('progress-count');
  const errBox = document.getElementById('search-error');
  const live = document.getElementById('search-live');
  const liveRows = document.getElementById('live-rows');
  const done = document.getElementById('search-done');
  const resultsLink = document.getElementById('results-link');

  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  // Suggestions de villes (Nominatim)
  let timer = null;
  input.addEventListener('input', () => {
    clearTimeout(timer);
    if (input.value.trim().length < 3) return;
    timer = setTimeout(async () => {
      const res = await fetch(LF_BASE + '/api/geo/suggest?q=' + encodeURIComponent(input.value.trim()));
      const list = await res.json();
      datalist.innerHTML = list.map(v => '<option value="' + esc(v) + '">').join('');
    }, 400);
  });

  const appendRow = p => {
    liveRows.insertAdjacentHTML('beforeend', `
      <tr>
        <td><a href="${LF_BASE}/prospect/${p.id}"><strong>${esc(p.nom)}</strong></a></td>
        <td>${p.telephone_normalise
          ? `<a href="tel:${esc(p.telephone_normalise)}">${esc(p.telephone_normalise)}</a>
             <a class="wa" target="_blank" rel="noopener" href="https://wa.me/${esc(p.telephone_normalise.replace('+',''))}">💬</a>`
          : '<span class="muted">—</span>'}</td>
        <td>${esc(p.adresse)}<div class="muted small">${esc(p.ville)}</div></td>
        <td><span class="badge st-${esc(p.statut_prospection)}">${LF_T['st_' + p.statut_prospection] || ''}</span></td>
        <td><span class="badge sc-${esc(p.score_confiance)}">${LF_T['sc_' + p.score_confiance] || ''}</span>
            <span class="badge sw-${esc(p.site_web_statut)}">${LF_T['sw_' + p.site_web_statut] || ''}</span></td>
      </tr>`);
  };

  form.addEventListener('submit', async e => {
    e.preventDefault();
    btn.disabled = true;
    wrap.classList.remove('hidden');
    live.classList.remove('hidden');
    done.classList.add('hidden');
    errBox.classList.add('hidden');
    liveRows.innerHTML = '';
    let kept = 0;

    const start = await post('/api/search/start', {
      ville: input.value.trim(),
      categorie: form.categorie.value,
      include_social: form.include_social.checked ? '1' : '',
    });
    if (start.error) {
      errBox.textContent = start.error;
      errBox.classList.remove('hidden');
      btn.disabled = false;
      return;
    }

    let index = 0;
    while (index < start.total) {
      const step = await post('/api/search/step', { token: start.token });
      if (step.error) {
        errBox.textContent = step.error;
        errBox.classList.remove('hidden');
      }
      if (Array.isArray(step.prospects)) {
        step.prospects.forEach(appendRow);
        kept += step.prospects.length;
      }
      index = step.index ?? index + 1;
      const pct = Math.round((index / start.total) * 100);
      fill.style.width = pct + '%';
      count.textContent = index + '/' + start.total + ' (' + LF_T.progress_cells + ') · ' + kept;
      await new Promise(r => setTimeout(r, 250));
      if (step.done) break;
    }

    fill.style.width = '100%';
    label.textContent = LF_T.done_searching;
    document.getElementById('done-count').textContent = kept + ' ' + LF_T.prospects;
    resultsLink.href = LF_BASE + '/prospects?search_id=' + start.search_id;
    done.classList.remove('hidden');
    btn.disabled = false;
  });
})();
