{{-- resources/views/modules/codingTest/codingTest.blade.php --}}
<!DOCTYPE html>
<html lang="en" class="theme-light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Coding Test</title>

  <!-- Core CSS -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicons/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <!-- Highlight.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">

  <!-- CodeMirror -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">

  <style>
    :root{ --ct-max-width: 1320px; }

    body{
      background:var(--bg-body);
      color:var(--text-color);
      font-family:var(--font-sans, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
      -webkit-font-smoothing:antialiased;
    }

    /* ===== Top Appbar ===== */
    .ct-appbar{
      position:sticky; top:0; z-index:50;
      height:56px;
      background:var(--surface);
      border-bottom:1px solid var(--line-strong);
      display:flex; align-items:center;
    }
    .ct-appbar-inner{
      width:100%;
      max-width:var(--ct-max-width);
      margin-inline:auto;
      padding-inline:12px;
      display:flex; align-items:center; gap:10px;
    }
    .ct-brand{ display:flex; align-items:center; gap:8px; text-decoration:none; }
    .ct-brand img{ height:24px; }
    .ct-brand span{
      font-family:var(--font-head);
      font-weight:700;
      font-size:.98rem;
      color:var(--ink);
    }
    .ct-page-title{
      font-family:var(--font-head);
      font-weight:600;
      font-size:.95rem;
      color:var(--ink);
      opacity:.9;
    }
    .ct-app-actions{
      margin-left:auto;
      display:flex; align-items:center; gap:8px;
      flex-wrap:wrap;
      justify-content:flex-end;
    }
    .ct-icon-btn{
      width:32px; height:32px;
      border-radius:999px;
      border:1px solid var(--line-strong);
      background:var(--surface);
      display:inline-grid; place-items:center;
      font-size:.8rem;
      cursor:pointer;
    }
    .ct-icon-btn:hover{ background:var(--page-hover); }

    .ct-status-pill{
      height:32px;
      border-radius:999px;
      border:1px solid var(--line-strong);
      background:var(--surface-2);
      padding:0 10px;
      display:inline-flex;
      align-items:center;
      gap:8px;
      font-size:.78rem;
      color:var(--muted-color);
      white-space:nowrap;
    }
    .ct-status-pill strong{ color:var(--ink); font-weight:800; }
    .ct-status-pill i{ opacity:.9; }

    .ct-timer-pill[data-state="ok"]{
      border-color:rgba(34,197,94,.55);
      color:#22c55e;
      background:rgba(34,197,94,.08);
    }
    .ct-timer-pill[data-state="warn"]{
      border-color:rgba(245,158,11,.60);
      color:#f59e0b;
      background:rgba(245,158,11,.08);
    }
    .ct-timer-pill[data-state="danger"]{
      border-color:rgba(251,113,133,.65);
      color:#fb7185;
      background:rgba(251,113,133,.08);
    }

    /* ===== Main Container ===== */
    .ct-shell{
      max-width:var(--ct-max-width);
      margin:12px auto 24px;
      padding-inline:12px;
    }

    .ct-header-row{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      flex-wrap:wrap;
      gap:10px;
      margin-bottom:12px;
    }
    .ct-title-row{
      display:flex;
      align-items:flex-start;
      gap:10px;
      font-family:var(--font-head);
    }
    .ct-title-row i{
      margin-top:2px;
      color:var(--accent-color);
    }
    .ct-title-main{
      font-size:1.05rem;
      font-weight:700;
      color:var(--ink);
      line-height:1.2;
    }
    .ct-sub-muted{
      font-size:.82rem;
      color:var(--muted-color);
      margin-top:2px;
      max-width:880px;
    }

    .ct-badge-row{
      display:flex;
      flex-wrap:wrap;
      gap:6px;
      justify-content:flex-end;
      align-items:center;
    }

    .ct-pill{
      font-size:.72rem;
      padding:3px 8px;
      border-radius:999px;
      border:1px solid var(--line-soft);
      background:var(--surface-2);
      display:inline-flex;
      align-items:center;
      gap:6px;
      color:var(--muted-color);
      white-space:nowrap;
    }
    .ct-pill i{ font-size:.72rem; }

    /* ===== Layout ===== */
    .ct-grid{
      display:grid;
      grid-template-columns:360px minmax(0,1fr);
      gap:16px;
      align-items:start;
    }
    @media (max-width: 991px){
      .ct-grid{ grid-template-columns:1fr; }
    }

    body.ct-q-collapsed .ct-grid{ grid-template-columns:1fr; }
    body.ct-q-collapsed .ct-question-panel{ display:none; }

    .ct-panel{
      background:var(--surface);
      border-radius:16px;
      border:1px solid var(--line-strong);
      box-shadow:var(--shadow-2);
      padding:14px 16px;
    }

    /* ===== Question Panel ===== */
    .ct-question-panel{
      position:sticky;
      top:68px;
      max-height:calc(100vh - 80px);
      overflow:auto;
    }
    @media (max-width: 991px){
      .ct-question-panel{
        position:relative;
        top:auto;
        max-height:none;
      }
    }
    .ct-q-header{
      border-bottom:1px solid var(--line-soft);
      padding-bottom:8px;
      margin-bottom:8px;
    }
    .ct-q-title{
      font-size:.98rem;
      font-weight:700;
      margin-bottom:4px;
      color:var(--ink);
    }
    .ct-q-meta{
      display:flex;
      gap:6px;
      flex-wrap:wrap;
    }
    .ct-chip{
      font-size:.72rem;
      padding:3px 8px;
      border-radius:999px;
      background:color-mix(in oklab, var(--accent-color) 10%, transparent);
      border:1px solid color-mix(in oklab, var(--accent-color) 30%, transparent);
      color:var(--accent-color);
    }
    .ct-q-body{
      font-size:.88rem;
      line-height:1.7;
    }
    .ct-q-body pre{
      background:#020617;
      color:#e5e7eb;
      padding:10px 12px;
      border-radius:10px;
      overflow:auto;
      font-size:.8rem;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
      border:1px solid #0b1220;
    }
    .ct-q-body code{
      background:rgba(15,23,42,.06);
      border-radius:4px;
      padding:2px 4px;
      font-size:.85em;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    }

    /* ===== Editor Panel ===== */
    .ct-editor-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      border-bottom:1px solid var(--line-soft);
      padding-bottom:8px;
      margin-bottom:10px;
      flex-wrap:wrap;
    }
    .ct-lang-box{
      display:flex;
      align-items:center;
      gap:8px;
      flex-wrap:wrap;
    }
    .ct-lang-label{
      font-size:.8rem;
      color:var(--muted-color);
      font-weight:600;
    }
    .ct-lang-select-wrap select{
      height:32px;
      font-size:.82rem;
      border-radius:999px;
      padding-inline:10px 28px;
      color:var(--ink);
      background:var(--surface-2);
      border-color:var(--line-strong);
    }
    html.theme-dark .ct-lang-select-wrap select{
      color:var(--text-color);
      background:#0b1220;
      border-color:var(--line-strong);
    }
    .ct-lang-meta{
      font-size:.76rem;
      color:var(--muted-color);
      line-height:1;
      display:inline-flex;
      align-items:center;
      gap:8px;
    }
    .ct-lang-ext{
      display:inline-flex;
      align-items:center;
      gap:6px;
      font-size:.74rem;
      padding:2px 8px;
      border-radius:999px;
      border:1px solid var(--line-soft);
      background:var(--surface-2);
      color:var(--muted-color);
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    }

    .ct-editor-actions-top{
      display:flex;
      align-items:center;
      gap:6px;
      flex-wrap:wrap;
    }
    .btn-chip{
      border-radius:999px;
      font-size:.78rem;
      padding:5px 10px;
      border:1px solid var(--line-soft);
      background:var(--surface-2);
      display:inline-flex;
      align-items:center;
      gap:6px;
      cursor:pointer;
      user-select:none;
    }
    .btn-chip i{ font-size:.75rem; }
    .btn-chip:hover{ background:var(--page-hover); }
    .btn-chip:disabled{ opacity:.6; cursor:not-allowed; }
    .btn-chip-run{
      border-color:rgba(34,197,94,.55);
      color:#22c55e;
      background:rgba(34,197,94,.08);
    }
    .btn-chip-run:hover{ background:rgba(34,197,94,.13); }

    /* ===== Code editor ===== */
    .ct-editor-shell{
      border-radius:12px;
      border:1px solid var(--line-strong);
      overflow:hidden;
      background:#020617;
    }
    .CodeMirror{
      height:410px;
      min-height:410px;
      background:#020617;
      color:#e5e7eb;
      font-size:13px;
      line-height:1.55;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    }
    .CodeMirror-gutters{
      background:#020617;
      border-right:1px solid #111827;
    }
    .CodeMirror-linenumber{
      color:#6b7280;
      font-size:11px;
      padding:0 6px;
    }

    /* ===== Footer actions ===== */
    .ct-editor-footer{
      margin-top:10px;
      padding-top:8px;
      border-top:1px solid var(--line-soft);
      display:flex;
      align-items:center;
      gap:8px;
      flex-wrap:wrap;
    }
    .ct-shortcut-hint{
      font-size:.74rem;
      color:var(--muted-color);
      display:flex;
      align-items:center;
      gap:4px;
      margin-right:auto;
      flex-wrap:wrap;
    }
    .ct-kbd{
      border-radius:6px;
      border:1px solid var(--line-soft);
      padding:2px 7px;
      background:var(--surface-2);
      font-size:.7rem;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    }
    .btn-pill-sm{
      border-radius:999px;
      font-size:.82rem;
      padding:7px 14px;
      display:inline-flex;
      align-items:center;
      gap:6px;
    }
    .btn-pill-sm i{ font-size:.82rem; }

    .btn-submit-main{
      background:var(--accent-color);
      border-color:var(--accent-color);
      color:#fff;
    }
    .btn-submit-main:hover{
      filter:brightness(.96);
      color:#fff;
    }
    .btn-submit-main:disabled{
      opacity:.6;
      cursor:not-allowed;
    }

    /* ===== Loading ===== */
    .ct-loading{
      display:flex;
      justify-content:center;
      align-items:center;
      min-height:280px;
      flex-direction:column;
      gap:10px;
    }
    .ct-spinner{
      width:36px;
      height:36px;
      border-radius:999px;
      border:3px solid rgba(15,23,42,.15);
      border-top-color:var(--accent-color);
      animation:ct-spin 1s linear infinite;
    }
    @keyframes ct-spin{ to{transform:rotate(360deg);} }
    .ct-loading small{ color:var(--muted-color); }

    /* ===== Locked / exhausted ===== */
    .ct-lock{
      max-width:760px;
      margin:22px auto 0;
      background:var(--surface);
      border:1px solid var(--line-strong);
      border-radius:16px;
      box-shadow:var(--shadow-2);
      padding:18px;
    }
    .ct-lock h5{
      margin:0 0 8px;
      font-family:var(--font-head);
      color:var(--ink);
      font-weight:800;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .ct-lock p{ margin:0; color:var(--muted-color); font-size:.92rem; line-height:1.6; }
    .ct-lock .ct-lock-actions{ margin-top:12px; display:flex; gap:8px; flex-wrap:wrap; }

    /* ===== Terminal Results ===== */
    .ct-results{ margin-top:16px; display:none; }
    .ct-results-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      margin-bottom:6px;
      flex-wrap:wrap;
    }
    .ct-results-header h6{
      margin:0;
      font-size:.82rem;
      display:flex;
      align-items:center;
      gap:6px;
      color:var(--muted-color);
      font-weight:700;
    }
    .ct-results-header h6 i{ color:#22c55e; }
    .ct-results-summary{
      font-size:.76rem;
      color:var(--muted-color);
      white-space:nowrap;
    }
    .ct-results-summary span{ font-weight:700; }
    .ct-results-summary #passedCount{color:#16a34a;}
    .ct-results-summary #failedCount{color:#f97316;}
    .ct-results-summary #allPassBadge{
      margin-left:8px;
      padding:2px 8px;
      border-radius:999px;
      border:1px solid var(--line-soft);
      background:var(--surface-2);
      font-size:.72rem;
      display:inline-block;
    }

    .ct-terminal{
      background:#020617;
      border-radius:12px;
      border:1px solid #0b1220;
      overflow:hidden;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
      font-size:.78rem;
    }
    .ct-term-bar{
      display:flex;
      align-items:center;
      gap:6px;
      padding:7px 10px;
      border-bottom:1px solid #0b1220;
      background:#020617;
    }
    .ct-term-dot{ width:9px; height:9px; border-radius:999px; }
    .ct-term-dot.red{background:#f97373;}
    .ct-term-dot.amber{background:#fbbf24;}
    .ct-term-dot.green{background:#22c55e;}
    .ct-term-title{ font-size:.72rem; color:#9ca3af; margin-left:4px; }

    .ct-term-body{
      max-height:360px;
      overflow:auto;
      padding:10px 12px 12px;
      color:#e5e7eb;
    }
    .ct-line{ white-space:pre-wrap; word-wrap:break-word; }
    .ct-line + .ct-line{ margin-top:10px; }
    .ct-line-prefix{ color:#60a5fa; }
    .ct-line-status-pass{ color:#4ade80; }
    .ct-line-status-fail{ color:#fb7185; }
    .ct-line-label{ color:#a5b4fc; }
    .ct-line-error{ color:#fb7185; }
    .ct-dim{ color:#9ca3af; }

    .ct-term-body::-webkit-scrollbar{ width:6px; }
    .ct-term-body::-webkit-scrollbar-track{ background:#020617; }
    .ct-term-body::-webkit-scrollbar-thumb{ background:#374151; border-radius:3px; }
  </style>
</head>

<body>
  <!-- Appbar -->
  <header class="ct-appbar">
    <div class="ct-appbar-inner">
      <a href="{{ url('/') }}" class="ct-brand" id="brandLink">
        <img src="{{ asset('assets/media/images/web/logo.png') }}" alt="W3Techiez">
        <span>W3Techiez</span>
      </a>

      <span class="ct-page-title">Coding Test</span>

      <div class="ct-app-actions">
        <div class="ct-status-pill" id="attemptPill" style="display:none;" title="Attempt status">
          <i class="fa-solid fa-hashtag"></i>
          <span>Attempt</span>
          <strong id="attemptNoLabel">1</strong>
          <span class="ct-dim">/</span>
          <strong id="attemptAllowedLabel">1</strong>
          <span class="ct-dim">·</span>
          <span>Left</span>
          <strong id="attemptLeftLabel">0</strong>
        </div>

        <div class="ct-status-pill ct-timer-pill" id="timerPill" style="display:none;" data-state="ok" title="Time left">
          <i class="fa-regular fa-clock"></i>
          <strong id="timerLabel">--:--</strong>
        </div>

        <button type="button" class="ct-icon-btn" id="toggleQuestionBtn" title="Toggle question panel">
          <i class="fa-solid fa-columns"></i>
        </button>

        <button type="button" class="ct-icon-btn" id="themeToggleBtn" title="Toggle theme">
          <i class="fa-regular fa-moon"></i>
        </button>

        <button type="button" class="ct-icon-btn" id="helpBtn" title="Help">
          <i class="fa-regular fa-circle-question"></i>
        </button>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="ct-shell">
    <!-- Top heading row -->
    <div class="ct-header-row">
      <div class="ct-title-row">
        <i class="fa-solid fa-code"></i>
        <div>
          <div class="ct-title-main" id="testTitle">Coding Test</div>
          <div class="ct-sub-muted" id="testSubtitle">Solve the coding question and run against sample test cases.</div>
        </div>
      </div>

      <div class="ct-badge-row" id="badgeRow">
        <div class="ct-pill"><i class="fa-regular fa-circle-check"></i> Auto-graded</div>
        <div class="ct-pill"><i class="fa-regular fa-keyboard"></i> Run: <span style="font-family:monospace;">Ctrl/Cmd + Enter</span></div>
        <div class="ct-pill"><i class="fa-regular fa-floppy-disk"></i> Save: <span style="font-family:monospace;">Ctrl/Cmd + S</span></div>
      </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="ct-loading">
      <div class="ct-spinner"></div>
      <small id="loadingText">Loading question…</small>
    </div>

    <!-- Locked / attempts exhausted -->
    <div id="lockedScreen" class="ct-lock" style="display:none;">
      <h5><i class="fa-solid fa-lock" style="color:#fb7185;"></i> Exam not available</h5>
      <p id="lockedMessage">You have no attempts left for this coding question.</p>
      <div class="ct-lock-actions">
        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
          <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <button type="button" class="btn btn-sm btn-primary rounded-pill" id="retryStartBtn" style="display:none;">
          <i class="fa-solid fa-rotate-right me-1"></i> Retry
        </button>
      </div>
    </div>

    <!-- Main Layout -->
    <div id="mainContent" style="display:none;">
      <div class="ct-grid">
        <!-- Question Panel -->
        <aside class="ct-panel ct-question-panel" id="questionPanel">
          <div class="ct-q-header">
            <div class="ct-q-title" id="questionTitle">Loading...</div>
            <div class="ct-q-meta">
              <span class="ct-chip" id="questionDifficulty">Medium</span>
              <span class="ct-chip" id="questionModeChip">Coding</span>
              <span class="ct-chip" id="batchChip" style="display:none;">Batch</span>
            </div>
          </div>
          <article class="ct-q-body" id="questionDescription">Loading question description...</article>
        </aside>

        <!-- Editor & Results Panel -->
        <section class="ct-panel">
          <!-- Editor Header -->
          <div class="ct-editor-header">
            <div class="ct-lang-box">
              <span class="ct-lang-label">Language</span>

              <div class="ct-lang-select-wrap">
                <select id="languageSelect" class="form-select form-select-sm"></select>
              </div>

              <span class="ct-lang-meta">
                <span class="ct-lang-ext" id="languageExt">.txt</span>
                <span id="languageMeta"></span>
                <span class="ct-dim" id="saveStateText" style="margin-left:6px;"></span>
              </span>
            </div>

            <div class="ct-editor-actions-top">
              <button id="runBtn" type="button" class="btn btn-sm btn-chip btn-chip-run">
                <i class="fa-solid fa-play"></i> Run
              </button>
              <button id="saveBtn" type="button" class="btn btn-sm btn-chip">
                <i class="fa-regular fa-floppy-disk"></i> Save
              </button>
              <button id="resetBtn" type="button" class="btn btn-sm btn-chip">
                <i class="fa-solid fa-rotate-right"></i> Reset
              </button>
            </div>
          </div>

          <!-- Editor (CodeMirror) -->
          <div class="ct-editor-shell">
            <textarea id="codeEditor" spellcheck="false"></textarea>
          </div>

          <!-- Footer Actions -->
          <div class="ct-editor-footer">
            <div class="ct-shortcut-hint">
              <span class="ct-kbd">Ctrl</span><span>+</span><span class="ct-kbd">Enter</span>
              <span>Run</span>
              <span class="ct-dim">·</span>
              <span class="ct-kbd">Ctrl</span><span>+</span><span class="ct-kbd">S</span>
              <span>Save</span>
            </div>

            <button id="submitBtn" type="button" class="btn btn-primary btn-pill-sm btn-submit-main">
              <i class="fa-regular fa-paper-plane"></i> Submit
            </button>
          </div>

          <!-- Results (Terminal) -->
          <div class="ct-results" id="resultsPanel">
            <div class="ct-results-header">
              <h6><i class="fa-solid fa-terminal"></i> Execution Output</h6>
              <div class="ct-results-summary">
                <span id="passedCount">0</span> passed ·
                <span id="failedCount">0</span> failed ·
                <span id="totalCount">0</span> total
                <span id="allPassBadge" style="display:none;"></span>
              </div>
            </div>

            <div class="ct-terminal">
              <div class="ct-term-bar">
                <span class="ct-term-dot red"></span>
                <span class="ct-term-dot amber"></span>
                <span class="ct-term-dot green"></span>
                <span class="ct-term-title">judge@w3techiez:~/sandbox</span>
              </div>
              <div class="ct-term-body" id="testResultsContainer"></div>
            </div>
          </div>

        </section>
      </div>
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- CodeMirror core + modes -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/python/python.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/clike/clike.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/php/php.min.js"></script>

  <script>
    ;(() => {
      /* =========================
        Context (URL)
        - /coding-test/{identifier}
        - optional: ?batch_uuid=xxxx
      ========================== */
      const pathParts = window.location.pathname.split('/').filter(Boolean);
      const questionIdentifier = pathParts[pathParts.length - 1]; // uuid/slug/id

      const qs = new URLSearchParams(window.location.search);
      const batchUuid = (qs.get('batch_uuid') || qs.get('batch') || qs.get('quiz_batch_uuid') || '').trim();

      /* storage keys */
      const attemptKey      = `w3t_attempt_${questionIdentifier}_${batchUuid || 'solo'}`;
      const attemptMetaKey  = `w3t_attempt_meta_${questionIdentifier}_${batchUuid || 'solo'}`;

      /* =========================
        State
      ========================== */
      let questionData = null;
      let currentLanguage = 'python';
      let originalCode = {};  // language_key => starter code
      let editor = null;

      let isDirty = false;
      let suppressDirty = false;

      // attempt / timer
      let attemptUuid = (qs.get('attempt_uuid') || sessionStorage.getItem(attemptKey) || '').trim();
      let attemptNo = null;
      let attemptsAllowed = null;
      let attemptsLeft = null;

      let expiresAt = null; // Date
      let serverSkewMs = 0; // server_now - client_now
      let timerInterval = null;

      let isSubmitting = false;
      let isSubmittedFinal = false;
      let autoSubmitted = false;

      /* =========================
        DOM
      ========================== */
      const loadingState = document.getElementById('loadingState');
      const loadingText  = document.getElementById('loadingText');
      const mainContent  = document.getElementById('mainContent');

      const lockedScreen = document.getElementById('lockedScreen');
      const lockedMessage= document.getElementById('lockedMessage');
      const retryStartBtn= document.getElementById('retryStartBtn');

      const testTitle = document.getElementById('testTitle');
      const testSubtitle = document.getElementById('testSubtitle');
      const badgeRow = document.getElementById('badgeRow');

      const questionTitle = document.getElementById('questionTitle');
      const questionDifficulty = document.getElementById('questionDifficulty');
      const questionModeChip = document.getElementById('questionModeChip');
      const batchChip = document.getElementById('batchChip');
      const questionDescription = document.getElementById('questionDescription');

      const languageSelect = document.getElementById('languageSelect');
      const languageMeta = document.getElementById('languageMeta');
      const languageExt = document.getElementById('languageExt');
      const saveStateText = document.getElementById('saveStateText');
      const codeEditorTextarea = document.getElementById('codeEditor');

      const runBtn = document.getElementById('runBtn');
      const submitBtn = document.getElementById('submitBtn');
      const resetBtn = document.getElementById('resetBtn');
      const saveBtn = document.getElementById('saveBtn');

      const resultsPanel = document.getElementById('resultsPanel');
      const testResultsContainer = document.getElementById('testResultsContainer');
      const passedCount = document.getElementById('passedCount');
      const failedCount = document.getElementById('failedCount');
      const totalCount = document.getElementById('totalCount');
      const allPassBadge = document.getElementById('allPassBadge');

      const helpBtn = document.getElementById('helpBtn');
      const themeToggleBtn = document.getElementById('themeToggleBtn');
      const toggleQuestionBtn = document.getElementById('toggleQuestionBtn');
      const brandLink = document.getElementById('brandLink');

      const attemptPill = document.getElementById('attemptPill');
      const attemptNoLabel = document.getElementById('attemptNoLabel');
      const attemptAllowedLabel = document.getElementById('attemptAllowedLabel');
      const attemptLeftLabel = document.getElementById('attemptLeftLabel');

      const timerPill = document.getElementById('timerPill');
      const timerLabel = document.getElementById('timerLabel');

      /* =========================
        Helpers
      ========================== */
      function getAuthToken(){
        return sessionStorage.getItem('token') || localStorage.getItem('token') || '';
      }

      function authHeaders(extra = {}){
        const token = getAuthToken();
        const headers = Object.assign({
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }, extra);

        if (token) headers['Authorization'] = `Bearer ${token}`;
        return headers;
      }

      async function fetchJson(url, options = {}){
        const res = await fetch(url, options);
        let data = null;
        try { data = await res.json(); } catch (e) { /* ignore */ }

        if (!res.ok) {
          const msg = (data && (data.message || data.error)) ? (data.message || data.error) : `Request failed (${res.status})`;
          const err = new Error(msg);
          err.status = res.status;
          err.payload = data;
          throw err;
        }
        return data;
      }

      function showError(message){
        Swal.fire({ title:'Error', text: message, icon:'error', confirmButtonText:'OK' });
      }

      function showLocked(message, showRetry = false){
        loadingState.style.display = 'none';
        mainContent.style.display = 'none';
        lockedScreen.style.display = 'block';
        lockedMessage.textContent = message || 'Exam not available.';
        retryStartBtn.style.display = showRetry ? 'inline-flex' : 'none';
      }

      function escapeHtml(text){
        const div = document.createElement('div');
        div.textContent = (text ?? '');
        return div.innerHTML;
      }

      function storageKey(lang){
        return `w3t_code_${questionIdentifier}_${batchUuid || 'solo'}_${lang}`;
      }

      function setBusy(btn, busy, htmlIdle){
        if (!btn) return;
        btn.disabled = !!busy;
        if (busy) {
          btn.dataset._old = btn.innerHTML;
          btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Please wait';
        } else {
          btn.innerHTML = htmlIdle || (btn.dataset._old || btn.innerHTML);
        }
      }

      function setButtonsEnabled(enabled){
        const dis = !enabled;
        runBtn.disabled = dis;
        saveBtn.disabled = dis;
        resetBtn.disabled = dis;
        submitBtn.disabled = dis;
        languageSelect.disabled = dis;
        if (editor) editor.setOption('readOnly', dis ? 'nocursor' : false);
      }

      function getCode(){
        return editor ? editor.getValue() : (codeEditorTextarea.value || '');
      }

      function markDirty(flag){
        isDirty = !!flag;
        saveStateText.textContent = isDirty ? 'Unsaved changes' : '';
      }

      function diffLabel(diff){
        const d = (diff || 'medium').toLowerCase();
        return d.charAt(0).toUpperCase() + d.slice(1);
      }

      function normalizeLangKey(lang){
        return (lang || '').toString().trim().toLowerCase();
      }

      function getCodeMirrorMode(lang){
        const key = normalizeLangKey(lang);

        if (key === 'python' || key === 'py') return 'python';
        if (key === 'js' || key === 'javascript' || key === 'node') return 'javascript';

        if (key === 'java') return 'text/x-java';
        if (key === 'c') return 'text/x-csrc';
        if (key === 'cpp' || key === 'c++') return 'text/x-c++src';
        if (key === 'csharp' || key === 'c#') return 'text/x-csharp';

        if (key === 'php') return 'application/x-httpd-php';

        return 'text/plain';
      }

      function getLangExt(lang){
        const key = normalizeLangKey(lang);
        if (key === 'python' || key === 'py') return '.py';
        if (key === 'java') return '.java';
        if (key === 'javascript' || key === 'js' || key === 'node') return '.js';
        if (key === 'cpp' || key === 'c++') return '.cpp';
        if (key === 'c') return '.c';
        if (key === 'csharp' || key === 'c#') return '.cs';
        if (key === 'php') return '.php';
        return '.txt';
      }

      function updateLanguageMeta(){
        const key = normalizeLangKey(currentLanguage);
        languageMeta.textContent = key ? key.toUpperCase() : '';
        languageExt.textContent = getLangExt(key);
      }

      function setCodeProgrammatically(text){
        const v = text || '';
        suppressDirty = true;
        if (editor){
          editor.setValue(v);
          editor.refresh();
          editor.focus();
        } else {
          codeEditorTextarea.value = v;
        }
        suppressDirty = false;
        markDirty(false);
      }

      function saveCodeLocally(showToast = true){
        try{
          localStorage.setItem(storageKey(currentLanguage), getCode());
          markDirty(false);
          if (showToast) {
            Swal.fire({
              toast:true, position:'bottom-end', timer:1400, showConfirmButton:false,
              icon:'success', title:'Saved locally'
            });
          }
        }catch(e){
          console.warn('Unable to save locally', e);
        }
      }

      function maskTestDetails(test){
        const isHidden = (test.visibility || '').toLowerCase() === 'hidden';
        if (!isHidden) return { showDetails:true };
        return { showDetails:false, summary: 'Hidden test case (details not shown).' };
      }

      function renderResults(results, allPass){
        const list = Array.isArray(results) ? results : [];
        const passed = list.filter(r => !!r.pass).length;
        const total = list.length;
        const failed = total - passed;

        passedCount.textContent = passed;
        failedCount.textContent = failed;
        totalCount.textContent = total;

        allPassBadge.style.display = 'inline-block';
        allPassBadge.textContent = allPass ? 'ALL PASSED' : 'NOT PASSED';
        allPassBadge.style.borderColor = allPass ? 'rgba(34,197,94,.6)' : 'rgba(251,113,133,.6)';
        allPassBadge.style.color = allPass ? '#16a34a' : '#fb7185';
        allPassBadge.style.background = allPass ? 'rgba(34,197,94,.10)' : 'rgba(251,113,133,.10)';

        testResultsContainer.innerHTML = '';

        if (!total){
          testResultsContainer.innerHTML = '<div class="ct-line"><span class="ct-line-prefix">$</span> no test cases returned.</div>';
        } else {
          list.forEach((t, idx) => {
            const statusClass = t.pass ? 'ct-line-status-pass' : 'ct-line-status-fail';
            const statusLabel = t.pass ? 'PASSED' : 'FAILED';

            const runtime = t.runtime ? escapeHtml(t.runtime) : '';
            const output  = escapeHtml(t.output ?? '');
            const input   = escapeHtml(t.input ?? '');
            const expected= escapeHtml(t.expected ?? '');

            const policy = maskTestDetails(t);

            const block = document.createElement('div');
            block.className = 'ct-line';

            if (!policy.showDetails) {
              block.innerHTML = `
                <div>
                  <span class="ct-line-prefix">$</span>
                  test <span>#${idx + 1}</span>
                  <span class="${statusClass}">[${statusLabel}]</span>
                  <span class="ct-dim">— ${escapeHtml(policy.summary)}</span>
                </div>
                ${runtime ? `
                  <div style="margin-top:6px;"><span class="ct-line-label">&gt; runtime</span></div>
                  <div class="ct-line-error">${runtime}</div>
                ` : (t.pass ? '' : `
                  <div class="ct-dim" style="margin-top:6px;">(Tip: Check edge cases.)</div>
                `)}
              `;
            } else {
              block.innerHTML = `
                <div>
                  <span class="ct-line-prefix">$</span>
                  test <span>#${idx + 1}</span>
                  <span class="${statusClass}">[${statusLabel}]</span>
                </div>

                <div style="margin-top:8px;"><span class="ct-line-label">&gt; input</span></div>
                <div>${input || '(none)'}</div>

                <div style="margin-top:8px;"><span class="ct-line-label">&gt; expected</span></div>
                <div>${expected || '(none)'}</div>

                <div style="margin-top:8px;"><span class="ct-line-label">&gt; output</span></div>
                <div>${output || '(none)'}</div>

                ${runtime ? `
                  <div style="margin-top:8px;"><span class="ct-line-label">&gt; runtime</span></div>
                  <div class="ct-line-error">${runtime}</div>
                ` : ''}
              `;
            }

            testResultsContainer.appendChild(block);
          });
        }

        resultsPanel.style.display = 'block';
        resultsPanel.scrollIntoView({ behavior:'smooth', block:'start' });
      }

      function setTheme(theme){
        document.documentElement.classList.remove('theme-light','theme-dark');
        document.documentElement.classList.add(theme);
        localStorage.setItem('w3t_theme', theme);
        themeToggleBtn.innerHTML = (theme === 'theme-dark')
          ? '<i class="fa-regular fa-sun"></i>'
          : '<i class="fa-regular fa-moon"></i>';
      }

      function parseServerDate(s){
        if (!s) return null;
        const str = String(s).trim();
        let d = null;
        if (str.includes('T')) d = new Date(str);
        else d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return null;
        return d;
      }

      function formatHMS(sec){
        const s = Math.max(0, Math.floor(sec));
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const ss = s % 60;
        if (h > 0) return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(ss).padStart(2,'0')}`;
        return `${String(m).padStart(2,'0')}:${String(ss).padStart(2,'0')}`;
      }

      function stopTimer(){
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = null;
      }

      function startTimerFromServer({ expires_at, remaining_seconds, server_now }){
        // no time limit
        if (!expires_at && (remaining_seconds == null || remaining_seconds <= 0)) {
          timerPill.style.display = 'none';
          stopTimer();
          return;
        }

        // compute skew if server_now present
        if (server_now) {
          const sn = parseServerDate(server_now);
          if (sn) serverSkewMs = sn.getTime() - Date.now();
        }

        if (remaining_seconds != null && remaining_seconds > 0) {
          // build expiresAt from remaining_seconds and skew
          const nowSkewed = Date.now() + serverSkewMs;
          expiresAt = new Date(nowSkewed + (remaining_seconds * 1000));
        } else if (expires_at) {
          expiresAt = parseServerDate(expires_at);
        }

        if (!expiresAt) return;

        timerPill.style.display = 'inline-flex';
        stopTimer();

        const tick = () => {
          const nowSkewed = Date.now() + serverSkewMs;
          const leftSec = Math.floor((expiresAt.getTime() - nowSkewed) / 1000);

          timerLabel.textContent = formatHMS(leftSec);

          if (leftSec <= 10) timerPill.dataset.state = 'danger';
          else if (leftSec <= 60) timerPill.dataset.state = 'warn';
          else timerPill.dataset.state = 'ok';

          if (leftSec <= 0) {
            stopTimer();
            handleTimeUp();
          }
        };

        tick();
        timerInterval = setInterval(tick, 1000);
      }

      async function handleTimeUp(){
        if (isSubmittedFinal || isSubmitting || autoSubmitted) return;

        autoSubmitted = true;
        setButtonsEnabled(false);

        await Swal.fire({
          title: 'Time is up',
          text: 'Submitting your solution automatically.',
          icon: 'warning',
          confirmButtonText: 'OK'
        });

        await submitCode({ auto: true });
      }

      function setAttemptPills(){
        if (attemptNo != null && attemptsAllowed != null){
          attemptNoLabel.textContent = attemptNo;
          attemptAllowedLabel.textContent = attemptsAllowed;
          attemptLeftLabel.textContent = (attemptsLeft != null) ? attemptsLeft : 0;
          attemptPill.style.display = 'inline-flex';
        }
      }

      function normalizeStartPayload(res){
        // supports:
        // {status:'success', data:{...}} OR {status:'success', ...}
        const d = (res && typeof res === 'object' && res.data && typeof res.data === 'object') ? res.data : res;

        return {
          attempt_uuid: d?.attempt_uuid || d?.attemptUuid || null,
          attempt_no: d?.attempt_no ?? d?.attemptNo ?? null,

          attempts_allowed: d?.attempts_allowed ?? d?.attempt_allowed ?? d?.attemptsAllowed ?? null,
          attempts_used: d?.attempts_used ?? d?.attemptsUsed ?? null,
          attempts_remaining: d?.attempts_remaining ?? d?.attempts_left ?? d?.attemptsLeft ?? null,

          expires_at: d?.expires_at ?? d?.expiresAt ?? null,
          remaining_seconds: d?.remaining_seconds ?? null,
          time_limit_seconds: d?.time_limit_seconds ?? d?.time_limit_sec ?? d?.timeLimitSec ?? null,

          server_now: d?.server_now ?? null,
          started_at: d?.started_at ?? null,
        };
      }

      async function postJson(url, body){
        return fetchJson(url, {
          method:'POST',
          headers: authHeaders(),
          body: JSON.stringify(body)
        });
      }

      /* =========================
        Init
      ========================== */
      document.addEventListener('DOMContentLoaded', async () => {
        const savedTheme = localStorage.getItem('w3t_theme') ||
          (document.documentElement.classList.contains('theme-dark') ? 'theme-dark' : 'theme-light');
        setTheme(savedTheme);

        // brand link: prefer back to referrer (same-origin) if available
        try{
          if (document.referrer && new URL(document.referrer).origin === window.location.origin){
            brandLink.href = document.referrer;
          }
        }catch(e){}

        // batch badge
        if (batchUuid){
          batchChip.style.display = 'inline-flex';
          batchChip.textContent = 'Batch';
          const pill = document.createElement('div');
          pill.className = 'ct-pill';
          pill.innerHTML = `<i class="fa-solid fa-layer-group"></i> Batch mode`;
          badgeRow.appendChild(pill);
          questionModeChip.textContent = 'Batch';
        }

        helpBtn.addEventListener('click', () => {
          Swal.fire({
            title: 'How it works',
            html: `
              <div style="text-align:left; font-size:.92rem; line-height:1.6;">
                <div><b>Run</b> checks your code against sample tests.</div>
                <div style="margin-top:8px;"><b>Submit</b> evaluates tests and stores your attempt.</div>
                <div style="margin-top:8px;"><b>Timer</b> is enforced by server; when time ends, auto-submit happens.</div>
                <div style="margin-top:8px;"><b>Save</b> stores code locally in your browser.</div>
              </div>
            `,
            icon: 'info',
            confirmButtonText: 'OK'
          });
        });

        themeToggleBtn.addEventListener('click', () => {
          const isDark = document.documentElement.classList.contains('theme-dark');
          setTheme(isDark ? 'theme-light' : 'theme-dark');
        });

        toggleQuestionBtn.addEventListener('click', () => {
          document.body.classList.toggle('ct-q-collapsed');
        });

        retryStartBtn.addEventListener('click', async () => {
          lockedScreen.style.display = 'none';
          loadingState.style.display = 'flex';
          loadingText.textContent = 'Restarting…';
          try{
            await bootstrapAll();
          }catch(e){
            console.error(e);
            showLocked(e.message || 'Failed to start.', true);
          }
        });

        try{
          await bootstrapAll();
        }catch(err){
          console.error(err);
          loadingState.style.display = 'none';
          showError(err.message || 'Failed to load.');
        }
      });

      async function bootstrapAll(){
        loadingText.textContent = 'Loading question…';
        await loadQuestion();

        loadingText.textContent = 'Starting attempt…';
        await startAttemptOrLock();

        initUI();
      }

      async function loadQuestion(){
        const data = await fetchJson(`/api/coding_questions/${encodeURIComponent(questionIdentifier)}`, {
          method: 'GET',
          headers: authHeaders()
        });

        if (!data || data.status !== 'success'){
          throw new Error((data && data.message) ? data.message : 'Failed to load question');
        }

        questionData = data.data;
      }

      async function startAttemptOrLock(){
        // IMPORTANT: /api/judge/start must be authoritative
        try{
          const res = await postJson('/api/judge/start', {
            question_id: questionData.id,
            question_uuid: questionData.uuid || questionIdentifier,
            batch_uuid: batchUuid || null,
            attempt_uuid: attemptUuid || null
          });

          if (!res || res.status !== 'success'){
            throw new Error(res?.message || 'Unable to start attempt');
          }

          const p = normalizeStartPayload(res);

          attemptUuid = (p.attempt_uuid || '').trim();
          if (attemptUuid) sessionStorage.setItem(attemptKey, attemptUuid);

          attemptNo = (typeof p.attempt_no === 'number') ? p.attempt_no : (p.attempt_no ? Number(p.attempt_no) : null);
          attemptsAllowed = (p.attempts_allowed != null) ? Number(p.attempts_allowed) : null;
          attemptsLeft = (p.attempts_remaining != null) ? Number(p.attempts_remaining) : null;

          // persist meta
          try{
            sessionStorage.setItem(attemptMetaKey, JSON.stringify({
              attempt_uuid: attemptUuid,
              attempt_no: attemptNo,
              attempts_allowed: attemptsAllowed,
              attempts_remaining: attemptsLeft,
              expires_at: p.expires_at || null,
              remaining_seconds: p.remaining_seconds ?? null,
              server_now: p.server_now || null
            }));
          }catch(e){}

          setAttemptPills();
          startTimerFromServer({
            expires_at: p.expires_at,
            remaining_seconds: p.remaining_seconds,
            server_now: p.server_now
          });

          return;
        }catch(e){
          // attempts exhausted or forbidden
          if (e.status === 429){
            showLocked(e.message || 'Attempt limit reached.');
            return;
          }
          if (e.status === 403){
            showLocked(e.message || 'Not allowed to start this exam.');
            return;
          }
          // if route missing (should not in production)
          if (e.status === 404){
            console.warn('[judge/start] not found. This should not happen in production.');
            showLocked('Exam start endpoint not found. Please contact admin.', true);
            return;
          }

          throw e;
        }
      }

      function initUI(){
        // meta
        testTitle.textContent = questionData.title || 'Coding Test';
        questionTitle.textContent = questionData.title || 'Untitled Question';
        questionDifficulty.textContent = diffLabel(questionData.difficulty);

        // description
        questionDescription.innerHTML = questionData.description || '<p>No description provided.</p>';
        setTimeout(() => { try{ hljs.highlightAll(); }catch(e){} }, 0);

        // languages
        buildLanguages();

        // show content
        loadingState.style.display = 'none';
        lockedScreen.style.display = 'none';
        mainContent.style.display = 'block';

        // editor
        editor = CodeMirror.fromTextArea(codeEditorTextarea, {
          lineNumbers: true,
          mode: getCodeMirrorMode(currentLanguage),
          theme: 'dracula',
          indentUnit: 4,
          tabSize: 4,
          indentWithTabs: false,
          viewportMargin: Infinity
        });

        // start disabled until attempt is valid (it is now)
        setButtonsEnabled(true);

        editor.on('change', () => {
          if (suppressDirty) return;
          markDirty(true);
        });

        // apply code for first lang
        setTimeout(() => {
          applyLanguageAndCode(true);

          // auto-save every 20s if dirty
          setInterval(() => {
            if (isSubmittedFinal) return;
            if (!editor) return;
            if (!isDirty) return;
            saveCodeLocally(false);
            saveStateText.textContent = 'Auto-saved';
            setTimeout(() => { if (!isDirty) saveStateText.textContent = ''; }, 1200);
          }, 20000);
        }, 0);

        // language change
        languageSelect.addEventListener('change', () => {
          try{ localStorage.setItem(storageKey(currentLanguage), getCode()); }catch(e){}
          currentLanguage = languageSelect.value;
          updateLanguageMeta();
          applyLanguageAndCode(false);
        });

        wireActions();
        updateLanguageMeta();
      }

      function buildLanguages(){
        languageSelect.innerHTML = '';
        originalCode = {};

        const snippets = Array.isArray(questionData.snippets) ? questionData.snippets : [];
        const qlangs = Array.isArray(questionData.languages) ? questionData.languages : [];

        const langsFromSnips = snippets.map(s => normalizeLangKey(s.language_key)).filter(Boolean);
        const langsFromLangs = qlangs.map(l => normalizeLangKey(l.language_key)).filter(Boolean);

        const langs = [...new Set([...(langsFromSnips.length ? langsFromSnips : langsFromLangs)])];

        if (!langs.length){
          langs.push('python');
          originalCode['python'] = '# Write your solution here\n';
        }

        langs.forEach(lang => {
          const opt = document.createElement('option');
          opt.value = lang;
          opt.textContent = lang.charAt(0).toUpperCase() + lang.slice(1);
          languageSelect.appendChild(opt);

          const snip = snippets.find(s => normalizeLangKey(s.language_key) === lang);
          originalCode[lang] = (snip && typeof snip.template === 'string') ? snip.template : '';
        });

        if (langs.includes('python')) languageSelect.value = 'python';
        else languageSelect.value = langs[0];

        currentLanguage = languageSelect.value;
      }

      function applyLanguageAndCode(isInitial){
        if (!editor) return;

        editor.setOption('mode', getCodeMirrorMode(currentLanguage));

        const saved = localStorage.getItem(storageKey(currentLanguage));
        const base = originalCode[currentLanguage] || '';

        // If initial and saved exists and differs: ask restore (professional behavior)
        if (isInitial && saved && saved !== base) {
          setCodeProgrammatically(base);
          Swal.fire({
            title:'Restore saved code?',
            text:'We found locally saved code for this language. Restore it?',
            icon:'question',
            showCancelButton:true,
            confirmButtonText:'Yes, restore',
            cancelButtonText:'No'
          }).then(res => {
            if (res.isConfirmed) setCodeProgrammatically(saved);
          });
        } else {
          setCodeProgrammatically(saved || base);
        }

        updateLanguageMeta();
      }

      function wireActions(){
        runBtn.addEventListener('click', () => runCode());
        submitBtn.addEventListener('click', () => submitCode({ auto:false }));
        resetBtn.addEventListener('click', resetCode);
        saveBtn.addEventListener('click', () => saveCodeLocally(true));

        // keyboard shortcuts
        document.addEventListener('keydown', (e) => {
          if ((e.ctrlKey || e.metaKey) && e.key === 'Enter'){
            e.preventDefault();
            runBtn.click();
          }
          if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's'){
            e.preventDefault();
            saveCodeLocally(true);
          }
        });

        // before unload warning only if dirty
        window.addEventListener('beforeunload', (e) => {
          if (!isDirty || isSubmittedFinal) return;
          e.preventDefault();
          e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
          return e.returnValue;
        });
      }

      async function runCode(){
        if (isSubmittedFinal) return;

        const code = getCode().trim();
        if (!code){
          showError('Please write some code before running.');
          return;
        }

        resultsPanel.style.display = 'none';

        setBusy(runBtn, true, '<i class="fa-solid fa-play"></i> Run');
        try{
          // Prefer /api/judge/run (controller run). Fallback to /api/judge/execute (your older route).
          let res = null;

          try{
            res = await postJson('/api/judge/run', {
              question_id: questionData.id,
              question_uuid: questionData.uuid || questionIdentifier,
              batch_uuid: batchUuid || null,
              attempt_uuid: attemptUuid || null,
              language: currentLanguage,
              code,
              only_samples: true
            });
          }catch(e){
            if (e.status !== 404) throw e;
            res = await postJson('/api/judge/execute', {
              question_id: questionData.id,
              question_uuid: questionData.uuid || questionIdentifier,
              batch_uuid: batchUuid || null,
              attempt_uuid: attemptUuid || null,
              language: currentLanguage,
              code,
              only_samples: true
            });
          }

          if (!res || res.status !== 'success'){
            throw new Error(res?.message || 'Failed to run code.');
          }

          renderResults(res.results || [], !!res.all_pass);
        }catch(err){
          console.error(err);
          showError(err.message || 'Run failed.');
        }finally{
          setBusy(runBtn, false, '<i class="fa-solid fa-play"></i> Run');
        }
      }

      async function submitCode({ auto }){
        if (isSubmittedFinal || isSubmitting) return;

        const code = getCode().trim();
        if (!code){
          showError('Please write some code before submitting.');
          return;
        }

        if (!attemptUuid){
          showError('Attempt not started. Please refresh.');
          return;
        }

        if (!auto){
          const confirm = await Swal.fire({
            title:'Submit solution?',
            text:'This will submit your final answer for evaluation.',
            icon:'question',
            showCancelButton:true,
            confirmButtonText:'Yes, submit',
            cancelButtonText:'Cancel'
          });
          if (!confirm.isConfirmed) return;
        }

        isSubmitting = true;
        setBusy(submitBtn, true, '<i class="fa-regular fa-paper-plane"></i> Submit');

        try{
          const res = await postJson('/api/judge/submit', {
            question_id: questionData.id,
            question_uuid: questionData.uuid || questionIdentifier,
            batch_uuid: batchUuid || null,
            attempt_uuid: attemptUuid,
            auto_submit: !!auto,
            language: currentLanguage,
            code
          });

          if (!res || res.status !== 'success'){
            throw new Error(res?.message || 'Submit failed.');
          }

          isSubmittedFinal = true;
          isDirty = false;

          stopTimer();
          setButtonsEnabled(false);

          // show results (submit usually returns sample_results)
          renderResults(res.results || res.sample_results || [], !!res.all_pass);

          await Swal.fire({
            title: res.all_pass ? 'Submitted ✅' : 'Submitted',
            text: res.all_pass ? 'All tests passed.' : 'Submitted. Some tests did not pass.',
            icon: res.all_pass ? 'success' : 'info',
            confirmButtonText: 'OK'
          });

        }catch(err){
          console.error(err);

          if (err.status === 429){
            showLocked(err.message || 'Attempt limit reached.');
            return;
          }
          if (err.status === 409){
            showLocked(err.message || 'This attempt is already evaluated.');
            return;
          }
          if (err.status === 403){
            showLocked(err.message || 'Submission not allowed.');
            return;
          }

          showError(err.message || 'Submit failed.');
          if (!isSubmittedFinal) setButtonsEnabled(true);

        }finally{
          isSubmitting = false;
          setBusy(submitBtn, false, '<i class="fa-regular fa-paper-plane"></i> Submit');
        }
      }

      async function resetCode(){
        if (isSubmittedFinal) return;

        const res = await Swal.fire({
          title:'Reset code?',
          text:'This will restore the starter code for the selected language.',
          icon:'warning',
          showCancelButton:true,
          confirmButtonText:'Yes, reset',
          cancelButtonText:'Cancel'
        });

        if (!res.isConfirmed) return;

        const base = originalCode[currentLanguage] || '';
        setCodeProgrammatically(base);
        try{ localStorage.removeItem(storageKey(currentLanguage)); }catch(e){}
        markDirty(false);
      }
    })();
  </script>
</body>
</html>
