@php
  // safety: if not passed for some reason, read it from the route
  $quizKey = $quizKey ?? request()->route('quiz');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="quiz-key" content="{{ $quizKey }}">
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Exam • W3Techiez</title>

  <!-- Brand theme -->
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Icons / Alerts / Math -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    window.MathJax = {
      tex : {inlineMath:[['$','$'],['\\(','\\)']], displayMath:[['$$','$$'],['\\[','\\]']]},
      svg : {fontCache:'global'}
    };
  </script>
  <script async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

  <style>
    body{background:var(--bg-body); color:var(--text-color); font-family:var(--font-sans)}
    header.sticky-top{background:#fff;border-bottom:1px solid var(--line-strong)}
    .kpi{font-family:var(--font-head); color:var(--ink)}
    .card{background:var(--surface); border:1px solid var(--line-strong); border-radius:16px; box-shadow:var(--shadow-2)}
    .btn{padding:.7rem 1rem; border-radius:.6rem; font-weight:600}
    .btn-primary{background:var(--primary-color); color:#fff}
    .btn-primary:hover{filter:brightness(.95)}
    .btn-light{background:#f5f7fb; border:1px solid var(--line-strong); color:var(--secondary-color)}
    .opt{border:1px solid var(--line-strong); border-radius:.75rem; padding:.9rem}
    .opt:hover{background:var(--page-hover)}
    .nav-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:.5rem}
    .nav-btn{width:34px;height:34px;border-radius:999px;font-weight:700;border:1px solid var(--line-strong);background:#fff}
    .nav-btn.current{background:var(--accent-color); color:#fff; border-color:var(--accent-color)}
    .nav-btn.answered{background:var(--t-success); color:#fff}
    .nav-btn.review{background:var(--t-warn); color:#fff}
    .nav-btn.visited{background:var(--page-hover)}
    .w3-progress{height:10px;border-radius:999px;background:var(--line-soft);overflow:hidden}
    .w3-progress > div{height:100%; background:var(--accent-color); width:0%}
    mjx-container,mjx-container[display="block"],.mjx-chtml{display:inline-block!important}
    mjx-container svg,mjx-container[display="block"] svg,.mjx-chtml svg{vertical-align:middle}
    @media (min-width: 992px){
      .col-fixed-260{flex:0 0 260px;max-width:260px}
    }
  </style>
</head>
<body>

  <!-- Top bar -->
  <header class="sticky-top">
    <div class="container-xxl py-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <img id="logo" src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez" style="height:32px">
        <h1 id="exam-title" class="kpi fs-5 mb-0">Exam</h1>
      </div>
      <div id="timer-pill" class="px-3 py-1 rounded-3 fw-semibold text-white" style="background:var(--secondary-color)">
        <i class="fa-solid fa-clock me-2"></i><span id="time-left">--:--</span>
      </div>
    </div>
  </header>

  <main class="container-xxl py-4">
    <div class="row g-3 g-lg-4">
      <!-- Sidebar: navigator -->
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
            <i class="fa-solid fa-paper-plane me-2"></i>Submit Exam
          </button>
        </div>
      </aside>

      <!-- Main: question area -->
      <section class="col-12 col-lg">
        <div id="question-wrap" class="card p-4"></div>

        <!-- bottom controls -->
        <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center">
          <button id="prev-btn" class="btn btn-light"><i class="fa-solid fa-arrow-left me-2"></i>Previous</button>
          <button id="review-btn" class="btn btn-light"><i class="fa-solid fa-flag me-2"></i>Mark Review</button>
          <button id="next-btn" class="btn btn-primary">Next<i class="fa-solid fa-arrow-right ms-2"></i></button>
        </div>
      </section>
    </div>
  </main>

<script>
/* ==============================
   0) Utilities / Global state
   ============================== */
const $  = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));

const token = sessionStorage.student_token || sessionStorage.token || '';
const studentId = Number(sessionStorage.student_id || 0);

// quiz key from route param (Blade passes it if you use /exam/{quiz})
const QUIZ_KEY =
  (document.querySelector('meta[name="quiz-key"]')?.content || '').trim()
  || new URLSearchParams(location.search).get('quiz') || '';

// Avoid top-level await
if (!QUIZ_KEY) {
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({icon:'error', title:'Missing quiz key', text:'No quiz id/uuid provided.'})
      .then(() => history.back());
  });
}

let ATTEMPT_UUID = localStorage.getItem('attempt_uuid:'+QUIZ_KEY) || null;

let questions = [];           // array of { ... , question_id, question_type, has_multiple_correct_answer, answers: [...] }
let selections = {};          // { [qid]: (int|int[]|string|null) } from server
let reviews = {};             // local mark-for-review flags
let visited = {};             // local visited flags
let currentIndex = 0;
let timeLeft = 0;             // seconds
let timerHandle = null;

/* API helper */
async function api(path, opts={}) {
  const res = await fetch(path, {
    ...opts,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...(opts.headers||{})
    }
  });
  const data = await res.json().catch(()=> ({}));
  if (!res.ok || data.success === false) {
    const msg = data.message || `HTTP ${res.status}`;
    throw new Error(msg);
  }
  return data;
}

/* Time formatting */
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

/* ==============================
   1) Boot: start attempt + load questions
   ============================== */
document.addEventListener('DOMContentLoaded', init);

async function init(){
  try{
    if (!QUIZ_KEY) return;

    // Always call /start — idempotent and returns running attempt if any
    const started = await api('/api/exam/start', {
      method:'POST',
      body: JSON.stringify({ quiz: QUIZ_KEY })
    });

    const attempt = started.attempt || {};
    ATTEMPT_UUID = attempt.attempt_uuid || ATTEMPT_UUID;
    localStorage.setItem('attempt_uuid:'+QUIZ_KEY, ATTEMPT_UUID);

    // Title + timer
    $('#exam-title').textContent = attempt.quiz_name || 'Exam';
    startTimer(attempt.time_left_sec ?? attempt.total_time_sec ?? 0);

    // Load questions + previously saved selections
    await loadQuestions();

    // Paint navigator & first question
    buildNavigator();
    renderQuestion();

    // Wire controls
    $('#prev-btn').addEventListener('click', onPrev);
    $('#next-btn').addEventListener('click', onNext);
    $('#review-btn').addEventListener('click', onToggleReview);
    $('#submit-btn').addEventListener('click', () => doSubmit(false));

    // Periodically sync time_left with server (safety)
    setInterval(syncStatus, 30000);

  }catch(e){
    console.error(e);
    Swal.fire({icon:'error', title:'Cannot start exam', text: e.message || 'Please try again.'});
  }
}

async function loadQuestions(){
  const data = await api(`/api/exam/attempts/${ATTEMPT_UUID}/questions`);
  questions   = data.questions || [];
  selections  = data.selections || {};
  timeLeft    = (data.attempt && typeof data.attempt.time_left_sec === 'number') ? data.attempt.time_left_sec : timeLeft;
  $('#time-left').textContent = mmss(timeLeft);

  $('#progress-total').textContent = String(questions.length);
  updateProgress();
}

/* ==============================
   2) Navigator / progress
   ============================== */
function buildNavigator(){
  const grid = $('#nav-grid');
  grid.innerHTML = '';
  questions.forEach((q, idx) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'nav-btn';
    b.textContent = String(idx+1);
    b.addEventListener('click', async () => {
      await flushCurrentSelection();      // attribute time slice
      currentIndex = idx;
      renderQuestion();
    });
    grid.appendChild(b);
  });
  refreshNav();
}

