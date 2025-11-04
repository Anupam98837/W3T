{{-- resources/views/modules/batches/manageBatches.blade.php --}}
@section('title','Manage Batches')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Shell ===== */
.bat-wrap{max-width:1140px;margin:16px auto 40px}
.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

/* Toolbar */
.mfa-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .btn{height:40px;border-radius:12px}
.mfa-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
.mfa-toolbar .btn-primary{background:var(--primary-color);border:none}
.segment{display:inline-flex;border:1px solid var(--line-strong);border-radius:12px;overflow:hidden;background:var(--surface)}
.segment .seg{padding:8px 12px;cursor:pointer;border-right:1px solid var(--line-strong);user-select:none}
.segment .seg:last-child{border-right:none}
.segment .seg.active{background:var(--accent-color);color:#fff}

/* Table Card */
.table-wrap.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible}
.table-wrap .card-body{overflow:visible}
.table-responsive{overflow:visible !important}
.table{--bs-table-bg:transparent}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
.small{font-size:12.5px}
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Batch cell */
.bcell{display:flex;align-items:center;gap:10px}
.bthumb{width:48px;height:32px;border-radius:8px;border:1px solid var(--line-strong);object-fit:cover;background:#f4f4f8}

/* Bin rows */
tr.bin > *{background:rgba(220,38,38,.04)}
tr.bin:hover > *{background:rgba(220,38,38,.08)}

/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:240px; z-index:1085}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Empty & loader */
#empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg,#0001,#0000000d,#0001);border-radius:8px}

/* Modals (shared) */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control,.form-select{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control,html.theme-dark .form-select{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}

/* ===== Manage Students modal ===== */
.mstab-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;gap:12px;flex-wrap:wrap}
.st-table thead th{font-size:12px}
.st-table td,.st-table th{vertical-align:middle}
.form-switch .form-check-input{transform:scale(1.0)}
.mstab-head .left-tools{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* CSV import */
.dropzone{border:1.5px dashed var(--line-strong);border-radius:14px;padding:24px;text-align:center;background:var(--surface);transition:.15s}
.dropzone.drag{background:color-mix(in oklab,var(--accent-color) 10%,transparent); border-color:var(--accent-color); box-shadow:0 0 0 3px color-mix(in oklab,var(--accent-color) 18%,transparent)}
.dropzone .hint{color:var(--muted-color);font-size:13px}

/* ===== Create/Edit Batch modal – Group Links UI polish ===== */
.gl-wrap-tip{color:var(--muted-color);font-size:12px}
.gl-row{display:grid;grid-template-columns:180px 1fr auto;gap:8px;align-items:center}
.gl-row .gl-key{height:40px}
.gl-row .gl-url{height:40px}
.gl-row .remove{
  width:40px;height:40px;border-radius:10px;border:1px solid var(--line-strong);
  background:var(--surface);display:inline-flex;align-items:center;justify-content:center;
  transition:.15s;color:#6b7280
}
.gl-row .remove:hover{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.35);color:var(--danger-color)}
.gl-row + .gl-row{margin-top:8px}
#gl_add{border-radius:10px;border:1px solid var(--line-strong);background:var(--surface)}
#gl_add:hover{box-shadow:var(--shadow-1)}

/* Help text */
.help{color:var(--muted-color);font-size:12px}

