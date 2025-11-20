{{-- resources/views/Assignments.blade.php --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* same compact CSS style as study material page but with as- prefix where needed */
.crs-wrap{ }
.as-list{max-width:1100px;margin:18px auto}
.as-card{border-radius:12px;padding:18px}
.as-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-radius:10px;border:1px solid var(--line-strong)}
.as-item+.as-item{margin-top:10px}
.as-item .left{display:flex;gap:12px;align-items:center}
.as-item .meta{display:flex;flex-direction:column;gap:4px}
.as-item .meta .title{font-weight:700;color:var(--ink);font-family:var(--font-head)}
.as-item .meta .sub{color:var(--muted-color);font-size:13px}
.as-item .btn{padding:6px 10px;border-radius:8px;font-size:13px}
.as-empty{border:1px dashed var(--line-strong);border-radius:12px;padding:18px;background:transparent;color:var(--muted-color);text-align:center}
.as-loader{display:flex;align-items:center;gap:8px;color:var(--muted-color)}
.duration-pill{font-size:12px;color:var(--muted-color);background:transparent;border-radius:999px;padding:4px 8px;border:1px solid var(--line-strong)}
.as-fullscreen{position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:2147483647;display:flex;align-items:center;justify-content:center;padding:18px}
.as-fullscreen .fs-inner{width:100%;height:100%;max-width:1400px;max-height:92vh;background:#fff;border-radius:8px;overflow:hidden;display:flex;flex-direction:column}
.as-fullscreen .fs-header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid rgba(0,0,0,0.06);background:#fafafa}
.as-fullscreen .fs-title{font-weight:700;font-size:16px;color:#111}
.as-fullscreen .fs-close{border:0;background:transparent;font-size:18px;cursor:pointer;padding:6px 10px}
.as-fullscreen .fs-body{flex:1;display:flex;align-items:center;justify-content:center;padding:10px;background:#fff}
.as-fullscreen iframe,.as-fullscreen img,.as-fullscreen video{width:100%;height:100%;object-fit:contain;border:0}
.as-more{position:relative;display:inline-block}
.as-more .as-dd-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);padding:6px 8px;border-radius:10px;cursor:pointer;font-size:var(--fs-14)}
.as-more .as-dd{position:absolute;top:calc(100% + 6px);right:0;min-width:160px;background:var(--surface);border:1px solid var(--line-strong);box-shadow:var(--shadow-2);border-radius:10px;overflow:hidden;display:none;z-index:1000;padding:6px 0}
.as-more .as-dd.show{display:block}
.as-more .as-dd a,.as-more .as-dd button.dropdown-item{display:flex;align-items:center;gap:10px;padding:10px 12px;text-decoration:none;color:inherit;cursor:pointer;background:transparent;border:0;width:100%;text-align:left;font-size:14px}
.as-more .as-dd a:hover,.as-more .as-dd button.dropdown-item:hover{background:color-mix(in oklab,var(--muted-color) 6%,transparent)}
.as-more .as-dd .divider{height:1px;background:var(--line-strong);margin:6px 0}
.as-more .as-dd i{width:18px;text-align:center;font-size:14px}
.as-icon-purple{color:#6f42c1}
.as-icon-red{color:#dc3545}
.as-icon-black{color:#111}
.as-more .as-dd a.text-danger,.as-more .as-dd button.dropdown-item.text-danger{color:var(--danger-color,#dc2626)!important}
@media(max-width:720px){.as-item{flex-direction:column;align-items:flex-start}.as-item .right{width:100%;display:flex;justify-content:flex-end;gap:8px}.as-more .as-dd{right:6px;left:auto;min-width:160px}}
/* Make modal body scrollable and fit inside viewport (kept for details modal) */
.modal.show .modal-dialog { max-height: calc(100vh - 48px); }
.modal.show .modal-content { height: 100%; display: flex; flex-direction: column; }
.modal.show .modal-body { overflow: auto; max-height: calc(100vh - 200px); -webkit-overflow-scrolling: touch; }

#as_existing_attachments .btn { padding: 6px 8px; font-size: 13px; }
#as_existing_attachments .small.text-primary { text-decoration: underline; cursor: pointer; }
/* Modal RTE — make it match the larger, rounded editor */
.modal .toolbar { gap:10px; margin-bottom:10px; }
.modal .toolbar .tool.btn {
  border:1px solid var(--line-strong);
  background:var(--surface);
  padding:8px 10px;
  min-width:40px;
  height:40px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  box-shadow: none;
  transition: background .12s, transform .06s;
}
.modal .toolbar .tool.btn i { font-size:14px; }
.modal .toolbar .tool.btn:active { transform: translateY(1px); }
.modal .toolbar .tiny { margin-left:8px; color:var(--muted-color); }

/* Editor surface */
.modal .rte-wrap { position:relative; }
.modal .rte {
  min-height:180px;           /* taller area */
  max-height:420px;
  padding:18px;               /* roomy padding */
  border-radius:12px;         /* rounded corners */
  border:1px solid var(--line-strong);
  background: var(--surface, #fff);
  overflow:auto;
  line-height:1.6;
  outline: none;
  box-shadow: none;
  transition: box-shadow .12s, border-color .12s;
  font-size:15px;
  color:var(--ink);
}

/* Focus ring */
.modal .rte:focus {
  box-shadow: 0 0 0 6px color-mix(in oklab, var(--accent-color) 10%, transparent);
  border-color: var(--accent-color);
}

/* placeholder (visible when rte empty) */
.modal .rte-ph {
  position: absolute;
  top: 18px;
  left: 18px;
  right: 18px;
  color: var(--muted-color);
  pointer-events: none;
  font-size: 15px;
  line-height: 1.4;
}

/* hide placeholder when editor has content */
.modal .rte.has-content + .rte-ph { display: none; }

/* ensure toolbar wraps nicely on small widths */
.modal .toolbar { flex-wrap:wrap; align-items:center; }

/* make file-list / chips match modal sizing */
.modal .type-chip { padding:6px 10px; border-radius:999px; font-size:13px; }
/* toolbar button base (keep your existing sizes) */
.tool {
  padding: 8px 10px;
  width:40px;
  height:40px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius:999px;
  border:1px solid rgba(111,66,193,0.12); /* subtle ring */
  background: var(--surface, #fff);
  color:var(--ink, #111);
  cursor:pointer;
  transition: transform .08s ease, box-shadow .12s ease, background .12s ease;
  box-shadow: none;
}

/* hover */
.tool:hover { transform: translateY(-1px); }

/* active / selected */
.tool.active {
  background: var(--primary-color, #6f42c1); /* primary bg */
  color: #000 !important;                      /* black text as requested */
  font-weight: 600;
  box-shadow: 0 6px 18px color-mix(in oklab, var(--primary-color, #6f42c1) 14%, transparent);
  transform: translateY(-1px) scale(1.02);
  border-color: rgba(0,0,0,0.06);
}

/* when using icon inside, make icon inherit color */
.tool i { color: inherit; }

/* focused keyboard navigation */
.tool:focus {
  outline: 3px solid color-mix(in oklab, var(--primary-color, #6f42c1) 10%, transparent);
  outline-offset: 2px;
}
#as-details-body {
  white-space: normal !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
}
/* Toggle wrapper */
.late-toggle {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
  cursor: pointer;
}

/* Hide actual checkbox */
.late-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* Track (background) */
.late-toggle .slider {
  position: absolute;
  inset: 0;
  background: #dfd6e4ff;  /* Purple like your screenshot */
  border-radius: 34px;
  transition: .3s;
}

/* Knob (circle) */
.late-toggle .slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 3px;
  top: 3px;
  background: white;
  border-radius: 50%;
  transition: .3s;
}

/* On state */
.late-toggle input:checked + .slider {
  background: #7a0bc4; /* darker purple like screenshot */
}

/* Move knob when ON */
.late-toggle input:checked + .slider:before {
  transform: translateX(24px);
}
/* Custom backdrop ONLY for Submit Assignment Modal */
#submitAssignmentModal.show ~ .modal-backdrop {
  background-color: rgba(0,0,0,0.85) !important;  /* dark background */
  opacity: 1 !important;                           /* full opacity */
  backdrop-filter: blur(4px);                       /* smooth blur effect */
}
/* Blur effect when submit modal is open */
body.modal-open #submitAssignmentModal ~ .modal-backdrop {
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
  background-color: rgba(0,0,0,0.85) !important;
}
/*  */
.grade-modal-inner {
    border-radius: 16px !important;   /* or 20px, 24px, whatever you like */
}


/* Make sure other modals don't get the blur */
body.modal-open .modal-backdrop:not(#submitAssignmentModal ~ .modal-backdrop) {
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
}
body.role-privileged #submitAssignSend {
  display: none !important;
}
/* #submitAssignmentModal.privileged .form-label { display:none !important; } */
#submitAssignmentModal.privileged .form-label label[for="st-attachment"] { display:none !important; }


</style>

<div class="crs-wrap">
  <div class="panel as-card rounded-1 shadow-1" style="padding:18px;">
    <div class="d-flex align-items-center w-100">
      <h2 class="panel-title d-flex align-items-center gap-2 mb-0">
        <i class="fa fa-file-lines" style="color: var(--primary-color);"></i>
        Assignments
      </h2>

      <!-- BIN BUTTON pushed to the end -->
      <button id="btn-bin" class="btn btn-light text-danger ms-auto" title="Bin / Deleted Items">
        <i class="fa fa-trash text-danger"></i> Bin
      </button>
    </div>

    <div class="panel-head w-100 mt-3">
      <div class="container-fluid px-0">
        <div class="p-3 border rounded-3">
          <div class="row g-3 align-items-center">
            <div class="col-md-5 col-lg-4">
              <div class="input-group">
                <span class="input-group-text">
                  <i class="fa fa-search text-muted"></i>
                </span>
                <input id="as-search" type="text" class="form-control" placeholder="Search assignments...">
              </div>
            </div>

            <div class="col-md-4 col-lg-4 d-flex align-items-center gap-2">
              <select id="as-sort" class="form-select">
                <option value="" disabled selected>Sort by</option>
                <option value="created_desc">Newest first</option>
                <option value="created_asc">Oldest first</option>
                <option value="title_asc">Title A → Z</option>
              </select>
              <button id="btn-refresh" class="btn btn-outline-primary d-flex align-items-center gap-1">
                <i class="fa fa-rotate-right"></i>
                Refresh
              </button>
            </div>

            <div class="col-md-2 col-lg-4 d-flex justify-content-end">
              <!-- Upload visible only for admin/instructor (kept but no modal) -->
              <button id="btn-upload" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="createAssignmentModal" style="display:none;">
                + Assignment
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div style="margin-top:14px;">
      <div id="as-loader" class="as-loader" style="display:none;">
        <div class="spin" aria-hidden="true"></div>
        <div class="text-muted">Loading assignments…</div>
      </div>

      <div id="as-empty" class="as-empty" style="display:none;">
        <div style="font-weight:600; margin-bottom:6px;">No assignments yet</div>
        <div class="text-muted small">Assignments uploaded by instructors will appear here.</div>
      </div>

      <div id="as-items" style="display:none; margin-top:8px;">
        <!-- items inserted by JS -->
      </div>
    </div>
  </div>
</div>
<!-- + Assignment Modal (paste into Assignments page) -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus me-2"></i> Add Assignment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="assignCreateForm" class="needs-validation" novalidate>
        <div class="modal-body">
          <div id="assignCreateAlert" style="display:none;" class="alert alert-danger small"></div>

          <!-- HIDDEN: batch will be auto extracted -->
          <input type="hidden" id="assign_batch_key" name="batch_uuid" value="">

          <!-- BASIC -->
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input id="assign_title" name="title" type="text" class="form-control" maxlength="255" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Slug</label>
              <input id="assign_slug" name="slug" type="text" class="form-control" maxlength="140" placeholder="auto">
              <div class="tiny">Leave blank to auto-generate</div>
            </div>

            <div class="col-12">
              <label class="form-label">Instructions</label>
              <div class="toolbar" aria-label="Instructions toolbar">
                <button type="button" class="tool btn btn-sm" data-cmd="bold"><i class="fa fa-bold"></i></button>
                <button type="button" class="tool btn btn-sm" data-cmd="italic"><i class="fa fa-italic"></i></button>
                <button type="button" class="tool btn btn-sm" data-cmd="insertUnorderedList"><i class="fa fa-list-ul"></i></button>
                <button type="button" class="tool btn btn-sm" data-cmd="insertOrderedList"><i class="fa fa-list-ol"></i></button>
                <button type="button" class="tool btn btn-sm" id="assignBtnLink"><i class="fa fa-link"></i></button>
                <span class="tiny ms-2">Task requirements, guidelines</span>
              </div>
              <div class="rte-wrap">
                <div id="assign_instructions" class="rte" contenteditable="true" spellcheck="true"></div>
                <div class="rte-ph">Add assignment instructions…</div>
              </div>
            </div>

            <!-- Submission settings -->
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select id="assign_status" name="status" class="form-select">
                <option value="published" selected>Published</option>
                <option value="draft">Draft</option>
                <option value="closed">Closed</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Submission Type</label>
              <select id="assign_submission_type" name="submission_type" class="form-select">
                <option value="file" selected>File Upload</option>
                <option value="text">Text</option>
                <option value="link">Link</option>
                <option value="code">Code</option>
                <option value="mixed">Mixed</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Attempts Allowed</label>
              <input id="assign_attempts_allowed" name="attempts_allowed" class="form-control" type="number" min="0" value="1">
            </div>

            <div class="col-md-6">
              <label class="form-label">Total Marks</label>
              <input id="assign_total_marks" name="total_marks" class="form-control" type="number" min="0" placeholder="100">
            </div>
            <div class="col-md-6">
              <label class="form-label">Pass Marks</label>
              <input id="assign_pass_marks" name="pass_marks" class="form-control" type="number" min="0" placeholder="70">
            </div>

            <div class="col-md-6">
              <label class="form-label">Due At</label>
              <input id="assign_due_at" name="due_at" class="form-control" type="datetime-local">
            </div>
            <div class="col-md-6">
              <label class="form-label">End At</label>
              <input id="assign_end_at" name="end_at" class="form-control" type="datetime-local">
            </div>
<div class="col-md-6 d-flex align-items-center">
  <label class="form-label mb-0 me-3">Allow Late Submissions</label>

  <label class="late-toggle">
    <input type="checkbox" id="assign_allow_late" />
    <span class="slider"></span>
  </label>

  <span class="tiny ms-2" style="display:none;">Accept submissions after due date</span>
</div>

<div class="col-md-6">
  <label class="form-label">Late Penalty (%)</label>
  <input id="assign_late_penalty" class="form-control" type="number" min="0" max="100" disabled>
</div>

            <!-- allowed submission types -->
            <div class="col-12">
              <button type="button" id="assignAllowedBtn" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#assignAllowedTypesModal">
                <i class="fa fa-list-check me-1"></i> Allowed submission types
              </button>
              <div id="assignSelectedTypeWrap" class="selected-types mt-2"></div>
            </div>

            <!-- attachments -->
            <div class="col-12">
              <label class="form-label mt-2">Attachments (optional)</label>
              <div id="assign_dropzone" class="dropzone">
                <div class="drop-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
                <div class="lead">Drag & drop files here</div>
                <div class="tiny mt-1">PDF, DOC, Images • max 50MB each</div>
                <div class="drop-actions mt-2">
                  <label class="btn btn-outline-primary mb-0" for="assign_attachments">Choose Files</label>
                  <input id="assign_attachments" name="attachments[]" type="file" multiple hidden>
                  <button type="button" id="assign_btnClearFiles" class="btn btn-light">Clear</button>
                </div>
              </div>
              <div id="assign_fileList" class="file-list mt-2"></div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" id="assignCancel" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button id="assignSave" type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Create
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Allowed types modal (small, reused inside this page) -->
<div class="modal fade" id="assignAllowedTypesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-list-check me-2"></i> Allowed file types</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="type-grid mb-3">
          <!-- a small subset; you can add more like in create page -->
          <label class="type-item"><input type="checkbox" data-type="pdf"> <i class="fa fa-file-pdf text-danger"></i> <span>PDF</span></label>
          <label class="type-item"><input type="checkbox" data-type="docx"> <i class="fa fa-file-word text-primary"></i> <span>DOCX</span></label>
          <label class="type-item"><input type="checkbox" data-type="zip"> <i class="fa fa-file-zipper text-warning"></i> <span>ZIP</span></label>
          <label class="type-item"><input type="checkbox" data-type="jpg"> <i class="fa fa-image text-success"></i> <span>JPG</span></label>
          <label class="type-item"><input type="checkbox" data-type="png"> <i class="fa fa-image text-success"></i> <span>PNG</span></label>
          <label class="type-item"><input type="checkbox" data-type="pdf"> <i class="fa fa-file-pdf text-danger"></i> <span>PDF</span></label>
        </div>

        <div class="mb-2">
          <label class="form-label tiny mb-1">Add custom type</label>
          <div style="display:flex;gap:8px">
            <input id="assign_custom_type" class="form-control form-control-sm" placeholder="e.g., mp4, svg">
            <button id="assign_btnAddType" class="btn btn-sm btn-outline-primary" type="button">Add</button>
          </div>
          <div class="tiny mt-1">Enter extension without dot</div>
        </div>

        <div class="mt-3">
          <label class="form-label tiny mb-1">Selected types</label>
          <div id="assign_modalSelectedType" style="display:flex;gap:8px;flex-wrap:wrap"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button id="assign_btnClearAllTypes" type="button" class="btn btn-light">Clear</button>
        <button id="assign_btnSaveTypes" type="button" class="btn btn-primary" data-bs-dismiss="modal">Save</button>
      </div>
    </div>
  </div>
</div>
<!-- Submit Assignment Modal -->
<div class="modal fade" id="submitAssignmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <!-- Header -->
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-upload me-2"></i> Submit Assignment
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Form -->
      <form id="submitAssignmentForm" class="needs-validation" novalidate>
        
        <input type="hidden" id="submit_assignment_key" name="assignment_key" />

        <div class="modal-body">

          <!-- Alert -->
          <div id="submitAssignAlert" class="alert alert-danger small" style="display:none;"></div>

          <!-- Title + Meta -->
          <div class="mb-2">
            <strong id="submit_assign_title">Assignment title</strong>
            <div class="tiny text-muted" id="submit_assign_meta"></div>
          </div>
          <!-- NEW: Allowed Types Note -->
          <div id="submit_note" class="alert alert-info mb-3">
            <strong>Note:</strong> <span id="submit_note_content">Loading submission info...</span>
          </div>

          <!-- NEW: Attempts Counter -->
          <div id="submit_attempts" class="alert alert-warning mb-3">
            <strong>Attempts:</strong> <span id="submit_attempts_content">Loading attempts info...</span>
          </div>
          <!-- Submit Instructions -->
          <div class="mb-3">
            <label class="form-label">Submit Instructions</label>
            <div id="submit_instructions"
              class="p-3"
              style="border:1px dashed var(--line-strong); border-radius:12px;min-height:80px; color:var(--muted-color);">
              <div class="tiny text-muted">No instructions provided.</div>
            </div>
          </div>
          <!-- Attachments -->
          <div class="mb-2">
            <label class="form-label" id="st-attachment">Attachments<span class="text-danger">*</span></label>

            <!-- Drag & Drop Zone -->
            <div id="submit_dropzone"
              class="dropzone"
              style="border-radius:12px;border:2px dashed #9b27c2;background:rgba(123,17,148,0.03);padding:34px;text-align:center;cursor:pointer;">

              <div style="font-size:28px;margin-bottom:8px;color:#6f42c1;">
                <i class="fa fa-cloud-arrow-up"></i>
              </div>

              <div style="font-size:18px;font-weight:600;color:var(--ink);">
                Drag & drop files here
              </div>

              <div class="tiny text-muted mt-1">PDF, DOC, Images • max 50MB each</div>

              <!-- Buttons -->
              <div class="mt-3 d-flex gap-2 justify-content-center">
                <label for="submit_attachments" class="btn btn-outline-primary mb-0" style="border-radius:10px;padding:8px 14px;">
                  Choose Files
                </label>
                <button id="submit_btnClearFiles" type="button" class="btn btn-light" style="border-radius:10px;padding:8px 14px;">
                  Clear
                </button>
              </div>

              <!-- Hidden File Input -->
              <input id="submit_attachments" name="attachments[]" type="file" multiple hidden required>
            </div>

            <!-- File List -->
            <div id="submit_fileList" class="mt-3"></div>
          </div>

          <!-- NEW: Previous Submissions -->
          <div id="submit_existing_submissions" class="mt-4">
            <h6 class="border-bottom pb-2">Your Previous Submissions</h6>
            <div id="submit_existing_list" class="mt-2">
              <div class="text-muted">Loading your submissions...</div>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" id="submitAssignCancel" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="submitAssignSend" class="btn btn-primary">
            <i class="fa fa-paper-plane me-1"></i> Submit
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

{{-- Details Modal (shows assignment metadata — opens when user clicks View in ⋮ menu) --}}
<div id="as-details-modal" class="modal" style="display:none;" aria-hidden="true">
  <div class="modal-dialog" style="max-width:560px; margin:80px auto;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assignment Details</h5>
        <button type="button" id="as-details-close" class="btn btn-light">Close</button>
      </div>
      <div class="modal-body" id="as-details-body" style="padding:18px;">
        <!-- Filled by JS -->
      </div>
      <div class="modal-footer" id="as-details-footer" style="display:none;">
        <!-- optional actions injected by JS (Edit for admins) -->
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  const role=(sessionStorage.getItem('role')||localStorage.getItem('role')||'').toLowerCase();
  const TOKEN=localStorage.getItem('token')||sessionStorage.getItem('token')||'';
  if(!TOKEN){ Swal.fire({icon:'warning',title:'Login required',text:'Please sign in to continue.',allowOutsideClick:false,allowEscapeKey:false}).then(()=>{window.location.href='/';}); return; }

  const isAdmin=role.includes('admin')||role.includes('super_admin')||role.includes('superadmin');
  const isSuperAdmin=role.includes('super_admin')||role.includes('superadmin');
  const isInstructor=role.includes('instructor');
  const canCreate=isAdmin||isSuperAdmin||isInstructor;
  const canEdit=canCreate;
  const canDelete=canCreate;
  const canViewBin=isAdmin||isSuperAdmin;
  const isPrivileged = isAdmin || isInstructor;

  const apiBase='/api/assignments';
  const defaultHeaders={'Authorization':'Bearer '+TOKEN,'Accept':'application/json'};

  const $loader=document.getElementById('as-loader'), $empty=document.getElementById('as-empty'),
        $items=document.getElementById('as-items'), $search=document.getElementById('as-search'),
        $sort=document.getElementById('as-sort'), $refresh=document.getElementById('btn-refresh'),
        $uploadBtn=document.getElementById('btn-upload'), $btnBin=document.getElementById('btn-bin');
  const detailsModal=document.getElementById('as-details-modal'), detailsBody=document.getElementById('as-details-body'),
        detailsClose=document.getElementById('as-details-close'), detailsFooter=document.getElementById('as-details-footer');

  const showOk=(msg)=>Swal.fire({toast:true,position:'top-end',icon:'success',title:msg||'Done',showConfirmButton:false,timer:2500,timerProgressBar:true});
  const showErr=(msg)=>Swal.fire({toast:true,position:'top-end',icon:'error',title:msg||'Something went wrong',showConfirmButton:false,timer:3500,timerProgressBar:true});
  const showLoader=v=>{$loader&&( $loader.style.display=v?'flex':'none');};
  const showEmpty=v=>{$empty&&($empty.style.display=v?'block':'none');};
  const showItems=v=>{$items&&($items.style.display=v?'block':'none');};
  const formatSize=b=>{if(b==null)return'';const u=['B','KB','MB','GB'];let i=0;let n=Number(b);while(n>=1024&&i<u.length-1){n/=1024;i++;}return `${n.toFixed(n<10&&i>0?1:0)} ${u[i]}`;};
  const escapeHtml=str=>String(str||'').replace(/[&<>"'`=\/]/g,s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60","=":"&#x3D;"}[s]));
  const lateCheckbox = document.getElementById('assign_allow_late');
const latePenalty = document.getElementById('assign_late_penalty');
if (isPrivileged) document.body.classList.add('role-privileged');

function syncLate() {
  if (lateCheckbox.checked) {
    latePenalty.disabled = false;
  } else {
    latePenalty.disabled = true;
    latePenalty.value = "";
  }
}

lateCheckbox.addEventListener("change", syncLate);
syncLate();

  async function apiFetch(url,opts={}){opts.headers=Object.assign({},opts.headers||{},defaultHeaders);const res=await fetch(url,opts);if(res.status===401){try{await Swal.fire({icon:'warning',title:'Session expired',text:'Please login again.',allowOutsideClick:false,allowEscapeKey:false});}catch(e){}location.href='/';throw new Error('Unauthorized');}return res;}

  const deriveCourseKey=()=>{const p=location.pathname.split('/').filter(Boolean);const last=p.at(-1)?.toLowerCase();if(last==='view'&&p.length>=2)return p.at(-2);return p.at(-1);};
  function getQueryParam(name){try{return(new URL(window.location.href)).searchParams.get(name);}catch(e){return null;}}

  (function ensureBatchInDomFromUrl(){const host=document.querySelector('.crs-wrap');if(!host)return;const existing=host.dataset.batchId??host.dataset.batch_id??'';if(!existing||String(existing).trim()===''){const pathKey=deriveCourseKey();if(pathKey){host.dataset.batchId=String(pathKey);host.dataset.batch_id=String(pathKey);}const qModule=getQueryParam('module')||getQueryParam('course_module_id');if(qModule){host.dataset.moduleId=String(qModule);host.dataset.module_id=String(qModule);}}})();

  function readContext(){const host=document.querySelector('.crs-wrap');if(host){const batchId=host.dataset.batchId??host.dataset.batch_id??'';const moduleId=host.dataset.moduleId??host.dataset.module_id??'';if(batchId)return {batch_id:String(batchId)||null,module_id:moduleId||null};}const pathBatch=deriveCourseKey()||null;const qModule=getQueryParam('module')||getQueryParam('course_module_id')||null;return {batch_id:pathBatch||null,module_id:qModule||null};}
  function readContextFallback(){const host=document.querySelector('.crs-wrap');if(host){const batchId=host.dataset.batchId??host.dataset.batch_id??'';const moduleId=host.dataset.moduleId??host.dataset.module_id??'';if(batchId)return {batch_id:String(batchId),module_id:String(moduleId)||null};}const pathBatch=deriveCourseKey()||null;const qModule=(new URL(window.location.href)).searchParams.get('module')||(new URL(window.location.href)).searchParams.get('course_module_id')||null;return {batch_id:pathBatch,module_id:qModule};}

  function closeAllDropdowns(){document.querySelectorAll('.as-more .as-dd.show').forEach(d=>{d.classList.remove('show');d.setAttribute('aria-hidden','true');});}
  document.addEventListener('click',()=>closeAllDropdowns());
  document.addEventListener('keydown',e=>{if(e.key==='Escape')closeAllDropdowns();});
  
  function normalizeAttachments(row){
    let raw=row.attachments_json??row.attachments??row.attachment??[];
    if(typeof raw==='string'&&raw.trim()!==''){try{raw=JSON.parse(raw);}catch(e){raw=[];}}
    if(raw&&!Array.isArray(raw))raw=[raw];
    const arr=(raw||[]).map((a,idx)=>{if(typeof a==='string'){const url=a;const ext=(url.split('?')[0].split('.').pop()||'').toLowerCase();return{id:`s-${idx}`,url,path:url,name:url.split('/').pop(),mime:'',ext};}const url=a.url||a.path||a.file_url||null;const name=a.original_name||a.name||(url?url.split('/').pop():`file-${idx}`);const mime=a.mime||a.content_type||'';let ext=(a.ext||a.extension||'').toLowerCase();if(!ext&&url)ext=(url.split('?')[0].split('.').pop()||'').toLowerCase();return{id:a.id||a.attachment_id||a.file_id||(`o-${idx}`),url,path:a.path,name,mime,ext,size:a.size||a.file_size||0,raw:a};});
    return arr.filter(it=>it&&(it.url||it.path));
  }

  function createItemRow(row){
    const attachments=normalizeAttachments(row);row.attachments=attachments;if(typeof row.attachment_count==='undefined')row.attachment_count=attachments.length;
    const wrapper=document.createElement('div');wrapper.className='as-item';
    const left=document.createElement('div');left.className='left';
    const icon=document.createElement('div');icon.className='icon';icon.style.width='44px';icon.style.height='44px';icon.style.borderRadius='10px';icon.style.display='flex';icon.style.alignItems='center';icon.style.justifyContent='center';icon.style.border='1px solid var(--line-strong)';icon.style.background='linear-gradient(180deg, rgba(0,0,0,0.02), transparent)';icon.innerHTML='<i class="fa fa-check-square" style="color:var(--secondary-color)"></i>';
    const meta=document.createElement('div');meta.className='meta';const title=document.createElement('div');title.className='title';title.textContent=row.title||'Untitled';const sub=document.createElement('div');sub.className='sub';
    sub.textContent = row.attachment_count ? `${row.attachment_count} attachment(s)`: 'No attachments';meta.appendChild(title);meta.appendChild(sub);left.appendChild(icon);left.appendChild(meta);
    const right=document.createElement('div');right.className='right';right.style.display='flex';right.style.alignItems='center';right.style.gap='8px';
    const datePill=document.createElement('div');datePill.className='duration-pill';datePill.textContent=row.created_at?new Date(row.created_at).toLocaleDateString():'';
    right.appendChild(datePill);
    // only show preview button when there are attachments
if (Array.isArray(row.attachments) && row.attachments.length > 0) {
  const previewBtn = document.createElement('button');
  previewBtn.className = 'btn btn-outline-primary';
  previewBtn.style.minWidth = '80px';
  previewBtn.type = 'button';
  previewBtn.textContent = 'Preview';
  previewBtn.addEventListener('click', () => openFullscreenPreview(row, row.attachments || [], 0));

  if (row.attachments.length > 1) {
    const badge = document.createElement('span');
    badge.className = 'small text-muted';
    badge.style.marginLeft = '6px';
    badge.textContent = `(${row.attachments.length})`;
    previewBtn.appendChild(badge);
  }

  right.appendChild(previewBtn);
}
    const moreWrap=document.createElement('div');moreWrap.className='as-more';
    moreWrap.innerHTML = `
  <button class="as-dd-btn" aria-haspopup="true" aria-expanded="false" title="More">⋮</button>
  <div class="as-dd" role="menu" aria-hidden="true">
    <a href="#" data-action="view"><i class="fa fa-eye as-icon-purple"></i><span>View</span></a>
    <a href="#" data-action="view-instructions"><i class="fa fa-align-left as-icon-black"></i><span>Instructions</span></a>
    ${canEdit ? `<a href="#" data-action="edit"><i class="fa fa-pen as-icon-black"></i><span>Edit</span></a>` : ''}
    ${canDelete ? `<div class="divider"></div><a href="#" data-action="delete" class="text-danger"><i class="fa fa-trash as-icon-red"></i><span>Delete</span></a>` : ''}
  </div>
`;
    right.appendChild(moreWrap);wrapper.appendChild(left);wrapper.appendChild(right);

    const ddBtn=moreWrap.querySelector('.as-dd-btn'), dd=moreWrap.querySelector('.as-dd');
    if(ddBtn&&dd){ddBtn.addEventListener('click',ev=>{ev.stopPropagation();const isOpen=dd.classList.contains('show');closeAllDropdowns();if(!isOpen){dd.classList.add('show');dd.setAttribute('aria-hidden','false');ddBtn.setAttribute('aria-expanded','true');}});}
    const viewBtn=moreWrap.querySelector('[data-action="view"]');if(viewBtn)viewBtn.addEventListener('click',ev=>{ev.preventDefault();ev.stopPropagation();openDetailsModal(row);closeAllDropdowns();});
const editBtn = moreWrap.querySelector('[data-action="edit"]');
if (typeof role !== 'undefined') {
  try {
    const normRole = String(role).toLowerCase().replace(/[-\s]/g, '_');
    const canShowSubmit = ['student', 'admin', 'instructor', 'super_admin', 'superadmin'].includes(normRole);

    // --- Submissions link for permitted roles ---
    if (canShowSubmit) {
      const a = document.createElement('a');
      a.href = '#';
      a.setAttribute('data-action', 'submit');
      a.innerHTML = `<i class="fa fa-upload as-icon-black"></i><span>Submissions</span>`;
      const divider = dd.querySelector('.divider');
      if (divider) {
        divider.insertAdjacentElement('beforebegin', a);
      } else {
        dd.appendChild(a);
      }
      a.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        closeAllDropdowns();
        if (typeof openSubmitModal === 'function') {
          openSubmitModal(row);
        } else {
          showErr('Submit modal not available');
        }
      });
    }

    // --- View Marks (student only) ---
    if (normRole === 'student') {
      const vm = document.createElement('a');
      vm.href = '#';
      vm.setAttribute('data-action', 'view-marks');
      vm.innerHTML = `<i class="fa fa-star as-icon-black"></i><span>View Marks</span>`;
      const divider2 = dd.querySelector('.divider');
      if (divider2) divider2.insertAdjacentElement('beforebegin', vm);
      else dd.appendChild(vm);

      vm.addEventListener('click', async (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        closeAllDropdowns();

        // Helper function to format marks data
        const formatMarksData = (marksData) => {
          if (!marksData) return '<p>No marks data available.</p>';
          
          // Handle array of attempts
          if (Array.isArray(marksData)) {
            return `
              <div class="marks-container" style="max-height: 400px; overflow-y: auto;">
                ${marksData.map((attempt, index) => `
                  <div class="attempt-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f9f9f9;">
                    <h4 style="margin: 0 0 10px 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">
                      Attempt ${index + 1}
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                      ${attempt.score !== undefined ? `
                        <div><strong>Score:</strong></div>
                        <div style="font-weight: bold; color: #27ae60;">${attempt.score}</div>
                      ` : ''}
                      ${attempt.total_marks !== undefined ? `
                        <div><strong>Total Marks:</strong></div>
                        <div>${attempt.total_marks}</div>
                      ` : ''}
                      ${attempt.percentage !== undefined ? `
                        <div><strong>Percentage:</strong></div>
                        <div style="font-weight: bold; color: #e67e22;">${attempt.percentage}%</div>
                      ` : ''}
                      ${attempt.grade ? `
                        <div><strong>Grade:</strong></div>
                        <div style="font-weight: bold; color: #9b59b6;">${attempt.grade}</div>
                      ` : ''}
                      ${attempt.feedback ? `
                        <div><strong>Feedback:</strong></div>
                        <div style="font-style: italic; color: #7f8c8d;">${attempt.feedback}</div>
                      ` : ''}
                      ${attempt.graded_by ? `
                        <div><strong>Graded By:</strong></div>
                        <div>${attempt.graded_by}</div>
                      ` : ''}
                      ${attempt.graded_at ? `
                        <div><strong>Graded At:</strong></div>
                        <div>${new Date(attempt.graded_at).toLocaleString()}</div>
                      ` : ''}
                      ${attempt.submitted_at ? `
                        <div><strong>Submitted At:</strong></div>
                        <div>${new Date(attempt.submitted_at).toLocaleString()}</div>
                      ` : ''}
                    </div>
                  </div>
                `).join('')}
              </div>
            `;
          }
          
          // Handle single attempt object
          else if (typeof marksData === 'object') {
            return `
              <div class="marks-container" style="max-width: 500px;">
                <div class="attempt-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #f9f9f9;">
                  <h4 style="margin: 0 0 15px 0; color: #2c3e50; text-align: center;">Marks Details</h4>
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    ${marksData.score !== undefined ? `
                      <div><strong>Score:</strong></div>
                      <div style="font-weight: bold; color: #27ae60; font-size: 1.1em;">${marksData.score}</div>
                    ` : ''}
                    ${marksData.total_marks !== undefined ? `
                      <div><strong>Total Marks:</strong></div>
                      <div>${marksData.total_marks}</div>
                    ` : ''}
                    ${marksData.percentage !== undefined ? `
                      <div><strong>Percentage:</strong></div>
                      <div style="font-weight: bold; color: #e67e22; font-size: 1.1em;">${marksData.percentage}%</div>
                    ` : ''}
                    ${marksData.grade ? `
                      <div><strong>Grade:</strong></div>
                      <div style="font-weight: bold; color: #9b59b6; font-size: 1.1em;">${marksData.grade}</div>
                    ` : ''}
                    ${marksData.feedback ? `
                      <div><strong>Feedback:</strong></div>
                      <div style="grid-column: 1 / -1; font-style: italic; color: #7f8c8d; padding: 8px; background: white; border-radius: 4px; margin-top: 5px;">
                        ${marksData.feedback}
                      </div>
                    ` : ''}
                    ${marksData.graded_by ? `
                      <div><strong>Graded By:</strong></div>
                      <div>${marksData.graded_by}</div>
                    ` : ''}
                    ${marksData.graded_at ? `
                      <div><strong>Graded At:</strong></div>
                      <div>${new Date(marksData.graded_at).toLocaleString()}</div>
                    ` : ''}
                  </div>
                </div>
              </div>
            `;
          }
          
          // Fallback for unexpected format
          return `<pre style="text-align:left; white-space: pre-wrap;">${JSON.stringify(marksData, null, 2)}</pre>`;
        };

        // Helper: present marks in formatted way
        function presentMarks(marksData) {
          if (typeof openMarksModal === 'function') {
            return openMarksModal(marksData, { assignment: row });
          }
          
          return Swal.fire({
            title: 'Submission Marks',
            html: formatMarksData(marksData),
            width: 600,
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            customClass: {
              container: 'marks-swal-container'
            }
          });
        }
        
        // 1) If marks embedded on row already — show immediately
        const embeddedMarks =
          row.marks ||
          row.my_marks ||
          row.student_marks ||
          (row.my_submission && row.my_submission.marks) ||
          (row.submission && row.submission.marks) ||
          null;

        if (embeddedMarks) {
          if (typeof openMarksModal === 'function') {
            return openMarksModal(embeddedMarks, { assignment: row });
          }
          return presentMarks(embeddedMarks);
        }

        // 2) Try assignment-scoped route first: /api/assignments/{id}/student/marks
        const assignId = row.id || row.uuid || row.assignment_id || row.key || row.slug || null;
        if (assignId) {
          try {
            const url = `/api/assignments/${encodeURIComponent(assignId)}/student/marks`;
            const res = await apiFetch(url, { method: 'GET' });
            if (res && res.ok) {
              const j = await res.json().catch(() => null);
              const marksData = j && (j.data || j.marks) ? (j.data || j.marks) : j;
              if (marksData) {
                return presentMarks(marksData);
              }
              // if OK but empty, fall through to discovery
            }
          } catch (e) {
            console.warn('assignment-scoped marks route failed', e);
            // fall through
          }
        }

        // 3) Try to find a submission id on the row
        let submissionId =
          row.submission_id ||
          (row.submission && (row.submission.id || row.submission.uuid)) ||
          (row.my_submission && (row.my_submission.id || row.my_submission.uuid)) ||
          (row.latest_submission && (row.latest_submission.id || row.latest_submission.uuid)) ||
          (row.user_submission && (row.user_submission.id || row.user_submission.uuid)) ||
          (row.submissions && Array.isArray(row.submissions) && row.submissions[0] && (row.submissions[0].id || row.submissions[0].uuid)) ||
          null;

        // helper: discover student's submission via likely endpoints
        async function discoverSubmissionFromApi(assignIdOrKey) {
          if (!assignIdOrKey) return null;
          const candidates = [
            `/api/assignments/${encodeURIComponent(assignIdOrKey)}/my-submission`,
            `/assignments/${encodeURIComponent(assignIdOrKey)}/my-submission`,
            `/api/assignments/${encodeURIComponent(assignIdOrKey)}/submissions?mine=1`,
            `/api/assignments/${encodeURIComponent(assignIdOrKey)}/submissions?me=1`,
            `/api/submissions?assignment_id=${encodeURIComponent(assignIdOrKey)}&me=1`,
            `/api/submissions?assignment_key=${encodeURIComponent(assignIdOrKey)}&me=1`,
            `/api/submissions?assignment_id=${encodeURIComponent(assignIdOrKey)}`
          ];
          for (const p of candidates) {
            try {
              const r = await apiFetch(p, { method: 'GET' });
              if (!r || !r.ok) continue;
              const j = await r.json().catch(() => null);
              if (!j) continue;
              const candidate = j && (j.data || j.submission || j.submissions || j.items) ? (j.data || j.submission || j.submissions || j.items) : j;
              if (!candidate) continue;
              if (Array.isArray(candidate) && candidate.length) {
                const s = candidate[0];
                const id = s && (s.id || s.uuid);
                if (id) return id;
              } else if (typeof candidate === 'object') {
                const id = candidate.id || candidate.uuid || (candidate.submission && (candidate.submission.id || candidate.submission.uuid));
                if (id) return id;
              }
            } catch (e) {
              // ignore and continue
              continue;
            }
          }
          return null;
        }

        // 4) If no submissionId found, try discovery using assignment id/title
        if (!submissionId) {
          const assignGuess = row.id || row.uuid || row.assignment_id || row.key || row.slug || row.title || null;
          if (assignGuess) {
            try {
              submissionId = await discoverSubmissionFromApi(assignGuess);
            } catch (e) {
              console.warn('submission discovery failed', e);
            }
          }
        }

        if (!submissionId) {
          showErr('No submission found for this assignment. Submit first to view marks.');
          return;
        }

        // 5) Fetch marks from submission-scoped endpoints as a final fallback
        const markPaths = [
          `/api/submissions/${encodeURIComponent(submissionId)}/marks`,
          `/submissions/${encodeURIComponent(submissionId)}/marks`
        ];
        try {
          let res = null;
          let json = null;
          for (const p of markPaths) {
            try {
              res = await apiFetch(p, { method: 'GET' });
              if (res && res.ok) {
                json = await res.json().catch(() => null);
                break;
              }
            } catch (e) {
              // try next
              continue;
            }
          }

          if (!res || !res.ok || !json) {
            showErr('Could not fetch marks (server error).');
            return;
          }

          const marksData = (json && (json.data || json.marks)) ? (json.data || json.marks) : json;
          if (!marksData) {
            showErr('No marks data returned.');
            return;
          }

          presentMarks(marksData);
        } catch (err) {
          console.error('View marks failed', err);
          showErr('Failed to load marks.');
        }
      });
    } // end if student
  } catch (ex) {
    console.warn('attach submit/view-marks action failed', ex);
  }
} // end if typeof role
if (editBtn) {
  editBtn.addEventListener('click', async (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    closeAllDropdowns();

    const idOrUuid = encodeURIComponent(row.id || row.uuid || '');
    if (!idOrUuid) {
      showErr('No assignment identifier available for edit');
      return;
    }

    try {
      // fetch the fresh single-assignment record from API
      const res = await apiFetch(`/api/assignments/${idOrUuid}`);
      if (!res.ok) {
        // try alternative endpoint if your API exposes batch/assignment route
        throw new Error('Failed to fetch assignment: ' + res.status);
      }
      const json = await res.json().catch(()=>null);
      const fresh = (json && (json.data || json.item || json.assignment)) ? (json.data || json.item || json.assignment) : (json || null);

      if (!fresh) {
        // as fallback, fall back to original row but warn
        console.warn('Edit: unable to parse API result, falling back to list item', json);
        openEditModal(row);
        return;
      }

      // open modal with fresh object
      openEditModal(fresh);
    } catch (err) {
      console.error('Failed to load assignment for edit', err);
      // Fallback: still open modal with the current row so user can edit something
      openEditModal(row);
      showErr('Could not load latest assignment data — editing local copy');
    }
  });
}

    const delBtn=moreWrap.querySelector('[data-action="delete"]');if(delBtn){delBtn.addEventListener('click',async(ev)=>{ev.preventDefault();ev.stopPropagation();const r=await Swal.fire({title:'Move to Bin?',text:`Move "${row.title||'this assignment'}" to Bin?`,icon:'warning',showCancelButton:true,confirmButtonText:'Yes, move it',cancelButtonText:'Cancel'});if(!r.isConfirmed){closeAllDropdowns();return;}try{const res=await apiFetch(`${apiBase}/${encodeURIComponent(row.id||row.uuid)}`,{method:'DELETE'});if(!res.ok)throw new Error('Delete failed: '+res.status);showOk('Moved to Bin');await loadAssignments();}catch(e){console.error(e);showErr('Delete failed');}finally{closeAllDropdowns();}});}
    const viewInstBtn = moreWrap.querySelector('[data-action="view-instructions"]');
if (viewInstBtn) {
  viewInstBtn.addEventListener('click', (ev) => {
    ev.preventDefault(); ev.stopPropagation();
    // open a modal that renders instruction HTML safely
    openInstructionsModal(row);
    closeAllDropdowns();
  });
}

    return wrapper;
  }
  
  function renderList(items){if(!$items)return;$items.innerHTML='';if(!items||items.length===0){showItems(false);showEmpty(true);return;}showEmpty(false);showItems(true);items.forEach(it=>$items.appendChild(createItemRow(it)));}
  function openInstructionsModal(row) {
  if (!detailsModal || !detailsBody) return;

  // compute a safe HTML fragment for instructions (server may send description/instruction)
  const raw = (row.description || row.instruction || row.instructions || '') + '';
  const cleaned = raw ? sanitizeHtml(raw) : '';
  const final = cleaned ? enforceSafeLinksOnFragment(cleaned) : '';

  detailsModal.style.display = 'block';
  detailsModal.classList.add('show');
  detailsModal.setAttribute('aria-hidden', 'false');

  const backdrop = document.createElement('div');
  backdrop.className = 'modal-backdrop fade show';
  backdrop.id = 'asDetailsBackdrop';
  document.body.appendChild(backdrop);

  document.body.classList.add('modal-open');
  backdrop.addEventListener('click', closeDetailsModal);

  detailsBody.innerHTML = `
    <div style="font-size:15px; line-height:1.6;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <div style="font-weight:700">${escapeHtml(row.title || 'Instructions')}</div>
        <div style="color:var(--muted-color);font-size:13px">${row.created_at ? (new Date(row.created_at)).toLocaleString() : ''}</div>
      </div>
      <div class="instruction-html">${ final || '<div class="text-muted">No instructions available.</div>' }</div>
      <div style="margin-top:8px;color:var(--muted-color);font-size:13px;"><strong>ID:</strong> ${escapeHtml(String(row.id || row.uuid || ''))}</div>
    </div>
  `;

  // footer: close button & optional edit
  if (detailsFooter) {
    detailsFooter.innerHTML = '';
    const close = document.createElement('button'); close.className = 'btn btn-light'; close.textContent = 'Close';
    close.addEventListener('click', closeDetailsModal); detailsFooter.appendChild(close);
    if (canEdit) {
      const edit = document.createElement('button'); edit.className = 'btn btn-primary'; edit.textContent = 'Edit';
      edit.addEventListener('click', () => {
        closeDetailsModal();
        const id = encodeURIComponent(row.id || row.uuid || '');
        if (id) {
          // switch modal to edit mode (we will rely on your edit wiring to set createForm.dataset.editing etc)
          // fill modal fields for editing
          openCreateModal(); // show the create modal
          // populate fields (small delay to ensure modal DOM is present)
          setTimeout(() => {
            createForm.dataset.editing = String(row.id || row.uuid || '');
            document.getElementById('assign_title').value = row.title || '';
            document.getElementById('assign_slug').value = row.slug || '';
            // populate editor with raw HTML (sanitized)
            const editor = document.getElementById(descriptionFieldId);
            if (editor) {
              editor.innerHTML = sanitizeHtml(row.description || row.instruction || row.instructions || '');
              // ensure placeholder toggles
              const ev = new Event('input'); editor.dispatchEvent(ev);
            }
            // other fields
            document.getElementById('assign_status').value = row.status || 'draft';
            document.getElementById('assign_submission_type').value = row.submission_type || 'file';
            document.getElementById('assign_attempts_allowed').value = row.attempts_allowed || row.attempts || '';
            document.getElementById('assign_total_marks').value = row.total_marks || '';
            document.getElementById('assign_pass_marks').value = row.pass_marks || '';
            if (row.due_at) document.getElementById('assign_due_at').value = row.due_at;
            if (row.end_at) document.getElementById('assign_end_at').value = row.end_at;
            // preserved attachments: if server returned attachments with ids, store them
            window.existingAttachmentIds = Array.isArray(row.attachments) ? row.attachments.map(a => a.id || a.attachment_id || a.file_id).filter(Boolean) : [];
            // rebuild UI for allowed types etc if server sends allowed_submission_types
            if (Array.isArray(row.allowed_submission_types)) {
              allowedTypes = row.allowed_submission_types.slice();
              renderAssignModalSelected();
              renderAssignSelectedChips();
            }
          }, 60);
        } else {
          // fallback to open admin edit page
          window.location.href = `/admin/assignments/${id}`;
        }
      });
      detailsFooter.appendChild(edit);
    }
  }
}

  function openDetailsModal(row){
    detailsModal.style.display='block';detailsModal.classList.add('show');detailsModal.setAttribute('aria-hidden','false');
    const backdrop=document.createElement('div');backdrop.className='modal-backdrop fade show';backdrop.id='asDetailsBackdrop';document.body.appendChild(backdrop);
    document.body.classList.add('modal-open');backdrop.addEventListener('click',closeDetailsModal);
    const attachments=row.attachments&&Array.isArray(row.attachments)?row.attachments:[];const attachList=attachments.length?attachments.map(a=>{const name=a.name||(a.url||a.path||'').split('/').pop();const size=a.size?` (${formatSize(a.size)})`:'';return`<div style="display:flex; justify-content:space-between; gap:8px;"><div>${escapeHtml(name)}</div><div style="color:var(--muted-color); font-size:13px;">${escapeHtml(a.mime||a.ext||'')}${size}</div></div>`;}).join(''):'<div style="color:var(--muted-color)">No attachments</div>';
    if(detailsBody){detailsBody.innerHTML=`<div style="display:flex; flex-direction:column; gap:12px; font-size:15px;"><div><strong>Title:</strong> ${escapeHtml(row.title||'Untitled')}</div><div><strong>Instruction:</strong> ${escapeHtml(row.instruction||'—')}</div><div><strong>Created At:</strong> ${row.created_at?new Date(row.created_at).toLocaleString():'—'}</div><div><strong>Created By:</strong> ${escapeHtml(row.creator_name||row.created_by_name||'—')}</div><div><strong>Attachments:</strong> ${attachments.length} file(s)</div><div style="margin-top:6px;">${attachList}</div><div style="color:var(--muted-color); font-size:13px; margin-top:6px;"><strong>ID:</strong> ${escapeHtml(String(row.id||row.uuid||''))}</div></div>`;}
    if(detailsFooter){detailsFooter.innerHTML='';const close=document.createElement('button');close.className='btn btn-light';close.textContent='Close';close.addEventListener('click',closeDetailsModal);detailsFooter.appendChild(close);if(canEdit){const edit=document.createElement('button');edit.className='btn btn-primary';edit.textContent='Edit';edit.addEventListener('click',()=>{closeDetailsModal();const id=encodeURIComponent(row.id||row.uuid||'');if(id)window.location.href=`/admin/assignments/${id}`;});detailsFooter.appendChild(edit);}}
  }
  function closeDetailsModal(){detailsModal.classList.remove('show');detailsModal.style.display='none';detailsModal.setAttribute('aria-hidden','true');const bd=document.getElementById('asDetailsBackdrop');if(bd)bd.remove();document.body.classList.remove('modal-open');if(detailsBody)detailsBody.innerHTML='';if(detailsFooter)detailsFooter.innerHTML='';}
  if(detailsClose)detailsClose.addEventListener('click',closeDetailsModal);

  function closeFullscreenPreview(){const existing=document.querySelector('.as-fullscreen');if(!existing)return;const blobUrl=existing.dataset.blobUrl;if(blobUrl)try{URL.revokeObjectURL(blobUrl);}catch(e){}document.removeEventListener('contextmenu',suppressContextMenuWhileOverlay);existing.remove();document.documentElement.style.overflow='';document.body.style.overflow='';}
  function suppressContextMenuWhileOverlay(e){e.preventDefault();}
  async function fetchProtectedBlob(url){const res=await apiFetch(url,{method:'GET',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'*/*'}});if(!res.ok)throw new Error('Failed to fetch file: '+res.status);return await res.blob();}
  async function isUrlPublic(url){try{const res=await fetch(url,{method:'HEAD',mode:'cors'});return res.ok;}catch(e){try{const res2=await fetch(url,{method:'GET',headers:{'Range':'bytes=0-0'},mode:'cors'});return res2.ok;}catch(err){return false;}}}

  async function openFullscreenPreview(row,attachments=[],startIndex=0){
    closeFullscreenPreview();if(!Array.isArray(attachments)||attachments.length===0)return;
    attachments=attachments.map(a=>(typeof a==='string'?{url:a}:a||{}));let currentIndex=Math.max(0,Math.min(startIndex||0,attachments.length-1));
    const wrap=document.createElement('div');wrap.className='as-fullscreen';wrap.setAttribute('role','dialog');wrap.setAttribute('aria-modal','true');wrap.dataset.blobUrl='';wrap.dataset.currentIndex=String(currentIndex);
    const inner=document.createElement('div');inner.className='fs-inner';
    const header=document.createElement('div');header.className='fs-header';
    const title=document.createElement('div');title.className='fs-title';title.textContent=row.title||'';
    const controls=document.createElement('div');controls.style.display='flex';controls.style.alignItems='center';controls.style.gap='8px';
    const prevBtn=document.createElement('button');prevBtn.className='fs-close btn btn-sm';prevBtn.type='button';prevBtn.title='Previous';prevBtn.innerHTML='◀';
    const nextBtn=document.createElement('button');nextBtn.className='fs-close btn btn-sm';nextBtn.type='button';nextBtn.title='Next';nextBtn.innerHTML='▶';
    const idxIndicator=document.createElement('div');idxIndicator.style.fontSize='13px';idxIndicator.style.color='var(--muted-color)';idxIndicator.textContent=`${currentIndex+1} / ${attachments.length}`;
    const closeBtn=document.createElement('button');closeBtn.className='fs-close';closeBtn.innerHTML='✕';closeBtn.setAttribute('aria-label','Close preview');
    controls.appendChild(prevBtn);controls.appendChild(idxIndicator);controls.appendChild(nextBtn);header.appendChild(title);header.appendChild(controls);header.appendChild(closeBtn);
    const body=document.createElement('div');body.className='fs-body';inner.appendChild(header);inner.appendChild(body);wrap.appendChild(inner);document.body.appendChild(wrap);document.documentElement.style.overflow='hidden';document.body.style.overflow='hidden';
    // disable right-click only inside preview wrap
wrap.addEventListener('contextmenu', (e) => {
  e.preventDefault();
});


    async function renderAt(index){
      index=Math.max(0,Math.min(index,attachments.length-1));currentIndex=index;wrap.dataset.currentIndex=index;idxIndicator.textContent=`${currentIndex+1} / ${attachments.length}`;
      const prevBlob=wrap.dataset.blobUrl;if(prevBlob){try{URL.revokeObjectURL(prevBlob);}catch(e){}wrap.dataset.blobUrl='';}
      const attachment=attachments[currentIndex]||{};const urlCandidate=attachment.signed_url||attachment.url||attachment.path||null;const mime=(attachment.mime||'');const ext=((attachment.ext||'')).toLowerCase();body.innerHTML='';
      try{
        if(urlCandidate){
          const publicOk=await isUrlPublic(urlCandidate);
          if(publicOk){
            if(mime.startsWith('image/')||['png','jpg','jpeg','gif','webp'].includes(ext)){const img=document.createElement('img');img.src=urlCandidate;img.alt=attachment.name||row.title||'image';img.style.maxWidth='100%';img.style.maxHeight='100%';img.style.objectFit='contain';body.appendChild(img);return;}
            // --- PDF preview: try to fetch as blob first (so we can block right-click), fallback to direct iframe ---
            if (mime === 'application/pdf' || ext === 'pdf') {
              // attempt to fetch protected blob (this uses the existing helper in your file)
              try {
                const blob = await fetchProtectedBlob(urlCandidate);
                const blobUrl = URL.createObjectURL(blob);
                // remember it so outer cleanup can revoke it
                wrap.dataset.blobUrl = blobUrl;
              
                const iframe = document.createElement('iframe');
                // disable built-in toolbar (where possible)
                iframe.src = blobUrl + (blobUrl.indexOf('#') === -1 ? '#toolbar=0&navpanes=0&scrollbar=0' : '&toolbar=0&navpanes=0&scrollbar=0');
                iframe.setAttribute('aria-label', row.title || 'PDF preview');
                iframe.style.width = '100%';
                iframe.style.height = '100%';
                iframe.style.border = '0';
                body.appendChild(iframe);
              
                // attach contextmenu blocker inside the iframe when accessible (blob is same-origin)
                function _prevent(e){ e.preventDefault(); e.stopPropagation(); return false; }
                try {
                  // try immediate attach (some browsers allow it right away)
                  if (iframe.contentWindow && iframe.contentWindow.document) {
                    iframe.contentWindow.document.addEventListener('contextmenu', _prevent, { capture: true });
                  } else {
                    // otherwise wait for load
                    iframe.addEventListener('load', () => {
                      try { iframe.contentWindow.document.addEventListener('contextmenu', _prevent, { capture: true }); } catch(e){ console.warn('attach failed', e); }
                    }, { once: true });
                  }
                } catch (err) {
                  // if anything fails, silently ignore (we still have the blob preview)
                  console.warn('Could not attach contextmenu handler to iframe:', err);
                }
              
                return;
              } catch (err) {
                // fetchProtectedBlob failed (CORS / auth) -> fall through to public iframe fallback
                console.warn('Blob fetch failed; falling back to public iframe:', err);
              }
            
              // fallback: public iframe (cross-origin) — parent cannot block context menu
              try {
                const iframe = document.createElement('iframe');
                iframe.src = urlCandidate + (urlCandidate.indexOf('#') === -1 ? '#toolbar=0&navpanes=0&scrollbar=0' : '&toolbar=0&navpanes=0&scrollbar=0');
                iframe.setAttribute('aria-label', row.title || 'PDF preview');
                iframe.style.width = '100%';
                iframe.style.height = '100%';
                iframe.style.border = '0';
                body.appendChild(iframe);
                return;
              } catch (e) {
                body.innerHTML = '<div>Unable to preview this PDF.</div>';
                return;
              }
            }
            if(mime.startsWith('video/')||['mp4','webm','ogg'].includes(ext)){const v=document.createElement('video');v.controls=true;v.style.width='100%';const s=document.createElement('source');s.src=urlCandidate;s.type=mime||'video/mp4';v.appendChild(s);body.appendChild(v);return;}
            const iframe=document.createElement('iframe');iframe.src=urlCandidate;iframe.setAttribute('aria-label',row.title||'Preview');iframe.style.width='100%';iframe.style.height='100%';body.appendChild(iframe);return;
          }
        }
        if(!urlCandidate){body.innerHTML='<div>No preview available for this file.</div>';return;}
        const blob=await fetchProtectedBlob(urlCandidate);const blobUrl=URL.createObjectURL(blob);wrap.dataset.blobUrl=blobUrl;
        if(mime.startsWith('image/')||['png','jpg','jpeg','gif','webp'].includes(ext)){const img=document.createElement('img');img.src=blobUrl;img.alt=attachment.name||row.title||'image';img.style.maxWidth='100%';img.style.maxHeight='100%';img.style.objectFit='contain';body.appendChild(img);return;}
        if(mime==='application/pdf'||ext==='pdf'){const iframe=document.createElement('iframe');iframe.src=blobUrl+'#toolbar=0&navpanes=0&scrollbar=0';iframe.setAttribute('aria-label',row.title||'PDF preview');iframe.style.width='100%';iframe.style.height='100%';body.appendChild(iframe);return;}
        if(mime.startsWith('video/')||['mp4','webm','ogg'].includes(ext)){const v=document.createElement('video');v.controls=true;v.style.width='100%';v.src=blobUrl;body.appendChild(v);return;}
        const iframe=document.createElement('iframe');iframe.src=blobUrl;iframe.style.width='100%';iframe.style.height='100%';body.appendChild(iframe);return;
      }catch(err){console.error('Preview error',err);body.innerHTML='<div>Unable to preview this file (permission denied or unsupported).</div>';}
    }

    prevBtn.addEventListener('click',()=>{if(currentIndex>0)renderAt(currentIndex-1);});
    nextBtn.addEventListener('click',()=>{if(currentIndex<attachments.length-1)renderAt(currentIndex+1);});
    const keyHandler=e=>{if(e.key==='Escape'){closeFullscreenPreview();}if(e.key==='ArrowLeft'){if(currentIndex>0)renderAt(currentIndex-1);}if(e.key==='ArrowRight'){if(currentIndex<attachments.length-1)renderAt(currentIndex+1);}};
    document.addEventListener('keydown',keyHandler);
    function cleanupOnClose(){try{const b=wrap.dataset.blobUrl;if(b)URL.revokeObjectURL(b);}catch(e){}document.removeEventListener('keydown',keyHandler);document.removeEventListener('contextmenu',suppressContextMenuWhileOverlay);}
    closeBtn.addEventListener('click',()=>{cleanupOnClose();closeFullscreenPreview();});
    await renderAt(currentIndex);
  }

  async function loadAssignments(){
    showLoader(true);showItems(false);showEmpty(false);
    try{
      const ctx=readContext();if(!ctx||!ctx.batch_id)throw new Error('Batch context required');
      const url=`/api/batches/${encodeURIComponent(ctx.batch_id)}/assignments`;const res=await apiFetch(url);if(!res.ok)throw new Error('HTTP '+res.status);
      const json=await res.json().catch(()=>null);if(!json||!json.data)throw new Error('Invalid response format');
      const modulesWithAssignments=json.data.modules_with_assignments||[];let allAssignments=[];
      modulesWithAssignments.forEach(moduleGroup=>{if(moduleGroup.assignments&&Array.isArray(moduleGroup.assignments)){moduleGroup.assignments.forEach(assign=>{assign.module_title=moduleGroup.module?.title||'Unknown Module';assign.module_uuid=moduleGroup.module?.uuid||'';allAssignments.push(assign);});}});
      const sortVal=$sort?$sort.value:'created_desc';
      allAssignments.sort((a,b)=>{const da=a.created_at?new Date(a.created_at):new Date(0);const db=b.created_at?new Date(b.created_at):new Date(0);if(sortVal==='created_desc')return db-da; if(sortVal==='created_asc')return da-db; if(sortVal==='title_asc')return (a.title||'').localeCompare(b.title||''); return 0;});
      renderList(allAssignments);
      if(json.data.batch)window.currentBatchContext=json.data.batch;
    }catch(e){console.error('Load assignments error:',e);if($items)$items.innerHTML='<div class="as-empty">Unable to load assignments — please refresh.</div>';showItems(true);showErr('Failed to load assignments: '+(e.message||'Unknown error'));}
    finally{showLoader(false);}
  }
  if($refresh)$refresh.addEventListener('click',loadAssignments);
  if($search)$search.addEventListener('keyup',e=>{if(e.key==='Enter')loadAssignments();});
  if($sort)$sort.addEventListener('change',loadAssignments);

  const modalEl=document.getElementById('createAssignmentModal');let modalInstance=null;const bootstrapAvailable=!!(window.bootstrap&&typeof window.bootstrap.Modal==='function');
  if(modalEl&&bootstrapAvailable){try{modalInstance=bootstrap.Modal.getInstance(modalEl)||new bootstrap.Modal(modalEl,{backdrop:'static',keyboard:false});}catch(e){console.warn('bootstrap modal init failed',e);modalInstance=null;}}

  function showModalSafe(){if(modalInstance){try{modalInstance.show();return;}catch(e){console.warn('modalInstance.show failed',e);}}if(modalEl){modalEl.classList.add('show');modalEl.style.display='block';modalEl.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');if(!document.getElementById('createAssignmentBackdrop')){const bd=document.createElement('div');bd.id='createAssignmentBackdrop';bd.className='modal-backdrop fade show';document.body.appendChild(bd);}}}
  function hideModalSafe(){if(modalInstance){try{modalInstance.hide();return;}catch(e){console.warn('modalInstance.hide failed',e);}}if(modalEl){modalEl.classList.remove('show');modalEl.style.display='none';modalEl.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');const bd=document.getElementById('createAssignmentBackdrop');if(bd)bd.remove();}}

  function sanitizeHtml(html){
    const ALLOWED_TAGS=['p','br','strong','b','em','i','u','ul','ol','li','a','h2','h3'];const ALLOWED_ATTR=['href','target','rel','alt'];
    if(window.DOMPurify&&typeof DOMPurify.sanitize==='function')return DOMPurify.sanitize(html,{ALLOWED_TAGS,ALLOWED_ATTR});
    try{
      const parser=new DOMParser();const doc=parser.parseFromString(html,'text/html');
      function cleanNode(node){if(node.nodeType===Node.TEXT_NODE)return document.createTextNode(node.textContent||'');if(node.nodeType!==Node.ELEMENT_NODE)return null;const tag=node.tagName.toLowerCase();if(!ALLOWED_TAGS.includes(tag)){const frag=document.createDocumentFragment();node.childNodes.forEach(ch=>{const c=cleanNode(ch);if(c)frag.appendChild(c);});return frag;}const el=document.createElement(tag);Array.from(node.attributes||[]).forEach(attr=>{const name=attr.name.toLowerCase();const val=attr.value||'';if(ALLOWED_ATTR.includes(name)){if(name==='href'){if(/^https?:\/\//i.test(val))el.setAttribute('href',val);}else el.setAttribute(name,val);}});node.childNodes.forEach(ch=>{const c=cleanNode(ch);if(c)el.appendChild(c);});return el;}
      const body=doc.body;const out=document.createDocumentFragment();body.childNodes.forEach(n=>{const c=cleanNode(n);if(c)out.appendChild(c);});const container=document.createElement('div');container.appendChild(out);return container.innerHTML;
    }catch(e){return escapeHtml(html);}
  }
  function enforceSafeLinksOnFragment(html){try{const parser=new DOMParser();const doc=parser.parseFromString(html,'text/html');doc.querySelectorAll('a').forEach(a=>{const href=a.getAttribute('href')||'';if(/^https?:\/\//i.test(href)){a.setAttribute('target','_blank');a.setAttribute('rel','noopener noreferrer');}else a.removeAttribute('href');});return doc.body.innerHTML;}catch(e){return html;}}

  function wireRTE_Modal(rootId,linkBtnId){
    const el=document.getElementById(rootId);if(!el)return;el.setAttribute('role','textbox');el.setAttribute('aria-multiline','true');el.setAttribute('spellcheck','true');
    const parent=el.closest('.modal-body')||document;
    parent.querySelectorAll('.tool[data-cmd]').forEach(b=>{b.addEventListener('click',()=>{document.execCommand(b.dataset.cmd,false,null);el.focus();togglePlaceholder(el);});});
    parent.querySelector('#'+linkBtnId)?.addEventListener('click',()=>{
      const u=prompt('Enter URL (https://...)');
      if(u&&/^https?:\/\//i.test(u)){document.execCommand('createLink',false,u);setTimeout(()=>{try{el.querySelectorAll('a').forEach(a=>{const href=a.getAttribute('href')||'';if(/^https?:\/\//i.test(href)){a.setAttribute('target','_blank');a.setAttribute('rel','noopener noreferrer');}else a.removeAttribute('href');});}catch(e){}},30);}else if(u){alert('Please enter a valid http(s) URL.');}el.focus();togglePlaceholder(el);
    });
    function togglePlaceholder(editor){try{const ph=editor.parentElement&&editor.parentElement.querySelector('.rte-ph');if(!ph)return;const has=(editor.textContent||'').trim().length>0||(editor.innerHTML||'').trim().length>0;editor.classList.toggle('has-content',has);ph.style.display=has?'none':'';}catch(e){}}
    ['input','keyup','blur','focus'].forEach(ev=>el.addEventListener(ev,()=>togglePlaceholder(el)));togglePlaceholder(el);
    el.addEventListener('paste',function(e){e.preventDefault();const text=(e.clipboardData||window.clipboardData).getData('text/plain')||'';if(!text)return;const paragraphs=text.split(/\n{2,}/).map(p=>p.trim()).filter(Boolean);const html=paragraphs.length?paragraphs.map(p=>`<p>${escapeHtml(p).replace(/\n/g,'<br>')}</p>`).join(''):`<p>${escapeHtml(text)}</p>`;const cleaned=sanitizeHtml(html);const final=enforceSafeLinksOnFragment(cleaned);document.execCommand('insertHTML',false,final);setTimeout(()=>togglePlaceholder(el),10);});
  }
//submit assignments
(function(){
  const modalEl = document.getElementById('submitAssignmentModal');
  if(!modalEl) return;
  const form = document.getElementById('submitAssignmentForm');
  const titleEl = document.getElementById('submit_assign_title');
  const metaEl = document.getElementById('submit_assign_meta');
  const instEl = document.getElementById('submit_instructions');
  const dropzone = document.getElementById('submit_dropzone');
  const fileInput = document.getElementById('submit_attachments');
  const fileList = document.getElementById('submit_fileList');
  const clearBtn = document.getElementById('submit_btnClearFiles');
  const alertEl = document.getElementById('submitAssignAlert');
  const assignmentKeyInput = document.getElementById('submit_assignment_key');
  const submitBtn = document.getElementById('submitAssignSend');

  // New elements
  const noteEl = document.getElementById('submit_note');
  const attemptsEl = document.getElementById('submit_attempts');
  const existingSubmissionsEl = document.getElementById('submit_existing_submissions'); // container for both student and admin views

  // Optional close/cancel selectors
  const cancelBtn = document.getElementById('submitAssignCancel');
  const closeBtn  = modalEl.querySelector('.btn-close, #submitAssignClose');
  const anyDismiss = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .modal-close');
  // module-level helpers
  let _openModalController = null;
  const _assignmentInfoCache = new Map(); // key -> info
  const STUDENT_PREVIEW_COUNT = 5;

  let bsModal = null;
  if(window.bootstrap && typeof bootstrap.Modal === 'function') bsModal = bootstrap.Modal.getOrCreateInstance(modalEl,{backdrop:'static'});
  
  // Role detection (from local/session storage) - used to branch UI
  const _rawRole = (sessionStorage.getItem('role')||localStorage.getItem('role')||'').toLowerCase();
  const role = String(_rawRole || '').toLowerCase();
  const isAdmin = role.includes('admin') || role.includes('super_admin') || role.includes('superadmin');
  const isInstructor = role.includes('instructor');
  const isPrivileged = isAdmin || isInstructor; // admin / instructor

  function fmtSize(b){ if(!b) return ''; const u=['B','KB','MB','GB']; let i=0, n=Number(b); while(n>=1024 && i<u.length-1){ n/=1024; i++; } return `${n.toFixed(n<10&&i>0?1:0)} ${u[i]}`; }
  function escapeHtml(s){ return String(s).replace(/[&<>\"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":"&#39;" }[c])); }

  // Fetch assignment info for allowed types and attempts
  async function fetchAssignmentInfo(assignmentKey) {
    try {
      const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
      if(!assignmentKey) return null;
      // endpoint: /api/assignments/{assignmentKey}/submit-info
      const response = await fetch(`/api/assignments/${encodeURIComponent(assignmentKey)}/submit-info`, {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
      });
      if (!response.ok) {
        console.warn('fetchAssignmentInfo non-ok status', response.status);
        return null;
      }
      const data = await response.json();
      return data.data || null;
    } catch (error) {
      console.error('Error fetching assignment info:', error);
      return null;
    }
  }

  // Fetch user's existing submissions for this assignment (student view)
  async function fetchExistingSubmissions(assignmentKey) {
    try {
      const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
      const url = assignmentKey ? `/api/assignments/my-submissions/${encodeURIComponent(assignmentKey)}` : `/api/assignments/my-submissions`;
      const response = await fetch(url, {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
      });
      if (!response.ok) {
        console.warn('fetchExistingSubmissions non-ok status', response.status);
        return [];
      }
      const data = await response.json();
      return (data && data.data && data.data.items) ? data.data.items : [];
    } catch (error) {
      console.error('Error fetching existing submissions:', error);
      return [];
    }
  }

  // Fetch all submissions for assignment (instructor/admin view) - tolerant parser
  async function fetchAssignmentSubmissionsForAdmin(assignmentKey) {
    try {
      console.debug('[admin] fetchAssignmentSubmissionsForAdmin key=', assignmentKey);
      const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
      if(!assignmentKey) return [];
      const url = `/api/assignments/${encodeURIComponent(assignmentKey)}/submissions`;
      const response = await fetch(url, {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
      });
      const text = await response.text();
      let data;
      try { data = text ? JSON.parse(text) : {}; } catch(e){
        console.warn('[admin] failed parse submissions response, raw:', text);
        data = {};
      }
      console.debug('[admin] submissions raw response:', data);

      // tolerant extraction (common variants)
      if (data && data.data && data.data.submissions) return data.data.submissions;
      if (data && data.submissions) return data.submissions;
      if (data && data.data && data.data.items) return data.data.items;
      if (Array.isArray(data)) return data;
      return [];
    } catch (error) {
      console.error('Error fetching assignment submissions (admin):', error);
      return [];
    }
  }

  // ----- Normalized fetch for /student-status -----
  async function fetchStudentSubmissionStatus(assignmentKey) {
    try {
      console.debug('[admin] fetchStudentSubmissionStatus key=', assignmentKey);
      const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
      if (!assignmentKey) return { assignment: null, statistics: null, students: [], submitted: [], not_submitted: [] };

      const url = `/api/assignments/${encodeURIComponent(assignmentKey)}/student-status`;
      const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }});
      const text = await res.text();
      let json;
      try { json = text ? JSON.parse(text) : {}; } catch(e){
        console.warn('[admin] failed parse student-status raw:', text);
        json = {};
      }
      console.debug('[admin] student-status raw response:', json);

      // payload usually lives at json.data
      const payload = (json && json.data) ? json.data : (json || {});

      // possible places where students might be returned
      let students = Array.isArray(payload.students) ? payload.students.slice() : null;
      if (!students && Array.isArray(payload)) students = payload.slice(); // defensive
      if (!students) {
        // try some other possible keys
        students = Array.isArray(payload.items) ? payload.items.slice() : [];
      }

      // normalize each student object to include canonical keys used by the UI:
      students = students.map(s => {
        s = Object.assign({}, s);
        s.student_id = s.student_id ?? s.id ?? s.studentId ?? s.user_id ?? null;
        s.student_name = s.student_name ?? s.name ?? s.full_name ?? '';
        s.student_email = s.student_email ?? s.email ?? '';
        s.submission_count = (typeof s.submission_count === 'number') ? s.submission_count
                           : (typeof s.submissionCount === 'number') ? s.submissionCount
                           : (Array.isArray(s.all_attempts) ? s.all_attempts.length
                               : (Array.isArray(s.allAttempts) ? s.allAttempts.length : 0));
        const latestAttempt = s.latest_attempt ?? s.latestAttempt ?? s.latestAttemptNo ?? s.latestAttemptNumber ?? null;
        const fallbackAttempt = (s.all_attempts && s.all_attempts.length) ? (s.all_attempts[0].attempt_no ?? s.all_attempts[0].attemptNo ?? null) : null;
        s.attempt_no = s.attempt_no ?? s.attemptNo ?? latestAttempt ?? fallbackAttempt ?? (s.submission_count ? s.submission_count : null);
        s.submitted_at = s.submitted_at ?? s.latest_submission_date ?? s.latestSubmissionDate ?? s.created_at ?? s.submittedAt ?? null;
        s.status = s.status ?? (s.has_submitted ? 'submitted' : (s.submission_status ?? 'not_submitted'));
        s.all_attempts = Array.isArray(s.all_attempts) ? s.all_attempts
                         : Array.isArray(s.allAttempts) ? s.allAttempts
                         : (Array.isArray(s.attempts) ? s.attempts : []);
        if (Array.isArray(s.all_attempts)) {
          try {
            s.documents_count = s.all_attempts.reduce((acc, a) => {
              if (!a) return acc;
              if (Array.isArray(a.attachments)) return acc + a.attachments.length;
              if (Array.isArray(a.files)) return acc + a.files.length;
              return acc;
            }, 0);
          } catch (err) { s.documents_count = null; }
        } else {
          s.documents_count = null;
        }
        s.has_submitted = !!(s.has_submitted || s.submission_count > 0 || String(s.status).toLowerCase() === 'submitted');
        return s;
      });

      const submitted = students.filter(s => s.has_submitted);
      const not_submitted = students.filter(s => !s.has_submitted);

      const statistics = payload.statistics ?? {
        submitted: submitted.length,
        not_submitted: not_submitted.length,
        late_submissions: students.filter(s => !!s.is_late).length,
        submission_rate: students.length ? Math.round((submitted.length / students.length) * 100) : 0
      };

      return {
        assignment: payload.assignment ?? null,
        statistics,
        students,
        submitted,
        not_submitted
      };
    } catch (err) {
      console.error('Error fetching student status:', err);
      return { assignment: null, statistics: null, students: [], submitted: [], not_submitted: [] };
    }
  }

  // Soft delete a submission (student can delete own; admin endpoints tried)
  async function softDeleteSubmission(submissionId) {
    try {
      const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
      if (!submissionId) return false;
      const candidates = [
        `/api/assignments/submission/key/${encodeURIComponent(submissionId)}`,
        `/api/assignments/submission/${encodeURIComponent(submissionId)}`,
        `/api/submissions/${encodeURIComponent(submissionId)}`,
      ];
      for (const url of candidates) {
        try {
          const resp = await fetch(url, {
            method: 'DELETE',
            headers: {
              'Authorization': 'Bearer ' + token,
              'Accept': 'application/json'
            }
          });
          const text = await resp.text();
          if (resp.ok) return true;
          if (resp.status === 404) { console.warn('Soft-delete 404 for', url, 'body:', text); continue; }
          if (resp.status === 401 || resp.status === 403) { console.warn('Permission/unauth when deleting:', resp.status, url, text); return false; }
          console.warn('Soft-delete failed', resp.status, url, text);
        } catch (errInner) {
          console.warn('Network/error trying', url, errInner);
        }
      }
      return false;
    } catch (err) {
      console.error('Error deleting submission:', err);
      return false;
    }
  }
function setFileInputVisibility(show) {
  try {
    const disp = show ? '' : 'none';
    if (dropzone) dropzone.style.display = disp;
    if (fileInput) fileInput.style.display = disp;
    if (fileList) fileList.style.display = disp;
    if (clearBtn) clearBtn.style.display = disp;

    // selectors to cover common markup variants
    const labelSelectors = [
      'label[for="submit_attachments"]',
      '#submit_attachments_label',
      '.submit-attachments-label',
      '.form-label' // fallback - will be filtered by text
    ];

    document.querySelectorAll(labelSelectors.join(',')).forEach(el => {
      try {
        const text = (el.textContent || '').trim().toLowerCase();
        // only target elements that *look like* the attachments label to avoid hiding unrelated labels
        const looksLikeAttachments = text.includes('attach') || text.includes('attachment') || text.includes('attachments');
        if (!show) {
          if (looksLikeAttachments) {
            // remember old display so we can restore later
            el.dataset._wasDisplay = el.style.display || '';
            el.style.display = 'none';
          }
        } else {
          // restore previously saved display state if any
          if (el.dataset && Object.prototype.hasOwnProperty.call(el.dataset, '_wasDisplay')) {
            el.style.display = el.dataset._wasDisplay || '';
            delete el.dataset._wasDisplay;
          }
        }
      } catch (inner) { /* ignore per-element failures */ }
    });
  } catch (e) {
    console.warn('setFileInputVisibility error', e);
  }
}

  // Render assignment info (allowed types + attempts)
// Replace your existing renderAssignmentInfo with this function
function renderAssignmentInfo(info) {
  // defensive defaults
  const noteDefault = 'Unable to load submission info';
  const attemptsDefault = 'Unable to load attempts info';

  if (!info) {
    if (noteEl) {
      const c = noteEl.querySelector('#submit_note_content');
      if (c) c.textContent = noteDefault;
    }
    if (attemptsEl) {
      const c = attemptsEl.querySelector('#submit_attempts_content');
      if (c) c.textContent = attemptsDefault;
      attemptsEl.className = 'alert alert-info mb-3';
    }
    submitBtn.disabled = false;
    setFileInputVisibility(true);
    return;
  }

  // Note content (allowed types)
  const noteContent = info.allowed_display ||
    (info.allowed_submission_types && info.allowed_submission_types.length > 0
      ? info.allowed_submission_types.join(', ')
      : 'No restrictions');

  if (noteEl) {
    const c = noteEl.querySelector('#submit_note_content');
    if (c) c.textContent = `Allowed submission types: ${noteContent}`;
  }

  // ---------- Different presentation for students vs admin/instructor ----------
  if (isPrivileged) {
    // Admin / Instructor view: show total attempts allowed and attempts taken (if available)
    let attemptsText = '';
    if (info.attempts_allowed !== null && info.attempts_allowed !== undefined) {
      attemptsText = `Total attempts allowed: ${info.attempts_allowed}`;
      // also show attempts taken when available
      if (info.attempts_taken !== null && info.attempts_taken !== undefined) {
        // attemptsText += ` • ${info.attempts_taken} attempts used`;
      }
    } else {
      // no limit
      attemptsText = 'Total attempts allowed: Unlimited';
      if (info.attempts_taken !== null && info.attempts_taken !== undefined) {
        attemptsText += ` • ${info.attempts_taken} attempts used`;
      }
    }

    if (attemptsEl) {
      const c = attemptsEl.querySelector('#submit_attempts_content');
      if (c) c.textContent = attemptsText;
      // admin view should look informational
      attemptsEl.className = 'alert alert-info mb-3';
    }

    // Admins shouldn't be blocked by attempt limits in this UI; enable submit UI but hide file inputs if configured elsewhere
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit';
    // note: do not change file input visibility here; callers already toggle it based on role
    return;
  }

  // ---------- Student view (default) ----------
  // Determine attempts remaining if available
  let attemptsText = '';
  if (info.attempts_allowed !== null && info.attempts_allowed !== undefined) {
    if (info.attempts_left !== null && info.attempts_left !== undefined) {
      attemptsText = `${info.attempts_left} attempts remaining.`;
    } else if (info.attempts_taken !== null && info.attempts_taken !== undefined) {
      // attempts_allowed present but attempts_left missing — compute if possible
      const left = typeof info.attempts_allowed === 'number' && typeof info.attempts_taken === 'number'
        ? Math.max(0, info.attempts_allowed - info.attempts_taken)
        : null;
      attemptsText = (left === null) ? 'Attempts information available.' : `${left} attempts remaining.`;
    } else {
      attemptsText = 'Attempts information available.';
    }
  } else {
    // unlimited attempts
    if (info.attempts_taken !== null && info.attempts_taken !== undefined) {
      attemptsText = `${info.attempts_taken} submissions made. Unlimited attempts allowed.`;
    } else {
      attemptsText = 'Unlimited attempts allowed.';
    }
  }

  if (attemptsEl) {
    const c = attemptsEl.querySelector('#submit_attempts_content');
    if (c) c.textContent = attemptsText;
    // highlight when only a few attempts are left
    if (info.attempts_left !== null && info.attempts_left !== undefined && info.attempts_left === 0) {
      attemptsEl.className = 'alert alert-danger mb-3';
    } else if (info.attempts_left !== null && info.attempts_left !== undefined && info.attempts_left < 3) {
      attemptsEl.className = 'alert alert-warning mb-3';
    } else {
      attemptsEl.className = 'alert alert-info mb-3';
    }
  }

  // enforce UI behavior for zero attempts left for students
  if (info.attempts_left === 0) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-ban me-1"></i> No Attempts Left';
    setFileInputVisibility(false);
  } else {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit';
    setFileInputVisibility(true);
  }
}

  // Render existing submissions for student view (unchanged)
  function renderExistingSubmissions(submissions) {
    const container = document.getElementById('submit_existing_list');
    if (!container) return;
    if (!submissions || submissions.length === 0) {
      container.innerHTML = '<div class="text-muted">No previous submissions found.</div>';
      return;
    }
    container.innerHTML = submissions.map(submission => `
      <div class="card mb-2 existing-submission" data-id="${submission.id}">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center mb-2">
                <div style="display:flex;gap:8px;align-items:center"><strong class="me-2">Attempt ${submission.attempt_no || submission.attemptNo || '?'}</strong>
                <span class="badge bg-${getStatusBadgeColor(submission.status || 'submitted')}">${submission.status || 'submitted'}</span></div>
                <small class="text-muted ms-2">${submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : (submission.created_at ? new Date(submission.created_at).toLocaleString() : '')}</small>
              </div>
              ${renderSubmissionAttachments(submission.attachments)}
              ${submission.content_text ? `<div class="mt-1"><small class="text-muted">Text: ${escapeHtml((submission.content_text+'').substring(0, 100))}${(submission.content_text||'').length > 100 ? '...' : ''}</small></div>` : ''}
              ${submission.link_url ? `<div class="mt-1"><small><a href="${submission.link_url}" target="_blank">${escapeHtml(submission.link_url)}</a></small></div>` : ''}
            </div>
            <div class="ms-3">
              <button class="btn btn-sm btn-outline-danger delete-submission" data-id="${submission.id}" title="Remove Submission">&times;</button>
            </div>
          </div>
        </div>
      </div>
    `).join('');

    // Attach delete handlers
    container.querySelectorAll('.delete-submission').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const submissionId = btn.getAttribute('data-id');
        if (!submissionId) return;
        if (confirm('Are you sure you want to delete this submission? This action can be undone.')) {
          btn.disabled = true;
          const success = await softDeleteSubmission(submissionId);
          btn.disabled = false;
          if (success) {
            const node = btn.closest('.existing-submission');
            if (node) node.remove();
            if (typeof showOk === 'function') showOk('Submission deleted successfully');
            const assignmentKey = assignmentKeyInput.value;
            if (assignmentKey) {
              const info = await fetchAssignmentInfo(assignmentKey);
              renderAssignmentInfo(info);
            }
          } else {
            if (typeof showErr === 'function') showErr('Failed to delete submission');
            else { if(alertEl){ alertEl.innerHTML = 'Failed to delete submission'; alertEl.style.display = 'block'; } }
          }
        }
      });
    });
  }

  // Export helper: convert array of objects to CSV and download
  function downloadCSV(filename, rows, fields) {
    const header = fields.map(f => `"${(f.label||f.key).replace(/"/g,'""')}"`).join(',');
    const lines = rows.map(r => fields.map(f => {
      const v = (r[f.key] == null) ? '' : String(r[f.key]);
      return `"${v.replace(/"/g,'""')}"`;
    }).join(','));
    const csv = [header].concat(lines).join('\r\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    try { URL.revokeObjectURL(url); } catch(e){}
  }
  // small helper that returns the action menu HTML (three dots dropdown)
function adminActionMenuHtml() {
  return `
    <div class="admin-action-host" style="margin-left:8px;">
      <div class="dropdown admin-action-dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
          <i class="fa fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item admin-action-view" href="#" data-action="view">View Submissions</a></li>
          <li><a class="dropdown-item admin-action-grade" href="#" data-action="grade">Give marks</a></li>
        </ul>
      </div>
    </div>
  `;
}

  // NEW: Render submitted / not-submitted tabs for admin/instructor (NO full listing)
  async function renderAdminTabsWithStatus(assignmentKey, allSubmissions) {
  if (!existingSubmissionsEl) return;
  const tabsHost = document.getElementById('admin_tabs_wrapper') || existingSubmissionsEl;
  tabsHost.innerHTML = `
    <div class="admin-submissions-tabs mt-3">
      <ul class="nav nav-tabs mb-2" role="tablist" style="gap:8px; justify-content:center;">
        <li class="nav-item"><button class="nav-link active" data-tab="submitted" type="button">Submitted</button></li>
        <li class="nav-item"><button class="nav-link" data-tab="not_submitted" type="button">Not Submitted</button></li>
      </ul>
      <div class="tab-content" style="min-height:120px;">
        <div class="tab-pane active" data-pane="submitted">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div><strong id="admin_submitted_count">Loading...</strong></div>
            <div><button type="button" id="export_submitted_csv" class="btn btn-sm btn-outline-primary"><i class="fa fa-download me-1"></i> Export CSV</button></div>
          </div>
          <div id="admin_submitted_list"></div>
        </div>
        <div class="tab-pane" data-pane="not_submitted" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div><strong id="admin_not_submitted_count">Loading...</strong></div>
            <div><button type="button" id="export_not_submitted_csv" class="btn btn-sm btn-outline-primary"><i class="fa fa-download me-1"></i> Export CSV</button></div>
          </div>
          <div id="admin_not_submitted_list"></div>
        </div>
      </div>
    </div>
  `;

  // wire tabs
  const tabs = tabsHost.querySelectorAll('.nav-link[data-tab]');
  tabs.forEach(btn => {
    btn.addEventListener('click', (ev) => {
      tabs.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const tab = btn.dataset.tab;
      const panes = tabsHost.querySelectorAll('.tab-pane');
      panes.forEach(p => {
        if (p.dataset.pane === tab) { p.style.display = ''; p.classList.add('active'); }
        else { p.style.display = 'none'; p.classList.remove('active'); }
      });
    });
  });

  // --- Helper: action menu HTML ---
  function adminActionMenuHtml() {
    return `
      <div class="admin-action-host" style="margin-left:8px;">
        <div class="dropdown admin-action-dropdown">
          <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item admin-action-view" href="#" data-action="view">View Submissions</a></li>
            <li><a class="dropdown-item admin-action-grade" href="#" data-action="grade">Give marks</a></li>
          </ul>
        </div>
      </div>
    `;
  }

  // --- Helper: fetch uuids from server for an assignment+student, return {assignment_uuid, student_uuid} or null ---
  async function fetchAssignmentAndStudentUuids(assignmentKeyOrUuidOrId, studentKeyOrId) {
    try {
      const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
      const a = encodeURIComponent(String(assignmentKeyOrUuidOrId));
      const s = encodeURIComponent(String(studentKeyOrId));
      // this is the API you said exists and returns assignment_uuid & student_uuid
      const url = `/api/assignments/${a}/students/${s}/documents`;
      const resp = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          ...(token ? { 'Authorization': 'Bearer ' + token } : {})
        }
      });
      if (!resp.ok) return null;
      const j = await resp.json().catch(()=>null);
      if (!j || !j.data) return null;
      return {
        assignment_uuid: j.data.assignment_uuid || (j.data.assignment && j.data.assignment.uuid) || null,
        student_uuid: j.data.student_uuid || (j.data.student && j.data.student.uuid) || null
      };
    } catch (e) {
      console.warn('fetchAssignmentAndStudentUuids error', e);
      return null;
    }
  }

  // --- Helper: attempt to open student's submissions page in new tab (tries to fetch uuids first) ---
  async function openStudentSubmissionsPage(assignmentKey, student) {
    // Try to use any uuid already present on the student object first
    const candidateAssignmentUuid =
      student.assignment_uuid ||
      student.assignmentUuid ||
      student.assignmentId ||
      null;

    const candidateStudentUuid =
      student.student_uuid ||
      student.uuid ||
      student.user_uuid ||
      student.userUuid ||
      null;

    // If both UUIDs are present locally, open canonical route immediately
    if (candidateAssignmentUuid && candidateStudentUuid) {
      const uiUrl = `/assignments/${encodeURIComponent(candidateAssignmentUuid)}/students/${encodeURIComponent(candidateStudentUuid)}/documents`;
      window.open(uiUrl, '_blank');
      return uiUrl;
    }

    // Otherwise, call API to fetch uuids
    const sidKey = student.student_id || student.id || student.student_email || student.email || '';
    const lookup = await fetchAssignmentAndStudentUuids(assignmentKey, sidKey);
    if (lookup && lookup.assignment_uuid && lookup.student_uuid) {
      const uiUrl = `/assignments/${encodeURIComponent(lookup.assignment_uuid)}/students/${encodeURIComponent(lookup.student_uuid)}/documents`;
      window.open(uiUrl, '_blank');
      return uiUrl;
    }

    // Fallback: try to open best-effort (may be numeric ids)
    const a = encodeURIComponent(String(candidateAssignmentUuid || assignmentKey || ''));
    const s = encodeURIComponent(String(candidateStudentUuid || sidKey || ''));
    const fallbackUrl = `/assignments/${a}/students/${s}/documents`;
    window.open(fallbackUrl, '_blank');
    return fallbackUrl;
  }

  // --- Helper: grade modal (renders DOM modal, fetches submissions, posts grade) ---
  async function openGradeModal(assignmentKey, student, allSubmissionsMap) {
    // remove existing modal if any
    const prev = document.getElementById('adminGradeModal');
    if (prev) prev.remove();

    const modalHtml = `
      <div class="modal fade" id="adminGradeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Grade: ${escapeHtml(student.student_name || student.name || ('#' + (student.student_id||student.id||'')))}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div id="grade_alert" style="display:none" class="alert alert-danger"></div>
              <div class="mb-2"><small class="text-muted">Fetching submissions...</small></div>
              <div id="grade_submissions_list" style="max-height:320px;overflow:auto;"></div>
              <hr/>
              <form id="gradeForm">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label">Marks</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="marks" required />
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Grade letter (optional)</label>
                    <input type="text" class="form-control" name="grade_letter" />
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Apply late penalty</label>
                    <select class="form-select" name="apply_late_penalty">
                      <option value="true" selected>Yes (default)</option>
                      <option value="false">No</option>
                    </select>
                  </div>
                </div>
                <div class="mt-2">
                  <label class="form-label">Grader note / Feedback (optional)</label>
                  <textarea class="form-control" name="grader_note" rows="3"></textarea>
                </div>
                <input type="hidden" name="submission_id" value="" />
                <input type="hidden" name="submission_uuid" value="" />
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button id="gradeSubmitBtn" type="button" class="btn btn-primary"><i class="fa fa-check me-1"></i> Save marks</button>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalEl = document.getElementById('adminGradeModal');
    const bsModal = (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    // Try to get submissions: prefer local allSubmissionsMap, fallback to API
    let submissions = [];
    try {
      const sidKey = String(student.student_id || student.id || '');
      if (allSubmissionsMap && allSubmissionsMap[sidKey]) {
        submissions = allSubmissionsMap[sidKey];
      } else {
        const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';

        // Attempt to get UUIDs first so we can call the UUID-based API
        let assignmentUuid = null;
        let studentUuid = null;
        if (student.student_uuid || student.uuid) {
          studentUuid = student.student_uuid || student.uuid;
        }

        // Try to fetch uuids via the documents API if not present locally
        if (!studentUuid || !assignmentUuid) {
          const lookup = await fetchAssignmentAndStudentUuids(assignmentKey, sidKey);
          if (lookup) {
            assignmentUuid = lookup.assignment_uuid || null;
            studentUuid = lookup.student_uuid || studentUuid || null;
          }
        }

        // Prefer UUID API route if we have them
        const urlCandidates = [];
        if (assignmentUuid && studentUuid) {
          urlCandidates.push(`/api/assignments/${encodeURIComponent(assignmentUuid)}/students/${encodeURIComponent(studentUuid)}/documents`);
          urlCandidates.push(`/api/assignments/${encodeURIComponent(assignmentUuid)}/students/${encodeURIComponent(studentUuid)}/documents`); // duplicate safe
        }
        // fallback numeric/legacy routes
        urlCandidates.push(`/api/assignments/${encodeURIComponent(assignmentKey)}/students/${encodeURIComponent(sidKey)}/documents`);
        urlCandidates.push(`/api/submissions/student/${encodeURIComponent(sidKey)}?assignment=${encodeURIComponent(assignmentKey)}`);
        urlCandidates.push(`/api/assignments/${encodeURIComponent(assignmentKey)}/student/${encodeURIComponent(sidKey)}/documents`);

        for (const u of urlCandidates) {
          try {
            const resp = await fetch(u, { headers: { 'Authorization': token ? 'Bearer ' + token : '', 'Accept': 'application/json' }});
            if (!resp.ok) continue;
            const j = await resp.json().catch(()=>({}));
            submissions = (j && j.data && j.data.submissions) ? j.data.submissions : (j && j.submissions) ? j.submissions : [];
            // Normalize array of attempts if returned as object or nested -> keep as-is if empty
            if (Array.isArray(submissions) && submissions.length) {
              break;
            }
          } catch (e) { /* ignore & try next */ }
        }
      }
    } catch (err) {
      console.warn('Failed to fetch submissions for grading', err);
      submissions = [];
    }

    const listHost = modalEl.querySelector('#grade_submissions_list');
    if (!listHost) return;
    if (!submissions || submissions.length === 0) {
      listHost.innerHTML = '<div class="tiny text-muted">No submissions found for this student.</div>';
    } else {
      submissions.sort((a,b) => (b.attempt_no||b.attemptNo||0) - (a.attempt_no||a.attemptNo||0));
      const itemsHtml = submissions.map(s => {
        const sid = s.id || s.submission_id || s.submissionId || '';
        const subUuid = s.submission_uuid || s.uuid || s.submissionUuid || '';
        const attempt = s.attempt_no ?? s.attemptNo ?? s.attempt ?? '-';
        const when = s.submitted_at || s.submittedAt || s.created_at || s.createdAt || '';
        const totalMarks = s.total_marks ?? s.totalMarks ?? (s.metadata && s.metadata.grading_details && s.metadata.grading_details.final_marks_after_penalty) ?? '';
        const isLate = !!(s.is_late || s.isLate);
        return `<div class="p-2" style="border-bottom:1px solid rgba(0,0,0,0.04)">
          <div style="display:flex;justify-content:space-between">
            <div><strong>Attempt ${escapeHtml(String(attempt))}</strong> ${when ? `<small class="text-muted ms-2">${new Date(when).toLocaleString()}</small>` : ''}</div>
            <div><small class="tiny text-muted">${isLate? 'Late':''} ${totalMarks!==''? ' • Marks: ' + escapeHtml(String(totalMarks)) : ''}</small></div>
          </div>
          <div class="tiny text-muted mt-1">Files: ${(Array.isArray(s.attachments) ? s.attachments.length : (Array.isArray(s.all_attachments)?s.all_attachments.length:0))}</div>
          <div class="mt-1"><button class="btn btn-sm btn-outline-secondary select-submission" data-id="${escapeHtml(String(sid))}" data-uuid="${escapeHtml(String(subUuid))}">Select this attempt to grade</button></div>
        </div>`;
      }).join('');
      listHost.innerHTML = itemsHtml;

      // default select first
      const first = submissions[0];
      if (first) {
        const input = modalEl.querySelector('input[name="submission_id"]');
        const inputUuid = modalEl.querySelector('input[name="submission_uuid"]');
        if (input) input.value = first.id || first.submission_id || first.submissionId || '';
        if (inputUuid) inputUuid.value = first.submission_uuid || first.uuid || first.submissionUuid || '';
      }

      listHost.querySelectorAll('.select-submission').forEach(btn => {
        btn.addEventListener('click', (ev) => {
          ev.preventDefault();
          const sid = btn.getAttribute('data-id');
          const suuid = btn.getAttribute('data-uuid');
          const inpt = modalEl.querySelector('input[name="submission_id"]');
          const inptUuid = modalEl.querySelector('input[name="submission_uuid"]');
          if (inpt) inpt.value = sid;
          if (inptUuid) inptUuid.value = suuid || '';
          listHost.querySelectorAll('.select-submission').forEach(b => b.classList.remove('btn-primary'));
          btn.classList.add('btn-primary');
        });
      });
    }

    if (bsModal) bsModal.show();
    else { modalEl.style.display = 'block'; modalEl.classList.add('show'); document.body.classList.add('modal-open'); }

    // grade submit handler
    const gradeBtn = modalEl.querySelector('#gradeSubmitBtn');
    gradeBtn.addEventListener('click', async (ev) => {
      ev.preventDefault();
      gradeBtn.disabled = true;
      gradeBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Saving...';
      const alertBox = modalEl.querySelector('#grade_alert');

      try {
        const form = modalEl.querySelector('#gradeForm');
        const fd = new FormData(form);
        const submissionId = fd.get('submission_id');
        const submissionUuid = fd.get('submission_uuid');
        if (!submissionId && !submissionUuid) {
          if (alertBox) { alertBox.style.display='block'; alertBox.textContent = 'No submission selected to grade.'; }
          throw new Error('No submission chosen');
        }
        const payload = {
          marks: parseFloat(fd.get('marks')),
          grade_letter: fd.get('grade_letter') || null,
          grader_note: fd.get('grader_note') || null,
          feedback_html: null,
          feedback_visible: true,
          apply_late_penalty: fd.get('apply_late_penalty') !== 'false'
        };

        const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
        // Prefer submission-uuid-based endpoints if we have uuid, else try numeric id endpoints
        const gradeCandidates = [];
        if (submissionUuid) {
          gradeCandidates.push(`/api/submissions/${encodeURIComponent(submissionUuid)}/grade`);
          gradeCandidates.push(`/api/assignments/submissions/${encodeURIComponent(submissionUuid)}/grade`);
        }
        if (submissionId) {
          gradeCandidates.push(`/api/assignments/submissions/${encodeURIComponent(submissionId)}/grade`);
          gradeCandidates.push(`/api/submission/${encodeURIComponent(submissionId)}/grade`);
          gradeCandidates.push(`/api/assignments/${encodeURIComponent(assignmentKey)}/grade/${encodeURIComponent(submissionId)}`);
        }

        let gradeResp = null;
        for (const url of gradeCandidates) {
          try {
            const resp = await fetch(url, {
              method: 'POST',
              headers: {
                'Authorization': token ? 'Bearer ' + token : '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify(payload)
            });
            const j = await resp.json().catch(()=>({}));
            if (resp.ok) { gradeResp = { ok: true, data: j }; break; }
            // if unauthorized, break and surface error
            if (resp.status === 401 || resp.status === 403) { gradeResp = { ok: false, status: resp.status, json: j, url }; break; }
          } catch (err) { /* try next */ }
        }

        if (!gradeResp || gradeResp.ok === false) {
          const msg = (gradeResp && gradeResp.json && (gradeResp.json.error || gradeResp.json.message)) ? (gradeResp.json.error || gradeResp.json.message) : 'Failed to grade submission';
          if (alertBox) { alertBox.style.display='block'; alertBox.textContent = msg; }
          if (typeof showErr === 'function') showErr(msg);
          throw new Error(msg);
        }

        if (typeof showOk === 'function') showOk((gradeResp.data && (gradeResp.data.message || gradeResp.data.msg)) ? (gradeResp.data.message || gradeResp.data.msg) : 'Graded successfully');
        if (bsModal) bsModal.hide(); else { modalEl.classList.remove('show'); modalEl.style.display = 'none'; document.body.classList.remove('modal-open'); }
        // optionally refresh admin view; caller can re-run renderAdminTabsWithStatus if desired
      } catch (err) {
        console.error('Grade submit error', err);
      } finally {
        gradeBtn.disabled = false;
        gradeBtn.innerHTML = '<i class="fa fa-check me-1"></i> Save marks';
      }
    });

    // cleanup after hide
    modalEl.addEventListener('hidden.bs.modal', () => { modalEl.remove(); });
  }

  // --- attach handlers for action menus ---
  function attachAdminActionHandlers(containerEl, assignmentKey, allSubmissionsArray) {
    if (!containerEl) return;

    // build a map student_id -> [submissions]
    const allMap = {};
    if (Array.isArray(allSubmissionsArray)) {
      allSubmissionsArray.forEach(s => {
        const sid = String(s.student_id || s.id || s.studentId || s.user_id || '');
        if (!allMap[sid]) allMap[sid] = [];
        allMap[sid].push(s);
      });
    }

    // delegation: listen for clicks on dropdown items inside this container
    containerEl.addEventListener('click', (ev) => {
      const menuItem = ev.target.closest && ev.target.closest('.dropdown-item');
      if (!menuItem) return;
      ev.preventDefault();

      // find submission row
      const row = ev.target.closest('[data-student-id]') || ev.target.closest('.submission-row') || ev.target.closest('.d-flex');
      const studentIdAttr = row && row.getAttribute ? row.getAttribute('data-student-id') : null;

      // determine student object
      let student = null;
      if (studentIdAttr) {
        const nameEl = row.querySelector('.student-name');
        // Attempt to find any uuid stored in a data attribute (if you add it in markup)
        const studentUuidAttr = row.getAttribute('data-student-uuid') || null;
        student = { student_id: studentIdAttr, student_name: nameEl ? nameEl.textContent.trim() : null, student_uuid: studentUuidAttr };
      } else {
        // fallback: parse text
        const text = row ? (row.textContent || '') : '';
        const m = text.match(/#(\d{1,10})/);
        student = { student_id: m ? m[1] : null, student_name: (text.split('\n')[0] || '').trim() };
      }

      const action = menuItem.dataset && menuItem.dataset.action;
      if (!action) return;

      if (action === 'view') {
        if (!student || !student.student_id) { if (typeof showErr === 'function') showErr('Cannot determine student'); return; }
        // open page using UUIDs when possible (async)
        openStudentSubmissionsPage(assignmentKey, student);
      } else if (action === 'grade') {
        if (!student || !student.student_id) { if (typeof showErr === 'function') showErr('Cannot determine student'); return; }
        openGradeModal(assignmentKey, student, allMap);
      }
    }, { capture: true });
  }

  // fetch student status list
  const { submitted, not_submitted } = await fetchStudentSubmissionStatus(assignmentKey);
  console.debug('[admin] student status parsed:', { submitted, not_submitted, allSubmissions });

  // Normalize submitted list to objects
  let submittedNormalized = [];
  if (submitted.length && typeof submitted[0] === 'object') {
    submittedNormalized = submitted;
  } else if (submitted.length && allSubmissions && allSubmissions.length) {
    const mapByStudent = {};
    allSubmissions.forEach(s => {
      if (s.student_id) mapByStudent[String(s.student_id)] = s;
      if (s.student_email) mapByStudent[String(s.student_email).toLowerCase()] = s;
      if (s.studentId) mapByStudent[String(s.studentId)] = s;
    });
    submittedNormalized = submitted.map(id => {
      const key = String(id);
      return mapByStudent[key] || mapByStudent[key.toLowerCase()] || { student_id: id, student_name: id };
    });
  } else {
    submittedNormalized = submitted.map(s => (typeof s === 'object' ? s : { student_id: s, student_name: s }));
  }

  // Render Submitted list (with action menu & data-student-id)
  const submittedListEl = tabsHost.querySelector('#admin_submitted_list');
  const submittedCountEl = tabsHost.querySelector('#admin_submitted_count');
  if (submittedListEl) {
    if (!submittedNormalized || submittedNormalized.length === 0) {
      submittedListEl.innerHTML = '<div class="tiny text-muted">No submissions found.</div>';
      if (submittedCountEl) submittedCountEl.textContent = 'Submitted: 0';
    } else {
      const submittedRowsHtml = (submittedNormalized || []).map(s => {
        const sid = s.student_id ?? s.id ?? '';
        const name = escapeHtml(s.student_name || s.name || s.full_name || (s.student_email||'Student'));
        const email = escapeHtml(s.student_email || s.email || '');
        const attempt = s.attempt_no ?? s.attemptNo ?? s.attempt ?? '';
        const status = escapeHtml(s.status || s.submission_status || 'submitted');
        const when = s.submitted_at || s.created_at || '';
        // include student's uuid in data attribute when available to help openGradeModal/openStudentSubmissionsPage
        const dataUuid = s.student_uuid || s.uuid || s.user_uuid || '';
        return `
          <div class="d-flex align-items-center justify-content-between p-2 submission-row" data-student-id="${escapeHtml(String(sid))}" ${dataUuid ? `data-student-uuid="${escapeHtml(String(dataUuid))}"` : ''} style="border-bottom:1px solid rgba(0,0,0,0.04);">
            <div>
              <div class="student-name" style="font-weight:600">${name} ${email?`<small class="text-muted">(${email})</small>`:''}</div>
              <div class="tiny text-muted">Attempt: ${attempt || '-'} • ${status} • ${when?new Date(when).toLocaleString():'—'}</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <div style="min-width:80px"><small class="text-muted">#${escapeHtml(String(sid||''))}</small></div>
              ${adminActionMenuHtml()}
            </div>
          </div>
        `;
      }).join('');
      submittedListEl.innerHTML = submittedRowsHtml;
      if (submittedCountEl) submittedCountEl.textContent = `Submitted: ${submittedNormalized.length}`;

      // attach handlers immediately
      try { attachAdminActionHandlers(submittedListEl, assignmentKey, allSubmissions || []); } catch (e) { console.warn('attach handlers error', e); }
    }
  }

  // Render Not submitted list
  const notListEl = tabsHost.querySelector('#admin_not_submitted_list');
  const notCountEl = tabsHost.querySelector('#admin_not_submitted_count');
  if (notListEl) {
    if (!not_submitted || not_submitted.length === 0) {
      notListEl.innerHTML = '<div class="tiny text-muted">All students have submitted.</div>';
      if (notCountEl) notCountEl.textContent = 'Not submitted: 0';
    } else {
      notListEl.innerHTML = not_submitted.map(s => {
        const name = escapeHtml(s.student_name || s.name || (s.student_email||'Student'));
        const email = escapeHtml(s.student_email || s.email || '');
        const sid = s.student_id || s.id || '';
        return `<div class="d-flex align-items-center justify-content-between p-2" style="border-bottom:1px solid rgba(0,0,0,0.04);">
          <div>
            <div style="font-weight:600">${name} ${email?`<small class=\"text-muted\">(${email})</small>`:''}</div>
          </div>
          <div style="min-width:80px"><small class="text-muted">#${sid}</small></div>
        </div>`;
      }).join('');
      if (notCountEl) notCountEl.textContent = `Not submitted: ${not_submitted.length}`;
    }
  }

  // Exports (same as before)
  const exportSubmittedBtn = tabsHost.querySelector('#export_submitted_csv');
  const exportNotSubmittedBtn = tabsHost.querySelector('#export_not_submitted_csv');

  if (exportSubmittedBtn) {
    exportSubmittedBtn.addEventListener('click', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      const rows = (submittedNormalized || []).map(s => ({
        student_id: s.student_id ?? s.id ?? '',
        student_name: s.student_name ?? s.name ?? '',
        student_email: s.student_email ?? s.email ?? '',
        attempt_no: s.attempt_no ?? s.attemptNo ?? s.attempt ?? '',
        status: s.status ?? s.submission_status ?? '',
        submitted_at: s.submitted_at ?? s.created_at ?? ''
      }));
      downloadCSV(`submitted_${assignmentKey}.csv`, rows, [
        { key: 'student_id', label: 'Student ID' },
        { key: 'student_name', label: 'Name' },
        { key: 'student_email', label: 'Email' },
        { key: 'attempt_no', label: 'Attempt' },
        { key: 'status', label: 'Status' },
        { key: 'submitted_at', label: 'Submitted At' }
      ]);
    });
  }
  if (exportNotSubmittedBtn) {
    exportNotSubmittedBtn.addEventListener('click', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      const rows = (not_submitted || []).map(s => ({
        student_id: s.student_id ?? s.id ?? '',
        student_name: s.student_name ?? s.name ?? '',
        student_email: s.student_email ?? s.email ?? ''
      }));
      downloadCSV(`not_submitted_${assignmentKey}.csv`, rows, [
        { key: 'student_id', label: 'Student ID' },
        { key: 'student_name', label: 'Name' },
        { key: 'student_email', label: 'Email' }
      ]);
    });
  }
}

  // Helper to render submission attachments (for student existing submissions)
  function renderSubmissionAttachments(attachments) {
    if (!attachments || attachments.length === 0) return '<div class="tiny text-muted">No files attached.</div>';
    return (attachments || []).map(att => {
      const url = att.url || att.path || '#';
      const name = escapeHtml(att.name || att.original_name || 'file');
      const size = att.size ? ` <span class="tiny text-muted">(${fmtSize(att.size)})</span>` : '';
      return `<div style="margin-top:6px;"><a href="${url}" target="_blank">${name}</a>${size}</div>`;
    }).join('');
  }

  // Helper for status badge colors
  function getStatusBadgeColor(status) {
    const colors = {
      'submitted': 'primary',
      'graded': 'success',
      'draft': 'secondary',
      'late': 'warning',
      'rejected': 'danger'
    };
    return colors[status] || 'secondary';
  }

  function renderFiles(fileListObj){
    if(!fileList) return;
    fileList.innerHTML = '';
    if(!fileListObj || fileListObj.length === 0) {
      fileList.innerHTML = '<div class="tiny text-muted">No files selected.</div>';
      return;
    }
    Array.from(fileListObj).forEach((f, idx) => {
      const div = document.createElement('div');
      div.className = 'd-flex align-items-center justify-content-between p-2';
      div.style.border = '1px solid rgba(0,0,0,0.04)';
      div.style.borderRadius = '8px';
      div.style.marginBottom = '8px';
      div.innerHTML = `<div style="display:flex;gap:12px;align-items:center"><i class="fa fa-file" style="width:28px;"></i>
        <div><div style="font-weight:600">${escapeHtml(f.name)}</div><div class="tiny text-muted">${fmtSize(f.size)}</div></div></div>
        <div><button type="button" class="btn btn-sm btn-light remove-file" data-idx="${idx}">&times;</button></div>`;
      fileList.appendChild(div);
      div.querySelector('.remove-file').addEventListener('click', ()=> {
        const dt = new DataTransfer();
        Array.from(fileInput.files).forEach((file, i) => { if(i !== idx) dt.items.add(file); });
        fileInput.files = dt.files;
        renderFiles(fileInput.files);
      });
    });
  }

  function closeModal({reset = false} = {}) {
    if(bsModal) {
      try { bsModal.hide(); } catch(e){ }
    } else {
      modalEl.classList.remove('show');
      modalEl.style.display = 'none';
      document.body.classList.remove('modal-open');
    }

    if(reset){
      try { form.reset(); } catch(e) {}
      if(fileInput){ fileInput.value = ''; }
      renderFiles([]);
      if(alertEl){ alertEl.style.display = 'none'; alertEl.innerHTML = ''; }
      submitBtn.disabled = false;
      assignmentKeyInput.value = '';
      if (noteEl) { const c = noteEl.querySelector('#submit_note_content'); if(c) c.textContent = 'Loading submission info...'; }
      if (attemptsEl) { const c = attemptsEl.querySelector('#submit_attempts_content'); if(c) c.textContent = 'Loading attempts info...'; }
      if (existingSubmissionsEl) existingSubmissionsEl.innerHTML = '<div class="text-muted">Loading...</div>';
    }
  }

  // Updated openSubmitModal with new features and role branching
  window.openSubmitModal = async function(row){
    if(!row) return;
    const key = row.id || row.uuid || '';
    assignmentKeyInput.value = key;
    titleEl.textContent = row.title || 'Untitled';
    metaEl.textContent = row.module_title ? `${row.module_title} • ${row.created_at ? new Date(row.created_at).toLocaleString() : ''}` : (row.created_at ? new Date(row.created_at).toLocaleString() : '');

    const raw = (row.description || row.instruction || row.instructions || '') + '';
    if(raw.trim()){
      try { instEl.innerHTML = sanitizeHtml ? sanitizeHtml(raw) : raw; } catch(e){ instEl.textContent = raw; }
    } else {
      instEl.innerHTML = '<div class="tiny text-muted">No instructions available.</div>';
    }

    // reset file input UI and alerts by default
    if (fileInput) fileInput.value = '';
    renderFiles([]);
    if (alertEl) { alertEl.style.display='none'; alertEl.innerHTML=''; }
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit';

    // Show modal immediately (lazy-loading will handle data fetches in background)
    if(bsModal) bsModal.show(); else { modalEl.classList.add('show'); modalEl.style.display='block'; document.body.classList.add('modal-open'); }

    // Branch: student view (unchanged) vs admin/instructor view
    try {
      // start fetches but do not block UI — handle results asynchronously
      const infoPromise = fetchAssignmentInfo(key);
      const existingPromise = isPrivileged ? Promise.resolve([]) : fetchExistingSubmissions(key);

      Promise.all([infoPromise, existingPromise]).then(async ([assignmentInfo, existingSubmissions]) => {

        // Render assignment meta/attempts note (same for both)
        renderAssignmentInfo(assignmentInfo);

        if (!isPrivileged) {
          modalEl.classList.remove('privileged');

          // Student view: show file input and personal previous submissions
          setFileInputVisibility(true);
          renderExistingSubmissions(existingSubmissions);
        } else {
          // Admin / Instructor view:
          // Hide the choose file input field + personal previous submissions area
           modalEl.classList.add('privileged');
           document.querySelectorAll(
      'label[for="submit_attachments"], #submit_attachments_label, .submit-attachments-label, .form-label'
    ).forEach(el => {
      if (el.dataset && Object.prototype.hasOwnProperty.call(el.dataset, '_wasDisplay')) {
        el.style.display = el.dataset._wasDisplay || '';
        delete el.dataset._wasDisplay;
      }
    });
          setFileInputVisibility(false);

          // Show a loading placeholder
          if (existingSubmissionsEl) {
            existingSubmissionsEl.innerHTML = '<div class="tiny text-muted">Loading submissions...</div>';
          }

          // Fetch all submissions for this assignment from server (done lazily after modal shown)
          const allSubmissions = await fetchAssignmentSubmissionsForAdmin(key);

          // NOTE: full listing removed. Only render the Submitted / Not submitted tabs.
          await renderAdminTabsWithStatus(key, allSubmissions);
        }

      }).catch((error) => {
        // handle async loading errors (assignmentInfo / existingSubmissions)
        console.error('Error loading submission data (async):', error);
        if (noteEl) { const c = noteEl.querySelector('#submit_note_content'); if(c) c.textContent = 'Error loading submission info'; }
        if (attemptsEl) { const c = attemptsEl.querySelector('#submit_attempts_content'); if(c) c.textContent = 'Error loading attempts info'; }
        if (existingSubmissionsEl) existingSubmissionsEl.innerHTML = '<div class="text-muted">Error loading submissions</div>';
      });

    } catch (error) {
      // fallback synchronous error handling (should be rare since main work is done async)
      console.error('Error initiating submission data loads:', error);
      if (noteEl) { const c = noteEl.querySelector('#submit_note_content'); if(c) c.textContent = 'Error loading submission info'; }
      if (attemptsEl) { const c = attemptsEl.querySelector('#submit_attempts_content'); if(c) c.textContent = 'Error loading attempts info'; }
      if (existingSubmissionsEl) existingSubmissionsEl.innerHTML = '<div class="text-muted">Error loading submissions</div>';
    }

    // keep original final show (harmless if modal already shown)
    if(bsModal) bsModal.show(); else { modalEl.classList.add('show'); modalEl.style.display='block'; document.body.classList.add('modal-open'); }
  };

  // Event listeners for drag/drop & file input
  ['dragenter','dragover'].forEach(e => dropzone && dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.add('dragover'); }));
  ['dragleave','drop','dragend'].forEach(e => dropzone && dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.remove('dragover'); }));
  dropzone && dropzone.addEventListener('drop', ev => {
    ev.preventDefault();
    const dt = ev.dataTransfer;
    if(!dt||!dt.files) return;
    const dtNew = new DataTransfer();
    Array.from(fileInput.files||[]).forEach(f=>dtNew.items.add(f));
    Array.from(dt.files).forEach(f=>dtNew.items.add(f));
    fileInput.files = dtNew.files;
    renderFiles(fileInput.files);
  });
  dropzone && dropzone.addEventListener('click', ()=> fileInput && fileInput.click());
  fileInput && fileInput.addEventListener('change', ()=> renderFiles(fileInput.files));
  clearBtn && clearBtn.addEventListener('click', ()=> { if(fileInput) fileInput.value=''; renderFiles([]); });

  if(cancelBtn){
    cancelBtn.addEventListener('click', (ev)=>{ ev.preventDefault(); closeModal({reset:true}); });
  }
  if(closeBtn){
    closeBtn.addEventListener('click', (ev)=>{ ev.preventDefault(); closeModal({reset:true}); });
  }
  if(anyDismiss && anyDismiss.length){
    anyDismiss.forEach(el=>{ el.addEventListener('click', ()=> closeModal({reset:true})); });
  }

  if(window.bootstrap && modalEl){
    modalEl.addEventListener('hidden.bs.modal', ()=> {
      try { submitBtn.disabled = false; } catch(e){}
      if (alertEl) { alertEl.style.display = 'none'; alertEl.innerHTML = ''; }
      if(fileInput){ fileInput.value=''; renderFiles([]); }
    });
  }

  // Submit handler (keeps same behavior — admins/instructors may still submit if desired)
  let _submitting = false;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (_submitting) {
      console.debug('submit prevented: already submitting');
      return;
    }
    _submitting = true;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Submitting...';
    if (alertEl) { alertEl.style.display = 'none'; alertEl.innerHTML = ''; }

    try {
      const key = (assignmentKeyInput && assignmentKeyInput.value) ? assignmentKeyInput.value : '';
      if(!key) {
        if (alertEl) { alertEl.innerHTML = 'Assignment not specified'; alertEl.style.display = 'block'; }
        submitBtn.disabled=false;
        submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit';
        _submitting = false;
        return;
      }

      const token = localStorage.getItem('token')||sessionStorage.getItem('token')||'';
      if(!token) {
        if (alertEl) { alertEl.innerHTML = 'Authentication required. Please sign in and try again.'; alertEl.style.display = 'block'; }
        submitBtn.disabled=false;
        submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit';
        _submitting = false;
        return;
      }

      const MAX_BYTES = 50 * 1024 * 1024;
      if(fileInput && fileInput.files && fileInput.files.length) {
        for(const f of Array.from(fileInput.files)) {
          if(f.size > MAX_BYTES) {
            if (alertEl) { alertEl.innerHTML = `File "${escapeHtml(f.name)}" exceeds maximum size of 50 MB.`; alertEl.style.display = 'block'; }
            submitBtn.disabled=false;
            submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit';
            _submitting = false;
            return;
          }
        }
      }

      const fd = new FormData();
      if(fileInput && fileInput.files && fileInput.files.length){
        Array.from(fileInput.files).forEach(f => fd.append('attachments[]', f));
      }

      ['content_text','content_html','link_url','repo_url','attempt_no','version_no'].forEach(n=>{
        const el = form.querySelector(`[name="${n}"]`) || document.getElementById(n);
        if(el && (el.value || (el.type === 'checkbox' && el.checked))) fd.append(n, el.value);
      });

      let res;
      try {
        res = await fetch(`/api/assignments/${encodeURIComponent(key)}/submit`, {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
          body: fd
        });
      } catch (networkErr) {
        console.error('Network error during submit', networkErr);
        if (alertEl) { alertEl.innerHTML = 'Network error — submission failed. Check your connection.'; alertEl.style.display = 'block'; }
        throw networkErr;
      }

      let json = null;
      try { json = await res.json(); } catch(parseErr) { console.warn('Submit response parse failed', parseErr); json = null; }

      if(!res.ok) {
        if(res.status === 422 && json && json.errors) {
          const messages = Object.entries(json.errors).map(([k,v])=>`${k}: ${Array.isArray(v)?v.join(', '):v}`).join('<br>');
          if (alertEl) { alertEl.innerHTML = messages; alertEl.style.display = 'block'; }
        } else if(json && (json.error || json.message)) {
          if (alertEl) { alertEl.innerHTML = json.error || json.message; alertEl.style.display = 'block'; }
        } else {
          if (alertEl) { alertEl.innerHTML = (json && json.message) ? json.message : ('HTTP '+res.status); alertEl.style.display = 'block'; }
        }
        throw new Error('Submit failed: HTTP ' + res.status);
      }

      // Success - close modal and refresh data
      closeModal({reset:true});
      if(typeof showOk === 'function') showOk((json && json.message) ? json.message : 'Submitted successfully');
      if(typeof loadAssignments === 'function') loadAssignments();

    } catch(err) {
      console.error('Submit error', err);
      if(typeof showErr === 'function') showErr('Submission failed');
      else { if(alertEl){ alertEl.innerHTML = 'Submission failed'; alertEl.style.display = 'block'; } }
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit';
      _submitting = false;
    }
  });

  function sanitizeHtml(html){
    if(window.DOMPurify && typeof DOMPurify.sanitize === 'function') return DOMPurify.sanitize(html, {ALLOWED_TAGS:['p','br','strong','b','em','i','ul','ol','li','a'], ALLOWED_ATTR:['href','target','rel']});
    return html.replace(/<(script|style)[^>]*>[\s\S]*?<\/\1>/gi,'').replace(/on\w+="[^"]*"/g,'');
  }
})();


  let allowedTypes=[], selectedFiles=[]; 
  const assignModalEl=document.getElementById('assignAllowedTypesModal');
  /* ===== Ensure Allowed Types modal is populated & opens reliably ===== */
(function ensureAssignAllowedTypesShow() {
  const modalId = 'assignAllowedTypesModal'; // expected modal id in this page
  const modalEl = document.getElementById(modalId);
  // possible trigger buttons (cover both pages): btnAllowedTypes (create page) and assignAllowedBtn (assignments page)
  const triggers = Array.from(document.querySelectorAll('#btnAllowedTypes, #assignAllowedBtn'));

  if (!modalEl) {
    console.warn(`[AllowedTypes] modal element #${modalId} not found.`);
    return;
  }

  const master = [
    {t:'pdf', label:'PDF (.pdf)', icon:'fa fa-file-pdf text-danger'},
    {t:'doc', label:'Word (.doc)', icon:'fa fa-file-word text-primary'},
    {t:'docx', label:'Word (.docx)', icon:'fa fa-file-word text-primary'},
    {t:'txt', label:'Text (.txt)', icon:'fa fa-file-lines text-secondary'},
    {t:'pptx', label:'PowerPoint (.pptx)', icon:'fa fa-file-powerpoint text-danger'},
    {t:'xlsx', label:'Excel (.xlsx)', icon:'fa fa-file-excel text-success'},
    {t:'zip', label:'ZIP (.zip)', icon:'fa fa-file-zipper text-warning'},
    {t:'rar', label:'RAR (.rar)', icon:'fa fa-file-zipper text-warning'},
    {t:'7z', label:'7-Zip (.7z)', icon:'fa fa-file-zipper text-warning'},
    {t:'jpg', label:'JPEG (.jpg)', icon:'fa fa-image text-success'},
    {t:'png', label:'PNG (.png)', icon:'fa fa-image text-success'},
    {t:'gif', label:'GIF (.gif)', icon:'fa fa-image text-success'},
    {t:'mp4', label:'MP4 (.mp4)', icon:'fa fa-file-video text-info'},
    {t:'js', label:'JavaScript (.js)', icon:'fa fa-code text-info'},
    {t:'py', label:'Python (.py)', icon:'fa fa-code text-info'},
    {t:'java', label:'Java (.java)', icon:'fa fa-code text-info'},
    {t:'html', label:'HTML (.html)', icon:'fa fa-code text-info'},
    {t:'css', label:'CSS (.css)', icon:'fa fa-code text-info'},
    {t:'svg', label:'SVG (.svg)', icon:'fa fa-image text-success'}
  ];

  // Populate the grid when modal opens
  function populateGrid() {
    const grid = modalEl.querySelector('.type-grid');
    if (!grid) { console.warn('[AllowedTypes] .type-grid not found inside modal'); return; }
    // produce unique list (avoid duplicates)
    const seen = new Set();
    grid.innerHTML = master.filter(m => {
      if (seen.has(m.t)) return false; seen.add(m.t); return true;
    }).map(m => {
      return `<label class="type-item" style="cursor:pointer;padding:8px;">
                <input type="checkbox" data-type="${m.t}" style="margin-right:8px;">
                <i class="${m.icon}"></i>
                <span style="margin-left:8px">${m.label}</span>
              </label>`;
    }).join('');
  }

  // Show modal programmatically (Bootstrap-aware fallback)
  function showModal() {
    try {
      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        let inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        inst.show();
        return;
      }
    } catch(e) { console.warn('bootstrap modal show failed', e); }
    // fallback: manual show
    modalEl.classList.add('show'); modalEl.style.display = 'block'; modalEl.setAttribute('aria-hidden','false');
    if (!document.getElementById('assignAllowedBackdrop')) {
      const bd = document.createElement('div'); bd.id = 'assignAllowedBackdrop'; bd.className = 'modal-backdrop fade show'; document.body.appendChild(bd);
    }
    document.body.classList.add('modal-open');
  }

  // Wire triggers: ensure grid populated then show modal
  triggers.forEach(btn => {
    if (!btn) return;
    btn.addEventListener('click', (ev) => {
      ev.preventDefault();
      try { populateGrid(); } catch (e) { console.error('populateGrid error', e); }
      showModal();
      // focus first checkbox for accessibility
      setTimeout(()=> {
        const first = modalEl.querySelector('.type-grid input[type="checkbox"]');
        if (first) first.focus();
      }, 50);
    });
  });

  // also populate when bootstrap triggers show (in case some other code opens modal)
  modalEl.addEventListener('show.bs.modal', populateGrid);
  // safety: also populate on click inside modal header (rare cases)
  modalEl.addEventListener('click', e => { if (e.target && e.target.closest('.type-grid')) return; });

  // debug helper to call from console if needed
  window._debugPopulateAssignTypes = populateGrid;
})();

  const descriptionFieldId=document.getElementById('assign_description')?'assign_description':'assign_instructions';
  wireRTE_Modal(descriptionFieldId,'assignBtnLink');
  
  (function initModalRTEPlaceholder(){
    const descriptionFieldId=document.getElementById('assign_description')?'assign_description':'assign_instructions';
    const editor=document.getElementById(descriptionFieldId); if(!editor) return;
    const wrap=editor.closest('.rte-wrap'); const ph=wrap?wrap.querySelector('.rte-ph'):null;
    function togglePh(){const text=(editor.textContent||'').replace(/\u00A0/g,'').trim();const html=(editor.innerHTML||'').replace(/<br\s*\/?>/gi,'').replace(/\u00A0/g,'').trim();const has=text.length>0||html.length>0;editor.classList.toggle('has-content',!!has);if(ph)ph.style.display=has?'none':'';}
    ['input','keyup','paste','blur','cut'].forEach(ev=>editor.addEventListener(ev,()=>setTimeout(togglePh,0)));togglePh();
    const modalEl=document.getElementById('createAssignmentModal');if(modalEl){modalEl.addEventListener('shown.bs.modal',()=>setTimeout(togglePh,0));modalEl.addEventListener('hidden.bs.modal',()=>{editor.innerHTML='';editor.classList.remove('has-content');if(ph)ph.style.display='';});}
  })();

  function renderAssignModalSelected(){const container=document.getElementById('assign_modalSelectedType');if(!container)return;container.innerHTML='';allowedTypes.forEach(t=>{const chip=document.createElement('div');chip.className='type-chip';chip.innerHTML=`<strong>.${escapeHtml(t)}</strong>`;container.appendChild(chip);});}
  function renderAssignSelectedChips(){const wrap=document.getElementById('assignSelectedTypeWrap');if(!wrap)return;wrap.innerHTML='';if(!allowedTypes.length){wrap.innerHTML='<div class="tiny text-muted">All types allowed</div>';return;}allowedTypes.forEach((t,idx)=>{const chip=document.createElement('div');chip.className='type-chip';chip.innerHTML=`<strong>.${escapeHtml(t)}</strong><span class="remove-type" data-idx="${idx}" style="margin-left:8px;cursor:pointer;color:#c00">&times;</span>`;wrap.appendChild(chip);});wrap.querySelectorAll('.remove-type').forEach(el=>{el.addEventListener('click',()=>{allowedTypes.splice(Number(el.dataset.idx),1);renderAssignModalSelected();renderAssignSelectedChips();});});}

  if(assignModalEl){assignModalEl.addEventListener('show.bs.modal',()=>{assignModalEl.querySelectorAll('input[type="checkbox"][data-type]').forEach(cb=>{cb.checked=allowedTypes.includes((cb.dataset.type||'').toLowerCase());});renderAssignModalSelected();});assignModalEl.addEventListener('click',ev=>{const target=ev.target;if(target&&target.matches('input[type="checkbox"][data-type]')){const t=(target.dataset.type||'').toLowerCase();if(target.checked){if(!allowedTypes.includes(t))allowedTypes.push(t);}else{allowedTypes=allowedTypes.filter(x=>x!==t);}renderAssignModalSelected();renderAssignSelectedChips();}});}

  document.getElementById('assign_btnAddType')?.addEventListener('click',()=>{const v=(document.getElementById('assign_custom_type').value||'').trim().toLowerCase();if(!v)return;if(!allowedTypes.includes(v))allowedTypes.push(v);document.getElementById('assign_custom_type').value='';renderAssignModalSelected();renderAssignSelectedChips();});
  document.getElementById('assign_btnClearAllTypes')?.addEventListener('click',()=>{allowedTypes=[];renderAssignModalSelected();renderAssignSelectedChips();});
  document.getElementById('assign_btnSaveTypes')?.addEventListener('click',()=>{renderAssignSelectedChips();});

  function formatFileSize(bytes){if(bytes<1024)return bytes+' B';if(bytes<1024*1024)return (bytes/1024).toFixed(1)+' KB';return (bytes/(1024*1024)).toFixed(1)+' MB';}
  const assignDrop=document.getElementById('assign_dropzone'), assignFileInput=document.getElementById('assign_attachments'), assignFileList=document.getElementById('assign_fileList');
  function renderAssignFileList(){if(!assignFileList)return;assignFileList.innerHTML='';selectedFiles.forEach((f,idx)=>{const div=document.createElement('div');div.className='file-item';div.innerHTML=`<div class="file-info"><i class="fa fa-file"></i><span class="file-name" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</span><span class="file-size">${formatFileSize(f.size)}</span></div><span class="file-remove" data-idx="${idx}" style="cursor:pointer;color:#c00">&times;</span>`;assignFileList.appendChild(div);});assignFileList.querySelectorAll('.file-remove').forEach(btn=>{btn.addEventListener('click',()=>{selectedFiles.splice(Number(btn.dataset.idx),1);renderAssignFileList();});});}
  if(assignDrop){['dragenter','dragover'].forEach(e=>assignDrop.addEventListener(e,ev=>{ev.preventDefault();assignDrop.classList.add('dragover');}));['dragleave','drop','dragend'].forEach(e=>assignDrop.addEventListener(e,ev=>{ev.preventDefault();assignDrop.classList.remove('dragover');}));assignDrop.addEventListener('drop',ev=>{const f=ev.dataTransfer&&ev.dataTransfer.files;if(f){Array.from(f).forEach(x=>selectedFiles.push(x));renderAssignFileList();}});}
  if(assignFileInput){assignFileInput.addEventListener('change',()=>{if(assignFileInput.files){Array.from(assignFileInput.files).forEach(x=>selectedFiles.push(x));renderAssignFileList();assignFileInput.value='';}});}
  document.getElementById('assign_btnClearFiles')?.addEventListener('click',()=>{selectedFiles=[];renderAssignFileList();});

  const assignAllowLate=document.getElementById('assign_allow_late'), assignLatePenalty=document.getElementById('assign_late_penalty');
  function syncLateUI(){if(assignLatePenalty)assignLatePenalty.disabled=!(assignAllowLate&&assignAllowLate.checked);if(assignAllowLate&&!assignAllowLate.checked&&assignLatePenalty)assignLatePenalty.value='';}
  if(assignAllowLate)assignAllowLate.addEventListener('change',syncLateUI);syncLateUI();

  const assignTitleEl=document.getElementById('assign_title'), assignSlugEl=document.getElementById('assign_slug');
  if(assignTitleEl)assignTitleEl.addEventListener('blur',()=>{const s=assignSlugEl;if(s&&!s.value.trim()){s.value=(assignTitleEl.value||'').toLowerCase().replace(/\s+/g,'-').replace(/[^\w\-]+/g,'').slice(0,140);}});

function resetCreateModalFields(){try{const cf=document.getElementById('assignCreateForm'); if(cf) delete cf.dataset.editing; window.existingAttachmentIds=[]; document.getElementById('assign_title')&&(document.getElementById('assign_title').value='');document.getElementById('assign_slug')&&(document.getElementById('assign_slug').value='');const inst=document.getElementById(descriptionFieldId);if(inst)inst.innerHTML='';document.getElementById('assign_status')&&(document.getElementById('assign_status').value='draft');document.getElementById('assign_submission_type')&&(document.getElementById('assign_submission_type').value='file');document.getElementById('assign_attempts_allowed')&&(document.getElementById('assign_attempts_allowed').value='');document.getElementById('assign_total_marks')&&(document.getElementById('assign_total_marks').value='');document.getElementById('assign_pass_marks')&&(document.getElementById('assign_pass_marks').value='');document.getElementById('assign_due_at')&&(document.getElementById('assign_due_at').value='');document.getElementById('assign_end_at')&&(document.getElementById('assign_end_at').value='');if(assignAllowLate)assignAllowLate.checked=false;if(assignLatePenalty)assignLatePenalty.value='';selectedFiles=[];renderAssignFileList();allowedTypes=[];renderAssignModalSelected();renderAssignSelectedChips();const alertEl=document.getElementById('assignCreateAlert');if(alertEl){alertEl.style.display='none';alertEl.innerHTML='';}}catch(e){console.warn('reset modal fields error',e);}}

  if(modalEl){if(bootstrapAvailable){modalEl.addEventListener('hidden.bs.modal',()=>{resetCreateModalFields();});}else{modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn=>btn.addEventListener('click',()=>{hideModalSafe();resetCreateModalFields();}));}}

  if($uploadBtn){$uploadBtn.type='button';if(canCreate){$uploadBtn.style.display='inline-block';$uploadBtn.addEventListener('click',ev=>{ev.preventDefault();const ctx=(typeof readContext==='function')?readContext():readContextFallback();const batchKey=ctx?.batch_id||deriveCourseKey();const batchInput=document.getElementById('assign_batch_key');if(batchInput)batchInput.value=batchKey||'';resetCreateModalFields();showModalSafe();});}else{$uploadBtn.style.display='none';}}

  function openCreateModal(){if(!modalEl){Swal.fire({icon:'info',title:'Create assignment',html:'Assignment creation modal not found on this page. <br><a href="/admin/assignments/new" target="_blank">Open admin > create assignment</a>',showCloseButton:true});return;}const ctx=(typeof readContext==='function')?readContext():readContextFallback();const batchKey=ctx?.batch_id||deriveCourseKey();const batchInput=document.getElementById('assign_batch_key');if(batchInput)batchInput.value=batchKey||'';resetCreateModalFields();showModalSafe();}
  window.openCreateAssignmentModal=openCreateModal;

  const createForm=document.getElementById('assignCreateForm');
  function getSanitizedInstructionsHtml(){const instEl=document.getElementById(descriptionFieldId)||{};const raw=(instEl.innerHTML||instEl.value||'').trim();if(!raw)return'';const cleaned=sanitizeHtml(raw);const final=enforceSafeLinksOnFragment(cleaned);return final;}

  if(createForm){
createForm.addEventListener('submit',async(ev)=>{ev.preventDefault();const alertEl=document.getElementById('assignCreateAlert');if(alertEl){alertEl.style.display='none';alertEl.innerHTML='';}
      const title=(document.getElementById('assign_title').value||'').trim();if(!title){if(alertEl){alertEl.textContent='Title required';alertEl.style.display='';}return;}
      const batchKey=(document.getElementById('assign_batch_key')||{}).value||'';if(!batchKey){if(alertEl){alertEl.textContent='Batch context not found';alertEl.style.display='';}return;}
      const fd=new FormData();const ctx=(typeof readContext==='function')?readContext():readContextFallback();if(ctx&&ctx.module_id){if(/^\d+$/.test(String(ctx.module_id)))fd.append('course_module_id',ctx.module_id);else fd.append('module_uuid',ctx.module_id);}
      fd.append('title',title);const slug=(document.getElementById('assign_slug').value||'').trim();if(slug)fd.append('slug',slug);
      const instHtml=getSanitizedInstructionsHtml();
      if(instHtml){fd.append('description',instHtml);fd.append('instruction',instHtml);fd.append('instructions',instHtml);}else{fd.append('description','');fd.append('instruction','');fd.append('instructions','');}
      fd.append('status',(document.getElementById('assign_status')||{}).value||'draft');fd.append('submission_type',(document.getElementById('assign_submission_type')||{}).value||'file');
      if(allowedTypes&&allowedTypes.length)allowedTypes.forEach(t=>fd.append('allowed_submission_types[]',t));
      const attempts=(document.getElementById('assign_attempts_allowed')||{}).value;if(attempts!=='')fd.append('attempts_allowed',attempts);
      const totalMarks=(document.getElementById('assign_total_marks')||{}).value;if(totalMarks!=='')fd.append('total_marks',totalMarks);
      const passMarks=(document.getElementById('assign_pass_marks')||{}).value;if(passMarks!=='')fd.append('pass_marks',passMarks);
      const dueAt=(document.getElementById('assign_due_at')||{}).value;if(dueAt)fd.append('due_at',dueAt);const endAt=(document.getElementById('assign_end_at')||{}).value;if(endAt)fd.append('end_at',endAt);
      fd.append('allow_late',(assignAllowLate&&assignAllowLate.checked)?'1':'0');if(assignAllowLate&&assignAllowLate.checked&&assignLatePenalty&&assignLatePenalty.value)fd.append('late_penalty_percent',assignLatePenalty.value);

      if(window.existingAttachmentIds && Array.isArray(window.existingAttachmentIds) && window.existingAttachmentIds.length){
        window.existingAttachmentIds.forEach(id=>fd.append('existing_attachment_ids[]', id));
      }

      selectedFiles.forEach(f=>fd.append('attachments[]',f));

      const saveBtn=document.getElementById('assignSave');if(saveBtn){saveBtn.disabled=true;saveBtn.classList.add('btn-loading');}

      try{
        const editingId=(createForm&&createForm.dataset&&createForm.dataset.editing)?String(createForm.dataset.editing).trim():'';
        let url,methodLabel='created';
        if(editingId){
          fd.append('_method','PUT');
          url=`/api/assignments/${encodeURIComponent(editingId)}`;
          methodLabel='updated';
        }else{
          url=`/api/assignments/batch/${encodeURIComponent(batchKey)}`;
        }

        const res=await fetch(url,{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'},body:fd});
        const json=await res.json().catch(()=>({}));
        if(!res.ok){
          if(res.status===422&&json.errors){
            const messages=Object.entries(json.errors).map(([k,v])=>`${k}: ${Array.isArray(v)?v.join(', '):v}`).join('<br>');
            if(alertEl){alertEl.innerHTML=messages;alertEl.style.display='';}
          }else{
            if(alertEl){alertEl.innerHTML=(json.message||('HTTP '+res.status));alertEl.style.display='';}
          }
          throw new Error('Save failed');
        }

        hideModalSafe();
        resetCreateModalFields();
        if(typeof showOk==='function')showOk(json.message||(`Assignment ${methodLabel}`));else alert(json.message||(`Assignment ${methodLabel}`));
        if(typeof loadAssignments==='function')loadAssignments();

      }catch(err){
        console.error('Create assignment error',err);
        if(typeof showErr==='function')showErr('Create failed');
      }finally{
        if(saveBtn){saveBtn.disabled=false;saveBtn.classList.remove('btn-loading');}
      }
});   
}    
function openEditModal(item) {
    console.log('Opening edit modal for item:', item);
    
    // Check if modal element exists
    if (!modalEl) { 
        console.error('Modal element not found');
        showErr('Create modal not available'); 
        return; 
    }

    try {
        resetCreateModalFields();
    } catch (error) {
        console.warn('Error in resetCreateModalFields:', error);
    }

    // mark editing id on form
    try {
        createForm.dataset.editing = String(item.id || item.uuid || '');
    } catch (error) {
        console.warn('Error setting editing ID:', error);
    }

    // modal title + button
    try {
        const titleEl = modalEl.querySelector('.modal-title');
        if (titleEl) titleEl.innerHTML = `<i class="fa fa-pen me-2"></i> Edit Assignment`;
        const saveBtn = document.getElementById('assignSave');
        if (saveBtn) saveBtn.innerHTML = `<i class="fa fa-save me-1"></i> Update`;
    } catch (error) {
        console.warn('Error updating modal header:', error);
    }

    // basic fields with error handling
    const basicFields = [
        { id: 'assign_title', value: item.title || '' },
        { id: 'assign_slug', value: item.slug || '' },
        { id: 'assign_status', value: item.status || 'draft' },
        { id: 'assign_submission_type', value: item.submission_type || 'file' },
        { id: 'assign_attempts_allowed', value: item.attempts_allowed ?? '' },
        { id: 'assign_total_marks', value: item.total_marks ?? '' },
        { id: 'assign_pass_marks', value: item.pass_marks ?? '' },
        { id: 'assign_due_at', value: item.due_at ? (new Date(item.due_at)).toISOString().slice(0,16) : '' },
        { id: 'assign_end_at', value: item.end_at ? (new Date(item.end_at)).toISOString().slice(0,16) : '' }
    ];

    basicFields.forEach(field => {
        try {
            const element = document.getElementById(field.id);
            if (element) {
                element.value = field.value;
            }
        } catch (error) {
            console.warn(`Error setting field ${field.id}:`, error);
        }
    });

    // late options
    try {
        const allowLateEl = document.getElementById('assign_allow_late');
        const latePenaltyEl = document.getElementById('assign_late_penalty');
        if (allowLateEl) allowLateEl.checked = !!(item.allow_late || item.allow_late === 1 || item.allow_late === '1');
        if (latePenaltyEl) latePenaltyEl.value = item.late_penalty_percent ?? '';
        
        if (typeof syncLateUI === 'function') {
            syncLateUI();
        }
    } catch (error) {
        console.warn('Error setting late options:', error);
    }

    // allowed types - IMPROVED VERSION
    try {
        console.log('Processing allowed_submission_types:', item.allowed_submission_types);
        
        if (Array.isArray(item.allowed_submission_types)) {
            allowedTypes = item.allowed_submission_types.map(String).filter(Boolean);
        } else if (typeof item.allowed_submission_types === 'string') {
            // Try to parse as JSON, or handle as comma-separated
            try {
                const parsed = JSON.parse(item.allowed_submission_types);
                allowedTypes = Array.isArray(parsed) ? parsed.map(String).filter(Boolean) : [];
            } catch (e) {
                // If not JSON, try comma-separated
                allowedTypes = item.allowed_submission_types.split(',').map(s => s.trim()).filter(Boolean);
            }
        } else {
            allowedTypes = [];
        }
        
        console.log('Processed allowedTypes:', allowedTypes);

        // Render the selected types with error handling
        if (typeof renderAssignModalSelected === 'function') {
            console.log('Calling renderAssignModalSelected');
            renderAssignModalSelected();
        } else {
            console.warn('renderAssignModalSelected is not a function');
        }
        
        if (typeof renderAssignSelectedChips === 'function') {
            console.log('Calling renderAssignSelectedChips');
            renderAssignSelectedChips();
        } else {
            console.warn('renderAssignSelectedChips is not a function');
        }
    } catch (error) {
        console.error('Error in allowed types processing:', error);
        allowedTypes = [];
    }

    // instructions / description
    try {
        const descEl = document.getElementById(descriptionFieldId);
        if (descEl) {
            descEl.innerHTML = item.description || item.instruction || item.instructions || '';
            // toggle placeholder state
            const ph = descEl.parentElement && descEl.parentElement.querySelector('.rte-ph');
            const has = (descEl.textContent || '').trim().length > 0 || (descEl.innerHTML || '').trim().length > 0;
            descEl.classList.toggle('has-content', !!has);
            if (ph) ph.style.display = has ? 'none' : '';
        }
    } catch (error) {
        console.warn('Error setting description:', error);
    }

    // existing attachments
    try {
        window.existingAttachmentIds = [];
        const attachListEl = document.getElementById('assign_fileList');
        if (attachListEl) {
            attachListEl.innerHTML = '';
            const attachments = normalizeAttachments(item);
            attachments.forEach((a, idx) => {
                // store id/path for server to keep
                const keepId = a.id ?? a.attachment_id ?? a.file_id ?? a.path ?? a.url ?? null;
                if (keepId) window.existingAttachmentIds.push(keepId);
                const div = document.createElement('div'); 
                div.className = 'file-item existing-file';
                div.innerHTML = `
                    <div class="file-info">
                        <i class="fa fa-file"></i>
                        <span class="file-name" title="${escapeHtml(a.name||'file')}">${escapeHtml(a.name||'file')}</span>
                        <span class="file-size">${a.size ? formatSize(a.size) : ''}</span>
                    </div>
                    <span class="file-remove-existing" data-idx="${idx}" style="cursor:pointer;color:#c00">&times;</span>
                `;
                attachListEl.appendChild(div);
                
                const removeBtn = div.querySelector('.file-remove-existing');
                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        const removed = window.existingAttachmentIds[idx];
                        window.existingAttachmentIds = window.existingAttachmentIds.filter(x => x !== removed);
                        div.remove();
                    });
                }
            });
        } else {
            window.existingAttachmentIds = [];
        }
    } catch (error) {
        console.warn('Error setting attachments:', error);
        window.existingAttachmentIds = [];
    }

    // set batch key
    try {
        const ctx = (typeof readContext === 'function') ? readContext() : readContextFallback();
        const batchKey = ctx?.batch_id || deriveCourseKey();
        const batchInput = document.getElementById('assign_batch_key');
        if (batchInput) batchInput.value = batchKey || '';
    } catch (error) {
        console.warn('Error setting batch key:', error);
    }

    // show modal - ROBUST VERSION
    console.log('Attempting to show modal...');
    
    try {
        // Check if modal element still exists
        if (!modalEl) {
            throw new Error('modalEl became null');
        }

        // Method 1: Use showModalSafe if available
        if (typeof showModalSafe === 'function') {
            console.log('Using showModalSafe function');
            showModalSafe();
        } 
        // Method 2: Use Bootstrap Modal
        else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            console.log('Using Bootstrap Modal');
            let modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (!modalInstance) {
                console.log('Creating new Bootstrap Modal instance');
                modalInstance = new bootstrap.Modal(modalEl, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
            }
            modalInstance.show();
        }
        // Method 3: Manual fallback
        else {
            console.log('Using manual modal display');
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Add backdrop
            const existingBackdrop = document.querySelector('.modal-backdrop');
            if (!existingBackdrop) {
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        }
        
        console.log('Modal shown successfully');
        
    } catch (modalError) {
        console.error('Error showing modal:', modalError);
        
        // Emergency fallback
        if (modalEl) {
            console.log('Using emergency fallback for modal');
            modalEl.style.display = 'block';
        } else {
            showErr('Cannot open edit modal - please refresh the page');
        }
    }
}
// safe helper: hide bootstrap dropdown if possible
function safeHideDropdown(triggerEl) {
  try {
    if (!triggerEl) return;
    if (!window.bootstrap || !bootstrap.Dropdown || typeof bootstrap.Dropdown.getOrCreateInstance !== 'function') return;
    const inst = bootstrap.Dropdown.getOrCreateInstance(triggerEl);
    if (inst && typeof inst.hide === 'function') inst.hide();
  } catch (err) {
    // don't break UI on any internal bootstrap error
    console.warn('safeHideDropdown failed', err);
  }
}
  (function initBin(){
    if(!$btnBin)return;if(!canViewBin){$btnBin.style.display='none';return;}else{$btnBin.style.display='inline-block';}
    async function fetchDeletedAssignments(params=''){try{const ctx=readContext();let url;if(ctx&&ctx.batch_id){url=`/api/batches/${encodeURIComponent(ctx.batch_id)}/assignments/bin`+(params?('?'+params):'');}else{url=`/api/assignments/bin`+(params?('?'+params):'');}const r=await apiFetch(url);if(!r.ok)throw new Error('HTTP '+r.status);const j=await r.json().catch(()=>null);const items=j&&(j.data||j.items)?(j.data||j.items):(Array.isArray(j)?j:[]);return(items||[]).map(it=>{if(typeof it.attachments_json==='string'&&it.attachments_json){try{it.attachments_json=JSON.parse(it.attachments_json);}catch{}}return it;}).filter(it=>!!(it&&(it.deleted_at||it.deletedAt)));}catch(e){console.error('fetchDeletedAssignments failed',e);return [];}}
    function buildBinTable(items){
      if(!document.getElementById('bin-overflow-css')){const s=document.createElement('style');s.id='bin-overflow-css';s.textContent=`.dropdown-menu { overflow: visible !important; white-space: nowrap; } .table-responsive { overflow: visible !important; }`;document.head.appendChild(s);}
      const wrap=document.createElement('div');wrap.className='as-card p-3';
      const heading=document.createElement('div');heading.className='d-flex align-items-center justify-content-between mb-2';
      heading.innerHTML=`<div class="fw-semibold" style="font-size:15px">Deleted Assignments</div><div class="d-flex gap-2"><button id="bin-refresh" class="btn btn-sm btn-primary"><i class="fa fa-rotate-right me-1"></i></button><button id="bin-back" class="btn btn-sm btn-outline-primary"> <i class="fa fa-arrow-left me-1"></i> Back</button></div>`;
      wrap.appendChild(heading);
      const resp=document.createElement('div');resp.className='table-responsive';
      const table=document.createElement('table');table.className='table table-hover table-borderless table-sm mb-0';table.style.fontSize='13px';
      table.innerHTML=`<thead class="text-muted" style="font-weight:600; font-size:var(--fs-14);"><tr><th class="text-start">Assignment</th><th style="width:140px">Created</th><th style="width:160px">Deleted At</th><th style="width:120px">Attachments</th><th style="width:120px" class="text-end">Actions</th></tr></thead><tbody></tbody>`;
      const tbody=table.querySelector('tbody');
      if(!items||items.length===0){tbody.innerHTML=`<tr><td colspan="5" class="text-center py-3 text-muted small">No deleted items.</td></tr>`;}else{items.forEach((it,idx)=>{const attCount=Array.isArray(it.attachments_json)?it.attachments_json.length:(it.attachment_count||0);const tr=document.createElement('tr');tr.style.borderTop='1px solid var(--line-soft)';
        const titleTd = document.createElement('td');
titleTd.innerHTML = `<div class="fw-semibold" style="line-height:1.1;">${escapeHtml(it.title || 'Untitled')}</div>`;
const createdTd=document.createElement('td');createdTd.textContent=it.created_at?new Date(it.created_at).toLocaleString():'-';const deletedTd=document.createElement('td');deletedTd.textContent=it.deleted_at?new Date(it.deleted_at).toLocaleString():'-';const attachTd=document.createElement('td');attachTd.textContent=`${attCount} file(s)`;const actionsTd=document.createElement('td');actionsTd.className='text-end';const dd=document.createElement('div');dd.className='dropdown d-inline-block';dd.innerHTML=`<button class="btn btn-sm btn-light" type="button" id="binDdBtn${idx}" data-bs-toggle="dropdown" aria-expanded="false"><span style="font-size:18px; line-height:1;">⋮</span></button><ul class="dropdown-menu dropdown-menu-end" aria-labelledby="binDdBtn${idx}" style="min-width:160px;"><li><button class="dropdown-item restore-action" type="button"><i class="fa fa-rotate-left me-2"></i> Restore</button></li><li><hr class="dropdown-divider"></li><li><button class="dropdown-item text-danger force-action" type="button"><i class="fa fa-skull-crossbones me-2"></i> Delete permanently</button></li></ul>`;actionsTd.appendChild(dd);tr.appendChild(titleTd);tr.appendChild(createdTd);tr.appendChild(deletedTd);tr.appendChild(attachTd);tr.appendChild(actionsTd);
        tbody.appendChild(tr);
            const restoreBtn=dd.querySelector('.restore-action'), forceBtn=dd.querySelector('.force-action');
            restoreBtn.addEventListener('click',()=>{ safeHideDropdown(dd.querySelector('[data-bs-toggle="dropdown"]'));
              restoreItem(it);});
            forceBtn.addEventListener('click',()=>{ safeHideDropdown(dd.querySelector('[data-bs-toggle="dropdown"]')); 
              forceDeleteItem(it);});
      });}
      resp.appendChild(table);wrap.appendChild(resp);
      setTimeout(()=>{wrap.querySelector('#bin-refresh')?.addEventListener('click',()=>openBin());wrap.querySelector('#bin-back')?.addEventListener('click',()=>loadAssignments());},0);
      return wrap;
    }

    async function restoreItem(item){const r=await Swal.fire({title:'Restore item?',text:`Restore "${item.title||'this item'}"?`,icon:'question',showCancelButton:true,confirmButtonText:'Yes, restore',cancelButtonText:'Cancel'});if(!r.isConfirmed)return;try{const url=`/api/assignments/${encodeURIComponent(item.id||item.uuid)}/restore`;const res=await apiFetch(url,{method:'POST'});if(!res.ok)throw new Error('Restore failed: '+res.status);showOk('Restored');await openBin();}catch(e){console.error(e);showErr('Restore failed');}}
    async function forceDeleteItem(item){const r=await Swal.fire({title:'Permanently delete?',html:`Permanently delete "<strong>${escapeHtml(item.title||'this item')}</strong>"?<br><strong>This cannot be undone.</strong>`,icon:'warning',showCancelButton:true,confirmButtonText:'Yes, delete permanently',cancelButtonText:'Cancel',focusCancel:true});if(!r.isConfirmed)return;try{const url=`/api/assignments/${encodeURIComponent(item.id||item.uuid)}/force`;const res=await apiFetch(url,{method:'DELETE'});if(!res.ok)throw new Error('Delete failed: '+res.status);showOk('Permanently deleted');await openBin();}catch(e){console.error(e);showErr('Delete failed');}}

    let _prevContent=null;
    async function openBin(){if(!_prevContent&&$items)_prevContent=$items.innerHTML;showLoader(true);showEmpty(false);showItems(false);try{const ctx=readContext();const params=new URLSearchParams();if(ctx&&ctx.module_id)params.set('module_uuid',ctx.module_id);const items=await fetchDeletedAssignments(params.toString());const tableEl=buildBinTable(items||[]);if($items){$items.innerHTML='';$items.appendChild(tableEl);showItems(true);}const back=document.getElementById('bin-back');if(back)back.addEventListener('click',e=>{e.preventDefault();restorePreviousList();});const refresh=document.getElementById('bin-refresh');if(refresh)refresh.addEventListener('click',e=>{e.preventDefault();openBin();});}catch(e){console.error(e);if($items)$items.innerHTML='<div class="as-empty p-3">Unable to load bin. Try refreshing the page.</div>';showItems(true);showErr('Failed to load bin');}finally{showLoader(false);}}
    function restorePreviousList(){if($items){if(_prevContent!==null){$items.innerHTML=_prevContent;_prevContent=null;}else{if(typeof loadAssignments==='function')loadAssignments();}}}
    $btnBin.addEventListener('click',ev=>{ev.preventDefault();openBin();});
  })();

  loadAssignments();
})();
</script>
