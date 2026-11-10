(function () {
  const verifyBtn = document.getElementById('verify-btn');
  if (verifyBtn) {
    verifyBtn.addEventListener('click', async () => {
      verifyBtn.disabled = true;
      const out = document.getElementById('verify-result');
      out.textContent = LF_T.verifying;
      const r = await post('/api/prospect/verify', { id: verifyBtn.dataset.id });
      out.textContent = r.error ? r.error : r.message;
      verifyBtn.disabled = false;
      if (!r.error) setTimeout(() => location.reload(), 900);
    });
  }

  const aiBtn = document.getElementById('ai-message-btn');
  if (aiBtn) {
    aiBtn.addEventListener('click', async () => {
      aiBtn.disabled = true;
      const out = document.getElementById('ai-message-output');
      out.value = LF_T.ai_processing;
      const r = await post('/api/prospect/message', {
        id: aiBtn.dataset.id,
        channel: document.getElementById('ai-channel').value,
      });
      out.value = r.error ? r.error : r.message;
      aiBtn.disabled = false;
    });
  }

  const normBtn = document.getElementById('ai-normalize-btn');
  if (normBtn) {
    normBtn.addEventListener('click', async () => {
      normBtn.disabled = true;
      const r = await post('/api/prospect/ai-normalize', { id: normBtn.dataset.id });
      if (!r.error) setTimeout(() => location.reload(), 600);
      else alert(r.error);
    });
  }

  const copyBtn = document.getElementById('copy-msg-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', async () => {
      await navigator.clipboard.writeText(document.getElementById('ai-message-output').value);
      copyBtn.textContent = LF_T.copied;
    });
  }
})();
