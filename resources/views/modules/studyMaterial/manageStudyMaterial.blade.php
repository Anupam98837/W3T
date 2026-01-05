{{-- resources/views/modules/study/manageStudyMaterial.blade.php --}}
@section('title','Manage Study Materials')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Shell ===== */
.sm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
/* ===== FIX: Dropdown visibility in tables ===== */

/* 1. Ensure table wrappers don't clip */
.table-wrap {
  overflow: visible !important;
}

.table-wrap .table-responsive {
  overflow-x: auto;
  overflow-y: visible !important;
  position: relative;
}

.module-materials .table-responsive {
  overflow-x: auto;
  overflow-y: visible !important;
  position: relative;
}

/* 2. Card body must not clip either */
.table-wrap.card .card-body {
  overflow: visible !important;
}

/* 3. Dropdown positioning */
.table-wrap .dropdown {
  position: static !important; /* Changed from relative to static */
  z-index: auto;
}

.table-wrap .dd-toggle {
  position: relative;
  z-index: 1;
  border-radius: 10px;
}

/* 4. Dropdown menu with maximum z-index and fixed positioning fallback */
.dropdown-menu {
  border-radius: 12px;
  border: 1px solid var(--line-strong);
  box-shadow: var(--shadow-2);
  min-width: 220px;
  z-index: 9999 !important; /* Increased from 5000 */
  position: absolute !important;
}

.dropdown-menu.show {
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
}

/* 5. Ensure no parent elements have transform/filter/perspective 
      (these create new stacking contexts that can clip dropdowns) */
.table-wrap,
.table-wrap .card-body,
.table-responsive {
  transform: none !important;
  filter: none !important;
  perspective: none !important;
}

/* 6. Alternative: Use Popper's strategy='fixed' for viewport positioning */
.dropdown-menu[data-popper-placement] {
  position: fixed !important;
}
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
</style>
@endpush

