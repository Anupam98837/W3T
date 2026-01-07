{{-- resources/views/modules/blog/manageBlogs.blade.php --}}
@section('title','Manage Blogs')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
.blog-wrap,
.table-wrap,
.card,
.card-body,
.table-responsive{
  position: relative !important;
  overflow-x: auto !important; /* keep horizontal hidden (optional) */
  overflow-y: auto !important;   /* ✅ vertical scroll */
  transform: none !important;
}


/* Table rows and cells */
.table tbody tr {
  position: relative;
  overflow: visible !important;
}

.table tbody tr td {
  overflow: visible !important;
}

.table tbody tr td:last-child {
  position: relative;
  z-index: 1;
  overflow: visible !important;
}

.dropdown-item i {
  width: 18px;
  text-align: center;
  opacity: 0.8;
}

.dropdown-item:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--ink, #1f2937);
}

.dropdown-item.text-danger {
  color: #ef4444 !important;
}

.dropdown-item.text-danger:hover {
  background: rgba(239, 68, 68, 0.08);
  color: #dc2626 !important;
}

/* Divider */
.dropdown-divider {
  margin: 0.375rem 0;
  border-top: 1px solid var(--line-strong, #e5e7eb);
}

/* Links in dropdown */
.dropdown-item[href] {
  text-decoration: none;
}

.dropdown-item[href]:hover {
  text-decoration: none;
}

/* Dark mode support */
html.theme-dark .dropdown-menu,
html.theme-dark .dropdown-menu.dd-portal {
  background: #0f172a;
  border-color: #334155;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 
              0 0 0 1px rgba(255, 255, 255, 0.1);
}

html.theme-dark .dropdown-item {
  color: #e5e7eb;
}

html.theme-dark .dropdown-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #f3f4f6;
}

html.theme-dark .dropdown-item.text-danger {
  color: #f87171 !important;
}

html.theme-dark .dropdown-item.text-danger:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5 !important;
}

html.theme-dark .dropdown-divider {
  border-color: #334155;
}



/* Make sure card footer doesn't clip */
.card-body {
  padding-bottom: 0 !important;
}

.card-body > .d-flex:last-child {
  padding: 1rem;
  overflow: visible !important;
}

/* Special handling for last rows in table */
.table tbody tr:nth-last-child(-n+3) .dropdown-menu {
  margin-bottom: 0 !important;
}

/* Ensure nav-tabs don't interfere */
.nav-tabs {
  position: relative;
  z-index: 1;
}

/* Tab content must allow overflow */
.tab-content {
  position: relative;
  overflow: visible !important;
}

.tab-pane {
  position: relative;
  overflow: visible !important;
}
/* ===== Pills / badges ===== */
.badge-soft{background:color-mix(in oklab, var(--muted-color) 12%, transparent);color:var(--ink)}
.badge-status{font-weight:700;letter-spacing:.02em}
.badge-status.draft{background:color-mix(in oklab, var(--warning-color) 22%, transparent); color:color-mix(in oklab, var(--warning-color) 55%, black)}
.badge-status.pending_approval{background:color-mix(in oklab, #0ea5e9 20%, transparent); color:color-mix(in oklab, #0ea5e9 55%, black)}
.badge-status.approved{background:color-mix(in oklab, var(--success-color) 18%, transparent); color:color-mix(in oklab, var(--success-color) 55%, black)}
.badge-status.active{background:color-mix(in oklab, var(--success-color) 22%, transparent); color:color-mix(in oklab, var(--success-color) 55%, black)}
.badge-status.inactive{background:color-mix(in oklab, var(--danger-color) 16%, transparent); color:color-mix(in oklab, var(--danger-color) 55%, black)}

.badge-pub.yes{background:color-mix(in oklab, var(--success-color) 18%, transparent); color:color-mix(in oklab, var(--success-color) 60%, black)}
.badge-pub.no{background:color-mix(in oklab, var(--muted-color) 14%, transparent); color:var(--ink)}

/* ===== Sorting ===== */
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* ===== Row cues ===== */
tr.is-inactive td{background:color-mix(in oklab, var(--danger-color) 6%, transparent)}
tr.is-draft td{background:color-mix(in oklab, var(--warning-color) 5%, transparent)}
tr.is-deleted td{background:color-mix(in oklab, var(--danger-color) 8%, transparent)}

.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color)!important}

/* ===== Table bits ===== */
.thumb-mini{
  width:34px;height:34px;border-radius:10px;object-fit:cover;
  border:1px solid var(--line-strong);background:#fff
}
.title-row{display:flex;align-items:center;gap:10px}
.small-muted{color:var(--muted-color);font-size:12.5px}

/* ===== Empty & loader ===== */
.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg, #00000010, #00000005, #00000010);border-radius:8px}

/* ===== Dark tweaks ===== */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}
</style>
@endpush

