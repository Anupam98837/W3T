@php
  $quizKey  = $quizKey ?? request()->route('quiz') ?? request()->query('quiz');
  $batchKey = request()->query('batch'); // batch_quiz uuid/id (optional)
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="quiz-key" content="{{ trim((string)$quizKey) }}">
  <meta name="batch-key" content="{{ trim((string)$batchKey) }}">

  <title>Exam</title>

  {{-- Theme --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- Icons + SweetAlert --}}
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  {{-- MathJax --}}
  <script>
    window.MathJax = {
      tex: {
        inlineMath: [['$', '$'], ['\\(', '\\)']],
        displayMath: [['\\[','\\]'], ['$$','$$']],
        processEscapes: true
      },
      options: { skipHtmlTags: ['script','noscript','style','textarea','pre','code'] },
      startup: { typeset: false }
    };
  </script>
  <script id="MathJax-script" defer src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml-full.js"></script>

  <style>
    :root{
      --exam-accent: var(--accent-color, #4f46e5);
      --exam-primary: var(--primary-color, #4f46e5);
      --exam-surface: var(--surface, #ffffff);
      --exam-ink: var(--text-color, #111827);
      --exam-muted: var(--muted-color, #6b7280);
      --exam-line: var(--line-strong, #e5e7eb);
      --exam-line-soft: var(--line-soft, #eef2f7);
      --exam-hover: var(--page-hover, #f7f8fc);
      --exam-bg: var(--page-bg, #f4f5fb);
      --exam-success: var(--t-success, #16a34a);
      --exam-warn: var(--t-warn, #f59e0b);
    }
    body{ background: var(--exam-bg); color: var(--exam-ink); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    .exam-topbar{ background: var(--exam-surface); border-bottom: 1px solid var(--exam-line); box-shadow: 0 6px 18px rgba(15,23,42,.05); z-index: 20; }
    .exam-brand{ display:flex; align-items:center; gap:.6rem; font-weight: 800; letter-spacing:.01em; }
    .exam-brand img{height:32px; width:auto}
    .timer-pill{ padding:.48rem .95rem; border-radius:999px; font-weight:700; font-size:.92rem; background: var(--exam-primary); color:#fff; display:flex; align-items:center; gap:.55rem; box-shadow: 0 10px 18px rgba(0,0,0,.08); user-select:none; }
    .exam-card{ background: var(--exam-surface); border: 1px solid var(--exam-line); border-radius: 16px; box-shadow: 0 10px 30px rgba(15,23,42,.08); }
    .exam-card-slim{ background: var(--exam-surface); border: 1px solid var(--exam-line); border-radius: 16px; box-shadow: 0 6px 18px rgba(15,23,42,.05); }
    .btn{ border-radius:.85rem; font-weight:700; padding:.7rem 1rem; }
    .btn-primary{ background: var(--exam-primary); border-color: var(--exam-primary); }
    .btn-primary:hover{ filter: brightness(.97); }
    .btn-light{ background: var(--exam-surface); border: 1px solid var(--exam-line); color: #334155; font-weight: 700; }
    .btn-light:hover{ background: var(--exam-hover); }
    .nav-grid{ display:grid; grid-template-columns: repeat(6, 1fr); gap:.5rem; }
    @media (max-width: 576px){ .nav-grid{ grid-template-columns: repeat(8, 1fr); } }
    @media (min-width: 992px){
      .col-fixed-260{ flex:0 0 260px; max-width:260px; }
      .nav-grid{ grid-template-columns: repeat(5, 1fr); }
    }
    .nav-btn{
      width:38px; height:38px; border-radius:999px; border:1px solid var(--exam-line);
      background: var(--exam-surface); font-size:.84rem; font-weight:800;
      display:flex; align-items:center; justify-content:center;
      transition: all .10s ease;
      cursor:pointer;
    }
    .nav-btn:hover{ background: var(--exam-hover); }
    .nav-btn.current{ background: var(--exam-accent); border-color: var(--exam-accent); color:#fff; box-shadow: 0 0 0 2px rgba(79,70,229,.15); }
    .nav-btn.answered{ background: var(--exam-success); border-color: var(--exam-success); color:#111827; }
    .nav-btn.review{ background: var(--exam-warn); border-color: var(--exam-warn); color:#111827; }
    .nav-btn.visited{ background: var(--exam-hover); }
    .w3-progress{ height:10px; border-radius:999px; background: var(--exam-line-soft); overflow:hidden; }
    .w3-progress > div{ height:100%; width:0%; background: var(--exam-accent); transition: width .2s ease; }
    .q-title{ font-family: Poppins, Inter, system-ui, sans-serif; font-weight: 700; font-size: 1.03rem; line-height: 1.35; }
    .q-meta{ font-size:.84rem; color: var(--exam-muted); display:flex; align-items:center; flex-wrap: wrap; gap:.45rem; }
    .q-badge{ font-size:.72rem; padding:.18rem .6rem; border-radius:999px; background: color-mix(in oklab, var(--exam-accent) 10%, transparent); border: 1px solid color-mix(in oklab, var(--exam-accent) 25%, transparent); color: var(--exam-accent); font-weight: 800; }
    .opt{ border-radius: 12px; border: 1px solid var(--exam-line-soft); padding: .65rem .75rem; margin-bottom: .42rem; background: var(--exam-surface); cursor: pointer; transition: background .12s ease, border-color .12s ease, box-shadow .12s ease; }
    .opt:hover{ background: var(--exam-hover); border-color: color-mix(in oklab, var(--exam-accent) 45%, var(--exam-line-soft)); box-shadow: 0 10px 18px rgba(15,23,42,.06); }
    .opt input.form-check-input{ margin-top: 0; }
    .opt .form-check-label{ cursor:pointer; font-weight: 650; font-size: .94rem; }
    .fib-underline{ display:inline-block; min-width:90px; border-bottom:2px solid #cbd5e1; margin: 0 .22rem .18rem .22rem; }
    .fib-fields .form-control{ height:40px; border-radius:10px; }
    .skeleton{ position:relative; overflow:hidden; background: var(--exam-line-soft); border-radius: 10px; }
    .skeleton::after{ content:""; position:absolute; inset:0; transform: translateX(-100%); background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.55) 50%, rgba(255,255,255,0) 100%); animation: shimmer 1.2s infinite; }
    @keyframes shimmer{ 100% { transform: translateX(100%); } }
    mjx-container[display="block"]{ display:block !important; margin:.45rem 0; }
    mjx-container{ max-width:100%; overflow-x:auto; overflow-y:hidden; }
  </style>
</head>

<body>
  {{-- ✅ Overlay used ONLY for first load / refresh --}}
  @include('partials.overlay')

  <header class="exam-topbar sticky-top">
    <div class="container-xxl py-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <div class="exam-brand">
          <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
          <span id="exam-title">Exam</span>
        </div>
        <span class="badge rounded-pill text-bg-light border">
          <i class="fa-solid fa-pencil me-1"></i> Live
        </span>
      </div>

      <div id="timer-pill" class="timer-pill">
        <i class="fa-solid fa-clock"></i>
        <span id="time-left">--:--</span>
      </div>
    </div>
  </header>

  <main class="container-xxl py-4">
    <div class="row g-3 g-lg-4">
      <aside class="col-12 col-lg-3 col-fixed-260">
        <div class="exam-card-slim p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 class="fs-6 mb-0 fw-bold">Question Navigator</h2>
            <small class="text-muted">Jump to…</small>
          </div>

          <div id="nav-grid" class="nav-grid mb-3" aria-label="Question navigator"></div>

          <div class="mb-2">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <small class="text-muted"><i class="fa-solid fa-chart-line me-1"></i>Progress</small>
              <strong id="progress-pct" style="color:var(--exam-accent)">0%</strong>
            </div>
            <div class="w3-progress"><div id="progress-bar-fill"></div></div>
            <div class="mt-1">
              <small class="text-muted">
                <span id="progress-count">0</span> of <span id="progress-total">0</span> answered
              </small>
            </div>
          </div>

          <button id="submit-btn" class="btn btn-primary w-100 mt-3">
            <span class="btn-label"><i class="fa-solid fa-paper-plane me-2"></i>Submit Exam</span>
            <span class="btn-spinner d-none">
              <span class="spinner-border spinner-border-sm me-1"></span>Submitting…
            </span>
          </button>
        </div>
      </aside>

      <section class="col-12 col-lg">
        <div id="question-wrap" class="exam-card p-4">
          <div id="q-skeleton">
            <div class="skeleton mb-3" style="height:22px;width:60%"></div>
            <div class="skeleton mb-2" style="height:15px;width:40%"></div>
            <div class="skeleton mb-4" style="height:15px;width:30%"></div>
            <div class="skeleton" style="height:120px;width:100%"></div>
          </div>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center">
          <button id="prev-btn" class="btn btn-light" disabled>
            <i class="fa-solid fa-arrow-left me-2"></i>Previous
          </button>

          <button id="review-btn" class="btn btn-light">
            <i class="fa-solid fa-flag me-2"></i>Mark Review
          </button>

          <button id="next-btn" class="btn btn-primary">
            Next<i class="fa-solid fa-arrow-right ms-2"></i>
          </button>
        </div>
      </section>
    </div>
  </main>

<script>
/* ================= MathJax Typeset Helper ================= */
function typeset(el){
  if (!el) return;
  const run = () => {
    try {
      if (window.MathJax?.typesetClear) MathJax.typesetClear([el]);
      return window.MathJax?.typesetPromise ? MathJax.typesetPromise([el]) : null;
    } catch (e) { console.warn('MathJax typeset error:', e); }
  };
  if (window.MathJax?.startup?.promise) MathJax.startup.promise.then(run);
  else document.getElementById('MathJax-script')?.addEventListener('load', run, { once:true });
}

/* ================= Globals ================= */
const $  = sel => document.querySelector(sel);
const $$ = sel => Array.from(document.querySelectorAll(sel));

const token = sessionStorage.student_token || sessionStorage.token || '';

const QUIZ_KEY =
  (document.querySelector('meta[name="quiz-key"]')?.content || '').trim()
  || new URLSearchParams(location.search).get('quiz') || '';

const BATCH_KEY =
  (document.querySelector('meta[name="batch-key"]')?.content || '').trim()
  || new URLSearchParams(location.search).get('batch') || '';

const EXAM_SCOPE  = BATCH_KEY ? `${QUIZ_KEY}::${BATCH_KEY}` : QUIZ_KEY;

const STORAGE_KEY = 'attempt_uuid:' + EXAM_SCOPE;
const CACHE_KEY   = 'exam_cache:' + EXAM_SCOPE;
const TIME_KEY    = 'exam_time:' + EXAM_SCOPE;

let ATTEMPT_UUID = localStorage.getItem(STORAGE_KEY) || null;

let questions = [];
let selections = {};
let reviews = {};
let visited = {};
let currentIndex = 0;

let timeLeft = 0;
let timerHandle = null;
let isSubmitting = false;

let qStartedAt = null;
let currentQid = null;
let timeSpentMap = {};
let SUBMITTED_OK = false;

/* ================= Overlay Helpers (partials.overlay) ================= */
function getOverlayEl(){
  return document.getElementById('overlay')
    || document.getElementById('overlayLoading')
    || document.getElementById('pageOverlay')
    || document.querySelector('.overlay')
    || document.querySelector('.overlay-loading')
    || document.querySelector('[data-overlay="loading"]')
    || null;
}
function showOverlay(){
  const el = getOverlayEl();
  if (!el) return;
  el.classList.remove('d-none');
  el.style.display = '';
  el.setAttribute('aria-hidden', 'false');
}
function hideOverlay(){
  const el = getOverlayEl();
  if (!el) return;
  el.classList.add('d-none');
  el.style.display = 'none';
  el.setAttribute('aria-hidden', 'true');
}

/* ================= Helpers ================= */
async function api(path, opts={}){
  const res = await fetch(path, {
    ...opts,
    headers: {
      'Content-Type':'application/json',
      'Authorization': `Bearer ${token}`,
      ...(opts.headers||{})
    }
  });

  let data = {};
  try { data = await res.json(); } catch(e){ data = {}; }

  if (!res.ok || data.success === false) {
    throw new Error(data.message || `HTTP ${res.status}`);
  }
  return data;
}

const mmss = s => {
  s = Math.max(0, Math.floor(s));
  const m = String(Math.floor(s/60)).padStart(2,'0');
  const n = String(s%60).padStart(2,'0');
  return `${m}:${n}`;
};

function startTimer(sec){
  timeLeft = Math.max(0, Number(sec || 0));
  $('#time-left').textContent = mmss(timeLeft);

  if (timerHandle) clearInterval(timerHandle);
  timerHandle = setInterval(async () => {
    timeLeft--;
    if (timeLeft <= 0) {
      clearInterval(timerHandle);
      $('#time-left').textContent = '00:00';
      await doSubmit(true);
      return;
    }
    $('#time-left').textContent = mmss(timeLeft);
  }, 1000);
}

function typeLabel(t){
  t = String(t||'').toLowerCase();
  if (t === 'fill_in_the_blank') return 'Fill in the blanks';
  if (t === 'true_false')       return 'True / False';
  if (t === 'mcq')              return 'Single choice';
  return 'Single choice';
}

function showSkeleton(on=true){
  $('#q-skeleton')?.classList.toggle('d-none', !on);
}

function normalizeTeXDelimiters(s){
  return String(s ?? '')
    .replace(/\\\\\[/g, '\\[').replace(/\\\\\]/g, '\\]')
    .replace(/\\\\\(/g, '\\(').replace(/\\\\\)/g, '\\)');
}

function safeHTML(html){
  const tpl = document.createElement('template');
  tpl.innerHTML = String(html ?? '');
  tpl.content.querySelectorAll('script').forEach(n => n.remove());
  tpl.content.querySelectorAll('*').forEach(el => {
    [...el.attributes].forEach(a => {
      const name = a.name.toLowerCase();
      const val  = String(a.value || '').toLowerCase();
      if (name.startsWith('on')) el.removeAttribute(a.name);
      if ((name === 'href' || name === 'src') && val.startsWith('javascript:')) {
        el.removeAttribute(a.name);
      }
    });
  });
  return tpl.innerHTML;
}

function toDisplay(s){
  const x = normalizeTeXDelimiters(String(s||''));
  return safeHTML(x).replace(/\{dash\}/gi, '<span class="fib-underline">&nbsp;</span>');
}

function answeredVal(qid){
  const sel = selections[qid];
  if (sel == null) return false;
  if (Array.isArray(sel)) return sel.filter(v => String(v).trim() !== '').length > 0;
  return String(sel).trim() !== '';
}

function escapeHtml(str){
  return (str ?? '').toString()
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}

/* ================= Cache ================= */
function loadCache(){
  try{
    const raw = localStorage.getItem(CACHE_KEY);
    if (!raw) return null;
    return JSON.parse(raw);
  }catch(e){ return null; }
}
function saveCache(){
  try{
    const payload = { selections, reviews, visited, currentIndex, saved_at: Date.now() };
    localStorage.setItem(CACHE_KEY, JSON.stringify(payload));
  }catch(e){}
}
function loadTimeCache(){
  try{
    const raw = localStorage.getItem(TIME_KEY);
    if (!raw) return {};
    const obj = JSON.parse(raw);
    return obj && typeof obj === 'object' ? obj : {};
  }catch(e){ return {}; }
}
function saveTimeCache(){
  try{
    localStorage.setItem(TIME_KEY, JSON.stringify(timeSpentMap || {}));
  }catch(e){}
}
function clearAllCache(){
  localStorage.removeItem(CACHE_KEY);
  localStorage.removeItem(TIME_KEY);
  localStorage.removeItem(STORAGE_KEY);
}

/* ================= Time Accounting ================= */
function stopAndAccumulateTime(){
  if (!currentQid || !qStartedAt) return;
  const sec = Math.max(0, Math.round((Date.now() - qStartedAt) / 1000));
  if (sec > 0){
    const qid = String(currentQid);
    timeSpentMap[qid] = (timeSpentMap[qid] || 0) + sec;
    saveTimeCache();
  }
  qStartedAt = null;
}

/* ================= Boot ================= */
document.addEventListener('DOMContentLoaded', init);

async function init(){
  // ✅ Overlay ONLY for first load / refresh
  showOverlay();

  try{
    const cached = loadCache();
    if (cached) {
      selections   = cached.selections || {};
      reviews      = cached.reviews || {};
      visited      = cached.visited || {};
      currentIndex = Number.isFinite(cached.currentIndex) ? cached.currentIndex : 0;
    }
    timeSpentMap = loadTimeCache();

    // API #1 (start)
    const started = await api('/api/exam/start', {
      method:'POST',
      body: JSON.stringify({ quiz: QUIZ_KEY, batch_quiz: BATCH_KEY || null })
    });

    const attempt = started.attempt || {};
    ATTEMPT_UUID = attempt.attempt_uuid || ATTEMPT_UUID;
    localStorage.setItem(STORAGE_KEY, ATTEMPT_UUID);

    $('#exam-title').textContent = attempt.quiz_name || 'Exam';
    document.title = (attempt.quiz_name ? `${attempt.quiz_name} • Exam` : 'Exam');

    startTimer(attempt.time_left_sec ?? attempt.total_time_sec ?? 0);

    // API #2 (questions)
    await loadQuestions();
    buildNavigator();
    renderQuestion();

    $('#prev-btn').addEventListener('click', onPrev);
    $('#next-btn').addEventListener('click', onNext);
    $('#review-btn').addEventListener('click', onToggleReview);
    $('#submit-btn').addEventListener('click', () => doSubmit(false));

    window.addEventListener('beforeunload', () => {
      if (SUBMITTED_OK) return;
      stopAndAccumulateTime();
      saveCache();
      saveTimeCache();
    });

  }catch(e){
    console.error(e);
    Swal.fire({icon:'error', title:'Cannot start exam', text: e.message || 'Please try again.'});
  }finally{
    hideOverlay();
  }
}

/* ================= Load Questions ================= */
async function loadQuestions(){
  showSkeleton(true);

  const data = await api(`/api/exam/attempts/${encodeURIComponent(ATTEMPT_UUID)}/questions`);
  questions  = data.questions || [];

  const serverSelections = data.selections || {};
  Object.keys(serverSelections).forEach(k => {
    if (selections[k] == null) selections[k] = serverSelections[k];
  });

  questions.forEach(q => {
    if (String(q.question_type).toLowerCase() === 'fill_in_the_blank') {
      const cur = selections[q.question_id];
      if (cur == null) selections[q.question_id] = [];
      else if (!Array.isArray(cur)) {
        const v = String(cur).trim();
        selections[q.question_id] = v ? [v] : [];
      }
    }
  });

  if (currentIndex < 0) currentIndex = 0;
  if (currentIndex >= questions.length) currentIndex = 0;

  $('#progress-total').textContent = String(questions.length);
  updateProgress();
  showSkeleton(false);

  saveCache();
}

/* ================= Nav / Progress ================= */
function buildNavigator(){
  const grid = $('#nav-grid');
  grid.innerHTML = '';

  questions.forEach((q, idx) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'nav-btn';
    b.textContent = String(idx+1);

    b.addEventListener('click', () => goToIndexInstant(idx));
    grid.appendChild(b);
  });

  refreshNav();
}

function goToIndexInstant(idx){
  stopAndAccumulateTime();
  saveCache();

  currentIndex = idx;
  renderQuestion();
}

function refreshNav(){
  const grid = $('#nav-grid').children;
  questions.forEach((q, idx) => {
    const btn = grid[idx];
    if (!btn) return;
    btn.className = 'nav-btn';
    if (idx === currentIndex) btn.classList.add('current');
    else if (reviews[q.question_id]) btn.classList.add('review');
    else if (answeredVal(q.question_id)) btn.classList.add('answered');
    else if (visited[q.question_id]) btn.classList.add('visited');
  });
}

function updateProgress(){
  const done  = questions.filter(q => answeredVal(q.question_id)).length;
  const total = questions.length || 1;
  const pct   = Math.round((done/total)*100);

  $('#progress-count').textContent = String(done);
  $('#progress-pct').textContent   = pct + '%';
  $('#progress-bar-fill').style.width = pct + '%';
}

/* ================= Render Question ================= */
function renderQuestion(){
  const q = questions[currentIndex];
  if (!q) return;

  visited[q.question_id] = true;

  const wrap = $('#question-wrap');
  const rawType = String(q.question_type || '').toLowerCase();
  const multi   = !!q.has_multiple_correct_answer;
  const label   = (multi && rawType !== 'fill_in_the_blank') ? 'Multiple choice' : typeLabel(rawType);

  const titleHTML = toDisplay(q.question_title);
  const descHTML  = q.question_description ? toDisplay(q.question_description) : '';

  const isReview = !!reviews[q.question_id];

  let html = `
    <div class="d-flex align-items-start justify-content-between gap-3">
      <div class="flex-grow-1">
        <div class="q-title mb-1">Q${currentIndex+1}. ${titleHTML}</div>
        ${descHTML ? `<div class="small text-muted mb-2">${descHTML}</div>` : ``}
        <div class="q-meta">
          <span>Marks: <b>${q.question_mark ?? 1}</b></span>
          <span class="q-badge">${label}</span>
        </div>
      </div>
      <span class="badge rounded-pill text-bg-info ${isReview ? '' : 'invisible'}">Review</span>
    </div>

    <div class="mt-3" id="options">`;

  const sel = selections[q.question_id];

  if (rawType === 'fill_in_the_blank') {
    const gaps = countGaps(q);
    const values = Array.isArray(sel) ? sel.slice(0, gaps).map(v => String(v)) : [];
    while (values.length < gaps) values.push('');

    html += `
      <div class="opt p-3 fib-fields">
        <label class="form-label small mb-2 fw-bold">Your answers</label>
        <div class="row g-2">`;
    for (let i=0; i<gaps; i++){
      html += `
          <div class="col-12 col-sm-6 col-md-4">
            <input class="form-control" data-fib-index="${i}"
                   placeholder="Answer ${i+1}"
                   value="${escapeHtml(values[i] || '')}">
          </div>`;
    }
    html += `
        </div>
      </div>`;
  } else {
    (q.answers || []).forEach(a => {
      const checked = multi
        ? (Array.isArray(sel) && sel.map(Number).includes(Number(a.answer_id)))
        : (!Array.isArray(sel) && Number(sel) === Number(a.answer_id));

      const ansHTML = safeHTML(normalizeTeXDelimiters(a.answer_title ?? ''));

      html += `
        <label class="opt form-check d-flex align-items-center gap-2">
          <input class="form-check-input"
                 type="${multi ? 'checkbox' : 'radio'}"
                 name="q_${q.question_id}${multi ? '[]' : ''}"
                 value="${a.answer_id}"
                 ${checked ? 'checked' : ''}/>
          <span class="form-check-label">${ansHTML}</span>
        </label>`;
    });
  }

  html += `</div>`;
  wrap.innerHTML = html;

  typeset(wrap);

  if (rawType === 'fill_in_the_blank') {
    $$('#options input[data-fib-index]').forEach(inp => {
      inp.addEventListener('input', () => {
        selections[q.question_id] = $$('#options input[data-fib-index]').map(i => i.value || '');
        saveCache(); updateProgress(); refreshNav();
      });
    });
  } else {
    $$('#options input').forEach(inp => {
      inp.addEventListener('change', () => {
        selections[q.question_id] = collectSelectionFor(q);
        saveCache(); updateProgress(); refreshNav();
      });
    });
  }

  $('#prev-btn').disabled = currentIndex === 0;
  $('#next-btn').innerHTML =
    (currentIndex < questions.length - 1)
      ? `Next<i class="fa-solid fa-arrow-right ms-2"></i>`
      : `Submit<i class="fa-solid fa-paper-plane ms-2"></i>`;

  $('#review-btn').innerHTML = isReview
    ? `<i class="fa-solid fa-flag me-2"></i>Unmark Review`
    : `<i class="fa-solid fa-flag me-2"></i>Mark Review`;

  refreshNav();
  updateProgress();

  // time tracking local only
  stopAndAccumulateTime();
  currentQid  = q.question_id;
  qStartedAt  = Date.now();
}

function countGaps(q){
  const title = String(q.question_title || '');
  const desc  = String(q.question_description || '');
  const re = /\{dash\}/gi;
  const n = (title.match(re)||[]).length + (desc.match(re)||[]).length;
  if (n > 0) return n;
  const ansLen = Array.isArray(q.answers) ? q.answers.length : 0;
  return ansLen > 0 ? ansLen : 1;
}

function collectSelectionFor(q){
  const multi = !!q.has_multiple_correct_answer;
  const type  = String(q.question_type || '').toLowerCase();
  if (type === 'fill_in_the_blank') {
    return $$('#options input[data-fib-index]').map(i => i.value || '');
  }
  const checked = $$('#options input:checked').map(i => Number(i.value));
  return multi ? checked : (checked[0] ?? null);
}

/* ================= Prev/Next ================= */
function onPrev(){
  if (currentIndex > 0) goToIndexInstant(currentIndex - 1);
}
function onNext(){
  if (currentIndex < questions.length - 1) goToIndexInstant(currentIndex + 1);
  else doSubmit(false);
}
function onToggleReview(){
  const q = questions[currentIndex];
  if (!q) return;
  reviews[q.question_id] = !reviews[q.question_id];
  saveCache();
  renderQuestion();
}

/* ================= Build Bulk Payload ================= */
function buildBulkPayload(){
  const payload = [];
  questions.forEach(q => {
    const qid = Number(q.question_id);
    const selected = selections[q.question_id] ?? null;

    let normalized = selected;
    if (Array.isArray(normalized) && normalized.filter(v => String(v).trim() !== '').length === 0) normalized = null;
    if (!Array.isArray(normalized) && (normalized === '' || normalized === undefined)) normalized = null;

    payload.push({
      question_id: qid,
      selected: normalized,
      time_spent_sec: Number(timeSpentMap[String(q.question_id)] || 0)
    });
  });
  return payload;
}

/* ================= Submit (Swal only, NO overlay) ================= */
async function doSubmit(auto){
  if (isSubmitting) return;

  if (!auto) {
    const ok = (await Swal.fire({
      title:'Submit exam?',
      text:'Once submitted, answers cannot be changed.',
      icon:'question',
      showCancelButton:true,
      confirmButtonText:'Submit'
    })).isConfirmed;
    if (!ok) return;

    Swal.fire({
      title: 'Submitting your answers…',
      text: 'Please wait',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });
  } else {
    // auto submit => show small loading too
    Swal.fire({
      title: 'Time over',
      text: 'Submitting…',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });
  }

  try{
    isSubmitting = true;

    $('#submit-btn').disabled = true;
    $('#submit-btn .btn-label').classList.add('d-none');
    $('#submit-btn .btn-spinner').classList.remove('d-none');

    stopAndAccumulateTime();
    saveCache();
    saveTimeCache();

    // API #1 submit bulk
    const answersPayload = buildBulkPayload();
    await api(`/api/exam/attempts/${encodeURIComponent(ATTEMPT_UUID)}/bulk-answer`, {
      method:'POST',
      body: JSON.stringify({ answers: answersPayload })
    });

    // API #2 final submit
    await api(`/api/exam/attempts/${encodeURIComponent(ATTEMPT_UUID)}/submit`, {
      method:'POST'
    });

    if (timerHandle) clearInterval(timerHandle);

    Swal.close();

    await Swal.fire({
      icon:'success',
      title:'Submitted',
      text:'Your exam has been submitted.'
    });

    SUBMITTED_OK = true;
    clearAllCache();

    const role = sessionStorage.getItem('role') || 'student';
    window.location.replace(`/${role}/dashboard`);

  }catch(e){
    console.error(e);
    Swal.close();
    Swal.fire({
      icon:'error',
      title:'Submit failed',
      text: e.message || 'Please try again.'
    });
  }finally{
    isSubmitting = false;
    $('#submit-btn').disabled = false;
    $('#submit-btn .btn-label').classList.remove('d-none');
    $('#submit-btn .btn-spinner').classList.add('d-none');
  }
}
</script>
</body>
</html>
