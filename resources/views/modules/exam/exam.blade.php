@php
  $quizKey = $quizKey ?? request()->route('quiz');
@endphp

@php
  // batch quiz key from ?batch=... (if present)
  $batchKey = request()->query('batch');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="quiz-key" content="{{ $quizKey }}">
  <meta name="batch-key" content="{{ $batchKey }}">
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Exam • W3Techiez</title>

  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- MathJax -->
  <script>
    window.MathJax = {
      tex : {inlineMath:[['$','$'],['\\(','\\)']], displayMath:[['$$','$$'],['\\[','\\]']]},
      svg : {fontCache:'global'}
    };
  </script>
  <script async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

  <style>
    :root{
      --primary-color:#6c3ff3;
      --accent-color:#6c3ff3;
      --t-success:#16a34a;
      --t-warn:#f59e0b;
      --page-hover:#f7f8fc;
      --surface:#fff;
      --bg-body:#f6f7fb;
      --line-strong:#e5e7eb;
      --line-soft:#eef2f7;
      --text-color:#111827;
      --font-sans: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Inter, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
      --font-head: Poppins, var(--font-sans);
    }
    body{background:var(--bg-body); color:var(--text-color); font-family:var(--font-sans)}
    header.sticky-top{background:#fff; border-bottom:1px solid var(--line-strong)}
    .kpi{font-family:var(--font-head)}
    .card{background:var(--surface); border:1px solid var(--line-strong); border-radius:16px; box-shadow:0 8px 24px rgba(17,24,39,.06)}
    .btn{padding:.7rem 1rem; border-radius:.6rem; font-weight:600}
    .btn-primary{background:var(--primary-color); border:none}
    .btn-primary:hover{filter:brightness(.95)}
    .btn-light{background:#f5f7fb; border:1px solid var(--line-strong); color:#334155}
    .nav-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:.5rem}
    @media (max-width:576px){ .nav-grid{grid-template-columns:repeat(8,1fr)} }
    .nav-btn{
      width:36px;height:36px;border-radius:999px;font-weight:700;border:1px solid var(--line-strong);background:#fff;
      display:flex;align-items:center;justify-content:center
    }
    .nav-btn.current{background:var(--primary-color); color:#fff; border-color:var(--primary-color)}
    .nav-btn.answered{background:var(--t-success); color:#fff; border-color:var(--t-success)}
    .nav-btn.review{background:var(--t-warn); color:#111827; border-color:var(--t-warn)}
    .nav-btn.visited{background:var(--page-hover)}
    .w3-progress{height:10px;border-radius:999px;background:var(--line-soft);overflow:hidden}
    .w3-progress>div{height:100%; background:var(--accent-color); width:0%}

    /* FIB */
    .fib-underline{display:inline-block; min-width:90px; border-bottom:2px solid #cbd5e1; margin:0 .22rem .2rem .22rem}
    .fib-fields .form-control{height:40px}

    /* Skeleton */
    .skeleton{position:relative; overflow:hidden; background:#eef2f7; border-radius:10px; min-height:90px}
    .skeleton:after{
      content:""; position:absolute; inset:0; transform:translateX(-100%);
      background:linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.6) 50%, rgba(255,255,255,0) 100%);
      animation:shimmer 1.2s infinite;
    }
    @keyframes shimmer{100%{transform:translateX(100%)}}

    mjx-container,mjx-container[display="block"],.mjx-chtml{display:inline-block!important}
    mjx-container svg,mjx-container[display="block"] svg,.mjx-chtml svg{vertical-align:middle}

    @media (min-width:992px){ .col-fixed-260{flex:0 0 260px; max-width:260px} }
    .spin{
        border-radius: none !important;
        border-color:transparent !important;
    }
  </style>
</head>
<body>
  <!-- Top -->
  <header class="sticky-top">
    <div class="container-xxl py-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <img id="logo" src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez" style="height:32px">
        <h1 id="exam-title" class="kpi fs-5 mb-0">Exam</h1>
      </div>
      <div id="timer-pill" class="px-3 py-1 rounded-3 fw-semibold text-white" style="background:var(--primary-color)">
        <i class="fa-solid fa-clock me-2"></i><span id="time-left">--:--</span>
      </div>
    </div>
  </header>

  <main class="container-xxl py-4">
    <div class="row g-3 g-lg-4">
      <!-- Navigator -->
      <aside class="col-12 col-lg-3 col-fixed-260">
        <div class="card p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h3 class="kpi fs-6 mb-0">Question Navigator</h3>
            <small class="text-muted">Go to…</small>
          </div>

          <div id="nav-grid" class="nav-grid"></div>

          <div class="mt-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <small class="text-muted"><i class="fa-solid fa-chart-line me-2"></i>Progress</small>
              <strong id="progress-pct" style="color:var(--accent-color)">0%</strong>
            </div>
            <div class="w3-progress"><div id="progress-bar-fill"></div></div>
            <div class="mt-1">
              <small class="text-muted"><span id="progress-count">0</span> of <span id="progress-total">0</span> answered</small>
            </div>
          </div>

          <button id="submit-btn" class="btn btn-primary w-100 mt-3">
            <span class="btn-label"><i class="fa-solid fa-paper-plane me-2"></i>Submit Exam</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm"></span> Submitting…</span>
          </button>
        </div>
      </aside>

      <!-- Question -->
      <section class="col-12 col-lg">
        <div id="question-wrap" class="card p-4">
          <!-- initial skeleton -->
          <div id="q-skeleton">
            <div class="skeleton mb-3" style="height:24px;width:60%"></div>
            <div class="skeleton mb-2" style="height:16px;width:45%"></div>
            <div class="skeleton mb-4" style="height:16px;width:35%"></div>
            <div class="skeleton" style="height:120px;width:100%"></div>
          </div>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center">
          <button id="prev-btn" class="btn btn-light" disabled>
            <span class="lbl"><i class="fa-solid fa-arrow-left me-2"></i>Previous</span>
            <span class="spin d-none"><span class="spinner-border spinner-border-sm me-2"></span>Loading</span>
          </button>
          <button id="review-btn" class="btn btn-light"><i class="fa-solid fa-flag me-2"></i>Mark Review</button>
          <button id="next-btn" class="btn btn-primary">
            <span class="lbl">Next<i class="fa-solid fa-arrow-right ms-2"></i></span>
            <span class="spin d-none"><span class="spinner-border spinner-border-sm me-2"></span>Saving</span>
          </button>
        </div>
      </section>
    </div>
  </main>

<script>
/* ===== Globals ===== */
const $  = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));

const token = sessionStorage.student_token || sessionStorage.token || '';

const QUIZ_KEY =
  (document.querySelector('meta[name="quiz-key"]')?.content || '').trim()
  || new URLSearchParams(location.search).get('quiz') || '';

// optional batch context: /exam/{quiz}?batch={batch_quiz_uuid}
const BATCH_KEY =
  (document.querySelector('meta[name="batch-key"]')?.content || '').trim()
  || new URLSearchParams(location.search).get('batch') || '';

// scope for localStorage – so same quiz in different batches
// doesn't reuse the same attempt UUID
const EXAM_SCOPE  = BATCH_KEY ? `${QUIZ_KEY}::${BATCH_KEY}` : QUIZ_KEY;
const STORAGE_KEY = 'attempt_uuid:' + EXAM_SCOPE;

if (!QUIZ_KEY) {
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({icon:'error', title:'Missing quiz key', text:'No quiz id/uuid provided.'})
      .then(() => history.back());
  });
}

