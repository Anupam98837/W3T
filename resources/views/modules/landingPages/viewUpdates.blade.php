{{-- resources/views/updates/index.blade.php --}}
 
<body class="lp-page">
  <style>
  /* ================================
     Landing Updates – Modern Strip
     ================================ */
 
  .updates-shell {
    max-width: 1200px;
    margin: 32px auto 56px;
    padding: 0 16px;
  }
 
  .updates-header {
    text-align: center;
    margin-bottom: 32px;
  }
 
  .updates-title {
    font-size: 2.1rem;
    font-weight: 700;
    margin-bottom: 10px;
    font-family: var(--font-head);
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--ink) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
 
  .updates-subtitle {
    font-size: 1rem;
    color: var(--muted-color);
    max-width: 560px;
    margin: 0 auto;
    line-height: 1.6;
    font-family: var(--font-sans);
  }
 
  /* Search + stats */
  .updates-toolbar {
    background: linear-gradient(135deg, var(--surface) 0%, var(--bg-body) 100%);
    border-radius: var(--radius-1);
    padding: 18px 18px 14px;
    border: 1px solid var(--line-soft);
    box-shadow: var(--shadow-1);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
  }
 
  .updates-search-wrap {
    position: relative;
    flex: 1 1 280px;
    max-width: 420px;
  }
 
  .updates-search-wrap .form-control {
    border-radius: 8px;
    border: 1.5px solid var(--line-strong);
    padding: 10px 14px 10px 40px;
    font-size: 0.95rem;
    background: var(--surface);
    color: var(--text-color);
    transition: var(--transition);
    font-family: var(--font-sans);
  }
 
  .updates-search-wrap .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: var(--ring);
    outline: none;
  }
 
  .updates-search-wrap .form-control::placeholder {
    color: var(--muted-color);
  }
 
  .updates-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: var(--muted-color);
  }
 
  .updates-counter {
    font-size: 0.9rem;
    color: var(--muted-color);
    font-family: var(--font-sans);
  }
 
  .updates-badge-new {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.8rem;
    background: var(--t-primary);
    color: var(--primary-color);
    font-weight: 500;
  }
 
  /* List */
  .updates-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
 
  .update-item {
    position: relative;
    display: flex;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    background: var(--surface);
    border: 1px solid var(--line-soft);
    box-shadow: var(--shadow-1);
    align-items: flex-start;
    transition: var(--transition);
  }
 
  .update-item::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 12px;
    bottom: 12px;
    width: 3px;
    border-radius: 999px;
    background: linear-gradient(180deg, var(--t-primary) 0%, var(--t-info) 100%);
    opacity: 0;
    transition: opacity 0.2s ease;
  }
 
  .update-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-3);
    border-color: var(--line-strong);
  }
 
  .update-item:hover::before {
    opacity: 1;
  }
 
  .update-icon {
    flex: 0 0 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--t-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 16px;
    margin-left: 4px;
    position: relative;
    z-index: 1;
  }
 
  .update-body {
    flex: 1;
    position: relative;
    z-index: 1;
  }
 
  .update-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 4px;
    font-family: var(--font-head);
  }
 
  .update-title span {
    /* for plain text fallback */
    display: inline-block;
  }
 
  .update-description {
    font-size: 0.9rem;
    color: var(--muted-color);
    margin-bottom: 4px;
    font-family: var(--font-sans);
  }
 
  .update-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 16px;
    font-size: 0.8rem;
    color: var(--muted-color);
  }
 
  .update-dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--muted-color);
  }
 
  .update-time {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
 
  .update-pill {
    padding: 2px 8px;
    border-radius: 999px;
    background: var(--page-disabled);
    color: var(--muted-color);
  }
 
  .update-highlight {
    background: var(--t-success);
    color: var(--success-color);
  }
 
  /* Skeletons */
  .update-skeleton {
    display: flex;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    background: var(--surface);
    border: 1px solid var(--line-soft);
  }
 
  .update-skel-icon {
    flex: 0 0 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(
      90deg,
      var(--page-disabled) 25%,
      var(--line-soft) 50%,
      var(--page-disabled) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
  }
 
  .update-skel-lines {
    flex: 1;
  }
 
  .update-skel-line {
    height: 12px;
    border-radius: 6px;
    background: var(--page-disabled);
    margin-bottom: 8px;
  }
 
  .update-skel-line.short {
    width: 50%;
  }
 
  .update-skel-line.mid {
    width: 70%;
  }
 
  .update-skel-line.full {
    width: 100%;
  }
 
  @keyframes shimmer {
    0%   { background-position: -200% 0; }
    100% { background-position:  200% 0; }
  }
 
  /* Empty state */
  .updates-empty {
    text-align: center;
    padding: 44px 20px;
    background: var(--bg-body);
    border-radius: 12px;
    margin-top: 18px;
    border: 1px dashed var(--line-soft);
  }
 
  .updates-empty-icon {
    font-size: 40px;
    margin-bottom: 12px;
    color: var(--muted-color);
  }
 
  .updates-empty-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 6px;
    font-family: var(--font-head);
  }
 
  .updates-empty-text {
    font-size: 0.9rem;
    color: var(--muted-color);
    max-width: 420px;
    margin: 0 auto;
    font-family: var(--font-sans);
  }
 
  @media (max-width: 576px) {
    .updates-title {
      font-size: 1.7rem;
    }
    .updates-toolbar {
      padding: 14px 12px;
    }
    .update-item {
      padding: 12px 12px;
    }
    .update-item::before {
      left: 8px;
    }
    .update-icon {
      margin-left: 0;
    }
  }
  </style>
 
  <main class="lp-section" style="padding-top: 32px;">
    <div class="lp-section-inner">
      <div class="updates-shell">
        {{-- Header --}}
        <div class="updates-header">
          <h1 class="updates-title">What’s New at W3Techiez</h1>
          <!-- <p class="updates-subtitle">
            Short highlight strips for your landing page – new bootcamps, placement-oriented batches,
            mentor support and more. This pulls live data from your <code>landingpage_updates</code> table.
          </p> -->
        </div>
 
        {{-- Search + counter --}}
        <div class="updates-toolbar">
          <div class="updates-search-wrap">
            <i class="fa fa-search updates-search-icon"></i>
            <input
              type="text"
              id="updatesSearchInput"
              class="form-control"
              placeholder="Search updates (e.g. interview, placement, bootcamp)..."
              aria-label="Search updates"
            >
          </div>
 
          <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 ms-sm-auto">
            <span class="updates-counter" id="updatesCounter" style="display:none;">
              Showing <strong><span id="updatesCount">0</span></strong> update(s)
            </span>
            <span class="updates-badge-new" style="display:none;">
              <i class="fa fa-bolt"></i>
              Live landing highlights
            </span>
          </div>
        </div>
 
        {{-- Updates list --}}
        <div id="updatesList" class="updates-list">
          {{-- skeletons --}}
          @for($i = 0; $i < 5; $i++)
            <div class="update-skeleton">
              <div class="update-skel-icon"></div>
              <div class="update-skel-lines">
                <div class="update-skel-line full"></div>
                <div class="update-skel-line mid"></div>
                <div class="update-skel-line short"></div>
              </div>
            </div>
          @endfor
        </div>
 
        {{-- Empty state --}}
        <div id="updatesEmpty" class="updates-empty" style="display:none;">
          <div class="updates-empty-icon">
            <i class="fa fa-newspaper"></i>
          </div>
          <div class="updates-empty-title">No updates found</div>
          <p class="updates-empty-text">
            There are no active highlight strips right now. Add some updates from your admin panel to
            instantly reflect them on your landing page.
          </p>
        </div>
      </div>
    </div>
  </main>
 
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" defer></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const qs = sel => document.querySelector(sel);
 
    const listEl   = qs('#updatesList');
    const emptyEl  = qs('#updatesEmpty');
    const searchEl = qs('#updatesSearchInput');
    const counter  = qs('#updatesCounter');
    const countEl  = qs('#updatesCount');
 
    let allUpdates = [];
    let filtered   = [];
 
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
          console.warn('[Updates] API failed:', url, res.status);
          return null;
        }
        return await res.json();
      } catch (e) {
        console.warn('[Updates] Network error:', e);
        return null;
      }
    };
 
    const formatDate = (dateStr) => {
      if (!dateStr) return '';
      const d = new Date(dateStr.replace(' ', 'T') + 'Z');
      if (isNaN(d.getTime())) return '';
      return d.toLocaleDateString(undefined, {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      });
    };
 
    const isRecent = (dateStr) => {
      if (!dateStr) return false;
      const d = new Date(dateStr.replace(' ', 'T') + 'Z');
      if (isNaN(d.getTime())) return false;
      const now = new Date();
      const diffMs = now - d;
      const diffDays = diffMs / (1000 * 60 * 60 * 24);
      return diffDays <= 7; // last 7 days = "New"
    };
 
    const getUpdateIconClass = (title) => {
      const t = (title || '').toLowerCase();
      if (t.includes('placement') || t.includes('job') || t.includes('career')) {
        return 'fa-solid fa-briefcase';
      }
      if (t.includes('bootcamp') || t.includes('coding') || t.includes('project')) {
        return 'fa-solid fa-code';
      }
      if (t.includes('mentor') || t.includes('guidance') || t.includes('support')) {
        return 'fa-solid fa-user-graduate';
      }
      if (t.includes('interview') || t.includes('resume')) {
        return 'fa-solid fa-file-lines';
      }
      return 'fa-solid fa-bolt';
    };
 
    const renderUpdates = (rows) => {
      listEl.innerHTML = '';
 
      if (!rows.length) {
        emptyEl.style.display = 'block';
        counter.style.display = 'none';
        return;
      }
 
      emptyEl.style.display = 'none';
      counter.style.display = 'block';
      countEl.textContent = rows.length.toString();
 
      const html = rows.map((row, index) => {
        const titleRaw   = row.title || '';
        const desc       = row.description || '';
        const created    = formatDate(row.created_at);
        const updated    = formatDate(row.updated_at);
        const isNew      = isRecent(row.created_at || row.updated_at);
        const iconClass  = getUpdateIconClass(titleRaw);
 
        // If admin stored HTML (like <u>…</u>) keep it as-is
        const titleHtml  = titleRaw;
 
        return `
          <article class="update-item lp-animate is-visible" data-lp-animate="fade-up"
                   style="animation-delay: ${(index % 6) * 80}ms">
            <div class="update-icon">
              <i class="${iconClass}"></i>
            </div>
            <div class="update-body">
              <div class="update-title">
                ${titleHtml}
              </div>
              ${desc ? `<div class="update-description">${desc}</div>` : ''}
              <div class="update-meta-row">
                ${created ? `
                  <span class="update-time">
                    <i class="fa-regular fa-clock"></i>
                    <span>Created: ${created}</span>
                  </span>
                ` : ''}
                ${updated && updated !== created ? `
                  <span class="update-time">
                    <span class="update-dot"></span>
                    <span>Updated: ${updated}</span>
                  </span>
                ` : ''}
                ${isNew ? `
                  <span class="update-pill update-highlight">
                    <i class="fa fa-star"></i>&nbsp;New
                  </span>
                ` : ''}
                <span class="update-pill" style="display:none;">
                  ID&nbsp;#${row.id}
                </span>
              </div>
            </div>
          </article>
        `;
      }).join('');
 
      listEl.innerHTML = html;
    };
 
    const filterAndRender = () => {
      const term = (searchEl.value || '').toLowerCase().trim();
      if (!term) {
        filtered = allUpdates;
        renderUpdates(filtered);
        return;
      }
 
      filtered = allUpdates.filter(row => {
        const title = (row.title || '').toLowerCase();
        const desc  = (row.description || '').toLowerCase();
        return title.includes(term) || desc.includes(term);
      });
 
      renderUpdates(filtered);
    };
 
    const showSkeletons = () => {
      listEl.innerHTML = Array(5).fill(0).map(() => `
        <div class="update-skeleton">
          <div class="update-skel-icon"></div>
          <div class="update-skel-lines">
            <div class="update-skel-line full"></div>
            <div class="update-skel-line mid"></div>
            <div class="update-skel-line short"></div>
          </div>
        </div>
      `).join('');
      emptyEl.style.display = 'none';
      counter.style.display = 'none';
    };
 
    const loadUpdates = async () => {
      showSkeletons();
      const url = "{{ url('api/landing/updates') }}?per_page=50";
      const res = await fetchJson(url);
 
      if (!res || !Array.isArray(res.data)) {
        console.error('[Updates] Invalid response shape:', res);
        listEl.innerHTML = '';
        emptyEl.style.display = 'block';
        counter.style.display = 'none';
        return;
      }
 
      // Just use returned order (or sort by display_order if needed)
      allUpdates = [...res.data].sort((a, b) => {
        const ao = a.display_order ?? 0;
        const bo = b.display_order ?? 0;
        if (ao !== bo) return ao - bo;
        return (b.id || 0) - (a.id || 0);
      });
 
      filtered = allUpdates;
      renderUpdates(filtered);
    };
 
    // Search debounce
    let searchTimeout;
    if (searchEl) {
      searchEl.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterAndRender, 250);
      });
 
      searchEl.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          searchEl.value = '';
          filterAndRender();
        }
      });
    }
 
    loadUpdates();
  });
  </script>
</body>
 