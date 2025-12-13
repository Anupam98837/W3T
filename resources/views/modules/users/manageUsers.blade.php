{{-- resources/views/modules/users/manageUsers.blade.php --}}
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:1085}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Ensure overflow visibility for dropdowns */
.table-responsive{overflow:visible !important}
.card-body{overflow:visible !important}
</style>

<div class="crs-wrap">

  {{-- ================= Filters ================= --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Per Page</label>
        <select id="perPage" class="form-select" style="width:96px;">
          <option>10</option><option>20</option><option>50</option><option>100</option>
        </select>
      </div>

      <div class="position-relative" style="min-width:300px;">
        <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by name or email…">
        <i class="fa fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
      </div>

      {{-- Filter Button to match courses page --}}
      <button id="btnFilter" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="fa fa-filter me-1"></i>Filter
      </button>

      <button id="btnReset" class="btn btn-primary"><i class="fa fa-rotate-left me-1"></i>Reset</button>
    </div>
    
    <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
      <div id="writeControls" style="display:none;">
        <button type="button" class="btn btn-primary" id="btnAddUser">
          <i class="fa fa-plus me-1"></i> Add User
        </button>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top" style="z-index:2;">
            <tr>
              <th style="width:88px;">Active</th>
              <th style="width:74px;">Avatar</th>
              <th>Name</th>
              <th>Email</th>
              <th style="width:160px;">Role</th>
              <th style="width:108px;" class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="usersTbody">
            <tr>
              <td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- Footer: pagination --}}
      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="resultsInfo">—</div>
        <nav><ul id="pager" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>
</div>

{{-- ================= Filter Users Modal ================= --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filter Users</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          {{-- Status --}}
          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          {{-- Role --}}
          <div class="col-12">
            <label class="form-label">Role</label>
            <select id="modal_role" class="form-select">
              <option value="">All Roles</option>
              <option value="super_admin">Super Admin</option>
              <option value="admin">Admin</option>
              <option value="instructor">Instructor</option>
              <option value="student">Student</option>
              <option value="author">Author</option>
            </select>
          </div>

          {{-- Sort By --}}
          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="name">Name A-Z</option>
              <option value="-name">Name Z-A</option>
              <option value="email">Email A-Z</option>
              <option value="-email">Email Z-A</option>
            </select>
          </div>
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

          {{-- Optional profile/contact fields --}}
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Delegated handler for dropdown toggles
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;

  try {
    const inst = bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: btn.getAttribute('data-bs-auto-close') || undefined,
      boundary: btn.getAttribute('data-bs-boundary') || 'viewport'
    });
    inst.toggle();
  } catch (ex) {
    console.error('Dropdown toggle error', ex);
  }
});


