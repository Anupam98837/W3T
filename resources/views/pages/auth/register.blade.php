<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Register — W3Techiez</title>

  <!-- Vendors -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  <!-- Your global tokens (kept) -->
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}"/>

  <style>

html, body { 
  height: 100%;
  overflow: hidden; /* Prevent overall page scroll */
}

body.lx-auth-body {
  /* height: 100vh; */
  overflow: hidden;
}

.lx-grid { 
  /* height: 100vh; */
  display: grid;
  grid-template-columns: minmax(420px, 560px) 1fr;
}

@media (max-width: 992px) { 
  .lx-grid { 
    grid-template-columns: 1fr;
    /* height: 100vh; */
    overflow: hidden;
  }
}

.lx-left {
  /* height: 100vh; */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: clamp(22px, 5vw, 56px);
  position: relative;
  isolation: isolate;
  overflow: hidden; /* Prevent scrolling in left section */
}

.lx-card {
  position: relative;
  z-index: 1;
  background: var(--surface);
  border: 1px solid var(--line-strong);
  border-radius: 18px;
  padding: 24px;
  box-shadow: var(--shadow-2);
  width: 100%;
  max-width: 430px;
  overflow: visible; /* Change from auto to visible */
  /* max-height: 70vh; Limit height to viewport */
  display: flex;
  flex-direction: column;
}

/* Adjust margins and padding to fit better */
.lx-title {
  font-family: var(--font-head);
  font-weight: 700;
  color: var(--ink);
  text-align: center;
  font-size: clamp(1.6rem, 2.6vw, 2.2rem);
  margin: .2rem 0 .15rem; /* Reduced margin */
  position: relative;
  z-index: 1;
  line-height: 1.2;
}

.lx-sub {
  text-align: center;
  color: var(--muted-color);
  margin-bottom: 12px; /* Reduced from 18px */
  position: relative;
  z-index: 1;
  font-size: 0.95rem;
  line-height: 1.3;
}

.lx-brand {
  display: grid;
  place-items: center;
  margin-bottom: 12px; /* Reduced from 18px */
  position: relative;
  z-index: 1;
}

.lx-brand img {
  height: 50px; /* Reduced from 60px */
}

/* Adjust form spacing */
.mb-3 {
  margin-bottom: 0.75rem !important; /* Reduced from 1rem */
}

.mb-2 {
  margin-bottom: 0.5rem !important; /* Reduced from 0.5rem */
}

.lx-row {
  margin-top: 0.5rem; /* Added space above row */
  margin-bottom: 1rem; /* Added space below row */
}

/* Adjust button positioning */
.lx-login {
  margin-top: 0.5rem; /* Reduced space above button */
}

/* For very small screens, adjust further */
@media (max-width: 768px) {
  .lx-left {
    padding: 20px;
    justify-content: flex-start; /* Start from top on mobile */
    padding-top: 40px; /* Add top padding */
  }
  
  .lx-card {
    max-height: calc(100vh - 100px); /* More space on mobile */
    padding: 20px;
  }
  
  .lx-brand {
    margin-bottom: 8px;
  }
  
  .lx-title {
    margin: 0.1rem 0 0.1rem;
    font-size: 1.4rem;
  }
  
  .lx-sub {
    margin-bottom: 8px;
    font-size: 0.9rem;
  }
  
  .mb-3 {
    margin-bottom: 0.6rem !important;
  }
}

/* For very short screens */
@media (max-height: 700px) {
  .lx-left {
    padding: 16px;
  }
  
  .lx-card {
    padding: 18px;
    /* max-height: 90vh; Use more of the viewport */
  }
  
  .lx-brand img {
    height: 40px;
  }
  
  .lx-title {
    font-size: 1.3rem;
    margin: 0.1rem 0;
  }
  
  .lx-sub {
    margin-bottom: 8px;
    font-size: 0.85rem;
  }
  
  .lx-control {
    height: 42px; /* Slightly smaller inputs */
  }
  
  .mb-3 {
    margin-bottom: 0.5rem !important;
  }
}

/* Remove the previous media query that was causing issues */
@media (max-height: 600px), (max-width: 420px) {
  /* This was removed to prevent excessive shrinking */
}

/* Ensure form elements don't overflow */
.lx-input-wrap {
  margin-bottom: 0.25rem;
}

/* Make sure the error message doesn't push content */
#lx_pw_error {
  margin-top: 0.25rem;
  margin-bottom: 0.5rem;
  min-height: 20px; /* Reserve space for error */
}