let ATTEMPT_UUID = localStorage.getItem(STORAGE_KEY) || null;
let questions = [];
let selections = {};   // qid -> int | int[] | string | string[]
let reviews = {};
let visited = {};
let currentIndex = 0;
let timeLeft = 0;
let timerHandle = null;
let isSubmitting = false;
let navLock = false;
let qStartedAt = null; 

/* ===== Helpers ===== */
async function api(path, opts={}){
  const res = await fetch(path, {
    ...opts,
    headers: {
      'Content-Type':'application/json',
      'Authorization': `Bearer ${token}`,
      ...(opts.headers||{})
    }
  });
  const data = await res.json().catch(()=> ({}));
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
  timeLeft = sec;
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
function setBtnLoading(selector, on){
  const b = $(selector);
  if (!b) return;
  b.disabled = !!on;
  b.querySelector('.lbl')?.classList.toggle('d-none', !!on);
  b.querySelector('.spin')?.classList.toggle('d-none', !on);
}
function showSkeleton(on=true){
  $('#q-skeleton')?.classList.toggle('d-none', !on);
}

/* ===== Boot ===== */
document.addEventListener('DOMContentLoaded', init);

async function init(){
  try{
    const started = await api('/api/exam/start', {
    method:'POST',
    body: JSON.stringify({
    quiz: QUIZ_KEY,
    batch_quiz: BATCH_KEY || null
  })
});


    const attempt = started.attempt || {};
    ATTEMPT_UUID = attempt.attempt_uuid || ATTEMPT_UUID;
localStorage.setItem(STORAGE_KEY, ATTEMPT_UUID);


    $('#exam-title').textContent = attempt.quiz_name || 'Exam';
    startTimer(attempt.time_left_sec ?? attempt.total_time_sec ?? 0);

    await loadQuestions();           // show skeleton initially
    buildNavigator();
    renderQuestion();                // render index 0
    await focusQuestion(questions[0]?.question_id);  // start timing for first visible question

    $('#prev-btn').addEventListener('click', onPrev);
    $('#next-btn').addEventListener('click', onNext);
    $('#review-btn').addEventListener('click', onToggleReview);
    $('#submit-btn').addEventListener('click', () => doSubmit(false));

    setInterval(syncStatus, 30000);
  }catch(e){
    console.error(e);
    Swal.fire({icon:'error', title:'Cannot start exam', text: e.message || 'Please try again.'});
  }
}

async function loadQuestions(){
  showSkeleton(true);
  const data = await api(`/api/exam/attempts/${ATTEMPT_UUID}/questions`);
  questions   = data.questions || [];
  selections  = data.selections || {};
  timeLeft    = (data.attempt && typeof data.attempt.time_left_sec === 'number') ? data.attempt.time_left_sec : timeLeft;
  $('#time-left').textContent = mmss(timeLeft);

  // Normalize FIB selections to array for local state (no network on input)
  questions.forEach(q => {
    if (String(q.question_type).toLowerCase() === 'fill_in_the_blank') {
      const cur = selections[q.question_id];
      if (cur == null) selections[q.question_id] = [];
      else if (!Array.isArray(cur)) selections[q.question_id] = String(cur).trim() ? [String(cur)] : [];
    }
  });

  $('#progress-total').textContent = String(questions.length);
  updateProgress();
  showSkeleton(false);
}

/* ===== Nav / Progress ===== */
function buildNavigator(){
  const grid = $('#nav-grid');
  grid.innerHTML = '';
  questions.forEach((q, idx) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'nav-btn';
    b.textContent = String(idx+1);
    b.addEventListener('click', async () => {
      if (navLock) return;
      try{
        navLock = true;
        await saveCurrent();                 // persist current before jumping
        currentIndex = idx;
        renderQuestion();
        await focusQuestion(q.question_id);  // start timing for the new visible question
      } finally {
        navLock = false;
      }
    });
    grid.appendChild(b);
  });
  refreshNav();
}
function answeredVal(qid){
  const sel = selections[qid];
  if (sel == null) return false;
  if (Array.isArray(sel)) return sel.filter(s => String(s).trim() !== '').length > 0;
  return String(sel).trim() !== '';
}
function refreshNav(){
  const grid = $('#nav-grid').children;
  questions.forEach((q, idx) => {
    const btn = grid[idx];
    btn.className = 'nav-btn';
    if (idx === currentIndex) btn.classList.add('current');
    else if (reviews[q.question_id]) btn.classList.add('review');
    else if (answeredVal(q.question_id)) btn.classList.add('answered');
    else if (visited[q.question_id]) btn.classList.add('visited');
  });
}
function updateProgress(){
  const done = questions.filter(q => answeredVal(q.question_id)).length;
  const total = questions.length || 1;
  const pct = Math.round((done/total)*100);
  $('#progress-count').textContent = String(done);
  $('#progress-pct').textContent = pct + '%';
  $('#progress-bar-fill').style.width = pct + '%';
}

