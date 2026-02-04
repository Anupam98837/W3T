{{-- resources/views/modules/batches/manageBatches.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manage Batches')</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
    
    <!-- Internal Styles -->
    <style>
    /* ===== Shell ===== */
    .bat-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
    .panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

    /* Tabs */
    .nav.nav-tabs{border-color:var(--line-strong)}
    .nav-tabs .nav-link{color:var(--ink)}
    .nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}
    .tab-content,.tab-pane{overflow:visible}

    /* Table Card */
    .table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);}

    .table-wrap .card-body{overflow:visible}
    .table-responsive{overflow:visible !important}
    .table{--bs-table-bg:transparent}
    .table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
    .table thead.sticky-top{z-index:3}
    .table tbody tr{border-top:1px solid var(--line-soft)}
    .table tbody tr:hover{background:var(--page-hover)}
    .small{font-size:12.5px}

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
    .table .badge.badge-warning{background:#f59e0b!important;color:#fff!important}
    .table .badge.badge-info{background:#38bdf8!important;color:#fff!important}
    .table .badge.badge-secondary{background:#64748b!important;color:#fff!important}

    /* Dropdowns inside table (with portal) */
    .table-wrap .dropdown{position:relative;}
    .table-wrap .dd-toggle{position:relative;z-index:7}
    .dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
    .table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:5000}
    .dropdown-menu.dd-portal{position:fixed!important;left:0;top:0;transform:none!important;z-index:5000;border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;background:var(--surface)}
    .dropdown-item{display:flex;align-items:center;gap:.6rem}
    .dropdown-item i{width:16px;text-align:center}
    .dropdown-item.text-danger{color:var(--danger-color)!important}

    /* Batch cell */
    .bcell{display:flex;align-items:center;gap:10px}
    .bthumb{width:48px;height:32px;border-radius:8px;border:1px solid var(--line-strong);object-fit:cover;background:#f4f4f8}

    /* Empty & loader */
    .empty{color:var(--muted-color)}
    .placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

    /* Modals */
    .modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
    .modal-header{border-bottom:1px solid var(--line-strong)}
    .modal-footer{border-top:1px solid var(--line-strong)}
    .form-control,.form-select,textarea{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
    html.theme-dark .form-control,html.theme-dark .form-select,html.theme-dark textarea{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}

    /* ===== Text Editor Styles (from reference) ===== */
    .toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
    .tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
    .tool:hover{background:var(--page-hover)}
    .rte-wrap{position:relative}
    .rte{
      min-height:300px;max-height:600px;overflow:auto;
      border:1px solid var(--line-strong);border-radius:12px;background:#fff;padding:12px;line-height:1.6;outline:none
    }
    .rte:focus{box-shadow:var(--ring);border-color:var(--accent-color)}
    .rte-ph{position:absolute;top:12px;left:12px;color:#9aa3b2;pointer-events:none;font-size:var(--fs-14)}
    .rte.has-content + .rte-ph{display:none}

    /* Input focus polish */
    .form-control:focus, .form-select:focus{box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 20%, transparent);border-color:var(--accent-color)}

    /* Dark tweaks */
    html.theme-dark .panel,
    html.theme-dark .table-wrap.card,
    html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
    html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
    html.theme-dark .table tbody tr{border-color:var(--line-soft)}
    html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}
    html.theme-dark .mfa-toolbar .form-control,
    html.theme-dark .mfa-toolbar .form-select{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}
    html.theme-dark .rte{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
    html.theme-dark .tool{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
    .verify-content.hidden {
    opacity: 0;
    pointer-events: none;
}

    </style>
</head>
<body>
@section('content')
<div class="bat-wrap">
  {{-- ===== Global (applies to all tabs) ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-xxl d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Course</label>
        <select id="courseSel" class="form-select" style="min-width:260px;">
          <option value="">Select a course…</option>
        </select>
      </div>

      <div id="courseHint" class="small text-muted" style="display:none;">Pick a course to load batches.</div>
    </div>
  </div>

  {{-- ===== Tabs ===== --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true"><i class="fa-solid fa-layer-group me-2"></i>Batches</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-archived" role="tab" aria-selected="false"><i class="fa-solid fa-folder me-2"></i>Archived</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-bin" role="tab" aria-selected="false"><i class="fa-solid fa-trash me-2"></i>Bin</a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ===== ACTIVE (non-archived) ===== --}}
    <div class="tab-pane fade show active" id="tab-active" role="tabpanel">

      {{-- Toolbar (Active only) --}}
      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;" disabled>
              <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:300px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search batch title/tagline…" disabled>
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          {{-- Filter Button --}}
          <button id="btnFilter" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal" disabled>
            <i class="fa fa-filter me-1"></i>Filter
          </button>

          <button id="btnReset" class="btn btn-primary" disabled><i class="fa fa-rotate-left me-1"></i>Reset</button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <button id="btnCreate" class="btn btn-primary" disabled>
            <i class="fa fa-plus me-1"></i>New Batch
          </button>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th class="sortable" data-col="badge_title">BATCH <span class="caret"></span></th>
                  <th style="width:110px;">MODE</th>
                  <th class="sortable" data-col="status" style="width:120px;">STATUS <span class="caret"></span></th>
                  <th class="sortable" data-col="starts_at" style="width:160px;">STARTS <span class="caret"></span></th>
                  <th style="width:160px;">ENDS</th>
                  <th style="width:170px;">DURATION</th>
                  <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-active">
                <tr id="loaderRow-active" style="display:none;">
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
                <tr id="askCourse-active">
                  <td colspan="8" class="p-4 text-center text-muted">
                    <i class="fa fa-layer-group mb-2" style="font-size:28px;opacity:.6"></i>
                    <div>Please select a course to load its batches.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No batches found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-active">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-active" class="pagination mb-0"></ul></nav>
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
                  <th>BATCH</th>
                  <th style="width:110px;">MODE</th>
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-archived">
                <tr id="loaderRow-archived" style="display:none;">
                  <td colspan="4" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr id="askCourse-archived">
                  <td colspan="4" class="p-4 text-center text-muted">
                    <i class="fa fa-box-archive mb-2" style="font-size:28px;opacity:.6"></i>
                    <div>Select a course to view archived batches.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-archived" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-box-archive mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No archived batches.</div>
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
                  <th>BATCH</th>
                  <th style="width:110px;">MODE</th>
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

{{-- ================= Filter Batches Modal ================= --}}
<div class="modal" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalLabel"><i class="fa fa-filter me-2"></i>Filter Batches</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          {{-- Mode --}}
          <div class="col-12">
            <label class="form-label">Mode</label>
            <select id="modal_mode" class="form-select">
              <option value="">All Modes</option>
              <option value="online">Online</option>
              <option value="offline">Offline</option>
              <option value="hybrid">Hybrid</option>
            </select>
          </div>

          {{-- Status --}}
          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          {{-- Sort By --}}
          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="badge_title">Title A-Z</option>
              <option value="-badge_title">Title Z-A</option>
              <option value="starts_at">Start Date (Oldest)</option>
              <option value="-starts_at">Start Date (Newest)</option>
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

{{-- ================= View Batch (modal) ================= --}}
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-circle-info fa-fw me-2"></i>Batch Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><div id="vBody">Loading…</div></div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

{{-- ================= Manage Students (modal) ================= --}}
<div class="modal fade" id="studentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-user-graduate me-2"></i>Manage Students</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="studTabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabExisting" type="button" role="tab">Existing Students</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabImport" type="button" role="tab">Import Students</button></li>
        </ul>
        <div class="tab-content pt-3">
          <div class="tab-pane fade show active" id="tabExisting" role="tabpanel">
            <div class="d-flex align-items-center justify-content-between mstab-head mb-3">
              <div class="left-tools d-flex align-items-center gap-2">
                <input id="st_q" class="form-control" style="width:240px" placeholder="Search by name/email/phone…">
                <label class="text-muted small mb-0">Per page</label>
                <select id="st_per" class="form-select" style="width:90px"><option>10</option><option selected>20</option><option>30</option><option>50</option></select>
                <label class="text-muted small mb-0">Assigned</label>
                <select id="st_assigned" class="form-select" style="width:150px">
                  <option value="all" selected>All</option>
                  <option value="assigned">Assigned</option>
                  <option value="unassigned">Unassigned</option>
                </select>
                <button id="st_apply" class="btn btn-primary"><i class="fa fa-check me-1"></i>Apply</button>
              </div>
              <div class="text-muted small" id="st_meta">—</div>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle st-table mb-0">
<thead>
  <tr>
    <th>Name</th>
    <th style="width:28%;">Email</th>
    <th style="width:16%;">Phone</th>
    <th style="width:14%;">Enrollment Status</th>
    <th style="width:110px;" class="text-center">Select</th>
    <th class="text-center" style="width:110px;">Verification</th>
  </tr>
</thead>
                <tbody id="st_rows">
                  <tr id="st_loader" style="display:none;"><td colspan="4" class="p-3"><div class="placeholder-wave"><div class="placeholder col-12 mb-2" style="height:16px;"></div><div class="placeholder col-12 mb-2" style="height:16px;"></div><div class="placeholder col-12 mb-2" style="height:16px;"></div></div></td></tr>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-end p-2"><ul id="st_pager" class="pagination mb-0"></ul></div>
          </div>
          <div class="tab-pane fade" id="tabImport" role="tabpanel">
            <div class="dropzone text-center" id="csvDrop">
              <div class="mb-2"><i class="fa-regular fa-circle-up" style="font-size:28px;opacity:.8"></i></div>
              <div class="fw-semibold">Import CSV File</div>
              <div class="hint mt-1">Columns required: <b>email</b>, <b>name</b>, <b>phone</b> (or <b>phone_number</b>).</div>
              <div class="mt-2"><label class="btn btn-light me-2"><i class="fa fa-file-csv me-1"></i>Import CSV<input id="csvFile" type="file" class="d-none" accept=".csv,text/csv"></label></div>
            </div>
            <div class="mt-3 small text-muted" id="csvHint">—</div>
            <div class="mt-2" id="csvSummary" style="display:none;"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

{{-- ================= Assign Instructors (modal) ================= --}}
<div class="modal fade" id="instructorsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-chalkboard-user me-2"></i>Assign Instructors</h5>
        <a id="ins_add_btn" href="/admin/users/manage" class="btn btn-primary btn-sm ms-auto"><i class="fa fa-user-plus me-1"></i> Add Instructor</a>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between mstab-head mb-3">
          <div class="left-tools d-flex align-items-center gap-2">
            <input id="ins_q" class="form-control" style="width:240px" placeholder="Search by name/email/phone…">
            <label class="text-muted small mb-0">Per page</label>
            <select id="ins_per" class="form-select" style="width:90px"><option>10</option><option selected>20</option><option>30</option><option>50</option></select>
            <label class="text-muted small mb-0">Assigned</label>
            <select id="ins_assigned" class="form-select" style="width:150px">
              <option value="all" selected>All</option>
              <option value="assigned">Assigned</option>
              <option value="unassigned">Unassigned</option>
            </select>
            <button id="ins_apply" class="btn btn-primary"><i class="fa fa-check me-1"></i>Apply</button>
          </div>
          <div class="text-muted small" id="ins_meta">—</div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle st-table mb-0">
            <thead><tr><th>Name</th><th style="width:28%;">Email</th><th style="width:18%;">Phone</th><th style="width:18%;">Role in Batch</th><th class="text-center" style="width:110px;">Assign</th></tr></thead>
            <tbody id="ins_rows">
              <tr id="ins_loader" style="display:none;"><td colspan="5" class="p-3"><div class="placeholder-wave"><div class="placeholder col-12 mb-2" style="height:16px;"></div><div class="placeholder col-12 mb-2" style="height:16px;"></div><div class="placeholder col-12 mb-2" style="height:16px;"></div></div></td></tr>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end p-2"><ul id="ins_pager" class="pagination mb-0"></ul></div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

{{-- ================= Assign Quizzes (modal) ================= --}}
<div class="modal fade" id="quizzesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-question me-2"></i>Assign Quizzes</h5>
        <a id="qz_add_btn" href="/admin/quizz/create" class="btn btn-primary btn-sm ms-auto"><i class="fa fa-plus me-1"></i> Add Quiz</a>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between mstab-head mb-3">
          <div class="left-tools d-flex align-items-center gap-2">
            <input id="qz_q" class="form-control" style="width:240px" placeholder="Search by title/type…">
            <label class="text-muted small mb-0">Per page</label>
            <select id="qz_per" class="form-select" style="width:90px"><option>10</option><option selected>20</option><option>30</option><option>50</option></select>
            <label class="text-muted small mb-0">Assigned</label>
            <select id="qz_assigned" class="form-select" style="width:150px">
              <option value="all" selected>All</option>
              <option value="assigned">Assigned</option>
              <option value="unassigned">Unassigned</option>
            </select>
            <button id="qz_apply" class="btn btn-primary"><i class="fa fa-check me-1"></i>Apply</button>
          </div>
          <div class="text-muted small" id="qz_meta">—</div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle st-table mb-0">
    <thead>
        <tr>
            <th>Title</th>
            <th style="width:120px;">Attempts</th>
            <th style="width:120px;">Publish</th>
            <th class="text-center" style="width:110px;">Assign</th>
        </tr>
    </thead>
    <tbody id="qz_rows">
        <tr id="qz_loader" style="display:none;">
            <td colspan="4" class="p-3">
                <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                </div>
            </td>
        </tr>
    </tbody>
</table>

        </div>
        <div class="d-flex justify-content-end p-2"><ul id="qz_pager" class="pagination mb-0"></ul></div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
 
{{-- ================= Assign Coding Questions (modal) ================= --}}
<div class="modal fade" id="codingQuestionsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-code me-2"></i>Assign Coding Questions</h5>
        <!-- <a id="cq_add_btn" href="/admin/coding-questions/create" class="btn btn-primary btn-sm ms-auto">
          <i class="fa fa-plus me-1"></i> Add Coding Question
        </a> -->
        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
      </div>
 
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between mstab-head mb-3">
          <div class="left-tools d-flex align-items-center gap-2 flex-wrap">
            <input id="cq_q" class="form-control" style="width:260px" placeholder="Search by title/difficulty…">
            <label class="text-muted small mb-0">Per page</label>
            <select id="cq_per" class="form-select" style="width:90px">
              <option>10</option><option selected>20</option><option>30</option><option>50</option>
            </select>
 
            <label class="text-muted small mb-0">Assigned</label>
            <select id="cq_assigned" class="form-select" style="width:150px">
              <option value="all" selected>All</option>
              <option value="assigned">Assigned</option>
              <option value="unassigned">Unassigned</option>
            </select>
 
            <button id="cq_apply" class="btn btn-primary">
              <i class="fa fa-check me-1"></i>Apply
            </button>
          </div>
 
          <div class="text-muted small" id="cq_meta">—</div>
        </div>
 
        <div class="table-responsive">
          <table class="table table-hover align-middle st-table mb-0">
            <thead>
  <tr>
    <th>Title</th>
    <th style="width:120px;">Difficulty</th>
    <th style="width:140px;" class="text-center">Max Attempts</th>
    <th style="width:220px;" class="text-center">Batch Attempts</th>
    <th class="text-center" style="width:110px;">Assign</th>
  </tr>
</thead>
 
 
            <tbody id="cq_rows">
              <tr id="cq_loader" style="display:none;">
                <td colspan="5" class="p-3">
                  <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
 
        <div class="d-flex justify-content-end p-2">
          <ul id="cq_pager" class="pagination mb-0"></ul>
        </div>
      </div>
 
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
 
{{-- ================= Create / Edit Batch (modal) ================= --}}
<div class="modal fade" id="batchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="bm_title" class="modal-title"><i class="fa fa-certificate me-2"></i>Create Batch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <input type="hidden" id="bm_mode" value="create">
          <input type="hidden" id="bm_uuid" value="">
          <div class="col-md-6">
            <label class="form-label">Course</label>
            <input id="bm_course_label" class="form-control" readonly>
            <input id="bm_course_id" type="hidden">
            <div class="small text-muted mt-1">Course is set from the page toolbar.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select id="bm_status" class="form-select">
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Badge Title <span class="text-danger">*</span></label>
            <input id="bm_title_input" class="form-control" maxlength="255" placeholder="e.g., Python Mastery — Jan Cohort (Evening)">
          </div>
          <div class="col-md-4">
            <label class="form-label">Mode</label>
            <select id="bm_mode_select" class="form-select">
              <option value="online">Online</option>
              <option value="offline">Offline</option>
              <option value="hybrid" selected>Hybrid</option>
            </select>
          </div>
          <div class="col-12"><label class="form-label">Tagline</label><input id="bm_tagline" class="form-control" maxlength="255" placeholder="Short hook line (optional)"></div>
          
          {{-- UPDATED TEXT EDITOR SECTION --}}
          <div class="col-12">
            <label class="form-label">Badge Description</label>
            <div class="toolbar">
              <button class="tool" type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
              <button class="tool" type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
              <button class="tool" type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
              <button class="tool" type="button" data-format="H1">H1</button>
              <button class="tool" type="button" data-format="H2">H2</button>
              <button class="tool" type="button" data-format="H3">H3</button>
              <button class="tool" type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
              <button class="tool" type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
              <button class="tool" type="button" id="btnLink"><i class="fa-solid fa-link"></i></button>
            </div>
            <div class="rte-wrap">
              <div id="bm_desc_editor" class="rte" contenteditable="true" spellcheck="true"></div>
              <div class="rte-ph">Write the batch description here…</div>
            </div>
            <div class="err" data-for="bm_desc"></div>
          </div>

          <div class="col-md-6"><label class="form-label">Contact Number</label><input id="bm_contact" class="form-control" maxlength="32" placeholder="+91… (optional)"></div>
          <div class="col-md-6"><label class="form-label">Note</label><input id="bm_note" class="form-control" placeholder="Internal note (optional)"></div>
          <div class="col-md-4"><label class="form-label">Start Date</label><input id="bm_start" type="date" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">End Date</label><input id="bm_end" type="date" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Duration (auto)</label><input id="bm_duration_preview" class="form-control" readonly></div>
          <div class="col-md-6"><label class="form-label">Featured Image (optional)</label><input id="bm_image" type="file" class="form-control" accept="image/*"><div class="small text-muted mt-1">Max 5MB. jpg/jpeg/png/webp/gif</div></div>
          <div class="col-md-6 d-flex align-items-end">
            {{-- UPDATED PREVIEW IMAGE WITH BETTER DEFAULT --}}
            <img id="bm_image_preview" class="bthumb" style="width:120px;height:80px" src="https://dummyimage.com/120x80/e9e3f5/5e1570.jpg&text=Batch+Image" alt="Batch preview" />
          </div>
          <div class="col-12">
            <label class="form-label">Group Links (Key → URL)</label>
            <div class="small text-muted mb-1">Example: <b>WhatsApp</b> → <span class="text-muted">https://chat.whatsapp.com/…</span> • <b>Telegram</b> → <span class="text-muted">https://t.me/…</span></div>
            <div id="gl_wrap"></div>
            <button id="gl_add" class="btn btn-light btn-sm mt-2" type="button"><i class="fa fa-plus me-1"></i>Add Link</button>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button id="bm_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button></div>
    </div>
  </div>
</div>
{{-- ================= Manage Course Modules (modal) ================= --}}
<div class="modal fade" id="modulesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-layer-group me-2"></i>Manage Course Modules</h5>
        <div class="text-muted small ms-2" id="mod_ctx">—</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <ul class="nav nav-tabs" id="modTabs" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#modTabCourse" type="button" role="tab">
              Course Modules
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modTabBatch" type="button" role="tab">
              Batch Course Modules
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modTabSettings" type="button" role="tab">
              Settings
            </button>
          </li>
        </ul>

        <div class="tab-content pt-3">
          {{-- Course Modules --}}
          <div class="tab-pane fade show active" id="modTabCourse" role="tabpanel">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <input id="cm_q" class="form-control" style="width:280px" placeholder="Search modules…">
                <label class="text-muted small mb-0">Per page</label>
                <select id="cm_per" class="form-select" style="width:90px">
                  <option>10</option><option selected>20</option><option>30</option><option>50</option>
                </select>
                <button id="cm_apply" class="btn btn-primary"><i class="fa fa-check me-1"></i>Apply</button>
              </div>
              <div class="text-muted small" id="cm_meta">—</div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Module</th>
                    <th style="width:160px;">Type</th>
                    <th style="width:140px;">Status</th>
                    <th class="text-center" style="width:110px;">Assign</th>
                  </tr>
                </thead>
                <tbody id="cm_rows">
                  <tr id="cm_loader" style="display:none;">
                    <td colspan="4" class="p-3">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end p-2">
              <ul id="cm_pager" class="pagination mb-0"></ul>
            </div>
          </div>

          {{-- Batch Course Modules --}}
          <div class="tab-pane fade" id="modTabBatch" role="tabpanel">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <input id="bm_q" class="form-control" style="width:280px" placeholder="Search assigned modules…">
                <label class="text-muted small mb-0">Per page</label>
                <select id="bm_per" class="form-select" style="width:90px">
                  <option>10</option><option selected>20</option><option>30</option><option>50</option>
                </select>
                <button id="bm_apply_mod" class="btn btn-primary"><i class="fa fa-check me-1"></i>Apply</button>
              </div>
              <div class="text-muted small" id="bm_meta_mod">—</div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Module</th>
                    <th style="width:160px;">Order</th>
                    <th style="width:170px;">Completed</th>
                    <th class="text-end" style="width:140px;">Actions</th>
                  </tr>
                </thead>
                <tbody id="bm_rows_mod">
                  <tr id="bm_loader_mod" style="display:none;">
                    <td colspan="4" class="p-3">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:16px;"></div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end p-2">
              <ul id="bm_pager_mod" class="pagination mb-0"></ul>
            </div>
          </div>

          {{-- Settings --}}
          <div class="tab-pane fade" id="modTabSettings" role="tabpanel">
            <div class="row g-3">
              <div class="col-12">
                <div class="alert alert-info small mb-0">
                  These toggles decide which rule(s) unlock the next module for the student.
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between border rounded-3 p-3">
                  <div>
                    <div class="fw-semibold">Previous Module Completed</div>
                    <div class="small text-muted">Require previous module completion to unlock next.</div>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="set_prev_completed">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between border rounded-3 p-3">
                  <div>
                    <div class="fw-semibold">Assignments Submitted</div>
                    <div class="small text-muted">Require assignment submission to unlock next.</div>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="set_assignments_submitted">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between border rounded-3 p-3">
                  <div>
                    <div class="fw-semibold">Exams Submitted</div>
                    <div class="small text-muted">Require exam submission to unlock next.</div>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="set_exams_submitted">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between border rounded-3 p-3">
                  <div>
                    <div class="fw-semibold">Coding Test Submitted</div>
                    <div class="small text-muted">Require coding test submission to unlock next.</div>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="set_coding_test_submitted">
                  </div>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-end gap-2">
                <button class="btn btn-light" id="set_reload"><i class="fa fa-rotate me-1"></i>Reload</button>
                <button class="btn btn-primary" id="set_save"><i class="fa fa-save me-1"></i>Save Settings</button>
              </div>

              <div class="col-12">
                <div class="small text-muted" id="set_meta">—</div>
              </div>
            </div>
          </div>

        </div>{{-- /.tab-content --}}
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- ================= Edit Batch Course Module (mini modal) ================= --}}
<div class="modal fade" id="bcmEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-pen-to-square me-2"></i>Edit Batch Course Module</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="bcm_id" value="">
        <div class="mb-3 d-none">
          <label class="form-label">Display Order</label>
          <input type="number" min="1" class="form-control" id="bcm_order" placeholder="e.g. 1">
        </div>

        <div class="mb-3">
          <label class="form-label">Completed</label>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="bcm_completed">
            <label class="form-check-label small text-muted">Mark this module as completed for the batch.</label>
          </div>
        </div>

        <div class="small text-muted" id="bcm_meta">—</div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="bcm_save"><i class="fa fa-save me-1"></i>Save</button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
  <div id="okToast" class="toast text-bg-success border-0"><div class="d-flex"><div id="okMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div></div>
  <div id="errToast" class="toast text-bg-danger border-0 mt-2"><div class="d-flex"><div id="errMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div></div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
/* =================== AUTH / GLOBALS =================== */
const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
if (!TOKEN){ Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/'); }

const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
const errToast = new bootstrap.Toast(document.getElementById('errToast'));
const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

/* Ensure 3-dots menu always works */
document.addEventListener('click', (e)=>{
  const btn = e.target.closest('.dd-toggle'); if(!btn) return;
  e.preventDefault(); e.stopPropagation();
  const inst = bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'});
  inst.toggle();
});

/* =================== UTILS =================== */
const esc = (s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>m[ch]); };
function fmtDate(iso){ if(!iso) return '-'; const d=new Date(iso); return isNaN(d)?esc(iso):d.toLocaleDateString(undefined,{year:'numeric',month:'short',day:'2-digit'}); }
function fmtDateTime(iso){ if(!iso) return '-'; const d=new Date(iso); return isNaN(d)?esc(iso):d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'}); }
function badgeStatus(s){ s=(s||'').toString().toLowerCase(); const map={active:'success',inactive:'warning',archived:'secondary'}; const cls=map[s]||'secondary'; return `<span class="badge badge-${cls} text-uppercase">${esc(s||'-')}</span>`; }
function firstError(j){ if(j?.errors){ const k=Object.keys(j.errors)[0]; if(k){ const v=j.errors[k]; return Array.isArray(v)?v[0]:String(v); } } return j?.message||''; }
function humanYMD(s,e){
  // return '-' on invalid / end-before-start (based on original inputs)
  const origSd = new Date(s), origEd = new Date(e);
  if(isNaN(origSd) || isNaN(origEd) || origEd < origSd) return '-';

  // Make calculation **inclusive** by shifting the start one day earlier.
  // This has the effect of counting the start date itself.
  const sd = new Date(origSd);
  sd.setDate(sd.getDate() - 1);
  const ed = new Date(origEd);

  let y = ed.getFullYear() - sd.getFullYear();
  let m = ed.getMonth() - sd.getMonth();
  let d = ed.getDate() - sd.getDate();

  if (d < 0) {
    const prevDays = new Date(ed.getFullYear(), ed.getMonth(), 0).getDate();
    d += prevDays;
    m -= 1;
  }
  if (m < 0) {
    m += 12;
    y -= 1;
  }
  const parts = [];
  if (y > 0) parts.push(y + ' ' + (y === 1 ? 'year' : 'years'));
  if (m > 0) parts.push(m + ' ' + (m === 1 ? 'month' : 'months'));
  // show days when >0, or if nothing else present show at least "1 day" (inclusive)
  if (d > 0 || !parts.length) parts.push(d + ' ' + (d === 1 ? 'day' : 'days'));

  return parts.join(' ');
}
(function () {
  // prevent double-init if included multiple times
  if (window.__RTE_ACTIVE_SYNC__) return;
  window.__RTE_ACTIVE_SYNC__ = true;

  const FORMAT_MAP = { H1: "h1", H2: "h2", H3: "h3", P: "p" };

  function findEditorForToolbar(toolbar) {
    // Most common: toolbar -> next sibling .rte-wrap -> .rte
    let next = toolbar.nextElementSibling;
    if (next && next.classList && next.classList.contains("rte-wrap")) {
      const ed = next.querySelector(".rte");
      if (ed) return ed;
    }

    // Fallback: search nearby container
    const block =
      toolbar.closest(".mb-1,.mb-2,.mb-3,.mb-4,.col-12,.col-md-12,.form-group") ||
      toolbar.parentElement ||
      document;

    return block.querySelector(".rte-wrap .rte");
  }

  function selectionInside(editor) {
    const sel = document.getSelection();
    if (!sel || !sel.anchorNode) return false;
    const node = sel.anchorNode;
    return node === editor || editor.contains(node);
  }

  function isFormatActive(fmt) {
    try {
      const val = (document.queryCommandValue("formatBlock") || "").toLowerCase();
      const want = (FORMAT_MAP[fmt] || fmt || "").toLowerCase();
      return !!want && val.includes(want);
    } catch {
      return false;
    }
  }

  function bindToolbar(toolbar) {
    if (toolbar.__rteBound) return; // avoid rebinding
    const editor = findEditorForToolbar(toolbar);
    if (!editor) return;

    toolbar.__rteBound = true;

    const tools = Array.from(toolbar.querySelectorAll(".tool"));

    function update() {
      const inside = selectionInside(editor) || document.activeElement === editor;

      // Editor ring (optional)
      editor.classList.toggle("active", document.activeElement === editor);

      if (!inside) return;

      tools.forEach((btn) => {
        const cmd = btn.dataset.cmd;
        const fmt = btn.dataset.format;

        let on = false;
        try {
          if (cmd) on = !!document.queryCommandState(cmd);
          else if (fmt) on = isFormatActive(fmt);
        } catch {
          on = false;
        }

        btn.classList.toggle("active", on);
        btn.setAttribute("aria-pressed", on ? "true" : "false");
      });
    }

    // update after toolbar actions (your existing execCommand can remain as-is)
    toolbar.addEventListener("click", () => setTimeout(update, 30));

    // update while typing / moving caret
    ["keyup", "mouseup", "input", "focus", "blur"].forEach((ev) => {
      editor.addEventListener(ev, () => setTimeout(update, 0));
    });

    // update when selection changes (only if inside this editor)
    document.addEventListener("selectionchange", () => {
      if (selectionInside(editor)) update();
    });

    // initial
    update();
  }

  function initAll() {
    document.querySelectorAll(".toolbar").forEach(bindToolbar);
  }

  // init now / on load
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll);
  } else {
    initAll();
  }

  // handle editors that appear later (modals, dynamic HTML)
  const mo = new MutationObserver(() => initAll());
  mo.observe(document.body, { childList: true, subtree: true });
})();

/* New helper: enrollment badge render */
function badgeEnrollment(s){
  const st = (s||'').toString().toLowerCase();
  const map = { enrolled:'success', pending:'warning', invited:'info', unassigned:'secondary' };
  const cls = map[st] || 'secondary';
  return `<span class="badge badge-${cls} text-capitalize enrollment-badge">${esc(s||'-')}</span>`;
}

/* =================== ELEMENTS & STATE =================== */
const courseSel = document.getElementById('courseSel');
const courseHint= document.getElementById('courseHint');
const btnCreate = document.getElementById('btnCreate');
const q          = document.getElementById('q');
const btnFilter  = document.getElementById('btnFilter');
const perPageSel = document.getElementById('per_page');
const btnReset   = document.getElementById('btnReset');

const tabs = {
  active   :{rows:'#rows-active',   loader:'#loaderRow-active',   empty:'#empty-active',   pager:'#pager-active',   ask:'#askCourse-active',   meta:'#metaTxt-active'},
  archived :{rows:'#rows-archived', loader:'#loaderRow-archived', empty:'#empty-archived', pager:'#pager-archived', ask:'#askCourse-archived', meta:'#metaTxt-archived'},
  bin      :{rows:'#rows-bin',      loader:'#loaderRow-bin',      empty:'#empty-bin',      pager:'#pager-bin',      ask:'#askCourse-bin',      meta:'#metaTxt-bin'},
};

let currentCourseId = '';
let currentCourseUuid = '';
const state = { active:{page:1}, archived:{page:1}, bin:{page:1} };
let sort = '-created_at';
let filterState = { mode: '', status: '' };

/* =================== INIT =================== */
applyFromURL();
loadCourses();
wiring();
/* ================= COURSE MODULES MANAGER ================= */
const modulesModalEl = document.getElementById('modulesModal');
const mod_ctx = document.getElementById('mod_ctx');

const cm_q = document.getElementById('cm_q');
const cm_per = document.getElementById('cm_per');
const cm_apply = document.getElementById('cm_apply');
const cm_rows = document.getElementById('cm_rows');
const cm_loader = document.getElementById('cm_loader');
const cm_meta = document.getElementById('cm_meta');
const cm_pager = document.getElementById('cm_pager');

const bm_q_mod = document.getElementById('bm_q');
const bm_per_mod = document.getElementById('bm_per');
const bm_apply_mod = document.getElementById('bm_apply_mod');
const bm_rows_mod = document.getElementById('bm_rows_mod');
const bm_loader_mod = document.getElementById('bm_loader_mod');
const bm_meta_mod = document.getElementById('bm_meta_mod');
const bm_pager_mod = document.getElementById('bm_pager_mod');

const set_prev_completed = document.getElementById('set_prev_completed');
const set_assignments_submitted = document.getElementById('set_assignments_submitted');
const set_exams_submitted = document.getElementById('set_exams_submitted');
const set_coding_test_submitted = document.getElementById('set_coding_test_submitted');
const set_reload = document.getElementById('set_reload');
const set_save = document.getElementById('set_save');
const set_meta = document.getElementById('set_meta');

let modulesModal;
let mod_batch_uuid = null;     // batch uuid (preferred)
let mod_batch_key = null;      // batch id/uuid fallback
let cm_page = 1;
let bm_page_mod = 1;
let mod_batch_id = null;

function pickList(j){
  if (Array.isArray(j)) return j;
  if (Array.isArray(j?.data)) return j.data;
  if (Array.isArray(j?.data?.data)) return j.data.data;
  if (Array.isArray(j?.items)) return j.items;
  if (Array.isArray(j?.modules)) return j.modules;
  return [];
}
function pickPag(j, fallbackLen, perVal, pageVal){
  return j?.pagination || j?.meta || j?.data?.meta || {
    current_page: Number(pageVal || 1),
    per_page: Number(perVal || 20),
    total: fallbackLen
  };
}
function li(dis,act,label,t){
  return `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
    <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
  </li>`;
}
function showRowLoader(el, on){ if(el) el.style.display = on ? '' : 'none'; }

function cmParams(){
  const p = new URLSearchParams();
  if (cm_q?.value?.trim()) p.set('q', cm_q.value.trim());
  p.set('per_page', cm_per?.value || 20);
  p.set('page', cm_page);

  // ✅ send both (backend can accept either)
  if (currentCourseId) p.set('course_id', currentCourseId);
  if (currentCourseUuid) p.set('course_uuid', currentCourseUuid);

  if (mod_batch_uuid) p.set('batch_uuid', mod_batch_uuid);
  p.set('type', 'course');
  return p.toString();
}
function bmParamsMod(){
  const p = new URLSearchParams();
  if (bm_q_mod?.value?.trim()) p.set('q', bm_q_mod.value.trim());
  p.set('per_page', bm_per_mod?.value || 20);
  p.set('page', bm_page_mod);

  // ✅ send both
  if (currentCourseId) p.set('course_id', currentCourseId);
  if (currentCourseUuid) p.set('course_uuid', currentCourseUuid);

  if (mod_batch_uuid) p.set('batch_uuid', mod_batch_uuid);
  p.set('type', 'batch');
  return p.toString();
}

function openModules(batchId, batchUuid){
  modulesModal = modulesModal || new bootstrap.Modal(modulesModalEl);

  mod_batch_uuid = batchUuid || null;                 // uuid
  mod_batch_id   = batchId ? Number(batchId) : null;  // numeric id
  mod_batch_key  = batchUuid || batchId || null;      // ✅ set fallback key

  cm_page = 1;
  bm_page_mod = 1;

  const courseName = courseSel?.options?.[courseSel.selectedIndex]?.text || '—';
  mod_ctx.textContent = `Course: ${courseName} • Batch: ${batchUuid || batchId}`;

  modulesModal.show();
  loadCourseModulesTab();
}

cm_apply?.addEventListener('click', ()=>{ cm_page = 1; loadCourseModulesTab(); });
cm_per?.addEventListener('change', ()=>{ cm_page = 1; loadCourseModulesTab(); });
let cmT;
cm_q?.addEventListener('input', ()=>{ clearTimeout(cmT); cmT=setTimeout(()=>{ cm_page=1; loadCourseModulesTab(); }, 350); });

bm_apply_mod?.addEventListener('click', ()=>{ bm_page_mod = 1; loadBatchModulesTab(); });
bm_per_mod?.addEventListener('change', ()=>{ bm_page_mod = 1; loadBatchModulesTab(); });
let bmT;
bm_q_mod?.addEventListener('input', ()=>{ clearTimeout(bmT); bmT=setTimeout(()=>{ bm_page_mod=1; loadBatchModulesTab(); }, 350); });

document.querySelectorAll('#modTabs button[data-bs-toggle="tab"]').forEach(btn=>{
  btn.addEventListener('shown.bs.tab', (e)=>{
    const target = e.target?.getAttribute('data-bs-target') || '';
    if (target === '#modTabCourse') loadCourseModulesTab();
    if (target === '#modTabBatch') loadBatchModulesTab();
    if (target === '#modTabSettings') loadModuleSettings();
  });
});

set_reload?.addEventListener('click', ()=> loadModuleSettings());
set_save?.addEventListener('click', ()=> saveModuleSettings());

async function loadCourseModulesTab(){
  if(!mod_batch_uuid) return;
  showRowLoader(cm_loader, true);
  cm_rows.querySelectorAll('tr:not(#cm_loader)').forEach(tr=>tr.remove());
  cm_pager.innerHTML = '';
  cm_meta.textContent = '—';

  try{
    const res = await fetch(`/api/course-modules?${cmParams()}`, {
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || 'Failed to load course modules');

    const items = pickList(j);
    const pag = pickPag(j, items.length, cm_per?.value, cm_page);

    const frag = document.createDocumentFragment();

    items.forEach(m=>{
      // try to normalize
      const id = m.id ?? m.module_id ?? m.course_module_id ?? m.uuid ?? '';
      const title = m.title ?? m.name ?? m.module_title ?? 'Untitled';
      const type = m.type ?? m.module_type ?? 'module';
      const status = m.status ?? (m.active ? 'active' : 'inactive');
      
      // FIXED: More comprehensive check for assigned status
      // Check multiple possible field names and formats from API response
      const assigned = !!(
        m.assigned ?? 
        m.is_assigned ?? 
        m.in_batch ?? 
        m.batch_assigned ?? 
        m.assignedToBatch ??
        m.pivot?.assigned ??
        m.pivot?.is_assigned ??
        (m.batch_course_module_id !== null && m.batch_course_module_id !== undefined) ??
        false
      );

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-semibold">${esc(title)}</td>
        <td class="text-capitalize">${esc(type)}</td>
        <td>${badgeStatus(status)}</td>
        <td class="text-center">
          <div class="form-check form-switch d-inline-block">
            <input class="form-check-input cm-tg" type="checkbox"
              data-id="${esc(id)}" ${assigned?'checked':''}>
          </div>
        </td>
      `;
      frag.appendChild(tr);
    });

    cm_rows.appendChild(frag);

    // assign/unassign
    cm_rows.querySelectorAll('.cm-tg').forEach(ch=>{
      ch.addEventListener('change', async ()=>{
        const moduleId = ch.dataset.id;
        const wantAssigned = !!ch.checked;
        const originalState = !wantAssigned; // Store for rollback

        ch.disabled = true;
        try{
          await assignCourseModuleToBatch(moduleId, wantAssigned);
          ok(wantAssigned ? 'Module assigned' : 'Module unassigned');

          // Optional: Refresh the Batch Modules tab if it's currently visible
          const batchTabActive = document.querySelector('#modTabBatch.active');
          if(batchTabActive){
            await loadBatchModulesTab();
          }

        }catch(e){
          // Rollback to original state on error
          ch.checked = originalState;
          err(e.message || 'Assign failed');
        }finally{
          ch.disabled = false;
        }
      });
    });

    // pager
    const total = Number(pag.total||items.length);
    const per = Number(pag.per_page||cm_per?.value||20);
    const cur = Number(pag.current_page||pag.page||cm_page||1);
    const pages = Math.max(1, Math.ceil(total/per));

    let html = '';
    html += li(cur<=1,false,'Prev',cur-1);
    const w=2,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    for(let i=s;i<=e;i++) html += li(false,i===cur,i,i);
    html += li(cur>=pages,false,'Next',cur+1);
    cm_pager.innerHTML = html;

    cm_pager.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click', ()=>{
        const t = Number(a.dataset.page);
        if(!t || t===cm_page) return;
        cm_page = t;
        loadCourseModulesTab();
      });
    });

    cm_meta.textContent = `Page ${cur} of ${pages} — ${total} module(s)`;
  }catch(e){
    console.error(e);
    err(e.message || 'Failed to load course modules');
  }finally{
    showRowLoader(cm_loader, false);
  }
}

async function assignCourseModuleToBatch(moduleId, assigned){
  // normalize module id (number if possible, otherwise keep as string/uuid)
  const numericModuleId = Number(moduleId);
  const isNumeric = Number.isFinite(numericModuleId) && String(numericModuleId) === String(moduleId);
  
  // Build comprehensive payload with all possible field names
  // to ensure backend receives the data it expects
  const payload = {
    // ✅ REQUIRED by your backend - course identifiers
    course_id: currentCourseId || null,
    course_uuid: currentCourseUuid || null,

    // batch identifiers
    batch_id: mod_batch_id || null,
    batch_uuid: mod_batch_uuid || null,
    batch_key: mod_batch_key || null,

    // module identifiers
    course_module_id: isNumeric ? numericModuleId : null,
    course_module_uuid: !isNumeric ? String(moduleId) : null,
    module_id: isNumeric ? numericModuleId : null,
    module_uuid: !isNumeric ? String(moduleId) : null,

    // Send assigned status in multiple formats for backend compatibility
    assigned: !!assigned,
    is_assigned: !!assigned,
    assign: !!assigned,
    
    // Also send action field in case backend expects it
    action: assigned ? 'assign' : 'unassign',
    status: assigned ? 'assigned' : 'unassigned'
  };

  const res = await fetch(`/api/batch-course-modules/assign`, {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + TOKEN,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  const j = await res.json().catch(()=>({}));
  if(!res.ok) {
    // Enhanced error logging
    console.error('Assignment API error:', {
      status: res.status,
      statusText: res.statusText,
      moduleId: moduleId,
      assigned: assigned,
      payload: payload,
      response: j
    });
    throw new Error(j?.message || firstError(j) || 'Assign failed');
  }
  
  // Log success for debugging (can be removed in production)
  console.log('Assignment successful:', {
    moduleId,
    assigned,
    response: j
  });
  
  return j;
}
function toIntOrNull(v){
  const n = Number(v);
  return Number.isFinite(n) ? Math.trunc(n) : null;
}
function getOrderVal(x){
  const raw = (x?.display_order ?? x?.order ?? x?.position ?? null);
  const n = toIntOrNull(raw);
  return (n === null || n <= 0) ? null : n;
}
function sortByDisplayOrder(a, b){
  const ao = getOrderVal(a);
  const bo = getOrderVal(b);

  // null/empty orders go to bottom
  if (ao === null && bo === null) return 0;
  if (ao === null) return 1;
  if (bo === null) return -1;

  if (ao !== bo) return ao - bo;

  // stable-ish tie breaker
  const at = String(a?.title ?? a?.module_title ?? a?.name ?? '');
  const bt = String(b?.title ?? b?.module_title ?? b?.name ?? '');
  return at.localeCompare(bt);
}

function sortBatchRowsDom(){
  const rows = Array.from(bm_rows_mod.querySelectorAll('tr'))
    .filter(r => r.id !== 'bm_loader_mod');

  rows.sort((r1, r2) => {
    const a = toIntOrNull(r1.dataset.order) ?? 999999;
    const b = toIntOrNull(r2.dataset.order) ?? 999999;
    if (a !== b) return a - b;
    return String(r1.dataset.title || '').localeCompare(String(r2.dataset.title || ''));
  });

  rows.forEach(r => bm_rows_mod.appendChild(r));
}

async function updateBcmOrderOnly(idOrUuid, newOrder){
  const payload = {
    display_order: newOrder,
    order: newOrder,
    position: newOrder
  };

  const res = await fetch(`/api/batch-course-modules/${encodeURIComponent(idOrUuid)}`, {
    method:'PUT',
    headers:{ 'Authorization':'Bearer '+TOKEN, 'Content-Type':'application/json', 'Accept':'application/json' },
    body: JSON.stringify(payload)
  });

  const j = await res.json().catch(()=>({}));
  if(!res.ok) throw new Error(j?.message || firstError(j) || 'Order update failed');
  return j;
}

async function loadBatchModulesTab(){
  if(!mod_batch_uuid) return;
  showRowLoader(bm_loader_mod, true);
  bm_rows_mod.querySelectorAll('tr:not(#bm_loader_mod)').forEach(tr=>tr.remove());
  bm_pager_mod.innerHTML = '';
  bm_meta_mod.textContent = '—';

  try{
    const res = await fetch(`/api/batch-course-modules?${bmParamsMod()}`, {
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || 'Failed to load batch modules');

const items = pickList(j).slice().sort(sortByDisplayOrder);
    const pag = pickPag(j, items.length, bm_per_mod?.value, bm_page_mod);

    const frag = document.createDocumentFragment();

    items.forEach(x=>{
  const idOrUuid = x.uuid ?? x.id ?? x.batch_course_module_uuid ?? x.batch_course_module_id ?? '';
  const title = x.title ?? x.module_title ?? x.name ?? 'Untitled';
  const orderNum = getOrderVal(x);
  const completed = !!(x.completed ?? x.is_completed ?? x.completed_status);

  const tr = document.createElement('tr');

  // ✅ store order/title for DOM sort
  tr.dataset.order = orderNum ?? '';
  tr.dataset.title = String(title);

  tr.innerHTML = `
    <td class="fw-semibold">${esc(title)}</td>

    <td style="width:170px;">
      <div class="input-group input-group-sm">
        <input type="number" min="1" step="1"
          class="form-control bcm-order-input"
          value="${orderNum ?? ''}"
          data-id="${esc(idOrUuid)}"
          placeholder="—">
        <button class="btn btn-outline-primary bcm-order-save"
          type="button"
          data-id="${esc(idOrUuid)}">
          Save
        </button>
      </div>
      <div class="small text-muted mt-1 bcm-order-msg" data-id="${esc(idOrUuid)}"></div>
    </td>

    <td>
      <div class="d-flex align-items-center gap-2">
        <span class="badge ${completed ? 'badge-success' : 'badge-secondary'}">${completed ? 'Completed' : 'Not Completed'}</span>
        <button class="btn btn-light btn-sm bcm-toggle d-none" data-id="${esc(idOrUuid)}">
          <i class="fa fa-rotate"></i> Toggle
        </button>
      </div>
    </td>

    <td class="text-end">
      <button class="btn btn-primary btn-sm bcm-edit" data-id="${esc(idOrUuid)}">
        <i class="fa fa-pen-to-square me-1"></i>Edit
      </button>
    </td>
  `;
  frag.appendChild(tr);
});

    bm_rows_mod.appendChild(frag);
// ✅ inline order save + instant rearrange
bm_rows_mod.querySelectorAll('.bcm-order-save').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    const idOrUuid = btn.dataset.id;
    const input = bm_rows_mod.querySelector(`.bcm-order-input[data-id="${CSS.escape(idOrUuid)}"]`);
    const msg = bm_rows_mod.querySelector(`.bcm-order-msg[data-id="${CSS.escape(idOrUuid)}"]`);
    if(!input) return;

    const newOrder = toIntOrNull(input.value);
    if(!newOrder || newOrder <= 0){
      err('Display order must be a positive number');
      return;
    }

    btn.disabled = true;
    input.disabled = true;
    if(msg) msg.textContent = 'Saving…';

    try{
      await updateBcmOrderOnly(idOrUuid, newOrder);
      ok('Order updated');

      // ✅ update row dataset and re-sort DOM immediately
      const row = input.closest('tr');
      if(row){
        row.dataset.order = String(newOrder);
        sortBatchRowsDom();
      }

      if(msg) msg.textContent = 'Saved.';
    }catch(e){
      err(e.message || 'Order update failed');
      if(msg) msg.textContent = 'Failed.';
    }finally{
      btn.disabled = false;
      input.disabled = false;
      setTimeout(()=>{ if(msg) msg.textContent = ''; }, 1200);
    }
  });
});

// allow Enter to save
bm_rows_mod.querySelectorAll('.bcm-order-input').forEach(inp=>{
  inp.addEventListener('keydown', (e)=>{
    if(e.key === 'Enter'){
      e.preventDefault();
      const idOrUuid = inp.dataset.id;
      bm_rows_mod.querySelector(`.bcm-order-save[data-id="${CSS.escape(idOrUuid)}"]`)?.click();
    }
  });
});

    // toggle completed
    bm_rows_mod.querySelectorAll('.bcm-toggle').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const idOrUuid = btn.dataset.id;
        btn.disabled = true;
        try{
          const res = await fetch(`/api/batch-course-modules/${encodeURIComponent(idOrUuid)}/toggle-completed`,{
            method:'PATCH',
            headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
          });
          const j = await res.json().catch(()=>({}));
          if(!res.ok) throw new Error(j?.message || firstError(j) || 'Toggle failed');
          ok('Completed status updated');
          loadBatchModulesTab();
        }catch(e){
          err(e.message || 'Toggle failed');
        }finally{
          btn.disabled = false;
        }
      });
    });

    // edit
    bm_rows_mod.querySelectorAll('.bcm-edit').forEach(btn=>{
      btn.addEventListener('click', ()=> openBcmEdit(btn.dataset.id));
    });

    // pager
    const total = Number(pag.total||items.length);
    const per = Number(pag.per_page||bm_per_mod?.value||20);
    const cur = Number(pag.current_page||pag.page||bm_page_mod||1);
    const pages = Math.max(1, Math.ceil(total/per));

    let html = '';
    html += li(cur<=1,false,'Prev',cur-1);
    const w=2,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    for(let i=s;i<=e;i++) html += li(false,i===cur,i,i);
    html += li(cur>=pages,false,'Next',cur+1);
    bm_pager_mod.innerHTML = html;

    bm_pager_mod.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click', ()=>{
        const t = Number(a.dataset.page);
        if(!t || t===bm_page_mod) return;
        bm_page_mod = t;
        loadBatchModulesTab();
      });
    });

    bm_meta_mod.textContent = `Page ${cur} of ${pages} — ${total} item(s)`;
  }catch(e){
    console.error(e);
    err(e.message || 'Failed to load batch modules');
  }finally{
    showRowLoader(bm_loader_mod, false);
  }
}

/* ===== Edit Batch Course Module ===== */
const bcmEditModalEl = document.getElementById('bcmEditModal');
const bcm_id = document.getElementById('bcm_id');
const bcm_order = document.getElementById('bcm_order');
const bcm_completed = document.getElementById('bcm_completed');
const bcm_meta = document.getElementById('bcm_meta');
const bcm_save = document.getElementById('bcm_save');

let bcmEditModal;

async function openBcmEdit(idOrUuid){
  bcmEditModal = bcmEditModal || new bootstrap.Modal(bcmEditModalEl);
  bcm_id.value = idOrUuid || '';
  bcm_meta.textContent = 'Loading…';
  bcm_order.value = '';
  bcm_completed.checked = false;
  bcmEditModal.show();

  try{
    const res = await fetch(`/api/batch-course-modules/${encodeURIComponent(idOrUuid)}`, {
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || 'Failed to load module');
    const x = j?.data || j;

    bcm_order.value = x.display_order ?? x.order ?? x.position ?? '';
    bcm_completed.checked = !!(x.completed ?? x.is_completed ?? x.completed_status);
    bcm_meta.textContent = 'Ready.';
  }catch(e){
    bcm_meta.textContent = 'Failed to load.';
    err(e.message);
  }
}

bcm_save?.addEventListener('click', async ()=>{
  const idOrUuid = bcm_id.value;
  if(!idOrUuid) return;

  bcm_save.disabled = true;
  bcm_meta.textContent = 'Saving…';

  try{
    const payload = {
      display_order: bcm_order.value !== '' ? Number(bcm_order.value) : null,
      order: bcm_order.value !== '' ? Number(bcm_order.value) : null,
      completed: !!bcm_completed.checked,
      is_completed: !!bcm_completed.checked
    };

    const res = await fetch(`/api/batch-course-modules/${encodeURIComponent(idOrUuid)}`, {
      method:'PUT',
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Content-Type':'application/json', 'Accept':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Update failed');

    ok('Batch module updated');
    bootstrap.Modal.getOrCreateInstance(bcmEditModalEl).hide();
    loadBatchModulesTab();
  }catch(e){
    err(e.message || 'Update failed');
  }finally{
    bcm_save.disabled = false;
    bcm_meta.textContent = '—';
  }
});

/* ===== Settings ===== */
async function loadModuleSettings(){
  if(!mod_batch_uuid) return;
  set_meta.textContent = 'Loading…';

  try{
    // ✅ Build query params with REQUIRED course identifiers
    const params = new URLSearchParams();
    if (currentCourseId) params.set('course_id', currentCourseId);
    if (currentCourseUuid) params.set('course_uuid', currentCourseUuid);

    const url = `/api/batch-course-modules/${encodeURIComponent(mod_batch_uuid)}/settings?${params.toString()}`;
    
    const res = await fetch(url, {
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || 'Failed to load settings');

    const s = j?.data?.settings || j?.data || j?.settings || {};

    // accept snake_case or camelCase from backend
    set_prev_completed.checked = !!(s.previous_module_completed ?? s.previousModuleCompleted ?? 0);
    set_assignments_submitted.checked = !!(s.assignment_submitted ?? s.assignmentSubmitted ?? s.assignments_submitted ?? s.assignmentsSubmitted ?? 0);
    set_exams_submitted.checked = !!(s.exam_submitted ?? s.examSubmitted ?? s.exams_submitted ?? s.examsSubmitted ?? 0);
    set_coding_test_submitted.checked = !!(s.coding_test_submitted ?? s.codingTestSubmitted ?? 0);

    set_meta.textContent = 'Loaded.';
  }catch(e){
    set_meta.textContent = 'Failed to load.';
    console.error('Settings load error:', e);
    err(e.message || 'Failed to load settings');
  }
}

async function saveModuleSettings(){
  if(!mod_batch_uuid) return;
  set_save.disabled = true;
  set_meta.textContent = 'Saving…';

  try{
    const payload = {
      // ✅ REQUIRED: course identifiers (backend validation requires these)
      course_id: currentCourseId || null,
      course_uuid: currentCourseUuid || null,

      // settings_json: send as object (backend will json_encode it)
      settings_json: {
        previous_module_completed: set_prev_completed.checked ? 1 : 0,
        assignment_submitted: set_assignments_submitted.checked ? 1 : 0,
        exam_submitted: set_exams_submitted.checked ? 1 : 0,
        coding_test_submitted: set_coding_test_submitted.checked ? 1 : 0
      }
    };

    const res = await fetch(`/api/batch-course-modules/${encodeURIComponent(mod_batch_uuid)}/settings`, {
      method:'POST',
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Content-Type':'application/json', 'Accept':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Save failed');

    ok('Settings saved');
    set_meta.textContent = 'Saved.';
  }catch(e){
    set_meta.textContent = 'Save failed.';
    console.error('Settings save error:', e);
    err(e.message || 'Failed to save settings');
  }finally{
    set_save.disabled = false;
  }
}

function setToolbarEnabled(on){
  [q, perPageSel, btnFilter, btnReset, btnCreate].forEach(el=> el.disabled = !on);
  courseHint.style.display = on ? 'none' : '';
}

function syncSortHeaders(){
  document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{
    th.classList.remove('asc','desc');
    const col = th.dataset.col;
    if(sort===col) th.classList.add('asc');
    if(sort==='-'+col) th.classList.add('desc');
  });
}

function wiring(){
  document.querySelectorAll('#tab-active thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col=th.dataset.col;
      if(sort===col) sort='-'+col; else if(sort==='-'+col) sort=col; else sort=(col==='created_at'||col==='starts_at')?('-'+col):col;
      state.active.page=1; syncSortHeaders(); load('active');
    });
  });
  
  let srT; q.addEventListener('input', ()=>{ clearTimeout(srT); srT=setTimeout(()=>{ state.active.page=1; load('active'); }, 350); });
  
  // Proper modal event handling with backdrop cleanup
  const filterModal = document.getElementById('filterModal');
  const filterModalInstance = new bootstrap.Modal(filterModal, {
    backdrop: true,
    keyboard: true,
    focus: true
  });
  
  filterModal.addEventListener('show.bs.modal', () => {
    document.getElementById('modal_mode').value = filterState.mode || '';
    document.getElementById('modal_status').value = filterState.status || '';
    document.getElementById('modal_sort').value = sort || '-created_at';
  });

  filterModal.addEventListener('shown.bs.modal', () => {
    // Focus first element when modal opens
    document.getElementById('modal_mode').focus();
  });

  filterModal.addEventListener('hide.bs.modal', () => {
    // Remove focus from apply button before modal closes
    document.getElementById('btnApplyFilters').blur();
  });

  filterModal.addEventListener('hidden.bs.modal', () => {
    // Clean up any lingering backdrop
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
      if (backdrop.parentNode) {
        backdrop.parentNode.removeChild(backdrop);
      }
    });
    
    // Remove any modal-open classes from body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    
    // Return focus to filter button after modal closes
    setTimeout(() => {
      document.getElementById('btnFilter').focus();
    }, 100);
  });

  document.getElementById('btnApplyFilters').addEventListener('click', (e) => {
    e.preventDefault();
    filterState.mode = document.getElementById('modal_mode').value || '';
    filterState.status = document.getElementById('modal_status').value || '';
    sort = document.getElementById('modal_sort').value || '-created_at';
    state.active.page = 1;
    syncSortHeaders();
    
    // Remove focus before closing
    document.getElementById('btnApplyFilters').blur();
    
    // Close modal and then load data
    filterModalInstance.hide();
    
    // Force cleanup of backdrop
    setTimeout(() => {
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(backdrop => backdrop.remove());
      document.body.classList.remove('modal-open');
    }, 150);
    
    setTimeout(() => {
      load('active');
    }, 200);
  });

  btnReset.addEventListener('click', ()=>{ 
    q.value=''; 
    perPageSel.value='20'; 
    sort='-created_at'; 
    filterState.mode = '';
    filterState.status = '';
    state.active.page=1; 
    syncSortHeaders(); 
    load('active'); 
  });
  
  perPageSel.addEventListener('change', ()=>{ state.active.page=1; load('active'); });
  
  courseSel.addEventListener('change', ()=>{
  currentCourseId = courseSel.value || '';
  currentCourseUuid = courseSel.options[courseSel.selectedIndex]?.dataset?.uuid || '';

  const on = !!(currentCourseId || currentCourseUuid);
  setToolbarEnabled(on);
  clearAllTables();

  if(on){
    state.active.page=1;
    load('active');
  }
});

  btnCreate.addEventListener('click', ()=>{ if(!currentCourseId) return Swal.fire('Pick a course','Please select a course first.','info'); openCreateModal(); });
  
  document.querySelector('a[href="#tab-active"]').addEventListener('shown.bs.tab', ()=>{ if(currentCourseId) load('active'); });
  document.querySelector('a[href="#tab-archived"]').addEventListener('shown.bs.tab', ()=>{ if(currentCourseId) load('archived'); });
  document.querySelector('a[href="#tab-bin"]').addEventListener('shown.bs.tab', ()=>{ if(currentCourseId) load('bin'); });

  document.addEventListener('click',(e)=>{
    const item=e.target.closest('.dropdown-item[data-act]'); if(!item) return;
    e.preventDefault(); const act=item.dataset.act, uuid=item.dataset.uuid, name=item.dataset.name || 'this batch';
    if(act==='view') openView(uuid);
    if(act==='edit') openEditModal(uuid);
    if(act==='instructors') openInstructors(uuid);
    if(act==='quizzes') openQuizzes(uuid);            // <-- Assign Quiz action
    if(act==='coding') openCodingQuestions(item.dataset.batch || uuid);  
if(act==='modules') {
  const bid = item.dataset.batchId || item.dataset.batch || '';
  const buu = item.dataset.batchUuid || uuid || '';
  openModules(bid, buu);
}


    if(act==='assign') openStudents(uuid);
    if(act==='archive') return archiveBatch(uuid);
    if(act==='unarchive') return unarchiveBatch(uuid);
    if(act==='delete') return deleteBatch(uuid);
    if(act==='restore') return restoreBatch(uuid);
    const toggle=item.closest('.dropdown')?.querySelector('.dd-toggle'); if(toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
  });
}

function clearAllTables(){
  ['active','archived','bin'].forEach(scope=>{
    const rowsEl=document.querySelector(tabs[scope].rows);
    rowsEl.querySelectorAll('tr:not([id^="loaderRow"]):not([id^="askCourse"])').forEach(n=>n.remove());
    document.querySelector(tabs[scope].empty).style.display='none';
    document.querySelector(tabs[scope].ask).style.display = currentCourseId ? 'none' : '';
    document.querySelector(tabs[scope].pager).innerHTML='';
    document.querySelector(tabs[scope].meta).textContent='—';
  });
}

async function loadCourses(){
  try{
    const res = await fetch('/api/courses?status=published&per_page=1000', {
      headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const j = await res.json();
    let items = [];
    if (Array.isArray(j)) items = j;
    else if (j?.data && Array.isArray(j.data)) items = j.data;
    else if (j?.courses && Array.isArray(j.courses)) items = j.courses;
    else if (j?.success && Array.isArray(j.data)) items = j.data;
    
    courseSel.innerHTML = '<option value="">Select a course…</option>' + 
      items.map(c => `<option value="${c.id}" data-uuid="${esc(c.uuid||'')}">${esc(c.title||'(untitled)')}</option>`).join('');
    setToolbarEnabled(false);
  } catch(e) { 
    console.error('Course load error:', e);
    err(e.message || 'Course list error'); 
  }
}

function applyFromURL(){}

function rowActions(scope, r){
  if(scope==='active'){
    return `<div class="dropdown text-end" data-bs-display="static"><button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="fa fa-ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><button class="dropdown-item" data-act="view" data-uuid="${r.uuid}"><i class="fa fa-circle-info"></i> View Batch</button></li><li><button class="dropdown-item" data-act="edit" data-uuid="${r.uuid}"><i class="fa fa-pen-to-square"></i> Edit Batch</button></li><li><button class="dropdown-item" data-act="instructors" data-uuid="${r.uuid}"><i class="fa fa-chalkboard-user"></i> Assign Instructor</button></li><li><button class="dropdown-item" data-act="quizzes" data-uuid="${r.uuid}"><i class="fa fa-question"></i> Assign Quiz</button></li><li>
  <button class="dropdown-item" data-act="coding" data-uuid="${r.uuid}" data-batch="${r.id || r.uuid}">
    <i class="fa fa-code"></i> Assign Coding Question
  </button>
</li>
<li>
<button class="dropdown-item"
  data-act="modules"
  data-batch-id="${r.id || ''}"
  data-batch-uuid="${r.uuid || ''}">
  <i class="fa fa-layer-group"></i> Manage Course Modules
</button>
  
</li>

<li><button class="dropdown-item" data-act="assign" data-uuid="${r.uuid}"><i class="fa fa-user-plus"></i> Manage Students</button></li><li><hr class="dropdown-divider"></li><li><button class="dropdown-item" data-act="archive" data-uuid="${r.uuid}"><i class="fa fa-box-archive"></i> Archive</button></li><li><button class="dropdown-item text-danger" data-act="delete" data-uuid="${r.uuid}"><i class="fa fa-trash"></i> Delete</button></li></ul></div>`;
  }
  if(scope==='archived'){
    return `<div class="dropdown text-end" data-bs-display="static"><button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><button class="dropdown-item" data-act="view" data-uuid="${r.uuid}"><i class="fa fa-circle-info"></i> View Batch</button></li><li><hr class="dropdown-divider"></li><li><button class="dropdown-item" data-act="unarchive" data-uuid="${r.uuid}"><i class="fa fa-box-open"></i> Unarchive</button></li><li><button class="dropdown-item text-danger" data-act="delete" data-uuid="${r.uuid}"><i class="fa fa-trash"></i> Delete</button></li></ul></div>`;
  }
  return `<div class="dropdown text-end" data-bs-display="static"><button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><button class="dropdown-item" data-act="restore" data-uuid="${r.uuid}"><i class="fa fa-rotate-left"></i> Restore</button></li></ul></div>`;
}

function rowHTML(scope, r){
  const tr=document.createElement('tr');
  // Use better default image
  const img = esc(r.featured_image||'https://dummyimage.com/96x64/e9e3f5/5e1570.jpg&text=Batch');
  const dur = (Number(r.duration_days||0)>0) ? (r.duration_days+' days') : '-';
  const created = fmtDateTime(r.created_at);
  const delAt = fmtDateTime(r.deleted_at);

  if((r.status||'').toLowerCase()==='archived' && scope!=='archived' && scope!=='bin') tr.classList.add('state-archived');
  if(r.deleted_at || scope==='bin') tr.classList.add('state-deleted');

  const head = `<div class="bcell"><img class="bthumb" src="${img}" onerror="this.src='https://dummyimage.com/96x64/e9e3f5/5e1570.jpg&text=Batch';this.onerror=null;"><div><div class="fw-semibold">${esc(r.badge_title||'Untitled')}</div><div class="small text-muted">${esc(r.tagline||'')}</div></div></div>`;

  if(scope==='active'){
    tr.innerHTML = `<td>${head}</td><td class="text-capitalize">${esc(r.mode||'-')}</td><td>${badgeStatus(r.status)}</td><td>${fmtDate(r.starts_at)}</td><td>${fmtDate(r.ends_at)}</td><td>${dur}</td><td>${created}</td><td class="text-end">${rowActions(scope,r)}</td>`;
    return tr;
  }
  if(scope==='archived'){
    tr.innerHTML = `<td>${head}</td><td class="text-capitalize">${esc(r.mode||'-')}</td><td>${created}</td><td class="text-end">${rowActions(scope,r)}</td>`;
    return tr;
  }
  tr.innerHTML = `<td>${head}</td><td class="text-capitalize">${esc(r.mode||'-')}</td><td>${delAt}</td><td class="text-end">${rowActions(scope,r)}</td>`;
  return tr;
}

function baseParams(scope){
  const usp=new URLSearchParams();
  usp.set('course_id', currentCourseId);
  const per=Number(perPageSel?.value || 20);
  const pg = Number(state[scope].page||1);
  usp.set('per_page', per);
  usp.set('page', pg);
  
  if(scope==='active'){
    usp.set('sort', sort);
    const searchVal = q?.value?.trim();
    if(searchVal) usp.set('q', searchVal);
    if (filterState.mode) usp.set('mode', filterState.mode);
    if (filterState.status) usp.set('status', filterState.status);
  }else if(scope==='archived'){
    usp.set('status','archived'); 
    usp.set('sort','-created_at');
  }else{
    usp.set('only_deleted','1'); 
    usp.set('sort','-created_at');
  }
  return usp.toString();
}

function urlFor(scope){ return '/api/batches?' + baseParams(scope); }
function show(el,v){ el.style.display = v ? '' : 'none'; }
function clearBody(scope){ const rowsEl=document.querySelector(tabs[scope].rows); rowsEl.querySelectorAll('tr:not([id^="loaderRow"]):not([id^="askCourse"])').forEach(n=>n.remove()); }
function showLoader(scope,v){ show(document.querySelector(tabs[scope].loader), v); }

async function load(scope){
  if(!currentCourseId) return;
  const refs=tabs[scope], rowsEl=document.querySelector(refs.rows), empty=document.querySelector(refs.empty), ask=document.querySelector(refs.ask), pager=document.querySelector(refs.pager), meta=document.querySelector(refs.meta);
  show(ask,false); clearBody(scope); show(empty,false); pager.innerHTML=''; meta.textContent='—'; showLoader(scope,true);
  
  const fetchUrl = urlFor(scope);
  console.log('Fetching:', fetchUrl);
  
  try{
    const res = await fetch(fetchUrl, {headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json','Cache-Control':'no-cache'}});
    const j = await res.json(); 
    if(!res.ok) throw new Error(j?.message||'Load failed');
    
    let items = j?.data || [];
    const pag = j?.pagination || j?.meta || {page:1, per_page:Number(perPageSel?.value||20), total:items.length};

    if(scope==='active' && !filterState.status){
      items = items.filter(r=> String(r.status||'').toLowerCase() !== 'archived' && !r.deleted_at);
    }

    if(items.length===0) show(empty,true);
    const frag=document.createDocumentFragment(); items.forEach(r=> frag.appendChild(rowHTML(scope,r))); rowsEl.appendChild(frag);

    const total=Number(pag.total||items.length), per=Number(pag.per_page||20), cur=Number(pag.page||pag.current_page||1);
    const pages=Math.max(1, Math.ceil(total/per));
    const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
    let html=''; html+=li(cur<=1,false,'Previous',cur-1);
    const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
    for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
    if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
    html+=li(cur>=pages,false,'Next',cur+1);
    pager.innerHTML=html;
    pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{ const t=Number(a.dataset.page); if(!t || t===state[scope].page) return; state[scope].page = Math.max(1,t); load(scope); window.scrollTo({top:0,behavior:'smooth'}); }));
    meta.textContent = `Showing page ${cur} of ${pages} — ${total} result(s)`;
  }catch(e){ 
    console.error('Load error:', e); 
    show(empty,true); 
    document.querySelector(tabs[scope].meta).textContent='Failed to load'; 
    err(e.message||'Load error'); 
  }
  finally{ showLoader(scope,false); if(scope==='active') syncSortHeaders(); }
}

async function openView(uuid){
  const modal=new bootstrap.Modal(document.getElementById('viewModal'));
  const vBody=document.getElementById('vBody'); vBody.innerHTML='Loading…'; modal.show();
  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Load failed');
    const r=j.data||{};
    let linksHtml = '<div class="small text-muted">—</div>';
    if (r.group_links && typeof r.group_links === 'object' && !Array.isArray(r.group_links)) {
      const entries = Object.entries(r.group_links);
      if (entries.length) linksHtml = entries.map(([k,u])=>`<div class="small"><b>${esc(k)}:</b> <a href="${esc(u)}" target="_blank">${esc(u)}</a></div>`).join('');
    } else if (Array.isArray(r.group_links) && r.group_links.length){
      linksHtml = r.group_links.map(u=>`<div class="small"><a href="${esc(u)}" target="_blank">${esc(u)}</a></div>`).join('');
    }
    const dur = (Number(r.duration_days||0)>0) ? (r.duration_days+' days') : '-';
    vBody.innerHTML = `<div class="d-flex gap-3 align-items-start"><img class="bthumb" style="width:120px;height:80px" src="${esc(r.featured_image||'https://dummyimage.com/200x120/e9e3f5/5e1570.jpg&text=Batch')}" onerror="this.src='https://dummyimage.com/200x120/e9e3f5/5e1570.jpg&text=Batch';this.onerror=null;"><div><div class="h5 mb-1">${esc(r.badge_title||'Untitled')}</div><div class="text-muted small">${esc(r.tagline||'')}</div><div class="mt-1">${badgeStatus(r.status)} <span class="ms-2 badge badge-info text-uppercase">${esc(r.mode||'-')}</span></div></div></div><hr class="my-3"><div class="row g-3"><div><span class="text-muted small">Duration:</span> <strong>${dur}</strong></div><div class="col-md-6"><div><span class="text-muted small">Contact:</span> ${esc(r.contact_number||'-')}</div><div><span class="text-muted small">Note:</span> ${esc(r.badge_note||'-')}</div></div><div class="col-12"><div class="mb-1 fw-semibold">Group Links</div>${linksHtml}</div><div class="col-12"><div class="mb-1 fw-semibold">Description</div><div class="small">${(r.badge_description||'').length?esc(r.badge_description):'<span class="text-muted">—</span>'}</div></div></div>`;
  }catch(e){ vBody.innerHTML = `<div class="text-danger">${esc(e.message||'Failed to load')}</div>`; }
}
//Manage Instructor
const ins_q = document.getElementById('ins_q'),
      ins_per = document.getElementById('ins_per'),
      ins_apply = document.getElementById('ins_apply'),
      ins_assigned = document.getElementById('ins_assigned'),
      ins_rows = document.getElementById('ins_rows'),
      ins_loader = document.getElementById('ins_loader'),
      ins_meta = document.getElementById('ins_meta'),
      ins_pager = document.getElementById('ins_pager');

let instructorsModal, ins_uuid = null, ins_page = 1;
function instructorsParams(){
  const p = new URLSearchParams();
  if (ins_q.value.trim()) p.set('q', ins_q.value.trim());
  p.set('per_page', ins_per.value || 20);
  p.set('page', ins_page);
  if (ins_assigned.value === 'assigned') p.set('assigned', '1');
  if (ins_assigned.value === 'unassigned') p.set('assigned', '0');
  return p.toString();
}

function openInstructors(uuid){
  instructorsModal = instructorsModal || new bootstrap.Modal(document.getElementById('instructorsModal'));
  ins_uuid = uuid;
  ins_page = 1;
  ins_assigned.value = 'all';
  instructorsModal.show();
  loadInstructors();
}

ins_apply.addEventListener('click', ()=>{ ins_page = 1; loadInstructors(); });
ins_per.addEventListener('change', ()=>{ ins_page = 1; loadInstructors(); });
ins_assigned.addEventListener('change', ()=>{ ins_page = 1; loadInstructors(); });

let insT;
ins_q.addEventListener('input', ()=>{ clearTimeout(insT); insT = setTimeout(()=>{ ins_page = 1; loadInstructors(); }, 350); });

async function loadInstructors(){
  if (!ins_uuid) return;
  ins_loader.style.display = '';
  // remove existing rows except loader
  ins_rows.querySelectorAll('tr:not(#ins_loader)').forEach(tr=>tr.remove());

  try{
    const res = await fetch(`/api/batches/${encodeURIComponent(ins_uuid)}/instructors?` + instructorsParams(), {
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const j = await res.json();
    if(!res.ok) throw new Error(j?.message || 'Failed to load instructors');

    const items = j?.data || [];
    const pag = j?.pagination || { current_page: 1, per_page: Number(ins_per.value||20), total: items.length };

    const frag = document.createDocumentFragment();
    items.forEach(u=>{
      const assigned = !!u.assigned;
      const role = u.role_in_batch || 'instructor';
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-semibold">${esc(u.name||'-')}</td>
        <td>${esc(u.email||'-')}</td>
        <td>${esc(u.phone ?? u.phone_number ?? '-')}</td>
        <td>
          <select class="form-select form-select-sm ins-role" ${assigned ? '' : 'disabled'}>
            <option value="instructor" ${role==='instructor'?'selected':''}>Instructor</option>
            <option value="tutor" ${role==='tutor'?'selected':''}>Tutor</option>
            <option value="TA" ${role==='TA'?'selected':''}>TA</option>
            <option value="mentor" ${role==='mentor'?'selected':''}>Mentor</option>
          </select>
        </td>
        <td class="text-center">
          <div class="form-check form-switch d-inline-block">
            <input class="form-check-input ins-tg" type="checkbox" data-id="${u.id}" ${assigned?'checked':''}>
          </div>
        </td>`;
      frag.appendChild(tr);
    });
    ins_rows.appendChild(frag);

    // attach toggle listeners
    ins_rows.querySelectorAll('.ins-tg').forEach(ch=>{
      ch.addEventListener('change', ()=>{
        const row = ch.closest('tr');
        const roleSel = row?.querySelector('.ins-role');
        const roleVal = roleSel ? roleSel.value : 'instructor';
        toggleInstructor(Number(ch.dataset.id), ch.checked, ch, roleVal);
      });
    });

    // pagination
    const total = Number(pag.total||0), per = Number(pag.per_page||20), cur = Number(pag.current_page||1);
    const pages = Math.max(1, Math.ceil(total/per));
    function li(dis,act,label,t){ const c=['page-item',dis?'disabled':'',act?'active':''].filter(Boolean).join(' '); return `<li class="${c}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`; }
    let html=''; html+=li(cur<=1,false,'Prev',cur-1); const w=2,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
    html+=li(cur>=pages,false,'Next',cur+1);
    ins_pager.innerHTML = html;
    ins_pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click', ()=>{ const t=Number(a.dataset.page); if(!t||t===ins_page) return; ins_page = t; loadInstructors(); }));

    const label = ins_assigned.value==='all' ? 'All' : (ins_assigned.value==='assigned' ? 'Assigned' : 'Unassigned');
    ins_meta.textContent = `${label} — Page ${cur}/${pages} — ${total} instructor(s)`;
  }catch(e){
    err(e.message || 'Load error');
  }finally{
    ins_loader.style.display = 'none';
  }
}

async function toggleInstructor(userId, assigned, checkboxEl, roleVal){
  try{
    const body = assigned ? { user_id: userId, assigned: true, role_in_batch: roleVal || 'instructor' } : { user_id: userId, assigned: false };
    const res = await fetch(`/api/batches/${encodeURIComponent(ins_uuid)}/instructors/toggle`,{
      method:'POST',
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Content-Type':'application/json', 'Accept':'application/json' },
      body: JSON.stringify(body)
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Toggle failed');

    const row = checkboxEl.closest('tr');
    const roleSel = row?.querySelector('.ins-role');
    if (roleSel) roleSel.disabled = !assigned;
    ok(assigned ? 'Instructor assigned to batch' : 'Instructor unassigned');

    // refresh list if filtering hides this row
    if((ins_assigned.value==='assigned' && !assigned) || (ins_assigned.value==='unassigned' && assigned)){
      loadInstructors();
    }
  }catch(e){
    if (checkboxEl) checkboxEl.checked = !assigned;
    err(e.message);
  }
}
/* ================= MANAGE STUDENTS (updated) ================= */
const st_rows=document.getElementById('st_rows'), st_loader=document.getElementById('st_loader'), st_meta=document.getElementById('st_meta'), st_pager=document.getElementById('st_pager'), st_q=document.getElementById('st_q'), st_per=document.getElementById('st_per'), st_apply=document.getElementById('st_apply');
const st_assigned=document.getElementById('st_assigned');
const csvFile=document.getElementById('csvFile'), csvDrop=document.getElementById('csvDrop'), csvHint=document.getElementById('csvHint'), csvSummary=document.getElementById('csvSummary');
let studentsModal, st_uuid=null, st_page=1;

/* NEW: fetch list of user_ids that are 'not_verified' for the current batch */
async function fetchNotVerifiedIds(batchUuid){
  try{
    const res = await fetch(`/api/batches/${encodeURIComponent(batchUuid)}/students/not-verified`, {
      headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' }
    });
    if(!res.ok){
      // non-fatal: return empty set if endpoint fails
      try { const j = await res.json(); console.warn('not-verified fetch failed', j); } catch(_) {}
      return new Set();
    }
    const j = await res.json();
    // The API returns data as an array of user_ids. Support either {data: [...] } or raw array.
    const arr = Array.isArray(j) ? j : (Array.isArray(j?.data) ? j.data : []);
    return new Set(arr.map(x => Number(x)));
  }catch(e){
    console.error('Failed to fetch not-verified list', e);
    return new Set();
  }
}
function studentsParams(){ const p=new URLSearchParams(); if(st_q.value.trim()) p.set('q',st_q.value.trim()); p.set('per_page',st_per.value||20); p.set('page',st_page); if(st_assigned.value==='assigned') p.set('assigned','1'); if(st_assigned.value==='unassigned') p.set('assigned','0'); return p.toString(); }
function openStudents(uuid){ studentsModal = studentsModal || new bootstrap.Modal(document.getElementById('studentsModal')); st_uuid=uuid; st_page=1; st_assigned.value='all'; studentsModal.show(); loadStudents(); }
st_apply.addEventListener('click',()=>{ st_page=1; loadStudents(); }); st_per.addEventListener('change',()=>{ st_page=1; loadStudents(); }); st_assigned.addEventListener('change',()=>{ st_page=1; loadStudents(); });
let stT; st_q.addEventListener('input',()=>{ clearTimeout(stT); stT=setTimeout(()=>{ st_page=1; loadStudents(); },350); });
function badgeVerified(isVerified){
  if (!isVerified) return '';
  return `<span class="badge bg-success ms-2">Verified</span>`;
}

/* REPLACED loadStudents: now queries "not-verified" first and uses it to decide verification column visibility */
async function loadStudents(){
  if(!st_uuid) return;
  st_loader.style.display='';
  st_rows.querySelectorAll('tr:not(#st_loader)').forEach(tr=>tr.remove());

  // Fetch not-verified ids first (so UI can map show/hide verification toggles)
  const notVerifiedSet = await fetchNotVerifiedIds(st_uuid);

  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students?`+studentsParams(),{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
    const j=await res.json();
    if(!res.ok) throw new Error(j?.message||'Failed to load students');
    let items=j?.data||[];
    const pag=j?.pagination||{current_page:1,per_page:Number(st_per.value||20),total:items.length};

    if(st_assigned.value==='assigned') items = items.filter(u=> !!u.assigned);
    if(st_assigned.value==='unassigned') items = items.filter(u=> !u.assigned);

    const frag=document.createDocumentFragment();
    items.forEach(u=>{
      const tr=document.createElement('tr');

      // Determine values (backend may provide `enrollment_status` and `verified` boolean)
      const isNotVerified = notVerifiedSet.has(Number(u.id));
      
      // For not-verified students, select toggle should be OFF by default
      // For others, use the actual assigned status
      const shouldBeAssigned = isNotVerified ? false : !!u.assigned;
      const enrollmentStatus = u.enrollment_status || (shouldBeAssigned ? 'enrolled' : 'unassigned');
      const verified = !!u.verified;

      // Render row: Name | Email | Phone | Enrollment Status | Assigned toggle | Verified toggle
      // If user is NOT in notVerifiedSet, we hide the verify cell (display:none) to match your requirement.
      tr.innerHTML = `
        <td class="fw-semibold">${esc(u.name||'-')}</td>
        <td>${esc(u.email||'-')}</td>
        <td>${esc((u.phone_number ?? u.phone ?? '-'))}</td>
        <td class="text-center align-middle enrollment-cell">${badgeEnrollment(enrollmentStatus)}</td>
        <td class="text-center align-middle">
          <div class="form-check form-switch d-inline-block">
            <input class="form-check-input st-tg" type="checkbox" data-id="${u.id}" ${shouldBeAssigned?'checked':''}>
          </div>
        </td>
        <td class="text-center align-middle verify-cell" style="${isNotVerified ? '' : 'visibility:hidden;'}">
          <div class="form-check form-switch d-inline-block">
            <input class="form-check-input st-verify" type="checkbox" data-id="${u.id}" ${verified?'checked':''}>
          </div>
        </td>
      `;
      frag.appendChild(tr);
    });
    st_rows.appendChild(frag);

    /* ================= ASSIGN TOGGLE (existing) ================= */
   st_rows.querySelectorAll('.st-tg').forEach(ch=>{
      ch.addEventListener('change', ()=>{
        toggleStudent(Number(ch.dataset.id), ch.checked, ch);
      });
    });

    st_rows.querySelectorAll('.st-verify').forEach(ch=>{
      ch.addEventListener('change', async ()=>{
        const userId = Number(ch.dataset.id);
        const checked = !!ch.checked;
        const row = ch.closest('tr');
        const assignCheckbox = row?.querySelector('.st-tg');

        // If turning ON verification
        if(checked){
          // Always ensure student is assigned first
          if(assignCheckbox && !assignCheckbox.checked){
            // Optimistically update the select checkbox UI first
            assignCheckbox.checked = true;
            
            // Disable verify control while assign is in-flight
            ch.disabled = true;
            try{
              // Call the API to assign
              const res=await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students/toggle`,{
                method:'POST',
                headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},
                body:JSON.stringify({user_id:userId,assigned:true})
              });
              const j=await res.json().catch(()=>({}));
              if(!res.ok) throw new Error(j?.message||firstError(j)||'Toggle failed');
              
              ok('Student assigned to batch');
              
              // Update enrollment badge if provided
              const enrollCell = row.querySelector('.enrollment-cell');
              const newStatus = (j?.data?.enrollment_status) || 'enrolled';
              if(enrollCell) enrollCell.innerHTML = badgeEnrollment(newStatus);
              
              // After successful assignment, call verify API
              await verifyStudent(userId, true, ch);
            }catch(e){
              // Revert both checkboxes on error
              assignCheckbox.checked = false;
              ch.checked = false;
              err(e.message);
            } finally {
              ch.disabled = false;
            }
          } else {
            // Already assigned, just verify
            try{
              await verifyStudent(userId, true, ch);
            }catch(e){
              // verifyStudent handles revert and toasts
            }
          }
        } else {
          // Turning OFF verification - just update verification, don't touch assignment
          try{
            await verifyStudent(userId, false, ch);
          }catch(e){
            // verifyStudent handles revert and toasts
          }
        }
      });
    });

    const total=Number(pag.total||0), per=Number(pag.per_page||20), cur=Number(pag.current_page||1);
    const pages=Math.max(1,Math.ceil(total/per));
    function li(dis,act,label,t){ const c=['page-item',dis?'disabled':'',act?'active':''].filter(Boolean).join(' '); return `<li class="${c}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`; }
    let html='';
    html+=li(cur<=1,false,'Prev',cur-1);
    const w=2,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    for(let i=s;i<=e;i++) html+=li(false,i===cur,i);
    html+=li(cur>=pages,false,'Next',cur+1);
    st_pager.innerHTML=html;
    st_pager.querySelectorAll('a.page-link[data-page]').forEach(a=>a.addEventListener('click',()=>{
      const t=Number(a.dataset.page); if(!t||t===st_page) return; st_page=t; loadStudents();
    }));
    const label = st_assigned.value==='all' ? 'All' : (st_assigned.value==='assigned' ? 'Assigned' : 'Unassigned');
    st_meta.textContent=`${label} — Page ${cur}/${pages} — ${total} student(s)`;
  }catch(e){
    err(e.message);
  } finally{
    st_loader.style.display='none';
  }
}
/**
 * toggleStudent (assign/unassign)
 * - updates assignment on the server
 * - syncs verify checkbox enabled/disabled state
 */
