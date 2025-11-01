<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login — W3Techiez</title>

  <!-- Vendors -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  <!-- Your global tokens (kept) -->
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}"/>

  <style>
    /* =========================
       Namespaced Login (lx-*)
       ========================= */

    html, body { height:100%; }
    body.lx-auth-body{
      height:100%;
      overflow:hidden;               /* No page scroll */
      background:var(--bg-body);
      color:var(--text-color);
      font-family:var(--font-sans);
    }

    /* Grid: left form + right visual */
    .lx-grid{
      height:100vh;
      display:grid;
      grid-template-columns: minmax(420px,560px) 1fr;
    }
    @media (max-width: 992px){
      .lx-grid{ grid-template-columns: 1fr; }
    }

    /* LEFT: form column (centered) */
    .lx-left{
      height:100vh;
      display:flex; flex-direction:column;
      justify-content:center; align-items:center;
      padding:clamp(22px,5vw,56px);
      position:relative; isolation:isolate;
    }

    /* Mobile-only animated blobs behind form */
    .lx-left::before,
    .lx-left::after{
      content:""; position:absolute; z-index:0; pointer-events:none;
      border-radius:50%; filter: blur(26px); opacity:.25;
      display:none;
    }
    .lx-left::before{
      width:320px; height:320px; left:-80px; top:10%;
      background: radial-gradient(closest-side, #ffc857, transparent 70%);
      animation: lx-floatA 9s ease-in-out infinite;
    }
    .lx-left::after{
      width:280px; height:280px; right:-60px; bottom:14%;
      background: radial-gradient(closest-side, #9d4edd, transparent 70%);
      animation: lx-floatB 11s ease-in-out infinite;
    }
    @media (max-width: 992px){
      .lx-left::before, .lx-left::after{ display:block; }
    }

    .lx-brand{ display:grid; place-items:center; margin-bottom:18px; position:relative; z-index:1; }
    .lx-brand img{ height:60px; }

    .lx-title{
      font-family:var(--font-head); font-weight:700; color:var(--ink);
      text-align:center; font-size:clamp(1.6rem, 2.6vw, 2.2rem); margin:.35rem 0 .25rem;
      position:relative; z-index:1;
    }
    .lx-sub{ text-align:center; color:var(--muted-color); margin-bottom:18px; position:relative; z-index:1; }

    /* Card with floating shapes inside */
    .lx-card{
      position:relative; z-index:1;
      background:var(--surface); border:1px solid var(--line-strong);
      border-radius:18px; padding:24px; box-shadow:var(--shadow-2);
      width:100%; max-width:430px; overflow:hidden;
    }
    /* Soft in-card floaters */
    .lx-card::before,
    .lx-card::after{
      content:""; position:absolute; border-radius:50%;
      filter: blur(18px); opacity:.25; pointer-events:none;
    }
    .lx-card::before{
      width:160px; height:160px; left:-40px; top:-40px;
      background: radial-gradient(closest-side, var(--accent-color), transparent 65%);
      animation: lx-orbitA 12s linear infinite;
    }
    .lx-card::after{
      width:140px; height:140px; right:-30px; bottom:-30px;
      background: radial-gradient(closest-side, var(--primary-color), transparent 65%);
      animation: lx-orbitB 14s linear infinite reverse;
    }
    .lx-float-chip{
      position:absolute; top:12px; right:12px; z-index:1;
      padding:6px 10px; border-radius:999px; font-size:.78rem;
      background:rgba(0,0,0,.04); color:var(--text-color);
      border:1px solid var(--line-strong);
      backdrop-filter: blur(4px);
      animation: lx-chip 7s ease-in-out infinite;
    }

    /* Fields */
    .lx-label{ font-weight:600; color:var(--ink); }
    .lx-input-wrap{ position:relative; } /* anchor for eye */

    .lx-control{
      height:46px; border-radius:12px; padding-right:48px; /* space for eye */
    }
    .lx-control::placeholder{ color:#aab2c2; }

    /* Perfectly centered eye toggle relative to the input */
    .lx-eye{
      position:absolute; top:50%; right:10px; transform:translateY(-50%);
      width:36px; height:36px; border:none; background:transparent; color:#8892a6;
      display:grid; place-items:center; cursor:pointer; border-radius:8px;
    }
    .lx-eye:focus-visible{ outline:none; box-shadow: var(--ring); }

    .lx-row{ display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .lx-login{
      width:100%; height:48px; border:none; border-radius:12px; font-weight:700; color:#fff;
      background:linear-gradient(180deg, color-mix(in oklab, var(--primary-color) 92%, #fff 8%), var(--primary-color));
      box-shadow:0 10px 22px rgba(149,30,170,.22); transition:var(--transition);
    }
    .lx-login:hover{ filter:brightness(.98); transform:translateY(-1px); }

    /* RIGHT: visual panel (hidden on mobile) */
    .lx-right{
      position:relative; height:100vh; display:grid; place-items:center;
      background: radial-gradient(120% 100% at 10% 10%, rgba(149,30,170,.12) 0%, rgba(7,13,42,0) 55%),
                  linear-gradient(180deg,#070d2a,#081337);
      padding: clamp(24px, 4vw, 60px);
      isolation:isolate; overflow:hidden;
    }
    @media (max-width: 992px){ .lx-right{ display:none; } }

    .lx-arc{
      position:absolute; inset: -18% -10% auto auto;
      width:120%; height:140%;
      background:radial-gradient(110% 110% at 80% 20%, rgba(201,79,240,.18) 0%, rgba(149,30,170,.12) 35%, rgba(7,13,42,0) 62%);
      border-bottom-left-radius:48% 44%;
      pointer-events:none; animation: lx-drift 16s ease-in-out infinite;
    }
    .lx-ring{
      position:absolute; inset:auto -120px -80px auto; width:420px; height:420px; border-radius:50%;
      background:
        radial-gradient(closest-side, rgba(255,255,255,.14), rgba(255,255,255,0) 70%),
        conic-gradient(from 0deg, rgba(149,30,170,.25), rgba(0,210,196,.25), rgba(149,30,170,.25));
      filter:blur(18px); opacity:.18; pointer-events:none; animation: lx-spin 24s linear infinite;
    }

    /* Framed hero */
    .lx-hero{ position:relative; width:min(680px, 96%); aspect-ratio: 3/4; animation: lx-pop .7s ease-out both; }
    .lx-hero-frame{
      position:relative; width:100%; height:100%; padding:20px; border-radius:36px;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
      box-shadow: 0 24px 54px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.06) inset;
      transition: transform .25s ease, box-shadow .25s ease;
      will-change: transform;
    }
    .lx-hero-img{
      width:100%; height:100%; border-radius:24px; overflow:hidden; position:relative;
      box-shadow:0 18px 40px rgba(0,0,0,.35);
    }
    .lx-hero-img img{
      width:100%; height:100%; object-fit:cover; display:block; transform:translateZ(0);
      animation: lx-zoom 26s ease-in-out infinite alternate;
      will-change: transform;
    }

    /* Tiny dotted grid overlay over image */
    .lx-particles{
      position:absolute; inset:0; pointer-events:none; opacity:.28;
      background:
        radial-gradient(#ffffff 1px, transparent 2px) 0 0/22px 22px,
        radial-gradient(#ffffff 1px, transparent 2px) 11px 11px/22px 22px;
      mix-blend-mode: overlay;
      animation: lx-twinkle 12s linear infinite;
    }

    .lx-hero:hover .lx-hero-frame{
      transform:translateY(-4px);
      box-shadow: 0 30px 64px rgba(0,0,0,.42), 0 0 0 1px rgba(255,255,255,.10) inset, 0 0 0 8px rgba(149,30,170,.06);
    }

    /* Decorative “study desk” objects */
    .lx-obj{ position:absolute; z-index:3; opacity:.9; filter: drop-shadow(0 8px 18px rgba(0,0,0,.28)); user-select:none; pointer-events:none; }
    .lx-books{ top: clamp(18px, 3vw, 36px); left: clamp(12px, 2vw, 28px); display:grid; gap:6px; }
    .lx-book{ width:110px; height:22px; border-radius:5px; background:linear-gradient(90deg, #9d4edd, #7b2cbf); transform:rotate(-6deg); }
    .lx-book:nth-child(2){ width:124px; height:24px; background:linear-gradient(90deg, #00d3c4, #00a99c); transform:rotate(-4deg) translateX(8px); }
    .lx-book:nth-child(3){ width:132px; height:24px; background:linear-gradient(90deg, #ffb199, #ff8a74); transform:rotate(-2deg) translateX(14px); }

    .lx-cup{ right: clamp(16px, 3vw, 36px); bottom: clamp(18px, 3vw, 36px); width:90px; height:110px; }
    .lx-cup-body{ position:absolute; left:0; bottom:0; width:90px; height:64px; border-radius:12px 12px 18px 18px; background:linear-gradient(180deg, #1b2a55, #0f1a3a); border:1px solid rgba(255,255,255,.10); }
    .lx-pencil{ position:absolute; bottom:40px; width:10px; height:78px; border-radius:6px; background:linear-gradient(180deg, #ffc857, #f7a400); box-shadow:inset 0 0 0 1px rgba(0,0,0,.08); transform-origin:bottom center; animation: lx-sway 5s ease-in-out infinite; }
    .lx-pencil:nth-child(2){ left:24px; transform:rotate(-8deg); background:linear-gradient(180deg, #00d3c4, #00a99c); animation-delay:.6s; }
    .lx-pencil:nth-child(3){ left:46px; transform:rotate(6deg); background:linear-gradient(180deg, #e56b6f, #cf4446); animation-delay:1.2s; }
    .lx-pencil:nth-child(4){ left:64px; transform:rotate(-2deg); background:linear-gradient(180deg, #9d4edd, #7b2cbf); animation-delay:1.8s; }

    /* Pause zoom while tracking and add slight depth */
    .lx-hero.is-tracking .lx-hero-img img{
      animation:none;
      transform:scale(1.05);
    }

    /* Dark-mode flips */
    html.theme-dark .lx-right{ background:linear-gradient(180deg,#050a22,#071235); }

    /* Animations */
    @keyframes lx-pop{ from{opacity:0; transform:translateY(10px) scale(.98);} to{opacity:1; transform:none;} }
    @keyframes lx-zoom{ from{transform:scale(1);} to{transform:scale(1.06);} }
    @keyframes lx-drift{ 0%,100%{transform:translate3d(0,0,0);} 50%{transform:translate3d(-2%,2%,0);} }
    @keyframes lx-spin{ 0%{ transform:rotate(0deg);} 100%{ transform:rotate(360deg);} }
    @keyframes lx-sway{ 0%,100%{ transform:rotate(0deg);} 50%{ transform:rotate(4deg);} }
    @keyframes lx-floatA{ 0%,100%{ transform:translate(0,0);} 50%{ transform:translate(10px, -14px);} }
    @keyframes lx-floatB{ 0%,100%{ transform:translate(0,0);} 50%{ transform:translate(-12px, 10px);} }
    @keyframes lx-orbitA{ 0%{transform:translate(0,0);} 50%{transform:translate(6px, -6px);} 100%{transform:translate(0,0);} }
    @keyframes lx-orbitB{ 0%{transform:translate(0,0);} 50%{transform:translate(-6px, 6px);} 100%{transform:translate(0,0);} }
    @keyframes lx-chip{ 0%,100%{ transform:translateY(0);} 50%{ transform:translateY(-6px);} }
    @keyframes lx-twinkle{ 0%{opacity:.22;} 50%{opacity:.34;} 100%{opacity:.22;} }
  </style>
</head>
<body class="lx-auth-body">

<div class="lx-grid">
  <!-- LEFT: LOGIN FORM -->
  <section class="lx-left">
    <div class="lx-brand">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
    </div>

    <h1 class="lx-title">Join with us now</h1>
    <p class="lx-sub">Enter your credentials to access your account</p>

    <form class="lx-card" action="/login" method="post" novalidate>
      @csrf

      <!-- Email or Phone -->
      <div class="mb-3">
        <label class="lx-label form-label" for="lx_id_or_email">Email or Phone Number</label>
        <div class="lx-input-wrap">
          <input id="lx_id_or_email" type="text" class="lx-control form-control" name="identifier"
                 placeholder="you@example.com or 90000 00000" required>
        </div>
      </div>

      <!-- Password with perfectly centered eye toggle -->
      <div class="mb-2">
        <label class="lx-label form-label" for="lx_pw">Password</label>
        <div class="lx-input-wrap">
          <input id="lx_pw" type="password" class="lx-control form-control" name="password"
                 placeholder="Enter at least 8+ characters" minlength="8" required>
          <button type="button" class="lx-eye" id="lx_togglePw" aria-label="Toggle password visibility">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <div class="lx-row mb-3">
        <div class="form-check m-0">
          <input class="form-check-input" type="checkbox" id="lx_keep">
          <label class="form-check-label" for="lx_keep">Keep me logged in</label>
        </div>
        <a class="text-decoration-none" href="/forgot-password">Forgot password?</a>
      </div>

      <button class="lx-login" type="submit">Login</button>
    </form>
  </section>

  <!-- RIGHT: VISUAL (hidden on mobile) -->
  <aside class="lx-right" id="lx_visual">
    <span class="lx-arc" aria-hidden="true"></span>
    <span class="lx-ring" aria-hidden="true"></span>

    <div class="lx-obj lx-books" aria-hidden="true">
      <div class="lx-book"></div>
      <div class="lx-book"></div>
      <div class="lx-book"></div>
    </div>

    <div class="lx-obj lx-cup" aria-hidden="true">
      <div class="lx-cup-body"></div>
      <div class="lx-pencil" style="left:8px;"></div>
      <div class="lx-pencil"></div>
      <div class="lx-pencil"></div>
      <div class="lx-pencil"></div>
    </div>

    <div class="lx-hero" id="lx_hero">
      <div class="lx-hero-frame">
        <div class="lx-hero-img">
          <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1600&auto=format&fit=crop"
               alt="College students working on laptops">
          <!-- Tiny dotted grid overlay -->
          <div class="lx-particles" aria-hidden="true"></div>
        </div>
      </div>
    </div>
  </aside>
</div>

<script>
  // Password toggle (icon anchored to input wrapper)
  (function(){
    const pw = document.getElementById('lx_pw');
    const toggle = document.getElementById('lx_togglePw');
    if (pw && toggle){
      toggle.addEventListener('click', () => {
        const show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        toggle.innerHTML = show
          ? '<i class="fa-regular fa-eye" aria-hidden="true"></i>'
          : '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>';
      });
    }
  })();

  // Cursor-tracking parallax (desktop only) — translate + tilt with smoothing
  (function(){
    const stage  = document.getElementById('lx_visual');          // the right panel
    const hero   = document.getElementById('lx_hero');            // wrapper (adds is-tracking)
    const frame  = document.querySelector('.lx-hero-frame');      // outer frame we move/tilt
    const img    = document.querySelector('.lx-hero-img img');    // inner image (counter-shift)
    if (!stage || !frame || !img || !hero) return;

    const mq = window.matchMedia('(max-width: 992px)');
    let targetTX = 0, targetTY = 0, targetRX = 0, targetRY = 0;   // targets
    let currTX = 0, currTY = 0, currRX = 0, currRY = 0;           // smoothed
    let rafId = null;

    // tuning knobs
    const MAX_T = 18;    // max translate px
    const MAX_RX = 6;    // max rotateX deg
    const MAX_RY = 8;    // max rotateY deg
    const LERP  = 0.12;  // smoothing (0..1)

    function onMove(e){
      const rect = stage.getBoundingClientRect();
      const cx = rect.left + rect.width/2;
      const cy = rect.top  + rect.height/2;
      // normalized -1..1
      const dx = (e.clientX - cx) / (rect.width/2);
      const dy = (e.clientY - cy) / (rect.height/2);
      // clamp
      const ndx = Math.max(-1, Math.min(1, dx));
      const ndy = Math.max(-1, Math.min(1, dy));

      targetTX = ndx * MAX_T;         // translateX
      targetTY = ndy * MAX_T;         // translateY
      targetRY = ndx * MAX_RY;        // rotateY follows X movement
      targetRX = -ndy * MAX_RX;       // rotateX inverses Y movement

      if (!hero.classList.contains('is-tracking')){
        hero.classList.add('is-tracking'); // also pauses img zoom (via CSS)
        tick();
      }
    }

    function onLeave(){
      // spring back to rest
      targetTX = targetTY = targetRX = targetRY = 0;
    }

    function tick(){
      // lerp towards target
      currTX += (targetTX - currTX) * LERP;
      currTY += (targetTY - currTY) * LERP;
      currRX += (targetRX - currRX) * LERP;
      currRY += (targetRY - currRY) * LERP;

      // apply transforms
      frame.style.transform =
        `translate3d(${currTX.toFixed(2)}px, ${currTY.toFixed(2)}px, 0)
         rotateX(${currRX.toFixed(2)}deg)
         rotateY(${currRY.toFixed(2)}deg)`;

      // counter-shift image for depth (move opposite & a bit less)
      const ix = (-currTX * 0.6).toFixed(2);
      const iy = (-currTY * 0.6).toFixed(2);
      img.style.transform = `translate3d(${ix}px, ${iy}px, 0) scale(1.05)`;

      // continue until close to rest and no target offset
      const nearZero =
        Math.abs(currTX) < 0.15 && Math.abs(currTY) < 0.15 &&
        Math.abs(currRX) < 0.08 && Math.abs(currRY) < 0.08 &&
        Math.abs(targetTX) < 0.15 && Math.abs(targetTY) < 0.15 &&
        Math.abs(targetRX) < 0.08 && Math.abs(targetRY) < 0.08;

      if (!nearZero){
        rafId = requestAnimationFrame(tick);
      } else {
        // settle & cleanup
        frame.style.transform = 'translate3d(0,0,0) rotateX(0) rotateY(0)';
        img.style.transform = 'translate3d(0,0,0) scale(1)';
        hero.classList.remove('is-tracking');
        rafId && cancelAnimationFrame(rafId);
        rafId = null;
      }
    }

    function attach(){
      if (mq.matches) return; // desktop only
      stage.addEventListener('mousemove', onMove);
      stage.addEventListener('mouseleave', onLeave);
    }
    function detach(){
      stage.removeEventListener('mousemove', onMove);
      stage.removeEventListener('mouseleave', onLeave);
      onLeave();
    }

    attach();
    mq.addEventListener('change', () => { detach(); attach(); });
    window.addEventListener('blur', onLeave);
  })();
</script>
</body>
</html>
