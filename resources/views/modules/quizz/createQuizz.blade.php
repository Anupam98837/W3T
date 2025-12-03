{{-- resources/views/modules/quizz/createQuizz.blade.php --}}
@section('title','Create Quizz')

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

  /* Image tabs + dropzone */
  .nav-tabs .nav-link{border:1px solid var(--line-strong);border-bottom-color:transparent;border-top-left-radius:10px;border-top-right-radius:10px;background:var(--surface)}
  .nav-tabs .nav-link.active{border-color:var(--primary-color);color:var(--ink)}
  .tab-pane{border:1px solid var(--line-strong);border-top:none;border-bottom-left-radius:12px;border-bottom-right-radius:12px;padding:14px;background:var(--surface)}

  .dropzone{
    display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;
    border:2px dashed var(--line-strong);border-radius:14px;background:var(--surface-2, #fff);
    padding:24px; transition:border-color .18s ease, background .18s ease;
  }
  .dropzone:hover{border-color:var(--primary-color);background:color-mix(in oklab, var(--primary-color) 7%, transparent)}
  .dropzone.dragover{border-color:var(--primary-color);box-shadow:0 0 0 3px color-mix(in oklab, var(--primary-color) 15%, transparent)}
  .drop-icon{width:56px;height:56px;border-radius:999px;border:1px dashed var(--line-strong);display:flex;align-items:center;justify-content:center;margin-bottom:10px;opacity:.9}
  .drop-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .img-prev{width:100%;max-width:520px;aspect-ratio:16/10;border:1px dashed var(--line-strong);border-radius:12px;background:#f6f7f9 center/cover no-repeat;margin-top:12px}

  /* Button loading state */
  .btn-loading{pointer-events:none;opacity:.85}
  .btn-loading .btn-label{visibility:hidden}
  .btn-loading .btn-spinner{display:inline-block !important}

  .btn-spinner{display:none; width:1rem;height:1rem;border:.2rem solid #0001;border-top-color:#fff;border-radius:50%;vertical-align:-.125em;animation:rot 1s linear infinite}
  .btn-light .btn-spinner{border-top-color:#0009}

  /* Dark mode parity */
  html.theme-dark .rte{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .tool{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .tab-pane{background:#0f172a}
  html.theme-dark .dropzone{background:#0f172a;border-color:var(--line-strong)}
  html.theme-dark .img-prev{background:#0b1220}
</style>

@section('content')
<div class="qz-wrap">
  <div class="card qz">
    <div class="card-header">
      <div class="qz-head">
        <i class="fa-solid fa-square-poll-horizontal"></i>
        <strong id="pageTitle">Create Quizz</strong>
        <span class="hint" id="hint">— Fill title, custom description, instructions, image & schedule.</span>
      </div>
    </div>

    <div class="card-body position-relative">
      <div class="dim" id="busy"><div class="spin" aria-label="Saving…"></div></div>

      {{-- Basics --}}
      <h3 class="section-title">Basics</h3>
      <div class="divider-soft"></div>

      <div class="mb-3">
        <label class="form-label" for="quiz_name">Quiz Title <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
          <input id="quiz_name" class="form-control" type="text" maxlength="255" placeholder="e.g., DSA Fundamentals Quiz" autocomplete="off">
        </div>
        <div class="err" data-for="quiz_name"></div>
      </div>

      <div class="mb-3">
        <label class="form-label d-block">Custom Description</label>
        <div class="toolbar" aria-label="Description toolbar">
          <button class="tool" type="button" data-cmd="bold" aria-label="Bold"><i class="fa-solid fa-bold"></i></button>
          <button class="tool" type="button" data-cmd="italic" aria-label="Italic"><i class="fa-solid fa-italic"></i></button>
          <button class="tool" type="button" data-cmd="underline" aria-label="Underline"><i class="fa-solid fa-underline"></i></button>
          <button class="tool" type="button" data-format="H2">H2</button>
          <button class="tool" type="button" data-format="H3">H3</button>
          <button class="tool" type="button" data-cmd="insertUnorderedList" aria-label="Bulleted list"><i class="fa-solid fa-list-ul"></i></button>
          <button class="tool" type="button" data-cmd="insertOrderedList" aria-label="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
          <button class="tool" type="button" id="btnLinkDesc" aria-label="Insert link"><i class="fa-solid fa-link"></i></button>
        </div>
        <div class="rte-wrap">
          <div id="quiz_description" class="rte" contenteditable="true" spellcheck="true"></div>
          <div class="rte-ph">Write the quiz description (HTML allowed)…</div>
        </div>
        <div class="err" data-for="quiz_description"></div>
      </div>

      <div class="mb-3">
        <label class="form-label d-block">Instructions (shown before start)</label>
        <div class="toolbar" aria-label="Instructions toolbar">
          <button class="tool" type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
          <button class="tool" type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
          <button class="tool" type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
          <button class="tool" type="button" data-format="H2">H2</button>
          <button class="tool" type="button" data-format="H3">H3</button>
          <button class="tool" type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
          <button class="tool" type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
          <button class="tool" type="button" id="btnLinkInst"><i class="fa-solid fa-link"></i></button>
          <span class="tiny">Tip: rules, negative marking, timeouts, etc.</span>
        </div>
        <div class="rte-wrap">
          <div id="instructions" class="rte" contenteditable="true" spellcheck="true"></div>
          <div class="rte-ph">Write the instructions to show candidates…</div>
        </div>
        <div class="err" data-for="instructions"></div>
      </div>

      {{-- Image --}}
      <h3 class="section-title">Quiz Image</h3>
      <div class="divider-soft"></div>
      <ul class="nav nav-tabs" id="imgTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#filePane" type="button" role="tab" aria-controls="filePane" aria-selected="true">Upload File</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="url-tab" data-bs-toggle="tab" data-bs-target="#urlPane" type="button" role="tab" aria-controls="urlPane" aria-selected="false">Use URL</button>
        </li>
      </ul>

      <div class="tab-content mt-0" id="imgTabContent">
        {{-- File Pane with drag & drop --}}
        <div class="tab-pane fade show active" id="filePane" role="tabpanel" aria-labelledby="file-tab">
          <div id="dropzone" class="dropzone" aria-label="Image dropzone">
            <div class="drop-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
            <div class="lead fw-semibold">Drag & drop your image here</div>
            <div class="tiny mt-1">JPG, PNG, GIF, WEBP, AVIF • up to 4 MB</div>
            <div class="drop-actions">
              <label class="btn btn-outline-primary mb-0" for="quiz_img_file">
                <i class="fa fa-file-image me-1"></i>Select file
              </label>
              <input id="quiz_img_file" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,.avif" hidden>
              <button type="button" id="btnChooseImageFromLibrary" class="btn btn-outline-secondary ms-2">
                <i class="fa fa-book me-1"></i> Choose from Library
              </button>
              <button type="button" id="btnClearFile" class="btn btn-light">Clear</button>
            </div>
            <div id="filePrev" class="img-prev" aria-label="File preview"></div>
          </div>
          <div class="err" data-for="quiz_img"></div>
        </div>

        {{-- URL Pane --}}
        <div class="tab-pane fade" id="urlPane" role="tabpanel" aria-labelledby="url-tab">
          <div class="row g-3 align-items-end">
            <div class="col-md-8">
              <label class="form-label" for="quiz_img_url">Image URL (https://)</label>
              <input id="quiz_img_url" class="form-control" type="url" placeholder="https://…/cover.webp" autocomplete="off">
            </div>
            <div class="col-md-4">
              <button id="btnUrlPreview" class="btn btn-primary w-100" type="button"><i class="fa fa-eye me-1"></i>Preview</button>
            </div>
            <div class="col-12">
              <div id="urlPrev" class="img-prev" aria-label="URL preview"></div>
            </div>
          </div>
          <div class="err" data-for="quiz_img_url"></div>
        </div>
      </div>

      {{-- Visibility & Scheduling --}}
      <h3 class="section-title mt-4">Visibility & Scheduling</h3>
      <div class="divider-soft"></div>
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label" for="is_public">Public?</label>
          <select id="is_public" class="form-select">
            <option value="no" selected>No</option>
            <option value="yes">Yes</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="result_set_up_type">Result Setup</label>
          <select id="result_set_up_type" class="form-select">
            <option value="Immediately" selected>Immediately</option>
            <option value="Now">Now</option>
            <option value="Schedule">Schedule</option>
          </select>
          <div class="tiny mt-1">Choose <b>Schedule</b> to reveal on a date.</div>
        </div>
        <div class="col-md-5">
          <label class="form-label" for="result_release_date">Result Release Date</label>
          <input id="result_release_date" class="form-control" type="datetime-local" disabled>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-3">
          <label class="form-label" for="total_time">Total Time (minutes)</label>
          <input id="total_time" class="form-control" type="number" min="1" placeholder="60">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="total_attempts">Attempts Allowed</label>
          <input id="total_attempts" class="form-control" type="number" min="1" value="1">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="status">Status</label>
          <select id="status" class="form-select">
            <option value="active" selected>Active</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="note">Note (optional)</label>
          <input id="note" class="form-control" type="text" maxlength="255" placeholder="Short internal note">
        </div>
      </div>

      {{-- Actions --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <a id="cancel" class="btn btn-light" href="/admin/quizz/manage">Cancel</a>
        <button id="btnSave" class="btn btn-primary" type="button">
          <span class="btn-spinner" aria-hidden="true"></span>
          <span class="btn-label"><i class="fa fa-floppy-disk me-1"></i> <span id="saveBtnText">Create Quiz</span></span>
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

  const backList = '/admin/quizz/manage';
  const API_BASE = '/api/quizz';              // quiz API (single + list)
  // We will use API_BASE itself as the quiz image library source

  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  // small helpers used by library picker
  function escapeHtml(str){
    return String(str || '').replace(/[&<>"'`=\/]/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60','=':'&#x3D;'
    }[s] || s));
  }
  function extOf(u){
    try { return (u || '').split('?')[0].split('.').pop().toLowerCase(); } catch(e){ return ''; }
  }
  function isImageExt(e){
    return ['png','jpg','jpeg','gif','webp','avif','svg'].includes(e);
  }

  // detect Edit mode (?edit=<uuid or id>)
  const url_ = new URL(location.href);
  const editKey = url_.searchParams.get('edit');
  const isEdit = !!editKey;
  let currentUUID = editKey || null;

  if(isEdit){
    $('pageTitle').textContent = 'Edit Quiz';
    $('saveBtnText').textContent = 'Update Quiz';
    $('hint').textContent = '— Update quiz details, description, instructions & schedule.';
    // load quiz to form
    loadQuiz(editKey).catch((e)=> {
      console.error(e);
      Swal.fire('Not found','Could not load quiz for editing.','error')
        .then(()=> location.replace(backList));
    });
  }

  /* ===== enable/disable form during save ===== */
  function setFormDisabled(disabled){
    document.querySelectorAll('.card-body input, .card-body select, .card-body button, .card-body textarea, .card-body .tool, .card-body .nav-link')
      .forEach(el=>{
        if (el.id === 'cancel') return; // keep Cancel clickable
        if (el.id === 'btnSave') return; // handled separately
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

  /* ===== wire RTE (desc + instructions) ===== */
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
  wireRTE('quiz_description','btnLinkDesc');
  wireRTE('instructions','btnLinkInst');

  /* ===== Image: drag & drop + preview + URL preview ===== */
  const drop = $('dropzone');
  const fileInput = $('quiz_img_file');
  const filePrev  = $('filePrev');
  const btnClear  = $('btnClearFile');
  const urlInput  = $('quiz_img_url');
  const urlPrev   = $('urlPrev');
  const btnUrlPreview = $('btnUrlPreview');

  function clearFile(){
    fileInput.value = '';
    filePrev.style.backgroundImage = '';
  }

  function setPreviewFromFile(f){
    const okType = /image\/(jpeg|png|gif|webp|avif)/i.test(f.type);
    if(!okType){ err('Unsupported file type'); return; }
    if(f.size > 4*1024*1024){ err('File too large (max 4MB)'); return; }
    const reader = new FileReader();
    reader.onload = e => { filePrev.style.backgroundImage = `url('${e.target.result}')`; };
    reader.readAsDataURL(f);
  }

  ;['dragenter','dragover'].forEach(ev=>{
    drop.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); drop.classList.add('dragover'); });
  });
  ;['dragleave','dragend','drop'].forEach(ev=>{
    drop.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); drop.classList.remove('dragover'); });
  });
  drop.addEventListener('drop', e=>{
    const f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
    if(f){ setPreviewFromFile(f); fileInput.files = e.dataTransfer.files; }
  });

  fileInput.addEventListener('change', ()=>{
    const f = fileInput.files && fileInput.files[0];
    if(!f){ clearFile(); return; }
    setPreviewFromFile(f);
  });
  btnClear.addEventListener('click', clearFile);

  btnUrlPreview.addEventListener('click', () => {
  let u = (urlInput.value || '').trim();
  if (!u) {
    err('Provide an image path or URL'); 
    return;
  }

  // if it's not http(s), treat as relative to the app origin
  const hasHttp = /^https?:\/\//i.test(u);
  const fullUrl = hasHttp ? u : new URL(u.replace(/^\/+/, ''), window.location.origin + '/').href;

  urlPrev.style.backgroundImage = `url('${fullUrl}')`;
});

  /* ===== Visibility/schedule wiring ===== */
  const resultType = $('result_set_up_type');
  const resultDate = $('result_release_date');
  function syncRelease(){
    const on = resultType.value === 'Schedule';
    resultDate.disabled = !on;
    if(!on) resultDate.value = '';
  }
  resultType.addEventListener('change', syncRelease); syncRelease();

  /* ===== errors ===== */
  function fErr(field,msg){ const el=document.querySelector(`.err[data-for="${field}"]`); if(el){ el.textContent=msg||''; el.style.display=msg?'block':'none'; } }
  function clrErr(){ document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }

  /* ===== payload builder (no pricing, no assoc fields) ===== */
  function buildCommonPayload(){
    return {
      quiz_name:            ($('quiz_name').value||'').trim(),
      quiz_description:     ($('quiz_description').innerHTML||'').trim() || null,
      instructions:         ($('instructions').innerHTML||'').trim() || null,
      is_public:            $('is_public').value,
      result_set_up_type:   $('result_set_up_type').value,
      result_release_date:  $('result_release_date').value || null,
      total_time:           Number($('total_time').value||0) || null,
      total_attempts:       Number($('total_attempts').value||1) || 1,
      status:               $('status').value,
      note:                 ($('note').value||'').trim() || null
    };
  }

  function activeImgTab(){
    return document.querySelector('#imgTabs .nav-link.active')?.id === 'file-tab' ? 'file' : 'url';
  }

  /* ===== load (Edit mode) ===== */
  async function loadQuiz(key){
    $('busy').classList.add('show');
    try{
      const res = await fetch(`${API_BASE}/${encodeURIComponent(key)}`, {
        headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
      });
      const json = await res.json().catch(()=> ({}));
      
      if(!res.ok) throw new Error(json?.message || 'Load failed');
      
      const q = json?.data || json;
      if(!q) throw new Error('Quiz not found');

      currentUUID = q.uuid || key;

      // fill fields
      $('quiz_name').value = q.quiz_name || '';
      $('quiz_description').innerHTML = q.quiz_description || '';
      $('instructions').innerHTML = q.instructions || '';
      
      $('is_public').value = q.is_public || 'no';
      $('result_set_up_type').value = q.result_set_up_type || 'Immediately';
      
      if(q.result_release_date){
        const d = new Date(q.result_release_date);
        if(!isNaN(d)){
          $('result_release_date').value = d.toISOString().slice(0,16);
        }
      }
      
      $('total_time').value = q.total_time || '';
      $('total_attempts').value = q.total_attempts || 1;
      $('status').value = q.status || 'active';
      $('note').value = q.note || '';

      // Handle image preview if exists (DB holds in quiz_img)
      if(q.quiz_img){
        urlInput.value = q.quiz_img;
        urlPrev.style.backgroundImage = `url('${q.quiz_img}')`;
        // optionally set URL tab as active
        try{
          const urlTab = document.getElementById('url-tab');
          if (urlTab && window.bootstrap && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(urlTab).show();
          }
        }catch(e){}
      }

      // Update RTE placeholders
      document.querySelectorAll('.rte-ph').forEach(ph => {
        const editor = ph.previousElementSibling;
        const hasContent = (editor.textContent || '').trim().length > 0 || (editor.innerHTML||'').trim().length > 0;
        editor.classList.toggle('has-content', hasContent);
      });

      syncRelease();
      
    }catch(e){
      console.error(e);
      throw e;
    }finally{
      $('busy').classList.remove('show');
    }
  }

  /* ============================
   *   QUIZ IMAGE LIBRARY (cards)
   *   UI same as Study Materials
   *   BUT source = existing quizzes
   * ============================ */

  const btnChooseLib = $('btnChooseImageFromLibrary');
let libModalEl = null;

function ensureImageLibraryModal(){
  if (libModalEl) return libModalEl;
  const m = document.createElement('div');
  m.className = 'modal fade';
  m.id = 'quizImageLibraryModal';
  m.tabIndex = -1;
  m.innerHTML = `
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-book me-2"></i>Choose Image from Quiz Library</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="min-height:180px;">
          <div id="qzLibLoader" style="display:none; text-align:center; padding:20px;">
            <div class="spin mb-2"></div>
            <div class="text-muted small">Loading quiz image library…</div>
          </div>
          <div id="qzLibEmpty" class="text-muted small p-3" style="display:none;">No quiz images found yet.</div>
          
          <!-- Search Bar -->
          <div id="qzLibSearchContainer" class="mb-3" style="display:none;">
            <div class="input-group input-group-sm">
              <span class="input-group-text" id="search-addon">
                <i class="fa fa-search"></i>
              </span>
              <input 
                type="text" 
                id="qzLibSearch" 
                class="form-control" 
                placeholder="Search images by name or quiz title..." 
                aria-label="Search"
                aria-describedby="search-addon"
              />
              <button id="qzLibClearSearch" class="btn btn-outline-secondary" type="button" style="display:none;">
                <i class="fa fa-times"></i>
              </button>
            </div>
            <div id="qzLibSearchResults" class="small text-muted mt-2" style="display:none;"></div>
          </div>
          
          <div id="qzLibList" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button id="qzLibConfirm" type="button" class="btn btn-primary" disabled>Add image</button>
        </div>
      </div>
    </div>
  `;
  document.body.appendChild(m);

  // inject card styles once
  if (!document.getElementById('qz-lib-card-styles')) {
    const style = document.createElement('style');
    style.id = 'qz-lib-card-styles';
    style.textContent = `
      #qzLibList .sm-lib-grid { display:grid; gap:12px; grid-template-columns:repeat(3, 1fr); }
      @media (max-width:1024px){ #qzLibList .sm-lib-grid { grid-template-columns:repeat(2, 1fr); } }
      @media (max-width:640px){ #qzLibList .sm-lib-grid { grid-template-columns:repeat(1, 1fr); } }

      .sm-lib-card { display:flex; flex-direction:column; gap:8px; padding:10px; border-radius:10px; border:1px solid rgba(0,0,0,0.06); background:#fff; min-height:160px; position:relative; overflow:hidden; }
      .sm-lib-thumb { height:120px; width:100%; object-fit:cover; border-radius:8px; background:#f5f5f5; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.02); }
      .sm-lib-card .overlay-checkbox { position:absolute; top:10px; left:10px; z-index:5; background:rgba(255,255,255,0.95); padding:6px; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
      .sm-lib-card .card-name { margin-top:6px; font-weight:600; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
      .sm-lib-card .card-refs { font-size:12px; color:var(--muted-color); margin-top:4px; max-height:3.6em; overflow:hidden; }
      .sm-lib-card .card-actions { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:auto; }
      .sm-lib-placeholder-icon { width:100%; height:120px; display:flex; align-items:center; justify-content:center; font-size:36px; color:rgba(0,0,0,0.35); border-radius:8px; background: linear-gradient(180deg,#fafafa,#fff); }
      
      /* Search highlighting */
      .highlight { background-color: rgba(255, 255, 0, 0.3); padding: 0 1px; border-radius: 2px; }
    `;
    document.head.appendChild(style);
  }

  libModalEl = m;
  return libModalEl;
}

async function openImageLibraryPicker(){
  const modalEl = ensureImageLibraryModal();
  const libList   = modalEl.querySelector('#qzLibList');
  const libLoader = modalEl.querySelector('#qzLibLoader');
  const libEmpty  = modalEl.querySelector('#qzLibEmpty');
  const libConfirm= modalEl.querySelector('#qzLibConfirm');
  const searchContainer = modalEl.querySelector('#qzLibSearchContainer');
  const searchInput = modalEl.querySelector('#qzLibSearch');
  const clearSearchBtn = modalEl.querySelector('#qzLibClearSearch');
  const searchResults = modalEl.querySelector('#qzLibSearchResults');

  libList.innerHTML = '';
  libLoader.style.display = '';
  libList.style.display   = 'none';
  libEmpty.style.display  = 'none';
  searchContainer.style.display = 'none';
  searchInput.value = '';
  clearSearchBtn.style.display = 'none';
  searchResults.style.display = 'none';
  libConfirm.disabled     = true;

  // show modal
  if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  } else {
    modalEl.style.display = 'block';
    modalEl.classList.add('show');
    document.body.classList.add('modal-open');
  }

  try{
    // Fetch all quizzes and build a unique image library from quiz_img
    const url = API_BASE; // e.g. /api/quizz → should return list with quiz_img
    const res = await fetch(url, {
      headers: { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const json = await res.json().catch(()=>null);
    if (!res.ok || !json) {
      throw new Error(json?.message || ('HTTP '+res.status));
    }

    const rows = Array.isArray(json.data) ? json.data
               : (Array.isArray(json.items) ? json.items
               : (Array.isArray(json) ? json : []));
  
const docMap = new Map();
(rows || []).forEach(q => {
  // Prefer the public URL if backend sends it
  const rawImg = q.quiz_img_url || q.quiz_img || '';
if (!rawImg) return;

// Normalize to a real, fetchable URL
const urlCandidate = resolveImgUrl(String(rawImg));
const ext = extOf(urlCandidate.split('?')[0]);
if (!isImageExt(ext)) return;

// Use the normalized URL as dedupe key (strip query)
const key = urlCandidate.split('?')[0];

  const quizName = q.quiz_name || 'Untitled Quiz';
  const fileName = (urlCandidate.split('/').pop() || quizName || 'image');

  if (!docMap.has(key)) {
    docMap.set(key, {
      url: urlCandidate,          
      name: fileName,
      refs: [quizName],
      searchText: (quizName || '') + ' ' + fileName
    });
  } else {
    const entry = docMap.get(key);
    if (!entry.refs.includes(quizName)) {
      entry.refs.push(quizName);
      entry.searchText += ' ' + quizName;
    }
  }
});
    libLoader.style.display = 'none';
    const items = Array.from(docMap.values());
    if (!items.length) {
      libEmpty.style.display = '';
      libEmpty.textContent = 'No quiz images found yet.';
      return;
    }

    // Show search container since we have items
    searchContainer.style.display = '';

    // Function to highlight search terms in text
    function highlightText(text, searchTerm) {
      if (!searchTerm || !text) return escapeHtml(text);
      
      const escapedText = escapeHtml(text);
      const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
      return escapedText.replace(regex, '<span class="highlight">$1</span>');
    }
    function resolveImgUrl(raw) {
  const u = (raw || '').trim();
  if (!u) return '';

  // Already absolute http(s) → use as-is
  if (/^https?:\/\//i.test(u)) return u;

  try {
    // If it starts with '/', treat it as root-relative
    if (u.startsWith('/')) {
      return new URL(u, window.location.origin).href;
    }

    // Otherwise, treat as relative to app root (NOT /admin/...)
    return new URL(u.replace(/^\/+/, ''), window.location.origin + '/').href;
  } catch (e) {
    console.error('resolveImgUrl failed for', raw, e);
    return u;
  }
}

    // Function to render library items with optional filtering
    function renderLibraryItems(filteredItems = items, searchTerm = '') {
      const cardsHtml = filteredItems.map((it, idx) => {
        const url = it.url || '';
        const name = it.name || (url||'').split('/').pop() || `image-${idx+1}`;
        const refs = it.refs || [];
        const short = refs.slice(0,3).join(', ');
        const more  = Math.max(0, refs.length - 3);
        const refsDisplay = short + (more ? `, +${more} more` : '');

        // Highlight name and refs if searching
        const highlightedName = searchTerm ? highlightText(name, searchTerm) : escapeHtml(name);
        const highlightedRefs = searchTerm ? highlightText(refsDisplay, searchTerm) : escapeHtml(refsDisplay);

        return `
          <div class="sm-lib-card" data-url="${escapeHtml(url)}">
            <div class="overlay-checkbox">
              <input class="sm-lib-checkbox" type="checkbox" data-url="${escapeHtml(url)}" />
            </div>
            <div class="thumb-wrap">
              <img loading="lazy" class="sm-lib-thumb" src="${escapeHtml(url)}" alt="${escapeHtml(name)}">
            </div>
            <div class="card-name" title="${escapeHtml(name)}">${highlightedName}</div>
            <div class="card-refs" title="${escapeHtml(refs.join(' • '))}">
              ${highlightedRefs || 'Used in quiz'}
            </div>
            <div class="card-actions">
              <button type="button" class="sm-lib-preview-row btn btn-sm btn-outline-primary" data-url="${escapeHtml(url)}">
                <i class="fa fa-arrow-up-right-from-square me-1"></i>Preview
              </button>
              <div style="font-size:12px; color:var(--muted-color);">${escapeHtml(String(refs.length))} quiz(es)</div>
            </div>
          </div>
        `;
      }).join('');

      libList.innerHTML = `<div class="sm-lib-grid">${cardsHtml}</div>`;
      libList.style.display = '';
      libEmpty.style.display = 'none';

      // Update search results counter
      if (searchTerm) {
        searchResults.style.display = '';
        searchResults.textContent = `Found ${filteredItems.length} of ${items.length} images`;
      } else {
        searchResults.style.display = 'none';
      }

      // Wire up card interactions
      wireCardInteractions();
    }

    // Function to wire up card interactions
    function wireCardInteractions() {
      const grid = libList.querySelector('.sm-lib-grid') || libList;

      // Single-select: when one checkbox checked, uncheck others
      grid.querySelectorAll('.sm-lib-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
          if (cb.checked) {
            grid.querySelectorAll('.sm-lib-checkbox').forEach(other => {
              if (other !== cb) other.checked = false;
            });
          }
          const any = Array.from(grid.querySelectorAll('.sm-lib-checkbox')).some(n => n.checked);
          libConfirm.disabled = !any;
        });
      });

      // Clicking card toggles its checkbox
      grid.querySelectorAll('.sm-lib-card').forEach(card => {
        const cb = card.querySelector('.sm-lib-checkbox');
        card.addEventListener('click', (ev) => {
          if (ev.target.closest('.sm-lib-preview-row') || ev.target.tagName === 'INPUT') return;
          cb.checked = !cb.checked;
          if (cb.checked) {
            grid.querySelectorAll('.sm-lib-checkbox').forEach(other => {
              if (other !== cb) other.checked = false;
            });
          }
          const any = Array.from(grid.querySelectorAll('.sm-lib-checkbox')).some(n => n.checked);
          libConfirm.disabled = !any;
        });
      });

      // Preview button – SweetAlert2 image preview
      // Preview button – open in new browser tab
grid.querySelectorAll('.sm-lib-preview-row').forEach(btn => {
  btn.addEventListener('click', (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    const u = btn.dataset.url;
    if (!u) return;
    window.open(u, '_blank', 'noopener');
  });
});

    }

    // Function to update confirm button state
    function updateConfirmButtonState() {
      const grid = libList.querySelector('.sm-lib-grid');
      if (!grid) {
        libConfirm.disabled = true;
        return;
      }
      const any = Array.from(grid.querySelectorAll('.sm-lib-checkbox')).some(n => n.checked);
      libConfirm.disabled = !any;
    }

    // Initial render
    renderLibraryItems(items);

    // Search functionality
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
      clearTimeout(searchTimeout);
      
      const searchTerm = e.target.value.trim().toLowerCase();
      
      // Show/hide clear button
      if (searchTerm) {
        clearSearchBtn.style.display = 'block';
      } else {
        clearSearchBtn.style.display = 'none';
        searchResults.style.display = 'none';
        renderLibraryItems(items, '');
        return;
      }

      // Debounce search
      searchTimeout = setTimeout(() => {
        const filtered = items.filter(item => {
          // Search in image name and quiz references
          return item.searchText.toLowerCase().includes(searchTerm);
        });

        renderLibraryItems(filtered, searchTerm);
      }, 300);
    });

    // Clear search button
    clearSearchBtn.addEventListener('click', function() {
      searchInput.value = '';
      clearSearchBtn.style.display = 'none';
      searchResults.style.display = 'none';
      renderLibraryItems(items, '');
      searchInput.focus();
    });

    // Keyboard shortcuts for search
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        if (this.value) {
          this.value = '';
          clearSearchBtn.style.display = 'none';
          searchResults.style.display = 'none';
          renderLibraryItems(items, '');
        } else {
          // Close modal on second escape if search is empty
          try {
            if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
              bootstrap.Modal.getInstance(modalEl)?.hide();
            }
          } catch(e) {}
        }
      }
    });

    // Confirm → pick selected image & apply as quiz_img_url
    libConfirm.onclick = () => {
      const checked = Array.from(libList.querySelectorAll('.sm-lib-checkbox')).find(n => n.checked);
      if (!checked) return;
      const imgUrl = checked.dataset.url;
      if (!imgUrl) return;

      // Set URL tab + value + preview
      urlInput.value = imgUrl;
      urlPrev.style.backgroundImage = `url('${imgUrl}')`;
      clearFile(); // clear file input so API uses URL

      try{
        const urlTab = document.getElementById('url-tab');
        if (urlTab && window.bootstrap && bootstrap.Tab) {
          bootstrap.Tab.getOrCreateInstance(urlTab).show();
        }
      }catch(e){}

      // close modal
      try {
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
          bootstrap.Modal.getInstance(modalEl)?.hide();
        } else {
          modalEl.classList.remove('show'); modalEl.style.display = 'none'; document.body.classList.remove('modal-open');
        }
      } catch(e){}
    };

  }catch(e){
    console.error('Quiz image library error', e);
    libLoader.style.display = 'none';
    libList.style.display = 'none';
    libEmpty.textContent = 'Unable to load quiz image library.';
    libEmpty.style.display = '';
    searchContainer.style.display = 'none';
  }
}

if (btnChooseLib) {
  btnChooseLib.addEventListener('click', openImageLibraryPicker);
}
  /* ===== submit ===== */
  $('btnSave').addEventListener('click', async ()=>{
    clrErr();
    const name = ($('quiz_name').value||'').trim();
    if(!name){ fErr('quiz_name','Quiz title is required.'); $('quiz_name').focus(); return; }

    const tab = activeImgTab();
    const hasFile = fileInput.files && fileInput.files[0];
    const urlVal  = (urlInput.value||'').trim();

    setSaving(true);
    try{
      let res, json;

      const url  = isEdit ? `${API_BASE}/${encodeURIComponent(currentUUID)}` : API_BASE;
      const method = isEdit ? 'PUT' : 'POST';

      if(tab==='file' && hasFile){
        // multipart for file upload
        const fd = new FormData();
        const common = buildCommonPayload();
        Object.entries(common).forEach(([k,v])=> { if(v!==undefined && v!==null) fd.append(k, v); });
        fd.append('quiz_img', hasFile); // controller handles file at 'quiz_img'

        res = await fetch(url, {
          method: method,
          headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' },
          body: fd
        });
      } else if (tab === 'url' && urlVal) {
    // can be full URL or relative path
    const payload = { ...buildCommonPayload(), quiz_img_url: urlVal };
    res = await fetch(url, {
      method: method,
      headers:{
        'Authorization':'Bearer '+TOKEN,
        'Accept':'application/json',
        'Content-Type':'application/json'
      },
      body: JSON.stringify(payload)
    });
}else{
        // No image provided → JSON; backend may set default/leave null
        const payload = buildCommonPayload();
        res = await fetch(url, {
          method: method,
          headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json', 'Content-Type':'application/json' },
          body: JSON.stringify(payload)
        });
      }

      json = await res.json().catch(()=> ({}));

      if(res.ok){
        ok(isEdit ? 'Quiz updated successfully' : 'Quiz created successfully');
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
