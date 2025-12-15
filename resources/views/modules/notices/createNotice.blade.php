{{-- resources/views/modules/notices/createNotice.blade.php --}}
@section('title','Create Notice')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
@push('styles')
<style>
  /* (styles copied/adapted from your study material + assignment RTE) */
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
  .preview-container {max-height: 70vh; overflow: auto;}
  .preview-image {max-width: 100%; max-height: 60vh; border-radius: 8px;}
  .preview-pdf {width: 100%; height: 500px; border: 1px solid var(--line-strong); border-radius: 8px;}
  .preview-text {text-align: left; background: var(--surface-2); padding: 1rem; border-radius: 8px; max-height: 60vh; overflow: auto; font-family: monospace;}
  html.theme-dark .dropzone{background:#0f172a;border-color:var(--line-strong)}
  html.theme-dark .file-row{background:#0b1020;border-color:var(--line-strong)}
  html.theme-dark .file-row:hover{background:#131d35;}
  html.theme-dark .preview-text {background: #1a2335}
  .notice-lib-thumb {
  width: 100%;
  height: 120px;                /* fixed height container */
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fafafa;          /* optional */
  overflow: hidden;             /* prevents overflow */
  border-radius: 6px;
}

.notice-lib-thumb img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;          /* maintain aspect ratio */
  object-position: center center;
  display: block;
}
.toolbar .tool.is-active{
  background: var(--primary-color);
  border-color: var(--primary-color);
  color:#fff;
}
.toolbar .tool.is-active i{ color:#fff !important; }
.rte.rte-active{ outline: 2px solid var(--primary-color); outline-offset: 2px; border-radius: 10px; }

  
</style>
@endpush

@section('content')
<div class="sm-wrap">
  <div class="card sm">
    <div class="card-header">
      <div class="sm-head">
        <i class="fa-solid fa-bullhorn"></i>
        <strong>Create Notice</strong>
        <span class="hint">— Choose Course → Module → Batch, add message & attachments.</span>
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
          <label class="form-label" for="course_module_id">Course Module</label>
          <select id="course_module_id" class="form-select" disabled></select>
          <div class="err" data-for="course_module_id"></div>
        </div>
        <div class="col-md-4">
        <label class="form-label" for="batch_id">Batch </label>
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
          <input id="title" class="form-control" type="text" maxlength="255" placeholder="e.g., Exam postponed" autocomplete="off">
        </div>
        <div class="err" data-for="title"></div>
      </div>

      {{-- Message (RTE) --}}
      <div class="mb-3">
        <label class="form-label d-block">Message</label>

        <div class="toolbar" id="rte_toolbar" aria-label="Message toolbar">
          <button class="tool" type="button" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
          <button class="tool" type="button" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
          <button class="tool" type="button" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
          <button class="tool" type="button" data-cmd="insertUnorderedList" title="Bulleted list"><i class="fa fa-list-ul"></i></button>
          <button class="tool" type="button" data-cmd="insertOrderedList" title="Numbered list"><i class="fa fa-list-ol"></i></button>
          <button class="tool" type="button" data-cmd="createLink" title="Insert link"><i class="fa fa-link"></i></button>
          <button class="tool" type="button" data-cmd="removeFormat" title="Remove formatting"><i class="fa fa-eraser"></i></button>
          <select id="insertHeading" class="tool" title="Insert heading" style="padding:6px 8px;border-radius:8px; display:none;">
            <option value="">Insert…</option>
            <option value="h2">Heading</option>
            <option value="p">Paragraph</option>
          </select>
        </div>

        <div class="rte-wrap">
          <div id="rte" class="rte" contenteditable="true" aria-label="Notice message editor" role="textbox" spellcheck="true"></div>
          <div class="rte-ph">Write the notice message here…</div>
        </div>
        <textarea id="message_html" name="message_html" hidden></textarea>
        <div class="err" data-for="message_html"></div>
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
          <button type="button" id="btnChooseFromLibrary" class="btn btn-outline-secondary ms-2">
    <i class="fa fa-book me-1"></i>Choose from Library
    </button>

          <button type="button" id="btnClearAll" class="btn btn-light ms-2">Clear All</button>
        </div>
      </div>
      <div id="fileList" class="file-list"></div>
      <div class="err" data-for="attachments"></div>

      <div class="d-flex justify-content-between align-items-center mt-4">
        <a id="cancel" class="btn btn-light" href="/admin/notice/manage">Cancel</a>
        <button id="btnSave" class="btn btn-primary" type="button">
          <span class="btn-spinner" aria-hidden="true"></span>
          <span class="btn-label"><i class="fa fa-floppy-disk me-1"></i>Create Notice</span>
        </button>
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

  {{-- Toasts --}}
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
<script>
(function(){
  const $ = (id)=> document.getElementById(id);
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const okToast  = $('okToast') ? new bootstrap.Toast($('okToast')) : null;
  const errToast = $('errToast') ? new bootstrap.Toast($('errToast')) : null;
  const previewModal = $('previewModal') ? new bootstrap.Modal($('previewModal')) : null;

  const ok  = (m)=>{ if(okToast) { $('okMsg').textContent  = m||'Done'; okToast.show(); } else console.log('OK:', m); };
  const err = (m)=>{ if(errToast){ $('errMsg').textContent = m||'Something went wrong'; errToast.show(); } else console.warn('ERR:', m); };

  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/');
    return;
  }

  const API_NOTICES = '/api/notices';
  const API_COURSES = '/api/courses?mode=active&per_page=50';
  const API_MODULES = (courseId)=> `/api/course-modules?mode=active&course_id=${encodeURIComponent(courseId)}&per_page=50`;
  const API_BATCHES = (courseId)=> `/api/batches?mode=active&course_id=${encodeURIComponent(courseId)}&per_page=50`;

  function setBusy(on){ const el = $('busy'); if(el) el.classList.toggle('show', !!on); }
  function setSaving(on){
    const btn = $('btnSave');
    if(btn) btn.classList.toggle('btn-loading', !!on), btn.disabled = !!on;
    setBusy(on);
    document.querySelectorAll('.card-body input, .card-body select, .card-body textarea, .card-body button, .card-body .tool')
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
    if(!sel) return;
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

  async function initDropdowns(){
    setBusy(true);
    try{
      const courses = await loadJSON(API_COURSES);
      fillSelect($('course_id'), courses, 'title');
      if($('course_module_id')) $('course_module_id').disabled = true;
      if($('batch_id')) $('batch_id').disabled = true;
    }catch(e){
      console.error('Courses load failed:', e);
      fErr('course_id','Failed to load courses');
    }finally{
      setBusy(false);
    }
  }

  // course change
  if($('course_id')){
    $('course_id').addEventListener('change', async ()=>{
      const cid = $('course_id').value;
      if($('course_module_id')){ fillSelect($('course_module_id'), []); $('course_module_id').disabled = true; }
      if($('batch_id')){ fillSelect($('batch_id'), []); $('batch_id').disabled = true; }
      if(!cid) return;
      setBusy(true);
      try{
        const [modules, batches] = await Promise.all([
          loadJSON(API_MODULES(cid)).catch(e=>{ console.error('Modules load failed:', e); fErr('course_module_id','Failed to load'); return []; }),
          loadJSON(API_BATCHES(cid)).catch(e=>{ console.error('Batches load failed:', e); fErr('batch_id','Failed to load'); return []; })
        ]);
        fillSelect($('course_module_id'), modules, 'title');
        fillSelect($('batch_id'), batches, 'badge_title');
        if($('course_module_id')) $('course_module_id').disabled = false;
        if($('batch_id')) $('batch_id').disabled = false;
      }finally{ setBusy(false); }
    });
  }
(function () {
  // prevent double-init
  if (window.__NOTICE_RTE_ACTIVE_SYNC__) return;
  window.__NOTICE_RTE_ACTIVE_SYNC__ = true;

  const FORMAT_MAP = { H1: "h1", H2: "h2", H3: "h3", P: "p" };

  function selectionInside(editor) {
    const sel = document.getSelection();
    if (!sel || !sel.anchorNode) return false;
    const node = sel.anchorNode;
    return node === editor || editor.contains(node);
  }

  function isFormatActive(fmt) {
    try {
      const val = (document.queryCommandValue("formatBlock") || "").toLowerCase();
      const want = (FORMAT_MAP[fmt] || fmt || "").toLowerCase();
      return !!want && val.includes(want);
    } catch {
      return false;
    }
  }

  function findEditorForToolbar(toolbar) {
    // Your Create Notice layout: toolbar -> next sibling .rte-wrap -> .rte
    const next = toolbar.nextElementSibling;
    if (next && next.classList && next.classList.contains("rte-wrap")) {
      const ed = next.querySelector(".rte");
      if (ed) return ed;
    }

    // fallback: search inside the same block
    const block =
      toolbar.closest(".mb-1,.mb-2,.mb-3,.mb-4,.col-12,.col-md-12,.form-group") ||
      toolbar.parentElement ||
      document;

    return block.querySelector(".rte-wrap .rte");
  }

  function bindToolbar(toolbar) {
    if (toolbar.__rteBound) return;

    // IMPORTANT: only bind Create Notice toolbar
    if (toolbar.id !== "rte_toolbar") return;

    const editor = findEditorForToolbar(toolbar);
    if (!editor) return;

    toolbar.__rteBound = true;
    const tools = Array.from(toolbar.querySelectorAll(".tool"));

    function clearAll() {
      tools.forEach((btn) => {
        btn.classList.remove("is-active");
        btn.setAttribute("aria-pressed", "false");
      });
      editor.classList.remove("rte-active");
    }

    function update() {
      const inside = selectionInside(editor) || document.activeElement === editor;

      // editor ring hook (optional)
      editor.classList.toggle("rte-active", document.activeElement === editor);

      // If caret not inside editor, clear states (prevents “stuck” active look)
      if (!inside) {
        clearAll();
        return;
      }

      tools.forEach((btn) => {
        const cmd = btn.dataset.cmd;
        const fmt = btn.dataset.format;

        let on = false;
        try {
          if (cmd) on = !!document.queryCommandState(cmd);
          else if (fmt) on = isFormatActive(fmt);
        } catch {
          on = false;
        }

        btn.classList.toggle("is-active", on);
        btn.setAttribute("aria-pressed", on ? "true" : "false");
      });
    }

    // update after toolbar actions (your existing execCommand handler can remain)
    toolbar.addEventListener("click", () => setTimeout(update, 30));

    // update while typing / caret moves
    ["keyup", "mouseup", "input", "focus", "blur"].forEach((ev) => {
      editor.addEventListener(ev, () => setTimeout(update, 0));
    });

    // update when selection changes (only if in this editor)
    document.addEventListener("selectionchange", () => {
      if (selectionInside(editor)) update();
      else clearAll();
    });

    update();
  }

  function init() {
    document.querySelectorAll(".toolbar").forEach(bindToolbar);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  // if your HTML is injected later (rare here), still safe
  const mo = new MutationObserver(init);
  mo.observe(document.body, { childList: true, subtree: true });
})();

  /* ===== RTE toolbar & placeholder (robust) ===== */
  const rte = $('rte');
  if(rte){
    // toggle placeholder state and expose helper
    const togglePlaceholder = ()=> {
      try {
        const has = (rte.textContent||'').trim().length>0 || (rte.innerHTML||'').trim().length>0;
        rte.classList.toggle('has-content', has);
      } catch(e){ /* ignore */ }
    };
    ['input','keyup','paste','blur'].forEach(ev=> rte.addEventListener(ev, togglePlaceholder));
    togglePlaceholder();

    // observe DOM mutations so placeholder toggles when scripts change content
    try {
      const mo = new MutationObserver(togglePlaceholder);
      mo.observe(rte, { childList:true, subtree:true, characterData:true });
      // optional: keep reference for debugging
      window.__rteMutationObserver = mo;
    } catch(e){ /* ignore */ }

    const toolbar = $('rte_toolbar');
    if(toolbar){
      toolbar.addEventListener('click', (e)=>{
        const btn = e.target.closest('[data-cmd]');
        if(!btn) return;
        const cmd = btn.getAttribute('data-cmd');
        if(cmd === 'createLink'){
          const url = prompt('Enter URL (including https://):','https://');
          if(url && /^https?:\/\//i.test(url)) document.execCommand('createLink', false, url);
          return;
        }
        try { document.execCommand(cmd, false, null); } catch(e){ console.warn('execCommand failed',e); }
        rte.focus();
      });
    }

    const insertHeading = $('insertHeading');
    if(insertHeading){
      insertHeading.addEventListener('change', function(){
        const v=this.value;
        if(!v) return;
        if(v==='h2') document.execCommand('formatBlock', false, 'h2');
        else if(v==='p') document.execCommand('formatBlock', false, 'p');
        this.value='';
        rte.focus();
      });
    }
  }

  function collectRte(){
    try {
      const hidden = $('message_html');
      if(!hidden) return;
      if(rte) hidden.value = rte.innerHTML.trim();
      else hidden.value = '';
    } catch(e){ console.warn('collectRte failed', e); }
  }

  /* ===== attachments (same as study material) ===== */
  const dz = $('dz'), input = $('attachments'), list = $('fileList');
  let dt = new DataTransfer();

  // NEW: URLs selected from Notice Library
  let libraryUrls = [];

  // map to keep object URLs created for preview (to revoke later)
  const previewObjectURLs = new Set();

  function bytes(n){
    if(!n) return '0 B';
    if(n>=1<<30) return (n/(1<<30)).toFixed(1)+' GB';
    if(n>=1<<20) return (n/(1<<20)).toFixed(1)+' MB';
    if(n>=1<<10) return (n/(1<<10)).toFixed(1)+' KB';
    return n+' B';
  }

  function revokeAllPreviewURLs(){
    previewObjectURLs.forEach(u=>{
      try{ URL.revokeObjectURL(u); } catch(e){/*ignore*/ }
    });
    previewObjectURLs.clear();
  }

  function previewFile(file) {
    if(!file) return;
    const previewContent = $('previewContent');
    const previewTitle = $('previewModalTitle');
    const downloadBtn = $('downloadPreview');
    previewTitle && (previewTitle.textContent = `Preview: ${file.name}`);
    const fileUrl = URL.createObjectURL(file);
    previewObjectURLs.add(fileUrl);
    if(downloadBtn){ downloadBtn.href = fileUrl; downloadBtn.download = file.name; downloadBtn.style.display = 'inline-block'; }
    if(previewContent) previewContent.innerHTML = '';
    const fileType = file.type || '';
    const isImage = fileType.startsWith('image/');
    const isPDF = fileType === 'application/pdf';
    const isText = fileType.startsWith('text/');
    if (isImage && previewContent) {
      const img = document.createElement('img');
      img.src = fileUrl; img.alt = file.name; img.className = 'preview-image';
      previewContent.appendChild(img);
    } else if (isPDF && previewContent) {
      const embed = document.createElement('embed');
      embed.src = fileUrl; embed.type = 'application/pdf'; embed.className = 'preview-pdf';
      previewContent.appendChild(embed);
    } else if (isText && previewContent) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const pre = document.createElement('pre');
        pre.className = 'preview-text';
        pre.textContent = e.target.result;
        previewContent.appendChild(pre);
      };
      reader.readAsText(file);
    } else if(previewContent) {
      previewContent.innerHTML = `
        <div class="p-4">
          <i class="fas fa-file fa-3x text-muted mb-3"></i>
          <p class="text-muted">Preview not available for this file type</p>
          <p class="small text-muted">File type: ${fileType || 'Unknown'}</p>
        </div>
      `;
    }
    if(previewModal) previewModal.show();
  }

    function handleFileAction(e) {
    const target = e.target;
    if(!target) return;
    const previewBtn = target.closest('.btn-preview');
    const deleteBtn = target.closest('.btn-delete');

    if (!previewBtn && !deleteBtn) return;

    const row = (previewBtn || deleteBtn).closest('.file-row');
    if (!row) return;
    const type  = row.dataset.type || 'local';
    const index = Number(row.dataset.index || -1);

    if (previewBtn) {
      e.preventDefault(); e.stopPropagation();
      if (type === 'local') {
        const file = dt.files[index];
        if (file) previewFile(file);
      } else if (type === 'lib') {
        const url = libraryUrls[index];
        if (url) window.open(url, '_blank');
      }
      return;
    }

    if (deleteBtn) {
      e.preventDefault(); e.stopPropagation();
      if (type === 'local') {
        const next = new DataTransfer();
        Array.from(dt.files).forEach((ff,i)=>{ if(i!==index) next.items.add(ff); });
        dt = next; if(input) input.files = dt.files;
      } else if (type === 'lib') {
        if (index >= 0 && index < libraryUrls.length) {
          libraryUrls.splice(index, 1);
        }
      }
      redraw();
    }
  }

  function redraw(){
    if(!list) return;
    list.innerHTML='';

    // 1) Local uploaded files
    Array.from(dt.files).forEach((f,idx)=>{
      const row = document.createElement('div');
      row.className = 'file-row';
      row.dataset.type  = 'local';
      row.dataset.index = String(idx);

      const n = document.createElement('div');
      n.className = 'name';
      n.textContent = f.name;
      n.title = f.name;

      const s = document.createElement('div');
      s.className = 'size';
      s.textContent = bytes(f.size);

      const btnGroup = document.createElement('div');
      btnGroup.className = 'btn-group';

      const previewBtn = document.createElement('button');
      previewBtn.className = 'btn-action btn-preview';
      previewBtn.type = 'button';
      previewBtn.innerHTML = '<i class="fa fa-eye"></i><span>Preview</span>';
      previewBtn.title = 'Preview file';

      const rm = document.createElement('button');
      rm.className = 'btn-action btn-delete';
      rm.type = 'button';
      rm.innerHTML = '<i class="fa fa-trash"></i><span>Delete</span>';
      rm.title = 'Remove file';

      btnGroup.appendChild(previewBtn);
      btnGroup.appendChild(rm);

      row.appendChild(n);
      row.appendChild(s);
      row.appendChild(btnGroup);
      list.appendChild(row);
    });

    // 2) Library-selected URLs
    libraryUrls.forEach((u, idx) => {
      const row = document.createElement('div');
      row.className = 'file-row';
      row.dataset.type  = 'lib';
      row.dataset.index = String(idx);

      const name = (u || '').split('/').pop() || u;

      const n = document.createElement('div');
      n.className = 'name';
      n.textContent = name + ' (from library)';
      n.title = u;

      const s = document.createElement('div');
      s.className = 'size';
      s.textContent = 'From Notice Library';

      const btnGroup = document.createElement('div');
      btnGroup.className = 'btn-group';

      const previewBtn = document.createElement('button');
      previewBtn.className = 'btn-action btn-preview';
      previewBtn.type = 'button';
      previewBtn.innerHTML = '<i class="fa fa-eye"></i><span>Preview</span>';
      previewBtn.title = 'Open in new tab';

      const rm = document.createElement('button');
      rm.className = 'btn-action btn-delete';
      rm.type = 'button';
      rm.innerHTML = '<i class="fa fa-trash"></i><span>Delete</span>';
      rm.title = 'Remove from library selection';

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
      if(f.size>maxPer){ fErr('attachments',`"${f.name}" exceeds 50 MB.`); hasLargeFile = true; return; }
      dt.items.add(f);
    });
    if(input) input.files = dt.files;
    redraw();
    if (!hasLargeFile) fErr('attachments', '');
  }

  if(dz){ dz.addEventListener('click', ()=> input && input.click()); }
  if(input){ input.addEventListener('change', ()=> addFiles(input.files)); }
  ;['dragenter','dragover'].forEach(ev=> { if(dz) dz.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dz.classList.add('drag'); }); });
  ;['dragleave','dragend','drop'].forEach(ev=> { if(dz) dz.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dz.classList.remove('drag'); }); });
  if(dz) dz.addEventListener('drop', e=> addFiles(e.dataTransfer && e.dataTransfer.files));
if ($('btnClearAll')) {
  $('btnClearAll').addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();   // ⬅️ stop click bubbling to #dz (so no file dialog)

    dt = new DataTransfer();
    if (input) {
      input.value = '';
      input.files = dt.files;
    }

    libraryUrls = [];
    redraw();
    fErr('attachments', '');
    revokeAllPreviewURLs?.();
  });
}
  if(list) list.addEventListener('click', handleFileAction);
   

  let noticeLibModalEl = null;

  function ensureNoticeLibModal(){
    if (noticeLibModalEl) return noticeLibModalEl;

    const m = document.createElement('div');
    m.className = 'modal fade';
    m.id = 'noticeLibraryModal';
    m.tabIndex = -1;
    m.innerHTML = `
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fa fa-book me-2"></i>Notice Library
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body" style="min-height:180px;">
            <div id="noticeLibLoader" class="p-3">
              <div class="d-flex align-items-center gap-2">
                <div class="spin" aria-hidden="true"></div>
                <div class="text-muted">Loading notice files…</div>
              </div>
            </div>

            <div id="noticeLibEmpty" class="text-muted small p-3" style="display:none;">
              No files found in notices.
            </div>

            <!-- Search bar -->
            <div id="noticeLibSearchContainer" class="mb-3" style="display:none;">
              <div class="input-group input-group-sm">
                <span class="input-group-text">
                  <i class="fa fa-search"></i>
                </span>
                <input type="text" id="noticeLibSearch" class="form-control" placeholder="Search by file name, notice title, or URL…">
                <button type="button" id="noticeLibClearSearch" class="btn btn-outline-secondary" style="display:none;">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div id="noticeLibSearchResults" class="small text-muted mt-1" style="display:none;"></div>
            </div>

            <div id="noticeLibList" style="display:none;"></div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="noticeLibConfirm" class="btn btn-primary">Add selected</button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(m);
    noticeLibModalEl = m;

    // one-time styles for card grid
    if (!document.getElementById('notice-lib-card-css')) {
      const style = document.createElement('style');
      style.id = 'notice-lib-card-css';
      style.textContent = `
        #noticeLibList .notice-lib-grid {
          display: grid;
          gap: 12px;
          grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        @media (max-width: 1024px) {
          #noticeLibList .notice-lib-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
          }
        }
        @media (max-width: 640px) {
          #noticeLibList .notice-lib-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
          }
        }

        .notice-lib-card {
          position: relative;
          display: flex;
          flex-direction: column;
          gap: 8px;
          padding: 10px;
          border-radius: 10px;
          border: 1px solid rgba(0,0,0,0.08);
          background: #fff;
          box-shadow: 0 1px 2px rgba(15,23,42,0.04);
          min-height: 170px;
          overflow: hidden;
          cursor: pointer;
        }
        .notice-lib-card:hover {
          box-shadow: 0 2px 6px rgba(15,23,42,0.10);
          border-color: rgba(59,130,246,0.35);
        }

        .notice-lib-thumb {
          width: 100%;
          height: 110px;
          border-radius: 8px;
          object-fit: cover;
          background: linear-gradient(180deg,#f8fafc,#ffffff);
        }
        .notice-lib-placeholder {
          width: 100%;
          height: 110px;
          border-radius: 8px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 30px;
          color: rgba(100,116,139,0.9);
          background: linear-gradient(180deg,#f9fafb,#ffffff);
        }

        .notice-lib-checkbox-wrap {
          position: absolute;
          top: 8px;
          left: 8px;
          z-index: 5;
          background: rgba(255,255,255,0.95);
          border-radius: 999px;
          padding: 4px 7px;
          box-shadow: 0 1px 3px rgba(15,23,42,0.18);
        }

        .notice-lib-card .card-name {
          font-size: 13px;
          font-weight: 600;
          margin-top: 6px;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        .notice-lib-card .card-refs {
          font-size: 12px;
          color: var(--muted-color, #6b7280);
          max-height: 3.2em;
          overflow: hidden;
        }
        .notice-lib-card .card-footer {
          margin-top: auto;
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 8px;
          font-size: 12px;
        }

        .notice-lib-highlight {
          background: rgba(250,204,21,0.4);
          border-radius: 2px;
          padding: 0 2px;
        }
      `;
      document.head.appendChild(style);
    }

    return m;
  }

  function normalizeNoticeAttachments(row){
    let raw = row.attachment ?? row.attachments ?? row.attachments_json ?? [];
    if (typeof raw === 'string' && raw.trim() !== '') {
      try { raw = JSON.parse(raw); }
      catch(e){
        const parts = raw.split(/\s*,\s*|\s*\|\|\s*/).filter(Boolean);
        raw = parts.length ? parts : [];
      }
    }
    if (raw && !Array.isArray(raw)) raw = [raw];

    return (raw || []).map((a, idx) => {
      if (typeof a === 'string') {
        const url = a;
        return {
          url,
          name: (url.split('/').pop() || `file-${idx+1}`),
          ext: (url.split('?')[0].split('.').pop() || '').toLowerCase()
        };
      }
      const url = a.signed_url || a.url || a.path || '';
      const name = a.name || (url.split('/').pop() || a.original_name || `file-${idx+1}`);
      const ext = (a.ext || a.extension || (url.split('?')[0].split('.').pop() || '')).toLowerCase();
      return { url, name, ext };
    }).filter(it => !!it.url);
  }

  async function openNoticeLibraryPicker(onSelected){
    const modalEl = ensureNoticeLibModal();
    const libLoader       = modalEl.querySelector('#noticeLibLoader');
    const libEmpty        = modalEl.querySelector('#noticeLibEmpty');
    const libList         = modalEl.querySelector('#noticeLibList');
    const libConfirm      = modalEl.querySelector('#noticeLibConfirm');
    const searchContainer = modalEl.querySelector('#noticeLibSearchContainer');
    const searchInput     = modalEl.querySelector('#noticeLibSearch');
    const clearSearchBtn  = modalEl.querySelector('#noticeLibClearSearch');
    const searchResults   = modalEl.querySelector('#noticeLibSearchResults');

    libLoader.style.display = '';
    libEmpty.style.display  = 'none';
    libList.style.display   = 'none';
    libList.innerHTML       = '';
    searchContainer.style.display = 'none';
    searchInput.value       = '';
    clearSearchBtn.style.display = 'none';
    searchResults.style.display  = 'none';
    libConfirm.disabled     = true;

    // show modal
    let bsModal = null;
    try {
      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
      } else {
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
      }
    } catch(e){}

    const esc = (s)=> String(s || '').replace(/[&<>"'`=\/]/g, ch => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60','=':'&#x3D;'
    }[ch] || ch));

    const extIcon = (ext)=>{
      ext = (ext || '').toLowerCase();
      if (['png','jpg','jpeg','webp','gif','svg'].includes(ext)) return '<i class="fa fa-file-image"></i>';
      if (ext === 'pdf') return '<i class="fa fa-file-pdf"></i>';
      if (['mp4','webm','ogg','mov'].includes(ext)) return '<i class="fa fa-file-video"></i>';
      if (['doc','docx','rtf','odt'].includes(ext)) return '<i class="fa fa-file-word"></i>';
      if (['xls','xlsx','csv','ods'].includes(ext)) return '<i class="fa fa-file-excel"></i>';
      if (['ppt','pptx','odp'].includes(ext)) return '<i class="fa fa-file-powerpoint"></i>';
      return '<i class="fa fa-file"></i>';
    };

    const highlight = (text, term)=>{
      if (!term) return esc(text);
      const safe = esc(text);
      const re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')','gi');
      return safe.replace(re, '<span class="notice-lib-highlight">$1</span>');
    };

    try {
      // optional filters by course / batch
      const course_id = $('course_id') ? $('course_id').value : '';
      const batch_id  = $('batch_id') ? $('batch_id').value : '';

      const params = [];
      if (course_id) params.push('course_id=' + encodeURIComponent(course_id));
      if (batch_id)  params.push('batch_id=' + encodeURIComponent(batch_id));

      const url = API_NOTICES + (params.length ? ('?' + params.join('&')) : '');
      const data = await loadJSON(url);
      const rows = Array.isArray(data) ? data : (data.data || data.items || data.rows || []);

      const docMap = new Map();

      (rows || []).forEach(n => {
        const noticeTitle = n.title || 'Untitled';
        const atts = normalizeNoticeAttachments(n);
        atts.forEach(att => {
          const baseUrl = att.url.split('?')[0];
          if (!baseUrl) return;
          if (!docMap.has(baseUrl)) {
            docMap.set(baseUrl, {
              url: att.url,
              baseUrl,
              name: att.name,
              ext: att.ext,
              refs: [noticeTitle]
            });
          } else {
            const e = docMap.get(baseUrl);
            if (!e.refs.includes(noticeTitle)) e.refs.push(noticeTitle);
          }
        });
      });

      libLoader.style.display = 'none';

      const items = Array.from(docMap.values());
      if (!items.length) {
        libEmpty.style.display = '';
        return;
      }

      searchContainer.style.display = '';
      function isImageExt(ext) {
  return ['jpg','jpeg','png','gif','webp','avif','svg'].includes(
    String(ext || '').toLowerCase()
  );
}

function resolveFileUrl(u) {
  const raw = (u || '').trim();
  if (!raw) return '';

  // absolute http(s)
  if (/^https?:\/\//i.test(raw)) return raw;

  try {
    // make it absolute to current origin
    return new URL(raw.replace(/^\/+/, '/'), window.location.origin).href;
  } catch (e) {
    console.warn('resolveFileUrl failed for', raw, e);
    return raw;
  }
}

      function renderCards(list, term) {
  const selectedSet = new Set((libraryUrls || []).map(u => String(u)));

  const gridHtml = list.map((it, idx) => {
    const fullUrl = resolveFileUrl(it.url || '');
    const checked = selectedSet.has(it.url) || selectedSet.has(fullUrl) ? 'checked' : '';

    const shortRefs = (it.refs || []).slice(0, 2).join(', ');
    const more = Math.max(0, (it.refs || []).length - 2);
    const refsText = shortRefs + (more ? `, +${more} more` : '');
    const fileName = it.name || (it.baseUrl.split('/').pop() || 'file');

    const isImg = isImageExt(it.ext) && fullUrl;

    const placeholderHtml = isImg
      ? `<div class="notice-lib-thumb">
           <img src="${esc(fullUrl)}" alt="${esc(fileName)}">
         </div>`
      : `<div class="notice-lib-placeholder">
           ${extIcon(it.ext)}
         </div>`;

    return `
      <div class="notice-lib-card position-relative" data-url="${esc(fullUrl)}" data-idx="${idx}">
        <div class="notice-lib-checkbox-wrap">
          <input type="checkbox" class="form-check-input notice-lib-checkbox" data-url="${esc(fullUrl)}" ${checked}>
        </div>

        ${placeholderHtml}

        <div class="card-name" title="${esc(fileName)}">
          ${term ? highlight(fileName, term) : esc(fileName)}
        </div>

        <div class="card-refs" title="${esc((it.refs || []).join(', '))}">
          ${term ? highlight(refsText, term) : esc(refsText)}
        </div>

        <div class="card-footer">
          <button type="button" class="btn btn-sm btn-outline-primary notice-lib-preview-btn">
            <i class="fa fa-arrow-up-right-from-square me-1"></i> Preview
          </button>
          <div class="text-muted small">${esc(String((it.refs || []).length))} notice(s)</div>
        </div>
      </div>
    `;
  }).join('');

  libList.innerHTML = `<div class="notice-lib-grid">${gridHtml}</div>`;
  libList.style.display = '';

  const grid = libList.querySelector('.notice-lib-grid');
  if (!grid) return;

  function updateConfirm() {
    const anyChecked = !!grid.querySelector('.notice-lib-checkbox:checked');
    libConfirm.disabled = !anyChecked;
  }

  grid.querySelectorAll('.notice-lib-card').forEach(card => {
    const cb = card.querySelector('.notice-lib-checkbox');
    const previewBtn = card.querySelector('.notice-lib-preview-btn');
    const url = card.dataset.url;

    card.addEventListener('click', (ev) => {
      if (ev.target === cb || ev.target.closest('.notice-lib-preview-btn')) return;
      cb.checked = !cb.checked;
      updateConfirm();
    });

    if (cb) {
      cb.addEventListener('change', updateConfirm);
    }

    if (previewBtn) {
      previewBtn.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        if (url) window.open(url, '_blank');
      });
    }
  });

  updateConfirm();
}

      // initial render
      renderCards(items, '');

      // search behavior
      let searchTimer = null;
      searchInput.addEventListener('input', ()=>{
        clearTimeout(searchTimer);
        const term = searchInput.value.trim().toLowerCase();
        if (!term) {
          clearSearchBtn.style.display = 'none';
          searchResults.style.display  = 'none';
          renderCards(items, '');
          return;
        }
        clearSearchBtn.style.display = '';
        searchTimer = setTimeout(()=>{
          const filtered = items.filter(it => {
            const text = [
              it.name || '',
              (it.refs || []).join(' '),
              it.url || ''
            ].join(' ').toLowerCase();
            return text.includes(term);
          });
          searchResults.style.display = '';
          searchResults.textContent = `Found ${filtered.length} of ${items.length} files`;
          renderCards(filtered, term);
        }, 200);
      });

      clearSearchBtn.addEventListener('click', ()=>{
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        searchResults.style.display  = 'none';
        renderCards(items, '');
        searchInput.focus();
      });

      // confirm selection
      libConfirm.onclick = ()=>{
        const selected = Array.from(libList.querySelectorAll('.notice-lib-checkbox:checked'))
          .map(cb => cb.dataset.url)
          .filter(Boolean);

        if (selected.length && typeof onSelected === 'function') {
          onSelected(selected);
        }

        try {
          if (bsModal) bsModal.hide();
          else {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
          }
        } catch(e){}
      };

    } catch(e){
      console.error('Notice library load failed:', e);
      libLoader.style.display = 'none';
      libEmpty.style.display  = '';
      libEmpty.textContent = 'Unable to load notice files.';
    }
  }

  // Wire "Choose from Library" button (already added in HTML)
  if ($('btnChooseFromLibrary')) {
  $('btnChooseFromLibrary').addEventListener('click', (e)=>{
    e.preventDefault();
    e.stopPropagation();  

    openNoticeLibraryPicker((urls)=>{
      const set = new Set(libraryUrls);
      (urls || []).forEach(u => { if (u) set.add(u); });
      libraryUrls = Array.from(set);
      redraw();
    });
  });
}

  /* When preview modal hides, revoke object URLs we created for previews */
  if($('previewModal')){
    $('previewModal').addEventListener('hidden.bs.modal', function() {
      try {
        // revoke all URLs we created for previews
        revokeAllPreviewURLs();
      } catch(e){ console.warn(e); }
      const downloadBtn = $('downloadPreview');
      if (downloadBtn){ downloadBtn.style.display = 'none'; downloadBtn.removeAttribute('href'); }
      const previewContent = $('previewContent');
      if(previewContent) previewContent.innerHTML = '<div class="p-4"><i class="fas fa-file fa-3x text-muted mb-3"></i><p class="text-muted">Select a file to preview</p></div>';
    });
  }

  /* ===== submit ===== */
  if($('btnSave')){
    $('btnSave').addEventListener('click', async ()=>{
      clrErr();
      const course_id = $('course_id') ? $('course_id').value : '';
      const course_module_id = $('course_module_id') ? $('course_module_id').value : '';
      const batch_id = $('batch_id') ? $('batch_id').value : '';
      const title = ($('title')? $('title').value : '').trim();
      collectRte();
      const message_html = $('message_html') ? $('message_html').value : '';

      let hasErr=false;
      if(!course_id){ fErr('course_id','Course is required.'); hasErr=true; }
      // if(!batch_id){ fErr('batch_id','Batch is required.'); hasErr=true; }
      if(!title){ fErr('title','Title is required.'); hasErr=true; }
      if(hasErr) return;

            const fd = new FormData();
      fd.append('course_id', course_id);
      if(course_module_id) fd.append('course_module_id', course_module_id);
      if(batch_id) fd.append('batch_id', batch_id);
      fd.append('title', title);
      if(message_html) fd.append('message_html', message_html);
      Array.from(dt.files).forEach(f=> fd.append('attachments[]', f, f.name));

      // NEW: library URLs
      (libraryUrls || []).forEach(u => {
        if (u) fd.append('library_urls[]', u);
      });


      setSaving(true);
      try{
        const res = await fetch(API_NOTICES, { method:'POST', headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }, body:fd });
        const json = await res.json().catch(()=> ({}));
        if(res.ok){ ok('Notice created'); setTimeout(()=> location.replace('/admin/notice/manage'), 700); return; }
        if(res.status===422){
          const e = json.errors || {};
          if(e['attachments.*']) fErr('attachments', Array.isArray(e['attachments.*'])? e['attachments.*'][0] : String(e['attachments.*']));
          ['course_id','course_module_id','batch_id','title','message_html','attachments'].forEach(k=> e[k] && fErr(k, Array.isArray(e[k])? e[k][0] : String(e[k])));
          err(json.message || 'Please fix the highlighted fields.'); return;
        }
        if(res.status===403){ Swal.fire({icon:'error',title:'Unauthorized',html:'Your role lacks permission for this endpoint.'}); return; }
        Swal.fire('Save failed', json.message || ('HTTP '+res.status), 'error');
      }catch(ex){
        console.error(ex);
        Swal.fire('Network error','Please check your connection and try again.','error');
      }finally{
        setSaving(false);
      }
    });
  }

  // boot
  initDropdowns();
})();
</script>

@endpush
