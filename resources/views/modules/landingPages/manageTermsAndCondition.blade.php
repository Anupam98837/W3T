{{-- resources/views/modules/terms/manage.blade.php --}}
@section('title','Manage Terms & Conditions')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
@push('styles')
<style>
/* ===== Page Container ===== */
.terms-wrap {
    max-width: 1140px;
    margin: 16px auto 40px;
    overflow: visible;
}

/* ===== Toolbar ===== */
.terms-toolbar {
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    box-shadow: var(--shadow-2);
    padding: 14px;
    margin-bottom: 16px;
}

.terms-toolbar .form-control {
    height: 40px;
    border-radius: 12px;
    border: 1px solid var(--line-strong);
    background: var(--surface);
}

.terms-toolbar .btn {
    height: 40px;
    border-radius: 12px;
}

.terms-toolbar .btn-light {
    background: var(--surface);
    border: 1px solid var(--line-strong);
}

.terms-toolbar .btn-primary {
    background: var(--primary-color);
    border: none;
}

/* ===== Card Container ===== */
.terms-card {
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-2);
    overflow: hidden;
    position: relative;
}

/* ===== Header ===== */
.terms-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--line-strong);
    background: var(--surface);
}

.terms-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.terms-head i {
    color: var(--accent-color);
    font-size: 20px;
}

.terms-head strong {
    color: var(--ink);
    font-family: var(--font-head);
    font-weight: 700;
    font-size: 20px;
}

.terms-sub {
    color: var(--muted-color);
    font-size: var(--fs-13);
    margin-bottom: 0;
}

/* ===== Status Badge ===== */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.draft {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fbbf24;
}

.status-badge.published {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #22c55e;
}

/* ===== Content Area ===== */
.terms-body {
    padding: 24px;
}

/* ===== Loading States ===== */
.loader-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 100;
    border-radius: 12px;
}

html.theme-dark .loader-overlay {
    background: rgba(0, 0, 0, 0.6);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--border);
    border-top: 4px solid var(--accent-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ===== Form Elements ===== */
.form-section {
    margin-bottom: 28px;
}

.section-title {
    font-weight: 600;
    color: var(--ink);
    font-family: var(--font-head);
    margin: 0 0 14px 0;
    font-size: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--line-soft);
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i {
    color: var(--accent-color);
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--ink);
    font-size: 14px;
}

.form-control, .form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    background: var(--surface);
    color: var(--ink);
    transition: all 0.2s;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-control.error, .form-select.error {
    border-color: #ef4444;
}

.error-message {
    font-size: 12px;
    color: #ef4444;
    margin-top: 6px;
    display: none;
}

.error-message.show {
    display: block;
}
/* About Us - Save button (doesn't touch global .btn-primary / pills) */
.au-save-btn{
  background: var(--primary-color);
  border: 1px solid var(--primary-color);
  color: #fff;
  border-radius: 12px;
  padding: .6rem 1rem;
  font-weight: 600;
}
.au-save-btn:hover{
  background: var(--secondary-color);
  border-color: var(--secondary-color);
}
.au-save-btn:focus{
  box-shadow: 0 0 0 .2rem rgba(158,54,58,.25);
}

