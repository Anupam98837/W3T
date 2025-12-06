{{-- resources/views/landing/manageUpdates.blade.php --}}
@extends('pages.users.admin.layout.structure')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
.sm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}

/* Cards */
.card.lp-card{
  border-radius:16px;
  border:1px solid var(--line-strong,#e5e7eb);
  box-shadow:0 10px 30px rgba(15,23,42,.08)
}
.card.lp-card .card-header{
  border-bottom:1px solid var(--line-strong,#e5e7eb);
  background:var(--surface,#fff);
  border-radius:16px 16px 0 0;
  padding: 1rem 1.5rem;
}

/* Panel layout (shared with other admin pages) */
.panel{
  background:var(--surface,#fff);
  border:1px solid var(--line-strong,#e5e7eb);
  border-radius:16px;
  box-shadow:0 10px 30px rgba(15,23,42,.08);
  padding:14px 18px;
}

/* RTE */
.rte-wrap{
  border-radius:12px;
  border:1px solid var(--line-strong,#e5e7eb);
  background:#fff;
  overflow:hidden;
}
.rte-toolbar{
  display:flex;
  gap:4px;
  padding:4px 6px;
  border-bottom:1px solid var(--line-strong,#e5e7eb);
  background:#f8fafc;
}
.rte-toolbar button{
  border:none;
  background:transparent;
  width:28px;
  height:28px;
  border-radius:8px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:12px;
  cursor: pointer;
}
.rte-toolbar button:hover{
  background:#e5e7eb;
}
.rte{
  min-height:42px;
  max-height:160px;
  padding:12px;
  overflow-y:auto;
  outline:none;
  font-size:14px;
}
.rte.empty{
  color:#9ca3af;
}

/* Scrollable modal */
.modal-dialog.modal-dialog-scrollable {
  max-height: calc(100vh - 2rem);
}
.modal-dialog.modal-dialog-scrollable .modal-content {
  max-height: 100%;
  display: flex;
  flex-direction: column;
}
.modal-dialog.modal-dialog-scrollable .modal-body {
  overflow-y: auto;
  max-height: calc(100vh - 8rem);
  padding: 1.5rem;
}

/* Table improvements */
.table {
  margin-bottom: 0;
}
.table thead th {
  background-color: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  padding: 0.75rem 1rem;
  font-weight: 600;
  color: #374151;
  white-space: nowrap;
}
.table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb;
}

/* Dropdowns in table */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.table-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong,#e5e7eb);
  box-shadow:0 12px 35px rgba(15,23,42,.16);
  min-width:180px;
  z-index:5000;
}
.dropdown-item{
  display:flex;
  align-items:center;
  gap:.6rem;
}
.dropdown-item i{
  width:16px;
  text-align:center;
}
.dropdown-item.text-danger{
  color:var(--danger-color,#dc2626)!important;
}
.dd-toggle{
  padding:0;
  width:34px;
  height:34px;
  border-radius:10px;
}

/* Loading state */
.loading-placeholder {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}

/* Pager */
#lpUpdatesPager .page-link{
  border-radius:999px;
}

/* Reorder mode */
#lpUpdatesAdmin.reorder-mode #lpUpdatesTableBody tr[data-id]{
  cursor: move;
}

/* Ensure dropdown works properly */
.dropdown-menu {
  min-width: 120px;
}
</style>
@endpush

@section('content')
@php
  $apiBase = url('api/landing/updates');
@endphp

<div class="sm-wrap py-4"
     id="lpUpdatesAdmin"
     data-api="{{ $apiBase }}"
     data-csrf="{{ csrf_token() }}">

  {{-- ===== HEADER PANEL ===== --}}
  <div class="panel mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
      <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
        <i class="fa fa-bullhorn text-primary fa-lg"></i>
      </div>
      <div>
        <div class="fw-semibold fs-5">Landing Page Updates</div>
        <div class="small text-muted">Manage the scrolling announcement strip on your landing page.</div>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button id="btnReorder" class="btn btn-outline-primary">
        <i class="fa fa-arrows-up-down-left-right me-1"></i>Reorder
      </button>
      <button id="btnCreate" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i> New Update
      </button>
    </div>
  </div>

  {{-- ===== FILTERS PANEL (per page / search / reset) ===== --}}
  <div class="panel mb-3 row align-items-center g-2">
    <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Per page</label>
        <select id="per_page" class="form-select" style="width:96px;">
          <option>10</option>
          <option selected>20</option>
          <option>30</option>
          <option>50</option>
          <option>100</option>
        </select>
      </div>

      <div class="position-relative" style="min-width:260px;">
        <input id="searchBox" type="text" class="form-control ps-5"
               placeholder="Search title / description…">
        <i class="fa fa-search position-absolute"
           style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      </div>

      <button id="btnReset" class="btn btn-light border">
        <i class="fa fa-rotate-left me-1"></i>Reset
      </button>
    </div>

    <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end mt-2 mt-xl-0">
      <span class="badge bg-light text-dark" id="updatesCount">0 updates</span>
    </div>
  </div>

  {{-- ===== TABLE CARD (same style as categories/heroes) ===== --}}
  <div class="card table-wrap lp-card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
            <tr>
              <th style="width:70px;">ID</th>
              <th>Title</th>
              <th style="width:110px;">Order</th>
              <th style="width:160px;">Created At</th>
              <th class="text-end" style="width:140px;">Actions</th>
            </tr>
          </thead>
          <tbody id="lpUpdatesTableBody">
            <tr>
              <td colspan="5" class="text-center py-5 loading-placeholder">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Loading updates…
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2 border-top">
        <div id="paginationInfo" class="small text-muted">—</div>
        <nav><ul id="lpUpdatesPager" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>
</div>

{{-- ================== Add/Edit Modal ================== --}}
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form id="updateForm" class="modal-content">
      @csrf
      <input type="hidden" id="upd_id" name="id">

      <div class="modal-header">
        <h5 id="updateModalTitle" class="modal-title">
          <i class="fa fa-bullhorn me-2"></i>Create Update
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-4">
          <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
          <div class="form-text small mb-2">Text shown in the announcement strip</div>
          <div class="rte-wrap">
            <div class="rte-toolbar">
              <button type="button" class="rte-tool" data-field="title" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-tool" data-field="title" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-tool" data-field="title" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <button type="button" class="rte-tool" data-field="title" data-cmd="createLink" title="Insert link"><i class="fa fa-link"></i></button>
              <button type="button" class="rte-tool" data-field="title" data-cmd="unlink" title="Remove link"><i class="fa fa-unlink"></i></button>
            </div>
            <div id="rteTitle" class="rte" contenteditable="true" data-placeholder="Write title here..."></div>
          </div>
          <div id="titleError" class="invalid-feedback d-none">Title is required</div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Description</label>
          <div class="form-text small mb-2">Additional details (optional)</div>
          <div class="rte-wrap">
            <div class="rte-toolbar">
              <button type="button" class="rte-tool" data-field="description" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-tool" data-field="description" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-tool" data-field="description" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <button type="button" class="rte-tool" data-field="description" data-cmd="createLink" title="Insert link"><i class="fa fa-link"></i></button>
              <button type="button" class="rte-tool" data-field="description" data-cmd="unlink" title="Remove link"><i class="fa fa-unlink"></i></button>
            </div>
            <div id="rteDescription" class="rte" contenteditable="true" data-placeholder="Write description here... (optional)"></div>
          </div>
        </div>

        <div class="mb-3">
          <label for="upd_display_order" class="form-label fw-semibold">Display Order</label>
          <input type="number" id="upd_display_order" name="display_order" class="form-control" value="0" min="0" required>
          <div class="form-text mt-1">Lower numbers appear earlier in the strip.</div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="updateSaveBtn">
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          <span class="btn-text">Save Update</span>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
  <div id="okToast" class="toast text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <i class="fa fa-check-circle me-2"></i>
      <strong class="me-auto">Success</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div id="okMsg" class="toast-body">Operation completed successfully.</div>
  </div>
  
  <div id="errToast" class="toast text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <i class="fa fa-exclamation-circle me-2"></i>
      <strong class="me-auto">Error</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div id="errMsg" class="toast-body">Something went wrong.</div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const adminRoot = document.getElementById('lpUpdatesAdmin');
  if (!adminRoot) return;

  const API_BASE  = adminRoot.dataset.api;
  const CSRF      = adminRoot.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const tbody      = document.getElementById('lpUpdatesTableBody');
  const updatesCount = document.getElementById('updatesCount');
  const pager      = document.getElementById('lpUpdatesPager');
  const paginationLbl = document.getElementById('paginationInfo');

  const perPageSel  = document.getElementById('per_page');
  const searchInput = document.getElementById('searchBox');
  const btnReset    = document.getElementById('btnReset');
  const btnCreate   = document.getElementById('btnCreate');
  const btnReorder  = document.getElementById('btnReorder');

  const updateModalEl    = document.getElementById('updateModal');
  const updateModal      = bootstrap.Modal ? new bootstrap.Modal(updateModalEl) : null;
  const updateModalTitle = document.getElementById('updateModalTitle');
  const updateForm       = document.getElementById('updateForm');
  const updateSaveBtn    = document.getElementById('updateSaveBtn');

  const updIdInput       = document.getElementById('upd_id');
  const updOrderInput    = document.getElementById('upd_display_order');

  const rteTitle         = document.getElementById('rteTitle');
  const rteDescription   = document.getElementById('rteDescription');
  const titleError       = document.getElementById('titleError');

  // Toast helpers
  const okToastEl  = document.getElementById('okToast');
  const errToastEl = document.getElementById('errToast');
  const okToast    = okToastEl  && bootstrap.Toast ? new bootstrap.Toast(okToastEl)  : null;
  const errToast   = errToastEl && bootstrap.Toast ? new bootstrap.Toast(errToastEl) : null;

  const showToast = (type, message, title = null) => {
    if (type === 'success') {
      document.getElementById('okMsg').textContent = message;
      if (title) okToastEl.querySelector('.toast-header strong').textContent = title;
      okToast?.show();
    } else {
      document.getElementById('errMsg').textContent = message;
      if (title) errToastEl.querySelector('.toast-header strong').textContent = title;
      errToast?.show();
    }
  };

  // Pagination / state
  let page        = 1;
  let perPage     = Number(perPageSel.value || 20);
  let total       = 0;
  let query       = "";
  let currentRows = [];

  // Reorder state
  let reorderMode      = false;
  let sortableInstance = null;

  /* =========== RTE helpers =========== */
  function initRTE(element) {
    if (!element) return;
    const placeholder = element.dataset.placeholder || '';

    if (!element.textContent.trim()) {
      element.classList.add('empty');
      element.innerHTML = `<span class="text-muted">${placeholder}</span>`;
    }

    element.addEventListener('focus', function() {
      if (this.classList.contains('empty')) {
        this.classList.remove('empty');
        this.innerHTML = '';
      }
    });

    element.addEventListener('blur', function() {
      if (!this.textContent.trim()) {
        this.classList.add('empty');
        this.innerHTML = `<span class="text-muted">${placeholder}</span>`;
      }
    });

    element.addEventListener('input', function() {
      if (this === rteTitle) validateTitle();
    });

    element.addEventListener('paste', function(e) {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, text);
    });
  }
  /* ========== 3-dot dropdown toggle (same pattern as other pages) ========== */
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".dd-toggle");
    if (!btn) return;

    // Stop the click from bubbling and triggering table handlers
    e.preventDefault();
    e.stopPropagation();

    bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: "outside",
      boundary: "viewport",
    }).toggle();
  });

  function getRTEValue(element) {
    if (!element || element.classList.contains('empty')) return '';
    return element.innerHTML.trim();
  }

  function setRTEValue(element, value) {
    if (!element) return;
    const placeholder = element.dataset.placeholder || '';
    if (value && value.trim()) {
      element.classList.remove('empty');
      element.innerHTML = value;
    } else {
      element.classList.add('empty');
      element.innerHTML = `<span class="text-muted">${placeholder}</span>`;
    }
  }

  function validateTitle() {
    const title = getRTEValue(rteTitle);
    const isValid = title.trim().length > 0;

    if (!isValid) {
      titleError.classList.remove('d-none');
      rteTitle.closest('.rte-wrap').classList.add('border-danger');
    } else {
      titleError.classList.add('d-none');
      rteTitle.closest('.rte-wrap').classList.remove('border-danger');
    }
    return isValid;
  }

  initRTE(rteTitle);
  initRTE(rteDescription);

  // RTE toolbar handler
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.rte-tool');
    if (!btn) return;

    const field = btn.dataset.field;
    const cmd   = btn.dataset.cmd;
    let targetEl = field === 'title' ? rteTitle : rteDescription;
    if (!targetEl) return;

    if (targetEl.classList.contains('empty')) {
      targetEl.classList.remove('empty');
      targetEl.innerHTML = '';
    }
    targetEl.focus();

    if (cmd === 'createLink') {
      const url = prompt('Enter URL (include https://):', 'https://');
      if (url) document.execCommand(cmd, false, url);
    } else {
      document.execCommand(cmd, false, null);
    }

    if (field === 'title') validateTitle();
  });

  /* =========== Reorder helpers =========== */
  function enableReorderMode() {
    if (reorderMode) return;
    reorderMode = true;

    adminRoot.classList.add('reorder-mode');

    if (btnReorder) {
      btnReorder.classList.remove('btn-outline-primary');
      btnReorder.classList.add('btn-success');
      btnReorder.innerHTML = '<i class="fa fa-floppy-disk me-1"></i>Save order';
    }

    if (!paginationLbl.dataset.defaultText) {
      paginationLbl.dataset.defaultText = paginationLbl.textContent || '';
    }
    paginationLbl.textContent = 'Reorder mode: drag rows, then click "Save order".';

    sortableInstance = Sortable.create(tbody, {
      animation: 150,
      filter: 'tr:not([data-id])' // ignore placeholder rows
    });
  }

  function disableReorderMode() {
    if (!reorderMode) return;
    reorderMode = false;

    adminRoot.classList.remove('reorder-mode');

    if (btnReorder) {
      btnReorder.classList.remove('btn-success');
      btnReorder.classList.add('btn-outline-primary');
      btnReorder.innerHTML = '<i class="fa fa-arrows-up-down-left-right me-1"></i>Reorder';
    }

    if (sortableInstance) {
      sortableInstance.destroy();
      sortableInstance = null;
    }

    if (paginationLbl.dataset.defaultText !== undefined) {
      paginationLbl.textContent = paginationLbl.dataset.defaultText;
      delete paginationLbl.dataset.defaultText;
    }
  }

  /* =========== Modal helpers =========== */
  function resetModal() {
    updIdInput.value = '';
    updOrderInput.value = '0';
    setRTEValue(rteTitle, '');
    setRTEValue(rteDescription, '');
    titleError.classList.add('d-none');
    rteTitle.closest('.rte-wrap').classList.remove('border-danger');
    updateModalTitle.innerHTML = '<i class="fa fa-plus me-2"></i>Create Update';
  }

  function openCreateModal() {
    resetModal();
    updateModal?.show();
  }

  function openEditModal(item) {
    updIdInput.value = item.id;
    updOrderInput.value = item.display_order || 0;
    setRTEValue(rteTitle, item.title || '');
    setRTEValue(rteDescription, item.description || '');
    updateModalTitle.innerHTML = '<i class="fa fa-edit me-2"></i>Edit Update';
    updateModal?.show();
  }

  if (btnCreate) {
    btnCreate.addEventListener('click', () => {
      disableReorderMode();
      openCreateModal();
    });
  }

  if (updateModalEl) {
    updateModalEl.addEventListener('hidden.bs.modal', function() {
      resetModal();
    });
  }

  /* =========== Load updates (with search / per_page) =========== */
  async function loadUpdates() {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-5 loading-placeholder">
          <div class="spinner-border spinner-border-sm text-primary me-2"></div>
          Loading updates…
        </td>
      </tr>
    `;

    try {
      perPage = Number(perPageSel.value || 20);
      const url = `${API_BASE}?page=${page}&per_page=${perPage}&q=${encodeURIComponent(query)}`;

      const response = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      let updates = [];
      let paginationTotal = null;

      if (Array.isArray(data)) {
        updates = data;
      } else if (data && Array.isArray(data.data)) {
        updates = data.data;
        paginationTotal = data.pagination?.total ?? data.total ?? null;
      } else if (data && Array.isArray(data.updates)) {
        updates = data.updates;
        paginationTotal = data.total ?? null;
      }

      currentRows = updates;
      total = paginationTotal ?? updates.length;

      renderUpdatesTable(updates);
      buildPager();
    } catch (error) {
      console.error('Error loading updates:', error);
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center py-5 text-danger">
            <i class="fa fa-exclamation-triangle me-2"></i>
            Failed to load updates. Please try again.
          </td>
        </tr>
      `;
      updatesCount.textContent = '0 updates';
      paginationLbl.textContent = 'Failed to load';
      pager.innerHTML = '';
      showToast('error', 'Failed to load updates');
    }
  }

  function renderUpdatesTable(updates) {
    if (!updates || !updates.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center py-5">
            <div class="text-muted mb-2">
              <i class="fa fa-inbox fa-2x"></i>
            </div>
            <div class="fw-semibold">No updates found</div>
            <div class="small text-muted mt-1">Click "New Update" to create your first announcement</div>
          </td>
        </tr>
      `;
      updatesCount.textContent = '0 updates';
      paginationLbl.textContent = 'No results';
      return;
    }

    updatesCount.textContent = `${updates.length} item${updates.length !== 1 ? 's' : ''}`;

    const rows = updates.map(item => {
      const createdAt = item.created_at ?
        new Date(item.created_at).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        }) : 'N/A';

      // strip HTML for snippet
      let descText = '';
      if (item.description) {
        descText = item.description.replace(/<[^>]*>/g,'');
        if (descText.length > 60) descText = descText.substring(0, 60) + '…';
      }

      return `
        <tr data-id="${item.id}">
          <td class="text-muted">#${item.id}</td>
          <td>
            <div class="d-flex align-items-start gap-2">
              <i class="fa fa-bullhorn text-primary mt-1"></i>
              <div>
                <div class="fw-semibold">${item.title || '<span class="text-muted">(No title)</span>'}</div>
                ${descText ? `<div class="small text-muted mt-1">${descText}</div>` : ''}
              </div>
            </div>
          </td>
          <td>
            <span class="badge bg-light text-dark">${item.display_order ?? 0}</span>
          </td>
          <td class="text-muted">${createdAt}</td>
          <td class="text-end">
            <div class="dropdown" data-bs-display="static">
              <button type="button"
                      class="btn btn-primary btn-sm dd-toggle border-0"
                      data-bs-toggle="dropdown"
                      aria-expanded="false">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <button class="dropdown-item upd-edit-btn"
                          data-item='${JSON.stringify(item).replace(/'/g,"&#39;")}'>
                    <i class="fa fa-pen-to-square"></i> Edit
                  </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <button class="dropdown-item text-danger upd-del-btn"
                          data-id="${item.id}">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </li>
              </ul>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    tbody.innerHTML = rows;

    const pages = Math.max(1, Math.ceil(total / perPage));
    paginationLbl.textContent = `Page ${page} of ${pages} — ${total} item(s)`;
  }

  function buildPager() {
    const pages = Math.max(1, Math.ceil(total / perPage));
    const li = (dis, act, label, target) => `
      <li class="page-item ${dis ? "disabled" : ""} ${act ? "active" : ""}">
        <a class="page-link" href="javascript:void(0)" data-page="${target ?? ""}">
          ${label}
        </a>
      </li>
    `;

    let html = "";
    html += li(page <= 1, false, "Previous", page - 1);

    const windowSize = 3;
    const start = Math.max(1, page - windowSize);
    const end   = Math.min(pages, page + windowSize);

    if (start > 1) {
      html += li(false, false, 1, 1);
      if (start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }

    for (let i = start; i <= end; i++) {
      html += li(false, i === page, i, i);
    }

    if (end < pages) {
      if (end < pages - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
      html += li(false, false, pages, pages);
    }

    html += li(page >= pages, false, "Next", page + 1);
    pager.innerHTML = html;

    pager.querySelectorAll("a.page-link[data-page]").forEach(a => {
      a.addEventListener("click", () => {
        const target = Number(a.dataset.page);
        if (!target || target === page) return;
        page = Math.max(1, target);
        disableReorderMode();
        loadUpdates();
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    });
  }

  /* =========== Form submission =========== */
  if (updateForm) {
    updateForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      if (!validateTitle()) {
        rteTitle.focus();
        return;
      }

      const id = updIdInput.value;
      const title = getRTEValue(rteTitle);
      const description = getRTEValue(rteDescription);
      const displayOrder = parseInt(updOrderInput.value) || 0;

      const submitBtn = updateSaveBtn;
      const btnText = submitBtn.querySelector('.btn-text');
      const spinner = submitBtn.querySelector('.spinner-border');

      submitBtn.disabled = true;
      btnText.textContent = id ? 'Saving...' : 'Creating...';
      spinner.classList.remove('d-none');

      try {
        const url = id ? `${API_BASE}/${id}` : API_BASE;
        const method = id ? 'PUT' : 'POST';

        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            title: title,
            description: description || null,
            display_order: displayOrder
          })
        });

        const data = await response.json().catch(()=> ({}));

        if (!response.ok) {
          throw new Error(data.message || `HTTP ${response.status}`);
        }

        updateModal?.hide();
        showToast('success', id ? 'Update saved successfully' : 'Update created successfully');
        disableReorderMode();
        loadUpdates();
      } catch (error) {
        console.error('Error saving update:', error);
        showToast('error', error.message || 'Failed to save update');
      } finally {
        submitBtn.disabled = false;
        btnText.textContent = 'Save Update';
        spinner.classList.add('d-none');
      }
    });
  }

  /* =========== Event delegation for dropdown actions =========== */
  if (tbody) {
  tbody.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.upd-edit-btn');
    const delBtn  = e.target.closest('.upd-del-btn');

    if (editBtn) {
      const itemStr = editBtn.dataset.item || '{}';
      try {
        const item = JSON.parse(itemStr.replace(/&#39;/g, "'"));
        openEditModal(item);
      } catch (error) {
        console.error('Error parsing item:', error);
        showToast('error', 'Could not load update for editing');
      }
    }

    if (delBtn) {
      const id = delBtn.dataset.id;
      if (id) confirmDelete(id);
    }

    // ✅ Only close dropdown after clicking a menu item, not the 3-dot button
    const ddItem = e.target.closest('.dropdown-item');
    if (ddItem) {
      const parentDD = ddItem.closest('.dropdown');
      if (parentDD) {
        const toggleEl = parentDD.querySelector('[data-bs-toggle="dropdown"]');
        if (toggleEl) {
          const inst = bootstrap.Dropdown.getOrCreateInstance(toggleEl);
          inst.hide();
        }
      }
    }
  });
}

  async function confirmDelete(id) {
    const result = await Swal.fire({
      title: 'Delete this update?',
      text: "This update will be permanently deleted.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it',
      cancelButtonText: 'Cancel',
      reverseButtons: true
    });

    if (!result.isConfirmed) return;

    try {
      const response = await fetch(`${API_BASE}/${id}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await response.json().catch(()=> ({}));
      if (!response.ok) {
        throw new Error(data.message || `HTTP ${response.status}`);
      }

      showToast('success', 'Update deleted successfully');
      disableReorderMode();
      loadUpdates();
    } catch (error) {
      console.error('Error deleting update:', error);
      showToast('error', 'Failed to delete update');
    }
  }

  /* =========== Filters & per page =========== */
  let searchTimer = null;
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        query = searchInput.value.trim();
        page  = 1;
        disableReorderMode();
        loadUpdates();
      }, 300);
    });
  }

  if (btnReset) {
    btnReset.addEventListener('click', () => {
      searchInput.value = "";
      query = "";
      perPageSel.value = "20";
      page = 1;
      disableReorderMode();
      loadUpdates();
    });
  }

  if (perPageSel) {
    perPageSel.addEventListener('change', () => {
      page = 1;
      disableReorderMode();
      loadUpdates();
    });
  }

  /* =========== Reorder button handler =========== */
  if (btnReorder) {
    btnReorder.addEventListener('click', async () => {
      if (!reorderMode) {
        enableReorderMode();
        return;
      }

      const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
      if (!rows.length) {
        showToast('error', 'Nothing to reorder');
        disableReorderMode();
        return;
      }

      const ids = rows.map(tr => tr.dataset.id).filter(Boolean);
      if (!ids.length) {
        showToast('error', 'No rows to reorder');
        return;
      }

      try {
        btnReorder.disabled = true;

        const res = await fetch(`${API_BASE}/reorder`, {
          method: 'POST', // change to 'POST' if your route uses POST
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
          },
          body: JSON.stringify({ ids }),
        });
        const json = await res.json().catch(() => ({}));

        if (!res.ok) {
          throw new Error(json.message || 'Failed to reorder updates');
        }

        showToast('success', 'Order updated');
        disableReorderMode();
        loadUpdates();
      } catch (ex) {
        console.error(ex);
        showToast('error', ex.message || 'Error saving order');
      } finally {
        btnReorder.disabled = false;
      }
    });
  }

  // Initial load
  loadUpdates();
});
</script>
@endpush
