<!DOCTYPE html>
<html lang="en" class="theme-light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coding Test</title>

  <!-- Core CSS -->
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/media/images/favicons/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/common/main.css">

  <!-- Highlight.js for question description code blocks -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">

  <!-- CodeMirror for editor with line numbers + syntax coloring -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">

  <style>
    :root{
      --ct-max-width: 1280px;
    }

    body{
      background:var(--bg-body);
      color:var(--text-color);
      font-family:var(--font-sans, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
      -webkit-font-smoothing:antialiased;
    }

    /* ===== Top Appbar (similar feel to structure) ===== */
    .ct-appbar{
      position:sticky;
      top:0;
      z-index:50;
      height:56px;
      background:var(--surface);
      border-bottom:1px solid var(--line-strong);
      display:flex;
      align-items:center;
    }
    .ct-appbar-inner{
      width:100%;
      max-width:var(--ct-max-width);
      margin-inline:auto;
      padding-inline:12px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .ct-brand{
      display:flex;
      align-items:center;
      gap:8px;
      text-decoration:none;
    }
    .ct-brand img{
      height:24px;
    }
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
    }
    .ct-app-actions{
      margin-left:auto;
      display:flex;
      align-items:center;
      gap:6px;
    }
    .ct-icon-btn{
      width:32px;
      height:32px;
      border-radius:999px;
      border:1px solid var(--line-strong);
      background:var(--surface);
      display:inline-grid;
      place-items:center;
      font-size:.8rem;
    }
    .ct-icon-btn:hover{
      background:var(--page-hover);
    }

    /* ===== Main Container ===== */
    .ct-shell{
      max-width:var(--ct-max-width);
      margin:12px auto 24px;
      padding-inline:12px;
    }

    .ct-header-row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
      gap:8px;
      margin-bottom:10px;
    }

    .ct-title-row{
      display:flex;
      align-items:center;
      gap:8px;
      font-family:var(--font-head);
    }

    .ct-title-row i{
      color:var(--accent-color);
    }

    .ct-title-main{
      font-size:1.05rem;
      font-weight:600;
      color:var(--ink);
    }

    .ct-sub-muted{
      font-size:.8rem;
      color:var(--muted-color);
    }

    .ct-badge-row{
      display:flex;
      flex-wrap:wrap;
      gap:6px;
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
    }
    .ct-pill i{
      font-size:.72rem;
    }

    /* ===== Layout ===== */
    .ct-grid{
      display:grid;
      grid-template-columns:320px minmax(0,1fr);
      gap:16px;
    }
    @media (max-width: 991px){
      .ct-grid{
        grid-template-columns:1fr;
      }
    }

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

    .ct-q-header{
      border-bottom:1px solid var(--line-soft);
      padding-bottom:8px;
      margin-bottom:8px;
    }

    .ct-q-title{
      font-size:.98rem;
      font-weight:600;
      margin-bottom:4px;
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
      background:rgba(79,70,229,.08);
      border:1px solid rgba(79,70,229,.25);
      color:var(--accent-color);
    }

    .ct-q-body{
      font-size:.88rem;
      line-height:1.65;
    }

    .ct-q-body pre{
      background:#020617;
      color:#e5e7eb;
      padding:10px 12px;
      border-radius:10px;
      overflow:auto;
      font-size:.8rem;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
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
      gap:8px;
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
      font-weight:500;
    }

    .ct-lang-select-wrap select{
      height:30px;
      font-size:.82rem;
      border-radius:999px;
      padding-inline:10px 24px;
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
      padding:4px 10px;
      border:1px solid var(--line-soft);
      background:var(--surface-2);
      display:inline-flex;
      align-items:center;
      gap:6px;
    }
    .btn-chip i{
      font-size:.75rem;
    }
    .btn-chip:hover{
      background:var(--page-hover);
    }

    .btn-chip-run{
      border-color:rgba(34,197,94,.55);
      color:#22c55e;
      background:rgba(34,197,94,.08);
    }
    .btn-chip-run:hover{
      background:rgba(34,197,94,.13);
    }

    /* ===== Code editor (CodeMirror) ===== */
    .ct-editor-shell{
      border-radius:12px;
      border:1px solid var(--line-strong);
      overflow:hidden;
      background:#020617;
    }

    .CodeMirror{
      height:360px;
      min-height:360px;
      background:#020617;
      color:#e5e7eb;
      font-size:13px;
      line-height:1.5;
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
    }
    .ct-kbd{
      border-radius:4px;
      border:1px solid var(--line-soft);
      padding:1px 5px;
      background:var(--surface-2);
      font-size:.7rem;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    }

    .btn-pill-sm{
      border-radius:999px;
      font-size:.8rem;
      padding:6px 14px;
      display:inline-flex;
      align-items:center;
      gap:6px;
    }
    .btn-pill-sm i{
      font-size:.8rem;
    }

    .btn-submit-main{
      background:var(--accent-color);
      border-color:var(--accent-color);
      color:#fff;
    }
    .btn-submit-main:hover{
      filter:brightness(.96);
      color:#fff;
    }

    /* ===== Loading ===== */
    .ct-loading{
      display:flex;
      justify-content:center;
      align-items:center;
      min-height:260px;
    }
    .ct-spinner{
      width:36px;
      height:36px;
      border-radius:999px;
      border:3px solid rgba(15,23,42,.15);
      border-top-color:var(--accent-color);
      animation:ct-spin 1s linear infinite;
    }
    @keyframes ct-spin{
      to{transform:rotate(360deg);}
    }

    /* ===== Terminal-style Results ===== */
    .ct-results{
      margin-top:16px;
      display:none;
    }

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
    }
    .ct-results-header h6 i{
      color:#22c55e;
    }

    .ct-results-summary{
      font-size:.76rem;
      color:var(--muted-color);
      white-space:nowrap;
    }
    .ct-results-summary span{
      font-weight:600;
    }
    .ct-results-summary #passedCount{color:#16a34a;}
    .ct-results-summary #failedCount{color:#f97316;}

    .ct-terminal{
      background:#020617;
      border-radius:12px;
      border:1px solid #020617;
      overflow:hidden;
      font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
      font-size:.78rem;
    }
    .ct-term-bar{
      display:flex;
      align-items:center;
      gap:6px;
      padding:6px 8px;
      border-bottom:1px solid #020617;
      background:#020617;
    }
    .ct-term-dot{
      width:9px;
      height:9px;
      border-radius:999px;
    }
    .ct-term-dot.red{background:#f97373;}
    .ct-term-dot.amber{background:#fbbf24;}
    .ct-term-dot.green{background:#22c55e;}
    .ct-term-title{
      font-size:.72rem;
      color:#9ca3af;
      margin-left:4px;
    }

    .ct-term-body{
      max-height:320px;
      overflow:auto;
      padding:8px 10px 10px;
      color:#e5e7eb;
    }

    .ct-line{
      white-space:pre-wrap;
      word-wrap:break-word;
    }

    .ct-line + .ct-line{
      margin-top:6px;
    }

    .ct-line-prefix{
      color:#60a5fa;
    }

    .ct-line-status-pass{
      color:#4ade80;
    }
    .ct-line-status-fail{
      color:#fb7185;
    }
    .ct-line-label{
      color:#a5b4fc;
    }
    .ct-line-error{
      color:#fb7185;
    }

    .ct-term-body::-webkit-scrollbar{
      width:6px;
    }
    .ct-term-body::-webkit-scrollbar-track{
      background:#020617;
    }
    .ct-term-body::-webkit-scrollbar-thumb{
      background:#374151;
      border-radius:3px;
    }
  </style>
</head>
<body>
  <!-- Appbar -->
  <header class="ct-appbar">
    <div class="ct-appbar-inner">
      <a href="/admin/dashboard" class="ct-brand">
        <img src="/assets/media/images/web/logo.png" alt="W3Techiez">
        <span>W3Techiez</span>
      </a>
      <span class="ct-page-title">Coding Test</span>

      <div class="ct-app-actions">
        <button type="button" class="ct-icon-btn" title="Help">
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
          <div class="ct-sub-muted">Solve the coding question and run against test cases.</div>
        </div>
      </div>
      <div class="ct-badge-row">
        <div class="ct-pill">
          <i class="fa-regular fa-circle-check"></i>
          Auto-graded
        </div>
        <div class="ct-pill">
          <i class="fa-regular fa-keyboard"></i>
          Use <span style="font-family:monospace;">Ctrl/Cmd + Enter</span> to run
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="ct-loading">
      <div class="ct-spinner"></div>
    </div>

    <!-- Main Layout -->
    <div id="mainContent" style="display:none;">
      <div class="ct-grid">
        <!-- Question Panel -->
        <aside class="ct-panel ct-question-panel">
          <div class="ct-q-header">
            <div class="ct-q-title" id="questionTitle">Loading...</div>
            <div class="ct-q-meta">
              <span class="ct-chip" id="questionDifficulty">Medium</span>
              <span class="ct-chip">Coding</span>
            </div>
          </div>
          <article class="ct-q-body" id="questionDescription">
            Loading question description...
          </article>
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
              <span class="ct-lang-meta" id="languageMeta"></span>
            </div>
            <div class="ct-editor-actions-top">
              <!-- Run beside Reset -->
              <button id="runBtn" type="button" class="btn btn-sm btn-chip btn-chip-run">
                <i class="fa-solid fa-play"></i>
                Run
              </button>
              <button id="resetBtn" type="button" class="btn btn-sm btn-chip">
                <i class="fa-solid fa-rotate-right"></i>
                Reset
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
              <span>Run code</span>
            </div>

            <button id="submitBtn" type="button" class="btn btn-primary btn-pill-sm btn-submit-main">
              <i class="fa-regular fa-paper-plane"></i>
              Submit
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
              </div>
            </div>
            <div class="ct-terminal">
              <div class="ct-term-bar">
                <span class="ct-term-dot red"></span>
                <span class="ct-term-dot amber"></span>
                <span class="ct-term-dot green"></span>
                <span class="ct-term-title">judge@w3techiez:~/sandbox</span>
              </div>
              <div class="ct-term-body" id="testResultsContainer">
                <!-- Filled by JS -->
              </div>
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
    // Global variables
    let questionData = null;
    let currentLanguage = 'python';
    let originalCode = {};
    let testResults = [];
    let editor = null;

    // DOM Elements
    const loadingState = document.getElementById('loadingState');
    const mainContent = document.getElementById('mainContent');
    const testTitle = document.getElementById('testTitle');
    const questionTitle = document.getElementById('questionTitle');
    const questionDifficulty = document.getElementById('questionDifficulty');
    const questionDescription = document.getElementById('questionDescription');
    const languageSelect = document.getElementById('languageSelect');
    const languageMeta = document.getElementById('languageMeta');
    const codeEditorTextarea = document.getElementById('codeEditor');
    const runBtn = document.getElementById('runBtn');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');
    const resultsPanel = document.getElementById('resultsPanel');
    const testResultsContainer = document.getElementById('testResultsContainer');
    const passedCount = document.getElementById('passedCount');
    const failedCount = document.getElementById('failedCount');
    const totalCount = document.getElementById('totalCount');

    // Get UUID from URL (attempt or question slug/uuid at end)
    const uuid = window.location.pathname.split('/').filter(Boolean).pop();

    document.addEventListener('DOMContentLoaded', async () => {
      try {
        await loadQuestionData();
        initializePage();
      } catch (error) {
        showError('Failed to load question. Please try again.');
        console.error(error);
      }
    });

    // Load question data from API
    async function loadQuestionData() {
      const response = await fetch(`/api/coding_questions/${uuid}`);
      const result = await response.json();

      if (!response.ok || result.status !== 'success') {
        throw new Error(result.message || 'Failed to load question');
      }
      questionData = result.data;
    }

    function initializePage() {
      // Title & meta
      testTitle.textContent = questionData.title || 'Coding Test';
      questionTitle.textContent = questionData.title || 'Untitled Question';

      const diff = (questionData.difficulty || 'medium');
      const label = diff.charAt(0).toUpperCase() + diff.slice(1);
      questionDifficulty.textContent = label;

      // Description
      questionDescription.innerHTML = questionData.description || '<p>No description provided.</p>';

      // Initialize languages (sets textarea value for default language)
      initializeLanguages();

      // Initialize CodeMirror editor
      setupEditor();

      // Done loading
      loadingState.style.display = 'none';
      mainContent.style.display = 'block';

      // Highlight any code blocks in description
      hljs.highlightAll();

      // Try restore locally saved code
      restoreSavedCodeIfAny();
    }

    function initializeLanguages() {
      languageSelect.innerHTML = '';
      originalCode = {};

      // Unique language keys from snippets
      const snippets = questionData.snippets || [];
      const languages = [...new Set(snippets.map(s => s.language_key))];

      languages.forEach(lang => {
        const opt = document.createElement('option');
        opt.value = lang;
        opt.textContent = lang.charAt(0).toUpperCase() + lang.slice(1);
        languageSelect.appendChild(opt);

        const snippet = snippets.find(s => s.language_key === lang);
        originalCode[lang] = snippet?.template || '';
      });

      // Default language preference
      if (languages.includes('python')) {
        languageSelect.value = 'python';
      } else if (languages.length > 0) {
        languageSelect.value = languages[0];
      }

      currentLanguage = languageSelect.value;
      codeEditorTextarea.value = originalCode[currentLanguage] || '';
      updateLanguageMeta();

      languageSelect.addEventListener('change', () => {
        currentLanguage = languageSelect.value;
        updateLanguageMeta();
        updateEditorLanguageAndCode();
      });
    }

    function setupEditor() {
      editor = CodeMirror.fromTextArea(codeEditorTextarea, {
        lineNumbers: true,
        mode: getCodeMirrorMode(currentLanguage),
        theme: 'dracula',
        indentUnit: 4,
        tabSize: 4,
        indentWithTabs: false,
        viewportMargin: Infinity
      });
      editor.setValue(originalCode[currentLanguage] || '');
      editor.refresh();
    }

    function getCodeMirrorMode(lang) {
      if (!lang) return 'text/plain';
      const key = lang.toLowerCase();
      if (key === 'python' || key === 'py') return 'python';
      if (key === 'js' || key === 'javascript' || key === 'node') return 'javascript';
      if (key === 'cpp' || key === 'c++' || key === 'c' || key === 'java' || key === 'csharp' || key === 'c#') {
        return 'text/x-c++src';
      }
      if (key === 'php') return 'application/x-httpd-php';
      return 'text/plain';
    }

    function updateEditorLanguageAndCode() {
      if (!editor) return;
      editor.setOption('mode', getCodeMirrorMode(currentLanguage));
      const savedCode = localStorage.getItem(`code_${uuid}_${currentLanguage}`);
      editor.setValue(savedCode || originalCode[currentLanguage] || '');
      editor.refresh();
    }

    function updateLanguageMeta() {
      if (!currentLanguage) {
        languageMeta.textContent = '';
        return;
      }
      languageMeta.textContent = currentLanguage.toUpperCase();
    }

    function getCode() {
      return editor ? editor.getValue() : codeEditorTextarea.value;
    }
    function setCode(text) {
      if (editor) {
        editor.setValue(text || '');
        editor.refresh();
        editor.focus();
      } else {
        codeEditorTextarea.value = text || '';
      }
    }

    // ===== Run code =====
    runBtn.addEventListener('click', async () => {
      if (!getCode().trim()) {
        showError('Please write some code before running.');
        return;
      }

      runBtn.disabled = true;
      runBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Running';

      try {
        const response = await fetch('/api/judge/execute', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAuthToken()}`
          },
          body: JSON.stringify({
            question_id: questionData.id,
            language: currentLanguage,
            code: getCode()
          })
        });

        const result = await response.json();

        if (result.status === 'success') {
          displayTestResults(result.results || []);
        } else {
          showError(result.message || 'Failed to run code');
        }
      } catch (error) {
        console.error(error);
        showError('Network error. Please check your connection.');
      } finally {
        runBtn.disabled = false;
        runBtn.innerHTML = '<i class="fa-solid fa-play"></i> Run';
      }
    });

    // ===== Submit solution =====
    submitBtn.addEventListener('click', async () => {
      if (!getCode().trim()) {
        showError('Please write some code before submitting.');
        return;
      }

      const { value: confirmation } = await Swal.fire({
        title: 'Submit solution?',
        text: 'Are you sure you want to submit this attempt?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, submit',
        cancelButtonText: 'Cancel'
      });

      if (!confirmation) return;

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting';

      try {
        const response = await fetch('/api/submissions', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAuthToken()}`
          },
          body: JSON.stringify({
            question_id: questionData.id,
            language: currentLanguage,
            code: getCode(),
            attempt_uuid: uuid
          })
        });

        const result = await response.json();

        if (result.status === 'success') {
          await Swal.fire({
            title: 'Submitted',
            text: 'Your solution has been submitted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
          });
          if (result.data && result.data.submission_id) {
            window.location.href = `/submissions/${result.data.submission_id}`;
          }
        } else {
          showError(result.message || 'Failed to submit solution');
        }
      } catch (error) {
        console.error(error);
        showError('Network error. Please try again.');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-regular fa-paper-plane"></i> Submit';
      }
    });

    // ===== Reset code =====
    resetBtn.addEventListener('click', () => {
      Swal.fire({
        title: 'Reset code?',
        text: 'This will restore the original starter code for the selected language.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, reset',
        cancelButtonText: 'Cancel'
      }).then((res) => {
        if (res.isConfirmed) {
          setCode(originalCode[currentLanguage] || '');
        }
      });
    });

    // ===== Display test results (terminal style) =====
    function displayTestResults(results) {
      testResults = results || [];
      const passed = testResults.filter(r => r.pass).length;
      const total = testResults.length;
      const failed = total - passed;

      passedCount.textContent = passed;
      failedCount.textContent = failed;
      totalCount.textContent = total;

      testResultsContainer.innerHTML = '';

      if (!total) {
        testResultsContainer.innerHTML =
          '<div class="ct-line"><span class="ct-line-prefix">$</span> no test cases returned.</div>';
      } else {
        testResults.forEach((t, idx) => {
          const statusClass = t.pass ? 'ct-line-status-pass' : 'ct-line-status-fail';
          const statusLabel = t.pass ? 'PASSED' : 'FAILED';

          const input = escapeHtml(t.input || '(none)');
          const expected = escapeHtml(t.expected || '(none)');
          const output = escapeHtml(t.output || '(none)');
          const errorText = t.error ? escapeHtml(t.error) : '';

          const lineBlock = document.createElement('div');
          lineBlock.className = 'ct-line';
          lineBlock.innerHTML = `
            <div>
              <span class="ct-line-prefix">$</span>
              test <span>#${idx + 1}</span>
              <span class="${statusClass}">[${statusLabel}]</span>
            </div>
            <div><span class="ct-line-label">&gt; input</span></div>
            <div>${input}</div>
            <div style="margin-top:2px;"><span class="ct-line-label">&gt; expected</span></div>
            <div>${expected}</div>
            <div style="margin-top:2px;"><span class="ct-line-label">&gt; output</span></div>
            <div>${output}</div>
            ${errorText ? `
              <div style="margin-top:2px;"><span class="ct-line-label">&gt; error</span></div>
              <div class="ct-line-error">${errorText}</div>
            ` : ''}
          `;
          testResultsContainer.appendChild(lineBlock);
        });
      }

      resultsPanel.style.display = 'block';
      resultsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ===== Helpers =====
    function getAuthToken() {
      return localStorage.getItem('token') || sessionStorage.getItem('token') || '';
    }

    function showError(message) {
      Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function saveCodeLocally() {
      try {
        localStorage.setItem(`code_${uuid}_${currentLanguage}`, getCode());
      } catch (e) {
        console.warn('Unable to save locally', e);
      }

      const toast = document.createElement('div');
      toast.textContent = 'Code saved locally';
      toast.style.cssText = `
        position:fixed;
        bottom:18px;
        right:18px;
        background:#16a34a;
        color:#fff;
        padding:8px 14px;
        border-radius:999px;
        font-size:.8rem;
        z-index:9999;
        box-shadow:0 10px 25px rgba(0,0,0,.25);
        opacity:0;
        transform:translateY(6px);
        transition:opacity .2s ease, transform .2s ease;
      `;
      document.body.appendChild(toast);
      requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
      });
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(6px)';
        setTimeout(() => toast.remove(), 180);
      }, 1500);
    }

    function restoreSavedCodeIfAny() {
      const saved = localStorage.getItem(`code_${uuid}_${currentLanguage}`);
      if (saved && saved !== (originalCode[currentLanguage] || '')) {
        Swal.fire({
          title: 'Restore saved code?',
          text: 'We found locally saved code for this question. Restore it?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, restore',
          cancelButtonText: 'No, use starter'
        }).then(res => {
          if (res.isConfirmed) {
            setCode(saved);
          }
        });
      }
    }

    // Warn if leaving with unsaved edits
    window.addEventListener('beforeunload', (e) => {
      const current = getCode();
      const base = originalCode[currentLanguage] || '';
      if (current.trim() && current !== base) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
      }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
      // Ctrl+Enter or Cmd+Enter -> Run
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        runBtn.click();
      }

      // Ctrl+S / Cmd+S -> save to local
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        saveCodeLocally();
      }
    });
  </script>
</body>
</html>
