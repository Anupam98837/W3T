{{-- resources/views/modules/manageModule.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title','Manage Modules')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
/* ===== Shell ===== */
.cm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
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
.table{--bs-table-bg:transparent}
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

/* Row state cues */
tr.state-archived td{background:color-mix(in oklab, var(--muted-color) 6%, transparent)}
tr.state-deleted td{background:color-mix(in oklab, var(--danger-color) 6%, transparent)}

/* Status badges */
.badge-soft{background:color-mix(in oklab, var(--muted-color) 12%, transparent);color:var(--ink)}
.table .badge.badge-success{background:var(--success-color)!important;color:#fff!important}
.table .badge.badge-secondary{background:#64748b!important;color:#fff!important}

/* Dropdowns inside table (with portal) */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:5000}
.dropdown-menu.dd-portal{position:fixed!important;left:0;top:0;transform:none!important;z-index:5000;border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;background:var(--surface)}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color)!important}

/* Buttons */
.icon-btn{display:inline-flex;align-items:center;justify-content:center;height:34px;min-width:34px;padding:0 10px;border:1px solid var(--line-strong);background:var(--surface);border-radius:10px}
.icon-btn:hover{box-shadow:var(--shadow-1)}

/* Empty & loader */
.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

/* Modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control,.form-select,textarea{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control,html.theme-dark .form-select,html.theme-dark textarea{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}

/* Reorder affordances */
.dragging{opacity:.6}
.drag-handle{cursor:grab;color:#9ca3af}
.drag-handle:active{cursor:grabbing}

/* Dark tweaks */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}

/* Ensure common wrappers don't clip dropdowns */
.table-responsive,
.table-wrap,
.card,
.panel,
.cm-wrap {
  overflow: visible !important;
  transform: none !important;   /* transforms create new stacking contexts and break fixed/absolute positioning */
}

/* Make the regular dropdown escape parents when shown */
.dropdown-menu.dropdown-menu-end.show {
  position: fixed !important;       /* fixed ensures it positions relative to viewport */
  transform: none !important;
  left: 0 !important;               /* will be overwritten by JS */
  top: 0 !important;                /* will be overwritten by JS */
  z-index: 9000 !important;         /* keep above table/footer */
  min-width: 220px;
  overflow: visible !important;
  display: block !important;
}

/* Visual style for portal menus */
.dropdown-menu.dd-portal {
  position: fixed !important;
  transform: none !important;
  z-index: 9000 !important;
  min-width: 220px;
  border-radius: 12px;
  border: 1px solid var(--line-strong);
  box-shadow: var(--shadow-2);
  background: var(--surface);
}

/* keep dropdown toggle above table row so it remains clickable */
.table-wrap .dd-toggle { z-index: 7; position: relative; }

/* small: ensure dropdown caret/contents not clipped visually */
.dropdown-menu { overflow: visible; }

/* Privilege modal helpers */
.priv-rows .row { gap: .5rem; align-items: center; margin-bottom: .5rem; }
.priv-rows .col-action { flex: 1 1 45%; }
.priv-rows .col-desc   { flex: 1 1 45%; }
.priv-rows .col-remove { flex: 0 0 48px; text-align: right; }
</style>
@endpush
@section('content')
<div class="cm-wrap">

  {{-- ===== Global toolbar (no course selector) ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-xxl d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted medium mb-0">My Modules</label>
      </div>

    </div>

  </div>

  {{-- ===== Tabs ===== --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true">
      <i class="fa-solid fa-layer-group me-2" aria-hidden="true"></i>
      Modules
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-archived" role="tab" aria-selected="false">
      <i class="fa-solid fa-box-archive me-2" aria-hidden="true"></i>
      Archived
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-bin" role="tab" aria-selected="false">
      <i class="fa-solid fa-trash-can me-2" aria-hidden="true"></i>
      Bin
    </a>
  </li>
</ul>

  <div class="tab-content mb-3">

    {{-- ===== ACTIVE ===== --}}
    <div class="tab-pane fade show active" id="tab-active" role="tabpanel">

      {{-- Toolbar (Active only) --}}
      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;">
              <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
            </select>
          </div>
          <div class="position-relative" style="min-width:280px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search name/description…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>
          <button id="btnFilterOpen" class="btn btn-light" title="Filters">
        <i class="fa fa-filter me-1"></i>Filters
      </button>

          <button id="btnReset" class="btn btn-primary"><i class="fa fa-rotate-left me-1"></i>Reset</button>
        </div>
        <div class="col-12 col-xxl-auto ms-xxl-auto d-flex justify-content-xxl-end gap-2">
            <button id="btnReorder" class="btn btn-primary">
        <i class="fa fa-up-down-left-right me-1"></i>Reorder
      </button>

      {{-- New: Privileges button --}}
      <!-- <button id="btnPrivilege" class="btn btn-light">
        <i class="fa fa-shield-alt me-1"></i>+ Privilege
      </button> -->

      <button id="btnCreate" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>New Module
      </button>
    </div>

        <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end small text-muted">
          Sorting: <span id="sortHint" class="ms-1">Newest first</span>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:40px;"></th>
                  <th class="sortable" data-col="name">NAME <span class="caret"></span></th>
                  <th style="width:22%;">DESCRIPTION</th>
                  <th class="sortable" data-col="status" style="width:130px;">STATUS <span class="caret"></span></th>
                  <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-active">
                <tr id="loaderRow-active" style="display:none;">
                  <td colspan="6" class="p-0">
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

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No modules found.</div>
          </div>

          <div id="reorderHint" class="p-3 small text-muted" style="display:none;">
            Reorder mode is ON — drag rows using <i class="fa fa-grip-lines-vertical"></i> and click <b>Save Order</b>.
            This applies to the currently visible page. Set a larger “Per page” to reorder more items at once.
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-active">—</div>
            <div class="d-flex align-items-center gap-2">
              <button id="btnSaveOrder" class="btn btn-primary btn-sm" style="display:none;"><i class="fa fa-floppy-disk me-1"></i>Save Order</button>
              <button id="btnCancelOrder" class="btn btn-light btn-sm" style="display:none;">Cancel</button>
              <nav style="position:relative; z-index:1;"><ul id="pager-active" class="pagination mb-0"></ul></nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== ARCHIVED ===== --}}
    <div class="tab-pane fade" id="tab-archived" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>NAME</th>
                  <th style="width:22%;">DESCRIPTION</th>
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-archived">
                <tr id="loaderRow-archived" style="display:none;">
                  <td colspan="4" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-archived" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-box-archive mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No archived modules.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-archived">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-archived" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== BIN ===== --}}
    <div class="tab-pane fade" id="tab-bin" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>NAME</th>
                  <th style="width:22%;">DESCRIPTION</th>
                  <th style="width:140px;">DELETED AT</th>
                  <th class="text-end" style="width:160px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-bin">
                <tr id="loaderRow-bin" style="display:none;">
                  <td colspan="4" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-bin" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No items in Bin.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-bin">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-bin" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.tab-content -->
</div>

{{-- ===== Create / Edit Module Modal ===== --}}
<div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="mm_title" class="modal-title"><i class="fa fa-book me-2"></i>Create Module</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="mm_mode" value="create">
        <input type="hidden" id="mm_key" value="">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input id="mm_name" class="form-control" maxlength="150" placeholder="e.g., Module 1 — Getting Started">
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea id="mm_description" class="form-control" rows="4" placeholder="Short description (optional)"></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select id="mm_status" class="form-select">
              <option value="Active" selected>Active</option>
              <option value="Draft">Draft</option>
              <option value="Published">Published</option>
              <option value="Archived">Archived</option>
            </select>
          </div>

          {{-- NEW: href input --}}
          <div class="col-md-8">
            <label class="form-label">Href (route or URL)<span class="text-danger">*</span></label>
            <input id="mm_href" class="form-control" maxlength="255" placeholder="e.g. /modules/intro or https://example.com/path">
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="mm_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
      </div>
    </div>
  </div>
</div>
{{-- ===== Filter Modal ===== --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">Any</option>
              <option value="Active">Active</option>
              <option value="Draft" style="display:none" >Draft</option>
              <option value="Published" style="display:none" >Published</option>
              <option value="Archived">Archived</option>
            </select>
          </div>

          <div class="col-md-6" style="display:none">
            <label class="form-label">Per page</label>
            <select id="modal_per_page" class="form-select">
              <option value="10">10</option>
              <option value="20" selected>20</option>
              <option value="30">30</option>
              <option value="50">50</option>
            </select>
          </div>

          <div class="col-md-12">
            <label class="form-label">Sort</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at" selected>Newest first</option>
              <option value="created_at">Oldest first</option>
              <option value="name">Name (A → Z)</option>
              <option value="-name">Name (Z → A)</option>
            </select>
          </div>

          <div class="col-12" style="display:none">
            <label class="form-label">Search</label>
            <input id="modal_q" type="text" class="form-control" placeholder="Search name/description… (optional)">
            <div class="small text-muted mt-1">Note: main search box will still be used if you type there.</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button id="btnClearFilters" type="button" class="btn btn-light">Clear</button>
        <button id="btnApplyFilters" type="button" class="btn btn-primary">Apply</button>
      </div>
    </div>
  </div>
</div>

{{-- ===== Privileges Modal ===== --}}
<div class="modal fade" id="privilegeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-shield-alt me-2"></i>Create Privileges</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 mb-2">
          <div class="col-md-8">
            <label class="form-label">Module</label>
            <select id="priv_module_select" class="form-select">
              <option value="">Loading modules…</option>
            </select>
            <div class="small text-muted mt-1">Select the module to attach privileges to.</div>
          </div>

          <div class="col-md-4 d-flex align-items-end">
            <button id="btnAddPrivRow" type="button" class="btn btn-primary ms-auto"><i class="fa fa-plus me-1"></i>Add Privilege Row</button>
          </div>
        </div>

        <div class="priv-rows" id="priv_rows_container">
          <!-- dynamic rows will be appended here -->
        </div>

        <!-- <div class="mt-2 small text-muted">
          Example image for layout (uploaded):<br>
          <img src="/mnt/data/c8eb5f6b-0796-4e3c-af92-80d5a1632456.png" alt="example" style="max-width:320px;border-radius:8px;border:1px solid #eee;margin-top:.5rem;">
        </div> -->
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="priv_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Create Privileges</button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
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
/* =================== Dropdown portal to <body> (unchanged) =================== */
(function(){
  let activePortal=null;
  const place=(menu, btnRect)=>{
    const vw=Math.max(document.documentElement.clientWidth, window.innerWidth||0);
    menu.classList.add('dd-portal'); menu.style.display='block'; menu.style.visibility='hidden';
    document.body.appendChild(menu);
    const mw=menu.offsetWidth, mh=menu.offsetHeight;
    let left = (vw - btnRect.right < mw && btnRect.right - mw > 8) ? (btnRect.right - mw) : btnRect.left;
    let top  = btnRect.bottom + 4;
    const vh=Math.max(document.documentElement.clientHeight, window.innerHeight||0);
    if(top + mh > vh - 8) top = Math.max(8, vh - mh - 8);
    menu.style.left = left + 'px'; menu.style.top = top + 'px'; menu.style.visibility='visible';
  };
  document.addEventListener('show.bs.dropdown', (ev)=>{
    const dd=ev.target, btn=dd.querySelector('.dd-toggle,[data-bs-toggle="dropdown"]'), menu=dd.querySelector('.dropdown-menu');
    if(!btn || !menu) return;
    if(activePortal?.menu?.isConnected){ activePortal.menu.classList.remove('dd-portal'); activePortal.parent.appendChild(activePortal.menu); activePortal=null; }
    const rect=btn.getBoundingClientRect(); menu.__parent=menu.parentElement; place(menu, rect); activePortal={menu, parent:menu.__parent};
    const close=()=>{ try{ bootstrap.Dropdown.getOrCreateInstance(btn).hide(); }catch{} };
    menu.__ls=[ ['resize',close,false], ['scroll',close,true] ];
    window.addEventListener('resize', close); document.addEventListener('scroll', close, true);
  });
  document.addEventListener('hidden.bs.dropdown', (ev)=>{
    const dd=ev.target; const menu=dd.querySelector('.dropdown-menu.dd-portal') || activePortal?.menu;
    if(!menu) return;
    if(menu.__ls){ document.removeEventListener('scroll', menu.__ls[1][1], true); window.removeEventListener('resize', menu.__ls[0][1]); menu.__ls=null; }
    if(menu.__parent){ menu.classList.remove('dd-portal'); menu.style.cssText=''; menu.__parent.appendChild(menu); activePortal=null; }
  });
})();

/* =================== Dropdown toggle click =================== */
document.addEventListener('click',(e)=>{
  const btn=e.target.closest('.dd-toggle'); if(!btn) return;
  e.preventDefault(); e.stopPropagation();
  bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'}).toggle();
});

/* =================== App logic =================== */
(function(){
  /* Auth */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/');
    return;
  }

  /* Toasts */
  const okToast=new bootstrap.Toast(document.getElementById('okToast'));
  const errToast=new bootstrap.Toast(document.getElementById('errToast'));
  const ok=(m)=>{document.getElementById('okMsg').textContent=m||'Done'; okToast.show();};
  const err=(m)=>{document.getElementById('errMsg').textContent=m||'Something went wrong'; errToast.show();};

  /* Elements */
  const btnCreate = document.getElementById('btnCreate');
  const btnPrivilege = document.getElementById('btnPrivilege');
  const btnReorder= document.getElementById('btnReorder');
  const sortHint  = document.getElementById('sortHint');
  const btnFilterOpen = document.getElementById('btnFilterOpen');

  /* Active toolbar */
  const q           = document.getElementById('q');
  const perPageSel  = document.getElementById('per_page');
  const btnReset    = document.getElementById('btnReset');

  /* Modal filter elements (optional — may not exist) */
  const filterModalEl = document.getElementById('filterModal');
  const modalStatus   = document.getElementById('modal_status');
  const modalPerPage  = document.getElementById('modal_per_page');
  const modalSort     = document.getElementById('modal_sort');
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const btnClearFilters = document.getElementById('btnClearFilters');

  /* Rows & pagers */
  const tabs = {
    active   :{rows:'#rows-active',   loader:'#loaderRow-active',   empty:'#empty-active',   pager:'#pager-active',   ask:null,   meta:'#metaTxt-active'},
    archived :{rows:'#rows-archived', loader:'#loaderRow-archived', empty:'#empty-archived', pager:'#pager-archived', ask:null, meta:'#metaTxt-archived'},
    bin      :{rows:'#rows-bin',      loader:'#loaderRow-bin',      empty:'#empty-bin',      pager:'#pager-bin',      ask:null,      meta:'#metaTxt-bin'},
  };

  /* Modal and mm refs */
  const mm = {
    modal  : new bootstrap.Modal(document.getElementById('moduleModal')),
    mode   : document.getElementById('mm_mode'),
    key    : document.getElementById('mm_key'),
    title  : document.getElementById('mm_title'),
    name   : document.getElementById('mm_name'),
    description: document.getElementById('mm_description'),
    status : document.getElementById('mm_status'),
    href   : document.getElementById('mm_href'),
    save   : document.getElementById('mm_save'),
  };

  /* Privileges modal refs */
  const priv = {
    modalEl: document.getElementById('privilegeModal'),
    modal: new bootstrap.Modal(document.getElementById('privilegeModal')),
    moduleSelect: document.getElementById('priv_module_select'),
    rowsContainer: document.getElementById('priv_rows_container'),
    addRowBtn: document.getElementById('btnAddPrivRow'),
    saveBtn: document.getElementById('priv_save')
  };

  /* State */
  const state = { active:{page:1}, archived:{page:1}, bin:{page:1} };
  let sort = '-created_at';
  let reorderMode = false;
  let draggingRow = null;

  /* Utils */
  const esc=(s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>m[ch]); };
  const fmtDate=(iso)=>{ if(!iso) return '-'; const d=new Date(iso); if(isNaN(d)) return esc(iso); return d.toLocaleString(undefined,{year:'numeric',month:'short','day':'2-digit','hour':'2-digit','minute':'2-digit'}); };
  const badgeStatus=(s)=>{ s=String(s||'').toLowerCase(); const map={active:'success',draft:'warning',published:'success',archived:'secondary'}; const cls=map[s]||'secondary'; return `<span class="badge badge-${cls} text-uppercase">${esc(s)}</span>`; };
  const qs=(sel)=> sel ? document.querySelector(sel) : null;
  const show=(el,v)=>{ if(!el) return; el.style.display = v ? '' : 'none'; };
  const enable=(el,v)=>{ if(!el) return; el.disabled = !v; };

  const showLoader=(which, v)=>{ show(qs(tabs[which].loader), v); };
  const clearBody=(which)=>{
    const rowsEl=qs(tabs[which].rows);
    if(!rowsEl) return;
    rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(n=>n.remove());
  };

  function setToolbarEnabled(on){
    [q, perPageSel, btnReset, btnCreate, btnReorder, btnFilterOpen, btnPrivilege].forEach(el=> enable(el, on));
  }

  function syncSortHeaders(){
    document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{
      th.classList.remove('asc','desc');
      const col = th.dataset.col;
      if(sort===col) th.classList.add('asc');
      if(sort==='-'+col) th.classList.add('desc');
    });
    sortHint.textContent = (sort==="-created_at") ? "Newest first" : (sort==="created_at" ? "Oldest first" : ("Sorted by "+sort.replace('-','')+(sort.startsWith('-')?' (desc)':'')));
  }

  /* ========== Build URLs ========== */
  function baseParams(scope){
    const usp=new URLSearchParams();

    const modalPer = (modalPerPage && modalPerPage.value) ? Number(modalPerPage.value) : null;
    const per = modalPer || Number(perPageSel?.value || 20);
    const pg = Number(state[scope].page||1);
    usp.set('per_page', per);
    usp.set('page', pg);

    if(scope==='active'){
      const sVal = (modalSort && modalSort.value) ? modalSort.value : sort;
      usp.set('sort', sVal);
      if(q && q.value.trim()) usp.set('q', q.value.trim());
      const st = (modalStatus && modalStatus.value) ? modalStatus.value : '';
      if(st) usp.set('status', st);
    }else if(scope==='archived'){
      usp.set('status','archived');
      usp.set('sort', '-created_at');
    }else{
      usp.set('sort','-created_at');
    }
    return usp.toString();
  }
  function urlFor(scope){
    if(scope==='bin') return '/api/modules/bin?' + baseParams(scope);
    return '/api/modules?' + baseParams(scope);
  }

  /* ========== Row builders & DnD helpers ========== */
  function actionMenu(scope, r){
    const key = r.uuid || r.id;
    if(scope==='active' || scope==='archived'){
      const archived = String(r.status||'').toLowerCase()==='archived';
      return `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" data-act="edit" data-key="${key}" data-name="${esc(r.name||'')}"><i class="fa fa-pen-to-square"></i> Edit</button></li>

            <!-- + Privilege entry for this module -->
            <li><button class="dropdown-item" data-act="privileges" data-key="${key}" data-name="${esc(r.name||'')}"><i class="fa fa-shield-alt"></i> + Privilege</button></li>

            <li><hr class="dropdown-divider"></li>
            ${archived
              ? `<li><button class="dropdown-item" data-act="unarchive" data-key="${key}" data-name="${esc(r.name||'')}"><i class="fa fa-box-open"></i> Unarchive</button></li>`
              : `<li><button class="dropdown-item" data-act="archive" data-key="${key}" data-name="${esc(r.name||'')}"><i class="fa fa-box-archive"></i> Archive</button></li>`
            }
            <li><button class="dropdown-item text-danger" data-act="delete" data-key="${key}" data-name="${esc(r.name||'')}"><i class="fa fa-trash"></i> Delete</button></li>
          </ul>
        </div>`;
    }
    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-vertical"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" data-act="restore" data-key="${key}" data-name="${esc(r.name||'')}"><i class="fa fa-rotate-left"></i> Restore</button></li>
          <li><button class="dropdown-item text-danger" data-act="force" data-key="${key}" data-name="${esc(r.name||'')}"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>
        </ul>
      </div>`;
  }

  function rowHTML(scope, r){
    const tr=document.createElement('tr');
    const desc = (r.description || '') + '';
    const created = fmtDate(r.created_at);
    const delAt   = fmtDate(r.deleted_at);
    const isArchived = String(r.status||'').toLowerCase()==='archived';
    const isDeleted  = !!r.deleted_at;

    if(isArchived && scope!=='bin') tr.classList.add('state-archived');
    if(isDeleted  || scope==='bin') tr.classList.add('state-deleted');

    // small helper to render href: show a small link under name when present
   const renderHref = (href) => {
  if (!href) return '';
  const safeHref = esc(href);
  const isExternal = /^https?:\/\//i.test(safeHref);

  return `
    <div class="small">
      <a 
        href="${safeHref}"
        ${isExternal ? 'target="_blank" rel="noopener noreferrer"' : ''} 
        class="text-decoration-none"
      >
        <i class="fa fa-up-right-from-square me-1"></i>${safeHref}
      </a>
    </div>
  `;
};


    if(scope==='active'){
      tr.draggable = reorderMode;
      tr.dataset.key = r.uuid || r.id;
      tr.dataset.id  = r.id;
      tr.classList.add('reorderable');
      tr.innerHTML = `
        <td class="text-center"><i class="fa fa-grip-lines-vertical drag-handle"></i></td>
        <td>
          <div class="fw-semibold">${esc(r.name || '-')}</div>
          ${renderHref(r.href)}
          <div class="small text-muted">${esc((desc || '').slice(0,100))}${desc && desc.length>100 ? '…' : ''}</div>
        </td>
        <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
        <td>${badgeStatus(r.status || '-')}</td>
        <td>${created}</td>
        <td class="text-end">${actionMenu(scope, r)}</td>`;

      const handle = tr.querySelector('.drag-handle');
      if(reorderMode){ handle && (handle.style.opacity = '1'); } else { handle && (handle.style.opacity = '.35'); }

      tr.addEventListener('dragstart', (ev)=>{ if(!reorderMode){ ev.preventDefault(); return; } draggingRow=tr; tr.classList.add('dragging'); });
      tr.addEventListener('dragend',   ()=>{ tr.classList.remove('dragging'); draggingRow=null; });
      tr.addEventListener('dragover',  (ev)=>{ if(!reorderMode) return; ev.preventDefault();
        const tbody = tr.parentElement;
        const after = getDragAfterElement(tbody, ev.clientY);
        if(after==null) tbody.appendChild(draggingRow); else tbody.insertBefore(draggingRow, after);
      });
      return tr;
    }

    if(scope==='archived'){
      tr.innerHTML = `
        <td>
          <div class="fw-semibold">${esc(r.name || '-')}</div>
          ${renderHref(r.href)}
          <div class="small text-muted">${esc((desc || '').slice(0,100))}${desc && desc.length>100 ? '…' : ''}</div>
        </td>
        <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
        <td>${created}</td>
        <td class="text-end">${actionMenu(scope, r)}</td>`;
      return tr;
    }

    // bin
    tr.innerHTML = `
      <td>
        <div class="fw-semibold">${esc(r.name || '-')}</div>
        ${renderHref(r.href)}
        <div class="small text-muted">${esc((desc || '').slice(0,100))}${desc && desc.length>100 ? '…' : ''}</div>
      </td>
      <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
      <td>${delAt}</td>
      <td class="text-end">${actionMenu(scope, r)}</td>`;
    return tr;
  }

  function getDragAfterElement(container, y){
    const rows=[...container.querySelectorAll('tr.reorderable:not(.dragging)')];
    return rows.reduce((closest, child)=>{
      const box=child.getBoundingClientRect();
      const offset=y - box.top - box.height/2;
      if(offset<0 && offset>closest.offset){ return {offset, element:child}; }
      else return closest;
    }, {offset:Number.NEGATIVE_INFINITY}).element;
  }

  /* ========== Loaders ========== */
  function load(scope){
    const refs=tabs[scope], rowsEl=qs(refs.rows), empty=qs(refs.empty), pager=qs(refs.pager), meta=qs(refs.meta);
    if(!rowsEl) return;
    clearBody(scope); show(empty,false); pager.innerHTML=''; meta.textContent='—'; showLoader(scope,true);

    fetch(urlFor(scope), {headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json','Cache-Control':'no-cache'}})
    .then(r=>r.json().then(j=>({ok:r.ok, j})))
    .then(({ok,j})=>{
      if(!ok) throw new Error(j?.message||'Load failed');
      const items=j?.data || (j.module ? [j.module] : []);
      const pag  = j?.pagination || j?.meta || {page:1, per_page:20, total:items.length};

      if(items.length===0) show(empty, true);

      const frag=document.createDocumentFragment();
      items.forEach(r=> frag.appendChild(rowHTML(scope, r)));
      rowsEl.appendChild(frag);

      // Pager
      const total=Number(pag.total||0), per=Number(pag.per_page||20), cur=Number(pag.page||1);
      const pages=Math.max(1, Math.ceil(total/per));
      const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
      let html=''; html+=li(cur<=1,false,'Previous',cur-1);
      const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
      if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
      for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
      if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
      html+=li(cur>=pages,false,'Next',cur+1);
      pager.innerHTML=html;
      pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{
        const t=Number(a.dataset.page); if(!t || t===state[scope].page) return; state[scope].page = Math.max(1,t); load(scope);
        window.scrollTo({top:0,behavior:'smooth'});
      }));

      meta.textContent = `Showing page ${cur} of ${pages} — ${total} result(s)`;
    })
    .catch(e=>{
      console.error(e); show(empty,true); qs(tabs[scope].meta).textContent='Failed to load'; err(e.message||'Load error');
    })
    .finally(()=> showLoader(scope,false));
  }

  /* ========== Sorting (active) ========== */
  document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col=th.dataset.col;
      if(sort===col) sort='-'+col;
      else if(sort==='-'+col) sort=col;
      else sort=(col==='created_at')?'-created_at':col;
      state.active.page=1; syncSortHeaders(); load('active');
    });
  });

  /* ========== Filters (active) ========== */
  let srT;
  if(q) q.addEventListener('input', ()=>{ clearTimeout(srT); srT=setTimeout(()=>{ state.active.page=1; load('active'); }, 350); });

  // Reset: clear search and modal filters and reload
  if(btnReset){
    btnReset.addEventListener('click', ()=>{
      if(q) q.value='';
      if(modalStatus) modalStatus.value = '';
      if(modalPerPage) modalPerPage.value = '20';
      if(modalSort) modalSort.value = '-created_at';
      if(perPageSel) perPageSel.value='20';
      sort='-created_at';
      state.active.page=1;
      syncSortHeaders();
      load('active');
    });
  }

  if(perPageSel) perPageSel.addEventListener('change', ()=>{ state.active.page=1; load('active'); });

  /* ========== Filter modal init & handlers ======== */
  (function initFilterModal(){
    if (!filterModalEl) return;

    btnFilterOpen && btnFilterOpen.addEventListener('click', ()=>{
      const instance = bootstrap.Modal.getOrCreateInstance(filterModalEl);
      instance.show();
    });

    filterModalEl.addEventListener('show.bs.modal', ()=>{
      try{
        if (modalStatus) modalStatus.value = modalStatus.value || '';
        if (modalPerPage) modalPerPage.value = modalPerPage.value || (perPageSel?.value || '20');
        if (modalSort) modalSort.value = modalSort.value || sort || '-created_at';
      }catch(e){}
    });

    if(btnApplyFilters){
      btnApplyFilters.addEventListener('click', ()=>{
        if(modalSort) sort = modalSort.value || sort;
        if(modalPerPage && perPageSel) perPageSel.value = modalPerPage.value || perPageSel.value;
        state.active.page = 1;
        const instance = bootstrap.Modal.getInstance(filterModalEl) || new bootstrap.Modal(filterModalEl);
        instance.hide();

        const onHidden = () => {
          filterModalEl.removeEventListener('hidden.bs.modal', onHidden);
          syncSortHeaders();
          load('active');
        };
        filterModalEl.addEventListener('hidden.bs.modal', onHidden);
      });
    }

    if(btnClearFilters){
      btnClearFilters.addEventListener('click', ()=>{
        if(modalStatus) modalStatus.value = '';
        if(modalPerPage) modalPerPage.value = '20';
        if(modalSort) modalSort.value = '-created_at';
        sort = '-created_at';
        syncSortHeaders();
      });
    }
  })();

  /* ========== Tab on-demand loads ========= */
  document.querySelector('a[href="#tab-active"]').addEventListener('shown.bs.tab', ()=>{ load('active'); });
  document.querySelector('a[href="#tab-archived"]').addEventListener('shown.bs.tab', ()=>{ load('archived'); });
  document.querySelector('a[href="#tab-bin"]').addEventListener('shown.bs.tab', ()=>{ load('bin'); });

  /* ========== Create / Edit Module ========= */
  btnCreate && btnCreate.addEventListener('click', ()=>{ openCreate(); });

  function openCreate(){
    mm.mode.value='create'; mm.key.value=''; mm.title.textContent='Create Module';
    if(mm.name) mm.name.value=''; if(mm.description) mm.description.value=''; if(mm.status) mm.status.value='Active';
    if(mm.href) mm.href.value = '';
    mm.modal.show();
    setTimeout(()=> { mm.name && mm.name.focus && mm.name.focus(); }, 150);
  }

  async function openEdit(key){
    try{
      const res=await fetch(`/api/modules/${encodeURIComponent(key)}`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
      const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Load failed');
      // tolerate both {module:...} and {data:...}
      const r = j.module || j.data || j || {};
      mm.mode.value='edit'; mm.key.value=(r.uuid || r.id); mm.title.textContent='Edit Module';
      if(mm.name) mm.name.value    = r.name || '';
      if(mm.description) mm.description.value = r.description || '';
      if(mm.status) mm.status.value  = (r.status || 'Active');
      if(mm.href) mm.href.value = r.href || '';
      mm.modal.show();
    }catch(e){ err(e.message||'Failed to open'); }
  }

  /* ========== Save handler (create/update) ======== */
  mm.save && mm.save.addEventListener('click', async ()=>{
    if(!mm.name || !mm.name.value.trim()) return Swal.fire('Name required','Please enter a module name.','info');

    const payload = {
      name: mm.name.value.trim(),
      description: mm.description.value.trim() || null,
      status: mm.status && mm.status.value ? mm.status.value : 'Active',
      href: (mm.href && mm.href.value) ? mm.href.value.trim() : '' // include href (empty string default)
    };

    const isEdit = (mm.mode.value==='edit' && mm.key.value);
    const url    = isEdit ? `/api/modules/${encodeURIComponent(mm.key.value)}` : '/api/modules';
    const method = isEdit ? 'PATCH' : 'POST';

    mm.save.disabled = true;
    try{
      const res=await fetch(url,{method,headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload)});
      const j=await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message||'Save failed');
      ok('Module saved');
      mm.modal.hide();
      load('active');
    }catch(e){ err(e.message||'Save failed'); }
    finally{ mm.save.disabled = false; }
  });

  /* ========== Row actions (archive/delete/restore/force & privileges) ========= */
  document.addEventListener('click', async (e)=>{
    const it = e.target.closest('.dropdown-item[data-act]');
    if(!it) return;
    const act=it.dataset.act, key=it.dataset.key, name=it.dataset.name || 'this module';

    if(act==='edit'){ openEdit(key); return; }

    // NEW: handle opening privileges modal for the specific module
    if(act === 'privileges'){
      try{
        // prepare modal with placeholder row while loading
        priv.rowsContainer.innerHTML = '';
        priv.rowsContainer.appendChild(createPrivRow());
        // ensure module list is populated
        await loadModulesForSelect();
        // try set select value to the module key (works whether value is id or uuid)
        if(priv.moduleSelect){
          priv.moduleSelect.value = key;
          // load privileges for that module
          await loadPrivilegesForModule(key);
        }else{
          await loadPrivilegesForModule(key);
        }
        priv.modal.show();
      }catch(openErr){
        console.error(openErr);
        err('Failed to open privileges');
      }
      return;
    }

    if(act==='archive'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Archive module?',html:`“${esc(name)}”`,showCancelButton:true,confirmButtonText:'Archive',confirmButtonColor:'#8b5cf6'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/modules/${encodeURIComponent(key)}/archive`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Archive failed');
        ok('Module archived'); load('active');
      }catch(e){ err(e.message||'Archive failed'); }
      return;
    }

    if(act==='unarchive'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Unarchive module?',html:`“${esc(name)}”`,showCancelButton:true,confirmButtonText:'Unarchive',confirmButtonColor:'#10b981'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/modules/${encodeURIComponent(key)}/unarchive`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Unarchive failed');
        ok('Module unarchived'); load('archived'); load('active');
      }catch(e){ err(e.message||'Unarchive failed'); }
      return;
    }

    if(act==='delete'){
      const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete (soft)?',html:`This moves “${esc(name)}” to Bin.`,showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/modules/${encodeURIComponent(key)}`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Delete failed');
        ok('Moved to Bin'); load('active');
      }catch(e){ err(e.message||'Delete failed'); }
      return;
    }

    if(act==='restore'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Restore module?',html:`“${esc(name)}” will be restored.`,showCancelButton:true,confirmButtonText:'Restore',confirmButtonColor:'#0ea5e9'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/modules/${encodeURIComponent(key)}/restore`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Restore failed');
        ok('Module restored'); load('bin'); load('active');
      }catch(e){ err(e.message||'Restore failed'); }    
      return;
    }

    if(act==='force'){
      const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete permanently?',html:`This cannot be undone.<br>“${esc(name)}”`,showCancelButton:true,confirmButtonText:'Delete permanently',confirmButtonColor:'#dc2626'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/modules/${encodeURIComponent(key)}/force`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Force delete failed');
        ok('Permanently deleted'); load('bin');
      }catch(e){ err(e.message||'Force delete failed'); }
      return;
    }
  });

  /* ========== Reorder mode ========= */
  btnReorder && btnReorder.addEventListener('click', ()=>{
    reorderMode = !reorderMode;
    btnReorder.classList.toggle('btn-primary', reorderMode);
    btnReorder.classList.toggle('btn-light', !reorderMode);
    btnReorder.innerHTML = reorderMode ? '<i class="fa fa-check-double me-1"></i>Reorder On' : '<i class="fa fa-up-down-left-right me-1"></i>Reorder';
    document.getElementById('reorderHint').style.display = reorderMode ? '' : 'none';
    document.getElementById('btnSaveOrder').style.display = reorderMode ? '' : 'none';
    document.getElementById('btnCancelOrder').style.display = reorderMode ? '' : 'none';
    load('active');
  });

  document.getElementById('btnCancelOrder')?.addEventListener('click', ()=>{
    reorderMode=false;
    btnReorder.classList.remove('btn-primary'); btnReorder.classList.add('btn-light');
    btnReorder.innerHTML='<i class="fa fa-up-down-left-right me-1"></i>Reorder';
    document.getElementById('reorderHint').style.display='none';
    document.getElementById('btnSaveOrder').style.display='none';
    document.getElementById('btnCancelOrder').style.display='none';
    load('active');
  });

  document.getElementById('btnSaveOrder')?.addEventListener('click', async ()=>{
    const rows = [...document.querySelectorAll('#rows-active tr.reorderable')];
    const ids  = rows.map(tr => Number(tr.dataset.id)).filter(Number.isInteger);
    if (!ids.length) return Swal.fire('Nothing to save','Drag rows to change order first.','info');

    const payload = { ids };
    try{
      const res = await fetch('/api/modules/reorder', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type' : 'application/json', 'Accept' : 'application/json' },
        body: JSON.stringify(payload)
      });
      const j = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(j?.message || 'Reorder failed');
      ok('Order updated');
      document.getElementById('btnCancelOrder').click();
    }catch(e){ err(e.message || 'Reorder failed'); }
  });

  /* ========== Privileges modal logic (UPDATED) ========== */

  // track originally loaded privilege ids for delete-detection
  let _originalPrivIds = new Set();

  // helper to create a new privilege row DOM (accepts optional id)
  function createPrivRow(action = '', description = '', id = null) {
    const wrapper = document.createElement('div');
    wrapper.className = 'row g-2 align-items-center mb-2 priv-row';
    if (id) wrapper.dataset.privId = String(id);

    wrapper.innerHTML = `
      <div class="col">
        <input type="text" class="form-control priv-action"
          placeholder="Privilege action (e.g., view_reports)"
          maxlength="60"
          value="${esc(action)}">
      </div>

      <div class="col">
        <input type="text" class="form-control priv-desc"
          placeholder="Short description (optional)"
          value="${esc(description)}">
      </div>

      <div class="col-auto">
        <button type="button" class="btn btn-light btn-sm priv-remove" title="Remove">
          <i class="fa fa-trash text-danger"></i>
        </button>
      </div>
    `;

    wrapper.querySelector('.priv-remove').addEventListener('click', () => wrapper.remove());
    return wrapper;
  }


  // load modules into moduleSelect (adds change handler to load existing privileges)
  async function loadModulesForSelect(){
    if(!priv.moduleSelect) return;
    priv.moduleSelect.innerHTML = '<option value="">Loading…</option>';
    try{
      const res = await fetch('/api/modules?per_page=200', {headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
      const j = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message || 'Failed to load modules');
      const items = j.data || [];
      if(!items.length){
        priv.moduleSelect.innerHTML = '<option value="">No modules found</option>';
        return;
      }
      // prefer name, but tolerate title (older api)
      const opts = items.map(m => {
        const label = (m.name || m.title || ('#'+m.id));
        const val = m.uuid || m.id;
        return `<option value="${esc(val)}">${esc(label)}</option>`;
      });
      priv.moduleSelect.innerHTML = '<option value="">Select module…</option>' + opts.join('');
    }catch(e){
      priv.moduleSelect.innerHTML = '<option value="">Failed to load modules</option>';
      console.error(e);
    }

    // attach change handler once (idempotent)
    if(!priv.moduleSelect._hasChangeHandler){
      priv.moduleSelect.addEventListener('change', async ()=>{
        const moduleVal = priv.moduleSelect.value || null;
        // clear existing rows
        priv.rowsContainer.innerHTML = '';
        _originalPrivIds = new Set();
        if(!moduleVal){
          // no module selected: keep one empty editable row
          priv.rowsContainer.appendChild(createPrivRow());
          return;
        }
        // load privileges for selected module
        await loadPrivilegesForModule(moduleVal);
      });
      priv.moduleSelect._hasChangeHandler = true;
    }
  }

  // Fetch privileges for a module and populate rows
  async function loadPrivilegesForModule(moduleVal){
    priv.rowsContainer.innerHTML = '<div class="p-3 small text-muted">Loading privileges…</div>';
    try{
      // Try two likely endpoints (order: module nested endpoint -> generic privileges query)
      const tryEndpoints = [
        `/api/modules/${encodeURIComponent(moduleVal)}/privileges`,
        `/api/privileges?module_id=${encodeURIComponent(moduleVal)}`
      ];

      let items = [];
      let success = false;
      for(const url of tryEndpoints){
        try{
          const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' } });
          if(!res.ok) { continue; }
          const j = await res.json().catch(()=>({}));
          // tolerate {data: [...]}, or direct array, or {privileges: [...]}
          items = j.data || j.privileges || (Array.isArray(j) ? j : []);
          success = true;
          break;
        }catch(e){
          // try next endpoint
          continue;
        }
      }

      priv.rowsContainer.innerHTML = '';
      _originalPrivIds = new Set();
      if(!success || !items || !items.length){
        // no privileges found — show one empty row to allow adding
        priv.rowsContainer.appendChild(createPrivRow());
        return;
      }

      // Populate rows with existing privileges (editable). Expect items elements to have id|uuid, action, description
      items.forEach(it => {
        const id = it.id || it.uuid || it.key || null;
        const action = it.action || it.name || '';
        const desc = it.description || '';
        if(id) _originalPrivIds.add(String(id));
        priv.rowsContainer.appendChild(createPrivRow(action, desc, id));
      });

    }catch(e){
      console.error(e);
      priv.rowsContainer.innerHTML = '<div class="p-3 text-danger small">Failed to load privileges.</div>';
      // fallback to a blank row so user can still add
      priv.rowsContainer.appendChild(createPrivRow());
    }
  }

  // add row button
  priv.addRowBtn && priv.addRowBtn.addEventListener('click', ()=>{
    priv.rowsContainer.appendChild(createPrivRow());
  });

  // save privileges: handle deletes (destroy), PATCH existing ones, POST new ones; show summary
  priv.saveBtn && priv.saveBtn.addEventListener('click', async ()=>{
    const moduleVal = priv.moduleSelect && priv.moduleSelect.value ? priv.moduleSelect.value : null;
    if(!moduleVal) return Swal.fire('Module required','Please select a module first','info');

    const rows = [...priv.rowsContainer.querySelectorAll('.priv-row')];
    // current IDs present in UI
    const currentIds = new Set(rows.map(r => r.dataset.privId).filter(Boolean).map(String));

    // IDs deleted by user = originalIds - currentIds
    const deletedIds = [..._originalPrivIds].filter(id => !currentIds.has(id));

    const payloads = rows.map(r => {
      const actionEl = r.querySelector('.priv-action');
      const descEl = r.querySelector('.priv-desc');
      return {
        id: r.dataset.privId ? r.dataset.privId : null,
        action: actionEl ? actionEl.value.trim() : '',
        description: descEl ? descEl.value.trim() : ''
      };
    }).filter(p => p.action && p.action.length>0);

    if(!payloads.length && !deletedIds.length) return Swal.fire('No changes','Nothing to save or delete','info');

    const { isConfirmed } = await Swal.fire({
      icon: 'question',
      title: 'Apply privilege changes?',
      html: `This will create/update ${payloads.length} privilege(s) and delete ${deletedIds.length} removed privilege(s).`,
      showCancelButton: true,
      confirmButtonText: 'Proceed',
      confirmButtonColor: '#0ea5e9'
    });
    if(!isConfirmed) return;

    priv.saveBtn.disabled = true;
    try{
      // perform delete ops first (so server constraints on uniqueness won't conflict)
      const deleteOps = deletedIds.map(id => fetch(`/api/privileges/${encodeURIComponent(id)}`, {
        method: 'DELETE',
        headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' }
      }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err })));

      const deleteSettled = await Promise.allSettled(deleteOps);
      let deleted = 0, deleteFailed = 0;
      for(let i=0;i<deleteSettled.length;i++){
        const s = deleteSettled[i];
        if(s.status === 'fulfilled'){
          const r = s.value;
          if(r.err || !r.res || !r.res.ok) { deleteFailed++; } else deleted++;
        } else deleteFailed++;
      }

      // now perform create/update for remaining rows
      const ops = payloads.map(p => {
        if(p.id){
          // update existing privilege
          const url = `/api/privileges/${encodeURIComponent(p.id)}`;
          return fetch(url, {
            method: 'PATCH',
            headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ action: p.action, description: p.description || null, module_id: moduleVal })
          }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err }));
        }else{
          // create new privilege
          const url = `/api/privileges`;
          return fetch(url, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ module_id: moduleVal, action: p.action, description: p.description || null })
          }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err }));
        }
      });

      const settled = await Promise.allSettled(ops);
      let created = 0, updated = 0, failed = 0;
      // iterate results to count outcomes and optionally update DOM with new ids for created rows
      for(let i=0;i<settled.length;i++){
        const s = settled[i];
        const input = payloads[i];
        if(s.status === 'fulfilled'){
          const payloadResult = s.value;
          if(payloadResult.err){ failed++; continue; }
          const res = payloadResult.res;
          const body = payloadResult.j || {};
          if(!res || !res.ok){ failed++; continue; }
          if(input.id) updated++;
          else created++;
          // if new, try to write back returned id to row so further edits will PATCH
          if(!input.id && body && (body.id || body.uuid || body.data?.id || body.data?.uuid)){
            const newId = body.id || body.uuid || body.data?.id || body.data?.uuid;
            // find the corresponding row (match by action + description — best-effort)
            const matchRow = [...priv.rowsContainer.querySelectorAll('.priv-row')].find(r=>{
              const a=r.querySelector('.priv-action')?.value?.trim();
              const d=r.querySelector('.priv-desc')?.value?.trim();
              return a===input.action && d===(input.description||'') && !r.dataset.privId;
            });
            if(matchRow) matchRow.dataset.privId = String(newId);
          }
        }else{
          failed++;
        }
      }

      const parts = [];
      if(created) parts.push(`${created} created`);
      if(updated) parts.push(`${updated} updated`);
      if(deleted) parts.push(`${deleted} deleted`);
      if(failed) parts.push(`${failed} failed`);
      if(deleteFailed) parts.push(`${deleteFailed} delete-failed`);
      ok(parts.length ? parts.join(' • ') : 'No changes');

      // keep modal open if some failed, otherwise close
      if(failed === 0 && deleteFailed === 0){
        priv.modal.hide();
      }else{
        err(`${(failed+deleteFailed)} operation(s) failed — check rows and try again`);
      }

      // reload modules into select (to reflect any label changes if your API returns updated module list)
      await loadModulesForSelect();
      // refresh the list for the currently selected module to reflect server state
      if(priv.moduleSelect.value) await loadPrivilegesForModule(priv.moduleSelect.value);

    }catch(e){
      console.error(e);
      err(e.message || 'Failed to create/update privileges');
    }finally{
      priv.saveBtn.disabled = false;
    }
  });

  /* ========== Initial setup ========= */
  syncSortHeaders();
  setToolbarEnabled(true);
  load('active');

})();
</script>
@endpush
