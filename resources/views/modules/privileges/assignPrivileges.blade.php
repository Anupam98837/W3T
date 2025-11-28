{{-- resources/views/users/assignPrivileges.blade.php --}}
@section('title','Assign Privileges')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Assign Privileges (ViewCourse-style shell) ===== */
.ap-wrap{max-width:1140px;margin:16px auto 40px}

/* Header card */
.ap-panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:14px;
}

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
.ap-card-head-left{
  display:flex;
  flex-direction:column;
}
.ap-card-head-title{
  font-weight:600;
}
.ap-card-head-sub{
  font-size:0.8rem;
  color:var(--muted-color);
}
.ap-card-head-right{
  display:flex;
  align-items:center;
  gap:6px;
}
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
  display:flex;
  align-items:center;
  padding:8px 14px;
  gap:8px;
  font-weight:600;
  background:var(--background-soft);
  color:var(--ink);
}
.ap-accordion .accordion-button:not(.collapsed){
  background:var(--surface-soft);
  box-shadow:none;
}
.ap-accordion .accordion-button:focus{
  box-shadow:0 0 0 1px var(--primary-color);
}
.ap-module-header-inner{
  display:flex;
  align-items:center;
  justify-content:space-between;
  width:100%;
}
.ap-module-title{
  font-weight:700;
  font-size:0.96rem;
}
.ap-module-pill{
  font-size:0.75rem;
  border-radius:999px;
  padding:2px 10px;
  background:var(--background-soft);
  color:var(--muted-color);
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
.ap-module-body{
  padding:4px 0;
}
.ap-priv-table-wrap{
  margin-top:4px;
}
.ap-priv-table{
  width:100%;
  border-collapse:collapse;
  font-size:0.86rem;
}
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
.ap-priv-row{
  transition:background .15s ease, box-shadow .15s ease, border-color .15s ease;
}
.ap-priv-row:hover{
  background:var(--background-hover);
  box-shadow:0 3px 8px rgba(0,0,0,0.02);
}
.ap-priv-row.active{
  background:var(--background-soft);
  box-shadow:0 0 0 2px rgba(201,75,80,.10);
}

/* Privilege text */
.ap-priv-title{
  font-size:0.93rem;
  font-weight:700;
  color:var(--ink);
  margin-bottom:2px;
}
.ap-priv-desc{
  font-size:0.8rem;
  color:var(--muted-color);
}

/* Toggle Switch (checkbox in row) */
.ap-toggle-switch{
  position:relative;
  display:inline-block;
  width:44px;
  height:24px;
}
.ap-toggle-switch input{
  opacity:0;
  width:0;
  height:0;
}
.ap-toggle-slider{
  position:absolute;
  cursor:pointer;
  top:0;left:0;right:0;bottom:0;
  background-color:var(--line-strong);
  transition:.3s;
  border-radius:24px;
}
.ap-toggle-slider:before{
  position:absolute;
  content:"";
  height:18px;width:18px;
  left:3px;bottom:3px;
  background-color:white;
  transition:.3s;
  border-radius:50%;
}
input:checked + .ap-toggle-slider{
  background:var(--primary-color);
}
input:checked + .ap-toggle-slider:before{
  transform:translateX(20px);
}
input:disabled + .ap-toggle-slider{
  opacity:0.5;
  cursor:not-allowed;
}

/* Empty State */
.ap-empty{
  padding:32px;
  text-align:center;
  color:var(--muted-color);
}
.ap-empty i{
  font-size:2rem;
  margin-bottom:12px;
  opacity:0.5;
}

/* Small muted helper */
.ap-small-muted{
  font-size:13px;
  color:var(--muted-color);
}

/* Responsive */
@media (max-width: 576px){
  .ap-card-head{
    align-items:flex-start;
  }
  .ap-card-head-right{
    width:100%;
    justify-content:flex-start;
  }
  .ap-priv-table thead{
    display:none;
  }
  .ap-priv-table tbody td{
    display:block;
    width:100%;
    border-top:none;
    border-bottom:1px solid var(--line-soft);
  }
  .ap-priv-table tbody tr:last-child td{
    border-bottom:none;
  }
  .ap-priv-table tbody td:first-child{
    padding-top:8px;
  }
  .ap-priv-table tbody td:last-child{
    padding-bottom:8px;
  }
}
</style>
@endpush

@section('content')
<div class="ap-wrap">
  {{-- Header Panel --}}
  <div class="row g-2 mb-3 align-items-center ap-panel">
    <div class="col">
      <h4 class="mb-0">Manage User Privileges</h4>
      <div id="userSummary" class="ap-small-muted">Loading user…</div>
    </div>
    <div class="col-auto d-flex flex-wrap gap-2 justify-content-end">
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

  {{-- Main card with accordion modules --}}
  <div class="ap-card">
    <div class="ap-card-head">
      <div class="ap-card-head-left">
        <span class="ap-card-head-title">Modules &amp; Privileges</span>
        <span class="ap-card-head-sub">
          Toggle the privileges you want this user to have, then click <strong>Save</strong>.
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
    if (typeof Swal !== 'undefined') {
      Swal.fire('Login required','Please login again','warning').then(()=>location.href='/');
    } else {
      alert('Login required. Redirecting to home.');
      location.href='/';
    }
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
  const userSummary        = document.getElementById('userSummary');
  const btnSaveAll         = document.getElementById('btnSaveAll');
  const btnRefresh         = document.getElementById('btnRefresh');
  const chkGlobalSelectAll = document.getElementById('chkGlobalSelectAll');

  let modules          = [];
  let assignedPrivIds  = new Set();  // current in-UI state
  let isSaving         = false;
  let resolvedUserId   = null;
  let resolvedUserUuid = null;

  function escapeHtml(s){
    return (s||'').toString().replace(/[&<>"'`]/g, ch => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'
    }[ch]));
  }

  // ========== User resolution ==========

  async function resolveUserIdentity(){
    if (userIdParam){
      resolvedUserId = Number(userIdParam);
      try{
        const r = await fetch(`/api/user/${encodeURIComponent(resolvedUserId)}`, { headers: authHeaders() });
        if (r.ok){
          const js = await r.json().catch(()=>({}));
          const u  = js.user || js.data || {};
          resolvedUserUuid = u.uuid || u.user_uuid || resolvedUserUuid;
          if (u){
            userSummary.innerHTML =
              `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
          }
        }
      }catch(e){}
      return;
    }

    if (userUuidParam){
      resolvedUserUuid = userUuidParam;
      try{
        const r1 = await fetch(`/api/user/${encodeURIComponent(userUuidParam)}`, { headers: authHeaders() });
        if (r1.ok){
          const js = await r1.json().catch(()=>({}));
          const u  = js.user || js.data || {};
          if (u && (u.id || u.uuid)){
            resolvedUserId   = Number(u.id);
            resolvedUserUuid = u.uuid || resolvedUserUuid;
            userSummary.innerHTML =
              `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
            return;
          }
        }
      }catch(e){}
    }

    throw new Error('Could not resolve user from provided parameters (user_id/user_uuid).');
  }

  async function loadUserInfo(){
    try{
      if (resolvedUserId){
        const res = await fetch(`/api/user/${encodeURIComponent(resolvedUserId)}`, { headers: authHeaders() });
        const js  = await res.json().catch(()=>({}));
        if(res.ok && js.user){
          const u = js.user;
          userSummary.innerHTML =
            `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
          resolvedUserUuid = resolvedUserUuid || (u.uuid || u.user_uuid);
          return;
        }
      } else if (resolvedUserUuid){
        const res = await fetch(`/api/user/${encodeURIComponent(resolvedUserUuid)}`, { headers: authHeaders() });
        const js  = await res.json().catch(()=>({}));
        if(res.ok && js.user){
          const u = js.user;
          userSummary.innerHTML =
            `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
          resolvedUserId   = Number(u.id);
          resolvedUserUuid = u.uuid || u.user_uuid || resolvedUserUuid;
          return;
        }
      }
      userSummary.textContent = 'User information not available';
    }catch(e){
      userSummary.textContent = 'Failed to load user';
    }
  }

  // ========== Privileges: load from DB ==========

  async function loadAssignedPrivileges(){
    assignedPrivIds = new Set();
    if (!resolvedUserId){
      console.warn('No resolved user id when loading assigned privileges');
      return;
    }
    try{
      const res = await fetch(`/api/user-privileges/list?user_id=${encodeURIComponent(resolvedUserId)}`, {
        headers: authHeaders()
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load assigned privileges');
      const data = Array.isArray(js.data) ? js.data : (js.data?.length ? js.data : []);
      for(const r of data){
        const pidKey = r.privilege_id != null
          ? String(r.privilege_id)
          : (r.privilege_uuid ? String(r.privilege_uuid) : null);
        if (pidKey){
          assignedPrivIds.add(pidKey);
        }
      }
    }catch(e){
      console.warn('Could not load assigned privileges', e);
    }
  }

  // ========== Helpers for select-all states ==========

  function updateModuleSelectAllState(moduleEl){
    if (!moduleEl) return;
    const moduleCheckbox = moduleEl.querySelector('.ap-mod-select-all');
    if (!moduleCheckbox) return;
    const rows   = moduleEl.querySelectorAll('.ap-priv-row');
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
    checkboxes.forEach(cb=>{
      if (cb.checked) checkedCount++;
    });
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

  // ========== Modules & privileges ==========

  async function loadModulesWithPrivileges(){
    modulesContainer.innerHTML =
      `<div class="ap-empty">Loading modules &amp; privileges…</div>`;
    modulesEmpty.style.display = 'none';
    try{
      const res = await fetch('/api/modules/all-with-privileges', {
        headers: authHeaders()
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load modules');
      modules = Array.isArray(js.data) ? js.data : (js.data?.length ? js.data : []);
      if(!modules.length){
        modulesContainer.innerHTML = '';
        modulesEmpty.style.display = '';
        chkGlobalSelectAll.checked = false;
        chkGlobalSelectAll.indeterminate = false;
        return;
      }
      await loadAssignedPrivileges(); // load initial assigned set from DB
      renderModules();
    }catch(e){
      console.error(e);
      modulesContainer.innerHTML =
        `<div class="ap-empty text-danger">
           Failed to load modules: ${escapeHtml(e.message || '')}
         </div>`;
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

    modules.forEach((m, index) => {
      const item       = document.createElement('div');
      const collapseId = `ap_mod_${m.id ?? ('i'+index)}`;
      const headerId   = `ap_modh_${m.id ?? ('i'+index)}`;
      const moduleKey  = String(m.id ?? ('i'+index));

      item.className = 'accordion-item';
      item.dataset.moduleId = moduleKey;

      item.innerHTML = `
        <h2 class="accordion-header" id="${headerId}">
          <button class="accordion-button ${index ? 'collapsed' : ''}" type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#${collapseId}"
                  aria-expanded="${index ? 'false':'true'}"
                  aria-controls="${collapseId}">
            <div class="ap-module-header-inner">
              <span class="ap-module-title">${escapeHtml(m.name || ('Module #'+(m.id || (index+1))))}</span>
              <span class="ap-module-pill">
                <i class="fa fa-shield-halved me-1"></i>
                Privileges: ${m.privileges?.length || 0}
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
            </div>
          </div>
        </div>
      `;

      const tbody           = item.querySelector('tbody');
      const moduleSelectAll = item.querySelector('.ap-mod-select-all');

      if(!m.privileges || !m.privileges.length){
        tbody.innerHTML = `
          <tr><td colspan="2">
            <div class="ap-empty ap-small-muted">
              <i class="fa fa-ban"></i>
              <div>No privileges for this module</div>
            </div>
          </td></tr>`;
      }else{
        m.privileges.forEach(p => {
          const pid     = String(p.id ?? p.uuid ?? '');
          const checked = assignedPrivIds.has(pid);
          const tr      = document.createElement('tr');
          tr.className  = 'ap-priv-row';
          tr.dataset.privId   = pid;
          tr.dataset.moduleId = moduleKey;

          const title  = p.action || p.name || 'Untitled';
          const desc   = p.description || '';

          tr.innerHTML = `
            <td class="text-center">
              <label class="ap-toggle-switch mb-0">
                <input type="checkbox" class="sm-privilege-checkbox" ${checked ? 'checked':''}>
                <span class="ap-toggle-slider"></span>
              </label>
            </td>
            <td>
              <div class="ap-priv-title">${escapeHtml(title)}</div>
              ${desc ? `<div class="ap-priv-desc">${escapeHtml(desc)}</div>` : ''}
            </td>
          `;

          const checkbox = tr.querySelector('.sm-privilege-checkbox');

          if (checked){
            tr.classList.add('active');
          }

          checkbox.addEventListener('change', (ev) => {
            const nowChecked = ev.target.checked;
            const key = String(p.id ?? p.uuid ?? pid);

            if (nowChecked) {
              assignedPrivIds.add(key);
              tr.classList.add('active');
            } else {
              assignedPrivIds.delete(key);
              tr.classList.remove('active');
            }

            const moduleEl = tr.closest('.accordion-item');
            updateModuleSelectAllState(moduleEl);
            updateGlobalSelectAllState();
          });

          tbody.appendChild(tr);
        });
      }

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

      modulesContainer.appendChild(item);
    });

    updateAllModulesSelectAllState();
    updateGlobalSelectAllState();
  }

  // ========== Global "Select all" checkbox ==========
  if (chkGlobalSelectAll){
    chkGlobalSelectAll.addEventListener('change', (e)=>{
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
    btnSaveAll.innerHTML =
      '<span class="spinner-border spinner-border-sm me-1"></span>Saving';

    try{
      const currentIds = [];
      modules.forEach(m=>{
        (m.privileges || []).forEach(p=>{
          const key = String(p.id ?? p.uuid ?? '');
          if(assignedPrivIds.has(key)){
            currentIds.push(p.id ? Number(p.id) : String(p.uuid));
          }
        });
      });

      const payload = { privileges: Array.from(new Set(currentIds)) };
      if (resolvedUserId) payload.user_id = Number(resolvedUserId);
      else payload.user_uuid = String(resolvedUserUuid);

      const res = await fetch('/api/user-privileges/sync', {
        method:'POST',
        headers: authHeaders({'Content-Type':'application/json'}),
        body: JSON.stringify(payload)
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Sync failed');

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
      if (!resolvedUserId && !resolvedUserUuid){
        throw new Error('User not found');
      }
      await loadModulesWithPrivileges();
    }catch(e){
      console.error(e);
      if (typeof Swal !== 'undefined') {
        Swal.fire('Cannot continue', e.message || 'Missing or invalid user identifier', 'error');
      }
      modulesContainer.innerHTML =
        `<div class="ap-empty text-danger">
           Cannot load privileges: ${escapeHtml(e.message || '')}
         </div>`;
      chkGlobalSelectAll.checked = false;
      chkGlobalSelectAll.indeterminate = false;
    }
  })();

});
</script>
@endpush
