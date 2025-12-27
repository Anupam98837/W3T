
@push('styles')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* Shell */
.cm-wrap{
  max-width:1140px;
  margin:16px auto 40px;
  overflow:visible;
}
.small{font-size:12.5px;}

/* Modal */
.modal-dialog.modal-dialog-scrollable{
  max-height:calc(100vh - 2rem);
}
.modal-dialog.modal-dialog-scrollable .modal-content{
  height:100%;
  display:flex;
  flex-direction:column;
}
.modal-dialog.modal-dialog-scrollable .modal-body{
  flex:1 1 auto;
  overflow-y:auto;
  padding-bottom:2rem;
}

/* Toasts */
.toast-container{z-index:2000;}

/* RTE */
.rte-wrap{
  border-radius:12px;
  border:1px solid #e5e7eb;
  background:#fff;
  overflow:hidden;
}
.rte-toolbar{
  display:flex;
  gap:4px;
  padding:4px 6px;
  border-bottom:1px solid #e5e7eb;
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
}
.rte-toolbar button:hover{
  background:#e5e7eb;
}
.rte{
  min-height:42px;
  max-height:160px;
  padding:6px 8px;
  overflow-y:auto;
  outline:none;
  font-size:14px;
}
.rte.empty{
  color:#9ca3af;
}

/* Dark tweaks (optional, matching privileges page) */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{
  background:#0f172a;
  border-color:#1f2937;
}
html.theme-dark .table thead th{
  background:#0f172a;
  border-color:#1f2937;
  color:#94a3b8;
}
html.theme-dark .table tbody tr{
  border-color:#1f2937;
}
html.theme-dark .form-control,
html.theme-dark .form-select,
html.theme-dark textarea{
  background:#0f172a;
  color:#e5e7eb;
  border-color:#1f2937;
}
.dropdown-menu{
  border-radius:12px;
  border:1px solid #e5e7eb;
  box-shadow:0 10px 30px rgba(15,23,42,.08);
  min-width:180px;
}

.dropdown-item i{
  width:16px;
  text-align:center;
}

