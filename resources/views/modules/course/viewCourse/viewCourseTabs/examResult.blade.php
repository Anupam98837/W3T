{{-- resources/views/exam/quizResultStandalone.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <title>Quiz Result</title>

  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/web/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

  <style id="erStyles">
    .er-wrap{
      max-width:1100px;
      margin:16px auto 40px;
    }
    .er-shell{
      border-radius:16px;
      border:1px solid var(--line-strong);
      background:var(--surface);
      box-shadow:var(--shadow-2);
      padding:16px 18px 18px;
      position:relative;
    }
    .er-head{
      display:flex;
      align-items:flex-start;
      gap:14px;
      margin-bottom:12px;
    }
    .er-head-icon{
      width:40px;height:40px;
      border-radius:14px;
      border:1px solid var(--line-strong);
      background:var(--surface-2);
      display:flex;align-items:center;justify-content:center;
      color:var(--accent-color);
      flex-shrink:0;
    }
    .er-breadcrumb{
      font-size:var(--fs-12);
      color:var(--muted-color);
      margin-bottom:2px;
    }
    .er-breadcrumb a{ color:var(--secondary-color); }
    .er-title{
      font-family:var(--font-head);
      font-weight:700;
      color:var(--ink);
      font-size:1.25rem;
      margin:0;
    }
    .er-sub{
      font-size:var(--fs-13);
      color:var(--muted-color);
      margin-top:3px;
    }
    .er-actions{
      margin-left:auto;
      display:flex;
      flex-wrap:wrap;
      gap:8px;
    }
    .er-actions .btn{
      border-radius:999px;
      padding-inline:12px;
    }
    .er-actions .btn i{ margin-right:6px; }

    .er-row{ margin-top:10px; }

    .er-card{
      border-radius:14px;
      border:1px solid var(--line-strong);
      background:var(--surface-2);
      padding:12px 12px 10px;
      box-shadow:var(--shadow-1);
    }
    .er-card-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      margin-bottom:8px;
    }
    .er-card-title{
      font-family:var(--font-head);
      font-weight:600;
      color:var(--ink);
      font-size:.95rem;
      margin:0;
    }
    .er-chip{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:3px 8px;
      border-radius:999px;
      font-size:11px;
      border:1px solid var(--line-strong);
      background:var(--surface);
      color:var(--muted-color);
    }
    .er-chip i{font-size:10px;}
    .er-chip-primary{
      background:var(--t-primary);
      border-color:rgba(20,184,166,.25);
      color:#0f766e;
    }

    .er-score-main{
      display:flex;
      align-items:center;
      gap:14px;
      margin-bottom:10px;
    }
    .er-score-circle{
      width:72px;height:72px;
      border-radius:50%;
      border:5px solid rgba(20,184,166,.16);
      display:flex;align-items:center;justify-content:center;
      flex-direction:column;
      font-family:var(--font-head);
      color:var(--ink);
      position:relative;
    }
    .er-score-circle::after{
      content:"";
      position:absolute;inset:6px;
      border-radius:inherit;
      border:3px solid var(--accent-color);
      opacity:.5;
    }
    .er-score-value{
      font-size:1.25rem;
      font-weight:700;
    }
    .er-score-label{
      font-size:11px;
      color:var(--muted-color);
    }
    .er-score-text strong{ font-weight:600; }
    .er-score-text{
      font-size:var(--fs-13);
      color:var(--muted-color);
    }

    .er-metrics{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:8px;
      margin-top:4px;
    }
    .er-metric{
      border-radius:10px;
      background:var(--surface);
      border:1px dashed var(--line-strong);
      padding:6px 8px;
      font-size:var(--fs-12);
    }
    .er-metric-label{
      color:var(--muted-color);
      margin-bottom:3px;
    }
    .er-metric-value{
      font-weight:600;
      color:var(--ink);
    }

    .er-bar-wrap{
      margin-top:4px;
    }
    .er-bar-bg{
      width:100%;
      height:8px;
      border-radius:999px;
      background:#e5eff0;
      overflow:hidden;
    }
    .er-bar-fill{
      height:100%;
      border-radius:inherit;
      background:var(--accent-color);
      width:0%;
      transition:width .4s ease;
    }
    .er-bar-label{
      font-size:var(--fs-12);
      color:var(--muted-color);
      margin-top:2px;
    }

    .er-pill-row{
      display:flex;
      flex-wrap:wrap;
      gap:6px;
      font-size:var(--fs-12);
    }
    .er-pill{
      padding:4px 8px;
      border-radius:999px;
      border:1px solid var(--line-strong);
      display:inline-flex;
      align-items:center;
      gap:5px;
      background:var(--surface);
    }
    .er-pill i{font-size:11px;}
    .er-pill-green{
      background:var(--t-success);
      border-color:rgba(22,163,74,.25);
      color:#15803d;
    }
    .er-pill-red{
      background:var(--t-danger);
      border-color:rgba(220,38,38,.25);
      color:#b91c1c;
    }
    .er-pill-gray{
      background:var(--surface-3);
    }

    .er-table-card{
      margin-top:14px;
      border-radius:14px;
      border:1px solid var(--line-strong);
      background:var(--surface-2);
      box-shadow:var(--shadow-1);
      padding:10px 12px 12px;
    }
    .er-table-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      margin-bottom:6px;
    }
    .er-table-title{
      font-family:var(--font-head);
      font-weight:600;
      color:var(--ink);
      font-size:1rem;
      margin:0;
    }
    .er-table-sub{
      font-size:var(--fs-13);
      color:var(--muted-color);
    }

    .er-q-status{
      display:inline-flex;
      align-items:center;
      gap:4px;
      font-size:11px;
      padding:2px 6px;
      border-radius:999px;
    }
    .er-q-status.correct{
      background:var(--t-success);
      color:#15803d;
    }
    .er-q-status.wrong{
      background:var(--t-danger);
      color:#b91c1c;
    }
    .er-q-status.skipped{
      background:var(--surface-3);
      color:var(--muted-color);
    }

    .er-loader-wrap{
      position:absolute;
      inset:0;
      display:none;
      align-items:center;
      justify-content:center;
      background:rgba(0,0,0,.04);
      z-index:5;
    }
    .er-loader-wrap.show{display:flex;}
    .er-loader{
      width:22px;height:22px;
      border-radius:50%;
      border:3px solid #0001;
      border-top-color:var(--accent-color);
      animation:er-rot 1s linear infinite;
    }
    @keyframes er-rot{to{transform:rotate(360deg)}}

    .er-error{
      margin-top:8px;
      font-size:12px;
      color:var(--danger-color);
      display:none;
    }
    .er-error.show{display:block;}

    .er-empty{
      margin-top:8px;
      border:1px dashed var(--line-strong);
      border-radius:10px;
      padding:16px;
      text-align:center;
      font-size:var(--fs-13);
      color:var(--muted-color);
      background:var(--surface-2);
    }

    /* === Question sheet-style cards === */
    .er-q-list{
      margin-top:8px;
      display:flex;
      flex-direction:column;
      gap:8px;
    }
    .er-qcard{
      border-radius:12px;
      border:1px solid var(--line-strong);
      background:var(--surface);
      padding:8px 9px 8px;
      box-shadow:var(--shadow-1);
    }
    .er-qcard-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      margin-bottom:4px;
    }
    .er-q-left{
      display:flex;
      align-items:center;
      gap:8px;
    }
    .er-q-badge{
      min-width:32px;
      height:22px;
      border-radius:999px;
      border:1px solid var(--line-strong);
      background:var(--surface-3);
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:11px;
      color:var(--muted-color);
    }
    .er-q-meta{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      justify-content:flex-end;
      font-size:11px;
      color:var(--muted-color);
    }
    .er-q-meta span strong{
      color:var(--ink);
    }

    .er-q-question{
      margin-top:2px;
    }
    .er-q-question-main{
      font-size:var(--fs-13);
      color:var(--text-color);
    }
    .er-q-question-desc{
      margin-top:2px;
      font-size:11px;
      color:var(--muted-color);
    }

    .er-qcard-answers{
      margin-top:6px;
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:8px;
    }
    @media (max-width: 768px){
      .er-qcard-answers{
        grid-template-columns:1fr;
      }
    }
    .er-q-answer-block{
      border-radius:10px;
      border:1px solid var(--line-strong);
      background:var(--surface-2);
      padding:6px 8px;
      font-size:11px;
    }
    .er-q-answer-block.correct{
      background:var(--t-success);
      border-color:rgba(22,163,74,.35);
      color:#14532d;
    }
    .er-q-answer-block.your{
      border-style:dashed;
    }
    .er-q-answer-label{
      font-weight:600;
      text-transform:uppercase;
      letter-spacing:.03em;
      margin-bottom:2px;
      color:var(--muted-color);
    }
    .er-q-answer-text{
      font-size:var(--fs-12);
      color:var(--text-color);
    }

    .er-q-time{
      margin-top:6px;
    }
    .er-q-time-top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      font-size:11px;
      color:var(--muted-color);
      margin-bottom:3px;
    }
    .er-q-time-bar-bg{
      width:100%;
      height:4px;
      border-radius:999px;
      background:var(--surface-3);
      overflow:hidden;
    }
    .er-q-time-bar-fill{
      height:100%;
      border-radius:inherit;
      background:var(--accent-color);
      width:0%;
      transition:width .3s ease;
    }

    /* blanks for FIB {dash} */
    .er-blank{
      display:inline-block;
      min-width:40px;
      border-bottom:2px solid #9ca3af;
      margin:0 3px;
    }
    html.theme-dark .er-blank{
      border-bottom-color:#6b7280;
    }

    /* Print: show only the result shell */
    @media print{
      #sidebar,
      .w3-sidebar,
      .w3-appbar,
      #sidebarOverlay{
        display:none!important;
      }
      body{
        background:#fff!important;
      }
      main.w3-content{
        max-width:100%!important;
        padding:0!important;
        margin:0!important;
      }
      .panel{
        border:none!important;
        box-shadow:none!important;
        padding:0!important;
      }
      .er-wrap{
        margin:0!important;
        max-width:100%!important;
      }
      .er-actions{
        display:none!important;
      }
    }

    /* Dark mode tweaks */
    html.theme-dark .er-shell,
    html.theme-dark .er-card,
    html.theme-dark .er-table-card{
      background:#04151f;
    }
    html.theme-dark .er-head-icon{
      background:#020b13;
    }
    html.theme-dark .er-empty{
      background:#020b13;
    }
    html.theme-dark .er-qcard{
      background:#020b13;
    }

    /* MathJax layout tweaks (match manageQuestions) */
    mjx-container,
    mjx-container[display="block"],
    .mjx-chtml {
      display:inline-block !important;
    }
    mjx-container svg,
    mjx-container[display="block"] svg,
    .mjx-chtml svg {
      vertical-align:middle;
    }
    .er-q-question mjx-container[display="block"],
    .er-q-question-desc mjx-container[display="block"],
    .er-q-answer-text mjx-container[display="block"]{
      margin:4px 0;
    }
  </style>

  {{-- MathJax config for LaTeX rendering --}}
  <script>
    window.MathJax = {
      tex: {
        inlineMath: [['$', '$'], ['\\(', '\\)']],
        displayMath: [['\\[', '\\]'], ['$$', '$$']],
        processEscapes: true
      },
      options: {
        skipHtmlTags: ['script','noscript','style','textarea','pre','code']
      },
      startup: {
        // we will call MathJax ourselves after injecting HTML
        typeset: false
      }
    };
  </script>

  {{-- ✅ FIX: load MathJax ONCE (no duplicate tag). Use defer so config is applied reliably. --}}
  <script id="MathJax-script" defer
          src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"></script>
