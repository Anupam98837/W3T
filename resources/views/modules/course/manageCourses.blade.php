{{-- resources/views/modules/courses/manageCourses.blade.php --}}
@section('title','Manage Courses')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/extraStylings.css') }}"/>

<style>
/* ===== Shell ===== */
.crs-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
/* .panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px} */

/* Toolbar */
/* .mfa-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .btn{height:40px;border-radius:12px}
.mfa-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
.mfa-toolbar .btn-primary{background:var(--primary-color);border:none} */

/* Tabs */
/* .nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}
.tab-content,.tab-pane{overflow:visible} */

/* Table Card */
/* .table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible}
.table-wrap .card-body{overflow:visible}
.table-responsive{overflow:visible !important}
.table{--bs-table-bg:transparent}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)} */
/* td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px} */

/* Badges (stronger specificity so they don't go white) */
.table .badge.badge-success{background:var(--success-color) !important;color:#fff !important}
.table .badge.badge-warning{background:var(--warning-color) !important;color:#0b1324 !important}
.table .badge.badge-secondary{background:#64748b !important;color:#fff !important}
.badge-soft{background:color-mix(in oklab, var(--muted-color) 12%, transparent);color:var(--ink)}

/* Pills / sorting */
.level-pill{display:inline-block;padding:.22rem .5rem;border-radius:999px;border:1px solid var(--line-strong);font-size:.8rem;background:var(--surface-2, var(--surface))}
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Row visual cues */
tr.is-archived{opacity:.92}
tr.is-archived td{background:color-mix(in oklab, var(--muted-color) 6%, transparent)}
tr.is-draft td{background:color-mix(in oklab, var(--warning-color) 4%, transparent)}
tr.is-deleted td{background:color-mix(in oklab, var(--danger-color) 6%, transparent)}

/* Dropdowns inside table */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
/* Default dropdown menu (when not portaled) */
.table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:5000}
/* Portaled dropdown menu (moved to body) */
.dropdown-menu.dd-portal{position:fixed!important;left:0;top:0;transform:none!important;z-index:5000;border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;background:var(--surface)}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color)!important}

/* Ensure pointer for View Course */
.view-course-link,
.dropdown-item { cursor: pointer; }

/* Empty & loader */
.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg, #00000010, #00000005, #00000010);border-radius:8px}

/* Modals — match look across both modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control, .form-select{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control, html.theme-dark .form-select{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}
.modal-title i{opacity:.9}

/* ===== Featured Media modal — polished to mirror Create Module modal ===== */
.media-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px}
.media-head .meta .title{font-weight:700;color:var(--ink);font-family:var(--font-head);line-height:1.2}
.media-head .meta .sub{color:var(--muted-color);font-size:13px}

/* Section labels inside modal for consistency */
.modal .section-label{font-weight:600;color:var(--ink);margin-top:6px}

/* Dropzone */
.dropzone{
  border:1.5px dashed var(--line-strong);
  border-radius:14px;
  padding:18px;
  text-align:center;
  background:var(--surface-2, #fff);
  transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
}
.dropzone.drag{background:color-mix(in oklab, var(--accent-color) 10%, transparent); border-color:var(--accent-color); box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 18%, transparent)}
.dropzone .hint{color:var(--muted-color);font-size:13px}

/* Media list */
.media-list{margin-top:8px}
.media-item{
  display:grid;grid-template-columns:28px 1fr auto;align-items:center;gap:10px;
  border:1px solid var(--line-strong);border-radius:12px;background:var(--surface-2, #fff);
  padding:10px 12px;margin-bottom:8px
}
.media-item .handle{cursor:grab;opacity:.7}
.media-item.dragging{opacity:.5}
.media-item .url{font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:52vw}
.media-item .kind{font-size:12px;color:var(--muted-color)}
.media-item .btn-icon{border:none;background:transparent;padding:.25rem .4rem;color:#6b7280}
.media-item .btn-icon:hover{color:var(--ink)}
.media-item .icon{
  width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;
  border:1px solid var(--line-strong);background:#fff
}

/* Dark tweaks */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .dropzone{background:#0b1020;border-color:var(--line-strong)}
html.theme-dark .media-item{background:#0b1020;border-color:var(--line-strong)}

/* Dropdown visibility safety nets */
.table-wrap, .table-wrap .card-body, .table-responsive { overflow: visible !important; }
.table-wrap .dropdown { position: relative; }
.table-wrap .dropdown-menu { z-index: 2050; }

/* File button look */
.btn-light{background:var(--surface);border:1px solid var(--line-strong)}

.table-wrap, .table-wrap .card-body, .table-responsive {
    overflow: auto !important;
}
 /* Featured media modal – card grid */
  #mediaModal .media-list {
    margin-top: 8px;
  }

  #mediaModal .media-list.highlight {
    box-shadow: 0 0 0 1px #bfdbfe;
    border-radius: 14px;
    padding: 4px;
    animation: mediaLibFlash .9s ease-out 1;
  }
  @keyframes mediaLibFlash {
    0%   { box-shadow: 0 0 0 0 rgba(59,130,246,0.7); }
    100% { box-shadow: 0 0 0 1px rgba(59,130,246,0.4); }
  }

  #mediaModal .media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }

  #mediaModal .media-card {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    cursor: grab;
    transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
  }
  #mediaModal .media-card:active {
    cursor: grabbing;
  }
  #mediaModal .media-card:hover {
    box-shadow: 0 10px 18px rgba(15,23,42,0.08);
    transform: translateY(-1px);
    border-color: #60a5fa;
  }
  #mediaModal .media-card.dragging {
    opacity: .8;
    box-shadow: 0 0 0 1px #60a5fa;
  }

  #mediaModal .media-card .card-thumb {
    position: relative;
    width: 100%;
    padding-top: 62%;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
  }
  #mediaModal .media-card .card-thumb a {
    position: absolute;
    inset: 0;
    display: block;
  }
  #mediaModal .media-card .card-thumb img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  #mediaModal .media-card .card-thumb .icon-center {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    opacity: .75;
  }

  #mediaModal .media-card .card-body {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  #mediaModal .media-card .card-body .name {
    font-size: .82rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #mediaModal .media-card .card-body .meta {
    font-size: .75rem;
    color: #6b7280;
  }

  #mediaModal .media-card .card-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
  }
  #mediaModal .media-card .btn-icon {
    border: none;
    background: transparent;
    padding: 4px;
    border-radius: 999px;
  }
  #mediaModal .media-card .btn-icon:hover {
    background: #fee2e2;
    color: #b91c1c;
  }