@section('content')
<div class="sm-wrap">

  {{-- ===== Global Toolbar: Course & Batch only ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Course</label>
        <select id="courseSel" class="form-select" style="min-width:240px;">
          <option value="">Select a course…</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Batch</label>
        <select id="batchSel" class="form-select" style="min-width:220px;" disabled>
          <option value="">Select a batch…</option>
        </select>
      </div>

      <div class="ms-auto small text-muted d-none d-md-block">
        Pick Course & Batch to load study materials.
      </div>
    </div>
  </div>

  {{-- ===== Tabs (Active + Bin only) ===== --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" href="javascript:void(0)" id="smTabActive" data-scope="active">
        <i class="fa-solid fa-book-open me-2" aria-hidden="true"></i>
        Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="javascript:void(0)" id="smTabBin" data-scope="bin">
        <i class="fa-solid fa-trash-can me-2" aria-hidden="true"></i>
        Bin
      </a>
    </li>
  </ul>

  {{-- ===== Toolbar Panel (OUTSIDE table card) ===== --}}
  <div class="panel mb-3">
    <div class="row align-items-center g-2 px-3 pt-3 pb-2 mfa-toolbar" id="listToolbar">
      <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">

        {{-- Per-page --}}
        <div class="d-flex align-items-center gap-2">
          <label for="perPageSel" class="text-muted small mb-0">Per page</label>
          <select id="perPageSel" class="form-select form-select-sm" style="width:auto; min-width:90px;">
            <option value="10">10</option>
            <option value="20" selected>20</option>
            <option value="30">30</option>
            <option value="50">50</option>
          </select>
        </div>

        {{-- Search --}}
        <div class="position-relative flex-grow-1" style="min-width:260px;">
          <input id="q" type="text" class="form-control ps-5" placeholder="Search title/description…" disabled>
          <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
        </div>

      </div>

      <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end gap-2">

        {{-- Filters button --}}
        <button type="button" id="btnFilters" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#smFilterModal">
          <i class="fa fa-filter me-1"></i> Filters
        </button>

        {{-- Create (redirects to /study-material/create) --}}
        <a
          id="btnCreate"
          href="/study-material/create"
          class="btn btn-primary"
          data-create-url="/study-material/create"
        >
          <i class="fa fa-plus me-1"></i> New Material
        </a>
      </div>
    </div>
  </div>

  {{-- ===== Card: Table ===== --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      {{-- ===== Table ===== --}}
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
                <i class="fa fa-book mb-2" style="font-size:28px;opacity:.6"></i>
                <div>Please select Course → Batch to load study materials.</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="empty" class="empty p-4 text-center" style="display:none;">
        <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
        <div>No study materials found.</div>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="metaTxt">—</div>
        <nav style="position:relative; z-index:1;"><ul id="pager" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>
</div>

{{-- ================= View Material (modal) ================= --}}
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xxl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-eye fa-fw me-2"></i>View Material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="">
            <div class="attachment-list" id="attList"></div>
          </div>
          <div class="">
            <div class="viewer-tools mb-2">
              <div class="badge badge-soft-info" id="vMime">—</div>
              <div class="badge badge-soft-primary" id="vSize">—</div>
            </div>
            <div class="viewer-wrap" id="viewer">
              <div class="text-muted small text-center">
                Select a file on the left to preview. Right-click is disabled to discourage downloads.
              </div>
            </div>
            <div class="small text-muted mt-2">
              Note: True “no download” isn’t enforceable on the web; we stream privately and render inline to discourage saving.
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

{{-- ================= Filters Modal ================= --}}
<div class="modal fade" id="smFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-sliders me-2"></i> Filters
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="fltStatus" class="form-label">Status</label>
          <select id="fltStatus" class="form-select">
            <option value="">All</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="scheduled">Scheduled</option>
          </select>
        </div>

        <div class="mb-0">
          <label for="fltSort" class="form-label">Sort by</label>
          <select id="fltSort" class="form-select">
            <option value="-created_at" selected>Newest first</option>
            <option value="created_at">Oldest first</option>
            <option value="title">Title A → Z</option>
            <option value="-title">Title Z → A</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="fltApply">
          <i class="fa fa-filter me-1"></i> Apply
        </button>
      </div>
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
if (!TOKEN){
  Swal.fire('Login needed','Your session expired. Please login again.','warning')
    .then(()=> location.href='/');
}

async function getMyRole(token){
  if(!token) return '';
  try{
    const res = await fetch('/api/auth/my-role', {
      method: 'GET',
      headers: { Authorization: 'Bearer ' + token, Accept: 'application/json', 'Cache-Control':'no-cache' }
    });
    if(!res.ok) return '';
    const data = await res.json().catch(()=>null);
    const role = data?.role;
    return role ? String(role).trim().toLowerCase() : '';
  }catch(_){
    return '';
  }
}

let ROLE = '';
let PERM = {
  canSeeBin: false,
  canCreate: false,
  canEdit: false,
  canDelete: false
};

async function initRoleAndPermissions(){
  ROLE = (await getMyRole(TOKEN)) || '';
  const r = String(ROLE).toLowerCase();

  const isAdmin = r.includes('admin') || r.includes('super_admin') || r.includes('superadmin');
  const isInstructor = r.includes('instructor');
  const isStudent = r.includes('student') || (!isAdmin && !isInstructor);

  PERM = {
    canSeeBin: isAdmin,
    canCreate: !isStudent,
    canEdit: isAdmin,
    canDelete: isAdmin || isInstructor
  };

  applyRoleUI();
}

function applyRoleUI(){
  if (smTabBin && !PERM.canSeeBin) smTabBin.style.display = 'none';

  if(btnCreate){
    if(!PERM.canCreate){
      btnCreate.style.display = 'none';
      btnCreate.classList.add('disabled');
      btnCreate.setAttribute('aria-disabled','true');
      btnCreate.setAttribute('tabindex','-1');
    } else {
      btnCreate.style.display = '';
      btnCreate.classList.remove('disabled');
      btnCreate.removeAttribute('tabindex');
      btnCreate.setAttribute('aria-disabled','false');
    }
  }
}

const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
const errToast = new bootstrap.Toast(document.getElementById('errToast'));
const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

/** API endpoints used by this view (tweak here if your routes differ) */
const API = {
  index : (qs)=> '/api/study-materials?' + qs.toString(),
  show  : (uuid)=> `/api/study-materials/show/${encodeURIComponent(uuid)}`,
  destroy: (id, force=false)=> `/api/study-materials/${encodeURIComponent(id)}${force ? '?force=1' : ''}`,
  restore: (id)=> `/api/study-materials/${encodeURIComponent(id)}/restore`,
  file   : (id)=> `/api/study-materials/file/${encodeURIComponent(id)}`,
  binIndex : (qs)=> '/api/study-materials/deleted?' + qs.toString()
};

const H = {
  esc: (s)=>{
    const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
    return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>m[ch]);
  },
  fmtDateTime(iso){
    if(!iso) return '-';
    const d=new Date(iso);
    return isNaN(d)
      ? H.esc(iso)
      : d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
  },
  bytes(n){
    n=Number(n||0); if(!n) return '0 B';
    const u=['B','KB','MB','GB']; let i=0;
    while(n>=1024 && i<u.length-1){ n/=1024; i++; }
    return n.toFixed(n<10&&i?1:0)+' '+u[i];
  },
  icon(ext){
    ext=(ext||'').toLowerCase();
    const map={
      pdf:'fa-file-pdf',doc:'fa-file-word',docx:'fa-file-word',
      ppt:'fa-file-powerpoint',pptx:'fa-file-powerpoint',
      xls:'fa-file-excel',xlsx:'fa-file-excel',
      txt:'fa-file-lines',
      png:'fa-file-image',jpg:'fa-file-image',jpeg:'fa-file-image',
      webp:'fa-file-image',svg:'fa-file-image'
    };
    return map[ext]||'fa-file';
  }
};

function extOf(u){
  try { return (u||'').split('?')[0].split('.').pop().toLowerCase(); }
  catch(e){ return ''; }
}

const courseSel   = document.getElementById('courseSel');
const batchSel    = document.getElementById('batchSel');
const q           = document.getElementById('q');
const btnCreate   = document.getElementById('btnCreate');
const rowsEl      = document.getElementById('rows');
const loaderRow   = document.getElementById('loaderRow');
const emptyEl     = document.getElementById('empty');
const askEl       = document.getElementById('ask');
const pager       = document.getElementById('pager');
const metaTxt     = document.getElementById('metaTxt');

const listToolbar  = document.getElementById('listToolbar');
const perPageSel   = document.getElementById('perPageSel');
const toolbarPanel = listToolbar ? listToolbar.closest('.panel') : null;

const smTabActive  = document.getElementById('smTabActive');
const smTabBin     = document.getElementById('smTabBin');

const fltStatus = document.getElementById('fltStatus');
const fltSort   = document.getElementById('fltSort');
const fltApply  = document.getElementById('fltApply');

let sort    = '-created_at';
let page    = 1;
let perPage = 20;
let scope   = 'active';   // 'active' | 'bin'
let binMode = false;
let statusFilter = '';

let modulesForTable = [];
const moduleMaterialsCache = new Map();

/* ================= SCOPE HANDLING ================== */
function setScope(newScope){
  scope   = newScope;
  binMode = (scope === 'bin');

  if (smTabActive && smTabBin) {
    smTabActive.classList.toggle('active',   scope === 'active');
    smTabBin.classList.toggle('active',      scope === 'bin');
  }

  if (toolbarPanel) {
    toolbarPanel.classList.toggle('d-none', scope === 'bin');
  }

  if (listToolbar) {
    if (scope === 'bin') {
      listToolbar.classList.add('opacity-75');
      q.disabled = true;
    } else {
      listToolbar.classList.remove('opacity-75');
      q.disabled = !batchSel.value;
    }
  }

  // create button gating (anchor needs bootstrap disabled class)
  if(btnCreate){
    const shouldDisable = (!PERM.canCreate) || (!batchSel.value) || (scope === 'bin');
    btnCreate.classList.toggle('disabled', shouldDisable);
    btnCreate.setAttribute('aria-disabled', shouldDisable ? 'true' : 'false');
    if(shouldDisable) btnCreate.setAttribute('tabindex','-1'); else btnCreate.removeAttribute('tabindex');
  }

  page = 1;
  moduleMaterialsCache.clear();

  if (batchSel.value) {
    renderModuleTable();
  } else {
    rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
    emptyEl.style.display='none';
    askEl.style.display='';
    pager.innerHTML='';
    metaTxt.textContent='—';
  }
}

/* =================== INIT =================== */
(async function boot(){
  await initRoleAndPermissions().catch(()=>{});
  await loadCourses();
  wire();
  setScope('active');
})();

function enableFilters(on){
  [batchSel, q].forEach(el=> el.disabled = !on);
  if(btnCreate){
    const shouldDisable = (!PERM.canCreate) || (!on) || (scope === 'bin');
    btnCreate.classList.toggle('disabled', shouldDisable);
    btnCreate.setAttribute('aria-disabled', shouldDisable ? 'true' : 'false');
    if(shouldDisable) btnCreate.setAttribute('tabindex','-1'); else btnCreate.removeAttribute('tabindex');
  }
}

function wire(){

  /* ✅ FIXED: Dropdown handling with proper positioning */

document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();
  e.stopImmediatePropagation();

  // Close other open dropdowns
  document.querySelectorAll('.dd-toggle').forEach(other => {
    if (other !== btn) {
      try {
        const instance = bootstrap.Dropdown.getInstance(other);
        if (instance) instance.hide();
      } catch(_) {}
    }
  });

  // Create/get dropdown with optimized config
  const dd = bootstrap.Dropdown.getOrCreateInstance(btn, {
    autoClose: true,  // Changed from 'outside' to true for better behavior
    boundary: 'viewport',
    display: 'dynamic',
    popperConfig: {
      strategy: 'fixed',  // Use fixed positioning relative to viewport
      modifiers: [
        {
          name: 'preventOverflow',
          options: {
            boundary: 'viewport',
            padding: 8
          }
        },
        {
          name: 'flip',
          options: {
            fallbackPlacements: ['top-end', 'bottom-end', 'left', 'right']
          }
        },
        {
          name: 'offset',
          options: {
            offset: [0, 4]
          }
        }
      ]
    }
  });
  
  dd.toggle();
}, true);

// Additional fix: Ensure dropdowns stay visible when opened
document.addEventListener('shown.bs.dropdown', (e) => {
  const menu = e.target.querySelector('.dropdown-menu');
  if (menu) {
    menu.style.zIndex = '9999';
    menu.style.position = 'fixed';
  }
});

  /* ========= Column sorting (works with module view) ========= */
  document.querySelectorAll('thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      sort = (sort===col) ? ('-'+col) : (sort==='-'+col ? col : (col==='created_at' ? '-created_at' : col));
      page = 1;
      moduleMaterialsCache.clear();
      if (batchSel.value) renderModuleTable();

      document.querySelectorAll('thead th.sortable').forEach(t=> t.classList.remove('asc','desc'));
      if(sort===col) th.classList.add('asc');
      if(sort==='-'+col) th.classList.add('desc');
    });
  });

  /* ========= Course change ========= */
  courseSel.addEventListener('change', async ()=>{
    batchSel.innerHTML = '<option value="">Select a batch…</option>';
    enableFilters(false);

    rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
    emptyEl.style.display='none';
    askEl.style.display='';
    pager.innerHTML='';
    metaTxt.textContent='—';
    modulesForTable = [];
    moduleMaterialsCache.clear();

    if(!courseSel.value) return;

    await loadModules(courseSel.value);
    await loadBatches(courseSel.value);
    enableFilters(true);
  });

  /* ========= Batch change ========= */
  batchSel.addEventListener('change', ()=>{
    moduleMaterialsCache.clear();
    if(batchSel.value){
      setScope(scope); // refresh create disabled state + rerender
      renderModuleTable();
    }else{
      rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
      emptyEl.style.display='none';
      askEl.style.display='';
      pager.innerHTML='';
      metaTxt.textContent='—';
      setScope(scope);
    }
  });

  /* ========= Per page ========= */
  if (perPageSel) {
    perPageSel.value = String(perPage);
    perPageSel.addEventListener('change', () => {
      perPage = Number(perPageSel.value) || 20;
      page = 1;
      moduleMaterialsCache.clear();
      if (batchSel.value) renderModuleTable();
    });
  }

  /* ========= Filters ========= */
  if (fltApply) {
    fltApply.addEventListener('click', () => {
      statusFilter = fltStatus ? fltStatus.value : '';
      sort         = fltSort ? fltSort.value : '-created_at';
      page = 1;
      moduleMaterialsCache.clear();
      if (batchSel.value) renderModuleTable();
      const m = bootstrap.Modal.getInstance(document.getElementById('smFilterModal'));
      if (m) m.hide();
    });
  }

  /* ========= Search ========= */
  let t;
  q.addEventListener('input', ()=>{
    clearTimeout(t);
    t=setTimeout(()=>{
      moduleMaterialsCache.clear();
      if(batchSel.value) renderModuleTable();
    }, 350);
  });

  /* ========= Tabs ========= */
  if (smTabActive && smTabBin) {
    smTabActive.addEventListener('click', (e)=>{
      e.preventDefault();
      if (scope !== 'active') setScope('active');
    });
    smTabBin.addEventListener('click', (e)=>{
      e.preventDefault();
      if (!PERM.canSeeBin) return;
      if (scope !== 'bin') setScope('bin');
    });
  }

  /* ========= Create button: redirect to /study-material/create (optionally with current filters) ========= */
  if (btnCreate) {
    btnCreate.addEventListener('click', (e)=>{
      const disabled = btnCreate.classList.contains('disabled') || btnCreate.getAttribute('aria-disabled') === 'true';
      if(disabled){
        e.preventDefault();
        return;
      }

      // Keep requested redirect path; we add context params (safe) when available.
      const base = btnCreate.dataset.createUrl || btnCreate.getAttribute('href') || '/study-material/create';
      const url = new URL(base, window.location.origin);

      if (courseSel.value) url.searchParams.set('course_id', courseSel.value);
      if (batchSel.value)  url.searchParams.set('batch_id', batchSel.value);

      e.preventDefault();
      window.location.href = url.pathname + (url.search || '');
    });
  }

  /* ========= Actions (single delegated handler) ========= */
  document.addEventListener('click', (e)=>{
    const item=e.target.closest('.dropdown-item[data-act]');
    if(!item) return;
    e.preventDefault();

    const act     = item.dataset.act;
    const id      = item.dataset.id;
    const uuid    = item.dataset.uuid;
    const moduleId= item.dataset.moduleId;

    // permission gating
    if(act==='edit'   && !PERM.canEdit)   return;
    if((act==='delete' || act==='purge') && !PERM.canDelete) return;
    if((act==='restore' || act==='purge') && !PERM.canSeeBin) return;

    if(act==='view')    openView(uuid);
    if(act==='edit')    openEditRedirect({ id, uuid, moduleId });
    if(act==='delete')  deleteItem(id);
    if(act==='purge')   purgeItem(id);
    if(act==='restore') restoreItem(id);

    const toggle=item.closest('.dropdown')?.querySelector('.dd-toggle');
    if(toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
  });

  document.getElementById('viewer').addEventListener('contextmenu', (e)=> e.preventDefault());
}

async function loadCourses(){
  try{
    const res=await fetch('/api/courses/my?status=published&per_page=1000',{
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
    });
    const j=await res.json();
    if(!res.ok) throw new Error(j?.message||'Failed to load courses');
    const items=j?.data||[];
    courseSel.innerHTML = '<option value="">Select a course…</option>' +
      items.map(c=>`<option value="${c.id}" data-uuid="${H.esc(c.uuid||'')}">${H.esc(c.title||'(untitled)')}</option>`).join('');
  }catch(e){ err(e.message||'Courses error'); }
}

async function loadModules(courseId){
  try{
    const res = await fetch(`/api/course-modules?course_id=${encodeURIComponent(courseId)}&per_page=1000`,{
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
    });
    const j = await res.json();
    if(!res.ok) throw new Error(j?.message||'Failed to load modules');
    modulesForTable = j?.data || [];
  }catch(e){
    modulesForTable = [];
    err(e.message||'Modules error');
  }
}

async function loadBatches(courseId){
  try{
    const qs = new URLSearchParams({course_id:courseId, per_page:'200'});
    const res=await fetch('/api/batches/my?'+qs.toString(),{
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
    });
    const j=await res.json();
    if(!res.ok) throw new Error(j?.message||'Failed to load batches');
    const items=j?.data||[];
    batchSel.innerHTML = '<option value="">Select a batch…</option>' +
      items.map(b=>`<option value="${b.id}" data-uuid="${H.esc(b.uuid||'')}">${H.esc(b.badge_title||'(untitled)')}</option>`).join('');
  }catch(e){ err(e.message||'Batches error'); }
}

function renderModuleTable(){
  if(!batchSel.value){
    showAsk(true);
    return;
  }

  showAsk(false);
  showLoader(true);
  emptyEl.style.display='none';
  pager.innerHTML='';
  metaTxt.textContent='—';

  rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());

  if(!modulesForTable.length){
    showLoader(false);
    emptyEl.style.display='';
    return;
  }

  const frag = document.createDocumentFragment();

  const total = modulesForTable.length;
  const per   = perPage;
  const pages = Math.max(1, Math.ceil(total / per));
  const cur   = Math.min(page, pages);

  const start = (cur - 1) * per;
  const end   = Math.min(start + per, total);
  const slice = modulesForTable.slice(start, end);

  slice.forEach(m => {
    const modId = m.id;
    const modTitle = m.title || '(untitled module)';
    const modDesc  = m.description || '';

    const tr = document.createElement('tr');
    tr.className = 'module-row';
    tr.dataset.moduleId = modId;

    tr.innerHTML = `
      <td>
        <button type="button" class="btn btn-sm btn-outline-secondary me-2 toggle-mod">
          <i class="fa fa-chevron-right"></i>
        </button>
        <span class="fw-semibold">${H.esc(modTitle)}</span>
        ${modDesc ? `<div class="small text-muted text-truncate" style="max-width:520px">${H.esc(modDesc)}</div>` : ''}
      </td>
      <td colspan="3" class="text-muted small">Module under "${H.esc(courseSel.options[courseSel.selectedIndex]?.text || '')}"</td>
      <td>-</td>
      <td class="text-end">
      
      </td>
    `;

    const trDetails = document.createElement('tr');
    trDetails.className = 'module-materials';
    trDetails.dataset.moduleId = modId;
    trDetails.style.display = 'none';
    trDetails.innerHTML = `
      <td colspan="6">
        <div class="p-3 border-top border-light-subtle" id="mm_wrap_${modId}">
          <div class="small text-muted">Click the arrow to load materials for this module & batch.</div>
        </div>
      </td>
    `;

    frag.appendChild(tr);
    frag.appendChild(trDetails);
  });

  rowsEl.appendChild(frag);

  const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
    <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;

  let html='';
  html+=li(cur<=1,false,'Previous',cur-1);
  const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
  if(s>1){
    html+=li(false,false,1,1);
    if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>';
  }
  for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
  if(e<pages){
    if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>';
    html+=li(false,false,pages,pages);
  }
  html+=li(cur>=pages,false,'Next',cur+1);
  pager.innerHTML=html;

  pager.querySelectorAll('a.page-link[data-page]').forEach(a=>{
    a.addEventListener('click',()=>{
      const t=Number(a.dataset.page);
      if(!t || t===page) return;
      page=Math.max(1,t);
      renderModuleTable();
      window.scrollTo({top:0,behavior:'smooth'});
    });
  });

  metaTxt.textContent = `Page ${cur} of ${pages} — ${total} module(s)`;

  rowsEl.querySelectorAll('.module-row .toggle-mod').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const tr = btn.closest('.module-row');
      const moduleId = tr.dataset.moduleId;
      const detailsRow = rowsEl.querySelector(`.module-materials[data-module-id="${moduleId}"]`);
      const icon = btn.querySelector('i');

      if(detailsRow.style.display === 'none'){
        detailsRow.style.display = '';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
        loadModuleMaterials(moduleId);
      }else{
        detailsRow.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
      }
    });
  });

  rowsEl.querySelectorAll('[data-act="create-under-module"]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      if(!PERM.canCreate) return;
      const mId = btn.dataset.moduleId;
      const base = btnCreate?.dataset?.createUrl || '/study-material/create';
      const qs = new URLSearchParams({
        course_id: courseSel.value,
        course_module_id: mId,
        batch_id: batchSel.value
      });
      window.location.href = `${base}?${qs.toString()}`;
    });
  });

  showLoader(false);
}

