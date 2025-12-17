{{-- resources/views/modules/courses/viewCourse.blade.php --}}


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Course View (non-breaking on top of main.css) ===== */
/* Centered, responsive wrapper */
.cv-wrap{
  max-width: 1140px;       /* keeps layout from getting too wide */
  margin: 16px auto;       /* centers horizontally across viewports & zooms */
  padding: 0 20px;         /* small side padding for narrow viewports */
  box-sizing: border-box;  /* include padding in width calculations */
}

/* Hero */
.cv-hero{
  border-radius:16px;
  overflow:hidden;
  box-shadow:var(--shadow-2);
  border:1px solid var(--line-strong);
  background:#000;
  min-height:220px;
  display:flex;
  align-items:center;
  justify-content:center;
  width:100%;              /* use full width of wrapper */
}

/* Hero image (responsive & centered). Hidden until load like before. */
.cv-hero img{
  display:none;            /* still hidden until loaded by JS */
  margin: 0 auto;          /* center within hero */
  max-width:100%;          /* never exceed container width */
  height:auto;
  max-height:60vh;         /* keep hero from overtaking the viewport */
  object-fit:contain;
}

/* Titles */
.cv-title{font-family:var(--font-head);line-height:1.15;margin-bottom:.35rem}
.cv-sub{color:var(--muted-color)}
.cv-badges .badge{margin-right:6px}

/* Cards */
.cv-card{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
}
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
#cvThumbs{margin-top:.5rem; justify-content:center;} /* ensure thumbs centered */
.cv-thumbs .thumb{
  display:block;
  width:100%;
  padding:0;
  border:1px solid var(--line-strong);
  border-radius:10px;
  overflow:hidden;
  background:transparent;
  cursor:pointer;
  box-sizing: border-box;
}
/* keep as safety; main sizing is inline to defeat overrides */
#cvThumbs .thumb img{
  width:100% !important;
  height:56px !important;
  object-fit:cover !important;
  display:block !important;
}
.cv-thumbs .thumb.active{outline:2px solid var(--primary-color);outline-offset:2px}
.cv-thumbs .thumb:focus-visible{outline:2px solid var(--primary-color);outline-offset:3px}

/* Accordion */
.cv-mod .accordion-item{border-radius:12px;overflow:hidden}
.cv-mod .badge{vertical-align:middle}

