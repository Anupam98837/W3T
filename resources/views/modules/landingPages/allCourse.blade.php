{{-- resources/views/courses/index.blade.php --}}

<body class="lp-page">
  {{-- Enhanced CSS for better UI --}}
  <style>
    :root {
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

/* Filter section */
.courses-filter-section {
  background: var(--surface);
  border-radius: var(--radius-1);
  padding: 24px;
  margin-bottom: 32px;
  border: 1px solid var(--line-soft);
  box-shadow: var(--shadow-1);
}

.filter-header {
  margin-bottom: 20px;
}

.filter-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--ink);
  margin-bottom: 6px;
  font-family: var(--font-head);
}

.filter-subtitle {
  font-size: 0.9rem;
  color: var(--muted-color);
  font-family: var(--font-sans);
}

.filter-form .form-control,
.filter-form .form-select {
  border-radius: 8px;
  border: 1.5px solid var(--line-strong);
  padding: 10px 14px;
  font-size: 0.95rem;
  transition: var(--transition);
  height: 46px;
  font-family: var(--font-sans);
  background: var(--surface);
  color: var(--text-color);
}

.filter-form .form-control:focus,
.filter-form .form-select:focus {
  border-color: var(--primary-color);
  box-shadow: var(--ring);
  outline: none;
}

.filter-form .form-control::placeholder {
  color: var(--muted-color);
}

.filter-actions {
  display: flex;
  gap: 12px;
  align-items: center;
  height: 46px;
}

/* Primary apply button */
.btn-apply {
  flex: 1;
  background: var(--primary-color);
  border: none;
  border-radius: var(--radius-1);
  padding: 10px 20px;
  font-weight: 500;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: var(--surface);
  box-shadow: var(--shadow-1);
  font-family: var(--font-sans);
}

.btn-apply:hover {
  background: var(--secondary-color);
  transform: translateY(-1px);
  box-shadow: var(--shadow-2);
}

.btn-apply:active {
  transform: translateY(0);
}

/* Reset button */
.btn-reset {
  width: 46px;
  height: 46px;
  border-radius: var(--radius-1);
  border: 1.5px solid var(--line-strong);
  background: var(--surface);
  color: var(--muted-color);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
  box-shadow: var(--shadow-1);
}

.btn-reset:hover {
  background: var(--page-hover);
  border-color: var(--line-soft);
  color: var(--text-color);
  transform: translateY(-1px);
}

.btn-reset:active {
  transform: translateY(0);
}

.btn-reset i {
  font-size: 16px;
}

.courses-counter {
  font-size: 0.9rem;
  color: var(--muted-color);
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--line-soft);
}

/* Enhanced skeleton loader */
.skeleton-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.skeleton-thumb {
  background: linear-gradient(
    90deg,
    var(--page-disabled) 25%,
    var(--line-soft) 50%,
    var(--page-disabled) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

/* Enhanced course cards */
.lp-course-card {
  transition: var(--transition);
  border: 1px solid var(--line-strong);
  overflow: hidden;
  background: var(--surface);
  box-shadow: var(--shadow-1);
  border-radius: var(--radius-1);
}

.lp-course-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-3);
  border-color: var(--line-strong);
}

.lp-course-thumb {
  height: 160px;
  position: relative;
  overflow: hidden;
}

.lp-course-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.lp-course-card:hover .lp-course-thumb img {
  transform: scale(1.05);
}

.lp-category-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: var(--primary-color);
  color: var(--surface);
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
  backdrop-filter: blur(4px);
  font-family: var(--font-sans);
  opacity: 0.9;
}

.lp-course-body {
  padding: 18px;
}

.lp-course-title {
  font-weight: 600;
  color: var(--ink);
  font-size: 1.1rem;
  line-height: 1.4;
  margin-bottom: 10px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  font-family: var(--font-head);
}

.lp-course-meta {
  display: flex;
  gap: 16px;
  margin-bottom: 12px;
  font-size: 0.85rem;
  color: var(--muted-color);
  font-family: var(--font-sans);
}

.lp-course-meta span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.lp-course-meta i {
  font-size: 0.8rem;
}

.lp-course-summary {
  color: var(--muted-color);
  font-size: 0.9rem;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  font-family: var(--font-sans);
}