</style>
@endpush


@section('content')
<div class="crs-wrap">

  {{-- ================= Tabs ================= --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-courses" role="tab" aria-selected="true"><i class="fa-solid fa-layer-group me-2"></i>Courses</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-archived" role="tab" aria-selected="false"><i class="fa-solid fa-folder me-2"></i>Archived</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-draft" role="tab" aria-selected="false"><i class="fa-solid fa-file-lines me-2"></i>Draft</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-bin" role="tab" aria-selected="false"><i class="fa-solid fa-trash me-2"></i>Bin</a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ========== TAB: Courses (active list) ========== --}}
    <div class="tab-pane fade show active" id="tab-courses" role="tabpanel">
      {{-- ================= Toolbar ================= --}}
      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;">
              <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:300px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search by title or slug…">
            <i class="fa fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
          </div>

          {{-- Filter Button --}}
          <button id="btnFilter" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fa fa-filter me-1"></i>Filter
          </button>

          <button id="btnReset" class="btn btn-primary"><i class="fa fa-rotate-left me-1"></i>Reset</button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <a id="btnCreate" href="/admin/courses/create" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i>New Course
          </a>
        </div>
      </div>

      {{-- ================= Table ================= --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top" style="z-index:2;">
                <tr>
                  <th class="sortable" data-col="title">COURSE <span class="caret"></span></th>
                  <th class="sortable" data-col="course_type" style="width:120px;">TYPE <span class="caret"></span></th>
                  <th style="width:200px;">PRICE / FINAL</th>
                  <th class="sortable" data-col="status" style="width:130px;">STATUS <span class="caret"></span></th>
                  <th style="width:120px;">LEVEL</th>
                  <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
                  <th class="text-end" style="width:108px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-courses">
                <tr id="loaderRow-courses" style="display:none;">
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
              </tbody>
            </table>
          </div>

          {{-- Empty state --}}
          <div id="empty-courses" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No courses found for current filters.</div>
          </div>

          {{-- Footer: pagination --}}
          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-courses">—</div>
            <nav><ul id="pager-courses" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== TAB: Archived ========== --}}
    <div class="tab-pane fade" id="tab-archived" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>COURSE</th>
                  <th style="width:120px;">TYPE</th>
                  <th style="width:200px;">PRICE / FINAL</th>
                  {{-- STATUS intentionally hidden for archived --}}
                  <th style="width:120px;">LEVEL</th>
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:108px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-archived">
                <tr id="loaderRow-archived" style="display:none;">
                  <td colspan="6" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
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

          <div id="empty-archived" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-box-archive mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No archived courses.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-archived">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-archived" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== TAB: Draft ========== --}}
    <div class="tab-pane fade" id="tab-draft" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>COURSE</th>
                  <th style="width:120px;">TYPE</th>
                  <th style="width:200px;">PRICE / FINAL</th>
                  {{-- STATUS intentionally hidden for draft --}}
                  <th style="width:120px;">LEVEL</th>
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:108px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-draft">
                <tr id="loaderRow-draft" style="display:none;">
                  <td colspan="6" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
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

          <div id="empty-draft" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-file-lines mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No draft courses.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-draft">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-draft" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== TAB: Bin (Deleted) ========== --}}
    <div class="tab-pane fade" id="tab-bin" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>COURSE</th>
                  <th style="width:120px;">TYPE</th>
                  <th style="width:200px;">PRICE / FINAL</th>
                  <th style="width:120px;">LEVEL</th>
                  <th style="width:170px;">DELETED AT</th>
                  <th class="text-end" style="width:160px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-bin">
                <tr id="loaderRow-bin" style="display:none;">
                  <td colspan="6" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
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

          <div id="empty-bin" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash mb-2" style="font-size:32px; opacity:.6;"></i>
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