/* ===== Render question ===== */
function renderQuestion(){
  const q = questions[currentIndex];
  if (!q) return;
  visited[q.question_id] = true;

  const wrap = $('#question-wrap');
  const rawType = String(q.question_type || '').toLowerCase();
  const multi   = !!q.has_multiple_correct_answer;
  const label   = multi && rawType !== 'fill_in_the_blank' ? 'Multiple choice' : typeLabel(rawType);

  // Build display title/desc with underscores instead of {dash}
  const toDisplay = s => String(s||'').replace(/\{dash\}/gi, '<span class="fib-underline">&nbsp;</span>');
  const titleHTML = toDisplay(q.question_title);
  const descHTML  = q.question_description ? toDisplay(q.question_description) : '';

  let html = `
    <div class="d-flex align-items-start justify-content-between gap-3">
      <div class="flex-grow-1">
        <div class="kpi fs-6 mb-1">Q${currentIndex+1}. ${titleHTML}</div>
        ${descHTML ? `<div class="small text-muted mb-2">${descHTML}</div>` : ``}
        <div class="small text-muted">Marks: <b>${q.question_mark ?? 1}</b> • Type: ${label}</div>
      </div>
      <span class="badge rounded-pill text-bg-info ${reviews[q.question_id] ? '' : 'invisible'}">Review</span>
    </div>
    <div class="mt-3" id="options">`;

  const sel = selections[q.question_id];

  if (rawType === 'fill_in_the_blank') {
    const gaps = countGaps(q);
    const values = Array.isArray(sel) ? sel.slice(0, gaps).map(v => String(v)) : [];
    while (values.length < gaps) values.push('');

    html += `<div class="opt p-3 fib-fields">
      <label class="form-label small mb-2">Your answers</label>
      <div class="row g-2">`;
    for (let i = 0; i < gaps; i++){
      html += `
        <div class="col-12 col-sm-6 col-md-4">
          <input class="form-control" data-fib-index="${i}" placeholder="Answer ${i+1}" value="${escapeHtml(values[i]||'')}">
        </div>`;
    }
    html += `</div>
      <div class="form-text">Enter each blank separately. Answers are case-insensitive.</div>
    </div>`;
  } else {
    (q.answers || []).forEach(a => {
      const checked = multi
        ? Array.isArray(sel) && sel.map(Number).includes(Number(a.answer_id))
        : (!Array.isArray(sel) && Number(sel) === Number(a.answer_id));
      html += `
      <label class="opt form-check d-flex align-items-center gap-2">
        <input class="form-check-input" type="${multi ? 'checkbox' : 'radio'}"
               name="q_${q.question_id}${multi ? '[]' : ''}" value="${a.answer_id}"
               ${checked ? 'checked' : ''}/>
        <span class="form-check-label">${a.answer_title ?? ''}</span>
      </label>`;
    });
  }

  html += `</div>`;
  wrap.innerHTML = html;

  // Math typeset
  MathJax.typesetPromise([wrap]);

  // Local-only handlers (no network)
  if (rawType === 'fill_in_the_blank') {
    $$('#options input[data-fib-index]').forEach(inp => {
      inp.addEventListener('input', () => {
        const arr = $$('#options input[data-fib-index]').map(i => i.value || '');
        selections[q.question_id] = arr;           // local state only
        updateProgress(); refreshNav();
      });
      inp.addEventListener('blur', () => {
        const arr = $$('#options input[data-fib-index]').map(i => i.value || '');
        selections[q.question_id] = arr;
        updateProgress(); refreshNav();
      });
    });
  } else {
    $$('#options input').forEach(inp => {
      inp.addEventListener('change', () => {
        const val = collectSelectionFor(q);
        selections[q.question_id] = val;           // local state only
        updateProgress(); refreshNav();
      });
    });
  }

  // Bottom controls
  $('#prev-btn').disabled = currentIndex === 0;
  $('#next-btn .lbl').innerHTML = (currentIndex < questions.length - 1)
    ? `Next<i class="fa-solid fa-arrow-right ms-2"></i>`
    : `Submit<i class="fa-solid fa-paper-plane ms-2"></i>`;
  $('#review-btn').innerHTML = reviews[q.question_id]
    ? `<i class="fa-solid fa-flag me-2"></i>Unmark Review`
    : `<i class="fa-solid fa-flag me-2"></i>Mark Review`;

  refreshNav();
  updateProgress();
}

