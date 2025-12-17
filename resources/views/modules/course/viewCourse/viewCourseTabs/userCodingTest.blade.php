{{-- resources/views/modules/codingTest/userCodingTest.blade.php --}}

@php
  // Try to detect batch id from request or injected variable
  $batchId =
      request('batch')
      ?? request('batch_id')
      ?? (isset($batch) ? ($batch->id ?? $batch->uuid ?? null) : null);

  // Where your full coding test runner page lives (adjust if your route is different)
  $codingTestUrl = url('/coding-test');
@endphp

<div class="ct-shell" id="codingTestsRoot"
     data-batch-id="{{ $batchId }}"
     data-api-base="{{ url('/api') }}"
     data-test-url="{{ $codingTestUrl }}">

  <div class="ct-topbar">
    <div class="ct-title">
      <div class="ct-ico"><i class="fa-solid fa-code"></i></div>
      <div>
        <div class="ct-h">Coding Tests</div>
        <div class="ct-sub">Assigned questions, attempts, and quick actions.</div>
      </div>
    </div>

    <div class="ct-actions">
      <div class="ct-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input id="ctSearch" type="text" placeholder="Search questions, tags, difficulty…">
      </div>

      <select id="ctDifficulty" class="ct-select">
        <option value="">All difficulties</option>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>

      <button id="ctRefresh" class="ct-btn ct-btn-ghost" type="button">
        <i class="fa-solid fa-rotate"></i><span>Refresh</span>
      </button>
    </div>
  </div>

  <div class="ct-stats" id="ctStats" style="display:none;">
    <div class="ct-stat" style="display:none;" >
      <div class="k">Total</div><div class="v" id="stTotal">0</div>
    </div>
    <div class="ct-stat" style="display:none;">
      <div class="k">Assigned</div><div class="v" id="stAssigned">0</div>
    </div>
    <div class="ct-stat" style="display:none;">
      <div class="k">Your Attempts</div><div class="v" id="stAttempts">0</div>
    </div>
    <div class="ct-stat" style="display:none;">
      <div class="k">Role</div><div class="v" id="stRole">—</div>
    </div>
  </div>

  <div class="ct-card">
    <div class="ct-card-hd">
      <div class="ct-card-hl">
        <span class="ct-dot"></span>
        <span id="ctListTitle">Loading…</span>
      </div>

      <div class="ct-card-hr">
        <span class="ct-pill" id="ctHintPill" style="display:none;"></span>
      </div>
    </div>

    <div class="ct-card-bd">
      {{-- loaders / alerts --}}
      <div class="ct-alert ct-alert-warn" id="ctNoToken" style="display:none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
          <div class="t">Token not found</div>
          <div class="d">Please login again. Session storage key <b>token</b> is missing.</div>
        </div>
      </div>

      <div class="ct-alert ct-alert-warn" id="ctNoBatch" style="display:none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
          <div class="t">Batch id missing</div>
          <div class="d">Pass <b>?batch=ID</b> (or <b>batch_id</b>) or provide <b>$batch</b> to this view.</div>
        </div>
      </div>

      <div class="ct-skeleton" id="ctSkeleton">
        <div class="r"></div><div class="r"></div><div class="r"></div><div class="r"></div><div class="r"></div>
      </div>

      {{-- Desktop table --}}
      <div class="ct-tablewrap d-none d-lg-block" id="ctTableWrap" style="display:none;">
        <table class="ct-table">
          <thead>
          <tr>
            <th style="width:40%;">Question</th>
            <th style="width:14%;">Difficulty</th>
            <th style="width:18%;">Tags</th>
            <th style="width:14%;">Attempts</th>
            <th style="width:14%; text-align:right;">Actions</th>
          </tr>
          </thead>
          <tbody id="ctTbody"></tbody>
        </table>
      </div>

      {{-- Mobile cards --}}
      <div class="ct-cards d-lg-none" id="ctCards" style="display:none;"></div>

      {{-- empty --}}
      <div class="ct-empty" id="ctEmpty" style="display:none;">
        <div class="ct-empty-ico"><i class="fa-regular fa-folder-open"></i></div>
        <div class="ct-empty-t">No questions found</div>
        <div class="ct-empty-d">Try clearing filters or refresh the list.</div>
      </div>
    </div>
  </div>

  {{-- tiny toast --}}
  <div class="ct-toast" id="ctToast" style="display:none;"></div>