{{-- ================= Filter Courses Modal ================= --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filter Courses</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          {{-- Course Type --}}
          <div class="col-12">
            <label class="form-label">Course Type</label>
            <select id="modal_course_type" class="form-select">
              <option value="">All Types</option>
              <option value="paid">Paid</option>
              <option value="free">Free</option>
            </select>
          </div>

          {{-- Status --}}
          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">All Status</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          {{-- Level --}}
          <div class="col-12">
            <label class="form-label">Level</label>
            <select id="modal_level" class="form-select">
              <option value="">All Levels</option>
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
            </select>
          </div>

          {{-- Sort By --}}
          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="course_type">Type A-Z</option>
              <option value="-course_type">Type Z-A</option>
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

{{-- ================= Create Course Module (modal) ================= --}}
<div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-layer-group me-2"></i>Create Course Module</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 small text-muted" id="modCourseInfo">—</div>

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input id="mod_title" type="text" class="form-control" maxlength="255" placeholder="e.g., Introduction to Web Development">
          </div>
          <div class="col-md-4">
            <label class="form-label">Order No.</label>
            <input id="mod_order" type="number" min="0" class="form-control" placeholder="1">
          </div>
          <div class="col-12">
            <label class="form-label">Short Description</label>
            <input id="mod_short" type="text" class="form-control" placeholder="Brief summary">
          </div>
          <div class="col-12">
            <label class="form-label">Long Description</label>
            <textarea id="mod_long" class="form-control" rows="6" placeholder="Detailed module content…"></textarea>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Status</label>
            <select id="mod_status" class="form-select">
              <option value="draft">Draft</option>
              <option value="published" selected>Published</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="btnSaveModule" class="btn btn-primary">
          <i class="fa fa-save me-1"></i>Save Module
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= Featured Media (modal) ================= --}}
<div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-images me-2"></i>Course Featured Media</h5>
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
              <div class="mt-2">
                <label for="mediaFiles" class="btn btn-light me-2">
                  <i class="fa fa-file-arrow-up me-1"></i>Choose Files
                </label>
                <input id="mediaFiles" type="file" class="d-none" multiple accept="image/*,video/*,audio/*,application/pdf">
                <button id="btnAddUrl" class="btn btn-light" type="button"><i class="fa fa-link me-1"></i>Add via URL</button>
              </div>
            </div>
          </div>

          <div class="col-12" id="urlRow" style="display:none;">
            <label class="form-label section-label">Add via URL</label>
            <div class="row g-2 align-items-center">
              <div class="col">
                <input id="urlInput" type="url" class="form-control" placeholder="https://example.com/image.jpg">
              </div>
              <div class="col-auto">
                <button id="btnSaveUrl" class="btn btn-primary" type="button"><i class="fa fa-plus me-1"></i>Add</button>
              </div>
              <div class="col-12 small text-muted mt-1">Paste a direct link to an image/video/audio/PDF.</div>
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
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
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
/* ===== Force dropdown overflows to body (portal) ===== */
(function(){
  let activePortal = null;
  const placeMenu = (menu, btnRect) => {
    const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    const spaceRight = vw - btnRect.right;
    menu.classList.add('dd-portal');
    menu.style.display = 'block';
    menu.style.visibility = 'hidden'; // measure first
    document.body.appendChild(menu);

    // compute size after in body
    const mw = menu.offsetWidth, mh = menu.offsetHeight;
    let left = btnRect.left;
    if (spaceRight < mw && btnRect.right - mw > 8) {
      left = btnRect.right - mw; // flip to align right if not enough space
    }
    let top = btnRect.bottom + 4; // little offset below button
    // Keep within viewport vertically
    const vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    if (top + mh > vh - 8) top = Math.max(8, vh - mh - 8);

    menu.style.left = left + 'px';
    menu.style.top  = top + 'px';
    menu.style.visibility = 'visible';
  };

  document.addEventListener('show.bs.dropdown', function(ev){
    const toggle = ev.target; // .dropdown
    const btn = toggle.querySelector('.dd-toggle, [data-bs-toggle="dropdown"]');
    const menu = toggle.querySelector('.dropdown-menu');
    if (!btn || !menu) return;

    // clean any previous
    if (activePortal && activePortal.menu && activePortal.menu.isConnected) {
      activePortal.menu.classList.remove('dd-portal');
      activePortal.parent.appendChild(activePortal.menu);
      activePortal = null;
    }

    const rect = btn.getBoundingClientRect();
    // Remember original parent to restore on hide
    menu.__ddParent = menu.parentElement;
    placeMenu(menu, rect);
    activePortal = { menu: menu, parent: menu.__ddParent };

    // Close on scroll/resize to avoid stale position
    const closeOnEnv = () => {
      try { bootstrap.Dropdown.getOrCreateInstance(btn).hide(); } catch {}
    };
    menu.__ddListeners = [
      ['scroll', closeOnEnv, true],
      ['resize', closeOnEnv, false]
    ];
    window.addEventListener('resize', closeOnEnv);
    document.addEventListener('scroll', closeOnEnv, true);
  });

  document.addEventListener('hidden.bs.dropdown', function(ev){
    const toggle = ev.target;
    const menu = toggle.querySelector('.dropdown-menu.dd-portal') || activePortal?.menu;
    if (!menu) return;

    // remove listeners
    if (menu.__ddListeners) {
      document.removeEventListener('scroll', menu.__ddListeners[0][1], true);
      window.removeEventListener('resize', menu.__ddListeners[1][1]);
      menu.__ddListeners = null;
    }

    // restore to original parent
    if (menu.__ddParent) {
      menu.classList.remove('dd-portal');
      menu.style.cssText = ''; // reset inline styles
      menu.__ddParent.appendChild(menu);
      activePortal = null;
    }
  });
})();

/* ================= Dropdown toggle handler ================= */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;
  e.preventDefault(); e.stopPropagation();
  const inst = bootstrap.Dropdown.getOrCreateInstance(btn, { autoClose:'outside', boundary:'viewport' });
  inst.toggle();
});