async function toggleStudent(userId, assigned, checkboxEl){
  const row = checkboxEl.closest('tr');
  const verifyEl = row?.querySelector('.st-verify');
  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students/toggle`,{
      method:'POST',
      headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},
      body:JSON.stringify({user_id:userId,assigned:!!assigned})
    });
    const j=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message||firstError(j)||'Toggle failed');

    // Success: show toast
    ok(assigned ? 'Student assigned to batch' : 'Student removed from batch');

    // Update verify checkbox: enable only when assigned
    if(verifyEl){
      verifyEl.disabled = !assigned;
      if(!assigned){
        // also uncheck verification locally (server likely has removed association)
        verifyEl.checked = false;
      }
    }

    // Update enrollment badge cell if server sent back enrollment_status
    if(row){
      const enrollCell = row.querySelector('.enrollment-cell');
      const newStatus = (j?.data?.enrollment_status) || (assigned ? 'enrolled' : 'unassigned');
      if(enrollCell) enrollCell.innerHTML = badgeEnrollment(newStatus);
    }

    // If current filter view requires refresh (assigned/unassigned filter), reload
    if((st_assigned.value==='assigned' && !assigned) || (st_assigned.value==='unassigned' && assigned)){
      loadStudents();
    }
  }catch(e){
    // revert UI toggle if error
    if(checkboxEl) checkboxEl.checked = !assigned;
    err(e.message);
  }
}

/**
 * verifyStudent (new)
 * - calls POST /api/batches/{uuid}/students/{userId}/verify
 * - payload: { verified: true/false }
 * - reverts checkbox on error and updates enrollment badge on success if returned
 */
async function verifyStudent(userId, verified, checkboxEl){
  const row = checkboxEl.closest('tr');
  try{
    // optimistic: disable control while request in flight
    checkboxEl.disabled = true;
    const res = await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students/${encodeURIComponent(userId)}/verify`, {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ verified: !!verified })
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Verification failed');

    // Update UI based on response if provided
    // server may return { success: true, data: { verified: true, enrollment_status: 'enrolled' } }
    const data = j?.data || {};
    const newVerified = (typeof data.verified === 'boolean') ? data.verified : verified;
    const newEnrollmentStatus = data.enrollment_status || null;

    // reflect final checked state
    checkboxEl.checked = !!newVerified;
    ok(newVerified ? 'Student verified' : 'Verification removed');

    // update enrollment badge if server returned one
    if(newEnrollmentStatus && row){
      const enrollCell = row.querySelector('.enrollment-cell');
      if(enrollCell) enrollCell.innerHTML = badgeEnrollment(newEnrollmentStatus);
    }
  }catch(e){
    // revert checkbox state on error
    checkboxEl.checked = !verified;
    err(e.message);
  }finally{
    // only enable if still assigned
    const assignedCheckbox = row?.querySelector('.st-tg');
    const stillAssigned = !!assignedCheckbox?.checked;
    checkboxEl.disabled = !stillAssigned;
  }
}

