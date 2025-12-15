{{-- resources/views/categories/index.blade.php --}}

<body class="lp-page">
  {{-- Enhanced CSS for categories page --}}
  <style>
   /* Enhanced CSS for categories page using design tokens */

/* Wrapper for search + counter */
.categories-section {
  background: linear-gradient(135deg, var(--surface) 0%, var(--bg-body) 100%);
  border-radius: var(--radius-1);
  padding: 24px;
  margin-bottom: 32px;
  border: 1px solid var(--line-soft);
  box-shadow: var(--shadow-1);
}

/* Search */
.search-container {
  max-width: 500px;
}

.search-wrapper {
  position: relative;
}

.search-wrapper .form-control {
  border-radius: 8px;
  border: 1.5px solid var(--line-strong);
  padding: 12px 16px 12px 44px;
  font-size: 0.95rem;
  transition: var(--transition);
  background: var(--surface);
  color: var(--text-color);
  font-family: var(--font-sans);
}

.search-wrapper .form-control:focus {
  border-color: var(--primary-color);
  box-shadow: var(--ring);
  outline: none;
}

.search-wrapper .form-control::placeholder {
  color: var(--muted-color);
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--muted-color);
  font-size: 16px;
}

/* Header text */
.categories-header {
  text-align: center;
  margin-bottom: 40px;
  padding: 0 20px;
}

.categories-title {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 12px;
  font-family: var(--font-head);
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--ink) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.categories-subtitle {
  font-size: 1.1rem;
  color: var(--muted-color);
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
  font-family: var(--font-sans);
}

/* Grid */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  margin-top: 32px;
}

/* Category card */
.category-card {
  background: var(--surface);
  border-radius: 12px;
  padding: 28px 24px;
  text-align: center;
  transition: var(--transition);
  border: 1px solid var(--line-strong);
  position: relative;
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-1);
}

.category-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color) 0%, var(--info-color) 100%);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
}

.category-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-3);
  border-color: var(--line-strong);
}

.category-card:hover::before {
  transform: scaleX(1);
}

/* Icon */
.category-icon {
  width: 72px;
  height: 72px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  font-size: 28px;
  background: linear-gradient(135deg, var(--t-info) 0%, var(--t-primary) 100%);
  color: var(--primary-color);
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.category-icon::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(
    45deg,
    transparent 30%,
    rgba(255,255,255,0.4) 50%,
    transparent 70%
  );
  transform: translateX(-100%) translateY(-100%) rotate(45deg);
  transition: transform 0.6s ease;
}

.category-card:hover .category-icon {
  transform: scale(1.1);
  box-shadow: var(--shadow-2);
}

.category-card:hover .category-icon::after {
  transform: translateX(100%) translateY(100%) rotate(45deg);
}

/* Name + meta */
.category-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--ink);
  margin-bottom: 12px;
  line-height: 1.4;
  font-family: var(--font-head);
}

.category-meta {
  font-size: 0.95rem;
  color: var(--muted-color);
  line-height: 1.5;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  font-family: var(--font-sans);
}

/* Full-card link */
.category-link {
  text-decoration: none !important;
  color: inherit;
  display: block;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
}

/* Arrow CTA */
.category-arrow {
  margin-top: 12px;
  font-size: 0.9rem;
  color: var(--primary-color);
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  opacity: 0;
  transform: translateY(10px);
  transition: var(--transition);
  font-family: var(--font-sans);
}

.category-card:hover .category-arrow {
  opacity: 1;
  transform: translateY(0);
}

.category-arrow i {
  font-size: 12px;
  transition: transform 0.3s ease;
}

.category-card:hover .category-arrow i {
  transform: translateX(3px);
}

/* Counter */
.results-counter {
  font-size: 0.9rem;
  color: var(--muted-color);
  margin-top: 16px;
  text-align: center;
  font-family: var(--font-sans);
}

/* Empty state */
.empty-state-container {
  text-align: center;
  padding: 60px 20px;
  background: var(--bg-body);
  border-radius: 12px;
  margin: 30px 0;
}