/* Dark tweaks */
html.theme-dark .panel,html.theme-dark .table-wrap.card,html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .dropzone{background:#0b1020;border-color:var(--line-strong)}

/* Keep dropdowns visible over sticky header etc. */
.table-wrap,.table-wrap .card-body,.table-responsive{overflow:visible !important}
.table-wrap .dropdown{position:relative}
.table-wrap .dropdown-menu{z-index:2050}

/* ===== Minimal rich-text toolbar (Bold/Italic only) ===== */
.rt-toolbar{display:flex;gap:8px;margin-bottom:6px}
.rt-btn{
  width:40px;height:36px;border-radius:10px;border:1px solid var(--line-strong);
  background:var(--surface);display:inline-flex;align-items:center;justify-content:center;
  cursor:pointer;transition:.15s
}
.rt-btn:hover{box-shadow:var(--shadow-1)}
html.theme-dark .rt-btn{background:#0f172a;border-color:var(--line-strong)}
#instructorsModal .ins-role { min-width: 160px; }
</style>
@endpush

@section('content')
<div class="bat-wrap">

  {{-- ================= Toolbar ================= --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Course</label>
        <select id="courseSel" class="form-select" style="min-width:260px;">
          <option value="">Select a course…</option>
        </select>
      </div>

      <div class="position-relative" style="min-width:260px;">
        <input id="q" type="text" class="form-control ps-5" placeholder="Search batch title/tagline…" disabled>
        <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Status</label>
        <select id="status" class="form-select" style="width:140px;" disabled>
          <option value="">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      <div class="segment" id="segView" title="Active / Bin">
        <div class="seg active" data-mode="active"><i class="fa fa-list-ul me-1"></i>Active</div>
        <div class="seg" data-mode="bin"><i class="fa fa-trash me-1"></i>Bin</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Per page</label>
        <select id="per_page" class="form-select" style="width:96px;">
          <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
        </select>
      </div>

      <button id="btnApply" class="btn btn-light ms-1" disabled><i class="fa fa-check me-1"></i>Apply</button>
      <button id="btnReset" class="btn btn-light" disabled><i class="fa fa-rotate-left me-1"></i>Reset</button>
    </div>

    <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
      <button id="btnCreate" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>Create New Batch
      </button>
    </div>
  </div>

  {{-- ================= Table ================= --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top" style="z-index:2;">
            <tr>
              <th class="sortable" data-col="badge_title">BATCH <span class="caret"></span></th>
              <th style="width:110px;">MODE</th>
              <th class="sortable" data-col="status" style="width:120px;">STATUS <span class="caret"></span></th>
              <th class="sortable" data-col="starts_at" style="width:160px;">STARTS <span class="caret"></span></th>
              <th style="width:160px;">ENDS</th>
              <th style="width:170px;">DURATION</th>
              <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
              <th class="text-end" style="width:88px;">ACTIONS</th>
            </tr>
          </thead>
          <tbody id="rows">
            <tr id="askCourseRow">
              <td colspan="8" class="p-4 text-center text-muted">
                <i class="fa fa-layer-group mb-2" style="font-size:28px;opacity:.6"></i>
                <div>Please select a course to load its batches.</div>
              </td>
            </tr>
            <tr id="loaderRow" style="display:none;">
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

      {{-- Empty state --}}
      <div id="empty" class="p-4 text-center" style="display:none;">
        <i class="fa fa-folder-open mb-2" style="font-size:32px;opacity:.6;"></i>
        <div>No batches found for current filters.</div>
      </div>

      {{-- Footer: pagination --}}
      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="metaTxt">—</div>
        <nav><ul id="pager" class="pagination mb-0"></ul></nav>
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
      <div class="modal-body">
        <div id="vBody">Loading…</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
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
          {{-- Existing --}}
          <div class="tab-pane fade show active" id="tabExisting" role="tabpanel">
            <div class="mstab-head">
              <div class="left-tools d-flex align-items-center gap-2">
                <input id="st_q" class="form-control" style="width:240px" placeholder="Search by name/email/phone…">
                <label class="text-muted small mb-0">Per page</label>
                <select id="st_per" class="form-select" style="width:90px">
                  <option>10</option><option selected>20</option><option>30</option><option>50</option>
                </select>

                {{-- NEW: Assigned dropdown (matches Per page UI) --}}
                <label class="text-muted small mb-0">Assigned</label>
                <select id="st_assigned" class="form-select" style="width:150px">
                  <option value="all" selected>All</option>
                  <option value="assigned">Assigned</option>
                  <option value="unassigned">Unassigned</option>
                </select>

                <button id="st_apply" class="btn btn-light"><i class="fa fa-check me-1"></i>Apply</button>
              </div>
              <div class="text-muted small" id="st_meta">—</div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle st-table mb-0">
                <thead><tr><th>Name</th><th style="width:30%;">Email</th><th style="width:20%;">Phone</th><th class="text-center" style="width:110px;">Select</th></tr></thead>
                <tbody id="st_rows">
                  <tr id="st_loader" style="display:none;">
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
              <ul id="st_pager" class="pagination mb-0"></ul>
            </div>
          </div>

          {{-- Import --}}
          <div class="tab-pane fade" id="tabImport" role="tabpanel">
            <div class="dropzone text-center" id="csvDrop">
              <div class="mb-2"><i class="fa-regular fa-circle-up" style="font-size:28px;opacity:.8"></i></div>
              <div class="fw-semibold">Import CSV File</div>
              <div class="hint mt-1">
                Columns required: <b>email</b>, <b>name</b>, <b>phone</b> (or <b>phone_number</b>).
              </div>
              <div class="mt-2">
                <label class="btn btn-light me-2">
                  <i class="fa fa-file-csv me-1"></i>Import CSV
                  <input id="csvFile" type="file" class="d-none" accept=".csv,text/csv">
                </label>
              </div>
            </div>
            <div class="mt-3 small text-muted" id="csvHint">—</div>
            <div class="mt-2" id="csvSummary" style="display:none;"></div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- ================= Assign Instructors (modal) ================= --}}
<div class="modal fade" id="instructorsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-chalkboard-user me-2"></i>Assign Instructors</h5>
        <a id="ins_add_btn" href="/admin/users/manage" class="btn btn-primary btn-sm ms-auto">
          <i class="fa fa-user-plus me-1"></i> Add Instructor
        </a>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Controls: search, per page, assigned filter -->
        <div class="mstab-head">
          <div class="left-tools d-flex align-items-center gap-2">
            <input id="ins_q" class="form-control" style="width:240px" placeholder="Search by name/email/phone…">
            <label class="text-muted small mb-0">Per page</label>
            <select id="ins_per" class="form-select" style="width:90px">
              <option>10</option><option selected>20</option><option>30</option><option>50</option>
            </select>
            <label class="text-muted small mb-0">Assigned</label>
            <select id="ins_assigned" class="form-select" style="width:150px">
              <option value="all" selected>All</option>
              <option value="assigned">Assigned</option>
              <option value="unassigned">Unassigned</option>
            </select>
            <button id="ins_apply" class="btn btn-light"><i class="fa fa-check me-1"></i>Apply</button>
          </div>
          <div class="text-muted small" id="ins_meta">—</div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle st-table mb-0">
            <thead>
              <tr>
                <th>Name</th>
                <th style="width:28%;">Email</th>
                <th style="width:18%;">Phone</th>
                <th style="width:18%;">Role in Batch</th>
                <th class="text-center" style="width:110px;">Assign</th>
              </tr>
            </thead>
            <tbody id="ins_rows">
              <tr id="ins_loader" style="display:none;">
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
          <ul id="ins_pager" class="pagination mb-0"></ul>
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
            <div class="help mt-1">Course is set from the toolbar selection.</div>
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

          <div class="col-12">
            <label class="form-label">Tagline</label>
            <input id="bm_tagline" class="form-control" maxlength="255" placeholder="Short hook line (optional)">
          </div>

          <div class="col-12">
            <label class="form-label">Badge Description</label>

            {{-- ⚠️ Bold/Italic toolbar (Markdown-style **bold** and *italic*) --}}
            <div class="rt-toolbar" aria-label="Text formatting">
              <button id="rt_bold" type="button" class="rt-btn" title="Bold (Ctrl/Cmd+B)"><i class="fa-solid fa-bold"></i></button>
              <button id="rt_italic" type="button" class="rt-btn" title="Italic (Ctrl/Cmd+I)"><i class="fa-solid fa-italic"></i></button>
            </div>

            <textarea id="bm_desc" class="form-control" rows="4" placeholder="Longer description (optional)"></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Contact Number</label>
            <input id="bm_contact" class="form-control" maxlength="32" placeholder="+91… (optional)">
          </div>
          <div class="col-md-6">
            <label class="form-label">Note</label>
            <input id="bm_note" class="form-control" placeholder="Internal note (optional)">
          </div>

          <div class="col-md-4">
            <label class="form-label">Start Date</label>
            <input id="bm_start" type="date" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">End Date</label>
            <input id="bm_end" type="date" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Duration (auto)</label>
            <input id="bm_duration_preview" class="form-control" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Featured Image (optional)</label>
            <input id="bm_image" type="file" class="form-control" accept="image/*">
            <div class="help mt-1">Max 5MB. jpg/jpeg/png/webp/gif</div>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <img id="bm_image_preview" class="bthumb" style="width:120px;height:80px" src="" alt="" />
          </div>

          <div class="col-12">
            <label class="form-label">Group Links (Key → URL)</label>
            <div class="gl-wrap-tip mb-1">Example: <b>WhatsApp</b> → <span class="text-muted">https://chat.whatsapp.com/…</span> • <b>Telegram</b> → <span class="text-muted">https://t.me/…</span></div>
            <div id="gl_wrap"></div>
            <button id="gl_add" class="btn btn-light btn-sm mt-2" type="button"><i class="fa fa-plus me-1"></i>Add Link</button>
            <div class="help mt-2">Saved as key-value pairs on the server. Old plain URL arrays are still supported on load.</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="bm_save" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save</button>
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
/* ================== AUTH / GLOBALS ================== */
const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
if (!TOKEN){ Swal.fire('Login needed','Your session expired. Please login again.','warning').then(()=> location.href='/'); }

const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
const errToast = new bootstrap.Toast(document.getElementById('errToast'));
const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

/* Ensure 3-dots menu always works (no bubbling issues) */
document.addEventListener('click', (e)=>{
  const btn = e.target.closest('.dd-toggle'); if(!btn) return;
  e.preventDefault(); e.stopPropagation();
  const inst = bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'});
  inst.toggle();
});

/* ================== UTILS ================== */
const esc = (s)=> (s==null?'':String(s)).replace(/[&<>"'`]/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;' }[m]));
function fmtDate(iso){ if(!iso) return '-'; const d=new Date(iso); return isNaN(d)?esc(iso):d.toLocaleDateString(undefined,{year:'numeric',month:'short',day:'2-digit'}); }
function fmtDateTime(iso){ if(!iso) return '-'; const d=new Date(iso); return isNaN(d)?esc(iso):d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'}); }
function badgeStatus(s){ const map={active:'success',inactive:'warning',archived:'info'}; const cls=map[s]||'info'; return `<span class="badge badge-${cls} text-uppercase">${esc(s||'-')}</span>`; }
function diffYMD(sISO,eISO){
  const s=new Date(sISO), e=new Date(eISO); if(isNaN(s)||isNaN(e)||e<s) return null;
  let y=e.getFullYear()-s.getFullYear(), m=e.getMonth()-s.getMonth(), d=e.getDate()-s.getDate();
  if(d<0){ const prevDays=new Date(e.getFullYear(),e.getMonth(),0).getDate(); d+=prevDays; m-=1; }
  if(m<0){ m+=12; y-=1; }
  return {y,m,d};
}
function humanYMD(s,e){ const v=diffYMD(s,e); if(!v) return '-'; const parts=[]; if(v.y>0) parts.push(v.y+' '+(v.y===1?'year':'years')); if(v.m>0) parts.push(v.m+' '+(v.m===1?'month':'months')); if(v.d>0||!parts.length) parts.push(v.d+' '+(v.d===1?'day':'days')); return parts.join(' '); }
function firstError(j){ if(j?.errors){ const k=Object.keys(j.errors)[0]; if(k){ const v=j.errors[k]; return Array.isArray(v)?v[0]:String(v); } } return j?.message||''; }

/* Safe key for PHP form names: group_links[KEY] */
function safeKey(k){
  return (k||'').toString().trim().replace(/[^\w\-\.]/g,'_').substring(0,60) || 'Link';
}

/* ================== ELEMENTS & STATE ================== */
const courseSel = document.getElementById('courseSel');
const q = document.getElementById('q');
const statusSel = document.getElementById('status');
const perPageSel = document.getElementById('per_page');
const segView = document.getElementById('segView');
const btnApply = document.getElementById('btnApply');
const btnReset = document.getElementById('btnReset');

const rowsEl = document.getElementById('rows');
const loader = document.getElementById('loaderRow');
const askRow = document.getElementById('askCourseRow');
const emptyEl = document.getElementById('empty');
const pagerEl = document.getElementById('pager');
const metaTxt = document.getElementById('metaTxt');

/* Create/Edit modal els */
const bm_modal_title  = document.getElementById('bm_title');
const bm_mode         = document.getElementById('bm_mode');
const bm_uuid         = document.getElementById('bm_uuid');
const bm_course_label = document.getElementById('bm_course_label');
const bm_course_id    = document.getElementById('bm_course_id');
const bm_status       = document.getElementById('bm_status');
const bm_title_input  = document.getElementById('bm_title_input');
const bm_mode_select  = document.getElementById('bm_mode_select');
const bm_tagline      = document.getElementById('bm_tagline');
const bm_desc         = document.getElementById('bm_desc');
const bm_contact      = document.getElementById('bm_contact');
const bm_note         = document.getElementById('bm_note');
const bm_start        = document.getElementById('bm_start');
const bm_end          = document.getElementById('bm_end');
const bm_dur_prev     = document.getElementById('bm_duration_preview');
const bm_image        = document.getElementById('bm_image');
const bm_img_prev     = document.getElementById('bm_image_preview');
const bm_save         = document.getElementById('bm_save');
const gl_wrap         = document.getElementById('gl_wrap');
const gl_add          = document.getElementById('gl_add');

/* ================== Instructors modal ================== */
const ins_q       = document.getElementById('ins_q');
const ins_per     = document.getElementById('ins_per');
const ins_apply   = document.getElementById('ins_apply');
const ins_assigned= document.getElementById('ins_assigned');

const ins_rows    = document.getElementById('ins_rows');
const ins_loader  = document.getElementById('ins_loader');
const ins_meta    = document.getElementById('ins_meta');
const ins_pager   = document.getElementById('ins_pager');

let instructorsModal, ins_uuid=null, ins_page=1;


/* === Bold/Italic buttons (works in both Create & Edit modes) === */
const rt_bold   = document.getElementById('rt_bold');
const rt_italic = document.getElementById('rt_italic');

let page = 1, sort = '-created_at', mode = 'active';
let currentCourseId = '';
let studentsModal, batchModal;

/* ================== INIT / EVENTS ================== */
applyFromURL();
wireEvents();
loadCourses(); // async; will call load() if URL has course_id

function wireEvents(){
  document.getElementById('btnCreate').addEventListener('click', openCreateModal);

  document.querySelectorAll('th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      if (sort===col) sort='-'+col;
      else if (sort==='-'+col) sort=col;
      else sort = (col==='created_at'||col==='starts_at')?('-'+col):col;
      page=1; syncSortHeaders(); load();
    });
  });

  segView.addEventListener('click', (e)=>{
    const seg = e.target.closest('.seg'); if(!seg) return;
    segView.querySelectorAll('.seg').forEach(s=>s.classList.remove('active'));
    seg.classList.add('active'); mode = seg.dataset.mode==='bin'?'bin':'active';
    page=1; load();
  });

  btnApply.addEventListener('click', ()=>{ page=1; load(); });
  btnReset.addEventListener('click', ()=>{
    q.value=''; statusSel.value=''; perPageSel.value='20'; page=1; sort='-created_at';
    mode='active'; segView.querySelectorAll('.seg').forEach(s=>s.classList.remove('active'));
    segView.querySelector('[data-mode="active"]').classList.add('active'); load();
  });
  perPageSel.addEventListener('change', ()=>{ page=1; load(); });
  courseSel.addEventListener('change', ()=>{
    currentCourseId = courseSel.value || '';
    const on = !!currentCourseId;
    [q,statusSel,btnApply,btnReset].forEach(el=> el.disabled = !on);
    askRow.style.display = on ? 'none' : '';
    bm_course_label.value = courseSel.options[courseSel.selectedIndex]?.text || '';
    bm_course_id.value = currentCourseId || '';
    page=1; load();
  });

  let srT; q.addEventListener('input', ()=>{ clearTimeout(srT); srT=setTimeout(()=>{ page=1; load(); }, 350); });

  // duration preview in modal
  [bm_start,bm_end].forEach(el=> el.addEventListener('change', ()=>{
    bm_dur_prev.value = (bm_start.value && bm_end.value) ? humanYMD(bm_start.value,bm_end.value) : '';
  }));
  bm_image.addEventListener('change', ()=>{
    if (bm_image.files && bm_image.files[0]) bm_img_prev.src = URL.createObjectURL(bm_image.files[0]);
    else bm_img_prev.src='';
  });
  bm_save.addEventListener('click', saveBatch);

  gl_add.addEventListener('click', ()=> addGlRow('',''));

  /* ===== Bold / Italic only formatting for Description (Markdown-style) ===== */
  function wrapSelection(el, left, right, placeholder){
    el.focus();
    const s = el.selectionStart ?? 0, e = el.selectionEnd ?? 0;
    const val = el.value || '';
    const sel = val.slice(s, e);
    const text = sel || placeholder || 'text';
    const out = left + text + right;
    el.value = val.slice(0, s) + out + val.slice(e);
    const caret = s + out.length;
    try{ el.setSelectionRange(caret, caret); }catch(_){}
    el.dispatchEvent(new Event('input',{bubbles:true}));
  }
  if(rt_bold)   rt_bold.addEventListener('click', ()=> wrapSelection(bm_desc, '**','**','bold text'));
  if(rt_italic) rt_italic.addEventListener('click', ()=> wrapSelection(bm_desc, '*','*','italic text'));

  // Keyboard shortcuts inside textarea: Ctrl/Cmd+B / Ctrl/Cmd+I
  bm_desc.addEventListener('keydown', (e)=>{
    if((e.ctrlKey||e.metaKey) && (e.key==='b' || e.key==='B')){ e.preventDefault(); wrapSelection(bm_desc,'**','**','bold text'); }
    if((e.ctrlKey||e.metaKey) && (e.key==='i' || e.key==='I')){ e.preventDefault(); wrapSelection(bm_desc,'*','*','italic text'); }
  });
}

/* ================== URL helpers ================== */
function queryParams(){
  const p = new URLSearchParams();
  if (currentCourseId) p.set('course_id', currentCourseId);
  if (q.value.trim())  p.set('q', q.value.trim());
  if (statusSel.value) p.set('status', statusSel.value);
  p.set('per_page', perPageSel.value || 20);
  p.set('page', page);
  p.set('sort', sort);
  p.set('mode', mode);
  return p.toString();
}
function pushURL(){ history.replaceState(null,'', location.pathname + '?' + queryParams()); }
function applyFromURL(){
  const url=new URL(location.href), g=(k)=>url.searchParams.get(k)||'';
  if (g('mode')){ mode=(g('mode')==='bin'?'bin':'active'); segView.querySelectorAll('.seg').forEach(s=>s.classList.remove('active')); segView.querySelector(`[data-mode="${mode}"]`).classList.add('active'); }
  if (g('per_page')) perPageSel.value=g('per_page');
  if (g('page')) page=Number(g('page'))||1;
  if (g('sort')) sort=g('sort');
  syncSortHeaders();
}

/* ================== Courses dropdown ================== */
async function loadCourses(){
  try{
    const res = await fetch('/api/courses?status=published&per_page=1000', { headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
    const j = await res.json();
    if(!res.ok) throw new Error(j?.message || 'Failed to load courses');
    const items=j?.data||[];
    courseSel.innerHTML = '<option value="">Select a course…</option>' + items.map(c=>`<option value="${c.id}">${esc(c.title||'(untitled)')}</option>`).join('');
    const cid = new URL(location.href).searchParams.get('course_id') || '';
    if (cid){
      courseSel.value = cid; currentCourseId = cid;
      [q,statusSel,btnApply,btnReset].forEach(el=> el.disabled = false);
      askRow.style.display='none';
      bm_course_label.value = courseSel.options[courseSel.selectedIndex]?.text || '';
      bm_course_id.value = currentCourseId;
      load();
    }
  }catch(e){ err(e.message||'Course list error'); }
}

/* ================== Sorting header state ================== */
function syncSortHeaders(){
  document.querySelectorAll('th.sortable').forEach(th=>{
    th.classList.remove('asc','desc');
    const col = th.dataset.col;
    if (sort===col) th.classList.add('asc');
    if (sort==='-'+col) th.classList.add('desc');
  });
}

/* ================== Fetch Batches ================== */
function showLoader(v){ loader.style.display = v ? '' : 'none'; }
function rowActions(r){
  const inBin = !!r.deleted_at;
  const isArchived = (r.status === 'archived');
  return `
  <div class="dropdown text-end" data-bs-display="static">
    <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
      <i class="fa fa-ellipsis-vertical"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><button class="dropdown-item" data-act="view" data-uuid="${r.uuid}"><i class="fa fa-eye"></i> View Batch</button></li>
      ${!inBin ? `<li><button class="dropdown-item" data-act="edit" data-uuid="${r.uuid}"><i class="fa fa-pen-to-square"></i> Edit Batch</button></li>` : ''}
      ${!inBin ? `<li><button class="dropdown-item" data-act="instructors" data-uuid="${r.uuid}"><i class="fa fa-chalkboard-user"></i> Assign Instructor</button></li>` : ''}
      ${!inBin ? `<li><button class="dropdown-item" data-act="assign" data-uuid="${r.uuid}"><i class="fa fa-user-plus"></i> Manage Students</button></li>` : ''}
      <li><hr class="dropdown-divider"></li>
      ${(!inBin && !isArchived) ? `<li><button class="dropdown-item" data-act="archive" data-uuid="${r.uuid}"><i class="fa fa-box-archive"></i> Archive</button></li>` : ''}
      ${(!inBin && isArchived)  ? `<li><button class="dropdown-item" data-act="unarchive" data-uuid="${r.uuid}"><i class="fa fa-box-open"></i> Unarchive</button></li>` : ''}
      ${ inBin ? `<li><button class="dropdown-item" data-act="restore" data-uuid="${r.uuid}"><i class="fa fa-rotate-left"></i> Restore</button></li>`
               : `<li><button class="dropdown-item text-danger" data-act="delete" data-uuid="${r.uuid}"><i class="fa fa-trash"></i> Delete</button></li>`}
    </ul>
  </div>`;
}
function renderRows(items){
  rowsEl.querySelectorAll('tr:not(#loaderRow):not(#askCourseRow)').forEach(tr=>tr.remove());
  const frag=document.createDocumentFragment();
  items.forEach(r=>{
    const tr=document.createElement('tr'); if(r.deleted_at) tr.classList.add('bin');
    const img = esc(r.featured_image||'');
    tr.innerHTML = `
      <td>
        <div class="bcell">
          <img class="bthumb" src="${img}" onerror="this.src='https://dummyimage.com/96x64/e9e3f5/5e1570.jpg&text=%F0%9F%8E%93';this.onerror=null;">
          <div>
            <div class="fw-semibold">${esc(r.badge_title||'Untitled')}</div>
            <div class="small text-muted">${esc(r.tagline||'')}</div>
          </div>
        </div>
      </td>
      <td class="text-capitalize">${esc(r.mode||'-')}</td>
      <td>${badgeStatus(r.status)}</td>
      <td>${fmtDate(r.starts_at)}</td>
      <td>${fmtDate(r.ends_at)}</td>
      <td>${(Number(r.duration_days||0)>0) ? (r.duration_days+' days') : '-'}</td>
      <td>${fmtDateTime(r.created_at)}</td>
      <td class="text-end">${rowActions(r)}</td>`;
    frag.appendChild(tr);
  });
  rowsEl.appendChild(frag);
}
function renderPager(p){
  const total=Number(p.total||0), per=Number(p.per_page||20), cur=Number(p.current_page||1);
  const pages=Math.max(1,Math.ceil(total/per));
  function li(dis,act,label,target){ const c=['page-item',dis?'disabled':'',act?'active':''].filter(Boolean).join(' '); return `<li class="${c}"><a class="page-link" href="javascript:void(0)" data-page="${target||''}">${label}</a></li>`; }
  let html=''; html+=li(cur<=1,false,'Previous',cur-1);
  const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
  if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
  for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
  if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
  html+=li(cur>=pages,false,'Next',cur+1);
  pagerEl.innerHTML=html;
  pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{ const t=Number(a.dataset.page); if(!t||t===page) return; page=Math.max(1,t); load(); }));
  metaTxt.textContent = `Showing page ${cur} of ${pages} — ${total} result(s)`;
}
async function load(){
  pushURL();
  if(!currentCourseId){ rowsEl.querySelectorAll('tr:not(#askCourseRow)').forEach(tr=>tr.remove()); emptyEl.style.display='none'; return; }
  showLoader(true); emptyEl.style.display='none';
  rowsEl.querySelectorAll('tr:not(#loaderRow):not(#askCourseRow)').forEach(tr=>tr.remove());
  try{
    const p = new URLSearchParams();
    p.set('course_id', currentCourseId);
    if(q.value.trim()) p.set('q', q.value.trim());
    if(statusSel.value) p.set('status', statusSel.value);
    p.set('per_page', perPageSel.value || 20);
    p.set('page', page);
    p.set('sort', sort);
    if(mode==='bin') p.set('only_deleted','1');

    const res = await fetch('/api/batches?'+p.toString()+'&_='+Date.now(), {
      headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json','Cache-Control':'no-cache'}
    });
    const j = await res.json();
    if(!res.ok) throw new Error(j?.message||'Failed to load');
    const items=j?.data||[];
    const pagination=j?.pagination||{current_page:1,per_page:Number(perPageSel.value||20),total:items.length};
    if(items.length===0) emptyEl.style.display='';
    renderRows(items);
    renderPager(pagination);
  }catch(e){ console.error(e); emptyEl.style.display=''; metaTxt.textContent='Failed to load batches'; err(e.message||'Load error'); }
  finally{ showLoader(false); syncSortHeaders(); }
}

/* ================== View modal ================== */
async function openView(uuid){
  const modal=new bootstrap.Modal(document.getElementById('viewModal'));
  const vBody=document.getElementById('vBody');
  vBody.innerHTML='Loading…'; modal.show();
  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Load failed');
    const r=j.data||{};
    // Links: support associative object {key:url} or array of strings
    let linksHtml = '<div class="small text-muted">—</div>';
    if (r.group_links && typeof r.group_links === 'object' && !Array.isArray(r.group_links)) {
      const entries = Object.entries(r.group_links);
      if (entries.length){
        linksHtml = entries.map(([k,u])=>`<div class="small"><b>${esc(k)}:</b> <a href="${esc(u)}" target="_blank">${esc(u)}</a></div>`).join('');
      }
    } else if (Array.isArray(r.group_links) && r.group_links.length){
      linksHtml = r.group_links.map(u=>`<div class="small"><a href="${esc(u)}" target="_blank">${esc(u)}</a></div>`).join('');
    }

    vBody.innerHTML = `
      <div class="d-flex gap-3 align-items-start">
        <img class="bthumb" style="width:120px;height:80px" src="${esc(r.featured_image||'')}" onerror="this.src='https://dummyimage.com/200x120/e9e3f5/5e1570.jpg&text=Batch';this.onerror=null;">
        <div>
          <div class="h5 mb-1">${esc(r.badge_title||'Untitled')}</div>
          <div class="text-muted small">${esc(r.tagline||'')}</div>
          <div class="mt-1">${badgeStatus(r.status)} <span class="ms-2 badge badge-info text-uppercase">${esc(r.mode||'-')}</span></div>
        </div>
      </div>
      <hr class="my-3">
      <div class="row g-3">
        <div><span class="text-muted small">Duration:</span>
          <strong>${Number(r.duration_days||0)>0 ? (r.duration_days+' days') : '-'}</strong>
        </div>
        <div class="col-md-6"><div><span class="text-muted small">Contact:</span> ${esc(r.contact_number||'-')}</div><div><span class="text-muted small">Note:</span> ${esc(r.badge_note||'-')}</div></div>
        <div class="col-12"><div class="mb-1 fw-semibold">Group Links</div>${linksHtml}</div>
        <div class="col-12"><div class="mb-1 fw-semibold">Description</div><div class="small">${(r.badge_description||'').length?esc(r.badge_description):'<span class="text-muted">—</span>'}</div></div>
      </div>`;
  }catch(e){ vBody.innerHTML = `<div class="text-danger">${esc(e.message||'Failed to load')}</div>`; }
}

/* ================== Students modal (Existing + CSV) ================== */
const st_rows=document.getElementById('st_rows'), st_loader=document.getElementById('st_loader'), st_meta=document.getElementById('st_meta'), st_pager=document.getElementById('st_pager'), st_q=document.getElementById('st_q'), st_per=document.getElementById('st_per'), st_apply=document.getElementById('st_apply');
const st_assigned=document.getElementById('st_assigned');
const csvFile=document.getElementById('csvFile'), csvDrop=document.getElementById('csvDrop'), csvHint=document.getElementById('csvHint'), csvSummary=document.getElementById('csvSummary');
let st_uuid=null, st_page=1;

function studentsParams(){
  const p=new URLSearchParams();
  if(st_q.value.trim()) p.set('q',st_q.value.trim());
  p.set('per_page',st_per.value||20);
  p.set('page',st_page);
  if(st_assigned.value==='assigned')   p.set('assigned','1');
  if(st_assigned.value==='unassigned') p.set('assigned','0');
  return p.toString();
}
function openStudents(uuid){
  studentsModal = studentsModal || new bootstrap.Modal(document.getElementById('studentsModal'));
  st_uuid=uuid; st_page=1; st_assigned.value='all';
  studentsModal.show(); loadStudents();
}
st_apply.addEventListener('click',()=>{ st_page=1; loadStudents(); });
st_per.addEventListener('change',()=>{ st_page=1; loadStudents(); });
st_assigned.addEventListener('change',()=>{ st_page=1; loadStudents(); });

let stT; st_q.addEventListener('input',()=>{ clearTimeout(stT); stT=setTimeout(()=>{ st_page=1; loadStudents(); },350); });

async function loadStudents(){
  if(!st_uuid) return;
  st_loader.style.display=''; st_rows.querySelectorAll('tr:not(#st_loader)').forEach(tr=>tr.remove());
  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students?`+studentsParams(),{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Failed to load students');
    let items=j?.data||[];
    const pag=j?.pagination||{current_page:1,per_page:Number(st_per.value||20),total:items.length};

    // Client-side fallback if API ignores ?assigned=
    if(st_assigned.value==='assigned')   items = items.filter(u=> !!u.assigned);
    if(st_assigned.value==='unassigned') items = items.filter(u=> !u.assigned);

    const frag=document.createDocumentFragment();
    items.forEach(u=>{
      const tr=document.createElement('tr');
      tr.innerHTML=`
        <td class="fw-semibold">${esc(u.name||'-')}</td>
        <td>${esc(u.email||'-')}</td>
        <td>${esc((u.phone_number ?? u.phone ?? '-') )}</td>
        <td class="text-center">
          <div class="form-check form-switch d-inline-block">
            <input class="form-check-input st-tg" type="checkbox" data-id="${u.id}" ${u.assigned?'checked':''}>
          </div>
        </td>`;
      frag.appendChild(tr);
    });
    st_rows.appendChild(frag);
    st_rows.querySelectorAll('.st-tg').forEach(ch=>{
      ch.addEventListener('change',()=>{
        toggleStudent(Number(ch.dataset.id), ch.checked, ch);
      });
    });

    // pager (server data)
    const total=Number(pag.total||0), per=Number(pag.per_page||20), cur=Number(pag.current_page||1);
    const pages=Math.max(1,Math.ceil(total/per));
    function li(dis,act,label,t){ const c=['page-item',dis?'disabled':'',act?'active':''].filter(Boolean).join(' '); return `<li class="${c}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`; }
    let html=''; html+=li(cur<=1,false,'Prev',cur-1); const w=2,s=Math.max(1,cur-w),e=Math.min(pages,cur+w); for(let i=s;i<=e;i++) html+=li(false,i===cur,i); html+=li(cur>=pages,false,'Next',cur+1);
    st_pager.innerHTML=html; st_pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{ const t=Number(a.dataset.page); if(!t||t===st_page) return; st_page=t; loadStudents(); }));
    const label = st_assigned.value==='all' ? 'All' : (st_assigned.value==='assigned' ? 'Assigned' : 'Unassigned');
    st_meta.textContent=`${label} — Page ${cur}/${pages} — ${total} student(s)`;
  }catch(e){ err(e.message); }
  finally{ st_loader.style.display='none'; }
}
async function toggleStudent(userId, assigned, checkboxEl){
  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students/toggle`,{
      method:'POST',
      headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},
      body:JSON.stringify({user_id:userId,assigned:!!assigned})
    });
    const j=await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message||firstError(j)||'Toggle failed');
    ok(assigned ? 'Student assigned to batch' : 'Student removed from batch');

    // If the current filter would hide this row, refresh
    if((st_assigned.value==='assigned' && !assigned) || (st_assigned.value==='unassigned' && assigned)){
      loadStudents();
    }
  }catch(e){
    if(checkboxEl) checkboxEl.checked = !assigned;
    err(e.message);
  }
}

/* ================== Instructors modal (logic) ================== */
function instructorsParams(){
  const p = new URLSearchParams();
  if (ins_q.value.trim()) p.set('q', ins_q.value.trim());
  p.set('per_page', ins_per.value || 20);
  p.set('page', ins_page);
  if (ins_assigned.value === 'assigned')   p.set('assigned', '1');
  if (ins_assigned.value === 'unassigned') p.set('assigned', '0');
  return p.toString();
}

function openInstructors(uuid){
  instructorsModal = instructorsModal || new bootstrap.Modal(document.getElementById('instructorsModal'));
  ins_uuid = uuid; ins_page = 1; ins_assigned.value = 'all';
  instructorsModal.show();
  loadInstructors();
}

// filters & search
ins_apply.addEventListener('click', ()=>{ ins_page=1; loadInstructors(); });
ins_per.addEventListener('change', ()=>{ ins_page=1; loadInstructors(); });
ins_assigned.addEventListener('change', ()=>{ ins_page=1; loadInstructors(); });

let insT;
ins_q.addEventListener('input', ()=>{
  clearTimeout(insT);
  insT = setTimeout(()=>{ ins_page=1; loadInstructors(); }, 350);
});

async function loadInstructors(){
  if (!ins_uuid) return;
  ins_loader.style.display = '';
  ins_rows.querySelectorAll('tr:not(#ins_loader)').forEach(tr=>tr.remove());

  try{
    const res = await fetch(`/api/batches/${encodeURIComponent(ins_uuid)}/instructors?` + instructorsParams(), {
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const j = await res.json(); if(!res.ok) throw new Error(j?.message || 'Failed to load instructors');

    const items = j?.data || [];
    const pag   = j?.pagination || { current_page: 1, per_page: Number(ins_per.value||20), total: items.length };

    // Build rows
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
          <select class="form-select form-select-sm ins-role" ${assigned?'':'disabled'}>
            <option value="instructor" ${role==='instructor'?'selected':''}>Instructor</option>
            <option value="tutor"      ${role==='tutor'?'selected':''}>Tutor</option>
            <option value="TA"         ${role==='TA'?'selected':''}>TA</option>
            <option value="mentor"     ${role==='mentor'?'selected':''}>Mentor</option>
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

    // Toggle assign/unassign
    ins_rows.querySelectorAll('.ins-tg').forEach(ch=>{
      ch.addEventListener('change', ()=>{
        const row  = ch.closest('tr');
        const roleSel = row?.querySelector('.ins-role');
        const roleVal = roleSel ? roleSel.value : 'instructor';
        toggleInstructor(Number(ch.dataset.id), ch.checked, ch, roleVal);
      });
    });

    // Pagination (server-driven)
    const total=Number(pag.total||0), per=Number(pag.per_page||20), cur=Number(pag.current_page||1);
    const pages=Math.max(1,Math.ceil(total/per));
    function li(dis,act,label,t){ const c=['page-item',dis?'disabled':'',act?'active':''].filter(Boolean).join(' '); return `<li class="${c}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`; }
    let html=''; html+=li(cur<=1,false,'Prev',cur-1);
    const w=2,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
    for(let i=s;i<=e;i++) html+=li(false,i===cur,i);
    html+=li(cur>=pages,false,'Next',cur+1);
    ins_pager.innerHTML=html;
    ins_pager.querySelectorAll('a.page-link[data-page]').forEach(a=> a.addEventListener('click',()=>{
      const t=Number(a.dataset.page); if(!t||t===ins_page) return; ins_page=t; loadInstructors();
    }));

    // Meta label
    const label = ins_assigned.value==='all' ? 'All' : (ins_assigned.value==='assigned' ? 'Assigned' : 'Unassigned');
    ins_meta.textContent = `${label} — Page ${cur}/${pages} — ${total} instructor(s)`;
  }catch(e){
    err(e.message || 'Load error');
  }finally{
    ins_loader.style.display='none';
  }
}

