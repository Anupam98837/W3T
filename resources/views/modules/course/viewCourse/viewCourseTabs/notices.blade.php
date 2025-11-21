{{-- resources/views/Notices.blade.php --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* styling copied/adapted from StudyMaterial view but names adjusted */
.crs-wrap{ }
.sm-list{max-width:1100px;margin:18px auto}
.sm-card{border-radius:12px;padding:18px}
.sm-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-radius:10px;border:1px solid var(--line-strong);}
/* ... (unchanged earlier styles kept) ... */
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
.modal.show .modal-dialog { max-height: calc(100vh - 48px); }
.modal.show .modal-content { height: 100%; display: flex; flex-direction: column; }
.modal.show .modal-body { overflow: auto; max-height: calc(100vh - 200px); -webkit-overflow-scrolling: touch; }

#notice_existing_attachments .btn { padding: 6px 8px; font-size: 13px; }
#notice_existing_attachments .small.text-primary { text-decoration: underline; cursor: pointer; }

/* ---------- RTE styles (copied from your previous editor) ---------- */
.toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
.tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
.tool:hover{background:var(--page-hover)}
.rte-wrap{position:relative}
.rte{
  min-height:120px;max-height:400px;overflow:auto;
  border:1px solid var(--line-strong);border-radius:8px;background:#fff;padding:10px;line-height:1.6;outline:none
}
.rte:focus{box-shadow:var(--ring);border-color:var(--accent-color)}
.rte-ph{position:absolute;top:10px;left:10px;color:#9aa3b2;pointer-events:none;font-size:14px}
.rte.has-content + .rte-ph{display:none}
/* small toolbar tweaks for modal */
#notice_rte_toolbar .tool{padding:6px 8px;font-size:14px}
...
/* toolbar compact for modal */
#notice_rte_toolbar .tool { padding:6px 8px; font-size:14px; }

/* selection and content formatting consistency */
.rte a { color: var(--secondary-color); text-decoration: underline; }
.rte img { max-width:100%; height:auto; display:block; margin:8px 0; border-radius:6px; }
.rte pre, .rte code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, "Roboto Mono", "Courier New", monospace; background: rgba(0,0,0,0.03); padding:8px; border-radius:6px; overflow:auto; }

/* Small accessibility tweak for toolbar buttons */
.tool:focus { box-shadow: var(--ring); outline: none; }

/* Make placeholder work on small editors too */
.rte-wrap .rte-ph { transition: opacity .12s ease; }

/* Ensure contrast in dark mode by relying on theme tokens.
   No hard-coded colors — theme tokens from main.css will handle dark-mode */
html.theme-dark .tool,
html.theme-dark .rte,
html.theme-dark .rte-ph {
  /* no values here intentionally — main.css theme tokens already adjust vars used above */
  /* this block exists so you can override later if you want tiny tweaks for dark only */
}

/* keep toolbar icons consistent */
.tool .fa, .tool i { color: inherit; font-size: 0.95rem; }

/* ----------------------------- */
/* Notices — attachments dropzone (SMALLER variant) */
/* ----------------------------- */

/* Container */
.notice-dropzone {
  border: 2px dashed #8c2fb7;            /* purple dashed border (slightly thinner) */
  border-radius: 14px;                   /* slightly smaller radius */
  background: rgba(140,47,183,0.05);    /* lighter purple fill */
  padding: 20px 18px;                    /* reduced padding from 36 -> 20 */
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
  min-height: 110px;                     /* reduced min-height */
  position: relative;
}

/* inner dotted icon circle */
.notice-drop-icon {
  width: 48px;                           /* reduced from 56 -> 48 */
  height: 48px;
  border-radius: 50%;
  border: 1px dotted rgba(140,47,183,0.35);
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(255,255,255,0.6);
  font-size:16px;                        /* slightly smaller icon */
  color:#8c2fb7;
}

/* heading text inside dropzone */
.notice-dropzone .lead {
  font-weight:600;
  font-size:16px;                        /* reduced from 18 -> 16 */
  color:#2b2b2b;
  margin-top:4px;
}
.notice-dropzone .tiny {
  font-size:13px;
  color:#666;
}

/* actions row */
.notice-drop-actions {
  display:flex;
  gap:8px;
  align-items:center;
  justify-content:center;
  margin-top:6px;
}

/* purple choose file button (slightly smaller) */
.notice-choose-btn {
  background: #8c2fb7;
  color: #fff;
  border: 1px solid rgba(0,0,0,0.04);
  padding: 6px 12px;                     /* reduced padding */
  border-radius:8px;
  display: inline-flex;
  gap:8px;
  align-items:center;
  cursor:pointer;
  font-weight:600;
  font-size:14px;
}
.notice-choose-btn:hover { filter:brightness(.98); transform: translateY(-1px); }

/* clear all button (smaller) */
.notice-clear-btn {
  background: transparent;
  color: #3b3b3b;
  border: 1px solid rgba(0,0,0,0.06);
  padding: 6px 10px;
  border-radius:8px;
  cursor:pointer;
  font-size:14px;
}

/* dragover state (subtle) */
.notice-dropzone.dragover {
  border-color: #662d91;
  box-shadow: 0 6px 18px rgba(102,45,145,0.06);
  background: rgba(140,47,183,0.07);
}

/* hide native file input UI */
.notice-dropzone input[type="file"] { display:none; }

/* file list (kept compact) */
.notice-file-list { width:100%; max-width:820px; margin-top:10px; display:flex; flex-direction:column; gap:8px; align-items:center; }
.notice-file-item {
  width:100%;
  max-width:820px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding:8px 12px;                       /* reduced padding */
  border-radius:10px;
  background: #fff;
  border: 1px solid rgba(0,0,0,0.04);
  font-size:14px;
  color:#222;
}
.notice-file-item .meta { display:flex; gap:10px; align-items:center; min-width:0; }
.notice-file-item .name { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0; }
.notice-file-item .size { color:#777; font-size:13px; white-space:nowrap; }
.notice-file-remove { color:#c5308d; cursor:pointer; padding:6px; border-radius:6px; }

/* ensure good small-screen behavior */
@media (max-width:576px){
  .notice-dropzone { padding:14px 12px; min-height:96px; gap:8px; }
  .notice-drop-icon { width:40px; height:40px; font-size:14px; }
  .notice-choose-btn, .notice-clear-btn { padding:6px 10px; font-size:13px; }
  .notice-dropzone .lead { font-size:15px; }
  .notice-file-item { padding:8px 10px; }
}
</style>

<div class="crs-wrap" data-batch-id="">
  <div class="panel sm-card rounded-1 shadow-1" style="padding:18px;">
    <div class="d-flex align-items-center w-100">
      <h2 class="panel-title d-flex align-items-center gap-2 mb-0">
        <i class="fa fa-bullhorn" style="color: var(--primary-color);"></i>
        Notices
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
                <span class="input-group-text "><i class="fa fa-search text-muted"></i></span>
                <input id="notice-search" type="text" class="form-control" placeholder="Search notices...">
              </div>
            </div>

            <div class="col-md-4 col-lg-4 d-flex align-items-center gap-2">
              <select id="notice-sort" class="form-select">
                <option value="" disabled selected>Sort by</option>
                <option value="created_desc">Newest first</option>
                <option value="created_asc">Oldest first</option>
                <option value="title_asc">Title A → Z</option>
              </select>
              <button id="btn-refresh" class="btn btn-outline-primary d-flex align-items-center gap-1"><i class="fa fa-rotate-right"></i> Refresh</button>
            </div>

            <div class="col-md-2 col-lg-4 d-flex justify-content-end">
              <button id="btn-upload" class="btn btn-primary" style="display:none;" data-bs-toggle="modal" data-bs-target="#createNoticeModal">+ Notice</button>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div style="margin-top:14px;">
      <div id="notice-loader" class="sm-loader" style="display:none;"><div class="spin" aria-hidden="true"></div><div class="text-muted">Loading notices…</div></div>
      <div id="notice-empty" class="sm-empty" style="display:none;"><div style="font-weight:600; margin-bottom:6px;">No notices yet</div><div class="text-muted small">Notices posted by instructors will appear here.</div></div>
      <div id="notice-items" style="display:none; margin-top:8px;"></div>
    </div>
  </div>
</div>

<!-- Create Notice Modal -->
<div class="modal fade" id="createNoticeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus me-2"></i> Notice Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="noticeCreateForm" class="needs-validation" novalidate>
        <div class="modal-body">
          <div id="noticeCreateAlert" style="display:none;" class="alert alert-danger small"></div>
          <div class="row g-3">
            <div class="col-12">
              <div id="noticeContextInfo" class="small text-muted mb-2">Adding to: <span id="noticeContextText">—</span></div>
              <div id="noticeContextError" class="alert alert-warning small" style="display:none;">Unable to detect the target Batch for this page.</div>
            </div>

            <input type="hidden" id="notice_id" name="id" value="">
            <input type="hidden" id="notice__method" name="_method" value="">

            <div class="col-12">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input id="notice_title" name="title" type="text" class="form-control" maxlength="255" required>
              <div class="invalid-feedback">Title required.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Message (HTML allowed)</label>

              <!-- RTE toolbar + editor -->
              <div id="notice_rte_toolbar" class="toolbar" aria-label="Notice message toolbar">
                <button class="tool" type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                <button class="tool" type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                <button class="tool" type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                <button class="tool" type="button" data-format="H2">H2</button>
                <button class="tool" type="button" data-format="H3">H3</button>
                <button class="tool" type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
                <button class="tool" type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
                <button class="tool" type="button" id="btnLinkNotice"><i class="fa-solid fa-link"></i></button>
                <span class="tiny" style="display:none">Use HTML formatting for rich notices</span>
              </div>

              <div class="rte-wrap">
                <div id="notice_message_rte" class="rte" contenteditable="true" spellcheck="true" aria-label="Notice message editor"></div>
                <div class="rte-ph">Write the notice message here…</div>
              </div>

              <!-- Hidden textarea preserved for existing code and form submission -->
              <textarea id="notice_message_html" name="message_html" class="form-control d-none" rows="4"></textarea>
            </div>

            <div class="col-md-6" style="display:none">
              <label class="form-label">Visibility</label>
              <select id="notice_visibility" name="visibility_scope" class="form-select">
                <option value="batch" selected>Batch</option>
                <option value="course">Course</option>
                <option value="module">Module</option>
              </select>
            </div>

            <div class="col-12" id="notice_existing_attachments_wrap" style="display:none;">
              <label class="form-label">Existing attachments</label>
              <div id="notice_existing_attachments" class="small text-muted" style="padding:8px; border:1px dashed var(--line-strong); border-radius:6px; background: #fbfbfb;"></div>
              <div class="small text-muted mt-1">Select files to remove before updating (server-side removal).</div>
            </div>

            <div class="col-12">
             <label class="form-label">Attachments <small class="text-muted">(multiple allowed, max 50MB each)</small></label>

<div id="notice_dropzone" class="notice-dropzone" aria-label="Attachments dropzone">
  <div class="notice-drop-icon"><i class="fa fa-upload"></i></div>
  <div class="lead">Drag &amp; drop files here or click to upload</div>
  <div class="tiny">Any format • up to 50 MB per file</div>

  <div class="notice-drop-actions">
    <label for="notice_attachments" class="notice-choose-btn" id="notice_choose_label">
      <i class="fa fa-file-upload"></i>
      <span>Choose Files</span>
    </label>

    <button type="button" id="notice_clear_files" class="notice-clear-btn">Clear All</button>
  </div>

  <!-- hidden native input (keeps existing server-side wiring) -->
  <input id="notice_attachments" name="attachments[]" type="file" multiple />
</div>
 <!-- selected file list -->
  <div id="notice_fileList" class="notice-file-list" aria-live="polite"></div>

<div class="small text-muted mt-1">Supported: images, pdf, video, etc.</div>

            </div>

            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select id="notice_priority" name="priority" class="form-select">
                <option value="normal" selected>Normal</option>
                <option value="low">Low</option>
                <option value="high">High</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select id="notice_status" name="status" class="form-select">
                <option value="draft" selected>Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
              </select>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button id="noticeCreateSubmit" type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div id="notice-details-modal" class="modal" style="display:none;" aria-hidden="true">
  <div class="modal-dialog" style="max-width:560px; margin:80px auto;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Notice Details</h5>
        <button type="button" id="notice-details-close" class="btn btn-light">Close</button>
      </div>
      <div class="modal-body" id="notice-details-body" style="padding:18px;"></div>
      <div class="modal-footer" id="notice-details-footer" style="display:none;"></div>
    </div>
  </div>
</div>

<script>
(function(){
  const role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    Swal.fire({ icon: 'warning', title: 'Login required', text: 'Please sign in to continue.', allowOutsideClick: false }).then(()=>{ window.location.href = '/'; });
    return;
  }

  const isAdmin      = role.includes('admin')|| role.includes('superadmin');
  const isSuperAdmin = role.includes('super_admin') || role.includes('superadmin');
  const isInstructor = role.includes('instructor');

  const canCreate = isAdmin || isSuperAdmin || isInstructor;
  const canEdit   = isAdmin || isSuperAdmin || isInstructor;
  const canDelete = isAdmin || isSuperAdmin || isInstructor;
  const canViewBin = isAdmin || isSuperAdmin;

  const apiBase = '/api/notices';
  const defaultHeaders = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };

  // DOM refs
  const $loader = document.getElementById('notice-loader');
  const $empty  = document.getElementById('notice-empty');
  const $items  = document.getElementById('notice-items');
  const $search = document.getElementById('notice-search');
  const $sort   = document.getElementById('notice-sort');
  const $refresh = document.getElementById('btn-refresh');
  const $uploadBtn = document.getElementById('btn-upload');
  const $btnBin = document.getElementById('btn-bin');

  const detailsModal = document.getElementById('notice-details-modal');
  const detailsBody = document.getElementById('notice-details-body');
  const detailsClose = document.getElementById('notice-details-close');
  const detailsFooter = document.getElementById('notice-details-footer');

  const createModalEl = document.getElementById('createNoticeModal');

  const noticeIdInput = document.getElementById('notice_id');
  const noticeMethodInput = document.getElementById('notice__method');
  const noticeTitleInput = document.getElementById('notice_title');
  const noticeMessageInput = document.getElementById('notice_message_html'); // hidden textarea (kept for compatibility)
  const noticeVisibility = document.getElementById('notice_visibility');
  const noticeAttachmentsInput = document.getElementById('notice_attachments');
  const noticeExistingWrap = document.getElementById('notice_existing_attachments_wrap');
  const noticeExistingList = document.getElementById('notice_existing_attachments');
  const noticeCreateSubmitBtn = document.getElementById('noticeCreateSubmit');
  const noticeCreateFormEl = document.getElementById('noticeCreateForm');
  const noticePriority = document.getElementById('notice_priority');
  const noticeStatus = document.getElementById('notice_status');

  // RTE elements
  const noticeRte = document.getElementById('notice_message_rte');
  const noticeRteToolbar = document.getElementById('notice_rte_toolbar');
  const btnLinkNotice = document.getElementById('btnLinkNotice');

  function deriveCourseKey() {
    const parts = location.pathname.split('/').filter(Boolean);
    const last = parts.at(-1)?.toLowerCase();
    if (last === 'view' && parts.length >= 2) return parts.at(-2);
    return parts.at(-1);
  }
  function getQueryParam(name) { try { return (new URL(window.location.href)).searchParams.get(name); } catch(e){ return null; } }

  (function ensureBatchInDomFromUrl() {
    const host = document.querySelector('.crs-wrap');
    if (!host) return;
    const existing = host.dataset.batchId ?? host.dataset.batch_id ?? '';
    if (!existing || String(existing).trim() === '') {
      const pathKey = deriveCourseKey();
      if (pathKey) { host.dataset.batchId = String(pathKey); host.dataset.batch_id = String(pathKey); }
      const qModule = getQueryParam('module') || getQueryParam('course_module_id');
      if (qModule) { host.dataset.moduleId = String(qModule); host.dataset.module_id = String(qModule); }
    }
  })();

  function readContext() {
    const host = document.querySelector('.crs-wrap');
    if (host) {
      const batchId = host.dataset.batchId ?? host.dataset.batch_id ?? '';
      const moduleId = host.dataset.moduleId ?? host.dataset.module_id ?? '';
      if (batchId) return { batch_id: String(batchId) || null, module_id: moduleId || null };
    }
    const pathBatch = deriveCourseKey() || null;
    const qModule = getQueryParam('module') || getQueryParam('course_module_id') || null;
    return { batch_id: pathBatch || null, module_id: qModule || null };
  }

  function showOk(msg){ Swal.fire({ toast:true, position:'top-end', icon:'success', title: msg || 'Done', showConfirmButton:false, timer:2500, timerProgressBar:true }); }
  function showErr(msg){ Swal.fire({ toast:true, position:'top-end', icon:'error', title: msg || 'Something went wrong', showConfirmButton:false, timer:3500, timerProgressBar:true }); }
  function showLoader(v){ if ($loader) $loader.style.display = v ? 'flex' : 'none'; }
  function showEmpty(v){ if ($empty) $empty.style.display = v ? 'block' : 'none'; }
  function showItems(v){ if ($items) $items.style.display = v ? 'block' : 'none'; }
  function escapeHtml(str){ return String(str || '').replace(/[&<>"'`=\/]/g, s => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60","=":"&#x3D;"}[s])); }

  async function apiFetch(url, opts = {}) {
    opts.headers = Object.assign({}, opts.headers || {}, defaultHeaders);
    const res = await fetch(url, opts);
    if (res.status === 401) { try { await Swal.fire({ icon: 'warning', title: 'Session expired', text: 'Please login again.', allowOutsideClick: false }); } catch(e){} location.href = '/'; throw new Error('Unauthorized'); }
    return res;
  }

  function closeAllDropdowns(){ document.querySelectorAll('.sm-more .sm-dd.show').forEach(d => { d.classList.remove('show'); d.setAttribute('aria-hidden', 'true'); }); }
  document.addEventListener('click', () => closeAllDropdowns()); document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAllDropdowns(); });

  /* -----------------------------
     RTE wiring (execCommand-based) — mirrors your previous editor
     - Uses #notice_message_rte (contenteditable) for editing
     - Keeps hidden textarea #notice_message_html as canonical form for submission
     ----------------------------- */
  function wireNoticeRTE(){
    if (!noticeRte) return;
    const el = noticeRte;
    const ph = el.nextElementSibling;
    const hasContent = () => (el.textContent || '').trim().length > 0 || (el.innerHTML||'').trim().length > 0;
    function togglePh(){ el.classList.toggle('has-content', hasContent()); }
    ['input','keyup','paste','blur'].forEach(ev => el.addEventListener(ev, togglePh));
    togglePh();

    // toolbar buttons with data-cmd / data-format
    if (noticeRteToolbar) {
      noticeRteToolbar.querySelectorAll('.tool[data-cmd]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand(b.dataset.cmd,false,null); el.focus(); togglePh(); }));
      noticeRteToolbar.querySelectorAll('.tool[data-format]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand('formatBlock',false,b.dataset.format); el.focus(); togglePh(); }));
    }
    if (btnLinkNotice) {
      btnLinkNotice.addEventListener('click', ()=> {
        const u = prompt('Enter URL (https://…)'); if(u && /^https?:\/\//i.test(u)){ document.execCommand('createLink',false,u); el.focus(); }
      });
    }
  }
  wireNoticeRTE();

  // Sync helper: copy contenteditable -> hidden textarea
  function syncRTEtoTextarea(){
    try {
      if (noticeMessageInput && noticeRte) {
        // preserve innerHTML exactly
        noticeMessageInput.value = (noticeRte.innerHTML || '').trim();
      }
    } catch(e){ console.warn('syncRTEtoTextarea error', e); }
  }

  // When modal opens, initialize editor content from hidden textarea
  try {
    if (createModalEl) {
      createModalEl.addEventListener('show.bs.modal', function(){
        // populate RTE from hidden textarea value
        try { if (noticeRte && noticeMessageInput) { noticeRte.innerHTML = noticeMessageInput.value || ''; noticeRte.classList.toggle('has-content', (noticeRte.innerHTML||'').trim().length>0); } } catch(e){}
      });
    }
  } catch(e){ /* ignore if bootstrap not present */ }

  /* -------------- rest of your existing code (unchanged) -------------- */
  function normalizeAttachments(row) {
    let raw = row.attachment ?? row.attachments ?? row.attachments_json ?? [];
    if (typeof raw === 'string' && raw.trim() !== '') {
      try { raw = JSON.parse(raw); } catch (e) { const possibleUrls = raw.split(/\s*,\s*|\s*\|\|\s*/).filter(Boolean); if (possibleUrls.length) raw = possibleUrls; else raw = []; }
    }
    if (raw && !Array.isArray(raw)) raw = [raw];
    const arr = (raw || []).map((a, idx) => {
      if (typeof a === 'string') { const url = a; const ext = (url.split('?')[0].split('.').pop() || '').toLowerCase(); return { id: `s-${idx}`, url, path: url, name: url.split('/').pop(), mime: '', ext }; }
      const url = a.signed_url || a.url || a.path || a.file_url || a.storage_url || null;
      const name = a.name || a.label || (url ? url.split('/').pop() : (a.original_name || `file-${idx}`));
      const mime = a.mime || a.content_type || a.contentType || '';
      let ext = (a.ext || a.extension || '').toLowerCase(); if (!ext && url) ext = (url.split('?')[0].split('.').pop() || '').toLowerCase();
      return { id: a.id || a.attachment_id || a.file_id || a.storage_key || (`o-${idx}`), url, signed_url: a.signed_url, path: a.path, name, mime, ext, size: a.size || a.file_size || a.filesize || 0, raw: a };
    });
    return arr.filter(it => it && (it.url || it.path || it.signed_url));
  }

  function createItemRow(row) {
    const attachments = normalizeAttachments(row);
    row.attachment = attachments;
    if (typeof row.attachment_count === 'undefined') row.attachment_count = attachments.length;

    const wrapper = document.createElement('div'); wrapper.className = 'sm-item';
    const left = document.createElement('div'); left.className = 'left';
    const icon = document.createElement('div'); icon.className = 'icon'; icon.style.width='44px'; icon.style.height='44px'; icon.style.borderRadius='10px'; icon.style.display='flex'; icon.style.alignItems='center'; icon.style.justifyContent='center'; icon.style.border='1px solid var(--line-strong)'; icon.style.background='linear-gradient(180deg, rgba(0,0,0,0.02), transparent)'; icon.innerHTML = '<i class="fa fa-bullhorn" style="color:var(--secondary-color)"></i>';
    const meta = document.createElement('div'); meta.className='meta'; const title = document.createElement('div'); title.className='title'; title.textContent = row.title || 'Untitled'; const sub = document.createElement('div'); sub.className='sub'; sub.textContent = row.message_html ? 'Has message' : (row.attachment_count ? `${row.attachment_count} attachment(s)` : '—'); meta.appendChild(title); meta.appendChild(sub); left.appendChild(icon); left.appendChild(meta);

    const right = document.createElement('div'); right.className='right'; right.style.display='flex'; right.style.alignItems='center'; right.style.gap='8px';
    const datePill = document.createElement('div'); datePill.className='duration-pill'; datePill.textContent = row.created_at ? new Date(row.created_at).toLocaleDateString() : ''; right.appendChild(datePill);

    // --- Preview button (show only when attachments exist) ---
const previewBtn = document.createElement('button');
previewBtn.className = 'btn btn-outline-primary';
previewBtn.style.minWidth = '80px';
previewBtn.type = 'button';
previewBtn.textContent = 'Preview';

// normalize attachments for this row
const attachmentsArr = Array.isArray(row.attachment) ? row.attachment : [];

if (attachmentsArr.length > 0) {
  // show the button and wire preview
  previewBtn.style.display = 'inline-flex';
  previewBtn.addEventListener('click', () => openFullscreenPreview(row, attachmentsArr, 0));

  // show count badge when multiple attachments
  if (attachmentsArr.length > 1) {
    const badge = document.createElement('span');
    badge.className = 'small text-muted';
    badge.style.marginLeft = '6px';
    badge.textContent = `(${attachmentsArr.length})`;
    previewBtn.appendChild(badge);
  }
} else {
  // hide the preview button when zero attachments
  previewBtn.style.display = 'none';
}

right.appendChild(previewBtn);
 
    const moreWrap = document.createElement('div'); moreWrap.className='sm-more';
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

    const ddBtn = moreWrap.querySelector('.sm-dd-btn'); const dd = moreWrap.querySelector('.sm-dd');
    if (ddBtn && dd) { ddBtn.addEventListener('click', (ev)=>{ ev.stopPropagation(); const isOpen = dd.classList.contains('show'); closeAllDropdowns(); if (!isOpen) { dd.classList.add('show'); dd.setAttribute('aria-hidden','false'); ddBtn.setAttribute('aria-expanded','true'); } }); }

    const viewBtn = moreWrap.querySelector('[data-action="view"]'); if (viewBtn) viewBtn.addEventListener('click', (ev) => { ev.preventDefault(); ev.stopPropagation(); openDetailsModal(row); closeAllDropdowns(); });
    const editBtn = moreWrap.querySelector('[data-action="edit"]'); if (editBtn) editBtn.addEventListener('click', (ev)=>{ ev.preventDefault(); ev.stopPropagation(); enterEditMode(row); closeAllDropdowns(); });

    const delBtn = moreWrap.querySelector('[data-action="delete"]'); if (delBtn) { delBtn.addEventListener('click', async (ev)=>{ ev.preventDefault(); ev.stopPropagation(); const r = await Swal.fire({ title: 'Move to Bin?', text: `Move "${row.title || 'this notice'}" to Bin?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, move it', cancelButtonText: 'Cancel' }); if (!r.isConfirmed){ closeAllDropdowns(); return; } try { const res = await apiFetch(`${apiBase}/${encodeURIComponent(row.id)}`, { method: 'DELETE' }); if (!res.ok) throw new Error('Delete failed: '+res.status); showOk('Moved to Bin'); await loadNotices(); } catch(e){ console.error(e); showErr('Delete failed'); } finally { closeAllDropdowns(); } }); }

    return wrapper;
  }

  function renderList(items){ if (!$items) return; $items.innerHTML=''; if (!items || items.length===0){ showItems(false); showEmpty(true); return; } showEmpty(false); showItems(true); items.forEach(it => $items.appendChild(createItemRow(it))); }
function openDetailsModal(row){
  detailsModal.style.display='block';
  detailsModal.classList.add('show');
  detailsModal.setAttribute('aria-hidden','false');
  const backdrop = document.createElement('div');
  backdrop.className='modal-backdrop fade show';
  backdrop.id='detailsBackdrop';
  document.body.appendChild(backdrop);
  document.body.classList.add('modal-open');
  backdrop.addEventListener('click', closeDetailsModal);

  const attachments = row.attachment && Array.isArray(row.attachment) ? row.attachment : [];
  const attachList = attachments.length ? attachments.map(a=>{
    const name = a.name || (a.url||a.path||'').split('/').pop();
    const size = a.size ? ` (${formatSize(a.size)})` : '';
    return `<div style="display:flex; justify-content:space-between; gap:8px;"><div>${escapeHtml(name)}</div><div style="color:var(--muted-color); font-size:13px;">${escapeHtml(a.mime||a.ext||'')}${size}</div></div>`;
  }).join('') : '<div style="color:var(--muted-color)">No attachments</div>';

  // --- SANITIZE message_html before injecting ---
  let rawMessage = row.message_html || '';
  let safeMessage = rawMessage;
  if (window.DOMPurify && typeof DOMPurify.sanitize === 'function') {
    safeMessage = DOMPurify.sanitize(rawMessage, {ALLOWED_ATTR: ['href','target','style','class'], ALLOWED_URI_REGEXP: /^(?:(?:https?|mailto|tel):|data:image\/)/i});
    // adjust ALLOWED_ATTR / ALLOWED_URI_REGEXP to fit your needs
  } else {
    // Fallback: if DOMPurify not loaded and you trust the content, use rawMessage.
    // Otherwise, escape as before.
    // safeMessage = escapeHtml(rawMessage); // uncomment to fallback to escaped text
  }

  if (detailsBody) {
    detailsBody.innerHTML = `
      <div style="display:flex; flex-direction:column; gap:12px; font-size:15px;">
        <div><strong>Title:</strong> ${escapeHtml(row.title||'Untitled')}</div>
        <div><strong>Message:</strong></div>
        <div style="padding:6px 0;">${safeMessage || '<span style="color:var(--muted-color)">—</span>'}</div>
        <div><strong>Created At:</strong> ${row.created_at ? new Date(row.created_at).toLocaleString() : '—'}</div>
        <div><strong>Created By:</strong> ${escapeHtml(row.creator_name || row.created_by_name || '—')}</div>
        <div><strong>Attachments:</strong> ${attachments.length} file(s)</div>
        <div style="margin-top:6px;">${attachList}</div>
        <div style="color:var(--muted-color); font-size:13px; margin-top:6px;"><strong>ID:</strong> ${escapeHtml(String(row.id||''))}</div>
      </div>
    `;
  }
    if (detailsFooter) { detailsFooter.innerHTML=''; const close = document.createElement('button'); close.className='btn btn-light'; close.textContent='Close'; close.addEventListener('click', closeDetailsModal); detailsFooter.appendChild(close); if (canCreate || canEdit){ const edit = document.createElement('button'); edit.className='btn btn-primary'; edit.textContent='Edit'; edit.addEventListener('click', ()=>{ enterEditMode(row); closeDetailsModal(); }); detailsFooter.appendChild(edit); } }
  }
  function closeDetailsModal(){ detailsModal.classList.remove('show'); detailsModal.style.display='none'; detailsModal.setAttribute('aria-hidden','true'); const bd = document.getElementById('detailsBackdrop'); if (bd) bd.remove(); document.body.classList.remove('modal-open'); if (detailsBody) detailsBody.innerHTML=''; if (detailsFooter) detailsFooter.innerHTML=''; }
  if (detailsClose) detailsClose.addEventListener('click', closeDetailsModal);

  function closeFullscreenPreview(){ const existing = document.querySelector('.sm-fullscreen'); if (!existing) return; const blobUrl = existing.dataset.blobUrl; if (blobUrl) try{ URL.revokeObjectURL(blobUrl); }catch(e){} existing.remove(); document.documentElement.style.overflow=''; document.body.style.overflow=''; }
  function suppressContextMenuWhileOverlay(e){ e.preventDefault(); }

  async function fetchProtectedBlob(url){ const res = await apiFetch(url, { method: 'GET', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': '*/*' } }); if (!res.ok) throw new Error('Failed to fetch file: ' + res.status); return await res.blob(); }

  async function isUrlPublic(url){ try{ const res = await fetch(url, { method: 'HEAD', mode: 'cors' }); return res.ok; } catch(e){ try{ const res2 = await fetch(url, { method: 'GET', headers: { 'Range': 'bytes=0-0' }, mode: 'cors' }); return res2.ok; } catch(err){ return false; } } }

  async function openFullscreenPreview(row, attachments = [], startIndex = 0){ closeFullscreenPreview(); if (!Array.isArray(attachments) || attachments.length===0) return; attachments = attachments.map(a => (typeof a === 'string' ? { url: a } : a || {})); let currentIndex = Math.max(0, Math.min(startIndex||0, attachments.length-1)); const wrap = document.createElement('div'); wrap.className='sm-fullscreen'; wrap.setAttribute('role','dialog'); wrap.setAttribute('aria-modal','true'); wrap.dataset.blobUrl = ''; wrap.dataset.currentIndex = String(currentIndex);
    const inner = document.createElement('div'); inner.className='fs-inner'; const header = document.createElement('div'); header.className='fs-header'; const title = document.createElement('div'); title.className='fs-title'; title.textContent = row.title || ''; const controls = document.createElement('div'); controls.style.display='flex'; controls.style.alignItems='center'; controls.style.gap='8px';
    const prevBtn = document.createElement('button'); prevBtn.className='fs-close btn btn-sm'; prevBtn.type='button'; prevBtn.title='Previous'; prevBtn.innerHTML='◀'; const nextBtn = document.createElement('button'); nextBtn.className='fs-close btn btn-sm'; nextBtn.type='button'; nextBtn.title='Next'; nextBtn.innerHTML='▶'; const idxIndicator = document.createElement('div'); idxIndicator.style.fontSize='13px'; idxIndicator.style.color='var(--muted-color)'; idxIndicator.textContent = `${currentIndex+1} / ${attachments.length}`;
    const closeBtn = document.createElement('button'); closeBtn.className='fs-close'; closeBtn.innerHTML='✕'; closeBtn.setAttribute('aria-label','Close preview'); controls.appendChild(prevBtn); controls.appendChild(idxIndicator); controls.appendChild(nextBtn); header.appendChild(title); header.appendChild(controls); header.appendChild(closeBtn);
    const body = document.createElement('div'); body.className='fs-body'; inner.appendChild(header); inner.appendChild(body); wrap.appendChild(inner); document.body.appendChild(wrap); document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden'; document.addEventListener('contextmenu', suppressContextMenuWhileOverlay);

    async function renderAt(index){ index = Math.max(0, Math.min(index, attachments.length-1)); currentIndex = index; wrap.dataset.currentIndex = index; idxIndicator.textContent = `${currentIndex+1} / ${attachments.length}`; const prevBlob = wrap.dataset.blobUrl; if (prevBlob) { try{ URL.revokeObjectURL(prevBlob);}catch(e){} wrap.dataset.blobUrl=''; }
      const attachment = attachments[currentIndex] || {}; const urlCandidate = attachment.signed_url || attachment.url || attachment.path || null; const mime = (attachment.mime || ''); const ext = ((attachment.ext||'')).toLowerCase(); body.innerHTML='';
      try{
        if (urlCandidate){ const publicOk = await isUrlPublic(urlCandidate); if (publicOk){ if (mime.startsWith('image/') || ['png','jpg','jpeg','gif','webp'].includes(ext)){ const img=document.createElement('img'); img.src=urlCandidate; img.alt=attachment.name||row.title||'image'; img.style.maxWidth='100%'; img.style.maxHeight='100%'; img.style.objectFit='contain'; body.appendChild(img); return; }
            if (mime==='application/pdf' || ext==='pdf'){ const iframe=document.createElement('iframe'); iframe.src = urlCandidate + (urlCandidate.indexOf('#')===-1 ? '#toolbar=0&navpanes=0&scrollbar=0' : '&toolbar=0&navpanes=0&scrollbar=0'); iframe.setAttribute('aria-label', row.title || 'PDF preview'); iframe.style.width='100%'; iframe.style.height='100%'; body.appendChild(iframe); return; }
            if (mime.startsWith('video/') || ['mp4','webm','ogg'].includes(ext)){ const v=document.createElement('video'); v.controls=true; v.style.width='100%'; const s=document.createElement('source'); s.src=urlCandidate; s.type=mime||'video/mp4'; v.appendChild(s); body.appendChild(v); return; }
            const iframe=document.createElement('iframe'); iframe.src=urlCandidate; iframe.setAttribute('aria-label', row.title || 'Preview'); iframe.style.width='100%'; iframe.style.height='100%'; body.appendChild(iframe); return; }
        }
        if (!urlCandidate){ body.innerHTML = '<div>No preview available for this file.</div>'; return; }
        const blob = await fetchProtectedBlob(urlCandidate); const blobUrl = URL.createObjectURL(blob); wrap.dataset.blobUrl = blobUrl;
        if (mime.startsWith('image/') || ['png','jpg','jpeg','gif','webp'].includes(ext)){ const img=document.createElement('img'); img.src=blobUrl; img.alt=attachment.name||row.title||'image'; img.style.maxWidth='100%'; img.style.maxHeight='100%'; img.style.objectFit='contain'; body.appendChild(img); return; }
        if (mime==='application/pdf' || ext==='pdf'){ const iframe=document.createElement('iframe'); iframe.src = blobUrl + '#toolbar=0&navpanes=0&scrollbar=0'; iframe.setAttribute('aria-label', row.title || 'PDF preview'); iframe.style.width='100%'; iframe.style.height='100%'; body.appendChild(iframe); return; }
        if (mime.startsWith('video/') || ['mp4','webm','ogg'].includes(ext)){ const v=document.createElement('video'); v.controls=true; v.style.width='100%'; v.src=blobUrl; body.appendChild(v); return; }
        const iframe=document.createElement('iframe'); iframe.src=blobUrl; iframe.style.width='100%'; iframe.style.height='100%'; body.appendChild(iframe); return;
      } catch(err){ console.error('Preview error', err); body.innerHTML = '<div>Unable to preview this file (permission denied or unsupported).</div>'; }
    }

    prevBtn.addEventListener('click', ()=>{ if (currentIndex>0) renderAt(currentIndex-1); }); nextBtn.addEventListener('click', ()=>{ if (currentIndex<attachments.length-1) renderAt(currentIndex+1); });
    const keyHandler = (e)=>{ if (e.key === 'Escape') { closeFullscreenPreview(); } if (e.key === 'ArrowLeft'){ if (currentIndex>0) renderAt(currentIndex-1); } if (e.key === 'ArrowRight'){ if (currentIndex<attachments.length-1) renderAt(currentIndex+1); } };
    document.addEventListener('keydown', keyHandler);
    function cleanupOnClose(){ try{ const b = wrap.dataset.blobUrl; if (b) URL.revokeObjectURL(b); }catch(e){} document.removeEventListener('keydown', keyHandler); document.removeEventListener('contextmenu', suppressContextMenuWhileOverlay); }
    function wrappedClose(){ cleanupOnClose(); closeFullscreenPreview(); }
    try{ closeBtn.removeEventListener('click', closeFullscreenPreview); }catch(e){}
    closeBtn.addEventListener('click', wrappedClose);
    window._sm_close_fullscreen_preview = ()=>{ cleanupOnClose(); };
    await renderAt(currentIndex);
  }

  function formatSize(bytes){ if (bytes==null) return ''; const units=['B','KB','MB','GB']; let i=0; let b=Number(bytes); while(b>=1024 && i<units.length-1){ b/=1024; i++; } return `${b.toFixed(b<10 && i>0 ? 1 : 0)} ${units[i]}`; }

  async function loadNotices(){ showLoader(true); showItems(false); showEmpty(false); try{ const ctx=readContext(); if (!ctx || !ctx.batch_id) { throw new Error('Batch context required'); }
      const url = `${apiBase}/batch/${encodeURIComponent(ctx.batch_id)}`;
      const res = await apiFetch(url); if (!res.ok) throw new Error('HTTP '+res.status); const json = await res.json().catch(()=>null);
      if (!json || !json.data) throw new Error('Invalid response format');
      const modulesWithNotices = json.data.modules_with_notices || [];
      let allNotices = [];
      modulesWithNotices.forEach(moduleGroup => { if (moduleGroup.notices && Array.isArray(moduleGroup.notices)) { moduleGroup.notices.forEach(notice => { notice.module_title = moduleGroup.module?.title || 'Unknown Module'; notice.module_uuid = moduleGroup.module?.uuid || ''; allNotices.push(notice); }); } });
      const sortVal = $sort ? $sort.value : 'created_desc';
      allNotices.sort((a,b)=>{ const da = a.created_at ? new Date(a.created_at) : new Date(0); const db = b.created_at ? new Date(b.created_at) : new Date(0); if (sortVal === 'created_desc') return db-da; if (sortVal === 'created_asc') return da-db; if (sortVal==='title_asc') return (a.title||'').localeCompare(b.title||''); return 0; });
      renderList(allNotices);
      if (json.data.batch) window.currentBatchContext = json.data.batch;
  } catch(e){ console.error('Load notices error:', e); if ($items) $items.innerHTML = '<div class="sm-empty">Unable to load notices — please refresh.</div>'; showItems(true); showErr('Failed to load notices: ' + (e.message || 'Unknown error')); } finally { showLoader(false); } }

  if ($refresh) $refresh.addEventListener('click', loadNotices); if ($search) $search.addEventListener('keyup', (e)=>{ if (e.key === 'Enter') loadNotices(); }); if ($sort) $sort.addEventListener('change', loadNotices);

  let createModalInstance = null; try { if (window.bootstrap && typeof window.bootstrap.Modal === 'function' && createModalEl) createModalInstance = new bootstrap.Modal(createModalEl); } catch(e){ createModalInstance = null; }

  if ($uploadBtn) { $uploadBtn.type='button'; if (canCreate) { $uploadBtn.style.display='inline-block'; const cleanBtn = $uploadBtn.cloneNode(true); $uploadBtn.parentNode.replaceChild(cleanBtn, $uploadBtn); cleanBtn.addEventListener('click', (ev)=>{ ev.preventDefault(); showCreateModal(); }); } else { $uploadBtn.style.display='none'; } }

  let _manualBackdrop = null;
  function showCreateModal(){ const nFormEl = document.getElementById('noticeCreateForm'); if (nFormEl) { const isEditing = (noticeIdInput && noticeIdInput.value && noticeIdInput.value.trim() !== ''); if (!isEditing) { nFormEl.reset(); nFormEl.classList.remove('was-validated'); } else { nFormEl.classList.remove('was-validated'); } }
    const nAlert = document.getElementById('noticeCreateAlert'); if (nAlert) nAlert.style.display='none'; updateContextDisplay();
     if (createModalInstance && typeof createModalInstance.show === 'function') { createModalInstance.show(); return; } if (!createModalEl) return; _manualBackdrop = document.createElement('div'); _manualBackdrop.className='modal-backdrop fade show'; document.body.appendChild(_manualBackdrop); createModalEl.classList.add('show'); createModalEl.style.display='block'; createModalEl.setAttribute('aria-hidden','false'); document.body.classList.add('modal-open'); document.documentElement.style.overflow='hidden'; if (!createModalEl._fallbackHooksAdded) { createModalEl.querySelectorAll('[data-bs-dismiss], .btn-close').forEach(btn => btn.addEventListener('click', hideCreateModal)); _manualBackdrop.addEventListener('click', hideCreateModal); document.addEventListener('keydown', _fallbackEscHandler); createModalEl._fallbackHooksAdded = true; } }

  function cleanupModalBackdrops() {
    // remove any stray backdrops
    document.querySelectorAll('.modal-backdrop').forEach(b => { try { b.remove(); } catch(e){} });
    // remove modal-open class and restore overflow
    document.body.classList.remove('modal-open');
    try { document.documentElement.style.overflow = ''; document.body.style.overflow = ''; } catch(e){}
  }

  function hideCreateModal(){
    // Prefer bootstrap instance hide if available
    if (createModalInstance && typeof createModalInstance.hide === 'function') {
      try {
        createModalInstance.hide();
      } catch(e) {
        // fall through to manual cleanup
      }
      // ensure any leftover backdrop is removed (bootstrap sometimes leaves it if operations overlap)
      setTimeout(cleanupModalBackdrops, 50);
      // also exit edit mode/state
      exitEditMode();
      return;
    }
    // manual fallback removal (existing logic)
    if (!createModalEl) return;
    createModalEl.classList.remove('show');
    createModalEl.style.display='none';
    createModalEl.setAttribute('aria-hidden','true');
    cleanupModalBackdrops();
    if (_manualBackdrop && _manualBackdrop.parentNode) { _manualBackdrop.parentNode.removeChild(_manualBackdrop); _manualBackdrop = null; }
    try { document.removeEventListener('keydown', _fallbackEscHandler); } catch(e){}
    exitEditMode();
  }
  function _fallbackEscHandler(e){ if (e.key === 'Escape') hideCreateModal(); }

  function enterEditMode(row){ if (noticeIdInput) noticeIdInput.value = row.id || ''; if (noticeMethodInput) noticeMethodInput.value = 'PATCH'; if (noticeTitleInput) noticeTitleInput.value = row.title || ''; 
    // Write into the hidden textarea (existing code uses .value) then sync into RTE
    if (noticeMessageInput) noticeMessageInput.value = row.message_html || '';
    if (noticeRte) { try { noticeRte.innerHTML = row.message_html || ''; noticeRte.classList.toggle('has-content', (noticeRte.innerHTML||'').trim().length>0); } catch(e){} }
    if (noticeVisibility) noticeVisibility.value = row.visibility_scope || 'batch'; if (noticeAttachmentsInput) noticeAttachmentsInput.value=''; if (noticeExistingWrap && noticeExistingList){ const attachments = row.attachment && Array.isArray(row.attachment) ? row.attachment : []; noticeExistingList.innerHTML=''; if (attachments.length){ attachments.forEach((a, idx)=>{ const id = a.id || a.attachment_id || a.file_id || a.storage_key || a.key || (a.url||a.path||'').split('/').pop() || (`file-${idx}`); const name = a.name || (a.url||a.path||'').split('/').pop() || `(attachment ${idx+1})`; const size = a.size ? ` (${formatSize(a.size)})` : ''; const mime = a.mime || a.ext || '';
      const rowEl = document.createElement('div'); rowEl.style.display='flex'; rowEl.style.alignItems='center'; rowEl.style.justifyContent='space-between'; rowEl.style.gap='8px'; rowEl.style.padding='8px'; rowEl.style.borderRadius='6px'; rowEl.style.border='1px dashed var(--line-strong)'; rowEl.style.background='#fbfbfb'; const left = document.createElement('div'); left.style.display='flex'; left.style.flexDirection='column'; left.innerHTML = `<div style="font-weight:600">${escapeHtml(name)}${escapeHtml(size)}</div><div class="small text-muted" style="margin-top:4px;">${escapeHtml(mime)}</div>`; const right = document.createElement('div'); right.style.display='flex'; right.style.alignItems='center'; right.style.gap='8px'; const chk = document.createElement('input'); chk.type='checkbox'; chk.name='remove_attachments[]'; chk.value = id; chk.style.display='none'; chk.title='Marked for removal'; const removeBtn = document.createElement('button'); removeBtn.type='button'; removeBtn.className='btn btn-sm btn-outline-danger'; removeBtn.innerHTML = '<i class="fa fa-trash"></i> Remove'; removeBtn.title='Remove this file on update'; const undoBtn = document.createElement('button'); undoBtn.type='button'; undoBtn.className='btn btn-sm btn-outline-secondary'; undoBtn.style.display='none'; undoBtn.innerHTML = '<i class="fa fa-undo"></i> Undo'; if (a.url || a.signed_url || a.path) { const previewLink = document.createElement('a'); previewLink.href = a.signed_url || a.url || a.path; previewLink.target='_blank'; previewLink.className='small text-primary'; previewLink.style.display='none'; previewLink.style.marginRight='8px'; previewLink.textContent='Preview'; right.appendChild(previewLink); }
      removeBtn.addEventListener('click', ()=>{ chk.checked = true; rowEl.style.opacity='0.45'; rowEl.style.filter='grayscale(0.4)'; removeBtn.style.display='none'; undoBtn.style.display=''; });
      undoBtn.addEventListener('click', ()=>{ chk.checked = false; rowEl.style.opacity=''; rowEl.style.filter=''; removeBtn.style.display=''; undoBtn.style.display='none'; });
      right.appendChild(chk); right.appendChild(removeBtn); right.appendChild(undoBtn); rowEl.appendChild(left); rowEl.appendChild(right); noticeExistingList.appendChild(rowEl); }); } else { noticeExistingList.innerHTML = '<div class="text-muted small">No existing attachments</div>'; } noticeExistingWrap.style.display=''; }
    const modalTitle = createModalEl.querySelector('.modal-title'); if (modalTitle) modalTitle.innerHTML = '<i class="fa fa-pen me-2"></i> Edit Notice'; if (noticeCreateSubmitBtn) noticeCreateSubmitBtn.innerHTML = '<i class="fa fa-save me-1"></i> Update'; updateContextDisplay(); 
    if (createModalInstance && typeof createModalInstance.show === 'function') createModalInstance.show(); else showCreateModal(); }

  function exitEditMode(){ if (noticeIdInput) noticeIdInput.value=''; if (noticeMethodInput) noticeMethodInput.value=''; if (noticeCreateFormEl) noticeCreateFormEl.reset(); if (noticeExistingList) noticeExistingList.innerHTML=''; if (noticeExistingWrap) noticeExistingWrap.style.display='none'; const modalTitle = createModalEl.querySelector('.modal-title'); if (modalTitle) modalTitle.innerHTML = '<i class="fa fa-plus me-2"></i> Add Notice'; if (noticeCreateSubmitBtn) noticeCreateSubmitBtn.innerHTML = '<i class="fa fa-save me-1"></i> Create'; }
  try{ if (createModalEl) createModalEl.addEventListener('hidden.bs.modal', exitEditMode); }catch(e){}

  (function initCreateForm(){ const nForm = noticeCreateFormEl; if (!nForm) return; const nSubmit = noticeCreateSubmitBtn; const nAlert = document.getElementById('noticeCreateAlert'); nForm.addEventListener('submit', async (ev)=>{ ev.preventDefault(); ev.stopPropagation(); if (nAlert) nAlert.style.display='none'; nForm.classList.add('was-validated'); if (!nForm.checkValidity()) return; const editingId = noticeIdInput && noticeIdInput.value ? noticeIdInput.value.trim() : ''; const ctx = readContext(); const batchKey = ctx?.batch_id ?? ctx?.batch_uuid ?? null; if (!editingId && !batchKey){ if (nAlert){ nAlert.innerHTML = 'Missing Batch context — cannot create notice here.'; nAlert.style.display=''; } return; }

    // --- SYNC RTE content to hidden textarea BEFORE building FormData ---
    try { syncRTEtoTextarea(); } catch(e){ console.warn('RTE sync failed', e); }

    const fd = new FormData(); if (ctx && ctx.batch_id) fd.append('batch_uuid', ctx.batch_id); // module if available
    const mod = ctx && ctx.module_id ? String(ctx.module_id).trim() : ''; if (mod){ if (/^\d+$/.test(mod)) fd.append('course_module_id', mod); else fd.append('module_uuid', mod); }
    fd.append('title', noticeTitleInput ? noticeTitleInput.value.trim() : ''); fd.append('message_html', noticeMessageInput ? noticeMessageInput.value.trim() : ''); fd.append('visibility_scope', noticeVisibility ? noticeVisibility.value : 'batch'); fd.append('priority', noticePriority ? noticePriority.value : 'normal'); fd.append('status', noticeStatus ? noticeStatus.value : 'draft'); const files = noticeAttachmentsInput && noticeAttachmentsInput.files ? noticeAttachmentsInput.files : []; for (let i=0;i<files.length;i++) fd.append('attachments[]', files[i]); const toRemove = Array.from(nForm.querySelectorAll('input[name="remove_attachments[]"]:checked')).map(n=>n.value); toRemove.forEach(v=>fd.append('remove_attachments[]', v)); const prevHtml = nSubmit ? nSubmit.innerHTML : (editingId ? 'Update' : 'Create'); if (nSubmit){ nSubmit.disabled = true; nSubmit.innerHTML = `<i class="fa fa-spinner fa-spin me-1"></i> ${editingId ? 'Updating...' : 'Creating...'}`; }
    try{ let endpoint = apiBase; let method = 'POST'; if (editingId){ fd.append('_method','PATCH'); fd.append('id', editingId); endpoint = `${apiBase}/${encodeURIComponent(editingId)}`; method = 'POST'; } else { const ctxBatch = ctx && ctx.batch_id ? ctx.batch_id : null; if (!ctxBatch){ if (nAlert){ nAlert.innerHTML = 'Missing Batch context — cannot create notice here.'; nAlert.style.display=''; } throw new Error('Missing batch context'); } endpoint = `${apiBase}/batch/${encodeURIComponent(ctxBatch)}`; method = 'POST'; }
      const res = await apiFetch(endpoint, { method: method, body: fd }); const json = await res.json().catch(()=>({})); if (!res.ok) { if (res.status === 422 && json.errors) { let msgs=[]; for (const k in json.errors){ if (Array.isArray(json.errors[k])) msgs.push(`${k}: ${json.errors[k].join(', ')}`); else msgs.push(`${k}: ${json.errors[k]}`); } if (nAlert){ nAlert.innerHTML = msgs.map(m=>`<div>${escapeHtml(m)}</div>`).join(''); nAlert.style.display=''; } } else { if (nAlert){ nAlert.innerHTML = `<div>${escapeHtml(json.message || 'Failed to create/update notice')}</div>`; nAlert.style.display=''; } } throw new Error('Save error'); }
      try { if (createModalInstance && typeof createModalInstance.hide === 'function') createModalInstance.hide(); else hideCreateModal(); } catch(e){ hideCreateModal(); } 
      setTimeout(cleanupModalBackdrops, 60);
      exitEditMode(); showOk(json.message || (editingId ? 'Updated' : 'Created')); await loadNotices();
    } catch(err){ console.error('Create/Update failed', err); showErr('Save failed'); }
    finally { if (nSubmit){ nSubmit.disabled = false; nSubmit.innerHTML = prevHtml; } }
  }); if (createModalEl){ try{ createModalEl.addEventListener('show.bs.modal', ()=> updateContextDisplay()); } catch(e){} } })();

  function updateContextDisplay(){ const ctx = readContext(); const ctxText = document.getElementById('noticeContextText'); const ctxErr = document.getElementById('noticeContextError'); const submitBtn = document.getElementById('noticeCreateSubmit'); const isEditing = (noticeIdInput && noticeIdInput.value && noticeIdInput.value.trim() !== ''); if (!ctx || !ctx.batch_id) { if (ctxText) ctxText.textContent = isEditing ? 'Editing (batch unknown)' : 'Missing context'; if (isEditing) { if (ctxErr) ctxErr.style.display='none'; if (submitBtn) submitBtn.disabled=false; return; } if (ctxErr) ctxErr.style.display=''; if (submitBtn) submitBtn.disabled = true; } else { if (ctxText) ctxText.textContent = `Batch: ${ctx.batch_id}` + (ctx.module_id ? ` • Module: ${ctx.module_id}` : ''); if (ctxErr) ctxErr.style.display='none'; if (submitBtn) submitBtn.disabled=false; } }

  (function initBin(){ if (!$btnBin) return; if (!canViewBin) { $btnBin.style.display='none'; return; } else { $btnBin.style.display='inline-block'; }
    async function fetchDeletedNotices(params=''){
      try{ const ctx = readContext(); let url; if (ctx && ctx.batch_id){ url = `${apiBase}/bin/batch/${encodeURIComponent(ctx.batch_id)}`; if (ctx.module_id) url += `?module_uuid=${encodeURIComponent(ctx.module_id)}`; } else { url = '/api/notices/deleted' + (params ? ('?'+params) : ''); }
        const r = await apiFetch(url); if (!r.ok) throw new Error('HTTP '+r.status); const j = await r.json().catch(()=>null); const items = j && (j.data || j.items) ? (j.data || j.items) : (Array.isArray(j) ? j : []);
        return (items || []).map(it => { if (typeof it.attachments === 'string' && it.attachments) { try { it.attachments = JSON.parse(it.attachments); } catch { /* leave */ } } if (typeof it.attachments_json === 'string' && it.attachments_json){ try { it.attachments = JSON.parse(it.attachments_json); } catch {} } return it; }).filter(it => !!(it && (it.deleted_at || it.deletedAt)));
      } catch(e){ console.error('fetchDeletedNotices failed', e); return []; }
    }

    function buildBinTable(items){ if (!document.getElementById('bin-overflow-css')){ const s=document.createElement('style'); s.id='bin-overflow-css'; s.textContent = `.dropdown-menu { overflow: visible !important; white-space: nowrap; } .table-responsive { overflow: visible !important; }`; document.head.appendChild(s); }
      const wrap = document.createElement('div'); wrap.className='sm-card p-3'; const heading = document.createElement('div'); heading.className='d-flex align-items-center justify-content-between mb-2'; heading.innerHTML = `<div class="fw-semibold" style="font-size:15px">Deleted Notices</div><div class="d-flex gap-2"><button id="bin-refresh" class="btn btn-sm btn-primary"><i class="fa fa-rotate-right me-1"></i></button><button id="bin-back" class="btn btn-sm btn-outline-primary"> <i class="fa fa-arrow-left me-1"></i> Back</button></div>`; wrap.appendChild(heading);
      const resp = document.createElement('div'); resp.className='table-responsive'; const table = document.createElement('table'); table.className='table table-hover table-borderless table-sm mb-0'; table.style.fontSize='13px'; table.innerHTML = `<thead class="text-muted" style="font-weight:600; font-size:var(--fs-14);"><tr><th class="text-start">Notice</th><th style="width:140px">Created</th><th style="width:160px">Deleted At</th><th style="width:120px">Attachments</th><th style="width:120px" class="text-end">Actions</th></tr></thead><tbody></tbody>`; const tbody = table.querySelector('tbody'); if (!items || items.length===0){ tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-muted small">No deleted items.</td></tr>`; } else { items.forEach((it, idx)=>{ if (typeof it.attachments === 'string' && it.attachments){ try { it.attachments = JSON.parse(it.attachments); } catch { it.attachments = []; } } if (typeof it.attachments_json === 'string' && it.attachments_json){ try { it.attachments = JSON.parse(it.attachments_json); } catch {} }
            const attCount = Array.isArray(it.attachments) ? it.attachments.length : (it.attachment_count || 0);
            const tr = document.createElement('tr'); tr.style.borderTop = '1px solid var(--line-soft)'; const titleTd = document.createElement('td'); titleTd.innerHTML = `<div class="fw-semibold" style="line-height:1.1;">${escapeHtml(it.title || 'Untitled')}</div><div class="small text-muted mt-1">${escapeHtml(it.slug || '')}</div>`; const createdTd = document.createElement('td'); createdTd.textContent = it.created_at ? new Date(it.created_at).toLocaleString() : '-'; const deletedTd = document.createElement('td'); deletedTd.textContent = it.deleted_at ? new Date(it.deleted_at).toLocaleString() : '-'; const attachTd = document.createElement('td'); attachTd.textContent = `${attCount} file(s)`; const actionsTd = document.createElement('td'); actionsTd.className='text-end'; const dd = document.createElement('div'); dd.className='dropdown d-inline-block'; dd.innerHTML = ` <button class="btn btn-sm btn-light" type="button" id="binDdBtn${idx}" data-bs-toggle="dropdown" aria-expanded="false"><span style="font-size:18px; line-height:1;">⋮</span></button><ul class="dropdown-menu dropdown-menu-end" aria-labelledby="binDdBtn${idx}" style="min-width:160px;"><li><button class="dropdown-item restore-action" type="button"><i class="fa fa-rotate-left me-2"></i> Restore</button></li><li><hr class="dropdown-divider"></li><li><button class="dropdown-item text-danger force-action" type="button"><i class="fa fa-skull-crossbones me-2"></i> Delete permanently</button></li></ul>`; actionsTd.appendChild(dd); tr.appendChild(titleTd); tr.appendChild(createdTd); tr.appendChild(deletedTd); tr.appendChild(attachTd); tr.appendChild(actionsTd); tbody.appendChild(tr);
            const restoreBtn = dd.querySelector('.restore-action'); const forceBtn = dd.querySelector('.force-action'); restoreBtn.addEventListener('click', ()=>{ try{ bootstrap.Dropdown.getOrCreateInstance(dd.querySelector('[data-bs-toggle="dropdown"]')).hide(); }catch{} restoreItem(it); }); forceBtn.addEventListener('click', ()=>{ try{ bootstrap.Dropdown.getOrCreateInstance(dd.querySelector('[data-bs-toggle="dropdown"]')).hide(); }catch{} forceDeleteItem(it); }); }); }
      resp.appendChild(table); wrap.appendChild(resp); setTimeout(()=>{ wrap.querySelector('#bin-refresh')?.addEventListener('click', ()=> openBin()); wrap.querySelector('#bin-back')?.addEventListener('click', ()=> loadNotices()); },0); return wrap; }

    async function restoreItem(item){ const r = await Swal.fire({ title: 'Restore item?', text: `Restore "${item.title || 'this item'}"?`, icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, restore', cancelButtonText: 'Cancel' }); if (!r.isConfirmed) return; try{ const url = `/api/notices/${encodeURIComponent(item.id)}/restore`; const res = await apiFetch(url, { method: 'POST' }); if (!res.ok) throw new Error('Restore failed: '+res.status); showOk('Restored'); await openBin(); } catch(e){ console.error(e); showErr('Restore failed'); } }

    async function forceDeleteItem(item){ const r = await Swal.fire({ title: 'Permanently delete?', html: `Permanently delete "<strong>${escapeHtml(item.title || 'this item')}</strong>"?<br><strong>This cannot be undone.</strong>`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete permanently', cancelButtonText: 'Cancel', focusCancel: true }); if (!r.isConfirmed) return; try{ const url = `/api/notices/${encodeURIComponent(item.id)}/force`; const res = await apiFetch(url, { method: 'DELETE' }); if (!res.ok) throw new Error('Delete failed: '+res.status); showOk('Permanently deleted'); await openBin(); } catch(e){ console.error(e); showErr('Delete failed'); } }

    let _prevContent = null; async function openBin(){ if (!_prevContent && $items) _prevContent = $items.innerHTML; showLoader(true); showEmpty(false); showItems(false); try{ const host = document.querySelector('.crs-wrap'); const params = new URLSearchParams(); const ctx = readContext(); if (ctx && ctx.batch_id) params.set('batch_uuid', ctx.batch_id); if (ctx && ctx.module_id) params.set('module_uuid', ctx.module_id); const items = await fetchDeletedNotices(params.toString()); const tableEl = buildBinTable(items || []); if ($items) { $items.innerHTML = ''; $items.appendChild(tableEl); showItems(true); } const back = document.getElementById('bin-back'); if (back) back.addEventListener('click', (e)=>{ e.preventDefault(); restorePreviousList(); }); const refresh = document.getElementById('bin-refresh'); if (refresh) refresh.addEventListener('click', (e)=>{ e.preventDefault(); openBin(); }); } catch(e){ console.error(e); if ($items) $items.innerHTML = '<div class="sm-empty p-3">Unable to load bin. Try refreshing the page.</div>'; showItems(true); showErr('Failed to load bin'); } finally { showLoader(false); } }

    function restorePreviousList(){ if ($items) { if (_prevContent !== null) { $items.innerHTML = _prevContent; _prevContent = null; } else { if (typeof loadNotices === 'function') loadNotices(); } } }

    $btnBin.addEventListener('click', (ev)=>{ ev.preventDefault(); openBin(); });
  })();
  /* ===== Attachments UI: filelist + drag/drop + clear/remove (paste before the final "})();" ) ===== */
(function wireAttachmentsUI(){
  const dz = document.getElementById('notice_dropzone');
  const fileInput = document.getElementById('notice_attachments');
  const fileListEl = document.getElementById('notice_fileList');
  const clearBtn = document.getElementById('notice_clear_files');
  const chooseLabel = document.getElementById('notice_choose_label');

  if (!dz || !fileInput || !fileListEl) return;

  // Helper: format size
  function fmtSize(bytes){
    if (!bytes && bytes !== 0) return '';
    const units = ['B','KB','MB','GB'];
    let b = Number(bytes), i = 0;
    while (b >= 1024 && i < units.length - 1){ b /= 1024; i++; }
    return `${b.toFixed(b < 10 && i > 0 ? 1 : 0)} ${units[i]}`;
  }

  // Render the current fileInput.files into the UI
  function renderFileList(){
    const files = fileInput.files || [];
    fileListEl.innerHTML = '';
    if (!files.length) { fileListEl.style.display = 'none'; return; }
    fileListEl.style.display = 'flex';
    Array.from(files).forEach((f, idx) => {
      const row = document.createElement('div');
      row.className = 'notice-file-item';
      row.innerHTML = `
        <div class="meta" style="min-width:0; overflow:hidden;">
          <div class="name" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</div>
          <div class="size small text-muted">${fmtSize(f.size)}</div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <button type="button" class="btn btn-sm btn-outline-secondary notice-file-preview" data-idx="${idx}" title="Preview (local)">Preview</button>
          <button type="button" class="btn btn-sm btn-outline-danger notice-file-remove" data-idx="${idx}">Remove</button>
        </div>
      `;
      fileListEl.appendChild(row);
    });

    // wire remove/preview handlers
    fileListEl.querySelectorAll('.notice-file-remove').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const idx = Number(btn.dataset.idx);
        removeFileAtIndex(idx);
      });
    });
    fileListEl.querySelectorAll('.notice-file-preview').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = Number(btn.dataset.idx);
        const f = fileInput.files[idx];
        if (!f) return;
        // For images/videos show in new tab using blob url, otherwise open file name
        const url = URL.createObjectURL(f);
        window.open(url, '_blank');
        // revoke later to avoid memory leak
        setTimeout(()=> URL.revokeObjectURL(url), 30000);
      });
    });
  }

  // Remove a file from input.files by index (using DataTransfer)
  function removeFileAtIndex(idx){
    const dt = new DataTransfer();
    Array.from(fileInput.files).forEach((f, i) => { if (i !== idx) dt.items.add(f); });
    fileInput.files = dt.files;
    renderFileList();
  }

  // Clear all files
  function clearAllFiles(){
    fileInput.value = '';
    // also reset DataTransfer to be safe
    try { fileInput.files = new DataTransfer().files; } catch(e){}
    renderFileList();
  }

  // When native file input changes
  fileInput.addEventListener('change', () => {
    renderFileList();
  });

  // Clear button
  if (clearBtn) clearBtn.addEventListener('click', (e) => {
    e.preventDefault();
    clearAllFiles();
  });

  // Drag/drop wiring
  function prevent(ev){ ev.preventDefault(); ev.stopPropagation(); }
  ['dragenter','dragover'].forEach(ev => {
    dz.addEventListener(ev, (e) => {
      prevent(e);
      dz.classList.add('dragover');
    });
  });
  ['dragleave','dragend','drop'].forEach(ev => {
    dz.addEventListener(ev, (e) => {
      if (ev === 'drop') {
        prevent(e);
        dz.classList.remove('dragover');
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length){
          // Merge dropped files with existing selection
          const newDT = new DataTransfer();
          // keep existing files
          Array.from(fileInput.files || []).forEach(f => newDT.items.add(f));
          // add dropped files
          Array.from(dt.files).forEach(f => newDT.items.add(f));
          fileInput.files = newDT.files;
          renderFileList();
        }
      } else {
        dz.classList.remove('dragover');
      }
    });
  });

  // clicking the whole dropzone triggers the native input (label already exists but support click anywhere)
  dz.addEventListener('click', (e) => {
    // if the click targeted a button or input, ignore
    const tag = (e.target && e.target.tagName || '').toLowerCase();
    if (['button','a','input','label'].includes(tag)) return;

    // fileInput.click();
  });

  // support keyboard accessibility: pressing Enter/Space on the choose label opens input
  if (chooseLabel){
    chooseLabel.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault();
         fileInput.click(); 
        }
    });
  }

  // Small helper to escape HTML in JS text
  function escapeHtml(s){ return String(s || '').replace(/[&<>"'`=\/]/g, function(ch){ return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60','=':'&#x3D;'}[ch]); }); }

  // init (if files already present e.g. from edit mode)
  renderFileList();
})();

  updateContextDisplay(); loadNotices();
})();
</script>