(function(){
  /* ========= Globals ========= */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const ROLE  = (localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase();
  const basePanel = (ROLE.includes('super') ? '/super_admin' : '/admin');

  if (!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }
  document.getElementById('btnCreate').setAttribute('href', basePanel + '/courses/create');

  const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
  const errToast = new bootstrap.Toast(document.getElementById('errToast'));
  const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  /* ========= DOM refs per tab ========= */
  const tabs = {
    courses:  { rows:'#rows-courses',  loader:'#loaderRow-courses',  empty:'#empty-courses',  meta:'#metaTxt-courses',  pager:'#pager-courses'  },
    archived: { rows:'#rows-archived', loader:'#loaderRow-archived', empty:'#empty-archived', meta:'#metaTxt-archived', pager:'#pager-archived' },
    draft:    { rows:'#rows-draft',    loader:'#loaderRow-draft',    empty:'#empty-draft',    meta:'#metaTxt-draft',    pager:'#pager-draft'    },
    bin:      { rows:'#rows-bin',      loader:'#loaderRow-bin',      empty:'#empty-bin',      meta:'#metaTxt-bin',      pager:'#pager-bin'      },
  };

  /* ========= Shared filter elements (Courses tab) ========= */
  const q           = document.getElementById('q');
  const perPageSel  = document.getElementById('per_page');
  const btnReset    = document.getElementById('btnReset');

  /* ========= State ========= */
  let sort = '-created_at';
  const state = { courses:{page:1}, archived:{page:1}, draft:{page:1}, bin:{page:1} };
  let currentCourse = null;  // {id, uuid, title, short}

  /* ========= Utils ========= */
  function decodeHtml(s){
    const t = document.createElement('textarea');
    t.innerHTML = s == null ? '' : String(s);
    return t.value;
  }

  function escapeHtml(s){
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;','`':'&#96;'};
    return (s==null ? '' : String(s)).replace(/[&<>"'`]/g, ch => map[ch]);
  }

  function fmtMoney(v, cur){
    const n = Number(v || 0);
    const sym = (cur||'INR').toUpperCase()==='INR' ? '₹' : (cur||'').toUpperCase()+' ';
    return sym + n.toFixed(2);
  }

  function fmtDate(iso){
    if(!iso) return '-';
    const d=new Date(iso); if (isNaN(d)) return escapeHtml(iso);
    return d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
  }

  function badgeStatus(s){
    const map={draft:'warning',published:'success',archived:'secondary'};
    const cls=map[s]||'secondary';
    return `<span class="badge badge-${cls} text-uppercase">${escapeHtml(s)}</span>`;
  }

  function getToken(){ return TOKEN; }

  function showLoader(scope, v){ 
    const loader = document.querySelector(tabs[scope].loader);
    if (loader) loader.style.display = v ? '' : 'none'; 
  }

  function queryParams(scope){
    const params = new URLSearchParams();
    const p = state[scope].page || 1;
    const pp = Number(perPageSel?.value || 20);
    params.set('per_page', pp);
    params.set('page', p);
    params.set('sort', sort);

    if (scope === 'courses'){
      if (q.value.trim()) params.set('q', q.value.trim());
      
      // Get filter values from modal
      const typeFilter = document.getElementById('modal_course_type')?.value;
      if (typeFilter) params.set('course_type', typeFilter);
      
      const statusFilter = document.getElementById('modal_status')?.value;
      if (statusFilter) params.set('status', statusFilter);
      
      const levelFilter = document.getElementById('modal_level')?.value;
      if (levelFilter) params.set('level', levelFilter);

      // Default status for courses tab if no specific status filter
      if (!statusFilter) {
        params.set('status', 'published');
      }
    } else if (scope === 'archived'){
      params.set('status', 'archived');
    } else if (scope === 'draft'){
      params.set('status', 'draft');
    } else if (scope === 'bin'){
      params.set('only_deleted', '1');
    }
    return params.toString();
  }

  function pushURL(scope){ 
    history.replaceState(null,'', location.pathname + '?' + queryParams(scope)); 
  }

  function applyFromURL(){
    const url=new URL(location.href);
    const g=(k)=>url.searchParams.get(k)||'';
    if (g('q')) q.value=g('q');
    if (g('course_type')) {
      document.getElementById('modal_course_type').value = g('course_type');
    }
    if (g('status')) document.getElementById('modal_status').value = g('status');
    if (g('level')) document.getElementById('modal_level').value = g('level');
    if (g('per_page')) perPageSel.value=g('per_page');
    if (g('page')) state.courses.page=Number(g('page'))||1;
    if (g('sort')) {
      sort=g('sort');
      document.getElementById('modal_sort').value = g('sort');
    }
    syncSortHeaders();
  }

  function syncSortHeaders(){
    document.querySelectorAll('#tab-courses th.sortable').forEach(th=>{
      th.classList.remove('asc','desc');
      const col = th.dataset.col;
      if (sort === col) th.classList.add('asc');
      if (sort === '-'+col) th.classList.add('desc');
    });
  }

  /* ========= Row Actions ========= */
  function rowActions(scope, r){
    const isArchived = String(r.status||'').toLowerCase() === 'archived';
    const isDraft = String(r.status||'').toLowerCase() === 'draft';
    const isDeleted = !!r.deleted_at;

    if (scope === 'bin') {
      return `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" data-act="restore" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}">
              <i class="fa fa-rotate-left"></i> Restore
            </button></li>
            <li><button class="dropdown-item text-danger" data-act="force" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}">
              <i class="fa fa-skull-crossbones"></i> Delete Permanently
            </button></li>
          </ul>
        </div>`;
    }

    let statusActions = '';
    if (scope === 'courses') {
      statusActions = `
        <li><button class="dropdown-item" data-act="archive" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" title="Archive this course">
             <i class="fa fa-box-archive"></i> Archive
           </button></li>`;
    } else if (scope === 'archived') {
      statusActions = `
        <li><button class="dropdown-item" data-act="unarchive" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" title="Move back to published">
             <i class="fa fa-box-open"></i> Unarchive
           </button></li>`;
    } else if (scope === 'draft') {
      statusActions = `
        <li><button class="dropdown-item" data-act="publish" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" title="Publish this course">
             <i class="fa fa-rocket"></i> Publish
           </button></li>
        <li><button class="dropdown-item" data-act="archive" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" title="Archive this course">
             <i class="fa fa-box-archive"></i> Archive
           </button></li>`;
    }

    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Actions">
          <i class="fa fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item view-course-link" href="courses/${encodeURIComponent(r.uuid)}" title="View Course">
              <i class="fa fa-eye"></i> View Course
            </a>
          </li>
          <li><button class="dropdown-item" data-act="edit" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}">
            <i class="fa fa-pen-to-square"></i> Edit
          </button></li>
          <li><button class="dropdown-item" data-act="modules" data-id="${r.id}" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" data-short="${escapeHtml(r.short_description||'')}">
            <i class="fa fa-layer-group"></i> Create Course Module
          </button></li>
          <li><button class="dropdown-item" data-act="media" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}" data-short="${escapeHtml(r.short_description||'')}">
            <i class="fa fa-images"></i> Course Featured Media
          </button></li>
          <li><hr class="dropdown-divider"></li>
          ${statusActions}
          <li><button class="dropdown-item text-danger" data-act="delete" data-uuid="${r.uuid}" data-title="${escapeHtml(r.title||'')}">
            <i class="fa fa-trash"></i> Delete
          </button></li>
        </ul>
      </div>`;
  }

  function renderRows(scope, items){
    const rowsEl = document.querySelector(tabs[scope].rows);
    rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(tr=>tr.remove());
    const frag=document.createDocumentFragment();

    items.forEach(r=>{
      const tr=document.createElement('tr');
      const isArchived = String(r.status||'').toLowerCase() === 'archived';
      const isDraft = String(r.status||'').toLowerCase() === 'draft';
      const isDeleted = !!r.deleted_at;

      if (isArchived && !isDeleted) tr.classList.add('is-archived');
      if (isDraft && !isDeleted) tr.classList.add('is-draft');
      if (isDeleted) tr.classList.add('is-deleted');

      const priceCell = (r.course_type==='paid')
          ? `${fmtMoney(r.price_amount,r.price_currency)} <span class="text-muted">→</span> <strong>${fmtMoney(r.final_price_ui ?? r.final_price ?? 0, r.price_currency)}</strong>`
          : '<span class="badge badge-success">FREE</span>';
      const level = r.level ? `<span class="level-pill">${escapeHtml(r.level)}</span>` : '-';

      let rowHTML = '';
      if (scope === 'bin') {
        rowHTML = `
          <td>
            <div class="fw-semibold">
              <span class="text-muted">${escapeHtml(r.title||'')}</span>
            </div>
            <div class="text-muted small">${escapeHtml(r.slug||'')}</div>
          </td>
          <td class="text-capitalize">${escapeHtml(r.course_type||'')}</td>
          <td>${priceCell}</td>
          <td>${level}</td>
          <td>${fmtDate(r.deleted_at)}</td>
          <td class="text-end">${rowActions(scope, r)}</td>
        `;
      } else if (scope === 'archived' || scope === 'draft') {
        rowHTML = `
          <td>
            <div class="fw-semibold">
              <a href="${basePanel}/courses/${encodeURIComponent(r.uuid)}"
                 class="link-offset-2 link-underline-opacity-0 view-course-link">${escapeHtml(r.title||'')}</a>
            </div>
            <div class="text-muted small">${escapeHtml(r.slug||'')}</div>
          </td>
          <td class="text-capitalize">${escapeHtml(r.course_type||'')}</td>
          <td>${priceCell}</td>
          <td>${level}</td>
          <td>${fmtDate(r.created_at)}</td>
          <td class="text-end">${rowActions(scope, r)}</td>
        `;
      } else {
        // courses tab
        rowHTML = `
          <td>
            <div class="fw-semibold">
              <a href="${basePanel}/courses/${encodeURIComponent(r.uuid)}"
                 class="link-offset-2 link-underline-opacity-0 view-course-link">${escapeHtml(r.title||'')}</a>
            </div>
            <div class="text-muted small">${escapeHtml(r.slug||'')}</div>
          </td>
          <td class="text-capitalize">${escapeHtml(r.course_type||'')}</td>
          <td>${priceCell}</td>
          <td>${badgeStatus(r.status)}</td>
          <td>${level}</td>
          <td>${fmtDate(r.created_at)}</td>
          <td class="text-end">${rowActions(scope, r)}</td>
        `;
      }

      tr.innerHTML = rowHTML;
      frag.appendChild(tr);
    });

    rowsEl.appendChild(frag);
  }

  function renderPager(scope, pagination){
    const pagerEl = document.querySelector(tabs[scope].pager);
    const metaTxt = document.querySelector(tabs[scope].meta);

    const total   = Number(pagination.total||0);
    const perPage = Number(pagination.per_page||20);
    const current = Number(pagination.page||1);
    const totalPages = Math.max(1, Math.ceil(total / perPage));

    function li(disabled, active, label, targetPage){
      const cls=['page-item',disabled?'disabled':'',active?'active':''].filter(Boolean).join(' ');
      const href=disabled?'#':'javascript:void(0)';
      return `<li class="${cls}"><a class="page-link" href="${href}" data-page="${targetPage||''}">${label}</a></li>`;
    }

    let html='';
    html += li(current<=1,false,'Previous',current-1);
    const w=3, start=Math.max(1,current-w), end=Math.min(totalPages,current+w);
    if (start>1){ html += li(false,false,1,1); if(start>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
    for(let p=start;p<=end;p++) html += li(false,p===current,p,p);
    if (end<totalPages){ if(end<totalPages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,totalPages,totalPages); }
    html += li(current>=totalPages,false,'Next',current+1);

    pagerEl.innerHTML=html;
    pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click',()=>{
        const target=Number(a.dataset.page); if(!target||target===state[scope].page) return;
        state[scope].page=Math.max(1,target); load(scope);
      });
    });

    metaTxt.textContent = `Showing page ${current} of ${totalPages} — ${total} result(s)`;
  }

  async function load(scope){
    showLoader(scope, true);
    const emptyEl = document.querySelector(tabs[scope].empty);
    const rowsEl = document.querySelector(tabs[scope].rows);
    
    emptyEl.style.display='none';
    rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(tr=>tr.remove());
    
    if (scope === 'courses') pushURL(scope);
    
    try{
      const res = await fetch('/api/courses?' + queryParams(scope), {
        headers:{ 'Authorization':'Bearer '+getToken(), 'Accept':'application/json' }
      });
      const json = await res.json();
      if(!res.ok) throw new Error(json?.message || 'Failed to load');
      const items = json?.data || [];
      const pagination = json?.pagination || {page:1,per_page:Number(perPageSel.value||20),total:items.length};
      
      if (items.length===0) emptyEl.style.display='';
      renderRows(scope, items);
      renderPager(scope, pagination);
    }catch(e){
      console.error(e);
      emptyEl.style.display='';
      const metaTxt = document.querySelector(tabs[scope].meta);
      metaTxt.textContent='Failed to load courses';
      err('Failed to load courses');
    }finally{
      showLoader(scope, false);
      if (scope === 'courses') syncSortHeaders();
    }
  }

  /* ========= Course Actions ========= */
  async function archiveCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'question', title:'Archive course?',
      html:`You can unarchive later.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Archive', confirmButtonColor:'#8b5cf6'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}`, {
        method:'PATCH',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ status:'archived' })
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Archive failed'); }
      ok('Course archived');
      load('courses');
      load('archived');
    }catch(e){ err(e.message); }
  }

  async function unarchiveCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'question', title:'Unarchive to Published?',
      html:`This will move the course back to Published.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Unarchive', confirmButtonColor:'#10b981'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}`, {
        method:'PATCH',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ status:'published' })
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Unarchive failed'); }
      ok('Moved to Published');
      load('archived');
      load('courses');
    }catch(e){ err(e.message); }
  }

  async function publishCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'question', title:'Publish course?',
      html:`This will make the course live.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Publish', confirmButtonColor:'#10b981'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}`, {
        method:'PATCH',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ status:'published' })
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Publish failed'); }
      ok('Course published');
      load('draft');
      load('courses');
    }catch(e){ err(e.message); }
  }

  async function deleteCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'warning', title:'Delete course?',
      html:`This will move it to Bin.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#ef4444'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}`, {
        method:'DELETE', headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Delete failed'); }
      ok('Course moved to Bin'); 
      load('courses');
      load('archived');
      load('draft');
      load('bin');
    }catch(e){ err(e.message); }
  }

  async function restoreCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'question', title:'Restore course?',
      html:`This will restore from Bin.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Restore', confirmButtonColor:'#0ea5e9'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}/restore`, {
        method:'PATCH', headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Restore failed'); }
      ok('Course restored'); 
      load('bin');
      load('courses');
    }catch(e){ err(e.message); }
  }

  async function forceDeleteCourse(uuid, title){
    const {isConfirmed} = await Swal.fire({
      icon:'warning', title:'Delete permanently?',
      html:`This cannot be undone.<br><b>${escapeHtml(title||'This course')}</b>`,
      showCancelButton:true, confirmButtonText:'Delete permanently', confirmButtonColor:'#dc2626'
    });
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(uuid)}/force`, {
        method:'DELETE', headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Permanent delete failed'); }
      ok('Course permanently deleted'); 
      load('bin');
    }catch(e){ err(e.message); }
  }

  function goEdit(uuid){
    location.href = `${basePanel}/courses/create?edit=${encodeURIComponent(uuid)}`;
  }

  // Handle dropdown actions
  document.addEventListener('click', (e) => {
    const item = e.target.closest('.dropdown-item[data-act]');
    if (!item) return;

    e.preventDefault();

    const act   = item.dataset.act;
    const uuid  = item.dataset.uuid || null;
    const id    = item.dataset.id ? Number(item.dataset.id) : null;
    const title = decodeHtml(item.dataset.title || '');
    const short = decodeHtml(item.dataset.short || '');

    if (act === 'media')        openMedia(uuid, title, short);
    else if (act === 'modules') openModules(id, uuid, title, short);
    else if (act === 'archive') archiveCourse(uuid, title);
    else if (act === 'unarchive') unarchiveCourse(uuid, title);
    else if (act === 'publish') publishCourse(uuid, title);
    else if (act === 'delete')  deleteCourse(uuid, title);
    else if (act === 'restore') restoreCourse(uuid, title);
    else if (act === 'force')   forceDeleteCourse(uuid, title);
    else if (act === 'edit')    goEdit(uuid);

    const toggle = item.closest('.dropdown')?.querySelector('.dd-toggle');
    if (toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
  });

  /* ========= Modules Modal ========= */
  function openModules(id, uuid, title, short){
    currentCourse = { id, uuid, title, short };
    document.getElementById('modCourseInfo').textContent = `${title || 'Course'} — ${short || ''}`.trim();
    document.getElementById('mod_title').value='';
    document.getElementById('mod_short').value='';
    document.getElementById('mod_long').value='';
    document.getElementById('mod_order').value='';
    document.getElementById('mod_status').value='published';

    const moduleModal = new bootstrap.Modal(document.getElementById('moduleModal'));
    moduleModal.show();
  }

  document.getElementById('btnSaveModule').addEventListener('click', async ()=>{
    const t=(document.getElementById('mod_title').value||'').trim();
    if(!t){ return Swal.fire('Title required','Please enter a module title.','info'); }
    if(!currentCourse?.id){ return err('Missing course id'); }

    const payload={
      course_id: currentCourse.id,
      title: t,
      short_description: (document.getElementById('mod_short').value||'').trim() || null,
      long_description: (document.getElementById('mod_long').value||'').trim() || null,
      order_no: Number(document.getElementById('mod_order').value||0),
      status: document.getElementById('mod_status').value || 'published'
    };
    try{
      const res = await fetch('/api/course-modules', {
        method:'POST',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify(payload)
      });
      const j = await res.json().catch(()=>({}));
      if(!res.ok){ throw new Error(j?.message||'Save failed'); }
      ok('Module created'); 
      bootstrap.Modal.getInstance(document.getElementById('moduleModal')).hide();
    }catch(e){
      err(e.message || 'Module API error');
    }
  });
  /* ========= Media Modal ========= */
  const mediaFiles = document.getElementById('mediaFiles');
  const urlRow     = document.getElementById('urlRow');
  const urlInput   = document.getElementById('urlInput');
  const btnAddUrl  = document.getElementById('btnAddUrl');
  const btnSaveUrl = document.getElementById('btnSaveUrl');
  const btnFromLib = document.getElementById('btnFromLib');
  const dropzone   = document.getElementById('dropzone');
  const mediaList  = document.getElementById('mediaList');
  const mTitle     = document.getElementById('m_title');
  const mSub       = document.getElementById('m_sub');
  const mediaCount = document.getElementById('mediaCount');

  function openMedia(uuid, title, short){
    currentCourse = { uuid, title, short };
    mTitle.textContent = title || 'Course';
    mSub.textContent   = (short && short.trim()) ? short.trim() : '—';
    urlRow.style.display='none'; 
    urlInput.value='';

    const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
    mediaModal.show();
    loadMedia();
  }

  function iconFor(kind){
    const map = { image:'fa-image', video:'fa-film', audio:'fa-music', pdf:'fa-file-pdf', other:'fa-file' };
    const k   = map[kind] || 'fa-file';
    return `<i class="fa ${k}"></i>`;
  }

  // NEW: Choose from library → scroll to existing items
  if (btnFromLib) {
    btnFromLib.addEventListener('click', () => {
      if (!mediaList) return;
      mediaList.classList.add('highlight');
      mediaList.scrollIntoView({ behavior:'smooth', block:'center' });
      setTimeout(() => mediaList.classList.remove('highlight'), 1200);
    });
  }

  async function loadMedia(){
    mediaList.innerHTML='<div class="text-center text-muted small py-4"><i class="fa fa-spinner fa-spin me-2"></i>Loading media...</div>';
    mediaCount.textContent='Loading…';
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`, {
        headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      const json = await res.json();
      if(!res.ok) throw new Error(json?.message||'Load failed');

      const items = json?.media || [];
      mediaCount.textContent = `${items.length} item(s)`;

      if(items.length===0){
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

      items.forEach(it => {
        const card = document.createElement('article');
        card.className = 'media-card';
        card.setAttribute('draggable','true');
        card.dataset.id = it.id;

        const urlSafe = escapeHtml(it.featured_url || '');
        const label   = escapeHtml(it.label || it.filename || it.featured_type?.toUpperCase() || 'Media');

        // thumb
        let thumbInner;
        if ((it.featured_type || '').toLowerCase() === 'image') {
          thumbInner = `
            <a href="${urlSafe}" target="_blank" rel="noopener">
              <img src="${urlSafe}" alt="${label}">
            </a>`;
        } else {
          thumbInner = `
            <a href="${urlSafe}" target="_blank" rel="noopener">
              <div class="icon-center">${iconFor(it.featured_type)}</div>
            </a>`;
        }

        card.innerHTML = `
          <div class="card-thumb">
            ${thumbInner}
          </div>
          <div class="card-body">
            <div class="name" title="${urlSafe}">${label}</div>
            <div class="meta">
              Type: ${escapeHtml(it.featured_type || 'other')}
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

      mediaList.innerHTML = '';
      mediaList.appendChild(grid);

      mediaList.querySelectorAll('[data-del]').forEach(btn=>{
        btn.addEventListener('click', ()=> deleteMedia(btn.getAttribute('data-del')));
      });

      initDragReorder();
    }catch(e){
      mediaList.innerHTML = '<div class="text-center text-danger small py-3">Failed to load media.</div>';
      mediaCount.textContent='Failed to load';
      err(e.message);
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
        headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Delete failed'); }
      ok('Media deleted');
      loadMedia();
    }catch(e){ err(e.message); }
  }

  mediaFiles.addEventListener('change', async ()=>{
    if(!mediaFiles.files?.length) return;
    await uploadFiles(mediaFiles.files);
    mediaFiles.value='';
  });

  async function uploadFiles(fileList){
    const fd=new FormData();
    Array.from(fileList).forEach(f=> fd.append('files[]', f));
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`, {
        method:'POST',
        headers:{'Authorization':'Bearer '+getToken()},
        body: fd
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Upload failed');
      ok(`Uploaded ${(j?.inserted||[]).length} file(s)`);
      loadMedia();
    }catch(e){ err(e.message); }
  }

  btnAddUrl.addEventListener('click', ()=>{
    urlRow.style.display = (urlRow.style.display==='none' ? '' : 'none');
  });

  btnSaveUrl.addEventListener('click', async ()=>{
    const url=(urlInput.value||'').trim(); 
    if(!/^https?:\/\//i.test(url)) {
      return Swal.fire('Invalid URL','Provide a valid http(s) URL.','info');
    }
    try{
      const res=await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`,{
        method:'POST',
        headers:{
          'Authorization':'Bearer '+getToken(),
          'Content-Type':'application/json',
          'Accept':'application/json'
        },
        body: JSON.stringify({ url })
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Add failed');
      ok('Media added');
      urlInput.value='';
      urlRow.style.display='none';
      loadMedia();
    }catch(e){ err(e.message); }
  });

  ['dragenter','dragover'].forEach(ev=> dropzone.addEventListener(ev, e=>{
    e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag');
  }));
  ['dragleave','drop'].forEach(ev=> dropzone.addEventListener(ev, e=>{
    e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag');
  }));
  dropzone.addEventListener('drop', e=>{
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
          'Authorization':'Bearer '+getToken(),
          'Content-Type':'application/json',
          'Accept':'application/json'
        },
        body: JSON.stringify({ ids })
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Reorder failed');
      ok('Order updated');
      loadMedia();
    }catch(e){ err(e.message); }
  }


  async function deleteMedia(id){
    const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete media?',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'});
    if(!isConfirmed) return;
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media/${encodeURIComponent(id)}`, {
        method:'DELETE', headers:{'Authorization':'Bearer '+getToken(),'Accept':'application/json'}
      });
      if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j?.message||'Delete failed'); }
      ok('Media deleted'); loadMedia();
    }catch(e){ err(e.message); }
  }

  mediaFiles.addEventListener('change', async ()=>{
    if(!mediaFiles.files?.length) return;
    await uploadFiles(mediaFiles.files);
    mediaFiles.value='';
  });

  async function uploadFiles(fileList){
    const fd=new FormData();
    Array.from(fileList).forEach(f=> fd.append('files[]', f));
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`, {
        method:'POST', headers:{'Authorization':'Bearer '+getToken()}, body: fd
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Upload failed');
      ok(`Uploaded ${ (j?.inserted||[]).length } file(s)`); loadMedia();
    }catch(e){ err(e.message); }
  }

  btnAddUrl.addEventListener('click', ()=>{ urlRow.style.display = (urlRow.style.display==='none' ? '' : 'none'); });
  btnSaveUrl.addEventListener('click', async ()=>{
    const url=(urlInput.value||'').trim(); if(!/^https?:\/\//i.test(url)) return Swal.fire('Invalid URL','Provide a valid http(s) URL.','info');
    try{
      const res=await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media`,{
        method:'POST',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ url })
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Add failed');
      ok('Media added'); urlInput.value=''; urlRow.style.display='none'; loadMedia();
    }catch(e){ err(e.message); }
  });

  ;['dragenter','dragover'].forEach(ev=> dropzone.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag'); }));
  ;['dragleave','drop'].forEach(ev=> dropzone.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag'); }));
  dropzone.addEventListener('drop', e=>{
    const files = e.dataTransfer?.files || []; if(files.length) uploadFiles(files);
  });

  function initDragReorder(){
    let dragSrc=null;
    mediaList.querySelectorAll('.media-item').forEach(it=>{
      it.addEventListener('dragstart', e=>{ dragSrc=it; it.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; });
      it.addEventListener('dragend', ()=>{ dragSrc=null; it.classList.remove('dragging'); });
      it.addEventListener('dragover', e=>{ e.preventDefault(); e.dataTransfer.dropEffect='move'; });
      it.addEventListener('drop', e=>{
        e.preventDefault();
        if(!dragSrc || dragSrc===it) return;
        const items=[...mediaList.querySelectorAll('.media-item')];
        const srcIdx=items.indexOf(dragSrc), dstIdx=items.indexOf(it);
        if(srcIdx<dstIdx) it.after(dragSrc); else it.before(dragSrc);
        persistReorder();
      });
    });
  }

  async function persistReorder(){
    const ids=[...mediaList.querySelectorAll('.media-item')].map(n=> Number(n.dataset.id));
    try{
      const res = await fetch(`/api/courses/${encodeURIComponent(currentCourse.uuid)}/media/reorder`, {
        method:'POST',
        headers:{'Authorization':'Bearer '+getToken(),'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ ids })
      });
      const j=await res.json();
      if(!res.ok) throw new Error(j?.message||'Reorder failed');
      ok('Order updated'); loadMedia();
    }catch(e){ err(e.message); }
  }

  /* ========= Event Listeners ========= */
  document.querySelectorAll('#tab-courses th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      if (sort === col){ sort = '-'+col; }
      else if (sort === '-'+col){ sort = col; }
      else { sort = (col === 'created_at') ? '-created_at' : col; }
      state.courses.page=1; syncSortHeaders(); load('courses');
    });
  });

  // Apply filters from modal - FIXED: Proper modal closing
  // Apply filters from modal - FIXED: Backdrop issue resolved
