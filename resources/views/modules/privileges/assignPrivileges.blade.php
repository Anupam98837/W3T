{{-- resources/views/users/assignPrivileges.blade.php --}}
@extends('pages.users.admin.layout.structure')
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
@push('styles')
<style>
/* prefixed with sm- */
.sm-cm-wrap{max-width:1140px;margin:16px auto 40px; background: var(--primary-color)}
.sm-panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:12px;padding:12px}

/* Module Card Styling */
.sm-module-card{border:1px solid var(--line-strong);border-radius:12px;padding:16px;margin-bottom:16px;background:var(--surface);box-shadow:var(--shadow-1)}
.sm-module-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--line-soft)}
.sm-module-title{font-weight:700;font-size:1.1rem;color:var(--ink)}
.sm-module-priv-count{font-size:0.85rem;color:var(--muted-color);background:var(--background-soft);padding:2px 8px;border-radius:12px}

/* Privilege List Styling */
.sm-privilege-item{display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:8px;border:1px solid var(--line-soft);margin-bottom:8px;background:var(--background-soft);transition:all 0.2s ease}
.sm-privilege-item:hover{background:var(--background-hover);border-color:var(--line-strong)}
.sm-privilege-left{display:flex;align-items:center;gap:12px;flex:1}
.sm-privilege-checkbox{width:18px;height:18px;cursor:pointer}
.sm-privilege-content{flex:1}
.sm-privilege-action{font-family:'Courier New', monospace;font-size:0.95rem;font-weight:600;color:var(--ink);margin-bottom:2px}
.sm-privilege-desc{font-size:0.85rem;color:var(--muted-color)}
.sm-privilege-badge{background:var(--primary-light);color:var(--primary-dark);padding:2px 8px;border-radius:10px;font-size:0.75rem;font-weight:600}

/* Empty State */
.sm-card-empty{padding:32px;text-align:center;color:var(--muted-color)}
.sm-card-empty i{font-size:2rem;margin-bottom:12px;opacity:0.5}

/* Action Buttons */
.sm-assign-actions{display:flex;gap:6px;align-items:center}
.sm-btn-sm-icon{padding:4px 8px;font-size:0.8rem}
.sm-small-muted{font-size:13px;color:var(--muted-color)}

/* Toggle Switch Style */
.sm-toggle-switch{position:relative;display:inline-block;width:44px;height:24px}
.sm-toggle-switch input{opacity:0;width:0;height:0}
.sm-toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:var(--line-strong);transition:.3s;border-radius:24px}
.sm-toggle-slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background-color:white;transition:.3s;border-radius:50%}
input:checked + .sm-toggle-slider{background-color:var(--primary)}
input:checked + .sm-toggle-slider:before{transform:translateX(20px)}
input:disabled + .sm-toggle-slider{opacity:0.6;cursor:not-allowed}

/* small helper for active state if needed */
.sm-privilege-item.active{box-shadow:0 0 0 2px rgba(0,0,0,0.03)}
</style>
@endpush

@section('content')
<div class="sm-cm-wrap">
  <!-- Header panel remains the same -->
  <div class="row g-2 mb-3 align-items-center sm-panel">
    <div class="col">
      <h4 class="mb-0">Manage Privileges for User</h4>
      <div id="userSummary" class="sm-small-muted">Loading user…</div>
    </div>
    <div class="col-auto d-flex gap-2">
      <a href="javascript:history.back()" class="btn btn-light"><i class="fa fa-arrow-left me-1"></i>Back</a>
      <button id="btnRefresh" class="btn btn-light"><i class="fa fa-rotate-right me-1"></i>Refresh</button>
      <button id="btnSaveAll" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
    </div>
  </div>

  <!-- Updated Modules Container Structure -->
  <div id="modulesContainer">
    <!-- Each module card will have this structure: -->
    <div class="sm-module-card">
      <div class="sm-module-header">
        <div class="sm-module-title">Course</div>
        <div class="sm-module-priv-count">Privileges: 3</div>
      </div>
      <div class="sm-priv-list">
        <div class="sm-privilege-item active" data-priv-id="1">
          <div class="sm-privilege-left">
            <label class="sm-toggle-switch">
              <input type="checkbox" class="sm-privilege-checkbox" checked>
              <span class="sm-toggle-slider"></span>
            </label>
            <div class="sm-privilege-content">
              <div class="sm-privilege-action">Add</div>
              <div class="sm-privilege-desc">Adding new items</div>
            </div>
          </div>
          <div class="sm-assign-actions">
            <span class="sm-privilege-badge">ID: 123</span>
            {{-- trash removed as requested --}}
          </div>
        </div>
        <!-- More privilege items... -->
      </div>
    </div>
  </div>

  <div class="sm-small-muted mt-3">Tip: toggle privileges on/off and press <b>Save</b>. Checked = assigned; unchecked = unassigned.</div>