async function loadModuleMaterials(moduleId){
  const wrap = document.getElementById(`mm_wrap_${moduleId}`);
  if(!wrap) return;

  if(moduleMaterialsCache.has(moduleId)){
    renderModuleMaterials(moduleId, moduleMaterialsCache.get(moduleId));
    return;
  }

  wrap.innerHTML = '<div class="small text-muted">Loading materials…</div>';

  try{
    const usp = new URLSearchParams({
      course_id: courseSel.value,
      course_module_id: moduleId,
      batch_id: batchSel.value,
      per_page: 500,
      sort
    });

    if (q.value.trim()) usp.set('search', q.value.trim());
    if (statusFilter) usp.set('status', statusFilter);
    if (!binMode) usp.set('include_deleted','0');

    const url = (scope === 'bin') ? API.binIndex(usp) : API.index(usp);

    const res = await fetch(url,{
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json','Cache-Control':'no-cache'}
    });
    const j=await res.json();
    if(!res.ok) throw new Error(j?.message||'Load failed');

    const items = j?.data || [];
    moduleMaterialsCache.set(moduleId, items);
    renderModuleMaterials(moduleId, items);
  }catch(e){
    wrap.innerHTML = `<div class="text-danger small">${H.esc(e.message||'Failed to load materials')}</div>`;
  }
}