@section('content')
<div class="blog-wrap">

  {{-- ================= Tabs ================= --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-all" role="tab" aria-selected="true">
        <i class="fa-solid fa-newspaper me-2"></i>All
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-draft" role="tab" aria-selected="false">
        <i class="fa-solid fa-file-lines me-2"></i>Draft
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-pending" role="tab" aria-selected="false">
        <i class="fa-solid fa-hourglass-half me-2"></i>Pending
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-approved" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-check me-2"></i>Approved
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="false">
        <i class="fa-solid fa-bolt me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-ban me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-bin" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash me-2"></i>Bin
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ========== TAB: All ========== --}}
    <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;">
              <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:300px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search title / slug / shortcode…">
            <i class="fa fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
          </div>

          <button id="btnFilter" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fa fa-filter me-1"></i>Filter
          </button>

          <button id="btnReset" class="btn btn-primary"><i class="fa fa-rotate-left me-1"></i>Reset</button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <a id="btnCreate" href="/blog/create" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i>New Blog
          </a>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top" style="z-index:2;">
                <tr>
                  <th class="sortable" data-col="title">BLOG <span class="caret"></span></th>
                  <th class="sortable" data-col="blog_date" style="width:160px;">BLOG DATE <span class="caret"></span></th>
                  <th class="sortable" data-col="is_published" style="width:150px;">PUBLISHED <span class="caret"></span></th>
                  <th class="sortable" data-col="status" style="width:170px;">STATUS <span class="caret"></span></th>
                  <th class="sortable" data-col="updated_at" style="width:190px;">UPDATED <span class="caret"></span></th>
                  <th class="text-end" style="width:108px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-all">
                <tr id="loaderRow-all" style="display:none;">
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
              </tbody>
            </table>
          </div>

          <div id="empty-all" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No blogs found for current filters.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-all">—</div>
            <nav><ul id="pager-all" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== Other tabs (reuse same table shell) ===== --}}
    @php
      $tabs = [
        ['key'=>'draft','icon'=>'fa-file-lines','title'=>'Draft'],
        ['key'=>'pending','icon'=>'fa-hourglass-half','title'=>'Pending Approval'],
        ['key'=>'approved','icon'=>'fa-circle-check','title'=>'Approved'],
        ['key'=>'active','icon'=>'fa-bolt','title'=>'Active'],
        ['key'=>'inactive','icon'=>'fa-ban','title'=>'Inactive'],
        ['key'=>'bin','icon'=>'fa-trash','title'=>'Bin'],
      ];
    @endphp

    @foreach($tabs as $t)
      <div class="tab-pane fade" id="tab-{{ $t['key'] }}" role="tabpanel">
        <div class="card table-wrap">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-borderless align-middle mb-0">
                <thead class="sticky-top">
                  <tr>
                    <th>BLOG</th>
                    <th style="width:160px;">BLOG DATE</th>
                    <th style="width:150px;">PUBLISHED</th>
                    <th style="width:170px;">STATUS</th>
                    <th style="width:190px;">UPDATED</th>
                    <th class="text-end" style="width:160px;">ACTIONS</th>
                  </tr>
                </thead>
                <tbody id="rows-{{ $t['key'] }}">
                  <tr id="loaderRow-{{ $t['key'] }}" style="display:none;">
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
                </tbody>
              </table>
            </div>

            <div id="empty-{{ $t['key'] }}" class="empty p-4 text-center" style="display:none;">
              <i class="fa {{ $t['icon'] }} mb-2" style="font-size:32px; opacity:.6;"></i>
              <div>No items here.</div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
              <div class="text-muted small" id="metaTxt-{{ $t['key'] }}">—</div>
              <nav style="position:relative;"><ul id="pager-{{ $t['key'] }}" class="pagination mb-0"></ul></nav>
            </div>
          </div>
        </div>
      </div>
    @endforeach

  </div>
</div>

