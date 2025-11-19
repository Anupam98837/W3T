{{-- resources/views/modules/notices/manageNotices.blade.php --}}
@section('title','Manage Notices')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* (Kept the same visual rules as your study manager; only a few textual/icon tweaks) */
/* ===== Shell ===== */
.sm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

/* Toolbar */
.mfa-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .btn{height:40px;border-radius:12px}
.mfa-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
.mfa-toolbar .btn-primary{background:var(--primary-color);border:none}

/* Table Card */
.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible}
.table-wrap .card-body{overflow:visible}
.table-responsive{overflow:visible !important}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
.small{font-size:12.5px}

/* Sorting */
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Dropdowns in table */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:5000}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color)!important}

/* Empty & loader */
.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

/* Modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control,.form-select,textarea{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control,html.theme-dark .form-select,html.theme-dark textarea{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}

/* Viewer area */
.viewer-wrap{border:1px dashed var(--line-strong);border-radius:12px;min-height:360px;display:flex;align-items:center;justify-content:center;background:var(--surface)}
.viewer-wrap iframe{width:100%;height:70vh;border:none;border-radius:12px}
.viewer-wrap img{max-width:100%;max-height:70vh;border-radius:12px}
.viewer-tools{display:flex;align-items:center;gap:8px}
.attachment-list .att{display:flex;align-items:center;gap:10px;border:1px solid var(--line-strong);border-radius:10px;padding:8px 10px;margin-bottom:6px;background:var(--surface)}
.attachment-list .att .name{font-weight:600}
.attachment-list .att .meta{font-size:12px;color:var(--muted-color)}

