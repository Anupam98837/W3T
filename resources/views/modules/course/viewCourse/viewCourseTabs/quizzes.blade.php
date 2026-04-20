{{-- resources/views/Quizzes.blade.php --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Reuse study material styles but scoped to quizzes */
.qz-list{max-width:1100px;margin:18px auto}
.qz-card{border-radius:12px;padding:18px}
.qz-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-radius:10px;border:1px solid var(--line-strong);background:transparent}
.qz-item+.qz-item{margin-top:10px}
.qz-item .left{display:flex;gap:12px;align-items:center}
.qz-item .meta{display:flex;flex-direction:column;gap:4px}
.qz-item .meta .title{font-weight:700;color:var(--ink);font-family:var(--font-head)}
.qz-item .meta .sub{color:var(--muted-color);font-size:13px}
.qz-item .btn{padding:6px 10px;border-radius:8px;font-size:13px}
.qz-empty{border:1px dashed var(--line-strong);border-radius:12px;padding:18px;background:transparent;color:var(--muted-color);text-align:center}
.qz-loader{display:flex;align-items:center;gap:8px;color:var(--muted-color)}
.duration-pill{font-size:12px;color:var(--muted-color);background:transparent;border-radius:999px;padding:4px 8px;border:1px solid var(--line-strong)}
.qz-more{position:relative;display:inline-block}
.qz-more .qz-dd-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);padding:6px 8px;border-radius:10px;cursor:pointer;font-size:var(--fs-14)}
.qz-more .qz-dd{position:absolute;top:calc(100% + 6px);right:0;min-width:160px;background:var(--surface);border:1px solid var(--line-strong);box-shadow:var(--shadow-2);border-radius:10px;overflow:hidden;display:none;z-index:1000;padding:6px 0}
.qz-more .qz-dd.show{display:block}
.qz-more .qz-dd a,.qz-more .qz-dd button.dropdown-item{display:flex;align-items:center;gap:10px;padding:10px 12px;text-decoration:none;color:inherit;cursor:pointer;background:transparent;border:0;width:100%;text-align:left;font-size:14px}
.qz-more .qz-dd a:hover,.qz-more .qz-dd button.dropdown-item:hover{background:color-mix(in oklab,var(--muted-color) 6%,transparent)}
.qz-more .qz-dd .divider{height:1px;background:var(--line-strong);margin:6px 0}
@media(max-width:720px){.qz-item{flex-direction:column;align-items:flex-start}.qz-item .right{width:100%;display:flex;justify-content:flex-end;gap:8px}.qz-more .qz-dd{right:6px;left:auto;min-width:160px}}
.modal.show .modal-dialog { max-height: calc(100vh - 48px); }
.modal.show .modal-content { display: flex; flex-direction: column; }
.modal.show .modal-body { overflow: auto; max-height: calc(100vh - 200px); -webkit-overflow-scrolling: touch; }
</style>

<div class="crs-wrap">
  <div class="panel qz-card rounded-1 shadow-1" style="padding:18px;">
    <div class="d-flex align-items-center w-100">
      <h2 class="panel-title d-flex align-items-center gap-2 mb-0">
        <i class="fa fa-question-circle" style="color: var(--primary-color);"></i>
        Quizzes
      </h2>

      <button id="qz-bin" class="btn btn-light text-danger ms-auto" title="Bin / Deleted Items">
        <i class="fa fa-trash text-danger"></i> Bin
      </button>
    </div>

    <div class="panel-head w-100 mt-3">
      <div class="container-fluid px-0">
        <div class="p-3 border rounded-3">
          <div class="row g-3 align-items-center">
            <div class="col-md-5 col-lg-4">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-search text-muted"></i></span>
                <input id="qz-search" type="text" class="form-control" placeholder="Search quizzes...">
              </div>
            </div>

            <div class="col-md-4 col-lg-4 d-flex align-items-center gap-2">
              <select id="qz-sort" class="form-select">
                <option value="" disabled selected>Sort by</option>
                <option value="display_asc">Order</option>
                <option value="created_desc">Newest first</option>
                <option value="created_asc">Oldest first</option>
                <option value="title_asc">Title A → Z</option>
              </select>
              <button id="qz-refresh" class="btn btn-outline-primary d-flex align-items-center gap-1">
                <i class="fa fa-rotate-right"></i> Refresh
              </button>
            </div>

            <div class="col-md-2 col-lg-4 d-flex justify-content-end">
              <button id="qz-assign-btn" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="fa fa-plus"></i> Assign Quiz
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="margin-top:14px;">
      <div id="qz-loader" class="qz-loader" style="display:none;"><div class="spin"></div><div class="text-muted">Loading quizzes…</div></div>
      <div id="qz-empty" class="qz-empty" style="display:none;"><div style="font-weight:600; margin-bottom:6px;">No quizzes yet</div><div class="text-muted small">Assigned quizzes will appear here.</div></div>
      <div id="qz-items" style="display:none; margin-top:8px;"></div>
    </div>
  </div>
</div>

<!-- EDIT QUIZ MODAL -->
<div class="modal fade" id="editQuizModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-pen me-2"></i>Edit Quiz</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editQuizForm" class="needs-validation" novalidate>
        <div class="modal-body">
          <div id="editQuizAlert" class="alert alert-danger small" style="display:none;"></div>
          <input type="hidden" id="edit_quiz_id" name="id" value="">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Quiz Name <span class="text-danger">*</span></label>
              <input id="edit_quiz_name" name="quiz_name" type="text" class="form-control" maxlength="255" required>
              <div class="invalid-feedback">Quiz name required.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Public</label>
              <select id="edit_is_public" name="is_public" class="form-select">
                <option value="no">No</option>
                <option value="yes">Yes</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Max Attempts</label>
              <input id="edit_total_attempts" name="total_attempts" type="number" class="form-control" min="1" value="1">
            </div>
            <div class="col-12">
              <label class="form-label">Result Setup Type</label>
              <select id="edit_result_set_up_type" name="result_set_up_type" class="form-select">
                <option value="Immediately">Immediately</option>
                <option value="After Completion">After Completion</option>
                <option value="Manual">Manual</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button id="editQuizSubmit" type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Update Quiz</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div id="qz-details-modal" class="modal" style="display:none;" aria-hidden="true">
  <div class="modal-dialog" style="max-width:720px; margin:80px auto;">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Quiz Details</h5><button type="button" id="qz-details-close" class="btn btn-light">Close</button></div>
      <div class="modal-body" id="qz-details-body" style="padding:18px;"></div>
      <div class="modal-footer" id="qz-details-footer" style="display:none;"></div>
    </div>
  </div>
</div>

<!-- Assign Quiz Modal -->
<div class="modal fade" id="assignQuizModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-square-check me-2"></i>Assign Quiz</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="aq_mode" value="create">
        <input type="hidden" id="aq_quiz_id" value="">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Course <span class="text-danger">*</span></label>
            <select id="aq_course" class="form-select">
              <option value="">Select a course…</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Module (optional)</label>
            <select id="aq_module" class="form-select">
              <option value="">(Any module)</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Quiz <span class="text-danger">*</span></label>
            <select id="aq_quiz" class="form-select">
              <option value="">Select a quiz…</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Available From</label>
            <input id="aq_from" type="datetime-local" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Available Until</label>
            <input id="aq_until" type="datetime-local" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Max Attempts</label>
            <input id="aq_attempts" type="number" min="1" class="form-control" value="1">
          </div>
          <div class="col-md-6">
            <label class="form-label">Passing Marks (%)</label>
            <input id="aq_passing" type="number" min="0" max="100" class="form-control" value="40">
          </div>
          <div class="col-12">
            <label class="form-label">Additional Options (JSON)</label>
            <textarea id="aq_options" class="form-control" rows="3" placeholder='e.g. {"shuffle":true,"time_limit_min":30}'></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="aq_save" class="btn btn-primary">
          <i class="fa fa-paper-plane me-1"></i>Assign
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Quizzes Assignment Modal -->
<div class="modal fade" id="quizzesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-question me-2"></i>Assign Quizzes</h5>
        <a id="qz_add_btn" href="/admin/quizzes/manage" class="btn btn-primary btn-sm ms-auto" style="display:none"><i class="fa fa-plus me-1"></i> Add Quiz</a>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between mstab-head">
          <div class="left-tools d-flex align-items-center gap-2" style="display:none !important;">
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

