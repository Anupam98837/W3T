{{-- resources/views/global/about-us.blade.php --}}
@section('title','About Us')
 
<style>
:root{
  --about-bg: radial-gradient(circle at top left, rgba(149,30,170,.14), transparent 60%),
              radial-gradient(circle at bottom right, rgba(201,79,240,.12), transparent 55%),
              var(--surface, #f9f5ff);
  --about-ink: var(--ink, #1c1324);
  --about-muted: var(--muted-color, #6b7280);
  --about-accent: #c94ff0;
  --about-primary: #951eaa;
}
 
/* ===== SECTION SHELL ===== */
.about-section {
  position: relative;
  margin-top:0;   
  margin-bottom:120px;
  padding: 80px 0;
  background: var(--about-bg);
  overflow: hidden;
  isolation:isolate;
}
 
/* soft vignette */
.about-section::before{
  content:"";
  position:absolute;
  inset:-40%;
  background:
    radial-gradient(circle at 15% 10%, rgba(255,255,255,.8), transparent 55%),
    radial-gradient(circle at 80% 90%, rgba(255,255,255,.5), transparent 55%);
  mix-blend-mode: soft-light;
  pointer-events:none;
  z-index:-1;
}
 
/* floating blobs */
.about-blob{
  position:absolute;
  width:160px;
  height:160px;
  border-radius:50%;
  opacity:.22;
  pointer-events:none;
  mix-blend-mode:screen;
  z-index:0;
}
.about-blob.blob-1{
  top:5%;
  left:-40px;
  background:conic-gradient(from 220deg, #951eaa, #c94ff0, #f472b6, #951eaa);
  animation: blobDrift1 22s ease-in-out infinite alternate;
}
.about-blob.blob-2{
  bottom:-60px;
  right:-40px;
  background:conic-gradient(from 140deg, #facc15, #f9a8d4, #c94ff0, #facc15);
  animation: blobDrift2 26s ease-in-out infinite alternate;
}
 
/* blob animations */
@keyframes blobDrift1{
  0%{ transform:translate3d(0,0,0) scale(1); }
  50%{ transform:translate3d(50px,40px,0) scale(1.08); }
  100%{ transform:translate3d(20px,-10px,0) scale(1.02); }
}
@keyframes blobDrift2{
  0%{ transform:translate3d(0,0,0) scale(1); }
  50%{ transform:translate3d(-30px,-40px,0) scale(1.1); }
  100%{ transform:translate3d(-10px,20px,0) scale(.96); }
}
 
/* ===== LAYOUT ===== */
.about-container {
  max-width: 1200px;
  margin: auto;
  display: grid;
  grid-template-columns: 1.05fr 1fr;
  align-items: center;
  gap: 70px;
  padding: 0 24px;
  z-index:1;
}
 
/* LEFT SIDE */
.about-left{
  opacity:0;
  transform:translateY(24px);
  transition: opacity .7s ease-out .05s, transform .7s ease-out .05s;
}
 
.about-kicker{
  display:inline-flex;
  align-items:center;
  gap:.45rem;
  padding:.18rem .75rem .18rem .35rem;
  border-radius:999px;
  border:1px solid rgba(149,30,170,.22);
  background:white;
  font-size:.78rem;
  font-weight:600;
  letter-spacing:.08em;
  text-transform:uppercase;
  color:var(--about-primary);
  box-shadow:0 14px 40px rgba(24,10,40,.05);
  margin-bottom:14px;
}
 
.about-kicker-dot{
  width:9px;
  height:9px;
  border-radius:50%;
  background:#22c55e;
  box-shadow:0 0 0 6px rgba(34,197,94,.25);
}
 
.about-title{
  font-size:3rem;
  font-weight:800;
  line-height:1.15;
  color:var(--about-primary);
  margin-bottom:14px;
}
 
.about-text{
  font-size:1.05rem;
  line-height:1.7;
  color:var(--about-muted);
  max-width:520px;
}
 
/* CTA LINK CARDS */
.about-links{
  display:flex;
  flex-wrap:wrap;
  gap:14px;
  margin-top:26px;
}
 
.about-link-card{
  background:white;
  border:1px solid rgba(149,30,170,.2);
  border-radius:14px;
  padding:12px 18px;
  display:flex;
  align-items:center;
  gap:12px;
  min-width:200px;
  text-decoration:none;
  box-shadow:0 14px 40px rgba(24,10,40,.05);
  transition:.25s ease;
}
 
.about-link-card:hover{
  transform:translateY(-4px);
  border-color:var(--about-primary);
  box-shadow:0 24px 60px rgba(24,10,40,.12);
}
 
.about-link-icon{
  width:38px;
  height:38px;
  border-radius:50%;
  background:rgba(149,30,170,.1);
  color:var(--about-primary);
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:1.1rem;
}
 
.about-link-text-title{
  font-size:.95rem;
  font-weight:700;
  color:var(--about-ink);
}
 
.about-link-text-sub{
  font-size:.75rem;
  color:#6b7280;
}
 
/* RIGHT SIDE */
.about-right{
  position:relative;
  opacity:0;
  transform:translateY(26px);
  transition: opacity .7s ease-out .17s, transform .7s ease-out .17s;
}
 
.image-shell{
  position:relative;
  width:100%;
  max-width:440px;
  background:white;
  padding:14px;
  border-radius:22px;
  border:1px solid rgba(149,30,170,.25);
  box-shadow:0 30px 80px rgba(24,10,40,.1);
}
 
/* IMAGE */
.image-wrapper{
  overflow:hidden;
  border-radius:16px;
}
.image-wrapper img{
  width:100%;
  transform:scale(1.02);
  transition:6s ease-out;
}
 
/* RING */
.ring-bg{
  position:absolute;
  width:420px;
  height:420px;
  border-radius:50%;
  border:30px solid rgba(149,30,170,.35);
  top:52%;
  left:54%;
  transform:translate(-50%,-50%);
  filter:blur(0px);
  animation: ringFloat 7s infinite ease-in-out, ringSpin 28s linear infinite;
}
@keyframes ringFloat{
  0%{ transform:translate(-50%,-50%) translateY(0); }
  50%{ transform:translate(-50%,-50%) translateY(-14px); }
  100%{ transform:translate(-50%,-50%) translateY(0); }
}
@keyframes ringSpin{
  0%{ transform:translate(-50%,-50%) rotate(0deg); }
  100%{ transform:translate(-50%,-50%) rotate(360deg); }
}
 
/* STRUCTURAL ICON SHAPES */
.about-struct-stack{
  position:absolute;
  left:-70px;
  top:50%;
  transform:translateY(-50%);
  display:flex;
  flex-direction:column;
  gap:14px;
}
 
.about-struct-item{
  width:60px;
  height:60px;
  border-radius:16px;
  background:white;
  border:1px solid rgba(149,30,170,.3);
  box-shadow:0 18px 40px rgba(24,10,40,.15);
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--about-primary);
  font-size:1.3rem;
  animation: structFloat 6s infinite ease-in-out alternate;
}
@keyframes structFloat{
  0%{ transform:translateY(-6px); }
  100%{ transform:translateY(6px); }
}
 
/* LOADING STATE */
.about-status{
  text-align:center;
  padding:120px 20px;
  font-size:.98rem;
  color:var(--about-muted);
}
 
/* WHEN READY */
.about-section.about-ready .about-left,
.about-section.about-ready .about-right{
  opacity:1;
  transform:translateY(0);
}
.about-section.about-ready .image-wrapper img{
  transform:scale(1.06);
}
</style>
 
<section class="about-section">
 
  <div class="about-blob blob-1"></div>
  <div class="about-blob blob-2"></div>
 
  {{-- LOADING --}}
  <div id="aboutLoading" class="about-status">
      Loading About Us...
  </div>
 
  {{-- CONTENT --}}
  <div id="aboutContent" style="display:none;">
    <div class="about-container">
 
      <!-- LEFT -->
      <div class="about-left">
 
        <div class="about-kicker">
          <span class="about-kicker-dot"></span>
          <span>About W3Techiez Academy</span>
        </div>
 
        <h1 class="about-title" id="aboutTitle"></h1>
 
        <p class="about-text" id="aboutText"></p>
 
        <!-- REPLACED STATS WITH CTA LINKS -->
        <div class="about-links">
 
          <a href="/courses/all" class="about-link-card">
            <div class="about-link-icon"><i class="fa-solid fa-play-circle"></i></div>
            <div>
              <div class="about-link-text-title">View Courses</div>
              <div class="about-link-text-sub">Explore our complete course library</div>
            </div>
          </a>
 
          <a href="/categories/all" class="about-link-card">
            <div class="about-link-icon"><i class="fa-solid fa-layer-group"></i></div>
            <div>
              <div class="about-link-text-title">View Categories</div>
              <div class="about-link-text-sub">Browse topics & learning paths</div>
            </div>
          </a>
 
          <a href="/updates/all" class="about-link-card">
            <div class="about-link-icon"><i class="fa-solid fa-bell"></i></div>
            <div>
              <div class="about-link-text-title">View Updates</div>
              <div class="about-link-text-sub">Stay informed about new releases</div>
            </div>
          </a>
 
        </div>
      </div>
 
      <!-- RIGHT -->
      <div class="about-right">
 
        <div class="ring-bg"></div>
 
        <div class="image-shell">
 
          <div class="about-struct-stack">
            <div class="about-struct-item"><i class="fa-solid fa-sitemap"></i></div>
            <div class="about-struct-item"><i class="fa-solid fa-diagram-project"></i></div>
            <div class="about-struct-item"><i class="fa-solid fa-graduation-cap"></i></div>
          </div>
 
          <div class="image-wrapper">
            <img id="aboutImage" src="" alt="">
          </div>
        </div>
      </div>
 
    </div>
  </div>
</section>
 
<script>
document.addEventListener('DOMContentLoaded', () => {
 
  const section = document.querySelector('.about-section');
  const loading = document.getElementById('aboutLoading');
  const content = document.getElementById('aboutContent');
  const img = document.getElementById('aboutImage');
 
  function revealOnScroll() {
    const rect = section.getBoundingClientRect();
    if (rect.top < window.innerHeight - 80) {
      section.classList.add('about-ready');
      window.removeEventListener('scroll', revealOnScroll);
    }
  }
 
  fetch('/api/about-us')
    .then(r => r.json())
    .then(data => {
      loading.style.display = 'none';
 
      if (!data.success || !data.about) return;
 
      const a = data.about;
 
      document.getElementById('aboutTitle').innerHTML = a.title || "We build career-ready tech talent.";
      document.getElementById('aboutText').innerHTML = a.content || "";
 
      if (a.image) img.src = a.image + '?v=' + Date.now();
 
      content.style.display = 'block';
 
      revealOnScroll();
      window.addEventListener('scroll', revealOnScroll);
    })
    .catch(() => {
      loading.innerText = "Failed to load content.";
    });
 
});
</script>
 