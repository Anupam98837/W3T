{{-- resources/views/dashboard.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@push('styles')
<style>
  /* ===== Shell ===== */
  .dash-wrap{
    max-width:1180px;
    margin:16px auto 40px;
  }

  .dash-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:16px;
  }

  .dash-head-left{
    display:flex;
    align-items:center;
    gap:10px;
  }

  .dash-pill{
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding:10px 14px;
    border-radius:999px;
    background:var(--surface);
    border:1px solid var(--line-strong);
    box-shadow:var(--shadow-2);
  }

  .dash-pill-icon{
    width:30px;height:30px;
    border-radius:999px;
    display:flex;align-items:center;justify-content:center;
    background:var(--t-primary);
    color:var(--primary-color);
  }

  .dash-title-main{
    font-family:var(--font-head);
    font-weight:700;
    color:var(--ink);
    font-size:1.15rem;
  }

  .dash-title-sub{
    font-size:var(--fs-13);
    color:var(--muted-color);
  }

  .dash-head-right{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:var(--fs-13);
    color:var(--muted-color);
  }

  .role-chip{
    padding:5px 10px;
    border-radius:999px;
    background:var(--surface);
    border:1px solid var(--line-strong);
    font-size:var(--fs-12);
    font-weight:600;
    color:var(--secondary-color);
    text-transform:uppercase;
    letter-spacing:.05em;
  }

  .last-update{
    color:var(--muted-color);
  }

  .btn-reload{
    border-radius:999px;
    border:1px solid var(--line-strong);
    background:var(--surface);
    padding:5px 12px;
    font-size:var(--fs-13);
    display:inline-flex;
    align-items:center;
    gap:6px;
  }

  .btn-reload i{font-size:12px;}

  /* Stats grid */
  .stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(210px,1fr));
    gap:14px;
    margin-bottom:18px;
  }

  .stat-card{
    background:var(--surface);
    border:1px solid var(--line-strong);
    border-radius:16px;
    padding:14px 14px 12px;
    box-shadow:var(--shadow-2);
    display:flex;
    flex-direction:column;
    gap:4px;
    height:100%;
  }

  .stat-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:4px;
  }

  .stat-icon{
    width:40px;height:40px;
    border-radius:14px;
    display:flex;align-items:center;justify-content:center;
    background:var(--surface-2);
    color:var(--secondary-color);
    flex-shrink:0;
  }

  .stat-kicker{
    font-size:var(--fs-12);
    text-transform:uppercase;
    letter-spacing:.06em;
    color:var(--muted-color);
  }

  .stat-value{
    font-size:1.7rem;
    font-weight:700;
    color:var(--ink);
    line-height:1.1;
  }

  .stat-label{
    font-size:var(--fs-13);
    color:var(--muted-color);
  }

  .stat-meta{
    font-size:var(--fs-12);
    color:var(--secondary-color);
    margin-top:2px;
  }

  /* Panels */
  .dash-panel{
    background:var(--surface);
    border:1px solid var(--line-strong);
    border-radius:16px;
    box-shadow:var(--shadow-2);
    padding:14px 14px 12px;
    height:100%;
  }

  .dash-panel-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    margin-bottom:8px;
  }

  .dash-panel-title{
    font-family:var(--font-head);
    font-weight:600;
    color:var(--ink);
    font-size:var(--fs-15);
  }

  .dash-panel-sub{
    font-size:var(--fs-12);
    color:var(--muted-color);
  }

  /* Charts */
  .chart-shell{
    position:relative;
    height:260px;
  }

  .chart-shell-sm{
    position:relative;
    height:190px;
  }

  /* Lists */
  .top-list{
    max-height:230px;
    overflow:auto;
  }

  .top-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    padding:8px 0;
    border-bottom:1px solid var(--line-soft);
  }

  .top-item:last-child{border-bottom:none;}

  .top-item-main{ flex:1; }

  .top-item-title{
    font-size:var(--fs-13);
    font-weight:500;
    color:var(--ink);
    margin-bottom:2px;
  }

  .top-item-meta{
    font-size:var(--fs-12);
    color:var(--muted-color);
  }

  .top-item-badge{
    font-size:var(--fs-12);
    font-weight:600;
    color:var(--secondary-color);
  }

  .empty{
    text-align:center;
    padding:14px 0;
    font-size:var(--fs-13);
    color:var(--muted-color);
  }

  @media (max-width: 768px){
    .dash-head{flex-direction:column;align-items:flex-start;}
  }
