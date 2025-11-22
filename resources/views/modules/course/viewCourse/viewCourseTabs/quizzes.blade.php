{{-- resources/views/Quizzes.blade.php --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* reuse your existing sm-* styles from StudyMaterial for visual parity */
.sm-list{max-width:1100px;margin:18px auto}
.sm-card{border-radius:12px;padding:18px}
.sm-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-radius:10px;border:1px solid var(--line-strong);background:var(--surface)}
.sm-item+.sm-item{margin-top:10px}
.sm-item .left{display:flex;gap:12px;align-items:center}
.sm-item .meta{display:flex;flex-direction:column;gap:4px}
.sm-item .meta .title{font-weight:700;color:var(--ink);font-family:var(--font-head)}
.sm-item .meta .sub{color:var(--muted-color);font-size:13px}
.sm-empty{border:1px dashed var(--line-strong);border-radius:12px;padding:18px;background:transparent;color:var(--muted-color);text-align:center}
.sm-loader{display:flex;align-items:center;gap:8px;color:var(--muted-color)}
.duration-pill{font-size:12px;color:var(--muted-color);background:transparent;border-radius:999px;padding:4px 8px;border:1px solid var(--line-strong)}
.sm-more{position:relative;display:inline-block}
.sm-more .sm-dd-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);padding:6px 8px;border-radius:10px;cursor:pointer}
.sm-more .sm-dd{position:absolute;top:calc(100% + 6px);right:0;min-width:160px;background:var(--surface);border:1px solid var(--line-strong);box-shadow:var(--shadow-2);border-radius:10px;overflow:hidden;display:none;z-index:1000;padding:6px 0}
.sm-more .sm-dd.show{display:block}
.sm-more .sm-dd a{display:flex;align-items:center;gap:10px;padding:10px 12px;text-decoration:none;color:inherit;cursor:pointer;background:transparent;border:0;width:100%;text-align:left;font-size:14px}
.sm-more .sm-dd a:hover{background:color-mix(in oklab,var(--muted-color) 6%,transparent)}
.sm-more .sm-dd .divider{height:1px;background:var(--line-strong);margin:6px 0}
.sm-icon-purple{color:#6f42c1}
.sm-icon-red{color:#dc3545}
.sm-icon-black{color:#111}
@media(max-width:720px){.sm-item{flex-direction:column;align-items:flex-start}.sm-item .right{width:100%;display:flex;justify-content:flex-end;gap:8px}.sm-more .sm-dd{right:6px;left:auto;min-width:160px}}
/* Modal layout safe */
.modal.show .modal-dialog { max-height: calc(100vh - 48px); }
.modal.show .modal-content { height: 100%; display:flex; flex-direction: column; }
.modal.show .modal-body { overflow:auto; max-height: calc(100vh - 200px); -webkit-overflow-scrolling: touch; }

/* small helper styles */
.qz-small-input { width:110px; }
</style>

<div class="crs-wrap">
  <div id="quizzesPanel" class="panel sm-card rounded-1 shadow-1" style="padding:18px; max-width:1100px; margin:18px auto;">
    <div class="d-flex align-items-center w-100 mb-2">
      <h2 class="panel-title d-flex align-items-center gap-2 mb-0">
        <i class="fa fa-question" style="color: var(--primary-color);"></i>
        Quizzes
      </h2>

      <div class="ms-auto d-flex gap-2 align-items-center">
        <button id="btn-open-quizzes-modal" class="btn btn-outline-primary btn-sm" type="button">Manage quizzes</button>
        <button id="btn-refresh-quizzes" class="btn btn-light btn-sm" title="Refresh"><i class="fa fa-rotate-right"></i></button>
      </div>
    </div>

    <div id="qz_list_wrap" style="margin-top:12px;">
      <div id="qz_list" class="sm-list"></div>

      <div id="qz_list_empty" class="sm-empty" style="display:none; margin-top:8px;">
        <div style="font-weight:600">No quizzes yet</div>
        <div class="text-muted small">Create quizzes and assign them to this batch.</div>
      </div>

      <div id="qz_list_loader" class="sm-loader" style="display:none; margin-top:8px;">
        <div class="spin" aria-hidden="true"></div>
        <div class="text-muted">Loading quizzes…</div>
      </div>

      <div id="qz_list_meta" class="small text-muted mt-2"></div>
    </div>
  </div>
</div>

<!-- Quizzes Modal -->
<div class="modal fade" id="quizzesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign / Manage Quizzes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3 d-flex gap-2 align-items-center">
          <input id="qz_q" class="form-control form-control-sm" placeholder="Search quizzes...">
          <select id="qz_assigned" class="form-select form-select-sm" style="width:150px;">
            <option value="all">All</option>
            <option value="assigned">Assigned</option>
            <option value="unassigned">Unassigned</option>
          </select>
          <select id="qz_per" class="form-select form-select-sm" style="width:90px;">
            <option value="10">10</option><option value="20" selected>20</option><option value="50">50</option>
          </select>
          <select id="qz_scope" class="form-select form-select-sm" style="width:160px;">
            <option value="batch" selected>Quizzes (Batch)</option>
            <option value="course">Quizzes (Course)</option>
            <option value="module">Quizzes (Module)</option>
          </select>
          <button id="qz_apply" class="btn btn-sm btn-primary">Apply</button>
        </div>

        <div id="qz_modal_list_wrap" style="margin-top:6px;">
          <div id="qz_modal_list" class="sm-list"></div>

          <div id="qz_modal_empty" class="sm-empty" style="display:none; margin-top:8px;">
            <div style="font-weight:600">No quizzes found</div>
          </div>

          <div id="qz_modal_loader" class="sm-loader" style="display:none; margin-top:8px;">
            <div class="spin" aria-hidden="true"></div>
            <div class="text-muted">Loading…</div>
          </div>

          <div id="qz_modal_meta" class="small text-muted mt-2"></div>
          <div id="qz_modal_pager" class="mt-2"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Quiz Details Modal -->
<div id="quizDetailsModal" class="modal" style="display:none;" aria-hidden="true">
  <div class="modal-dialog" style="max-width:680px; margin:80px auto;">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Quiz details</h5><button type="button" class="btn-close" id="quizDetailsClose"></button></div>
      <div class="modal-body" id="quizDetailsBody" style="padding:18px;"></div>
      <div class="modal-footer"><button class="btn btn-light" id="quizDetailsOk">Close</button></div>
    </div>
  </div>
</div>

<script>
/*
  Quizzes UI (full replacement)
  - Uses:
      GET  /api/quizz/by-batch/{batch}
      GET  /api/quizz/by-course/{course}
      GET  /api/quizz/by-module/{module}
      POST /api/batches/{batch}/quizzes/toggle
      PATCH /api/batches/{batch}/quizzes/update  (optional)
  - Expects auth token in localStorage/sessionStorage under 'token'
*/

(function(){
  // --- auth + role ---
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const role = (localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase();
  if(!TOKEN) {
    // keep behaviour consistent with your app
    try { Swal.fire({ icon:'warning', title:'Login required', text:'Please sign in to continue.', allowOutsideClick:false, allowEscapeKey:false }).then(()=>{ location.href = '/'; }); }
    catch(e){ location.href = '/'; }
    return;
  }

  const isAdmin = role.includes('admin') || role.includes('superadmin') || role.includes('super_admin');
  const isInstructor = role.includes('instructor');

  // --- DOM refs ---
  const btnOpenModal = document.getElementById('btn-open-quizzes-modal');
  const btnRefresh = document.getElementById('btn-refresh-quizzes');
  const listEl = document.getElementById('qz_list');
  const listEmpty = document.getElementById('qz_list_empty');
  const listLoader = document.getElementById('qz_list_loader');
  const listMeta = document.getElementById('qz_list_meta');

  // modal refs
  const qz_q = document.getElementById('qz_q');
  const qz_assigned = document.getElementById('qz_assigned');
  const qz_per = document.getElementById('qz_per');
  const qz_apply = document.getElementById('qz_apply');
  const qz_modal_list = document.getElementById('qz_modal_list');
  const qz_modal_loader = document.getElementById('qz_modal_loader');
  const qz_modal_empty = document.getElementById('qz_modal_empty');
  const qz_modal_meta = document.getElementById('qz_modal_meta');
  const qz_modal_pager = document.getElementById('qz_modal_pager');
  const qz_scope = document.getElementById('qz_scope');

  const quizDetailsModal = document.getElementById('quizDetailsModal');
  const quizDetailsBody = document.getElementById('quizDetailsBody');
  const quizDetailsClose = document.getElementById('quizDetailsClose');
  const quizDetailsOk = document.getElementById('quizDetailsOk');

  let qz_page = 1;

  function getBatchUuid(){
    const host = document.querySelector('.crs-wrap');
    if(host && (host.dataset.batchId || host.dataset.batch_id)) return host.dataset.batchId || host.dataset_batch_id || host.dataset.batch_id;
    // fallback to global var if you set it on page
    if(typeof currentBatchUuid !== 'undefined' && currentBatchUuid) return currentBatchUuid;
    return null;
  }

  function getCourseKey(){
    // optional - derive from URL path
    try {
      const parts = location.pathname.split('/').filter(Boolean);
      const last = parts.at(-1);
      if(!last) return null;
      return last;
    } catch(e){ return null; }
  }

  function getModuleKey(){
    const host = document.querySelector('.crs-wrap');
    if(host && (host.dataset.moduleId || host.dataset.module_id)) return host.dataset.moduleId || host.dataset.module_id;
    const q = (new URL(window.location.href)).searchParams.get('module') || (new URL(window.location.href)).searchParams.get('course_module_id');
    return q || null;
  }

  async function apiFetch(url, opts = {}){
    opts.headers = Object.assign({}, opts.headers || {}, { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' });
    const res = await fetch(url, opts);
    if(res.status === 401){
      try{ await Swal.fire({ icon:'warning', title:'Session expired', text:'Please login again.' }); }catch(e){}
      location.href = '/';
      throw new Error('Unauthorized');
    }
    return res;
  }

  // toggling assignment/publish/attempts
  async function backendToggle(batchUuid, payload){
    const url = `/api/batches/${encodeURIComponent(batchUuid)}/quizzes/toggle`;
    const res = await apiFetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(j?.message || 'Toggle failed');
    return j;
  }

  async function doToggle(batchUuid, payload, checkboxEl=null, quiet=false){
    try {
      const j = await backendToggle(batchUuid, payload);
      if(!quiet) Swal.fire({ toast:true, position:'top-end', icon:'success', title: payload.assigned ? 'Quiz assigned' : 'Quiz updated', showConfirmButton:false, timer:1400 });
      return j;
    } catch (e) {
      if(checkboxEl) checkboxEl.checked = !checkboxEl.checked;
      console.error(e);
      if(!quiet) Swal.fire('Action failed', e.message || 'Unable to complete action', 'error');
      throw e;
    }
  }

  // create single quiz list item (UI matches your spec)
  function createQuizListItem(item){
    const wrap = document.createElement('div'); wrap.className = 'sm-item';
    wrap.style.marginBottom = '8px';

    const left = document.createElement('div'); left.className = 'left';
    const icon = document.createElement('div'); icon.className = 'icon';
    icon.style.cssText = 'width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;border:1px solid var(--line-strong);background:linear-gradient(180deg, rgba(0,0,0,0.02), transparent);';
    icon.innerHTML = '<i class="fa fa-question" style="color:var(--secondary-color)"></i>';
    const meta = document.createElement('div'); meta.className = 'meta';
    const title = document.createElement('div'); title.className = 'title'; title.textContent = item.title || item.quiz_name || (`Quiz #${item.id||'?'}`);
    const sub = document.createElement('div'); sub.className = 'sub'; sub.style.fontSize='13px'; sub.style.color='var(--muted-color)';
    let subText = '';
    if(item.total_marks != null) subText += (item.total_marks+' marks');
    if(item.description) subText += (subText? ' • ':'') + item.description.slice(0,80);
    sub.textContent = subText || '-';
    meta.appendChild(title); meta.appendChild(sub);
    left.appendChild(icon); left.appendChild(meta);

    const right = document.createElement('div'); right.className = 'right';
    right.style.display='flex'; right.style.alignItems='center'; right.style.gap='8px';

    // attempts input
    const attemptsInput = document.createElement('input');
    attemptsInput.type = 'number'; attemptsInput.min = '0'; attemptsInput.className = 'form-control form-control-sm qz-small-input';
    attemptsInput.value = (typeof item.attempt_allowed !== 'undefined' && item.attempt_allowed !== null) ? String(item.attempt_allowed) : '';
    right.appendChild(attemptsInput);

    // publish toggle
    const pubWrap = document.createElement('div'); pubWrap.className='form-check form-switch';
    const pubInput = document.createElement('input'); pubInput.type='checkbox'; pubInput.className='form-check-input'; pubInput.checked = !!item.publish_to_students;
    pubWrap.appendChild(pubInput); right.appendChild(pubWrap);

    // assign toggle
    const assignWrap = document.createElement('div'); assignWrap.className='form-check form-switch';
    const assignInput = document.createElement('input'); assignInput.type='checkbox'; assignInput.className='form-check-input'; assignInput.dataset.id = item.id; assignInput.checked = !!item.assigned;
    assignWrap.appendChild(assignInput); right.appendChild(assignWrap);

    // 3-dots
    const moreWrap = document.createElement('div'); moreWrap.className='sm-more'; moreWrap.style.marginLeft='4px';
    moreWrap.innerHTML = `
      <button class="sm-dd-btn" aria-haspopup="true" aria-expanded="false" title="More">⋮</button>
      <div class="sm-dd" role="menu" aria-hidden="true">
        <a href="#" data-action="view"><i class="fa fa-eye sm-icon-purple"></i><span>View</span></a>
        ${ (isAdmin||isInstructor) ? `<a href="#" data-action="edit"><i class="fa fa-pen sm-icon-black"></i><span>Edit</span></a>` : '' }
        <div class="divider"></div>
        <a href="#" data-action="delete" class="text-danger"><i class="fa fa-trash sm-icon-red"></i><span>Unassign</span></a>
      </div>
    `;
    right.appendChild(moreWrap);

    wrap.appendChild(left); wrap.appendChild(right);

    // dropdown wiring
    const ddBtn = moreWrap.querySelector('.sm-dd-btn'), dd = moreWrap.querySelector('.sm-dd');
    ddBtn.addEventListener('click', (ev)=>{ ev.stopPropagation(); const isOpen = dd.classList.contains('show'); document.querySelectorAll('.sm-more .sm-dd.show').forEach(d => d.classList.remove('show')); if(!isOpen){ dd.classList.add('show'); dd.setAttribute('aria-hidden','false'); ddBtn.setAttribute('aria-expanded','true'); } else { dd.classList.remove('show'); ddBtn.setAttribute('aria-expanded','false'); } });

    // actions
    moreWrap.querySelector('[data-action="view"]').addEventListener('click', (ev)=>{ ev.preventDefault(); ev.stopPropagation(); openQuizDetails(item); dd.classList.remove('show'); });
    const editEl = moreWrap.querySelector('[data-action="edit"]');
    if(editEl) editEl.addEventListener('click', (ev)=>{ ev.preventDefault(); ev.stopPropagation(); dd.classList.remove('show'); /* open edit screen if you have */ });

    moreWrap.querySelector('[data-action="delete"]').addEventListener('click', async (ev)=>{ ev.preventDefault(); ev.stopPropagation(); dd.classList.remove('show'); if(!confirm('Unassign this quiz from the batch?')) return; try{ await doToggle(getBatchUuid(), { quiz_id: item.id, assigned: false, publish_to_students: !!pubInput.checked, attempt_allowed: attemptsInput.value!==''?Number(attemptsInput.value):null }); await loadQuizzesModal(); await loadMain(); }catch(e){ console.error(e); Swal.fire('Unassign failed','Unable to unassign','error'); } });

    // attempts blur => save
    attemptsInput.addEventListener('blur', async ()=> {
      const raw = attemptsInput.value;
      const attemptsVal = raw === '' ? null : Number(raw);
      const payload = { quiz_id: item.id, assigned: !!assignInput.checked, publish_to_students: !!pubInput.checked };
      if(attemptsVal !== null && !Number.isNaN(attemptsVal)) payload.attempt_allowed = attemptsVal;
      try { await doToggle(getBatchUuid(), payload, null, true); } catch(e){ console.error(e); Swal.fire('Save failed','Unable to save attempts','error'); }
    });

    pubInput.addEventListener('change', async ()=> {
      const val = attemptsInput.value !== '' ? Number(attemptsInput.value) : null;
      const payload = { quiz_id: item.id, assigned: !!assignInput.checked, publish_to_students: !!pubInput.checked };
      if(val !== null && !Number.isNaN(val)) payload.attempt_allowed = val;
      try{ await doToggle(getBatchUuid(), payload, null, true); } catch(e){ console.error(e); Swal.fire('Update failed','Unable to update publish','error'); }
    });

    assignInput.addEventListener('change', async ()=> {
      const assigned = !!assignInput.checked;
      const val = attemptsInput.value !== '' ? Number(attemptsInput.value) : null;
      const payload = { quiz_id: item.id, assigned: assigned, publish_to_students: !!pubInput.checked };
      if(val !== null && !Number.isNaN(val)) payload.attempt_allowed = val;
      try {
        await doToggle(getBatchUuid(), payload, assignInput);
        await loadQuizzesModal(); await loadMain();
      } catch(e){ console.error(e); }
    });

    // close dropdowns on outside click
    document.addEventListener('click', ()=>{ try{ dd.classList.remove('show'); ddBtn.setAttribute('aria-expanded','false'); }catch(e){} });

    return wrap;
  }

  // render modal list with pagination UI
  function renderModalList(items, pag){
    qz_modal_list.innerHTML = '';
    if(!items || !items.length){ qz_modal_empty.style.display = ''; qz_modal_meta.textContent = ''; qz_modal_pager.innerHTML = ''; return; }
    qz_modal_empty.style.display = 'none';
    const frag = document.createDocumentFragment();
    items.forEach(i => frag.appendChild(createQuizListItem(i)));
    qz_modal_list.appendChild(frag);

    if(pag){
      const cur = Number(pag.current_page||1), total = Number(pag.total||items.length), per = Number(pag.per_page||20);
      qz_modal_meta.textContent = `Page ${cur} of ${Math.max(1,Math.ceil(total/per))} — ${total} quiz(es)`;
      const pages = Math.max(1, Math.ceil(total/per));
      let html = '<ul class="pagination pagination-sm mb-0">';
      html += `<li class="page-item ${cur<=1?'disabled':''}"><a class="page-link" href="javascript:void(0)" data-page="${cur-1}">Prev</a></li>`;
      const windowSize = 5;
      const start = Math.max(1, cur - Math.floor(windowSize/2)), end = Math.min(pages, start + windowSize - 1);
      for(let p = start; p <= end; p++){ html += `<li class="page-item ${p===cur?'active':''}"><a class="page-link" href="javascript:void(0)" data-page="${p}">${p}</a></li>`; }
      html += `<li class="page-item ${cur>=pages?'disabled':''}"><a class="page-link" href="javascript:void(0)" data-page="${cur+1}">Next</a></li>`;
      html += '</ul>';
      qz_modal_pager.innerHTML = html;
      qz_modal_pager.querySelectorAll('a.page-link[data-page]').forEach(a => a.addEventListener('click', (ev) => {
        const t = Number(a.dataset.page);
        if(!t || t === qz_page) return;
        qz_page = t;
        loadQuizzesModal();
      }));
    } else {
      qz_modal_meta.textContent = `${items.length} quiz(es)`;
      qz_modal_pager.innerHTML = '';
    }
  }

  // quiz details modal
  function openQuizDetails(item){
    if(!quizDetailsModal || !quizDetailsBody) return;
    quizDetailsBody.innerHTML = `
      <div style="font-size:15px">
        <div><strong>Title:</strong> ${escapeHtml(item.title || item.quiz_name || '')}</div>
        <div style="margin-top:8px"><strong>Marks:</strong> ${escapeHtml(String(item.total_marks ?? '-'))}</div>
        <div style="margin-top:8px"><strong>Attempts allowed:</strong> ${escapeHtml(String(item.attempt_allowed ?? '-'))}</div>
        <div style="margin-top:8px"><strong>Description:</strong><div class="small text-muted" style="margin-top:6px">${escapeHtml(item.description || item.quiz_description || '-')}</div></div>
        <div style="margin-top:10px;color:var(--muted-color);font-size:13px"><strong>ID:</strong> ${escapeHtml(String(item.id||''))}</div>
      </div>
    `;
    // show modal
    try {
      const bsModal = new bootstrap.Modal(quizDetailsModal); bsModal.show();
    } catch(e){
      quizDetailsModal.style.display='block'; quizDetailsModal.classList.add('show'); document.body.classList.add('modal-open');
    }
    quizDetailsClose.onclick = () => { try{ bootstrap.Modal.getInstance(quizDetailsModal).hide(); }catch(e){ quizDetailsModal.style.display='none'; quizDetailsModal.classList.remove('show'); document.body.classList.remove('modal-open'); } };
    quizDetailsOk.onclick = quizDetailsClose.onclick;
  }

  function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"'`=\/]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60','=':'&#x3D;'}[ch])); }

  // load main assigned quizzes (uses GET /api/quizz/by-batch/{batch})
  async function loadMain(){
    listEl.innerHTML = ''; listEmpty.style.display = 'none'; listLoader.style.display = '';
    const batchUuid = getBatchUuid();
    if(!batchUuid){ listEmpty.style.display=''; listLoader.style.display='none'; listMeta.textContent = 'Batch context missing'; return; }
    try {
      // call the new quizz/by-batch endpoint
      const res = await apiFetch(`/api/quizz/by-batch/${encodeURIComponent(batchUuid)}`);
      const j = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message || 'Failed to load');
      const modulesWithQuizzes = j.data?.modules_with_quizzes || [];
      let assigned = [];
      modulesWithQuizzes.forEach(mg => {
        (mg.quizzes || mg.materials || []).forEach(q => {
          // ensure consistent keys: id, title, attempt_allowed, publish_to_students, assigned
          assigned.push(Object.assign({}, q, { title: q.quiz_name || q.title }));
        });
      });
      if(!assigned.length){ listEmpty.style.display=''; listMeta.textContent = '0 quizzes'; return; }
      const frag = document.createDocumentFragment();
      assigned.forEach(i => frag.appendChild(createQuizListItem(i)));
      listEl.appendChild(frag);
      listMeta.textContent = `${assigned.length} quiz(es) assigned to this batch`;
    } catch(e) {
      console.error('loadMain error', e);
      listEmpty.style.display=''; listMeta.textContent = 'Failed to load';
    } finally { listLoader.style.display = 'none'; }
  }

  // load modal list (supports batch/course/module scopes using the quizz endpoints)
  async function loadQuizzesModal(){
    qz_modal_list.innerHTML = ''; qz_modal_empty.style.display = 'none'; qz_modal_loader.style.display = '';
    const scope = (qz_scope && qz_scope.value) ? qz_scope.value : 'batch';
    const q = (qz_q && qz_q.value) ? qz_q.value.trim() : '';
    const per = Number(qz_per?.value || 20);
    const assigned = qz_assigned?.value || 'all';
    try {
      let url = null;
      const batchUuid = getBatchUuid();
      if(scope === 'batch') {
        if(!batchUuid) throw new Error('Batch context required');
        // we call the batch endpoint but pass pagination/query to server via query string if supported
        const params = new URLSearchParams(); params.set('per_page', per); params.set('page', qz_page); if(q) params.set('q', q);
        if(assigned === 'assigned') params.set('assigned','1'); if(assigned === 'unassigned') params.set('assigned','0');
        url = `/api/batches/${encodeURIComponent(batchUuid)}/quizzes?` + params.toString();
      } else if(scope === 'course') {
        // try courseKey from DOM or URL
        const courseKey = getCourseKey();
        if(!courseKey) throw new Error('Course context required');
        const params = new URLSearchParams(); params.set('per_page', per); params.set('page', qz_page); if(q) params.set('q', q);
        url = `/api/quizz/by-course/${encodeURIComponent(courseKey)}?` + params.toString();
      } else if(scope === 'module') {
        const moduleKey = getModuleKey();
        if(!moduleKey) throw new Error('Module context required');
        const params = new URLSearchParams(); params.set('per_page', per); params.set('page', qz_page); if(q) params.set('q', q);
        url = `/api/quizz/by-module/${encodeURIComponent(moduleKey)}?` + params.toString();
      }

      const res = await apiFetch(url);
      const j = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j?.message || 'Failed to load quizzes');

      // server may return either data.modules_with_quizzes (grouped) OR data (flat array)
      let items = [];
      if(Array.isArray(j.data)) {
        items = j.data;
      } else if (j.data && Array.isArray(j.data.modules_with_quizzes)) {
        // flatten grouped response
        j.data.modules_with_quizzes.forEach(g => {
          (g.quizzes || g.materials || g.materials || []).forEach(qi => items.push(Object.assign({}, qi, { title: qi.quiz_name || qi.title })));
        });
      } else if (j.data && Array.isArray(j.data.quizzes)) {
        items = j.data.quizzes;
      }

      // attempt to read pagination
      const pag = j.pagination || j.data?.pagination || { current_page: qz_page, per_page: per, total: items.length };
      renderModalList(items, pag);
    } catch(e) {
      console.error('loadQuizzesModal error', e);
      qz_modal_empty.style.display = ''; qz_modal_meta.textContent = 'Failed to load';
    } finally { qz_modal_loader.style.display = 'none'; }
  }

  // wire events
  if(btnOpenModal) btnOpenModal.addEventListener('click', ()=>{
    try {
      const modal = new bootstrap.Modal(document.getElementById('quizzesModal'));
      qz_page = 1; if(qz_q) qz_q.value = ''; if(qz_assigned) qz_assigned.value = 'all'; if(qz_scope) qz_scope.value = 'batch';
      modal.show(); loadQuizzesModal();
    } catch(e){
      document.getElementById('quizzesModal').style.display = 'block'; loadQuizzesModal();
    }
  });
  if(btnRefresh) btnRefresh.addEventListener('click', ()=> { loadMain(); });
  if(qz_apply) qz_apply.addEventListener('click', ()=>{ qz_page = 1; loadQuizzesModal(); });
  if(qz_q) { let t; qz_q.addEventListener('input', ()=>{ clearTimeout(t); t = setTimeout(()=>{ qz_page = 1; loadQuizzesModal(); }, 350); }); }
  if(qz_per) qz_per.addEventListener('change', ()=>{ qz_page = 1; loadQuizzesModal(); });
  if(qz_assigned) qz_assigned.addEventListener('change', ()=>{ qz_page = 1; loadQuizzesModal(); });
  if(qz_scope) qz_scope.addEventListener('change', ()=>{ qz_page = 1; loadQuizzesModal(); });

  // export quick helpers
  window.loadQuizzesModal = loadQuizzesModal;
  window.loadMainQuizzes = loadMain;

  // initial load
  setTimeout(()=> loadMain(), 50);
})();
</script>
