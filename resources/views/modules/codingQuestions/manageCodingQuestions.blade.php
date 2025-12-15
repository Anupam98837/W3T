{{-- resources/views/admin/questions/index.blade.php --}}
{{-- Tabbed Admin: Code Questions --}}

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<style>
  /* Layout guards */
  html, body { width:100%; max-width:100%; overflow-x:hidden; }
  .layout, .right-panel, .main-content { overflow-x:hidden; }

  /* Header */
  .page-indicator{
    display:inline-flex;align-items:center;gap:8px;
    background: var(--bg-body);
    border:1px solid var(--border-color);
    border-radius:var(--radius-md);
    padding:10px 12px;
    /* box-shadow:var(--shadow-sm); */
    color: var(--text-color);
  }
  .page-indicator i{color:var(--primary-color);}
  .page-sub{ color: var(--text-muted); font-size: 12px; }

  /* Tabs */
  .nav-tabs .nav-link{ border:1px solid var(--border-color); border-bottom:none; margin-right:6px; }
  .nav-tabs .nav-link.active{ background:#fff; border-bottom-color:#fff; }

  /* Toolbar */
  .q-toolbar{
    display:flex;gap:10px;justify-content:space-between;align-items:center;margin:12px 0;flex-wrap:wrap;
  }
  .q-toolbar .left,.q-toolbar .right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

  /* 2-column shell */
  .q-wrap{ display:grid; grid-template-columns: 330px 1fr; gap:14px; }
  @media (max-width: 992px){ .q-wrap{ grid-template-columns: 1fr; } }

  /* Left list */
  .q-list{
    background: var(--light-color);
    border:1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow:hidden; display:flex; flex-direction:column; min-height: 60vh;
  }
  .q-list-head{ padding:10px; border-bottom:1px solid var(--border-color); display:flex; gap:8px; align-items:center; }
  .q-list-body{ flex:1; overflow:auto; }
  .q-item{
    display:flex; align-items:start; gap:8px;
    padding:10px 12px; border-bottom:1px solid var(--border-color);
    cursor:pointer; background:transparent; transition: background .1s ease;
  }
  .q-item:hover{ background: var(--bg-body); }
  .q-item.active{ background: rgba(99,102,241,.08); }
  .q-item .drag{ cursor:grab; opacity:.75; padding-top:2px; }
  .q-item-title{ font-weight:600; font-size:13px; color: var(--text-color); }
  .q-item-sub{ font-size:12px; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 100%; }

  /* Right editor */
  .q-editor{ background: var(--light-color); border:1px solid var(--border-color); border-radius: var(--radius-md); overflow:hidden; }
  .q-editor-head{ padding:10px 12px; border-bottom:1px solid var(--border-color); display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
  .q-editor-body{ padding:14px; }

  .tiny{ font-size:12px; }
  .form-help{ font-size:12px; color:#6b7280; }
  .text-muted{ color:#6b7280; }

  .chip{ display:inline-flex; align-items:center; gap:6px; padding:2px 8px; border:1px solid var(--border-color);
    border-radius:999px; font-size:12px; background:#fff; }
  .chip .x{ cursor:pointer; opacity:.75; }

  .card-lite{ border:1px solid var(--border-color); border-radius:10px; padding:10px; background:#fff; }
  .card-lite h6{ margin:0 0 8px 0; font-weight:700; font-size:13px; }
  .grid-2{ display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
  .grid-3{ display:grid; grid-template-columns: repeat(3,1fr); gap:12px; }
  .grid-auto{ display:grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap:12px; }

  /* Editors (generic) */
  .ce-text-toolbar { display:flex; flex-wrap:wrap; gap:.5em; align-items:center; margin-bottom:6px; }
  .ce-text-toolbar button, .ce-text-toolbar select, .ce-text-toolbar input[type="color"]{
    margin-right:4px; padding:4px 8px; font-size:13px; border:1px solid var(--border-color);
    background:#fff; border-radius:4px; cursor:pointer;
  }
  .ce-text-toolbar .sep{ width:1px; height:22px; background:var(--border-color); margin:0 4px; }
  .ce-text-area{
    border:1px solid var(--border-color); border-radius:6px; min-height:220px; padding:8px; outline:none; background:#fff;
  }
  .ce-text-area:focus{ box-shadow:0 0 0 3px rgba(99,102,241,.15); }

  /* Language card */
  .lang-card,.dialect-card{ border:1px solid var(--border-color); border-radius:10px; padding:10px; background:#fff; margin-bottom:12px; }
  .lang-card .head,.dialect-card .head{ display:flex; gap:8px; align-items:center; }
  .lang-card .drag{ cursor:grab; opacity:.65; }
  .lang-card .row-actions,.dialect-card .row-actions{ margin-left:auto; display:flex; gap:6px; }
  .lang-card details summary,.dialect-card details summary{ cursor:pointer; }

  /* Tests */
  .test-row{ border:1px dashed var(--border-color); border-radius:8px; padding:8px; margin-bottom:8px; background:#fafafa; }
  .test-row .drag{ cursor:grab; opacity:.65; }

  /* Info buttons */
  .i-btn{
    border:1px solid var(--border-color);
    border-radius:999px;
    width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;
    background:#fff; font-size:12px; margin-left:6px; cursor:pointer;
    transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease, color .15s ease;
  }
  .i-btn:hover{ background:#f3f4f6; }
  .i-btn:focus{ outline:0; box-shadow:0 0 0 3px rgba(99,102,241,.25); }

  /* Pretty details/accordion */
  details{ border:1px dashed var(--border-color); border-radius:8px; padding:8px; background:#fff; }
  details > summary{ list-style:none; font-weight:600; display:flex; align-items:center; gap:8px; color: var(--text-color); }
  details > summary::before{ content: "▸"; transition: transform .15s ease; font-size: 12px; color: var(--text-muted); }
  details[open] > summary::before{ transform: rotate(90deg); }

  /* ===== Layout guards (duplicate-safe) ===== */
  html, body {
    width:100%;
    max-width:100%;
    overflow-x:hidden;
  }
  .layout, .right-panel, .main-content {
    overflow-x:hidden;
  }

  /* ===== Page head ===== */
  .page-head{
    margin-bottom: 10px;
  }
  .page-indicator{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background: var(--light-color, #ffffff);
    border:1px solid var(--border-color, #e5e7eb);
    border-radius:999px;
    padding:8px 14px;
    box-shadow:0 4px 12px rgba(15,23,42,.08);
    color: var(--text-color, #0f172a);
  }
  .page-indicator i{
    color:var(--primary-color, #6366f1);
    font-size: 14px;
  }
  .page-indicator strong{
    font-size: 14px;
    font-weight: 600;
  }
  .page-sub{
    color: var(--text-muted, #6b7280);
    font-size: 12px;
    display:flex;
    align-items:center;
    gap:6px;
  }

  /* ===== Tabs ===== */
  .nav-tabs{
    border-color: var(--border-color, #e5e7eb);
    margin-top: 12px;
  }
  .nav-tabs .nav-link{
    border:1px solid transparent;
    border-radius:999px;
    padding:6px 14px;
    font-size:13px;
    display:inline-flex;
    align-items:center;
    gap:6px;
    color: var(--text-muted, #6b7280);
    background:transparent;
    transition: background .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease;
  }
  .nav-tabs .nav-link i{
    font-size: 13px;
  }
  .nav-tabs .nav-link:hover{
    background:rgba(99,102,241,.06);
    color:var(--primary-color, #6366f1);
  }
  .nav-tabs .nav-link.active{
    background:#fff;
    color:var(--primary-color, #6366f1);
    border-color: var(--border-color, #e5e7eb);
    box-shadow:0 4px 10px rgba(15,23,42,.06);
  }

  /* ===== Toolbar ===== */
  .q-toolbar{
    display:flex;
    gap:10px;
    justify-content:space-between;
    align-items:center;
    margin:16px 0 12px 0;
    flex-wrap:wrap;
  }
  .q-toolbar .left,
  .q-toolbar .right{
    display:flex;
    gap:8px;
    align-items:center;
    flex-wrap:wrap;
  }
  .q-toolbar .input-group .form-control{
    font-size:13px;
    border-radius:999px;
  }
  .q-toolbar .input-group-text{
    border-radius:999px 0 0 999px;
    font-size:12px;
  }
  .q-toolbar .btn{
    font-size:13px;
    border-radius:999px;
    padding:6px 14px;
  }
  .q-toolbar .btn-light{
    border-color: var(--border-color, #e5e7eb);
    background:#f9fafb;
  }
  .q-toolbar .btn-light:hover{
    background:#f3f4f6;
  }

  /* ===== 2-column shell ===== */
  .q-wrap{
    display:grid;
    grid-template-columns: 320px minmax(0,1fr);
    gap:14px;
    align-items:flex-start;
  }
  @media (max-width: 992px){
    .q-wrap{
      grid-template-columns: 1fr;
    }
  }

  /* ===== Left list refined ===== */
  .q-list{
    background: radial-gradient(circle at top left, rgba(129,140,248,.08), transparent 55%) var(--light-color, #f9fafb);
    border:1px solid var(--border-color, #e5e7eb);
    border-radius: 14px;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    min-height: 60vh;
    box-shadow:0 14px 35px rgba(15,23,42,.10);
  }
  .q-list-head{
    padding:10px 12px;
    border-bottom:1px solid var(--border-color, #e5e7eb);
    display:flex;
    gap:8px;
    align-items:center;
    background:rgba(15,23,42,.02);
    position: sticky;
    top:0;
    z-index:2;
  }
  .q-list-head .tiny{
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.08em;
  }
  .q-list-body{
    flex:1;
    overflow:auto;
  }

  .q-item{
    display:flex;
    align-items:flex-start;
    gap:8px;
    padding:9px 12px;
    border-bottom:1px solid rgba(148,163,184,.25);
    cursor:pointer;
    background:transparent;
    transition: background .12s ease, box-shadow .12s ease, transform .08s ease, border-left .12s ease;
    position:relative;
  }
  .q-item:last-child{
    border-bottom:none;
  }
  .q-item:hover{
    background: rgba(148,163,184,.08);
  }
  .q-item.active{
    background: rgba(129,140,248,.16);
    border-left:3px solid var(--primary-color, #6366f1);
  }
  .q-item .drag{
    cursor:grab;
    opacity:.7;
    padding-top:3px;
    color:#9ca3af;
  }
  .q-item .drag i{
    font-size: 12px;
  }
  .q-item-title{
    font-weight:600;
    font-size:13px;
    color: var(--text-color, #0f172a);
  }
  .q-item-sub{
    font-size:11px;
    color:#6b7280;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width: 100%;
  }
  .q-item .badge{
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    border-radius:999px;
    padding:4px 8px;
  }

  /* ===== Right editor refined ===== */
  .q-editor{
    background: radial-gradient(circle at top left, rgba(96,165,250,.10), transparent 55%) var(--light-color, #ffffff);
    border:1px solid var(--border-color, #e5e7eb);
    border-radius: 14px;
    overflow:hidden;
    box-shadow:0 18px 45px rgba(15,23,42,.12);
  }
  .q-editor-head{
    padding:10px 14px;
    border-bottom:1px solid var(--border-color, #e5e7eb);
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    align-items:center;
    backdrop-filter: blur(12px);
    background:linear-gradient(to right, rgba(15,23,42,.02),rgba(129,140,248,.10));
  }
  .q-editor-body{
    padding:16px;
  }

  .tiny{
    font-size:12px;
  }
  .form-help{
    font-size:12px;
    color:#6b7280;
  }
  .text-muted{
    color:#6b7280;
  }

  /* ===== Chips & pill elements ===== */
  .chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:2px 9px;
    border:1px solid var(--border-color, #e5e7eb);
    border-radius:999px;
    font-size:11px;
    background:#ffffff;
    color: var(--text-muted, #4b5563);
    box-shadow:0 1px 3px rgba(15,23,42,.08);
  }
  .chip i{
    font-size: 11px;
  }
  .chip .x{
    cursor:pointer;
    opacity:.75;
    font-size:11px;
  }
  .chip .x:hover{
    opacity:1;
  }

  /* ===== Cards ===== */
  .card-lite{
    border:1px solid var(--border-color, #e5e7eb);
    border-radius:12px;
    padding:12px 12px 10px 12px;
    background:#ffffff;
    box-shadow:0 6px 18px rgba(15,23,42,.05);
  }
  .card-lite h6{
    margin:0 0 10px 0;
    font-weight:700;
    font-size:13px;
    display:flex;
    align-items:center;
    gap:6px;
  }
  .card-lite h6::before{
    content:'';
    width:3px;
    height:14px;
    border-radius:999px;
    background:var(--primary-color, #6366f1);
  }

  .grid-2{
    display:grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap:12px;
  }
  .grid-3{
    display:grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap:12px;
  }
  .grid-auto{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(240px,1fr));
    gap:12px;
  }

  @media (max-width: 768px){
    .grid-2,
    .grid-3{
      grid-template-columns: 1fr;
    }
  }

  /* ===== WYSIWYG toolbar & editor (for desc/solution) ===== */
  .ce-text-toolbar{
    display:flex;
    flex-wrap:wrap;
    gap:.4em;
    align-items:center;
    margin-bottom:6px;
    padding:4px 6px;
    border-radius:8px;
    background:linear-gradient(to right,rgba(249,250,251,1),rgba(241,245,249,1));
    border:1px solid var(--border-color, #e5e7eb);
  }
  .ce-text-toolbar button,
  .ce-text-toolbar select,
  .ce-text-toolbar input[type="color"]{
    margin-right:0;
    padding:4px 7px;
    font-size:12px;
    border:1px solid var(--border-color, #e5e7eb);
    background:#fff;
    border-radius:6px;
    cursor:pointer;
    line-height:1.2;
    min-height:26px;
    display:inline-flex;
    align-items:center;
    gap:4px;
    transition: background .12s ease, box-shadow .12s ease, transform .05s ease, border-color .12s ease;
  }
  .ce-text-toolbar button i{
    font-size:12px;
  }
  .ce-text-toolbar button:hover,
  .ce-text-toolbar select:hover{
    background:#eef2ff;
    border-color:rgba(99,102,241,.5);
    box-shadow:0 0 0 1px rgba(129,140,248,.25);
  }
  .ce-text-toolbar .sep{
    width:1px;
    height:20px;
    background:var(--border-color, #e5e7eb);
    margin:0 2px;
  }
  .ce-text-area{
    border:1px solid var(--border-color, #e5e7eb);
    border-radius:9px;
    min-height:160px;
    padding:10px;
    outline:none;
    background:#ffffff;
    font-size:13px;
    line-height:1.5;
    overflow:auto;
  }
  .ce-text-area:focus{
    box-shadow:0 0 0 2px rgba(129,140,248,.25);
    border-color:rgba(99,102,241,.7);
  }
  .ce-text-area[placeholder]:empty:before{
    content: attr(placeholder);
    color:#9ca3af;
    font-style:italic;
  }

  /* ===== Language / Dialect cards ===== */
  .lang-card,
  .dialect-card{
    border:1px solid var(--border-color, #e5e7eb);
    border-radius:12px;
    padding:10px 10px 12px 10px;
    background:#ffffff;
    margin-bottom:12px;
    box-shadow:0 8px 20px rgba(15,23,42,.06);
  }
  .lang-card .head,
  .dialect-card .head{
    display:flex;
    gap:8px;
    align-items:center;
  }
  .lang-card .drag,
  .dialect-card .drag{
    cursor:grab;
    opacity:.65;
    color:#9ca3af;
  }
  .lang-card .drag i,
  .dialect-card .drag i{
    font-size:12px;
  }
  .lang-card .row-actions,
  .dialect-card .row-actions{
    margin-left:auto;
    display:flex;
    gap:6px;
    align-items:center;
  }
  .lang-card details summary,
  .dialect-card details summary{
    cursor:pointer;
  }

  /* ===== Tests ===== */
  .test-row{
    border:1px dashed var(--border-color, #e5e7eb);
    border-radius:10px;
    padding:9px;
    margin-bottom:8px;
    background:#f9fafb;
    transition: box-shadow .12s ease, border-color .12s ease, background .12s ease;
  }
  .test-row:hover{
    border-color:rgba(129,140,248,.6);
    box-shadow:0 10px 20px rgba(15,23,42,.1);
    background:#f3f4ff;
  }
  .test-row .drag{
    cursor:grab;
    opacity:.7;
    color:#9ca3af;
  }
  .test-row .drag i{
    font-size:12px;
  }

  /* ===== Info buttons ===== */
  .i-btn{
    border:1px solid var(--border-color, #e5e7eb);
    border-radius:999px;
    width:20px;
    height:20px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:#fff;
    font-size:11px;
    margin-left:6px;
    cursor:pointer;
    transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease, color .15s ease, transform .05s ease;
  }
  .i-btn:hover{
    background:#eef2ff;
    border-color:rgba(99,102,241,.7);
    box-shadow:0 0 0 2px rgba(129,140,248,.25);
    color:var(--primary-color, #6366f1);
    transform: translateY(-0.5px);
  }
  .i-btn:focus{
    outline:0;
    box-shadow:0 0 0 3px rgba(99,102,241,.35);
  }

  /* ===== Pretty details/accordion ===== */
  details{
    border:1px dashed var(--border-color, #e5e7eb);
    border-radius:10px;
    padding:8px;
    background:#ffffff;
  }
  details > summary{
    list-style:none;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:6px;
    color: var(--text-color, #0f172a);
    font-size:12px;
  }
  details[open] > summary::before{
    transform: rotate(90deg);
  }

  /* ===== Form controls tweak ===== */
  .form-label{
    font-size:12px;
    font-weight:500;
    color:var(--text-muted, #4b5563);
    margin-bottom:3px;
  }
  .form-control,
  .form-select{
    font-size:13px;
    border-radius:9px;
  }
  .form-control:focus,
  .form-select:focus{
    border-color:rgba(99,102,241,.7);
    box-shadow:0 0 0 2px rgba(129,140,248,.25);
  }

  /* ===== Dark mode (refined) ===== */
  html.theme-dark .page-indicator{
    background:#020617;
    border-color:rgba(148,163,184,.4);
    box-shadow:0 14px 40px rgba(15,23,42,.9);
  }
  html.theme-dark .page-sub{
    color:#9ca3af;
  }
  html.theme-dark .nav-tabs .nav-link{
    color:#9ca3af;
  }
  html.theme-dark .nav-tabs .nav-link.active{
    background:#020617;
    border-color:rgba(148,163,184,.5);
    box-shadow:0 10px 28px rgba(15,23,42,1);
  }

  html.theme-dark .q-item-sub{
    color:#93a4b8;
  }
  html.theme-dark .ce-text-toolbar button,
  html.theme-dark .ce-text-toolbar select,
  html.theme-dark .ce-text-toolbar input[type="color"]{
    background:#020617;
    color:#e5e7eb;
    border-color:rgba(148,163,184,.4);
  }
  html.theme-dark .ce-text-toolbar{
    background:linear-gradient(to right,rgba(15,23,42,1),rgba(15,23,42,.9));
    border-color:rgba(148,163,184,.5);
  }
  html.theme-dark .ce-text-area{
    background:#020617;
    color:#e5e7eb;
    border-color:rgba(148,163,184,.5);
  }
  html.theme-dark .ce-text-area[placeholder]:empty:before{
    color:#64748b;
  }

  html.theme-dark .chip{
    background: rgba(15,23,42,1);
    border-color: rgba(148,163,184,.6);
    color: #e5e7eb;
  }
  html.theme-dark .chip i{
    color:#a9b7ff;
  }
  html.theme-dark .chip strong{
    color:#fff;
  }

  html.theme-dark .q-editor,
  html.theme-dark .q-list{
    background:#020617;
    border-color:rgba(148,163,184,.5);
    box-shadow:0 18px 45px rgba(0,0,0,1);
  }
  html.theme-dark .q-editor-head{
    background:linear-gradient(to right,rgba(15,23,42,1),rgba(30,64,175,.6));
    border-bottom-color:rgba(148,163,184,.5);
  }
  html.theme-dark .q-item:hover{
    background: rgba(15,23,42,.8);
  }
  html.theme-dark .card-lite,
  html.theme-dark .lang-card,
  html.theme-dark .dialect-card{
    background:#020617;
    border-color:rgba(148,163,184,.5);
    box-shadow:0 12px 30px rgba(0,0,0,1);
  }
  html.theme-dark .test-row{
    background:#020617;
    border-color:rgba(148,163,184,.6);
  }
  html.theme-dark .test-row:hover{
    background:#020617;
    box-shadow:0 14px 30px rgba(0,0,0,1);
  }
  html.theme-dark details{
    background:#020617;
    border-color:rgba(148,163,184,.6);
  }
  html.theme-dark .form-label{
    color:#cbd5f5;
  }
  html.theme-dark .form-control,
  html.theme-dark .form-select{
    background:#020617;
    color:#e5e7eb;
    border-color:rgba(148,163,184,.6);
  }
  html.theme-dark .form-control:focus,
  html.theme-dark .form-select:focus{
    border-color:rgba(129,140,248,.9);
    box-shadow:0 0 0 2px rgba(79,70,229,.7);
  }
  html.theme-dark .i-btn{
    background:#020617;
    border-color:rgba(148,163,184,.6);
    color:#e5e7eb;
  }
  html.theme-dark .i-btn:hover{
    background:#111827;
  }
</style>
@endpush

@php
  $topicId   = $topic_id   ?? request()->get('topic_id');
  $moduleId  = $module_id  ?? request()->get('module_id');
  $topicName = $topic_name ?? 'Topic';
  $moduleName= $module_name?? 'Module';
@endphp

<div class="page-head">
  <div class="page-indicator">
    <i class="fa-solid fa-circle-question"></i>
    <strong>Manage Questions</strong>
  </div>
  <div class="page-sub mt-1">
    <i class="fa fa-layer-group me-1"></i> {{ $topicName }}
    <span class="mx-2">/</span>
    <i class="fa fa-rectangle-list me-1"></i> {{ $moduleName }}
  </div>
</div>

<ul class="nav nav-tabs mt-3" id="qTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="tab-code" data-bs-toggle="tab" data-bs-target="#pane-code" type="button" role="tab" aria-controls="pane-code" aria-selected="true">
      <i class="fa-solid fa-code me-1"></i> Code Questions
    </button>
  </li>
</ul>

<div class="tab-content">
  {{-- ======================= CODE TAB ======================= --}}
  <div class="tab-pane fade show active" id="pane-code" role="tabpanel" aria-labelledby="tab-code">
    <div class="q-toolbar">
      <div class="left">
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-search"></i></span>
          <input id="searchInput" type="search" class="form-control" placeholder="Search by title, slug, status, difficulty…">
        </div>
      </div>
      <div class="right">
        <button class="btn btn-primary" id="btnAdd"><i class="fa fa-plus me-2"></i>Add Question</button>
        <button class="btn btn-light" id="btnRefresh"><i class="fa fa-rotate me-1"></i>Refresh</button>
      </div>
    </div>

    <div class="q-wrap">
      {{-- LEFT: LIST --}}
      <aside class="q-list">
        <div class="q-list-head">
          <span class="tiny text-muted">Questions</span>
          <span class="tiny text-muted ms-auto" id="qCount">—</span>
        </div>
        <div class="q-list-body" id="qList">
          <div class="p-3 text-center text-muted tiny">Loading…</div>
        </div>
      </aside>

      {{-- RIGHT: EDITOR --}}
      <section class="q-editor position-relative">
        <div class="q-editor-head">
          <div class="chip"><i class="fa fa-layer-group"></i> Topic: <strong class="ms-1">{{ $topicName }}</strong></div>
          <div class="chip"><i class="fa fa-rectangle-list"></i> Module: <strong class="ms-1">{{ $moduleName }}</strong></div>
          <div class="ms-auto tiny text-muted" id="saveStatus">—</div>
        </div>

        <div class="q-editor-body">
          <form id="qForm" class="needs-validation" novalidate>
            <input type="hidden" id="qid">
            <input type="hidden" id="topic_id"  value="{{ $topicId }}">
            <input type="hidden" id="module_id" value="{{ $moduleId }}">

            {{-- Meta --}}
            <div class="card-lite mb-3">
              <h6>Meta</h6>
              <div class="grid-3">
                <div>
                  <label class="form-label">Title <span class="text-danger">*</span></label>
                  <span class="i-btn" data-i-title="Title" data-i-text="Human-friendly title shown in the admin list and to users. Max 200 chars. Required.">i</span>
                  <input class="form-control" id="title" required maxlength="200" placeholder="e.g., Sum Two Integers">
                  <div class="invalid-feedback">Title is required.</div>
                </div>
                <div>
                  <label class="form-label">Slug</label>
                  <span class="i-btn" data-i-title="Slug" data-i-text="URL-safe identifier. Auto-generated from title if empty; you can edit if needed.">i</span>
                  <input class="form-control" id="slug" maxlength="200" placeholder="auto-slug-from-title">
                </div>
                <div>
                  <label class="form-label">Sort Order</label>
                  <span class="i-btn" data-i-title="Sort Order" data-i-text="Lower numbers appear first in the list. You can also drag items in the left panel.">i</span>
                  <input type="number" id="sort_order" class="form-control" value="0" min="0">
                </div>
              </div>
             <div class="grid-3 mt-2">
  <div>
    <label class="form-label">Status</label>
    <select id="status" class="form-select">
      <option value="active">Active</option>
      <option value="draft">Draft</option>
      <option value="archived">Archived</option>
    </select>
  </div>

  <div>
    <label class="form-label">Difficulty</label>
    <select id="difficulty" class="form-select">
      <option value="easy">Easy</option>
      <option value="medium" selected>Medium</option>
      <option value="hard">Hard</option>
    </select>
  </div>

  <div>
    <label class="form-label">Total Time (min) <span class="text-muted">(optional)</span></label>
    <span class="i-btn"
          data-i-title="Total Time (minutes)"
          data-i-text="Optional time limit for this question. Leave empty for no overall time limit.">
      i
    </span>
    <input type="number" id="total_time_min" class="form-control" min="1" placeholder="e.g., 45">
    <div class="form-help">Leave empty = no time limit.</div>
  </div>
</div>

<div class="mt-2">
  <label class="form-label">Tags (optional)</label>
  <input class="form-control" id="tags" placeholder="e.g., arrays, dp, math">
  <div class="form-help">Comma-separated tags. Optional, for your own search/filter.</div>
</div>


            {{-- Problem Statement with WYSIWYG --}}
            <div class="card-lite mb-3">
              <h6>Problem Statement</h6>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <span class="i-btn" data-i-title="Description" data-i-text="The full problem statement. Supports basic formatting (B, I, U, headings, lists).">i</span>
                <div class="ce-text-toolbar" data-target="#descEditor">
                  <button type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                  <button type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                  <button type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="h1">H1</button>
                  <button type="button" data-cmd="h2">H2</button>
                  <button type="button" data-cmd="p">P</button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="ul"><i class="fa-solid fa-list-ul"></i></button>
                  <button type="button" data-cmd="ol"><i class="fa-solid fa-list-ol"></i></button>
                </div>
                <div id="descEditor"
                     class="ce-text-area"
                     contenteditable="true"
                     data-bind-textarea="#desc"
                     placeholder="Describe the problem…"></div>
                <textarea id="desc" class="form-control d-none" rows="8"></textarea>
              </div>
              <div>
                <label class="form-label">Explanation (optional)</label>
                <span class="i-btn" data-i-title="Explanation" data-i-text="Optional editorial notes or solution outline. Supports basic formatting.">i</span>
                <div class="ce-text-toolbar" data-target="#solutionEditor">
                  <button type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                  <button type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                  <button type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="h1">H1</button>
                  <button type="button" data-cmd="h2">H2</button>
                  <button type="button" data-cmd="p">P</button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="ul"><i class="fa-solid fa-list-ul"></i></button>
                  <button type="button" data-cmd="ol"><i class="fa-solid fa-list-ol"></i></button>
                </div>
                <div id="solutionEditor"
                     class="ce-text-area"
                     contenteditable="true"
                     data-bind-textarea="#solution"
                     placeholder="Explain the approach…"></div>
                <textarea id="solution" class="form-control d-none" rows="6"></textarea>
              </div>
            </div>

            {{-- Checker --}}
            <div class="card-lite mb-3">
              <h6>Checker</h6>
              <div class="grid-3">
                <div>
                  <label class="form-label">Compare Mode</label>
                  <span class="i-btn" data-i-title="Compare Mode" data-i-text="How to compare expected vs actual output.">i</span>
                  <select id="compare_mode" class="form-select">
                    <option value="exact">exact</option>
                    <option value="icase">icase</option>
                    <option value="token">token</option>
                    <option value="float_abs">float_abs</option>
                    <option value="float_rel">float_rel</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Trim Output</label>
                  <span class="i-btn" data-i-title="Trim Output" data-i-text="Removes leading/trailing whitespace before comparing.">i</span>
                  <select id="trim_output" class="form-select">
                    <option value="1" selected>Yes</option>
                    <option value="0">No</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Whitespace Mode</label>
                  <span class="i-btn" data-i-title="Whitespace Mode" data-i-text="trim: strip ends; squash: collapse multiple spaces; none: compare as-is.">i</span>
                  <select id="whitespace_mode" class="form-select">
                    <option value="trim" selected>trim</option>
                    <option value="squash">squash</option>
                    <option value="none">none</option>
                  </select>
                </div>
              </div>
              <div class="grid-2 mt-2">
                <div>
                  <label class="form-label">Float Abs Tol</label>
                  <span class="i-btn" data-i-title="Float Abs Tol" data-i-text="Absolute tolerance for floating comparisons (float_abs).">i</span>
                  <input type="number" step="any" id="float_abs_tol" class="form-control" placeholder="e.g., 1e-6">
                </div>
                <div>
                  <label class="form-label">Float Rel Tol</label>
                  <span class="i-btn" data-i-title="Float Rel Tol" data-i-text="Relative tolerance for floating comparisons (float_rel).">i</span>
                  <input type="number" step="any" id="float_rel_tol" class="form-control" placeholder="e.g., 1e-6">
                </div>
              </div>
              <div class="form-help mt-1">
                These map to DB columns: <code>compare_mode</code>, <code>trim_output</code>, <code>whitespace_mode</code>, <code>float_abs_tol</code>, <code>float_rel_tol</code>.
              </div>
            </div>

            {{-- Languages --}}
            <div class="card-lite mb-3">
              <h6>Languages</h6>
              <div id="langsWrap"></div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAddLang">
                <i class="fa fa-plus me-1"></i>Add Language
              </button>
              <div class="form-help mt-1">
                Each language card includes runtime/cmds, limits, allow/deny and starter snippet.
              </div>
            </div>

            {{-- Tests --}}
            <div class="card-lite mb-3">
              <h6>Tests</h6>
              <div id="testsWrap"></div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAddTest">
                <i class="fa fa-plus me-1"></i>Add Test
              </button>
              <div class="form-help mt-1">
                Drag to reorder. Use <strong>sample</strong> for visible tests and <strong>hidden</strong> for secret tests.
              </div>
            </div>

            {{-- Save/Delete --}}
            <div class="d-flex gap-2">
              <button class="btn btn-primary" type="submit" id="btnSave">
                <i class="fa fa-save me-2"></i>Save
              </button>
              <button class="btn btn-outline-danger" type="button" id="btnDelete">
                <i class="fa fa-trash me-2"></i>Delete
              </button>
            </div>
          </form>
        </div>

      </section>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

@push('scripts')
<script>"use strict";</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ============== tiny helpers ============== */
const _isDark = () => (localStorage.getItem('theme') === 'dark');
const _esc = (s)=> (s??'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
const _debounce=(fn,ms=350)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}};
function _toast(id,msg){ const el=document.getElementById(id); el.querySelector('.toast-body').textContent=msg; (new bootstrap.Toast(el)).show(); }
const _ok = (m)=>_toast('toastSuccess', m), _err=(m)=>_toast('toastError', m);
function _getToken(){ return localStorage.getItem('token') || sessionStorage.getItem('token') || ''; }
function _hdr(){ return { 'Authorization': `Bearer ${_getToken()}` }; }
function _hdrJSON(){ return { ..._hdr(), 'Content-Type':'application/json' }; }
/* IMPORTANT: empty string => null, so nullable validation works */
function _toNum(v){
  if (v === null || v === undefined) return null;
  if (typeof v === 'string' && v.trim() === '') return null;
  const n = Number(v);
  return Number.isFinite(n) ? n : null;
}
function _slugify(s){ return (s||'').toString().trim().toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,200); }

/* ===== WYSIWYG helpers (Description / Explanation) ===== */
function syncEditorsFromTextareas(){
  document.querySelectorAll('[data-bind-textarea]').forEach(ed=>{
    const sel = ed.getAttribute('data-bind-textarea');
    const ta = sel ? document.querySelector(sel) : null;
    if (!ta) return;
    ed.innerHTML = ta.value || '';
  });
}
function syncTextareasFromEditors(){
  document.querySelectorAll('[data-bind-textarea]').forEach(ed=>{
    const sel = ed.getAttribute('data-bind-textarea');
    const ta = sel ? document.querySelector(sel) : null;
    if (!ta) return;
    ta.value = ed.innerHTML.trim();
  });
}
function initEditors(){
  syncEditorsFromTextareas();
  document.querySelectorAll('.ce-text-toolbar').forEach(tb=>{
    const targetSel = tb.getAttribute('data-target');
    const editor = targetSel ? document.querySelector(targetSel) : null;
    if (!editor) return;
    tb.querySelectorAll('button[data-cmd]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const cmd = btn.getAttribute('data-cmd');
        editor.focus();
        if(cmd === 'h1'){ document.execCommand('formatBlock', false, 'H1'); return; }
        if(cmd === 'h2'){ document.execCommand('formatBlock', false, 'H2'); return; }
        if(cmd === 'p'){ document.execCommand('formatBlock', false, 'P'); return; }
        if(cmd === 'ul'){ document.execCommand('insertUnorderedList', false, null); return; }
        if(cmd === 'ol'){ document.execCommand('insertOrderedList', false, null); return; }
        document.execCommand(cmd, false, null);
      });
    });
  });
}

(function(){
  "use strict";
  const TOPIC_ID  = {{ json_encode($topicId) }};
  const MODULE_ID = {{ json_encode($moduleId) }};
  if(!TOPIC_ID || !MODULE_ID){
    document.getElementById('pane-code').innerHTML = `<div class="p-4 text-danger">Missing topic_id or module_id.</div>`;
    return;
  }

  // ===== Presets for dropdowns =====
  // Only basic languages now: C, C++, Java, Python
  const LANGUAGE_OPTIONS = ['c','cpp','java','python'];
  const LANGUAGE_RUNTIMES = {
    python:     ['piston','judge0','dockerlocal'],
    cpp:        ['judge0','dockerlocal','piston'],
    c:          ['judge0','dockerlocal','piston'],
    java:       ['judge0','dockerlocal','piston'],
    javascript: ['piston','judge0','dockerlocal'],
    typescript: ['piston','judge0','dockerlocal'],
    go:         ['judge0','dockerlocal'],
    ruby:       ['piston','dockerlocal'],
    rust:       ['judge0','dockerlocal'],
    php:        ['piston','judge0','dockerlocal'],
    csharp:     ['judge0','dockerlocal'],
    kotlin:     ['judge0','dockerlocal']
  };
  const RUNTIME_FALLBACK = ['piston','judge0','dockerlocal'];
  const runtimeOptionsFor = (lang)=> LANGUAGE_RUNTIMES[lang] || RUNTIME_FALLBACK;

  // ===== API =====
  const API = {
    list:    () => fetch(`/api/coding_questions?topic_id=${TOPIC_ID}&module_id=${MODULE_ID}&per_page=200`, { headers: _hdr() }),
    get:     id => fetch(`/api/coding_questions/${id}`, { headers: _hdr() }),
    create:  payload => fetch('/api/coding_questions', {
                  method:'POST',
                  headers: _hdrJSON(),
                  body: JSON.stringify(payload)
               }),
    update: (id,payload)=> fetch(`/api/coding_questions/${id}`, {
                  method:'PUT',
                  headers: _hdrJSON(),
                  body: JSON.stringify(payload)
               }),
    delete:  id => fetch(`/api/coding_questions/${id}`, {
                  method:'DELETE',
                  headers: _hdr()
               }),
    reorder: order => fetch('/api/coding_questions/reorder', {
                  method:'POST',
                  headers: _hdrJSON(),
                  body: JSON.stringify({ order })
               }),
  };

  // ===== DOM =====
  const qList = document.getElementById('qList');
  const qCount= document.getElementById('qCount');
  const btnRefresh = document.getElementById('btnRefresh');
  const btnAdd = document.getElementById('btnAdd');
  const searchInput = document.getElementById('searchInput');

  const form = document.getElementById('qForm');
  const qid  = document.getElementById('qid');
  const title= document.getElementById('title');
  const slug = document.getElementById('slug');
  const status = document.getElementById('status');
  const difficulty = document.getElementById('difficulty');
  const sort_order = document.getElementById('sort_order');
  const total_time_min = document.getElementById('total_time_min');


  const desc = document.getElementById('desc');
  const solution = document.getElementById('solution');

  // Checker
  const compare_mode    = document.getElementById('compare_mode');
  const trim_output     = document.getElementById('trim_output');
  const whitespace_mode = document.getElementById('whitespace_mode');
  const float_abs_tol   = document.getElementById('float_abs_tol');
  const float_rel_tol   = document.getElementById('float_rel_tol');

  // Tags (simple)
  const tagsInput = document.getElementById('tags');

  // Languages
  const langsWrap  = document.getElementById('langsWrap');
  const btnAddLang = document.getElementById('btnAddLang');

  // Tests
  const testsWrap  = document.getElementById('testsWrap');
  const btnAddTest = document.getElementById('btnAddTest');

  const btnSave = document.getElementById('btnSave');
  const btnDelete = document.getElementById('btnDelete');
  const saveStatus = document.getElementById('saveStatus');

  // WYSIWYG init (description/explanation)
  initEditors();

  // ===== State =====
  let all = [];
  let view = [];
  let currentId = null;

  let langBlocks = [];
  let testRows   = [];

  // ===== List loading =====
  async function loadList(){
    qList.innerHTML = `<div class="p-3 text-center text-muted tiny">Loading…</div>`;
    try{
      const json = await API.list().then(r=>r.json());
      const rows = Array.isArray(json.data?.data) ? json.data.data
                : Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
      all = rows.map((r,i)=>({ ...r, sort_order: r.sort_order ?? i }));
      qCount.textContent = `${all.length} total`;
      applyFilter();
      if (all.length) select(all[0].id); else resetForm();
    }catch(e){
      qList.innerHTML = `<div class="p-3 text-danger tiny">Failed to load</div>`;
    }
  }

  function applyFilter(){
    const q = (searchInput.value||'').toLowerCase().trim();
    view = !q ? [...all] : all.filter(r=>{
      const hay = [r.title, r.slug, r.status, r.difficulty].map(x=>(x||'').toLowerCase()).join(' ');
      return hay.includes(q);
    });
    view.sort((a,b)=> (a.sort_order??0)-(b.sort_order??0) || (a.id-b.id));
    renderList();
  }
  searchInput.addEventListener('input', _debounce(applyFilter, 200));

  function renderList(){
    if(!view.length){
      qList.innerHTML = `
        <div class="p-4 text-center text-muted">
          <i class="fa-regular fa-folder-open fa-2x mb-2"></i>
          <div>No questions found</div>
        </div>`;
      return;
    }

    qList.innerHTML = '';
    view.forEach(row=>{
      const item = document.createElement('div');
      item.className = 'q-item';
      item.dataset.id = row.id;
      item.draggable = true;

      const timePart = (row.total_time_min && Number(row.total_time_min) > 0)
  ? ` • ${row.total_time_min}m`
  : '';

const sub =
  (row.slug ? `/${row.slug}` : '') +
  (row.difficulty ? ` • ${row.difficulty}` : '') +
  timePart;


      item.innerHTML = `
        <div class="drag"><i class="fa fa-grip-vertical"></i></div>
        <div class="flex-1">
          <div class="q-item-title text-truncate">${_esc(row.title || 'Untitled')}</div>
          <div class="q-item-sub">${_esc(sub || '—')}</div>
        </div>
        <div class="badge ${row.status === 'active'
          ? 'bg-success'
          : (row.status === 'archived' ? 'bg-secondary' : 'bg-warning text-dark')
        }">${_esc(row.status || 'active')}</div>
      `;

      item.addEventListener('click', (e)=>{ if (!e.target.closest('.drag')) select(row.id); });
      item.addEventListener('dragstart', onDragStart);
      item.addEventListener('dragover', onDragOver);
      item.addEventListener('dragleave', onDragLeave);
      item.addEventListener('drop', onDrop);
      item.addEventListener('dragend', onDragEnd);

      if (row.id === currentId) item.classList.add('active');
      qList.appendChild(item);
    });
  }

  function markActive(){
    qList.querySelectorAll('.q-item')
      .forEach(n => n.classList.toggle('active', String(n.dataset.id) === String(currentId)));
  }

  // ===== Select item =====
  async function select(id){
    currentId = id;
    markActive();
    saveStatus.textContent = 'Loading…';
    try{
      const json = await API.get(id).then(r=>r.json());
      const q = json.data || json.question || json;

      // Basics
      qid.value = q.id || '';
      title.value = q.title || '';
      slug.value = q.slug || '';
      status.value = q.status || 'active';
      difficulty.value = q.difficulty || 'medium';
      sort_order.value = q.sort_order ?? 0;
      total_time_min.value = (q.total_time_min ?? '');


      desc.value = q.description || '';
      solution.value = q.solution || '';

      // Sync WYSIWYG editors with textarea values
      syncEditorsFromTextareas();

      // Checker
      compare_mode.value    = q.compare_mode || 'exact';
      trim_output.value     = (q.trim_output ?? true) ? '1':'0';
      whitespace_mode.value = q.whitespace_mode || 'trim';
      float_abs_tol.value   = q.float_abs_tol ?? '';
      float_rel_tol.value   = q.float_rel_tol ?? '';

      // Tags
      if (Array.isArray(q.tags)) {
        tagsInput.value = q.tags.join(', ');
      } else if (typeof q.tags === 'string') {
        tagsInput.value = q.tags;
      } else {
        tagsInput.value = '';
      }

      // Languages (merge languages + snippets by language_key)
      const langs = Array.isArray(q.languages) ? q.languages : (q.question_languages||[]);
      const snips = Array.isArray(q.snippets)  ? q.snippets  : (q.question_snippets||[]);
      const snipMap = new Map(snips.map(s=>[s.language_key, s]));

      langBlocks = (langs||[]).map((L,i)=>({
        id: L.id,
        language_key: L.language_key||'',
        runtime_key: L.runtime_key||'',
        source_filename: L.source_filename||'',
        compile_cmd: L.compile_cmd||'',
        run_cmd: L.run_cmd||'',
        time_limit_ms: L.time_limit_ms ?? '',
        memory_limit_kb: L.memory_limit_kb ?? '',
        stdout_kb_max: L.stdout_kb_max ?? '',
        line_limit: L.line_limit ?? '',
        byte_limit: L.byte_limit ?? '',
        max_inputs: L.max_inputs ?? '',
        max_stdin_tokens: L.max_stdin_tokens ?? '',
        max_args: L.max_args ?? '',
        allow_label: L.allow_label||'',
        allow: normalizeToArray(L.allow),
        forbid_regex: normalizeToArray(L.forbid_regex),
        is_enabled: L.is_enabled !== false,
        sort_order: L.sort_order ?? i,
        // snippet merged:
        entry_hint: snipMap.get(L.language_key)?.entry_hint || '',
        template:   snipMap.get(L.language_key)?.template   || '',
        is_default: !!(snipMap.get(L.language_key)?.is_default)
      }));
      // include snippets-only
      snips.forEach((s)=>{
        if(!langBlocks.find(b=>b.language_key===s.language_key)){
          langBlocks.push({
            language_key: s.language_key, runtime_key:'', source_filename:'',
            compile_cmd:'', run_cmd:'', time_limit_ms:'', memory_limit_kb:'', stdout_kb_max:'',
            line_limit:'', byte_limit:'', max_inputs:'', max_stdin_tokens:'', max_args:'',
            allow_label:'', allow:[], forbid_regex:[], is_enabled:true, sort_order: langBlocks.length,
            entry_hint: s.entry_hint||'', template: s.template||'', is_default: !!s.is_default
          });
        }
      });
      renderLangs();

      // Tests (keep id so backend updates instead of duplicating)
      testRows = (q.tests || q.question_tests || []).map((t,i)=>({
        id: t.id || null,
        visibility: t.visibility || 'hidden',
        input: t.input ?? '',
        expected: t.expected ?? '',
        score: t.score ?? 1,
        is_active: !!t.is_active,
        sort_order: t.sort_order ?? i
      }));
      renderTests();

      saveStatus.textContent = 'Loaded';
      setTimeout(()=> saveStatus.textContent='—', 800);
    }catch(e){
      _err('Failed to load question');
      saveStatus.textContent = 'Error';
    }
  }

  function normalizeToArray(v){
    if (!v) return [];
    if (Array.isArray(v)) return v;
    try {
      const p = typeof v === 'string' ? JSON.parse(v) : v;
      return Array.isArray(p) ? p : [];
    } catch { return []; }
  }

  function resetForm(){
    form.reset();
    qid.value='';
    title.value='';
    slug.value='';
    desc.value='';
    solution.value='';
    compare_mode.value='exact';
    trim_output.value='1';
    whitespace_mode.value='trim';
    float_abs_tol.value='';
    float_rel_tol.value='';
    total_time_min.value = '';
    tagsInput.value = '';
    langBlocks=[];
    renderLangs();
    testRows=[];
    renderTests();
    // Reset editors
    syncEditorsFromTextareas();
  }

  // ===== Language block UI =====
  function optionsHTML(list, selected){
    const labelMap = { python:'Python', cpp:'C++', c:'C', java:'Java' };
    return list.map(v=>{
      const label = labelMap[v] || v;
      return `<option value="${_esc(v)}" ${selected===v?'selected':''}>${_esc(label)}</option>`;
    }).join('');
  }

  function langCard(row, idx){
    const runtimeOpts = runtimeOptionsFor(row.language_key || 'python');
    const selectedRuntime = runtimeOpts.includes(row.runtime_key) ? row.runtime_key : runtimeOpts[0];

    return `
      <div class="lang-card" data-lang="${idx}">
        <div class="head mb-2">
          <span class="drag"><i class="fa fa-grip-vertical"></i></span>
          <strong>Language</strong>
          <div class="row-actions">
            <label class="form-check form-switch tiny mt-1">
              <input class="form-check-input lang_enabled" type="checkbox" ${row.is_enabled?'checked':''}>
              <span class="form-check-label">enabled</span>
            </label>
            <button type="button" class="btn btn-sm btn-outline-danger btnDelLang">Delete</button>
          </div>
        </div>

        <div class="grid-3">
          <div>
            <label class="form-label">language_key</label>
            <select class="form-select lang_language_key">
              ${optionsHTML(LANGUAGE_OPTIONS, row.language_key||'python')}
            </select>
          </div>
          <div>
            <label class="form-label">runtime_key</label>
            <select class="form-select lang_runtime_key">
              ${optionsHTML(runtimeOpts, selectedRuntime)}
            </select>
          </div>
          <div>
            <label class="form-label">source_filename</label>
            <span class="i-btn" data-i-title="Source filename" data-i-text="Default source file used when compiling/running the solution.">i</span>
            <input class="form-control lang_source_filename" value="${_esc(row.source_filename||'')}" placeholder="main.py / main.c / Main.java">
          </div>
        </div>

        <div class="grid-3 mt-2">
          <div>
            <label class="form-label">compile_cmd</label>
            <span class="i-btn" data-i-title="Compile command" data-i-text="Compilation command for compiled languages. Leave empty for interpreted languages.">i</span>
            <input class="form-control lang_compile_cmd" value="${_esc(row.compile_cmd||'')}" placeholder="gcc -O2 main.c -o main">
          </div>
          <div>
            <label class="form-label">run_cmd</label>
            <span class="i-btn" data-i-title="Run command" data-i-text="How to execute the program. Example: ./main or python3 main.py">i</span>
            <input class="form-control lang_run_cmd" value="${_esc(row.run_cmd||'')}" placeholder="./main or python3 main.py">
          </div>
          <div>
            <label class="form-label">stdout_kb_max</label>
            <span class="i-btn" data-i-title="Stdout limit" data-i-text="Maximum stdout size (KB) captured before truncation or failure.">i</span>
            <input type="number" class="form-control lang_stdout_kb_max" value="${row.stdout_kb_max??''}" min="0">
          </div>
        </div>

        <details class="mt-2" open>
          <summary class="small">Resource Limits & Allow/Deny</summary>
          <div class="grid-3 mt-2">
            <div><label class="form-label">time_limit_ms</label><span class="i-btn" data-i-title="Time limit (ms)" data-i-text="Maximum allowed execution time for a single run in milliseconds.">i</span><input type="number" class="form-control lang_time_limit_ms" value="${row.time_limit_ms??''}" min="0"></div>
            <div><label class="form-label">memory_limit_kb</label><span class="i-btn" data-i-title="Memory limit (KB)" data-i-text="Maximum memory in kilobytes.">i</span><input type="number" class="form-control lang_memory_limit_kb" value="${row.memory_limit_kb??''}" min="0"></div>
            <div><label class="form-label">line_limit</label><span class="i-btn" data-i-title="Line limit" data-i-text="Optional output line cap to prevent runaway output.">i</span><input type="number" class="form-control lang_line_limit" value="${row.line_limit??''}" min="0"></div>
          </div>
          <div class="grid-3 mt-2">
            <div><label class="form-label">byte_limit</label><span class="i-btn" data-i-title="Byte limit" data-i-text="Maximum total output bytes allowed.">i</span><input type="number" class="form-control lang_byte_limit" value="${row.byte_limit??''}" min="0"></div>
            <div><label class="form-label">max_inputs</label><span class="i-btn" data-i-title="Max inputs" data-i-text="Number of separate input runs allowed for this question/language.">i</span><input type="number" class="form-control lang_max_inputs" value="${row.max_inputs??''}" min="0"></div>
            <div><label class="form-label">max_stdin_tokens</label><span class="i-btn" data-i-title="Max stdin tokens" data-i-text="Upper bound for tokenized stdin, when your infrastructure measures tokens.">i</span><input type="number" class="form-control lang_max_stdin_tokens" value="${row.max_stdin_tokens??''}" min="0"></div>
          </div>
          <div class="grid-3 mt-2">
            <div><label class="form-label">max_args</label><span class="i-btn" data-i-title="Max args" data-i-text="Maximum number of command-line arguments allowed.">i</span><input type="number" class="form-control lang_max_args" value="${row.max_args??''}" min="0"></div>
            <div>
              <label class="form-label">allow_label</label>
              <select class="form-select lang_allow_label">
                ${['headers','imports','modules','packages','paths','none'].map(v=>`<option value="${_esc(v)}" ${row.allow_label===v?'selected':''}>${_esc(v)}</option>`).join('')}
              </select>
            </div>
            <div>
              <label class="form-label">allow (comma separated)</label>
              <input class="form-control lang_allow" value="${_esc((row.allow||[]).join(', '))}" placeholder="e.g., iostream, vector">
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label">forbid_regex (comma separated)</label>
            <input class="form-control lang_forbid" value="${_esc((row.forbid_regex||[]).join(', '))}" placeholder="e.g., system\\(">
          </div>
        </details>

        <details class="mt-2" open>
          <summary class="small">Starter Snippet</summary>
          <div class="grid-3 mt-2">
            <div>
              <label class="form-label">entry_hint</label>
              <span class="i-btn" data-i-title="Entry hint" data-i-text="Short instruction shown next to the starter code.">i</span>
              <input class="form-control snip_entry_hint" value="${_esc(row.entry_hint||'')}" placeholder="Implement solve()">
            </div>
            <div>
              <label class="form-label">Default</label>
              <select class="form-select snip_is_default">
                <option value="0" ${!row.is_default?'selected':''}>No</option>
                <option value="1" ${row.is_default?'selected':''}>Yes</option>
              </select>
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label">template</label>
            <span class="i-btn" data-i-title="Template" data-i-text="Starter code provided to the user.">i</span>
            <textarea class="form-control snip_template" rows="6" placeholder="// starter code…">${_esc(row.template||'')}</textarea>
          </div>
        </details>
      </div>
    `;
  }

  function renderLangs(){
    if (!langBlocks.length){
      langsWrap.innerHTML = `<div class="text-muted small">No languages yet.</div>`;
      attachInfoButtons();
      return;
    }
    langsWrap.innerHTML = langBlocks.map((r,i)=>langCard(r,i)).join('');

    // delete handlers
    langsWrap.querySelectorAll('.btnDelLang').forEach(btn=>{
      btn.addEventListener('click', e=>{
        const card = e.target.closest('[data-lang]');
        const idx = parseInt(card.dataset.lang,10);
        langBlocks.splice(idx,1);
        renderLangs();
      });
    });

    // drag reorder
    langsWrap.querySelectorAll('[data-lang]').forEach(card=>{
      card.draggable = true;
      card.addEventListener('dragstart', e=>{
        card.classList.add('opacity-50');
        e.dataTransfer.effectAllowed='move';
      });
      card.addEventListener('dragend', e=>{
        card.classList.remove('opacity-50');
        langsWrap.querySelectorAll('.bg-light').forEach(n=>n.classList.remove('bg-light'));
      });
      card.addEventListener('dragover', e=>{
        e.preventDefault();
        card.classList.add('bg-light');
      });
      card.addEventListener('dragleave', e=>{
        card.classList.remove('bg-light');
      });
      card.addEventListener('drop', e=>{
        e.preventDefault();
        card.classList.remove('bg-light');
        const from = parseInt(langsWrap.querySelector('[data-lang].opacity-50')?.dataset.lang, 10);
        const to   = parseInt(card.dataset.lang, 10);
        if (Number.isInteger(from) && Number.isInteger(to) && from !== to) {
          const row = langBlocks.splice(from, 1)[0];
          langBlocks.splice(to, 0, row);
          renderLangs();
        }
      });
    });

    attachInfoButtons();
  }

  function defaultSourceFilename(lang){
    switch(lang){
      case 'python': return 'main.py';
      case 'cpp': return 'main.cpp';
      case 'c': return 'main.c';
      case 'java': return 'Main.java';
      default: return 'main.txt';
    }
  }
  function defaultRunCmd(lang){
    switch(lang){
      case 'python': return 'python3 main.py';
      case 'cpp': return './main';
      case 'c': return './main';
      case 'java': return 'java Main';
      default: return './main';
    }
  }

  btnAddLang.addEventListener('click', ()=>{
    // choose a default language not yet used if possible
    let lang = 'python';
    for (const opt of LANGUAGE_OPTIONS){
      if (!langBlocks.some(b => b.language_key === opt)) { lang = opt; break; }
    }
    const allowed = runtimeOptionsFor(lang);
    langBlocks.push({
      language_key: lang,
      runtime_key: allowed[0],
      source_filename: defaultSourceFilename(lang),
      compile_cmd:'', run_cmd: defaultRunCmd(lang),
      time_limit_ms:'', memory_limit_kb:'', stdout_kb_max:'',
      line_limit:'', byte_limit:'', max_inputs:'', max_stdin_tokens:'', max_args:'',
      allow_label:'imports', allow:[], forbid_regex:[],
      entry_hint:'read from stdin, write to stdout', template:'', is_default: langBlocks.length===0,
      is_enabled:true, sort_order: langBlocks.length
    });
    renderLangs();
  });

  // ===== Tests UI =====
  function testCard(row, idx){
    return `
      <div class="test-row" data-test="${idx}" data-id="${row.id ?? ''}">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="drag"><i class="fa fa-grip-vertical"></i></span>
          <strong class="me-auto">Test #${idx+1}</strong>
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm t_visibility" style="width:130px">
              <option value="sample" ${row.visibility==='sample'?'selected':''}>sample</option>
              <option value="hidden" ${row.visibility!=='sample'?'selected':''}>hidden</option>
            </select>
            <input type="number" class="form-control form-control-sm t_score" style="width:90px" min="0" value="${row.score??1}" placeholder="score">
            <select class="form-select form-select-sm t_active" style="width:110px">
              <option value="1" ${row.is_active!==false?'selected':''}>active</option>
              <option value="0" ${row.is_active===false?'selected':''}>inactive</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger btnDelTest">Delete</button>
          </div>
        </div>
        <div class="grid-2">
          <div>
            <label class="form-label">Input</label>
            <span class="i-btn" data-i-title="Test Input" data-i-text="Stdin fed to the program for this test case.">i</span>
            <textarea class="form-control t_input" rows="3" placeholder="stdin">${_esc(row.input||'')}</textarea>
          </div>
          <div>
            <label class="form-label">Expected Output</label>
            <span class="i-btn" data-i-title="Expected Output" data-i-text="What the program should print to stdout for the input above.">i</span>
            <textarea class="form-control t_expected" rows="3" placeholder="stdout">${_esc(row.expected||'')}</textarea>
          </div>
        </div>
      </div>
    `;
  }

  function renderTests(){
    if (!testRows.length){
      testsWrap.innerHTML = `<div class="text-muted small">No tests yet.</div>`;
      attachInfoButtons();
      return;
    }

    testsWrap.innerHTML = testRows.map((r,i)=>testCard(r,i)).join('');

    testsWrap.querySelectorAll('.btnDelTest').forEach(btn=>{
      btn.addEventListener('click', e=>{
        const row = e.target.closest('[data-test]');
        const idx = parseInt(row.dataset.test,10);
        testRows.splice(idx,1);
        renderTests();
      });
    });

    // drag reorder
    testsWrap.querySelectorAll('[data-test]').forEach(card=>{
      card.draggable=true;
      card.addEventListener('dragstart', e=>{
        card.classList.add('opacity-50');
        e.dataTransfer.effectAllowed='move';
      });
      card.addEventListener('dragend', e=>{
        card.classList.remove('opacity-50');
        testsWrap.querySelectorAll('.bg-light').forEach(n=>n.classList.remove('bg-light'));
      });
      card.addEventListener('dragover', e=>{
        e.preventDefault();
        card.classList.add('bg-light');
      });
      card.addEventListener('dragleave', e=>{
        card.classList.remove('bg-light');
      });
      card.addEventListener('drop', e=>{
        e.preventDefault();
        card.classList.remove('bg-light');
        const from = parseInt(testsWrap.querySelector('[data-test].opacity-50')?.dataset.test,10);
        const to   = parseInt(card.dataset.test,10);
        if(Number.isInteger(from) && Number.isInteger(to) && from!==to){
          const row = testRows.splice(from,1)[0];
          testRows.splice(to,0,row);
          renderTests();
        }
      });
    });

    attachInfoButtons();
  }

  btnAddTest.addEventListener('click', ()=>{
    testRows.push({
      id: null,
      visibility:'sample',
      input:'',
      expected:'',
      score:1,
      is_active:true,
      sort_order:testRows.length
    });
    renderTests();
  });

  // ===== Left list DnD persist =====
  let dragSrc = null;
  function onDragStart(e){
    dragSrc = e.currentTarget;
    e.dataTransfer.effectAllowed='move';
    e.currentTarget.classList.add('opacity-50');
  }
  function onDragOver(e){
    e.preventDefault();
    e.dataTransfer.dropEffect='move';
    e.currentTarget.classList.add('bg-light');
  }
  function onDragLeave(e){
    e.currentTarget.classList.remove('bg-light');
  }
  async function onDrop(e){
    e.preventDefault();
    const target = e.currentTarget;
    target.classList.remove('bg-light');
    if (dragSrc === target) return;
    const rect = target.getBoundingClientRect();
    const before = (e.clientY - rect.top) < rect.height/2;
    if(before) target.parentNode.insertBefore(dragSrc, target);
    else target.parentNode.insertBefore(dragSrc, target.nextSibling);
    await persistOrder();
  }
  function onDragEnd(e){
    e.currentTarget.classList.remove('opacity-50');
    qList.querySelectorAll('.bg-light').forEach(n=>n.classList.remove('bg-light'));
  }
  function getIdsFromDOM(){
    return Array.from(qList.querySelectorAll('.q-item')).map(n=>parseInt(n.dataset.id,10)).filter(Boolean);
  }
  async function persistOrder(){
    const ids = getIdsFromDOM();
    try{
      const r = await API.reorder(ids).then(r=>r.json());
      if (r.status !== 'success') throw new Error(r.message||'Reorder failed');
      _ok('Order updated');
      const map = new Map(all.map(x=>[x.id,x]));
      ids.forEach((id,i)=>{ const row = map.get(id); if(row) row.sort_order = i; });
    }catch(e){
      _err(e.message||'Reorder failed');
    }
  }

  // ===== Create / Update / Delete =====
  btnAdd.addEventListener('click', ()=>{
    resetForm();
    currentId=null;
    markActive();
    title.focus();
  });
  btnRefresh.addEventListener('click', loadList);

  title.addEventListener('input', ()=>{
    if (!slug.value) slug.value = _slugify(title.value);
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    form.classList.add('was-validated');
    if(!title.value.trim()) return;

    try{
      btnSave.disabled = true;
      btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
      saveStatus.textContent = 'Saving…';

      const payload = buildPayload();
      let json;
      if(qid.value){
        json = await API.update(qid.value, payload).then(r=>r.json());
      } else {
        json = await API.create(payload).then(r=>r.json());
      }

      if (json.status !== 'success') throw new Error(json.message || 'Save failed');

      _ok(qid.value ? 'Updated' : 'Created');
      saveStatus.textContent = 'Saved';
      await loadList();
      const newId = json.data?.id || qid.value;
      if (newId) select(newId);
    }catch(err){
      _err(err.message || 'Save failed');
      saveStatus.textContent = 'Error';
    }finally{
      btnSave.disabled = false;
      btnSave.innerHTML = '<i class="fa fa-save me-2"></i>Save';
      setTimeout(()=> saveStatus.textContent='—', 1000);
    }
  });

  btnDelete.addEventListener('click', async ()=>{
    if(!qid.value) return;
    const res = await Swal.fire({
      title: 'Delete this question?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
      background: _isDark() ? '#0b1526' : '#fff',
      color: _isDark() ? '#e6edf7' : '#111',
    });
    if(!res.isConfirmed) return;

    try{
      const r = await API.delete(qid.value).then(r=>r.json());
      if (r.status !== 'success') throw new Error(r.message||'Delete failed');
      _ok('Deleted');
      await loadList();
      resetForm();
      currentId = null;
      markActive();
    }catch(e){
      _err(e.message||'Delete failed');
    }
  });

  function buildPayload(){
    // sync WYSIWYG editors into hidden textareas
    syncTextareasFromEditors();

    // sync langBlocks with DOM inputs
    const cards = Array.from(langsWrap.querySelectorAll('[data-lang]'));
    const updatedLangs = cards.map((card,i)=>{
      const idx = parseInt(card.dataset.lang,10);
      const base = langBlocks[idx] || {};
      const allowStr  = card.querySelector('.lang_allow').value || '';
      const forbidStr = card.querySelector('.lang_forbid').value || '';
      const allows = allowStr.split(',').map(s=>s.trim()).filter(Boolean);
      const forbids = forbidStr.split(',').map(s=>s.trim()).filter(Boolean);

       return {
    language_key: card.querySelector('.lang_language_key').value.trim(),
    runtime_key:  card.querySelector('.lang_runtime_key').value.trim(),
    source_filename: card.querySelector('.lang_source_filename').value.trim(),
    compile_cmd: card.querySelector('.lang_compile_cmd').value.trim(),
    run_cmd:     card.querySelector('.lang_run_cmd').value.trim(),
    stdout_kb_max: _toNum(card.querySelector('.lang_stdout_kb_max').value),
    time_limit_ms:  _toNum(card.querySelector('.lang_time_limit_ms').value),
    memory_limit_kb: _toNum(card.querySelector('.lang_memory_limit_kb').value),
    line_limit:     _toNum(card.querySelector('.lang_line_limit').value),
    byte_limit:     _toNum(card.querySelector('.lang_byte_limit').value),
    max_inputs:     _toNum(card.querySelector('.lang_max_inputs').value),
    max_stdin_tokens: _toNum(card.querySelector('.lang_max_stdin_tokens').value),
    max_args:         _toNum(card.querySelector('.lang_max_args').value),
    allow_label:   card.querySelector('.lang_allow_label').value.trim() || null,
    allow:         allows.length ? allows : [],
    forbid_regex:  forbids.length ? forbids : [],
    is_enabled:    card.querySelector('.lang_enabled').checked,
    sort_order:    i,

    // ✅ keep snippet fields here (these are fine)
    entry_hint:    card.querySelector('.snip_entry_hint').value.trim(),
    template:      card.querySelector('.snip_template').value,
    is_default:    card.querySelector('.snip_is_default').value === '1'
  };
});
    langBlocks = updatedLangs;

    // collect tests from DOM (keep id so backend updates instead of duplicating)
    testRows = Array.from(testsWrap.querySelectorAll('[data-test]')).map((card,i)=>({
      id: card.getAttribute('data-id') ? Number(card.getAttribute('data-id')) : undefined,
      visibility: card.querySelector('.t_visibility').value,
      input: card.querySelector('.t_input').value,
      expected: card.querySelector('.t_expected').value,
      score: _toNum(card.querySelector('.t_score').value) ?? 1,
      is_active: card.querySelector('.t_active').value === '1',
      sort_order: i
    }));

    // dedupe languages by language_key (avoid DB unique errors on snippets)
    const seenLangs = new Set();
    const languages = [];
    const snippets = [];
    langBlocks.forEach((b,i)=>{
      if (!b.language_key) return;
      if (seenLangs.has(b.language_key)) return;
      seenLangs.add(b.language_key);

      languages.push({
        language_key: b.language_key,
        runtime_key: b.runtime_key,
        source_filename: b.source_filename,
        compile_cmd: b.compile_cmd,
        run_cmd: b.run_cmd,
        stdout_kb_max: b.stdout_kb_max,
        time_limit_ms: b.time_limit_ms,
        memory_limit_kb: b.memory_limit_kb,
        line_limit: b.line_limit,
        byte_limit: b.byte_limit,
        max_inputs: b.max_inputs,
        max_stdin_tokens: b.max_stdin_tokens,
        max_args: b.max_args,
        allow_label: b.allow_label,
        allow: b.allow,
        forbid_regex: b.forbid_regex,
        is_enabled: b.is_enabled,
        sort_order: b.sort_order
      });

      snippets.push({
        language_key: b.language_key,
        entry_hint: b.entry_hint,
        template: b.template,
        is_default: b.is_default,
        sort_order: b.sort_order
      });
    });

    const tagsArr = (tagsInput.value || '')
      .split(',')
      .map(t=>t.trim())
      .filter(Boolean);

    return {
  topic_id: Number(TOPIC_ID),
  module_id: Number(MODULE_ID),

  title: title.value.trim(),
  slug: slug.value.trim() || undefined,
  status: status.value,
  difficulty: difficulty.value,
  sort_order: _toNum(sort_order.value) ?? 0,

  // ✅ FIX: send total_time_min at top-level (NOT inside languages)
  total_time_min: _toNum(total_time_min.value),

  tags: tagsArr.length ? tagsArr : undefined,

  description: desc.value.trim(),
  solution: (solution.value || '').trim() || null,

  compare_mode: compare_mode.value,
  trim_output: (trim_output.value === '1'),
  whitespace_mode: whitespace_mode.value,
  float_abs_tol: float_abs_tol.value.trim() !== '' ? Number(float_abs_tol.value) : null,
  float_rel_tol: float_rel_tol.value.trim() !== '' ? Number(float_rel_tol.value) : null,

  languages,
  snippets,
  tests: testRows,
  prune_missing_children: true
};
  }

  // ===== Info buttons (SweetAlert) =====
  function attachInfoButtons(){
    document.querySelectorAll('#pane-code .i-btn').forEach(btn=>{
      if (btn._hasInfoHandler) return;
      btn._hasInfoHandler = true;
      btn.addEventListener('click', ()=>{
        const title = btn.getAttribute('data-i-title') || 'Info';
        const text  = btn.getAttribute('data-i-text')  || '';
        Swal.fire({
          title, text, icon: 'info', confirmButtonText: 'OK',
          background: _isDark() ? '#0b1526' : '#fff',
          color: _isDark() ? '#e6edf7' : '#111',
        });
      });
    });
  }

  // Boot
  loadList();
})();
</script>
@endpush