/* Placeholder shimmer */
.placeholder{background:linear-gradient(90deg,#0001,#0000000d,#0001);border-radius:8px}

/* Hero skeleton (quiet, no big “Loading…” image) */
#cvHeroSkel{
  width:100%;
  height:380px;
  border-radius:16px;
  border:1px solid var(--line-strong);
  display:block;
  box-sizing: border-box;
}

/* Loading state toggles */
.is-loading #cvHeroSkel{display:block}
.is-loading #cvCover{display:none}

/* make sure modals / cards don't overflow at odd scales */
.cv-card, .cv-mod, .cv-price { width: 100%; box-sizing: border-box; }

/* Responsive adjustments */
@media (max-width: 1200px){
  .cv-wrap{ max-width: 980px; }
  #cvHeroSkel{ height:340px; }
}
@media (max-width: 992px){
  .cv-wrap{ padding: 0 12px; }
  #cvHeroSkel{ height:260px; }
  .cv-hero img{ max-height:46vh; }
}
@media (max-width: 576px){
  .cv-wrap{ margin:12px auto; padding: 0 10px; }
  #cvHeroSkel{ height:200px; border-radius:12px; }
  .cv-hero{ min-height:180px; }
  .cv-hero img{ max-height:40vh; }
}
.course-details-title {
  font-size: 2rem;
  text-align:center;
  font-weight: 700;
  margin-bottom: 12px;
  font-family: var(--font-head);

  background: linear-gradient(
    135deg,
    var(--primary-color) 0%,
    var(--ink) 100%
  );

  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

</style>

@php
  // {course} may be id | uuid | slug
  $courseParam = request()->route('course');
@endphp

<div id="cvWrap" class="cv-wrap is-loading container" data-course="{{ $courseParam }}" aria-busy="true">
  {{-- ===== Heading ===== --}}
  <div class="mb-3">
    <div class="cv-breadcrumb small text-muted mb-1">
      <a href="javascript:history.back()" class="me-1" style="display:none;"><i class="fa fa-arrow-left-long me-1"></i>Back</a>
    </div>

    
    <div id="cvBadges" style="display:none;" class="cv-badges mt-2"><!-- no “Loading” badge --></div>
  </div>

  {{-- ===== Hero ===== --}}
<h1 class="course-details-title">Course Details</h1>
  <div class="cv-hero d-flex justify-content-center mb-3">
    <div id="cvHeroSkel" class="placeholder"></div>
    {{-- tiny transparent pixel as placeholder; hidden until real cover loads --}}
    <img class="img-fluid" id="cvCover" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="Course cover">
  </div>
  

  {{-- ===== Thumb strip (gallery) ===== --}}
  <div id="cvThumbs" class="d-flex justify-content-center row g-2 mb-4 cv-thumbs"></div>
<h1 id="cvTitle" class="cv-title">—</h1>
    <div id="cvSub" class="cv-sub">—</div>
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
            <button id="cvPrimaryCta" href="javascript:void(0)" class="btn btn-primary"><i class="fa fa-cart-shopping me-1"></i>Enroll / Manage</button>
          </div>
        </div>
      </div>

      {{-- Details --}}
      <div class="cv-card cv-details">
        <div class="cv-head">Course Details</div>
        <div class="cv-body">
          <div class="row py-1" style="display:none;"><div class="col-6 lbl" >Status</div><div id="dStatus" class="col-6 val" style="display:none;">—</div></div>
          <div class="row py-1" style="display:none;"><div class="col-6 lbl" >Type</div><div id="dType" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Difficulty</div><div id="dDiff" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Language</div><div id="dLang" class="col-6 val">—</div></div>
          <div class="row py-1"><div class="col-6 lbl">Duration</div><div id="dDur" class="col-6 val">—</div></div>
          <div class="row py-1" style="display:none;"><div class="col-6 lbl">Created</div><div id="dCreated" class="col-6 val">—</div></div>
          <hr class="my-2">
          <div class="small text-muted">
            <div><b>Course ID:</b> <span id="dUuid">—</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Batch selection modal -->
<div class="modal fade" id="cvBatchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Choose a batch to enroll</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="cvBatchLoading" class="text-center py-4">
          <div class="placeholder col-10 mx-auto" style="height:14px"></div>
          <div class="placeholder col-8 mx-auto mt-2 " style="height:14px"></div>
        </div>

        <div id="cvBatchList" class="d-flex flex-column" style="display:none;"></div>

        <div id="cvBatchEmpty" class="text-muted text-center py-3" style="display:none;">
          No batches available for this course.
        </div>

        <div id="cvBatchError" class="text-danger small" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button id="cvBatchCancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- Enrollment Toast (Bootstrap 5) -->
<div aria-live="polite" aria-atomic="true" class="position-fixed" style="z-index:1080; right:1rem; bottom:1rem;">
  <div id="cvToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000" style="min-width: 260px;">
    <div class="toast-header">
      <strong class="me-auto" id="cvToastTitle">Enrollment</strong>
      <small class="text-muted" id="cvToastTime">now</small>
      <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div id="cvToastBody" class="toast-body">—</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const FETCH_TIMEOUT_MS = 10_000; // 10s
const TOKEN = (() => localStorage.getItem('token') || sessionStorage.getItem('token') || '')();
const wrap = document.getElementById('cvWrap');
const DEFAULT_COURSE_IMG = "{{ asset('assets/media/images/course/default_course.jpg') }}";

/* ---------- Utilities ---------- */
const esc = (s) => (s == null ? '' : String(s))
  .replace(/[&<>"'`]/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;' }[m]));

const money = (n, ccy='INR') => {
  if (n == null || isNaN(+n)) return '—';
  try {
    return new Intl.NumberFormat('en-IN', { style:'currency', currency: ccy, maximumFractionDigits: 0 }).format(+n);
  } catch {
    return (ccy + ' ' + (+n).toLocaleString());
  }
};

const fmtDate = (ts) => {
  if (!ts) return '—';
  const d = new Date(String(ts).replace(' ', 'T'));
  return isNaN(d) ? esc(ts) : d.toLocaleDateString(undefined, { year:'numeric', month:'short', day:'2-digit' });
};

const cleanUrl = (u) => {
  if (!u) return '';
  try { return String(u).replace(/%22"?$/,'').replace(/"$/,''); } catch { return u; }
};

function sanitizeHtml(html) {
  if (!html) return '';
  // Remove <script> tags and content
  html = html.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '');
  // Remove inline event handlers: onxxxxx="..."
  html = html.replace(/\son\w+\s*=\s*(?:"[^"]*"|'[^']*'|[^\s>]+)/gi, '');
  // Remove javascript: URIs (href or src)
  html = html.replace(/(href|src)\s*=\s*(['"]?)\s*javascript:[^'"]*\2/gi, '$1=$2#' + '$2');
  return html;
}

/* Fetch with timeout using AbortController */
async function fetchWithTimeout(url, opts = {}, timeout = FETCH_TIMEOUT_MS) {
  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), timeout);
  try {
    const res = await fetch(url, { ...opts, signal: controller.signal });
    clearTimeout(id);
    return res;
  } catch (err) {
    clearTimeout(id);
    throw err;
  }
}

/* Badge helper */
function badgeStatus(s){
  const map = { published:'success', draft:'warning', archived:'info' };
  const cls = map[s] || 'info';
  return `<span class="badge badge-${cls} text-uppercase">${esc(s||'-')}</span>`;
}

/* ---------- DOM nodes ---------- */
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

/* ---------- Course param resolution ---------- */
const COURSE_PARAM = (() => {
  const k = wrap?.dataset?.course;
  if (k) return k;
  const m = location.pathname.match(/\/courses(?:\/view)?\/([^\/?#]+)/i);
  return m ? decodeURIComponent(m[1]) : '';
})();

if (!COURSE_PARAM) {
  n.title.textContent = 'Failed to load course';
  n.sub.textContent   = 'No course key in URL';
  throw new Error('COURSE_PARAM not found');
}

/* ---------- UI helpers ---------- */
function revealHero() {
  if (n.heroSkel) n.heroSkel.style.display = 'none';
  if (n.cover) n.cover.style.display = 'block';
  wrap?.classList.remove('is-loading');
  wrap?.removeAttribute('aria-busy');
}

function hideHero() {
  if (n.heroSkel) n.heroSkel.style.display = '';
  if (n.cover) n.cover.style.display = 'none';
  wrap?.classList.add('is-loading');
  wrap?.setAttribute('aria-busy', 'true');
}

/* ---------- Rendering functions ---------- */
function renderBadges(course, pricing) {
  n.badges.innerHTML = `
    ${badgeStatus(course.status||'-')}
    <span class="badge badge-primary text-uppercase">${esc(course.course_type || 'paid')}</span>
    ${pricing.has_discount ? `<span class="badge badge-warning"><i class="fa fa-percent"></i> ${(pricing.effective_percent ?? pricing.discount_percent) || 0}% off</span>` : ''}
  `;
}

function renderGallery(media, coverUrl) {
  const gal = Array.isArray(media.gallery) ? media.gallery : [];
  if (!gal.length) {
    n.thumbs.innerHTML = '';
    return;
  }
  const html = gal.map((g, i) => {
    const url = esc(cleanUrl(g.url));
    const active = url === coverUrl ? 'active' : '';
    // make the buttons keyboard-focusable and accessible
    return `
      <div class="col-3 col-sm-2">
        <button type="button" class="thumb ${active}" data-src="${url}" aria-label="Preview image ${i+1}" tabindex="0">
          <img src="${url}" loading="lazy" alt="Gallery ${i+1}"
               style="height:56px;object-fit:cover;width:100%;display:block">
        </button>
      </div>`;
  }).join('');
  n.thumbs.innerHTML = html;

  // use event delegation so listeners survive innerHTML changes
  n.thumbs.removeEventListener('click', onThumbClick);
  n.thumbs.addEventListener('click', onThumbClick);
  n.thumbs.removeEventListener('keydown', onThumbKeyDown);
  n.thumbs.addEventListener('keydown', onThumbKeyDown);
}

function onThumbClick(e) {
  const btn = e.target.closest('.thumb');
  if (!btn) return;
  e.preventDefault();
  const src = btn.dataset.src;
  if (src) {
    n.cover.src = src;
    // mark active class
    n.thumbs.querySelectorAll('.thumb').forEach(x => x.classList.remove('active'));
    btn.classList.add('active');
    n.cover.parentElement.scrollIntoView({ behavior:'smooth', block:'center', inline:'nearest' });
  }
}
function onThumbKeyDown(e) {
  if (e.key === 'Enter' || e.key === ' ') {
    const btn = e.target.closest('.thumb');
    if (btn) {
      e.preventDefault();
      btn.click();
    }
  }
}

function renderDescription(course) {
  const long = course.full_description || '';
  // If text appears to be plain text (no < or >) we render safe text node.
  if (!/[<>]/.test(long)) {
    n.desc.innerHTML = `<div class="small" style="white-space:pre-wrap">${esc(long)}</div>`;
  } else {
    // Basic client-side sanitization & then render. Server sanitization still recommended.
    const clean = sanitizeHtml(long);
    n.desc.innerHTML = clean ? `<div class="small" style="white-space:pre-wrap">${clean}</div>` : `<div class="text-muted small">No description yet.</div>`;
  }
}

function renderModules(mods) {
  const visibleMods = Array.isArray(mods) ? mods.filter(md => String(md.status || '').toLowerCase() !== 'archived') : [];
  if (!visibleMods.length) {
    n.modWrap.innerHTML = '';
    n.modEmpty.style.display = '';
    return;
  }
  n.modEmpty.style.display = 'none';
  const sorted = visibleMods.slice().sort((a,b)=> (a.order_no ?? 999) - (b.order_no ?? 999));
  n.modWrap.innerHTML = sorted.map((mod, i) => {
    const id = `mod_${i}`, hid = `modh_${i}`;
    const short = mod.short_description ? `<div class="mb-1">${esc(mod.short_description)}</div>` : '';
    // long_description may contain markup; sanitize and present
    const long = mod.long_description ? `<div class="small text-muted" style="white-space:pre-wrap">${sanitizeHtml(mod.long_description)}</div>` : '';
    return `
      <div class="accordion-item mb-2">
        <h2 class="accordion-header" id="${hid}">
          <button class="accordion-button ${i ? 'collapsed' : ''}" type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#${id}"
                  aria-expanded="${i ? 'false' : 'true'}"
                  aria-controls="${id}">
            <b class="me-2">${i+1}.</b> ${esc(mod.title || 'Module')}
            <span class="ms-2 badge ${String(mod.status).toLowerCase()==='published'?'badge-success':'badge-warning'} text-uppercase" style="display:none;">
              ${esc(mod.status||'-')}
            </span>
          </button>
        </h2>
        <div id="${id}" class="accordion-collapse collapse ${i ? '' : 'show'}" aria-labelledby="${hid}" data-bs-parent="#cvModWrap">
          <div class="accordion-body">
            ${short}${long}
          </div>
        </div>
      </div>`;
  }).join('');
}

function renderPricing(p) {
  if (p.is_free) {
    n.priceFinal.textContent = 'Free';
    n.priceOrig.textContent = '';
    n.priceOff.style.display = 'none';
    n.priceNote.textContent = 'This course is free to enroll.';
  } else {
    n.priceFinal.textContent = money(p.final, p.currency || 'INR');
    n.priceOrig.textContent = p.has_discount ? money(p.original, p.currency || 'INR') : '';
    if (p.has_discount) {
      const off = (p.effective_percent ?? p.discount_percent) || 0;
      n.priceOff.textContent = `-${off}%`;
      n.priceOff.style.display = '';
      n.priceNote.textContent = 'Limited-time discount may apply.';
    } else {
      n.priceOff.style.display = 'none';
      n.priceNote.textContent = 'Standard pricing.';
    }
  }
}
async function checkUserEnrollmentStatus(batchId) {
    if (!TOKEN) return false;
    
    try {
        const res = await fetchWithTimeout(`/api/batches/${encodeURIComponent(batchId)}/enrollment/status`, {
            headers: { 
                'Authorization': 'Bearer ' + TOKEN,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }, 5000);
        
        if (!res.ok) {
            console.warn('Failed to check enrollment status:', res.status);
            return false;
        }
        
        const data = await res.json();
        return data.enrolled === true;
    } catch (err) {
        console.warn('Error checking enrollment status:', err);
        return false;
    }
}
// Function to update CTA button based on enrollment status
async function updateEnrollButton() {
    if (!TOKEN || !n.cta) return;
    
    // Show loading state
    n.cta.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Checking...';
    n.cta.setAttribute('disabled', 'disabled');
    n.cta.classList.add('btn-secondary');
    n.cta.classList.remove('btn-primary');
    
    try {
        // Check if user is enrolled in ANY batch of this course
        const isEnrolled = await checkUserCourseEnrollment();
        
        if (isEnrolled) {
            // User is enrolled - disable button
            n.cta.innerHTML = '<i class="fa fa-check me-1"></i>Enrolled';
            n.cta.classList.add('btn-success');
            n.cta.classList.remove('btn-secondary', 'btn-primary');
            n.cta.setAttribute('disabled', 'disabled');
            n.cta.setAttribute('aria-disabled', 'true');
            n.cta.setAttribute('title', 'You are already enrolled in this course');
            
            // Update badges
            if (n.badges) {
                n.badges.style.display = '';
                const enrolledBadge = document.createElement('span');
                enrolledBadge.className = 'badge badge-success text-uppercase ms-2';
                enrolledBadge.innerHTML = '<i class="fa fa-check me-1"></i>Enrolled';
                n.badges.appendChild(enrolledBadge);
            }
        } else {
            // User is not enrolled - enable button
            n.cta.innerHTML = '<i class="fa fa-cart-shopping me-1"></i>Enroll';
            n.cta.classList.add('btn-primary');
            n.cta.classList.remove('btn-secondary', 'btn-success');
            n.cta.removeAttribute('disabled');
            n.cta.removeAttribute('aria-disabled');
            n.cta.setAttribute('title', 'Enroll in this course');
        }
    } catch (err) {
        // On error, set to default enroll button
        console.warn('Failed to check enrollment status:', err);
        n.cta.innerHTML = '<i class="fa fa-cart-shopping me-1"></i>Enroll';
        n.cta.classList.add('btn-primary');
        n.cta.classList.remove('btn-secondary', 'btn-success');
        n.cta.removeAttribute('disabled');
        n.cta.removeAttribute('aria-disabled');
    }
}


function setupCta(course) {
  const courseUUID = course.uuid || COURSE_PARAM;

  // Make CTA a button
  n.cta.removeAttribute('href');
  n.cta.setAttribute('role', 'button');

  // Initial button setup
  if (TOKEN) {
    n.cta.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Checking...';
  } else {
    n.cta.innerHTML = '<i class="fa fa-user-plus me-1"></i>Register to enroll';
  }

  // Replace node to remove old handlers
  const clone = n.cta.cloneNode(true);
  n.cta.replaceWith(clone);
  const CTA = document.getElementById('cvPrimaryCta');

  /* --- Toast Setup --- */
  const toastEl = document.getElementById('cvToast');
  const toast = toastEl ? new bootstrap.Toast(toastEl) : null;
  const toastTitleEl = document.getElementById('cvToastTitle');
  const toastBodyEl = document.getElementById('cvToastBody');
  const toastTimeEl = document.getElementById('cvToastTime');

  function showToast(title, body, short = false) {
    if (!toastEl || !toast) {
      try { alert(title + '\n\n' + body); } catch(e){ console.log(title, body); }
      return;
    }
    if (toastTitleEl) toastTitleEl.textContent = title;
    if (toastBodyEl) toastBodyEl.textContent = body;
    if (toastTimeEl) toastTimeEl.textContent = short ? 'now' : new Date().toLocaleTimeString();
    toast.show();
  }

  /* --- Batch modal elements --- */
  const batchModalEl = document.getElementById('cvBatchModal');
  const batchModal = batchModalEl ? new bootstrap.Modal(batchModalEl) : null;
  const batchList = document.getElementById('cvBatchList');
  const batchLoading = document.getElementById('cvBatchLoading');
  const batchEmpty = document.getElementById('cvBatchEmpty');
  const batchError = document.getElementById('cvBatchError');

  /* --- Helpers --- */
  function idFromJwt(token) {
    if (!token || typeof token !== 'string') return null;
    const parts = token.split('.');
    if (parts.length !== 3) return null;
    try {
      let payload = parts[1].replace(/-/g, '+').replace(/_/g, '/');
      const pad = payload.length % 4;
      if (pad) payload += '='.repeat(4 - pad);
      const decoded = atob(payload);
      const obj = JSON.parse(decoded);
      return obj.user_id ?? obj.id ?? obj.sub ?? obj.uid ?? null;
    } catch (e) {
      return null;
    }
  }

  async function resolveUserId() {
    const stored = localStorage.getItem('user_id') || sessionStorage.getItem('user_id');
    if (stored && /^\d+$/.test(String(stored))) return Number(stored);
    const token = TOKEN || localStorage.getItem('token') || sessionStorage.getItem('token');
    const id = idFromJwt(token);
    if (id && /^\d+$/.test(String(id))) {
      try { localStorage.setItem('user_id', String(id)); } catch(e){/* ignore */ }
      return Number(id);
    }
    return null;
  }

  let cachedCourseNumericId = null;
  async function resolveCourseNumericId() {
    if (cachedCourseNumericId) return cachedCourseNumericId;
    const stored = wrap?.dataset?.courseNumericId || localStorage.getItem(`course_numeric_${courseUUID}`);
    if (stored && /^\d+$/.test(String(stored))) {
      cachedCourseNumericId = Number(stored);
      return cachedCourseNumericId;
    }
    try {
      const res = await fetchWithTimeout(`/api/courses/${encodeURIComponent(courseUUID)}`, {
        headers: { 'Authorization': TOKEN ? 'Bearer ' + TOKEN : '', 'Accept': 'application/json' },
        credentials: 'same-origin'
      }, FETCH_TIMEOUT_MS);
      const j = await res.json().catch(()=>null);
      if (!res.ok) {
        const msg = j?.message || `Unable to resolve course (${res.status})`;
        if (batchError) { batchError.style.display = ''; batchError.textContent = msg; }
        throw new Error(msg);
      }
      const numericId = j?.data?.id || j?.id;
      if (!numericId || !/^\d+$/.test(String(numericId))) {
        const msg = 'Course resource fetched but numeric id not found';
        if (batchError) { batchError.style.display = ''; batchError.textContent = msg; }
        throw new Error(msg);
      }
      cachedCourseNumericId = Number(numericId);
      try {
        localStorage.setItem(`course_numeric_${courseUUID}`, String(cachedCourseNumericId));
        if (wrap) wrap.dataset.courseNumericId = String(cachedCourseNumericId);
      } catch (e) { /* ignore */ }
      return cachedCourseNumericId;
    } catch (err) {
      console.error('resolveCourseNumericId error', err);
      throw err;
    }
  }

  function getBatchDate(batch, candidates = ['starts_at','start_at','start_date','starts','start']) {
    if (!batch) return null;
    for (const k of candidates) {
      const raw = batch[k];
      if (!raw && raw !== 0) continue;
      const s = String(raw).trim();
      if (!s || s === '0000-00-00' || s.startsWith('0000-00-00')) continue;
      if (/^\d+$/.test(s)) {
        const n = Number(s);
        const d = new Date(n > 1e12 ? n : n * 1000);
        if (!isNaN(d)) return d;
      }
      const d = new Date(s.replace(' ', 'T'));
      if (!isNaN(d)) return d;
      const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (m) {
        const dd = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
        if (!isNaN(dd)) return dd;
      }
    }
    return null;
  }

  function isUserEnrolled(batch, userId) {
    if (!userId || !batch) return false;
    
    // Direct flags
    if (batch.is_enrolled === true) return true;
    if (batch.user_enrolled === true) return true;
    if (batch.enrolled === true) return true;
    if (batch.enrollment_status === 'verified') return true;
    
    // Check enrollment object status
    if (batch.my_enrollment) {
      const status = String(batch.my_enrollment.status || '').toLowerCase();
      return ['verified', 'pending', 'accepted', 'enrolled'].includes(status);
    }
    
    // Check enrolled users array
    if (Array.isArray(batch.enrolled_users)) {
      return batch.enrolled_users.some(u => 
        String(u.user_id || u.id) === String(userId)
      );
    }
    
    // Legacy checks
    if (Array.isArray(batch.enrolled_user_ids)) {
      return batch.enrolled_user_ids.some(x => String(x) === String(userId));
    }
    
    if (Array.isArray(batch.students)) {
      return batch.students.some(st => 
        st && (String(st.user_id || st.id) === String(userId))
      );
    }
    
    if (String(batch.current_user_id) === String(userId)) return true;
    
    return false;
  }
  /* --- Disable other batch buttons when a batch is selected/enrolled --- */
  function disableOtherBatchButtons(selectedBatchId) {
    if (!batchList) return;
    // find every select button
    const selBtns = Array.from(batchList.querySelectorAll('.btn-select-batch, .btn-outline-secondary'));
    for (const b of selBtns) {
      const bid = b.dataset.batch || '';
      // keep the selected one as 'Enrolled' (already handled elsewhere)
      if (String(bid) === String(selectedBatchId)) continue;

      // mark as disabled because user already enrolled in a different batch
      b.className = 'btn btn-outline-secondary btn-sm disabled';
      b.setAttribute('disabled', 'disabled');
      b.setAttribute('aria-disabled', 'true');

      // Add a friendly title explaining why it's disabled (avoid duplicate titles)
      if (!b.getAttribute('title')) {
        b.setAttribute('title', 'You are already enrolled in another batch of this course');
      }
    }
  }

  // SIMPLIFIED: Use only this API function to check enrollment
  async function checkUserEnrollmentStatus(batchId) {
    if (!TOKEN) return false;
    
    try {
      const res = await fetchWithTimeout(`/api/batches/${encodeURIComponent(batchId)}/enrollment/status`, {
        headers: { 
          'Authorization': 'Bearer ' + TOKEN,
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      }, 5000);
      
      if (!res.ok) {
        console.warn('Failed to check enrollment status:', res.status);
        return false;
      }
      
      const data = await res.json();
      return data.enrolled === true;
    } catch (err) {
      console.warn('Error checking enrollment status:', err);
      return false;
    }
  }

  /* --- Keep full batch details for later --- */
  const batchMap = new Map();

  /* --- Batch card render --- */
  async function batchCardHtml(batch, userId) {
    const id = batch.uuid || batch.id || '';
    const title = esc(batch.name || batch.title || 'Batch');
    const badgeTitle = batch.badge_title ? `<span class="badge bg-primary small me-1">${esc(batch.badge_title)}</span>` : '';
    const startD = getBatchDate(batch, ['starts_at','start_at','start_date','starts','start']);
    const endD   = getBatchDate(batch, ['ends_at','end_at','end_date','ends','end']);
    const start = startD ? fmtDate(startD.toISOString()) : 'TBD';
    const end = endD ? fmtDate(endD.toISOString()) : null;
    const dateHtml = end ? `<div class="small text-muted">Starts: <strong>${esc(start)}</strong> · Ends: <strong>${esc(end)}</strong></div>` : `<div class="small text-muted">Starts: <strong>${esc(start)}</strong></div>`;
    const seats = batch.capacity || batch.seats ? `<div class="small text-muted mt-1">${esc(String(batch.capacity || batch.seats))} seats</div>` : '';
    const descText = batch.description || batch.short_description || batch.tagline || batch.note || '';
    const desc  = descText ? `<div class="mt-2 small text-muted" style="white-space:pre-wrap">${esc(descText)}</div>` : '';

    // SIMPLIFIED: Check enrollment using only the API
    let enrolled = false;
    if (TOKEN && id) {
      enrolled = await checkUserEnrollmentStatus(id);
    } else {
      enrolled = isUserEnrolled(batch, userId);
    }

    const btnClass = enrolled ? 'btn btn-outline-secondary btn-sm disabled' : 'btn btn-primary btn-sm btn-select-batch';
    const btnAttrs = enrolled ? 'disabled aria-disabled="true" title="You are already enrolled"' : `data-batch="${esc(id)}"`;

    return `
      <div class="col-12 col-md-12 mb-3" aria-label="Batch ${esc(title)}">
        <article class="cv-card p-3 h-100 d-flex flex-column" data-batch-card="${esc(id)}" role="button" tabindex="0">
          <header class="d-flex align-items-start justify-content-between">
            <div style="min-width:0;">
              <div class="d-flex align-items-center mb-1">
                ${badgeTitle}
                <h3 class="h6 mb-0 text-truncate" style="max-width:72%">${title}</h3>
              </div>
              ${dateHtml}
              ${seats}
            </div>
            <div class="ms-3 text-end">
              <button type="button" class="${btnClass}" ${btnAttrs} ${enrolled ? '' : 'aria-label="Select batch"'}>${enrolled ? '<i class="fa fa-check me-1"></i>Enrolled' : 'Select'}</button>
              <div class="small text-muted mt-2">${esc(String(batch.status || 'open').toUpperCase())}</div>
            </div>
          </header>
          <div class="mt-2" style="margin-top:auto">
            ${desc}
          </div>
        </article>
      </div>
    `;
  }

  /* --- Create or ensure details modal exists in DOM --- */
  function ensureBatchDetailModal() {
    let el = document.getElementById('cvBatchDetailModal');
    if (el) return el;
    const modalHtml = `
      <div class="modal fade" id="cvBatchDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 id="cvBatchDetailTitle" class="modal-title"></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cvBatchDetailBody"></div>
            <div class="modal-footer">
              <button type="button" id="cvBatchDetailEnrollBtn" class="btn btn-primary">Select / Enroll</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    `;
    const wrapper = document.createElement('div');
    wrapper.innerHTML = modalHtml;
    document.body.appendChild(wrapper.firstElementChild);
    return document.getElementById('cvBatchDetailModal');
  }

  /* --- Build HTML for details --- */
  function buildBatchDetailsHtml(batch) {
    if (!batch) return '<div class="text-muted">No details available.</div>';
    
    const title = esc(batch.name || batch.title || 'Batch');
    const badge = batch.badge_title ? 
      `<span class="badge bg-primary small me-1">${esc(batch.badge_title)}</span>` : '';
    
    const badgeDesc = batch.badge_description ? 
      `<div class="mt-1 small text-muted"><i class="fa fa-info-circle me-1"></i>${esc(batch.badge_description)}</div>` : '';
    
    const tagline = batch.tagline || batch.tag_line || batch.subtitle || '';
    const taglineHtml = tagline ? `<div class="mb-3"><em class="text-muted fs-6">${esc(tagline)}</em></div>` : '';
    
    const startD = getBatchDate(batch, ['starts_at','start_at','start_date','starts','start']);
    const endD = getBatchDate(batch, ['ends_at','end_at','end_date','ends','end']);
    const start = startD ? fmtDate(startD.toISOString()) : 'TBD';
    const end = endD ? fmtDate(endD.toISOString()) : 'TBD';
    
    const scheduleHtml = `
      <div class="mb-2"><i class="fa fa-calendar-start text-primary me-2"></i><strong>Start:</strong> ${esc(start)}</div>
      <div class="mb-2"><i class="fa fa-calendar-check text-success me-2"></i><strong>End:</strong> ${esc(end)}</div>
    `;
    
    const seats = (batch.capacity || batch.seats) ? 
      `<div class="mb-2"><i class="fa fa-users text-info me-2"></i><strong>Seats:</strong> ${esc(String(batch.capacity || batch.seats))}</div>` : '';
    
    const mode = batch.mode ? 
      `<div class="mb-2"><i class="fa fa-laptop text-warning me-2"></i><strong>Mode:</strong> ${esc(batch.mode)}</div>` : '';
    
    const status = batch.status ? 
      `<div class="mb-2"><i class="fa fa-info-circle text-secondary me-2"></i><strong>Status:</strong> <span class="badge bg-${batch.status === 'open' ? 'success' : 'secondary'}">${esc(batch.status)}</span></div>` : '';
    
    const desc = batch.description || batch.long_description || batch.short_description || batch.note || '';
    const descHtml = desc ? 
      `<div class="mt-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
        <strong class="d-block mb-2"><i class="fa fa-align-left me-2"></i>Description</strong>
        <div class="small text-muted" style="white-space:pre-wrap">${esc(desc)}</div>
      </div>` : '';

    let instrHtml = '';
    if (batch.instructors && Array.isArray(batch.instructors) && batch.instructors.length) {
      instrHtml = `<div class="mt-3">
        <strong class="d-block mb-2"><i class="fa fa-chalkboard-teacher me-2"></i>Instructors</strong>
        <ul class="list-unstyled small">` +
        batch.instructors.map(i => 
          `<li class="mb-1"><i class="fa fa-user-tie text-primary me-2"></i>${esc(i.name || i.full_name || i.title || i)}</li>`
        ).join('') +
        `</ul>
      </div>`;
    } else if (batch.instructor_name || batch.teacher) {
      const name = batch.instructor_name || batch.teacher;
      instrHtml = `<div class="mt-3">
        <strong class="d-block mb-2"><i class="fa fa-chalkboard-teacher me-2"></i>Instructor</strong>
        <div class="small"><i class="fa fa-user-tie text-primary me-2"></i>${esc(name)}</div>
      </div>`;
    }

    let metaHtml = '';
    if (batch.metadata) {
      let metaObj = batch.metadata;
      if (typeof metaObj === 'string') {
        try { metaObj = JSON.parse(metaObj); } catch(e){ metaObj = null; }
      }
      if (metaObj && typeof metaObj === 'object') {
        const keys = Object.keys(metaObj);
        if (keys.length) {
          metaHtml = `<div class="mt-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
            <strong class="d-block mb-2"><i class="fa fa-info-circle me-2"></i>Additional Information</strong>
            <dl class="row small mb-0">` +
            keys.map(k => 
              `<dt class="col-5 text-muted">${esc(k)}</dt><dd class="col-7">${esc(String(metaObj[k]))}</dd>`
            ).join('') +
            `</dl>
          </div>`;
        }
      }
    }

    return `
      <div>
        <div class="mb-3 pb-2 border-bottom">
          ${badge}<h4 class="d-inline-block align-middle ms-1 mb-0">${title}</h4>
          ${badgeDesc}
        </div>
        ${taglineHtml}
        <div class="row">
          <div class="col-md-6">
            ${scheduleHtml}
            ${seats}
            ${mode}
            ${status}
          </div>
          <div class="col-md-6">
            ${instrHtml}
          </div>
        </div>
        ${descHtml}
        ${metaHtml}
      </div>
    `;
  }

  /* --- Fetch batches --- */
  async function fetchBatches() {
    if (!batchList || !batchLoading || !batchEmpty) {
      location.href = `/enroll?course=${encodeURIComponent(courseUUID)}`;
      return;
    }

    batchList.style.display = 'none';
    batchEmpty.style.display = 'none';
    if (batchError) batchError.style.display = 'none';
    batchLoading.style.display = '';

    let numericId;
    try {
      numericId = await resolveCourseNumericId();
    } catch (err) {
      batchLoading.style.display = 'none';
      showBatchError(err?.message || 'Failed to resolve course id');
      return;
    }

    const url = `/api/batches?course_id=${encodeURIComponent(numericId)}`;
    try {
      const res = await fetchWithTimeout(url, {
        headers: { 'Authorization': TOKEN ? 'Bearer ' + TOKEN : '', 'Accept': 'application/json' },
        credentials: 'same-origin'
      }, FETCH_TIMEOUT_MS);

      const json = await res.json().catch(()=>null);
      batchLoading.style.display = 'none';

      if (!res.ok) {
        const msg = json?.message || (json?.errors ? JSON.stringify(json.errors) : `Failed to load batches (${res.status})`);
        showBatchError(msg);
        return;
      }

      let batches = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : (Array.isArray(json?.batches) ? json.batches : []));
      if (!batches || !batches.length) {
        batchEmpty.style.display = '';
        return;
      }

      // Get current user id
      const currentUserId = await resolveUserId();

      // Filter: only future batches
      const now = new Date();
      batches = batches.filter(b => {
        const s = getBatchDate(b, ['starts_at','start_at','start_date','starts','start']);
        return s && s > now;
      });

      if (!batches.length) {
        batchEmpty.style.display = '';
        return;
      }

      // Sort by start date
      batches.sort((a,b) => {
        const as = getBatchDate(a, ['starts_at','start_at','start_date','starts','start']);
        const bs = getBatchDate(b, ['starts_at','start_at','start_date','starts','start']);
        if (!as && !bs) return 0;
        if (!as) return 1;
        if (!bs) return -1;
        return as.getTime() - bs.getTime();
      });

      // Store in map
      batchMap.clear();
      for (const b of batches) {
        const key = String(b.uuid || b.id || '');
        if (key) batchMap.set(key, b);
      }

      // Render batches
      const batchCardsHtml = [];
      for (const batch of batches) {
        const cardHtml = await batchCardHtml(batch, currentUserId);
        batchCardsHtml.push(cardHtml);
      }
      
      batchList.innerHTML = batchCardsHtml.join('');
      batchList.style.display = '';
(function enforceSingleEnrollmentOnRender(){
  try {
    const enrolledBtn = batchList.querySelector(
      '.btn-outline-secondary.disabled, .btn-select-batch[disabled], button:disabled'
    );

    const enrolledByText =
      !enrolledBtn &&
      Array.from(batchList.querySelectorAll('button, .btn-select-batch'))
        .find(b => /Enrolled/i.test(b.innerText || ''));

    const btn = enrolledBtn || enrolledByText;

    if (btn) {
      const bid = btn.dataset.batch || btn.getAttribute('data-batch') || null;
      if (bid) disableOtherBatchButtons(bid);
    }
  } catch (e) {
    // ignore silently
  }
})();
    } catch (err) {
      console.error('fetchBatches error', err);
      batchLoading.style.display = 'none';
      showBatchError('Unable to load batches. Try again later.');
    }
  }

  function showBatchError(msg) {
    if (batchError) {
      batchError.style.display = '';
      batchError.textContent = msg;
    }
  }

  /* --- Enroll into selected batch --- */
  let enrolling = false;
  async function enrollIntoBatch(batchId) {
    if (enrolling) return;
    enrolling = true;

    const selBtns = batchList ? Array.from(batchList.querySelectorAll('.btn-select-batch, .btn-outline-secondary')) : [];
    selBtns.forEach(b => b.setAttribute('disabled', 'disabled'));

    const userId = await resolveUserId();
    if (!userId) {
      console.warn('user_id not found locally; proceeding without it and relying on token on server');
    }

    const batch = batchMap.get(String(batchId));
    const batchName = batch ? (batch.name || batch.title || `Batch`) : `Batch`;
    const startText = batch && (batch.start_date || batch.start_at) ? fmtDate(batch.start_date || batch.start_at) : null;
    const endText   = batch && (batch.end_date || batch.end_at) ? fmtDate(batch.end_date || batch.end_at) : null;
    const descText  = batch && (batch.description || batch.short_description || batch.note) || '';

    let infoLine = batchName;
    if (startText && endText) infoLine += ` (Starts ${startText}, Ends ${endText})`;
    else if (startText) infoLine += ` (Starts ${startText})`;

    // Show processing modal
    Swal.fire({
      title: 'Processing enrollment',
      html: `Processing enrollment for <strong>${esc(infoLine)}</strong>. Please wait...`,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Enroll endpoint
    const url = `/api/batches/${encodeURIComponent(batchId)}/students/enroll`;
    const headers = { 'Authorization': TOKEN ? 'Bearer ' + TOKEN : '', 'Accept': 'application/json' };
    let body = null;

    if (userId) {
      headers['Content-Type'] = 'application/json';
      body = JSON.stringify({ user_id: Number(userId) });
    }

    try {
      const opts = { method: 'POST', headers, credentials: 'same-origin' };
      if (body) opts.body = body;

      const res = await fetchWithTimeout(url, opts, FETCH_TIMEOUT_MS);
      const j = await res.json().catch(()=>null);

      // Auth issues
      if (res.status === 401 || res.status === 403) {
        Swal.close();
        await Swal.fire({
          icon: 'warning',
          title: 'Session expired',
          text: 'Your session is invalid or expired. You will be redirected to login.',
          confirmButtonText: 'Login'
        });
        const next = location.pathname + location.search;
        location.href = `/register?next=${encodeURIComponent(next)}`;
        return;
      }

      if (res.ok) {
        const baseMsg = j?.message || (res.status === 201 ? 'Student enrolled' : 'Enrollment recorded');
        const detailMsg = descText ? `${baseMsg} • ${descText}` : baseMsg;

        // Close processing modal
        Swal.close();

        // Show success toast
        Swal.fire({
          icon: 'success',
          title: 'Enrollment recorded',
text: `Your request for enrolling in ${batchName} has been registered. Our team will get back to you after the verification process.`,
          toast: true,
          position: 'bottom-end',
          timer: 4000,
          showConfirmButton: false
        });

        // Update UI
        const btn = batchList.querySelector(`[data-batch="${CSS.escape(String(batchId))}"]`);
        if (btn) {
          btn.className = 'btn btn-outline-secondary btn-sm disabled';
          btn.setAttribute('disabled', 'disabled');
          btn.setAttribute('aria-disabled', 'true');
          btn.innerHTML = '<i class="fa fa-check me-1"></i>Enrolled';
        }
        
        // Update main CTA button to show "Enrolled"
        CTA.innerHTML = '<i class="fa fa-check me-1"></i>Enrolled';
        CTA.classList.add('btn-success');
        CTA.classList.remove('btn-primary');
        CTA.setAttribute('disabled', 'disabled');
        CTA.setAttribute('aria-disabled', 'true');
        CTA.setAttribute('title', 'You are already enrolled in this course');
        
        // Update badges
        if (n.badges) {
          n.badges.style.display = '';
          const enrolledBadge = document.createElement('span');
          enrolledBadge.className = 'badge badge-success text-uppercase ms-2';
          enrolledBadge.innerHTML = '<i class="fa fa-check me-1"></i>Enrolled';
          n.badges.appendChild(enrolledBadge);
        }
        
        setTimeout(()=>{ if (batchModal) batchModal.hide(); }, 700);
      } else {
        Swal.close();
        if (j?.errors) {
          const keys = Object.keys(j.errors || {});
          const errMsg = keys.length
            ? keys.map(k => `${k}: ${((j.errors[k]||[])[0]||'')}`).join('; ')
            : (j.message || `Server error (${res.status})`);
          await Swal.fire({ icon: 'error', title: 'Enrollment failed', text: errMsg });
        } else {
          await Swal.fire({ icon: 'error', title: 'Enrollment failed', text: j?.message || `Server error (${res.status})` });
        }
        selBtns.forEach(b => b.removeAttribute('disabled'));
      }
    } catch (err) {
      console.error('enrollIntoBatch error', err);
      Swal.close();
      await Swal.fire({ icon: 'error', title: 'Network error', text: 'Unable to enroll. Please try again.' });
      selBtns.forEach(b => b.removeAttribute('disabled'));
    } finally {
      enrolling = false;
    }
  }

  /* --- Delegated click handler for Select buttons --- */
  function delegatedClick(e) {
    const btn = e.target.closest && e.target.closest('.btn-select-batch');
    if (!btn) return;
    const batchId = btn.dataset.batch;
    if (!batchId) return;

    // Show confirmation dialog
    Swal.fire({
      title: 'Confirm enrollment',
      text: 'Do you want to enroll in this batch?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, enroll me',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      focusCancel: true
    }).then((result) => {
      if (result.isConfirmed) {
        enrollIntoBatch(batchId);
      }
    });
  }

  if (batchList) {
    batchList.removeEventListener('click', delegatedClick);
    batchList.addEventListener('click', delegatedClick);
  }

  /* --- Delegated click handler for showing batch details --- */
  function delegatedCardClick(e) {
    const art = e.target.closest && e.target.closest('article[data-batch-card]');
    if (!art) return;
    if (e.target.closest && e.target.closest('.btn-select-batch')) return;
    const batchId = art.getAttribute('data-batch-card');
    if (!batchId) return;
    showBatchDetails(batchId);
  }

  if (batchList) {
    batchList.removeEventListener('click', delegatedCardClick);
    batchList.addEventListener('click', delegatedCardClick);
    batchList.removeEventListener('keydown', onBatchCardKeyDown);
    batchList.addEventListener('keydown', onBatchCardKeyDown);
  }

  function onBatchCardKeyDown(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      const art = e.target.closest && e.target.closest('article[data-batch-card]');
      if (art) {
        e.preventDefault();
        showBatchDetails(art.getAttribute('data-batch-card'));
      }
    }
  }

  function showBatchDetails(batchId) {
    const modalEl = ensureBatchDetailModal();
    const bs = batchMap.get(String(batchId));
    const titleEl = modalEl.querySelector('#cvBatchDetailTitle');
    const bodyEl = modalEl.querySelector('#cvBatchDetailBody');
    const enrollBtn = modalEl.querySelector('#cvBatchDetailEnrollBtn');

    if (!bs) {
      if (titleEl) titleEl.textContent = 'Batch details';
      if (bodyEl) bodyEl.innerHTML = '<div class="text-muted">Batch details not available.</div>';
      const m = new bootstrap.Modal(modalEl);
      m.show();
      return;
    }

    if (titleEl) titleEl.textContent = bs.name || bs.title || 'Batch details';
    if (bodyEl) bodyEl.innerHTML = buildBatchDetailsHtml(bs);

    // Check if user is already enrolled in this batch
    checkUserEnrollmentStatus(batchId).then(isEnrolled => {
      if (isEnrolled) {
    enrollBtn.innerHTML = '<i class="fa fa-check me-1"></i>Already Enrolled';
    enrollBtn.classList.add('btn-success', 'disabled');
    enrollBtn.classList.remove('btn-primary');

    // Disable all interactions
    enrollBtn.setAttribute('aria-disabled', 'true');
    enrollBtn.style.pointerEvents = 'none';
    enrollBtn.style.opacity = '0.7';
}
 else {
        enrollBtn.innerHTML = 'Select / Enroll';
        enrollBtn.classList.add('btn-primary');
        enrollBtn.classList.remove('btn-success');
        enrollBtn.removeAttribute('disabled');
        enrollBtn.removeAttribute('aria-disabled');
        
        // Add click handler for enrollment
        enrollBtn.onclick = (ev) => {
          ev.preventDefault();
          enrollIntoBatch(String(batchId));
          const m = bootstrap.Modal.getInstance(modalEl);
          if (m) m.hide();
        };
      }
    });

    const m = new bootstrap.Modal(modalEl);
    m.show();
  }

  /* --- SIMPLIFIED: Update CTA button using only batch enrollment checks --- */
  async function updateCTAButton() {
  if (!TOKEN || !CTA) return;

  // Show loading state
  CTA.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Checking...';
  CTA.classList.add('btn-secondary');
  CTA.classList.remove('btn-primary');
  CTA.setAttribute('aria-busy', 'true');          // loading flag
  CTA.style.pointerEvents = 'none';                // prevent clicks while loading

  try {
    // Get all batches for the course
    const numericId = await resolveCourseNumericId();
    if (!numericId) {
      CTA.innerHTML = '<i class="fa fa-cart-shopping me-1"></i>Enroll';
      CTA.classList.add('btn-primary');
      CTA.classList.remove('btn-secondary');
      CTA.removeAttribute('aria-busy');
      CTA.style.pointerEvents = 'auto';
      return;
    }

    const url = `/api/batches?course_id=${encodeURIComponent(numericId)}`;
    const res = await fetchWithTimeout(url, {
      headers: { 'Authorization': TOKEN ? 'Bearer ' + TOKEN : '', 'Accept': 'application/json' },
      credentials: 'same-origin'
    }, FETCH_TIMEOUT_MS);

    if (!res.ok) throw new Error(`Failed to fetch batches: ${res.status}`);

    const json = await res.json().catch(() => null);
    let batches = [];

    if (json?.data && Array.isArray(json.data)) batches = json.data;
    else if (Array.isArray(json?.batches)) batches = json.batches;
    else if (Array.isArray(json)) batches = json;

    // Check each batch for enrollment
    let isEnrolledInAnyBatch = false;
    for (const batch of batches) {
      const batchId = batch.uuid || batch.id;
      if (!batchId) continue;
      const isEnrolled = await checkUserEnrollmentStatus(batchId);
      if (isEnrolled) { isEnrolledInAnyBatch = true; break; }
    }

    if (isEnrolledInAnyBatch) {
      // User is enrolled in at least one batch -> show disabled Enrolled CTA
      CTA.innerHTML = '<i class="fa fa-check me-1"></i>Enrolled';
      CTA.classList.add('btn-success', 'disabled');
      CTA.classList.remove('btn-secondary', 'btn-primary');

      // Properly disable an anchor
      CTA.setAttribute('aria-disabled', 'true');
      CTA.setAttribute('tabindex', '-1');
      CTA.removeAttribute('href');            // prevents navigation in older browsers
      CTA.style.pointerEvents = 'none';
      CTA.setAttribute('title', 'You are already enrolled in this course');

      // Update badges (avoid duplicates)
      if (n.badges) {
        n.badges.style.display = '';
        if (!n.badges.querySelector('.badge-enrolled')) {
          const enrolledBadge = document.createElement('span');
          enrolledBadge.className = 'badge badge-success text-uppercase ms-2 badge-enrolled';
          enrolledBadge.innerHTML = '<i class="fa fa-check me-1"></i>Enrolled';
          n.badges.appendChild(enrolledBadge);
        }
      }
    } else {
      // Not enrolled -> enable CTA
      CTA.innerHTML = '<i class="fa fa-cart-shopping me-1"></i>Enroll';
      CTA.classList.add('btn-primary');
      CTA.classList.remove('btn-secondary', 'btn-success', 'disabled');
      CTA.removeAttribute('aria-busy');
      CTA.removeAttribute('aria-disabled');
      CTA.removeAttribute('tabindex');
      CTA.removeAttribute('title');
      CTA.style.pointerEvents = 'auto';

      // restore href if you want the anchor to navigate (optional)
      // CTA.setAttribute('href', '#'); // keep as needed
    }
  } catch (err) {
    console.warn('Failed to update CTA button:', err);
    CTA.innerHTML = '<i class="fa fa-cart-shopping me-1"></i>Enroll';
    CTA.classList.add('btn-primary');
    CTA.classList.remove('btn-secondary', 'btn-success', 'disabled');
    CTA.removeAttribute('aria-busy');
    CTA.removeAttribute('aria-disabled');
    CTA.style.pointerEvents = 'auto';
  }
}

/* --- CTA CLICK => OPEN MODAL & FETCH --- */
CTA.addEventListener('click', async (ev) => {
  ev.preventDefault();

  // Ignore clicks while loading or disabled
  if (CTA.getAttribute('aria-busy') === 'true' || CTA.getAttribute('aria-disabled') === 'true') {
    return;
  }

  if (!TOKEN) {
    const next = location.pathname + location.search;
    location.href = `/register?next=${encodeURIComponent(next)}`;
    return;
  }
    // Double-check if CTA is disabled (already enrolled)
    if (CTA.getAttribute('disabled') === 'disabled' && CTA.classList.contains('btn-success')) {
      // User is already enrolled, show message
      if (window.Swal) {
        Swal.fire({
          icon: 'info',
          title: 'Already Enrolled',
          text: 'You are already enrolled in this course.',
          confirmButtonText: 'OK'
        });
      } else {
        alert('You are already enrolled in this course.');
      }
      return;
    }

    if (!batchModalEl || !batchModal || !batchList) {
      location.href = `/enroll?course=${encodeURIComponent(courseUUID)}`;
      return;
    }

    if (batchError) { batchError.style.display = 'none'; batchError.textContent = ''; }
    batchList.innerHTML = '';
    batchList.style.display = 'none';
    batchEmpty.style.display = 'none';
    batchLoading.style.display = '';

    batchModal.show();
    await fetchBatches();
  });

  // Update the CTA button based on enrollment status
  updateCTAButton();
}
/* ---------- Main loader ---------- */
async function loadCourse() {
  hideHero();
  try {
    const res = await fetchWithTimeout(`/api/courses/${encodeURIComponent(COURSE_PARAM)}/view`, {
      headers: { 'Authorization': TOKEN ? 'Bearer ' + TOKEN : '', 'Accept': 'application/json' },
      credentials: 'same-origin'
    }, FETCH_TIMEOUT_MS);

    if (res.status === 401 || res.status === 403) {
      // Not authenticated — still allow public view but adjust CTA
      console.warn('Unauthenticated request; loading public data if available.');
    }

    const j = await res.json().catch(() => ({ error: 'Invalid JSON response' }));
    if (!res.ok) {
      const msg = j?.message || `Failed to load course (${res.status})`;
      throw new Error(msg);
    }

    const d = j?.data || {};
    const c = d.course || {};
    const p = d.pricing || {};
    const m = d.media || {};
    const mods = Array.isArray(d.modules) ? d.modules : [];

    // Title + meta
    n.title.textContent = c.title || 'Untitled Course';
    n.sub.textContent = c.short_description || '—';
    renderBadges(c, p);

    // Hero image
    const coverUrl = cleanUrl(m?.cover?.url) || cleanUrl((Array.isArray(m.gallery) && m.gallery[0]?.url) || '')||
  DEFAULT_COURSE_IMG;
    if (coverUrl) {
      n.cover.onload = () => revealHero();
      n.cover.onerror = () => revealHero();
      // assign src last to trigger load
      n.cover.src = coverUrl;
      if (n.cover.complete) revealHero();
    } else {
      revealHero();
    }

    // Gallery
    renderGallery(m, coverUrl);

    // Description
    renderDescription(c);

    // Modules
    renderModules(mods);

    // Pricing
    renderPricing(p);

    // Details
    n.dStatus.innerHTML  = badgeStatus(c.status||'-');
    n.dType.textContent  = (c.course_type||'paid').toUpperCase();
    n.dDiff.textContent  = c.difficulty || '—';
    n.dLang.textContent  = c.language   || '—';
    n.dDur.textContent   = c.duration_hours ? (c.duration_hours + ' hour' + (Number(c.duration_hours) == 1 ? '' : 's')) : '—';
    n.dCreated.textContent = fmtDate(c.created_at);
    n.dUuid.textContent    = c.uuid || '—';

    // CTA
    setupCta(c);
  } catch (err) {
    console.error('loadCourse error:', err);
    n.title.textContent = 'Failed to load course';
    n.sub.textContent = err?.message || 'An unexpected error occurred';
    // ensure skeleton hidden so page doesn't look stuck
    if (n.heroSkel) n.heroSkel.remove();
    if (n.cover) n.cover.style.display = 'none';
    wrap?.classList.remove('is-loading');
    wrap?.removeAttribute('aria-busy');
  }
}

document.addEventListener('DOMContentLoaded', loadCourse, { once: true });
</script>
