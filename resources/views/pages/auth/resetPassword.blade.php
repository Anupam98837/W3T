{{-- resources/views/auth/reset-password.blade.php (W3Techiez • exact same UI as login + reset logic) --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Reset Password — W3Techiez</title>

  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/web/favicon.png') }}">

  <!-- Vendors -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  <!-- Global tokens -->
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

    .lx-login{
      width:100%; height:48px; border:none; border-radius:12px; font-weight:700; color:#fff;
      background:linear-gradient(180deg, color-mix(in oklab, var(--primary-color) 92%, #fff 8%), var(--primary-color));
      transition:var(--transition);
    }
    .lx-login:hover{ filter:brightness(.98); transform:translateY(-1px); }

    .lx-secondary{
      width:100%; height:46px; border-radius:12px;
      border:1px solid var(--line-strong);
      background:transparent;
      font-weight:700;
    }

    /* captcha */
    .lx-captcha{
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      border:1px dashed var(--line-strong);
      border-radius:14px;
      padding:10px 12px;
      background:rgba(0,0,0,.02);
    }
    .lx-captcha-code{
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-weight:800;
      letter-spacing:3px;
      padding:8px 12px;
      border-radius:12px;
      border:1px solid var(--line-strong);
      background:rgba(255,255,255,.55);
      user-select:none;
      min-width:140px;
      text-align:center;
    }
    .lx-captcha .btn{
      border-radius:12px;
      border:1px solid var(--line-strong);
    }

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

    /* Animations */
    @keyframes lx-pop{ from{opacity:0; transform:translateY(10px) scale(.98);} to{opacity:1; transform:none;} }
    @keyframes lx-zoom{ from{transform:scale(1);} to{transform:scale(1.06);} }
    @keyframes lx-drift{ 0%,100%{transform:translate3d(0,0,0);} 50%{transform:translate3d(-2%,2%,0);} }
    @keyframes lx-spin{ 0%{ transform:rotate(0deg);} 100%{ transform:rotate(360deg);} }
    @keyframes lx-twinkle{ 0%{opacity:.22;} 50%{opacity:.34;} 100%{opacity:.22;} }

    /* Hide browser-native password reveal buttons */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear { display:none !important; }
    input[type="password"]::-webkit-textfield-decoration-container,
    input[type="password"]::-webkit-password-toggle-button,
    input[type="password"]::-webkit-credentials-auto-fill-button { display:none !important; }
    input[type="password"]::-webkit-textfield-decoration-container { opacity:0 !important; }
  </style>
</head>

<body class="lx-auth-body">
<div class="lx-grid">
  <!-- LEFT -->
  <section class="lx-left">
    <div class="lx-brand">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
    </div>

    <h1 class="lx-title">Create a new password</h1>
    <p class="lx-sub">Use the reset token from OTP verification to set a new password</p>

    <form class="lx-card" id="rp_form" action="javascript:void(0)" method="post" novalidate>
      @csrf

      <div id="rp_alert" class="alert d-none mb-3" role="alert"></div>

      <!-- Email -->
      <div class="mb-3">
        <label class="lx-label form-label" for="rp_email">Email address</label>
        <div class="lx-input-wrap">
          <input id="rp_email" type="email" class="lx-control form-control"
                 placeholder="you@example.com" autocomplete="email" required>
        </div>
      </div>

      <!-- New Password -->
      <div class="mb-3">
        <label class="lx-label form-label" for="rp_pw">New password</label>
        <div class="lx-input-wrap">
          <input id="rp_pw" type="password" class="lx-control form-control"
                 placeholder="Enter at least 8+ characters" minlength="8" required>
          <button type="button" class="lx-eye" id="rp_togglePw" aria-label="Toggle password visibility">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <!-- Confirm Password -->
      <div class="mb-3">
        <label class="lx-label form-label" for="rp_pw2">Confirm new password</label>
        <div class="lx-input-wrap">
          <input id="rp_pw2" type="password" class="lx-control form-control"
                 placeholder="Re-enter password" minlength="8" required>
          <button type="button" class="lx-eye" id="rp_togglePw2" aria-label="Toggle password visibility">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <!-- Captcha -->
      <div class="mb-3">
        <label class="lx-label form-label" for="rp_captcha_in">Captcha</label>

        <div class="lx-captcha mb-2">
          <div class="lx-captcha-code" id="rp_captcha_code">------</div>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="rp_captcha_refresh">
            <i class="fa-solid fa-rotate"></i>
          </button>
        </div>

        <div class="lx-input-wrap">
          <input id="rp_captcha_in" type="text" class="lx-control form-control"
                 placeholder="Type the captcha shown above" autocomplete="off" required>
        </div>

        <div class="small text-muted mt-2">
          This captcha is front-end only (basic bot protection). For production, use reCAPTCHA/hCaptcha.
        </div>
      </div>

      <button class="lx-login" id="rp_btn" type="submit">
        <span class="me-2"><i class="fa-solid fa-lock"></i></span> Reset Password
      </button>

      <button class="lx-secondary mt-2" type="button" id="rp_back">
        <i class="fa-solid fa-arrow-left me-2"></i> Back to Login
      </button>
    </form>
  </section>

  <!-- RIGHT -->
  
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
    const RESET_API   = "/api/auth/forgot-password/reset";
    const LOGIN_PAGE  = "/login";

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const form = document.getElementById('rp_form');
    const emailIn = document.getElementById('rp_email');
    const pwIn = document.getElementById('rp_pw');
    const pw2In = document.getElementById('rp_pw2');
    const capCodeEl = document.getElementById('rp_captcha_code');
    const capIn = document.getElementById('rp_captcha_in');
    const capRefresh = document.getElementById('rp_captcha_refresh');

    const btn = document.getElementById('rp_btn');
    const alertEl = document.getElementById('rp_alert');
    const backBtn = document.getElementById('rp_back');

    const t1 = document.getElementById('rp_togglePw');
    const t2 = document.getElementById('rp_togglePw2');

    function setBusy(b){
      btn.disabled = b;
      btn.innerHTML = b
        ? '<i class="fa-solid fa-spinner fa-spin me-2"></i>Resetting…'
        : '<span class="me-2"><i class="fa-solid fa-lock"></i></span> Reset Password';
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

    // Password eye toggles
    t1?.addEventListener('click', () => {
      const show = pwIn.type === 'password';
      pwIn.type = show ? 'text' : 'password';
      t1.innerHTML = show ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
    });
    t2?.addEventListener('click', () => {
      const show = pw2In.type === 'password';
      pw2In.type = show ? 'text' : 'password';
      t2.innerHTML = show ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
    });

    backBtn?.addEventListener('click', () => window.location.href = LOGIN_PAGE);

    // Basic captcha
    function genCaptcha(){
      const alphabet = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789"; // no confusing chars
      let out = "";
      for(let i=0;i<6;i++) out += alphabet[Math.floor(Math.random()*alphabet.length)];
      sessionStorage.setItem('rp_captcha', out);
      capCodeEl.textContent = out;
    }
    capRefresh?.addEventListener('click', genCaptcha);

    // Prefill email from sessionStorage (from forgot-password flow)
    document.addEventListener('DOMContentLoaded', () => {
      genCaptcha();
      const savedEmail = sessionStorage.getItem('fp_email');
      if (savedEmail) emailIn.value = savedEmail;

      // Must have reset_token from verify-otp
      const tok = sessionStorage.getItem('fp_reset_token');
      if (!tok){
        showAlert('warn','Reset token not found. Please verify OTP again.');
      }
    });

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlert();

      const email = (emailIn.value || '').trim().toLowerCase();
      const p1 = pwIn.value || '';
      const p2 = pw2In.value || '';
      const cap = (capIn.value || '').trim().toUpperCase();

      const expectedCap = (sessionStorage.getItem('rp_captcha') || '').toUpperCase();
      const resetToken = sessionStorage.getItem('fp_reset_token') || '';

      if(!email){ showAlert('error','Please enter your email.'); return; }
      if(!p1 || p1.length < 8){ showAlert('error','Password must be at least 8 characters.'); return; }
      if(p1 !== p2){ showAlert('error','Password and confirm password do not match.'); return; }

      if(!cap){ showAlert('error','Please enter captcha.'); return; }
      if(!expectedCap || cap !== expectedCap){
        showAlert('error','Captcha does not match. Please try again.');
        genCaptcha();
        capIn.value = '';
        capIn.focus();
        return;
      }

      if(!resetToken){
        showAlert('error','Reset token missing. Please verify OTP again.');
        return;
      }

      setBusy(true);
      try{
        const res = await fetch(RESET_API, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            email,
            reset_token: resetToken,
            password: p1,
            password_confirmation: p2
          })
        });

        const data = await res.json().catch(() => ({}));

        if(!res.ok){
          const msg = data?.message || data?.error ||
                      (data?.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to reset password.');
          showAlert('error', msg);
          genCaptcha();
          capIn.value = '';
          return;
        }

        // Clear reset flow keys
        sessionStorage.removeItem('fp_reset_token');
        sessionStorage.removeItem('fp_otp');
        sessionStorage.removeItem('fp_request_id');
        sessionStorage.removeItem('fp_expires_in_minutes');

        showAlert('success', data?.message || 'Password reset successful. Redirecting to login…');

        setTimeout(() => {
          window.location.assign(LOGIN_PAGE);
        }, 700);

      } catch(err){
        showAlert('error','Network error. Please try again.');
      } finally {
        setBusy(false);
      }
    });

    // Parallax (same as login)
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
