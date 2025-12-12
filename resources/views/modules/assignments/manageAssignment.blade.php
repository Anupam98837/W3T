
@section('content')
<div class="sm-wrap">

  {{-- ===== Toolbar: Course & Batch only ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0" for="courseSel">Course</label>
        <select id="courseSel" class="form-select" style="min-width:240px;">
          <option value="">Select a course…</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0" for="batchSel">Batch</label>
        <select id="batchSel" class="form-select" style="min-width:220px;" disabled>
          <option value="">Select a batch…</option>
        </select>
      </div>

      <div class="ms-auto small text-muted d-none d-md-block">
        Pick Course & Batch to load assignments.
      </div>
    </div>
  </div>

  {{-- ===== Tabs (Active + Bin) ===== --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" href="javascript:void(0)" id="asTabActive" data-scope="active"><i class="fa-solid fa-clipboard-check me-2"></i> Active</a></li>
    <li class="nav-item"><a class="nav-link" href="javascript:void(0)" id="asTabBin" data-scope="bin"><i class="fa-solid fa-trash-can me-2"></i> Bin</a></li>
  </ul>

  {{-- ===== Toolbar Panel ===== --}}
  <div class="panel mb-3">
    <div class="row align-items-center g-2 px-3 pt-3 pb-2 mfa-toolbar" id="listToolbar">
      <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">

        <div class="d-flex align-items-center gap-2">
          <label for="perPageSel" class="text-muted small mb-0">Per page</label>
          <select id="perPageSel" class="form-select form-select-sm" style="width:auto; min-width:90px;">
            <option value="10">10</option>
            <option value="20" selected>20</option>
            <option value="30">30</option>
            <option value="50">50</option>
          </select>
        </div>

        <div class="position-relative flex-grow-1" style="min-width:260px;">
          <input id="q" type="text" class="form-control ps-5" placeholder="Search title/instructions…" disabled>
          <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
        </div>

      </div>

      <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end gap-2">
        <button type="button" id="btnFilters" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#asFilterModal"><i class="fa fa-filter me-1"></i> Filters</button>

        <a id="btnCreate" href="/admin/assignments/create" class="btn btn-primary" data-create-url="/admin/assignments/create" disabled>
          <i class="fa fa-plus me-1"></i> New Assignment
        </a>
      </div>
    </div>
  </div>

  {{-- ===== Card: Toolbar + Table ===== --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
            <tr>
              <th>TITLE</th>
              <th style="width:20%;">MODULE</th>
              <th style="width:16%;">BATCH</th>
              <th style="width:14%" class="text-center">SUBMISSIONS</th>
              <th class="sortable" data-col="created_at" style="width:160px;">CREATED <span class="caret"></span></th>
              <th class="text-end" style="width:112px;">ACTIONS</th>
            </tr>
          </thead>
          <tbody id="rows">
            <tr id="loaderRow" style="display:none;"><td colspan="6" class="p-0"><div class="p-4"><div class="placeholder-wave"><div class="placeholder col-12 mb-2" style="height:18px;"></div><div class="placeholder col-12 mb-2" style="height:18px;"></div><div class="placeholder col-12 mb-2" style="height:18px;"></div></div></div></td></tr>
            <tr id="ask"><td colspan="6" class="p-4 text-center text-muted"><i class="fa fa-clipboard mb-2" style="font-size:28px;opacity:.6"></i><div>Please select Course → Batch to load assignments.</div></td></tr>
          </tbody>
        </table>
      </div>

      <div id="empty" class="empty p-4 text-center" style="display:none;"><i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i><div>No assignments found.</div></div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="metaTxt">—</div>
        <nav style="position:relative; z-index:1;"><ul id="pager" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>

</div>

{{-- View Assignment modal (preview instructions + attachments) --}}
<div class="modal fade" id="asViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-eye fa-fw me-2"></i>View Assignment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-4" id="asAttList"></div>
          <div class="col-lg-8">
            <h5 id="asViewTitle">—</h5>
            <div class="small text-muted mb-2" id="asViewMeta">—</div>
            <div id="asViewer" class="viewer-wrap"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="asFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="fa fa-sliders me-2"></i> Filters</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Status</label><select id="fltStatus" class="form-select"><option value="">All</option><option value="published">Published</option><option value="draft">Draft</option><option value="archived">Archived</option></select></div>
        <div><label class="form-label">Sort by</label><select id="fltSort" class="form-select"><option value="-created_at" selected>Newest first</option><option value="created_at">Oldest first</option><option value="title">Title A → Z</option><option value="-title">Title Z → A</option></select></div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" id="fltApply"><i class="fa fa-filter me-1"></i> Apply</button></div>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  // small runtime CSS patch (avoid editing CSS files; safe to run once)
  (function injectRuntimeStyles(){
    const id = 'as-manage-runtime-styles';
    if(document.getElementById(id)) return;
    const css = `
      .table-responsive { overflow: visible !important; }
      table { overflow: visible !important; }
      .dim { pointer-events: none; }
      .dim.show { pointer-events: auto; }
    `;
    const s = document.createElement('style');
    s.id = id;
    s.appendChild(document.createTextNode(css));
    document.head.appendChild(s);
  })();

  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!TOKEN){ alert('Login required'); location.href='/'; return; }

  // ===== API endpoints — aligned with your Laravel routes =====
  const API = {
  index: (qs)=> '/api/assignments?' + qs.toString(),
  show: (assignment)=> `/api/assignments/${encodeURIComponent(assignment)}`,
  destroy: (id, force=false)=> force
    ? `/api/assignments/${encodeURIComponent(id)}/force`
    : `/api/assignments/${encodeURIComponent(id)}`,
  restore: (id)=> `/api/assignments/${encodeURIComponent(id)}/restore`,
  file: (id)=> `/api/assignments/file/${encodeURIComponent(id)}`,
  binIndex: (qs)=> '/api/assignments/bin?' + (qs ? qs.toString() : '')
};


  const H = {
    esc: (s)=>{ const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;","`":"&#96;"}; return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>m[ch]); },
    fmtDateTime(iso){ if(!iso) return '-'; const d=new Date(iso); return isNaN(d)? H.esc(iso) : d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'}); }
  };

  // DOM (defensive queries)
  const courseSel = document.getElementById('courseSel');
  const batchSel  = document.getElementById('batchSel');
  const q         = document.getElementById('q');
  const btnCreate = document.getElementById('btnCreate');
  const rowsEl    = document.getElementById('rows');
  const loaderRow = document.getElementById('loaderRow');
  const askEl     = document.getElementById('ask');
  const emptyEl   = document.getElementById('empty');
  const pager     = document.getElementById('pager');
  const metaTxt   = document.getElementById('metaTxt');
  const perPageSel= document.getElementById('perPageSel');
  const fltStatus = document.getElementById('fltStatus');
  const fltSort   = document.getElementById('fltSort');
  const fltApply  = document.getElementById('fltApply');
  const smTabActive = document.getElementById('asTabActive');
  const smTabBin    = document.getElementById('asTabBin');
  const listToolbar = document.getElementById('listToolbar');

  // viewer modal elements (defensive)
  const asViewModalEl = document.getElementById('asViewModal');
  const vModal = asViewModalEl ? new bootstrap.Modal(asViewModalEl) : null;
  const asAttList = document.getElementById('asAttList');
  const asViewer  = document.getElementById('asViewer');
  const asViewTitle = document.getElementById('asViewTitle');
  const asViewMeta  = document.getElementById('asViewMeta');

  let sort = '-created_at', page = 1, perPage = 20, scope = 'active', binMode=false, statusFilter='';
  let modulesForTable = [];
  const moduleAssignmentsCache = new Map();
  const moduleFetchControllers = new Map();

  /* ================= helpers for dropdowns ================= */
  function initDropdowns(root=document){
    if(!root) return;
    root.querySelectorAll('[data-bs-toggle="dropdown"], .dd-toggle').forEach(btn=>{
      try{
        // Only create one instance
        bootstrap.Dropdown.getOrCreateInstance(btn, {
          autoClose: 'outside',
          boundary: 'viewport',
          popperConfig: { strategy: 'fixed' }
        });
      }catch(_){}
    });
  }

  // delegated click for custom .dd-toggle buttons (keeps consistent behaviour)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.dd-toggle');
    if(!btn) return;
    e.preventDefault();
    e.stopPropagation();
    try{
      const dd = bootstrap.Dropdown.getOrCreateInstance(btn, {
        autoClose: 'outside',
        boundary: 'viewport',
        popperConfig: { strategy: 'fixed' }
      });
      dd.toggle();
    }catch(_){}
  });

  /* ================= scope handling ================= */
  function setScope(newScope){
    scope = newScope;
    binMode = (scope === 'bin');

    if(smTabActive && smTabBin){
      smTabActive.classList.toggle('active', scope==='active');
      smTabBin.classList.toggle('active', scope==='bin');
    }

    const toolbarPanel = listToolbar?.closest?.('.panel');
    if(toolbarPanel) toolbarPanel.classList.toggle('d-none', scope==='bin');

    if(scope==='bin'){
      listToolbar && listToolbar.classList.add('opacity-75');
      if(q) q.disabled = true;
      if(btnCreate) btnCreate.disabled = true;
    } else {
      listToolbar && listToolbar.classList.remove('opacity-75');
      if(q) q.disabled = !(batchSel && Array.from(batchSel.options).some(o=>o.value));
      if(btnCreate) btnCreate.disabled = !(batchSel && batchSel.value);
    }

    page = 1;
    moduleAssignmentsCache.clear();

    if(batchSel && batchSel.value) renderModuleTable();
    else {
      // clear rows safely
      rowsEl && rowsEl.querySelectorAll && rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
      if(emptyEl) emptyEl.style.display = 'none';
      if(askEl) askEl.style.display = '';
      if(pager) pager.innerHTML = '';
      if(metaTxt) metaTxt.textContent = '—';
    }
  }

  /* ================= init ================= */
  loadCourses();
  wire();
  setScope('active');

  async function loadCourses(){
    if(!courseSel) return;
    try{
      const res = await fetch('/api/courses?status=published&per_page=1000',{headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
      const j = await res.json();
      if(!res.ok) throw new Error(j?.message||'Failed to load courses');
      const items = j?.data||[];
      courseSel.innerHTML = '<option value="">Select a course…</option>' + items.map(c=>`<option value="${c.id}" data-uuid="${H.esc(c.uuid||'')}">${H.esc(c.title||'(untitled)')}</option>`).join('');
    }catch(e){
      console.error(e);
    }
  }

  async function loadModules(courseId){
    try{
      const res = await fetch(`/api/course-modules?course_id=${encodeURIComponent(courseId)}&per_page=1000`,{headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
      const j = await res.json();
      if(!res.ok) throw new Error(j?.message||'Failed to load modules');
      modulesForTable = j?.data || [];
    }catch(e){
      modulesForTable = [];
      console.error(e);
    }
  }

  async function loadBatches(courseId){
    if(!batchSel) return;
    try{
      const qs = new URLSearchParams({course_id:courseId, per_page:'200'});
      const res = await fetch('/api/batches?'+qs.toString(),{headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'}});
      const j = await res.json();
      if(!res.ok) throw new Error(j?.message||'Failed to load batches');
      const items = j?.data||[];
      batchSel.innerHTML = '<option value="">Select a batch…</option>' + items.map(b=>`<option value="${b.id}" data-uuid="${H.esc(b.uuid||'')}">${H.esc(b.badge_title||b.tagline||'(untitled)')}</option>`).join('');
      batchSel.disabled = false;
    }catch(e){
      batchSel.innerHTML = '<option value="">— Failed to load —</option>';
      batchSel.disabled = true;
      console.error(e);
    }
  }

  /* ================= wiring ================= */
  function wire(){
    // sortable headers
    document.querySelectorAll('thead th.sortable').forEach(th=>{
      th.addEventListener('click', ()=>{
        const col = th.dataset.col;
        sort = (sort===col) ? ('-'+col) : (sort==='-'+col ? col : (col==='created_at' ? '-created_at' : col));
        page = 1; loadList();
        document.querySelectorAll('thead th.sortable').forEach(t=> t.classList.remove('asc','desc'));
        if(sort===col) th.classList.add('asc');
        if(sort==='-'+col) th.classList.add('desc');
      });
    });

    // course change
    courseSel && courseSel.addEventListener('change', async (ev)=>{
      const v = ev.target.value;
      modulesForTable = [];
      moduleAssignmentsCache.clear();

      // clear rows / reset UI
      rowsEl && rowsEl.querySelectorAll && rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
      if(emptyEl) emptyEl.style.display='none';
      if(askEl) askEl.style.display='';
      if(pager) pager.innerHTML='';
      if(metaTxt) metaTxt.textContent='—';

      if (q) q.disabled = true;
      if (btnCreate) btnCreate.disabled = true;

      if(batchSel){
        batchSel.innerHTML  = '<option value="">Select a batch…</option>';
        batchSel.disabled = true;
      }

      if (!v) return;

      await loadModules(courseSel.value);
      await loadBatches(courseSel.value);

      const hasBatchOptions = batchSel && Array.from(batchSel.options).some(opt => opt.value !== '');
      if (q) q.disabled = !hasBatchOptions;
      if (btnCreate) btnCreate.disabled = !(batchSel && batchSel.value);
    });

    // batch change -> render module table
    batchSel && batchSel.addEventListener('change', ()=>{
      moduleAssignmentsCache.clear();
      if(batchSel.value) renderModuleTable();
      else {
        rowsEl && rowsEl.querySelectorAll && rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());
        if(emptyEl) emptyEl.style.display='none';
        if(askEl) askEl.style.display='';
        if(pager) pager.innerHTML='';
        if(metaTxt) metaTxt.textContent='—';
      }
    });

    // per page
    if(perPageSel){
      perPageSel.addEventListener('change', ()=>{
        perPage = Number(perPageSel.value)||20;
        page = 1;
        moduleAssignmentsCache.clear();
        if(batchSel && batchSel.value) renderModuleTable();
      });
    }

    // search (debounced) — only attach if q exists
    if(q){
      let t;
      q.addEventListener('input', ()=>{
        clearTimeout(t);
        t = setTimeout(()=>{
          moduleAssignmentsCache.clear();
          if(batchSel && batchSel.value) renderModuleTable();
        }, 350);
      });
    }

    // tabs
    if(smTabActive && smTabBin){
      smTabActive.addEventListener('click', (e)=>{ e.preventDefault(); if(scope!=='active') setScope('active'); });
      smTabBin.addEventListener('click', (e)=>{ e.preventDefault(); if(scope!=='bin') setScope('bin'); });
    }

    // filters modal apply
    fltApply && fltApply.addEventListener('click', ()=>{
      statusFilter = fltStatus ? fltStatus.value : '';
      sort = fltSort ? fltSort.value : '-created_at';
      page = 1;
      moduleAssignmentsCache.clear();
      if(batchSel && batchSel.value) renderModuleTable();
      const m = bootstrap.Modal.getInstance(document.getElementById('asFilterModal'));
      if (m) m.hide();
    });

    // global handler for dropdown-item actions (works for rows & nested rows)
    document.addEventListener('click', (e)=>{
      const item = e.target.closest('.dropdown-item[data-act]');
      if(!item) return;
      e.preventDefault();

      const act = item.dataset.act, id = item.dataset.id, uuid = item.dataset.uuid;
      if(act==='view') openView(uuid);
else if(act==='edit') {
  // prefer uuid if available (dropdown items should include data-uuid)
  const key = uuid || id;
  location.href = `/admin/assignments/create?edit=${encodeURIComponent(key)}`;
}
      else if(act==='delete') deleteItem(id);
      else if(act==='purge') purgeItem(id);
      else if(act==='restore') restoreItem(id);

      // hide dropdown if present
      const toggle = item.closest('.dropdown')?.querySelector('.dd-toggle');
      if(toggle) try{ bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); }catch(_){}
    });
  }

  /* ================= rendering modules table ================= */
  function renderModuleTable(){
    if(!batchSel || !batchSel.value){ showAsk(true); return; }
    showAsk(false); showLoader(true);
    if(emptyEl) emptyEl.style.display='none';
    if(pager) pager.innerHTML='';
    if(metaTxt) metaTxt.textContent='—';
    rowsEl && rowsEl.querySelectorAll && rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());

    if(!modulesForTable.length){
      showLoader(false);
      if(emptyEl) emptyEl.style.display='';
      return;
    }

    const frag = document.createDocumentFragment();
    const total = modulesForTable.length;
    const per = perPage;
    const pages = Math.max(1, Math.ceil(total / per));
    const cur = Math.min(page, pages);
    const start = (cur - 1) * per;
    const end = Math.min(start + per, total);
    const slice = modulesForTable.slice(start, end);

    slice.forEach(m=>{
      const modId = m.id;
      const modTitle = m.title || '(untitled module)';
      const modDesc  = m.description || '';

      const tr = document.createElement('tr');
      tr.className = 'module-row';
      tr.dataset.moduleId = modId;
      tr.innerHTML = `
        <td>
          <button type="button" class="btn btn-sm btn-outline-secondary me-2 toggle-mod">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
          </button>
          <span class="fw-semibold">${H.esc(modTitle)}</span>
          ${modDesc ? `<div class="small text-muted text-truncate" style="max-width:520px">${H.esc(modDesc)}</div>` : ''}
        </td>
        <td colspan="3" class="text-muted small">Module under "${H.esc(courseSel?.options[courseSel?.selectedIndex]?.text||'')}"</td>
        <td>-</td>
        <td class="text-end">
          <a class="btn btn-sm btn-primary" style="display:none;" href="/admin/assignments/create?course_id=${encodeURIComponent(courseSel?.value||'')}&course_module_id=${encodeURIComponent(modId)}&batch_id=${encodeURIComponent(batchSel?.value||'')}">
            <i class="fa fa-plus"></i> Add assignment
          </a>
        </td>
      `;

      const trDetails = document.createElement('tr');
      trDetails.className = 'module-assignments';
      trDetails.dataset.moduleId = modId;
      trDetails.style.display = 'none';
      trDetails.innerHTML = `
        <td colspan="6">
          <div class="p-3 border-top border-light-subtle" id="mm_wrap_${modId}">
            <div class="small text-muted">Click the arrow to load assignments for this module & batch.</div>
          </div>
        </td>
      `;

      frag.appendChild(tr);
      frag.appendChild(trDetails);
    });

    rowsEl && rowsEl.appendChild(frag);

    // pager UI
    const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
    let html='';
    html+=li(cur<=1,false,'Previous',cur-1);
    const w=3, s=Math.max(1,cur-w), e=Math.min(pages,cur+w);
    if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
    for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
    if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
    html+=li(cur>=pages,false,'Next',cur+1);
    if(pager) pager.innerHTML = html;
    if(pager) pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click', ()=>{
      const t = Number(a.dataset.page);
      if(!t || t===page) return;
      page = Math.max(1, t);
      renderModuleTable();
      window.scrollTo({top:0,behavior:'smooth'});
    }));

    if(metaTxt) metaTxt.textContent = `Page ${cur} of ${pages} — ${total} module(s)`;

    // wire expand buttons
    rowsEl && rowsEl.querySelectorAll && rowsEl.querySelectorAll('.module-row .toggle-mod').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const tr = btn.closest('.module-row');
        const moduleId = tr.dataset.moduleId;
        const detailsRow = rowsEl.querySelector(`.module-assignments[data-module-id="${moduleId}"]`);
        const icon = btn.querySelector('i');

        if(detailsRow.style.display === 'none'){
          detailsRow.style.display = '';
          icon.classList.remove('fa-chevron-right'); icon.classList.add('fa-chevron-down');
          loadModuleAssignments(moduleId);
        } else {
          detailsRow.style.display = 'none';
          icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-right');
        }
      });
    });

    showLoader(false);
  }

  function showAsk(v){ if(askEl) askEl.style.display = v ? '' : 'none'; }
  function showLoader(v){ if(loaderRow) loaderRow.style.display = v ? '' : 'none'; }

  /* ================= module assignments (nested) ================= */
  async function loadModuleAssignments(moduleId){
    const wrap = document.getElementById(`mm_wrap_${moduleId}`);
    if(!wrap) return;
    if(moduleAssignmentsCache.has(moduleId)){
      renderModuleAssignments(moduleId, moduleAssignmentsCache.get(moduleId));
      return;
    }

    if(moduleFetchControllers.has(moduleId)){
      try{ moduleFetchControllers.get(moduleId).abort(); }catch(_){}
    }
    const ac = new AbortController();
    moduleFetchControllers.set(moduleId, ac);

    wrap.innerHTML = '<div class="small text-muted">Loading assignments…</div>';
    try{
      const usp = new URLSearchParams({ course_id: courseSel?.value, course_module_id: moduleId, batch_id: batchSel?.value, per_page:500, sort });
      if(q && q.value && q.value.trim()) usp.set('search', q.value.trim());
      if(statusFilter) usp.set('status', statusFilter);
      if(!binMode) usp.set('include_deleted','0');

const url = scope==='bin' ? API.binIndex(usp) : API.index(usp);
      const res = await fetch(url, { headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json','Cache-Control':'no-cache'}, signal: ac.signal });
      const j = await res.json();
      if(!res.ok) throw new Error(j?.message||'Load failed');
      const items = j?.data || [];
      moduleAssignmentsCache.set(moduleId, items);
      renderModuleAssignments(moduleId, items);
    }catch(e){
      if(e.name === 'AbortError') return;
      wrap.innerHTML = `<div class="text-danger small">${H.esc(e.message||'Failed to load assignments')}</div>`;
    }finally{
      moduleFetchControllers.delete(moduleId);
    }
  }

  function rowActions(r){
    if(scope==='bin'){
      return `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" title="Actions"><i class="fa fa-ellipsis-vertical"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" data-act="view" data-uuid="${H.esc(r.uuid)}"><i class="fa fa-eye"></i> View</button></li>
            <li><button class="dropdown-item" data-act="restore" data-id="${r.id}"><i class="fa fa-rotate-left"></i> Restore</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item text-danger" data-act="purge" data-id="${r.id}"><i class="fa fa-trash-can"></i> Delete permanently</button></li>
          </ul>
        </div>
      `;
    }

    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" title="Actions"><i class="fa fa-ellipsis-vertical"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" data-act="view" data-uuid="${H.esc(r.uuid)}"><i class="fa fa-eye"></i> View</button></li>
<li>
  <a class="dropdown-item" href="/admin/assignments/create?edit=${encodeURIComponent(r.uuid || r.id)}">
    <i class="fa fa-pen-to-square"></i> Edit
  </a>
</li>
          <li><hr class="dropdown-divider"></li>
          <li><button class="dropdown-item text-danger" data-act="delete" data-id="${r.id}"><i class="fa fa-trash"></i> Delete</button></li>
        </ul>
      </div>
    `;
  }

  function renderModuleAssignments(moduleId, items){
    const wrap = document.getElementById(`mm_wrap_${moduleId}`);
    if(!wrap) return;

    if(!items.length){
      wrap.innerHTML = '<div class="small text-muted">No assignments for this module & batch.</div>';
      return;
    }

    const rows = items.map(r => `
      <tr>
        <td>
          <div class="fw-semibold">${H.esc(r.title||'(untitled)')}</div>
          <div class="small text-muted text-truncate" style="max-width:520px">${H.esc(r.instructions ? (String(r.instructions).replace(/<[^>]+>/g,'').slice(0,220)) : '')}</div>
        </td>
        <td>${H.esc(r.module_title||r.course_module_title||'-')}</td>
        <td>${H.esc(r.batch_title||r.batch_name||'-')}</td>
        <td class="text-center"><span class="badge badge-soft-primary"><i class="fa fa-paperclip"></i> ${Number(r.submission_count||0)}</span></td>
        <td>${H.fmtDateTime(r.created_at)}</td>
        <td class="text-end">${rowActions(r)}</td>
      </tr>
    `).join('');

    wrap.innerHTML = `<div class="table-responsive"><table class="table table-sm align-middle mb-0"><tbody>${rows}</tbody></table></div>`;

    // initialize dropdowns we just injected inside this wrap
    initDropdowns(wrap);
  }

  /* ================= flat list (loadList) ================= */
  async function loadList(){
    if(!batchSel || !batchSel.value){ showAsk(true); return; }
    showAsk(false); showLoader(true);
    if(emptyEl) emptyEl.style.display='none';
    if(pager) pager.innerHTML='';
    if(metaTxt) metaTxt.textContent='—';
    rowsEl && rowsEl.querySelectorAll && rowsEl.querySelectorAll('tr:not(#loaderRow):not(#ask)').forEach(n=>n.remove());

    try{
      const usp = new URLSearchParams({ course_id: courseSel?.value, batch_id: batchSel?.value, per_page: perPage, page, sort });
if(q && q.value && q.value.trim()) usp.set('q', q.value.trim());
      if(!binMode) usp.set('include_deleted','0');

      const url = scope==='bin' ? API.binIndex(usp) : API.index(usp);
      const res = await fetch(url, { headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json','Cache-Control':'no-cache'} });
      const j = await res.json();
      if(!res.ok) throw new Error(j?.message||'Load failed');
      const items = j?.data||[];
      if(items.length===0){ if(emptyEl) emptyEl.style.display=''; return; }

      const frag = document.createDocumentFragment();
      items.forEach(r=> frag.appendChild(rowHTML(r)));
      rowsEl && rowsEl.appendChild(frag);

      // initialize dropdowns injected in these rows
      initDropdowns(rowsEl);

      const meta = j?.meta || j?.pagination || {page:1, per_page:perPage, total:items.length};
      const total = Number(meta.total||items.length),
            per = Number(meta.per_page||perPage),
            cur = Number(meta.page||meta.current_page||1);
      const pages = Math.max(1, Math.ceil(total/per));
      const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
      let html='';
      html+=li(cur<=1,false,'Previous',cur-1);
      const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
      if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
      for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
      if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
      html+=li(cur>=pages,false,'Next',cur+1);
      if(pager) pager.innerHTML=html;
      if(pager) pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click', ()=>{ const t=Number(a.dataset.page); if(!t || t===page) return; page = Math.max(1, t); loadList(); window.scrollTo({top:0,behavior:'smooth'}); }));

      if(metaTxt) metaTxt.textContent = `Page ${cur} of ${pages} — ${total} item(s)`;

    }catch(e){
      console.error(e);
      if(emptyEl) emptyEl.style.display='';
    }finally{
      showLoader(false);
    }
  }

  function rowHTML(r){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <div class="fw-semibold">${H.esc(r.title||'(untitled)')}</div>
        <div class="small text-muted text-truncate" style="max-width:520px">${H.esc(r.instructions ? (String(r.instructions).replace(/<[^>]+>/g,'').slice(0,220)) : '')}</div>
      </td>
      <td>${H.esc(r.module_title || r.course_module_title || '-')}</td>
      <td>${H.esc(r.batch_title || r.batch_name || '-')}</td>
      <td class="text-center"><span class="badge badge-soft-primary"><i class="fa fa-paperclip"></i> ${Number(r.submission_count||0)}</span></td>
      <td>${H.fmtDateTime(r.created_at)}</td>
      <td class="text-end">${rowActions(r)}</td>
    `;
    return tr;
  }

  /* ================= view / preview ================= */
  let lastObjectUrl = null;
  async function openView(assignmentKey){
    if(!vModal){ alert('Viewer not available'); return; }
    try{
      const res = await fetch(API.show(assignmentKey),{ headers:{Authorization:'Bearer '+TOKEN,Accept:'application/json'} });
      const row = await res.json();
      if(!res.ok) throw new Error(row?.message||'Load failed');
      const data = row?.data || row;

      asAttList && (asAttList.innerHTML = '');
      asViewer && (asViewer.innerHTML = '<div class="text-muted small text-center">Select an attachment to preview.</div>');
      asViewTitle && (asViewTitle.textContent = data.title || '—');
      asViewMeta && (asViewMeta.textContent = `${data.module_title||data.course_module_title||''} • ${data.batch_title||data.batch_name||''}`);

      const atts = Array.isArray(data.attachment) ? data.attachment : (Array.isArray(data.attachments) ? data.attachments : []);
      if(atts.length && asAttList){
        asAttList.innerHTML = atts.map(a=>`
          <div class="att" data-id="${H.esc(a.id)}" data-mime="${H.esc(a.mime||'')}" data-ext="${H.esc(a.ext||'')}" data-url="${H.esc(a.url||'')}">
            <div class="icon"><i class="fa ${a.ext?('fa-file-'+a.ext):'fa-file'}"></i></div>
            <div class="flex-grow-1">
              <div class="name">${H.esc(a.name|| (a.ext||'').toUpperCase()+' file')}</div>
              <div class="meta">${H.esc(a.mime||'-')} • ${a.size?formatBytes(a.size):''}</div>
            </div>
            <button class="btn btn-light btn-sm">Preview</button>
          </div>
        `).join('');
        asAttList.querySelectorAll('.att').forEach(div => {
          div.querySelector('button')?.addEventListener('click', ()=> previewAttachment(div.dataset));
          div.addEventListener('dblclick', ()=> previewAttachment(div.dataset));
        });
      } else if(asAttList){
        asAttList.innerHTML = '<div class="small text-muted">No attachments.</div>';
      }
      vModal.show();
    }catch(e){
      console.error(e);
      alert(e.message||'Failed to open');
    }
  }

  async function previewAttachment(meta){
    const { id, mime, ext } = meta || {};
    const url = (meta && meta.url && meta.url !== 'undefined') ? meta.url : API.file(id);

    if(lastObjectUrl){
      try{ URL.revokeObjectURL(lastObjectUrl); }catch(_){}
      lastObjectUrl = null;
    }
    if(asViewer) asViewer.innerHTML = '<div class="text-muted">Loading preview…</div>';

    try{
      const res = await fetch(url, { headers:{ Authorization:'Bearer '+TOKEN, Accept:'*/*' } });
      if(!res.ok) throw new Error('Unable to fetch file');
      const blob = await res.blob();
      const lower = (ext||'').toLowerCase();

      if((mime||'').startsWith('image/') || ['png','jpg','jpeg','webp','svg','gif'].includes(lower)){
        const obj = URL.createObjectURL(blob);
        lastObjectUrl = obj;
        if(asViewer) asViewer.innerHTML = `<img src="${obj}" alt="image" style="max-width:100%;height:auto;">`;
        return;
      }
      if(mime === 'application/pdf' || lower === 'pdf'){
        const obj = URL.createObjectURL(blob);
        lastObjectUrl = obj;
        if(asViewer) asViewer.innerHTML = `<iframe src="${obj}#toolbar=0&navpanes=0&scrollbar=1" style="width:100%;height:70vh;border:0;"></iframe>`;
        return;
      }

      if(asViewer) asViewer.innerHTML = '<div class="text-muted small">Preview not supported for this type.</div>';
    }catch(e){
      if(asViewer) asViewer.innerHTML = `<div class="text-danger small">${H.esc(e.message||'Preview failed')}</div>`;
    }
  }

  asViewModalEl && asViewModalEl.addEventListener('hidden.bs.modal', ()=>{
    if(lastObjectUrl){
      try{ URL.revokeObjectURL(lastObjectUrl); }catch(_){}
      lastObjectUrl = null;
    }
    if(asViewer) asViewer.innerHTML = '<div class="text-muted small text-center">Select an attachment to preview.</div>';
  });

  function formatBytes(n){ if(!n) return '0 B'; if(n<1024) return n+' B'; if(n<1024*1024) return (n/1024).toFixed(1)+' KB'; return (n/(1024*1024)).toFixed(1)+' MB'; }

  /* ================= CRUD helpers ================= */
  async function deleteItem(id){ if(!confirm('Move assignment to bin?')) return; try{ const res = await fetch(API.destroy(id,false),{ method:'DELETE', headers:{ Authorization:'Bearer '+TOKEN, Accept:'application/json' } }); const j = await res.json().catch(()=>({})); if(!res.ok) throw new Error(j.message||'Delete failed'); alert('Moved to Bin'); moduleAssignmentsCache.clear(); if(batchSel && batchSel.value) renderModuleTable(); }catch(e){ alert(e.message||'Delete failed'); } }
  async function purgeItem(id){ if(!confirm('Delete permanently? This cannot be undone.')) return; try{ const res = await fetch(API.destroy(id,true),{ method:'DELETE', headers:{ Authorization:'Bearer '+TOKEN, Accept:'application/json' } }); const j = await res.json().catch(()=>({})); if(!res.ok) throw new Error(j.message||'Purge failed'); alert('Deleted permanently'); moduleAssignmentsCache.clear(); if(batchSel && batchSel.value) renderModuleTable(); }catch(e){ alert(e.message||'Purge failed'); } }
  async function restoreItem(id){ try{ const res = await fetch(API.restore(id),{ method:'POST', headers:{ Authorization:'Bearer '+TOKEN, Accept:'application/json' } }); const j = await res.json().catch(()=>({})); if(!res.ok) throw new Error(j.message||'Restore failed'); alert('Restored'); moduleAssignmentsCache.clear(); if(batchSel && batchSel.value) renderModuleTable(); }catch(e){ alert(e.message||'Restore failed'); } }

  // Expose loadList for 'flat' mode if needed
  window.asLoadList = loadList;

  // initial control toggles
  function initControls(){
    if(perPageSel) perPageSel.value = String(perPage);
    const hasBatchOptions = batchSel && Array.from(batchSel.options).some(opt => opt.value !== '');
    if(q) q.disabled = !hasBatchOptions;
    if(btnCreate) btnCreate.disabled = !(batchSel && batchSel.value);
  }
  initControls();

})();
</script>
@endpush
