{{-- resources/views/modules/viewCourse/viewCourseLayout.blade.php --}}
@php
  // Tab map (server-side include for first render; JS will swap panes without full reload)
  $tabKey = request('tab', 'recorded');
  $tabs = [
    'recorded'     => 'modules.course.viewCourse.viewCourseTabs.recordedVideos',
    'materials'    => 'modules.course.viewCourse.viewCourseTabs.studyMaterial',
    'assignments'  => 'modules.course.viewCourse.viewCourseTabs.assignments',
    'quizzes'      => 'modules.course.viewCourse.viewCourseTabs.quizzes',
    // NEW: Coding Tests tab (you will link to userCodingTest.blade.php content)
    'codingtests'  => 'modules.course.viewCourse.viewCourseTabs.userCodingTest',   // <- create this view
    'notices'      => 'modules.course.viewCourse.viewCourseTabs.notices',
    'chat'         => 'modules.course.viewCourse.viewCourseTabs.chat',
  ];
  $tabKey     = array_key_exists($tabKey, $tabs) ? $tabKey : 'recorded';
  $tabPartial = $tabs[$tabKey];
  $moduleUuid = request('module'); // optional
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>View Course</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  {{-- Paint theme ASAP to avoid white flash --}}
  <script>
    (function(){
      try{
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const mode = saved || (prefersDark ? 'dark' : 'light');
        if (mode === 'dark') document.documentElement.classList.add('theme-dark');
      }catch(e){}
    })();
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

  {{-- SweetAlert2 used by multiple tabs (avoid reloading on tab switch) --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    /* ===== Page/Layout ===================================================== */
    html, body { height: 100%; }
    body{ background: var(--bg-body); color: var(--text-color); min-height: 100dvh; }

    .vc-wrap{ max-width: 1180px; margin: 18px auto 40px; padding: 0 14px; }
    /* Full width when desktop sidebar is collapsed */
    body.vc-wide .vc-wrap{ max-width: 100%; }

    .vc-grid{
      display:grid;
      grid-template-columns: 360px minmax(0,1fr);
      gap:16px;
      min-height: calc(100dvh - 90px);
    }

    /* Mobile: single column + controlled via .mobile-hidden */
    @media (max-width: 992px){
      .vc-grid{ grid-template-columns: 1fr; }
      .vc-aside.mobile-hidden{ display: none !important; }
      .vc-main.mobile-hidden{ display: none !important; }
    }

    /* Desktop: collapsible sidebar */
    .vc-grid.sidebar-collapsed{
      grid-template-columns: minmax(0,1fr);
    }
    .vc-grid.sidebar-collapsed .vc-aside{ display:none !important; }
    .vc-grid.sidebar-collapsed .vc-main{ grid-column: 1 / -1; }

    /* ===== Back button ===== */
    .back-to-courses-btn{
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      margin-bottom: 16px;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      border-radius: 10px;
      cursor: pointer;
      color: var(--ink);
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
    }
    .back-to-courses-btn:hover{
      background: color-mix(in oklab, var(--accent-color) 8%, transparent);
      border-color: var(--accent-color);
      color: var(--ink);
    }

    /* ===== Aside Back button ===== */
    .aside-back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      border-radius: 8px;
      cursor: pointer;
      color: var(--ink);
      font-weight: 500;
      font-size: var(--fs-13);
      text-decoration: none;
      transition: var(--transition);
      margin-bottom: 12px;
      white-space: nowrap;
    }
    .aside-back-btn:hover {
      background: color-mix(in oklab, var(--accent-color) 8%, transparent);
      border-color: var(--accent-color);
      color: var(--ink);
    }

    /* ===== Mobile back button ===== */
    .mobile-back-btn{
      display: none;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      margin-bottom: 12px;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      border-radius: 10px;
      cursor: pointer;
      color: var(--ink);
      font-weight: 600;
    }
    @media (max-width: 992px){
      .mobile-back-btn{ display: inline-flex; }
    }

    /* ===== Desktop hamburger toggle ===== */
    .vc-side-toggle{
      width: 40px; height: 40px;
      display: inline-flex;
      align-items:center; justify-content:center;
      border-radius: 12px;
      border:1px solid var(--line-strong);
      background: var(--surface);
      color: var(--ink);
      transition: var(--transition);
      flex: 0 0 auto;
    }
    .vc-side-toggle:hover{
      border-color: color-mix(in oklab, var(--accent-color) 55%, transparent);
      background: color-mix(in oklab, var(--accent-color) 8%, transparent);
    }
    @media (max-width: 992px){
      .vc-side-toggle{ display:none !important; }
    }

    /* ===== Left column (sticky card) ====================================== */
    .vc-aside .panel{ padding: 12px; position: sticky; top: 16px; }
    .vc-head-left{
      display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:8px;
    }
    .vc-title{ font-family: var(--font-head); font-weight:700; color:var(--ink); margin:0; font-size:1.12rem; }

    .vc-cover{
      width:100%; aspect-ratio: 16/10;
      border:1px solid var(--line-strong); border-radius:12px; overflow:hidden;
      background:#f6f3fb; display:flex; align-items:center; justify-content:center;
    }
    html.theme-dark .vc-cover{ background:#0e1930; }
    .vc-cover img{ width:100%; height:100%; object-fit:cover; }

    .vc-thumbs{ display:flex; gap:8px; overflow:auto; padding:8px 2px 2px; }
    .vc-thumb{
      width:56px; height:56px; border:1px solid var(--line-strong); border-radius:10px;
      overflow:hidden; flex:0 0 auto; background:#fff; cursor:pointer;
    }
    .vc-thumb img{ width:100%; height:100%; object-fit:cover; }
    html.theme-dark .vc-thumb{ background:#0f172a; }

    .vc-chips{ display:flex; flex-wrap:wrap; gap:8px; margin: 6px 0 4px; }
    .vc-chip{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px;
      border:1px solid var(--line-strong); border-radius:999px; background:#fff; font-size:var(--fs-13);
    }
    html.theme-dark .vc-chip{ background:#0f172a; }

    .vc-batch-details{
      margin: 12px 0;
      padding: 12px;
      border: 1px solid var(--line-strong);
      border-radius: 12px;
      background: var(--surface);
    }
    .vc-batch-title{ font-weight: 600; color: var(--ink); margin-bottom: 4px; }
    .vc-batch-description{ font-size: var(--fs-14); color: var(--text-color); margin-bottom: 8px; line-height: 1.5; }
    .vc-batch-tagline{ font-size: var(--fs-13); color: var(--muted-color); margin-bottom: 8px; }
    .vc-batch-info{ display: flex; flex-wrap: wrap; gap: 8px; font-size: var(--fs-13); }
    .vc-batch-info-item{ display: inline-flex; align-items: center; gap: 4px; color: var(--muted-color); }
    .vc-batch-info-item i{ width: 14px; text-align: center; }

    .vc-search{ position:relative; margin-top:8px; }
    .vc-search input{ padding-left:34px; height:40px; border-radius:12px; }
    .vc-search i{ position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#8a8593; }

    .vc-modules{
      margin-top:10px; display:flex; flex-direction:column; gap:8px;
      max-height: calc(100dvh - 420px); overflow: auto;
    }
    .vc-module{
      border:1px solid var(--line-strong); border-radius:12px; background:var(--surface);
      padding:10px; cursor:pointer; transition: var(--transition);
      display: flex; justify-content: space-between; align-items: center; gap: 10px;
    }
    .vc-module:hover{ border:2px solid color-mix(in oklab, var(--accent-color) 55%, transparent); }
    .vc-module .module-content{ flex: 1; }
    .vc-module .t{ font-weight:600; color:var(--ink); }
    .vc-module .d{ font-size: var(--fs-13); color: var(--muted-color); }
    .vc-module .module-arrow{
      color: var(--muted-color);
      font-size: 1.2rem;
      flex-shrink: 0;
      transition: var(--transition);
    }
    .vc-module.active{ border:2px solid color-mix(in oklab, var(--accent-color) 55%, transparent); }
    .vc-module.active .module-arrow{ color: var(--accent-color); }

    .vc-empty{ border:1px dashed var(--line-strong); border-radius: 10px; padding: 16px; text-align:center; color: var(--muted-color); }

    /* ===== Right column (module header + tabs) ============================ */
    .vc-main .panel{ padding: 14px; min-height: calc(100% - 0px); }
    .vc-top{
      display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:8px;
    }
    .vc-top .title{ font-family:var(--font-head); font-weight:700; color:var(--ink); margin:0; font-size:1.18rem; }
    .vc-top .sub{ color: var(--muted-color); }

    .vc-top-left{
      display:flex;
      align-items:flex-start;
      gap:10px;
      min-width: 0;
    }
    .vc-top-left .txt{
      min-width:0;
    }

    .vc-price{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border:1px solid var(--line-strong);
      border-radius:999px; background:#fff; font-size:var(--fs-13);
      white-space: nowrap;
    }
    html.theme-dark .vc-price{ background:#0f172a; }

    .tabbar{
      margin-top:6px; border-bottom:1px solid var(--line-strong); padding-bottom:4px;
      display:flex; flex-wrap:wrap; gap:6px;
    }
    .tabbar .nav-link{
      border:0; border-radius:10px; padding:8px 12px;
      color: var(--muted-color); background: transparent;
    }
    .tabbar .nav-link i{ width:16px; text-align:center; margin-right:6px; }
    .tabbar .nav-link:hover{ background: rgba(2,6,23,.04); }
    html.theme-dark .tabbar .nav-link:hover{ background:#0c172d; }
    .tabbar .nav-link.active{
      color: var(--ink); background: color-mix(in oklab, var(--accent-color) 12%, transparent);
      box-shadow: var(--shadow-1);
    }

    /* ===== Tab switching (no full page reload) ===== */
    #tabContent{ position: relative; }
    #tabContent.is-loading{
      opacity: .65;
      pointer-events: none;
      filter: saturate(.9);
      transition: opacity .12s ease;
    }
    .vc-tab-spinner{
      position:absolute;
      inset: 10px 10px auto auto;
      display:none;
      align-items:center;
      gap:8px;
      padding:8px 10px;
      background: var(--surface);
      border:1px solid var(--line-strong);
      border-radius: 999px;
      box-shadow: var(--shadow-2);
      z-index: 50;
      color: var(--muted-color);
      font-size: var(--fs-13);
    }
    #tabContent.is-loading .vc-tab-spinner{ display:flex; }
    .vc-dot{
      width: 8px; height: 8px; border-radius: 999px;
      background: var(--muted-color);
      animation: vcPulse 900ms infinite ease-in-out;
    }
    .vc-dot:nth-child(2){ animation-delay: 150ms; }
    .vc-dot:nth-child(3){ animation-delay: 300ms; }
    @keyframes vcPulse{
      0%, 100%{ transform: translateY(0); opacity:.35; }
      50%{ transform: translateY(-3px); opacity:.95; }
    }
  </style>
</head>
<body data-initial-tab="{{ $tabKey }}">
  <main class="vc-wrap">
    <div class="vc-grid" id="vcGrid">
      {{-- ================= LEFT: Overview + Modules ================= --}}
      <aside class="vc-aside" id="vcAside">
        <div class="panel shadow-1">

          <h2>Course Details - </h2>
          <div class="vc-head-left">
            <div>
              <h2 class="vc-title" id="courseTitle">Course</h2>
              <div class="text-muted" id="courseShort">—</div>
            </div>

            <a href="javascript:void(0)" class="aside-back-btn" id="asideBackBtn">
              <i class="fa fa-arrow-left"></i>
              <span>Back To Courses</span>
            </a>
          </div>

          <div class="vc-cover rounded-1 shadow-1 mb-2" id="mediaCover">
            <i class="fa-regular fa-image"></i>
          </div>
          <div class="vc-thumbs" id="mediaThumbs"></div>

          <div class="divider my-2"></div>

          <div class="vc-chips" id="courseChips"></div>

          <h2>Batch Details - </h2>
          <div class="vc-batch-details" id="batchDetails" style="display:none">
            <div class="vc-batch-title" id="batchTitle"></div>
            <div class="vc-batch-description" id="batchDescription"></div>
            <div class="vc-batch-tagline" id="batchTagline"></div>
            <div class="vc-batch-info" id="batchInfo"></div>
          </div>

          <h2>Course Modules - </h2>
          <div class="vc-search">
            <i class="fa fa-search"></i>
            <input id="moduleSearch" type="text" class="form-control" placeholder="Search modules...">
          </div>

          <div class="vc-modules" id="modulesList">
            <div class="vc-empty" id="modulesEmpty" style="display:none">No modules found.</div>
          </div>
        </div>
      </aside>

      {{-- ================= RIGHT: Module Header + Tabs ================= --}}
      <section class="vc-main" id="vcMain">
        <div class="panel shadow-1">
          <div class="mobile-back-btn" id="mobileBackBtn">
            <i class="fa fa-arrow-left"></i>
            <span>Back to Modules</span>
          </div>

          <div class="vc-top">
            <div class="vc-top-left">
              <button class="vc-side-toggle" id="sidebarToggleBtn" type="button" title="Toggle sidebar">
                <i class="fa-solid fa-bars" id="sidebarToggleIcon"></i>
              </button>

              <div class="txt">
                <h1 class="title" id="moduleTitle">Select a module</h1>
                <div class="sub" id="moduleShort">Pick a module from the left to see its content.</div>
              </div>
            </div>

            <div id="pricePill" class="vc-price" style="display:none"></div>
          </div>

          @php
            $self = url()->current();
            $mParam = $moduleUuid ? ('&module='.urlencode($moduleUuid)) : '';
          @endphp

          <ul class="nav tabbar" id="vcTabs">
            <li class="nav-item">
              <a class="nav-link {{ $tabKey==='recorded'?'active':'' }}"
                 data-tab="recorded"
                 href="{{ $self }}?tab=recorded{{ $mParam }}">
                <i class="fa-regular fa-circle-play"></i>Recorded Sessions
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link {{ $tabKey==='materials'?'active':'' }}"
                 data-tab="materials"
                 href="{{ $self }}?tab=materials{{ $mParam }}">
                <i class="fa-regular fa-folder-open"></i>Study Material
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link {{ $tabKey==='assignments'?'active':'' }}"
                 data-tab="assignments"
                 href="{{ $self }}?tab=assignments{{ $mParam }}">
                <i class="fa-regular fa-square-check"></i>Assignments
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link {{ $tabKey==='quizzes'?'active':'' }}"
                 data-tab="quizzes"
                 href="{{ $self }}?tab=quizzes{{ $mParam }}">
                <i class="fa-regular fa-file-lines"></i>Quizzes
              </a>
            </li>

            {{-- NEW TAB: Coding Tests (next to Quizzes) --}}
            <li class="nav-item">
              <a class="nav-link {{ $tabKey==='codingtests'?'active':'' }}"
                 data-tab="codingtests"
                 href="{{ $self }}?tab=codingtests{{ $mParam }}">
                <i class="fa-solid fa-code"></i>Coding Tests
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link {{ $tabKey==='notices'?'active':'' }}"
                 data-tab="notices"
                 href="{{ $self }}?tab=notices{{ $mParam }}">
                <i class="fa-regular fa-bell"></i>Notices
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link {{ $tabKey==='chat'?'active':'' }}"
                 data-tab="chat"
                 href="{{ $self }}?tab=chat{{ $mParam }}">
                <i class="fa-regular fa-comments"></i>Chat
              </a>
            </li>
          </ul>

          <div id="tabContent" class="mt-3">
            <div class="vc-tab-spinner" aria-hidden="true">
              <span class="vc-dot"></span><span class="vc-dot"></span><span class="vc-dot"></span>
              <span>Loading…</span>
            </div>

            {{-- initial render (JS will turn it into a cached pane) --}}
            @includeIf($tabPartial, ['moduleUuid' => $moduleUuid])
          </div>
        </div>
      </section>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
document.addEventListener('DOMContentLoaded', () => {
  const isMobile = () => window.matchMedia('(max-width: 992px)').matches;

  // ===== Derive {uuid} from /.../courses/{uuid}/view OR /.../courses/{uuid}
  const deriveCourseKey = () => {
    const parts = location.pathname.split('/').filter(Boolean);
    const last = parts.at(-1)?.toLowerCase();
    if (last === 'view' && parts.length >= 2) return parts.at(-2);
    if (last === 'courses') return null;
    return parts.at(-1);
  };
  const courseKey = deriveCourseKey();

  // ===== Elements
  const el = {
    grid:   document.getElementById('vcGrid'),
    aside:  document.getElementById('vcAside'),
    main:   document.getElementById('vcMain'),

    title:  document.getElementById('courseTitle'),
    short:  document.getElementById('courseShort'),
    cover:  document.getElementById('mediaCover'),
    thumbs: document.getElementById('mediaThumbs'),
    chips:  document.getElementById('courseChips'),

    mSearch:document.getElementById('moduleSearch'),
    mList:  document.getElementById('modulesList'),
    mEmpty: document.getElementById('modulesEmpty'),

    mTitle: document.getElementById('moduleTitle'),
    mShort: document.getElementById('moduleShort'),
    price:  document.getElementById('pricePill'),

    backBtn: document.getElementById('mobileBackBtn'),
    asideBackBtn: document.getElementById('asideBackBtn'),

    batchDetails: document.getElementById('batchDetails'),
    batchTitle: document.getElementById('batchTitle'),
    batchDescription: document.getElementById('batchDescription'),
    batchTagline: document.getElementById('batchTagline'),
    batchInfo: document.getElementById('batchInfo'),

    tabs:   document.getElementById('vcTabs'),
    tabContent: document.getElementById('tabContent'),

    sideToggleBtn: document.getElementById('sidebarToggleBtn'),
    sideToggleIcon: document.getElementById('sidebarToggleIcon'),
  };

  // ===== Token & role for protected API
  const tok = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  const auth = tok ? { 'Authorization': 'Bearer ' + tok } : {};

  // ===== Module param handling: keep ?module in URL and on tab links
  const qs = new URLSearchParams(location.search);
  let selectedModuleUuid = qs.get('module') || null;

  const getTabFromUrl = () => {
    const p = new URLSearchParams(location.search);
    return p.get('tab') || (document.body.dataset.initialTab || 'recorded');
  };

  // Update tab links to include (or remove) module param
  const syncTabLinks = (moduleUuid) => {
    const links = document.querySelectorAll('#vcTabs .nav-link');
    links.forEach(a => {
      try {
        const u = new URL(a.href, location.origin);
        if (moduleUuid) u.searchParams.set('module', moduleUuid);
        else u.searchParams.delete('module');
        a.href = u.toString();
      } catch (err) {
        // fallback best-effort
      }
    });
  };

  // Utility: update query param without reload
  const updateQueryParam = (key, val) => {
    const usp = new URLSearchParams(location.search);
    if (!val) usp.delete(key); else usp.set(key, val);
    const qsString = usp.toString();
    history.replaceState({ tab: getTabFromUrl() }, '', `${location.pathname}${qsString ? ('?' + qsString) : ''}`);
    if (key === 'module') syncTabLinks(val);
  };

  syncTabLinks(selectedModuleUuid);

  // ===== Role-aware back to courses
  const USER_ROLE = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
  const derivedRole = (USER_ROLE && ['student','admin','instructor'].includes(USER_ROLE)) ? USER_ROLE : (USER_ROLE || '');

  (function setupBackToCourses() {
    const roleCourses = {
      student: "/student/courses",
      admin: "/admin/courses",
      instructor: "/instructor/courses"
    };
    const go = (url) => { try { window.location.href = url; } catch(e){} };

    const handler = (e) => {
      if (e && typeof e.preventDefault === 'function') e.preventDefault();
      go(roleCourses[derivedRole] || '/courses');
      return false;
    };

    window.backToCourses = handler; // keep compatibility
    el.asideBackBtn?.addEventListener('click', handler);
  })();

  // ===== Small bus (tabs can listen)
  const bus = { emit(evt, detail){ document.dispatchEvent(new CustomEvent(evt, { detail })); } };
  window.__VCBUS__ = bus;

  // ===== Desktop sidebar toggle (hamburger)
  const SIDEBAR_KEY = 'vc_sidebar_open';
  let desktopSidebarOpen = false;

  const applyDesktopSidebar = (open) => {
    desktopSidebarOpen = !!open;
    if (!el.grid) return;

    if (desktopSidebarOpen) {
      el.grid.classList.remove('sidebar-collapsed');
      document.body.classList.remove('vc-wide');
      if (el.sideToggleIcon) el.sideToggleIcon.className = 'fa-solid fa-xmark';
      localStorage.setItem(SIDEBAR_KEY, '1');
    } else {
      el.grid.classList.add('sidebar-collapsed');
      document.body.classList.add('vc-wide');
      if (el.sideToggleIcon) el.sideToggleIcon.className = 'fa-solid fa-bars';
      localStorage.setItem(SIDEBAR_KEY, '0');
    }
  };

  const initDesktopSidebar = () => {
    if (isMobile()) return; // desktop only
    const saved = localStorage.getItem(SIDEBAR_KEY);
    // Per your requirement: sidebar shows ONLY when hamburger is clicked (default hidden)
    const open = saved === null ? false : (saved === '1');
    applyDesktopSidebar(open);
  };

  el.sideToggleBtn?.addEventListener('click', () => {
    if (isMobile()) return;
    applyDesktopSidebar(!desktopSidebarOpen);
    // focus search when opening
    if (desktopSidebarOpen) setTimeout(() => el.mSearch?.focus?.(), 50);
  });

  // ===== Mobile view flow:
  // - Start: show ONLY left sidebar (modules)
  // - On module click: show ONLY right side
  // - Back: show left again
  const ensureMobileInitialView = () => {
    if (!isMobile()) return;
    if (!selectedModuleUuid) {
      el.aside?.classList.remove('mobile-hidden');
      el.main?.classList.add('mobile-hidden');
    } else {
      el.aside?.classList.add('mobile-hidden');
      el.main?.classList.remove('mobile-hidden');
    }
  };

  el.backBtn?.addEventListener('click', () => {
    el.aside?.classList.remove('mobile-hidden');
    el.main?.classList.add('mobile-hidden');
  });

  // ===== Render helpers
  const setCover = (m) => {
    if (!m || !m.url) return;
    el.cover.innerHTML = `<img src="${m.url}" alt="cover">`;
  };

  const renderThumbs = (gallery=[]) => {
    el.thumbs.innerHTML = '';
    (gallery || []).forEach(m => {
      if (m.type !== 'image') return;
      const d = document.createElement('div');
      d.className = 'vc-thumb';
      d.title = 'Cover';
      d.innerHTML = `<img src="${m.url}" alt="">`;
      d.addEventListener('click', () => setCover(m));
      el.thumbs.appendChild(d);
    });
  };

  const setModuleHeader = (m) => {
    if (!m){
      el.mTitle.textContent = 'Select a module';
      el.mShort.textContent = 'Pick a module from the left to see its content.';
      return;
    }
    el.mTitle.textContent = m.title || 'Module';
    el.mShort.textContent = m.short_description || '';
  };

  const tryRefreshCurrentTab = () => {
    // best-effort auto refresh for tabs that depend on module
    // (these buttons exist only inside the active tab DOM)
    const btns = [
      '#btn-refresh',   // study material / assignments / notices
      '#qz-refresh',    // quizzes
    ];
    for (const sel of btns) {
      const b = document.querySelector(sel);
      if (b && typeof b.click === 'function') { b.click(); break; }
    }
  };

  const renderModules = (modules=[]) => {
    el.mList.innerHTML = '';
    el.mEmpty.style.display = modules.length ? 'none' : '';
    const frag = document.createDocumentFragment();

    modules.forEach(m => {
      const div = document.createElement('div');
      div.className = 'vc-module';
      div.dataset.uuid = m.uuid;
      div.innerHTML = `
        <div class="module-content">
          <div class="t">${m.title ?? 'Untitled module'}</div>
          <div class="d">${m.short_description ?? ''}</div>
        </div>
        <div class="module-arrow">›</div>
      `;
      div.addEventListener('click', () => {
        selectedModuleUuid = m.uuid;
        [...el.mList.children].forEach(c => c.classList.toggle('active', c.dataset.uuid === selectedModuleUuid));
        setModuleHeader(m);

        updateQueryParam('module', selectedModuleUuid);
        bus.emit('vc:module-changed', { moduleUuid: selectedModuleUuid, module: m });

        // Mobile: hide aside, show main
        if (isMobile()) {
          el.aside?.classList.add('mobile-hidden');
          el.main?.classList.remove('mobile-hidden');
        } else {
          // Desktop: after selecting a module, collapse sidebar for full screen
          applyDesktopSidebar(false);
        }

        // Refresh active tab data (module-sensitive tabs read module from URL)
        tryRefreshCurrentTab();
      });
      frag.appendChild(div);
    });

    el.mList.appendChild(frag);

    // Preselect behavior:
    // - Mobile: DO NOT auto-select (unless module is already in URL)
    // - Desktop: auto-select first module if none is selected (nice UX)
    let chosen = modules.find(x => x.uuid === selectedModuleUuid) || null;

    if (!chosen && modules.length && !isMobile()) {
      chosen = modules[0];
      selectedModuleUuid = chosen.uuid;
      updateQueryParam('module', selectedModuleUuid);
    }

    if (chosen) {
      [...el.mList.children].forEach(c => c.classList.toggle('active', c.dataset.uuid === selectedModuleUuid));
      setModuleHeader(chosen);
      bus.emit('vc:module-changed', { moduleUuid: selectedModuleUuid, module: chosen });
    } else {
      setModuleHeader(null);
    }

    // Ensure correct initial view on mobile
    ensureMobileInitialView();
  };

  const wireSearch = (modules=[]) => {
    if (!el.mSearch) return;
    el.mSearch.addEventListener('input', (e) => {
      const q = (e.target.value || '').toLowerCase();
      [...el.mList.children].forEach(li => {
        const m = modules.find(x => x.uuid === li.dataset.uuid);
        const hay = `${m?.title ?? ''} ${m?.short_description ?? ''}`.toLowerCase();
        li.style.display = hay.includes(q) ? '' : 'none';
      });
    });
  };

  // ===== Tab switching without full page reload (cache panes)
  const tabCache = new Map();     // tabKey -> paneEl
  const tabMeta  = new Map();     // tabKey -> { inited:boolean, inlineScripts:string[] }

  const setTabsActiveUI = (tabKey) => {
    document.querySelectorAll('#vcTabs .nav-link').forEach(a => {
      const k = a.dataset.tab || '';
      a.classList.toggle('active', k === tabKey);
    });
  };

  const setTabLoading = (on) => {
    if (!el.tabContent) return;
    el.tabContent.classList.toggle('is-loading', !!on);
  };

  const buildTabUrl = (tabKey) => {
    const u = new URL(window.location.href);
    u.searchParams.set('tab', tabKey);
    if (selectedModuleUuid) u.searchParams.set('module', selectedModuleUuid);
    else u.searchParams.delete('module');
    return u.toString();
  };

  const execInline = (code) => {
    try {
      const s = document.createElement('script');
      s.text = String(code || '');
      document.body.appendChild(s);
      s.remove();
    } catch(e) {
      console.error('inline script failed', e);
    }
  };

  const loadScriptOnce = (() => {
    const seen = new Set();
    return (src) => new Promise((resolve, reject) => {
      if (!src) return resolve();
      if (seen.has(src)) return resolve();
      seen.add(src);
      const s = document.createElement('script');
      s.src = src;
      s.async = true;
      s.onload = () => resolve();
      s.onerror = () => reject(new Error('Failed to load script: ' + src));
      document.head.appendChild(s);
    });
  })();

  const captureInitialPane = () => {
    const initialTab = getTabFromUrl();
    const pane = document.createElement('div');
    pane.className = 'vc-tab-pane';
    pane.dataset.tab = initialTab;

    // move current rendered nodes into the pane
    const nodes = [...el.tabContent.childNodes].filter(n => !(n.nodeType === 1 && n.classList.contains('vc-tab-spinner')));
    nodes.forEach(n => pane.appendChild(n));

    // clear and re-add spinner + pane
    const spinner = el.tabContent.querySelector('.vc-tab-spinner');
    el.tabContent.innerHTML = '';
    if (spinner) el.tabContent.appendChild(spinner);
    el.tabContent.appendChild(pane);

    tabCache.set(initialTab, pane);
    tabMeta.set(initialTab, { inited: true, inlineScripts: [] }); // already executed by browser on first load
    setTabsActiveUI(initialTab);
    syncTabLinks(selectedModuleUuid);
  };

  const ensurePaneLoaded = async (tabKey) => {
    if (tabCache.has(tabKey)) return;

    setTabLoading(true);
    const url = buildTabUrl(tabKey);

    let html = '';
    try {
      const res = await fetch(url, { headers: { 'Accept': 'text/html', ...auth } });
      html = await res.text();
      if (!res.ok) throw new Error('Failed to load tab');
    } catch (e) {
      setTabLoading(false);
      // fallback to hard navigation if fetch fails
      window.location.href = url;
      return;
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const src = doc.querySelector('#tabContent');
    if (!src) {
      setTabLoading(false);
      window.location.href = url;
      return;
    }

    const pane = document.createElement('div');
    pane.className = 'vc-tab-pane';
    pane.dataset.tab = tabKey;
    pane.innerHTML = src.innerHTML;

    // Strip duplicate external resources inside pane
    pane.querySelectorAll('link[rel="stylesheet"]').forEach(l => l.remove());
    pane.querySelectorAll('script[src]').forEach(s => {
      const srcUrl = (s.getAttribute('src') || '').trim();
      // SweetAlert2 is already loaded globally; keep future-proof loader for other libs
      if (srcUrl && !srcUrl.includes('sweetalert2')) {
        loadScriptOnce(srcUrl).catch(()=>{});
      }
      s.remove();
    });

    // Collect inline scripts and remove from DOM (we will execute once when pane is shown)
    const inline = [];
    pane.querySelectorAll('script:not([src])').forEach(s => {
      inline.push(s.textContent || '');
      s.remove();
    });

    tabCache.set(tabKey, pane);
    tabMeta.set(tabKey, { inited: false, inlineScripts: inline });

    setTabLoading(false);
  };

  const showPane = async (tabKey, opts = { push: true }) => {
    const currentTab = getTabFromUrl();
    if (!tabKey) tabKey = currentTab;

    // no-op
    const currentPane = tabCache.get(currentTab);
    if (currentPane && currentPane.dataset.tab === tabKey) return;

    setTabsActiveUI(tabKey);
    setTabLoading(true);

    await ensurePaneLoaded(tabKey);

    // detach current pane (keep cached)
    const existing = el.tabContent.querySelector('.vc-tab-pane');
    if (existing) existing.remove();

    const pane = tabCache.get(tabKey);
    if (pane) el.tabContent.appendChild(pane);

    // update URL (no reload)
    if (opts.push) {
      const u = new URL(window.location.href);
      u.searchParams.set('tab', tabKey);
      if (selectedModuleUuid) u.searchParams.set('module', selectedModuleUuid);
      else u.searchParams.delete('module');
      history.pushState({ tab: tabKey }, '', u.toString());
    }

    // init inline scripts once
    const meta = tabMeta.get(tabKey);
    if (meta && !meta.inited) {
      meta.inited = true;
      // run after mount
      setTimeout(() => {
        (meta.inlineScripts || []).forEach(execInline);
        bus.emit('vc:tab-changed', { tab: tabKey, moduleUuid: selectedModuleUuid });
      }, 0);
    } else {
      bus.emit('vc:tab-changed', { tab: tabKey, moduleUuid: selectedModuleUuid });
    }

    setTabLoading(false);
  };

  // Intercept tab clicks (no full page reload)
  el.tabs?.addEventListener('click', async (e) => {
    const a = e.target.closest('a.nav-link');
    if (!a) return;
    const tabKey = a.dataset.tab;
    if (!tabKey) return;

    e.preventDefault();
    await showPane(tabKey, { push: true });
  });

  // Browser back/forward: swap panes
  window.addEventListener('popstate', () => {
    const t = getTabFromUrl();
    showPane(t, { push: false });
  });

  // ===== Fetch course view payload
  const api = courseKey ? `/api/courses/by-batch/${encodeURIComponent(courseKey)}/view` : null;
  if (!api) {
    el.title.textContent = 'Course';
    el.short.textContent = '';
    return;
  }

  // init UI states
  initDesktopSidebar();
  ensureMobileInitialView();
  captureInitialPane();

  // handle resize: keep flows correct
  window.addEventListener('resize', () => {
    if (isMobile()) {
      // mobile flow controlled by module selection
      ensureMobileInitialView();
      // ensure desktop classes cleared
      el.grid?.classList.remove('sidebar-collapsed');
      document.body.classList.remove('vc-wide');
    } else {
      initDesktopSidebar();
      // show both columns if sidebar open
      el.aside?.classList.remove('mobile-hidden');
      el.main?.classList.remove('mobile-hidden');
    }
  });

  fetch(api, { headers: { 'Accept':'application/json', ...auth } })
    .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
    .then(({ data }) => {
      const { course, pricing, media, modules, batch } = data;

      el.title.textContent  = course.title || 'Course';
      el.short.textContent  = course.short_description || '';

      if (pricing){
        el.price.style.display = 'inline-flex';
        el.price.innerHTML = pricing.is_free
          ? `<i class="fa fa-badge-check"></i> Free`
          : (pricing.has_discount
              ? `<i class="fa fa-tags"></i> ${pricing.currency} ${pricing.final}
                 <span class="text-muted" style="text-decoration:line-through">&nbsp;${pricing.currency} ${pricing.original}</span>`
              : `<i class="fa fa-tag"></i> ${pricing.currency} ${pricing.original}`);
      }

      // chips
      const chips = [];
      if (course.difficulty)     chips.push(`<span class="vc-chip"><i class="fa fa-signal"></i>${course.difficulty}</span>`);
      if (course.language)       chips.push(`<span class="vc-chip"><i class="fa fa-language"></i>${course.language}</span>`);
      if (course.duration_hours) chips.push(`<span class="vc-chip"><i class="fa fa-clock"></i>${course.duration_hours} hrs</span>`);
      el.chips.innerHTML = chips.join(' ');

      // batch details
      if (batch) {
        el.batchDetails.style.display = 'block';
        el.batchTitle.textContent = batch.badge_title || 'Batch';
        el.batchDescription.textContent = batch.badge_description || batch.description || '';
        el.batchTagline.textContent = batch.tagline || '';

        const batchInfoItems = [];
        if (batch.mode) {
          const modeIcon = batch.mode === 'online' ? 'fa-globe' : batch.mode === 'offline' ? 'fa-location-dot' : 'fa-layer-group';
          batchInfoItems.push(`<span class="vc-batch-info-item"><i class="fa ${modeIcon}"></i>${batch.mode.charAt(0).toUpperCase() + batch.mode.slice(1)}</span>`);
        }
        if (batch.starts_at) {
          const startDate = new Date(batch.starts_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
          batchInfoItems.push(`<span class="vc-batch-info-item"><i class="fa fa-calendar-day"></i>Starts: ${startDate}</span>`);
        }
        if (batch.ends_at) {
          const endDate = new Date(batch.ends_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
          batchInfoItems.push(`<span class="vc-batch-info-item"><i class="fa fa-calendar-check"></i>Ends: ${endDate}</span>`);
        }
        el.batchInfo.innerHTML = batchInfoItems.join('');
      }

      if (media?.cover) setCover(media.cover);
      renderThumbs(media?.gallery || []);

      const sorted = (modules || []).slice().sort((a,b)=> (a.order_no ?? 0) - (b.order_no ?? 0));
      renderModules(sorted);
      wireSearch(sorted);

      // Ensure mobile initial view after data
      ensureMobileInitialView();
    })
    .catch(err => {
      console.error('viewCourse.fetch', err);
      if (el.title) el.title.textContent = 'Failed to load course';
      if (el.short) el.short.textContent = 'Please check API or authentication.';
    });
});
</script>
</body>
</html>