.lp-course-footer {
  padding: 14px 18px;
  border-top: 1px solid var(--line-soft);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--bg-body);
}

.lp-price {
  font-weight: 600;
  color: var(--ink);
  font-size: 1.1rem;
  font-family: var(--font-sans);
}

.lp-badge-level {
  background: var(--page-disabled);
  color: var(--muted-color);
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  font-family: var(--font-sans);
}

/* Enhanced pagination */
.pagination .page-link {
  border: 1.5px solid var(--line-strong);
  color: var(--text-color);
  padding: 8px 14px;
  margin: 0 4px;
  border-radius: 6px;
  font-weight: 500;
  transition: var(--transition);
  background: var(--page-bg);
  font-family: var(--font-sans);
}

.pagination .page-link:hover {
  background: var(--page-hover);
  border-color: var(--line-soft);
}

.pagination .active .page-link {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: var(--surface);
}

/* Empty state enhancement */
.empty-state-container {
  text-align: center;
  padding: 60px 20px;
  background: var(--bg-body);
  border-radius: var(--radius-1);
  margin: 30px 0;
}

.empty-state-icon {
  font-size: 48px;
  color: var(--muted-color);
  opacity: 0.7;
}

.empty-state-title {
  font-size: 1.2rem;
  color: var(--text-color);
  margin-bottom: 10px;
  font-weight: 600;
  font-family: var(--font-head);
}

