{{-- resources/views/admin/questions/index.blade.php --}}
{{-- Tabbed Admin: Code Questions (unchanged UX) + SQL Questions --}}

@push('styles')
<style>
  /* Layout guards */
  html, body { width:100%; max-width:100%; overflow-x:hidden; }
  .layout, .right-panel, .main-content { overflow-x:hidden; }

  /* Header */
  .page-indicator{
    display:inline-flex;align-items:center;gap:8px;
    background: var(--bg-body);
    border:1px solid var(--border-color);
    border-radius:var(--radius-md);
    padding:10px 12px;
    box-shadow:var(--shadow-sm);
    color: var(--text-color);
  }
  .page-indicator i{color:var(--primary-color);}
  .page-sub{ color: var(--text-muted); font-size: 12px; }

  /* Tabs */
  .nav-tabs .nav-link{ border:1px solid var(--border-color); border-bottom:none; margin-right:6px; }
  .nav-tabs .nav-link.active{ background:#fff; border-bottom-color:#fff; }

  /* Toolbar */
  .q-toolbar{
    display:flex;gap:10px;justify-content:space-between;align-items:center;margin:12px 0;flex-wrap:wrap;
  }
  .q-toolbar .left,.q-toolbar .right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

  /* 2-column shell */
  .q-wrap{ display:grid; grid-template-columns: 330px 1fr; gap:14px; }
  @media (max-width: 992px){ .q-wrap{ grid-template-columns: 1fr; } }

  /* Left list */
  .q-list{
    background: var(--light-color);
    border:1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow:hidden; display:flex; flex-direction:column; min-height: 60vh;
  }
  .q-list-head{ padding:10px; border-bottom:1px solid var(--border-color); display:flex; gap:8px; align-items:center; }
  .q-list-body{ flex:1; overflow:auto; }
  .q-item{
    display:flex; align-items:start; gap:8px;
    padding:10px 12px; border-bottom:1px solid var(--border-color);
    cursor:pointer; background:transparent; transition: background .1s ease;
  }
  .q-item:hover{ background: var(--bg-body); }
  .q-item.active{ background: rgba(99,102,241,.08); }
  .q-item .drag{ cursor:grab; opacity:.75; padding-top:2px; }
  .q-item-title{ font-weight:600; font-size:13px; color: var(--text-color); }
  .q-item-sub{ font-size:12px; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 100%; }

  /* Right editor */
  .q-editor{ background: var(--light-color); border:1px solid var(--border-color); border-radius: var(--radius-md); overflow:hidden; }
  .q-editor-head{ padding:10px 12px; border-bottom:1px solid var(--border-color); display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
  .q-editor-body{ padding:14px; }

  .tiny{ font-size:12px; }
  .form-help{ font-size:12px; color:#6b7280; }
  .text-muted{ color:#6b7280; }

  .chip{ display:inline-flex; align-items:center; gap:6px; padding:2px 8px; border:1px solid var(--border-color);
    border-radius:999px; font-size:12px; background:#fff; }
  .chip .x{ cursor:pointer; opacity:.75; }

  .card-lite{ border:1px solid var(--border-color); border-radius:10px; padding:10px; background:#fff; }
  .card-lite h6{ margin:0 0 8px 0; font-weight:700; font-size:13px; }
  .grid-2{ display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
  .grid-3{ display:grid; grid-template-columns: repeat(3,1fr); gap:12px; }
  .grid-auto{ display:grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap:12px; }

  /* Editors */
  .ce-text-toolbar { display:flex; flex-wrap:wrap; gap:.5em; align-items:center; margin-bottom:6px; }
  .ce-text-toolbar button, .ce-text-toolbar select, .ce-text-toolbar input[type="color"]{
    margin-right:4px; padding:4px 8px; font-size:13px; border:1px solid var(--border-color);
    background:#fff; border-radius:4px; cursor:pointer;
  }
  .ce-text-toolbar .sep{ width:1px; height:22px; background:var(--border-color); margin:0 4px; }
  .ce-text-area{
    border:1px solid var(--border-color); border-radius:6px; min-height:220px; padding:8px; outline:none; background:#fff;
  }
  .ce-text-area:focus{ box-shadow:0 0 0 3px rgba(99,102,241,.15); }

  /* Language card */
  .lang-card,.dialect-card{ border:1px solid var(--border-color); border-radius:10px; padding:10px; background:#fff; margin-bottom:12px; }
  .lang-card .head,.dialect-card .head{ display:flex; gap:8px; align-items:center; }
  .lang-card .drag{ cursor:grab; opacity:.65; }
  .lang-card .row-actions,.dialect-card .row-actions{ margin-left:auto; display:flex; gap:6px; }
  .lang-card details summary,.dialect-card details summary{ cursor:pointer; }

  /* Tests */
  .test-row{ border:1px dashed var(--border-color); border-radius:8px; padding:8px; margin-bottom:8px; background:#fafafa; }
  .test-row .drag{ cursor:grab; opacity:.65; }

  /* Info buttons */
  .i-btn{
    border:1px solid var(--border-color);
    border-radius:999px;
    width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;
    background:#fff; font-size:12px; margin-left:6px; cursor:pointer;
    transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease, color .15s ease;
  }
  .i-btn:hover{ background:#f3f4f6; }
  .i-btn:focus{ outline:0; box-shadow:0 0 0 3px rgba(99,102,241,.25); }

  /* Pretty details/accordion */
  details{ border:1px dashed var(--border-color); border-radius:8px; padding:8px; background:#fff; }
  details > summary{ list-style:none; font-weight:600; display:flex; align-items:center; gap:8px; color: var(--text-color); }
  details > summary::before{ content: "▸"; transition: transform .15s ease; font-size: 12px; color: var(--text-muted); }
  details[open] > summary::before{ transform: rotate(90deg); }

  /* Dark mode */
  html.theme-dark .q-item-sub{ color:#93a4b8; }
  html.theme-dark .ce-text-toolbar button, html.theme-dark .ce-text-toolbar select{ background:#0e1a2d; color:#fff; }
  html.theme-dark .ce-text-area{ background:#0b1526; color:#fff; border-color:rgba(255,255,255,.08); }
  html.theme-dark .chip{ background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.18); color: #e6edf7; }
  html.theme-dark .chip i{ color:#a9b7ff; }
  html.theme-dark .chip strong{ color:#fff; }
  html.theme-dark .q-editor, html.theme-dark .q-list{ background:#0b1526; border-color:rgba(255,255,255,.08); }
  html.theme-dark .q-editor-head{ background:transparent; border-bottom-color:rgba(255,255,255,.12); }
  html.theme-dark .q-item:hover{ background: rgba(255,255,255,.03); }
  html.theme-dark .card-lite, html.theme-dark .lang-card, html.theme-dark .dialect-card{ background:#0b1526; border-color:rgba(255,255,255,.08); }
  html.theme-dark details{ background:#0b1526; border-color:rgba(255,255,255,.12); }
</style>
@endpush

@php
  $topicId   = $topic_id   ?? request()->get('topic_id');
  $moduleId  = $module_id  ?? request()->get('module_id');
  $topicName = $topic_name ?? 'Topic';
  $moduleName= $module_name?? 'Module';
@endphp

<div class="page-head">
  <div class="page-indicator">
    <i class="fa-solid fa-circle-question"></i>
    <strong>Manage Questions</strong>
  </div>
  <div class="page-sub mt-1">
    <i class="fa fa-layer-group me-1"></i> {{ $topicName }}
    <span class="mx-2">/</span>
    <i class="fa fa-rectangle-list me-1"></i> {{ $moduleName }}
  </div>
</div>

<ul class="nav nav-tabs mt-3" id="qTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="tab-code" data-bs-toggle="tab" data-bs-target="#pane-code" type="button" role="tab" aria-controls="pane-code" aria-selected="true">
      <i class="fa-solid fa-code me-1"></i> Code Questions
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tab-sql" data-bs-toggle="tab" data-bs-target="#pane-sql" type="button" role="tab" aria-controls="pane-sql" aria-selected="false">
      <i class="fa-solid fa-database me-1"></i> SQL Questions
    </button>
  </li>
</ul>

<div class="tab-content">
  {{-- ======================= CODE TAB (your original, unchanged) ======================= --}}
  <div class="tab-pane fade show active" id="pane-code" role="tabpanel" aria-labelledby="tab-code">
    <div class="q-toolbar">
      <div class="left">
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-search"></i></span>
          <input id="searchInput" type="search" class="form-control" placeholder="Search by title, slug, status, difficulty…">
        </div>
      </div>
      <div class="right">
        <button class="btn btn-primary" id="btnAdd"><i class="fa fa-plus me-2"></i>Add Question</button>
        <button class="btn btn-light" id="btnRefresh"><i class="fa fa-rotate me-1"></i>Refresh</button>
      </div>
    </div>

    <div class="q-wrap">
      {{-- LEFT: LIST --}}
      <aside class="q-list">
        <div class="q-list-head">
          <span class="tiny text-muted">Questions</span>
          <span class="tiny text-muted ms-auto" id="qCount">—</span>
        </div>
        <div class="q-list-body" id="qList">
          <div class="p-3 text-center text-muted tiny">Loading…</div>
        </div>
      </aside>

      {{-- RIGHT: EDITOR --}}
      <section class="q-editor position-relative">
        <div class="q-editor-head">
          <div class="chip"><i class="fa fa-layer-group"></i> Topic: <strong class="ms-1">{{ $topicName }}</strong></div>
          <div class="chip"><i class="fa fa-rectangle-list"></i> Module: <strong class="ms-1">{{ $moduleName }}</strong></div>
          <div class="ms-auto tiny text-muted" id="saveStatus">—</div>
        </div>

        <div class="q-editor-body">
          <form id="qForm" class="needs-validation" novalidate>
            <input type="hidden" id="qid">
            <input type="hidden" id="topic_id"  value="{{ $topicId }}">
            <input type="hidden" id="module_id" value="{{ $moduleId }}">

            {{-- Meta --}}
            <div class="card-lite mb-3">
              <h6>Meta</h6>
              <div class="grid-3">
                <div>
                  <label class="form-label">Title</label>
                  <span class="i-btn" data-i-title="Title" data-i-text="Human-friendly title shown in the admin list and to users. Max 200 chars.">i</span>
                  <input class="form-control" id="title" required maxlength="200" placeholder="e.g., Sum Two Integers">
                  <div class="invalid-feedback">Title is required.</div>
                </div>
                <div>
                  <label class="form-label">Slug</label>
                  <span class="i-btn" data-i-title="Slug" data-i-text="URL-safe identifier. Auto-generated from title; you can edit if needed.">i</span>
                  <input class="form-control" id="slug" maxlength="200" placeholder="auto-slug-from-title">
                </div>
                <div>
                  <label class="form-label">Sort Order</label>
                  <span class="i-btn" data-i-title="Sort Order" data-i-text="Lower numbers appear first in the list. You can also drag items in the left panel.">i</span>
                  <input type="number" id="sort_order" class="form-control" value="0" min="0">
                </div>
              </div>
              <div class="grid-3 mt-2">
                <div>
                  <label class="form-label">Status</label>
                  <select id="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Difficulty</label>
                  <select id="difficulty" class="form-select">
                    <option value="easy">Easy</option>
                    <option value="medium" selected>Medium</option>
                    <option value="hard">Hard</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Tags (chips)</label>
                  <span class="i-btn" data-i-title="Tags" data-i-text="Type a tag and press Enter to add. Tags help with search and categorization.">i</span>
                  <div class="d-flex flex-wrap gap-2" id="tagsChips"></div>
                  <input class="form-control mt-1" id="tagsInput" placeholder="Type tag and press Enter">
                  <div class="form-help">We’ll convert chips to array automatically.</div>
                </div>
              </div>
            </div>

            {{-- Problem Statement --}}
            <div class="card-lite mb-3">
              <h6>Problem Statement</h6>
              <div class="mb-2">
                <label class="form-label">Description</label>
                <span class="i-btn" data-i-title="Description" data-i-text="The full problem statement. Use the toolbar to format, add links, tables, and images.">i</span>
                <div class="ce-text-toolbar" data-for="desc">
                  <button type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                  <button type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                  <button type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="insertUnorderedList"><i class="fa fa-list-ul"></i></button>
                  <button type="button" data-cmd="insertOrderedList"><i class="fa fa-list-ol"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="createLink">🔗</button>
                  <button type="button" class="btn-insert-image"><i class="fa-regular fa-image"></i> Image URL</button>
                  <button type="button" class="btn-insert-table"><i class="fa-solid fa-table-cells"></i> Table</button>
                  <span class="sep"></span>
                  <select data-cmd="formatBlock">
                    <option value="p">Paragraph</option>
                    <option value="h3">H3</option>
                    <option value="h4">H4</option>
                    <option value="blockquote">Quote</option>
                  </select>
                  <input type="color" data-cmd="foreColor" title="Text color">
                </div>
                <div id="desc" class="ce-text-area" contenteditable="true" placeholder="Describe the problem…"></div>
              </div>
              <div>
                <label class="form-label">Explanation (optional)</label>
                <span class="i-btn" data-i-title="Explanation" data-i-text="Optional editorial notes or solution outline shown to admins/mentors or after solving, depending on your app.">i</span>
                <div class="ce-text-toolbar" data-for="solution">
                  <button type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                  <button type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                  <button type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="insertUnorderedList"><i class="fa fa-list-ul"></i></button>
                  <button type="button" data-cmd="insertOrderedList"><i class="fa fa-list-ol"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="createLink">🔗</button>
                  <button type="button" class="btn-insert-image"><i class="fa-regular fa-image"></i> Image URL</button>
                  <button type="button" class="btn-insert-table"><i class="fa-solid fa-table-cells"></i> Table</button>
                  <span class="sep"></span>
                  <select data-cmd="formatBlock">
                    <option value="p">Paragraph</option>
                    <option value="h4">H4</option>
                    <option value="blockquote">Quote</option>
                  </select>
                  <input type="color" data-cmd="foreColor" title="Text color">
                </div>
                <div id="solution" class="ce-text-area" contenteditable="true" placeholder="Explain the approach…"></div>
              </div>
            </div>

            {{-- Checker --}}
            <div class="card-lite mb-3">
              <h6>Checker</h6>
              <div class="grid-3">
                <div>
                  <label class="form-label">Compare Mode</label>
                  <span class="i-btn" data-i-title="Compare Mode" data-i-text="How to compare expected vs actual output: exact, case-insensitive, tokenized, or floating-point tolerance.">i</span>
                  <select id="compare_mode" class="form-select">
                    <option value="exact">exact</option>
                    <option value="icase">icase</option>
                    <option value="token">token</option>
                    <option value="float_abs">float_abs</option>
                    <option value="float_rel">float_rel</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Trim Output</label>
                  <span class="i-btn" data-i-title="Trim Output" data-i-text="Removes leading/trailing whitespace before comparing.">i</span>
                  <select id="trim_output" class="form-select">
                    <option value="1" selected>Yes</option>
                    <option value="0">No</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Whitespace Mode</label>
                  <span class="i-btn" data-i-title="Whitespace Mode" data-i-text="trim: strip ends; squash: collapse multiple spaces; none: compare as-is.">i</span>
                  <select id="whitespace_mode" class="form-select">
                    <option value="trim" selected>trim</option>
                    <option value="squash">squash</option>
                    <option value="none">none</option>
                  </select>
                </div>
              </div>
              <div class="grid-2 mt-2">
                <div>
                  <label class="form-label">Float Abs Tol</label>
                  <span class="i-btn" data-i-title="Float Abs Tol" data-i-text="Absolute tolerance for floating comparisons. Example: 1e-6. Used when compare_mode is float_abs.">i</span>
                  <input type="number" step="any" id="float_abs_tol" class="form-control" placeholder="e.g., 1e-6">
                </div>
                <div>
                  <label class="form-label">Float Rel Tol</label>
                  <span class="i-btn" data-i-title="Float Rel Tol" data-i-text="Relative tolerance for floating comparisons (fraction of expected). Example: 1e-6. Used when compare_mode is float_rel.">i</span>
                  <input type="number" step="any" id="float_rel_tol" class="form-control" placeholder="e.g., 1e-6">
                </div>
              </div>
              <div class="form-help mt-1">
                These map to DB columns: <code>compare_mode</code>, <code>trim_output</code>, <code>whitespace_mode</code>, <code>float_abs_tol</code>, <code>float_rel_tol</code>.
              </div>
            </div>

            {{-- Languages (ONE section: runtime + limits + snippet) --}}
            <div class="card-lite mb-3">
              <h6>Languages</h6>
              <div id="langsWrap"></div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAddLang"><i class="fa fa-plus me-1"></i>Add Language</button>
              <div class="form-help mt-1">Each language card includes runtime/cmds, resource limits, allow/deny and the starter snippet.</div>
            </div>

            {{-- Tests --}}
            <div class="card-lite mb-3">
              <h6>Tests</h6>
              <div id="testsWrap"></div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAddTest"><i class="fa fa-plus me-1"></i>Add Test</button>
              <div class="form-help mt-1">Drag to reorder. Use <strong>sample</strong> for visible tests and <strong>hidden</strong> for secret tests.</div>
            </div>

            {{-- Save/Delete --}}
            <div class="d-flex gap-2">
              <button class="btn btn-primary" type="submit" id="btnSave"><i class="fa fa-save me-2"></i>Save</button>
              <button class="btn btn-outline-danger" type="button" id="btnDelete"><i class="fa fa-trash me-2"></i>Delete</button>
            </div>
          </form>
        </div>

      </section>
    </div>
  </div>

  {{-- ======================= SQL TAB ======================= --}}
  <div class="tab-pane fade" id="pane-sql" role="tabpanel" aria-labelledby="tab-sql">
    <div class="q-toolbar">
      <div class="left">
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-search"></i></span>
          <input id="sq-search" type="search" class="form-control" placeholder="Search by title, slug, status, difficulty…">
        </div>
      </div>
      <div class="right">
        <button class="btn btn-primary" id="sq-btnAdd"><i class="fa fa-plus me-2"></i>Add SQL Question</button>
        <button class="btn btn-light" id="sq-btnRefresh"><i class="fa fa-rotate me-1"></i>Refresh</button>
      </div>
    </div>

    <div class="q-wrap">
      {{-- LEFT --}}
      <aside class="q-list">
        <div class="q-list-head">
          <span class="tiny text-muted">SQL Questions</span>
          <span class="tiny text-muted ms-auto" id="sq-count">—</span>
        </div>
        <div class="q-list-body" id="sq-list"><div class="p-3 text-center text-muted tiny">Loading…</div></div>
      </aside>

      {{-- RIGHT --}}
      <section class="q-editor position-relative">
        <div class="q-editor-head">
          <div class="chip"><i class="fa fa-database"></i> SQL</div>
          <div class="chip"><i class="fa fa-layer-group"></i> Topic: <strong class="ms-1">{{ $topicName }}</strong></div>
          <div class="chip"><i class="fa fa-rectangle-list"></i> Module: <strong class="ms-1">{{ $moduleName }}</strong></div>
          <div class="ms-auto tiny text-muted" id="sq-saveStatus">—</div>
        </div>

        <div class="q-editor-body">
          <form id="sq-form" class="needs-validation" novalidate>
            <input type="hidden" id="sq-id">
            <input type="hidden" id="sq-topic"  value="{{ $topicId }}">
            <input type="hidden" id="sq-module" value="{{ $moduleId }}">

            {{-- Meta --}}
            <div class="card-lite mb-3">
              <h6>Meta</h6>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Title</label>
                  <input class="form-control" id="sq-title" required maxlength="200" placeholder="e.g., Employees earning > 5000">
                  <div class="invalid-feedback">Title is required.</div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Slug</label>
                  <input class="form-control" id="sq-slug" maxlength="200" placeholder="auto-slug">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Difficulty</label>
                  <select id="sq-difficulty" class="form-select">
                    <option value="easy">Easy</option>
                    <option value="medium" selected>Medium</option>
                    <option value="hard">Hard</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Sort Order</label>
                  <input type="number" id="sq-sort" class="form-control" value="0" min="0">
                </div>
              </div>
              <div class="row g-3 mt-1">
                <div class="col-md-3">
                  <label class="form-label">Status</label>
                  <select id="sq-status" class="form-select">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                  </select>
                </div>
              </div>
            </div>

            {{-- Problem --}}
            <div class="card-lite mb-3">
              <h6>Problem Statement</h6>
              <label class="form-label">Description</label>
              <textarea id="sq-desc" class="form-control" rows="5" placeholder="Describe the SQL problem…"></textarea>
            </div>

            {{-- Dialects --}}
            <div class="card-lite mb-3">
              <h6>SQL Dialects</h6>
              <div id="sq-dialects"></div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="sq-addDialect"><i class="fa fa-plus me-1"></i>Add Dialect</button>
              <div class="form-help mt-1">Supports MySQL, PostgreSQL, MongoDB. Each dialect has runtime key, optional time limit, and a starter query.</div>
            </div>

            {{-- Tests --}}
            <div class="card-lite mb-3">
              <h6>Tests</h6>
              <div id="sq-tests"></div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="sq-addTest"><i class="fa fa-plus me-1"></i>Add Test</button>
              <div class="form-help mt-1">Each test includes <strong>db_key</strong>, input DB setup (schema + seed) and expected rows/output.</div>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-primary" type="submit" id="sq-save"><i class="fa fa-save me-2"></i>Save</button>
              <button class="btn btn-outline-danger" type="button" id="sq-delete"><i class="fa fa-trash me-2"></i>Delete</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>
</div>

{{-- Toasts (shared) --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

@push('scripts')
<script>"use strict";</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ============== tiny shared helpers (not global polluted) ============== */
const _isDark = () => (localStorage.getItem('theme') === 'dark');
const _esc = (s)=> (s??'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
const _debounce=(fn,ms=350)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}};
function _toast(id,msg){ const el=document.getElementById(id); el.querySelector('.toast-body').textContent=msg; (new bootstrap.Toast(el)).show(); }
const _ok = (m)=>_toast('toastSuccess', m), _err=(m)=>_toast('toastError', m);
function _getToken(){ return localStorage.getItem('token') || sessionStorage.getItem('token') || ''; }
function _hdr(){ return { 'Authorization': `Bearer ${_getToken()}` }; }
function _hdrJSON(){ return { ..._hdr(), 'Content-Type':'application/json' }; }
function _toNum(v){ const n=Number(v); return Number.isFinite(n)?n:null; }
function _slugify(s){ return (s||'').toString().trim().toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,200); }

/* =====================================================================================
   CODE TAB (kept the same, plus one important fix: unique IDs prevent collisions)
   The previous "Add Language not working" bug was from ID collisions when merging pages.
   Here, the Code tab uses #btnAddLang and the SQL tab uses #sq-addDialect — no conflicts.
===================================================================================== */
(function(){
  "use strict";
  const TOPIC_ID  = {{ json_encode($topicId) }};
  const MODULE_ID = {{ json_encode($moduleId) }};
  if(!TOPIC_ID || !MODULE_ID){
    document.getElementById('pane-code').innerHTML = `<div class="p-4 text-danger">Missing topic_id or module_id.</div>`;
    return;
  }

  // ===== Presets for dropdowns =====
  const LANGUAGE_OPTIONS = [
    'python','cpp','c','java','javascript','typescript','go','ruby','rust','php','csharp','kotlin'
  ];
  const LANGUAGE_RUNTIMES = {
    python:     ['piston','judge0','dockerlocal'],
    cpp:        ['judge0','dockerlocal','piston'],
    c:          ['judge0','dockerlocal','piston'],
    java:       ['judge0','dockerlocal','piston'],
    javascript: ['piston','judge0','dockerlocal'],
    typescript: ['piston','judge0','dockerlocal'],
    go:         ['judge0','dockerlocal'],
    ruby:       ['piston','dockerlocal'],
    rust:       ['judge0','dockerlocal'],
    php:        ['piston','judge0','dockerlocal'],
    csharp:     ['judge0','dockerlocal'],
    kotlin:     ['judge0','dockerlocal']
  };
  const RUNTIME_FALLBACK = ['piston','judge0','dockerlocal'];
  const runtimeOptionsFor = (lang)=> LANGUAGE_RUNTIMES[lang] || RUNTIME_FALLBACK;

  // ===== API =====
  const API = {
    list:    () => fetch(`/api/questions?topic_id=${TOPIC_ID}&module_id=${MODULE_ID}&per_page=200`, { headers: _hdr() }),
    get:     id => fetch(`/api/questions/${id}`, { headers: _hdr() }),
    create:  payload => fetch('/api/questions', { method:'POST', headers: _hdrJSON(), body: JSON.stringify(payload) }),
    update: (id,payload)=> fetch(`/api/questions/${id}`, { method:'PUT', headers: _hdrJSON(), body: JSON.stringify(payload) }),
    delete:  id => fetch(`/api/questions/${id}`, { method:'DELETE', headers: _hdr() }),
    reorder: order => fetch('/api/questions/reorder', { method:'POST', headers: _hdrJSON(), body: JSON.stringify({ order }) }),
  };

  // ===== DOM =====
  const qList = document.getElementById('qList');
  const qCount= document.getElementById('qCount');
  const btnRefresh = document.getElementById('btnRefresh');
  const btnAdd = document.getElementById('btnAdd');
  const searchInput = document.getElementById('searchInput');

  const form = document.getElementById('qForm');
  const qid  = document.getElementById('qid');
  const title= document.getElementById('title');
  const slug = document.getElementById('slug');
  const status = document.getElementById('status');
  const difficulty = document.getElementById('difficulty');
  const sort_order = document.getElementById('sort_order');

  const desc = document.getElementById('desc');
  const solution = document.getElementById('solution');

  // Checker
  const compare_mode    = document.getElementById('compare_mode');
  const trim_output     = document.getElementById('trim_output');
  const whitespace_mode = document.getElementById('whitespace_mode');
  const float_abs_tol   = document.getElementById('float_abs_tol');
  const float_rel_tol   = document.getElementById('float_rel_tol');

  // Tags chips
  const tagsChips = document.getElementById('tagsChips');
  const tagsInput = document.getElementById('tagsInput');

  // Languages unified
  const langsWrap  = document.getElementById('langsWrap');
  const btnAddLang = document.getElementById('btnAddLang');

  // Tests
  const testsWrap  = document.getElementById('testsWrap');
  const btnAddTest = document.getElementById('btnAddTest');

  const btnSave = document.getElementById('btnSave');
  const btnDelete = document.getElementById('btnDelete');
  const saveStatus = document.getElementById('saveStatus');

  // ===== State =====
  let all = [];
  let view = [];
  let currentId = null;

  let langBlocks = []; // unified rows
  let testRows   = [];

  let tags = []; // chips
  let activeEditor; // <-- declare once

  // ===== Chips =====
  function renderChips(){
    tagsChips.innerHTML = '';
    tags.forEach((t,i)=>{
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.innerHTML = `<i class="fa fa-tag"></i> ${_esc(t)} <span class="x" title="remove">✕</span>`;
      chip.querySelector('.x').addEventListener('click', ()=>{ tags.splice(i,1); renderChips(); });
      tagsChips.appendChild(chip);
    });
  }
  tagsInput.addEventListener('keydown', (e)=>{
    if(e.key==='Enter'){
      e.preventDefault();
      const v = tagsInput.value.trim();
      if(v && !tags.includes(v)){ tags.push(v); renderChips(); }
      tagsInput.value='';
    }
  });

  // ===== List loading =====
  async function loadList(){
    qList.innerHTML = `<div class="p-3 text-center text-muted tiny">Loading…</div>`;
    try{
      const json = await API.list().then(r=>r.json());
      const rows = Array.isArray(json.data?.data) ? json.data.data
                : Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
      all = rows.map((r,i)=>({ ...r, sort_order: r.sort_order ?? i }));
      qCount.textContent = `${all.length} total`;
      applyFilter();
      if (all.length) select(all[0].id); else resetForm();
    }catch(e){ qList.innerHTML = `<div class="p-3 text-danger tiny">Failed to load</div>`; }
  }

  function applyFilter(){
    const q = (searchInput.value||'').toLowerCase().trim();
    view = !q ? [...all] : all.filter(r=>{
      const hay = [r.title, r.slug, r.status, r.difficulty].map(x=>(x||'').toLowerCase()).join(' ');
      return hay.includes(q);
    });
    view.sort((a,b)=> (a.sort_order??0)-(b.sort_order??0) || (a.id-b.id));
    renderList();
  }
  searchInput.addEventListener('input', _debounce(applyFilter, 200));

  function renderList(){
    if(!view.length){
      qList.innerHTML = `
        <div class="p-4 text-center text-muted">
          <i class="fa-regular fa-folder-open fa-2x mb-2"></i>
          <div>No questions found</div>
        </div>`;
      return;
    }

    qList.innerHTML = '';
    view.forEach(row=>{
      const item = document.createElement('div');
      item.className = 'q-item';
      item.dataset.id = row.id;
      item.draggable = true;

      const sub =
        (row.slug ? `/${row.slug}` : '') +
        (row.difficulty ? ` • ${row.difficulty}` : '');

      item.innerHTML = `
        <div class="drag"><i class="fa fa-grip-vertical"></i></div>
        <div class="flex-1">
          <div class="q-item-title text-truncate">${_esc(row.title || 'Untitled')}</div>
          <div class="q-item-sub">${_esc(sub || '—')}</div>
        </div>
        <div class="badge ${row.status === 'active'
          ? 'bg-success'
          : (row.status === 'archived' ? 'bg-secondary' : 'bg-warning text-dark')
        }">${_esc(row.status || 'active')}</div>
      `;

      item.addEventListener('click', (e)=>{ if (!e.target.closest('.drag')) select(row.id); });
      item.addEventListener('dragstart', onDragStart);
      item.addEventListener('dragover', onDragOver);
      item.addEventListener('dragleave', onDragLeave);
      item.addEventListener('drop', onDrop);
      item.addEventListener('dragend', onDragEnd);

      if (row.id === currentId) item.classList.add('active');
      qList.appendChild(item);
    });
  }

  function markActive(){
    qList.querySelectorAll('.q-item')
      .forEach(n => n.classList.toggle('active', String(n.dataset.id) === String(currentId)));
  }

  // ===== Select item =====
  async function select(id){
    currentId = id; markActive(); saveStatus.textContent = 'Loading…';
    try{
      const json = await API.get(id).then(r=>r.json());
      const q = json.data || json.question || json;

      // Basics
      qid.value = q.id || '';
      title.value = q.title || '';
      slug.value = q.slug || '';
      status.value = q.status || 'active';
      difficulty.value = q.difficulty || 'medium';
      sort_order.value = q.sort_order ?? 0;

      desc.innerHTML = q.description || '';
      solution.innerHTML = q.solution || '';

      // Checker
      compare_mode.value    = q.compare_mode || 'exact';
      trim_output.value     = (q.trim_output ?? true) ? '1':'0';
      whitespace_mode.value = q.whitespace_mode || 'trim';
      float_abs_tol.value   = q.float_abs_tol ?? '';
      float_rel_tol.value   = q.float_rel_tol ?? '';

      // Tags
      tags = Array.isArray(q.tags) ? q.tags.slice(0) : [];
      renderChips();

      // Languages unified (merge languages + snippets by language_key)
      const langs = Array.isArray(q.languages) ? q.languages : (q.question_languages||[]);
      const snips = Array.isArray(q.snippets)  ? q.snippets  : (q.question_snippets||[]);
      const snipMap = new Map(snips.map(s=>[s.language_key, s]));

      langBlocks = (langs||[]).map((L,i)=>({
        id: L.id,
        language_key: L.language_key||'',
        runtime_key: L.runtime_key||'',
        source_filename: L.source_filename||'',
        compile_cmd: L.compile_cmd||'',
        run_cmd: L.run_cmd||'',
        time_limit_ms: L.time_limit_ms ?? '',
        memory_limit_kb: L.memory_limit_kb ?? '',
        stdout_kb_max: L.stdout_kb_max ?? '',
        line_limit: L.line_limit ?? '',
        byte_limit: L.byte_limit ?? '',
        max_inputs: L.max_inputs ?? '',
        max_stdin_tokens: L.max_stdin_tokens ?? '',
        max_args: L.max_args ?? '',
        allow_label: L.allow_label||'',
        allow: normalizeToArray(L.allow),
        forbid_regex: normalizeToArray(L.forbid_regex),
        is_enabled: L.is_enabled !== false,
        sort_order: L.sort_order ?? i,
        // snippet merged:
        entry_hint: snipMap.get(L.language_key)?.entry_hint || '',
        template:   snipMap.get(L.language_key)?.template   || '',
        is_default: !!(snipMap.get(L.language_key)?.is_default)
      }));
      // include snippets-only
      snips.forEach((s)=>{
        if(!langBlocks.find(b=>b.language_key===s.language_key)){
          langBlocks.push({
            language_key: s.language_key, runtime_key:'', source_filename:'',
            compile_cmd:'', run_cmd:'', time_limit_ms:'', memory_limit_kb:'', stdout_kb_max:'',
            line_limit:'', byte_limit:'', max_inputs:'', max_stdin_tokens:'', max_args:'',
            allow_label:'', allow:[], forbid_regex:[], is_enabled:true, sort_order: langBlocks.length,
            entry_hint: s.entry_hint||'', template: s.template||'', is_default: !!s.is_default
          });
        }
      });
      renderLangs();

      // Tests
      testRows = (q.tests || q.question_tests || []).map((t,i)=>({
        id:t.id, uid:t.uid||null, visibility:t.visibility||'hidden',
        input:t.input ?? '', expected:t.expected ?? '',
        score: t.score ?? 1, is_active: !!t.is_active, sort_order: t.sort_order ?? i
      }));
      renderTests();

      saveStatus.textContent = 'Loaded'; setTimeout(()=> saveStatus.textContent='—', 800);
    }catch(e){ _err('Failed to load question'); saveStatus.textContent = 'Error'; }
  }

  function normalizeToArray(v){
    if (!v) return [];
    if (Array.isArray(v)) return v;
    try {
      const p = typeof v === 'string' ? JSON.parse(v) : v;
      return Array.isArray(p) ? p : [];
    } catch { return []; }
  }

  function resetForm(){
    form.reset();
    qid.value=''; title.value=''; slug.value='';
    desc.innerHTML=''; solution.innerHTML='';
    compare_mode.value='exact'; trim_output.value='1'; whitespace_mode.value='trim';
    float_abs_tol.value=''; float_rel_tol.value='';
    tags = []; renderChips();
    langBlocks=[]; renderLangs();
    testRows=[]; renderTests();
  }

  // ===== Language block UI =====
  function optionsHTML(list, selected){
    return list.map(v=>`<option value="${_esc(v)}" ${selected===v?'selected':''}>${_esc(v)}</option>`).join('');
  }

  function langCard(row, idx){
    const runtimeOpts = runtimeOptionsFor(row.language_key || 'python');
    const selectedRuntime = runtimeOpts.includes(row.runtime_key) ? row.runtime_key : runtimeOpts[0];

    return `
      <div class="lang-card" data-lang="${idx}">
        <div class="head mb-2">
          <span class="drag"><i class="fa fa-grip-vertical"></i></span>
          <strong>Language</strong>
          <div class="row-actions">
            <label class="form-check form-switch tiny mt-1">
              <input class="form-check-input lang_enabled" type="checkbox" ${row.is_enabled?'checked':''}>
              <span class="form-check-label">enabled</span>
            </label>
            <button type="button" class="btn btn-sm btn-outline-danger btnDelLang">Delete</button>
          </div>
        </div>

        <div class="grid-3">
          <div>
            <label class="form-label">language_key</label>
            <select class="form-select lang_language_key">
              ${optionsHTML(LANGUAGE_OPTIONS, row.language_key||'python')}
            </select>
          </div>
          <div>
            <label class="form-label">runtime_key</label>
            <select class="form-select lang_runtime_key">
              ${optionsHTML(runtimeOpts, selectedRuntime)}
            </select>
          </div>
          <div>
            <label class="form-label">source_filename</label>
            <span class="i-btn" data-i-title="Source filename" data-i-text="Default source file used when compiling/running the solution.">i</span>
            <input class="form-control lang_source_filename" value="${_esc(row.source_filename||'')}" placeholder="main.py / main.c / Main.java">
          </div>
        </div>

        <div class="grid-3 mt-2">
          <div>
            <label class="form-label">compile_cmd</label>
            <span class="i-btn" data-i-title="Compile command" data-i-text="Compilation command for compiled languages. Leave empty for interpreted languages. Example: gcc -O2 main.c -o main">i</span>
            <input class="form-control lang_compile_cmd" value="${_esc(row.compile_cmd||'')}" placeholder="gcc -O2 main.c -o main">
          </div>
            <div>
            <label class="form-label">run_cmd</label>
            <span class="i-btn" data-i-title="Run command" data-i-text="How to execute the program. Example: ./main or python3 main.py">i</span>
            <input class="form-control lang_run_cmd" value="${_esc(row.run_cmd||'')}" placeholder="./main or python3 main.py">
          </div>
          <div>
            <label class="form-label">stdout_kb_max</label>
            <span class="i-btn" data-i-title="Stdout limit" data-i-text="Maximum stdout size (KB) captured before truncation or failure.">i</span>
            <input type="number" class="form-control lang_stdout_kb_max" value="${row.stdout_kb_max??''}" min="0">
          </div>
        </div>

        <details class="mt-2" open>
          <summary class="small">Resource Limits & Allow/Deny</summary>
          <div class="grid-3 mt-2">
            <div><label class="form-label">time_limit_ms</label><span class="i-btn" data-i-title="Time limit (ms)" data-i-text="Maximum allowed execution time for a single run in milliseconds.">i</span><input type="number" class="form-control lang_time_limit_ms" value="${row.time_limit_ms??''}" min="0"></div>
            <div><label class="form-label">memory_limit_kb</label><span class="i-btn" data-i-title="Memory limit (KB)" data-i-text="Maximum memory in kilobytes.">i</span><input type="number" class="form-control lang_memory_limit_kb" value="${row.memory_limit_kb??''}" min="0"></div>
            <div><label class="form-label">line_limit</label><span class="i-btn" data-i-title="Line limit" data-i-text="Optional output line cap to prevent runaway output.">i</span><input type="number" class="form-control lang_line_limit" value="${row.line_limit??''}" min="0"></div>
          </div>
          <div class="grid-3 mt-2">
            <div><label class="form-label">byte_limit</label><span class="i-btn" data-i-title="Byte limit" data-i-text="Maximum total output bytes allowed.">i</span><input type="number" class="form-control lang_byte_limit" value="${row.byte_limit??''}" min="0"></div>
            <div><label class="form-label">max_inputs</label><span class="i-btn" data-i-title="Max inputs" data-i-text="Number of separate input runs allowed for this question/language.">i</span><input type="number" class="form-control lang_max_inputs" value="${row.max_inputs??''}" min="0"></div>
            <div><label class="form-label">max_stdin_tokens</label><span class="i-btn" data-i-title="Max stdin tokens" data-i-text="Upper bound for tokenized stdin, when your infrastructure measures tokens.">i</span><input type="number" class="form-control lang_max_stdin_tokens" value="${row.max_stdin_tokens??''}" min="0"></div>
          </div>
          <div class="grid-3 mt-2">
            <div><label class="form-label">max_args</label><span class="i-btn" data-i-title="Max args" data-i-text="Maximum number of command-line arguments allowed.">i</span><input type="number" class="form-control lang_max_args" value="${row.max_args??''}" min="0"></div>
            <div>
              <label class="form-label">allow_label</label>
              <select class="form-select lang_allow_label">
                ${optionsHTML(['headers','imports','modules','packages','paths','none'], row.allow_label||'')}
              </select>
            </div>
            <div>
              <label class="form-label">allow (chips)</label>
              <span class="i-btn" data-i-title="Allow list" data-i-text="Whitelist of permitted headers/imports/modules depending on language/runtime. Press Enter to add.">i</span>
              <div class="chips chips-allow"></div>
              <input class="form-control chips-input chips-allow-input mt-1" placeholder="Add item and press Enter" value="${_esc((row.allow||[]).join(', '))}" data-prefill>
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label">forbid_regex (chips)</label>
            <span class="i-btn" data-i-title="Forbidden patterns" data-i-text="Regex patterns to block dangerous code or APIs. Press Enter to add.">i</span>
            <div class="chips chips-forbid"></div>
            <input class="form-control chips-input chips-forbid-input mt-1" placeholder="Add pattern and press Enter" value="${_esc((row.forbid_regex||[]).join(', '))}" data-prefill>
          </div>
        </details>

        <details class="mt-2" open>
          <summary class="small">Starter Snippet</summary>
          <div class="grid-3 mt-2">
            <div>
              <label class="form-label">entry_hint</label>
              <span class="i-btn" data-i-title="Entry hint" data-i-text="Short instruction shown next to the starter code (e.g., 'Implement solve()').">i</span>
              <input class="form-control snip_entry_hint" value="${_esc(row.entry_hint||'')}" placeholder="Implement solve()">
            </div>
            <div>
              <label class="form-label">Default</label>
              <select class="form-select snip_is_default">
                <option value="0" ${!row.is_default?'selected':''}>No</option>
                <option value="1" ${row.is_default?'selected':''}>Yes</option>
              </select>
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label">template</label>
            <span class="i-btn" data-i-title="Template" data-i-text="Starter code provided to the user. Keep it minimal, compile-ready, and consistent with runtime/commands.">i</span>
            <textarea class="form-control snip_template" rows="6" placeholder="// starter code…">${_esc(row.template||'')}</textarea>
          </div>
        </details>
      </div>
    `;
  }

  function renderChipSet(container, items, onChange){
    container.innerHTML = '';
    (items||[]).forEach((t,i)=>{
      const chip = document.createElement('span');
      chip.className = 'chip me-1 mb-1';
      chip.innerHTML = `<i class="fa fa-tag"></i> ${_esc(t)} <span class="x" title="remove">✕</span>`;
      chip.querySelector('.x').addEventListener('click', ()=>{
        const arr = (items||[]).slice(0); arr.splice(i,1); onChange(arr); renderChipSet(container, arr, onChange);
      });
      container.appendChild(chip);
    });
  }

  function renderLangs(){
    langsWrap.innerHTML = langBlocks.map((r,i)=>langCard(r,i)).join('') || `<div class="text-muted small">No languages yet.</div>`;

    // delete handlers
    langsWrap.querySelectorAll('.btnDelLang').forEach(btn=>{
      btn.addEventListener('click', e=>{
        const card = e.target.closest('[data-lang]');
        const idx = parseInt(card.dataset.lang,10);
        langBlocks.splice(idx,1); renderLangs();
      });
    });

    // drag ordering + dependent dropdowns + chips
    langsWrap.querySelectorAll('[data-lang]').forEach(card=>{
      card.draggable = true;
      card.addEventListener('dragstart', e=>{ card.classList.add('opacity-50'); e.dataTransfer.effectAllowed='move'; });
      card.addEventListener('dragend', e=>{ card.classList.remove('opacity-50'); });
      card.addEventListener('dragover', e=>{ e.preventDefault(); card.classList.add('bg-light'); });
      card.addEventListener('dragleave', e=>{ card.classList.remove('bg-light'); });
      card.addEventListener('drop', e=>{
        e.preventDefault(); card.classList.remove('bg-light');
        const from = parseInt(document.querySelector('[data-lang].opacity-50')?.dataset.lang, 10);
        const to   = parseInt(card.dataset.lang, 10);
        if (Number.isInteger(from) && Number.isInteger(to) && from !== to) {
          const row = langBlocks.splice(from, 1)[0];
          langBlocks.splice(to, 0, row);
          renderLangs();
        }
      });

      const idx = parseInt(card.dataset.lang,10);
      const selLang = card.querySelector('.lang_language_key');
      const selRun  = card.querySelector('.lang_runtime_key');

      selLang.addEventListener('change', ()=>{
        const newLang = selLang.value;
        const allowed = runtimeOptionsFor(newLang);
        const current = selRun.value;
        const chosen = allowed.includes(current) ? current : allowed[0];
        selRun.innerHTML = allowed.map(v=>`<option value="${_esc(v)}" ${chosen===v?'selected':''}>${_esc(v)}</option>`).join('');
        if (langBlocks[idx]){
          langBlocks[idx].language_key = newLang;
          langBlocks[idx].runtime_key  = chosen;
          if(!langBlocks[idx].source_filename){ langBlocks[idx].source_filename = defaultSourceFilename(newLang); }
          if(!langBlocks[idx].run_cmd){ langBlocks[idx].run_cmd = defaultRunCmd(newLang); }
          if(newLang==='python' && !langBlocks[idx].compile_cmd){ langBlocks[idx].compile_cmd=''; }
        }
      });
      selRun.addEventListener('change', ()=>{ if (langBlocks[idx]) langBlocks[idx].runtime_key = selRun.value; });

      // init chips
      const allowInput  = card.querySelector('.chips-allow-input');
      const forbidInput = card.querySelector('.chips-forbid-input');
      const allowWrap   = card.querySelector('.chips-allow');
      const forbidWrap  = card.querySelector('.chips-forbid');

      // prefill from the visible CSV (first render only)
      if (allowInput?.dataset.prefill) {
        const arr = (allowInput.value||'').split(',').map(s=>s.trim()).filter(Boolean);
        if (!langBlocks[idx].allow?.length) langBlocks[idx].allow = arr;
        delete allowInput.dataset.prefill;
      }
      if (forbidInput?.dataset.prefill) {
        const arr = (forbidInput.value||'').split(',').map(s=>s.trim()).filter(Boolean);
        if (!langBlocks[idx].forbid_regex?.length) langBlocks[idx].forbid_regex = arr;
        delete forbidInput.dataset.prefill;
      }

      renderChipSet(allowWrap,  langBlocks[idx].allow,        v=>{ langBlocks[idx].allow = v; });
      renderChipSet(forbidWrap, langBlocks[idx].forbid_regex, v=>{ langBlocks[idx].forbid_regex = v; });

      allowInput?.addEventListener('keydown', e=>{
        if(e.key==='Enter'){ e.preventDefault(); const v = allowInput.value.trim();
          if(v && !langBlocks[idx].allow.includes(v)){ langBlocks[idx].allow.push(v); }
          allowInput.value=''; renderChipSet(allowWrap, langBlocks[idx].allow, v=>{ langBlocks[idx].allow=v; });
        }
      });
      forbidInput?.addEventListener('keydown', e=>{
        if(e.key==='Enter'){ e.preventDefault(); const v = forbidInput.value.trim();
          if(v && !langBlocks[idx].forbid_regex.includes(v)){ langBlocks[idx].forbid_regex.push(v); }
          forbidInput.value=''; renderChipSet(forbidWrap, langBlocks[idx].forbid_regex, v=>{ langBlocks[idx].forbid_regex=v; });
        }
      });
    });

    attachInfoButtons();
  }

  function defaultSourceFilename(lang){
    switch(lang){
      case 'python': return 'main.py';
      case 'cpp': return 'main.cpp';
      case 'c': return 'main.c';
      case 'java': return 'Main.java';
      case 'javascript': return 'main.js';
      case 'typescript': return 'main.ts';
      case 'go': return 'main.go';
      case 'ruby': return 'main.rb';
      case 'rust': return 'main.rs';
      case 'php': return 'main.php';
      case 'csharp': return 'Program.cs';
      case 'kotlin': return 'Main.kt';
      default: return 'main.txt';
    }
  }
  function defaultRunCmd(lang){
    switch(lang){
      case 'python': return 'python3 main.py';
      case 'cpp': return './main';
      case 'c': return './main';
      case 'java': return 'java Main';
      case 'javascript': return 'node main.js';
      case 'typescript': return 'ts-node main.ts';
      case 'go': return './main';
      case 'ruby': return 'ruby main.rb';
      case 'rust': return './main';
      case 'php': return 'php main.php';
      case 'csharp': return './main';
      case 'kotlin': return 'java -jar main.jar';
      default: return './main';
    }
  }

  // IMPORTANT FIX: ensure the Add Language button is unique to this tab
  btnAddLang.addEventListener('click', ()=>{
    const lang = 'python';
    const allowed = runtimeOptionsFor(lang);
    langBlocks.push({
      language_key: lang,
      runtime_key: allowed[0],
      source_filename: defaultSourceFilename(lang),
      compile_cmd:'', run_cmd: defaultRunCmd(lang),
      time_limit_ms:'', memory_limit_kb:'', stdout_kb_max:'',
      line_limit:'', byte_limit:'', max_inputs:'', max_stdin_tokens:'', max_args:'',
      allow_label:'imports', allow:[], forbid_regex:[],
      entry_hint:'read from stdin, write to stdout', template:'', is_default: langBlocks.length===0,
      is_enabled:true, sort_order: langBlocks.length
    });
    renderLangs();
  });

  // ===== Tests UI =====
  function testCard(row, idx){
    return `
      <div class="test-row" data-test="${idx}">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="drag"><i class="fa fa-grip-vertical"></i></span>
          <strong class="me-auto">Test #${idx+1}</strong>
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm t_visibility" style="width:130px">
              <option value="sample" ${row.visibility==='sample'?'selected':''}>sample</option>
              <option value="hidden" ${row.visibility!=='sample'?'selected':''}>hidden</option>
            </select>
            <input type="number" class="form-control form-control-sm t_score" style="width:90px" min="0" value="${row.score??1}" placeholder="score">
            <select class="form-select form-select-sm t_active" style="width:110px">
              <option value="1" ${row.is_active!==false?'selected':''}>active</option>
              <option value="0" ${row.is_active===false?'selected':''}>inactive</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger btnDelTest">Delete</button>
          </div>
        </div>
        <div class="grid-2">
          <div>
            <label class="form-label">Input</label>
            <span class="i-btn" data-i-title="Test Input" data-i-text="Stdin fed to the program for this test case.">i</span>
            <textarea class="form-control t_input" rows="3" placeholder="stdin">${_esc(row.input||'')}</textarea>
          </div>
          <div>
            <label class="form-label">Expected Output</label>
            <span class="i-btn" data-i-title="Expected Output" data-i-text="What the program should print to stdout for the input above.">i</span>
            <textarea class="form-control t_expected" rows="3" placeholder="stdout">${_esc(row.expected||'')}</textarea>
          </div>
        </div>
      </div>
    `;
  }

  function renderTests(){
    testsWrap.innerHTML = testRows.map((r,i)=>testCard(r,i)).join('') || `<div class="text-muted small">No tests yet.</div>`;
    testsWrap.querySelectorAll('.btnDelTest').forEach(btn=>{
      btn.addEventListener('click', e=>{
        const row = e.target.closest('[data-test]');
        const idx = parseInt(row.dataset.test,10);
        testRows.splice(idx,1); renderTests();
      });
    });
    // drag
    testsWrap.querySelectorAll('[data-test]').forEach(card=>{
      card.draggable=true;
      card.addEventListener('dragstart', e=>{ card.classList.add('opacity-50'); e.dataTransfer.effectAllowed='move'; });
      card.addEventListener('dragend', e=>{ card.classList.remove('opacity-50'); });
      card.addEventListener('dragover', e=>{ e.preventDefault(); card.classList.add('bg-light'); });
      card.addEventListener('dragleave', e=>{ card.classList.remove('bg-light'); });
      card.addEventListener('drop', e=>{
        e.preventDefault(); card.classList.remove('bg-light');
        const from = parseInt(document.querySelector('[data-test].opacity-50')?.dataset.test,10);
        const to   = parseInt(card.dataset.test,10);
        if(Number.isInteger(from) && Number.isInteger(to) && from!==to){
          const row = testRows.splice(from,1)[0]; testRows.splice(to,0,row); renderTests();
        }
      });
    });

    attachInfoButtons();
  }
  btnAddTest.addEventListener('click', ()=>{
    testRows.push({visibility:'sample', input:'', expected:'', score:1, is_active:true, sort_order:testRows.length});
    renderTests();
  });

  // ===== Left list DnD persist =====
  let dragSrc = null;
  function onDragStart(e){ dragSrc = e.currentTarget; e.dataTransfer.effectAllowed='move'; e.currentTarget.classList.add('opacity-50'); }
  function onDragOver(e){ e.preventDefault(); e.dataTransfer.dropEffect='move'; e.currentTarget.classList.add('bg-light'); }
  function onDragLeave(e){ e.currentTarget.classList.remove('bg-light'); }
  async function onDrop(e){
    e.preventDefault();
    const target = e.currentTarget; target.classList.remove('bg-light'); if (dragSrc === target) return;
    const rect = target.getBoundingClientRect();
    const before = (e.clientY - rect.top) < rect.height/2;
    if(before) target.parentNode.insertBefore(dragSrc, target);
    else target.parentNode.insertBefore(dragSrc, target.nextSibling);
    await persistOrder();
  }
  function onDragEnd(e){ e.currentTarget.classList.remove('opacity-50'); qList.querySelectorAll('.bg-light').forEach(n=>n.classList.remove('bg-light')); }
  function getIdsFromDOM(){ return Array.from(qList.querySelectorAll('.q-item')).map(n=>parseInt(n.dataset.id,10)).filter(Boolean); }
  async function persistOrder(){
    const ids = getIdsFromDOM();
    try{
      const r = await API.reorder(ids).then(r=>r.json());
      if (r.status !== 'success') throw new Error(r.message||'Reorder failed');
      _ok('Order updated');
      const map = new Map(all.map(x=>[x.id,x])); ids.forEach((id,i)=>{ const row = map.get(id); if(row) row.sort_order = i; });
    }catch(e){ _err(e.message||'Reorder failed'); }
  }

  // ===== Create / Update / Delete =====
  btnAdd.addEventListener('click', ()=>{ resetForm(); currentId=null; markActive(); title.focus(); });
  title.addEventListener('input', ()=>{ if (!slug.value) slug.value = _slugify(title.value); });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    form.classList.add('was-validated');
    if(!title.value.trim()) return;

    try{
      btnSave.disabled = true; btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
      saveStatus.textContent = 'Saving…';

      const payload = buildPayload();
      let json;
      if(qid.value){ json = await API.update(qid.value, payload).then(r=>r.json()); }
      else{ json = await API.create(payload).then(r=>r.json()); }

      if (json.status !== 'success') throw new Error(json.message || 'Save failed');

      _ok(qid.value ? 'Updated' : 'Created');
      saveStatus.textContent = 'Saved';
      await loadList();
      const newId = json.data?.id || qid.value;
      if (newId) select(newId);
    }catch(err){ _err(err.message || 'Save failed'); saveStatus.textContent = 'Error';
    }finally{
      btnSave.disabled = false; btnSave.innerHTML = '<i class="fa fa-save me-2"></i>Save';
      setTimeout(()=> saveStatus.textContent='—', 1000);
    }
  });

  btnDelete.addEventListener('click', async ()=>{
    if(!qid.value) return;
    const res = await Swal.fire({
      title: 'Delete this question?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
      background: _isDark() ? '#0b1526' : '#fff',
      color: _isDark() ? '#e6edf7' : '#111',
    });
    if(!res.isConfirmed) return;

    try{
      const r = await API.delete(qid.value).then(r=>r.json());
      if (r.status !== 'success') throw new Error(r.message||'Delete failed');
      _ok('Deleted'); await loadList(); resetForm(); currentId = null; markActive();
    }catch(e){ _err(e.message||'Delete failed'); }
  });

  function buildPayload(){
    // sync langBlocks with DOM inputs before assembling payload
    langBlocks = Array.from(langsWrap.querySelectorAll('[data-lang]')).map((card,i)=>{
      const idx = parseInt(card.dataset.lang,10);
      const base = langBlocks[idx] || {};
      return {
        language_key: card.querySelector('.lang_language_key').value.trim(),
        runtime_key:  card.querySelector('.lang_runtime_key').value.trim(),
        source_filename: card.querySelector('.lang_source_filename').value.trim(),
        compile_cmd: card.querySelector('.lang_compile_cmd').value.trim(),
        run_cmd:     card.querySelector('.lang_run_cmd').value.trim(),
        stdout_kb_max: _toNum(card.querySelector('.lang_stdout_kb_max').value),
        time_limit_ms:  _toNum(card.querySelector('.lang_time_limit_ms').value),
        memory_limit_kb: _toNum(card.querySelector('.lang_memory_limit_kb').value),
        line_limit:     _toNum(card.querySelector('.lang_line_limit').value),
        byte_limit:     _toNum(card.querySelector('.lang_byte_limit').value),
        max_inputs:     _toNum(card.querySelector('.lang_max_inputs').value),
        max_stdin_tokens: _toNum(card.querySelector('.lang_max_stdin_tokens').value),
        max_args:         _toNum(card.querySelector('.lang_max_args').value),
        allow_label:   card.querySelector('.lang_allow_label').value.trim() || null,
        allow:         (base.allow||[]),
        forbid_regex:  (base.forbid_regex||[]),
        is_enabled:    card.querySelector('.lang_enabled').checked,
        sort_order:    i,
        entry_hint:    card.querySelector('.snip_entry_hint').value.trim(),
        template:      card.querySelector('.snip_template').value,
        is_default:    card.querySelector('.snip_is_default').value === '1'
      };
    });

    // collect tests from DOM
    testRows = Array.from(testsWrap.querySelectorAll('[data-test]')).map((card,i)=>({
      visibility: card.querySelector('.t_visibility').value,
      input: card.querySelector('.t_input').value,
      expected: card.querySelector('.t_expected').value,
      score: _toNum(card.querySelector('.t_score').value) ?? 1,
      is_active: card.querySelector('.t_active').value === '1',
      sort_order: i
    }));

    // split unified langBlocks into backend arrays
    const languages = langBlocks.map(b=>({
      language_key: b.language_key,
      runtime_key: b.runtime_key,
      source_filename: b.source_filename,
      compile_cmd: b.compile_cmd,
      run_cmd: b.run_cmd,
      stdout_kb_max: b.stdout_kb_max,
      time_limit_ms: b.time_limit_ms,
      memory_limit_kb: b.memory_limit_kb,
      line_limit: b.line_limit,
      byte_limit: b.byte_limit,
      max_inputs: b.max_inputs,
      max_stdin_tokens: b.max_stdin_tokens,
      max_args: b.max_args,
      allow_label: b.allow_label,
      allow: b.allow,
      forbid_regex: b.forbid_regex,
      is_enabled: b.is_enabled,
      sort_order: b.sort_order
    }));

    const snippets = langBlocks.map(b=>({
      language_key: b.language_key,
      entry_hint: b.entry_hint,
      template: b.template,
      is_default: b.is_default,
      sort_order: b.sort_order
    }));

    return {
      topic_id: Number(TOPIC_ID),
      module_id: Number(MODULE_ID),

      title: title.value.trim(),
      slug: slug.value.trim() || undefined,
      status: status.value,
      difficulty: difficulty.value,
      sort_order: _toNum(sort_order.value) ?? 0,

      tags,

      description: desc.innerHTML.trim(),
      solution: (solution.innerHTML || '').trim() || null,

      compare_mode: compare_mode.value,
      trim_output: (trim_output.value === '1'),
      whitespace_mode: whitespace_mode.value,
      float_abs_tol: float_abs_tol.value ? Number(float_abs_tol.value) : null,
      float_rel_tol: float_rel_tol.value ? Number(float_rel_tol.value) : null,

      languages,
      snippets,
      tests: testRows
    };
  }

  // ===== Info buttons (SweetAlert) =====
  function attachInfoButtons(){
    document.querySelectorAll('#pane-code .i-btn').forEach(btn=>{
      if (btn._hasInfoHandler) return;
      btn._hasInfoHandler = true;
      btn.addEventListener('click', ()=>{
        const title = btn.getAttribute('data-i-title') || 'Info';
        const text  = btn.getAttribute('data-i-text')  || '';
        Swal.fire({
          title, text, icon: 'info', confirmButtonText: 'OK',
          background: _isDark() ? '#0b1526' : '#fff',
          color: _isDark() ? '#e6edf7' : '#111',
        });
      });
    });
  }

  // simple WYSIWYG commands
  // set default active editor ONCE after elements exist
  activeEditor = desc;
  document.querySelectorAll('#pane-code .ce-text-toolbar [data-cmd]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const cmd = btn.getAttribute('data-cmd');
      let val = null;
      if (cmd === 'formatBlock' || cmd === 'foreColor') {
        if (btn.tagName === 'SELECT') val = btn.value;
        else {
          const colorInput = btn.previousElementSibling;
          val = colorInput && colorInput.type === 'color' ? colorInput.value : null;
        }
      }
      (activeEditor||desc).focus(); document.execCommand(cmd, false, val);
    });
  });
  [desc,solution].forEach(ed=> ed.addEventListener('focus', ()=> activeEditor = ed ));

  // image insert/edit
  document.querySelectorAll('#pane-code .btn-insert-image').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      Swal.fire({
        title: 'Insert Image',
        input: 'url',
        inputLabel: 'Image URL',
        inputValue: 'https://auth-db1635.hstgr.io/themes/pmahomme/img/logo_left.png',
        showCancelButton: true,
        background: _isDark() ? '#0b1526' : '#fff',
        color: _isDark() ? '#e6edf7' : '#111',
      }).then(res=>{
        if(res.isConfirmed && res.value){
          const html = `<img src="${_esc(res.value)}" alt="">`;
          (activeEditor||desc).focus();
          document.execCommand('insertHTML', false, html);
        }
      });
    });
  });

  // Boot
  loadList();
})();

/* =====================================================================================
   SQL TAB
===================================================================================== */
(function(){
  "use strict";
  const TOPIC_ID  = {{ json_encode($topicId) }};
  const MODULE_ID = {{ json_encode($moduleId) }};
  if(!TOPIC_ID || !MODULE_ID){
    document.getElementById('pane-sql').innerHTML = `<div class="p-4 text-danger">Missing topic_id or module_id.</div>`;
    return;
  }

  const DB_KEYS = ['mysql','postgres','mongodb'];
  const API = {
    list:    () => fetch(`/api/sql-questions?topic_id=${TOPIC_ID}&module_id=${MODULE_ID}&per_page=200`, { headers: _hdr() }),
    get:     id => fetch(`/api/sql-questions/${id}`, { headers: _hdr() }),
    create:  payload => fetch('/api/sql-questions', { method:'POST', headers: _hdrJSON(), body: JSON.stringify(payload) }),
    update: (id,payload)=> fetch(`/api/sql-questions/${id}`, { method:'PUT', headers: _hdrJSON(), body: JSON.stringify(payload) }),
    delete:  id => fetch(`/api/sql-questions/${id}`, { method:'DELETE', headers: _hdr() }),
  };

  // DOM
  const listEl = document.getElementById('sq-list'), countEl = document.getElementById('sq-count');
  const searchEl = document.getElementById('sq-search');
  const btnAdd = document.getElementById('sq-btnAdd'), btnRefresh = document.getElementById('sq-btnRefresh');

  const form = document.getElementById('sq-form');
  const sqId = document.getElementById('sq-id');
  const title = document.getElementById('sq-title');
  const slug  = document.getElementById('sq-slug');
  const diff  = document.getElementById('sq-difficulty');
  const sort  = document.getElementById('sq-sort');
  const stat  = document.getElementById('sq-status');
  const desc  = document.getElementById('sq-desc');

  const dWrap = document.getElementById('sq-dialects');
  const tWrap = document.getElementById('sq-tests');

  const addDialectBtn = document.getElementById('sq-addDialect');
  const addTestBtn    = document.getElementById('sq-addTest');
  const saveBtn       = document.getElementById('sq-save');
  const delBtn        = document.getElementById('sq-delete');
  const saveStatus    = document.getElementById('sq-saveStatus');

  // State
  let all=[], view=[], currentId=null;
  let dialects=[], tests=[];

  // List
  async function loadList(){
    listEl.innerHTML='<div class="p-3 text-center text-muted tiny">Loading…</div>';
    try{
      const json=await API.list().then(r=>r.json());
      const rows = Array.isArray(json.data?.data) ? json.data.data
                : Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
      all = rows || []; view=[...all]; renderList(); countEl.textContent=`${all.length} total`;
      if(all.length) select(all[0].id);
    }catch(e){ listEl.innerHTML='<div class="p-3 text-danger tiny">Failed to load</div>'; }
  }

  function renderList(){
    const q = (searchEl.value||'').toLowerCase().trim();
    const rows = !q ? view : view.filter(r=>{
      const s = [r.title,r.slug,r.status,r.difficulty].map(x=>(x||'').toLowerCase()).join(' ');
      return s.includes(q);
    });
    if(!rows.length){ listEl.innerHTML='<div class="p-3 text-center text-muted"><i class="fa-regular fa-folder-open fa-2x mb-2"></i><br>No SQL questions</div>'; return; }
    listEl.innerHTML='';
    rows.forEach(r=>{
      const div=document.createElement('div');
      div.className='q-item'; div.dataset.id=r.id;
      div.innerHTML=`
        <div class="flex-1">
          <div class="q-item-title">${_esc(r.title||'Untitled')}</div>
          <div class="q-item-sub">${_esc(r.slug||'')} • ${_esc(r.difficulty||'')}</div>
        </div>
        <div class="badge ${r.status==='active'?'bg-success':(r.status==='archived'?'bg-secondary':'bg-warning text-dark')}">${_esc(r.status||'active')}</div>
      `;
      div.addEventListener('click', ()=> select(r.id));
      if(r.id===currentId) div.classList.add('active');
      listEl.appendChild(div);
    });
  }
  searchEl.addEventListener('input', _debounce(renderList, 200));

  async function select(id){
    currentId=id; saveStatus.textContent='Loading…';
    try{
      const json=await API.get(id).then(r=>r.json());
      const q=json.data||json;

      sqId.value=q.id||'';
      title.value=q.title||'';
      slug.value=q.slug||'';
      diff.value=q.difficulty||'medium';
      sort.value=q.sort_order ?? 0;
      stat.value=q.status || 'active';
      desc.value=q.description||'';

      const langs = Array.isArray(q.languages)? q.languages : [];
      const snips = Array.isArray(q.snippets)? q.snippets : [];
      const sMap = new Map(snips.map(s=>[s.db_key, s]));
      dialects = (langs||[]).map((L,i)=>({
        db_key: L.db_key||'mysql',
        runtime_key: L.runtime_key||'',
        time_limit_ms: L.time_limit_ms ?? '',
        sort_order: L.sort_order ?? i,
        entry_hint: sMap.get(L.db_key)?.entry_hint || '',
        template:   sMap.get(L.db_key)?.template   || '',
        is_default: !!(sMap.get(L.db_key)?.is_default)
      }));
      snips.forEach(s=>{
        if(!dialects.find(d=>d.db_key===s.db_key)){
          dialects.push({
            db_key: s.db_key, runtime_key:'', time_limit_ms:'',
            sort_order: dialects.length, entry_hint: s.entry_hint||'',
            template: s.template||'', is_default: !!s.is_default
          });
        }
      });
      renderDialects();

      tests=(q.tests||[]).map((t,i)=>({
        db_key: t.db_key || 'mysql',
        schema_sql: t.schema_sql || '',
        seed_data_sql: t.seed_data_sql || '',
        expected: t.expected || '',
        visibility: t.visibility || 'hidden',
        score: t.score ?? 1,
        is_active: t.is_active !== false,
        sort_order: t.sort_order ?? i
      }));
      renderTests();

      document.querySelectorAll('#sq-list .q-item').forEach(n=>n.classList.toggle('active', String(n.dataset.id)===String(currentId)));
      saveStatus.textContent='Loaded'; setTimeout(()=>saveStatus.textContent='—', 800);
    }catch(e){ _err('Failed to load'); saveStatus.textContent='Error'; }
  }

  // Dialects
  function dialectCard(d, i){
    return `
      <div class="dialect-card" data-idx="${i}">
        <div class="head mb-2">
          <strong>Dialect</strong>
          <div class="row-actions">
            <button type="button" class="btn btn-sm btn-outline-danger sq-delDialect">Delete</button>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">db_key</label>
            <select class="form-select d_db_key">
              ${DB_KEYS.map(k=>`<option value="${k}" ${d.db_key===k?'selected':''}>${k}</option>`).join('')}
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">runtime_key</label>
            <input class="form-control d_runtime_key" value="${_esc(d.runtime_key||'')}" placeholder="e.g., postgres:15 / mysql:8">
          </div>
          <div class="col-md-3">
            <label class="form-label">time_limit_ms</label>
            <input type="number" class="form-control d_time_limit_ms" value="${d.time_limit_ms??''}" min="0" placeholder="3000">
          </div>
          <div class="col-md-2">
            <label class="form-label">Default</label>
            <select class="form-select d_is_default">
              <option value="0" ${!d.is_default?'selected':''}>No</option>
              <option value="1" ${d.is_default?'selected':''}>Yes</option>
            </select>
          </div>
        </div>
        <div class="mt-2">
          <label class="form-label">entry_hint</label>
          <input class="form-control d_entry_hint" value="${_esc(d.entry_hint||'')}" placeholder="Write a SELECT to…">
        </div>
        <div class="mt-2">
          <label class="form-label">template (starter query)</label>
          <textarea class="form-control d_template" rows="4" placeholder="-- starter query">${_esc(d.template||'')}</textarea>
        </div>
      </div>
    `;
  }
  function renderDialects(){
    dWrap.innerHTML = dialects.map(dialectCard).join('') || '<div class="text-muted small">No dialects yet.</div>';
    dWrap.querySelectorAll('.sq-delDialect').forEach(btn=>{
      btn.addEventListener('click', e=>{
        const card = e.target.closest('[data-idx]');
        const idx = parseInt(card.dataset.idx,10);
        dialects.splice(idx,1); renderDialects();
      });
    });
  }
  addDialectBtn.addEventListener('click', ()=>{
    dialects.push({db_key:'mysql', runtime_key:'', time_limit_ms:'', entry_hint:'', template:'', is_default: dialects.length===0, sort_order: dialects.length});
    renderDialects();
  });

  // Tests
  function testCard(t,i){
    return `
      <div class="test-row" data-test="${i}">
        <div class="d-flex align-items-center gap-2 mb-2">
          <strong class="me-auto">Test #${i+1}</strong>
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm t_visibility" style="width:130px">
              <option value="sample" ${t.visibility==='sample'?'selected':''}>sample</option>
              <option value="hidden" ${t.visibility!=='sample'?'selected':''}>hidden</option>
            </select>
            <input type="number" class="form-control form-control-sm t_score" style="width:90px" min="0" value="${t.score??1}" placeholder="score">
            <select class="form-select form-select-sm t_active" style="width:110px">
              <option value="1" ${t.is_active!==false?'selected':''}>active</option>
              <option value="0" ${t.is_active===false?'selected':''}>inactive</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger sq-delTest">Delete</button>
          </div>
        </div>
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label">db_key</label>
            <select class="form-select t_db_key">
              ${DB_KEYS.map(k=>`<option value="${k}" ${t.db_key===k?'selected':''}>${k}</option>`).join('')}
            </select>
          </div>
          <div class="col-md-9">
            <label class="form-label">Schema SQL</label>
            <textarea class="form-control t_schema" rows="3" placeholder="CREATE TABLE…">${_esc(t.schema_sql||'')}</textarea>
          </div>
        </div>
        <div class="row g-2 mt-1">
          <div class="col-md-6">
            <label class="form-label">Seed Data SQL</label>
            <textarea class="form-control t_seed" rows="3" placeholder="INSERT INTO…">${_esc(t.seed_data_sql||'')}</textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Expected</label>
            <textarea class="form-control t_expected" rows="3" placeholder='[{"col": "val"}, …] or tabular text'>${_esc(t.expected||'')}</textarea>
          </div>
        </div>
      </div>
    `;
  }
  function renderTests(){
        tWrap.innerHTML = tests.map(testCard).join('') || '<div class="text-muted small">No tests yet.</div>';
        tWrap.querySelectorAll('.sq-delTest').forEach(btn=>{
          btn.addEventListener('click', e=>{
            const row = e.target.closest('[data-test]');
            const i = parseInt(row.dataset.test,10);
            tests.splice(i,1); renderTests();
          });
        });
      }
      addTestBtn.addEventListener('click', ()=>{
        tests.push({db_key:'mysql', schema_sql:'', seed_data_sql:'', expected:'', visibility:'sample', score:1, is_active:true, sort_order:tests.length});
        renderTests();
      });

      // CRUD
      btnAdd.addEventListener('click', ()=>{ reset(); title.focus(); });
      btnRefresh.addEventListener('click', loadList);

      form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        form.classList.add('was-validated');
        if(!title.value.trim()) return;
        try{
          setSaving(true);
          const payload = buildPayload();
          let json;
          if(sqId.value){ json=await API.update(sqId.value,payload).then(r=>r.json()); }
          else{ json=await API.create(payload).then(r=>r.json()); }
          if(json.status==='success'){ _ok('Saved'); await loadList(); const id=json.data?.id || sqId.value; if(id) select(id); }
          else{ throw new Error(json.message||'Save failed'); }
        }catch(err){ _err(err.message||'Save failed'); }
        finally{ setSaving(false); }
      });

      delBtn.addEventListener('click', async ()=>{
        if(!sqId.value) return;
        const res=await Swal.fire({title:'Delete?',text:'Cannot undo',icon:'warning',showCancelButton:true});
        if(!res.isConfirmed) return;
        try{
          const json=await API.delete(sqId.value).then(r=>r.json());
          if(json.status==='success'){ _ok('Deleted'); await loadList(); reset(); }
          else{ throw new Error(json.message||'Delete failed'); }
        }catch{ _err('Delete failed'); }
      });

      // helpers
      function setSaving(is){
        saveBtn.disabled = is;
        saveBtn.innerHTML = is ? '<span class="spinner-border spinner-border-sm me-2"></span>Saving…' : '<i class="fa fa-save me-2"></i>Save';
        saveStatus.textContent = is ? 'Saving…' : '—';
      }
      function reset(){
        form.reset();
        sqId.value=''; title.value=''; slug.value=''; diff.value='medium'; sort.value=0; stat.value='active'; desc.value='';
        dialects=[]; renderDialects();
        tests=[]; renderTests();
        saveStatus.textContent='—';
      }
      title.addEventListener('input', ()=>{ if(!slug.value.trim()) slug.value = _slugify(title.value); });

      function buildPayload(){
        dialects = Array.from(dWrap.querySelectorAll('[data-idx]')).map((card,i)=>({
          db_key: card.querySelector('.d_db_key').value,
          runtime_key: card.querySelector('.d_runtime_key').value.trim(),
          time_limit_ms: _toNum(card.querySelector('.d_time_limit_ms').value),
          entry_hint: card.querySelector('.d_entry_hint').value.trim(),
          template: card.querySelector('.d_template').value,
          is_default: card.querySelector('.d_is_default').value === '1',
          sort_order: i
        }));
        tests = Array.from(tWrap.querySelectorAll('[data-test]')).map((card,i)=>({
          db_key: card.querySelector('.t_db_key').value,
          schema_sql: card.querySelector('.t_schema').value,
          seed_data_sql: card.querySelector('.t_seed').value,
          expected: card.querySelector('.t_expected').value,
          visibility: card.querySelector('.t_visibility').value,
          score: _toNum(card.querySelector('.t_score').value) ?? 1,
          is_active: card.querySelector('.t_active').value === '1',
          sort_order: i
        }));

        const languages = dialects.map(d=>({
          db_key: d.db_key,
          runtime_key: d.runtime_key || null,
          time_limit_ms: d.time_limit_ms ?? null,
          sort_order: d.sort_order
        }));
        const snippets = dialects
          .filter(d => d.template?.trim() || d.entry_hint?.trim() || d.is_default)
          .map(d=>({
            db_key: d.db_key,
            template: d.template || '',
            entry_hint: d.entry_hint || null,
            is_default: !!d.is_default,
            sort_order: d.sort_order
          }));

        return {
          topic_id: Number(TOPIC_ID),
          module_id: Number(MODULE_ID),
          title: title.value.trim(),
          slug: slug.value.trim() || undefined,
          difficulty: diff.value,
          status: stat.value,
          sort_order: _toNum(sort.value) ?? 0,
          description: desc.value,
          languages, snippets, tests
        };
      }

      // boot
      loadList();
    })();
  </script>
@endpush
