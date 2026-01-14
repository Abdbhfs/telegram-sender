document.addEventListener('DOMContentLoaded', () => {
  const sendBtn = document.getElementById('sendMessageBtn');
  const uploadBtn = document.getElementById('uploadFileBtn');
  const clearBtn = document.getElementById('clearBtn');
  const spinner = document.getElementById('spinner');
  const result = document.getElementById('result');
  const progressWrap = document.getElementById('uploadProgress');
  const progressBar = progressWrap?.querySelector('.progress-bar');

  function showSpinner(on) { spinner.classList.toggle('d-none', !on); }
  function showResult(obj) { result.textContent = JSON.stringify(obj, null, 2); }

  sendBtn.addEventListener('click', async () => {
    const token = document.getElementById('token').value.trim();
    const chat = document.getElementById('chat').value.trim();
    const text = document.getElementById('text').value;
    if (!token || !chat) return alert('Provide token and chat');

    showSpinner(true);
    try {
      const fd = new FormData();
      fd.append('action', 'sendMessage');
      fd.append('token', token);
      fd.append('chat', chat);
      fd.append('text', text);

      const res = await fetch('api.php', { method: 'POST', body: fd });
      const data = await res.json();
      showResult(data);
    } catch (e) {
      showResult({ ok: false, error: e.message });
    } finally { showSpinner(false); }
  });

  uploadBtn.addEventListener('click', () => {
    const token = document.getElementById('token').value.trim();
    const chat = document.getElementById('chat').value.trim();
    const fileEl = document.getElementById('file');
    const fileType = document.getElementById('fileType').value;
    if (!token || !chat) return alert('Provide token and chat');
    if (!fileEl.files.length) return alert('Choose a file to upload');

    const file = fileEl.files[0];
    const fd = new FormData();
    fd.append('action', 'sendFile');
    fd.append('token', token);
    fd.append('chat', chat);
    fd.append('file', file);
    fd.append('fileType', fileType);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php');
    xhr.upload.addEventListener('progress', (ev) => {
      if (!ev.lengthComputable) return;
      const percent = Math.round(ev.loaded / ev.total * 100);
      progressWrap.classList.remove('d-none');
      progressBar.style.width = percent + '%';
      progressBar.textContent = percent + '%';
    });
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        progressWrap.classList.add('d-none');
        try { showResult(JSON.parse(xhr.responseText)); }
        catch (e) { showResult({ ok: false, error: 'Invalid JSON response' }); }
        showSpinner(false);
      }
    };
    showSpinner(true);
    xhr.send(fd);
  });

  clearBtn.addEventListener('click', () => {
    document.getElementById('text').value = '';
    document.getElementById('file').value = '';
    document.getElementById('fileType').value = 'auto';
    result.textContent = 'No actions yet.';
  });
});