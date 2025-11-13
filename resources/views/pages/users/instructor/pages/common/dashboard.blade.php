{{-- resources/views/pages/users/instructor/pages/common/dashboard.blade.php --}}
@extends('pages.users.instructor.layout.structure')

@section('title', 'Instructor Dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">Instructor Dashboard</h1>
      <div class="text-muted">Welcome back, Instructor!</div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-primary bg-opacity-10 p-3 rounded">
                  <i class="fa-solid fa-book-open text-primary fs-4"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h4 class="mb-0">12</h4>
                <p class="text-muted mb-0">Active Courses</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-success bg-opacity-10 p-3 rounded">
                  <i class="fa-solid fa-users text-success fs-4"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h4 class="mb-0">156</h4>
                <p class="text-muted mb-0">Students</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-warning bg-opacity-10 p-3 rounded">
                  <i class="fa-solid fa-file-lines text-warning fs-4"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h4 class="mb-0">8</h4>
                <p class="text-muted mb-0">Pending Assignments</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-info bg-opacity-10 p-3 rounded">
                  <i class="fa-solid fa-calendar-day text-info fs-4"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h4 class="mb-0">3</h4>
                <p class="text-muted mb-0">Today's Sessions</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity & Upcoming Sessions -->
    <div class="row">
      <div class="col-md-8 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-bottom">
            <h5 class="mb-0">Recent Activity</h5>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <div class="list-group-item px-0">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="bg-primary bg-opacity-10 p-2 rounded">
                      <i class="fa-solid fa-file-import text-primary"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">New assignment submissions</h6>
                    <p class="text-muted mb-0">5 students submitted Web Development Assignment</p>
                    <small class="text-muted">2 hours ago</small>
                  </div>
                </div>
              </div>
              <div class="list-group-item px-0">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="bg-success bg-opacity-10 p-2 rounded">
                      <i class="fa-solid fa-comment text-success"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">New questions in forum</h6>
                    <p class="text-muted mb-0">3 new questions in Data Structures course</p>
                    <small class="text-muted">5 hours ago</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-bottom">
            <h5 class="mb-0">Today's Schedule</h5>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1">Web Development</h6>
                    <p class="text-muted mb-0">10:00 AM - 11:30 AM</p>
                  </div>
                  <span class="badge bg-primary">Live</span>
                </div>
              </div>
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1">Data Structures</h6>
                    <p class="text-muted mb-0">02:00 PM - 03:30 PM</p>
                  </div>
                  <span class="badge bg-secondary">Upcoming</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection