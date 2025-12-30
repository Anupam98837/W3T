{{-- resources/views/users/assignPrivileges.blade.php --}}
@extends('pages.users.layout.structure')

@section('title','Assign Privileges')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Assign Privileges (polished shell) ===== */
.ap-wrap{max-width:1140px;margin:16px auto 40px}

/* Profile mini */
.ap-profile{
  display:flex;gap:12px;align-items:center;
  padding:12px 14px;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:linear-gradient(180deg, var(--background-soft), transparent);
  margin:10px 0 12px;
}
.ap-avatar{
  width:48px;height:48px;border-radius:999px;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg, rgba(158,54,58,.18), rgba(201,75,80,.10));
  border:1px solid var(--line-soft);
  color:var(--primary-color);
  font-weight:900;
  flex:0 0 auto;
}
.ap-profile-meta{display:flex;flex-direction:column;min-width:0}
.ap-profile-name{
  font-weight:900;color:var(--ink);line-height:1.15;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:520px;
}
.ap-profile-sub{
  font-size:0.84rem;color:var(--muted-color);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:640px;
}
.ap-profile-badges{display:flex;flex-wrap:wrap;gap:6px;margin-top:6px}
.ap-badge{
  display:inline-flex;align-items:center;gap:6px;
  border:1px solid var(--line-soft);
  background:var(--surface);
  color:var(--muted-color);
  border-radius:999px;
  padding:3px 10px;
  font-size:0.78rem;
}
.ap-badge b{color:var(--ink);font-weight:800}

/* Main card */
.ap-card{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:18px;
  box-shadow:var(--shadow-2);
  margin-top:10px;
  overflow:visible;
}
.ap-card-head{
  padding:12px 14px;
  border-bottom:1px solid var(--line-strong);
  display:flex;flex-wrap:wrap;gap:10px;
  align-items:center;justify-content:space-between;
}
.ap-card-head-left{display:flex;flex-direction:column;gap:2px;min-width:260px}
.ap-card-head-title{font-weight:800}
.ap-card-head-sub{font-size:0.82rem;color:var(--muted-color)}
.ap-card-head-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.ap-card-body{padding:14px}

/* Top tools */
.ap-tools{
  display:flex;align-items:center;gap:8px;flex-wrap:wrap;
  margin-bottom:10px;
}
.ap-tools .input-group{max-width:520px;min-width:240px}
.ap-tools .input-group-text{background:var(--background-soft);border-color:var(--line-strong);color:var(--muted-color)}
.ap-tools .form-control{border-color:var(--line-strong)}
.ap-tools .form-control:focus{box-shadow:0 0 0 2px rgba(158,54,58,.16);border-color:rgba(158,54,58,.55)}
.ap-kpi{
  margin-left:auto;
  display:flex;gap:8px;flex-wrap:wrap;align-items:center;justify-content:flex-end;
}
.ap-kpi .ap-chip{
  display:inline-flex;align-items:center;gap:8px;
  border:1px solid var(--line-soft);
  background:var(--background-soft);
  color:var(--muted-color);
  border-radius:999px;
  padding:6px 10px;
  font-size:0.82rem;
}
.ap-kpi .ap-chip b{color:var(--ink);font-weight:900}

/* Dropdown (must be able to dropup too) */
.ap-dd{position:relative}
.ap-dd .dd-toggle{border-radius:12px}
.ap-dd .dropdown-menu{
  border-radius:14px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:240px;
  z-index:6000;
}
.ap-dd .dropdown-menu.show{display:block !important}
.ap-dd .dropdown-item{display:flex;align-items:center;gap:.65rem}
.ap-dd .dropdown-item i{width:16px;text-align:center}
.ap-dd .dropdown-item.text-danger{color:var(--danger-color) !important}

/* Bootstrap accordion styling */
.ap-accordion .accordion-item{
  border-radius:14px;
  border:1px solid var(--line-soft);
  margin-bottom:10px;
  background:var(--surface);
  overflow:visible; /* ✅ important for dropdowns and dropup */
}
.ap-accordion .accordion-header{position:relative}
.ap-accordion .accordion-button{
  display:flex;align-items:center;
  padding:10px 14px;gap:10px;
  font-weight:800;
  background:var(--background-soft);
  color:var(--ink);
}
.ap-accordion .accordion-button:not(.collapsed){
  background:var(--surface);
  box-shadow:none;
}
.ap-accordion .accordion-button:focus{box-shadow:0 0 0 2px rgba(158,54,58,.18)}
.ap-accordion .accordion-body{overflow:visible}

