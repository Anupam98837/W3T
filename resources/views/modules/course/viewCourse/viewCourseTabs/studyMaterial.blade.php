{{-- resources/views/StudyMaterial.blade.php --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ... (your CSS unchanged) ... */
.sm-list{max-width:1100px;margin:18px auto}
.sm-card{border-radius:12px;padding:18px}
.sm-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-radius:10px;border:1px solid var(--line-strong);}
.sm-item+.sm-item{margin-top:10px}
.sm-item .left{display:flex;gap:12px;align-items:center}
.sm-item .meta{display:flex;flex-direction:column;gap:4px}
.sm-item .meta .title{font-weight:700;color:var(--ink);font-family:var(--font-head)}
.sm-item .meta .sub{color:var(--muted-color);font-size:13px}
.sm-item .btn{padding:6px 10px;border-radius:8px;font-size:13px}
.sm-empty{border:1px dashed var(--line-strong);border-radius:12px;padding:18px;background:transparent;color:var(--muted-color);text-align:center}
.sm-loader{display:flex;align-items:center;gap:8px;color:var(--muted-color)}
.duration-pill{font-size:12px;color:var(--muted-color);background:transparent;border-radius:999px;padding:4px 8px;border:1px solid var(--line-strong)}
.sm-fullscreen{position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:2147483647;display:flex;align-items:center;justify-content:center;padding:18px}
.sm-fullscreen .fs-inner{width:100%;height:100%;max-width:1400px;max-height:92vh;background:#fff;border-radius:8px;overflow:hidden;display:flex;flex-direction:column}
.sm-fullscreen .fs-header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid rgba(0,0,0,0.06);background:#fafafa}
.sm-fullscreen .fs-title{font-weight:700;font-size:16px;color:#111}
.sm-fullscreen .fs-close{border:0;background:transparent;font-size:18px;cursor:pointer;padding:6px 10px}
.sm-fullscreen .fs-body{flex:1;display:flex;align-items:center;justify-content:center;padding:10px;background:#fff}
.sm-fullscreen iframe,.sm-fullscreen img,.sm-fullscreen video{width:100%;height:100%;object-fit:contain;border:0}
.sm-more{position:relative;display:inline-block}
.sm-more .sm-dd-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);padding:6px 8px;border-radius:10px;cursor:pointer;font-size:var(--fs-14)}
.sm-more .sm-dd{position:absolute;top:calc(100% + 6px);right:0;min-width:160px;background:var(--surface);border:1px solid var(--line-strong);box-shadow:var(--shadow-2);border-radius:10px;overflow:hidden;display:none;z-index:1000;padding:6px 0}
.sm-more .sm-dd.show{display:block}
.sm-more .sm-dd a,.sm-more .sm-dd button.dropdown-item{display:flex;align-items:center;gap:10px;padding:10px 12px;text-decoration:none;color:inherit;cursor:pointer;background:transparent;border:0;width:100%;text-align:left;font-size:14px}
.sm-more .sm-dd a:hover,.sm-more .sm-dd button.dropdown-item:hover{background:color-mix(in oklab,var(--muted-color) 6%,transparent)}
.sm-more .sm-dd .divider{height:1px;background:var(--line-strong);margin:6px 0}
.sm-more .sm-dd i{width:18px;text-align:center;font-size:14px}
.sm-icon-purple{color:#6f42c1}
.sm-icon-red{color:#dc3545}
.sm-icon-black{color:#111}
.sm-more .sm-dd a.text-danger,.sm-more .sm-dd button.dropdown-item.text-danger{color:var(--danger-color,#dc2626)!important}
@media(max-width:720px){.sm-item{flex-direction:column;align-items:flex-start}.sm-item .right{width:100%;display:flex;justify-content:flex-end;gap:8px}.sm-more .sm-dd{right:6px;left:auto;min-width:160px}}
/* Make modal body scrollable and fit inside viewport */
.modal.show .modal-dialog {
  max-height: calc(100vh - 48px); /* room for header + footer + margin */
}
.modal.show .modal-content {
  height: 100%;
  display: flex;
  flex-direction: column;
}
.modal.show .modal-body {
  overflow: auto;
  /* subtract header/footer heights — adjust if your header/footer are taller */
  max-height: calc(100vh - 200px);
  -webkit-overflow-scrolling: touch; /* smooth scrolling on iOS */
}

#sm_existing_attachments .btn { padding: 6px 8px; font-size: 13px; }
#sm_existing_attachments .small.text-primary { text-decoration: underline; cursor: pointer; }
/* StudyMaterial dropzone */
.sm-dropzone{
  border:2px dashed rgba(0,0,0,0.08);
  border-radius:12px;
  background: rgba(0,0,0,0.01);
  padding:18px;
  text-align:center;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:8px;
  transition:border-color .12s ease, box-shadow .12s ease, background .12s ease;
  min-height:110px;
}
.sm-dropzone.dragover{ border-color: rgba(111,66,193,0.9); box-shadow:0 8px 20px rgba(111,66,193,0.06); background: rgba(111,66,193,0.03); }
.sm-drop-icon{ width:48px; height:48px; border-radius:50%; border:1px dotted rgba(111,66,193,0.25); display:flex; align-items:center; justify-content:center; font-size:18px; color:#6f42c1; background: rgba(255,255,255,0.6); }
.sm-drop-lead{ font-size:16px; color:var(--ink); margin-top:4px;}
.sm-drop-tiny{ font-size:13px; color:var(--muted-color); }
.sm-drop-actions{ display:flex; gap:10px; align-items:center; justify-content:center; margin-top:6px; }
.sm-choose-btn{ background:#6f42c1; color:#fff; padding:8px 12px; border-radius:8px; font-weight:600; }
.sm-clear-btn{ background:transparent; border:1px solid rgba(0,0,0,0.04); color:var(--muted-color); padding:8px 12px; border-radius:8px; }

.sm-file-list{ width:100%; max-width:880px; display:flex; flex-direction:column; gap:8px; align-items:center; }
.sm-file-item{ width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 12px; border-radius:8px; background:#fff; border:1px solid rgba(0,0,0,0.04); font-size:14px; color:#222; }
.sm-file-item .meta{ display:flex; gap:12px; align-items:center; min-width:0; }
.sm-file-item .name{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0; font-weight:600;}
.sm-file-item .size{ color:#777; font-size:13px; white-space:nowrap; }
.sm-file-remove{ color:#c5308d; cursor:pointer; padding:6px; border-radius:6px; background:transparent; border:0; }

</style>

<div class="crs-wrap">
  <div class="panel sm-card rounded-1 shadow-1" style="padding:18px;">
    <div class="d-flex align-items-center w-100">
  <h2 class="panel-title d-flex align-items-center gap-2 mb-0">
<i class="fa fa-book" style="color: var(--primary-color);"></i>
    Study Material
  </h2>

  <!-- BIN BUTTON pushed to the end -->
  <button id="btn-bin" class="btn btn-light text-danger ms-auto" title="Bin / Deleted Items">
    <i class="fa fa-trash text-danger"></i> Bin
  </button>
</div>


    <div class="panel-head w-100 mt-3">
      <div class="container-fluid px-0">

        <!-- Border Box -->
        <div class="p-3 border rounded-3" >

          <div class="row g-3 align-items-center">

            <!-- Search -->
            <div class="col-md-5 col-lg-4">
              <div class="input-group">
                <span class="input-group-text">
                  <i class="fa fa-search text-muted"></i>
                </span>
                <input id="sm-search" type="text" class="form-control" placeholder="Search study materials...">
              </div>
            </div>

            <!-- Sort + Refresh -->
            <div class="col-md-4 col-lg-4 d-flex align-items-center gap-2">
              <select id="sm-sort" class="form-select">
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
            <!-- Bin + Upload -->
            <div class="col-md-2 col-lg-4 d-flex justify-content-end">
              <!-- Upload visible only for admin (JS will show/hide) -->
              <button id="btn-upload" class="btn btn-primary" style="display:none;"
                      data-bs-toggle="modal" data-bs-target="#createSmModal">
                + Study Material
              </button>

              
            </div>

          </div>

        </div>
        <!-- End Border Box -->

      </div>
    </div>

    <div style="margin-top:14px;">
      <div id="sm-loader" class="sm-loader" style="display:none;">
        <div class="spin" aria-hidden="true"></div>
        <div class="text-muted">Loading study materials…</div>
      </div>

      <div id="sm-empty" class="sm-empty" style="display:none;">
        <div style="font-weight:600; margin-bottom:6px;">No study materials yet</div>
        <div class="text-muted small">Materials uploaded by instructors will appear here.</div>
      </div>

      <div id="sm-items" style="display:none; margin-top:8px;">
        <!-- items inserted by JS -->
      </div>
    </div>
  </div>
</div>

<!-- Create Study Material Modal -->
<div class="modal fade" id="createSmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Study Material Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="smCreateForm" class="needs-validation" novalidate>
        <div class="modal-body">
          <div id="smCreateAlert" style="display:none;" class="alert alert-danger small"></div>

          <div class="row g-3">
            <div class="col-12">
              <div id="smContextInfo" class="small text-muted mb-2">Adding to: <span id="smContextText">—</span></div>
              <div id="smContextError" class="alert alert-warning small" style="display:none;">
                Unable to detect the target Batch for this page. Make sure this page is opened from a batch context.
              </div>
            </div>
            <!-- hidden fields for edit mode -->
            <input type="hidden" id="sm_id" name="id" value="">
            <input type="hidden" id="sm__method" name="_method" value="">

            <div class="col-12">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input id="sm_title" name="title" type="text" class="form-control" maxlength="255" required>
              <div class="invalid-feedback">Title required.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea id="sm_description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">View policy</label>
              <select id="sm_view_policy" name="view_policy" class="form-select">
                <option value="inline_only" selected>Inline only (no download)</option>
                <option value="downloadable">Downloadable</option>
              </select>
            </div>

            <!-- existing attachments placeholder (for edit mode) -->
            <div class="col-12" id="sm_existing_attachments_wrap" style="display:none;">
              <label class="form-label">Existing attachments</label>
              <div id="sm_existing_attachments" class="small text-muted" style="padding:8px; border:1px dashed var(--line-strong); border-radius:6px; "></div>
              <div class="small text-muted mt-1">Select files to remove before updating (server-side removal).</div>
            </div>

            <div class="col-12">
              <label class="form-label">Attachments <span class="text-danger">*</span> <small class="text-muted">(multiple allowed, max 50MB each)</small></label>
              <div id="sm_dropzone" class="sm-dropzone" aria-label="Attachments dropzone">
  <div class="sm-drop-icon"><i class="fa fa-upload"></i></div>
  <div class="sm-drop-lead" style="font-weight:600">Drag &amp; drop files here or click to upload</div>
  <div class="sm-drop-tiny">Any format • up to 50 MB per file</div>

  <div class="sm-drop-actions" style="margin-top:8px;">
    <label for="sm_attachments" id="sm_choose_label" class="sm-choose-btn" style="cursor:pointer; display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px;">
      <i class="fa fa-file-upload"></i>
      <span>Choose Files</span>
    </label>
    <!-- <button type="button" id="sm_choose_library" class="sm-choose-library btn btn-outline-secondary" style="display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px;">
      <i class="fa fa-book"></i> <span>Choose from Library</span>
    </button> -->
    <button type="button" id="sm_clear_files" class="sm-clear-btn btn btn-light">Clear All</button>
  </div>

  <!-- native input kept but hidden (server wiring intact) -->
  <input id="sm_attachments" name="attachments[]" type="file" multiple style="display:none;" />

  <!-- file list rendered here (scoped to modal) -->
 
</div>
 <div id="sm_fileList" class="sm-file-list" aria-live="polite" style="margin-top:12px;"></div>
<div class="small text-muted mt-1">Supported: images, pdf, video, etc.</div>

            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button id="smCreateSubmit" type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Create
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- DIFF Confirm Modal (place near existing modals) -->
<div class="modal fade" id="smDiffModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="fa fa-code-merge me-2"></i> Confirm changes</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body" id="smDiffBody" style="font-family: monospace; font-size:13px;">
<!-- diff rows inserted by JS -->
</div>
<div class="modal-footer">
<button type="button" id="smDiffCancel" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
<button type="button" id="smDiffConfirm" class="btn btn-primary">Confirm & Continue</button>
</div>
</div>
</div>
</div>
<!-- Study Material Library Modal -->
<div class="modal fade" id="smLibraryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-book me-2"></i>Choose from Library</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="smLibraryNotice" class="small text-muted mb-2">Select files from this course's study material library. Selected items are URL-based links (no file upload).</div>
        <div id="smLibraryLoader" style="display:none;" class="d-flex align-items-center gap-2"><div class="spin" aria-hidden="true"></div><div>Loading library…</div></div>
        <div id="smLibraryEmpty" style="display:none;" class="text-muted">No library items found for this course.</div>
        <div id="smLibraryList" class="list-group" style="max-height:50vh; overflow:auto;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" id="smLibraryCancel" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="smLibraryConfirm" class="btn btn-primary">Add selected</button>
      </div>
    </div>
  </div>
</div>

{{-- Details Modal (shows study material metadata — opens when user clicks View in ⋮ menu) --}}
<div id="details-modal" class="modal" style="display:none;" aria-hidden="true">
  <div class="modal-dialog" style="max-width:560px; margin:80px auto;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Study Material Details</h5>
        <button type="button" id="details-close" class="btn btn-light">Close</button>
      </div>
      <div class="modal-body" id="details-body" style="padding:18px;">
        <!-- Filled by JS -->
      </div>
      <div class="modal-footer" id="details-footer" style="display:none;">
        <!-- optional actions injected by JS (Edit for admins) -->
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  // const role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    Swal.fire({ icon: 'warning', title: 'Login required', text: 'Please sign in to continue.', allowOutsideClick: false })
      .then(()=>{ window.location.href = '/'; });
    return;
  }

  // ===== In-memory role (cache + async event), SAME as chat =====
  let role = '';
  const getRoleNow = () => {
    if (window.__AUTH_CACHE__ && typeof window.__AUTH_CACHE__.role === 'string') {
      return window.__AUTH_CACHE__.role;
    }
    return '';
  };
  role = String(getRoleNow() || '').trim().toLowerCase();

  const isAdmin      = role.includes('admin')|| role.includes('superadmin');
  const isSuperAdmin = role.includes('super_admin') || role.includes('superadmin');
  const isInstructor = role.includes('instructor');

  const canCreate = isAdmin || isSuperAdmin || isInstructor;
  const canEdit   = isAdmin || isSuperAdmin || isInstructor;
  const canDelete = isAdmin || isSuperAdmin || isInstructor;
  const canViewBin = isAdmin || isSuperAdmin;

  const apiBase = '/api/study-materials';
  const defaultHeaders = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };

  // DOM refs
  const $loader = document.getElementById('sm-loader');
  const $empty  = document.getElementById('sm-empty');
  const $items  = document.getElementById('sm-items');
  const $search = document.getElementById('sm-search');
  const $sort   = document.getElementById('sm-sort');
  const $refresh = document.getElementById('btn-refresh');
  const $uploadBtn = document.getElementById('btn-upload');
  const $btnBin = document.getElementById('btn-bin');

  const detailsModal = document.getElementById('details-modal');
  const detailsBody = document.getElementById('details-body');
  const detailsClose = document.getElementById('details-close');
  const detailsFooter = document.getElementById('details-footer');

  const createModalEl = document.getElementById('createSmModal');

  const smIdInput = document.getElementById('sm_id');
  const smMethodInput = document.getElementById('sm__method');
  const smTitleInput = document.getElementById('sm_title');
  const smDescInput = document.getElementById('sm_description');
  const smViewPolicy = document.getElementById('sm_view_policy');
  const smAttachmentsInput = document.getElementById('sm_attachments');
  const smExistingWrap = document.getElementById('sm_existing_attachments_wrap');
  const smExistingList = document.getElementById('sm_existing_attachments');
  const smCreateSubmitBtn = document.getElementById('smCreateSubmit');
  const smCreateFormEl = document.getElementById('smCreateForm');

  // ---------------------
  // URL / page context extraction
  // ---------------------
  const deriveCourseKey = () => {
    const parts = location.pathname.split('/').filter(Boolean);
    const last = parts.at(-1)?.toLowerCase();
    if (last === 'view' && parts.length >= 2) return parts.at(-2);
    return parts.at(-1);
  };

  /* ---------------------
   * Module UUID from URL (FIXED + ROBUST)
   * - supports module_uuid, module, module_id, course_module_id, etc.
   * - URL ALWAYS WINS over dataset defaults
   * - also supports UUID in path (/modules/{uuid}) as fallback
   * --------------------- */
  const deriveModuleUuid = () => {
    try {
      const url = new URL(window.location.href);

      const candidates = [
        'module_uuid',
        'module',
        'moduleId',
        'module_id',
        'course_module_uuid',
        'course_module_id',
        'mid',
        'm'
      ];

      for (const key of candidates) {
        const v = url.searchParams.get(key);
        if (v && String(v).trim() !== '') return String(v).trim();
      }

      // fallback: UUID inside path like /modules/<uuid> or /module/<uuid>
      const uuidRe = /[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i;
      const parts = url.pathname.split('/').filter(Boolean);

      const modulesIdx = parts.findIndex(p => ['module','modules'].includes(String(p).toLowerCase()));
      if (modulesIdx !== -1 && parts[modulesIdx + 1] && uuidRe.test(parts[modulesIdx + 1])) {
        return parts[modulesIdx + 1];
      }

      const any = parts.find(p => uuidRe.test(p));
      if (any) return any;

      // fallback: UUID in hash
      const hash = (url.hash || '').replace('#','');
      if (hash && uuidRe.test(hash)) return hash;

      return null;
    } catch (e) {
      return null;
    }
  };

  function getQueryParam(name) {
    try { return (new URL(window.location.href)).searchParams.get(name); } catch(e) { return null; }
  }

  /* ---------------------
   * Sync context into DOM (FIXED)
   * - If module exists in URL, override dataset (prevents "first module default" bug)
   * --------------------- */
  (function ensureBatchInDomFromUrl() {
    const host = document.querySelector('.crs-wrap');
    if (!host) return;

    const existing = host.dataset.batchId ?? host.dataset.batch_id ?? '';
    if (!existing || String(existing).trim() === '') {
      const pathKey = deriveCourseKey();
      if (pathKey) {
        host.dataset.batchId = String(pathKey);
        host.dataset.batch_id = String(pathKey);
      }
    }

    const qModule = deriveModuleUuid();
    if (qModule) {
      host.dataset.moduleId = String(qModule);
      host.dataset.module_id = String(qModule);
    }
  })();

  /* ---------------------
   * readContext (FIXED)
   * - URL FIRST, then dataset fallback
   * - keep dataset in sync
   * --------------------- */
  function readContext() {
    const host = document.querySelector('.crs-wrap');

    const batchId =
      host?.dataset.batchId ||
      host?.dataset.batch_id ||
      deriveCourseKey() ||
      null;

    const moduleFromUrl = deriveModuleUuid();
    const moduleId =
      (moduleFromUrl && String(moduleFromUrl).trim() !== '' ? moduleFromUrl : null) ||
      host?.dataset.moduleId ||
      host?.dataset.module_id ||
      null;

    if (host && moduleId) {
      host.dataset.moduleId = String(moduleId);
      host.dataset.module_id = String(moduleId);
    }

    return {
      batch_id: batchId ? String(batchId) : null,
      module_id: moduleId ? String(moduleId) : null
    };
  }

  function waitForModuleContext(timeout = 1200) {
    return new Promise((resolve) => {
      const start = Date.now();
      (function check() {
        // always resync from URL
        try {
          const host = document.querySelector('.crs-wrap');
          const qModule = deriveModuleUuid();
          if (host && qModule) {
            host.dataset.moduleId = String(qModule);
            host.dataset.module_id = String(qModule);
          }
        } catch(e){}

        const ctx = readContext();
        if (ctx && ctx.module_id) return resolve(ctx);

        if (Date.now() - start > timeout) return resolve(null);
        requestAnimationFrame(check);
      })();
    });
  }

  // ---------------------
  // small UI helpers
  // ---------------------
  function showOk(msg){
    Swal.fire({ toast:true, position:'top-end', icon:'success', title: msg || 'Done', showConfirmButton:false, timer:2500, timerProgressBar:true });
  }
  function showErr(msg){
    Swal.fire({ toast:true, position:'top-end', icon:'error', title: msg || 'Something went wrong', showConfirmButton:false, timer:3500, timerProgressBar:true });
  }

  function showLoader(v){ if ($loader) $loader.style.display = v ? 'flex' : 'none'; }
  function showEmpty(v){ if ($empty) $empty.style.display = v ? 'block' : 'none'; }
  function showItems(v){ if ($items) $items.style.display = v ? 'block' : 'none'; }

  // STRICT module from URL-first context
  function getStrictModuleFromContext() {
    const fromUrl = deriveModuleUuid();
    if (fromUrl && String(fromUrl).trim() !== '') return String(fromUrl).trim();

    const ctx = readContext();
    if (!ctx || !ctx.module_id) return null;
    return String(ctx.module_id).trim();
  }

  function formatSize(bytes){
    if (bytes == null) return '';
    const units = ['B','KB','MB','GB'];
    let i = 0; let b = Number(bytes);
    while(b >= 1024 && i < units.length-1){ b /= 1024; i++; }
    return `${b.toFixed(b<10 && i>0 ? 1 : 0)} ${units[i]}`;
  }

  function escapeHtml(str){ return String(str || '').replace(/[&<>"'`=\/]/g, s => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60","=":"&#x3D;"}[s])); }

  async function apiFetch(url, opts = {}) {
    opts.headers = Object.assign({}, opts.headers || {}, defaultHeaders);
    const res = await fetch(url, opts);
    if (res.status === 401) {
      try {
        await Swal.fire({ icon: 'warning', title: 'Session expired', text: 'Please login again.', allowOutsideClick: false, allowEscapeKey: false });
      } catch(e){}
      location.href = '/';
      throw new Error('Unauthorized');
    }
    return res;
  }

  function closeAllDropdowns(){ document.querySelectorAll('.sm-more .sm-dd.show').forEach(d => { d.classList.remove('show'); d.setAttribute('aria-hidden', 'true'); }); }
  document.addEventListener('click', () => closeAllDropdowns());
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAllDropdowns(); });

  function normalizeAttachments(row) {
    let raw = row.attachment ?? row.attachments ?? [];
    if (typeof raw === 'string' && raw.trim() !== '') {
      try {
        raw = JSON.parse(raw);
      } catch (e) {
        const possibleUrls = raw.split(/\s*,\s*|\s*\|\|\s*/).filter(Boolean);
        if (possibleUrls.length) raw = possibleUrls;
        else raw = [];
      }
    }
    if (raw && !Array.isArray(raw)) raw = [raw];

    const arr = (raw || []).map((a, idx) => {
      if (typeof a === 'string') {
        const url = a;
        const ext = (url.split('?')[0].split('.').pop() || '').toLowerCase();
        return { id: `s-${idx}`, url, path: url, name: url.split('/').pop(), mime: '', ext };
      }
      const url = a.signed_url || a.url || a.path || a.file_url || a.storage_url || null;
      const name = a.name || a.label || (url ? url.split('/').pop() : (a.original_name || `file-${idx}`));
      const mime = a.mime || a.content_type || a.contentType || '';
      let ext = (a.ext || a.extension || '').toLowerCase();
      if (!ext && url) ext = (url.split('?')[0].split('.').pop() || '').toLowerCase();
      return {
        id: a.id || a.attachment_id || a.file_id || a.storage_key || (`o-${idx}`),
        url,
        signed_url: a.signed_url,
        path: a.path,
        name,
        mime,
        ext,
        size: a.size || a.file_size || a.filesize || 0,
        raw: a
      };
    });

    return arr.filter(it => it && (it.url || it.path || it.signed_url));
  }

  // ---------------------
  // Preview helpers for library URLs
  // ---------------------
  function normalizeAttachmentForPreview(a){
    if (!a) return null;
    if (typeof a === 'string') return { url: a, name: (a||'').split('/').pop() || a };
    const url = a.signed_url || a.url || a.path || null;
    if (!url) return null;
    return { url, name: a.name || a.original_name || url.split('/').pop(), mime: a.mime || a.content_type || '' };
  }
  function openAttachmentPreview(attachment, title){
    if (!attachment || !attachment.url) { Swal.fire({ icon:'info', title:'No preview', text:'Unable to preview this item.' }); return; }
    try { openFullscreenPreview({ title: title || (attachment.name || '') }, [attachment], 0); } catch(e) { console.error('Preview open failed', e); Swal.fire({ icon:'error', title:'Preview failed' }); }
  }

  // ---------------------
  // UI builders
  // ---------------------
  function createItemRow(row) {
    const attachments = normalizeAttachments(row);
    row.attachment = attachments;
    if (typeof row.attachment_count === 'undefined') row.attachment_count = attachments.length;

    const wrapper = document.createElement('div'); wrapper.className = 'sm-item';

    const left = document.createElement('div'); left.className = 'left';
    const icon = document.createElement('div'); icon.className = 'icon';
    icon.style.width = '44px'; icon.style.height = '44px'; icon.style.borderRadius = '10px';
    icon.style.display = 'flex'; icon.style.alignItems = 'center'; icon.style.justifyContent = 'center';
    icon.style.border = '1px solid var(--line-strong)';
    icon.style.background = 'linear-gradient(180deg, rgba(0,0,0,0.02), transparent)';
    icon.innerHTML = '<i class="fa fa-file" style="color:var(--secondary-color)"></i>';

    const meta = document.createElement('div'); meta.className = 'meta';
    const title = document.createElement('div'); title.className = 'title'; title.textContent = row.title || 'Untitled';

    const sub = document.createElement('div'); sub.className = 'sub';
    sub.textContent = row.description ? row.description : (row.attachment_count ? `${row.attachment_count} attachment(s)` : '—');

    const creatorInfo = document.createElement('div');
    creatorInfo.className = 'creator-info';
    creatorInfo.style.fontSize = '12px';
    creatorInfo.style.color = 'var(--muted-color)';
    creatorInfo.style.marginTop = '4px';
    creatorInfo.style.display = 'flex';
    creatorInfo.style.alignItems = 'center';
    creatorInfo.style.gap = '6px';

    creatorInfo.innerHTML = `
      <i class="fa fa-user" style="font-size:10px;"></i>
      <span>${escapeHtml(row.created_by_name || 'Unknown')}</span>
    `;

    meta.appendChild(title);
    meta.appendChild(sub);
    meta.appendChild(creatorInfo);

    left.appendChild(icon);
    left.appendChild(meta);

    const right = document.createElement('div'); right.className = 'right';
    right.style.display = 'flex'; right.style.alignItems = 'center'; right.style.gap = '8px';
    const datePill = document.createElement('div'); datePill.className = 'duration-pill';
    datePill.textContent = row.created_at ? new Date(row.created_at).toLocaleDateString() : '';
    right.appendChild(datePill);

    const previewBtn = document.createElement('button');
    previewBtn.className = 'btn btn-outline-primary'; previewBtn.style.minWidth = '80px';
    previewBtn.textContent = 'Preview'; previewBtn.type = 'button';
    if (Array.isArray(row.attachment) && row.attachment.length > 0) {
      previewBtn.addEventListener('click', () => openFullscreenPreview(row, row.attachment || [], 0));
      if (row.attachment.length > 1) {
        const badge = document.createElement('span');
        badge.className = 'small text-muted';
        badge.style.marginLeft = '6px';
        badge.textContent = `(${row.attachment.length})`;
        previewBtn.appendChild(badge);
      }
    } else {
      previewBtn.disabled = true; previewBtn.classList.add('btn-light');
    }
    right.appendChild(previewBtn);

    const moreWrap = document.createElement('div'); moreWrap.className = 'sm-more';
    moreWrap.innerHTML = `
      <button class="sm-dd-btn" aria-haspopup="true" aria-expanded="false" title="More">⋮</button>
      <div class="sm-dd" role="menu" aria-hidden="true">
        <a href="#" data-action="view"><i class="fa fa-eye sm-icon-purple"></i><span>View</span></a>
        ${canEdit ? `<a href="#" data-action="edit"><i class="fa fa-pen sm-icon-black"></i><span>Edit</span></a>` : ''}
        ${canDelete ? `<div class="divider"></div><a href="#" data-action="delete" class="text-danger"><i class="fa fa-trash sm-icon-red"></i><span>Delete</span></a>` : ''}
      </div>
    `;
    right.appendChild(moreWrap);
    wrapper.appendChild(left); wrapper.appendChild(right);

    const ddBtn = moreWrap.querySelector('.sm-dd-btn');
    const dd = moreWrap.querySelector('.sm-dd');
    if (ddBtn && dd) {
      ddBtn.addEventListener('click', (ev) => {
        ev.stopPropagation();
        const isOpen = dd.classList.contains('show');
        closeAllDropdowns();
        if (!isOpen) { dd.classList.add('show'); dd.setAttribute('aria-hidden', 'false'); ddBtn.setAttribute('aria-expanded','true'); }
      });
    }

    const viewBtn = moreWrap.querySelector('[data-action="view"]');
    if (viewBtn) viewBtn.addEventListener('click', (ev) => { ev.preventDefault(); ev.stopPropagation(); openDetailsModal(row); closeAllDropdowns(); });

    const editBtn = moreWrap.querySelector('[data-action="edit"]');
    if (editBtn) editBtn.addEventListener('click', (ev) => { ev.preventDefault(); ev.stopPropagation(); enterEditMode(row); closeAllDropdowns(); });

    const delBtn = moreWrap.querySelector('[data-action="delete"]');
    if (delBtn) {
      delBtn.addEventListener('click', async (ev) => {
        ev.preventDefault(); ev.stopPropagation();
        const r = await Swal.fire({
          title: 'Move to Bin?',
          text: `Move "${row.title || 'this item'}" to Bin?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, move it',
          cancelButtonText: 'Cancel'
        });
        if (!r.isConfirmed) { closeAllDropdowns(); return; }

        try {
          const res = await apiFetch(`${apiBase}/${encodeURIComponent(row.id)}`, { method: 'DELETE' });
          if (!res.ok) throw new Error('Delete failed: ' + res.status);
          showOk('Moved to Bin');
          await loadMaterials();
        } catch (e) { console.error(e); showErr('Delete failed'); }
        finally { closeAllDropdowns(); }
      });
    }

    return wrapper;
  }

  // details modal
  function openDetailsModal(row) {
    if (!detailsModal) return;
    detailsModal.style.display = 'block';
    detailsModal.classList.add('show');
    detailsModal.setAttribute('aria-hidden', 'false');

    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'detailsBackdrop';
    document.body.appendChild(backdrop);

    document.body.classList.add('modal-open');
    backdrop.addEventListener('click', closeDetailsModal);

    const attachments = row.attachment && Array.isArray(row.attachment) ? row.attachment : [];

    let attachList = '';
    if (!attachments.length) {
      attachList = '<div style="color:var(--muted-color)">No attachments</div>';
    } else {
      attachList = attachments.map((a, idx) => {
        const name = a.name || (a.url || a.path || '').split('/').pop() || `file-${idx+1}`;
        const size = a.size ? ` (${formatSize(a.size)})` : '';
        const type = escapeHtml(a.mime || a.ext || '');
        return `
          <div class="sm-attach-row" style="display:flex; justify-content:space-between; gap:8px; align-items:center; padding:6px 0; border-bottom:1px solid rgba(0,0,0,0.04);">
            <div style="min-width:0;">
              <a href="#" class="sm-attach-link" data-idx="${idx}" style="font-weight:600; text-decoration:none;">${escapeHtml(name)}${escapeHtml(size)}</a>
              <div class="small text-muted" style="margin-top:4px;">${type}</div>
            </div>
            <div style="flex:0 0 auto;">
              <button class="btn btn-sm btn-outline-primary sm-attach-preview" data-idx="${idx}" type="button">Preview</button>
            </div>
          </div>
        `;
      }).join('');
    }

    if (detailsBody) {
      detailsBody.innerHTML = `
        <div style="display:flex; flex-direction:column; gap:12px; font-size:15px;">
          <div><strong>Title:</strong> ${escapeHtml(row.title || "Untitled")}</div>
          <div><strong>Description:</strong> ${escapeHtml(row.description || "—")}</div>
          <div><strong>Created At:</strong> ${row.created_at ? new Date(row.created_at).toLocaleString() : "—"}</div>
          <div><strong>Created By:</strong> ${escapeHtml(row.creator_name || row.created_by_name || "—")}</div>
          <div><strong>Attachments:</strong> ${attachments.length} file(s)</div>
          <div style="margin-top:6px;">${attachList}</div>
          <div style="color:var(--muted-color); font-size:13px; margin-top:6px;"><strong>ID:</strong> ${escapeHtml(String(row.id || ""))}</div>
        </div>
      `;

      const links = detailsBody.querySelectorAll(".sm-attach-link, .sm-attach-preview");
      links.forEach(el => {
        el.addEventListener("click", (ev) => {
          ev.preventDefault();
          const idx = Number(el.dataset.idx);
          try { closeDetailsModal(); } catch(e){}
          openFullscreenPreview(row, attachments, idx);
        });
      });
    }

    if (detailsFooter) {
      detailsFooter.innerHTML = '';
      const close = document.createElement('button'); close.className = 'btn btn-light'; close.textContent = 'Close';
      close.addEventListener('click', closeDetailsModal); detailsFooter.appendChild(close);
      if (canCreate || canEdit) {
        const edit = document.createElement('button'); edit.className = 'btn btn-primary'; edit.textContent = 'Edit';
        edit.addEventListener('click', () => { enterEditMode(row); closeDetailsModal(); });
        detailsFooter.appendChild(edit);
      }
    }
  }

  function closeDetailsModal() {
    if (!detailsModal) return;
    detailsModal.classList.remove('show');
    detailsModal.style.display = 'none';
    detailsModal.setAttribute('aria-hidden', 'true');
    const bd = document.getElementById('detailsBackdrop'); if (bd) bd.remove();
    document.body.classList.remove('modal-open');
    if (detailsBody) detailsBody.innerHTML = '';
    if (detailsFooter) detailsFooter.innerHTML = '';
  }
  if (detailsClose) detailsClose.addEventListener('click', closeDetailsModal);

  // ---------------------
  // Fullscreen preview helpers
  // ---------------------
  function closeFullscreenPreview() {
    const existing = document.querySelector('.sm-fullscreen');
    if (!existing) return;
    const blobUrl = existing.dataset.blobUrl;
    if (blobUrl) try { URL.revokeObjectURL(blobUrl); } catch(e){}
    document.removeEventListener('contextmenu', suppressContextMenuWhileOverlay);
    existing.remove();
    document.documentElement.style.overflow = ''; document.body.style.overflow = '';
  }
  function suppressContextMenuWhileOverlay(e){ e.preventDefault(); }

  async function fetchProtectedBlob(url) {
    const res = await apiFetch(url, { method: 'GET', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': '*/*' } });
    if (!res.ok) throw new Error('Failed to fetch file: ' + res.status);
    return await res.blob();
  }

  async function isUrlPublic(url) {
    try {
      const res = await fetch(url, { method: 'HEAD', mode: 'cors' });
      return res.ok;
    } catch (e) {
      try {
        const res2 = await fetch(url, { method: 'GET', headers: { 'Range': 'bytes=0-0' }, mode: 'cors' });
        return res2.ok;
      } catch (err) { return false; }
    }
  }

  async function openFullscreenPreview(row, attachments = [], startIndex = 0) {
    closeFullscreenPreview();
    if (!Array.isArray(attachments) || attachments.length === 0) return;

    attachments = attachments.map(a => (typeof a === 'string' ? { url: a } : a || {}));
    let currentIndex = Math.max(0, Math.min(startIndex || 0, attachments.length - 1));
    const wrap = document.createElement('div');
    wrap.className = 'sm-fullscreen';
    wrap.setAttribute('role', 'dialog'); wrap.setAttribute('aria-modal', 'true');
    wrap.dataset.blobUrl = '';
    wrap.dataset.currentIndex = String(currentIndex);

    const inner = document.createElement('div'); inner.className = 'fs-inner';
    const header = document.createElement('div'); header.className = 'fs-header';
    const title = document.createElement('div'); title.className = 'fs-title'; title.textContent = row.title || '';
    const controls = document.createElement('div'); controls.style.display = 'flex'; controls.style.alignItems = 'center'; controls.style.gap = '8px';

    const prevBtn = document.createElement('button'); prevBtn.className = 'fs-close btn btn-sm'; prevBtn.type = 'button'; prevBtn.title = 'Previous'; prevBtn.innerHTML = '◀';
    const nextBtn = document.createElement('button'); nextBtn.className = 'fs-close btn btn-sm'; nextBtn.type = 'button'; nextBtn.title = 'Next'; nextBtn.innerHTML = '▶';
    const idxIndicator = document.createElement('div'); idxIndicator.style.fontSize = '13px'; idxIndicator.style.color = 'var(--muted-color)';
    idxIndicator.textContent = `${currentIndex + 1} / ${attachments.length}`;

    const closeBtn = document.createElement('button'); closeBtn.className = 'fs-close'; closeBtn.innerHTML = '✕'; closeBtn.setAttribute('aria-label','Close preview');

    controls.appendChild(prevBtn);
    controls.appendChild(idxIndicator);
    controls.appendChild(nextBtn);
    header.appendChild(title);
    header.appendChild(controls);
    header.appendChild(closeBtn);

    const body = document.createElement('div'); body.className = 'fs-body';
    inner.appendChild(header); inner.appendChild(body); wrap.appendChild(inner); document.body.appendChild(wrap);
    document.documentElement.style.overflow = 'hidden'; document.body.style.overflow = 'hidden';
    document.addEventListener('contextmenu', suppressContextMenuWhileOverlay);

    async function renderAt(index) {
      index = Math.max(0, Math.min(index, attachments.length - 1));
      currentIndex = index;
      wrap.dataset.currentIndex = index;
      idxIndicator.textContent = `${currentIndex + 1} / ${attachments.length}`;

      const prevBlob = wrap.dataset.blobUrl;
      if (prevBlob) { try { URL.revokeObjectURL(prevBlob); } catch(e){} wrap.dataset.blobUrl = ''; }

      const attachment = attachments[currentIndex] || {};
      const urlCandidate = attachment.signed_url || attachment.url || attachment.path || null;
      const mime = (attachment.mime || '') ;
      const ext = ((attachment.ext || '')).toLowerCase();
      body.innerHTML = '';

      try {
        if (urlCandidate) {
          const publicOk = await isUrlPublic(urlCandidate);
          if (publicOk) {
            if (mime.startsWith('image/') || ['png','jpg','jpeg','gif','webp'].includes(ext)) {
              const img = document.createElement('img'); img.src = urlCandidate; img.alt = attachment.name || row.title || 'image'; img.style.maxWidth = '100%'; img.style.maxHeight = '100%'; img.style.objectFit = 'contain';
              body.appendChild(img); return;
            }
            if (mime === 'application/pdf' || ext === 'pdf') {
              const iframe = document.createElement('iframe'); iframe.src = urlCandidate + (urlCandidate.indexOf('#') === -1 ? '#toolbar=0&navpanes=0&scrollbar=0' : '&toolbar=0&navpanes=0&scrollbar=0'); iframe.setAttribute('aria-label', row.title || 'PDF preview'); iframe.style.width='100%'; iframe.style.height='100%';
              body.appendChild(iframe); return;
            }
            if (mime.startsWith('video/') || ['mp4','webm','ogg'].includes(ext)) {
              const v = document.createElement('video'); v.controls = true; v.style.width = '100%'; const s = document.createElement('source'); s.src = urlCandidate; s.type = mime || 'video/mp4'; v.appendChild(s); body.appendChild(v); return;
            }
            const iframe = document.createElement('iframe'); iframe.src = urlCandidate; iframe.setAttribute('aria-label', row.title || 'Preview'); iframe.style.width='100%'; iframe.style.height='100%';
            body.appendChild(iframe); return;
          }
        }

        if (!urlCandidate) {
          body.innerHTML = '<div>No preview available for this file.</div>'; return;
        }

        const blob = await fetchProtectedBlob(urlCandidate);
        const blobUrl = URL.createObjectURL(blob);
        wrap.dataset.blobUrl = blobUrl;

        if (mime.startsWith('image/') || ['png','jpg','jpeg','gif','webp'].includes(ext)) {
          const img = document.createElement('img'); img.src = blobUrl; img.alt = attachment.name || row.title || 'image'; img.style.maxWidth='100%'; img.style.maxHeight='100%'; img.style.objectFit='contain';
          body.appendChild(img); return;
        }
        if (mime === 'application/pdf' || ext === 'pdf') {
          const iframe = document.createElement('iframe'); iframe.src = blobUrl + '#toolbar=0&navpanes=0&scrollbar=0'; iframe.setAttribute('aria-label', row.title || 'PDF preview'); iframe.style.width='100%'; iframe.style.height='100%';
          body.appendChild(iframe); return;
        }
        if (mime.startsWith('video/') || ['mp4','webm','ogg'].includes(ext)) {
          const v = document.createElement('video'); v.controls = true; v.style.width='100%'; v.src = blobUrl; body.appendChild(v); return;
        }
        const iframe = document.createElement('iframe'); iframe.src = blobUrl; iframe.style.width='100%'; iframe.style.height='100%';
        body.appendChild(iframe); return;
      } catch (err) {
        console.error('Preview error', err);
        body.innerHTML = '<div>Unable to preview this file (permission denied or unsupported).</div>';
      }
    }

    prevBtn.addEventListener('click', () => { if (currentIndex > 0) renderAt(currentIndex - 1); });
    nextBtn.addEventListener('click', () => { if (currentIndex < attachments.length - 1) renderAt(currentIndex + 1); });

    const keyHandler = (e) => {
      if (e.key === 'Escape') { closeFullscreenPreview(); }
      if (e.key === 'ArrowLeft') { if (currentIndex > 0) renderAt(currentIndex - 1); }
      if (e.key === 'ArrowRight') { if (currentIndex < attachments.length - 1) renderAt(currentIndex + 1); }
    };
    document.addEventListener('keydown', keyHandler);

    function cleanupOnClose() {
      try { const b = wrap.dataset.blobUrl; if (b) URL.revokeObjectURL(b); } catch(e){}
      document.removeEventListener('keydown', keyHandler);
      document.removeEventListener('contextmenu', suppressContextMenuWhileOverlay);
    }

    const originalClose = closeFullscreenPreview;
    function wrappedClose() {
      cleanupOnClose();
      originalClose();
    }

    try { closeBtn.removeEventListener('click', closeFullscreenPreview); } catch(e){}
    closeBtn.addEventListener('click', wrappedClose);

    const prevCloseFn = window._sm_close_fullscreen_preview;
    window._sm_close_fullscreen_preview = () => { cleanupOnClose(); if (typeof prevCloseFn === 'function') prevCloseFn(); };

    await renderAt(currentIndex);
  }

  function renderModules(modulesWithMaterials) {
    if (!$items) return;
    $items.innerHTML = '';

    if (!modulesWithMaterials || modulesWithMaterials.length === 0) {
      showItems(false);
      showEmpty(true);
      return;
    }

    showEmpty(false);
    showItems(true);

    modulesWithMaterials.forEach(group => {
      const module = group.module || {};
      const materials = Array.isArray(group.materials) ? group.materials : [];

      const moduleWrap = document.createElement('div');
      moduleWrap.className = 'mb-4';

      const moduleHeader = document.createElement('div');
      moduleHeader.className = 'd-flex align-items-center gap-2 mb-2';
      moduleHeader.innerHTML = `
        <h5 class="mb-0" style="font-weight:700">
          <i class="fa fa-layer-group me-1 text-primary"></i>
          ${escapeHtml(module.title || 'Untitled Module')}
        </h5>
        <span class="small text-muted">(${materials.length})</span>
      `;

      moduleWrap.appendChild(moduleHeader);

      if (!materials.length) {
        const empty = document.createElement('div');
        empty.className = 'sm-empty';
        empty.textContent = 'No study materials in this module.';
        moduleWrap.appendChild(empty);
        $items.appendChild(moduleWrap);
        return;
      }

      materials.forEach(mat => {
        moduleWrap.appendChild(createItemRow(mat));
      });

      $items.appendChild(moduleWrap);
    });
  }

  // -------------------------
  // loadMaterials (FIXED: strict module from URL-first context)
  // -------------------------
  // Add these variables at the top of your IIFE (after const declarations)
let currentLoadRequest = null;
let isLoading = false;
let lastLoadedModule = null;

async function loadMaterials() {
  // Get current module to check if we need to reload
  const currentModule = getStrictModuleFromContext();
  
  // Skip if already loading the same module
  if (isLoading && lastLoadedModule === currentModule) {
    console.debug('Load already in progress for this module, skipping...');
    return;
  }
  
  // Cancel previous request if loading different module
  if (currentLoadRequest) {
    console.debug('Cancelling previous load request');
    currentLoadRequest.abort();
    currentLoadRequest = null;
  }
  
  // Mark as loading
  isLoading = true;
  currentLoadRequest = new AbortController();
  
  showLoader(true);
  showItems(false);
  showEmpty(false);

  try {
    // always resync DOM context from URL before reading
    try {
      const host = document.querySelector('.crs-wrap');
      const qModule = deriveModuleUuid();
      if (host && qModule) {
        host.dataset.moduleId = String(qModule);
        host.dataset.module_id = String(qModule);
      }
    } catch(e){}

    const ctx = await waitForModuleContext();

    if (!ctx || !ctx.batch_id) {
      throw new Error('Batch context required');
    }

    if (!ctx.module_id) {
      console.warn('Module context missing – aborting material load');
      showEmpty(true);
      return;
    }

    // Update last loaded module
    lastLoadedModule = ctx.module_id;

    // ALWAYS pass module_uuid (uuid)
    const url =
      `${apiBase}/batch/${encodeURIComponent(ctx.batch_id)}` +
      `?module_uuid=${encodeURIComponent(ctx.module_id)}`;

    console.debug('Loading materials with URL →', url);

    const res = await apiFetch(url, {
      signal: currentLoadRequest.signal
    });
    
    if (!res.ok) throw new Error('HTTP ' + res.status);

    const json = await res.json().catch(() => null);
    if (!json || !json.data) throw new Error('Invalid response format');

    const modulesWithMaterials = json.data.modules_with_materials || [];

    // SORT (per module)
    const sortVal = $sort ? $sort.value : 'created_desc';

    modulesWithMaterials.forEach(group => {
      if (!Array.isArray(group.materials)) return;
      group.materials.sort((a, b) => {
        const da = a.created_at ? new Date(a.created_at) : new Date(0);
        const db = b.created_at ? new Date(b.created_at) : new Date(0);
        if (sortVal === 'created_desc') return db - da;
        if (sortVal === 'created_asc') return da - db;
        if (sortVal === 'title_asc') return (a.title || '').localeCompare(b.title || '');
        return 0;
      });
    });

    renderModules(modulesWithMaterials);

    if (json.data.batch) {
      window.currentBatchContext = json.data.batch;
    }

  } catch (e) {
    // Ignore aborted requests
    if (e.name === 'AbortError') {
      console.debug('Load request was aborted');
      return;
    }
    
    console.error('Load materials error:', e);
    if ($items) {
      $items.innerHTML =
        '<div class="sm-empty">Unable to load study materials — please refresh.</div>';
    }
    showItems(true);
    showErr('Failed to load study materials');
  } finally {
    isLoading = false;
    currentLoadRequest = null;
    showLoader(false);
  }
}
  if ($refresh) $refresh.addEventListener('click', loadMaterials);
  if ($search) $search.addEventListener('keyup', (e)=> { if (e.key === 'Enter') loadMaterials(); });
  if ($sort) $sort.addEventListener('change', loadMaterials);

  let createModalInstance = null;
  try { if (window.bootstrap && typeof window.bootstrap.Modal === 'function' && createModalEl) createModalInstance = new bootstrap.Modal(createModalEl); } catch(e){ createModalInstance = null; }

  if ($uploadBtn) {
    $uploadBtn.type = 'button';
    if (canCreate) {
      $uploadBtn.style.display = 'inline-block';
      const cleanBtn = $uploadBtn.cloneNode(true);
      $uploadBtn.parentNode.replaceChild(cleanBtn, $uploadBtn);
      cleanBtn.addEventListener('click', (ev) => { ev.preventDefault(); showCreateModal(); });
    } else {
      $uploadBtn.style.display = 'none';
    }
  }

  let _manualBackdrop = null;

  function showCreateModal() {
    const smFormEl = document.getElementById('smCreateForm');

    if (smFormEl) {
      const isEditing = (smIdInput && smIdInput.value && smIdInput.value.trim() !== '');

      if (!isEditing) {
        smFormEl.reset();
        smFormEl.classList.remove('was-validated');
        if (createModalEl) createModalEl._selectedLibraryUrls = [];
      } else {
        smFormEl.classList.remove('was-validated');
      }
    }

    const smAlert = document.getElementById('smCreateAlert');
    if (smAlert) smAlert.style.display = 'none';

    // FORCE MODULE FROM URL-FIRST CONTEXT
    const strictModule = getStrictModuleFromContext();
    if (!strictModule && !smIdInput?.value) {
      if (smAlert) {
        smAlert.innerHTML = 'Module context missing. Please open this module first.';
        smAlert.style.display = '';
      }
      return;
    }

    updateContextDisplay();

    if (createModalInstance && typeof createModalInstance.show === 'function') {
      createModalInstance.show();
      return;
    }
    if (!createModalEl) return;

    _manualBackdrop = document.createElement('div'); _manualBackdrop.className = 'modal-backdrop fade show';
    document.body.appendChild(_manualBackdrop);

    createModalEl.classList.add('show'); createModalEl.style.display = 'block'; createModalEl.setAttribute('aria-hidden','false');
    document.body.classList.add('modal-open'); document.documentElement.style.overflow = 'hidden';

    if (!createModalEl._fallbackHooksAdded) {
      createModalEl.querySelectorAll('[data-bs-dismiss], .btn-close').forEach(btn => btn.addEventListener('click', hideCreateModal));
      _manualBackdrop.addEventListener('click', hideCreateModal);
      document.addEventListener('keydown', _fallbackEscHandler);
      createModalEl._fallbackHooksAdded = true;
    }
  }

  function cleanupModalBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(b => { try { b.remove(); } catch(e){} });
    document.body.classList.remove('modal-open');
    try { document.documentElement.style.overflow = ''; document.body.style.overflow = ''; } catch(e){}
  }

  function hideCreateModal() {
    if (createModalInstance && typeof createModalInstance.hide === 'function') {
      try { createModalInstance.hide(); } catch(e) {}
      setTimeout(cleanupModalBackdrops, 50);
      exitEditMode();
      return;
    }
    if (!createModalEl) return;
    createModalEl.classList.remove('show');
    createModalEl.style.display = 'none';
    createModalEl.setAttribute('aria-hidden','true');
    cleanupModalBackdrops();
    if (_manualBackdrop && _manualBackdrop.parentNode) { _manualBackdrop.parentNode.removeChild(_manualBackdrop); _manualBackdrop = null; }
    try { document.removeEventListener('keydown', _fallbackEscHandler); } catch(e){}
    exitEditMode();
  }

  function _fallbackEscHandler(e){ if (e.key === 'Escape') hideCreateModal(); }

  function enterEditMode(row){
    // lock module to URL-first context even in edit mode
    const strictModule = getStrictModuleFromContext();
    if (!strictModule) {
      showErr('Module context missing. Reload page.');
    }

    if (smIdInput) smIdInput.value = row.id || '';
    if (smMethodInput) smMethodInput.value = 'PATCH';
    if (smTitleInput) smTitleInput.value = row.title || '';
    if (smDescInput) smDescInput.value = row.description || '';
    if (smViewPolicy) smViewPolicy.value = row.view_policy || 'inline_only';
    if (smAttachmentsInput) smAttachmentsInput.value = '';
    if (smExistingWrap && smExistingList) {
      const attachments = row.attachment && Array.isArray(row.attachment) ? row.attachment : [];
      smExistingList.innerHTML = '';
      if (attachments.length) {
        attachments.forEach((a, idx) => {
          const id = a.id || a.attachment_id || a.file_id || a.storage_key || a.key || (a.url || a.path || '').split('/').pop() || (`file-${idx}`);
          const name = a.name || (a.url || a.path || '').split('/').pop() || `(attachment ${idx+1})`;
          const size = a.size ? ` (${formatSize(a.size)})` : '';
          const mime = a.mime || a.ext || '';

          const rowEl = document.createElement('div');
          rowEl.style.display = 'flex';
          rowEl.style.alignItems = 'center';
          rowEl.style.justifyContent = 'space-between';
          rowEl.style.gap = '8px';
          rowEl.style.padding = '8px';
          rowEl.style.borderRadius = '6px';
          rowEl.style.border = '1px dashed var(--line-strong)';
          rowEl.style.background = '#fbfbfb';

          const left = document.createElement('div');
          left.style.display = 'flex';
          left.style.flexDirection = 'column';
          left.innerHTML = `<div style="font-weight:600">${escapeHtml(name)}${escapeHtml(size)}</div>
                            <div class="small text-muted" style="margin-top:4px;">${escapeHtml(mime)}</div>`;

          const right = document.createElement('div');
          right.style.display = 'flex';
          right.style.alignItems = 'center';
          right.style.gap = '8px';

          const chk = document.createElement('input');
          chk.type = 'checkbox';
          chk.name = 'remove_attachments[]';
          chk.value = id;
          chk.style.display = 'none';
          chk.title = 'Marked for removal';

          const removeBtn = document.createElement('button');
          removeBtn.type = 'button'; removeBtn.className = 'btn btn-sm btn-outline-danger';
          removeBtn.innerHTML = '<i class="fa fa-trash"></i> Remove';
          removeBtn.title = 'Remove this file on update';

          const undoBtn = document.createElement('button');
          undoBtn.type = 'button'; undoBtn.className = 'btn btn-sm btn-outline-secondary';
          undoBtn.style.display = 'none';
          undoBtn.innerHTML = '<i class="fa fa-undo"></i> Undo';

          if (a.url || a.signed_url || a.path) {
            const previewLink = document.createElement('a');
            previewLink.href = a.signed_url || a.url || a.path;
            previewLink.target = '_blank';
            previewLink.className = 'small text-primary';
            previewLink.style.display = 'inline-block';
            previewLink.style.marginRight = '8px';
            previewLink.textContent = 'Preview';
            right.appendChild(previewLink);
          }

          removeBtn.addEventListener('click', () => {
            chk.checked = true;
            rowEl.style.opacity = '0.45';
            rowEl.style.filter = 'grayscale(0.4)';
            removeBtn.style.display = 'none';
            undoBtn.style.display = '';
          });

          undoBtn.addEventListener('click', () => {
            chk.checked = false;
            rowEl.style.opacity = '';
            rowEl.style.filter = '';
            removeBtn.style.display = '';
            undoBtn.style.display = 'none';
          });

          right.appendChild(chk);
          right.appendChild(removeBtn);
          right.appendChild(undoBtn);

          rowEl.appendChild(left);
          rowEl.appendChild(right);
          smExistingList.appendChild(rowEl);
        });
      } else {
        smExistingList.innerHTML = '<div class="text-muted small">No existing attachments</div>';
      }
      smExistingWrap.style.display = '';
    }

    const modalTitle = createModalEl.querySelector('.modal-title');
    if (modalTitle) modalTitle.innerHTML = '<i class="fa fa-pen me-2"></i> Edit Study Material';
    if (smCreateSubmitBtn) smCreateSubmitBtn.innerHTML = '<i class="fa fa-save me-1"></i> Update';
    updateContextDisplay();
    if (createModalInstance && typeof createModalInstance.show === 'function') createModalInstance.show();
    else showCreateModal();
  }

  function exitEditMode(){
    if (smIdInput) smIdInput.value = '';
    if (smMethodInput) smMethodInput.value = '';
    if (smCreateFormEl) smCreateFormEl.reset();
    if (smExistingList) smExistingList.innerHTML = '';
    if (smExistingWrap) smExistingWrap.style.display = 'none';
    const modalTitle = createModalEl.querySelector('.modal-title');
    if (modalTitle) modalTitle.innerHTML = '<i class="fa fa-plus me-2"></i> Add Study Material';
    if (smCreateSubmitBtn) smCreateSubmitBtn.innerHTML = '<i class="fa fa-save me-1"></i> Create';
    if (createModalEl) createModalEl._selectedLibraryUrls = [];
    const fileList = createModalEl?.querySelector('#sm_fileList');
    if (fileList) fileList.innerHTML = '';
  }

  try { if (createModalEl) createModalEl.addEventListener('hidden.bs.modal', exitEditMode); } catch(e){}

  // Init create form handling
  (function initCreateForm(){
    const smForm = smCreateFormEl;
    if (!smForm) return;
    const smSubmit = smCreateSubmitBtn;
    const smAlert  = document.getElementById('smCreateAlert');

    smForm.addEventListener('submit', async (ev) => {
      ev.preventDefault(); ev.stopPropagation();
      if (smAlert) smAlert.style.display = 'none';
      smForm.classList.add('was-validated');
      if (!smForm.checkValidity()) return;

      const editingId = smIdInput && smIdInput.value ? smIdInput.value.trim() : '';
      const ctx = readContext();
      const batchKey = ctx?.batch_id ?? ctx?.batch_uuid ?? null;

      if (!editingId && !batchKey) {
        if (smAlert) { smAlert.innerHTML = 'Missing Batch context — cannot create study material here.'; smAlert.style.display = ''; }
        return;
      }

      const fd = new FormData();
      if (ctx && ctx.batch_id) fd.append('batch_uuid', ctx.batch_id);

      const mod = getStrictModuleFromContext();
      if (!editingId && !mod) {
        if (smAlert) {
          smAlert.innerHTML = 'Module is required to create study material.';
          smAlert.style.display = '';
        }
        throw new Error('Missing module context');
      }

      if (mod) {
        if (/^\d+$/.test(mod)) {
          fd.append('course_module_id', mod);
        } else {
          fd.append('module_uuid', mod);
        }
      }

      fd.append('title', smTitleInput ? smTitleInput.value.trim() : '');
      fd.append('description', smDescInput ? smDescInput.value.trim() : '');
      fd.append('view_policy', smViewPolicy ? smViewPolicy.value : 'inline_only');

      // files from DnD/native input
      let files = [];
      if (createModalEl && typeof createModalEl._getSelectedSmFiles === 'function') {
        files = createModalEl._getSelectedSmFiles() || [];
      }
      if ((!files || files.length === 0) && smAttachmentsInput && smAttachmentsInput.files) {
        files = Array.from(smAttachmentsInput.files);
      }
      for (let i = 0; i < (files || []).length; i++) {
        fd.append('attachments[]', files[i]);
      }

      // append library URLs if any
      const libUrls = (createModalEl && Array.isArray(createModalEl._selectedLibraryUrls)) ? createModalEl._selectedLibraryUrls : [];
      if (libUrls && libUrls.length) {
        const seen = {};
        libUrls.forEach(u => {
          if (!u) return;
          if (typeof u !== 'string') u = String(u);
          if (seen[u]) return;
          seen[u] = true;
          fd.append('library_urls[]', u);
        });
      }

      const toRemove = Array.from(smForm.querySelectorAll('input[name="remove_attachments[]"]:checked')).map(n => n.value);
      toRemove.forEach(v => fd.append('remove_attachments[]', v));

      const prevHtml = smSubmit ? smSubmit.innerHTML : (editingId ? 'Update' : 'Create');
      if (smSubmit) { smSubmit.disabled = true; smSubmit.innerHTML = `<i class="fa fa-spinner fa-spin me-1"></i> ${editingId ? 'Updating...' : 'Creating...'}`; }

      try {
        let endpoint = apiBase;
        let method = 'POST';

        if (editingId) {
          fd.append('_method', 'PATCH');
          fd.append('id', editingId);
          endpoint = `${apiBase}/${encodeURIComponent(editingId)}`;
          method = 'POST';
        } else {
          const ctxBatch = ctx && ctx.batch_id ? ctx.batch_id : null;
          if (!ctxBatch) {
            if (smAlert) { smAlert.innerHTML = 'Missing Batch context — cannot create study material here.'; smAlert.style.display = ''; }
            throw new Error('Missing batch context');
          }
          endpoint = `${apiBase}/batch/${encodeURIComponent(ctxBatch)}`;
          method = 'POST';
        }

        const res = await apiFetch(endpoint, { method: method, body: fd });
        const json = await res.json().catch(()=>({}));

        if (!res.ok) {
          if (res.status === 422 && json.errors) {
            let msgs = [];
            for (const k in json.errors) {
              if (Array.isArray(json.errors[k])) msgs.push(`${k}: ${json.errors[k].join(', ')}`);
              else msgs.push(`${k}: ${json.errors[k]}`);
            }
            if (smAlert) { smAlert.innerHTML = msgs.map(m => `<div>${escapeHtml(m)}</div>`).join(''); smAlert.style.display = ''; }
          } else {
            if (smAlert) { smAlert.innerHTML = `<div>${escapeHtml(json.message || 'Failed to create/update study material')}</div>`; smAlert.style.display = ''; }
          }
          throw new Error('Save error');
        }

        try { if (createModalInstance && typeof createModalInstance.hide === 'function') createModalInstance.hide(); else hideCreateModal(); } catch(e){ hideCreateModal(); }
        setTimeout(cleanupModalBackdrops, 60);

        exitEditMode();
        showOk(json.message || (editingId ? 'Updated' : 'Created'));
        await loadMaterials();
      } catch (err) {
        console.error('Create/Update failed', err);
        showErr('Save failed');
      } finally {
        if (smSubmit) { smSubmit.disabled = false; smSubmit.innerHTML = prevHtml; }
      }
    });

    if (createModalEl) {
      try { createModalEl.addEventListener('show.bs.modal', () => updateContextDisplay()); } catch(e){}
    }
  })();

  function updateContextDisplay() {
    const ctx = readContext();
    const ctxText = document.getElementById('smContextText');
    const ctxErr  = document.getElementById('smContextError');
    const submitBtn = document.getElementById('smCreateSubmit');

    const isEditing = (smIdInput && smIdInput.value && smIdInput.value.trim() !== '');

    if (!ctx || !ctx.batch_id) {
      if (ctxText) ctxText.textContent = isEditing ? 'Editing (batch unknown)' : 'Missing batch context';
      if (ctxErr) ctxErr.style.display = isEditing ? 'none' : '';
      if (submitBtn) submitBtn.disabled = !isEditing;
      return;
    }

    if (ctxText) {
      if (ctx.module_id) ctxText.textContent = `Batch: ${ctx.batch_id} • Module: ${ctx.module_id}`;
      else ctxText.textContent = `Batch: ${ctx.batch_id} • Module: NOT SELECTED`;
    }

    if (!isEditing && !ctx.module_id) {
      if (ctxErr) ctxErr.style.display = '';
      if (submitBtn) submitBtn.disabled = true;
      return;
    }

    if (ctxErr) ctxErr.style.display = 'none';
    if (submitBtn) submitBtn.disabled = false;
  }

  // -------------------------
  // BIN / Deleted items logic
  // -------------------------
  (function initBin(){
    if (!$btnBin) return;
    if (!canViewBin) {
      $btnBin.style.display = 'none';
      return;
    } else {
      $btnBin.style.display = 'inline-block';
    }

    async function fetchDeletedMaterials(params = '') {
      try {
        const ctx = readContext();
        let url;
        if (ctx && ctx.batch_id) {
          url = `${apiBase}/bin/batch/${encodeURIComponent(ctx.batch_id)}`;
          if (ctx.module_id) url += `?module_uuid=${encodeURIComponent(ctx.module_id)}`;
        } else {
          url = '/api/study-materials/deleted' + (params ? ('?' + params) : '');
        }

        const r = await apiFetch(url);
        if (!r.ok) throw new Error('HTTP ' + r.status);
        const j = await r.json().catch(() => null);

        const items = j && (j.data || j.items) ? (j.data || j.items) : (Array.isArray(j) ? j : []);
        return (items || []).map(it => {
          if (typeof it.attachment === 'string' && it.attachment) {
            try { it.attachment = JSON.parse(it.attachment); } catch {}
          }
          return it;
        }).filter(it => !!(it && (it.deleted_at || it.deletedAt)));
      } catch (e) {
        console.error('fetchDeletedMaterials failed', e);
        return [];
      }
    }

    function buildBinTable(items) {
      if (!document.getElementById('bin-overflow-css')) {
        const s = document.createElement('style'); s.id = 'bin-overflow-css';
        s.textContent = `.dropdown-menu { overflow: visible !important; white-space: nowrap; } .table-responsive { overflow: visible !important; }`;
        document.head.appendChild(s);
      }

      const wrap = document.createElement('div'); wrap.className = 'sm-card p-3';
      const heading = document.createElement('div'); heading.className = 'd-flex align-items-center justify-content-between mb-2';
      heading.innerHTML = `<div class="fw-semibold" style="font-size:15px">Deleted Study Materials</div>
        <div class="d-flex gap-2">
          <button id="bin-refresh" class="btn btn-sm btn-primary"><i class="fa fa-rotate-right me-1"></i></button>
          <button id="bin-back" class="btn btn-sm btn-outline-primary"> <i class="fa fa-arrow-left me-1"></i> Back</button>
        </div>`;
      wrap.appendChild(heading);

      const resp = document.createElement('div'); resp.className = 'table-responsive';
      const table = document.createElement('table'); table.className = 'table table-hover table-borderless table-sm mb-0'; table.style.fontSize = '13px';
      table.innerHTML = `<thead class="text-muted" style="font-weight:600; font-size:var(--fs-14);">
        <tr>
          <th class="text-start">Study material</th>
          <th style="width:140px">Created</th>
          <th style="width:160px">Deleted At</th>
          <th style="width:120px">Attachments</th>
          <th style="width:120px" class="text-end">Actions</th>
        </tr>
      </thead><tbody></tbody>`;
      const tbody = table.querySelector('tbody');

      if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-muted small">No deleted items.</td></tr>`;
      } else {
        items.forEach((it, idx) => {
          if (typeof it.attachment === 'string' && it.attachment) {
            try { it.attachment = JSON.parse(it.attachment); } catch { it.attachment = []; }
          }
          const attCount = Array.isArray(it.attachment) ? it.attachment.length : (it.attachment_count || 0);
          const tr = document.createElement('tr'); tr.style.borderTop = '1px solid var(--line-soft)';
          const titleTd = document.createElement('td');
          titleTd.innerHTML = `<div class="fw-semibold" style="line-height:1.1;">${escapeHtml(it.title || 'Untitled')}</div>
            <div class="small text-muted mt-1">${escapeHtml(it.description || it.slug || '')}</div>`;
          const createdTd = document.createElement('td'); createdTd.textContent = it.created_at ? new Date(it.created_at).toLocaleString() : '-';
          const deletedTd = document.createElement('td'); deletedTd.textContent = it.deleted_at ? new Date(it.deleted_at).toLocaleString() : '-';
          const attachTd = document.createElement('td'); attachTd.textContent = `${attCount} file(s)`;
          const actionsTd = document.createElement('td'); actionsTd.className = 'text-end';
          const dd = document.createElement('div'); dd.className = 'dropdown d-inline-block';
          dd.innerHTML = `
            <button class="btn btn-sm btn-light" type="button" id="binDdBtn${idx}" data-bs-toggle="dropdown" aria-expanded="false">
              <span style="font-size:18px; line-height:1;">⋮</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="binDdBtn${idx}" style="min-width:160px;">
              <li><button class="dropdown-item restore-action" type="button"><i class="fa fa-rotate-left me-2"></i> Restore</button></li>
              <li><hr class="dropdown-divider"></li>
              <li><button class="dropdown-item text-danger force-action" type="button"><i class="fa fa-skull-crossbones me-2"></i> Delete permanently</button></li>
            </ul>`;
          actionsTd.appendChild(dd);
          tr.appendChild(titleTd); tr.appendChild(createdTd); tr.appendChild(deletedTd); tr.appendChild(attachTd); tr.appendChild(actionsTd);
          tbody.appendChild(tr);

          const restoreBtn = dd.querySelector('.restore-action');
          const forceBtn = dd.querySelector('.force-action');

          restoreBtn.addEventListener('click', () => {
            try { bootstrap.Dropdown.getOrCreateInstance(dd.querySelector('[data-bs-toggle="dropdown"]')).hide(); } catch {}
            restoreItem(it);
          });

          forceBtn.addEventListener('click', () => {
            try { bootstrap.Dropdown.getOrCreateInstance(dd.querySelector('[data-bs-toggle="dropdown"]')).hide(); } catch {}
            forceDeleteItem(it);
          });
        });
      }

      resp.appendChild(table);
      wrap.appendChild(resp);

      setTimeout(() => {
        wrap.querySelector('#bin-refresh')?.addEventListener('click', () => openBin());
        wrap.querySelector('#bin-back')?.addEventListener('click', () => loadMaterials());
      }, 0);

      return wrap;
    }

    async function restoreItem(item) {
      const r = await Swal.fire({ title: 'Restore item?', text: `Restore "${item.title || 'this item'}"?`, icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, restore', cancelButtonText: 'Cancel' });
      if (!r.isConfirmed) return;
      try {
        const url = `/api/study-materials/${encodeURIComponent(item.id)}/restore`;
        const res = await apiFetch(url, { method: 'POST' });
        if (!res.ok) throw new Error('Restore failed: ' + res.status);
        showOk('Restored');
        await openBin();
      } catch (e) { console.error(e); showErr('Restore failed'); }
    }

    async function forceDeleteItem(item) {
      const r = await Swal.fire({
        title: 'Permanently delete?',
        html: `Permanently delete "<strong>${escapeHtml(item.title || 'this item')}</strong>"?<br><strong>This cannot be undone.</strong>`,
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete permanently', cancelButtonText: 'Cancel', focusCancel: true
      });
      if (!r.isConfirmed) return;
      try {
        const url = `/api/study-materials/${encodeURIComponent(item.id)}/force`;
        const res = await apiFetch(url, { method: 'DELETE' });
        if (!res.ok) throw new Error('Delete failed: ' + res.status);
        showOk('Permanently deleted');
        await openBin();
      } catch (e) { console.error(e); showErr('Delete failed'); }
    }

    let _prevContent = null;
    async function openBin() {
      if (!_prevContent && $items) _prevContent = $items.innerHTML;
      showLoader(true); showEmpty(false); showItems(false);
      try {
        const params = new URLSearchParams();
        const ctx = readContext();
        if (ctx && ctx.batch_id) params.set('batch_uuid', ctx.batch_id);
        if (ctx && ctx.module_id) params.set('module_uuid', ctx.module_id);

        const items = await fetchDeletedMaterials(params.toString());
        const tableEl = buildBinTable(items || []);
        if ($items) { $items.innerHTML = ''; $items.appendChild(tableEl); showItems(true); }

        const back = document.getElementById('bin-back');
        if (back) back.addEventListener('click', (e)=>{ e.preventDefault(); restorePreviousList(); });

        const refresh = document.getElementById('bin-refresh');
        if (refresh) refresh.addEventListener('click', (e)=>{ e.preventDefault(); openBin(); });
      } catch (e) {
        console.error(e);
        if ($items) $items.innerHTML = '<div class="sm-empty p-3">Unable to load bin. Try refreshing the page.</div>';
        showItems(true);
        showErr('Failed to load bin');
      } finally { showLoader(false); }
    }

    function restorePreviousList() {
      if ($items) {
        if (_prevContent !== null) {
          $items.innerHTML = _prevContent;
          _prevContent = null;
        } else {
          if (typeof loadMaterials === 'function') loadMaterials();
        }
      }
    }

    $btnBin.addEventListener('click', (ev) => { ev.preventDefault(); openBin(); });
  })();

  /* ===== scoped drag & drop + Library picker for Study Material modal ===== */
  (function wireSmAttachments(){
    if (!createModalEl) return;

    const $in = (sel) => createModalEl.querySelector(sel);

    const dropzone = $in('#sm_dropzone');
    const fileInput = $in('#sm_attachments');
    const fileListWrap = $in('#sm_fileList');
    const chooseLabel = $in('#sm_choose_label');
    const clearBtn = $in('#sm_clear_files');

    let selectedFiles = [];
    if (!createModalEl._selectedLibraryUrls) createModalEl._selectedLibraryUrls = [];

    function escapeHtmlLocal(str){ return String(str||'').replace(/[&<>"'`=\/]/g, s=> ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60","=":"&#x3D;"}[s])); }
    function formatFileSize(bytes){
      if(bytes==null) return '';
      if(bytes < 1024) return bytes + ' B';
      if(bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
      return (bytes/(1024*1024)).toFixed(1) + ' MB';
    }

    function renderFileList(){
      if(!fileListWrap) return;
      fileListWrap.innerHTML = '';

      selectedFiles.forEach((f, idx) => {
        const item = document.createElement('div');
        item.className = 'sm-file-item';
        item.innerHTML = `
          <div class="meta">
            <i class="fa fa-file fa-fw" style="opacity:.6"></i>
            <div style="min-width:0">
              <div class="name" title="${escapeHtmlLocal(f.name)}">${escapeHtmlLocal(f.name)}</div>
              <div class="size">${formatFileSize(f.size)}</div>
            </div>
          </div>
          <div style="display:flex; gap:8px; align-items:center">
            <button type="button" class="sm-file-remove btn btn-sm" data-idx="${idx}" title="Remove"><i class="fa fa-xmark"></i></button>
          </div>
        `;
        fileListWrap.appendChild(item);
      });

      const libs = createModalEl._selectedLibraryUrls || [];
      libs.forEach((u, idx) => {
        const name = (u || '').split('/').pop() || u;
        const item = document.createElement('div');
        item.className = 'sm-file-item sm-library-item';
        item.innerHTML = `
          <div class="meta">
            <i class="fa fa-link fa-fw" style="opacity:.6"></i>
            <div style="min-width:0">
              <div class="name" title="${escapeHtmlLocal(name)}">${escapeHtmlLocal(name)}</div>
              <div class="size small text-muted" title="${escapeHtmlLocal(u)}">${escapeHtmlLocal(u)}</div>
            </div>
          </div>
          <div style="display:flex; gap:8px; align-items:center">
            <button type="button" class="sm-lib-preview btn btn-sm btn-outline-primary" data-idx="${idx}" data-url="${escapeHtmlLocal(u)}" title="Preview"><i class="fa fa-eye"></i></button>
            <button type="button" class="sm-lib-remove btn btn-sm btn-outline-danger" data-idx="${idx}" title="Remove"><i class="fa fa-trash"></i></button>
          </div>
        `;
        fileListWrap.appendChild(item);
      });

      fileListWrap.querySelectorAll('.sm-file-remove').forEach(btn => {
        btn.addEventListener('click', ()=> {
          const i = Number(btn.dataset.idx);
          if (!Number.isNaN(i)) {
            selectedFiles.splice(i, 1);
            renderFileList();
          }
        });
      });

      fileListWrap.querySelectorAll('.sm-lib-remove').forEach(btn => {
        btn.addEventListener('click', ()=> {
          const i = Number(btn.dataset.idx);
          if (!Number.isNaN(i)) {
            createModalEl._selectedLibraryUrls.splice(i, 1);
            renderFileList();
          }
        });
      });

      fileListWrap.querySelectorAll('.sm-lib-preview').forEach(btn => {
        btn.addEventListener('click', (ev) => {
          ev.preventDefault();
          const url = btn.dataset.url;
          const at = normalizeAttachmentForPreview(url);
          openAttachmentPreview(at, 'Preview');
        });
      });
    }

    function addFiles(files){
      Array.from(files || []).forEach(f => {
        if (!f) return;
        if (f.size > 50 * 1024 * 1024) {
          Swal.fire({ icon:'warning', title:'File too large', text: `File "${f.name}" exceeds 50MB.` });
          return;
        }
        selectedFiles.push(f);
      });
      renderFileList();
    }

    if (chooseLabel && !createModalEl.querySelector('#sm_choose_from_library')) {
      const libBtn = document.createElement('button');
      libBtn.type = 'button';
      libBtn.id = 'sm_choose_from_library';
      libBtn.className = 'sm-choose-btn btn btn-outline-secondary';
      libBtn.style.marginLeft = '8px';
      libBtn.innerHTML = `<i class="fa fa-book"></i> <span style="margin-left:6px">Choose from Library</span>`;
      chooseLabel.parentNode.insertBefore(libBtn, chooseLabel.nextSibling);

      libBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openLibraryPicker();
      });
    }

    if (dropzone) {
      ['dragenter','dragover'].forEach(ev => dropzone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); dropzone.classList.add('dragover'); }));
      ['dragleave','dragend','drop'].forEach(ev => dropzone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('dragover'); }));
      dropzone.addEventListener('drop', (e) => {
        e.preventDefault(); e.stopPropagation();
        const files = (e.dataTransfer && e.dataTransfer.files) ? e.dataTransfer.files : null;
        if (files) addFiles(files);
      });
    }

    if (fileInput) {
      fileInput.addEventListener('change', ()=> {
        if (fileInput.files) addFiles(fileInput.files);
        fileInput.value = '';
      });
    }

    if (chooseLabel && fileInput) {
      chooseLabel.addEventListener('keydown', (e)=> { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }});
    }

    if (clearBtn) clearBtn.addEventListener('click', ()=> { selectedFiles = []; createModalEl._selectedLibraryUrls = []; renderFileList(); });

    createModalEl._getSelectedSmFiles = ()=> selectedFiles.slice();
    if (!Array.isArray(createModalEl._selectedLibraryUrls)) createModalEl._selectedLibraryUrls = [];

    try {
      createModalEl.addEventListener('show.bs.modal', ()=> {
        const isEditing = (smIdInput && smIdInput.value && smIdInput.value.trim() !== '');
        if (!isEditing) { selectedFiles = []; createModalEl._selectedLibraryUrls = []; renderFileList(); }
        else { renderFileList(); }
      });
    } catch(e){}

    // ---------------------------
    // Library picker implementation (unchanged, relies on apiBase + batch)
    // ---------------------------
    let _libModal = null;

    function ensureLibraryModal() {
      if (_libModal) return _libModal;
      const m = document.createElement('div');
      m.className = 'modal fade';
      m.id = 'smLibraryModal';
      m.tabIndex = -1;
      m.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-book me-2"></i>Choose from Library</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:160px;">
             <div id="smLibLoader" style="display:none; padding:18px;">
              <div style="display:flex; align-items:center; gap:8px;">
                <div class="spin" aria-hidden="true"></div>
                <div class="text-muted">Loading library…</div>
              </div>
            </div>

              <div id="smLibEmpty" style="display:none;" class="text-muted small p-3;">No library items found for this batch.</div>

              <div id="smLibSearchContainer" class="mb-3" style="display:none;">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" id="search-addon">
                    <i class="fa fa-search"></i>
                  </span>
                  <input
                    type="text"
                    id="smLibSearch"
                    class="form-control"
                    placeholder="Search documents by name or reference..."
                    aria-label="Search"
                    aria-describedby="search-addon"
                  />
                  <button id="smLibClearSearch" class="btn btn-outline-secondary" type="button" style="display:none;">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
                <div id="smLibSearchResults" class="small text-muted mt-2" style="display:none;"></div>
              </div>

              <div id="smLibList" style="display:none;"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
              <button id="smLibConfirm" type="button" class="btn btn-primary">Add selected</button>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(m);
      _libModal = m;
      return _libModal;
    }

    async function openLibraryPicker() {
      const modalEl = ensureLibraryModal();
      const libList = modalEl.querySelector('#smLibList');
      const libLoader = modalEl.querySelector('#smLibLoader');
      const libEmpty = modalEl.querySelector('#smLibEmpty');
      const libConfirm = modalEl.querySelector('#smLibConfirm');
      const searchContainer = modalEl.querySelector('#smLibSearchContainer');
      const searchInput = modalEl.querySelector('#smLibSearch');
      const clearSearchBtn = modalEl.querySelector('#smLibClearSearch');
      const searchResults = modalEl.querySelector('#smLibSearchResults');

      libList.innerHTML = '';
      libLoader.style.display = '';
      libList.style.display = 'none';
      libEmpty.style.display = 'none';
      searchContainer.style.display = 'none';
      searchInput.value = '';
      clearSearchBtn.style.display = 'none';
      searchResults.style.display = 'none';
      libConfirm.disabled = true;

      try {
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
          bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
          modalEl.style.display = 'block';
          modalEl.classList.add('show');
          document.body.classList.add('modal-open');
        }
      } catch (e) {}

      if (!document.getElementById('sm-lib-card-styles')) {
        const style = document.createElement('style');
        style.id = 'sm-lib-card-styles';
        style.textContent = `
          #smLibList .sm-lib-grid { display: grid; gap: 12px; grid-template-columns: repeat(3, 1fr); }
          @media (max-width: 1024px) { #smLibList .sm-lib-grid { grid-template-columns: repeat(2, 1fr); } }
          @media (max-width: 640px) { #smLibList .sm-lib-grid { grid-template-columns: repeat(1, 1fr); } }

          .sm-lib-card { display:flex; flex-direction:column; gap:8px; padding:10px; border-radius:10px; border:1px solid rgba(0,0,0,0.06); background:#fff; min-height:160px; position:relative; overflow:hidden; }
          .sm-lib-thumb { height:120px; display:block; width:100%; object-fit:cover; border-radius:8px; background: linear-gradient(180deg,#f7f7f7,#fff); box-shadow: inset 0 0 0 1px rgba(0,0,0,0.02); }
          .sm-lib-placeholder-icon { width:100%; height:120px; display:flex; align-items:center; justify-content:center; font-size:36px; color:rgba(0,0,0,0.35); border-radius:8px; background: linear-gradient(180deg,#fafafa,#fff); }
          .sm-lib-card .overlay-checkbox { position:absolute; top:10px; left:10px; z-index:5; background:rgba(255,255,255,0.9); padding:6px; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
          .sm-lib-card .card-refs { font-size:12px; color:var(--muted-color); margin-top:6px; max-height:3.6em; overflow:hidden; }
          .sm-lib-card .card-actions { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:auto; }
          .sm-lib-card .card-name { margin-top:6px; font-weight:600; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
          .highlight { background-color: rgba(255, 255, 0, 0.3); padding: 0 1px; border-radius: 2px; }
        `;
        document.head.appendChild(style);
      }

      try {
        const ctx = readContext();
        if (!ctx || !ctx.batch_id) throw new Error('Missing batch context');

        const url = `${apiBase}/batch/${encodeURIComponent(ctx.batch_id)}`;
        const res = await apiFetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json().catch(() => null);
        if (!json || !json.data) throw new Error('Invalid response');

        const modulesWithMaterials = json.data.modules_with_materials || [];

        const docMap = new Map();
        modulesWithMaterials.forEach(mg => {
          const moduleTitle = mg.module?.title || '';
          (mg.materials || []).forEach(mat => {
            const materialTitle = mat.title || 'Untitled';
            const materialId = mat.id || mat.uuid || '';
            const atts = Array.isArray(mat.attachment) ? mat.attachment : (mat.attachments || []);
            (atts || []).forEach(a => {
              const normalized = (typeof a === 'string') ? { url: a, name: (a||'').split('/').pop() } : a;
              const urlCandidate = (normalized.signed_url || normalized.url || normalized.path || '') + '';
              if (!urlCandidate) return;
              const key = urlCandidate.split('?')[0];
              if (!docMap.has(key)) {
                docMap.set(key, {
                  url: urlCandidate,
                  name: normalized.name || (urlCandidate.split('/').pop() || 'file'),
                  refs: [{ materialTitle, moduleTitle, materialId }],
                  sample: normalized,
                  searchText: (normalized.name || '') + ' ' + (materialTitle || '') + ' ' + (moduleTitle || '') + ' ' + (urlCandidate || '')
                });
              } else {
                const entry = docMap.get(key);
                const hasRef = entry.refs.some(r => String(r.materialId) === String(materialId) && r.materialTitle === materialTitle);
                if (!hasRef) {
                  entry.refs.push({ materialTitle, moduleTitle, materialId });
                  entry.searchText += ' ' + (materialTitle || '') + ' ' + (moduleTitle || '');
                }
              }
            });
          });
        });

        libLoader.style.display = 'none';

        const items = Array.from(docMap.values());
        if (!items.length) {
          libEmpty.style.display = '';
          libList.style.display = 'none';
          searchContainer.style.display = 'none';
          libConfirm.disabled = true;
          return;
        }

        searchContainer.style.display = '';

        function extOf(u){ try { return (u || '').split('?')[0].split('.').pop().toLowerCase(); } catch(e){ return ''; } }
        function isImageExt(e){ return ['png','jpg','jpeg','webp','gif','svg'].includes(e); }
        function isPdfExt(e){ return e === 'pdf'; }
        function isVideoExt(e){ return ['mp4','webm','ogg','mov'].includes(e); }

        function highlightText(text, searchTerm) {
          if (!searchTerm || !text) return escapeHtml(text);
          const escapedText = escapeHtml(text);
          const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
          return escapedText.replace(regex, '<span class="highlight">$1</span>');
        }

        function renderLibraryItems(filteredItems = items, searchTerm = '') {
          const selectedSet = new Set((createModalEl._selectedLibraryUrls || []).map(u => String(u)));

          const cardHtml = filteredItems.map((it, idx) => {
            const url = it.url || '';
            const name = it.name || (url||'').split('/').pop() || `file-${idx+1}`;
            const ext = extOf(url);
            const isImg = isImageExt(ext);
            const isPdf = isPdfExt(ext);
            const isVid = isVideoExt(ext);
            const refs = it.refs.map(r => (r.moduleTitle ? `${r.materialTitle} • ${r.moduleTitle}` : r.materialTitle));
            const refsShort = refs.slice(0,3).join(', ');
            const more = Math.max(0, refs.length - 3);
            const refsDisplay = refsShort + (more ? `, +${more} more` : '');
            const checked = selectedSet.has(url) ? 'checked' : '';

            const highlightedName = searchTerm ? highlightText(name, searchTerm) : escapeHtml(name);
            const highlightedRefs = searchTerm ? highlightText(refsDisplay, searchTerm) : escapeHtml(refsDisplay);

            let thumbHtml = '';
            if (isImg) {
              thumbHtml = `<img loading="lazy" class="sm-lib-thumb" src="${escapeHtml(url)}" alt="${escapeHtml(name)}" />`;
            } else if (isPdf) {
              thumbHtml = `<div class="sm-lib-placeholder-icon"><i class="fa fa-file-pdf"></i></div>`;
            } else if (isVid) {
              thumbHtml = `<div class="sm-lib-placeholder-icon"><i class="fa fa-video"></i></div>`;
            } else {
              thumbHtml = `<div class="sm-lib-placeholder-icon"><i class="fa fa-file"></i></div>`;
            }

            return `
              <div class="sm-lib-card" data-url="${escapeHtml(url)}" data-ext="${escapeHtml(ext)}" data-name="${escapeHtml(name)}">
                <div class="overlay-checkbox">
                  <input class="sm-lib-checkbox" type="checkbox" data-url="${escapeHtml(url)}" ${checked} />
                </div>

                <div class="thumb-wrap">${thumbHtml}</div>

                <div class="card-name" title="${escapeHtml(name)}">${highlightedName}</div>
                <div class="card-refs" title="${escapeHtml(refs.join(' • '))}">${highlightedRefs}</div>

                <div class="card-actions">
                  <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" class="sm-lib-preview-row btn btn-sm btn-outline-primary" data-url="${escapeHtml(url)}" title="Preview"><i class="fa fa-eye"></i> Preview</button>
                  </div>
                  <div style="font-size:12px; color:var(--muted-color);">${escapeHtml(String(it.refs.length))} ref(s)</div>
                </div>
              </div>
            `;
          }).join('');

          libList.innerHTML = `<div class="sm-lib-grid">${cardHtml}</div>`;
          libList.style.display = '';
          libEmpty.style.display = 'none';

          if (searchTerm) {
            searchResults.style.display = '';
            searchResults.textContent = `Found ${filteredItems.length} of ${items.length} items`;
          } else {
            searchResults.style.display = 'none';
          }

          wireCardInteractions();
        }

        function wireCardInteractions() {
          const grid = libList.querySelector('.sm-lib-grid');
          if (!grid) return;

          grid.querySelectorAll('.sm-lib-card').forEach(card => {
            const cb = card.querySelector('.sm-lib-checkbox');
            card.addEventListener('click', (ev) => {
              if (ev.target.closest('.sm-lib-preview-row') || ev.target.tagName === 'INPUT' || ev.target.closest('.overlay-checkbox')) return;
              cb.checked = !cb.checked;
              updateConfirmButtonState();
            });
            cb.addEventListener('change', updateConfirmButtonState);
          });

          libList.querySelectorAll('.sm-lib-preview-row').forEach(btn => {
            btn.addEventListener('click', (ev) => {
              ev.preventDefault();
              ev.stopPropagation();
              const u = btn.dataset.url;
              if (!u) return;

              const at = { url: u, name: (u||'').split('/').pop() };
              try {
                if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                  bootstrap.Modal.getInstance(modalEl)?.hide();
                } else {
                  modalEl.classList.remove('show');
                  modalEl.style.display = 'none';
                  document.body.classList.remove('modal-open');
                }
              } catch(e){}
              openAttachmentPreview(at, at.name || 'Preview');
            });
          });
        }

        function updateConfirmButtonState() {
          const grid = libList.querySelector('.sm-lib-grid');
          if (!grid) { libConfirm.disabled = true; return; }
          const any = Array.from(grid.querySelectorAll('.sm-lib-checkbox')).some(n => n.checked);
          libConfirm.disabled = !any;
        }

        renderLibraryItems(items);

        let searchTimeout;
        searchInput.addEventListener('input', function(e) {
          clearTimeout(searchTimeout);

          const searchTerm = e.target.value.trim().toLowerCase();

          if (searchTerm) {
            clearSearchBtn.style.display = 'block';
          } else {
            clearSearchBtn.style.display = 'none';
            searchResults.style.display = 'none';
            renderLibraryItems(items, '');
            return;
          }

          searchTimeout = setTimeout(() => {
            const filtered = items.filter(item => item.searchText.toLowerCase().includes(searchTerm));
            renderLibraryItems(filtered, searchTerm);
          }, 300);
        });

        clearSearchBtn.addEventListener('click', function() {
          searchInput.value = '';
          clearSearchBtn.style.display = 'none';
          searchResults.style.display = 'none';
          renderLibraryItems(items, '');
          searchInput.focus();
        });

        searchInput.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            if (this.value) {
              this.value = '';
              clearSearchBtn.style.display = 'none';
              searchResults.style.display = 'none';
              renderLibraryItems(items, '');
            } else {
              try {
                if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                  bootstrap.Modal.getInstance(modalEl)?.hide();
                }
              } catch(e) {}
            }
          }
        });

        libConfirm.onclick = () => {
          const checked = Array.from(libList.querySelectorAll('.sm-lib-checkbox'))
            .filter(n => n.checked)
            .map(n => n.dataset.url);
          const exist = createModalEl._selectedLibraryUrls || [];
          const set = new Set(exist.concat([]));
          checked.forEach(u => { if (u && !set.has(u)) set.add(u); });
          createModalEl._selectedLibraryUrls = Array.from(set);
          renderFileList();
          try {
            if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
              bootstrap.Modal.getInstance(modalEl)?.hide();
            } else {
              modalEl.classList.remove('show');
              modalEl.style.display = 'none';
              document.body.classList.remove('modal-open');
            }
          } catch (e) {}
        };

      } catch (err) {
        console.error('Library picker error', err);
        libLoader.style.display = 'none';
        libList.style.display = 'none';
        libEmpty.style.display = '';
        libEmpty.textContent = 'Unable to load library items.';
        searchContainer.style.display = 'none';
      }
    }
  })();

  // -------------------------
  // ✅ Detect URL changes (SPA) and reload correct module
  // -------------------------
  // -------------------------
