{{-- resources/views/modules/exam/examResult.blade.php --}}
@section('title','Exam Result')

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Exam Result</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

  {{-- ✅ DOMPurify (sanitize HTML safely before inserting innerHTML) --}}
  <script defer src="https://cdn.jsdelivr.net/npm/dompurify@3.1.7/dist/purify.min.js"></script>

  {{-- ✅ MathJax (LaTeX renderer) --}}
  <script>
    window.MathJax = {
      tex: {
        inlineMath: [['$', '$'], ['\\(', '\\)']],
        displayMath: [['$$', '$$'], ['\\[', '\\]']],
        processEscapes: true,
        processEnvironments: true
      },
      options: {
        skipHtmlTags: ['script','noscript','style','textarea','pre','code']
      }
    };
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

  <style>
    /* ========= Theme Bridge (fallbacks if a token is missing) ========= */
    :root{
      --ink: var(--ink, #0f172a);
      --muted: var(--muted, #64748b);
      --surface: var(--surface, #ffffff);
      --surface-2: var(--surface-2, #f8fafc);
      --line: var(--line-strong, #e5e7eb);
      --brand: var(--accent-color, #4f46e5);
      --shadow-1: var(--shadow-1, 0 1px 2px rgba(0,0,0,.05));
      --shadow-2: var(--shadow-2, 0 6px 16px rgba(0,0,0,.08));
      --success: #16a34a;
      --danger: #dc2626;
      --warning: #d97706;
      --ring-bg: var(--ring-bg, #e5e7eb);
      --bg: var(--bg, #f1f5f9);
      --radius: 16px;
      --radius-sm: 12px;
      --radius-lg: 20px;
      --pad: 16px;
      --pad-lg: 20px;
      --transition: 200ms ease;
    }
    html.theme-dark :root{
      --surface: var(--surface, #0b1220);
      --surface-2: var(--surface-2, #0f172a);
      --bg: var(--bg, #0a0f1a);
      --line: var(--line-strong, #273244);
      --ring-bg: #1f2937;
    }

    body{ background:var(--bg); color:var(--ink); font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,"Apple Color Emoji","Segoe UI Emoji"; }
    .wrap{ max-width:1200px; margin:24px auto 40px; padding:0 12px; display:grid; grid-template-columns: 1fr; gap:16px; }
    @media(min-width:1100px){ .wrap{ grid-template-columns: 1.15fr .85fr; } }

    .panel{ background:var(--surface); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow-2); padding:var(--pad); }
    .panel.compact{ padding:12px; }

    .hero{ position:relative; overflow:hidden; border-radius:var(--radius); background:linear-gradient(135deg, rgba(79,70,229,.12), rgba(99,102,241,.08)); border:1px solid var(--line); padding:18px; }
    .hero .top{ display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .hero .title{ font-size:20px; font-weight:800; letter-spacing:.2px; }
    .hero .sub{ font-size:13px; color:var(--muted); margin-top:4px; }
    .hero .actions{ display:flex; gap:10px; flex-wrap:wrap; }

    .btn{ display:inline-flex; align-items:center; gap:8px; padding:10px 12px; border-radius:12px; border:1px solid var(--line); background:var(--surface); cursor:pointer; font-weight:700; transition:transform var(--transition), border-color var(--transition), background var(--transition); }
    .btn:hover{ border-color:var(--brand); transform:translateY(-1px); }
    .btn.brand{ background:var(--brand); color:#fff; border-color:var(--brand); }
    .btn.ghost{ background:transparent; }
    .btn:disabled{ opacity:.6; cursor:default; }

    .score-card{ display:flex; align-items:center; gap:16px; }
    .ring{
      --p: 0;
      width:96px; height:96px; border-radius:50%;
      background: conic-gradient(var(--brand) calc(var(--p)*1%), var(--ring-bg) 0);
      display:grid; place-items:center;
      border:6px solid var(--surface); outline:1px solid var(--line);
    }
    .ring > .inner{ width:72px; height:72px; border-radius:50%; background:var(--surface); display:grid; place-items:center; border:1px solid var(--line); }
    .ring .pct{ font-weight:900; font-size:18px; }
    .score-meta .big{ font-size:28px; font-weight:900; }
    .score-meta .muted{ color:var(--muted); font-size:12px; }

    .kpis{ display:grid; gap:12px; grid-template-columns: repeat(2,1fr); }
    @media(min-width:680px){ .kpis{ grid-template-columns: repeat(4,1fr); } }
    .kpi{ border:1px solid var(--line); background:var(--surface); border-radius:14px; padding:14px; }
    .kpi .label{ font-size:12px; color:var(--muted); }
    .kpi .value{ font-weight:800; font-size:22px; margin-top:6px; }
    .kpi.pass{ outline:1px solid rgba(22,163,74,.15); }
    .kpi.fail{ outline:1px solid rgba(220,38,38,.15); }

    .tools{ display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-top:10px; }
    .seg{ display:flex; gap:6px; padding:6px; border:1px solid var(--line); border-radius:999px; background:var(--surface); }
    .seg button{ border:0; background:transparent; padding:8px 12px; border-radius:999px; font-weight:700; color:var(--muted); cursor:pointer; }
    .seg button.active{ background:var(--brand); color:#fff; }
    .search{ display:flex; gap:8px; align-items:center; background:var(--surface); border:1px solid var(--line); border-radius:12px; padding:8px 10px; min-width:220px; }
    .search input{ border:0; outline:none; background:transparent; color:var(--ink); width:220px; }
    .toggle{ display:flex; gap:10px; align-items:center; }
    .badge{ display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; border:1px solid var(--line); font-size:12px; background:var(--surface); }
    .badge.pass{ background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
    .badge.fail{ background:#fef2f2; color:#991b1b; border-color:#fecaca; }

    .questions{ display:grid; gap:12px; }
    .q{ border:1px solid var(--line); border-radius:14px; background:var(--surface); padding:14px; transition:box-shadow var(--transition), transform var(--transition); }
    .q:hover{ box-shadow:var(--shadow-2); transform:translateY(-1px); }
    .qhead{ display:flex; justify-content:space-between; gap:10px; align-items:flex-start; }
    .qtitle{ font-weight:800; }
    .qdesc{ margin-top:8px; color:var(--ink); font-size:14px; line-height:1.45; }
    .qmeta{ font-size:12px; color:var(--muted); display:flex; gap:10px; flex-wrap:wrap; margin-top:6px; }
    .pill{ font-size:11px; border:1px solid var(--line); border-radius:999px; padding:4px 8px; white-space:nowrap; }
    .answers{ margin-top:10px; display:grid; gap:8px; }
    .ans{ padding:10px; border-radius:12px; border:1px dashed var(--line); display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .ans.correct{ background:#f0fdf4; border-color:#86efac; }
    .ans.chosen{ outline:2px solid rgba(79,70,229,.25); }
    .left{ display:flex; align-items:center; gap:10px; }
    .tick{ width:22px; height:22px; border-radius:999px; display:inline-grid; place-items:center; border:1px solid var(--line); font-size:12px; }

    .summary .head{ font-weight:900; margin-bottom:10px; }
    .summary .row{ display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px dashed var(--line); }
    .summary .row:last-child{ border-bottom:0; }
    .summary .key{ color:var(--muted); font-size:13px; }
    .summary .val{ font-weight:800; }

    .divider{ height:1px; background:var(--line); margin:12px 0; }
    .muted{ color:var(--muted); }
    .spinner{ width:18px; height:18px; border:3px solid var(--line); border-top-color:var(--brand); border-radius:50%; animation:spin .9s linear infinite; }
    @keyframes spin{ to{ transform:rotate(360deg); } }
    .hide{ display:none !important; }
    .error{ background:#fef2f2; border:1px solid #fecaca; padding:12px; border-radius:12px; color:#7f1d1d; }
    .empty{ background:var(--surface-2); border:1px dashed var(--line); border-radius:12px; padding:16px; text-align:center; color:var(--muted); }
    .skeleton{ position:relative; overflow:hidden; background:linear-gradient(90deg, rgba(0,0,0,0.06), rgba(0,0,0,0.04), rgba(0,0,0,0.06)); border-radius:12px; min-height:64px; }

    /* ✅ Make HTML content look nice inside options/questions */
    .qtitle p, .qdesc p, .ans .left div p{ margin:0 0 8px; }
    .qtitle img, .qdesc img, .ans .left img{ max-width:100%; height:auto; border-radius:10px; }
    .qdesc table, .ans .left table{ width:100%; border-collapse:collapse; overflow:hidden; border-radius:12px; }
    .qdesc table td, .qdesc table th, .ans .left table td, .ans .left table th{
      border:1px solid var(--line); padding:8px; vertical-align:top;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="panel">
      <div class="hero">
        <div class="top">
          <div>
            <div class="title" id="quizTitle">Exam Result</div>
            <div class="sub" id="quizSub">Loading…</div>
          </div>
          <div class="actions">
            <button class="btn" id="btnToggleAnswers"><i class="fa-regular fa-eye"></i><span>Hide answers</span></button>
            <button class="btn" id="btnPrint"><i class="fa-solid fa-print"></i> Print</button>
            <button class="btn brand" id="btnDocx"><i class="fa-regular fa-file-word"></i> DOCX</button>
            <button class="btn" id="btnHtml"><i class="fa-regular fa-file-lines"></i> HTML</button>
          </div>
        </div>

        <div class="divider"></div>

        <div class="score-card">
          <div class="ring" id="scoreRing" style="--p:0;">
            <div class="inner"><div class="pct" id="ringPct">0%</div></div>
          </div>
          <div class="score-meta">
            <div class="big" id="scoreBig">—</div>
            <div class="muted" id="scoreMeta">Accuracy — | Time —</div>
            <div class="badges" id="passBadge" style="margin-top:6px;"></div>
          </div>
        </div>

        <div class="kpis" style="margin-top:12px">
          <div class="kpi" id="kpiScore"><div class="label">Score</div><div class="value">—</div></div>
          <div class="kpi" id="kpiAccuracy"><div class="label">Accuracy</div><div class="value">—</div></div>
          <div class="kpi" id="kpiCorrect"><div class="label">Correct</div><div class="value">—</div></div>
          <div class="kpi" id="kpiTime"><div class="label">Time Used</div><div class="value">—</div></div>
        </div>

        <div class="tools">
          <div class="seg" role="tablist" aria-label="Filter questions">
            <button class="active" data-filter="all"><i class="fa-solid fa-layer-group"></i> All</button>
            <button data-filter="correct"><i class="fa-solid fa-check"></i> Correct</button>
            <button data-filter="wrong"><i class="fa-solid fa-xmark"></i> Wrong</button>
            <button data-filter="skipped"><i class="fa-regular fa-circle"></i> Skipped</button>
          </div>

          <div class="toggle">
            <div class="search">
              <i class="fa-solid fa-magnifying-glass muted"></i>
              <input id="searchBox" placeholder="Search question…" />
            </div>
            <span class="badge" id="countBadge"><i class="fa-regular fa-square-check"></i> 0 shown</span>
          </div>
        </div>
      </div>

      <div id="stateLoading" style="margin-top:14px;">
        <div class="skeleton" style="height:90px;"></div>
        <div class="skeleton" style="height:90px; margin-top:10px;"></div>
        <div class="skeleton" style="height:90px; margin-top:10px;"></div>
      </div>
      <div id="stateError" class="error hide"></div>
      <div id="stateEmpty" class="empty hide">No questions to show.</div>

      <div class="questions" id="questionsWrap" style="margin-top:12px;"></div>
    </div>

    <div class="panel summary compact">
      <div class="head">Attempt Summary</div>
      <div class="row"><div class="key">Attempt #</div><div class="val" id="sumAttemptNo">—</div></div>
      <div class="row"><div class="key">Status</div><div class="val" id="sumStatus">—</div></div>
      <div class="row"><div class="key">Started</div><div class="val" id="sumStart">—</div></div>
      <div class="row"><div class="key">Finished</div><div class="val" id="sumFinish">—</div></div>
      <div class="row"><div class="key">Total Questions</div><div class="val" id="sumQ">—</div></div>
      <div class="row"><div class="key">Correct</div><div class="val" id="sumC">—</div></div>
      <div class="row"><div class="key">Wrong</div><div class="val" id="sumW">—</div></div>
      <div class="row"><div class="key">Skipped</div><div class="val" id="sumS">—</div></div>
      <div class="row"><div class="key">Total Time</div><div class="val" id="sumT">—</div></div>
      <div class="divider"></div>
      <div class="head">Quiz</div>
      <div class="row"><div class="key">Name</div><div class="val" id="sumQuiz">—</div></div>
      <div class="row"><div class="key">Allotted</div><div class="val" id="sumAllotted">—</div></div>
    </div>
  </div>

  <script>
  (function () {
    const apiBase = `${location.origin}/api`;
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';

    /* ===== Helpers ===== */
    function getResultId() {
      const url = new URL(location.href);
      const qId = url.searchParams.get('resultId') || url.searchParams.get('result');
      if (qId && /^\d+$/.test(qId)) return qId;
      const parts = location.pathname.split('/').filter(Boolean);
      const ix = parts.findIndex(p => p === 'results' || p === 'result');
      if (ix >= 0 && parts[ix+1] && /^\d+$/.test(parts[ix+1])) return parts[ix+1];
      for (const p of parts) if (/^\d+$/.test(p)) return p;
      return null;
    }
    function fmtDate(dt) {
      if (!dt) return '—';
      try { return new Date((dt+'').replace(' ','T')).toLocaleString(); } catch(e){ return dt; }
    }
    function fmtDur(sec) {
      sec = Math.max(0, parseInt(sec||0,10));
      const h = Math.floor(sec/3600), m = Math.floor((sec%3600)/60), s = sec%60;
      const hh = h>0 ? (h+':') : '';
      return hh + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    }

    // ✅ Safe HTML setter (DOMPurify sanitize, then innerHTML)
    function setHtml(el, html) {
      const s = String(html ?? '');
      if (window.DOMPurify) {
        el.innerHTML = window.DOMPurify.sanitize(s, {
          // allow common formatting + images + tables
          ALLOWED_TAGS: [
            'b','strong','i','em','u','s','br','hr','span','div','p',
            'ul','ol','li',
            'h1','h2','h3','h4','h5','h6',
            'table','thead','tbody','tr','th','td',
            'img','a','code','sup','sub','small'
          ],
          ALLOWED_ATTR: ['href','target','rel','src','alt','title','style','class']
        });
        return;
      }
      // fallback: trusted HTML only
      el.innerHTML = s;
    }

    // ✅ MathJax re-typeset after dynamic DOM injection
    function typesetMath() {
      try {
        if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
          window.MathJax.typesetClear && window.MathJax.typesetClear();
          return window.MathJax.typesetPromise();
        }
      } catch(e){}
      return Promise.resolve();
    }

    const PASS_THRESHOLD = 60;

    /* ===== Refs ===== */
    const els = {
      quizTitle: document.getElementById('quizTitle'),
      quizSub: document.getElementById('quizSub'),
      scoreRing: document.getElementById('scoreRing'),
      ringPct: document.getElementById('ringPct'),
      scoreBig: document.getElementById('scoreBig'),
      scoreMeta: document.getElementById('scoreMeta'),
      passBadge: document.getElementById('passBadge'),
      kKpiScore: document.querySelector('#kpiScore .value'),
      kKpiAccuracy: document.querySelector('#kpiAccuracy .value'),
      kKpiCorrect: document.querySelector('#kpiCorrect .value'),
      kKpiTime: document.querySelector('#kpiTime .value'),
      kpiAccuracyBox: document.getElementById('kpiAccuracy'),
      loading: document.getElementById('stateLoading'),
      error: document.getElementById('stateError'),
      empty: document.getElementById('stateEmpty'),
      qwrap: document.getElementById('questionsWrap'),
      btnToggle: document.getElementById('btnToggleAnswers'),
      btnPrint: document.getElementById('btnPrint'),
      btnDocx: document.getElementById('btnDocx'),
      btnHtml: document.getElementById('btnHtml'),
      search: document.getElementById('searchBox'),
      countBadge: document.getElementById('countBadge'),
      sAttemptNo: document.getElementById('sumAttemptNo'),
      sStatus: document.getElementById('sumStatus'),
      sStart: document.getElementById('sumStart'),
      sFinish: document.getElementById('sumFinish'),
      sQ: document.getElementById('sumQ'),
      sC: document.getElementById('sumC'),
      sW: document.getElementById('sumW'),
      sS: document.getElementById('sumS'),
      sT: document.getElementById('sumT'),
      sQuiz: document.getElementById('sumQuiz'),
      sAllotted: document.getElementById('sumAllotted'),
    };

    let answersVisible = true;
    let current = null;
    let resultId = getResultId();
    let filterMode = 'all';
    let searchTerm = '';

    function show(el){ el.classList.remove('hide'); }
    function hide(el){ el.classList.add('hide'); }

    function setRing(pct){
      pct = Math.max(0, Math.min(100, Number(pct||0)));
      els.scoreRing.style.setProperty('--p', pct);
      els.ringPct.textContent = pct.toFixed(2) + '%';
    }

    function setHeader(data){
      const r = data.result, a = data.attempt, q = data.quiz;
      const scoreText = `${r.marks_obtained} / ${r.total_marks}`;
      const pct = Number(r.percentage || 0);
      const timeUsed = a.time_used_sec ?? 0;
      const totalSec = a.total_time_sec ?? (q.total_time ? q.total_time*60 : 0);

      els.quizTitle.textContent = q.name || 'Exam Result';
      els.quizSub.textContent = `Attempt #${r.attempt_number ?? 1} • ${String(a.status||'').toUpperCase()} • Started ${fmtDate(a.started_at)} • Finished ${fmtDate(a.finished_at)}`;

      setRing(pct);
      els.scoreBig.textContent = scoreText;
      els.scoreMeta.textContent = `Accuracy ${pct.toFixed(2)}%  |  Time ${ totalSec ? (fmtDur(timeUsed)+' / '+fmtDur(totalSec)) : fmtDur(timeUsed) }`;

      els.passBadge.innerHTML = '';
      const pass = pct >= PASS_THRESHOLD;
      const div = document.createElement('div');
      div.className = 'badge ' + (pass ? 'pass' : 'fail');
      div.innerHTML = `<i class="fa-solid ${pass ? 'fa-check' : 'fa-xmark'}"></i> <b>${pass ? 'PASS' : 'FAIL'}</b>`;
      els.passBadge.appendChild(div);

      els.kKpiScore.textContent = scoreText;
      els.kKpiAccuracy.textContent = pct.toFixed(2) + '%';
      els.kKpiCorrect.textContent = `${r.total_correct} / ${r.total_questions}`;
      els.kKpiTime.textContent = totalSec ? `${fmtDur(timeUsed)} / ${fmtDur(totalSec)}` : fmtDur(timeUsed);

      els.kpiAccuracyBox.classList.toggle('pass', pass);
      els.kpiAccuracyBox.classList.toggle('fail', !pass);

      els.sAttemptNo.textContent = (r.attempt_number ?? 1);
      els.sStatus.textContent = String(a.status || '').toUpperCase();
      els.sStart.textContent = fmtDate(a.started_at);
      els.sFinish.textContent = fmtDate(a.finished_at);
      els.sQ.textContent = r.total_questions;
      els.sC.textContent = r.total_correct;
      els.sW.textContent = r.total_incorrect;
      els.sS.textContent = r.total_skipped;
      els.sT.textContent = totalSec ? `${fmtDur(timeUsed)} / ${fmtDur(totalSec)}` : fmtDur(timeUsed);
      els.sQuiz.textContent = q.name || '—';
      els.sAllotted.textContent = q.total_time ? `${q.total_time} min` : '—';

      document.title = `${q.name || 'Exam Result'} • ${scoreText}`;
    }

    function statusOf(q){
      const sel = q.selected_answer_ids;
      const isCorrect = (q.is_correct ?? 0) === 1;
      const skipped =
        (sel === null || (Array.isArray(sel) && sel.length === 0)) &&
        !(q.selected_text && String(q.selected_text).trim() !== '');
      if (skipped) return 'skipped';
      return isCorrect ? 'correct' : 'wrong';
    }

    async function renderQuestions(data){
      const list = Array.isArray(data.questions) ? data.questions.slice() : [];
      els.qwrap.innerHTML = '';

      const filtered = list.filter(q => {
        const st = statusOf(q);
        const hitFilter = (filterMode === 'all') ? true : (st === filterMode);
        if (!hitFilter) return false;
        if (!searchTerm) return true;
        const hay = `${q.title||''} ${q.description||''}`.toLowerCase();
        return hay.includes(searchTerm);
      });

      els.countBadge.innerHTML = `<i class="fa-regular fa-square-check"></i> ${filtered.length} shown`;

      if (!filtered.length){
        hide(els.loading); show(els.empty);
        await typesetMath();
        return;
      }
      hide(els.loading); hide(els.empty);

      filtered.forEach(q => {
        const card = document.createElement('div');
        card.className = 'q';

        const correct = (q.is_correct ?? 0) === 1;
        const markStr = `${q.awarded_mark ?? 0} / ${q.mark ?? 0}`;
        const timeStr = fmtDur(q.time_spent_sec ?? 0);

        const head = document.createElement('div');
        head.className = 'qhead';

        const leftBlock = document.createElement('div');

        const title = document.createElement('div');
        title.className = 'qtitle';
        // ✅ HTML render (question title may contain HTML + LaTeX)
        setHtml(title, `Q${q.order}. ${q.title ?? ''}`);
        leftBlock.appendChild(title);

        const descText = (q.description ?? '').toString().trim();
        if (descText) {
          const desc = document.createElement('div');
          desc.className = 'qdesc';
          // ✅ HTML render (description may contain HTML + LaTeX)
          setHtml(desc, descText);
          leftBlock.appendChild(desc);
        }

        const meta = document.createElement('div');
        meta.className = 'qmeta';
        meta.innerHTML = `
          <span>Type: ${(q.type || '—')}</span>
          <span>Marks: ${markStr}</span>
          <span>Time: ${timeStr}</span>
        `;
        leftBlock.appendChild(meta);

        const rightBlock = document.createElement('div');
        const pill = document.createElement('span');
        pill.className = 'pill ' + (correct ? '' : 'muted');
        pill.innerHTML = correct
          ? `<i class="fa-solid fa-check"></i> Correct`
          : `<i class="fa-solid fa-xmark"></i> Incorrect`;
        rightBlock.appendChild(pill);

        head.appendChild(leftBlock);
        head.appendChild(rightBlock);

        const ansWrap = document.createElement('div');
        ansWrap.className = 'answers ' + (answersVisible ? '' : 'hide');

        const chosenIds = (q.selected_answer_ids && Array.isArray(q.selected_answer_ids)) ? q.selected_answer_ids.map(Number) : [];

        if (q.type === 'fill_in_the_blank') {
          const a = document.createElement('div');
          a.className = 'ans chosen ' + (correct ? 'correct' : '');
          a.innerHTML = `
            <div class="left">
              <span class="tick">${correct?'<i class="fa-solid fa-check"></i>':'<i class="fa-solid fa-xmark"></i>'}</span>
              <div>
                <div><b>Your answer:</b> <span class="fibText"></span></div>
                <div class="muted" style="font-size:12px">FIB</div>
              </div>
            </div>
          `;
          // ✅ if typed answer includes HTML/LaTeX
          setHtml(a.querySelector('.fibText'), q.selected_text ? String(q.selected_text) : '—');
          ansWrap.appendChild(a);
        } else {
          const options = Array.isArray(q.answers) ? q.answers : [];
          options.forEach(opt => {
            const isCorrect = (opt.is_correct ?? 0) === 1;
            const isChosen  = chosenIds.includes(Number(opt.answer_id));

            const row = document.createElement('div');
            row.className = 'ans' + (isCorrect ? ' correct' : '') + (isChosen ? ' chosen' : '');

            const left = document.createElement('div');
            left.className = 'left';

            const tick = document.createElement('span');
            tick.className = 'tick';
            tick.innerHTML = isChosen ? '<i class="fa-solid fa-check-double"></i>' : '';

            const text = document.createElement('div');
            // ✅ HTML render (option title may contain HTML + LaTeX)
            setHtml(text, (opt.title ?? '').toString());

            left.appendChild(tick);
            left.appendChild(text);

            const right = document.createElement('div');
            right.className = 'right';
            if (isCorrect) {
              const p = document.createElement('span');
              p.className = 'pill';
              p.innerHTML = '<i class="fa-solid fa-check"></i> Correct';
              right.appendChild(p);
            }

            row.appendChild(left);
            row.appendChild(right);
            ansWrap.appendChild(row);
          });

          if (!options.length) {
            const none = document.createElement('div');
            none.className = 'muted';
            none.textContent = 'No options provided for this question.';
            ansWrap.appendChild(none);
          }
        }

        card.appendChild(head);
        card.appendChild(ansWrap);
        els.qwrap.appendChild(card);
      });

      // ✅ IMPORTANT: typeset AFTER DOM injection
      await typesetMath();
    }

    async function fetchResult() {
      if (!token) { hide(els.loading); els.error.textContent='Missing token. Please log in again.'; show(els.error); return; }
      if (!resultId){ hide(els.loading); els.error.textContent='Missing result id in URL.'; show(els.error); return; }

      try {
        const res = await fetch(`${apiBase}/exam/results/${resultId}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!res.ok) {
          hide(els.loading);
          let msg = `Failed (${res.status})`;
          try { const j = await res.json(); if (j && j.message) msg = j.message; } catch(e){}
          if (res.status === 401) msg = 'Unauthorized. Please log in again.';
          if (res.status === 403) msg = msg || 'Result is not yet published for students.';
          els.error.textContent = msg; show(els.error); return;
        }

        const data = await res.json();
        if (!data || !data.success) {
          hide(els.loading);
          els.error.textContent = (data && data.message) || 'Unknown error.';
          show(els.error); return;
        }

        current = data;
        setHeader(data);
        await renderQuestions(data);
      } catch (e) {
        hide(els.loading);
        els.error.textContent = 'Network error. Please try again.';
        show(els.error);
      }
    }

    async function downloadExport(format){
      if (!current) return;
      const btn = (format === 'docx') ? els.btnDocx : els.btnHtml;
      btn.disabled = true;
      const old = btn.innerHTML;
      btn.innerHTML = `<span class="spinner"></span> Preparing…`;
      try {
        const url = `${apiBase}/exam/results/${current.result.result_id}/export?format=${encodeURIComponent(format)}`;
        const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
        if (!res.ok) {
          let msg = `Download failed (${res.status})`;
          try { const j = await res.json(); if (j && j.message) msg = j.message; } catch(e){}
          alert(msg);
        } else {
          const blob = await res.blob();
          const a = document.createElement('a');
          const ext = (format === 'docx') ? 'docx' : 'html';
          const name = `exam_result_${current.result.result_id}.${ext}`;
          a.href = URL.createObjectURL(blob);
          a.download = name;
          document.body.appendChild(a);
          a.click();
          a.remove();
          URL.revokeObjectURL(a.href);
        }
      } catch(e){
        alert('Network error while downloading.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = old;
      }
    }

    /* ===== Events ===== */
    els.btnToggle.addEventListener('click', async () => {
      answersVisible = !answersVisible;
      els.btnToggle.querySelector('span').textContent = answersVisible ? 'Hide answers' : 'Show answers';
      document.querySelectorAll('.answers').forEach(el => el.classList.toggle('hide', !answersVisible));
      await typesetMath();
    });

    els.btnPrint.addEventListener('click', () => window.print());
    els.btnDocx.addEventListener('click', () => downloadExport('docx'));
    els.btnHtml.addEventListener('click', () => downloadExport('html'));

    document.querySelectorAll('.seg button').forEach(b => b.addEventListener('click', async (e) => {
      document.querySelectorAll('.seg button').forEach(x => x.classList.remove('active'));
      e.currentTarget.classList.add('active');
      filterMode = e.currentTarget.dataset.filter || 'all';
      if (current) await renderQuestions(current);
    }));

    els.search.addEventListener('input', async (e) => {
      searchTerm = String(e.target.value || '').toLowerCase().trim();
      if (current) await renderQuestions(current);
    });

    /* ===== Start ===== */
    fetchResult();
  })();
  </script>
</body>
</html>
