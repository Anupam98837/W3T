{{-- resources/views/modules/assignments/createAssignment.blade.php --}}
@section('title','Create Assignment')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  /* ===== Shell ===== */
  .qz-wrap{max-width:1100px;margin:14px auto 40px}
  .qz.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:hidden}
  .qz .card-header{background:var(--surface);border-bottom:1px solid var(--line-strong);padding:16px 18px}
  .qz-head{display:flex;align-items:center;gap:10px}
  .qz-head i{color:var(--accent-color)}
  .qz-head strong{color:var(--ink);font-family:var(--font-head);font-weight:700}
  .qz-head .hint{color:var(--muted-color);font-size:var(--fs-13)}

  .section-title{font-weight:600;color:var(--ink);font-family:var(--font-head);margin:12px 2px 14px}
  .divider-soft{height:1px;background:var(--line-soft);margin:10px 0 16px}

  /* RTE */
  .toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
  .tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
  .tool:hover{background:var(--page-hover)}
  .rte-wrap{position:relative}
  .rte{
    min-height:200px;max-height:600px;overflow:auto;
    border:1px solid var(--line-strong);border-radius:12px;background:#fff;padding:12px;line-height:1.6;outline:none
  }
  .rte:focus{box-shadow:var(--ring);border-color:var(--accent-color)}
  .rte-ph{position:absolute;top:12px;left:12px;color:#9aa3b2;pointer-events:none;font-size:var(--fs-14)}
  .rte.has-content + .rte-ph{display:none}

  /* Inputs polish */
  .form-control:focus, .form-select:focus{box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 20%, transparent);border-color:var(--accent-color)}
  .input-group-text{background:var(--surface);border-color:var(--line-strong)}
  .tiny{font-size:12px;color:#6b7280}

  /* Errors */
  .err{font-size:12px;color:var(--danger-color);display:none;margin-top:6px}
  .err:not(:empty){display:block}

  /* Busy overlay */
  .dim{position:absolute;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.06);z-index:2}
  .dim.show{display:flex}
  .spin{width:18px;height:18px;border:3px solid #0001;border-top-color:var(--accent-color);border-radius:50%;animation:rot 1s linear infinite}
  @keyframes rot{to{transform:rotate(360deg)}}

  /* Attachments dropzone */
  .dropzone{
    display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;
    border:2px dashed var(--line-strong);border-radius:14px;background:var(--surface-2, #fff);
    padding:24px; transition:border-color .18s ease, background .18s ease;
  }
  .dropzone:hover{border-color:var(--primary-color);background:color-mix(in oklab, var(--primary-color) 7%, transparent)}
  .dropzone.dragover{border-color:var(--primary-color);box-shadow:0 0 0 3px color-mix(in oklab, var(--primary-color) 15%, transparent)}
  .drop-icon{width:56px;height:56px;border-radius:999px;border:1px dashed var(--line-strong);display:flex;align-items:center;justify-content:center;margin-bottom:10px;opacity:.9}
  .drop-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}

  /* File list */
  .file-list{margin-top:12px;display:flex;flex-direction:column;gap:8px}
  .file-item{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border:1px solid var(--line-strong);border-radius:10px;background:var(--surface)}
  .file-info{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
  .file-info i{color:var(--muted-color)}
  .file-name{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px}
  .file-size{color:var(--muted-color);font-size:12px;white-space:nowrap}
  .file-remove{color:var(--danger-color);cursor:pointer;padding:4px 8px}
  .file-remove:hover{opacity:.7}

  /* Toggle switch */
  .toggle-wrap{display:flex;align-items:center;gap:10px}
  .toggle-switch{position:relative;display:inline-block;width:48px;height:24px}
  .toggle-switch input{opacity:0;width:0;height:0}
  .toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#cbd5e1;border-radius:24px;transition:.3s}
  .toggle-slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:white;border-radius:50%;transition:.3s}
  .toggle-switch input:checked + .toggle-slider{background:var(--primary-color)}
  .toggle-switch input:checked + .toggle-slider:before{transform:translateX(24px)}

  /* Allowed submission types styling */
  .selected-types{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap}
  .type-chip{background:var(--surface);border:1px solid var(--line-strong);padding:6px 8px;border-radius:999px;font-size:13px;display:inline-flex;align-items:center;gap:8px}
  .type-chip .remove-type{cursor:pointer;color:var(--danger-color);font-size:12px;padding-left:6px}

  /* Modal styles */
  .modal .type-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;max-height:300px;overflow-y:auto;padding:5px}
  .modal .type-item{display:flex;align-items:center;gap:8px;padding:8px;border:1px solid var(--line-strong);border-radius:8px;background:var(--surface);cursor:pointer;transition:background-color 0.2s}
  .modal .type-item:hover{background:var(--page-hover)}
  .modal .type-item input{transform:scale(1.15);margin:0}

  /* Button loading state */
  .btn-loading{pointer-events:none;opacity:.85}
  .btn-loading .btn-label{visibility:hidden}
  .btn-loading .btn-spinner{display:inline-block !important}

  .btn-spinner{display:none; width:1rem;height:1rem;border:.2rem solid #0001;border-top-color:#fff;border-radius:50%;vertical-align:-.125em;animation:rot 1s linear infinite}
  .btn-light .btn-spinner{border-top-color:#0009}

  /* Dark mode parity */
  html.theme-dark .rte{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .tool{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .dropzone{background:#0f172a;border-color:var(--line-strong)}
  html.theme-dark .file-item{background:#0f172a;border-color:var(--line-strong)}
  .lib-card {
  position: relative;
  overflow: hidden;
}

.lib-overlay-check {
  position: absolute;
  top: 8px;
  left: 8px;
  z-index: 5;
  background: rgba(255,255,255,0.95);
  padding: 4px 8px;
  border-radius: 999px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

.lib-overlay-check .form-check-input {
  cursor: pointer;
}

</style>

@section('content')
<div class="qz-wrap">
  <div class="card qz">
    <div class="card-header">
      <div class="qz-head">
        <i class="fa-solid fa-file-pen"></i>
        <strong id="pageTitle">Create New Assignment</strong>
        <span class="hint" id="hint">— Configure assignment details, instructions, and submission settings.</span>
      </div>
    </div>

    <div class="card-body position-relative">
      <div class="dim" id="busy"><div class="spin" aria-label="Saving…"></div></div>

      {{-- Course, Module, Batch Cascade --}}
      <h3 class="section-title">Course & Module Selection</h3>
      <div class="divider-soft"></div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label" for="course_select">Course <span class="text-danger">*</span></label>
          <select id="course_select" class="form-select">
            <option value="">Select Course</option>
            {{-- Options will be populated via JS --}}
          </select>
          <div class="err" data-for="course_id"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="module_select">Module <span class="text-danger">*</span></label>
          <select id="module_select" class="form-select" disabled>
            <option value="">Select Module</option>
            {{-- Options will be populated via JS --}}
          </select>
          <div class="err" data-for="course_module_id"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="batch_select">Batch <span class="text-danger">*</span></label>
          <select id="batch_select" class="form-select" disabled>
            <option value="">Select Batch</option>
            {{-- Options will be populated via JS --}}
          </select>
          <div class="err" data-for="batch_id"></div>
        </div>
      </div>

      {{-- Basic Information --}}
      <h3 class="section-title">Basic Information</h3>
      <div class="divider-soft"></div>

      <div class="mb-3">
        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
          <input id="title" class="form-control" type="text" maxlength="255" placeholder="e.g., React Component Design Principles" autocomplete="off">
        </div>
        <div class="err" data-for="title"></div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="slug">Slug</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
          <input id="slug" class="form-control" type="text" maxlength="255" placeholder="Auto-generated from title" autocomplete="off">
        </div>
        <div class="tiny mt-1">Leave blank to auto-generate from title</div>
        <div class="err" data-for="slug"></div>
      </div>

      <div class="mb-3">
        <label class="form-label d-block">Instructions</label>
        <div class="toolbar" aria-label="Instructions toolbar">
          <button class="tool" type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
          <button class="tool" type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
          <button class="tool" type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
          <button class="tool" type="button" data-format="H2">H2</button>
          <button class="tool" type="button" data-format="H3">H3</button>
          <button class="tool" type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
          <button class="tool" type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
          <button class="tool" type="button" id="btnLinkInst"><i class="fa-solid fa-link"></i></button>
          <span class="tiny">Task requirements, guidelines, and expectations</span>
        </div>
        <div class="rte-wrap">
          <div id="instructions" class="rte" contenteditable="true" spellcheck="true"></div>
          <div class="rte-ph">Your task is to design and implement a flexible React component…</div>
        </div>
        <div class="err" data-for="instructions"></div>
      </div>

      {{-- Submission Settings --}}
      <h3 class="section-title">Submission Settings</h3>
      <div class="divider-soft"></div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label" for="status">Status</label>
          <select id="status" class="form-select">
            <option value="published" selected>Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="submission_type">Submission Type</label>
          <select id="submission_type" class="form-select">
            <option value="file" selected>File Upload</option>
            <option value="text">Text Entry</option>
            <option value="link">Link/URL</option>
            <option value="code">Code</option>
            <option value="mixed">Mixed</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="attempts_allowed">Attempts Allowed</label>
          <input id="attempts_allowed" class="form-control" type="number" min="1" value="3" placeholder="3">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="total_marks">Total Marks</label>
          <input id="total_marks" class="form-control" type="number" min="1" placeholder="100">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="pass_marks">Pass Marks</label>
          <input id="pass_marks" class="form-control" type="number" min="0" placeholder="70">
        </div>
      </div>

      {{-- Allowed submission types button --}}
      <div class="mb-3 d-flex align-items-center" style="gap:10px">
        <button type="button" id="btnAllowedTypes" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#allowedTypesModal">
          <i class="fa fa-list-check me-1"></i> Allowed submission types
        </button>
        <div id="selectedTypeWrap" class="selected-types" aria-hidden="false"></div>
      </div>
      <div id="selectedTypeHint" class="tiny mt-1">Select specific submission methods. If empty, all types are allowed.</div>

      {{-- Schedule --}}
      <h3 class="section-title">Schedule</h3>
      <div class="divider-soft"></div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="due_at">Due At</label>
          <input id="due_at" class="form-control" type="datetime-local">
          <div class="err" data-for="due_at"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="end_at">End At</label>
          <input id="end_at" class="form-control" type="datetime-local">
          <div class="tiny mt-1">Final deadline (no submissions after this)</div>
          <div class="err" data-for="end_at"></div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label d-block">Allow Late Submissions</label>
          <div class="toggle-wrap">
            <label class="toggle-switch">
              <input type="checkbox" id="allow_late_submissions">
              <span class="toggle-slider"></span>
            </label>
            <span class="tiny">Accept submissions after due date</span>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="late_penalty">Late Penalty (%)</label>
          <input id="late_penalty" class="form-control" type="number" min="0" max="100" placeholder="10" disabled>
          <div class="tiny mt-1">Percentage deducted per day late</div>
        </div>
      </div>

      {{-- Attachments --}}
      <h3 class="section-title">Attachments</h3>
      <div class="divider-soft"></div>

      <div id="dropzone" class="dropzone" aria-label="Attachments dropzone">
        <div class="drop-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
        <div class="lead fw-semibold">Drag & drop files here</div>
        <div class="tiny mt-1">PDF, DOC, DOCX, ZIP, Images • up to 10 MB each</div>
        <div class="drop-actions">
          <label class="btn btn-outline-primary mb-0" for="attachments">
            <i class="fa fa-file me-1"></i>Choose Files
          </label>
          <input id="attachments" type="file" multiple accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png,.gif" hidden>
          <button type="button" id="btnOpenLibrary" class="btn btn-outline-secondary">
            <i class="fa fa-book me-1"></i>Choose from Library
          </button>
          <button type="button" id="btnClearFiles" class="btn btn-light">Clear All</button>
        </div>
      </div>
      <div id="fileList" class="file-list"></div>
      <div id="libraryList" class="file-list library-list mt-2"></div>
      <div class="err" data-for="attachments"></div>

      {{-- Actions --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <a id="cancel" class="btn btn-light" href="/assignments/manage">Cancel</a>
        <button id="btnSave" class="btn btn-primary" type="button">
          <span class="btn-spinner" aria-hidden="true"></span>
          <span class="btn-label"><i class="fa fa-floppy-disk me-1"></i> <span id="saveBtnText">Create Assignment</span></span>
        </button>
      </div>
    </div>
  </div>

  {{-- toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
    <div id="okToast" class="toast text-bg-success border-0">
      <div class="d-flex"><div id="okMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>
    </div>
    <div id="errToast" class="toast text-bg-danger border-0 mt-2">
      <div class="d-flex"><div id="errMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>
    </div>
  </div>
</div>
<!-- Study Material / Library Modal -->
<div class="modal fade" id="smLibraryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-book me-2"></i>
          Choose from Library
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        {{-- Search bar --}}
        <div class="mb-3 d-flex" style="gap:8px">
          <input id="libSearch" type="search" class="form-control" placeholder="Search by title, file name, type...">
          <button id="btnLibSearch" class="btn btn-outline-primary" type="button">
            <i class="fa fa-magnifying-glass me-1"></i>
          </button>
        </div>
        <div class="tiny mb-2 text-muted">
          Results are filtered per course / batch if selected.
        </div>

        {{-- Results grid --}}
        <div id="libEmpty" class="tiny text-muted mb-2" style="display:none;">No items found.</div>
        <div id="libLoading" class="tiny mb-2" style="display:none;">
          <i class="fa fa-spinner fa-spin me-1"></i>Loading library…
        </div>

        <div id="libGrid" class="row g-3">
          {{-- Cards inserted via JS --}}
        </div>
      </div>

      <div class="modal-footer">
        <span class="tiny me-auto" id="libSelectionInfo"></span>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" id="btnLibAddSelected" class="btn btn-primary">
          <i class="fa fa-plus me-1"></i>Add Selected
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Allowed submission types modal -->
<div class="modal fade" id="allowedTypesModal" tabindex="-1" aria-labelledby="allowedTypesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-list-check me-2"></i> Allowed file types</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="tiny">Select the file types you want to allow for submissions. These will be sent as allowed submission types.</p>

        <div class="type-grid mb-3">
          <!-- Documents -->
          <label class="type-item">
            <input type="checkbox" data-type="pdf"> 
            <i class="fa fa-file-pdf text-danger"></i> 
            <span>PDF (.pdf)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="doc"> 
            <i class="fa fa-file-word text-primary"></i> 
            <span>Word Document (.doc)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="docx"> 
            <i class="fa fa-file-word text-primary"></i> 
            <span>Word Document (.docx)</span>
          </label>
          
          <!-- Archives -->
          <label class="type-item">
            <input type="checkbox" data-type="zip"> 
            <i class="fa fa-file-zipper text-warning"></i> 
            <span>ZIP Archive (.zip)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="rar"> 
            <i class="fa fa-file-zipper text-warning"></i> 
            <span>RAR Archive (.rar)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="7z"> 
            <i class="fa fa-file-zipper text-warning"></i> 
            <span>7-Zip Archive (.7z)</span>
          </label>
          
          <!-- Images -->
          <label class="type-item">
            <input type="checkbox" data-type="jpg"> 
            <i class="fa fa-image text-success"></i> 
            <span>JPEG Image (.jpg)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="jpeg"> 
            <i class="fa fa-image text-success"></i> 
            <span>JPEG Image (.jpeg)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="png"> 
            <i class="fa fa-image text-success"></i> 
            <span>PNG Image (.png)</span>
          </label>
          
          <!-- Other files -->
          <label class="type-item">
            <input type="checkbox" data-type="txt"> 
            <i class="fa fa-file-lines text-secondary"></i> 
            <span>Text File (.txt)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="pptx"> 
            <i class="fa fa-file-powerpoint text-danger"></i> 
            <span>PowerPoint (.pptx)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="xlsx"> 
            <i class="fa fa-file-excel text-success"></i> 
            <span>Excel (.xlsx)</span>
          </label>
          
          <!-- Code files -->
          <label class="type-item">
            <input type="checkbox" data-type="js"> 
            <i class="fa fa-code text-info"></i> 
            <span>JavaScript (.js)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="py"> 
            <i class="fa fa-code text-info"></i> 
            <span>Python (.py)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="java"> 
            <i class="fa fa-code text-info"></i> 
            <span>Java (.java)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="html"> 
            <i class="fa fa-code text-info"></i> 
            <span>HTML (.html)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="css"> 
            <i class="fa fa-code text-info"></i> 
            <span>CSS (.css)</span>
          </label>
          <label class="type-item">
            <input type="checkbox" data-type="cpp"> 
            <i class="fa fa-code text-info"></i> 
            <span>C++ (.cpp)</span>
          </label>
        </div>

        <div class="mb-2">
          <label class="form-label tiny mb-1">Add custom file type</label>
          <div style="display:flex;gap:8px">
            <input id="custom_type" class="form-control form-control-sm" placeholder="e.g., mp4, svg, php" maxlength="10" />
            <button id="btnAddType" class="btn btn-sm btn-outline-primary" type="button">Add</button>
          </div>
          <div class="tiny mt-1">Enter file extension without dot. Will be added to allowed submission types.</div>
        </div>

        <div class="mt-3">
          <label class="form-label tiny mb-1">Selected file types</label>
          <div id="modalSelectedType" style="display:flex;gap:8px;flex-wrap:wrap"></div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" id="btnClearAllTypes" class="btn btn-light">Clear All</button>
        <button type="button" id="btnSaveTypes" class="btn btn-primary" data-bs-dismiss="modal">Save</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  /* ===== helpers & auth ===== */
  const $ = id => document.getElementById(id);
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const okToast  = new bootstrap.Toast($('okToast'));
  const errToast = new bootstrap.Toast($('errToast'));
  const ok  = (m)=>{ $('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ $('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  const backList = '/assignments/manage';
  const API_BASE = '/api/assignments';
  const COURSES_API = '/api/courses';
  const MODULES_API = '/api/course-modules';
  const BATCHES_API  = '/api/batches';
    // Assignment Library API (you implement this in backend)
  const LIBRARY_API = API_BASE; 
  // URLs chosen from library (these will be sent as library_urls[])
  let libraryUrls = [];


  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  // detect Edit mode (?edit=<uuid or id>)
  const url_ = new URL(location.href);
  const editKey = url_.searchParams.get('edit');
  const isEdit = !!editKey;
  let currentUUID = editKey || null;

  if(isEdit){
    $('pageTitle').textContent = 'Edit Assignment';
    $('saveBtnText').textContent = 'Update Assignment';
    $('hint').textContent = '— Update assignment details and settings.';
    loadAssignment(editKey).catch((e)=> {
      console.error(e);
      Swal.fire('Not found','Could not load assignment for editing.','error')
        .then(()=> location.replace(backList));
    });
  }

  /* ===== Courses -> Modules -> Batches cascade ===== */
  const courseSel = $('course_select');
  const moduleSel = $('module_select');
  const batchSel  = $('batch_select');

  async function fetchCourses(){
  courseSel.innerHTML = '<option value="">Loading...</option>';
  try{
    const res = await fetch(COURSES_API + '?per_page=200', { headers:{ 'Authorization':'Bearer '+TOKEN }});
    const json = await res.json().catch(()=> ({}));
    const rows = (json && json.data) ? json.data : (Array.isArray(json) ? json : []);
    courseSel.innerHTML = '<option value="">— Select course —</option>';
    rows.forEach(function(c){
      const opt = document.createElement('option');
      opt.value = (c && c.id) != null ? c.id : '';
      opt.textContent = (c && c.title) != null ? c.title : '';
      courseSel.appendChild(opt);
    });
    // return rows so callers can await and use them
    return rows;
  }catch(e){
    courseSel.innerHTML = '<option value="">— Failed to load —</option>';
    console.error(e);
    // rethrow so caller knows it failed
    throw e;
  }
}

  async function fetchModulesFor(courseId){
    moduleSel.innerHTML = '<option value="">Loading modules...</option>'; moduleSel.disabled = true;
    try{
      const res = await fetch(MODULES_API + '?course_id=' + encodeURIComponent(courseId) + '&per_page=200', { headers:{ 'Authorization':'Bearer '+TOKEN }});
      const json = await res.json().catch(()=> ({}));
      const rows = (json && json.data) ? json.data : (Array.isArray(json) ? json : []);
      moduleSel.innerHTML = '<option value="">— Select module —</option>';
      rows.forEach(function(m){
        const opt = document.createElement('option');
        opt.value = (m && m.id) != null ? m.id : '';
        opt.textContent = (m && m.title) != null ? m.title : '';
        moduleSel.appendChild(opt);
      });
      moduleSel.disabled = false;
    }catch(e){
      moduleSel.innerHTML = '<option value="">— Failed to load —</option>';
      moduleSel.disabled = true;
      console.error(e);
    }
  }

  async function fetchBatchesFor(courseId){
    batchSel.innerHTML = '<option value="">Loading batches...</option>'; batchSel.disabled = true;
    try{
      const res = await fetch(BATCHES_API + '?course_id=' + encodeURIComponent(courseId) + '&per_page=200', { headers:{ 'Authorization':'Bearer '+TOKEN }});
      const json = await res.json().catch(()=> ({}));
      const rows = (json && json.data) ? json.data : (Array.isArray(json) ? json : []);
      batchSel.innerHTML = '<option value="">— Select batch —</option>';
      rows.forEach(function(b){
        const opt = document.createElement('option');
        opt.value = (b && b.id) != null ? b.id : '';
        opt.textContent = (b && (b.badge_title || b.tagline)) ? (b.badge_title || b.tagline) : ('Batch ' + ((b && b.id) ? b.id : ''));
        batchSel.appendChild(opt);
      });
      batchSel.disabled = false;
    }catch(e){
      batchSel.innerHTML = '<option value="">— Failed to load —</option>';
      batchSel.disabled = true;
      console.error(e);
    }
  }

  courseSel.addEventListener('change', async (ev)=>{
    const v = ev.target.value;
    moduleSel.innerHTML = '<option value="">— Select module —</option>'; moduleSel.disabled = true;
    batchSel.innerHTML  = '<option value="">— Select batch —</option>';  batchSel.disabled = true;

    if (!v) return;
    await fetchModulesFor(v);
    await fetchBatchesFor(v);
  });

  // initial load
  fetchCourses();

  /* ===== enable/disable form during save ===== */
  function setFormDisabled(disabled){
    document.querySelectorAll('.card-body input, .card-body select, .card-body button, .card-body .tool')
      .forEach(el=>{
        if (el.id === 'cancel') return;
        if (el.id === 'btnSave') return;
        el.disabled = !!disabled;
      });
  }

  function setSaving(on){
    const btn = $('btnSave');
    btn.classList.toggle('btn-loading', !!on);
    btn.disabled = !!on;
    $('busy').classList.toggle('show', !!on);
    setFormDisabled(!!on);
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

  /* ===== wire RTE ===== */
  function wireRTE(rootId, linkBtnId){
    const el = $(rootId), ph = el.nextElementSibling;
    const hasContent = () => (el.textContent || '').trim().length > 0 || (el.innerHTML||'').trim().length > 0;
    function togglePh(){ el.classList.toggle('has-content', hasContent()); }
    ['input','keyup','paste','blur'].forEach(ev => el.addEventListener(ev, togglePh));
    togglePh();

    const parent = el.closest('.mb-3') || document;
    parent.querySelectorAll('.tool[data-cmd]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand(b.dataset.cmd,false,null); el.focus(); togglePh(); }));
    parent.querySelectorAll('.tool[data-format]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand('formatBlock',false,b.dataset.format); el.focus(); togglePh(); }));
    if(linkBtnId){
      const lb = $(linkBtnId);
      lb && lb.addEventListener('click',()=>{
        const u = prompt('Enter URL (https://…)'); if(u && /^https?:\/\//i.test(u)){ document.execCommand('createLink',false,u); el.focus(); }
      });
    }
  }
  wireRTE('instructions','btnLinkInst');

  /* ===== File handling ===== */
    const drop = $('dropzone');
  const fileInput = $('attachments');
  const fileList = $('fileList');
  const libraryList = $('libraryList');           // NEW
  const btnClearFiles = $('btnClearFiles');
  const btnOpenLibrary = $('btnOpenLibrary');     // NEW
  let selectedFiles = [];
    function renderLibraryList(){
  // guard if element is missing
  if (!libraryList) return;

  libraryList.innerHTML = '';
  if (!libraryUrls || libraryUrls.length === 0) {
    const empty = document.createElement('div');
    empty.className = 'tiny text-muted';
    empty.textContent = 'No files chosen from library.';
    libraryList.appendChild(empty);
    return;
  }

  libraryUrls.forEach((url, idx) => {
    const item = document.createElement('div');
    item.className = 'file-item library-item';
    item.innerHTML = `
      <div class="file-info">
        <i class="fa-solid fa-book-open-reader"></i>
        <span class="file-name" title="${url}">${url}</span>
        <span class="file-size badge bg-secondary ms-2">Library</span>
      </div>
      <span class="file-remove" data-idx="${idx}" title="Remove from library selection">
        <i class="fa-solid fa-xmark"></i>
      </span>
    `;
    libraryList.appendChild(item);
  });

  libraryList.querySelectorAll('.file-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      const idx = parseInt(btn.dataset.idx);
      libraryUrls.splice(idx, 1);
      renderLibraryList();
    });
  });
}

  function formatFileSize(bytes){
    if(bytes < 1024) return bytes + ' B';
    if(bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
  }

  function renderFileList(){
    fileList.innerHTML = '';
    selectedFiles.forEach((f, idx) => {
      const item = document.createElement('div');
      item.className = 'file-item';
      item.innerHTML = `
        <div class="file-info">
          <i class="fa-solid fa-file"></i>
          <span class="file-name" title="${f.name}">${f.name}</span>
          <span class="file-size">${formatFileSize(f.size)}</span>
        </div>
        <span class="file-remove" data-idx="${idx}"><i class="fa-solid fa-xmark"></i></span>
      `;
      fileList.appendChild(item);
    });

    // Wire remove buttons
    fileList.querySelectorAll('.file-remove').forEach(btn => {
      btn.addEventListener('click', ()=>{
        const idx = parseInt(btn.dataset.idx);
        selectedFiles.splice(idx, 1);
        renderFileList();
      });
    });
  }

  function addFiles(files){
    Array.from(files).forEach(f => {
      if(f.size > 10*1024*1024){ err(`File ${f.name} exceeds 10MB`); return; }
      selectedFiles.push(f);
    });
    renderFileList();
  }

  ;['dragenter','dragover'].forEach(ev=>{
    drop.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); drop.classList.add('dragover'); });
  });
  ;['dragleave','dragend','drop'].forEach(ev=>{
    drop.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); drop.classList.remove('dragover'); });
  });
  drop.addEventListener('drop', e=>{
    const files = e.dataTransfer && e.dataTransfer.files;
    if(files) addFiles(files);
  });

  fileInput.addEventListener('change', ()=>{
    if(fileInput.files) addFiles(fileInput.files);
    fileInput.value = '';
  });

    btnClearFiles.addEventListener('click', ()=>{
    selectedFiles = [];
    renderFileList();

    // also clear library selection if you want
    libraryUrls = [];
    renderLibraryList();
  });


  /* ===== Late submission toggle ===== */
  const allowLate = $('allow_late_submissions');
  const latePenalty = $('late_penalty');
  function syncLatePenalty(){
    latePenalty.disabled = !allowLate.checked;
    if(!allowLate.checked) latePenalty.value = '';
  }
  allowLate.addEventListener('change', syncLatePenalty);
  syncLatePenalty();
  
// modal elements (assume they exist in DOM)
const libModal = document.getElementById('smLibraryModal');
const libGrid = $('libGrid');           // container where cards go
const libEmpty = $('libEmpty');         // "no items" element
const libLoading = $('libLoading');     // loader element
const libSearch = $('libSearch');       // search input inside modal
const btnLibSearch = $('btnLibSearch');
const btnLibAddSelected = $('btnLibAddSelected');
const libSelectionInfo = $('libSelectionInfo');

let libItems = [];              // raw items fetched from server (assignments)
let libSelectedUrls = new Set(); // selected attachment URLs (unique keys)
const libModalInstance = libModal ? new bootstrap.Modal(libModal) : null;

function updateLibSelectionInfo() {
  const count = libSelectedUrls.size;
  libSelectionInfo.textContent = count ? `${count} item${count>1?'s':''} selected` : 'No items selected';
}

function extOf(u){ try { return (u||'').split('?')[0].split('.').pop().toLowerCase(); } catch(e){ return ''; } }
function isImageExt(e){ return ['png','jpg','jpeg','gif','webp','avif','svg'].includes(e); }
function escapeHtml(s){ return String(s||'').replace(/[&<>"'`=\/]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60','=':'&#x3D;'}[ch])); }

// Normalize attachment entry to { url, name, mime, size, ext }
function normalizeAttach(a){
  if(!a) return null;
  if(typeof a === 'string') {
    const url = a;
    const name = url.split('/').pop() || url;
    const ext = extOf(url);
    return { url, name, mime:'', size:0, ext };
  }
  // object-ish
  const url = a.signed_url || a.url || a.path || a.file_url || a.storage_url || a.path_with_namespace || null;
  if(!url) return null;
  const name = a.name || a.label || a.original_name || (url.split('/').pop()) || 'file';
  const mime = a.mime || a.content_type || a.contentType || '';
  const size = a.size || a.filesize || 0;
  const ext = (a.ext || a.extension || extOf(url)).toLowerCase();
  return { url, name, mime, size, ext, raw: a };
}

// Render the grid of cards in the modal
function renderLibGrid() {
  if (!libGrid) return;
  libGrid.innerHTML = '';
  libEmpty.style.display = libItems.length ? 'none' : 'block';

  if (!libItems.length) { 
    updateLibSelectionInfo(); 
    return; 
  }

  const rowFrag = document.createDocumentFragment();

  libItems.forEach((doc, idx) => {
    // doc: { key, url, name, refs, ext, isImage, size, mime }
    const idKey = doc.key; // normalized key (url w/o query)
    const cardCol = document.createElement('div');
    cardCol.className = 'col-md-4 mb-3';

    const card = document.createElement('div');
    card.className = 'card h-100 lib-card';

    const thumbHtml = doc.isImage && doc.url
      ? `<img src="${escapeHtml(doc.url)}" alt="${escapeHtml(doc.name)}" class="img-fluid rounded" style="max-height:140px;object-fit:cover;">`
      : `<div class="lib-icon d-flex align-items-center justify-content-center" style="height:140px;">
           <i class="fa fa-file fa-2x text-muted"></i>
         </div>`;

    card.innerHTML = `
      <!-- Top-left overlay checkbox -->
      <div class="lib-overlay-check">
        <div class="form-check form-check-sm m-0">
          <input 
            class="form-check-input lib-select" 
            type="checkbox" 
            data-url="${escapeHtml(doc.url)}" 
            id="lib-${idx}">
        </div>
      </div>

      <div class="card-img-top lib-thumb text-center p-3">
        ${thumbHtml}
      </div>

      <div class="card-body d-flex flex-column">
        <h6 class="card-title text-truncate" title="${escapeHtml(doc.name)}">
          ${escapeHtml(doc.name)}
        </h6>
        <p class="card-text tiny text-muted mb-1">
          ${escapeHtml(doc.mime || ('File .' + (doc.ext || '')))}
        </p>
        <p class="card-text tiny text-muted mb-2">
          ${doc.size ? formatFileSize(doc.size) : ''}
        </p>

        <div class="mt-auto d-flex justify-content-start align-items-center">
  ${
    doc.url 
      ? `<a 
           href="${escapeHtml(doc.url)}" 
           target="_blank" 
           rel="noopener" 
           class="btn btn-sm btn-outline-primary tiny d-inline-flex align-items-center px-2 py-1"
           style="font-size:12px;"
         >
           <i class="fa fa-arrow-up-right-from-square me-1"></i>
           Preview
         </a>`
      : ''
  }
</div>

      </div>
    `;

    const checkbox = card.querySelector('.lib-select');

    // Pre-check if already selected
    if (libSelectedUrls.has(idKey) || libSelectedUrls.has(doc.url)) {
      checkbox.checked = true;
    }

    // Checkbox change → update selection set
    checkbox.addEventListener('change', (e) => {
      const theUrl = doc.url;
      const key = doc.key;
      if (e.target.checked) {
        libSelectedUrls.add(key);
        libSelectedUrls.add(theUrl);
      } else {
        libSelectedUrls.delete(key);
        libSelectedUrls.delete(theUrl);
      }
      updateLibSelectionInfo();
    });

    // Clicking card (but not preview or checkbox) toggles checkbox
    card.addEventListener('click', (ev) => {
      if (ev.target.closest('.lib-select') || ev.target.closest('a')) return;
      checkbox.checked = !checkbox.checked;
      checkbox.dispatchEvent(new Event('change'));
    });

    cardCol.appendChild(card);
    rowFrag.appendChild(cardCol);
  });

  libGrid.appendChild(rowFrag);
  updateLibSelectionInfo();
}

// Fetch library items from assignments index and normalize into deduped docs
async function fetchLibraryItems(query = '') {
  if(!LIBRARY_API) return;
  libLoading.style.display = 'block';
  libEmpty.style.display = 'none';
  libGrid.innerHTML = '';
  libItems = [];
  libSelectedUrls = new Set();
  updateLibSelectionInfo();

  try {
    const params = new URLSearchParams();
    params.append('per_page','200');
    const cid = courseSel.value;
    const bid = batchSel.value;
    if(cid) params.append('course_id', cid);
    if(bid) params.append('batch_id', bid);
    if(query) params.append('q', query);

    const url = LIBRARY_API + (params.toString() ? ('?' + params.toString()) : '');
    console.log('[LIBRARY] Fetching assignments for library:', url);

    const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' }});
    const text = await res.text();
    let json = null;
    try { json = text ? JSON.parse(text) : null; } catch(e) { console.warn('[LIBRARY] non-json response', e); }

    if(!res.ok) {
      const msg = (json && (json.message || json.error)) || ('HTTP ' + res.status);
      err('Library error: ' + msg);
      libItems = [];
      renderLibGrid();
      return;
    }

    // normalize list shape
    let rows = [];
    if (Array.isArray(json)) rows = json;
    else if (json && Array.isArray(json.data)) rows = json.data;
    else if (json && json.data && Array.isArray(json.data.data)) rows = json.data.data;
    else if (json && Array.isArray(json.items)) rows = json.items;
    else if (json && Array.isArray(json.rows)) rows = json.rows;
    else rows = [];

    // build docMap deduped by url (strip query)
    const docMap = new Map();
    rows.forEach(row => {
      // determine attachments in row: try multiple fields
      const rawAtts = row.attachments || row.attachment || row.files || row.resources || [];
      const atts = Array.isArray(rawAtts) ? rawAtts : (rawAtts ? [rawAtts] : []);

      atts.forEach(a => {
        const n = normalizeAttach(a);
        if(!n || !n.url) return;
        const key = n.url.split('?')[0];
        if(!key) return;
        const assignmentTitle = row.title || row.name || row.quiz_name || ('Assignment ' + (row.id || ''));
        if(!docMap.has(key)) {
          docMap.set(key, {
            key,
            url: n.url,
            name: n.name || (n.url.split('/').pop() || 'file'),
            mime: n.mime || '',
            size: n.size || 0,
            ext: n.ext || extOf(n.url),
            isImage: isImageExt(n.ext || extOf(n.url)),
            refs: [assignmentTitle],
            searchText: ((assignmentTitle||'') + ' ' + (n.name||'') + ' ' + (n.url||'')).toLowerCase()
          });
        } else {
          const entry = docMap.get(key);
          if(!entry.refs.includes(assignmentTitle)) {
            entry.refs.push(assignmentTitle);
            entry.searchText += ' ' + assignmentTitle;
          }
        }
      });
    });

    libItems = Array.from(docMap.values());
    renderLibGrid();
  } catch (e) {
    console.error('Library fetch failed', e);
    libItems = [];
    renderLibGrid();
  } finally {
    libLoading.style.display = 'none';
  }
}

// Open library modal handler (wires initial state)
if(btnOpenLibrary){
  btnOpenLibrary.addEventListener('click', () => {
    if(!libModalInstance) return;
    // preselect urls already in libraryUrls
    libSelectedUrls.clear();
    (libraryUrls || []).forEach(u => {
      if(u) libSelectedUrls.add( (String(u)).split('?')[0] );
      libSelectedUrls.add(String(u));
    });
    libSearch.value = '';
    updateLibSelectionInfo();
    libModalInstance.show();
    fetchLibraryItems('');
  });
}

// Search wiring
if(btnLibSearch) btnLibSearch.addEventListener('click', ()=> fetchLibraryItems((libSearch.value||'').trim()));
if(libSearch) libSearch.addEventListener('keypress', (e)=> { if(e.key==='Enter'){ e.preventDefault(); fetchLibraryItems((libSearch.value||'').trim()); } });

// Confirm selected → add selected URLs to libraryUrls
if(btnLibAddSelected){
  btnLibAddSelected.addEventListener('click', () => {
    const chosen = [];
    libItems.forEach(item => {
      const key = item.key;
      if(libSelectedUrls.has(key) || libSelectedUrls.has(item.url)) chosen.push(item.url);
    });
    // merge & dedupe
    const merged = new Set([...(libraryUrls || []), ...chosen]);
    libraryUrls = Array.from(merged);
    renderLibraryList();
    if(libModalInstance) libModalInstance.hide();
  });
}

  /* ===== Auto-slug generation ===== */
  const titleInput = $('title');
  const slugInput = $('slug');
  function slugify(s){
    return String(s || '').toLowerCase()
      .replace(/\s+/g,'-')
      .replace(/[^\w\-]+/g,'')
      .replace(/\-\-+/g,'-')
      .replace(/^-+|-+$/g,'')
      .slice(0,140);
  }
  titleInput.addEventListener('blur', ()=> {
    if (!slugInput.value.trim()) slugInput.value = slugify(titleInput.value || '');
  });

  /* ===== errors ===== */
  function fErr(field,msg){ const el=document.querySelector(`.err[data-for="${field}"]`); if(el){ el.textContent=msg||''; el.style.display=msg?'block':'none'; } }
  function clrErr(){ document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }

  /* ===== Allowed submission types logic ===== */
  let allowedSubmissionTypes = []; // e.g. ['pdf', 'doc', 'jpg']

  const modalEl = document.getElementById('allowedTypesModal');
  const modalSelectedEl = $('modalSelectedType');
  const selectedWrap = $('selectedTypeWrap');
  const selectedHint = $('selectedTypeHint');

  // icon mapping for file types with colors
  const typeIconMap = {
    pdf: 'fa fa-file-pdf text-danger',
    doc: 'fa fa-file-word text-primary',
    docx: 'fa fa-file-word text-primary',
    zip: 'fa fa-file-zipper text-warning',
    rar: 'fa fa-file-zipper text-warning',
    '7z': 'fa fa-file-zipper text-warning',
    jpg: 'fa fa-image text-success',
    jpeg: 'fa fa-image text-success',
    png: 'fa fa-image text-success',
    txt: 'fa fa-file-lines text-secondary',
    pptx: 'fa fa-file-powerpoint text-danger',
    xlsx: 'fa fa-file-excel text-success',
    js: 'fa fa-code text-info',
    py: 'fa fa-code text-info',
    java: 'fa fa-code text-info',
    html: 'fa fa-code text-info',
    css: 'fa fa-code text-info',
    cpp: 'fa fa-code text-info'
  };

  // type display names
  const typeDisplayMap = {
    pdf: 'PDF',
    doc: 'Word (.doc)',
    docx: 'Word (.docx)',
    zip: 'ZIP',
    rar: 'RAR',
    '7z': '7-Zip',
    jpg: 'JPEG',
    jpeg: 'JPEG',
    png: 'PNG',
    txt: 'Text',
    pptx: 'PowerPoint',
    xlsx: 'Excel',
    js: 'JavaScript',
    py: 'Python',
    java: 'Java',
    html: 'HTML',
    css: 'CSS',
    cpp: 'C++'
  };

  // fill modal selection from allowedSubmissionTypes on open
  modalEl && modalEl.addEventListener('show.bs.modal', function () {
    const checkboxes = modalEl.querySelectorAll('input[type="checkbox"][data-type]');
    checkboxes.forEach(cb => {
      const type = (cb.dataset.type || '').toLowerCase();
      cb.checked = allowedSubmissionTypes.includes(type);
    });
    renderModalSelected();
  });

  function renderModalSelected(){
    modalSelectedEl.innerHTML = '';
    allowedSubmissionTypes.forEach(function(type){
      const div = document.createElement('div'); 
      div.className='type-chip';
      
      const icon = document.createElement('i'); 
      icon.className = (typeIconMap[type] || 'fa fa-file text-secondary') + ' fa-fw';
      div.appendChild(icon);
      
      const txt = document.createElement('span'); 
      txt.textContent = typeDisplayMap[type] || `.${type}`;
      div.appendChild(txt);
      
      modalSelectedEl.appendChild(div);
    });
  }

  function renderSelectedChips(){
    selectedWrap.innerHTML = '';
    if(allowedSubmissionTypes.length === 0){
      selectedHint.style.display = 'block';
      return;
    }
    selectedHint.style.display = 'none';
    
    allowedSubmissionTypes.forEach(function(type, idx){
      const chip = document.createElement('div'); 
      chip.className='type-chip';
      
      const icon = document.createElement('i'); 
      icon.className = (typeIconMap[type] || 'fa fa-file text-secondary') + ' fa-fw';
      chip.appendChild(icon);
      
      const strong = document.createElement('strong'); 
      strong.textContent = typeDisplayMap[type] || `.${type}`;
      chip.appendChild(strong);
      
      const rem = document.createElement('span'); 
      rem.className='remove-type'; 
      rem.dataset.idx = idx; 
      rem.innerHTML='&times;';
      rem.addEventListener('click', function(){
        allowedSubmissionTypes.splice(idx,1);
        
        // Also uncheck the corresponding checkbox in modal if it exists
        const existingCheckbox = modalEl.querySelector(`input[data-type="${type}"]`);
        if(existingCheckbox) {
          existingCheckbox.checked = false;
        }
        
        renderModalSelected();
        renderSelectedChips();
      });
      chip.appendChild(rem);
      selectedWrap.appendChild(chip);
    });
  }

  // modal checkboxes toggle
  modalEl && modalEl.addEventListener('click', function(ev){
    const target = ev.target;
    if(target && target.matches('input[type="checkbox"][data-type]')){
      const type = (target.dataset.type||'').toLowerCase();
      if(target.checked){
        if(!allowedSubmissionTypes.includes(type)) allowedSubmissionTypes.push(type);
      } else {
        allowedSubmissionTypes = allowedSubmissionTypes.filter(t=> t !== type);
      }
      renderModalSelected();
      renderSelectedChips();
    }
  });

  // add custom type
  $('btnAddType').addEventListener('click', function(){
    const val = ( $('custom_type').value || '' ).trim().toLowerCase();
    if(!val){ 
      err('Enter a file type'); 
      return; 
    }
    if(!/^[a-z0-9_]{1,10}$/.test(val)){ 
      err('Invalid file type (use letters, numbers or underscore, no dot)'); 
      return; 
    }
    
    // Check if already exists
    if(!allowedSubmissionTypes.includes(val)) {
      allowedSubmissionTypes.push(val);
      
      // Also check the corresponding checkbox in modal if it exists
      const existingCheckbox = modalEl.querySelector(`input[data-type="${val}"]`);
      if(existingCheckbox) {
        existingCheckbox.checked = true;
      }
      
      $('custom_type').value = '';
      renderModalSelected();
      renderSelectedChips();
    } else {
      err('This file type is already added');
    }
  });

  // Also allow pressing Enter in the custom type input
  $('custom_type').addEventListener('keypress', function(e){
    if(e.key === 'Enter') {
      e.preventDefault();
      $('btnAddType').click();
    }
  });

  // clear all in modal
  $('btnClearAllTypes').addEventListener('click', function(){
    allowedSubmissionTypes = [];
    // uncheck checkboxes
    modalEl.querySelectorAll('input[type="checkbox"][data-type]').forEach(cb => cb.checked = false);
    renderModalSelected();
    renderSelectedChips();
  });

  // save modal (close triggers with data-bs-dismiss already)
  $('btnSaveTypes').addEventListener('click', function(){
    // normalize unique
    allowedSubmissionTypes = Array.from(new Set(allowedSubmissionTypes.map(t=> t.toLowerCase())));
    renderSelectedChips();
  });

  // init UI
  renderSelectedChips();
  renderLibraryList();
  function numOrNull(val){
  if(val === undefined || val === null) return null;
  const s = String(val).trim();
  if(s === '') return null;      // IMPORTANT: don’t send empty string
  const n = Number(s);
  return Number.isFinite(n) ? n : null;
}

  /* ===== payload builder ===== */
  function buildPayload(){
    return {
      course_id: courseSel.value || null,
      course_module_id: moduleSel.value || null,
      batch_id: batchSel.value || null,
      title: ($('title').value||'').trim(),
      slug: ($('slug').value||'').trim() || null,
      instructions: ($('instructions').innerHTML||'').trim() || null,
      status: $('status').value,
      submission_type: $('submission_type').value,
      allowed_submission_types: allowedSubmissionTypes,
      attempts_allowed: Number($('attempts_allowed').value||0) || null,
      total_marks: numOrNull($('total_marks').value),
pass_marks:  numOrNull($('pass_marks').value),

      due_at: $('due_at').value || null,
      end_at: $('end_at').value || null,
      allow_late_submissions: allowLate.checked ? 1 : 0,
      late_penalty: allowLate.checked ? (Number($('late_penalty').value||0) || null) : null,
       library_urls: libraryUrls   
    };
  }
// robust loadAssignment that waits for courses and selects course reliably
async function loadAssignment(key){
  const busyEl = $('busy');
  if(busyEl) busyEl.classList.add('show');
  try{
    // Try multiple possible endpoints for assignment
    const endpoints = [
      `/api/assignments/${encodeURIComponent(key)}`,
      `/api/assignments/show/${encodeURIComponent(key)}`,
      `/api/assignments/${encodeURIComponent(key)}/edit`
    ];

    let res = null, json = null;
    for (const ep of endpoints) {
      try {
        res = await fetch(ep, { headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }});
        const text = await res.text().catch(()=>null);
        json = text ? JSON.parse(text) : null;
        if(res && res.ok) break;
      } catch(innerErr){
        res = null; json = null;
      }
    }

    if(!res || !res.ok){
      const message = (json && (json.message || json.error)) || 'Not found';
      throw new Error('Load failed: ' + message);
    }

    const a = json?.data || json;
    if(!a) throw new Error('Assignment not found');

    currentUUID = a.uuid || a.id || key;

    // 1) Ensure course options are loaded
    try {
      // If you have an ensureCoursesLoaded wrapper, use it; otherwise call fetchCourses()
      if(typeof ensureCoursesLoaded === 'function'){
        await ensureCoursesLoaded();
      } else {
        await fetchCourses();
      }
    } catch(e){
      // courses failed to load — we'll still attempt to set course and create option if needed
      console.warn('Warning: fetchCourses failed while loading assignment', e);
    }

    // Helper: try to pick the best candidate option for the course
    function pickCourseOption(courseIdCandidates = [], courseTitleCandidate){
      if(!courseSel) return null;
      // 1) exact match on option.value
      for(const cand of courseIdCandidates){
        if(cand == null) continue;
        const opt = Array.from(courseSel.options).find(o => String(o.value) === String(cand));
        if(opt) return opt;
      }
      // 2) match data-uuid attribute (some options may have data-uuid)
      for(const cand of courseIdCandidates){
        if(cand == null) continue;
        const opt = Array.from(courseSel.options).find(o => (o.dataset && o.dataset.uuid) && String(o.dataset.uuid) === String(cand));
        if(opt) return opt;
      }
      // 3) match by visible text/title (fallback)
      if(courseTitleCandidate){
        const opt = Array.from(courseSel.options).find(o => (o.textContent||'').trim() === (courseTitleCandidate||'').trim());
        if(opt) return opt;
      }
      return null;
    }

    // Build possible ids to try. The assignment payload might use course_id (id), course_uuid, or nested objects.
    const courseIdCandidates = [];
    if(a.course_id !== undefined && a.course_id !== null) courseIdCandidates.push(a.course_id);
    if(a.course_uuid !== undefined && a.course_uuid !== null) courseIdCandidates.push(a.course_uuid);
    if(a.course && (a.course.id || a.course.uuid)) {
      if(a.course.id) courseIdCandidates.push(a.course.id);
      if(a.course.uuid) courseIdCandidates.push(a.course.uuid);
    }
    // also try the currentUUID if that's actually the course id in some apps
    courseIdCandidates.push(a.id || a.uuid || null);

    const courseTitleCandidate = a.course_title || a.course_name || a.course?.title || a.course?.name || a.course_name || a.title;

    let picked = pickCourseOption(courseIdCandidates, courseTitleCandidate);

    // If nothing matched, attempt loose matching by checking numeric/string equality
    if(!picked){
      // attempt more aggressive matching: compare without type strictness
      const vals = Array.from(courseSel.options).map(o => ({opt:o, v:String(o.value), uuid:(o.dataset && o.dataset.uuid? String(o.dataset.uuid): null), txt:(o.textContent||'').trim()}));
      for(const cand of courseIdCandidates){
        if(!cand) continue;
        const sc = String(cand);
        const found = vals.find(x => x.v === sc || x.uuid === sc);
        if(found){ picked = found.opt; break; }
      }
    }

    // If still nothing — insert a new option so select shows something useful (helps when backend returns id not present in list)
    if(!picked){
      const newOpt = document.createElement('option');
      // prefer human-readable label
      const label = courseTitleCandidate || (`Course ${courseIdCandidates.find(Boolean) || key}`);
      // set value preferring numeric id if present, otherwise UUID or first candidate
      const v = (a.course_id != null) ? a.course_id : (a.course?.id != null ? a.course.id : (a.course_uuid || a.course?.uuid || courseIdCandidates.find(Boolean) || ''));
      newOpt.value = v;
      newOpt.textContent = label;
      // if we have uuid, set data-uuid to help future matches
      if(a.course_uuid) newOpt.dataset.uuid = a.course_uuid;
      // append and pick
      courseSel.appendChild(newOpt);
      picked = newOpt;
    }

    // finally set the select to the chosen option value
    if(picked){
      courseSel.value = picked.value;
    }

    // Trigger cascade: fetch modules & batches for the selected course
    const cidToUse = courseSel.value || (a.course_id || a.course?.id || a.course_uuid || a.course?.uuid || null);
    if(cidToUse){
      await fetchModulesFor(cidToUse);
      await fetchBatchesFor(cidToUse);
      // now set module & batch values (they should exist after the fetch)
      if(a.course_module_id && moduleSel) moduleSel.value = a.course_module_id;
      if(a.batch_id && batchSel) batchSel.value = a.batch_id;
      // sometimes backend keeps module/batch as nested objects:
      if(!moduleSel.value && a.course_module && a.course_module.id) moduleSel.value = a.course_module.id;
      if(!batchSel.value && a.batch && a.batch.id) batchSel.value = a.batch.id;
    }

    // fill other fields
    if($('title')) $('title').value = a.title || '';
    if($('slug')) $('slug').value = a.slug || '';
    if($('instructions')) $('instructions').innerHTML = a.instructions || '';
    if($('status')) $('status').value = a.status || 'published';
    if($('submission_type')) $('submission_type').value = a.submission_type || 'file';
    if($('attempts_allowed')) $('attempts_allowed').value = a.attempts_allowed || 3;
    if($('total_marks')) $('total_marks').value = a.total_marks || '';
    if($('pass_marks')) $('pass_marks').value = a.pass_marks || '';

    if(a.allowed_submission_types && Array.isArray(a.allowed_submission_types)) {
      allowedSubmissionTypes = [...a.allowed_submission_types];
      renderSelectedChips();
    }

    if(a.due_at && $('due_at')) { const d = new Date(a.due_at); if(!isNaN(d)) $('due_at').value = d.toISOString().slice(0,16); }
    if(a.end_at && $('end_at')) { const d = new Date(a.end_at); if(!isNaN(d)) $('end_at').value = d.toISOString().slice(0,16); }

    if(allowLate && typeof a.allow_late_submissions !== 'undefined') allowLate.checked = !!a.allow_late_submissions;
    if($('late_penalty')) $('late_penalty').value = a.late_penalty || '';
    syncLatePenalty();

    // library attachments normalization
    if(a.library_urls && Array.isArray(a.library_urls) && a.library_urls.length){
      libraryUrls = Array.from(new Set(a.library_urls));
      renderLibraryList();
    } else if(a.attachments || a.attachment){
      libraryUrls = [];
      const raw = a.attachments || a.attachment || [];
      const arr = Array.isArray(raw) ? raw : (raw ? [raw] : []);
      arr.forEach(t => { const n = normalizeAttach(t); if(n && n.url) libraryUrls.push(n.url); });
      libraryUrls = Array.from(new Set(libraryUrls));
      renderLibraryList();
    }

    // update RTE visuals
    document.querySelectorAll('.rte-ph').forEach(ph => {
      const editor = ph.previousElementSibling;
      if(!editor) return;
      const hasContent = (editor.textContent || '').trim().length > 0 || (editor.innerHTML||'').trim().length > 0;
      editor.classList.toggle('has-content', hasContent);
    });

  }catch(e){
    console.error(e);
    throw e;
  }finally{
    if(busyEl) busyEl.classList.remove('show');
  }
}

  /* ===== submit ===== */
  $('btnSave').addEventListener('click', async ()=>{
    clrErr();
    
    // Validate required fields
    const title = ($('title').value||'').trim();
    if(!title){ fErr('title','Title is required.'); $('title').focus(); return; }

    const courseId = courseSel.value;
    if(!courseId){ fErr('course_id','Course is required.'); courseSel.focus(); return; }

    const courseModuleId = moduleSel.value;
    if(!courseModuleId){ fErr('course_module_id','Course module is required.'); moduleSel.focus(); return; }

    const batchId = batchSel.value;
    if(!batchId){ fErr('batch_id','Batch is required.'); batchSel.focus(); return; }

    setSaving(true);
    try{
      let res, json;

      // Use course-specific endpoint for creating assignments
      const url = isEdit 
        ? `/api/assignments/${encodeURIComponent(currentUUID)}`
        : `/api/courses/${encodeURIComponent(courseId)}/assignments`;
        
      const method = isEdit ? 'PUT' : 'POST';

      // If we have files, use multipart; otherwise JSON
      if(selectedFiles.length > 0){
        const fd = new FormData();
        const payload = buildPayload();
        Object.entries(payload).forEach(([k,v])=> { 
          if(v!==undefined && v!==null) {
            // Handle arrays properly for FormData
            if(Array.isArray(v)) {
              v.forEach(item => {
                fd.append(k + '[]', item);
              });
            } else {
              fd.append(k, v);
            }
          }
        });
        
        selectedFiles.forEach(f => fd.append('attachments[]', f));

        res = await fetch(url, {
          method: method,
          headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' },
          body: fd
        });
      }else{
        // JSON only
        const payload = buildPayload();
        res = await fetch(url, {
          method: method,
          headers:{ 
            'Authorization':'Bearer '+TOKEN, 
            'Accept':'application/json', 
            'Content-Type':'application/json' 
          },
          body: JSON.stringify(payload)
        });
      }

      json = await res.json().catch(()=> ({}));

      if(res.ok){
        ok(isEdit ? 'Assignment updated successfully' : 'Assignment created successfully');
        setTimeout(()=> location.replace(backList), 800);
        return;
      }

      if(res.status===422){
        const e = json.errors || json.fields || {};
        Object.entries(e).forEach(([k,v])=> fErr(k, Array.isArray(v)? v[0] : String(v)));
        err(json.message || 'Please fix the highlighted fields.');
        return;
      }

      if(res.status===403){
        Swal.fire({icon:'error',title:'Unauthorized',html:'Token/role lacks permission for this endpoint.'});
        return;
      }

      Swal.fire(isEdit ? 'Update failed' : 'Save failed', json.message || ('HTTP '+res.status), 'error');
    }catch(ex){
      console.error(ex);
      Swal.fire('Network error','Please check your connection and try again.','error');
    }finally{
      setSaving(false);
    }
  });
})();
</script>
@endpush