.ap-module-header-inner{display:flex;align-items:center;justify-content:space-between;width:100%;gap:10px}
.ap-module-title{font-weight:900;font-size:0.98rem}
.ap-module-pill{
  font-size:0.78rem;border-radius:999px;padding:4px 10px;
  background:var(--background-soft);color:var(--muted-color);
  border:1px solid var(--line-soft);
  display:inline-flex;align-items:center;gap:6px;
  white-space:nowrap;
}
.ap-module-pill b{color:var(--ink);font-weight:900}

/* Module tools */
.ap-module-tools{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  margin-bottom:8px;flex-wrap:wrap;
}
.ap-module-tools-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.ap-module-tools-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* Privilege table */
.ap-module-body{padding:2px 0}
.ap-priv-table-wrap{margin-top:6px}
.ap-priv-table{width:100%;border-collapse:collapse;font-size:0.88rem}
.ap-priv-table thead th{
  background:var(--background-soft);
  color:var(--muted-color);
  font-weight:800;
  border-bottom:1px solid var(--line-soft);
  padding:8px 10px;
}
.ap-priv-table tbody td{
  border-top:1px solid var(--line-soft);
  padding:8px 10px;
  vertical-align:middle;
}
.ap-priv-row{transition:background .15s ease, box-shadow .15s ease, border-color .15s ease}
.ap-priv-row:hover{background:var(--background-hover);box-shadow:0 3px 10px rgba(0,0,0,0.03)}
.ap-priv-row.active{background:var(--background-soft);box-shadow:0 0 0 2px rgba(201,75,80,.10)}

/* Privilege text */
.ap-priv-title{font-size:0.94rem;font-weight:900;color:var(--ink);margin-bottom:2px}
.ap-priv-desc{font-size:0.82rem;color:var(--muted-color)}

/* Checkbox */
.ap-check{width:18px;height:18px;accent-color:var(--primary-color);cursor:pointer}

/* Empty State */
.ap-empty{padding:28px;text-align:center;color:var(--muted-color)}
.ap-empty i{font-size:2rem;margin-bottom:12px;opacity:.55}

/* Small helper */
.ap-small-muted{font-size:13px;color:var(--muted-color)}

/* Responsive */
@media (max-width: 576px){
  .ap-card-head{align-items:flex-start}
  .ap-card-head-right{width:100%;justify-content:flex-start}
  .ap-tools .input-group{min-width:100%;max-width:100%}
  .ap-kpi{width:100%;justify-content:flex-start;margin-left:0}
  .ap-priv-table thead{display:none}
  .ap-priv-table tbody td{
    display:block;width:100%;
    border-top:none;border-bottom:1px solid var(--line-soft);
  }
  .ap-priv-table tbody tr:last-child td{border-bottom:none}
  .ap-priv-table tbody td:first-child{padding-top:10px}
  .ap-priv-table tbody td:last-child{padding-bottom:10px}
  .ap-profile-name{max-width:240px}
  .ap-profile-sub{max-width:260px}
}
</style>
@endpush