.info-box {
    padding: 12px 16px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    font-size: 13px;
    color: #1e40af;
    margin-top: 16px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

html.theme-dark .info-box {
    background: #1e3a8a;
    border-color: #3b82f6;
    color: #bfdbfe;
}

.info-box i {
    margin-top: 2px;
    font-size: 14px;
}

/* ===== Rich Text Editor ===== */
.editor-wrapper {
    position: relative;
    margin-bottom: 20px;
}

.editor-tabs {
    display: flex;
    border-bottom: 1px solid var(--line-soft);
    background: var(--surface);
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    overflow: hidden;
}

.editor-tab {
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    color: var(--muted);
    background: var(--surface);
}

.editor-tab.active {
    border-bottom-color: var(--accent-color);
    color: var(--accent-color);
    font-weight: 600;
}

.editor-content {
    display: none;
    border: 1px solid var(--line-strong);
    border-top: none;
    border-radius: 0 0 8px 8px;
    overflow: hidden;
}

.editor-content.active {
    display: block;
}

/* Toolbar */
.rte-toolbar {
    padding: 8px;
    background: var(--surface);
    border-bottom: 1px solid var(--line-soft);
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.rte-toolbar button {
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

.rte-toolbar button:hover {
    background: var(--accent-color);
    border-color: var(--accent-color);
    color: white;
}

.rte-toolbar button.active {
    background: color-mix(in oklab, var(--accent-color) 14%, transparent);
    border-color: color-mix(in oklab, var(--accent-color) 28%, transparent);
    color: var(--accent-color);
}

/* Editor Area */
.rte-area {
    min-height: 300px;
    max-height: 500px;
    overflow-y: auto;
    padding: 16px;
    background: var(--surface);
    outline: none;
    font-size: 14px;
    line-height: 1.6;
    color: var(--ink);
}

.rte-area:focus {
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Code Editor */
.code-editor {
    width: 100%;
    height: 300px;
    padding: 16px;
    border: none;
    background: #1e1e1e;
    color: #d4d4d4;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.5;
    resize: vertical;
    outline: none;
}

html.theme-dark .code-editor {
    background: #0f172a;
    color: #e2e8f0;
}

/* ===== Action Buttons ===== */
.terms-actions {
    padding: 20px 24px;
    border-top: 1px solid var(--line-strong);
    background: var(--surface);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.btn {
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

.btn-primary {
    background: var(--accent-color);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #4338ca;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-light {
    background: var(--surface);
    color: var(--ink);
    border: 1px solid var(--border);
}

.btn-light:hover {
    background: var(--bg-gray);
    border-color: var(--accent-color);
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #dc2626;
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}

.empty-state i {
    font-size: 48px;
    opacity: 0.3;
    margin-bottom: 16px;
}

/* ===== Dark Mode ===== */
html.theme-dark .terms-card,
html.theme-dark .terms-toolbar {
    background: #0f172a;
    border-color: var(--line-strong);
}

html.theme-dark .rte-area,
html.theme-dark .rte-toolbar {
    background: #0f172a;
    border-color: var(--line-strong);
}

html.theme-dark .rte-toolbar button {
    background: #0f172a;
    border-color: var(--line-strong);
    color: #e5e7eb;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .terms-wrap {
        margin: 10px;
    }
    
    .terms-header,
    .terms-body,
    .terms-actions {
        padding: 16px;
    }
    
    .terms-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .terms-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .rte-toolbar {
        justify-content: center;
    }
    
    .terms-toolbar .row {
        flex-direction: column;
        gap: 12px;
    }
}
</style>
@endpush
@section('content')
<div class="terms-wrap">
    {{-- Toolbar --}}
    <div class="terms-toolbar">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <strong>Terms & Conditions</strong>
                        <div class="terms-sub">Manage your platform's terms and conditions</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                <div class="d-flex align-items-center gap-2">
                    <span id="termsStatus" class="status-badge draft" style="display: none;">
                        <i class="fa-solid fa-circle fa-xs"></i>
                        <span>Draft</span>
                    </span>
                    <span class="text-muted small" id="lastUpdated">
                        Last updated: Never
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card --}}
    <div class="terms-card">
        {{-- Loading Overlay --}}
        <div class="loader-overlay" id="loader">
            <div class="spinner"></div>
        </div>

        {{-- Form --}}
        <div class="terms-body">
            <form id="termsForm">
                {{-- Title Section --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-heading"></i>
                        Basic Information
                    </h3>
                    
                    <div class="mb-3">
                        <label class="form-label" for="termsTitle">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="termsTitle" 
                            class="form-control" 
                            maxlength="255" 
                            placeholder="Enter terms and conditions title"
                            required
                        >
                        <div id="titleError" class="error-message"></div>
                    </div>

                    <div class="info-box">
                        <i class="fa-solid fa-info-circle"></i>
                        <div>
                            <strong>Note:</strong> Only one terms and conditions document can exist at a time.
                            If content already exists, this form will be in edit mode.
                        </div>
                    </div>
                </div>

                {{-- Content Section --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-file-lines"></i>
                        Content
                    </h3>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            Full Content <span class="text-danger">*</span>
                        </label>
                        
                        {{-- Rich Text Editor --}}
                        <div class="editor-wrapper">
                            <div class="editor-tabs">
                                <div class="editor-tab active" data-tab="visual">Visual Editor</div>
                                <div class="editor-tab" data-tab="code">HTML Editor</div>
                            </div>
                            
                            {{-- Visual Editor --}}
                            <div class="editor-content active" data-tab="visual">
                                <div class="rte-toolbar">
                                    <button type="button" data-cmd="bold" title="Bold">
                                        <i class="fa-solid fa-bold"></i>
                                    </button>
                                    <button type="button" data-cmd="italic" title="Italic">
                                        <i class="fa-solid fa-italic"></i>
                                    </button>
                                    <button type="button" data-cmd="underline" title="Underline">
                                        <i class="fa-solid fa-underline"></i>
                                    </button>
                                    <button type="button" data-cmd="insertUnorderedList" title="Bulleted List">
                                        <i class="fa-solid fa-list-ul"></i>
                                    </button>
                                    <button type="button" data-cmd="insertOrderedList" title="Numbered List">
                                        <i class="fa-solid fa-list-ol"></i>
                                    </button>
                                    <button type="button" data-cmd="formatBlock" data-format="h1" title="Heading 1">
                                        H1
                                    </button>
                                    <button type="button" data-cmd="formatBlock" data-format="h2" title="Heading 2">
                                        H2
                                    </button>
                                    <button type="button" data-cmd="formatBlock" data-format="h3" title="Heading 3">
                                        H3
                                    </button>
                                    <button type="button" id="btnLink" title="Insert Link">
                                        <i class="fa-solid fa-link"></i>
                                    </button>
                                </div>
                                <div 
                                    id="contentEditor" 
                                    class="rte-area" 
                                    contenteditable="true" 
                                    spellcheck="true"
                                    data-placeholder="Write your terms and conditions here..."
                                ></div>
                            </div>
                            
                            {{-- HTML Editor --}}
                            <div class="editor-content" data-tab="code">
                                <textarea 
                                    id="contentCode" 
                                    class="code-editor" 
                                    placeholder="&lt;h1&gt;Your Terms & Conditions&lt;/h1&gt;&#10;&lt;p&gt;Start writing your content in HTML...&lt;/p&gt;"
                                ></textarea>
                            </div>
                        </div>
                        
                        <div id="contentError" class="error-message"></div>
                    </div>
                </div>
                
                <input type="hidden" id="termsId">
                <input type="hidden" id="isEditMode" value="false">
            </form>
        </div>

        {{-- Actions --}}
        <div class="terms-actions">
            <div>
                <a href="{{ url()->previous() ?: '/' }}" class="btn btn-light">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>
                <button type="button" id="btnPreview" class="btn btn-light" style="display: none;">
                    <i class="fa-solid fa-eye"></i>
                    Preview
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="btnReset" class="btn btn-light">
                    <i class="fa-solid fa-rotate-left"></i>
                    Reset
                </button>
                <button type="button" id="btnDelete" class="btn btn-danger" style="display: none;">
                    <i class="fa-solid fa-trash"></i>
                    Delete
                </button>
                <button type="button" id="btnSave" class="btn au-save-btn">
  <i class="fa-solid fa-save"></i>
  Save Terms
</button>

            </div>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewTitle">Terms & Conditions Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
    <div id="okToast" class="toast text-bg-success border-0">
        <div class="d-flex">
            <div id="okMsg" class="toast-body">Success!</div>
            <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="errToast" class="toast text-bg-danger border-0 mt-2">
        <div class="d-flex">
            <div id="errMsg" class="toast-body">Error!</div>
            <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="warningToast" class="toast text-bg-warning border-0 mt-2">
        <div class="d-flex">
            <div id="warningMsg" class="toast-body">Warning!</div>
            <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* =================== App logic =================== */
(function(){
    // Authentication
    const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
    if(!TOKEN){
        Swal.fire({
            icon: 'warning',
            title: 'Login needed',
            text: 'Your session expired. Please login again.',
            confirmButtonText: 'Login'
        }).then(() => location.href = '/');
        return;
    }

    /* ===== Toasts (from course modules) ===== */
    const okToast = new bootstrap.Toast(document.getElementById('okToast'));
    const errToast = new bootstrap.Toast(document.getElementById('errToast'));
    const warningToast = new bootstrap.Toast(document.getElementById('warningToast'));
    
    const showToast = (type, message) => {
        let toast, msgEl;
        if (type === 'success') {
            toast = okToast;
            msgEl = document.getElementById('okMsg');
        } else if (type === 'error') {
            toast = errToast;
            msgEl = document.getElementById('errMsg');
        } else {
            toast = warningToast;
            msgEl = document.getElementById('warningMsg');
        }
        
        if (msgEl) msgEl.textContent = message;
        toast.show();
    };

    /* ===== DOM Elements ===== */
    const loader = document.getElementById('loader');
    const termsTitle = document.getElementById('termsTitle');
    const contentEditor = document.getElementById('contentEditor');
    const contentCode = document.getElementById('contentCode');
    const termsId = document.getElementById('termsId');
    const isEditMode = document.getElementById('isEditMode');
    const btnSave = document.getElementById('btnSave');
    const btnReset = document.getElementById('btnReset');
    const btnDelete = document.getElementById('btnDelete');
    const btnPreview = document.getElementById('btnPreview');
    const termsStatus = document.getElementById('termsStatus');
    const lastUpdated = document.getElementById('lastUpdated');
    
    // Preview Modal
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const previewTitle = document.getElementById('previewTitle');
    const previewContent = document.getElementById('previewContent');
    
    /* ===== State ===== */
    let currentEditor = null;
    let currentSelection = null;
    let termsData = null;
    
    /* ===== Helper Functions (from course modules) ===== */
    const esc = (s) => {
        const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
        return String(s || '').replace(/[&<>"']/g, m => map[m]);
    };
    
    const stripHtml = (html) => {
        if (!html) return '';
        const doc = new DOMParser().parseFromString(html, 'text/html');
        return doc.body.textContent || '';
    };
    
    const showLoader = (show) => {
        if (loader) loader.style.display = show ? 'flex' : 'none';
    };
    
    const formatDate = (iso) => {
        if (!iso) return '-';
        const d = new Date(iso);
        if (isNaN(d)) return iso;
        return d.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    };
    
    /* ===== API Helper (from course modules) ===== */
    async function apiFetch(url, options = {}) {
        options.headers = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + TOKEN,
            ...options.headers
        };
        
        const response = await fetch(url, options);
        const contentType = response.headers.get('content-type') || '';
        
        try {
            if (contentType.includes('application/json')) {
                const data = await response.json();
                return {
                    ok: response.ok,
                    status: response.status,
                    data: data
                };
            } else {
                const text = await response.text();
                return {
                    ok: response.ok,
                    status: response.status,
                    data: text
                };
            }
        } catch (error) {
            return {
                ok: false,
                status: response.status,
                data: null
            };
        }
    }
    
    function handleApiError(prefix, response) {
        console.error(prefix, response);
        
        if (response.status === 401 || response.status === 419) {
            showToast('error', 'Session expired. Please login again.');
            setTimeout(() => location.href = '/', 2000);
            return;
        }
        
        if (response.data && response.data.message) {
            showToast('error', `${prefix}: ${response.data.message}`);
        } else {
            showToast('error', `${prefix}: HTTP ${response.status}`);
        }
    }
    
    /* ===== Editor Functions ===== */
    function saveCursorPosition(editorArea) {
        currentEditor = editorArea;
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            currentSelection = selection.getRangeAt(0);
        }
    }
    
    function restoreCursorPosition() {
        if (currentEditor && currentSelection) {
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(currentSelection);
            currentEditor.focus();
        }
    }
    
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
                
                // Sync content
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
        
        // Sync visual editor when code editor changes
        codeArea.addEventListener('input', function() {
            if (editorWrapper.querySelector('.editor-tab[data-tab="visual"]').classList.contains('active')) {
                visualArea.innerHTML = this.value;
            }
        });
    }
    
    function setupEditorToolbar(editorWrapper) {
        const toolbar = editorWrapper.querySelector('.rte-toolbar');
        const area = editorWrapper.querySelector('.rte-area');
        
        if (!toolbar || !area) return;
        
        // Save cursor position when interacting with editor
        area.addEventListener('click', () => saveCursorPosition(area));
        area.addEventListener('keyup', () => saveCursorPosition(area));
        
        // Handle toolbar buttons
        toolbar.addEventListener('click', function(e) {
            const button = e.target.closest('button');
            if (!button) return;
            e.preventDefault();
            
            if (button.dataset.cmd) {
                saveCursorPosition(area);
                document.execCommand(button.dataset.cmd, false, null);
                area.focus();
            } else if (button.id === 'btnLink') {
                saveCursorPosition(area);
                const url = prompt('Enter URL:');
                if (url) {
                    document.execCommand('createLink', false, url);
                }
                area.focus();
            }
        });
        
        // Update toolbar button states based on selection
        function updateToolbarState() {
            const buttons = toolbar.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.cmd) {
                    try {
                        if (document.queryCommandState(btn.dataset.cmd)) {
                            btn.classList.add('active');
                        }
                    } catch (e) {}
                }
            });
        }
        
        area.addEventListener('mouseup', updateToolbarState);
        area.addEventListener('keyup', updateToolbarState);
        document.addEventListener('selectionchange', updateToolbarState);
    }
    
    /* ===== Load Existing Terms ===== */
    async function loadExistingTerms() {
        showLoader(true);
        
        try {
            const response = await apiFetch('/api/terms/check');
            
            if (response.ok && response.data) {
                const data = response.data;
                
                if (data.exists && data.terms) {
                    // Edit mode - populate form
                    isEditMode.value = 'true';
                    termsData = data.terms;
                    
                    // Update UI
                    termsStatus.style.display = 'inline-flex';
                    termsStatus.className = 'status-badge published';
                    termsStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Published</span>';
                    
                    // Format date
                    lastUpdated.textContent = 'Last updated: ' + formatDate(termsData.updated_at);
                    
                    // Populate form
                    termsTitle.value = termsData.title || '';
                    contentEditor.innerHTML = termsData.content || '';
                    contentCode.value = termsData.content || '';
                    termsId.value = termsData.id || '';
                    
                    // Show delete and preview buttons
                    btnDelete.style.display = 'inline-flex';
                    btnPreview.style.display = 'inline-flex';
                    
                    showToast('success', 'Existing terms loaded successfully');
                } else {
                    // Create mode
                    isEditMode.value = 'false';
                    termsStatus.style.display = 'inline-flex';
                    termsStatus.className = 'status-badge draft';
                    termsStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Draft</span>';
                    lastUpdated.textContent = 'Last updated: Never';
                    
                    showToast('warning', 'No existing terms found. Ready to create new document.');
                }
            } else {
                handleApiError('Failed to check terms', response);
            }
        } catch (error) {
            console.error('Error loading terms:', error);
            showToast('error', 'Failed to load terms. Please try again.');
        } finally {
            showLoader(false);
        }
    }
    
    /* ===== Form Validation ===== */
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
            el.classList.remove('show');
        });
        
        document.querySelectorAll('.form-control.error, .form-select.error').forEach(el => {
            el.classList.remove('error');
        });
        
        // Validate title
        if (!termsTitle.value.trim()) {
            const errorEl = document.getElementById('titleError');
            errorEl.textContent = 'Title is required';
            errorEl.classList.add('show');
            termsTitle.classList.add('error');
            isValid = false;
        }
        
        // Validate content
        const content = contentEditor.innerHTML.trim();
        const plainContent = stripHtml(content);
        if (!plainContent) {
            const errorEl = document.getElementById('contentError');
            errorEl.textContent = 'Content is required';
            errorEl.classList.add('show');
            contentEditor.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            contentEditor.style.borderColor = '';
        }
        
        return isValid;
    }
    
    /* ===== Save Terms ===== */
    async function saveTerms() {
        if (!validateForm()) {
            return;
        }
        
        showLoader(true);
        btnSave.disabled = true;
        const originalText = btnSave.innerHTML;
        btnSave.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        
        try {
            const payload = {
                title: termsTitle.value.trim(),
                content: contentEditor.innerHTML.trim()
            };
            
            let url = '/api/terms';
            let method = 'POST';
            
            if (isEditMode.value === 'true' && termsId.value) {
                // Edit mode - update existing
                url = '/api/terms';
                method = 'PUT';
                payload.id = termsId.value;
            }
            
            const response = await apiFetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            if (response.ok) {
                showToast('success', isEditMode.value === 'true' ? 
                    'Terms updated successfully!' : 'Terms created successfully!');
                
                // Update UI for edit mode
                if (isEditMode.value === 'false') {
                    isEditMode.value = 'true';
                    termsStatus.className = 'status-badge published';
                    termsStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Published</span>';
                    
                    const now = new Date();
                    lastUpdated.textContent = 'Last updated: ' + now.toLocaleString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    btnDelete.style.display = 'inline-flex';
                    btnPreview.style.display = 'inline-flex';
                    
                    if (response.data && response.data.terms) {
                        termsId.value = response.data.terms.id || '';
                    }
                } else {
                    // Refresh updated date
                    lastUpdated.textContent = 'Last updated: ' + formatDate(new Date().toISOString());
                }
            } else {
                handleApiError('Failed to save terms', response);
            }
        } catch (error) {
            console.error('Error saving terms:', error);
            showToast('error', 'Failed to save terms. Please try again.');
        } finally {
            showLoader(false);
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        }
    }
    
    /* ===== Delete Terms ===== */
    async function deleteTerms() {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Delete Terms & Conditions?',
            text: 'This will permanently delete the current terms and conditions document.',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        });
        
        if (!result.isConfirmed) return;
        
        showLoader(true);
        
        try {
            const response = await apiFetch('/api/terms', {
                method: 'DELETE'
            });
            
            if (response.ok) {
                showToast('success', 'Terms deleted successfully!');
                
                // Reset form to create mode
                resetForm();
                isEditMode.value = 'false';
                termsStatus.className = 'status-badge draft';
                termsStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Draft</span>';
                lastUpdated.textContent = 'Last updated: Never';
                btnDelete.style.display = 'none';
                btnPreview.style.display = 'none';
                
                setTimeout(() => {
                    loadExistingTerms();
                }, 1000);
            } else {
                handleApiError('Failed to delete terms', response);
            }
        } catch (error) {
            console.error('Error deleting terms:', error);
            showToast('error', 'Failed to delete terms. Please try again.');
        } finally {
            showLoader(false);
        }
    }
    
    /* ===== Reset Form ===== */
    function resetForm() {
        if (isEditMode.value === 'true' && termsData) {
            // Reset to original data
            termsTitle.value = termsData.title || '';
            contentEditor.innerHTML = termsData.content || '';
            contentCode.value = termsData.content || '';
            showToast('warning', 'Form reset to original values');
        } else {
            // Clear form for create mode
            termsTitle.value = '';
            contentEditor.innerHTML = '';
            contentCode.value = '';
            showToast('warning', 'Form cleared');
        }
        
        // Clear errors
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
            el.classList.remove('show');
        });
        
        document.querySelectorAll('.form-control.error, .form-select.error').forEach(el => {
            el.classList.remove('error');
        });
        
        contentEditor.style.borderColor = '';
    }
    
    /* ===== Preview Terms ===== */
    function previewTerms() {
        const title = termsTitle.value.trim() || 'Untitled Terms';
        const content = contentEditor.innerHTML.trim();
        
        if (!content) {
            showToast('error', 'No content to preview');
            return;
        }
        
        previewTitle.textContent = title;
        previewContent.innerHTML = content;
        previewModal.show();
    }
    
    /* ===== Event Listeners ===== */
    btnSave.addEventListener('click', saveTerms);
    btnReset.addEventListener('click', resetForm);
    btnDelete.addEventListener('click', deleteTerms);
    btnPreview.addEventListener('click', previewTerms);
    
    // Add placeholder text to editor
    contentEditor.addEventListener('focus', function() {
        if (!this.innerHTML.trim()) {
            this.innerHTML = '<p>Start writing your terms and conditions here...</p>';
        }
    });
    
    contentEditor.addEventListener('blur', function() {
        if (this.innerHTML === '<p>Start writing your terms and conditions here...</p>') {
            this.innerHTML = '';
        }
    });
    
    /* ===== Initialize ===== */
    // Initialize editor
    const editorWrapper = document.querySelector('.editor-wrapper');
    if (editorWrapper) {
        setupEditorTabs(editorWrapper);
        setupEditorToolbar(editorWrapper);
    }
    
    // Load existing terms on page load
    loadExistingTerms();
})();
</script>
@endpush