/* Dark tweaks */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}
/* RTE used in create/edit (small toolbar) */
.toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
.tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
.tool:hover{background:var(--page-hover)}
.rte-wrap{position:relative}
.rte{
  min-height:160px;max-height:400px;overflow:auto;
  border:1px solid var(--line-strong);border-radius:8px;background:#fff;padding:10px;line-height:1.6;outline:none
}
.rte:focus{box-shadow:var(--ring);border-color:var(--accent-color)}
.rte-ph{position:absolute;top:10px;left:10px;color:#9aa3b2;pointer-events:none;font-size:14px}
.rte.has-content + .rte-ph{display:none}
</style>
@endpush

@section('content')
<div class="sm-wrap">
  {{-- ===== Toolbar ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Course</label>
        <select id="courseSel" class="form-select" style="min-width:240px;">
          <option value="">Select a course…</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Module</label>
        <select id="moduleSel" class="form-select" style="min-width:240px;" disabled>
          <option value="">Select a module…</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Batch</label>
        <select id="batchSel" class="form-select" style="min-width:220px;" disabled>
          <option value="">Select a batch…</option>
        </select>
      </div>

      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="form-check form-switch me-1">
          <input class="form-check-input" type="checkbox" id="binToggle" />
          <label class="form-check-label small" for="binToggle">Bin</label>
        </div>

        <div class="position-relative" style="min-width:240px;">
          <input id="q" type="text" class="form-control ps-5" placeholder="Search title/message…" disabled>
          <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
        </div>
        <button id="btnCreate" class="btn btn-primary" disabled>
          <i class="fa fa-plus"></i> New Notice
        </button>
      </div>
    </div>
  </div>

  {{-- ===== Table ===== --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
            <tr>
              <th class="sortable" data-col="title">TITLE <span class="caret"></span></th>
              <th style="width:20%;">MODULE</th>
              <th style="width:16%;">BATCH</th>
              <th class="text-center" style="width:120px;">FILES</th>
              <th class="sortable" data-col="created_at" style="width:160px;">CREATED <span class="caret"></span></th>
              <th class="text-end" style="width:112px;">ACTIONS</th>
            </tr>
          </thead>
          <tbody id="rows">
            <tr id="loaderRow" style="display:none;">
              <td colspan="6" class="p-0">
                <div class="p-4">
                  <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  </div>
                </div>
              </td>
            </tr>
            <tr id="ask">
              <td colspan="6" class="p-4 text-center text-muted">
                <i class="fa fa-bullhorn mb-2" style="font-size:28px;opacity:.6"></i>
                <div>Please select Course → Module → Batch to load notices.</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="empty" class="empty p-4 text-center" style="display:none;">
        <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
        <div>No notices found.</div>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="metaTxt">—</div>
        <nav style="position:relative; z-index:1;"><ul id="pager" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>
</div>

{{-- ================= View Notice (modal) ================= --}}
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xxl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-eye fa-fw me-2"></i>View Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-4">
            <div class="mb-3">
              <h6>Notice details</h6>
              <div class="small text-muted" id="vTitle">—</div>
              <div class="small text-muted mt-1">Priority: <span id="vPriority">—</span> • Status: <span id="vStatus">—</span></div>
              <hr/>
            </div>
            <div class="attachment-list" id="attList"></div>
          </div>
          <div class="col-lg-8">
            <div class="viewer-tools mb-2">
              <div class="badge badge-soft-info" id="vMime">—</div>
              <div class="badge badge-soft-primary" id="vSize">—</div>
            </div>
            <div class="viewer-wrap" id="viewer">
              <div class="text-muted small text-center">
                The notice message will be shown here; select an attachment to preview.
              </div>
            </div>
            <div class="small text-muted mt-2">
              Note: Message HTML is rendered as-is. Make sure stored HTML is sanitized server-side to avoid XSS.
            </div>
          </div>
        </div>
        <div class="mt-3">
          <h6>Message</h6>
          <div id="vMessage" class="p-3" style="border:1px solid var(--line-soft);border-radius:8px;background:var(--surface-2);"></div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

{{-- ================= Create / Edit Notice (modal) ================= --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="em_title" class="modal-title"><i class="fa fa-paper-plane me-2"></i>Create Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <input type="hidden" id="em_mode" value="create">
          <input type="hidden" id="em_id" value="">

          <div class="col-md-4">
            <label class="form-label">Course</label>
            <input id="em_course_label" class="form-control" readonly>
            <input id="em_course_id" type="hidden">
          </div>
          <div class="col-md-4">
            <label class="form-label">Module</label>
            <input id="em_module_label" class="form-control" readonly>
            <input id="em_module_id" type="hidden">
          </div>
          <div class="col-md-4">
            <label class="form-label">Batch</label>
            <input id="em_batch_label" class="form-control" readonly>
            <input id="em_batch_id" type="hidden">
          </div>

          <div class="col-12">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input id="em_title_input" class="form-control" maxlength="255" placeholder="e.g., Exam postponed">
          </div>

          <div class="col-12">
            <label class="form-label">Message</label>
            <div class="rte-wrap">
              <div id="rte_toolbar" class="toolbar" role="toolbar" aria-label="Formatting toolbar">
                <button type="button" class="tool" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                <button type="button" class="tool" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                <button type="button" class="tool" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
                <button type="button" class="tool" data-cmd="insertUnorderedList" title="Bulleted list"><i class="fa fa-list-ul"></i></button>
                <button type="button" class="tool" data-cmd="insertOrderedList" title="Numbered list"><i class="fa fa-list-ol"></i></button>
                <button type="button" class="tool" data-cmd="createLink" title="Insert link"><i class="fa fa-link"></i></button>
                <button type="button" class="tool" data-cmd="removeFormat" title="Remove formatting"><i class="fa fa-eraser"></i></button>
                <select id="insertHeading" class="tool" title="Insert heading" style="padding:6px 8px;border-radius:8px; display:none;">
                  <option value="">Insert…</option>
                  <option value="h2">Heading</option>
                  <option value="p">Paragraph</option>
                </select>
              </div>

              <div id="rte" class="rte" contenteditable="true" aria-label="Notice message editor" role="textbox" spellcheck="true"></div>
              <div class="rte-ph" style="display:none;">Write your notice here…</div>
            </div>
            <textarea id="message_html" name="message_html" hidden></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">Visibility</label>
            <select id="em_visibility" class="form-select">
              <option value="">Default (batch)</option>
              <option value="course">Course</option>
              <option value="module">Module</option>
              <option value="batch">Batch</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Priority</label>
            <select id="em_priority" class="form-select">
              <option value="normal">Normal</option>
              <option value="low">Low</option>
              <option value="high">High</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select id="em_status" class="form-select">
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Attachments</label>
            <input id="em_files" type="file" class="form-control" multiple>
            <div class="small text-muted mt-1">Max 50MB per file.</div>
          </div>

        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button id="em_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button></div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
  <div id="okToast" class="toast text-bg-success border-0"><div class="d-flex"><div id="okMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div></div>
  <div id="errToast" class="toast text-bg-danger border-0 mt-2"><div class="d-flex"><div id="errMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Viewers (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.1/dist/docx-preview.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<link  href="https://cdn.jsdelivr.net/npm/pptxjs@3.5.0/dist/pptxjs.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pptxjs@3.5.0/dist/pptxjs.min.js"></script>

<script>
/* =================== AUTH / GLOBALS =================== */
const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
if (!TOKEN){ Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/'); }

const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
const errToast = new bootstrap.Toast(document.getElementById('errToast'));
const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

/** API endpoints for Notices */
const API = {
  index: (qs)=> '/api/notices?' + qs.toString(),
  show: (uuid)=> `/api/notices/show/${encodeURIComponent(uuid)}`,
  store: '/api/notices',
  update: (id)=> `/api/notices/${encodeURIComponent(id)}`,
  destroy: (id)=> `/api/notices/${encodeURIComponent(id)}`,
  restore: (id)=> `/api/notices/${encodeURIComponent(id)}/restore`, // POST according to routes
  forceDelete: (id)=> `/api/notices/${encodeURIComponent(id)}/force`,
  deletedIndex: (qs)=> '/api/notices/deleted?' + qs.toString()
};

const H = {
  esc: (s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>m[ch]); },
  fmtDateTime(iso){ if(!iso) return '-'; const d=new Date(iso); return isNaN(d)?H.esc(iso):d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'}); },
  bytes(n){ n=Number(n||0); if(!n) return '0 B'; const u=['B','KB','MB','GB']; let i=0; while(n>=1024 && i<u.length-1){ n/=1024; i++; } return n.toFixed(n<10&&i?1:0)+' '+u[i]; },
  icon(ext){ ext=(ext||'').toLowerCase(); const map={pdf:'fa-file-pdf',doc:'fa-file-word',docx:'fa-file-word',ppt:'fa-file-powerpoint',pptx:'fa-file-powerpoint',xls:'fa-file-excel',xlsx:'fa-file-excel',txt:'fa-file-lines',png:'fa-file-image',jpg:'fa-file-image',jpeg:'fa-file-image',webp:'fa-file-image',svg:'fa-file-image'}; return map[ext]||'fa-file'; }
};

/* =================== ELEMENTS & STATE =================== */
const courseSel = document.getElementById('courseSel');
const moduleSel = document.getElementById('moduleSel');
const batchSel  = document.getElementById('batchSel');
const q         = document.getElementById('q');
const btnCreate = document.getElementById('btnCreate');
const rowsEl    = document.getElementById('rows');
const loaderRow = document.getElementById('loaderRow');
const emptyEl   = document.getElementById('empty');
const askEl     = document.getElementById('ask');
const pager     = document.getElementById('pager');
const metaTxt   = document.getElementById('metaTxt');
const binToggle = document.getElementById('binToggle');

let sort = '-created_at';
let page = 1;
let perPage = 20;
let binMode = false;

/* =================== INIT =================== */
loadCourses();
wire();

function enableFilters(on){
  [moduleSel, batchSel, q].forEach(el=> el.disabled = !on);
  btnCreate.disabled = !on || binMode;
}

function wire(){
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.dd-toggle');
    if(!btn) return;
    e.preventDefault(); e.stopPropagation();
    const dd = bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: 'outside',
      boundary: 'viewport',
      popperConfig: { strategy: 'fixed' }
    });
    dd.toggle();
  });

  // Sorting
  document.querySelectorAll('thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      sort = (sort===col) ? ('-'+col) : (sort==='-'+col ? col : (col==='created_at' ? '-created_at' : col));
      page = 1; loadList();
      document.querySelectorAll('thead th.sortable').forEach(t=> t.classList.remove('asc','desc'));
      if(sort===col) th.classList.add('asc'); if(sort==='-'+col) th.classList.add('desc');
    });
  });

  courseSel.addEventListener('change', async ()=>{
    moduleSel.innerHTML = '<option value="">Select a module…</option>';
    batchSel.innerHTML  = '<option value="">Select a batch…</option>';
    enableFilters(false);
    rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
    emptyEl.style.display='none'; askEl.style.display='';
    pager.innerHTML=''; metaTxt.textContent='—';
    if(!courseSel.value) return;
    await loadModules(courseSel.value);
    await loadBatches(courseSel.value);
    enableFilters(true);
  });

  moduleSel.addEventListener('change', async ()=>{
    await loadBatches(courseSel.value, moduleSel.value||'');
    askEl.style.display=''; // wait for batch selection
    rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
    emptyEl.style.display='none'; pager.innerHTML=''; metaTxt.textContent='—';
  });

  batchSel.addEventListener('change', ()=>{ if(batchSel.value){ page=1; loadList(); } });

  let t; q.addEventListener('input', ()=>{ clearTimeout(t); t=setTimeout(()=>{ page=1; if(batchSel.value) loadList(); }, 350); });

  binToggle.addEventListener('change', ()=>{ binMode = !!binToggle.checked; btnCreate.disabled = binMode || !batchSel.value; page = 1; if(batchSel.value) loadList(); });

  btnCreate.addEventListener('click', openCreateModal);

  // Row actions (delegated)
  document.addEventListener('click', (e)=>{
    const item=e.target.closest('.dropdown-item[data-act]'); if(!item) return;
    e.preventDefault();
    const act=item.dataset.act, id=item.dataset.id, uuid=item.dataset.uuid;
    if(act==='view') openView(uuid);
    if(act==='edit') openEdit(id);
    if(act==='delete') deleteItem(id);
    if(act==='purge') purgeItem(id);
    if(act==='restore') restoreItem(id);
    const toggle=item.closest('.dropdown')?.querySelector('.dd-toggle'); if(toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
  });

  // disable right click on viewer
  document.getElementById('viewer').addEventListener('contextmenu', (e)=> e.preventDefault());
}

/* =================== LOADERS =================== */
async function loadCourses(){
  try{
    const res=await fetch('/api/courses?status=published&per_page=1000',{headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Failed to load courses');
    const items=j?.data||[];
    courseSel.innerHTML = '<option value="">Select a course…</option>' + items.map(c=>`<option value="${c.id}" data-uuid="${H.esc(c.uuid||'')}">${H.esc(c.title||'(untitled)')}</option>`).join('');
  }catch(e){ err(e.message||'Courses error'); }
}

async function loadModules(courseId){
  try{
    const res=await fetch(`/api/course-modules?course_id=${encodeURIComponent(courseId)}&per_page=1000`,{headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Failed to load modules');
    const items=j?.data||[];
    moduleSel.innerHTML = '<option value="">Select a module…</option>' + items.map(m=>`<option value="${m.id}" data-uuid="${H.esc(m.uuid||'')}">${H.esc(m.title||'(untitled)')}</option>`).join('');
  }catch(e){ err(e.message||'Modules error'); }
}

async function loadBatches(courseId, moduleId){
  try{
    const qs = new URLSearchParams({course_id:courseId, per_page:'200'});
    if(moduleId) qs.set('course_module_id', moduleId);
    const res=await fetch('/api/batches?'+qs.toString(),{headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Failed to load batches');
    const items=j?.data||[];
    batchSel.innerHTML = '<option value="">Select a batch…</option>' + items.map(b=>`<option value="${b.id}" data-uuid="${H.esc(b.uuid||'')}">${H.esc(b.badge_title||'(untitled)')}</option>`).join('');
  }catch(e){ err(e.message||'Batches error'); }
}

function showAsk(v){ askEl.style.display = v ? '' : 'none'; }
function showLoader(v){ loaderRow.style.display = v ? '' : 'none'; }

function rowActions(r){
  if(!binMode){
    return `
    <div class="dropdown text-end" data-bs-display="static">
      <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
        <i class="fa fa-ellipsis-vertical"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button class="dropdown-item" data-act="view" data-uuid="${H.esc(r.uuid)}"><i class="fa fa-eye"></i> View</button></li>
        <li><button class="dropdown-item" data-act="edit" data-id="${r.id}"><i class="fa fa-pen-to-square"></i> Edit</button></li>
        <li><hr class="dropdown-divider"></li>
        <li><button class="dropdown-item text-danger" data-act="delete" data-id="${r.id}"><i class="fa fa-trash"></i> Delete</button></li>
      </ul>
    </div>`;
  }else{
    return `
    <div class="dropdown text-end" data-bs-display="static">
      <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
        <i class="fa fa-ellipsis-vertical"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button class="dropdown-item" data-act="view" data-uuid="${H.esc(r.uuid)}"><i class="fa fa-eye"></i> View</button></li>
        <li><button class="dropdown-item" data-act="restore" data-id="${r.id}"><i class="fa fa-rotate-left"></i> Restore</button></li>
        <li><hr class="dropdown-divider"></li>
        <li><button class="dropdown-item text-danger" data-act="purge" data-id="${r.id}"><i class="fa fa-trash-can"></i> Delete permanently</button></li>
      </ul>
    </div>`;
  }
}

function rowHTML(r){
  const tr=document.createElement('tr');
  tr.innerHTML = `
    <td><div class="fw-semibold">${H.esc(r.title||'(untitled)')}</div><div class="small text-muted text-truncate" style="max-width:520px">${H.esc(r.message_html ? (r.message_html.replace(/(<([^>]+)>)/ig, ' ').slice(0,200)) : '')}</div></td>
    <td>${H.esc(r.module_title || r.course_module_title || '-')}</td>
    <td>${H.esc(r.batch_title || r.batch_name || '-')}</td>
    <td class="text-center"><span class="badge badge-soft-primary"><i class="fa fa-paperclip"></i> ${Number((r.attachments && (Array.isArray(r.attachments)? r.attachments.length : r.attachment_count))||0)}</span></td>
    <td>${H.fmtDateTime(r.created_at)}</td>
    <td class="text-end">${rowActions(r)}</td>`;
  return tr;
}

async function loadList(){
  if(!batchSel.value){ showAsk(true); return; }
  showAsk(false); showLoader(true); emptyEl.style.display='none'; pager.innerHTML=''; metaTxt.textContent='—';
  rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
  try{
    const usp=new URLSearchParams({
      course_id: courseSel.value,
      course_module_id: moduleSel.value||'',
      batch_id: batchSel.value,
      per_page: perPage, page, sort
    });
    if(q.value.trim()) usp.set('search', q.value.trim());

    let endpoint = API.index(usp);
    if(binMode){
      // Use deleted index endpoint
      endpoint = API.deletedIndex(usp);
    }

    const res=await fetch(endpoint, {headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json','Cache-Control':'no-cache'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Load failed');

    const items=j?.data||[];
    if(items.length===0){ emptyEl.style.display=''; return; }

    const frag=document.createDocumentFragment();
    items.forEach(r=> frag.appendChild(rowHTML(r)));
    rowsEl.appendChild(frag);

    // Pagination
    const meta = j?.meta || j?.pagination || {page:1, per_page:perPage, total:items.length};
    const total=Number(meta.total||items.length), per=Number(meta.per_page||perPage), cur=Number(meta.page||meta.current_page||1);
    const pages=Math.max(1, Math.ceil(total/per));
    const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
    let html=''; html+=li(cur<=1,false,'Previous',cur-1);
    const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
    for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
    if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
    html+=li(cur>=pages,false,'Next',cur+1);
    pager.innerHTML=html;
    pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{ const t=Number(a.dataset.page); if(!t || t===page) return; page=Math.max(1,t); loadList(); window.scrollTo({top:0,behavior:'smooth'}); }));
    metaTxt.textContent = `Page ${cur} of ${pages} — ${total} item(s)`;

  }catch(e){ err(e.message||'Load error'); emptyEl.style.display=''; }
  finally{ showLoader(false); }
}

/* =================== CREATE / EDIT (RTE + form) =================== */
const em_title = document.getElementById('em_title');
const em_mode  = document.getElementById('em_mode');
const em_id    = document.getElementById('em_id');
const em_course_label=document.getElementById('em_course_label');
const em_course_id   =document.getElementById('em_course_id');
const em_module_label=document.getElementById('em_module_label');
const em_module_id   =document.getElementById('em_module_id');
const em_batch_label =document.getElementById('em_batch_label');
const em_batch_id    =document.getElementById('em_batch_id');
const em_title_input =document.getElementById('em_title_input');
const em_files       =document.getElementById('em_files');
const em_save        =document.getElementById('em_save');
const em_visibility  =document.getElementById('em_visibility');
const em_priority    =document.getElementById('em_priority');
const em_status      =document.getElementById('em_status');
const rte             = document.getElementById('rte');
const hiddenMessage   = document.getElementById('message_html');

function resetEditor(){
  em_title_input.value=''; em_files.value=''; rte.innerHTML='';
  em_course_id.value=courseSel.value; em_course_label.value = courseSel.options[courseSel.selectedIndex]?.text || '';
  em_module_id.value=moduleSel.value||''; em_module_label.value = moduleSel.value ? moduleSel.options[moduleSel.selectedIndex]?.text||'' : '';
  em_batch_id.value=batchSel.value||''; em_batch_label.value = batchSel.options[batchSel.selectedIndex]?.text || '';
  em_visibility.value=''; em_priority.value='normal'; em_status.value='draft';
}

function openCreateModal(){
  if(!courseSel.value || !batchSel.value) return Swal.fire('Select filters','Pick Course → Module → Batch first.','info');
  if(binMode) return Swal.fire('Bin view active','Switch off Bin to create.','info');
  const m=new bootstrap.Modal(document.getElementById('editModal'));
  em_mode.value='create'; em_id.value=''; em_title.textContent='Create Notice'; resetEditor(); m.show();
}

async function openEdit(id){
  const m=new bootstrap.Modal(document.getElementById('editModal'));
  if(binMode) return Swal.fire('Bin view','Restore the item first to edit.','info');
  em_mode.value='edit'; em_id.value=id; em_title.textContent='Edit Notice'; resetEditor();
  try{
    // fetch single row. backend doesn't have show by id — we query index for id filter
    const usp = new URLSearchParams({id});
    const res = await fetch('/api/notices?'+usp.toString(), {headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
    const j = await res.json();
    if(!res.ok) throw new Error(j?.message||'Fetch failed');
    const row = (j?.data && j.data[0]) ? j.data[0] : null;
    if(row){
      em_title_input.value = row.title||'';
      rte.innerHTML = row.message_html || '';
      em_visibility.value = row.visibility_scope || '';
      em_priority.value = row.priority || 'normal';
      em_status.value = row.status || 'draft';
      em_course_id.value = row.course_id||courseSel.value;
      em_module_id.value = row.course_module_id||moduleSel.value||'';
      em_batch_id.value = row.batch_id||batchSel.value||'';
      em_course_label.value = courseSel.options[courseSel.selectedIndex]?.text || '';
      em_module_label.value = moduleSel.options[moduleSel.selectedIndex]?.text || '';
      em_batch_label.value  = batchSel.options[batchSel.selectedIndex]?.text || '';
    }
    m.show();
  }catch(e){ err('Failed to open editor'); }
}

// RTE toolbar (execCommand)
document.getElementById('rte_toolbar').addEventListener('click', (e)=>{
  const btn = e.target.closest('[data-cmd]'); if(!btn) return;
  const cmd = btn.getAttribute('data-cmd');
  if(cmd === 'createLink'){
    const url = prompt('Enter URL (including https://):','https://');
    if(url) document.execCommand('createLink', false, url);
    return;
  }
  document.execCommand(cmd, false, null);
});
document.getElementById('insertHeading').addEventListener('change', function(){ const v=this.value; if(!v) return; if(v==='h2') document.execCommand('formatBlock', false, 'h2'); else if(v==='p') document.execCommand('formatBlock', false, 'p'); this.value=''; });

function collectRteHtml(){ hiddenMessage.value = rte.innerHTML.trim(); }

em_save.addEventListener('click', async ()=>{
  collectRteHtml();
  if(!em_title_input.value.trim()) return Swal.fire('Title required','Please enter a title.','info');
  const fd = new FormData();
  fd.append('course_id', em_course_id.value);
  if(em_module_id.value) fd.append('course_module_id', em_module_id.value);
  fd.append('batch_id', em_batch_id.value);
  fd.append('title', em_title_input.value.trim());
  if(hiddenMessage.value) fd.append('message_html', hiddenMessage.value);
  if(em_visibility.value) fd.append('visibility_scope', em_visibility.value);
  if(em_priority.value) fd.append('priority', em_priority.value);
  if(em_status.value) fd.append('status', em_status.value);
  if(em_files.files && em_files.files.length) { [...em_files.files].forEach(f=> fd.append('attachments[]', f)); }

  try{
    let url = API.store; let method = 'POST';
    if(em_mode.value==='edit' && em_id.value){
      url = API.update(em_id.value);
      fd.append('_method','PATCH'); method='POST';
    }
    const res = await fetch(url, { method, headers:{ Authorization:'Bearer '+TOKEN, Accept:'application/json' }, body: fd });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error((j?.message) || (j?.errors ? Object.values(j.errors)[0] : 'Save failed'));
    ok('Notice saved');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).hide();
    loadList();
  }catch(e){ err(e.message||'Save failed'); }
});

/* =================== DELETE / RESTORE =================== */
async function deleteItem(id){
  const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete notice?',text:'This will move the notice to Bin.',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'});
  if(!isConfirmed) return;
  try{
    const res=await fetch(API.destroy(id),{method:'DELETE',headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
    const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Delete failed');
    ok('Moved to Bin'); loadList();
  }catch(e){ err(e.message||'Delete failed'); }
}

async function purgeItem(id){
  const {isConfirmed}=await Swal.fire({icon:'error',title:'Delete permanently?',text:'This cannot be undone.',showCancelButton:true,confirmButtonText:'Delete permanently',confirmButtonColor:'#b91c1c'});
  if(!isConfirmed) return;
  try{
    const res=await fetch(API.forceDelete(id),{method:'DELETE',headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
    const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Purge failed');
    ok('Deleted permanently'); loadList();
  }catch(e){ err(e.message||'Purge failed'); }
}

async function restoreItem(id){
  try{
    // your route is POST /notices/{id}/restore
    const res=await fetch(API.restore(id),{method:'POST',headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
    const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Restore failed');
    ok('Restored'); loadList();
  }catch(e){ err(e.message||'Restore failed'); }
}

/* =================== VIEWER =================== */
const vModal = new bootstrap.Modal(document.getElementById('viewModal'));
const attList = document.getElementById('attList');
const viewer  = document.getElementById('viewer');
const vMime   = document.getElementById('vMime');
const vSize   = document.getElementById('vSize');
const vTitle  = document.getElementById('vTitle');
const vMessage = document.getElementById('vMessage');
const vPriority = document.getElementById('vPriority');
const vStatus = document.getElementById('vStatus');

let currentUuid = null;

async function openView(uuid){
  try{
    const res = await fetch(API.show(uuid), {headers:{Authorization:'Bearer '+TOKEN, Accept:'application/json'}});
    const row = await res.json();
    if(!res.ok) throw new Error(row?.message || 'Load failed');
    currentUuid = uuid;
    // row should be the notice object (controller returns row)
    const atts = Array.isArray(row.attachments) ? row.attachments : (Array.isArray(row.attachment) ? row.attachment : []);
    vTitle.textContent = row.title || '—';
    vPriority.textContent = row.priority || '—';
    vStatus.textContent = row.status || '—';
    vMessage.innerHTML = row.message_html || '<div class="text-muted small">No message.</div>';

    attList.innerHTML = atts.length ? atts.map(a=>`
      <div class="att" data-id="${H.esc(a.id)}" data-mime="${H.esc(a.mime||'')}" data-ext="${H.esc(a.ext||'')}" data-url="${H.esc(a.url||'')}">
        <div class="icon"><i class="fa ${H.icon(a.ext)}"></i></div>
        <div class="flex-grow-1">
          <div class="name">${H.esc((a.name || (a.ext||'').toUpperCase()+' file'))}</div>
          <div class="meta">${H.esc(a.mime||'-')} • ${H.bytes(a.size||0)}</div>
        </div>
        <button class="btn btn-light btn-sm"><i class="fa fa-eye"></i> Preview</button>
      </div>`).join('') : '<div class="small text-muted">No attachments.</div>';

    attList.querySelectorAll('.att').forEach(div=>{
      div.querySelector('button')?.addEventListener('click', ()=> previewAttachment(div.dataset));
      div.addEventListener('dblclick', ()=> previewAttachment(div.dataset));
    });

    viewer.innerHTML = '<div class="text-muted small text-center">Select a file on the left to preview.</div>';
    vMime.textContent='—'; vSize.textContent='—';

    vModal.show();
  }catch(e){ err(e.message||'Open failed'); }
}

async function previewAttachment(meta){
  const {id, mime, ext, url} = meta;
  // prefer stream url provided by backend (attachments.stream_url). If not present, construct using uuid.
  let fetchUrl = url || (`/api/notices/stream/${encodeURIComponent(currentUuid)}/${encodeURIComponent(id)}`);
  vMime.textContent = mime || ext || '—';
  vSize.textContent = '—';
  viewer.innerHTML  = '<div class="text-muted">Loading preview…</div>';

  try{
    const res = await fetch(fetchUrl, {headers:{Authorization:'Bearer '+TOKEN,Accept:'*/*'}});
    if(!res.ok) throw new Error('Unable to fetch file');
    const blob = await res.blob();
    vSize.textContent = H.bytes(blob.size||0);
    const lower = (ext||'').toLowerCase();
    if ((mime||'').startsWith('image/') || ['png','jpg','jpeg','webp','svg'].includes(lower)) {
      const obj = URL.createObjectURL(blob);
      viewer.innerHTML = `<img src="${obj}" alt="image">`;
      return;
    }
    if (mime==='application/pdf' || lower==='pdf') {
      const obj = URL.createObjectURL(blob);
      viewer.innerHTML = `<iframe src="${obj}#toolbar=0&navpanes=0&scrollbar=1"></iframe>`;
      return;
    }
    if (['doc','docx'].includes(lower)) {
      viewer.innerHTML = `<div id="docxRoot" class="p-2 w-100"></div>`;
      const arrayBuffer = await blob.arrayBuffer();
      window.docx && window.docx.renderAsync(new Uint8Array(arrayBuffer), document.getElementById('docxRoot'))
        .catch(()=> viewer.innerHTML='<div class="text-danger small">DOCX preview failed.</div>');
      return;
    }
    if (['xls','xlsx'].includes(lower)) {
      const ab = await blob.arrayBuffer();
      const wb = XLSX.read(ab,{type:'array'});
      const sheet = wb.SheetNames[0];
      const html = XLSX.utils.sheet_to_html(wb.Sheets[sheet], {editable:false});
      viewer.innerHTML = `<div class="p-2" style="max-height:70vh;overflow:auto;border-radius:12px">${html}</div>`;
      return;
    }
    if (['ppt','pptx'].includes(lower)) {
      viewer.innerHTML = `<div id="pptxRoot" class="p-2 w-100" style="max-height:70vh;overflow:auto;"></div>`;
      const arrbuf = await blob.arrayBuffer();
      try{
        const b64 = await blobToDataURL(new Blob([arrbuf], {type: mime||'application/octet-stream'}));
        window.PPTXjs && PPTXjs.render(document.getElementById('pptxRoot'), b64)
          .catch(()=> viewer.innerHTML='<div class="text-danger small">PPTX preview failed.</div>');
      }catch(_){ viewer.innerHTML='<div class="text-danger small">PPTX preview not supported for this file.</div>'; }
      return;
    }
    viewer.innerHTML = `<div class="text-muted small">Preview not supported for this type.</div>`;
  }catch(e){
    viewer.innerHTML = `<div class="text-danger small">${H.esc(e.message||'Preview failed')}</div>`;
  }
}
async function blobToDataURL(blob){ return await new Promise((res)=>{ const r=new FileReader(); r.onload=()=>res(r.result); r.readAsDataURL(blob); }); }
</script>
@endpush
