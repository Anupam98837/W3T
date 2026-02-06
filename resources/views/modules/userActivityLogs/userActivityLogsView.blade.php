{{-- resources/views/modules/activityLogs/manageActivityLogs.blade.php --}}
@extends('pages.users.layout.structure')
@section('title','Activity Logs')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

{{-- Select2 (searchable dropdowns) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"/>

<style>
/* =========================
 * Activity Logs (MSIT theme)
 * - Dynamic from GET /api/activity-logs
 * - Filters: role, user (dropdown), batch, course, module, quiz/assignment/material/coding question (dropdowns)
 * - Paginated + details modal
 * ========================= */

.al-wrap{max-width:1320px;margin:18px auto 60px;padding:0 12px;overflow:visible}
.al-toolbar.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:12px}
.al-toolbar .row{--bs-gutter-x:.6rem;--bs-gutter-y:.6rem}
.al-title{display:flex;align-items:center;gap:.6rem}
.al-title .badge{border:1px solid var(--line-strong);background:var(--surface-2);color:var(--ink)}
.al-actions{display:flex;gap:.5rem;justify-content:flex-end;flex-wrap:wrap}
.al-actions .btn{border-radius:12px}
.al-search{position:relative}
.al-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted-color)}
.al-search input{padding-left:36px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink)}
.al-chipbar{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:8px}
.al-chip{border:1px solid var(--line-strong);border-radius:999px;padding:6px 10px;background:var(--surface);color:var(--ink);cursor:pointer;user-select:none}
.al-chip.active{background:rgba(149,30,170,.12);border-color:rgba(149,30,170,.35)}
html.theme-dark .al-chip.active{background:rgba(201,79,240,.12);border-color:rgba(201,79,240,.35)}

.table-wrap{margin-top:12px;background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);overflow:hidden}
.table-wrap .table{margin:0}
.table thead th{white-space:nowrap;border-bottom:1px solid var(--line-strong)}
.table tbody td{vertical-align:middle}
.al-col-msg{max-width:360px}
.al-col-ep{max-width:220px}
.al-muted{color:var(--muted-color)}
.al-actor{display:flex;flex-direction:column;gap:2px}
.al-actor .name{font-weight:700;color:var(--ink);line-height:1.1}
.al-actor .sub{font-size:12px;color:var(--muted-color)}
.al-kv{display:flex;gap:8px;flex-wrap:wrap}
.al-kv .k{font-size:12px;color:var(--muted-color)}
.al-kv .v{font-size:12px;color:var(--ink)}
.al-pill{display:inline-flex;align-items:center;gap:.35rem;padding:4px 8px;border-radius:999px;border:1px solid var(--line-strong);background:var(--surface-2);font-size:12px;white-space:nowrap}

.al-pagination{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-top:1px solid var(--line-strong);background:var(--surface)}
.al-pagebtns{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.al-pagebtns .btn{border-radius:12px}
.al-perpage{display:flex;align-items:center;gap:8px}
.al-perpage select{border-radius:12px;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);padding:6px 10px}

/* Skeleton */
.al-skel{padding:12px}
.skel-row{height:14px;border-radius:8px;background:linear-gradient(90deg, rgba(0,0,0,.06), rgba(0,0,0,.02), rgba(0,0,0,.06));background-size:200% 100%;animation:skel 1.2s infinite}
html.theme-dark .skel-row{background:linear-gradient(90deg, rgba(255,255,255,.10), rgba(255,255,255,.06), rgba(255,255,255,.10))}
@keyframes skel{0%{background-position:0% 0}100%{background-position:-200% 0}}

.modal .form-control, .modal .form-select{
  border-radius:12px;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink)
}
.modal .form-label{color:var(--muted-color);font-size:12px}
.al-pre{
  background:var(--surface-2);
  border:1px solid var(--line-strong);
  border-radius:12px;
  padding:10px;
  max-height:240px;
  overflow:auto;
  font-size:12px;
  color:var(--ink);
  white-space:pre-wrap;
  word-break:break-word;
}

/* =========================
 * Select2 theme alignment
 * ========================= */
.select2-container{width:100%!important}
.select2-container--default .select2-selection--single{
  height:42px; border-radius:12px; border:1px solid var(--line-strong);
  background:var(--surface); color:var(--ink);
  display:flex; align-items:center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered{
  color:var(--ink); line-height:40px; padding-left:12px; padding-right:28px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow{
  height:40px; right:8px;
}
.select2-dropdown{
  border:1px solid var(--line-strong)!important;
  background:var(--surface)!important;
  color:var(--ink)!important;
  border-radius:12px!important;
  box-shadow:var(--shadow-2);
}
.select2-container--default .select2-search--dropdown .select2-search__field{
  border-radius:12px; border:1px solid var(--line-strong);
  background:var(--surface); color:var(--ink);
  padding:8px 10px;
}
.select2-container--default .select2-results__option{
  font-size:13px;
}
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
  background:rgba(149,30,170,.12);
  color:var(--ink);
}
html.theme-dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
  background:rgba(201,79,240,.12);
}
.select2-container--default .select2-selection--single .select2-selection__placeholder{color:var(--muted-color)}

/* Ensure dropdown shows above modal content */
.select2-container--open{z-index: 2000}
</style>
@endpush

