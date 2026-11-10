(function () {
  document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', e => e.dataTransfer.setData('text/plain', card.dataset.id));
  });
  document.querySelectorAll('.kanban-col-body').forEach(col => {
    col.addEventListener('dragover', e => e.preventDefault());
    col.addEventListener('drop', async e => {
      e.preventDefault();
      const id = e.dataTransfer.getData('text/plain');
      const card = document.querySelector('.kanban-card[data-id="' + id + '"]');
      if (!card) return;
      col.appendChild(card);
      const r = await post('/api/prospect/status', { id, status: col.dataset.status });
      if (r.error) alert(r.error);
    });
  });
})();
