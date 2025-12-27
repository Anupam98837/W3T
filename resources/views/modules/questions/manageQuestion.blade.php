{{-- resources/views/modules/quizz/manageQuestions.blade.php --}}
@section('title','Manage Questions')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

    <script>
  window.MathJax = {
    tex: {
      inlineMath: [['$', '$'], ['\\(', '\\)']],
      displayMath: [['\\[', '\\]'], ['$$', '$$']],
      processEscapes: true
    },
    options: {
      skipHtmlTags: ['script','noscript','style','textarea','pre','code']
    }
  };
</script>
<script id="MathJax-script" async
        src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"></script>

    
    <style>
    :root{
        --ink: #111827;
        --muted: #6b7280;
        --surface: #ffffff;
        --border: #e5e7eb;
        --primary: #4f46e5;
        --danger: #ef4444;
        --bg-gray: #f9fafb;
    }
    html.theme-dark :root{ 
        --surface: #1e293b; 
        --border: #334155; 
        --bg-gray: #0f172a; 
        --ink: #f1f5f9;
    }

    body{ background: var(--bg-gray); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; padding: 0; }
    .container{ max-width: 1400px; margin: 0 auto; padding: 20px; }

    .quiz-header{
    max-width: 1400px;
    margin: 0 auto 16px;
    padding: 14px 20px;
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: 12px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}
.quiz-header-main{ flex: 1; min-width: 0; }

.quiz-chip{
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #eef2ff;
    color: #4338ca;
    margin-bottom: 6px;
}

.quiz-header-title{
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--ink);
}
.quiz-header-desc{
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--muted);
    max-width: 640px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.quiz-header-meta{
    display: flex;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.quiz-header-meta-item{
    text-align: right;
    min-width: 90px;
}
.quiz-header-meta-label{
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
}
.quiz-header-meta-value{
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
}

@media (max-width: 768px){
    .quiz-header{
        flex-direction: column;
        align-items: flex-start;
    }
    .quiz-header-meta{
        width: 100%;
        justify-content: flex-start;
    }
    .quiz-header-meta-item{
        text-align: left;
    }
}


    /* Layout */
    .layout-grid{ 
        display: grid; 
        grid-template-columns: 280px 1fr; 
        gap: 20px; 
        align-items: start;
    }
    @media (max-width: 1024px){ .layout-grid{ grid-template-columns: 1fr; } }

    /* Sidebar */
    .sidebar{ 
        background: var(--surface); 
        border: 1px solid var(--line-strong); 
        border-radius: 12px; 
        overflow: hidden;
        position: sticky;
        top: 20px;
    }
    .sidebar-header{ 
        padding: 16px; 
        border-bottom: 1px solid var(--line-soft); 
        background: var(--bg-body);
    }
    .sidebar-header h6{ 
        margin: 0; 
        font-size: 14px; 
        font-weight: 600; 
        color: var(--ink);
    }
    .sidebar-search{
        padding: 12px 16px;
        border-bottom: 1px solid var(--line-soft);
    }
    .sidebar-search input{
        width: 100%;
        padding: 8px 12px 8px 36px;
        border: 1px solid var(--line-soft);
        border-radius: 8px;
        font-size: 13px;
        background: var(--surface);
    }
    .sidebar-search .search-icon{
        position: absolute;
        left: 28px;
        top: 22px;
        color: var(--muted);
        font-size: 12px;
    }

    .question-list{ max-height: 600px; overflow-y: auto; }
    .question-item{ 
        padding: 12px 16px; 
        border-bottom: 1px solid var(--line-soft); 
        cursor: pointer; 
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        position: relative;
    }
    .question-item:hover{ background: var(--bg-body); }
    .question-item.active{ 
        background: #eef2ff; 
        border-left: 3px solid var(--primary);
    }
    html.theme-dark .question-item.active{ background: #312e81; }

    .question-item .q-number{
        font-size: 11px;
        color: var(--muted);
        min-width: 24px;
    }
    .question-item .q-title{
        flex: 1;
        font-size: 13px;
        color: var(--ink);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .question-item .q-badge{
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }
    .question-item .q-badge.medium{ background: #fef3c7; color: #92400e; }
    .question-item .q-badge.staff{ background: #dbeafe; color: #1e40af; }

    /* Three-dot menu */
    .question-menu{
        position: relative;
    }
    .menu-btn{
        width: 28px;
        height: 28px;
        border: none;
        background: transparent;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--muted);
        transition: all 0.2s;
    }
    .menu-dropdown{
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--surface);
        border: 1px solid var(--line-strong);
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 100;
        min-width: 120px;
        display: none;
    }
    .menu-dropdown.show{
        display: block;
    }
    .menu-item{
        padding: 8px 12px;
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .menu-item:hover{
        background: var(--bg-gray);
    }
    .menu-item.edit{ color: var(--primary); }
    .menu-item.delete{ color: var(--danger); }

    /* Main Content */
    .main-content{
        background: var(--surface);
        border: 1px solid var(--line-strong);
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }
    .content-header{
        padding: 16px 20px;
        border-bottom: 1px solid var(--line-strong);
        background: var(--bg-body);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .content-header h5{
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--ink);
    }

    .content-body{
        padding: 24px;
        max-height: calc(100vh - 200px);
        overflow-y: auto;
        position: relative;
    }

    /* Loader */
    .loader-overlay{
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
        border-radius: 12px;
        display: none;
    }
    html.theme-dark .loader-overlay{
        background: rgba(0,0,0,0.6);
    }
    .loader{
        width: 40px;
        height: 40px;
        border: 4px solid var(--border);
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin{
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Form Groups */
    .form-group{ margin-bottom: 20px; }
    .form-label{ 
        display: block; 
        margin-bottom: 6px; 
        font-size: 13px; 
        font-weight: 600; 
        color: var(--ink);
    }
    .form-control, .form-select{
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        background: var(--surface);
        color: var(--ink);
    }
    .form-control:focus, .form-select:focus{
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    /* Rich Text Editor */
    .rte-wrapper{
        border: 1px solid var(--line-soft);
        border-radius: 8px;
        overflow: hidden;
    }
    .rte-wrapper:focus-within{
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .rte-toolbar{
        padding: 8px;
        background: var(--surface);
        border-bottom: 1px solid var(--line-soft);
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
    }
    .rte-toolbar button{
        width: 32px;
        height: 32px;
        border: 1px solid var(--line-soft);
        background: var(--surface);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: var(--text-color);
    }
    .rte-toolbar button:hover{
        background: var(--primary);
        border-color: var(--primary);
    }
    .rte-area{
        padding: 12px;
        min-height: 120px;
        background: var(--surface);
        outline: none;
        font-size: 14px;
        line-height: 1.6;
    }
    .rte-area img{
        max-width: 100%;
        height: auto;
        border-radius: 4px;
        margin: 4px 0;
        display: block;
        cursor: move;
        position: relative;
    }

    /* Image Resize Handles */
    .rte-area img.resizable{
        border: 2px dashed var(--primary);
    }
    .resize-handle{
        position: absolute;
        width: 12px;
        height: 12px;
        background: var(--primary);
        border: 2px solid var(--surface);
        border-radius: 50%;
        z-index: 1000;
        cursor: nwse-resize;
    }
    .resize-handle.se{
        right: -6px;
        bottom: -6px;
    }
    .resize-handle.sw{
        left: -6px;
        bottom: -6px;
        cursor: nesw-resize;
    }
    .resize-handle.ne{
        right: -6px;
        top: -6px;
        cursor: nesw-resize;
    }
    .resize-handle.nw{
        left: -6px;
        top: -6px;
        cursor: nwse-resize;
    }

    /* Code Editor Tab */
    .editor-tabs{
        display: flex;
        border-bottom: 1px solid var(--line-soft);
        background: var(--surface);
    }
    .editor-tab{
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }
    .editor-tab.active{
        border-bottom-color: var(--primary);
        color: var(--primary);
        background: var(--surface);
    }
    .editor-content{
        display: none;
    }
    .editor-content.active{
        display: block;
    }
    .code-editor{
        width: 100%;
        height: 200px;
        padding: 12px;
        border: none;
        background: #1e1e1e;
        color: #d4d4d4;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.5;
        resize: vertical;
        outline: none;
    }
    html.theme-dark .code-editor{
        background: #0f172a;
        color: #e2e8f0;
    }

    /* Answer Options */
    .answer-option{
        padding: 16px;
        border: 1px solid var(--line-strong);
        border-radius: 8px;
        margin-bottom: 12px;
        background: var(--surface);
        transition: all 0.2s;
    }
    .answer-option:hover{
        border-color: var(--primary);
    }
    .answer-option.correct{
        border-color: #10b981;
        background: #f0fdf4;
    }
    html.theme-dark .answer-option.correct{ background: #064e3b; }

    .answer-header{
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }
    .answer-check{
        width: 20px;
        height: 20px;
        margin-top: 2px;
        cursor: pointer;
    }
    .answer-label{
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 8px;
    }
    .answer-content{
        flex: 1;
    }
    .answer-actions{
        display: flex;
        gap: 12px;
        margin-top: 12px;
    }
    .answer-delete{
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border: 1px solid var(--danger);
        background: transparent;
        border-radius: 6px;
        font-size: 12px;
        color: var(--danger);
        cursor: pointer;
        transition: all 0.2s;
    }
    .answer-delete:hover{
        background: #fee;
        border-color: var(--danger);
    }

    /* Buttons */
    .btn{
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary{
        background: var(--primary);
        color: white;
    }
    .btn-primary:hover{
        background: #4338ca;
        transform: translateY(-1px);
    }
    .btn-light{
        background: var(--surface);
        color: var(--ink);
        border: 1px solid var(--border);
    }
    .btn-light:hover{
        background: var(--bg-gray);
        border-color: var(--primary);
    }
    .btn-danger{
        background: var(--danger);
        color: white;
    }
    .btn-danger:hover{
        background: #dc2626;
    }
    .btn-sm{
        padding: 6px 12px;
        font-size: 13px;
    }

    .content-footer{
        padding: 16px 24px;
        border-top: 1px solid var(--line-strong);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: var(--bg-body);
    }

    /* Add Button */
    .add-option-btn{
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 2px dashed var(--border);
        background: transparent;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: var(--primary);
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        justify-content: center;
    }
    .add-option-btn:hover{
        border-color: var(--primary);
        background: #f5f3ff;
    }

    /* Row Layout */
    .row{ display: flex; gap: 16px; margin-bottom: 20px; }
    .col{ flex: 1; }

    /* Scrollbar */
    .question-list::-webkit-scrollbar,
    .content-body::-webkit-scrollbar{
        width: 8px;
    }
    .question-list::-webkit-scrollbar-thumb,
    .content-body::-webkit-scrollbar-thumb{
        background: rgba(0,0,0,0.2);
        border-radius: 4px;
    }

    /* Toast */
    .toast-container{
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
    .toast{
        min-width: 300px;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        align-items: center;
        gap: 12px;
    }
    .toast.show{ display: flex; }
    .toast.success{
        background: #10b981;
        color: white;
    }
    .toast.error{
        background: #ef4444;
        color: white;
    }

    .section-title{
        font-size: 15px;
        font-weight: 600;
        color: var(--ink);
        margin: 0 0 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--line-strong);
    }

    .info-box{
        padding: 12px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        font-size: 13px;
        color: #1e40af;
        margin-top: 12px;
    }
    html.theme-dark .info-box{
        background: #1e3a8a;
        border-color: #3b82f6;
        color: #bfdbfe;
    }

    .empty-state{
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
    }
    .empty-state i{
        font-size: 48px;
        opacity: 0.3;
        margin-bottom: 16px;
    }

    /* SweetAlert2 customization */
    .swal2-popup{
        background: var(--surface) !important;
        color: var(--ink) !important;
    }
    .swal2-title, .swal2-content{
        color: var(--ink) !important;
    }
    .swal2-input{
        background: var(--surface) !important;
        border-color: var(--border) !important;
        color: var(--ink) !important;
    }
    .swal2-confirm{
        background: var(--primary) !important;
    }

    /* Fill in the Blank specific styles */
    .dash-btn-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    
    .dash-btn {
        padding: 6px 12px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .dash-btn:hover {
        background: #4338ca;
    }
    
    .fill-blank-inputs {
        margin-top: 16px;
        padding: 16px;
        border: 1px solid var(--line-soft);
        border-radius: 8px;
        background: var(--surface);
    }
    
    .fill-blank-input {
        margin-bottom: 12px;
    }
    
    .fill-blank-input label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
    }
    
    .fill-blank-input input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--surface);
        color: var(--ink);
    }
    
    .fill-blank-note {
        font-size: 12px;
        color: var(--muted);
        margin-top: 8px;
        font-style: italic;
    }

    /* Dash placeholder styling */
    .dash-placeholder {
        background: #f3f4f6;
        border: 1px dashed #d1d5db;
        padding: 2px 6px;
        border-radius: 4px;
        margin: 0 2px;
        color: #374151;
        font-weight: 500;
        display: inline-block;
    }
    html.theme-dark .dash-placeholder {
        background: #374151;
        border-color: #6b7280;
        color: #f3f4f6;
    }

    /* ===== Media Picker (W3T) ===== */
.mp-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9998;display:flex;align-items:center;justify-content:center}
.mp-dialog{width:min(1100px,96vw);height:min(90vh,880px);background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.25);display:flex;flex-direction:column;overflow:hidden}
.mp-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border);background:var(--bg-gray)}
.mp-title{font-weight:700;color:var(--ink)}
.mp-close{border:0;background:transparent;color:var(--ink);width:36px;height:36px;border-radius:8px;cursor:pointer}
.mp-toolbar{display:flex;gap:10px;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border)}
.mp-tabs{display:flex;gap:6px}
.mp-tab{border:1px solid var(--border);background:var(--surface);padding:8px 12px;border-radius:8px;cursor:pointer;color:var(--ink);font-weight:600}
.mp-tab.active{border-color:var(--primary);color:var(--primary);box-shadow:0 0 0 2px rgba(79,70,229,.1)}
.mp-filters{display:flex;gap:8px;align-items:center}
.mp-input{height:36px;border:1px solid var(--border);background:var(--surface);border-radius:8px;padding:0 10px;color:var(--ink)}
.mp-body{flex:1;overflow:auto;position:relative}
.mp-panel{height:100%;padding:16px 16px 8px}
.mp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px}
.mp-card{border:1px solid var(--border);background:var(--surface);border-radius:12px;overflow:hidden;cursor:pointer;display:flex;flex-direction:column;transition:.15s}
.mp-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-2, 0 6px 18px rgba(0,0,0,.08))}
.mp-thumb{width:100%;height:120px;object-fit:cover;background:#eee}
.mp-meta{padding:8px 10px;display:flex;justify-content:space-between;gap:8px;align-items:center}
.mp-title-sm{font-size:12px;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:120px}
.mp-tag{font-size:10px;background:#eef2ff;color:#4338ca;padding:2px 6px;border-radius:6px}
.mp-del{border:0;background:transparent;color:#ef4444;cursor:pointer}
.mp-empty,.mp-loading{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:var(--muted)}
.mp-upload{display:grid;grid-template-columns:1.3fr .7fr;gap:16px}
.mp-drop{border:2px dashed #cbd5e1;border-radius:12px;padding:28px;text-align:center;background:#f8fafc;cursor:pointer}
.mp-drop.drag{background:#eef2ff;border-color:#64748b}
.mp-preview{margin-top:14px;display:flex;align-items:center;gap:12px}
.mp-preview img{width:120px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border)}
.mp-footer{padding:12px 16px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg-gray)}
.muted{color:var(--muted)}

 .swal2-container { z-index: 99999 !important; }

 .content-header .heading{ display:flex; align-items:center; gap:8px; }
.content-header .actions{ display:flex; align-items:center; gap:8px; }
.back-btn{
  width:32px;height:32px;border:1px solid var(--border);background:var(--surface);
  border-radius:8px;color:var(--ink);cursor:pointer
}
.back-btn:hover{ border-color:var(--primary); color:var(--primary); }

.question-item .q-badge.easy   { background:#dcfce7; color:#166534; }
.question-item .q-badge.medium { background:#fef3c7; color:#92400e; } /* existing ok */
.question-item .q-badge.hard   { background:#fee2e2; color:#991b1b; }

.question-item-main{
    flex: 1;
    min-width: 0;
    display: flex;              /* title + chips in one row */
    align-items: center;
    gap: 8px;
}

.question-item-title{
    font-size: 13px;
    color: var(--ink);
    margin-bottom: 0;
    white-space: nowrap;        /* single line */
    overflow: hidden;
    text-overflow: ellipsis;    /* ... when too long */
}

.question-item-meta{
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;             /* chips don’t disappear */
}

.question-item .q-type{
    padding: 2px 7px;
    border-radius: 999px;
    font-size: 10px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--muted);
}

.question-item .q-type i{
    font-size: 10px;
}

/* hide SC / MCQ / Fill text – keep only icon */
.question-item .q-type span{
    display: none;
}


/* keep your difficulty colours, plus per-type tint (if not already there) */
.question-item .q-type.single_choice{
    background:#eef2ff;border-color:#c7d2fe;color:#4338ca;
}
.question-item .q-type.multiple_choice{
    background:#f0fdf4;border-color:#bbf7d0;color:#166534;
}
.question-item .q-type.true_false{
    background:#e0f2fe;border-color:#bae6fd;color:#075985;
}
.question-item .q-type.fill_in_the_blank{
    background:#f9fafb;border-color:#e5e7eb;color:#374151;
}


/* ===== Question Preview Modal (refined) ===== */
.preview-overlay{
    position:fixed;
    inset:0;
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9998;
    background:rgba(15,23,42,0.35);
    backdrop-filter:blur(3px);
}

html.theme-dark .preview-overlay{
    background:rgba(15,23,42,0.75);
}

.preview-modal{
    width:min(800px,96vw);
    max-height:82vh;
    background:var(--surface);
    border-radius:18px;
    border:1px solid var(--line-strong);
    box-shadow:0 22px 55px rgba(15,23,42,0.45);
    display:flex;
    flex-direction:column;
    overflow:hidden;
}

/* Header */
.preview-header{
    padding:14px 20px 12px;
    border-bottom:1px solid var(--line-strong);
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    background:linear-gradient(
        135deg,
        var(--bg-body),
        rgba(79,70,229,0.04)
    );
}
.preview-title{
    margin:2px 0 0;
    font-size:18px;
    font-weight:600;
    color:var(--ink);
}
.preview-chips{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-bottom:4px;
}
.preview-chip{
    font-size:10px;
    padding:3px 9px;
    border-radius:999px;
    background:#eef2ff;
    color:#4338ca;
    text-transform:uppercase;
    letter-spacing:0.04em;
    font-weight:600;
}

/* difficulty tints */
.preview-chip.diff-easy{background:#dcfce7;color:#166534;}
.preview-chip.diff-medium{background:#fef3c7;color:#92400e;}
.preview-chip.diff-hard{background:#fee2e2;color:#991b1b;}

/* Body */
.preview-body{
    padding:16px 20px 4px;
    overflow-y:auto;
    background:var(--surface);
}
.preview-desc{
    font-size:13px;
    color:var(--muted);
    margin-bottom:12px;
}
.preview-options{
    margin-top:4px;
}

/* Options */
.preview-option{
    border-radius:12px;
    border:1px solid var(--border);
    padding:10px 12px;
    display:flex;
    gap:10px;
    align-items:flex-start;
    margin-bottom:8px;
    background:var(--bg-gray);
    transition:
        box-shadow .15s ease,
        border-color .15s ease,
        background .15s ease,
        transform .12s ease;
}
.preview-option:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 18px rgba(15,23,42,0.12);
}
.preview-option-label{
    width:24px;
    height:24px;
    border-radius:999px;
    border:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:11px;
    font-weight:600;
    margin-top:2px;
    color:var(--muted);
    background:var(--surface);
}
.preview-option-content{
    flex:1;
    min-width:0;
    font-size:13px;
    line-height:1.5;
}

/* Correct option state */
.preview-option.correct{
    border-color:#16a34a;
    background:#f0fdf4;
    box-shadow:
        0 0 0 1px rgba(22,163,74,0.24),
        0 10px 24px rgba(22,163,74,0.18);
}
.preview-option.correct .preview-option-label{
    background:#16a34a;
    border-color:#16a34a;
    color:#ecfdf5;
}

/* Fill-in-the-blank blanks */
.preview-blank{
    display:inline-block;
    min-width:40px;
    border-bottom:2px solid #9ca3af;
    margin:0 3px;
}

/* Explanation section */
.preview-extra{
    margin-top:14px;
    font-size:13px;
}
.preview-extra-title{
    font-weight:600;
    margin-bottom:4px;
}

/* Footer */
.preview-footer{
    padding:10px 20px 14px;
    border-top:1px solid var(--line-strong);
    display:flex;
    justify-content:flex-end;
    gap:8px;
    background:var(--bg-body);
}

/* ---- Dark mode tweaks ---- */
html.theme-dark .preview-modal{
    background:#020617;
    border-color:#1f2937;
}
html.theme-dark .preview-header{
    background:linear-gradient(
        135deg,
        #020617,
        rgba(79,70,229,0.12)
    );
    border-bottom-color:#1f2937;
}
html.theme-dark .preview-body{
    background:#020617;
}
html.theme-dark .preview-footer{
    background:#020617;
    border-top-color:#1f2937;
}
html.theme-dark .preview-option{
    background:#020617;
    border-color:#334155;
    box-shadow:none;
}
html.theme-dark .preview-option:hover{
    box-shadow:0 10px 26px rgba(15,23,42,0.8);
}
html.theme-dark .preview-option-label{
    border-color:#475569;
    background:#020617;
    color:#cbd5f5;
}
html.theme-dark .preview-option.correct{
    background:#022c22;
    border-color:#16a34a;
    box-shadow:
        0 0 0 1px rgba(34,197,94,0.55),
        0 12px 30px rgba(22,163,74,0.45);
}

mjx-container,
    mjx-container[display="block"],
    .mjx-chtml {
      display: inline-block !important;
    }
    mjx-container svg,
    mjx-container[display="block"] svg,
    .mjx-chtml svg {
      vertical-align: middle;
    }

    </style>
</head>
<body>
    @section('content')
    <div class="container">


    {{-- Quiz header card --}}
    <div class="quiz-header">
        <div class="quiz-header-main">
            <div class="quiz-chip">
                <i class="fa fa-clipboard-list"></i>
                <span>Quiz</span>
            </div>
            <h4 class="quiz-header-title" id="quizTitle">Loading quiz…</h4>
            <p class="quiz-header-desc" id="quizDesc">
                Please wait while we fetch quiz details.
            </p>
        </div>

        <div class="quiz-header-meta">
            <div class="quiz-header-meta-item">
                <div class="quiz-header-meta-label">Questions</div>
                <div class="quiz-header-meta-value" id="quizQuestionCountTop">0</div>
            </div>
            <div class="quiz-header-meta-item">
                <div class="quiz-header-meta-label">Total time</div>
                <div class="quiz-header-meta-value" id="quizTotalTime">—</div>
            </div>
        </div>
    </div>

        <div class="layout-grid">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <h6><i class="fa fa-list-ul me-2"></i>Quiz Questions (<span id="questionsCount">0</span>)</h6>
                </div>
                <div class="sidebar-search" style="position: relative;">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" id="qSearch" placeholder="Search questions...">
                </div>
                <div id="qList" class="question-list">
                    <div class="empty-state">
                        <i class="fa fa-spinner fa-spin"></i>
                        <div>Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <div class="loader-overlay" id="contentLoader">
                    <div class="loader"></div>
                </div>
                
                <div class="content-header">
  <div class="heading">
    <button id="btnBack" class="back-btn" title="Back">
      <i class="fa fa-arrow-left"></i>
    </button>
    <h5 class="m-0">Edit Question</h5>
  </div>
  <div class="actions">
    <button id="btnHelp" class="btn btn-light btn-sm" title="How to add questions">
      <i class="fa fa-circle-question"></i>
    </button>
    <button id="btnNew" class="btn btn-primary btn-sm">
      <i class="fa fa-plus"></i> New
    </button>
  </div>
</div>


                <div class="content-body">
                    <form id="qForm" novalidate>
                        <input type="hidden" id="qId">

                        <!-- Basic Info Section -->
                        <div class="section-title">
                            <i class="fa fa-info-circle"></i> Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-label">Question Type</label>
                                    <select id="qType" class="form-select">
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="single_choice">Single Choice</option>
                                        <option value="true_false">True / False</option>
                                        <option value="fill_in_the_blank">Fill in the Blank</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-label">Marks</label>
                                    <input id="qMarks" type="number" min="1" class="form-control" value="1">
                                </div>
                            </div>
                            <div class="col">
  <div class="form-group">
    <label class="form-label">Difficulty</label>
    <select id="qDifficulty" class="form-select">
      <option value="easy">Easy</option>
      <option value="medium" selected>Medium</option>
      <option value="hard">Hard</option>
    </select>
  </div>
</div>

                            <div class="col">
                                <div class="form-group">
                                    <label class="form-label">Display Order</label>
                                    <input id="qOrder" type="number" min="1" class="form-control" value="1">
                                </div>
                            </div>
                        </div>

                        <!-- Question Content Section -->
                        <div class="section-title">
                            <i class="fa fa-file-text"></i> Question Content
                        </div>

                        <!-- Question Title -->
                        <div class="form-group">
                            <div class="dash-btn-container">
                                <label class="form-label">Question Title</label>
                                <button type="button" id="btnAddDash" class="dash-btn" style="display: none;">
                                    <i class="fa fa-plus"></i> Add Dash
                                </button>
                            </div>
                            <div id="edTitle" class="rte-wrapper">
                                <div class="editor-tabs">
                                    <div class="editor-tab active" data-tab="visual">Visual</div>
                                    <div class="editor-tab" data-tab="code">Code</div>
                                </div>
                                <div class="editor-content active" data-tab="visual">
                                    <div class="rte-toolbar">
                                        <button data-cmd="bold" type="button" title="Bold"><i class="fa fa-bold"></i></button>
                                        <button data-cmd="italic" type="button" title="Italic"><i class="fa fa-italic"></i></button>
                                        <button data-cmd="underline" type="button" title="Underline"><i class="fa fa-underline"></i></button>
                                        <button data-cmd="insertUnorderedList" type="button" title="List"><i class="fa fa-list-ul"></i></button>
                                        <button data-link type="button" title="Link"><i class="fa fa-link"></i></button>
                                        <button data-image type="button" title="Image"><i class="fa fa-image"></i></button>
                                    </div>
                                    <div class="rte-area" contenteditable="true" spellcheck="true"></div>
                                </div>
                                <div class="editor-content" data-tab="code">
                                    <textarea class="code-editor" id="edTitle-code"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label class="form-label">Description (Optional)</label>
                            <div id="edDesc" class="rte-wrapper">
                                <div class="editor-tabs">
                                    <div class="editor-tab active" data-tab="visual">Visual</div>
                                    <div class="editor-tab" data-tab="code">Code</div>
                                </div>
                                <div class="editor-content active" data-tab="visual">
                                    <div class="rte-toolbar">
                                        <button data-cmd="bold" type="button"><i class="fa fa-bold"></i></button>
                                        <button data-cmd="italic" type="button"><i class="fa fa-italic"></i></button>
                                        <button data-cmd="underline" type="button"><i class="fa fa-underline"></i></button>
                                        <button data-link type="button" title="Link"><i class="fa fa-link"></i></button>
                                        <button data-image type="button" title="Image"><i class="fa fa-image"></i></button>
                                    </div>
                                    <div class="rte-area" contenteditable="true" spellcheck="true"></div>
                                </div>
                                <div class="editor-content" data-tab="code">
                                    <textarea class="code-editor" id="edDesc-code"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Answers Section -->
                        <div class="section-title">
                            <i class="fa fa-list-check"></i> Answers
                        </div>

                        <!-- Options -->
                        <div class="form-group">
                            <label class="form-label">Answer Options</label>
                            <div id="ansWrap"></div>
                            <div id="fillBlankWrap" class="fill-blank-inputs" style="display: none;">
                                <!-- Fill in the blank inputs will be generated here -->
                            </div>
                            <button id="btnAddAns" type="button" class="add-option-btn">
                                <i class="fa fa-plus"></i> Add Option
                            </button>
                        </div>

                        <!-- Explanation -->
                        <div class="form-group">
                            <label class="form-label">Answer Explanation (Optional)</label>
                            <div id="edExplain" class="rte-wrapper">
                                <div class="editor-tabs">
                                    <div class="editor-tab active" data-tab="visual">Visual</div>
                                    <div class="editor-tab" data-tab="code">Code</div>
                                </div>
                                <div class="editor-content active" data-tab="visual">
                                    <div class="rte-toolbar">
                                        <button data-cmd="bold" type="button"><i class="fa fa-bold"></i></button>
                                        <button data-cmd="italic" type="button"><i class="fa fa-italic"></i></button>
                                        <button data-cmd="underline" type="button"><i class="fa fa-underline"></i></button>
                                        <button data-link type="button" title="Link"><i class="fa fa-link"></i></button>
                                        <button data-image type="button" title="Image"><i class="fa fa-image"></i></button>
                                    </div>
                                    <div class="rte-area" contenteditable="true" spellcheck="true"></div>
                                </div>
                                <div class="editor-content" data-tab="code">
                                    <textarea class="code-editor" id="edExplain-code"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="info-box">
                            <i class="fa fa-info-circle me-2"></i>
                            For <strong>Single Choice</strong> & <strong>True/False</strong>, only one answer can be marked correct. 
                            For <strong>Multiple Choice</strong>, select all correct answers. For <strong>Fill in the Blank</strong>, 
                            use the "Add Dash" button to insert {dash} placeholders in the question text.
                        </div>
                    </form>
                </div>

                <div class="content-footer">
                    <button id="btnCancel" class="btn btn-light">Cancel</button>
                    <button id="btnDelete" class="btn btn-danger" style="display: none;">
                        <i class="fa fa-trash"></i> Delete Question
                    </button>
                    <button id="btnSave" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Question
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container">
        <div id="successToast" class="toast success">
            <i class="fa fa-check-circle"></i>
            <span id="successMsg">Success!</span>
        </div>
        <div id="errorToast" class="toast error">
            <i class="fa fa-exclamation-circle"></i>
            <span id="errorMsg">Error!</span>
        </div>
    </div>

    <!-- Media Picker (W3T) -->
<div id="mediaPicker" class="mp-overlay" style="display:none;">
  <div class="mp-dialog">
    <div class="mp-header">
      <div class="mp-title"><i class="fa fa-images me-2"></i>Media Library</div>
      <button type="button" class="mp-close" id="mpCloseBtn"><i class="fa fa-times"></i></button>
    </div>

    <div class="mp-toolbar">
      <div class="mp-tabs" role="tablist">
        <button class="mp-tab active" data-tab="lib">Library</button>
        <button class="mp-tab" data-tab="upload">Upload</button>
      </div>
      <div class="mp-filters">
        <input id="mpSearch" class="mp-input" placeholder="Search title/description…">
        <select id="mpUsageTag" class="mp-input">
          <option value="">All tags</option>
        </select>
        <button id="mpRefresh" class="btn btn-light btn-sm"><i class="fa fa-rotate"></i></button>
      </div>
    </div>

    <div class="mp-body">
      <!-- LIBRARY -->
      <div class="mp-panel" data-tab="lib" style="display:block;">
        <div id="mpGrid" class="mp-grid"></div>
        <div id="mpEmpty" class="mp-empty">No media found.</div>
        <div id="mpLoading" class="mp-loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading…</div>
      </div>

      <!-- UPLOAD -->
      <div class="mp-panel" data-tab="upload">
        <div class="mp-upload">
          <div id="mpDrop" class="mp-drop">
            <i class="fa fa-cloud-arrow-up fa-2x mb-2"></i>
            <div>Drag & drop file here</div>
            <div class="hint">or click to choose</div>
            <input type="file" id="mpFile" class="d-none" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.7z,.txt">
            <button type="button" id="mpBrowse" class="btn btn-light btn-sm mt-2">Browse…</button>
          </div>

          <div class="mp-upload-meta">
            <label class="form-label">Title (optional)</label>
            <input id="mpTitle" class="mp-input" placeholder="Attachment title">
            <label class="form-label mt-2">Usage tag (optional)</label>
            <input id="mpTag" class="mp-input" placeholder="e.g., question, quiz, course">
            <button id="mpUploadBtn" class="btn btn-primary mt-3">
              <i class="fa fa-cloud-arrow-up me-1"></i>Upload
            </button>

            <div id="mpPreview" class="mp-preview" style="display:none;">
              <img id="mpPreviewImg" alt="" />
              <div class="meta">
                <div id="mpPreviewName"></div>
                <div id="mpPreviewInfo" class="muted"></div>
              </div>
              <button type="button" id="mpClear" class="btn btn-light btn-sm">Clear</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mp-footer">
      <div class="muted small" id="mpSelected">Selected: —</div>
      <div class="mp-actions">
        <button class="btn btn-light" id="mpCancel">Close</button>
        <button class="btn btn-primary" id="mpInsert" disabled><i class="fa fa-check me-1"></i>Insert</button>
      </div>
    </div>
  </div>
</div>

<!-- Question Preview Modal -->
<div id="previewOverlay" class="preview-overlay">
  <div class="preview-modal">
    <div class="preview-header">
      <div>
        <div id="previewChips" class="preview-chips"></div>
        <h5 id="previewTitle" class="preview-title">Question preview</h5>
      </div>
      <button type="button" class="btn btn-light btn-sm" id="previewCloseBtn">
        <i class="fa fa-times"></i>
      </button>
    </div>
    <div class="preview-body">
      <div id="previewDesc" class="preview-desc" style="display:none;"></div>
      <div id="previewOptions" class="preview-options"></div>
      <div id="previewExtra" class="preview-extra"></div>
    </div>
    <div class="preview-footer">
      <button type="button" class="btn btn-light btn-sm" id="previewCloseBtn2">Close</button>
    </div>
  </div>
</div>


    @endsection

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
        var ROLE = (localStorage.getItem('role') || 'admin');
        var quizMeta = null; 

        function typesetMath(root) {
        if (!window.MathJax || !window.MathJax.typesetPromise) return;
        const nodes = root ? [root] : undefined;
        MathJax.typesetPromise(nodes).catch(function (err) {
            console.error('MathJax typeset error:', err);
        });
    }
        var previewOverlay = document.getElementById('previewOverlay');
var previewTitle   = document.getElementById('previewTitle');
var previewDesc    = document.getElementById('previewDesc');
var previewOptions = document.getElementById('previewOptions');
var previewExtra   = document.getElementById('previewExtra');
var previewChips   = document.getElementById('previewChips');

function openPreviewModal(){
    if (!previewOverlay) return;
    previewOverlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closePreviewModal(){
    if (!previewOverlay) return;
    previewOverlay.style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('previewCloseBtn')?.addEventListener('click', closePreviewModal);
document.getElementById('previewCloseBtn2')?.addEventListener('click', closePreviewModal);
previewOverlay?.addEventListener('click', function(e){
    if (e.target === previewOverlay) closePreviewModal();
});


document.getElementById('btnBack')?.addEventListener('click', function(){
  location.href = `/quizz/manage`;
});

document.getElementById('btnHelp')?.addEventListener('click', function(){
  Swal.fire({
    title: 'How to add questions',
    width: 800,
    html: `
      <div style="text-align:left;font-size:13px;line-height:1.55">
        <h6>1) Multiple Choice</h6>
        <p>Use <b>Multiple Choice</b> when more than one answer can be correct. Mark all correct options using the checkboxes.</p>

        <h6>2) Single Choice</h6>
        <p>Use <b>Single Choice</b> when exactly one answer is correct. Only one option can be selected as correct.</p>

        <h6>3) True / False</h6>
        <p>Prebuilt with two options: True and False. Mark the correct one.</p>

        <h6>4) Fill in the Blank</h6>
        <p>Click <b>Add Dash</b> to insert <code>{dash}</code> placeholders inside the question text (each dash creates one blank). Then fill the answers below in the same order.</p>
        <ul style="margin:6px 0 12px 18px">
          <li>At least one <code>{dash}</code> is required for this type.</li>
          <li>All blanks must have an answer.</li>
        </ul>

        <h6>Marks, Order & Difficulty</h6>
        <p>Set <b>Marks</b>, display <b>Order</b>, and <b>Difficulty</b> (Easy/Medium/Hard) from Basic Information.</p>

        <h6>Media</h6>
        <p>Use the image button in editors to pick from the media library. Images are inserted at your cursor position and can be resized.</p>
      </div>
    `,
    confirmButtonText: 'Got it'
  });
});

        
        function showToast(type, msg){
            var toast = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
            var msgEl = document.getElementById(type === 'success' ? 'successMsg' : 'errorMsg');
            if (toast && msgEl) {
                msgEl.textContent = msg;
                toast.classList.add('show');
                setTimeout(function(){ toast.classList.remove('show'); }, 3000);
            }
        }

        function esc(s){ 
            var m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
            return String(s||'').replace(/[&<>"']/g, function(c){ return m[c]; }); 
        }

        function stripHtml(html, keepTags = []) { 
            if (!html) return '';
            
            // Create a temporary div
            var d = document.createElement('div'); 
            d.innerHTML = html;
            
            // Remove script, style, head, meta, link, title elements
            const tagsToRemove = ['script', 'style', 'head', 'meta', 'link', 'title'];
            tagsToRemove.forEach(tag => {
                d.querySelectorAll(tag).forEach(el => el.remove());
            });
            
            // Return text content for preview, or sanitized HTML for editing
            return d.textContent || ''; 
        }

        // Function to truncate text to 3-4 words
        function truncateText(text, maxWords = 4) {
            if (!text) return '';
            const words = text.trim().split(/\s+/);
            if (words.length <= maxWords) return text;
            return words.slice(0, maxWords).join(' ') + '...';
        }

        async function apiFetch(url, options){
            options = options || {};
            options.headers = Object.assign({ 'Accept': 'application/json' }, options.headers || {});
            if (TOKEN) options.headers['Authorization'] = 'Bearer ' + TOKEN;
            
            const res = await fetch(url, options);
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            let data, isJson = false;
            
            try{
                if (ct.includes('application/json')) { 
                    data = await res.json(); 
                    isJson = true; 
                } else { 
                    data = await res.text(); 
                }
            } catch(_){ 
                try{ data = await res.text(); } catch(__){} 
            }
            
            const looksHtml = typeof data === 'string' && /<\s*(!doctype|html)\b/i.test(data);
            return { ok: res.ok && !looksHtml, status: res.status, isJson: isJson && !looksHtml, data, looksHtml };
        }

        /* ===================== Media Picker (W3T) ===================== */
(function(){
  // DOM
  const mpEl        = document.getElementById('mediaPicker');
  const mpCloseBtn  = document.getElementById('mpCloseBtn');
  const mpCancel    = document.getElementById('mpCancel');
  const mpInsert    = document.getElementById('mpInsert');
  const mpTabs      = document.querySelectorAll('.mp-tab');
  const mpPanels    = document.querySelectorAll('.mp-panel');
  const mpSearch    = document.getElementById('mpSearch');
  const mpUsageTag  = document.getElementById('mpUsageTag');
  const mpRefresh   = document.getElementById('mpRefresh');
  const mpGrid      = document.getElementById('mpGrid');
  const mpEmpty     = document.getElementById('mpEmpty');
  const mpLoading   = document.getElementById('mpLoading');
  const mpSelected  = document.getElementById('mpSelected');

  // upload
  const mpDrop      = document.getElementById('mpDrop');
  const mpFile      = document.getElementById('mpFile');
  const mpBrowse    = document.getElementById('mpBrowse');
  const mpTitle     = document.getElementById('mpTitle');
  const mpTag       = document.getElementById('mpTag');
  const mpUploadBtn = document.getElementById('mpUploadBtn');
  const mpPreview   = document.getElementById('mpPreview');
  const mpPreviewImg= document.getElementById('mpPreviewImg');
  const mpPreviewName=document.getElementById('mpPreviewName');
  const mpPreviewInfo=document.getElementById('mpPreviewInfo');
  const mpClear     = document.getElementById('mpClear');

  // State
  let mpCtx = { editor: null, usageTag: '', category: 'image' };
  let mpList = [];
  let mpSel = null;
  let mpFileObj = null;

  // Expose opener to outer scope
  window.openMediaPicker = function(editorArea, opts){
    mpCtx.editor   = editorArea;
    mpCtx.usageTag = (opts && opts.usageTag) || '';
    mpCtx.category = (opts && opts.category) || 'image';

    // prefill usage tag on upload tab
    mpTag.value = mpCtx.usageTag || '';
    mpSearch.value = '';
    mpSel = null; setSelectedInfo();

    // show overlay
    mpEl.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // tab: library
    switchTab('lib');

    // load list
    loadLibrary();
  };

  function closePicker(){
    mpEl.style.display = 'none';
    document.body.style.overflow = '';
    mpSel = null; setSelectedInfo();
  }

  function switchTab(key){
    mpTabs.forEach(t => t.classList.toggle('active', t.dataset.tab === key));
    mpPanels.forEach(p => p.style.display = (p.dataset.tab === key ? 'block' : 'none'));
  }

  // library
  async function loadLibrary(){
    mpLoading.style.display = 'flex';
    mpEmpty.style.display   = 'none';
    mpGrid.innerHTML = '';

    // Build query (images only for editor)
    const params = new URLSearchParams({
      per_page:'60',
      page:'1',
      category: mpCtx.category || 'image',
      status:'active'
    });
    const q = mpSearch.value.trim();
    const tag = mpUsageTag.value.trim();
    if (q)   params.set('q', q);
    if (tag) params.set('usage_tag', tag);

    const resp = await apiFetch('/api/media?' + params.toString(), { method: 'GET' });
    mpLoading.style.display = 'none';

    if (!resp.ok) { handleApiFailure('Media load failed', resp); return; }

    const data = resp.isJson ? resp.data : {};
    mpList = Array.isArray(data?.data) ? data.data : [];

    // Fill tag filter from result set (unique)
    const tags = Array.from(new Set(mpList.map(x => (x.usage_tag || '').trim()).filter(Boolean))).sort();
    mpUsageTag.innerHTML = `<option value="">All tags</option>` + tags.map(t => `<option value="${t}">${t}</option>`).join('');
    if (tag) mpUsageTag.value = tag;

    renderGrid();
  }


  function renderGrid(){
    if (!mpList.length){
      mpEmpty.style.display = 'flex';
      mpGrid.innerHTML = '';
      return;
    }
    mpEmpty.style.display = 'none';
    mpGrid.innerHTML = mpList.map(item => {
      const thumb = item.category === 'image' ? item.url : iconFor(item.category);
      return `
      <div class="mp-card" data-id="${item.id}" data-uuid="${item.uuid}">
        ${item.category === 'image'
          ? `<img class="mp-thumb" src="${item.url}" alt="">`
          : `<div class="mp-thumb" style="display:flex;align-items:center;justify-content:center;font-size:32px">${iconFor(item.category)}</div>`
        }
        <div class="mp-meta">
          <div class="mp-title-sm" title="${esc(item.title||'')}">${esc(item.title|| (item.ext?('.'+item.ext):' (untitled)'))}</div>
          <div class="d-flex align-items-center gap-2">
            ${item.usage_tag ? `<span class="mp-tag">${esc(item.usage_tag)}</span>`:''}
            <button class="mp-del" title="Delete" data-del="${item.id}"><i class="fa fa-trash"></i></button>
          </div>
        </div>
      </div>`;
    }).join('');

    // Select/Insert on click
    mpGrid.querySelectorAll('.mp-card').forEach(card => {
      card.addEventListener('click', async (e) => {
        // delete?
        const delBtn = e.target.closest('[data-del]');
        if (delBtn){
          e.stopPropagation();
          await deleteMedia(delBtn.getAttribute('data-del'));
          return;
        }
        const id = card.getAttribute('data-id');
        const row = mpList.find(x => String(x.id) === String(id));
        if (!row) return;

        // select + enable insert
        mpSel = row; setSelectedInfo();

        // UX: single click = select; double-click = insert immediately
        if (e.detail >= 2) insertSelected();
      });
    });
  }

  function setSelectedInfo(){
    if (!mpSel){ mpSelected.textContent = 'Selected: —'; mpInsert.disabled = true; return; }
    mpSelected.textContent = `Selected: ${mpSel.title || mpSel.uuid} (${mpSel.category}, ${mpSel.ext || 'file'})`;
    mpInsert.disabled = false;
  }

async function deleteMedia(id){
  const c = await Swal.fire({
    icon:'warning',
    title:'Delete media permanently?',
    text:'This will remove the file from disk.',
    showCancelButton:true,
    confirmButtonText:'Delete',
    confirmButtonColor:'#ef4444'
  });
  if (!c.isConfirmed) return;

  const resp = await apiFetch('/api/media/' + encodeURIComponent(id) + '?hard=true', { method:'DELETE' });
  if (!resp.ok){ handleApiFailure('Delete failed', resp); return; }
  showToast('success', 'Deleted permanently');
  await loadLibrary();
}


  function insertSelected(){
    if (!mpSel || !mpCtx.editor) return;
    // restore cursor into the right editor before insertion
    restoreCursorPosition();

    const url = mpSel.url;
    const html = `<img src="${url}" alt="${esc(mpSel.alt_text||'')}" style="max-width:100%; height:auto; border-radius:4px; margin:4px 0; display:block; cursor:move;">`;
    document.execCommand('insertHTML', false, html);

    // enable resizing on the newly inserted image
    setTimeout(() => {
      const imgs = mpCtx.editor.querySelectorAll('img');
      const img = imgs[imgs.length-1];
      if (img) makeImageResizable(img);
    }, 50);

    // copy to clipboard (as you asked)
    navigator.clipboard?.writeText(url).catch(()=>{});
    closePicker();
  }

  function iconFor(cat){
    const map = { video:'<i class="fa fa-film"></i>', audio:'<i class="fa fa-music"></i>', document:'<i class="fa fa-file-lines"></i>', archive:'<i class="fa fa-file-zipper"></i>', other:'<i class="fa fa-file"></i>' };
    return map[cat] || '<i class="fa fa-file"></i>';
  }

  // upload
  function showPreview(file){
    mpFileObj = file;
    mpPreview.style.display = 'flex';
    mpPreviewName.textContent = file.name || 'Selected file';
    mpPreviewInfo.textContent = `${(file.size/1024).toFixed(1)} KB • ${file.type||'file'}`;
    if (file.type.startsWith('image/')){
      const url = URL.createObjectURL(file);
      mpPreviewImg.src = url;
      mpPreviewImg.onload = () => URL.revokeObjectURL(url);
      mpPreviewImg.style.display = '';
    } else {
      mpPreviewImg.style.display = 'none';
    }
  }
  function clearPreview(){
    mpFile.value = '';
    mpFileObj = null;
    mpPreview.style.display = 'none';
    mpPreviewImg.src = '';
    mpPreviewName.textContent = '';
    mpPreviewInfo.textContent = '';
  }

  // Drag & drop UI
  ['dragenter','dragover'].forEach(evt=>{
    mpDrop.addEventListener(evt, e=>{ e.preventDefault(); mpDrop.classList.add('drag'); });
  });
  ['dragleave','drop'].forEach(evt=>{
    mpDrop.addEventListener(evt, e=>{ e.preventDefault(); mpDrop.classList.remove('drag'); });
  });
  mpDrop.addEventListener('click', e => { if (e.target.id !== 'mpBrowse') mpFile.click(); });
  document.getElementById('mpBrowse').addEventListener('click', e=>{ e.stopPropagation(); mpFile.click(); });
  mpFile.addEventListener('change', () => { if (mpFile.files && mpFile.files[0]) showPreview(mpFile.files[0]); });
  mpDrop.addEventListener('drop', e => { const f = e.dataTransfer.files?.[0]; if (f) { mpFile.files = e.dataTransfer.files; showPreview(f); } });
  mpClear.addEventListener('click', clearPreview);

  mpUploadBtn.addEventListener('click', async ()=>{
    const f = mpFileObj;
    if (!f){ showToast('error', 'Choose a file'); return; }

    // Prepare form-data
    const fd = new FormData();
    fd.append('file', f);
    if (mpTitle.value.trim()) fd.append('title', mpTitle.value.trim());
    if (mpCtx.usageTag || mpTag.value.trim()) fd.append('usage_tag', (mpTag.value.trim() || mpCtx.usageTag));
    // status → active
    fd.append('status', 'active');

    // Upload
    mpUploadBtn.disabled = true; mpUploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Uploading…';
    const resp = await apiFetch('/api/media', { method:'POST', body: fd });
    mpUploadBtn.disabled = false; mpUploadBtn.innerHTML = '<i class="fa fa-cloud-arrow-up me-1"></i>Upload';

    if (!resp.ok){ handleApiFailure('Upload failed', resp); return; }

    const created = resp.isJson ? resp.data?.data || resp.data : null;
    if (!created){ showToast('error','Server returned no row'); return; }

    showToast('success','Uploaded');
    clearPreview();
    // refresh library, preselect the uploaded item
    await loadLibrary();
    mpSel = mpList.find(x => String(x.id) === String(created.id)) || created;
    setSelectedInfo();
    // Switch to Library so user can confirm/preview
    switchTab('lib');
  });

  // Events
  mpTabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.tab)));
  mpCloseBtn.addEventListener('click', closePicker);
  mpCancel.addEventListener('click', closePicker);
  mpInsert.addEventListener('click', insertSelected);
  mpRefresh.addEventListener('click', loadLibrary);
  mpUsageTag.addEventListener('change', loadLibrary);
  mpSearch.addEventListener('input', debounce(loadLibrary, 250));

  // tiny debounce
  function debounce(fn, ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }
})();


        function handleApiFailure(prefix, resp){
            if (resp.looksHtml){
                showToast('error', prefix + ': Session expired or invalid response');
                if (resp.status === 401 || resp.status === 419) {
                    setTimeout(function(){ location.href = '/'; }, 2000);
                }
            } else if (resp.isJson){
                const j = resp.data || {};
                showToast('error', j.message || j.error || (prefix + ': HTTP ' + resp.status));
            } else {
                showToast('error', prefix + ': HTTP ' + resp.status);
            }
        }

        function updateQuizHeader(){
    if (!quizMeta) return;

    var titleEl = document.getElementById('quizTitle');
    var descEl  = document.getElementById('quizDesc');
    var timeEl  = document.getElementById('quizTotalTime');

    if (titleEl) {
        titleEl.textContent = quizMeta.quiz_name || 'Untitled quiz';
    }

    if (descEl) {
        var raw  = quizMeta.quiz_description || '';
        var tmp  = document.createElement('div');
        tmp.innerHTML = raw;
        var text = (tmp.textContent || tmp.innerText || '').trim();

        if (text.length > 180) {
            text = text.slice(0, 180).trim() + '…';
        }
        descEl.textContent = text || 'No description added for this quiz yet.';
    }

    if (timeEl) {
        var minutes = parseInt(quizMeta.total_time, 10);
        if (!isNaN(minutes) && minutes > 0) {
            timeEl.textContent = minutes + ' min';
        } else {
            timeEl.textContent = '—';
        }
    }
}


        var usp = new URLSearchParams(location.search);
        var quizK = usp.get('quiz');
        var quizId = null;
        var questions = [];
        var editingId = null;

        // Store the current cursor position
        var currentEditor = null;
        var currentSelection = null;

        // Image resizing variables
        var resizingImage = null;
        var startX, startY, startWidth, startHeight;

        // Function to save cursor position
        function saveCursorPosition(editorArea) {
            currentEditor = editorArea;
            currentSelection = window.getSelection().getRangeAt(0);
        }

        // Function to restore cursor position
        function restoreCursorPosition() {
            if (currentEditor && currentSelection) {
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(currentSelection);
                currentEditor.focus();
            }
        }

        // Function to switch between visual and code editor
        function setupEditorTabs(editorWrapper) {
            const tabs = editorWrapper.querySelectorAll('.editor-tab');
            const contents = editorWrapper.querySelectorAll('.editor-content');
            const visualArea = editorWrapper.querySelector('.rte-area');
            const codeArea = editorWrapper.querySelector('.code-editor');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabName = this.dataset.tab;
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update active content
                    contents.forEach(content => {
                        content.classList.remove('active');
                        if (content.dataset.tab === tabName) {
                            content.classList.add('active');
                        }
                    });
                    
                    // Sync content between visual and code editors
                    if (tabName === 'code') {
                        codeArea.value = visualArea.innerHTML;
                        codeArea.focus();
                    } else {
                        visualArea.innerHTML = codeArea.value;
                        visualArea.focus();
                    }
                });
            });
            
            // Sync code editor when visual editor changes
            visualArea.addEventListener('input', function() {
                if (editorWrapper.querySelector('.editor-tab[data-tab="code"]').classList.contains('active')) {
                    codeArea.value = this.innerHTML;
                }
            });
        }

        // Function to show image link dialog (without size controls)
        function showImageDialog(editorArea) {
            // Save current cursor position before showing dialog
            saveCursorPosition(editorArea);
            
            Swal.fire({
                title: 'Insert Image',
                html: `
                    <div class="form-group">
                        <label class="form-label">Image URL</label>
                        <input type="text" id="imageUrl" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <div class="form-label">Preview</div>
                        <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 6px;">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Insert',
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    const imageUrlInput = document.getElementById('imageUrl');
                    const imagePreview = document.getElementById('imagePreview');
                    const previewImg = document.getElementById('previewImg');
                    
                    imageUrlInput.addEventListener('input', function() {
                        const url = this.value.trim();
                        if (url && /\.(jpeg|jpg|gif|png|webp|svg)$/i.test(url)) {
                            previewImg.src = url;
                            imagePreview.style.display = 'block';
                        } else {
                            imagePreview.style.display = 'none';
                        }
                    });
                    
                    // Focus on the input field
                    imageUrlInput.focus();
                },
                preConfirm: () => {
                    const imageUrl = document.getElementById('imageUrl').value.trim();
                    if (!imageUrl) {
                        Swal.showValidationMessage('Please enter an image URL');
                        return false;
                    }
                    return imageUrl;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = result.value;
                    
                    // Restore cursor position before inserting
                    restoreCursorPosition();
                    
                    // Create a temporary image element to test the URL
                    const testImg = new Image();
                    testImg.onload = function() {
                        // Image loaded successfully, insert it at cursor position
                        const imgHtml = `<img src="${url}" alt="Inserted image" style="max-width:100%; height:auto; border-radius:4px; margin:4px 0; display:block; cursor:move;">`;
                        document.execCommand('insertHTML', false, imgHtml);
                        currentEditor.focus();
                        
                        // Add resize handles to the newly inserted image
                        setTimeout(() => {
                            const images = currentEditor.querySelectorAll('img');
                            const newImage = images[images.length - 1];
                            if (newImage) {
                                makeImageResizable(newImage);
                            }
                        }, 100);
                    };
                    testImg.onerror = function() {
                        // Image failed to load, still insert but show error
                        const imgHtml = `<img src="${url}" alt="Inserted image (failed to load)" style="max-width:100%; height:auto; border-radius:4px; margin:4px 0; display:block; border:1px solid red; cursor:move;">`;
                        document.execCommand('insertHTML', false, imgHtml);
                        currentEditor.focus();
                        showToast('error', 'Image inserted but may not load correctly');
                        
                        // Add resize handles even to failed images
                        setTimeout(() => {
                            const images = currentEditor.querySelectorAll('img');
                            const newImage = images[images.length - 1];
                            if (newImage) {
                                makeImageResizable(newImage);
                            }
                        }, 100);
                    };
                    testImg.src = url;
                }
            });
        }

        // Function to make images resizable with mouse
        function makeImageResizable(img) {
            // Remove any existing resize handles
            removeResizeHandles();
            
            // Add click event to show resize handles
            img.addEventListener('click', function(e) {
                e.stopPropagation();
                showResizeHandles(this);
            });
            
            // Add draggable functionality
            img.setAttribute('draggable', 'true');
            img.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', 'image');
            });
        }

        // Function to show resize handles around an image
        function showResizeHandles(img) {
            // Remove any existing resize handles first
            removeResizeHandles();
            
            // Add resizable class for visual feedback
            img.classList.add('resizable');
            
            // Create resize handles
            const handles = ['nw', 'ne', 'sw', 'se'];
            handles.forEach(handle => {
                const handleEl = document.createElement('div');
                handleEl.className = `resize-handle ${handle}`;
                document.body.appendChild(handleEl);
                
                // Position the handle
                const rect = img.getBoundingClientRect();
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                switch(handle) {
                    case 'nw':
                        handleEl.style.left = (rect.left + scrollLeft) + 'px';
                        handleEl.style.top = (rect.top + scrollTop) + 'px';
                        break;
                    case 'ne':
                        handleEl.style.left = (rect.right + scrollLeft) + 'px';
                        handleEl.style.top = (rect.top + scrollTop) + 'px';
                        break;
                    case 'sw':
                        handleEl.style.left = (rect.left + scrollLeft) + 'px';
                        handleEl.style.top = (rect.bottom + scrollTop) + 'px';
                        break;
                    case 'se':
                        handleEl.style.left = (rect.right + scrollLeft) + 'px';
                        handleEl.style.top = (rect.bottom + scrollTop) + 'px';
                        break;
                }
                
                // Add mouse events for resizing
                handleEl.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    startResize(img, handle, e);
                });
            });
            
            // Hide handles when clicking elsewhere
            const hideHandles = function(e) {
                if (!img.contains(e.target) && !e.target.classList.contains('resize-handle')) {
                    removeResizeHandles();
                    img.classList.remove('resizable');
                    document.removeEventListener('click', hideHandles);
                }
            };
            
            setTimeout(() => {
                document.addEventListener('click', hideHandles);
            }, 0);
        }

        // Function to start resizing
        function startResize(img, handle, e) {
            resizingImage = img;
            startX = e.clientX;
            startY = e.clientY;
            startWidth = parseInt(document.defaultView.getComputedStyle(img).width, 10);
            startHeight = parseInt(document.defaultView.getComputedStyle(img).height, 10);
            
            document.addEventListener('mousemove', resize);
            document.addEventListener('mouseup', stopResize);
        }

        // Function to handle resizing
        function resize(e) {
            if (!resizingImage) return;
            
            const width = startWidth + (e.clientX - startX);
            const height = startHeight + (e.clientY - startY);
            
            // Apply new dimensions while maintaining aspect ratio for corner handles
            if (width > 50) { // Minimum width
                resizingImage.style.width = width + 'px';
            }
            if (height > 50) { // Minimum height
                resizingImage.style.height = height + 'px';
            }
            
            // Update resize handles position
            updateResizeHandles(resizingImage);
        }

        // Function to stop resizing
        function stopResize() {
            document.removeEventListener('mousemove', resize);
            document.removeEventListener('mouseup', stopResize);
            resizingImage = null;
        }

        // Function to update resize handles position
        function updateResizeHandles(img) {
            const handles = document.querySelectorAll('.resize-handle');
            const rect = img.getBoundingClientRect();
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            handles.forEach(handle => {
                const handleType = handle.className.split(' ')[1];
                switch(handleType) {
                    case 'nw':
                        handle.style.left = (rect.left + scrollLeft) + 'px';
                        handle.style.top = (rect.top + scrollTop) + 'px';
                        break;
                    case 'ne':
                        handle.style.left = (rect.right + scrollLeft) + 'px';
                        handle.style.top = (rect.top + scrollTop) + 'px';
                        break;
                    case 'sw':
                        handle.style.left = (rect.left + scrollLeft) + 'px';
                        handle.style.top = (rect.bottom + scrollTop) + 'px';
                        break;
                    case 'se':
                        handle.style.left = (rect.right + scrollLeft) + 'px';
                        handle.style.top = (rect.bottom + scrollTop) + 'px';
                        break;
                }
            });
        }

        // Function to remove resize handles
        function removeResizeHandles() {
            const handles = document.querySelectorAll('.resize-handle');
            handles.forEach(handle => handle.remove());
        }

        // Initialize image resizing for existing images when editors are focused
        function initImageResizing() {
            document.querySelectorAll('.rte-area').forEach(area => {
                area.addEventListener('click', function() {
                    // Add resizable functionality to all images in this editor
                    const images = this.querySelectorAll('img');
                    images.forEach(img => {
                        makeImageResizable(img);
                    });
                });
            });
        }

        function makeEditor(root){
            if (!root) return;
            
            // Setup editor tabs
            setupEditorTabs(root);
            
            var toolbar = root.querySelector('.rte-toolbar');
            var area = root.querySelector('.rte-area');
            if (!toolbar || !area) return;

            // Save cursor position when user interacts with the editor
            area.addEventListener('click', function() {
                saveCursorPosition(this);
            });
            area.addEventListener('keyup', function() {
                saveCursorPosition(this);
            });

            toolbar.addEventListener('click', function(e){
                var b = e.target.closest('button'); 
                if(!b) return;
                e.preventDefault();
                
                if (b.getAttribute('data-cmd')){
                    // Save cursor position before command
                    saveCursorPosition(area);
                    document.execCommand(b.getAttribute('data-cmd'), false, null);
                    area.focus();
                    return;
                }
                if (b.hasAttribute('data-link')){
                    // Save cursor position before link dialog
                    saveCursorPosition(area);
                    var url = prompt('Enter URL:');
                    if(url) document.execCommand('createLink', false, url);
                    area.focus();
                    return;
                }
          if (b.hasAttribute('data-image')){
                openMediaPicker(area, { usageTag: 'question', category: 'image' });
                return;
            }
            });
        }

        // Initialize all main editors
        makeEditor(document.getElementById('edTitle'));
        makeEditor(document.getElementById('edDesc'));
        makeEditor(document.getElementById('edExplain'));

        // Initialize image resizing
        initImageResizing();

        // Function to add dash placeholder to question title
        function addDashToTitle() {
            const titleArea = document.querySelector('#edTitle .rte-area');
            if (!titleArea) return;
            
            // Save cursor position
            saveCursorPosition(titleArea);
            
            // Insert the dash placeholder at cursor position
            const dashHtml = '<span class="dash-placeholder" contenteditable="false">{dash}</span>';
            document.execCommand('insertHTML', false, dashHtml);
            
            // Restore focus
            titleArea.focus();
            
            // Update fill blank inputs based on dash count
            updateFillBlankInputs();
        }

        // Function to count dashes in question title
        function countDashesInTitle() {
            const titleArea = document.querySelector('#edTitle .rte-area');
            if (!titleArea) return 0;
            
            const html = titleArea.innerHTML;
            const dashMatches = html.match(/{dash}/g);
            return dashMatches ? dashMatches.length : 0;
        }

        // Function to update fill blank inputs based on dash count
        function updateFillBlankInputs() {
            const fillBlankWrap = document.getElementById('fillBlankWrap');
            const dashCount = countDashesInTitle();
            
            if (!fillBlankWrap) return;
            
            // Clear existing inputs
            fillBlankWrap.innerHTML = '';
            
            // Create inputs based on dash count
            for (let i = 1; i <= dashCount; i++) {
                const inputDiv = document.createElement('div');
                inputDiv.className = 'fill-blank-input';
                inputDiv.innerHTML = `
                    <label>Answer for Blank ${i}</label>
                    <input type="text" class="form-control fill-blank-answer" data-index="${i}" placeholder="Enter correct answer for blank ${i}">
                `;
                fillBlankWrap.appendChild(inputDiv);
            }
            
            // Show note if there are dashes
            if (dashCount > 0) {
                const noteDiv = document.createElement('div');
                noteDiv.className = 'fill-blank-note';
                noteDiv.textContent = `Found ${dashCount} blank(s) in the question. Please provide the correct answers for each blank.`;
                fillBlankWrap.appendChild(noteDiv);
            }
        }

        // Function to get fill blank answers
        function getFillBlankAnswers() {
            const inputs = document.querySelectorAll('.fill-blank-answer');
            const answers = [];
            
            inputs.forEach(input => {
                const index = parseInt(input.dataset.index);
                const value = input.value.trim();
                if (value) {
                    answers.push({
                        answer_title: value,
                        is_correct: 1, // All fill blank answers are correct by default
                        answer_order: index,
                        belongs_question_type: 'fill_in_the_blank'
                    });
                }
            });
            
            return answers;
        }

        // Function to process fill blank answers for editing
        function processFillBlankAnswersForEdit(questionTitle, answers) {
            // Replace {dash} placeholders with styled spans for editing
            let processedTitle = questionTitle;
            let dashCount = 0;
            
            // Count dashes and replace with styled placeholders
            processedTitle = processedTitle.replace(/{dash}/g, function() {
                dashCount++;
                return '<span class="dash-placeholder" contenteditable="false">{dash}</span>';
            });
            
            return {
                processedTitle: processedTitle,
                dashCount: dashCount
            };
        }

        function makeAnswerBlock(data, single){
            data = data || {};
            var wrap = document.createElement('div');
            wrap.className = 'answer-option';
            if (data.is_correct) wrap.classList.add('correct');

            wrap.innerHTML = `
                <div class="answer-header">
                    <input class="answer-check" type="${single?'radio':'checkbox'}" 
                        name="ansCorrect${single?'-single':''}" ${data.is_correct?'checked':''}>
                    <div class="answer-content">
                        <div class="answer-label">${data.answer_title ? esc(stripHtml(data.answer_title)) : 'Option'}</div>
                        <div class="rte-wrapper">
                            <div class="editor-tabs">
                                <div class="editor-tab active" data-tab="visual">Visual</div>
                                <div class="editor-tab" data-tab="code">Code</div>
                            </div>
                            <div class="editor-content active" data-tab="visual">
                                <div class="rte-toolbar">
                                    <button data-cmd="bold" type="button"><i class="fa fa-bold"></i></button>
                                    <button data-cmd="italic" type="button"><i class="fa fa-italic"></i></button>
                                    <button data-cmd="underline" type="button"><i class="fa fa-underline"></i></button>
                                    <button data-link type="button" title="Link"><i class="fa fa-link"></i></button>
                                    <button data-image type="button" title="Image"><i class="fa fa-image"></i></button>
                                </div>
                                <div class="rte-area" contenteditable="true" spellcheck="true"></div>
                            </div>
                            <div class="editor-content" data-tab="code">
                                <textarea class="code-editor"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="answer-actions">
                    <button type="button" class="answer-delete">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>`;

            var rte = wrap.querySelector('.rte-wrapper');
            makeEditor(rte);
            var area = wrap.querySelector('.rte-area');
            if (data.answer_title) area.innerHTML = data.answer_title;

            wrap.querySelector('.answer-check').addEventListener('change', function(e){
                if (single){
                    document.querySelectorAll('#ansWrap .answer-option').forEach(function(b){ 
                        b.classList.remove('correct');
                        b.querySelector('.answer-check').checked = false;
                    });
                    wrap.querySelector('.answer-check').checked = true;
                }
                wrap.classList.toggle('correct', e.target.checked);
            });

            wrap.querySelector('.answer-delete').addEventListener('click', function(){
                if (document.querySelectorAll('#ansWrap .answer-option').length > 1) {
                    wrap.remove();
                } else {
                    showToast('error', 'At least one answer is required');
                }
            });

            return wrap;
        }

        function resetAnswersForType(){
            var t = document.getElementById('qType');
            if (!t) return;
            
            var type = t.value;
            var isSingle = (type==='single_choice' || type==='true_false');
            var isFillBlank = (type==='fill_in_the_blank');
            var wrap = document.getElementById('ansWrap');
            var fillBlankWrap = document.getElementById('fillBlankWrap');
            var btnAddAns = document.getElementById('btnAddAns');
            var btnAddDash = document.getElementById('btnAddDash');
            
            if (!wrap || !fillBlankWrap || !btnAddAns || !btnAddDash) return;
            
            // Show/hide appropriate elements based on question type
            if (isFillBlank) {
                wrap.style.display = 'none';
                fillBlankWrap.style.display = 'block';
                btnAddAns.style.display = 'none';
                btnAddDash.style.display = 'inline-flex';
                
                // Update fill blank inputs based on current dashes in title
                updateFillBlankInputs();
            } else {
                wrap.style.display = 'block';
                fillBlankWrap.style.display = 'none';
                btnAddAns.style.display = '';
                btnAddDash.style.display = 'none';
                
                wrap.innerHTML = '';
                
                if (type==='true_false'){
                    wrap.appendChild(makeAnswerBlock({answer_title:'True', is_correct:true}, true));
                    wrap.appendChild(makeAnswerBlock({answer_title:'False', is_correct:false}, true));
                } else {
                    wrap.appendChild(makeAnswerBlock({}, isSingle));
                    wrap.appendChild(makeAnswerBlock({}, isSingle));
                }
            }
        }

        function rowItem(q){
            var li = document.createElement('div');
            li.className = 'question-item';
            li.dataset.id = q.question_id;

            var titlePlain = stripHtml(q.question_title);
            var truncatedTitle = truncateText(titlePlain, 4);
    const diff = (q.question_difficulty || 'medium').toLowerCase();
const badgeLabel = diff.charAt(0).toUpperCase() + diff.slice(1);
const typeMeta = typeMetaForList(q);

li.innerHTML = `
    <div class="q-number">${q.question_order || '-'}</div>

    <div class="question-item-main">
        <div class="question-item-title">
            ${esc(truncateText(stripHtml(q.question_title || ''), 10)) || 'Untitled'}
        </div>
        <div class="question-item-meta">
            <span class="q-badge ${diff}">${badgeLabel}</span>
            <span class="q-type ${typeMeta.key}" title="${typeMeta.label}">
                <i class="${typeMeta.icon}"></i>
                <span>${typeMeta.short}</span>
            </span>
        </div>
    </div>

    <div class="question-menu">
        <button class="menu-btn">
            <i class="fa fa-ellipsis-v"></i>
        </button>
        <div class="menu-dropdown">
            <div class="menu-item view" data-id="${q.question_id}">
                <i class="fa fa-eye"></i> View
            </div>
            <div class="menu-item edit" data-id="${q.question_id}">
                <i class="fa fa-edit"></i> Edit
            </div>
            <div class="menu-item delete" data-id="${q.question_id}">
                <i class="fa fa-trash"></i> Delete
            </div>
        </div>
    </div>`;


            
            return li;
        }

        function renderList(arr){
            var box = document.getElementById('qList');
            if (!box) return;
            
            if (!arr.length){
                box.innerHTML = `<div class="empty-state">
                    <i class="fa fa-inbox"></i>
                    <div>No questions yet</div>
                </div>`;
                var questionsCount = document.getElementById('questionsCount');
                if (questionsCount) questionsCount.textContent = '0';
                return;
            }
            
            var frag = document.createDocumentFragment();
            arr.forEach(function(q){ frag.appendChild(rowItem(q)); });
            box.innerHTML = '';
            box.appendChild(frag);
            var questionsCount = document.getElementById('questionsCount');
            if (questionsCount) questionsCount.textContent = arr.length;
            var headerCount = document.getElementById('quizQuestionCountTop');
            if (headerCount) headerCount.textContent = arr.length;

            
            // Add event listeners for menu buttons
            document.querySelectorAll('.menu-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.nextElementSibling;
                    const isVisible = dropdown.classList.contains('show');
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.menu-dropdown').forEach(function(d) {
                        d.classList.remove('show');
                    });
                    
                    // Toggle this dropdown
                    if (!isVisible) {
                        dropdown.classList.add('show');
                        
                        // Check if dropdown would go outside the container
                        const btnRect = this.getBoundingClientRect();
                        const dropdownRect = dropdown.getBoundingClientRect();
                        const containerRect = box.getBoundingClientRect();
                        
                        // If dropdown would extend beyond the bottom of the container
                        if (btnRect.bottom + dropdownRect.height > containerRect.bottom) {
                            // Position the dropdown above the button
                            dropdown.style.top = 'auto';
                            dropdown.style.bottom = '100%';
                        } else {
                            // Position the dropdown below the button (default)
                            dropdown.style.top = '100%';
                            dropdown.style.bottom = 'auto';
                        }
                    }
                });
            });
            
            // Add event listeners for menu items
            document.querySelectorAll('.menu-item.edit').forEach(function(item) {
                item.addEventListener('click', async function(e) {
                    e.stopPropagation();
                    const id = this.dataset.id;
                    document.querySelectorAll('.menu-dropdown').forEach(function(d) {
                        d.classList.remove('show');
                    });
                    await openQuestion(id);
                });
            });
            
            document.querySelectorAll('.menu-item.delete').forEach(function(item) {
                item.addEventListener('click', async function(e) {
                    e.stopPropagation();
                    const id = this.dataset.id;
                    document.querySelectorAll('.menu-dropdown').forEach(function(d) {
                        d.classList.remove('show');
                    });
                    
                    const c = await Swal.fire({
                        icon:'warning',
                        title:'Delete question?',
                        text:'This action cannot be undone',
                        showCancelButton:true,
                        confirmButtonText:'Delete',
                        cancelButtonText:'Cancel',
                        confirmButtonColor:'#ef4444'
                    });
                    
                    if(!c.isConfirmed) return;

                    const resp = await apiFetch('/api/quizz/questions/' + encodeURIComponent(id), { method:'DELETE' });
                    
                    if(!resp.ok){ 
                        handleApiFailure('Delete failed', resp); 
                        return; 
                    }
                    
                    showToast('success', 'Question deleted');
                    loadList(); 
                    resetForm();
                });
            });
            
            // Close dropdowns when clicking elsewhere
            document.addEventListener('click', function() {
                document.querySelectorAll('.menu-dropdown').forEach(function(d) {
                    d.classList.remove('show');
                });
            });

            document.querySelectorAll('.menu-item.view').forEach(function(item) {
    item.addEventListener('click', async function(e) {
        e.stopPropagation();
        const id = this.dataset.id;
        document.querySelectorAll('.menu-dropdown').forEach(function(d) {
            d.classList.remove('show');
        });
        await viewQuestion(id);
    });
});

typesetMath(box);

        }

        async function loadList(){
            if (!quizId) return;
            var qList = document.getElementById('qList');
            if (qList) {
                qList.innerHTML = '<div class="empty-state"><i class="fa fa-spinner fa-spin"></i><div>Loading...</div></div>';
            }

            const resp = await apiFetch('/api/quizz/questions?quiz=' + encodeURIComponent(quizK), { method:'GET' });
            if (!resp.ok){
                handleApiFailure('Load failed', resp);
                return;
            }
            
            var payload = resp.isJson ? resp.data : {};
            questions = Array.isArray(payload.data) ? payload.data : [];
            renderList(questions);
        }

        function resetForm(){
            editingId = null;
            var qForm = document.getElementById('qForm');
            if (qForm) qForm.classList.remove('was-validated');
            
            var qId = document.getElementById('qId');
            if (qId) qId.value = '';
            
            var qType = document.getElementById('qType');
            if (qType) qType.value = 'multiple_choice';

            if (qType) qType.disabled = false;
var qDifficulty = document.getElementById('qDifficulty');
if (qDifficulty) qDifficulty.value = 'medium';

            
            var qMarks = document.getElementById('qMarks');
            if (qMarks) qMarks.value = '1';
            
            var qOrder = document.getElementById('qOrder');
            if (qOrder) {
                qOrder.value = (questions.length > 0 ? Math.max(...questions.map(q => q.question_order || 0)) + 1 : 1);
            }
            
            var edTitleArea = document.querySelector('#edTitle .rte-area');
            if (edTitleArea) edTitleArea.innerHTML = '';
            
            var edDescArea = document.querySelector('#edDesc .rte-area');
            if (edDescArea) edDescArea.innerHTML = '';
            
            var edExplainArea = document.querySelector('#edExplain .rte-area');
            if (edExplainArea) edExplainArea.innerHTML = '';
            
            resetAnswersForType();
            
            document.querySelectorAll('.question-item').forEach(function(i){ i.classList.remove('active'); });
            
            var btnDelete = document.getElementById('btnDelete');
            if (btnDelete) btnDelete.style.display = 'none';
        }

        var qType = document.getElementById('qType');
        if (qType) {
            qType.addEventListener('change', resetAnswersForType);
        }
        
        var btnAddAns = document.getElementById('btnAddAns');
        if (btnAddAns) {
            btnAddAns.addEventListener('click', function(){
                var t = document.getElementById('qType');
                if (!t) return;
                var type = t.value;
                var isSingle = (type==='single_choice' || type==='true_false');
                var ansWrap = document.getElementById('ansWrap');
                if (ansWrap) ansWrap.appendChild(makeAnswerBlock({}, isSingle));
            });
        }

        var btnAddDash = document.getElementById('btnAddDash');
        if (btnAddDash) {
            btnAddDash.addEventListener('click', addDashToTitle);
        }

        // Listen for changes in title editor to update fill blank inputs
        const titleArea = document.querySelector('#edTitle .rte-area');
        if (titleArea) {
            titleArea.addEventListener('input', function() {
                const qType = document.getElementById('qType');
                if (qType && qType.value === 'fill_in_the_blank') {
                    updateFillBlankInputs();
                }
            });
        }

        var btnNew = document.getElementById('btnNew');
        if (btnNew) {
            btnNew.addEventListener('click', resetForm);
        }

        var btnCancel = document.getElementById('btnCancel');
        if (btnCancel) {
            btnCancel.addEventListener('click', resetForm);
        }

        var qList = document.getElementById('qList');
        if (qList) {
            qList.addEventListener('click', async function(e){
                var li = e.target.closest('.question-item');
                if(!li || e.target.closest('.menu-btn') || e.target.closest('.menu-item')) return;
                
                var id = li.dataset.id;
                await openQuestion(id, li);
            });
        }

        function guessUiType(q){
          if (!q) return 'multiple_choice';
          if (q.question_type !== 'mcq') return q.question_type; // 'true_false' | 'fill_in_the_blank'
        
          const ans = Array.isArray(q.answers) ? q.answers : [];
          const anySingleFlag = ans.some(a => a && a.belongs_question_type === 'single_choice');
          const correctCount  = ans.reduce((c,a)=> c + ((a && (a.is_correct===true || a.is_correct===1 || a.is_correct==='1')) ? 1 : 0), 0);
        
          // if we ever stored single flag on answers or there is exactly one correct, treat it as single_choice
          return (anySingleFlag || correctCount === 1) ? 'single_choice' : 'multiple_choice';
        }

function renderQuestionPreview(q){
    if (!previewTitle) return;
    const uiType = guessUiType(q);
    const diff   = (q.question_difficulty || 'medium').toLowerCase();
    const typeMeta = typeMetaForList(q);

    // chips
    previewChips.innerHTML = `
        <span class="preview-chip diff-${diff}">${diff.charAt(0).toUpperCase()+diff.slice(1)}</span>
        <span class="preview-chip">${typeMeta.label}</span>
        <span class="preview-chip">${q.question_mark || 1} mark${(q.question_mark||1) > 1 ? 's':''}</span>
    `;

    // ---- TITLE: strip HTML so TeX is contiguous ----
    const rawTitle   = q.question_title || 'Untitled question';
    const plainTitle = stripHtml(rawTitle); // remove <p>, <br>, etc.
    let   titleHtml  = plainTitle;

    if (uiType === 'fill_in_the_blank') {
        // turn {dash} tokens into blanks
        titleHtml = plainTitle.replace(/{dash}/g, '<span class="preview-blank"></span>');
    }
    previewTitle.innerHTML = titleHtml;

    // ---- DESCRIPTION (also flattened so TeX works) ----
    const descPlain = stripHtml(q.question_description || '');
    if (descPlain.trim().length){
        previewDesc.style.display = 'block';
        previewDesc.innerHTML = esc(descPlain);
    } else {
        previewDesc.style.display = 'none';
        previewDesc.innerHTML = '';
    }

    // ---- OPTIONS ----
    previewOptions.innerHTML = '';
    const answers = Array.isArray(q.answers) ? q.answers.slice() : [];
    answers.sort((a,b) => (a.answer_order||0) - (b.answer_order||0));

    if (uiType === 'fill_in_the_blank') {
        if (answers.length){
            const heading = document.createElement('div');
            heading.className = 'preview-extra-title';
            heading.textContent = 'Correct answers';
            previewOptions.appendChild(heading);

            answers.forEach((ans, idx) => {
                const text = stripHtml(ans.answer_title || '');
                const wrap = document.createElement('div');
                wrap.className = 'preview-option correct';
                wrap.innerHTML = `
                    <div class="preview-option-label">${idx+1}</div>
                    <div class="preview-option-content">${esc(text)}</div>
                `;
                previewOptions.appendChild(wrap);
            });
        }
    } else {
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        answers.forEach((ans, idx) => {
            const text    = stripHtml(ans.answer_title || '');
            const correct = !!ans.is_correct;
            const wrap    = document.createElement('div');
            const letter  = letters[idx] || '?';

            wrap.className = 'preview-option' + (correct ? ' correct' : '');
            wrap.innerHTML = `
                <div class="preview-option-label">${letter}</div>
                <div class="preview-option-content">${esc(text)}</div>
            `;
            previewOptions.appendChild(wrap);
        });
    }

    // ---- EXPLANATION ----
    const explPlain = stripHtml(q.answer_explanation || '');
    if (explPlain.trim().length){
        previewExtra.innerHTML = `
            <div class="preview-extra-title">Explanation</div>
            <div>${esc(explPlain)}</div>
        `;
    } else {
        previewExtra.innerHTML = '';
    }

    // finally typeset the whole modal (header + body)
    const previewRoot = document.getElementById('previewOverlay');
    typesetMath(previewRoot);
}


async function viewQuestion(id){
    const loader = document.getElementById('contentLoader');
    if (loader) loader.style.display = 'flex';

    const resp = await apiFetch('/api/quizz/questions/' + encodeURIComponent(id), { method:'GET' });

    if (loader) loader.style.display = 'none';

    if (!resp.ok){
        handleApiFailure('Load failed', resp);
        return;
    }
    const q = resp.isJson ? (resp.data.data || resp.data) : null;
    if (!q){
        showToast('error', 'Failed to load question');
        return;
    }
    renderQuestionPreview(q);
    openPreviewModal();
}


function typeMetaForList(q){
    const uiType = guessUiType(q); // 'single_choice' | 'multiple_choice' | 'true_false' | 'fill_in_the_blank'

    switch (uiType) {
        case 'single_choice':
            return {
                key: 'single_choice',
                label: 'Single choice',
                short: 'SC',
                icon: 'fa-regular fa-circle-dot'
            };
        case 'multiple_choice':
            return {
                key: 'multiple_choice',
                label: 'Multiple choice',
                short: 'MCQ',
                icon: 'fa-solid fa-square-check'
            };
        case 'true_false':
            return {
                key: 'true_false',
                label: 'True / False',
                short: 'T/F',
                icon: 'fa-solid fa-check'
            };
       case 'fill_in_the_blank':
    return {
        key: 'fill_in_the_blank',
        label: 'Fill in the blank',
        short: 'Fill',
        // no more horizontal dots; looks like blanks/lines
        icon: 'fa-solid fa-grip-lines'
    };

        default:
            return {
                key: 'multiple_choice',
                label: 'MCQ',
                short: 'MCQ',
                icon: 'fa-regular fa-circle-question'
            };
    }
}




        async function openQuestion(id, liEl){
            // Show loader
            const loader = document.getElementById('contentLoader');
            if (loader) loader.style.display = 'flex';
            
            const resp = await apiFetch('/api/quizz/questions/' + encodeURIComponent(id), { method:'GET' });
            
            // Hide loader
            if (loader) loader.style.display = 'none';
            
            if(!resp.ok){ 
                handleApiFailure('Load failed', resp); 
                return; 
            }
            
            var q = resp.isJson ? (resp.data.data || resp.data) : null;
            if(!q){ 
                showToast('error', 'Failed to load question'); 
                return; 
            }

            editingId = q.question_id;
            var qId = document.getElementById('qId');
            if (qId) qId.value = editingId;
            
            var uiType = guessUiType(q);
            var qTypeEl = document.getElementById('qType');
            if (qTypeEl){
              qTypeEl.value = uiType;
              resetAnswersForType();            // <-- forces the correct panel (answers vs blanks, radios vs checkboxes, Add Dash button, etc.)
             
            }
             if (qTypeEl) qTypeEl.disabled = true;

            var qType = document.getElementById('qType');
            if (qType) qType.value = uiType;
            
            var qMarks = document.getElementById('qMarks');
            if (qMarks) qMarks.value = (q.question_mark != null ? q.question_mark : 1);

            var qDifficulty = document.getElementById('qDifficulty');
if (qDifficulty) qDifficulty.value = (q.question_difficulty || 'medium');

            
            var qOrder = document.getElementById('qOrder');
            if (qOrder) qOrder.value = (q.question_order != null ? q.question_order : 1);

            // Handle question title based on type
            var edTitleArea = document.querySelector('#edTitle .rte-area');
            if (edTitleArea) {
                if (uiType === 'fill_in_the_blank') {
                    // Process fill blank question title to replace {dash} with styled placeholders
                    const processed = processFillBlankAnswersForEdit(q.question_title || '', q.answers || []);
                    edTitleArea.innerHTML = processed.processedTitle;
                } else {
                    edTitleArea.innerHTML = q.question_title || '';
                }
            }
            
            var edDescArea = document.querySelector('#edDesc .rte-area');
            if (edDescArea) edDescArea.innerHTML = q.question_description || '';
            
            var edExplainArea = document.querySelector('#edExplain .rte-area');
            if (edExplainArea) edExplainArea.innerHTML = q.answer_explanation || '';

            // Handle different answer types
            if (uiType === 'fill_in_the_blank') {
                // Update fill blank inputs based on dashes in title
                updateFillBlankInputs();
                
                // Pre-fill answers if available
                if (Array.isArray(q.answers) && q.answers.length){
                   q.answers.forEach((answer, idx) => {
                   const input = document.querySelector(`.fill-blank-answer[data-index="${idx+1}"]`);
                  if (input) input.value = stripHtml(answer.answer_title || '');
                   });
                }
            } else {
                var wrap = document.getElementById('ansWrap');
                if (wrap) {
                    wrap.innerHTML = '';
                    var single = (uiType==='single_choice' || uiType==='true_false');
                    
                    (q.answers || []).forEach(function(a){ 
                        wrap.appendChild(makeAnswerBlock(a, single)); 
                    });
                }
            }
            
            document.querySelectorAll('.question-item').forEach(function(x){ x.classList.remove('active'); });
            if (liEl) liEl.classList.add('active');
            
            var btnDelete = document.getElementById('btnDelete');
            if (btnDelete) btnDelete.style.display = 'inline-flex';
            window.scrollTo({top:0, behavior:'smooth'});
        }

        var qSearch = document.getElementById('qSearch');
        if (qSearch) {
        qSearch.addEventListener('input', function(){
    var t = this.value.toLowerCase();
    document.querySelectorAll('#qList .question-item').forEach(function(li){
        var txtEl = li.querySelector('.question-item-title');
        var txt   = txtEl ? txtEl.textContent.toLowerCase() : '';
        li.style.display = (txt.indexOf(t) !== -1) ? '' : 'none';
    });
});

        }

        var btnSave = document.getElementById('btnSave');
        if (btnSave) {
            btnSave.addEventListener('click', submitForm);
        }
        
        async function submitForm(){
            // Get content from visual editors (code editors are synced automatically)
            var edTitleArea = document.querySelector('#edTitle .rte-area');
            if (!edTitleArea) return;
            
            var titleHTML = edTitleArea.innerHTML.trim();
            
            if (!stripHtml(titleHTML).trim()){
                showToast('error', 'Please enter question text');
                return;
            }

            var qType = document.getElementById('qType');
            if (!qType) return;
            
            var type = qType.value;
            var isFillBlank = (type === 'fill_in_the_blank');

            var answers = [];
            var hasCorrect = false;
            
            if (isFillBlank) {
                // Handle fill in the blank answers
                const dashCount = countDashesInTitle();
                const fillBlankAnswers = getFillBlankAnswers();
                
                if (dashCount === 0) {
                    showToast('error', 'For Fill in the Blank questions, add at least one {dash} placeholder using the "Add Dash" button');
                    return;
                }
                
                if (fillBlankAnswers.length !== dashCount) {
                    showToast('error', `Please provide answers for all ${dashCount} blank(s)`);
                    return;
                }
                
                answers = fillBlankAnswers;
                hasCorrect = true; // All fill blank answers are correct
            } else {
                // Handle regular multiple/single choice answers
                var single = (type==='single_choice' || type==='true_false');

                document.querySelectorAll('#ansWrap .answer-option').forEach(function(blk, i){
                    var area = blk.querySelector('.rte-area');
                    var html = area ? area.innerHTML.trim() : '';
                    var plain = stripHtml(html).trim();
                    var correct = blk.querySelector('.answer-check').checked;

                    if (plain.length){
                        answers.push({
                            answer_title: html,
                            is_correct: correct ? 1 : 0,
                            answer_order: i+1,
                            belongs_question_type: type
                        });
                        if (correct) hasCorrect = true;
                    }
                });

                if (!answers.length){
                    showToast('error', 'Add at least one answer option');
                    return;
                }
                
                if (single){
                    var ccount = 0; 
                    answers.forEach(function(a){ if(a.is_correct===1) ccount++; });
                    if (ccount !== 1){ 
                        showToast('error', 'Select exactly one correct answer');
                        return; 
                    }
                } else if (!hasCorrect){
                    showToast('error', 'Mark at least one correct answer');
                    return;
                }
            }

            var edDescArea = document.querySelector('#edDesc .rte-area');
            var descHTML = edDescArea ? (edDescArea.innerHTML || '') : '';

            var edExplainArea = document.querySelector('#edExplain .rte-area');
            var explHTML = edExplainArea ? (edExplainArea.innerHTML || '') : '';

            var body = {
  quiz_id: quizId,
  question_title: titleHTML,
  question_description: descHTML,
  answer_explanation: explHTML,
  question_type: type,
  question_mark: Number(document.getElementById('qMarks')?.value || 1),
  question_order: Number(document.getElementById('qOrder')?.value || 1),
  question_difficulty: (document.getElementById('qDifficulty')?.value || 'medium'),
  answers: answers
};


            var btn = document.getElementById('btnSave');
            if (!btn) return;
            
            var originalText = btn.innerHTML;
            btn.disabled = true; 
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

            const url = editingId ? ('/api/quizz/questions/' + encodeURIComponent(editingId)) : '/api/quizz/questions';
            const method = editingId ? 'PUT' : 'POST';

            const resp = await apiFetch(url, {
                method: method,
                headers: { 'Content-Type':'application/json' },
                body: JSON.stringify(body)
            });

            if(!resp.ok){ 
                handleApiFailure('Save failed', resp); 
            } else { 
                showToast('success', editingId ? 'Question updated' : 'Question created'); 
                loadList(); 
                resetForm(); 
            }

            btn.disabled = false; 
            btn.innerHTML = originalText;
        }

        var btnDelete = document.getElementById('btnDelete');
        if (btnDelete) {
            btnDelete.addEventListener('click', async function(){
                if (!editingId) return;
                
                const c = await Swal.fire({
                    icon:'warning',
                    title:'Delete question?',
                    text:'This action cannot be undone',
                    showCancelButton:true,
                    confirmButtonText:'Delete',
                    cancelButtonText:'Cancel',
                    confirmButtonColor:'#ef4444'
                });
                
                if(!c.isConfirmed) return;

                const resp = await apiFetch('/api/quizz/questions/' + encodeURIComponent(editingId), { method:'DELETE' });
                
                if(!resp.ok){ 
                    handleApiFailure('Delete failed', resp); 
                    return; 
                }
                
                showToast('success', 'Question deleted');
                loadList(); 
                resetForm();
            });
        }

        (async function initQuiz(){
            if(!TOKEN){
                await Swal.fire({icon:'warning',title:'Login needed',confirmButtonText:'Login'});
                location.href='/';
                return;
            }
            if(!quizK){
                await Swal.fire({icon:'error',title:'Missing quiz parameter'});
                history.back();
                return;
            }
            
            const resp = await apiFetch('/api/quizz/' + encodeURIComponent(quizK), { method:'GET' });
    if(!resp.ok){
        handleApiFailure('Quiz load failed', resp);
        return;
    }
    
    var row = (resp.isJson ? (resp.data.data || resp.data) : {}) || {};
    quizId   = row.id;
    quizMeta = row;
    updateQuizHeader();
    
    loadList();
    resetForm();
        })();

        resetAnswersForType();
    });
    </script>
</body>
</html>