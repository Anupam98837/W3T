
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
/* ===== Shell ===== */
.fc-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}

/* Sorting */
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Row state cues */
tr.state-draft td{background:color-mix(in oklab, var(--muted-color) 4%, transparent)}
tr.state-archived td{background:color-mix(in oklab, var(--muted-color) 7%, transparent)}

/* Status badges */
.badge-soft{
  background:color-mix(in oklab, var(--muted-color) 12%, transparent);
  color:var(--ink);
  border-radius:999px;
  padding:.15rem .55rem;
  font-size:11px
}
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 20%, transparent);
  color:#166534
}
.badge-soft-danger{
  background:color-mix(in oklab, var(--danger-color) 20%, transparent);
  color:#7f1d1d
}
.badge-soft-info{
  background:color-mix(in oklab, var(--primary-color) 18%, transparent);
  color:#1d4ed8
}

/* Dropdowns inside table (with portal) */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.table-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:220px;
  z-index:5000
}
.dropdown-menu.dd-portal{
  position:fixed!important;left:0;top:0;transform:none!important;z-index:5000;
  border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);
  min-width:220px;background:var(--surface)
}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color)!important}

/* Buttons */
.icon-btn{
  display:inline-flex;align-items:center;justify-content:center;
  height:34px;min-width:34px;padding:0 10px;
  border:1px solid var(--line-strong);background:var(--surface);border-radius:10px
}
.icon-btn:hover{box-shadow:var(--shadow-1)}

/* Empty & loader */
.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

/* Dark tweaks */
html.theme-dark .modal-content,
html.theme-dark .table-wrap.card{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}

/* Ensure wrappers don't clip dropdowns */
.table-responsive,
.table-wrap,
.card,
.fc-wrap {
  transform: none !important;
}

/* Featured toggle column */
.feature-toggle-cell{white-space:nowrap}
.feature-toggle-label{font-size:11px;color:var(--muted-color);display:block}

/* ===== Featured Media modal — same styling as Courses page ===== */
.media-head{
  display:flex;align-items:flex-start;justify-content:space-between;
  gap:12px;margin-bottom:8px
}
.media-head .meta .title{
  font-weight:700;color:var(--ink);
  font-family:var(--font-head, inherit);line-height:1.2
}
.media-head .meta .sub{
  color:var(--muted-color);font-size:13px
}
.modal .section-label{font-weight:600;color:var(--ink);margin-top:6px}

/* Dropzone */
.dropzone{
  border:1.5px dashed var(--line-strong);
  border-radius:14px;
  padding:18px;
  text-align:center;
  background:var(--surface-2, #fff);
  transition:background .15s ease,border-color .15s ease,box-shadow .15s ease;
}
.dropzone.drag{
  background:color-mix(in oklab, var(--accent-color) 10%, transparent);
  border-color:var(--accent-color);
  box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 18%, transparent)
}
.dropzone .hint{color:var(--muted-color);font-size:13px}
/* Media list → card grid */
.media-list{margin-top:8px}

/* Highlight when "Choose from Library" is clicked */
.media-list.highlight{
  box-shadow:0 0 0 1px #bfdbfe;
  border-radius:14px;
  padding:4px;
  animation:mediaLibFlash .9s ease-out 1;
}
@keyframes mediaLibFlash{
  0%   { box-shadow:0 0 0 0 rgba(59,130,246,0.7); }
  100% { box-shadow:0 0 0 1px rgba(59,130,246,0.4); }
}

.media-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(150px,1fr));
  gap:12px;
}

