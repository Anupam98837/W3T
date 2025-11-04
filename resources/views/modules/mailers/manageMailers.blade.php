{{-- resources/views/modules/mailers/manageMailers.blade.php --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  /* Tiny enhancements using your tokens */
  .sortable .tiny-sort { font-size:14px; opacity:.7; margin-left:6px; }
  .btn-icon{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px;
             border:1px solid var(--line-strong); border-radius:8px; background:var(--surface); }
  .btn-icon:hover{ background:#f6f8fc; }
  .btn-icon.danger{ color:#dc2626; border-color:#ef4444; }
  .status-badge{ display:inline-flex; align-items:center; gap:6px; font-weight:700; border-radius:999px;
                 padding:2px 8px; font-size:11px; border:1px solid transparent; }
  .status-badge.active{ background:var(--t-success); color:#15803d; border-color:rgba(22,163,74,.22); }
  .status-badge.debit{ background:var(--t-info); color:#1d4ed8; border-color:rgba(59,130,246,.22); }
  .status-badge.archived{ background:#f3f4f6; color:#6b7280; border-color:#e5e7eb; }
</style>

{{-- Top: Breadcrumb / Title --}}
<div class="container-fluid mb-3">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-2">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li class="breadcrumb-item active" aria-current="page">Mailers</li>
    </ol>
  </nav>

  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h1 class="mb-0" style="font-size:1.25rem;">Mailers
      <span id="mailersCount" class="text-muted" style="font-size:var(--fs-13);">—</span>
    </h1>
    <div id="writeControls" class="d-flex gap-2" style="display:none;">
      <button id="btnAddMailer" class="btn btn-primary btn-sm">
        <i class="fa fa-plus"></i> Add Mailer
      </button>
    </div>
  </div>
</div>

{{-- Filters / Search --}}
<div class="container-fluid">
  <div class="panel shadow-1">
    <div class="panel-head">
      <div class="d-flex flex-wrap align-items-end gap-2">
        <div>
          <label class="form-label mb-1 small">Rows</label>
          <select id="perPage" class="form-select" style="width:120px;">
            <option>10</option><option selected>20</option><option>50</option><option>100</option>
          </select>
        </div>

        <div>
          <label class="form-label mb-1 small">Driver</label>
          <select id="driverFilter" class="form-select" style="min-width:180px;">
            <option value="">All</option>
            <option value="smtp">SMTP</option>
            <option value="sendmail">Sendmail</option>
            <option value="ses">SES</option>
            <option value="mailgun">Mailgun</option>
            <option value="postmark">Postmark</option>
            <option value="log">Log</option>
            <option value="array">Array</option>
          </select>
        </div>

        <div>
          <label class="form-label mb-1 small">Encryption</label>
          <select id="encryptionFilter" class="form-select" style="min-width:160px;">
            <option value="">All</option>
            <option value="ssl">SSL</option>
            <option value="tls">TLS</option>
            <option value="none">None</option>
          </select>
        </div>
      </div>

      <div class="ms-auto" style="min-width:280px;">
        <label class="form-label mb-1 small">Search</label>
        <div class="position-relative">
          <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by driver, host, user, label…">
          <i class="fa fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th style="width: 84px;">Default</th>
            <th>Driver</th>
            <th>Host</th>
            <th>Port</th>
            <th>Username</th>
            <th>Encryption</th>
            <th>From Address</th>
            <th>From Name</th>
            <th>Label</th>
            <th id="thCreated" class="sortable" style="cursor:pointer;">
              Created <i class="fa fa-angle-down tiny-sort"></i>
            </th>
            <th style="width: 140px;">Action</th>
          </tr>
        </thead>
        <tbody id="mailersTbody">
          <tr><td colspan="11" class="text-center text-muted" style="padding:36px;">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    {{-- Footer --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
      <div id="resultsInfo" class="text-muted small"></div>
      <nav aria-label="Page navigation">
        <ul id="pager" class="pagination mb-0"></ul>
      </nav>
    </div>
  </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="mailerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="mailerForm">
      <div class="modal-header">
        <h5 class="modal-title" id="mailerModalTitle">Add Mailer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="mailerId"/>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Driver <span class="text-danger">*</span></label>
            <select class="form-select" id="mailerDriver" required>
              <option value="smtp">SMTP</option>
              <option value="sendmail">Sendmail</option>
              <option value="ses">SES</option>
              <option value="mailgun">Mailgun</option>
              <option value="postmark">Postmark</option>
              <option value="log">Log</option>
              <option value="array">Array</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Label</label>
            <input class="form-control" id="mailerLabel" maxlength="255" placeholder="e.g., Primary SMTP">
          </div>

          <div class="col-md-6">
            <label class="form-label">Host <span class="text-danger">*</span></label>
            <input class="form-control" id="mailerHost" required maxlength="255" placeholder="smtp.mailserver.com">
          </div>

          <div class="col-md-3">
            <label class="form-label">Port <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="mailerPort" required placeholder="587">
          </div>

          <div class="col-md-3">
            <label class="form-label">Encryption</label>
            <select class="form-select" id="mailerEncryption">
              <option value="">None</option>
              <option value="ssl">SSL</option>
              <option value="tls">TLS</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <input class="form-control" id="mailerUsername" required maxlength="255" placeholder="user@domain.com">
          </div>

          <div class="col-md-6">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="mailerPassword" required placeholder="••••••••">
          </div>

          <div class="col-md-6">
            <label class="form-label">From Address <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="mailerFromAddress" required maxlength="255" placeholder="noreply@domain.com">
          </div>

          <div class="col-md-6">
            <label class="form-label">From Name <span class="text-danger">*</span></label>
            <input class="form-control" id="mailerFromName" required maxlength="255" placeholder="Your App Name">
          </div>

          <div class="col-12">
            <label class="form-check-label" for="mailerIsDefault">Set as default mailer</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="mailerIsDefault">
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveMailerBtn">
          <i class="fa fa-floppy-disk"></i> Save
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

@push('scripts')
  {{-- jQuery + Select2 are optional; page works without them. If you already load these globally, remove lines below. --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
  window.addEventListener('load', function(){
    const bs = window.bootstrap;
    if (!bs) { console.error('Bootstrap bundle not found.'); return; }

    // ---- Auth / Role gate ----
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/auth/login'; return; }

    const writeRoles = ['admin','super_admin'];
    const role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
    const canWrite = writeRoles.includes(role);
    if (canWrite) document.getElementById('writeControls').style.display = 'flex';

    // ---- State ----
    const API = '/api/mailer';
    let page=1, perPage=20, q='', driverFilter='', encryptionFilter='', totalPages=1, totalCount=0;
    let sortBy='created_at', sortDir='desc';

    // ---- Els ----
    const tbody   = document.getElementById('mailersTbody');
    const pager   = document.getElementById('pager');
    const info    = document.getElementById('resultsInfo');
    const countEl = document.getElementById('mailersCount');
    const thCreated = document.getElementById('thCreated');

    const searchInput   = document.getElementById('searchInput');
    const driverSel     = document.getElementById('driverFilter');
    const encryptionSel = document.getElementById('encryptionFilter');
    const perPageSel    = document.getElementById('perPage');

    const mailerModal = new bs.Modal(document.getElementById('mailerModal'));
    const form        = document.getElementById('mailerForm');
    const idInput     = document.getElementById('mailerId');
    const driverInput = document.getElementById('mailerDriver');
    const labelInput  = document.getElementById('mailerLabel');
    const hostInput   = document.getElementById('mailerHost');
    const portInput   = document.getElementById('mailerPort');
    const usernameInput = document.getElementById('mailerUsername');
    const passwordInput = document.getElementById('mailerPassword');
    const encryptionInput = document.getElementById('mailerEncryption');
    const fromAddressInput = document.getElementById('mailerFromAddress');
    const fromNameInput = document.getElementById('mailerFromName');
    const isDefaultInput = document.getElementById('mailerIsDefault');
    const modalTitle  = document.getElementById('mailerModalTitle');
    const saveBtn     = document.getElementById('saveMailerBtn');
    const btnAdd      = document.getElementById('btnAddMailer');

    function authHeaders(extra={}){ return Object.assign({'Authorization':'Bearer '+token}, extra); }
    function debounce(fn,ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }
    function escapeHtml(str){ return (str ?? '').toString().replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s])); }

    const toastOk  = new bs.Toast(document.getElementById('toastSuccess'));
    const toastErr = new bs.Toast(document.getElementById('toastError'));
    const okTxt  = document.getElementById('toastSuccessText');
    const errTxt = document.getElementById('toastErrorText');
    const ok = m=>{ okTxt.textContent=m||'Done'; toastOk.show(); }
    const err= m=>{ errTxt.textContent=m||'Something went wrong'; toastErr.show(); }

    function s2(sel,dp){ if(!window.jQuery || !jQuery.fn.select2) return;
      const $el=jQuery(sel); if(!$el.length) return;
      if($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
      $el.select2({width:'100%', dropdownParent: dp? jQuery(dp) : jQuery(document.body), minimumResultsForSearch:0});
    }
    function initSelects(){
      s2('#driverFilter'); s2('#encryptionFilter'); s2('#perPage');
      s2('#mailerDriver','#mailerModal'); s2('#mailerEncryption','#mailerModal');
    }
    setTimeout(initSelects,0);

    // Sync initial values from DOM
    perPage         = parseInt(perPageSel.value,10) || 20;
    driverFilter    = driverSel.value || '';
    encryptionFilter= encryptionSel.value || '';

    // ---- Fetch & Render ----
    async function fetchMailers(){
      tbody.innerHTML = `<tr><td class="text-center text-muted" colspan="11" style="padding:36px;">Loading…</td></tr>`;
      const params = new URLSearchParams({
        page:String(page), per_page:String(perPage), q:q, driver:driverFilter, encryption:encryptionFilter
      });
      const res = await fetch(`${API}?${params.toString()}`, { headers: authHeaders() });
      let json; try{ json = await res.json(); }catch{ json = { message:'Invalid response' }; }
      if (!res.ok) throw new Error(json.message || 'Failed to fetch');

      let rows = json.data || [];
      totalPages = json.meta?.total_pages || 1;
      totalCount = json.meta?.total || rows.length || 0;

      if (sortBy === 'created_at'){
        rows = rows.slice().sort((a,b)=>{
          const ta = a.created_at ? new Date(a.created_at).getTime() : 0;
          const tb = b.created_at ? new Date(b.created_at).getTime() : 0;
          return sortDir==='asc' ? (ta-tb) : (tb-ta);
        });
      }
      renderTable(rows);
      renderPager();
      info.textContent = rows.length
        ? `Showing ${(page-1)*perPage + 1} to ${(page-1)*perPage + rows.length} of ${totalCount} entries`
        : `0 of ${totalCount}`;
      countEl.textContent = `${totalCount} total`;
    }

    function renderTable(rows){
      if(!rows.length){
        tbody.innerHTML = `<tr><td colspan="11" class="text-center text-muted" style="padding:36px;">No mailers found</td></tr>`;
        return;
      }
      tbody.innerHTML = rows.map(row=>{
        const isDefault = !!row.is_default;
        const created = row.created_at ? new Date(row.created_at).toLocaleString() : '—';

        const defaultToggle = canWrite
          ? `<div class="form-switch"><input class="form-check-input js-toggle-default" type="checkbox" ${isDefault?'checked':''} aria-label="Toggle default"></div>`
          : `<span class="text-muted">${isDefault?'Yes':'No'}</span>`;

        let enc = '<span class="status-badge archived">none</span>';
        if (row.encryption === 'ssl') enc = '<span class="status-badge active">SSL</span>';
        if (row.encryption === 'tls') enc = '<span class="status-badge debit">TLS</span>';

        const actions = canWrite
          ? `<div class="d-inline-flex gap-1">
               <button class="btn-icon" data-action="edit" title="Edit"><i class="fa fa-pen"></i></button>
               <button class="btn-icon danger" data-action="delete" title="Delete"><i class="fa fa-trash"></i></button>
             </div>`
          : `<button class="btn-icon" data-action="view" title="View"><i class="fa fa-eye"></i></button>`;

        return `
          <tr data-id="${row.id}">
            <td>${defaultToggle}</td>
            <td><code>${escapeHtml(row.mailer||'')}</code></td>
            <td>${escapeHtml(row.host||'') || '<span class="text-muted">—</span>'}</td>
            <td>${escapeHtml(row.port||'') || '<span class="text-muted">—</span>'}</td>
            <td>${escapeHtml(row.username||'') || '<span class="text-muted">—</span>'}</td>
            <td>${enc}</td>
            <td>${row.from_address ? `<a href="mailto:${escapeHtml(row.from_address)}">${escapeHtml(row.from_address)}</a>` : '<span class="text-muted">—</span>'}</td>
            <td>${escapeHtml(row.from_name||'') || '<span class="text-muted">—</span>'}</td>
            <td>${escapeHtml(row.label||'') || '<span class="text-muted">—</span>'}</td>
            <td>${created}</td>
            <td>${actions}</td>
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
      html += item(Math.max(1, page - 1), 'Previous', page <= 1);
      const st = Math.max(1, page - 2), en = Math.min(totalPages, page + 2);
      for(let p = st; p <= en; p++) html += item(p, p, false, p === page);
      html += item(Math.min(totalPages, page + 1), 'Next', page >= totalPages);
      pager.innerHTML = html;
    }

    pager.addEventListener('click', e=>{
      const a = e.target.closest('.page-link'); if(!a) return;
      e.preventDefault();
      const p = parseInt(a.dataset.page,10);
      if(!Number.isNaN(p) && p!==page){ page=p; fetchMailers().catch(e=>err(e.message)); window.scrollTo({top:0,behavior:'smooth'}); }
    });

    const onSearch = debounce(()=>{ q = searchInput.value.trim(); page=1; fetchMailers().catch(e=>err(e.message)); }, 300);
    searchInput.addEventListener('input', onSearch);

    const wire = (el, fn) => {
      if (window.jQuery && jQuery.fn.select2) {
        const $el = jQuery(el);
        $el.off('.sel');
        $el.on('change.sel select2:select.sel select2:clear.sel', fn);
      } else {
        el.addEventListener('change', fn);
      }
    };
    wire(driverSel,     ()=>{ driverFilter=driverSel.value;           page=1; fetchMailers().catch(e=>err(e.message)); });
    wire(encryptionSel, ()=>{ encryptionFilter=encryptionSel.value;   page=1; fetchMailers().catch(e=>err(e.message)); });
    wire(perPageSel,    ()=>{ perPage=parseInt(perPageSel.value,10)||20; page=1; fetchMailers().catch(e=>err(e.message)); });

    thCreated.addEventListener('click', ()=>{
      if (sortBy!=='created_at'){ sortBy='created_at'; sortDir='desc'; } else { sortDir = (sortDir==='asc'?'desc':'asc'); }
      thCreated.querySelector('.tiny-sort').className = 'fa '+(sortDir==='asc'?'fa-angle-up':'fa-angle-down')+' tiny-sort';
      fetchMailers().catch(e=>err(e.message));
    });

    // Toggle default
    tbody.addEventListener('change', async (e)=>{
      const sw = e.target.closest('.js-toggle-default'); if(!sw) return;
      if (!canWrite){ sw.checked = !sw.checked; return; }
      const tr = sw.closest('tr'); const id = tr?.dataset?.id; if(!id) return;
      const willDefault = sw.checked;

      if (!confirm(willDefault ? 'Set this as default mailer?' : 'Remove default status?')){
        sw.checked = !willDefault; return;
      }
      try{
        const res = await fetch(`${API}/${id}/default`, { method:'PUT', headers: authHeaders() });
        const json = await res.json();
        if(!res.ok) throw new Error(json.message || 'Set default failed');
        ok('Default mailer updated'); fetchMailers().catch(()=>{});
      }catch(ex){ err(ex.message); sw.checked = !sw.checked; }
    });

    // Row actions
    tbody.addEventListener('click', (e)=>{
      const btn = e.target.closest('button[data-action]'); if(!btn) return;
      const tr = btn.closest('tr'); const id = tr?.dataset?.id; if(!id) return;

      const spin=()=>{ btn.disabled=true; btn.dataset._old = btn.innerHTML; btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>'; }
      const un  =()=>{ btn.disabled=false; btn.innerHTML = btn.dataset._old || btn.innerHTML; }

      const act = btn.dataset.action;
      if (act==='edit'){
        if(!canWrite) return;
        spin(); openEdit(id).catch(ex=>err(ex.message)).finally(un);
      } else if (act==='delete'){
        if(!canWrite) return;
        if(!confirm('Delete mailer? This cannot be undone.')) return;
        (async ()=>{
          try{
            spin();
            const res = await fetch(`${API}/${id}`, { method:'DELETE', headers: authHeaders() });
            const json = await res.json();
            if(!res.ok) throw new Error(json.message || 'Delete failed');
            ok('Mailer deleted'); fetchMailers().catch(()=>{});
          }catch(ex){ err(ex.message); } finally{ un(); }
        })();
      } else if (act==='view'){
        openEdit(id,true).catch(ex=>err(ex.message));
      }
    });

    // Add
    document.getElementById('btnAddMailer')?.addEventListener('click', ()=>{
      resetForm(); modalTitle.textContent='Add Mailer'; mailerModal.show();
    });

    // Save
    form.addEventListener('submit', async (e)=>{
      e.preventDefault(); if (!canWrite) return;
      if (!hostInput.value.trim()) return hostInput.focus();
      if (!portInput.value.trim()) return portInput.focus();
      if (!usernameInput.value.trim()) return usernameInput.focus();
      if (!passwordInput.value.trim() && !idInput.value) return passwordInput.focus();
      if (!fromAddressInput.value.trim()) return fromAddressInput.focus();
      if (!fromNameInput.value.trim()) return fromNameInput.focus();

      const payload = {
        mailer: driverInput.value,
        host: hostInput.value.trim(),
        port: parseInt(portInput.value.trim(), 10),
        username: usernameInput.value.trim(),
        password: passwordInput.value.trim(),   // server can ignore empty on update
        encryption: encryptionInput.value,
        from_address: fromAddressInput.value.trim(),
        from_name: fromNameInput.value.trim(),
        label: labelInput.value.trim(),
        is_default: isDefaultInput.checked
      };

      const isEdit = !!idInput.value;
      const url = isEdit ? `${API}/${idInput.value}` : API;
      const method = isEdit ? 'PUT' : 'POST';

      try{
        saveBtn.disabled=true; saveBtn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Saving';
        const res = await fetch(url, {
          method: method,
          headers: { ...authHeaders(), 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Save failed');
        mailerModal.hide(); ok(isEdit ? 'Mailer updated' : 'Mailer created');
        fetchMailers().catch(()=>{});
      }catch(ex){ err(ex.message); }
      finally{ saveBtn.disabled=false; saveBtn.innerHTML='<i class="fa fa-floppy-disk"></i> Save'; }
    });

    async function openEdit(id, viewOnly=false){
      const res = await fetch(`${API}/${id}`, { headers: authHeaders() });
      const json = await res.json();
      if(!res.ok) throw new Error(json.message || 'Fetch failed');
      const m = json.data || {};
      resetForm();

      idInput.value           = m.id;
      driverInput.value       = m.mailer || 'smtp';
      labelInput.value        = m.label || '';
      hostInput.value         = m.host || '';
      portInput.value         = m.port || '';
      usernameInput.value     = m.username || '';
      passwordInput.value     = '';
      encryptionInput.value   = m.encryption || '';
      fromAddressInput.value  = m.from_address || '';
      fromNameInput.value     = m.from_name || '';
      isDefaultInput.checked  = !!m.is_default;

      if (window.jQuery && jQuery.fn.select2){
        jQuery('#mailerDriver').val(driverInput.value).trigger('change.select2');
        jQuery('#mailerEncryption').val(encryptionInput.value).trigger('change.select2');
      }

      modalTitle.textContent = viewOnly ? 'View Mailer' : 'Edit Mailer';
      saveBtn.style.display = viewOnly ? 'none' : '';
      Array.from(form.querySelectorAll('input,select,textarea')).forEach(el=>{
        if (el.tagName === 'SELECT') el.disabled = viewOnly;
        if (el.tagName !== 'SELECT') el.readOnly = viewOnly;
      });

      mailerModal.show();
    }

    function resetForm(){
      form.reset(); idInput.value='';
      saveBtn.style.display='';
      Array.from(form.querySelectorAll('input,select,textarea')).forEach(el=>{
        if (el.tagName === 'SELECT') el.disabled = false;
        if (el.tagName !== 'SELECT') el.readOnly = false;
      });
      if (window.jQuery && jQuery.fn.select2){
        jQuery('#mailerDriver').val('smtp').trigger('change.select2');
        jQuery('#mailerEncryption').val('').trigger('change.select2');
      }
    }

    // Initial load
    fetchMailers().catch(e=>err(e.message));
  });
  </script>
@endpush
