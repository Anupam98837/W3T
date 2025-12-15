{{-- resources/views/modules/courses/manageCourseModule.blade.php --}}
@section('title','Manage Course Modules')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Shell ===== */
.cm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}

/* Sorting */
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Row state cues */
tr.state-archived td{background:color-mix(in oklab, var(--muted-color) 6%, transparent)}
tr.state-deleted td{background:color-mix(in oklab, var(--danger-color) 6%, transparent)}

/* Status badges */
.badge-soft{background:color-mix(in oklab, var(--muted-color) 12%, transparent);color:var(--ink)}
.table .badge.badge-success{background:var(--success-color)!important;color:#fff!important}
.table .badge.badge-secondary{background:#64748b!important;color:#fff!important}

/* Dropdowns inside table (with portal) */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:5000}
.dropdown-menu.dd-portal{position:fixed!important;left:0;top:0;transform:none!important;z-index:5000;border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;background:var(--surface)}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color)!important}

/* Buttons */
.icon-btn{display:inline-flex;align-items:center;justify-content:center;height:34px;min-width:34px;padding:0 10px;border:1px solid var(--line-strong);background:var(--surface);border-radius:10px}
.icon-btn:hover{box-shadow:var(--shadow-1)}

/* Empty & loader */
.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

/* Modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control,.form-select,textarea{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control,html.theme-dark .form-select,html.theme-dark textarea{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}

/* Reorder affordances */
.dragging{opacity:.6}
.drag-handle{cursor:grab;color:#9ca3af}
.drag-handle:active{cursor:grabbing}

/* Dark tweaks */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}

/* Tab icons */
.nav-tabs .nav-link i { font-size: 0.95rem; line-height: 1; vertical-align: middle; }
.nav-tabs .nav-link { display: inline-flex; align-items: center; gap: .5rem; }

/* Ensure common wrappers don't clip dropdowns */
.table-responsive,
.table-wrap,
.card,
.panel,
.cm-wrap {
  transform: none !important;   /* transforms create new stacking contexts and break fixed/absolute positioning */
}

/* keep dropdown toggle above table row so it remains clickable */
.table-wrap .dd-toggle { z-index: 7; position: relative; }

/* small: ensure dropdown caret/contents not clipped visually */
.dropdown-menu { overflow: visible; }
</style>
@endpush

@section('content')
<div class="cm-wrap">

  {{-- ===== Global (applies to all tabs) ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-xxl d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Course</label>
        <select id="courseSel" class="form-select" style="min-width:260px;">
          <option value="">Select a course…</option>
        </select>
      </div>

      <div id="courseHint" class="small text-muted" style="display:none;">Pick a course to load modules.</div>
    </div>

    
  </div>

  {{-- ===== Tabs ===== --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true">
      <i class="fa-solid fa-layer-group me-2" aria-hidden="true"></i>
      Modules
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-archived" role="tab" aria-selected="false">
      <i class="fa-solid fa-box-archive me-2" aria-hidden="true"></i>
      Archived
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-bin" role="tab" aria-selected="false">
      <i class="fa-solid fa-trash-can me-2" aria-hidden="true"></i>
      Bin
    </a>
  </li>
</ul>


  <div class="tab-content mb-3">

    {{-- ===== ACTIVE ===== --}}
    <div class="tab-pane fade show active" id="tab-active" role="tabpanel">

      {{-- Toolbar (Active only) --}}
      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;" disabled>
              <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
            </select>
          </div>
          <div class="position-relative" style="min-width:280px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search title/description…" disabled>
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>
          <button id="btnFilterOpen" class="btn btn-light" disabled title="Filters">
        <i class="fa fa-filter me-1"></i>Filters
      </button>

          <!-- Status & Apply moved to Filter Modal -->
          

          <button id="btnReset" class="btn btn-primary" disabled><i class="fa fa-rotate-left me-1"></i>Reset</button>
        </div>
        <div class="col-12 col-xxl-auto ms-xxl-auto d-flex justify-content-xxl-end gap-2">
            <button id="btnReorder" class="btn btn-primary" disabled>
        <i class="fa fa-up-down-left-right me-1"></i>Reorder
      </button>
      <button id="btnCreate" class="btn btn-primary" disabled>
        <i class="fa fa-plus me-1"></i>New Module
      </button>
    </div>

        <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end small text-muted">
          Sorting: <span id="sortHint" class="ms-1">Newest first</span>
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
                  <th class="sortable" data-col="title">TITLE <span class="caret"></span></th>
                  <th style="width:22%;">SHORT DESCRIPTION</th>
                  <th class="sortable" data-col="order_no" style="width:110px;">ORDER <span class="caret"></span></th>
                  <th class="sortable" data-col="status" style="width:130px;">STATUS <span class="caret"></span></th>
                  <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-active">
                <tr id="loaderRow-active" style="display:none;">
                  <td colspan="7" class="p-0">
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
                <tr id="askCourse-active">
                  <td colspan="7" class="p-4 text-center text-muted">
                    <i class="fa fa-layer-group mb-2" style="font-size:28px;opacity:.6"></i>
                    <div>Please select a course to load its modules.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No modules found.</div>
          </div>

          <div id="reorderHint" class="p-3 small text-muted" style="display:none;">
            Reorder mode is ON — drag rows using <i class="fa fa-grip-lines-vertical"></i> and click <b>Save Order</b>.
            This applies to the currently visible page. Set a larger “Per page” to reorder more items at once.
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-active">—</div>
            <div class="d-flex align-items-center gap-2">
              <button id="btnSaveOrder" class="btn btn-primary btn-sm" style="display:none;"><i class="fa fa-floppy-disk me-1"></i>Save Order</button>
              <button id="btnCancelOrder" class="btn btn-light btn-sm" style="display:none;">Cancel</button>
              <nav style="position:relative; z-index:1;"><ul id="pager-active" class="pagination mb-0"></ul></nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== ARCHIVED ===== --}}
    <div class="tab-pane fade" id="tab-archived" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>TITLE</th>
                  <th style="width:22%;">SHORT DESCRIPTION</th>
                  <th style="width:110px;">ORDER</th>
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-archived">
                <tr id="loaderRow-archived" style="display:none;">
                  <td colspan="5" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr id="askCourse-archived">
                  <td colspan="5" class="p-4 text-center text-muted">
                    <i class="fa fa-box-archive mb-2" style="font-size:28px;opacity:.6"></i>
                    <div>Select a course to view archived modules.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-archived" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-box-archive mb-2" style="font-size:32px; opacity:.6"></i>
            <div>No archived modules.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-archived">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-archived" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== BIN ===== --}}
    <div class="tab-pane fade" id="tab-bin" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>TITLE</th>
                  <th style="width:22%;">SHORT DESCRIPTION</th>
                  <th style="width:140px;">DELETED AT</th>
                  <th class="text-end" style="width:160px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-bin">
                <tr id="loaderRow-bin" style="display:none;">
                  <td colspan="4" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr id="askCourse-bin">
                  <td colspan="4" class="p-4 text-center text-muted">
                    <i class="fa fa-trash mb-2" style="font-size:28px;opacity:.6"></i>
                    <div>Select a course to view items in Bin.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-bin" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash mb-2" style="font-size:32px; opacity:.6"></i>
            <div>No items in Bin.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-bin">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-bin" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.tab-content -->