@section('content')
<div class="al-wrap">

  {{-- Toolbar --}}
  <div class="al-toolbar panel">
    <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
      <div class="al-title">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <div>
          <div style="font-weight:800;color:var(--ink);font-family:var(--font-head)">User Activity Logs</div>
          <div class="al-muted" style="font-size:12px">Search, filter & audit actions across modules</div>
        </div>
        <span class="badge ms-1" id="alTotalBadge">Total: —</span>
      </div>

      <div class="al-actions">
        <button class="btn btn-outline-secondary" id="alBtnRefresh">
          <i class="fa-solid fa-rotate"></i> Refresh
        </button>
        <button class="btn btn-primary" id="alOpenFilters">
          <i class="fa-solid fa-filter"></i> Filters
        </button>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-12 col-lg-6">
        <div class="al-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input id="alSearch" class="form-control" placeholder="Search note / module / activity / table / ids..." />
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="al-search">
          <i class="fa-solid fa-user"></i>
          <input id="alName" class="form-control" placeholder="Filter by actor name (optional)..." />
        </div>
      </div>
    </div>

    {{-- Quick module chips (CLICK opens filters + focuses relevant dropdown) --}}
    <div class="al-chipbar" id="alChipbar">
      <div class="al-chip" data-module="quizzes" data-focus="alQuiz"><i class="fa-solid fa-circle-question"></i> Quizzes</div>
      <div class="al-chip" data-module="courses" data-focus="alCourse"><i class="fa-solid fa-book"></i> Courses</div>
      <div class="al-chip" data-module="coding_tests" data-focus="alCodingQuestion"><i class="fa-solid fa-code"></i> Coding Tests</div>
      <div class="al-chip" data-module="assignments" data-focus="alAssignment"><i class="fa-solid fa-file-lines"></i> Assignments</div>
      <div class="al-chip" data-module="study_materials" data-focus="alStudyMaterial"><i class="fa-solid fa-folder-open"></i> Study Materials</div>
    </div>
  </div>

  {{-- Table --}}
  <div class="table-wrap">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:160px">Time</th>
            <th style="width:220px">Actor</th>
            <th style="width:140px">Module</th>
            <th style="width:170px">Action</th>
            <th class="al-col-msg">Message</th>
            <th style="width:220px">Course / Batch</th>
            <th class="al-col-ep">Endpoint</th>
            <th style="width:120px">IP</th>
            <th style="width:110px">Details</th>
          </tr>
        </thead>
        <tbody id="alTbody">
          <tr><td colspan="9">
            <div class="al-skel">
              <div class="skel-row" style="width:92%"></div>
              <div class="skel-row mt-2" style="width:86%"></div>
              <div class="skel-row mt-2" style="width:78%"></div>
            </div>
          </td></tr>
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="al-pagination">
      <div class="al-muted" style="font-size:12px" id="alPageInfo">—</div>

      <div class="al-pagebtns" id="alPageBtns">
        {{-- buttons injected --}}
      </div>

      <div class="al-perpage">
        <span class="al-muted" style="font-size:12px">Rows</span>
        <select id="alPerPage">
          <option value="10">10</option>
          <option value="20" selected>20</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
    </div>
  </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="alFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:18px;border:1px solid var(--line-strong);background:var(--surface)">
      <div class="modal-header" style="border-color:var(--line-strong)">
        <h5 class="modal-title" style="color:var(--ink);font-family:var(--font-head);font-weight:800">
          <i class="fa-solid fa-sliders"></i> Filter Activity Logs
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">

          <div class="col-12 col-md-4">
            <label class="form-label">Role</label>
            <select class="form-select" id="alRole">
              <option value="">All roles</option>
              <option value="admin">admin</option>
              <option value="superadmin">superadmin</option>
              <option value="director">director</option>
              <option value="principal">principal</option>
              <option value="hod">hod</option>
              <option value="faculty">faculty</option>
              <option value="technical_assistant">technical_assistant</option>
              <option value="it_person">it_person</option>
              <option value="placement_officer">placement_officer</option>
              <option value="student">student</option>
              <option value="instructor">instructor</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">User (searchable)</label>
            <select class="form-select" id="alUser">
              <option value="">All users</option>
            </select>
            <div class="al-muted mt-1" style="font-size:11px">Loads from API (best-effort)</div>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Batch</label>
            <select class="form-select" id="alBatch">
              <option value="">All batches</option>
            </select>
            <div class="al-muted mt-1" style="font-size:11px">Loads from API if available</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Course</label>
            <select class="form-select" id="alCourse">
              <option value="">All courses</option>
            </select>
            <div class="al-muted mt-1" style="font-size:11px">Loads from API if available</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Quiz (optional)</label>
            <select class="form-select" id="alQuiz">
              <option value="">All quizzes</option>
            </select>
            <div class="al-muted mt-1" style="font-size:11px">Pick a quiz to see actions by all users on it</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Assignment (optional)</label>
            <select class="form-select" id="alAssignment">
              <option value="">All assignments</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Study Material (optional)</label>
            <select class="form-select" id="alStudyMaterial">
              <option value="">All study materials</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Coding Question (optional)</label>
            <select class="form-select" id="alCodingQuestion">
              <option value="">All coding questions</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Modules</label>
            <div class="d-flex flex-wrap gap-2">
              <label class="al-pill">
                <input type="checkbox" class="form-check-input m-0 me-2 alMod" value="quizzes"> Quizzes
              </label>
              <label class="al-pill">
                <input type="checkbox" class="form-check-input m-0 me-2 alMod" value="courses"> Courses
              </label>
              <label class="al-pill">
                <input type="checkbox" class="form-check-input m-0 me-2 alMod" value="coding_tests"> Coding Tests
              </label>
              <label class="al-pill">
                <input type="checkbox" class="form-check-input m-0 me-2 alMod" value="assignments"> Assignments
              </label>
              <label class="al-pill">
                <input type="checkbox" class="form-check-input m-0 me-2 alMod" value="study_materials"> Study Materials
              </label>
              <label class="al-pill">
                <input type="checkbox" class="form-check-input m-0 me-2 alMod" value="users"> Users
              </label>
            </div>
            <div class="al-muted mt-2" style="font-size:11px">
              Tip: checkbox values must match what backend stores in <code>user_data_activity_log.module</code>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Action contains</label>
            <input id="alActionLike" class="form-control" placeholder="e.g. create, update, delete, submit..." />
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">From (optional)</label>
            <input id="alFrom" type="date" class="form-control" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">To (optional)</label>
            <input id="alTo" type="date" class="form-control" />
          </div>

        </div>
      </div>

      <div class="modal-footer" style="border-color:var(--line-strong)">
        <button class="btn btn-outline-secondary" id="alClearFilters">
          <i class="fa-regular fa-circle-xmark"></i> Clear
        </button>
        <button class="btn btn-primary" id="alApplyFilters" data-bs-dismiss="modal">
          <i class="fa-solid fa-check"></i> Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Details Modal --}}
