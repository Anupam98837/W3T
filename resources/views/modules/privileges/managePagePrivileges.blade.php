{{-- resources/views/privileges/managePrivilege.blade.php --}}
@extends('pages.users.layout.structure')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
/* ===== Shell ===== */
.cm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
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

/* Reorder affordances (only for archived/bin tables now) */
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
  transform: none !important;
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

/* Privilege specific helpers */
.priv-rows .row { gap: .5rem; align-items: center; margin-bottom: .5rem; }
.priv-rows .col-action { flex: 1 1 25%; }
.priv-rows .col-api    { flex: 1 1 35%; }
.priv-rows .col-module { flex: 1 1 15%; }
.priv-rows .col-desc   { flex: 1 1 25%; }
.priv-rows .col-remove { flex: 0 0 48px; text-align: right; }

/* ===== ACTIVE TAB ACCORDION VIEW ===== */
.priv-accordion .accordion-item{
  border-radius:12px;
  overflow:hidden;
  border:1px solid var(--line-soft);
  margin-bottom:8px;
  background:var(--surface);
}
.priv-accordion .accordion-button{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:8px 14px;
  gap:8px;
  font-weight:600;
  background:var(--background-soft);
  color:var(--ink);
}
.priv-accordion .accordion-button:not(.collapsed){
  background:var(--surface-soft);
  box-shadow:none;
}
.priv-accordion .accordion-button:focus{
  box-shadow:0 0 0 1px var(--primary-color);
}
.priv-accordion .priv-module-pill{
  font-size:0.75rem;
  border-radius:999px;
  padding:2px 10px;
  background:var(--background-soft);
  color:var(--muted-color);
  border:1px solid var(--line-soft);
}

.priv-accordion .priv-add-wrap{
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap:.5rem;
  margin-bottom:.75rem;
}

.priv-accordion .priv-add-wrap .form-select,
.priv-accordion .priv-add-wrap .form-control{
  height:36px;
  border-radius:10px;
}

.priv-accordion .list-group-item{
  border:none;
  border-radius:10px;
  margin-bottom:6px;
  background:var(--background-soft);
}
.priv-accordion .list-group-item .fw-semibold{
  font-size:0.9rem;
}
.priv-accordion .list-group-item .badge{
  font-size:0.68rem;
}

html.theme-dark .priv-accordion .list-group-item{
  background:#0b1220;
}
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

    {{-- ========== ACTIVE (MODULE-WISE ACCORDION) ========== --}}
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
          <button id="btnBulkPriv" class="btn btn-light"><i class="fa fa-plus-square me-1"></i>Bulk Edit</button>
        </div>

        <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end small text-muted">
          Sorting: <span id="sortHint" class="ms-1">Newest first</span>
        </div>
      </div>

      <div class="card table-wrap">
        <div class="card-body p-0">

          <div id="accordion-active" class="accordion priv-accordion"></div>

          <div id="loaderRow-active" style="display:none;">
            <div class="p-4">
              <div class="placeholder-wave">
                <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                <div class="placeholder col-12 mb-2" style="height:18px;"></div>
              </div>
            </div>
          </div>

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-shield-alt mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No privileges found.</div>
          </div>

          <div id="reorderHint" class="p-3 small text-muted" style="display:none;">
            Reorder mode is ON — (applies mainly to tabular views).
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

    {{-- ========== ARCHIVED (TABLE) ========== --}}
    <div class="tab-pane fade" id="tab-archived" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>ACTION & API</th>
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

    {{-- ========== BIN (TABLE) ========== --}}
    <div class="tab-pane fade" id="tab-bin" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>ACTION & API</th>
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

{{-- ===== Bulk Edit Modal ===== --}}
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
/* ============================================================
   ✅ RUN-ONCE GUARD (prevents duplicate bindings)
   ============================================================ */
