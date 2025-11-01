@extends('ui.structure')

@section('title','EduPro UI Showcase')
@section('subtitle','Comprehensive preview of styled components in light & dark themes')

@section('content')
<div class="container-fluid">

  <!-- ================= TYPOGRAPHY ================= -->
  <section class="mb-4">
    <h1>Heading H1</h1>
    <h2>Heading H2</h2>
    <h3>Heading H3</h3>
    <p>Regular paragraph — Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    <p class="text-muted">Muted text for subtle context or helper copy.</p>
    <hr>
  </section>

  <!-- ================= BUTTONS & DROPDOWNS ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Buttons</h3>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
      <button class="btn btn-primary"><i class="fa fa-plus"></i>Primary</button>
      <button class="btn btn-secondary">Secondary</button>
      <button class="btn btn-light">Light</button>
      <button class="btn btn-outline-primary">Outline</button>
      <button class="btn btn-primary" disabled>Disabled</button>
      <button class="btn btn-sm btn-primary">Small</button>
      <button class="btn btn-lg btn-primary">Large</button>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-3">
      <div class="btn-group">
        <button class="btn btn-primary">Left</button>
        <button class="btn btn-primary">Middle</button>
        <button class="btn btn-primary">Right</button>
      </div>

      <div class="btn-group">
        <button type="button" class="btn btn-outline-primary">Action</button>
        <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="visually-hidden">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">First</a></li>
          <li><a class="dropdown-item" href="#">Second</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#">Separated</a></li>
        </ul>
      </div>
    </div>
  </section>

  <!-- ================= BADGES & ALERTS ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Badges & Alerts</h3>
    <div class="d-flex flex-wrap gap-2 mb-3">
      <span class="badge badge-success">Success</span>
      <span class="badge badge-warning">Warning</span>
      <span class="badge badge-danger">Danger</span>
      <span class="badge badge-info">Info</span>
    </div>

    <div class="alert alert-info mb-2"><strong>Info:</strong> Upcoming faculty meeting on Monday.</div>
    <div class="alert alert-success mb-2"><strong>Success:</strong> New batch enrolled successfully.</div>
    <div class="alert alert-warning mb-2"><strong>Warning:</strong> Payment pending for 2 students.</div>
    <div class="alert alert-danger"><strong>Error:</strong> Could not connect to database.</div>
  </section>

  <!-- ================= BREADCRUMB ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Breadcrumb</h3>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Library</a></li>
        <li class="breadcrumb-item active" aria-current="page">Data</li>
      </ol>
    </nav>
  </section>

  <!-- ================= CARDS / PANELS ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Cards / Panels</h3>
    <div class="panel mb-3">
      <div class="panel-head">
        <h4 class="title mb-0">Panel Example</h4>
        <button class="btn btn-sm btn-primary"><i class="fa fa-gear"></i>Action</button>
      </div>
      <p>This is a content panel showing border, shadow, and typography. Panels adapt automatically to dark theme.</p>
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header">Card Header</div>
          <div class="card-body">
            <h5 class="card-title">Course snapshot</h5>
            <p class="card-text">Quick summary of a course with standard card styling.</p>
            <a href="#" class="btn btn-outline-primary btn-sm">Details</a>
          </div>
          <div class="card-footer">Last updated 2 days ago</div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="course-card">
          <div class="meta">
            <div class="title">Machine Learning Fundamentals</div>
            <div class="byline">Instructor: Prof. R. Saha</div>
            <div class="tags d-flex gap-1">
              <span class="tag">AI</span><span class="tag">Python</span>
            </div>
          </div>
          <div class="cta">
            <button class="btn btn-primary btn-sm">Enroll</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= TABS & PILLS ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Tabs & Pills</h3>
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-overview">Overview</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-courses">Courses</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-faculty">Faculty</a></li>
    </ul>
    <div class="tab-content panel mb-3">
      <div class="tab-pane fade show active" id="tab-overview">
        <p>Overview content goes here.</p>
      </div>
      <div class="tab-pane fade" id="tab-courses">
        <p>Courses content goes here.</p>
      </div>
      <div class="tab-pane fade" id="tab-faculty">
        <p>Faculty content goes here.</p>
      </div>
    </div>

    <ul class="nav nav-pills">
      <li class="nav-item"><a class="nav-link active" href="#">All</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Active</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Archived</a></li>
    </ul>
  </section>

  <!-- ================= TABLES ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Tables</h3>

    <div class="table-responsive mb-3">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Course</th>
            <th>Instructor</th>
            <th>Students</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td><td>AI & ML</td><td>Prof. S. Mukherjee</td><td>240</td>
            <td><span class="badge badge-success">Active</span></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View"><i class="fa fa-eye"></i></button>
              <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <tr>
            <td>2</td><td>Operating Systems</td><td>Dr. P. Roy</td><td>180</td>
            <td><span class="badge badge-warning">Pending</span></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></button>
              <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <tr>
            <td>3</td><td>Data Science</td><td>Prof. B. Chatterjee</td><td>300</td>
            <td><span class="badge badge-info">Ongoing</span></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></button>
              <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <h6 class="text-muted mb-2">Striped & Compact</h6>
    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle mb-0">
        <thead>
          <tr>
            <th>#</th><th>Dept</th><th>Intake</th><th>Placed</th><th>%</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>1</td><td>CSE</td><td>200</td><td>180</td><td>90%</td></tr>
          <tr><td>2</td><td>ECE</td><td>180</td><td>150</td><td>83%</td></tr>
          <tr><td>3</td><td>ME</td><td>120</td><td>96</td><td>80%</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- ================= FORMS ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Form Elements</h3>
    <form class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" placeholder="Enter full name">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email address</label>
        <input type="email" class="form-control" placeholder="example@domain.com">
      </div>
      <div class="col-md-6">
        <label class="form-label">Department</label>
        <select class="form-select">
          <option>Computer Science</option>
          <option>Mechanical</option>
          <option>Electrical</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Website</label>
        <div class="input-group">
          <span class="input-group-text">https://</span>
          <input type="text" class="form-control" placeholder="domain.com">
        </div>
      </div>
      <div class="col-12">
        <label class="form-label">About</label>
        <textarea class="form-control" rows="3" placeholder="Short bio…"></textarea>
      </div>
      <div class="col-md-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="chk1" checked>
          <label class="form-check-label" for="chk1">Receive updates</label>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="r1" id="r1a" checked>
          <label class="form-check-label" for="r1a">Option A</label>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="sw1" checked>
          <label class="form-check-label" for="sw1">Enable feature</label>
        </div>
      </div>
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary"><i class="fa fa-paper-plane"></i>Submit</button>
      </div>
    </form>
  </section>

  <!-- ================= ACCORDION & LIST GROUP ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Accordion & List Group</h3>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="accordion" id="accExample">
          <div class="accordion-item">
            <h2 class="accordion-header" id="hOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cOne">Semester 1</button>
            </h2>
            <div id="cOne" class="accordion-collapse collapse show" data-bs-parent="#accExample">
              <div class="accordion-body">Maths, Physics, Programming Basics</div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="hTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cTwo">Semester 2</button>
            </h2>
            <div id="cTwo" class="accordion-collapse collapse" data-bs-parent="#accExample">
              <div class="accordion-body">DSA, Digital Logic, English</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <ul class="list-group">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Notification preferences <span class="badge badge-info">New</span>
          </li>
          <li class="list-group-item">Security</li>
          <li class="list-group-item active">Billing</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- ================= PROGRESS & SPINNERS ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Progress & Spinners</h3>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="progress mb-2">
          <div class="progress-bar" style="width: 45%;">45%</div>
        </div>
        <div class="progress">
          <div class="progress-bar" style="width: 75%;">75%</div>
        </div>
      </div>
      <div class="col-md-6 d-flex align-items-center gap-3">
        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
        <div class="spinner-grow" role="status"><span class="visually-hidden">Loading...</span></div>
      </div>
    </div>
  </section>

  <!-- ================= PAGINATION ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Pagination</h3>
    <nav>
      <ul class="pagination">
        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item"><a class="page-link" href="#">1</a></li>
        <li class="page-item active"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
    </nav>
  </section>

  <!-- ================= TOOLTIPS, POPOVERS, TOASTS, MODAL, OFFCANVAS ================= -->
  <section class="mb-5">
    <h3 class="mb-3">Overlays & Feedback</h3>
    <div class="d-flex flex-wrap gap-2 mb-3">
      <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Tooltip on top">Tooltip</button>
      <button class="btn btn-outline-primary" data-bs-toggle="popover" data-bs-content="Popover body content" data-bs-placement="right" title="Popover title">Popover</button>
      <button id="btnToast" class="btn btn-outline-primary">Show Toast</button>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#demoModal">Open Modal</button>
      <button class="btn btn-secondary" data-bs-toggle="offcanvas" data-bs-target="#demoCanvas">Open Offcanvas</button>
    </div>

    <!-- Toast container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
      <div id="demoToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">System</strong>
          <small>Just now</small>
          <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">This is a toast message following your theme.</div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="demoModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Demo Modal</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            All modal surfaces, borders, and text adapt to light/dark themes.
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary">Confirm</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="demoCanvas" aria-labelledby="demoCanvasLabel">
      <div class="offcanvas-header">
        <h5 id="demoCanvasLabel">Offcanvas Panel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        Use this to host filters, quick notes, or contextual help.
      </div>
    </div>
  </section>

</div>
@endsection

@push('scripts')
<script>
  // Enable tooltips & popovers
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));

  // Toast trigger
  document.getElementById('btnToast')?.addEventListener('click', () => {
    const toastEl = document.getElementById('demoToast');
    const t = new bootstrap.Toast(toastEl);
    t.show();
  });
</script>
@endpush
