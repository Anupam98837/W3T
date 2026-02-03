{{-- resources/views/modules/codingTest/userCodingTest.blade.php --}}

@php
  // Try to detect batch id from request or injected variable
  $batchId =
      request('batch')
      ?? request('batch_id')
      ?? (isset($batch) ? ($batch->id ?? $batch->uuid ?? null) : null);

  // Where your full coding test runner page lives (adjust if your route is different)
  $codingTestUrl = url('/coding-test');
@endphp
<style>
 
.ct-modal-backdrop{
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.55);
  display:grid;
  place-items:center;
  z-index:9999;
}
.ct-modal{
  width:100%;
  max-width:520px;
  background:var(--surface);
  border-radius:18px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-3);
}
.ct-modal-hd{
  padding:12px 14px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid var(--line-strong);
}
.ct-modal-title{
  font-weight:900;
  font-size:15px;
}
.ct-modal-close{
  background:none;
  border:none;
  font-size:16px;
  cursor:pointer;
}
.ct-modal-bd{
  padding:14px;
  overflow-y: auto;   /* vertical scroll */
  overflow-x: visible;/* allow dropdown to overflow */
  flex: 1;
}
.ct-table,
.ct-table tbody,
.ct-table tr,
.ct-table td {
  overflow: visible;
}