async function toggleInstructor(userId, assigned, checkboxEl, roleVal){
  try{
    const body = assigned
      ? { user_id: userId, assigned: true,  role_in_batch: roleVal || 'instructor' }
      : { user_id: userId, assigned: false };

    const res = await fetch(`/api/batches/${encodeURIComponent(ins_uuid)}/instructors/toggle`,{
      method:'POST',
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Content-Type':'application/json', 'Accept':'application/json' },
      body: JSON.stringify(body)
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Toggle failed');

    // Enable/disable the role select to reflect assignment state
    const row = checkboxEl.closest('tr');
    const roleSel = row?.querySelector('.ins-role');
    if (roleSel) roleSel.disabled = !assigned;

    ok(assigned ? 'Instructor assigned to batch' : 'Instructor unassigned');

    // If current filter would hide/show, refresh
    if((ins_assigned.value==='assigned' && !assigned) || (ins_assigned.value==='unassigned' && assigned)){
      loadInstructors();
    }
  }catch(e){
    // Revert UI
    if (checkboxEl) checkboxEl.checked = !assigned;
    err(e.message);
  }
}

/* CSV upload */
;['dragenter','dragover'].forEach(ev=> csvDrop.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();csvDrop.classList.add('drag');}));
;['dragleave','drop'].forEach(ev=> csvDrop.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();csvDrop.classList.remove('drag');}));
csvDrop.addEventListener('drop',e=>{const files=e.dataTransfer?.files||[]; if(files.length) handleCsv(files[0]);});
csvFile.addEventListener('change',()=>{ if(csvFile.files?.length) handleCsv(csvFile.files[0]); });
async function handleCsv(file){
  if(!st_uuid) return;
  if(!file || !/\.csv$/i.test(file.name)) return Swal.fire('Invalid file','Please choose a .csv file','info');
  csvHint.textContent=`Uploading ${file.name}…`; const fd=new FormData(); fd.append('csv', file);
  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(st_uuid)}/students/upload-csv`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN},body:fd});
    const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(j?.message||firstError(j)||'Upload failed');
    const s=j.summary||{}; csvSummary.style.display=''; csvSummary.innerHTML=`
      <div class="alert alert-success mb-2">
        <div class="fw-semibold mb-1">Import Summary</div>
        <div class="small">Created users: <b>${s.created_users||0}</b></div>
        <div class="small">Updated users: <b>${s.updated_users||0}</b></div>
        <div class="small">Enrolled to batch: <b>${s.enrolled||0}</b></div>
      </div>
      ${(Array.isArray(s.errors)&&s.errors.length)?`<div class="alert alert-warning small"><div class="fw-semibold mb-1">Errors (${s.errors.length})</div>${s.errors.map(x=>`<div>• ${esc(x)}</div>`).join('')}</div>`:''}`;
    csvHint.textContent='Done.'; ok('CSV processed'); loadStudents();
  }catch(e){ csvHint.textContent='Failed.'; err(e.message); }
}

/* ================== Create / Edit ================== */
function openCreateModal(){
  if(!currentCourseId) return Swal.fire('Pick a course','Please select a course first.','info');
  batchModal = batchModal || new bootstrap.Modal(document.getElementById('batchModal'));
  bm_mode.value='create'; bm_uuid.value=''; bm_modal_title.textContent='Create Batch'; resetBatchForm();
  bm_course_id.value=currentCourseId; bm_course_label.value=courseSel.options[courseSel.selectedIndex]?.text || '';
  batchModal.show();
}
async function openEditModal(uuid){
  batchModal = batchModal || new bootstrap.Modal(document.getElementById('batchModal'));
  bm_mode.value='edit'; bm_uuid.value=uuid; bm_modal_title.textContent='Edit Batch'; resetBatchForm();
  try{
    const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}});
    const j=await res.json(); if(!res.ok) throw new Error(j?.message||'Load failed');
    const r=j.data||{};
    bm_course_id.value=r.course_id||currentCourseId||''; bm_course_label.value=courseSel.options[courseSel.selectedIndex]?.text||'';
    bm_status.value=r.status||'active'; bm_title_input.value=r.badge_title||''; bm_mode_select.value=r.mode||'online';
    bm_tagline.value=r.tagline||''; bm_desc.value=r.badge_description||''; bm_contact.value=r.contact_number||''; bm_note.value=r.badge_note||'';
    bm_start.value=r.starts_at?r.starts_at.slice(0,10):''; bm_end.value=r.ends_at?r.ends_at.slice(0,10):''; bm_dur_prev.value=(r.starts_at&&r.ends_at)?humanYMD(r.starts_at,r.ends_at):'';
    bm_img_prev.src=r.featured_image||'';

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
  }catch(e){ err(e.message||'Failed to open editor'); }
}
function resetBatchForm(){
  bm_status.value='active'; bm_title_input.value=''; bm_mode_select.value='hybrid'; bm_tagline.value=''; bm_desc.value=''; bm_contact.value=''; bm_note.value='';
  bm_start.value=''; bm_end.value=''; bm_dur_prev.value=''; bm_image.value=''; bm_img_prev.src='';
  gl_wrap.innerHTML=''; addGlRow('',''); addGlRow('','');
}

/* ---- URL key→value row builder ---- */
function addGlRow(keyVal, urlVal){
  const row=document.createElement('div');
  row.className='gl-row';
  row.innerHTML=`
    <input class="form-control gl-key" placeholder="Key (e.g., WhatsApp, Telegram, Discord…)" value="${esc(keyVal||'')}">
    <input class="form-control gl-url" placeholder="https://…" value="${esc(urlVal||'')}">
    <button type="button" class="remove" title="Remove"><i class="fa fa-xmark"></i></button>`;
  row.querySelector('.remove').addEventListener('click',()=> row.remove());
  gl_wrap.appendChild(row);
}

function collectGroupLinks(){
  const rows = [...gl_wrap.querySelectorAll('.gl-row')];
  const map = {};
  const used = new Set();
  let idx = 1;

  rows.forEach(r=>{
    let key = r.querySelector('.gl-key')?.value?.trim() || '';
    const url = r.querySelector('.gl-url')?.value?.trim() || '';
    if(!url) return;

    if(!key){
      try{ key = new URL(url).hostname.replace(/^www\./,''); }catch(_){ key = 'Link '+idx; }
    }
    key = safeKey(key);
    let base = key, i = 2;
    while (used.has(key)) { key = `${base}_${i++}`; }
    used.add(key);
    map[key] = url;
    idx++;
  });

  return map;
}

async function saveBatch(){
  if(!bm_title_input.value.trim()) return Swal.fire('Title required','Please enter a badge title.','info');
  if(!bm_course_id.value) return Swal.fire('Course missing','Pick a course from the toolbar.','info');
  if(bm_start.value && bm_end.value && (new Date(bm_end.value) < new Date(bm_start.value)))
    return Swal.fire('Invalid dates','End date cannot be before start date.','info');

  const fd=new FormData();
  fd.append('course_id', bm_course_id.value);
  fd.append('badge_title', bm_title_input.value.trim());
  if(bm_desc.value.trim())    fd.append('badge_description', bm_desc.value.trim());
  if(bm_tagline.value.trim()) fd.append('tagline', bm_tagline.value.trim());
  fd.append('mode', bm_mode_select.value);
  if(bm_contact.value.trim()) fd.append('contact_number', bm_contact.value.trim());
  if(bm_note.value.trim())    fd.append('badge_note', bm_note.value.trim());
  fd.append('status', bm_status.value);
  if(bm_start.value) fd.append('starts_on', bm_start.value);
  if(bm_end.value)   fd.append('ends_on', bm_end.value);

  const kv = collectGroupLinks();
  const keys = Object.keys(kv);
  if (keys.length){
    keys.forEach(k => fd.append(`group_links[${k}]`, kv[k]));
  } else {
    fd.append('group_links[]','');
  }

  if(bm_image.files && bm_image.files[0]) fd.append('featured_image', bm_image.files[0]);

  try{
    let url='/api/batches', method='POST';
    if (bm_mode.value==='edit' && bm_uuid.value) {
      url = `/api/batches/${encodeURIComponent(bm_uuid.value)}`;
      fd.append('_method','PATCH');
      method = 'POST';
    }

    const res = await fetch(url,{
      method,
      headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json', 'Cache-Control':'no-cache' },
      body: fd
    });

    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(firstError(j)||'Save failed');

    ok('Batch saved');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('batchModal')).hide();
    load();
  }catch(e){ err(e.message||'Save failed'); }
}


/* ================== Archive/Unarchive/Delete/Restore ================== */
document.addEventListener('click',(e)=>{
  const item=e.target.closest('.dropdown-item[data-act]'); if(!item) return;
  e.preventDefault(); const act=item.dataset.act, uuid=item.dataset.uuid;
  if(act==='view') openView(uuid);
  if(act==='edit') openEditModal(uuid);
  if(act==='instructors') openInstructors(uuid);
  if(act==='assign') openStudents(uuid);
  if(act==='archive') return archiveBatch(uuid);
  if(act==='unarchive') return unarchiveBatch(uuid);
  if(act==='delete') return deleteBatch(uuid);
  if(act==='restore') return restoreBatch(uuid);
  const toggle=item.closest('.dropdown')?.querySelector('.dd-toggle'); if(toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
});
async function archiveBatch(uuid){
  const {isConfirmed}=await Swal.fire({icon:'question',title:'Archive batch?',showCancelButton:true,confirmButtonText:'Archive',confirmButtonColor:'#8b5cf6'}); if(!isConfirmed) return;
  try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}/archive`,{method:'PATCH',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Archive failed'); ok('Batch archived'); load(); }catch(e){ err(e.message); }
}
async function unarchiveBatch(uuid){
  try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{method:'PATCH',headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({status:'active'})}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Unarchive failed'); ok('Batch unarchived'); load(); }catch(e){ err(e.message); }
}
async function deleteBatch(uuid){
  const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete batch?',text:'This moves the batch to Bin (soft delete).',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'}); if(!isConfirmed) return;
  try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}`,{method:'DELETE',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Delete failed'); ok('Batch deleted'); load(); }catch(e){ err(e.message); }
}
async function restoreBatch(uuid){
  try{ const res=await fetch(`/api/batches/${encodeURIComponent(uuid)}/restore`,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'}}); const j=await res.json().catch(()=>({})); if(!res.ok) throw new Error(firstError(j)||'Restore failed'); ok('Batch restored'); load(); }
  catch(e){ err(e.message); }
}
</script>
@endpush
