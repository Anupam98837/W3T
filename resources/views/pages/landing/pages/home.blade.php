
  @include('pages.landing.components.header')
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
      <a href="/categories/all" class="lp-section-link">
        View all Categories <i class="fa fa-arrow-right ms-1"></i>
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
        <a href="/courses/all" class="lp-section-link">
          Browse courses <i class="fa fa-arrow-right ms-1"></i>
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
  <!-- <section id="mentors" class="lp-section">
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
  </section> -->

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
        <button type="button" class="lp-btn-primary" style="display:none">
          Get started for free
        </button>
        <button type="button" class="lp-btn-outline">
          Talk to an advisor
        </button>
      </div>
    </div>
  </section>
  @include('pages.landing.components.footer')
