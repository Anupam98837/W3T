{{-- resources/views/auth/forgot-password.blade.php (W3Techiez • matches login UI + reset LINK flow) --}}
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
      overflow-x:hidden;
      overflow-y:visible;
      background:var(--bg-body);
      color:var(--text-color);
      font-family:var(--font-sans);
    }

    .lx-grid{
      min-height:100vh;
      display:grid;
      grid-template-columns: minmax(420px,560px) 1fr;
    }
    @media (max-width: 992px){ .lx-grid{ grid-template-columns: 1fr; } }

    /* LEFT: form column */
    .lx-left{
      top:-50px;
      min-height:100vh;
      display:flex; flex-direction:column;
      justify-content:center; align-items:center;
      padding:clamp(22px,5vw,56px);
      position:relative; isolation:isolate;
      overflow-x:hidden;
      overflow-y:visible;
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
    .lx-login:disabled{ opacity:.68; transform:none; cursor:not-allowed; }

    .lx-secondary{
      width:100%; height:46px; border-radius:12px;
      border:1px solid var(--line-strong);
      background:transparent;
      font-weight:700;
    }

    /* Link sent chip UI (re-using your OTP chip style) */
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
      position:relative; min-height:100vh; display:grid; place-items:center;
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
    <p class="lx-sub">Enter your email and we’ll send you a reset link.</p>

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

      <!-- Link sent UI (hidden until sent) -->
      <div class="mb-3 d-none" id="fp_link_wrap">
        <div class="fp-chip">
          <div class="small">
            <b>Check your inbox:</b> reset link sent (valid for 10 minutes).
          </div>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="fp_resend" style="border-radius:10px;">
            <i class="fa-solid fa-rotate me-1"></i> Resend
          </button>
        </div>

        <div class="small text-muted mt-2" id="fp_link_help">
          If you don’t receive it, check spam/junk and try resend.
        </div>
      </div>

      <!-- Primary -->
      <button class="lx-login" id="fp_btn" type="submit">
        <span class="me-2"><i class="fa-solid fa-paper-plane"></i></span> Send Reset Link
      </button>

      <button class="lx-secondary mt-2" type="button" id="fp_back">
        <i class="fa-solid fa-arrow-left me-2"></i> Back to Login
      </button>
    </form>
  </section>

  <!-- RIGHT -->
  <aside class="lx-right" id="lx_visual">
    <span class="lx-arc" aria-hidden="true"></span>
    <span class="lx-ring" aria-hidden="true"></span>

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
  // ✅ Reset-link flow API + routes
  const SEND_LINK_API = @json(url('/api/auth/forgot-password/send-link'));
  const LOGIN_PAGE    = @json(url('/login'));

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const form      = document.getElementById('fp_form');
  const emailIn   = document.getElementById('fp_email');
  const btn       = document.getElementById('fp_btn');
  const alertEl   = document.getElementById('fp_alert');
  const backBtn   = document.getElementById('fp_back');

  const linkWrap  = document.getElementById('fp_link_wrap');
  const linkHelp  = document.getElementById('fp_link_help');
  const resendBtn = document.getElementById('fp_resend');

  let sentOnce = false;

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

  function validEmail(email){
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function showSentState(email){
    sentOnce = true;
    linkWrap.classList.remove('d-none');
    emailIn.readOnly = true; // keep consistent; user can resend without changing email
    sessionStorage.setItem('fp_email', email);

    // keep primary button usable as resend too
    btn.innerHTML = '<span class="me-2"><i class="fa-solid fa-paper-plane"></i></span> Resend Reset Link';
    linkHelp.textContent = 'Link sent to: ' + email + ' (valid for 10 minutes). Check spam/junk if needed.';
  }

  async function sendLink(isResend=false){
    clearAlert();
    const email = (emailIn.value || '').trim().toLowerCase();

    if(!email){ showAlert('error','Please enter your email address.'); emailIn.focus(); return; }
    if(!validEmail(email)){ showAlert('error','Please enter a valid email address.'); emailIn.focus(); return; }

    setBusy(true, '<i class="fa-solid fa-spinner fa-spin me-2"></i>' + (isResend ? 'Resending…' : 'Sending…'));

    try{
      const res = await fetch(SEND_LINK_API, {
        method: 'POST',
        credentials: 'same-origin',
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
                    (data?.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to send reset link.');
        showAlert('error', msg);
        setBusy(false, '<span class="me-2"><i class="fa-solid fa-paper-plane"></i></span> ' + (sentOnce ? 'Resend Reset Link' : 'Send Reset Link'));
        return;
      }

      // ✅ Always show success (no email enumeration)
      showSentState(email);
      showAlert('success', data?.message || 'If the email exists, a reset link has been sent.');

    } catch(e){
      showAlert('error','Network error. Please try again.');
    } finally{
      setBusy(false, '<span class="me-2"><i class="fa-solid fa-paper-plane"></i></span> ' + (sentOnce ? 'Resend Reset Link' : 'Send Reset Link'));
    }
  }

  backBtn?.addEventListener('click', () => window.location.href = LOGIN_PAGE);
  resendBtn?.addEventListener('click', () => sendLink(true));

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    await sendLink(sentOnce);
  });

  // Prefill email if user returned
  document.addEventListener('DOMContentLoaded', () => {
    const savedEmail = sessionStorage.getItem('fp_email');
    if (savedEmail) emailIn.value = savedEmail;
  });
})();
</script>

</body>
</html>