.empty-state-text {
  color: var(--muted-color);
  max-width: 400px;
  margin: 0 auto;
  font-family: var(--font-sans);
}

  </style>

  <main class="lp-section" style="padding-top: 32px;">
    <div class="lp-section-inner">

      {{-- Page heading --}}
      <div class="lp-section-head mb-4">
        <div>
          <h2 class="lp-section-title" style=" color: #1f2937;">All Courses</h2>
          <div class="lp-section-sub" style="font-size: 1.1rem; color: #6b7280;">
            Browse our comprehensive catalog of job-ready programs
          </div>
        </div>
      </div>

      {{-- Enhanced filter section --}}
      <div class="courses-filter-section">
        <div class="filter-header">
          <div class="filter-title">Filter Courses</div>
          <div class="filter-subtitle">Refine your search by category or keywords</div>
        </div>
        
        <form id="coursesFilterForm" class="row g-3 filter-form">
          {{-- Search --}}
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="fa fa-search text-muted"></i>
              </span>
              <input
                type="text"
                id="courseSearchInput"
                class="form-control border-start-0 ps-0"
                placeholder="Search courses, topics, or skills..."
              >
            </div>
          </div>

          {{-- Category filter --}}
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="fa fa-filter text-muted"></i>
              </span>
              <select id="courseCategorySelect" class="form-select border-start-0 ps-0">
                <option value="">All Categories</option>
              </select>
            </div>
          </div>

          {{-- Action buttons --}}
          <div class="col-md-2">
            <div class="filter-actions">
              <button type="button" id="courseApplyBtn" class="btn-apply">
                <i class="fa fa-search"></i>
                <span>Search</span>
              </button>
              <button type="button" id="courseResetBtn" class="btn-reset" title="Reset filters">
                <i class="fa fa-times"></i>
              </button>
            </div>
          </div>
          
          {{-- Results counter --}}
          <div id="resultsCounter" class="courses-counter" style="display: none;">
            Showing <span id="resultsCount">0</span> courses
          </div>
        </form>
      </div>

      {{-- Courses grid --}}
      <div id="allCoursesGrid" class="lp-course-grid">
        {{-- Enhanced skeleton loaders --}}
        @for($i = 0; $i < 8; $i++)
          <article class="lp-course-card lp-animate is-visible">
            <div class="lp-course-thumb skeleton-thumb"></div>
            <div class="lp-course-body">
              <div class="lp-course-title skeleton-pulse" style="height:24px;background:#f3f4f6;border-radius:6px;margin-bottom:12px;"></div>
              <div class="lp-course-meta">
                <div style="width:80px;height:14px;background:#f3f4f6;border-radius:4px;"></div>
                <div style="width:60px;height:14px;background:#f3f4f6;border-radius:4px;"></div>
              </div>
              <div class="lp-course-summary">
                <div style="height:16px;background:#f3f4f6;border-radius:4px;margin-bottom:6px;"></div>
                <div style="height:16px;background:#f3f4f6;border-radius:4px;width:80%;"></div>
              </div>
            </div>
            <div class="lp-course-footer" style="display:none">
              <div style="width:60px;height:20px;background:#f3f4f6;border-radius:4px;"></div>
              <div style="width:50px;height:20px;background:#f3f4f6;border-radius:20px;"></div>
            </div>
          </article>
        @endfor
      </div>

      {{-- Enhanced empty state --}}
      <div id="coursesEmptyState" class="empty-state-container" style="display:none;">
        <div class="empty-state-icon">
          <i class="fa fa-search"></i>
        </div>
        <h3 class="empty-state-title">No courses found</h3>
        <p class="empty-state-text">
          We couldn't find any courses matching your search criteria. 
          Try adjusting your filters or search terms.
        </p>
        <button id="resetFromEmpty" class="btn-apply mt-3" style="max-width: 200px;">
          <i class="fa fa-times me-2"></i> Clear all filters
        </button>
      </div>

      {{-- Pagination --}}
      <div id="coursesPagination" class="mt-5 d-flex justify-content-center"></div>

    </div>
  </main>
  {{-- Page-specific JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const qs = sel => document.querySelector(sel);
      const qsa = sel => Array.from(document.querySelectorAll(sel));

      const grid = qs('#allCoursesGrid');
      const emptyState = qs('#coursesEmptyState');
      const pagination = qs('#coursesPagination');
      const searchInput = qs('#courseSearchInput');
      const categorySel = qs('#courseCategorySelect');
      const applyBtn = qs('#courseApplyBtn');
      const resetBtn = qs('#courseResetBtn');
      const resetFromEmpty = qs('#resetFromEmpty');
      const resultsCounter = qs('#resultsCounter');
      const resultsCount = qs('#resultsCount');

      let currentPage = 1;
      const perPage = 12;
      let totalResults = 0;

      const fetchJson = async (url, opts = {}) => {
        try {
          const res = await fetch(url, { 
            headers: { 
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }, 
            ...opts 
          });
          if (!res.ok) {
            console.warn('[Courses] API failed:', url, res.status);
            return null;
          }
          return await res.json();
        } catch (error) {
          console.warn('[Courses] Network error:', error);
          return null;
        }
      };

      /* ============= Load Categories ============= */
      const loadCategories = async () => {
        grid.innerHTML = createSkeletonCards(8);
        
        const url = "{{ url('api/landing/categories/display') }}";
        const data = await fetchJson(url);
        
        if (!data || !Array.isArray(data.data)) {
          console.warn('Failed to load categories');
          return;
        }

        categorySel.innerHTML = '<option value="">All Categories</option>';
        data.data.forEach(cat => {
          const opt = document.createElement('option');
          opt.value = cat.id;
          opt.textContent = cat.title || cat.name || 'Uncategorized';
          categorySel.appendChild(opt);
        });

        // Load courses after categories are loaded
        loadCourses(1);
      };

      /* ============= Skeleton Loader ============= */
      const createSkeletonCards = (count) => {
        let skeleton = '';
        for (let i = 0; i < count; i++) {
          skeleton += `
            <article class="lp-course-card lp-animate is-visible">
              <div class="lp-course-thumb skeleton-thumb"></div>
              <div class="lp-course-body">
                <div class="lp-course-title skeleton-pulse" style="height:24px;background:#f3f4f6;border-radius:6px;margin-bottom:12px;"></div>
                <div class="lp-course-meta">
                  <div style="width:80px;height:14px;background:#f3f4f6;border-radius:4px;"></div>
                  <div style="width:60px;height:14px;background:#f3f4f6;border-radius:4px;"></div>
                </div>
                <div class="lp-course-summary">
                  <div style="height:16px;background:#f3f4f6;border-radius:4px;margin-bottom:6px;"></div>
                  <div style="height:16px;background:#f3f4f6;border-radius:4px;width:80%;"></div>
                </div>
              </div>
              <div class="lp-course-footer" style="display:none">
                <div style="width:60px;height:20px;background:#f3f4f6;border-radius:4px;"></div>
                <div style="width:50px;height:20px;background:#f3f4f6;border-radius:20px;"></div>
              </div>
            </article>
          `;
        }
        return skeleton;
      };

      /* ============= Build API URL ============= */
      const buildCoursesApiUrl = (page = 1) => {
        const params = new URLSearchParams();
        const q = searchInput.value.trim();
        const c = categorySel.value;

        if (q) params.set('q', q);
        if (c) params.set('category', c);
        params.set('page', page);
        params.set('per_page', perPage);

        return "{{ url('api/courses') }}?" + params.toString();
      };

      /* ============= Price Formatter ============= */
      const formatPrice = (course) => {
        if (course.final_price_ui != null && Number(course.final_price_ui) > 0) {
          return '₹' + Number(course.final_price_ui).toLocaleString('en-IN');
        }
        if (course.price_amount == null || Number(course.price_amount) === 0) {
          return 'Free';
        }
        const amt = Number(course.price_amount);
        const cur = course.price_currency || 'INR';
        if (cur === 'INR') {
          return '₹' + amt.toLocaleString('en-IN');
        }
        return cur + ' ' + amt.toLocaleString('en-IN');
      };

      /* ============= Render Courses Grid ============= */
      const renderCourses = (items) => {
        if (!items.length) {
          emptyState.style.display = 'block';
          resultsCounter.style.display = 'none';
          return;
        }

        emptyState.style.display = 'none';
        resultsCounter.style.display = 'block';
        resultsCount.textContent = totalResults.toLocaleString();

        grid.innerHTML = items.map(course => {
          const imageUrl = course.thumbnail_url || 
                          course.banner_url || 
                          "{{ asset('assets/media/images/web/course-placeholder.jpg') }}";
          
          const categoryLabel = course.category_title || 
                              course.category_name || 
                              course.category || '';
          
          const titleText = course.title || course.course_title || 'Untitled Course';
          const levelText = course.level || 'Beginner';
          const language = course.language || 'English';
          const summaryHtml = course.short_description || course.summary || '';
          
          const courseId = course.uuid || course.course_uuid || course.id;
          const detailUrl = courseId ? 
            "{{ url('courses') }}/" + encodeURIComponent(courseId) : '#';

          return `
            <article class="lp-course-card lp-animate is-visible"
                     onclick="window.location.href='${detailUrl}'"
                     onkeydown="if(event.key === 'Enter') window.location.href='${detailUrl}'"
                     tabindex="0"
                     role="button">
              <div class="lp-course-thumb">
                <img src="${imageUrl}" alt="${titleText}" loading="lazy">
                ${categoryLabel ? `<div class="lp-category-badge">${categoryLabel}</div>` : ''}
              </div>
              <div class="lp-course-body">
                <h3 class="lp-course-title">${titleText}</h3>
                <div class="lp-course-meta">
                  <span><i class="fa fa-signal"></i> ${levelText}</span>
                  <span><i class="fa fa-language"></i> ${language}</span>
                </div>
                <div class="lp-course-summary">${summaryHtml}</div>
              </div>
              <div class="lp-course-footer" style="display:none">
                <span class="lp-price">${formatPrice(course)}</span>
                ${levelText ? `<span class="lp-badge-level">${levelText}</span>` : ''}
              </div>
            </article>
          `;
        }).join('');
      };

      /* ============= Enhanced Pagination ============= */
      const renderPagination = (meta) => {
        pagination.innerHTML = '';
        if (!meta || !meta.total || meta.total <= meta.per_page) return;

        const totalPages = Math.ceil(meta.total / meta.per_page);
        const container = document.createElement('nav');
        container.setAttribute('aria-label', 'Course pagination');
        
        const ul = document.createElement('ul');
        ul.className = 'pagination';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${meta.page <= 1 ? 'disabled' : ''}`;
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'page-link';
        prevBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
        prevBtn.disabled = meta.page <= 1;
        if (meta.page > 1) {
          prevBtn.addEventListener('click', () => loadCourses(meta.page - 1));
        }
        prevLi.appendChild(prevBtn);

        // Page numbers
        ul.appendChild(prevLi);

        const maxVisible = 5;
        let start = Math.max(1, meta.page - Math.floor(maxVisible / 2));
        let end = Math.min(totalPages, start + maxVisible - 1);

        if (end - start + 1 < maxVisible) {
          start = Math.max(1, end - maxVisible + 1);
        }

        if (start > 1) {
          const firstLi = document.createElement('li');
          firstLi.className = 'page-item';
          const firstBtn = document.createElement('button');
          firstBtn.type = 'button';
          firstBtn.className = 'page-link';
          firstBtn.textContent = '1';
          firstBtn.addEventListener('click', () => loadCourses(1));
          firstLi.appendChild(firstBtn);
          ul.appendChild(firstLi);
          
          if (start > 2) {
            const ellipsisLi = document.createElement('li');
            ellipsisLi.className = 'page-item disabled';
            const ellipsisSpan = document.createElement('span');
            ellipsisSpan.className = 'page-link';
            ellipsisSpan.textContent = '...';
            ellipsisLi.appendChild(ellipsisSpan);
            ul.appendChild(ellipsisLi);
          }
        }

        for (let i = start; i <= end; i++) {
          const pageLi = document.createElement('li');
          pageLi.className = `page-item ${i === meta.page ? 'active' : ''}`;
          const pageBtn = document.createElement('button');
          pageBtn.type = 'button';
          pageBtn.className = 'page-link';
          pageBtn.textContent = i;
          if (i !== meta.page) {
            pageBtn.addEventListener('click', () => loadCourses(i));
          }
          pageLi.appendChild(pageBtn);
          ul.appendChild(pageLi);
        }

        if (end < totalPages) {
          if (end < totalPages - 1) {
            const ellipsisLi = document.createElement('li');
            ellipsisLi.className = 'page-item disabled';
            const ellipsisSpan = document.createElement('span');
            ellipsisSpan.className = 'page-link';
            ellipsisSpan.textContent = '...';
            ellipsisLi.appendChild(ellipsisSpan);
            ul.appendChild(ellipsisLi);
          }
          
          const lastLi = document.createElement('li');
          lastLi.className = 'page-item';
          const lastBtn = document.createElement('button');
          lastBtn.type = 'button';
          lastBtn.className = 'page-link';
          lastBtn.textContent = totalPages;
          lastBtn.addEventListener('click', () => loadCourses(totalPages));
          lastLi.appendChild(lastBtn);
          ul.appendChild(lastLi);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${meta.page >= totalPages ? 'disabled' : ''}`;
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'page-link';
        nextBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
        nextBtn.disabled = meta.page >= totalPages;
        if (meta.page < totalPages) {
          nextBtn.addEventListener('click', () => loadCourses(meta.page + 1));
        }
        nextLi.appendChild(nextBtn);
        ul.appendChild(nextLi);

        container.appendChild(ul);
        pagination.appendChild(container);
      };

      /* ============= Load Courses ============= */
      const loadCourses = async (page = 1) => {
        currentPage = page;
        
        // Show loading state
        grid.innerHTML = createSkeletonCards(8);
        
        const url = buildCoursesApiUrl(page);
        const data = await fetchJson(url);

        if (!data || !Array.isArray(data.data)) {
          renderCourses([]);
          pagination.innerHTML = '';
          return;
        }

        totalResults = data.pagination?.total || data.data.length;
        renderCourses(data.data);
        
        const meta = data.pagination || {
          page: page,
          per_page: perPage,
          total: totalResults
        };
        renderPagination(meta);
      };

      /* ============= Event Listeners ============= */
      applyBtn.addEventListener('click', () => loadCourses(1));
      
      resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        categorySel.value = '';
        const baseUrl = "{{ url('courses/all') }}";
        window.history.replaceState({}, '', baseUrl);
        loadCourses(1);
      });
      
      resetFromEmpty.addEventListener('click', () => {
        searchInput.value = '';
        categorySel.value = '';
        loadCourses(1);
      });

      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          loadCourses(1);
        }
      });

      // Debounced search input
      let searchTimeout;
      searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          if (searchInput.value.trim().length >= 2 || searchInput.value.trim() === '') {
            loadCourses(1);
          }
        }, 500);
      });

      categorySel.addEventListener('change', () => {
        loadCourses(1);
      });

      /* ============= Initialize ============= */
      const urlParams = new URLSearchParams(window.location.search);
      const initialCategoryId = urlParams.get('category') || '';
      const initialQ = urlParams.get('q') || '';

      (async () => {
        await loadCategories();
        
        if (initialCategoryId) {
          categorySel.value = initialCategoryId;
        }
        
        if (initialQ) {
          searchInput.value = initialQ;
        }
      })();
    });
  </script>
</body>