function renderModuleMaterials(moduleId, items){
  const wrap = document.getElementById(`mm_wrap_${moduleId}`);
  if(!wrap) return;

  if(!items.length){
    wrap.innerHTML = '<div class="small text-muted">No study materials for this module & batch.</div>';
    return;
  }

  const rows = items.map(r=>`
    <tr>
      <td>
        <div class="fw-semibold">${H.esc(r.title||'(untitled)')}</div>
        <div class="small text-muted text-truncate" style="max-width:520px">${H.esc(r.description||'')}</div>
      </td>
      <td>${H.esc(r.batch_title || r.batch_name || '-')}</td>
      <td class="text-center">
        <span class="badge badge-soft-primary">
          <i class="fa fa-paperclip"></i> ${Number(r.attachment_count||0)}
        </span>
      </td>
      <td>${H.fmtDateTime(r.created_at)}</td>
      <td class="text-end">${rowActions(r, moduleId)}</td>
    </tr>
  `).join('');

  wrap.innerHTML = `
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead style="display:none">
          <tr>
            <th>Title</th>
            <th style="width:16%;">Batch</th>
            <th class="text-center" style="width:120px;">Files</th>
            <th style="width:160px;">Created</th>
            <th class="text-end" style="width:112px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </div>
  `;
}