// ✅ Detect URL changes (SPA) and reload correct module
// -------------------------
(function watchUrlChanges(){
  let lastHref = String(location.href);
  let urlChangeDebounce = null;

  function handlePossibleChange(){
    const now = String(location.href);
    if (now === lastHref) return;
    lastHref = now;

    // Debounce to prevent rapid-fire calls
    clearTimeout(urlChangeDebounce);
    
    urlChangeDebounce = setTimeout(() => {
      // resync DOM from URL
      try {
        const host = document.querySelector('.crs-wrap');
        const qModule = deriveModuleUuid();
        if (host && qModule) {
          host.dataset.moduleId = String(qModule);
          host.dataset.module_id = String(qModule);
        }
      } catch(e){}

      try { updateContextDisplay(); } catch(e){}
      loadMaterials();
    }, 200); // Wait 200ms for URL changes to settle
  }

  // back/forward
  window.addEventListener('popstate', handlePossibleChange);

  // patch pushState/replaceState
  const _ps = history.pushState;
  const _rs = history.replaceState;

  history.pushState = function(){
    const r = _ps.apply(this, arguments);
    setTimeout(handlePossibleChange, 0);
    return r;
  };
  history.replaceState = function(){
    const r = _rs.apply(this, arguments);
    setTimeout(handlePossibleChange, 0);
    return r;
  };

  // fallback poll (reduced frequency)
  setInterval(handlePossibleChange, 2000); // Changed from 500ms to 2000ms
})();
  // initial UI and data load
  updateContextDisplay();
  loadMaterials();
})();
</script>