/* CSV upload and other student helpers remain unchanged (kept from your original script) */
;['dragenter','dragover'].forEach(ev=> csvDrop?.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();csvDrop.classList.add('drag');}));
;['dragleave','drop'].forEach(ev=> csvDrop?.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();csvDrop.classList.remove('drag');}));
csvDrop?.addEventListener('drop',e=>{const files=e.dataTransfer?.files||[]; if(files.length) handleCsv(files[0]);});
document.getElementById('csvFile')?.addEventListener('change',()=>{ const f=document.getElementById('csvFile'); if(f.files?.length) handleCsv(f.files[0]); });
async function handleCsv(file){ if(!file || !/\.csv$/i.test(file.name)) return Swal.fire('Invalid file','Please choose a .csv file','info'); if(!studentsModal) studentsModal=new bootstrap.Modal(document.getElementById('studentsModal')); csvHint.textContent=`Uploading ${file.name}…`; const fd=new FormData(); fd.append('csv', file); try{ const res=await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students/upload-csv`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN},body:fd}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||firstError(j)||'Upload failed'); const s=j.summary||{}; csvSummary.style.display=''; csvSummary.innerHTML=`<div class="alert alert-success mb-2"><div class="fw-semibold mb-1">Import Summary</div><div class="small">Created users: <b>${s.created_users||0}</b></div><div class="small">Updated users: <b>${s.updated_users||0}</b></div><div class="small">Enrolled to batch: <b>${s.enrolled||0}</b></div></div>${(Array.isArray(s.errors)&&s.errors.length)?`<div class="alert alert-warning small"><div class="fw-semibold mb-1">Errors (${s.errors.length})</div>${s.errors.map(x=>`<div>• ${esc(x)}</div>`).join('')}</div>`:''}`; csvHint.textContent='Done.'; ok('CSV processed'); loadStudents(); }catch(e){ csvHint.textContent='Failed.'; err(e.message); } }

/* =================== BATCH FORM / EDITOR (unchanged) =================== */
const bm_title = document.getElementById('bm_title');
const bm_mode = document.getElementById('bm_mode');
const bm_uuid = document.getElementById('bm_uuid');
const bm_course_label = document.getElementById('bm_course_label');
const bm_course_id = document.getElementById('bm_course_id');
const bm_status = document.getElementById('bm_status');
const bm_title_input = document.getElementById('bm_title_input');
const bm_mode_select = document.getElementById('bm_mode_select');
const bm_tagline = document.getElementById('bm_tagline');
const bm_desc_editor = document.getElementById('bm_desc_editor');
const bm_contact = document.getElementById('bm_contact');
const bm_note = document.getElementById('bm_note');
const bm_start = document.getElementById('bm_start');
const bm_end = document.getElementById('bm_end');
const bm_dur_prev= document.getElementById('bm_duration_preview');
const bm_image = document.getElementById('bm_image');
const bm_img_prev= document.getElementById('bm_image_preview');
const bm_save = document.getElementById('bm_save');
const gl_wrap = document.getElementById('gl_wrap');
const gl_add = document.getElementById('gl_add');

/* =================== TEXT EDITOR SETUP =================== */
const bm_desc_ph = bm_desc_editor.nextElementSibling;

function toggleEditorPlaceholder(){
  const hasContent = (bm_desc_editor.textContent || '').trim().length > 0;
  bm_desc_editor.classList.toggle('has-content', hasContent);
}

// Initialize editor
['input','keyup','paste','blur'].forEach(ev => bm_desc_editor.addEventListener(ev, toggleEditorPlaceholder));
toggleEditorPlaceholder();

// Toolbar functionality
document.querySelectorAll('#batchModal .tool[data-cmd]').forEach(b=> {
  b.addEventListener('click',()=>{
    document.execCommand(b.dataset.cmd,false,null); 
    bm_desc_editor.focus(); 
    toggleEditorPlaceholder();
  });
});

document.querySelectorAll('#batchModal .tool[data-format]').forEach(b=> {
  b.addEventListener('click',()=>{
    document.execCommand('formatBlock',false,b.dataset.format); 
    bm_desc_editor.focus(); 
    toggleEditorPlaceholder();
  });
});

document.getElementById('btnLink').addEventListener('click',()=>{
  const u = prompt('Enter URL (https://…)'); 
  if(u && /^https?:\/\//i.test(u)){
    document.execCommand('createLink',false,u); 
    bm_desc_editor.focus();
  }
});

function addGlRow(keyVal, urlVal){ const row=document.createElement('div'); row.className='gl-row d-flex align-items-center gap-2'; row.innerHTML=`<input class="form-control gl-key" placeholder="Key (e.g., WhatsApp, Telegram)" value="${esc(keyVal||'')}"><input class="form-control gl-url" placeholder="https://…" value="${esc(urlVal||'')}"><button type="button" class="btn btn-light" title="Remove"><i class="fa fa-xmark"></i></button>`; row.querySelector('button').addEventListener('click',()=> row.remove()); gl_wrap.appendChild(row); }
function safeKey(k){ return (k||'').toString().trim().replace(/[^\w\-\.]/g,'_').substring(0,60) || 'Link'; }
function collectGroupLinks(){ const rows=[...gl_wrap.querySelectorAll('.gl-row')]; const map={}; const used=new Set(); let idx=1; rows.forEach(r=>{ let key=r.querySelector('.gl-key')?.value?.trim()||''; const url=r.querySelector('.gl-url')?.value?.trim()||''; if(!url) return; if(!key){ try{ key=new URL(url).hostname.replace(/^www\./,''); }catch(_){ key='Link '+idx; } } key=safeKey(key); let base=key,i=2; while(used.has(key)){ key=`${base}_${i++}`; } used.add(key); map[key]=url; idx++; }); return map; }

function resetBatchForm(){ 
  bm_status.value='active'; 
  bm_title_input.value=''; 
  bm_mode_select.value='hybrid'; 
  bm_tagline.value=''; 
  bm_desc_editor.innerHTML=''; 
  toggleEditorPlaceholder();
  bm_contact.value=''; 
  bm_note.value=''; 
  bm_start.value=''; 
  bm_end.value=''; 
  bm_dur_prev.value=''; 
  bm_image.value=''; 
  bm_img_prev.src='https://dummyimage.com/120x80/e9e3f5/5e1570.jpg&text=Batch+Image'; 
  gl_wrap.innerHTML=''; 
  addGlRow('',''); 
}

function openCreateModal(){ const batchModal=new bootstrap.Modal(document.getElementById('batchModal')); bm_mode.value='create'; bm_uuid.value=''; bm_title.textContent='Create Batch'; resetBatchForm(); bm_course_id.value=currentCourseId; bm_course_label.value=courseSel.options[courseSel.selectedIndex]?.text || ''; batchModal.show(); }

async function openEditModal(uuid){ 
  const batchModal=new bootstrap.Modal(document.getElementById('batchModal')); 
  bm_mode.value='edit'; 
  bm_uuid.value=uuid; 
  bm_title.textContent='Edit Batch'; 
  resetBatchForm(); 
  try{ 
    const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); 
    const j=await res.json(); 
    if(!res.ok) throw new Error(j?.message||'Load failed'); 
    const r=j.data||{}; 
    bm_course_id.value=r.course_id||currentCourseId||''; 
    bm_course_label.value=courseSel.options[courseSel.selectedIndex]?.text||''; 
    bm_status.value=r.status||'active'; 
    bm_title_input.value=r.badge_title||''; 
    bm_mode_select.value=r.mode||'online'; 
    bm_tagline.value=r.tagline||''; 
    bm_desc_editor.innerHTML = r.badge_description || '';
    toggleEditorPlaceholder();
    bm_contact.value=r.contact_number||''; 
    bm_note.value=r.badge_note||''; 
    bm_start.value=r.starts_at?r.starts_at.slice(0,10):''; 
    bm_end.value=r.ends_at?r.ends_at.slice(0,10):''; 
    bm_dur_prev.value=(r.starts_at&&r.ends_at)?humanYMD(r.starts_at,r.ends_at):''; 
    bm_img_prev.src=r.featured_image||'https://dummyimage.com/120x80/e9e3f5/5e1570.jpg&text=Batch+Image'; 
    gl_wrap.innerHTML=''; 
    if (r.group_links && typeof r.group_links === 'object' && !Array.isArray(r.group_links)) { 
      for (const [k,v] of Object.entries(r.group_links)) addGlRow(k||'', v||''); 
    } else if (Array.isArray(r.group_links) && r.group_links.length && typeof r.group_links[0] === 'object') { 
      r.group_links.forEach(x=> addGlRow(x.key||'', x.url||'')); 
    } else if (Array.isArray(r.group_links)) { 
      r.group_links.forEach(u=> addGlRow('', u||'')); 
    } 
    if(!gl_wrap.children.length) addGlRow('',''); 
    batchModal.show(); 
  }catch(e){ 
    err(e.message||'Failed to open editor'); 
  } 
}

gl_add.addEventListener('click', ()=> addGlRow('',''));
[bm_start,bm_end].forEach(el=> el.addEventListener('change', ()=>{ bm_dur_prev.value = (bm_start.value && bm_end.value) ? humanYMD(bm_start.value,bm_end.value) : ''; }));
bm_image.addEventListener('change', ()=>{ if (bm_image.files && bm_image.files[0]) bm_img_prev.src = URL.createObjectURL(bm_image.files[0]); else bm_img_prev.src='https://dummyimage.com/120x80/e9e3f5/5e1570.jpg&text=Batch+Image'; });
/* Replace old: bm_save.addEventListener('click', saveBatch);
   and the old async function saveBatch() {...}
   with the following: */

function setSaveLoading(on, text) {
  if (!bm_save) return;
  if (on) {
    bm_save.disabled = true;
    // store previous content to restore later
    if (!bm_save.dataset.prevHtml) bm_save.dataset.prevHtml = bm_save.innerHTML;
    bm_save.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>${esc(text || 'Saving…')}`;
    bm_save.setAttribute('aria-busy', 'true');
  } else {
    bm_save.disabled = false;
    if (bm_save.dataset.prevHtml) bm_save.innerHTML = bm_save.dataset.prevHtml;
    bm_save.removeAttribute('aria-busy');
    delete bm_save.dataset.prevHtml;
  }
}