</div>

{{-- ===== Create / Edit Modal ===== --}}
<div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="mm_title" class="modal-title"><i class="fa fa-book me-2"></i>Create Module</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="mm_mode" value="create">
        <input type="hidden" id="mm_key" value="">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Course</label>
            <input id="mm_course_label" class="form-control" readonly>
            <input id="mm_course_id" type="hidden">
            <div class="small text-muted mt-1">Course is taken from the page toolbar.</div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select id="mm_status" class="form-select">
              <option value="draft" selected>Draft</option>
              <option value="published">Published</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Order No.</label>
            <input id="mm_order" type="number" min="0" step="1" class="form-control" value="0">
          </div>

          <div class="col-12">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input id="mm_title_input" class="form-control" maxlength="255" placeholder="e.g., Module 1 — Getting Started">
          </div>

          <div class="col-12">
            <label class="form-label">Short Description</label>
            <textarea id="mm_short" class="form-control" rows="3" placeholder="One-paragraph intro (optional)"></textarea>
          </div>
<div class="col-12">
  <label class="form-label">Long Description</label>

  <!-- Toolbar (RTE) -->
  <div id="mm_rte_toolbar" class="toolbar mb-2" aria-label="Long description toolbar" style="display:flex;gap:8px;flex-wrap:wrap;">
    <button type="button" class="tool btn btn-light btn-sm" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
    <button type="button" class="tool btn btn-light btn-sm" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
    <button type="button" class="tool btn btn-light btn-sm" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
    <button type="button" class="tool btn btn-light btn-sm" data-cmd="insertUnorderedList" title="Bulleted list"><i class="fa fa-list-ul"></i></button>
    <button type="button" class="tool btn btn-light btn-sm" data-cmd="insertOrderedList" title="Numbered list"><i class="fa fa-list-ol"></i></button>
    <button type="button" class="tool btn btn-light btn-sm" data-cmd="createLink" title="Insert link"><i class="fa fa-link"></i></button>
    <button type="button" class="tool btn btn-light btn-sm" data-cmd="removeFormat" title="Remove formatting"><i class="fa fa-eraser"></i></button>

    <select id="mm_insertHeading" class="tool form-select form-select-sm" title="Insert heading" style="width:auto;min-width:120px;">
      <option value="">Insert…</option>
      <option value="h2">Heading</option>
      <option value="p">Paragraph</option>
    </select>
  </div>

  <!-- RTE container -->
  <div id="mm_long_editor" style="min-height:240px; background:#fff; border-radius:12px; border:1px solid var(--line-strong); padding:12px;" contenteditable="true" role="textbox" aria-label="Module long description editor" spellcheck="true"></div>

  <!-- Hidden textarea used to hold HTML that will be sent to the API -->
  <textarea id="mm_long" class="form-control d-none" rows="6" placeholder="Detailed overview (optional)"></textarea>

  <div class="small text-muted mt-1">Use the editor to format your description. HTML will be saved to the server.</div>
</div>

          <div class="col-12">
            <label class="form-label">Metadata (JSON)</label>
            <textarea id="mm_meta" class="form-control" rows="4" placeholder='e.g. {"tags":["intro","basics"],"quiz_count":0}'></textarea>
            <div class="small text-muted mt-1">Optional. Must be valid JSON. Saved as JSON column.</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="mm_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
      </div>
    </div>
  </div>
</div>

<!-- ===== Filter Modal: Status, Per page, Sort & Apply ===== -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">All (non-archived)</option>
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Per page</label>
            <select id="modal_per_page" class="form-select">
              <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="order_no">Order (asc)</option>
              <option value="-order_no">Order (desc)</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button id="btnClearFilters" type="button" class="btn btn-light">Clear</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="btnApplyFilters" type="button" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
  <div id="okToast" class="toast text-bg-success border-0"><div class="d-flex">
    <div id="okMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
  </div></div>
  <div id="errToast" class="toast text-bg-danger border-0 mt-2"><div class="d-flex">
    <div id="errMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
  </div></div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* =================== Dropdown portal to <body> =================== */
