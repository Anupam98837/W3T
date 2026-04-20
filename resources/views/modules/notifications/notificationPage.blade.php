@section('content')
<div class="container-fluid">
  <!-- Page Header -->
  <div class="page-head d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-bell fs-4" style="color: var(--primary-color);"></i>
      <h5 class="mb-0 fw-bold">Notification History</h5>
      <span class="badge bg-danger rounded-pill ms-1" id="unreadBadge" style="display:none;font-size:11px;"></span>
    </div>
    <div class="actions d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" id="filterBtn">
        <i class="fa-solid fa-filter me-1"></i>Filters
      </button>
      <button class="btn btn-primary btn-sm" id="markAllReadBtn">
        <i class="fa-solid fa-check-double me-1"></i>Mark All Read
      </button>
    </div>
  </div>

  <!-- Filter Panel -->
  <div class="card mb-3" id="filterPanel" style="display:none;">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Read Status</label>
          <select class="form-select form-select-sm" id="filterStatus">
            <option value="">All</option>
            <option value="unread">Unread Only</option>
            <option value="read">Read Only</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Priority</label>
          <select class="form-select form-select-sm" id="filterPriority">
            <option value="">All Priorities</option>
            <option value="urgent">Urgent</option>
            <option value="high">High</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Type</label>
          <select class="form-select form-select-sm" id="filterType">
            <option value="">All Types</option>
            <option value="general">General</option>
            <option value="system">System</option>
            <option value="alert">Alert</option>
            <option value="reminder">Reminder</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary btn-sm w-100" id="applyFiltersBtn">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Apply Filters
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Notifications List -->
  <div class="card">
    <div class="card-body">
      <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 mb-0">Loading notifications...</p>
      </div>

      <div id="emptyState" class="text-center py-5" style="display:none;">
        <i class="fa-regular fa-bell-slash fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">No notifications found</h6>
        <p class="text-muted small mb-0">You're all caught up!</p>
      </div>

      <div id="notificationsList" style="display:none;"></div>

      <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-4" style="display:none;">
        <div class="text-muted small" id="paginationInfo"></div>
        <nav aria-label="Notification pagination">
          <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <i id="notifModalIcon" class="fa-solid fa-bell me-1"></i>
          <h5 class="modal-title" id="notifModalTitle">Notification</h5>
          <span id="notifModalPriority" class="badge priority-badge ms-2">normal</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-wrap gap-3 small text-muted mb-3">
          <span id="notifModalTime"><i class="fa-regular fa-clock me-1"></i>—</span>
          <span id="notifModalType" class="d-none"><i class="fa-solid fa-tag me-1"></i><span></span></span>
          <span id="notifModalStatus"><i class="fa-solid fa-envelope-open me-1"></i>Unread</span>
        </div>
        <div id="notifModalMessage" class="notif-modal-message"></div>
      </div>
      <div class="modal-footer">
        <div class="notify d-none">
            <a id="notifModalOpenLink" class="btn btn-primary d-none" href="#" target="_blank" rel="noopener">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open Link
        </a>
        </div>
        
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.notification-item {
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
  transition: all 0.2s ease;
  background: #fff;
}
html.theme-dark .notification-item {
  background: var(--light-color, #0f172a);
  border-color: var(--border-color, #273244);
}
.notification-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateY(-2px); }
.notification-item.unread { background: rgba(79,70,229,.04); border-left: 4px solid var(--accent-color, #6366f1); }
html.theme-dark .notification-item.unread { background: rgba(99,102,241,.08); }

.notification-header { display: flex; align-items: start; gap: 12px; margin-bottom: 12px; }
.notification-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
.notification-icon.urgent { background:rgba(220,38,38,.1);color:#dc2626; }
.notification-icon.high   { background:rgba(234,179,8,.1);color:#eab308; }
.notification-icon.normal { background:rgba(59,130,246,.1);color:#3b82f6; }
.notification-icon.low    { background:rgba(107,114,128,.1);color:#6b7280; }

.notification-content { flex:1;min-width:0;cursor:pointer; }
.notification-title { font-weight:600;font-size:15px;color:var(--text-color);margin-bottom:4px;display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.notification-message { color:var(--muted-color,#6b7280);font-size:14px;line-height:1.5;margin-bottom:8px; }
.notification-meta { display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted-color,#6b7280); }
.notification-actions { display:flex;gap:8px;margin-top:12px; }
.notification-actions .btn { padding:.25rem .5rem; }

.badge.priority-badge { font-size:10px;font-weight:600;padding:4px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:.5px; }
.unread-indicator { width:8px;height:8px;border-radius:50%;background:var(--accent-color,#6366f1);flex-shrink:0; }

@keyframes slideDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
#filterPanel { animation: slideDown .3s ease; }

.notif-modal-message { white-space:pre-wrap;font-size:14px; }
html.theme-dark .modal-content { background:var(--light-color,#0f172a);color:var(--text-color,#e5e7eb); }

@media(max-width:768px){
  .notification-header{flex-direction:column;align-items:stretch;}
  .notification-actions{flex-direction:column;}
  .notification-actions .btn{width:100%;}
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
  // ── Auth: actor identity comes from the token server-side; role for UI only ──
  const TOKEN = (sessionStorage.getItem('token') || localStorage.getItem('token') || '').trim();
  const role  = (sessionStorage.getItem('role')  || localStorage.getItem('type')  || '').toLowerCase();

  // Role hierarchy for UI gating only — actor identity is resolved server-side from the token
  const ADMIN_ROLES = ['super_admin', 'superadmin', 'admin'];
  const IS_ADMIN      = ADMIN_ROLES.includes(role);          // can delete notifications
  const IS_INSTRUCTOR = role === 'instructor';               // read-only, no delete
  const IS_STUDENT    = role === 'student';                  // read-only, no delete

  /* ── DOM refs ── */
  const loadingState       = document.getElementById('loadingState');
  const emptyState         = document.getElementById('emptyState');
  const notificationsList  = document.getElementById('notificationsList');
  const paginationContainer= document.getElementById('paginationContainer');
  const paginationInfo     = document.getElementById('paginationInfo');
  const paginationControls = document.getElementById('paginationControls');
  const filterPanel        = document.getElementById('filterPanel');
  const filterBtn          = document.getElementById('filterBtn');
  const applyFiltersBtn    = document.getElementById('applyFiltersBtn');
  const markAllReadBtn     = document.getElementById('markAllReadBtn');
  const unreadBadge        = document.getElementById('unreadBadge');

  const notifModal = (() => {
    try { return new bootstrap.Modal(document.getElementById('notifModal')); } catch { return null; }
  })();

  let currentPage    = 1;
  let currentFilters = {};
  let notifCache     = new Map();

  /* ── API helpers ── */
  const API = '/api';

  async function jfetch(url, opt = {}) {
    const res  = await fetch(url, { ...opt, headers: { 'Content-Type':'application/json', ...(TOKEN ? { Authorization: 'Bearer '+TOKEN } : {}), ...(opt.headers||{}) } });
    const text = await res.text();
    let json = {};
    try { json = text ? JSON.parse(text) : {}; } catch { throw new Error('Invalid JSON'); }
    if (!res.ok) throw new Error(json?.message || `HTTP ${res.status}`);
    return json;
  }

  const apiGet   = (path)       => jfetch(`${API}${path}`);
  const apiPost  = (path, body) => jfetch(`${API}${path}`, { method:'POST',  body: JSON.stringify(body) });
  const apiPatch = (path, body) => jfetch(`${API}${path}`, { method:'PATCH', body: JSON.stringify(body) });

  /* ── Unread badge (uses ?count_only=1 on GET /notifications) ── */
  async function refreshUnreadBadge() {
    try {
      const res = await apiGet('/notifications?count_only=1');
      const n   = res?.unread ?? 0;
      if (n > 0) { unreadBadge.textContent = n > 99 ? '99+' : n; unreadBadge.style.display = ''; }
      else         unreadBadge.style.display = 'none';
    } catch { /* silent */ }
  }

  /* ── Utility ── */
  function formatTimestamp(ts) {
    if (!ts) return 'Unknown';
    try {
      const d = new Date(ts), now = new Date(), diff = now - d;
      const m = Math.floor(diff/60000), h = Math.floor(diff/3600000), dy = Math.floor(diff/86400000);
      if (m < 1)  return 'Just now';
      if (m < 60) return `${m}m ago`;
      if (h < 24) return `${h}h ago`;
      if (dy < 7) return `${dy}d ago`;
      return d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
    } catch { return 'Unknown'; }
  }

  // A notification is "read by the current user" if ANY receiver entry has read=1.
  // The backend already filters receivers to only the actor's entries.
  function isRead(receivers) {
    return Array.isArray(receivers) && receivers.some(r => Number(r.read) === 1);
  }

  const priorityColor = p => ({ urgent:'danger', high:'warning', normal:'primary', low:'secondary' }[p] || 'secondary');
  const priorityIcon  = p => ({ urgent:'fa-solid fa-circle-exclamation', high:'fa-solid fa-triangle-exclamation', normal:'fa-solid fa-bell', low:'fa-regular fa-bell' }[p] || 'fa-solid fa-bell');

  /* ── Render one notification card ── */
  function renderCard(notif) {
    const read     = isRead(notif.receivers || []);
    const priority = (notif.priority || 'normal').toLowerCase();

    return `
      <div class="notification-item ${read ? 'read' : 'unread'}" data-id="${notif.id}">
        <div class="notification-header">
          <div class="notification-icon ${priority}">
            <i class="${priorityIcon(priority)}"></i>
          </div>
          <div class="notification-content" data-id="${notif.id}" ${notif.link_url ? `data-link="${notif.link_url}"` : ''}>
            <div class="notification-title">
              ${!read ? '<span class="unread-indicator"></span>' : ''}
              <span>${notif.title || 'Notification'}</span>
              <span class="badge priority-badge bg-${priorityColor(priority)}">${priority}</span>
            </div>
            <div class="notification-message">${notif.message || ''}</div>
            <div class="notification-meta">
              <span><i class="fa-regular fa-clock me-1"></i>${formatTimestamp(notif.created_at)}</span>
              ${notif.type ? `<span><i class="fa-solid fa-tag me-1"></i>${notif.type}</span>` : ''}
              ${read ? '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Read</span>' : ''}
            </div>
            <div class="notification-actions">
              ${!read ? `<button class="btn btn-sm btn-outline-success notif-read-btn" data-id="${notif.id}"><i class="fa-solid fa-check me-1"></i>Mark as Read</button>` : ''}
              ${IS_ADMIN ? `<button class="btn btn-sm btn-outline-danger notif-delete-btn" data-id="${notif.id}"><i class="fa-solid fa-trash"></i></button>` : ''}
            </div>
          </div>
        </div>
      </div>`;
  }

  /* ── Optimistically update card to "read" in DOM + cache ── */
  function applyReadToCard(id) {
    const card = document.querySelector(`.notification-item[data-id="${id}"]`);
    if (!card) return;
    card.classList.replace('unread', 'read');
    card.querySelector('.unread-indicator')?.remove();
    card.querySelector('.notif-read-btn')?.remove();
    const meta = card.querySelector('.notification-meta');
    if (meta && !meta.querySelector('.text-success')) {
      meta.insertAdjacentHTML('beforeend', '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Read</span>');
    }
    const cached = notifCache.get(id);
    if (cached) {
      cached.receivers = (cached.receivers || []).map(r => ({ ...r, read: 1, read_at: new Date().toISOString() }));
      notifCache.set(id, cached);
    }
  }

  /* ── Mark one notification read ── */
  // PATCH /api/notifications/{id}  body: { action: "read", read: true }
  async function markOneRead(id) {
    await apiPatch(`/notifications/${id}`, { action: 'read', read: true });
    applyReadToCard(id);
    refreshUnreadBadge();
  }

  /* ── Event delegation after each render ── */
  function attachEvents() {

    // Click on notification body → open modal, auto-mark read
    notificationsList.querySelectorAll('.notification-content').forEach(el => {
      el.addEventListener('click', async e => {
        if (e.target.closest('.notification-actions')) return;
        const id = Number(el.dataset.id);

        // Fetch fresh detail from GET /api/notifications/{id}
        let notif = notifCache.get(id) || {};
        try {
          const detail = await apiGet(`/notifications/${id}`);
          notif = { ...notif, ...detail };
          notifCache.set(id, notif);
        } catch { /* use cache */ }

        openModal(notif);

        if (!isRead(notif.receivers || [])) {
          try { await markOneRead(id); } catch { /* silent */ }
          if (currentFilters.status === 'unread') removeCardFromView(id);
        }
      });
    });

    // Mark as read button
    notificationsList.querySelectorAll('.notif-read-btn').forEach(btn => {
      btn.addEventListener('click', async e => {
        e.stopPropagation();
        const id = Number(btn.dataset.id);
        try {
          await markOneRead(id);
          if (currentFilters.status === 'unread') removeCardFromView(id);
          Swal.fire({ icon:'success', title:'Marked as read', timer:1000, showConfirmButton:false });
        } catch (err) {
          Swal.fire('Error', err.message, 'error');
        }
      });
    });

    // Delete button (admin only) → PATCH /api/notifications/{id} { action:"delete" }
    if (IS_ADMIN) {
      notificationsList.querySelectorAll('.notif-delete-btn').forEach(btn => {
        btn.addEventListener('click', async e => {
          e.stopPropagation();
          const id = Number(btn.dataset.id);
          const ok = await Swal.fire({ title:'Delete Notification?', text:'This cannot be undone', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Delete' });
          if (!ok.isConfirmed) return;
          try {
            await apiPatch(`/notifications/${id}`, { action: 'delete' });
            await loadNotifications(currentPage);
            Swal.fire({ icon:'success', title:'Deleted', timer:1200, showConfirmButton:false });
          } catch (err) {
            Swal.fire('Error', err.message, 'error');
          }
        });
      });
    }

    // Pagination links
    paginationControls.querySelectorAll('.page-link[data-page]').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const page = Number(link.dataset.page);
        if (page && page !== currentPage) { loadNotifications(page); window.scrollTo({ top:0, behavior:'smooth' }); }
      });
    });
  }

  function removeCardFromView(id) {
    const card = document.querySelector(`.notification-item[data-id="${id}"]`);
    if (!card) return;
    card.style.opacity = '0.4';
    setTimeout(() => {
      card.remove();
      if (!notificationsList.querySelector('.notification-item')) {
        notificationsList.style.display = 'none';
        paginationContainer.style.display = 'none';
        emptyState.style.display = 'block';
      }
    }, 300);
  }

  /* ── Mark all read ── */
  // PATCH /api/notifications  body: { action: "read_all" }   (no {id})
  markAllReadBtn.addEventListener('click', async () => {
    const ok = await Swal.fire({ title:'Mark All as Read?', text:'All your notifications will be marked read', icon:'question', showCancelButton:true, confirmButtonText:'Yes, mark all' });
    if (!ok.isConfirmed) return;
    try {
      await apiPatch('/notifications', { action: 'read_all' });
      // Update DOM
      notificationsList.querySelectorAll('.notification-item').forEach(card => applyReadToCard(Number(card.dataset.id)));
      if (currentFilters.status === 'unread') {
        notificationsList.style.display = 'none';
        paginationContainer.style.display = 'none';
        emptyState.style.display = 'block';
      }
      unreadBadge.style.display = 'none';
      Swal.fire({ icon:'success', title:'All marked as read', timer:1500, showConfirmButton:false });
    } catch (err) {
      Swal.fire('Error', err.message, 'error');
    }
  });

  /* ── Load notifications ── */
  // GET /api/notifications?page=&limit=&unread=1&priority=&type=
  // Actor is resolved server-side from Bearer token — no actor_id sent from client
  async function loadNotifications(page = 1) {
    loadingState.style.display = 'block';
    emptyState.style.display = 'none';
    notificationsList.style.display = 'none';
    paginationContainer.style.display = 'none';

    try {
      const qp = new URLSearchParams({ page, limit: 15 });
      if (currentFilters.status === 'unread') qp.set('unread', '1');
      if (currentFilters.priority)            qp.set('priority', currentFilters.priority);
      if (currentFilters.type)               qp.set('type',     currentFilters.type);

      const data  = await apiGet(`/notifications?${qp}`);
      let   items = Array.isArray(data?.data) ? data.data : [];

      // Client-side "read only" filter (backend has no read-only param; unread is the only server param)
      if (currentFilters.status === 'read') items = items.filter(n => isRead(n.receivers));

      notifCache = new Map(items.map(n => [Number(n.id), n]));

      loadingState.style.display = 'none';

      if (items.length === 0) { emptyState.style.display = 'block'; return; }

      notificationsList.innerHTML = items.map(renderCard).join('');
      notificationsList.style.display = 'block';

      if (data.pagination) {
        renderPagination(data.pagination);
        paginationContainer.style.display = 'flex';
      }

      currentPage = page;
      attachEvents();
    } catch (err) {
      loadingState.style.display = 'none';
      Swal.fire('Error', 'Failed to load notifications: ' + err.message, 'error');
    }
  }

  /* ── Pagination ── */
  function renderPagination({ current_page, last_page, total, per_page }) {
    const start = (current_page - 1) * per_page + 1;
    const end   = Math.min(current_page * per_page, total);
    paginationInfo.textContent = `Showing ${start}–${end} of ${total} notifications`;

    const maxV = 5;
    let s = Math.max(1, current_page - Math.floor(maxV/2));
    let e = Math.min(last_page, s + maxV - 1);
    if (e - s < maxV - 1) s = Math.max(1, e - maxV + 1);

    const li = (page, label, disabled = false, active = false) =>
      `<li class="page-item ${disabled?'disabled':''} ${active?'active':''}">
        <a class="page-link" href="#" ${!disabled?`data-page="${page}"`:''}>${label}</a>
      </li>`;

    let html = li(current_page - 1, '<i class="fa-solid fa-chevron-left"></i>', current_page === 1);
    if (s > 1) { html += li(1,'1'); if (s > 2) html += li(null,'…',true); }
    for (let i = s; i <= e; i++) html += li(i, i, false, i === current_page);
    if (e < last_page) { if (e < last_page-1) html += li(null,'…',true); html += li(last_page, last_page); }
    html += li(current_page + 1, '<i class="fa-solid fa-chevron-right"></i>', current_page === last_page);

    paginationControls.innerHTML = html;
  }

  /* ── Modal ── */
  function openModal(notif) {
    const priority = (notif?.priority || 'normal').toLowerCase();
    const read     = isRead(notif?.receivers || []);

    document.getElementById('notifModalTitle').textContent    = notif?.title || 'Notification';
    document.getElementById('notifModalIcon').className       = `${priorityIcon(priority)} me-1`;
    const prBadge = document.getElementById('notifModalPriority');
    prBadge.className   = `badge priority-badge bg-${priorityColor(priority)}`;
    prBadge.textContent = priority;

    document.getElementById('notifModalTime').innerHTML = `<i class="fa-regular fa-clock me-1"></i>${formatTimestamp(notif?.created_at)}`;

    const typeWrap = document.getElementById('notifModalType');
    if (notif?.type) { typeWrap.querySelector('span').textContent = notif.type; typeWrap.classList.remove('d-none'); }
    else               typeWrap.classList.add('d-none');

    document.getElementById('notifModalStatus').innerHTML = read
      ? '<i class="fa-solid fa-check me-1 text-success"></i>Read'
      : '<i class="fa-solid fa-envelope-open me-1 text-primary"></i>Unread';

    document.getElementById('notifModalMessage').textContent = notif?.message || '';

    const linkBtn = document.getElementById('notifModalOpenLink');
    if (notif?.link_url) { linkBtn.href = notif.link_url; linkBtn.classList.remove('d-none'); }
    else                   linkBtn.classList.add('d-none');

    notifModal?.show();
  }

  /* ── Filter handlers ── */
  filterBtn.addEventListener('click', () => {
    filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
  });
  applyFiltersBtn.addEventListener('click', () => {
    currentFilters = {
      status:   document.getElementById('filterStatus').value,
      priority: document.getElementById('filterPriority').value,
      type:     document.getElementById('filterType').value,
    };
    loadNotifications(1);
  });

  /* ── Boot ── */
  await loadNotifications(1);
  refreshUnreadBadge();
});
</script>
@endpush