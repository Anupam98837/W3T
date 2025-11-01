{{-- resources/views/modules/users/manageUsers.blade.php --}}
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<div class="container-fluid px-0">
  {{-- Heading + actions --}}
  <div class="panel shadow-1 rounded-1 mb-3">
    <div class="panel-head">
      <div>
        <h1 class="panel-title mb-0">Users <span class="text-muted fs-6" id="usersCount">—</span></h1>
        <div class="panel-sub">Manage platform users (roles: Super Admin, Admin, Instructor, Student, Author)</div>
      </div>
      <div class="d-flex gap-2 align-items-center" id="writeControls" style="display:none;">
        <button class="btn btn-primary btn-sm" id="btnAddUser">
          <i class="fa fa-plus me-1"></i> Add User
        </button>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="panel shadow-1 rounded-1 mb-3">
    <div class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <label class="form-label mb-1">Rows</label>
        <select id="perPage" class="form-select">
          <option>10</option><option>20</option><option>50</option><option>100</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label mb-1">Status</label>
        <select id="statusFilter" class="form-select">
          <option value="all">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label mb-1">Role</label>
        <select id="roleFilter" class="form-select">
          <option value="">All</option>
          <option value="super_admin">Super Admin</option>
          <option value="admin">Admin</option>
          <option value="instructor">Instructor</option>
          <option value="student">Student</option>
          <option value="author">Author</option>
        </select>
      </div>
      <div class="col-12 col-md-5">
        <label class="form-label mb-1">Search</label>
        <input id="searchInput" type="search" class="form-control" placeholder="Search by name or email…">
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card shadow-1 rounded-1">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:88px;">Active</th>
            <th style="width:74px;">Avatar</th>
            <th>Name</th>
            <th>Email</th>
            <th style="width:160px;">Role</th>
            <th style="width:180px;" class="text-end">Action</th>
          </tr>
        </thead>
        <tbody id="usersTbody">
          <tr>
            <td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div id="resultsInfo" class="text-muted"></div>
      <nav>
        <ul class="pagination mb-0" id="pager"></ul>
      </nav>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="userForm" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalTitle">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="userId"/>

        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input class="form-control" id="userName" required maxlength="150" placeholder="John Doe"/>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="userEmail" required maxlength="255" placeholder="john.doe@example.com"/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" id="userPhone" maxlength="32" placeholder="+91 99999 99999"/>
          </div>

          <div class="col-md-6">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select class="form-select" id="userRole" required>
              <option value="">Select Role</option>
              <option value="super_admin">Super Admin</option>
              <option value="admin">Admin</option>
              <option value="instructor">Instructor</option>
              <option value="student">Student</option>
              <option value="author">Author</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" id="userStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
            <input type="password" class="form-control" id="userPassword" placeholder="••••••••"/>
            <div class="form-text" id="passwordHelp">Enter password for new user</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="userPasswordConfirmation" placeholder="••••••••"/>
          </div>

          {{-- NEW: Optional profile/contact fields --}}
          <div class="col-md-6">
            <label class="form-label">Alt. Email</label>
            <input type="email" class="form-control" id="userAltEmail" maxlength="255" placeholder="alt@example.com"/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Alt. Phone</label>
            <input class="form-control" id="userAltPhone" maxlength="32" placeholder="+91 88888 88888"/>
          </div>
          <div class="col-md-6">
            <label class="form-label">WhatsApp</label>
            <input class="form-control" id="userWhatsApp" maxlength="32" placeholder="+91 77777 77777"/>
          </div>
          <div class="col-md-12">
            <label class="form-label">Address</label>
            <textarea class="form-control" id="userAddress" rows="2" placeholder="Street, City, State, ZIP"></textarea>
          </div>

          <div class="col-md-12">
            <label class="form-label">Avatar (optional)</label>
            <div class="d-flex align-items-center gap-2">
              <img id="imagePreview" alt="Preview" style="width:48px;height:48px;border-radius:10px;object-fit:cover;display:none;border:1px solid var(--line-strong);">
              <input type="file" id="userImage" accept="image/*" class="form-control">
            </div>
            <div class="form-text">PNG, JPG, WEBP, GIF, SVG up to 5MB.</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveUserBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