function showAsk(v){ askEl.style.display = v ? '' : 'none'; }
function showLoader(v){ loaderRow.style.display = v ? '' : 'none'; }

function rowActions(r, moduleIdForRedirect){
  // Bin scope
  if (scope === 'bin') {
    if(!PERM.canSeeBin) return '';
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

  // Active scope
  return `
    <div class="dropdown text-end" data-bs-display="static">
      <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
        <i class="fa fa-ellipsis-vertical"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button class="dropdown-item" data-act="view" data-uuid="${H.esc(r.uuid)}"><i class="fa fa-eye"></i> View</button></li>
        ${PERM.canEdit ? `<li>
          <button
            class="dropdown-item"
            data-act="edit"
            data-id="${r.id}"
            data-uuid="${H.esc(r.uuid||'')}"
            data-module-id="${H.esc(r.course_module_id || moduleIdForRedirect || '')}"
          >
            <i class="fa fa-pen-to-square"></i> Edit
          </button>
        </li>` : ``}
        ${PERM.canDelete ? `<li><hr class="dropdown-divider"></li>
        <li><button class="dropdown-item text-danger" data-act="delete" data-id="${r.id}"><i class="fa fa-trash"></i> Delete</button></li>` : ``}
      </ul>
    </div>`;
}

/* =================== EDIT REDIRECT ===================
   ✅ Removed Add/Edit modal.
   - "New Material" goes to /study-material/create
   - "Edit" goes to /study-material/create with query so the create page can prefill.
====================================================== */
function openEditRedirect({ id, uuid, moduleId }){
  if(binMode) return Swal.fire('Bin view','Restore the item first to edit.','info');
  if(!PERM.canEdit) return;

  const base = btnCreate?.dataset?.createUrl || '/study-material/create';
  const qs = new URLSearchParams();

  // pass both (safe): your create page can use either
  if (id)   qs.set('id', id);
  if (uuid) qs.set('uuid', uuid);

  // keep context
  if (courseSel.value) qs.set('course_id', courseSel.value);
  if (batchSel.value)  qs.set('batch_id', batchSel.value);
  if (moduleId)        qs.set('course_module_id', moduleId);

  window.location.href = `${base}?${qs.toString()}`;
}

/* =================== DELETE / RESTORE =================== */
async function deleteItem(id){
  if(!PERM.canDelete) return;
  const {isConfirmed}=await Swal.fire({
    icon:'warning',
    title:'Delete material?',
    text:'This will move the item to Bin.',
    showCancelButton:true,
    confirmButtonText:'Delete',
    confirmButtonColor:'#ef4444'
  });
  if(!isConfirmed) return;
  try{
    const res=await fetch(API.destroy(id,false),{
      method:'DELETE',
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
    });
    const j=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message||'Delete failed');
    ok('Moved to Bin');
    moduleMaterialsCache.clear();
    if(batchSel.value) renderModuleTable();
  }catch(e){ err(e.message||'Delete failed'); }
}

