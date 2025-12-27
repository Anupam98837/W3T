{{-- resources/views/users/assignPrivileges.blade.php --}}
@extends('pages.users.layout.structure')

@section('title','Assign Privileges')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Assign Privileges (ViewCourse-style shell) ===== */
.ap-wrap{max-width:1140px;margin:16px auto 40px}


/* Profile mini */
.ap-profile{
  display:flex;
  gap:12px;
  align-items:center;
  padding:10px 12px;
  border:1px solid var(--line-strong);
  border-radius:14px;
  background:var(--background-soft);
  margin-bottom:10px;
}
.ap-avatar{
  width:46px;height:46px;border-radius:999px;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg, rgba(158,54,58,.18), rgba(201,75,80,.10));
  border:1px solid var(--line-soft);
  color:var(--primary-color);
  font-weight:800;
  flex:0 0 auto;
}
.ap-profile-meta{display:flex;flex-direction:column;min-width:0}
.ap-profile-name{
  font-weight:800;
  color:var(--ink);
  line-height:1.15;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  max-width:420px;
}
.ap-profile-sub{
  font-size:0.82rem;
  color:var(--muted-color);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  max-width:520px;
}
.ap-profile-badges{
  display:flex;flex-wrap:wrap;gap:6px;
  margin-top:6px;
}
.ap-badge{
  display:inline-flex;align-items:center;gap:6px;
  border:1px solid var(--line-soft);
  background:var(--surface);
  color:var(--muted-color);
  border-radius:999px;
  padding:2px 10px;
  font-size:0.78rem;
}
.ap-badge b{color:var(--ink);font-weight:700}

/* Main card */
.ap-card{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  margin-top:12px;
}
.ap-card-head{
  padding:12px 14px;
  border-bottom:1px solid var(--line-strong);
  font-weight:600;
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  align-items:center;
  justify-content:space-between;
}
.ap-card-head-left{display:flex;flex-direction:column}
.ap-card-head-title{font-weight:600}
.ap-card-head-sub{font-size:0.8rem;color:var(--muted-color)}
.ap-card-head-right{display:flex;align-items:center;gap:6px}
.ap-card-body{padding:14px}

/* Bootstrap accordion styling */
.ap-accordion .accordion-item{
  border-radius:12px;
  overflow:hidden;
  border:1px solid var(--line-soft);
  margin-bottom:8px;
  background:var(--surface);
}
.ap-accordion .accordion-button{
  display:flex;align-items:center;
  padding:8px 14px;gap:8px;
  font-weight:600;
  background:var(--background-soft);
  color:var(--ink);
}
.ap-accordion .accordion-button:not(.collapsed){
  background:var(--surface-soft);
  box-shadow:none;
}
.ap-accordion .accordion-button:focus{box-shadow:0 0 0 1px var(--primary-color)}
.ap-module-header-inner{display:flex;align-items:center;justify-content:space-between;width:100%}
.ap-module-title{font-weight:700;font-size:0.96rem}
.ap-module-pill{
  font-size:0.75rem;border-radius:999px;padding:2px 10px;
  background:var(--background-soft);color:var(--muted-color);
  border:1px solid var(--line-soft);
}

/* Module tools (local Select all) */
.ap-module-tools{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  margin-bottom:6px;
}

/* Privilege table area */
.ap-module-body{padding:4px 0}
.ap-priv-table-wrap{margin-top:4px}
.ap-priv-table{width:100%;border-collapse:collapse;font-size:0.86rem}
.ap-priv-table thead th{
  background:var(--background-soft);
  color:var(--muted-color);
  font-weight:600;
  border-bottom:1px solid var(--line-soft);
  padding:6px 8px;
}
.ap-priv-table tbody td{
  border-top:1px solid var(--line-soft);
  padding:6px 8px;
  vertical-align:middle;
}
.ap-priv-row{transition:background .15s ease, box-shadow .15s ease, border-color .15s ease}
.ap-priv-row:hover{background:var(--background-hover);box-shadow:0 3px 8px rgba(0,0,0,0.02)}
.ap-priv-row.active{background:var(--background-soft);box-shadow:0 0 0 2px rgba(201,75,80,.10)}