function answeredVal(qid){
  const sel = selections[qid];
  if (sel == null) return false;
  return Array.isArray(sel) ? sel.length > 0 : String(sel).trim() !== '';
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

/* ==============================
   3) Render / collect selection (Bootstrap markup)
   ============================== */
function renderQuestion(){
  const q = questions[currentIndex];
  if (!q) return;

  visited[q.question_id] = true;
  refreshNav();
  updateProgress();

  const wrap = $('#question-wrap');
  const multi = !!q.has_multiple_correct_answer;
  const type  = String(q.question_type || '').toLowerCase();

  let body = `
    <div class="d-flex align-items-start justify-content-between gap-3">
      <div>
        <div class="kpi fs-6 mb-1">Q${currentIndex+1}. ${q.question_title ?? ''}</div>
        ${q.question_description ? `<div class="small text-muted mb-2">${q.question_description}</div>` : ``}
        <div class="small text-muted">Marks: <b>${q.question_mark ?? 1}</b> • Type: ${q.question_type}</div>
      </div>
      <span class="badge rounded-pill text-bg-info ${reviews[q.question_id] ? '' : 'invisible'}">Review</span>
    </div>
    <div class="mt-3" id="options">`;

  const sel = selections[q.question_id];

  if (type === 'fill_in_the_blank') {
    const value = (sel ?? '').toString();
    body += `
      <div class="opt">
        <label class="form-label small">Your answer</label>
        <input id="fib-input" class="form-control" placeholder="Type your answer…" value="${escapeHtml(value)}"/>
      </div>`;
  } else {
    (q.answers || []).forEach(a => {
      const checked = multi
        ? Array.isArray(sel) && sel.includes(a.answer_id)
        : (!Array.isArray(sel) && Number(sel) === Number(a.answer_id));

      body += `
      <div class="opt form-check">
        <input 
          class="form-check-input" 
          id="ans_${a.answer_id}"
          type="${multi ? 'checkbox' : 'radio'}"
          name="q_${q.question_id}${multi ? '[]' : ''}"
          value="${a.answer_id}"
          ${checked ? 'checked' : ''} />
        <label class="form-check-label" for="ans_${a.answer_id}">${a.answer_title ?? ''}</label>
      </div>`;
    });
  }

  body += `</div>`;
  wrap.innerHTML = body;

  // Math typeset
  MathJax.typesetPromise([wrap]);

  // Wire change handlers → saveAnswer
  if (type === 'fill_in_the_blank') {
    const input = $('#fib-input');
    let t = null;
    input.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => saveSelection(q, input.value), 400);
    });
    input.addEventListener('blur', () => saveSelection(q, input.value));
  } else {
    $$('#options input').forEach(inp => {
      inp.addEventListener('change', async () => {
        const val = collectSelectionFor(q);
        await saveSelection(q, val);
      });
    });
  }

  // Buttons
  $('#prev-btn').disabled = currentIndex === 0;
  $('#next-btn').innerHTML = (currentIndex < questions.length - 1)
    ? `Next<i class="fa-solid fa-arrow-right ms-2"></i>`
    : `Submit<i class="fa-solid fa-paper-plane ms-2"></i>`;
  $('#review-btn').innerHTML = reviews[q.question_id]
    ? `<i class="fa-solid fa-flag me-2"></i>Unmark Review`
    : `<i class="fa-solid fa-flag me-2"></i>Mark Review`;
}