<!-- Admin Quiz Results Modal -->
<div class="modal fade" id="adminQuizResultsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-chart-bar me-2"></i>
          Quiz Results — <span id="aqr_quiz_title"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="p-3 border-bottom">
          <div class="d-flex gap-2 align-items-center">
            <div class="btn-group w-100" style="max-width:340px;">
              {{-- FIX: call window.aqrSwitchTab so onclick can reach the function --}}
              <button id="aqr_tab_submitted" type="button"
                class="btn btn-primary"
                onclick="window.aqrSwitchTab('submitted')">
                <i class="fa fa-check-circle me-1"></i> Submitted
              </button>
              <button id="aqr_tab_not_submitted" type="button"
                class="btn btn-light"
                onclick="window.aqrSwitchTab('not_submitted')">
                <i class="fa fa-clock me-1"></i> Not Submitted
              </button>
            </div>
            <div id="aqr_summary" class="ms-auto text-muted small"></div>
          </div>
        </div>
        <div id="aqr_loader" class="text-center py-5" style="display:none;">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="text-muted small mt-2">Loading results…</div>
        </div>
        <div id="aqr_panel_submitted">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13.5px;">
              <thead class="table-light">
                <tr>
                  <th style="width:40px;">#</th>
                  <th>Student</th>
                  <th style="width:120px;">Attempt</th>
                  <th style="width:110px;">Score</th>
                  <th style="width:90px;">Status</th>
                  <th class="text-end" style="width:110px;">Action</th>
                </tr>
              </thead>
              <tbody id="aqr_rows_submitted">
                <tr><td colspan="6" class="text-center py-4 text-muted">—</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div id="aqr_panel_not_submitted" style="display:none;">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13.5px;">
              <thead class="table-light">
                <tr>
                  <th style="width:40px;">#</th>
                  <th>Student</th>
                  <th>Email</th>
                </tr>
              </thead>
              <tbody id="aqr_rows_not_submitted">
                <tr><td colspan="3" class="text-center py-4 text-muted">—</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
