{{-- resources/views/modules/about/manage.blade.php --}}
@section('title','Manage About Us')

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
  box-shadow: 0 0 0 .2rem rgba(110, 54, 158, 0.25);
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

/* ===== Image Upload Section ===== */
.image-upload-section {
    margin-bottom: 24px;
}

.image-upload-area {
    border: 2px dashed var(--line-strong);
    border-radius: 12px;
    padding: 32px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--surface);
    position: relative;
    overflow: hidden;
}

.image-upload-area:hover {
    border-color: var(--accent-color);
    background: color-mix(in oklab, var(--accent-color) 8%, transparent);
}

.image-upload-area.dragover {
    border-color: var(--accent-color);
    background: color-mix(in oklab, var(--accent-color) 12%, transparent);
    transform: scale(1.01);
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: var(--muted-color);
}

.upload-placeholder i {
    font-size: 48px;
    color: var(--accent-color);
    opacity: 0.7;
}

.upload-placeholder h5 {
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.upload-placeholder p {
    font-size: 13px;
    margin: 0;
    color: var(--muted);
}

.upload-preview {
    display: none;
    position: relative;
    max-width: 100%;
}

.upload-preview img {
    max-width: 100%;
    max-height: 400px;
    border-radius: 12px;
    object-fit: contain;
    box-shadow: var(--shadow-1);
}

.btn-remove-image {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.9);
    border: 2px solid white;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.btn-remove-image:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.image-info {
    margin-top: 12px;
    font-size: 12px;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}

.image-info i {
    color: var(--accent-color);
}

.image-input {
    display: none;
}

/* ===== Mission & Vision Section ===== */
.mission-vision-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.mission-card, .vision-card {
    border: 1px solid var(--line-soft);
    border-radius: 12px;
    padding: 20px;
    background: var(--surface);
}

.mission-card h5, .vision-card h5 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: var(--ink);
    font-weight: 600;
}

.mission-card h5 i {
    color: #3b82f6;
}

