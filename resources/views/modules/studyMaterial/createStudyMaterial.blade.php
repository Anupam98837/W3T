{{-- resources/views/modules/studyMaterials/createStudyMaterial.blade.php --}}
@section('title','Create Study Material')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  .sm-wrap{max-width:1100px;margin:16px auto 40px}
  .sm.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:hidden}
  .sm .card-header{background:var(--surface);border-bottom:1px solid var(--line-strong);padding:16px 18px}
  .sm-head{display:flex;align-items:center;gap:10px}
  .sm-head i{color:var(--accent-color)}
  .sm-head strong{color:var(--ink);font-family:var(--font-head);font-weight:700}
  .sm-head .hint{color:var(--muted-color);font-size:var(--fs-13)}
  .section-title{font-weight:600;color:var(--ink);font-family:var(--font-head);margin:12px 2px 14px}
  .divider-soft{height:1px;background:var(--line-soft);margin:10px 0 16px}

  .dim{position:absolute;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.06);z-index:2}
  .dim.show{display:flex}
  .spin{width:18px;height:18px;border:3px solid #0001;border-top-color:var(--accent-color);border-radius:50%;animation:rot 1s linear infinite}
  @keyframes rot{to{transform:rotate(360deg)}}

  .err{font-size:12px;color:var(--danger-color);display:none;margin-top:6px}
  .err:not(:empty){display:block}

  .dropzone{
    display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;
    border:2px dashed var(--line-strong);border-radius:14px;background:var(--surface-2,#fff);
    padding:20px;transition:border-color .18s ease, background .18s ease;
  }
  .dropzone:hover{border-color:var(--primary-color);background:color-mix(in oklab, var(--primary-color) 7%, transparent)}
  .dropzone.drag{border-color:var(--primary-color);box-shadow:0 0 0 3px color-mix(in oklab, var(--primary-color) 15%, transparent)}
  .drop-icon{width:52px;height:52px;border-radius:999px;border:1px dashed var(--line-strong);display:flex;align-items:center;justify-content:center;margin-bottom:10px;opacity:.9}
  .file-list{margin-top:10px}
  .file-row{
    display:grid;grid-template-columns:1fr auto auto auto;align-items:center;gap:12px;
    border:1px solid var(--line-strong);border-radius:12px;background:var(--surface-2,#fff);
    padding:10px 14px;margin-bottom:10px;transition:all 0.2s ease;
  }
  .file-row:hover{background:var(--surface-3);border-color:var(--line-medium);}
  .file-row .name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:500;color:var(--ink)}
  .file-row .size{color:var(--muted-color);font-size:12px;min-width:70px;text-align:right}
  
  /* Improved button styles */
  .btn-action{
    border:none;background:transparent;padding:6px 10px;border-radius:6px;
    transition:all 0.2s ease;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:4px;
    border:1px solid transparent;
  }
  .btn-preview{color:var(--primary-color);border-color:var(--primary-light);}
  .btn-preview:hover{background:var(--primary-color);color:white;}
  .btn-delete{color:var(--danger-color);border-color:var(--danger-light);}
  .btn-delete:hover{background:var(--danger-color);color:white;}
  
  .btn-group{display:flex;gap:8px;align-items:center;}

  .btn-loading{pointer-events:none;opacity:.85}
  .btn-loading .btn-label{visibility:hidden}
  .btn-loading .btn-spinner{display:inline-block !important}
  .btn-spinner{display:none;width:1rem;height:1rem;border:.2rem solid #0001;border-top-color:#fff;border-radius:50%;vertical-align:-.125em;animation:rot 1s linear infinite}

  /* Preview modal styles */
  .preview-container {max-height: 70vh; overflow: auto;}
  .preview-image {max-width: 100%; max-height: 60vh; border-radius: 8px;}
  .preview-pdf {width: 100%; height: 500px; border: 1px solid var(--line-strong); border-radius: 8px;}
  .preview-text {text-align: left; background: var(--surface-2); padding: 1rem; border-radius: 8px; max-height: 60vh; overflow: auto; font-family: monospace;}

  html.theme-dark .dropzone{background:#0f172a;border-color:var(--line-strong)}
  html.theme-dark .file-row{background:#0b1020;border-color:var(--line-strong)}
  html.theme-dark .file-row:hover{background:#131d35;}
  html.theme-dark .preview-text {background: #1a2335;}
  .lib-overlay-check {
  position: absolute;
  top: 8px;
  left: 8px; /* original */
  margin-left: 6px; /* added */
}

</style>

@section('content')
<div class="sm-wrap">
  <div class="card sm">
    <div class="card-header">
      <div class="sm-head">
        <i class="fa-solid fa-book-open"></i>
        <strong>Create Study Material</strong>
        <span class="hint">— Choose Course → Module → Batch, add details & upload files.</span>
      </div>
    </div>

    <div class="card-body position-relative">
      <div class="dim" id="busy"><div class="spin" aria-label="Saving…"></div></div>

      {{-- Associations --}}
      <h3 class="section-title">Associations</h3>
      <div class="divider-soft"></div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label" for="course_id">Course <span class="text-danger">*</span></label>
          <select id="course_id" class="form-select"></select>
          <div class="err" data-for="course_id"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="course_module_id">Course Module <span class="text-danger">*</span></label>
          <select id="course_module_id" class="form-select" disabled></select>
          <div class="err" data-for="course_module_id"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="batch_id">Batch <span class="text-danger">*</span></label>
          <select id="batch_id" class="form-select" disabled></select>
          <div class="err" data-for="batch_id"></div>
        </div>
      </div>

      {{-- Basics --}}
      <h3 class="section-title mt-3">Basics</h3>
      <div class="divider-soft"></div>
      <div class="mb-3">
        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
          <input id="title" class="form-control" type="text" maxlength="255" placeholder="e.g., React Component Design Principles" autocomplete="off">
        </div>
        <div class="err" data-for="title"></div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" class="form-control" rows="4" placeholder="Short description..."></textarea>
        <div class="err" data-for="description"></div>
      </div>
      <label class="form-check-label" for="allow_download">Allow Downloading Attachments</label>
      <div class="mb-3 form-check form-switch">
        <input class="form-check-input" type="checkbox" id="allow_download">
      </div>

      {{-- Attachments --}}
      <h3 class="section-title">Attachments</h3>
      <div class="divider-soft"></div>
      <div id="dz" class="dropzone" aria-label="Attachment dropzone">
        <div class="drop-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
        <div class="lead fw-semibold">Drag & drop files here or click to upload</div>
        <div class="tiny mt-1">Any format • up to 50 MB per file</div>
        <div class="mt-2">
          <label class="btn btn-outline-primary mb-0" for="attachments">
            <i class="fa fa-file-arrow-up me-1"></i>Choose Files
          </label>
          <input id="attachments" type="file" hidden multiple>
           <button type="button" id="btnOpenLibrary" class="btn btn-outline-secondary">
            <i class="fa fa-book me-1"></i>Choose from Library
          </button>
          <button type="button" id="btnClearAll" class="btn btn-light ms-2">Clear All</button>
        </div>
      </div>
      <div id="fileList" class="file-list"></div>
      <div class="err" data-for="attachments"></div>
      <div id="libraryList" class="file-list"></div>
  <div class="err" data-for="library_urls"></div>
      <div class="d-flex justify-content-between align-items-center mt-4">
        <a id="cancel" class="btn btn-light" href="/admin/study-materials/manage">Cancel</a>
        <button id="btnSave" class="btn btn-primary" type="button">
          <span class="btn-spinner" aria-hidden="true"></span>
          <span class="btn-label"><i class="fa fa-floppy-disk me-1"></i>Create Study Material</span>
        </button>
      </div>
    </div>
  </div>
    {{-- Study Material Library Modal --}}
  <div class="modal fade" id="smLibraryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa fa-book me-2"></i>Choose from Study Material Library
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <div id="libLoading" style="display:none; padding:18px;">
            <div class="d-flex align-items-center gap-2">
              <div class="spin" aria-hidden="true"></div>
              <div class="tiny text-muted">Loading library…</div>
            </div>
          </div>

          <div id="libEmpty" class="tiny text-muted p-3" style="display:none;">
            No library items found for the selected course/batch.
          </div>

          <div class="mb-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input id="libSearch" type="text" class="form-control"
                     placeholder="Search by file name or study material title…">
              <button id="btnLibSearch" class="btn btn-outline-secondary" type="button">
                <i class="fa fa-magnifying-glass"></i>
              </button>
            </div>
          </div>

          <div id="libGrid" class="row g-3"></div>
        </div>
        <div class="modal-footer d-flex justify-content-between align-items-center">
          <div id="libSelectionInfo" class="tiny text-muted">
            No items selected
          </div>
          <div>
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button id="btnLibAddSelected" type="button" class="btn btn-primary">
              Add selected
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Preview Modal --}}
  <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="previewModalTitle">File Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="previewContent" class="preview-container text-center">
            <div class="p-4">
              <i class="fas fa-file fa-3x text-muted mb-3"></i>
              <p class="text-muted">Select a file to preview</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a id="downloadPreview" href="#" class="btn btn-primary" download style="display: none;">
            <i class="fas fa-download me-1"></i>Download
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
    <div id="okToast" class="toast text-bg-success border-0">
      <div class="d-flex"><div id="okMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>
    </div>
    <div id="errToast" class="toast text-bg-danger border-0 mt-2">
      <div class="d-flex"><div id="errMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const $ = (id)=> document.getElementById(id);
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const okToast  = new bootstrap.Toast($('okToast'));
  const errToast = new bootstrap.Toast($('errToast'));
  const previewModal = new bootstrap.Modal($('previewModal'));
  const ok  = (m)=>{ $('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ $('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/');
    return;
  }

  const API_SM      = '/api/study-materials';
  const API_COURSES = '/api/courses?mode=active&per_page=50';
  const API_MODULES = (courseId)=> `/api/course-modules?mode=active&course_id=${encodeURIComponent(courseId)}&per_page=50`;
  const API_BATCHES = (courseId)=> `/api/batches?mode=active&course_id=${encodeURIComponent(courseId)}&per_page=50`;
  const LIBRARY_API = API_SM;

  let libraryUrls = [];   // selected URLs from library

  function setBusy(on){ $('busy').classList.toggle('show', !!on); }
  function setSaving(on){
    $('btnSave').classList.toggle('btn-loading', !!on);
    $('btnSave').disabled = !!on;
    setBusy(on);
    document.querySelectorAll('.card-body input, .card-body select, .card-body textarea, .card-body button')
      .forEach(el=>{ if(el.id!=='btnSave' && el.id!=='cancel') el.disabled = !!on; });
  }

  function fErr(field,msg){
    const el = document.querySelector(`.err[data-for="${field}"]`);
    if(el){ el.textContent = msg || ''; el.style.display = msg ? 'block' : 'none'; }
  }
  function clrErr(){ document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }

  async function loadJSON(url){
    const res = await fetch(url, { headers: { 'Authorization': 'Bearer '+TOKEN, 'Accept': 'application/json' }});
    const json = await res.json().catch(()=> ({}));
    if(!res.ok) throw new Error(json?.message || ('HTTP '+res.status));
    return json?.data || json?.rows || json?.items || json;
  }

  function fillSelect(sel, rows, labelKey){
    sel.innerHTML = '';
    const opt0 = document.createElement('option');
    opt0.value = ''; opt0.textContent = 'Select...';
    sel.appendChild(opt0);

    (rows||[]).forEach(r=>{
      const label =
        (labelKey && r[labelKey]) ||
        r.badge_title || r.batch_name || r.title || r.name || r.label || r.code ||
        ('#'+r.id);

      const o = document.createElement('option');
      o.value = r.id;
      o.textContent = label;
      sel.appendChild(o);
    });
  }

  // Init: only load courses; modules & batches wait for course selection
  async function initDropdowns(){
    setBusy(true);
    try{
      const courses = await loadJSON(API_COURSES);
      fillSelect($('course_id'), courses, 'title');
      $('course_module_id').disabled = true;
      $('batch_id').disabled = true;
    }catch(e){
      console.error('Courses load failed:', e);
      fErr('course_id','Failed to load courses');
    }finally{
      setBusy(false);
    }
  }

  // When course changes -> load modules and batches for that course
  $('course_id').addEventListener('change', async ()=>{
    const cid = $('course_id').value;

    // Reset & disable until loaded
    fillSelect($('course_module_id'), []); $('course_module_id').disabled = true;
    fillSelect($('batch_id'), []);        $('batch_id').disabled = true;

    if(!cid) return;

    setBusy(true);
    try{
      const [modules, batches] = await Promise.all([
        loadJSON(API_MODULES(cid)).catch(e=>{ console.error('Modules load failed:', e); fErr('course_module_id','Failed to load'); return []; }),
        loadJSON(API_BATCHES(cid)).catch(e=>{ console.error('Batches load failed:', e); fErr('batch_id','Failed to load'); return []; })
      ]);
      fillSelect($('course_module_id'), modules, 'title');
      fillSelect($('batch_id'), batches, 'badge_title');
      $('course_module_id').disabled = false;
      $('batch_id').disabled = false;
    }finally{
      setBusy(false);
    }
  });

  /* ===== attachments (local uploads) ===== */
  const dz   = $('dz'),
        input= $('attachments'),
        list = $('fileList');
  let dt = new DataTransfer();

  function bytes(n){
    if(n>=1<<30) return (n/(1<<30)).toFixed(1)+' GB';
    if(n>=1<<20) return (n/(1<<20)).toFixed(1)+' MB';
    if(n>=1<<10) return (n/(1<<10)).toFixed(1)+' KB';
    return n+' B';
  }

  function previewFile(file) {
    const previewContent = $('previewContent');
    const previewTitle = $('previewModalTitle');
    const downloadBtn = $('downloadPreview');

    previewTitle.textContent = `Preview: ${file.name}`;

    const fileUrl = URL.createObjectURL(file);
    downloadBtn.href = fileUrl;
    downloadBtn.download = file.name;
    downloadBtn.style.display = 'inline-block';

    previewContent.innerHTML = '';

    const fileType = file.type || '';
    const isImage = fileType.startsWith('image/');
    const isPDF   = fileType === 'application/pdf';
    const isText  = fileType.startsWith('text/');

    if (isImage) {
      const img = document.createElement('img');
      img.src = fileUrl;
      img.alt = file.name;
      img.className = 'preview-image';
      previewContent.appendChild(img);
    } else if (isPDF) {
      const embed = document.createElement('embed');
      embed.src = fileUrl;
      embed.type = 'application/pdf';
      embed.className = 'preview-pdf';
      previewContent.appendChild(embed);
    } else if (isText) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const pre = document.createElement('pre');
        pre.className = 'preview-text';
        pre.textContent = e.target.result;
        previewContent.appendChild(pre);
      };
      reader.readAsText(file);
    } else {
      previewContent.innerHTML = `
        <div class="p-4">
          <i class="fas fa-file fa-3x text-muted mb-3"></i>
          <p class="text-muted">Preview not available for this file type</p>
          <p class="small text-muted">File type: ${fileType || 'Unknown'}</p>
        </div>
      `;
    }

    previewModal.show();
  }

  function redraw(){
    list.innerHTML='';
    Array.from(dt.files).forEach((f)=>{
      const row = document.createElement('div');
      row.className='file-row';

      const n = document.createElement('div');
      n.className='name';
      n.textContent=f.name;
      n.title = f.name;

      const s = document.createElement('div');
      s.className='size';
      s.textContent=bytes(f.size);

      const btnGroup = document.createElement('div');
      btnGroup.className = 'btn-group';

      const previewBtn = document.createElement('button');
      previewBtn.className='btn-action btn-preview';
      previewBtn.type='button';
      previewBtn.innerHTML='<i class="fa fa-eye"></i><span>Preview</span>';
      previewBtn.title = 'Preview file';

      const rm = document.createElement('button');
      rm.className='btn-action btn-delete';
      rm.type='button';
      rm.innerHTML='<i class="fa fa-trash"></i><span>Delete</span>';
      rm.title = 'Remove file';

      previewBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        e.stopPropagation();
        const idx = Array.from(list.children).indexOf(row);
        const file = dt.files[idx];
        if(file) previewFile(file);
      });

      rm.addEventListener('click',(e)=>{
        e.preventDefault();
        e.stopPropagation();
        const idx = Array.from(list.children).indexOf(row);
        const next = new DataTransfer();
        Array.from(dt.files).forEach((ff,i)=>{ if(i!==idx) next.items.add(ff); });
        dt = next;
        input.files = dt.files;
        redraw();
      });

      btnGroup.appendChild(previewBtn);
      btnGroup.appendChild(rm);

      row.appendChild(n);
      row.appendChild(s);
      row.appendChild(btnGroup);
      list.appendChild(row);
    });
  }

  function addFiles(files){
    const maxPer = 50*1024*1024;
    let hasLargeFile = false;

    Array.from(files||[]).forEach(f=>{
      if(f.size>maxPer){
        fErr('attachments',`"${f.name}" exceeds 50 MB.`);
        hasLargeFile = true;
        return;
      }
      dt.items.add(f);
    });

    input.files = dt.files;
    redraw();

    if(!hasLargeFile){
      fErr('attachments','');
    }
  }

  dz.addEventListener('click', ()=> input.click());
  input.addEventListener('change', ()=> addFiles(input.files));
  ['dragenter','dragover'].forEach(ev=>{
    dz.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dz.classList.add('drag'); });
  });
  ['dragleave','dragend','drop'].forEach(ev=>{
    dz.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dz.classList.remove('drag'); });
  });
  dz.addEventListener('drop', e=> addFiles(e.dataTransfer && e.dataTransfer.files));
  $('btnClearAll').addEventListener('click', ()=>{
    dt = new DataTransfer();
    input.value='';
    input.files = dt.files;
    redraw();
    fErr('attachments','');
  });

  $('previewModal').addEventListener('hidden.bs.modal', function() {
    const downloadBtn = $('downloadPreview');
    if (downloadBtn.href && downloadBtn.href.startsWith('blob:')) {
      URL.revokeObjectURL(downloadBtn.href);
    }
    downloadBtn.style.display = 'none';
  });

  /* ===== LIBRARY (Study Material attachments reused) ===== */
  const libraryList       = $('libraryList');       // list of chosen library files (you'll need this div in the blade)
  const btnOpenLibrary    = $('btnOpenLibrary');    // "Choose from Library" button
  const libModal          = document.getElementById('smLibraryModal');
  const libGrid           = $('libGrid');
  const libEmpty          = $('libEmpty');
  const libLoading        = $('libLoading');
  const libSearch         = $('libSearch');
  const btnLibSearch      = $('btnLibSearch');
  const btnLibAddSelected = $('btnLibAddSelected');
  const libSelectionInfo  = $('libSelectionInfo');

  const libModalInstance  = libModal ? new bootstrap.Modal(libModal) : null;
  let libItems        = [];        // normalized docs
  let libSelectedKeys = new Set(); // url-without-query keys

  function escapeHtml(str){
    return String(str || '').replace(/[&<>"'`=\/]/g, ch => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60','=':'&#x3D;'
    }[ch] || ch));
  }
  function extOf(u){
    try{ return (u||'').split('?')[0].split('.').pop().toLowerCase(); }catch(e){ return ''; }
  }
  function isImageExt(e){
    return ['png','jpg','jpeg','gif','webp','avif','svg'].includes(e);
  }

  function renderLibraryList(){
    if (!libraryList) return;
    libraryList.innerHTML = '';

    if (!libraryUrls || libraryUrls.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'tiny text-muted';
      empty.textContent = 'No files chosen from library.';
      libraryList.appendChild(empty);
      return;
    }

    libraryUrls.forEach((url, idx) => {
      const row = document.createElement('div');
      row.className = 'file-row';

      const nameDiv = document.createElement('div');
      nameDiv.className = 'name';
      nameDiv.title = url;
      nameDiv.textContent = url.split('/').pop() || url;

      const sizeDiv = document.createElement('div');
      sizeDiv.className = 'size';
      sizeDiv.textContent = 'Library';

      const btnGroup = document.createElement('div');
      btnGroup.className = 'btn-group';

      const rm = document.createElement('button');
      rm.type = 'button';
      rm.className = 'btn-action btn-delete';
      rm.innerHTML = '<i class="fa fa-trash"></i><span>Remove</span>';
      rm.title = 'Remove library file';
      rm.dataset.idx = String(idx);
      rm.addEventListener('click', ()=>{
        const i = parseInt(rm.dataset.idx,10);
        if(!isNaN(i)){
          libraryUrls.splice(i,1);
          renderLibraryList();
        }
      });

      btnGroup.appendChild(rm);
      row.appendChild(nameDiv);
      row.appendChild(sizeDiv);
      row.appendChild(btnGroup);
      libraryList.appendChild(row);
    });
  }

  function updateLibSelectionInfo(){
    if(!libSelectionInfo) return;
    const count = libSelectedKeys.size;
    libSelectionInfo.textContent = count
      ? `${count} item${count>1?'s':''} selected`
      : 'No items selected';
  }

  function normalizeAttach(a){
    if(!a) return null;

    if(typeof a === 'string'){
      // try JSON string
      try{
        const parsed = JSON.parse(a);
        if(Array.isArray(parsed)){
          if(!parsed.length) return null;
          return normalizeAttach(parsed[0]);
        }
        if(parsed && typeof parsed === 'object') return normalizeAttach(parsed);
      }catch(e){
        // treat as plain URL
        const url = a;
        const name = url.split('/').pop() || url;
        const ext  = extOf(url);
        return { url, name, mime:'', size:0, ext };
      }
    }

    const url =
      a.signed_url ||
      a.stream_url ||
      a.url ||
      a.path ||
      a.file_url ||
      a.storage_url ||
      a.path_with_namespace ||
      null;

    if(!url) return null;

    const name =
      a.name ||
      a.label ||
      a.original_name ||
      (url.split('/').pop() || 'file');

    const mime = a.mime || a.content_type || a.contentType || '';
    const size = a.size || a.filesize || 0;
    const ext  = (a.ext || a.extension || extOf(url)).toLowerCase();

    return { url, name, mime, size, ext };
  }

  function renderLibGrid(){
  if(!libGrid || !libEmpty) return;
  libGrid.innerHTML = '';

  if(!libItems.length){
    libEmpty.style.display = 'block';
    updateLibSelectionInfo();
    return;
  }
  libEmpty.style.display = 'none';

  const frag = document.createDocumentFragment();

  libItems.forEach((doc, idx)=>{
    const col = document.createElement('div');
    col.className = 'col-md-4 mb-3';

    const card = document.createElement('div');
    card.className = 'card h-100 lib-card position-relative';

    const thumbHtml = (doc.isImage && doc.url)
      ? `<img src="${escapeHtml(doc.url)}" alt="${escapeHtml(doc.name)}" class="img-fluid rounded" style="max-height:140px;object-fit:cover;">`
      : `<div class="lib-icon d-flex align-items-center justify-content-center" style="height:140px;">
           <i class="fa fa-file fa-2x"></i>
         </div>`;

    const mimeText = doc.mime || ('File .' + (doc.ext || ''));
    const sizeText = doc.size ? bytes(doc.size) : '';

    card.innerHTML = `
      <!-- checkbox overlay top-left -->
      <div class="lib-overlay-check ml-6">
        <input 
          class="form-check-input lib-select" 
          type="checkbox" 
          data-key="${escapeHtml(doc.key)}" 
          id="lib-${idx}"
        >
      </div>

      <div class="card-img-top lib-thumb text-center p-3">
        ${thumbHtml}
      </div>

      <div class="card-body d-flex flex-column">
        <h6 class="card-title text-truncate" title="${escapeHtml(doc.name)}">
          ${escapeHtml(doc.name)}
        </h6>

        <p class="card-text tiny text-muted mb-1">${escapeHtml(mimeText)}</p>
        <p class="card-text tiny text-muted mb-2">${escapeHtml(sizeText)}</p>

        <div class="mt-auto d-flex justify-content-start align-items-center">
          ${
            doc.url 
              ? `<button 
                   type="button" 
                   class="btn btn-sm btn-outline-primary tiny d-inline-flex align-items-center px-2 py-1 lib-preview" 
                   data-url="${escapeHtml(doc.url)}"
                   style="font-size:12px;"
                 >
                   <i class="fa fa-arrow-up-right-from-square me-1"></i>
                   Preview
                 </button>`
              : ''
          }
        </div>
      </div>
    `;

    // ===== checkbox =====
    const checkbox = card.querySelector('.lib-select');

    // pre-check
    if(libSelectedKeys.has(doc.key)) checkbox.checked = true;

    checkbox.addEventListener('change',(e)=>{
      const k = checkbox.getAttribute('data-key') || '';
      if(e.target.checked) libSelectedKeys.add(k);
      else libSelectedKeys.delete(k);
      updateLibSelectionInfo();
    });

    // ===== card click → toggle checkbox =====
    card.addEventListener('click',(ev)=>{
      if(
        ev.target.closest('.lib-select') ||
        ev.target.closest('.lib-preview')
      ) return;

      checkbox.checked = !checkbox.checked;
      checkbox.dispatchEvent(new Event('change'));
    });

    col.appendChild(card);
    frag.appendChild(col);
  });

  libGrid.appendChild(frag);
  updateLibSelectionInfo();
}


  async function fetchLibraryItems(query){
    if(!LIBRARY_API || !libLoading || !libGrid) return;

    libLoading.style.display = 'block';
    libGrid.innerHTML = '';
    if(libEmpty) libEmpty.style.display = 'none';
    libItems = [];
    libSelectedKeys = new Set();
    updateLibSelectionInfo();

    try{
      const params = new URLSearchParams();
      params.append('per_page','200');
      if(query) params.append('q', query);

      const url = LIBRARY_API + '?' + params.toString();
      const res = await fetch(url,{
        headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
      });
      const text = await res.text();
      let json = null;
      if(text){
        try{ json = JSON.parse(text); }catch(e){ json = null; }
      }

      if(!res.ok){
        const msg = json && (json.message || json.error) ? (json.message || json.error) : ('HTTP '+res.status);
        err('Library error: ' + msg);
        libItems = [];
        renderLibGrid();
        return;
      }

      let rows = [];
      if(Array.isArray(json)) rows = json;
      else if(json && Array.isArray(json.data)) rows = json.data;
      else if(json && json.data && Array.isArray(json.data.data)) rows = json.data.data;
      else if(json && Array.isArray(json.items)) rows = json.items;
      else if(json && Array.isArray(json.rows)) rows = json.rows;

      const docMap = new Map();

      rows.forEach(row=>{
        let atts = row.attachments || row.files || row.resources || row.attachment || row.file || [];
        if(typeof atts === 'string'){
          try{
            const parsed = JSON.parse(atts);
            atts = Array.isArray(parsed) ? parsed : (parsed ? [parsed] : []);
          }catch(e){
            atts = atts ? [atts] : [];
          }
        }
        if(!Array.isArray(atts)) atts = atts ? [atts] : [];

        const smTitle = row.title || row.name || ('Study Material ' + (row.id || ''));

        atts.forEach(a=>{
          const n = normalizeAttach(a);
          if(!n || !n.url) return;
          const key = n.url.split('?')[0];
          if(!key) return;

          if(!docMap.has(key)){
            docMap.set(key,{
              key,
              url: n.url,
              name: n.name || (n.url.split('/').pop() || 'file'),
              mime: n.mime || '',
              size: n.size || 0,
              ext:  n.ext || extOf(n.url),
              isImage: isImageExt(n.ext || extOf(n.url)),
              refs: [smTitle],
              searchText: (smTitle + ' ' + (n.name || '') + ' ' + (n.url || '')).toLowerCase()
            });
          }else{
            const entry = docMap.get(key);
            if(entry.refs.indexOf(smTitle) === -1){
              entry.refs.push(smTitle);
              entry.searchText += ' ' + smTitle;
            }
          }
        });
      });

      libItems = Array.from(docMap.values());
      renderLibGrid();
    }catch(e){
      console.error('Library fetch failed', e);
      libItems = [];
      renderLibGrid();
    }finally{
      libLoading.style.display = 'none';
    }
  }

  // open modal
  if(btnOpenLibrary && libModalInstance){
    btnOpenLibrary.addEventListener('click',()=>{
      libSelectedKeys = new Set();
      (libraryUrls || []).forEach(u=>{
        if(!u) return;
        const key = String(u).split('?')[0];
        libSelectedKeys.add(key);
      });
      if(libSearch) libSearch.value='';
      updateLibSelectionInfo();
      libModalInstance.show();
      fetchLibraryItems('');
    });
  }

  // search
  if(btnLibSearch && libSearch){
    btnLibSearch.addEventListener('click',()=>{
      const q = (libSearch.value||'').trim();
      fetchLibraryItems(q);
    });
    libSearch.addEventListener('keypress',(e)=>{
      if(e.key==='Enter'){
        e.preventDefault();
        const q = (libSearch.value||'').trim();
        fetchLibraryItems(q);
      }
    });
  }

  // confirm selections → libraryUrls[]
  if(btnLibAddSelected && libModalInstance){
    btnLibAddSelected.addEventListener('click',()=>{
      const chosen = [];
      libItems.forEach(item=>{
        if(libSelectedKeys.has(item.key)) chosen.push(item.url);
      });
      const merged = new Set([...(libraryUrls || []), ...chosen]);
      libraryUrls = Array.from(merged);
      renderLibraryList();
      libModalInstance.hide();
    });
  }

  /* ===== submit ===== */
  $('btnSave').addEventListener('click', async ()=>{
    clrErr();
    const course_id        = $('course_id').value;
    const course_module_id = $('course_module_id').value;
    const batch_id         = $('batch_id').value;
    const title            = ($('title').value||'').trim();

    let hasErr=false;
    if(!course_id){ fErr('course_id','Course is required.'); hasErr=true; }
    if(!course_module_id){ fErr('course_module_id','Course module is required.'); hasErr=true; }
    if(!batch_id){ fErr('batch_id','Batch is required.'); hasErr=true; }
    if(!title){ fErr('title','Title is required.'); hasErr=true; }
    if(hasErr) return;

    const fd = new FormData();
    fd.append('course_id', course_id);
    fd.append('course_module_id', course_module_id);
    fd.append('batch_id', batch_id);
    fd.append('title', title);
    const desc = ($('description').value||'').trim(); if(desc) fd.append('description', desc);
    fd.append('view_policy', $('allow_download').checked ? 'downloadable' : 'inline_only');

    // local uploads
    Array.from(dt.files).forEach(f=> fd.append('attachments[]', f, f.name));
    // library URLs
    (libraryUrls || []).forEach(u=>{
      if(u) fd.append('library_urls[]', u);
    });

    setSaving(true);
    try{
      const res = await fetch(API_SM, {
        method:'POST',
        headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' },
        body:fd
      });
      const json = await res.json().catch(()=> ({}));
      if(res.ok){
        ok('Study material created');
        setTimeout(()=> location.replace('/admin/course/studyMaterial/manage'), 700);
        return;
      }
      if(res.status===422){
        const e = json.errors || {};
        if(e['attachments.*']) fErr('attachments', Array.isArray(e['attachments.*'])? e['attachments.*'][0] : String(e['attachments.*']));
        ['course_id','course_module_id','batch_id','title','description','view_policy','attachments','library_urls'].forEach(k=>{
          if(e[k]) fErr(k, Array.isArray(e[k])? e[k][0] : String(e[k]));
        });
        err(json.message || 'Please fix the highlighted fields.');
        return;
      }
      if(res.status===403){
        Swal.fire({icon:'error',title:'Unauthorized',html:'Your role lacks permission for this endpoint.'});
        return;
      }
      Swal.fire('Save failed', json.message || ('HTTP '+res.status), 'error');
    }catch(ex){
      console.error(ex);
      Swal.fire('Network error','Please check your connection and try again.','error');
    }finally{
      setSaving(false);
    }
  });

  // boot
  initDropdowns();
  renderLibraryList();   // initial state
})();
</script>
@endpush