(function(){
  let activePortal=null;
  const place=(menu, btnRect)=>{
    const vw=Math.max(document.documentElement.clientWidth, window.innerWidth||0);
    menu.classList.add('dd-portal'); menu.style.display='block'; menu.style.visibility='hidden';
    document.body.appendChild(menu);
    const mw=menu.offsetWidth, mh=menu.offsetHeight;
    let left = (vw - btnRect.right < mw && btnRect.right - mw > 8) ? (btnRect.right - mw) : btnRect.left;
    let top  = btnRect.bottom + 4;
    const vh=Math.max(document.documentElement.clientHeight, window.innerHeight||0);
    if(top + mh > vh - 8) top = Math.max(8, vh - mh - 8);
    menu.style.left = left + 'px'; menu.style.top = top + 'px'; menu.style.visibility='visible';
  };
  document.addEventListener('show.bs.dropdown', (ev)=>{
  // Always work from the .dropdown wrapper
  const dd   = ev.target.closest('.dropdown');
  if (!dd) return;

  const btn  = dd.querySelector('.dd-toggle,[data-bs-toggle="dropdown"]');
  const menu = dd.querySelector('.dropdown-menu');
  if (!btn || !menu) return;

  if (activePortal?.menu?.isConnected) {
    activePortal.menu.classList.remove('dd-portal');
    activePortal.parent.appendChild(activePortal.menu);
    activePortal = null;
  }

  const rect = btn.getBoundingClientRect();
  menu.__parent = menu.parentElement;
  place(menu, rect);
  activePortal = { menu, parent: menu.__parent };

  const close = () => {
    try { bootstrap.Dropdown.getOrCreateInstance(btn).hide(); } catch {}
  };
  menu.__ls = [
    ['resize', close, false],
    ['scroll', close, true]
  ];
  window.addEventListener('resize', close);
  document.addEventListener('scroll', close, true);
});

  document.addEventListener('hidden.bs.dropdown', (ev)=>{
    const dd=ev.target; const menu=dd.querySelector('.dropdown-menu.dd-portal') || activePortal?.menu;
    if(!menu) return;
    if(menu.__ls){ document.removeEventListener('scroll', menu.__ls[1][1], true); window.removeEventListener('resize', menu.__ls[0][1]); menu.__ls=null; }
    if(menu.__parent){ menu.classList.remove('dd-portal'); menu.style.cssText=''; menu.__parent.appendChild(menu); activePortal=null; }
  });
})();

/* =================== Dropdown toggle click =================== */
document.addEventListener('click',(e)=>{
  const btn=e.target.closest('.dd-toggle'); if(!btn) return;
  e.preventDefault(); e.stopPropagation();
  bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'}).toggle();
});

