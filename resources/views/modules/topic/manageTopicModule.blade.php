{{-- resources/views/admin/modules/index.blade.php --}}
{{-- Modules Manage Page — grouped by ACTIVE topics in an accordion.
    - Add/Edit Module with topic selector
    - Drag-and-drop reorder per topic (persisted to /api/modules/reorder)
    - Search & status filters (global)
    - Mobile Filters modal
    - Only one accordion open at a time
    - Search-aware topic ordering + "N module(s) found" badge
    - Dark-mode fixes for accordion surfaces
    - Per-topic pagination (like Topics page)
--}}

@push('styles')
<style>
  html, body { width:100%; max-width:100%; overflow-x:hidden; }
  .layout, .right-panel, .main-content { overflow-x:hidden; }

  .page-indicator{
    display:inline-flex;align-items:center;gap:8px;
    background: var(--bg-body);
    border:1px solid var(--border-color);
    border-radius:var(--radius-md);
    padding:10px 12px;
    box-shadow:var(--shadow-sm);
    color: var(--text-color);
  }
  .page-indicator i{color:var(--primary-color);}

  .clients-toolbar{
    display:flex;gap:10px;justify-content:space-between;align-items:center;margin:12px 0;
  }
  .clients-toolbar .left{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .clients-toolbar .right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

  .table-wrap{ border-top:1px solid var(--border-color); width:100%; overflow:hidden; border-radius: var(--radius-md); }
  .table-wrap .table-responsive{ overflow-x:auto; -webkit-overflow-scrolling:touch; }

  .badge-status{text-transform:uppercase}
  .form-switch .form-check-input{width:2.5rem;height:1.3rem}

  .drag-handle{ cursor:grab; opacity:.85; }
  tr.dragging{ opacity:.6; }
  tr.drop-target{ outline:2px dashed var(--accent-color); }

  .pager-wrap{display:flex;justify-content:flex-end}
  .tiny{font-size:12px;color:#6b7280}
  .filter-chip{
    border:1px dashed var(--border-color);
    padding:6px 10px;border-radius:8px;
    background: var(--bg-body);
    color: var(--text-color);
  }

  /* ---------- Dark mode corrections ---------- */
  html.theme-dark .page-indicator{background: var(--light-color); border-color: var(--border-color); color: var(--text-color);}
  html.theme-dark .filter-chip{background: var(--light-color); border-color: var(--border-color); color: var(--text-color);}
  html.theme-dark .tiny{color:#93a4b8;}
  html.theme-dark .bg-white{background: var(--light-color)!important; color: var(--text-color)!important; border-color: var(--border-color)!important;}

  /* Fix accordion whites in dark mode */
  html.theme-dark .accordion-item{
    background: var(--light-color);
    border-color: var(--border-color);
  }
  html.theme-dark .accordion-button{
    background: var(--light-color);
    color: var(--text-color);
    border-bottom: 1px solid var(--border-color);
  }
  html.theme-dark .accordion-button:not(.collapsed){
    background: var(--light-color);
    color: var(--text-color);
    box-shadow: none;
  }
  html.theme-dark .accordion-body{
    background: var(--light-color);
    color: var(--text-color);
    border-top: 1px solid var(--border-color);
  }

  html.theme-dark .form-switch .form-check-input{
    background-color:#1e293b;border-color:var(--border-color);
    background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23e5e7eb'/%3e%3c/svg%3e");
    background-position:left center;background-repeat:no-repeat;
    transition:background-position .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out;
  }
  html.theme-dark .form-switch .form-check-input:checked{
    background-color:var(--accent-color);
    border-color:var(--accent-color);
    background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23ffffff'/%3e%3c/svg%3e");
    background-position:right center;
  }
  html.theme-dark .form-check-input:focus{ box-shadow:0 0 0 .2rem rgba(79,70,229,.35); }
  
  /* ---------- Responsiveness ---------- */
  @media (max-width: 768px){
    .table { min-width: 720px; }
    .clients-toolbar{flex-direction:column; align-items:stretch; gap:12px;}
    .clients-toolbar .right{justify-content:flex-end;}
  }

  /* Responsive filter button (same as Topics page) */
  @media (max-width: 576px){
    .filters-inline { display:none !important; }
    #btnFilters { display:inline-flex !important; }
  }
  #btnFilters { display:none; }

  th.sortable .tiny-sort-icon{ font-size:12px; margin-left:6px; opacity:.6; }
  th.sortable .tiny-sort-icon.active{ opacity:1; color:var(--accent-color); }

  /* Select2 tweaks */
  .select2-container{ min-width:180px; width:100% !important; }
  .select2-container--default .select2-selection--single{
    height: calc(1.5em + .5rem + 2px);
    border: 1px solid var(--border-color);
    border-radius: .375rem;
    background: var(--light-color);
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered{
    line-height: calc(1.5em + .5rem + 2px);
    padding-left: .5rem;
    color: var(--text-color) !important;
  }
  .select2-dropdown{ border-color: var(--border-color); }
  html.theme-dark .select2-container--default .select2-selection--single{ background:#0e1a2d; color:#e5e7eb; }
  html.theme-dark .select2-dropdown{ background:#0e1a2d; color:#e5e7eb; }
</style>
@endpush

<div class="page-head">
  <div class="page-indicator">
    <i class="fa-solid fa-layer-group"></i>
    <strong>Modules</strong>
    <span class="text-muted small ms-1" id="modulesCount">—</span>
  </div>
  <div class="actions"><!-- reserved --></div>
</div>

<div class="clients-toolbar">
  <div class="left">
    <div class="input-group">
      <span class="input-group-text"><i class="fa fa-search"></i></span>
      <input id="searchInput" type="search" class="form-control" placeholder="Search modules by title, slug, topic, status…">
    </div>

    <div class="filters-inline d-flex gap-2">
      <div class="filter-chip">
        <div class="d-flex align-items-center gap-2">
          <label class="tiny m-0">Status</label>
          <select id="statusFilter" class="form-select form-select-sm" style="min-width:130px">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>

      <div class="filter-chip">
        <div class="d-flex align-items-center gap-2">
          <label class="tiny m-0">Rows</label>
          <select id="perPage" class="form-select form-select-sm" style="min-width:110px">
            <option>10</option><option>20</option><option>50</option><option>100</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="right">
    <button class="btn btn-outline-secondary" id="btnFilters">
      <i class="fa fa-filter me-2"></i>Filters
    </button>
    <button class="btn btn-primary" id="btnAddModule">
      <i class="fa fa-plus me-2"></i>Add Module
    </button>
  </div>
</div>

{{-- Filters Modal (mobile) --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Filters</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label tiny d-block">Status</label>
          <select id="statusFilterModal" class="form-select">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label tiny d-block">Rows</label>
          <select id="perPageModal" class="form-select">
            <option>10</option><option>20</option><option>50</option><option>100</option>
          </select>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="applyFiltersBtn"><i class="fa fa-check me-2"></i>Apply</button>
      </div>
    </div>
  </div>
</div>

{{-- Accordion: one panel per ACTIVE topic (only one open at a time) --}}
<div class="accordion" id="topicsAccordion">
  <div class="text-center text-muted p-4" id="accLoading">Loading topics…</div>
</div>

{{-- Add/Edit Module Modal --}}
<div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="moduleForm">
      <div class="modal-header">
        <h5 class="modal-title" id="moduleModalTitle">Add Module</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="moduleId">

        <div class="mb-3">
          <label class="form-label">Topic</label>
          <select id="moduleTopic" class="form-select" required></select>
        </div>

        <div class="mb-3">
          <label class="form-label">Title</label>
          <input class="form-control" id="moduleTitle" required maxlength="200" placeholder="e.g., Lists & Tuples">
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" id="moduleDesc" rows="3" placeholder="Optional short description…"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select class="form-select" id="moduleStatus">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveModuleBtn">
          <i class="fa fa-save me-2"></i>Save
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

<!-- Core -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jQuery + Select2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@push('scripts')
<script>
(function(){
  const API_TOPICS  = '/api/topics';
  const API_MODULES = '/api/modules';

  function getToken(){ return localStorage.getItem('token') || sessionStorage.getItem('token') || ''; }
  function showToast(id, text){ const el=document.getElementById(id); if(!el) return; el.querySelector('.toast-body').textContent=text; (new bootstrap.Toast(el)).show(); }
  function showSuccess(msg){ showToast('toastSuccess', msg); }
  function showError(msg){ showToast('toastError', msg); }
  const debounce=(fn,ms=400)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}};
  const normalize=s=>(s??'').toString().toLowerCase();
  const esc=s=>(s??'').replace(/[&<>"']/g, ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));

  // State
  let activeTopics = [];           // [{id, title, slug, ...}]
  let modulesByTopic = new Map();  // topic_id -> full array of modules
  let filteredByTopic = new Map(); // topic_id -> filtered array (after search/filter)
  let pageByTopic = new Map();     // topic_id -> current page (1-based)
  let q = '', statusFilter = '', perPage = 10;

  // DOM
  const modulesCountEl = document.getElementById('modulesCount');
  const acc = document.getElementById('topicsAccordion');
  const accLoading = document.getElementById('accLoading');

  const searchInput = document.getElementById('searchInput');
  const statusSelect = document.getElementById('statusFilter');
  const perPageSel = document.getElementById('perPage');

  const btnFilters = document.getElementById('btnFilters');
  const filtersModal = new bootstrap.Modal(document.getElementById('filtersModal'));
  const statusFilterModal = document.getElementById('statusFilterModal');
  const perPageModal = document.getElementById('perPageModal');
  const applyFiltersBtn = document.getElementById('applyFiltersBtn');

  const moduleModal = new bootstrap.Modal(document.getElementById('moduleModal'));
  const moduleForm  = document.getElementById('moduleForm');
  const moduleId    = document.getElementById('moduleId');
  const moduleTopic = document.getElementById('moduleTopic');
  const moduleTitle = document.getElementById('moduleTitle');
  const moduleDesc  = document.getElementById('moduleDesc');
  const moduleStatus= document.getElementById('moduleStatus');
  const moduleModalTitle = document.getElementById('moduleModalTitle');
  const saveModuleBtn = document.getElementById('saveModuleBtn');
  document.getElementById('btnAddModule').addEventListener('click', onClickAddModule);

  // ===== Fetch =====
  async function fetchActiveTopics(){
    const params = new URLSearchParams({ per_page: 100, status: 'active' });
    const res = await fetch(`${API_TOPICS}?${params}`, { headers:{ 'Authorization': `Bearer ${getToken()}` }});
    const json = await res.json();
    if(!res.ok) throw new Error(json.message || 'Failed to fetch topics');
    let list = [];
    if (Array.isArray(json)) list = json;
    else if (Array.isArray(json.data)) list = json.data;
    else if (json?.data && Array.isArray(json.data.data)) list = json.data.data;
    else if (Array.isArray(json.items)) list = json.items;
    activeTopics = (list || []).filter(t => t.status === 'active');
  }

  async function fetchModulesForTopic(topicId){
    const params = new URLSearchParams({ per_page: 100, topic_id: topicId });
    const res = await fetch(`${API_MODULES}?${params}`, { headers:{ 'Authorization': `Bearer ${getToken()}` }});
    const json = await res.json();
    if(!res.ok) throw new Error(json.message || 'Failed to fetch modules');

    let list = [];
    if (Array.isArray(json)) list = json;
    else if (Array.isArray(json.data)) list = json.data;
    else if (json?.data && Array.isArray(json.data.data)) list = json.data.data;
    else if (Array.isArray(json.items)) list = json.items;

    modulesByTopic.set(topicId, list || []);
  }

  // ===== Render =====
  function pluralize(n){ return n === 1 ? 'module found' : 'modules found'; }

  function renderAccordion(sortedTopics){
    if(!sortedTopics.length){
      acc.innerHTML = `<div class="text-center text-muted p-4">No active topics found.</div>`;
      return;
    }
    let html = '';
    sortedTopics.forEach((t, idx)=>{
      const tid = t.id;
      const list = filteredByTopic.get(tid) || [];
      const totalPages = Math.max(1, Math.ceil(list.length / perPage));
      const curPage = Math.min(pageByTopic.get(tid) || 1, totalPages);
      pageByTopic.set(tid, curPage);

      const start = (curPage-1) * perPage;
      const pageItems = list.slice(start, start + perPage);

      const searchBadge = q ? `<span class="badge rounded-pill bg-primary ms-2">${list.length} ${pluralize(list.length)}</span>` : '';
      const showingText = list.length
        ? `${list.length ? (start + 1) : 0}–${Math.min(start+perPage, list.length)} of ${list.length}`
        : 'No modules';

      html += `
      <div class="accordion-item">
        <h2 class="accordion-header" id="hd-${tid}">
          <button class="accordion-button ${idx ? 'collapsed' : ''}" type="button"
                  data-bs-toggle="collapse" data-bs-target="#cl-${tid}"
                  aria-expanded="${idx===0?'true':'false'}" aria-controls="cl-${tid}">
            <div class="d-flex w-100 justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <strong>${esc(t.title)}</strong>
                <span class="tiny ms-2 text-muted">${list.length} modules</span>
                ${searchBadge}
              </div>
              <div class="tiny text-muted">${showingText}</div>
            </div>
          </button>
        </h2>
        <div id="cl-${tid}" class="accordion-collapse collapse ${idx===0?'show':''}"
             aria-labelledby="hd-${tid}" data-bs-parent="#topicsAccordion" data-topic="${tid}">
          <div class="accordion-body">
            <div class="card table-wrap">
              <div class="table-responsive">
                <table class="${localStorage.getItem('theme')==='dark'?'table table-dark m-0 align-middle':'table table-hover m-0 align-middle'}" data-topic-table="${tid}">
                  <thead>
                    <tr>
                      <th style="width:44px;"></th>
                      <th style="width:88px;">Active</th>
                      <th>Title</th>
                      <th>Slug</th>
                      <th>Status</th>
                      <th style="width:120px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="tbody-${tid}">
                    ${renderModuleRows(tid, pageItems)}
                  </tbody>
                </table>
              </div>
              <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small" id="info-${tid}">
                    ${showingText}
                  </div>
                  <nav class="pager-wrap">
                    <ul class="pagination m-0" data-pager="${tid}">
                      ${renderPager(tid, curPage, totalPages)}
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>`;
    });
    acc.innerHTML = html;

    // Bind drag & actions & pager for each topic
    sortedTopics.forEach(t => {
      bindTableInteractions(t.id);
      bindPager(t.id);
    });
  }

  function renderModuleRows(topicId, items){
    if(!items.length){
        return `
        <tr>
            <td colspan="6" class="text-center p-4 text-muted">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <img src="{{ asset('/assets/media/images/web/no-data.png') }}"
                    alt="No data"
                    style="max-width:160px; opacity:.75; margin-bottom:12px;">
                <div>No modules in this topic</div>
            </div>
            </td>
        </tr>`;
    }
    return items.map(row=>{
      const id = row.id ?? row.module_id;
        const active = (row.status === 'active');
        const statusBadge =
        row.status === 'active' ? '<span class="badge bg-success badge-status">active</span>' :
        row.status === 'inactive' ? '<span class="badge bg-danger badge-status">inactive</span>' :
        '<span class="badge bg-secondary badge-status">archived</span>';

        const manageUrl = `/admin/topics/${topicId}/modules/${row.id}/questions`;

        return `
        <tr data-id="${row.id}" data-topic="${topicId}" draggable="true">
            <td class="text-center"><i class="fa fa-grip-vertical drag-handle" title="Drag to reorder"></i></td>
            <td>
            <div class="form-switch">
                <input class="form-check-input js-toggle" type="checkbox" ${active ? 'checked' : ''} aria-label="Toggle active">
            </div>
            </td>
            <td>${esc(row.title || '')}</td>
            <td><code>${esc(row.slug || '')}</code></td>
            <td>${statusBadge}</td>
            <td class="row-actions d-flex gap-1">
            <button class="btn btn-sm ce-like" data-action="edit">
                <i class="fa fa-pen"></i> <span class="js-edit-text"></span>
            </button>
            <a class="btn btn-sm ce-like" href="${manageUrl}">
                <i class="fa-solid fa-circle-question"></i>Questions
            </a>
            </td>
        </tr>
        `;
    }).join('');
    }


  function renderPager(topicId, cur, tp){
    function li(p, label, disable=false, active=false){
      const cls = ['page-item', disable?'disabled':'', active?'active':''].join(' ');
      return `<li class="${cls}"><a class="page-link" href="#" data-topic="${topicId}" data-page="${p}">${label}</a></li>`;
    }
    let html = '';
    html += li(Math.max(1, cur-1), '&laquo;', cur<=1, false);
    const start = Math.max(1, cur-2), end = Math.min(tp, cur+2);
    for(let p=start; p<=end; p++) html += li(p, String(p), false, p===cur);
    html += li(Math.min(tp, cur+1), '&raquo;', cur>=tp, false);
    return html;
  }

  function bindPager(topicId){
    const pager = acc.querySelector(`ul.pagination[data-pager="${topicId}"]`);
    if(!pager) return;
    pager.addEventListener('click', (e)=>{
      const a = e.target.closest('.page-link'); if(!a) return;
      e.preventDefault();
      const p = parseInt(a.dataset.page, 10);
      if(Number.isNaN(p)) return;
      pageByTopic.set(topicId, p);
      renderAccordion(currentSortedTopics()); // re-render the whole accordion (keeps single-open behavior via data-bs-parent)
    });
  }

  function bindTableInteractions(topicId){
    const tbody = document.getElementById(`tbody-${topicId}`);
    if(!tbody) return;

    // Drag & drop within this topic
    let dragSrcEl = null;
    function onDragStart(e){ const row=e.currentTarget; dragSrcEl=row; row.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; try{e.dataTransfer.setData('text/plain', row.dataset.id||'');}catch(_){}} 
    function onDragOver(e){ e.preventDefault(); const t=e.currentTarget; if(t===dragSrcEl) return; t.classList.add('drop-target'); }
    function onDragLeave(e){ e.currentTarget.classList.remove('drop-target'); }
    function onDrop(e){
      e.stopPropagation();
      const target = e.currentTarget;
      target.classList.remove('drop-target');
      const dragging = dragSrcEl;
      if (!dragging || dragging === target) return;

      const rect = target.getBoundingClientRect();
      const before = (e.clientY - rect.top) < (rect.height / 2);

      if (before) target.parentNode.insertBefore(dragging, target);
      else target.parentNode.insertBefore(dragging, target.nextSibling);

      persistOrderFromDOM(topicId).catch(err => showError(err.message || 'Failed to reorder'));
    }
    function onDragEnd(e){ e.currentTarget.classList.remove('dragging'); tbody.querySelectorAll('.drop-target').forEach(el=>el.classList.remove('drop-target')); }

    tbody.querySelectorAll('tr[draggable="true"]').forEach(row=>{
      row.addEventListener('dragstart', onDragStart);
      row.addEventListener('dragover', onDragOver);
      row.addEventListener('dragleave', onDragLeave);
      row.addEventListener('drop', onDrop);
      row.addEventListener('dragend', onDragEnd);
    });

    // Toggle status
    tbody.addEventListener('change', async (e)=>{
      const input = e.target.closest('.js-toggle'); if(!input) return;
      const tr = e.target.closest('tr'); const id = tr?.dataset?.id; if(!id) return;
      const willBeActive = input.checked;
      const msg = willBeActive ? 'Activate this module?' : 'Deactivate this module?';
      const confirm = await Swal.fire({ title:'Confirm', text:msg, icon:'question', showCancelButton:true, confirmButtonText:'Yes' });
      if(!confirm.isConfirmed){ input.checked = !willBeActive; return; }

      try{
        const res = await fetch(`${API_MODULES}/${id}/toggle-status`, { method:'PATCH', headers:{ 'Authorization':`Bearer ${getToken()}` }});
        const json = await res.json();
        if(!res.ok) throw new Error(json.message || 'Toggle failed');

        showSuccess('Status updated');

        const list = modulesByTopic.get(topicId) || [];
        const idx = list.findIndex(m => String(m.id) === String(id));
        if(idx>-1){ list[idx].status = json.new_status || (willBeActive?'active':'inactive'); }
        applyFiltersAndRender();
      }catch(err){
        showError(err.message || 'Toggle failed'); input.checked = !input.checked;
      }
    });

    // Edit click
    tbody.addEventListener('click', (e)=>{
      const btn = e.target.closest('button[data-action]'); if(!btn) return;
      if(btn.dataset.action === 'edit'){
        const tr = btn.closest('tr'); const id = tr?.dataset?.id; if(!id) return;
        const original = btn.innerHTML; btn.disabled=true; btn.innerHTML=`<span class="spinner-border spinner-border-sm me-2"></span>Loading…`;
        openEdit(id).catch(err=>showError(err.message||'Fetch failed')).finally(()=>{ btn.disabled=false; btn.innerHTML=original; });
      }
    });
  }

  // ===== Filters & sorting =====
  function applyFiltersAndRender(){
    let totalModules = 0;
    filteredByTopic.clear();

    activeTopics.forEach(t=>{
      const list = modulesByTopic.get(t.id) || [];
      const nq = normalize(q);
      const filtered = list.filter(row=>{
        const sOk = statusFilter ? (row.status === statusFilter) : true;
        if(!sOk) return false;
        if(!nq) return true;
        const hay = [row.title, row.slug, row.status, t.title, row.description].map(normalize).join(' ');
        return hay.includes(nq);
      });

      // Sort by sort_order asc, then title
      filtered.sort((a,b)=>{
        const da = a.sort_order ?? 0, db = b.sort_order ?? 0;
        if (da !== db) return da - db;
        return (a.title||'').localeCompare(b.title||'');
      });

      filteredByTopic.set(t.id, filtered);
      totalModules += filtered.length;

      // Reset page to 1 if current exceeds total pages
      const tp = Math.max(1, Math.ceil(filtered.length / perPage));
      const cur = Math.min(pageByTopic.get(t.id) || 1, tp);
      pageByTopic.set(t.id, cur);
    });

    modulesCountEl.textContent = `${totalModules} modules total`;
    renderAccordion(currentSortedTopics());
  }

  function currentSortedTopics(){
    const topicsToRender = [...activeTopics];
    if (q) {
      topicsToRender.sort((a,b)=>{
        const ca = (filteredByTopic.get(a.id) || []).length;
        const cb = (filteredByTopic.get(b.id) || []).length;
        if (cb !== ca) return cb - ca;
        return (a.title||'').localeCompare(b.title||'');
      });
    }
    return topicsToRender;
  }

  // ===== Modal (Add / Edit) =====
  function onClickAddModule(){
    resetModuleForm();
    moduleModalTitle.textContent = 'Add Module';
    moduleModal.show();
  }

  function resetModuleForm(){
    moduleForm.reset();
    moduleId.value = '';
    if (moduleTopic.options.length) moduleTopic.selectedIndex = 0;
    if (window.jQuery && jQuery.fn.select2){
      jQuery('#moduleTopic').trigger('change.select2');
      jQuery('#moduleStatus').val('active').trigger('change.select2');
    }
  }

  async function openEdit(id){
    const res = await fetch(`${API_MODULES}/${id}`, { headers:{ 'Authorization':`Bearer ${getToken()}` }});
    const json = await res.json();
    if(!res.ok) throw new Error(json.message || 'Fetch failed');

    const m = json.data || json;
    resetModuleForm();
    moduleId.value     = m.id;
    moduleTitle.value  = m.title || '';
    moduleDesc.value   = m.description || '';
    moduleStatus.value = m.status || 'active';
    if (String(m.topic_id)) {
      moduleTopic.value = String(m.topic_id);
      if (window.jQuery && jQuery.fn.select2) jQuery('#moduleTopic').val(String(m.topic_id)).trigger('change.select2');
    }

    moduleModalTitle.textContent = 'Edit Module';
    moduleModal.show();
  }

  // Create / Update
  moduleForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(!moduleTitle.value.trim()) return moduleTitle.focus();
    if(!moduleTopic.value) return moduleTopic.focus();

    const isEdit = !!moduleId.value;
    const url = isEdit ? `${API_MODULES}/${moduleId.value}` : API_MODULES;
    const method = 'POST';
    const fd = new FormData();
    if(isEdit) fd.append('_method','PUT');
    fd.append('topic_id', moduleTopic.value);
    fd.append('title', moduleTitle.value.trim());
    if(moduleDesc.value.trim()) fd.append('description', moduleDesc.value.trim());
    fd.append('status', moduleStatus.value);

    try{
      saveModuleBtn.disabled = true; saveModuleBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
      const res = await fetch(url, { method, headers:{ 'Authorization':`Bearer ${getToken()}` }, body: fd });
      const json = await res.json();
      if(!res.ok) throw new Error(json.message || 'Save failed');

      moduleModal.hide();
      showSuccess(isEdit ? 'Module updated' : 'Module created');

      const tId = parseInt(moduleTopic.value,10);
      await fetchModulesForTopic(tId);
      applyFiltersAndRender();
    }catch(err){
      showError(err.message || 'Save failed');
    }finally{
      saveModuleBtn.disabled = false; saveModuleBtn.innerHTML = '<i class="fa fa-save me-2"></i>Save';
    }
  });

  // ===== Persist reorder (within a topic) =====
  function getIdsFromDOM(topicId){
    const tbody = document.getElementById(`tbody-${topicId}`);
    if(!tbody) return [];
    return Array.from(tbody.querySelectorAll('tr[data-id]')).map(tr => parseInt(tr.dataset.id,10));
  }

  async function persistOrderFromDOM(topicId){
    const domIds = getIdsFromDOM(topicId);
    const view = filteredByTopic.get(topicId) || [];
    const idToItem = new Map(view.map(m=>[m.id, m]));
    const newOrderForPage = domIds.map(id => idToItem.get(id)).filter(Boolean);

    // Merge new page order back into full filtered view
    const curPage = pageByTopic.get(topicId) || 1;
    const start = (curPage-1) * perPage;
    const merged = [...view];
    for(let i=0;i<newOrderForPage.length;i++){
      merged[start + i] = newOrderForPage[i];
    }
    filteredByTopic.set(topicId, merged);

    // Send full order (based on merged)
    const orderedIds = (merged.length ? merged : (modulesByTopic.get(topicId) || [])).map(m => m.id);

    const res = await fetch(`${API_MODULES}/reorder`, {
      method:'POST',
      headers:{ 'Content-Type':'application/json', 'Authorization': `Bearer ${getToken()}` },
      body: JSON.stringify({ order: orderedIds })
    });
    const json = await res.json();
    if(!res.ok) throw new Error(json.message || 'Reorder failed');

    showSuccess('Sort order updated');

    // Update canonical list sort_order locally
    const canonical = modulesByTopic.get(topicId) || [];
    orderedIds.forEach((id, idx)=>{
      const it = canonical.find(x=>x.id===id);
      if (it) it.sort_order = idx;
    });

    applyFiltersAndRender();
  }

  // ===== Filters wiring =====
  const onSearch = debounce(()=>{ q = searchInput.value.trim(); /* reset pages */ pageByTopic = new Map(); applyFiltersAndRender(); }, 250);
  searchInput.addEventListener('input', onSearch);
  statusSelect?.addEventListener('change', ()=>{ statusFilter = statusSelect.value; pageByTopic = new Map(); applyFiltersAndRender(); });
  perPageSel?.addEventListener('change', ()=>{ perPage = parseInt(perPageSel.value,10) || 10; /* keep current page per topic within bounds */ applyFiltersAndRender(); });

  // Filters modal open/apply
  btnFilters.addEventListener('click', ()=>{
    statusFilterModal.value = statusFilter || '';
    perPageModal.value = String(perPage);
    filtersModal.show();
  });
  applyFiltersBtn.addEventListener('click', ()=>{
    statusFilter = statusFilterModal.value || '';
    perPage = parseInt(perPageModal.value,10) || 10;
    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#statusFilter').val(statusFilter).trigger('change.select2');
      jQuery('#perPage').val(String(perPage)).trigger('change.select2');
    } else {
      document.getElementById('statusFilter').value = statusFilter;
      document.getElementById('perPage').value = String(perPage);
    }
    filtersModal.hide();
    pageByTopic = new Map();
    applyFiltersAndRender();
  });

  // ===== Select2 =====
  function enhanceSelect(selector, parent){
    if (!window.jQuery || !jQuery.fn.select2) return;
    const $el = jQuery(selector);
    if (!$el.length) return;
    const val = $el.val();
    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
    $el.select2({ width:'100%', dropdownParent: parent ? jQuery(parent) : jQuery(document.body), minimumResultsForSearch: 0 });
    if (typeof val !== 'undefined') $el.val(val).trigger('change.select2');
  }

  function initAllSelect2(){
    enhanceSelect('#statusFilter');
    enhanceSelect('#perPage');
    enhanceSelect('#moduleTopic', '#moduleModal');
    enhanceSelect('#moduleStatus', '#moduleModal');
    enhanceSelect('#statusFilterModal', '#filtersModal');
    enhanceSelect('#perPageModal', '#filtersModal');

    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#statusFilter').val(statusFilter || '').trigger('change.select2');
      jQuery('#perPage').val(String(perPage)).trigger('change.select2');
      jQuery('#statusFilterModal').val(statusFilter || '').trigger('change.select2');
      jQuery('#perPageModal').val(String(perPage)).trigger('change.select2');
    }
  }

  // ===== Bootstrap =====
  async function init(){
    try{
      await fetchActiveTopics();

      // Populate topic select
      moduleTopic.innerHTML = activeTopics.length
        ? activeTopics.map(t => `<option value="${t.id}">${esc(t.title)}</option>`).join('')
        : '<option value="">No active topics</option>';

      // Load modules for each active topic
      for (const t of activeTopics){
        await fetchModulesForTopic(t.id);
        pageByTopic.set(t.id, 1);
      }

      initAllSelect2();

      accLoading?.remove();
      applyFiltersAndRender();
    }catch(err){
      acc.innerHTML = `<div class="text-danger p-4">${esc(err.message || 'Failed to load data')}</div>`;
    }
  }

  init();
})();
</script>
@endpush
