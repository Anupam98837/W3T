{{-- resources/views/landing/manageContact.blade.php --}}
@extends('pages.users.admin.layout.structure')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
.cm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
/* Reorder mode: show move cursor on contact rows */
body.contact-reorder-mode #rows-active tr {
  cursor: move;
}

/* Sorting */
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Row state cues */
tr.state-deleted td{background:color-mix(in oklab, var(--danger-color) 6%, transparent)}

/* Dropdowns inside table (portal safe) */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:180px;z-index:5000}
.dropdown-menu.dd-portal{position:fixed!important;left:0;top:0;transform:none!important;z-index:5000;border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:180px;background:var(--surface)}

.icon-btn{display:inline-flex;align-items:center;justify-content:center;height:34px;min-width:34px;padding:0 10px;border:1px solid var(--line-strong);background:var(--surface);border-radius:10px}
.icon-btn:hover{box-shadow:var(--shadow-1)}

.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

/* Modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control,.form-select,textarea{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control,html.theme-dark .form-select,html.theme-dark textarea{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}

/* Prevent clipping */
.table-responsive,
.table-wrap,
.card,
.panel,
.cm-wrap {
  transform: none !important;
}
</style>
@endpush

@section('content')
<div class="cm-wrap">
<div class="panel mb-3">
<div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Manage Contacts</label>
      </div>