document.addEventListener('DOMContentLoaded', function(){

  /* ==========================================================
      AUTH / ROLE CHECK
  ========================================================== */
  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const roleStored = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
  const canWrite   = (roleStored === 'admin' || roleStored === 'super_admin');
  const canDelete  = (roleStored === 'super_admin');

  try { if (canWrite) document.getElementById('writeControls').style.display = 'flex'; } catch(e){}


  /* ==========================================================
      DOM ELEMENTS
  ========================================================== */
  const tbody     = document.getElementById('usersTbody');
  const pager     = document.getElementById('pager');
  const info      = document.getElementById('resultsInfo');

  const perPageSel  = document.getElementById('perPage');
  const searchInput = document.getElementById('searchInput');

  // Filter modal
  const modalStatus = document.getElementById('modal_status');
  const modalRole   = document.getElementById('modal_role');
  const modalSort   = document.getElementById('modal_sort');
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const btnReset        = document.getElementById('btnReset');

  // User modal
  const userModalEl = document.getElementById('userModal');
  const userModal   = userModalEl ? new bootstrap.Modal(userModalEl) : null;
  const form        = document.getElementById('userForm');
  const modalTitle  = document.getElementById('userModalTitle');
  const saveBtn     = document.getElementById('saveUserBtn');

  // Inputs
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

  // Extra fields
  const altEmailInput = document.getElementById('userAltEmail');
  const altPhoneInput = document.getElementById('userAltPhone');
  const waInput       = document.getElementById('userWhatsApp');
  const addrInput     = document.getElementById('userAddress');

  // Toasts
  const toastOk  = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt    = document.getElementById('toastSuccessText');
  const errTxt   = document.getElementById('toastErrorText');
  const ok = m => { okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err= m => { errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };


  /* ==========================================================
      HELPERS
  ========================================================== */
  function authHeaders(extra={}){ return Object.assign({'Authorization':'Bearer '+token}, extra); }

  function escapeHtml(str){
    return (str ?? '').toString().replace(/[&<>"']/g,
      s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s])
    );
  }
  function debounce(fn,ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }
  function fixImageUrl(url){
    if (!url) return null;
    url = String(url).trim();
    if (!url) return null;
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//')) return url;
    if (url.startsWith('/')) return url;
    return '/' + url;
  }


  /* ==========================================================
      STATE
  ========================================================== */
  let page=1, perPage=10, q='', statusFilter='all', roleFilter='', sort='-created_at';
  let totalPages=1, totalCount=0;
  let lastRows = [];

  if (perPageSel) perPage = parseInt(perPageSel.value,10) || 10;


  /* ==========================================================
      FETCH USERS
  ========================================================== */
  async function fetchUsers(){
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
    const params = new URLSearchParams({
      page:String(page),
      per_page:String(perPage),
      q:q,
      sort:sort
    });

    if (statusFilter !== 'all') params.set('status', statusFilter);
    if (roleFilter) params.set('role', roleFilter);

    const res = await fetch(`/api/users?${params.toString()}`, { headers: authHeaders() });
    if (res.status === 401) { window.location.href='/'; return; }

    let json = await res.json().catch(()=>({}));
    if (!res.ok) throw new Error(json.message || 'Failed to load users');

    lastRows   = Array.isArray(json.data) ? json.data : [];
    totalPages = json.meta?.total_pages || 1;
    totalCount = json.meta?.total || 0;

    renderTable(lastRows);
    renderPager();

    const shown = lastRows.length;
    info.textContent = shown
      ? `Showing ${(page-1)*perPage + 1} to ${(page-1)*perPage + shown} of ${totalCount} entries`
      : `0 of ${totalCount}`;
  }


  /* ==========================================================
      RENDER USERS TABLE
  ========================================================== */
  function renderTable(rows){
    if (!rows.length){
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted" style="padding:38px;">No users found</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(row=>{
      const r = (row.role||'').toLowerCase();
      const active = row.status === 'active';

      // Safely extract UUID (backend may return uuid or user_uuid)
      const userUuid = row.uuid || row.user_uuid || '';

      const fixedImageUrl = fixImageUrl(row.image);
      const avatar = fixedImageUrl
        ? `<img src="${escapeHtml(fixedImageUrl)}" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid var(--line-strong);" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">`
        : '';

      const avatarFallback =
        `<div style="width:40px;height:40px;border-radius:10px;border:1px solid var(--line-strong);display:${fixedImageUrl?'none':'flex'};align-items:center;justify-content:center;color:#9aa3b2;">—</div>`;

      const toggle = canWrite
        ? `<div class="form-check form-switch m-0">
            <input class="form-check-input js-toggle" type="checkbox" ${active?'checked':''}>
           </div>`
        : `<span class="badge ${active?'badge-soft-success':'badge-soft-danger'}">${active?'Active':'Inactive'}</span>`;

      return `
<tr data-id="${escapeHtml(String(row.id))}" data-uuid="${escapeHtml(String(userUuid))}">
  <td>${toggle}</td>
  <td>${avatar}${avatarFallback}</td>
  <td class="fw-semibold">${escapeHtml(row.name)}</td>
  <td>${row.email?`<a href="mailto:${escapeHtml(row.email)}">${escapeHtml(row.email)}</a>`:'<span class="text-muted">—</span>'}</td>
  <td><span class="badge badge-soft-primary">${escapeHtml(r)}</span></td>
  <td class="text-end">
    <div class="dropdown">
      <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown">
        <i class="fa fa-ellipsis-vertical"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
        ${canWrite?`<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen"></i> Edit</button></li>`:''}
        <li><button type="button" class="dropdown-item" data-action="manage-privileges"><i class="fa fa-shield-alt"></i> Manage Privileges</button></li>
        ${canDelete?`<li><hr class="dropdown-divider"></li><li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>`:''}
      </ul>
    </div>
  </td>
</tr>`;
    }).join('');
  }


  /* ==========================================================
      PAGER
  ========================================================== */
  function renderPager(){
    let html='';
    const item=(p,l,dis=false,act=false)=>{
      if(dis) return `<li class="page-item disabled"><span class="page-link">${l}</span></li>`;
      if(act) return `<li class="page-item active"><span class="page-link">${l}</span></li>`;
      return `<li class="page-item"><a class="page-link" data-page="${p}" href="#">${l}</a></li>`;
    };
    html+=item(Math.max(1,page-1),'Previous',page<=1);
    const st=Math.max(1,page-2), en=Math.min(totalPages,page+2);
    for(let p=st;p<=en;p++) html+=item(p,p,false,p===page);
    html+=item(Math.min(totalPages,page+1),'Next',page>=totalPages);
    pager.innerHTML=html;
  }


  /* ==========================================================
      PAGER CLICK
  ========================================================== */
  pager.addEventListener('click',e=>{
    const a=e.target.closest('a.page-link'); if(!a) return;
    e.preventDefault();
    const p=parseInt(a.dataset.page,10);
    if(p!==page){ page=p; fetchUsers(); }
  });


  /* ==========================================================
      SEARCH
  ========================================================== */
  searchInput.addEventListener('input', debounce(()=>{
    q=searchInput.value.trim();
    page=1;
    fetchUsers();
  },320));


  /* ==========================================================
      PER PAGE
  ========================================================== */
  perPageSel.addEventListener('change',()=>{
    perPage=parseInt(perPageSel.value,10)||10;
    page=1;
    fetchUsers();
  });


  /* ==========================================================
      FILTERS
  ========================================================== */
  const filterModalEl = document.getElementById('filterModal');
  const filterModal = new bootstrap.Modal(filterModalEl);

  filterModalEl.addEventListener('show.bs.modal',()=>{
    modalStatus.value = statusFilter;
    modalRole.value   = roleFilter;
    modalSort.value   = sort;
  });
/* ----- APPLY FILTERS: hide modal first, then fetch on hidden ----- */
btnApplyFilters.addEventListener('click', () => {
  statusFilter = modalStatus.value;
  roleFilter   = modalRole.value;
  sort         = modalSort.value;
  page = 1;

  // hide the modal — fetching will run after it's fully hidden
  filterModal.hide();
});

/* when the modal is fully hidden, run fetch and also cleanup leftover backdrops */
filterModalEl.addEventListener('hidden.bs.modal', () => {
  // safe place to fetch — modal DOM is fully cleaned up by Bootstrap now
  fetchUsers().catch(ex => err(ex.message));

  // defensive cleanup in case a backdrop or body class was left behind
  try {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  } catch (cleanEx) {
    console.warn('Modal cleanup failed', cleanEx);
  }
});

  btnReset.addEventListener('click',()=>{
    statusFilter='all';
    roleFilter='';
    sort='-created_at';
    q='';
    page=1;
    perPage=10;

    searchInput.value='';
    perPageSel.value='10';
    modalStatus.value='all';
    modalRole.value='';
    modalSort.value='-created_at';

    fetchUsers();
  });


  /* ==========================================================
      TOGGLE ACTIVE
  ========================================================== */
  tbody.addEventListener('change', async (e)=>{
    const sw=e.target.closest('.js-toggle'); if(!sw) return;
    if(!canWrite){ sw.checked=!sw.checked; return; }

    const tr=sw.closest('tr');
    const id=tr.dataset.id;

    const willActive=sw.checked;

    const conf=await Swal.fire({
      title:'Confirm',
      text:willActive?'Activate user?':'Deactivate user?',
      icon:'question',showCancelButton:true
    });

    if(!conf.isConfirmed){
      sw.checked=!willActive;
      return;
    }

    try{
      const res=await fetch(`/api/users/${id}`,{
        method:'PATCH',
        headers:{ ...authHeaders({'Content-Type':'application/json'}) },
        body:JSON.stringify({ status: willActive?'active':'inactive' })
      });
      const js=await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message||'Status update failed');
      ok('Status updated');
      fetchUsers();
    }catch(ex){
      err(ex.message);
      sw.checked=!sw.checked;
    }
  });


  /* ==========================================================
      ROW ACTIONS (Edit, View, Delete, Manage Privileges)
  ========================================================== */
  tbody.addEventListener('click', (e)=>{
    const btn=e.target.closest('button[data-action]'); if(!btn) return;

    const tr=btn.closest('tr');
    const id=tr.dataset.id;
    const rowUuid = tr.dataset.uuid || "";

    const spin=()=>{ btn.disabled=true; btn.dataset._old=btn.innerHTML; btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>'; }
    const un  =()=>{ btn.disabled=false; btn.innerHTML=btn.dataset._old; }

    const act=btn.dataset.action;

    /* ---------- VIEW ---------- */
    if(act==='view'){
      spin(); openEdit(id,true).finally(un);
    }

    /* ---------- EDIT ---------- */
    else if(act==='edit'){
      if(!canWrite) return;
      spin(); openEdit(id,false).finally(un);
    }

    /* ---------- MANAGE PRIVILEGES: ALWAYS USE UUID ---------- */
    else if(act==='manage-privileges'){
      spin();

      (async ()=>{

        // 1) If the row already has a real UUID → redirect immediately
        if (rowUuid && rowUuid.includes("-")) {
          closeDropdown(btn);
          window.location.href = `/user-privileges/manage?user_uuid=${encodeURIComponent(rowUuid)}`;
          return;
        }

        // 2) Otherwise fetch user details to extract UUID
        try {
          const uRes = await fetch(`/api/users/${id}`, { headers:authHeaders() });
          let uJson = await uRes.json().catch(()=>({}));
          const uObj = uJson.user || uJson.data || {};
          const serverUuid = (uObj.uuid || uObj.user_uuid || '').trim();

          if (serverUuid) {
            closeDropdown(btn);
            window.location.href = `/user-privileges/manage?user_uuid=${encodeURIComponent(serverUuid)}`;
            return;
          }
        } catch (ex) {
          console.warn("UUID fetch failed → fallback to user_id");
        }

        // 3) FINAL fallback → numeric ID
        closeDropdown(btn);
        window.location.href = `/user-privileges/manage?user_id=${encodeURIComponent(id)}`;
      })()
      .catch(()=>{})
      .finally(un);

      return;
    }

    /* ---------- DELETE ---------- */
    else if(act==='delete'){
      if(!canDelete) return;

      Swal.fire({
        title:'Delete user?',
        text:'This performs soft delete.',
        icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444'
      }).then(async r=>{
        if(!r.isConfirmed) return;
        try{
          spin();
          const res=await fetch(`/api/users/${id}`,{ method:'DELETE',headers:authHeaders() });
          const js=await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(js.message||'Delete failed');
          ok('User deleted');
          fetchUsers();
        }catch(ex){ err(ex.message); }finally{ un(); }
      });
    }

    closeDropdown(btn);
  });

  function closeDropdown(btn){
    try{
      const toggleBtn=btn.closest('.dropdown')?.querySelector('.dd-toggle');
      if(toggleBtn){
        bootstrap.Dropdown.getOrCreateInstance(toggleBtn).hide();
      }
    }catch(e){}
  }


  /* ==========================================================
      ADD USER
  ========================================================== */
  btnAdd.addEventListener('click',()=>{
    resetForm();
    modalTitle.textContent='Add User';
    pwdReq.style.display='inline';
    pwdHelp.textContent='Enter password for new user';
    userModal.show();
  });


  /* ==========================================================
      IMAGE PREVIEW
  ========================================================== */
  imgInp.addEventListener('change',()=>{
    const f=imgInp.files?.[0];
    if(!f){ imgPrev.style.display='none'; imgPrev.src=''; return; }
    const reader=new FileReader();
    reader.onload=e=>{ imgPrev.src=e.target.result; imgPrev.style.display='block'; };
    reader.readAsDataURL(f);
  });


  /* ==========================================================
      CREATE / UPDATE USER
  ========================================================== */
  form.addEventListener('submit',async e=>{
    e.preventDefault();
    if(!canWrite) return;

    if(!nameInput.value.trim()) return nameInput.focus();
    if(!emailInput.value.trim()) return emailInput.focus();
    if(!roleInput.value.trim()) return roleInput.focus();

    const isEdit=!!idInput.value;

    if(!isEdit && !pwdInput.value.trim()) return pwdInput.focus();
    if(pwdInput.value && pwdInput.value!==pwd2Input.value){
      err('Passwords do not match'); return pwd2Input.focus();
    }

    const url=isEdit?`/api/users/${idInput.value}`:'/api/users';
    const fd=new FormData();
    if(isEdit) fd.append('_method','PUT');

    fd.append('name',nameInput.value.trim());
    fd.append('email',emailInput.value.trim());
    if(phoneInput.value) fd.append('phone_number',phoneInput.value.trim());
    if(altEmailInput.value) fd.append('alternative_email',altEmailInput.value.trim());
    if(altPhoneInput.value) fd.append('alternative_phone_number',altPhoneInput.value.trim());
    if(waInput.value) fd.append('whatsapp_number',waInput.value.trim());
    if(addrInput.value) fd.append('address',addrInput.value.trim());
    fd.append('role',roleInput.value);
    fd.append('status',statusInput.value);
    if(!isEdit) fd.append('password',pwdInput.value.trim());
    if(imgInp.files[0]) fd.append('image',imgInp.files[0]);

    try{
      saveBtn.disabled=true;
      saveBtn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Saving';

      const res=await fetch(url,{ method:'POST',headers:authHeaders(),body:fd });
      const js=await res.json().catch(()=>({}));
      if(!res.ok){
        let msg=js.message||'Save failed';
        if(js.errors){
          const k=Object.keys(js.errors)[0];
          if(k) msg=js.errors[k][0];
        }
        throw new Error(msg);
      }

      // Update password if changed while editing
      if(isEdit && pwdInput.value){
        const res2=await fetch(`/api/users/${idInput.value}/password`,{
          method:'PATCH',
          headers:authHeaders({'Content-Type':'application/json'}),
          body:JSON.stringify({password:pwdInput.value})
        });
        const js2=await res2.json().catch(()=>({}));
        if(!res2.ok) throw new Error(js2.message||'Password update failed');
      }

      userModal.hide();
      ok(isEdit?'User updated':'User created');
      fetchUsers();
    }
    catch(ex){ err(ex.message); }
    finally{
      saveBtn.disabled=false;
      saveBtn.innerHTML='<i class="fa fa-floppy-disk me-1"></i> Save';
    }
  });



  /* ==========================================================
      OPEN EDIT
  ========================================================== */
  async function openEdit(id,viewOnly=false){
    const res=await fetch(`/api/users/${id}`,{ headers:authHeaders() });
    if(res.status===401){ window.location.href='/'; return; }
    const js=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(js.message||'Failed to fetch');

    const u=js.user||{};

    resetForm();

    idInput.value=u.id||'';
    nameInput.value=u.name||'';
    emailInput.value=u.email||'';
    phoneInput.value=u.phone_number||'';
    altEmailInput.value=u.alternative_email||'';
    altPhoneInput.value=u.alternative_phone_number||'';
    waInput.value=u.whatsapp_number||'';
    addrInput.value=u.address||'';
    roleInput.value=(u.role||'').toLowerCase();
    statusInput.value=u.status||'active';

    if(u.image){
      imgPrev.src = fixImageUrl(u.image);
      imgPrev.style.display='block';
    }

    modalTitle.textContent = viewOnly ? 'View User' : 'Edit User';
    saveBtn.style.display = viewOnly ? 'none' : '';

    // Lock fields
    Array.from(form.querySelectorAll('input,select,textarea')).forEach(el=>{
      if(el===imgInp) el.disabled=viewOnly;
      if(el.tagName==='SELECT') el.disabled=viewOnly;
      if(el.tagName!=='SELECT') el.readOnly=viewOnly;
    });

    pwdReq.style.display='none';
    pwdHelp.textContent='Leave blank to keep current password';

    userModal.show();
  }


  /* ==========================================================
      RESET FORM
  ========================================================== */
  function resetForm(){
    form.reset();
    idInput.value='';
    imgPrev.style.display='none';
    imgPrev.src='';
    saveBtn.style.display='';

    Array.from(form.querySelectorAll('input,select,textarea')).forEach(el=>{
      if(el===imgInp) el.disabled=false;
      if(el.tagName==='SELECT') el.disabled=false;
      if(el.tagName!=='SELECT') el.readOnly=false;
    });

    statusInput.value='active';
    pwdReq.style.display='inline';
    pwdHelp.textContent='Enter password for new user';
  }
  fetchUsers().catch(ex=>err(ex.message));

});
</script>
