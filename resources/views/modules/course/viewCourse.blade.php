{{-- resources/views/modules/courses/viewCourse.blade.php --}}
@section('title', 'View Course')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Course View (non-breaking on top of main.css) ===== */
.cv-wrap{max-width:1140px;margin:16px auto 40px}

/* Hero */
.cv-hero{
  border-radius:16px;overflow:hidden;box-shadow:var(--shadow-2);
  border:1px solid var(--line-strong);background:#000;min-height:220px;
  display:flex;align-items:center;justify-content:center;
}
.cv-hero img{
  max-height:380px;max-width:100%;width:auto;height:auto;
  object-fit:contain;display:none; /* revealed after it loads */
}

/* Titles */
.cv-title{font-family:var(--font-head);line-height:1.15;margin-bottom:.35rem}
.cv-sub{color:var(--muted-color)}
.cv-badges .badge{margin-right:6px}

/* Cards */
.cv-card{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2)}
.cv-card .cv-head{padding:12px 14px;border-bottom:1px solid var(--line-strong);font-weight:600}
.cv-card .cv-body{padding:14px}

/* Details */
.cv-details .row + .row{border-top:1px solid var(--line-soft)}
.cv-details .lbl{color:var(--muted-color)}
.cv-details .val{font-weight:600}

/* Pricing */
.cv-price .big{font-size:1.6rem;font-weight:800}
.cv-price .cut{opacity:.6;text-decoration:line-through;margin-left:.5rem}
.cv-price .off{margin-left:.5rem}
.cv-cta .btn{height:42px;border-radius:12px}

/* Gallery — compact strip */
#cvThumbs{margin-top:.5rem}
.cv-thumbs .thumb{
  display:block;width:100%;padding:0;border:1px solid var(--line-strong);
  border-radius:10px;overflow:hidden;background:transparent;cursor:pointer
}
/* keep as safety; main sizing is inline to defeat overrides */
#cvThumbs .thumb img{
  width:100% !important;height:56px !important;object-fit:cover !important;display:block !important;
}
.cv-thumbs .thumb.active{outline:2px solid var(--primary-color);outline-offset:2px}
.cv-thumbs .thumb:focus-visible{outline:2px solid var(--primary-color);outline-offset:3px}

/* Accordion */
.cv-mod .accordion-item{border-radius:12px;overflow:hidden}
.cv-mod .badge{vertical-align:middle}

