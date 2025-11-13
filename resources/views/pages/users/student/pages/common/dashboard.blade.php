@extends('pages.users.student.layout.structure')

@section('title', 'Student Dashboard - W3Techiez')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
  
<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">Student Dashboard</h1>
      <div class="text-muted">
        <i class="fa-regular fa-calendar me-2"></i>
        <span id="currentDate">{{ date('F j, Y') }}</span>
      </div>
    </div>

    <!-- Welcome Card -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h3 class="card-title mb-2">Welcome back, Student! 👋</h3>
            <p class="card-text text-muted mb-3">
              Continue your learning journey. You have 3 pending assignments and 1 upcoming quiz this week.
            </p>
            <div class="d-flex gap-2">
              <a href="/student/courses" class="btn btn-primary">
                <i class="fa-solid fa-book-open me-2"></i>My Courses
              </a>
              <a href="/student/assignments" class="btn btn-outline-primary">
                <i class="fa-solid fa-file-lines me-2"></i>Assignments
              </a>
            </div>
          </div>
          <div class="col-md-4 text-center d-none d-md-block">
            <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-block">
              <i class="fa-solid fa-graduation-cap fa-3x text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
      <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
              <i class="fa-solid fa-book-open text-primary fa-lg"></i>
            </div>
            <h4 class="mb-1">5</h4>
            <p class="text-muted small mb-0">Enrolled Courses</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
              <i class="fa-solid fa-file-lines text-warning fa-lg"></i>
            </div>
            <h4 class="mb-1">3</h4>
            <p class="text-muted small mb-0">Pending Assignments</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
              <i class="fa-solid fa-pen-to-square text-info fa-lg"></i>
            </div>
            <h4 class="mb-1">1</h4>
            <p class="text-muted small mb-0">Upcoming Quizzes</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
              <i class="fa-solid fa-star text-success fa-lg"></i>
            </div>
            <h4 class="mb-1">85%</h4>
            <p class="text-muted small mb-0">Overall Progress</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Recent Activities -->
      <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="card-title mb-0">
              <i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Activities
            </h5>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <div class="list-group-item px-0">
                <div class="d-flex align-items-center">
                  <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="fa-solid fa-check text-success fa-sm"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1">Assignment Submitted</h6>
                    <p class="text-muted small mb-0">Math Assignment - Calculus Chapter 3</p>
                  </div>
                  <small class="text-muted">2 hours ago</small>
                </div>
              </div>
              <div class="list-group-item px-0">
                <div class="d-flex align-items-center">
                  <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="fa-solid fa-video text-primary fa-sm"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1">Video Watched</h6>
                    <p class="text-muted small mb-0">Physics - Quantum Mechanics Lecture</p>
                  </div>
                  <small class="text-muted">1 day ago</small>
                </div>
              </div>
              <div class="list-group-item px-0">
                <div class="d-flex align-items-center">
                  <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="fa-solid fa-pen-to-square text-info fa-sm"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1">Quiz Completed</h6>
                    <p class="text-muted small mb-0">Chemistry Quiz - Score: 18/20</p>
                  </div>
                  <small class="text-muted">2 days ago</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming Deadlines -->
      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="card-title mb-0">
              <i class="fa-solid fa-calendar-day me-2"></i>Upcoming Deadlines
            </h5>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <div class="list-group-item px-0 border-warning">
                <div class="d-flex align-items-start">
                  <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                    <i class="fa-solid fa-file-lines text-warning fa-sm"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1">Physics Lab Report</h6>
                    <p class="text-muted small mb-1">Due: Tomorrow, 11:59 PM</p>
                    <span class="badge bg-warning text-dark">Urgent</span>
                  </div>
                </div>
              </div>
              <div class="list-group-item px-0">
                <div class="d-flex align-items-start">
                  <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                    <i class="fa-solid fa-pen-to-square text-info fa-sm"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1">Math Quiz</h6>
                    <p class="text-muted small mb-0">Due: Feb 15, 2:00 PM</p>
                  </div>
                </div>
              </div>
              <div class="list-group-item px-0">
                <div class="d-flex align-items-start">
                  <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                    <i class="fa-solid fa-file-lines text-primary fa-sm"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1">Programming Assignment</h6>
                    <p class="text-muted small mb-0">Due: Feb 18, 5:00 PM</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.bg-opacity-10 {
  background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Update current date
  const currentDate = new Date();
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  document.getElementById('currentDate').textContent = currentDate.toLocaleDateString('en-US', options);
});
</script>
@endsection