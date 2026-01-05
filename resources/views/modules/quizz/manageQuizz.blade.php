{{-- resources/views/modules/quizz/manageQuizz.blade.php --}}
@section('title','Manage Quizzes')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== COMPLETE DROPDOWN FIX FOR QUIZ MANAGEMENT ===== */

/* 1. Ensure all containers allow overflow */
.qz-wrap,
.table-wrap,
.card,
.card-body,
.table-responsive,
.tab-content,
.tab-pane {
  position: relative !important;
  overflow: visible !important;
  transform: none !important;
  filter: none !important;
  perspective: none !important;
}

/* 2. Force table to allow overflow */
.table {
  position: relative;
  overflow: visible !important;
}

/* 3. Table cells and rows */
.table tbody tr {
  position: relative;
  overflow: visible !important;
}

.table tbody tr td {
  overflow: visible !important;
}

.table tbody tr td:last-child {
  position: relative;
  z-index: 1;
  overflow: visible !important;
}

.table td:has(.dropdown),
.table th:has(.dropdown) {
  position: static !important;
  overflow: visible !important;
}

/* 4. Dropdown container - static positioning */
.dropdown {
  position: static !important;
}

/* 5. Dropdown toggle button */
.dd-toggle,
[data-bs-toggle="dropdown"] {
  position: relative;
  z-index: 10;
  border-radius: 8px;
  transition: all 0.15s ease;
}

.dd-toggle:hover,
[data-bs-toggle="dropdown"]:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* 6. Non-portaled dropdown menu (fallback) */
.dropdown-menu:not(.dd-portal) {
  position: fixed !important;
  z-index: 999999 !important;
  min-width: 220px;
  max-width: 300px;
  border-radius: 12px;
  border: 1px solid var(--line-strong, #e5e7eb);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
  background: var(--surface, #ffffff);
  padding: 0.5rem 0;
}

/* 7. Portaled dropdown menu - HIGHEST priority */
.dropdown-menu.dd-portal {
  position: fixed !important;
  left: 0 !important;
  top: 0 !important;
  transform: none !important;
  z-index: 999999 !important;
  min-width: 220px;
  max-width: 300px;
  border-radius: 12px;
  border: 1px solid var(--line-strong, #e5e7eb);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2), 
              0 0 0 1px rgba(0, 0, 0, 0.05);
  background: var(--surface, #ffffff);
  overflow: visible !important;
  padding: 0.5rem 0;
  margin: 0 !important;
  will-change: transform;
}

/* 8. Force show state */
.dropdown-menu.show {
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
  z-index: 999999 !important;
  overflow: visible !important;
}

/* 9. Remove Bootstrap's default popper positioning */
.dropdown-menu[data-bs-popper] {
  position: fixed !important;
}

/* 10. Dropdown items */
.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.5rem 1rem;
  cursor: pointer;
  transition: background-color 0.15s ease;
  border: none;
  background: none;
  text-decoration: none;
  color: var(--ink, #1f2937);
  width: 100%;
  text-align: left;
  white-space: nowrap;
}

.dropdown-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
  color: var(--ink, #1f2937);
}

.dropdown-item i {
  width: 18px;
  text-align: center;
  flex-shrink: 0;
  opacity: 0.8;
}

.dropdown-item.text-danger {
  color: #ef4444 !important;
}

.dropdown-item.text-danger:hover {
  background-color: rgba(239, 68, 68, 0.08);
  color: #dc2626 !important;
}

/* 11. Divider */
.dropdown-divider {
  margin: 0.375rem 0;
  border-top: 1px solid var(--line-strong, #e5e7eb);
}

/* 12. Dark mode support */
html.theme-dark .dropdown-menu,
html.theme-dark .dropdown-menu.dd-portal {
  background: #0f172a;
  border-color: #334155;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 
              0 0 0 1px rgba(255, 255, 255, 0.1);
}

html.theme-dark .dropdown-item {
  color: #e5e7eb;
}

html.theme-dark .dropdown-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: #f3f4f6;
}

html.theme-dark .dropdown-item.text-danger {
  color: #f87171 !important;
}

html.theme-dark .dropdown-item.text-danger:hover {
  background-color: rgba(239, 68, 68, 0.15);
  color: #fca5a5 !important;
}

html.theme-dark .dropdown-divider {
  border-color: #334155;
}

/* 13. Pagination and footer */
.pagination {
  position: relative;
  z-index: 1;
}

.card-body > .d-flex:last-child {
  padding: 1rem;
  overflow: visible !important;
}

/* 14. Nav tabs positioning */
.nav-tabs {
  position: relative;
  z-index: 1;
}

/* 15. Special handling for last rows */
.table tbody tr:nth-last-child(-n+3) .dropdown-menu {
  margin-bottom: 0 !important;
}

/* 16. Status badges - keep original styles */
.badge-soft {
  background: color-mix(in oklab, var(--muted-color) 12%, transparent);
  color: var(--ink);
}

.table .badge.badge-success {
  background: var(--success-color) !important;
  color: #fff !important;
}

.table .badge.badge-secondary {
  background: #64748b !important;
  color: #fff !important;
}

/* 17. Responsive adjustments */
@media (max-width: 768px) {
  .dropdown-menu,
  .dropdown-menu.dd-portal {
    min-width: 180px;
    max-width: 240px;
  }
  
  .dropdown-item {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
  }
}
/* ===== Status Badges ===== */
.badge-soft {
  background: color-mix(in oklab, var(--muted-color) 12%, transparent);
  color: var(--ink);
}

.table .badge.badge-success {
  background: var(--success-color) !important;
  color: #fff !important;
}

.table .badge.badge-secondary {
  background: #64748b !important;
  color: #fff !important;
}

/* ===== Sorting ===== */
.sortable {
  cursor: pointer;
  white-space: nowrap;
  user-select: none;
}

.sortable .caret {
  display: inline-block;
  margin-left: 0.35rem;
  opacity: 0.65;
}

.sortable.asc .caret::after {
  content: "▲";
  font-size: 0.7rem;
}

.sortable.desc .caret::after {
  content: "▼";
  font-size: 0.7rem;
}

/* ===== Row Visual Cues ===== */
tr.is-archived td {
  background: color-mix(in oklab, var(--muted-color) 6%, transparent);
}

tr.is-deleted td {
  background: color-mix(in oklab, var(--danger-color) 6%, transparent);
}

/* ===== Action Icon Style ===== */
.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 34px;
  min-width: 34px;
  padding: 0 10px;
  border: 1px solid var(--line-strong);
  background: var(--surface);
  border-radius: 10px;
  transition: box-shadow 0.2s ease;
}

.icon-btn:hover {
  box-shadow: var(--shadow-1);
}

/* ===== Empty State & Loader ===== */
.empty {
  color: var(--muted-color);
}

.placeholder {
  background: linear-gradient(90deg, #00000010, #00000005, #00000010);
  border-radius: 8px;
  animation: placeholder-wave 2s ease-in-out infinite;
}

@keyframes placeholder-wave {
  0% {
    background-position: -200px 0;
  }
  100% {
    background-position: calc(200px + 100%) 0;
  }
}

.placeholder-wave {
  animation: placeholder-wave 2s linear infinite;
  background-size: 200px 100%;
}

/* ===== Modals ===== */
.modal-content {
  border-radius: 16px;
  border: 1px solid var(--line-strong);
  background: var(--surface);
}

.modal-header {
  border-bottom: 1px solid var(--line-strong);
}

.modal-footer {
  border-top: 1px solid var(--line-strong);
}

.form-control,
.form-select {
  border-radius: 12px;
  border: 1px solid var(--line-strong);
  background: #fff;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-control:focus,
.form-select:focus {
  border-color: var(--primary-color, #3b82f6);
  box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.1);
  outline: none;
}

/* ===== Dark Mode Tweaks ===== */
html.theme-dark .form-control,
html.theme-dark .form-select {
  background: #0f172a;
  color: #e5e7eb;
  border-color: var(--line-strong);
}

html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content {
  background: #0f172a;
  border-color: var(--line-strong);
}

html.theme-dark .table thead th {
  background: #0f172a;
  border-color: var(--line-strong);
  color: #94a3b8;
}

html.theme-dark .table tbody tr {
  border-color: var(--line-soft);
}

html.theme-dark .dropdown-menu {
  background: #0f172a;
  border-color: var(--line-strong);
}

/* ===== Randomization Modal Tweaks ===== */
#randomizeModal .form-check {
  display: grid;
  grid-template-columns: auto 1fr;
  column-gap: 0.8rem;
  align-items: flex-start;
  margin-bottom: 1rem;
}

#randomizeModal .form-check-input {
  margin-left: 0;
  margin-top: 0.25rem;
  cursor: pointer;
}

#randomizeModal .form-check-label {
  margin-bottom: 0;
  grid-column: 2;
  cursor: pointer;
}

#randomizeModal .form-text {
  grid-column: 2;
  margin-top: 0.15rem;
  font-size: 0.875rem;
  color: var(--muted-color);
}

/* ===== Additional Table Improvements ===== */
.table {
  margin-bottom: 0;
}

.table thead {
  position: sticky;
  top: 0;
  z-index: 10;
  background: var(--surface);
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

html.theme-dark .table-hover tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* ===== Button Loading States ===== */
.btn .spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ===== Pagination Styling ===== */
.pagination {
  gap: 0.25rem;
}

.page-link {
  border-radius: 8px;
  border: 1px solid var(--line-strong);
  transition: all 0.2s ease;
}

.page-link:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.page-item.active .page-link {
  background-color: var(--primary-color, #3b82f6);
  border-color: var(--primary-color, #3b82f6);
}

html.theme-dark .page-link {
  background: #0f172a;
  color: #e5e7eb;
}

html.theme-dark .page-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* ===== Responsive Adjustments ===== */
@media (max-width: 768px) {
  .dropdown-menu {
    min-width: 180px;
  }
  
  .table-responsive {
    font-size: 0.9rem;
  }
  
  .qz-wrap {
    margin: 12px;
  }
}

</style>
@endpush

@section('content')
<div class="qz-wrap">

  {{-- ================= Tabs ================= --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-quizzes" role="tab" aria-selected="true"><i class="fa-solid fa-layer-group me-2"></i>Quizzes</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-archived" role="tab" aria-selected="false"><i class="fa-solid fa-folder me-2"></i>Archived</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-deleted" role="tab" aria-selected="false"><i class="fa-solid fa-trash me-2"></i>Bin</a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ========== TAB: Quizzes (active list) ========== --}}
    <div class="tab-pane fade show active" id="tab-quizzes" role="tabpanel">
      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="per_page" class="form-select" style="width:96px;">
              <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
            </select>
          </div>
          
          <div class="position-relative" style="min-width:300px;">
            <input id="q" type="text" class="form-control ps-5" placeholder="Search by title…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="btnFilter" class="btn btn-primary ms-1" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fa fa-filter me-1"></i>Filter
          </button>
          <button id="btnReset" class="btn btn-primary"><i class="fa fa-rotate-left me-1"></i>Reset</button>
        </div>

        <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end">
          <a id="btnCreate" href="/quizz/create" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i>New Quiz
          </a>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th class="sortable" data-col="quiz_name">QUIZ <span class="caret"></span></th>
                  <th style="width:120px;">PUBLIC</th>
                  <th class="sortable" data-col="status" style="width:130px;">STATUS <span class="caret"></span></th>
                  <th style="width:140px;">ATTEMPTS</th>
                  <th style="width:160px;">RESULT SETUP</th>
                  <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-active">
                <tr id="loaderRow-active" style="display:none;">
                  <td colspan="7" class="p-0">
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

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-folder-open mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No quizzes found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-active">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-active" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== TAB: Archived (Result Setup hidden) ========== --}}
    <div class="tab-pane fade" id="tab-archived" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>QUIZ</th>
                  <th style="width:120px;">PUBLIC</th>
                  <th style="width:140px;">ATTEMPTS</th>
                  {{-- RESULT SETUP intentionally hidden --}}
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:112px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-archived">
                <tr id="loaderRow-archived" style="display:none;">
                  <td colspan="5" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-archived" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-box-archive mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No archived quizzes.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-archived">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-archived" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== TAB: Bin (Deleted) ========== --}}
    <div class="tab-pane fade" id="tab-deleted" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>QUIZ</th>
                  <th style="width:120px;">PUBLIC</th>
                  <th style="width:140px;">ATTEMPTS</th>
                  <th style="width:170px;">DELETED AT</th>
                  <th class="text-end" style="width:160px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="rows-deleted">
                <tr id="loaderRow-deleted" style="display:none;">
                  <td colspan="5" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-deleted" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No items in Bin.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="metaTxt-deleted">—</div>
            <nav style="position:relative; z-index:1;"><ul id="pager-deleted" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.tab-content -->

</div>

{{-- Filter Modal --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filter Quizzes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="filterIsPublic" class="form-label">Public</label>
          <select id="filterIsPublic" class="form-select">
            <option value="">All</option>
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="btnApplyFilters" type="button" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Notes Modal --}}
<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-note-sticky me-2"></i>Quiz Notes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="notesList" class="mb-3 small text-muted">Loading…</div>
        <label class="form-label">Add a note</label>
        <textarea id="noteText" class="form-control" rows="4" placeholder="Type a short audit/comment…"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button id="btnAddNote" class="btn btn-primary"><i class="fa fa-plus me-1"></i>Add Note</button>
      </div>
    </div>
  </div>
</div>

{{-- Randomization Modal --}}
<div class="modal fade" id="randomizeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-shuffle me-2"></i>
          Randomization
          <small class="text-muted d-block fs-6" id="randomQuizTitle"></small>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="toggleQuestionRandom">
          <label class="form-check-label fw-semibold" for="toggleQuestionRandom">
            Randomize question order
          </label>
          <div class="form-text">
            Each student will see the questions in a different order.
          </div>
        </div>

        <div class="form-check form-switch mb-2">
          <input class="form-check-input" type="checkbox" id="toggleOptionRandom">
          <label class="form-check-label fw-semibold" for="toggleOptionRandom">
            Randomize options within each question
          </label>
          <div class="form-text">
            Shuffles choices for MCQ / True–False. Fill in the blanks are not affected.
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
       <button id="btnSaveRandom" class="btn btn-primary">
  <span class="lbl">
    <i class="fa fa-check me-1"></i>Save
  </span>
  <span class="spin d-none">
    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    Saving…
  </span>
</button>

      </div>
    </div>
  </div>
</div>


{{-- Toasts (success/error only) --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
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
/* ===== Force dropdown overflows to body (portal) ===== */
/* ===== IMPROVED DROPDOWN HANDLER WITH PORTAL APPROACH ===== */
/* ===== ENHANCED DROPDOWN PORTAL LOGIC ===== */
(function() {
  let activePortal = null;

  /**
   * Places dropdown menu with smart positioning
   */
  function placeMenuInBody(menu, btnRect) {
    const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    const vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    
    // Add portal class
    menu.classList.add('dd-portal');
    
    // Show for measurement
    menu.style.display = 'block';
    menu.style.visibility = 'hidden';
    
    // Append to body
    document.body.appendChild(menu);
    
    // Get dimensions
    const menuWidth = menu.offsetWidth;
    const menuHeight = menu.offsetHeight;
    
    // Horizontal positioning
    let left = btnRect.left;
    const spaceRight = vw - btnRect.right;
    
    if (spaceRight < menuWidth && btnRect.right - menuWidth > 8) {
      left = btnRect.right - menuWidth;
    }
    
    // Keep within viewport horizontally
    left = Math.max(8, Math.min(left, vw - menuWidth - 8));
    
    // Vertical positioning - smart detection
    let top = btnRect.bottom + 4;
    const spaceBelow = vh - btnRect.bottom;
    const spaceAbove = btnRect.top;
    
    // If not enough space below but enough above, position above
    if (spaceBelow < menuHeight + 20 && spaceAbove > menuHeight + 20) {
      top = btnRect.top - menuHeight - 4;
    } else if (spaceBelow < menuHeight + 20) {
      // Not enough space either way
      top = Math.max(8, Math.min(top, vh - menuHeight - 8));
    }
    
    // Apply position
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    menu.style.visibility = 'visible';
  }

  /**
   * Closes active portaled dropdown
   */
  function closeActivePortal() {
    if (activePortal && activePortal.menu && activePortal.menu.isConnected) {
      activePortal.menu.classList.remove('dd-portal');
      activePortal.menu.style.cssText = '';
      
      if (activePortal.parent) {
        activePortal.parent.appendChild(activePortal.menu);
      }
      
      activePortal = null;
    }
  }

  /**
   * Show dropdown event
   */
  document.addEventListener('show.bs.dropdown', function(event) {
    const dropdownElement = event.target;
    const toggleBtn = dropdownElement.querySelector('.dd-toggle, [data-bs-toggle="dropdown"]');
    const menu = dropdownElement.querySelector('.dropdown-menu');
    
    if (!toggleBtn || !menu) return;
    
    // Close existing
    closeActivePortal();
    
    // Store parent
    menu.__originalParent = menu.parentElement;
    
    // Get position
    const rect = toggleBtn.getBoundingClientRect();
    
    // Place in body
    placeMenuInBody(menu, rect);
    
    // Store reference
    activePortal = {
      menu: menu,
      parent: menu.__originalParent
    };
    
    // Close on scroll/resize
    const closeHandler = () => {
      try {
        const instance = bootstrap.Dropdown.getInstance(toggleBtn);
        if (instance) instance.hide();
      } catch(e) {
        console.error('Error closing dropdown:', e);
      }
    };
    
    menu.__closeHandler = closeHandler;
    window.addEventListener('scroll', closeHandler, true);
    window.addEventListener('resize', closeHandler);
  });

  /**
   * Hide dropdown event
   */
  document.addEventListener('hidden.bs.dropdown', function(event) {
    const dropdownElement = event.target;
    const menu = dropdownElement.querySelector('.dropdown-menu.dd-portal');
    
    if (!menu && !activePortal) return;
    
    const targetMenu = menu || (activePortal && activePortal.menu);
    
    if (targetMenu) {
      // Remove listeners
      if (targetMenu.__closeHandler) {
        window.removeEventListener('scroll', targetMenu.__closeHandler, true);
        window.removeEventListener('resize', targetMenu.__closeHandler);
        targetMenu.__closeHandler = null;
      }
      
      // Restore to parent
      targetMenu.classList.remove('dd-portal');
      targetMenu.style.cssText = '';
      
      if (targetMenu.__originalParent) {
        targetMenu.__originalParent.appendChild(targetMenu);
        targetMenu.__originalParent = null;
      }
    }
    
    activePortal = null;
  });

  /**
   * Handle toggle clicks
   */
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.dd-toggle');
    if (!btn) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    // Close other dropdowns
    document.querySelectorAll('.dd-toggle').forEach(otherBtn => {
      if (otherBtn !== btn) {
        try {
          const instance = bootstrap.Dropdown.getInstance(otherBtn);
          if (instance) instance.hide();
        } catch(e) {}
      }
    });
    
    // Toggle this dropdown
    const instance = bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: true,
      boundary: 'viewport'
    });
    
    instance.toggle();
  });

  /**
   * Close when clicking outside
   */
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dd-toggle')) {
      closeActivePortal();
    }
  });

})();
(function(){
  /* ========= Auth / base panel ========= */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const ROLE  = (localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase();
  const basePanel = (ROLE.includes('super') ? '/super_admin' : '/admin');
  if (!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }
  document.getElementById('btnCreate').setAttribute('href','/quizz/create');

  /* ========= Toast helpers ========= */
  const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
  const errToast = new bootstrap.Toast(document.getElementById('errToast'));
  const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  /* ========= DOM refs per tab ========= */
  const tabs = {
    active:   { rows:'#rows-active',   loader:'#loaderRow-active',   empty:'#empty-active',   meta:'#metaTxt-active',   pager:'#pager-active'   },
    archived: { rows:'#rows-archived', loader:'#loaderRow-archived', empty:'#empty-archived', meta:'#metaTxt-archived', pager:'#pager-archived' },
    deleted:  { rows:'#rows-deleted',  loader:'#loaderRow-deleted',  empty:'#empty-deleted',  meta:'#metaTxt-deleted',  pager:'#pager-deleted'  },
  };

  /* ========= Shared filter elements (Active tab) ========= */
  const q = document.getElementById('q');
  const filterIsPublicSel = document.getElementById('filterIsPublic');
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const perPageSel  = document.getElementById('per_page');
  const btnReset    = document.getElementById('btnReset');

  /* ========= State ========= */
  let sort = '-created_at';
  const state = { active:{page:1}, archived:{page:1}, deleted:{page:1} };

  /* ========= Utils ========= */
  const esc=(s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>m[ch]); };
  const fmtDate=(iso)=>{ if(!iso) return '-'; const d=new Date(iso); if(isNaN(d)) return esc(iso); return d.toLocaleString(undefined,{year:'numeric',month:'short','day':'2-digit','hour':'2-digit','minute':'2-digit'}); };
  const badgeStatus=(s)=>{ const map={active:'success',archived:'secondary'}; const cls=map[s]||'secondary'; return `<span class="badge badge-${cls} text-uppercase">${esc(s||'-')}</span>`; };
  const qs=(sel)=>document.querySelector(sel);
  const qsa=(sel)=>document.querySelectorAll(sel);
  const showLoader=(scope, v)=>{ qs(tabs[scope].loader).style.display = v ? '' : 'none'; };
  const setBtnBusy = (btn, on) => {
  if (!btn) return;
  btn.disabled = !!on;
  const lbl  = btn.querySelector('.lbl');
  const spin = btn.querySelector('.spin');
  if (lbl)  lbl.classList.toggle('d-none', !!on);
  if (spin) spin.classList.toggle('d-none', !on);
};


  function paramsBase(scope){
    const usp = new URLSearchParams();
    const p = state[scope].page || 1;
    const pp = Number(perPageSel?.value || 20);
    usp.set('page', p); usp.set('per_page', pp);
    usp.set('sort', sort);

    if (scope === 'active'){
      if (q && q.value.trim()) usp.set('q', q.value.trim());
      if (filterIsPublicSel && filterIsPublicSel.value) usp.set('is_public', filterIsPublicSel.value);
      usp.set('status','active');
    } else if (scope === 'archived'){
      usp.set('status','archived');
    }
    return usp.toString();
  }

  function urlFor(scope){
    if (scope === 'deleted') return '/api/quizz?only_deleted=1&' + paramsBase(scope);
    return '/api/quizz?' + paramsBase(scope);
  }

  /* ========= Row builders ========= */
  function actionMenu(scope, r){
    const key = r.uuid || r.id;
    if (scope === 'active' || scope === 'archived'){
      return `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" data-act="edit" data-key="${key}" data-name="${esc(r.quiz_name||'')}">
              <i class="fa fa-pen-to-square"></i> Edit
            </button></li>
            <li><button class="dropdown-item" data-act="questions" data-key="${key}" data-name="${esc(r.quiz_name||'')}">
              <i class="fa fa-list-check"></i> View Questions
            </button></li>
            <li>
              <button class="dropdown-item" data-act="testrun" data-key="${key}" data-name="${esc(r.quiz_name||'')}">
                <i class="fa-solid fa-vial"></i> Test Run
              </button>
            </li>
            <li><button class="dropdown-item" data-act="notes" data-key="${key}" data-name="${esc(r.quiz_name||'')}">
              <i class="fa fa-note-sticky"></i> Notes
            </button></li>
            <li><button class="dropdown-item" data-act="randomize" data-key="${key}" data-name="${esc(r.quiz_name||'')}">
          <i class="fa fa-shuffle"></i> Randomize
        </button></li>
            <li><hr class="dropdown-divider"></li>
            ${String(r.status||'').toLowerCase()==='archived'
              ? `<li><button class="dropdown-item" data-act="unarchive" data-key="${key}" data-name="${esc(r.quiz_name||'')}"><i class="fa fa-box-open"></i> Unarchive</button></li>`
              : `<li><button class="dropdown-item" data-act="archive" data-key="${key}" data-name="${esc(r.quiz_name||'')}"><i class="fa fa-box-archive"></i> Archive</button></li>`
            }
            <li><button class="dropdown-item text-danger" data-act="delete" data-key="${key}" data-name="${esc(r.quiz_name||'')}">
              <i class="fa fa-trash"></i> Delete
            </button></li>
          </ul>
        </div>`;
    }
    // Bin
    return `
      <div class="dropdown text-end" data-bs-display="static">
        <button type="button" class="btn btn-light btn-sm dd-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="fa fa-ellipsis-vertical"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" data-act="restore" data-key="${key}" data-name="${esc(r.quiz_name||'')}"><i class="fa fa-rotate-left"></i> Restore</button></li>
          <li><button class="dropdown-item text-danger" data-act="force" data-key="${key}" data-name="${esc(r.quiz_name||'')}"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>
        </ul>
      </div>`;
    }

  function rowHTML(scope, r){
    const isArchived = String(r.status||'').toLowerCase()==='archived';
    const isDeleted  = !!r.deleted_at;
    const name = esc(r.quiz_name || '-');
    const pub  = esc(r.is_public || '-');
    const attempts = (r.total_attempts ?? 1);
    const setup = esc(r.result_set_up_type || 'Immediately');
    const created = fmtDate(r.created_at);
    const delAt   = fmtDate(r.deleted_at);

    let tr = document.createElement('tr');
    if (isArchived && !isDeleted && scope!=='deleted') tr.classList.add('is-archived');
    if (isDeleted || scope==='deleted') tr.classList.add('is-deleted');

    if (scope==='deleted'){
      tr.innerHTML = `
        <td>
          <div class="fw-semibold"><a href="${basePanel}/quizz/${encodeURIComponent(r.uuid || r.id)}" class="link-offset-2 link-underline-opacity-0">${name}</a></div>
          <div class="text-muted small">${(r.question_count ?? 0)} Qs • ${(r.student_count ?? 0)} students</div>
        </td>
        <td class="text-capitalize">${pub}</td>
        <td>${attempts} attempt(s)</td>
        <td>${delAt}</td>
        <td class="text-end">${actionMenu(scope, r)}</td>`;
      return tr;
    }

    if (scope==='archived'){
      tr.innerHTML = `
        <td>
          <div class="fw-semibold"><a href="${basePanel}/quizz/${encodeURIComponent(r.uuid || r.id)}" class="link-offset-2 link-underline-opacity-0">${name}</a></div>
          <div class="text-muted small">${(r.question_count ?? 0)} Qs • ${(r.student_count ?? 0)} students</div>
        </td>
        <td class="text-capitalize">${pub}</td>
        <td>${attempts} attempt(s)</td>
        <td>${created}</td>
        <td class="text-end">${actionMenu(scope, r)}</td>`;
      return tr;
    }

    // active
    tr.innerHTML = `
      <td>
        <div class="fw-semibold"><a href="${basePanel}/quizz/${encodeURIComponent(r.uuid || r.id)}" class="link-offset-2 link-underline-opacity-0">${name}</a></div>
        <div class="text-muted small">${(r.question_count ?? 0)} Qs • ${(r.student_count ?? 0)} students</div>
      </td>
      <td class="text-capitalize">${pub}</td>
      <td>${badgeStatus(r.status||'-')}</td>
      <td>${attempts} attempt(s)</td>
      <td>${setup}</td>
      <td>${created}</td>
      <td class="text-end">${actionMenu(scope, r)}</td>`;
    tr.dataset.key = r.uuid || r.id;
    tr.dataset.status = String(r.status||'').toLowerCase();
    tr.dataset.name = r.quiz_name || '';
    return tr;
  }

  /* ========= Fetch & render ========= */
  async function load(scope){
    const refs = tabs[scope];
    const rowsEl = qs(refs.rows);
    const empty  = qs(refs.empty);
    const pager  = qs(refs.pager);
    const meta   = qs(refs.meta);

    rowsEl.querySelectorAll('tr:not([id^="loaderRow"])').forEach(n=>n.remove());
    empty.style.display='none';
    pager.innerHTML = '';
    meta.textContent = '—';
    showLoader(scope, true);

    try{
      const res = await fetch(urlFor(scope), { headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' }});
      const json = await res.json().catch(()=> ({}));
      if (!res.ok){
        if(scope==='deleted'){
          empty.style.display='';
          meta.textContent='Bin is not supported by the API yet.';
          err('Bin (deleted list) not supported by API');
          return;
        }
        throw new Error(json?.message || 'Load failed');
      }

      const items = json?.data || [];
      const pagination = json?.pagination || json?.meta || {page:1, per_page:20, total:items.length};
      if (scope==='deleted'){ items.forEach(it => it.deleted_at = it.deleted_at || it.updated_at || it.created_at); }

      if (items.length===0) empty.style.display='';

      const frag = document.createDocumentFragment();
      items.forEach(r => frag.appendChild(rowHTML(scope, r)));
      rowsEl.appendChild(frag);

      /* pager */
      const total   = Number(pagination.total||0);
      const perPage = Number(pagination.per_page||20);
      const current = Number(pagination.page||1);
      const totalPages = Math.max(1, Math.ceil(total / perPage));

      function li(disabled, active, label, target){
        const cls=['page-item',disabled?'disabled':'',active?'active':''].filter(Boolean).join(' ');
        const href=disabled?'#':'javascript:void(0)';
        return `<li class="${cls}"><a class="page-link" href="${href}" data-page="${target||''}">${label}</a></li>`;
      }

      let html='';
      html += li(current<=1,false,'Previous',current-1);
      const w=3, start=Math.max(1,current-w), end=Math.min(totalPages,current+w);
      if (start>1){ html += li(false,false,1,1); if(start>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
      for(let p=start;p<=end;p++) html += li(false,p===current,p,p);
      if (end<totalPages){ if(end<totalPages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,totalPages,totalPages); }
      html += li(current>=totalPages,false,'Next',current+1);
      pager.innerHTML = html;
      pager.querySelectorAll('a.page-link[data-page]').forEach(a=>{
        a.addEventListener('click',()=>{
          const target=Number(a.dataset.page); if(!target||target===state[scope].page) return;
          state[scope].page = Math.max(1,target); load(scope);
          window.scrollTo({top:0,behavior:'smooth'});
        });
      });

      meta.textContent = `Showing page ${current} of ${totalPages} — ${total} result(s)`;
      ok('Loaded');
    }catch(e){
      console.error(e);
      empty.style.display='';
      meta.textContent='Failed to load';
      err(e.message || 'Load error');
    }finally{
      showLoader(scope, false);
    }
  }

  /* ========= Sorting (active table) ========= */
  qsa('#tab-quizzes thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      if (sort === col){ sort = '-'+col; }
      else if (sort === '-'+col){ sort = col; }
      else { sort = (col === 'created_at') ? '-created_at' : col; }
      state.active.page = 1;
      load('active');
      qsa('#tab-quizzes thead th.sortable').forEach(t=>t.classList.remove('asc','desc'));
      if (sort === col) th.classList.add('asc'); else if (sort === '-'+col) th.classList.add('desc');
    });
  });

  /* ========= Filters (active tab) ========= */
  let srchT; 
  q?.addEventListener('input', ()=>{ clearTimeout(srchT); srchT=setTimeout(()=>{ state.active.page=1; load('active'); }, 350); });
  
  btnApplyFilters?.addEventListener('click', ()=>{
    // Close the modal properly
    const filterModal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
    filterModal.hide();
    
    // Apply the filters
    state.active.page=1; 
    load('active');
  });
  
  btnReset?.addEventListener('click', ()=>{
    if (q) q.value=''; 
    if (filterIsPublicSel) filterIsPublicSel.value='';
    if (perPageSel) perPageSel.value='20';
    sort='-created_at'; 
    state.active.page=1; 
    load('active');
  });
  
  perPageSel?.addEventListener('change', ()=>{ state.active.page=1; load('active'); });

  /* ========= Tab change => load on demand ========= */
  document.querySelector('a[href="#tab-quizzes"]').addEventListener('shown.bs.tab', ()=> load('active'));
  document.querySelector('a[href="#tab-archived"]').addEventListener('shown.bs.tab', ()=> load('archived'));
  document.querySelector('a[href="#tab-deleted"]').addEventListener('shown.bs.tab', ()=> load('deleted'));

  /* ========= Initial load ========= */
  load('active');

  /* ========= Row action handlers (all tabs) ========= */
  document.addEventListener('click', async (e)=>{
    const it = e.target.closest('.dropdown-item[data-act]');
    if(!it) return;
    const act  = it.dataset.act;
    const key  = it.dataset.key;
    const name = it.dataset.name || 'this quiz';

    if (act==='edit'){
      location.href = `/quizz/create?edit=${encodeURIComponent(key)}`;
      return;
    }
    if (act==='questions'){
      // Redirect to manage questions page with quiz UUID
      location.href = `/quizz/questions/manage?quiz=${encodeURIComponent(key)}`;
      return;
    }
    if (act==='notes'){
      openNotes(key, name);
      return;
    }
    if (act === 'testrun') {
      const url = `/test-exam/${encodeURIComponent(key)}`;
      window.location.href = url;
      return;
    }


    if (act==='randomize'){
      openRandomModal(key, name);
      return;
    }
    if (act==='archive'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Archive quiz?',html:`"${esc(name)}"`,showCancelButton:true,confirmButtonText:'Archive',confirmButtonColor:'#8b5cf6'});
      if(!isConfirmed) return;
      await callStatus(key, 'archived', 'Quiz archived'); load('active');
      return;
    }
    if (act==='unarchive'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Unarchive quiz?',html:`"${esc(name)}"`,showCancelButton:true,confirmButtonText:'Unarchive',confirmButtonColor:'#10b981'});
      if(!isConfirmed) return;
      await callStatus(key, 'active', 'Quiz unarchived'); load('archived');
      return;
    }
    if (act==='delete'){
      const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete (soft)?',html:`This moves "${esc(name)}" to Bin.`,showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#ef4444'});
      if(!isConfirmed) return;
      try{
        const res = await fetch(`/api/quizz/${encodeURIComponent(key)}`, { method:'DELETE', headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'} });
        const j=await res.json().catch(()=>({}));
        if(!res.ok) throw new Error(j?.message||'Delete failed');
        ok('Moved to Bin'); load('active');
      }catch(e){ err(e.message||'Delete failed'); }
      return;
    }
    if (act==='restore'){
      const {isConfirmed}=await Swal.fire({icon:'question',title:'Restore quiz?',html:`"${esc(name)}" will be restored.`,showCancelButton:true,confirmButtonText:'Restore',confirmButtonColor:'#0ea5e9'});
      if(!isConfirmed) return;
      try{
        const res = await fetch(`/api/quizz/${encodeURIComponent(key)}/restore`, { method:'PATCH', headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'} });
        const j=await res.json().catch(()=>({}));
        if(!res.ok) throw new Error(j?.message||'Restore failed');
        ok('Quiz restored'); load('deleted'); load('active');
      }catch(e){ err(e.message||'Restore failed'); }
      return;
    }
    if (act==='force'){
      const {isConfirmed}=await Swal.fire({icon:'warning',title:'Delete permanently?',html:`This cannot be undone.<br>"${esc(name)}"`,showCancelButton:true,confirmButtonText:'Delete permanently',confirmButtonColor:'#dc2626'});
      if(!isConfirmed) return;
      try{
        const res = await fetch(`/api/quizz/${encodeURIComponent(key)}/force`, { method:'DELETE', headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'} });
        const j=await res.json().catch(()=>({}));
        if(!res.ok) throw new Error(j?.message||'Force delete failed');
        ok('Permanently deleted'); load('deleted');
      }catch(e){ err(e.message||'Force delete failed'); }
      return;
    }
  });

  async function callStatus(key, statusVal, doneMsg){
    try{
      const res = await fetch(`/api/quizz/${encodeURIComponent(key)}/status`,{
        method:'PATCH',
        headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ status: statusVal })
      });
      const j=await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message||'Status update failed');
      ok(doneMsg || 'Updated');
    }catch(e){ err(e.message||'Status update failed'); }
  }

    /* ========= Randomization Modal ========= */
  let currentKeyForRandom = null;

  const randomModalEl   = document.getElementById('randomizeModal');
  const randomModal     = randomModalEl ? new bootstrap.Modal(randomModalEl) : null;
  const randomQuizTitle = document.getElementById('randomQuizTitle');
  const toggleQuestionRandom = document.getElementById('toggleQuestionRandom');
  const toggleOptionRandom   = document.getElementById('toggleOptionRandom');
  const btnSaveRandom        = document.getElementById('btnSaveRandom');

  btnSaveRandom?.addEventListener('click', saveRandomSettings);


  async function openRandomModal(key, name){
    currentKeyForRandom = key;

    if (randomQuizTitle) {
      randomQuizTitle.textContent = name || 'Quiz';
    }
    if (toggleQuestionRandom) toggleQuestionRandom.checked = false;
    if (toggleOptionRandom)   toggleOptionRandom.checked   = false;

    // Load current flags from API
    try{
      const res = await fetch(`/api/quizz/${encodeURIComponent(key)}`, {
        headers: {
          'Authorization':'Bearer '+TOKEN,
          'Accept':'application/json'
        }
      });
      const j = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(j?.message || 'Failed to load quiz');

      const q = j.data || j.quiz || j;
      const qFlag = String(q?.is_question_random || 'no').toLowerCase() === 'yes';
      const oFlag = String(q?.is_option_random   || 'no').toLowerCase() === 'yes';

      if (toggleQuestionRandom) toggleQuestionRandom.checked = qFlag;
      if (toggleOptionRandom)   toggleOptionRandom.checked   = oFlag;
    } catch(e){
      console.error(e);
      err(e.message || 'Failed to load randomization');
    }

    randomModal?.show();
  }

  async function saveRandomSettings(){
    if (!currentKeyForRandom) return;

    const isQ = toggleQuestionRandom?.checked ? 'yes' : 'no';
    const isO = toggleOptionRandom?.checked   ? 'yes' : 'no';

    setBtnBusy(btnSaveRandom, true);   // <— turn loader ON

    try{
      const res = await fetch(`/api/quizz/${encodeURIComponent(currentKeyForRandom)}`, {
        method: 'PATCH',
        headers: {
          'Authorization':'Bearer '+TOKEN,
          'Content-Type':'application/json',
          'Accept':'application/json'
        },
        body: JSON.stringify({
          is_question_random: isQ,
          is_option_random:   isO
        })
      });
      const j = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(j?.message || 'Failed to update settings');

      ok('Randomization updated');
      randomModal?.hide();
      load('active');  // refresh table
    }catch(e){
      console.error(e);
      err(e.message || 'Failed to update settings');
    }finally{
      setBtnBusy(btnSaveRandom, false); // <— turn loader OFF
    }
  }



  /* ========= Notes ========= */
  let currentKeyForNotes = null;
  const notesList = document.getElementById('notesList');
  const noteText  = document.getElementById('noteText');
  const notesModal= new bootstrap.Modal(document.getElementById('notesModal'));
  document.getElementById('btnAddNote').addEventListener('click', addNote);

  async function openNotes(key, name){
    currentKeyForNotes = key;
    notesList.innerHTML = `<div class="small text-muted">Loading notes for "${esc(name)}"...</div>`;
    noteText.value='';
    notesModal.show();
    try{
      const res = await fetch(`/api/quizz/${encodeURIComponent(key)}/notes`, { headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'} });
      const j = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message||'Load notes failed');
      const items = Array.isArray(j?.data) ? j.data : [];
      if(items.length===0){ notesList.innerHTML = '<div class="small text-muted">No notes yet.</div>'; return; }
      const html = items.map(n => `
        <div class="border rounded-3 p-2 mb-2">
          <div class="small text-muted">${fmtDate(n.created_at)} — by ${esc(n.created_by_role || 'user')} #${n.created_by ?? '-'}</div>
          <div>${esc(n.note || '')}</div>
        </div>`).join('');
      notesList.innerHTML = html;
      ok('Notes loaded');
    }catch(e){
      notesList.innerHTML = '<div class="text-danger small">Failed to load notes.</div>';
      err(e.message||'Failed to load notes');
    }
  }

  async function addNote(){
    const text = (noteText.value||'').trim();
    if(!text){ return Swal.fire('Note required','Please type something.','info'); }
    try{
      const res = await fetch(`/api/quizz/${encodeURIComponent(currentKeyForNotes)}/notes`,{
        method:'POST',
        headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ note: text })
      });
      const j=await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message||'Add note failed');
      noteText.value='';
      ok('Note added'); openNotes(currentKeyForNotes, 'Quiz');
    }catch(e){ err(e.message||'Add note failed'); }
  }
})();
</script>
@endpush