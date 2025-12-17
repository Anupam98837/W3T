@extends('pages.users.student.layout.structure')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
<style>


.profile-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-card {
    background: var(--surface);
    border-radius: var(--radius-1);
    overflow: hidden;
    box-shadow: var(--shadow-2);
    border: 1px solid var(--line-strong);
}

/* =========================================================
   HEADER SECTION
   ========================================================= */

.profile-header {
  background: linear-gradient(
    135deg,
    color-mix(in srgb, var(--primary-color) 35%, white) 0%,
    color-mix(in srgb, var(--accent-color) 35%, white) 100%
);


    padding: 3rem 2.5rem 2rem;
    color: white;
    position: relative;
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 1.5rem;
}

/* Avatar Wrapper & Circular Cropped Image */
.avatar-wrapper {
    width: 130px;
    height: 130px;
}

.avatar-container {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid rgba(255,255,255,0.35);
    box-shadow: var(--shadow-3);
    position: relative;
}

.avatar-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.65);
    padding: 8px;
    font-size: .8rem;
    text-align: center;
    color: white;
    opacity: 0;
    transition: var(--transition);
}

.avatar-container:hover .avatar-overlay {
    opacity: 1;
}

/* Profile Info */
.profile-info h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: .5rem;
}

.profile-info .role-badge {
    background: rgba(255,255,255,.25);
    padding: .4rem 1rem;
    border-radius: 20px;
    font-size: .9rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.3);
    margin-bottom: .5rem;
}

.profile-info .email {
    opacity: .9;
    font-size: 1rem;
    color: white;
}

/* =========================================================
   TABS
   ========================================================= */

.profile-tabs {
    background: var(--surface);
    padding: 0 2.5rem;
    border-bottom: 1px solid var(--line-strong);
}

.nav-tabs {
    border: none;
    gap: 1rem;
}

.nav-tabs .nav-link {
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: var(--muted-color);
    background: transparent;
    border-radius: 8px 8px 0 0;
    transition: var(--transition);
}

.nav-tabs .nav-link:hover {
    color: var(--primary-color);
    background: var(--line-soft);
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    background: var(--surface);
    border-bottom: 3px solid var(--primary-color);
}

/* =========================================================
   TAB CONTENT
   ========================================================= */

.tab-content {
    padding: 2.5rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--secondary-color);
    padding-bottom: .75rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--line-soft);
    position: relative;
}

.section-title::after {
    content: "";
    position: absolute;
    bottom: -2px;
    left: 0;
    height: 2px;
    width: 60px;
    background: var(--primary-color);
}

/* =========================================================
   FORMS
   ========================================================= */

.form-label {
    font-weight: 500;
    margin-bottom: .4rem;
    color: var(--ink);
}

.form-control {
    border: 2px solid var(--line-strong);
    border-radius: var(--radius-1);
    padding: .75rem 1rem;
    font-size: .95rem;
    background: var(--surface);
    transition: var(--transition);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: var(--ring);
}

.form-control:disabled {
    background: var(--line-soft);
    color: var(--muted-color);
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

/* =========================================================
   BUTTONS
   ========================================================= */

.btn {
    padding: .75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: .95rem;
    transition: var(--transition);
    border: none;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-color);
    transform: translateY(-2px);
}

.btn-dark {
    background: var(--secondary-color);
    color: white;
}

.btn-dark:hover {
    background: #3b0f47;
    transform: translateY(-2px);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
}

/* =========================================================
   FILE UPLOAD
   ========================================================= */

.file-upload-container {
    background: var(--line-soft);
    border: 2px dashed var(--line-strong);
    border-radius: var(--radius-1);
    padding: 2rem;
    text-align: center;
    margin-bottom: 1.5rem;
    transition: var(--transition);
}

.file-upload-container:hover {
    border-color: var(--primary-color);
    background: rgba(149,30,170,.05);
}

/* =========================================================
   STATUS MESSAGES
   ========================================================= */

.status-message {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: none;
}

.status-message.success {
    display: block;
    background: var(--t-success);
    color: var(--success-color);
}

.status-message.error {
    display: block;
    background: var(--t-danger);
    color: var(--danger-color);
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 768px) {

    .profile-header {
        padding: 2rem 1.5rem;
    }

    .profile-avatar-section {
        flex-direction: column;
        text-align: center;
        gap: 1.2rem;
    }

    .avatar-wrapper,
    .avatar-container {
        width: 120px;
        height: 120px;
    }

    .profile-info h1 {
        font-size: 1.6rem;
    }

    .profile-tabs,
    .tab-content {
        padding: 1.5rem;
    }

    .nav-tabs .nav-link {
        font-size: .9rem;
        padding: .75rem 1rem;
    }
}
/* ===============================
   DROPZONE UPLOAD
   =============================== */

.dropzone {
    cursor: pointer;
    position: relative;
}

.dropzone.dragover {
    background: rgba(149,30,170,.08);
    border-color: var(--primary-color);
}

.dz-content {
    pointer-events: none;
}

