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
    @media (max-width: 576px){
      .lp-cat-grid{ grid-template-columns: 1fr; }
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
        <!-- Courses -->
        <div class="lp-nav-item">
          <a href="#courses">
            Courses <i class="fa-solid fa-chevron-down"></i>
          </a>
          <div class="lp-submenu">
            <div class="lp-submenu-title">Popular Programs</div>
            <ul class="lp-submenu-links">
              <li>
                <a href="#courses">
                  <span class="label">Full-Stack TypeScript</span>
                  <span class="meta">Frontend, backend, deployment</span>
                </a>
              </li>
              <li>
                <a href="#courses">
                  <span class="label">SDET / QA Automation</span>
                  <span class="meta">Testing, frameworks, CI</span>
                </a>
              </li>
              <li>
                <a href="#courses">
                  <span class="label">Data Analytics</span>
                  <span class="meta">Python, SQL, dashboards</span>
                </a>
              </li>
              <li>
                <a href="#courses">
                  <span class="label">DevOps Essentials</span>
                  <span class="meta">Docker, CI/CD, cloud basics</span>
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Categories -->
        <div class="lp-nav-item">
          <a href="#categories">
            Categories <i class="fa-solid fa-chevron-down"></i>
          </a>
          <div class="lp-submenu">
            <div class="lp-submenu-title">Career Paths</div>
            <ul class="lp-submenu-links">
              <li>
                <a href="#categories">
                  <span class="label">Full-Stack Developer</span>
                  <span class="meta">Frontend + backend + deployment</span>
                </a>
              </li>
              <li>
                <a href="#categories">
                  <span class="label">SDET / QA Engineer</span>
                  <span class="meta">Automation, APIs, tools</span>
                </a>
              </li>
              <li>
                <a href="#categories">
                  <span class="label">Data Analyst</span>
                  <span class="meta">Data cleaning, SQL, BI tools</span>
                </a>
              </li>
              <li>
                <a href="#categories">
                  <span class="label">Cloud & DevOps</span>
                  <span class="meta">Infrastructure, CI/CD, monitoring</span>
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Why Us -->
        <div class="lp-nav-item">
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

        <!-- Reviews -->
        <div class="lp-nav-item">
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
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="lp-hero">
    <div class="lp-hero-inner">
      <div class="lp-animate is-visible">
        <div class="lp-hero-kicker">
          <span class="badge">New</span>
          <span>Placement-ready tech programs with mentor support</span>
        </div>
        <h1 class="lp-hero-title">
          Learn modern <span class="highlight">tech skills</span><br/>
          from industry experts.
        </h1>
        <p class="lp-hero-sub">
          Build job-ready skills in Full-Stack Development, Data, DevOps, and more — with structured paths, live doubts support, and real projects designed for placements.
        </p>

        <form class="lp-hero-search" action="#" method="get" style="display:none">
          <input type="text" class="form-control" placeholder="Search for a course, skill, or technology…">
          <button type="submit" class="lp-btn-primary">
            <i class="fa fa-search me-1"></i> Search
          </button>
        </form>

        <div class="lp-hero-tags">
          <span>Popular:</span>
          <button type="button">Full-Stack MERN</button>
          <button type="button">Java Spring Boot</button>
          <button type="button">SDET / QA Automation</button>
          <button type="button">Data Analytics</button>
        </div>

        <div class="lp-hero-stats">
          <div class="lp-hero-stat">
            <strong>10k+</strong>
            <span>Learners trained</span>
          </div>
          <div class="lp-hero-stat">
            <strong>200+</strong>
            <span>Hours of video</span>
          </div>
          <div class="lp-hero-stat">
            <strong>40+</strong>
            <span>Job-oriented tracks</span>
          </div>
        </div>
      </div>

      <!-- Right visuals: rotated image stack with arrow navigation -->
      <div class="lp-hero-visual lp-animate lp-animate-delay-1" data-lp-animate="fade">
  <div class="lp-hero-stack-wrap">

    <button type="button" class="lp-hero-nav lp-hero-nav-prev" id="heroPrevBtn">
      <i class="fa-solid fa-chevron-left"></i>
    </button>

    <div class="lp-hero-stack" id="heroImageStack">
      <!-- JS will render slides here -->
    </div>

    <button type="button" class="lp-hero-nav lp-hero-nav-next" id="heroNextBtn">
      <i class="fa-solid fa-chevron-right"></i>
    </button>

  </div>
</div>

    </div>
  </section>

  <!-- Trusted by -->
  <section class="lp-trusted lp-animate" data-lp-animate="fade-up">
    <div class="lp-trusted-inner">
      <div class="lp-trusted-label">
        Trusted by learners from
      </div>
      <div class="lp-trusted-logos">
        <span class="lp-logo-pill">IITs & NITs</span>
        <span class="lp-logo-pill">Tier-1 Engineering Colleges</span>
        <span class="lp-logo-pill">Working Professionals</span>
        <span class="lp-logo-pill">Fresh Graduates</span>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="lp-section lp-how">
    <div class="lp-section-inner">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-section-title">How learning at W3Techiez works</h2>
          <div class="lp-section-sub">A simple, guided process that takes you from confused to confident.</div>
        </div>
      </div>

      <div class="lp-how-grid">
        <div class="lp-how-card lp-animate lp-hover-lift" data-lp-animate="fade-up">
          <div class="lp-how-step">1</div>
          <div class="lp-how-title">Pick a career path</div>
          <div class="lp-how-text">
            Choose from curated tracks like Full-Stack, SDET, Data Analytics or DevOps based on your background and goals.
          </div>
        </div>
        <div class="lp-how-card lp-animate lp-hover-lift lp-animate-delay-1" data-lp-animate="fade-up">
          <div class="lp-how-step">2</div>
          <div class="lp-how-title">Learn with structure & mentors</div>
          <div class="lp-how-text">
            Follow weekly modules, live doubt sessions and projects. You always know what to study next and where you stand.
          </div>
        </div>
        <div class="lp-how-card lp-animate lp-hover-lift lp-animate-delay-2" data-lp-animate="fade-up">
          <div class="lp-how-step">3</div>
          <div class="lp-how-title">Prepare for interviews & placements</div>
          <div class="lp-how-text">
            Get help with resume, portfolio, mock interviews and referrals, so your skills translate into real offers.
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories -->
  <!-- Categories -->
<section id="categories" class="lp-section">
  <div class="lp-section-inner">
    <div class="lp-section-head">
      <div>
        <h2 class="lp-section-title">Explore categories</h2>
        <div class="lp-section-sub">Pick a track that matches your career goal.</div>
      </div>
      <a href="#courses" class="lp-section-link">
        View all courses <i class="fa fa-arrow-right ms-1"></i>
      </a>
    </div>

    {{-- JS will populate this grid using API data --}}
    <div class="lp-cat-grid" id="lpCategoriesGrid">
      <div class="lp-cat-card lp-animate lp-hover-lift lp-cat-skeleton">
        <div class="lp-cat-icon">
          <i class="fa-solid fa-circle-notch fa-spin"></i>
        </div>
        <div class="lp-cat-name">Loading categories…</div>
        <div class="lp-cat-meta">Please wait</div>
      </div>
    </div>
  </div>
</section>

  <!-- Featured courses -->
  <section id="courses" class="lp-section" style="padding-top: 10px;">
    <div class="lp-section-inner">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-section-title">Featured job-ready courses</h2>
          <div class="lp-section-sub">Carefully designed tracks that focus on skills, projects, and placements.</div>
        </div>
        <a href="#" class="lp-section-link">
          Browse complete catalog <i class="fa fa-arrow-right ms-1"></i>
        </a>
      </div>

      <div class="lp-course-grid">
  <!-- Course cards will be injected here via JS -->
</div>

    </div>
  </section>

  <!-- PARALLAX (fixed background) section -->
  <section class="lp-parallax lp-animate" data-lp-animate="fade-up">
    <div class="lp-parallax-inner">
      <div>
        <h2 class="lp-parallax-title">Serious learning for serious careers.</h2>
        <p class="lp-parallax-sub">
          Whether you’re from a CS background or completely switching careers, W3Techiez is built to give you clarity, structure, and accountability — not just random videos.
        </p>
        <div class="lp-parallax-pills">
          <span><i class="fa-solid fa-circle-check me-1"></i> Live & recorded content</span>
          <span><i class="fa-solid fa-circle-check me-1"></i> Doubts on chat & calls</span>
          <span><i class="fa-solid fa-circle-check me-1"></i> Project-driven curriculum</span>
        </div>
      </div>
      <div class="lp-parallax-cardGrid">
        <div class="lp-parallax-mini">
          <i class="fa-solid fa-user-group"></i>
          <div>
            Small cohorts and guided timelines help you stay on track instead of learning alone and giving up midway.
          </div>
        </div>
        <div class="lp-parallax-mini">
          <i class="fa-solid fa-clipboard-check"></i>
          <div>
            Weekly assignments, code reviews and checkpoints make sure you don’t just watch – you actually build.
          </div>
        </div>
        <div class="lp-parallax-mini">
          <i class="fa-solid fa-briefcase"></i>
          <div>
            Interview preparation starts early, with problem-solving practice, projects, and mock interviews built in.
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Outcomes band -->
  <section class="lp-section lp-outcomes" id="outcomes">
    <div class="lp-outcomes-inner">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-section-title">Career outcomes & placements</h2>
          <div class="lp-section-sub">Learners from our programs have joined product companies, IT services, and fast-growing startups.</div>
        </div>
      </div>

      <div class="lp-outcomes-grid">
        <div class="lp-animate lp-hover-lift" data-lp-animate="fade-up">
          <div class="lp-outcomes-logos">
            <span class="lp-company-pill"><i class="fa-solid fa-building"></i> Product startups</span>
            <span class="lp-company-pill"><i class="fa-solid fa-building"></i> IT services & MNCs</span>
            <span class="lp-company-pill"><i class="fa-solid fa-building"></i> Analytics & consulting</span>
            <span class="lp-company-pill"><i class="fa-solid fa-building"></i> Fintech & SaaS</span>
          </div>
        </div>
        <div class="lp-animate lp-animate-delay-1" data-lp-animate="fade-up">
          <ul class="lp-outcomes-list">
            <li>Structured career guidance from mentors who have cracked these roles themselves.</li>
            <li>Showcaseable projects – from full-stack apps to test automation suites and data dashboards.</li>
            <li>Profile review support: resume, LinkedIn, GitHub and portfolio clean-up.</li>
            <li>Mock interviews and interview prep resources tailored to your chosen track.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats band -->
  <section class="lp-stats-band" id="features">
    <div class="lp-stats-inner">
      <div class="lp-stat-card lp-animate" data-lp-animate="fade-up">
        <strong>Structured paths</strong>
        <span>Roadmaps designed to take you from basics to interview-ready with clear milestones.</span>
      </div>
      <div class="lp-stat-card lp-animate lp-animate-delay-1" data-lp-animate="fade-up">
        <strong>Mentor support</strong>
        <span>Live doubt clearing, code reviews, and project feedback from industry mentors.</span>
      </div>
      <div class="lp-stat-card lp-animate lp-animate-delay-2" data-lp-animate="fade-up">
        <strong>Real projects</strong>
        <span>Build applications you can actually show in your CV and portfolio.</span>
      </div>
      <div class="lp-stat-card lp-animate lp-animate-delay-3" data-lp-animate="fade-up">
        <strong>Placement focus</strong>
        <span>Resume review, mock interviews, and referrals through our network.</span>
      </div>
    </div>
  </section>

  <!-- Mentors (beautified) -->
  <section id="mentors" class="lp-section">
    <div class="lp-section-inner">
      <div class="lp-section-head">
        <div>
          <div class="lp-mentors-header-badge">
            <i class="fa-solid fa-user-tie"></i>
            Mentor-led learning
          </div>
          <h2 class="lp-section-title">Learn from mentors who work in the industry</h2>
          <div class="lp-section-sub">
            Engineers and analysts who’ve shipped products, broken production (and fixed it), and sat on the interview panel.
          </div>
          <div class="lp-mentors-meta-row">
            <span><i class="fa-solid fa-briefcase"></i> 5–10+ years experience</span>
            <span><i class="fa-solid fa-comments"></i> 1:1 & small-group doubt support</span>
            <span><i class="fa-solid fa-code-branch"></i> Real-world project reviews</span>
          </div>
        </div>
      </div>

      <div class="lp-mentors-grid">
        <article class="lp-mentor-card lp-animate lp-hover-lift" data-lp-animate="fade-up">
          <div class="lp-mentor-content">
            <div class="lp-mentor-header">
              <div class="lp-mentor-avatar">
                <span>AK</span>
              </div>
              <div>
                <div class="lp-mentor-name">Ankit Kumar</div>
                <div class="lp-mentor-role">Senior Software Engineer – Product MNC</div>
                <div class="lp-mentor-pill-row">
                  <span class="lp-mentor-pill">
                    <i class="fa-solid fa-star"></i> 4.8 mentor rating
                  </span>
                  <span class="lp-mentor-pill">
                    <i class="fa-solid fa-users"></i> 300+ mentees
                  </span>
                </div>
              </div>
            </div>
            <div class="lp-mentor-text">
              Full-Stack and DevOps mentor. Helps learners understand how real teams manage deployments, reviews and system design.
            </div>
            <div class="lp-mentor-tags">
              <span>Node.js</span>
              <span>React</span>
              <span>System Design</span>
              <span>DevOps</span>
            </div>
          </div>
        </article>

        <article class="lp-mentor-card lp-animate lp-hover-lift lp-animate-delay-1" data-lp-animate="fade-up">
          <div class="lp-mentor-content">
            <div class="lp-mentor-header">
              <div class="lp-mentor-avatar">
                <span>SN</span>
              </div>
              <div>
                <div class="lp-mentor-name">Srestha N.</div>
                <div class="lp-mentor-role">SDET – IT Services</div>
                <div class="lp-mentor-pill-row">
                  <span class="lp-mentor-pill">
                    <i class="fa-solid fa-bug-slash"></i> 500+ test cases reviewed
                  </span>
                  <span class="lp-mentor-pill">
                    <i class="fa-solid fa-user-check"></i> Interview panelist
                  </span>
                </div>
              </div>
            </div>
            <div class="lp-mentor-text">
              Guides SDET/QA learners on test automation, frameworks, and how to present testing work confidently in interviews.
            </div>
            <div class="lp-mentor-tags">
              <span>Java</span>
              <span>Selenium</span>
              <span>API Testing</span>
              <span>CI/CD</span>
            </div>
          </div>
        </article>

        <article class="lp-mentor-card lp-animate lp-hover-lift lp-animate-delay-2" data-lp-animate="fade-up">
          <div class="lp-mentor-content">
            <div class="lp-mentor-header">
              <div class="lp-mentor-avatar">
                <span>AB</span>
              </div>
              <div>
                <div class="lp-mentor-name">Anwita B.</div>
                <div class="lp-mentor-role">Data Analyst – Startup</div>
                <div class="lp-mentor-pill-row">
                  <span class="lp-mentor-pill">
                    <i class="fa-solid fa-chart-line"></i> Domain: product analytics
                  </span>
                  <span class="lp-mentor-pill">
                    <i class="fa-solid fa-clipboard-list"></i> Case-study focused
                  </span>
                </div>
              </div>
            </div>
            <div class="lp-mentor-text">
              Helps data learners build dashboards, structure case studies, and translate raw numbers into stories recruiters care about.
            </div>
            <div class="lp-mentor-tags">
              <span>Python</span>
              <span>SQL</span>
              <span>Power BI</span>
              <span>Case Studies</span>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="reviews" class="lp-section lp-testimonials">
    <div class="lp-section-inner">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-section-title">What learners say</h2>
          <div class="lp-section-sub">Stories from students who switched careers or landed their first job.</div>
        </div>
      </div>

      <div class="lp-test-grid">
        <article class="lp-test-card lp-animate" data-lp-animate="fade-up">
          <p class="lp-test-text">
            “The Full-Stack program felt like a guided roadmap. The projects we built in class actually came up in my interview.”
          </p>
          <div class="lp-test-author">
            <div class="lp-avatar">R</div>
            <div>
              <span class="name">Rahul Mehta</span><br/>
              <span class="role">Software Engineer, Product MNC</span>
            </div>
          </div>
        </article>

        <article class="lp-test-card lp-animate lp-animate-delay-1" data-lp-animate="fade-up">
          <p class="lp-test-text">
            “Coming from a non-CS background, I was scared of code. The mentors really slowed down, explained concepts, and pushed me to build.”
          </p>
          <div class="lp-test-author">
            <div class="lp-avatar">S</div>
            <div>
              <span class="name">Srestha N.</span><br/>
              <span class="role">SDET, IT Services</span>
            </div>
          </div>
        </article>

        <article class="lp-test-card lp-animate lp-animate-delay-2" data-lp-animate="fade-up">
          <p class="lp-test-text">
            “Assignments, quizzes, and mock interviews gave me confidence. I knew what to expect in real interviews.”
          </p>
          <div class="lp-test-author">
            <div class="lp-avatar">A</div>
            <div>
              <span class="name">Anwita B.</span><br/>
              <span class="role">Data Analyst, Startup</span>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- FAQ (beautified) -->
  <section id="faq" class="lp-section lp-faq">
    <div class="lp-section-inner">
      <div class="lp-section-head">
        <div>
          <h2 class="lp-section-title">Frequently asked questions</h2>
          <div class="lp-section-sub">Quick answers to common doubts before you start.</div>
        </div>
      </div>

      <div class="lp-faq-layout">
        <!-- Left text column -->
        <div class="lp-faq-intro lp-animate" data-lp-animate="fade-up">
          <p>
            Still wondering if W3Techiez is the right fit for you? Most learners have similar questions about background,
            time commitment, and placements — we’ve answered the most important ones here.
          </p>
          <ul class="lp-faq-intro-list">
            <li>Can non-CS and career switchers succeed here?</li>
            <li>How strong is the placement support in real life?</li>
            <li>Can I manage this with college or a full-time job?</li>
          </ul>
          <div class="lp-faq-contact">
            <strong>Didn’t find your question?</strong><br/>
            Reach out to our team on WhatsApp or schedule a short counselling call — we’ll help you choose the right track.
          </div>
        </div>

        <!-- Right accordion column -->
        <div class="lp-animate lp-animate-delay-1" data-lp-animate="fade-up">
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item lp-animate" data-lp-animate="fade-up">
              <h2 class="accordion-header" id="faqOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqOneBody" aria-expanded="false" aria-controls="faqOneBody">
                  I am from a non-CS background. Can I still join?
                </button>
              </h2>
              <div id="faqOneBody" class="accordion-collapse collapse" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Yes. Many of our learners are from non-CS branches or even non-engineering backgrounds. We start from fundamentals, give extra support for basics, and focus on helping you build confidence step by step.
                </div>
              </div>
            </div>

            <div class="accordion-item lp-animate lp-animate-delay-1" data-lp-animate="fade-up">
              <h2 class="accordion-header" id="faqTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwoBody" aria-expanded="false" aria-controls="faqTwoBody">
                  Will I get placement support after finishing the program?
                </button>
              </h2>
              <div id="faqTwoBody" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  We help you with resume, LinkedIn, GitHub, mock interviews and guidance on job search strategy. We also share openings from our network and referrals wherever possible, but no responsible institute can guarantee a specific package.
                </div>
              </div>
            </div>

            <div class="accordion-item lp-animate lp-animate-delay-2" data-lp-animate="fade-up">
              <h2 class="accordion-header" id="faqThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqThreeBody" aria-expanded="false" aria-controls="faqThreeBody">
                  Can I balance this with college or a full-time job?
                </button>
              </h2>
              <div id="faqThreeBody" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Yes. Content is designed with working professionals and college students in mind. Sessions are scheduled in the evenings or weekends, and you get recordings plus structured weekly plans so you can pace your learning.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- /lp-faq-layout -->
    </div>
  </section>

  <!-- CTA -->
  <section class="lp-cta lp-animate" data-lp-animate="fade-up">
    <div class="lp-cta-inner">
      <div class="lp-cta-text">
        <h3 class="lp-cta-title">Ready to start your next chapter?</h3>
        <p class="lp-cta-sub">
          Create your free account, explore the catalog, and start learning your first module today. Pay only when you join a full program.
        </p>
      </div>
      <div class="lp-cta-actions">
        <button type="button" class="lp-btn-primary">
          Get started for free
        </button>
        <button type="button" class="lp-btn-outline">
          Talk to an advisor
        </button>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="lp-footer">
    <div class="lp-footer-inner">
      <div class="lp-footer-main">
        <div class="lp-footer-brand">
          <div class="lp-footer-brand-logo">
            <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="W3Techiez">
            <strong>W3Techiez Academy</strong>
          </div>
          <div>
            Learn modern tech skills with structured, placement-focused programs built by engineers and educators.
          </div>
          <!-- Top contact info (will be filled via JS) -->
          
            <div class="lp-nav-contact">
          <span><i class="fa-solid fa-phone"></i> +91-98765-43210</span>
          <span><i class="fa-brands fa-whatsapp"></i> WhatsApp: +91-98765-43210</span>
          <span><i class="fa-solid fa-envelope"></i> support@w3techiez.com</span>
        </div>
          
        </div>
        <div>
          <div class="lp-footer-col-title">Academy</div>
          <ul class="lp-footer-links">
            <li><a href="#courses">All courses</a></li>
            <li><a href="#categories">Career tracks</a></li>
            <li><a href="#features">Why W3Techiez</a></li>
            <li><a href="#">Corporate training</a></li>
          </ul>
        </div>
        <div>
          <div class="lp-footer-col-title">Support</div>
          <ul class="lp-footer-links">
            <li><a href="#">Help center</a></li>
            <li><a href="#">Contact us</a></li>
            <li><a href="#">Scholarships</a></li>
            <li><a href="#">Student community</a></li>
          </ul>
        </div>
        <div>
          <div class="lp-footer-col-title">Legal</div>
          <ul class="lp-footer-links">
            <li><a href="#">Terms of use</a></li>
            <li><a href="#">Privacy policy</a></li>
            <li><a href="#">Refund policy</a></li>
          </ul>
        </div>
      </div>

      <div class="lp-footer-bottom">
        <span>© {{ date('Y') }} W3Techiez. All rights reserved.</span>
        <div class="lp-footer-social">
          <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Back-to-top button -->
  <button class="lp-back-top" id="lpBackTop" aria-label="Back to top">
    <i class="fa-solid fa-arrow-up"></i>
  </button>

  <!-- Fixed batch badges -->
  {{-- <div class="lp-batch-badges">
    <div class="lp-batch-badge lp-batch-badge-main">
      <i class="fa-solid fa-bolt"></i>
      <div>
        <div class="lp-batch-badge-label">New batch: Full-Stack TypeScript</div>
        <div class="lp-batch-badge-meta">Starting 5 Dec · Limited seats</div>
      </div>
    </div>
    <div class="lp-batch-badge">
      <i class="fa-solid fa-flask"></i>
      <div>
        <div class="lp-batch-badge-label">SDET / QA Automation</div>
        <div class="lp-batch-badge-meta">Weekend cohort · Dec intake</div>
      </div>
    </div>
    <div class="lp-batch-badge">
      <i class="fa-solid fa-chart-bar"></i>
      <div>
        <div class="lp-batch-badge-label">Data Analytics</div>
        <div class="lp-batch-badge-meta">Next batch · Jan 2026</div>
      </div>
    </div>
  </div> --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  /* =========================
     Small helpers
     ========================= */
  const qs  = sel => document.querySelector(sel);
  const qsa = sel => Array.from(document.querySelectorAll(sel));

  const pageOverlay = qs('#pageOverlay');
  const showPageOverlay = () => { if (pageOverlay) pageOverlay.style.display = 'flex'; };
  const hidePageOverlay = () => { if (pageOverlay) pageOverlay.style.display = 'none'; };

  const fetchJson = async (url, opts = {}) => {
    const res = await fetch(url, { headers: { Accept: 'application/json' }, ...opts });
    if (!res.ok) {
      console.warn('[API] Failed:', url, res.status);
      return null;
    }
    try { return await res.json(); } catch { return null; }
  };

  const isOkPayload = data =>
    data && (data.status === 'success' || data.success === true) && Array.isArray(data.data);


  /* =========================
     HERO IMAGES (DB-driven stack)
     ========================= */
  let heroCards = [];
  let heroActiveIndex = 0;

  const applyHeroClasses = () => {
    if (!heroCards.length) return;
    const total = heroCards.length;
    const active = heroActiveIndex;
    const prev = (active - 1 + total) % total;
    const next = (active + 1) % total;

    heroCards.forEach((card, idx) => {
      card.classList.remove('is-active', 'is-prev', 'is-next', 'is-far');
      if (idx === active) card.classList.add('is-active');
      else if (idx === prev) card.classList.add('is-prev');
      else if (idx === next) card.classList.add('is-next');
      else card.classList.add('is-far');
    });
  };

  const renderHeroImages = images => {
    const stack = qs('#heroImageStack');
    if (!stack) return;

    stack.innerHTML = '';
    heroCards = [];
    heroActiveIndex = 0;

    if (!images.length) return;

    images.forEach((img, idx) => {
      const div = document.createElement('div');
      div.className = 'lp-hero-card-img';
      div.dataset.index = idx;
      div.innerHTML = `<img src="${img.image_url}" alt="${img.img_title || ''}">`;
      stack.appendChild(div);
      heroCards.push(div);
    });

    applyHeroClasses();
  };

  const loadHeroImages = async () => {
    const data = await fetchJson("{{ url('api/landing/hero-images/display') }}");
    if (!isOkPayload(data)) return;
    renderHeroImages(data.data);
  };

  const moveHero = dir => {
    if (heroCards.length <= 1) return;
    const total = heroCards.length;
    heroActiveIndex = (heroActiveIndex + dir + total) % total;
    applyHeroClasses();
  };

  qs('#heroPrevBtn')?.addEventListener('click', () => moveHero(-1));
  qs('#heroNextBtn')?.addEventListener('click', () => moveHero(1));

  // Auto-rotation
  setInterval(() => moveHero(1), 9000);

  loadHeroImages();


  /* =========================
     Scroll "arrival" animation
     ========================= */
  const animatedEls = qsa('[data-lp-animate], .lp-animate');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.18 });

    animatedEls.forEach(el => {
      if (!el.classList.contains('is-visible')) observer.observe(el);
    });
  } else {
    animatedEls.forEach(el => el.classList.add('is-visible'));
  }


  /* =========================
     Updates marquee
     ========================= */
  const renderUpdatesMarquee = updates => {
    const scrollEl = qs('.lp-announcement-scroll');
    if (!scrollEl) {
      console.warn('[Landing] .lp-announcement-scroll not found');
      return;
    }

    scrollEl.innerHTML = '';
    if (!updates.length) return;

    updates.forEach(item => {
      const span = document.createElement('span');
      span.className = 'lp-announcement-item';

      let container = span;

      if (item.url) {
        const link = document.createElement('a');
        link.href = item.url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        span.appendChild(link);
        container = link;
      }

      if (item.title) {
        const titleEl = document.createElement('span');
        titleEl.className = 'lp-update-title';
        titleEl.innerHTML = item.title;
        container.appendChild(titleEl);
      }

      if (item.description) {
        const descEl = document.createElement('span');
        descEl.className = 'lp-update-desc';
        descEl.innerHTML = item.description;
        container.appendChild(descEl);
      }

      scrollEl.appendChild(span);
    });

    const original = scrollEl.innerHTML;
    scrollEl.innerHTML = original + original;
  };

  const loadLandingUpdates = async () => {
    const data = await fetchJson("{{ url('api/landing/updates') }}");
    if (!isOkPayload(data)) return;
    renderUpdatesMarquee(data.data);
  };

  loadLandingUpdates();


  /* =========================
     Contacts bar
     ========================= */
  const renderContactsBar = contacts => {
    const bar = qs('#lpNavContact') || qs('.lp-nav-contact');
    if (!bar) {
      console.warn('[Landing] lpNavContact container not found');
      return;
    }

    bar.innerHTML = '';
    if (!contacts.length) {
      bar.style.display = 'none';
      return;
    }
    bar.style.display = '';

    contacts.forEach(item => {
      const span = document.createElement('span');
      span.style.display = 'inline-flex';
      span.style.alignItems = 'center';
      span.style.gap = '6px';

      if (item.icon) {
        const i = document.createElement('i');
        i.className = item.icon;
        span.appendChild(i);
      }

      span.append(item.value || '');
      bar.appendChild(span);
    });
  };

  const loadLandingContacts = async () => {
    const data = await fetchJson("{{ url('api/landing/contact') }}");
    if (!isOkPayload(data)) return;
    renderContactsBar(data.data);
  };

  loadLandingContacts();


  /* =========================
     Categories grid
     ========================= */
  const renderCategoriesGrid = categories => {
    const grid = qs('#lpCategoriesGrid') || qs('.lp-cat-grid');
    if (!grid) return console.warn('[Landing] Categories grid not found');

    grid.innerHTML = '';

    categories.forEach((cat, idx) => {
      const card = document.createElement('div');
      card.className = 'lp-cat-card lp-animate lp-hover-lift';
      card.dataset.lpAnimate = 'fade-up';

      if (idx === 1) card.classList.add('lp-animate-delay-1');
      if (idx === 2) card.classList.add('lp-animate-delay-2');
      if (idx === 3) card.classList.add('lp-animate-delay-3');
      if (idx >= 4)  card.classList.add('lp-animate-delay-4');

      const iconClass =
        cat.icon ||
        cat.icon_class ||
        'fa-solid fa-circle-dot';

      const titleText =
        cat.title ||
        cat.name ||
        'Untitled category';

      const descHtml =
        cat.description ||
        cat.meta ||
        '';

      const iconWrap = document.createElement('div');
      iconWrap.className = 'lp-cat-icon';

      const icon = document.createElement('i');
      icon.className = iconClass;
      iconWrap.appendChild(icon);

      const nameEl = document.createElement('div');
      nameEl.className = 'lp-cat-name';
      nameEl.textContent = titleText;

      const metaEl = document.createElement('div');
      metaEl.className = 'lp-cat-meta';
      metaEl.innerHTML = descHtml;

      card.appendChild(iconWrap);
      card.appendChild(nameEl);
      card.appendChild(metaEl);

      card.classList.add('is-visible');

      grid.appendChild(card);
    });
  };

  const loadLandingCategories = async () => {
    const data = await fetchJson("{{ url('api/landing/categories/display') }}");
    if (!isOkPayload(data)) {
      console.warn('[Landing] categories payload invalid', data);
      return;
    }
    renderCategoriesGrid(data.data);
  };

  loadLandingCategories();

 /* =========================
     Featured courses grid
     ========================= */
  const renderFeaturedCourses = courses => {
    const grid = qs('#lpFeaturedCoursesGrid') || qs('.lp-course-grid');
    if (!grid) {
      console.warn('[Landing] Featured courses grid not found');
      return;
    }

    grid.innerHTML = '';
    if (!courses.length) return;

    console.log('[Landing] Featured courses sample:', courses[0]); // debug

    // Base URL for public course page: /courses/{course_uuid}
    const baseCourseUrl = "{{ url('admin/courses') }}";

    courses.forEach((course, idx) => {
      let imageUrl = '';

      if (Array.isArray(course.images) && course.images.length) {
        const first = course.images[0];
        imageUrl =
          first.url ||
          first.image_url ||
          first.image_path ||
          first.path ||
          '';
      } else if (Array.isArray(course.course_images) && course.course_images.length) {
        const first = course.course_images[0];
        imageUrl =
          first.url ||
          first.image_url ||
          first.image_path ||
          first.path ||
          '';
      } else {
        imageUrl =
          course.image_url ||
          course.img_url ||
          course.image_path ||
          course.img_path ||
          course.thumbnail_url ||
          course.thumbnail ||
          course.banner_url ||
          course.banner_path ||
          '';
      }

      const finalImageUrl = imageUrl || "{{ asset('assets/media/images/web/course-placeholder.jpg') }}";

      const categoryLabel =
        course.category_title ||
        course.category_name ||
        course.category ||
        course.category_label ||
        course.track_name ||
        '';

      const titleText =
        course.title ||
        course.course_title ||
        course.name ||
        'Untitled course';

      const difficultyText =
        course.level ||
        course.difficulty ||
        course.difficulty_label ||
        'Beginner';

      const languageText =
        course.language ||
        course.language_label ||
        'English';

      const summaryHtml =
        course.short_description ||
        course.summary ||
        course.subtitle ||
        '';

      let priceText = '';
      if (course.price_amount != null && course.price_currency) {
        const amt = Number(course.price_amount);
        if (!Number.isNaN(amt)) {
          if (course.price_currency === 'INR') {
            priceText = `₹${amt.toFixed(2)}`;
          } else {
            priceText = `${course.price_currency} ${amt.toFixed(2)}`;
          }
        }
      }

      const badgeText =
        course.badge_label ||
        course.level_badge ||
        course.course_type ||
        '';

      // 🔹 Build course detail URL using UUID only
      const courseId  = course.uuid || course.course_uuid || course.id;
      const detailUrl = courseId ? `${baseCourseUrl}/${encodeURIComponent(courseId)}` : '#';

      const article = document.createElement('article');
      article.className = 'lp-course-card lp-animate';
      article.dataset.lpAnimate = 'fade-up';
      if (idx === 1) article.classList.add('lp-animate-delay-1');
      if (idx === 2) article.classList.add('lp-animate-delay-2');
      if (idx >= 3)  article.classList.add('lp-animate-delay-3');

      const thumb = document.createElement('div');
      thumb.className = 'lp-course-thumb';

      const img = document.createElement('img');
      img.src = finalImageUrl;
      img.alt = titleText;
      thumb.appendChild(img);

      if (categoryLabel) {
        const badge = document.createElement('div');
        badge.className = 'lp-category-badge';
        badge.textContent = categoryLabel;
        thumb.appendChild(badge);
      }

      const body = document.createElement('div');
      body.className = 'lp-course-body';

      const titleEl = document.createElement('div');
      titleEl.className = 'lp-course-title';
      titleEl.textContent = titleText;

      const meta = document.createElement('div');
      meta.className = 'lp-course-meta';

      const spanDiff = document.createElement('span');
      spanDiff.innerHTML = `<i class="fa fa-signal"></i> ${difficultyText}`;
      meta.appendChild(spanDiff);

      const spanLang = document.createElement('span');
      spanLang.innerHTML = `<i class="fa fa-language"></i> ${languageText}`;
      meta.appendChild(spanLang);

      const summaryEl = document.createElement('div');
      summaryEl.className = 'lp-course-summary';
      summaryEl.innerHTML = summaryHtml;

      body.appendChild(titleEl);
      body.appendChild(meta);
      body.appendChild(summaryEl);

      article.appendChild(thumb);
      article.appendChild(body);

      // 🔹 Make entire card clickable to /courses/{uuid}
      if (detailUrl && detailUrl !== '#') {
        article.classList.add('lp-course-clickable');
        article.style.cursor = 'pointer';

        article.addEventListener('click', () => {
          window.location.href = detailUrl;
        });

        article.tabIndex = 0;
        article.addEventListener('keydown', ev => {
          if (ev.key === 'Enter' || ev.key === ' ') {
            ev.preventDefault();
            window.location.href = detailUrl;
          }
        });
      }

      article.classList.add('is-visible');

      grid.appendChild(article);
    });
  };


  const loadFeaturedCourses = async () => {
    const data = await fetchJson("{{ url('api/landing/featured-courses/display') }}");

    if (!data || data.status !== 'success' || !data.data) {
      console.warn('[Landing] Featured courses payload invalid', data);
      return;
    }

    const courses = Array.isArray(data.data)
      ? data.data
      : (data.data.featured || []);

    if (!courses.length) {
      console.warn('[Landing] No featured courses to render');
      return;
    }

    renderFeaturedCourses(courses);
  };

  loadFeaturedCourses();

  /* =========================
     Back-to-top button
     ========================= */
  const backTopBtn = qs('#lpBackTop');
  if (backTopBtn) {
    window.addEventListener('scroll', () => {
      backTopBtn.classList.toggle('is-visible', window.scrollY > 260);
    });
    backTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }


  /* =========================
     Role / Login button
     ========================= */
  const getMyRole = async token => {
    if (!token) return '';
    const res = await fetch('/api/auth/my-role', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
      }
    });
    if (!res.ok) return '';
    const data = await res.json().catch(() => null);
    if (data?.status === 'success' && data?.role) {
      return String(data.role).trim().toLowerCase();
    }
    return '';
  };

  const loginBtn = qs('#lpLoginBtn');
  if (loginBtn) {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token');
    let role = '';

    const setButtonLabel = isLoggedIn => {
      loginBtn.textContent = isLoggedIn ? 'Dashboard' : 'Log in';
    };
    setButtonLabel(false);

    if (token) {
      showPageOverlay();
      getMyRole(token)
        .then(r => {
          role = r || '';
          setButtonLabel(!!role);
        })
        .catch(err => {
          console.error('[Landing] role fetch error:', err);
          role = '';
          setButtonLabel(false);
        })
        .finally(hidePageOverlay);
    }

    loginBtn.addEventListener('click', () => {
      const currentToken = sessionStorage.getItem('token') || localStorage.getItem('token');
      if (currentToken && role) {
        window.location.assign(`/${role}/dashboard`);
      } else {
        window.location.assign('/login');
      }
    });
  }
});
</script>