async function purgeItem(id){
  if(!PERM.canDelete) return;
  const {isConfirmed}=await Swal.fire({
    icon:'error',
    title:'Delete permanently?',
    text:'This cannot be undone.',
    showCancelButton:true,
    confirmButtonText:'Delete permanently',
    confirmButtonColor:'#b91c1c'
  });
  if(!isConfirmed) return;
  try{
    const res=await fetch(API.destroy(id,true),{
      method:'DELETE',
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
    });
    const j=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message||'Purge failed');
    ok('Deleted permanently');
    moduleMaterialsCache.clear();
    if(batchSel.value) renderModuleTable();
  }catch(e){ err(e.message||'Purge failed'); }
}

async function restoreItem(id){
  if(!PERM.canSeeBin) return;
  try{
    const res=await fetch(API.restore(id),{
      method:'POST',
      headers:{
        Authorization:'Bearer '+TOKEN,
        Accept:'application/json'
      }
    });
    const j=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message||'Restore failed');
    ok('Restored');
    moduleMaterialsCache.clear();
    if(batchSel.value) renderModuleTable();
  }catch(e){
    err(e.message||'Restore failed');
  }
}

/* =================== VIEWER =================== */
const vModal = new bootstrap.Modal(document.getElementById('viewModal'));
const attList = document.getElementById('attList');
const viewer  = document.getElementById('viewer');
const vMime   = document.getElementById('vMime');
const vSize   = document.getElementById('vSize');

