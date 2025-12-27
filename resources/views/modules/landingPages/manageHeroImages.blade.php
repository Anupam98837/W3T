
@section('title','Manage Hero Images')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
.sm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
/* .panel{background:var(--surface,#fff);border:1px solid var(--line-strong,#e5e7eb);border-radius:16px;box-shadow:var(--shadow-2,0 10px 30px rgba(15,23,42,.08));padding:14px} */

/* Table Card */
/* .table-wrap.card{position:relative;border:1px solid var(--line-strong,#e5e7eb);border-radius:16px;background:var(--surface,#fff);box-shadow:var(--shadow-2,0 12px 35px rgba(15,23,42,.08));overflow:visible}
.table-wrap .card-body{overflow:visible}
.table thead th{font-weight:600;color:var(--muted-color,#64748b);font-size:13px;border-bottom:1px solid var(--line-strong,#e5e7eb);background:var(--surface,#fff)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft,#e5e7eb)}
.table tbody tr:hover{background:var(--page-hover,#f8fafc)}
.small{font-size:12.5px} */

/* Dropdowns in table */
.table-wrap .dropdown{position:relative;z-index:6}
.table-wrap .dd-toggle{position:relative;z-index:7}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong,#e5e7eb);box-shadow:var(--shadow-2,0 12px 35px rgba(15,23,42,.16));min-width:220px;z-index:5000}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color,#dc2626)!important}

/* Hide first column (ID column) */
table.table th:nth-child(1),
table.table td:nth-child(1) {
    display: none;
}

/* Reorder mode cursor */
#heroPage.reorder-mode #heroRows tr[data-id] {
    cursor: move;
}

