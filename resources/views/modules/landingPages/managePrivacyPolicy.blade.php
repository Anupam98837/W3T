{{-- resources/views/modules/privacy/manage.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title','Manage Privacy Policy')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
@push('styles')
<style>
/* ===== Page Container ===== */
.privacy-wrap {
    max-width: 1140px;
    margin: 16px auto 40px;
    overflow: visible;
}

/* ===== Toolbar ===== */
.privacy-toolbar {
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    box-shadow: var(--shadow-2);
    padding: 14px;
    margin-bottom: 16px;
}

.privacy-toolbar .form-control {
    height: 40px;
    border-radius: 12px;
    border: 1px solid var(--line-strong);
    background: var(--surface);
}

.privacy-toolbar .btn {
    height: 40px;
    border-radius: 12px;
}

.privacy-toolbar .btn-light {
    background: var(--surface);
    border: 1px solid var(--line-strong);
}

.privacy-toolbar .btn-primary {
    background: var(--primary-color);
    border: none;
}

/* ===== Card Container ===== */
.privacy-card {
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-2);
    overflow: hidden;
    position: relative;
}
/* ===== Form Body Padding ===== */
.privacy-body {
    padding: 24px;
}

.form-section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-primary);
}

.form-control {
    border: 1px solid var(--line-strong);
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
}

.info-box {
    border-radius: 8px;
    font-size: 13px;
    color: #1e40af;
}