{{-- ================= Filter Modal ================= --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filter Blogs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">All</option>
              <option value="draft">Draft</option>
              <option value="pending_approval">Pending Approval</option>
              <option value="approved">Approved</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Published</label>
            <select id="modal_published" class="form-select">
              <option value="">All</option>
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-updated_at">Recently Updated</option>
              <option value="updated_at">Oldest Updated</option>
              <option value="-created_at">Newest Created</option>
              <option value="created_at">Oldest Created</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="-blog_date">Blog Date (newest)</option>
              <option value="blog_date">Blog Date (oldest)</option>
            </select>
          </div>
        </div>

        <div class="small text-muted mt-3">
          Note: Bin tab shows soft-deleted items (from <code>/api/blogs/deleted</code>).
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="btnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
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
/* ===== Force dropdown overflows to body (portal) — stable implementation ===== */
(function(){
  let activePortal = null;

  const placeMenu = (menu, btnRect) => {
    const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    const vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

    menu.classList.add('dd-portal');
    menu.style.display = 'block';
    menu.style.visibility = 'hidden';
    document.body.appendChild(menu);

    const mw = menu.offsetWidth;
    const mh = menu.offsetHeight;

    // Horizontal positioning
    let left = btnRect.left;
    const spaceRight = vw - btnRect.right;
    if (spaceRight < mw && btnRect.right - mw > 8) left = btnRect.right - mw;

    // Vertical positioning
    let top = btnRect.bottom + 4;
    const spaceBelow = vh - btnRect.bottom;
    const spaceAbove = btnRect.top;

    if (spaceBelow < mh + 20 && spaceAbove > mh + 20) {
      top = btnRect.top - mh - 4;
    } else if (spaceBelow < mh + 20) {
      top = Math.max(8, Math.min(top, vh - mh - 8));
    }

    menu.style.left = left + 'px';
    menu.style.top  = top + 'px';
    menu.style.visibility = 'visible';
  };

  document.addEventListener('show.bs.dropdown', function(ev){
    const dd = ev.target;
    const btn = dd.querySelector('.dd-toggle, [data-bs-toggle="dropdown"]');
    const menu = dd.querySelector('.dropdown-menu');
    if (!btn || !menu) return;

    if (activePortal?.menu?.isConnected) {
      activePortal.menu.classList.remove('dd-portal');
      activePortal.parent.appendChild(activePortal.menu);
      activePortal = null;
    }

    const rect = btn.getBoundingClientRect();
    menu.__ddParent = menu.parentElement;
    placeMenu(menu, rect);
    activePortal = { menu, parent: menu.__ddParent };

    const closeOnEnv = () => {
      try { bootstrap.Dropdown.getOrCreateInstance(btn).hide(); } catch {}
    };

    menu.__ddCloseOnEnv = closeOnEnv;
    window.addEventListener('resize', closeOnEnv);
    document.addEventListener('scroll', closeOnEnv, true);
  });

  document.addEventListener('hidden.bs.dropdown', function(ev){
    const dd = ev.target;
    const menu = dd.querySelector('.dropdown-menu.dd-portal') || activePortal?.menu;
    if (!menu) return;

    if (menu.__ddCloseOnEnv) {
      window.removeEventListener('resize', menu.__ddCloseOnEnv);
      document.removeEventListener('scroll', menu.__ddCloseOnEnv, true);
      menu.__ddCloseOnEnv = null;
    }

    if (menu.__ddParent) {
      menu.classList.remove('dd-portal');
      menu.style.cssText = '';
      menu.__ddParent.appendChild(menu);
      activePortal = null;
    }
  });
})();

/* ================= Dropdown toggle handler ================= */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;
  e.preventDefault(); e.stopPropagation();
  const inst = bootstrap.Dropdown.getOrCreateInstance(btn, { autoClose:'outside', boundary:'viewport' });
  inst.toggle();
});

