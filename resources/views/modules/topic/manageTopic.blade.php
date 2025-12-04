{{-- resources/views/admin/topics/index.blade.php --}}
{{-- Topic Manage Page — EXACT same styling/layout as your Clients page.
    Additions: drag-and-drop reordering for topics (persisted to /api/topics/reorder).
    This version fixes the "allTopics.filter is not a function" issue by normalizing API responses. --}}

{{-- ---------- Styles (pushable) ---------- --}}
@push('styles')
<style>
  /* === Page-level overflow guard (mirror Users page behavior) === */
  html, body { width:100%; max-width:100%; overflow-x:hidden; }
  .layout, .right-panel, .main-content { overflow-x:hidden; }

  /* Page indicator (left) */
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

  /* Toolbar */
  .clients-toolbar{
    display:flex;gap:10px;justify-content:space-between;align-items:center;margin:12px 0;
  }
  .clients-toolbar .left{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .clients-toolbar .right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

  /* Table polish + make container own the scroll (not the page) */
  .table-wrap{
    border-top:1px solid var(--border-color);
    width:100%;
    overflow:hidden;
    border-radius: var(--radius-md);
  }
  .table-wrap .table-responsive{
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
  }

  .logo-thumb{width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);background:var(--light-color)}
  .badge-status{text-transform:uppercase}
  .form-switch .form-check-input{width:2.5rem;height:1.3rem}

  /* Drag handle */
  .drag-handle{ cursor:grab; opacity:.85; }
  tr.dragging{ opacity:.6; }
  tr.drop-target{ outline:2px dashed var(--accent-color); }

  /* Footer */
  .pager-wrap{display:flex;justify-content:flex-end}

  /* Modal preview */
  .img-preview{width:72px;height:72px;border:1px solid var(--border-color);border-radius:8px;object-fit:cover;background:var(--light-color)}

  .tiny{font-size:12px;color:#6b7280}
  .filter-chip{
    border:1px dashed var(--border-color);
    padding:6px 10px;border-radius:8px;
    background: var(--bg-body);
    color: var(--text-color);
  }

  /* Dark-mode corrections (surfaces that were white before) */
  html.theme-dark .page-indicator{background: var(--light-color); border-color: var(--border-color); color: var(--text-color);}
  html.theme-dark .filter-chip{background: var(--light-color); border-color: var(--border-color); color: var(--text-color);}
  html.theme-dark .tiny{color:#93a4b8;}
  html.theme-dark .bg-white{background: var(--light-color)!important; color: var(--text-color)!important; border-color: var(--border-color)!important;}

  /* Dark-mode switch knob visibility (if not already in global) */
  html.theme-dark .form-switch .form-check-input{
    background-color:#1e293b;
    border-color:var(--border-color);
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
    .table { min-width: 820px; } /* slightly wider to include drag column */
    .clients-toolbar{flex-direction:column; align-items:stretch; gap:12px;}
    .clients-toolbar .right{justify-content:flex-end;}
  }
  @media (max-width: 576px){
    .table { min-width: 720px; }
    .table thead th:nth-child(7),
    .table tbody td:nth-child(7)  /* Created */
    { display:none; }

    .filters-inline { display:none !important; }
    #btnFilters { display:inline-flex !important; }
  }
  #btnFilters { display:none; }

  th.sortable .tiny-sort-icon{ font-size:12px; margin-left:6px; opacity:.6; }
  th.sortable .tiny-sort-icon.active{ opacity:1; color:var(--accent-color); }

  /* ========== Select2 polish & fixes ========== */
  .select2-container{ min-width:140px; width:100% !important; }
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
  .select2-container--default .select2-selection--single .select2-selection__arrow{
    height: calc(1.5em + .5rem + 2px);
  }
  .select2-dropdown{ border-color: var(--border-color); }
  .select2-search__field{ outline: none; color: var(--text-color); background:#fff; }

  .select2-results__option{ color: var(--text-color); }

  /* ===== Dark mode fixes for Select2 ===== */
  html.theme-dark .select2-container--default .select2-selection--single{
    background: #0e1a2d; color: #e5e7eb; border-color: var(--border-color);
  }
  html.theme-dark .select2-dropdown{
    background: #0e1a2d; color: #e5e7eb; border-color: var(--border-color);
  }
  html.theme-dark .select2-results__option{ color: #e5e7eb; }
  html.theme-dark .select2-results__option--highlighted{
    background: rgba(99,102,241,.18); color: #ffffff;
  }
  html.theme-dark .select2-search__field{
    background:#0b1526 !important; color:#ffffff !important; border:1px solid var(--border-color) !important;
  }
  html.theme-dark .select2-container--default .select2-selection--single .select2-selection__rendered{
    color:#ffffff !important;
  }
  html.theme-dark .select2-container--default .select2-results>.select2-results__options{
    scrollbar-color: var(--border-color) transparent;
  }
  html.theme-dark .select2-search__field::placeholder{ color:#93a4b8; }
</style>
@endpush

{{-- ---------- Markup ---------- --}}
<div class="page-head">
  <div class="page-indicator">
    <i class="fa-solid fa-layer-group"></i>
    <strong>Topics</strong>
    <span class="text-muted small ms-1" id="topicsCount">—</span>
  </div>
  <div class="actions"><!-- reserved --></div>
</div>

<div class="clients-toolbar">
  <div class="left">
    <div class="input-group">
      <span class="input-group-text"><i class="fa fa-search"></i></span>
      <input id="searchInput" type="search" class="form-control" placeholder="Search topics by title, slug, status…">
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
          <select id="perPage" class="form-select form-select-sm" style="min-width:100px">
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
    <button class="btn btn-primary" id="btnAddTopic">
      <i class="fa fa-plus me-2"></i>Add Topic
    </button>
  </div>
</div>

<div class="card table-wrap">
  <div class="table-responsive">
    <table class="table table-hover m-0 align-middle" id="topicsTable">
      <thead>
        <tr>
          <th style="width:44px;"></th>          {{-- drag handle --}}
          <th style="width:88px;">Active</th>
          <th style="width:64px;">Image</th>
          <th>Title</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Created</th>
          <th style="width:120px;">Actions</th>
        </tr>
      </thead>
      <tbody id="topicsTbody">
        <tr><td class="text-center p-4 text-muted" colspan="8">Loading…</td></tr>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-white">
    <div class="d-flex justify-content-between align-items-center">
      <div class="text-muted small" id="resultsInfo"></div>
      <nav class="pager-wrap">
        <ul class="pagination m-0" id="pager"></ul>
      </nav>
    </div>
  </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="topicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="topicForm" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="topicModalTitle">Add Topic</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="topicId">

        <div class="mb-3">
          <label class="form-label">Title</label>
          <input class="form-control" id="topicTitle" required maxlength="200" placeholder="e.g., Dynamic Programming">
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" id="topicDesc" rows="3" placeholder="Optional short description…"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select class="form-select" id="topicStatus">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Image (optional)</label>
          <div class="d-flex align-items-center gap-2">
            <img id="imagePreview" class="img-preview" alt="Preview" src="" style="display:none;">
            <input type="file" id="topicImage" accept="image/*" class="form-control">
          </div>
          <div class="form-text">PNG, JPG, WEBP, GIF up to 3MB.</div>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" value="1" id="deleteImageChk">
          <label class="form-check-label tiny" for="deleteImageChk">
            Remove existing image on save
          </label>
        </div>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveTopicBtn">
          <i class="fa fa-save me-2"></i>Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Filters Modal --}}
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

{{-- Top-right Toasts --}}
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

<!-- Core Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jQuery + Select2 (for searchable selects) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@stack('scripts')
@yield('scripts')

{{-- ---------- Scripts (pushable) ---------- --}}
@push('scripts')
<script>
(function(){
  const API_BASE   = '/api/topics';
  const ASSET_BASE = "{{ asset('') }}";

  // ---------- helpers ----------
  function getToken(){ return localStorage.getItem('token') || sessionStorage.getItem('token') || ''; }
  function showToast(id, text){ const el=document.getElementById(id); if(!el) return; el.querySelector('.toast-body').textContent=text; (new bootstrap.Toast(el)).show(); }
  function showSuccess(msg){ showToast('toastSuccess', msg); }
  function showError(msg){ showToast('toastError', msg); }
  const debounce=(fn,ms=400)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}};
  const normalize=s=>(s??'').toString().toLowerCase();
  const esc=s=>(s??'').replace(/[&<>"']/g, ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));

  // Normalize common API payload shapes into an array
  function extractList(json){
    if (Array.isArray(json)) return json;
    if (Array.isArray(json.data)) return json.data;
    if (json?.data && Array.isArray(json.data.data)) return json.data.data;
    if (json?.data && Array.isArray(json.data.items)) return json.data.items;
    if (Array.isArray(json.items)) return json.items;
    if (Array.isArray(json.topics)) return json.topics;
    return [];
  }

  // ---------- DOM refs / state ----------
  const tbody  = document.getElementById('topicsTbody');
  const pager  = document.getElementById('pager');
  const info   = document.getElementById('resultsInfo');
  const countEl= document.getElementById('topicsCount');

  const perPageSel   = document.getElementById('perPage');
  const statusSelect = document.getElementById('statusFilter');
  const btnFilters   = document.getElementById('btnFilters');
  const filtersModal = new bootstrap.Modal(document.getElementById('filtersModal'));
  const statusFilterModal= document.getElementById('statusFilterModal');
  const perPageModal     = document.getElementById('perPageModal');
  const applyFiltersBtn  = document.getElementById('applyFiltersBtn');

  const topicModal = new bootstrap.Modal(document.getElementById('topicModal'));
  const form        = document.getElementById('topicForm');
  const idInput     = document.getElementById('topicId');
  const titleInput  = document.getElementById('topicTitle');
  const descInput   = document.getElementById('topicDesc');
  const statusInput = document.getElementById('topicStatus');
  const imgInput    = document.getElementById('topicImage');
  const imgPrev     = document.getElementById('imagePreview');
  const deleteImageChk = document.getElementById('deleteImageChk');
  const modalTitle  = document.getElementById('topicModalTitle');
  const saveBtn     = document.getElementById('saveTopicBtn');

  const searchInput = document.getElementById('searchInput');
  const btnAdd      = document.getElementById('btnAddTopic');

  let page = 1, perPage = 10, q = '', statusFilter = '';
  let allTopics = [];   // ALWAYS an array (thanks to extractList)
  let viewTopics = [];
  let sortBy = 'sort_order';
  let sortDir = 'asc';

  // ---------- fetching ----------
  async function fetchTopicsPage(p){
    const params = new URLSearchParams({ page: p, per_page: 100 });
    const res = await fetch(`${API_BASE}?${params}`, { headers: { 'Authorization': `Bearer ${getToken()}` }});
    const json = await res.json();
    if (!res.ok) throw new Error(json.message || 'Failed to fetch topics');

    // Normalize paginator meta (support Laravel paginator too)
    if (!json.meta && typeof json.current_page !== 'undefined') {
      json.meta = { current_page: json.current_page, last_page: json.last_page };
    }
    return json;
  }

  async function fetchAllTopics(){
    tbody.innerHTML = `<tr><td class="text-center p-4 text-muted" colspan="8">Loading…</td></tr>`;

    const first = await fetchTopicsPage(1);
    const totalPages = first.meta?.last_page || first.meta?.total_pages || 1;

    let acc = extractList(first);
    for (let p = 2; p <= totalPages; p++){
      const next = await fetchTopicsPage(p);
      acc = acc.concat(extractList(next));
    }

    allTopics = acc; // <-- guaranteed array
    countEl.textContent = `${allTopics.length} total`;
    applyFilters();
  }

  // ---------- filtering/sorting/paging ----------
  function applyFilters(){
    const nq = normalize(q);
    const source = Array.isArray(allTopics) ? allTopics : extractList(allTopics); // belt & suspenders

    viewTopics = source.filter(row=>{
      const sOk = statusFilter ? (row.status === statusFilter) : true;
      if(!sOk) return false;
      if(!nq) return true;
      const hay = [row.title, row.slug, row.status, row.description].map(normalize).join(' ');
      return hay.includes(nq);
    });

    sortView();
    page = Math.max(1, Math.min(page, Math.ceil(viewTopics.length / perPage) || 1));
    renderCurrentPage();
  }

  function sortView(){
    viewTopics.sort((a,b)=>{
      if (sortBy === 'sort_order') {
        const da = a.sort_order ?? 0, db = b.sort_order ?? 0;
        return sortDir === 'asc' ? (da - db) : (db - da);
      }
      if (sortBy === 'created_at') {
        const ta = a.created_at ? new Date(a.created_at).getTime() : 0;
        const tb = b.created_at ? new Date(b.created_at).getTime() : 0;
        return sortDir === 'asc' ? (ta - tb) : (tb - ta);
      }
      const na = (a.title||'').localeCompare(b.title||'');
      return sortDir === 'asc' ? na : -na;
    });
  }

  function renderCurrentPage(){
    const total = viewTopics.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const start = (page-1)*perPage;
    const pageItems = viewTopics.slice(start, start + perPage);
    renderTable(pageItems);
    renderPagination({ page, total_pages: totalPages });
    info.textContent = `${total ? (start + 1) : 0}–${Math.min(start+perPage, total)} of ${total} shown · page ${page}/${totalPages}`;
  }

  function renderTable(items){
    if(!items.length){
      tbody.innerHTML = `
      <tr>
        <td colspan="8" class="text-center p-4 text-muted">
          <div class="d-flex flex-column align-items-center justify-content-center">
            <img src="{{ asset('/assets/media/images/web/no-data.png') }}"
                 alt="No data"
                 style="max-width:160px; opacity:.75; margin-bottom:12px;">
            <div>No topics found</div>
          </div>
        </td>
      </tr>`;
      return;
    }
    tbody.innerHTML = items.map(row=>{
      const active = (row.status === 'active');
      const imgUrl = row.image_path ? ("{{ asset('') }}" + String(row.image_path).replace(/^\/+/,'')) : '';
      const imgHTML = imgUrl
        ? `<img src="${imgUrl}" class="logo-thumb" alt="${esc(row.title)}">`
        : '<span class="text-muted tiny">—</span>';
      const statusBadge =
        row.status === 'active' ? '<span class="badge bg-success badge-status">active</span>' :
        row.status === 'inactive' ? '<span class="badge bg-danger badge-status">inactive</span>' :
        '<span class="badge bg-secondary badge-status">archived</span>';
      const created = row.created_at ? new Date(row.created_at).toLocaleString() : '—';

      return `
        <tr data-id="${row.id}" draggable="true">
          <td class="text-center"><i class="fa fa-grip-vertical drag-handle" title="Drag to reorder"></i></td>
          <td>
            <div class="form-switch">
              <input class="form-check-input js-toggle" type="checkbox" ${active ? 'checked' : ''} aria-label="Toggle active">
            </div>
          </td>
          <td>${imgHTML}</td>
          <td>${esc(row.title || '')}</td>
          <td><code>${esc(row.slug || '')}</code></td>
          <td>${statusBadge}</td>
          <td>${created}</td>
          <td class="row-actions">
            <button class="btn btn-sm ce-like" data-action="edit"><i class="fa fa-pen"></i> <span class="js-edit-text">Edit</span></button>
          </td>
        </tr>
      `;
    }).join('');

    bindDragAndDrop();
  }

  function renderPagination(meta){
    const { page:cur=1, total_pages:tp=1 } = meta;
    let html = '';
    function pageItem(p, label=String(p), disabled=false, active=false){
      const cls = ['page-item', disabled?'disabled':'', active?'active':''].join(' ');
      return `<li class="${cls}">
        <a class="page-link" href="#" data-page="${p}">${label}</a>
      </li>`;
    }
    html += pageItem(Math.max(1, cur-1), '&laquo;', cur<=1);
    const start = Math.max(1, cur-2), end = Math.min(tp, cur+2);
    for(let p=start; p<=end; p++) html += pageItem(p, String(p), false, p===cur);
    html += pageItem(Math.min(tp, cur+1), '&raquo;', cur>=tp);
    pager.innerHTML = html;
  }

  // Pagination click
  pager.addEventListener('click', (e)=>{
    const a = e.target.closest('.page-link');
    if(!a) return;
    e.preventDefault();
    const newPage = parseInt(a.dataset.page,10);
    if(!Number.isNaN(newPage) && newPage !== page){
      page = newPage; renderCurrentPage(); window.scrollTo({top:0, behavior:'smooth'});
    }
  });

  // Search / filters
  const onSearch = debounce(()=>{ q = searchInput.value.trim(); page=1; applyFilters(); }, 250);
  searchInput.addEventListener('input', onSearch);
  statusSelect?.addEventListener('change', ()=>{ statusFilter = statusSelect.value; page=1; applyFilters(); });
  perPageSel?.addEventListener('change', ()=>{ perPage = parseInt(perPageSel.value,10) || 10; page=1; renderCurrentPage(); });

  // Filters modal wiring
  btnFilters.addEventListener('click', ()=>{
    statusFilterModal.value = statusFilter || '';
    perPageModal.value = String(perPage);
    filtersModal.show();
  });
  applyFiltersBtn.addEventListener('click', ()=>{
    statusFilter = statusFilterModal.value || '';
    perPage = parseInt(perPageModal.value,10) || 10;
    if(statusSelect) { statusSelect.value = statusFilter; if (window.jQuery && jQuery.fn.select2) jQuery(statusSelect).val(statusFilter).trigger('change.select2'); }
    if(perPageSel)   { perPageSel.value   = String(perPage); if (window.jQuery && jQuery.fn.select2) jQuery(perPageSel).val(String(perPage)).trigger('change.select2'); }
    page=1; filtersModal.hide(); applyFilters();
  });

  // Add
  document.getElementById('btnAddTopic').addEventListener('click', ()=>{
    resetForm();
    modalTitle.textContent = 'Add Topic';
    topicModal.show();
  });

  // Toggle status
  tbody.addEventListener('change', async (e)=>{
    const input = e.target.closest('.js-toggle'); if(!input) return;
    const tr = e.target.closest('tr'); const id = tr?.dataset?.id; if(!id) return;
    const willBeActive = input.checked;
    const msg = willBeActive ? 'Activate this topic?' : 'Deactivate this topic?';
    const confirm = await Swal.fire({ title:'Confirm', text:msg, icon:'question', showCancelButton:true, confirmButtonText:'Yes' });
    if(!confirm.isConfirmed){ input.checked = !willBeActive; return; }

    try{
      const res = await fetch(`${API_BASE}/${id}/toggle-status`, { method:'PATCH', headers:{ 'Authorization':`Bearer ${getToken()}` }});
      const json = await res.json();
      if(!res.ok) throw new Error(json.message || 'Toggle failed');

      showSuccess('Status updated');

      const idx = allTopics.findIndex(t => String(t.id) === String(id));
      if(idx>-1){ allTopics[idx].status = json.new_status || (willBeActive?'active':'inactive'); }
      applyFilters();
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

  // Image preview
  imgInput.addEventListener('change', ()=>{
    const file = imgInput.files?.[0];
    if(!file){ imgPrev.style.display='none'; imgPrev.src=''; return; }
    const rd = new FileReader();
    rd.onload = e => { imgPrev.src = e.target.result; imgPrev.style.display='block'; };
    rd.readAsDataURL(file);
  });

  // Save (create/update)
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(!titleInput.value.trim()) return titleInput.focus();

    const isEdit = !!idInput.value;
    const url = isEdit ? `${API_BASE}/${idInput.value}` : API_BASE;
    const method = 'POST'; // always POST with _method for PUT
    const fd = new FormData();
    if(isEdit) fd.append('_method','PUT');
    fd.append('title', titleInput.value.trim());
    if(descInput.value.trim()) fd.append('description', descInput.value.trim());
    fd.append('status', statusInput.value);
    if(imgInput.files?.[0]) fd.append('image', imgInput.files[0]);
    if(deleteImageChk.checked) fd.append('delete_image', '1');

    try{
      saveBtn.disabled = true; saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
      const res = await fetch(url, { method, headers:{ 'Authorization':`Bearer ${getToken()}` }, body: fd });
      const json = await res.json();
      if(!res.ok) throw new Error(json.message || 'Save failed');

      topicModal.hide();
      showSuccess(isEdit ? 'Topic updated' : 'Topic created');
      await fetchAllTopics();
    }catch(err){
      showError(err.message || 'Save failed');
    }finally{
      saveBtn.disabled = false; saveBtn.innerHTML = '<i class="fa fa-save me-2"></i>Save';
    }
  });

  async function openEdit(id){
    const res = await fetch(`${API_BASE}/${id}`, { headers:{ 'Authorization':`Bearer ${getToken()}` }});
    const json = await res.json();
    if(!res.ok) throw new Error(json.message || 'Fetch failed');

    resetForm();
    const t = json.data || json.topic || json;
    idInput.value     = t.id;
    titleInput.value  = t.title || '';
    descInput.value   = t.description || '';
    statusInput.value = t.status || 'active';

    if (t.image_path) {
      imgPrev.src = "{{ asset('') }}" + String(t.image_path).replace(/^\/+/, '');
      imgPrev.style.display='block';
    }
    modalTitle.textContent = 'Edit Topic';
    topicModal.show();
  }

  function resetForm(){
    form.reset();
    idInput.value = '';
    imgPrev.src = ''; imgPrev.style.display='none';
    deleteImageChk.checked = false;
    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#topicStatus').val('active').trigger('change.select2');
    }
  }

  // ===================== Drag & Drop Reordering =====================
  let dragSrcEl = null;

  function bindDragAndDrop(){
    const rows = tbody.querySelectorAll('tr[draggable="true"]');
    rows.forEach(row=>{
      row.addEventListener('dragstart', onDragStart);
      row.addEventListener('dragover', onDragOver);
      row.addEventListener('dragleave', onDragLeave);
      row.addEventListener('drop', onDrop);
      row.addEventListener('dragend', onDragEnd);
    });
  }

  function onDragStart(e){
    const row = e.currentTarget;
    dragSrcEl = row;
    row.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    try { e.dataTransfer.setData('text/plain', row.dataset.id || ''); } catch(_){}
  }

  function onDragOver(e){
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const target = e.currentTarget;
    if (target === dragSrcEl) return;
    target.classList.add('drop-target');
  }

  function onDragLeave(e){
    e.currentTarget.classList.remove('drop-target');
  }

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

    persistOrderFromDOM().catch(err => showError(err.message || 'Failed to reorder'));
  }

  function onDragEnd(e){
    e.currentTarget.classList.remove('dragging');
    tbody.querySelectorAll('.drop-target').forEach(el=>el.classList.remove('drop-target'));
  }

  function getIdsFromDOM(){
    return Array.from(tbody.querySelectorAll('tr[data-id]')).map(tr => parseInt(tr.dataset.id,10));
  }

  async function persistOrderFromDOM(){
    const pageStart = (page-1)*perPage;
    const domIds = getIdsFromDOM();
    const idToTopic = new Map(viewTopics.map(t=>[t.id, t]));
    const newPageTopics = domIds.map(id => idToTopic.get(id)).filter(Boolean);

    viewTopics = [
      ...viewTopics.slice(0, pageStart),
      ...newPageTopics,
      ...viewTopics.slice(pageStart + newPageTopics.length)
    ];

    const orderedIds = viewTopics.map(t=>t.id);

    const res = await fetch(`${API_BASE}/reorder`, {
      method:'POST',
      headers:{ 'Content-Type':'application/json', 'Authorization': `Bearer ${getToken()}` },
      body: JSON.stringify({ order: orderedIds })
    });
    const json = await res.json();
    if(!res.ok) throw new Error(json.message || 'Reorder failed');

    showSuccess('Sort order updated');

    // Reflect local sort_order so UI remains consistent
    viewTopics.forEach((t, idx)=> t.sort_order = idx);
    const mapAll = new Map(allTopics.map(t=>[t.id, t]));
    allTopics = viewTopics.map(t=> Object.assign(mapAll.get(t.id)||{}, t));
    renderCurrentPage();
  }

  // ===================== Select2 Enhancements =====================
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
    enhanceSelect('#topicStatus', '#topicModal'); 
    enhanceSelect('#statusFilterModal', '#filtersModal');
    enhanceSelect('#perPageModal', '#filtersModal');

    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#statusFilter').val(statusFilter || '').trigger('change.select2');
      jQuery('#perPage').val(String(perPage)).trigger('change.select2');
      jQuery('#statusFilterModal').val(statusFilter || '').trigger('change.select2');
      jQuery('#perPageModal').val(String(perPage)).trigger('change.select2');
      jQuery('#topicStatus').val('active').trigger('change.select2');
    }
  }

  jQuery(document)
    .off('change.select2fix', '#statusFilter, #statusFilterModal, #perPage, #perPageModal')
    .on('change.select2fix', '#statusFilter, #statusFilterModal', function(){
      statusFilter = this.value || '';
      jQuery('#statusFilter, #statusFilterModal').val(statusFilter).trigger('change.select2');
      page = 1; applyFilters();
    })
    .on('change.select2fix', '#perPage, #perPageModal', function(){
      perPage = parseInt(this.value,10) || 10;
      jQuery('#perPage, #perPageModal').val(String(perPage)).trigger('change.select2');
      page = 1; renderCurrentPage();
    });

  document.getElementById('filtersModal')?.addEventListener('shown.bs.modal', ()=>{
    enhanceSelect('#statusFilterModal', '#filtersModal');
    enhanceSelect('#perPageModal', '#filtersModal');
    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#statusFilterModal').val(statusFilter || '').trigger('change.select2');
      jQuery('#perPageModal').val(String(perPage)).trigger('change.select2');
    }
  });

  // ===== Init =====
  initAllSelect2();
  fetchAllTopics().catch(err=>{
    tbody.innerHTML = `<tr><td colspan="8" class="text-danger p-4">${err.message}</td></tr>`;
  });

})();
</script>
@endpush
