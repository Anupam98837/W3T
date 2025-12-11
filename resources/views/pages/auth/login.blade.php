{{-- resources/views/auth/login.blade.php (W3Techiez • uses your UI, adds required auth logic) --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login — W3Techiez</title>

  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <!-- Vendors -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  <!-- Global tokens (kept) -->
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}"/>

  <style>
    html, body { height:100%; }
    body.lx-auth-body{
      height:100%;
      overflow:hidden;
      background:var(--bg-body);
      color:var(--text-color);
      font-family:var(--font-sans);
    }

    .lx-grid{
      height:100vh;
      display:grid;
      grid-template-columns: minmax(420px,560px) 1fr;
    }
    @media (max-width: 992px){ .lx-grid{ grid-template-columns: 1fr; } }

    /* LEFT: form column */
    .lx-left{
      height:100vh;
      display:flex; flex-direction:column;
      justify-content:center; align-items:center;
      padding:clamp(22px,5vw,56px);
      position:relative; isolation:isolate;
    }
    .lx-left::before,
    .lx-left::after{
      content:""; position:absolute; z-index:0; pointer-events:none;
      border-radius:50%; filter: blur(26px); opacity:.25; display:none;
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
    @media (max-width: 992px){ .lx-left::before, .lx-left::after{ display:block; } }

    .lx-brand{ display:grid; place-items:center; margin-bottom:18px; position:relative; z-index:1; }
    .lx-brand img{ height:60px; }

    .lx-title{
      font-family:var(--font-head); font-weight:700; color:var(--ink);
      text-align:center; font-size:clamp(1.6rem, 2.6vw, 2.2rem); margin:.35rem 0 .25rem;
      position:relative; z-index:1;
    }
    .lx-sub{ text-align:center; color:var(--muted-color); margin-bottom:18px; position:relative; z-index:1; }

    .lx-card{
      position:relative; z-index:1;
      background:var(--surface); border:1px solid var(--line-strong);
      border-radius:18px; padding:24px; box-shadow:var(--shadow-2);
      width:100%; max-width:430px; overflow:hidden;
    }
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

    .lx-label{ font-weight:600; color:var(--ink); }
    .lx-input-wrap{ position:relative; }
    .lx-control{ height:46px; border-radius:12px; padding-right:48px; }
    .lx-control::placeholder{ color:#aab2c2; }
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

    /* RIGHT visuals (hidden on mobile) */
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
    .lx-hero{ position:relative; width:min(680px, 96%); aspect-ratio: 3/4; animation: lx-pop .7s ease-out both; }
    .lx-hero-frame{
      position:relative; width:100%; height:100%; padding:20px; border-radius:36px;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
      box-shadow: 0 24px 54px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.06) inset;
      transition: transform .25s ease, box-shadow .25s ease;
      will-change: transform;
    }
    .lx-hero-img{ width:100%; height:100%; border-radius:24px; overflow:hidden; position:relative; box-shadow:0 18px 40px rgba(0,0,0,.35); }
    .lx-hero-img img{
      width:100%; height:100%; object-fit:cover; display:block; transform:translateZ(0);
      animation: lx-zoom 26s ease-in-out infinite alternate; will-change: transform;
    }
    .lx-particles{
      position:absolute; inset:0; pointer-events:none; opacity:.28;
      background:
        radial-gradient(#ffffff 1px, transparent 2px) 0 0/22px 22px,
        radial-gradient(#ffffff 1px, transparent 2px) 11px 11px/22px 22px;
      mix-blend-mode: overlay; animation: lx-twinkle 12s linear infinite;
    }
    .lx-hero:hover .lx-hero-frame{
      transform:translateY(-4px);
      box-shadow: 0 30px 64px rgba(0,0,0,.42), 0 0 0 1px rgba(255,255,255,.10) inset, 0 0 0 8px rgba(149,30,170,.06);
    }
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

    <form class="lx-card" id="lx_form" action="/login" method="post" novalidate>
      <!-- <span class="lx-float-chip">Secure • Token Auth</span> -->
      @csrf

      <!-- Alerts -->
      <div id="lx_alert" class="alert d-none mb-3" role="alert"></div>

      <!-- Email (or phone UI label — API expects email) -->
      <div class="mb-3">
        <label class="lx-label form-label" for="lx_id_or_email">Email or Phone Number</label>
        <div class="lx-input-wrap">
          <input id="lx_id_or_email" type="text" class="lx-control form-control" name="identifier"
                 placeholder="you@example.com or 90000 00000" required>
        </div>
      </div>

      <!-- Password with eye toggle -->
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

      <button class="lx-login" id="lx_btn" type="submit">
        <span class="me-2"><i class="fa-solid fa-right-to-bracket"></i></span> Login
      </button>
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
          <div class="lx-particles" aria-hidden="true"></div>
        </div>
      </div>
    </div>
  </aside>
</div>

<script>
  (function(){
    // ---- CONFIG (uses your existing API contracts) ----
    const LOGIN_API = "/api/auth/login";
    const CHECK_API = "/api/auth/check";

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ---- DOM ----
    const form    = document.getElementById('lx_form');
    const emailIn = document.getElementById('lx_id_or_email');
    const pwIn    = document.getElementById('lx_pw');
    const keepCb  = document.getElementById('lx_keep');
    const btn     = document.getElementById('lx_btn');
    const alertEl = document.getElementById('lx_alert');
    const toggle  = document.getElementById('lx_togglePw');

    // ---- UI helpers ----
    function setBusy(b){
      btn.disabled = b;
      btn.innerHTML = b
        ? '<i class="fa-solid fa-spinner fa-spin me-2"></i>Signing you in…'
        : '<span class="me-2"><i class="fa-solid fa-right-to-bracket"></i></span> Login';
    }
    function showAlert(kind, msg){
      alertEl.classList.remove('d-none', 'alert-danger', 'alert-success', 'alert-warning');
      alertEl.classList.add('alert', kind === 'error' ? 'alert-danger' : (kind === 'warn' ? 'alert-warning' : 'alert-success'));
      alertEl.textContent = msg;
    }
    function clearAlert(){
      alertEl.classList.add('d-none');
      alertEl.textContent = '';
    }

    // ---- Storage helpers (keys EXACTLY "token" and "role") ----
    const authStore = {
      set(token, role, keep){
        // Always in session
        sessionStorage.setItem('token', token);
        sessionStorage.setItem('role', role);
        if (keep){
          // Keep me logged in: also in local
          localStorage.setItem('token', token);
          localStorage.setItem('role', role);
        } else {
          localStorage.removeItem('token');
          localStorage.removeItem('role');
        }
      },
      clear(){
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('role');
        localStorage.removeItem('token');
        localStorage.removeItem('role');
      },
      getSession(){
        return { token: sessionStorage.getItem('token'), role: sessionStorage.getItem('role') };
      },
      getLocal(){
        return { token: localStorage.getItem('token'), role: localStorage.getItem('role') };
      }
    };

    // ---- Build role dashboard path ----
    function rolePath(role){
      const r = (role || '').toString().trim().toLowerCase();
      if(!r) return '/dashboard';
      return `/${r}/dashboard`;
    }

    // ---- Password eye toggle ----
    toggle?.addEventListener('click', () => {
      const show = pwIn.type === 'password';
      pwIn.type = show ? 'text' : 'password';
      toggle.innerHTML = show
        ? '<i class="fa-regular fa-eye" aria-hidden="true"></i>'
        : '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>';
    });

    // ---- Auto-redirect if "Keep me logged in" token is present (verify first) ----
    async function tryAutoLoginFromLocal(){
      const { token, role } = authStore.getLocal();
      if(!token) return;

      try{
        const res = await fetch(CHECK_API, {
          headers: { 'Authorization': 'Bearer ' + token }
        });
        const data = await res.json().catch(() => ({}));
        if(res.ok && data && data.user){
          // sync role if API returns user.role (source of truth)
          const resolvedRole = (data.user.role || role || '').toString().toLowerCase();
          // Save to BOTH storages because user opted "keep me logged in" earlier
          authStore.set(token, resolvedRole, true);
          window.location.replace(rolePath(resolvedRole));
        } else {
          // invalid/expired -> show alert and clear
          authStore.clear();
          showAlert('error', data?.message || 'Your session expired. Please log in again.');
        }
      } catch(e){
        // network issue: do nothing (stay on page)
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      tryAutoLoginFromLocal();
    });

    // ---- Intercept submit -> call /api/auth/login ----
    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlert();

      const identifier = (emailIn.value || '').trim();
      const password   = pwIn.value || '';
      const keep       = !!keepCb.checked;

      if(!identifier || !password){
        showAlert('error','Please enter both email and password.');
        return;
      }

      setBusy(true);
      try{
        // NOTE: API expects { email, password, remember? }
        const res = await fetch(LOGIN_API, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({ email: identifier, password, remember: keep })
        });

        const data = await res.json().catch(() => ({}));

        if(!res.ok){
          const msg = data?.message || data?.error ||
                      (data?.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to log in.');
          showAlert('error', msg);
          setBusy(false);
          return;
        }

        // Expected: { access_token, token_type, expires_at?, user:{ role, ... } }
        const token = data?.access_token || data?.token || '';
        const role  = (data?.user?.role || localStorage.getItem('role') || 'student').toLowerCase();

        if(!token){
          showAlert('error', 'No token received from server.');
          setBusy(false);
          return;
        }

        // Save token+role
        authStore.set(token, role, keep);

        showAlert('success', 'Login successful. Redirecting…');
        setTimeout(() => {
          window.location.assign(rolePath(role));
        }, 500);

      } catch(err){
        showAlert('error','Network error. Please try again.');
      } finally {
        setBusy(false);
      }
    });

    // ---- Parallax (desktop only) ----
    (function(){
      const stage  = document.getElementById('lx_visual');
      const hero   = document.getElementById('lx_hero');
      const frame  = document.querySelector('.lx-hero-frame');
      const img    = document.querySelector('.lx-hero-img img');
      if (!stage || !frame || !img || !hero) return;

      const mq = window.matchMedia('(max-width: 992px)');
      let targetTX = 0, targetTY = 0, targetRX = 0, targetRY = 0;
      let currTX = 0, currTY = 0, currRX = 0, currRY = 0;
      let rafId = null;

      const MAX_T = 18, MAX_RX = 6, MAX_RY = 8, LERP = 0.12;

      function onMove(e){
        const rect = stage.getBoundingClientRect();
        const cx = rect.left + rect.width/2;
        const cy = rect.top  + rect.height/2;
        const dx = (e.clientX - cx) / (rect.width/2);
        const dy = (e.clientY - cy) / (rect.height/2);
        const ndx = Math.max(-1, Math.min(1, dx));
        const ndy = Math.max(-1, Math.min(1, dy));

        targetTX = ndx * MAX_T;
        targetTY = ndy * MAX_T;
        targetRY = ndx * MAX_RY;
        targetRX = -ndy * MAX_RX;

        if (!hero.classList.contains('is-tracking')){
          hero.classList.add('is-tracking');
          tick();
        }
      }
      function onLeave(){ targetTX = targetTY = targetRX = targetRY = 0; }
      function tick(){
        currTX += (targetTX - currTX) * LERP;
        currTY += (targetTY - currTY) * LERP;
        currRX += (targetRX - currRX) * LERP;
        currRY += (targetRY - currRY) * LERP;

        frame.style.transform =
          `translate3d(${currTX.toFixed(2)}px, ${currTY.toFixed(2)}px, 0)
           rotateX(${currRX.toFixed(2)}deg)
           rotateY(${currRY.toFixed(2)}deg)`;

        const ix = (-currTX * 0.6).toFixed(2);
        const iy = (-currTY * 0.6).toFixed(2);
        img.style.transform = `translate3d(${ix}px, ${iy}px, 0) scale(1.05)`;

        const nearZero =
          Math.abs(currTX) < 0.15 && Math.abs(currTY) < 0.15 &&
          Math.abs(currRX) < 0.08 && Math.abs(currRY) < 0.08 &&
          Math.abs(targetTX) < 0.15 && Math.abs(targetTY) < 0.15 &&
          Math.abs(targetRX) < 0.08 && Math.abs(targetRY) < 0.08;

        if (!nearZero){
          rafId = requestAnimationFrame(tick);
        } else {
          frame.style.transform = 'translate3d(0,0,0) rotateX(0) rotateY(0)';
          img.style.transform = 'translate3d(0,0,0) scale(1)';
          hero.classList.remove('is-tracking');
          rafId && cancelAnimationFrame(rafId);
          rafId = null;
        }
      }
      function attach(){ if (mq.matches) return; stage.addEventListener('mousemove', onMove); stage.addEventListener('mouseleave', onLeave); }
      function detach(){ stage.removeEventListener('mousemove', onMove); stage.removeEventListener('mouseleave', onLeave); onLeave(); }

      attach();
      mq.addEventListener('change', () => { detach(); attach(); });
      window.addEventListener('blur', onLeave);
    })();
  })();
</script>
</body>
</html>
