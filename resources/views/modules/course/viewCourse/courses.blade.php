{{-- resources/views/modules/course/viewCourse/courses.blade.php --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
.course-card {
  background: var(--surface);
  border: 1px solid var(--line-strong);
  border-radius: 16px;
  overflow: hidden;
  transition: var(--transition);
  box-shadow: var(--shadow-1);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.course-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-2);
  border-color: var(--accent-color);
}

.course-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
}

.course-image::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 100%);
}

.course-content {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.course-title {
  font-family: var(--font-head);
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--ink);
  margin-bottom: 10px;
  line-height: 1.3;
  min-height: 2.6rem;
}

.course-description {
  color: var(--text-color);
  font-size: var(--fs-14);
  line-height: 1.6;
  margin-bottom: 16px;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-meta {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
  padding-top: 12px;
  border-top: 1px solid var(--line-soft);
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: var(--fs-13);
  color: var(--muted-color);
}

.meta-item i {
  color: var(--secondary-color);
  font-size: 14px;
}

.badge-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.badge-status.active {
  background: var(--t-success);
  color: #15803d;
  border: 1px solid rgba(22,163,74,.22);
}

.badge-status.archived {
  background: var(--t-danger);
  color: #b91c1c;
  border: 1px solid rgba(220,38,38,.22);
}

.badge-status i {
  font-size: 8px;
}

.btn-view-details {
  width: 100%;
  background: var(--primary-color);
  border: none;
  color: #fff;
  font-weight: 600;
  font-family: var(--font-head);
  padding: 10px;
  border-radius: 10px;
  transition: var(--transition);
  font-size: var(--fs-14);
}

.btn-view-details:hover {
  background: var(--secondary-color);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(149,30,170,0.3);
}

.remaining-days {
  background: var(--t-primary);
  color: var(--secondary-color);
  padding: 6px 12px;
  border-radius: 8px;
  font-size: var(--fs-13);
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* Dark theme support */
.theme-dark .course-card {
  background: var(--surface);
  border-color: var(--line-strong);
}

.theme-dark .course-card:hover {
  border-color: var(--accent-color);
}

.theme-dark .course-title {
  color: var(--ink);
}

.theme-dark .course-description {
  color: var(--text-color);
}

.theme-dark .meta-item {
  color: var(--muted-color);
}
</style>

<div class="container-fluid px-4 py-3">
  <!-- Header Section -->
  <div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
      <h1 class="mb-0" style="font-family: var(--font-head); color: var(--ink);">Available Courses</h1>
      
      <!-- Search Bar -->
      <div class="position-relative" style="width: 320px;">
        <input 
          type="text" 
          id="searchInput" 
          class="form-control ps-5" 
          placeholder="Search courses..." 
          style="border-radius: 12px;"
        >
        <i class="fas fa-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted-color);"></i>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div id="loadingState" class="text-center py-5">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-3 text-muted">Loading courses...</p>
  </div>

  <!-- Empty State -->
  <div id="emptyState" class="empty" style="display: none; padding: 60px 20px;">
    <i class="fas fa-inbox" style="font-size: 64px; color: var(--muted-color); opacity: 0.3; margin-bottom: 16px;"></i>
    <h3 style="color: var(--muted-color); font-weight: 600;">No Courses Found</h3>
    <p style="color: var(--muted-color); margin-top: 8px;">There are no available courses at the moment.</p>
  </div>

  <!-- Courses Grid -->
  <div id="coursesGrid" class="row g-4" style="display: none;">
    <!-- Course cards will be dynamically inserted here -->
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const coursesGrid = document.getElementById('coursesGrid');
  const loadingState = document.getElementById('loadingState');
  const emptyState = document.getElementById('emptyState');
  const searchInput = document.getElementById('searchInput');
  
  let allCourses = [];

  // Get authentication token
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';

  // Fetch courses from API - Try server-side route first, fallback to API
  async function fetchCourses() {
    try {
      // First try: Use a web route that serves the data (server-side rendering approach)
      // This bypasses the API role restriction
      const response = await fetch('/student/courses/data', {
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + TOKEN,
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      // If web route doesn't exist, fallback to API endpoint
      if (response.status === 404) {
        return await fetchFromAPI();
      }

      if (!response.ok) {
        throw new Error('HTTP error! status: ' + response.status);
      }

      const result = await response.json();
      allCourses = result.data || result || [];
      
      renderCourses(allCourses);
      
    } catch (error) {
      console.error('Error fetching courses:', error);
      // Try API as fallback
      await fetchFromAPI();
    }
  }

  // Fallback: Fetch from API endpoint
  async function fetchFromAPI() {
    try {
      const response = await fetch('/api/courses/cards', {
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + TOKEN,
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        if (response.status === 403) {
          throw new Error('Access denied. Please contact your administrator.');
        }
        throw new Error('HTTP error! status: ' + response.status);
      }

      const result = await response.json();
      allCourses = result.data || [];
      
      renderCourses(allCourses);
      
    } catch (error) {
      console.error('Error fetching from API:', error);
      loadingState.style.display = 'none';
      emptyState.style.display = 'block';
      
      if (error.message.includes('Access denied')) {
        emptyState.innerHTML = `
          <i class="fas fa-lock" style="font-size: 64px; color: var(--danger-color); opacity: 0.3; margin-bottom: 16px;"></i>
          <h3 style="color: var(--danger-color); font-weight: 600;">Access Restricted</h3>
          <p style="color: var(--muted-color); margin-top: 8px;">You don't have permission to view courses. Please contact your administrator.</p>
        `;
      } else {
        emptyState.innerHTML = `
          <i class="fas fa-exclamation-circle" style="font-size: 64px; color: var(--danger-color); opacity: 0.3; margin-bottom: 16px;"></i>
          <h3 style="color: var(--danger-color); font-weight: 600;">Error Loading Courses</h3>
          <p style="color: var(--muted-color); margin-top: 8px;">Please try refreshing the page.</p>
        `;
      }
    }
  }

  // Render courses
  function renderCourses(courses) {
    loadingState.style.display = 'none';
    
    if (!courses || courses.length === 0) {
      emptyState.style.display = 'block';
      coursesGrid.style.display = 'none';
      return;
    }

    emptyState.style.display = 'none';
    coursesGrid.style.display = 'flex';
    coursesGrid.innerHTML = '';

    courses.forEach(item => {
      const { batch, course } = item;
      
      const card = document.createElement('div');
      card.className = 'col-12 col-md-6 col-lg-4';
      
      // Calculate status badge
      const statusBadge = batch.status === 'active' 
        ? '<span class="badge-status active"><i class="fas fa-circle"></i> Active</span>'
        : '<span class="badge-status archived"><i class="fas fa-circle"></i> Archived</span>';
      
      // Format dates
      const startDate = new Date(batch.start_date).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric' 
      });
      
      // Remaining days text
      const remainingText = batch.remaining_days > 0 
        ? `${batch.remaining_days} Days Left`
        : 'Expired';
      
      card.innerHTML = `
        <div class="course-card">
<img 
  src="${course.cover_url || '/assets/images/default-course.jpg'}" 
  alt="${course.title}"
  class="course-image"
  onerror="if(this.src!=='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22%3E%3Crect fill=%22%23667eea%22 width=%22400%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial,sans-serif%22 font-size=%2220%22 fill=%22white%22%3E${encodeURIComponent(course.title).replace(/%20/g, ' ')}%3C/text%3E%3C/svg%3E'){this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22%3E%3Crect fill=%22%23667eea%22 width=%22400%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial,sans-serif%22 font-size=%2220%22 fill=%22white%22%3E${encodeURIComponent(course.title).replace(/%20/g, ' ')}%3C/text%3E%3C/svg%3E';this.onerror=null;}"
>
          
          <div class="course-content">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h3 class="course-title">${course.title}</h3>
              ${statusBadge}
            </div>
            
            <p class="course-description">${course.short_description || 'No description available'}</p>
            
            <div class="course-meta">
              <div class="meta-item">
                <i class="far fa-calendar"></i>
                <span>${batch.name}</span>
              </div>
              <div class="meta-item">
                <i class="far fa-clock"></i>
                <span>${batch.duration_days} Days</span>
              </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="remaining-days">
                <i class="fas fa-hourglass-half"></i>
                ${remainingText}
              </span>
              <small class="text-muted">Starts: ${startDate}</small>
            </div>
            
            <button 
              class="btn-view-details" 
              onclick="viewCourseDetails('${batch.uuid}')"
            >
              View Details
            </button>
          </div>
        </div>
      `;
      
      coursesGrid.appendChild(card);
    });
  }

  // Search functionality
  searchInput.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    
    if (!searchTerm) {
      renderCourses(allCourses);
      return;
    }
    
    const filtered = allCourses.filter(item => {
      const { batch, course } = item;
      return (
        course.title.toLowerCase().includes(searchTerm) ||
        course.short_description.toLowerCase().includes(searchTerm) ||
        batch.name.toLowerCase().includes(searchTerm)
      );
    });
    
    renderCourses(filtered);
  });

  // View course details
  window.viewCourseDetails = function(batchUuid) {
    window.location.href = `/courses/${batchUuid}/view`;
  };

  // Initialize
  fetchCourses();
});
</script>
@endpush