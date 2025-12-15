@extends('pages.users.admin.layout.structure')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
<style>
:root{
    /* Brand */
    --primary-color:   #951eaa;    /* Vibrant violet / royal purple */
    --secondary-color: #5e1570;    /* Deeper supporting purple */
    --accent-color:    #c94ff0;    /* Soft neon-lavender accent */
  
    /* Neutrals */
    --bg-body:     #faf8fc;
    --surface:     #ffffff;
    --ink:         #1c1324;
    --text-color:  #352f3b;
    --muted-color: #7a6e85;
  
    /* Lines */
    --line-strong: #e4dfee;
    --line-soft:   #f3edf9;
  
    /* States */
    --info-color:    #6366f1;  /* Indigo blue tone for info */
    --success-color: #16a34a;
    --warning-color: #f59e0b;
    --danger-color:  #dc2626;
  
    /* Effects */
    --shadow-1: 0 1px 2px rgba(30,12,48,.06);
    --shadow-2: 0 10px 24px rgba(30,12,48,.08);
    --shadow-3: 0 18px 50px rgba(30,12,48,.12);
    --radius-0: 0px;
    --radius-1: 10px;
    --transition: all .18s ease;
    --ring: 0 0 0 .18rem rgba(149,30,170,.25);
  
    /* Type */
    --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Noto Sans', sans-serif;
    --font-head: 'Poppins', var(--font-sans);
    --fs-12: .75rem;
    --fs-13: .8125rem;
    --fs-14: .875rem;
    --fs-15: .9375rem;
    --fs-16: 1rem;
  
    /* Table density */
    --row-pad-y: 10px;
    --row-pad-x: 12px;
  
    /* Pagination tones */
    --page-bg: #fff;
    --page-hover: #f5f0fa;
    --page-disabled: #f8f3fc;
  
    /* Badge pastel tints */
    --t-success: rgba(22,163,74,.12);
    --t-danger:  rgba(220,38,38,.12);
    --t-info:    rgba(99,102,241,.12);
    --t-warn:    rgba(245,158,11,.14);
    --t-primary: rgba(149,30,170,.14);
  }
  
/* =========================================================
   PROFILE PAGE STYLING - THEME VARIABLE COMPLIANT
   ========================================================= */

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
                
                <div class="file-upload-container">
                    <form id="imgForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label d-block mb-3">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Upload New Photo
                            </label>
                            <input type="file" class="form-control editable" name="image" 
                                   accept="image/*">
                            <small class="text-muted mt-2 d-block">
                                Supported formats: JPG, PNG, GIF. Max size: 5MB
                            </small>
                        </div>
                        <button type="button" class="btn btn-dark edit-btn" id="btnUploadImg">
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
document.addEventListener("DOMContentLoaded", function() {
    loadProfile();
    
    // Add Font Awesome if not present
    if (!document.querySelector('link[href*="font-awesome"]')) {
        const faLink = document.createElement('link');
        faLink.rel = 'stylesheet';
        faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
        document.head.appendChild(faLink);
    }
    
    // Toggle password visibility
    document.getElementById('showPasswords')?.addEventListener('change', function(e) {
        const passwords = document.querySelectorAll('#passwordForm input[type="password"]');
        passwords.forEach(input => {
            input.type = e.target.checked ? 'text' : 'password';
        });
    });
});

// Get token from storage
function getToken() {
    return localStorage.getItem("token") || sessionStorage.getItem("token");
}

// Show message
function showMessage(elementId, message, type) {
    const element = document.getElementById(elementId);
    element.textContent = message;
    element.className = `status-message ${type}`;
    element.style.display = 'block';
    
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
}