/* Card */
.media-card{
  border-radius:12px;
  border:1px solid var(--line-strong);
  background:var(--surface-2,#fff);
  padding:8px;
  display:flex;
  flex-direction:column;
  gap:6px;
  cursor:grab;
  transition:box-shadow .15s ease,transform .15s ease,border-color .15s ease;
}
.media-card:active{cursor:grabbing}
.media-card.dragging{
  opacity:.8;
  box-shadow:0 0 0 1px #60a5fa;
}
.media-card:hover{
  box-shadow:0 10px 18px rgba(15,23,42,0.08);
  transform:translateY(-1px);
  border-color:#60a5fa;
}

/* Thumbnail area */
.media-card .card-thumb{
  position:relative;
  width:100%;
  padding-top:62%;
  border-radius:10px;
  overflow:hidden;
  background:#f3f4f6;
}
.media-card .card-thumb a{
  position:absolute;
  inset:0;
  display:block;
}
.media-card .card-thumb img{
  position:absolute;
  inset:0;
  width:100%;
  height:100%;
  object-fit:cover;
}
.media-card .card-thumb .icon-center{
  position:absolute;
  inset:0;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:22px;
  opacity:.75;
}

/* Text/Meta */
.media-card .card-body{
  display:flex;
  flex-direction:column;
  gap:2px;
}
.media-card .card-body .name{
  font-size:.82rem;
  font-weight:500;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.media-card .card-body .meta{
  font-size:.75rem;
  color:var(--muted-color);
}

/* Actions */
.media-card .card-actions{
  display:flex;
  justify-content:flex-end;
  align-items:center;
  gap:4px;
  margin-top:4px;
}
.media-card .btn-icon{
  border:none;
  background:transparent;
  padding:.25rem .4rem;
  border-radius:999px;
  color:#6b7280;
}
.media-card .btn-icon:hover{
  background:#fee2e2;
  color:#b91c1c;
}

/* Dark tweaks for media */
html.theme-dark .dropzone{background:#0b1020;border-color:var(--line-strong)}
html.theme-dark .media-item{background:#0b1020;border-color:var(--line-strong)}

</style>
@endpush

@section('content')
<div class="fc-wrap">

  {{-- ===== Global toolbar ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-xxl d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Manage Featured Courses</label>
      </div>
    </div>
  </div>

  {{-- ===== Tabs ===== --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-featured" role="tab" aria-selected="true">
        <i class="fa-solid fa-star me-2" aria-hidden="true"></i>
        Featured Courses
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-nonfeatured" role="tab" aria-selected="false">
        <i class="fa-regular fa-star me-2" aria-hidden="true"></i>
        Non Featured Courses
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- FEATURED --}}
    <div class="tab-pane fade show active" id="tab-featured" role="tabpanel">

      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;">
              <option>10</option><option>20</option><option selected>30</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:320px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search by title/slug/category…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="btnReset" class="btn btn-primary">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end small text-muted">
          Sorting: <span id="sortHint" class="ms-1">By featured rank</span>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:40px;"></th>
                  <th class="sortable" data-col="title">COURSE <span class="caret"></span></th>
                  <th>CATEGORY</th>
                  <th style="width:16%;">TYPE / PRICE</th>
                  <th class="sortable" data-col="featured_rank" style="width:90px;">RANK <span class="caret"></span></th>
                  <th style="width:120px;">STATUS</th>
                  <th style="width:140px;" class="text-center">FEATURED</th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-featured">
                <tr id="loaderRow-featured" style="display:none;">
                  <td colspan="8" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-featured" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-star mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No featured courses found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-featured">—</div>
            <nav style="position:relative; z-index:1;">
              <ul id="pager-featured" class="pagination mb-0"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    {{-- NON FEATURED --}}
    <div class="tab-pane fade" id="tab-nonfeatured" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:40px;"></th>
                  <th class="sortable" data-col="title">COURSE <span class="caret"></span></th>
                  <th>CATEGORY</th>
                  <th style="width:16%;">TYPE / PRICE</th>
<th style="width:120px;">STATUS</th>
                  <th style="width:140px;" class="text-center">FEATURED</th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-nonfeatured">
                <tr id="loaderRow-nonfeatured" style="display:none;">
                  <td colspan="7" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-nonfeatured" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-circle-dot mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No non-featured courses.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-nonfeatured">—</div>
            <nav style="position:relative; z-index:1;">
              <ul id="pager-nonfeatured" class="pagination mb-0"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.tab-content -->
</div>

{{-- ===== Featured Media Modal (same as Courses page, + Choose from Library) ===== --}}
<div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-images me-2"></i>Course Featured Media
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="media-head">
          <div class="meta">
            <div class="title" id="m_title">—</div>
            <div class="sub" id="m_sub">—</div>
          </div>
          <div class="small text-muted">
            Drag & drop to reorder • Click trash to delete
          </div>
        </div>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label section-label">Upload files</label>
            <div id="dropzone" class="dropzone">
              <div class="mb-2">
                <i class="fa-regular fa-circle-up" style="font-size:28px; opacity:.8"></i>
              </div>
              <div class="fw-semibold">Drag & drop your media here</div>
              <div class="hint mt-1">Images, videos, audio or PDFs. Or</div>
              <div class="mt-2 d-flex flex-wrap gap-2 justify-content-center">
                <label for="mediaFiles" class="btn btn-light">
                  <i class="fa fa-file-arrow-up me-1"></i>Choose Files
                </label>
                <input id="mediaFiles" type="file" class="d-none" multiple
                       accept="image/*,video/*,audio/*,application/pdf">
                <button id="btnAddUrl" class="btn btn-light" type="button">
                  <i class="fa fa-link me-1"></i>Add via URL
                </button>
                <button id="btnChooseLib" class="btn btn-light" type="button" style="display:none">
                  <i class="fa fa-images me-1"></i>Choose from Library
                </button>
              </div>
            </div>
          </div>

          <div class="col-12" id="urlRow" style="display:none;">
            <label class="form-label section-label">Add via URL</label>
            <div class="row g-2 align-items-center">
              <div class="col">
                <input id="urlInput" type="url" class="form-control"
                       placeholder="https://example.com/image.jpg">
              </div>
              <div class="col-auto">
                <button id="btnSaveUrl" class="btn btn-primary" type="button">
                  <i class="fa fa-plus me-1"></i>Add
                </button>
              </div>
              <div class="col-12 small text-muted mt-1">
                Paste a direct link to an image/video/audio/PDF.
              </div>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label section-label">Current featured media</label>
            <div class="media-list" id="mediaList"></div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div class="me-auto small text-muted" id="mediaCount">—</div>
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
  <div id="okToast" class="toast text-bg-success border-0">
    <div class="d-flex">
      <div id="okMsg" class="toast-body">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="errToast" class="toast text-bg-danger border-0 mt-2">
    <div class="d-flex">
      <div id="errMsg" class="toast-body">Something went wrong</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ===== Dropdown portal logic (same as privileges) ===== */
(function(){
  let activePortal=null;
  const place=(menu, btnRect)=>{
    const vw=Math.max(document.documentElement.clientWidth, window.innerWidth||0);
    menu.classList.add('dd-portal');
    menu.style.display='block';
    menu.style.visibility='hidden';
    document.body.appendChild(menu);

    const mw=menu.offsetWidth, mh=menu.offsetHeight;
    let left = (vw - btnRect.right < mw && btnRect.right - mw > 8)
      ? (btnRect.right - mw)
      : btnRect.left;
    let top  = btnRect.bottom + 4;
    const vh=Math.max(document.documentElement.clientHeight, window.innerHeight||0);
    if(top + mh > vh - 8) top = Math.max(8, vh - mh - 8);

    menu.style.left = left + 'px';
    menu.style.top  = top + 'px';
    menu.style.visibility='visible';
  };

  document.addEventListener('show.bs.dropdown', (ev)=>{
    const dd=ev.target,
          btn=dd.querySelector('.dd-toggle,[data-bs-toggle="dropdown"]'),
          menu=dd.querySelector('.dropdown-menu');
    if(!btn || !menu) return;
    if(activePortal?.menu?.isConnected){
      activePortal.menu.classList.remove('dd-portal');
      activePortal.parent.appendChild(activePortal.menu);
      activePortal=null;
    }
    const rect=btn.getBoundingClientRect();
    menu.__parent=menu.parentElement;
    place(menu, rect);
    activePortal={menu, parent:menu.__parent};

    const close=()=>{ try{ bootstrap.Dropdown.getOrCreateInstance(btn).hide(); }catch{} };
    menu.__ls=[ ['resize',close,false], ['scroll',close,true] ];
    window.addEventListener('resize', close);
    document.addEventListener('scroll', close, true);
  });

  document.addEventListener('hidden.bs.dropdown', (ev)=>{
    const dd=ev.target;
    const menu=dd.querySelector('.dropdown-menu.dd-portal') || activePortal?.menu;
    if(!menu) return;
    if(menu.__ls){
      document.removeEventListener('scroll', menu.__ls[1][1], true);
      window.removeEventListener('resize', menu.__ls[0][1]);
      menu.__ls=null;
    }
    if(menu.__parent){
      menu.classList.remove('dd-portal');
      menu.style.cssText='';
      menu.__parent.appendChild(menu);
      activePortal=null;
    }
  });
})();
document.addEventListener('click',(e)=>{
  const btn=e.target.closest('.dd-toggle');
  if(!btn) return;
  e.preventDefault();
  e.stopPropagation();
  bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'}).toggle();
});

/* ===== Main logic ===== */
(function(){
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  // 🔹 Same role/basePanel logic as Courses page
  const ROLE  = (localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase();
  const basePanel = (ROLE.includes('super') ? '/super_admin' : '/admin');

  const okToast=new bootstrap.Toast(document.getElementById('okToast'));
  const errToast=new bootstrap.Toast(document.getElementById('errToast'));
  const ok=(m)=>{document.getElementById('okMsg').textContent=m||'Done'; okToast.show();};
  const err=(m)=>{document.getElementById('errMsg').textContent=m||'Something went wrong'; errToast.show();};

  const perPageSel = document.getElementById('per_page');
  const q          = document.getElementById('q');
  const sortHint   = document.getElementById('sortHint');
  const btnReset   = document.getElementById('btnReset');

  const tabs = {
    featured:    {rows:'#rows-featured',    loader:'#loaderRow-featured',    empty:'#empty-featured',    pager:'#pager-featured',    meta:'#metaTxt-featured'},
    nonfeatured: {rows:'#rows-nonfeatured', loader:'#loaderRow-nonfeatured', empty:'#empty-nonfeatured', pager:'#pager-nonfeatured', meta:'#metaTxt-nonfeatured'},
  };
  const state = {
    featured:    {page:1},
    nonfeatured: {page:1},
  };

  // sort default: by featured_rank asc in featured, created_at desc in non
  let sortFeatured    = 'featured_rank';
  let sortNonFeatured = '-created_at';

  // current course for media modal
  let currentCourse = null;

  const esc=(s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;',"`":'&#96;'};return (s==null?'':String(s)).replace(/[&<>\"'`]/g,ch=>m[ch]);};
  const fmtDate=(iso)=>{ if(!iso) return '-'; const d=new Date(iso); if(isNaN(d)) return esc(iso); return d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'}); };
  const qs=(sel)=> sel ? document.querySelector(sel) : null;
  const show=(el,v)=>{ if(!el) return; el.style.display = v ? '' : 'none'; };

  function showLoader(scope, v){ const row=qs(tabs[scope].loader); if(row) show(row, v); }
  function clearBody(scope){
    const rowsEl=qs(tabs[scope].rows);
    if(!rowsEl) return;
    rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(n=>n.remove());
  }

  // categories cache
  let categoryMap = {};
  async function loadCategoriesMap(){
    try{
      const res = await fetch('/api/landing/categories',{
        headers:{'Authorization':'Bearer '+TOKEN, 'Accept':'application/json'}
      });
      const j = await res.json().catch(()=>({}));
      const items = j.data || j.items || j || [];
      categoryMap = {};
      items.forEach(c=>{
        if(!c) return;
        const id = c.id ?? c.category_id;
        if(id == null) return;
        categoryMap[String(id)] = c.name || c.title || c.label || ('Category #'+id);
      });
    }catch(e){
      console.error('Failed to load categories', e);
      categoryMap = {};
    }
  }
  const catName = (id)=>{
    if(id == null) return '-';
    return categoryMap[String(id)] || ('#'+id);
  };

  function baseParams(scope){
    const usp=new URLSearchParams();
    const per = Number(perPageSel?.value || 30);
    const pg  = Number(state[scope].page||1);
    usp.set('per_page', per);
    usp.set('page', pg);
    if(q && q.value.trim()){
      usp.set('q', q.value.trim());
    }
    // send hint to API too
    if(scope==='featured'){
      usp.set('is_featured', 1);
      usp.set('sort', sortFeatured);
    }else{
      usp.set('is_featured', 0);
      usp.set('sort', sortNonFeatured);
    }
    return usp.toString();
  }

  function urlFor(scope){
    // reuses /api/courses index with is_featured filter
    return '/api/courses?' + baseParams(scope);
  }

  function syncSortHeaders(scope){
    const headSel = (scope==='featured') ? '#tab-featured thead th.sortable' : '#tab-nonfeatured thead th.sortable';
    const sort = (scope==='featured') ? sortFeatured : sortNonFeatured;
    document.querySelectorAll(headSel).forEach(th=>{
      th.classList.remove('asc','desc');
      const col = th.dataset.col;
      if(sort===col) th.classList.add('asc');
      if(sort==='-'+col) th.classList.add('desc');
    });

    if(scope==='featured'){
      if(sortFeatured==='featured_rank'){
        sortHint.textContent = 'By featured rank';
      }else if(sortFeatured==='-created_at'){
        sortHint.textContent = 'Newest first';
      }else if(sortFeatured==='created_at'){
        sortHint.textContent = 'Oldest first';
      }else{
        sortHint.textContent = 'Sorted by '+sortFeatured.replace('-','');
      }
    }
  }

  function badgeStatus(r){
    const st = (r.status || '').toLowerCase();
    if(st === 'published'){
      return '<span class="badge-soft badge-soft-success">Published</span>';
    }
    if(st === 'archived'){
      return '<span class="badge-soft badge-soft-danger">Archived</span>';
    }
    return '<span class="badge-soft">Draft</span>';
  }

  function courseTypePrice(r){
    const tp = (r.course_type || '').toLowerCase();
    const price = Number(r.price_amount || 0);
    const cur   = (r.price_currency || 'INR').toUpperCase();
    if(tp === 'free'){
      return '<div class="fw-semibold text-success">Free</div>';
    }
    const p = isFinite(price) ? price.toFixed(2) : esc(String(r.price_amount||0));
    return `<div class="fw-semibold">${esc(cur)} ${p}</div>
            <div class="small text-muted">${tp === 'paid' ? 'Paid' : esc(r.course_type||'-')}</div>`;
  }

  // 🔹 Same pattern as Courses page: View Course (admin) + Edit + Media
  function actionMenu(r){
    const uuid = r.uuid || r.id;
    const title = r.title || '';
    const editUrl = `${basePanel}/courses/create?edit=${encodeURIComponent(uuid)}`;
    const viewUrl = `/courses/${encodeURIComponent(uuid)}`;

    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
          <i class="fa fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item view-course-link"
               href="${viewUrl}"
               title="View Course">
              <i class="fa fa-eye"></i> View Course
            </a>
          </li>
          <li>
            <a class="dropdown-item"
               href="${editUrl}">
              <i class="fa fa-pen-to-square"></i> Edit Course
            </a>
          </li>
          <li>
            <button class="dropdown-item" type="button"
                    data-act="media"
                    data-uuid="${esc(uuid)}"
                    data-title="${esc(title)}">
              <i class="fa fa-images"></i> Course Featured Media
            </button>
          </li>
        </ul>
      </div>
    `;
  }

  function rowHTML(scope, r){
    const tr=document.createElement('tr');
    const created = fmtDate(r.created_at);
    const cat     = catName(r.category_id);
    const title   = r.title || '(Untitled course)';
    const stCls   = (r.status || '').toLowerCase()==='draft' ? 'state-draft'
                    : (r.status||'').toLowerCase()==='archived' ? 'state-archived' : '';
    if(stCls) tr.classList.add(stCls);

    const toggleChecked = (Number(r.is_featured || 0) === 1) ? 'checked' : '';

    const viewHref = `${basePanel}/courses/${encodeURIComponent(r.uuid || r.id)}`;

    // 🔹 Title clickable → same as Courses page
    const commonCols = `
      <td>
        <div class="fw-semibold">
          <a href="${viewHref}"
             class="link-offset-2 link-underline-opacity-0 view-course-link">
            ${esc(title)}
          </a>
        </div>
      </td>
      <td>${esc(cat)}</td>
      <td>${courseTypePrice(r)}</td>
    `;

    if(scope === 'featured'){
      tr.innerHTML = `
        <td class="text-center"><i class="fa fa-star text-warning"></i></td>
        ${commonCols}
        <td>${Number(r.featured_rank ?? 0)}</td>
<td>${badgeStatus(r)}</td>
        <td class="feature-toggle-cell text-center">
          <div class="form-check form-switch d-inline-flex align-items-center">
            <input class="form-check-input fc-toggle" type="checkbox" data-id="${esc(r.uuid || r.id)}" ${toggleChecked}>
          </div>
          <span class="feature-toggle-label">On = Featured</span>
        </td>
        <td class="text-end">${actionMenu(r)}</td>
      `;
    } else {
      tr.innerHTML = `
        <td class="text-center"><i class="fa-regular fa-star text-muted"></i></td>
        ${commonCols}
<td>${badgeStatus(r)}</td>
        <td class="feature-toggle-cell text-center">
          <div class="form-check form-switch d-inline-flex align-items-center">
            <input class="form-check-input fc-toggle" type="checkbox" data-id="${esc(r.uuid || r.id)}" ${toggleChecked}>
          </div>
          <span class="feature-toggle-label">On = Featured</span>
        </td>
        <td class="text-end">${actionMenu(r)}</td>
      `;
    }

    return tr;
  }

  function load(scope){
    const refs = tabs[scope];
    const rowsEl = qs(refs.rows);
    if(!rowsEl) return;
    clearBody(scope);
    show(qs(refs.empty), false);
    qs(refs.pager).innerHTML = '';
    qs(refs.meta).textContent = '—';
    showLoader(scope, true);

    fetch(urlFor(scope), {
      headers:{
        'Authorization':'Bearer '+TOKEN,
        'Accept':'application/json',
        'Cache-Control':'no-cache'
      }
    })
    .then(r=>r.json().then(j=>({ok:r.ok, j})))
    .then(({ok,j})=>{
      if(!ok) throw new Error(j?.message || 'Load failed');
      let items = j?.data || [];

      // IMPORTANT: client-side filtering to guarantee correct tabs
      if(scope === 'featured'){
        items = items.filter(r => Number(r.is_featured || 0) === 1);
      }else{
        items = items.filter(r => Number(r.is_featured || 0) !== 1);
      }

      const pag   = j?.pagination || j?.meta || {page:1, per_page:items.length || 1, total:items.length};

      if(items.length === 0){
        show(qs(refs.empty), true);
      }

      const frag=document.createDocumentFragment();
      items.forEach(r => frag.appendChild(rowHTML(scope, r)));
      rowsEl.appendChild(frag);

      const total=Number(pag.total||0);
      const per  =Number(pag.per_page||30);
      const cur  =Number(pag.page||1);
      const pages=Math.max(1, Math.ceil(total/per));

      const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
        <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
      </li>`;

      let html='';
      html+=li(cur<=1,false,'Previous',cur-1);
      const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
      if(s>1){
        html+=li(false,false,1,1);
        if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>';
      }
      for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
      if(e<pages){
        if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>';
        html+=li(false,false,pages,pages);
      }
      html+=li(cur>=pages,false,'Next',cur+1);

      const pagerEl = qs(refs.pager);
      pagerEl.innerHTML = html;
      pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a=>{
        a.addEventListener('click',()=>{
          const t=Number(a.dataset.page);
          if(!t || t===state[scope].page) return;
          state[scope].page = Math.max(1,t);
          load(scope);
          window.scrollTo({top:0, behavior:'smooth'});
        });
      });

      qs(refs.meta).textContent = `Showing page ${cur} of ${pages} — ${total} course(s)`;

      syncSortHeaders(scope);
    })
    .catch(e=>{
      console.error(e);
      show(qs(refs.empty), true);
      qs(refs.meta).textContent = 'Failed to load';
      err(e.message || 'Load error');
    })
    .finally(()=> showLoader(scope,false));
  }

  // Sorting header events
  document.querySelectorAll('#tab-featured thead th.sortable').forEach(th=>{
    th.addEventListener('click',()=>{
      const col = th.dataset.col;
      if(sortFeatured === col) sortFeatured = '-'+col;
      else if(sortFeatured === '-'+col) sortFeatured = col;
      else sortFeatured = col;
      state.featured.page = 1;
      load('featured');
    });
  });
  document.querySelectorAll('#tab-nonfeatured thead th.sortable').forEach(th=>{
    th.addEventListener('click',()=>{
      const col = th.dataset.col;
      if(sortNonFeatured === col) sortNonFeatured = '-'+col;
      else if(sortNonFeatured === '-'+col) sortNonFeatured = col;
      else sortNonFeatured = col;
      state.nonfeatured.page = 1;
      load('nonfeatured');
    });
  });

  // Search + reset + per-page
  let srT;
  if(q) q.addEventListener('input', ()=>{
    clearTimeout(srT);
    srT=setTimeout(()=>{
      state.featured.page = 1;
      state.nonfeatured.page = 1;
      load('featured');
      load('nonfeatured');
    }, 350);
  });

  if(btnReset){
    btnReset.addEventListener('click', ()=>{
      if(q) q.value = '';
      if(perPageSel) perPageSel.value = '30';
      sortFeatured    = 'featured_rank';
      sortNonFeatured = '-created_at';
      state.featured.page = 1;
      state.nonfeatured.page = 1;
      load('featured');
      load('nonfeatured');
    });
  }

  if(perPageSel){
    perPageSel.addEventListener('change', ()=>{
      state.featured.page = 1;
      state.nonfeatured.page = 1;
      load('featured');
      load('nonfeatured');
    });
  }

  // Tab show events (lazy load nonfeatured)
  document.querySelector('a[href="#tab-featured"]').addEventListener('shown.bs.tab', ()=>{
    load('featured');
  });
  document.querySelector('a[href="#tab-nonfeatured"]').addEventListener('shown.bs.tab', ()=>{
    load('nonfeatured');
  });

  // Toggle featured switch
  document.addEventListener('change', async (e)=>{
    const toggle = e.target.closest('.fc-toggle');
    if(!toggle) return;

    const id = toggle.dataset.id;  // uuid OR id
    const on = toggle.checked ? 1 : 0;

    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(id)}/featured`, {
        method:'PATCH',
        headers:{
          'Authorization':'Bearer '+TOKEN,
          'Content-Type':'application/json',
          'Accept':'application/json'
        },
        body: JSON.stringify({ is_featured: on })
      });
      const j = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message || 'Update failed');

      ok(on ? 'Course marked as featured' : 'Course un-featured');

      // Refresh both tabs so rows move correctly
      load('featured');
      load('nonfeatured');
    }catch(ex){
      console.error(ex);
      err(ex.message || 'Failed to update');
      toggle.checked = !on; // revert
    }
  });

    /* ===== Featured Media JS (card previews + choose from library) ===== */
  const mediaFiles   = document.getElementById('mediaFiles');
  const urlRow       = document.getElementById('urlRow');
  const urlInput     = document.getElementById('urlInput');
  const btnAddUrl    = document.getElementById('btnAddUrl');
  const btnSaveUrl   = document.getElementById('btnSaveUrl');
  const btnChooseLib = document.getElementById('btnChooseLib');
  const dropzone     = document.getElementById('dropzone');
  const mediaList    = document.getElementById('mediaList');
  const mTitle       = document.getElementById('m_title');
  const mSub         = document.getElementById('m_sub');
  const mediaCount   = document.getElementById('mediaCount');

  function iconFor(kind){
    const map={image:'fa-image',video:'fa-film',audio:'fa-music',pdf:'fa-file-pdf',other:'fa-file'};
    const k = map[(kind||'').toLowerCase()] || 'fa-file';
    return `<i class="fa ${k}"></i>`;
  }

  function openMedia(uuid, title, short=''){
    if(!uuid) return;
    currentCourse = { uuid, title, short };
    mTitle.textContent = title || 'Course';
    mSub.textContent   = (short && short.trim()) ? short.trim() : '—';
    urlRow.style.display='none';
    urlInput.value='';
    const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
    mediaModal.show();
    loadMedia();
  }

  // "Choose from Library" → scroll to existing cards
  btnChooseLib?.addEventListener('click', ()=>{
    if(!mediaList) return;
    mediaList.classList.add('highlight');
    mediaList.scrollIntoView({behavior:'smooth', block:'center'});
    setTimeout(()=> mediaList.classList.remove('highlight'), 1200);
  });

  async function loadMedia(){
    mediaList.innerHTML='<div class="text-center text-muted small py-4"><i class="fa fa-spinner fa-spin me-2"></i>Loading media...</div>';
    mediaCount.textContent='Loading…';
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`, {
        headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
      });
      const json = await res.json();
      if(!res.ok) throw new Error(json?.message||'Load failed');

      const items = json?.media || json?.data || [];
      mediaCount.textContent = `${items.length} item(s)`;

      if(!items.length){
        mediaList.innerHTML = `
          <div class="text-center text-muted small py-3">
            <i class="fa fa-image mb-2" style="font-size:22px;opacity:.6;"></i><br/>
            No featured media yet. Upload files, add a URL, or choose from library.
          </div>`;
        return;
      }

      const grid = document.createElement('div');
      grid.className = 'media-grid';
      grid.id = 'mediaLibraryList';

      items.forEach(it=>{
        const urlRaw = it.featured_url || it.url || '';
        const urlSafe = esc(urlRaw);
        // try a nice label
        let label = it.label || it.filename || '';
        if(!label && urlRaw){
          try{
            const u = new URL(urlRaw, window.location.origin);
            label = u.pathname.split('/').filter(Boolean).pop() || urlRaw;
          }catch{ label = urlRaw; }
        }
        label = esc(label);

        const kind = (it.featured_type || it.type || 'other').toLowerCase();
        const isImg = kind === 'image';

        const card = document.createElement('article');
        card.className = 'media-card';
        card.setAttribute('draggable','true');
        card.dataset.id = it.id;

        const thumbInner = isImg
          ? `<a href="${urlSafe}" target="_blank" rel="noopener">
               <img src="${urlSafe}" alt="${label || 'Media'}">
             </a>`
          : `<a href="${urlSafe}" target="_blank" rel="noopener">
               <div class="icon-center">${iconFor(kind)}</div>
             </a>`;

        card.innerHTML = `
          <div class="card-thumb">
            ${thumbInner}
          </div>
          <div class="card-body">
            <div class="name" title="${urlSafe}">${label || 'Media file'}</div>
            <div class="meta">
              Type: ${esc(it.featured_type || it.type || 'other')}
              • Order: <span class="ord">${it.order_no || 0}</span>
            </div>
          </div>
          <div class="card-actions">
            <button class="btn-icon" title="Delete" data-del="${it.id}">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        `;

        grid.appendChild(card);
      });

      mediaList.innerHTML='';
      mediaList.appendChild(grid);

      mediaList.querySelectorAll('[data-del]').forEach(btn=>{
        btn.addEventListener('click', ()=> deleteMedia(btn.getAttribute('data-del')));
      });

      initDragReorder();
    }catch(e){
      mediaList.innerHTML = '<div class="text-center text-danger small py-3">Failed to load media.</div>';
      mediaCount.textContent='Failed to load';
      err(e.message || 'Media load error');
    }
  }

  async function deleteMedia(id){
    const {isConfirmed}=await Swal.fire({
      icon:'warning',
      title:'Delete media?',
      showCancelButton:true,
      confirmButtonText:'Delete',
      confirmButtonColor:'#ef4444'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media/${encodeURIComponent(id)}`, {
        method:'DELETE',
        headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}
      });
      const j = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message||'Delete failed');
      ok('Media deleted');
      loadMedia();
    }catch(e){ err(e.message || 'Delete failed'); }
  }

    async function uploadFiles(fileList){
    if (!currentCourse || !currentCourse.uuid) {
      err('No course selected for upload');
      return;
    }

    const fd = new FormData();
    Array.from(fileList).forEach(f => fd.append('files[]', f));

    try {
      // Optional: small inline "uploading" feedback
      mediaList.innerHTML = `
        <div class="text-center text-muted small py-4">
          <i class="fa fa-spinner fa-spin me-2"></i>Uploading…
        </div>`;
      mediaCount.textContent = 'Uploading…';

      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`, {
        method: 'POST',
        headers: {
          'Authorization': 'Bearer ' + TOKEN,
          'Accept': 'application/json'
        },
        body: fd
      });

      const j = await res.json().catch(() => ({}));
      if (!res.ok) {
        throw new Error(j?.message || 'Upload failed');
      }

      const inserted = (j?.inserted || j?.media || j?.data || []);
      ok(`Uploaded ${inserted.length || fileList.length} file(s)`);

      // 🔴 Force a fresh reload AFTER the API finishes
      await loadMedia();
    } catch (e) {
      console.error(e);
      err(e.message || 'Upload failed');
      // keep previous list if loadMedia failed
      mediaCount.textContent = 'Upload failed';
    }
  }

  mediaFiles?.addEventListener('change', async () => {
    if (!mediaFiles.files?.length) return;
    await uploadFiles(mediaFiles.files);
    // reset so selecting the same file again still triggers "change"
    mediaFiles.value = '';
  });

  btnAddUrl?.addEventListener('click', ()=>{
    urlRow.style.display = (urlRow.style.display==='none' || !urlRow.style.display) ? '' : 'none';
  });

  btnSaveUrl?.addEventListener('click', async ()=>{
    const url=(urlInput.value||'').trim();
    if(!/^https?:\/\//i.test(url))
      return Swal.fire('Invalid URL','Provide a valid http(s) URL.','info');
    try{
      const res=await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`,{
        method:'POST',
        headers:{
          'Authorization':'Bearer '+TOKEN,
          'Content-Type':'application/json',
          'Accept':'application/json'
        },
        body: JSON.stringify({ url })
      });
      const j=await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message||'Add failed');
      ok('Media added');
      urlInput.value='';
      urlRow.style.display='none';
      loadMedia();
    }catch(e){ err(e.message || 'Add failed'); }
  });

  ['dragenter','dragover'].forEach(ev=> dropzone?.addEventListener(ev, e=>{
    e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag');
  }));
  ['dragleave','drop'].forEach(ev=> dropzone?.addEventListener(ev, e=>{
    e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag');
  }));
  dropzone?.addEventListener('drop', e=>{
    const files = e.dataTransfer?.files || [];
    if(files.length) uploadFiles(files);
  });

  function initDragReorder(){
    const cards = mediaList.querySelectorAll('.media-card');
    let dragSrc = null;

    cards.forEach(card=>{
      card.addEventListener('dragstart', e=>{
        dragSrc = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed='move';
      });
      card.addEventListener('dragend', ()=>{
        dragSrc = null;
        card.classList.remove('dragging');
      });
      card.addEventListener('dragover', e=>{
        e.preventDefault();
        e.dataTransfer.dropEffect='move';
      });
      card.addEventListener('drop', e=>{
        e.preventDefault();
        if(!dragSrc || dragSrc===card) return;
        const grid = mediaList.querySelector('.media-grid');
        const items=[...grid.querySelectorAll('.media-card')];
        const srcIdx=items.indexOf(dragSrc);
        const dstIdx=items.indexOf(card);
        if(srcIdx<dstIdx) card.after(dragSrc); else card.before(dragSrc);
        persistReorder();
      });
    });
  }

  async function persistReorder(){
    const ids=[...mediaList.querySelectorAll('.media-card')].map(n=> Number(n.dataset.id));
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media/reorder`, {
        method:'POST',
        headers:{
          'Authorization':'Bearer '+TOKEN,
          'Content-Type':'application/json',
          'Accept':'application/json'
        },
        body: JSON.stringify({ ids })
      });
      const j=await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message||'Reorder failed');
      ok('Order updated');
      loadMedia();
    }catch(e){ err(e.message || 'Reorder failed'); }
  }

  // Delegated click for media action in dropdown (unchanged)
  document.addEventListener('click', (e)=>{
    const item = e.target.closest('.dropdown-item[data-act]');
    if(!item) return;
    const act = item.dataset.act;
    if(act === 'media'){
      e.preventDefault();
      const uuid  = item.dataset.uuid;
      const title = item.dataset.title || '';
      openMedia(uuid, title);
      const dd = item.closest('.dropdown');
      const toggle = dd?.querySelector('.dd-toggle,[data-bs-toggle="dropdown"]');
      if(toggle){
        bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
      }
    }
  });


  // init
  (async ()=>{
    await loadCategoriesMap();
    // initial load for featured tab
    load('featured');
  })();
})();
</script>
@endpush