.vision-card h5 i {
    color: #8b5cf6;
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

/* ===== Contact Info Section ===== */
.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.contact-field {
    margin-bottom: 16px;
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

html.theme-dark .mission-card,
html.theme-dark .vision-card {
    background: #1e293b;
    border-color: #334155;
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
    
    .mission-vision-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .image-upload-area {
        padding: 20px;
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
                        <strong>About Us Page</strong>
                        <div class="terms-sub">Manage your platform's About Us page content</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                <div class="d-flex align-items-center gap-2">
                    <span id="aboutStatus" class="status-badge draft" style="display: none;">
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
            <form id="aboutForm">
                {{-- Title & Tagline Section --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-heading"></i>
                        Basic Information
                    </h3>
                    
                    <div class="mb-3">
                        <label class="form-label" for="aboutTitle">
                            Page Title <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="aboutTitle" 
                            class="form-control" 
                            maxlength="255" 
                            placeholder="e.g., About Our Company"
                            required
                        >
                        <div id="titleError" class="error-message"></div>
                    </div>

                    <div class="mb-3" style="display:none">
                        <label class="form-label" for="aboutTagline">
                            Tagline
                        </label>
                        <input 
                            type="text" 
                            id="aboutTagline" 
                            class="form-control" 
                            maxlength="255" 
                            placeholder="e.g., We're changing the world one step at a time"
                        >
                    </div>

                    <div class="info-box">
                        <i class="fa-solid fa-info-circle"></i>
                        <div>
                            <strong>Note:</strong> This content will be displayed on your public About Us page.
                            Make sure to include compelling information about your company.
                        </div>
                    </div>
                </div>

                {{-- Hero Image Section --}}
                <div class="form-section image-upload-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-image"></i>
                        Hero Image
                    </h3>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            Featured Image <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-3">Recommended: 1200x600px JPG or PNG (max 5MB)</p>
                        
                        <div class="image-upload-area" id="dropzone">
                            <input type="file" id="imageInput" class="image-input" accept="image/*">
                            
                            <div class="upload-placeholder" id="dzPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <h5>Drag & drop your image here</h5>
                                <p>or click to browse files</p>
                                <span class="text-muted small">Supports JPG, PNG, GIF up to 5MB</span>
                            </div>
                            
                            <div class="upload-preview" id="dzPreview">
                                <img id="previewImg" alt="Preview">
                                <button type="button" class="btn-remove-image" id="removeImage">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="image-info">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>This image will be displayed at the top of your About Us page</span>
                        </div>
                        
                        <div id="imageError" class="error-message"></div>
                    </div>
                </div>

                {{-- Main Content Section --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-file-lines"></i>
                        Main Content
                    </h3>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            Detailed Content <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-3">Tell your story, share your values, and connect with visitors</p>
                        
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
                                    data-placeholder="Write your company story, mission, and values here..."
                                ></div>
                            </div>
                            
                            {{-- HTML Editor --}}
                            <div class="editor-content" data-tab="code">
                                <textarea 
                                    id="contentCode" 
                                    class="code-editor" 
                                    placeholder="&lt;h1&gt;Our Story&lt;/h1&gt;&#10;&lt;p&gt;Share your journey and what makes you unique...&lt;/p&gt;"
                                ></textarea>
                            </div>
                        </div>
                        
                        <div id="contentError" class="error-message"></div>
                    </div>
                </div>

                {{-- Mission & Vision Section --}}
                <div class="form-section" style="display:none">
                    <h3 class="section-title">
                        <i class="fa-solid fa-bullseye"></i>
                        Mission & Vision
                    </h3>
                    
                    <div class="mission-vision-grid">
                        <div class="mission-card">
                            <h5><i class="fa-solid fa-flag"></i> Our Mission</h5>
                            <textarea 
                                id="missionText" 
                                class="form-control" 
                                rows="5" 
                                placeholder="What is your company's purpose? What do you aim to achieve?"
                            ></textarea>
                        </div>
                        
                        <div class="vision-card">
                            <h5><i class="fa-solid fa-eye"></i> Our Vision</h5>
                            <textarea 
                                id="visionText" 
                                class="form-control" 
                                rows="5" 
                                placeholder="Where do you see your company in the future? What is your ultimate goal?"
                            ></textarea>
                        </div>
                    </div>
                </div>

                {{-- Contact Information --}}
                <div class="form-section" style="display:none">
                    <h3 class="section-title">
                        <i class="fa-solid fa-address-book"></i>
                        Contact Information
                    </h3>
                    
                    <div class="contact-grid">
                        <div class="contact-field">
                            <label class="form-label" for="contactEmail">
                                <i class="fa-solid fa-envelope"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                id="contactEmail" 
                                class="form-control" 
                                placeholder="contact@company.com"
                            >
                        </div>
                        
                        <div class="contact-field">
                            <label class="form-label" for="contactPhone">
                                <i class="fa-solid fa-phone"></i> Phone Number
                            </label>
                            <input 
                                type="tel" 
                                id="contactPhone" 
                                class="form-control" 
                                placeholder="+1 (555) 123-4567"
                            >
                        </div>
                        
                        <div class="contact-field">
                            <label class="form-label" for="contactAddress">
                                <i class="fa-solid fa-location-dot"></i> Office Address
                            </label>
                            <textarea 
                                id="contactAddress" 
                                class="form-control" 
                                rows="3" 
                                placeholder="123 Main Street, City, Country"
                            ></textarea>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="aboutId">
                <input type="hidden" id="isEditMode" value="false">
                <input type="hidden" id="existingImage" value="">
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
  Save About Us
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
                <h5 class="modal-title" id="previewTitle">About Us Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewImage" class="text-center mb-4">
                    <img id="previewModalImg" class="img-fluid rounded" style="max-height: 300px;" alt="About Us">
                </div>
                <div id="previewContent"></div>
                <div id="previewMissionVision" class="mt-4"></div>
                <div id="previewContact" class="mt-4"></div>
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

    /* ===== Toasts ===== */
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
    const aboutTitle = document.getElementById('aboutTitle');
    const aboutTagline = document.getElementById('aboutTagline');
    const contentEditor = document.getElementById('contentEditor');
    const contentCode = document.getElementById('contentCode');
    const aboutId = document.getElementById('aboutId');
    const isEditMode = document.getElementById('isEditMode');
    const btnSave = document.getElementById('btnSave');
    const btnReset = document.getElementById('btnReset');
    const btnDelete = document.getElementById('btnDelete');
    const btnPreview = document.getElementById('btnPreview');
    const aboutStatus = document.getElementById('aboutStatus');
    const lastUpdated = document.getElementById('lastUpdated');
    const missionText = document.getElementById('missionText');
    const visionText = document.getElementById('visionText');
    const contactEmail = document.getElementById('contactEmail');
    const contactPhone = document.getElementById('contactPhone');
    const contactAddress = document.getElementById('contactAddress');
    const existingImage = document.getElementById('existingImage');
    
    // Image upload elements
    const dropzone = document.getElementById('dropzone');
    const imageInput = document.getElementById('imageInput');
    const dzPlaceholder = document.getElementById('dzPlaceholder');
    const dzPreview = document.getElementById('dzPreview');
    const previewImg = document.getElementById('previewImg');
    const removeImage = document.getElementById('removeImage');
    
    // Preview Modal
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const previewTitle = document.getElementById('previewTitle');
    const previewContent = document.getElementById('previewContent');
    const previewImage = document.getElementById('previewImage');
    const previewModalImg = document.getElementById('previewModalImg');
    const previewMissionVision = document.getElementById('previewMissionVision');
    const previewContact = document.getElementById('previewContact');
    
    /* ===== State ===== */
    let currentEditor = null;
    let currentSelection = null;
    let aboutData = null;
    let imageFile = null;
    
    /* ===== Helper Functions ===== */
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
    
    /* ===== API Helper ===== */
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
    
    /* ===== Image Upload Functions ===== */
    function setupImageUpload() {
        // Click to upload
        dropzone.addEventListener('click', (e) => {
            if (e.target !== removeImage) {
                imageInput.click();
            }
        });
        
        // File selection
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            handleImageFile(file);
        });
        
        // Drag and drop
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });
        
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            handleImageFile(file);
        });
        
        // Remove image
        removeImage.addEventListener('click', (e) => {
            e.stopPropagation();
            removeSelectedImage();
        });
    }
    
    function handleImageFile(file) {
        if (!file || !file.type.startsWith('image/')) {
            showToast('error', 'Please select a valid image file');
            return;
        }
        
        // Check file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            showToast('error', 'Image size should be less than 5MB');
            return;
        }
        
        imageFile = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            dzPreview.style.display = 'block';
            dzPlaceholder.style.display = 'none';
            showToast('success', 'Image selected successfully');
        };
        reader.readAsDataURL(file);
    }
    
    function removeSelectedImage() {
        imageFile = null;
        previewImg.src = '';
        dzPreview.style.display = 'none';
        dzPlaceholder.style.display = 'block';
        imageInput.value = '';
        showToast('warning', 'Image removed');
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
    
    /* ===== Load Existing About Us ===== */
    async function loadExistingAbout() {
        showLoader(true);
        
        try {
            const response = await apiFetch('/api/about-us/check');
            
            if (response.ok && response.data) {
                const data = response.data;
                
                if (data.exists && data.about) {
                    // Edit mode - populate form
                    isEditMode.value = 'true';
                    aboutData = data.about;
                    
                    // Update UI
                    aboutStatus.style.display = 'inline-flex';
                    aboutStatus.className = 'status-badge published';
                    aboutStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Published</span>';
                    
                    // Format date
                    lastUpdated.textContent = 'Last updated: ' + formatDate(aboutData.updated_at);
                    
                    // Populate form fields
                    aboutTitle.value = aboutData.title || '';
                    aboutTagline.value = aboutData.tagline || '';
                    contentEditor.innerHTML = aboutData.content || '';
                    contentCode.value = aboutData.content || '';
                    missionText.value = aboutData.mission || '';
                    visionText.value = aboutData.vision || '';
                    contactEmail.value = aboutData.email || '';
                    contactPhone.value = aboutData.phone || '';
                    contactAddress.value = aboutData.address || '';
                    aboutId.value = aboutData.id || '';
                    existingImage.value = aboutData.image || '';
                    
                    // Handle image preview
                    if (aboutData.image) {
                        previewImg.src = aboutData.image;
                        dzPreview.style.display = 'block';
                        dzPlaceholder.style.display = 'none';
                    }
                    
                    // Show delete and preview buttons
                    btnDelete.style.display = 'inline-flex';
                    btnPreview.style.display = 'inline-flex';
                    
                    showToast('success', 'About Us content loaded successfully');
                } else {
                    // Create mode
                    isEditMode.value = 'false';
                    aboutStatus.style.display = 'inline-flex';
                    aboutStatus.className = 'status-badge draft';
                    aboutStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Draft</span>';
                    lastUpdated.textContent = 'Last updated: Never';
                    
                    showToast('warning', 'No About Us content found. Ready to create new content.');
                }
            } else {
                handleApiError('Failed to load About Us', response);
            }
        } catch (error) {
            console.error('Error loading About Us:', error);
            showToast('error', 'Failed to load About Us content. Please try again.');
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
        if (!aboutTitle.value.trim()) {
            const errorEl = document.getElementById('titleError');
            errorEl.textContent = 'Title is required';
            errorEl.classList.add('show');
            aboutTitle.classList.add('error');
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
        
        // Validate image (only for new creation if no existing image)
        if (!imageFile && !existingImage.value && isEditMode.value === 'false') {
            const errorEl = document.getElementById('imageError');
            errorEl.textContent = 'Image is required';
            errorEl.classList.add('show');
            dropzone.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            dropzone.style.borderColor = '';
        }
        
        return isValid;
    }
    
    /* ===== Save About Us ===== */
    async function saveAboutUs() {
        if (!validateForm()) {
            return;
        }
        
        showLoader(true);
        btnSave.disabled = true;
        const originalText = btnSave.innerHTML;
        btnSave.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        
        try {
            const formData = new FormData();
            formData.append('title', aboutTitle.value.trim());
            formData.append('tagline', aboutTagline.value.trim());
            formData.append('content', contentEditor.innerHTML.trim());
            formData.append('mission', missionText.value.trim());
            formData.append('vision', visionText.value.trim());
            formData.append('email', contactEmail.value.trim());
            formData.append('phone', contactPhone.value.trim());
            formData.append('address', contactAddress.value.trim());
            
            if (imageFile) {
                formData.append('image', imageFile);
            }
            
            if (isEditMode.value === 'true' && aboutId.value) {
                formData.append('_method', 'PUT');
                formData.append('id', aboutId.value);
            }
            
            const url = '/api/about-us';
            const method = isEditMode.value === 'true' ? 'POST' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': 'Bearer ' + TOKEN,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok) {
                showToast('success', isEditMode.value === 'true' ? 
                    'About Us updated successfully!' : 'About Us created successfully!');
                
                // Update UI for edit mode
                if (isEditMode.value === 'false') {
                    isEditMode.value = 'true';
                    aboutStatus.className = 'status-badge published';
                    aboutStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Published</span>';
                    
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
                    
                    if (result && result.about) {
                        aboutId.value = result.about.id || '';
                        existingImage.value = result.about.image || '';
                    }
                } else {
                    // Refresh updated date
                    lastUpdated.textContent = 'Last updated: ' + formatDate(new Date().toISOString());
                    
                    // Update existing image reference if new image was uploaded
                    if (result && result.about && result.about.image) {
                        existingImage.value = result.about.image;
                        previewImg.src = result.about.image;
                    }
                }
                
                // Clear file input
                imageFile = null;
                imageInput.value = '';
            } else {
                handleApiError('Failed to save About Us', {
                    ok: response.ok,
                    status: response.status,
                    data: result
                });
            }
        } catch (error) {
            console.error('Error saving About Us:', error);
            showToast('error', 'Failed to save About Us. Please try again.');
        } finally {
            showLoader(false);
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        }
    }
    
    /* ===== Delete About Us ===== */
    async function deleteAboutUs() {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Delete About Us Content?',
            text: 'This will permanently delete the About Us page content. Are you sure?',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        });
        
        if (!result.isConfirmed) return;
        
        showLoader(true);
        
        try {
            const response = await apiFetch('/api/about-us', {
                method: 'DELETE'
            });
            
            if (response.ok) {
                showToast('success', 'About Us content deleted successfully!');
                
                // Reset form to create mode
                resetForm();
                isEditMode.value = 'false';
                aboutStatus.className = 'status-badge draft';
                aboutStatus.innerHTML = '<i class="fa-solid fa-circle fa-xs"></i><span>Draft</span>';
                lastUpdated.textContent = 'Last updated: Never';
                btnDelete.style.display = 'none';
                btnPreview.style.display = 'none';
                existingImage.value = '';
                
                setTimeout(() => {
                    loadExistingAbout();
                }, 1000);
            } else {
                handleApiError('Failed to delete About Us', response);
            }
        } catch (error) {
            console.error('Error deleting About Us:', error);
            showToast('error', 'Failed to delete About Us. Please try again.');
        } finally {
            showLoader(false);
        }
    }
    
    /* ===== Reset Form ===== */
    function resetForm() {
        if (isEditMode.value === 'true' && aboutData) {
            // Reset to original data
            aboutTitle.value = aboutData.title || '';
            aboutTagline.value = aboutData.tagline || '';
            contentEditor.innerHTML = aboutData.content || '';
            contentCode.value = aboutData.content || '';
            missionText.value = aboutData.mission || '';
            visionText.value = aboutData.vision || '';
            contactEmail.value = aboutData.email || '';
            contactPhone.value = aboutData.phone || '';
            contactAddress.value = aboutData.address || '';
            
            // Reset image
            if (aboutData.image) {
                previewImg.src = aboutData.image;
                dzPreview.style.display = 'block';
                dzPlaceholder.style.display = 'none';
                existingImage.value = aboutData.image;
            } else {
                removeSelectedImage();
            }
            
            imageFile = null;
            imageInput.value = '';
            
            showToast('warning', 'Form reset to original values');
        } else {
            // Clear form for create mode
            aboutTitle.value = '';
            aboutTagline.value = '';
            contentEditor.innerHTML = '';
            contentCode.value = '';
            missionText.value = '';
            visionText.value = '';
            contactEmail.value = '';
            contactPhone.value = '';
            contactAddress.value = '';
            removeSelectedImage();
            existingImage.value = '';
            
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
        dropzone.style.borderColor = '';
    }
    
    /* ===== Preview About Us ===== */
    function previewAboutUs() {
        const title = aboutTitle.value.trim() || 'About Us';
        const tagline = aboutTagline.value.trim();
        const content = contentEditor.innerHTML.trim();
        const mission = missionText.value.trim();
        const vision = visionText.value.trim();
        const email = contactEmail.value.trim();
        const phone = contactPhone.value.trim();
        const address = contactAddress.value.trim();
        const imageSrc = imageFile ? previewImg.src : (existingImage.value || '');
        
        if (!content) {
            showToast('error', 'No content to preview');
            return;
        }
        
        // Set preview title
        previewTitle.textContent = title;
        
        // Set image preview
        if (imageSrc) {
    previewImage.style.display = 'block';

    // Force repaint (important)
    previewModalImg.src = '';
    setTimeout(() => {
        previewModalImg.src = imageSrc;
    }, 0);
} else {
    previewImage.style.display = 'none';
}

        // Set content preview
        let previewHTML = '';
        
        if (tagline) {
            previewHTML += `<div class="lead mb-4">${esc(tagline)}</div>`;
        }
        
        previewHTML += `<div class="content">${content}</div>`;
        
        previewContent.innerHTML = previewHTML;
        
        // Set mission & vision preview
        let missionVisionHTML = '';
        if (mission || vision) {
            missionVisionHTML = `<div class="row g-4">`;
            
            if (mission) {
                missionVisionHTML += `
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa-solid fa-flag me-2"></i>Our Mission</h5>
                            <p class="card-text">${esc(mission)}</p>
                        </div>
                    </div>
                </div>
                `;
            }
            
            if (vision) {
                missionVisionHTML += `
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa-solid fa-eye me-2"></i>Our Vision</h5>
                            <p class="card-text">${esc(vision)}</p>
                        </div>
                    </div>
                </div>
                `;
            }
            
            missionVisionHTML += `</div>`;
        }
        
        previewMissionVision.innerHTML = missionVisionHTML;
        
        // Set contact preview
        let contactHTML = '';
        if (email || phone || address) {
            contactHTML = `<div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-address-book me-2"></i>Contact Information</h5>
                    <div class="row g-3">`;
            
            if (email) {
                contactHTML += `
                <div class="col-md-6">
                    <p class="mb-1"><i class="fa-solid fa-envelope me-2"></i>Email</p>
                    <p class="text-muted">${esc(email)}</p>
                </div>
                `;
            }
            
            if (phone) {
                contactHTML += `
                <div class="col-md-6">
                    <p class="mb-1"><i class="fa-solid fa-phone me-2"></i>Phone</p>
                    <p class="text-muted">${esc(phone)}</p>
                </div>
                `;
            }
            
            if (address) {
                contactHTML += `
                <div class="col-12">
                    <p class="mb-1"><i class="fa-solid fa-location-dot me-2"></i>Address</p>
                    <p class="text-muted">${esc(address.replace(/\n/g, '<br>'))}</p>
                </div>
                `;
            }
            
            contactHTML += `</div></div></div>`;
        }
        
        previewContact.innerHTML = contactHTML;
        
        // Show modal
        previewModal.show();
    }
    
    /* ===== Event Listeners ===== */
    btnSave.addEventListener('click', saveAboutUs);
    btnReset.addEventListener('click', resetForm);
    btnDelete.addEventListener('click', deleteAboutUs);
    btnPreview.addEventListener('click', previewAboutUs);
    
    // Add placeholder text to editor
    contentEditor.addEventListener('focus', function() {
        if (!this.innerHTML.trim()) {
            this.innerHTML = '<p>Start writing your About Us content here...</p>';
        }
    });
    
    contentEditor.addEventListener('blur', function() {
        if (this.innerHTML === '<p>Start writing your About Us content here...</p>') {
            this.innerHTML = '';
        }
    });
    
    /* ===== Initialize ===== */
    // Initialize image upload
    setupImageUpload();
    
    // Initialize editor
    const editorWrapper = document.querySelector('.editor-wrapper');
    if (editorWrapper) {
        setupEditorTabs(editorWrapper);
        setupEditorToolbar(editorWrapper);
    }
    
    // Load existing about us on page load
    loadExistingAbout();
})();
</script>
@endpush