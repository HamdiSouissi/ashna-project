(function () {
  const el = document.getElementById('map');
  if (!el) return;

  const colors = {
    nouveau: '#64748b', a_contacter: '#f59e0b', contacte: '#3b82f6',
    interesse: '#8b5cf6', rdv: '#14b8a6', client: '#22c55e', refuse: '#ef4444',
  };

  const map = L.map('map').setView([35.5, 9.5], 6); // vue par defaut Afrique du Nord
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  fetch(LF_BASE + '/api/prospects/map')
    .then(r => r.json())
    .then(points => {
      const bounds = [];
      points.forEach(p => {
        const lat = parseFloat(p.latitude), lon = parseFloat(p.longitude);
        bounds.push([lat, lon]);
        L.circleMarker([lat, lon], {
          radius: 7, weight: 1,
          color: colors[p.statut_prospection] || '#64748b', fillOpacity: 0.85,
        }).addTo(map).bindPopup(
          '<strong>' + p.nom + '</strong><br>' +
          (p.adresse || '') + ' ' + (p.ville || '') + '<br>' +
          (p.telephone_normalise ? '<a href="tel:' + p.telephone_normalise + '">' + p.telephone_normalise + '</a><br>' : '') +
          '<a href="' + LF_BASE + '/prospect/' + p.id + '">' + (LF_T.prospect || 'Fiche') + '</a>'
        );
      });
      if (bounds.length > 0) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
    })
    .catch(() => {});
})();