.ct-attempt-item{
  border:1px solid var(--line-strong);
  border-radius:14px;
  padding:10px 12px;
  margin-bottom:10px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.ct-attempt-meta{
  font-size:12.5px;
  color:var(--muted-color);
}
.ct-attempt-status{
  font-weight:900;
}
.ct-attempt-status.pass{color:#16a34a}
.ct-attempt-status.fail{color:#dc2626}

  .ct-shell{max-width:1180px;margin:14px auto 36px;padding:0 10px}
  .ct-topbar{display:flex;gap:12px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap}
  .ct-title{display:flex;gap:12px;align-items:center}
  .ct-ico{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;
    background:linear-gradient(135deg, rgba(149,30,170,.14), rgba(201,79,240,.10));
    border:1px solid var(--line-strong);
    box-shadow:var(--shadow-1)
  }
  .ct-ico i{color:var(--primary-color);font-size:18px}
  .ct-h{font-family: "Poppins", ui-sans-serif; font-weight:700; letter-spacing:.2px; font-size:18px; color:var(--text-color)}
  .ct-sub{font-size:12.5px;color:var(--muted-color);margin-top:2px}

  .ct-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .ct-search{display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:14px;
    border:1px solid var(--line-strong); background:var(--surface); min-width:260px;
    box-shadow:var(--shadow-1)
  }
  .ct-search i{color:var(--muted-color)}
  .ct-search input{border:none;outline:none;background:transparent;color:var(--text-color);width:100%;font-size:13px}
  .ct-select{padding:10px 12px;border-radius:14px;border:1px solid var(--line-strong);background:var(--surface);
    color:var(--text-color);font-size:13px;box-shadow:var(--shadow-1)
  }

  .ct-btn{border:none;border-radius:14px;padding:10px 12px;font-weight:600;font-size:13px;display:inline-flex;gap:8px;align-items:center}
  .ct-btn span{line-height:1}
  .ct-btn-ghost{background:var(--surface);color:var(--text-color);border:1px solid var(--line-strong);box-shadow:var(--shadow-1)}
  .ct-btn-primary{background:var(--primary-color);color:#fff;box-shadow:var(--shadow-2)}
  .ct-btn-danger{background:rgba(220,53,69,.12);color:#dc3545;border:1px solid rgba(220,53,69,.25)}
  .ct-btn:disabled{opacity:.6;cursor:not-allowed}

  .ct-stats{margin-top:12px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
  @media(max-width: 992px){ .ct-stats{grid-template-columns:repeat(2,1fr)} }
  .ct-stat{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;padding:10px 12px;box-shadow:var(--shadow-1)}
  .ct-stat .k{font-size:12px;color:var(--muted-color)}
  .ct-stat .v{font-size:16px;font-weight:800;color:var(--text-color);margin-top:2px}

  .ct-card{margin-top:12px;background:var(--surface);border:1px solid var(--line-strong);border-radius:18px;box-shadow:var(--shadow-2);overflow:hidden}
  .ct-card-hd{padding:12px 14px;border-bottom:1px solid var(--line-strong);display:flex;align-items:center;justify-content:space-between;gap:10px}
  .ct-card-hl{display:flex;gap:10px;align-items:center;font-weight:800;color:var(--text-color)}
  .ct-dot{width:10px;height:10px;border-radius:99px;background:var(--primary-color);box-shadow:0 0 0 4px rgba(149,30,170,.12)}
  .ct-pill{padding:6px 10px;border-radius:999px;border:1px solid var(--line-strong);background:rgba(149,30,170,.08);color:var(--text-color);font-size:12px;font-weight:700}
  .ct-card-bd{padding:12px 14px}

  .ct-alert{display:flex;gap:10px;align-items:flex-start;padding:12px 12px;border-radius:16px;border:1px solid var(--line-strong);background:rgba(255,193,7,.10);color:var(--text-color)}
  .ct-alert i{margin-top:2px}
  .ct-alert .t{font-weight:900}
  .ct-alert .d{font-size:13px;color:var(--muted-color);margin-top:2px}

  .ct-skeleton .r{height:46px;border-radius:14px;background:rgba(125,125,125,.12);border:1px solid rgba(125,125,125,.18);margin-bottom:10px}
  .ct-skeleton .r:nth-child(2){height:54px}
  .ct-skeleton .r:nth-child(4){height:52px}

  .ct-table{width:100%;border-collapse:separate;border-spacing:0 10px}
  .ct-table thead th{font-size:12px;color:var(--muted-color);font-weight:800;padding:0 10px 8px}
  .ct-table tbody tr{background:var(--surface);border:1px solid var(--line-strong)}
  .ct-table tbody td{padding:12px 10px;vertical-align:middle;border-top:1px solid var(--line-strong);border-bottom:1px solid var(--line-strong)}
  .ct-table tbody tr td:first-child{border-left:1px solid var(--line-strong);border-top-left-radius:14px;border-bottom-left-radius:14px}
  .ct-table tbody tr td:last-child{border-right:1px solid var(--line-strong);border-top-right-radius:14px;border-bottom-right-radius:14px}

  .ct-qtitle{font-weight:900;color:var(--text-color);line-height:1.2}
  .ct-qmeta{font-size:12.5px;color:var(--muted-color);margin-top:3px;display:flex;gap:10px;flex-wrap:wrap}
  .ct-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:900;border:1px solid var(--line-strong);background:rgba(0,0,0,.03)}
  .ct-badge.easy{background:rgba(25,135,84,.10);border-color:rgba(25,135,84,.20)}
  .ct-badge.medium{background:rgba(255,193,7,.10);border-color:rgba(255,193,7,.25)}
  .ct-badge.hard{background:rgba(220,53,69,.10);border-color:rgba(220,53,69,.25)}

  .ct-tags{display:flex;gap:6px;flex-wrap:wrap}
  .ct-tag{font-size:12px;padding:5px 9px;border-radius:999px;border:1px solid var(--line-strong);background:rgba(94,21,112,.06);color:var(--text-color);font-weight:800}

  .ct-attemptBox{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .ct-num{width:92px;min-width:92px;padding:9px 10px;border-radius:14px;border:1px solid var(--line-strong);background:var(--surface);
    color:var(--text-color);font-weight:800;font-size:13px
  }
  .ct-dd{min-width:180px;padding:9px 10px;border-radius:14px;border:1px solid var(--line-strong);background:var(--surface);
    color:var(--text-color);font-weight:700;font-size:13px
  }

  .ct-toggle{display:inline-flex;align-items:center;gap:8px}
  .ct-switch{width:44px;height:26px;border-radius:999px;background:rgba(125,125,125,.20);border:1px solid var(--line-strong);position:relative;cursor:pointer}
  .ct-switch::after{content:"";width:20px;height:20px;border-radius:999px;background:#fff;position:absolute;top:2px;left:2px;transition:all .18s ease;box-shadow:0 6px 14px rgba(0,0,0,.12)}
  .ct-switch.on{background:rgba(149,30,170,.28);border-color:rgba(149,30,170,.35)}
  .ct-switch.on::after{left:22px;background:var(--primary-color)}
  .ct-tlbl{font-size:12.5px;color:var(--muted-color);font-weight:900}

  .ct-cards{display:grid;gap:10px}
  .ct-cardItem{border:1px solid var(--line-strong);border-radius:18px;padding:12px;background:var(--surface);box-shadow:var(--shadow-1)}
  .ct-cardItem .top{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}
  .ct-cardItem .mid{margin-top:10px}
  .ct-cardItem .bot{margin-top:12px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center}

  .ct-empty{padding:18px 10px;text-align:center}
  .ct-empty-ico{font-size:28px;color:var(--muted-color)}
  .ct-empty-t{font-weight:900;color:var(--text-color);margin-top:6px}
  .ct-empty-d{font-size:13px;color:var(--muted-color);margin-top:3px}

  .ct-toast{position:fixed;right:14px;bottom:14px;z-index:9999;
    background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;
    box-shadow:var(--shadow-3);padding:10px 12px;color:var(--text-color);font-weight:800;max-width:320px
  }
  .ct-dropdown {
  position: relative;
  display: inline-block;
}

.ct-dropdown-toggle {
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
  color: #6c757d;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.ct-dropdown-toggle:hover {
  background-color: #f8f9fa;
  color: #495057;
}

.ct-dropdown-menu {
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  min-width: 160px;
  z-index: 10000;
  display: none;
  margin-top: 4px;
  overflow: hidden;
}

.ct-dropdown-menu.show {
  display: block;
}

.ct-dropdown-item {
  
  display: block;
  width: 100%;
  padding: 10px 16px;
  border: none;
  background: none;
  text-align: left;
  cursor: pointer;
  color: #495057;
  transition: background-color 0.2s;
  white-space: nowrap;
}

.ct-dropdown-item:hover {
  background-color: #f8f9fa;
  color: #212529;
}

.ct-dropdown-item i {
  margin-right: 8px;
  width: 16px;
  text-align: center;
}

.ct-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: grid;
  place-items: center;
  z-index: 9999;
}

/* =========================
   Modal Container (NO WIDTH LIMIT)
   ========================= */
.ct-modal {
  
  max-height: 90vh;
  background: var(--surface);
  border-radius: 18px;
  border: 1px solid var(--line-strong);
  box-shadow: var(--shadow-3);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* =========================
   Modal Header
   ========================= */
.ct-modal-hd {
  padding: 14px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--line-strong);
  flex-shrink: 0;
}

.ct-modal-title {
  font-weight: 900;
  font-size: 15px;
}

.ct-modal-close {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: var(--muted-color);
}

.ct-modal-close:hover {
  color: #dc2626;
}

/* =========================
   Modal Tabs
   ========================= */
.ct-modal-tabs {
  display: flex;
  gap: 6px;
  padding: 10px;
  background: #f1f5f9;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.ct-tab {
  flex: 1;
  border: none;
  background: transparent;
  padding: 10px 14px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border-radius: 10px;
  color: #475569;
}

.ct-tab:hover {
  background: #e2e8f0;
}

.ct-tab.active {
  background: #ffffff;
  color: #0f172a;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* =========================
   Modal Body (Scrollable)
   ========================= */
.ct-modal-bd {
  padding: 16px 18px;
  overflow-y: auto;
  overflow-x: visible;
  flex: 1;
}

/* =========================
   Student Cards
   ========================= */
.ct-student {
  border: 1px solid var(--line-strong);
  border-radius: 14px;
  padding: 12px 14px;
  margin-bottom: 12px;
  background: var(--surface);
}

.ct-student.simple {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.ct-student-hd {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.ct-student strong {
  font-size: 14px;
  color: var(--text-color);
}

.ct-student .muted {
  font-size: 12px;
  color: var(--muted-color);
}

/* =========================
   Student Attempts
   ========================= */
.ct-student-attempts {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed var(--line-strong);
}

.ct-attempt-row {
  padding: 8px 10px;
  margin-bottom: 6px;
  border-radius: 10px;
  background: rgba(125,125,125,.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  font-size: 13px;
}

.ct-attempt-row:hover {
  background: rgba(125,125,125,.15);
}

/* =========================
   Attempt Status
   ========================= */
.ct-status {
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 900;
}

.ct-status.pass {
  background: rgba(22,163,74,.12);
  color: #16a34a;
}

.ct-status.fail {
  background: rgba(220,38,38,.12);
  color: #dc2626;
}

/* =========================
   Buttons
   ========================= */
.ct-btn {
  border-radius: 14px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 700;
  border: none;
  cursor: pointer;
}

.ct-btn-ghost {
  background: var(--surface);
  color: var(--text-color);
  border: 1px solid var(--line-strong);
}

/* =========================
   Dropdown (safe overflow)
   ========================= */
.ct-dropdown {
  position: relative;
}

.ct-dropdown-menu {
  top: 100%;
  right: 0;
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-radius: 10px;
  min-width: 160px;
  z-index: 10000;
  display: none;
  margin-top: 6px;
  box-shadow: 0 8px 24px rgba(0,0,0,.15);
}

.ct-dropdown-menu.show {
  display: block;
}

.ct-dropdown-item {
  padding: 10px 14px;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-size: 13px;
}

.ct-dropdown-item:hover {
  background: #f8f9fa;
}

/* =========================
   Mobile Tweaks
   ========================= */
@media (max-width: 640px) {
  .ct-modal {
    width: 98vw;
    border-radius: 12px;
  }

  .ct-modal-bd {
    padding: 12px;
  }
}

</style>
<div class="ct-shell" id="codingTestsRoot"
     data-batch-id="{{ $batchId }}"
     data-api-base="{{ url('/api') }}"
     data-test-url="{{ $codingTestUrl }}">

  <div class="ct-topbar">
    <div class="ct-title">
      <div class="ct-ico"><i class="fa-solid fa-code"></i></div>
      <div>
        <div class="ct-h">Coding Tests</div>
        <div class="ct-sub">Assigned questions, attempts, and quick actions.</div>
      </div>
    </div>

    <div class="ct-actions">
      <div class="ct-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input id="ctSearch" type="text" placeholder="Search questions, tags, difficulty…">
      </div>

      <select id="ctDifficulty" class="ct-select">
        <option value="">All difficulties</option>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>

      <button id="ctRefresh" class="ct-btn ct-btn-ghost" type="button">
        <i class="fa-solid fa-rotate"></i><span>Refresh</span>
      </button>

      <!-- Added Assign button (top-right) -->
      <button id="ctAssignBtn"
              class="ct-btn ct-btn-primary"
              type="button"
              style="display:none; margin-left:8px;">
        <i class="fa-solid fa-plus"></i>
        <span>Assign Coding Test</span>
      </button>
    </div>
  </div>

  <div class="ct-stats" id="ctStats" style="display:none;">
    <div class="ct-stat" style="display:none;" >
      <div class="k">Total</div><div class="v" id="stTotal">0</div>
    </div>
    <div class="ct-stat" style="display:none;">
      <div class="k">Assigned</div><div class="v" id="stAssigned">0</div>
    </div>
    <div class="ct-stat" style="display:none;">
      <div class="k">Your Attempts</div><div class="v" id="stAttempts">0</div>
    </div>
    <div class="ct-stat" style="display:none;">
      <div class="k">Role</div><div class="v" id="stRole">—</div>
    </div>
  </div>

  <div class="ct-card">
    <div class="ct-card-hd">
      <div class="ct-card-hl">
        <span class="ct-dot"></span>
        <span id="ctListTitle">Loading…</span>
      </div>

      <div class="ct-card-hr">
        <span class="ct-pill" id="ctHintPill" style="display:none;"></span>
      </div>
    </div>

    <div class="ct-card-bd">
      {{-- loaders / alerts --}}
      <div class="ct-alert ct-alert-warn" id="ctNoToken" style="display:none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
          <div class="t">Token not found</div>
          <div class="d">Please login again. Session storage key <b>token</b> is missing.</div>
        </div>
      </div>

      <div class="ct-alert ct-alert-warn" id="ctNoBatch" style="display:none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
          <div class="t">Batch id missing</div>
          <div class="d">Pass <b>?batch=ID</b> (or <b>batch_id</b>) or provide <b>$batch</b> to this view.</div>
        </div>
      </div>

      <div class="ct-skeleton" id="ctSkeleton">
        <div class="r"></div><div class="r"></div><div class="r"></div><div class="r"></div><div class="r"></div>
      </div>

      {{-- Desktop table --}}
      <div class="ct-tablewrap d-none d-lg-block" id="ctTableWrap" style="display:none;">
        <table class="ct-table">
          <thead>
          <tr>
            <th style="width:40%;">Question</th>
            <th style="width:14%;">Difficulty</th>
            <th style="width:14%;">Attempts</th>
            <th style="width:14%; text-align:right;">Actions</th>
          </tr>
          </thead>
          <tbody id="ctTbody"></tbody>
        </table>
      </div>

      {{-- Mobile cards --}}
      <div class="ct-cards d-lg-none" id="ctCards" style="display:none;"></div>

      {{-- empty --}}
      <div class="ct-empty" id="ctEmpty" style="display:none;">
        <div class="ct-empty-ico"><i class="fa-regular fa-folder-open"></i></div>
        <div class="ct-empty-t">No questions found</div>
        <div class="ct-empty-d">Try clearing filters or refresh the list.</div>
      </div>
    </div>
  </div>
  
  <!-- Attempts Modal -->
  <div class="ct-modal-backdrop" id="ctAttemptsModal" style="display:none;">
    <div class="ct-modal">
      <div class="ct-modal-hd">
        <div class="ct-modal-title">My Results</div>
        <button class="ct-modal-close" data-act="close-modal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="ct-modal-bd" id="ctAttemptsBody">
        <div class="ct-skeleton">
          <div class="r"></div><div class="r"></div><div class="r"></div>
        </div>
      </div>
    </div>
  </div>
<!-- Admin Results Modal -->
<div class="ct-modal-backdrop" id="ctAdminResultsModal" style="display:none;">
  <div class="ct-modal ct-modal-lg">
    <div class="ct-modal-hd">
      <div class="ct-modal-title">Question Results</div>
      <button class="ct-modal-close" data-act="close-admin-results">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="ct-modal-tabs">
      <button class="ct-tab active" data-tab="participated">Participated</button>
      <button class="ct-tab" data-tab="not-participated">Not Participated</button>
    </div>

    <div class="ct-modal-bd">
      <div id="ctAdminParticipated"></div>
      <div id="ctAdminNotParticipated" style="display:none;"></div>
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
  {{-- tiny toast --}}
  <div class="ct-toast" id="ctToast" style="display:none;"></div>
</div>
<script>
(function(){
  const root = document.getElementById('codingTestsRoot');
  if(!root) return;

  const API_BASE  = root.dataset.apiBase || '';
  const TEST_URL  = root.dataset.testUrl || '';
  const BATCH_ID  = root.dataset.batchId || '';
  const token     = sessionStorage.getItem('token') || localStorage.getItem('token');

  const el = (id) => document.getElementById(id);
  function syncAssignSwitch(questionUuid, assigned) {
    const ctSwitch = root.querySelector(
      `.ct-switch[data-uuid="${CSS.escape(questionUuid)}"]`
    );
    if (ctSwitch) {
      ctSwitch.classList.toggle('on', assigned);
      ctSwitch.setAttribute('aria-checked', assigned ? 'true' : 'false');
    }

    const cqSwitch = document.querySelector(
      `.cq-tg[data-uuid="${CSS.escape(questionUuid)}"]`
    );
    if (cqSwitch) {
      cqSwitch.checked = !!assigned;
    }
  }

  const $skeleton = el('ctSkeleton');
  const $tableWrap= el('ctTableWrap');
  const $cards    = el('ctCards');
  const $tbody    = el('ctTbody');
  const $empty    = el('ctEmpty');
  const $noToken  = el('ctNoToken');
  const $noBatch  = el('ctNoBatch');

  const $search   = el('ctSearch');
  const $diff     = el('ctDifficulty');
  const $refresh  = el('ctRefresh');

  const $stats    = el('ctStats');
  const stTotal   = el('stTotal');
  const stAssigned= el('stAssigned');
  const stAttempts= el('stAttempts');
  const stRole    = el('stRole');

  const $listTitle= el('ctListTitle');
  const $hintPill = el('ctHintPill');

  const $toast    = el('ctToast');
  const $attemptsModal = el('ctAttemptsModal');
  const $attemptsBody  = el('ctAttemptsBody');
  const $adminModal = el('ctAdminResultsModal');
  const $adminPart  = el('ctAdminParticipated');
  const $adminNot   = el('ctAdminNotParticipated');

  const $assignBtn = el('ctAssignBtn');

  // Assign modal elements
  const cq_q = el('cq_q');
  const cq_per = el('cq_per');
  const cq_apply = el('cq_apply');
  const cq_assigned = el('cq_assigned');
  const cq_rows = el('cq_rows');
  const cq_loader = el('cq_loader');
  const cq_meta = el('cq_meta');
  const cq_pager = el('cq_pager');

  let codingQuestionsModal, cq_batch_key = null, cq_page = 1, cqT;

  const getMyRole = async token => {
    if (!token) return '';

    try {
      const res = await fetch('/api/auth/my-role', {
        method: 'GET',
        headers: {
          'Authorization': 'Bearer ' + token,
          'Accept': 'application/json'
        }
      });

      if (!res.ok) return '';

      const data = await res.json().catch(() => null);

      if (data?.status === 'success' && data?.role) {
        return String(data.role).trim().toLowerCase();
      }
    } catch (e) {}

    return '';
  };
    // ============================
  // ✅ Module Context (from URL)
  // ============================
  const URLP = new URLSearchParams(window.location.search);

  const RAW_MODULE_UUID =
    (URLP.get('module_uuid') || URLP.get('course_module_uuid') || '').trim();

  const RAW_MODULE_ID =
    (URLP.get('module_id') || URLP.get('course_module_id') || '').trim();

  const MODULE = {
    id: (RAW_MODULE_ID && /^\d+$/.test(RAW_MODULE_ID)) ? Number(RAW_MODULE_ID) : null,
    uuid: (RAW_MODULE_UUID && /^[0-9a-fA-F-]{36}$/.test(RAW_MODULE_UUID)) ? RAW_MODULE_UUID : null
  };

  // Query string to append to API URLs
  const MODULE_QS = (() => {
    const p = new URLSearchParams();
    if (MODULE.id != null) {
      p.set('course_module_id', String(MODULE.id));
      p.set('module_id', String(MODULE.id)); // compat
    }
    if (MODULE.uuid) {
      p.set('course_module_uuid', MODULE.uuid);
      p.set('module_uuid', MODULE.uuid); // compat
    }
    return p.toString();
  })();

  // Append module filter to a relative API path (used by api())
  function withModule(path){
    if(!MODULE_QS) return path;
    return path + (path.includes('?') ? '&' : '?') + MODULE_QS;
  }

  // Append module filter to a full URL string
  function withModuleUrl(url){
    if(!MODULE_QS) return url;
    return url + (url.includes('?') ? '&' : '?') + MODULE_QS;
  }

  // Add module keys into POST bodies (assign/start)
  function modulePayload(){
    const p = {};
    if (MODULE.id != null) {
      p.course_module_id = MODULE.id;
      p.module_id = MODULE.id; // compat
    }
    if (MODULE.uuid) {
      p.course_module_uuid = MODULE.uuid;
      p.module_uuid = MODULE.uuid; // compat
    }
    return p;
  }


  function openAttemptsModal(uuid){
    $attemptsModal.style.display = 'grid';
    
    $attemptsBody.innerHTML = `
      <div class="ct-skeleton">
        <div class="r"></div><div class="r"></div><div class="r"></div>
      </div>`;
    
    loadQuestionAttempts(uuid);
  }
  
  function closeAttemptsModal(){
    $attemptsModal.style.display = 'none';
    $attemptsBody.innerHTML = '';
  }
  
  $attemptsModal.addEventListener('click', (e) => {
    if(e.target === $attemptsModal) {
      closeAttemptsModal();
    }
  });

  function openAdminResultsModal(questionUuid){
    $adminModal.style.display = 'grid';
    $adminPart.innerHTML = `<div class="ct-skeleton"><div class="r"></div></div>`;
    $adminNot.innerHTML  = `<div class="ct-skeleton"><div class="r"></div></div>`;
    loadAdminResults(questionUuid);
  }

  function closeAdminResultsModal(){
    $adminModal.style.display = 'none';
    $adminPart.innerHTML = '';
    $adminNot.innerHTML  = '';
  }

  function openCodingQuestions(batchKey){
    codingQuestionsModal = codingQuestionsModal || new bootstrap.Modal(el('codingQuestionsModal'));
    cq_batch_key = batchKey;
    cq_page = 1;
    cq_assigned.value = 'all';
    codingQuestionsModal.show();
    loadCodingQuestions();
  }

  let RAW = null;
  let ROLE = '';
  let CAN_MANAGE = false;
  let LIST = [];
  let FILTERED = [];

  function toast(msg){
    $toast.textContent = msg;
    $toast.style.display = 'block';
    clearTimeout($toast._t);
    $toast._t = setTimeout(()=> $toast.style.display='none', 2200);
  }

  function esc(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function normDifficulty(d){
    d = String(d ?? '').toLowerCase().trim();
    if(['easy','beginner'].includes(d)) return 'easy';
    if(['medium','intermediate','mid'].includes(d)) return 'medium';
    if(['hard','advanced','difficult'].includes(d)) return 'hard';
    return d || '';
  }

  function pickArray(obj){
    return obj?.questions || obj?.data || obj?.items || obj?.list || [];
  }

  function detectRole(obj){
    return obj?.actor?.role || obj?.role || obj?.user?.role || '';
  }

  function detectCanManage(obj, role){
    if(typeof obj?.can_manage === 'boolean') return obj.can_manage;
    if(typeof obj?.canManage === 'boolean') return obj.canManage;
    return ['superadmin','admin','instructor'].includes(String(role||'').toLowerCase());
  }

  function getAssignedFlag(q){
    if (q?.assigned === true || q?.assigned === 1) return true;
    if (q?.is_assigned === true || q?.is_assigned === 1) return true;
    if (q?.assigned_to_batch === true || q?.assigned_to_batch === 1) return true;
    return false;
  }

  function normalizeQuestion(q){
    const uuid = q?.uuid || q?.question_uuid || q?.questionUuid || q?.id;
    const title= q?.title || q?.name || q?.question_title || ('Question ' + (uuid||''));
    const diff = normDifficulty(q?.difficulty);
    const tags = Array.isArray(q?.tags) ? q.tags : (typeof q?.tags === 'string' ? q.tags.split(',').map(t=>t.trim()).filter(Boolean) : []);
    const assigned = getAssignedFlag(q);

    let attemptsUsed = 0;

    const arr =
      q?.my_attempts ||
      q?.attempts ||
      [];

    if (Array.isArray(arr) && arr.length) {
      attemptsUsed = Math.max(
        ...arr.map(a => Number(a?.attempt_no || 0))
      );
    } else {
      attemptsUsed = Number(
        q?.last_attempt_no ??
        q?.attempt_no ??
        0
      );
    }

    const maxAttempts = Number(
      q?.attempt_allowed ??
      q?.max_attempts ??
      q?.allowed_attempts ??
      q?.attempt_limit ??
      1
    ) || 1;


    return {
      raw: q,
      uuid, title, diff, tags,
      assigned,
      attemptsCount: attemptsUsed,
      maxAttempts
    };
  }

  function applyFilters(){
    const term = ($search.value || '').trim().toLowerCase();
    const d = ($diff.value || '').trim().toLowerCase();

    FILTERED = LIST.filter(q=>{
      if(d && q.diff !== d) return false;

      if(term){
        const hay = (q.title + ' ' + q.tags.join(' ') + ' ' + q.diff).toLowerCase();
        if(!hay.includes(term)) return false;
      }
      return true;
    });

    render();
  }

  function badge(d){
    const cls = d ? `ct-badge ${esc(d)}` : 'ct-badge';
    const label = d ? d.toUpperCase() : '—';
    return `<span class="${cls}"><i class="fa-solid fa-signal"></i>${esc(label)}</span>`;
  }

  function tagsHTML(tags){
    if(!tags || !tags.length) return `<span class="ct-tag">—</span>`;
    return tags.slice(0,4).map(t=> `<span class="ct-tag">${esc(t)}</span>`).join('');
  }

  function attemptsCell(q){
    const max  = Math.max(1, Number(q.maxAttempts));
    const used = Math.max(0, Number(q.attemptsCount || 0));
    const left = Math.max(0, max - used);
    console.log(used);
    if(CAN_MANAGE){
      return `
        <div class="ct-attemptBox">
          <input class="ct-num" type="number" min="1" max="999"
                 value="${max}"
                 data-act="attempts-input"
                 data-uuid="${esc(q.uuid)}"
                 aria-label="Max attempts">
          <span class="ct-tlbl">max</span>
        </div>
      `;
    }

    return `
      <div class="ct-attemptBox">
        <div class="ct-num">${left} / ${max}</div>
        <span class="ct-tlbl">left</span>
      </div>
    `;
  }

  function actionsCell(q){

    if (CAN_MANAGE) {
      const on  = q.assigned ? 'on' : '';
      const lbl = q.assigned ? 'Assigned' : 'Unassigned';

      return `
        <div style="display:flex;justify-content:flex-end;gap:8px;align-items:center;">
          
          <div class="ct-toggle">
            <div class="ct-switch ${on}"
                 role="switch"
                 tabindex="0"
                 aria-checked="${q.assigned ? 'true' : 'false'}"
                 data-act="toggle-assign"
                 data-uuid="${esc(q.uuid)}">
            </div>
            <div class="ct-tlbl">${esc(lbl)}</div>
          </div>

          <div class="ct-dropdown">
            <button class="ct-dropdown-toggle"
                    type="button"
                    data-act="toggle-dropdown"
                    data-uuid="${esc(q.uuid)}">
              <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>

            <div class="ct-dropdown-menu" data-uuid="${esc(q.uuid)}">
              <button class="ct-dropdown-item"
                      type="button"
                      data-act="open-admin-results"
                      data-uuid="${esc(q.uuid)}">
                <i class="fa-solid fa-chart-bar"></i> Results
              </button>
            </div>
          </div>
        </div>
      `;
    }

    return `
      <div style="display:flex;justify-content:flex-end;gap:8px;align-items:center;">
        
        <button class="ct-btn ct-btn-primary"
                type="button"
                data-act="start"
                data-uuid="${esc(q.uuid)}">
          <i class="fa-solid fa-play"></i> Start
        </button>

        <div class="ct-dropdown">
          <button class="ct-dropdown-toggle"
                  type="button"
                  data-act="toggle-dropdown"
                  data-uuid="${esc(q.uuid)}">
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </button>

          <div class="ct-dropdown-menu" data-uuid="${esc(q.uuid)}">
            <button class="ct-dropdown-item"
                    type="button"
                    data-act="open-attempts-modal"
                    data-uuid="${esc(q.uuid)}">
              <i class="fa-solid fa-list-check"></i> My Results
            </button>
          </div>
        </div>
      </div>
    `;
  }

  function rowHTML(q){
    return `
      <tr data-uuid="${esc(q.uuid)}">
        <td>
          <div class="ct-qtitle">${esc(q.title)}</div>
          <div class="ct-qmeta">
            <span><i class="fa-regular fa-id-badge"></i> ${esc(q.uuid || '—')}</span>
            ${q.assigned ? `<span><i class="fa-solid fa-circle-check"></i> Assigned</span>` : `<span><i class="fa-regular fa-circle"></i> Not assigned</span>`}
          </div>
        </td>
        <td>${badge(q.diff)}</td>
        <td>${attemptsCell(q)}</td>
        <td style="text-align:right;">${actionsCell(q)}</td>
      </tr>
    `;
  }

  function cardHTML(q){
    return `
      <div class="ct-cardItem" data-uuid="${esc(q.uuid)}">
        <div class="top">
          <div>
            <div class="ct-qtitle">${esc(q.title)}</div>
            <div class="ct-qmeta" style="margin-top:4px">
              <span>${q.assigned ? 'Assigned' : 'Not assigned'}</span>
              <span>•</span>
              <span>${esc(q.uuid || '')}</span>
            </div>
          </div>
          <div>${badge(q.diff)}</div>
        </div>

        <div class="mid">
          <div class="ct-tags">${tagsHTML(q.tags)}</div>
        </div>

        <div class="bot">
          <div>${attemptsCell(q)}</div>
          <div>${actionsCell(q)}</div>
        </div>
      </div>
    `;
  }

  function render(){
    $tbody.innerHTML = '';
    $cards.innerHTML = '';

    if(!FILTERED.length){
      $tableWrap.style.display = 'none';
      $cards.style.display = 'none';
      $empty.style.display = 'block';
      return;
    }

    $empty.style.display = 'none';
    $tableWrap.style.display = '';
    $cards.style.display = '';

    $tbody.innerHTML = FILTERED.map(rowHTML).join('');
    $cards.innerHTML = FILTERED.map(cardHTML).join('');
  }

  async function api(path, opts={}){
    const res = await fetch(API_BASE + path, {
      ...opts,
      headers: {
        'Accept':'application/json',
        'Content-Type':'application/json',
        ...(opts.headers || {}),
        'Authorization': 'Bearer ' + token
      }
    });
    const text = await res.text();
    let data = null;
    try { data = text ? JSON.parse(text) : null; } catch(e){ data = { raw:text }; }

    if(!res.ok){
      const msg = data?.message || data?.error || ('Request failed: ' + res.status);
      throw new Error(msg);
    }
    return data;
  }

  async function loadAdminResults(uuid){
    try {
      const data = await api(
        `/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/${encodeURIComponent(uuid)}/allstudent-results`
      );

      renderParticipated(data.participated || []);
      renderNotParticipated(data.not_participated || []);
    } catch(err) {
      toast(err.message || 'Failed to load results');
    }
  }

  function renderParticipated(list){
  if (!list.length) {
    $adminPart.innerHTML = `<div class="ct-empty">No students participated yet</div>`;
    return;
  }

  $adminPart.innerHTML = list.map(s => `
    <div class="ct-student">
      <div class="ct-student-hd">
        <div>
          <strong>${esc(s.name)}</strong>
          <div class="muted">${esc(s.email)}</div>
        </div>

        <button class="ct-btn ct-btn-ghost"
                data-act="toggle-student-attempts">
          Attempts ▾
        </button>
      </div>

      <div class="ct-student-attempts" style="display:none;">
        ${s.attempts.map(a => {
          const resultUuid =
            a?.result_uuid ||
            a?.coding_result_uuid ||
            a?.result?.uuid ||
            a?.uuid ||
            '';

          return `
            <div class="ct-attempt-row">
              <div class="ct-attempt-left"
                   data-act="open-result"
                   data-result="${esc(resultUuid)}">
                Attempt #${a.attempt_no}
                <span class="ct-status ${a.status === 'PASS' ? 'pass' : 'fail'}">
                  ${a.status}
                </span>
              </div>

              ${
                resultUuid
                  ? `<button
                        class="ct-btn ct-btn-ghost ct-btn-icon"
                        title="View Result"
                        data-act="open-result"
                        data-result="${esc(resultUuid)}">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                     </button>`
                  : ''
              }
            </div>
          `;
        }).join('')}
      </div>
    </div>
  `).join('');
}


  function renderNotParticipated(list){
    if (!list.length) {
      $adminNot.innerHTML = `<div class="ct-empty">All students participated</div>`;
      return;
    }

    $adminNot.innerHTML = list.map(s => `
      <div class="ct-student simple">
        <strong>${esc(s.name)}</strong>
        <span class="muted">${esc(s.email)}</span>
      </div>
    `).join('');
  }

  function setLoading(on){
    $skeleton.style.display = on ? 'block' : 'none';
    $tableWrap.style.display = on ? 'none' : '';
    $cards.style.display = on ? 'none' : '';
    $empty.style.display = 'none';
  }

  function updateStats(){
    const total = LIST.length;
    const assigned = LIST.filter(x=>x.assigned).length;
    const attempts = LIST.reduce((a,x)=>a + (Number(x.attemptsCount)||0), 0);

    stTotal.textContent = String(total);
    stAssigned.textContent = String(assigned);
    stAttempts.textContent = String(attempts);
    stRole.textContent = ROLE ? ROLE : '—';

    $stats.style.display = 'grid';
  }

  function renderAttemptsModal(attempts, questionTitle){

    attempts = Array.isArray(attempts)
      ? [...attempts].sort(
          (a, b) => (a.attempt_no ?? 0) - (b.attempt_no ?? 0)
        )
      : [];

    if(!attempts.length){
      $attemptsBody.innerHTML = `
        <div class="ct-empty">
          <div class="ct-empty-ico"><i class="fa-regular fa-folder-open"></i></div>
          <div class="ct-empty-t">No attempts found</div>
          <div class="ct-empty-d">You haven't attempted this question yet.</div>
        </div>`;
      return;
    }

    $attemptsBody.innerHTML = `
      <div class="ct-attempts-header">
        <h4>${esc(questionTitle)}</h4>
        <div class="ct-attempts-count">Total attempts: ${attempts.length}</div>
      </div>

      <div class="ct-attempts-list">
        ${attempts.map(a => {
          const resultUuid =
            a?.result_uuid ||
            a?.coding_result_uuid ||
            a?.result?.uuid ||
            a?.uuid ||
            a?.data?.attempt_uuid ||
            '';

          const status = (a?.status || a?.verdict || '—').toUpperCase();
          const time   = a?.submitted_at || a?.created_at || '';
          const score  = a?.score ?? a?.marks ?? a?.points ?? '';
          const maxScore = a?.max_score ?? a?.total_marks ?? '';

          let statusClass = '';
          if (['PASS','PASSED','SUCCESS'].includes(status)) statusClass = 'pass';
          else if (['FAIL','FAILED','WRONG'].includes(status)) statusClass = 'fail';

          return `
            <div class="ct-attempt-item">
              <div class="ct-attempt-info">
                <div class="ct-attempt-number">
                  <strong>Attempt #${a.attempt_no}</strong>
                </div>
                <div class="ct-attempt-meta">
                  ${time ? new Date(time).toLocaleString() : ''}
                </div>
              </div>

              <div class="ct-attempt-details">
                ${score !== '' ? `
                  <div class="ct-attempt-score">
                    Score: ${score}${maxScore ? `/${maxScore}` : ''}
                  </div>
                ` : ''}

                <div class="ct-attempt-status ${statusClass}">
                  ${status}
                </div>

                ${resultUuid ? `
                  <a class="ct-btn ct-btn-primary ct-btn-sm"
                     href="/coding/results/${encodeURIComponent(resultUuid)}/view"
                     target="_blank">
                    View Result
                  </a>
                ` : ''}
              </div>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }

  async function loadQuestionAttempts(uuid){
    try {
      const data = await api(
        `/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/${encodeURIComponent(uuid)}/my-attempts`,
        { method:'GET' }
      );

      const attempts = data?.attempts || data?.data || [];
      const question = LIST.find(q => q.uuid === uuid);
      const questionTitle = question?.title || 'Question';
      
      renderAttemptsModal(attempts, questionTitle);
    } catch(err) {
      $attemptsBody.innerHTML = `
        <div class="ct-alert ct-alert-warn">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div>
            <div class="t">Failed to load attempts</div>
            <div class="d">${esc(err.message || 'Unknown error occurred')}</div>
          </div>
        </div>`;
    }
  }

  async function loadIndex(){
    if(!token){
      $noToken.style.display = 'flex';
      setLoading(false);
      return;
    }
    if(!BATCH_ID){
      $noBatch.style.display = 'flex';
      setLoading(false);
      return;
    }

    $noToken.style.display = 'none';
    $noBatch.style.display = 'none';
    setLoading(true);

    try{
      const myRole = await getMyRole(token);

            RAW = await api(withModule(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions`), {
        method: 'GET'
      });

      ROLE = myRole || detectRole(RAW);
      console.log('[CodingTests]', { ROLE, CAN_MANAGE });

      CAN_MANAGE = ['admin', 'superadmin', 'instructor'].includes(ROLE);

      if ($assignBtn) {
        $assignBtn.style.display = CAN_MANAGE ? 'inline-flex' : 'none';
      }

      const arr = pickArray(RAW);
      LIST = Array.isArray(arr) ? arr.map(normalizeQuestion) : [];

      if(!CAN_MANAGE){
        LIST = LIST.filter(q => q.assigned === true || RAW?.only_assigned === true);
      }

      $listTitle.textContent = CAN_MANAGE ? 'Manage Batch Coding Questions' : 'Your Assigned Coding Questions';

           $hintPill.style.display = 'inline-flex';

      const modHint = MODULE_QS ? 'Module filter ON • ' : '';

      $hintPill.textContent = CAN_MANAGE
        ? (modHint + 'Set max attempts, then toggle Assign')
        : (modHint + 'Click ••• to view your previous attempts');


      updateStats();

      FILTERED = LIST.slice();
      setLoading(false);
      applyFilters();

    }catch(err){
      setLoading(false);
      $listTitle.textContent = 'Failed to load';
      toast(err.message || 'Failed to load');
    }
  }

  async function assignQuestion(uuid, maxAttempts){
    const payload = {
            ...modulePayload(),
      question_uuid: uuid,
      questionUuids: [uuid],
      question_uuids: [uuid],
      max_attempts: maxAttempts,
      allowed_attempts: maxAttempts,
      attempt_limit: maxAttempts,
      attempt_allowed: maxAttempts,
      attemptAllowed: maxAttempts,
      publish_to_students: 1,
      assign_status: 1
    };

    await api(withModule(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/assign`), {
      method:'POST',
      body: JSON.stringify(payload)
    });
  }

  async function unassignQuestion(uuid){
    await api(withModule(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/${encodeURIComponent(uuid)}`), {
      method:'DELETE'
    });
  }

  async function startQuestion(uuid){
    const payload = {  ...modulePayload(), batch_id: BATCH_ID, batch: BATCH_ID, question_uuid: uuid, questionUuid: uuid };

    const data = await api(`/judge/start`, { method:'POST', body: JSON.stringify(payload) });

    const attemptUuid =
      data?.attempt_uuid || data?.attemptUuid || data?.attempt?.uuid || data?.uuid || data?.data?.attempt_uuid || '';

    const url = new URL(TEST_URL, window.location.origin);
    url.searchParams.set('batch', BATCH_ID);
    url.searchParams.set('question', uuid);
    if (MODULE.id != null) url.searchParams.set('module_id', String(MODULE.id));
    if (MODULE.uuid) url.searchParams.set('module_uuid', MODULE.uuid);
    if(attemptUuid) url.searchParams.set('attempt', attemptUuid);

    window.location.href = url.toString();
  }

  window.addEventListener('codingQuestionsAssigned', ()=> {
    try { loadIndex(); } catch(e) { }
  });

  document.addEventListener('click', (e) => {
    const tab = e.target.closest('.ct-tab');
    if (!tab) return;

    document.querySelectorAll('.ct-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');

    const isPart = tab.dataset.tab === 'participated';
    $adminPart.style.display = isPart ? 'block' : 'none';
    $adminNot.style.display  = isPart ? 'none' : 'block';
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.ct-dropdown')) {
      document.querySelectorAll('.ct-dropdown-menu').forEach(menu => {
        menu.classList.remove('show');
      });
    }
  });

  $refresh.addEventListener('click', loadIndex);
  $search.addEventListener('input', applyFilters);
  $diff.addEventListener('change', applyFilters);

  if ($assignBtn) {
    $assignBtn.addEventListener('click', () => {
      try { openCodingQuestions(BATCH_ID); } catch (e) { console.error(e); }
    });
  }

  root.addEventListener('click', async (e)=>{
    const btn = e.target.closest('[data-act]');
    if(!btn) return;

    const act = btn.dataset.act;
    const uuid= btn.dataset.uuid;

    try{
      if(act === 'toggle-assign'){
  if(!CAN_MANAGE) return;

  const q = LIST.find(x=>x.uuid === uuid);
  if(!q) return;

  btn.style.pointerEvents = 'none';

  if(!q.assigned){
    await assignQuestion(uuid, q.maxAttempts);
    q.assigned = true;

    syncAssignSwitch(uuid, true); // 🔁
    toast('Assigned');
  }else{
    await unassignQuestion(uuid);
    q.assigned = false;

    syncAssignSwitch(uuid, false); // 🔁
    toast('Unassigned');
  }

  applyFilters();
  updateStats();
  btn.style.pointerEvents = '';
}

      if(act === 'toggle-dropdown'){
        e.stopPropagation();
        const dropdown = btn.closest('.ct-dropdown');
        const menu = dropdown.querySelector('.ct-dropdown-menu');
        menu.classList.toggle('show');
      }
      
      if(act === 'open-attempts-modal'){
        const dropdown = btn.closest('.ct-dropdown');
        const menu = dropdown.querySelector('.ct-dropdown-menu');
        menu.classList.remove('show');
        
        openAttemptsModal(uuid);
      }
      
      if(act === 'start'){
        await startQuestion(uuid);
      }
      
      if(act === 'close-modal'){
        closeAttemptsModal();
      }

      if (act === 'open-admin-results') {
        openAdminResultsModal(uuid);
      }

      if (act === 'close-admin-results') {
        closeAdminResultsModal();
      }

      if (act === 'toggle-student-attempts') {
        const box = btn.closest('.ct-student')
                       .querySelector('.ct-student-attempts');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
      }

      if (act === 'open-result') {
        window.open(
          `/coding/results/${encodeURIComponent(btn.dataset.result)}/view`,
          '_blank'
        );
      }

    }catch(err){
      toast(err.message || 'Action failed');
      if(act === 'toggle-assign'){
        btn.style.pointerEvents = '';
      }
    }
  });

  root.addEventListener('input', (e)=>{
    const inp = e.target.closest('input[data-act="attempts-input"]');
    if(!inp) return;
    const uuid = inp.dataset.uuid;
    const q = LIST.find(x=>x.uuid === uuid);
    if(!q) return;

    let v = Number(inp.value || 1);
    if(!Number.isFinite(v) || v < 1) v = 1;
    if(v > 999) v = 999;
    q.maxAttempts = v;
  });

  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape') {
      if($attemptsModal.style.display === 'grid') {
        closeAttemptsModal();
      }
      if($adminModal.style.display === 'grid') {
        closeAdminResultsModal();
      }
    }
  });

  // ================= ASSIGN MODAL FUNCTIONS =================

  function pickItems(j){
    if (Array.isArray(j?.data)) return j.data;
    if (Array.isArray(j?.data?.data)) return j.data.data;
    if (Array.isArray(j?.questions)) return j.questions;
    if (Array.isArray(j?.items)) return j.items;
    return [];
  }

  function pickPagination(j, fallbackLen){
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
    const v = (q?.attempt_allowed ?? q?.attempts_allowed);
    return (v === null || v === undefined || v === '') ? '' : Number(v);
  }

  function clampAttempts(v, hardMax){
    const lim = Math.max(1, Math.min(50, Number(hardMax) || 1));
    let n = parseInt(v, 10);
    if (!Number.isFinite(n) || n < 1) n = lim;
    if (n > lim) n = lim;
    return n;
  }

  function cqParams(){
    const p = new URLSearchParams();
    if (cq_q.value.trim()) p.set('q', cq_q.value.trim());
    p.set('per_page', cq_per.value || 20);
    p.set('page', cq_page);
    if (cq_assigned.value === 'assigned') p.set('assigned', '1');
    if (cq_assigned.value === 'unassigned') p.set('assigned', '0');
    return p.toString();
  }

  function firstError(j){
    if (typeof j?.errors === 'object' && j.errors) {
      const firstKey = Object.keys(j.errors)[0];
      if (firstKey && Array.isArray(j.errors[firstKey])) {
        return j.errors[firstKey][0];
      }
    }
    return '';
  }

  function ok(msg){
    toast(msg);
  }

  function err(msg){
    toast(msg);
  }

  async function assignCodingQuestion(batchKey, questionUuid, attemptAllowed, quiet=false){
    const payload = {
            ...modulePayload(),

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

    const assignUrl =
      `/api/batches/${encodeURIComponent(batchKey)}/coding-questions/assign` +
      (MODULE_QS ? `?${MODULE_QS}` : '');

    const res = await fetch(assignUrl, {
      method: 'POST',
      headers: { 'Authorization':'Bearer '+token, 'Content-Type':'application/json', 'Accept':'application/json' },
      body: JSON.stringify(payload)
    });

    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Assign failed');
    if(!quiet) ok('Coding question assigned');

    if (typeof window !== 'undefined') {
      try {
        window.dispatchEvent(new CustomEvent('codingQuestionsAssigned', { detail: { questionUuid, assigned: true } }));
      } catch(e) {}
    }

    return j;
  }

  async function unassignCodingQuestion(batchKey, questionUuid, quiet=false){
    const delUrl =
      `/api/batches/${encodeURIComponent(batchKey)}/coding-questions/${encodeURIComponent(questionUuid)}` +
      (MODULE_QS ? `?${MODULE_QS}` : '');

    const res = await fetch(delUrl, {
      method: 'DELETE',
      headers: { 'Authorization':'Bearer '+token, 'Accept':'application/json' }
    });

    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || firstError(j) || 'Unassign failed');
    if(!quiet) ok('Coding question unassigned');

    if (typeof window !== 'undefined') {
      try {
        window.dispatchEvent(new CustomEvent('codingQuestionsAssigned', { detail: { questionUuid, assigned: false } }));
      } catch(e) {}
    }

    return j;
  }

  async function loadCodingQuestions(){
    if(!cq_batch_key) return;

    cq_loader.style.display = '';
    cq_rows.querySelectorAll('tr:not(#cq_loader)').forEach(tr=>tr.remove());

    try{
      const baseUrl = `/api/batches/${encodeURIComponent(cq_batch_key)}/coding-questions?mode=all`;
      const url = baseUrl + (MODULE_QS ? `&${MODULE_QS}` : '') + `&${cqParams()}`;

      const res = await fetch(url, {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
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
        syncAssignSwitch(qUuid, assigned);
      });

      cq_rows.appendChild(frag);

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

          try{
            if(wantAssigned){
              await assignCodingQuestion(cq_batch_key, questionUuid, attemptAllowed);
            }else{
              await unassignCodingQuestion(cq_batch_key, questionUuid);
            }
              syncAssignSwitch(questionUuid, wantAssigned); 
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

      cq_rows.querySelectorAll('.cq-attempt').forEach(inp=>{
        inp.addEventListener('blur', async ()=>{
          const row = inp.closest('tr');
          const ch  = row.querySelector('.cq-tg');
          if(!ch) return;

          const questionUuid = ch.dataset.uuid;
          if(!questionUuid) return;

          const limit = Number(inp.max || 1);
          const attemptAllowed = clampAttempts(inp.value, limit);
          inp.value = String(attemptAllowed);

          try{
            await assignCodingQuestion(cq_batch_key, questionUuid, attemptAllowed, true);
            ok('Attempts updated');
            if (typeof window !== 'undefined') {
              try { window.dispatchEvent(new CustomEvent('codingQuestionsAssigned', { detail: { questionUuid, assigned: true } })); } catch(e) {}
            }
          }catch(e){
            err(e.message || 'Failed to update attempts');
          }
        });
      });

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

  cq_apply.addEventListener('click', ()=>{ cq_page = 1; loadCodingQuestions(); });
  cq_per.addEventListener('change', ()=>{ cq_page = 1; loadCodingQuestions(); });
  cq_assigned.addEventListener('change', ()=>{ cq_page = 1; loadCodingQuestions(); });

  cq_q.addEventListener('input', ()=>{
    clearTimeout(cqT);
    cqT = setTimeout(()=>{ cq_page = 1; loadCodingQuestions(); }, 350);
  });

  loadIndex();
})();
</script>