</div>

<style>
  /* =========================
     Coding Tests UI (namespaced)
     Uses main.css tokens (no overrides)
     ========================= */

  .ct-shell{max-width:1180px;margin:14px auto 36px;padding:0 10px}
  .ct-topbar{display:flex;gap:12px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap}
  .ct-title{display:flex;gap:12px;align-items:center}
  .ct-ico{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;
    background:linear-gradient(135deg, rgba(149,30,170,.14), rgba(201,79,240,.10));
    border:1px solid var(--line-strong);
    box-shadow:var(--shadow-1)
  }
  .ct-ico i{color:var(--primary-color);font-size:18px}
  .ct-h{font-family: "Poppins", ui-sans-serif; font-weight:700; letter-spacing:.2px; font-size:18px; color:var(--text-color)}
  .ct-sub{font-size:12.5px;color:var(--muted-color);margin-top:2px}

  .ct-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .ct-search{display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:14px;
    border:1px solid var(--line-strong); background:var(--surface); min-width:260px;
    box-shadow:var(--shadow-1)
  }
  .ct-search i{color:var(--muted-color)}
  .ct-search input{border:none;outline:none;background:transparent;color:var(--text-color);width:100%;font-size:13px}
  .ct-select{padding:10px 12px;border-radius:14px;border:1px solid var(--line-strong);background:var(--surface);
    color:var(--text-color);font-size:13px;box-shadow:var(--shadow-1)
  }

  .ct-btn{border:none;border-radius:14px;padding:10px 12px;font-weight:600;font-size:13px;display:inline-flex;gap:8px;align-items:center}
  .ct-btn span{line-height:1}
  .ct-btn-ghost{background:var(--surface);color:var(--text-color);border:1px solid var(--line-strong);box-shadow:var(--shadow-1)}
  .ct-btn-primary{background:var(--primary-color);color:#fff;box-shadow:var(--shadow-2)}
  .ct-btn-danger{background:rgba(220,53,69,.12);color:#dc3545;border:1px solid rgba(220,53,69,.25)}
  .ct-btn:disabled{opacity:.6;cursor:not-allowed}

  .ct-stats{margin-top:12px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
  @media(max-width: 992px){ .ct-stats{grid-template-columns:repeat(2,1fr)} }
  .ct-stat{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;padding:10px 12px;box-shadow:var(--shadow-1)}
  .ct-stat .k{font-size:12px;color:var(--muted-color)}
  .ct-stat .v{font-size:16px;font-weight:800;color:var(--text-color);margin-top:2px}

  .ct-card{margin-top:12px;background:var(--surface);border:1px solid var(--line-strong);border-radius:18px;box-shadow:var(--shadow-2);overflow:hidden}
  .ct-card-hd{padding:12px 14px;border-bottom:1px solid var(--line-strong);display:flex;align-items:center;justify-content:space-between;gap:10px}
  .ct-card-hl{display:flex;gap:10px;align-items:center;font-weight:800;color:var(--text-color)}
  .ct-dot{width:10px;height:10px;border-radius:99px;background:var(--primary-color);box-shadow:0 0 0 4px rgba(149,30,170,.12)}
  .ct-pill{padding:6px 10px;border-radius:999px;border:1px solid var(--line-strong);background:rgba(149,30,170,.08);color:var(--text-color);font-size:12px;font-weight:700}
  .ct-card-bd{padding:12px 14px}

  .ct-alert{display:flex;gap:10px;align-items:flex-start;padding:12px 12px;border-radius:16px;border:1px solid var(--line-strong);background:rgba(255,193,7,.10);color:var(--text-color)}
  .ct-alert i{margin-top:2px}
  .ct-alert .t{font-weight:900}
  .ct-alert .d{font-size:13px;color:var(--muted-color);margin-top:2px}

  .ct-skeleton .r{height:46px;border-radius:14px;background:rgba(125,125,125,.12);border:1px solid rgba(125,125,125,.18);margin-bottom:10px}
  .ct-skeleton .r:nth-child(2){height:54px}
  .ct-skeleton .r:nth-child(4){height:52px}

  .ct-table{width:100%;border-collapse:separate;border-spacing:0 10px}
  .ct-table thead th{font-size:12px;color:var(--muted-color);font-weight:800;padding:0 10px 8px}
  .ct-table tbody tr{background:var(--surface);border:1px solid var(--line-strong)}
  .ct-table tbody td{padding:12px 10px;vertical-align:middle;border-top:1px solid var(--line-strong);border-bottom:1px solid var(--line-strong)}
  .ct-table tbody tr td:first-child{border-left:1px solid var(--line-strong);border-top-left-radius:14px;border-bottom-left-radius:14px}
  .ct-table tbody tr td:last-child{border-right:1px solid var(--line-strong);border-top-right-radius:14px;border-bottom-right-radius:14px}

  .ct-qtitle{font-weight:900;color:var(--text-color);line-height:1.2}
  .ct-qmeta{font-size:12.5px;color:var(--muted-color);margin-top:3px;display:flex;gap:10px;flex-wrap:wrap}
  .ct-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:900;border:1px solid var(--line-strong);background:rgba(0,0,0,.03)}
  .ct-badge.easy{background:rgba(25,135,84,.10);border-color:rgba(25,135,84,.20)}
  .ct-badge.medium{background:rgba(255,193,7,.10);border-color:rgba(255,193,7,.25)}
  .ct-badge.hard{background:rgba(220,53,69,.10);border-color:rgba(220,53,69,.25)}

  .ct-tags{display:flex;gap:6px;flex-wrap:wrap}
  .ct-tag{font-size:12px;padding:5px 9px;border-radius:999px;border:1px solid var(--line-strong);background:rgba(94,21,112,.06);color:var(--text-color);font-weight:800}

  .ct-attemptBox{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .ct-num{width:92px;min-width:92px;padding:9px 10px;border-radius:14px;border:1px solid var(--line-strong);background:var(--surface);
    color:var(--text-color);font-weight:800;font-size:13px
  }
  .ct-dd{min-width:180px;padding:9px 10px;border-radius:14px;border:1px solid var(--line-strong);background:var(--surface);
    color:var(--text-color);font-weight:700;font-size:13px
  }

  .ct-toggle{display:inline-flex;align-items:center;gap:8px}
  .ct-switch{width:44px;height:26px;border-radius:999px;background:rgba(125,125,125,.20);border:1px solid var(--line-strong);position:relative;cursor:pointer}
  .ct-switch::after{content:"";width:20px;height:20px;border-radius:999px;background:#fff;position:absolute;top:2px;left:2px;transition:all .18s ease;box-shadow:0 6px 14px rgba(0,0,0,.12)}
  .ct-switch.on{background:rgba(149,30,170,.28);border-color:rgba(149,30,170,.35)}
  .ct-switch.on::after{left:22px;background:var(--primary-color)}
  .ct-tlbl{font-size:12.5px;color:var(--muted-color);font-weight:900}

  .ct-cards{display:grid;gap:10px}
  .ct-cardItem{border:1px solid var(--line-strong);border-radius:18px;padding:12px;background:var(--surface);box-shadow:var(--shadow-1)}
  .ct-cardItem .top{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}
  .ct-cardItem .mid{margin-top:10px}
  .ct-cardItem .bot{margin-top:12px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center}

  .ct-empty{padding:18px 10px;text-align:center}
  .ct-empty-ico{font-size:28px;color:var(--muted-color)}
  .ct-empty-t{font-weight:900;color:var(--text-color);margin-top:6px}
  .ct-empty-d{font-size:13px;color:var(--muted-color);margin-top:3px}

  .ct-toast{position:fixed;right:14px;bottom:14px;z-index:9999;
    background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;
    box-shadow:var(--shadow-3);padding:10px 12px;color:var(--text-color);font-weight:800;max-width:320px
  }
</style>

<script>
(function(){
  const root = document.getElementById('codingTestsRoot');
  if(!root) return;

  const API_BASE  = root.dataset.apiBase || '';
  const TEST_URL  = root.dataset.testUrl || '';
  const BATCH_ID  = root.dataset.batchId || '';
  const token     = sessionStorage.getItem('token');

  const el = (id) => document.getElementById(id);

  const $skeleton = el('ctSkeleton');
  const $tableWrap= el('ctTableWrap');
  const $cards    = el('ctCards');
  const $tbody    = el('ctTbody');
  const $empty    = el('ctEmpty');
  const $noToken  = el('ctNoToken');
  const $noBatch  = el('ctNoBatch');

  const $search   = el('ctSearch');
  const $diff     = el('ctDifficulty');
  const $refresh  = el('ctRefresh');

  const $stats    = el('ctStats');
  const stTotal   = el('stTotal');
  const stAssigned= el('stAssigned');
  const stAttempts= el('stAttempts');
  const stRole    = el('stRole');

  const $listTitle= el('ctListTitle');
  const $hintPill = el('ctHintPill');

  const $toast    = el('ctToast');

  let RAW = null;
  let ROLE = '';
  let CAN_MANAGE = false;
  let LIST = [];       // normalized questions
  let FILTERED = [];

  function toast(msg){
    $toast.textContent = msg;
    $toast.style.display = 'block';
    clearTimeout($toast._t);
    $toast._t = setTimeout(()=> $toast.style.display='none', 2200);
  }

  function esc(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function normDifficulty(d){
    d = String(d ?? '').toLowerCase().trim();
    if(['easy','beginner'].includes(d)) return 'easy';
    if(['medium','intermediate','mid'].includes(d)) return 'medium';
    if(['hard','advanced','difficult'].includes(d)) return 'hard';
    return d || '';
  }

  function pickArray(obj){
    // try common response shapes
    return obj?.questions || obj?.data || obj?.items || obj?.list || [];
  }

  function detectRole(obj){
    return obj?.actor?.role || obj?.role || obj?.user?.role || '';
  }

  function detectCanManage(obj, role){
    if(typeof obj?.can_manage === 'boolean') return obj.can_manage;
    if(typeof obj?.canManage === 'boolean') return obj.canManage;
    return ['superadmin','admin','instructor'].includes(String(role||'').toLowerCase());
  }

  function getAssignedFlag(q){
  if (q?.assigned === true || q?.assigned === 1) return true;
  if (q?.is_assigned === true || q?.is_assigned === 1) return true;
  if (q?.assigned_to_batch === true || q?.assigned_to_batch === 1) return true;
  return false;
}


  function normalizeQuestion(q){
    const uuid = q?.uuid || q?.question_uuid || q?.questionUuid || q?.id;
    const title= q?.title || q?.name || q?.question_title || ('Question ' + (uuid||''));
    const diff = normDifficulty(q?.difficulty);
    const tags = Array.isArray(q?.tags) ? q.tags : (typeof q?.tags === 'string' ? q.tags.split(',').map(t=>t.trim()).filter(Boolean) : []);
    const assigned = getAssignedFlag(q);

    // attempts related (student)
    const attemptsCount = Number(q?.attempts_count ?? q?.my_attempts_count ?? q?.attempt_count ?? 0) || 0;

    // allowed attempts (instructor)
    const maxAttempts = Number(q?.max_attempts ?? q?.allowed_attempts ?? q?.attempt_limit ?? 1) || 1;

    return {
      raw: q,
      uuid, title, diff, tags,
      assigned,
      attemptsCount,
      maxAttempts
    };
  }

  function applyFilters(){
    const term = ($search.value || '').trim().toLowerCase();
    const d = ($diff.value || '').trim().toLowerCase();

    FILTERED = LIST.filter(q=>{
      if(d && q.diff !== d) return false;

      if(term){
        const hay = (q.title + ' ' + q.tags.join(' ') + ' ' + q.diff).toLowerCase();
        if(!hay.includes(term)) return false;
      }
      return true;
    });

    render();
  }

  function badge(d){
    const cls = d ? `ct-badge ${esc(d)}` : 'ct-badge';
    const label = d ? d.toUpperCase() : '—';
    return `<span class="${cls}"><i class="fa-solid fa-signal"></i>${esc(label)}</span>`;
  }

  function tagsHTML(tags){
    if(!tags || !tags.length) return `<span class="ct-tag">—</span>`;
    return tags.slice(0,4).map(t=> `<span class="ct-tag">${esc(t)}</span>`).join('');
  }

  function attemptsCell(q){
    // Instructor: numeric input first, then toggle
    if(CAN_MANAGE){
      const v = Math.max(1, Math.min(999, Number(q.maxAttempts)||1));
      return `
        <div class="ct-attemptBox">
          <input class="ct-num" type="number" min="1" max="999"
                 value="${v}"
                 data-act="attempts-input"
                 data-uuid="${esc(q.uuid)}"
                 aria-label="Max attempts">
          <span class="ct-tlbl">max</span>
        </div>
      `;
    }

    // Student: attempts dropdown + count
    return `
      <div class="ct-attemptBox">
        <select class="ct-dd" data-act="attempts-dd" data-uuid="${esc(q.uuid)}">
          <option value="">Attempts (${q.attemptsCount})</option>
        </select>
        <button class="ct-btn ct-btn-ghost" type="button"
                data-act="load-attempts" data-uuid="${esc(q.uuid)}">
          <i class="fa-solid fa-list"></i><span>Load</span>
        </button>
      </div>
    `;
  }

  function actionsCell(q){
    if(CAN_MANAGE){
      const on = q.assigned ? 'on' : '';
      const lbl= q.assigned ? 'Assigned' : 'Unassigned';
      return `
        <div style="display:flex;justify-content:flex-end;gap:10px;align-items:center;flex-wrap:wrap;">
          <div class="ct-toggle">
            <div class="ct-switch ${on}" role="switch" tabindex="0"
                 aria-checked="${q.assigned ? 'true':'false'}"
                 data-act="toggle-assign" data-uuid="${esc(q.uuid)}"></div>
            <div class="ct-tlbl">${esc(lbl)}</div>
          </div>
        </div>
      `;
    }

    // Student
    return `
      <div style="display:flex;justify-content:flex-end;gap:10px;align-items:center;flex-wrap:wrap;">
        <button class="ct-btn ct-btn-primary" type="button"
                data-act="start" data-uuid="${esc(q.uuid)}">
          <i class="fa-solid fa-play"></i><span>Start</span>
        </button>
        <button class="ct-btn ct-btn-ghost" type="button"
                data-act="open-selected" data-uuid="${esc(q.uuid)}">
          <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Open</span>
        </button>
      </div>
    `;
  }

  function rowHTML(q){
    return `
      <tr data-uuid="${esc(q.uuid)}">
        <td>
          <div class="ct-qtitle">${esc(q.title)}</div>
          <div class="ct-qmeta">
            <span><i class="fa-regular fa-id-badge"></i> ${esc(q.uuid || '—')}</span>
            ${q.assigned ? `<span><i class="fa-solid fa-circle-check"></i> Assigned</span>` : `<span><i class="fa-regular fa-circle"></i> Not assigned</span>`}
          </div>
        </td>
        <td>${badge(q.diff)}</td>
        <td><div class="ct-tags">${tagsHTML(q.tags)}</div></td>
        <td>${attemptsCell(q)}</td>
        <td style="text-align:right;">${actionsCell(q)}</td>
      </tr>
    `;
  }

  function cardHTML(q){
    return `
      <div class="ct-cardItem" data-uuid="${esc(q.uuid)}">
        <div class="top">
          <div>
            <div class="ct-qtitle">${esc(q.title)}</div>
            <div class="ct-qmeta" style="margin-top:4px">
              <span>${q.assigned ? 'Assigned' : 'Not assigned'}</span>
              <span>•</span>
              <span>${esc(q.uuid || '')}</span>
            </div>
          </div>
          <div>${badge(q.diff)}</div>
        </div>

        <div class="mid">
          <div class="ct-tags">${tagsHTML(q.tags)}</div>
        </div>

        <div class="bot">
          <div>${attemptsCell(q)}</div>
          <div>${actionsCell(q)}</div>
        </div>
      </div>
    `;
  }

  function render(){
    $tbody.innerHTML = '';
    $cards.innerHTML = '';

    if(!FILTERED.length){
      $tableWrap.style.display = 'none';
      $cards.style.display = 'none';
      $empty.style.display = 'block';
      return;
    }

    $empty.style.display = 'none';
    $tableWrap.style.display = '';
    $cards.style.display = '';

    $tbody.innerHTML = FILTERED.map(rowHTML).join('');
    $cards.innerHTML = FILTERED.map(cardHTML).join('');
  }

  async function api(path, opts={}){
    const res = await fetch(API_BASE + path, {
      ...opts,
      headers: {
        'Accept':'application/json',
        'Content-Type':'application/json',
        ...(opts.headers || {}),
        'Authorization': 'Bearer ' + token
      }
    });
    const text = await res.text();
    let data = null;
    try { data = text ? JSON.parse(text) : null; } catch(e){ data = { raw:text }; }

    if(!res.ok){
      const msg = data?.message || data?.error || ('Request failed: ' + res.status);
      throw new Error(msg);
    }
    return data;
  }

  function setLoading(on){
    $skeleton.style.display = on ? 'block' : 'none';
    $tableWrap.style.display = on ? 'none' : '';
    $cards.style.display = on ? 'none' : '';
    $empty.style.display = 'none';
  }

  function updateStats(){
    const total = LIST.length;
    const assigned = LIST.filter(x=>x.assigned).length;
    const attempts = LIST.reduce((a,x)=>a + (Number(x.attemptsCount)||0), 0);

    stTotal.textContent = String(total);
    stAssigned.textContent = String(assigned);
    stAttempts.textContent = String(attempts);
    stRole.textContent = ROLE ? ROLE : '—';

    $stats.style.display = 'grid';
  }

  async function loadIndex(){
    if(!token){
      $noToken.style.display = 'flex';
      setLoading(false);
      return;
    }
    if(!BATCH_ID){
      $noBatch.style.display = 'flex';
      setLoading(false);
      return;
    }

    $noToken.style.display = 'none';
    $noBatch.style.display = 'none';
    setLoading(true);

    try{
      RAW = await api(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions`, { method:'GET' });

      ROLE = detectRole(RAW);
      CAN_MANAGE = detectCanManage(RAW, ROLE);

      const arr = pickArray(RAW);
      LIST = Array.isArray(arr) ? arr.map(normalizeQuestion) : [];

      // If student & API returns all questions, optionally auto-hide unassigned:
      if(!CAN_MANAGE){
        // show only assigned by default
        LIST = LIST.filter(q => q.assigned === true || RAW?.only_assigned === true);
      }

      $listTitle.textContent = CAN_MANAGE ? 'Manage Batch Coding Questions' : 'Your Assigned Coding Questions';

      $hintPill.style.display = 'inline-flex';
      $hintPill.textContent = CAN_MANAGE
        ? 'Set max attempts, then toggle Assign'
        : 'Load attempts dropdown when needed';

      updateStats();

      FILTERED = LIST.slice();
      setLoading(false);
      applyFilters();

    }catch(err){
      setLoading(false);
      $listTitle.textContent = 'Failed to load';
      toast(err.message || 'Failed to load');
    }
  }

  async function assignQuestion(uuid, maxAttempts){
    const payload = {
      // send multiple keys to be compatible with your controller handling
      question_uuid: uuid,
      questionUuids: [uuid],
      question_uuids: [uuid],
      max_attempts: maxAttempts,
      allowed_attempts: maxAttempts,
      attempt_limit: maxAttempts
    };

    await api(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/assign`, {
      method:'POST',
      body: JSON.stringify(payload)
    });
  }

  async function unassignQuestion(uuid){
    await api(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/${encodeURIComponent(uuid)}`, {
      method:'DELETE'
    });
  }

  async function loadAttempts(uuid, selectEl){
    const data = await api(`/batches/${encodeURIComponent(BATCH_ID)}/coding-questions/${encodeURIComponent(uuid)}/my-attempts`, { method:'GET' });

    const attempts = data?.attempts || data?.data || data?.items || [];
    selectEl.innerHTML = `<option value="">Attempts (${attempts.length})</option>` +
      attempts.map((a, idx)=>{
        const id = a?.uuid || a?.attempt_uuid || a?.id || '';
        const st = (a?.status || a?.verdict || a?.result || '').toString();
        const sc = (a?.score ?? a?.marks ?? a?.points ?? '');
        const ts = (a?.submitted_at || a?.created_at || a?.updated_at || '');
        const label = `#${idx+1}${st ? ' • ' + st : ''}${sc!=='' ? ' • ' + sc : ''}${ts ? ' • ' + ts : ''}`;
        return `<option value="${esc(id)}">${esc(label)}</option>`;
      }).join('');
  }

  async function startQuestion(uuid){
    // We do not hard-assume your JudgeController payload,
    // so we send common keys; your backend can pick what it needs.
    const payload = { batch_id: BATCH_ID, batch: BATCH_ID, question_uuid: uuid, questionUuid: uuid };

    const data = await api(`/judge/start`, { method:'POST', body: JSON.stringify(payload) });

    // try to detect attempt uuid returned
    const attemptUuid =
      data?.attempt_uuid || data?.attemptUuid || data?.attempt?.uuid || data?.uuid || data?.data?.attempt_uuid || '';

    // open coding test page (adjust if your route expects different query keys)
    const url = new URL(TEST_URL, window.location.origin);
    url.searchParams.set('batch', BATCH_ID);
    url.searchParams.set('question', uuid);
    if(attemptUuid) url.searchParams.set('attempt', attemptUuid);

    window.location.href = url.toString();
  }

  // events
  $refresh.addEventListener('click', loadIndex);
  $search.addEventListener('input', applyFilters);
  $diff.addEventListener('change', applyFilters);

  // delegate clicks for both desktop table and mobile cards
  root.addEventListener('click', async (e)=>{
    const btn = e.target.closest('[data-act]');
    if(!btn) return;

    const act = btn.dataset.act;
    const uuid= btn.dataset.uuid;

    try{
      if(act === 'toggle-assign'){
        if(!CAN_MANAGE) return;

        const q = LIST.find(x=>x.uuid === uuid);
        if(!q) return;

        // read current attempts input value from the row/card (attempts input first)
        const input = root.querySelector(`input[data-act="attempts-input"][data-uuid="${CSS.escape(uuid)}"]`);
        let maxA = input ? Number(input.value || 1) : Number(q.maxAttempts||1);
        if(!Number.isFinite(maxA) || maxA < 1) maxA = 1;
        if(maxA > 999) maxA = 999;

        btn.style.pointerEvents = 'none';

        if(!q.assigned){
          await assignQuestion(uuid, maxA);
          q.assigned = true;
          q.maxAttempts = maxA;
          toast('Assigned');
        }else{
          await unassignQuestion(uuid);
          q.assigned = false;
          toast('Unassigned');
        }

        // re-render while keeping filters
        applyFilters();
        updateStats();

        btn.style.pointerEvents = '';
      }

      if(act === 'load-attempts'){
        const sel = root.querySelector(`select[data-act="attempts-dd"][data-uuid="${CSS.escape(uuid)}"]`);
        if(!sel) return;

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i><span>Loading</span>`;

        await loadAttempts(uuid, sel);

        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-list"></i><span>Load</span>`;
        toast('Attempts loaded');
      }

      if(act === 'open-selected'){
        // open selected attempt if chosen, else open test page with batch+question
        const sel = root.querySelector(`select[data-act="attempts-dd"][data-uuid="${CSS.escape(uuid)}"]`);
        const attemptUuid = sel ? sel.value : '';

        const url = new URL(TEST_URL, window.location.origin);
        url.searchParams.set('batch', BATCH_ID);
        url.searchParams.set('question', uuid);
        if(attemptUuid) url.searchParams.set('attempt', attemptUuid);

        window.open(url.toString(), '_blank');
      }

      if(act === 'start'){
        await startQuestion(uuid);
      }

    }catch(err){
      toast(err.message || 'Action failed');
      // restore any disabled button content if needed
      if(act === 'load-attempts'){
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-list"></i><span>Load</span>`;
      }
      if(act === 'toggle-assign'){
        btn.style.pointerEvents = '';
      }
    }
  });

  // keep attempts input synced to local list (no api call until toggle)
  root.addEventListener('input', (e)=>{
    const inp = e.target.closest('input[data-act="attempts-input"]');
    if(!inp) return;
    const uuid = inp.dataset.uuid;
    const q = LIST.find(x=>x.uuid === uuid);
    if(!q) return;

    let v = Number(inp.value || 1);
    if(!Number.isFinite(v) || v < 1) v = 1;
    if(v > 999) v = 999;
    q.maxAttempts = v;
  });

  // init
  loadIndex();
})();
</script>