.info-box i {
    margin-right: 6px;
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

/* ===== Loader ===== */
.loader-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--border);
    border-top: 4px solid var(--accent-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin { 0% {transform:rotate(0)} 100% {transform:rotate(360deg)} }

/* ===== Editor + Fields ===== */
.form-label{font-weight:600;margin-bottom:8px}
.form-control{border-radius:8px;}
.editor-wrapper{margin-bottom:20px;}
.editor-tabs{display:flex;border-bottom:1px solid var(--line-soft);}
.editor-tab{padding:10px 16px;cursor:pointer;font-size:13px;border-bottom:2px solid transparent;}
.editor-tab.active{border-bottom-color:var(--accent-color);color:var(--accent-color);}
.editor-content{display:none;border:1px solid var(--line-strong);border-top:none;}
.editor-content.active{display:block;}
.rte-toolbar{padding:8px;border-bottom:1px solid var(--line-soft);display:flex;gap:4px;}
.rte-area{min-height:300px;padding:16px;outline:none;}
.code-editor{width:100%;height:300px;background:#1e1e1e;color:white;padding:16px;}

/* ===== Actions ===== */
.privacy-actions {
    padding: 20px 24px;
    border-top: 1px solid var(--line-strong);
    background: var(--surface);
    display: flex;
    justify-content: space-between;
}
/* ===== RTE Toolbar Enhanced ===== */
.rte-toolbar {
    padding: 8px;
    border-bottom: 1px solid var(--line-soft);
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.rte-toolbar button {
    width: 36px;
    height: 36px;
    border: 1px solid var(--line-strong);
    background: var(--surface);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary);
}

.rte-toolbar button:hover {
    background: var(--hover-bg, #f3f4f6);
    border-color: var(--accent-color);
}

.rte-toolbar button.active {
    background: var(--accent-color, #3b82f6);
    color: white;
    border-color: var(--accent-color, #3b82f6);
}

.rte-toolbar button:active {
    transform: scale(0.95);
}

.rte-toolbar button i {
    font-size: 14px;
}
</style>
@endpush
@section('content')
<div class="privacy-wrap">

    {{-- Toolbar --}}
    <div class="privacy-toolbar">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <div>
                    <strong>Privacy Policy</strong>
                    <div class="privacy-sub">Manage your platform's privacy policy</div>
                </div>
            </div>

            <div class="col-md-6 d-flex justify-content-end">
                <span id="privacyStatus" class="status-badge draft" style="display:none;">
                    <i class="fa-solid fa-circle fa-xs"></i> <span>Draft</span>
                </span>
                <span class="text-muted small" id="lastUpdatedPrivacy">Last updated: Never</span>
            </div>
        </div>
    </div>

    {{-- Card --}}
    <div class="privacy-card">
        <div class="loader-overlay" id="loaderPrivacy">
            <div class="spinner"></div>
        </div>

        <div class="privacy-body">
            <form id="privacyForm">

                <div class="form-section">
                    <h3 class="section-title"><i class="fa-solid fa-heading"></i> Basic Information</h3>

                    <label class="form-label" for="privacyTitle">Title <span class="text-danger">*</span></label>
                    <input type="text" id="privacyTitle" class="form-control" maxlength="255" placeholder="Enter privacy policy title">
                    <div id="privacyTitleError" class="error-message text-danger"></div>

                    <div class="info-box mt-3 p-2" style="border:1px solid #bfdbfe;background:#eff6ff;">
                        <i class="fa-solid fa-info-circle"></i>
                        Only one privacy policy document can exist at a time.
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fa-solid fa-file-lines"></i> Content</h3>

                    <div class="editor-wrapper">
                        <div class="editor-tabs">
                            <div class="editor-tab active" data-tab="visual">Visual Editor</div>
                            <div class="editor-tab" data-tab="code">HTML Editor</div>
                        </div>

                        <div class="editor-content active" data-tab="visual">
                            <div class="rte-toolbar">
                                <button type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                                <button type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                                <button type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                                <button type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
                                <button type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
                                <button type="button" id="btnPrivacyLink"><i class="fa-solid fa-link"></i></button>
                            </div>
                            <div id="privacyEditor" class="rte-area" contenteditable="true"></div>
                        </div>

                        <div class="editor-content" data-tab="code">
                            <textarea id="privacyCode" class="code-editor"></textarea>
                        </div>
                    </div>

                    <div id="privacyContentError" class="error-message text-danger"></div>
                </div>

                <input type="hidden" id="privacyId">
                <input type="hidden" id="privacyMode" value="false">

            </form>
        </div>

        {{-- Actions --}}
        <div class="privacy-actions">
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <button type="button" id="btnPrivacyPreview" class="btn btn-light" style="display:none;">
                    <i class="fa-solid fa-eye"></i> Preview
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="btnPrivacyReset" class="btn btn-light">Reset</button>
                <button type="button" id="btnPrivacyDelete" class="btn btn-danger" style="display:none;">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
                <button type="button" id="btnPrivacySave" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Save Privacy Policy
                </button>
            </div>
        </div>

    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="privacyPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy Preview</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="privacyPreviewContent"></div>
            </div>
        </div>
    </div>
</div>

{{-- Toast system --}}
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="okToast" class="toast text-bg-success"></div>
    <div id="errToast" class="toast text-bg-danger mt-2"></div>
    <div id="warningToast" class="toast text-bg-warning mt-2"></div>
</div>

@endsection

@push('scripts')
<script>
(function(){

const TOKEN = sessionStorage.getItem('token') || localStorage.getItem('token');
if(!TOKEN){ location.href = '/'; }

/* -------------- DOM -------------- */
const loader = document.getElementById('loaderPrivacy');
const privacyTitle = document.getElementById('privacyTitle');
const privacyEditor = document.getElementById('privacyEditor');
const privacyCode = document.getElementById('privacyCode');
const privacyId = document.getElementById('privacyId');
const privacyMode = document.getElementById('privacyMode');

const statusBadge = document.getElementById('privacyStatus');
const lastUpdated = document.getElementById('lastUpdatedPrivacy');

const btnSave = document.getElementById('btnPrivacySave');
const btnDelete = document.getElementById('btnPrivacyDelete');
const btnReset = document.getElementById('btnPrivacyReset');
const btnPreview = document.getElementById('btnPrivacyPreview');
const btnLink = document.getElementById('btnPrivacyLink');

/* -------------- Helpers -------------- */
function showLoader(x){ loader.style.display = x ? 'flex' : 'none'; }
function stripHTML(html){ return new DOMParser().parseFromString(html,"text/html").body.textContent; }

async function api(url,method='GET',body=null){
    const res = await fetch(url,{
        method,
        headers:{
            'Authorization':'Bearer '+TOKEN,
            'Content-Type':'application/json'
        },
        body: body ? JSON.stringify(body): null
    });
    return res.json();
}

/* -------------- RTE Functionality -------------- */
function execCommand(command, value = null) {
    document.execCommand(command, false, value);
    privacyEditor.focus();
    updateToolbarState();
}

function updateToolbarState() {
    const buttons = document.querySelectorAll('.rte-toolbar button[data-cmd]');
    buttons.forEach(btn => {
        const cmd = btn.dataset.cmd;
        const isActive = document.queryCommandState(cmd);
        if (isActive) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

// RTE Toolbar buttons
document.querySelectorAll('.rte-toolbar button[data-cmd]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const cmd = btn.dataset.cmd;
        execCommand(cmd);
    });
});

// Link button
if (btnLink) {
    btnLink.addEventListener('click', (e) => {
        e.preventDefault();
        const url = prompt('Enter URL:');
        if (url) {
            execCommand('createLink', url);
        }
    });
}

// Update toolbar state on selection change
privacyEditor.addEventListener('mouseup', updateToolbarState);
privacyEditor.addEventListener('keyup', updateToolbarState);
privacyEditor.addEventListener('focus', updateToolbarState);

// Sync code editor on input
privacyEditor.addEventListener('input', () => {
    privacyCode.value = privacyEditor.innerHTML;
});

/* -------------- Load Existing -------------- */
async function loadPrivacy(){
    showLoader(true);
    const r = await api('/api/privacy-policy/check');

    if(r.exists){
        privacyMode.value = 'true';
        privacyId.value = r.privacy_policy.id;

        privacyTitle.value = r.privacy_policy.title;
        privacyEditor.innerHTML = r.privacy_policy.content;
        privacyCode.value = r.privacy_policy.content;

        statusBadge.style.display = 'inline-flex';
        statusBadge.className = 'status-badge published';
        statusBadge.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i> Published';

        lastUpdated.textContent = "Last updated: " + r.privacy_policy.updated_at;

        btnDelete.style.display = 'inline-flex';
        btnPreview.style.display = 'inline-flex';
    } else {
        statusBadge.style.display = 'inline-flex';
        statusBadge.className = 'status-badge draft';
        lastUpdated.textContent = "Last updated: Never";
    }
    showLoader(false);
}

/* -------------- Sync Editors -------------- */
document.querySelectorAll('.editor-tab').forEach(tab=>{
    tab.addEventListener('click',()=>{
        document.querySelectorAll('.editor-tab').forEach(t=>t.classList.remove('active'));
        tab.classList.add('active');

        const x = tab.dataset.tab;
        document.querySelectorAll('.editor-content').forEach(c=>c.classList.remove('active'));
        document.querySelector(`.editor-content[data-tab="${x}"]`).classList.add('active');

        if(x === 'code'){ 
            privacyCode.value = privacyEditor.innerHTML; 
        }
        else { 
            privacyEditor.innerHTML = privacyCode.value;
            updateToolbarState();
        }
    });
});

/* -------------- Save -------------- */
btnSave.onclick = async ()=>{
    if(!privacyTitle.value.trim()) return alert("Title required.");
    if(stripHTML(privacyEditor.innerHTML).trim()==='') return alert("Content required.");

    showLoader(true);

    const body = {
        title: privacyTitle.value.trim(),
        content: privacyEditor.innerHTML.trim()
    };

    const r = await api('/api/privacy-policy','POST',body);

    showLoader(false);

    if(r.success){
        alert("Saved!");
        loadPrivacy();
    }
};

/* -------------- Delete -------------- */
btnDelete.onclick = async ()=>{
    if(!confirm("Delete privacy policy?")) return;
    showLoader(true);
    await api('/api/privacy-policy','DELETE');
    showLoader(false);
    location.reload();
};

/* -------------- Reset -------------- */
btnReset.onclick = ()=> location.reload();

/* -------------- Preview -------------- */
btnPreview.onclick = ()=>{
    document.getElementById('privacyPreviewContent').innerHTML = privacyEditor.innerHTML;
    new bootstrap.Modal('#privacyPreviewModal').show();
};

/* Init */
loadPrivacy();

})();
</script>
@endpush