</div>
{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1200">
  <div id="toastOk" class="toast text-bg-success border-0"><div class="d-flex"><div id="toastOkMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div></div>
  <div id="toastErr" class="toast text-bg-danger border-0 mt-2"><div class="d-flex"><div id="toastErrMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div></div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', ()=> {

  const deriveCourseKey = () => {
    const parts = location.pathname.split('/').filter(Boolean);
    const last = parts.at(-1)?.toLowerCase();
    if (last === 'view' && parts.length >= 2) return parts.at(-2);
    return parts.at(-1);
  };
  const courseKey = deriveCourseKey();
  console.log('derived courseKey:', courseKey);

  // ===== query params (unchanged) =====
  const params = new URLSearchParams(location.search);
  // Accept either user_uuid OR user_id (numeric) in querystring
  const userUuidParam = params.get('user_uuid') || params.get('uuid') || '';
  const userIdParam   = params.get('user_id') || params.get('id') || '';
  console.log('query user_uuid:', userUuidParam, 'query user_id:', userIdParam);

  const token = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!token) { Swal.fire('Login required','Please login again','warning').then(()=>location.href='/'); return; }

  const authHeaders = (extra={}) => Object.assign({'Authorization':'Bearer '+token, 'Accept':'application/json'}, extra);
  const toastOk = new bootstrap.Toast(document.getElementById('toastOk'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastErr'));
  const ok = (m='Done') => { document.getElementById('toastOkMsg').textContent = m; toastOk.show(); };
  const err = (m='Something went wrong') => { document.getElementById('toastErrMsg').textContent = m; toastErr.show(); };

  const modulesContainer = document.getElementById('modulesContainer');
  const userSummary = document.getElementById('userSummary');
  const btnSaveAll = document.getElementById('btnSaveAll');
  const btnRefresh = document.getElementById('btnRefresh');

  // local state
  let modules = [];                 // array of modules {id, uuid, name, privileges: [ {id, uuid, action, description} ]}
  let assignedPrivIds = new Set();  // privilege_id integers (or uuids depending on your api) currently assigned to user
  let mappingByPriv = {};           // map: privilege_id_or_uuid -> mapping_uuid (for fast unassign)
  let isSaving = false;
  let resolvedUserId = null;        // numeric id we will resolve and use for API calls
  let resolvedUserUuid = null;      // canonical uuid (if we found one)

  // utility
  function escapeHtml(s){ return (s||'').toString().replace(/[&<>"'`]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}[ch])); }

  async function resolveUserIdentity(){
    // if numeric id provided, prefer it
    if (userIdParam) {
      resolvedUserId = Number(userIdParam);
      // also try to fetch the canonical UUID for display (but don't fail if fetch fails)
      try {
        const r = await fetch(`/api/user/${encodeURIComponent(resolvedUserId)}`, { headers: authHeaders() });
        if (r.ok) {
          const js = await r.json().catch(()=>({}));
          const u = js.user || js.data || {};
          resolvedUserUuid = u.uuid || u.user_uuid || resolvedUserUuid;
          // set UI summary
          if (u) userSummary.innerHTML = `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
        }
      } catch(e){ /* ignore */ }
      return;
    }

    // if uuid param provided, attempt to find numeric user id using common endpoints
    if (userUuidParam) {
      resolvedUserUuid = userUuidParam;
      // 1) Try GET /api/users/{uuid}
      try {
        const r1 = await fetch(`/api/user/${encodeURIComponent(userUuidParam)}`, { headers: authHeaders() });
        if (r1.ok) {
          const js = await r1.json().catch(()=>({}));
          const u = js.user || js.data || {};
          if (u && (u.id || u.uuid)) {
            resolvedUserId = Number(u.id);
            resolvedUserUuid = u.uuid || resolvedUserUuid;
            userSummary.innerHTML = `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
            return;
          }
        }
      } catch(e){ /* continue to next */ }
    }

    // Nothing resolved
    throw new Error('Could not resolve user from provided parameters (user_id/user_uuid).');
  }

  async function loadUserInfo(){
    try {
      if (resolvedUserId) {
        const res = await fetch(`/api/user/${encodeURIComponent(resolvedUserId)}`, { headers: authHeaders() });
        const js = await res.json().catch(()=>({}));
        if(res.ok && js.user){
          const u = js.user;
          userSummary.innerHTML = `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
          resolvedUserUuid = resolvedUserUuid || (u.uuid || u.user_uuid);
          return;
        }
      } else if (resolvedUserUuid) {
        // last resort: try to fetch by uuid
        const res = await fetch(`/api/user/${encodeURIComponent(resolvedUserUuid)}`, { headers: authHeaders() });
        const js = await res.json().catch(()=>({}));
        if(res.ok && js.user){
          const u = js.user;
          userSummary.innerHTML = `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
          resolvedUserId = Number(u.id);
          resolvedUserUuid = u.uuid || u.user_uuid || resolvedUserUuid;
          return;
        }
      }

      // fallback text if fetch didn't populate UI
      userSummary.textContent = 'User information not available';
    } catch(e){
      userSummary.textContent = 'Failed to load user';
    }
  }

  // load all modules with privileges (non-deleted)
  async function loadModulesWithPrivileges(){
    modulesContainer.innerHTML = `<div class="sm-card-empty">Loading modules & privileges…</div>`;
    try {
      const res = await fetch('/api/modules/all-with-privileges', { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load modules');
      modules = Array.isArray(js.data) ? js.data : (js.data?.length ? js.data : []);
      if(!modules.length){
        modulesContainer.innerHTML = `<div class="sm-card-empty">No modules or privileges found.</div>`;
        return;
      }
      await loadAssignedPrivileges();
      renderModules();
    } catch(e){
      console.error(e);
      modulesContainer.innerHTML = `<div class="sm-card-empty text-danger">Failed to load modules: ${escapeHtml(e.message || '')}</div>`;
    }
  }

  // load assigned privileges for this user - NOTE: backend list expects numeric user_id
  async function loadAssignedPrivileges(){
    assignedPrivIds = new Set();
    mappingByPriv = {};
    if (!resolvedUserId) {
      console.warn('No resolved user id when loading assigned privileges');
      return;
    }
    try {
      // endpoint: GET /api/user-privileges/list?user_id=ID
      const res = await fetch(`/api/user-privileges/list?user_id=${encodeURIComponent(resolvedUserId)}`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load assigned privileges');
      const data = Array.isArray(js.data) ? js.data : (js.data?.length ? js.data : []);
      for(const r of data){
        // r: mapping_uuid, privilege_id, privilege_uuid, privilege_action, privilege_description
        // prefer privilege_id numeric; fallback to uuid or mapping_uuid
        const pidKey = r.privilege_id != null ? String(r.privilege_id) : (r.privilege_uuid ? String(r.privilege_uuid) : null);
        if (pidKey) {
          assignedPrivIds.add(pidKey);
          if (r.mapping_uuid) mappingByPriv[pidKey] = String(r.mapping_uuid);
        } else if (r.mapping_uuid) {
          // fallback: store mapping by the mapping uuid itself to allow lookup if needed
          mappingByPriv[r.mapping_uuid] = String(r.mapping_uuid);
        }
      }
    } catch(e){
      console.warn('Could not load assigned privileges', e);
    }
  }

function renderModules(){
  modulesContainer.innerHTML = '';
  
  if(!modules.length){
    modulesContainer.innerHTML = `
      <div class="sm-card-empty">
        <i class="fa fa-folder-open"></i>
        <div>No modules or privileges found.</div>
      </div>
    `;
    return;
  }

  modules.forEach(m => {
    const card = document.createElement('div');
    card.className = 'sm-module-card';
    
    // Module header
    const header = document.createElement('div');
    header.className = 'sm-module-header';
    header.innerHTML = `
      <div class="sm-module-title">${escapeHtml(m.name || ('#'+m.id))}</div>
      <div class="sm-module-priv-count">Privileges: ${m.privileges?.length || 0}</div>
    `;
    card.appendChild(header);

    // Privileges list
    const list = document.createElement('div');
    list.className = 'sm-priv-list';

    if(!m.privileges || !m.privileges.length){
      list.innerHTML = `
        <div class="sm-card-empty sm-small-muted">
          <i class="fa fa-ban"></i>
          <div>No privileges for this module</div>
        </div>
      `;
    } else {
      // --- inner privileges loop (prefixed classes) ---
m.privileges.forEach(p => {
  const pid = String(p.id ?? p.uuid ?? '');
  const checked = assignedPrivIds.has(pid) ? 'checked' : '';
  const row = document.createElement('div');
  row.className = 'sm-privilege-item';
  row.dataset.privId = pid;

  // build row WITHOUT trash button (toggle-only)
  row.innerHTML = `
    <div class="sm-privilege-left">
      <label class="sm-toggle-switch">
        <input type="checkbox" class="sm-privilege-checkbox" ${checked}>
        <span class="sm-toggle-slider"></span>
      </label>
      <div class="sm-privilege-content">
        <div class="sm-privilege-action">${escapeHtml(p.action || p.name || 'Untitled')}</div>
        <div class="sm-privilege-desc">${escapeHtml(p.description || 'No description')}</div>
      </div>
    </div>
    <div class="sm-assign-actions">
      <span class="sm-privilege-badge">ID: ${p.id ?? (p.uuid ? p.uuid.substring(0,8) : 'N/A')}</span>
    </div>
  `;

  // find checkbox element
  const checkbox = row.querySelector('.sm-privilege-checkbox');

  // helper UI while awaiting network
  const setBusy = (busy) => {
    checkbox.disabled = !!busy;
    if (busy) row.style.opacity = '0.6';
    else row.style.opacity = '';
  };

  // checkbox change handler -> immediate assign/unassign
  checkbox.addEventListener('change', async (ev) => {
    const nowChecked = ev.target.checked;

    // optimistic UI: disable while request in-flight
    setBusy(true);

    try {
      if (nowChecked) {
        // ASSIGN
        const body = {};
        if (resolvedUserId) body.user_id = Number(resolvedUserId);
        else if (resolvedUserUuid) body.user_uuid = String(resolvedUserUuid);

        if (p.id) body.privilege_id = Number(p.id);
        else if (p.uuid) body.privilege_uuid = String(p.uuid);

        const res = await fetch('/api/user-privileges/assign', {
          method: 'POST',
          headers: authHeaders({'Content-Type':'application/json'}),
          body: JSON.stringify(body)
        });
        const js = await res.json().catch(()=>({}));

        if (!res.ok) throw new Error(js.message || 'Assign failed');

        // success: update local state
        const returned = js.data || js.mapping || js || {};
        // mapping_uuid could be in returned.mapping_uuid or returned.mapping?.uuid etc.
        const mappingUuid = returned.mapping_uuid || returned.mapping?.uuid || returned.uuid || null;
        const key = p.id != null ? String(p.id) : (p.uuid ? String(p.uuid) : pid);
        assignedPrivIds.add(key);
        if (mappingUuid) mappingByPriv[key] = String(mappingUuid);

        row.classList.add('active');
        ok('Privilege assigned');
      } else {
        // UNASSIGN
        // prefer sending mapping_uuid if we have it
        const key = p.id != null ? String(p.id) : (p.uuid ? String(p.uuid) : pid);
        const mappingUuid = mappingByPriv[key] || null;

        const body = {};
        if (mappingUuid) {
          body.mapping_uuid = String(mappingUuid);
        } else {
          if (resolvedUserId) body.user_id = Number(resolvedUserId);
          else if (resolvedUserUuid) body.user_uuid = String(resolvedUserUuid);

          if (p.id) body.privilege_id = Number(p.id);
          else if (p.uuid) body.privilege_uuid = String(p.uuid);
        }

        const res = await fetch('/api/user-privileges/unassign', {
          method: 'POST',
          headers: authHeaders({'Content-Type':'application/json'}),
          body: JSON.stringify(body)
        });
        const js = await res.json().catch(()=>({}));

        if (!res.ok) throw new Error(js.message || 'Unassign failed');

        // success: update local state
        assignedPrivIds.delete(key);
        if (mappingByPriv[key]) delete mappingByPriv[key];

        row.classList.remove('active');
        ok('Privilege removed');
      }
    } catch (e) {
      console.error(e);
      // revert checkbox state on error
      checkbox.checked = !nowChecked;
      err(e.message || 'Operation failed');
    } finally {
      setBusy(false);
    }
  });

  // set initial active class (if checked)
  if (checked) row.classList.add('active');

  list.appendChild(row);
});
    }

    card.appendChild(list);
    modulesContainer.appendChild(card);
  });
}

  // Save all changes (sync) — sends numeric user_id when available
  btnSaveAll.addEventListener('click', async ()=>{
    if(isSaving) return;
    if (!resolvedUserId && !resolvedUserUuid) { err('User not resolved'); return; }

    isSaving = true;
    btnSaveAll.disabled = true;
    btnSaveAll.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving';

    try {
      // gather selected privilege ids - prefer numeric where present
      const currentIds = [];
      modules.forEach(m=>{
        (m.privileges || []).forEach(p=>{
          const pid = String(p.id ?? p.uuid ?? '');
          if(assignedPrivIds.has(pid)){
            currentIds.push(p.id ? Number(p.id) : String(p.uuid));
          }
        });
      });

      const payload = { privileges: Array.from(new Set(currentIds)) };
      if (resolvedUserId) payload.user_id = Number(resolvedUserId);
      else payload.user_uuid = String(resolvedUserUuid);

      const res = await fetch('/api/user-privileges/sync', {
        method: 'POST',
        headers: authHeaders({'Content-Type':'application/json'}),
        body: JSON.stringify(payload)
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Sync failed');

      ok('Privileges synced');
      await loadAssignedPrivileges();
      renderModules();
    } catch(e){
      console.error(e);
      err(e.message || 'Save failed');
    } finally {
      isSaving = false;
      btnSaveAll.disabled = false;
      btnSaveAll.innerHTML = '<i class="fa fa-save me-1"></i>Save';
    }
  });

  btnRefresh.addEventListener('click', async ()=> {
    if (!resolvedUserId && !resolvedUserUuid) { err('User not resolved'); return; }
    await loadAssignedPrivileges();
    renderModules();
    ok('Refreshed');
  });

  // boot sequence: resolve identity then load data
  (async ()=>{
    try {
      console.log('boot sequence — courseKey:', courseKey, 'userUuidParam:', userUuidParam, 'userIdParam:', userIdParam);
      await resolveUserIdentity();   // sets resolvedUserId / resolvedUserUuid and populates userSummary
      if (!resolvedUserId && !resolvedUserUuid) throw new Error('User not found');

      // now fetch modules & assigned privileges (assigned uses resolvedUserId)
      await loadModulesWithPrivileges();
    } catch (e) {
      console.error(e);
      Swal.fire('Cannot continue', e.message || 'Missing or invalid user identifier', 'error');
      modulesContainer.innerHTML = `<div class="sm-card-empty text-danger">Cannot load privileges: ${escapeHtml(e.message || '')}</div>`;
    }
  })();

});
</script>
@endpush
