@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
.sm-wrap{max-width:1140px;margin:16px auto 40px}
.lp-card{
  border-radius:16px;
  border:1px solid var(--line-strong);
  box-shadow:0 10px 30px rgba(15,23,42,.08)
}
.table thead th{
  background:#f8fafc;
  font-weight:600;
  white-space:nowrap;
}
.btn-icon{
  width:34px;height:34px;border-radius:10px;
  display:inline-flex;align-items:center;justify-content:center;
  border:1px solid var(--line-strong);background:#fff;
}
.btn-icon:hover{background:#f1f5f9}
.sortable{cursor:pointer}
.tiny-sort{font-size:14px;margin-left:6px;opacity:.6}
</style>
@endpush
@section('content')
<div class="sm-wrap py-4" id="enquiryAdmin">

  {{-- ===== HEADER ===== --}}
  <div class="panel mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
      <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
        <i class="fa fa-envelope text-primary"></i>
      </div>
      <div>
        <div class="fw-semibold fs-5">Enquiries</div>
        <div class="small text-muted">Messages received from Contact Us form</div>
      </div>
    </div>

    <button id="exportCsv" class="btn btn-outline-primary btn-sm">
      <i class="fa fa-file-csv me-1"></i> Export CSV
    </button>
  </div>

  {{-- ===== FILTERS ===== --}}
  <div class="panel mb-3 row align-items-center g-2">
    <div class="col d-flex align-items-center gap-2 flex-wrap">

      <label class="small text-muted">Per page</label>
      <select id="perPage" class="form-select" style="width:90px;">
        <option>10</option>
        <option selected>20</option>
        <option>50</option>
        <option>100</option>
      </select>

      <select id="sortSelect" class="form-select" style="width:180px;">
        <option value="created_desc" selected>Newest first</option>
        <option value="created_asc">Oldest first</option>
        <option value="name_asc">Name A → Z</option>
        <option value="name_desc">Name Z → A</option>
      </select>

      <div class="position-relative" style="min-width:260px;">
        <input id="searchBox" class="form-control ps-5"
               placeholder="Search name, email, phone…">
        <i class="fa fa-search position-absolute"
           style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      </div>
    </div>

    <div class="col-auto ms-auto">
      <span id="totalCount" class="badge bg-light text-dark">0 enquiries</span>
    </div>
  </div>

  {{-- ===== TABLE ===== --}}
  <div class="card lp-card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Message</th>
            <th>Received</th>
    <th>Read</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <div class="spinner-border spinner-border-sm me-2"></div>
              Loading enquiries…
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center p-3 border-top">
      <div id="pageInfo" class="small text-muted">—</div>
      <ul id="pager" class="pagination mb-0"></ul>
    </div>
  </div>
</div>
<!-- Message View Modal -->
<div class="modal fade" id="msgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="msgModalBody" style="white-space:pre-wrap;"></div>
      </div>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

  const API = '/api/contact-us';

  let page = 1;
  let perPage = 20;
  let q = '';
  let sortBy = 'created_at';
  let sortDir = 'desc';

  const tbody = document.getElementById('tbody');
  const pager = document.getElementById('pager');
  const info  = document.getElementById('pageInfo');
  const count = document.getElementById('totalCount');

  const modalEl = document.getElementById('msgModal');
  const modalBody = document.getElementById('msgModalBody');
  const modal = new bootstrap.Modal(modalEl);

  async function load(){
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-5 text-muted">
          <div class="spinner-border spinner-border-sm me-2"></div> Loading…
        </td>
      </tr>`;

    const params = new URLSearchParams({
      page,
      per_page: perPage,
      q,
      sort_by: sortBy,
      sort_dir: sortDir
    });

    const res = await fetch(`${API}?${params.toString()}`);
    const json = await res.json();

    const rows = json.data || [];
    const meta = json.meta || {};

    count.textContent = `${meta.total || 0} enquiries`;

    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-5 text-muted">
            <i class="fa fa-inbox fa-2x mb-2"></i><br>No enquiries found
          </td>
        </tr>`;
      info.textContent = '—';
      pager.innerHTML = '';
      return;
    }

    tbody.innerHTML = rows.map(r => `
      <tr ${r.is_read == 0 ? 'class="fw-semibold"' : ''}>
        <td>${esc(r.name)}</td>
        <td><a href="mailto:${esc(r.email)}">${esc(r.email)}</a></td>
        <td>${esc(r.phone || '—')}</td>

        <td>
          <div class="d-flex align-items-center gap-2">
            <span title="${esc(r.message)}">
              ${esc(r.message).slice(0,40)}${r.message.length > 40 ? '…' : ''}
            </span>
            <button class="btn-icon btn-sm" onclick="viewMsg(${r.id})">
              <i class="fa fa-eye"></i>
            </button>
          </div>
        </td>

        <td>${new Date(r.created_at).toLocaleString()}</td>

        <td>
          ${
            r.is_read == 1
              ? `<span class="badge bg-success">Read</span>`
              : `<button class="btn btn-sm btn-outline-primary"
                         onclick="markRead(${r.id})">
                    Mark as read
                 </button>`
          }
        </td>
      </tr>
    `).join('');

    buildPager(meta);
  }

  function buildPager(meta){
    const current = meta.page || 1;
    const pages = meta.total_pages || 1;
    info.textContent = `Page ${current} of ${pages}`;

    let html = '';
    const li = (p,l,d,a)=>`
      <li class="page-item ${d?'disabled':''} ${a?'active':''}">
        <a class="page-link" href="#" data-p="${p}">${l}</a>
      </li>`;

    html += li(current-1,'Prev',current<=1);
    for(let i=Math.max(1,current-2); i<=Math.min(pages,current+2); i++)
      html += li(i,i,false,i===current);
    html += li(current+1,'Next',current>=pages);

    pager.innerHTML = html;
  }

  pager.addEventListener('click', e=>{
    const a = e.target.closest('a[data-p]');
    if (!a) return;
    e.preventDefault();
    page = Number(a.dataset.p);
    load();
  });

  // 👁 View full message in modal (ONLY message)
  window.viewMsg = async (id)=>{
    const r = await fetch(`${API}/${id}`);
    const j = await r.json();

    modalBody.textContent = j.message.message;
    modal.show();
    load(); // refresh read state
  };

  // Mark as read button
  window.markRead = async (id)=>{
    const r = await fetch(`${API}/${id}/read`, { method: 'PATCH' });
    const j = await r.json();
    if (j.success) load();
  };

  document.getElementById('searchBox').addEventListener('input', e=>{
    q = e.target.value.trim();
    page = 1;
    load();
  });

  document.getElementById('perPage').addEventListener('change', e=>{
    perPage = e.target.value;
    page = 1;
    load();
  });

  document.getElementById('sortSelect').addEventListener('change', e=>{
    const v = e.target.value;
    if (v === 'created_desc'){ sortBy='created_at'; sortDir='desc'; }
    if (v === 'created_asc'){ sortBy='created_at'; sortDir='asc'; }
    if (v === 'name_asc'){ sortBy='name'; sortDir='asc'; }
    if (v === 'name_desc'){ sortBy='name'; sortDir='desc'; }
    page = 1;
    load();
  });

  document.getElementById('exportCsv').onclick = ()=>{
    window.location.href =
      `/api/contact-us/export/csv?sort_by=${sortBy}&sort_dir=${sortDir}&q=${encodeURIComponent(q)}`;
  };

  function esc(s){
    return String(s||'').replace(/[&<>"']/g,m=>(
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  load();
});
</script>
@endpush