let currentManifest=null, currentUuid=null;

async function openView(uuid){
  try{
    const res=await fetch(API.show(uuid),{
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
    });
    const row=await res.json();
    if(!res.ok) throw new Error(row?.message||'Load failed');
    currentManifest=row; currentUuid=uuid;

    const atts = Array.isArray(row.attachment) ? row.attachment : [];
    attList.innerHTML = atts.length
      ? atts.map(a=>`
      <div class="att" data-id="${H.esc(a.id)}" data-mime="${H.esc(a.mime||'')}" data-ext="${H.esc(a.ext||'')}" data-url="${H.esc(a.url||'')}">
        <div class="icon"><i class="fa ${H.icon(a.ext)}"></i></div>
        <div class="flex-grow-1">
          <div class="name">${H.esc(a.name || ((a.ext||'').toUpperCase()+' file'))}</div>
          <div class="meta">${H.esc(a.mime||'-')} • ${H.bytes(a.size||0)}</div>
        </div>
        <button class="btn btn-light btn-sm"><i class="fa fa-eye"></i> Preview</button>
      </div>`).join('')
      : '<div class="small text-muted">No attachments.</div>';

    attList.querySelectorAll('.att').forEach(div=>{
      div.querySelector('button')?.addEventListener('click', ()=> previewAttachment(div.dataset));
      div.addEventListener('dblclick', ()=> previewAttachment(div.dataset));
    });

    viewer.innerHTML = '<div class="text-muted small text-center">Select a file on the left to preview.</div>';
    vMime.textContent='—'; vSize.textContent='—';

    vModal.show();

  }catch(e){
    err(e.message||'Open failed');
  }
}

async function previewAttachment(meta){
  const {id, mime, ext} = meta;
  const url = meta.url ? meta.url : API.file(id);

  vMime.textContent = mime || ext || '—';
  vSize.textContent = '—';
  viewer.innerHTML  = '<div class="text-muted">Loading preview…</div>';

  try{
    const res = await fetch(url, {headers:{Authorization:'Bearer '+TOKEN,Accept:'*/*'}});
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
      }catch(_){
        viewer.innerHTML='<div class="text-danger small">PPTX preview not supported on this file.</div>';
      }
      return;
    }

    viewer.innerHTML = `<div class="text-muted small">Preview not supported for this type.</div>`;
  }catch(e){
    viewer.innerHTML = `<div class="text-danger small">${H.esc(e.message||'Preview failed')}</div>`;
  }
}

async function blobToDataURL(blob){
  return await new Promise((res)=>{
    const r=new FileReader();
    r.onload=()=>res(r.result);
    r.readAsDataURL(blob);
  });
}
</script>
@endpush