/* Privilege text */
.ap-priv-title{font-size:0.93rem;font-weight:700;color:var(--ink);margin-bottom:2px}
.ap-priv-desc{font-size:0.8rem;color:var(--muted-color)}

/* Checkbox */
.ap-check{
  width:18px;height:18px;
  accent-color: var(--primary-color);
  cursor:pointer;
}

/* Empty State */
.ap-empty{padding:32px;text-align:center;color:var(--muted-color)}
.ap-empty i{font-size:2rem;margin-bottom:12px;opacity:0.5}

/* Small muted helper */
.ap-small-muted{font-size:13px;color:var(--muted-color)}

/* Responsive */
@media (max-width: 576px){
  .ap-card-head{align-items:flex-start}
  .ap-card-head-right{width:100%;justify-content:flex-start}
  .ap-priv-table thead{display:none}
  .ap-priv-table tbody td{
    display:block;width:100%;
    border-top:none;border-bottom:1px solid var(--line-soft);
  }
  .ap-priv-table tbody tr:last-child td{border-bottom:none}
  .ap-priv-table tbody td:first-child{padding-top:8px}
  .ap-priv-table tbody td:last-child{padding-bottom:8px}
  .ap-profile-name{max-width:240px}
  .ap-profile-sub{max-width:260px}
}
</style>
@endpush

