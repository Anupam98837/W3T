{{-- resources/views/modules/assignments/createAssignment.blade.php --}}
@section('title','Create Assignment')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  /* ===== Shell ===== */
  .qz-wrap{max-width:1100px;margin:14px auto 40px}
  .qz.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:hidden}
  .qz .card-header{background:var(--surface);border-bottom:1px solid var(--line-strong);padding:16px 18px}
  .qz-head{display:flex;align-items:center;gap:10px}
  .qz-head i{color:var(--accent-color)}
  .qz-head strong{color:var(--ink);font-family:var(--font-head);font-weight:700}
  .qz-head .hint{color:var(--muted-color);font-size:var(--fs-13)}

  .section-title{font-weight:600;color:var(--ink);font-family:var(--font-head);margin:12px 2px 14px}
  .divider-soft{height:1px;background:var(--line-soft);margin:10px 0 16px}

  /* RTE */
  .toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
  .tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
  .tool:hover{background:var(--page-hover)}
  .rte-wrap{position:relative}
  .rte{
    min-height:200px;max-height:600px;overflow:auto;
    border:1px solid var(--line-strong);border-radius:12px;background:#fff;padding:12px;line-height:1.6;outline:none
  }
  .rte:focus{box-shadow:var(--ring);border-color:var(--accent-color)}
  .rte-ph{position:absolute;top:12px;left:12px;color:#9aa3b2;pointer-events:none;font-size:var(--fs-14)}
  .rte.has-content + .rte-ph{display:none}

  /* Inputs polish */
  .form-control:focus, .form-select:focus{box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 20%, transparent);border-color:var(--accent-color)}
  .input-group-text{background:var(--surface);border-color:var(--line-strong)}
  .tiny{font-size:12px;color:#6b7280}

  /* Errors */
  .err{font-size:12px;color:var(--danger-color);display:none;margin-top:6px}
  .err:not(:empty){display:block}

  /* Busy overlay */
  .dim{position:absolute;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.06);z-index:2}
  .dim.show{display:flex}
  .spin{width:18px;height:18px;border:3px solid #0001;border-top-color:var(--accent-color);border-radius:50%;animation:rot 1s linear infinite}
  @keyframes rot{to{transform:rotate(360deg)}}

  /* Attachments dropzone */
  .dropzone{
    display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;
    border:2px dashed var(--line-strong);border-radius:14px;background:var(--surface-2, #fff);
    padding:24px; transition:border-color .18s ease, background .18s ease;
  }
  .dropzone:hover{border-color:var(--primary-color);background:color-mix(in oklab, var(--primary-color) 7%, transparent)}
  .dropzone.dragover{border-color:var(--primary-color);box-shadow:0 0 0 3px color-mix(in oklab, var(--primary-color) 15%, transparent)}
  .drop-icon{width:56px;height:56px;border-radius:999px;border:1px dashed var(--line-strong);display:flex;align-items:center;justify-content:center;margin-bottom:10px;opacity:.9}
  .drop-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}

  /* File list */
  .file-list{margin-top:12px;display:flex;flex-direction:column;gap:8px}
  .file-item{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border:1px solid var(--line-strong);border-radius:10px;background:var(--surface)}
  .file-info{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
  .file-info i{color:var(--muted-color)}
  .file-name{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px}
  .file-size{color:var(--muted-color);font-size:12px;white-space:nowrap}
  .file-remove{color:var(--danger-color);cursor:pointer;padding:4px 8px}
  .file-remove:hover{opacity:.7}

  /* Toggle switch */
  .toggle-wrap{display:flex;align-items:center;gap:10px}
  .toggle-switch{position:relative;display:inline-block;width:48px;height:24px}
  .toggle-switch input{opacity:0;width:0;height:0}
  .toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#cbd5e1;border-radius:24px;transition:.3s}
  .toggle-slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:white;border-radius:50%;transition:.3s}
  .toggle-switch input:checked + .toggle-slider{background:var(--primary-color)}
  .toggle-switch input:checked + .toggle-slider:before{transform:translateX(24px)}

  /* Button loading state */
  .btn-loading{pointer-events:none;opacity:.85}
  .btn-loading .btn-label{visibility:hidden}
  .btn-loading .btn-spinner{display:inline-block !important}

  .btn-spinner{display:none; width:1rem;height:1rem;border:.2rem solid #0001;border-top-color:#fff;border-radius:50%;vertical-align:-.125em;animation:rot 1s linear infinite}
  .btn-light .btn-spinner{border-top-color:#0009}

  /* Dark mode parity */
  html.theme-dark .rte{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .tool{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .dropzone{background:#0f172a;border-color:var(--line-strong)}
  html.theme-dark .file-item{background:#0f172a;border-color:var(--line-strong)}
</style>

@section('content')
<div class="qz-wrap">
  <div class="card qz">
    <div class="card-header">
      <div class="qz-head">
        <i class="fa-solid fa-file-pen"></i>
        <strong id="pageTitle">Create New Assignment</strong>
        <span class="hint" id="hint">— Configure assignment details, instructions, and submission settings.</span>
      </div>
    </div>

    <div class="card-body position-relative">
      <div class="dim" id="busy"><div class="spin" aria-label="Saving…"></div></div>

      {{-- Basic Information --}}
      <h3 class="section-title">Basic Information</h3>
      <div class="divider-soft"></div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="course_module_id">Course Module <span class="text-danger">*</span></label>
          <select id="course_module_id" class="form-select">
            <option value="">Select Module</option>
            {{-- Options will be populated via JS --}}
          </select>
          <div class="err" data-for="course_module_id"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="batch_id">Batch <span class="text-danger">*</span></label>
          <select id="batch_id" class="form-select">
            <option value="">Select Batch</option>
            {{-- Options will be populated via JS --}}
          </select>
          <div class="err" data-for="batch_id"></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
          <input id="title" class="form-control" type="text" maxlength="255" placeholder="e.g., React Component Design Principles" autocomplete="off">
        </div>
        <div class="err" data-for="title"></div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="slug">Slug</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
          <input id="slug" class="form-control" type="text" maxlength="255" placeholder="Auto-generated from title" autocomplete="off">
        </div>
        <div class="tiny mt-1">Leave blank to auto-generate from title</div>
        <div class="err" data-for="slug"></div>
      </div>

      <div class="mb-3">
        <label class="form-label d-block">Instructions</label>
        <div class="toolbar" aria-label="Instructions toolbar">
          <button class="tool" type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
          <button class="tool" type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
          <button class="tool" type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
          <button class="tool" type="button" data-format="H2">H2</button>
          <button class="tool" type="button" data-format="H3">H3</button>
          <button class="tool" type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
          <button class="tool" type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
          <button class="tool" type="button" id="btnLinkInst"><i class="fa-solid fa-link"></i></button>
          <span class="tiny">Task requirements, guidelines, and expectations</span>
        </div>
        <div class="rte-wrap">
          <div id="instructions" class="rte" contenteditable="true" spellcheck="true"></div>
          <div class="rte-ph">Your task is to design and implement a flexible React component…</div>
        </div>
        <div class="err" data-for="instructions"></div>
      </div>

      {{-- Submission Settings --}}
      <h3 class="section-title">Submission Settings</h3>
      <div class="divider-soft"></div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="status">Status</label>
          <select id="status" class="form-select">
            <option value="published" selected>Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="submission_type">Submission Type</label>
          <select id="submission_type" class="form-select">
            <option value="file" selected>File Upload</option>
            <option value="text">Text Entry</option>
            <option value="link">Link/URL</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label" for="attempts_allowed">Attempts Allowed</label>
          <input id="attempts_allowed" class="form-control" type="number" min="1" value="3" placeholder="3">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="total_marks">Total Marks</label>
          <input id="total_marks" class="form-control" type="number" min="1" placeholder="100">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="pass_marks">Pass Marks</label>
          <input id="pass_marks" class="form-control" type="number" min="0" placeholder="70">
        </div>
      </div>

      {{-- Schedule --}}
      <h3 class="section-title">Schedule</h3>
      <div class="divider-soft"></div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="due_at">Due At</label>
          <input id="due_at" class="form-control" type="datetime-local">
          <div class="err" data-for="due_at"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="end_at">End At</label>
          <input id="end_at" class="form-control" type="datetime-local">
          <div class="tiny mt-1">Final deadline (no submissions after this)</div>
          <div class="err" data-for="end_at"></div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label d-block">Allow Late Submissions</label>
          <div class="toggle-wrap">
            <label class="toggle-switch">
              <input type="checkbox" id="allow_late_submissions">
              <span class="toggle-slider"></span>
            </label>
            <span class="tiny">Accept submissions after due date</span>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="late_penalty">Late Penalty (%)</label>
          <input id="late_penalty" class="form-control" type="number" min="0" max="100" placeholder="10" disabled>
          <div class="tiny mt-1">Percentage deducted per day late</div>
        </div>
      </div>

      {{-- Attachments --}}
      <h3 class="section-title">Attachments</h3>
      <div class="divider-soft"></div>

      <div id="dropzone" class="dropzone" aria-label="Attachments dropzone">
        <div class="drop-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
        <div class="lead fw-semibold">Drag & drop files here</div>
        <div class="tiny mt-1">PDF, DOC, DOCX, ZIP, Images • up to 10 MB each</div>
        <div class="drop-actions">
          <label class="btn btn-outline-primary mb-0" for="attachments">
            <i class="fa fa-file me-1"></i>Choose Files
          </label>
          <input id="attachments" type="file" multiple accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png,.gif" hidden>
          <button type="button" id="btnClearFiles" class="btn btn-light">Clear All</button>
        </div>
      </div>
      <div id="fileList" class="file-list"></div>
      <div class="err" data-for="attachments"></div>

      {{-- Actions --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <a id="cancel" class="btn btn-light" href="/admin/assignments/manage">Cancel</a>
        <button id="btnSave" class="btn btn-primary" type="button">
          <span class="btn-spinner" aria-hidden="true"></span>
          <span class="btn-label"><i class="fa fa-floppy-disk me-1"></i> <span id="saveBtnText">Create Assignment</span></span>
        </button>
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
  /* ===== helpers & auth ===== */
  const $ = id => document.getElementById(id);
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const okToast  = new bootstrap.Toast($('okToast'));
  const errToast = new bootstrap.Toast($('errToast'));
  const ok  = (m)=>{ $('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ $('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  const backList = '/admin/assignments/manage';
  const API_BASE = '/api/assignments';

  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  // detect Edit mode (?edit=<uuid or id>)
  const url_ = new URL(location.href);
  const editKey = url_.searchParams.get('edit');
  const isEdit = !!editKey;
  let currentUUID = editKey || null;

  if(isEdit){
    $('pageTitle').textContent = 'Edit Assignment';
    $('saveBtnText').textContent = 'Update Assignment';
    $('hint').textContent = '— Update assignment details and settings.';
    loadAssignment(editKey).catch((e)=> {
      console.error(e);
      Swal.fire('Not found','Could not load assignment for editing.','error')
        .then(()=> location.replace(backList));
    });
  }

  /* ===== Load course modules & batches ===== */
  async function loadDropdowns(){
    try{
      // Load course modules
      const modRes = await fetch('/api/course-modules', {
        headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
      });
      if(modRes.ok){
        const modData = await modRes.json();
        const modules = modData.data || modData;
        const modSelect = $('course_module_id');
        modules.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m.id;
          opt.textContent = m.title || m.module_name || `Module ${m.id}`;
          modSelect.appendChild(opt);
        });
      }

      // Load batches
      const batchRes = await fetch('/api/batches', {
        headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
      });
      if(batchRes.ok){
        const batchData = await batchRes.json();
        const batches = batchData.data || batchData;
        const batchSelect = $('batch_id');
        batches.forEach(b => {
          const opt = document.createElement('option');
          opt.value = b.id;
          opt.textContent = b.name || b.batch_name || `Batch ${b.id}`;
          batchSelect.appendChild(opt);
        });
      }
    }catch(e){
      console.error('Failed to load dropdowns:', e);
    }
  }
  loadDropdowns();

  /* ===== enable/disable form during save ===== */
  function setFormDisabled(disabled){
    document.querySelectorAll('.card-body input, .card-body select, .card-body button, .card-body .tool')
      .forEach(el=>{
        if (el.id === 'cancel') return;
        if (el.id === 'btnSave') return;
        el.disabled = !!disabled;
      });
  }

  function setSaving(on){
    const btn = $('btnSave');
    btn.classList.toggle('btn-loading', !!on);
    btn.disabled = !!on;
    $('busy').classList.toggle('show', !!on);
    setFormDisabled(!!on);
  }

  /* ===== wire RTE ===== */
  function wireRTE(rootId, linkBtnId){
    const el = $(rootId), ph = el.nextElementSibling;
    const hasContent = () => (el.textContent || '').trim().length > 0 || (el.innerHTML||'').trim().length > 0;
    function togglePh(){ el.classList.toggle('has-content', hasContent()); }
    ['input','keyup','paste','blur'].forEach(ev => el.addEventListener(ev, togglePh));
    togglePh();

    const parent = el.closest('.mb-3') || document;
    parent.querySelectorAll('.tool[data-cmd]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand(b.dataset.cmd,false,null); el.focus(); togglePh(); }));
    parent.querySelectorAll('.tool[data-format]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand('formatBlock',false,b.dataset.format); el.focus(); togglePh(); }));
    if(linkBtnId){
      const lb = $(linkBtnId);
      lb && lb.addEventListener('click',()=>{
        const u = prompt('Enter URL (https://…)'); if(u && /^https?:\/\//i.test(u)){ document.execCommand('createLink',false,u); el.focus(); }
      });
    }
  }
  wireRTE('instructions','btnLinkInst');

  /* ===== File handling ===== */
  const drop = $('dropzone');
  const fileInput = $('attachments');
  const fileList = $('fileList');
  const btnClearFiles = $('btnClearFiles');
  let selectedFiles = [];

  function formatFileSize(bytes){
    if(bytes < 1024) return bytes + ' B';
    if(bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
  }

  function renderFileList(){
    fileList.innerHTML = '';
    selectedFiles.forEach((f, idx) => {
      const item = document.createElement('div');
      item.className = 'file-item';
      item.innerHTML = `
        <div class="file-info">
          <i class="fa-solid fa-file"></i>
          <span class="file-name" title="${f.name}">${f.name}</span>
          <span class="file-size">${formatFileSize(f.size)}</span>
        </div>
        <span class="file-remove" data-idx="${idx}"><i class="fa-solid fa-xmark"></i></span>
      `;
      fileList.appendChild(item);
    });

    // Wire remove buttons
    fileList.querySelectorAll('.file-remove').forEach(btn => {
      btn.addEventListener('click', ()=>{
        const idx = parseInt(btn.dataset.idx);
        selectedFiles.splice(idx, 1);
        renderFileList();
      });
    });
  }

  function addFiles(files){
    Array.from(files).forEach(f => {
      if(f.size > 10*1024*1024){ err(`File ${f.name} exceeds 10MB`); return; }
      selectedFiles.push(f);
    });
    renderFileList();
  }

  ;['dragenter','dragover'].forEach(ev=>{
    drop.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); drop.classList.add('dragover'); });
  });
  ;['dragleave','dragend','drop'].forEach(ev=>{
    drop.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); drop.classList.remove('dragover'); });
  });
  drop.addEventListener('drop', e=>{
    const files = e.dataTransfer && e.dataTransfer.files;
    if(files) addFiles(files);
  });

  fileInput.addEventListener('change', ()=>{
    if(fileInput.files) addFiles(fileInput.files);
    fileInput.value = '';
  });

  btnClearFiles.addEventListener('click', ()=>{
    selectedFiles = [];
    renderFileList();
  });

  /* ===== Late submission toggle ===== */
  const allowLate = $('allow_late_submissions');
  const latePenalty = $('late_penalty');
  function syncLatePenalty(){
    latePenalty.disabled = !allowLate.checked;
    if(!allowLate.checked) latePenalty.value = '';
  }
  allowLate.addEventListener('change', syncLatePenalty);
  syncLatePenalty();

  /* ===== Auto-slug generation ===== */
  const titleInput = $('title');
  const slugInput = $('slug');
  titleInput.addEventListener('blur', ()=>{
    if(!slugInput.value.trim()){
      slugInput.value = titleInput.value.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    }
  });

  /* ===== errors ===== */
  function fErr(field,msg){ const el=document.querySelector(`.err[data-for="${field}"]`); if(el){ el.textContent=msg||''; el.style.display=msg?'block':'none'; } }
  function clrErr(){ document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }

  /* ===== payload builder ===== */
  function buildPayload(){
    return {
      course_module_id: $('course_module_id').value || null,
      batch_id: $('batch_id').value || null,
      title: ($('title').value||'').trim(),
      slug: ($('slug').value||'').trim() || null,
      instructions: ($('instructions').innerHTML||'').trim() || null,
      status: $('status').value,
      submission_type: $('submission_type').value,
      attempts_allowed: Number($('attempts_allowed').value||0) || null,
      total_marks: Number($('total_marks').value||0) || null,
      pass_marks: Number($('pass_marks').value||0) || null,
      due_at: $('due_at').value || null,
      end_at: $('end_at').value || null,
      allow_late_submissions: allowLate.checked ? 1 : 0,
      late_penalty: allowLate.checked ? (Number($('late_penalty').value||0) || null) : null
    };
  }

  /* ===== load (Edit mode) ===== */
  async function loadAssignment(key){
    $('busy').classList.add('show');
    try{
      const res = await fetch(`/api/assignments/${encodeURIComponent(key)}`, {
        headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
      });
      const json = await res.json().catch(()=> ({}));
      
      if(!res.ok) throw new Error(json?.message || 'Load failed');
      
      const a = json?.data || json;
      if(!a) throw new Error('Assignment not found');

      currentUUID = a.uuid || a.id || key;

      // fill fields
      $('course_module_id').value = a.course_module_id || '';
      $('batch_id').value = a.batch_id || '';
      $('title').value = a.title || '';
      $('slug').value = a.slug || '';
      $('instructions').innerHTML = a.instructions || '';
      $('status').value = a.status || 'published';
      $('submission_type').value = a.submission_type || 'file';
      $('attempts_allowed').value = a.attempts_allowed || 3;
      $('total_marks').value = a.total_marks || '';
      $('pass_marks').value = a.pass_marks || '';
      
      if(a.due_at){
        const d = new Date(a.due_at);
        if(!isNaN(d)) $('due_at').value = d.toISOString().slice(0,16);
      }
      if(a.end_at){
        const d = new Date(a.end_at);
        if(!isNaN(d)) $('end_at').value = d.toISOString().slice(0,16);
      }

      allowLate.checked = !!a.allow_late_submissions;
      $('late_penalty').value = a.late_penalty || '';
      syncLatePenalty();

      // Update RTE placeholder
      document.querySelectorAll('.rte-ph').forEach(ph => {
        const editor = ph.previousElementSibling;
        const hasContent = (editor.textContent || '').trim().length > 0 || (editor.innerHTML||'').trim().length > 0;
        editor.classList.toggle('has-content', hasContent);
      });
      
    }catch(e){
      console.error(e);
      throw e;
    }finally{
      $('busy').classList.remove('show');
    }
  }

  /* ===== submit ===== */
  $('btnSave').addEventListener('click', async ()=>{
    clrErr();
    
    // Validate required fields
    const title = ($('title').value||'').trim();
    if(!title){ fErr('title','Title is required.'); $('title').focus(); return; }

    const courseModuleId = $('course_module_id').value;
    if(!courseModuleId){ fErr('course_module_id','Course module is required.'); $('course_module_id').focus(); return; }

    const batchId = $('batch_id').value;
    if(!batchId){ fErr('batch_id','Batch is required.'); $('batch_id').focus(); return; }

    setSaving(true);
    try{
      let res, json;

      const url  = isEdit ? `/api/assignments/${encodeURIComponent(currentUUID)}` : API_BASE;
      const method = isEdit ? 'PUT' : 'POST';

      // If we have files, use multipart; otherwise JSON
      if(selectedFiles.length > 0){
        const fd = new FormData();
        const payload = buildPayload();
        Object.entries(payload).forEach(([k,v])=> { 
          if(v!==undefined && v!==null) fd.append(k, v); 
        });
        
        selectedFiles.forEach(f => fd.append('attachments[]', f));

        res = await fetch(url, {
          method: method,
          headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' },
          body: fd
        });
      }else{
        // JSON only
        const payload = buildPayload();
        res = await fetch(url, {
          method: method,
          headers:{ 
            'Authorization':'Bearer '+TOKEN, 
            'Accept':'application/json', 
            'Content-Type':'application/json' 
          },
          body: JSON.stringify(payload)
        });
      }

      json = await res.json().catch(()=> ({}));

      if(res.ok){
        ok(isEdit ? 'Assignment updated successfully' : 'Assignment created successfully');
        setTimeout(()=> location.replace(backList), 800);
        return;
      }

      if(res.status===422){
        const e = json.errors || json.fields || {};
        Object.entries(e).forEach(([k,v])=> fErr(k, Array.isArray(v)? v[0] : String(v)));
        err(json.message || 'Please fix the highlighted fields.');
        return;
      }

      if(res.status===403){
        Swal.fire({icon:'error',title:'Unauthorized',html:'Token/role lacks permission for this endpoint.'});
        return;
      }

      Swal.fire(isEdit ? 'Update failed' : 'Save failed', json.message || ('HTTP '+res.status), 'error');
    }catch(ex){
      console.error(ex);
      Swal.fire('Network error','Please check your connection and try again.','error');
    }finally{
      setSaving(false);
    }
  });
})();
</script>
@endpush