(function(){
  if (window.__PRIVILEGE_MANAGE_INIT__) return;
  window.__PRIVILEGE_MANAGE_INIT__ = true;

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
      if(activePortal?.menu?.isConnected){
        activePortal.menu.classList.remove('dd-portal');
        activePortal.parent.appendChild(activePortal.menu);
        activePortal=null;
      }
      const rect=btn.getBoundingClientRect(); menu.__parent=menu.parentElement; place(menu, rect); activePortal={menu, parent:menu.__parent};
      const close=()=>{ try{ bootstrap.Dropdown.getOrCreateInstance(btn).hide(); }catch{} };
      menu.__ls=[ ['resize',close,false], ['scroll',close,true] ];
      window.addEventListener('resize', close); document.addEventListener('scroll', close, true);
    });
    document.addEventListener('hidden.bs.dropdown', (ev)=>{
      const dd=ev.target; const menu=dd.querySelector('.dropdown-menu.dd-portal') || activePortal?.menu;
      if(!menu) return;
      if(menu.__ls){
        document.removeEventListener('scroll', menu.__ls[1][1], true);
        window.removeEventListener('resize', menu.__ls[0][1]);
        menu.__ls=null;
      }
      if(menu.__parent){
        menu.classList.remove('dd-portal');
        menu.style.cssText='';
        menu.__parent.appendChild(menu);
        activePortal=null;
      }
    });
  })();

  document.addEventListener('click',(e)=>{
    const btn=e.target.closest('.dd-toggle');
    if(!btn) return;
    e.preventDefault();
    e.stopPropagation();
    bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'}).toggle();
  });

  (function(){
    const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
    if(!TOKEN){
      Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/');
      return;
    }

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

    const accordionActive = document.getElementById('accordion-active');

    /* Bulk Modal */
    const bulk = {
      modal: new bootstrap.Modal(document.getElementById('privBulkModal')),
      moduleSelect: document.getElementById('bulk_module_select'),
      rowsContainer: document.getElementById('bulk_rows_container'),
      addBtn: document.getElementById('bulkAddRow'),
      saveBtn: document.getElementById('bulk_save')
    };

    const tabs = {
      active:   { loader:'#loaderRow-active',   empty:'#empty-active',   pager:'#pager-active',   meta:'#metaTxt-active' },
      archived: { rows:'#rows-archived', loader:'#loaderRow-archived', empty:'#empty-archived', pager:'#pager-archived', meta:'#metaTxt-archived' },
      bin:      { rows:'#rows-bin',      loader:'#loaderRow-bin',      empty:'#empty-bin',      pager:'#pager-bin',      meta:'#metaTxt-bin' }
    };

    const state = { active:{page:1}, archived:{page:1}, bin:{page:1} };
    let sort = '-created_at';
    let reorderMode = false;

    const DEFAULT_ACTIONS = ['add','edit','delete','view'];

    const esc=(s)=>{
      const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
      return (s==null?'':String(s)).replace(/[&<>\"'`]/g,ch=>m[ch]);
    };

    const fmtDate=(iso)=>{
      if(!iso) return '-';
      const d=new Date(iso);
      if(isNaN(d)) return esc(iso);
      return d.toLocaleString(undefined,{year:'numeric',month:'short','day':'2-digit','hour':'2-digit','minute':'2-digit'});
    };

    const qs=(sel)=> sel ? document.querySelector(sel) : null;
    const show=(el,v)=>{ if(!el) return; el.style.display = v ? '' : 'none'; };
    const showLoader=(which, v)=>{ show(qs(tabs[which].loader), v); };

    /* ========= NEW HELPERS (FIX: dashboard_menu_id + assigned_apis + meta.http_method) ========= */
    const tryParseJson = (v)=>{
      if(v == null) return null;
      if(typeof v === 'object') return v;
      if(typeof v === 'string'){
        const s = v.trim();
        if(!s) return null;
        try { return JSON.parse(s); } catch { return null; }
      }
      return null;
    };

    // Accept "GET /api/users" OR "[GET] /api/users" OR "POST:/api/users"
    function parseApiInput(raw){
      const out = { method:'', api:'' };
      const s = (raw || '').trim();
      if(!s) return out;

      let m = s.match(/^\[\s*(GET|POST|PUT|PATCH|DELETE)\s*\]\s*(.+)$/i);
      if(m){ out.method = m[1].toUpperCase(); out.api = (m[2]||'').trim(); return out; }

      m = s.match(/^(GET|POST|PUT|PATCH|DELETE)\s+(.+)$/i);
      if(m){ out.method = m[1].toUpperCase(); out.api = (m[2]||'').trim(); return out; }

      m = s.match(/^(GET|POST|PUT|PATCH|DELETE)\s*:\s*(.+)$/i);
      if(m){ out.method = m[1].toUpperCase(); out.api = (m[2]||'').trim(); return out; }

      out.api = s;
      return out;
    }

    function pickApiAndMethodFromRecord(r){
      const metaObj = tryParseJson(r?.meta) || {};
      const assigned = tryParseJson(r?.assigned_apis);
      const assignedFirst = Array.isArray(assigned) ? (assigned[0] || '') : '';

      const method =
        (metaObj && metaObj.http_method ? String(metaObj.http_method).toUpperCase() : '') ||
        (Array.isArray(r?.http_methods) ? r.http_methods.join(',') : '') ||
        (r?.http_method ? String(r.http_method).toUpperCase() : '') ||
        (typeof r?.http_methods === 'string' ? r.http_methods : '');

      const api =
        (r?.api_pattern || r?.api || r?.endpoint || assignedFirst || '');

      return { api, method };
    }

    function syncSortHeaders(){
      sortHint.textContent =
        (sort==="-created_at") ? "Newest first"
        : (sort==="created_at" ? "Oldest first" : ("Sorted by "+sort.replace('-','')));
    }

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
        usp.set('sort','-created_at');
        usp.set('status','archived');
      } else {
        usp.set('sort','-created_at');
      }
      return usp.toString();
    }

    function urlFor(scope){
      if (scope === 'bin')      return '/api/privileges/bin?' + baseParams(scope);
      if (scope === 'archived') return '/api/privileges/archived?' + baseParams(scope);
      return '/api/privileges?' + baseParams(scope);
    }

    function actionMenu(scope, r){
      const key = r.uuid || r.id;

      if(scope==='archived'){
        return `
        <div class="d-flex justify-content-end gap-1">
          <button class="btn btn-light btn-sm" data-act="unarchive" data-key="${key}" data-action="${esc(r.action||'')}">
            <i class="fa fa-box-open"></i>
          </button>
          <button class="btn btn-light btn-sm text-danger" data-act="delete" data-key="${key}" data-action="${esc(r.action||'')}">
            <i class="fa fa-trash"></i>
          </button>
        </div>`;
      }

      if(scope==='bin'){
        return `
        <div class="d-flex justify-content-end gap-1">
          <button class="btn btn-light btn-sm" data-act="restore" data-key="${key}" data-action="${esc(r.action||'')}">
            <i class="fa fa-rotate-left"></i>
          </button>
          <button class="btn btn-light btn-sm text-danger" data-act="force" data-key="${key}" data-action="${esc(r.action||'')}">
            <i class="fa fa-skull-crossbones"></i>
          </button>
        </div>`;
      }

      return '';
    }

    function rowHTML(scope, r){
      const tr=document.createElement('tr');
      const desc = (r.description || '') + '';
      const created = fmtDate(r.created_at);
      const delAt   = fmtDate(r.deleted_at);
      const isArchived = String(r.status||'').toLowerCase()==='archived';
      const isDeleted  = !!r.deleted_at;

      const { api, method } = pickApiAndMethodFromRecord(r);
      const apiLine = api
        ? `<span class="d-block mt-1"><i class="fa fa-code me-1 opacity-75"></i>${esc((method ? '['+method+'] ' : '') + api)}</span>`
        : '';

      if(isArchived && scope!=='bin') tr.classList.add('state-archived');
      if(isDeleted  || scope==='bin') tr.classList.add('state-deleted');

      if(scope==='archived'){
        tr.innerHTML = `
        <td>
          <div class="fw-semibold">${esc(r.action || '-')}</div>
          <div class="small text-muted">
            ${esc((desc || '').slice(0,80))}${desc && desc.length>80 ? '…' : ''}
            ${apiLine}
          </div>
        </td>
        <td>${esc((r.module_name||r.module || '-'))}</td>
        <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
        <td>${created}</td>
        <td class="text-end">${actionMenu(scope, r)}</td>`;
        return tr;
      }

      tr.innerHTML = `
      <td>
        <div class="fw-semibold">${esc(r.action || '-')}</div>
        <div class="small text-muted">
          ${esc((desc || '').slice(0,80))}${desc && desc.length>80 ? '…' : ''}
          ${apiLine}
        </div>
      </td>
      <td>${esc((r.module_name||r.module || '-'))}</td>
      <td>${esc((desc || '').slice(0,140))}${desc && desc.length>140 ? '…' : ''}</td>
      <td>${delAt}</td>
      <td class="text-end">${actionMenu(scope, r)}</td>`;
      return tr;
    }

    function clearBody(scope){
      if(scope==='active'){
        if(accordionActive) accordionActive.innerHTML = '';
        return;
      }
      const rowsEl=qs(tabs[scope].rows);
      if(!rowsEl) return;
      rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(n=>n.remove());
    }

    /* ========= BULK EDIT HELPERS ========= */
    let _originalPrivIds = new Set();

    async function loadModulesInto(selectEl){
      if(!selectEl) return;
      try{
        const res = await fetch('/api/dashboard-menus?per_page=500',{
          headers:{
            'Authorization':'Bearer '+TOKEN,
            'Accept':'application/json'
          }
        });
        const j = await res.json().catch(()=>({}));
        const items = j.data || [];
        const opts = items.map(m =>
          `<option value="${esc(m.uuid || m.id)}">${esc(m.name || ('#' + m.id))}</option>`
        );
        selectEl.innerHTML = '<option value="">Select module…</option>' + opts.join('');
      }catch(e){
        selectEl.innerHTML = '<option value="">Failed to load</option>';
      }
    }

    function createBulkRow(action = '', description = '', apiRaw = '', id = null){
      const wrapper=document.createElement('div');
      wrapper.className='row g-2 align-items-center mb-2 priv-row';
      if(id) wrapper.dataset.privId = String(id);
      wrapper.innerHTML = `
        <div class="col col-action">
          <input type="text" class="form-control priv-action" placeholder="Action (e.g., view_reports)" maxlength="60" value="${esc(action)}">
        </div>
        <div class="col col-api">
          <input type="text" class="form-control priv-api" placeholder="API endpoint / route (optional: GET /api/...)" maxlength="255" value="${esc(apiRaw)}">
        </div>
        <div class="col col-module">
          <select class="form-select priv-module"><option value="">Use module</option></select>
        </div>
        <div class="col col-desc">
          <input type="text" class="form-control priv-desc" placeholder="Description (optional)" value="${esc(description)}">
        </div>
        <div class="col-auto col-remove">
          <button type="button" class="btn btn-light btn-sm priv-remove" title="Remove"><i class="fa fa-trash text-danger"></i></button>
        </div>
      `;
      wrapper.querySelector('.priv-remove').addEventListener('click', ()=> wrapper.remove());

      const sel = wrapper.querySelector('.priv-module');
      loadModulesInto(sel);

      return wrapper;
    }

    async function loadModulesForBulk(){
      if(!bulk.moduleSelect) return;
      bulk.moduleSelect.innerHTML = '<option>Loading…</option>';
      try{
        await loadModulesInto(bulk.moduleSelect);
        if(!bulk.moduleSelect._hasChangeHandler){
          bulk.moduleSelect.addEventListener('change', async ()=>{
            bulk.rowsContainer.innerHTML='';
            _originalPrivIds = new Set();
            if(!bulk.moduleSelect.value){
              bulk.rowsContainer.appendChild(createBulkRow());
              return;
            }
            await loadBulkPrivilegesForModule(bulk.moduleSelect.value);
          });
          bulk.moduleSelect._hasChangeHandler = true;
        }
      }catch(e){
        console.error(e);
      }
    }

    async function loadBulkPrivilegesForModule(moduleVal){
      bulk.rowsContainer.innerHTML = '<div class="p-3 small text-muted">Loading privileges…</div>';
      try{
        // ✅ support both query params (some backends use module_id)
        const url = `/api/privileges?dashboard_menu_id=${encodeURIComponent(moduleVal)}&module_id=${encodeURIComponent(moduleVal)}&per_page=500`;
        const res = await fetch(url,{
          headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
        });
        const j=await res.json().catch(()=>({}));
        const items = j.data || [];
        bulk.rowsContainer.innerHTML='';
        _originalPrivIds = new Set();

        if(!items.length){
          bulk.rowsContainer.appendChild(createBulkRow());
          return;
        }

        for(const it of items){
          const id=it.id||it.uuid||null;
          const action=it.action||'';
          const desc=it.description||'';

          const { api, method } = pickApiAndMethodFromRecord(it);
          const apiRaw = (method ? `[${method}] ` : '') + (api || '');

          if(id) _originalPrivIds.add(String(id));
          const row=createBulkRow(action, desc, apiRaw, id);

          const sel = row.querySelector('.priv-module');
          await loadModulesInto(sel);
          if(sel) sel.value = moduleVal;

          bulk.rowsContainer.appendChild(row);
        }
      }catch(e){
        console.error(e);
        bulk.rowsContainer.innerHTML = '<div class="p-3 text-danger small">Failed to load privileges.</div>';
        bulk.rowsContainer.appendChild(createBulkRow());
      }
    }

    async function openBulkCreate(){
      bulk.rowsContainer.innerHTML='';
      bulk.rowsContainer.appendChild(createBulkRow());
      await loadModulesForBulk();
      if(bulk.moduleSelect) bulk.moduleSelect.value = '';
      bulk.modal.show();
    }

    /* ===== LOAD ACTIVE (ACCORDION) ===== */
    function renderActiveAccordion(items, pagination){
      accordionActive.innerHTML = '';
      const empty = qs(tabs.active.empty);
      const pager = qs(tabs.active.pager);
      const meta  = qs(tabs.active.meta);

      if(!items.length){
        show(empty, true);
        pager.innerHTML = '';
        meta.textContent = 'No privileges.';
        return;
      }

      show(empty, false);

      // ✅ group by module (support dashboard_menu_id + uuid + names)
      const groups = {};
      items.forEach(r=>{
        const key = String(
          r.dashboard_menu_id || r.dashboard_menu_uuid ||
          r.module_id || r.module_uuid ||
          r.module || r.module_name || '0'
        );
        if(!groups[key]){
          groups[key] = {
            module_id: r.dashboard_menu_id || r.module_id || null,
            module_uuid: r.dashboard_menu_uuid || r.module_uuid || null,
            module_name: r.module_name || r.module || 'Unassigned',
            items: []
          };
        }
        groups[key].items.push(r);
      });

      const builtInSet = new Set(DEFAULT_ACTIONS);

      let idx = 0;
      Object.values(groups).forEach(g=>{
        const collapseId = 'modAcc_'+(g.module_uuid || g.module_id || idx);
        const headerId   = collapseId+'_h';

        const card = document.createElement('div');
        card.className = 'accordion-item';

        card.innerHTML = `
          <h2 class="accordion-header" id="${headerId}">
            <button class="accordion-button ${idx ? 'collapsed' : ''}" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#${collapseId}"
                    aria-expanded="${idx ? 'false':'true'}"
                    aria-controls="${collapseId}">
              <span class="fw-semibold">${esc(g.module_name)}</span>
              <span class="priv-module-pill ms-2">
                <i class="fa fa-shield-halved me-1"></i>${g.items.length} privilege(s)
              </span>
            </button>
          </h2>
          <div id="${collapseId}" class="accordion-collapse collapse ${idx ? '' : 'show'}"
               aria-labelledby="${headerId}">
            <div class="accordion-body">
              <div class="priv-add-wrap">
                <div class="flex-grow-1 d-flex gap-2">
                  <select class="form-select form-select-sm acc-add-action"></select>
                  <input type="text" class="form-control form-control-sm acc-add-other d-none" placeholder="Custom action">
                </div>
                <button type="button" class="btn btn-primary acc-add-btn">
                  <i class="fa fa-plus me-1"></i>Add
                </button>
              </div>
              <div class="list-group priv-group-rows"></div>
            </div>
          </div>
        `;

        const list       = card.querySelector('.priv-group-rows');
        const select     = card.querySelector('.acc-add-action');
        const otherInput = card.querySelector('.acc-add-other');
        const addBtn     = card.querySelector('.acc-add-btn');

        // ✅ IMPORTANT: backend expects dashboard_menu_id
        const moduleIdentifier = g.module_uuid || g.module_id;

        const existingActions = new Set(
          g.items.map(it => (it.action || '').toLowerCase())
        );

        let optionsHtml = '<option value="">Select action…</option>';
        DEFAULT_ACTIONS.forEach(a=>{
          if(!existingActions.has(a)){
            const label = a.charAt(0).toUpperCase() + a.slice(1);
            optionsHtml += `<option value="${a}">${label}</option>`;
          }
        });
        optionsHtml += '<option value="__other">Other…</option>';
        select.innerHTML = optionsHtml;

        select.addEventListener('change', ()=>{
          const v = select.value;
          if(v === '__other'){
            otherInput.classList.remove('d-none');
            otherInput.focus();
          } else {
            otherInput.classList.add('d-none');
            otherInput.value = '';
          }
        });

        addBtn.addEventListener('click', async ()=>{
          const selVal = select.value;
          if(!selVal){
            return Swal.fire('Action required','Please select an action.','info');
          }

          let actionStr = '';
          if(selVal === '__other'){
            const txt = (otherInput.value || '').trim();
            if(!txt){
              return Swal.fire('Custom action','Please type a custom action name.','info');
            }
            actionStr = txt;
          } else {
            actionStr = selVal;
          }

          if(!moduleIdentifier){
            return err('Module identifier missing for this group');
          }

          const { value: apiVal } = await Swal.fire({
            title: 'API endpoint / route',
            input: 'text',
            inputLabel: 'Required (optional prefix: GET /api/...)',
            inputPlaceholder: 'e.g. GET /api/users or users.index',
            inputAttributes: { maxlength: 255 },
            showCancelButton: true,
            inputValidator: (value)=>{
              if(!value || !value.trim()) return 'API endpoint / route is required';
            }
          });
          if(!apiVal) return;

          const parsed = parseApiInput(apiVal.trim());
          if(!parsed.api){
            return Swal.fire('API required','Please enter a valid API endpoint / route.','info');
          }

          addBtn.disabled = true;
          try{
            const payload = {
              dashboard_menu_id: moduleIdentifier,      // ✅ FIX
              action: actionStr,
              description: null,
              assigned_apis: [parsed.api],              // ✅ FIX
              meta: parsed.method ? { http_method: parsed.method } : null
            };

            const res = await fetch('/api/privileges', {
              method:'POST',
              headers:{
                'Authorization':'Bearer '+TOKEN,
                'Content-Type':'application/json',
                'Accept':'application/json'
              },
              body: JSON.stringify(payload)
            });
            const j = await res.json().catch(()=>({}));
            if(!res.ok) throw new Error(j?.message || 'Create failed');
            ok('Privilege added');
            load('active');
          }catch(e){
            err(e.message || 'Create failed');
          }finally{
            addBtn.disabled = false;
          }
        });

        // render privileges in this module
        g.items.forEach(r=>{
          const isBuiltIn = builtInSet.has((r.action || '').toLowerCase());
          const row = document.createElement('div');
          row.className = 'list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2';
          row.dataset.key = r.uuid || r.id;

          const statusRaw = (r.status || 'active').toString();
          const statusLabel = statusRaw.charAt(0).toUpperCase() + statusRaw.slice(1);

          const { api, method } = pickApiAndMethodFromRecord(r);
          const apiLine = api
            ? `<div class="small text-muted mt-1"><i class="fa fa-code me-1 opacity-75"></i>${esc((method ? '['+method+'] ' : '') + api)}</div>`
            : '';

          row.innerHTML = `
            <div class="flex-grow-1">
              <div class="fw-semibold mb-1">
                ${esc(r.action || '-')}
                ${isBuiltIn ? '<span class="badge bg-secondary ms-1">Core</span>' : ''}
                ${statusLabel ? `<span class="badge bg-light text-muted ms-1">${esc(statusLabel)}</span>` : ''}
              </div>
              <div class="small text-muted">
                ${r.description ? esc(r.description) : '<span class="text-muted">No description</span>'}
              </div>
              ${apiLine}
            </div>
            <div class="d-flex flex-shrink-0 gap-1">
              <button type="button" class="btn btn-light btn-sm" data-act="edit" data-key="${esc(r.uuid||r.id)}" data-action="${esc(r.action||'')}">
                <i class="fa fa-pen"></i>
              </button>
              ${
                String(r.status||'').toLowerCase()==='archived'
                ? `<button type="button" class="btn btn-light btn-sm" data-act="unarchive" data-key="${esc(r.uuid||r.id)}" data-action="${esc(r.action||'')}"><i class="fa fa-box-open"></i></button>`
                : `<button type="button" class="btn btn-light btn-sm" data-act="archive"   data-key="${esc(r.uuid||r.id)}" data-action="${esc(r.action||'')}"><i class="fa fa-box-archive"></i></button>`
              }
              <button type="button" class="btn btn-light btn-sm text-danger" data-act="delete" data-key="${esc(r.uuid||r.id)}" data-action="${esc(r.action||'')}">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          `;

          list.appendChild(row);
        });

        accordionActive.appendChild(card);
        idx++;
      });

      // pagination/meta
      const total=Number(pagination.total||0);
      const per=Number(pagination.per_page||30);
      const cur=Number(pagination.page||1);
      const pages=Math.max(1, Math.ceil(total/per));

      const li=(dis,act,label,t)=>
        `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
           <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
         </li>`;

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
          if(!t || t===state.active.page) return;
          state.active.page = Math.max(1,t);
          load('active');
          window.scrollTo({top:0,behavior:'smooth'});
        });
      });

      meta.textContent = `Showing page ${pagination.page} of ${pages} — ${total} result(s)`;
    }

    function load(scope){
      const empty = qs(tabs[scope].empty);
      const pager = qs(tabs[scope].pager);
      const meta  = qs(tabs[scope].meta);

      if(scope==='active'){
        accordionActive && (accordionActive.innerHTML = '');
      }else{
        clearBody(scope);
      }

      show(empty,false);
      pager && (pager.innerHTML='');
      meta  && (meta.textContent='—');
      showLoader(scope,true);

      fetch(urlFor(scope), {
        headers:{
          'Authorization':'Bearer '+TOKEN,
          'Accept':'application/json',
          'Cache-Control':'no-cache'
        }
      })
      .then(r=>r.json().then(j=>({ok:r.ok,j})))
      .then(({ok,j})=>{
        if(!ok) throw new Error(j?.message || 'Load failed');

        const items =
          j.data
          || j.privileges
          || (Array.isArray(j) ? j : (j.privilege ? [j.privilege] : []));

        const pag =
          j.pagination
          || j.meta
          || j.links
          || { page: 1, per_page: items.length || 30, total: items.length || 0 };

        if(scope==='active'){
          renderActiveAccordion(items, pag);
          return;
        }

        const rowsEl=qs(tabs[scope].rows);
        if(!rowsEl) return;
        if(items.length===0) show(empty,true);
        const frag=document.createDocumentFragment();
        items.forEach(r=> frag.appendChild(rowHTML(scope, r)));
        rowsEl.appendChild(frag);

        const total=Number(pag.total||0), per=Number(pag.per_page||30), cur=Number(pag.page||1);
        const pages=Math.max(1, Math.ceil(total/per));

        const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
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
            if(!t || t===state[scope].page) return;
            state[scope].page = Math.max(1,t);
            load(scope);
            window.scrollTo({top:0,behavior:'smooth'});
          });
        });
        meta.textContent = `Showing page ${pag.page} of ${pages} — ${total} result(s)`;
      })
      .catch(e=>{
        console.error(e);
        show(empty,true);
        meta && (meta.textContent='Failed to load');
        err(e.message||'Load error');
      })
      .finally(()=> showLoader(scope,false));
    }

    syncSortHeaders();

    let srT;
    if(q) q.addEventListener('input', ()=>{
      clearTimeout(srT);
      srT=setTimeout(()=>{
        state.active.page=1;
        load('active');
      }, 350);
    });

    if(btnReset){
      btnReset.addEventListener('click', ()=>{
        if(q) q.value='';
        if(perPageSel) perPageSel.value='30';
        sort='-created_at';
        state.active.page=1;
        syncSortHeaders();
        load('active');
      });
    }

    if(perPageSel) perPageSel.addEventListener('change', ()=>{
      state.active.page=1;
      load('active');
    });

    // Tab events
    document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', ()=>{ load('active'); });
    document.querySelector('a[href="#tab-archived"]')?.addEventListener('shown.bs.tab', ()=>{ load('archived'); });
    document.querySelector('a[href="#tab-bin"]')?.addEventListener('shown.bs.tab', ()=>{ load('bin'); });

    // ✅ redirect to create page (kept)
    btnCreatePriv && btnCreatePriv.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      window.location.href = '/page-privilege/create';
    });

    btnBulkPriv && btnBulkPriv.addEventListener('click', async ()=>{
      await openBulkCreate();
    });

    // Delegated actions (edit/archive/unarchive/delete/restore/force)
    document.addEventListener('click', async (e)=>{
      const target = e.target.closest('[data-act]');
      if(!target) return;

      const act = target.dataset.act;
      const key = target.dataset.key;
      const actionName = target.dataset.action || 'this privilege';

      /* ============================================================
         ✅ EDIT: Redirect to /page-privilege/create and pass identifier
         - Query param "edit" (identifier can be id or uuid)
         - Also store in sessionStorage for extra reliability
         ============================================================ */
      if(act==='edit'){
        try { sessionStorage.setItem('privilege_edit_key', String(key || '')); } catch(e){}
        const usp = new URLSearchParams();
        usp.set('edit', String(key || ''));
        window.location.href = '/page-privilege/create?' + usp.toString();
        return;
      }

      if(act==='archive'){
        const {isConfirmed}=await Swal.fire({
          icon:'question',
          title:'Archive privilege?',
          html:`“${esc(actionName)}”`,
          showCancelButton:true,
          confirmButtonText:'Archive'
        });
        if(!isConfirmed) return;
        try{
          const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/archive`,{
            method:'POST',
            headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
          });
          const j=await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(j?.message||'Archive failed');
          ok('Privilege archived');
          load('active');
          load('archived');
        }catch(e){ err(e.message||'Archive failed'); }
        return;
      }

      if(act==='unarchive'){
        const {isConfirmed}=await Swal.fire({
          icon:'question',
          title:'Unarchive privilege?',
          html:`“${esc(actionName)}”`,
          showCancelButton:true,
          confirmButtonText:'Unarchive'
        });
        if(!isConfirmed) return;
        try{
          const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/unarchive`,{
            method:'POST',
            headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
          });
          const j=await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(j?.message||'Unarchive failed');
          ok('Privilege unarchived');
          load('active');
          load('archived');
        }catch(e){ err(e.message||'Unarchive failed'); }
        return;
      }

      if(act==='delete'){
        const {isConfirmed}=await Swal.fire({
          icon:'warning',
          title:'Delete (soft)?',
          html:`This moves “${esc(actionName)}” to Bin.`,
          showCancelButton:true,
          confirmButtonText:'Delete',
          confirmButtonColor:'#ef4444'
        });
        if(!isConfirmed) return;
        try{
          const res=await fetch(`/api/privileges/${encodeURIComponent(key)}`,{
            method:'DELETE',
            headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
          });
          const j=await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(j?.message||'Delete failed');
          ok('Moved to Bin');
          load('active');
          load('bin');
        }catch(e){ err(e.message||'Delete failed'); }
        return;
      }

      if(act==='restore'){
        const {isConfirmed}=await Swal.fire({
          icon:'question',
          title:'Restore privilege?',
          html:`“${esc(actionName)}” will be restored.`,
          showCancelButton:true,
          confirmButtonText:'Restore'
        });
        if(!isConfirmed) return;
        try{
          const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/restore`,{
            method:'POST',
            headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
          });
          const j=await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(j?.message||'Restore failed');
          ok('Privilege restored');
          load('bin');
          load('active');
        }catch(e){ err(e.message||'Restore failed'); }
        return;
      }

      if(act==='force'){
        const {isConfirmed}=await Swal.fire({
          icon:'warning',
          title:'Delete permanently?',
          html:`This cannot be undone.<br>“${esc(actionName)}”`,
          showCancelButton:true,
          confirmButtonText:'Delete permanently',
          confirmButtonColor:'#dc2626'
        });
        if(!isConfirmed) return;
        try{
          const res=await fetch(`/api/privileges/${encodeURIComponent(key)}/force`,{
            method:'DELETE',
            headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
          });
          const j=await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(j?.message||'Force delete failed');
          ok('Permanently deleted');
          load('bin');
        }catch(e){ err(e.message||'Force delete failed'); }
        return;
      }
    });

    // Reorder buttons (kept; still table-based if you extend later)
    btnReorder && btnReorder.addEventListener('click', ()=>{
      reorderMode = !reorderMode;
      btnReorder.classList.toggle('btn-primary', reorderMode);
      btnReorder.classList.toggle('btn-light', !reorderMode);
      btnReorder.innerHTML = reorderMode
        ? '<i class="fa fa-check-double me-1"></i>Reorder On'
        : '<i class="fa fa-up-down-left-right me-1"></i>Reorder';
      document.getElementById('reorderHint').style.display = reorderMode ? '' : 'none';
      document.getElementById('btnSaveOrder').style.display = reorderMode ? '' : 'none';
      document.getElementById('btnCancelOrder').style.display = reorderMode ? '' : 'none';
      load('active');
    });

    document.getElementById('btnCancelOrder')?.addEventListener('click', ()=>{
      reorderMode=false;
      btnReorder.classList.remove('btn-primary');
      btnReorder.classList.add('btn-light');
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
        const res = await fetch('/api/privileges/reorder', {
          method: 'POST',
          headers: {
            'Authorization': 'Bearer ' + TOKEN,
            'Content-Type' : 'application/json',
            'Accept' : 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const j = await res.json().catch(()=>({}));
        if (!res.ok) throw new Error(j?.message || 'Reorder failed');
        ok('Order updated');
        document.getElementById('btnCancelOrder').click();
      }catch(e){
        err(e.message || 'Reorder failed');
      }
    });

    /* Bulk modal open + save (FIXED payload fields) */
    bulk.addBtn && bulk.addBtn.addEventListener('click', ()=>{
      bulk.rowsContainer.appendChild(createBulkRow());
    });

    bulk.saveBtn && bulk.saveBtn.addEventListener('click', async ()=>{
      const moduleVal = bulk.moduleSelect && bulk.moduleSelect.value ? bulk.moduleSelect.value : null;
      const rows = [...bulk.rowsContainer.querySelectorAll('.priv-row')];
      const currentIds = new Set(rows.map(r => r.dataset.privId).filter(Boolean).map(String));
      const deletedIds = [..._originalPrivIds].filter(id => !currentIds.has(id));

      const payloads = rows.map(r=>{
        const actionEl=r.querySelector('.priv-action');
        const descEl=r.querySelector('.priv-desc');
        const apiEl=r.querySelector('.priv-api');
        const modEl=r.querySelector('.priv-module');

        return {
          id: r.dataset.privId ? r.dataset.privId : null,
          action: actionEl ? actionEl.value.trim() : '',
          description: descEl ? descEl.value.trim() : '',
          api_raw: apiEl ? apiEl.value.trim() : '',
          dashboard_menu_id: modEl && modEl.value ? modEl.value : moduleVal
        };
      }).filter(p=>p.action && p.action.length>0);

      if(!payloads.length && !deletedIds.length){
        return Swal.fire('No changes','Nothing to save or delete','info');
      }

      const missingApi = payloads.some(p => !p.api_raw);
      if(missingApi){
        return Swal.fire('API required','Every privilege row must have an API endpoint / route.','info');
      }

      const {isConfirmed} = await Swal.fire({
        icon:'question',
        title:'Apply privilege changes?',
        html:`This will create/update ${payloads.length} privilege(s) and delete ${deletedIds.length} removed privilege(s).`,
        showCancelButton:true,
        confirmButtonText:'Proceed'
      });
      if(!isConfirmed) return;

      bulk.saveBtn.disabled = true;
      try{
        // delete removed
        const deleteOps = deletedIds.map(id =>
          fetch(`/api/privileges/${encodeURIComponent(id)}`, {
            method: 'DELETE',
            headers: {
              'Authorization': 'Bearer ' + TOKEN,
              'Accept': 'application/json'
            }
          }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err }))
        );
        const deleteSettled = await Promise.allSettled(deleteOps);
        let deleted = 0, deleteFailed = 0;
        for(let i=0;i<deleteSettled.length;i++){
          const s = deleteSettled[i];
          if(s.status === 'fulfilled'){
            const r = s.value;
            if(r.err || !r.res || !r.res.ok) { deleteFailed++; } else deleted++;
          } else deleteFailed++;
        }

        const ops = payloads.map(p => {
          const parsed = parseApiInput(p.api_raw);
          const apiFinal = (parsed.api || '').trim();
          if(!apiFinal){
            return Promise.resolve({ err: new Error('API missing') });
          }

          const body = {
            dashboard_menu_id: p.dashboard_menu_id,           // ✅ FIX
            action: p.action,
            description: p.description || null,
            assigned_apis: [apiFinal],                        // ✅ FIX
            meta: parsed.method ? { http_method: parsed.method } : null
          };

          if(p.id){
            const url = `/api/privileges/${encodeURIComponent(p.id)}`;
            return fetch(url, {
              method: 'PATCH',
              headers: {
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify(body)
            }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err }));
          } else {
            const url = `/api/privileges`;
            return fetch(url, {
              method: 'POST',
              headers: {
                'Authorization': 'Bearer ' + TOKEN,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify(body)
            }).then(async res => ({ res, j: await res.json().catch(()=>({})) })).catch(err => ({ err }));
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

            if(!input.id && body){
              const newId = body.id || body.uuid || body.data?.id || body.data?.uuid;
              if(newId){
                const matchRow = [...bulk.rowsContainer.querySelectorAll('.priv-row')].find(r=>{
                  const a=r.querySelector('.priv-action')?.value?.trim();
                  const d=r.querySelector('.priv-desc')?.value?.trim();
                  const api=r.querySelector('.priv-api')?.value?.trim();
                  return a===input.action && d===(input.description||'') && api===input.api_raw && !r.dataset.privId;
                });
                if(matchRow) matchRow.dataset.privId = String(newId);
              }
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

        if(failed === 0 && deleteFailed === 0){
          bulk.modal.hide();
        }else{
          err(`${(failed+deleteFailed)} operation(s) failed — check rows and try again`);
        }

        await loadModulesForBulk();
        if(bulk.moduleSelect.value) await loadBulkPrivilegesForModule(bulk.moduleSelect.value);
        load('active');

      }catch(e){
        console.error(e);
        err(e.message || 'Failed to create/update privileges');
      }finally{
        bulk.saveBtn.disabled = false;
      }
    });

    // Initial load
    load('active');
  })();
})();
</script>
@endpush