bm_save.addEventListener('click', saveBatch);

async function saveBatch(){
  if(!bm_title_input.value.trim()) return Swal.fire('Title required','Please enter a badge title.','info');
  if(!bm_course_id.value) return Swal.fire('Course missing','Pick a course from the toolbar.','info');
  if(bm_start.value && bm_end.value && (new Date(bm_end.value) < new Date(bm_start.value))) return Swal.fire('Invalid dates','End date cannot be before start date.','info');

  const fd=new FormData();
  fd.append('course_id', bm_course_id.value);
  fd.append('badge_title', bm_title_input.value.trim());

  if(bm_desc_editor.innerHTML.trim()) fd.append('badge_description', bm_desc_editor.innerHTML.trim());
  if(bm_tagline.value.trim()) fd.append('tagline', bm_tagline.value.trim());
  fd.append('mode', bm_mode_select.value);
  if(bm_contact.value.trim()) fd.append('contact_number', bm_contact.value.trim());
  if(bm_note.value.trim()) fd.append('badge_note', bm_note.value.trim());
  fd.append('status', bm_status.value);
  if(bm_start.value) fd.append('starts_on', bm_start.value);
  if(bm_end.value) fd.append('ends_on', bm_end.value);
  const kv=collectGroupLinks();
  const keys=Object.keys(kv);
  if(keys.length){
    keys.forEach(k=> fd.append(`group_links[${k}]`, kv[k]));
  } else {
    fd.append('group_links[]','');
  }
  if(bm_image.files && bm_image.files[0]) fd.append('featured_image', bm_image.files[0]);

  // Turn on Save button loading UI
  setSaveLoading(true, 'Saving');

  try{
    let url='/api/batches', method='POST';
    if (bm_mode.value==='edit' && bm_uuid.value) {
      url = `/api/batches/${encodeURIComponent(bm_uuid.value)}`;
      fd.append('_method','PATCH');
      method = 'POST';
    }

    // show immediate table loader so users see activity in the list
    if (currentCourseId) {
      // clear current rows and show loader for active tab
      clearBody('active');
      showLoader('active', true);
      document.querySelector(tabs.active.meta).textContent = 'Saving…';
    }

    const res = await fetch(url,{
      method,
      headers:{
        'Authorization':'Bearer '+TOKEN,
        'Accept':'application/json',
        'Cache-Control':'no-cache'
      },
      body: fd
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(firstError(j)||'Save failed');

    ok('Batch saved');

    // Hide modal before reloading list (keeps modal closing smooth)
    bootstrap.Modal.getOrCreateInstance(document.getElementById('batchModal')).hide();

    // After save: reload active list (load() shows loader too). Keep the loader visible for a short moment.
    await load('active');

  }catch(e){
    console.error('Save error:', e);
    err(e.message||'Save failed');

    // If we showed the active loader earlier, hide it so user can retry
    showLoader('active', false);
  } finally {
    // restore Save button state
    setSaveLoading(false);
  }
}

/* =================== ARCHIVE / DELETE / RESTORE (unchanged) =================== */
async function archiveBatch(uuid){ const {isConfirmed}=await Swal.fire({icon:'question',title:'Archive batch?',showCancelButton:true,confirmButtonText:'Archive',confirmButtonColor:'#8b5cf6'}); if(!isConfirmed) return; try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}/archive`,{method:'PATCH',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Archive failed'); ok('Batch archived'); load('active'); }catch(e){ err(e.message); } }
async function unarchiveBatch(uuid){ try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{method:'PATCH',headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({status:'active'})}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Unarchive failed'); ok('Batch unarchived'); load('archived'); load('active'); }catch(e){ err(e.message); } }
async function deleteBatch(uuid){ const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete batch?',text:'This moves the batch to Bin (soft delete).',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'}); if(!isConfirmed) return; try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Delete failed'); ok('Batch deleted'); load('active'); }catch(e){ err(e.message); } }
async function restoreBatch(uuid){ try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}/restore`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Restore failed'); ok('Batch restored'); load('bin'); load('active'); }catch(e){ err(e.message); } }


/* ================= QUIZZES (Assign Quiz UI) ================= */
const qz_q = document.getElementById('qz_q'), qz_per = document.getElementById('qz_per'), qz_apply = document.getElementById('qz_apply'), qz_assigned = document.getElementById('qz_assigned'), qz_rows = document.getElementById('qz_rows'), qz_loader = document.getElementById('qz_loader'), qz_meta = document.getElementById('qz_meta'), qz_pager = document.getElementById('qz_pager');
let quizzesModal, qz_uuid=null, qz_page=1;
function quizzesParams(){ const p=new URLSearchParams(); if(qz_q.value.trim()) p.set('q', qz_q.value.trim()); p.set('per_page', qz_per.value || 20); p.set('page', qz_page); if(qz_assigned.value==='assigned') p.set('assigned','1'); if(qz_assigned.value==='unassigned') p.set('assigned','0'); return p.toString(); }
function openQuizzes(uuid){ quizzesModal = quizzesModal || new bootstrap.Modal(document.getElementById('quizzesModal')); qz_uuid = uuid; qz_page = 1; qz_assigned.value = 'all'; quizzesModal.show(); loadQuizzes(); }
qz_apply.addEventListener('click', ()=>{ qz_page=1; loadQuizzes(); }); qz_per.addEventListener('change', ()=>{ qz_page=1; loadQuizzes(); }); qz_assigned.addEventListener('change', ()=>{ qz_page=1; loadQuizzes(); });
let qzT; qz_q.addEventListener('input', ()=>{ clearTimeout(qzT); qzT = setTimeout(()=>{ qz_page=1; loadQuizzes(); }, 350); });

async function loadQuizzes(){
  if(!qz_uuid) return;
  qz_loader.style.display='';
  qz_rows.querySelectorAll('tr:not(#qz_loader)').forEach(tr=>tr.remove());

  try{
    const res = await fetch(`/api/batches/${encodeURIComponent(qz_uuid)}/quizzes?` + quizzesParams(), {
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });

    const j = await res.json();
    if(!res.ok) throw new Error(j?.message || 'Failed to load quizzes');

    let items = j?.data || [];
    const pag = j?.pagination || { current_page:1, per_page:Number(qz_per.value||20), total: items.length };

    if(qz_assigned.value==='assigned') items = items.filter(x=> !!x.assigned);
    if(qz_assigned.value==='unassigned') items = items.filter(x=> !x.assigned);

    const frag = document.createDocumentFragment();
    items.forEach(u=>{
      const assigned = !!u.assigned;
      const title = u.title || u.name || ('Quiz #'+(u.id||'?'));
      const publish = !!u.publish_to_students;

      // attempts from batch_quizzes.attempt_allowed
      const attemptsVal = (u.attempt_allowed !== null && u.attempt_allowed !== undefined)
                          ? u.attempt_allowed
                          : '';

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-semibold">${esc(title)}</td>

        <td>
            <input class="form-control form-control-sm qz-order"
                   type="number"
                   min="0"
                   value="${esc(attemptsVal)}"
                   style="width:110px">
        </td>

        <td class="text-center">
            <div class="form-check form-switch d-inline-block">
                <input class="form-check-input qz-pub" type="checkbox" ${publish ? 'checked' : ''}>
            </div>
        </td>

        <td class="text-center">
            <div class="form-check form-switch d-inline-block">
                <input class="form-check-input qz-tg" type="checkbox" data-id="${u.id}" ${assigned?'checked':''}>
            </div>
        </td>
      `;
      frag.appendChild(tr);
    });

    qz_rows.appendChild(frag);

    /* ================= ASSIGN TOGGLE ================= */
    qz_rows.querySelectorAll('.qz-tg').forEach(ch=>{
      ch.addEventListener('change', async ()=>{
        const row = ch.closest('tr');
        const quizId = Number(ch.dataset.id);
        const assigned = !!ch.checked;
        const pubEl = row.querySelector('.qz-pub');
        const attemptEl = row.querySelector('.qz-order');

        const payload = {
          quiz_id: quizId,
          assigned,
          publish_to_students: pubEl?.checked ?? false,
        };

        if(attemptEl && attemptEl.value !== '') {
          payload.attempt_allowed = Number(attemptEl.value);
        }

        try{
          await toggleQuiz(qz_uuid, payload, ch);

          if((qz_assigned.value==='assigned' && !assigned) ||
             (qz_assigned.value==='unassigned' && assigned)) loadQuizzes();

        }catch(e){}
      });
    });

    /* ================= PUBLISH TOGGLE ================= */
    qz_rows.querySelectorAll('.qz-pub').forEach(pb=>{
      pb.addEventListener('change', async ()=>{
        const row = pb.closest('tr');
        const ch = row.querySelector('.qz-tg');
        const quizId = Number(ch?.dataset.id);
        if(!quizId) return;

        const attemptEl = row.querySelector('.qz-order');

        const payload = {
          quiz_id: quizId,
          assigned: !!ch.checked,
          publish_to_students: !!pb.checked
        };

        if(attemptEl && attemptEl.value !== '') {
          payload.attempt_allowed = Number(attemptEl.value);
        }

        try{ await toggleQuiz(qz_uuid, payload, null, true); }catch(_){}
      });
    });

    /* ================= ATTEMPTS INPUT SAVE ================= */
    qz_rows.querySelectorAll('.qz-order').forEach(io=>{
      io.addEventListener('blur', async ()=>{
        const row = io.closest('tr');
        const ch = row.querySelector('.qz-tg');
        const quizId = Number(ch?.dataset.id);
        if(!quizId) return;

        const val = io.value !== '' ? Number(io.value) : null;

        const payload = {
          quiz_id: quizId,
          assigned: !!ch.checked,
          attempt_allowed: val
        };

        try{
          await toggleQuiz(qz_uuid, payload, null, true);
        }catch(e){
          err('Failed to save attempts');
        }
      });
    });

    /* ================= PAGINATION ================= */
    const total = Number(pag.total||items.length), per = Number(pag.per_page||20), cur = Number(pag.current_page||1);
    const pages = Math.max(1, Math.ceil(total/per));

    function li(dis,act,label,t){
      return `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
                <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
              </li>`;
    }

    let html=''; 
    html+=li(cur<=1,false,'Prev',cur-1);

    const w=2,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);

    html+=li(cur>=pages,false,'Next',cur+1);

    qz_pager.innerHTML = html;

    qz_pager.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click', ()=>{
        const t = Number(a.dataset.page);
        if(!t || t===qz_page) return;
        qz_page = t;
        loadQuizzes();
      });
    });

    qz_meta.textContent = `Page ${cur} of ${pages} — ${total} quizzes`;

  }catch(e){
    console.error('Quiz load error:', e);
    err(e.message || 'Failed to load quizzes');
  }finally{
    qz_loader.style.display='none';
  }
}
/**
 * toggleQuiz
 * - uuid: batch uuid
 * - payload: { quiz_id, assigned, display_order?, publish_to_students?, available_from?, available_until? }
 * - checkboxEl: the checkbox element that triggered the action (optional) - will be reverted on error
 * - quiet: if true, won't show the standard ok toast (useful for display_order/publish quick-saves)
 */
async function toggleQuiz(uuid, payload, checkboxEl=null, quiet=false){
  try{
    // Ensure boolean is proper boolean
    if(typeof payload.assigned === 'undefined') payload.assigned = true;
    const res = await fetch(`/api/batches/${encodeURIComponent(uuid)}/quizzes/toggle`,{
      method: 'POST',
      headers: { 'Authorization':'Bearer '+TOKEN, 'Content-Type':'application/json', 'Accept':'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Quiz toggle failed');
    if(!quiet) ok(payload.assigned ? 'Quiz assigned to batch' : 'Quiz unassigned from batch');
    return j;
  }catch(e){
    // revert UI toggle if a checkbox element was provided
    if(checkboxEl) checkboxEl.checked = !checkboxEl.checked;
    err(e.message || 'Toggle failed');
    throw e;
  }
}

;/* end quizzes section */
/* ================= CODING QUESTIONS (Assign Coding Question UI) ================= */
const cq_q = document.getElementById('cq_q'),
      cq_per = document.getElementById('cq_per'),
      cq_apply = document.getElementById('cq_apply'),
      cq_assigned = document.getElementById('cq_assigned'),
      cq_rows = document.getElementById('cq_rows'),
      cq_loader = document.getElementById('cq_loader'),
      cq_meta = document.getElementById('cq_meta'),
      cq_pager = document.getElementById('cq_pager');

let codingQuestionsModal, cq_batch_key = null, cq_page = 1;

function cqParams(){
  const p = new URLSearchParams();
  if (cq_q.value.trim()) p.set('q', cq_q.value.trim());
  p.set('per_page', cq_per.value || 20);
  p.set('page', cq_page);
  if (cq_assigned.value === 'assigned') p.set('assigned', '1');
  if (cq_assigned.value === 'unassigned') p.set('assigned', '0');
  return p.toString();
}

function pickItems(j){
  // supports: {data: []} OR {data:{data:[]}} OR {questions: []} OR {items:[]}
  if (Array.isArray(j?.data)) return j.data;
  if (Array.isArray(j?.data?.data)) return j.data.data;
  if (Array.isArray(j?.questions)) return j.questions;
  if (Array.isArray(j?.items)) return j.items;
  return [];
}
function pickPagination(j, fallbackLen){
  // supports: {pagination:{...}} OR {meta:{...}} OR paginator {data:{...}}
  return j?.pagination || j?.meta || j?.data?.meta || {
    current_page: 1,
    per_page: Number(cq_per?.value || 20),
    total: fallbackLen
  };
}

function getQTitle(q){ return q?.title || q?.name || q?.question_title || 'Untitled'; }
function getQDifficulty(q){ return (q?.difficulty || q?.level || '—').toString(); }
function getQUuid(q){
  return (q?.question_uuid
    || q?.uuid
    || q?.questionUuid
    || q?.questionUUID
    || q?.question_key
    || q?.questionKey
    || '').toString();
}

function getAssigned(q){
  return !!(q?.assigned ?? q?.is_assigned ?? q?.assigned_to_batch ?? q?.assignedToBatch);
}
function getMaxAttempts(q){
  return Number(q?.total_attempts ?? q?.max_attempts ?? q?.maxAttempts ?? q?.attempt_limit ?? 1) || 1;
}
function getAttemptAllowed(q){
  const v = (q?.attempt_allowed ?? q?.allowed_attempts ?? q?.attemptAllowed ?? q?.attempts_allowed);
  return (v === null || v === undefined || v === '') ? '' : Number(v);
}
function clampAttempts(v, hardMax){
  const lim = Math.max(1, Math.min(50, Number(hardMax) || 1));
  let n = parseInt(v, 10);
  if (!Number.isFinite(n) || n < 1) n = lim;
  if (n > lim) n = lim;
  return n;
}

function openCodingQuestions(batchKey){
  codingQuestionsModal = codingQuestionsModal || new bootstrap.Modal(document.getElementById('codingQuestionsModal'));
  cq_batch_key = batchKey;
  cq_page = 1;
  cq_assigned.value = 'all';
  codingQuestionsModal.show();
  loadCodingQuestions();
}

cq_apply.addEventListener('click', ()=>{ cq_page = 1; loadCodingQuestions(); });
cq_per.addEventListener('change', ()=>{ cq_page = 1; loadCodingQuestions(); });
cq_assigned.addEventListener('change', ()=>{ cq_page = 1; loadCodingQuestions(); });

let cqT;
cq_q.addEventListener('input', ()=>{
  clearTimeout(cqT);
  cqT = setTimeout(()=>{ cq_page = 1; loadCodingQuestions(); }, 350);
});

async function assignCodingQuestion(batchKey, questionUuid, attemptAllowed, quiet=false){
  const payload = {
    // support multiple backend expectations safely
    question_uuid: questionUuid,
    questionUuid: questionUuid,
    question_uuids: [questionUuid],
    questionUuids: [questionUuid],

    attempt_allowed: attemptAllowed,
    attemptAllowed: attemptAllowed,
    max_attempts: attemptAllowed,
    allowed_attempts: attemptAllowed,

    publish_to_students: 1,
    assign_status: 1
  };

  const res = await fetch(`/api/batches/${encodeURIComponent(batchKey)}/coding-questions/assign`, {
    method: 'POST',
    headers: { 'Authorization':'Bearer '+TOKEN, 'Content-Type':'application/json', 'Accept':'application/json' },
    body: JSON.stringify(payload)
  });

  const j = await res.json().catch(()=>({}));
  if(!res.ok) throw new Error(j?.message || firstError(j) || 'Assign failed');
  if(!quiet) ok('Coding question assigned');
  return j;
}

async function unassignCodingQuestion(batchKey, questionUuid, quiet=false){
  const res = await fetch(`/api/batches/${encodeURIComponent(batchKey)}/coding-questions/${encodeURIComponent(questionUuid)}`, {
    method: 'DELETE',
    headers: { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
  });

  const j = await res.json().catch(()=>({}));
  if(!res.ok) throw new Error(j?.message || firstError(j) || 'Unassign failed');
  if(!quiet) ok('Coding question unassigned');
  return j;
}

async function loadCodingQuestions(){
  if(!cq_batch_key) return;

  cq_loader.style.display = '';
  cq_rows.querySelectorAll('tr:not(#cq_loader)').forEach(tr=>tr.remove());

  try{
    const res = await fetch(`/api/batches/${encodeURIComponent(cq_batch_key)}/coding-questions?mode=all&${cqParams()}`, {
  headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' }
});


    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || 'Failed to load coding questions');

    const items = pickItems(j);
    const pag   = pickPagination(j, items.length);

    const frag = document.createDocumentFragment();

    items.forEach(qn=>{
      const qUuid = getQUuid(qn);
      const assigned = getAssigned(qn);

      const title = getQTitle(qn);
      const diff  = getQDifficulty(qn);

      const maxAttempts = getMaxAttempts(qn);
      const limit = Math.min(50, maxAttempts);

      let attemptAllowed = getAttemptAllowed(qn);
      if (attemptAllowed === '') attemptAllowed = maxAttempts;
      attemptAllowed = clampAttempts(attemptAllowed, limit);

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-semibold">${esc(title)}</td>
        <td class="text-capitalize">${esc(diff)}</td>

        <td class="text-center">
          <span class="badge badge-info">${esc(maxAttempts)}</span>
        </td>

        <td class="text-center">
          <input
            class="form-control form-control-sm cq-attempt"
            type="number"
            min="1"
            max="${esc(limit)}"
            value="${esc(attemptAllowed)}"
            style="width:120px; margin-inline:auto; text-align:center;"
          >
          <div class="small text-muted mt-1">1 — ${esc(limit)}</div>
        </td>

        <td class="text-center">
          <div class="form-check form-switch d-inline-block">
            <input
              class="form-check-input cq-tg"
              type="checkbox"
              data-uuid="${esc(qUuid)}"
              ${assigned ? 'checked' : ''}
              ${qUuid ? '' : 'disabled'}
            >
          </div>
        </td>
      `;
      frag.appendChild(tr);
    });

    cq_rows.appendChild(frag);

    // Toggle assign/unassign
    cq_rows.querySelectorAll('.cq-tg').forEach(ch=>{
      ch.addEventListener('change', async ()=>{
        const row = ch.closest('tr');
        const attemptEl = row.querySelector('.cq-attempt');

        const questionUuid = ch.dataset.uuid;
        if(!questionUuid){
          ch.checked = !ch.checked;
          return err('Question UUID missing from API response (uuid/question_uuid not found).');
        }

        const wantAssigned = !!ch.checked;
        const limit = Number(attemptEl.max || 1);
        const attemptAllowed = clampAttempts(attemptEl.value, limit);
        attemptEl.value = String(attemptAllowed);

        ch.disabled = true;
        // attemptEl.disabled = true;

        try{
          if(wantAssigned){
            await assignCodingQuestion(cq_batch_key, questionUuid, attemptAllowed);
          }else{
            await unassignCodingQuestion(cq_batch_key, questionUuid);
          }

          if ((cq_assigned.value==='assigned' && !wantAssigned) ||
              (cq_assigned.value==='unassigned' && wantAssigned)){
            loadCodingQuestions();
          }
        }catch(e){
          ch.checked = !wantAssigned;
          err(e.message);
        }finally{
          ch.disabled = false;
          attemptEl.disabled = false;
        }
      });
    });

    // Save attempts only if assigned
    cq_rows.querySelectorAll('.cq-attempt').forEach(inp=>{
      inp.addEventListener('blur', async ()=>{
        const row = inp.closest('tr');
        const ch  = row.querySelector('.cq-tg');
        if(!ch )return;

        const questionUuid = ch.dataset.uuid;
        if(!questionUuid) return;

        const limit = Number(inp.max || 1);
        const attemptAllowed = clampAttempts(inp.value, limit);
        inp.value = String(attemptAllowed);

        try{
          await assignCodingQuestion(cq_batch_key, questionUuid, attemptAllowed, true);
          ok('Attempts updated');
        }catch(e){
          err(e.message || 'Failed to update attempts');
        }
      });
    });

    // Pagination
    const total = Number(pag.total || items.length);
    const per   = Number(pag.per_page || cq_per.value || 20);
    const cur   = Number(pag.current_page || pag.page || 1);
    const pages = Math.max(1, Math.ceil(total / per));

    function li(dis, act, label, t){
      return `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
                <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
              </li>`;
    }

    let html='';
    html += li(cur<=1,false,'Prev',cur-1);
    const w=2, s=Math.max(1,cur-w), e=Math.min(pages,cur+w);
    for(let i=s;i<=e;i++) html += li(false,i===cur,i,i);
    html += li(cur>=pages,false,'Next',cur+1);

    cq_pager.innerHTML = html;
    cq_pager.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click', ()=>{
        const t = Number(a.dataset.page);
        if(!t || t===cq_page) return;
        cq_page = t;
        loadCodingQuestions();
      });
    });

    cq_meta.textContent = `Page ${cur} of ${pages} — ${total} coding question(s)`;

  }catch(e){
    console.error(e);
    err(e.message || 'Failed to load coding questions');
  }finally{
    cq_loader.style.display = 'none';
  }
}
/* end coding questions section */
});
</script>
</body>
</html>
