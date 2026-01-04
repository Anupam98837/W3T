<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>@yield('title','W3Techiez')</title>

  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/web/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

  @stack('styles')
  @yield('styles')

  <style>
    /* ================= W3Techiez Layout (namespaced; no overrides of main.css) ================= */
    :root{
      --w3-rail-w: 256px;
      --w3-rail-bg:       var(--surface);
      --w3-rail-text:     var(--text-color);
      --w3-rail-muted:    var(--muted-color);
      --w3-rail-border:   var(--line-strong);
      --w3-rail-hover:    rgba(2,6,23,.045);
      --w3-rail-active:   rgba(13,148,136,.12);

      --w3-rule-grad-l:   linear-gradient(90deg, rgba(2,6,23,0), rgba(2,6,23,.14), rgba(2,6,23,0));
      --w3-rule-grad-d:   linear-gradient(90deg, rgba(226,232,240,0), rgba(226,232,240,.22), rgba(226,232,240,0));
    }

    body{min-height:100dvh;background:var(--bg-body);color:var(--text-color)}

    /* ================= LOADING OVERLAY (NEW) ================= */
    #w3BootOverlay{
      position:fixed; inset:0; z-index:2000;
      background:color-mix(in oklab, var(--bg-body,#f6f7fb) 92%, #000 8%);
      display:flex; align-items:center; justify-content:center;
      transition:opacity .2s ease, visibility .2s ease;
    }
    html.theme-dark #w3BootOverlay{
      background:rgba(2,6,23,.92);
    }
    #w3BootOverlay.w3-hide{
      opacity:0; visibility:hidden; pointer-events:none;
    }
    .w3-boot-card{
      width:min(420px, calc(100% - 28px));
      background:var(--surface,#fff);
      border:1px solid var(--line-strong,#e5e7eb);
      border-radius:16px;
      box-shadow:var(--shadow-2);
      padding:16px 16px;
      display:flex;
      align-items:center;
      gap:12px;
    }
    html.theme-dark .w3-boot-card{
      background:#0f172a;
      border-color:var(--line-strong,#1f2937);
    }
    .w3-boot-logo{
      width:44px; height:44px; border-radius:12px;
      border:1px solid var(--line-strong,#e5e7eb);
      overflow:hidden;
      display:grid; place-items:center;
      background:var(--surface-2,#fff);
      flex:0 0 auto;
    }
    html.theme-dark .w3-boot-logo{
      background:#0b1220;
      border-color:var(--line-strong,#1f2937);
    }
    .w3-boot-logo img{width:100%; height:100%; object-fit:cover}
    .w3-boot-text{flex:1}
    .w3-boot-title{
      font-family:var(--font-head);
      font-weight:800;
      color:var(--ink,#111827);
      margin:0;
      font-size:1.05rem;
      line-height:1.2;
    }
    html.theme-dark .w3-boot-title{color:#e5e7eb}
    .w3-boot-sub{
      margin:2px 0 0;
      color:var(--muted-color,#6b7280);
      font-size:.9rem;
    }
    .w3-boot-spin{
      width:34px; height:34px;
      display:grid; place-items:center;
      border-radius:999px;
      border:1px solid var(--line-strong,#e5e7eb);
      background:var(--surface-2,#fff);
      flex:0 0 auto;
    }
    html.theme-dark .w3-boot-spin{
      background:#0b1220;
      border-color:var(--line-strong,#1f2937);
    }
    .w3-boot-spinner{
      width:18px; height:18px;
      border-radius:999px;
      border:2px solid rgba(148,163,184,.55);
      border-top-color:var(--accent-color,#2563eb);
      animation:w3spin .8s linear infinite;
    }
    @keyframes w3spin{to{transform:rotate(360deg)}}

    /* Sidebar */
    .w3-sidebar{
      position:fixed; inset:0 auto 0 0; width:var(--w3-rail-w); background:var(--w3-rail-bg);
      border-right:1px solid var(--w3-rail-border); display:flex; flex-direction:column; z-index:1041;
      transform:translateX(0); transition:transform .28s ease;
    }
    .w3-sidebar-head{
      height:64px; display:flex; align-items:center; gap:10px; padding:0 14px;
      border-bottom:1px solid var(--w3-rail-border)
    }
    .w3-brand{display:flex; align-items:center; gap:10px; text-decoration:none}
    .w3-brand img{height:26px}
    .w3-brand span{font-family:var(--font-head); font-weight:700; color:var(--ink); font-size:1.02rem}

    .w3-sidebar-scroll{flex:1; overflow:auto; padding:8px 10px}

    /* Section separators (Wrike-like) */
    .w3-nav-section{padding:10px 6px 6px}
    .w3-section-title{
      display:flex; align-items:center; gap:8px; color:var(--primary-color);
      font-size:.72rem; font-weight:700; letter-spacing:.12rem; text-transform:uppercase; padding:0 6px;
    }
    .w3-section-rule{height:10px; display:grid; align-items:center}
    .w3-section-rule::before{content:""; height:1px; width:100%; background:var(--w3-rule-grad-l)}
    html.theme-dark .w3-section-rule::before{ background:var(--w3-rule-grad-d) }

    /* Menu */
    .w3-menu{display:grid; gap:4px; padding:6px 4px}
    .w3-link{
      display:flex; align-items:center; gap:10px; padding:9px 10px;
      color:var(--w3-rail-text); border-radius:10px; transition:background .18s ease, transform .18s ease;
      text-decoration:none;
    }
    .w3-link i{opacity:.9; min-width:18px; text-align:center}
    .w3-link:hover{background:var(--w3-rail-hover); transform:translateX(2px)}
    .w3-link.active{background:var(--w3-rail-active); position:relative}
    .w3-link.active::before{
      content:""; position:absolute; left:-6px; top:8px; bottom:8px; width:3px; background:var(--accent-color); border-radius:4px;
    }

    /* Group / Submenu */
    .w3-group{display:grid; gap:4px; margin-top:2px}
    .w3-toggle{cursor:pointer}
    .w3-toggle .w3-chev{
      margin-left:auto; margin-right:2px; padding-left:6px;
      transition:transform .18s ease; opacity:.85;
    }
    .w3-toggle.w3-open .w3-chev{transform:rotate(180deg)}

    .w3-submenu{
      display:grid; gap:2px; margin-left:8px; padding-left:8px; border-left:1px dashed var(--w3-rail-border);
      max-height:0; overflow:hidden; transition:max-height .24s ease;
    }
    .w3-submenu.w3-open{max-height:600px}
    .w3-submenu .w3-link{padding:8px 10px 8px 34px; font-size:.86rem}

    .w3-sidebar-foot{border-top:1px solid var(--w3-rail-border); padding:8px 10px}

    /* Appbar */
    .w3-appbar{
      position:sticky; top:0; z-index:1030; height:64px; background:var(--surface);
      border-bottom:1px solid var(--line-strong); display:flex; align-items:center;
    }
    .w3-appbar-inner{
      width:100%;
      display:flex; align-items:center; gap:10px; padding:0 12px;
    }
    @media (min-width: 992px){
      .w3-appbar-inner{ margin-left: 0; }
    }

    /* Mobile appbar logo */
    .w3-app-logo{display:flex; align-items:center; gap:8px; text-decoration:none}
    .w3-app-logo img{height:22px}
    .w3-app-logo span{font-family:var(--font-head); font-weight:700; color:var(--ink); font-size:.98rem}

    .w3-icon-btn{
      width:36px; height:36px; display:inline-grid; place-items:center; border:1px solid var(--line-strong);
      background:#fff; color:var(--secondary-color); border-radius:999px; transition:transform .18s ease, background .18s ease;
    }
    .w3-icon-btn:hover{background:#f6f8fc; transform:translateY(-1px)}

    /* Hamburger (morph) */
    .w3-hamburger{width:40px; height:40px; border:1px solid var(--line-strong); border-radius:999px; background:#fff; display:inline-grid; place-items:center; cursor:pointer}
    .w3-bars{position:relative; width:18px; height:12px}
    .w3-bar{position:absolute; left:0; width:100%; height:2px; background:#1f2a44; border-radius:2px; transition:transform .25s ease, opacity .2s ease, top .25s ease}
    .w3-bar:nth-child(1){top:0}
    .w3-bar:nth-child(2){top:5px}
    .w3-bar:nth-child(3){top:10px}
    .w3-hamburger.is-active .w3-bar:nth-child(1){top:5px; transform:rotate(45deg)}
    .w3-hamburger.is-active .w3-bar:nth-child(2){opacity:0}
    .w3-hamburger.is-active .w3-bar:nth-child(3){top:5px; transform:rotate(-45deg)}

    /* Content */
    .w3-content{
      padding:16px;
      max-width:1280px;
      margin-inline:auto;
      transition:padding .28s ease;
    }
    @media (min-width: 992px){
      .w3-content{ padding-left: calc(16px + var(--w3-rail-w)); }
    }

    /* Overlay (mobile) */
    .w3-overlay{
      position:fixed; top:0; bottom:0; right:0; left:var(--w3-rail-w);
      background:rgba(0,0,0,.45); z-index:1040; opacity:0; visibility:hidden; pointer-events:none;
      transition:opacity .2s ease, visibility .2s ease;
    }
    .w3-overlay.w3-on{opacity:1; visibility:visible; pointer-events:auto}

    .rounded-xs{ border-radius:6px; }

    /* Mobile */
    @media (max-width: 991px){
      .w3-sidebar{transform:translateX(-100%)}
      .w3-sidebar.w3-on{transform:translateX(0)}
      .w3-content{ padding-left:16px; }
      .w3-appbar-inner{margin-left:0; padding-inline:10px}
      .js-theme-btn{display:none!important}
      .w3-overlay{left:var(--w3-rail-w)}
      .w3-app-logo{display:flex}
    }
    @media (min-width: 992px){
      .w3-app-logo{display:none}
    }

    /* Dark flips */
    html.theme-dark .w3-sidebar{background:var(--surface); border-right-color:var(--line-strong)}
    html.theme-dark .w3-sidebar-head{border-bottom-color:var(--line-strong)}
    html.theme-dark .w3-link:hover{background:#0c172d}
    html.theme-dark .w3-link.active{background:rgba(20,184,166,.12)}
    html.theme-dark .w3-overlay{background:rgba(0,0,0,.55)}

    html.theme-dark .w3-appbar{background:var(--surface); border-bottom-color:var(--line-strong)}
    html.theme-dark .w3-icon-btn, html.theme-dark .w3-hamburger{background:var(--surface); border-color:var(--line-strong); color:var(--text-color)}
    html.theme-dark .w3-icon-btn:hover, html.theme-dark .w3-hamburger:hover{background:#0c172d}
    html.theme-dark .w3-bar{ background:#e8edf7; }

    html.theme-dark .dropdown-menu{ background:#0f172a; border-color:var(--line-strong); }
    html.theme-dark .dropdown-menu .dropdown-header{ color:var(--text-color); }
    html.theme-dark .dropdown-menu .dropdown-item{ color:var(--text-color); }
    html.theme-dark .dropdown-menu .dropdown-item:hover{ background:#13203a; color:var(--accent-color); }

    /* RTE (unchanged) */
    .toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
    .tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
    .tool:hover{background:var(--page-hover)}
    .rte-wrap{position:relative}
    .rte{
      min-height:200px;max-height:600px;overflow:auto;
      border:1px solid var(--line-strong);border-radius:12px;background:#fff;padding:12px;line-height:1.6;outline:none
    }
    .rte:focus{box-shadow:var(--ring);border-color:var(--accent-color)}
    .rte-ph{position:absolute;top:12px;left:12px;color:#9aa3b2;pointer-events:none;font-size:var(--fs-14)}
    .rte.has-content + .rte-ph{display:none}

    #rte, .rte, .notice-editor #rte {
      display:block !important;
      min-height:160px !important;
      max-height:600px !important;
      overflow:auto !important;
      padding:12px 14px !important;
      border-radius:12px !important;
      border:1px solid var(--line-strong,#d1d5db) !important;
      background:var(--surface,#ffffff) !important;
      color:var(--ink,#111827) !important;
      line-height:1.6 !important;
      font-size:15px !important;
      box-sizing:border-box !important;
      -webkit-font-smoothing:antialiased !important;
    }
    #rte:focus, .rte:focus {
      outline: none !important;
      box-shadow: 0 0 0 4px color-mix(in oklab, var(--accent-color,#2563eb) 12%, transparent) !important;
      border-color: var(--accent-color,#2563eb) !important;
    }
    #rte.has-content + .rte-ph,
    .rte.has-content + .rte-ph { display: none !important; }
    .rte-ph { position:absolute; top:12px; left:12px; pointer-events:none; color:var(--muted-color,#9aa3b2) !important; font-size:0.95rem; }
    #rte_toolbar, .rte-toolbar, .notice-editor #rte_toolbar {
      display:flex !important;
      gap:6px !important;
      flex-wrap:wrap !important;
      margin-bottom:8px !important;
      align-items:center !important;
    }
    #rte_toolbar [data-cmd], #rte_toolbar .tool {
      border:1px solid var(--line-strong,#e6e9ef) !important;
      background:var(--surface-2,#fff) !important;
      padding:6px 9px !important;
      border-radius:10px !important;
      cursor:pointer !important;
      font-size:14px !important;
      color:var(--ink,#111827) !important;
    }
    #rte_toolbar [data-cmd]:hover { background: var(--page-hover,#f3f4f6) !important; }
    #rte_toolbar [data-cmd].active, #rte_toolbar [data-cmd][aria-pressed="true"] {
      background: var(--accent-color,#2563eb) !important;
      color: #fff !important;
      border-color: transparent !important;
    }
    #rte p, #rte div { margin: 0 0 0.75rem !important; }
    html.theme-dark #rte, html.theme-dark .rte {
      background:#0f172a !important;
      color:#e5e7eb !important;
      border-color:var(--line-strong,#1f2937) !important;
    }
    html.theme-dark #rte_toolbar [data-cmd], html.theme-dark .tool {
      background:#0b1220 !important;
      color:#e5e7eb !important;
      border-color:var(--line-strong,#1f2937) !important;
    }
    #rte:empty:before, .rte:empty:before { content: "" !important; display:block; }
    #rte, .rte { user-select: text !important; -moz-user-select: text !important; -webkit-user-select: text !important; }

    html.theme-dark .rte{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}

    /* ✅ Academics no-access message (NEW, minimal) */
    html.theme-dark #noAcademicAccess .alert{
      background:#0b1220;
      border-color:var(--line-strong,#1f2937);
      color:#e5e7eb;
    }
    html.theme-dark #noAcademicAccess .alert a{color:#e5e7eb}
  </style>

  <style>
    /* Force dark mode scrollbar styles */
    html.theme-dark ::-webkit-scrollbar { width: 8px !important; }
    html.theme-dark ::-webkit-scrollbar-track { background: #1e293b !important; border-radius: 4px !important; }
    html.theme-dark ::-webkit-scrollbar-thumb { background: #475569 !important; border-radius: 4px !important; }
    html.theme-dark ::-webkit-scrollbar-thumb:hover { background: #64748b !important; }

    html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar { width: 6px !important; }
    html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar-track { background: #1e293b !important; }
    html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar-thumb { background: #475569 !important; }
    /* Admin user pill button (isolated from .btn-primary conflicts) */
.admin-pill-btn{
  background: var(--primary-color);
  border: 1px solid var(--primary-color);
  color:#fff;
}
.admin-pill-btn:hover{
  background: var(--secondary-color);
  border-color: var(--secondary-color);
  color:#fff;
}
.admin-pill-btn:focus{
  box-shadow: 0 0 0 .2rem rgba(158,54,58,.25);
}
/* User dropdown (isolated classes to avoid conflicts) */
.admin-user-menu{
  border-radius: 14px;
  border: 1px solid var(--line-strong);
  box-shadow: var(--shadow-2);
  min-width: 220px;
  padding: .4rem;
}

.admin-user-menu__header{
  font-size: .78rem;
  color: var(--muted);
  padding: .35rem .75rem;
}

.admin-user-menu__item{
  border-radius: 10px;
  padding: .55rem .75rem;
}

.admin-user-menu__item:hover,
.admin-user-menu__item:focus{
  background: rgba(201,75,80,.10);
}

.admin-user-menu__divider{
  margin: .35rem .4rem;
}

.admin-user-menu__item--danger{
  color: var(--danger-color) !important;
}
.admin-user-menu__item--danger:hover{
  background: rgba(220,53,69,.10);
}

  </style>
</head>
<body>

<!-- ✅ BOOT LOADING OVERLAY (NEW) -->
<div id="w3BootOverlay" aria-live="polite" aria-busy="true">
    @include('partials.overlay')
</div>

<!-- Sidebar -->
<aside id="sidebar" class="w3-sidebar" aria-label="Sidebar">
  <div class="w3-sidebar-head">
    <a href="/" class="w3-brand">
      <img id="logo" src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
      <span>W3Techiez</span>
    </a>
  </div>

  <div class="w3-sidebar-scroll">

    <!-- Overview -->
    <div class="w3-nav-section">
      <div class="w3-section-title text"><i class="fa-solid fa-chart-simple"></i> OVERVIEW</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu" aria-label="Overview">
      <a href="/dashboard" class="w3-link"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>

      <!-- ✅ Default for all users (NEW) -->
    </nav>

    <!-- ACADEMICS heading -->
    <div class="w3-nav-section">
      <div class="w3-section-title"><i class="fa-solid fa-graduation-cap"></i> ACADEMICS</div>
      <div class="w3-section-rule"></div>
    </div>

    <div id="allMenuWrap" style="display:none">
      <nav class="w3-menu" aria-label="Academics (All)">

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-courses" aria-expanded="false">
            <i class="fa-solid fa-book-open"></i><span>Courses</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-courses" class="w3-submenu" role="group" aria-label="Courses submenu">
            <a href="/courses/manage" class="w3-link">All Courses</a>
            <a href="/courses/create" class="w3-link" style="display:none">Create Course</a>
            <a href="/course-categories/manage" class="w3-link">Categories</a>
            <a href="/running-courses" class="w3-link">Running Courses</a>
            <a href="/batches/manage" class="w3-link">Batches</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-course-modules" aria-expanded="false">
            <i class="fa-solid fa-layer-group"></i><span>Course Modules</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-course-modules" class="w3-submenu" role="group" aria-label="Course Modules submenu">
            <a href="/courses-module/manage" class="w3-link">All Course Modules</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-assignments" aria-expanded="false">
            <i class="fa-solid fa-file-lines"></i><span>Assignments</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-assignments" class="w3-submenu" role="group" aria-label="Assignments submenu">
            <a href="/assignments/create" class="w3-link">Create Assignment</a>
            <a href="/assignments/manage" class="w3-link">All Assignments</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-study-materials" aria-expanded="false">
            <i class="fa-solid fa-book"></i><span>Study Materials</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-study-materials" class="w3-submenu" role="group" aria-label="Study Materials submenu">
            <a href="/study-material/create" class="w3-link">Create Study Material</a>
            <a href="/study-material/manage" class="w3-link">All Study Materials</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-notices" aria-expanded="false">
            <i class="fa-solid fa-bullhorn"></i><span>Notices</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-notices" class="w3-submenu" role="group" aria-label="Notices submenu">
            <a href="/notice/manage" class="w3-link">All Notices</a>
            <a href="/notice/create" class="w3-link">Create Notice</a>
          </div>
        </div>

      </nav>

      <!-- Exams -->
      <div class="w3-nav-section">
        <div class="w3-section-title"><i class="fa-solid fa-file-circle-check"></i> EXAMS</div>
        <div class="w3-section-rule"></div>
      </div>
      <nav class="w3-menu" aria-label="Exams (All)">

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-quiz" aria-expanded="false">
            <i class="fa-solid fa-pen-to-square"></i><span>Quiz</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-quiz" class="w3-submenu" role="group" aria-label="Quiz submenu">
            <a href="/quizz/manage" class="w3-link">All Quizzes</a>
            <a href="/quizz/create" class="w3-link">Create Quiz</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-coding-tests" aria-expanded="false">
            <i class="fa-solid fa-code"></i><span>Coding Tests</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-coding-tests" class="w3-submenu" role="group" aria-label="Coding Tests submenu">
            <a href="/topic/manage" class="w3-link">Manage Topics</a>
            <a href="/topic/module/manage" class="w3-link">Manage Topic Modules</a>
          </div>
        </div>

      </nav>

      <!-- USERS -->
      <div class="w3-nav-section">
        <div class="w3-section-title"><i class="fa-solid fa-users"></i><span class="ms-1">USERS</span></div>
        <div class="w3-section-rule"></div>
      </div>
      <nav class="w3-menu" aria-label="Users (All)">
        <a href="/users/manage" class="w3-link">
          <i class="fa-solid fa-user-pen" aria-hidden="true"></i><span>Users</span>
        </a>
      </nav>

      <!-- Privileges -->
      <div class="w3-nav-section">
        <div class="w3-section-title"><i class="fa-solid fa-screwdriver-wrench"></i> PRIVILEGES</div>
        <div class="w3-section-rule"></div>
      </div>
      <nav class="w3-menu" aria-label="Privileges (All)">

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-dashboard-menu" aria-expanded="false">
            <i class="fa-solid fa-puzzle-piece"></i><span>Dashboard Menu</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-dashboard-menu" class="w3-submenu" role="group" aria-label="Dashboard Menu submenu">
            <a href="/dashboard-menu/create" class="w3-link"><span>Create Menu</span></a>
            <a href="/dashboard-menu/manage" class="w3-link"><span>Manage Menu</span></a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-page-privilege" aria-expanded="false">
            <i class="fa-solid fa-shield-halved"></i><span>Page Privilege</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-page-privilege" class="w3-submenu" role="group" aria-label="Page Privilege submenu">
            <a href="/page-privilege/create" class="w3-link"><span>Create Privilege</span></a>
            <a href="/page-privilege/manage" class="w3-link"><span>Manage Privilege</span></a>
          </div>
        </div>

      </nav>

      <!-- LANDING PAGE -->
      <div class="w3-nav-section">
        <div class="w3-section-title"><i class="fa-solid fa-earth-asia"></i><span class="ms-1">LANDING PAGE</span></div>
        <div class="w3-section-rule"></div>
      </div>
      <nav class="w3-menu" aria-label="Landing Page (All)">

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-landingpage" aria-expanded="false">
            <i class="fa-solid fa-globe"></i><span>Landing Page</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-landingpage" class="w3-submenu">
            <a href="/updates/manage" class="w3-link">Updates</a>
            <a href="/contacts/manage" class="w3-link">Contacts</a>
            <a href="/hero-images/manage" class="w3-link">Hero Images</a>
            <a href="/featured/courses/manage" class="w3-link">Featured Courses</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-company" aria-expanded="false">
            <i class="fa-solid fa-building"></i><span>Company</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-company" class="w3-submenu">
            <a href="/about-us/manage" class="w3-link">About Us</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-blog" aria-expanded="false">
            <i class="fa-solid fa-pen-to-square"></i><span>Blog</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-blog" class="w3-submenu" role="group" aria-label="Blog submenu">
            <a href="/blog/manage" class="w3-link">All Blogs</a>
            <a href="/blog/create" class="w3-link">Create Blog</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-legal" aria-expanded="false">
            <i class="fa-solid fa-scale-balanced"></i><span>Legal</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-legal" class="w3-submenu">
            <a href="/terms-and-conditions/manage" class="w3-link">Terms & Conditions</a>
            <a href="/privacy-policy/manage" class="w3-link">Privacy Policy</a>
            <a href="/refund-policy/manage" class="w3-link">Refund Policy</a>
          </div>
        </div>

        <div class="w3-group">
          <a href="#" class="w3-link w3-toggle" data-target="sm-user-interaction" aria-expanded="false">
            <i class="fa-solid fa-comments"></i><span>User Enquiries</span>
            <i class="fa fa-chevron-down w3-chev"></i>
          </a>
          <div id="sm-user-interaction" class="w3-submenu">
            <a href="/enquiry/manage" class="w3-link">Enquiries</a>
          </div>
        </div>

      </nav>

      <!-- OPERATIONS -->
      <div class="w3-nav-section">
        <div class="w3-section-title"><i class="fa-solid fa-screwdriver-wrench"></i> OPERATIONS</div>
        <div class="w3-section-rule"></div>
      </div>
      <nav class="w3-menu" aria-label="Operations (All)">
        <a href="/mailers/manage" class="w3-link"><i class="fa-solid fa-gear"></i><span>Mailer</span></a>
      </nav>
    </div>

    <div id="dynamicMenuWrap" style="display:none">
      <nav id="dynamicMenu" class="w3-menu" aria-label="Dynamic Menu"></nav>
    </div>

    <!-- ✅ If no academic routes assigned -->
    <div id="noAcademicAccess" style="display:none">
      <div class="px-2 pt-2">
        <div class="alert alert-warning small mb-0">
          <i class="fa-solid fa-lock me-2"></i>
          You don’t have access to any Academic modules yet. Ask your instructor or admin to grant access.
        </div>
      </div>
    </div>


  </div>

  <div class="w3-sidebar-foot">
    <a href="/profile" class="w3-link"><i class="fa-regular fa-circle-user"></i><span>Profile</span></a>
    {{-- <a href="/settings" class="w3-link"><i class="fa fa-gear"></i><span>Settings</span></a> --}}

    <a href="#" id="logoutBtnSidebar" class="w3-link" style="padding:8px 10px">
      <i class="fa fa-right-from-bracket"></i><span>Logout</span>
    </a>
  </div>
</aside>

<!-- Appbar -->
<header class="w3-appbar">
  <div class="w3-appbar-inner">
    <button id="btnHamburger" class="w3-hamburger d-lg-none" aria-label="Open menu" aria-expanded="false" title="Menu">
      <span class="w3-bars" aria-hidden="true">
        <span class="w3-bar"></span><span class="w3-bar"></span><span class="w3-bar"></span>
      </span>
    </button>

    <!-- Mobile brand -->
    <a href="/dashboard" class="w3-app-logo d-lg-none">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
      <span>W3Techiez</span>
    </a>

    <strong class="ms-1 d-none d-lg-inline" style="font-family:var(--font-head);color:var(--ink)">
      @yield('title','W3Techiez Admin')
    </strong>

    <div class="ms-auto d-flex align-items-center gap-2">
      <button id="btnTheme" class="w3-icon-btn js-theme-btn d-none d-lg-inline-grid" aria-label="Toggle theme" title="Toggle theme">
        <i class="fa-regular fa-moon" id="themeIcon"></i>
      </button>

      <div class="dropdown">
        <a href="#" class="w3-icon-btn" id="alertsMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Alerts" title="Alerts">
          <i class="fa-regular fa-bell"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-2 shadow" style="min-width:320px">
          <div class="d-flex align-items-center justify-content-between px-2 mb-2">
            <strong>Notifications</strong>
            <a class="text-muted" href="/notifications">View all</a>
          </div>
          <div class="w3-note rounded-xs">
            <div class="small"><strong>Schedule update</strong> — Lab ME-302 moved to Thu 11 AM.</div>
          </div>
        </div>
      </div>

      <div class="dropdown d-none d-lg-block">
       <a href="#"
   class="btn admin-pill-btn rounded-pill d-flex align-items-center gap-2 px-3"
   id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
  <i class="fa-regular fa-user"></i>
  <span id="userRoleLabel" class="d-none d-xl-inline">Admin</span>
</a>
<ul class="dropdown-menu dropdown-menu-end admin-user-menu">
  <li class="dropdown-header admin-user-menu__header">Account</li>

  <!-- ✅ default routes -->
  <li>
    <a class="dropdown-item admin-user-menu__item" href="/profile">
      <i class="fa fa-id-badge me-2"></i>Profile
    </a>
  </li>
  <li>
    <a class="dropdown-item admin-user-menu__item" href="/settings">
      <i class="fa fa-gear me-2"></i>Settings
    </a>
  </li>

  <li><hr class="dropdown-divider admin-user-menu__divider"></li>

  <li>
    <a class="dropdown-item admin-user-menu__item admin-user-menu__item--danger" href="#" id="logoutBtn">
      <i class="fa fa-right-from-bracket me-2"></i>Logout
    </a>
  </li>
</ul>

      </div>
    </div>
  </div>
</header>

<!-- Overlay (mobile) -->
<div id="sidebarOverlay" class="w3-overlay" aria-hidden="true"></div>

<!-- Content -->
<main class="w3-content mx-auto">
  <section class="panel mx-auto">@yield('content')</section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
@yield('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {
  const html = document.documentElement;
  const THEME_KEY = 'theme';
  const btnTheme = document.getElementById('btnTheme');
  const themeIcon = document.getElementById('themeIcon');

  // ✅ overlay helpers (NEW)
  const bootOverlay = document.getElementById('w3BootOverlay');
  const showBoot = () => { try{ bootOverlay?.classList.remove('w3-hide'); }catch(e){} };
  const hideBoot = () => { try{ bootOverlay?.classList.add('w3-hide'); }catch(e){} };

  // show immediately on DOM ready
  showBoot();

  // ===== Theme
  function setTheme(mode){
    const isDark = mode === 'dark';
    html.classList.toggle('theme-dark', isDark);
    localStorage.setItem(THEME_KEY, mode);
    if (themeIcon) themeIcon.className = isDark ? 'fa-regular fa-sun' : 'fa-regular fa-moon';
  }
  setTheme(localStorage.getItem(THEME_KEY) || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
  btnTheme?.addEventListener('click', () => setTheme(html.classList.contains('theme-dark') ? 'light' : 'dark'));

  // ===== Sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const btnHamburger = document.getElementById('btnHamburger');

  const openSidebar = () => {
    sidebar.classList.add('w3-on');
    overlay.classList.add('w3-on');
    btnHamburger?.classList.add('is-active');
    btnHamburger?.setAttribute('aria-expanded','true');
    btnHamburger?.setAttribute('aria-label','Close menu');
  };
  const closeSidebar = () => {
    sidebar.classList.remove('w3-on');
    overlay.classList.remove('w3-on');
    btnHamburger?.classList.remove('is-active');
    btnHamburger?.setAttribute('aria-expanded','false');
    btnHamburger?.setAttribute('aria-label','Open menu');
  };

  btnHamburger?.addEventListener('click', () => sidebar.classList.contains('w3-on') ? closeSidebar() : openSidebar());
  overlay?.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });

  // ===== Submenus (bind once)
  function bindSubmenuToggles(root=document){
    root.querySelectorAll('.w3-toggle').forEach(tg => {
      if (tg.__bound) return;
      tg.__bound = true;

      tg.addEventListener('click', (e) => {
        e.preventDefault();
        const id = tg.dataset.target;
        const el = document.getElementById(id);
        if (!el) return;
        const open = el.classList.toggle('w3-open');
        tg.classList.toggle('w3-open', open);
        tg.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    });
  }

  // ===== Active link + open parent
  function markActiveLinks(){
    const path = window.location.pathname.replace(/\/+$/, '');
    document.querySelectorAll('.w3-menu a[href]').forEach(a => {
      const href = a.getAttribute('href');
      if (href && href !== '#' && href.replace(/\/+$/, '') === path){
        a.classList.add('active');
        const sub = a.closest('.w3-submenu');
        if (sub){
          sub.classList.add('w3-open');
          const toggle = sub.previousElementSibling;
          toggle?.classList.add('w3-open');
          toggle?.setAttribute('aria-expanded','true');
        }
      }
    });
  }

  // ===== Role label
  const roleLabelEl = document.getElementById('userRoleLabel');
  function titleizeRole(r){
    if (!r) return 'Admin';
    return r.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
  }
  const roleFromStorage = sessionStorage.getItem('role') || localStorage.getItem('role');
  if (roleLabelEl) roleLabelEl.textContent = titleizeRole(roleFromStorage) || 'Admin';

  // ===== Auth helpers
  function getBearerToken(){
    return sessionStorage.getItem('token') || localStorage.getItem('token') || null;
  }

  // ===== Sidebar API logic
  const allMenuWrap = document.getElementById('allMenuWrap');
  const dynamicMenuWrap = document.getElementById('dynamicMenuWrap');
  const dynamicMenu = document.getElementById('dynamicMenu');
  const noAcademicAccess = document.getElementById('noAcademicAccess'); // ✅ NEW

  function safeText(v){ return (v ?? '').toString(); }

  function iconHtml(iconClass, fallback='fa-solid fa-circle'){
    const cls = safeText(iconClass).trim();
    return `<i class="${cls || fallback}"></i>`;
  }

  function renderDynamicTree(tree){
    if (!dynamicMenu) return;
    dynamicMenu.innerHTML = '';

    (tree || []).forEach((header, hi) => {
      const hid = parseInt(header?.id || 0, 10);
      if (!hid) return;

      const headerName = safeText(header?.name || 'Menu');
      const headerIcon = header?.icon_class || 'fa-solid fa-folder';
      const subId = `dyn-sub-${hid}-${hi}`;

      const wrap = document.createElement('div');
      wrap.className = 'w3-group';

      wrap.innerHTML = `
        <a href="#" class="w3-link w3-toggle" data-target="${subId}" aria-expanded="false">
          ${iconHtml(headerIcon, 'fa-solid fa-folder')}<span>${headerName}</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="${subId}" class="w3-submenu" role="group" aria-label="${headerName} submenu"></div>
      `;

      const sub = wrap.querySelector('#' + subId);
      const pages = Array.isArray(header?.children) ? header.children : [];

      pages.forEach((p) => {
        const href = safeText(p?.href || '#');
        const name = safeText(p?.name || 'Page');
        const pIcon = safeText(p?.icon_class || '');

        const a = document.createElement('a');
        a.className = 'w3-link';
        a.href = href === '' ? '#' : href;
        a.innerHTML = pIcon ? `${iconHtml(pIcon)}<span>${name}</span>` : `<span>${name}</span>`;
        sub.appendChild(a);
      });

      if (sub.children.length) dynamicMenu.appendChild(wrap);
    });

    bindSubmenuToggles(dynamicMenu);
  }

  async function loadSidebarFromNewApi(){
    const token = getBearerToken();

    // reset visibility (NEW)
    noAcademicAccess && (noAcademicAccess.style.display = 'none');

    if (!token){
      allMenuWrap && (allMenuWrap.style.display = 'none');
      dynamicMenuWrap && (dynamicMenuWrap.style.display = 'none');
      noAcademicAccess && (noAcademicAccess.style.display = 'none');
      return;
    }

    try{
      const res = await fetch('/api/my/sidebar-menus', {
        method: 'GET',
        headers: {
          'Authorization': 'Bearer ' + token,
          'Accept': 'application/json'
        }
      });

      if (!res.ok){
        allMenuWrap && (allMenuWrap.style.display = 'none');
        dynamicMenuWrap && (dynamicMenuWrap.style.display = 'none');
        // keep message hidden on API error
        noAcademicAccess && (noAcademicAccess.style.display = 'none');
        return;
      }

      const data = await res.json();

      if (data === 'all' || data?.tree === 'all') {
        allMenuWrap && (allMenuWrap.style.display = '');
        dynamicMenuWrap && (dynamicMenuWrap.style.display = 'none');
        noAcademicAccess && (noAcademicAccess.style.display = 'none'); // ✅ NEW
        bindSubmenuToggles(allMenuWrap || document);
        return;
      }

      const tree = Array.isArray(data?.tree) ? data.tree : Array.isArray(data) ? data : [];
      if (tree.length) {
        allMenuWrap && (allMenuWrap.style.display = 'none');
        dynamicMenuWrap && (dynamicMenuWrap.style.display = '');
        noAcademicAccess && (noAcademicAccess.style.display = 'none'); // ✅ NEW
        renderDynamicTree(tree);
      } else {
        // ✅ NEW: show message when user has no academics routes assigned
        allMenuWrap && (allMenuWrap.style.display = 'none');
        dynamicMenuWrap && (dynamicMenuWrap.style.display = 'none');
        noAcademicAccess && (noAcademicAccess.style.display = '');
      }

    }catch(e){
      allMenuWrap && (allMenuWrap.style.display = 'none');
      dynamicMenuWrap && (dynamicMenuWrap.style.display = 'none');
      // keep message hidden on unexpected error
      noAcademicAccess && (noAcademicAccess.style.display = 'none');
    }
  }

  // ===== Logout with SweetAlert2 (redirect home)
  const API_LOGOUT = '/api/auth/logout';
  const LOGIN_PAGE = '/';

  function clearAuthStorage(){
    try { sessionStorage.removeItem('token'); } catch(e){}
    try { sessionStorage.removeItem('role'); } catch(e){}
    try { localStorage.removeItem('token'); } catch(e){}
    try { localStorage.removeItem('role'); } catch(e){}
  }

  async function performLogout(){
    const token = getBearerToken();

    const confirm = await Swal.fire({
      title: 'Log out?',
      text: 'You will be signed out of W3Techiez.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, logout',
      cancelButtonText: 'Cancel',
      focusCancel: true,
      confirmButtonColor: '#951eaa'
    });

    if (!confirm.isConfirmed) return;

    let ok = false;
    if (token){
      try{
        const res = await fetch(API_LOGOUT, {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
          body: ''
        });
        ok = res.ok;
      }catch(e){ ok = false; }
    }

    clearAuthStorage();

    await Swal.fire({
      title: ok ? 'Logged out' : 'Signed out locally',
      text: ok ? 'See you soon 👋' : 'Your session was cleared on this device.',
      icon: ok ? 'success' : 'info',
      timer: 1200,
      showConfirmButton: false
    });

    window.location.replace(LOGIN_PAGE);
  }

  document.getElementById('logoutBtn')?.addEventListener('click', (e) => { e.preventDefault(); performLogout(); });
  document.getElementById('logoutBtnSidebar')?.addEventListener('click', (e) => { e.preventDefault(); performLogout(); });

  // ===== INIT (Overlay stays until everything ready)
  (async () => {
    try{
      bindSubmenuToggles(document);
      await loadSidebarFromNewApi();   // ✅ load menu first
      markActiveLinks();              // ✅ then mark active
    } finally {
      // ✅ hide overlay when environment is ready
      hideBoot();
    }
  })();
});
</script>

<script>
/* =========================================================
   GLOBAL: Portal dropdown menus out of overflow containers
   Fixes dropdown being clipped inside .table-responsive
   ========================================================= */
(function(){
  let active = null;

  function cleanup(){
    if (!active) return;

    window.removeEventListener('resize', active.onEnv);
    document.removeEventListener('scroll', active.onEnv, true);

    const { menu, parent } = active;
    if (menu && parent && parent.isConnected) {
      menu.classList.remove('dd-portal');
      menu.style.cssText = '';
      parent.appendChild(menu);
    }
    active = null;
  }

  function positionMenu(toggleEl, menuEl){
    const rect = toggleEl.getBoundingClientRect();
    if (!rect || (rect.width === 0 && rect.height === 0)) return;

    menuEl.style.visibility = 'hidden';
    menuEl.style.display = 'block';

    const mw = menuEl.offsetWidth;
    const mh = menuEl.offsetHeight;

    const vw = document.documentElement.clientWidth;
    const vh = document.documentElement.clientHeight;

    let left = rect.left;
    if (left + mw > vw - 8) left = Math.max(8, rect.right - mw);
    if (left < 8) left = 8;

    let top = rect.bottom + 6;
    if (top + mh > vh - 8) top = Math.max(8, rect.top - mh - 6);

    menuEl.style.left = left + 'px';
    menuEl.style.top  = top  + 'px';
    menuEl.style.visibility = 'visible';
  }

  document.addEventListener('shown.bs.dropdown', function(e){
    const dropdownEl = e.target;
    const toggleEl   = e.relatedTarget || dropdownEl.querySelector('[data-bs-toggle="dropdown"], .dd-toggle');

    if (!dropdownEl || !toggleEl) return;

    const menuEl = dropdownEl.querySelector('.dropdown-menu');
    if (!menuEl) return;

    if (!dropdownEl.closest('.table-responsive, .table-wrap')) return;

    cleanup();

    const parent = menuEl.parentElement;

    menuEl.classList.add('dd-portal');
    document.body.appendChild(menuEl);

    menuEl.style.position  = 'fixed';
    menuEl.style.inset     = 'auto';
    menuEl.style.transform = 'none';
    menuEl.style.margin    = '0';

    positionMenu(toggleEl, menuEl);

    const inst = bootstrap.Dropdown.getOrCreateInstance(toggleEl);
    const onEnv = () => { try { inst.hide(); } catch(_){} };

    window.addEventListener('resize', onEnv);
    document.addEventListener('scroll', onEnv, true);

    active = { menu: menuEl, parent, onEnv };
  }, true);

  document.addEventListener('hidden.bs.dropdown', function(){
    cleanup();
  }, true);
})();
</script>

</body>
</html>