@section('content')
<div class="ap-wrap">
  {{-- Header Panel --}}
  <div class="row g-2 mb-3 align-items-center panel">
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
{{-- ✅ Mini profile section --}}
      <div class="ap-profile">
        <div id="apAvatar" class="ap-avatar">U</div>
        <div class="ap-profile-meta">
          <div id="apProfileName" class="ap-profile-name">Loading user…</div>
          <div id="apProfileSub" class="ap-profile-sub">Please wait</div>
          <div id="apProfileBadges" class="ap-profile-badges" style="display:none;"></div>
        </div>
      </div>
  {{-- Main card with accordion modules --}}
  <div class="card">
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
      </div>
    </div>

    <div class="ap-card-body">
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

  // profile nodes
  const apAvatar        = document.getElementById('apAvatar');
  const apProfileName   = document.getElementById('apProfileName');
  const apProfileSub    = document.getElementById('apProfileSub');
  const apProfileBadges = document.getElementById('apProfileBadges');

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
    if (u.id)   badges.push(`<span class="ap-badge"><i class="fa fa-id-card"></i><b>ID</b> ${escapeHtml(u.id)}</span>`);
    if (u.uuid) badges.push(`<span class="ap-badge"><i class="fa fa-fingerprint"></i><b>UUID</b> ${escapeHtml(u.uuid)}</span>`);
    if (role)   badges.push(`<span class="ap-badge"><i class="fa fa-user-shield"></i><b>Role</b> ${escapeHtml(role)}</span>`);

    if (apProfileBadges){
      apProfileBadges.innerHTML = badges.join('');
      apProfileBadges.style.display = badges.length ? '' : 'none';
    }
  }

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

  // ========== Privileges: load from DB ==========
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
  function updateModuleSelectAllState(moduleEl){
    if (!moduleEl) return;
    const moduleCheckbox = moduleEl.querySelector('.ap-mod-select-all');
    if (!moduleCheckbox) return;

    const rows = moduleEl.querySelectorAll(':scope .ap-priv-row');
    if (!rows.length){
      moduleCheckbox.checked = false;
      moduleCheckbox.indeterminate = false;
      return;
    }

    let checkedCount = 0;
    rows.forEach(row=>{
      const cb = row.querySelector('.sm-privilege-checkbox');
      if (cb && cb.checked) checkedCount++;
    });

    if (checkedCount === 0){
      moduleCheckbox.checked = false;
      moduleCheckbox.indeterminate = false;
    } else if (checkedCount === rows.length){
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

  function buildMenuIndexFromModulesTree(roots){
    const menuById = new Map();
    const walk = (nodes, parentId = null) => {
      (nodes || []).forEach(n=>{
        if (n && n.id != null){
          menuById.set(Number(n.id), {
            id: Number(n.id),
            name: n.name || n.title || null,
            href: n.href || null,
            icon_class: n.icon_class || null,
            parent_id: n.parent_id != null ? Number(n.parent_id) : (parentId != null ? Number(parentId) : null),
            is_dropdown_head: (n.is_dropdown_head != null ? !!n.is_dropdown_head : null),
          });
        }
        if (n.children && n.children.length){
          walk(n.children, n.id);
        }
      });
    };
    walk(roots, null);
    return menuById;
  }

  // ✅ accordion builder (checkbox)
  function buildAccordionItem(node, index, depth = 0, rootHeaderId = null){
    const item       = document.createElement('div');
    const collapseId = `ap_mod_${node.id ?? ('i'+index)}_${depth}_${index}`;
    const headerId   = `ap_modh_${node.id ?? ('i'+index)}_${depth}_${index}`;
    const moduleKey  = String(node.id ?? ('i'+index));
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
              <i class="fa fa-shield-halved me-1"></i>
              Privileges: ${privCount}
            </span>
          </div>
        </button>
      </h2>

      <div id="${collapseId}" class="accordion-collapse collapse ${index ? '' : 'show'}"
           aria-labelledby="${headerId}">
        <div class="accordion-body">
          <div class="ap-module-tools">
            <span class="ap-small-muted">Quick actions for this module:</span>
            <label class="form-check mb-0 d-flex align-items-center gap-2">
              <input class="form-check-input ap-mod-select-all" type="checkbox">
              <span class="ap-small-muted">Select all</span>
            </label>
          </div>

          <div class="ap-module-body">
            <div class="ap-priv-table-wrap">
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

    // Render privileges
    if(!node.privileges || !node.privileges.length){
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

        // ✅ IMPORTANT: dataset for payload
        tr.dataset.privId = pid;
        tr.dataset.action = String(p.action || p.name || '').toLowerCase();

        // ✅ pageId: node.id (normal)
        // if for some reason node.id missing, fallback to 0
        tr.dataset.pageId = String(node.id ?? '0');

        // ✅ headerId: root header id
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

    // Children
    const childWrap = item.querySelector('.ap-child-accordion');
    if (node.children && node.children.length){
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
    }
  }

  function renderModules(){
    modulesContainer.innerHTML = '';
    if(!modules.length){
      modulesEmpty.style.display = '';
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = false;
      return;
    }
    modulesEmpty.style.display = 'none';

    modules.forEach((rootNode, index) => {
      modulesContainer.appendChild(buildAccordionItem(rootNode, index, 0, null));
    });

    updateAllModulesSelectAllState();
    updateGlobalSelectAllState();
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
    ok(checked ? 'All privileges selected (not yet saved)' : 'All privileges deselected (not yet saved)');
  });

  // ==========================================================
  // ✅ BUILD TREE PAYLOAD FROM UI  (FIXED)
  // - headerId fallback (prevents skipping rows)
  // - pageId fallback
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

      // ✅ Fix: fallback so we don't skip and accidentally wipe
      const pageId   = pageIdRaw || headerIdRaw || 0;
      const headerId = headerIdRaw || pageId || 0;

      const action = String(row.dataset.action || '').toLowerCase() || null;

      if (!pageId || !headerId) return;

      if (!headersMap.has(headerId)){
        headersMap.set(headerId, {
          id: headerId,
          type: "header",
          children: []
        });
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

    // stable order
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

      // always send array (even if empty) so backend validator is satisfied
      const flat_privileges = Array.from(assignedPrivIds).map(v => Number(v)).filter(Boolean);

      const payload = { tree: Array.isArray(tree) ? tree : [], privileges: flat_privileges };
      if (resolvedUserId) payload.user_id = Number(resolvedUserId);
      else payload.user_uuid = String(resolvedUserUuid);

      // DEBUG (uncomment if needed)
      // console.log('SYNC payload:', payload);

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
    }
  })();

});
</script>
@endpush
