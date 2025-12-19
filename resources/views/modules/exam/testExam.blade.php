@php
  $quizKey  = $quizKey ?? request()->route('quiz') ?? request()->query('quiz');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="quiz-key" content="{{ trim((string)$quizKey) }}">

  <title>Test Run</title>

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
      --exam-success: var(--success-color, #16a34a);
      --exam-warn: var(--t-warn, #f59e0b);
    }
    body{ background: var(--exam-bg); color: var(--exam-ink); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    .exam-topbar{ background: var(--exam-surface); border-bottom: 1px solid var(--exam-line); box-shadow: 0 6px 18px rgba(15,23,42,.05); z-index: 20; }
    .exam-brand{ display:flex; align-items:center; gap:.6rem; font-weight: 800; letter-spacing:.01em; }
    .exam-brand img{height:32px; width:auto}
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
    .nav-btn.visited{ background: var(--exam-hover); }

    .w3-progress{ height:10px; border-radius:999px; background: var(--exam-line-soft); overflow:hidden; }
    .w3-progress > div{ height:100%; width:0%; background: var(--exam-accent); transition: width .2s ease; }

    .q-title{ font-family: Poppins, Inter, system-ui, sans-serif; font-weight: 700; font-size: 1.03rem; line-height: 1.35; }
    .q-meta{ font-size:.84rem; color: var(--exam-muted); display:flex; align-items:center; flex-wrap: wrap; gap:.45rem; }
    .q-badge{ font-size:.72rem; padding:.18rem .6rem; border-radius:999px; background: color-mix(in oklab, var(--exam-accent) 10%, transparent); border: 1px solid color-mix(in oklab, var(--exam-accent) 25%, transparent); color: var(--exam-accent); font-weight: 800; }

    .opt{ border-radius: 12px; border: 1px solid var(--exam-line-soft); padding: .65rem .75rem; margin-bottom: .42rem; background: var(--exam-surface); cursor: default; transition: background .12s ease, border-color .12s ease, box-shadow .12s ease; }
    .opt:hover{ background: var(--exam-hover); border-color: color-mix(in oklab, var(--exam-accent) 45%, var(--exam-line-soft)); box-shadow: 0 10px 18px rgba(15,23,42,.06); }
    .opt input.form-check-input{ margin-top: 0; }
    .opt .form-check-label{ font-weight: 650; font-size: .94rem; }

    /* ✅ Correct highlight */
    .opt.correct{
      background: color-mix(in oklab, var(--exam-success) 12%, var(--exam-surface));
      border-color: color-mix(in oklab, var(--exam-success) 40%, var(--exam-line-soft));
      box-shadow: 0 10px 18px rgba(22,163,74,.12);
    }
    .opt.correct .opt-icon{ color: var(--exam-success); }
    .opt.correct .form-check-label{ font-weight: 900; }

    .fib-underline{ display:inline-block; min-width:90px; border-bottom:2px solid #cbd5e1; margin: 0 .22rem .18rem .22rem; }

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
          <span id="exam-title">Test Run</span>
        </div>
        <span class="badge rounded-pill text-bg-light border">
          <i class="fa-solid fa-flask me-1"></i> Test Run
        </span>
      </div>

      <button id="back-btn" class="btn btn-light">
        <i class="fa-solid fa-arrow-left me-2"></i>Back
      </button>
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
                <span id="progress-count">0</span> of <span id="progress-total">0</span> visited
              </small>
            </div>
          </div>
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

let questions = [];
let visited = {};
let currentIndex = 0;

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
      ...(token ? {'Authorization': `Bearer ${token}`} : {}),
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

/* ✅ Map your API keys to the keys used by the Exam renderer */
function normalizeQuestion(q){
  const answers = Array.isArray(q.answers) ? q.answers : [];
  return {
    question_id: q.question_id ?? q.id ?? q.questionId,
    question_title: q.question_title ?? q.title ?? '',
    question_description: q.question_description ?? q.description ?? null,
    question_explanation: q.question_explanation ?? q.explanation ?? null,
    question_type: q.question_type ?? q.type ?? 'mcq',
    question_mark: q.question_mark ?? q.mark ?? 1,
    has_multiple_correct_answer: !!(q.has_multiple_correct_answer ?? q.is_multiple ?? q.multiple),
    correct_answer_ids: Array.isArray(q.correct_answer_ids) ? q.correct_answer_ids : [],
    answers: answers.map(a => ({
      answer_id: a.answer_id ?? a.id ?? a.answerId,
      answer_title: a.answer_title ?? a.title ?? a.answer ?? '',
      is_correct: a.is_correct ?? 0
    }))
  };
}

function setProgress(){
  const total = questions.length || 0;
  const done  = Object.values(visited).filter(Boolean).length;
  const pct   = total ? Math.round((done/total)*100) : 0;

  $('#progress-total').textContent = String(total);
  $('#progress-count').textContent = String(done);
  $('#progress-pct').textContent = pct + '%';
  $('#progress-bar-fill').style.width = pct + '%';
}

function buildNavigator(){
  const grid = $('#nav-grid');
  grid.innerHTML = '';

  questions.forEach((q, idx) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'nav-btn';
    b.textContent = String(idx+1);
    b.addEventListener('click', () => goTo(idx));
    grid.appendChild(b);
  });

  refreshNav();
}

function refreshNav(){
  const nodes = $('#nav-grid')?.children || [];
  questions.forEach((q, idx) => {
    const btn = nodes[idx];
    if (!btn) return;
    btn.className = 'nav-btn';
    if (idx === currentIndex) btn.classList.add('current');
    else if (visited[q.question_id]) btn.classList.add('visited');
  });
}

function goTo(idx){
  currentIndex = idx;
  renderQuestion();
}

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

  const correctSet = new Set((q.correct_answer_ids || []).map(Number));

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
    </div>

    <div class="mt-3" id="options">
  `;

  if (rawType === 'fill_in_the_blank') {
    // show correct answers (if present)
    const answers = Array.isArray(q.answers) ? q.answers : [];
    const gaps = answers.length ? answers.length : 1;

    html += `
      <div class="opt p-3">
        <div class="fw-bold small mb-2">Correct answer(s)</div>
        <div class="row g-2">
    `;

    for (let i=0; i<gaps; i++){
      const val = answers[i]?.answer_title ?? '';
      html += `
        <div class="col-12 col-sm-6 col-md-4">
          <div class="opt correct mb-0">
            <i class="fa-solid fa-circle-check opt-icon me-2"></i>
            <span>${safeHTML(val)}</span>
          </div>
        </div>
      `;
    }

    html += `</div></div>`;
  } else {
    (q.answers || []).forEach(a => {
      const aid = Number(a.answer_id);
      const correct = correctSet.has(aid) || a.is_correct === 1 || a.is_correct === '1';

      const ansHTML = safeHTML(normalizeTeXDelimiters(a.answer_title ?? ''));

      html += `
        <label class="opt form-check d-flex align-items-center gap-2 ${correct ? 'correct' : ''}">
          <i class="opt-icon ${correct ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle'}"></i>
          <input class="form-check-input" type="${multi ? 'checkbox' : 'radio'}" disabled>
          <span class="form-check-label">${ansHTML}</span>
        </label>
      `;
    });
  }

  html += `</div>`;
  wrap.innerHTML = html;

  typeset(wrap);

  $('#prev-btn').disabled = currentIndex === 0;
  $('#next-btn').innerHTML =
    (currentIndex < questions.length - 1)
      ? `Next<i class="fa-solid fa-arrow-right ms-2"></i>`
      : `Finish<i class="fa-solid fa-check ms-2"></i>`;

  refreshNav();
  setProgress();
}

/* ================= Boot ================= */
document.addEventListener('DOMContentLoaded', init);

async function init(){
  showOverlay();
  showSkeleton(true);

  $('#back-btn').addEventListener('click', () => history.back());
  $('#prev-btn').addEventListener('click', () => { if (currentIndex>0) goTo(currentIndex-1); });
  $('#next-btn').addEventListener('click', () => {
    if (currentIndex < questions.length - 1) goTo(currentIndex+1);
    else Swal.fire({icon:'success', title:'Completed', text:'Test run finished.'});
  });

  try{
    if (!QUIZ_KEY) throw new Error('Missing quiz key');

    // ✅ ONE API CALL ONLY (your payload format)
    const data = await api(`/api/test/quizz/${encodeURIComponent(QUIZ_KEY)}/questions`, { method:'GET' });

    const rawQs = Array.isArray(data.questions) ? data.questions : [];
    questions = rawQs.map(normalizeQuestion);

    const quizName = data.quiz?.name || data.quiz?.quiz_name || data.quiz_name || 'Test Run';
    $('#exam-title').textContent = quizName;
    document.title = `${quizName} • Test Run`;

    showSkeleton(false);

    buildNavigator();
    if (!questions.length){
      $('#question-wrap').innerHTML = `<div class="text-muted">No questions found.</div>`;
      return;
    }
    renderQuestion();

  }catch(e){
    console.error(e);
    Swal.fire({icon:'error', title:'Unable to load test run', text: e.message || 'Please try again.'});
  }finally{
    hideOverlay();
  }
}
</script>
</body>
</html>