<div class="modal fade" id="alDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:18px;border:1px solid var(--line-strong);background:var(--surface)">
      <div class="modal-header" style="border-color:var(--line-strong)">
        <h5 class="modal-title" style="color:var(--ink);font-family:var(--font-head);font-weight:800">
          <i class="fa-solid fa-circle-info"></i> Log Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-lg-4">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Time</span>
              <span class="v" id="dTime">—</span>
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Actor</span>
              <span class="v" id="dActor">—</span>
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Role</span>
              <span class="v" id="dRole">—</span>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Module</span>
              <span class="v" id="dModule">—</span>
            </div>
          </div>
          <div class="col-12 col-lg-6">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Action</span>
              <span class="v" id="dAction">—</span>
            </div>
          </div>

          <div class="col-12">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Message</span>
              <span class="v" id="dMessage">—</span>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Course</span>
              <span class="v" id="dCourse">—</span>
            </div>
          </div>
          <div class="col-12 col-lg-6">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Batch</span>
              <span class="v" id="dBatch">—</span>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Endpoint</span>
              <span class="v" id="dEndpoint">—</span>
            </div>
          </div>
          <div class="col-12 col-lg-3">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">Method</span>
              <span class="v" id="dMethod">—</span>
            </div>
          </div>
          <div class="col-12 col-lg-3">
            <div class="al-pill w-100 justify-content-between">
              <span class="k">IP</span>
              <span class="v" id="dIp">—</span>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Changed Fields</label>
            <pre class="al-pre" id="dChanges">—</pre>
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label">Old Values</label>
            <pre class="al-pre" id="dOld">—</pre>
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label">New Values</label>
            <pre class="al-pre" id="dNew">—</pre>
          </div>

          <div class="col-12">
            <label class="form-label">User Agent</label>
            <pre class="al-pre" id="dUa">—</pre>
          </div>
        </div>
      </div>

      <div class="modal-footer" style="border-color:var(--line-strong)">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- jQuery + Select2 --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function(){
  // =========================
  // Config (adjust if needed)
  // =========================
  const API_LIST   = '/api/activity-logs';
  const API_BATCH  = '/api/batches';
  const API_COURSE = '/api/courses';

  // Best-effort lookup endpoints (try in order)
  const API_USERS_TRY = [
    '/api/users?per_page=500',
    '/api/users?per_page=300',
    '/api/users',
  ];

  const API_QUIZZES_TRY = [
    '/api/quizzes?per_page=500',
    '/api/quizz?per_page=500',
    '/api/quizzes',
    '/api/quizz',
  ];

  const API_ASSIGNMENTS_TRY = [
    '/api/assignments?per_page=500',
    '/api/assignments',
  ];

  const API_STUDY_MATERIALS_TRY = [
    '/api/study-materials?per_page=500',
    '/api/study_materials?per_page=500',
    '/api/study-materials',
    '/api/study_materials',
  ];

  const API_CODING_QUESTIONS_TRY = [
    '/api/coding-questions?per_page=500',
    '/api/coding_questions?per_page=500',
    '/api/coding-questions',
    '/api/coding_questions',
  ];

  // =========================
  // State
  // =========================
  const state = {
    page: 1,
    per_page: 20,
    search: '',
    name: '',          // text filter by actor name
    role: '',
    user_id: '',       // dropdown filter (performed_by)
    batch_id: '',
    course_id: '',
    modules: [],
    action_like: '',
    from: '',
    to: '',

    // specific targets
    quiz_id: '',
    assignment_id: '',
    study_material_id: '',
    coding_question_id: '',

    // cache lookup for names (for table display fallback)
    batchMap: {},   // id -> title/name
    courseMap: {},  // id -> title/name

    lookupsLoaded: {
      select2: false,
      users: false,
      batches: false,
      courses: false,
      quizzes: false,
      assignments: false,
      study_materials: false,
      coding_questions: false,
    },

    last: { total: 0, last_page: 1 }
  };

  // =========================
  // Helpers
  // =========================
  const $id = (id)=>document.getElementById(id);

  function getToken(){
    return sessionStorage.getItem('token')
      || localStorage.getItem('token')
      || sessionStorage.getItem('auth_token')
      || localStorage.getItem('auth_token')
      || '';
  }

  function headers(){
    const h = { 'Accept':'application/json' };
    const t = getToken();
    if (t) h['Authorization'] = 'Bearer ' + t;
    return h;
  }

  function safeJsonParse(v){
    if (!v) return null;
    if (typeof v === 'object') return v;
    try { return JSON.parse(v); } catch(e){ return null; }
  }
  function safeHtml(html){
  const s = String(html ?? '');
  // If DOMPurify isn't available, fallback to escaped text
  if (typeof DOMPurify === 'undefined') return esc(s);

  // Sanitize and allow common formatting tags + attributes
  return DOMPurify.sanitize(s, {
    ALLOWED_TAGS: [
      'b','strong','i','em','u','br','p','div','span','ul','ol','li',
      'table','thead','tbody','tr','th','td',
      'code','pre','small','sup','sub','hr','a'
    ],
    ALLOWED_ATTR: ['class','style','href','target','rel','title','data-*']
  });
}


  function pretty(v){
    const obj = safeJsonParse(v);
    if (obj === null) return (v === null || v === undefined || v === '') ? '—' : String(v);
    try { return JSON.stringify(obj, null, 2); } catch(e){ return '—'; }
  }

  function fmtTime(ts){
    if (!ts) return '—';
    const d = new Date(ts);
    if (isNaN(d.getTime())) return String(ts);
    return d.toLocaleString();
  }

  function esc(s){
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function skel(){
    $id('alTbody').innerHTML = `
      <tr><td colspan="9">
        <div class="al-skel">
          <div class="skel-row" style="width:92%"></div>
          <div class="skel-row mt-2" style="width:86%"></div>
          <div class="skel-row mt-2" style="width:78%"></div>
        </div>
      </td></tr>`;
  }

  function normalizeListResponse(json){
    // supports:
    // { data: [...], meta:{page,per_page,total,last_page} }
    // { data: [...], pagination:{page,per_page,total,last_page} }
    const meta = json?.meta || json?.pagination || {};
    const page = parseInt(meta.page || 1,10);
    const per  = parseInt(meta.per_page || meta.perPage || state.per_page,10);
    const total = parseInt(meta.total || 0,10);
    const last = parseInt(meta.last_page || Math.ceil(total / Math.max(1,per)) || 1,10);
    return { rows: json?.data || [], meta: { page, per_page: per, total, last_page: last } };
  }

  function normalizeArrayFromAny(json){
    // tries common patterns
    if (!json) return [];
    if (Array.isArray(json)) return json;
    if (Array.isArray(json.data)) return json.data;
    if (Array.isArray(json.rows)) return json.rows;
    if (json.data && Array.isArray(json.data.data)) return json.data.data;
    return [];
  }

  async function fetchFirstOk(urls){
    for (const url of urls){
      try{
        const r = await fetch(url, { headers: headers() });
        if (!r.ok) continue;
        const j = await r.json();
        return { url, json: j };
      }catch(e){}
    }
    return null;
  }

  function buildQuery(){
    const q = new URLSearchParams();
    q.set('page', state.page);
    q.set('per_page', state.per_page);

    if (state.search) q.set('search', state.search);
    if (state.name) q.set('name', state.name);

    if (state.role) q.set('role', state.role);
    if (state.user_id) q.set('user_id', state.user_id);

    if (state.batch_id) q.set('batch_id', state.batch_id);
    if (state.course_id) q.set('course_id', state.course_id);

    if (state.modules.length) q.set('modules', state.modules.join(','));
    if (state.action_like) q.set('action_like', state.action_like);

    if (state.quiz_id) q.set('quiz_id', state.quiz_id);
    if (state.assignment_id) q.set('assignment_id', state.assignment_id);
    if (state.study_material_id) q.set('study_material_id', state.study_material_id);
    if (state.coding_question_id) q.set('coding_question_id', state.coding_question_id);

    if (state.from) q.set('from', state.from);
    if (state.to) q.set('to', state.to);

    q.set('sort', 'created_at');
    q.set('dir', 'desc');

    return q.toString();
  }

  function extractCourseBatchNames(row){
    // Prefer backend normalized fields (your updated index should send course_name/batch_name)
    const cn = row.course_name || row.course_title || (row.course && (row.course.title || row.course.name)) || '';
    const bn = row.batch_name  || row.batch_title  || (row.batch && (row.batch.title || row.batch.name)) || '';

    const courseId = row.course_id || '';
    const batchId  = row.batch_id  || '';

    const cn2 = cn || (courseId && state.courseMap[String(courseId)]) || '';
    const bn2 = bn || (batchId  && state.batchMap[String(batchId)])  || '';

    return { cn: cn2 || '—', bn: bn2 || '—' };
  }

  function actorName(row){
    return row.performed_by_name
      || row.actor_name
      || row.user_name
      || row.name
      || 'Unknown';
  }

  function actorRole(row){
    return row.performed_by_role
      || row.actor_role
      || row.role
      || '—';
  }

  function actorId(row){
    return row.performed_by || row.actor_id || row.user_id || '';
  }

  function moduleVal(row){
    return row.module || row.log_module || '—';
  }

  function actionVal(row){
    return row.activity || row.action || '—';
  }

  function messageVal(row){
    return row.log_note || row.message || '—';
  }

  // =========================
  // Select2 init + filling
  // =========================
  function initSelect2Once(){
    if (state.lookupsLoaded.select2) return;
    state.lookupsLoaded.select2 = true;

    const parent = $('#alFilterModal');
    $('#alRole').select2({ dropdownParent: parent, width:'100%' });

    $('#alUser').select2({
      dropdownParent: parent,
      width:'100%',
      placeholder: 'All users',
      allowClear: true
    });

    $('#alBatch').select2({
      dropdownParent: parent,
      width:'100%',
      placeholder: 'All batches',
      allowClear: true
    });

    $('#alCourse').select2({
      dropdownParent: parent,
      width:'100%',
      placeholder: 'All courses',
      allowClear: true
    });

    $('#alQuiz').select2({
      dropdownParent: parent,
      width:'100%',
      placeholder: 'All quizzes',
      allowClear: true
    });

    $('#alAssignment').select2({
      dropdownParent: parent,
      width:'100%',
      placeholder: 'All assignments',
      allowClear: true
    });

    $('#alStudyMaterial').select2({
      dropdownParent: parent,
      width:'100%',
      placeholder: 'All study materials',
      allowClear: true
    });

    $('#alCodingQuestion').select2({
      dropdownParent: parent,
      width:'100%',
      placeholder: 'All coding questions',
      allowClear: true
    });
  }

  function fillSelectNative(selectEl, firstLabel, items){
    const opts = [`<option value="">${esc(firstLabel)}</option>`];
    items.forEach(it=>{
      if (!it || it.id === undefined || it.id === null) return;
      opts.push(`<option value="${esc(it.id)}">${esc(it.text || it.title || it.name || ('#'+it.id))}</option>`);
    });
    selectEl.innerHTML = opts.join('');
  }

  // =========================
  // Rendering
  // =========================
  function render(rows, meta){
    $id('alTotalBadge').textContent = 'Total: ' + (meta.total ?? 0);

    if (!rows.length){
      $id('alTbody').innerHTML = `<tr><td colspan="9" class="text-center py-5">
        <div style="color:var(--muted-color)">
          <i class="fa-regular fa-folder-open" style="font-size:22px"></i>
          <div class="mt-2" style="font-weight:700;color:var(--ink)">No logs found</div>
          <div style="font-size:12px">Try changing filters</div>
        </div>
      </td></tr>`;
      renderPager(meta);
      return;
    }

    const html = rows.map(r=>{
      const time = fmtTime(r.created_at || r.time || r.createdAt);
      const an = actorName(r);
      const ar = actorRole(r);
      const aid = actorId(r);

      const mod = moduleVal(r);
      const act = actionVal(r);
      const msg = messageVal(r);

      const ep  = r.endpoint || r.path || r.url || '—';
      const ip  = r.ip || r.ip_address || '—';
      const method = r.method || r.http_method || '—';

      const { cn, bn } = extractCourseBatchNames(r);

      // show quiz/assignment/material/coding title if present
      const extra = (r.quiz_name || r.assignment_title || r.study_material_title || r.coding_question_title)
        ? `<div class="al-muted" style="font-size:12px;margin-top:4px">
             ${r.quiz_name ? ('Quiz: '+esc(r.quiz_name)) : ''}
             ${r.assignment_title ? ((r.quiz_name?' • ':'')+'Assignment: '+esc(r.assignment_title)) : ''}
             ${r.study_material_title ? (((r.quiz_name||r.assignment_title)?' • ':'')+'Material: '+esc(r.study_material_title)) : ''}
             ${r.coding_question_title ? (((r.quiz_name||r.assignment_title||r.study_material_title)?' • ':'')+'Coding: '+esc(r.coding_question_title)) : ''}
           </div>`
        : '';

      return `
        <tr>
          <td>
            <div style="font-weight:700;color:var(--ink)">${esc(time)}</div>
            <div class="al-muted" style="font-size:12px">${esc(method)}</div>
          </td>

          <td>
            <div class="al-actor">
              <div class="name">${esc(an)}</div>
              <div class="sub">Role: ${esc(ar)} ${aid ? ('• ID: '+esc(aid)) : ''}</div>
            </div>
          </td>

          <td><span class="al-pill"><i class="fa-solid fa-cube"></i> ${esc(mod)}</span></td>
          <td><span class="al-pill"><i class="fa-solid fa-bolt"></i> ${esc(act)}</span></td>

          <td class="al-col-msg">
<div style="font-weight:700;color:var(--ink)" class="al-msg-html">${safeHtml(msg)}</div>
            <div class="al-muted" style="font-size:12px">
              ${r.table_name ? ('Table: '+esc(r.table_name)) : ''}
              ${r.record_id ? (' • Row: '+esc(r.record_id)) : ''}
            </div>
            ${extra}
          </td>

          <td>
            <div class="al-kv">
              <span class="al-pill"><i class="fa-solid fa-book"></i> ${esc(cn)}</span>
              <span class="al-pill"><i class="fa-solid fa-layer-group"></i> ${esc(bn)}</span>
            </div>
          </td>

          <td class="al-col-ep"><code style="font-size:12px;color:var(--ink)">${esc(ep)}</code></td>
          <td><span class="al-pill"><i class="fa-solid fa-network-wired"></i> ${esc(ip)}</span></td>

          <td>
            <button class="btn btn-sm btn-outline-secondary alBtnDetails"
              data-row='${esc(JSON.stringify(r))}'>
              <i class="fa-regular fa-eye"></i> View
            </button>
          </td>
        </tr>
      `;
    }).join('');

    $id('alTbody').innerHTML = html;
    renderPager(meta);

    // bind details
    document.querySelectorAll('.alBtnDetails').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        let row = null;
        try { row = JSON.parse(btn.getAttribute('data-row')); } catch(e){ row = null; }
        if (!row) return;

        const an = actorName(row);
        const ar = actorRole(row);
        const aid = actorId(row);

        const mod = moduleVal(row);
        const act = actionVal(row);
        const msg = messageVal(row);

        const ep  = row.endpoint || row.path || row.url || '—';
        const ip  = row.ip || row.ip_address || '—';
        const method = row.method || row.http_method || '—';
        const ua = row.user_agent || row.ua || '—';

        const { cn, bn } = extractCourseBatchNames(row);

        $id('dTime').textContent = fmtTime(row.created_at || row.time || row.createdAt);
        $id('dActor').textContent = an + (aid ? (' (ID '+aid+')') : '');
        $id('dRole').textContent = ar;
        $id('dModule').textContent = mod;
        $id('dAction').textContent = act;
$id('dMessage').innerHTML = safeHtml(msg);
        $id('dCourse').textContent = cn;
        $id('dBatch').textContent = bn;
        $id('dEndpoint').textContent = ep;
        $id('dMethod').textContent = method;
        $id('dIp').textContent = ip;

        // Your log table fields:
        $id('dChanges').textContent = pretty(row.changed_fields);
        $id('dOld').textContent = pretty(row.old_values);
        $id('dNew').textContent = pretty(row.new_values);
        $id('dUa').textContent = String(ua);

        const modal = new bootstrap.Modal(document.getElementById('alDetailsModal'));
        modal.show();
      });
    });
  }

  function renderPager(meta){
    state.last.total = meta.total;
    state.last.last_page = meta.last_page || 1;

    const page = meta.page || 1;
    const per = meta.per_page || state.per_page;
    const total = meta.total || 0;

    const start = total === 0 ? 0 : ((page-1)*per + 1);
    const end = Math.min(page*per, total);

    $id('alPageInfo').textContent = `Showing ${start}-${end} of ${total} • Page ${page}/${meta.last_page || 1}`;

    const last = meta.last_page || 1;
    const btns = [];

    btns.push(`<button class="btn btn-sm btn-outline-secondary" ${page<=1?'disabled':''} data-page="${page-1}">
      <i class="fa-solid fa-angle-left"></i>
    </button>`);

    const windowSize = 2;
    const from = Math.max(1, page - windowSize);
    const to = Math.min(last, page + windowSize);

    if (from > 1){
      btns.push(`<button class="btn btn-sm btn-outline-secondary" data-page="1">1</button>`);
      if (from > 2) btns.push(`<span class="al-muted" style="padding:6px 6px">…</span>`);
    }

    for (let p=from; p<=to; p++){
      btns.push(`<button class="btn btn-sm ${p===page?'btn-primary':'btn-outline-secondary'}" data-page="${p}">${p}</button>`);
    }

    if (to < last){
      if (to < last-1) btns.push(`<span class="al-muted" style="padding:6px 6px">…</span>`);
      btns.push(`<button class="btn btn-sm btn-outline-secondary" data-page="${last}">${last}</button>`);
    }

    btns.push(`<button class="btn btn-sm btn-outline-secondary" ${page>=last?'disabled':''} data-page="${page+1}">
      <i class="fa-solid fa-angle-right"></i>
    </button>`);

    $id('alPageBtns').innerHTML = btns.join('');
    $id('alPageBtns').querySelectorAll('button[data-page]').forEach(b=>{
      b.addEventListener('click', ()=>{
        const p = parseInt(b.getAttribute('data-page'),10);
        if (!p || p<1 || p>last) return;
        state.page = p;
        load();
      });
    });
  }

  // =========================
  // Data loading
  // =========================
  async function load(){
    skel();
    try{
      const url = API_LIST + '?' + buildQuery();
      const resp = await fetch(url, { headers: headers() });
      if (!resp.ok){
        const t = await resp.text();
        throw new Error(t || ('HTTP '+resp.status));
      }
      const json = await resp.json();
      const { rows, meta } = normalizeListResponse(json);
      render(rows, meta);
    }catch(err){
      $id('alTbody').innerHTML = `<tr><td colspan="9" class="text-center py-5">
        <div style="color:var(--muted-color)">
          <i class="fa-solid fa-triangle-exclamation" style="font-size:22px"></i>
          <div class="mt-2" style="font-weight:800;color:var(--ink)">Failed to load activity logs</div>
          <div style="font-size:12px;max-width:820px;margin:8px auto">${esc(err.message || err)}</div>
          <div class="mt-3">
            <button class="btn btn-primary" id="alRetry"><i class="fa-solid fa-rotate"></i> Retry</button>
          </div>
        </div>
      </td></tr>`;
      const retry = document.getElementById('alRetry');
      if (retry) retry.addEventListener('click', load);
    }
  }

  async function loadBatchCourseMaps(){
    // Used only for display fallback if backend doesn't return course_name/batch_name
    // batches
    try{
      const u = API_BATCH + '?per_page=500';
      const r = await fetch(u, { headers: headers() });
      if (r.ok){
        const j = await r.json();
        const items = normalizeArrayFromAny(j);
        items.forEach(b=>{
          const id = b.id ?? '';
          const title = b.badge_title || b.title || b.name || b.slug || ('Batch #' + id);
          if (id !== '') state.batchMap[String(id)] = title;
        });
      }
    }catch(e){}

    // courses
    try{
      const u = API_COURSE + '?per_page=500';
      const r = await fetch(u, { headers: headers() });
      if (r.ok){
        const j = await r.json();
        const items = normalizeArrayFromAny(j);
        items.forEach(c=>{
          const id = c.id ?? '';
          const title = c.title || c.name || c.slug || ('Course #' + id);
          if (id !== '') state.courseMap[String(id)] = title;
        });
      }
    }catch(e){}
  }

  async function loadLookupsIfNeeded(){
    initSelect2Once();

    // USERS
    if (!state.lookupsLoaded.users){
      state.lookupsLoaded.users = true;
      const got = await fetchFirstOk(API_USERS_TRY);
      const items = got ? normalizeArrayFromAny(got.json) : [];
      const out = items.map(u=>{
        const id = u.id ?? u.user_id ?? '';
        const name = u.name || u.full_name || u.username || ('User #' + id);
        const email = u.email ? (' • ' + u.email) : '';
        return { id, text: name + email };
      }).filter(x=>x.id!=='');

      fillSelectNative($id('alUser'), 'All users', out);
      $('#alUser').trigger('change.select2');
    }

    // BATCHES
    if (!state.lookupsLoaded.batches){
      state.lookupsLoaded.batches = true;
      try{
        const r = await fetch(API_BATCH + '?per_page=500', { headers: headers() });
        const j = r.ok ? await r.json() : null;
        const items = normalizeArrayFromAny(j);
        const out = items.map(b=>{
          const id = b.id ?? '';
          const title = b.badge_title || b.title || b.name || b.slug || ('Batch #' + id);
          return { id, text: title };
        }).filter(x=>x.id!=='');
        fillSelectNative($id('alBatch'), 'All batches', out);
        $('#alBatch').trigger('change.select2');
      }catch(e){}
    }

    // COURSES
    if (!state.lookupsLoaded.courses){
      state.lookupsLoaded.courses = true;
      try{
        const r = await fetch(API_COURSE + '?per_page=500', { headers: headers() });
        const j = r.ok ? await r.json() : null;
        const items = normalizeArrayFromAny(j);
        const out = items.map(c=>{
          const id = c.id ?? '';
          const title = c.title || c.name || c.slug || ('Course #' + id);
          return { id, text: title };
        }).filter(x=>x.id!=='');
        fillSelectNative($id('alCourse'), 'All courses', out);
        $('#alCourse').trigger('change.select2');
      }catch(e){}
    }

    // QUIZZES
    if (!state.lookupsLoaded.quizzes){
      state.lookupsLoaded.quizzes = true;
      const got = await fetchFirstOk(API_QUIZZES_TRY);
      const items = got ? normalizeArrayFromAny(got.json) : [];
      const out = items.map(q=>{
        const id = q.id ?? q.quiz_id ?? '';
        const title = q.quiz_name || q.title || q.name || ('Quiz #' + id);
        return { id, text: title };
      }).filter(x=>x.id!=='');
      fillSelectNative($id('alQuiz'), 'All quizzes', out);
      $('#alQuiz').trigger('change.select2');
    }

    // ASSIGNMENTS
    if (!state.lookupsLoaded.assignments){
      state.lookupsLoaded.assignments = true;
      const got = await fetchFirstOk(API_ASSIGNMENTS_TRY);
      const items = got ? normalizeArrayFromAny(got.json) : [];
      const out = items.map(a=>{
        const id = a.id ?? '';
        const title = a.title || a.name || a.slug || ('Assignment #' + id);
        return { id, text: title };
      }).filter(x=>x.id!=='');
      fillSelectNative($id('alAssignment'), 'All assignments', out);
      $('#alAssignment').trigger('change.select2');
    }

    // STUDY MATERIALS
    if (!state.lookupsLoaded.study_materials){
      state.lookupsLoaded.study_materials = true;
      const got = await fetchFirstOk(API_STUDY_MATERIALS_TRY);
      const items = got ? normalizeArrayFromAny(got.json) : [];
      const out = items.map(sm=>{
        const id = sm.id ?? '';
        const title = sm.title || sm.name || sm.slug || ('Material #' + id);
        return { id, text: title };
      }).filter(x=>x.id!=='');
      fillSelectNative($id('alStudyMaterial'), 'All study materials', out);
      $('#alStudyMaterial').trigger('change.select2');
    }

    // CODING QUESTIONS
    if (!state.lookupsLoaded.coding_questions){
      state.lookupsLoaded.coding_questions = true;
      const got = await fetchFirstOk(API_CODING_QUESTIONS_TRY);
      const items = got ? normalizeArrayFromAny(got.json) : [];
      const out = items.map(cq=>{
        const id = cq.id ?? '';
        const title = cq.title || cq.name || cq.slug || ('Coding Question #' + id);
        return { id, text: title };
      }).filter(x=>x.id!=='');
      fillSelectNative($id('alCodingQuestion'), 'All coding questions', out);
      $('#alCodingQuestion').trigger('change.select2');
    }
  }

  function openFiltersAndFocus(focusId){
    const modalEl = document.getElementById('alFilterModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    // ensure lookups + select2 ready, then open the dropdown search
    setTimeout(async ()=>{
      await loadLookupsIfNeeded();
      if (focusId && document.getElementById(focusId)) {
        const $el = $('#'+focusId);
        if ($el.length && $el.data('select2')) {
          $el.select2('open');
        } else {
          document.getElementById(focusId).focus();
        }
      }
    }, 200);
  }

  // =========================
  // Events
  // =========================
  let tmr1=null, tmr2=null;

  $id('alSearch').addEventListener('input', (e)=>{
    clearTimeout(tmr1);
    tmr1 = setTimeout(()=>{
      state.search = (e.target.value || '').trim();
      state.page = 1;
      load();
    }, 350);
  });

  $id('alName').addEventListener('input', (e)=>{
    clearTimeout(tmr2);
    tmr2 = setTimeout(()=>{
      state.name = (e.target.value || '').trim();
      state.page = 1;
      load();
    }, 350);
  });

  $id('alPerPage').addEventListener('change', ()=>{
    state.per_page = parseInt($id('alPerPage').value,10) || 20;
    state.page = 1;
    load();
  });

  $id('alBtnRefresh').addEventListener('click', load);

  $id('alOpenFilters').addEventListener('click', ()=>{
    openFiltersAndFocus(null);
  });

  // chipbar: toggle module AND open modal focusing target dropdown
  document.querySelectorAll('#alChipbar .al-chip').forEach(chip=>{
    chip.addEventListener('click', ()=>{
      const m = chip.getAttribute('data-module');
      const focusId = chip.getAttribute('data-focus');

      if (!m) return;

      const idx = state.modules.indexOf(m);
      const turningOn = idx < 0;

      if (turningOn) state.modules.push(m);
      else state.modules.splice(idx,1);

      // If turning OFF a module, also clear its specific target filter
      if (!turningOn){
        if (m === 'quizzes') state.quiz_id = '';
        if (m === 'assignments') state.assignment_id = '';
        if (m === 'study_materials') state.study_material_id = '';
        if (m === 'coding_tests') state.coding_question_id = '';
        // keep course_id (courses chip maps to course filter; user might still want it)
      }

      // sync chip active
      chip.classList.toggle('active', state.modules.includes(m));

      // open filters modal and focus correct searchable dropdown
      openFiltersAndFocus(focusId);

      // sync modal checkboxes immediately
      document.querySelectorAll('.alMod').forEach(cb=>{
        if (cb.value === m) cb.checked = state.modules.includes(m);
      });
    });
  });

  // When filter modal opens, init select2 + load lookups (best-effort)
  document.getElementById('alFilterModal').addEventListener('show.bs.modal', ()=>{
    loadLookupsIfNeeded();
  });

  $id('alApplyFilters').addEventListener('click', ()=>{
    state.role = $id('alRole').value || '';

    // dropdown filters
    state.user_id = $id('alUser').value || '';
    state.batch_id = $id('alBatch').value || '';
    state.course_id = $id('alCourse').value || '';

    state.quiz_id = $id('alQuiz').value || '';
    state.assignment_id = $id('alAssignment').value || '';
    state.study_material_id = $id('alStudyMaterial').value || '';
    state.coding_question_id = $id('alCodingQuestion').value || '';

    state.action_like = ($id('alActionLike').value || '').trim();
    state.from = $id('alFrom').value || '';
    state.to = $id('alTo').value || '';

    // modules from modal
    const mods = [];
    document.querySelectorAll('.alMod:checked').forEach(cb=>mods.push(cb.value));
    state.modules = mods;

    // sync chips
    document.querySelectorAll('#alChipbar .al-chip').forEach(chip=>{
      const m = chip.getAttribute('data-module');
      chip.classList.toggle('active', state.modules.includes(m));
    });

    // If user is selected from dropdown, clear the top name input to avoid confusion
    if (state.user_id){
      state.name = '';
      $id('alName').value = '';
    }

    state.page = 1;
    load();
  });

  $id('alClearFilters').addEventListener('click', ()=>{
    $id('alRole').value = '';
    $id('alActionLike').value = '';
    $id('alFrom').value = '';
    $id('alTo').value = '';

    // reset selects (native + select2)
    ['alUser','alBatch','alCourse','alQuiz','alAssignment','alStudyMaterial','alCodingQuestion'].forEach(id=>{
      const el = document.getElementById(id);
      if (el){
        el.value = '';
        try { $('#'+id).val('').trigger('change'); } catch(e){}
      }
    });

    document.querySelectorAll('.alMod').forEach(cb=>cb.checked=false);

    state.role = '';
    state.user_id = '';
    state.batch_id = '';
    state.course_id = '';
    state.modules = [];
    state.action_like = '';
    state.from = '';
    state.to = '';

    state.quiz_id = '';
    state.assignment_id = '';
    state.study_material_id = '';
    state.coding_question_id = '';

    document.querySelectorAll('#alChipbar .al-chip').forEach(chip=>chip.classList.remove('active'));

    state.page = 1;
    load();
  });

  // =========================
  // Init
  // =========================
  loadBatchCourseMaps().finally(load);

})();
</script>
@endpush
