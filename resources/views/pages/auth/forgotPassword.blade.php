{{-- resources/views/auth/forgot-password.blade.php (W3Techiez • matches login UI + Verify + redirect) --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Forgot Password — W3Techiez</title>

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
    .lx-control{ height:46px; border-radius:12px; }

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

    /* OTP tiny UI */
    .fp-chip{
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      padding:10px 12px; border-radius:12px;
      border:1px dashed var(--line-strong);
      background:rgba(0,0,0,.02);
      color:var(--muted-color);
      margin-top:10px;
    }
    .fp-chip b{ color:var(--ink); }

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

    @keyframes lx-pop{ from{opacity:0; transform:translateY(10px) scale(.98);} to{opacity:1; transform:none;} }
    @keyframes lx-zoom{ from{transform:scale(1);} to{transform:scale(1.06);} }
    @keyframes lx-drift{ 0%,100%{transform:translate3d(0,0,0);} 50%{transform:translate3d(-2%,2%,0);} }
    @keyframes lx-spin{ 0%{ transform:rotate(0deg);} 100%{ transform:rotate(360deg);} }
    @keyframes lx-twinkle{ 0%{opacity:.22;} 50%{opacity:.34;} 100%{opacity:.22;} }
  </style>
</head>
<body class="lx-auth-body">

<div class="lx-grid">
  <!-- LEFT -->
  <section class="lx-left">
    <div class="lx-brand">
      <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
    </div>

    <h1 class="lx-title">Reset your password</h1>
    <p class="lx-sub">Enter your email → generate OTP → verify OTP.</p>

    <form class="lx-card" id="fp_form" action="javascript:void(0)" method="post" novalidate>
      @csrf

      <div id="fp_alert" class="alert d-none mb-3" role="alert"></div>

      <!-- Email -->
      <div class="mb-3">
        <label class="lx-label form-label" for="fp_email">Email address</label>
        <div class="lx-input-wrap">
          <input id="fp_email" type="email" class="lx-control form-control"
                 placeholder="you@example.com" autocomplete="email" required>
        </div>
      </div>

      <!-- OTP (hidden until generated) -->
      <div class="mb-3 d-none" id="fp_otp_wrap">
        <label class="lx-label form-label" for="fp_otp">OTP</label>
        <div class="lx-input-wrap">
          <input id="fp_otp" type="text" class="lx-control form-control"
                 placeholder="Enter 6-digit OTP"
                 inputmode="numeric" pattern="[0-9]*" maxlength="6"
                 autocomplete="one-time-code" disabled>
        </div>

        <div class="fp-chip">
          <div class="small">
            <b>Tip:</b> In dev, OTP may auto-fill. Otherwise check server logs.
          </div>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="fp_resend" style="border-radius:10px;">
            <i class="fa-solid fa-rotate me-1"></i> Resend
          </button>
        </div>

        <div class="small text-muted mt-2" id="fp_otp_help">
          OTP will appear here after generation.
        </div>
      </div>

      <!-- Primary button changes by step -->
      <button class="lx-login" id="fp_btn" type="submit">
        <span class="me-2"><i class="fa-solid fa-key"></i></span> Generate OTP
      </button>

      <button class="lx-secondary mt-2" type="button" id="fp_back">
        <i class="fa-solid fa-arrow-left me-2"></i> Back to Login
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
    const SEND_OTP_API   = "/api/auth/forgot-password/send-otp";
    const VERIFY_OTP_API = "/api/auth/forgot-password/verify-otp";
    const RESET_PAGE     = "/reset-password";

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const form    = document.getElementById('fp_form');
    const emailIn = document.getElementById('fp_email');
    const otpWrap = document.getElementById('fp_otp_wrap');
    const otpIn   = document.getElementById('fp_otp');
    const otpHelp = document.getElementById('fp_otp_help');

    const btn     = document.getElementById('fp_btn');
    const alertEl = document.getElementById('fp_alert');
    const backBtn = document.getElementById('fp_back');
    const resendBtn = document.getElementById('fp_resend');

    // step: "send" -> "verify"
    let step = "send";

    function setBusy(b, labelHtml){
      btn.disabled = b;
      if (labelHtml) btn.innerHTML = labelHtml;
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

    function setStepSend(){
      step = "send";
      otpWrap.classList.add('d-none');
      otpIn.disabled = true;
      otpIn.value = '';
      otpHelp.textContent = 'OTP will appear here after generation.';
      btn.innerHTML = '<span class="me-2"><i class="fa-solid fa-key"></i></span> Generate OTP';
    }

    function setStepVerify(){
      step = "verify";
      otpWrap.classList.remove('d-none');
      otpIn.disabled = false;
      btn.innerHTML = '<span class="me-2"><i class="fa-solid fa-shield-check"></i></span> Verify OTP';
      otpHelp.textContent = 'Enter the OTP and verify to continue.';
    }

    backBtn?.addEventListener('click', () => window.location.href = '/login');

    // resend = just call send again
    resendBtn?.addEventListener('click', async () => {
      clearAlert();
      await doSendOtp(true);
    });

    // allow Enter key in OTP box to verify
    otpIn?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && step === 'verify'){
        e.preventDefault();
        form?.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
      }
    });

    async function doSendOtp(isResend=false){
      const email = (emailIn.value || '').trim();
      if(!email){
        showAlert('error','Please enter your email address.');
        return;
      }

      setBusy(true, '<i class="fa-solid fa-spinner fa-spin me-2"></i>' + (isResend ? 'Resending…' : 'Generating…'));
      try{
        const res = await fetch(SEND_OTP_API, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ email })
        });

        const data = await res.json().catch(() => ({}));

        if(!res.ok){
          const msg = data?.message || data?.error ||
                      (data?.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to generate OTP.');
          showAlert('error', msg);
          return;
        }

        // move to verify step
        setStepVerify();

        // store email for next page
        sessionStorage.setItem('fp_email', email);

        // store request_id/expires if server sends
        const reqId = data?.data?.request_id || '';
        const expMin = data?.data?.expires_in_minutes ?? null;
        if (reqId) sessionStorage.setItem('fp_request_id', String(reqId));
        if (expMin !== null) sessionStorage.setItem('fp_expires_in_minutes', String(expMin));

        // DEV OTP: if present, console.log + autofill
        const otpFromApi = data?.data?.otp ?? data?.otp ?? null;
        if (otpFromApi) {
          console.log("OTP:", otpFromApi);
          otpIn.value = String(otpFromApi);
          sessionStorage.setItem('fp_otp', String(otpFromApi));
          otpHelp.textContent = 'OTP auto-filled (dev). Now click Verify OTP.';
        } else {
          otpHelp.textContent = 'OTP generated. Check server logs (or later email) and type OTP here.';
        }

        otpIn.focus();
        showAlert('success', data?.message || 'If the account exists, an OTP has been generated.');

      } catch(err){
        showAlert('error','Network error. Please try again.');
      } finally {
        setBusy(false, '<span class="me-2"><i class="fa-solid ' + (step === 'verify' ? 'fa-shield-check' : 'fa-key') + '"></i></span> ' + (step === 'verify' ? 'Verify OTP' : 'Generate OTP'));
      }
    }

    async function doVerifyOtp(){
      const email = (emailIn.value || '').trim();
      const otp   = (otpIn.value || '').trim();

      if(!email){
        showAlert('error','Please enter your email address.');
        return;
      }
      if(!otp || otp.length !== 6){
        showAlert('error','Please enter a valid 6-digit OTP.');
        return;
      }

      setBusy(true, '<i class="fa-solid fa-spinner fa-spin me-2"></i>Verifying…');
      try{
        const res = await fetch(VERIFY_OTP_API, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ email, otp })
        });

        const data = await res.json().catch(() => ({}));

        if(!res.ok){
          const msg = data?.message || data?.error ||
                      (data?.errors ? Object.values(data.errors).flat().join(', ') : 'Invalid OTP.');
          showAlert('error', msg);
          return;
        }

        // Expect: data.reset_token
        const resetToken = data?.data?.reset_token || data?.reset_token || '';
        if(!resetToken){
          showAlert('error','Reset token not received from server.');
          return;
        }

        // Save for reset-password page
        sessionStorage.setItem('fp_email', email);
        sessionStorage.setItem('fp_reset_token', resetToken);

        showAlert('success', data?.message || 'OTP verified. Redirecting…');

        setTimeout(() => {
          window.location.assign(RESET_PAGE);
        }, 450);

      } catch(err){
        showAlert('error','Network error. Please try again.');
      } finally {
        setBusy(false, '<span class="me-2"><i class="fa-solid fa-shield-check"></i></span> Verify OTP');
      }
    }

    // main submit handler: send or verify depending on step
    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlert();

      if (step === "send") {
        await doSendOtp(false);
      } else {
        await doVerifyOtp();
      }
    });

    // optional: if user already has email stored and came back
    document.addEventListener('DOMContentLoaded', () => {
      const savedEmail = sessionStorage.getItem('fp_email');
      if (savedEmail) emailIn.value = savedEmail;
    });
  })();
</script>

</body>
</html>
