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
  const token     = sessionStorage.getItem('token');

  const el = (id) => document.getElementById(id);

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

  function openAttemptsModal(uuid){
    $attemptsModal.style.display = 'grid';
    
    // Set loading state
    $attemptsBody.innerHTML = `
      <div class="ct-skeleton">
        <div class="r"></div><div class="r"></div><div class="r"></div>
      </div>`;
    
    // Load attempts for this specific question
    loadQuestionAttempts(uuid);
  }
  
  function closeAttemptsModal(){
    $attemptsModal.style.display = 'none';
    $attemptsBody.innerHTML = '';
  }
  
  // Close modal when clicking backdrop
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

  let RAW = null;
  let ROLE = '';
  let CAN_MANAGE = false;
  let LIST = [];       // normalized questions
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
    // try common response shapes
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

    // student attempts (array OR count)
    let attemptsUsed = 0;

// If attempts array exists → take highest attempt_no
const arr =
  q?.my_attempts ||
  q?.attempts ||
  [];

if (Array.isArray(arr) && arr.length) {
  attemptsUsed = Math.max(
    ...arr.map(a => Number(a?.attempt_no || 0))
  );
} else {
  // fallback if API gives last_attempt_no directly
  attemptsUsed = Number(
    q?.last_attempt_no ??
    q?.attempt_no ??
    0
  );
}

    // assignment limit
    // assignment limit (AUTHORITATIVE)
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

  /* =========================================================
     PRIVILEGED USERS (CAN_MANAGE === true)
     ========================================================= */
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

  /* =========================================================
     NON-PRIVILEGED USERS (NO RESTRICTIONS)
     ========================================================= */
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
          ${s.attempts.map(a => `
            <div class="ct-attempt-row"
                 data-act="open-result"
                 data-result="${esc(a.result_uuid)}">
              Attempt #${a.attempt_no}
              <span class="ct-status ${a.status === 'PASS' ? 'pass' : 'fail'}">
                ${a.status}
              </span>
            </div>
          `).join('')}
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

  // ✅ IMPORTANT: sort attempts by real attempt_no
  attempts = Array.isArray(attempts)
    ? [...attempts].sort(
        (a, b) => (a.attempt_no ?? 0) - (b.attempt_no ?? 0)
      )
    : [];

  // ✅ Empty state
  if(!attempts.length){
    $attemptsBody.innerHTML = `
      <div class="ct-empty">
        <div class="ct-empty-ico"><i class="fa-regular fa-folder-open"></i></div>
        <div class="ct-empty-t">No attempts found</div>
        <div class="ct-empty-d">You haven't attempted this question yet.</div>
      </div>`;
    return;
  }

  // ✅ Render
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
      // ✅ 1. Fetch real role from auth (authoritative)
const myRole = await getMyRole(token);

// ✅ 2. Load batch questions
RAW = await api(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions`, {
  method: 'GET'
});

// ✅ 3. Decide role (auth role > API role)
ROLE = myRole || detectRole(RAW);
console.log('[CodingTests]', { ROLE, CAN_MANAGE });

// ✅ 4. Decide permissions
CAN_MANAGE = ['admin', 'superadmin', 'instructor'].includes(ROLE);

      const arr = pickArray(RAW);
      LIST = Array.isArray(arr) ? arr.map(normalizeQuestion) : [];

      // If student & API returns all questions, optionally auto-hide unassigned:
      if(!CAN_MANAGE){
        // show only assigned by default
        LIST = LIST.filter(q => q.assigned === true || RAW?.only_assigned === true);
      }

      $listTitle.textContent = CAN_MANAGE ? 'Manage Batch Coding Questions' : 'Your Assigned Coding Questions';

      $hintPill.style.display = 'inline-flex';
      $hintPill.textContent = CAN_MANAGE
        ? 'Set max attempts, then toggle Assign'
        : 'Click ••• to view your previous attempts';

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
      question_uuid: uuid,
      questionUuids: [uuid],
      question_uuids: [uuid],
      max_attempts: maxAttempts,
      allowed_attempts: maxAttempts,
      attempt_limit: maxAttempts
    };

    await api(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/assign`, {
      method:'POST',
      body: JSON.stringify(payload)
    });
  }

  async function unassignQuestion(uuid){
    await api(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/${encodeURIComponent(uuid)}`, {
      method:'DELETE'
    });
  }

  async function startQuestion(uuid){
    const payload = { batch_id: BATCH_ID, batch: BATCH_ID, question_uuid: uuid, questionUuid: uuid };

    const data = await api(`/judge/start`, { method:'POST', body: JSON.stringify(payload) });

    const attemptUuid =
      data?.attempt_uuid || data?.attemptUuid || data?.attempt?.uuid || data?.uuid || data?.data?.attempt_uuid || '';

    const url = new URL(TEST_URL, window.location.origin);
    url.searchParams.set('batch', BATCH_ID);
    url.searchParams.set('question', uuid);
    if(attemptUuid) url.searchParams.set('attempt', attemptUuid);

    window.location.href = url.toString();
  }

  // Tab switching for admin modal
  document.addEventListener('click', (e) => {
    const tab = e.target.closest('.ct-tab');
    if (!tab) return;

    document.querySelectorAll('.ct-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');

    const isPart = tab.dataset.tab === 'participated';
    $adminPart.style.display = isPart ? 'block' : 'none';
    $adminNot.style.display  = isPart ? 'none' : 'block';
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.ct-dropdown')) {
      document.querySelectorAll('.ct-dropdown-menu').forEach(menu => {
        menu.classList.remove('show');
      });
    }
  });

  // Event listeners
  $refresh.addEventListener('click', loadIndex);
  $search.addEventListener('input', applyFilters);
  $diff.addEventListener('change', applyFilters);

  // Delegate clicks
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

        const input = root.querySelector(`input[data-act="attempts-input"][data-uuid="${CSS.escape(uuid)}"]`);
        let maxA = input ? Number(input.value || 1) : Number(q.maxAttempts||1);
        if(!Number.isFinite(maxA) || maxA < 1) maxA = 1;
        if(maxA > 999) maxA = 999;

        btn.style.pointerEvents = 'none';

        if(!q.assigned){
          await assignQuestion(uuid, maxA);
          q.assigned = true;
          q.maxAttempts = maxA;
          toast('Assigned');
        }else{
          await unassignQuestion(uuid);
          q.assigned = false;
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
        // Close the dropdown
        const dropdown = btn.closest('.ct-dropdown');
        const menu = dropdown.querySelector('.ct-dropdown-menu');
        menu.classList.remove('show');
        
        // Open the modal
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

  // Keep attempts input synced
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

  // Close modal with escape key
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

  // Initialize
  loadIndex();
})();
</script>