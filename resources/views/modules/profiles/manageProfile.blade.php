@push('styles')
<style>
.profile-card {
    max-width: 960px;
    margin: 2rem auto;
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}

.nav-tabs .nav-link {
    font-weight: 600;
    padding: 10px 18px;
}

.profile-header img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary);
}
</style>
@endpush


@section('content')
<div class="profile-card">

    {{-- HEADER --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <img src="{{ $user->image ?? '/default-avatar.png' }}" alt="Avatar">

        <div>
            <h3 class="fw-bold mb-1">{{ $user->name }}</h3>
            <span class="badge bg-primary">{{ strtoupper($user->role_short_form) }} • {{ ucfirst($user->role) }}</span>
            <p class="text-muted mb-0">{{ $user->email }}</p>
        </div>
    </div>

    {{-- TABS --}}
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#manageTab">
                Manage Profile
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#securityTab">
                Security
            </button>
        </li>
    </ul>

    <div class="tab-content mt-4">

        <!-- ===========================
             TAB 1 — MANAGE PROFILE
        ============================ -->
        <div class="tab-pane fade show active" id="manageTab">

            <h5 class="fw-bold mb-3">Profile Details</h5>

            <form id="profileForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name</label>
                        <input type="text" class="form-control"
                               name="name" value="{{ $user->name }}"
                               @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control"
                               name="email" value="{{ $user->email }}"
                               @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone</label>
                        <input type="text" class="form-control"
                               name="phone_number" value="{{ $user->phone_number }}"
                               @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Address</label>
                        <input type="text" class="form-control"
                               name="address" value="{{ $user->address }}"
                               @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />
                    </div>
                </div>

                @if(in_array($user->role, ['admin','super_admin']))
                <button type="button" onclick="updateProfile()" class="btn btn-primary">
                    Save Changes
                </button>
                @endif
            </form>

            <hr class="my-4">

            <h5 class="fw-bold">Profile Picture</h5>

            <form id="imgForm" enctype="multipart/form-data">
                @csrf

                <input type="file" class="form-control mb-3" name="image"
                       @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />

                @if(in_array($user->role, ['admin','super_admin']))
                <button type="button" onclick="updateImage()" class="btn btn-dark">
                    Upload
                </button>
                @endif
            </form>

        </div>


        <!-- ===========================
             TAB 2 — SECURITY
        ============================ -->
        <div class="tab-pane fade" id="securityTab">

            <h5 class="fw-bold mb-3">Change Password</h5>

            <form id="passwordForm">
                @csrf

                <div class="mb-3">
                    <label>Current Password</label>
                    <input type="password" class="form-control"
                           name="current_password"
                           @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />
                </div>

                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" class="form-control"
                           name="password"
                           @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />
                </div>

                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control"
                           name="password_confirmation"
                           @if(!in_array($user->role, ['admin','super_admin'])) disabled @endif />
                </div>

                @if(in_array($user->role, ['admin','super_admin']))
                <button type="button" onclick="changePassword()" class="btn btn-danger">
                    Update Password
                </button>
                @endif
            </form>

        </div>

    </div>
</div>
@endsection


@push('scripts')
<script>
// -----------------------
// Profile Update
// -----------------------
function updateProfile() {
    let form = new FormData(document.getElementById("profileForm"));

    fetch(`/api/users/{{ $user->id }}`, {
        method: "POST",
        headers: {
            "Authorization": `Bearer ${localStorage.getItem('token')}`,
            "X-HTTP-Method-Override": "PATCH"
        },
        body: form
    })
    .then(r => r.json())
    .then(res => {
        Swal.fire("Success", "Profile updated", "success").then(() => location.reload());
    });
}

// -----------------------
// Image Update
// -----------------------
function updateImage() {
    let form = new FormData(document.getElementById("imgForm"));

    fetch(`/api/users/{{ $user->id }}/image`, {
        method: "POST",
        headers: {
            "Authorization": `Bearer ${localStorage.getItem('token')}`
        },
        body: form
    })
    .then(r => r.json())
    .then(res => {
        Swal.fire("Updated!", "Profile image updated.", "success").then(() => location.reload());
    });
}

// -----------------------
// Password Update
// -----------------------
function changePassword() {
    let form = new FormData(document.getElementById("passwordForm"));

    fetch(`/api/users/{{ $user->id }}/password`, {
        method: "POST",
        headers: {
            "Authorization": `Bearer ${localStorage.getItem('token')}`,
            "X-HTTP-Method-Override": "PATCH"
        },
        body: form
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            Swal.fire("Success", "Password changed", "success");
            document.getElementById("passwordForm").reset();
        } else {
            Swal.fire("Error", res.message || "Failed to update password", "error");
        }
    });
}
</script>
@endpush