/* Adjust the row with terms checkbox */
.lx-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px; /* Reduced gap */
  flex-wrap: wrap; /* Allow wrapping on small screens */
  margin-top: 0.75rem;
  margin-bottom: 0.75rem;
}
  .lx-input-wrap{ position:relative; }
    .lx-control{ height:46px; border-radius:12px; padding-right:48px; }
    .lx-control::placeholder{ color:#aab2c2; }
    .lx-eye{ position:absolute; top:50%; right:10px; transform:translateY(-50%); width:36px; height:36px; border:none; background:transparent; color:#8892a6; display:grid; place-items:center; cursor:pointer; border-radius:8px; }
    .lx-eye:focus-visible{ outline:none; box-shadow: var(--ring); }
    .lx-login{ width:100%; height:48px; border:none; border-radius:12px; font-weight:700; color:#fff; background:linear-gradient(180deg, color-mix(in oklab, var(--primary-color) 92%, #fff 8%), var(--primary-color)); box-shadow:0 10px 22px rgba(149,30,170,.22); transition:var(--transition); }
    .lx-login:hover{ filter:brightness(.98); transform:translateY(-1px); }
    .lx-right{ position:relative; height:100vh; display:grid; place-items:center; background: radial-gradient(120% 100% at 10% 10%, rgba(149,30,170,.12) 0%, rgba(7,13,42,0) 55%), linear-gradient(180deg,#070d2a,#081337); padding: clamp(24px, 4vw, 60px); isolation:isolate; overflow:hidden; }
    @media (max-width: 992px){ .lx-right{ display:none; } }
    .lx-arc{ position:absolute; inset: -18% -10% auto auto; width:120%; height:140%; background:radial-gradient(110% 110% at 80% 20%, rgba(201,79,240,.18) 0%, rgba(149,30,170,.12) 35%, rgba(7,13,42,0) 62%); border-bottom-left-radius:48% 44%; pointer-events:none; animation: lx-drift 16s ease-in-out infinite; }
    .lx-ring{ position:absolute; inset:auto -120px -80px auto; width:420px; height:420px; border-radius:50%; background: radial-gradient(closest-side, rgba(255,255,255,.14), rgba(255,255,255,0) 70%), conic-gradient(from 0deg, rgba(149,30,170,.25), rgba(0,210,196,.25), rgba(149,30,170,.25)); filter:blur(18px); opacity:.18; pointer-events:none; animation: lx-spin 24s linear infinite; }
    .lx-hero{ position:relative; width:min(680px, 96%); aspect-ratio: 3/4; animation: lx-pop .7s ease-out both; }
    .lx-hero-frame{ position:relative; width:100%; height:100%; padding:20px; border-radius:36px; background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02)); box-shadow: 0 24px 54px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.06) inset; transition: transform .25s ease, box-shadow .25s ease; will-change: transform; }
    .lx-hero-img{ width:100%; height:100%; border-radius:24px; overflow:hidden; position:relative; box-shadow:0 18px 40px rgba(0,0,0,.35); }
    .lx-hero-img img{ width:100%; height:100%; object-fit:cover; display:block; transform:translateZ(0); animation: lx-zoom 26s ease-in-out infinite alternate; will-change: transform; }
    .lx-particles{ position:absolute; inset:0; pointer-events:none; opacity:.28; background: radial-gradient(#ffffff 1px, transparent 2px) 0 0/22px 22px, radial-gradient(#ffffff 1px, transparent 2px) 11px 11px/22px 22px; mix-blend-mode: overlay; animation: lx-twinkle 12s linear infinite; }
    .lx-hero:hover .lx-hero-frame{ transform:translateY(-4px); box-shadow: 0 30px 64px rgba(0,0,0,.42), 0 0 0 1px rgba(255,255,255,.10) inset, 0 0 0 8px rgba(149,30,170,.06); }
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
  <!-- LEFT: REGISTER FORM -->
  <section class="lx-left">
    <div class="lx-brand">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
    </div>

    <h1 class="lx-title">Create your account</h1>
    <p class="lx-sub">Sign up to start learning — it's free and quick.</p>

    <form class="lx-card" action="/login" method="post" novalidate id="lx_registerForm">
      @csrf
      <input type="hidden" name="role" value="student">

      <div class="mb-3">
        <label class="lx-label form-label" for="lx_name">Full name</label>
        <div class="lx-input-wrap">
          <input id="lx_name" name="name" type="text" class="lx-control form-control" placeholder="Your full name" required maxlength="150">
        </div>
      </div>

      <div class="mb-3">
        <label class="lx-label form-label" for="lx_email">Email address</label>
        <div class="lx-input-wrap">
          <input id="lx_email" name="email" type="email" class="lx-control form-control" placeholder="you@example.com" required maxlength="255">
        </div>
      </div>

      <div class="mb-3" >
        <label class="lx-label form-label" for="lx_phone">Phone number</label>
        <div class="lx-input-wrap">
          <input id="lx_phone" name="phone_number" type="tel" class="lx-control form-control" placeholder="90000 00000" required maxlength="32">
        </div>
      </div>

      <div class="mb-2">
        <label class="lx-label form-label" for="lx_pw">Password</label>
        <div class="lx-input-wrap">
          <input id="lx_pw" name="password" type="password" class="lx-control form-control" placeholder="Enter at least 8+ characters" minlength="8" required>
          <button type="button" class="lx-eye" id="lx_togglePw" aria-label="Toggle password visibility">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <div class="mb-3">
        <label class="lx-label form-label" for="lx_pw2">Confirm password</label>
        <div class="lx-input-wrap">
          <input id="lx_pw2" name="password_confirmation" type="password" class="lx-control form-control" placeholder="Re-enter password" minlength="8" required>
          <button type="button" class="lx-eye" id="lx_togglePw2" aria-label="Toggle confirm password visibility">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <div class="lx-error" id="lx_pw_error" style="display:none;">Passwords do not match.</div>

      <div class="lx-row mb-3">
        <div class="form-check m-0">
          <input class="form-check-input" type="checkbox" id="lx_terms" required>
          <label class="form-check-label" for="lx_terms">I agree to the <a href="/terms" class="text-decoration-none">terms</a></label>
        </div>
        <a class="text-decoration-none" href="/login">Already have an account?</a>
      </div>

      <button class="lx-login" type="submit" id="lx_submitBtn">Register</button>
    </form>
  </section>

  <!-- RIGHT: VISUAL (same as login) -->
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function () {
  const form = document.getElementById('lx_registerForm');
  if (!form) return;

  const submitBtn = document.getElementById('lx_submitBtn');
  const pw = document.getElementById('lx_pw');
  const pw2 = document.getElementById('lx_pw2');
  const errBox = document.getElementById('lx_pw_error');

  function clearFieldErrors() {
    document.querySelectorAll('.lx-field-error').forEach(n => n.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  }
  function showFieldErrors(errors = {}) {
    clearFieldErrors();
    Object.entries(errors).forEach(([field, messages]) => {
      const el = form.querySelector(`[name="${field}"]`);
      if (!el) return;
      el.classList.add('is-invalid');
      const msg = document.createElement('div');
      msg.className = 'invalid-feedback lx-field-error';
      msg.textContent = Array.isArray(messages) ? messages.join(' ') : String(messages);
      const wrap = el.closest('.lx-input-wrap') || el.parentNode;
      wrap.appendChild(msg);
    });
  }

  function showGeneralError(title, text) {
    Swal.fire({ icon: 'error', title: title || 'Error', text: text || 'Something went wrong' });
  }

  // primary submit handler (shows Swal when passwords don't match)
form.addEventListener('submit', async function (e) {
  e.preventDefault();
  clearFieldErrors();
  errBox.style.display = 'none';

  // client-side password match — show SweetAlert2 if mismatch
  if (pw.value !== pw2.value) {
    await Swal.fire({
      icon: 'error',
      title: 'Passwords do not match',
      text: 'Please make sure both password fields match.',
      confirmButtonText: 'Ok'
    });
    pw2.focus();
    return;
  }

  const payload = {
    name: (form.querySelector('[name="name"]') || {}).value || '',
    email: (form.querySelector('[name="email"]') || {}).value || '',
    phone_number: (form.querySelector('[name="phone_number"]') || {}).value || '',
    password: pw.value,
    password_confirmation: pw2.value,
    role: (form.querySelector('[name="role"]') || {}).value || 'student',
  };

  submitBtn.disabled = true;
  submitBtn.textContent = 'Registering...';

  const endpoints = ['/api/auth/register', '/auth/register'];
  let response = null;
  let lastErr = null;

  for (let url of endpoints) {
    try {
      response = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      });
      if (response) break;
    } catch (err) {
      lastErr = err;
      response = null;
    }
  }

  submitBtn.disabled = false;
  submitBtn.textContent = 'Register';

  if (!response) {
    console.error('Registration failed (no response)', lastErr);
    return showGeneralError('Network error', 'Could not connect to the server. Please try again.');
  }

  let data;
  try { data = await response.json(); } catch (err) { data = null; }

  if (response.status === 201 || (data && data.status === 'success')) {
    const token = data && data.access_token ? data.access_token : null;
    const userRole = (data && data.user && data.user.role) ? data.user.role : 'student';

    if (token) {
      try { localStorage.setItem('w3t_token', token); } catch(e){}
      try { sessionStorage.setItem('w3t_token', token); } catch(e){}
    }
    try { sessionStorage.setItem('w3t_role', userRole); } catch(e){}
    try { localStorage.setItem('w3t_role', userRole); } catch(e){}

    await Swal.fire({
      icon: 'success',
      title: 'Account created',
      text: (data && data.message) ? data.message : 'Registration successful',
      confirmButtonText: 'Continue to dashboard'
    });

    window.location.href = '/student/dashboard';
    return;
  }

  if (response.status === 422 && data && (data.errors || data.message)) {
    const errs = data.errors || {};
    showFieldErrors(errs);
    if (data.message && typeof data.message === 'string') {
      Swal.fire({ icon:'error', title: 'Could not register', text: data.message });
    }
    return;
  }

  if (data && data.message) {
    showGeneralError('Registration failed', data.message);
    return;
  }

  showGeneralError('Registration failed', 'Please check your details and try again.');
});

  // hide inline errors as user types
  form.addEventListener('input', () => { clearFieldErrors(); errBox.style.display = 'none'; });

  // Password toggles
  (function(){
    const t1 = document.getElementById('lx_togglePw');
    const t2 = document.getElementById('lx_togglePw2');
    const p1 = document.getElementById('lx_pw');
    const p2 = document.getElementById('lx_pw2');
    if (t1 && p1) t1.addEventListener('click', () => {
      const show = p1.type === 'password'; p1.type = show ? 'text' : 'password';
      t1.innerHTML = show ? '<i class="fa-regular fa-eye" aria-hidden="true"></i>' : '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>';
    });
    if (t2 && p2) t2.addEventListener('click', () => {
      const show = p2.type === 'password'; p2.type = show ? 'text' : 'password';
      t2.innerHTML = show ? '<i class="fa-regular fa-eye" aria-hidden="true"></i>' : '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>';
    });
  })();

  // Cursor-tracking parallax (desktop)
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
    const MAX_T = 18, MAX_RX = 6, MAX_RY = 8, LERP  = 0.12;
    function onMove(e){
      const rect = stage.getBoundingClientRect();
      const cx = rect.left + rect.width/2;
      const cy = rect.top  + rect.height/2;
      const dx = (e.clientX - cx) / (rect.width/2);
      const dy = (e.clientY - cy) / (rect.height/2);
      const ndx = Math.max(-1, Math.min(1, dx));
      const ndy = Math.max(-1, Math.min(1, dy));
      targetTX = ndx * MAX_T; targetTY = ndy * MAX_T; targetRY = ndx * MAX_RY; targetRX = -ndy * MAX_RX;
      if (!hero.classList.contains('is-tracking')){ hero.classList.add('is-tracking'); tick(); }
    }
    function onLeave(){ targetTX = targetTY = targetRX = targetRY = 0; }
    function tick(){
      currTX += (targetTX - currTX) * LERP; currTY += (targetTY - currTY) * LERP; currRX += (targetRX - currRX) * LERP; currRY += (targetRY - currRY) * LERP;
      frame.style.transform = `translate3d(${currTX.toFixed(2)}px, ${currTY.toFixed(2)}px, 0) rotateX(${currRX.toFixed(2)}deg) rotateY(${currRY.toFixed(2)}deg)`;
      const ix = (-currTX * 0.6).toFixed(2); const iy = (-currTY * 0.6).toFixed(2);
      img.style.transform = `translate3d(${ix}px, ${iy}px, 0) scale(1.05)`;
      const nearZero = Math.abs(currTX) < 0.15 && Math.abs(currTY) < 0.15 && Math.abs(currRX) < 0.08 && Math.abs(currRY) < 0.08 && Math.abs(targetTX) < 0.15 && Math.abs(targetTY) < 0.15 && Math.abs(targetRX) < 0.08 && Math.abs(targetRY) < 0.08;
      if (!nearZero){ rafId = requestAnimationFrame(tick); } else { frame.style.transform = 'translate3d(0,0,0) rotateX(0) rotateY(0)'; img.style.transform = 'translate3d(0,0,0) scale(1)'; hero.classList.remove('is-tracking'); rafId && cancelAnimationFrame(rafId); rafId = null; }
    }
    function attach(){ if (mq.matches) return; stage.addEventListener('mousemove', onMove); stage.addEventListener('mouseleave', onLeave); }
    function detach(){ stage.removeEventListener('mousemove', onMove); stage.removeEventListener('mouseleave', onLeave); onLeave(); }
    attach(); mq.addEventListener('change', () => { detach(); attach(); }); window.addEventListener('blur', onLeave);
  })();

})();
</script>

</body>
</html>
