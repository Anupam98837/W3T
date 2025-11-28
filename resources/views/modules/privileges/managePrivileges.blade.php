{{-- resources/views/privileges/managePrivilege.blade.php --}}
@extends('pages.users.admin.layout.structure')


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
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid(var(--line-strong));background:var(--surface)}
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

/* Keep portal dropdown rules (as in modules view) */
.dropdown-menu.dropdown-menu-end.show {
  position: fixed !important;
  transform: none !important;
  left: 0 !important;
  top: 0 !important;
  z-index: 9000 !important;
  min-width: 220px;
  overflow: visible !important;
  display: block !important;
}

.dropdown-menu.dd-portal { position: fixed !important; transform: none !important; z-index: 9000 !important; min-width: 220px; border-radius: 12px; border: 1px solid var(--line-strong); box-shadow: var(--shadow-2); background: var(--surface); }

/* Privilege specific helpers */
.priv-rows .row { gap: .5rem; align-items: center; margin-bottom: .5rem; }
.priv-rows .col-action { flex: 1 1 40%; }
.priv-rows .col-desc   { flex: 1 1 40%; }
.priv-rows .col-module { flex: 1 1 20%; }
.priv-rows .col-remove { flex: 0 0 48px; text-align: right; }
</style>
@endpush

@section('content')
<div class="cm-wrap">

  {{-- ===== Global toolbar ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-xxl d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Manage Privileges</label>
      </div>
    </div>
  </div>

  {{-- ===== Tabs ===== --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-shield-alt me-2" aria-hidden="true"></i>
        Privileges
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

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="tab-active" role="tabpanel">

      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;">
              <option>10</option><option>20</option><option selected>30</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:320px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search action/description/module…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="btnReset" class="btn btn-primary"><i class="fa fa-rotate-left me-1"></i>Reset</button>
        </div>

        <div class="col-12 col-xxl-auto ms-xxl-auto d-flex justify-content-xxl-end gap-2">
          <button id="btnReorder" class="btn btn-light"><i class="fa fa-up-down-left-right me-1"></i>Reorder</button>
          <button id="btnCreatePriv" class="btn btn-primary"><i class="fa fa-plus me-1"></i>New Privilege</button>
          <button id="btnBulkPriv" class="btn btn-light" style="display:none"><i class="fa fa-plus-square me-1"></i>Bulk Edit</button>
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
                  <th class="sortable" data-col="action">ACTION <span class="caret"></span></th>
                  <th class="sortable" data-col="module" style="width:18%">MODULE <span class="caret"></span></th>
                  <th style="width:28%;">DESCRIPTION</th>
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
            <i class="fa fa-shield-alt mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No privileges found.</div>
          </div>

          <div id="reorderHint" class="p-3 small text-muted" style="display:none;">
            Reorder mode is ON — drag rows using <i class="fa fa-grip-lines-vertical"></i> and click <b>Save Order</b>.
            This applies to the currently visible page.
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

    {{-- ARCHIVED --}}
    <div class="tab-pane fade" id="tab-archived" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>ACTION</th>
                  <th style="width:18%;">MODULE</th>
                  <th style="width:28%;">DESCRIPTION</th>
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-archived">
                <tr id="loaderRow-archived" style="display:none;">
                  <td colspan="5" class="p-0">
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
            <div>No archived privileges.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-archived">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-archived" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- BIN --}}
    <div class="tab-pane fade" id="tab-bin" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>ACTION</th>
                  <th style="width:18%;">MODULE</th>
                  <th style="width:28%;">DESCRIPTION</th>
                  <th style="width:140px;">DELETED AT</th>
                  <th class="text-end" style="width:160px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-bin">
                <tr id="loaderRow-bin" style="display:none;">
                  <td colspan="5" class="p-0">
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

{{-- ===== Create / Edit Privilege Modal ===== --}}
<div class="modal fade" id="privCreateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="pv_title" class="modal-title"><i class="fa fa-shield-alt me-2"></i>Create Privilege</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="pv_mode" value="create">
        <input type="hidden" id="pv_key" value="">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Module <span class="text-danger">*</span></label>
            <select id="pv_module" class="form-select"></select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Action (unique per module) <span class="text-danger">*</span></label>
            <input id="pv_action" class="form-control" maxlength="60" placeholder="e.g., create:user">
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea id="pv_description" class="form-control" rows="3" placeholder="Optional description"></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select id="pv_status" class="form-select">
              <option value="Active" selected>Active</option>
              <option value="Draft">Draft</option>
              <option value="Archived">Archived</option>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="pv_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
      </div>
    </div>
  </div>
</div>

{{-- ===== Bulk Edit Modal (similar to Modules privileges modal) ===== --}}
<div class="modal fade" id="privBulkModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-list me-2"></i>Bulk Edit Privileges</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 mb-2">
          <div class="col-md-6">
            <label class="form-label">Module</label>
            <select id="bulk_module_select" class="form-select"><option>Loading…</option></select>
            <div class="small text-muted mt-1">Select the module to edit its privileges.</div>
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <button id="bulkAddRow" type="button" class="btn btn-primary ms-auto"><i class="fa fa-plus me-1"></i>Add Privilege Row</button>
          </div>
        </div>

        <div class="priv-rows" id="bulk_rows_container">
          <!-- dynamic rows -->
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="bulk_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Apply Changes</button>
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
/* Dropdown portal logic copied from modules view (unchanged) */
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

document.addEventListener('click',(e)=>{ const btn=e.target.closest('.dd-toggle'); if(!btn) return; e.preventDefault(); e.stopPropagation(); bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'}).toggle(); });

(function(){
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!TOKEN){ Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/'); return; }

  const okToast=new bootstrap.Toast(document.getElementById('okToast'));
  const errToast=new bootstrap.Toast(document.getElementById('errToast'));
  const ok=(m)=>{document.getElementById('okMsg').textContent=m||'Done'; okToast.show();};
  const err=(m)=>{document.getElementById('errMsg').textContent=m||'Something went wrong'; errToast.show();};

  /* Elements */
  const btnCreatePriv = document.getElementById('btnCreatePriv');
  const btnBulkPriv = document.getElementById('btnBulkPriv');
  const btnReorder = document.getElementById('btnReorder');
  const btnReset = document.getElementById('btnReset');
  const perPageSel = document.getElementById('per_page');
  const q = document.getElementById('q');
  const sortHint = document.getElementById('sortHint');

  /* Modals */
  const pv = {
    modal: new bootstrap.Modal(document.getElementById('privCreateModal')),
    mode: document.getElementById('pv_mode'),
    key: document.getElementById('pv_key'),
    title: document.getElementById('pv_title'),
    module: document.getElementById('pv_module'),
    action: document.getElementById('pv_action'),
    description: document.getElementById('pv_description'),
    status: document.getElementById('pv_status'),
    save: document.getElementById('pv_save')
  };

  const bulk = {
    modal: new bootstrap.Modal(document.getElementById('privBulkModal')),
    moduleSelect: document.getElementById('bulk_module_select'),
    rowsContainer: document.getElementById('bulk_rows_container'),
    addBtn: document.getElementById('bulkAddRow'),
    saveBtn: document.getElementById('bulk_save')
  };

  const tabs = { active:{rows:'#rows-active', loader:'#loaderRow-active', empty:'#empty-active', pager:'#pager-active', meta:'#metaTxt-active'}, archived:{rows:'#rows-archived', loader:'#loaderRow-archived', empty:'#empty-archived', pager:'#pager-archived', meta:'#metaTxt-archived'}, bin:{rows:'#rows-bin', loader:'#loaderRow-bin', empty:'#empty-bin', pager:'#pager-bin', meta:'#metaTxt-bin'} };
  const state = { active:{page:1}, archived:{page:1}, bin:{page:1} };
  let sort = '-created_at';
  let reorderMode = false;
  let draggingRow = null;

  const esc=(s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>\"'`]/g,ch=>m[ch]); };
  const fmtDate=(iso)=>{ if(!iso) return '-'; const d=new Date(iso); if(isNaN(d)) return esc(iso); return d.toLocaleString(undefined,{year:'numeric',month:'short','day':'2-digit','hour':'2-digit','minute':'2-digit'}); };
  const qs=(sel)=> sel ? document.querySelector(sel) : null;
  const show=(el,v)=>{ if(!el) return; el.style.display = v ? '' : 'none'; };
  const enable=(el,v)=>{ if(!el) return; el.disabled = !v; };
  const showLoader=(which, v)=>{ show(qs(tabs[which].loader), v); };
  const clearBody=(which)=>{ const rowsEl=qs(tabs[which].rows); if(!rowsEl) return; rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(n=>n.remove()); };

  function syncSortHeaders(){ document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{ th.classList.remove('asc','desc'); const col = th.dataset.col; if(sort===col) th.classList.add('asc'); if(sort==='-'+col) th.classList.add('desc'); }); sortHint.textContent = (sort==="-created_at") ? "Newest first" : (sort==="created_at" ? "Oldest first" : ("Sorted by "+sort.replace('-',''))); }

  function baseParams(scope){
    const usp=new URLSearchParams();
    const per = Number(perPageSel?.value || 30);
    const pg = Number(state[scope].page||1);
    usp.set('per_page', per);
    usp.set('page', pg);
    if(scope==='active'){
      usp.set('sort', sort);
      if(q && q.value.trim()) usp.set('q', q.value.trim());
    } else if(scope==='archived'){
      // no-op here: archived controller will return archived rows
      usp.set('sort','-created_at');
    } else {
      usp.set('sort','-created_at');
    }
    return usp.toString();
  }
  function urlFor(scope){
    if (scope === 'bin') {
      return '/api/privileges/bin?' + baseParams(scope);
    }
    if (scope === 'archived') {
      // use the dedicated archived endpoint which the controller exposes
      return '/api/privileges/archived?' + baseParams(scope);
    }
    return '/api/privileges?' + baseParams(scope);
  }

  function actionMenu(scope, r){
    const key = r.uuid || r.id;
    // module identifier to preselect modals (prefer uuid then id)
    const moduleVal = (r.module_uuid || r.module_id || r.module) || '';
    if(scope==='active' || scope==='archived'){
      const archived = String(r.status||'').toLowerCase()==='archived';
      return `
    <div class="dropdown text-end" data-bs-display="static">
      <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
        <i class="fa fa-ellipsis-vertical"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button class="dropdown-item" data-act="addpriv" data-module="${esc(moduleVal)}" data-key="${key}"><i class="fa fa-shield-alt"></i> + Privilege</button></li>
        <li><button class="dropdown-item" data-act="edit" data-key="${key}" data-action="${esc(r.action||'')}"><i class="fa fa-pen-to-square"></i> Edit</button></li>
        <li><hr class="dropdown-divider"></li>
        ${archived ? `<li><button class="dropdown-item" data-act="unarchive" data-key="${key}" data-action="${esc(r.action||'')}"><i class="fa fa-box-open"></i> Unarchive</button></li>` : `<li><button class="dropdown-item" data-act="archive" data-key="${key}" data-action="${esc(r.action||'')}"><i class="fa fa-box-archive"></i> Archive</button></li>`}
        <li><button class="dropdown-item text-danger" data-act="delete" data-key="${key}" data-action="${esc(r.action||'')}"><i class="fa fa-trash"></i> Delete</button></li>
      </ul>
    </div>`;
    }
    return `
    <div class="dropdown text-end" data-bs-display="static">
      <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-vertical"></i></button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button class="dropdown-item" data-act="restore" data-key="${key}" data-action="${esc(r.action||'')}"><i class="fa fa-rotate-left"></i> Restore</button></li>
        <li><button class="dropdown-item text-danger" data-act="force" data-key="${key}" data-action="${esc(r.action||'')}"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>
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

    if(scope==='active'){
      tr.draggable = reorderMode;
      tr.dataset.key = r.uuid || r.id;
      tr.dataset.id  = r.id;
      tr.classList.add('reorderable');
      tr.innerHTML = `
      <td class="text-center"><i class="fa fa-grip-lines-vertical drag-handle"></i></td>
      <td>
        <div class="fw-semibold">${esc(r.action || '-')}</div>
        <div class="small text-muted">${esc((desc || '').slice(0,80))}${desc && desc.length>80 ? '…' : ''}</div>
      </td>
      <td>${esc((r.module_name||r.module || '-'))}</td>
      <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
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
        <div class="fw-semibold">${esc(r.action || '-')}</div>
        <div class="small text-muted">${esc((desc || '').slice(0,80))}${desc && desc.length>80 ? '…' : ''}</div>
      </td>
      <td>${esc((r.module_name||r.module || '-'))}</td>
      <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
      <td>${created}</td>
      <td class="text-end">${actionMenu(scope, r)}</td>`;
      return tr;
    }

    // bin
    tr.innerHTML = `
    <td>
      <div class="fw-semibold">${esc(r.action || '-')}</div>
      <div class="small text-muted">${esc((desc || '').slice(0,80))}${desc && desc.length>80 ? '…' : ''}</div>
    </td>
    <td>${esc((r.module_name||r.module || '-'))}</td>
    <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
    <td>${delAt}</td>
    <td class="text-end">${actionMenu(scope, r)}</td>`;
    return tr;
  }

  function getDragAfterElement(container, y){ const rows=[...container.querySelectorAll('tr.reorderable:not(.dragging)')]; return rows.reduce((closest, child)=>{ const box=child.getBoundingClientRect(); const offset=y - box.top - box.height/2; if(offset<0 && offset>closest.offset){ return {offset, element:child}; } else return closest; }, {offset:Number.NEGATIVE_INFINITY}).element; }

  function load(scope){ const refs=tabs[scope], rowsEl=qs(refs.rows), empty=qs(refs.empty), pager=qs(refs.pager), meta=qs(refs.meta); if(!rowsEl) return; clearBody(scope); show(empty,false); pager.innerHTML=''; meta.textContent='—'; showLoader(scope,true);
    fetch(urlFor(scope), {headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json','Cache-Control':'no-cache'}})
    .then(r=>r.json().then(j=>({ok:r.ok, j})))
    .then(({ok,j})=>{ if(!ok) throw new Error(j?.message||'Load failed'); const items=j?.data || (j.privilege ? [j.privilege] : []); const pag  = j?.pagination || j?.meta || {page:1, per_page:30, total:items.length}; if(items.length===0) show(empty, true); const frag=document.createDocumentFragment(); items.forEach(r=> frag.appendChild(rowHTML(scope, r))); rowsEl.appendChild(frag); const total=Number(pag.total||0), per=Number(pag.per_page||30), cur=Number(pag.page||1); const pages=Math.max(1, Math.ceil(total/per)); const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`; let html=''; html+=li(cur<=1,false,'Previous',cur-1); const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w); if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; } for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i); if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); } html+=li(cur>=pages,false,'Next',cur+1); pager.innerHTML=html; pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{ const t=Number(a.dataset.page); if(!t || t===state[scope].page) return; state[scope].page = Math.max(1,t); load(scope); window.scrollTo({top:0,behavior:'smooth'}); })); meta.textContent = `Showing page ${cur} of ${pages} — ${total} result(s)`; })
    .catch(e=>{ console.error(e); show(empty,true); qs(tabs[scope].meta).textContent='Failed to load'; err(e.message||'Load error'); })
    .finally(()=> showLoader(scope,false)); }

  document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{ th.addEventListener('click', ()=>{ const col=th.dataset.col; if(sort===col) sort='-'+col; else if(sort==='-'+col) sort=col; else sort=(col==='created_at')?'-created_at':col; state.active.page=1; syncSortHeaders(); load('active'); }); });

  let srT; if(q) q.addEventListener('input', ()=>{ clearTimeout(srT); srT=setTimeout(()=>{ state.active.page=1; load('active'); }, 350); });
  if(btnReset){ btnReset.addEventListener('click', ()=>{ if(q) q.value=''; if(perPageSel) perPageSel.value='30'; sort='-created_at'; state.active.page=1; syncSortHeaders(); load('active'); }); }
  if(perPageSel) perPageSel.addEventListener('change', ()=>{ state.active.page=1; load('active'); });

  document.querySelector('a[href="#tab-active"]').addEventListener('shown.bs.tab', ()=>{ load('active'); });
  document.querySelector('a[href="#tab-archived"]').addEventListener('shown.bs.tab', ()=>{ load('archived'); });
  document.querySelector('a[href="#tab-bin"]').addEventListener('shown.bs.tab', ()=>{ load('bin'); });

  btnCreatePriv && btnCreatePriv.addEventListener('click', async ()=>{ openCreatePriv(); });

  // make openCreatePriv accept optional moduleVal to pre-select module
  async function openCreatePriv(moduleVal = null){
    pv.mode.value='create'; pv.key.value=''; pv.title.textContent='Create Privilege';
    if(pv.action) pv.action.value='';
    if(pv.description) pv.description.value='';
    if(pv.status) pv.status.value='Active';
    // load modules into the select and preselect if provided
    await loadModulesInto(pv.module);
    if(moduleVal && pv.module) pv.module.value = moduleVal;
    pv.modal.show();
    setTimeout(()=> pv.action && pv.action.focus && pv.action.focus(), 150);
  }

  async function openEditPriv(key){
    try{
      const res=await fetch(`/api/privileges/${encodeURIComponent(key)}`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Load failed');
      const r = j.privilege || j.data || j || {};
      pv.mode.value='edit'; pv.key.value=(r.uuid || r.id); pv.title.textContent='Edit Privilege';
      await loadModulesInto(pv.module);
      if(pv.module) pv.module.value = r.module_id || r.module_uuid || r.module || '';
      if(pv.action) pv.action.value = r.action || '';
      if(pv.description) pv.description.value = r.description || '';
      if(pv.status) pv.status.value = r.status || 'Active';
      pv.modal.show();
    }catch(e){ err(e.message||'Failed to open'); }
  }

  pv.save && pv.save.addEventListener('click', async ()=>{
    if(!pv.action || !pv.action.value.trim()) return Swal.fire('Action required','Please enter an action (unique per module).','info');
    if(!pv.module || !pv.module.value) return Swal.fire('Module required','Select module first.','info');
    const payload = { module_id: pv.module.value, action: pv.action.value.trim(), description: pv.description.value.trim() || null, status: pv.status.value || 'Active' };
    const isEdit = (pv.mode.value==='edit' && pv.key.value);
    const url = isEdit ? `/api/privileges/${encodeURIComponent(pv.key.value)}` : '/api/privileges';
    const method = isEdit ? 'PATCH' : 'POST';
    pv.save.disabled = true;
    try{ const res=await fetch(url,{method,headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload)}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Save failed'); ok('Privilege saved'); pv.modal.hide(); load('active'); }catch(e){ err(e.message||'Save failed'); } finally{ pv.save.disabled = false; } });

  document.addEventListener('click', async (e)=>{
    const it = e.target.closest('.dropdown-item[data-act]');
    if(!it) return;
    const act=it.dataset.act, key=it.dataset.key, actionName=it.dataset.action || 'this privilege';
    // row-level module value for addpriv
    const moduleVal = it.dataset.module || null;

    if(act==='addpriv'){
      // open the single-create modal and preselect module
      openCreatePriv(moduleVal);
      return;
    }

    if(act==='edit'){ openEditPriv(key); return; }

    if(act==='archive'){ const {isConfirmed}=await Swal.fire({icon:'question',title:'Archive privilege?',html:`“${esc(actionName)}”`,showCancelButton:true,confirmButtonText:'Archive'}); if(!isConfirmed) return; try{ const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/archive`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Archive failed'); ok('Privilege archived'); load('active'); }catch(e){ err(e.message||'Archive failed'); } return; }

    if(act==='unarchive'){ const {isConfirmed}=await Swal.fire({icon:'question',title:'Unarchive privilege?',html:`“${esc(actionName)}”`,showCancelButton:true,confirmButtonText:'Unarchive'}); if(!isConfirmed) return; try{ const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/unarchive`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Unarchive failed'); ok('Privilege unarchived'); load('archived'); load('active'); }catch(e){ err(e.message||'Unarchive failed'); } return; }

    if(act==='delete'){ const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete (soft)?',html:`This moves “${esc(actionName)}” to Bin.`,showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'}); if(!isConfirmed) return; try{ const res=await fetch(`/api/privileges/${encodeURIComponent(key)}`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Delete failed'); ok('Moved to Bin'); load('active'); }catch(e){ err(e.message||'Delete failed'); } return; }

    if(act==='restore'){ const {isConfirmed}=await Swal.fire({icon:'question',title:'Restore privilege?',html:`“${esc(actionName)}” will be restored.`,showCancelButton:true,confirmButtonText:'Restore'}); if(!isConfirmed) return; try{ const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/restore`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Restore failed'); ok('Privilege restored'); load('bin'); load('active'); }catch(e){ err(e.message||'Restore failed'); } return; }

    if(act==='force'){ const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete permanently?',html:`This cannot be undone.<br>“${esc(actionName)}”`,showCancelButton:true,confirmButtonText:'Delete permanently',confirmButtonColor:'#dc2626'}); if(!isConfirmed) return; try{ const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/force`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Force delete failed'); ok('Permanently deleted'); load('bin'); }catch(e){ err(e.message||'Force delete failed'); } return; }
  });

  btnReorder && btnReorder.addEventListener('click', ()=>{ reorderMode = !reorderMode; btnReorder.classList.toggle('btn-primary', reorderMode); btnReorder.classList.toggle('btn-light', !reorderMode); btnReorder.innerHTML = reorderMode ? '<i class="fa fa-check-double me-1"></i>Reorder On' : '<i class="fa fa-up-down-left-right me-1"></i>Reorder'; document.getElementById('reorderHint').style.display = reorderMode ? '' : 'none'; document.getElementById('btnSaveOrder').style.display = reorderMode ? '' : 'none'; document.getElementById('btnCancelOrder').style.display = reorderMode ? '' : 'none'; load('active'); });
  document.getElementById('btnCancelOrder')?.addEventListener('click', ()=>{ reorderMode=false; btnReorder.classList.remove('btn-primary'); btnReorder.classList.add('btn-light'); btnReorder.innerHTML='<i class="fa fa-up-down-left-right me-1"></i>Reorder'; document.getElementById('reorderHint').style.display='none'; document.getElementById('btnSaveOrder').style.display='none'; document.getElementById('btnCancelOrder').style.display='none'; load('active'); });
  document.getElementById('btnSaveOrder')?.addEventListener('click', async ()=>{ const rows = [...document.querySelectorAll('#rows-active tr.reorderable')]; const ids  = rows.map(tr => Number(tr.dataset.id)).filter(Number.isInteger); if (!ids.length) return Swal.fire('Nothing to save','Drag rows to change order first.','info'); const payload = { ids }; try{ const res = await fetch('/api/privileges/reorder', { method: 'POST', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type' : 'application/json', 'Accept' : 'application/json' }, body: JSON.stringify(payload) }); const j = await res.json().catch(()=>({})); if (!res.ok) throw new Error(j?.message || 'Reorder failed'); ok('Order updated'); document.getElementById('btnCancelOrder').click(); }catch(e){ err(e.message || 'Reorder failed'); } });

  /* Bulk modal helpers (left unchanged, minor robustness fixes below) */
  let _originalPrivIds = new Set();
  function createBulkRow(action = '', description = '', id = null){ const wrapper=document.createElement('div'); wrapper.className='row g-2 align-items-center mb-2 priv-row'; if(id) wrapper.dataset.privId = String(id); wrapper.innerHTML = `
    <div class="col col-action"><input type="text" class="form-control priv-action" placeholder="Action (e.g., view_reports)" maxlength="60" value="${esc(action)}"></div>
    <div class="col col-module"><select class="form-select priv-module"><option value="">Use module</option></select></div>
    <div class="col col-desc"><input type="text" class="form-control priv-desc" placeholder="Description (optional)" value="${esc(description)}"></div>
    <div class="col-auto"><button type="button" class="btn btn-light btn-sm priv-remove" title="Remove"><i class="fa fa-trash text-danger"></i></button></div>
  `; wrapper.querySelector('.priv-remove').addEventListener('click', ()=> wrapper.remove()); return wrapper; }

  async function loadModulesInto(selectEl){
    if(!selectEl) return;
    try{
      const res = await fetch('/api/modules?per_page=500',{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
      const j = await res.json().catch(()=>({}));
      const items = j.data || [];
      const opts = items.map(m=>`<option value="${esc(m.uuid||m.id)}">${esc(m.name||('#'+m.id))}</option>`);
      selectEl.innerHTML = '<option value="">Select module…</option>' + opts.join('');
    }catch(e){
      selectEl.innerHTML = '<option value="">Failed to load</option>';
    }
  }

  async function loadModulesForBulk(){
    if(!bulk.moduleSelect) return;
    bulk.moduleSelect.innerHTML = '<option>Loading…</option>';
    try{
      await loadModulesInto(bulk.moduleSelect);
      // attach change handler idempotently
      if(!bulk.moduleSelect._hasChangeHandler){
        bulk.moduleSelect.addEventListener('change', async ()=>{
          bulk.rowsContainer.innerHTML='';
          _originalPrivIds = new Set();
          if(!bulk.moduleSelect.value){ bulk.rowsContainer.appendChild(createBulkRow()); return; }
          await loadBulkPrivilegesForModule(bulk.moduleSelect.value);
        });
        bulk.moduleSelect._hasChangeHandler = true;
      }
    }catch(e){ console.error(e); }
  }

  async function loadBulkPrivilegesForModule(moduleVal){
    bulk.rowsContainer.innerHTML = '<div class="p-3 small text-muted">Loading privileges…</div>';
    try{
      const res = await fetch(`/api/privileges?module_id=${encodeURIComponent(moduleVal)}&per_page=500`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
      const j=await res.json().catch(()=>({}));
      const items = j.data || [];
      bulk.rowsContainer.innerHTML='';
      _originalPrivIds = new Set();
      if(!items.length){ bulk.rowsContainer.appendChild(createBulkRow()); return; }
      for(const it of items){
        const id=it.id||it.uuid||null; const action=it.action||''; const desc=it.description||'';
        if(id) _originalPrivIds.add(String(id));
        const row=createBulkRow(action, desc, id);
        const sel = row.querySelector('.priv-module');
        await loadModulesInto(sel);
        if(sel) sel.value = moduleVal;
        bulk.rowsContainer.appendChild(row);
      }
    }catch(e){ console.error(e); bulk.rowsContainer.innerHTML = '<div class="p-3 text-danger small">Failed to load privileges.</div>'; bulk.rowsContainer.appendChild(createBulkRow()); }
  }

  btnBulkPriv && btnBulkPriv.addEventListener('click', async ()=>{ bulk.rowsContainer.innerHTML=''; bulk.rowsContainer.appendChild(createBulkRow()); await loadModulesForBulk(); bulk.modal.show(); });
  bulk.addBtn && bulk.addBtn.addEventListener('click', ()=> bulk.rowsContainer.appendChild(createBulkRow()));

  bulk.saveBtn && bulk.saveBtn.addEventListener('click', async ()=>{
    const moduleVal = bulk.moduleSelect && bulk.moduleSelect.value ? bulk.moduleSelect.value : null;
    const rows = [...bulk.rowsContainer.querySelectorAll('.priv-row')];
    const currentIds = new Set(rows.map(r => r.dataset.privId).filter(Boolean).map(String));
    const deletedIds = [..._originalPrivIds].filter(id => !currentIds.has(id));
    const payloads = rows.map(r=>{
      const actionEl=r.querySelector('.priv-action');
      const descEl=r.querySelector('.priv-desc');
      const modEl=r.querySelector('.priv-module');
      return { id: r.dataset.privId ? r.dataset.privId : null, action: actionEl ? actionEl.value.trim() : '', description: descEl ? descEl.value.trim() : '', module_id: modEl ? modEl.value : moduleVal };
    }).filter(p=>p.action && p.action.length>0);

    if(!payloads.length && !deletedIds.length) return Swal.fire('No changes','Nothing to save or delete','info');
    const {isConfirmed} = await Swal.fire({icon:'question',title:'Apply privilege changes?',html:`This will create/update ${payloads.length} privilege(s) and delete ${deletedIds.length} removed privilege(s).`,showCancelButton:true,confirmButtonText:'Proceed'});
    if(!isConfirmed) return;
    bulk.saveBtn.disabled = true;
    try{
      const deleteOps = deletedIds.map(id => fetch(`/api/privileges/${encodeURIComponent(id)}`, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' } }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err })));
      const deleteSettled = await Promise.allSettled(deleteOps);
      let deleted = 0, deleteFailed = 0;
      for(let i=0;i<deleteSettled.length;i++){ const s = deleteSettled[i]; if(s.status === 'fulfilled'){ const r = s.value; if(r.err || !r.res || !r.res.ok) { deleteFailed++; } else deleted++; } else deleteFailed++; }

      const ops = payloads.map(p => {
        if(p.id){
          const url = `/api/privileges/${encodeURIComponent(p.id)}`;
          return fetch(url, { method: 'PATCH', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ action: p.action, description: p.description || null, module_id: p.module_id }) }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err }));
        } else {
          const url = `/api/privileges`;
          return fetch(url, { method: 'POST', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ module_id: p.module_id, action: p.action, description: p.description || null }) }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err }));
        }
      });

      const settled = await Promise.allSettled(ops);
      let created = 0, updated = 0, failed = 0;
      for(let i=0;i<settled.length;i++){
        const s = settled[i];
        const input = payloads[i];
        if(s.status === 'fulfilled'){
          const payloadResult = s.value;
          if(payloadResult.err){ failed++; continue; }
          const res = payloadResult.res;
          const body = payloadResult.j || {};
          if(!res || !res.ok){ failed++; continue; }
          if(input.id) updated++; else created++;
          if(!input.id && body && (body.id || body.uuid || body.data?.id || body.data?.uuid)){
            const newId = body.id || body.uuid || body.data?.id || body.data?.uuid;
            const matchRow = [...bulk.rowsContainer.querySelectorAll('.priv-row')].find(r=>{
              const a=r.querySelector('.priv-action')?.value?.trim();
              const d=r.querySelector('.priv-desc')?.value?.trim();
              return a===input.action && d===(input.description||'') && !r.dataset.privId;
            });
            if(matchRow) matchRow.dataset.privId = String(newId);
          }
        } else { failed++; }
      }

      const parts = [];
      if(created) parts.push(`${created} created`);
      if(updated) parts.push(`${updated} updated`);
      if(deleted) parts.push(`${deleted} deleted`);
      if(failed) parts.push(`${failed} failed`);
      if(deleteFailed) parts.push(`${deleteFailed} delete-failed`);
      ok(parts.length ? parts.join(' • ') : 'No changes');

      if(failed === 0 && deleteFailed === 0){ bulk.modal.hide(); }else{ err(`${(failed+deleteFailed)} operation(s) failed — check rows and try again`); }

      await loadModulesForBulk();
      if(bulk.moduleSelect.value) await loadBulkPrivilegesForModule(bulk.moduleSelect.value);

    }catch(e){ console.error(e); err(e.message || 'Failed to create/update privileges'); }finally{ bulk.saveBtn.disabled = false; }
  });

  syncSortHeaders(); load('active');
})();
</script>
@endpush