// Load profile from backend API
function loadProfile() {
    const token = getToken();

    if (!token) {
        window.location.href = "/login";
        return;
    }

    // Show loading state
    document.getElementById('pf_name').innerHTML = 'Loading <span class="loading"></span>';
    
    fetch("/api/profile", {
        headers: { 
            Authorization: "Bearer " + token,
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (r.status === 401) {
            localStorage.removeItem("token");
            sessionStorage.removeItem("token");
            window.location.href = "/login";
            return;
        }
        return r.json();
    })
    .then(res => {
        if (!res || res.status !== "success") {
            throw new Error(res?.message || "Failed to load profile");
        }

        const user = res.user;
        const perm = res.permissions;
        const endpoint = res.endpoints;

        // Update header
        document.getElementById("pf_name").textContent = user.name || 'No Name';
        document.getElementById("pf_email").innerHTML = `
            <i class="fas fa-envelope me-2"></i>${user.email || 'No Email'}
        `;
        document.getElementById("pf_role").textContent = 
            (user.role_short_form || 'User') + ' • ' + 
            (user.role ? capitalize(user.role) : 'Role');
        document.getElementById("pf_img").src = user.image || "/default-avatar.png";

        // Fill form fields
        document.querySelector("input[name='name']").value = user.name || '';
        document.querySelector("input[name='email']").value = user.email || '';
        document.querySelector("input[name='phone_number']").value = user.phone_number || '';
        document.querySelector("input[name='address']").value = user.address || '';

        // Handle permissions
        if (!perm?.can_edit_profile) {
            document.querySelectorAll(".editable").forEach(i => i.disabled = true);
            document.querySelectorAll(".edit-btn").forEach(b => b.style.display = "none");
            showMessage('profileMessage', 'Your role does not allow editing profile information.', 'error');
        }

        // Store endpoints globally
        window.profileApi = endpoint;
    })
    .catch(error => {
        console.error('Profile load error:', error);
        document.getElementById("pf_name").textContent = "Error Loading Profile";
        showMessage('profileMessage', error.message, 'error');
    });
}

function capitalize(s) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1).toLowerCase() : '';
}

// -----------------------
// Update Profile
// -----------------------
document.getElementById("btnSaveProfile")?.addEventListener("click", function () {
    const token = getToken();
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    btn.disabled = true;
    
    let form = new FormData(document.getElementById("profileForm"));

    fetch(profileApi.update_profile, {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token,
            "X-HTTP-Method-Override": "PATCH"
        },
        body: form
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            showMessage('profileMessage', 'Profile updated successfully!', 'success');
            setTimeout(() => loadProfile(), 1000);
        } else {
            showMessage('profileMessage', res.message || 'Update failed', 'error');
        }
    })
    .catch(error => {
        showMessage('profileMessage', 'Network error. Please try again.', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

// -----------------------
// Upload Image
// -----------------------
document.getElementById("btnUploadImg")?.addEventListener("click", function () {
    const token = getToken();
    const btn = this;
    const originalText = btn.innerHTML;
    const fileInput = document.querySelector("input[name='image']");
    
    if (!fileInput.files.length) {
        showMessage('imageMessage', 'Please select an image first.', 'error');
        return;
    }
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    btn.disabled = true;
    
    let form = new FormData(document.getElementById("imgForm"));

    fetch(profileApi.update_image, {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token
        },
        body: form
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            showMessage('imageMessage', 'Profile picture updated successfully!', 'success');
            fileInput.value = '';
            setTimeout(() => loadProfile(), 1000);
        } else {
            showMessage('imageMessage', res.message || 'Upload failed', 'error');
        }
    })
    .catch(error => {
        showMessage('imageMessage', 'Upload failed. Please try again.', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

// -----------------------
// Change Password
// -----------------------
document.getElementById("btnChangePassword")?.addEventListener("click", function () {
    const token = getToken();
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    btn.disabled = true;
    
    let form = new FormData(document.getElementById("passwordForm"));

    fetch(profileApi.update_password, {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token,
            "X-HTTP-Method-Override": "PATCH"
        },
        body: form
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            showMessage('passwordMessage', 'Password changed successfully!', 'success');
            document.getElementById("passwordForm").reset();
            document.getElementById('showPasswords').checked = false;
        } else {
            showMessage('passwordMessage', res.message || "Failed to update password", 'error');
        }
    })
    .catch(error => {
        showMessage('passwordMessage', 'Network error. Please try again.', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
@endpush