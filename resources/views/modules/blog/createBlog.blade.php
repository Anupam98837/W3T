{{-- resources/views/modules/blog/createBlog.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Blog</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- CSS -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/web/favicon.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    /* ========= Layout ========= */
    body.bg-light{ background: var(--bg, #f6f7fb) !important; }

    /* ✅ Make header truly full width while keeping page content max-width */
    .page-wrap{max-width:1250px;margin:0 auto 50px;padding:0 10px}
    .topbar-shell{
      position:sticky; top:0; z-index:1200;
      width:100%;
      padding:0;
      background:transparent;
    }
    .blog-topbar{
      width:100%;
      background:rgb(255, 255, 255);
      backdrop-filter: blur(12px);
      border-bottom:1px solid rgba(0,0,0,.07);
      padding:16px 0;
      box-shadow: 0 4px 20px rgba(0,0,0,.03);
    }
    html.theme-dark .blog-topbar{
      background:rgba(18,18,18,.85);
      border-bottom:1px solid rgba(255,255,255,.1);
    }
    .blog-topbar-inner{
      max-width:1250px;
      margin:0 auto;
      padding:0 10px;
    }

    .top-title{
      font-weight:800;
      letter-spacing:.2px;
      background: linear-gradient(135deg, var(--primary-color, #951eaa) 0%, #6a11cb 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .mini-help{
      font-size:.92rem;
      color:#6b7280;
      opacity:.85;
    }
    html.theme-dark .mini-help{color:rgba(255,255,255,.7)}

    /* Status Indicator - Enhanced */
    .saving-indicator{
      display:inline-flex;
      align-items:center;
      gap:8px;
      font-size:.9rem;
      padding:6px 12px;
      background: rgba(255,255,255,.7);
      border-radius: 20px;
      border: 1px solid rgba(0,0,0,.05);
      transition: all .2s ease;
    }
    html.theme-dark .saving-indicator{
      background: rgba(255,255,255,.08);
      border-color: rgba(255,255,255,.1);
    }
    .saving-indicator .icon{
      animation:pulse 1.5s infinite;
      font-size:.85rem;
    }
    @keyframes pulse{0%{opacity:.5}50%{opacity:1}100%{opacity:.5}}
    .saving-indicator.saving{
      background: rgba(13, 110, 253, .1);
      border-color: rgba(13, 110, 253, .2);
      color: #0d6efd;
    }
    .saving-indicator.saved{
      background: rgba(25, 135, 84, .1);
      border-color: rgba(25, 135, 84, .2);
      color: #198754;
    }
    .saving-indicator.error{
      background: rgba(220, 53, 69, .1);
      border-color: rgba(220, 53, 69, .2);
      color: #dc3545;
    }

    /* Card styling - Enhanced */
    .cardx{
      background: var(--surface, #fff);
      border: 1px solid var(--line-strong, rgba(0,0,0,.08));
      border-radius: 18px;
      box-shadow: var(--shadow-2, 0 10px 30px rgba(0,0,0,.06));
      overflow:hidden;
      margin-top: 18px;
    }
    .cardx .head{
      padding:16px 20px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      border-bottom:1px solid var(--line-strong, rgba(0,0,0,.08));
      background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.95));
    }
    html.theme-dark .cardx .head{
      background: linear-gradient(180deg, rgba(22,22,22,.95), rgba(22,22,22,.9));
    }
    .cardx .body{ padding:24px 20px; }

    /* Improved form layout */
    .form-section{ margin-bottom: 28px; }
    .form-section-title{
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-primary, #111827);
      margin-bottom: 16px;
      padding-bottom: 8px;
      border-bottom: 2px solid rgba(149, 30, 170, .15);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .form-section-title i{ color: var(--primary-color, #951eaa); opacity: .9; }
    html.theme-dark .form-section-title{ color: rgba(255,255,255,.95); }

    /* Better form controls */
    .form-label{
      font-weight: 500;
      color: var(--text-primary, #111827);
      margin-bottom: 8px;
      font-size: .95rem;
    }
    html.theme-dark .form-label{ color: rgba(255,255,255,.9); }

    .form-control, .form-select{
      padding: 10px 14px;
      border-radius: 12px;
      border: 1px solid var(--line-strong, #e5e7eb);
      background: var(--surface, #fff);
      transition: all .2s ease;
      font-size: .95rem;
    }
    .form-control:focus, .form-select:focus{
      border-color: var(--primary-color, #951eaa);
      box-shadow: 0 0 0 3px rgba(149, 30, 170, .1);
      background: var(--surface, #fff);
    }
    html.theme-dark .form-control,
    html.theme-dark .form-select{
      background: rgba(255,255,255,.05);
      border-color: rgba(255,255,255,.15);
      color: rgba(255,255,255,.9);
    }

    /* Thumbnail preview - Enhanced */
    .thumb{
      width:100%;
      max-width:360px;
      height:200px;
      object-fit:cover;
      border:2px solid var(--line-strong,#e5e7eb);
      border-radius:14px;
      background:#fff;
      box-shadow: 0 4px 12px rgba(0,0,0,.08);
      transition: all .3s ease;
    }
    .thumb:hover{ transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.12); }

    /* Editor container */
    #blog-editor-wrap{width:100%}
    #blog-editor-wrap .ce-editor,
    #blog-editor-wrap .ce-editor__holder,
    #blog-editor-wrap .ce-editor__redactor{width:100% !important;max-width:100% !important}

    /* Editor shell */
    .editor-shell{
      border: 1px solid var(--line-strong,#e5e7eb);
      border-radius: 18px;
      overflow: hidden;
      background: var(--surface, #fff);
      margin-top: 16px;
      box-shadow: 0 4px 16px rgba(0,0,0,.04);
    }
    .editor-top{
      display:flex; align-items:center; justify-content:space-between;
      padding:14px 20px;
      border-bottom:1px solid var(--line-strong,#e5e7eb);
      background: linear-gradient(180deg, rgba(0,0,0,.02), rgba(0,0,0,.01));
    }
    html.theme-dark .editor-top{
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
    }
    .editor-top .left{
      display:flex; align-items:center; gap:12px;
      font-weight:600; color: var(--text-primary, #111827);
    }
    .editor-top .right{ display:flex; align-items:center; gap:10px; }
    .icon-btn{
      width:42px;height:42px;border-radius:12px;
      border:1px solid var(--line-strong,#e5e7eb);
      background: var(--surface,#fff);
      display:inline-flex; align-items:center; justify-content:center;
      cursor:pointer; transition: all .2s ease;
      color: var(--text-secondary, #6b7280);
    }
    .icon-btn:hover{
      transform: translateY(-2px);
      box-shadow: var(--shadow-2, 0 8px 18px rgba(0,0,0,.1));
      border-color: var(--primary-color, #951eaa);
      color: var(--primary-color, #951eaa);
      background: rgba(149, 30, 170, .05);
    }
    html.theme-dark .icon-btn{
      background: rgba(255,255,255,.08);
      border-color: rgba(255,255,255,.15);
    }

    /* Fullscreen mode */
    .editor-fullscreen{
      position: fixed;
      inset: 12px;
      z-index: 20000;
      margin: 0 !important;
      border-radius: 18px;
      box-shadow: 0 24px 80px rgba(0,0,0,.35);
      display:flex;
      flex-direction:column;
      background: var(--surface,#fff);
      border:1px solid var(--line-strong,#e5e7eb);
    }
    .editor-fullscreen .editor-top{ border-radius:18px 18px 0 0; }
    .editor-fullscreen .editor-body{ flex:1; overflow:auto; padding:20px; }
    .editor-normal .editor-body{ padding:20px; }
    .editor-fullscreen #blog-editor-wrap .ce-editor__redactor{ min-height: calc(100vh - 180px) !important; }
    .editor-normal #blog-editor-wrap .ce-editor__redactor{ min-height: 450px; }

    /* Fullscreen backdrop */
    .fs-backdrop{
      position:fixed; inset:0;
      background:rgba(0,0,0,.5);
      z-index:19999;
      display:none;
      backdrop-filter: blur(4px);
    }
    .fs-backdrop.show{ display:block; }

    /* Buttons in header */
    .top-actions{ display:flex; gap:12px; align-items:center; flex-wrap:wrap; justify-content:flex-end; }
    @media (max-width: 768px){
      .blog-topbar{ padding:12px 0; }
      .top-actions{ gap:8px; }
      .cardx .body{ padding:20px 16px; }
    }

    /* Hints */
    .field-hint{
      font-size: .85rem;
      color: #6b7280;
      margin-top: 6px;
      opacity: .8;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .field-hint i{ font-size: .8rem; }
    .required-star{ color: #dc3545; margin-left: 4px; }

    /* Side panel styling */
    .side-panel{
      background: linear-gradient(180deg, rgba(249, 250, 251, .9), rgba(249, 250, 251, .95));
      border-radius: 16px;
      padding: 20px;
      border: 1px solid rgba(0,0,0,.05);
    }
    html.theme-dark .side-panel{
      background: linear-gradient(180deg, rgba(30, 30, 30, .9), rgba(30, 30, 30, .95));
      border-color: rgba(255,255,255,.08);
    }

    /* ✅ SweetAlert2: make loader appear below the sticky header */
    .swal2-container.swal-below-topbar{
      padding-top: 92px !important;
      align-items: flex-start !important;
    }
    @media (max-width: 768px){
      .swal2-container.swal-below-topbar{ padding-top: 80px !important; }
    }
  </style>
</head>

<body class="bg-light">

  <div id="fsBackdrop" class="fs-backdrop" aria-hidden="true"></div>

  <!-- ✅ FULL WIDTH STICKY HEADER -->
  <div class="topbar-shell">
    <div class="blog-topbar">
      <div class="blog-topbar-inner">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div class="pe-2">
            <div class="top-title h4 mb-1" id="pageTitle">Create Blog</div>
            <div class="mini-help" id="pageSub">Write blog details and save.</div>
          </div>

          <div class="top-actions">
            <!-- ✅ BACK BUTTON -->
            <button class="btn btn-outline-secondary btn-sm" id="btnBack" type="button" title="Back to Manage Blogs">
              <i class="fa-solid fa-arrow-left me-1"></i> Back
            </button>

            <span id="savingIndicator" class="saving-indicator saved">
              <i class="fas fa-cloud icon"></i>
              <span class="text">Ready</span>
            </span>

            <button class="btn btn-outline-secondary btn-sm" id="btnPreview" type="button">
              <i class="fa-solid fa-eye me-1"></i> Preview
            </button>

            <button class="btn btn-primary btn-sm" id="btnSave" type="button">
              <i class="fa-solid fa-save me-1"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-wrap">

    <div class="cardx">
      <div class="head">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-newspaper" style="color: var(--primary-color, #951eaa);"></i>
          <strong id="modeBadge">Create Mode</strong>
        </div>

        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-outline-info" id="bodyInstructionsBtn" title="Instructions">
            <i class="fa-solid fa-circle-info"></i> Help
          </button>
        </div>
      </div>

      <div class="body">
        @csrf
        <input type="hidden" id="blogUuid" />

        <!-- BLOG DETAILS SECTION -->
        <div class="form-section">
          <div class="form-section-title">
            <i class="fa-solid fa-file-lines"></i>
            Blog Details
          </div>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="row g-4">
                <div class="col-md-8">
                  <label class="form-label">Title <span class="required-star">*</span></label>
                  <input type="text" id="blogTitle" class="form-control" placeholder="e.g., Admission Open for 2026" required>
                  <div class="field-hint">
                    <i class="fa-solid fa-lightbulb"></i>
                    Make it descriptive and engaging
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Blog Date</label>
                  <input type="date" id="blogDate" class="form-control">
                  <div class="field-hint">Leave empty for today's date</div>
                </div>

                <div class="col-md-8">
                  <label class="form-label">Slug</label>
                  <input type="text" id="blogSlug" class="form-control" placeholder="auto-generated-if-empty">
                  <div class="field-hint">
                    <i class="fa-solid fa-link"></i>
                    URL-friendly version of your title
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Shortcode</label>
                  <input type="text" id="blogShortcode" class="form-control" placeholder="auto-generated-if-empty">
                  <div class="field-hint">For embedding in pages</div>
                </div>

                <div class="col-12">
                  <label class="form-label">Short Description</label>
                  <textarea id="blogShortDesc" class="form-control" rows="3"
                    placeholder="Brief summary of your blog post (max ~500 characters)"></textarea>
                  <div class="field-hint">
                    <i class="fa-solid fa-align-left"></i>
                    Appears in blog listings and meta descriptions
                  </div>
                </div>
              </div>
            </div>

            <!-- SIDE PANEL -->
            <div class="col-lg-4">
              <div class="side-panel h-100">
                <div class="mb-4">
                  <label class="form-label">Status</label>
                  <select id="blogStatus" class="form-select">
                    <option value="draft">Draft</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                  <div class="field-hint mt-1">Control visibility workflow</div>
                </div>

                <div class="mb-4">
                  <label class="form-label">Published</label>
                  <select id="blogPublished" class="form-select">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                  </select>
                  <div class="field-hint mt-1">Make visible to public</div>
                </div>

                <div class="mb-4">
                  <label class="form-label">Featured Image</label>
                  <div class="input-group">
                    <input type="file" id="featuredImage" class="form-control" accept="image/*">
                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('featuredImage').click()">
                      <i class="fa-solid fa-upload"></i>
                    </button>
                  </div>
                  <div class="field-hint mt-1">
                    <i class="fa-solid fa-image"></i>
                    Max 5MB (JPEG, PNG, WebP)
                  </div>
                </div>

                <div class="mb-3">
                  <div class="d-flex justify-content-center mb-2">
                    <img id="featuredPreview" class="thumb" src="" alt="Featured preview" style="display:none;">
                  </div>
                  <div id="featuredHint" class="field-hint text-center">
                    <i class="fa-solid fa-image"></i>
                    No featured image selected
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CONTENT SECTION -->
        <div class="form-section">
          <div class="form-section-title">
            <i class="fa-solid fa-pen-nib"></i>
            Blog Content
          </div>

          <div id="editorContainer" class="editor-shell editor-normal">
            <div class="editor-top">
              <div class="left">
                <i class="fa-solid fa-keyboard"></i>
                <span>Rich Text Editor</span>
                <span class="mini-help ms-2">Write your content here</span>
              </div>

              <div class="right">
                <button type="button" class="icon-btn" id="btnEditorFullscreen" title="Fullscreen editor">
                  <i class="fa-solid fa-expand"></i>
                  <span class="ms-1 d-none d-sm-inline"></span>
                </button>
              </div>
            </div>

            <div class="editor-body">
              <div id="blog-editor-wrap">
                @include('modules.blog.editor')
              </div>
              <textarea id="blogContentHtml" class="d-none"></textarea>
            </div>
          </div>

          <div class="field-hint mt-2">
            <i class="fa-solid fa-lightbulb"></i>
            Use the fullscreen button for distraction-free writing. All formatting is preserved.
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  /* ================== TOKEN ================== */
  function getToken(){ return sessionStorage.getItem('token') || localStorage.getItem('token'); }
  function getQueryParam(key){ const u=new URL(window.location.href); return u.searchParams.get(key); }

  /* ================== SWEETALERT HELPERS (below topbar) ================== */
  function swalLoadingBelowTopbar(title='Loading…', text='Please wait...'){
    return Swal.fire({
      title,
      text,
      allowOutsideClick:false,
      didOpen:()=>Swal.showLoading(),
      customClass:{ container:'swal-below-topbar' }
    });
  }

  /* ================== API CORE (JSON) ================== */
  async function apiJson(url,{method='GET',body=null,headers={}}={}){
    const token=getToken();
    if(!token) throw new Error('NO_TOKEN');

    const base={
      Accept:'application/json',
      Authorization:`Bearer ${token}`,
      'X-Requested-With':'XMLHttpRequest'
    };

    if(body && typeof body==='object' && !(body instanceof FormData)){
      base['Content-Type']='application/json';
      body=JSON.stringify(body);
    }

    if(['PUT','PATCH','DELETE'].includes(method)){
      base['X-HTTP-Method-Override']=method;
      method='POST';
    }

    const res=await fetch(url,{method,headers:{...base,...headers},body});
    const ct=res.headers.get('content-type')||'';
    let data;

    if(ct.includes('application/json')){
      try{ data=await res.json(); }catch{ data={}; }
    } else {
      const text=await res.text();
      console.warn('[apiJson] NON-JSON BODY', text.slice(0,200));
      throw new Error('NON_JSON_RESPONSE_POSSIBLE_AUTH ('+res.status+')');
    }

    if(!res.ok){
      const err=new Error(data.message||data.error||('HTTP '+res.status));
      err.status=res.status; err.payload=data; throw err;
    }
    return data;
  }

  /* ================== API CORE (FormData) ================== */
  async function apiForm(url,{method='POST',formData,headers={}}){
    const token=getToken();
    if(!token) throw new Error('NO_TOKEN');

    const base={
      Accept:'application/json',
      Authorization:`Bearer ${token}`,
      'X-Requested-With':'XMLHttpRequest'
    };

    if(['PUT','PATCH','DELETE'].includes(method)){
      base['X-HTTP-Method-Override']=method;
      method='POST';
    }

    const res=await fetch(url,{method,headers:{...base,...headers},body:formData});
    const ct=res.headers.get('content-type')||'';
    let data;

    if(ct.includes('application/json')){
      try{ data=await res.json(); }catch{ data={}; }
    } else {
      const text=await res.text();
      console.warn('[apiForm] NON-JSON BODY', text.slice(0,200));
      throw new Error('NON_JSON_RESPONSE_POSSIBLE_AUTH ('+res.status+')');
    }

    if(!res.ok){
      const err=new Error(data.message||data.error||('HTTP '+res.status));
      err.status=res.status; err.payload=data; throw err;
    }
    return data;
  }

  /* ================== SAFE EDITOR INIT ================== */
  function initBlogEditor(){
    return new Promise((resolve, reject) => {
      try{
        if(!window.CEBuilder){
          let tries = 0;
          const t = setInterval(() => {
            tries++;
            if(window.CEBuilder){
              clearInterval(t);
              initBlogEditor().then(resolve).catch(reject);
            } else if(tries > 40){
              clearInterval(t);
              resolve({ skipped:true, reason:'CEBuilder not found' });
            }
          }, 100);
          return;
        }

        if(typeof window.CEBuilder.init === 'function'){
          const p = window.CEBuilder.init();
          if(p && typeof p.then === 'function') return p.then(()=>resolve({ok:true, via:'init'})).catch(reject);
          return resolve({ok:true, via:'init-sync'});
        }

        if(typeof window.CEBuilder.create === 'function'){
          const p = window.CEBuilder.create();
          if(p && typeof p.then === 'function') return p.then(()=>resolve({ok:true, via:'create'})).catch(reject);
          return resolve({ok:true, via:'create-sync'});
        }

        return resolve({ok:true, via:'no-init-needed'});
      }catch(e){ reject(e); }
    });
  }

  /* ================== EDITOR HELPERS ================== */
  function setBodyHTML(html){
    html = html || '';
    if(window.CEBuilder){
      if(typeof window.CEBuilder.setHTML === 'function') window.CEBuilder.setHTML(html);
      else if(window.CEBuilder.editor && typeof window.CEBuilder.editor.setHTML === 'function') window.CEBuilder.editor.setHTML(html);
    }
    $('#blogContentHtml').val(html);
  }
  function getBodyHTML(){
    if(window.CEBuilder){
      if(typeof window.CEBuilder.getHTML === 'function') return (window.CEBuilder.getHTML() || '').trim();
      if(window.CEBuilder.editor && typeof window.CEBuilder.editor.getHTML === 'function') return (window.CEBuilder.editor.getHTML() || '').trim();
    }
    return ($('#blogContentHtml').val() || '').trim();
  }

  /* ================== SAVING INDICATOR ================== */
  let lastSaveTime = 0;
  function setSavingState(state) {
    const indicator = $('#savingIndicator');
    indicator.removeClass('saving saved error');

    if (state === 'saving') {
      indicator.addClass('saving');
      indicator.find('.icon').attr('class', 'fas fa-spinner fa-spin icon');
      indicator.find('.text').text('Saving...');
    } else if (state === 'saved') {
      indicator.addClass('saved');
      indicator.find('.icon').attr('class', 'fas fa-check-circle icon');
      const timeStr = lastSaveTime ? new Date(lastSaveTime).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : null;
      indicator.find('.text').text(timeStr ? `Saved at ${timeStr}` : 'All changes saved');
    } else if (state === 'error') {
      indicator.addClass('error');
      indicator.find('.icon').attr('class', 'fas fa-exclamation-triangle icon');
      indicator.find('.text').text('Error saving');
    } else {
      indicator.addClass('saved');
      indicator.find('.icon').attr('class', 'fas fa-cloud icon');
      indicator.find('.text').text('Ready to save');
    }
  }

  /* ================== MODE ================== */
  let mode='create';
  function setMode(m){
    mode=m;

    if(mode==='edit'){
      $('#pageTitle').text('Edit Blog');
      $('#modeBadge').text('Edit Mode');
      $('#pageSub').text('Editing existing blog (loaded via ?uuid=...)');
      $('#btnPreview').html('<i class="fa-solid fa-up-right-from-square me-1"></i> Preview');
    } else {
      $('#pageTitle').text('Create Blog');
      $('#modeBadge').text('Create Mode');
      $('#pageSub').text('Write blog details and save.');
      $('#btnPreview').html('<i class="fa-solid fa-eye me-1"></i> Preview');
    }
  }

  /* ================== PREVIEW URL (EDIT MODE DIRECT) ================== */
  function buildPreviewUrl(){
    const slug = ($('#blogSlug').val() || '').trim();
    if(!slug) return '';
    return `/blog/view/${encodeURIComponent(slug)}?mode=test`;
  }

  function previewDirectNewTab(){
    const url = buildPreviewUrl();
    if(!url){
      Swal.fire({
        icon:'info',
        title:'Slug missing',
        text:'Slug is required for direct preview. Please enter slug (or blur title to auto-generate).',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
      $('#blogSlug').focus();
      return;
    }
    window.open(url, '_blank', 'noopener');
  }

  /* ================== LOAD BLOG ================== */
  async function loadBlog(identifier){
    swalLoadingBelowTopbar('Loading…','Fetching blog data...');
    try{
      const json = await apiJson(`/api/blogs/${encodeURIComponent(identifier)}`, { method:'GET' });
      const b = json.data || json.blog || json;

      $('#blogUuid').val(b.uuid || identifier);
      $('#blogTitle').val(b.title || '');
      $('#blogSlug').val(b.slug || '');
      $('#blogShortcode').val(b.shortcode || '');
      $('#blogShortDesc').val(b.short_description || '');

      if(b.blog_date){
        $('#blogDate').val(String(b.blog_date).slice(0,10));
      } else {
        $('#blogDate').val('');
      }

      $('#blogStatus').val(b.status || 'draft');
      $('#blogPublished').val((String(b.is_published)==='1') ? 'yes' : 'no');

      setBodyHTML(b.content_html || '');

      if(b.featured_image_url){
        $('#featuredPreview').attr('src', b.featured_image_url).show();
        $('#featuredHint').html('<i class="fa-solid fa-check-circle"></i> Current featured image loaded');
      } else {
        $('#featuredPreview').hide().attr('src','');
        $('#featuredHint').html('<i class="fa-solid fa-image"></i> No featured image set');
      }

      Swal.close();
      setSavingState('ready');
    }catch(err){
      Swal.close();
      handleApiError('Failed to load blog', err);
    }
  }

  /* ================== SAVE ================== */
  async function saveBlog(){
    const title = $('#blogTitle').val().trim();
    if(!title){
      Swal.fire({
        icon: 'error',
        title: 'Missing Title',
        text: 'Blog title is required',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
      $('#blogTitle').focus();
      return;
    }

    const payloadBase = {
      title,
      slug: ($('#blogSlug').val().trim() || null),
      shortcode: ($('#blogShortcode').val().trim() || null),
      short_description: ($('#blogShortDesc').val().trim() || null),
      blog_date: ($('#blogDate').val() || null),
      status: $('#blogStatus').val() || 'draft',
      is_published: $('#blogPublished').val() || 'no',
      content_html: (getBodyHTML() || '')
    };

    const identifier = $('#blogUuid').val().trim();
    const file = document.getElementById('featuredImage').files[0] || null;

    setSavingState('saving');
    swalLoadingBelowTopbar('Saving Blog...','Please wait while we save your changes');

    try{
      if(file){
        const fd = new FormData();
        Object.entries(payloadBase).forEach(([k,v]) => {
          if(v !== null && v !== undefined) fd.append(k, v);
        });
        fd.append('featured_image', file);

        let url = '/api/blogs';
        let method = 'POST';

        if(mode === 'edit' && identifier){
          url = `/api/blogs/${encodeURIComponent(identifier)}`;
          method = 'PUT';
        }

        const json = await apiForm(url, { method, formData: fd });
        const saved = json.data || null;

        lastSaveTime = Date.now();
        setSavingState('saved');

        if(mode === 'create' && saved && (saved.uuid || saved.id || saved.slug)){
          const newId = saved.uuid || saved.id || saved.slug;
          Swal.close();
          window.location.href = `/blog/create?uuid=${encodeURIComponent(newId)}`;
          return;
        }

        Swal.fire({
          icon: 'success',
          title: 'Saved Successfully',
          text: json.message || 'Your blog has been saved',
          confirmButtonColor: 'var(--primary-color, #951eaa)',
          customClass:{ container:'swal-below-topbar' }
        });
        return;
      }

      let url = '/api/blogs';
      let method = 'POST';

      if(mode === 'edit' && identifier){
        url = `/api/blogs/${encodeURIComponent(identifier)}`;
        method = 'PUT';
      }

      const json = await apiJson(url, { method, body: payloadBase });
      const saved = json.data || null;

      lastSaveTime = Date.now();
      setSavingState('saved');

      if(mode === 'create' && saved && (saved.uuid || saved.id || saved.slug)){
        const newId = saved.uuid || saved.id || saved.slug;
        Swal.close();
        window.location.href = `/blog/create?uuid=${encodeURIComponent(newId)}`;
        return;
      }

      Swal.fire({
        icon: 'success',
        title: 'Saved Successfully',
        text: json.message || 'Your blog has been saved',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });

    }catch(err){
      setSavingState('error');
      Swal.close();
      handleApiError('Failed to save blog', err);
    }
  }

  /* ================== PREVIEW MODAL (CREATE MODE) ================== */
  async function previewModal(){
    const title = $('#blogTitle').val().trim() || 'Blog Preview';
    const html  = getBodyHTML();

    if(!html.trim()){
      Swal.fire({
        icon: 'info',
        title: 'No Content',
        text: 'Add some content to preview',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
      return;
    }

    Swal.fire({
      title: '',
      html: `
        <div class="preview-tools" style="display:flex;gap:8px;justify-content:center;margin-bottom:12px;flex-wrap:wrap">
          <button class="device-btn active" data-device="desktop"
            style="border:1px solid var(--line-strong);border-radius:10px;padding:8px 14px;background:var(--surface);color:var(--text-primary)">
            <i class="fa-solid fa-desktop me-1"></i>Desktop
          </button>
          <button class="device-btn" data-device="tablet"
            style="border:1px solid var(--line-strong);border-radius:10px;padding:8px 14px;background:var(--surface);color:var(--text-primary)">
            <i class="fa-solid fa-tablet-screen-button me-1"></i>Tablet
          </button>
          <button class="device-btn" data-device="mobile"
            style="border:1px solid var(--line-strong);border-radius:10px;padding:8px 14px;background:var(--surface);color:var(--text-primary)">
            <i class="fa-solid fa-mobile-screen-button me-1"></i>Mobile
          </button>
        </div>

        <div class="preview-heading" style="text-align:center;margin:6px 0 14px;">
          <div style="font-weight:800;font-size:1.25rem;color:var(--ink,#111827)">${escapeHtml(title)}</div>
          <div style="font-size:.9rem;color:var(--muted-color,#6b7280);margin-top:2px;">Preview (Create Mode)</div>
        </div>

        <div class="preview-container desktop" style="border:1px solid var(--line-strong);border-radius:14px;overflow:hidden">
          <iframe id="previewFrame" sandbox style="width:100%;height:70vh;border:0"
            srcdoc="${buildPreviewSrcdoc(html)}">
          </iframe>
        </div>

        <style>
          .preview-container.tablet iframe{max-width: 820px; margin:0 auto; display:block;}
          .preview-container.mobile iframe{max-width: 375px; margin:0 auto; display:block;}
          .device-btn.active{
            outline:2px solid var(--primary-color, #951eaa);
            background: rgba(149, 30, 170, .1) !important;
          }
        </style>
      `,
      width: '95%',
      showCloseButton: true,
      showConfirmButton: false,
      customClass: {
        popup: 'preview-modal',
        container: 'swal-below-topbar'
      },
      didOpen: () => {
        const container = Swal.getHtmlContainer();
        container.querySelectorAll('.device-btn').forEach(btn => {
          btn.addEventListener('click', e => {
            container.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
            e.currentTarget.classList.add('active');
            const device = e.currentTarget.dataset.device;
            const wrap = container.querySelector('.preview-container');
            wrap.className = 'preview-container ' + device;
          });
        });
      }
    });
  }

  function escapeHtml(str){
    return String(str || '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function buildPreviewSrcdoc(contentHtml){
    const body = String(contentHtml || '');
    const doc =
`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:20px;max-width:900px;margin:0 auto;line-height:1.65}
    img{max-width:100%;height:auto}
    a{color:#951eaa;font-weight:700}
  </style>
</head>
<body>${body}</body>
</html>`;
    return escapeHtml(doc);
  }

  /* ================== ERROR HANDLING ================== */
  function handleApiError(context,err){
    console.error(context,err);
    let msg=err.message||context;

    if(err.status===401) msg='Unauthorized – please login again.';
    else if(err.status===403) msg='Forbidden – check role/token.';
    else if(msg.startsWith('NON_JSON_RESPONSE_POSSIBLE_AUTH')) msg='Server returned non-JSON (likely login HTML). Auth failed.';
    else if(msg==='NO_TOKEN') msg='No auth token found. Please login again.';

    if(err.payload && err.payload.errors){
      const flat = Object.values(err.payload.errors).flat().slice(0,6).join('<br>');
      msg = flat || msg;
    }

    Swal.fire({
      icon: 'error',
      title: 'Error',
      html: msg,
      confirmButtonColor: 'var(--primary-color, #951eaa)',
      customClass:{ container:'swal-below-topbar' }
    }).then(()=>{
      if(msg.includes('login again') || msg.includes('No auth token')) location.href='/';
    });
  }

  /* ================== FULLSCREEN TOGGLE ================== */
  function setEditorFullscreen(on){
    const c = document.getElementById('editorContainer');
    const b = document.getElementById('fsBackdrop');
    const btn = document.getElementById('btnEditorFullscreen');
    const icon = btn ? btn.querySelector('i') : null;

    if(!c) return;

    if(on){
      b && b.classList.add('show');
      c.classList.remove('editor-normal');
      c.classList.add('editor-fullscreen');

      if(icon){
        icon.className = 'fa-solid fa-compress';
        btn.title = 'Exit fullscreen editor';
      }

      document.body.style.overflow = 'hidden';
    } else {
      b && b.classList.remove('show');
      c.classList.remove('editor-fullscreen');
      c.classList.add('editor-normal');

      if(icon){
        icon.className = 'fa-solid fa-expand';
        btn.title = 'Fullscreen editor';
      }

      document.body.style.overflow = '';
    }
  }

  /* ================== INITIALIZATION ================== */
  $(function(){
    const t=getToken();
    if(!t){
      Swal.fire({
        icon: 'warning',
        title: 'Auth Required',
        text: 'Session expired. Please login again.',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      }).then(()=>location.href='/');
      return;
    }

    /* ✅ BACK -> /blog/manage */
    $('#btnBack').on('click', function(){
      window.location.href = '/blog/manage';
    });

    $('#featuredImage').on('change', function(){
      const f = this.files && this.files[0] ? this.files[0] : null;
      if(!f){
        $('#featuredPreview').hide().attr('src','');
        $('#featuredHint').html('<i class="fa-solid fa-image"></i> No featured image selected');
        return;
      }

      if(f.size > 5 * 1024 * 1024){
        Swal.fire({
          icon: 'error',
          title: 'File Too Large',
          text: 'Maximum file size is 5MB',
          confirmButtonColor: 'var(--primary-color, #951eaa)',
          customClass:{ container:'swal-below-topbar' }
        });
        $(this).val('');
        return;
      }

      const url = URL.createObjectURL(f);
      $('#featuredPreview').attr('src', url).show();
      $('#featuredHint').html('<i class="fa-solid fa-check-circle"></i> Image selected - will upload on Save');
    });

    $('#btnEditorFullscreen').on('click', function(){
      const isOn = document.getElementById('editorContainer')?.classList.contains('editor-fullscreen');
      setEditorFullscreen(!isOn);
    });

    $(document).on('keydown', function(e){
      if(e.key === 'Escape'){
        const isOn = document.getElementById('editorContainer')?.classList.contains('editor-fullscreen');
        if(isOn) setEditorFullscreen(false);
      }
    });

    $('#fsBackdrop').on('click', function(){
      setEditorFullscreen(false);
    });

    $('#blogTitle').on('blur', function(){
      const title = $(this).val().trim();
      const slugInput = $('#blogSlug');
      if(title && (!slugInput.val().trim() || slugInput.val().trim() === 'auto-generated-if-empty')){
        const slug = title.toLowerCase()
          .replace(/[^\w\s-]/g, '')
          .replace(/\s+/g, '-')
          .replace(/--+/g, '-');
        slugInput.val(slug);
      }
    });

    initBlogEditor().then(() => {
      const qUuid = getQueryParam('uuid');
      if(qUuid){
        setMode('edit');
        loadBlog(qUuid);
      } else {
        setMode('create');
        const today = new Date().toISOString().split('T')[0];
        $('#blogDate').val(today);
        setBodyHTML('');
        setSavingState('ready');
      }
    }).catch(err=>{
      console.error('Editor init failed', err);
      const qUuid = getQueryParam('uuid');
      if(qUuid){ setMode('edit'); loadBlog(qUuid); } else { setMode('create'); }
      setSavingState('ready');
    });

    // Save
    $('#btnSave').on('click', saveBlog);

    // Preview behavior:
    // - create: modal preview
    // - edit: direct preview new tab (mode=test)
    $('#btnPreview').on('click', function(){
      if(mode === 'edit') return previewDirectNewTab();
      return previewModal();
    });

    $('#bodyInstructionsBtn').on('click',()=>{
      Swal.fire({
        icon:'info',
        title:'Blog Editor Guide',
        html:`<div style="text-align:left">
                <p><strong>Tips for great blog posts:</strong></p>
                <ul style="padding-left:20px;margin-bottom:20px;">
                  <li><b>Title:</b> Make it descriptive and SEO-friendly</li>
                  <li><b>Slug:</b> Auto-generated from title, but you can customize</li>
                  <li><b>Short Description:</b> Appears in listings and search results</li>
                  <li><b>Featured Image:</b> Choose an eye-catching image (5MB max)</li>
                  <li><b>Content:</b> Use the editor toolbar for rich formatting</li>
                  <li><b>Status:</b> Set to "Active" when ready to publish</li>
                </ul>
                <p><i class="fa-solid fa-expand"></i> Use <strong>Fullscreen mode</strong> for distraction-free writing</p>
              </div>`,
        width:700,
        showCloseButton:true,
        showConfirmButton:false,
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
    });

    setSavingState('ready');
  });
  </script>
</body>
</html>