</head>

<body>
  <div class="er-wrap">
    <div id="resultShell"
         class="er-shell"
         data-result-id="{{ $resultId }}">

      <div class="er-loader-wrap" id="erLoader">
        <div class="er-loader"></div>
      </div>

      <div class="er-head">

          <img id="logo" src="{{ asset('/assets/media/images/web/logo.png') }}" alt="Unzip Examination" style="height:50px;width:auto;">

        <div>
          <h1 class="er-title" id="erQuizTitle">Quiz Result</h1>
          <div class="er-sub" id="erAttemptMeta">
            Loading attempt details...
          </div>
        </div>

        <div class="er-actions">
          <button type="button" class="btn btn-light btn-sm d-none" id="erHtmlExport">
            <i class="fa-regular fa-file-code"></i> Export HTML
          </button>
          <button type="button" class="btn btn-primary btn-sm" id="erPdfExport">
            <i class="fa-regular fa-file-pdf"></i> Export PDF
          </button>
        </div>
      </div>

      <div class="row g-3 er-row">
        <div class="col-md-7">
          <div class="er-card">
            <div class="er-card-head">
              <h2 class="er-card-title">Score summary</h2>
              <span class="er-chip er-chip-primary" id="erScoreChip">
                <i class="fa-solid fa-award"></i> Overall
              </span>
            </div>
            <div class="er-score-main">
              <div class="er-score-circle">
                <div class="er-score-value" id="erPercent">0%</div>
                <div class="er-score-label">Percent</div>
              </div>
              <div class="er-score-text" id="erScoreText">
                Your score will appear here once the data is loaded.
              </div>
            </div>

            <div class="er-metrics">
              <div class="er-metric">
                <div class="er-metric-label">Marks obtained</div>
                <div class="er-metric-value" id="erMarks">0 / 0</div>
              </div>
              <div class="er-metric">
                <div class="er-metric-label">Questions attempted</div>
                <div class="er-metric-value" id="erAttempted">0 / 0</div>
              </div>
              <div class="er-metric">
                <div class="er-metric-label">Time spent</div>
                <div class="er-metric-value" id="erTimeSpent">-</div>
              </div>
            </div>

            <div class="er-bar-wrap">
              <div class="er-bar-bg">
                <div class="er-bar-fill" id="erScoreBar"></div>
              </div>
              <div class="er-bar-label" id="erBarLabel">
                Accuracy: 0%
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-5">
          <div class="er-card">
            <div class="er-card-head">
              <h2 class="er-card-title">Attempts & accuracy</h2>
              <span class="er-chip" id="erAttemptChip">
                <i class="fa-regular fa-circle-check"></i>
                Attempt
              </span>
            </div>

            <div class="er-pill-row mb-2">
              <span class="er-pill er-pill-green">
                <i class="fa-solid fa-check"></i>
                <span id="erCorrectCount">0 correct</span>
              </span>
              <span class="er-pill er-pill-red">
                <i class="fa-solid fa-xmark"></i>
                <span id="erWrongCount">0 wrong</span>
              </span>
              <span class="er-pill er-pill-gray">
                <i class="fa-regular fa-circle"></i>
                <span id="erSkippedCount">0 not attempted</span>
              </span>
            </div>

            <ul class="mb-0 small text-muted" id="erMetaList">
              <li>Current attempt ID: <span id="erAttemptId">-</span></li>
              <li>Submitted at: <span id="erSubmittedAt">-</span></li>
            </ul>
          </div>
        </div>
      </div>

      <div class="er-table-card mt-3">
        <div class="er-table-head">
          <div>
            <h2 class="er-table-title">Question-wise analysis</h2>
            <div class="er-table-sub">
              See which questions you got right, wrong, or skipped, with time spent.
            </div>
          </div>
        </div>

        <div id="erNoQuestions" class="er-empty d-none">
          No question-level data is available for this attempt.
        </div>

        <div id="erQuestionList" class="er-q-list">
          {{-- Filled by JS --}}
        </div>
      </div>

      <div id="erError" class="er-error"></div>

    </div>
  </div>

  <script>
  (function () {
    "use strict";

    function initResultPage() {
      var resultShell = document.getElementById("resultShell");
      if (!resultShell) return;

      var RESULT_ID = resultShell.dataset.resultId;
      if (!RESULT_ID) {
        var errEl = document.getElementById("erError");
        if (errEl) {
          errEl.textContent = "Result reference is missing.";
          errEl.classList.add("show");
        }
        console.error("Missing result id on resultShell");
        return;
      }

      var loaderEl    = document.getElementById("erLoader");
      var errorEl     = document.getElementById("erError");

      var quizTitleEl      = document.getElementById("erQuizTitle");
      var attemptMetaEl    = document.getElementById("erAttemptMeta");
      var scoreChipEl      = document.getElementById("erScoreChip");

      var percentEl   = document.getElementById("erPercent");
      var scoreTextEl = document.getElementById("erScoreText");
      var marksEl     = document.getElementById("erMarks");
      var attemptedEl = document.getElementById("erAttempted");
      var timeSpentEl = document.getElementById("erTimeSpent");
      var scoreBarEl  = document.getElementById("erScoreBar");
      var barLabelEl  = document.getElementById("erBarLabel");

      var correctCountEl = document.getElementById("erCorrectCount");
      var wrongCountEl   = document.getElementById("erWrongCount");
      var skippedCountEl = document.getElementById("erSkippedCount");
      var attemptIdEl    = document.getElementById("erAttemptId");
      var submittedAtEl  = document.getElementById("erSubmittedAt");

      var questionListEl = document.getElementById("erQuestionList");
      var noQuestionsEl  = document.getElementById("erNoQuestions");

      var pdfBtn  = document.getElementById("erPdfExport");
      var htmlBtn = document.getElementById("erHtmlExport");

      /* ---------- helpers ---------- */

      function getToken() {
        try {
          return sessionStorage.getItem("token") || localStorage.getItem("token") || null;
        } catch (e) {
          return null;
        }
      }

      function clearAuthStorage() {
        try { sessionStorage.removeItem("token"); } catch (e) {}
        try { sessionStorage.removeItem("role"); } catch (e) {}
        try { localStorage.removeItem("token"); } catch (e) {}
        try { localStorage.removeItem("role"); } catch (e) {}
      }

      function showLoader(show) {
        if (!loaderEl) return;
        if (show) loaderEl.classList.add("show");
        else loaderEl.classList.remove("show");
      }

      function showError(msg) {
        if (!errorEl) return;
        errorEl.textContent = msg || "Something went wrong while loading the result.";
        errorEl.classList.add("show");
      }

      function formatDateTime(str) {
        if (!str) return "-";
        var d = new Date(str);
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString();
      }

      function formatDuration(seconds) {
        var sec = Number(seconds || 0);
        if (!sec) return "-";
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        if (m && s) return m + "m " + s + "s";
        if (m) return m + "m";
        return s + "s";
      }

      // decode &nbsp; etc. once
      function decodeEntities(str) {
        if (str === null || str === undefined) return "";
        var txt = document.createElement("textarea");
        txt.innerHTML = String(str);
        return txt.value;
      }

      // For things that MUST be plain text (like FIB student answers)
      function setPlainText(el, value) {
        el.textContent = value == null ? "" : String(value);
      }

      // --- normalise answers (array / object / string) ---
      function normaliseAnswerField(raw) {
        if (raw == null) return "";
        if (Array.isArray(raw)) {
          return raw.map(function (item) {
            if (item == null) return "";
            if (typeof item === "string") return item;
            return item.answer_title || item.answer_text || item.text || "";
          }).filter(Boolean).join(", ");
        }
        if (typeof raw === "object") {
          if (raw.text || raw.answer_title || raw.answer_text) {
            return raw.text || raw.answer_title || raw.answer_text;
          }
          try {
            return JSON.stringify(raw);
          } catch (e) {
            return String(raw);
          }
        }
        return String(raw);
      }

      // Build question HTML, handling {dash} → blanks for FIB
      function buildQuestionHTML(raw, type) {
        var html = decodeEntities(raw || "");
        var t = (type || "").toLowerCase();
        if (t === "fill_in_the_blank" || t === "fill_in_the_blanks" || t === "fib") {
          // normalise spans with {dash}
          html = html.replace(/<span[^>]*>\s*\{dash\}\s*<\/span>/gi, "{dash}");
          html = html.replace(/\{dash\}/g, '<span class="er-blank"></span>');
        }
        return html;
      }

      // --- MathJax re-typeset for dynamically injected content ---
      let mathReadyPromise = null;

      function ensureMathReady() {
        if (!mathReadyPromise) {
          mathReadyPromise = new Promise(function (resolve, reject) {
            function wait() {
              const mj = window.MathJax;
              if (mj && (mj.typesetPromise || mj.typeset)) {
                if (mj.startup && mj.startup.promise) {
                  mj.startup.promise.then(function () {
                    resolve(mj);
                  }).catch(reject);
                } else {
                  resolve(mj);
                }
              } else {
                setTimeout(wait, 100);
              }
            }
            wait();
          });
        }
        return mathReadyPromise;
      }

      function typesetMath() {
        const shell = resultShell;
        if (!shell) return;

        ensureMathReady()
          .then(function (mj) {
            try {
              if (mj.typesetPromise) {
                return mj.typesetPromise([shell]);
              } else if (mj.typeset) {
                mj.typeset([shell]);
              }
            } catch (err) {
              console.error("MathJax typeset error", err);
            }
          })
          .catch(function (err) {
            console.error("MathJax failed to become ready", err);
          });
      }

      /* ---------- main renderer ---------- */

      function renderResult(payloadRaw) {
        // Support payload.data or direct
        var payload = payloadRaw && payloadRaw.data ? payloadRaw.data : payloadRaw || {};

        var quiz      = payload.quiz    || {};
        var attempt   = payload.attempt || {};
        var result    = payload.result  || {};
        var questions = payload.questions || [];

        var quizTitle = quiz.name || quiz.quiz_name || "Quiz Result";
        quizTitleEl.textContent      = quizTitle;

        var submittedAt = attempt.finished_at || attempt.submitted_at || null;
        var startedAt   = attempt.started_at  || null;

        attemptMetaEl.textContent =
          "Attempt on " + formatDateTime(submittedAt) +
          " | Started at " + formatDateTime(startedAt);

        var totalMarks     = Number(result.total_marks || 0);
        var obtainedMarks  = Number(result.marks_obtained || 0);
        var percent        = totalMarks
          ? (obtainedMarks / Math.max(1, totalMarks)) * 100
          : 0;

        var totalQ   = Number(result.total_questions || (questions ? questions.length : 0) || 0);
        var correctQ = Number(result.total_correct || 0);
        var wrongQ   = Number(result.total_incorrect || 0);
        var skippedQ = Number(result.total_skipped || Math.max(0, totalQ - correctQ - wrongQ));
        var attemptedQ = Math.max(0, totalQ - skippedQ);

        var timeSpentSec = Number(attempt.time_used_sec || result.time_used_sec || 0);

        var chipLabel = "Overall";
        if (percent >= 90)      chipLabel = "Excellent";
        else if (percent >= 75) chipLabel = "Great work";
        else if (percent >= 50) chipLabel = "Keep improving";
        else                    chipLabel = "Needs practice";

        scoreChipEl.innerHTML =
          '<i class="fa-solid fa-award"></i> ' + chipLabel;

        percentEl.textContent   = String(Math.round(percent)) + "%";
        marksEl.textContent     = obtainedMarks + " / " + (totalMarks || "-");
        attemptedEl.textContent = attemptedQ + " / " + (totalQ || "-");
        timeSpentEl.textContent = formatDuration(timeSpentSec);

        scoreTextEl.innerHTML =
          "You answered <strong>" + attemptedQ +
          "</strong> out of <strong>" + totalQ +
          "</strong> questions. You got <strong>" + correctQ +
          "</strong> correct and <strong>" + wrongQ + "</strong> wrong.";

        var accuracy = totalQ ? (correctQ / totalQ) * 100 : 0;
        requestAnimationFrame(function () {
          scoreBarEl.style.width = Math.min(100, Math.max(0, accuracy)) + "%";
        });
        barLabelEl.textContent = "Accuracy: " + Math.round(accuracy) + "%";

        correctCountEl.textContent = correctQ + " correct";
        wrongCountEl.textContent   = wrongQ + " wrong";
        skippedCountEl.textContent = skippedQ + " not attempted";

attemptIdEl.textContent = attempt.attempt_uuid || attempt.attempt_id || "-";
        submittedAtEl.textContent = formatDateTime(submittedAt);

        // --- Question-wise cards ---
        questionListEl.innerHTML = "";

        if (!questions || !questions.length) {
          noQuestionsEl.classList.remove("d-none");
          questionListEl.classList.add("d-none");
          typesetMath();
          return;
        }
        noQuestionsEl.classList.add("d-none");
        questionListEl.classList.remove("d-none");

        var frag = document.createDocumentFragment();

        // For relative time bars
        var maxTimeSec = 0;
        questions.forEach(function (q) {
          var t = Number(q.time_spent_sec || q.time_spent_seconds || q.time_spent || 0);
          if (t > maxTimeSec) maxTimeSec = t;
        });

        questions.forEach(function (q, idx) {
          var card = document.createElement("div");
          card.className = "er-qcard";

          var qNo       = q.order || q.question_order || (idx + 1);

          var typeRaw   = q.type || q.question_type || "";
          var type      = (typeRaw || "").toLowerCase();

          var textRaw   = q.title || q.question_title || q.question || "";
          var descRaw   = q.description || q.question_description || "";

// With this:
var yourAnsRaw = "";
if (q.selected_text && String(q.selected_text).trim() !== "") {
    yourAnsRaw = q.selected_text;
} else if (Array.isArray(q.selected_answer_ids) && q.selected_answer_ids.length && Array.isArray(q.answers)) {
    var chosenIds = q.selected_answer_ids.map(Number);
    var chosenLabels = q.answers
        .filter(function(a){ return chosenIds.includes(Number(a.answer_id)); })
        .map(function(a){ return a.title; });
    yourAnsRaw = chosenLabels.join(", ");
}
var correctAnsRaw = "";
if (Array.isArray(q.answers) && q.answers.length) {
    var correctOpts = q.answers.filter(function(a){ return Number(a.is_correct) === 1; });
    correctAnsRaw = correctOpts.map(function(a){ return a.title; }).join(", ");
}
// FIB fallback
if (!correctAnsRaw && q.selected_text !== undefined) {
    // no correct_text exposed for FIB in API, leave blank or handle separately
}
          var yourAns    = normaliseAnswerField(yourAnsRaw);
          var correctAns = normaliseAnswerField(correctAnsRaw);

          var isCorrect = Number(q.is_correct || 0) === 1;
          var isSkipped = (!yourAns || String(yourAns).trim() === "") 
    && !(Array.isArray(q.selected_answer_ids) && q.selected_answer_ids.length > 0);

          var statusClass = "skipped";
          var statusIcon  = "circle";
          var statusLabel = "Skipped";

          if (isCorrect) {
            statusClass = "correct";
            statusIcon  = "check";
            statusLabel = "Correct";
          } else if (!isSkipped) {
            statusClass = "wrong";
            statusIcon  = "xmark";
            statusLabel = "Wrong";
          }

          var marksAwarded = Number(q.awarded_mark || q.marks_obtained || q.awarded_marks || 0);
          var marksTotal   = Number(q.mark || q.total_mark || q.question_mark || 0);
          var qTime        = Number(q.time_spent_sec || q.time_spent_seconds || q.time_spent || 0);

          var share = 0;
          if (maxTimeSec > 0 && qTime > 0) {
            share = (qTime / maxTimeSec) * 100;
            if (share < 6) share = 6; // minimum visible bar
          }

          var yourAnsDisplay    = yourAns && String(yourAns).trim() !== "" ? yourAns : "-";
          var correctAnsDisplay = correctAns && String(correctAns).trim() !== "" ? correctAns : "-";

          /* ---- build DOM ---- */

          var head = document.createElement("div");
          head.className = "er-qcard-head";

          var left = document.createElement("div");
          left.className = "er-q-left";

          var badge = document.createElement("span");
          badge.className = "er-q-badge";
          badge.textContent = "Q" + qNo;

          var statusPill = document.createElement("span");
          statusPill.className = "er-q-status " + statusClass;
          statusPill.innerHTML =
            '<i class="fa-solid fa-' + statusIcon + '"></i>' +
            '<span>' + statusLabel + '</span>';

          left.appendChild(badge);
          left.appendChild(statusPill);

          var meta = document.createElement("div");
          meta.className = "er-q-meta";

          var metaMarks = document.createElement("span");
          metaMarks.innerHTML = 'Marks: <strong>' + marksAwarded + ' / ' + marksTotal + '</strong>';

          var metaTime = document.createElement("span");
          metaTime.innerHTML  = 'Time: <strong>' + formatDuration(qTime) + '</strong>';

          meta.appendChild(metaMarks);
          meta.appendChild(metaTime);

          head.appendChild(left);
          head.appendChild(meta);
          card.appendChild(head);

          var qWrap = document.createElement("div");
          qWrap.className = "er-q-question";

          var qMain = document.createElement("div");
          qMain.className = "er-q-question-main";
          qMain.innerHTML = buildQuestionHTML(textRaw, type);

          qWrap.appendChild(qMain);

          if (descRaw) {
            var qDesc = document.createElement("div");
            qDesc.className = "er-q-question-desc";
            qDesc.innerHTML = buildQuestionHTML(descRaw, type);
            qWrap.appendChild(qDesc);
          }

          card.appendChild(qWrap);

          var answersWrap = document.createElement("div");
          answersWrap.className = "er-qcard-answers";

          var correctBlock = document.createElement("div");
          correctBlock.className = "er-q-answer-block correct";

          var correctLabel = document.createElement("div");
          correctLabel.className = "er-q-answer-label";
          correctLabel.textContent = "Correct answer";

          var correctTextEl = document.createElement("div");
          correctTextEl.className = "er-q-answer-text";

          var yourBlock = document.createElement("div");
          yourBlock.className = "er-q-answer-block your";

          var yourLabel = document.createElement("div");
          yourLabel.className = "er-q-answer-label";
          yourLabel.textContent = "Your answer";

          var yourTextEl = document.createElement("div");
          yourTextEl.className = "er-q-answer-text";

          var isFibType = (type === "fill_in_the_blank" || type === "fill_in_the_blanks" || type === "fib");

          if (isFibType) {
            setPlainText(correctTextEl, correctAnsDisplay);
            setPlainText(yourTextEl, yourAnsDisplay);
          } else {
            correctTextEl.innerHTML = decodeEntities(correctAnsDisplay);
            yourTextEl.innerHTML    = decodeEntities(yourAnsDisplay);
          }

          correctBlock.appendChild(correctLabel);
          correctBlock.appendChild(correctTextEl);

          yourBlock.appendChild(yourLabel);
          yourBlock.appendChild(yourTextEl);

          answersWrap.appendChild(correctBlock);
          answersWrap.appendChild(yourBlock);

          card.appendChild(answersWrap);

          var timeWrap = document.createElement("div");
          timeWrap.className = "er-q-time";

          var timeTop = document.createElement("div");
          timeTop.className = "er-q-time-top";

          var timeLabel = document.createElement("span");
          timeLabel.textContent = "Time spent on this question";

          var timeValue = document.createElement("span");
          timeValue.textContent = formatDuration(qTime);

          timeTop.appendChild(timeLabel);
          timeTop.appendChild(timeValue);

          var barBg = document.createElement("div");
          barBg.className = "er-q-time-bar-bg";

          var barFill = document.createElement("div");
          barFill.className = "er-q-time-bar-fill";
          barFill.style.width = share + "%";

          barBg.appendChild(barFill);

          timeWrap.appendChild(timeTop);
          timeWrap.appendChild(barBg);

          card.appendChild(timeWrap);

          frag.appendChild(card);
        });

        questionListEl.appendChild(frag);

        // Trigger MathJax after DOM is in place (call twice for safety)
        typesetMath();
        setTimeout(typesetMath, 500);
      }

      async function loadResult() {
        var token = getToken();
        if (!token) {
          clearAuthStorage();
          window.location.replace("/login");
          return;
        }

        showLoader(true);
        if (errorEl) {
          errorEl.classList.remove("show");
          errorEl.textContent = "";
        }

        try {
          var res = await fetch("/api/exam/results/" + encodeURIComponent(RESULT_ID), {
            method: "GET",
            headers: {
              "Accept": "application/json",
              "Authorization": "Bearer " + token
            }
          });

          var json;
          try {
            json = await res.json();
          } catch (e) {
            json = {};
          }

          if (res.status === 401 || res.status === 403) {
            clearAuthStorage();
            window.location.replace("/login");
            return;
          }

          if (!res.ok) {
            throw new Error(json.message || json.error || "Failed to load result.");
          }

          renderResult(json);
        } catch (err) {
          console.error(err);
          showError(err.message || "Failed to load result.");
        } finally {
          showLoader(false);
        }
      }

      if (pdfBtn) {
        pdfBtn.addEventListener("click", function () {
          window.print();
        });
      }

      if (htmlBtn) {
        htmlBtn.addEventListener("click", function () {
          window.open("/api/exam/results/" + encodeURIComponent(RESULT_ID) + "/export", "_blank");
        });
      }

      loadResult();
    }

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", initResultPage);
    } else {
      initResultPage();
    }
  })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