/* =================== App logic =================== */
(function(){
  /* Auth */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/');
    return;
  }

  /* Toasts */
  const okToast=new bootstrap.Toast(document.getElementById('okToast'));
  const errToast=new bootstrap.Toast(document.getElementById('errToast'));
  const ok=(m)=>{document.getElementById('okMsg').textContent=m||'Done'; okToast.show();};
  const err=(m)=>{document.getElementById('errMsg').textContent=m||'Something went wrong'; errToast.show();};

  /* Elements */
  const courseSel = document.getElementById('courseSel');
  const courseHint= document.getElementById('courseHint');
  const btnCreate = document.getElementById('btnCreate');
  const btnReorder= document.getElementById('btnReorder');
  const sortHint  = document.getElementById('sortHint');
  const btnFilterOpen = document.getElementById('btnFilterOpen');

  /* Active toolbar */
  const q           = document.getElementById('q');
  const perPageSel  = document.getElementById('per_page');
  const btnReset    = document.getElementById('btnReset');

  /* Modal filter elements */
  const filterModalEl = document.getElementById('filterModal');
  const modalStatus   = document.getElementById('modal_status');
  const modalPerPage  = document.getElementById('modal_per_page');
  const modalSort     = document.getElementById('modal_sort');
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const btnClearFilters = document.getElementById('btnClearFilters');

  /* Rows & pagers */
  const tabs = {
    active   :{rows:'#rows-active',   loader:'#loaderRow-active',   empty:'#empty-active',   pager:'#pager-active',   ask:'#askCourse-active',   meta:'#metaTxt-active'},
    archived :{rows:'#rows-archived', loader:'#loaderRow-archived', empty:'#empty-archived', pager:'#pager-archived', ask:'#askCourse-archived', meta:'#metaTxt-archived'},
    bin      :{rows:'#rows-bin',      loader:'#loaderRow-bin',      empty:'#empty-bin',      pager:'#pager-bin',      ask:'#askCourse-bin',      meta:'#metaTxt-bin'},
  };

  /* Modal and mm refs */
  const mm = {
    modal  : new bootstrap.Modal(document.getElementById('moduleModal')),
    mode   : document.getElementById('mm_mode'),
    key    : document.getElementById('mm_key'),
    title  : document.getElementById('mm_title'),
    cLabel : document.getElementById('mm_course_label'),
    cId    : document.getElementById('mm_course_id'),
    status : document.getElementById('mm_status'),
    order  : document.getElementById('mm_order'),
    ttl    : document.getElementById('mm_title_input'),
    short  : document.getElementById('mm_short'),
    long   : document.getElementById('mm_long'),      // hidden textarea (HTML)
    meta   : document.getElementById('mm_meta'),
    save   : document.getElementById('mm_save'),
  };

  // ===== Button-only spinner for Save (NO overlay) =====
const MM_SAVE_DEFAULT = mm.save ? mm.save.innerHTML : '';

function mmSaveLoading(on){
  if(!mm.save) return;

  if(on){
    if(!mm.save.dataset.oldHtml) mm.save.dataset.oldHtml = mm.save.innerHTML;
    mm.save.disabled = true;
    mm.save.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Save`;
  } else {
    mm.save.disabled = false;
    mm.save.innerHTML = mm.save.dataset.oldHtml || MM_SAVE_DEFAULT;
    delete mm.save.dataset.oldHtml;
  }
}


  /* RTE elements (must exist in DOM) */
  const mmRte = document.getElementById('mm_long_editor');
  const mmToolbar = document.getElementById('mm_rte_toolbar');
  const mmHeading = document.getElementById('mm_insertHeading');

  /* State */
  let currentCourseId = '';
  const state = { active:{page:1}, archived:{page:1}, bin:{page:1} };
  let sort = '-created_at';
  let reorderMode = false;
  let draggingRow = null;

  /* Utils */
  const esc=(s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>m[ch]); };
  const fmtDate=(iso)=>{ if(!iso) return '-'; const d=new Date(iso); if(isNaN(d)) return esc(iso); return d.toLocaleString(undefined,{year:'numeric',month:'short','day':'2-digit','hour':'2-digit','minute':'2-digit'}); };
  const badgeStatus=(s)=>{ s=String(s||'').toLowerCase(); const map={draft:'warning',published:'success',archived:'secondary'}; const cls=map[s]||'secondary'; return `<span class="badge badge-${cls} text-uppercase">${esc(s)}</span>`; };
  const qs=(sel)=>document.querySelector(sel);
  const show=(el,v)=>{ el.style.display = v ? '' : 'none'; };
  const enable=(el,v)=>{ if(!el) return; el.disabled = !v; };

  const showLoader=(which, v)=>{ show(qs(tabs[which].loader), v); };
  const clearBody=(which)=>{
    const rowsEl=qs(tabs[which].rows);
    rowsEl.querySelectorAll('tr:not([id^="loaderRow"]):not([id^="askCourse"])').forEach(n=>n.remove());
  };

  function setToolbarEnabled(on){
    // status & Apply moved into modal; keep search, per-page, reset & create/reorder buttons here
    [q, perPageSel, btnReset, btnCreate, btnReorder, btnFilterOpen].forEach(el=> enable(el, on));
    courseHint.style.display = on ? 'none' : '';
  }

  function syncSortHeaders(){
    document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{
      th.classList.remove('asc','desc');
      const col = th.dataset.col;
      if(sort===col) th.classList.add('asc');
      if(sort==='-'+col) th.classList.add('desc');
    });
    sortHint.textContent = (sort==="-created_at") ? "Newest first" : (sort==="created_at" ? "Oldest first" : ("Sorted by "+sort.replace('-','')+(sort.startsWith('-')?' (desc)':'')));
  }
(function () {
  // prevent double-init if included multiple times
  if (window.__RTE_COURSEMODULE_SYNC__) return;
  window.__RTE_COURSEMODULE_SYNC__ = true;

  const FORMAT_MAP = { H1: "h1", H2: "h2", H3: "h3", P: "p" };

  function findEditorForToolbar(toolbar) {
    // 1) Explicit binding (optional): <div class="toolbar" data-editor="#mm_long_editor">
    const sel = toolbar.getAttribute("data-editor");
    if (sel) {
      const ed = document.querySelector(sel);
      if (ed) return ed;
    }

    // 2) Your current layout: toolbar then editor directly next
    let next = toolbar.nextElementSibling;
    if (next && (next.getAttribute("contenteditable") === "true" || next.isContentEditable)) {
      return next;
    }

    // 3) If wrapped: search nearby for a contenteditable editor
    const block =
      toolbar.closest(".modal,.col-12,.col-md-12,.form-group,.row,.card,.modal-body") ||
      toolbar.parentElement ||
      document;

    // Prefer your id if present
    const mm = block.querySelector("#mm_long_editor");
    if (mm) return mm;

    // Otherwise any contenteditable region
    return block.querySelector('[contenteditable="true"]');
  }

  function selectionInside(editor) {
    const sel = document.getSelection();
    if (!sel || !sel.anchorNode) return false;
    const node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
    return node === editor || editor.contains(node);
  }

  // More reliable than queryCommandValue in many browsers:
  function isFormatActive(editor, fmt) {
    const want = (FORMAT_MAP[fmt] || fmt || "").toLowerCase();
    if (!want) return false;

    const sel = document.getSelection();
    if (!sel || !sel.rangeCount) return false;

    let node = sel.getRangeAt(0).commonAncestorContainer;
    node = node.nodeType === 3 ? node.parentNode : node;

    while (node && node !== editor) {
      if (node.nodeType === 1 && node.tagName && node.tagName.toLowerCase() === want) return true;
      node = node.parentNode;
    }
    return false;
  }

  function bindToolbar(toolbar) {
    if (toolbar.__rteBound) return;

    const editor = findEditorForToolbar(toolbar);
    if (!editor) return;

    toolbar.__rteBound = true;

    // Only buttons that actually represent actions
    const tools = Array.from(toolbar.querySelectorAll(".tool"))
      .filter((el) => el.dataset && (el.dataset.cmd || el.dataset.format));

    function update() {
      const inside = selectionInside(editor) || document.activeElement === editor;

      // optional "active ring"
      editor.classList.toggle("active", document.activeElement === editor);

      if (!inside) return;

      tools.forEach((btn) => {
        const cmd = btn.dataset.cmd;
        const fmt = btn.dataset.format;

        let on = false;
        try {
          if (cmd) on = !!document.queryCommandState(cmd);
          else if (fmt) on = isFormatActive(editor, fmt);
        } catch {
          on = false;
        }

        btn.classList.toggle("active", on);
        btn.setAttribute("aria-pressed", on ? "true" : "false");
      });
    }

    // update after toolbar actions (your existing execCommand handler can remain)
    toolbar.addEventListener("click", () => setTimeout(update, 30));

    // update while typing / moving caret
    ["keyup", "mouseup", "input", "focus", "blur"].forEach((ev) => {
      editor.addEventListener(ev, () => setTimeout(update, 0));
    });

    // selection change (only update if selection is within this editor)
    document.addEventListener("selectionchange", () => {
      if (selectionInside(editor)) update();
    });

    update();
  }

  function initAll() {
    // Your toolbar has class="toolbar"
    document.querySelectorAll(".toolbar").forEach(bindToolbar);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll);
  } else {
    initAll();
  }

  // handle editors that appear later (bootstrap modals, dynamic html)
  const mo = new MutationObserver(() => initAll());
  mo.observe(document.body, { childList: true, subtree: true });
})();

  /* ========== Load Courses (once) ========== */
  (async function loadCourses(){
    try{
      const res=await fetch('/api/courses?per_page=1000',{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Failed to load courses');
      const items=j?.data || j?.courses || [];
      courseSel.innerHTML = '<option value="">Select a course…</option>' + items.map(c=>`<option value="${c.id}">${esc(c.title || c.name || '(untitled)')}</option>`).join('');
      setToolbarEnabled(false);
    }catch(e){
      err(e.message||'Course list error');
    }
  })();

  /* ========== Course change ========== */
  courseSel.addEventListener('change', ()=>{
    currentCourseId = courseSel.value || '';
    const on = !!currentCourseId;
    setToolbarEnabled(on);
    clearAllTables();
    if(on){
      mm.cLabel.value = courseSel.options[courseSel.selectedIndex]?.text || '';
      mm.cId.value    = currentCourseId;
      state.active.page=1;
      load('active');
    }
  });

  function clearAllTables(){
    ['active','archived','bin'].forEach(scope=>{
      clearBody(scope);
      show(qs(tabs[scope].empty), false);
      show(qs(tabs[scope].ask), !currentCourseId);
      qs(tabs[scope].pager).innerHTML='';
      qs(tabs[scope].meta).textContent='—';
    });
  }

  /* ========== Build URLs ========== */
  function baseParams(scope){
    const usp=new URLSearchParams();
    usp.set('course_id', currentCourseId);

    // Prefer modal-per-page if user set it; else fall back to toolbar per_page
    const modalPer = (modalPerPage && modalPerPage.value) ? Number(modalPerPage.value) : null;
    const per = modalPer || Number(perPageSel?.value || 20);
    const pg = Number(state[scope].page||1);
    usp.set('per_page', per);
    usp.set('page', pg);

    if(scope==='active'){
      // prefer modal sort if set, otherwise use global sort variable
      const sVal = (modalSort && modalSort.value) ? modalSort.value : sort;
      usp.set('sort', sVal);

      // search query from top toolbar
      if(q && q.value.trim()) usp.set('q', q.value.trim());

      // status comes from modal now
      const st = (modalStatus && modalStatus.value) ? modalStatus.value : '';
      if(st) usp.set('status', st);
    }else if(scope==='archived'){
      usp.set('status','archived');
      usp.set('sort', '-created_at');
    }else{
      usp.set('sort','-created_at');
    }
    return usp.toString();
  }
  function urlFor(scope){
    if(scope==='bin') return '/api/course-modules/bin?' + baseParams(scope);
    return '/api/course-modules?' + baseParams(scope);
  }

  /* ========== Row builders & DnD helpers ========== */
  function actionMenu(scope, r){
    const key = r.uuid || r.id;
    if(scope==='active' || scope==='archived'){
      const archived = String(r.status||'').toLowerCase()==='archived';
      return `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" data-act="edit" data-key="${key}" data-name="${esc(r.title||'')}"><i class="fa fa-pen-to-square"></i> Edit</button></li>
            <li><hr class="dropdown-divider"></li>
            ${archived
              ? `<li><button class="dropdown-item" data-act="unarchive" data-key="${key}" data-name="${esc(r.title||'')}"><i class="fa fa-box-open"></i> Unarchive</button></li>`
              : `<li><button class="dropdown-item" data-act="archive" data-key="${key}" data-name="${esc(r.title||'')}"><i class="fa fa-box-archive"></i> Archive</button></li>`
            }
            <li><button class="dropdown-item text-danger" data-act="delete" data-key="${key}" data-name="${esc(r.title||'')}"><i class="fa fa-trash"></i> Delete</button></li>
          </ul>
        </div>`;
    }
    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-vertical"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" data-act="restore" data-key="${key}" data-name="${esc(r.title||'')}"><i class="fa fa-rotate-left"></i> Restore</button></li>
          <li><button class="dropdown-item text-danger" data-act="force" data-key="${key}" data-name="${esc(r.title||'')}"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>
        </ul>
      </div>`;
  }

  function rowHTML(scope, r){
    const tr=document.createElement('tr');
    const short = (r.short_description || '') + '';
    const created = fmtDate(r.created_at);
    const delAt   = fmtDate(r.deleted_at);
    const ord = (r.order_no==null? '-' : r.order_no);
    const isArchived = String(r.status||'').toLowerCase()==='archived';
    const isDeleted  = !!r.deleted_at;

    if(isArchived && scope!=='bin') tr.classList.add('state-archived');
    if(isDeleted  || scope==='bin') tr.classList.add('state-deleted');

    if(scope==='active'){
      tr.draggable = reorderMode;
      tr.dataset.key = r.uuid || r.id;
      tr.dataset.id  = r.id;
      tr.classList.add('reorderable');
      tr.innerHTML = `
        <td class="text-center"><i class="fa fa-grip-lines-vertical drag-handle"></i></td>
        <td>
          <div class="fw-semibold">${esc(r.title || '-')}</div>
          <div class="small text-muted">${esc((short || '').slice(0,100))}${short && short.length>100 ? '…' : ''}</div>
        </td>
        <td>${esc((short || '').slice(0,140))}${short && short.length>140 ? '…' : ''}</td>
        <td>${esc(ord)}</td>
        <td>${badgeStatus(r.status || '-')}</td>
        <td>${created}</td>
        <td class="text-end">${actionMenu(scope, r)}</td>`;

      // ensure handle opacity
      const handle = tr.querySelector('.drag-handle');
      if(reorderMode){ handle && (handle.style.opacity = '1'); } else { handle && (handle.style.opacity = '.35'); }

      // DnD events
      tr.addEventListener('dragstart', (ev)=>{ if(!reorderMode){ ev.preventDefault(); return; } draggingRow=tr; tr.classList.add('dragging'); });
      tr.addEventListener('dragend',   ()=>{ tr.classList.remove('dragging'); draggingRow=null; });
      tr.addEventListener('dragover',  (ev)=>{ if(!reorderMode) return; ev.preventDefault();
        const tbody = tr.parentElement;
        const after = getDragAfterElement(tbody, ev.clientY);
        if(after==null) tbody.appendChild(draggingRow); else tbody.insertBefore(draggingRow, after);
      });
      return tr;
    }

    if(scope==='archived'){
      tr.innerHTML = `
        <td>
          <div class="fw-semibold">${esc(r.title || '-')}</div>
          <div class="small text-muted">${esc((short || '').slice(0,100))}${short && short.length>100 ? '…' : ''}</div>
        </td>
        <td>${esc((short || '').slice(0,140))}${short && short.length>140 ? '…' : ''}</td>
        <td>${esc(ord)}</td>
        <td>${created}</td>
        <td class="text-end">${actionMenu(scope, r)}</td>`;
      return tr;
    }

    // bin
    tr.innerHTML = `
      <td>
        <div class="fw-semibold">${esc(r.title || '-')}</div>
        <div class="small text-muted">${esc((short || '').slice(0,100))}${short && short.length>100 ? '…' : ''}</div>
      </td>
      <td>${esc((short || '').slice(0,140))}${short && short.length>140 ? '…' : ''}</td>
      <td>${delAt}</td>
      <td class="text-end">${actionMenu(scope, r)}</td>`;
    return tr;
  }

  function getDragAfterElement(container, y){
    const rows=[...container.querySelectorAll('tr.reorderable:not(.dragging)')];
    return rows.reduce((closest, child)=>{
      const box=child.getBoundingClientRect();
      const offset=y - box.top - box.height/2;
      if(offset<0 && offset>closest.offset){ return {offset, element:child}; }
      else return closest;
    }, {offset:Number.NEGATIVE_INFINITY}).element;
  }

  /* ========== Loaders ========== */
  function load(scope){
    if(!currentCourseId){ return; }
    const refs=tabs[scope], rowsEl=qs(refs.rows), empty=qs(refs.empty), ask=qs(refs.ask), pager=qs(refs.pager), meta=qs(refs.meta);
    show(ask,false); clearBody(scope); show(empty,false); pager.innerHTML=''; meta.textContent='—'; showLoader(scope,true);

    fetch(urlFor(scope), {headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json','Cache-Control':'no-cache'}})
    .then(r=>r.json().then(j=>({ok:r.ok, j})))
    .then(({ok,j})=>{
      if(!ok) throw new Error(j?.message||'Load failed');
      const items=j?.data || [];
      const pag  = j?.pagination || j?.meta || {page:1, per_page:20, total:items.length};

      if(items.length===0) show(empty, true);

      const frag=document.createDocumentFragment();
      items.forEach(r=> frag.appendChild(rowHTML(scope, r)));
      rowsEl.appendChild(frag);

      // Pager
      const total=Number(pag.total||0), per=Number(pag.per_page||20), cur=Number(pag.page||1);
      const pages=Math.max(1, Math.ceil(total/per));
      const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
      let html=''; html+=li(cur<=1,false,'Previous',cur-1);
      const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
      if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
      for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
      if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
      html+=li(cur>=pages,false,'Next',cur+1);
      pager.innerHTML=html;
      pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{ 
        const t=Number(a.dataset.page); if(!t || t===state[scope].page) return; state[scope].page = Math.max(1,t); load(scope);
        window.scrollTo({top:0,behavior:'smooth'});
      }));

      meta.textContent = `Showing page ${cur} of ${pages} — ${total} result(s)`;
    })
    .catch(e=>{
      console.error(e); show(empty,true); qs(tabs[scope].meta).textContent='Failed to load'; err(e.message||'Load error');
    })
    .finally(()=> showLoader(scope,false));
  }

  /* ========== Sorting (active) ========== */
  document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col=th.dataset.col;
      if(sort===col) sort='-'+col;
      else if(sort==='-'+col) sort=col;
      else sort=(col==='created_at')?'-created_at':col;
      state.active.page=1; syncSortHeaders(); load('active');
    });
  });

  /* ========== Filters (active) ========== */
  let srT;
  q.addEventListener('input', ()=>{ clearTimeout(srT); srT=setTimeout(()=>{ state.active.page=1; load('active'); }, 350); });

  // Reset: clear search and modal filters and reload
  btnReset.addEventListener('click', ()=>{
    q.value='';
    if(modalStatus) modalStatus.value = '';
    if(modalPerPage) modalPerPage.value = '20';
    if(modalSort) modalSort.value = '-created_at';
    if(perPageSel) perPageSel.value='20';
    sort='-created_at';
    state.active.page=1;
    syncSortHeaders();
    load('active');
  });

  perPageSel.addEventListener('change', ()=>{ state.active.page=1; load('active'); });

  /* ========== Filter modal init & handlers ======== */
  (function initFilterModal(){
    // open filter modal
    btnFilterOpen.addEventListener('click', ()=>{
      const instance = bootstrap.Modal.getOrCreateInstance(filterModalEl);
      instance.show();
    });

    // sync modal fields when shown
    filterModalEl.addEventListener('show.bs.modal', ()=>{
      try{
        // if modal already has values (user typed) preserve; else reflect current state
        modalStatus.value = modalStatus.value || '';
        modalPerPage.value = modalPerPage.value || (perPageSel?.value || '20');
        modalSort.value = modalSort.value || sort || '-created_at';
      }catch(e){}
    });

    // apply button: hide modal then load
    if(btnApplyFilters){
      btnApplyFilters.addEventListener('click', ()=>{
        // apply modal sort to global sort variable for header sync
        sort = modalSort.value || sort;

        // sync per-page: update top perPageSel so paging shows selected value
        if(modalPerPage && perPageSel) perPageSel.value = modalPerPage.value || perPageSel.value;

        state.active.page = 1;
        const instance = bootstrap.Modal.getInstance(filterModalEl) || new bootstrap.Modal(filterModalEl);
        instance.hide();

        const onHidden = () => {
          filterModalEl.removeEventListener('hidden.bs.modal', onHidden);
          syncSortHeaders();
          load('active');
        };
        filterModalEl.addEventListener('hidden.bs.modal', onHidden);
      });
    }

    // clear modal inputs
    if(btnClearFilters){
      btnClearFilters.addEventListener('click', ()=>{
        if(modalStatus) modalStatus.value = '';
        if(modalPerPage) modalPerPage.value = '20';
        if(modalSort) modalSort.value = '-created_at';
        sort = '-created_at';
        syncSortHeaders();
      });
    }
  })();

  /* ========== Tab on-demand loads ========= */
  document.querySelector('a[href="#tab-active"]').addEventListener('shown.bs.tab', ()=>{ if(currentCourseId) load('active'); });
  document.querySelector('a[href="#tab-archived"]').addEventListener('shown.bs.tab', ()=>{ if(currentCourseId) load('archived'); });
  document.querySelector('a[href="#tab-bin"]').addEventListener('shown.bs.tab', ()=>{ if(currentCourseId) load('bin'); });

  /* ========== Create / Edit ========= */
  btnCreate.addEventListener('click', ()=>{ if(!currentCourseId) return Swal.fire('Pick a course','Please select a course first.','info'); openCreate(); });

  function openCreate(){
    mm.mode.value='create'; mm.key.value=''; mm.title.textContent='Create Module';
    mm.cLabel.value = courseSel.options[courseSel.selectedIndex]?.text || '';
    mm.cId.value    = currentCourseId;
    mm.status.value = 'draft';
    mm.order.value  = '0';
    mm.ttl.value    = '';
    mm.short.value  = '';
    // clear hidden textarea and RTE
    if(mm.long) mm.long.value = '';
    if(mmRte) mmRte.innerHTML = '';
    mm.meta.value   = '';
    mm.modal.show();
    setTimeout(()=> { mmRte && mmRte.focus && mmRte.focus(); }, 150);
  }

  async function openEdit(key){
    try{
      const res=await fetch(`/api/course-modules/${encodeURIComponent(key)}`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
      const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Load failed');
      const r=j.data||{};
      mm.mode.value='edit'; mm.key.value=(r.uuid || r.id); mm.title.textContent='Edit Module';
      mm.cLabel.value = courseSel.options[courseSel.selectedIndex]?.text || '';
      mm.cId.value    = r.course_id || currentCourseId;
      mm.status.value = (r.status || 'draft');
      mm.order.value  = (r.order_no ?? 0);
      mm.ttl.value    = r.title || '';
      mm.short.value  = r.short_description || '';
      // server value -> hidden textarea and RTE
      mm.long.value   = r.long_description || '';
      mm.meta.value   = r.metadata || '';
      // set RTE content after a short tick so modal has rendered
      setTimeout(()=>{ if(mmRte) mmRte.innerHTML = mm.long.value || ''; }, 60);
      mm.modal.show();
    }catch(e){ err(e.message||'Failed to open'); }
  }

  /* ========== Collect mm.long from RTE (sanitized if DOMPurify present) ========== */
  function collectMmLong(){
    if(!mm.long) return;
    const raw = (mmRte ? mmRte.innerHTML : '') || '';
    if(window.DOMPurify){
      mm.long.value = DOMPurify.sanitize(raw);
    } else {
      mm.long.value = raw;
    }
  }

  /* Hook save: collect RTE HTML before building payload */
  mm.save.addEventListener('click', async ()=>{
    // collect editor contents into hidden textarea
    collectMmLong();

    if(!mm.ttl.value.trim()) return Swal.fire('Title required','Please enter a module title.','info');
    if(!mm.cId.value) return Swal.fire('Course missing','Pick a course from the toolbar.','info');

    // validate JSON if provided
    let metaVal = mm.meta.value.trim();
    if(metaVal){
      try{ JSON.parse(metaVal); }catch{ return Swal.fire('Invalid JSON','Please provide valid JSON in Metadata.','info'); }
    }

    const payload = {
      course_id: Number(mm.cId.value),
      title: mm.ttl.value.trim(),
      short_description: mm.short.value.trim() || null,
      long_description:  mm.long.value.trim() || null,
      order_no: Number(mm.order.value||0),
      status: mm.status.value,
      metadata: metaVal || null,
    };

    const isEdit = (mm.mode.value==='edit' && mm.key.value);
    const url    = isEdit ? `/api/course-modules/${encodeURIComponent(mm.key.value)}` : '/api/course-modules';
    const method = isEdit ? 'PATCH' : 'POST';

    mmSaveLoading(true);
try{
  const res=await fetch(url,{method,headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload)});
  const j=await res.json().catch(()=>({}));
  if(!res.ok) throw new Error(j?.message||'Save failed');
  ok('Module saved');
  mm.modal.hide();
  load('active');
}catch(e){ err(e.message||'Save failed'); }
finally{ mmSaveLoading(false); }

  });

  /* ========== Row actions ========= */
  document.addEventListener('click', async (e)=>{
    const it = e.target.closest('.dropdown-item[data-act]');
    if(!it) return;
    const act=it.dataset.act, key=it.dataset.key, name=it.dataset.name || 'this module';

    if(act==='edit'){ openEdit(key); return; }

    if(act==='archive'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Archive module?',html:`“${esc(name)}”`,showCancelButton:true,confirmButtonText:'Archive',confirmButtonColor:'#8b5cf6'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/course-modules/${encodeURIComponent(key)}/archive`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Archive failed');
        ok('Module archived'); load('active');
      }catch(e){ err(e.message||'Archive failed'); }
      return;
    }

    if(act==='unarchive'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Unarchive module?',html:`“${esc(name)}”`,showCancelButton:true,confirmButtonText:'Unarchive',confirmButtonColor:'#10b981'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/course-modules/${encodeURIComponent(key)}/unarchive`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Unarchive failed');
        ok('Module unarchived'); load('archived'); load('active');
      }catch(e){ err(e.message||'Unarchive failed'); }
      return;
    }

    if(act==='delete'){
      const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete (soft)?',html:`This moves “${esc(name)}” to Bin.`,showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/course-modules/${encodeURIComponent(key)}`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Delete failed');
        ok('Moved to Bin'); load('active');
      }catch(e){ err(e.message||'Delete failed'); }
      return;
    }

    if(act==='restore'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Restore module?',html:`“${esc(name)}” will be restored.`,showCancelButton:true,confirmButtonText:'Restore',confirmButtonColor:'#0ea5e9'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/course-modules/${encodeURIComponent(key)}/restore`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Restore failed');
        ok('Module restored'); load('bin'); load('active');
      }catch(e){ err(e.message||'Restore failed'); }
      return;
    }

    if(act==='force'){
      const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete permanently?',html:`This cannot be undone.<br>“${esc(name)}”`,showCancelButton:true,confirmButtonText:'Delete permanently',confirmButtonColor:'#dc2626'});
      if(!isConfirmed) return;
      try{
        const res=await fetch(`/api/course-modules/${encodeURIComponent(key)}/force`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
        const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||'Force delete failed');
        ok('Permanently deleted'); load('bin');
      }catch(e){ err(e.message||'Force delete failed'); }
      return;
    }
  });

  /* ========== Reorder mode ========= */
  btnReorder.addEventListener('click', ()=>{
    if(!currentCourseId) return Swal.fire('Pick a course','Please select a course first.','info');
    reorderMode = !reorderMode;
    btnReorder.classList.toggle('btn-primary', reorderMode);
    btnReorder.classList.toggle('btn-light', !reorderMode);
    btnReorder.innerHTML = reorderMode ? '<i class="fa fa-check-double me-1"></i>Reorder On' : '<i class="fa fa-up-down-left-right me-1"></i>Reorder';
    document.getElementById('reorderHint').style.display = reorderMode ? '' : 'none';
    document.getElementById('btnSaveOrder').style.display = reorderMode ? '' : 'none';
    document.getElementById('btnCancelOrder').style.display = reorderMode ? '' : 'none';
    load('active');
  });

  document.getElementById('btnCancelOrder').addEventListener('click', ()=>{
    reorderMode=false;
    btnReorder.classList.remove('btn-primary'); btnReorder.classList.add('btn-light');
    btnReorder.innerHTML='<i class="fa fa-up-down-left-right me-1"></i>Reorder';
    document.getElementById('reorderHint').style.display='none';
    document.getElementById('btnSaveOrder').style.display='none';
    document.getElementById('btnCancelOrder').style.display='none';
    load('active');
  });

  document.getElementById('btnSaveOrder').addEventListener('click', async ()=>{
    const rows = [...document.querySelectorAll('#rows-active tr.reorderable')];
    const ids  = rows.map(tr => Number(tr.dataset.id)).filter(Number.isInteger);
    if (!ids.length) return Swal.fire('Nothing to save','Drag rows to change order first.','info');

    const payload = { course_id: Number(currentCourseId), ids };
    try{
      const res = await fetch('/api/course-modules/reorder', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type' : 'application/json', 'Accept' : 'application/json' },
        body: JSON.stringify(payload)
      });
      const j = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(j?.message || 'Reorder failed');
      ok('Order updated');
      document.getElementById('btnCancelOrder').click();
    }catch(e){ err(e.message || 'Reorder failed'); }
  });

  /* ========== RTE for mm_long_editor: toolbar, placeholder, paste handling ======= */
  (function initMmRte(){
    if(!mmRte || !mm.long) return;

    // placeholder toggle
    const togglePlaceholder = ()=>{
      try{
        const has = (mmRte.textContent||'').trim().length>0 || (mmRte.innerHTML||'').trim().length>0;
        mmRte.classList.toggle('has-content', has);
      }catch(e){}
    };
    ['input','keyup','paste','blur'].forEach(ev=> mmRte.addEventListener(ev, togglePlaceholder));
    togglePlaceholder();

    // mutation observer
    try{
      const mo = new MutationObserver(togglePlaceholder);
      mo.observe(mmRte, { childList:true, subtree:true, characterData:true });
    }catch(e){}

    // toolbar click handling
    if(mmToolbar){
      mmToolbar.addEventListener('click', (e)=>{
        const btn = e.target.closest('[data-cmd]');
        if(!btn) return;
        const cmd = btn.getAttribute('data-cmd');
        if(cmd === 'createLink'){
          let url = prompt('Enter URL (including https://):','https://');
          if(!url) return;
          if(!/^https?:\/\//i.test(url)){ alert('Please include http:// or https://'); return; }
          try{ document.execCommand('createLink', false, url); } catch(e){ console.warn(e); }
          mmRte.focus(); return;
        }
        try{ document.execCommand(cmd, false, null);}catch(e){ console.warn('execCommand failed', e); }
        mmRte.focus();
      });
    }

    if(mmHeading){
      mmHeading.addEventListener('change', function(){
        const v=this.value;
        if(!v) return;
        if(v==='h2') document.execCommand('formatBlock', false, 'h2');
        else if(v==='p') document.execCommand('formatBlock', false, 'p');
        this.value='';
        mmRte.focus();
      });
    }

    // paste handler: prefer plain text
    mmRte.addEventListener('paste', function(e){
      e.preventDefault();
      const clipboard = (e.clipboardData || window.clipboardData);
      const html = clipboard.getData('text/html');
      const text = clipboard.getData('text/plain') || '';
      if(html && window.DOMPurify){
        // if DOMPurify available, sanitize clipboard html and insert
        const clean = DOMPurify.sanitize(html, {ALLOWED_TAGS: ['b','i','u','a','p','h2','ul','ol','li','br','strong','em','img'], ALLOWED_ATTR: ['href','src','alt','title']});
        document.execCommand('insertHTML', false, clean);
      } else {
        // fallback: plain text
        if(document.queryCommandSupported && document.queryCommandSupported('insertText')){
          document.execCommand('insertText', false, text);
        } else {
          const node = document.createTextNode(text);
          const sel = window.getSelection();
          if(!sel.rangeCount) mmRte.appendChild(node);
          else sel.getRangeAt(0).insertNode(node);
        }
      }
      togglePlaceholder();
    });

    // when modal opens, if hidden textarea has content, populate editor
    document.getElementById('moduleModal')?.addEventListener('shown.bs.modal', ()=> {
      if((mmRte.innerHTML||'').trim()==='' && (mm.long.value||'').trim()!==''){
        mmRte.innerHTML = mm.long.value;
      }
      togglePlaceholder();
    });

  })();

  /* ========== Initial setup ========= */
  syncSortHeaders();

})();
</script>
@endpush