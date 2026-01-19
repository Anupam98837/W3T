<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>CE Builder (Drag & Drop + Code)</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
:root{
  --ce-bg:#f3f4f6;--ce-white:#fff;--ce-border:#e5e7eb;--ce-text:#111827;--ce-muted:#6b7280;
  --ce-accent:#6366f1;--ce-accent-light:rgba(99,102,241,.08);
  --ce-radius:6px;--ce-shadow-sm:0 1px 3px rgba(0,0,0,.08);--ce-shadow-md:0 6px 18px rgba(0,0,0,.15);
  --ce-transition:.18s ease;font-family:Inter,system-ui,sans-serif;
}
body{margin:0;background:var(--ce-bg);color:var(--ce-text);}

/* TABS */
.ce-tabs{display:flex;background:var(--ce-white);border-bottom:1px solid var(--ce-border);}
.ce-tab-btn{padding:10px 16px;border:none;background:none;cursor:pointer;font-size:14px;border-right:1px solid var(--ce-border);
  color:var(--ce-text);display:flex;gap:6px;align-items:center;transition:background var(--ce-transition);}
.ce-tab-btn:hover{background:#f9fafb;}
.ce-tab-btn.ce-active{background:var(--ce-accent-light);color:var(--ce-accent);font-weight:600;}
.ce-tab-btn.ce-action{border-right:none;margin-left:auto;}
.ce-tab-pane{display:none;height:calc(100vh - 41px);}
.ce-tab-pane.ce-active{display:flex;}

/* LAYOUT */
.ce-editor { display:flex; max-height:100vh; width:100%; overflow:auto; }
.ce-inspector{width:300px;background:var(--ce-white);border-right:1px solid var(--ce-border);display:flex;flex-direction:column;}
.ce-panel-header{padding:14px 16px;font-weight:600;border-bottom:1px solid var(--ce-border);background:#f9fafb;}
.ce-inspector-actions{padding:8px 12px;border-bottom:1px solid var(--ce-border);display:flex;gap:8px;}
.ce-inspector-body{flex:1;overflow:auto;padding:0;font-size:14px;display:flex;flex-direction:column;}
.ce-muted{color:var(--ce-muted);font-size:13px;padding:12px;}
.ce-inspector, .ce-components-panel { position: sticky; top: 41px; height: calc(100vh - 41px); overflow: auto; }

/* Inspector inner tabs */
.ce-prop-tabs{display:flex;border-bottom:1px solid var(--ce-border);}
.ce-prop-tab-btn{flex:1;border:none;background:none;padding:10px 0;font-size:13px;cursor:pointer;color:var(--ce-text);transition:background var(--ce-transition);}
.ce-prop-tab-btn:hover{background:#f3f4f6;}
.ce-prop-tab-btn.ce-active{background:var(--ce-accent-light);color:var(--ce-accent);font-weight:600;}
.ce-prop-pane{display:none;padding:12px;overflow:auto;flex:1;}
.ce-prop-pane.ce-active{display:block;}

/* FIELDS */
.ce-field{margin:0 0 10px 0;}
.ce-field label{display:block;font-size:12px;margin:0 0 4px 0;color:#374151;font-weight:500;}
.ce-field input[type="text"],.ce-field input[type="number"],.ce-field input[type="color"],.ce-field textarea,.ce-field select{
  width:100%;border:1px solid var(--ce-border);border-radius:4px;padding:6px 8px;font-size:13px;box-sizing:border-box;background:#fff;}
.ce-field textarea{min-height:90px;resize:vertical;}
.ce-color-pair{display:flex;gap:8px;}
.ce-color-pair input[type=color]{width:34px;height:34px;padding:0;border:none;border-radius:4px;cursor:pointer;}
.ce-align-group,.ce-typo-tools,.ce-img-align{display:flex;gap:6px;margin-top:4px;}
.ce-align-btn,.ce-typo-btn,.ce-img-align-btn{
  flex:1;border:1px solid var(--ce-border);background:#fff;border-radius:4px;padding:6px 0;cursor:pointer;font-size:14px;
  display:flex;align-items:center;justify-content:center;color:#374151;transition:background .12s;}
.ce-align-btn.active,.ce-typo-btn.active,.ce-img-align-btn.active{
  background:var(--ce-accent-light);color:var(--ce-accent);border-color:var(--ce-accent);
}
.ce-unit-toggle {display: flex; gap: 6px; margin-bottom: 8px;}
.ce-unit-toggle button {
  flex: 1; padding: 4px; border: 1px solid var(--ce-border); background: #fff;
  border-radius: 4px; cursor: pointer; font-size: 12px;
}
.ce-unit-toggle button.active {
  background: var(--ce-accent-light); color: var(--ce-accent); border-color: var(--ce-accent);
}

/* CANVAS */
.ce-canvas-wrap{flex:1;position:relative;overflow:auto;background:#f3f4f6;max-height: 100vh}
.ce-canvas{
  min-height:100%;padding:32px 24px;outline:none;position:relative;
  width: 100%; max-width: 600px; margin: 24px auto; background: var(--ce-white); box-shadow: var(--ce-shadow-sm); border-radius: 8px;
}
.ce-canvas[data-placeholder]:empty:before{
  content:attr(data-placeholder);color:var(--ce-muted);font-size:14px;position:absolute;
  top:40%;left:50%;transform:translate(-50%,-50%);text-align:center;width:240px;
}

/* COMPONENTS */
.ce-components-panel{width:300px;background:var(--ce-white);border-left:1px solid var(--ce-border);display:flex;flex-direction:column;}
.ce-comp-tabs{display:flex;border-bottom:1px solid var(--ce-border);}
.ce-comp-tab-btn{flex:1;padding:10px 0;text-align:center;background:none;border:none;cursor:pointer;font-size:13px;color:var(--ce-text);transition:background var(--ce-transition);}
.ce-comp-tab-btn:hover{background:#f3f4f6;}
.ce-comp-tab-btn.ce-active{background:var(--ce-accent-light);color:var(--ce-accent);font-weight:600;}
.ce-components-list{flex:1;overflow:auto;padding:12px;display:none;}
.ce-components-list.ce-active{display:block;}
.ce-component{
  border:1px solid var(--ce-border);border-radius:var(--ce-radius);background:var(--ce-white);
  padding:10px;margin-bottom:10px;cursor:grab;display:flex;align-items:center;gap:8px;font-size:14px;
  transition:background var(--ce-transition),transform .08s;}
.ce-component:hover{background:#f3f4f6;}
.ce-component i{font-size:16px;}

/* BLOCK (only edit canvas shows outlines/handles) */
#ceCanvasEdit .ce-block{position:relative;}
#ceCanvasEdit .ce-block:hover::after,#ceCanvasEdit .ce-block.ce-selected::after{
  content:"";position:absolute;inset:0;outline:1px dotted var(--ce-accent);pointer-events:none;z-index:2;
}
.ce-block-handle{
  position:absolute;right:6px;top:6px;background:var(--ce-accent);color:#fff;border-radius:3px;
  padding:2px 6px;font-size:11px;display:flex;gap:6px;align-items:center;box-shadow:var(--ce-shadow-sm);
  opacity:0;pointer-events:none;transition:opacity .12s;z-index:3;
}
#ceCanvasEdit .ce-block:hover .ce-block-handle{opacity:1;pointer-events:auto;}
.ce-block-handle span{cursor:pointer;line-height:1;}
.ce-block-handle .ce-remove{color:#f87171;}
.ce-block-handle .ce-dup{color:#34d399;}
.ce-block-handle .ce-up,.ce-block-handle .ce-down{color:#fff;}

.ce-slot{min-height:24px;}
.ce-add-inside{
  margin-top:12px;display:flex;align-items:center;justify-content:center;gap:6px;
  font-size:12px;color:var(--ce-accent);cursor:pointer;width:100%;
}

/* ADD POPUP */
.ce-add-popup{
  position:absolute;background:#fff;border:1px solid var(--ce-border);border-radius:6px;
  box-shadow:var(--ce-shadow-md);padding:12px;width:220px;z-index:9999;font-size:13px;
}
.ce-add-popup h4{margin:0 0 8px 0;font-size:12px;color:#374151;font-weight:600;}
.ce-add-popup button{
  display:block;width:100%;text-align:left;border:0;background:none;padding:6px 8px;border-radius:4px;cursor:pointer;
}
.ce-add-popup button:hover{background:#f3f4f6;color:var(--ce-accent);}

/* DROP MARKER */
.ce-drop-marker{height:4px;background:var(--ce-accent);margin:4px 0;border-radius:2px;opacity:0;transition:opacity .1s linear;}
.ce-drop-marker.active{opacity:1;}

/* SMALL BUTTONS */
.ce-btn-sm{padding:6px 12px;border-radius:4px;border:1px solid var(--ce-border);background:#fff;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;gap:4px;}
.ce-btn-sm.ce-primary{background:var(--ce-accent);border-color:var(--ce-accent);color:#fff;}

/* CODE TAB */
.ce-code-pane{flex-direction:column;}
.ce-code-wrap{flex:1;display:flex;}
.ce-code-left{flex:1;overflow:auto;background:#f9fafb;padding:16px;}
.ce-code-right{flex:1;display:flex;padding:16px;background:#fafafa;}
#ceCodeArea{
  width:100%;height:100%;border:1px solid var(--ce-border);border-radius:6px;padding:12px;font-family:monospace;font-size:13px;resize:none;
  background:var(--ce-white);color:#374151;
}
#ceCanvasEdit a { pointer-events:none; cursor:default; }
.ce-code-actions{padding:10px 16px;background:var(--ce-white);border-top:1px solid var(--ce-border);text-align:right;}

/* MODAL */
#ceModal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
#ceModal .ce-modal-box{
  background:#fff;padding:16px 20px;border-radius:8px;width:500px;max-height:80%;overflow:auto;box-shadow:var(--ce-shadow-md);
}
#ceExport{width:100%;height:300px;font-size:12px;}

/* Hide export canvas */
#ceCanvasExport{display:none;}

/* Device Preview */
.ce-device-preview {
  display:flex;justify-content:center;gap:12px;padding:12px;border-bottom:1px solid var(--ce-border);
  background:var(--ce-white);position:sticky;top:0;z-index:10;
}
.ce-device-btn {
  border:none;background:none;padding:6px 12px;border-radius:4px;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:4px;
}
.ce-device-btn.active { background: var(--ce-accent-light); color: var(--ce-accent); }
.ce-device-btn i { font-size: 16px; }
/* ✅ Default Section Padding (existing + new) */
.ce-block[data-key^="ce-section-"] section{
  padding:12px 0 !important;
}

/* Responsive canvas sizes */
.ce-canvas.desktop { max-width: 100%; }
.ce-canvas.tablet { max-width: 768px; }
.ce-canvas.mobile { max-width: 375px; }

.ce-section-slot-wrapper { display:flex; flex-wrap:wrap; }
.ce-canvas.mobile .ce-section-slot-wrapper{ flex-direction:column !important; }

/* Rich text mini editor */
.ce-text-toolbar{display:flex;flex-wrap:wrap;gap:.5em;align-items:center;margin-bottom:6px;}
.ce-text-toolbar button,.ce-text-toolbar select,.ce-text-toolbar input[type="color"]{
  margin-right:4px;padding:2px 6px;font-size:14px;border:1px solid var(--ce-border);background:#fff;border-radius:4px;cursor:pointer;
}
.ce-text-toolbar .ce-font-controls{display:flex;gap:.25em;}
.ce-text-toolbar select{padding:.2em .4em;}
.ce-text-toolbar input[type="color"]{width:1.5em;height:1.5em;padding:0;border:none;border-radius:0;}
.ce-text-area{border:1px solid var(--ce-border);padding:6px;min-height:200px;margin-top:.5em;border-radius:4px;outline:none;}
</style>
</head>
<body>

<!-- TABS -->
<div class="ce-tabs" id="topTabs">
  <button class="ce-tab-btn ce-active" data-tab="editor"><i class="fa-solid fa-pen-ruler"></i>Editor</button>
  <button class="ce-tab-btn" data-tab="code"><i class="fa-solid fa-code"></i>Code</button>
  <button class="ce-tab-btn" data-tab="media"><i class="fa-solid fa-photo-film"></i>Media</button>
  <button class="ce-tab-btn ce-action" id="ceSave"><i class="fa-solid fa-file-export"></i>Export HTML</button>
</div>

<!-- EDITOR TAB -->
<div class="ce-tab-pane ce-active" id="tab-editor">
  <div class="ce-editor">
    <aside class="ce-inspector">
      <div class="ce-panel-header">Properties</div>
      <div class="ce-inspector-actions">
        <button id="ceUndo" class="ce-btn-sm" title="Undo (Ctrl+Z)"><i class="fa-solid fa-rotate-left"></i></button>
        <button id="ceRedo" class="ce-btn-sm" title="Redo (Ctrl+Shift+Z)"><i class="fa-solid fa-rotate-right"></i></button>
      </div>
      <div class="ce-inspector-body" id="ceInspector">
        <p class="ce-muted">Select a block to edit.</p>
      </div>
    </aside>

    <main class="ce-canvas-wrap">
      <div class="ce-device-preview">
        <button class="ce-device-btn active" data-device="desktop"><i class="fa-solid fa-desktop"></i> Desktop</button>
        <button class="ce-device-btn" data-device="tablet"><i class="fa-solid fa-tablet"></i> Tablet</button>
        <button class="ce-device-btn" data-device="mobile"><i class="fa-solid fa-mobile"></i> Mobile</button>
      </div>
      <!-- EDITING CANVAS -->
      <div id="ceCanvasEdit" class="ce-canvas desktop" data-placeholder="Drag components here"></div>
      <!-- CLEAN EXPORT MIRROR -->
      <div id="ceCanvasExport" class="ce-canvas"></div>
      <textarea id="ceDraftExport" style="display:none;"></textarea>
    </main>

    <aside class="ce-components-panel">
      <div class="ce-panel-header">Components</div>

      <div class="ce-comp-tabs">
        <button class="ce-comp-tab-btn ce-active" data-list="elements">Elements</button>
        <button class="ce-comp-tab-btn" data-list="sections">Sections</button>
      </div>

      <div class="ce-components-list ce-active" id="list-elements">

        <!-- =========================
             TEXT BASICS
        ========================== -->
        {{-- <div class="ce-component" draggable="true" data-key="ce-personalized-greeting"
          data-html="<p style='margin:0 0 12px 0; line-height:1.5; font-size:1rem;'>Hi %%first_name%%,</p>">
          <i class="fa-solid fa-hand-wave"></i> Personalized Greeting
        </div> --}}
      
        <div class="ce-component" draggable="true" data-key="ce-heading"
          data-html="<h2 style='margin:0 0 12px 0;'>Your Heading</h2>">
          <i class="fa-solid fa-heading"></i> Heading
        </div>
      
        {{-- <div class="ce-component" draggable="true" data-key="ce-subtitle"
          data-html="<h3 style='margin:0 0 12px 0;font-size:1.1rem;color:#374151;font-weight:600;'>Sub Title</h3>">
          <i class="fa-solid fa-heading"></i> Sub Title
        </div> --}}
      
        <div class="ce-component" draggable="true" data-key="ce-paragraph"
          data-html="<p style='margin:0 0 12px 0;line-height:1.5;'>Your paragraph text goes here…</p>">
          <i class="fa-solid fa-font"></i> Paragraph
        </div>
      
        <div class="ce-component" draggable="true" data-key="ce-divider"
          data-html="<hr style='border:0;border-top:2px solid #e5e7eb;margin:16px 0;'/>">
          <i class="fa-solid fa-minus"></i> Divider
        </div>
      
        <!-- =========================
             LISTS
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-ul"
          data-html="<ul style='margin:0 0 12px 18px;padding:0;'><li>List item 1</li><li>List item 2</li></ul>">
          <i class="fa-solid fa-list-ul"></i> Unordered List
        </div>
      
        <div class="ce-component" draggable="true" data-key="ce-ol"
          data-html="<ol style='margin:0 0 12px 18px;padding:0;'><li>List item 1</li><li>List item 2</li></ol>">
          <i class="fa-solid fa-list-ol"></i> Ordered List
        </div>
      
        <!-- =========================
             MEDIA
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-image"
          data-html="<img src='https://placehold.co/600x200' alt='image' style='max-width:100%;height:auto;display:inline-block;margin:0 0 12px 0;'/>">
          <i class="fa-solid fa-image"></i> Image
        </div>
      
        <div class="ce-component" draggable="true" data-key="ce-image-caption"
          data-html="<figure style='margin:0 0 12px 0;text-align:center;'><img src='https://placehold.co/600x260' alt='image' style='max-width:100%;height:auto;display:inline-block;margin:0;border-radius:6px;'/><figcaption style='margin-top:6px;font-size:12px;color:#6b7280;line-height:1.4;'>Image caption goes here…</figcaption></figure>">
          <i class="fa-regular fa-closed-captioning"></i> Image + Caption
        </div>
      
        <div class="ce-component" draggable="true" data-key="ce-gallery"
          data-html="<div class='ce-gallery' data-gallery='simple' style='display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:0 0 12px 0;'><img src='https://placehold.co/300x200' alt='Gallery image' style='width:100%;height:auto;display:block;border-radius:6px;'/><img src='https://placehold.co/300x200' alt='Gallery image' style='width:100%;height:auto;display:block;border-radius:6px;'/><img src='https://placehold.co/300x200' alt='Gallery image' style='width:100%;height:auto;display:block;border-radius:6px;'/></div>">
          <i class="fa-regular fa-images"></i> Gallery
        </div>
      
        <div class="ce-component" draggable="true" data-key="ce-gallery-caption"
          data-html="<div class='ce-gallery ce-gallery-captions' data-gallery='captions' style='display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:0 0 12px 0;'><figure style='margin:0;'><img src='https://placehold.co/400x260' alt='Gallery image' style='width:100%;height:auto;display:block;border-radius:6px;'/><figcaption style='margin-top:6px;font-size:12px;color:#6b7280;line-height:1.4;'>Caption 1</figcaption></figure><figure style='margin:0;'><img src='https://placehold.co/400x260' alt='Gallery image' style='width:100%;height:auto;display:block;border-radius:6px;'/><figcaption style='margin-top:6px;font-size:12px;color:#6b7280;line-height:1.4;'>Caption 2</figcaption></figure></div>">
          <i class="fa-solid fa-images"></i> Gallery + Captions
        </div>

        <!-- VIDEO -->
        <div class="ce-component" draggable="true" data-key="ce-video"
        data-html="
        <figure class='ce-video-wrap' style='margin:0 0 12px 0;text-align:center;'>
          <div class='ce-video-title' style='font-weight:600;margin-bottom:8px;'>Video Title</div>
          <div class='ce-video-frame' style='position:relative;padding-top:56.25%;'>
            <iframe
              src='https://www.youtube.com/embed/dQw4w9WgXcQ'
              style='position:absolute;top:0;left:0;width:100%;height:100%;border:0;border-radius:6px;'
              allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'
              allowfullscreen>
            </iframe>
          </div>
          <figcaption class='ce-video-caption' style='margin-top:8px;font-size:12px;color:#6b7280;'>
            Video caption goes here…
          </figcaption>
        </figure>">
        <i class="fa-solid fa-video"></i> Video Embed
      </div>
      

      
        <!-- =========================
             CTA / LINKS
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-button"
          data-html="<a href='#' style='display:inline-block;padding:10px 18px;background:#6366f1;color:#fff;border:1px solid #6366f1;border-radius:4px;text-decoration:none;margin:0 0 12px 0;'>Click me</a>">
          <i class="fa-solid fa-link"></i> Button
        </div>
      
        <!-- =========================
             DATA / STRUCTURED
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-table"
          data-html="<table class='ce-table' style='width:100%;border-collapse:collapse;margin:0 0 12px 0;font-size:14px;'><thead><tr><th style='border:1px solid #e5e7eb;padding:10px;text-align:left;background:#f9fafb;'>Header 1</th><th style='border:1px solid #e5e7eb;padding:10px;text-align:left;background:#f9fafb;'>Header 2</th><th style='border:1px solid #e5e7eb;padding:10px;text-align:left;background:#f9fafb;'>Header 3</th></tr></thead><tbody><tr><td style='border:1px solid #e5e7eb;padding:10px;'>Cell</td><td style='border:1px solid #e5e7eb;padding:10px;'>Cell</td><td style='border:1px solid #e5e7eb;padding:10px;'>Cell</td></tr><tr><td style='border:1px solid #e5e7eb;padding:10px;'>Cell</td><td style='border:1px solid #e5e7eb;padding:10px;'>Cell</td><td style='border:1px solid #e5e7eb;padding:10px;'>Cell</td></tr></tbody></table>">
          <i class="fa-solid fa-table"></i> Table
        </div>
      
        <div class="ce-component" draggable="true" data-key="ce-tags"
          data-html="<div class='ce-tags' style='margin:0 0 12px 0;display:flex;flex-wrap:wrap;gap:8px;'><span class='ce-tag' style='display:inline-block;padding:6px 10px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:12px;line-height:1;'>Tag 1</span><span class='ce-tag' style='display:inline-block;padding:6px 10px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:12px;line-height:1;'>Tag 2</span><span class='ce-tag' style='display:inline-block;padding:6px 10px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:12px;line-height:1;'>Tag 3</span></div>">
          <i class="fa-solid fa-tags"></i> Tags
        </div>
      
        <!-- =========================
             ENGAGEMENT
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-faq"
          data-html="<div class='ce-faq' style='margin:0 0 12px 0;'><div class='ce-faq-item' style='border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin:0 0 10px 0;'><div class='ce-faq-q' style='font-weight:700;margin:0 0 6px 0;'>Question 1?</div><div class='ce-faq-a' style='margin:0;color:#374151;line-height:1.5;'>Answer goes here.</div></div><div class='ce-faq-item' style='border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin:0;'><div class='ce-faq-q' style='font-weight:700;margin:0 0 6px 0;'>Question 2?</div><div class='ce-faq-a' style='margin:0;color:#374151;line-height:1.5;'>Answer goes here.</div></div></div>">
          <i class="fa-solid fa-circle-question"></i> FAQ
        </div>
      
        <!-- =========================
             SOCIAL
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-social"
          data-html="<div class='cs-social-links' style='margin:0 0 12px 0;display:flex;align-items:center;gap:12px;'><a href='#' style='text-decoration:none;'><i class='fa-brands fa-facebook-f'></i></a><a href='#' style='text-decoration:none;'><i class='fa-brands fa-x-twitter'></i></a><a href='#' style='text-decoration:none;'><i class='fa-brands fa-instagram'></i></a><a href='#' style='text-decoration:none;'><i class='fa-brands fa-linkedin-in'></i></a><a href='#' style='text-decoration:none;'><i class='fa-brands fa-youtube'></i></a></div>">
          <i class="fa-solid fa-share-nodes"></i> Social Media
        </div>
      
        <!-- =========================
             ADVANCED / CUSTOM
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-html"
          data-html="<div style='margin:0 0 12px 0;'>Custom HTML here</div>">
          <i class="fa-solid fa-code"></i> Custom HTML
        </div>
      
        <!-- =========================
             FOOTER
        ========================== -->
        <div class="ce-component" draggable="true" data-key="ce-footer"
          data-html="<div style='padding:12px;background:#f3f4f6;font-size:12px;text-align:center;color:#666;'>&copy; 2025 Your Company · <a href='#' style='color:inherit;text-decoration:underline;'>Unsubscribe</a></div>">
          <i class="fa-solid fa-flag"></i> Footer
        </div>
      
      </div>
      

      <div class="ce-components-list" id="list-sections">
        <div class="ce-component" draggable="true" data-key="ce-section-1" data-html="<section style='margin:0 auto;padding:12px 0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;'><div class='ce-section-slot' style='flex:1;min-width:100%;position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>"><i class='fa-solid fa-square'></i> 1 Column</div>
        <div class="ce-component" draggable="true" data-key="ce-section-2" data-html="<section style='margin:0 auto;padding:12px 0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px;'><div class='ce-section-slot' style='flex:1 1 calc(50% - 8px);min-width:calc(50% - 8px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1 1 calc(50% - 8px);min-width:calc(50% - 8px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>"><i class='fa-solid fa-table-columns'></i> 2 Columns</div>
        <div class="ce-component" draggable="true" data-key="ce-section-3" data-html="<section style='margin:0 auto;padding:12px 0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px;'><div class='ce-section-slot' style='flex:1 1 calc(33.333% - 11px);min-width:calc(33.333% - 11px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1 1 calc(33.333% - 11px);min-width:calc(33.333% - 11px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1 1 calc(33.333% - 11px);min-width:calc(33.333% - 11px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>"><i class='fa-solid fa-border-all'></i> 3 Columns</div>
        <div class="ce-component" draggable="true" data-key="ce-section-4" data-html="<section style='margin:0 auto;padding:12px 0;'><div class='ce-section-slot-wrapper' style='width:100%!important;display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px;'><div class='ce-section-slot' style='flex:1 1 calc(25% - 12px);min-width:calc(25% - 12px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1 1 calc(25% - 12px);min-width:calc(25% - 12px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1 1 calc(25% - 12px);min-width:calc(25% - 12px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div><div class='ce-section-slot' style='flex:1 1 calc(25% - 12px);min-width:calc(25% - 12px);position:relative;display:flex;flex-direction:column;'><div class='ce-slot'></div><span class='ce-add-inside'><i class='fa-solid fa-plus'></i> Add content</span></div></div></section>"><i class='fa-solid fa-grip-lines'></i> 4 Columns</div>
      </div>
      
    </aside>
  </div>
</div>

<!-- CODE TAB -->
<div class="ce-tab-pane ce-code-pane" id="tab-code">
  <div class="ce-code-wrap">
    <div class="ce-code-left">
      <div class="ce-panel-header" style="border:none;background:transparent;padding:0 0 8px 0;">Preview</div>
      <iframe id="ceCodePreview" style="width:100%;height:100%;border:1px solid var(--ce-border);border-radius:6px;background:#fff;"></iframe>
    </div>
    <div class="ce-code-right">
      <textarea id="ceCodeArea"></textarea>
    </div>
  </div>
  <div class="ce-code-actions">
    <button type="button" class="ce-btn-sm" id="ceCodeRefresh"><i class="fa-solid fa-rotate-right"></i> Refresh</button>
    <button type="button" class="ce-btn-sm ce-primary" id="ceCodeApply"><i class="fa-solid fa-check"></i> Apply</button>
  </div>
</div>

<!-- MEDIA TAB -->
<div class="ce-tab-pane" id="tab-media" style="background:#fff;overflow:auto;">
  @include('modules.media.manageMedia')
</div>

<!-- EXPORT MODAL -->
<div id="ceModal">
  <div class="ce-modal-box">
    <h3 style="margin-top:0;margin-bottom:12px;font-size:18px;">Exported HTML</h3>
    <textarea id="ceExport"></textarea>
    <div style="text-align:right;margin-top:10px;">
      <button id="ceCloseModal" class="ce-btn-sm"><i class="fa-solid fa-xmark"></i> Close</button>
    </div>
  </div>
</div>

<script>
let importedDocDoctype = '';
let importedDocHead = '';

(function(){
  const state = {
  editEl:null,
  outEl:null,
  markerEl:null,
  selectedBlock:null,
  undoStack:[],
  redoStack:[],
  saving:false,
  currentUnit: 'px',
  currentDevice: 'desktop',
  propTabByBlockId: {},
  popupEl: null,    
  popupAnchor: null 
};


  /* ===== HISTORY ===== */
  function pushHistory(){
    if(state.saving) return;
    state.undoStack.push(state.editEl.innerHTML);
    if(state.undoStack.length>100) state.undoStack.shift();
    state.redoStack.length=0;
  }
  function undo(){
    if(!state.undoStack.length) return;
    state.redoStack.push(state.editEl.innerHTML);
    const last=state.undoStack.pop();
    state.editEl.innerHTML=last;
    rebindAll();
    syncExport();
  }
  function redo(){
    if(!state.undoStack.length) return;
    state.undoStack.push(state.editEl.innerHTML);
    const last=state.redoStack.pop();
    state.editEl.innerHTML=last;
    rebindAll();
    syncExport();
  }

  /* ===== TABS ===== */
  document.getElementById('topTabs').addEventListener('click', (e)=>{
    const btn=e.target.closest('.ce-tab-btn');
    if(!btn) return;
    if(btn.id==='ceSave'){ exportHTML(); return; }
    const tab=btn.dataset.tab;
    document.querySelectorAll('.ce-tab-btn[data-tab]').forEach(b=>b.classList.remove('ce-active'));
    btn.classList.add('ce-active');
    document.querySelectorAll('.ce-tab-pane').forEach(p=>p.classList.remove('ce-active'));
    document.getElementById('tab-'+tab).classList.add('ce-active');
    if(tab==='code'){ loadCodeFromExport(); }
  });

  /* COMPONENT TABS */
  document.querySelectorAll('.ce-comp-tab-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      document.querySelectorAll('.ce-comp-tab-btn').forEach(b=>b.classList.remove('ce-active'));
      btn.classList.add('ce-active');
      const list=btn.dataset.list;
      document.querySelectorAll('.ce-components-list').forEach(l=>l.classList.remove('ce-active'));
      document.getElementById('list-'+list).classList.add('ce-active');
    });
  });

  /* DEVICE PREVIEW */
  document.querySelectorAll('.ce-device-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const device = btn.dataset.device;
      state.currentDevice = device;
      document.querySelectorAll('.ce-device-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('ceCanvasEdit').className = 'ce-canvas ' + device;
    });
  });

  /* ===== HELPERS ===== */
  const isUI = n => n.closest && (n.closest('.ce-block-handle') || n.classList.contains('ce-add-inside'));
  function getClosestBlock(el){
    while(el && el!==state.editEl){
      if(el.classList && el.classList.contains('ce-block')) return el;
      el=el.parentElement;
    }
    return null;
  }
  function deselect(){
    if(state.selectedBlock){
      state.selectedBlock.classList.remove('ce-selected');
      state.selectedBlock=null;
      renderInspector(null);
    }
  }

  function createBlock(html,key){
    const wrap=document.createElement('div');
    wrap.className='ce-block';
    wrap.dataset.blockId='b'+Date.now()+Math.random().toString(36).slice(2);
    wrap.dataset.key=key;
    wrap.innerHTML=html;

    const handle=document.createElement('div');
    handle.className='ce-block-handle';
    handle.innerHTML=`
      <span class="ce-up" title="Move Up">▲</span>
      <span class="ce-down" title="Move Down">▼</span>
      <span class="ce-dup" title="Duplicate">⧉</span>
      <span class="ce-remove" title="Remove">✕</span>
    `;
    wrap.appendChild(handle);

    if(wrap.querySelector('.ce-slot') && !wrap.querySelector('.ce-add-inside')){
      const addBtn=document.createElement('span');
      addBtn.className='ce-add-inside';
      addBtn.innerHTML=`<i class="fa-solid fa-plus"></i> Add content`;
      const slot=wrap.querySelector('.ce-slot');
      slot.insertAdjacentElement('afterend',addBtn);
    }

    if(key==='ce-column') wrap.style.flex='1';
    attachBlockEvents(wrap);
    bindAddInside(wrap);
    bindHandle(handle, wrap);
    return wrap;
  }

  function bindHandle(handle, block){
    handle.querySelector('.ce-remove').onclick = e=>{
      e.stopPropagation(); pushHistory();
      if(state.selectedBlock===block) deselect();
      block.remove(); syncExport();
    };
    handle.querySelector('.ce-dup').onclick = e=>{
      e.stopPropagation(); pushHistory();
      const clone=block.cloneNode(true);
      clone.dataset.blockId='b'+Date.now()+Math.random().toString(36).slice(2);
      block.insertAdjacentElement('afterend',clone);
      attachBlockEvents(clone);
      bindAddInside(clone);
      clone.querySelectorAll('.ce-block-handle').forEach(h=>bindHandle(h, getClosestBlock(h)));
      syncExport();
    };
    handle.querySelector('.ce-up').onclick = e=>{ e.stopPropagation(); moveBlock(block,'up'); };
    handle.querySelector('.ce-down').onclick = e=>{ e.stopPropagation(); moveBlock(block,'down'); };
  }

  function moveBlock(block, dir){
    const sib = dir==='up' ? block.previousElementSibling : block.nextElementSibling;
    if(!sib) return;
    pushHistory();
    sib.insertAdjacentElement(dir==='up'?'beforebegin':'afterend', block);
    syncExport();
  }

  /* ✅ inline table cell editing */
  function enableCellEdit(cell){
    if(!cell) return;
    pushHistory();
    cell.setAttribute('contenteditable','true');
    cell.focus();
    const finish = ()=>{
      cell.removeAttribute('contenteditable');
      cell.removeEventListener('blur', finish);
      cell.removeEventListener('keydown', onKey);
      syncExport();
    };
    const onKey = (e)=>{
      if(e.key==='Enter' && !e.shiftKey){
        e.preventDefault();
        cell.blur();
      }
    };
    cell.addEventListener('blur', finish);
    cell.addEventListener('keydown', onKey);
  }

  function attachBlockEvents(block) {
    block.addEventListener('click', onSelectBlock);
    block.setAttribute('draggable', 'true');
    block.addEventListener('dragstart', onDragStartBlock);

    // double click to edit table cell in place
    block.addEventListener('dblclick', (e)=>{
      const cell = e.target.closest('td,th');
      if(!cell || !block.contains(cell)) return;
      e.stopPropagation();
      e.preventDefault();
      enableCellEdit(cell);
    });
  }

  function bindAddInside(container){
    container.querySelectorAll('.ce-add-inside').forEach(btn=>{
      btn.onclick = function(e){
        e.stopPropagation();
        openAddPopup(btn);
      };
    });
  }

  /* ===== ADD POPUP (FIXED) ===== */
  function openAddPopup(anchor) {
    closePopup();

    state.popupAnchor = anchor;

    const items = Array.from(document.querySelectorAll('#list-elements .ce-component')).map(el => ({
      key: el.dataset.key,
      label: el.textContent.trim()
    }));

    const popup = document.createElement('div');
    popup.className = 'ce-add-popup';
    popup.innerHTML = `<h4>Add element</h4>${items.map(i => `<button data-key="${i.key}">${i.label}</button>`).join('')}`;
    document.body.appendChild(popup);

    state.popupEl = popup;

    const rect = anchor.getBoundingClientRect();
    const margin = 6;
    let top = rect.bottom + window.scrollY + margin;
    let left = rect.left + window.scrollX;

    popup.style.top = top + 'px';
    popup.style.left = left + 'px';

    const popupHeight = popup.offsetHeight;
    const viewportBottom = window.scrollY + window.innerHeight;

    // flip up if overflowing
    if (top + popupHeight > viewportBottom) {
      top = rect.top + window.scrollY - popupHeight - margin;
      popup.style.top = top + 'px';
    }

    // ✅ prevent outside-close firing when clicking inside popup
    popup.addEventListener('pointerdown', e => e.stopPropagation());

    popup.addEventListener('click', e => {
      const btn = e.target.closest('button');
      if (!btn) return;

      const key = btn.dataset.key;
      const data = getComponentDataByKey(key);
      if (!data) return;

      pushHistory();

      if (key === 'ce-html') {
        const html = prompt('Enter custom HTML:', '<div>Custom</div>');
        if (html !== null) data.html = html;
        else { closePopup(); return; }
      }

      const block = createBlock(data.html, data.key);

      // ✅ Always detect correct slot when clicked from Section Add-content
      let slot = null;
      const sectionSlot = anchor.closest('.ce-section-slot');
      if (sectionSlot) slot = sectionSlot.querySelector('.ce-slot');

      slot = slot || anchor.previousElementSibling || anchor.parentElement.querySelector('.ce-slot') || anchor.parentElement || state.editEl;

      slot.appendChild(block);
      closePopup();
      syncExport();
    });

    // ✅ IMPORTANT FIX:
    // attach outside close AFTER current click finishes,
    // so it doesn't instantly close the popup randomly (section bug)
    setTimeout(() => {
      document.addEventListener('pointerdown', closePopup, { once: true });
    }, 0);
  }

  function closePopup(e){
    const p = state.popupEl || document.querySelector('.ce-add-popup');
    if(!p) return;

    // ✅ if clicked inside popup or on the same anchor -> ignore
    if(e){
      if(p.contains(e.target)) return;
      if(state.popupAnchor && state.popupAnchor.contains && state.popupAnchor.contains(e.target)) return;
    }

    p.remove();
    state.popupEl = null;
    state.popupAnchor = null;
  }


  function closePopup(){
    const p=document.querySelector('.ce-add-popup');
    if(p) p.remove();
  }
  function getComponentDataByKey(key){
    const el=document.querySelector(`.ce-component[data-key="${key}"]`);
    if(!el) return null;
    return {key:el.dataset.key, html:el.dataset.html};
  }

  /* ===== INSPECTOR ===== */
  function renderInspector(block){
    const panel=document.getElementById('ceInspector');
    panel.innerHTML='';

    if(!block){
      panel.innerHTML='<p class="ce-muted">Select a block to edit.</p>';
      return;
    }

    const bId = block.dataset.blockId || 'global';
    let activeTab = state.propTabByBlockId[bId] || 'style';
    if(activeTab==='actions' && !block.querySelector('.ce-slot')) activeTab = 'style';

    const tabs=document.createElement('div');
    tabs.className='ce-prop-tabs';
    tabs.innerHTML=`
      <button class="ce-prop-tab-btn ${activeTab==='style'?'ce-active':''}" data-prop="style">Styling</button>
      <button class="ce-prop-tab-btn ${activeTab==='content'?'ce-active':''}" data-prop="content">Content</button>
      ${block.querySelector('.ce-slot')?`<button class="ce-prop-tab-btn ${activeTab==='actions'?'ce-active':''}" data-prop="actions">Actions</button>`:''}
    `;
    const stylePane=document.createElement('div');stylePane.className='ce-prop-pane' + (activeTab==='style'?' ce-active':'');
    const contentPane=document.createElement('div');contentPane.className='ce-prop-pane' + (activeTab==='content'?' ce-active':'');
    const actionsPane=document.createElement('div');actionsPane.className='ce-prop-pane' + (activeTab==='actions'?' ce-active':'');

    panel.appendChild(tabs);
    panel.appendChild(stylePane);
    panel.appendChild(contentPane);
    if(block.querySelector('.ce-slot')) panel.appendChild(actionsPane);

    tabs.addEventListener('click',e=>{
      const btn=e.target.closest('.ce-prop-tab-btn');
      if(!btn) return;
      tabs.querySelectorAll('.ce-prop-tab-btn').forEach(b=>b.classList.remove('ce-active'));
      btn.classList.add('ce-active');
      const which=btn.dataset.prop;
      state.propTabByBlockId[bId] = which; /* ✅ remember selected tab */
      stylePane.classList.toggle('ce-active', which==='style');
      contentPane.classList.toggle('ce-active', which==='content');
      actionsPane.classList.toggle('ce-active', which==='actions');
    });

    const contentHTML=getInnerHTMLWithoutUI(block);
    const tagNames=['H1','H2','H3','H4','H5','H6','P','A','SPAN','BUTTON','LI'];
    const textNodes = getOutermostTextNodes(block);

    function getOutermostTextNodes(block){
      // only “main” text containers (NOT span/a inside them)
      const selector = 'h1,h2,h3,h4,h5,h6,p,figcaption';
      const all = Array.from(block.querySelectorAll(selector));

      // keep only the highest-level ones (skip nested)
      return all.filter(el => !el.parentElement.closest(selector));
    }


    const unitToggle = document.createElement('div');
    unitToggle.className = 'ce-unit-toggle';
    unitToggle.innerHTML = `
      <button class="${state.currentUnit === 'px' ? 'active' : ''}" data-unit="px">PX</button>
      <button class="${state.currentUnit === '%' ? 'active' : ''}" data-unit="%">%</button>
    `;
    stylePane.appendChild(unitToggle);

    unitToggle.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        state.currentUnit = btn.dataset.unit;
        unitToggle.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderInspector(block);
      });
    });

    addAlignField(stylePane, block);
    addSpacingField(stylePane, block);
    addRadiusField(stylePane, block);
    addColorBorderField(stylePane, block);
    if(textNodes.length){ addTypographyField(stylePane, textNodes); }
    textNodes.forEach(node=>{
      if(/^H[1-6]$/.test(node.tagName)){ addHeadingLevelField(stylePane, node, block); }
    });

// ✅ Skip social icon anchors – they are handled only by addSocialEditors()
block.querySelectorAll('a,button').forEach((el,i)=>{
  if(el.closest('.cs-social-links')) return;
  addButtonStyleField(stylePane, el, i);
});

block.querySelectorAll('a').forEach((el,i)=>{
  if(el.closest('.cs-social-links')) return;
  addButtonContentField(contentPane, el, i);
});


    addImageStyleEditors(stylePane, block);

    addTextEditors(contentPane, textNodes);
    addListEditors(contentPane, block);
    addImageContentEditors(contentPane, block);

    addGalleryEditors(contentPane, stylePane, block);
    addTableEditors(contentPane, stylePane, block);      /* ✅ row/col/header + styling */
    addTableContentEditors(contentPane, block);          /* ✅ cell content editing grid */
    addTagsEditors(contentPane, stylePane, block);
    addFAQEditors(contentPane, stylePane, block);

    addSocialEditors(contentPane, stylePane, block);     /* ✅ new social editor */
    addVideoEditors(contentPane, stylePane, block);
    if(block.dataset.key==='ce-html'){ addCustomHTMLField(contentPane, block, contentHTML); }

    if(block.querySelector('.ce-slot')){
      const f=document.createElement('div');f.className='ce-field';
      f.innerHTML=`<button class="ce-btn-sm ce-primary" id="propAddBtn"><i class="fa-solid fa-plus"></i> Add element here</button>`;
      f.querySelector('#propAddBtn').addEventListener('click',()=>openAddPopup(block.querySelector('.ce-add-inside')||block));
      actionsPane.appendChild(f);
    }
  }

  /* === styling/content helpers === */
  function addAlignField(panel, block){
  // ✅ read current align from actual block
  const cs = getComputedStyle(block);
  let currentAlign = (block.style.textAlign || cs.textAlign || 'left').toLowerCase();
  if(!['left','center','right'].includes(currentAlign)) currentAlign = 'left';

  const field = document.createElement('div');
  field.className = 'ce-field';
  field.innerHTML = `
    <label>Horizontal Align</label>
    <div class="ce-align-group">
      <button class="ce-align-btn ${currentAlign === 'left' ? 'active' : ''}" data-align="left"><i class="fa-solid fa-align-left"></i></button>
      <button class="ce-align-btn ${currentAlign === 'center' ? 'active' : ''}" data-align="center"><i class="fa-solid fa-align-center"></i></button>
      <button class="ce-align-btn ${currentAlign === 'right' ? 'active' : ''}" data-align="right"><i class="fa-solid fa-align-right"></i></button>
    </div>
  `;

  field.querySelectorAll('.ce-align-btn').forEach(b => {
    b.addEventListener('click', () => {
      pushHistory();
      const align = b.dataset.align;

      field.querySelectorAll('.ce-align-btn').forEach(x => x.classList.remove('active'));
      b.classList.add('active');

      // ✅ actually apply alignment
      block.style.textAlign = align;

      // ✅ special cases (buttons/images often need inline-block to respect text-align)
      block.querySelectorAll('a, button, img, figure').forEach(el=>{
        if(el.closest('.cs-social-links')) return; // keep social editor separate
        const d = getComputedStyle(el).display;
        if(d === 'inline' || d === 'inline-flex') el.style.display = 'inline-block';
      });

      syncExport();
    });
  });

  panel.appendChild(field);
}


  function addSpacingField(panel, block){
    const cs=getComputedStyle(block);
    const mt=parseInt(cs.marginTop)||0, mr=parseInt(cs.marginRight)||0, mb=parseInt(cs.marginBottom)||0, ml=parseInt(cs.marginLeft)||0;
    const pt=parseInt(cs.paddingTop)||0, pr=parseInt(cs.paddingRight)||0, pb=parseInt(cs.paddingBottom)||0, pl=parseInt(cs.paddingLeft)||0;

    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Margin (${state.currentUnit})</label>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:6px;">
        <input type="number" value="${mt}" data-prop="marginTop" placeholder="Top">
        <input type="number" value="${mr}" data-prop="marginRight" placeholder="Right">
        <input type="number" value="${mb}" data-prop="marginBottom" placeholder="Bottom">
        <input type="number" value="${ml}" data-prop="marginLeft" placeholder="Left">
      </div>
      <label>Padding (${state.currentUnit})</label>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">
        <input type="number" value="${pt}" data-prop="paddingTop" placeholder="Top">
        <input type="number" value="${pr}" data-prop="paddingRight" placeholder="Right">
        <input type="number" value="${pb}" data-prop="paddingBottom" placeholder="Bottom">
        <input type="number" value="${pl}" data-prop="paddingLeft" placeholder="Left">
      </div>`;

    field.querySelectorAll('input').forEach(inp=>{
      inp.addEventListener('input',e=>{
        pushHistory();
        const value = e.target.value ? e.target.value + state.currentUnit : '';
        block.style[e.target.dataset.prop] = value;
        syncExport();
      });
    });
    panel.appendChild(field);
  }

  function addRadiusField(panel, block){
    const cs=getComputedStyle(block);
    const tl=parseInt(cs.borderTopLeftRadius)||0,tr=parseInt(cs.borderTopRightRadius)||0,br=parseInt(cs.borderBottomRightRadius)||0,bl=parseInt(cs.borderBottomLeftRadius)||0;
    block.style.overflow = (tl||tr||br||bl) ? 'hidden' : '';
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Border Radius (${state.currentUnit})</label>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">
        <input type="number" value="${tl}" data-prop="borderTopLeftRadius" placeholder="Top Left">
        <input type="number" value="${tr}" data-prop="borderTopRightRadius" placeholder="Top Right">
        <input type="number" value="${br}" data-prop="borderBottomRightRadius" placeholder="Bottom Right">
        <input type="number" value="${bl}" data-prop="borderBottomLeftRadius" placeholder="Bottom Left">
      </div>`;
    field.querySelectorAll('input').forEach(inp=>{
      inp.addEventListener('input',e=>{
        pushHistory();
        const value = e.target.value ? e.target.value + state.currentUnit : '';
        block.style[e.target.dataset.prop] = value;
        syncExport();
      });
    });
    panel.appendChild(field);
  }

  function addColorBorderField(panel, block){
    const cs=getComputedStyle(block);
    const txtColor = rgb2hex(cs.color);
    const bgColor  = rgb2hex(cs.backgroundColor);
    const borderColor = rgb2hex(cs.borderColor);
    const borderWidth = parseInt(cs.borderWidth)||0;
    const borderStyle = cs.borderStyle || 'none';
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Text / Background</label>
      <div class="ce-color-pair">
        <input type="color" value="${txtColor}" title="Text color">
        <input type="color" value="${bgColor}"  title="Background color">
      </div>
      <label style="margin-top:10px;">Border</label>
      <div style="display:flex;gap:6px;margin-bottom:6px;">
        <input type="number" min="0" value="${borderWidth}" style="width:60px;" title="Width (px)">
        <select style="flex:1;">
          <option value="none"   ${borderStyle==='none'?'selected':''}>none</option>
          <option value="solid"  ${borderStyle==='solid'?'selected':''}>solid</option>
          <option value="dashed" ${borderStyle==='dashed'?'selected':''}>dashed</option>
          <option value="dotted" ${borderStyle==='dotted'?'selected':''}>dotted</option>
        </select>
        <input type="color" value="${borderColor}" title="Border color" style="width:34px;height:34px;padding:0;border:none;">
      </div>`;
    const [txtInp,bgInp]=field.querySelectorAll('.ce-color-pair input');
    const inputs = field.querySelectorAll('input,select');
    const bWidth=inputs[2], bStyle=inputs[3], bColor=inputs[4];
    txtInp.addEventListener('input',e=>{ pushHistory(); block.style.color=e.target.value; syncExport();});
    bgInp.addEventListener('input',e=>{ pushHistory(); block.style.backgroundColor=e.target.value; syncExport();});
    bWidth.addEventListener('input',e=>{ pushHistory(); block.style.borderWidth=e.target.value+'px'; syncExport();});
    bStyle.addEventListener('change',e=>{ pushHistory(); block.style.borderStyle=e.target.value; syncExport();});
    bColor.addEventListener('input',e=>{ pushHistory(); block.style.borderColor=e.target.value; syncExport();});
    panel.appendChild(field);
  }

  function addTypographyField(panel, nodes){
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Typography</label>
      <div style="display:flex;gap:6px;margin-bottom:6px;">
        <select class="ce-font-family" style="flex:1;">
          <option value="">Default</option>
          <option value="Inter, sans-serif">Inter</option>
          <option value="Poppins, sans-serif">Poppins</option>
          <option value="Arial, sans-serif">Arial</option>
          <option value="Georgia, serif">Georgia</option>
          <option value="'Times New Roman', serif">Times New Roman</option>
        </select>
        <input type="number" class="ce-font-size" min="8" max="96" placeholder="px" style="width:70px;" />
      </div>
      <div class="ce-typo-tools">
        <button class="ce-typo-btn" data-style="bold"><i class="fa-solid fa-bold"></i></button>
        <button class="ce-typo-btn" data-style="italic"><i class="fa-solid fa-italic"></i></button>
        <button class="ce-typo-btn" data-style="underline"><i class="fa-solid fa-underline"></i></button>
      </div>`;
    const ff=field.querySelector('.ce-font-family');
    const fs=field.querySelector('.ce-font-size');
    ff.addEventListener('change',e=>{ pushHistory(); nodes.forEach(n=>n.style.fontFamily=e.target.value||''); syncExport();});
    fs.addEventListener('input',e=>{ pushHistory(); nodes.forEach(n=> n.style.fontSize=e.target.value?e.target.value+'px':''); syncExport();});
    field.querySelectorAll('.ce-typo-btn').forEach(btn=>{
      btn.addEventListener('click',()=>{
        pushHistory();
        const st=btn.dataset.style;
        btn.classList.toggle('active');
        nodes.forEach(n=>{
          if(st==='bold') n.style.fontWeight=btn.classList.contains('active')?'700':'';
          if(st==='italic') n.style.fontStyle=btn.classList.contains('active')?'italic':'';
          if(st==='underline') n.style.textDecoration=btn.classList.contains('active')?'underline':'';
        });
        syncExport();
      });
    });
    panel.appendChild(field);
  }

  function addHeadingLevelField(panel,node,block){
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Heading Level</label>
      <select class="ce-heading-level">
        <option value="H1">H1</option><option value="H2">H2</option><option value="H3">H3</option>
        <option value="H4">H4</option><option value="H5">H5</option><option value="H6">H6</option>
      </select>`;
    const sel=field.querySelector('.ce-heading-level');
    sel.value=node.tagName;
    sel.addEventListener('change',e=>{
      pushHistory();
      const newTag=e.target.value;
      const newNode=document.createElement(newTag);
      Array.from(node.attributes).forEach(a=>newNode.setAttribute(a.name,a.value));
      newNode.innerHTML=node.innerHTML;
      node.parentNode.replaceChild(newNode,node);
      renderInspector(block);
      syncExport();
    });
    panel.appendChild(field);
  }

  function addButtonStyleField(panel, el, idx){
    const cs=getComputedStyle(el);
    const bg=rgb2hex(cs.backgroundColor), color=rgb2hex(cs.color), bW=parseInt(cs.borderWidth)||0, bCol=rgb2hex(cs.borderColor), bSty=cs.borderStyle||'none', bRad=parseInt(cs.borderTopLeftRadius)||0;
    const field=document.createElement('div');field.className='ce-field';
    field.innerHTML=`<label>Link/Button ${idx+1} Colors</label>
      <div class="ce-color-pair" style="margin-bottom:6px;">
        <input type="color" value="${bg}" title="Background">
        <input type="color" value="${color}" title="Text">
      </div>
      <label>Border</label>
      <div style="display:flex;gap:6px;margin-bottom:6px;">
        <input type="number" min="0" value="${bW}" style="width:60px;" data-btn-prop="borderWidth">
        <select style="flex:1;" data-btn-prop="borderStyle">
          <option value="none" ${bSty==='none'?'selected':''}>none</option>
          <option value="solid" ${bSty==='solid'?'selected':''}>solid</option>
          <option value="dashed" ${bSty==='dashed'?'selected':''}>dashed</option>
          <option value="dotted" ${bSty==='dotted'?'selected':''}>dotted</option>
          <option value="double" ${bSty==='double'?'selected':''}>double</option>
        </select>
        <input type="color" value="${bCol}" style="width:34px;height:34px;padding:0;border:none;" data-btn-prop="borderColor">
      </div>
      <label>Border Radius (${state.currentUnit})</label>
      <input type="number" min="0" value="${bRad}" data-btn-prop="borderRadius">`;
    const [bgInp,txtInp]=field.querySelectorAll('.ce-color-pair input');
    bgInp.addEventListener('input',e=>{ pushHistory(); el.style.backgroundColor=e.target.value; el.style.borderColor=e.target.value; syncExport();});
    txtInp.addEventListener('input',e=>{ pushHistory(); el.style.color=e.target.value; syncExport();});
    field.querySelectorAll('[data-btn-prop]').forEach(inp=>{
      inp.addEventListener('input',e=>{
        pushHistory();
        const p=e.target.dataset.btnProp;
        let v=e.target.value;
        if(p==='borderWidth'||p==='borderRadius') v = v ? v + (p === 'borderRadius' ? state.currentUnit : 'px') : '';
        el.style[p]=v; syncExport();
      });
      if(inp.tagName==='SELECT'){
        inp.addEventListener('change',e=>{ pushHistory(); el.style[e.target.dataset.btnProp]=e.target.value; syncExport();});
      }
    });
    panel.appendChild(field);
  }

  function addButtonContentField(panel, el, idx) {
    const field = document.createElement('div');
    field.className = 'ce-field';
    field.innerHTML = `
      <label>Link ${idx+1} URL</label>
      <input type="text" class="ce-btn-link-url" placeholder="https://example.com" value="${el.getAttribute('href')||''}" />
      <div style="margin-top:8px;">
        <label style="display:flex;gap:8px;align-items:center;">
          <input type="checkbox" class="ce-btn-link-target" ${el.target === '_blank' ? 'checked' : ''}/>
          Open in new tab
        </label>
      </div>
    `;
    const urlInput = field.querySelector('.ce-btn-link-url');
    const targetCheckbox = field.querySelector('.ce-btn-link-target');

    urlInput.addEventListener('input', e => {
      pushHistory();
      el.setAttribute('href', e.target.value);
      syncExport();
    });

    targetCheckbox.addEventListener('change', e => {
      pushHistory();
      if (e.target.checked) {
        el.setAttribute('target', '_blank');
        el.setAttribute('rel', 'noopener noreferrer');
      } else {
        el.removeAttribute('target');
        el.removeAttribute('rel');
      }
      syncExport();
    });

    panel.appendChild(field);
  }

  function addImageStyleEditors(panel, block){
    const imgs=block.querySelectorAll('img');
    imgs.forEach((img, idx)=>{
      if(isUI(img)) return;
      const parent = img.parentElement;
      const parentAlign = getComputedStyle(parent).textAlign;
      let current = parentAlign==='center' ? 'center' : (parentAlign==='right' ? 'right' : 'left');
      const alignWrap=document.createElement('div');alignWrap.className='ce-field';
      alignWrap.innerHTML=`<label>Image ${idx+1} Align</label>
        <div class="ce-img-align">
          <button class="ce-img-align-btn ${current==='left'?'active':''}" data-img-align="left"><i class="fa-solid fa-align-left"></i></button>
          <button class="ce-img-align-btn ${current==='center'?'active':''}" data-img-align="center"><i class="fa-solid fa-align-center"></i></button>
          <button class="ce-img-align-btn ${current==='right'?'active':''}" data-img-align="right"><i class="fa-solid fa-align-right"></i></button>
        </div>`;
      alignWrap.querySelectorAll('.ce-img-align-btn').forEach(btn=>{
        btn.addEventListener('click',()=>{
          pushHistory();
          const val=btn.dataset.imgAlign;
          img.style.display='inline-block';
          parent.style.textAlign=val;
          alignWrap.querySelectorAll('.ce-img-align-btn').forEach(x=>x.classList.remove('active'));
          btn.classList.add('active');
          syncExport();
        });
      });
      panel.appendChild(alignWrap);
    });
  }

    function addTextEditors(panel, nodes) {
    nodes.forEach((node, idx) => {
      if (isUI(node)) return;
      if (node.classList && node.classList.contains('ce-tag')) return;
      if (node.closest && node.closest('.cs-social-links')) return;

      // ✅ IMPORTANT FIX: skip LI (list items will be handled by addListEditors)
      if (node.tagName === 'LI') return;

      const wrap = document.createElement('div');
      wrap.className = 'ce-field';
      wrap.innerHTML = `
        <label>Text ${idx + 1}</label>

        <div class="ce-text-toolbar">
          <button type="button" data-cmd="bold"><b>B</b></button>
          <button type="button" data-cmd="italic"><i>I</i></button>
          <button type="button" data-cmd="underline"><u>U</u></button>
          <button type="button" data-cmd="strikethrough"><s>S</s></button>

          <!-- ✅ NEW: List Buttons -->
          <button type="button" data-cmd="insertUnorderedList" title="Bullet List">• List</button>
          <button type="button" data-cmd="insertOrderedList" title="Numbered List">1. List</button>

          <button type="button" data-cmd="createLink">🔗</button>
          <input type="color" data-cmd="foreColor" title="Text color">

          <div class="ce-font-controls">
            <select data-cmd="fontName">
              <option value="Arial">Arial</option>
              <option value="Book Antiqua">Book Antiqua</option>
              <option value="Cambria">Cambria</option>
              <option value="Century Schoolbook">Century Schoolbook</option>
              <option value="Georgia">Georgia</option>
              <option value="Tahoma">Tahoma</option>
              <option value="Times New Roman">Times New Roman</option>
              <option value="Verdana">Verdana</option>
            </select>

            <select data-cmd="fontSize">
              <option value="1">10px</option>
              <option value="2">13px</option>
              <option value="3" selected>16px</option>
              <option value="4">18px</option>
              <option value="5">24px</option>
              <option value="6">32px</option>
              <option value="7">48px</option>
            </select>
          </div>
        </div>

        <div class="ce-text-area" contenteditable="true">${node.innerHTML}</div>
      `;

      // ✅ Buttons work automatically (UL/OL will work too)
      wrap.querySelectorAll('.ce-text-toolbar button').forEach(btn => {
        btn.addEventListener('click', () => {
          const cmd = btn.dataset.cmd;
          let val = null;

          if (cmd === 'createLink') {
            val = prompt('Enter URL:', 'https://');
            if (!val) return;
          }

          document.execCommand(cmd, false, val);
          wrap.querySelector('.ce-text-area').focus();
        });
      });

      wrap.querySelectorAll('.ce-text-toolbar select, .ce-text-toolbar input[type="color"]').forEach(ctrl => {
        ctrl.addEventListener('change', e => {
          document.execCommand(ctrl.dataset.cmd, false, e.target.value);
          wrap.querySelector('.ce-text-area').focus();
        });
      });

      const editable = wrap.querySelector('.ce-text-area');
      editable.addEventListener('input', () => {
        pushHistory();
        node.innerHTML = editable.innerHTML;
        syncExport();
      });

      panel.appendChild(wrap);
    });
  }



  function addListEditors(panel, block){
    const lists = block.querySelectorAll('ul,ol');
    lists.forEach((list, idx)=>{
      if(isUI(list)) return;

      const field=document.createElement('div');
      field.className='ce-field';
      field.innerHTML=`
        <label>List ${idx+1} Items</label>
        <div class="ce-list-items" style="display:flex;flex-direction:column;gap:10px;margin-bottom:8px;"></div>
        <button type="button" class="ce-btn-sm ce-primary ce-add-li">
          <i class="fa-solid fa-plus"></i> Add item
        </button>
      `;

      const itemsWrap = field.querySelector('.ce-list-items');

      function makeEditorRow(li){
        const row = document.createElement('div');
        row.style.border = '1px solid var(--ce-border)';
        row.style.borderRadius = '6px';
        row.style.padding = '8px';
        row.style.background = '#fff';

        row.innerHTML = `
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <strong style="font-size:12px;">Item</strong>
            <button type="button" class="ce-btn-sm ce-del-li" style="margin-left:auto;">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>

          <div class="ce-text-toolbar">
            <button type="button" data-cmd="bold"><b>B</b></button>
            <button type="button" data-cmd="italic"><i>I</i></button>
            <button type="button" data-cmd="underline"><u>U</u></button>
            <button type="button" data-cmd="strikethrough"><s>S</s></button>

            <!-- ✅ NEW: List buttons inside LI editor -->
            <button type="button" data-cmd="insertUnorderedList" title="Bullet List">• List</button>
            <button type="button" data-cmd="insertOrderedList" title="Numbered List">1. List</button>

            <button type="button" data-cmd="createLink">🔗</button>
            <input type="color" data-cmd="foreColor" title="Text color">

            <div class="ce-font-controls">
              <select data-cmd="fontName">
                <option value="Arial">Arial</option>
                <option value="Book Antiqua">Book Antiqua</option>
                <option value="Cambria">Cambria</option>
                <option value="Century Schoolbook">Century Schoolbook</option>
                <option value="Georgia">Georgia</option>
                <option value="Tahoma">Tahoma</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Verdana">Verdana</option>
              </select>

              <select data-cmd="fontSize">
                <option value="1">10px</option>
                <option value="2">13px</option>
                <option value="3" selected>16px</option>
                <option value="4">18px</option>
                <option value="5">24px</option>
                <option value="6">32px</option>
                <option value="7">48px</option>
              </select>
            </div>
          </div>

          <div class="ce-text-area ce-li-editor" contenteditable="true">${li.innerHTML}</div>
        `;

        const editor = row.querySelector('.ce-li-editor');

        // Toolbar actions
        row.querySelectorAll('.ce-text-toolbar button').forEach(btn=>{
          btn.addEventListener('click', ()=>{
            editor.focus();
            const cmd = btn.dataset.cmd;
            let val = null;

            if(cmd === 'createLink'){
              val = prompt('Enter URL:', 'https://');
              if(!val) return;
            }

            document.execCommand(cmd, false, val);
            editor.focus();
          });
        });

        row.querySelectorAll('.ce-text-toolbar select, .ce-text-toolbar input[type="color"]').forEach(ctrl=>{
          ctrl.addEventListener('change', e=>{
            editor.focus();
            document.execCommand(ctrl.dataset.cmd, false, e.target.value);
            editor.focus();
          });
        });

        // Live sync to LI HTML
        let armed=false;
        editor.addEventListener('input', ()=>{
          if(!armed){ pushHistory(); armed=true; }
          li.innerHTML = editor.innerHTML;
          syncExport();
        });
        editor.addEventListener('blur', ()=>{ armed=false; });

        // Delete
        row.querySelector('.ce-del-li').addEventListener('click', ()=>{
          pushHistory();
          li.remove();
          row.remove();
          syncExport();
        });

        return row;
      }

      // Build existing items
      Array.from(list.children).forEach(li=>{
        if(li.tagName !== 'LI') return;
        itemsWrap.appendChild(makeEditorRow(li));
      });

      // Add item (✅ creates editor also)
      field.querySelector('.ce-add-li').addEventListener('click', ()=>{
        pushHistory();
        const li=document.createElement('li');
        li.innerHTML = 'New item';
        list.appendChild(li);
        itemsWrap.appendChild(makeEditorRow(li));
        syncExport();
      });

      panel.appendChild(field);
    });
  }



  function addImageContentEditors(panel, block){
    const imgs=block.querySelectorAll('img');
    imgs.forEach((img, idx)=>{
      if(isUI(img)) return;

      // URL
      const wrap=document.createElement('div');wrap.className='ce-field';
      wrap.innerHTML=`<label>Image ${idx+1} URL</label><input type="text" value="${img.src}">`;
      wrap.querySelector('input').addEventListener('input',e=>{
        pushHistory(); img.src=e.target.value; syncExport();
      });
      panel.appendChild(wrap);

      // Caption editor if inside figure
      const fig = img.closest('figure');
      const cap = fig ? fig.querySelector('figcaption') : null;
      if(cap){
        const capWrap=document.createElement('div');capWrap.className='ce-field';
        capWrap.innerHTML=`<label>Caption</label><textarea>${cap.innerHTML}</textarea>`;
        capWrap.querySelector('textarea').addEventListener('input', e=>{
          pushHistory();
          cap.innerHTML = e.target.value;
          syncExport();
        });
        panel.appendChild(capWrap);
      }

      // size + unit
      const sizeWrap=document.createElement('div'); sizeWrap.className='ce-field';
      const w=img.getAttribute('width')||''; const h=img.getAttribute('height')||'';
      sizeWrap.innerHTML=`<label>Width / Height</label>
        <div style="display:flex;gap:6px;">
          <input type="number" min="0" placeholder="W" value="${parseInt(w)||''}" style="flex:1;">
          <input type="number" min="0" placeholder="H" value="${parseInt(h)||''}" style="flex:1;">
        </div>
        <div class="ce-unit-toggle" style="margin-top:6px;">
          <button class="${w.toString().includes('%') ? 'active' : ''}" data-unit="%">%</button>
          <button class="${w.toString().includes('px') || !w ? 'active' : ''}" data-unit="px">PX</button>
        </div>`;
      const [wInp,hInp]=sizeWrap.querySelectorAll('input');
      const unitToggle = sizeWrap.querySelector('.ce-unit-toggle');
      let currentUnit = w.toString().includes('%') ? '%' : 'px';

      wInp.addEventListener('input',e=>{
        pushHistory();
        const value = e.target.value ? e.target.value + currentUnit : '';
        if(value) img.setAttribute('width', value); else img.removeAttribute('width');
        syncExport();
      });
      hInp.addEventListener('input',e=>{
        pushHistory();
        const value = e.target.value ? e.target.value + currentUnit : '';
        if(value) img.setAttribute('height', value); else img.removeAttribute('height');
        syncExport();
      });

      unitToggle.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
          currentUnit = btn.dataset.unit;
          unitToggle.querySelectorAll('button').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const wVal = wInp.value ? wInp.value + currentUnit : '';
          const hVal = hInp.value ? hInp.value + currentUnit : '';
          if (wVal) img.setAttribute('width', wVal); else img.removeAttribute('width');
          if (hVal) img.setAttribute('height', hVal); else img.removeAttribute('height');
          syncExport();
        });
      });

      panel.appendChild(sizeWrap);
    });
  }

  /* ===== CLEAN & SYNC ===== */
  function getInnerHTMLWithoutUI(el){
    const clone=el.cloneNode(true);
    clone.querySelectorAll('.ce-block-handle,.ce-add-inside,.ce-drop-marker').forEach(x=>x.remove());
    return clone.innerHTML.trim();
  }

  function cleanCloneFromEdit(){
    const clone=state.editEl.cloneNode(true);
    clone.querySelectorAll('.ce-block-handle,.ce-add-inside,.ce-drop-marker').forEach(x=>x.remove());
    clone.querySelectorAll('.ce-block').forEach(b=>{
      b.classList.remove('ce-block','ce-selected','ce-prop-active');
      b.removeAttribute('data-block-id');
      b.removeAttribute('data-key');
    });
    clone.querySelectorAll('.ce-slot').forEach(s=>s.replaceWith(...s.childNodes));
    return clone;
  }

  function syncExport(){
    const cleaned = cleanCloneFromEdit();
    state.outEl.innerHTML = cleaned.innerHTML;
    document.getElementById('ceDraftExport').value = state.editEl.innerHTML;
  }

  function rebuildEditFromExport(html){
    state.editEl.innerHTML='';
    const tmp=document.createElement('div'); tmp.innerHTML=html;
    Array.from(tmp.childNodes).forEach(n=>{
      if(n.nodeType===1){
        const wrap=createBlock(n.outerHTML,'custom');
        state.editEl.appendChild(wrap);
      }else if(n.nodeType===3 && n.textContent.trim()){
        const wrap=createBlock(`<p>${n.textContent}</p>`,'ce-paragraph');
        state.editEl.appendChild(wrap);
      }
    });
    rebindAll();
    syncExport();
  }

  function rgb2hex(rgb){
    if(!rgb || rgb==='transparent') return '#ffffff';
    const m=rgb.match(/\d+/g); if(!m) return '#ffffff';
    const r=parseInt(m[0]).toString(16).padStart(2,'0');
    const g=parseInt(m[1]).toString(16).padStart(2,'0');
    const b=parseInt(m[2]).toString(16).padStart(2,'0');
    return '#'+r+g+b;
  }

  /* ===== DRAG from palette ===== */
  function onDragStartComponent(e){
    const el=e.currentTarget;
    e.dataTransfer.setData('text/plain', JSON.stringify({ key: el.dataset.key, html: el.dataset.html }));
    e.dataTransfer.effectAllowed='copy';
  }

  /* ===== CANVAS DnD ===== */
  function onDragOverCanvas(e){
    e.preventDefault();
    const marker=state.markerEl;
    const target=getClosestBlock(e.target);
    if(target){
      const rect=target.getBoundingClientRect();
      const before=(e.clientY-rect.top) < rect.height/2;
      marker.classList.add('active');
      target.insertAdjacentElement(before?'beforebegin':'afterend',marker);
      marker.dataset.position=before?'before':'after';
      marker.dataset.targetId=target.dataset.blockId;
    }else{
      state.editEl.appendChild(marker);
      marker.classList.add('active');
      marker.dataset.position='end';
      marker.dataset.targetId='';
    }
  }
  function onDropCanvas(e){
    e.preventDefault();
    const raw=e.dataTransfer.getData('text/plain');
    if(!raw) return;
    let data; try{data=JSON.parse(raw);}catch(_){return;}
    pushHistory();
    if(data.key==='ce-html'){
      const html=prompt('Enter custom HTML:','<div>Custom</div>');
      if(html!==null) data.html=html;
    }
    const block=createBlock(data.html,data.key);
    const marker=state.markerEl;
    const id=marker.dataset.targetId;
    const pos=marker.dataset.position;
    marker.classList.remove('active');
    if(id){
      const target=state.editEl.querySelector(`[data-block-id="${id}"]`);
      target.insertAdjacentElement(pos==='before'?'beforebegin':'afterend',block);
    }else{
      state.editEl.appendChild(block);
    }

    block.querySelectorAll('.ce-slot').forEach(slot=>{
      const parent = slot.parentElement;
      if(parent && !parent.classList.contains('ce-block')){
        const colBlock = createBlock(parent.innerHTML,'ce-column');
        colBlock.style.flex='1';
        parent.replaceWith(colBlock);
      }
    });

    syncExport();
  }
  function onDragStartBlock(e){
    const b=getClosestBlock(e.target);
    if(!b) return;
    e.dataTransfer.setData('application/x-ce-block', b.dataset.blockId);
    e.dataTransfer.effectAllowed='move';
  }

  /* ===== SELECT ===== */
  function onSelectBlock(e){
    e.stopPropagation();
    const block=getClosestBlock(e.target);
    if(!block) return;
    deselect();
    state.selectedBlock=block;
    block.classList.add('ce-selected');
    renderInspector(block);
  }

  /* ===== EXPORT ===== */
  function getExportHTML(){
    const inner = state.outEl.innerHTML.trim();
    return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Your Campaign</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <style>
    body, table, td { margin:0; padding:0; }
    img { border:none; display:block; max-width:100%; height:auto; }
    a { text-decoration:none; }
    .wrapper { width:100% !important; background:#f3f4f6; }
    .inner   { width:100% !important; margin:0 auto; background:#ffffff; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
    @media only screen and (max-width:600px) { .inner { box-shadow:none !important; } }
    .ce-section-slot-wrapper { width:100% !important; display:flex; }
    @media (max-width: 375px) { .ce-section-slot-wrapper { flex-direction: column !important; } }
  </style>
</head>
<body>
  <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
    <tr>
      <td align="center">
        <table class="inner" cellpadding="0" cellspacing="0" border="0" align="center">
          <tr>
            <td style="padding:24px; font-family:Arial,sans-serif; color:#111827;">
              ${inner}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>`;
  }

  function exportHTML(){
    const html=getExportHTML();
    document.getElementById('ceExport').value=html;
    document.getElementById('ceModal').style.display='flex';
  }

  /* ===== CODE TAB ===== */
  function loadCodeFromExport(){
    const html=getExportHTML();
    document.getElementById('ceCodeArea').value=html;
    refreshPreview(html);
  }
  function refreshPreview(html){
    const iframe=document.getElementById('ceCodePreview');
    iframe.contentDocument.open();iframe.contentDocument.write(html);iframe.contentDocument.close();
  }
  function stripToBodyInner(html) {
    const s = (html || '').trim();
    if (!s) return '';
    if (/<html[\s>]/i.test(s) || /<!doctype/i.test(s)) {
      const doc = new DOMParser().parseFromString(s, 'text/html');
      return (doc.body ? doc.body.innerHTML : s).trim();
    }
    return s;
  }
  function applyCodeToCanvas(){
    const txt = document.getElementById('ceCodeArea').value;
    refreshPreview(txt);
    rebuildEditFromExport(stripToBodyInner(txt));
    syncExport();
  }

  function rebindAll(){
    deselect();
    state.editEl.querySelectorAll('.ce-block').forEach(b=>attachBlockEvents(b));
    state.editEl.querySelectorAll('.ce-block-handle').forEach(h=>{
      const blk=getClosestBlock(h);
      bindHandle(h, blk);
    });
    bindAddInside(state.editEl);
  }

  /* ============================================================
     ✅ Gallery editor
     ============================================================ */
  function addGalleryEditors(panel, stylePanel, block){
    const gallery = block.querySelector('.ce-gallery');
    if(!gallery || isUI(gallery)) return;

    const computed = getComputedStyle(gallery);
    let cols = 3;
    const gtc = gallery.style.gridTemplateColumns || computed.gridTemplateColumns || '';
    const m = gtc.match(/repeat\((\d+)/);
    if(m) cols = parseInt(m[1],10) || cols;
    else if(gtc) cols = gtc.split(' ').filter(Boolean).length || cols;

    let gap = parseInt((gallery.style.gap || computed.gap || '0').toString(),10);
    if (Number.isNaN(gap)) gap = 10;

    const layout = document.createElement('div');
    layout.className='ce-field';
    layout.innerHTML = `
      <label>Gallery Layout</label>
      <div style="display:flex;gap:6px;align-items:center;">
        <input type="number" min="1" max="6" value="${cols}" style="width:80px;" title="Columns">
        <input type="number" min="0" value="${gap}" style="width:80px;" title="Gap (px)">
        <button type="button" class="ce-btn-sm" id="ceGalAddImg" style="margin-left:auto;">
          <i class="fa-solid fa-plus"></i> Add
        </button>
      </div>
      <div class="ce-muted" style="padding:6px 0 0 0;">Columns • Gap • Add/Remove images</div>
    `;
    const [colInp,gapInp] = layout.querySelectorAll('input');

    colInp.addEventListener('input', e=>{
      pushHistory();
      const v = Math.max(1, Math.min(6, parseInt(e.target.value||'1',10) || 1));
      gallery.style.display='grid';
      gallery.style.gridTemplateColumns = `repeat(${v}, 1fr)`;
      syncExport();
    });

    gapInp.addEventListener('input', e=>{
      pushHistory();
      const v = parseInt(e.target.value||'0',10) || 0;
      gallery.style.display='grid';
      gallery.style.gap = v+'px';
      syncExport();
    });

    layout.querySelector('#ceGalAddImg').addEventListener('click', ()=>{
      const url = prompt('Image URL:', 'https://placehold.co/400x260');
      if(!url) return;

      pushHistory();
      const withCaptions = gallery.dataset.gallery === 'captions' || gallery.classList.contains('ce-gallery-captions');

      if(withCaptions){
        const cap = prompt('Caption (optional):', '') || '';
        const fig = document.createElement('figure');
        fig.style.margin='0';
        fig.innerHTML =
          `<img src="${url}" alt="Gallery image" style="width:100%;height:auto;display:block;border-radius:6px;"/>`+
          `<figcaption style="margin-top:6px;font-size:12px;color:#6b7280;line-height:1.4;">${cap || 'Caption'}</figcaption>`;
        gallery.appendChild(fig);
      } else {
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Gallery image';
        img.style.width='100%';
        img.style.height='auto';
        img.style.display='block';
        img.style.borderRadius='6px';
        gallery.appendChild(img);
      }

      syncExport();
      renderInspector(block);
    });

    stylePanel.appendChild(layout);

    /* ✅ Content tab: Add more images + edit URLs/captions without jumping tabs */
    const withCaptions = gallery.dataset.gallery === 'captions' || gallery.classList.contains('ce-gallery-captions');

    const contentField = document.createElement('div');
    contentField.className='ce-field';
    contentField.innerHTML = `<label>Gallery Images</label>`;

    const addRow = document.createElement('div');
    addRow.style.display='flex';
    addRow.style.gap='6px';
    addRow.style.alignItems='center';
    addRow.style.marginBottom='8px';
    addRow.innerHTML = `
      <input type="text" placeholder="Image URL..." style="flex:1;">
      <button type="button" class="ce-btn-sm ce-primary"><i class="fa-solid fa-plus"></i> Add</button>
    `;
    contentField.appendChild(addRow);

    const list = document.createElement('div');
    list.style.display='flex';
    list.style.flexDirection='column';
    list.style.gap='8px';
    contentField.appendChild(list);

    const urlInp = addRow.querySelector('input');
    const addBtn = addRow.querySelector('button');

    addBtn.addEventListener('click', ()=>{
      const url = (urlInp.value || '').trim();
      if(!url) return;

      pushHistory();
      if(withCaptions){
        const fig = document.createElement('figure');
        fig.style.margin='0';
        fig.innerHTML =
          `<img src="${url}" alt="Gallery image" style="width:100%;height:auto;display:block;border-radius:6px;"/>`+
          `<figcaption style="margin-top:6px;font-size:12px;color:#6b7280;line-height:1.4;">Caption</figcaption>`;
        gallery.appendChild(fig);
      } else {
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Gallery image';
        img.style.width='100%';
        img.style.height='auto';
        img.style.display='block';
        img.style.borderRadius='6px';
        gallery.appendChild(img);
      }
      urlInp.value = '';
      syncExport();
      renderInspector(block);
    });
    urlInp.addEventListener('keydown', (e)=>{
      if(e.key==='Enter'){ e.preventDefault(); addBtn.click(); }
    });

    const items = withCaptions
      ? Array.from(gallery.querySelectorAll('figure'))
      : Array.from(gallery.querySelectorAll('img'));

    items.forEach((item, idx)=>{
      const card = document.createElement('div');
      card.style.border='1px solid var(--ce-border)';
      card.style.borderRadius='6px';
      card.style.padding='8px';

      const head = document.createElement('div');
      head.style.display='flex';
      head.style.alignItems='center';
      head.style.gap='8px';
      head.style.marginBottom='6px';

      const title = document.createElement('strong');
      title.style.fontSize='12px';
      title.textContent = `Image ${idx+1}`;
      head.appendChild(title);

      const del = document.createElement('button');
      del.type='button';
      del.className='ce-btn-sm';
      del.style.marginLeft='auto';
      del.innerHTML = `<i class="fa-solid fa-trash"></i>`;
      del.addEventListener('click', ()=>{
        pushHistory();
        item.remove();
        syncExport();
        renderInspector(block);
      });
      head.appendChild(del);

      card.appendChild(head);

      const imgEl = withCaptions ? item.querySelector('img') : item;
      const capEl = withCaptions ? item.querySelector('figcaption') : null;

      const uLbl = document.createElement('label');
      uLbl.textContent = 'Image URL';
      card.appendChild(uLbl);

      const u = document.createElement('input');
      u.type='text';
      u.value = (imgEl && imgEl.getAttribute('src')) ? imgEl.getAttribute('src') : '';
      u.addEventListener('input', (e)=>{
        pushHistory();
        if(imgEl) imgEl.setAttribute('src', e.target.value);
        syncExport();
      });
      card.appendChild(u);

      if(withCaptions && capEl){
        const cLbl = document.createElement('label');
        cLbl.style.marginTop='8px';
        cLbl.textContent = 'Caption';
        card.appendChild(cLbl);

        const c = document.createElement('textarea');
        c.style.minHeight='60px';
        c.value = (capEl.textContent || '').trim();
        c.addEventListener('input', (e)=>{
          pushHistory();
          capEl.textContent = e.target.value;
          syncExport();
        });
        card.appendChild(c);
      }

      list.appendChild(card);
    });

    panel.appendChild(contentField);
  }


  /* ================================
   VIDEO EMBED EDITOR
   ================================ */
   function addVideoEditors(panel, stylePanel, block){
  const iframe = block.querySelector('iframe');
  if(!iframe || isUI(iframe)) return;

  const frame = block.querySelector('.ce-video-frame');

  /* ===== CONTENT: URL + OPTIONS ===== */
  const c = document.createElement('div');
  c.className = 'ce-field';
  c.innerHTML = `
    <label>Video URL (Embed)</label>
    <input type="text" value="${iframe.getAttribute('src') || ''}">
    <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
      <label style="display:flex;gap:6px;align-items:center;">
        <input type="checkbox" data-opt="autoplay"> Autoplay
      </label>
      <label style="display:flex;gap:6px;align-items:center;">
        <input type="checkbox" data-opt="loop"> Loop
      </label>
      <label style="display:flex;gap:6px;align-items:center;">
        <input type="checkbox" data-opt="controls" checked> Controls
      </label>
    </div>
  `;
  const urlInp = c.querySelector('input');
  const autoplayCh = c.querySelector('[data-opt="autoplay"]');
  const loopCh     = c.querySelector('[data-opt="loop"]');
  const controlsCh = c.querySelector('[data-opt="controls"]');

  // Try to prefill checkboxes from current iframe src params
  (function initFromSrc(){
    try{
      const src = (iframe.getAttribute('src') || '').trim();
      const qs = src.includes('?') ? src.split('?')[1] : '';
      const p = new URLSearchParams(qs);
      autoplayCh.checked = p.get('autoplay') === '1';
      loopCh.checked     = p.get('loop') === '1';
      // Default controls true unless explicitly 0
      controlsCh.checked = p.get('controls') !== '0';
    }catch(_){}
  })();

  function baseUrl(u){ return (u||'').trim().split('#')[0].split('?')[0]; }

  function guessYoutubeId(u){
    const s = (u||'').trim();
    // https://www.youtube.com/embed/ID
    let m = s.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]{6,})/);
    if(m) return m[1];
    // https://youtu.be/ID
    m = s.match(/youtu\.be\/([a-zA-Z0-9_-]{6,})/);
    if(m) return m[1];
    // https://www.youtube.com/watch?v=ID
    m = s.match(/[?&]v=([a-zA-Z0-9_-]{6,})/);
    if(m) return m[1];
    return '';
  }

  function rebuildURL(){
    const raw = (urlInp.value || '').trim();
    if(!raw) return;

    const b = baseUrl(raw);
    const params = new URLSearchParams();

    if(autoplayCh.checked) params.set('autoplay','1');
    if(!controlsCh.checked) params.set('controls','0');

    if(loopCh.checked){
      params.set('loop','1');
      // YouTube loop needs playlist=videoId
      const yid = guessYoutubeId(raw) || guessYoutubeId(b) || b.split('/').pop();
      if(yid) params.set('playlist', yid);
    }

    const finalSrc = params.toString() ? (b + '?' + params.toString()) : b;
    iframe.setAttribute('src', finalSrc);
  }

  urlInp.addEventListener('input', ()=>{
    pushHistory();
    iframe.setAttribute('src', urlInp.value);
    syncExport();
  });

  [autoplayCh, loopCh, controlsCh].forEach(ch=>{
    ch.addEventListener('change', ()=>{
      pushHistory();
      rebuildURL();
      syncExport();
    });
  });

  panel.appendChild(c);

  /* ===== CONTENT: TITLE ===== */
  const titleEl = block.querySelector('.ce-video-title');
  if(titleEl){
    const t = document.createElement('div');
    t.className = 'ce-field';
    t.innerHTML = `
      <label>Video Title</label>
      <input type="text" value="${(titleEl.textContent || '').trim()}">
    `;
    t.querySelector('input').addEventListener('input', e=>{
      pushHistory();
      titleEl.textContent = e.target.value;
      syncExport();
    });
    panel.appendChild(t);
  }

  /* ===== CONTENT: CAPTION ===== */
  const capEl = block.querySelector('.ce-video-caption');
  if(capEl){
    const cap = document.createElement('div');
    cap.className = 'ce-field';
    cap.innerHTML = `
      <label>Video Caption</label>
      <textarea style="min-height:70px;">${(capEl.textContent || '').trim()}</textarea>
    `;
    cap.querySelector('textarea').addEventListener('input', e=>{
      pushHistory();
      capEl.textContent = e.target.value;
      syncExport();
    });
    panel.appendChild(cap);
  }

  /* ===== STYLE: ASPECT RATIO ===== */
  if(frame){
    let currentPct = 56.25;
    try{
      const cs = getComputedStyle(frame);
      const pt = (frame.style.paddingTop || cs.paddingTop || '56.25%').toString();
      currentPct = parseFloat(pt) || 56.25;
    }catch(_){}

    const s = document.createElement('div');
    s.className = 'ce-field';
    s.innerHTML = `
      <label>Aspect Ratio</label>
      <select>
        <option value="56.25">16:9</option>
        <option value="75">4:3</option>
        <option value="100">1:1</option>
      </select>
    `;
    const ratioSel = s.querySelector('select');

    // Set initial select value by closest match
    (function(){
      const v = [56.25, 75, 100].reduce((best, x)=> Math.abs(x-currentPct) < Math.abs(best-currentPct) ? x : best, 56.25);
      ratioSel.value = String(v);
    })();

    ratioSel.addEventListener('change', ()=>{
      pushHistory();
      frame.style.paddingTop = ratioSel.value + '%';
      syncExport();
    });

    stylePanel.appendChild(s);
  }
}



  /* ============================================================
     ✅ TABLE helpers: keep styles when adding/removing
     ============================================================ */
  function cloneCellStyle(src, dest){
    if(!src || !dest) return;
    const cs = getComputedStyle(src);
    dest.style.borderStyle = cs.borderStyle;
    dest.style.borderWidth = cs.borderWidth;
    dest.style.borderColor = cs.borderColor;
    dest.style.border = `${cs.borderWidth} ${cs.borderStyle} ${cs.borderColor}`;
    dest.style.padding = cs.padding;
    dest.style.textAlign = cs.textAlign;
    dest.style.backgroundColor = cs.backgroundColor;
    dest.style.color = cs.color;
    dest.style.fontWeight = cs.fontWeight;
  }

  function makeBodyCellLike(sample){
    const td = document.createElement('td');
    td.textContent = 'Cell';
    if(sample) cloneCellStyle(sample, td);
    if(!td.style.border) td.style.border = '1px solid #e5e7eb';
    if(!td.style.padding) td.style.padding = '10px';
    if(!td.style.textAlign) td.style.textAlign = 'left';
    return td;
  }

  function makeHeaderCellLike(sample){
    const th = document.createElement('th');
    th.textContent = 'Header';
    if(sample) cloneCellStyle(sample, th);
    if(!th.style.border) th.style.border = '1px solid #e5e7eb';
    if(!th.style.padding) th.style.padding = '10px';
    if(!th.style.textAlign) th.style.textAlign = 'left';
    if(!th.style.backgroundColor) th.style.backgroundColor = '#f9fafb';
    th.style.fontWeight = th.style.fontWeight || '700';
    return th;
  }

  function ensureTbody(table){
    let tbody = table.querySelector('tbody');
    if(!tbody){
      tbody = document.createElement('tbody');
      table.appendChild(tbody);
    }
    return tbody;
  }

  function tableGetCols(table){
    const r = table.querySelector('tr');
    if(!r) return 0;
    return Array.from(r.children).filter(c=>c.tagName==='TD' || c.tagName==='TH').length;
  }

  function toggleTableHeader(table){
    const thead = table.querySelector('thead');
    const tbody = ensureTbody(table);

    if(thead){
      const headRow = thead.querySelector('tr');
      if(headRow){
        const newRow = document.createElement('tr');
        Array.from(headRow.children).forEach(th=>{
          const td = document.createElement('td');
          td.textContent = th.textContent;
          cloneCellStyle(th, td);
          td.style.backgroundColor = '';
          td.style.fontWeight = '';
          newRow.appendChild(td);
        });
        tbody.insertAdjacentElement('afterbegin', newRow);
      }
      thead.remove();
      return;
    }

    let firstBodyRow = tbody.querySelector('tr');
    if(!firstBodyRow){
      firstBodyRow = document.createElement('tr');
      const cols = Math.max(3, tableGetCols(table) || 3);
      for(let i=0;i<cols;i++) firstBodyRow.appendChild(makeBodyCellLike(null));
      tbody.appendChild(firstBodyRow);
    }

    const newThead = document.createElement('thead');
    const newHeadRow = document.createElement('tr');
    const sample = firstBodyRow.querySelector('td') || null;

    Array.from(firstBodyRow.children).filter(c=>c.tagName==='TD' || c.tagName==='TH').forEach(td=>{
      const th = document.createElement('th');
      th.textContent = td.textContent || 'Header';
      cloneCellStyle(td, th);
      th.style.backgroundColor = th.style.backgroundColor || '#f9fafb';
      th.style.fontWeight = th.style.fontWeight || '700';
      newHeadRow.appendChild(th);
    });

    newThead.appendChild(newHeadRow);
    table.insertAdjacentElement('afterbegin', newThead);
    firstBodyRow.remove();
  }

  function tableAddRow(table){
    const tbody = ensureTbody(table);
    const cols = Math.max(1, tableGetCols(table) || 3);
    const sample = table.querySelector('tbody td') || table.querySelector('th') || null;
    const tr = document.createElement('tr');
    for(let i=0;i<cols;i++) tr.appendChild(makeBodyCellLike(sample));
    tbody.appendChild(tr);
  }

  function tableDelRow(table){
    const tbody = table.querySelector('tbody');
    if(!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));
    if(!rows.length) return;
    rows[rows.length-1].remove();
  }

  function tableAddCol(table){
    const rows = Array.from(table.querySelectorAll('tr'));
    const sampleTd = table.querySelector('td');
    const sampleTh = table.querySelector('th');
    rows.forEach(r=>{
      const isHead = r.closest('thead');
      if(isHead) r.appendChild(makeHeaderCellLike(sampleTh || sampleTd));
      else r.appendChild(makeBodyCellLike(sampleTd || sampleTh));
    });
  }

  function tableDelCol(table){
    const rows = Array.from(table.querySelectorAll('tr'));
    rows.forEach(r=>{
      const cells = Array.from(r.children).filter(c=>c.tagName==='TD' || c.tagName==='TH');
      if(cells.length>1) cells[cells.length-1].remove();
    });
  }

  /* ✅ Table: styling + header toggle + add row/col (restored) */
  function addTableEditors(panel, stylePanel, block){
    const table = block.querySelector('table.ce-table, table');
    if(!table || isUI(table)) return;

    const firstCell = table.querySelector('th,td');
    const cellCS = firstCell ? getComputedStyle(firstCell) : null;
    const bColor = cellCS ? rgb2hex(cellCS.borderColor) : '#e5e7eb';
    const bWidth = cellCS ? (parseInt(cellCS.borderWidth,10)||1) : 1;
    const pad    = cellCS ? (parseInt(cellCS.paddingTop,10)||10) : 10;

    const styleField = document.createElement('div');
    styleField.className='ce-field';
    styleField.innerHTML = `
      <label>Table Styling</label>
      <div style="display:flex;gap:6px;align-items:center;">
        <input type="number" min="0" value="${bWidth}" style="width:70px;" title="Border width (px)">
        <input type="color" value="${bColor}" title="Border color" style="width:34px;height:34px;padding:0;border:none;">
        <input type="number" min="0" value="${pad}" style="width:90px;" title="Cell padding (px)">
        <button type="button" class="ce-btn-sm" id="ceTblToggleHead" style="margin-left:auto;">
          <i class="fa-solid fa-heading"></i> Header
        </button>
      </div>
    `;
    const [bwInp, bcInp, padInp] = styleField.querySelectorAll('input');

    bwInp.addEventListener('input', e=>{
      pushHistory();
      const v = parseInt(e.target.value||'0',10) || 0;
      table.querySelectorAll('th,td').forEach(c=>{
        c.style.borderWidth = v+'px';
        c.style.borderStyle = c.style.borderStyle || 'solid';
        c.style.borderColor = c.style.borderColor || bcInp.value;
      });
      syncExport();
    });

    bcInp.addEventListener('input', e=>{
      pushHistory();
      table.querySelectorAll('th,td').forEach(c=>{
        c.style.borderColor = e.target.value;
        c.style.borderStyle = c.style.borderStyle || 'solid';
        c.style.borderWidth = c.style.borderWidth || (bwInp.value+'px');
      });
      syncExport();
    });

    padInp.addEventListener('input', e=>{
      pushHistory();
      const v = parseInt(e.target.value||'0',10) || 0;
      table.querySelectorAll('th,td').forEach(c=>c.style.padding = v+'px');
      syncExport();
    });

    styleField.querySelector('#ceTblToggleHead').addEventListener('click', ()=>{
      pushHistory();
      toggleTableHeader(table);
      syncExport();
      renderInspector(block);
    });

    stylePanel.appendChild(styleField);

    const ctrl = document.createElement('div');
    ctrl.className='ce-field';
    ctrl.innerHTML = `
      <label>Table Rows / Columns</label>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button type="button" class="ce-btn-sm" data-act="addRow"><i class="fa-solid fa-plus"></i> Row</button>
        <button type="button" class="ce-btn-sm" data-act="delRow"><i class="fa-solid fa-minus"></i> Row</button>
        <button type="button" class="ce-btn-sm" data-act="addCol"><i class="fa-solid fa-plus"></i> Col</button>
        <button type="button" class="ce-btn-sm" data-act="delCol"><i class="fa-solid fa-minus"></i> Col</button>
      </div>
      <div class="ce-muted" style="padding:6px 0 0 0;">Tip: double-click any cell to edit inside canvas.</div>
    `;
    ctrl.querySelectorAll('button[data-act]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const act = btn.dataset.act;
        pushHistory();
        if(act==='addRow') tableAddRow(table);
        if(act==='delRow') tableDelRow(table);
        if(act==='addCol') tableAddCol(table);
        if(act==='delCol') tableDelCol(table);
        syncExport();
        renderInspector(block);
      });
    });
    panel.appendChild(ctrl);
  }

  /* ✅ Table Content Editor (fix: edit cell text + header text) */
  function addTableContentEditors(panel, block){
    const table = block.querySelector('table.ce-table, table');
    if(!table || isUI(table)) return;

    const headRows = Array.from(table.querySelectorAll('thead tr'));
    const bodyRows = Array.from(table.querySelectorAll('tbody tr'));
    const allRows  = [...headRows, ...bodyRows];

    if(!allRows.length) return;

    const cols = Math.max(...allRows.map(r => Array.from(r.children).filter(c=>c.tagName==='TH'||c.tagName==='TD').length), 1);

    const field = document.createElement('div');
    field.className='ce-field';
    field.innerHTML = `<label>Table Content</label>
      <div class="ce-muted" style="padding:6px 0 8px 0;">Edit text in header/body cells (also works with double-click in canvas).</div>`;
    const box = document.createElement('div');
    box.style.border = '1px solid var(--ce-border)';
    box.style.borderRadius = '6px';
    box.style.padding = '8px';
    box.style.maxHeight = '320px';
    box.style.overflow = 'auto';
    field.appendChild(box);

    allRows.forEach((row, rIdx)=>{
      const isHead = !!row.closest('thead');
      const rowWrap = document.createElement('div');
      rowWrap.style.display = 'grid';
      rowWrap.style.gridTemplateColumns = `repeat(${cols}, minmax(0, 1fr))`;
      rowWrap.style.gap = '6px';
      rowWrap.style.marginBottom = '6px';

      const cells = Array.from(row.children).filter(c=>c.tagName==='TH'||c.tagName==='TD');
      for(let c=0;c<cols;c++){
        const cell = cells[c];
        const inp = document.createElement('input');
        inp.type = 'text';
        inp.value = cell ? (cell.textContent || '') : '';
        inp.placeholder = isHead ? `H${c+1}` : `R${rIdx+1}C${c+1}`;
        if(isHead){
          inp.style.fontWeight = '700';
          inp.style.background = '#f9fafb';
        }
        let armed=false;
        inp.addEventListener('input', ()=>{
          if(!cell) return;
          if(!armed){ pushHistory(); armed=true; }
          cell.textContent = inp.value;
          syncExport();
        });
        inp.addEventListener('blur', ()=>{ armed=false; });
        rowWrap.appendChild(inp);
      }

      box.appendChild(rowWrap);
    });

    panel.appendChild(field);
  }

  /* ✅ Tags Editor */
  function addTagsEditors(panel, stylePanel, block){
    const wrap = block.querySelector('.ce-tags');
    if(!wrap || isUI(wrap)) return;

    const firstTag = wrap.querySelector('.ce-tag') || wrap.querySelector('span');
    const cs = firstTag ? getComputedStyle(firstTag) : null;

    const bg = cs ? rgb2hex(cs.backgroundColor) : '#eef2ff';
    const fg = cs ? rgb2hex(cs.color) : '#4338ca';
    const rad = cs ? (parseInt(cs.borderTopLeftRadius,10)||999) : 999;
    const padY = cs ? (parseInt(cs.paddingTop,10)||6) : 6;
    const padX = cs ? (parseInt(cs.paddingLeft,10)||10) : 10;
    const gap = parseInt((wrap.style.gap || getComputedStyle(wrap).gap || '8').toString(),10) || 8;

    const s = document.createElement('div');
    s.className='ce-field';
    s.innerHTML = `
      <label>Tags Styling</label>
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
        <input type="color" value="${bg}" title="Tag background" style="width:34px;height:34px;padding:0;border:none;">
        <input type="color" value="${fg}" title="Tag text color" style="width:34px;height:34px;padding:0;border:none;">
        <input type="number" min="0" value="${rad}" title="Radius" style="width:70px;">
        <input type="number" min="0" value="${gap}" title="Gap" style="width:70px;">
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="number" min="0" value="${padY}" title="Padding Y" style="width:80px;">
        <input type="number" min="0" value="${padX}" title="Padding X" style="width:80px;">
      </div>
    `;

    const inputs = s.querySelectorAll('input');
    const bgInp = inputs[0];
    const fgInp = inputs[1];
    const radInp = inputs[2];
    const gapInp = inputs[3];
    const pyInp = inputs[4];
    const pxInp = inputs[5];

    function applyTagStyles(){
      const tags = wrap.querySelectorAll('.ce-tag');
      tags.forEach(t=>{
        t.style.backgroundColor = bgInp.value;
        t.style.color = fgInp.value;
        t.style.borderRadius = (parseInt(radInp.value||'0',10)||0) + 'px';
        t.style.padding = (parseInt(pyInp.value||'0',10)||0) + 'px ' + (parseInt(pxInp.value||'0',10)||0) + 'px';
      });
      wrap.style.display = 'flex';
      wrap.style.flexWrap = 'wrap';
      wrap.style.gap = (parseInt(gapInp.value||'0',10)||0) + 'px';
    }

    [bgInp,fgInp,radInp,gapInp,pyInp,pxInp].forEach(inp=>{
      inp.addEventListener('input', ()=>{
        pushHistory();
        applyTagStyles();
        syncExport();
      });
    });

    stylePanel.appendChild(s);

    const listField = document.createElement('div');
    listField.className='ce-field';

    const addRow = document.createElement('div');
    addRow.style.display='flex';
    addRow.style.gap='6px';
    addRow.innerHTML = `
      <input type="text" placeholder="New tag..." style="flex:1;">
      <button type="button" class="ce-btn-sm ce-primary"><i class="fa-solid fa-plus"></i> Add</button>
    `;
    const newInp = addRow.querySelector('input');
    const addBtn = addRow.querySelector('button');

    function addTag(txt){
      const span = document.createElement('span');
      span.className = 'ce-tag';
      span.textContent = txt;
      span.style.display='inline-block';
      span.style.fontSize='12px';
      span.style.lineHeight='1';
      wrap.appendChild(span);
      applyTagStyles();
    }

    addBtn.addEventListener('click', ()=>{
      const txt = (newInp.value || '').trim();
      if(!txt) return;
      pushHistory();
      addTag(txt);
      newInp.value = '';
      syncExport();
      renderInspector(block);
    });
    newInp.addEventListener('keydown', (e)=>{
      if(e.key==='Enter'){ e.preventDefault(); addBtn.click(); }
    });

    listField.innerHTML = `<label>Tags</label>`;
    listField.appendChild(addRow);

    const list = document.createElement('div');
    list.style.display='flex';
    list.style.flexDirection='column';
    list.style.gap='6px';
    list.style.marginTop='10px';
    listField.appendChild(list);

    Array.from(wrap.querySelectorAll('.ce-tag')).forEach((tag)=>{
      const row = document.createElement('div');
      row.style.display='flex';
      row.style.gap='6px';
      row.innerHTML = `
        <input type="text" value="${(tag.textContent||'').trim()}" style="flex:1;">
        <button type="button" class="ce-btn-sm"><i class="fa-solid fa-trash"></i></button>
      `;
      row.querySelector('input').addEventListener('input', e=>{
        pushHistory();
        tag.textContent = e.target.value;
        syncExport();
      });
      row.querySelector('button').addEventListener('click', ()=>{
        pushHistory();
        tag.remove();
        syncExport();
        renderInspector(block);
      });
      list.appendChild(row);
    });

    panel.appendChild(listField);
  }

  /* ✅ FAQ Editor */
  function addFAQEditors(panel, stylePanel, block){
    const faq = block.querySelector('.ce-faq');
    if(!faq || isUI(faq)) return;

    const firstItem = faq.querySelector('.ce-faq-item');
    const cs = firstItem ? getComputedStyle(firstItem) : null;

    const bColor = cs ? rgb2hex(cs.borderColor) : '#e5e7eb';
    const rad = cs ? (parseInt(cs.borderTopLeftRadius,10)||6) : 6;
    const pad = cs ? (parseInt(cs.paddingTop,10)||10) : 10;

    const s = document.createElement('div');
    s.className='ce-field';
    s.innerHTML = `
      <label>FAQ Styling</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" value="${bColor}" title="Border color" style="width:34px;height:34px;padding:0;border:none;">
        <input type="number" min="0" value="${rad}" title="Radius" style="width:80px;">
        <input type="number" min="0" value="${pad}" title="Padding" style="width:80px;">
      </div>
      <div class="ce-muted" style="padding:6px 0 0 0;">Border • Radius • Padding</div>
    `;

    const bInp = s.querySelector('input[type="color"]');
    const nums = s.querySelectorAll('input[type="number"]');
    const rInp = nums[0];
    const pInp = nums[1];

    function applyFAQStyles(){
      faq.querySelectorAll('.ce-faq-item').forEach(item=>{
        item.style.borderColor = bInp.value;
        item.style.borderWidth = item.style.borderWidth || '1px';
        item.style.borderStyle = item.style.borderStyle || 'solid';
        item.style.borderRadius = (parseInt(rInp.value||'0',10)||0)+'px';
        item.style.padding = (parseInt(pInp.value||'0',10)||0)+'px';
      });
    }

    [bInp,rInp,pInp].forEach(inp=>{
      inp.addEventListener('input', ()=>{
        pushHistory();
        applyFAQStyles();
        syncExport();
      });
    });

    stylePanel.appendChild(s);

    const listField = document.createElement('div');
    listField.className='ce-field';

    const header = document.createElement('div');
    header.style.display='flex';
    header.style.alignItems='center';
    header.style.justifyContent='space-between';
    header.style.gap='8px';
    header.innerHTML = `<label style="margin:0;">FAQ Items</label>
      <button type="button" class="ce-btn-sm ce-primary" id="ceFaqAddList"><i class="fa-solid fa-plus"></i> Add</button>`;
    listField.appendChild(header);

    function createFAQItem(q, a){
      const item = document.createElement('div');
      item.className = 'ce-faq-item';
      item.innerHTML = `
        <div class="ce-faq-q" style="font-weight:700;margin:0 0 6px 0;"></div>
        <div class="ce-faq-a" style="margin:0;color:#374151;line-height:1.5;"></div>
      `;
      item.style.border = '1px solid #e5e7eb';
      item.style.borderRadius = '6px';
      item.style.padding = '10px';
      item.style.margin = '0 0 10px 0';
      item.querySelector('.ce-faq-q').textContent = q;
      item.querySelector('.ce-faq-a').textContent = a;
      return item;
    }

    listField.querySelector('#ceFaqAddList').addEventListener('click', ()=>{
      const q = prompt('Question:', 'New question?');
      if(!q) return;
      const a = prompt('Answer:', 'Answer goes here.') || '';
      pushHistory();
      const item = createFAQItem(q, a);
      faq.appendChild(item);
      applyFAQStyles();
      syncExport();
      renderInspector(block);
    });

    const list = document.createElement('div');
    list.style.display='flex';
    list.style.flexDirection='column';
    list.style.gap='10px';
    list.style.marginTop='10px';
    listField.appendChild(list);

    const items = Array.from(faq.querySelectorAll('.ce-faq-item'));
    items.forEach((item, idx)=>{
      const qEl = item.querySelector('.ce-faq-q');
      const aEl = item.querySelector('.ce-faq-a');
      if(!qEl || !aEl) return;

      const card = document.createElement('div');
      card.style.border='1px solid var(--ce-border)';
      card.style.borderRadius='6px';
      card.style.padding='8px';

      const head = document.createElement('div');
      head.style.display='flex';
      head.style.alignItems='center';
      head.style.gap='8px';
      head.style.marginBottom='6px';

      const title = document.createElement('strong');
      title.style.fontSize='12px';
      title.textContent = `Item ${idx+1}`;
      head.appendChild(title);

      const del = document.createElement('button');
      del.type='button';
      del.className='ce-btn-sm';
      del.style.marginLeft='auto';
      del.innerHTML = `<i class="fa-solid fa-trash"></i>`;
      del.addEventListener('click', ()=>{
        pushHistory();
        item.remove();
        syncExport();
        renderInspector(block);
      });
      head.appendChild(del);

      card.appendChild(head);

      const qLbl = document.createElement('label');
      qLbl.textContent = 'Question';
      card.appendChild(qLbl);

      const qInp = document.createElement('input');
      qInp.type='text';
      qInp.value = (qEl.textContent||'').trim();
      qInp.addEventListener('input', e=>{
        pushHistory();
        qEl.textContent = e.target.value;
        syncExport();
      });
      card.appendChild(qInp);

      const aLbl = document.createElement('label');
      aLbl.style.marginTop='8px';
      aLbl.textContent = 'Answer';
      card.appendChild(aLbl);

      const aTa = document.createElement('textarea');
      aTa.style.minHeight='70px';
      aTa.value = (aEl.textContent||'').trim();
      aTa.addEventListener('input', e=>{
        pushHistory();
        aEl.textContent = e.target.value;
        syncExport();
      });
      card.appendChild(aTa);

      list.appendChild(card);
    });

    panel.appendChild(listField);
  }

  /* ✅ NEW: Social Links editor (FontAwesome, add/remove, color, target, spacing, align) */
  function addSocialEditors(panel, stylePanel, block){
    const wrap = block.querySelector('.cs-social-links');
    if(!wrap || isUI(wrap)) return;

    wrap.style.display = 'flex';
    wrap.style.alignItems = 'center';

    const computed = getComputedStyle(wrap);
    const gap = parseInt((wrap.style.gap || computed.gap || '12').toString(),10) || 12;

    // Style controls: spacing + icon size
    const styleField = document.createElement('div');
    styleField.className = 'ce-field';
    styleField.innerHTML = `
      <label>Social Styling</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="number" min="0" value="${gap}" style="width:90px;" title="Spacing (px)">
        <input type="number" min="10" value="${guessIconSize(wrap) || 22}" style="width:90px;" title="Icon size (px)">
      </div>
      <div class="ce-muted" style="padding:6px 0 0 0;">Spacing between icons • Icon size</div>
    `;
    const [gapInp, sizeInp] = styleField.querySelectorAll('input');

    gapInp.addEventListener('input', ()=>{
      pushHistory();
      wrap.style.gap = (parseInt(gapInp.value||'0',10) || 0) + 'px';
      syncExport();
    });

    sizeInp.addEventListener('input', ()=>{
      pushHistory();
      const v = parseInt(sizeInp.value||'22',10) || 22;
      wrap.querySelectorAll('i').forEach(i=>{
        i.style.fontSize = v + 'px';
      });
      syncExport();
    });

    stylePanel.appendChild(styleField);

    // Content controls: list of icons
    const field = document.createElement('div');
    field.className = 'ce-field';

    const top = document.createElement('div');
    top.style.display = 'flex';
    top.style.alignItems = 'center';
    top.style.justifyContent = 'space-between';
    top.style.gap = '8px';
    top.innerHTML = `
      <label style="margin:0;">Social Links</label>
      <button type="button" class="ce-btn-sm ce-primary" id="ceSocAdd"><i class="fa-solid fa-plus"></i> Add</button>
    `;
    field.appendChild(top);

    const list = document.createElement('div');
    list.style.display = 'flex';
    list.style.flexDirection = 'column';
    list.style.gap = '10px';
    list.style.marginTop = '10px';
    field.appendChild(list);

    function rebuildList(){
      list.innerHTML = '';
      const links = Array.from(wrap.querySelectorAll('a'));

      links.forEach((a, idx)=>{
        const icon = a.querySelector('i') || a.appendChild(document.createElement('i'));
        if(!icon.className) icon.className = 'fa-brands fa-linkedin-in';

        const card = document.createElement('div');
        card.style.border = '1px solid var(--ce-border)';
        card.style.borderRadius = '6px';
        card.style.padding = '8px';

        const head = document.createElement('div');
        head.style.display = 'flex';
        head.style.alignItems = 'center';
        head.style.gap = '8px';
        head.style.marginBottom = '8px';

        const title = document.createElement('strong');
        title.style.fontSize = '12px';
        title.textContent = `Icon ${idx+1}`;
        head.appendChild(title);

        const del = document.createElement('button');
        del.type='button';
        del.className='ce-btn-sm';
        del.style.marginLeft='auto';
        del.innerHTML = `<i class="fa-solid fa-trash"></i>`;
        del.addEventListener('click', ()=>{
          pushHistory();
          a.remove();
          syncExport();
          rebuildList();
        });
        head.appendChild(del);

        card.appendChild(head);

        // Icon select + custom class
        const iconLabel = document.createElement('label');
        iconLabel.textContent = 'Icon';
        card.appendChild(iconLabel);

        const iconRow = document.createElement('div');
        iconRow.style.display = 'flex';
        iconRow.style.gap = '6px';

        const sel = document.createElement('select');
        const presets = [
          ['fa-brands fa-facebook-f','Facebook'],
          ['fa-brands fa-x-twitter','X (Twitter)'],
          ['fa-brands fa-instagram','Instagram'],
          ['fa-brands fa-linkedin-in','LinkedIn'],
          ['fa-brands fa-youtube','YouTube'],
          ['fa-brands fa-whatsapp','WhatsApp'],
          ['fa-brands fa-telegram','Telegram'],
          ['fa-brands fa-github','GitHub'],
          ['fa-solid fa-globe','Website'],
        ];
        presets.forEach(([cls, name])=>{
          const o = document.createElement('option');
          o.value = cls;
          o.textContent = name;
          sel.appendChild(o);
        });
        // select best match if class equals one of preset
        const currentCls = normalizeFA(icon.className);
        if(presets.some(p=>normalizeFA(p[0])===currentCls)) sel.value = presets.find(p=>normalizeFA(p[0])===currentCls)[0];
        else sel.value = presets[0][0];

        const custom = document.createElement('input');
        custom.type='text';
        custom.placeholder = 'Custom FA class (optional)';
        custom.value = icon.className;

        sel.style.flex='1';
        custom.style.flex='1';

        iconRow.appendChild(sel);
        iconRow.appendChild(custom);
        card.appendChild(iconRow);

        // URL
        const urlLbl = document.createElement('label');
        urlLbl.style.marginTop='8px';
        urlLbl.textContent = 'URL';
        card.appendChild(urlLbl);

        const urlInp = document.createElement('input');
        urlInp.type='text';
        urlInp.value = a.getAttribute('href') || '';
        card.appendChild(urlInp);

        // new tab
        const ntRow = document.createElement('div');
        ntRow.style.display='flex';
        ntRow.style.alignItems='center';
        ntRow.style.gap='8px';
        ntRow.style.marginTop='8px';
        ntRow.innerHTML = `<input type="checkbox" ${a.getAttribute('target')==='_blank'?'checked':''}/> <span style="font-size:13px;">Open in new tab</span>`;
        card.appendChild(ntRow);

        // color
        const colLbl = document.createElement('label');
        colLbl.style.marginTop='8px';
        colLbl.textContent = 'Icon Color';
        card.appendChild(colLbl);

        const colorInp = document.createElement('input');
        colorInp.type='color';
        colorInp.value = rgb2hex(getComputedStyle(icon).color);
        colorInp.style.width='60px';
        card.appendChild(colorInp);

        // events
        sel.addEventListener('change', ()=>{
          pushHistory();
          icon.className = sel.value;
          custom.value = icon.className;
          syncExport();
        });

        custom.addEventListener('input', ()=>{
          pushHistory();
          icon.className = custom.value;
          syncExport();
        });

        urlInp.addEventListener('input', ()=>{
          pushHistory();
          a.setAttribute('href', urlInp.value);
          syncExport();
        });

        ntRow.querySelector('input').addEventListener('change', (e)=>{
          pushHistory();
          if(e.target.checked){
            a.setAttribute('target','_blank');
            a.setAttribute('rel','noopener noreferrer');
          } else {
            a.removeAttribute('target');
            a.removeAttribute('rel');
          }
          syncExport();
        });

        colorInp.addEventListener('input', ()=>{
          pushHistory();
          icon.style.color = colorInp.value;
          syncExport();
        });

        list.appendChild(card);
      });
    }

    field.querySelector('#ceSocAdd').addEventListener('click', ()=>{
      const url = prompt('Link URL:', 'https://');
      if(url === null) return;

      pushHistory();
      const a = document.createElement('a');
      a.setAttribute('href', url || '#');
      a.style.display='inline-flex';
      a.style.alignItems='center';
      a.style.justifyContent='center';
      a.style.textDecoration='none';

      const i = document.createElement('i');
      i.className = 'fa-solid fa-globe';
      i.style.color = '#6366f1';
      i.style.fontSize = (guessIconSize(wrap)||22)+'px';
      a.appendChild(i);

      wrap.appendChild(a);
      syncExport();
      rebuildList();
      renderInspector(block);
    });

    rebuildList();
    panel.appendChild(field);

    function normalizeFA(cls){ return (cls||'').trim().replace(/\s+/g,' '); }
    function guessIconSize(container){
      const i = container.querySelector('i');
      if(!i) return 0;
      const v = parseInt(getComputedStyle(i).fontSize||'0',10);
      return Number.isFinite(v) ? v : 0;
    }
  }

  function addCustomHTMLField(panel, block, html){
    const htmlField=document.createElement('div');htmlField.className='ce-field';
    htmlField.innerHTML=`<label>Custom HTML</label><textarea>${html}</textarea>`;
    htmlField.querySelector('textarea').addEventListener('change',e=>{
      pushHistory();
      const handle=block.querySelector('.ce-block-handle');
      const addBtns=[...block.querySelectorAll('.ce-add-inside')];
      block.innerHTML=e.target.value;
      if(handle) block.appendChild(handle);
      addBtns.forEach(a=>block.appendChild(a));
      bindAddInside(block);
      syncExport();
    });
    panel.appendChild(htmlField);
  }

  /* ===== BOOT ===== */
  window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('ceCanvasEdit').addEventListener('click', e => {
      if (e.target.closest('a')) e.preventDefault();
    });

    state.editEl   = document.getElementById('ceCanvasEdit');
    state.outEl    = document.getElementById('ceCanvasExport');
    state.markerEl = document.createElement('div');
    state.markerEl.className = 'ce-drop-marker';

    state.editEl.addEventListener('dragover', onDragOverCanvas);
    state.editEl.addEventListener('drop',    onDropCanvas);

    state.editEl.addEventListener('click', () => { deselect(); closePopup(); });

    document.querySelectorAll('#list-elements .ce-component, #list-sections .ce-component')
      .forEach(el => el.addEventListener('dragstart', onDragStartComponent));

    document.getElementById('ceCloseModal').addEventListener('click', () => {
      document.getElementById('ceModal').style.display = 'none';
    });

    document.getElementById('ceCodeRefresh').addEventListener('click', loadCodeFromExport);
    document.getElementById('ceCodeApply').addEventListener('click', applyCodeToCanvas);

    document.getElementById('ceUndo').addEventListener('click', undo);
    document.getElementById('ceRedo').addEventListener('click', redo);

    document.addEventListener('keydown', e => {
      if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === 'z') { e.preventDefault(); undo(); }
      if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'z') { e.preventDefault(); redo(); }
      if (state.selectedBlock && e.altKey && e.key === 'ArrowUp') { e.preventDefault(); moveBlock(state.selectedBlock, 'up'); }
      if (state.selectedBlock && e.altKey && e.key === 'ArrowDown') { e.preventDefault(); moveBlock(state.selectedBlock, 'down'); }
    });

    pushHistory();
    syncExport();
  });

  window.CEBuilder = {
    getHTML() { return (state.outEl?.innerHTML || '').trim(); },
    getEmailHTML: getExportHTML,
    setHTML(html) { rebuildEditFromExport(stripToBodyInner(html || '')); syncExport(); },
    getDraft() { return document.getElementById('ceDraftExport').value; }
  };

})();
</script>
</body>
</html>