{{-- Dependencies (SweetAlert2 + jQuery for convenience) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
  // Guard: must have token in sessionStorage or localStorage
  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  // Current actor role from storage (lowercased)
  const roleStored = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
  const canWrite   = (roleStored === 'admin' || roleStored === 'super_admin');
  const canDelete  = (roleStored === 'super_admin'); // Admins cannot delete

  if (canWrite) document.getElementById('writeControls').style.display = 'flex';

  // UI elements
  const tbody     = document.getElementById('usersTbody');
  const pager     = document.getElementById('pager');
  const info      = document.getElementById('resultsInfo');
  const countEl   = document.getElementById('usersCount');

  const perPageSel  = document.getElementById('perPage');
  const statusSel   = document.getElementById('statusFilter');
  const roleSel     = document.getElementById('roleFilter');
  const searchInput = document.getElementById('searchInput');

  // Modal elements
  const userModalEl = document.getElementById('userModal');
  const userModal   = new bootstrap.Modal(userModalEl);
  const form        = document.getElementById('userForm');
  const modalTitle  = document.getElementById('userModalTitle');
  const saveBtn     = document.getElementById('saveUserBtn');

  const idInput     = document.getElementById('userId');
  const nameInput   = document.getElementById('userName');
  const emailInput  = document.getElementById('userEmail');
  const phoneInput  = document.getElementById('userPhone');
  const roleInput   = document.getElementById('userRole');
  const statusInput = document.getElementById('userStatus');
  const pwdInput    = document.getElementById('userPassword');
  const pwd2Input   = document.getElementById('userPasswordConfirmation');
  const imgInp      = document.getElementById('userImage');
  const imgPrev     = document.getElementById('imagePreview');
  const pwdReq      = document.getElementById('passwordRequired');
  const pwdHelp     = document.getElementById('passwordHelp');
  const btnAdd      = document.getElementById('btnAddUser');

  // NEW: extra field refs
  const altEmailInput = document.getElementById('userAltEmail');
  const altPhoneInput = document.getElementById('userAltPhone');
  const waInput       = document.getElementById('userWhatsApp');
  const addrInput     = document.getElementById('userAddress');

  // Toast helpers
  const toastOk  = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt    = document.getElementById('toastSuccessText');
  const errTxt   = document.getElementById('toastErrorText');
  const ok  = m => { okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = m => { errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  const ROLE_LABEL = {
    super_admin: 'Super Admin',
    admin: 'Admin',
    instructor: 'Instructor',
    student: 'Student',
    author: 'Author'
  };
  const roleLabel = v => ROLE_LABEL[(v||'').toLowerCase()] || (v||'');

  function authHeaders(extra={}){ return Object.assign({'Authorization':'Bearer '+token}, extra); }
  function escapeHtml(str){ return (str ?? '').toString().replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s])); }
  function debounce(fn,ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }

  // State
  let page=1, perPage=10, q='', statusFilter='all', roleFilter='', totalPages=1, totalCount=0;
  let lastRows = [];

  // Initial control values
  perPage = parseInt(perPageSel.value,10) || 10;
  statusFilter = statusSel.value; // default 'all'
  roleFilter = roleSel.value;

  // Fetch + render
  async function fetchUsers(){
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
    const params = new URLSearchParams({
      page:String(page),
      per_page:String(perPage),
      q:q,
      status:statusFilter // 'all' | 'active' | 'inactive'
    });
    const res = await fetch(`/api/users?${params.toString()}`, { headers: authHeaders() });
    if (res.status === 401) { window.location.href='/'; return; }
    let json; try{ json = await res.json(); }catch{ json = {}; }
    if (!res.ok) throw new Error(json.message || 'Failed to load users');

    lastRows   = Array.isArray(json.data) ? json.data : [];
    totalPages = json.meta?.total_pages || 1;
    totalCount = json.meta?.total || (lastRows.length || 0);

    // Client-side role filter on the current page (API doesn't filter by role)
    let rows = lastRows;
    if (roleFilter) rows = rows.filter(r => (r.role||'').toLowerCase() === roleFilter);

    renderTable(rows);
    renderPager();

    const shown = rows.length;
    info.textContent = shown
      ? `Showing ${(page-1)*perPage + 1} to ${(page-1)*perPage + shown} of ${totalCount} entries`
      : `0 of ${totalCount}`;
    countEl.textContent = `${totalCount} total`;
  }

  function renderTable(rows){
    if (!rows.length){
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted" style="padding:38px;">No users found</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(row => {
      const r = (row.role||'').toLowerCase();
      const active = row.status === 'active';
      const avatar = row.image
        ? `<img src="${escapeHtml(row.image)}" alt="avatar" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid var(--line-strong);" loading="lazy">`
        : `<div style="width:40px;height:40px;border-radius:10px;border:1px solid var(--line-strong);display:flex;align-items:center;justify-content:center;color:#9aa3b2;">—</div>`;

      const toggle = canWrite
        ? `<div class="form-check form-switch m-0">
             <input class="form-check-input js-toggle" type="checkbox" ${active?'checked':''} title="Toggle Active">
           </div>`
        : `<span class="badge ${active?'badge-soft-success':'badge-soft-danger'}">${active?'Active':'Inactive'}</span>`;

      // Action buttons by actor role
      let actionHtml = '';
      if (canWrite){
        actionHtml += `
          <button class="icon-btn me-1" data-action="view" title="View"><i class="fa fa-eye"></i></button>
          <button class="icon-btn me-1" data-action="edit" title="Edit"><i class="fa fa-pen"></i></button>
        `;
        if (canDelete){
          actionHtml += `<button class="icon-btn" data-action="delete" title="Delete" style="border-color:#ef4444;color:#b91c1c;"><i class="fa fa-trash"></i></button>`;
        }
      } else {
        actionHtml = `<button class="icon-btn" data-action="view" title="View"><i class="fa fa-eye"></i></button>`;
      }

      return `
        <tr data-id="${row.id}">
          <td>${toggle}</td>
          <td>${avatar}</td>
          <td class="fw-semibold">${escapeHtml(row.name || '')}</td>
          <td>${row.email ? `<a href="mailto:${escapeHtml(row.email)}">${escapeHtml(row.email)}</a>` : '<span class="text-muted">—</span>'}</td>
          <td><span class="badge badge-soft-primary"><i class="fa fa-user-shield me-1"></i>${escapeHtml(roleLabel(r))}</span></td>
          <td class="text-end">${actionHtml}</td>
        </tr>`;
    }).join('');
  }

  function renderPager(){
    let html = '';
    function item(p, label, dis = false, act = false){
      if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
      if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
      return `<li class="page-item"><a class="page-link" href="#" data-page="${p}">${label}</a></li>`;
    }
    html += item(Math.max(1, page-1), 'Previous', page<=1);
    const st = Math.max(1, page-2), en = Math.min(totalPages, page+2);
    for (let p = st; p <= en; p++) html += item(p, p, false, p===page);
    html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);
    pager.innerHTML = html;
  }

  // Events: pager/search/filters
  pager.addEventListener('click', e=>{
    const a = e.target.closest('a.page-link'); if (!a) return;
    e.preventDefault();
    const p = parseInt(a.dataset.page,10);
    if (!Number.isNaN(p) && p !== page){ page=p; fetchUsers().catch(ex=>err(ex.message)); window.scrollTo({top:0,behavior:'smooth'}); }
  });

  const onSearch = debounce(()=>{ q = searchInput.value.trim(); page=1; fetchUsers().catch(ex=>err(ex.message)); }, 320);
  searchInput.addEventListener('input', onSearch);

  perPageSel.addEventListener('change', ()=>{ perPage=parseInt(perPageSel.value,10)||10; page=1; fetchUsers().catch(ex=>err(ex.message)); });
  statusSel.addEventListener('change', ()=>{ statusFilter=statusSel.value; page=1; fetchUsers().catch(ex=>err(ex.message)); });
  roleSel.addEventListener('change', ()=>{ roleFilter=roleSel.value; renderTable(filterByRole(lastRows)); });
  function filterByRole(rows){ if (!roleFilter) return rows; return (rows||[]).filter(r => (r.role||'').toLowerCase()===roleFilter); }

  // Row: toggle active
  tbody.addEventListener('change', async (e)=>{
    const sw = e.target.closest('.js-toggle'); if (!sw) return;
    if (!canWrite){ sw.checked = !sw.checked; return; }
    const tr = sw.closest('tr'); const id = tr?.dataset?.id; if(!id) return;
    const willActive = sw.checked;

    const conf = await Swal.fire({
      title:'Confirm',
      text: willActive ? 'Activate this user?' : 'Deactivate this user?',
      icon:'question', showCancelButton:true, confirmButtonText:'Yes'
    });
    if (!conf.isConfirmed){ sw.checked = !willActive; return; }

    try{
      const res = await fetch(`/api/users/${id}`, {
        method:'PATCH',
        headers: { ...authHeaders({'Content-Type':'application/json'}) },
        body: JSON.stringify({ status: willActive ? 'active' : 'inactive' })
      });
      const js = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(js.message || 'Status update failed');
      ok('Status updated');
      fetchUsers().catch(()=>{});
    }catch(ex){ err(ex.message); sw.checked = !sw.checked; }
  });

  // Row: actions
  tbody.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-action]'); if(!btn) return;
    const tr = btn.closest('tr'); const id = tr?.dataset?.id; if(!id) return;

    const spin=()=>{ btn.disabled=true; btn.dataset._old = btn.innerHTML; btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>'; }
    const un  =()=>{ btn.disabled=false; btn.innerHTML = btn.dataset._old || btn.innerHTML; }

    const act = btn.dataset.action;
    if (act==='view'){
      spin(); openEdit(id, true).catch(ex=>err(ex.message)).finally(un);
    } else if (act==='edit'){
      if(!canWrite) return;
      spin(); openEdit(id, false).catch(ex=>err(ex.message)).finally(un);
    } else if (act==='delete'){
      if(!canDelete) return;
      Swal.fire({
        title:'Delete user?',
        text:'This performs a soft delete (removes from list).',
        icon:'warning', showCancelButton:true,
        confirmButtonText:'Delete', confirmButtonColor:'#ef4444'
      }).then(async r=>{
        if(!r.isConfirmed) return;
        try{
          spin();
          const res = await fetch(`/api/users/${id}`, { method:'DELETE', headers: authHeaders() });
          const js = await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(js.message || 'Delete failed');
          ok('User deleted');
          fetchUsers().catch(()=>{});
        }catch(ex){ err(ex.message); } finally{ un(); }
      });
    }
  });

  // Add user
  btnAdd?.addEventListener('click', ()=>{
    resetForm();
    modalTitle.textContent = 'Add User';
    pwdReq.style.display = 'inline';
    pwdHelp.textContent = 'Enter password for new user';
    userModal.show();
  });

  // Image preview
  imgInp.addEventListener('change', ()=>{
    const f = imgInp.files?.[0];
    if (!f){ imgPrev.style.display='none'; imgPrev.src=''; return; }
    const reader = new FileReader();
    reader.onload = e => { imgPrev.src = e.target.result; imgPrev.style.display='block'; };
    reader.readAsDataURL(f);
  });

  // Submit (create/update)
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!canWrite) return;

    // Basic validations
    if (!nameInput.value.trim()) return nameInput.focus();
    if (!emailInput.value.trim()) return emailInput.focus();
    if (!roleInput.value) return roleInput.focus();

    const isEdit = !!idInput.value;

    if (!isEdit && !pwdInput.value.trim()){
      pwdInput.focus(); return;
    }
    if (pwdInput.value.trim() && pwdInput.value !== pwd2Input.value){
      err('Passwords do not match'); pwd2Input.focus(); return;
    }

    const url = isEdit ? `/api/users/${idInput.value}` : '/api/users';
    const fd = new FormData();
    if (isEdit) fd.append('_method','PUT');

    fd.append('name', nameInput.value.trim());
    fd.append('email', emailInput.value.trim());
    if (phoneInput.value) fd.append('phone_number', phoneInput.value.trim());
    if (altEmailInput.value) fd.append('alternative_email', altEmailInput.value.trim());
    if (altPhoneInput.value) fd.append('alternative_phone_number', altPhoneInput.value.trim());
    if (waInput.value)       fd.append('whatsapp_number', waInput.value.trim());
    if (addrInput.value)     fd.append('address', addrInput.value.trim());
    fd.append('role', roleInput.value);
    if (statusInput.value) fd.append('status', statusInput.value);
    if (!isEdit && pwdInput.value.trim()) fd.append('password', pwdInput.value.trim());
    if (imgInp.files?.[0]) fd.append('image', imgInp.files[0]);

    try{
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving';
      const res = await fetch(url, { method:'POST', headers: authHeaders(), body: fd });
      let js  = await res.json().catch(()=>({}));
      if (!res.ok){
        let msg = js.message || 'Save failed';
        if (js.errors){
          const k = Object.keys(js.errors)[0];
          if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
        }
        throw new Error(msg);
      }

      // If editing and password provided -> separate endpoint
      if (isEdit && pwdInput.value.trim()){
        const res2 = await fetch(`/api/users/${idInput.value}/password`, {
          method:'PATCH',
          headers: { ...authHeaders({'Content-Type':'application/json'}) },
          body: JSON.stringify({ password: pwdInput.value.trim() })
        });
        const js2 = await res2.json().catch(()=>({}));
        if (!res2.ok) throw new Error(js2.message || 'Password update failed');
      }

      userModal.hide();
      ok(isEdit ? 'User updated' : 'User created');
      fetchUsers().catch(()=>{});
    }catch(ex){
      err(ex.message);
    }finally{
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="fa fa-floppy-disk me-1"></i> Save';
    }
  });

  async function openEdit(id, viewOnly=false){
    const res = await fetch(`/api/users/${id}`, { headers: authHeaders() });
    if (res.status === 401) { window.location.href='/'; return; }
    const js = await res.json().catch(()=>({}));
    if (!res.ok) throw new Error(js.message || 'Failed to fetch user');

    const u = js.user || {};
    resetForm();

    idInput.value     = u.id || '';
    nameInput.value   = u.name || '';
    emailInput.value  = u.email || '';
    phoneInput.value  = u.phone_number || '';
    altEmailInput.value = u.alternative_email || '';
    altPhoneInput.value = u.alternative_phone_number || '';
    waInput.value       = u.whatsapp_number || '';
    addrInput.value     = u.address || '';
    roleInput.value   = (u.role || '').toLowerCase();
    statusInput.value = u.status || 'active';

    if (u.image){ imgPrev.src = u.image; imgPrev.style.display='block'; }

    modalTitle.textContent = viewOnly ? 'View User' : 'Edit User';
    saveBtn.style.display  = viewOnly ? 'none' : '';

    // Lock fields for viewOnly
    Array.from(form.querySelectorAll('input,select,textarea')).forEach(el=>{
      if (el === imgInp) el.disabled = viewOnly;
      if (el.tagName === 'SELECT') el.disabled = viewOnly;
      if (el.tagName !== 'SELECT') el.readOnly = viewOnly;
    });
    pwdReq.style.display = 'none';
    pwdHelp.textContent = 'Leave blank to keep current password';

    userModal.show();
  }

  function resetForm(){
    form.reset(); idInput.value=''; imgPrev.src=''; imgPrev.style.display='none';
    saveBtn.style.display='';
    Array.from(form.querySelectorAll('input,select,textarea')).forEach(el=>{
      if (el === imgInp) el.disabled = false;
      if (el.tagName === 'SELECT') el.disabled = false;
      if (el.tagName !== 'SELECT') el.readOnly = false;
    });
    statusInput.value = 'active';
    pwdReq.style.display = 'inline';
    pwdHelp.textContent = 'Enter password for new user';
  }

  // Kick off
  fetchUsers().catch(ex=>err(ex.message));
});
</script>
