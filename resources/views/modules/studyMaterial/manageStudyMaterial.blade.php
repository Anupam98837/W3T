{{-- resources/views/modules/study/manageStudyMaterial.blade.php --}}
@section('title','Manage Study Materials')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Shell ===== */
.sm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}

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

#em_dropzone {
  transition: all 0.3s ease;
  border-width: 2px;
  border-color: #dee2e6;
}

#em_dropzone:hover {
  background-color:var(--primary)!important;
  border-color: #86b7fe !important;
}

#em_dropzone.dz-dragover {
  background-color: var(--primary) !important;
  border-color: #0d6efd !important;
}

/* Library file cards */
.library-file {
  transition: all 0.2s ease;
}

.library-file:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.library-file.border-primary {
  border-width: 2px !important;
}

/* File list items */
.list-group-item {
  transition: all 0.2s ease;
}

.list-group-item:hover {
  background-color: #f8f9fa;
}

.library-card {
  position: relative;
  overflow: hidden;
}

.lib-overlay-check {
  position: absolute;
  top: 8px;
  left: 10px;
  z-index: 10;
  background: rgba(255,255,255,0.9);
  padding: 4px 6px;
  border-radius: 999px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

html.theme-dark .lib-overlay-check {
  background: rgba(15,23,42,0.9);
}

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

        {{-- Create --}}
        <a
          id="btnCreate"
          href="/admin/course/studyMaterial/create"
          class="btn btn-primary"
          data-create-url="/admin/course/studyMaterial/create"
          disabled
        >
          <i class="fa fa-plus me-1"></i> New Material
        </a>
      </div>
    </div>
  </div>

  {{-- ===== Card: Toolbar + Table ===== --}}
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

{{-- ================= Create / Edit Material (modal) ================= --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="em_title" class="modal-title"><i class="fa fa-file-circle-plus me-2"></i>Create Material</h5>
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
            <input id="em_title_input" class="form-control" maxlength="255" placeholder="e.g., Week 1 — Intro & Notes">
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea id="em_desc" class="form-control" rows="4" placeholder="Optional details"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Attachments (PDF, DOC/DOCX, PPT/PPTX, XLS/XLSX, TXT, JPG/PNG/WEBP/SVG)</label>

            {{-- ===== Drag & Drop Zone ===== --}}
            <div id="em_dropzone" class="border rounded p-5 text-center" style="border-style: dashed !important; cursor: pointer; background-color: #f8f9fa;">
              <div class="dz-message">
                <i class="fa fa-cloud-upload fa-3x text-muted mb-3"></i>
                <h5>Drag & drop files here</h5>
                <p class="text-muted mb-2">or click to browse</p>
                <p class="small text-muted">Any format • up to 50 MB per file</p>
                <div class="mt-3">
                  <button type="button" class="btn btn-outline-primary btn-sm me-2" id="em_browse">
                    <i class="fa fa-folder-open me-1"></i> Browse Files
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="em_library">
                    <i class="fa fa-photo-film me-1"></i> Choose from Library
                  </button>
                </div>
              </div>
            </div>

            {{-- Hidden file input --}}
            <input id="em_files" type="file" class="d-none" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.webp,.svg,image/*,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/plain">

            {{-- Selected files list --}}
            <div id="em_files_list" class="mt-3"></div>

            <div class="small text-muted mt-1">Max 50MB per file. Videos are not allowed.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>

        {{-- (CHANGE #2) spinner inside Save button --}}
        <button id="em_save" class="btn btn-primary d-inline-flex align-items-center">
          <span id="em_save_spin" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
          <i id="em_save_icon" class="fa fa-save me-1"></i>
          <span id="em_save_txt">Save</span>
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= Library Modal ================= --}}
<div class="modal fade" id="libraryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-photo-film me-2"></i>Choose from Study Material Library
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        {{-- Search bar --}}
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text">
              <i class="fa fa-search"></i>
            </span>
            <input
              type="text"
              id="library_search"
              class="form-control"
              placeholder="Search by file name or study material title..."
            >
            <button class="btn btn-outline-secondary" type="button" id="library_search_btn">
              <i class="fa fa-search"></i>
            </button>
          </div>
        </div>

        {{-- Grid --}}
        <div id="library_files" class="row g-3">
          <div class="col-12 text-center py-5" id="library_loading">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading library files...</p>
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-between">
        <div class="small text-muted" id="library_selected_text">No items selected</div>
        <div>
          <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button id="library_select" class="btn btn-primary" disabled>
            Add selected
          </button>
        </div>
      </div>
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

    // accept {status:'success', role:'x'} or {success:true, role:'x'} or {role:'x'}
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
  const isStudent = r.includes('student') || (!isAdmin && !isInstructor); // safe fallback

  // ✅ your rules
  PERM = {
    canSeeBin: isAdmin,              // student/instructor: no bin
    canCreate: !isStudent,           // student: no create
    canEdit: isAdmin,                // instructor: edit hidden
    canDelete: isAdmin || isInstructor // student: delete hidden
  };

  applyRoleUI();
}

function applyRoleUI(){
  // Bin tab
  if (smTabBin && !PERM.canSeeBin) smTabBin.style.display = 'none';

  // Create button
  if(btnCreate){
    if(!PERM.canCreate){
      btnCreate.style.display = 'none';
      btnCreate.disabled = true;
    } else {
      btnCreate.style.display = '';
      btnCreate.disabled = !batchSel?.value || scope === 'bin';
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
  store : '/api/study-materials',
  update: (id)=> `/api/study-materials/${encodeURIComponent(id)}`,
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

const listToolbar = document.getElementById('listToolbar');
const perPageSel  = document.getElementById('perPageSel');
const btnFilters  = document.getElementById('btnFilters');
const toolbarPanel = listToolbar ? listToolbar.closest('.panel') : null;

const smTabActive   = document.getElementById('smTabActive');
const smTabBin      = document.getElementById('smTabBin');

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
      if (btnCreate) btnCreate.disabled = true;
    } else {
      listToolbar.classList.remove('opacity-75');
      q.disabled = !batchSel.value;
      if (btnCreate) btnCreate.disabled = !batchSel.value;
    }
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
loadCourses();
wire();
setScope('active');

function enableFilters(on){
  [batchSel, q].forEach(el=> el.disabled = !on);
  btnCreate.disabled = !on || scope === 'bin';
}

function wire(){

  /* ========= Dropdown buttons ========= */
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

  /* ========= Column sorting ========= */
  document.querySelectorAll('thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      sort = (sort===col) ? ('-'+col) : (sort==='-'+col ? col : (col==='created_at' ? '-created_at' : col));
      page = 1; loadList();
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
      renderModuleTable();
    }else{
      rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
      emptyEl.style.display='none';
      askEl.style.display='';
      pager.innerHTML='';
      metaTxt.textContent='—';
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
      if (scope !== 'bin') setScope('bin');
    });
  }

  document.addEventListener('click', (e)=>{
    const item=e.target.closest('.dropdown-item[data-act]');
    if(!item) return;
    e.preventDefault();
    const act=item.dataset.act, id=item.dataset.id, uuid=item.dataset.uuid;
    if(act==='view')    openView(uuid);
    if(act==='edit')    openEdit(id);
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
        <button class="btn btn-sm btn-primary" data-act="create-under-module" data-module-id="${modId}" style="display:none">
          <i class="fa fa-plus"></i> Add material
        </button>
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
      const mId = btn.dataset.moduleId;
      const base = btnCreate.dataset.createUrl || '/admin/course/studyMaterial/create';
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

    if (statusFilter) {
      usp.set('status', statusFilter);
    }

    if (!binMode) {
      usp.set('include_deleted','0');
    }

    const url = (scope === 'bin')
      ? API.binIndex(usp)
      : API.index(usp);

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
      <td class="text-end">${rowActions(r)}</td>
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

  wrap.querySelectorAll('.dropdown-item[data-act]').forEach(item=>{
    item.addEventListener('click', (e)=>{
      e.preventDefault();
      const act = item.dataset.act, id=item.dataset.id, uuid=item.dataset.uuid;
      if(act==='view')    openView(uuid);
      if(act==='edit')    openEdit(id);
      if(act==='delete')  deleteItem(id);
      if(act==='purge')   purgeItem(id);
      if(act==='restore') restoreItem(id);
      const toggle=item.closest('.dropdown')?.querySelector('.dd-toggle');
      if(toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
    });
  });
}

function showAsk(v){ askEl.style.display = v ? '' : 'none'; }
function showLoader(v){ loaderRow.style.display = v ? '' : 'none'; }

function rowActions(r){
  if (scope === 'bin') {
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
}

/* ====== Existing flat list (kept as-is, not used by module view, left untouched) ====== */
function rowHTML(r){
  const tr=document.createElement('tr');
  tr.innerHTML = `
    <td>
      <div class="fw-semibold">${H.esc(r.title||'(untitled)')}</div>
      <div class="small text-muted text-truncate" style="max-width:520px">${H.esc(r.description||'')}</div>
    </td>
    <td>${H.esc(r.module_title || r.course_module_title || '-')}</td>
    <td>${H.esc(r.batch_title || r.batch_name || '-')}</td>
    <td class="text-center">
      <span class="badge badge-soft-primary">
        <i class="fa fa-paperclip"></i> ${Number(r.attachment_count||0)}
      </span>
    </td>
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
      batch_id: batchSel.value,
      per_page: perPage,
      page,
      sort
    });
    if(q.value.trim()) usp.set('search', q.value.trim());
    if(!binMode){
      usp.set('include_deleted','0');
    }

    const url = binMode ? API.binIndex(usp) : API.index(usp);

    const res=await fetch(url, {
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json','Cache-Control':'no-cache'}
    });
    const j=await res.json();
    if(!res.ok) throw new Error(j?.message||'Load failed');

    const items=j?.data||[];
    if(items.length===0){ emptyEl.style.display=''; return; }

    const frag=document.createDocumentFragment();
    items.forEach(r=> frag.appendChild(rowHTML(r)));
    rowsEl.appendChild(frag);

    const meta = j?.meta || j?.pagination || {page:1, per_page:perPage, total:items.length};
    const total=Number(meta.total||items.length),
          per  =Number(meta.per_page||perPage),
          cur  =Number(meta.page||meta.current_page||1);
    const pages=Math.max(1, Math.ceil(total/per));
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
        loadList();
        window.scrollTo({top:0,behavior:'smooth'});
      });
    });
    metaTxt.textContent = `Page ${cur} of ${pages} — ${total} item(s)`;

  }catch(e){
    err(e.message||'Load error');
    emptyEl.style.display='';
  }finally{
    showLoader(false);
  }
}

/* =================== EDITOR =================== */
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
const em_desc        =document.getElementById('em_desc');
const em_files       =document.getElementById('em_files');
const em_save        =document.getElementById('em_save');
const em_dropzone    =document.getElementById('em_dropzone');
const em_browse      =document.getElementById('em_browse');
const em_libraryBtn  =document.getElementById('em_library');
const em_files_list  =document.getElementById('em_files_list');

/* (CHANGE #2) spinner refs + saving guard */
const em_save_spin = document.getElementById('em_save_spin');
const em_save_icon = document.getElementById('em_save_icon');
const em_save_txt  = document.getElementById('em_save_txt');
let emIsSaving = false;

let emDT = new DataTransfer();
let emLibraryUrls = [];

/* (CHANGE #1) dedupe helper (prevents same file being added twice) */
function fileKey(f){
  return `${f?.name||''}__${f?.size||0}__${f?.lastModified||0}`;
}

function renderEmFiles(){
  em_files_list.innerHTML = '';
  const frag = document.createDocumentFragment();

  Array.from(emDT.files).forEach((f, idx)=>{
    const row = document.createElement('div');
    row.className = 'd-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1 bg-white';

    row.innerHTML = `
      <div class="d-flex align-items-center flex-grow-1">
        <i class="fa ${H.icon(extOf(f.name))} me-2 text-muted"></i>
        <div class="flex-grow-1 text-truncate" title="${H.esc(f.name)}">${H.esc(f.name)}</div>
        <div class="small text-muted ms-2">${H.bytes(f.size)}</div>
      </div>
      <button type="button" class="btn btn-sm btn-outline-danger ms-2" data-type="upload" data-idx="${idx}">
        <i class="fa fa-trash"></i>
      </button>
    `;
    frag.appendChild(row);
  });

  emLibraryUrls.forEach((u, idx)=>{
    const ext = extOf(u);
    const row = document.createElement('div');
    row.className = 'd-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1 bg-light';

    row.innerHTML = `
      <div class="d-flex align-items-center flex-grow-1">
        <i class="fa ${H.icon(ext)} me-2 text-primary"></i>
        <div class="flex-grow-1 text-truncate" title="${H.esc(u)}">${H.esc(u.split('/').pop() || u)}</div>
        <span class="badge bg-secondary ms-2">Library</span>
      </div>
      <button type="button" class="btn btn-sm btn-outline-danger ms-2" data-type="library" data-idx="${idx}">
        <i class="fa fa-trash"></i>
      </button>
    `;
    frag.appendChild(row);
  });

  if(!frag.childNodes.length){
    em_files_list.innerHTML = '<div class="small text-muted">No files added yet.</div>';
  } else {
    em_files_list.appendChild(frag);
  }
}

em_files_list.addEventListener('click',(e)=>{
  const btn = e.target.closest('button[data-type]');
  if(!btn) return;
  const type = btn.dataset.type;
  const idx  = Number(btn.dataset.idx);
  if(type==='upload'){
    const next = new DataTransfer();
    Array.from(emDT.files).forEach((f,i)=>{ if(i!==idx) next.items.add(f); });
    emDT = next;
    em_files.files = emDT.files;
  }else if(type==='library'){
    if(idx>=0) emLibraryUrls.splice(idx,1);
  }
  renderEmFiles();
});

function addEditorFiles(files){
  const maxPer = 50*1024*1024;
  let big = false;

  /* (CHANGE #1) build existing set to prevent duplicates */
  const existing = new Set(Array.from(emDT.files).map(fileKey));

  Array.from(files||[]).forEach(f=>{
    if(f.size > maxPer){
      big = true;
      err(`"${f.name}" exceeds 50 MB.`);
      return;
    }

    const k = fileKey(f);
    if(existing.has(k)) return; // skip duplicates
    existing.add(k);

    emDT.items.add(f);
  });

  em_files.files = emDT.files;
  if(!big) ok('File(s) added');
  renderEmFiles();
}

if(em_dropzone){
  /* (CHANGE #1) ignore clicks on inner buttons to avoid double-trigger */
  em_dropzone.addEventListener('click', (e)=>{
    if (e.target.closest('button')) return;
    em_files.click();
  });

  ['dragenter','dragover'].forEach(ev=>{
    em_dropzone.addEventListener(ev, e=>{
      e.preventDefault(); e.stopPropagation();
      em_dropzone.classList.add('border-primary','bg-light');
    });
  });
  ['dragleave','dragend','drop'].forEach(ev=>{
    em_dropzone.addEventListener(ev, e=>{
      e.preventDefault(); e.stopPropagation();
      em_dropzone.classList.remove('border-primary','bg-light');
    });
  });
  em_dropzone.addEventListener('drop', e=>{
    const files = e.dataTransfer && e.dataTransfer.files;
    if(files) addEditorFiles(files);
  });
}

if(em_browse){
  /* (CHANGE #1) stop bubbling so dropzone click doesn't fire too */
  em_browse.addEventListener('click',(e)=>{
    e.preventDefault();
    e.stopPropagation();
    em_files.click();
  });
}

/* file picker */
em_files.addEventListener('change', ()=> addEditorFiles(em_files.files));

function resetEditor(){
  em_title_input.value = '';
  em_desc.value = '';

  em_course_id.value    = courseSel.value || '';
  em_course_label.value = courseSel.options[courseSel.selectedIndex]?.text || '';

  em_module_id.value    = '';
  em_module_label.value = '';

  em_batch_id.value     = batchSel.value || '';
  em_batch_label.value  = batchSel.options[batchSel.selectedIndex]?.text || '';

  emDT = new DataTransfer();
  em_files.value = '';
  em_files.files = emDT.files;
  emLibraryUrls = [];
  renderEmFiles();
}

function openCreateModal(){
  if(!courseSel.value || !batchSel.value)
    return Swal.fire('Select filters','Pick Course → Module → Batch first.','info');
  if(binMode)
    return Swal.fire('Bin view active','Switch off Bin to create.','info');
  const m=new bootstrap.Modal(document.getElementById('editModal'));
  em_mode.value='create'; em_id.value=''; em_title.textContent='Create Material';
  resetEditor();
  m.show();
}

async function openEdit(id){
  if(binMode) return Swal.fire('Bin view','Restore the item first to edit.','info');

  const m = new bootstrap.Modal(document.getElementById('editModal'));
  em_mode.value = 'edit';
  em_id.value   = id;
  em_title.textContent = 'Edit Material';
  resetEditor();

  try{
    const res = await fetch('/api/study-materials?' + new URLSearchParams({id}), {
      headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
    });
    const j   = await res.json();
    const row = (j?.data || [])[0];

    if(row){
      em_title_input.value = row.title || '';
      em_desc.value        = row.description || '';

      em_course_id.value    = row.course_id || courseSel.value || '';
      em_course_label.value = courseSel.options[courseSel.selectedIndex]?.text || '';

      em_module_id.value    = row.course_module_id || '';
      em_module_label.value = row.module_title || row.course_module_title || '';

      em_batch_id.value     = row.batch_id || batchSel.value || '';
      em_batch_label.value  = batchSel.options[batchSel.selectedIndex]?.text || '';
    }

    m.show();
  }catch(e){
    err('Failed to open editor');
  }
}

em_save.addEventListener('click', saveMaterial);

/* (CHANGE #2) helper to show spinner + lock button (prevents double submit / double upload) */
function setSaveLoading(on){
  if(!em_save) return;
  em_save.disabled = !!on;
  if (em_save_spin) em_save_spin.classList.toggle('d-none', !on);
  if (em_save_icon) em_save_icon.classList.toggle('d-none', !!on);
  if (em_save_txt)  em_save_txt.textContent = on ? 'Saving…' : 'Save';
}

async function saveMaterial(){
  /* (CHANGE #2) guard against double click */
  if (emIsSaving) return;

  if(!em_title_input.value.trim())
    return Swal.fire('Title required','Please enter a title.','info');

  const fd=new FormData();
  fd.append('course_id', em_course_id.value);
  fd.append('course_module_id', em_module_id.value);
  fd.append('batch_id', em_batch_id.value);
  fd.append('title', em_title_input.value.trim());
  if(em_desc.value.trim()) fd.append('description', em_desc.value.trim());

  if(em_files.files && em_files.files.length){
    [...em_files.files].forEach(f=> fd.append('attachments[]', f));
  }

  (emLibraryUrls||[]).forEach(u=>{
    fd.append('library_urls[]', u);
  });

  try{
    emIsSaving = true;
    setSaveLoading(true);

    let url=API.store, method='POST';
    if (em_mode.value==='edit' && em_id.value) {
      url = API.update(em_id.value);
      fd.append('_method','PATCH');
      method='POST';
    }

    const res=await fetch(url,{
      method,
      headers:{ Authorization:'Bearer '+TOKEN, Accept:'application/json' },
      body: fd
    });

    const j=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error((j?.message)|| (j?.errors ? Object.values(j.errors)[0] : 'Save failed'));

    ok('Material saved');
    const editEl = document.getElementById('editModal');
const inst = bootstrap.Modal.getInstance(editEl) || bootstrap.Modal.getOrCreateInstance(editEl);

// after modal fully hides, force-clean any leftover backdrop/body state
editEl.addEventListener('hidden.bs.modal', () => {
  document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
  document.body.classList.remove('modal-open');
  document.body.style.removeProperty('padding-right');
}, { once: true });

inst.hide();


    moduleMaterialsCache.clear();
    if(batchSel.value) renderModuleTable();

  }catch(e){
    err(e.message||'Save failed');
  }finally{
    emIsSaving = false;
    setSaveLoading(false);
  }
}

/* =================== DELETE / RESTORE =================== */
async function deleteItem(id){
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

/* =================== STUDY MATERIAL LIBRARY (MODAL) – GRID UI =================== */
const libraryModalEl      = document.getElementById('libraryModal');
const libraryFiles        = document.getElementById('library_files');
const librarySearch       = document.getElementById('library_search');
const librarySearchBtn    = document.getElementById('library_search_btn');
const librarySelectBtn    = document.getElementById('library_select');
const librarySelectedText = document.getElementById('library_selected_text');
const libraryModal        = new bootstrap.Modal(libraryModalEl);

let libItems    = [];
let libSelected = new Set();

function updateSelectedText(){
  const c = libSelected.size;
  if(!c){
    librarySelectedText.textContent = 'No items selected';
  }else{
    librarySelectedText.textContent = `${c} item${c>1?'s':''} selected`;
  }
  librarySelectBtn.disabled = c === 0;
}

function normalizeAttach(a){
  if(!a) return null;

  if(typeof a === 'string'){
    try{
      const parsed = JSON.parse(a);
      if(Array.isArray(parsed)){
        if(!parsed.length) return null;
        return normalizeAttach(parsed[0]);
      }
      if(parsed && typeof parsed === 'object') return normalizeAttach(parsed);
    }catch(e){
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

async function fetchLibrary(query){
  const qs = new URLSearchParams({per_page:'200', include_deleted:'0'});
  if(courseSel.value) qs.set('course_id', courseSel.value);
  if(batchSel.value)  qs.set('batch_id', batchSel.value);
  if(query) qs.set('search', query);

  const res=await fetch(API.index(qs),{
    headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}
  });
  const j=await res.json();
  if(!res.ok) throw new Error(j?.message||'Library load failed');

  const rows=j?.data||[];
  const docMap=new Map();

  rows.forEach(row=>{
    const title = row.title || '(untitled)';
    let rawAtts =
      row.attachments ||
      row.attachment ||
      row.files ||
      row.resources ||
      row.file ||
      [];

    if(typeof rawAtts === 'string'){
      try{
        const parsed = JSON.parse(rawAtts);
        rawAtts = Array.isArray(parsed) ? parsed : (parsed ? [parsed] : []);
      }catch(e){
        rawAtts = rawAtts ? [rawAtts] : [];
      }
    }
    if(!Array.isArray(rawAtts)) rawAtts = rawAtts ? [rawAtts] : [];

    rawAtts.forEach(a=>{
      const n = normalizeAttach(a);
      if(!n || !n.url) return;
      const base = n.url.split('?')[0];
      if(!base) return;

      const key  = base;
      const mime = n.mime || '';
      const size = n.size || 0;
      const ext  = n.ext || extOf(base) || '';

      if(!docMap.has(key)){
        docMap.set(key,{
          key,
          url: n.url,
          name: n.name || (base.split('/').pop() || 'file'),
          mime,
          ext,
          size,
          sourceTitle: title
        });
      }
    });
  });

  libItems = Array.from(docMap.values());
}

async function ensureLibraryLoaded(query=''){
  libraryFiles.innerHTML = `
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading library files...</p>
    </div>`;
  try{
    await fetchLibrary(query);
    renderLibraryGrid();
  }catch(e){
    libraryFiles.innerHTML = `<div class="col-12 text-danger text-center py-4">${H.esc(e.message||'Failed to load library')}</div>`;
    librarySelectBtn.disabled = true;
    librarySelectedText.textContent = 'No items selected';
  }
}

function renderLibraryGrid(){
  libraryFiles.innerHTML = '';

  if(!libItems.length){
    libraryFiles.innerHTML = `
      <div class="col-12 text-muted text-center py-4">
        No library items found for the selected filters.
      </div>`;
    updateSelectedText();
    return;
  }

  libItems.forEach(att=>{
    const col = document.createElement('div');
    col.className = 'col-md-4 mb-3';

    const alreadyFromEditor = emLibraryUrls.some(u => u && u.split('?')[0] === (att.url||'').split('?')[0]);
    const selected = libSelected.has(att.key) || alreadyFromEditor;

    col.innerHTML = `
      <div class="card h-100 border-0 shadow-sm position-relative library-card">
        <div class="lib-overlay-check">
          <input
            class="form-check-input lib-check"
            type="checkbox"
            data-key="${H.esc(att.key)}"
          >
        </div>

        <div class="card-body d-flex flex-column">
          <div class="mb-2 text-center" style="min-height:120px;display:flex;align-items:center;justify-content:center;">
            ${
              att.mime && att.mime.startsWith('image/')
                ? `<img src="${H.esc(att.url)}" alt="${H.esc(att.name)}" style="max-height:120px;max-width:100%;object-fit:contain;border-radius:6px;">`
                : `<i class="fa ${H.icon(att.ext)} fa-3x text-muted"></i>`
            }
          </div>
          <div class="fw-semibold text-truncate" title="${H.esc(att.name)}">${H.esc(att.name)}</div>
          <div class="small text-muted">${H.esc(att.mime || '')}</div>
          <div class="small text-muted mb-2">${att.size ? H.bytes(att.size) : ''}</div>

          <div class="mt-auto d-flex justify-content-start align-items-center pt-2">
            ${
              att.url
                ? `<a href="${H.esc(att.url)}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center px-2 py-1 small">
                     <i class="fa fa-arrow-up-right-from-square me-1"></i>
                     Preview
                   </a>`
                : ''
            }
          </div>
        </div>
      </div>
    `;

    const cb = col.querySelector('.lib-check');
    cb.checked = selected;
    if(selected) libSelected.add(att.key);

    cb.addEventListener('change',(e)=>{
      if(e.target.checked) libSelected.add(att.key);
      else libSelected.delete(att.key);
      updateSelectedText();
    });

    libraryFiles.appendChild(col);
  });

  updateSelectedText();
}

if (em_libraryBtn) {
  em_libraryBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    ensureLibraryLoaded('').then(() => {
      emLibraryUrls.forEach(u => {
        const base = (u || '').split('?')[0];
        libItems.forEach(it => {
          if (it.url && it.url.split('?')[0] === base) {
            libSelected.add(it.key);
          }
        });
      });
      renderLibraryGrid();
    });

    libraryModal.show();
  });
}

if(librarySearchBtn && librarySearch){
  librarySearchBtn.addEventListener('click', ()=>{
    const term = (librarySearch.value||'').trim();
    ensureLibraryLoaded(term);
  });
  librarySearch.addEventListener('keypress',(e)=>{
    if(e.key==='Enter'){
      e.preventDefault();
      const term = (librarySearch.value||'').trim();
      ensureLibraryLoaded(term);
    }
  });
}

if(librarySelectBtn){
  librarySelectBtn.addEventListener('click', ()=>{
    if(!libSelected.size) return;
    const chosen = [];
    libItems.forEach(it=>{
      if(libSelected.has(it.key) && it.url) chosen.push(it.url);
    });
    const merged = new Set([...(emLibraryUrls||[]), ...chosen]);
    emLibraryUrls = Array.from(merged);
    renderEmFiles();
    libraryModal.hide();
  });
}
</script>
@endpush