.dz-icon {
    font-size: 2.2rem;
    color: var(--primary-color);
    margin-bottom: .75rem;
}

.dz-text {
    font-weight: 500;
    margin-bottom: .25rem;
}

.dz-link {
    color: var(--primary-color);
    text-decoration: underline;
}

.dz-preview {
    margin-top: 1rem;
}

.dz-preview img {
    max-width: 140px;
    max-height: 140px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: var(--shadow-2);
}

</style>
@endpush

@section('content')
<div class="profile-container">
    <div class="profile-card">
        
        {{-- Enhanced Header --}}
        <div class="profile-header">
            <div class="profile-avatar-section">
                <div class="avatar-wrapper">
                    <div class="avatar-container">
                        <img id="pf_img" src="/default-avatar.png" alt="Profile Avatar">
                        <div class="avatar-overlay">Change Photo</div>
                    </div>
                </div>
                <div class="profile-info">
                    <h1 id="pf_name">Loading...</h1>
                    <span class="role-badge" id="pf_role">Role</span>
                    <p class="email" id="pf_email">
                        <i class="fas fa-envelope me-2"></i>Email
                    </p>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="profile-tabs">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#manageTab">
                        <i class="fas fa-user-edit me-2"></i>Manage Profile
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#securityTab">
                        <i class="fas fa-shield-alt me-2"></i>Security
                    </button>
                </li>
            </ul>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content">
            
            {{-- Tab 1: Manage Profile --}}
            <div class="tab-pane fade show active" id="manageTab">
                <h4 class="section-title">
                    <i class="fas fa-id-card me-2"></i>Profile Details
                </h4>
                
                <div id="profileMessage" class="status-message"></div>
                
                <form id="profileForm" class="form-grid">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control editable" name="name" 
                               placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control editable" name="email" 
                               placeholder="Enter your email">
                        <small class="text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control editable" name="phone_number" 
                               placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control editable" name="address" 
                               placeholder="Enter your address">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <button type="button" class="btn btn-primary edit-btn" id="btnSaveProfile">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>

                <hr class="my-4">
                
                <h4 class="section-title">
                    <i class="fas fa-camera me-2"></i>Profile Picture
                </h4>
                
                <div id="imageMessage" class="status-message"></div>
                
                <div class="file-upload-container dropzone" id="dropzone">
    <form id="imgForm" enctype="multipart/form-data">
        @csrf

        <input type="file" name="image" id="imageInput" accept="image/*" hidden>

        <div class="dz-content">
            <i class="fas fa-cloud-upload-alt dz-icon"></i>
            <p class="dz-text">
                <strong>Drag & drop</strong> your image here<br>
                or <span class="dz-link">browse</span>
            </p>
            <small class="text-muted">
                JPG, PNG, GIF • Max 5MB
            </small>
        </div>

        <div id="previewBox" class="dz-preview" style="display:none;">
            <img id="previewImg" src="" alt="Preview">
        </div>

        <button type="button" class="btn btn-dark edit-btn mt-3" id="btnUploadImg">
            <i class="fas fa-upload me-2"></i>Upload Image
        </button>
    </form>
</div>

            </div>

            {{-- Tab 2: Security --}}
            <div class="tab-pane fade" id="securityTab">
                <h4 class="section-title">
                    <i class="fas fa-lock me-2"></i>Change Password
                </h4>
                
                <div id="passwordMessage" class="status-message"></div>
                
                <form id="passwordForm" class="form-grid">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control editable" 
                               name="current_password" placeholder="Enter current password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control editable" 
                               name="password" placeholder="Enter new password">
                        <small class="text-muted">Minimum 8 characters with letters and numbers</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control editable" 
                               name="password_confirmation" placeholder="Confirm new password">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="showPasswords">
                            <label class="form-check-label" for="showPasswords">
                                Show passwords
                            </label>
                        </div>
                        
                        <button type="button" class="btn btn-danger edit-btn" id="btnChangePassword">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                    </div>
                </form>
                
                <div class="mt-5 pt-4 border-top">
                    <h5 class="fw-bold mb-3">Security Tips</h5>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Use a strong, unique password
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Change your password regularly
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Never share your password with anyone
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
/* ===============================
   DOM READY
   =============================== */
document.addEventListener("DOMContentLoaded", function () {
    loadProfile();

    // Add Font Awesome if missing
    if (!document.querySelector('link[href*="font-awesome"]')) {
        const faLink = document.createElement('link');
        faLink.rel = 'stylesheet';
        faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
        document.head.appendChild(faLink);
    }

    // Toggle password visibility
    document.getElementById('showPasswords')?.addEventListener('change', function (e) {
        document
            .querySelectorAll('#passwordForm input[type="password"]')
            .forEach(i => i.type = e.target.checked ? 'text' : 'password');
    });

    initDropzone();
});

/* ===============================
   HELPERS
   =============================== */
function getToken() {
    return localStorage.getItem("token") || sessionStorage.getItem("token");
}

function capitalize(s) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1).toLowerCase() : '';
}

function showMessage(id, msg, type) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.className = `status-message ${type}`;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 5000);
}