/* Placeholder shimmer */
.placeholder{background:linear-gradient(90deg,#0001,#0000000d,#0001);border-radius:8px}

/* Hero skeleton (quiet, no big “Loading…” image) */
#cvHeroSkel{width:100%;height:380px;border-radius:16px;border:1px solid var(--line-strong);display:block}

/* Loading state toggles */
.is-loading #cvHeroSkel{display:block}
.is-loading #cvCover{display:none}

/* Responsive */
@media (max-width: 992px){
  #cvHeroSkel{height:260px}
}
</style>
@endpush

@section('content')
@php
  // {course} may be id | uuid | slug
  $courseParam = request()->route('course');
@endphp

<div id="cvWrap" class="cv-wrap is-loading" data-course="{{ $courseParam }}" aria-busy="true">
  {{-- ===== Heading ===== --}}
  <div class="mb-3">
    <div class="cv-breadcrumb small text-muted mb-1">
      <a href="javascript:history.back()" class="me-1"><i class="fa fa-arrow-left-long me-1"></i>Back</a>
    </div>

    <h1 id="cvTitle" class="cv-title">—</h1>
    <div id="cvSub" class="cv-sub">—</div>

    <div id="cvBadges" class="cv-badges mt-2"><!-- no “Loading” badge --></div>
  </div>

  {{-- ===== Hero ===== --}}
  <div class="cv-hero d-flex justify-content-center mb-3">
    <div id="cvHeroSkel" class="placeholder"></div>
    {{-- tiny transparent pixel as placeholder; hidden until real cover loads --}}
    <img class="img-fluid" id="cvCover" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="Course cover">
  </div>

  {{-- ===== Thumb strip (gallery) ===== --}}
  <div id="cvThumbs" class="d-flex justify-content-center row g-2 mb-4 cv-thumbs"></div>

  <div class="row g-3">
    {{-- ===== Left column ===== --}}
    <div class="col-lg-8">
      {{-- Description --}}
      <div class="cv-card mb-3">
        <div class="cv-head">Course Description</div>
        <div id="cvDesc" class="cv-body">
          <div class="placeholder col-12 mb-2" style="height:14px"></div>
          <div class="placeholder col-11 mb-2" style="height:14px"></div>
          <div class="placeholder col-10 mb-2" style="height:14px"></div>
        </div>
      </div>

      {{-- Modules --}}
      <div class="cv-card cv-mod">
        <div class="cv-head">Course Modules</div>
        <div class="cv-body">
          <div id="cvModWrap" class="accordion"></div>
          <div id="cvModEmpty" class="text-muted small" style="display:none;">No modules yet.</div>
        </div>
      </div>
    </div>

    {{-- ===== Right column ===== --}}
    <div class="col-lg-4">
      {{-- Pricing --}}
      <div class="cv-card cv-price mb-3">
        <div class="cv-head">Pricing</div>
        <div class="cv-body">
          <div id="cvPriceRow" class="mb-2">
            <span id="cvPriceFinal" class="big">—</span>
            <span id="cvPriceOrig" class="cut"></span>
            <span id="cvPriceOff"  class="badge badge-success off" style="display:none"></span>
          </div>
          <div id="cvPriceNote" class="small text-muted">—</div>
          <div class="cv-cta mt-3 d-grid">
            <a id="cvPrimaryCta" href="javascript:void(0)" class="btn btn-primary"><i class="fa fa-cart-shopping me-1"></i>Enroll / Manage</a>
          </div>
        </div>
      </div>

      {{-- Details --}}
      <div class="cv-card cv-details">
        <div class="cv-head">Course Details</div>
        <div class="cv-body">
          <div class="row py-1"><div class="col-6 lbl">Status</div><div id="dStatus" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Type</div><div id="dType" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Difficulty</div><div id="dDiff" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Language</div><div id="dLang" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Duration</div><div id="dDur" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Created</div><div id="dCreated" class="col-6 val">—</div></div>
          <hr class="my-2">
          <div class="small text-muted">
            <div><b>Course ID:</b> <span id="dUuid">—</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ================= Helpers ================= */
const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
const wrap  = document.getElementById('cvWrap');

const COURSE_PARAM = (() => {
  const k = wrap?.dataset?.course;
  if (k) return k;
  const m = location.pathname.match(/\/courses(?:\/view)?\/([^\/?#]+)/i);
  return m ? decodeURIComponent(m[1]) : '';
})();

if (!COURSE_PARAM) {
  document.getElementById('cvTitle').textContent = 'Failed to load course';
  document.getElementById('cvSub').textContent   = 'No course key in URL';
  throw new Error('COURSE_PARAM not found');
}

const esc = (s)=> (s==null?'':String(s)).replace(/[&<>"'`]/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;' }[m]));
const money = (n, ccy='INR') => {
  if (n==null || isNaN(+n)) return '—';
  try { return new Intl.NumberFormat('en-IN', { style:'currency', currency: ccy, maximumFractionDigits: 0 }).format(+n); }
  catch { return (ccy+' '+(+n).toLocaleString()); }
};
const fmtDate = (ts) => {
  if (!ts) return '—';
  const d = new Date(String(ts).replace(' ', 'T'));
  return isNaN(d) ? esc(ts) : d.toLocaleDateString(undefined, { year:'numeric', month:'short', day:'2-digit' });
};
const cleanUrl = (u)=> {
  if (!u) return '';
  try { return String(u).replace(/%22"?$/,'').replace(/"$/,''); } catch { return u; }
};
function badgeStatus(s){
  const map={published:'success', draft:'warning', archived:'info'};
  const cls = map[s] || 'info';
  return `<span class="badge badge-${cls} text-uppercase">${esc(s||'-')}</span>`;
}

/* ================= Nodes ================= */
const n = {
  title:      document.getElementById('cvTitle'),
  sub:        document.getElementById('cvSub'),
  badges:     document.getElementById('cvBadges'),
  cover:      document.getElementById('cvCover'),
  heroSkel:   document.getElementById('cvHeroSkel'),
  thumbs:     document.getElementById('cvThumbs'),
  desc:       document.getElementById('cvDesc'),
  modWrap:    document.getElementById('cvModWrap'),
  modEmpty:   document.getElementById('cvModEmpty'),
  priceFinal: document.getElementById('cvPriceFinal'),
  priceOrig:  document.getElementById('cvPriceOrig'),
  priceOff:   document.getElementById('cvPriceOff'),
  priceNote:  document.getElementById('cvPriceNote'),
  cta:        document.getElementById('cvPrimaryCta'),

  dStatus:  document.getElementById('dStatus'),
  dType:    document.getElementById('dType'),
  dDiff:    document.getElementById('dDiff'),
  dLang:    document.getElementById('dLang'),
  dDur:     document.getElementById('dDur'),
  dCreated: document.getElementById('dCreated'),
  dUuid:    document.getElementById('dUuid'),
};

function revealHero(){
  // hide skeleton, show image, clear loading state
  n.heroSkel.style.display = 'none';
  n.cover.style.display = 'block';
  wrap?.classList.remove('is-loading');
  wrap?.removeAttribute('aria-busy');
}

async function loadCourse(){
  try{
    const res = await fetch(`/api/courses/${encodeURIComponent(COURSE_PARAM)}/view`, {
      headers: { 'Authorization': 'Bearer '+TOKEN, 'Accept':'application/json' }
    });
    const j = await res.json();
    if (!res.ok) throw new Error(j?.message || 'Failed to load course');

    const d = j?.data || {};
    const c = d.course || {};
    const p = d.pricing || {};
    const m = d.media || {};
    const mods = Array.isArray(d.modules) ? d.modules : [];

    /* Title + meta */
    n.title.textContent = c.title || 'Untitled Course';
    n.sub.textContent   = c.short_description || '—';
    n.badges.innerHTML  = `
      ${badgeStatus(c.status||'-')}
      <span class="badge badge-primary text-uppercase">${esc(c.course_type || 'paid')}</span>
      ${p.has_discount ? `<span class="badge badge-warning"><i class="fa fa-percent"></i> ${(p.effective_percent ?? p.discount_percent) || 0}% off</span>` : ''}
    `;

    /* Hero image — quiet load (no giant loading image) */
    const coverUrl = cleanUrl(m?.cover?.url) || cleanUrl(m?.gallery?.[0]?.url) || '';
    if (coverUrl){
      n.cover.onload = ()=> revealHero();
      n.cover.onerror = ()=> revealHero();
      n.cover.src = coverUrl;
      if (n.cover.complete) revealHero(); // cached case
    } else {
      revealHero();
    }

    /* Gallery thumbs — enforce compact size inline to beat external CSS */
    const gal = Array.isArray(m.gallery) ? m.gallery : [];
    if (gal.length){
      n.thumbs.innerHTML = gal.map((g,i)=>{
        const url = esc(cleanUrl(g.url));
        const active = url === coverUrl ? 'active' : '';
        return `
          <div class="col-3 col-sm-2">
            <button type="button" class="thumb ${active}" data-src="${url}" aria-label="Preview image ${i+1}">
              <img src="${url}" loading="lazy" alt="Gallery ${i+1}"
                   style="height:56px;object-fit:cover;width:100%;display:block">
            </button>
          </div>`;
      }).join('');
      const btns = n.thumbs.querySelectorAll('.thumb');
      const setActive = (b)=>{ btns.forEach(x=>x.classList.remove('active')); b.classList.add('active'); };
      btns.forEach(b=>{
        b.addEventListener('click', e=>{
          e.preventDefault();
          const src = b.dataset.src;
          if (src){
            n.cover.src = src;
            setActive(b);
            // bring hero to the center of the viewport
            n.cover.parentElement.scrollIntoView({behavior:'smooth', block:'center', inline:'nearest'});
          }
        });
      });
    } else {
      n.thumbs.innerHTML = '';
    }

    /* Description */
    const long = c.full_description || '';
    n.desc.innerHTML = long
      ? `<div class="small" style="white-space:pre-wrap">${esc(long)}</div>`
      : `<div class="text-muted small">No description yet.</div>`;

    /* Modules (accordion; show 1., 2., 3.; HIDE archived) */
    const visibleMods = mods.filter(md => String(md.status || '').toLowerCase() !== 'archived');
    if (!visibleMods.length){
      n.modWrap.innerHTML = '';
      n.modEmpty.style.display = '';
    } else {
      n.modEmpty.style.display = 'none';
      const id = (i)=>`mod_${i}`;
      const hid = (i)=>`modh_${i}`;
      const sorted = visibleMods.slice().sort((a,b)=> (a.order_no ?? 999) - (b.order_no ?? 999));
      n.modWrap.innerHTML = sorted.map((mod, i)=>`
        <div class="accordion-item mb-2">
          <h2 class="accordion-header" id="${hid(i)}">
            <button class="accordion-button ${i? 'collapsed':''}" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#${id(i)}"
                    aria-expanded="${i? 'false':'true'}"
                    aria-controls="${id(i)}">
                    <b class="me-2">${i+1}.</b> ${esc(mod.title||'Module')}
                    <span class="ms-2 badge ${String(mod.status).toLowerCase()==='published'?'badge-success':'badge-warning'} text-uppercase">${esc(mod.status||'-')}</span>
            </button>
          </h2>
          <div id="${id(i)}" class="accordion-collapse collapse ${i? '':'show'}"
               aria-labelledby="${hid(i)}" data-bs-parent="#cvModWrap">
            <div class="accordion-body">
              ${mod.short_description ? `<div class="mb-1">${esc(mod.short_description)}</div>`:''}
              ${mod.long_description ? `<div class="small text-muted" style="white-space:pre-wrap">${esc(mod.long_description)}</div>`:''}
            </div>
          </div>
        </div>`).join('');
    }

    /* Pricing */
    if (p.is_free){
      n.priceFinal.textContent = 'Free';
      n.priceOrig.textContent  = '';
      n.priceOff.style.display = 'none';
      n.priceNote.textContent  = 'This course is free to enroll.';
    } else {
      n.priceFinal.textContent = money(p.final, p.currency || 'INR');
      n.priceOrig.textContent  = p.has_discount ? money(p.original, p.currency || 'INR') : '';
      if (p.has_discount){
        const off = (p.effective_percent ?? p.discount_percent) || 0;
        n.priceOff.textContent = `-${off}%`;
        n.priceOff.style.display = '';
        n.priceNote.textContent = 'Limited-time discount may apply.';
      } else {
        n.priceOff.style.display = 'none';
        n.priceNote.textContent = 'Standard pricing.';
      }
    }

    /* Details (UUID only) */
    n.dStatus.innerHTML  = badgeStatus(c.status||'-');
    n.dType.textContent  = (c.course_type||'paid').toUpperCase();
    n.dDiff.textContent  = c.difficulty || '—';
    n.dLang.textContent  = c.language   || '—';
    n.dDur.textContent   = c.duration_hours ? (c.duration_hours+' hour'+(Number(c.duration_hours)==1?'':'s')) : '—';
    n.dCreated.textContent = fmtDate(c.created_at);
    n.dUuid.textContent    = c.uuid || '—';

    /* CTA */
    const prefix = location.pathname.startsWith('/super_admin') ? '/super_admin' : '/admin';
    const idForQuery = c.id ?? c.uuid ?? '';
    n.cta.href = `${prefix}/batches/manage?course_id=${encodeURIComponent(idForQuery)}`;
  }catch(e){
    console.error(e);
    n.title.textContent = 'Failed to load course';
    n.sub.textContent   = e.message || '—';
    // even on failure, hide skeleton so page doesn't look stuck
    document.querySelector('.cv-hero #cvHeroSkel')?.remove();
    document.getElementById('cvCover').style.display = 'none';
    wrap?.classList.remove('is-loading');
    wrap?.removeAttribute('aria-busy');
  }
}

document.addEventListener('DOMContentLoaded', loadCourse);
</script>
@endpush