.empty-state-icon {
  font-size: 48px;
  color: var(--muted-color);
  margin-bottom: 20px;
  opacity: 0.8;
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

/* Skeleton loaders */
.skeleton-category {
  background: var(--surface);
  border-radius: 12px;
  padding: 28px 24px;
  text-align: center;
  border: 1px solid var(--line-strong);
}

.skeleton-icon {
  width: 72px;
  height: 72px;
  border-radius: 16px;
  margin: 0 auto 20px;
  background: linear-gradient(
    90deg,
    var(--page-disabled) 25%,
    var(--line-soft) 50%,
    var(--page-disabled) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-name {
  height: 24px;
  width: 80%;
  margin: 0 auto 12px;
  border-radius: 6px;
  background: var(--line-soft);
}

.skeleton-meta {
  height: 16px;
  width: 90%;
  margin: 0 auto 8px;
  border-radius: 4px;
  background: var(--page-disabled);
}

.skeleton-meta-short {
  height: 16px;
  width: 70%;
  margin: 0 auto;
  border-radius: 4px;
  background: var(--page-disabled);
}

@keyframes shimmer {
  0%   { background-position: -200% 0; }
  100% { background-position:  200% 0; }
}

/* Responsive tweaks */
@media (max-width: 768px) {
  .categories-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
  }

  .categories-title {
    font-size: 1.8rem;
  }

  .category-card {
    padding: 24px 20px;
  }
}

@media (max-width: 576px) {
  .categories-grid {
    grid-template-columns: 1fr;
  }
}

  </style>

  <main class="lp-section" style="padding-top: 32px;">
    <div class="lp-section-inner">

      {{-- Enhanced header section --}}
      <div class="categories-header">
        <h1 class="categories-title">Explore Learning Paths</h1>
        <p class="categories-subtitle">
          Pick a track you're passionate about. Each category leads to specialized courses designed to advance your career.
        </p>
      </div>

      {{-- Search section --}}
      <div class="categories-section">
        <div class="search-container mx-auto">
          <div class="search-wrapper">
            <i class="fa fa-search search-icon"></i>
            <input
              type="text"
              id="categoriesSearchInput"
              class="form-control"
              placeholder="Search categories by name or description..."
              aria-label="Search categories"
            >
          </div>
        </div>
        
        {{-- Results counter --}}
        <div id="categoriesCounter" class="results-counter" style="display: none;">
          Found <span id="categoriesCount">0</span> categories
        </div>
      </div>

      {{-- Categories grid --}}
      <div id="allCategoriesGrid" class="categories-grid">
        {{-- Enhanced skeleton loaders --}}
        @for($i = 0; $i < 6; $i++)
          <div class="skeleton-category">
            <div class="skeleton-icon"></div>
            <div class="skeleton-name"></div>
            <div class="skeleton-meta"></div>
            <div class="skeleton-meta"></div>
            <div class="skeleton-meta-short"></div>
          </div>
        @endfor
      </div>

      {{-- Enhanced empty state --}}
      <div id="categoriesEmptyState" class="empty-state-container" style="display:none;">
        <div class="empty-state-icon">
          <i class="fa fa-folder-open"></i>
        </div>
        <h3 class="empty-state-title">No Categories Found</h3>
        <p class="empty-state-text">
          We couldn't find any categories matching your search. Try a different term or browse all categories.
        </p>
      </div>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
document.addEventListener('DOMContentLoaded', () => {
  const qs = sel => document.querySelector(sel);
  
  const grid = qs('#allCategoriesGrid');
  const emptyState = qs('#categoriesEmptyState');
  const searchEl = qs('#categoriesSearchInput');
  const counter = qs('#categoriesCounter');
  const countSpan = qs('#categoriesCount');

  let allCategories = [];
  let filteredCategories = [];

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
        console.warn('[Categories] API failed:', url, res.status);
        return null;
      }
      return await res.json();
    } catch (error) {
      console.warn('[Categories] Network error:', error);
      return null;
    }
  };

  // Build URL to /courses/all with category filter
  // If your backend expects "category" param with UUID, use as-is.
  // If it expects "category_uuid", set useUuidParam = true
  const useUuidParam = false; // <-- set true if backend wants "category_uuid" param
  const buildCoursesUrlForCategory = (catId) => {
    const base = "{{ url('courses/all') }}";
    const paramName = useUuidParam ? 'category_uuid' : 'category';
    const params = new URLSearchParams({ [paramName]: String(catId) });
    return base + '?' + params.toString();
  };

  // Generate a unique gradient color based on category index
  const getCategoryColor = (index) => {
    const colors = [
      { bg: 'linear-gradient(135deg, #ebf8ff 0%, #e6fffa 100%)', icon: '#4299e1' },
      { bg: 'linear-gradient(135deg, #fef3c7 0%, #fce7f3 100%)', icon: '#ed8936' },
      { bg: 'linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%)', icon: '#7c3aed' },
      { bg: 'linear-gradient(135deg, #f0f9ff 0%, #ecfeff 100%)', icon: '#0891b2' },
      { bg: 'linear-gradient(135deg, #fef3c7 0%, #ffedd5 100%)', icon: '#d97706' },
      { bg: 'linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%)', icon: '#db2777' },
      { bg: 'linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%)', icon: '#4f46e5' },
      { bg: 'linear-gradient(135deg, #d1fae5 0%, #dcfce7 100%)', icon: '#059669' },
    ];
    return colors[index % colors.length];
  };

  // Generate icon based on category name
  const getCategoryIcon = (categoryName) => {
    const name = (categoryName || '').toLowerCase();
    const iconMap = {
      'web': 'fa-solid fa-code',
      'mobile': 'fa-solid fa-mobile-screen-button',
      'design': 'fa-solid fa-palette',
      'data': 'fa-solid fa-database',
      'ai': 'fa-solid fa-robot',
      'cloud': 'fa-solid fa-cloud',
      'security': 'fa-solid fa-shield-halved',
      'devops': 'fa-solid fa-server',
      'business': 'fa-solid fa-chart-line',
      'marketing': 'fa-solid fa-bullhorn',
      'finance': 'fa-solid fa-money-bill-trend-up',
      'health': 'fa-solid fa-heart-pulse',
      'education': 'fa-solid fa-graduation-cap',
      'language': 'fa-solid fa-language',
      'music': 'fa-solid fa-music',
      'art': 'fa-solid fa-paintbrush',
      'science': 'fa-solid fa-flask',
      'math': 'fa-solid fa-calculator',
    };

    for (const [keyword, icon] of Object.entries(iconMap)) {
      if (name.includes(keyword)) return icon;
    }

    if (name.includes('programming') || name.includes('coding') || name.includes('development')) {
      return 'fa-solid fa-code';
    }
    if (name.includes('design') || name.includes('ui') || name.includes('ux')) {
      return 'fa-solid fa-pen-ruler';
    }
    if (name.includes('business') || name.includes('management') || name.includes('entrepreneur')) {
      return 'fa-solid fa-briefcase';
    }
    if (name.includes('art') || name.includes('creative') || name.includes('drawing')) {
      return 'fa-solid fa-paintbrush';
    }

    return 'fa-solid fa-folder-open';
  };

  const renderCategories = (categories) => {
    grid.innerHTML = '';

    if (!categories.length) {
      emptyState.style.display = 'block';
      counter.style.display = 'none';
      return;
    }

    emptyState.style.display = 'none';
    counter.style.display = 'block';
    countSpan.textContent = categories.length.toLocaleString();

    const categoriesHtml = categories.map((cat, index) => {
      // **Prefer UUID** if present (cat.uuid), else fall back to cat.id
      const catId = cat.uuid || cat.id || null;
      const targetUrl = catId ? buildCoursesUrlForCategory(catId) : '#';
      const iconClass = cat.icon || cat.icon_class || getCategoryIcon(cat.title || cat.name);
      const titleText = cat.title || cat.name || 'Untitled Category';
      const descHtml = cat.description || cat.meta || 'Specialized courses and learning paths';
      const colorStyle = getCategoryColor(index);

      return `
        <div class="category-card lp-animate is-visible"
             data-lp-animate="fade-up"
             style="animation-delay: ${(index % 6) * 100}ms">
          <div class="category-icon" style="background: ${colorStyle.bg}; color: ${colorStyle.icon}">
            <i class="${iconClass}"></i>
          </div>
          <h3 class="category-name">${titleText}</h3>
          <div class="category-meta">${descHtml}</div>
          <div class="category-arrow">
            Explore courses
            <i class="fa fa-arrow-right"></i>
          </div>
          <a href="${targetUrl}" 
             class="category-link" 
             aria-label="Browse ${titleText} courses"
             title="Browse ${titleText} courses">
          </a>
        </div>
      `;
    }).join('');

    grid.innerHTML = categoriesHtml;
  };

  const filterAndRender = () => {
    const term = (searchEl.value || '').toLowerCase().trim();
    
    if (!term) {
      filteredCategories = allCategories;
      renderCategories(allCategories);
      return;
    }
    
    filteredCategories = allCategories.filter(cat => {
      const title = (cat.title || cat.name || '').toLowerCase();
      const desc = (cat.description || cat.meta || '').toLowerCase();
      return title.includes(term) || desc.includes(term);
    });
    
    renderCategories(filteredCategories);
  };

  const loadCategories = async () => {
    // Show skeleton loaders
    grid.innerHTML = Array(6).fill(0).map(() => `
      <div class="skeleton-category">
        <div class="skeleton-icon"></div>
        <div class="skeleton-name"></div>
        <div class="skeleton-meta"></div>
        <div class="skeleton-meta"></div>
        <div class="skeleton-meta-short"></div>
      </div>
    `).join('');

    try {
      const data = await fetchJson("{{ url('api/landing/categories') }}");
      
      if (!data || !Array.isArray(data.data)) {
        throw new Error('Invalid data format');
      }
      
      allCategories = data.data;
      filteredCategories = allCategories;
      renderCategories(allCategories);
    } catch (error) {
      console.error('Failed to load categories:', error);
      grid.innerHTML = '';
      emptyState.style.display = 'block';
      counter.style.display = 'none';
    }
  };

  // Debounced search
  let searchTimeout;
  if (searchEl) {
    searchEl.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(filterAndRender, 300);
    });
    
    // Clear search on Escape key
    searchEl.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        searchEl.value = '';
        filterAndRender();
      }
    });
  }

  // Initialize
  loadCategories();
});
</script>