/* ===============================
   LOAD PROFILE
   =============================== */
function loadProfile() {
    const token = getToken();
    if (!token) return location.href = "/login";

    document.getElementById('pf_name').innerHTML = 'Loading...';

    fetch("/api/profile", {
        headers: {
            Authorization: "Bearer " + token,
            Accept: "application/json"
        }
    })
    .then(r => {
        if (r.status === 401) {
            localStorage.clear();
            sessionStorage.clear();
            location.href = "/login";
            return;
        }
        return r.json();
    })
    .then(res => {
        if (res.status !== "success") throw new Error(res.message);

        const { user, permissions, endpoints } = res;

        // Header
        pf_name.textContent  = user.name || 'No Name';
        pf_email.innerHTML   = `<i class="fas fa-envelope me-2"></i>${user.email || 'No Email'}`;
        pf_role.textContent  = `${user.role_short_form || 'User'} • ${capitalize(user.role)}`;
        pf_img.src           = user.image || '/default-avatar.png';

        // Form fields
        document.querySelector("[name=name]").value = user.name || '';
        document.querySelector("[name=email]").value = user.email || '';
        document.querySelector("[name=phone_number]").value = user.phone_number || '';
        document.querySelector("[name=address]").value = user.address || '';

        // Permissions
        if (!permissions?.can_edit_profile) {
            document.querySelectorAll('.editable').forEach(i => i.disabled = true);
            document.querySelectorAll('.edit-btn').forEach(b => b.remove());
            showMessage('profileMessage', 'You do not have permission to edit profile.', 'error');
        }

        window.profileApi = endpoints;
    })
    .catch(err => {
        pf_name.textContent = "Error";
        showMessage('profileMessage', err.message, 'error');
    });
}

/* ===============================
   UPDATE PROFILE
   =============================== */
btnSaveProfile?.addEventListener("click", function () {
    const token = getToken();
    const btn = this;
    const original = btn.innerHTML;

    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Saving...`;
    btn.disabled = true;

    fetch(profileApi.update_profile, {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token,
            "X-HTTP-Method-Override": "PATCH"
        },
        body: new FormData(profileForm)
    })
    .then(r => r.json())
    .then(res => {
        res.status === "success"
            ? (showMessage('profileMessage', 'Profile updated!', 'success'), loadProfile())
            : showMessage('profileMessage', res.message, 'error');
    })
    .finally(() => {
        btn.innerHTML = original;
        btn.disabled = false;
    });
});

/* ===============================
   DRAG & DROP IMAGE UPLOAD
   =============================== */
function initDropzone() {
    const dropzone   = document.getElementById('dropzone');
    const input      = document.getElementById('imageInput');
    const previewBox = document.getElementById('previewBox');
    const previewImg = document.getElementById('previewImg');

    if (!dropzone || !input) return;

    dropzone.addEventListener('click', () => input.click());

    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () =>
        dropzone.classList.remove('dragover')
    );

    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            preview(input.files[0]);
        }
    });

    input.addEventListener('change', e => {
        if (e.target.files.length) preview(e.target.files[0]);
    });

    function preview(file) {
        if (!file.type.startsWith('image/')) return;
        const r = new FileReader();
        r.onload = e => {
            previewImg.src = e.target.result;
            previewBox.style.display = 'block';
        };
        r.readAsDataURL(file);
    }
}

/* ===============================
   UPLOAD IMAGE
   =============================== */
btnUploadImg?.addEventListener("click", function () {
    const token = getToken();
    const btn = this;
    const original = btn.innerHTML;
    const input = document.getElementById('imageInput');

    if (!input.files.length)
        return showMessage('imageMessage', 'Select an image first.', 'error');

    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Uploading...`;
    btn.disabled = true;

    fetch(profileApi.update_image, {
        method: "POST",
        headers: { Authorization: "Bearer " + token },
        body: new FormData(imgForm)
    })
    .then(r => r.json())
    .then(res => {
        res.status === "success"
            ? (showMessage('imageMessage', 'Image updated!', 'success'), loadProfile())
            : showMessage('imageMessage', res.message, 'error');
    })
    .finally(() => {
        btn.innerHTML = original;
        btn.disabled = false;
        input.value = '';
    });
});

/* ===============================
   CHANGE PASSWORD
   =============================== */
btnChangePassword?.addEventListener("click", function () {
    const token = getToken();
    const btn = this;
    const original = btn.innerHTML;

    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Updating...`;
    btn.disabled = true;

    fetch(profileApi.update_password, {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token,
            "X-HTTP-Method-Override": "PATCH"
        },
        body: new FormData(passwordForm)
    })
    .then(r => r.json())
    .then(res => {
        res.status === "success"
            ? (showMessage('passwordMessage', 'Password updated!', 'success'), passwordForm.reset())
            : showMessage('passwordMessage', res.message, 'error');
    })
    .finally(() => {
        btn.innerHTML = original;
        btn.disabled = false;
        showPasswords.checked = false;
    });
});
</script>
@endpush
