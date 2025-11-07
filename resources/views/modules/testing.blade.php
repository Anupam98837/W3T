{{-- resources/views/modules/assignment/createAssignment.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title','Create Assignment')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  /* ===== Shell ===== */
  .asg-wrap{max-width:1100px;margin:14px auto 40px}
  .asg.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:hidden}
  .asg .card-header{background:var(--surface);border-bottom:1px solid var(--line-strong);padding:16px 18px}
  .asg-head{display:flex;align-items:center;gap:10px}
  .asg-head i{color:var(--accent-color)}
  .asg-head strong{color:var(--ink);font-family:var(--font-head);font-weight:700}
  .asg-head .hint{color:var(--muted-color);font-size:var(--fs-13)}

  .section-title{font-weight:600;color:var(--ink);font-family:var(--font-head);margin:12px 2px 14px}
  .divider-soft{height:1px;background:var(--line-soft);margin:10px 0 16px}

  /* RTE */
  .toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
  .tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
  .tool:hover{background:var(--page-hover)}
  .rte-wrap{position:relative}
  .rte{
    min-height:140px;max-height:460px;overflow:auto;
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

  /* Dropzone */
  .dropzone{
    display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;
    border:2px dashed var(--line-strong);border-radius:14px;background:var(--surface-2, #fff);
    padding:18px; transition:border-color .18s ease, background .18s ease;
  }
  .dropzone:hover{border-color:var(--primary-color);background:color-mix(in oklab, var(--primary-color) 7%, transparent)}
  .dropzone.dragover{border-color:var(--primary-color);box-shadow:0 0 0 3px color-mix(in oklab, var(--primary-color) 15%, transparent)}
  .drop-icon{width:56px;height:56px;border-radius:999px;border:1px dashed var(--line-strong);display:flex;align-items:center;justify-content:center;margin-bottom:10px;opacity:.9}
  .drop-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .file-list{margin-top:12px}
  .file-item{display:flex;justify-content:space-between;align-items:center;border:1px solid var(--line-strong);border-radius:8px;padding:8px 12px;margin-top:8px;background:var(--surface)}
  .file-item .meta{display:flex;gap:12px;align-items:center}
  .file-item .meta i{color:var(--muted-color)}
  .file-warn{color:var(--accent-color);font-size:12px;margin-left:8px}

  .selected-types{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap}
  .type-chip{background:var(--surface);border:1px solid var(--line-strong);padding:6px 8px;border-radius:999px;font-size:13px;display:inline-flex;align-items:center;gap:8px}
  .type-chip .remove-type{cursor:pointer;color:var(--danger-color);font-size:12px;padding-left:6px}

  /* Button loading state */
  .btn-loading{pointer-events:none;opacity:.85}
  .btn-loading .btn-label{visibility:hidden}
  .btn-loading .btn-spinner{display:inline-block !important}

  .btn-spinner{display:none; width:1rem;height:1rem;border:.2rem solid #0001;border-top-color:#fff;border-radius:50%;vertical-align:-.125em;animation:rot 1s linear infinite}
  .btn-light .btn-spinner{border-top-color:#0009}

  /* Modal: small tweaks */
  .modal .type-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;max-height:300px;overflow-y:auto;padding:5px}
  .modal .type-item{display:flex;align-items:center;gap:8px;padding:8px;border:1px solid var(--line-strong);border-radius:8px;background:var(--surface);cursor:pointer;transition:background-color 0.2s}
  .modal .type-item:hover{background:var(--page-hover)}
  .modal .type-item input{transform:scale(1.15);margin:0}
</style>

@section('content')
<div class="asg-wrap">
  <div class="card asg">
    <div class="card-header">
      <div class="asg-head">
        <i class="fa-solid fa-file-lines"></i>
        <strong>Create New Assignment</strong>
        <span class="hint">— Choose Course → Module → Batch, add details & attachments.</span>
      </div>
    </div>

    <div class="card-body position-relative">
      <div class="dim" id="busy"><div class="spin" aria-label="Saving…"></div></div>

      {{-- Top selects --}}
      <div class="row g-3 mb-3">
        <div class="col-md-5">
          <label class="form-label">Course <span class="text-danger">*</span></label>
          <select id="course_select" class="form-select" aria-label="Select course">
            <option value="">— Select course —</option>
          </select>
          <div class="err" data-for="course_id"></div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Module <span class="text-danger">*</span></label>
          <select id="module_select" class="form-select" aria-label="Select module" disabled>
            <option value="">— Select module —</option>
          </select>
          <div class="err" data-for="course_module_id"></div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Batch <span class="text-danger">*</span></label>
          <select id="batch_select" class="form-select" aria-label="Select batch" disabled>
            <option value="">— Select batch —</option>
          </select>
          <div class="err" data-for="batch_id"></div>
        </div>
      </div>

      {{-- Title / Slug --}}
      <div class="mb-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input id="title" class="form-control" type="text" maxlength="255" placeholder="e.g., React Component Design Principles" autocomplete="off">
        <div class="err" data-for="title"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">Slug</label>
        <input id="slug" class="form-control" type="text" maxlength="140" placeholder="auto-generated from title (editable)">
        <div class="tiny mt-1">Unique human-readable slug (auto-generated; you may edit).</div>
        <div class="err" data-for="slug"></div>
      </div>

      {{-- Instructions (RTE) --}}
      <div class="mb-3">
        <label class="form-label d-block">Instructions</label>
        <div class="toolbar" aria-label="Instructions toolbar">
          <button class="tool" type="button" data-cmd="bold" aria-label="Bold"><i class="fa-solid fa-bold"></i></button>
          <button class="tool" type="button" data-cmd="italic" aria-label="Italic"><i class="fa-solid fa-italic"></i></button>
          <button class="tool" type="button" data-cmd="underline" aria-label="Underline"><i class="fa-solid fa-underline"></i></button>
          <button class="tool" type="button" data-format="H2">H2</button>
          <button class="tool" type="button" data-format="H3">H3</button>
          <button class="tool" type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
          <button class="tool" type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
          <button class="tool" type="button" id="btnLinkInst"><i class="fa-solid fa-link"></i></button>
          <span class="tiny">Tip: include rubric, requirements, constraints.</span>
        </div>
        <div class="rte-wrap">
          <div id="instructions" class="rte" contenteditable="true" spellcheck="true"></div>
          <div class="rte-ph">Write assignment instructions (HTML allowed)…</div>
        </div>
        <div class="err" data-for="instruction"></div>
      </div>

      {{-- Status / submission controls --}}
      <div class="row g-3 mt-3">
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select id="status" class="form-select">
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Submission Type</label>
          <select id="submission_type" class="form-select">
            <option value="file">File</option>
            <option value="link">Link</option>
            <option value="text">Text</option>
            <option value="code">Code</option>
            <option value="mixed">Mixed</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Attempts Allowed</label>
          <input id="attempts_allowed" class="form-control" type="number" min="0" value="1">
        </div>
        <div class="col-md-2">
          <label class="form-label">Total Marks</label>
          <input id="total_marks" class="form-control" type="number" min="0" value="100">
        </div>
      </div>

      <div class="row g-3 mt-3">
        <div class="col-md-3">
          <label class="form-label">Pass Marks</label>
          <input id="pass_marks" class="form-control" type="number" min="0" placeholder="e.g., 70">
        </div>
        <div class="col-md-3">
          <label class="form-label">Due At</label>
          <input id="due_at" class="form-control" type="datetime-local">
        </div>
        <div class="col-md-3">
          <label class="form-label">End At</label>
          <input id="end_at" class="form-control" type="datetime-local">
        </div>
        <div class="col-md-3 d-flex align-items-center">
          <div class="form-check form-switch ms-2 mt-2">
            <input class="form-check-input" type="checkbox" id="allow_late">
            <label class="form-check-label tiny" for="allow_late">Allow Late Submissions</label>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label">Late Penalty (%)</label>
        <input id="late_penalty_percent" class="form-control" type="number" min="0" max="100" placeholder="e.g., 10">
      </div>

      {{-- Allowed submission types button --}}
      <div class="mt-3 mb-2 d-flex align-items-center" style="gap:10px">
        <button type="button" id="btnAllowedTypes" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#allowedTypesModal">
          <i class="fa fa-list-check me-1"></i> Allowed submission types
        </button>
        <div id="selectedTypeWrap" class="selected-types" aria-hidden="false"></div>
        <div id="selectedTypeHint" class="tiny mt-1">Select specific submission methods. If empty, all types are allowed.</div>
      </div>

      {{-- Attachments dropzone --}}
      <h3 class="section-title mt-3">Attachments</h3>
      <div class="divider-soft"></div>
      <div id="attachments_drop" class="dropzone" aria-label="Attachments dropzone">
        <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <div class="lead fw-semibold">Drag and drop files here or click to upload</div>
        <div class="tiny mt-1">PDFs, docs, zips, images — multiple files allowed. Max 20 MB per file.</div>
        <div class="drop-actions">
          <label class="btn btn-outline-primary mb-0" for="attachments_file">
            <i class="fa fa-file-arrow-up me-1"></i>Choose Files
          </label>
          <input id="attachments_file" type="file" multiple hidden>
          <button type="button" id="btnClearAll" class="btn btn-light">Clear</button>
        </div>
      </div>

      <div id="fileList" class="file-list"></div>
      <div class="err" data-for="attachments"></div>

      {{-- Actions --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <a id="cancel" class="btn btn-light" href="#">Cancel</a>
        <button id="btnSave" class="btn btn-primary" type="button">
          <span class="btn-spinner" aria-hidden="true"></span>
          <span class="btn-label"><i class="fa fa-floppy-disk me-1"></i> Create Assignment</span>
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

<!-- Allowed submission types modal -->
<div class="modal fade" id="allowedTypesModal" tabindex="-1" aria-labelledby="allowedTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-list-check me-2"></i> Allowed file types</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="tiny">Select the file types you want to allow for submissions. These will be sent as allowed submission types.</p>
  
          <div class="type-grid mb-3">
            <!-- Documents -->
            <label class="type-item">
              <input type="checkbox" data-type="pdf"> 
              <i class="fa fa-file-pdf text-danger"></i> 
              <span>PDF (.pdf)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="doc"> 
              <i class="fa fa-file-word text-primary"></i> 
              <span>Word Document (.doc)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="docx"> 
              <i class="fa fa-file-word text-primary"></i> 
              <span>Word Document (.docx)</span>
            </label>
            
            <!-- Archives -->
            <label class="type-item">
              <input type="checkbox" data-type="zip"> 
              <i class="fa fa-file-zipper text-warning"></i> 
              <span>ZIP Archive (.zip)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="rar"> 
              <i class="fa fa-file-zipper text-warning"></i> 
              <span>RAR Archive (.rar)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="7z"> 
              <i class="fa fa-file-zipper text-warning"></i> 
              <span>7-Zip Archive (.7z)</span>
            </label>
            
            <!-- Images -->
            <label class="type-item">
              <input type="checkbox" data-type="jpg"> 
              <i class="fa fa-image text-success"></i> 
              <span>JPEG Image (.jpg)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="jpeg"> 
              <i class="fa fa-image text-success"></i> 
              <span>JPEG Image (.jpeg)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="png"> 
              <i class="fa fa-image text-success"></i> 
              <span>PNG Image (.png)</span>
            </label>
            
            <!-- Other files -->
            <label class="type-item">
              <input type="checkbox" data-type="txt"> 
              <i class="fa fa-file-lines text-secondary"></i> 
              <span>Text File (.txt)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="pptx"> 
              <i class="fa fa-file-powerpoint text-danger"></i> 
              <span>PowerPoint (.pptx)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="xlsx"> 
              <i class="fa fa-file-excel text-success"></i> 
              <span>Excel (.xlsx)</span>
            </label>
            
            <!-- Code files -->
            <label class="type-item">
              <input type="checkbox" data-type="js"> 
              <i class="fa fa-code text-info"></i> 
              <span>JavaScript (.js)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="py"> 
              <i class="fa fa-code text-info"></i> 
              <span>Python (.py)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="java"> 
              <i class="fa fa-code text-info"></i> 
              <span>Java (.java)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="html"> 
              <i class="fa fa-code text-info"></i> 
              <span>HTML (.html)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="css"> 
              <i class="fa fa-code text-info"></i> 
              <span>CSS (.css)</span>
            </label>
            <label class="type-item">
              <input type="checkbox" data-type="cpp"> 
              <i class="fa fa-code text-info"></i> 
              <span>C++ (.cpp)</span>
            </label>
          </div>
  
          <div class="mb-2">
            <label class="form-label tiny mb-1">Add custom file type</label>
            <div style="display:flex;gap:8px">
              <input id="custom_type" class="form-control form-control-sm" placeholder="e.g., mp4, svg, php" maxlength="10" />
              <button id="btnAddType" class="btn btn-sm btn-outline-primary" type="button">Add</button>
            </div>
            <div class="tiny mt-1">Enter file extension without dot. Will be added to allowed submission types.</div>
          </div>
  
          <div class="mt-3">
            <label class="form-label tiny mb-1">Selected file types</label>
            <div id="modalSelectedType" style="display:flex;gap:8px;flex-wrap:wrap"></div>
          </div>
  
        </div>
        <div class="modal-footer">
          <button type="button" id="btnClearAllTypes" class="btn btn-light">Clear All</button>
          <button type="button" id="btnSaveTypes" class="btn btn-primary" data-bs-dismiss="modal">Save</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const $ = id => document.getElementById(id);
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const okToast  = new bootstrap.Toast($('okToast'));
  const errToast = new bootstrap.Toast($('errToast'));
  const ok  = (m)=>{ $('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ $('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  const API_BASE = '/api/assignments';
  const COURSES_API = '/api/courses';
  const MODULES_API = '/api/course-modules';
  const BATCHES_API  = '/api/batches';

  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  function setFormDisabled(disabled){
    document.querySelectorAll('.card-body input, .card-body select, .card-body button, .card-body textarea, .card-body .tool, .card-body .nav-link')
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
    const busy = $('busy');
    if(busy) busy.classList.toggle('show', !!on);
    setFormDisabled(!!on);
  }

  /* RTE wiring (instructions) */
  function wireRTE(rootId, linkBtnId){
    const el = $(rootId), ph = el ? el.nextElementSibling : null;
    if(!el) return;
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

  /* ===== Courses -> Modules -> Batches cascade ===== */
  const courseSel = $('course_select');
  const moduleSel = $('module_select');
  const batchSel  = $('batch_select');

  async function fetchCourses(){
    courseSel.innerHTML = '<option value="">Loading...</option>';
    try{
      const res = await fetch(COURSES_API + '?per_page=200', { headers:{ 'Authorization':'Bearer '+TOKEN }});
      const json = await res.json().catch(()=> ({}));
      const rows = (json && json.data) ? json.data : (Array.isArray(json) ? json : []);
      courseSel.innerHTML = '<option value="">— Select course —</option>';
      rows.forEach(function(c){
        const opt = document.createElement('option');
        opt.value = (c && c.id) != null ? c.id : '';
        opt.textContent = (c && c.title) != null ? c.title : '';
        courseSel.appendChild(opt);
      });
    }catch(e){
      courseSel.innerHTML = '<option value="">— Failed to load —</option>';
      console.error(e);
    }
  }

  async function fetchModulesFor(courseId){
    moduleSel.innerHTML = '<option value="">Loading modules...</option>'; moduleSel.disabled = true;
    try{
      const res = await fetch(MODULES_API + '?course_id=' + encodeURIComponent(courseId) + '&per_page=200', { headers:{ 'Authorization':'Bearer '+TOKEN }});
      const json = await res.json().catch(()=> ({}));
      const rows = (json && json.data) ? json.data : (Array.isArray(json) ? json : []);
      moduleSel.innerHTML = '<option value="">— Select module —</option>';
      rows.forEach(function(m){
        const opt = document.createElement('option');
        opt.value = (m && m.id) != null ? m.id : '';
        opt.textContent = (m && m.title) != null ? m.title : '';
        moduleSel.appendChild(opt);
      });
      moduleSel.disabled = false;
    }catch(e){
      moduleSel.innerHTML = '<option value="">— Failed to load —</option>';
      moduleSel.disabled = true;
      console.error(e);
    }
  }

  async function fetchBatchesFor(courseId){
    batchSel.innerHTML = '<option value="">Loading batches...</option>'; batchSel.disabled = true;
    try{
      const res = await fetch(BATCHES_API + '?course_id=' + encodeURIComponent(courseId) + '&per_page=200', { headers:{ 'Authorization':'Bearer '+TOKEN }});
      const json = await res.json().catch(()=> ({}));
      const rows = (json && json.data) ? json.data : (Array.isArray(json) ? json : []);
      batchSel.innerHTML = '<option value="">— Select batch —</option>';
      rows.forEach(function(b){
        const opt = document.createElement('option');
        opt.value = (b && b.id) != null ? b.id : '';
        opt.textContent = (b && (b.badge_title || b.tagline)) ? (b.badge_title || b.tagline) : ('Batch ' + ((b && b.id) ? b.id : ''));
        batchSel.appendChild(opt);
      });
      batchSel.disabled = false;
    }catch(e){
      batchSel.innerHTML = '<option value="">— Failed to load —</option>';
      batchSel.disabled = true;
      console.error(e);
    }
  }

  courseSel.addEventListener('change', async (ev)=>{
    const v = ev.target.value;
    moduleSel.innerHTML = '<option value="">— Select module —</option>'; moduleSel.disabled = true;
    batchSel.innerHTML  = '<option value="">— Select batch —</option>';  batchSel.disabled = true;

    if (!v) return;
    await fetchModulesFor(v);
    await fetchBatchesFor(v);
  });

  // initial load
  fetchCourses();

  /* ===== slug auto-generation ===== */
  const titleEl = $('title'), slugEl = $('slug');
  function slugify(s){
    return String(s || '').toLowerCase()
      .replace(/\s+/g,'-')
      .replace(/[^\w\-]+/g,'')
      .replace(/\-\-+/g,'-')
      .replace(/^-+|-+$/g,'')
      .slice(0,140);
  }
  titleEl.addEventListener('blur', ()=> {
    if (!slugEl.value.trim()) slugEl.value = slugify(titleEl.value || '');
  });

  /* ===== attachments handling (multiple) ===== */
  const drop = $('attachments_drop');
  const fileInput = $('attachments_file');
  const fileList = $('fileList');
  const btnClearAll = $('btnClearAll');
  let attachments = []; // {file:File, id:tmpId}

  function extOfName(name){
    return (name && String(name).split('.').pop() || '').toLowerCase();
  }

  function renderFileList(){
    fileList.innerHTML = '';
    attachments.forEach(function(fObj, idx){
      const f = fObj.file;
      const div = document.createElement('div'); div.className = 'file-item';
      const meta = document.createElement('div'); meta.className = 'meta';
      const icon = document.createElement('i'); icon.className = extIconFor(f.name) + ' fa-fw';
      const name = document.createElement('div'); name.textContent = f.name;
      const size = document.createElement('div'); size.className='tiny'; size.textContent = (f.size/1024).toFixed(1) + ' KB';
      meta.appendChild(icon); meta.appendChild(name);
      div.appendChild(meta);
      const right = document.createElement('div'); right.style.display='flex'; right.style.alignItems='center'; right.style.gap='10px';
      right.appendChild(size);
      const rem = document.createElement('button'); rem.className='btn btn-sm btn-link text-danger'; rem.type='button'; rem.textContent='Remove';
      rem.addEventListener('click', function(){ attachments.splice(idx,1); renderFileList(); });
      right.appendChild(rem);
      div.appendChild(right);
      fileList.appendChild(div);
    });
  }

  // avoid double-open: programmatic click only from label triggers single native dialog
  ['dragenter','dragover'].forEach(function(ev){
    drop.addEventListener(ev, function(e){ e.preventDefault(); e.stopPropagation(); drop.classList.add('dragover'); });
  });
  ['dragleave','drop','dragend'].forEach(function(ev){
    drop.addEventListener(ev, function(e){ e.preventDefault(); e.stopPropagation(); drop.classList.remove('dragover'); });
  });
  drop.addEventListener('drop', function(e){
    var filesArr = [];
    try{ filesArr = (e && e.dataTransfer && e.dataTransfer.files) ? Array.from(e.dataTransfer.files) : []; }catch(x){ filesArr = []; }
    filesArr.forEach(function(f){ addAttachmentFile(f); });
  });

  // click on the dropzone should open the file picker once
  drop.addEventListener('click', function(e){
    // ensure the input exists then open
    if(fileInput){ fileInput.click(); }
  });

  fileInput.addEventListener('change', function(){
    var files = (fileInput && fileInput.files) ? Array.from(fileInput.files) : [];
    files.forEach(function(f){ addAttachmentFile(f); });
    fileInput.value = '';
  });

  function addAttachmentFile(f){
    if(!f) return;
    if(f.size > 20*1024*1024){ err('File too large (max 20 MB): '+f.name); return; }
    attachments.push({ file: f, id: Date.now() + Math.random() });
    renderFileList();
  }

  btnClearAll.addEventListener('click', function(){ attachments = []; renderFileList(); });

  /* ===== errors helper ===== */
  function fErr(field,msg){ var el=document.querySelector('.err[data-for="'+field+'"]'); if(el){ el.textContent=msg||''; el.style.display=msg?'block':'none'; } }
  function clrErr(){ document.querySelectorAll('.err').forEach(function(e){ e.textContent=''; e.style.display='none'; }); }

  /* ===== Allowed submission types logic ===== */
  let allowedSubmissionTypes = []; // e.g. ['pdf', 'doc', 'jpg']

  const modalEl = document.getElementById('allowedTypesModal');
  const modalSelectedEl = $('modalSelectedType');
  const selectedWrap = $('selectedTypeWrap');
  const selectedHint = $('selectedTypeHint');

  // icon mapping for file types with colors
  const typeIconMap = {
    pdf: 'fa fa-file-pdf text-danger',
    doc: 'fa fa-file-word text-primary',
    docx: 'fa fa-file-word text-primary',
    zip: 'fa fa-file-zipper text-warning',
    rar: 'fa fa-file-zipper text-warning',
    '7z': 'fa fa-file-zipper text-warning',
    jpg: 'fa fa-image text-success',
    jpeg: 'fa fa-image text-success',
    png: 'fa fa-image text-success',
    txt: 'fa fa-file-lines text-secondary',
    pptx: 'fa fa-file-powerpoint text-danger',
    xlsx: 'fa fa-file-excel text-success',
    js: 'fa fa-code text-info',
    py: 'fa fa-code text-info',
    java: 'fa fa-code text-info',
    html: 'fa fa-code text-info',
    css: 'fa fa-code text-info',
    cpp: 'fa fa-code text-info'
  };

  // type display names
  const typeDisplayMap = {
    pdf: 'PDF',
    doc: 'Word (.doc)',
    docx: 'Word (.docx)',
    zip: 'ZIP',
    rar: 'RAR',
    '7z': '7-Zip',
    jpg: 'JPEG',
    jpeg: 'JPEG',
    png: 'PNG',
    txt: 'Text',
    pptx: 'PowerPoint',
    xlsx: 'Excel',
    js: 'JavaScript',
    py: 'Python',
    java: 'Java',
    html: 'HTML',
    css: 'CSS',
    cpp: 'C++'
  };

  function extIconFor(name){
    const e = extOfName(name);
    return typeIconMap[e] || 'fa fa-file text-secondary';
  }

  // fill modal selection from allowedSubmissionTypes on open
  modalEl && modalEl.addEventListener('show.bs.modal', function () {
    const checkboxes = modalEl.querySelectorAll('input[type="checkbox"][data-type]');
    checkboxes.forEach(cb => {
      const type = (cb.dataset.type || '').toLowerCase();
      cb.checked = allowedSubmissionTypes.includes(type);
    });
    renderModalSelected();
  });

  function renderModalSelected(){
    modalSelectedEl.innerHTML = '';
    allowedSubmissionTypes.forEach(function(type){
      const div = document.createElement('div'); 
      div.className='type-chip';
      
      const icon = document.createElement('i'); 
      icon.className = (typeIconMap[type] || 'fa fa-file text-secondary') + ' fa-fw';
      div.appendChild(icon);
      
      const txt = document.createElement('span'); 
      txt.textContent = typeDisplayMap[type] || `.${type}`;
      div.appendChild(txt);
      
      modalSelectedEl.appendChild(div);
    });
  }

  function renderSelectedChips(){
    selectedWrap.innerHTML = '';
    if(allowedSubmissionTypes.length === 0){
      selectedHint.style.display = 'block';
      return;
    }
    selectedHint.style.display = 'none';
    
    allowedSubmissionTypes.forEach(function(type, idx){
      const chip = document.createElement('div'); 
      chip.className='type-chip';
      
      const icon = document.createElement('i'); 
      icon.className = (typeIconMap[type] || 'fa fa-file text-secondary') + ' fa-fw';
      chip.appendChild(icon);
      
      const strong = document.createElement('strong'); 
      strong.textContent = typeDisplayMap[type] || `.${type}`;
      chip.appendChild(strong);
      
      const rem = document.createElement('span'); 
      rem.className='remove-type'; 
      rem.dataset.idx = idx; 
      rem.innerHTML='&times;';
      rem.addEventListener('click', function(){
        allowedSubmissionTypes.splice(idx,1);
        
        // Also uncheck the corresponding checkbox in modal if it exists
        const existingCheckbox = modalEl.querySelector(`input[data-type="${type}"]`);
        if(existingCheckbox) {
          existingCheckbox.checked = false;
        }
        
        renderModalSelected();
        renderSelectedChips();
      });
      chip.appendChild(rem);
      selectedWrap.appendChild(chip);
    });
  }

  // modal checkboxes toggle
  modalEl && modalEl.addEventListener('click', function(ev){
    const target = ev.target;
    if(target && target.matches('input[type="checkbox"][data-type]')){
      const type = (target.dataset.type||'').toLowerCase();
      if(target.checked){
        if(!allowedSubmissionTypes.includes(type)) allowedSubmissionTypes.push(type);
      } else {
        allowedSubmissionTypes = allowedSubmissionTypes.filter(t=> t !== type);
      }
      renderModalSelected();
      renderSelectedChips();
    }
  });

  // add custom type
  $('btnAddType').addEventListener('click', function(){
    const val = ( $('custom_type').value || '' ).trim().toLowerCase();
    if(!val){ 
      err('Enter a file type'); 
      return; 
    }
    if(!/^[a-z0-9_]{1,10}$/.test(val)){ 
      err('Invalid file type (use letters, numbers or underscore, no dot)'); 
      return; 
    }
    
    // Check if already exists
    if(!allowedSubmissionTypes.includes(val)) {
      allowedSubmissionTypes.push(val);
      
      // Also check the corresponding checkbox in modal if it exists
      const existingCheckbox = modalEl.querySelector(`input[data-type="${val}"]`);
      if(existingCheckbox) {
        existingCheckbox.checked = true;
      }
      
      $('custom_type').value = '';
      renderModalSelected();
      renderSelectedChips();
    } else {
      err('This file type is already added');
    }
  });

  // Also allow pressing Enter in the custom type input
  $('custom_type').addEventListener('keypress', function(e){
    if(e.key === 'Enter') {
      e.preventDefault();
      $('btnAddType').click();
    }
  });

  // clear all in modal
  $('btnClearAllTypes').addEventListener('click', function(){
    allowedSubmissionTypes = [];
    // uncheck checkboxes
    modalEl.querySelectorAll('input[type="checkbox"][data-type]').forEach(cb => cb.checked = false);
    renderModalSelected();
    renderSelectedChips();
  });

  // save modal (close triggers with data-bs-dismiss already)
  $('btnSaveTypes').addEventListener('click', function(){
    // normalize unique
    allowedSubmissionTypes = Array.from(new Set(allowedSubmissionTypes.map(t=> t.toLowerCase())));
    renderSelectedChips();
  });

  // init UI
  renderSelectedChips();

  /* ===== build payload & submit ===== */
  function buildPayload(){
    return {
        course_id:         courseSel.value || null,
        course_module_id:  moduleSel.value || null,
        batch_id:          batchSel.value || null,
        title:             ((''+$('title').value)||'').trim() || null,
        slug:              ((''+$('slug').value)||'').trim() || null,
        instruction:       ($('instructions').innerHTML || '').trim() || null,
        status:            $('status').value || 'draft',
        submission_type:   $('submission_type').value || 'file',
        allowed_submission_types: allowedSubmissionTypes, // Always send as array, even if empty
        attempts_allowed:  Number($('attempts_allowed').value || 1),
        total_marks:       Number($('total_marks').value || 100),
        pass_marks:        $('pass_marks').value ? Number($('pass_marks').value) : null,
        release_at:        null,
        due_at:            $('due_at').value || null,
        end_at:            $('end_at').value || null,
        allow_late:        $('allow_late').checked ? 1 : 0,
        late_penalty_percent: $('late_penalty_percent').value ? Number($('late_penalty_percent').value) : null,
        attachments_json:  null,
        metadata:          null
    };
  }

  async function submitAssignment(){
    clrErr();
    const payload = buildPayload();
    // basic client-side validation
    if(!payload.course_id){ fErr('course_id','Please select a course'); return false; }
    if(!payload.course_module_id){ fErr('course_module_id','Please select a module'); return false; }
    if(!payload.batch_id){ fErr('batch_id','Please select a batch'); return false; }
    if(!payload.title){ fErr('title','Title is required'); return false; }

    setSaving(true);
    try{
      const courseParam = payload.course_id;
      const url = '/api/courses/' + encodeURIComponent(courseParam) + '/assignments';
      let res;
      if(attachments.length > 0){
        const fd = new FormData();
        Object.keys(payload).forEach(function(k){
          var v = payload[k];
          if(v === null || typeof v === 'undefined') return;
          
          // Handle arrays properly for FormData
          if(Array.isArray(v)) {
            // For arrays like allowed_submission_types, append each item
            v.forEach(item => {
              fd.append(k + '[]', item);
            });
          } else if(typeof v === 'object') {
            fd.append(k, JSON.stringify(v));
          } else {
            fd.append(k, v);
          }
        });
        // append files as attachments[]
        attachments.forEach(function(a){ fd.append('attachments[]', a.file); });
        res = await fetch(url, {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept':'application/json' },
          body: fd
        });
      } else {
        res = await fetch(url, {
          method: 'POST',
          headers: { 
            'Authorization': 'Bearer ' + TOKEN, 
            'Accept':'application/json', 
            'Content-Type':'application/json' 
          },
          body: JSON.stringify(payload)
        });
      }

      const json = await res.json().catch(()=> ({}));

      if(res.ok){
        ok('Assignment created successfully');
        setTimeout(()=> location.replace('#'), 700);
        return true;
      }

      if(res.status === 422){
        const e = json.errors || json.fields || {};
        Object.keys(e).forEach(function(k){
          var v = e[k];
          fErr(k, Array.isArray(v) ? v[0] : String(v));
        });
        err(json.message || 'Please fix the highlighted fields.');
        return false;
      }

      if(res.status === 403){
        Swal.fire({icon:'error',title:'Unauthorized',html:'Token/role lacks permission for this endpoint.'});
        return false;
      }

      Swal.fire('Save failed', json.message || ('HTTP '+res.status), 'error');
      return false;
    }catch(ex){
      console.error(ex);
      Swal.fire('Network error','Please check your connection and try again.','error');
      return false;
    }finally{
      setSaving(false);
    }
  }

  $('btnSave').addEventListener('click', async function(){ await submitAssignment(); });

})();
</script>
@endpush