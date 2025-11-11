{{-- resources/views/modules/viewCourse/viewCourseLayout.blade.php --}}
@php
  // Tab map (server-side include)
  $tabKey = request('tab', 'recorded');
  $tabs = [
    'recorded'    => 'modules.course.viewCourse.viewCourseTabs.recordedVideos',
    'materials'   => 'modules.course.viewCourse.viewCourseTabs.studyMaterial',
    'assignments' => 'modules.course.viewCourse.viewCourseTabs.assignments',
    'exams'       => 'modules.course.viewCourse.viewCourseTabs.exams',
    'notices'     => 'modules.course.viewCourse.viewCourseTabs.notices',
    'chat'        => 'modules.course.viewCourse.viewCourseTabs.chat',
  ];
  $tabKey    = array_key_exists($tabKey, $tabs) ? $tabKey : 'recorded';
  $tabPartial = $tabs[$tabKey];
  $moduleUuid = request('module'); // optional
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>View Course</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  {{-- Paint theme ASAP to avoid white flash --}}
  <script>
    (function(){
      try{
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const mode = saved || (prefersDark ? 'dark' : 'light');
        if (mode === 'dark') document.documentElement.classList.add('theme-dark');
      }catch(e){}
    })();
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

  <style>
    /* ===== Page/Layout ===================================================== */
    html, body { height: 100%; }
    body{ background: var(--bg-body); color: var(--text-color); min-height: 100dvh; }

    .vc-wrap{ max-width: 1180px; margin: 18px auto 40px; padding: 0 14px; }
    .vc-grid{
      display:grid;
      grid-template-columns: 360px minmax(0,1fr);
      gap:16px;
      min-height: calc(100dvh - 90px);
    }
    @media (max-width: 992px){ .vc-grid{ grid-template-columns: 1fr; } }

    /* ===== Left column (sticky card) ====================================== */
    .vc-aside .panel{ padding: 12px; position: sticky; top: 16px; }
    .vc-head-left{
      display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:8px;
    }
    .vc-title{ font-family: var(--font-head); font-weight:700; color:var(--ink); margin:0; font-size:1.12rem; }
    .vc-status{ font-size: 11px; }

    .vc-cover{
      width:100%; aspect-ratio: 16/10;
      border:1px solid var(--line-strong); border-radius:12px; overflow:hidden;
      background:#f6f3fb; display:flex; align-items:center; justify-content:center;
    }
    html.theme-dark .vc-cover{ background:#0e1930; }
    .vc-cover img{ width:100%; height:100%; object-fit:cover; }

    .vc-thumbs{ display:flex; gap:8px; overflow:auto; padding:8px 2px 2px; }
    .vc-thumb{
      width:56px; height:56px; border:1px solid var(--line-strong); border-radius:10px;
      overflow:hidden; flex:0 0 auto; background:#fff; cursor:pointer;
    }
    .vc-thumb img{ width:100%; height:100%; object-fit:cover; }
    html.theme-dark .vc-thumb{ background:#0f172a; }

    .vc-chips{ display:flex; flex-wrap:wrap; gap:8px; margin: 6px 0 4px; }
    .vc-chip{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px;
      border:1px solid var(--line-strong); border-radius:999px; background:#fff; font-size:var(--fs-13);
    }
    html.theme-dark .vc-chip{ background:#0f172a; }

    .vc-search{ position:relative; margin-top:8px; }
    .vc-search input{ padding-left:34px; height:40px; border-radius:12px; }
    .vc-search i{ position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#8a8593; }

    .vc-modules{
      margin-top:10px; display:flex; flex-direction:column; gap:8px;
      max-height: calc(100dvh - 420px); overflow: auto;
    }
    .vc-module{
      border:1px solid var(--line-strong); border-radius:12px; background:var(--surface);
      padding:10px; cursor:pointer; transition: var(--transition);
    }
    .vc-module:hover{ transform: translateY(-1px); }
    .vc-module .t{ font-weight:600; color:var(--ink); }
    .vc-module .d{ font-size: var(--fs-13); color: var(--muted-color); }
    .vc-module.active{ outline:2px solid color-mix(in oklab, var(--accent-color) 55%, transparent); }

    .vc-empty{ border:1px dashed var(--line-strong); border-radius: 10px; padding: 16px; text-align:center; color: var(--muted-color); }

    /* ===== Right column (module header + tabs) ============================ */
    .vc-main .panel{ padding: 14px; min-height: calc(100% - 0px); }
    .vc-top{
      display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:8px;
    }
    .vc-top .title{ font-family:var(--font-head); font-weight:700; color:var(--ink); margin:0; font-size:1.18rem; }
    .vc-top .sub{ color: var(--muted-color); }

    .vc-price{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border:1px solid var(--line-strong);
      border-radius:999px; background:#fff; font-size:var(--fs-13);
    }
    html.theme-dark .vc-price{ background:#0f172a; }

    .tabbar{
      margin-top:6px; border-bottom:1px solid var(--line-strong); padding-bottom:4px;
      display:flex; flex-wrap:wrap; gap:6px;
    }
    .tabbar .nav-link{
      border:0; border-radius:10px; padding:8px 12px;
      color: var(--muted-color); background: transparent;
    }
    .tabbar .nav-link i{ width:16px; text-align:center; margin-right:6px; }
    .tabbar .nav-link:hover{ background: rgba(2,6,23,.04); }
    html.theme-dark .tabbar .nav-link:hover{ background:#0c172d; }
    .tabbar .nav-link.active{
      color: var(--ink); background: color-mix(in oklab, var(--accent-color) 12%, transparent);
      box-shadow: var(--shadow-1);
    }
  </style>
</head>
<body>
  <main class="vc-wrap">
    <div class="vc-grid">
      {{-- ================= LEFT: Overview + Modules ================= --}}
      <aside class="vc-aside">
        <div class="panel shadow-1">
          <div class="vc-head-left">
            <div>
              <h2 class="vc-title" id="courseTitle">Course</h2>
              <div class="text-muted" id="courseShort">—</div>
            </div>
            <span class="badge badge-soft-primary vc-status" id="courseStatus">—</span>
          </div>

          <div class="vc-cover rounded-1 shadow-1 mb-2" id="mediaCover">
            <i class="fa-regular fa-image"></i>
          </div>
          <div class="vc-thumbs" id="mediaThumbs"></div>

          <div class="divider my-2"></div>

          <div class="vc-chips" id="courseChips"></div>

          <div class="vc-search">
            <i class="fa fa-search"></i>
            <input id="moduleSearch" type="text" class="form-control" placeholder="Search modules...">
          </div>

          <div class="vc-modules" id="modulesList">
            <div class="vc-empty" id="modulesEmpty" style="display:none">No modules found.</div>
          </div>
        </div>
      </aside>

      {{-- ================= RIGHT: Module Header + Tabs ================= --}}
      <section class="vc-main">
        <div class="panel shadow-1">
          <div class="vc-top">
            <div>
              <h1 class="title" id="moduleTitle">Select a module</h1>
              <div class="sub" id="moduleShort">Pick a module from the left to see its content.</div>
            </div>
            <div id="pricePill" class="vc-price" style="display:none"></div>
          </div>

          @php
            $self = url()->current();
            $mParam = $moduleUuid ? ('&module='.urlencode($moduleUuid)) : '';
          @endphp
          <ul class="nav tabbar" id="vcTabs">
            <li class="nav-item"><a class="nav-link {{ $tabKey==='recorded'?'active':'' }}"    href="{{ $self }}?tab=recorded{{ $mParam }}"><i class="fa-regular fa-circle-play"></i>Recorded Sessions</a></li>
            <li class="nav-item"><a class="nav-link {{ $tabKey==='materials'?'active':'' }}"   href="{{ $self }}?tab=materials{{ $mParam }}"><i class="fa-regular fa-folder-open"></i>Study Material</a></li>
            <li class="nav-item"><a class="nav-link {{ $tabKey==='assignments'?'active':'' }}" href="{{ $self }}?tab=assignments{{ $mParam }}"><i class="fa-regular fa-square-check"></i>Assignments</a></li>
            <li class="nav-item"><a class="nav-link {{ $tabKey==='exams'?'active':'' }}"       href="{{ $self }}?tab=exams{{ $mParam }}"><i class="fa-regular fa-file-lines"></i>Exams</a></li>
            <li class="nav-item"><a class="nav-link {{ $tabKey==='notices'?'active':'' }}"     href="{{ $self }}?tab=notices{{ $mParam }}"><i class="fa-regular fa-bell"></i>Notices</a></li>
            <li class="nav-item"><a class="nav-link {{ $tabKey==='chat'?'active':'' }}"        href="{{ $self }}?tab=chat{{ $mParam }}"><i class="fa-regular fa-comments"></i>Chat</a></li>
          </ul>

          <div id="tabContent" class="mt-3">
            @includeIf($tabPartial, ['moduleUuid' => $moduleUuid])
          </div>
        </div>
      </section>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // ===== Derive {uuid} from /admin/courses/{uuid}/view
    const deriveCourseKey = () => {
      const parts = location.pathname.split('/').filter(Boolean);
      const last = parts.at(-1)?.toLowerCase();
      if (last === 'view' && parts.length >= 2) return parts.at(-2);
      return parts.at(-1);
    };
    const courseKey = deriveCourseKey();

    // ===== Elements
    const el = {
      title:  document.getElementById('courseTitle'),
      short:  document.getElementById('courseShort'),
      status: document.getElementById('courseStatus'),
      cover:  document.getElementById('mediaCover'),
      thumbs: document.getElementById('mediaThumbs'),
      chips:  document.getElementById('courseChips'),
      mSearch:document.getElementById('moduleSearch'),
      mList:  document.getElementById('modulesList'),
      mEmpty: document.getElementById('modulesEmpty'),
      mTitle: document.getElementById('moduleTitle'),
      mShort: document.getElementById('moduleShort'),
      price:  document.getElementById('pricePill'),
    };

    // ===== Token for protected API (admin/student/instructor later)
    const tok = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    const auth = tok ? { 'Authorization': 'Bearer '+tok } : {};

    // ===== Keep ?module in URL and on tab links
    const qs = new URLSearchParams(location.search);
    let selectedModuleUuid = qs.get('module') || null;

    const updateQueryParam = (key, val) => {
      const usp = new URLSearchParams(location.search);
      if (!val) usp.delete(key); else usp.set(key, val);
      history.pushState({}, '', `${location.pathname}?${usp.toString()}`);
      document.querySelectorAll('#vcTabs .nav-link').forEach(a => {
        const u = new URL(a.href, location.origin);
        if (val) u.searchParams.set('module', val); else u.searchParams.delete('module');
        a.href = u.toString();
      });
    };

    // ===== Small bus (tabs can listen later)
    const bus = { emit(evt, detail){ document.dispatchEvent(new CustomEvent(evt, { detail })); } };
    window.__VCBUS__ = bus;

    // ===== Render helpers
    const setCover = (m) => {
      if (!m || !m.url) return;
      el.cover.innerHTML = `<img src="${m.url}" alt="cover">`;
    };

    const renderThumbs = (gallery=[]) => {
      el.thumbs.innerHTML = '';
      (gallery || []).forEach(m => {
        if (m.type !== 'image') return;
        const d = document.createElement('div');
        d.className = 'vc-thumb'; d.title = 'Cover';
        d.innerHTML = `<img src="${m.url}" alt="">`;
        d.addEventListener('click', () => setCover(m));
        el.thumbs.appendChild(d);
      });
    };

    const setModuleHeader = (m) => {
      if (!m){
        el.mTitle.textContent = 'Select a module';
        el.mShort.textContent = 'Pick a module from the left to see its content.';
        return;
      }
      el.mTitle.textContent = m.title || 'Module';
      el.mShort.textContent = m.short_description || '';
    };

    const renderModules = (modules=[]) => {
      el.mList.innerHTML = '';
      el.mEmpty.style.display = modules.length ? 'none' : '';
      const frag = document.createDocumentFragment();

      modules.forEach(m => {
        const div = document.createElement('div');
        div.className = 'vc-module';
        div.dataset.uuid = m.uuid;
        div.innerHTML = `
          <div class="t">${m.title ?? 'Untitled module'}</div>
          <div class="d">${m.short_description ?? ''}</div>
        `;
        div.addEventListener('click', () => {
          selectedModuleUuid = m.uuid;
          [...el.mList.children].forEach(c => c.classList.toggle('active', c.dataset.uuid === selectedModuleUuid));
          setModuleHeader(m);
          updateQueryParam('module', selectedModuleUuid);
          bus.emit('vc:module-changed', { moduleUuid: selectedModuleUuid, module: m });
        });
        frag.appendChild(div);
      });
      el.mList.appendChild(frag);

      // Preselect
      let chosen = modules.find(x => x.uuid === selectedModuleUuid);
      if (!chosen && modules.length){ chosen = modules[0]; selectedModuleUuid = chosen.uuid; updateQueryParam('module', selectedModuleUuid); }
      if (chosen){
        [...el.mList.children].forEach(c => c.classList.toggle('active', c.dataset.uuid === selectedModuleUuid));
        setModuleHeader(chosen);
        bus.emit('vc:module-changed', { moduleUuid: selectedModuleUuid, module: chosen });
      }
    };

    const wireSearch = (modules=[]) => {
      el.mSearch.addEventListener('input', (e) => {
        const q = (e.target.value || '').toLowerCase();
        [...el.mList.children].forEach(li => {
          const m = modules.find(x => x.uuid === li.dataset.uuid);
          const hay = `${m?.title ?? ''} ${m?.short_description ?? ''}`.toLowerCase();
          li.style.display = hay.includes(q) ? '' : 'none';
        });
      });
    };

    // ===== Fetch course view payload
    const api = `/api/courses/${encodeURIComponent(courseKey)}/view`;
    fetch(api, { headers: { 'Accept':'application/json', ...auth } })
      .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
      .then(({ data }) => {
        const { course, pricing, media, modules } = data;

        el.title.textContent  = course.title || 'Course';
        el.short.textContent  = course.short_description || '';
        el.status.textContent = course.status || '—';
        el.status.className   = 'badge ' + (
          course.status === 'published' ? 'badge-soft-success' :
          course.status === 'draft'     ? 'badge-soft-warning' :
                                          'badge-soft-info'
        );

        if (pricing){
          el.price.style.display = 'inline-flex';
          el.price.innerHTML = pricing.is_free
            ? `<i class="fa fa-badge-check"></i> Free`
            : (pricing.has_discount
                ? `<i class="fa fa-tags"></i> ${pricing.currency} ${pricing.final}
                   <span class="text-muted" style="text-decoration:line-through">&nbsp;${pricing.currency} ${pricing.original}</span>`
                : `<i class="fa fa-tag"></i> ${pricing.currency} ${pricing.original}`);
        }

        // chips
        const chips = [];
        if (course.difficulty)     chips.push(`<span class="vc-chip"><i class="fa fa-signal"></i>${course.difficulty}</span>`);
        if (course.language)       chips.push(`<span class="vc-chip"><i class="fa fa-language"></i>${course.language}</span>`);
        if (course.duration_hours) chips.push(`<span class="vc-chip"><i class="fa fa-clock"></i>${course.duration_hours} hrs</span>`);
        el.chips.innerHTML = chips.join(' ');

        if (media?.cover) setCover(media.cover);
        renderThumbs(media?.gallery || []);

        const sorted = (modules || []).slice().sort((a,b)=> (a.order_no ?? 0) - (b.order_no ?? 0));
        renderModules(sorted);
        wireSearch(sorted);
      })
      .catch(err => {
        console.error('viewCourse.fetch', err);
        el.title.textContent = 'Failed to load course';
        el.short.textContent = 'Please check API or authentication.';
      });
  });
  </script>
</body>
</html>
