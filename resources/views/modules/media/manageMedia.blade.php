<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Media Manager</title>
 
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <!-- Font Awesome -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    rel="stylesheet"
  />
 
  <meta name="csrf-token" content="{{ csrf_token() }}">
 
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/common/manageMedia.css') }}">
</head>
<body class="bg-light">
  <div class="container py-5">
 
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs" id="mediaTab">
      <li class="nav-item">
        <button class="nav-link active" type="button" id="library-tab" data-bs-toggle="tab" data-bs-target="#libraryPane">
          <i class="fa-solid fa-list"></i> Library
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" type="button" id="upload-tab" data-bs-toggle="tab" data-bs-target="#uploadPane">
          <i class="fa-solid fa-upload"></i> Upload
        </button>
      </li>
    </ul>
 
    <div class="tab-content">
 
      <!-- Library Pane -->
      <div class="tab-pane fade show active" id="libraryPane">
        <div class="card-container mt-3" id="mediaCards">
          <!-- cards injected here -->
        </div>
      </div>
 
      <!-- Upload Pane -->
      <div class="tab-pane fade" id="uploadPane">
        <div class="mt-3">
          <div id="dropZone" class="drop-zone mb-3">
            <p>Drag &amp; drop files here, or</p>
            <button class="btn btn-outline-primary" type="button" id="pickFileBtn">
              <i class="fa-solid fa-folder-open"></i> Choose File
            </button>
            <input type="file" id="fileInput" multiple class="d-none" />
          </div>
          <ul class="list-group" id="uploadList"></ul>
        </div>
      </div>
 
    </div>
  </div>
 
  <!-- Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script>
  // ✅ Get token (both storages)
  const token =
    (localStorage.getItem('token') || sessionStorage.getItem('token') || '').trim();

  if (!token) {
    Swal.fire({
      icon: 'warning',
      title: 'Login required',
      text: 'Please login to continue.',
      allowOutsideClick: false
    }).then(() => (window.location.href = '/'));
  }

  // ✅ Safe JSON fetch helper (handles non-JSON errors too)
  async function fetchJSON(url, options = {}) {
    const res = await fetch(url, {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(options.headers || {}),
        Authorization: `Bearer ${token}`,
      },
    });

    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const body = ct.includes('application/json') ? await res.json() : await res.text();

    if (!res.ok) {
      const msg =
        (typeof body === 'object' && body?.message) ? body.message :
        (typeof body === 'string' && body) ? body :
        `Request failed (${res.status})`;
      throw new Error(msg);
    }

    return body;
  }

  const API = {
    list:   () => fetchJSON('/api/media'),
    upload: (form) => fetchJSON('/api/media', { method: 'POST', body: form }),
    remove: (id) => fetchJSON(`/api/media/${id}`, { method: 'DELETE' }),
  };

  function fmtSize(b) {
    b = Number(b || 0);
    if (b < 1024) return b + ' B';
    if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB';
    return (b / (1024 * 1024)).toFixed(1) + ' MB';
  }

  // ——— Library ———
  async function loadLibrary() {
    const container = document.getElementById('mediaCards');
    container.innerHTML = '';

    try {
      const res = await API.list();

      const ok = (res?.status === 'success') || (res?.success === true);
      if (!ok) throw new Error(res?.message || 'Could not load media');

      const items = Array.isArray(res?.data) ? res.data : [];

      // ✅ Empty state (don’t show error)
      if (!items.length) {
        container.innerHTML = `
          <div class="text-center text-muted py-5">
            <i class="fa-solid fa-photo-film fa-2x mb-2"></i>
            <div>No media uploaded yet.</div>
          </div>
        `;
        return;
      }

      items.forEach(item => {
        const card = document.createElement('div');
        card.className = 'media-card';
        card.dataset.id = item.id; // ✅ use real id for delete

        card.innerHTML = `
          <div class="thumb-container">
            <img src="${item.url}" loading="lazy"
              onerror="
                this.style.display='none';
                this.parentNode.querySelector('.fallback-icon').style.display='flex';
              " />
            <div class="fallback-icon" style="display:none">
              <i class="fa-solid fa-file-image"></i>
            </div>
          </div>

          <div class="card-body">
            <div class="title">${String(item.url).split('/').pop()}</div>
          </div>

          <div class="overlay">
            <div class="url">${item.url}</div>
            <div class="size">${fmtSize(item.size)}</div>
            <button class="btn btn-copy btn-sm" type="button">
              <i class="fa-solid fa-copy me-1"></i>Copy URL
            </button>
          </div>
        `;

        // Copy handler
        card.querySelector('.btn-copy').onclick = (e) => {
          e.stopPropagation();
          navigator.clipboard.writeText(item.url)
            .then(() => Swal.fire({ icon: 'success', title: 'Copied!', timer: 1200, showConfirmButton: false }))
            .catch(() => Swal.fire('Error', 'Copy failed', 'error'));
        };

        // Click opens file
        card.onclick = () => window.open(item.url, '_blank');

        container.append(card);
      });

    } catch (err) {
      Swal.fire('Error', err?.message || 'Could not load media', 'error');
    }
  }

  // ——— Delete on long-press ———
  let pressTimer;
  document.addEventListener('mousedown', e => {
    const card = e.target.closest('.media-card');
    if (!card) return;

    pressTimer = setTimeout(async () => {
      const id = parseInt(card.dataset.id || '0', 10);
      if (!id) return;

      const { isConfirmed } = await Swal.fire({
        title: 'Delete this file?',
        icon: 'warning',
        showCancelButton: true
      });

      if (!isConfirmed) return;

      try {
        const r2 = await API.remove(id);
        const ok = (r2?.status === 'success') || (r2?.success === true);
        if (ok) {
          Swal.fire('Deleted', r2.message || 'Deleted', 'success');
          loadLibrary();
        } else {
          Swal.fire('Error', r2.message || 'Delete failed', 'error');
        }
      } catch (err) {
        Swal.fire('Error', err?.message || 'Delete failed', 'error');
      }

    }, 800);
  });
  document.addEventListener('mouseup', () => clearTimeout(pressTimer));

  // ——— Upload Pane ———
  const dropZone = document.getElementById('dropZone'),
        fileInput = document.getElementById('fileInput'),
        uploadList = document.getElementById('uploadList');

  document.getElementById('pickFileBtn').onclick = () => fileInput.click();
  fileInput.onchange = () => handleFiles([...fileInput.files]);

  ['dragenter', 'dragover'].forEach(evt =>
    dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('dragover'); })
  );
  ['dragleave', 'drop'].forEach(evt =>
    dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.remove('dragover'); })
  );
  dropZone.addEventListener('drop', e => handleFiles([...e.dataTransfer.files]));

  function addUploadItem(name) {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between';
    li.innerHTML = `<span>${name}</span>`;
    const badge = document.createElement('span');
    badge.className = 'badge bg-secondary';
    badge.textContent = 'waiting';
    li.append(badge);
    uploadList.append(li);
    return badge;
  }

  async function handleFiles(files) {
    for (let f of files) {
      const badge = addUploadItem(f.name);
      const form = new FormData();
      form.append('file', f);

      try {
        const json = await API.upload(form);
        const ok = (json?.status === 'success') || (json?.success === true);

        badge.className = ok ? 'badge bg-success' : 'badge bg-danger';
        badge.textContent = ok ? 'done' : 'error';
      } catch {
        badge.className = 'badge bg-danger';
        badge.textContent = 'error';
      }
    }
    await loadLibrary();
  }

  // ——— Initialize ———
  document.addEventListener('DOMContentLoaded', () => {
    loadLibrary();
    document.getElementById('library-tab')
      .addEventListener('shown.bs.tab', loadLibrary);
  });
</script>

</body>
</html>
 
 