(async function(){
  /* ========= Globals ========= */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
  const errToast = new bootstrap.Toast(document.getElementById('errToast'));
  const ok  = (m)=>{ const el=document.getElementById('okMsg'); if(el) el.textContent = m||'Done'; okToast.show(); };
  const err = (m)=>{ const el=document.getElementById('errMsg'); if(el) el.textContent = m||'Something went wrong'; errToast.show(); };

  /* ========= Fetch helper (needed early for my-privileges) ========= */
  async function fetchJson(url, opts={}){
    const res = await fetch(url, {
      ...opts,
      headers: {
        'Authorization': 'Bearer ' + TOKEN,
        'Accept': 'application/json',
        ...(opts.headers || {})
      }
    });
    const j = await res.json().catch(()=>({}));
    if (!res.ok) {
      const msg = j?.message || j?.error || ('HTTP ' + res.status);
      const e = new Error(msg);
      e.status = res.status;
      e.payload = j;
      throw e;
    }
    return j;
  }
// DEFAULT DENY
const PERMS = {
  loaded:false,
  canView:false,
  canViewBin:false,
  canCreate:false,
  canPreview:false,
  canEdit:false,
  canPublish:false,
  canDelete:false,
  canRestore:false,
  canForce:false,
  canAnyStatus:false,
  statusAllowed:{ draft:false, pending_approval:false, approved:false, active:false, inactive:false },
  privSet:new Set(),
};

function slugify(str){
  return String(str||'').trim().toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
}

function flattenTree(nodes, out=[]){
  (nodes||[]).forEach(n=>{
    out.push(n);
    if (Array.isArray(n?.children)) flattenTree(n.children, out);
  });
  return out;
}

function buildPrivSetFromNode(node){
  const set = new Set();
  (node?.privileges || []).forEach(p=>{
    if (!p) return;
    if (p.key) set.add(String(p.key).toLowerCase());     // e.g. all-blog.view
    if (p.action) set.add(slugify(p.action));            // e.g. view
    if (p.name) set.add(slugify(p.name));
  });
  return set;
}

function hasAny(set, ...keys){ return keys.some(k => set.has(k)); }

// ✅ IMPORTANT: view should allow page load
function computePermsStrict(set){
  const canCreate  = hasAny(set,'create','add','new','store');
  const canEdit    = hasAny(set,'edit','update','modify');
  const canDelete  = hasAny(set,'delete','remove','destroy');
  const canRestore = hasAny(set,'restore');
  const canForce   = hasAny(set,'force','force-delete','permanent-delete','delete-permanently');
  const canPublish = hasAny(set,'publish','unpublish','toggle-publish','togglepublish');
  const canPreview = true;

  const masterStatus = hasAny(set,'status','set-status','change-status');

  const statusAllowed = {
    draft:            masterStatus || hasAny(set,'draft','mark-draft','set-draft'),
    pending_approval: masterStatus || hasAny(set,'pending','pending-approval','pending_approval','mark-pending'),
    approved:         masterStatus || hasAny(set,'approved','approve','mark-approved'),
    active:           masterStatus || hasAny(set,'active','activate','mark-active'),
    inactive:         masterStatus || hasAny(set,'inactive','deactivate','mark-inactive'),
  };

  const canAnyStatus = Object.values(statusAllowed).some(Boolean);
  const canViewBin   = hasAny(set,'bin','trash','deleted') || canRestore || canForce;

  // ✅ STRICT VIEW: allow if action "view" OR any "*.view" key exists
  const canView =
    hasAny(set,'view','read','list') ||
    Array.from(set).some(x => String(x).endsWith('.view') || String(x).endsWith('-view'));

  return { canView, canViewBin, canCreate, canEdit, canDelete, canRestore, canForce, canPublish, canPreview, statusAllowed, canAnyStatus };
}

function applyPermsToUI(){
  // New Blog
  const btnCreate = document.getElementById('btnCreate');
  if (btnCreate && !PERMS.canCreate) btnCreate.style.display = 'none';

  // Bin tab
  const binTabLi = document.querySelector('a[href="#tab-bin"]')?.closest('li');
  const binPane  = document.getElementById('tab-bin');
  if (!PERMS.canViewBin){
    if (binTabLi) binTabLi.style.display = 'none';
    if (binPane)  binPane.style.display  = 'none';
  }

  // ✅ Hide Actions column if user has no action permissions
  const canAnyAction = PERMS.canEdit || PERMS.canPublish || PERMS.canDelete || PERMS.canRestore || PERMS.canForce || PERMS.canAnyStatus || PERMS.canPreview;
  if (!canAnyAction){
    document.querySelectorAll('th:last-child, td:last-child').forEach(el=>{
      // only hide in these tables (safe)
      if (el.closest('.table')) el.style.display = 'none';
    });
  }
}

async function initMyPrivileges(){
  const deny = (msg='You do not have permission to view Blogs.') => {
    PERMS.loaded = true;
    applyPermsToUI();
    Swal.fire('Access denied', msg, 'error');
    return false;
  };

  try{
    // ✅ Always pass menu_href so backend can set `current` (even if it returns null, we fallback)
    const res = await fetchJson('/api/my-privileges?menu_href=' + encodeURIComponent(location.pathname));

    if (!res?.success) return deny(res?.message || 'Permission check failed.');

    const tree = Array.isArray(res?.data) ? res.data : [];
    if (!tree.length) return deny();

    let set = new Set();

    // 1) Prefer current if present
    if (res?.current && Array.isArray(res.current.privileges) && res.current.privileges.length){
      set = buildPrivSetFromNode(res.current);
    } else {
      // 2) Fallback: match node by href from tree
      const flat = flattenTree(tree, []);
      const path = (location.pathname || '/').replace(/\/+$/,'') || '/';

      const node = flat.find(n=>{
        const href = String(n?.href || '').replace(/\/+$/,'');
        return href && (href === path || path.startsWith(href + '/'));
      });

      if (!node) return deny();

      set = buildPrivSetFromNode(node);
    }

    PERMS.privSet = set;

    const computed = computePermsStrict(set);
    PERMS.loaded = true;
    Object.assign(PERMS, computed);

    applyPermsToUI();

    if (!PERMS.canView) return deny();
    return true;

  }catch(e){
    return deny(e?.message || 'Permission check failed.');
  }
}

// call it (same as you already do)
const okToLoad = await initMyPrivileges();
if (!okToLoad) return;


  /* ========= DOM refs ========= */
  const q           = document.getElementById('q');
  const perPageSel  = document.getElementById('per_page');
  const btnReset    = document.getElementById('btnReset');

  const tabs = {
    all:      { rows:'#rows-all',      loader:'#loaderRow-all',      empty:'#empty-all',      meta:'#metaTxt-all',      pager:'#pager-all'      },
    draft:    { rows:'#rows-draft',    loader:'#loaderRow-draft',    empty:'#empty-draft',    meta:'#metaTxt-draft',    pager:'#pager-draft'    },
    pending:  { rows:'#rows-pending',  loader:'#loaderRow-pending',  empty:'#empty-pending',  meta:'#metaTxt-pending',  pager:'#pager-pending'  },
    approved: { rows:'#rows-approved', loader:'#loaderRow-approved', empty:'#empty-approved', meta:'#metaTxt-approved', pager:'#pager-approved' },
    active:   { rows:'#rows-active',   loader:'#loaderRow-active',   empty:'#empty-active',   meta:'#metaTxt-active',   pager:'#pager-active'   },
    inactive: { rows:'#rows-inactive', loader:'#loaderRow-inactive', empty:'#empty-inactive', meta:'#metaTxt-inactive', pager:'#pager-inactive' },
    bin:      { rows:'#rows-bin',      loader:'#loaderRow-bin',      empty:'#empty-bin',      meta:'#metaTxt-bin',      pager:'#pager-bin'      },
  };

  /* ========= State ========= */
  let sort = '-updated_at';
  const state = {
    all:{page:1}, draft:{page:1}, pending:{page:1}, approved:{page:1}, active:{page:1}, inactive:{page:1}, bin:{page:1}
  };

  /* ========= Utils ========= */
  function escapeHtml(s){
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;','`':'&#96;'};
    return (s==null ? '' : String(s)).replace(/[&<>"'`]/g, ch => map[ch]);
  }
  function decodeHtml(s){
    const t = document.createElement('textarea');
    t.innerHTML = s == null ? '' : String(s);
    return t.value;
  }
  function fmtDate(iso){
    if(!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d)) return escapeHtml(iso);
    return d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
  }
  function statusPill(s){
    const st = (s||'').toLowerCase();
    return `<span class="badge badge-soft badge-status ${escapeHtml(st)} text-uppercase">${escapeHtml(st || '-')}</span>`;
  }
  function pubPill(v){
    const yes = String(v) === '1' || String(v).toLowerCase()==='yes' || v === true;
    return yes
      ? `<span class="badge badge-soft badge-pub yes text-uppercase">YES</span>`
      : `<span class="badge badge-soft badge-pub no text-uppercase">NO</span>`;
  }

  function showLoader(scope, v){
    const loader = document.querySelector(tabs[scope].loader);
    if (loader) loader.style.display = v ? '' : 'none';
  }

  function apiSortAndDir(){
    let dir = 'desc';
    let col = sort || '-updated_at';
    if (col.startsWith('-')) { dir='desc'; col = col.slice(1); }
    else { dir='asc'; }
    return { sort: col, direction: dir };
  }

  function queryParams(scope){
    const params = new URLSearchParams();
    const p  = state[scope].page || 1;
    const pp = Number(perPageSel?.value || 20);
    const {sort:apiSort, direction} = apiSortAndDir();

    params.set('per_page', pp);
    params.set('page', p);
    params.set('sort', apiSort);
    params.set('direction', direction);

    if (scope === 'all'){
      if (q && q.value.trim()) params.set('q', q.value.trim());

      const st = document.getElementById('modal_status')?.value || '';
      if (st) params.set('status', st);

      const pub = document.getElementById('modal_published')?.value || '';
      if (pub) params.set('is_published', pub);
    }

    if (scope === 'draft') params.set('status','draft');
    if (scope === 'pending') params.set('status','pending_approval');
    if (scope === 'approved') params.set('status','approved');
    if (scope === 'active') params.set('status','active');
    if (scope === 'inactive') params.set('status','inactive');

    if (scope !== 'bin' && scope !== 'all'){
      if (q && q.value.trim()) params.set('q', q.value.trim());
      const pub = document.getElementById('modal_published')?.value || '';
      if (pub) params.set('is_published', pub);
    }

    return params.toString();
  }

  function pushURL(){
    const url = location.pathname + '?' + queryParams('all');
    history.replaceState(null,'', url);
  }

  function applyFromURL(){
    const url = new URL(location.href);
    const g = (k)=>url.searchParams.get(k)||'';
    if (g('q') && q) q.value = g('q');
    if (g('per_page') && perPageSel) perPageSel.value = g('per_page');
    if (g('page')) state.all.page = Number(g('page'))||1;

    const s = g('sort');
    const d = (g('direction')||'').toLowerCase();
    if (s){
      sort = (d === 'asc') ? s : ('-' + s);
      const ms = document.getElementById('modal_sort'); if (ms) ms.value = sort;
    }

    const st = g('status'); if (st){ const el=document.getElementById('modal_status'); if(el) el.value=st; }
    const pub = g('is_published'); if (pub){ const el=document.getElementById('modal_published'); if(el) el.value=pub; }

    syncSortHeaders();
  }

  function syncSortHeaders(){
    document.querySelectorAll('#tab-all th.sortable').forEach(th=>{
      th.classList.remove('asc','desc');
      const col = th.dataset.col;
      if (sort === col) th.classList.add('asc');
      if (sort === '-'+col) th.classList.add('desc');
    });
  }

  /**
   * Preview URL:
   * - Always /blog/view/{slug}
   * - If NOT published OR status not in (approved,active) => ?mode=test
   */
  function previewUrlFor(r){
    const slug = (r.slug || '').trim();
    const base = `/blog/view/${encodeURIComponent(slug)}`;

    const isPublished = String(r.is_published) === '1' || String(r.is_published).toLowerCase() === 'yes' || r.is_published === true;
    const st = String(r.status || '').toLowerCase();
    const okStatus = (st === 'approved' || st === 'active');

    if (!isPublished || !okStatus) return base + '?mode=test';
    return base;
  }

  function blogActions(scope, r){
    if (!PERMS.loaded) return '';

    const canAnyAction =
      PERMS.canEdit || PERMS.canPublish || PERMS.canDelete ||
      PERMS.canRestore || PERMS.canForce || PERMS.canAnyStatus || PERMS.canPreview;

    if (PERMS.loaded && !canAnyAction) return '';

    const uuid  = r.uuid;
    const title = escapeHtml(r.title || '');
    const slug  = (r.slug || '').trim();

    if (scope === 'bin'){
      const items = [];

      if (PERMS.canRestore){
        items.push(`
          <li><button class="dropdown-item" data-act="restore" data-uuid="${uuid}" data-title="${title}">
            <i class="fa fa-rotate-left"></i> Restore
          </button></li>
        `);
      }

      if (PERMS.canForce){
        items.push(`
          <li><button class="dropdown-item text-danger" data-act="force" data-uuid="${uuid}" data-title="${title}">
            <i class="fa fa-skull-crossbones"></i> Delete Permanently
          </button></li>
        `);
      }

      if (PERMS.loaded && items.length === 0) return '';

      return `
        <div class="dropdown text-end" data-bs-display="static" data-bs-boundary="viewport">
          <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            ${items.join('')}
          </ul>
        </div>`;
    }

    const items = [];

    if (slug){
      const previewHref = previewUrlFor(r);
      items.push(`
        <li>
          <a class="dropdown-item" href="${previewHref}" target="_blank" rel="noopener" title="Preview">
            <i class="fa-solid fa-up-right-from-square"></i> Preview
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
      `);
    }

    if (PERMS.canEdit){
      items.push(`
        <li>
          <a class="dropdown-item" href="/blog/create?uuid=${encodeURIComponent(uuid)}" title="Edit">
            <i class="fa fa-pen-to-square"></i> Edit
          </a>
        </li>
      `);
    }

    if (PERMS.canPublish){
      const isPub = String(r.is_published)==='1';
      const pubLabel = isPub ? 'Unpublish' : 'Publish';
      const pubIcon  = isPub ? 'fa-eye-slash' : 'fa-eye';
      const pubNext  = isPub ? 'no' : 'yes';

      items.push(`
        <li><button class="dropdown-item" data-act="togglePublish" data-uuid="${uuid}" data-title="${title}" data-publish="${pubNext}">
          <i class="fa ${pubIcon}"></i> ${pubLabel}
        </button></li>
      `);
    }

    const stItems = [];
    if (PERMS.statusAllowed?.draft) stItems.push(`
      <li><button class="dropdown-item" data-act="setStatus" data-uuid="${uuid}" data-title="${title}" data-status="draft">
        <i class="fa fa-file-lines"></i> Mark Draft
      </button></li>
    `);
    if (PERMS.statusAllowed?.pending_approval) stItems.push(`
      <li><button class="dropdown-item" data-act="setStatus" data-uuid="${uuid}" data-title="${title}" data-status="pending_approval">
        <i class="fa fa-hourglass-half"></i> Mark Pending
      </button></li>
    `);
    if (PERMS.statusAllowed?.approved) stItems.push(`
      <li><button class="dropdown-item" data-act="setStatus" data-uuid="${uuid}" data-title="${title}" data-status="approved">
        <i class="fa fa-circle-check"></i> Mark Approved
      </button></li>
    `);
    if (PERMS.statusAllowed?.active) stItems.push(`
      <li><button class="dropdown-item" data-act="setStatus" data-uuid="${uuid}" data-title="${title}" data-status="active">
        <i class="fa fa-bolt"></i> Mark Active
      </button></li>
    `);
    if (PERMS.statusAllowed?.inactive) stItems.push(`
      <li><button class="dropdown-item" data-act="setStatus" data-uuid="${uuid}" data-title="${title}" data-status="inactive">
        <i class="fa fa-ban"></i> Mark Inactive
      </button></li>
    `);

    if (stItems.length){
      items.push(`<li><hr class="dropdown-divider"></li>`);
      items.push(stItems.join(''));
    }

    if (PERMS.canDelete){
      items.push(`
        <li><hr class="dropdown-divider"></li>
        <li><button class="dropdown-item text-danger" data-act="delete" data-uuid="${uuid}" data-title="${title}">
          <i class="fa fa-trash"></i> Delete (Move to Bin)
        </button></li>
      `);
    }

    const meaningful = items.join('').trim();
    if (PERMS.loaded && !meaningful) return '';

    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Actions">
          <i class="fa fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          ${meaningful}
        </ul>
      </div>`;
  }

  function renderRows(scope, items){
    const rowsEl = document.querySelector(tabs[scope].rows);
    rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(tr=>tr.remove());
    const frag = document.createDocumentFragment();

    items.forEach(r=>{
      const tr = document.createElement('tr');

      const st = String(r.status||'').toLowerCase();
      const isDeleted = !!r.deleted_at;

      if (st === 'inactive') tr.classList.add('is-inactive');
      if (st === 'draft') tr.classList.add('is-draft');
      if (isDeleted) tr.classList.add('is-deleted');

      const img = r.featured_image_url
        ? `<img class="thumb-mini" src="${escapeHtml(r.featured_image_url)}" alt="thumb">`
        : `<div class="thumb-mini d-flex align-items-center justify-content-center text-muted"><i class="fa fa-image"></i></div>`;

      const title = escapeHtml(r.title||'-');
      const slug  = escapeHtml(r.slug||'');
      const sc    = escapeHtml(r.shortcode||'');
      const sd    = escapeHtml(r.short_description||'');

      tr.innerHTML = `
        <td>
          <div class="title-row">
            ${img}
            <div style="min-width:0">
              <div class="fw-semibold" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:62vw">
                ${title}
              </div>
              <div class="small-muted">
                ${slug ? `<span class="me-2"><i class="fa fa-link me-1"></i>${slug}</span>` : ''}
                ${sc ? `<span><i class="fa fa-hashtag me-1"></i>${sc}</span>` : ''}
              </div>
              ${sd ? `<div class="small text-muted" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:68vw">${sd}</div>` : ``}
            </div>
          </div>
        </td>

        <td>${r.blog_date ? escapeHtml(String(r.blog_date).slice(0,10)) : '-'}</td>
        <td>${pubPill(r.is_published)}</td>
        <td>${statusPill(r.status)}</td>
        <td>${fmtDate(r.updated_at || r.created_at)}</td>
        <td class="text-end">${blogActions(scope, r)}</td>
      `;
      frag.appendChild(tr);
    });

    rowsEl.appendChild(frag);
  }

  function renderPager(scope, pagination){
    const pagerEl = document.querySelector(tabs[scope].pager);
    const metaTxt = document.querySelector(tabs[scope].meta);

    const total = Number(pagination.total||0);
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

    if (start>1){
      html += li(false,false,1,1);
      if(start>2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
    for(let p=start;p<=end;p++) html += li(false,p===current,p,p);
    if (end<totalPages){
      if(end<totalPages-1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
      html += li(false,false,totalPages,totalPages);
    }
    html += li(current>=totalPages,false,'Next',current+1);

    pagerEl.innerHTML = html;
    pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click',()=>{
        const target = Number(a.dataset.page);
        if(!target || target === state[scope].page) return;
        state[scope].page = Math.max(1,target);
        load(scope);
      });
    });

    metaTxt.textContent = `Showing page ${current} of ${totalPages} — ${total} result(s)`;
  }

  async function load(scope){
    if (scope === 'bin' && PERMS.loaded && !PERMS.canViewBin){
      err('Not allowed');
      return;
    }

    showLoader(scope, true);
    const emptyEl = document.querySelector(tabs[scope].empty);
    const rowsEl  = document.querySelector(tabs[scope].rows);
    if (emptyEl) emptyEl.style.display = 'none';
    if (rowsEl) rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(tr=>tr.remove());

    try{
      let url;
      if (scope === 'bin'){
        url = '/api/blogs/deleted?' + new URLSearchParams({
          page: state[scope].page || 1,
          per_page: Number(perPageSel?.value || 20),
          q: (q?.value || '').trim()
        }).toString();
      } else {
        url = '/api/blogs?' + queryParams(scope);
      }

      const json = await fetchJson(url);
      const items = json?.data || [];
      const pagination = json?.pagination || { page:1, per_page:Number(perPageSel?.value||20), total: items.length };

      if (items.length === 0 && emptyEl) emptyEl.style.display = '';
      renderRows(scope, items);
      renderPager(scope, pagination);

      if (scope === 'all'){ pushURL(); syncSortHeaders(); }
    }catch(e){
      console.error(e);
      if (emptyEl) emptyEl.style.display = '';
      const metaTxt = document.querySelector(tabs[scope].meta);
      if (metaTxt) metaTxt.textContent = 'Failed to load';
      err(e.message || 'Failed to load');
    }finally{
      showLoader(scope, false);
    }
  }

  /* ========= Actions ========= */
  async function updateBlog(uuid, patch, successMsg){
    try{
      await fetchJson(`/api/blogs/${encodeURIComponent(uuid)}`, {
        method: 'PATCH',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(patch)
      });
      ok(successMsg || 'Updated');
      const activeTab = document.querySelector('.nav-link.active[data-bs-toggle="tab"]')?.getAttribute('href') || '#tab-all';
      const scope = activeTab.replace('#tab-','') || 'all';
      load(scope); if(scope!=='all') load('all');
    }catch(e){
      err(e.message || 'Update failed');
    }
  }

  async function deleteBlog(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'warning', title:'Delete blog?',
      html:`This will move it to Bin.<br><b>${escapeHtml(title||'This blog')}</b>`,
      showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#ef4444'
    });
    if(!isConfirmed) return;

    try{
      await fetchJson(`/api/blogs/${encodeURIComponent(uuid)}`, { method:'DELETE' });
      ok('Moved to Bin');
      load('all');
      if (PERMS.canViewBin) load('bin');
      load('draft'); load('pending'); load('approved'); load('active'); load('inactive');
    }catch(e){ err(e.message || 'Delete failed'); }
  }

  async function restoreBlog(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'question', title:'Restore blog?',
      html:`Restore from Bin.<br><b>${escapeHtml(title||'This blog')}</b>`,
      showCancelButton:true, confirmButtonText:'Restore', confirmButtonColor:'#0ea5e9'
    });
    if(!isConfirmed) return;

    try{
      await fetchJson(`/api/blogs/${encodeURIComponent(uuid)}/restore`, { method:'POST' });
      ok('Restored');
      if (PERMS.canViewBin) load('bin');
      load('all');
    }catch(e){ err(e.message || 'Restore failed'); }
  }

  async function forceDeleteBlog(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'warning', title:'Delete permanently?',
      html:`This cannot be undone.<br><b>${escapeHtml(title||'This blog')}</b>`,
      showCancelButton:true, confirmButtonText:'Delete permanently', confirmButtonColor:'#dc2626'
    });
    if(!isConfirmed) return;

    try{
      await fetchJson(`/api/blogs/${encodeURIComponent(uuid)}/force`, { method:'DELETE' });
      ok('Deleted permanently');
      if (PERMS.canViewBin) load('bin');
    }catch(e){ err(e.message || 'Permanent delete failed'); }
  }

  /* ========= Event wiring ========= */
  document.querySelectorAll('#tab-all th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      if (sort === col) sort = '-'+col;
      else if (sort === '-'+col) sort = col;
      else sort = (col === 'updated_at') ? '-updated_at' : col;

      state.all.page = 1;
      syncSortHeaders();
      load('all');
    });
  });

  document.getElementById('btnApplyFilters')?.addEventListener('click', ()=>{
    const ms = document.getElementById('modal_sort');
    if (ms && ms.value) sort = ms.value;

    state.all.page = 1;
    const modal = document.getElementById('filterModal');
    modal?.querySelector('[data-bs-dismiss="modal"]')?.click();
    setTimeout(()=> load('all'), 150);
  });

  let srchT;
  q?.addEventListener('input', ()=>{
    clearTimeout(srchT);
    srchT = setTimeout(()=>{
      Object.keys(state).forEach(k=> state[k].page = 1);
      const activeTab = document.querySelector('.nav-link.active[data-bs-toggle="tab"]')?.getAttribute('href') || '#tab-all';
      const scope = activeTab.replace('#tab-','') || 'all';
      load(scope);
      if (scope !== 'all') load('all');
    }, 350);
  });

  btnReset?.addEventListener('click', ()=>{
    if (q) q.value = '';
    if (perPageSel) perPageSel.value = '20';
    const st = document.getElementById('modal_status'); if (st) st.value = '';
    const pub = document.getElementById('modal_published'); if (pub) pub.value = '';
    const ms = document.getElementById('modal_sort'); if (ms) ms.value = '-updated_at';
    sort = '-updated_at';
    Object.keys(state).forEach(k=> state[k].page = 1);
    syncSortHeaders();
    load('all');
  });

  perPageSel?.addEventListener('change', ()=>{
    Object.keys(state).forEach(k=> state[k].page = 1);
    const activeTab = document.querySelector('.nav-link.active[data-bs-toggle="tab"]')?.getAttribute('href') || '#tab-all';
    const scope = activeTab.replace('#tab-','') || 'all';
    load(scope);
    if (scope !== 'all') load('all');
  });

  document.addEventListener('click', (e)=>{
    const item = e.target.closest('.dropdown-item[data-act]');
    if (!item) return;

    e.preventDefault();
    const act = item.dataset.act;
    const uuid = item.dataset.uuid || '';
    const title = decodeHtml(item.dataset.title || '');

    // ✅ permission guards
    if (PERMS.loaded){
      if (act === 'delete' && !PERMS.canDelete) return err('Not allowed');
      if (act === 'restore' && !PERMS.canRestore) return err('Not allowed');
      if (act === 'force' && !PERMS.canForce) return err('Not allowed');
      if (act === 'togglePublish' && !PERMS.canPublish) return err('Not allowed');

      if (act === 'setStatus'){
        const next = item.dataset.status || '';
        if (!PERMS.statusAllowed?.[next]) return err('Not allowed');
      }
    }

    if (act === 'delete') return deleteBlog(uuid, title);
    if (act === 'restore') return restoreBlog(uuid, title);
    if (act === 'force') return forceDeleteBlog(uuid, title);

    if (act === 'setStatus') {
      const next = item.dataset.status || 'draft';
      return updateBlog(uuid, { status: next }, 'Status updated');
    }

    if (act === 'togglePublish') {
      const next = item.dataset.publish || 'no';
      return updateBlog(uuid, { is_published: next }, 'Publish flag updated');
    }

    const toggle = item.closest('.dropdown')?.querySelector('.dd-toggle');
    if (toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
  });

  document.querySelector('a[href="#tab-all"]')?.addEventListener('shown.bs.tab', ()=> load('all'));
  document.querySelector('a[href="#tab-draft"]')?.addEventListener('shown.bs.tab', ()=> load('draft'));
  document.querySelector('a[href="#tab-pending"]')?.addEventListener('shown.bs.tab', ()=> load('pending'));
  document.querySelector('a[href="#tab-approved"]')?.addEventListener('shown.bs.tab', ()=> load('approved'));
  document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', ()=> load('active'));
  document.querySelector('a[href="#tab-inactive"]')?.addEventListener('shown.bs.tab', ()=> load('inactive'));
  document.querySelector('a[href="#tab-bin"]')?.addEventListener('shown.bs.tab', ()=>{
    if (PERMS.loaded && !PERMS.canViewBin){
      err('Not allowed');
      document.querySelector('a[href="#tab-all"]')?.click();
      return;
    }
    load('bin');
  });

  /* ========= Initial Load ========= */
  applyFromURL();
  load('all');

})(); // end async IIFE
</script>
@endpush