function countGaps(q){
  const title = String(q.question_title||'');
  const desc  = String(q.question_description||'');
  const re = /\{dash\}/gi;
  const n1 = (title.match(re)||[]).length;
  const n2 = (desc.match(re)||[]).length;
  if (n1+n2 > 0) return n1+n2;
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

/* ===== Focus + Save (network) ===== */
async function focusQuestion(questionId){
  if (!questionId) return;
  try{
    const res = await api(`/api/exam/attempts/${ATTEMPT_UUID}/focus`, {
      method:'POST',
      body: JSON.stringify({ question_id: Number(questionId) })
    });
    if (res?.attempt && typeof res.attempt.time_left_sec === 'number') {
      timeLeft = res.attempt.time_left_sec;
      $('#time-left').textContent = mmss(timeLeft);
    }

    // 🔹 start local timer for this question
    qStartedAt = Date.now();

  }catch(e){
    console.warn('focus failed:', e.message);
    if ((e.message||'').toLowerCase().includes('time over')) {
      await doSubmit(true);
    }
  }
}


async function saveCurrent(){
  const q = questions[currentIndex];
  if (!q) return;

  const selected = collectSelectionFor(q);  // read from DOM to be safe

  // 🔹 Calculate elapsed time in seconds for THIS question
  let timeSpent = 0;
  if (qStartedAt) {
    const diffMs = Date.now() - qStartedAt;
    // at least 1 second if the user actually saw the question
    timeSpent = Math.max(1, Math.round(diffMs / 1000));
  }

  try{
    await api(`/api/exam/attempts/${ATTEMPT_UUID}/answer`, {
      method:'POST',
      body: JSON.stringify({
        question_id: Number(q.question_id),
        selected: selected ?? null,
        time_spent: timeSpent    // 🔹 NEW
      })
    });
    selections[q.question_id] = selected ?? null;  // commit locally after success
  }catch(e){
    console.warn('saveCurrent failed:', e.message);
    if ((e.message||'').toLowerCase().includes('time over')) {
      await doSubmit(true);
    }
  }
}


/* ===== Nav buttons ===== */
async function onPrev(){
  if (navLock) return;
  setBtnLoading('#prev-btn', true);
  try{
    navLock = true;
    await saveCurrent();
    if (currentIndex > 0) {
      currentIndex--;
      renderQuestion();
      await focusQuestion(questions[currentIndex].question_id);
    }
  }finally{
    setBtnLoading('#prev-btn', false);
    navLock = false;
  }
}
async function onNext(){
  if (navLock) return;
  setBtnLoading('#next-btn', true);
  try{
    navLock = true;
    await saveCurrent();
    if (currentIndex < questions.length - 1) {
      currentIndex++;
      renderQuestion();
      await focusQuestion(questions[currentIndex].question_id);
    } else {
      await doSubmit(false);
    }
  }finally{
    setBtnLoading('#next-btn', false);
    navLock = false;
  }
}
function onToggleReview(){
  const q = questions[currentIndex];
  if (!q) return;
  reviews[q.question_id] = !reviews[q.question_id];
  renderQuestion();
}

/* ===== Submit ===== */
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
  }

  try{
    isSubmitting = true;
    $('#submit-btn').disabled = true;
    $('#submit-btn .btn-label').classList.add('d-none');
    $('#submit-btn .btn-spinner').classList.remove('d-none');

    // Always persist the latest visible question before submitting
    await saveCurrent();

    const res = await api(`/api/exam/attempts/${ATTEMPT_UUID}/submit`, { method:'POST' });
    clearInterval(timerHandle);

    const result = res.result || null;
    if (result && result.publish_to_student) {
      await Swal.fire({
        icon:'success',
        title:'Submitted',
        html:`<div class="text-start">
          <div><b>Marks:</b> ${result.marks_obtained}/${result.total_marks}</div>
          <div><b>Percentage:</b> ${result.percentage}%</div>
        </div>`
      });
    } else {
      await Swal.fire({icon:'success', title:'Submitted', text:'Your attempt has been submitted. Results will be released later.'});
    }
    let userRole = sessionStorage.getItem('role');
localStorage.removeItem(STORAGE_KEY);
location.href = `/${userRole}/dashboard`;

  }catch(e){
    console.error(e);
    Swal.fire({icon:'error', title:'Submit failed', text: e.message || 'Please try again.'});
  }finally{
    isSubmitting = false;
    $('#submit-btn').disabled = false;
    $('#submit-btn .btn-label').classList.remove('d-none');
    $('#submit-btn .btn-spinner').classList.add('d-none');
  }
}

/* ===== Status sync ===== */
async function syncStatus(){
  try{
    const s = await api(`/api/exam/attempts/${ATTEMPT_UUID}/status`);
    if (s.attempt) {
      timeLeft = Number(s.attempt.time_left_sec || timeLeft);
      $('#time-left').textContent = mmss(timeLeft);
      if ((s.attempt.status || '') !== 'in_progress') {
        await doSubmit(true);
      }
    }
  }catch(e){ /* ignore */ }
}

/* ===== small util ===== */
function escapeHtml(str){
  return (str ?? '').toString()
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>
</body>
</html>
