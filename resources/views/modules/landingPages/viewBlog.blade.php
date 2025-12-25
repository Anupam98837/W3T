{{-- resources/views/global/blog-view.blade.php --}}
@section('title','Blog')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
:root{
  --blog-bg:
    radial-gradient(circle at top left, rgba(149,30,170,.12), transparent 60%),
    radial-gradient(circle at bottom right, rgba(201,79,240,.10), transparent 55%),
    var(--surface, #f9f5ff);
  --blog-ink: var(--ink, #1c1324);
  --blog-muted: var(--muted-color, #6b7280);
  --blog-primary: #951eaa;
  --blog-line: rgba(149,30,170,.16);
}

/* ===== PAGE SHELL ===== */
.blog-page{
  position:relative;
  padding:72px 0 110px;
  background:var(--blog-bg);
  overflow:hidden;
  isolation:isolate;
}

/* soft vignette */
.blog-page::before{
  content:"";
  position:absolute;
  inset:-40%;
  background:
    radial-gradient(circle at 15% 10%, rgba(255,255,255,.85), transparent 55%),
    radial-gradient(circle at 80% 90%, rgba(255,255,255,.55), transparent 55%);
  mix-blend-mode:soft-light;
  pointer-events:none;
  z-index:-1;
}

/* blobs */
.blog-blob{
  position:absolute;
  width:160px;height:160px;border-radius:50%;
  opacity:.22;pointer-events:none;mix-blend-mode:screen;z-index:0;
}
.blog-blob.blob-1{
  top:6%;left:-40px;
  background:conic-gradient(from 220deg, #951eaa, #c94ff0, #f472b6, #951eaa);
  animation: blobDrift1 22s ease-in-out infinite alternate;
}
.blog-blob.blob-2{
  bottom:-60px;right:-40px;
  background:conic-gradient(from 140deg, #facc15, #f9a8d4, #c94ff0, #facc15);
  animation: blobDrift2 26s ease-in-out infinite alternate;
}
@keyframes blobDrift1{
  0%{transform:translate3d(0,0,0) scale(1)}
  50%{transform:translate3d(50px,40px,0) scale(1.08)}
  100%{transform:translate3d(20px,-10px,0) scale(1.02)}
}
@keyframes blobDrift2{
  0%{transform:translate3d(0,0,0) scale(1)}
  50%{transform:translate3d(-30px,-40px,0) scale(1.1)}
  100%{transform:translate3d(-10px,20px,0) scale(.96)}
}

/* ===== CONTAINER ===== */
.blog-wrap{
  max-width:1100px;
  margin:0 auto;
  padding:0 18px;
}

/* ===== LOADING / ERROR ===== */
.blog-status{
  text-align:center;
  padding:120px 20px;
  font-size:.98rem;
  color:var(--blog-muted);
}
.blog-status strong{color:var(--blog-ink)}
.blog-status .mini{margin-top:10px;font-size:.85rem;opacity:.9}

/* ===== TOP SECTION (ONLY 1st SECTION) ===== */
.blog-top{
  background: rgba(255,255,255,.72);
  border: 1px solid var(--blog-line);
  border-radius: 18px;
  box-shadow: 0 18px 50px rgba(24,10,40,.06);
  padding: 18px 18px;
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  margin-bottom: 16px;

  opacity:0;
  transform: translateY(14px);
  transition: opacity .55s ease, transform .55s ease;
}
.blog-ready .blog-top{opacity:1;transform:translateY(0)}

.blog-title{
  margin:0;
  font-size: 2.1rem;
  line-height: 1.15;
  font-weight: 900;
  color: var(--blog-primary);
  letter-spacing: .2px;
}

.blog-date{
  flex:0 0 auto;
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding: .48rem .75rem;
  border-radius: 999px;
  border:1px solid rgba(149,30,170,.18);
  background: #fff;
  color: var(--blog-ink);
  font-weight: 800;
  font-size: .88rem;
  box-shadow: 0 14px 40px rgba(24,10,40,.05);
  white-space: nowrap;
}
.blog-date i{color: var(--blog-primary)}
.blog-date.muted{
  opacity:.75;
  font-weight:700;
}

/* ===== HTML SECTION (ONLY 2nd SECTION) ===== */
.blog-body{
  background: rgba(255,255,255,.75);
  border: 1px solid var(--blog-line);
  border-radius: 18px;
  box-shadow: 0 18px 50px rgba(24,10,40,.06);
  padding: 18px 18px;

  opacity:0;
  transform: translateY(18px);
  transition: opacity .6s ease .08s, transform .6s ease .08s;
}
.blog-ready .blog-body{opacity:1;transform:translateY(0)}

/* safe basic html styles */
.blog-body :where(h1,h2,h3){color:var(--blog-ink);margin:16px 0 10px}
.blog-body :where(p,li){color:var(--blog-ink);line-height:1.8;font-size:1.02rem}
.blog-body :where(a){color:var(--blog-primary);font-weight:800;text-decoration:underline}
.blog-body :where(img){max-width:100%;border-radius:14px;border:1px solid rgba(149,30,170,.16)}
.blog-body :where(blockquote){
  margin:14px 0;
  padding:12px 14px;
  border-left:4px solid rgba(149,30,170,.55);
  background:rgba(149,30,170,.07);
  border-radius:12px;
}

/* responsive */
@media (max-width: 720px){
  .blog-top{flex-direction:column;align-items:flex-start}
  .blog-title{font-size:1.75rem}
  .blog-date{align-self:flex-start}
}
</style>

<section class="blog-page" id="blogPage">
  <div class="blog-blob blob-1"></div>
  <div class="blog-blob blob-2"></div>

  <div class="blog-wrap">
    {{-- LOADING --}}
    <div id="blogLoading" class="blog-status">
      Loading Blog...
      <div class="mini" id="blogHint"></div>
    </div>

    {{-- CONTENT (ONLY 2 SECTIONS) --}}
    <div id="blogContent" style="display:none;">
      <!-- SECTION 1: TOP (TITLE + DATE RIGHT) -->
      <div class="blog-top" id="blogTop">
        <h1 class="blog-title" id="blogTitle">Blog</h1>
        <div class="blog-date muted" id="blogDatePill" style="display:none;">
          <i class="fa-regular fa-calendar"></i>
          <span id="blogDateText"></span>
        </div>
      </div>

      <!-- SECTION 2: FULL HTML -->
      <div class="blog-body" id="blogHtmlWrap"></div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const page     = document.getElementById('blogPage');
  const loading  = document.getElementById('blogLoading');
  const hint     = document.getElementById('blogHint');
  const content  = document.getElementById('blogContent');

  const elTitle  = document.getElementById('blogTitle');
  const elHtml   = document.getElementById('blogHtmlWrap');

  const pillDate = document.getElementById('blogDatePill');
  const txtDate  = document.getElementById('blogDateText');

  // ---- detect identifier from URL
  // Supported:
  //   1) /blog/view/{identifier}
  //   2) /blog/{identifier}
  const pathname = window.location.pathname || '';
  const parts = pathname.split('/').filter(Boolean);

  function getIdentifierFromPath(parts){
    if (!parts || !parts.length) return '';
    const p0 = (parts[0] || '').toLowerCase();

    // /blog/view/{identifier}
    if (p0 === 'blog' && (parts[1] || '').toLowerCase() === 'view') {
      return parts.slice(2).join('/') || '';
    }

    // /blog/{identifier}
    if (p0 === 'blog') {
      return parts.slice(1).join('/') || '';
    }

    return parts[parts.length - 1] || '';
  }

  const identifier = getIdentifierFromPath(parts);

  // ---- mode from query ?mode=test or ?test=1
  const url = new URL(window.location.href);
  const mode = (url.searchParams.get('mode') || '').toLowerCase();
  const test = (url.searchParams.get('test') || '').toLowerCase();
  const isTest = (mode === 'test') || ['1','true','yes','y','on'].includes(test);

  // ✅ API route you provided:
  // Route::get('/blogs/view/{identifier}', [BlogController::class, 'publicView']);
  // so client calls:
  // /api/blogs/view/{identifier}
  const apiUrl = '/api/blogs/view/' + encodeURIComponent(identifier) + (isTest ? '?mode=test' : '');

  hint.textContent = identifier
    ? ('Identifier: ' + identifier + (isTest ? ' (mode=test)' : ''))
    : 'No identifier found in URL.';

  function fmtDate(d){
    try{
      const dt = new Date(d);
      if (isNaN(dt.getTime())) return '';
      return dt.toLocaleDateString(undefined, { year:'numeric', month:'long', day:'2-digit' });
    }catch(e){ return ''; }
  }

  function safeText(v){ return (v === null || v === undefined) ? '' : String(v); }

  function reveal(){
    content.style.display = 'block';
    page.classList.add('blog-ready');
  }

  if (!identifier) {
    loading.innerHTML = '<strong>Failed to load blog.</strong><div class="mini">Missing identifier in URL.</div>';
    return;
  }

  fetch(apiUrl, { headers: { 'Accept': 'application/json' } })
    .then(async (res) => {
      const json = await res.json().catch(()=>null);
      if (!res.ok) {
        const msg = (json && (json.error || json.message)) ? (json.error || json.message) : 'Failed to load blog';
        throw new Error(msg + ' (HTTP ' + res.status + ')');
      }
      return json;
    })
    .then((payload) => {
      const b = (payload && payload.data) ? payload.data : payload;

      loading.style.display = 'none';
      reveal();

      // Title
      elTitle.textContent = safeText(b.title) || 'Untitled Blog';

      // Date (top-right)
      const bd = safeText(b.blog_date);
      const bdFmt = fmtDate(bd);
      if (bdFmt) {
        pillDate.style.display = '';
        pillDate.classList.remove('muted');
        txtDate.textContent = bdFmt;
      } else {
        // if no date, just hide it (as you wanted minimal UI)
        pillDate.style.display = 'none';
      }

      // Full HTML (server already sanitizes)
      const html = safeText(b.content_html);
      if (html.trim() !== '') {
        elHtml.innerHTML = html;
      } else {
        elHtml.innerHTML = '<p style="color:var(--blog-muted);margin:0">No content available.</p>';
      }
    })
    .catch((err) => {
      loading.innerHTML =
        '<strong>Failed to load blog.</strong>' +
        '<div class="mini">' + (err && err.message ? err.message : 'Unknown error') + '</div>';
    });
});
</script>
