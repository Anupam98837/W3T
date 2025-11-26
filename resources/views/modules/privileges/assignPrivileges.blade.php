{{-- resources/views/users/managePrivileges.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title', 'Manage User Privileges')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
/* reuse look and feel from modules page with minor tweaks */
.cm-wrap{max-width:1140px;margin:16px auto 40px}
.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:12px;padding:12px}
.module-card{border:1px solid var(--line-strong);border-radius:12px;padding:12px;margin-bottom:12px;background:var(--surface);box-shadow:var(--shadow-1)}
.module-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
.module-title{font-weight:700}
.priv-list{margin-top:10px}
.priv-item{display:flex;align-items:center;justify-content:space-between;padding:8px;border-radius:8px;border:1px solid var(--line-soft);margin-bottom:6px;background:transparent}
.priv-left{display:flex;align-items:center;gap:10px}
.priv-action{font-family:monospace; font-size:0.95rem; color:var(--ink)}
.priv-desc{font-size:0.85rem;color:var(--muted-color)}
.card-empty{padding:18px;text-align:center;color:var(--muted-color)}
.assign-actions{display:flex;gap:8px;align-items:center}
.small-muted{font-size:13px;color:var(--muted-color)}
</style>
@endpush

@section('content')
<div class="cm-wrap">
  <div class="row g-2 mb-3 align-items-center panel">
    <div class="col">
      <h4 class="mb-0">Manage Privileges for User</h4>
      <div id="userSummary" class="small-muted">Loading user…</div>
    </div>
    <div class="col-auto d-flex gap-2">
      <a href="javascript:history.back()" class="btn btn-light"><i class="fa fa-arrow-left me-1"></i>Back</a>
      <button id="btnRefresh" class="btn btn-light"><i class="fa fa-rotate-right me-1"></i>Refresh</button>
      <button id="btnSaveAll" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
    </div>
  </div>

  <div id="modulesContainer">
    <div class="card-empty">Loading modules & privileges…</div>
  </div>

  <div class="small-muted mt-3">Tip: check/uncheck privileges and press <b>Save</b>. Unchecking removes privilege (will be synced).</div>
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
  const params = new URLSearchParams(location.search);
  const userId = params.get('user_id') || params.get('id') || '';
  const token = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!token) { Swal.fire('Login required','Please login again','warning').then(()=>location.href='/'); return; }
  if(!userId){ Swal.fire('Missing user','No user specified.','error'); return; }

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
  let isSaving = false;

  // load user basic info (optional, just for header)
  async function loadUserInfo(){
    try {
      const res = await fetch(`/api/users/${encodeURIComponent(userId)}`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(res.ok && js.user){
        const u = js.user;
        userSummary.innerHTML = `<strong>${escapeHtml(u.name || u.email || ('#'+u.id))}</strong> — ${escapeHtml(u.email || '')}`;
      } else {
        userSummary.textContent = 'User information not available';
      }
    } catch(e){
      userSummary.textContent = 'Failed to load user';
    }
  }

  // load all modules with privileges (non-deleted)
  async function loadModulesWithPrivileges(){
    modulesContainer.innerHTML = `<div class="card-empty">Loading modules & privileges…</div>`;
    try {
      const res = await fetch('/api/modules/all-with-privileges', { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load modules');
      modules = Array.isArray(js.data) ? js.data : (js.data?.length ? js.data : []);
      if(!modules.length){
        modulesContainer.innerHTML = `<div class="card-empty">No modules or privileges found.</div>`;
        return;
      }
      await loadAssignedPrivileges();
      renderModules();
    } catch(e){
      console.error(e);
      modulesContainer.innerHTML = `<div class="card-empty text-danger">Failed to load modules: ${escapeHtml(e.message || '')}</div>`;
    }
  }

  // load assigned privileges for this user
  async function loadAssignedPrivileges(){
    assignedPrivIds = new Set();
    try {
      // we expect endpoint: GET /api/user-privileges/list?user_id=ID
      const res = await fetch(`/api/user-privileges/list?user_id=${encodeURIComponent(userId)}`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load assigned privileges');
      const data = Array.isArray(js.data) ? js.data : (js.data?.length ? js.data : []);
      // the user_privileges list returns rows with privilege_id (int) or uuid depending on your schema.
      // prefer numeric id if present
      for(const r of data){
        if(r.privilege_id != null) assignedPrivIds.add(String(r.privilege_id));
        else if(r.id != null) assignedPrivIds.add(String(r.id));
        else if(r.uuid) assignedPrivIds.add(String(r.uuid));
      }
    } catch(e){
      console.warn('Could not load assigned privileges', e);
    }
  }

  function escapeHtml(s){ return (s||'').toString().replace(/[&<>"'`]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}[ch])); }

  // Render modules -> privs tree
  function renderModules(){
    modulesContainer.innerHTML = '';
    modules.forEach(m => {
      const card = document.createElement('div');
      card.className = 'module-card';

      const head = document.createElement('div');
      head.className = 'module-head';
      head.innerHTML = `<div>
        <div class="module-title">${escapeHtml(m.name || ('#'+m.id))}</div>
        <div class="small-muted">${escapeHtml(m.description || '')}</div>
      </div>
      <div class="small-muted">Privileges: ${ (m.privileges?.length || 0) }</div>`;

      card.appendChild(head);

      const list = document.createElement('div');
      list.className = 'priv-list';

      if(!m.privileges || !m.privileges.length){
        list.innerHTML = `<div class="card-empty small-muted">No privileges for this module.</div>`;
      } else {
        m.privileges.forEach(p => {
          // p: id, uuid, action, description
          const pid = String(p.id ?? p.uuid ?? '');
          const checked = assignedPrivIds.has(pid) ? 'checked' : '';
          const row = document.createElement('div');
          row.className = 'priv-item';
          row.dataset.privId = pid;

          row.innerHTML = `
            <div class="priv-left">
              <input type="checkbox" class="priv-checkbox" ${checked} />
              <div>
                <div class="priv-action">${escapeHtml(p.action || p.name || '')}</div>
                <div class="priv-desc">${escapeHtml(p.description || '')}</div>
              </div>
            </div>
            <div class="assign-actions">
              <button class="btn btn-sm btn-light btn-unassign" title="Unassign"><i class="fa fa-ban"></i></button>
            </div>
          `;

          // toggle checkbox handler
          row.querySelector('.priv-checkbox').addEventListener('change', (ev)=>{
            // visually update set only; final commit on Save; or we can do immediate sync on uncheck if desired
            const cb = ev.target;
            if(cb.checked) assignedPrivIds.add(pid);
            else assignedPrivIds.delete(pid);
          });

          // single unassign button (per privilege) - calls delete endpoint immediately
          row.querySelector('.btn-unassign').addEventListener('click', async ()=>{
            if(!confirm('Unassign this privilege from user?')) return;
            try {
              const body = { user_id: Number(userId), privilege_id: Number(p.id ?? 0) };
              // if your API expects uuid, send privilege_id as uuid string instead
              if(!p.id && p.uuid) body.privilege_id = p.uuid;
              const res = await fetch('/user-privileges/delete', {
                method: 'POST',
                headers: authHeaders({'Content-Type':'application/json'}),
                body: JSON.stringify(body)
              });
              const js = await res.json().catch(()=>({}));
              if(!res.ok) throw new Error(js.message || 'Unassign failed');
              // reflect locally
              assignedPrivIds.delete(pid);
              row.querySelector('.priv-checkbox').checked = false;
              ok('Privilege unassigned');
            } catch(e){
              console.error(e);
              err(e.message || 'Failed to unassign');
            }
          });

          list.appendChild(row);
        });
      }

      card.appendChild(list);
      modulesContainer.appendChild(card);
    });
  }

  // Save all changes (sync)
  btnSaveAll.addEventListener('click', async ()=>{
    if(isSaving) return;
    isSaving = true;
    btnSaveAll.disabled = true;
    btnSaveAll.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving';

    try {
      // prepare array of privilege ids (prefer numeric ids if available)
      // We need to gather actual numeric IDs — modules[].privileges[].id
      // Our assignedPrivIds set contains string ids (either numeric or uuid). We'll send numeric ones preferentially.
      const currentIds = [];
      modules.forEach(m=>{
        (m.privileges || []).forEach(p=>{
          const pid = String(p.id ?? p.uuid ?? '');
          if(assignedPrivIds.has(pid)){
            // push numeric id if available, else uuid
            currentIds.push( p.id ? Number(p.id) : String(p.uuid) );
          }
        });
      });

      const payload = { user_id: Number(userId), privileges: Array.from(new Set(currentIds)) };

      const res = await fetch('/api/user-privileges/sync', {
        method: 'POST',
        headers: authHeaders({'Content-Type':'application/json'}),
        body: JSON.stringify(payload)
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Sync failed');

      ok('Privileges synced');
      // refresh assigned list from server
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
    await loadAssignedPrivileges();
    renderModules();
    ok('Refreshed');
  });

  // initial load
  (async ()=>{
    await loadUserInfo();
    await loadModulesWithPrivileges();
  })();

});
</script>
@endpush
