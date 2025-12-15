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
    /* IMPORTANT: let the header span full width on desktop.
       We removed max-width/centering so actions stay pinned to the far right
       even on ultra-wide monitors. */
    @media (min-width: 992px){
      /* FIX: remove left offset from header; only main should account for sidebar */
      .w3-appbar-inner{ margin-left: 0; }
    }
 
    /* Mobile appbar logo (visible only <992px) */
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
 
    /* Content (max 1240px) */
    /* FIX: main content offsets the sidebar via padding-left (desktop only) */
    .w3-content{
      padding:16px;
      max-width:1280px;
      margin-inline:auto;
      transition:padding .28s ease;
    }
    @media (min-width: 992px){
      .w3-content{ padding-left: calc(16px + var(--w3-rail-w)); } /* beside the sidebar */
    }
 
    /* Overlay (mobile) */
    .w3-overlay{
      position:fixed; top:0; bottom:0; right:0; left:var(--w3-rail-w);
      background:rgba(0,0,0,.45); z-index:1040; opacity:0; visibility:hidden; pointer-events:none;
      transition:opacity .2s ease, visibility .2s ease;
    }
    .w3-overlay.w3-on{opacity:1; visibility:visible; pointer-events:auto}
 
    /* Utilities */
    .rounded-xs{ border-radius:6px; }
 
    /* Mobile */
    @media (max-width: 991px){
      .w3-sidebar{transform:translateX(-100%)}
      .w3-sidebar.w3-on{transform:translateX(0)}
      /* FIX: no large left padding on mobile */
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
    /* RTE (same as assignment instructions) */
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
  /* ===== Force RTE styles (high specificity + important) ===== */
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

/* Focus ring */
#rte:focus, .rte:focus {
  outline: none !important;
  box-shadow: 0 0 0 4px color-mix(in oklab, var(--accent-color,#2563eb) 12%, transparent) !important;
  border-color: var(--accent-color,#2563eb) !important;
}

/* Placeholder — must be the immediate sibling */
#rte.has-content + .rte-ph,
.rte.has-content + .rte-ph { display: none !important; }
.rte-ph { position:absolute; top:12px; left:12px; pointer-events:none; color:var(--muted-color,#9aa3b2) !important; font-size:0.95rem; }

/* toolbar buttons */
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

/* active/pressed visual */
#rte_toolbar [data-cmd].active, #rte_toolbar [data-cmd][aria-pressed="true"] {
  background: var(--accent-color,#2563eb) !important;
  color: #fff !important;
  border-color: transparent !important;
}

/* ensure inner tags keep spacing */
#rte p, #rte div { margin: 0 0 0.75rem !important; }

/* dark mode support */
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

/* Force precedence as last resort */
body #rte, body .rte { /* keeps selector specific */ }

/* small helper for preventing contenteditable from collapsing */
#rte:empty:before, .rte:empty:before {
  content: "" !important; display:block;
}

/* prevent accidental user-select styling conflicts */
#rte, .rte { user-select: text !important; -moz-user-select: text !important; -webkit-user-select: text !important; }


  html.theme-dark .rte{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  </style>
  <style>
/* Force dark mode scrollbar styles */
html.theme-dark ::-webkit-scrollbar {
  width: 8px !important;
}

html.theme-dark ::-webkit-scrollbar-track {
  background: #1e293b !important;
  border-radius: 4px !important;
}

html.theme-dark ::-webkit-scrollbar-thumb {
  background: #475569 !important;
  border-radius: 4px !important;
}

html.theme-dark ::-webkit-scrollbar-thumb:hover {
  background: #64748b !important;
}

/* Apply to specific scrollable containers */
html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar {
  width: 6px !important;
}

html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar-track {
  background: #1e293b !important;
}

html.theme-dark .w3-sidebar-scroll::-webkit-scrollbar-thumb {
  background: #475569 !important;
}
</style>
</head>
<body>
 
<!-- Sidebar -->
<aside id="sidebar" class="w3-sidebar" aria-label="Sidebar">
  <div class="w3-sidebar-head">
    <a href="/admin/dashboard" class="w3-brand">
      <img id="logo" src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
      <span>W3Techiez</span>
    </a>
  </div>
 
  <div class="w3-sidebar-scroll">
    <!-- Overview -->
    <div class="w3-nav-section">
      <div class="w3-section-title text"><i class="fa-solid  fa-chart-simple"></i> OVERVIEW</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu" aria-label="Overview">
      <a href="/admin/dashboard" class="w3-link"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
    </nav>
 
    <!-- Academics -->
    <div class="w3-nav-section">
      <div class="w3-section-title"><i class="fa-solid fa-graduation-cap"></i> ACADEMICS</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu" aria-label="Academics">
      <div class="w3-group">
        <a href="#" class="w3-link w3-toggle" data-target="sm-courses" aria-expanded="false">
          <i class="fa-solid fa-book-open"></i><span>Courses</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="sm-courses" class="w3-submenu" role="group" aria-label="Courses submenu">
          
          <a href="/admin/courses/manage" class="w3-link">All Courses</a>
          <a href="/admin/courses/create" class="w3-link" style="display:none">Create Course</a>
           <a href="/admin/LandingPage/categories/manage" class="w3-link">Categories</a>
          <a href="/admin/courses" class="w3-link">Running Courses</a>
          <a href="/admin/batches/manage" class="w3-link">Batches</a>
        </div>
      </div>

       <div class="w3-group">
        <a href="#" class="w3-link w3-toggle" data-target="sm-course-modules" aria-expanded="false">
          <i class="fa-solid fa-layer-group"></i><span>Course Modules</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="sm-course-modules" class="w3-submenu" role="group" aria-label="Course Modules submenu">
          <a href="/admin/coursesModule/manage" class="w3-link">All Course Modules</a>
        </div>

      </div> 
       <!-- Assignments Group Menu - Added -->
      <div class="w3-group">
        <a href="#" class="w3-link w3-toggle" data-target="sm-assignments" aria-expanded="false">
          <i class="fa-solid fa-file-lines"></i><span>Assignments</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="sm-assignments" class="w3-submenu" role="group" aria-label="Assignments submenu">
          <a href="/admin/assignments/create" class="w3-link">Create Assignment</a>
          <a href="/admin/assignments/manage" class="w3-link">All Assignments</a>

        </div>
      </div>

            <!-- Study Materials Group Menu - Added -->
      <div class="w3-group">
        <a href="#" class="w3-link w3-toggle" data-target="sm-study-materials" aria-expanded="false">
          <i class="fa-solid fa-book"></i><span>Study Materials</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="sm-study-materials" class="w3-submenu" role="group" aria-label="Study Materials submenu">
          <a href="/admin/course/studyMaterial/create" class="w3-link">Create Study Material</a>
          <a href="/admin/course/studyMaterial/manage" class="w3-link">All Study Materials</a>
        </div>
      </div>
    </nav>
    <!-- Exams Section -->
<div class="w3-nav-section">
  <div class="w3-section-title">
    <i class="fa-solid fa-file-circle-check"></i> EXAMS
  </div>
  <div class="w3-section-rule"></div>
</div>

<nav class="w3-menu" aria-label="Exams">

  <!-- Quiz Group Menu -->
  <div class="w3-group">
        <a href="#" class="w3-link w3-toggle" data-target="sm-quiz" aria-expanded="false">
          <i class="fa-solid fa-pen-to-square"></i><span>Quiz</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="sm-quiz" class="w3-submenu" role="group" aria-label="Quiz submenu">
          <a href="/admin/quizz/manage" class="w3-link">All Quizzes</a>
          <a href="/admin/quizz/create" class="w3-link">Create Quiz</a>
        </div>
      </div>
  <!-- Coding Tests Group -->
<div class="w3-group">
  <a href="#" class="w3-link w3-toggle" data-target="sm-coding-tests" aria-expanded="false">
    <i class="fa-solid fa-code"></i><span>Coding Tests</span>
    <i class="fa fa-chevron-down w3-chev"></i>
  </a>

  <div id="sm-coding-tests" class="w3-submenu" role="group" aria-label="Coding Tests submenu">
    <a href="/admin/topic/manage" class="w3-link">Manage Topics</a>
    <a href="/admin/topic/module/manage" class="w3-link">Manage Topic Modules</a>
  </div>
</div>

</nav>

    <!-- Users (section header + menu) -->
<!-- USERS SECTION -->
<div class="w3-nav-section">
  <div class="w3-section-title">
    <i class="fa-solid fa-users"></i>
    <span class="ms-1">USERS</span>
  </div>
  <div class="w3-section-rule"></div>
</div>

<nav class="w3-menu" aria-label="Users">
  <a href="/admin/users/manage" class="w3-link">
    <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
    <span>Users</span>
  </a>
</nav>

<!-- Privileges-->
<div class="w3-nav-section">
  <div class="w3-section-title">
    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
    <span class="ms-1">PRIVILEGES</span>
  </div>
  <div class="w3-section-rule"></div>
</div>

<nav class="w3-menu" aria-label="Privileges">
  <a href="/admin/module/manage" class="w3-link">
    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
    <span>Modules</span>
  </a>

  <a href="/admin/privilege/manage" class="w3-link">
    <i class="fa-solid fa-key" aria-hidden="true"></i>
    <span>Privileges</span>
  </a>
</nav>

     <!-- Landing Page Setting -->
    <div class="w3-nav-section">
      <div class="w3-section-title">
        <i class="fa-solid fa-earth-asia"></i>
        <span class="ms-1">LANDING PAGE SETTING</span>
      </div>
      <div class="w3-section-rule"></div>
    </div>

    <nav class="w3-menu" aria-label="Landing Page Setting">
      <div class="w3-group">
        <a href="#" class="w3-link w3-toggle" data-target="sm-landingpage" aria-expanded="false">
          <i class="fa-solid fa-globe"></i><span>Landing Page</span>
          <i class="fa fa-chevron-down w3-chev"></i>
        </a>
        <div id="sm-landingpage" class="w3-submenu" role="group" aria-label="Landing Page submenu">
          <a href="/admin/LandingPage/updates/manage" class="w3-link">Updates</a>
          <a href="/admin/LandingPage/contacts/manage" class="w3-link">Contacts</a>
          <a href="/admin/LandingPage/hero-images/manage" class="w3-link">Hero Images</a>
           <a href="/admin/featured/courses/manage" class="w3-link">Featured Courses</a>

         
        </div>
      </div>
    </nav>

    <!-- Operations -->
    <div class="w3-nav-section">
      <div class="w3-section-title"><i class="fa-solid fa-screwdriver-wrench"></i> OPERATIONS</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu" aria-label="Operations">
      <!-- Notices Group Menu -->
<div class="w3-group">
  <a href="#" class="w3-link w3-toggle" data-target="sm-notices" aria-expanded="false">
    <i class="fa-solid fa-bullhorn"></i><span>Notices</span>
    <i class="fa fa-chevron-down w3-chev"></i>
  </a>
  <div id="sm-notices" class="w3-submenu" role="group" aria-label="Notices submenu">
    <a href="/admin/notice/manage" class="w3-link">All Notices</a>
    <a href="/admin/notice/create" class="w3-link">Create Notice</a>
  </div>
</div>

 
      <a href="/admin/mailers/manage" class="w3-link"><i class="fa-solid fa-gear"></i><span>Mailer</span></a>
    </nav>
 
    <!-- Account (visible only on small screens) -->
    <div class="w3-nav-section d-lg-none">
      <div class="w3-section-title"><i class="fa-solid fa-user"></i> ACCOUNT</div>
      <div class="w3-section-rule"></div>
    </div>
    <nav class="w3-menu d-lg-none" aria-label="Account">
      <a href="/profile" class="w3-link"><i class="fa fa-id-badge"></i><span>Profile</span></a>
      <a href="/settings" class="w3-link"><i class="fa fa-gear"></i><span>Settings</span></a>
    </nav>
  </div>
 
  <div class="w3-sidebar-foot">
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
    <a href="/admin/dashboard" class="w3-app-logo d-lg-none">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
      <span>W3Techiez</span>
    </a>
 
    <strong class="ms-1 d-none d-lg-inline" style="font-family:var(--font-head);color:var(--ink)">
      @yield('title','W3Techiez Admin')
    </strong>
 
    <div class="ms-auto d-flex align-items-center gap-2">
      <!-- Theme toggle (desktop only) -->
      <button id="btnTheme" class="w3-icon-btn js-theme-btn d-none d-lg-inline-grid" aria-label="Toggle theme" title="Toggle theme">
        <i class="fa-regular fa-moon" id="themeIcon"></i>
      </button>
 
      <!-- Alerts -->
      <div class="dropdown">
        <a href="#" class="w3-icon-btn" id="alertsMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Alerts" title="Alerts">
          <i class="fa-regular fa-bell"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-2 shadow" style="min-width:320px">
          <div class="d-flex align-items-center justify-content-between px-2 mb-2">
            <strong>Notifications</strong>
            <a class="text-muted" href="/admin/notifications">View all</a>
          </div>
          <div class="w3-note rounded-xs">
            <div class="small"><strong>Schedule update</strong> — Lab ME-302 moved to Thu 11 AM.</div>
          </div>
        </div>
      </div>
 
      <!-- User (desktop only) -->
      <div class="dropdown d-none d-lg-block">
        <a href="#" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 px-3" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-regular fa-user"></i>
          <span id="userRoleLabel" class="d-none d-xl-inline">Admin</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="dropdown-header">Account</li>
          <li><a class="dropdown-item" href="/profile"><i class="fa fa-id-badge me-2"></i>Profile</a></li>
          <li><a class="dropdown-item" href="/settings"><i class="fa fa-gear me-2"></i>Settings</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="fa fa-right-from-bracket me-2"></i>Logout</a></li>
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
 
  // ===== Submenus
  document.querySelectorAll('.w3-toggle').forEach(tg => {
    tg.addEventListener('click', (e) => {
      e.preventDefault();
      const id = tg.dataset.target;
      const el = document.getElementById(id);
      const open = el.classList.toggle('w3-open');
      tg.classList.toggle('w3-open', open);
      tg.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
 
  // ===== Active link + open parent
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
 
  // ===== Role label ("Super Admin")
  const roleLabelEl = document.getElementById('userRoleLabel');
  function titleizeRole(r){
    if (!r) return 'Admin';
    return r.replace(/_/g,' ')
            .replace(/\b\w/g, c => c.toUpperCase());
  }
  const roleFromStorage = sessionStorage.getItem('role') || localStorage.getItem('role');
  roleLabelEl && (roleLabelEl.textContent = titleizeRole(roleFromStorage) || 'Super Admin');
 
  // ===== Logout with SweetAlert2 - CHANGED REDIRECTION TO HOME PAGE
  const API_LOGOUT = '/api/auth/logout';      // protected by checkRole
  const LOGIN_PAGE = '/';  // CHANGED: Redirect to home page instead of /auth/login
 
  function getBearerToken(){
    return sessionStorage.getItem('token') || localStorage.getItem('token') || null;
  }
  function clearAuthStorage(){
    try { sessionStorage.removeItem('token'); } catch(e){}
    try { sessionStorage.removeItem('role'); } catch(e){}
    try { localStorage.removeItem('token'); } catch(e){}
    try { localStorage.removeItem('role'); } catch(e){}
  }
 
  async function performLogout(){
    const token = getBearerToken();
 
    // Confirm
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
 
    // Try API (if we have a token)
    let ok = false;
    if (token){
      try{
        const res = await fetch(API_LOGOUT, {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
          body: '' // no payload
        });
        ok = res.ok;
      }catch(e){ ok = false; }
    }
 
    // Always clear local/session regardless of API result
    clearAuthStorage();
 
    // Feedback
    await Swal.fire({
      title: ok ? 'Logged out' : 'Signed out locally',
      text: ok ? 'See you soon 👋' : 'Your session was cleared on this device.',
      icon: ok ? 'success' : 'info',
      timer: 1200,
      showConfirmButton: false
    });
 
    // Redirect to home page
    window.location.replace(LOGIN_PAGE);
  }
 
  document.getElementById('logoutBtn')?.addEventListener('click', (e) => { e.preventDefault(); performLogout(); });
  document.getElementById('logoutBtnSidebar')?.addEventListener('click', (e) => { e.preventDefault(); performLogout(); });
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

    // Ensure menu is measurable
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

  // Use SHOWN so bootstrap already applied "show" class (sizes are correct)
  document.addEventListener('shown.bs.dropdown', function(e){
    const dropdownEl = e.target;                 // .dropdown wrapper
    const toggleEl   = e.relatedTarget           // the actual button/link
      || dropdownEl.querySelector('[data-bs-toggle="dropdown"], .dd-toggle');

    if (!dropdownEl || !toggleEl) return;

    const menuEl = dropdownEl.querySelector('.dropdown-menu');
    if (!menuEl) return;

    // Only portal dropdowns inside scroll/clip areas
    if (!dropdownEl.closest('.table-responsive, .table-wrap')) return;

    cleanup();

    const parent = menuEl.parentElement;

    // Move menu to body
    menuEl.classList.add('dd-portal');
    document.body.appendChild(menuEl);

    // Reset any popper positioning
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