</style>
@endpush

@section('content')
<div class="dash-wrap">
  {{-- Header --}}
  <div class="dash-head">
    <div class="dash-head-left">
      <div class="dash-pill">
        <div class="dash-pill-icon">
          <i class="fa-solid fa-chart-line"></i>
        </div>
        <div>
          <div class="dash-title-main" id="dashTitle">Dashboard</div>
          <div class="dash-title-sub" id="dashSubtitle">Live overview</div>
        </div>
      </div>
    </div>
    <div class="dash-head-right">
      <span class="role-chip" id="roleChip">—</span>
      <span class="last-update" id="lastUpdated">Updated just now</span>
      <button class="btn-reload" id="btnReload">
        <i class="fa-solid fa-rotate-right"></i>
        Reload
      </button>
    </div>
  </div>

  {{-- Quick stats --}}
  <div class="stats-grid" id="statsGrid">
    {{-- Filled by JS --}}
  </div>

  {{-- Charts row --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-8">
      <div class="dash-panel">
        <div class="dash-panel-head">
          <div>
            <div class="dash-panel-title" id="chartPrimaryTitle">Overview</div>
            <div class="dash-panel-sub" id="chartPrimarySub">Key stats</div>
          </div>
        </div>
        <div class="chart-shell">
          <canvas id="chartPrimary"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dash-panel">
        <div class="dash-panel-head">
          <div>
            <div class="dash-panel-title" id="chartSecondaryTitle">Snapshot</div>
            <div class="dash-panel-sub" id="chartSecondarySub">Secondary insights</div>
          </div>
        </div>
        <div class="chart-shell-sm">
          <canvas id="chartSecondary"></canvas>
        </div>
        <div class="small text-muted mt-2" id="chartSecondaryLegend"></div>
      </div>
    </div>
  </div>

  {{-- Lists row (role-aware) --}}
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="dash-panel">
        <div class="dash-panel-head">
          <div>
            <div class="dash-panel-title" id="list1_title">List A</div>
            <div class="dash-panel-sub" id="list1_sub">—</div>
          </div>
        </div>
        <div id="list1" class="top-list">
          <div class="empty">
            <span class="spinner-border spinner-border-sm me-2"></span> Loading…
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dash-panel">
        <div class="dash-panel-head">
          <div>
            <div class="dash-panel-title" id="list2_title">List B</div>
            <div class="dash-panel-sub" id="list2_sub">—</div>
          </div>
        </div>
        <div id="list2" class="top-list">
          <div class="empty">
            <span class="spinner-border spinner-border-sm me-2"></span> Loading…
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dash-panel">
        <div class="dash-panel-head">
          <div>
            <div class="dash-panel-title" id="list3_title">List C</div>
            <div class="dash-panel-sub" id="list3_sub">—</div>
          </div>
        </div>
        <div id="list3" class="top-list">
          <div class="empty">
            <span class="spinner-border spinner-border-sm me-2"></span> Loading…
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
  <div id="toastOk" class="toast text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastOkText">Dashboard updated</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastErr" class="toast text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastErrText">Failed to load dashboard</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
<script>
(function () {
  const API_SUMMARY = "{{ url('/api/dashboard') }}";

  const els = {
    title:  document.getElementById('dashTitle'),
    sub:    document.getElementById('dashSubtitle'),
    role:   document.getElementById('roleChip'),
    time:   document.getElementById('lastUpdated'),
    reload: document.getElementById('btnReload'),
    statsGrid: document.getElementById('statsGrid'),
    chartPrimaryTitle: document.getElementById('chartPrimaryTitle'),
    chartPrimarySub:   document.getElementById('chartPrimarySub'),
    chartSecondaryTitle: document.getElementById('chartSecondaryTitle'),
    chartSecondarySub:   document.getElementById('chartSecondarySub'),
    chartSecondaryLegend: document.getElementById('chartSecondaryLegend'),
    list1_title: document.getElementById('list1_title'),
    list1_sub:   document.getElementById('list1_sub'),
    list1:       document.getElementById('list1'),
    list2_title: document.getElementById('list2_title'),
    list2_sub:   document.getElementById('list2_sub'),
    list2:       document.getElementById('list2'),
    list3_title: document.getElementById('list3_title'),
    list3_sub:   document.getElementById('list3_sub'),
    list3:       document.getElementById('list3')
  };

  const toastOk  = new bootstrap.Toast(document.getElementById('toastOk'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastErr'));

  let chartPrimary = null;
  let chartSecondary = null;

  const donutColors = [
    'rgba(149, 30, 170, 0.9)',
    'rgba(201, 79, 240, 0.85)',
    'rgba(94, 21, 112, 0.85)',
    'rgba(0, 0, 0, 0.65)',
    'rgba(148, 163, 184, 0.9)',
    'rgba(56, 189, 248, 0.9)'
  ];

  function token() {
    return sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  }

  function esc(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, m => (
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]
    ));
  }

  function niceDate(dStr) {
    if (!dStr) return '';
    const d = new Date((dStr || '').replace(' ', 'T'));
    if (isNaN(d.getTime())) return dStr;
    return d.toLocaleString('en-IN', {
      day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'
    });
  }

  function shortDate(dStr) {
    if (!dStr) return '';
    const d = new Date((dStr || '').replace(' ', 'T'));
    if (isNaN(d.getTime())) return dStr;
    return d.toLocaleDateString('en-IN', {day:'2-digit', month:'short'});
  }

  function setEmpty(el, text) {
    el.innerHTML = `<div class="empty">${esc(text || 'No data')}</div>`;
  }

  function statCard(label, value, icon, meta, subLabel) {
    return `
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon">
            <i class="fa-solid ${icon}"></i>
          </div>
          <div class="stat-kicker">${esc(label)}</div>
        </div>
        <div class="stat-value">${value}</div>
        ${subLabel ? `<div class="stat-label">${esc(subLabel)}</div>` : ''}
        ${meta ? `<div class="stat-meta">${esc(meta)}</div>` : ''}
      </div>`;
  }

  async function loadSummary() {
    try {
      els.list1.innerHTML = els.list2.innerHTML = els.list3.innerHTML =
        '<div class="empty"><span class="spinner-border spinner-border-sm me-2"></span> Loading…</div>';

      const res = await fetch(API_SUMMARY, {
        headers: {
          'Authorization': 'Bearer ' + token(),
          'Accept': 'application/json'
        }
      });

      const json = await res.json().catch(() => ({}));
      if (!res.ok || !json.ok) {
        throw new Error(json.error || 'Failed to load dashboard');
      }

      const roleRaw = (json.role || '').toString();
      const role = roleRaw.toLowerCase();
      const data = json.data || {};
      const user = json.user || null;

      renderHeader(role, user, json.time);
      renderBody(role, data);

      toastOk.show();
    } catch (e) {
      console.error('[dashboard] error:', e);
      document.getElementById('toastErrText').textContent = e.message || 'Failed to load dashboard';
      toastErr.show();
      setEmpty(els.list1, 'Failed to load');
      setEmpty(els.list2, 'Failed to load');
      setEmpty(els.list3, 'Failed to load');
      if (chartPrimary) { chartPrimary.destroy(); chartPrimary = null; }
      if (chartSecondary) { chartSecondary.destroy(); chartSecondary = null; }
    }
  }

  function renderHeader(role, user, time) {
    let niceRole = role || 'user';
    if (role === 'superadmin') niceRole = 'super admin';

    if (role === 'superadmin' || role === 'admin') {
      els.title.textContent = 'Admin Dashboard';
      els.sub.textContent   = 'Overview of users, courses, batches and notices';
    } else if (role === 'instructor') {
      els.title.textContent = 'Instructor Dashboard';
      els.sub.textContent   = 'Your batches, students, grading queue and quizzes';
    } else {
      els.title.textContent = 'Student Dashboard';
      els.sub.textContent   = 'Your courses, assignments and upcoming quizzes';
    }

    const name = user && user.name ? user.name : '';
    els.role.textContent = (name ? name + ' · ' : '') + niceRole.toUpperCase();
    els.time.textContent = 'Updated ' + niceDate(time);
  }

  function renderBody(role, data) {
    const counts  = data.counts  || {};
    const widgets = data.widgets || {};

    const r = (role === 'superadmin' ? 'admin' : role); // superadmin behaves like admin

    renderStats(r, counts, widgets);
    renderPrimaryChart(r, counts);
    renderSecondaryChart(r, widgets);
    renderLists(r, widgets);
  }

  /* ===== Stats ===== */
  function renderStats(role, counts, widgets = {}) {
    const grid = els.statsGrid;
    grid.innerHTML = '';

    let items = [];

    if (role === 'admin') {
      const users   = Number(counts.users_total || 0);
      const courses = Number(counts.courses_total || 0);
      const batches = Number(counts.batches_active || 0);
      const quizzes = Number(counts.quizzes_total || 0);
      const pending = Number(counts.pending_to_grade || 0);

      items = [
        {label:'Users', value:users, icon:'fa-users', sub:'Total registered users'},
        {label:'Courses', value:courses, icon:'fa-book', sub:'Total courses'},
        {label:'Batches', value:batches, icon:'fa-layer-group', sub:'Active batches'},
        {label:'Quizzes', value:quizzes, icon:'fa-circle-question', sub:'Total quizzes'},
        {label:'Pending', value:pending, icon:'fa-clipboard-check', sub:'Submissions to grade'}
      ];
    } else if (role === 'instructor') {
      const myBatches  = Number(counts.my_batches || 0);
      const myStudents = Number(counts.my_students || 0);
      const toGrade    = Number(counts.submissions_to_grade || 0);
      const upcoming   = Number(counts.upcoming_quizzes || 0);

      items = [
        {label:'My Batches', value:myBatches, icon:'fa-layer-group', sub:'Assigned to you'},
        {label:'My Students', value:myStudents, icon:'fa-user-graduate', sub:'Unique learners'},
        {label:'To Grade', value:toGrade, icon:'fa-clipboard-check', sub:'Pending submissions'},
        {label:'Upcoming Quizzes', value:upcoming, icon:'fa-calendar-day', sub:'Scheduled quizzes'}
      ];
    } else { // student
      const myBatches = Number(counts.my_batches || 0);
      const active    = Number(counts.my_active_courses || 0);
      const pending   = Number(counts.pending_assignments || 0);
      const qup       = Number(counts.active_upcoming_quizzes || 0);
      const notices   = Array.isArray(widgets.recent_notices) ? widgets.recent_notices.length : 0;

      items = [
        {label:'My Courses', value:active, icon:'fa-book', sub:'Active right now'},
        {label:'My Batches', value:myBatches, icon:'fa-layer-group', sub:'Enrolled batches'},
        {label:'Pending Assignments', value:pending, icon:'fa-list-check', sub:'Not yet graded / submitted'},
        {label:'Upcoming Quizzes', value:qup, icon:'fa-calendar-day', sub:'Live or scheduled'},
        {label:'Notices', value:notices, icon:'fa-bell', sub:'Relevant updates'}
      ];
    }

    items.forEach(cardData => {
      grid.insertAdjacentHTML(
        'beforeend',
        statCard(cardData.label, cardData.value, cardData.icon, '', cardData.sub)
      );
    });
  }

  /* ===== Charts ===== */

  function renderPrimaryChart(role, counts) {
    const ctx = document.getElementById('chartPrimary').getContext('2d');
    if (chartPrimary) chartPrimary.destroy();

    let labels = [];
    let values = [];

    if (role === 'admin') {
      labels = ['Users','Courses','Batches','Quizzes','Pending'];
      values = [
        Number(counts.users_total || 0),
        Number(counts.courses_total || 0),
        Number(counts.batches_active || 0),
        Number(counts.quizzes_total || 0),
        Number(counts.pending_to_grade || 0)
      ];
      els.chartPrimaryTitle.textContent = 'Platform overview';
      els.chartPrimarySub.textContent   = 'Counts across core entities';
    } else if (role === 'instructor') {
      labels = ['My Batches','My Students','To Grade','Upcoming Quizzes'];
      values = [
        Number(counts.my_batches || 0),
        Number(counts.my_students || 0),
        Number(counts.submissions_to_grade || 0),
        Number(counts.upcoming_quizzes || 0)
      ];
      els.chartPrimaryTitle.textContent = 'Teaching overview';
      els.chartPrimarySub.textContent   = 'Batches, students and workload';
    } else {
      labels = ['Courses','Batches','Assignments','Quizzes'];
      values = [
        Number(counts.my_active_courses || 0),
        Number(counts.my_batches || 0),
        Number(counts.pending_assignments || 0),
        Number(counts.active_upcoming_quizzes || 0)
      ];
      els.chartPrimaryTitle.textContent = 'Study overview';
      els.chartPrimarySub.textContent   = 'Active items and pending work';
    }

    chartPrimary = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Count',
          data: values,
          borderWidth: 1,
          borderColor: 'rgba(0,0,0,0.12)',
          backgroundColor: [
            'rgba(149, 30, 170, 0.24)',
            'rgba(201, 79, 240, 0.24)',
            'rgba(94, 21, 112, 0.22)',
            'rgba(0, 0, 0, 0.08)',
            'rgba(148, 163, 184, 0.20)'
          ].slice(0, labels.length)
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display:false } },
        scales: {
          y: { beginAtZero:true, ticks:{ precision:0 } },
          x: { grid:{ display:false } }
        }
      }
    });
  }

  function renderSecondaryChart(role, widgets) {
    const canvas = document.getElementById('chartSecondary');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (chartSecondary) chartSecondary.destroy();

    let labels = [];
    let values = [];
    let title  = '';
    let sub    = '';
    let legend = '';

    if (role === 'admin') {
      const users = widgets.recent_users || [];
      const byRole = {};
      users.forEach(u => {
        const r = (u.role || 'other').toString().toLowerCase();
        byRole[r] = (byRole[r] || 0) + 1;
      });
      labels = Object.keys(byRole);
      values = labels.map(k => byRole[k]);
      title  = 'User mix';
      sub    = 'Role distribution from latest users';
      legend = 'Based on last few created users.';
    } else if (role === 'instructor') {
      const batches = widgets.my_batches || [];
      labels = batches.map(b => b.batch_name || ('Batch #' + b.id));
      values = batches.map(b => Number(b.student_count || 0));
      title  = 'Students per batch';
      sub    = 'Relative student load across your batches';
      legend = 'Each slice = a batch (up to 8).';
    } else {
      const act = widgets.my_activity || {};
      const subs = Array.isArray(act.recent_submissions) ? act.recent_submissions : [];
      const atts = Array.isArray(act.recent_quiz_attempts) ? act.recent_quiz_attempts : [];
      labels = ['Assignment submissions','Quiz attempts'];
      values = [subs.length, atts.length];
      title  = 'My activity';
      sub    = 'Assignments vs quizzes in recent history';
      legend = 'Shows counts from your latest submissions and attempts.';
    }

    els.chartSecondaryTitle.textContent = title || 'Snapshot';
    els.chartSecondarySub.textContent   = sub || 'Secondary insights';
    els.chartSecondaryLegend.textContent = legend;

    if (!labels.length || !values.some(v => v > 0)) {
      chartSecondary = null;
      return;
    }

    chartSecondary = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: values,
          borderWidth: 1,
          borderColor: 'rgba(255,255,255,0.9)',
          backgroundColor: donutColors.slice(0, labels.length)
        }]
      },
      options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins: {
          legend: {
            display:true,
            position:'bottom',
            labels:{ boxWidth:12, font:{ size:11 } }
          }
        },
        cutout:'60%'
      }
    });
  }

  /* ===== Lists ===== */

  function renderLists(role, widgets) {
    if (role === 'admin') {
      els.list1_title.textContent = 'Latest Notices';
      els.list1_sub.textContent   = 'Fresh updates';
      fillNotices(els.list1, widgets.latest_notices || []);

      els.list2_title.textContent = 'Recent Courses';
      els.list2_sub.textContent   = 'Latest created';
      fillCourses(els.list2, widgets.recent_courses || []);

      els.list3_title.textContent = 'Recent Batches';
      els.list3_sub.textContent   = 'Latest created';
      fillBatches(els.list3, widgets.recent_batches || []);
    } else if (role === 'instructor') {
      els.list1_title.textContent = 'My Batches';
      els.list1_sub.textContent   = 'With student counts';
      fillInstructorBatches(els.list1, widgets.my_batches || []);

      els.list2_title.textContent = 'Pending to Grade';
      els.list2_sub.textContent   = 'Assignments queue';
      fillPendingGrades(els.list2, widgets.pending_to_grade || []);

      els.list3_title.textContent = 'Recent Messages';
      els.list3_sub.textContent   = 'Batch announcements';
      fillMessages(els.list3, widgets.recent_messages || []);
    } else {
      els.list1_title.textContent = 'My Courses';
      els.list1_sub.textContent   = 'By batch';
      fillStudentCourses(els.list1, widgets.my_courses || []);

      els.list2_title.textContent = 'Upcoming deadlines';
      els.list2_sub.textContent   = 'Assignments & quizzes';
      fillUpcomingDeadlines(
        els.list2,
        widgets.upcoming_assignments || [],
        widgets.upcoming_quizzes || []
      );

      els.list3_title.textContent = 'My recent activity';
      els.list3_sub.textContent   = 'Latest submissions and attempts';
      const act = widgets.my_activity || {};
      fillStudentActivity(
        els.list3,
        Array.isArray(act.recent_submissions) ? act.recent_submissions : [],
        Array.isArray(act.recent_quiz_attempts) ? act.recent_quiz_attempts : []
      );
    }
  }

  /* ==== individual fillers ==== */

  function fillNotices(el, rows) {
    if (!rows.length) return setEmpty(el, 'No notices yet');
    el.innerHTML = rows.map(n => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">${esc(n.title || ('Notice #' + n.id))}</div>
          <div class="top-item-meta">${shortDate(n.created_at)}</div>
        </div>
        <div class="top-item-badge">#${n.id}</div>
      </div>`).join('');
  }

  function fillCourses(el, rows) {
    if (!rows.length) return setEmpty(el, 'No courses yet');
    el.innerHTML = rows.map(c => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">${esc(c.title || ('Course #' + c.id))}</div>
          <div class="top-item-meta">${esc(c.slug || '')} • ${shortDate(c.created_at)}</div>
        </div>
        <div class="top-item-badge">#${c.id}</div>
      </div>`).join('');
  }

  function fillBatches(el, rows) {
    if (!rows.length) return setEmpty(el, 'No batches yet');
    el.innerHTML = rows.map(b => {
      const name = b.batch_name || ('Batch #' + b.id);
      const code = b.batch_code || '—';
      return `
        <div class="top-item">
          <div class="top-item-main">
            <div class="top-item-title">${esc(name)}</div>
            <div class="top-item-meta">
              ${esc(code)} • ${esc(b.course_title || '—')} • ${shortDate(b.created_at)}
            </div>
          </div>
          <div class="top-item-badge">#${b.id}</div>
        </div>`;
    }).join('');
  }

  function fillInstructorBatches(el, rows) {
    if (!rows.length) return setEmpty(el, 'No assigned batches yet');
    el.innerHTML = rows.map(b => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">${esc(b.batch_name || ('Batch #' + b.id))}</div>
          <div class="top-item-meta">
            ${esc(b.course_title || '—')} • ${shortDate(b.created_at)}
          </div>
        </div>
        <div class="top-item-badge">${Number(b.student_count || 0)} students</div>
      </div>`).join('');
  }

  function fillPendingGrades(el, rows) {
    if (!rows.length) return setEmpty(el, 'Nothing to grade 🎉');
    el.innerHTML = rows.map(r => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">
            ${esc(r.assignment_title || ('Assignment #' + (r.assignment_id || '')))}
          </div>
          <div class="top-item-meta">
            Batch #${r.batch_id || '—'} • Last: ${shortDate(r.last_submitted_at)}
          </div>
        </div>
        <div class="top-item-badge">${Number(r.pending_count || 0)}</div>
      </div>`).join('');
  }

  function fillMessages(el, rows) {
    if (!rows.length) return setEmpty(el, 'No messages yet');
    el.innerHTML = rows.map(m => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">${esc(m.title || 'Message')}</div>
          <div class="top-item-meta">
            Batch #${m.batch_id || '—'} • ${shortDate(m.created_at)}
          </div>
        </div>
        <div class="top-item-badge">#${m.id}</div>
      </div>`).join('');
  }

  function fillStudentCourses(el, rows) {
    if (!rows.length) return setEmpty(el, 'No courses yet');
    el.innerHTML = rows.map(r => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">${esc(r.course_title || 'Course')}</div>
          <div class="top-item-meta">
            ${esc(r.batch_name || ('Batch #' + (r.batch_id || '')))} • ${esc(r.batch_code || '—')}
          </div>
        </div>
        <div class="top-item-badge">#${r.batch_id}</div>
      </div>`).join('');
  }

  function fillUpcomingDeadlines(el, assignments, quizzes) {
    const items = [];

    (assignments || []).forEach(a => {
      const when = a.due_at || a.created_at;
      items.push({
        type: 'Assignment',
        title: a.title || ('Assignment #' + a.id),
        when,
        batch: a.batch_id || null,
        id: a.id
      });
    });

    (quizzes || []).forEach(q => {
      const when = q.start_at || q.schedule_at || q.end_at || q.created_at;
      items.push({
        type: 'Quiz',
        title: q.title || ('Quiz #' + (q.quizz_id || q.id || '')),
        when,
        batch: q.batch_id || null,
        id: q.quizz_id || q.id
      });
    });

    items.sort((a,b) => {
      const da = new Date((a.when || '').replace(' ', 'T'));
      const db = new Date((b.when || '').replace(' ', 'T'));
      return da - db;
    });

    if (!items.length) return setEmpty(el, 'No upcoming deadlines');

    el.innerHTML = items.map(it => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">
            ${esc(it.type)}: ${esc(it.title)}
          </div>
          <div class="top-item-meta">
            Batch #${it.batch || '—'} • ${niceDate(it.when)}
          </div>
        </div>
        <div class="top-item-badge">#${it.id || ''}</div>
      </div>`).join('');
  }

  function fillStudentActivity(el, submissions, attempts) {
    const items = [];

    (submissions || []).forEach(s => {
      items.push({
        type: 'Assignment',
        title: 'Submission #' + (s.assignment_id || s.id),
        status: s.status || 'submitted',
        when: s.created_at,
        batch: s.batch_id || null
      });
    });

    (attempts || []).forEach(a => {
      items.push({
        type: 'Quiz',
        title: 'Quiz #' + (a.quizz_id || a.id),
        status: a.status || 'attempted',
        when: a.created_at,
        batch: a.batch_id || null
      });
    });

    items.sort((a,b) => {
      const da = new Date((a.when || '').replace(' ', 'T'));
      const db = new Date((b.when || '').replace(' ', 'T'));
      return db - da; // newest first
    });

    if (!items.length) return setEmpty(el, 'No recent activity');

    el.innerHTML = items.map(it => `
      <div class="top-item">
        <div class="top-item-main">
          <div class="top-item-title">
            ${esc(it.type)} · ${esc(it.title)}
          </div>
          <div class="top-item-meta">
            ${esc(it.status)} • Batch #${it.batch || '—'} • ${niceDate(it.when)}
          </div>
        </div>
      </div>`).join('');
  }

  /* events */
  els.reload.addEventListener('click', loadSummary);

  // initial load
  loadSummary();
})();
</script>
@endsection