.dd-toggle:hover{
  background:#f1f5f9!important;
}
#categoriesPage.reorder-mode #catTbody tr { cursor: move; }
/* RTE active tool state (DON'T use .active) */
.rte-toolbar .rte-tool.is-active{
  background: var(--primary-color, #2563eb);
  color:#fff;
}
.rte-toolbar .rte-tool.is-active i{ color:#fff !important; }

/* optional: focused editor ring */
.rte-wrap.is-focused{
  border-color: var(--primary-color, #2563eb);
  box-shadow: 0 0 0 3px rgba(37,99,235,.18);
}

</style>
@endpush


@section('content')
@php
  $apiBase = url('api/landing/categories');
@endphp

<div class="cm-wrap"
     id="categoriesPage"
     data-api="{{ $apiBase }}"
     data-csrf="{{ csrf_token() }}">

  {{-- ===== PANEL 1: HEADER TEXT ONLY ===== --}}
  <div class="panel mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <i class="fa fa-tags text-primary"></i>
      <div>
        <div class="fw-semibold">Landing Page – Categories</div>
        <div class="small text-muted">Manage categories for your landing page.</div>
      </div>
    </div>
  </div>

  {{-- ===== PANEL 2: PER PAGE / SEARCH / RESET / NEW ===== --}}
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

      <div class="position-relative" style="min-width:280px;">
        <input id="searchBox" type="text" class="form-control ps-5"
               placeholder="Search title / description / icon…">
        <i class="fa fa-search position-absolute"
           style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      </div>

      <button id="btnReset" class="btn btn-light border">
        <i class="fa fa-rotate-left me-1"></i>Reset
      </button>
      
<button id="btnReorder" class="btn btn-outline-primary">
  <i class="fa fa-arrows-up-down-left-right me-1"></i>Reorder
</button>
    </div>

    <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end mt-2 mt-xl-0">
      <button id="btnAdd" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>New Category
      </button>
    </div>
  </div>

  {{-- ===== TABLE CARD ===== --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
            <tr>
              <th style="width:60px;">#</th>
              <th>Title</th>
              <th style="width:120px;">Icon</th>
              <th>Description</th>
              <th style="width:140px;">Created</th>
              <th class="text-end" style="width:140px;">Actions</th>
            </tr>
          </thead>
          <tbody id="catTbody">
            <tr>
              <td colspan="6" class="py-3 text-center text-muted">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="emptyRow" class="p-4 text-center text-muted" style="display:none;">
        <i class="fa fa-tags mb-2" style="font-size:32px;opacity:.6;"></i>
        <div>No categories found.</div>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2 border-top">
        <div id="paginationInfo" class="small text-muted">—</div>
        <nav><ul id="pager" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>
</div>


{{-- ===================== MODAL ADD / EDIT ===================== --}}
<div class="modal fade" id="catModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <form class="modal-content" id="catForm">
      @csrf
      <input type="hidden" id="cat_id">

      <div class="modal-header">
        <h5 id="catModalTitle" class="modal-title">
          <i class="fa fa-tag me-2"></i>Create Category
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <label class="form-label">Title <span class="text-danger">*</span></label>
<input id="cat_title" class="form-control" placeholder="Category title">
        <label class="form-label mt-3">Icon (optional)</label>
        <input id="cat_icon" class="form-control" placeholder="fa-solid fa-heart">

        <label class="form-label mt-3">Description (optional)</label>
        <div class="rte-wrap">
          <div class="rte-toolbar">
            <button type="button" class="rte-tool" data-field="description" data-cmd="bold"      title="Bold"><i class="fa fa-bold"></i></button>
            <button type="button" class="rte-tool" data-field="description" data-cmd="italic"    title="Italic"><i class="fa fa-italic"></i></button>
            <button type="button" class="rte-tool" data-field="description" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
            <button type="button" class="rte-tool" data-field="description" data-cmd="createLink" title="Insert link"><i class="fa fa-link"></i></button>
          </div>
          <div id="rteDesc" class="rte empty" contenteditable="true" data-placeholder="Write description… (optional)"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="btnSave" type="submit" class="btn btn-primary">
          <i class="fa fa-save me-1"></i>Save
        </button>
      </div>
    </form>
  </div>
</div>


{{-- ===================== TOASTS ===================== --}}
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="okToast" class="toast text-bg-success border-0">
    <div class="d-flex">
      <div id="okMsg" class="toast-body">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>

  <div id="errToast" class="toast text-bg-danger border-0 mt-2">
    <div class="d-flex">
      <div id="errMsg" class="toast-body">Error</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {

  const root   = document.getElementById("categoriesPage");
  if (!root) return;

  const API    = root.dataset.api;
  const CSRF   = root.dataset.csrf;

  const tbody         = document.getElementById("catTbody");
  const emptyRow      = document.getElementById("emptyRow");
  const pager         = document.getElementById("pager");
  const paginationLbl = document.getElementById("paginationInfo");

  const perPageSel    = document.getElementById("per_page");
  const searchInput   = document.getElementById("searchBox");
  const btnReset      = document.getElementById("btnReset");
  const btnAdd        = document.getElementById("btnAdd");
  const btnReorder    = document.getElementById("btnReorder");

  const modalEl       = document.getElementById("catModal");
  const modal         = new bootstrap.Modal(modalEl);
  const modalTitle    = document.getElementById("catModalTitle");
  const form          = document.getElementById("catForm");

  const idInput       = document.getElementById("cat_id");
  const iconInput     = document.getElementById("cat_icon");
  const titleInput    = document.getElementById("cat_title"); // now a plain text input
  const btnSave       = document.getElementById("btnSave");   // Save button

  const rteDesc       = document.getElementById("rteDesc");   // description remains RTE

  // Toast helpers
  const okToast  = new bootstrap.Toast(document.getElementById("okToast"));
  const errToast = new bootstrap.Toast(document.getElementById("errToast"));
  const ok  = msg => { document.getElementById("okMsg").textContent  = msg || "Done"; okToast.show(); };
  const err = msg => { document.getElementById("errMsg").textContent = msg || "Something went wrong"; errToast.show(); };

  // Pagination / state
  let page        = 1;
  let perPage     = Number(perPageSel.value || 20);
  let total       = 0;
  let query       = "";
  let currentRows = [];

  // Reorder state
  let reorderMode       = false;
  let sortableInstance  = null;

  /* ====== Reorder helpers ====== */
  function enableReorderMode() {
    if (reorderMode) return;
    reorderMode = true;

    root.classList.add("reorder-mode");

    if (btnReorder) {
      btnReorder.classList.remove("btn-outline-secondary");
      // btnReorder.classList.add("btn-success");
      btnReorder.innerHTML = '<i class="fa fa-floppy-disk me-1"></i>Save order';
    }

    if (!paginationLbl.dataset.defaultText) {
      paginationLbl.dataset.defaultText = paginationLbl.textContent || "";
    }
    paginationLbl.textContent = 'Reorder mode: drag rows, then click "Save order".';

    sortableInstance = Sortable.create(tbody, {
      animation: 150,
    });
  }

  function disableReorderMode() {
    if (!reorderMode) return;
    reorderMode = false;

    root.classList.remove("reorder-mode");

    if (btnReorder) {
      btnReorder.classList.remove("btn-success");
      btnReorder.classList.add("btn-outline-secondary");
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

  /* ====== RTE helpers (description only) ====== */
  function syncRtePlaceholder(el) {
    if (!el) return;
    if (el.innerHTML.trim() === "") {
      el.classList.add("empty");
      el.innerHTML = el.dataset.placeholder || "";
    }
  }

  function clearRtePlaceholderOnFocus(el) {
    if (!el) return;
    if (el.classList.contains("empty")) {
      el.classList.remove("empty");
      el.innerHTML = "";
    }
  }

  function normalizeRte(el) {
    if (!el) return "";
    if (el.classList.contains("empty")) return "";
    return el.innerHTML.trim();
  }

  // bind only description RTE
  if (rteDesc) {
    syncRtePlaceholder(rteDesc);
    rteDesc.addEventListener("focus", () => clearRtePlaceholderOnFocus(rteDesc));
    rteDesc.addEventListener("blur", () => {
      if (rteDesc.innerHTML.trim() === "") syncRtePlaceholder(rteDesc);
    });
  }
(function () {
  if (window.__CAT_RTE_ACTIVE_SYNC__) return;
  window.__CAT_RTE_ACTIVE_SYNC__ = true;

  function selectionInside(editor) {
    const sel = document.getSelection();
    if (!sel || !sel.anchorNode) return false;
    return sel.anchorNode === editor || editor.contains(sel.anchorNode);
  }

  function anchorActive(editor) {
    const sel = document.getSelection();
    if (!sel || !sel.anchorNode) return false;
    let n = sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement;
    if (!n) return false;
    // inside <a> ?
    return !!(n.closest && n.closest("a"));
  }

  function bindOne(toolbar) {
    if (toolbar.__rteBound) return;

    const wrap = toolbar.closest(".rte-wrap");
    const editor = wrap ? wrap.querySelector(".rte") : null;
    if (!editor) return;

    toolbar.__rteBound = true;
    const tools = Array.from(toolbar.querySelectorAll(".rte-tool"));

    function clearAll() {
      tools.forEach(btn => {
        btn.classList.remove("is-active");
        btn.setAttribute("aria-pressed", "false");
      });
      if (wrap) wrap.classList.remove("is-focused");
    }

    function update() {
      const inside = selectionInside(editor) || document.activeElement === editor;

      if (wrap) wrap.classList.toggle("is-focused", document.activeElement === editor);

      if (!inside) {
        clearAll();
        return;
      }

      tools.forEach(btn => {
        const cmd = btn.dataset.cmd;
        let on = false;

        try {
          if (cmd === "createLink") {
            on = anchorActive(editor);
          } else if (cmd) {
            on = !!document.queryCommandState(cmd);
          }
        } catch {
          on = false;
        }

        btn.classList.toggle("is-active", on);
        btn.setAttribute("aria-pressed", on ? "true" : "false");
      });
    }

    // after toolbar actions
    toolbar.addEventListener("click", () => setTimeout(update, 30));

    // while typing / caret moves
    ["keyup", "mouseup", "input", "focus", "blur"].forEach(ev => {
      editor.addEventListener(ev, () => setTimeout(update, 0));
    });

    // selection changes
    document.addEventListener("selectionchange", () => {
      if (selectionInside(editor)) update();
      else clearAll();
    });

    update();
  }

  function initAll() {
    // bind all RTE toolbars in this page (currently only description)
    document.querySelectorAll(".rte-toolbar").forEach(bindOne);
  }

  initAll();

  // in case modal content is re-rendered
  const mo = new MutationObserver(initAll);
  mo.observe(document.body, { childList: true, subtree: true });
})();

  // RTE toolbar handler — only for description now
  document.addEventListener("click", e => {
    const btn = e.target.closest(".rte-tool");
    if (!btn) return;

    const field = btn.dataset.field;
    const cmd   = btn.dataset.cmd;

    if (field !== "description") return; // ignore title tools (we removed title RTE)

    const targetEl = rteDesc;
    if (!targetEl) return;

    clearRtePlaceholderOnFocus(targetEl);
    targetEl.focus();

    if (cmd === "createLink") {
      const url = prompt("Enter URL (including https://):", "https://");
      if (url) {
        document.execCommand("createLink", false, url);
      }
    } else {
      document.execCommand(cmd, false, null);
    }
  });

  /* ============= LOAD LIST ============= */
  async function load() {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="py-3 text-center text-muted">Loading…</td>
      </tr>
    `;
    emptyRow.style.display = "none";

    perPage = Number(perPageSel.value || 20);

    const url = `${API}?page=${page}&per_page=${perPage}&q=${encodeURIComponent(query)}`;

    try {
      const res  = await fetch(url, { headers: { Accept: "application/json" } });
      const json = await res.json();

      if (!res.ok) {
        err(json.message || "Failed to load categories");
        paginationLbl.textContent = "Failed to load";
        pager.innerHTML = "";
        return;
      }

      const rows = json.data || [];
      currentRows = rows;
      total = json.pagination?.total || rows.length;

      if (!rows.length) {
        tbody.innerHTML = "";
        emptyRow.style.display = "";
        paginationLbl.textContent = "No results";
        pager.innerHTML = "";
        return;
      }

      // Build tbody with HTML-friendly title/description
      tbody.innerHTML = rows.map(row => {
        const created   = row.created_at ? new Date(row.created_at).toLocaleDateString() : "";
        const titleHtml = row.title || "";
        const descHtml  = row.description || "";

        return `
          <tr data-id="${row.id}">
            <td>${row.id}</td>
            <td class="fw-semibold">${titleHtml || "-"}</td>
            <td>${row.icon ? `<i class="${row.icon}"></i>` : ""}</td>
            <td class="small">${descHtml || ""}</td>
            <td>${created}</td>
            <td class="text-end">
              <div class="dropdown">
                <button type="button"
                        class="btn btn-sm btn-primary border-0 p-0 dd-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        style="width:34px;height:34px;border-radius:10px;">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <button class="dropdown-item cat-edit-btn" data-id="${row.id}">
                      <i class="fa fa-pen-to-square me-2"></i>Edit
                    </button>
                  </li>

                  <li>
                    <button class="dropdown-item text-danger cat-del-btn" data-id="${row.id}">
                      <i class="fa fa-trash me-2"></i>Delete
                    </button>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        `;
      }).join("");

      // Pagination info
      const pages = Math.max(1, Math.ceil(total / perPage));
      paginationLbl.textContent = `Page ${page} of ${pages} — ${total} item(s)`;

      // Build pager
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
        if (start > 2) {
          html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
        }
      }

      for (let i = start; i <= end; i++) {
        html += li(false, i === page, i, i);
      }

      if (end < pages) {
        if (end < pages - 1) {
          html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
        }
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
          load();
          window.scrollTo({ top: 0, behavior: "smooth" });
        });
      });

      // If reorder mode is active and table reloaded somehow, re-init Sortable
      if (reorderMode && !sortableInstance) {
        sortableInstance = Sortable.create(tbody, { animation: 150 });
      }

    } catch (e) {
      console.error(e);
      err("Failed to load data");
      paginationLbl.textContent = "Failed to load";
      pager.innerHTML = "";
    }
  }

  /* ============= MODAL HELPERS ============= */
  function openCreate() {
    idInput.value   = "";
    iconInput.value = "";
    if (titleInput) titleInput.value = "";

    if (rteDesc) {
      rteDesc.classList.add("empty");
      rteDesc.innerHTML = rteDesc.dataset.placeholder || "Write description… (optional)";
    }

    modalTitle.textContent = "Create Category";
    modal.show();
  }

  function openEdit(row) {
    idInput.value   = row.id;
    iconInput.value = row.icon || "";

    if (titleInput) {
      titleInput.value = row.title || "";
    }

    if (rteDesc) {
      if (row.description) {
        rteDesc.classList.remove("empty");
        rteDesc.innerHTML = row.description;
      } else {
        rteDesc.classList.add("empty");
        rteDesc.innerHTML = rteDesc.dataset.placeholder || "Write description… (optional)";
      }
    }

    modalTitle.textContent = "Edit Category";
    modal.show();
  }

  /* ============= SAVE (CREATE / EDIT) ============= */
  form.addEventListener("submit", async e => {
    e.preventDefault();

    const id    = idInput.value;
    const title = (titleInput?.value || "").trim();
    const icon  = iconInput.value.trim();
    const desc  = normalizeRte(rteDesc);

    if (!title) {
      err("Title is required");
      return;
    }

    const payload = {
      title,
      icon,
      description: desc,
    };

    let url    = API;
    let method = "POST";
    if (id) {
      url    = `${API}/${id}`;
      method = "PUT";
    }

    // --- Save button loading state ---
    let originalSaveHtml = null;
    if (btnSave) {
      originalSaveHtml   = btnSave.innerHTML;
      btnSave.disabled   = true;
      btnSave.innerHTML  = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Saving...
      `;
    }

    try {
      const res  = await fetch(url, {
        method,
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": CSRF,
        },
        body: JSON.stringify(payload),
      });
      const json = await res.json();

      if (!res.ok) {
        err(json.message || "Save failed");
        return;
      }

      ok(id ? "Category updated" : "Category created");
      modal.hide();
      load();

    } catch (ex) {
      console.error(ex);
      err("Error saving category");
    } finally {
      // restore Save button
      if (btnSave && originalSaveHtml !== null) {
        btnSave.disabled  = false;
        btnSave.innerHTML = originalSaveHtml;
      }
    }
  });

  /* ============= DELETE ============= */
  async function del(id) {
    const confirmRes = await Swal.fire({
      icon: "warning",
      title: "Delete this category?",
      text: "This action cannot be undone.",
      showCancelButton: true,
      confirmButtonText: "Yes, delete",
      cancelButtonText: "Cancel",
      confirmButtonColor: "#dc2626",
    });

    if (!confirmRes.isConfirmed) return;

    try {
      const res  = await fetch(`${API}/${id}`, {
        method: "DELETE",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": CSRF,
        }
      });
      const json = await res.json();

      if (!res.ok) {
        err(json.message || "Delete failed");
        return;
      }

      ok("Category deleted");
      load();

    } catch (ex) {
      console.error(ex);
      err("Error deleting category");
    }
  }

  /* ============= TABLE BUTTONS ============= */
  tbody.addEventListener("click", e => {
    const editBtn = e.target.closest(".cat-edit-btn");
    if (editBtn) {
      const id  = editBtn.dataset.id;
      const row = currentRows.find(r => String(r.id) === String(id));
      if (row) openEdit(row);
      return;
    }

    const delBtn = e.target.closest(".cat-del-btn");
    if (delBtn) {
      const id = delBtn.dataset.id;
      if (id) del(id);
    }
  });

  /* ============= FILTERS & PER PAGE ============= */
  let searchTimer = null;
  searchInput.addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      query = searchInput.value.trim();
      page  = 1;
      disableReorderMode();
      load();
    }, 300);
  });

  btnReset.addEventListener("click", () => {
    searchInput.value = "";
    query = "";
    perPageSel.value = "20";
    page = 1;
    disableReorderMode();
    load();
  });

  perPageSel.addEventListener("change", () => {
    page = 1;
    disableReorderMode();
    load();
  });

  btnAdd.addEventListener("click", openCreate);

  // Dropdown fix
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".dd-toggle");
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: "outside",
      boundary: "viewport",
    }).toggle();
  });

  /* ============= REORDER BUTTON HANDLER ============= */
  if (btnReorder) {
    btnReorder.addEventListener("click", async () => {
      // First click: enter reorder mode
      if (!reorderMode) {
        enableReorderMode();
        return;
      }

      // Second click in reorder mode: save order
      const rows = Array.from(tbody.querySelectorAll("tr[data-id]"));
      if (!rows.length) {
        err("Nothing to reorder");
        disableReorderMode();
        return;
      }

      const ids = rows.map(tr => tr.dataset.id).filter(Boolean);
      if (!ids.length) {
        err("No rows to reorder");
        return;
      }

      try {
        btnReorder.disabled = true;

        const res  = await fetch(`${API}/reorder`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": CSRF,
          },
          body: JSON.stringify({ ids }),
        });
        const json = await res.json();

        if (!res.ok) {
          err(json.message || "Failed to reorder categories");
          return;
        }

        ok("Order updated");
        disableReorderMode();
        load();

      } catch (ex) {
        console.error(ex);
        err("Error saving order");
      } finally {
        btnReorder.disabled = false;
      }
    });
  }

  // init
  load();
});
</script>
@endpush
