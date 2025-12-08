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


  /* =========================
     Mobile burger / nav toggle
     ========================= */
  const nav       = qs('.lp-nav');
  const navToggle = qs('#lpNavToggle');

  if (nav && navToggle) {
    const updateIcon = (isOpen) => {
      const icon = navToggle.querySelector('i');
      if (!icon) return;
      if (isOpen) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-xmark');
      } else {
        icon.classList.add('fa-bars');
        icon.classList.remove('fa-xmark');
      }
    };

    navToggle.addEventListener('click', () => {
      const isOpen = nav.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      updateIcon(isOpen);
    });

    // Close menu when clicking a nav link (mobile)
    qsa('.lp-nav-links a').forEach(link => {
      link.addEventListener('click', () => {
        if (!nav.classList.contains('is-open')) return;
        nav.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
        updateIcon(false);
      });
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!nav.classList.contains('is-open')) return;
      if (!nav.contains(e.target)) {
        nav.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
        updateIcon(false);
      }
    });
  }
});
</script>
