{{-- resources/views/modules/viewCourse/viewCourseLayout.blade.php --}}
@php
  // Tab map (server-side include for first render; JS will swap panes without full reload)
  $tabKey = request('tab', 'recorded');
  $tabs = [
    'recorded'     => 'modules.course.viewCourse.viewCourseTabs.recordedVideos',
    'materials'    => 'modules.course.viewCourse.viewCourseTabs.studyMaterial',
    'assignments'  => 'modules.course.viewCourse.viewCourseTabs.assignments',
    'quizzes'      => 'modules.course.viewCourse.viewCourseTabs.quizzes',
    'codingtests'  => 'modules.course.viewCourse.viewCourseTabs.userCodingTest',
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
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes"/>
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

  {{-- SweetAlert2 used by multiple tabs --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    /* ===== Base Styles =================================================== */
    html, body { height: 100%; }
    body{
      background: var(--bg-body);
      color: var(--text-color);
      min-height: 100dvh;
      overflow-x: hidden;
    }

    .vc-wrap{
      max-width: 100%;
      margin: 18px auto 40px;
      padding: 0 14px;
    }

    @media (max-width: 768px) {
      .vc-wrap { padding: 0 12px; margin: 12px auto 20px; }
    }
    @media (max-width: 576px) {
      .vc-wrap { padding: 0 10px; margin: 8px auto 16px; }
    }

    .vc-grid{
      display:grid;
      grid-template-columns: minmax(0,1fr);
      gap:16px;
      min-height: calc(100dvh - 90px);
    }

    /* ===== FULL mode: two-column layout on desktop (DEFAULT) ============= */
    body.vc-mode-full .vc-grid{
      grid-template-columns: 320px minmax(0,1fr);
    }
    body.vc-mode-full .vc-main{ display:block; }

    /* Mobile adjustments for FULL mode */
    @media (max-width: 992px){
      .vc-grid{
        grid-template-columns: 1fr;
        gap: 12px;
      }

      body.vc-mode-full .vc-grid{
        grid-template-columns: 1fr;
      }

      /* Default in FULL mode on mobile: show main (tabs), hide sidebar */
      body.vc-mode-full .vc-aside {
        display: none;
      }
      body.vc-mode-full .vc-main{
        display: block;
        margin-top: 0;
      }

      /* When hamburger toggles sidebar open: show sidebar, hide main */
      body.vc-mode-full.mobile-sidebar-open .vc-aside {
        display: block;
      }
      body.vc-mode-full.mobile-sidebar-open .vc-main {
        display: none !important;
      }
    }

    /* ===== Back button ===== */
    .aside-back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      border-radius: 10px;
      cursor: pointer;
      color: var(--ink);
      font-weight: 500;
      font-size: var(--fs-14);
      text-decoration: none;
      transition: var(--transition);
      margin-bottom: 12px;
      white-space: nowrap;
      touch-action: manipulation;
    }
    .aside-back-btn:hover, .aside-back-btn:active {
      background: color-mix(in oklab, var(--accent-color) 8%, transparent);
      border-color: var(--accent-color);
      color: var(--ink);
    }
    @media (max-width: 576px) {
      .aside-back-btn { padding: 8px 12px; font-size: var(--fs-13); }
    }

    /* ===== Mobile Hamburger Menu ======================================== */
    .mobile-hamburger {
      display: none;
      width: 44px;
      height: 44px;
      border-radius: 12px;
      border: 1px solid var(--line-strong);
      background: var(--surface);
      color: var(--ink);
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      flex-shrink: 0;
    }
    .mobile-hamburger:hover {
      border-color: var(--accent-color);
      background: color-mix(in oklab, var(--accent-color) 8%, transparent);
    }
    @media (max-width: 992px) {
      .mobile-hamburger { display: inline-flex; }
    }

    /* ===== Left column ================================================== */
    .vc-aside .panel{
      padding: 16px;
      height: 100%;
      overflow-y: auto;
    }
    @media (max-width: 768px) { .vc-aside .panel { padding: 14px; } }
    @media (max-width: 576px) { .vc-aside .panel { padding: 12px; } }

    /* ===== Batch Details & Modules Row ================================== */
    .details-modules-row { margin-top: 6px; }
    @media (max-width: 992px) { .details-modules-row { margin-top: 8px; } }

    /* Batch Details */
    .vc-batch-details{
      margin: 0 0 16px 0;
      padding: 16px;
      border: 1px solid var(--line-strong);
      border-radius: 12px;
      background: var(--surface);
      height: 100%;
    }
    @media (max-width: 768px) { .vc-batch-details { padding: 14px; } }

    .vc-batch-title{
      font-weight: 600;
      color: var(--ink);
      margin-bottom: 8px;
      font-size: 1.1rem;
    }
    .vc-batch-description{
      font-size: var(--fs-14);
      color: var(--text-color);
      margin-bottom: 10px;
      line-height: 1.5;
    }
    .vc-batch-tagline{
      font-size: var(--fs-13);
      color: var(--muted-color);
      margin-bottom: 10px;
      font-style: italic;
    }
    .vc-batch-info{
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      font-size: var(--fs-13);
    }
    .vc-batch-info-item{
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: var(--muted-color);
      white-space: nowrap;
    }
    .vc-batch-info-item i{
      width: 16px;
      text-align: center;
      flex-shrink: 0;
    }
    @media (max-width: 1200px) {
      .vc-batch-info { flex-direction: column; gap: 8px; }
    }

    /* Search */
    .vc-search{
      position:relative;
      margin-bottom: 12px;
    }
    .vc-search input{
      padding-left:42px;
      height:44px;
      border-radius:12px;
      font-size: var(--fs-14);
      border: 1px solid var(--line-strong);
      background: var(--surface);
      color: var(--text-color);
    }
    .vc-search input:focus {
      border-color: var(--accent-color);
      box-shadow: 0 0 0 3px color-mix(in oklab, var(--accent-color) 20%, transparent);
    }
    .vc-search i{
      position:absolute;
      left:14px;
      top:50%;
      transform:translateY(-50%);
      color:var(--muted-color);
      pointer-events: none;
    }
    @media (max-width: 576px) {
      .vc-search input { height: 42px; padding-left: 40px; }
    }

    /* Modules List */
    .vc-modules{
      margin-top: 0;
      display:flex;
      flex-direction:column;
      gap:10px;
      max-height: 50vh;
      overflow-y: auto;
      padding-right: 4px;
    }
    .vc-modules::-webkit-scrollbar { width: 6px; }
    .vc-modules::-webkit-scrollbar-track { background: transparent; }
    .vc-modules::-webkit-scrollbar-thumb { background-color: var(--line-strong); border-radius: 3px; }
    .vc-modules::-webkit-scrollbar-thumb:hover { background-color: var(--muted-color); }
    @media (max-width: 768px) { .vc-modules { max-height: 60vh; } }

    .vc-module{
      border:1px solid var(--line-strong);
      border-radius:12px;
      background:var(--surface);
      padding:14px;
      cursor:pointer;
      transition: var(--transition);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      touch-action: manipulation;
    }
    .vc-module:hover, .vc-module:active{
      border-color: var(--accent-color);
      transform: translateX(2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .vc-module .module-content{ flex: 1; min-width: 0; }
    .vc-module .t{
      font-weight:600;
      color:var(--ink);
      margin-bottom: 4px;
      font-size: var(--fs-15);
      line-height: 1.3;
    }
    .vc-module .d{
      font-size: var(--fs-13);
      color: var(--muted-color);
      line-height: 1.4;
    }

    /* In FULL mode: hide module descriptions on desktop, show on mobile */
    body.vc-mode-full .vc-module .d{ display: none; }
    @media (max-width: 992px) {
      body.vc-mode-full .vc-module .d{ display: block; }
    }

    .vc-module .module-arrow{
      color: var(--muted-color);
      font-size: 1.2rem;
      flex-shrink: 0;
      transition: var(--transition);
    }
    .vc-module.active{
      border:2px solid var(--accent-color);
      background: color-mix(in oklab, var(--accent-color) 5%, transparent);
    }
    .vc-module.active .module-arrow{
      color: var(--accent-color);
      transform: translateX(4px);
    }

    .vc-empty{
      border:1px dashed var(--line-strong);
      border-radius: 12px;
      padding: 32px 16px;
      text-align:center;
      color: var(--muted-color);
      font-size: var(--fs-14);
    }
    .vc-empty i {
      font-size: 2rem;
      margin-bottom: 12px;
      opacity: 0.5;
    }

    /* ===== Right column (module header + tabs) ========================== */
    .vc-main .panel{
      padding: 16px;
      min-height: calc(100% - 0px);
      height: 100%;
      /* overflow-y: auto; */
    }
    @media (max-width: 768px) { .vc-main .panel { padding: 14px; } }
    @media (max-width: 576px) { .vc-main .panel { padding: 12px; } }

    /* Top header: row 1 = hamburger + title, row 2 = description */
    .vc-top{
      display:flex;
      flex-direction: column;
      gap:6px;
      margin-bottom:16px;
    }
    .vc-top-row1{
      display:flex;
      align-items:center;
      gap:12px;
    }
    .vc-top-row2{ padding-left: 0; }
    @media (max-width: 576px) {
      .vc-top { margin-bottom: 14px; }
      .vc-top-row1{ gap:10px; }
    }

    .vc-top .title{
      font-family:var(--font-head);
      font-weight:700;
      color:var(--ink);
      margin:0;
      font-size:1.3rem;
      line-height: 1.3;
      flex: 1;
    }
    @media (max-width: 768px) { .vc-top .title { font-size: 1.2rem; } }
    @media (max-width: 576px) { .vc-top .title { font-size: 1.15rem; } }

    .vc-top .sub{
      color: var(--muted-color);
      font-size: var(--fs-14);
      margin-top: 2px;
      line-height: 1.4;
    }

    /* Course Details in Hamburger Menu */
    .mobile-course-details {
      display: none;
      padding: 16px;
      border: 1px solid var(--line-strong);
      border-radius: 12px;
      background: var(--surface);
      margin-bottom: 16px;
      animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .mobile-course-details.show { display: block; }

    .mobile-course-details .section-title {
      font-weight: 600;
      color: var(--ink);
      margin-bottom: 12px;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .mobile-course-details .course-description {
      font-size: var(--fs-14);
      color: var(--text-color);
      line-height: 1.6;
      margin-bottom: 12px;
    }
    .mobile-course-details .course-meta {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 10px;
      margin-top: 12px;
    }
    .mobile-course-details .meta-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: var(--fs-13);
      color: var(--text-color);
    }
    .mobile-course-details .meta-item i {
      width: 16px;
      color: var(--accent-color);
      text-align: center;
    }
    @media (max-width: 576px) {
      .mobile-course-details .course-meta { grid-template-columns: 1fr; }
    }

    /* Tabs */
    .tabbar{
      margin-top:8px;
      border-bottom:1px solid var(--line-strong);
      padding-bottom:4px;
      display:flex;
      flex-wrap:wrap;
      gap:6px;
      overflow-x: auto;
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    .tabbar::-webkit-scrollbar { display: none; }
    @media (max-width: 768px) { .tabbar { gap: 4px; } }

    .tabbar .nav-link{
      border:0;
      border-radius:10px;
      padding:10px 14px;
      color: var(--muted-color);
      background: transparent;
      font-size: var(--fs-14);
      white-space: nowrap;
      transition: var(--transition);
      touch-action: manipulation;
    }
    .tabbar .nav-link i{
      width:16px;
      text-align:center;
      margin-right:6px;
      font-size: var(--fs-15);
    }
    .tabbar .nav-link:hover{ background: rgba(2,6,23,.04); }
    html.theme-dark .tabbar .nav-link:hover{ background:#0c172d; }

    .tabbar .nav-link.active{
      color: var(--ink);
      background: color-mix(in oklab, var(--accent-color) 12%, transparent);
      box-shadow: var(--shadow-1);
    }

    @media (max-width: 768px) {
      .tabbar .nav-link { padding: 8px 12px; font-size: var(--fs-13); }
    }
    @media (max-width: 576px) {
      .tabbar .nav-link { padding: 8px 10px; font-size: var(--fs-12); }
      .tabbar .nav-link i { margin-right: 4px; font-size: var(--fs-14); }
    }

    /* ===== Tab switching ================================================ */
    #tabContent{
      position: relative;
      margin-top: 16px;
    }
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
      padding:8px 12px;
      background: var(--surface);
      border:1px solid var(--line-strong);
      border-radius: 999px;
      box-shadow: var(--shadow-2);
      z-index: 50;
      color: var(--muted-color);
      font-size: var(--fs-13);
    }
    /* ===== View Course Page Overlay ===== */
    #vcPageOverlay{
      position: absolute;
      inset: 0;
      z-index: 50;
      display: none; /* IMPORTANT */
      align-items: center;
      justify-content: center;
      background: color-mix(in oklab, var(--bg-body) 85%, transparent);
    }

    html.theme-dark #vcPageOverlay{
      background: rgba(2,6,23,.75);
    }

    #tabContent.is-loading .vc-tab-spinner{ display:flex; }

    .vc-dot{
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: var(--muted-color);
      animation: vcPulse 900ms infinite ease-in-out;
    }
    .vc-dot:nth-child(2){ animation-delay: 150ms; }
    .vc-dot:nth-child(3){ animation-delay: 300ms; }

    @keyframes vcPulse{
      0%, 100%{ transform: translateY(0); opacity:.35; }
      50%{ transform: translateY(-3px); opacity:.95; }
    }
    /* ===== Locked / Unlocked badge (sidebar modules) ======================= */
.vc-module .module-meta{
  display:flex;
  align-items:center;
  gap:10px;
  flex-shrink: 0;
}

.vc-badge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:6px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
  border:1px solid var(--line-strong);
  background: color-mix(in oklab, var(--surface) 85%, transparent);
  color: var(--muted-color);
  white-space: nowrap;
}

.vc-badge.unlocked{
  border-color: color-mix(in oklab, var(--accent-color) 30%, var(--line-strong));
  background: color-mix(in oklab, var(--accent-color) 12%, transparent);
  color: var(--ink);
}

.vc-badge.locked{
  border-color: color-mix(in oklab, #ef4444 35%, var(--line-strong));
  background: color-mix(in oklab, #ef4444 12%, transparent);
  color: var(--ink);
}

.vc-module.is-locked{
  opacity: .75;
  cursor: not-allowed;
}

.vc-module.is-locked:hover, .vc-module.is-locked:active{
  transform: none;
  box-shadow: none;
  border-color: var(--line-strong);
}

  </style>
</head>

{{-- ✅ DEFAULT is FULL mode now --}}
<body class="vc-mode-full" data-initial-tab="{{ $tabKey }}">

  {{-- Global overlay loader (shared partial) --}}
{{-- Page-scoped overlay (hidden by default) --}}
<div id="vcPageOverlay" style="display:none">
  @include('partials.overlay')
</div>

  <main class="vc-wrap">
    <div class="vc-grid" id="vcGrid">

      {{-- ================= LEFT: Sidebar ================= --}}
      <aside class="vc-aside" id="vcAside">
        <div class="panel shadow-1">


          <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="javascript:void(0)" class="aside-back-btn" id="asideBackBtn">
              <i class="fa fa-arrow-left"></i>
              <span>Back to Courses</span>
            </a>
          </div>

          {{-- Batch Details + Modules --}}
          <div class="details-modules-row">
            {{-- Batch Details --}}
            <h3 class="h5 mb-3">Batch Details</h3>
            <div class="vc-batch-details" id="batchDetails" style="display:none">
              <div class="vc-batch-title" id="batchTitle"></div>
              <div class="vc-batch-description" id="batchDescription"></div>
              <div class="vc-batch-tagline" id="batchTagline"></div>
              <div class="vc-batch-info" id="batchInfo"></div>
            </div>

            {{-- Modules --}}
            <h3 class="h5 mb-3">Course Modules</h3>
            <div class="vc-search d-flex align-items-center">
              <i class="fa fa-search me-2"></i>
              <input id="moduleSearch" type="text" class="form-control" placeholder="Search modules...">
            </div>

            <div class="vc-modules" id="modulesList">
              <div class="vc-empty" id="modulesEmpty" style="display:none">
                <i class="fa-regular fa-folder-open"></i>
                <div>No modules found</div>
              </div>
            </div>
          </div>

        </div>
      </aside>

      {{-- ================= RIGHT: Module Header + Tabs ================= --}}
      <section class="vc-main" id="vcMain">
        <div class="panel shadow-1">
          <div class="vc-top">
            <div class="vc-top-row1">
              {{-- Hamburger Menu for Mobile --}}
              <button class="mobile-hamburger" id="mobileHamburgerBtn" type="button" aria-label="Toggle sidebar">
                <i class="fa-solid fa-bars"></i>
              </button>

              <h1 class="title" id="moduleTitle">Select a module</h1>
            </div>
            <div class="vc-top-row2">
              <div class="sub" id="moduleShort">Pick a module from the left to see its content.</div>
            </div>
          </div>

          {{-- Mobile Course Details --}}
          {{-- <div class="mobile-course-details" id="mobileCourseDetails">
            <div class="section-title">
              <i class="fa-solid fa-book-open"></i>
              <span>Course Details</span>
            </div>
            <div class="course-description" id="mobileCourseDescription">No description available.</div>
            <div class="course-meta" id="mobileCourseMeta"></div>
          </div> --}}

          @php
            $self = url()->current();
            $mParam = $moduleUuid ? ('&module='.urlencode($moduleUuid)) : '';
          @endphp

          <ul class="nav tabbar" id="vcTabs">
            <li class="nav-item d-none">
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
document.addEventListener('DOMContentLoaded', async () => {
  const qs  = (sel) => document.querySelector(sel);

  // Use global overlay helpers from partials.overlay
  const vcOverlay = document.getElementById('vcPageOverlay');

    const showPageOverlay = () => {
      if (vcOverlay) vcOverlay.style.display = 'flex';
    };

    const hidePageOverlay = () => {
      if (vcOverlay) vcOverlay.style.display = 'none';
    };


  // ✅ DEFAULT is FULL mode now (no intro screen)
  document.body.classList.add('vc-mode-full');
  document.body.classList.remove('vc-mode-overview');

  const deriveCourseKey = () => {
    const parts = location.pathname.split('/').filter(Boolean);
    const last = parts.at(-1)?.toLowerCase();
    if (last === 'view' && parts.length >= 2) return parts.at(-2);
    if (last === 'courses') return null;
    return parts.at(-1);
  };
  const courseKey = deriveCourseKey();

  const el = {
    asideBackBtn: document.getElementById('asideBackBtn'),
    mobileHamburgerBtn: document.getElementById('mobileHamburgerBtn'),

    mSearch:document.getElementById('moduleSearch'),
    mList:  document.getElementById('modulesList'),
    mEmpty: document.getElementById('modulesEmpty'),

    mTitle: document.getElementById('moduleTitle'),
    mShort: document.getElementById('moduleShort'),

    // mobileCourseDetails: document.getElementById('mobileCourseDetails'),
    mobileCourseDescription: document.getElementById('mobileCourseDescription'),
    mobileCourseMeta: document.getElementById('mobileCourseMeta'),

    batchDetails: document.getElementById('batchDetails'),
    batchTitle: document.getElementById('batchTitle'),
    batchDescription: document.getElementById('batchDescription'),
    batchTagline: document.getElementById('batchTagline'),
    batchInfo: document.getElementById('batchInfo'),

    tabs: document.getElementById('vcTabs'),
    tabContent: document.getElementById('tabContent'),
  };

  const isMobile = () => window.matchMedia('(max-width: 992px)').matches;

  // ===== Token & role (GLOBAL IN-MEMORY CACHE) =====
  let tok = '';
  try {
    tok = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  } catch (e) {
    tok = '';
  }
  const auth = tok ? { 'Authorization': 'Bearer ' + tok } : {};
// Add this to your existing script section in viewCourseLayout.blade.php

// ===== Token Expiration Checker =========================================
const setupTokenExpirationCheck = () => {
  let checkInterval = null;
  let warningShown = false;
  
  const checkTokenValidity = async () => {
    try {
      const token = sessionStorage.getItem('token') || localStorage.getItem('token');
      
      if (!token) {
        if (checkInterval) clearInterval(checkInterval);
        return;
      }
      
      const response = await fetch('/api/auth/token/check', {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      
      const data = await response.json();
      
      // Token is invalid or expired
      if (!response.ok || !data.success) {
        if (checkInterval) clearInterval(checkInterval);
        
        // Prevent multiple alerts
        if (warningShown) return;
        warningShown = true;
        
        const reason = data?.meta?.reason || 'unknown';
        let alertText = 'Your session has expired. Please log in again.';
        
        if (reason === 'missing_token') {
          alertText = 'Authentication token is missing. Please log in again.';
        } else if (reason === 'invalid_token') {
          alertText = 'Your session is invalid. Please log in again.';
        }
        
        Swal.fire({
          icon: 'warning',
          title: 'Session Expired',
          text: alertText,
          confirmButtonText: 'Login Now',
          allowOutsideClick: false,
          allowEscapeKey: false,
          customClass: {
            confirmButton: 'btn btn-primary'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            // Clear stored tokens
            try {
              sessionStorage.removeItem('token');
              localStorage.removeItem('token');
            } catch (e) {
              console.error('Failed to clear tokens:', e);
            }
            
            // Redirect to login page
            window.location.href = '/login';
          }
        });
        
        return;
      }
      
      // Token is valid - optionally show warning if expiring soon
      if (data.success && data.data?.seconds_left) {
        const secondsLeft = parseInt(data.data.seconds_left);
        const minutesLeft = Math.floor(secondsLeft / 60);
        
        // Warn if less than 5 minutes remaining (adjust as needed)
        if (minutesLeft <= 5 && minutesLeft > 0 && !warningShown) {
          warningShown = true;
          
          Swal.fire({
            icon: 'info',
            title: 'Session Expiring Soon',
            text: `Your session will expire in ${minutesLeft} minute${minutesLeft !== 1 ? 's' : ''}. Please save your work.`,
            confirmButtonText: 'OK',
            timer: 10000,
            timerProgressBar: true,
            customClass: {
              confirmButton: 'btn btn-primary'
            }
          }).then(() => {
            // Allow showing the warning again later
            setTimeout(() => { warningShown = false; }, 60000);
          });
        }
      }
      
    } catch (error) {
      console.error('Token check failed:', error);
      // Don't show error to user for network issues, just log it
    }
  };
  
  // Check token validity every 2 minutes (120000 ms)
  // Adjust based on your token expiration time
  checkInterval = setInterval(checkTokenValidity, 120000);
  
  // Also check immediately on page load
  checkTokenValidity();
  
  // Store interval ID globally so it can be cleared if needed
  window.__tokenCheckInterval = checkInterval;
};

// ===== Enhanced Fetch Helper with Token Check ===========================
const fetchWithTokenCheck = async (url, options = {}) => {
  try {
    const response = await fetch(url, options);
    
    // If unauthorized, check token validity
    if (response.status === 401) {
      const token = sessionStorage.getItem('token') || localStorage.getItem('token');
      
      if (token) {
        const tokenCheck = await fetch('/api/auth/token/check', {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        
        const tokenData = await tokenCheck.json();
        
        if (!tokenCheck.ok || !tokenData.success) {
          Swal.fire({
            icon: 'warning',
            title: 'Session Expired',
            text: 'Your session has expired. Please log in again.',
            confirmButtonText: 'Login Now',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
              confirmButton: 'btn btn-primary'
            }
          }).then(() => {
            sessionStorage.removeItem('token');
            localStorage.removeItem('token');
            window.location.href = '/login';
          });
          
          throw new Error('Token expired');
        }
      }
    }
    
    return response;
  } catch (error) {
    throw error;
  }
};
  // Global auth cache (in-memory only)
  window.__AUTH_CACHE__ = window.__AUTH_CACHE__ || {
    role: null,
    rolePromise: null
  };

  const normalizeRole = (r) => String(r || '').trim().toLowerCase();

  // Cached role fetcher
  const getMyRoleCached = async (token) => {
    if (!token) return '';

    const cache = window.__AUTH_CACHE__;

    // ✅ Already resolved
    if (cache.role !== null) return cache.role;

    // ⏳ Fetch already in progress
    if (cache.rolePromise) return cache.rolePromise;

    // 🚀 First-time fetch
    cache.rolePromise = fetch('/api/auth/my-role', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
      }
    })
      .then(res => res.ok ? res.json() : null)
      .then(data => {
        // Accept multiple common response shapes (no session/local role usage)
        const ok =
          data?.status === 'success' ||
          data?.success === true ||
          data?.ok === true ||
          data?.status === true;

        const roleVal =
          data?.role ??
          data?.data?.role ??
          data?.data?.user?.role ??
          data?.user?.role ??
          data?.auth?.role ??
          '';

        cache.role = normalizeRole(ok ? roleVal : roleVal);

        // 🔔 Notify other scripts
        document.dispatchEvent(
          new CustomEvent('auth:role-ready', {
            detail: { role: cache.role }
          })
        );

        return cache.role;
      })
      .catch(err => {
        console.error('getMyRoleCached failed', err);
        cache.role = '';
        return '';
      })
      .finally(() => {
        cache.rolePromise = null;
      });

    return cache.rolePromise;
  };
  window.getMyRoleCached = getMyRoleCached;

  // Convenience getter (always reads current cached role)
  window.getCurrentRole = () => normalizeRole(window.__AUTH_CACHE__?.role || '');

  // Fetch role once on page load
  if (tok) {
    await getMyRoleCached(tok);
        setupTokenExpirationCheck();

  }

  // Local alias (optional, but keeps your existing logic intact)
  const USER_ROLE = window.__AUTH_CACHE__.role || '';

  // Module param
  const qsObj = new URLSearchParams(location.search);
  let selectedModuleUuid = qsObj.get('module') || null;

  const getTabFromUrl = () => {
    const p = new URLSearchParams(location.search);
    return p.get('tab') || (document.body.dataset.initialTab || 'recorded');
  };

  const syncTabLinks = (moduleUuid) => {
    const links = document.querySelectorAll('#vcTabs .nav-link');
    links.forEach(a => {
      try {
        const u = new URL(a.href, location.origin);
        if (moduleUuid) u.searchParams.set('module', moduleUuid);
        else u.searchParams.delete('module');
        a.href = u.toString();
      } catch (err) {}
    });
  };

  const updateQueryParam = (key, val) => {
    const usp = new URLSearchParams(location.search);
    if (!val) usp.delete(key); else usp.set(key, val);
    const qsString = usp.toString();
    history.replaceState({ tab: getTabFromUrl() }, '', `${location.pathname}${qsString ? ('?' + qsString) : ''}`);
    if (key === 'module') syncTabLinks(val);
  };

  syncTabLinks(selectedModuleUuid);

  // Role-aware back button
  const roleCoursesUrl = (r) => {

    return "/running-courses";
  };

  (function setupBackToCourses() {
    const go = (url) => { try { window.location.href = url; } catch(e){} };

    const handler = async (e) => {
      if (e && typeof e.preventDefault === 'function') e.preventDefault();

      // Ensure role is available (but still safe if it fails)
      try { if (tok) await window.getMyRoleCached(tok); } catch(_e){}

      const roleNow = window.getCurrentRole() || USER_ROLE || '';
      go(roleCoursesUrl(roleNow));
      return false;
    };

    window.backToCourses = handler;
    el.asideBackBtn?.addEventListener('click', handler);
  })();

  // ===== Mobile Hamburger Menu ===========================================
  const toggleMobileSidebar = () => {
    if (!isMobile()) return;

    if (document.body.classList.contains('mobile-sidebar-open')) {
      document.body.classList.remove('mobile-sidebar-open');
      // el.mobileCourseDetails?.classList.add('show');
    } else {
      document.body.classList.add('mobile-sidebar-open');
      // el.mobileCourseDetails?.classList.remove('show');
    }
  };
  el.mobileHamburgerBtn?.addEventListener('click', toggleMobileSidebar);

  const closeMobileSidebar = () => {
    if (isMobile()) {
      document.body.classList.remove('mobile-sidebar-open');
      // el.mobileCourseDetails?.classList.add('show');
    }
  };

  // ===== Small bus (tabs can listen) =====================================
  const bus = { emit(evt, detail){ document.dispatchEvent(new CustomEvent(evt, { detail })); } };
  window.__VCBUS__ = bus;

  // ===== Mobile Course Details ===========================================
  const renderMobileCourseDetails = (course) => {
    if (!course || !el.mobileCourseDescription || !el.mobileCourseMeta) return;

    el.mobileCourseDescription.textContent = course.description || 'No description available.';

    const metaItems = [];
    if (course.difficulty) {
      metaItems.push(`<div class="meta-item"><i class="fa fa-signal"></i><span>${course.difficulty} Level</span></div>`);
    }
    if (course.language) {
      metaItems.push(`<div class="meta-item"><i class="fa fa-language"></i><span>${course.language}</span></div>`);
    }
    if (course.duration_hours) {
      metaItems.push(`<div class="meta-item"><i class="fa fa-clock"></i><span>${course.duration_hours} hours</span></div>`);
    }
    if (course.category) {
      metaItems.push(`<div class="meta-item"><i class="fa fa-tag"></i><span>${course.category}</span></div>`);
    }

    el.mobileCourseMeta.innerHTML = metaItems.join('');
  };

  // ===== Render helpers ===================================================
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
    const btns = ['#btn-refresh', '#qz-refresh'];
    for (const sel of btns) {
      const b = document.querySelector(sel);
      if (b && typeof b.click === 'function') { b.click(); break; }
    }
  };

  // ===== Modules ==========================================================
  const renderModules = (modules=[]) => {
  el.mList.innerHTML = '';
  el.mEmpty.style.display = modules.length ? 'none' : '';
  const frag = document.createDocumentFragment();

  // ✅ student detection (your role cache)
  const roleNow = (window.getCurrentRole?.() || USER_ROLE || '').toLowerCase();
  const isStudent = roleNow === 'student';

  modules.forEach(m => {
    const accessState = String(m.access_state || 'unlocked').toLowerCase(); // locked/unlocked
    const isLocked = isStudent && accessState === 'locked';

    const div = document.createElement('div');
    div.className = 'vc-module' + (isLocked ? ' is-locked' : '');
    div.dataset.uuid = m.uuid;

   const badgeHtml = isStudent
  ? (isLocked ? `<i class="fa-solid fa-lock vc-lock-ico" title="Locked"></i>` : ``)
  : '';


    div.innerHTML = `
      <div class="module-content">
        <div class="t">${m.title ?? 'Untitled module'}</div>
        <div class="d">${m.short_description ?? ''}</div>
      </div>

      <div class="module-meta">
        ${badgeHtml}
      </div>
    `;

    div.addEventListener('click', () => {
      // ✅ block locked module selection for students
      if (isLocked) {
        Swal?.fire?.({
          icon: 'info',
          title: 'Module Locked',
          text: 'Complete the required steps to unlock this module.',
          confirmButtonText: 'OK'
        });
        return;
      }

      selectedModuleUuid = m.uuid;
      [...el.mList.children].forEach(c => c.classList.toggle('active', c.dataset.uuid === selectedModuleUuid));
      setModuleHeader(m);

      updateQueryParam('module', selectedModuleUuid);
      bus.emit('vc:module-changed', { moduleUuid: selectedModuleUuid, module: m });

      closeMobileSidebar();
      tryRefreshCurrentTab();
    });

    frag.appendChild(div);
  });

  el.mList.appendChild(frag);

  // === DEFAULT SELECTION: first UNLOCKED module for students ============
  let chosen = null;

  if (modules.length) {
    const roleNow2 = (window.getCurrentRole?.() || USER_ROLE || '').toLowerCase();
    const isStudent2 = roleNow2 === 'student';

    const isUnlocked = (mm) => String(mm?.access_state || 'unlocked').toLowerCase() !== 'locked';

    if (selectedModuleUuid) {
      chosen = modules.find(x => x.uuid === selectedModuleUuid) || null;
      // if selected is locked for student, force fallback
      if (isStudent2 && chosen && !isUnlocked(chosen)) chosen = null;
    }

    if (!chosen) {
      chosen = isStudent2 ? (modules.find(isUnlocked) || modules[0]) : modules[0];
      selectedModuleUuid = chosen?.uuid || null;
      if (selectedModuleUuid) updateQueryParam('module', selectedModuleUuid);
    }
  }

  if (chosen) {
    [...el.mList.children].forEach(c => c.classList.toggle('active', c.dataset.uuid === selectedModuleUuid));
    setModuleHeader(chosen);
    bus.emit('vc:module-changed', { moduleUuid: selectedModuleUuid, module: chosen });
  } else {
    setModuleHeader(null);
  }
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

  // ===== Tab switching (no reload) =======================================
  const tabCache = new Map();
  const tabMeta  = new Map();

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

    const nodes = [...el.tabContent.childNodes].filter(n => !(n.nodeType === 1 && n.classList.contains('vc-tab-spinner')));
    nodes.forEach(n => pane.appendChild(n));

    const spinner = el.tabContent.querySelector('.vc-tab-spinner');
    el.tabContent.innerHTML = '';
    if (spinner) el.tabContent.appendChild(spinner);
    el.tabContent.appendChild(pane);

    tabCache.set(initialTab, pane);
    tabMeta.set(initialTab, { inited: true, inlineScripts: [] });
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

    pane.querySelectorAll('link[rel="stylesheet"]').forEach(l => l.remove());
    pane.querySelectorAll('script[src]').forEach(s => {
      const srcUrl = (s.getAttribute('src') || '').trim();
      if (srcUrl && !srcUrl.includes('sweetalert2')) {
        loadScriptOnce(srcUrl).catch(()=>{});
      }
      s.remove();
    });

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

    const currentPane = tabCache.get(currentTab);
    if (currentPane && currentPane.dataset.tab === tabKey) return;

    setTabsActiveUI(tabKey);
    setTabLoading(true);

    await ensurePaneLoaded(tabKey);

    const existing = el.tabContent.querySelector('.vc-tab-pane');
    if (existing) existing.remove();

    const pane = tabCache.get(tabKey);
    if (pane) el.tabContent.appendChild(pane);

    if (opts.push) {
      const u = new URL(window.location.href);
      u.searchParams.set('tab', tabKey);
      if (selectedModuleUuid) u.searchParams.set('module', selectedModuleUuid);
      else u.searchParams.delete('module');
      history.pushState({ tab: tabKey }, '', u.toString());
    }

    const meta = tabMeta.get(tabKey);
    if (meta && !meta.inited) {
      meta.inited = true;
      setTimeout(() => {
        (meta.inlineScripts || []).forEach(execInline);
        bus.emit('vc:tab-changed', { tab: tabKey, moduleUuid: selectedModuleUuid });
      }, 0);
    } else {
      bus.emit('vc:tab-changed', { tab: tabKey, moduleUuid: selectedModuleUuid });
    }

    setTabLoading(false);
  };

  el.tabs?.addEventListener('click', async (e) => {
    const a = e.target.closest('a.nav-link');
    if (!a) return;
    const tabKey = a.dataset.tab;
    if (!tabKey) return;

    e.preventDefault();
    await showPane(tabKey, { push: true });
  });

  window.addEventListener('popstate', () => {
    const t = getTabFromUrl();
    showPane(t, { push: false });
  });

  // ===== Fetch course view payload =======================================
  const api = courseKey ? `/api/courses/by-batch/${encodeURIComponent(courseKey)}/view` : null;
  if (!api) return;

  captureInitialPane();

  showPageOverlay();
  fetch(api, { headers: { 'Accept':'application/json', ...auth } })
    .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
    .then(({ data }) => {
      const { course, modules, batch } = data;

      renderMobileCourseDetails(course);

      if (batch) {
        el.batchDetails.style.display = 'block';
        el.batchTitle.textContent = batch.badge_title || 'Batch';
        el.batchDescription.textContent = batch.badge_description || batch.description || '';
        el.batchTagline.textContent = batch.tagline || '';

        const batchInfoItems = [];

        if (batch.mode) {
          const modeIcon =
            batch.mode === 'online'
              ? 'fa-globe'
              : batch.mode === 'offline'
              ? 'fa-location-dot'
              : 'fa-layer-group';

          const modeLabel = (batch.mode.charAt(0).toUpperCase() + batch.mode.slice(1));

          batchInfoItems.push(`<span class="vc-batch-info-item"><i class="fa ${modeIcon}"></i>${modeLabel}</span>`);
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

      const sorted = (modules || []).slice().sort((a, b) => (a.order_no ?? 0) - (b.order_no ?? 0));
      renderModules(sorted);
      wireSearch(sorted);

      // In FULL mode on mobile, show details card when main area is visible
      if (isMobile()) el.mobileCourseDetails?.classList.add('show');
    })
    .catch(err => {
      console.error('viewCourse.fetch', err);
    })
    .finally(() => {
      hidePageOverlay();
    });

  window.addEventListener('resize', () => {
    if (!isMobile()) document.body.classList.remove('mobile-sidebar-open');
  });
});
</script>
</body>
</html>
