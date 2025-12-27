{{-- resources/views/modules/refund/manage.blade.php --}}

@section('title','Manage Refund Policy')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
@push('styles')
<style>
/* ===== Page Container ===== */
.refund-wrap {
    max-width: 1140px;
    margin: 16px auto 40px;
}

/* ===== Toolbar ===== */
.refund-toolbar {
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    box-shadow: var(--shadow-2);
    padding: 14px;
    margin-bottom: 16px;
}

.refund-toolbar .btn-light {
    background: var(--surface);
    border: 1px solid var(--line-strong);
}

/* ===== Card ===== */
.refund-card {
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    background: var(--surface);
    position: relative;
}

/* ===== Loader ===== */
.loader-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99;
}
.spinner {
    width:40px;height:40px;border:4px solid var(--border);
    border-top:4px solid var(--accent-color);
    border-radius:50%;animation:spin 1s linear infinite;
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* ===== Editor ===== */
.editor-wrapper { margin-bottom:20px; }
.editor-tabs { display:flex;border-bottom:1px solid var(--line-soft); }
.editor-tab { padding:10px 16px;cursor:pointer;border-bottom:2px solid transparent; }
.editor-tab.active { border-bottom-color:var(--accent-color);color:var(--accent-color); }
.editor-content { display:none;border:1px solid var(--line-strong);border-top:none; }
.editor-content.active { display:block; }
.rte-toolbar { padding:8px;border-bottom:1px solid var(--line-soft);display:flex;gap:4px; }
.rte-area { min-height:300px;padding:16px;outline:none; }
.code-editor { width:100%;height:300px;background:#1e1e1e;color:white;padding:16px; }

/* ===== Actions ===== */
.refund-actions {
    padding:20px 24px;
    border-top:1px solid var(--line-strong);
    display:flex;
    justify-content:space-between;
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

/* ===== Form Sections ===== */
.form-section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-control {
    border: 1px solid var(--line-strong);
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 8px;
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
}

.info-box {
    border-radius: 8px;
    font-size: 13px;
}
</style>
@endpush
@section('content')
<div class="refund-wrap">

    {{-- Toolbar --}}
    <div class="refund-toolbar d-flex justify-content-between">
        <div>
            <strong>Refund Policy</strong>
            <div class="text-muted small">Manage your platform’s refund policy</div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span id="refundStatus" class="status-badge draft" style="display:none;">
                <i class="fa-solid fa-circle fa-xs"></i> Draft
            </span>
            <span class="text-muted small" id="refundLastUpdated">Last updated: Never</span>
        </div>
    </div>

    {{-- Card --}}
    <div class="refund-card">

        {{-- Loader --}}
        <div class="loader-overlay" id="refundLoader">
            <div class="spinner"></div>
        </div>

        <div class="p-4">

            <form id="refundForm">

                {{-- Basic --}}
                <div class="form-section">
                    <h4 class="section-title"><i class="fa-solid fa-heading"></i> Basic Information</h4>

                    <label>Title <span class="text-danger">*</span></label>
                    <input type="text" id="refundTitle" class="form-control" placeholder="Enter refund policy title">
                    <div id="refundTitleError" class="text-danger small"></div>

                    <div class="info-box mt-3 p-2" style="background:#eff6ff;border:1px solid #bfdbfe;">
                        <i class="fa-solid fa-info-circle"></i>
                        Only one refund policy can exist at a time.
                    </div>
                </div>

                {{-- Content --}}
                <div class="form-section">
                    <h4 class="section-title"><i class="fa-solid fa-file-lines"></i> Content</h4>

                    <div class="editor-wrapper">
                        <div class="editor-tabs">
                            <div class="editor-tab active" data-tab="visual">Visual Editor</div>
                            <div class="editor-tab" data-tab="code">HTML Editor</div>
                        </div>

                        {{-- Visual --}}
                        <div class="editor-content active" data-tab="visual">
                            <div class="rte-toolbar">
                                <button type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                                <button type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                                <button type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                                <button type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
                                <button type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
                            </div>

                            <div id="refundEditor" class="rte-area" contenteditable="true"></div>
                        </div>

                        {{-- Code --}}
                        <div class="editor-content" data-tab="code">
                            <textarea id="refundCode" class="code-editor"></textarea>
                        </div>

                    </div>

                    <div id="refundContentError" class="text-danger small"></div>
                </div>

                <input type="hidden" id="refundId">
                <input type="hidden" id="refundMode" value="false">

            </form>
        </div>

        {{-- Actions --}}
        <div class="refund-actions">
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i> Back</a>

                <button id="btnRefundPreview" class="btn btn-light" style="display:none;">
                    <i class="fa-solid fa-eye"></i> Preview
                </button>
            </div>

            <div class="d-flex gap-2">
                <button id="btnRefundReset" class="btn btn-light">Reset</button>

                <button id="btnRefundDelete" class="btn btn-danger" style="display:none;">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>

                <button id="btnRefundSave" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Save Refund Policy
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="refundPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refund Policy Preview</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="refundPreviewContent"></div>
            </div>
        </div>
    </div>
</div>

{{-- Toasts --}}
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

/* --------- DOM --------- */
const loader = document.getElementById('refundLoader');
const refundTitle = document.getElementById('refundTitle');
const refundEditor = document.getElementById('refundEditor');
const refundCode = document.getElementById('refundCode');
const refundId = document.getElementById('refundId');
const refundMode = document.getElementById('refundMode');

const statusBadge = document.getElementById('refundStatus');
const lastUpdated = document.getElementById('refundLastUpdated');

const btnSave = document.getElementById('btnRefundSave');
const btnDelete = document.getElementById('btnRefundDelete');
const btnReset = document.getElementById('btnRefundReset');
const btnPreview = document.getElementById('btnRefundPreview');

/* -------- Helpers -------- */
function showLoader(x){ loader.style.display = x ? 'flex':'none'; }
function stripHTML(html){ return new DOMParser().parseFromString(html, "text/html").body.textContent; }

async function api(url,method='GET',body=null){
    const r = await fetch(url,{
        method,
        headers:{
            'Authorization':'Bearer '+TOKEN,
            'Content-Type':'application/json'
        },
        body: body ? JSON.stringify(body): null
    });
    return r.json();
}

/* -------- RTE Functionality -------- */
function execCommand(command, value = null) {
    document.execCommand(command, false, value);
    refundEditor.focus();
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

// Update toolbar state on selection change
refundEditor.addEventListener('mouseup', updateToolbarState);
refundEditor.addEventListener('keyup', updateToolbarState);
refundEditor.addEventListener('focus', updateToolbarState);

// Sync code editor on input
refundEditor.addEventListener('input', () => {
    refundCode.value = refundEditor.innerHTML;
});

/* -------- Load Existing -------- */
async function loadRefund(){
    showLoader(true);

    const r = await api('/api/refund-policy/check');

    if(r.exists){
        refundMode.value = "true";
        refundId.value = r.refund_policy.id;

        refundTitle.value = r.refund_policy.title;
        refundEditor.innerHTML = r.refund_policy.content;
        refundCode.value = r.refund_policy.content;

        statusBadge.style.display = 'inline-flex';
        statusBadge.className = 'status-badge published';
        statusBadge.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i> Published';

        lastUpdated.textContent = "Last updated: " + r.refund_policy.updated_at;

        btnDelete.style.display = 'inline-flex';
        btnPreview.style.display = 'inline-flex';
    }
    else{
        statusBadge.style.display = 'inline-flex';
        statusBadge.className = 'status-badge draft';
        lastUpdated.textContent = "Last updated: Never";
    }

    showLoader(false);
}

/* -------- Sync Editor Tabs -------- */
document.querySelectorAll('.editor-tab').forEach(tab=>{
    tab.addEventListener('click',()=>{
        document.querySelectorAll('.editor-tab').forEach(t=>t.classList.remove('active'));
        tab.classList.add('active');

        const x = tab.dataset.tab;
        document.querySelectorAll('.editor-content').forEach(c=>c.classList.remove('active'));
        document.querySelector(`.editor-content[data-tab="${x}"]`).classList.add('active');

        if(x === 'code'){ 
            refundCode.value = refundEditor.innerHTML; 
        }
        else{ 
            refundEditor.innerHTML = refundCode.value;
            updateToolbarState();
        }
    });
});

/* -------- Save Refund Policy -------- */
btnSave.onclick = async ()=>{
    if(!refundTitle.value.trim()) return alert("Title required.");
    if(stripHTML(refundEditor.innerHTML).trim()==='') return alert("Content required.");

    showLoader(true);

    const body = {
        title: refundTitle.value.trim(),
        content: refundEditor.innerHTML.trim()
    };

    const r = await api('/api/refund-policy','POST',body);

    showLoader(false);

    if(r.success){
        alert("Refund policy saved!");
        loadRefund();
    }
};

/* -------- Delete -------- */
btnDelete.onclick = async ()=>{
    if(!confirm("Delete refund policy?")) return;

    showLoader(true);
    await api('/api/refund-policy','DELETE');
    showLoader(false);

    location.reload();
};

/* -------- Preview -------- */
btnPreview.onclick = ()=>{
    document.getElementById('refundPreviewContent').innerHTML = refundEditor.innerHTML;
    new bootstrap.Modal('#refundPreviewModal').show();
};

/* -------- Reset -------- */
btnReset.onclick = ()=> location.reload();

/* -------- Init -------- */
loadRefund();

})();
</script>
@endpush