document.getElementById('btnApplyFilters').addEventListener('click', () => {
  // Update sort from modal
  sort = document.getElementById('modal_sort').value;
  
  state.courses.page = 1;
  
  // Close modal using data-bs-dismiss instead of instance method
  const modal = document.getElementById('filterModal');
  const closeBtn = modal.querySelector('[data-bs-dismiss="modal"]');
  
  if (closeBtn) {
    closeBtn.click(); // This properly handles backdrop removal
  }
  
  // Load data after modal is fully closed
  setTimeout(() => {
    load('courses');
  }, 150);
});

  let srchT;
  q.addEventListener('input', ()=>{ clearTimeout(srchT); srchT=setTimeout(()=>{ state.courses.page=1; load('courses'); }, 350); });
  
  btnReset.addEventListener('click', ()=>{
    q.value=''; 
    perPageSel.value='20'; 
    
    // Reset modal filters
    document.getElementById('modal_course_type').value = '';
    document.getElementById('modal_status').value = '';
    document.getElementById('modal_level').value = '';
    document.getElementById('modal_sort').value = '-created_at';
    
    state.courses.page=1; 
    sort='-created_at'; 
    load('courses');
  });

  // Tab change events
  document.querySelector('a[href="#tab-courses"]').addEventListener('shown.bs.tab', ()=> load('courses'));
  document.querySelector('a[href="#tab-archived"]').addEventListener('shown.bs.tab', ()=> load('archived'));
  document.querySelector('a[href="#tab-draft"]').addEventListener('shown.bs.tab', ()=> load('draft'));
  document.querySelector('a[href="#tab-bin"]').addEventListener('shown.bs.tab', ()=> load('bin'));

  /* ========= Initial Load ========= */
  applyFromURL();
  load('courses');
})();
</script>
@endpush