</div>
  {{-- ===== Toolbar ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Per page</label>
        <select id="per_page" class="form-select" style="width:96px;">
          <option>10</option><option>20</option><option selected>30</option><option>50</option><option>100</option>
        </select>
      </div>

      <div class="position-relative" style="min-width:260px;">
        <input id="q" type="text" class="form-control ps-5" placeholder="Search type / value / icon…">
        <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      </div>

      

      <button id="btnReset" class="btn btn-primary"><i class="fa fa-rotate-left me-1"></i>Reset</button>
    
<button id="btnReorder" class="btn btn-outline-secondary">
  <i class="fa fa-arrows-up-down-left-right me-1"></i>Reorder
</button>
    </div>

    <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end gap-2">
      <button id="btnCreateContact" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>New Contact
      </button>
    </div>

    <div class="col-12 col-xl-auto ms-xl-auto d-flex justify-content-xl-end small text-muted">
      Sorting: <span id="sortHint" class="ms-1">Newest first</span>
    </div>
  </div>

  {{-- ===== Table ===== --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
            <tr>
              <th style="width:5%"></th>
              <th class="sortable" data-col="contact_key" style="width:16%">TYPE <span class="caret"></span></th>
              <th style="width:35%">VALUE</th>
              <th style="width:18%">ICON</th>
              <th class="sortable" data-col="display_order" style="width:10%">ORDER <span class="caret"></span></th>
              <th class="sortable" data-col="created_at" style="width:18%">CREATED <span class="caret"></span></th>
              <th class="text-end" style="width:90px;">ACTIONS</th>
            </tr>
          </thead>
          <tbody id="rows-active">
            <tr id="loaderRow-active" style="display:none;">
              <td colspan="7" class="p-0">
                <div class="p-4">
                  <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="empty-active" class="empty p-4 text-center" style="display:none;">
        <i class="fa fa-address-book mb-2" style="font-size:32px; opacity:.6;"></i>
        <div>No contacts found.</div>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="metaTxt-active">—</div>
        <nav style="position:relative; z-index:1;">
          <ul id="pager-active" class="pagination mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>

</div>

{{-- ===== Create / Edit Contact Modal ===== --}}
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="ct_title" class="modal-title">
          <i class="fa fa-address-book me-2"></i>Create Contact
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="ct_mode" value="create">
        <input type="hidden" id="ct_id" value="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select id="ct_key" class="form-select">
              <option value="">Select type…</option>
              <option value="phone">Phone</option>
              <option value="whatsapp">WhatsApp</option>
              <option value="email">Email</option>
              <option value="address">Address</option>
              <option value="custom">Custom</option>
            </select>
          </div>

          <div class="col-md-8">
            <label class="form-label">Value <span class="text-danger">*</span></label>
            <input id="ct_value" class="form-control" placeholder="e.g. +91-98765-43210">
          </div>

          <div class="col-md-6">
            <label class="form-label">Icon (Font Awesome class)</label>
            <input id="ct_icon" class="form-control" placeholder="e.g. fa-solid fa-phone">
          </div>

          <div class="col-md-6">
            <label class="form-label">Image path (optional)</label>
            <input id="ct_image" class="form-control" placeholder="/assets/icons/whatsapp.svg">
          </div>

          <div class="col-md-4">
            <label class="form-label">Display order</label>
            <input id="ct_order" type="number" min="0" class="form-control" value="0">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="ct_save" class="btn btn-primary">
          <i class="fa fa-save me-1"></i>Save
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
  <div id="okToast" class="toast text-bg-success border-0">
    <div class="d-flex">
      <div id="okMsg" class="toast-body">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="errToast" class="toast text-bg-danger border-0 mt-2">
    <div class="d-flex">
      <div id="errMsg" class="toast-body">Something went wrong</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  /* ===== Small helpers ===== */
  const esc = (s)=>{const m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>\"'`]/g,ch=>m[ch]); };
  const fmtDate = (iso)=>{ if(!iso) return '-'; const d=new Date(iso); if(isNaN(d)) return esc(iso); return d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'}); };
  const qs=(sel)=>document.querySelector(sel);
  const show=(el,v)=>{ if(!el) return; el.style.display = v ? '' : 'none'; };

  /* Toasts */
  const contactOkToast  = new bootstrap.Toast(document.getElementById('okToast'));
  const contactErrToast = new bootstrap.Toast(document.getElementById('errToast'));

  function contactOk(msg) {
    document.getElementById('okMsg').textContent = msg || 'Done';
    contactOkToast.show();
  }

  function contactErr(msg) {
    document.getElementById('errMsg').textContent = msg || 'Something went wrong';
    contactErrToast.show();
  }

  /* Elements */
  const perPageSel = qs('#per_page');
  const q          = qs('#q');
  const sortHint   = qs('#sortHint');
  const btnReset   = qs('#btnReset');
  const btnCreate  = qs('#btnCreateContact');
  const btnReorder = qs('#btnReorder');

  const rowsEl  = qs('#rows-active');
  const loader  = qs('#loaderRow-active');
  const emptyEl = qs('#empty-active');
  const pager   = qs('#pager-active');
  const metaTxt = qs('#metaTxt-active');

  /* Modal refs – lazy init */
  const contactModalEl = document.getElementById('contactModal');
  let contactModalInstance = null;

  function getContactModal() {
    if (contactModalEl && !contactModalInstance) {
      contactModalInstance = bootstrap.Modal.getOrCreateInstance(contactModalEl);
    }
    return contactModalInstance;
  }

  const ct = {
    mode: qs('#ct_mode'),
    id: qs('#ct_id'),
    title: qs('#ct_title'),
    key: qs('#ct_key'),
    value: qs('#ct_value'),
    icon: qs('#ct_icon'),
    image: qs('#ct_image'),
    order: qs('#ct_order'),
    save: qs('#ct_save')
  };

  /* Save button loading state */
  let contactSaveOriginalHTML = ct.save ? ct.save.innerHTML : '';

  function contactSetSaveLoading(on){
    if(!ct.save) return;
    ct.save.disabled = on;
    if(on){
      if(!contactSaveOriginalHTML) contactSaveOriginalHTML = ct.save.innerHTML;
      ct.save.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving…';
    }else{
      ct.save.innerHTML = contactSaveOriginalHTML || '<i class="fa fa-save me-1"></i>Save';
    }
  }

  let state = { page:1 };

  // 🔁 DEFAULT SORT = display_order (ascending)
  let sort  = 'display_order';

  // Reorder state
  let reorderMode      = false;
  let sortableInstance = null;
  let sortHintDefault  = sortHint ? sortHint.textContent : '';

  /* ===== Reorder helpers ===== */
  function enableReorderMode() {
    if (reorderMode) return;
    reorderMode = true;

    document.body.classList.add('contact-reorder-mode');

    if (btnReorder) {
      btnReorder.classList.remove('btn-outline-secondary');
      btnReorder.classList.add('btn-success');
      btnReorder.innerHTML = '<i class="fa fa-floppy-disk me-1"></i>Save order';
    }

    if (sortHint) {
      if (!sortHint.dataset.defaultText) {
        sortHint.dataset.defaultText = sortHint.textContent || '';
      }
      sortHint.textContent = 'Reorder mode: drag rows, then click Save order';
    }

    if (rowsEl && !sortableInstance) {
      sortableInstance = Sortable.create(rowsEl, {
        animation: 150,
        handle: '.fa-grip-lines-vertical'   // drag using the grip icon
      });
    }
  }

  function disableReorderMode() {
    if (!reorderMode) return;
    reorderMode = false;

    document.body.classList.remove('contact-reorder-mode');

    if (btnReorder) {
      btnReorder.classList.remove('btn-success');
      btnReorder.classList.add('btn-outline-secondary');
      btnReorder.innerHTML = '<i class="fa fa-arrows-up-down-left-right me-1"></i>Reorder';
    }

    if (sortableInstance) {
      sortableInstance.destroy();
      sortableInstance = null;
    }

    if (sortHint && sortHint.dataset.defaultText !== undefined) {
      sortHint.textContent = sortHint.dataset.defaultText;
      delete sortHint.dataset.defaultText;
    }
  }

  /* Sorting header sync (no more "Newest first") */
  function syncSortHeaders(){
    document.querySelectorAll('thead th.sortable').forEach(th=>{
      th.classList.remove('asc','desc');
      const col=th.dataset.col;
      if(sort===col) th.classList.add('asc');
      if(sort==='-'+col) th.classList.add('desc');
    });

    if (!sortHint) return;

    if (sort === 'display_order') {
      sortHint.textContent = 'By order';
    } else if (sort === '-display_order') {
      sortHint.textContent = 'Reverse order';
    } else {
      sortHint.textContent = 'Sorted';
    }
  }

  /* Build URL for GET */
  function baseParams(){
    const usp = new URLSearchParams();
    const per = Number(perPageSel?.value || 30);
    const pg  = Number(state.page || 1);
    usp.set('per_page', per);
    usp.set('page', pg);
    usp.set('sort', sort);
    if(q && q.value.trim()) usp.set('q', q.value.trim());
    return usp.toString();
  }

  function listUrl(){
    return "{{ route('landing.contact.index') }}" + '?' + baseParams();
  }

  /* Row html */
  function rowHTML(r){
    const tr=document.createElement('tr');
    tr.dataset.id = r.id;     // needed for reorder
    const icon = r.icon ? `<i class="${esc(r.icon)} me-1"></i><span class="small">${esc(r.icon)}</span>` : '<span class="text-muted small">—</span>';
    const img  = r.image_path ? `<span class="small">${esc(r.image_path)}</span>` : '';
    tr.innerHTML = `
      <td class="text-center">
        <i class="fa fa-grip-lines-vertical text-muted" style="opacity:.35;"></i>
      </td>
      <td class="fw-semibold text-uppercase small">${esc(r.contact_key || '-')}</td>
      <td>
        <div>${esc(r.value || '-')}</div>
        ${img ? `<div class="small text-muted">${img}</div>` : ''}
      </td>
      <td>${icon}</td>
      <td>${Number(r.display_order ?? 0)}</td>
      <td>${fmtDate(r.created_at)}</td>
      <td class="text-end">
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <button class="dropdown-item" data-act="edit" data-id="${r.id}">
                <i class="fa fa-pen-to-square"></i> Edit
              </button>
            </li>
            <li>
              <button class="dropdown-item text-danger" data-act="delete" data-id="${r.id}">
                <i class="fa fa-trash"></i> Delete
              </button>
            </li>
          </ul>
        </div>
      </td>`;
    return tr;
  }

  /* Load list */
  function clearBody(){
    if(!rowsEl) return;
    rowsEl.querySelectorAll('tr:not(#loaderRow-active)').forEach(n=>n.remove());
  }
  function showLoader(v){ show(loader, v); }

  function load(){
    clearBody(); show(emptyEl,false); pager.innerHTML=''; metaTxt.textContent='—'; showLoader(true);

    fetch(listUrl(), {
      headers:{
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest'
      }
    })
    .then(r=>r.json().then(j=>({ok:r.ok, j})))
    .then(({ok,j})=>{
      if(!ok) throw new Error(j?.message || 'Load failed');
      const items = j.data || [];
      const pag   = j.pagination || {page:1,per_page:items.length,total:items.length};

      if(!items.length) show(emptyEl,true);

      const frag=document.createDocumentFragment();
      items.forEach(r=>frag.appendChild(rowHTML(r)));
      rowsEl.appendChild(frag);

      // if we are in reorder mode and table is reloaded, re-init Sortable
      if (reorderMode && !sortableInstance && rowsEl) {
        sortableInstance = Sortable.create(rowsEl, {
          animation: 150,
          handle: '.fa-grip-lines-vertical'
        });
      }

      // pagination
      const total=Number(pag.total||0), per=Number(pag.per_page||30), cur=Number(pag.page||1);
      const pages=Math.max(1, Math.ceil(total/per));
      const li=(dis,act,label,t)=>`<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
        <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a></li>`;
      let html='';
      html+=li(cur<=1,false,'Previous',cur-1);
      const w=3,s=Math.max(1,cur-w),e=Math.min(pages,cur+w);
      if(s>1){ html+=li(false,false,1,1); if(s>2) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; }
      for(let i=s;i<=e;i++) html+=li(false,i===cur,i,i);
      if(e<pages){ if(e<pages-1) html+='<li class="page-item disabled"><span class="page-link">…</span></li>'; html+=li(false,false,pages,pages); }
      html+=li(cur>=pages,false,'Next',cur+1);
      pager.innerHTML=html;
      pager.querySelectorAll('a.page-link[data-page]').forEach(a=>{
        a.addEventListener('click',()=>{
          const t=Number(a.dataset.page);
          if(!t || t===state.page) return;
          state.page=Math.max(1,t);
          disableReorderMode();
          load();
        });
      });

      metaTxt.textContent = `Showing page ${cur} of ${pages} — ${total} result(s)`;
    })
    .catch(e=>{
      console.error(e);
      show(emptyEl,true);
      metaTxt.textContent='Failed to load';
      contactErr(e.message||'Load error');
    })
    .finally(()=> showLoader(false));
  }

  /* Sorting click */
  document.querySelectorAll('thead th.sortable').forEach(th=>{
    th.addEventListener('click',()=>{
      const col = th.dataset.col;

      // toggle asc/desc for clicked column
      if(sort===col) sort='-'+col;
      else if(sort==='-'+col) sort=col;
      else sort = col; // default to clicked col ascending

      state.page=1;
      disableReorderMode();
      syncSortHeaders();
      load();
    });
  });

  /* Search + reset */
  let srT;
  if(q){
    q.addEventListener('input', ()=>{
      clearTimeout(srT);
      srT=setTimeout(()=>{
        state.page=1;
        disableReorderMode();
        load();
      }, 350);
    });
  }

  btnReset?.addEventListener('click', ()=>{
    if(q) q.value='';
    if(perPageSel) perPageSel.value='30';
    sort='display_order';          // 🔁 reset = by order
    state.page=1;
    disableReorderMode();
    syncSortHeaders();
    load();
  });

  perPageSel?.addEventListener('change', ()=>{
    state.page=1;
    disableReorderMode();
    load();
  });

  /* Modal open helpers */
  function openCreate(){
    ct.mode.value='create';
    ct.id.value='';
    ct.title.textContent='Create Contact';
    ct.key.value='';
    ct.value.value='';
    ct.icon.value='';
    ct.image.value='';
    ct.order.value='0';
    const m = getContactModal();
    if(m) m.show();
  }

  function openEdit(id){
    fetch("{{ route('landing.contact.index') }}" + '?id='+encodeURIComponent(id),{
      headers:{
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest'
      }
    })
    .then(r=>r.json().then(j=>({ok:r.ok,j})))
    .then(({ok,j})=>{
      if(!ok) throw new Error(j?.message||'Load failed');
      const list = j.data || [];
      const row  = list.find(x=>String(x.id)===String(id));
      if(!row){
        throw new Error('Contact not found');
      }
      ct.mode.value='edit';
      ct.id.value=row.id;
      ct.title.textContent='Edit Contact';
      ct.key.value   = row.contact_key || '';
      ct.value.value = row.value || '';
      ct.icon.value  = row.icon || '';
      ct.image.value = row.image_path || '';
      ct.order.value = row.display_order ?? 0;
      const m = getContactModal();
      if(m) m.show();
    })
    .catch(e=>{ contactErr(e.message||'Failed to load contact'); });
  }

  btnCreate?.addEventListener('click', openCreate);

  /* Save (create / update) */
  ct.save?.addEventListener('click', ()=>{
    const key   = ct.key.value.trim();
    const value = ct.value.value.trim();
    const icon  = ct.icon.value.trim();
    const img   = ct.image.value.trim();
    const order = ct.order.value !== '' ? Number(ct.order.value) : 0;

    if(!key)   return Swal.fire('Type required','Please select contact type.','info');
    if(!value) return Swal.fire('Value required','Please enter value.','info');

    const isEdit = (ct.mode.value==='edit' && ct.id.value);
    const url    = isEdit
      ? "{{ url('api/landing/contact') }}/" + encodeURIComponent(ct.id.value)
      : "{{ route('landing.contact.store') }}";
    const method = isEdit ? 'PUT' : 'POST';

    const payload = {
      contact_key: key,
      value: value,
      icon: icon || null,
      image_path: img || null,
      display_order: order
    };

    contactSetSaveLoading(true);

    fetch(url,{
      method,
      headers:{
        'Accept':'application/json',
        'Content-Type':'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With':'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    })
    .then(r=>r.json().then(j=>({ok:r.ok,j})))
    .then(({ok,j})=>{
      if(!ok) throw new Error(j?.message||'Save failed');
      contactOk('Contact saved');
      const m = getContactModal();
      if(m) m.hide();
      load();
    })
    .catch(e=>{
      contactErr(e.message||'Save failed');
    })
    .finally(()=>{ contactSetSaveLoading(false); });
  });

  /* Actions: edit / delete */
  document.addEventListener('click', async (e)=>{
    const it = e.target.closest('.dropdown-item[data-act]');
    if(!it) return;
    const act = it.dataset.act;
    const id  = it.dataset.id;
    if(act==='edit'){
      openEdit(id);
      return;
    }
    if(act==='delete'){
      const {isConfirmed} = await Swal.fire({
        icon:'warning',
        title:'Delete contact?',
        text:'This will remove the contact from the list.',
        showCancelButton:true,
        confirmButtonText:'Delete',
        confirmButtonColor:'#ef4444'
      });
      if(!isConfirmed) return;
      fetch("{{ url('api/landing/contact') }}/"+encodeURIComponent(id),{
        method:'DELETE',
        headers:{
          'Accept':'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With':'XMLHttpRequest'
        }
      })
      .then(r=>r.json().then(j=>({ok:r.ok,j})))
      .then(({ok,j})=>{
        if(!ok) throw new Error(j?.message||'Delete failed');
        contactOk('Contact deleted');
        load();
      })
      .catch(e=> contactErr(e.message||'Delete failed'));
    }
  });

  /* Dropdown portal */
  (function(){
    let activePortal=null;
    const place=(menu, btnRect)=>{
      const vw=Math.max(document.documentElement.clientWidth, window.innerWidth||0);
      menu.classList.add('dd-portal'); menu.style.display='block'; menu.style.visibility='hidden';
      document.body.appendChild(menu);
      const mw=menu.offsetWidth, mh=menu.offsetHeight;
      let left = (vw - btnRect.right < mw && btnRect.right - mw > 8) ? (btnRect.right - mw) : btnRect.left;
      let top  = btnRect.bottom + 4;
      const vh=Math.max(document.documentElement.clientHeight, window.innerHeight||0);
      if(top + mh > vh - 8) top = Math.max(8, vh - mh - 8);
      menu.style.left = left + 'px'; menu.style.top = top + 'px'; menu.style.visibility='visible';
    };
    document.addEventListener('show.bs.dropdown', (ev)=>{
      const dd=ev.target, btn=dd.querySelector('.dd-toggle,[data-bs-toggle="dropdown"]'), menu=dd.querySelector('.dropdown-menu');
      if(!btn || !menu) return;
      if(activePortal?.menu?.isConnected){
        activePortal.menu.classList.remove('dd-portal');
        activePortal.parent.appendChild(activePortal.menu);
        activePortal=null;
      }
      const rect=btn.getBoundingClientRect(); menu.__parent=menu.parentElement; place(menu, rect); activePortal={menu, parent:menu.__parent};
      const close=()=>{ try{ bootstrap.Dropdown.getOrCreateInstance(btn).hide(); }catch{} };
      menu.__ls=[ ['resize',close,false], ['scroll',close,true] ];
      window.addEventListener('resize', close); document.addEventListener('scroll', close, true);
    });
    document.addEventListener('hidden.bs.dropdown', (ev)=>{
      const dd=ev.target; const menu=dd.querySelector('.dropdown-menu.dd-portal') || activePortal?.menu;
      if(!menu) return;
      if(menu.__ls){
        document.removeEventListener('scroll', menu.__ls[1][1], true);
        window.removeEventListener('resize', menu.__ls[0][1]);
        menu.__ls=null;
      }
      if(menu.__parent){
        menu.classList.remove('dd-portal'); menu.style.cssText=''; menu.__parent.appendChild(menu); activePortal=null;
      }
    });
  })();
  document.addEventListener('click',(e)=>{
    const btn=e.target.closest('.dd-toggle');
    if(!btn) return;
    e.preventDefault();
    e.stopPropagation();
    bootstrap.Dropdown.getOrCreateInstance(btn,{autoClose:'outside',boundary:'viewport'}).toggle();
  });

  /* Reorder button: toggle + save */
  if (btnReorder) {
    btnReorder.addEventListener('click', async () => {
      // first click -> enter reorder mode
      if (!reorderMode) {
        enableReorderMode();
        return;
      }

      // second click in reorder mode -> save order
      const trs = Array.from(rowsEl.querySelectorAll('tr[data-id]'));
      if (!trs.length) {
        contactErr('Nothing to reorder');
        disableReorderMode();
        return;
      }

      const ids = trs.map(tr => tr.dataset.id).filter(Boolean);
      if (!ids.length) {
        contactErr('No rows to reorder');
        return;
      }

      try {
        btnReorder.disabled = true;

        const res  = await fetch("{{ route('landing.contact.reorder') }}", {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With':'XMLHttpRequest'
          },
          body: JSON.stringify({ ids })
        });
        const json = await res.json();

        if (!res.ok) {
          throw new Error(json.message || 'Failed to reorder contacts');
        }

        contactOk('Order updated');
        disableReorderMode();
        load();

      } catch (e) {
        console.error(e);
        contactErr(e.message || 'Failed to reorder contacts');
      } finally {
        btnReorder.disabled = false;
      }
    });
  }

  /* init */
  sortHintDefault = sortHint ? sortHint.textContent : '';
  syncSortHeaders();
  load();

}); // DOMContentLoaded
</script>
@endpush
