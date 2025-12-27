{{-- resources/views/modules/dashboardMenu/createDashboardMenu.blade.php --}}
@extends('pages.users.layout.structure')

@section('title','Create Dashboard Menu')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
  .cm-wrap{max-width:980px;margin:16px auto 44px}
  .cardx{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2)}
  .cardx .cardx-h{padding:14px 16px;border-bottom:1px solid var(--line-strong);display:flex;align-items:center;justify-content:space-between;gap:12px}
  .cardx .cardx-b{padding:16px}
  .cardx .cardx-f{padding:14px 16px;border-top:1px solid var(--line-strong);display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap}

  .form-control,.form-select,textarea{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
  html.theme-dark .form-control,html.theme-dark .form-select,html.theme-dark textarea{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}
  .btn{border-radius:12px;}
  .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
  .btn-primary{background:var(--primary-color);border:none}
  .muted{color:var(--muted-color)}
  .small2{font-size:12.5px}

  .req{color:var(--danger-color)}
  .hint{font-size:12px;color:var(--muted-color);margin-top:6px}

  .err{font-size:12px;color:var(--danger-color);display:none;margin-top:6px}
  .err:not(:empty){display:block}

  /* Pills / badges (for parent picker UI) */
  .pill{border:1px solid var(--line-strong);border-radius:999px;padding:3px 8px;font-size:12px;color:var(--muted-color)}
  .badge-soft{background:var(--t-primary);color:#0f766e;border:1px solid rgba(201,75,80,.26);border-radius:999px;padding:2px 10px;font-size:12px;font-weight:700}
  .pick-parent-btn{white-space:nowrap}
  .divider-soft{height:1px;background:color-mix(in oklab, var(--line-strong) 70%, transparent);}

  /* ✅ "Header menu" badge inside tree */
  .badge-header{
    display:inline-flex;align-items:center;gap:6px;
    border-radius:999px;padding:2px 9px;font-size:11px;font-weight:800;
    border:1px solid rgba(201,75,80,.30);
    background:color-mix(in oklab, var(--t-primary) 80%, transparent);
    color:var(--primary-color);
    line-height:1;
    white-space:nowrap;
  }

  /* Toggle */
  .switch{
    display:flex;align-items:center;gap:10px;
    padding:10px 12px;border:1px solid var(--line-strong);border-radius:12px;background:var(--surface);
  }
  .switch input{width:44px;height:22px;appearance:none;-webkit-appearance:none;background:#cbd5e1;border-radius:999px;position:relative;outline:none;cursor:pointer;transition:.2s}
  .switch input:checked{background:var(--primary-color)}
  .switch input::after{content:"";width:18px;height:18px;background:#fff;border-radius:999px;position:absolute;top:2px;left:2px;transition:.2s;box-shadow:0 2px 6px rgba(0,0,0,.18)}
  .switch input:checked::after{left:24px}
  .switch .lbl{font-weight:600}
  .switch .sub{font-size:12px;color:var(--muted-color)}

  /* Dim loader */
  .dim{position:absolute;inset:0;background:rgba(0,0,0,.06);display:none;align-items:center;justify-content:center;z-index:10;border-radius:16px}
  .dim.show{display:flex}
  .spin{width:20px;height:20px;border:3px solid #0001;border-top-color:var(--accent-color);border-radius:50%;animation:rot 1s linear infinite}
  @keyframes rot{to{transform:rotate(360deg)}}

  /* Save button loader */
  #btnSave{position:relative}
  #btnSave .btn-spin{
    width:16px;height:16px;display:inline-block;border-radius:999px;
    border:3px solid #ffffff55;border-top-color:#fff;
    animation:btnrot .8s linear infinite;
  }
  @keyframes btnrot{to{transform:rotate(360deg)}}
  #btnSave.is-loading{pointer-events:none;opacity:.9}
  #btnSave.is-loading .btn-icon{display:none}
  #btnSave.is-loading .btn-text{content:"Saving…"}
  .btn .spinner-border{width:1rem;height:1rem;border-width:.18rem}

  /* Toasts */
  .toast-container{z-index:2200}

  /* =========================
     ✅ Parent Picker
     ========================= */
  .tree-wrap{position:relative;min-height:140px}
  .tree-loader{
    position:absolute; inset:0; display:none; align-items:center; justify-content:center;
    background: color-mix(in oklab, var(--surface) 86%, transparent);
    z-index:2;
  }
  .tree-loader.show{display:flex}
  .tree-loader .spin{width:22px;height:22px;border-width:3px}

  .tree{--pad:12px; --rad:12px}
  .tree ul{list-style:none;margin:0;padding-left:18px;border-left:1px dashed var(--line-strong)}
  .tree li{margin:4px 0 4px 0;position:relative}
  .tree-node{
    display:flex;align-items:center;gap:10px;
    padding:8px 10px;border:1px solid var(--line-strong);border-radius:var(--rad);
    background:var(--surface);
  }
  .tree-node .toggle{
    width:24px;height:24px;border:1px solid var(--line-strong);border-radius:8px;
    display:inline-grid;place-items:center;cursor:pointer;flex:0 0 auto;
    background:color-mix(in oklab, var(--surface) 92%, var(--ink) 0%);
  }
  .tree-node .toggle i{transition:transform .18s ease}
  .tree-node[data-open="1"] .toggle i{transform:rotate(90deg)}
  .tree-title{font-weight:600;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
  .tree-meta{font-size:12px;color:var(--muted-color)}
  .tree-actions{margin-left:auto;display:flex;gap:8px}
  .tree .children{margin-top:6px;display:none}
  .tree-node[data-open="1"] + .children{display:block}
  .tree-empty{padding:16px;border:1px dashed var(--line-strong);border-radius:12px;color:var(--muted-color);text-align:center}
  .modal-tools .input-group{height:36px}
  .modal-tools .form-control{height:36px}
</style>
@endpush

@section('content')
<div class="cm-wrap">

  <div class="cardx position-relative">
    <div id="busy" class="dim"><div class="spin"></div></div>

    <div class="cardx-h">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-grip-vertical muted"></i>
        <div>
          <div class="fw-semibold" id="pageTitle">Create Dashboard Menu</div>
          <div class="small2 muted" id="hint">Add a new menu item for the dashboard sidebar.</div>
        </div>
      </div>
    </div>

    <div class="cardx-b">
      <form id="menuForm" autocomplete="off">

        <div class="row g-3">
          {{-- Parent --}}
          <div class="col-12">
            <label class="form-label">Parent Menu <span class="pill ms-1">optional</span></label>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span id="parentBadge" class="badge-soft">Top level (No parent)</span>
              <button class="btn btn-light pick-parent-btn" type="button" id="btnPickParent">
                <i class="fa-solid fa-diagram-project me-1"></i>Choose parent
              </button>
              <button class="btn btn-outline-danger btn-sm" type="button" id="btnClearParent">
                <i class="fa-solid fa-xmark me-1"></i>Clear
              </button>
            </div>
            <input type="hidden" id="parent_id" value="">
            <div class="err" data-for="parent_id"></div>
            <div class="hint">Only menus marked as <b>Is dropdown head</b> can be chosen as a parent.</div>
          </div>

          <div class="divider-soft my-2"></div>

          {{-- Name --}}
          <div class="col-12">
            <label class="form-label">Name <span class="req">*</span></label>
            <input id="name" class="form-control" maxlength="150" placeholder="e.g., Students, Notice Board, Courses">
            <div class="err" data-for="name"></div>
            <div class="hint">This is the label shown in the dashboard menu.</div>
          </div>

          {{-- Icon class --}}
          <div class="col-md-6">
            <label class="form-label">Icon class <span class="pill ms-1">optional</span></label>
            <input id="icon_class" class="form-control" maxlength="120" placeholder='e.g., fa-solid fa-bullhorn'>
            <div class="err" data-for="icon_class"></div>
            <div class="hint">FontAwesome class name (optional).</div>
          </div>

          {{-- Status --}}
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select id="status" class="form-select">
              <option value="Active" selected>Active</option>
              <option value="Draft">Draft</option>
              <option value="Published">Published</option>
              <option value="Archived">Archived</option>
            </select>
            <div class="err" data-for="status"></div>
          </div>

          {{-- Description --}}
          <div class="col-12">
            <label class="form-label">Description <span class="pill ms-1">optional</span></label>
            <textarea id="description" class="form-control" rows="4" placeholder="Short description (optional)"></textarea>
            <div class="err" data-for="description"></div>
          </div>

          {{-- Is dropdown head toggle --}}
          <div class="col-12">
            <div class="switch">
              <input id="is_dropdown_head" type="checkbox" value="1">
              <div>
                <div class="lbl">Is dropdown head</div>
                <div class="sub">When ON, this menu becomes a dropdown header and the Href field is hidden.</div>
              </div>
            </div>
            <div class="err" data-for="is_dropdown_head"></div>
          </div>

          {{-- Href (default visible, hides when "Is dropdown head" is ON) --}}
          <div class="col-12" id="href_wrap">
            <label class="form-label">Href (route or URL)</label>
            <input id="href" class="form-control" maxlength="255" placeholder="e.g. /modules/intro or https://example.com/path">
            <div class="err" data-for="href"></div>
            <div class="hint">If you provide a relative path, it should start with <code>/</code>.</div>
          </div>
        </div>

      </form>
    </div>

    <div class="cardx-f">
      <a href="/dashboard-menu/manage" class="btn btn-light">Cancel</a>

      <button id="btnSave" type="button" class="btn btn-primary">
        <span class="btn-icon"><i class="fa fa-save me-1"></i></span>
        <span class="btn-spin d-none ms-2" aria-hidden="true"></span>
        <span class="btn-text" id="btnSaveText">Save</span>
      </button>
    </div>
  </div>
</div>

{{-- Parent Picker Modal --}}
<div class="modal fade" id="parentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="fa-solid fa-diagram-project me-2"></i>Pick Parent Menu</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2 modal-tools">
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light btn-sm" type="button" id="btnPickTop">
              <span class="label"><i class="fa-regular fa-circle-check"></i> Select “Top level”</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
            <button class="btn btn-light btn-sm" type="button" id="btnReloadTree">
              <span class="label"><i class="fa-solid fa-rotate"></i> Reload</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
          </div>

          <div class="input-group" style="max-width: 340px;">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" class="form-control" id="treeSearch" placeholder="Search by name…">
          </div>
        </div>

        <div class="tree-wrap">
          <div class="tree-loader" id="treeLoader">
            <div class="spin me-2"></div><span class="text-muted">Loading tree…</span>
          </div>
          <div id="treeEmpty" class="tree-empty" style="display:none">No menus found.</div>
          <div id="treeRoot" class="tree"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="toastSuccess" class="toast text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div id="toastSuccessText" class="toast-body">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast text-bg-danger border-0 mt-2" role="alert">
    <div class="d-flex">
      <div id="toastErrorText" class="toast-body">Something went wrong</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function(){
  const busy = document.getElementById('busy');

  const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastError   = new bootstrap.Toast(document.getElementById('toastError'));
  const ok  = (m)=>{ document.getElementById('toastSuccessText').textContent = m||'Done'; toastSuccess.show(); };
  const err = (m)=>{ document.getElementById('toastErrorText').textContent = m||'Something went wrong'; toastError.show(); };

  const byId = (id)=>document.getElementById(id);

  const headers = (() => {
    const t = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
    if (!t) {
      Swal.fire('Login needed','Your session expired. Please login again.','warning')
        .then(()=> location.href='/');
      throw new Error('No token');
    }
    return { 'Authorization':'Bearer '+t, 'Accept':'application/json', 'Content-Type':'application/json' };
  })();

  const API_BASE = '/api/dashboard-menus';
  const API_TREE = '/api/dashboard-menus/tree?only_active=0';

  const els = {
    parentId: byId('parent_id'),
    parentBadge: byId('parentBadge'),
    name: byId('name'),
    icon: byId('icon_class'),
    status: byId('status'),
    desc: byId('description'),
    ddToggle: byId('is_dropdown_head'),
    hrefWrap: byId('href_wrap'),
    href: byId('href'),

    btnSave: byId('btnSave'),
    btnSaveText: byId('btnSaveText'),

    pageTitle: byId('pageTitle'),
    hint: byId('hint'),

    btnPickParent: byId('btnPickParent'),
    btnClearParent: byId('btnClearParent'),
    parentModalEl: byId('parentModal'),
    parentModal: new bootstrap.Modal(byId('parentModal')),
    treeRoot: byId('treeRoot'),
    treeSearch: byId('treeSearch'),
    treeLoader: byId('treeLoader'),
    treeEmpty: byId('treeEmpty'),
    btnPickTop: byId('btnPickTop'),
    btnReloadTree: byId('btnReloadTree'),
  };

  const usp = new URLSearchParams(window.location.search || '');
  const EDIT_KEY_RAW = (usp.get('edit') || '').trim();
  const IS_EDIT = EDIT_KEY_RAW !== '';
  const EDIT_KEY = IS_EDIT ? EDIT_KEY_RAW : null;

  let initialData = null;

  function showBusy(on){ busy.classList.toggle('show', !!on); }

  function showError(field, msg){
    const el = document.querySelector(`.err[data-for="${field}"]`);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
  }
  function clearErrors(){
    document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; });
  }

  async function fetchJSON(url, opts={}){
    const r = await fetch(url, {
      ...opts,
      headers: { ...headers, ...(opts.headers||{}) }
    });
    let j={}; try{ j=await r.json(); }catch{ j={}; }

    if (!r.ok) {
      if (r.status === 401 || r.status === 403) {
        Swal.fire('Unauthorized', j?.message || 'Please login again.', 'warning')
          .then(()=> location.href='/');
      }
      const firstErr =
        j?.message ||
        (j?.errors ? Object.values(j.errors).flat()[0] : null) ||
        ('HTTP '+r.status);
      throw new Error(firstErr);
    }
    return j;
  }

  function setBtnBusy(btn, on, newLabel){
    if (!btn) return;
    const label = btn.querySelector('.label');
    const spin  = btn.querySelector('.spinner-border');
    if (label && typeof newLabel === 'string') label.innerHTML = newLabel;
    btn.disabled = !!on;
    if (spin) spin.classList.toggle('d-none', !on);
  }
  function setSaveBusy(on, text){
    const spin = els.btnSave.querySelector('.btn-spin');
    els.btnSave.classList.toggle('is-loading', !!on);
    els.btnSave.disabled = !!on;
    if (spin) spin.classList.toggle('d-none', !on);
    if (els.btnSaveText) els.btnSaveText.textContent = on ? (text || 'Saving…') : (IS_EDIT ? 'Save Changes' : 'Save');
  }

  /* ✅ INVERTED:
     Default: show href
     If is_dropdown_head ON: hide href
  */
  function syncHrefVisibility(){
    const isHead = !!els.ddToggle.checked;
    els.hrefWrap.style.display = isHead ? 'none' : '';
  }
  els.ddToggle.addEventListener('change', syncHrefVisibility);
  syncHrefVisibility();

  async function getMenuNameById(id){
    if(!id) return null;
    try{
      const pj = await fetchJSON(`${API_BASE}/${encodeURIComponent(id)}`);
      const pm = pj?.data ?? pj?.menu ?? pj?.module ?? pj;
      return pm?.name || pm?.title || null;
    }catch(e){
      return null;
    }
  }

  function setParent(id, label){
    els.parentId.value = id || '';
    els.parentBadge.textContent = id ? `#${id}: ${label}` : 'Top level (No parent)';
  }
  els.btnClearParent.addEventListener('click', ()=> setParent('', 'Top level (No parent)'));

  function forceBackdropCleanup(){
    setTimeout(()=>{
      if (document.querySelector('.modal.show')) return;
      document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
      document.body.style.removeProperty('overflow');
    }, 220);
  }

  function closeParentPicker(){
    try { els.parentModal.hide(); } catch {}
    els.treeLoader.classList.remove('show');
    forceBackdropCleanup();
  }

  els.parentModalEl.addEventListener('hidden.bs.modal', forceBackdropCleanup);

  els.btnPickTop.addEventListener('click', ()=>{
    setBtnBusy(els.btnPickTop, true, '<i class="fa-regular fa-circle-check"></i> Select “Top level”');
    setParent('', 'Top level (No parent)');
    setTimeout(()=>{ setBtnBusy(els.btnPickTop, false); closeParentPicker(); }, 150);
  });

  function getParentKey(it){
    let pid = it.parent_id ?? it.parentId ?? it.parent_menu_id ?? it.parent_menuId ?? null;
    if (pid && typeof pid === 'object') pid = pid.id ?? pid.value ?? null;
    if (pid === '' || pid === 0 || pid === '0') return null;
    const s = (pid == null) ? null : String(pid).trim();
    return (s && s !== '0') ? s : null;
  }

  function buildTreeFromFlat(items){
    const list = Array.isArray(items) ? items : [];
    const map = new Map();
    list.forEach(it=>{
      const idKey = (it?.id == null) ? null : String(it.id).trim();
      if (!idKey) return;
      map.set(idKey, { ...it, children: [] });
    });
    const roots = [];
    map.forEach((node)=>{
      const pidKey = getParentKey(node);
      if (pidKey && map.has(pidKey)) map.get(pidKey).children.push(node);
      else roots.push(node);
    });
    function label(n){ return String(n.name || n.title || '').toLowerCase(); }
    function sortRec(arr){
      arr.sort((a,b)=> label(a).localeCompare(label(b)));
      arr.forEach(x => sortRec(x.children || []));
    }
    sortRec(roots);
    return roots;
  }

  /* =========================
     ✅ Parent eligibility
     - only menus with is_dropdown_head = true can be selected as parent
     - show "Header menu" badge on those
     - hide "Use as parent" button for others
     ========================= */
  function isHeaderMenu(n){
    // backend may send 1/0, true/false, "1"/"0"
    const v = n?.is_dropdown_head;
    return v === true || v === 1 || v === '1' || String(v).toLowerCase() === 'true';
  }

  function renderTree(nodes){
    els.treeRoot.innerHTML = '';
    if (!nodes || !nodes.length){
      els.treeEmpty.style.display = 'block';
      return;
    }
    els.treeEmpty.style.display = 'none';

    const ul = document.createElement('ul');
    ul.className = 'm-0 p-0';

    function makeNode(n, depth=0){
      const li = document.createElement('li');

      const node = document.createElement('div');
      node.className = 'tree-node';
      node.dataset.open = (depth<=1 ? '1' : '0');

      const toggle = document.createElement('div');
      toggle.className = 'toggle';
      toggle.innerHTML = '<i class="fa-solid fa-chevron-right tiny"></i>';
      if (!n.children || !n.children.length) toggle.style.visibility = 'hidden';

      const title = document.createElement('div');
      title.className = 'tree-title';
      title.textContent = n.name || n.title || '-';

      // ✅ header menu badge (only if is_dropdown_head true)
      const headerOk = isHeaderMenu(n);
      if (headerOk){
        const badge = document.createElement('span');
        badge.className = 'badge-header';
        badge.innerHTML = '<i class="fa-solid fa-layer-group"></i> Header menu';
        title.appendChild(badge);
      }

      const meta = document.createElement('div');
      meta.className = 'tree-meta';
      const statusText = n.status ? (' • ' + n.status) : '';
      const hrefText   = n.href ? (' • href: ' + n.href) : '';
      const iconText   = n.icon_class ? (' • icon') : '';
      meta.textContent = `#${n.id || '-'}${statusText}${hrefText}${iconText}`;

      const actions = document.createElement('div');
      actions.className = 'tree-actions';

      // ✅ only for dropdown heads: show pick button
      if (headerOk){
        const pickBtn = document.createElement('button');
        pickBtn.type = 'button';
        pickBtn.className = 'btn btn-sm btn-outline-primary';
        pickBtn.innerHTML =
          '<span class="label"><i class="fa-regular fa-circle-check me-1"></i>Use as parent</span>' +
          '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>';

        pickBtn.addEventListener('click', ()=>{
          setBtnBusy(pickBtn, true);
          setParent(n.id, n.name || n.title || '-');
          setTimeout(()=>{ setBtnBusy(pickBtn, false); closeParentPicker(); }, 120);
        });

        actions.appendChild(pickBtn);
      } else {
        // per requirement: button display none for non-header menus
        // (do nothing)
      }

      node.appendChild(toggle);
      node.appendChild(title);
      node.appendChild(meta);
      node.appendChild(actions);

      li.appendChild(node);

      const childrenWrap = document.createElement('div');
      childrenWrap.className = 'children';
      if (n.children && n.children.length){
        const inner = document.createElement('ul');
        n.children.forEach(c => inner.appendChild(makeNode(c, depth+1)));
        childrenWrap.appendChild(inner);
      } else {
        const empty = document.createElement('div');
        empty.className = 'tiny text-muted ps-2';
        empty.textContent = 'No children';
        childrenWrap.appendChild(empty);
      }
      li.appendChild(childrenWrap);

      toggle.addEventListener('click', ()=>{
        const open = node.dataset.open === '1';
        node.dataset.open = open ? '0' : '1';
      });

      return li;
    }

    nodes.forEach(n => ul.appendChild(makeNode(n, 0)));
    els.treeRoot.appendChild(ul);

    els.treeSearch.value = '';
    els.treeSearch.oninput = function(){
      const q = this.value.trim().toLowerCase();
      els.treeRoot.querySelectorAll('.tree-node').forEach(nd=>{
        const title = (nd.querySelector('.tree-title')?.textContent || '').toLowerCase();
        const meta  = (nd.querySelector('.tree-meta')?.textContent || '').toLowerCase();
        const match = !q || title.includes(q) || meta.includes(q);
        nd.parentElement.style.display = match ? '' : 'none';
      });
      if (q){
        els.treeRoot.querySelectorAll('.tree-node').forEach(nd => nd.dataset.open = '1');
      }
    };
  }

  async function loadTree(){
    els.treeRoot.innerHTML = '';
    els.treeEmpty.style.display='none';
    els.treeLoader.classList.add('show');
    setBtnBusy(els.btnReloadTree, true);
    try{
      let nodes = [];
      try{
        const j = await fetchJSON(API_TREE);
        nodes = Array.isArray(j.data) ? j.data : (Array.isArray(j) ? j : []);
      }catch(_e){
        nodes = [];
      }

      const looksNested = nodes.some(n => Array.isArray(n?.children) && n.children.length);
      if (!nodes.length || !looksNested){
        const j2 = await fetchJSON(`${API_BASE}?per_page=500`);
        const items = Array.isArray(j2.data) ? j2.data : (Array.isArray(j2?.data?.data) ? j2.data.data : []);
        nodes = buildTreeFromFlat(items || []);
      }

      renderTree(nodes);

    }catch(e){
      console.error(e);
      els.treeEmpty.style.display='block';
      els.treeRoot.innerHTML='';
    }finally{
      els.treeLoader.classList.remove('show');
      setBtnBusy(els.btnReloadTree, false);
    }
  }

  els.btnPickParent.addEventListener('click', ()=>{
    loadTree();
    els.parentModal.show();
  });
  els.btnReloadTree.addEventListener('click', loadTree);

  async function loadForEdit(){
    if (!IS_EDIT) return;

    els.pageTitle.textContent = 'Edit Dashboard Menu';
    els.hint.textContent = 'Editing selected menu from Manage page.';
    if (els.btnSaveText) els.btnSaveText.textContent = 'Save Changes';

    showBusy(true);
    clearErrors();

    try{
      const j = await fetchJSON(`${API_BASE}/${encodeURIComponent(EDIT_KEY)}`);
      const m = j?.data ?? j?.menu ?? j?.module ?? j;
      initialData = m || null;
      if (!m || typeof m !== 'object') throw new Error('No data found');

      els.name.value = m.name || '';
      els.icon.value = m.icon_class || '';
      els.status.value = m.status || 'Active';
      els.desc.value = m.description || '';
      els.ddToggle.checked = !!m.is_dropdown_head;
      els.href.value = m.href || '';
      syncHrefVisibility();

      let parentLabel =
        m.parent_name ||
        (m.parent && (m.parent.name || m.parent.title)) ||
        null;

      if(!parentLabel && m.parent_id){
        parentLabel = await getMenuNameById(m.parent_id);
      }

      setParent(m.parent_id || '', parentLabel || ('#' + m.parent_id));

    }catch(e){
      console.error(e);
      err(e.message || 'Failed to load menu');
    }finally{
      showBusy(false);
    }
  }

  const createOrUpdate = async function(){
    if (window.__dashMenuCreateSaving) return;
    window.__dashMenuCreateSaving = true;

    try {
      clearErrors();

      const name = (els.name.value || '').trim();
      if (!name){
        showError('name','Name is required');
        els.name.focus();
        return;
      }

      const isHead = !!els.ddToggle.checked;

      const payload = {
        parent_id: els.parentId.value ? parseInt(els.parentId.value, 10) : null,
        name,
        description: (els.desc.value || '').trim() || null,
        status: els.status.value || 'Active',
        icon_class: (els.icon.value || '').trim() || null,
        is_dropdown_head: isHead ? 1 : 0,
        href: isHead ? null : ((els.href.value || '').trim() || null)
      };

      const url = IS_EDIT
        ? (`${API_BASE}/${encodeURIComponent(EDIT_KEY)}`)
        : API_BASE;

      const method = IS_EDIT ? 'PUT' : 'POST';

      setSaveBusy(true, 'Saving…');
      showBusy(true);

      const r = await fetch(url, {
        method,
        headers,
        body: JSON.stringify(payload)
      });

      const ct = (r.headers.get('content-type')||'').toLowerCase();
      const json = ct.includes('application/json')
        ? await r.json().catch(()=>({}))
        : { message: await r.text().catch(()=> '') };

      if (r.ok){
        ok(json.message || (IS_EDIT ? 'Dashboard menu updated.' : 'Dashboard menu created.'));
        setTimeout(()=>{ location.href = '/dashboard-menu/manage'; }, 600);

      } else if (r.status === 422){
        const errors = json.errors || {};
        Object.entries(errors).forEach(([k,v])=> showError(k, Array.isArray(v) ? v[0] : String(v)));
        err(json.message || 'Please fix the highlighted fields');

      } else if (r.status === 403){
        err('Forbidden');

      } else {
        console.error('Server error', json);
        err(`Server error (${r.status})`);
      }

    } catch (ex) {
      console.error(ex);
      err(ex.message || 'Network error');
    } finally {
      showBusy(false);
      setSaveBusy(false);
      window.__dashMenuCreateSaving = false;
    }
  };

  els.btnSave.addEventListener('click', (e)=>{
    e.preventDefault();
    e.stopPropagation();
    createOrUpdate();
  });

  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter' && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')){
      e.preventDefault();
      els.btnSave.focus();
      return false;
    }
  });

  (function init(){
    setParent('', 'Top level (No parent)');
    syncHrefVisibility();
    loadForEdit();
  })();

})();
</script>
@endpush