(async function(){

  // ============================================================
  // FIX 1: Expose aqrSwitchTab on window so inline onclick works
  // ============================================================
  window.aqrSwitchTab = function(tab) {
    const btnSub    = document.getElementById('aqr_tab_submitted');
    const btnNotSub = document.getElementById('aqr_tab_not_submitted');
    const panelSub  = document.getElementById('aqr_panel_submitted');
    const panelNot  = document.getElementById('aqr_panel_not_submitted');

    if (tab === 'submitted') {
      btnSub?.classList.remove('btn-light');    btnSub?.classList.add('btn-primary');
      btnNotSub?.classList.remove('btn-primary'); btnNotSub?.classList.add('btn-light');
      if (panelSub) panelSub.style.display = '';
      if (panelNot) panelNot.style.display = 'none';
    } else {
      btnSub?.classList.remove('btn-primary');  btnSub?.classList.add('btn-light');
      btnNotSub?.classList.remove('btn-light'); btnNotSub?.classList.add('btn-primary');
      if (panelSub) panelSub.style.display = 'none';
      if (panelNot) panelNot.style.display = '';
    }
  };

  // ============================================================
  // FIX 2: Token detection — check all storage locations robustly
  // ============================================================
  function resolveToken() {
    return (
      localStorage.getItem('token') ||
      localStorage.getItem('auth_token') ||
      localStorage.getItem('access_token') ||
      sessionStorage.getItem('token') ||
      sessionStorage.getItem('auth_token') ||
      sessionStorage.getItem('access_token') ||
      window._authToken ||
      window.TOKEN ||
      ''
    );
  }

  window.TOKEN = resolveToken();

  if (!window.TOKEN) {
    Swal.fire({
      icon:'warning',
      title:'Login required',
      text:'Please sign in to continue.',
      allowOutsideClick:false,
      allowEscapeKey:false
    }).then(()=>{ window.location.href = '/'; });
    return;
  }

  function normalizeRole(raw){
    return String(raw || '')
      .toLowerCase()
      .replace(/[-\s]+/g, '_')
      .replace(/_+/g, '_')
      .replace(/^_+|_+$/g, '');
  }

  function toNum(v){
    if (v === null || v === undefined || v === '') return null;
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
  }

  const getMyRole = async (token) => {
    if (!token) return '';
    try {
      const res = await fetch('/api/auth/my-role', {
        method: 'GET',
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
      });

      if (res.status === 401) {
        // FIX 2: re-check token before redirecting — could have just been set
        const freshToken = resolveToken();
        if (freshToken && freshToken !== token) {
          window.TOKEN = freshToken;
          return await getMyRole(freshToken);
        }
        try { await Swal.fire({ icon:'warning', title:'Session expired', text:'Please login again.' }); } catch(e){}
        location.href = '/';
        return '';
      }

      if (!res.ok) return '';
      const data = await res.json().catch(() => null);
      if (data?.status === 'success' && data?.role) return String(data.role).trim();
    } catch (e) {}
    return '';
  };

  const role = normalizeRole((await getMyRole(window.TOKEN)) || window.role || '');

  const isAdmin      = role === 'super_admin' || role === 'superadmin' || role === 'admin' || role.includes('_admin');
  const isInstructor = role.includes('instructor');
  const isStudent    = role === 'student';

  const canCreate  = isAdmin || isInstructor;
  const canEdit    = isAdmin || isInstructor;
  const canDelete  = isAdmin || isInstructor;
  const canViewBin = isAdmin;

  const apiBase = '/api';
  const defaultHeaders = { 'Accept': 'application/json', 'Authorization': 'Bearer ' + window.TOKEN };

  function escapeHtml(str){
    return String(str || '').replace(/[&<>"'`=\/]/g, s => (
      {"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60;","=":"&#x3D;"}[s]
    ));
  }

  function showOk(msg){
    Swal.fire({ toast:true, position:'top-end', icon:'success', title: msg || 'Done', showConfirmButton:false, timer:2000 });
  }
  function showErr(msg){
    Swal.fire({ toast:true, position:'top-end', icon:'error', title: msg || 'Something went wrong', showConfirmButton:false, timer:3000 });
  }

  async function apiFetch(url, opts = {}) {
    // Always use the latest token (it may have been refreshed)
    opts.headers = Object.assign({}, opts.headers || {}, {
      'Accept': 'application/json',
      'Authorization': 'Bearer ' + resolveToken()
    });
    const res = await fetch(url, opts);
    if (res.status === 401) {
      // FIX 2: one more token re-check before giving up
      const fresh = resolveToken();
      if (fresh && fresh !== window.TOKEN) {
        window.TOKEN = fresh;
        opts.headers['Authorization'] = 'Bearer ' + fresh;
        const retry = await fetch(url, opts);
        if (retry.status !== 401) return retry;
      }
      try { await Swal.fire({ icon:'warning', title:'Session expired', text:'Please login again.' }); } catch(e){}
      location.href = '/';
      throw new Error('Unauthorized');
    }
    return res;
  }

  // ----------------------------
  // DOM refs (main)
  // ----------------------------
  const $loader    = document.getElementById('qz-loader');
  const $empty     = document.getElementById('qz-empty');
  const $items     = document.getElementById('qz-items');
  const $search    = document.getElementById('qz-search');
  const $sort      = document.getElementById('qz-sort');
  const $refresh   = document.getElementById('qz-refresh');
  const $btnBin    = document.getElementById('qz-bin');
  const $assignBtn = document.getElementById('qz-assign-btn');

  const detailsModal  = document.getElementById('qz-details-modal');
  const detailsBody   = document.getElementById('qz-details-body');
  const detailsClose  = document.getElementById('qz-details-close');
  const detailsFooter = document.getElementById('qz-details-footer');

  const editModalEl       = document.getElementById('editQuizModal');
  const editQuizIdInput   = document.getElementById('edit_quiz_id');
  const editQuizName      = document.getElementById('edit_quiz_name');
  const editIsPublic      = document.getElementById('edit_is_public');
  const editTotalAttempts = document.getElementById('edit_total_attempts');
  const editResultSetup   = document.getElementById('edit_result_set_up_type');
  const editQuizForm      = document.getElementById('editQuizForm');
  const editQuizSubmit    = document.getElementById('editQuizSubmit');
  const editQuizAlert     = document.getElementById('editQuizAlert');

  const qz_q        = document.getElementById('qz_q'),
        qz_per      = document.getElementById('qz_per'),
        qz_apply    = document.getElementById('qz_apply'),
        qz_assigned = document.getElementById('qz_assigned'),
        qz_rows     = document.getElementById('qz_rows'),
        qz_loader   = document.getElementById('qz_loader'),
        qz_meta     = document.getElementById('qz_meta'),
        qz_pager    = document.getElementById('qz_pager');

  if (isStudent && $assignBtn) {
    $assignBtn.style.display = 'none';
    $assignBtn.disabled = true;
  }

  // ----------------------------
  // Context helpers
  // ----------------------------
  const deriveCourseKey = () => {
    const parts = location.pathname.split('/').filter(Boolean);
    const idx = parts.findIndex(p => p === 'batches' || p === 'batch');
    if (idx >= 0 && parts[idx+1]) return parts[idx+1];
    const last = parts.at(-1);
    if (last === 'view') return parts.at(-2);
    return last;
  };

  const deriveModuleUuid = () => {
    try {
      const url = new URL(window.location.href);
      const uuidRe = /[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i;
      const candidates = ['module_uuid','module','moduleId','module_id','course_module_uuid','course_module_id','mid','m'];

      for (const key of candidates) {
        const v = url.searchParams.get(key);
        if (v && uuidRe.test(String(v).trim())) return String(v).trim();
      }

      const parts = url.pathname.split('/').filter(Boolean);
      const modulesIdx = parts.findIndex(p => ['module','modules'].includes(String(p).toLowerCase()));
      if (modulesIdx !== -1 && parts[modulesIdx + 1] && uuidRe.test(parts[modulesIdx + 1])) return parts[modulesIdx + 1];
      const anyPath = parts.find(p => uuidRe.test(p));
      if (anyPath) return anyPath;

      const hash = (url.hash || '').replace('#','');
      if (hash && uuidRe.test(hash)) return hash;

      for (const key of candidates) {
        const v = url.searchParams.get(key);
        if (v && String(v).trim() !== '') return String(v).trim();
      }
      return null;
    } catch (e) { return null; }
  };

  window.deriveModuleUuid = deriveModuleUuid;

  function getModuleFilter() {
    const mod = String(deriveModuleUuid() || '').trim();
    if (!mod) return null;
    if (/^\d+$/.test(mod)) return { course_module_id: String(Number(mod)) };
    return { course_module_uuid: mod };
  }

  function moduleQueryString(){
    const mf = getModuleFilter();
    if (!mf) return '';
    const p = new URLSearchParams();
    if (mf.course_module_uuid) {
      p.set('course_module_uuid', String(mf.course_module_uuid).trim());
      p.set('module_uuid', String(mf.course_module_uuid).trim());
    } else if (mf.course_module_id != null) {
      p.set('course_module_id', String(mf.course_module_id).trim());
      p.set('module_id', String(mf.course_module_id).trim());
    }
    return p.toString();
  }

  function passesModuleFilter(item){
    const mf = getModuleFilter();
    if (!mf) return true;
    const cmid = item.course_module_id ?? item.module_id ?? item.pivot?.course_module_id ?? item.batch_quiz?.course_module_id ?? null;
    const cmuuid = item.course_module_uuid ?? item.module_uuid ?? item.pivot?.course_module_uuid ?? item.batch_quiz?.course_module_uuid ?? null;
    if (mf.course_module_id != null) return String(cmid ?? '') === String(mf.course_module_id);
    if (mf.course_module_uuid) return String(cmuuid ?? '').toLowerCase() === String(mf.course_module_uuid).toLowerCase();
    return true;
  }

  (function ensureBatchInDomFromUrl() {
    const host = document.querySelector('.crs-wrap');
    if (!host) return;
    const existing = host.dataset.batchId ?? host.dataset.batch_id ?? '';
    if (!existing || String(existing).trim() === '') {
      const pathKey = deriveCourseKey();
      if (pathKey) { host.dataset.batchId = String(pathKey); host.dataset.batch_id = String(pathKey); }
    }
  })();

  function readContext(){
    const host = document.querySelector('.crs-wrap');
    if (host) {
      const batchId = host.dataset.batchId ?? host.dataset.batch_id ?? '';
      if (batchId) return { batch_id: String(batchId) || null };
    }
    return { batch_id: deriveCourseKey() || null };
  }

  function showLoader(v){ if ($loader) $loader.style.display = v ? 'flex' : 'none'; }
  function showEmpty(v){ if ($empty) $empty.style.display  = v ? 'block' : 'none'; }
  function showItems(v){ if ($items) $items.style.display  = v ? 'block' : 'none'; }

  function closeAllDropdowns(){
    document.querySelectorAll('.qz-more .qz-dd.show').forEach(d => {
      d.classList.remove('show');
      d.setAttribute('aria-hidden','true');
      d.previousElementSibling?.setAttribute('aria-expanded','false');
    });
  }
  document.addEventListener('click', () => closeAllDropdowns());
  document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') closeAllDropdowns(); });

  function normalizeServerResponse(json) {
    if (!json) return { items: [], pagination: { total:0, per_page:20, current_page:1, last_page:1 } };
    let items = [];
    if (Array.isArray(json.data)) items = json.data;
    else if (json.data && (Array.isArray(json.data.items) || Array.isArray(json.data.quizzes))) items = json.data.items || json.data.quizzes;
    else if (json.items) items = json.items;
    else if (json.data && json.data.quizzes && Array.isArray(json.data.quizzes)) items = json.data.quizzes;

    items = items.map(it => {
      if (it.quiz && typeof it.quiz === 'object') {
        const q = Object.assign({}, it.quiz);
        return Object.assign({}, it, { title: it.title || q.title || q.quiz_name, excerpt: it.excerpt || q.excerpt || q.quiz_description || q.description, quiz: q });
      }
      if (!it.quiz) {
        const q = {};
        ['id','uuid','quiz_name','title','quiz_description','excerpt','total_questions','total_time','is_public','quiz_img','instructions','note','status','total_attempts','result_set_up_type'].forEach(k => {
          if (it[k] !== undefined) q[k] = it[k];
          if (k === 'quiz_name' && it['title'] !== undefined && !q['quiz_name']) q['quiz_name'] = it['title'];
        });
        if (!q.title) q.title = it.title || it.quiz_name || it.quiz?.title;
        if (!q.excerpt) q.excerpt = it.excerpt || it.quiz_description;
        it.quiz = Object.keys(q).length ? q : (it.quiz || {});
      }
      return it;
    });

    const pagination = (json.pagination || (json.data && json.data.pagination) || { total: items.length, per_page:20, current_page:1, last_page:1 });
    return { items, pagination };
  }

  // ----------------------------
  // MAIN LIST
  // ----------------------------
  let _assignedCache = [];

  function applyFiltersAndRender(){
    let list = (_assignedCache || []).slice();

    const q = ($search?.value || '').trim().toLowerCase();
    if (q) {
      list = list.filter(it => {
        const t = String(it.title || it.quiz?.title || it.quiz?.quiz_name || '').toLowerCase();
        const d = String(it.excerpt || it.quiz?.excerpt || it.quiz?.quiz_description || '').toLowerCase();
        return t.includes(q) || d.includes(q);
      });
    }

    const sortVal = ($sort ? $sort.value : '') || 'display_asc';
    if (sortVal === 'display_asc') list.sort((a,b)=> Number(a.display_order||0) - Number(b.display_order||0));
    else if (sortVal === 'created_desc') list.sort((a,b)=> new Date(b.assigned_at||b.created_at||0) - new Date(a.assigned_at||a.created_at||0));
    else if (sortVal === 'created_asc') list.sort((a,b)=> new Date(a.assigned_at||a.created_at||0) - new Date(b.assigned_at||b.created_at||0));
    else if (sortVal === 'title_asc') list.sort((a,b)=> String(a.title||'').localeCompare(String(b.title||'')));

    renderList(list);
  }

  function createQuizRow(row) {
    const wrapper = document.createElement('div');
    wrapper.className = 'qz-item';
    wrapper.dataset.quizId = String(row.id || row.quiz?.id || row.quiz_id || row.uuid || row.quiz?.uuid || '');

    const left = document.createElement('div');
    left.className = 'left';

    const icon = document.createElement('div');
    icon.style.cssText = 'width:44px;height:44px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--line-strong)';
    icon.innerHTML = '<i class="fa fa-list" style="color:var(--secondary-color)"></i>';

    const meta = document.createElement('div');
    meta.className = 'meta';

    const title = document.createElement('div');
    title.className = 'title';
    title.textContent = row.title || row.quiz?.title || row.quiz?.quiz_name || 'Untitled';

    const sub = document.createElement('div');
    sub.className = 'sub';
    const excerpt = row.excerpt || row.quiz?.excerpt || row.quiz?.quiz_description || '';
    sub.innerHTML = escapeHtml(excerpt).slice(0,200) || (row.quiz?.total_questions ? `${row.quiz.total_questions} Qs • ${row.quiz.total_time || '—'} mins` : '—');

    const creatorInfo = document.createElement('div');
    creatorInfo.style.cssText = 'font-size:12px;color:var(--muted-color);margin-top:4px;display:flex;align-items:center;gap:6px';
    creatorInfo.innerHTML = `<i class="fa fa-user" style="font-size:10px;"></i><span>${escapeHtml(row.created_by_name || row.quiz?.created_by_name || 'Unknown')}</span>`;

    meta.appendChild(title);
    meta.appendChild(sub);
    meta.appendChild(creatorInfo);
    left.appendChild(icon);
    left.appendChild(meta);

    const right = document.createElement('div');
    right.className = 'right';
    right.style.cssText = 'display:flex;align-items:center;gap:8px';

    const datePill = document.createElement('div');
    datePill.className = 'duration-pill';
    datePill.textContent = row.assigned_at ? new Date(row.assigned_at).toLocaleDateString() : '';
    right.appendChild(datePill);

    const attemptsAllowed = toNum(row.attempt_allowed ?? row.attempts_allowed ?? row.quiz?.total_attempts ?? null);
    const attemptsUsed    = toNum(row.attempt_used ?? row.attempts_used ?? row.used_attempts ?? null);
    let canAttemptMore = true;

    if (attemptsAllowed !== null && attemptsAllowed > 0) {
      const pill = document.createElement('div');
      pill.className = 'duration-pill';
      pill.textContent = `${attemptsUsed ?? 0}/${attemptsAllowed}`;
      pill.title = 'Attempts used / allowed';
      right.appendChild(pill);
      canAttemptMore = (attemptsUsed ?? 0) < attemptsAllowed;
    } else if (attemptsAllowed === 0 && attemptsUsed !== null) {
      const pill = document.createElement('div');
      pill.className = 'duration-pill';
      pill.textContent = `${attemptsUsed}/∞`;
      pill.title = 'Attempts used / unlimited';
      right.appendChild(pill);
    }

    if (isStudent && canAttemptMore) {
      const startBtn = document.createElement('button');
      startBtn.className = 'btn btn-primary';
      startBtn.style.minWidth = '80px';
      startBtn.textContent = 'Start Quiz';
      startBtn.addEventListener('click', ()=> startQuiz(row));
      right.appendChild(startBtn);
    } else if (isStudent && !canAttemptMore) {
      const overBtn = document.createElement('button');
      overBtn.className = 'btn btn-light';
      overBtn.disabled = true;
      overBtn.textContent = 'Attempts Over';
      right.appendChild(overBtn);
    }

    const moreWrap = document.createElement('div');
    moreWrap.className = 'qz-more';

    // FIX: Students no longer get a "Result" dropdown item — they are redirected directly via startQuiz.
    // Admin/Instructor get "Results" (admin results modal).
    const canAdminResults = isAdmin || isInstructor;

    moreWrap.innerHTML = `
      <button class="qz-dd-btn" aria-haspopup="true" aria-expanded="false" title="More">⋮</button>
      <div class="qz-dd" role="menu" aria-hidden="true">
        <a href="#" data-action="view"><i class="fa fa-eye"></i><span>View</span></a>
        ${canAdminResults ? `<a href="#" data-action="admin-results"><i class="fa fa-chart-bar"></i><span>Results</span></a>` : ''}
        ${canEdit        ? `<a href="#" data-action="edit"><i class="fa fa-pen"></i><span>Edit</span></a>` : ''}
        ${canDelete      ? `<div class="divider"></div><a href="#" data-action="delete" class="text-danger"><i class="fa fa-trash"></i><span>Move to Bin</span></a>` : ''}
      </div>
    `;

    right.appendChild(moreWrap);

    const ddBtn = moreWrap.querySelector('.qz-dd-btn');
    const dd    = moreWrap.querySelector('.qz-dd');

    ddBtn?.addEventListener('click', (ev) => {
      ev.stopPropagation();
      closeAllDropdowns();
      if (!dd.classList.contains('show')) {
        dd.classList.add('show');
        dd.setAttribute('aria-hidden','false');
        ddBtn.setAttribute('aria-expanded','true');
      }
    });

    moreWrap.querySelector('[data-action="view"]')?.addEventListener('click', (ev)=>{ ev.preventDefault(); openQzDetails(row); closeAllDropdowns(); });
    moreWrap.querySelector('[data-action="edit"]')?.addEventListener('click', (ev)=>{ ev.preventDefault(); enterQzEditMode(row); closeAllDropdowns(); });

    moreWrap.querySelector('[data-action="admin-results"]')?.addEventListener('click', (ev) => {
      ev.preventDefault(); ev.stopPropagation();
      closeAllDropdowns();
      openAdminQuizResults(row);
    });

    const delBtn = moreWrap.querySelector('[data-action="delete"]');
    if (delBtn) {
      delBtn.addEventListener('click', async (ev)=> {
        ev.preventDefault(); ev.stopPropagation();

        const confirmed = await Swal.fire({
          title: 'Move to Bin?',
          text: `Move "${row.title || row.quiz?.title || 'this quiz'}" to bin?`,
          icon: 'warning', showCancelButton: true,
          confirmButtonText: 'Yes, move to bin', cancelButtonText: 'Cancel'
        });

        if (!confirmed.isConfirmed) { closeAllDropdowns(); return; }

        try {
          let quizId = String(wrapper.dataset.quizId || row.id || row.quiz?.id || row.uuid || row.quiz_id || '').trim();
          if (!quizId || quizId === 'undefined' || quizId === 'null') { showErr('Cannot delete: missing quiz ID'); closeAllDropdowns(); return; }

          const ctx = readContext();
          const inBatchContext = !!(ctx && ctx.batch_id);
          const hasBatchRelation = row.batch_quiz_id || (typeof row.assign_status_flag !== 'undefined');

          if (hasBatchRelation && inBatchContext) {
            try {
              const payload = new FormData();
              if (row.batch_quiz_id) payload.append('batch_quiz_id', row.batch_quiz_id);
              else payload.append('quiz_id', quizId);
              payload.append('assign_status', 0);
              payload.append('publish_to_students', 0);
              payload.append('unassigned_at', new Date().toISOString());

              const res = await apiFetch(`${apiBase}/batches/${encodeURIComponent(ctx.batch_id)}/quizzes/update`, { method:'PATCH', body: payload });
              if (!res.ok) throw new Error(`HTTP ${res.status}`);
              showOk('Unassigned from batch'); await loadQuizzes(); return;
            } catch (batchErr) { console.warn('Batch unassign failed, falling back:', batchErr); }
          }

          const res = await apiFetch(`${apiBase}/quizz/${encodeURIComponent(quizId)}`, { method: 'DELETE' });
          if (!res.ok) { const j = await res.json().catch(()=>({})); throw new Error(j?.message || `HTTP ${res.status}`); }
          showOk('Moved to bin'); await loadQuizzes();
        } catch (e) { showErr('Move to bin failed: ' + (e.message || 'Unknown error')); }
        finally { closeAllDropdowns(); }
      });
    }

    wrapper.appendChild(left);
    wrapper.appendChild(right);
    return wrapper;
  }

  function startQuiz(row) {
    try {
      const quizUuid = row.uuid || row.quiz?.uuid || row.quiz?.id || row.id;
      if (!quizUuid) { showErr("Missing quiz UUID"); return; }
      const batchQuizUuid = row.batch_quizzes_uuid || row.batch_quiz_uuid || row.batch_quiz?.uuid || null;
      if (!batchQuizUuid) { showErr("Missing batch quiz UUID"); console.warn("Row data:", row); return; }
      // FIX 2: append token so the exam page can pick it up without relying on storage race condition
      const token = resolveToken();
      const finalUrl = `/exam/${encodeURIComponent(quizUuid)}?batch=${encodeURIComponent(batchQuizUuid)}${token ? '&_t=' + encodeURIComponent(token) : ''}`;
      window.location.href = finalUrl;
    } catch (e) { console.error("startQuiz error", e); showErr("Failed to start quiz"); }
  }

  function renderList(items){
    if (!$items) return;
    $items.innerHTML = '';
    if (!items || items.length === 0){ showItems(false); showEmpty(true); return; }
    showEmpty(false); showItems(true);
    const frag = document.createDocumentFragment();
    items.forEach(it => frag.appendChild(createQuizRow(it)));
    $items.appendChild(frag);
  }

  // ---------- Details Modal ----------
  function openQzDetails(row) {
    if (!detailsModal) return;
    detailsModal.style.display = 'block';
    detailsModal.classList.add('show');
    detailsModal.setAttribute('aria-hidden','false');
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show'; backdrop.id = 'qzBackdrop';
    document.body.appendChild(backdrop); document.body.classList.add('modal-open');
    backdrop.addEventListener('click', closeQzDetails);

    const quiz = row.quiz || {};
    const lines = [
      `<div><strong>Title:</strong> ${escapeHtml(row.title || quiz.title || quiz.quiz_name || '')}</div>`,
      `<div><strong>Description:</strong> ${escapeHtml(row.excerpt || quiz.excerpt || quiz.quiz_description || '')}</div>`,
      `<div><strong>Assigned At:</strong> ${row.assigned_at ? new Date(row.assigned_at).toLocaleString() : '—'}</div>`,
      `<div><strong>Available:</strong> ${row.available_from ? new Date(row.available_from).toLocaleDateString() : 'Always'} → ${row.available_until ? new Date(row.available_until).toLocaleDateString() : '—'}</div>`,
      `<div><strong>Total Questions:</strong> ${quiz.total_questions ?? '—'}</div>`,
      `<div><strong>Time:</strong> ${quiz.total_time ? quiz.total_time + ' mins' : '—'}</div>`,
      `<div><strong>Attempts Allowed:</strong> ${row.attempt_allowed ?? quiz.total_attempts ?? 1}</div>`,
      `<div><strong>Visibility:</strong> ${quiz.is_public === 'yes' ? 'Public' : 'Private'}</div>`,
      `<div><strong>Result Setup:</strong> ${quiz.result_set_up_type || 'Immediately'}</div>`,
    ];

    if (detailsBody) detailsBody.innerHTML = `<div style="display:flex;flex-direction:column;gap:10px">${lines.join('')}</div>`;
    if (detailsFooter) {
      detailsFooter.style.display = '';
      detailsFooter.innerHTML = '';
      const close = document.createElement('button'); close.className='btn btn-light'; close.textContent='Close'; close.addEventListener('click', closeQzDetails);
      detailsFooter.appendChild(close);
      if (canEdit) {
        const edit = document.createElement('button'); edit.className='btn btn-primary'; edit.textContent='Edit';
        edit.addEventListener('click', ()=>{ enterQzEditMode(row); closeQzDetails(); });
        detailsFooter.appendChild(edit);
      }
    }
  }

  function closeQzDetails(){
    if (!detailsModal) return;
    detailsModal.classList.remove('show'); detailsModal.style.display='none'; detailsModal.setAttribute('aria-hidden','true');
    document.getElementById('qzBackdrop')?.remove(); document.body.classList.remove('modal-open');
    if (detailsBody) detailsBody.innerHTML=''; if (detailsFooter) detailsFooter.innerHTML='';
  }
  detailsClose?.addEventListener('click', closeQzDetails);

  // ---------- Edit Quiz ----------
  function enterQzEditMode(row){
    if (!editQuizForm) return;
    editQuizIdInput.value   = row.id || row.quiz?.id || '';
    editQuizName.value      = row.quiz?.quiz_name || row.title || '';
    editIsPublic.value      = row.quiz?.is_public || 'no';
    editTotalAttempts.value = row.quiz?.total_attempts || 1;
    editResultSetup.value   = row.quiz?.result_set_up_type || 'Immediately';
    if (editQuizAlert) editQuizAlert.style.display='none';
    editModalEl._editingRow = row;
    try {
      if (window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(editModalEl).show();
    } catch(e){ editModalEl.classList.add('show'); editModalEl.style.display='block'; }
  }

  editQuizForm?.addEventListener('submit', async (ev)=> {
    ev.preventDefault(); ev.stopPropagation();
    editQuizForm.classList.add('was-validated');
    if (!editQuizForm.checkValidity()) return;

    const quizId = editQuizIdInput.value;
    if (!quizId) { if (editQuizAlert) { editQuizAlert.innerText='Missing quiz ID'; editQuizAlert.style.display=''; } return; }

    try {
      editQuizSubmit.disabled = true;
      const res = await apiFetch(`/api/quizz/${encodeURIComponent(quizId)}`, {
        method: 'PATCH', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          quiz_name: editQuizName.value.trim(),
          is_public: editIsPublic.value,
          total_attempts: parseInt(editTotalAttempts.value) || 1,
          result_set_up_type: editResultSetup.value
        })
      });
      const j = await res.json().catch(()=>({}));
      if (!res.ok) { if (editQuizAlert) { editQuizAlert.innerHTML=escapeHtml(j.message||'Quiz update failed'); editQuizAlert.style.display=''; } throw new Error('Quiz update failed'); }

      try { if (window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(editModalEl).hide(); }
      catch(e){ editModalEl.classList.remove('show'); editModalEl.style.display='none'; }

      showOk('Quiz updated successfully'); await loadQuizzes();
    } catch(e){ console.error('Quiz save failed', e); showErr('Save failed: ' + (e.message || '')); }
    finally { editQuizSubmit.disabled = false; }
  });

  // ---------- Bin ----------
  async function fetchDeletedQuizzes(params = '') {
    try {
      const ctx = readContext();
      const candidates = [];
      if (ctx?.batch_id) candidates.push(`${apiBase}/quizz/bin/batch/${encodeURIComponent(ctx.batch_id)}`);
      candidates.push(`${apiBase}/quizz/deleted`, `${apiBase}/quizz?deleted=1`);

      for (const base of candidates) {
        const url = params ? (base + (base.includes('?') ? '&' : '?') + params) : base;
        try {
          const r = await apiFetch(url);
          if (!r.ok) continue;
          const j = await r.json().catch(() => null);
          if (!j) return [];
          if (Array.isArray(j)) return j;
          if (Array.isArray(j.data)) return j.data;
          if (Array.isArray(j.items)) return j.items;
          if (j.data && Array.isArray(j.data.items)) return j.data.items;
          if (Array.isArray(j.quizzes)) return j.quizzes;
          const arr = Object.values(j).find(v => Array.isArray(v));
          if (Array.isArray(arr)) return arr;
          return [];
        } catch(inner){ continue; }
      }
      return [];
    } catch(e){ console.error('fetchDeletedQuizzes failed', e); return []; }
  }

  function buildBinTable(items) {
    const wrap = document.createElement('div'); wrap.className='qz-card p-3';
    const heading = document.createElement('div'); heading.className='d-flex align-items-center justify-content-between mb-2';
    heading.innerHTML = `<div class="fw-semibold" style="font-size:15px">Deleted Quizzes</div>
      <div class="d-flex gap-2">
        <button id="qz-bin-refresh" class="btn btn-sm btn-primary"><i class="fa fa-rotate-right"></i></button>
        <button id="qz-bin-back" class="btn btn-sm btn-outline-primary"><i class="fa fa-arrow-left me-1"></i> Back</button>
      </div>`;
    wrap.appendChild(heading);

    const resp = document.createElement('div'); resp.className='table-responsive';
    const table = document.createElement('table'); table.className='table table-hover table-borderless table-sm mb-0'; table.style.fontSize='13px';
    table.innerHTML = `<thead class="text-muted"><tr><th>Quiz</th><th style="width:160px">Deleted At</th><th style="width:120px" class="text-end">Actions</th></tr></thead><tbody></tbody>`;
    const tbody = table.querySelector('tbody');

    if (!items?.length) {
      tbody.innerHTML = `<tr><td colspan="3" class="text-center py-3 text-muted small">No deleted quizzes.</td></tr>`;
    } else {
      items.forEach((it, idx) => {
        const tr = document.createElement('tr'); tr.style.borderTop='1px solid var(--line-soft)';
        const titleTd = document.createElement('td');
        titleTd.innerHTML = `<div class="fw-semibold">${escapeHtml(it.title||it.quiz?.title||'Untitled')}</div><div class="small text-muted mt-1">${escapeHtml(it.excerpt||'')}</div>`;
        const deletedTd = document.createElement('td'); deletedTd.textContent = it.deleted_at ? new Date(it.deleted_at).toLocaleString() : '-';
        const actionsTd = document.createElement('td'); actionsTd.className='text-end';
        const dd = document.createElement('div'); dd.className='dropdown d-inline-block';
        dd.innerHTML = `<button class="btn btn-sm btn-light" id="binDd${idx}" data-bs-toggle="dropdown"><span style="font-size:18px;line-height:1;">⋮</span></button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="binDd${idx}" style="min-width:160px;">
            <li><button class="dropdown-item restore-action" type="button"><i class="fa fa-rotate-left me-2"></i> Restore</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item text-danger force-action" type="button"><i class="fa fa-skull-crossbones me-2"></i> Delete permanently</button></li>
          </ul>`;
        actionsTd.appendChild(dd);
        tr.appendChild(titleTd); tr.appendChild(deletedTd); tr.appendChild(actionsTd);
        tbody.appendChild(tr);

        dd.querySelector('.restore-action').addEventListener('click', async ()=> {
          try {
            const res = await apiFetch(`/api/quizz/${encodeURIComponent(it.id)}/restore`, { method:'PATCH' });
            if (!res.ok) { const j = await res.json().catch(()=>({})); throw new Error(j?.message||'Restore failed'); }
            showOk('Restored'); openBin();
          } catch(e){ showErr('Restore failed: ' + (e.message||'Unknown error')); }
        });

        dd.querySelector('.force-action').addEventListener('click', async ()=> {
          const confirmed = await Swal.fire({ title:'Permanently delete?', html:`Permanently delete "<strong>${escapeHtml(it.title||'this')}</strong>"? This cannot be undone.`, icon:'warning', showCancelButton:true, confirmButtonText:'Yes, delete' });
          if (!confirmed.isConfirmed) return;
          try {
            const res = await apiFetch(`/api/quizz/${encodeURIComponent(it.id)}/force`, { method:'DELETE' });
            if (!res.ok) { const j = await res.json().catch(()=>({})); throw new Error(j?.message||'Delete failed'); }
            showOk('Deleted'); openBin();
          } catch(e){ showErr('Delete failed: ' + (e.message||'Unknown error')); }
        });
      });
    }

    resp.appendChild(table); wrap.appendChild(resp);
    setTimeout(()=> {
      wrap.querySelector('#qz-bin-refresh')?.addEventListener('click', openBin);
      wrap.querySelector('#qz-bin-back')?.addEventListener('click', ()=> loadQuizzes());
    }, 0);
    return wrap;
  }

  async function openBin() {
    showLoader(true); showEmpty(false); showItems(false);
    try {
      const ctx = readContext();
      const items = await fetchDeletedQuizzes(ctx?.batch_id ? `batch_uuid=${encodeURIComponent(ctx.batch_id)}` : '');
      const dom = buildBinTable(items || []);
      if ($items) { $items.innerHTML = ''; $items.appendChild(dom); showItems(true); }
    } catch(e){ console.error(e); if ($items) $items.innerHTML = '<div class="qz-empty p-3">Unable to load bin.</div>'; showItems(true); showErr('Failed to load bin'); }
    finally { showLoader(false); }
  }

  $btnBin?.addEventListener('click', (e)=> { e.preventDefault(); if (!canViewBin) return; openBin(); });

  // ---------- Main list loader ----------
  async function loadQuizzes(){
    showLoader(true); showItems(false); showEmpty(false);
    try {
      const ctx = readContext();
      if (!ctx || !ctx.batch_id) throw new Error('Batch context required');

      const mqs = moduleQueryString();
      const url = `${apiBase}/batch/${encodeURIComponent(ctx.batch_id)}/quizzes${mqs ? ('?' + mqs) : ''}`;
      const res = await apiFetch(url);
      if (!res.ok) throw new Error('HTTP ' + res.status);

      const json = await res.json().catch(()=>null);
      const { items } = normalizeServerResponse(json);

      let assigned = (items || []).filter(it => !!it.assigned || it.assign_status_flag == 1 || it.batch_quiz_id != null);
      assigned = assigned.filter(passesModuleFilter);
      _assignedCache = assigned;
      applyFiltersAndRender();
    } catch(e){
      console.error('Load quizzes error', e);
      if ($items) $items.innerHTML = '<div class="qz-empty">Unable to load quizzes — please refresh.</div>';
      showItems(true);
      showErr('Failed to load quizzes: ' + (e.message || 'Unknown error'));
    } finally { showLoader(false); }
  }

  let searchTimer;
  $search?.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(()=> applyFiltersAndRender(), 250); });
  $sort?.addEventListener('change', applyFiltersAndRender);
  $refresh?.addEventListener('click', loadQuizzes);
  $assignBtn?.addEventListener('click', () => {
    const ctx = readContext();
    if (!ctx || !ctx.batch_id) { showErr('Batch context required to assign quizzes'); return; }
    openQuizzes(ctx.batch_id);
  });

  if ($btnBin) $btnBin.style.display = canViewBin ? 'inline-block' : 'none';
  loadQuizzes();

  // --------------------------
  // ASSIGN MODAL
  // --------------------------
  let quizzesModal, qz_uuid=null, qz_page=1;

  function quizzesParams(){
    const p = new URLSearchParams();
    if (qz_q?.value.trim()) p.set('q', qz_q.value.trim());
    p.set('per_page', qz_per?.value || 20);
    p.set('page', qz_page);
    if (qz_assigned?.value === 'assigned') p.set('assigned','1');
    if (qz_assigned?.value === 'unassigned') p.set('assigned','0');
    return p.toString();
  }

  function openQuizzes(uuid){
    try { quizzesModal = quizzesModal || new bootstrap.Modal(document.getElementById('quizzesModal')); }
    catch(e){ const el = document.getElementById('quizzesModal'); if (el) quizzesModal = { show:()=>el.classList.add('show'), hide:()=>el.classList.remove('show') }; }
    qz_uuid = uuid; qz_page = 1;
    if (qz_assigned) qz_assigned.value = 'all';
    quizzesModal?.show();
    loadAssignQuizzes();
  }

  qz_apply?.addEventListener('click', ()=>{ qz_page=1; loadAssignQuizzes(); });
  qz_per?.addEventListener('change', ()=>{ qz_page=1; loadAssignQuizzes(); });
  qz_assigned?.addEventListener('change', ()=>{ qz_page=1; loadAssignQuizzes(); });
  let qzT;
  qz_q?.addEventListener('input', ()=>{ clearTimeout(qzT); qzT=setTimeout(()=>{ qz_page=1; loadAssignQuizzes(); },350); });

  async function loadAssignQuizzes(){
    if (!qz_uuid) return;
    if (qz_loader) qz_loader.style.display='';
    qz_rows?.querySelectorAll('tr:not(#qz_loader)').forEach(tr=>tr.remove());

    try {
      const res = await apiFetch(`/api/batches/${encodeURIComponent(qz_uuid)}/quizzes?` + quizzesParams());
      const j = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(j?.message || 'Failed to load quizzes');

      let items = j?.data || [];
      const pag = j?.pagination || { current_page:1, per_page:Number(qz_per?.value||20), total:items.length };

      if (qz_assigned?.value === 'assigned') items = items.filter(x=> !!x.assigned);
      if (qz_assigned?.value === 'unassigned') items = items.filter(x=> !x.assigned);

      const frag = document.createDocumentFragment();
      items.forEach(u=>{
        const assigned = !!u.assigned;
        const title = u.title || u.name || ('Quiz #'+(u.id||'?'));
        const publish = !!u.publish_to_students;
        const attemptsVal = u.attempt_allowed !== null && u.attempt_allowed !== undefined ? u.attempt_allowed : '';
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="fw-semibold">${escapeHtml(title)}</td>
          <td><input class="form-control form-control-sm qz-order" type="number" min="0" value="${escapeHtml(String(attemptsVal))}" style="width:110px"></td>
          <td class="text-center"><div class="form-check form-switch d-inline-block"><input class="form-check-input qz-pub" type="checkbox" ${publish?'checked':''}></div></td>
          <td class="text-center"><div class="form-check form-switch d-inline-block"><input class="form-check-input qz-tg" type="checkbox" data-id="${u.id}" ${assigned?'checked':''}></div></td>
        `;
        frag.appendChild(tr);
      });
      qz_rows.appendChild(frag);

      qz_rows.querySelectorAll('.qz-tg').forEach(ch=>{
        ch.addEventListener('change', async ()=>{
          const row = ch.closest('tr');
          const quizId = Number(ch.dataset.id);
          const assigned = !!ch.checked;
          const pubEl = row.querySelector('.qz-pub');
          const attemptEl = row.querySelector('.qz-order');
          const payload = { quiz_id:quizId, assigned, publish_to_students: pubEl?.checked ?? false };
          if (attemptEl?.value !== '') payload.attempt_allowed = Number(attemptEl.value);
          try { await toggleQuiz(qz_uuid, payload, ch); if ((qz_assigned?.value==='assigned'&&!assigned)||(qz_assigned?.value==='unassigned'&&assigned)) loadAssignQuizzes(); } catch(e){}
        });
      });

      qz_rows.querySelectorAll('.qz-pub').forEach(pb=>{
        pb.addEventListener('change', async ()=>{
          const row = pb.closest('tr'); const ch = row.querySelector('.qz-tg');
          const quizId = Number(ch?.dataset.id); if (!quizId) return;
          const attemptEl = row.querySelector('.qz-order');
          const payload = { quiz_id:quizId, assigned:!!ch.checked, publish_to_students:!!pb.checked };
          if (attemptEl?.value !== '') payload.attempt_allowed = Number(attemptEl.value);
          try { await toggleQuiz(qz_uuid, payload, null, true); } catch(_){}
        });
      });

      qz_rows.querySelectorAll('.qz-order').forEach(io=>{
        io.addEventListener('blur', async ()=>{
          const row = io.closest('tr'); const ch = row.querySelector('.qz-tg');
          const quizId = Number(ch?.dataset.id); if (!quizId) return;
          const val = io.value !== '' ? Number(io.value) : null;
          try { await toggleQuiz(qz_uuid, { quiz_id:quizId, assigned:!!ch.checked, attempt_allowed:val }, null, true); } catch(e){ console.error('Failed to save attempts',e); }
        });
      });

      const total=Number(pag.total||items.length), per=Number(pag.per_page||20), cur=Number(pag.current_page||1);
      const pages = Math.max(1, Math.ceil(total/per));
      const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
      let html = li(cur<=1,false,'Prev',cur-1);
      const s=Math.max(1,cur-2), e=Math.min(pages,cur+2);
      for (let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
      html+=li(cur>=pages,false,'Next',cur+1);
      qz_pager.innerHTML = html;
      qz_pager.querySelectorAll('a.page-link[data-page]').forEach(a=>{
        a.addEventListener('click',()=>{ const t=Number(a.dataset.page); if(!t||t===qz_page)return; qz_page=t; loadAssignQuizzes(); });
      });
      qz_meta.textContent = `Page ${cur} of ${pages} — ${total} quizzes`;

    } catch(e){ console.error('Quiz load error:', e); }
    finally { if (qz_loader) qz_loader.style.display='none'; }
  }

  async function toggleQuiz(uuid, payload, checkboxEl=null, quiet=false){
    try {
      if (typeof payload.assigned === 'undefined') payload.assigned = true;
      const mf = getModuleFilter();
      if (mf) {
        if (mf.course_module_uuid) {
          payload.course_module_uuid = String(mf.course_module_uuid);
          payload.module_uuid = String(mf.course_module_uuid);
          delete payload.course_module_id; delete payload.module_id;
        } else if (mf.course_module_id != null) {
          payload.course_module_id = Number(mf.course_module_id);
          payload.module_id = Number(mf.course_module_id);
        }
      }
      const res = await apiFetch(`/api/batches/${encodeURIComponent(uuid)}/quizzes/toggle`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
      const j = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(j?.message || 'Quiz toggle failed');
      if (!quiet) Swal.fire({ toast:true, position:'top-end', icon:'success', title: payload.assigned ? 'Quiz assigned' : 'Quiz unassigned', showConfirmButton:false, timer:2000 });
      return j;
    } catch(e){
      if (checkboxEl) checkboxEl.checked = !checkboxEl.checked;
      Swal.fire({ toast:true, position:'top-end', icon:'error', title:e.message||'Toggle failed', showConfirmButton:false, timer:3000 });
      throw e;
    }
  }

  // ============================================================
  // ADMIN QUIZ RESULTS MODAL
  // ============================================================
  function aqrStatusPill(status) {
    const s = String(status||'').toLowerCase();
    let cls='secondary', label=status||'—';
    if (s==='submitted')      { cls='success';  label='Submitted'; }
    if (s==='auto_submitted') { cls='warning';  label='Auto-submitted'; }
    if (s==='in_progress')    { cls='primary';  label='In Progress'; }
    if (s==='pass')           { cls='success';  label='Pass'; }
    if (s==='fail')           { cls='danger';   label='Fail'; }
    return `<span class="badge bg-${cls}-subtle text-${cls} border border-${cls}-subtle">${escapeHtml(label)}</span>`;
  }

  async function openAdminQuizResults(row) {
    const adminResultsModalEl = document.getElementById('adminQuizResultsModal');
    if (!adminResultsModalEl) { showErr('Results modal not found'); return; }

    const titleEl = document.getElementById('aqr_quiz_title');
    if (titleEl) titleEl.textContent = row.title || row.quiz?.quiz_name || row.quiz?.title || 'Quiz';

    window.aqrSwitchTab('submitted');

    try {
      if (window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(adminResultsModalEl).show();
      else { adminResultsModalEl.classList.add('show'); adminResultsModalEl.style.display='block'; }
    } catch(e){ adminResultsModalEl.classList.add('show'); adminResultsModalEl.style.display='block'; }

    const loaderEl = document.getElementById('aqr_loader');
    const rowsSub  = document.getElementById('aqr_rows_submitted');
    const rowsNot  = document.getElementById('aqr_rows_not_submitted');
    const summary  = document.getElementById('aqr_summary');

    if (loaderEl) loaderEl.style.display='';
    if (rowsSub)  rowsSub.innerHTML  = '<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>';
    if (rowsNot)  rowsNot.innerHTML  = '<tr><td colspan="3" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>';

    try {
      const ctx = readContext();
      const quizKey = row.uuid || row.quiz?.uuid || row.quiz_id || row.quiz?.id || row.id;
      const batchQuizUuid = row.batch_quizzes_uuid || row.batch_quiz_uuid || row.batch_quiz?.uuid || null;

      let apiUrl = `/api/exam/quizzes/${encodeURIComponent(quizKey)}/results`;
      const params = new URLSearchParams();
      if (batchQuizUuid) params.set('batch_quiz', batchQuizUuid);
      if (ctx?.batch_id) params.set('batch_id', ctx.batch_id);
      if (params.toString()) apiUrl += '?' + params.toString();

      const res  = await apiFetch(apiUrl);
      const json = await res.json().catch(()=>({}));
      if (!res.ok || json.success === false) throw new Error(json.message || `HTTP ${res.status}`);

      let submitted    = Array.isArray(json.submitted)     ? json.submitted     : [];
      let notSubmitted = Array.isArray(json.not_submitted) ? json.not_submitted : [];

      if (!submitted.length && !notSubmitted.length) {
        const all = Array.isArray(json.attempts) ? json.attempts : Array.isArray(json.data) ? json.data : [];
        submitted    = all.filter(a => ['submitted','auto_submitted'].includes(String(a.status||'').toLowerCase()));
        notSubmitted = all.filter(a => !['submitted','auto_submitted'].includes(String(a.status||'').toLowerCase()));
      }

      if (summary) summary.textContent = `${submitted.length} submitted · ${notSubmitted.length} pending`;

      if (rowsSub) {
        if (!submitted.length) {
          rowsSub.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No submissions yet.</td></tr>';
        } else {
          rowsSub.innerHTML = '';
          submitted.forEach((a, i) => {
            const r = a.result || a;
            const name  = a.student_name  || a.user?.name  || a.name  || '—';
            const email = a.student_email || a.user?.email || a.email || '';
            const attemptNo = r.attempt_number || a.attempt_number || (i + 1);
            const scoreText = (r.marks_obtained !== undefined && r.total_marks !== undefined)
              ? `${r.marks_obtained}/${r.total_marks} (${Number(r.percentage||0).toFixed(1)}%)`
              : (a.score !== undefined ? String(a.score) : '—');
            const status = r.result_status || r.status || a.status || '—';
            const resultId = r.result_id || r.id || a.result_id || null;
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td class="text-muted">${i+1}</td>
              <td><div class="fw-semibold">${escapeHtml(name)}</div><div class="small text-muted">${escapeHtml(email)}</div></td>
              <td class="text-muted">#${attemptNo}</td>
              <td>${escapeHtml(scoreText)}</td>
              <td>${aqrStatusPill(status)}</td>
              <td class="text-end">
                ${resultId
                  ? `<a href="/exam/results/${encodeURIComponent(resultId)}/view" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-eye me-1"></i>View</a>`
                  : `<span class="text-muted small">—</span>`}
              </td>`;
            rowsSub.appendChild(tr);
          });
        }
      }

      if (rowsNot) {
        if (!notSubmitted.length) {
          rowsNot.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">All students have submitted.</td></tr>';
        } else {
          rowsNot.innerHTML = '';
          notSubmitted.forEach((s, i) => {
            const name  = s.student_name  || s.user?.name  || s.name  || '—';
            const email = s.student_email || s.user?.email || s.email || '';
            const tr = document.createElement('tr');
            tr.innerHTML = `<td class="text-muted">${i+1}</td><td class="fw-semibold">${escapeHtml(name)}</td><td class="text-muted">${escapeHtml(email)}</td>`;
            rowsNot.appendChild(tr);
          });
        }
      }

    } catch(e){
      console.error('openAdminQuizResults error', e);
      if (rowsSub) rowsSub.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger small">Failed to load: ${escapeHtml(e.message)}</td></tr>`;
      if (rowsNot) rowsNot.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-danger small">—</td></tr>`;
    } finally {
      if (loaderEl) loaderEl.style.display='none';
    }
  }

  window.openQuizzes = openQuizzes;

})();
</script>