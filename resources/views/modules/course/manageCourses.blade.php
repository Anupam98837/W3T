{{-- resources/views/modules/courses/viewCourses.blade.php --}}
@section('title','View Courses')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Shell ===== */
.crs-wrap{max-width:1140px;margin:16px auto 40px}
.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

/* Toolbar */
.mfa-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .btn{height:40px;border-radius:12px}
.mfa-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
.mfa-toolbar .btn-primary{background:var(--primary-color);border:none}

/* Table Card */
.table-wrap.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible}
.table-wrap .card-body{overflow:visible}
.table-responsive{overflow:visible !important}
.table{--bs-table-bg:transparent}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

/* Badges (stronger specificity so they don't go white) */
.table .badge.badge-success{background:var(--success-color) !important;color:#fff !important}
.table .badge.badge-warning{background:var(--warning-color) !important;color:#0b1324 !important}
.table .badge.badge-secondary{background:#64748b !important;color:#fff !important}

/* Pills / sorting */
.level-pill{display:inline-block;padding:.22rem .5rem;border-radius:999px;border:1px solid var(--line-strong);font-size:.8rem;background:var(--surface-2, var(--surface))}
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Archived row visual cue */
tr.is-archived{opacity:.92}
tr.is-archived td{background:color-mix(in oklab, var(--muted-color) 6%, transparent)}

/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:1085}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Empty & loader */
#empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg, #00000010, #00000005, #00000010);border-radius:8px}

/* Modals — match look across both modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control, .form-select{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control, html.theme-dark .form-select{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}
.modal-title i{opacity:.9}

/* ===== Featured Media modal — polished to mirror Create Module modal ===== */
.media-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px}
.media-head .meta .title{font-weight:700;color:var(--ink);font-family:var(--font-head);line-height:1.2}
.media-head .meta .sub{color:var(--muted-color);font-size:13px}

/* Section labels inside modal for consistency */
.modal .section-label{font-weight:600;color:var(--ink);margin-top:6px}

/* Dropzone */
.dropzone{
  border:1.5px dashed var(--line-strong);
  border-radius:14px;
  padding:18px;
  text-align:center;
  background:var(--surface-2, #fff);
  transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
}
.dropzone.drag{background:color-mix(in oklab, var(--accent-color) 10%, transparent); border-color:var(--accent-color); box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 18%, transparent)}
.dropzone .hint{color:var(--muted-color);font-size:13px}

/* Media list */
.media-list{margin-top:8px}
.media-item{
  display:grid;grid-template-columns:28px 1fr auto;align-items:center;gap:10px;
  border:1px solid var(--line-strong);border-radius:12px;background:var(--surface-2, #fff);
  padding:10px 12px;margin-bottom:8px
}
.media-item .handle{cursor:grab;opacity:.7}
.media-item.dragging{opacity:.5}
.media-item .url{font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:52vw}
.media-item .kind{font-size:12px;color:var(--muted-color)}
.media-item .btn-icon{border:none;background:transparent;padding:.25rem .4rem;color:#6b7280}
.media-item .btn-icon:hover{color:var(--ink)}
.media-item .icon{
  width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;
  border:1px solid var(--line-strong);background:#fff
}

/* Dark tweaks */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .dropzone{background:#0b1020;border-color:var(--line-strong)}
html.theme-dark .media-item{background:#0b1020;border-color:var(--line-strong)}

/* Dropdown visibility safety nets */
.table-wrap, .table-wrap .card-body, .table-responsive { overflow: visible !important; }
.table-wrap .dropdown { position: relative; }
.table-wrap .dropdown-menu { z-index: 2050; }

/* File button look */
.btn-light{background:var(--surface);border:1px solid var(--line-strong)}
</style>
@endpush


@section('content')
<div class="crs-wrap">

  {{-- ================= Toolbar (unchanged structure) ================= --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
      <div class="position-relative" style="min-width:300px;">
        <input id="q" type="text" class="form-control ps-5" placeholder="Search by title or slug…">
        <i class="fa fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Per page</label>
        <select id="per_page" class="form-select" style="width:96px;">
          <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Status</label>
        <select id="status" class="form-select" style="width:140px;">
          <option value="">All</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Type</label>
        <select id="course_type" class="form-select" style="width:140px;">
          <option value="">All</option>
          <option value="paid">Paid</option>
          <option value="free">Free</option>
        </select>
      </div>

      <button id="btnApply" class="btn btn-light ms-1"><i class="fa fa-check me-1"></i>Apply</button>
      <button id="btnReset" class="btn btn-light"><i class="fa fa-rotate-left me-1"></i>Reset</button>
    </div>

    <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
      <a id="btnCreate" href="/admin/courses/create" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>New Course
      </a>
    </div>
  </div>

  {{-- ================= Table ================= --}}
  <div class="card table-wrap">
    <div class="card-body p-0">

      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top" style="z-index:2;">
            <tr>
              <th class="sortable" data-col="title">COURSE <span class="caret"></span></th>
              <th class="sortable" data-col="course_type" style="width:120px;">TYPE <span class="caret"></span></th>
              <th style="width:200px;">PRICE / FINAL</th>
              <th class="sortable" data-col="status" style="width:130px;">STATUS <span class="caret"></span></th>
              <th style="width:120px;">LEVEL</th>
              <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
              <th class="text-end" style="width:108px;">ACTIONS</th>
            </tr>
          </thead>
          <tbody id="rows">
            <tr id="loaderRow" style="display:none;">
              <td colspan="7" class="p-0">
                <div class="p-4">
                  <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- Empty state --}}
      <div id="empty" class="p-4 text-center" style="display:none;">
        <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
        <div>No courses found for current filters.</div>
      </div>

      {{-- Footer: pagination --}}
      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="metaTxt">—</div>
        <nav><ul id="pager" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>
</div>

{{-- ================= Create Course Module (modal) ================= --}}
<div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-layer-group me-2"></i>Create Course Module</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 small text-muted" id="modCourseInfo">—</div>

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input id="mod_title" type="text" class="form-control" maxlength="255" placeholder="e.g., Introduction to Web Development">
          </div>
          <div class="col-md-4">
            <label class="form-label">Order No.</label>
            <input id="mod_order" type="number" min="0" class="form-control" placeholder="1">
          </div>
          <div class="col-12">
            <label class="form-label">Short Description</label>
            <input id="mod_short" type="text" class="form-control" placeholder="Brief summary">
          </div>
          <div class="col-12">
            <label class="form-label">Long Description</label>
            <textarea id="mod_long" class="form-control" rows="6" placeholder="Detailed module content…"></textarea>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Status</label>
            <select id="mod_status" class="form-select">
              <option value="draft">Draft</option>
              <option value="published" selected>Published</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="btnSaveModule" class="btn btn-primary">
          <i class="fa fa-save me-1"></i>Save Module
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= Featured Media (modal) — now styled like Create Module ================= --}}
<div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
  <!-- changed modal-xl to modal-lg for consistent sizing with module modal -->
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-images me-2"></i>Course Featured Media</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Course meta (mirrors module modal "info" line) -->
        <div class="media-head">
          <div class="meta">
            <div class="title" id="m_title">—</div>
            <div class="sub" id="m_sub">—</div>
          </div>
          <div class="small text-muted">
            Drag & drop to reorder • Click trash to delete
          </div>
        </div>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label section-label">Upload files</label>
            <div id="dropzone" class="dropzone">
              <div class="mb-2">
                <i class="fa-regular fa-circle-up" style="font-size:28px; opacity:.8"></i>
              </div>
              <div class="fw-semibold">Drag & drop your media here</div>
              <div class="hint mt-1">Images, videos, audio or PDFs. Or</div>
              <div class="mt-2">
                <label for="mediaFiles" class="btn btn-light me-2">
                  <i class="fa fa-file-arrow-up me-1"></i>Choose Files
                </label>
                <input id="mediaFiles" type="file" class="d-none" multiple accept="image/*,video/*,audio/*,application/pdf">
                <button id="btnAddUrl" class="btn btn-light" type="button"><i class="fa fa-link me-1"></i>Add via URL</button>
              </div>
            </div>
          </div>

          <div class="col-12" id="urlRow" style="display:none;">
            <label class="form-label section-label">Add via URL</label>
            <div class="row g-2 align-items-center">
              <div class="col">
                <input id="urlInput" type="url" class="form-control" placeholder="https://example.com/image.jpg">
              </div>
              <div class="col-auto">
                <button id="btnSaveUrl" class="btn btn-primary" type="button"><i class="fa fa-plus me-1"></i>Add</button>
              </div>
              <div class="col-12 small text-muted mt-1">Paste a direct link to an image/video/audio/PDF.</div>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label section-label">Current featured media</label>
            <div class="media-list" id="mediaList"></div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div class="me-auto small text-muted" id="mediaCount">—</div>
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="okToast" class="toast text-bg-success border-0"><div class="d-flex">
    <div id="okMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
  </div></div>
  <div id="errToast" class="toast text-bg-danger border-0 mt-2"><div class="d-flex">
    <div id="errMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
  </div></div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Delegated handler: works for current/future .dd-toggle buttons
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  const inst = bootstrap.Dropdown.getOrCreateInstance(btn, {
    autoClose: 'outside',
    boundary: 'viewport'
  });
  inst.toggle();
});

(function(){
  /* ========= Globals ========= */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const ROLE  = (localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase();
  const basePanel = (ROLE.includes('super') ? '/super_admin' : '/admin');

  if (!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }
  document.getElementById('btnCreate').setAttribute('href', basePanel + '/courses/create');

  const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
  const errToast = new bootstrap.Toast(document.getElementById('errToast'));
  const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  /* ========= Elements ========= */
  const rowsEl   = document.getElementById('rows');
  const loader   = document.getElementById('loaderRow');
  const emptyEl  = document.getElementById('empty');
  const pagerEl  = document.getElementById('pager');
  const metaTxt  = document.getElementById('metaTxt');

  const q           = document.getElementById('q');
  const status      = document.getElementById('status');
  const ctype       = document.getElementById('course_type');
  const perPageSel  = document.getElementById('per_page');
  const btnApply    = document.getElementById('btnApply');
  const btnReset    = document.getElementById('btnReset');

  /* ========= State ========= */
  let page = 1;
  let sort = '-created_at';
  let currentCourse = null;  // {id, uuid, title, short}
  let mediaModal, moduleModal;

  // Decode because we stored escaped text in data-* attributes
  function decodeHtml(s){
    const t = document.createElement('textarea');
    t.innerHTML = s == null ? '' : String(s);
    return t.value;
  }

  // Handle clicks on dropdown items for current/future rows
  rowsEl.addEventListener('click', (e) => {
    const item = e.target.closest('.dropdown-item[data-act]');
    if (!item) return;

    e.preventDefault();

    const act   = item.dataset.act;
    const uuid  = item.dataset.uuid || null;
    const id    = item.dataset.id ? Number(item.dataset.id) : null;
    const title = decodeHtml(item.dataset.title || '');
    const short = decodeHtml(item.dataset.short || '');

    if (act === 'media')        openMedia(uuid, title, short);
    else if (act === 'modules') openModules(id, uuid, title, short);
    else if (act === 'archive') archiveCourse(uuid, title);
    else if (act === 'unarchive') unarchiveCourse(uuid, title);
    else if (act === 'delete')  deleteCourse(uuid, title);
    else if (act === 'edit')    goEdit(uuid);

    // Hide the dropdown after selection
    const toggle = item.closest('.dropdown')?.querySelector('.dd-toggle');
    if (toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
  });

  function goEdit(uuid){
    location.href = `${basePanel}/courses/create?edit=${encodeURIComponent(uuid)}`;
  }

  /* ========= Helpers ========= */
  function showLoader(v){ loader.style.display = v ? '' : 'none'; }
  function escapeHtml(s){
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;','`':'&#96;'};
    return (s==null ? '' : String(s)).replace(/[&<>"'`]/g, ch => map[ch]);
  }

  function fmtMoney(v, cur){
    const n = Number(v || 0);
    const sym = (cur||'INR').toUpperCase()==='INR' ? '₹' : (cur||'').toUpperCase()+' ';
    return sym + n.toFixed(2);
  }
  function fmtDate(iso){
    if(!iso) return '-';
    const d=new Date(iso); if (isNaN(d)) return escapeHtml(iso);
    return d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
  }
  function badgeStatus(s){
    const map={draft:'warning',published:'success',archived:'info'};
    const cls=map[s]||'secondary';
    return `<span class="badge badge-${cls} text-uppercase">${escapeHtml(s)}</span>`;
  }

  function getToken(){ return TOKEN; }

  function queryParams(){
    const params = new URLSearchParams();
    if (q.value.trim()) params.set('q', q.value.trim());
    if (status.value)   params.set('status', status.value);
    if (ctype.value)    params.set('course_type', ctype.value);
    params.set('per_page', perPageSel.value || 20);
    params.set('page', page);
    params.set('sort', sort);
    return params.toString();
  }

  function pushURL(){ history.replaceState(null,'', location.pathname + '?' + queryParams()); }

  function applyFromURL(){
    const url=new URL(location.href);
    const g=(k)=>url.searchParams.get(k)||'';
    if (g('q')) q.value=g('q');
    if (g('status')) status.value=g('status');
    if (g('course_type')) ctype.value=g('course_type');
    if (g('per_page')) perPageSel.value=g('per_page');
    if (g('page')) page=Number(g('page'))||1;
    if (g('sort')) sort=g('sort');
    syncSortHeaders();
  }

  function syncSortHeaders(){
    document.querySelectorAll('th.sortable').forEach(th=>{
      th.classList.remove('asc','desc');
      const col = th.dataset.col;
      if (sort === col) th.classList.add('asc');
      if (sort === '-'+col) th.classList.add('desc');
    });
  }

  function rowActions(r){
    const isArchived = String(r.status||'').toLowerCase() === 'archived';
    const archiveToggle = isArchived
      ? `<button class="dropdown-item" data-act="unarchive" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" title="Move back to draft">
           <i class="fa fa-box-open"></i> Unarchive
         </button>`
      : `<button class="dropdown-item" data-act="archive" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" title="Archive this course">
           <i class="fa fa-box-archive"></i> Archive
         </button>`;

    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Actions">
          <i class="fa fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" data-act="edit" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}">
            <i class="fa fa-pen-to-square"></i> Edit
          </button></li>
          <li><button class="dropdown-item" data-act="modules" data-id="${r.id}" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" data-short="${escapeHtml(r.short_description||'')}">
            <i class="fa fa-layer-group"></i> Create Course Module
          </button></li>
          <li><button class="dropdown-item" data-act="media" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" data-short="${escapeHtml(r.short_description||'')}">
            <i class="fa fa-images"></i> Course Featured Media
          </button></li>
          <li><hr class="dropdown-divider"></li>
          <li>${archiveToggle}</li>
          <li><button class="dropdown-item text-danger" data-act="delete" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}">
            <i class="fa fa-trash"></i> Delete
          </button></li>
        </ul>
      </div>`;
  }

  function renderRows(items){
    rowsEl.querySelectorAll('tr:not(#loaderRow)').forEach(tr=>tr.remove());
    const frag=document.createDocumentFragment();

    items.forEach(r=>{
      const tr=document.createElement('tr');
      const isArchived = String(r.status||'').toLowerCase() === 'archived';
      if (isArchived) tr.classList.add('is-archived');

      const priceCell = (r.course_type==='paid')
          ? `${fmtMoney(r.price_amount,r.price_currency)} <span class="text-muted">→</span> <strong>${fmtMoney(r.final_price_ui ?? r.final_price ?? 0, r.price_currency)}</strong>`
          : '<span class="badge badge-success">FREE</span>';
      const level = r.level ? `<span class="level-pill">${escapeHtml(r.level)}</span>` : '-';

      tr.innerHTML = `
        <td>
          <div class="fw-semibold">
            <a href="${basePanel}/courses/${encodeURIComponent(r.uuid)}" class="link-offset-2 link-underline-opacity-0">${escapeHtml(r.title||'')}</a>
          </div>
          <div class="text-muted small">${escapeHtml(r.slug||'')}</div>
        </td>
        <td class="text-capitalize">${escapeHtml(r.course_type||'')}</td>
        <td>${priceCell}</td>
        <td>${badgeStatus(r.status)}</td>
        <td>${level}</td>
        <td>${fmtDate(r.created_at)}</td>
        <td class="text-end">${rowActions(r)}</td>
      `;
      frag.appendChild(tr);
    });

    rowsEl.appendChild(frag);
  }

  function renderPager(pagination){
    const total   = Number(pagination.total||0);
    const perPage = Number(pagination.per_page||20);
    const current = Number(pagination.page||1);
    const totalPages = Math.max(1, Math.ceil(total / perPage));

    function li(disabled, active, label, targetPage){
      const cls=['page-item',disabled?'disabled':'',active?'active':''].filter(Boolean).join(' ');
      const href=disabled?'#':'javascript:void(0)';
      return `<li class="${cls}"><a class="page-link" href="${href}" data-page="${targetPage||''}">${label}</a></li>`;
    }

    let html='';
    html += li(current<=1,false,'Previous',current-1);
    const w=3, start=Math.max(1,current-w), end=Math.min(totalPages,current+w);
    if (start>1){ html += li(false,false,1,1); if(start>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
    for(let p=start;p<=end;p++) html += li(false,p===current,p,p);
    if (end<totalPages){ if(end<totalPages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,totalPages,totalPages); }
    html += li(current>=totalPages,false,'Next',current+1);

    pagerEl.innerHTML=html;
    pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click',()=>{
        const target=Number(a.dataset.page); if(!target||target===page) return;
        page=Math.max(1,target); load();
      });
    });

    metaTxt.textContent = `Showing page ${current} of ${totalPages} — ${total} result(s)`;
  }

  /* ========= Fetch ========= */
  async function load(){
    showLoader(true);
    emptyEl.style.display='none';
    rowsEl.querySelectorAll('tr:not(#loaderRow)').forEach(tr=>tr.remove());
    pushURL();
    try{
      const res = await fetch('/api/courses?' + queryParams(), {
        headers:{ 'Authorization':'Bearer '+getToken(), 'Accept':'application/json' }
      });
      const json = await res.json();
      if(!res.ok) throw new Error(json?.message || 'Failed to load');
      const items = json?.data || [];
      const pagination = json?.pagination || {page:1,per_page:Number(perPageSel.value||20),total:items.length};
      if (items.length===0) emptyEl.style.display='';
      renderRows(items);
      renderPager(pagination);
    }catch(e){
      console.error(e);
      emptyEl.style.display='';
      metaTxt.textContent='Failed to load courses';
      err('Failed to load courses');
    }finally{
      showLoader(false);
      syncSortHeaders();
    }
  }

  /* ========= Course Actions ========= */
  async function archiveCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'question', title:'Archive course?',
      html:`You can unarchive later to Draft.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Archive', confirmButtonColor:'#8b5cf6'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}`, {
        method:'PATCH',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ status:'archived' })
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Archive failed'); }
      ok('Course archived');
      load();
    }catch(e){ err(e.message); }
  }

  async function unarchiveCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'question', title:'Unarchive to Draft?',
      html:`This will move the course back to Draft.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Unarchive', confirmButtonColor:'#10b981'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}`, {
        method:'PATCH',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ status:'draft' })
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Unarchive failed'); }
      ok('Moved to Draft');
      load();
    }catch(e){ err(e.message); }
  }

  async function deleteCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'warning', title:'Delete course?',
      html:`This will mark it deleted.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#ef4444'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}`, {
        method:'DELETE', headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Delete failed'); }
      ok('Course deleted'); load();
    }catch(e){ err(e.message); }
  }

  /* ========= Modules Modal ========= */
  function openModules(id, uuid, title, short){
    currentCourse = { id, uuid, title, short };
    document.getElementById('modCourseInfo').textContent = `${title || 'Course'} — ${short || ''}`.trim();
    document.getElementById('mod_title').value='';
    document.getElementById('mod_short').value='';
    document.getElementById('mod_long').value='';
    document.getElementById('mod_order').value='';
    document.getElementById('mod_status').value='published';

    moduleModal = moduleModal || new bootstrap.Modal(document.getElementById('moduleModal'));
    moduleModal.show();
  }

  document.getElementById('btnSaveModule').addEventListener('click', async ()=>{
    const t=(document.getElementById('mod_title').value||'').trim();
    if(!t){ return Swal.fire('Title required','Please enter a module title.','info'); }
    if(!currentCourse?.id){ return err('Missing course id'); }

    const payload={
      course_id: currentCourse.id,
      title: t,
      short_description: (document.getElementById('mod_short').value||'').trim() || null,
      long_description: (document.getElementById('mod_long').value||'').trim() || null,
      order_no: Number(document.getElementById('mod_order').value||0),
      status: document.getElementById('mod_status').value || 'published'
    };
    try{
      const res = await fetch('/api/course-modules', {
        method:'POST',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify(payload)
      });
      const j = await res.json().catch(()=>({}));
      if(!res.ok){ throw new Error(j?.message||'Save failed'); }
      ok('Module created'); moduleModal?.hide();
    }catch(e){
      err(e.message || 'Module API error');
    }
  });

  /* ========= Media Modal ========= */
  const mediaFiles = document.getElementById('mediaFiles');
  const urlRow     = document.getElementById('urlRow');
  const urlInput   = document.getElementById('urlInput');
  const btnAddUrl  = document.getElementById('btnAddUrl');
  const btnSaveUrl = document.getElementById('btnSaveUrl');
  const dropzone   = document.getElementById('dropzone');
  const mediaList  = document.getElementById('mediaList');
  const mTitle     = document.getElementById('m_title');
  const mSub       = document.getElementById('m_sub');
  const mediaCount = document.getElementById('mediaCount');

  function openMedia(uuid, title, short){
    currentCourse = { uuid, title, short };
    mTitle.textContent = title || 'Course';
    mSub.textContent   = (short && short.trim()) ? short.trim() : '—';
    urlRow.style.display='none'; urlInput.value='';
    mediaModal = mediaModal || new bootstrap.Modal(document.getElementById('mediaModal'));
    mediaModal.show();
    loadMedia();
  }

  function iconFor(kind){
    const map={image:'fa-image',video:'fa-film',audio:'fa-music',pdf:'fa-file-pdf',other:'fa-file'};
    const k=map[kind]||'fa-file';
    return `<div class="icon"><i class="fa ${k}" style="font-size:14px"></i></div>`;
  }

  async function loadMedia(){
    mediaList.innerHTML='<div class="text-center text-muted small py-4"><i class="fa fa-spinner fa-spin me-2"></i>Loading media...</div>';
    mediaCount.textContent='Loading…';
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`, {
        headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      const json = await res.json();
      if(!res.ok) throw new Error(json?.message||'Load failed');

      const items = json?.media || [];
      mediaCount.textContent = `${items.length} item(s)`;

      if(items.length===0){
        mediaList.innerHTML = `
          <div class="text-center text-muted small py-3">
            <i class="fa fa-image mb-2" style="font-size:22px;opacity:.6;"></i><br/>
            No featured media yet. Upload files or add a URL.
          </div>`;
        return;
      }

      const frag=document.createDocumentFragment();
      items.forEach(it=>{
        const div=document.createElement('div');
        div.className='media-item';
        div.setAttribute('draggable','true');
        div.dataset.id=it.id;
        div.innerHTML=`
          <div class="handle"><i class="fa fa-grip-lines"></i></div>
          <div class="info">
            <div class="d-flex align-items-center gap-2">
              ${iconFor(it.featured_type)}
              <div>
                <div class="url"><a href="${escapeHtml(it.featured_url)}" target="_blank" class="link-underline-opacity-0">${escapeHtml(it.featured_url)}</a></div>
                <div class="kind">Type: ${escapeHtml(it.featured_type||'other')} • Order: <span class="ord">${it.order_no||0}</span></div>
              </div>
            </div>
          </div>
          <div>
            <button class="btn-icon" title="Delete" data-del="${it.id}"><i class="fa fa-trash"></i></button>
          </div>
        `;
        frag.appendChild(div);
      });
      mediaList.innerHTML='';
      mediaList.appendChild(frag);

      // Delete bindings
      mediaList.querySelectorAll('[data-del]').forEach(btn=>{
        btn.addEventListener('click', ()=> deleteMedia(btn.getAttribute('data-del')));
      });

      // Drag & drop reorder
      initDragReorder();
    }catch(e){
      mediaList.innerHTML = '<div class="text-center text-danger small py-3">Failed to load media.</div>';
      mediaCount.textContent='Failed to load';
      err(e.message);
    }
  }

  async function deleteMedia(id){
    const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete media?',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'});
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media/${encodeURIComponent(id)}`, {
        method:'DELETE', headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Delete failed'); }
      ok('Media deleted'); loadMedia();
    }catch(e){ err(e.message); }
  }

  // Upload - files
  mediaFiles.addEventListener('change', async ()=>{
    if(!mediaFiles.files?.length) return;
    await uploadFiles(mediaFiles.files);
    mediaFiles.value='';
  });

  async function uploadFiles(fileList){
    const fd=new FormData();
    Array.from(fileList).forEach(f=> fd.append('files[]', f));
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`, {
        method:'POST', headers:{'Authorization':'Bearer '+getToken()}, body: fd
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Upload failed');
      ok(`Uploaded ${ (j?.inserted||[]).length } file(s)`); loadMedia();
    }catch(e){ err(e.message); }
  }

  // Upload - URL
  btnAddUrl.addEventListener('click', ()=>{ urlRow.style.display = (urlRow.style.display==='none' ? '' : 'none'); });
  btnSaveUrl.addEventListener('click', async ()=>{
    const url=(urlInput.value||'').trim(); if(!/^https?:\/\//i.test(url)) return Swal.fire('Invalid URL','Provide a valid http(s) URL.','info');
    try{
      const res=await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`,{
        method:'POST',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ url })
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Add failed');
      ok('Media added'); urlInput.value=''; urlRow.style.display='none'; loadMedia();
    }catch(e){ err(e.message); }
  });

  // Dropzone drag handling
  ;['dragenter','dragover'].forEach(ev=> dropzone.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag'); }));
  ;['dragleave','drop'].forEach(ev=> dropzone.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag'); }));
  dropzone.addEventListener('drop', e=>{
    const files = e.dataTransfer?.files || []; if(files.length) uploadFiles(files);
  });

  function initDragReorder(){
    let dragSrc=null;
    mediaList.querySelectorAll('.media-item').forEach(it=>{
      it.addEventListener('dragstart', e=>{ dragSrc=it; it.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; });
      it.addEventListener('dragend', ()=>{ dragSrc=null; it.classList.remove('dragging'); });
      it.addEventListener('dragover', e=>{ e.preventDefault(); e.dataTransfer.dropEffect='move'; });
      it.addEventListener('drop', e=>{
        e.preventDefault();
        if(!dragSrc || dragSrc===it) return;
        const items=[...mediaList.querySelectorAll('.media-item')];
        const srcIdx=items.indexOf(dragSrc), dstIdx=items.indexOf(it);
        if(srcIdx<dstIdx) it.after(dragSrc); else it.before(dragSrc);
        persistReorder();
      });
    });
  }

  async function persistReorder(){
    const ids=[...mediaList.querySelectorAll('.media-item')].map(n=> Number(n.dataset.id));
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media/reorder`, {
        method:'POST',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ ids })
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Reorder failed');
      ok('Order updated'); loadMedia();
    }catch(e){ err(e.message); }
  }

  /* ========= Sorting / Filters ========= */
  document.querySelectorAll('th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      if (sort === col){ sort = '-'+col; }
      else if (sort === '-'+col){ sort = col; }
      else { sort = (col === 'created_at') ? '-created_at' : col; }
      page=1; syncSortHeaders(); load();
    });
  });

  let srchT;
  q.addEventListener('input', ()=>{ clearTimeout(srchT); srchT=setTimeout(()=>{ page=1; load(); }, 350); });
  btnApply.addEventListener('click', ()=>{ page=1; load(); });
  btnReset.addEventListener('click', ()=>{
    q.value=''; status.value=''; ctype.value=''; perPageSel.value='20'; page=1; sort='-created_at'; load();
  });

  /* ========= Init ========= */
  applyFromURL();
  load();
})();
</script>
@endpush