@section('content')
<div class="ap-wrap">
  {{-- Header Panel --}}
  <div class="row g-2 mb-2 align-items-center panel">
    <div class="col-12 col-lg">
      <h4 class="mb-2">Manage User Privileges</h4>
      <div id="userSummary" class="ap-small-muted mt-2" style="display:none;"></div>
    </div>

    <div class="col-12 col-lg-auto d-flex flex-wrap gap-2 justify-content-lg-end">
      <a href="javascript:history.back()" class="btn btn-light">
        <i class="fa fa-arrow-left me-1"></i>Back
      </a>
      <button id="btnRefresh" class="btn btn-light">
        <i class="fa fa-rotate-right me-1"></i>Refresh
      </button>
      <button id="btnSaveAll" class="btn btn-primary">
        <i class="fa fa-save me-1"></i>Save
      </button>
    </div>
  </div>

  {{-- Mini profile --}}
  <div class="ap-profile">
    <div id="apAvatar" class="ap-avatar">U</div>
    <div class="ap-profile-meta">
      <div id="apProfileName" class="ap-profile-name">Loading user…</div>
      <div id="apProfileSub" class="ap-profile-sub">Please wait</div>
      <div id="apProfileBadges" class="ap-profile-badges" style="display:none;"></div>
    </div>
  </div>

  {{-- Main card --}}
  <div class="ap-card">
    <div class="ap-card-head">
      <div class="ap-card-head-left">
        <span class="ap-card-head-title">Modules &amp; Privileges</span>
        <span class="ap-card-head-sub">
          Tick the privileges you want this user to have, then click <strong>Save</strong>.
        </span>
      </div>

      <div class="ap-card-head-right">
        <label class="form-check mb-0 d-flex align-items-center gap-2">
          <input class="form-check-input" type="checkbox" id="chkGlobalSelectAll">
          <span class="ap-small-muted">Select all</span>
        </label>

        {{-- ✅ actions dropdown (supports dropup) --}}
        <div class="dropdown ap-dd">
          <button class="btn btn-light dd-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <button class="dropdown-item" type="button" data-ap-action="expandAll">
                <i class="fa fa-square-plus"></i> Expand all modules
              </button>
            </li>
            <li>
              <button class="dropdown-item" type="button" data-ap-action="collapseAll">
                <i class="fa fa-square-minus"></i> Collapse all modules
              </button>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <button class="dropdown-item" type="button" data-ap-action="selectAll">
                <i class="fa fa-check-double"></i> Select all privileges
              </button>
            </li>
            <li>
              <button class="dropdown-item text-danger" type="button" data-ap-action="clearAll">
                <i class="fa fa-trash"></i> Clear all selections
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="ap-card-body">
      {{-- ✅ search + KPIs --}}
      <div class="ap-tools">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="fa fa-magnifying-glass"></i></span>
          <input id="txtSearch" type="text" class="form-control" placeholder="Search privilege (e.g., create, edit, view)…">
          <button id="btnClearSearch" class="btn btn-light" type="button" title="Clear search">
            <i class="fa fa-xmark"></i>
          </button>
        </div>

        <div class="ap-kpi">
          <span class="ap-chip"><i class="fa fa-list-check"></i> Selected: <b id="kpiSelected">0</b></span>
          <span class="ap-chip"><i class="fa fa-shield-halved"></i> Total: <b id="kpiTotal">0</b></span>
        </div>
      </div>

      <div id="modulesContainer" class="accordion ap-accordion">
        {{-- JS will inject accordion items --}}
      </div>

      <div id="modulesEmpty" class="ap-empty" style="display:none;">
        <i class="fa fa-folder-open"></i>
        <div>No modules or privileges found.</div>
      </div>
    </div>
  </div>

  <div class="ap-small-muted mt-3">
    Tip: Use <b>Select all</b> at the top for every privilege, or the per-module <b>Select all</b> to control one module.
    Changes are only saved after you click <strong>Save</strong>.
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1200">
  <div id="toastOk" class="toast text-bg-success border-0">
    <div class="d-flex">
      <div id="toastOkMsg" class="toast-body">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastErr" class="toast text-bg-danger border-0 mt-2">
    <div class="d-flex">
      <div id="toastErrMsg" class="toast-body">Something went wrong</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', ()=> {

  const params        = new URLSearchParams(location.search);
  const userUuidParam = params.get('user_uuid') || params.get('uuid') || '';
  const userIdParam   = params.get('user_id')   || params.get('id')   || '';

  const token = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!token){
    alert('Login required. Redirecting to home.');
    location.href='/';
    return;
  }

  const authHeaders = (extra={}) =>
    Object.assign({'Authorization':'Bearer '+token, 'Accept':'application/json'}, extra);

  const toastOk  = new bootstrap.Toast(document.getElementById('toastOk'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastErr'));
  const ok  = (m='Done') => { document.getElementById('toastOkMsg').textContent = m; toastOk.show(); };
  const err = (m='Something went wrong') => { document.getElementById('toastErrMsg').textContent = m; toastErr.show(); };

  const modulesContainer   = document.getElementById('modulesContainer');
  const modulesEmpty       = document.getElementById('modulesEmpty');
  const btnSaveAll         = document.getElementById('btnSaveAll');
  const btnRefresh         = document.getElementById('btnRefresh');
  const chkGlobalSelectAll = document.getElementById('chkGlobalSelectAll');

  const txtSearch      = document.getElementById('txtSearch');
  const btnClearSearch = document.getElementById('btnClearSearch');
  const kpiSelected    = document.getElementById('kpiSelected');
  const kpiTotal       = document.getElementById('kpiTotal');

  // profile nodes
  const apAvatar        = document.getElementById('apAvatar');
  const apProfileName   = document.getElementById('apProfileName');
  const apProfileSub    = document.getElementById('apProfileSub');
  const apProfileBadges = document.getElementById('apProfileBadges');
  const userSummary     = document.getElementById('userSummary');

  let modules          = [];
  let assignedPrivIds  = new Set();
  let isSaving         = false;
  let resolvedUserId   = null;
  let resolvedUserUuid = null;

  function escapeHtml(s){
    return (s||'').toString().replace(/[&<>"'`]/g, ch => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'
    }[ch]));
  }

  function initials(nameOrEmail){
    const s = (nameOrEmail || '').trim();
    if(!s) return 'U';
    const parts = s.split(/\s+/).filter(Boolean);
    if(parts.length === 1) return parts[0].slice(0,2).toUpperCase();
    return (parts[0][0] + parts[parts.length-1][0]).toUpperCase();
  }

  function renderProfile(u){
    const displayName = u.name || u.full_name || u.username || (u.email ? u.email.split('@')[0] : '') || ('User #'+(u.id||''));
    const email = u.email || '';
    const role  = u.role || u.user_role || u.type || '';

    if (apAvatar) apAvatar.textContent = initials(displayName || email);
    if (apProfileName) apProfileName.textContent = displayName || 'User';
    if (apProfileSub) apProfileSub.textContent  = email ? email : (u.uuid ? ('UUID: ' + u.uuid) : '—');

    const badges = [];
    if (u.uuid) badges.push(`<span class="ap-badge"><i class="fa fa-fingerprint"></i><b>UUID</b> ${escapeHtml(u.uuid)}</span>`);
    if (role)   badges.push(`<span class="ap-badge"><i class="fa fa-user-shield"></i><b>Role</b> ${escapeHtml(role)}</span>`);

    if (apProfileBadges){
      apProfileBadges.innerHTML = badges.join('');
      apProfileBadges.style.display = badges.length ? '' : 'none';
    }
  }

  function updateKPIs(){
    const total = modulesContainer.querySelectorAll('.ap-priv-row').length;
    const selected = assignedPrivIds.size;
    if (kpiTotal) kpiTotal.textContent = String(total);
    if (kpiSelected) kpiSelected.textContent = String(selected);

    if (userSummary){
      userSummary.style.display = '';
      userSummary.innerHTML = `
        <i class="fa fa-circle-info me-1"></i>
        Selected <b>${selected}</b> of <b>${total}</b> privileges
      `;
    }
  }

  // ==========================================================
  // ✅ Dropdown dropup support (auto decide before opening)
  // ==========================================================
  document.addEventListener('show.bs.dropdown', (ev) => {
    const dd = ev.target; // .dropdown
    if (!dd || !dd.classList || !dd.classList.contains('dropdown')) return;

    const toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
    const menu   = dd.querySelector('.dropdown-menu');
    if (!toggle || !menu) return;

    // measure menu height even when hidden
    const prevDisplay = menu.style.display;
    const prevVis     = menu.style.visibility;
    const prevPos     = menu.style.position;

    menu.style.visibility = 'hidden';
    menu.style.display    = 'block';
    menu.style.position   = 'absolute';
    const menuHeight      = menu.getBoundingClientRect().height || menu.offsetHeight || 220;

    // revert
    menu.style.display    = prevDisplay;
    menu.style.visibility = prevVis;
    menu.style.position   = prevPos;

    const rect = toggle.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    // if not enough below, prefer dropup when it fits better
    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) dd.classList.add('dropup');
    else dd.classList.remove('dropup');
  });

  // ========== User resolution ==========
  async function resolveUserIdentity(){
    if (userIdParam){
      resolvedUserId = Number(userIdParam);
      return;
    }
    if (userUuidParam){
      resolvedUserUuid = userUuidParam;
      try{
        const r = await fetch(`/api/user/${encodeURIComponent(userUuidParam)}`, { headers: authHeaders() });
        if (r.ok){
          const js = await r.json().catch(()=>({}));
          const u  = js.user || js.data || {};
          if (u && u.id) resolvedUserId = Number(u.id);
        }
      }catch(e){}
      return;
    }
    throw new Error('Could not resolve user from provided parameters (user_id/user_uuid).');
  }

  async function loadUserInfo(){
    try{
      if (resolvedUserId){
        const res = await fetch(`/api/user/${encodeURIComponent(resolvedUserId)}`, { headers: authHeaders() });
        const js  = await res.json().catch(()=>({}));
        if(res.ok && (js.user || js.data)){
          const u = js.user || js.data;
          resolvedUserUuid = resolvedUserUuid || (u.uuid || u.user_uuid);
          renderProfile(u);
          return;
        }
      }
      if (apProfileName) apProfileName.textContent = 'User information not available';
      if (apProfileSub) apProfileSub.textContent  = '—';
    }catch(e){
      if (apProfileName) apProfileName.textContent = 'Failed to load user';
      if (apProfileSub) apProfileSub.textContent  = '—';
    }
  }

  // ========== Privileges: load assigned ==========
  async function loadAssignedPrivileges(){
    assignedPrivIds = new Set();
    if (!resolvedUserId) return;

    try{
      const res = await fetch(`/api/user-privileges/list?user_id=${encodeURIComponent(resolvedUserId)}`, {
        headers: authHeaders()
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load assigned privileges');

      const ids = Array.isArray(js.flat_privilege_ids) ? js.flat_privilege_ids : [];
      ids.forEach(id => assignedPrivIds.add(String(id)));
    }catch(e){
      console.warn('Could not load assigned privileges', e);
    }
  }

  // ========== Select-all helpers ==========
  function updateModulePill(moduleEl, checkedCount, totalCount){
    const pillSel = moduleEl.querySelector('.ap-pill-selected');
    const pillTot = moduleEl.querySelector('.ap-pill-total');
    if (pillSel) pillSel.textContent = String(checkedCount);
    if (pillTot) pillTot.textContent = String(totalCount);
  }

  function updateModuleSelectAllState(moduleEl){
    if (!moduleEl) return;
    const moduleCheckbox = moduleEl.querySelector('.ap-mod-select-all');
    const rows = moduleEl.querySelectorAll('.ap-priv-row');

    // totals include subtree privileges (works for header groups too)
    const total = rows.length;

    let checkedCount = 0;
    rows.forEach(row=>{
      const cb = row.querySelector('.sm-privilege-checkbox');
      if (cb && cb.checked) checkedCount++;
    });

    // update pill
    updateModulePill(moduleEl, checkedCount, total);

    if (!moduleCheckbox) return;
    if (!total){
      moduleCheckbox.checked = false;
      moduleCheckbox.indeterminate = false;
      return;
    }

    if (checkedCount === 0){
      moduleCheckbox.checked = false;
      moduleCheckbox.indeterminate = false;
    } else if (checkedCount === total){
      moduleCheckbox.checked = true;
      moduleCheckbox.indeterminate = false;
    } else {
      moduleCheckbox.checked = false;
      moduleCheckbox.indeterminate = true;
    }
  }

  function updateAllModulesSelectAllState(){
    const moduleEls = modulesContainer.querySelectorAll('.accordion-item');
    moduleEls.forEach(updateModuleSelectAllState);
  }

  function updateGlobalSelectAllState(){
    const checkboxes = modulesContainer.querySelectorAll('.ap-priv-row .sm-privilege-checkbox');
    if (!checkboxes.length){
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = false;
      return;
    }

    let checkedCount = 0;
    checkboxes.forEach(cb=>{ if (cb.checked) checkedCount++; });

    if (checkedCount === 0){
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = false;
    } else if (checkedCount === checkboxes.length){
      chkGlobalSelectAll.checked = true;
      chkGlobalSelectAll.indeterminate = false;
    } else {
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = true;
    }
  }

  // ========== TREE helpers ==========
  function countPrivilegesInTree(node){
    let c = (node.privileges && node.privileges.length) ? node.privileges.length : 0;
    if (node.children && node.children.length){
      node.children.forEach(ch => { c += countPrivilegesInTree(ch); });
    }
    return c;
  }

  // ✅ accordion builder
  function buildAccordionItem(node, index, depth = 0, rootHeaderId = null){
    const item       = document.createElement('div');
    const collapseId = `ap_mod_${node.id ?? ('i'+index)}_${depth}_${index}`;
    const headerId   = `ap_modh_${node.id ?? ('i'+index)}_${depth}_${index}`;
    const moduleKey  = String(node.id ?? ('i'+index));

    const hasChildren      = !!(node.children && node.children.length);
    const hasOwnPrivileges = !!(node.privileges && node.privileges.length);

    // ✅ "header never has privileges" => don't show "No privileges" for header/groups
    // We'll hide the privilege table when there are children but no own privileges.
    const showPrivilegeTable = hasOwnPrivileges || !hasChildren;

    const privCount  = countPrivilegesInTree(node);

    item.className = 'accordion-item';
    item.dataset.moduleId = moduleKey;

    const myRootHeaderId = (depth === 0) ? (node.id ?? null) : rootHeaderId;

    item.innerHTML = `
      <h2 class="accordion-header" id="${headerId}">
        <button class="accordion-button ${index ? 'collapsed' : ''}" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#${collapseId}"
                aria-expanded="${index ? 'false':'true'}"
                aria-controls="${collapseId}">
          <div class="ap-module-header-inner">
            <span class="ap-module-title">
              ${escapeHtml(node.name || ('Module #'+(node.id || (index+1))))}
            </span>
            <span class="ap-module-pill">
              <i class="fa fa-shield-halved"></i>
              Selected <b class="ap-pill-selected">0</b>/<b class="ap-pill-total">${privCount}</b>
            </span>
          </div>
        </button>
      </h2>

      <div id="${collapseId}" class="accordion-collapse collapse ${index ? '' : 'show'}"
           aria-labelledby="${headerId}">
        <div class="accordion-body">
          <div class="ap-module-tools">
            <div class="ap-module-tools-left">
              <span class="ap-small-muted">Quick actions:</span>
            </div>

            <div class="ap-module-tools-right">
              <label class="form-check mb-0 d-flex align-items-center gap-2">
                <input class="form-check-input ap-mod-select-all" type="checkbox">
                <span class="ap-small-muted">Select all (this module)</span>
              </label>

              <div class="dropdown ap-dd">
                <button class="btn btn-light btn-sm dd-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-ellipsis"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><button class="dropdown-item" type="button" data-mod-action="expand"><i class="fa fa-square-plus"></i> Expand subtree</button></li>
                  <li><button class="dropdown-item" type="button" data-mod-action="collapse"><i class="fa fa-square-minus"></i> Collapse subtree</button></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><button class="dropdown-item" type="button" data-mod-action="selectAll"><i class="fa fa-check-double"></i> Select all privileges</button></li>
                  <li><button class="dropdown-item text-danger" type="button" data-mod-action="clear"><i class="fa fa-trash"></i> Clear privileges</button></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="ap-module-body">
            <div class="ap-priv-table-wrap" ${showPrivilegeTable ? '' : 'style="display:none"'} >
              <table class="ap-priv-table">
                <thead>
                  <tr>
                    <th style="width:70px" class="text-center">Select</th>
                    <th>Privilege</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="ap-child-accordion mt-2"></div>
          </div>
        </div>
      </div>
    `;

    const tbody           = item.querySelector('tbody');
    const moduleSelectAll = item.querySelector('.ap-mod-select-all');
    const tableWrap       = item.querySelector('.ap-priv-table-wrap');

    // Render privileges (only when table is shown)
    if (showPrivilegeTable){
      if(!hasOwnPrivileges){
        // leaf with no privileges only
        tbody.innerHTML = `
          <tr><td colspan="2">
            <div class="ap-empty ap-small-muted">
              <i class="fa fa-ban"></i>
              <div>No privileges for this module</div>
            </div>
          </td></tr>`;
      } else {
        node.privileges.forEach(p => {
          const pid     = String(p.id ?? '');
          const checked = assignedPrivIds.has(pid);

          const tr = document.createElement('tr');
          tr.className = 'ap-priv-row';

          tr.dataset.privId   = pid;
          tr.dataset.action   = String(p.action || p.name || '').toLowerCase();
          tr.dataset.pageId   = String(node.id ?? '0');
          tr.dataset.headerId = String(myRootHeaderId || '0');

          const title  = p.action || p.name || 'Untitled';
          const desc   = p.description || '';

          tr.innerHTML = `
            <td class="text-center">
              <input type="checkbox" class="ap-check sm-privilege-checkbox" ${checked ? 'checked':''}>
            </td>
            <td>
              <div class="ap-priv-title">${escapeHtml(title)}</div>
              ${desc ? `<div class="ap-priv-desc">${escapeHtml(desc)}</div>` : ''}
            </td>
          `;

          const checkbox = tr.querySelector('.sm-privilege-checkbox');
          if (checked) tr.classList.add('active');

          checkbox.addEventListener('change', (ev) => {
            const nowChecked = ev.target.checked;
            if (nowChecked){
              assignedPrivIds.add(pid);
              tr.classList.add('active');
            } else {
              assignedPrivIds.delete(pid);
              tr.classList.remove('active');
            }
            const moduleEl = tr.closest('.accordion-item');
            updateModuleSelectAllState(moduleEl);
            updateGlobalSelectAllState();
            updateKPIs();
          });

          // Row click toggles (except when clicking checkbox directly)
          tr.addEventListener('click', (e)=>{
            if (e.target && (e.target.matches('input'))) return;
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change', { bubbles:true }));
          });

          tbody.appendChild(tr);
        });
      }
    } else {
      // header/group: no table, no "no privilege" badge needed ✅
      if (tbody) tbody.innerHTML = '';
      if (tableWrap) tableWrap.style.display = 'none';
    }

    // Children
    const childWrap = item.querySelector('.ap-child-accordion');
    if (hasChildren){
      const nested = document.createElement('div');
      nested.className = 'accordion ap-accordion';
      node.children.forEach((ch, i) => nested.appendChild(buildAccordionItem(ch, i, depth + 1, myRootHeaderId)));
      childWrap.appendChild(nested);
    }

    // Module select all (subtree)
    if (moduleSelectAll){
      moduleSelectAll.addEventListener('change', (ev)=>{
        const checked = ev.target.checked;
        const rows = item.querySelectorAll('.ap-priv-row');
        rows.forEach(row=>{
          const cb  = row.querySelector('.sm-privilege-checkbox');
          const key = row.dataset.privId;
          if (!cb || !key) return;

          cb.checked = checked;
          if (checked){
            assignedPrivIds.add(key);
            row.classList.add('active');
          } else {
            assignedPrivIds.delete(key);
            row.classList.remove('active');
          }
        });

        updateModuleSelectAllState(item);
        updateGlobalSelectAllState();
        updateKPIs();
      });
    }

    return item;
  }

  // ========== Modules & privileges ==========
  async function loadModulesWithPrivileges(){
    modulesContainer.innerHTML = `<div class="ap-empty">Loading modules &amp; privileges…</div>`;
    modulesEmpty.style.display = 'none';
    try{
      const res = await fetch('/api/dashboard-menus/all-with-privileges', { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load modules');

      modules = Array.isArray(js.data) ? js.data : [];
      if(!modules.length){
        modulesContainer.innerHTML = '';
        modulesEmpty.style.display = '';
        chkGlobalSelectAll.checked = false;
        chkGlobalSelectAll.indeterminate = false;
        updateKPIs();
        return;
      }

      await loadAssignedPrivileges();
      renderModules();
    }catch(e){
      console.error(e);
      modulesContainer.innerHTML =
        `<div class="ap-empty text-danger">Failed to load modules: ${escapeHtml(e.message || '')}</div>`;
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = false;
      updateKPIs();
    }
  }

  function renderModules(){
    modulesContainer.innerHTML = '';
    if(!modules.length){
      modulesEmpty.style.display = '';
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = false;
      updateKPIs();
      return;
    }
    modulesEmpty.style.display = 'none';

    modules.forEach((rootNode, index) => {
      modulesContainer.appendChild(buildAccordionItem(rootNode, index, 0, null));
    });

    updateAllModulesSelectAllState();
    updateGlobalSelectAllState();
    updateKPIs();
    applySearchFilter();
  }

  // ========== Global select all ==========
  chkGlobalSelectAll?.addEventListener('change', (e)=>{
    const checked = e.target.checked;
    const rows = modulesContainer.querySelectorAll('.ap-priv-row');
    rows.forEach(row=>{
      const cb  = row.querySelector('.sm-privilege-checkbox');
      const key = row.dataset.privId;
      if (!cb || !key) return;

      cb.checked = checked;
      if (checked){
        assignedPrivIds.add(key);
        row.classList.add('active');
      } else {
        assignedPrivIds.delete(key);
        row.classList.remove('active');
      }
    });

    updateAllModulesSelectAllState();
    updateGlobalSelectAllState();
    updateKPIs();
    ok(checked ? 'All privileges selected (not yet saved)' : 'All privileges deselected (not yet saved)');
  });

  // ==========================================================
  // ✅ BUILD TREE PAYLOAD FROM UI (kept)
  // ==========================================================
  function buildTreePayloadFromUI(){
    const headersMap = new Map();

    const rows = modulesContainer.querySelectorAll('.ap-priv-row');
    rows.forEach(row=>{
      const pid = row.dataset.privId;
      if (!pid) return;
      if (!assignedPrivIds.has(String(pid))) return;

      const pageIdRaw   = Number(row.dataset.pageId || 0);
      const headerIdRaw = Number(row.dataset.headerId || 0);

      const pageId   = pageIdRaw || headerIdRaw || 0;
      const headerId = headerIdRaw || pageId || 0;

      const action = String(row.dataset.action || '').toLowerCase() || null;

      if (!pageId || !headerId) return;

      if (!headersMap.has(headerId)){
        headersMap.set(headerId, { id: headerId, type: "header", children: [] });
      }

      const headerNode = headersMap.get(headerId);

      let pageNode = headerNode.children.find(x => Number(x.id) === pageId);
      if (!pageNode){
        pageNode = { id: pageId, type: "page", privileges: [] };
        headerNode.children.push(pageNode);
      }

      if (!pageNode.privileges.some(x => Number(x.id) === Number(pid))){
        pageNode.privileges.push({ id: Number(pid), action });
      }
    });

    const tree = Array.from(headersMap.values());
    tree.sort((a,b)=>a.id-b.id);
    tree.forEach(h=>{
      h.children.sort((a,b)=>a.id-b.id);
      h.children.forEach(p=>{
        if (Array.isArray(p.privileges)) p.privileges.sort((x,y)=>x.id-y.id);
      });
    });

    return tree;
  }

  // ========== Save all (sync) ==========
  btnSaveAll.addEventListener('click', async ()=>{
    if(isSaving) return;
    if (!resolvedUserId && !resolvedUserUuid){
      err('User not resolved');
      return;
    }

    isSaving = true;
    btnSaveAll.disabled = true;
    btnSaveAll.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving';

    try{
      const tree = buildTreePayloadFromUI();
      const flat_privileges = Array.from(assignedPrivIds).map(v => Number(v)).filter(Boolean);

      const payload = { tree: Array.isArray(tree) ? tree : [], privileges: flat_privileges };
      if (resolvedUserId) payload.user_id = Number(resolvedUserId);
      else payload.user_uuid = String(resolvedUserUuid);

      const res = await fetch('/api/user-privileges/sync', {
        method:'POST',
        headers: authHeaders({'Content-Type':'application/json'}),
        body: JSON.stringify(payload)
      });

      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || js.error || 'Sync failed');

      ok('Privileges saved');
      await loadAssignedPrivileges();
      renderModules();
    }catch(e){
      console.error(e);
      err(e.message || 'Save failed');
    }finally{
      isSaving = false;
      btnSaveAll.disabled = false;
      btnSaveAll.innerHTML = '<i class="fa fa-save me-1"></i>Save';
    }
  });

  // ========== Refresh ==========
  btnRefresh.addEventListener('click', async ()=>{
    if (!resolvedUserId && !resolvedUserUuid){
      err('User not resolved');
      return;
    }
    await loadAssignedPrivileges();
    renderModules();
    ok('Refreshed');
  });

  // ==========================================================
  // ✅ Global actions dropdown handlers
  // ==========================================================
  function setAllCollapses(expand){
    const colls = modulesContainer.querySelectorAll('.accordion-collapse');
    colls.forEach(c=>{
      const inst = bootstrap.Collapse.getOrCreateInstance(c, { toggle:false });
      expand ? inst.show() : inst.hide();
    });
  }

  function clearAllSelections(){
    assignedPrivIds.clear();
    const rows = modulesContainer.querySelectorAll('.ap-priv-row');
    rows.forEach(row=>{
      const cb = row.querySelector('.sm-privilege-checkbox');
      if (cb) cb.checked = false;
      row.classList.remove('active');
    });
    updateAllModulesSelectAllState();
    updateGlobalSelectAllState();
    updateKPIs();
  }

  function selectAllSelections(){
    const rows = modulesContainer.querySelectorAll('.ap-priv-row');
    rows.forEach(row=>{
      const cb = row.querySelector('.sm-privilege-checkbox');
      const key = row.dataset.privId;
      if (!cb || !key) return;
      cb.checked = true;
      assignedPrivIds.add(String(key));
      row.classList.add('active');
    });
    updateAllModulesSelectAllState();
    updateGlobalSelectAllState();
    updateKPIs();
  }

  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-ap-action]');
    if(!btn) return;
    e.preventDefault();

    const action = btn.dataset.apAction;
    if (action === 'expandAll') { setAllCollapses(true); return; }
    if (action === 'collapseAll') { setAllCollapses(false); return; }
    if (action === 'clearAll') { clearAllSelections(); ok('Cleared (not yet saved)'); return; }
    if (action === 'selectAll') { selectAllSelections(); ok('Selected all (not yet saved)'); return; }
  });

  // ==========================================================
  // ✅ Module dropdown handlers
  // ==========================================================
  modulesContainer.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-mod-action]');
    if(!btn) return;
    e.preventDefault();

    const moduleEl = btn.closest('.accordion-item');
    if(!moduleEl) return;

    const act = btn.dataset.modAction;

    if (act === 'expand' || act === 'collapse'){
      const colls = moduleEl.querySelectorAll('.accordion-collapse');
      colls.forEach(c=>{
        const inst = bootstrap.Collapse.getOrCreateInstance(c, { toggle:false });
        act === 'expand' ? inst.show() : inst.hide();
      });
      return;
    }

    const modSelect = moduleEl.querySelector('.ap-mod-select-all');
    if (act === 'selectAll'){
      if (modSelect){ modSelect.checked = true; modSelect.indeterminate = false; modSelect.dispatchEvent(new Event('change', {bubbles:true})); }
      return;
    }
    if (act === 'clear'){
      if (modSelect){ modSelect.checked = false; modSelect.indeterminate = false; modSelect.dispatchEvent(new Event('change', {bubbles:true})); }
      return;
    }
  });

  // ==========================================================
  // ✅ Search filter
  // ==========================================================
  function applySearchFilter(){
    const q = (txtSearch?.value || '').trim().toLowerCase();
    const rows = modulesContainer.querySelectorAll('.ap-priv-row');

    if(!q){
      rows.forEach(r=> r.style.display = '');
      return;
    }

    rows.forEach(r=>{
      const title = (r.querySelector('.ap-priv-title')?.textContent || '').toLowerCase();
      const desc  = (r.querySelector('.ap-priv-desc')?.textContent || '').toLowerCase();
      const hit = title.includes(q) || desc.includes(q) || (r.dataset.action || '').includes(q);
      r.style.display = hit ? '' : 'none';
    });
  }

  txtSearch?.addEventListener('input', applySearchFilter);
  btnClearSearch?.addEventListener('click', ()=>{
    if (txtSearch) txtSearch.value = '';
    applySearchFilter();
    txtSearch?.focus();
  });

  // ========== Boot ==========
  (async ()=>{
    try{
      await resolveUserIdentity();
      await loadUserInfo();
      await loadModulesWithPrivileges();
    }catch(e){
      console.error(e);
      modulesContainer.innerHTML =
        `<div class="ap-empty text-danger">Cannot load privileges: ${escapeHtml(e.message || '')}</div>`;
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = false;
      updateKPIs();
    }
  })();

});
</script>
@endpush