/* Empty & loader */
.empty{color:var(--muted-color,#64748b)}
.placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

/* Modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong,#e5e7eb);background:var(--surface,#fff)}
.modal-header{border-bottom:1px solid var(--line-strong,#e5e7eb)}
.modal-footer{border-top:1px solid var(--line-strong,#e5e7eb)}
.form-control,.form-select,textarea{border-radius:12px;border:1px solid var(--line-strong,#e5e7eb);background:#fff}

/* Dropzone */
.hero-dropzone{
  border:2px dashed var(--line-strong,#cbd5f5);
  border-radius:12px;
  padding:18px;
  text-align:center;
  cursor:pointer;
  background:#f8fafc;
  transition:.15s ease-in-out;
}
.hero-dropzone.hero-dropzone-active{
  background:#eff6ff;
  border-color:#3b82f6;
}
.hero-preview{
  margin-top:10px;
  display:flex;
  align-items:center;
  gap:10px;
}
.hero-preview img{
  width:120px;
  height:70px;
  object-fit:cover;
  border-radius:8px;
  border:1px solid #e5e7eb;
}

/* Library grid */
.lib-card{
  border-radius:14px;
  box-shadow:0 10px 30px rgba(15,23,42,.08);
  border:1px solid var(--line-strong,#e5e7eb);
  overflow:hidden;
}
.lib-card img{
  width:100%;
  max-height:140px;
  object-fit:cover;
}
.lib-card-body{
  padding:10px 12px;
}
</style>
@endpush

@section('content')
<div class="sm-wrap" id="heroPage">

  {{-- ===== Toolbar ===== --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <i class="fa fa-image text-primary"></i>
        <div>
          <div class="fw-semibold">Hero Images</div>
          <div class="small text-muted">Manage the hero slider images for your landing page.</div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button id="btnReorder" class="btn btn-outline-primary btn-sm">
          <i class="fa fa-arrows-up-down-left-right me-1"></i>Reorder
        </button>
        <button id="btnCreate" class="btn btn-primary btn-sm">
          <i class="fa fa-plus"></i> New Image
        </button>
      </div>
    </div>
  </div>

  {{-- ===== Card: Table ===== --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
          <tr>
            <th style="width:60px;">#</th>
            <th style="width:140px;">Preview</th>
            <th>Title</th>
            <th style="width:110px;">Order</th>
            <th class="text-end" style="width:120px;">Actions</th>
          </tr>
          </thead>
          <tbody id="heroRows">
            <tr id="loaderRow" style="display:none;">
              <td colspan="5" class="p-0">
                <div class="p-4">
                  <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  </div>
                </div>
              </td>
            </tr>
            <tr id="emptyRow" style="display:none;">
              <td colspan="5" class="p-4 text-center text-muted">
                <i class="fa fa-image mb-2" style="font-size:28px;opacity:.6"></i>
                <div>No hero images added yet. Click “New Image” to add one.</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="metaTxt">—</div>
      </div>
    </div>
  </div>
</div>

{{-- ================= ADD / EDIT MODAL ================= --}}
<div class="modal fade" id="heroModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="heroModalTitle" class="modal-title">
          <i class="fa fa-image me-2"></i>Create Hero Image
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="heroForm">
          @csrf
          <input type="hidden" name="id" id="hero_id">

          <div class="row g-3">
            <div class="col-md-5">

              {{-- Dropzone --}}
              <label class="form-label">Image file</label>
              <div id="heroDropzone" class="hero-dropzone">
                <i class="fa fa-cloud-upload fa-2x text-muted mb-2"></i>
                <div class="fw-semibold">Drag & drop image here</div>
                <div class="small text-muted">or click to browse, or pick from library</div>
                <div class="mt-2 d-flex justify-content-center gap-2 flex-wrap">
                  <button type="button" class="btn btn-outline-primary btn-sm" id="btnBrowseFile">
                    <i class="fa fa-folder-open me-1"></i> Browse
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLibrary">
                    <i class="fa fa-photo-film me-1"></i> From Library
                  </button>
                </div>
              </div>

              <input id="heroFile" type="file" class="d-none" accept="image/*">
              <div id="heroPreview" class="hero-preview" style="display:none;">
                <img src="" alt="Preview">
                <div class="small text-muted">
                  <div id="heroFileName">—</div>
                  <div id="heroFileSize">—</div>
                </div>
              </div>

            </div>

            <div class="col-md-7">
              <div class="mb-2">
                <label class="form-label">Title (optional)</label>
                <input type="text" name="img_title" id="img_title" class="form-control" maxlength="255">
              </div>

              <div class="mb-2">
                <label class="form-label">Image URL <span class="text-danger">*</span></label>
                <input type="text" name="image_url" id="image_url" class="form-control" required
                       placeholder="Will auto-fill when you upload / pick from library">
              </div>

              <div class="mb-2">
                <label class="form-label">Display Order</label>
                <input type="number" name="display_order" id="display_order" class="form-control" value="0">
                <div class="small text-muted mt-1">
                  Lower number = appears earlier in the slider.
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="heroSaveBtn" class="btn btn-primary">
          <i class="fa fa-save me-1"></i>Save
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= IMAGE LIBRARY MODAL ================= --}}
<div class="modal fade" id="imageLibraryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-photo-film me-2"></i>Choose from Image Library
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input type="text" id="lib_search" class="form-control" placeholder="Search by file name…">
            <button class="btn btn-outline-secondary" type="button" id="lib_search_btn">
              <i class="fa fa-search"></i>
            </button>
          </div>
        </div>

        <div id="lib_grid" class="row g-3">
          <div class="col-12 text-center py-5" id="lib_loading">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading images...</p>
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-between">
        <div class="small text-muted" id="lib_selected_text">No image selected</div>
        <div>
          <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button id="lib_select_btn" class="btn btn-primary" disabled>Use selected</button>
        </div>
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* =============== Toast helpers =============== */
const okToast  = new bootstrap.Toast(document.getElementById('okToast'));
const errToast = new bootstrap.Toast(document.getElementById('errToast'));
const ok  = (m)=>{ document.getElementById('okMsg').textContent  = m||'Done'; okToast.show(); };
const err = (m)=>{ document.getElementById('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

/* =============== Elements & State =============== */
const heroPage  = document.getElementById('heroPage');
const rowsEl    = document.getElementById('heroRows');
const loaderRow = document.getElementById('loaderRow');
const emptyRow  = document.getElementById('emptyRow');
const metaTxt   = document.getElementById('metaTxt');
const btnCreate = document.getElementById('btnCreate');
const btnReorder= document.getElementById('btnReorder');

/* Reorder state */
let reorderMode      = false;
let sortableInstance = null;

/* Modal + form elements */
const heroModalEl    = document.getElementById('heroModal');
const heroModal      = new bootstrap.Modal(heroModalEl);
const heroModalTitle = document.getElementById('heroModalTitle');
const heroForm       = document.getElementById('heroForm');
const heroSaveBtn    = document.getElementById('heroSaveBtn');

const heroId         = document.getElementById('hero_id');
const imgTitleInp    = document.getElementById('img_title');
const imgUrlInp      = document.getElementById('image_url');
const displayOrderInp= document.getElementById('display_order');

/* Dropzone elements */
const heroDropzone   = document.getElementById('heroDropzone');
const heroFileInput  = document.getElementById('heroFile');
const heroPreview    = document.getElementById('heroPreview');
const heroPreviewImg = heroPreview.querySelector('img');
const heroFileName   = document.getElementById('heroFileName');
const heroFileSize   = document.getElementById('heroFileSize');
const btnBrowseFile  = document.getElementById('btnBrowseFile');
const btnLibrary     = document.getElementById('btnLibrary');

/* Library elements */
const imageLibraryModalEl = document.getElementById('imageLibraryModal');
const imageLibraryModal   = new bootstrap.Modal(imageLibraryModalEl);
const libGrid        = document.getElementById('lib_grid');
const libSearch      = document.getElementById('lib_search');
const libSearchBtn   = document.getElementById('lib_search_btn');
const libSelectBtn   = document.getElementById('lib_select_btn');
const libSelectedText= document.getElementById('lib_selected_text');

let libItems = [];
let libSelectedKey = null;

/* API endpoints – adjust to match your routes */
const API = {
  index:   "{{ url('api/landing/hero-images') }}",
  store:   "{{ url('api/landing/hero-images') }}",
  update:  (id)=> "{{ url('api/landing/hero-images') }}/"+id,
  destroy: (id)=> "{{ url('api/landing/hero-images') }}/"+id,
  reorder: "{{ url('api/landing/hero/reorder') }}",

  // must return JSON like: { url: "https://..." }
  uploadImage:  "{{ url('api/uploads/hero-image') }}",

  // should return: { data: [ {url,name,size}, ... ] }
  imageLibrary: "{{ url('api/media/images') }}"
};

/* =============== Reorder helpers =============== */
function enableReorderMode() {
  if (reorderMode) return;
  reorderMode = true;

  heroPage.classList.add('reorder-mode');

  if (btnReorder) {
    btnReorder.classList.remove('btn-outline-primary');
    btnReorder.classList.add('btn-success');
    btnReorder.innerHTML = '<i class="fa fa-floppy-disk me-1"></i>Save order';
  }

  if (!metaTxt.dataset.defaultText) {
    metaTxt.dataset.defaultText = metaTxt.textContent || '';
  }
  metaTxt.textContent = 'Reorder mode: drag rows, then click "Save order".';

  sortableInstance = Sortable.create(rowsEl, {
    animation: 150,
    // if you add a drag handle later: handle: '.drag-handle'
    filter: 'tr#loaderRow, tr#emptyRow'
  });
}

function disableReorderMode() {
  if (!reorderMode) return;
  reorderMode = false;

  heroPage.classList.remove('reorder-mode');

  if (btnReorder) {
    btnReorder.classList.remove('btn-success');
    btnReorder.classList.add('btn-outline-primary');
    btnReorder.innerHTML = '<i class="fa fa-arrows-up-down-left-right me-1"></i>Reorder';
  }

  if (sortableInstance) {
    sortableInstance.destroy();
    sortableInstance = null;
  }

  if (metaTxt.dataset.defaultText !== undefined) {
    metaTxt.textContent = metaTxt.dataset.defaultText;
    delete metaTxt.dataset.defaultText;
  }
}

/* =============== Helpers =============== */
function bytes(n){
  n = Number(n||0);
  if(!n) return '0 B';
  const u=['B','KB','MB','GB']; let i=0;
  while(n>=1024 && i<u.length-1){ n/=1024; i++; }
  return n.toFixed(n<10&&i?1:0)+' '+u[i];
}

function showLoader(v){ loaderRow.style.display = v ? '' : 'none'; }
function showEmpty(v){  emptyRow.style.display  = v ? '' : 'none'; }

/* =============== Load table =============== */
async function loadImages(){
  showLoader(true);
  showEmpty(false);
  rowsEl.querySelectorAll('tr:not(#loaderRow):not(#emptyRow)').forEach(n=>n.remove());
  metaTxt.textContent = '—';

  try{
    const res = await fetch(API.index, {headers:{Accept:"application/json","Cache-Control":"no-cache"}});
    const json= await res.json();

    const items = json?.data || json || [];
    if(!items.length){
      showEmpty(true);
      metaTxt.textContent = '0 image(s)';
      return;
    }

    const frag = document.createDocumentFragment();
    items.sort((a,b)=> (a.display_order||0) - (b.display_order||0));

    items.forEach((item,i)=>{
      const tr = document.createElement('tr');
      tr.dataset.id = item.id; // used for reorder
      tr.innerHTML = `
        <td>${i+1}</td>
        <td>
          <img src="${item.image_url}" style="width:90px;height:50px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
        </td>
        <td>
          <div class="fw-semibold">${item.img_title ? item.img_title : '(No title)'}</div>
          <div class="small text-muted text-truncate" style="max-width:380px;">${item.image_url || ''}</div>
        </td>
        <td>${item.display_order ?? 0}</td>
        <td class="text-end">
          <div class="dropdown" data-bs-display="static">
            <button type="button" class="btn btn-primary btn-sm dd-toggle" data-bs-toggle="dropdown">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item" data-act="edit" data-item='${JSON.stringify(item).replace(/'/g,"&#39;")}'>
                  <i class="fa fa-pen-to-square"></i> Edit
                </button>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <button class="dropdown-item text-danger" data-act="delete" data-id="${item.id}">
                  <i class="fa fa-trash"></i> Delete
                </button>
              </li>
            </ul>
          </div>
        </td>
      `;
      frag.appendChild(tr);
    });
    rowsEl.appendChild(frag);
    metaTxt.textContent = `${items.length} image(s)`;

  }catch(e){
    console.error(e);
    err('Failed to load images');
    showEmpty(true);
  }finally{
    showLoader(false);
  }
}
loadImages();

/* =============== Open create / edit =============== */
function resetForm(){
  heroForm.reset();
  heroId.value          = '';
  displayOrderInp.value = 0;
  heroPreview.style.display = 'none';
  heroPreviewImg.src    = '';
  heroFileName.textContent = '—';
  heroFileSize.textContent = '—';
}

btnCreate.addEventListener('click', ()=>{
  resetForm();
  heroModalTitle.textContent = 'Create Hero Image';
  heroModal.show();
});

function openEdit(item){
  resetForm();
  heroModalTitle.textContent = 'Edit Hero Image';
  heroId.value          = item.id;
  imgTitleInp.value     = item.img_title ?? '';
  imgUrlInp.value       = item.image_url ?? '';
  displayOrderInp.value = item.display_order ?? 0;

  if(item.image_url){
    heroPreviewImg.src = item.image_url;
    heroPreview.style.display = 'flex';
    heroFileName.textContent = item.image_url.split('/').pop();
    heroFileSize.textContent = '';
  }

  heroModal.show();
}

/* row actions (edit/delete) */
document.addEventListener('click', (e)=>{
  const actBtn = e.target.closest('.dropdown-item[data-act]');
  if(!actBtn) return;

  const act = actBtn.dataset.act;

  if(act === 'edit'){
    const itemStr = actBtn.dataset.item || '{}';
    try{
      const item = JSON.parse(itemStr.replace(/&#39;/g,"'"));
      openEdit(item);
    }catch(_){}
  }

  if(act === 'delete'){
    const id = actBtn.dataset.id;
    if(id) deleteItem(id);
  }

  // close dropdown after click
  const parentDD = actBtn.closest('.dropdown');
  if(parentDD){
    const toggleEl = parentDD.querySelector('[data-bs-toggle="dropdown"]');
    if(toggleEl){
      const inst = bootstrap.Dropdown.getOrCreateInstance(toggleEl);
      inst.hide();
    }
  }
});

/* =============== Dropzone & upload =============== */
function setPreviewFromFile(file){
  if(!file) return;
  const url = URL.createObjectURL(file);
  heroPreviewImg.src = url;
  heroPreview.style.display = 'flex';
  heroFileName.textContent = file.name;
  heroFileSize.textContent = bytes(file.size);
}

async function uploadImage(file){
  try{
    const fd = new FormData();
    fd.append('file', file);

    const res = await fetch(API.uploadImage, {
      method:'POST',
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}",
        'Accept':'application/json'
      },
      body: fd
    });
    const j = await res.json();
    if(!res.ok){
      throw new Error(j?.message || 'Upload failed');
    }

    // Expecting JSON: { url: "https://..." }
    if(j.url){
      imgUrlInp.value = j.url;       // auto-fill URL field
      ok('Image uploaded');
    }else{
      err('Upload ok but no URL returned');
    }
  }catch(e){
    console.error(e);
    err(e.message || 'Upload failed');
  }
}

btnBrowseFile.addEventListener('click', ()=> heroFileInput.click());
heroFileInput.addEventListener('change', ()=>{
  const file = heroFileInput.files[0];
  if(!file) return;
  setPreviewFromFile(file);
  uploadImage(file);   // auto-detect path and fill URL
});

/* drag & drop */
['dragenter','dragover'].forEach(ev=>{
  heroDropzone.addEventListener(ev, e=>{
    e.preventDefault(); e.stopPropagation();
    heroDropzone.classList.add('hero-dropzone-active');
  });
});
['dragleave','dragend','drop'].forEach(ev=>{
  heroDropzone.addEventListener(ev, e=>{
    e.preventDefault(); e.stopPropagation();
    heroDropzone.classList.remove('hero-dropzone-active');
  });
});
heroDropzone.addEventListener('click', ()=> heroFileInput.click());
heroDropzone.addEventListener('drop', (e)=>{
  const f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
  if(!f) return;
  heroFileInput.files = e.dataTransfer.files;
  setPreviewFromFile(f);
  uploadImage(f);
});

/* =============== Save (create / update) =============== */
heroSaveBtn.addEventListener('click', async()=>{
  // URL is the main source of truth
  if(!imgUrlInp.value.trim()){
    return Swal.fire('Image URL required','Please upload an image or choose from library.','info');
  }

  const fd = new FormData(heroForm);
  const id = heroId.value;
  let url  = API.store;
  let method = 'POST';

  if(id){
    url = API.update(id);
    fd.append('_method','PUT');
  }

  try{
    const res = await fetch(url, {
      method: method,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}",
        'Accept':'application/json'
      },
      body: fd
    });
    const j = await res.json().catch(()=> ({}));
    if(!res.ok){
      throw new Error(j?.message || 'Save failed');
    }
    ok('Hero image saved');
    heroModal.hide();
    disableReorderMode();
    loadImages();
  }catch(e){
    console.error(e);
    err(e.message || 'Save failed');
  }
});

/* =============== Delete =============== */
async function deleteItem(id){
  const {isConfirmed} = await Swal.fire({
    icon:'warning',
    title:'Delete image?',
    text:'This will remove the image from your hero slider.',
    showCancelButton:true,
    confirmButtonText:'Delete',
    confirmButtonColor:'#ef4444'
  });
  if(!isConfirmed) return;

  try{
    const res = await fetch(API.destroy(id), {
      method:'POST',
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}",
        'Accept':'application/json'
      },
      body: new URLSearchParams({'_method':'DELETE'})
    });
    const j = await res.json().catch(()=> ({}));
    if(!res.ok){
      throw new Error(j?.message || 'Delete failed');
    }
    ok('Image deleted');
    disableReorderMode();
    loadImages();
  }catch(e){
    console.error(e);
    err(e.message || 'Delete failed');
  }
}

/* =============== Image Library =============== */
async function loadLibrary(query=''){
  libGrid.innerHTML = `
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading images...</p>
    </div>
  `;
  libSelectedKey = null;
  libSelectBtn.disabled = true;
  libSelectedText.textContent = 'No image selected';

  try{
    const params = new URLSearchParams();
    if(query) params.set('search', query);

    const res = await fetch(API.imageLibrary + '?' + params.toString(), {
      headers:{Accept:'application/json'}
    });
    const j = await res.json();
    const items = j?.data || j || [];

    libItems = items;
    if(!items.length){
      libGrid.innerHTML = `
        <div class="col-12 text-center text-muted py-4">
          No images found in library.
        </div>
      `;
      return;
    }

    const frag = document.createDocumentFragment();
    items.forEach((img,idx)=>{
      const col = document.createElement('div');
      col.className = 'col-md-3 col-sm-4 col-6';
      const name = img.name || (img.url ? img.url.split('/').pop() : 'Image');

      col.innerHTML = `
        <div class="lib-card h-100" data-key="${idx}" style="cursor:pointer;">
          ${img.url ? `<img src="${img.url}" alt="${name}">` : ''}
          <div class="lib-card-body">
            <div class="fw-semibold text-truncate" title="${name}">${name}</div>
            <div class="small text-muted">${img.size ? bytes(img.size) : ''}</div>
          </div>
        </div>
      `;
      col.addEventListener('click', ()=>{
        libSelectedKey = idx;
        libSelectBtn.disabled = false;
        libSelectedText.textContent = name;
        // highlight selection
        libGrid.querySelectorAll('.lib-card').forEach(c=> c.classList.remove('border-primary','shadow'));
        col.querySelector('.lib-card').classList.add('border-primary','shadow');
      });

      frag.appendChild(col);
    });
    libGrid.innerHTML = '';
    libGrid.appendChild(frag);

  }catch(e){
    console.error(e);
    libGrid.innerHTML = `
      <div class="col-12 text-danger text-center py-4">
        Failed to load image library.
      </div>
    `;
  }
}

btnLibrary.addEventListener('click', ()=>{
  loadLibrary('');
  imageLibraryModal.show();
});

libSearchBtn.addEventListener('click', ()=>{
  const q = libSearch.value.trim();
  loadLibrary(q);
});
libSearch.addEventListener('keypress',(e)=>{
  if(e.key === 'Enter'){
    e.preventDefault();
    loadLibrary(libSearch.value.trim());
  }
});

libSelectBtn.addEventListener('click', ()=>{
  if(libSelectedKey == null) return;
  const img = libItems[libSelectedKey];
  if(!img || !img.url) return;

  imgUrlInp.value = img.url;           // fill URL field
  heroPreviewImg.src = img.url;        // update preview
  heroPreview.style.display = 'flex';
  heroFileName.textContent = img.name || img.url.split('/').pop();
  heroFileSize.textContent = img.size ? bytes(img.size) : '';

  imageLibraryModal.hide();
  ok('Image selected from library');
});

/* =============== Reorder button handler =============== */
if (btnReorder) {
  btnReorder.addEventListener('click', async () => {
    // First click: enter reorder mode
    if (!reorderMode) {
      enableReorderMode();
      return;
    }

    // Second click: save order
    const rows = Array.from(rowsEl.querySelectorAll('tr[data-id]'));
    if (!rows.length) {
      err('Nothing to reorder');
      disableReorderMode();
      return;
    }

    const ids = rows.map(tr => tr.dataset.id).filter(Boolean);
    if (!ids.length) {
      err('No rows to reorder');
      return;
    }

    try {
      btnReorder.disabled = true;

      const res = await fetch(API.reorder, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': "{{ csrf_token() }}",
        },
        body: JSON.stringify({ ids }),
      });
      const json = await res.json().catch(() => ({}));

      if (!res.ok) {
        throw new Error(json?.message || 'Failed to reorder hero images');
      }

      ok('Order updated');
      disableReorderMode();
      loadImages();

    } catch (ex) {
      console.error(ex);
      err(ex.message || 'Error saving order');
    } finally {
      btnReorder.disabled = false;
    }
  });
}
</script>
@endpush