function collectSelectionFor(q){
  const multi = !!q.has_multiple_correct_answer;
  if (String(q.question_type || '').toLowerCase() === 'fill_in_the_blank') {
    return ($('#fib-input')?.value ?? '').toString();
  }
  const checked = $$('#options input:checked').map(i => Number(i.value));
  return multi ? checked : (checked[0] ?? null);
}

async function saveSelection(q, selected){
  try{
    const payload = { question_id: Number(q.question_id), selected: selected ?? null };
    const res = await api(`/api/exam/attempts/${ATTEMPT_UUID}/answer`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    // persist locally for UI
    selections[q.question_id] = selected ?? null;
    // update time if server sends it
    if (res.attempt && typeof res.attempt.time_left_sec === 'number') {
      timeLeft = res.attempt.time_left_sec;
      $('#time-left').textContent = mmss(timeLeft);
    }
    updateProgress();
    refreshNav();
  }catch(e){
    console.warn('saveSelection failed:', e.message);
    // If server says time over, try to submit
    if ((e.message||'').toLowerCase().includes('time over')) {
      await doSubmit(true);
    }
  }
}

/* attribute time slice on navigation even if selection unchanged */
async function flushCurrentSelection(){
  const q = questions[currentIndex];
  if (!q) return;
  const val = collectSelectionFor(q);
  try {
    await api(`/api/exam/attempts/${ATTEMPT_UUID}/answer`, {
      method: 'POST',
      body: JSON.stringify({ question_id: Number(q.question_id), selected: val ?? null })
    });
    selections[q.question_id] = val ?? null;
  } catch(e) {
    console.warn('flush failed:', e.message);
  }
}

/* ==============================
   4) Nav buttons
   ============================== */
async function onPrev(){
  await flushCurrentSelection();
  if (currentIndex > 0) currentIndex--;
  renderQuestion();
}
async function onNext(){
  await flushCurrentSelection();
  if (currentIndex < questions.length - 1) {
    currentIndex++;
    renderQuestion();
  } else {
    await doSubmit(false);
  }
}
function onToggleReview(){
  const q = questions[currentIndex];
  if (!q) return;
  reviews[q.question_id] = !reviews[q.question_id];
  renderQuestion();
}

/* ==============================
   5) Submit
   ============================== */
async function doSubmit(auto){
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

    // Clean attempt cache
    localStorage.removeItem('attempt_uuid:'+QUIZ_KEY);
    // Redirect to student dashboard / results page if you have one
    location.href = '/student';
  }catch(e){
    console.error(e);
    Swal.fire({icon:'error', title:'Submit failed', text: e.message || 'Please try again.'});
  }
}

/* ==============================
   6) Status sync (safety)
   ============================== */
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

/* ==============================
   7) Small helpers
   ============================== */
function escapeHtml(str){
  return (str ?? '').toString()
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>
</body>
</html>
