<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>W3Techiez</title>

  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicons/favicon.png') }}">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  
  <!-- Your global theme -->
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}"/>

  <!-- Page-specific styles (namespaced with .lp-) -->
  <style>
    body{
      background: var(--bg-body);
      color: var(--text-color);
      font-family: var(--font-sans);
    }

    .lp-page{
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* =========================
       Generic scroll "arrival" animation
       ========================= */
    .lp-animate{
      opacity: 0;
      transform: translateY(26px) scale(.98);
      transition:
        opacity .55s ease-out,
        transform .55s ease-out,
        box-shadow .25s ease-out,
        border-color .25s ease-out;
      will-change: opacity, transform;
    }
    .lp-animate.is-visible{
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    /* optional slight delay for staggered items */
    .lp-animate-delay-1{ transition-delay: .08s; }
    .lp-animate-delay-2{ transition-delay: .16s; }
    .lp-animate-delay-3{ transition-delay: .24s; }

    /* Hover lift for cards */
    .lp-hover-lift:hover{
      transform: translateY(-3px) scale(1.01);
      box-shadow: var(--shadow-3);
      border-color: var(--accent-color);
    }

    /* =========================
       Announcement Strip
       ========================= */
    .lp-announcement{
      background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
      color: #fff;
      font-size: var(--fs-12);
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .lp-announcement-inner{
      max-width: 1200px;
      margin: 0 auto;
      padding: 6px 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      overflow: hidden;
    }
    .lp-announcement-label{
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .08em;
      white-space: nowrap;
    }
    .lp-announcement-track{
      position: relative;
      flex: 1;
      overflow: hidden;
      height: 18px;
    }
    .lp-announcement-scroll{
      position: absolute;
      white-space: nowrap;
      animation: lp-marquee 18s linear infinite;
    }
    .lp-announcement-scroll span + span{
      margin-left: 40px;
    }
    @keyframes lp-marquee{
      0%{ transform: translateX(0); }
      100%{ transform: translateX(-50%); }
    }

    /* =========================
       Top Nav
       ========================= */
    .lp-nav{
      position: sticky;
      top: 0;
      z-index: 1030;
      backdrop-filter: blur(12px);
      background: color-mix(in oklab, var(--surface) 80%, transparent);
      border-bottom: 1px solid var(--line-strong);
    }
    .lp-nav-inner{
      max-width: 1200px;
      margin: 0 auto;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .lp-brand{
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }
    .lp-brand img{
      height: 30px;
    }
    .lp-brand span{
      font-family: var(--font-head);
      font-weight: 700;
      color: var(--ink);
      font-size: 1.1rem;
    }

    .lp-nav-links{
      margin-left: 24px;
      display: flex;
      align-items: center;
      gap: 18px;
      font-size: var(--fs-14);
    }
    .lp-nav-item{
      position: relative;
    }
    .lp-nav-item > a{
      color: var(--text-color);
      text-decoration: none;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .lp-nav-item > a i{
      font-size: 0.7rem;
    }
    .lp-nav-item > a:hover{
      color: var(--accent-color);
    }

    /* Sub navigation */
    .lp-submenu{
      position: absolute;
      top: 100%;
      left: 0;
      min-width: 220px;
      padding: 10px 10px 8px;
      border-radius: 12px;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-3);
      opacity: 0;
      transform: translateY(6px);
      pointer-events: none;
      transition: opacity .16s ease, transform .16s ease;
      z-index: 1200;
    }
    .lp-submenu-title{
      font-weight: 600;
      font-size: var(--fs-12);
      text-transform: uppercase;
      letter-spacing: .06em;
      color: var(--muted-color);
      margin-bottom: 4px;
    }
    .lp-submenu-links{
      list-style: none;
      padding: 0;
      margin: 0;
      font-size: var(--fs-13);
    }
    .lp-submenu-links li + li{
      margin-top: 3px;
    }
    .lp-submenu-links a{
      color: var(--text-color);
      text-decoration: none;
      display: block;
      padding: 3px 0;
    }
    .lp-submenu-links a span.label{
      font-weight: 500;
    }
    .lp-submenu-links a span.meta{
      display:block;
      font-size: var(--fs-11);
      color: var(--muted-color);
    }
    .lp-submenu-links a:hover{
      color: var(--accent-color);
    }

    .lp-nav-item:hover .lp-submenu{
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto;
    }

    .lp-nav-actions{
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Top contact info */
    .lp-nav-contact{
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 2px;
      padding-right: 10px;
      margin-right: 6px;
      border-right: 1px solid var(--line-soft);
      font-size: var(--fs-11);
      color: var(--muted-color);
      white-space: nowrap;
    }
    .lp-nav-contact span{
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .lp-nav-contact i{
      font-size: 0.75rem;
      color: var(--secondary-color);
    }
    @media (max-width: 768px){
      .lp-nav-contact{
        display: none;
      }
    }

    .lp-btn-outline{
      border-radius: 999px;
      padding: 6px 14px;
      border: 1px solid var(--line-strong);
      background: var(--surface);
      color: var(--text-color);
      font-size: var(--fs-14);
      font-weight: 500;
    }
    .lp-btn-outline:hover{
      background: color-mix(in oklab, var(--surface) 80%, var(--accent-color) 20%);
    }
    .lp-btn-primary{
      border-radius: 999px;
      padding: 7px 16px;
      border: none;
      background: var(--primary-color);
      color: #fff;
      font-size: var(--fs-14);
      font-weight: 600;
      box-shadow: var(--shadow-2);
    }
    .lp-btn-primary:hover{
      filter: brightness(0.96);
    }

    @media (max-width: 992px){
      .lp-nav-links{ display:none; }
    }

    /* =========================
       Hero Section
       ========================= */
    .lp-hero{
      position: relative;
      padding: 70px 16px 48px;
      color: var(--ink);
      background:
        linear-gradient(135deg,
          color-mix(in oklab, var(--bg-body) 70%, transparent) 0%,
          transparent 55%),
        /* url('{{ asset('assets/media/images/web/hero-bg.jpg') }}'); */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .lp-hero::before{
      content:"";
      position:absolute;
      inset:0;
      background:linear-gradient(135deg,
        color-mix(in oklab, var(--bg-body) 80%, transparent) 0%,
        color-mix(in oklab, var(--bg-body) 40%, transparent) 40%,
        color-mix(in oklab, #000000 30%, transparent) 100%
      );
      opacity:.88;
      pointer-events:none;
      z-index:0;
    }

    .lp-hero-inner{
      position: relative;
      z-index: 1;
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: minmax(0, 3fr) minmax(0, 2.6fr);
      gap: 40px;
      align-items: center;
    }
    @media (max-width: 992px){
      .lp-hero-inner{
        grid-template-columns: 1fr;
        text-align: center;
      }
    }

    .lp-hero-kicker{
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 999px;
      border: 1px solid var(--line-strong);
      background: var(--surface);
      font-size: var(--fs-12);
      color: var(--muted-color);
      margin-bottom: 10px;
    }
    .lp-hero-kicker span.badge{
      background: var(--t-primary);
      color: var(--primary-color);
      border-color: transparent;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .06em;
    }
    .lp-hero-title{
      font-family: var(--font-head);
      font-size: 2.3rem;
      line-height: 1.2;
      color: var(--ink);
      margin-bottom: 14px;
    }
    .lp-hero-title span.highlight{
      color: var(--primary-color);
    }
    .lp-hero-sub{
      font-size: var(--fs-15);
      color: var(--muted-color);
      max-width: 32rem;
      margin-bottom: 18px;
    }
    @media (max-width: 992px){
      .lp-hero-sub{ margin-inline:auto; }
    }

    .lp-hero-search{
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
      margin-bottom: 18px;
    }
    .lp-hero-search .form-control{
      flex: 1 1 220px;
      border-radius: 999px;
      padding-inline: 16px;
    }
    .lp-hero-tags{
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      font-size: var(--fs-12);
      color: var(--muted-color);
    }
    .lp-hero-tags button{
      border-radius: 999px;
      border: 1px solid var(--line-strong);
      background: var(--surface);
      padding: 3px 9px;
      font-size: var(--fs-12);
      cursor: pointer;
    }
    .lp-hero-tags button:hover{
      background: var(--page-hover);
    }

    .lp-hero-stats{
      display: flex;
      flex-wrap: wrap;
      gap: 18px;
      margin-top: 20px;
      font-size: var(--fs-13);
    }
    .lp-hero-stat{
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .lp-hero-stat strong{
      font-size: 1.1rem;
      color: var(--ink);
    }
    .lp-hero-stat span{
      color: var(--muted-color);
    }

    /* Right side hero visuals – stacked cards with rotation and arrows */
    .lp-hero-visual{
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 14px;
      align-items: center;
      justify-content: center;
    }

    .lp-hero-stack-wrap{
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      width: 100%;
    }

    .lp-hero-stack{
      position: relative;
      width: 100%;
      max-width: 420px;
      aspect-ratio: 4 / 3;
    }

    .lp-hero-card-img{
      position:absolute;
      inset:0;
      border-radius: 20px;
      border: 1px solid var(--line-strong);
      overflow:hidden;
      box-shadow: var(--shadow-2);
      background: var(--surface);
      opacity: .3;
      transform: translateY(24px) scale(.9) rotate(0deg);
      transition: opacity .25s ease, transform .25s ease, box-shadow .25s ease, z-index .25s ease;
      z-index: 1;
    }
    .lp-hero-card-img img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display:block;
    }
    .lp-hero-card-img.is-active{
      opacity: 1;
      transform: translate(0,0) scale(1) rotate(0deg);
      box-shadow: var(--shadow-3);
      z-index: 4;
    }
    .lp-hero-card-img.is-prev{
      opacity: .8;
      transform: translate(-20px, 18px) scale(.96) rotate(-7deg);
      z-index: 3;
    }
    .lp-hero-card-img.is-next{
      opacity: .8;
      transform: translate(20px, 18px) scale(.96) rotate(7deg);
      z-index: 3;
    }
    .lp-hero-card-img.is-far{
      opacity: .45;
      transform: translate(0, 32px) scale(.9) rotate(0deg);
      z-index: 2;
    }

    .lp-hero-nav{
      width: 34px;
      height: 34px;
      border-radius: 999px;
      border: 1px solid var(--line-soft);
      background: color-mix(in oklab, var(--surface) 85%, #020617 15%);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: var(--shadow-1);
      font-size: 0.85rem;
      transition: background .16s ease, transform .16s ease, box-shadow .16s ease, border-color .16s ease;
      color: var(--muted-color);
    }
    .lp-hero-nav:hover{
      background: color-mix(in oklab, var(--primary-color) 18%, var(--surface) 82%);
      border-color: var(--accent-color);
      color: var(--ink);
      transform: translateY(-1px);
      box-shadow: var(--shadow-2);
    }

    .lp-hero-nav-prev i{
      transform: translateX(-1px);
    }
    .lp-hero-nav-next i{
      transform: translateX(1px);
    }

    /* Subtle glow under stack only */
    .lp-hero-visual::before{
      content:"";
      position:absolute;
      bottom:-4px;
      width:78%;
      max-width: 460px;
      height: 90px;
      background:
        radial-gradient(ellipse at center,
          color-mix(in oklab, var(--accent-color) 28%, transparent) 0%,
          transparent 70%);
      opacity:.45;
      filter: blur(18px);
      pointer-events:none;
      z-index:0;
    }

    @media (max-width: 992px){
      .lp-hero-stack{
        max-width: 360px;
        margin-inline:auto;
      }
    }

    /* =========================
       Trusted by
       ========================= */
    .lp-trusted{
      background: var(--surface);
      border-top: 1px solid var(--line-soft);
      border-bottom: 1px solid var(--line-soft);
      padding: 18px 16px;
    }
    .lp-trusted-inner{
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 14px;
    }
    .lp-trusted-label{
      font-size: var(--fs-12);
      color: var(--muted-color);
      text-transform: uppercase;
      letter-spacing: .08em;
      font-weight: 600;
    }
    .lp-trusted-logos{
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      align-items: center;
      font-size: var(--fs-14);
      color: var(--muted-color);
    }
    .lp-logo-pill{
      padding: 4px 10px;
      border-radius: 999px;
      border: 1px solid var(--line-soft);
      background: var(--bg-body);
    }

    /* =========================
       Section generic
       ========================= */
    .lp-section{
      padding: 40px 16px 20px;
    }
    .lp-section-inner{
      max-width: 1200px;
      margin: 0 auto;
    }
    .lp-section-head{
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 18px;
    }
    .lp-section-title{
      font-family: var(--font-head);
      font-size: 1.3rem;
      color: var(--ink);
      margin-bottom: 0;
    }
    .lp-section-sub{
      font-size: var(--fs-13);
      color: var(--muted-color);
    }
    .lp-section-link{
      font-size: var(--fs-13);
      color: var(--secondary-color);
      text-decoration: none;
    }
    .lp-section-link:hover{
      color: var(--accent-color);
    }

    /* =========================
       Categories
       ========================= */
    .lp-cat-grid{
      display: grid;
      grid-template-columns: repeat(4, minmax(0,1fr));
      gap: 14px;
    }
    @media (max-width: 992px){
      .lp-cat-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    }
      /* --- Mobile category layout hard override --- */
      @media (max-width: 576px){
        .lp-cat-grid{
          display: flex !important;
          flex-wrap: wrap !important;
          gap: 12px;
        }
 
        .lp-cat-card{
          flex: 0 0 calc(50% - 8px);  /* two per row on most phones */
        }
      }
    .lp-cat-card{
      background: var(--surface);
      border: 1px solid var(--line-strong);
      border-radius: 14px;
      padding: 14px;
      box-shadow: var(--shadow-1);
      display: flex;
      flex-direction: column;
      gap: 6px;
      cursor: pointer;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }
    .lp-cat-icon{
      width: 34px;
      height: 34px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--t-primary);
      color: var(--primary-color);
      margin-bottom: 4px;
    }
    .lp-cat-name{
      font-weight: 600;
      color: var(--ink);
    }
    .lp-cat-meta{
      font-size: var(--fs-12);
      color: var(--muted-color);
    }
    .lp-cat-card:hover{
      transform: translateY(-2px);
      box-shadow: var(--shadow-2);
      border-color: var(--accent-color);
    }

    /* =========================
       Featured Courses
       ========================= */
    .lp-course-grid{
      display: grid;
      grid-template-columns: repeat(4, minmax(0,1fr));
      gap: 16px;
    }
    @media (max-width: 992px){
      .lp-course-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    }
    @media (max-width: 576px){
      .lp-course-grid{ grid-template-columns: 1fr; }
    }

    .lp-course-card{
      background: var(--surface);
      border-radius: 14px;
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      position: relative;
      transition: box-shadow .16s ease, transform .16s ease, border-color .16s ease;
    }
    .lp-course-card:hover{
      box-shadow: var(--shadow-3);
      transform: translateY(-2px);
      border-color: var(--accent-color);
      z-index: 1;
    }

    .lp-course-thumb{
      height: 150px;
      background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
      overflow: hidden;
    }
    .lp-course-thumb img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      opacity: .96;
      transform: scale(1.02);
      transition: transform .3s ease, opacity .3s ease;
    }
    .lp-course-card:hover .lp-course-thumb img{
      transform: scale(1.06);
      opacity: 1;
    }

    .lp-course-body{
      padding: 12px 12px 10px;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .lp-course-title{
      font-size: var(--fs-14);
      font-weight: 600;
      color: var(--ink);
    }
    .lp-course-meta{
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      font-size: var(--fs-12);
      color: var(--muted-color);
    }
    .lp-course-meta span{
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .lp-course-summary{
      font-size: var(--fs-12);
      color: var(--muted-color);
      margin-top: 4px;
    }

    .lp-course-footer{
      padding: 8px 12px 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: var(--fs-13);
      border-top: 1px solid var(--line-soft);
    }
    .lp-price{
      font-weight: 700;
      color: var(--ink);
    }
    .lp-badge-level{
      font-size: var(--fs-11);
      padding: 3px 9px;
      border-radius: 999px;
      background: var(--t-info);
      color: var(--info-color);
      font-weight: 500;
    }

    /* =========================
       HOW IT WORKS section
       ========================= */
    .lp-how{
      background: var(--bg-body);
    }
    .lp-how-grid{
      display: grid;
      grid-template-columns: repeat(3, minmax(0,1fr));
      gap: 16px;
    }
    @media (max-width: 992px){
      .lp-how-grid{ grid-template-columns: 1fr; }
    }
    .lp-how-card{
      border-radius: 16px;
      border: 1px solid var(--line-soft);
      background: var(--surface);
      padding: 16px 14px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      box-shadow: var(--shadow-1);
    }
    .lp-how-step{
      width: 26px;
      height: 26px;
      border-radius: 999px;
      border: 1px solid var(--primary-color);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: var(--fs-12);
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 4px;
      background: var(--t-primary);
    }
    .lp-how-title{
      font-weight: 600;
      color: var(--ink);
      font-size: var(--fs-14);
    }
    .lp-how-text{
      font-size: var(--fs-13);
      color: var(--muted-color);
    }

    /* =========================
       PARALLAX (fixed background) section
       ========================= */
    .lp-parallax{
      position: relative;
      padding: 70px 16px;
      color: #fff;
      background-image: url("https://images.pexels.com/photos/1181670/pexels-photo-1181670.jpeg?auto=compress&cs=tinysrgb&w=1600");
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      overflow: hidden;
      margin-top: 10px;
      margin-bottom: 10px;
    }
    .lp-parallax::before{
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top left,
                  rgba(148, 163, 255, 0.45),
                  transparent 50%)
                  ,
                  linear-gradient(135deg,
                  rgba(15, 23, 42, 0.88),
                  rgba(15, 23, 42, 0.96));
      pointer-events: none;
    }
    .lp-parallax-inner{
      position: relative;
      z-index: 1;
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: minmax(0, 3fr) minmax(0, 2.2fr);
      gap: 32px;
      align-items: center;
    }
    @media (max-width: 992px){
      .lp-parallax-inner{
        grid-template-columns: 1fr;
        text-align: center;
      }
    }
    .lp-parallax-title{
      font-family: var(--font-head);
      color: white;
      font-size: 1.8rem;
      margin-bottom: 8px;
    }
    .lp-parallax-sub{
      font-size: var(--fs-13);
      color: rgba(226,232,240,0.9);
      max-width: 30rem;
    }
    @media (max-width: 992px){
      .lp-parallax-sub{
        margin-inline: auto;
      }
    }
    .lp-parallax-pills{
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 16px;
      font-size: var(--fs-12);
    }
    .lp-parallax-pills span{
      border-radius: 999px;
      border: 1px solid rgba(148,163,255,0.5);
      padding: 4px 10px;
      background: rgba(15,23,42,0.7);
    }
    .lp-parallax-cardGrid{
      display: grid;
      grid-template-columns: 1fr;
      gap: 10px;
    }
    .lp-parallax-mini{
      border-radius: 14px;
      border: 1px solid rgba(148,163,255,0.5);
      background: rgba(15,23,42,0.9);
      padding: 10px 12px;
      font-size: var(--fs-13);
      display: flex;
      gap: 8px;
      align-items: flex-start;
    }
    .lp-parallax-mini i{
      margin-top: 2px;
    }

    /* =========================
       Mentors section - enhanced
       ========================= */
    #mentors{
      background: var(--bg-body);
    }
    .lp-mentors-header-badge{
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 3px 9px;
      border-radius: 999px;
      font-size: var(--fs-11);
      background: var(--t-primary);
      color: var(--primary-color);
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 4px;
    }
    .lp-mentors-meta-row{
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      font-size: var(--fs-12);
      color: var(--muted-color);
      margin-top: 4px;
    }
    .lp-mentors-meta-row span{
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .lp-mentors-grid{
      display: grid;
      grid-template-columns: repeat(3, minmax(0,1fr));
      gap: 16px;
    }
    @media (max-width: 992px){
      .lp-mentors-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    }
    @media (max-width: 576px){
      .lp-mentors-grid{ grid-template-columns: 1fr; }
    }
    .lp-mentor-card{
      border-radius: 18px;
      border: 1px solid var(--line-soft);
      background: radial-gradient(circle at top left,
                  color-mix(in oklab, var(--t-primary) 40%, transparent),
                  var(--surface) 55%);
      padding: 14px;
      box-shadow: var(--shadow-1);
      display: flex;
      flex-direction: column;
      gap: 8px;
      position: relative;
      overflow: hidden;
      cursor: default;
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .lp-mentor-card::after{
      content:"";
      position:absolute;
      inset:-40%;
      background: radial-gradient(circle at top,
                  color-mix(in oklab, var(--accent-color) 25%, transparent),
                  transparent 55%);
      opacity:0;
      transform: translateY(40px);
      transition: opacity .25s ease, transform .25s ease;
      pointer-events:none;
      z-index:0;
    }
    .lp-mentor-card:hover{
      transform: translateY(-4px);
      box-shadow: var(--shadow-3);
      border-color: var(--accent-color);
      background: radial-gradient(circle at top left,
                  color-mix(in oklab, var(--t-primary) 55%, transparent),
                  var(--surface) 65%);
    }
    .lp-mentor-card:hover::after{
      opacity:.5;
      transform: translateY(0);
    }
    .lp-mentor-content{
      position: relative;
      z-index: 1;
    }
    .lp-mentor-header{
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 4px;
    }
    .lp-mentor-avatar{
      width: 50px;
      height: 50px;
      border-radius: 999px;
      background: conic-gradient(
        from 180deg,
        color-mix(in oklab, var(--primary-color) 80%, #020617 20%),
        color-mix(in oklab, var(--accent-color) 80%, #020617 20%),
        color-mix(in oklab, var(--primary-color) 80%, #020617 20%)
      );
      padding: 2px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .lp-mentor-avatar span{
      width: 100%;
      height: 100%;
      border-radius: inherit;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      background: var(--surface);
      color: var(--primary-color);
      font-size: var(--fs-14);
    }
    .lp-mentor-name{
      font-weight: 600;
      color: var(--ink);
      font-size: var(--fs-14);
    }
    .lp-mentor-role{
      font-size: var(--fs-12);
      color: var(--muted-color);
    }
    .lp-mentor-pill-row{
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      font-size: var(--fs-11);
      margin-top: 4px;
    }
    .lp-mentor-pill{
      border-radius:999px;
      padding:3px 8px;
      border: 1px dashed var(--line-soft);
      background: color-mix(in oklab, var(--bg-body) 80%, transparent);
      color: var(--muted-color);
      display:inline-flex;
      align-items:center;
      gap:5px;
    }
    .lp-mentor-pill i{
      font-size: .7rem;
    }
    .lp-mentor-text{
      font-size: var(--fs-13);
      color: var(--muted-color);
      margin-top: 4px;
    }
    .lp-mentor-tags{
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 6px;
    }
    .lp-mentor-tags span{
      font-size: var(--fs-11);
      padding: 2px 8px;
      border-radius: 999px;
      border: 1px solid var(--line-soft);
      background: var(--bg-body);
      color: var(--text-color);
    }

    /* =========================
       Outcomes section
       ========================= */
    .lp-outcomes{
      background: var(--surface);
      border-top: 1px solid var(--line-soft);
      border-bottom: 1px solid var(--line-soft);
    }
    .lp-outcomes-inner{
      max-width: 1200px;
      margin: 0 auto;
    }
    .lp-outcomes-grid{
      display: grid;
      grid-template-columns: minmax(0, 2.2fr) minmax(0, 2.2fr);
      gap: 18px;
      align-items: center;
    }
    @media (max-width: 992px){
      .lp-outcomes-grid{ grid-template-columns: 1fr; }
    }
    .lp-outcomes-logos{
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      font-size: var(--fs-12);
    }
    .lp-company-pill{
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px dashed var(--line-soft);
      background: var(--bg-body);
      color: var(--muted-color);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .lp-company-pill i{
      font-size: 0.7rem;
    }
    .lp-outcomes-list{
      font-size: var(--fs-13);
      color: var(--muted-color);
      padding-left: 18px;
      margin: 0;
    }
    .lp-outcomes-list li + li{
      margin-top: 4px;
    }

    /* =========================
       Stats band
       ========================= */
    .lp-stats-band{
      background: var(--surface);
      border-top: 1px solid var(--line-soft);
      border-bottom: 1px solid var(--line-soft);
      padding: 24px 16px;
      margin-top: 10px;
    }
    .lp-stats-inner{
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(4, minmax(0,1fr));
      gap: 18px;
    }
    @media (max-width: 768px){
      .lp-stats-inner{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    }
    .lp-stat-card{
      border-radius: 14px;
      background: var(--bg-body);
      border: 1px solid var(--line-soft);
      padding: 12px 14px;
    }
    .lp-stat-card strong{
      display: block;
      font-size: 1.3rem;
      color: var(--ink);
    }
    .lp-stat-card span{
      font-size: var(--fs-13);
      color: var(--muted-color);
    }

    /* =========================
       Testimonials
       ========================= */
    .lp-testimonials{
      background: var(--bg-body);
    }
    .lp-test-grid{
      display: grid;
      grid-template-columns: repeat(3, minmax(0,1fr));
      gap: 16px;
    }
    @media (max-width: 992px){
      .lp-test-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    }
    @media (max-width: 576px){
      .lp-test-grid{ grid-template-columns: 1fr; }
    }
    .lp-test-card{
      border-radius: 14px;
      background: var(--surface);
      border: 1px solid var(--line-strong);
      padding: 14px;
      box-shadow: var(--shadow-1);
    }
    .lp-test-text{
      font-size: var(--fs-13);
      color: var(--text-color);
      margin-bottom: 8px;
    }
    .lp-test-author{
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: var(--fs-13);
    }
    .lp-avatar{
      width: 32px;
      height: 32px;
      border-radius: 999px;
      background: var(--t-primary);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-color);
      font-weight: 600;
    }
    .lp-test-author span.name{
      color: var(--ink);
      font-weight: 600;
    }
    .lp-test-author span.role{
      color: var(--muted-color);
      font-size: var(--fs-12);
    }

    /* =========================
       FAQ - enhanced
       ========================= */
    .lp-faq{
      background: var(--bg-body);
    }
    .lp-faq-layout{
      display: grid;
      grid-template-columns: minmax(0, 1.4fr) minmax(0, 2.2fr);
      gap: 24px;
      align-items: flex-start;
    }
    @media (max-width: 992px){
      .lp-faq-layout{
        grid-template-columns: 1fr;
      }
    }
    .lp-faq-intro{
      font-size: var(--fs-13);
      color: var(--muted-color);
    }
    .lp-faq-intro-list{
      margin-top: 10px;
      padding-left: 18px;
      font-size: var(--fs-13);
    }
    .lp-faq-intro-list li + li{
      margin-top: 4px;
    }
    .lp-faq-contact{
      margin-top: 16px;
      font-size: var(--fs-12);
      color: var(--muted-color);
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px dashed var(--line-soft);
      background: var(--surface);
    }

    .lp-faq .accordion-item{
      border-radius: 12px !important;
      border: 1px solid var(--line-soft);
      overflow: hidden;
      background: var(--surface);
      margin-bottom: 8px;
      box-shadow: var(--shadow-1);
    }
    .lp-faq .accordion-button{
      font-size: var(--fs-13);
      font-weight: 500;
      background: var(--surface);
      color: var(--ink);
      box-shadow: none;
      padding-top: 10px;
      padding-bottom: 10px;
    }
    .lp-faq .accordion-button::before{
      content:"\f059";
      font-family:"Font Awesome 6 Free";
      font-weight:900;
      margin-right: 8px;
      font-size:.8rem;
      color: var(--muted-color);
    }
    .lp-faq .accordion-button:not(.collapsed){
      color: var(--primary-color);
      background: var(--t-primary);
    }
    .lp-faq .accordion-button:not(.collapsed)::before{
      color: var(--primary-color);
    }
    .lp-faq .accordion-body{
      font-size: var(--fs-13);
      color: var(--muted-color);
    }

    /* =========================
       CTA strip
       ========================= */
    .lp-cta{
      padding: 40px 16px;
    }
    .lp-cta-inner{
      max-width: 900px;
      margin: 0 auto;
      border-radius: 18px;
      border: 1px solid var(--line-strong);
      background: linear-gradient(135deg,
        color-mix(in oklab, var(--primary-color) 20%, var(--surface) 80%),
        color-mix(in oklab, var(--accent-color) 18%, var(--surface) 82%)
      );
      padding: 24px 20px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 16px;
    }
    .lp-cta-text{
      flex: 1 1 260px;
    }
    .lp-cta-title{
      font-family: var(--font-head);
      font-size: 1.4rem;
      color: var(--ink);
      margin-bottom: 6px;
    }
    .lp-cta-sub{
      font-size: var(--fs-13);
      color: var(--muted-color);
    }
    .lp-cta-actions{
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    /* =========================
       Footer
       ========================= */
    .lp-footer{
      margin-top: auto;
      background: var(--surface);
      border-top: 1px solid var(--line-strong);
      padding: 28px 16px 14px;
    }
    .lp-footer-inner{
      max-width: 1200px;
      margin: 0 auto;
    }
    .lp-footer-main{
      display: grid;
      grid-template-columns: minmax(0, 2.4fr) repeat(3, minmax(0,1fr));
      gap: 20px;
      margin-bottom: 16px;
      align-items: flex-start;
    }
    @media (max-width: 768px){
      .lp-footer-main{ grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 576px){
      .lp-footer-main{ grid-template-columns: 1fr; }
    }
    .lp-footer-brand{
      font-size: var(--fs-13);
      color: var(--muted-color);
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .lp-footer-brand-logo{
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .lp-footer-brand-logo img{
      height: 30px;
    }
    .lp-footer-brand-logo strong{
      font-family: var(--font-head);
      color: var(--ink);
      font-size: 1.05rem;
    }

    .lp-footer-contact{
      margin-top: 4px;
      font-size: var(--fs-12);
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .lp-footer-contact span{
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .lp-footer-contact i{
      font-size: 0.75rem;
      color: var(--secondary-color);
    }

    .lp-footer-col-title{
      font-weight: 600;
      font-size: var(--fs-13);
      color: var(--ink);
      margin-bottom: 6px;
    }
    .lp-footer-links{
      list-style: none;
      padding: 0;
      margin: 0;
      font-size: var(--fs-13);
    }
    .lp-footer-links li + li{
      margin-top: 4px;
    }
    .lp-footer-links a{
      color: var(--muted-color);
      text-decoration: none;
    }
    .lp-footer-links a:hover{
      color: var(--accent-color);
    }
    .lp-footer-bottom{
      border-top: 1px solid var(--line-soft);
      padding-top: 10px;
      font-size: var(--fs-12);
      color: var(--muted-color);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 8px;
      justify-content: space-between;
    }
    .lp-footer-social{
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .lp-footer-social a{
      width: 28px;
      height: 28px;
      border-radius: 999px;
      border: 1px solid var(--line-strong);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--muted-color);
      text-decoration: none;
      font-size: 0.8rem;
    }
    .lp-footer-social a:hover{
      color: var(--accent-color);
      border-color: var(--accent-color);
    }

    /* =========================
       Back-to-top button
       ========================= */
    .lp-back-top{
      position: fixed;
      right: 18px;
      bottom: 20px;
      width: 42px;
      height: 42px;
      border-radius: 999px;
      border: 1px solid var(--line-strong);
      background: color-mix(in oklab, var(--surface) 85%, #020617 15%);
      color: var(--muted-color);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: .9rem;
      box-shadow: var(--shadow-2);
      cursor: pointer;
      opacity: 0;
      transform: translateY(10px);
      pointer-events: none;
      transition: opacity .25s ease, transform .25s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease;
      z-index: 1400;
    }
    .lp-back-top.is-visible{
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto;
    }
    .lp-back-top:hover{
      background: color-mix(in oklab, var(--primary-color) 18%, var(--surface) 82%);
      border-color: var(--accent-color);
      color: var(--ink);
      box-shadow: var(--shadow-3);
    }

    /* =========================
       Fixed batch badges
       ========================= */
    .lp-batch-badges{
      position: fixed;
      right: 18px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      flex-direction: column;
      gap: 8px;
      z-index: 1350;
    }
    .lp-batch-badge{
      border-radius: 999px;
      background: color-mix(in oklab, var(--surface) 90%, var(--primary-color) 10%);
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-2);
      padding: 8px 12px;
      font-size: var(--fs-11);
      display: flex;
      align-items: center;
      gap: 8px;
      max-width: 260px;
      cursor: pointer;
    }
    .lp-batch-badge-main{
      background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
      color: #fff;
      border-color: transparent;
    }
    .lp-batch-badge-main .lp-batch-badge-meta{
      opacity: 0.95;
    }
    .lp-batch-badge i{
      font-size: 0.85rem;
    }
    .lp-batch-badge-label{
      font-weight: 600;
    }
    .lp-batch-badge-meta{
      opacity: .85;
    }
    @media (max-width: 992px){
      .lp-batch-badges{
        display:none;
      }
    }

    /* Dark tweaks */
    html.theme-dark .lp-nav{
      background: color-mix(in oklab, var(--surface) 92%, transparent);
    }
    html.theme-dark .lp-course-thumb{
      background: linear-gradient(135deg,
        color-mix(in oklab, var(--primary-color) 60%, #020617 40%),
        color-mix(in oklab, var(--accent-color) 60%, #020617 40%)
      );
    }
    html.theme-dark .lp-stat-card{
      background: #020617;
      border-color: var(--line-strong);
    }
  
/* Category Badge (top-right) */
.lp-category-badge{
  position:absolute;
  top:12px;
  right:12px;
  background:#fef3c7;
  color:#b45309;
  padding:6px 12px;
  border-radius:999px;
  font-size:0.75rem;
  font-weight:600;
  box-shadow:0 4px 10px rgba(0,0,0,0.08);
  white-space:nowrap;
}
.lp-nav-inner{
  max-width: 1200px;
  margin: 0 auto;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  position: relative; /* 🔹 needed for absolute mobile menu */
}
/* Burger button (mobile) */
.lp-nav-toggle{
  display: none;
  width: 34px;
  height: 34px;
  border-radius: 999px;
  border: 1px solid var(--line-strong);
  background: var(--surface);
  margin-left: auto;
  margin-right: 4px;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  cursor: pointer;
}

.lp-nav-toggle i{
  pointer-events: none;
}
/* @media (max-width: 992px){
  .lp-nav-links{ display:none; }
} */
@media (max-width: 992px){
  /* Show burger button on mobile */
  .lp-nav-toggle{
    display: inline-flex;
  }

  /* Mobile dropdown styles */
  .lp-nav-links{
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    padding: 10px 16px 12px;
    background: var(--surface);
    border-bottom: 1px solid var(--line-strong);
    box-shadow: var(--shadow-2);
    flex-direction: column;
    gap: 10px;
  }

  /* Default: hidden */
  .lp-nav:not(.is-open) .lp-nav-links{
    display: none;
  }

  /* When nav has .is-open -> show menu */
  .lp-nav.is-open .lp-nav-links{
    display: flex;
  }

  /* Submenus behave like simple stacked sections on mobile */
  .lp-nav-item{
    width: 100%;
  }
  .lp-submenu{
    position: static;
    opacity: 1;
    transform: none;
    pointer-events: auto;
    box-shadow: none;
    border: none;
    padding: 4px 0 0;
    background: transparent;
  }
}


  </style>
</head>
<body class="lp-page">
  {{-- 🔹 Include your global overlay here (ensure it has id="pageOverlay") --}}
  {{-- Change the include path to match your project --}}
  @include('partials.overlay')

  <!-- Announcement strip -->
  <div class="lp-announcement">
  <div class="lp-announcement-inner">
    <div class="lp-announcement-label">Updates</div>
    <div class="lp-announcement-track">
      <div class="lp-announcement-scroll">
        {{-- JS will inject <span> elements here --}}
      </div>
    </div>
  </div>
</div>


  <!-- Top Nav -->
  <nav class="lp-nav">
    <div class="lp-nav-inner">
      <a href="/" class="lp-brand">
        <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
        <span>W3Techiez</span>
      </a>
  
      <div class="lp-nav-links">
         <div class="lp-nav-item">
    <a href="{{ url('/') }}">
      Home
    </a>
  </div>
  <!-- Courses (no dropdown) -->
  <div class="lp-nav-item">
    <a href="{{ url('courses/all') }}">
      Courses
    </a>
  </div>

  <!-- Categories (no dropdown) -->
  <div class="lp-nav-item">
    <a href="{{ url('categories/all') }}">
      Categories
    </a>
  </div>
    <div class="lp-nav-item">
    <a href="{{ url('/updates/all') }}">
      Updates
    </a>
  </div>
  <!-- Why Us (keeps dropdown) -->
  <div class="lp-nav-item" style="display:none">
    <a href="#features">
      Why Us <i class="fa-solid fa-chevron-down"></i>
    </a>
    <div class="lp-submenu">
      <div class="lp-submenu-title">What you get</div>
      <ul class="lp-submenu-links">
        <li>
          <a href="#features">
            <span class="label">Structured learning paths</span>
            <span class="meta">From basics to interview-ready</span>
          </a>
        </li>
        <li>
          <a href="#features">
            <span class="label">Mentor support</span>
            <span class="meta">Live doubts, code reviews</span>
          </a>
        </li>
        <li>
          <a href="#features">
            <span class="label">Real projects</span>
            <span class="meta">Portfolio-worthy applications</span>
          </a>
        </li>
        <li>
          <a href="#features">
            <span class="label">Placement focus</span>
            <span class="meta">Mock interviews, referrals</span>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Reviews (keeps dropdown) -->
  <div class="lp-nav-item" style="display:none">
    <a href="#reviews">
      Reviews <i class="fa-solid fa-chevron-down"></i>
    </a>
    <div class="lp-submenu">
      <div class="lp-submenu-title">Success Stories</div>
      <ul class="lp-submenu-links">
        <li>
          <a href="#reviews">
            <span class="label">Career transitions</span>
            <span class="meta">From non-CS to developer</span>
          </a>
        </li>
        <li>
          <a href="#reviews">
            <span class="label">Freshers to first job</span>
            <span class="meta">Placement stories and journeys</span>
          </a>
        </li>
        <li>
          <a href="#reviews">
            <span class="label">Working professionals</span>
            <span class="meta">Upskilling for better roles</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>


      <div class="lp-nav-actions">
        <!-- Top contact info -->
        <div class="lp-nav-contact"  id="lpNavContact">
          <!-- <span><i class="fa-solid fa-phone"></i> +91-98765-43210</span>
          <span><i class="fa-brands fa-whatsapp"></i> WhatsApp: +91-98765-43210</span>
          <span><i class="fa-solid fa-envelope"></i> support@w3techiez.com</span> -->
        </div>

        <button class="lp-btn-primary" id="lpLoginBtn" type="button">Log in</button>
        {{-- <button class="lp-btn-primary" type="button">Sign up for free</button> --}}
        <!-- 🔹 Mobile burger button -->
      <button
        class="lp-nav-toggle"
        id="lpNavToggle"
        type="button"
        aria-label="Toggle navigation"
        aria-expanded="false"
      >
        <i class="fa-solid fa-bars"></i>
      </button>
      </div>
    </div>
  </nav>