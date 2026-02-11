{{-- resources/views/modules/metaTags/manageMetaTags.blade.php --}}
@section('title','Meta Tags')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
  .mtg-wrap{max-width:1200px;margin:16px auto 54px;padding:0 6px;overflow:visible}
  .mtg-panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:12px;}
  .mtg-card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;}
  .mtg-card .card-header{background:transparent;border-bottom:1px solid var(--line-soft)}
  .loading-overlay{position:fixed; inset:0;background:rgba(0,0,0,.45);display:none;justify-content:center;align-items:center;z-index:9999;backdrop-filter:blur(2px)}
  .loading-overlay.is-show{display:flex}

  .count-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color);font-weight:900;font-size:12px;white-space:nowrap}
  .text-mini{font-size:12px;color:var(--muted-color)}
  .hr-soft{border-color:var(--line-soft)!important}

  .mtg-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
  .mtg-toolbar .left{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .mtg-toolbar .right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

  .mtg-formrow{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
  .mtg-formrow .fg{min-width:240px;flex:1}
  .mtg-formrow label{font-weight:900;font-size:12px;color:var(--muted-color);margin-bottom:6px}
  .mtg-formrow input, .mtg-formrow select{width:100%;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);border-radius:12px;padding:10px 12px;outline:none;}
  .mtg-formrow input:focus, .mtg-formrow select:focus{box-shadow:0 0 0 .2rem rgba(201,75,80,.35);border-color:color-mix(in oklab, var(--primary-color) 45%, var(--line-strong));}

  .mtg-table-wrap{border:1px solid var(--line-soft);border-radius:14px;overflow:auto;max-width:100%}
  .mtg-table{width:100%;min-width:1080px;margin:0}
  .mtg-table thead th{position:sticky;top:0;background:var(--surface);z-index:3;border-bottom:1px solid var(--line-strong);font-size:12px;text-transform:uppercase;letter-spacing:.04em}
  .mtg-table th,.mtg-table td{vertical-align:top;padding:12px 12px;border-bottom:1px solid var(--line-soft)}
  .mtg-table tbody tr:hover{background:var(--page-hover)}

  /* ✅ give table inputs/selects the same UI */
  .mtg-table select, .mtg-table input{
    width:100%;
    border:1px solid var(--line-strong);
    background:var(--surface);
    color:var(--ink);
    border-radius:12px;
    padding:10px 12px;
    outline:none;
  }
  .mtg-table select:focus, .mtg-table input:focus{
    box-shadow:0 0 0 .2rem rgba(201,75,80,.35);
    border-color:color-mix(in oklab, var(--primary-color) 45%, var(--line-strong));
  }

  .mtg-row-actions{display:flex;gap:8px;align-items:center;justify-content:flex-end}
  .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);box-shadow:var(--shadow-sm);cursor:pointer;transition:transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease, color .15s ease;}
  .icon-btn:hover{transform:translateY(-1px)}
  .icon-btn.danger{border-color:rgba(239,68,68,.45)}
  .icon-btn.danger:hover{box-shadow:0 10px 22px rgba(239,68,68,.10)}

  .code-pill{display:inline-flex;align-items:center;gap:8px;border:1px dashed var(--line-soft);border-radius:12px;padding:8px 10px;background:color-mix(in oklab, var(--primary-color) 6%, var(--surface));}
  .code-pill code{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:12px; color:var(--ink);white-space:nowrap;}
  .code-copy{width:30px;height:30px;border-radius:10px;border:1px solid var(--line-strong);background:var(--surface);cursor:pointer;}

  .empty-state{text-align:center;padding:42px 20px}
  .empty-state i{font-size:48px;color:var(--muted-color);margin-bottom:16px;opacity:.6}
  .empty-state .title{font-weight:950;color:var(--ink);margin-bottom:8px}
  .empty-state .subtitle{font-size:14px;color:var(--muted-color)}

  .row-error{background: rgba(239,68,68,.08) !important;}
  .row-error td{border-bottom-color: rgba(239,68,68,.30) !important;}
  .row-error .mtg-err{color:#b91c1c;font-weight:900;font-size:12px;margin-top:6px}

  .id-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;background:rgba(16,185,129,.14);color:#059669;border:1px solid rgba(16,185,129,.35);font-weight:900;font-size:12px;white-space:nowrap;}

  /* ✅ Tabs minor polish (keeps bootstrap working, does not override global theme) */
  .mtg-tabs .nav-link{border-radius:999px;font-weight:900}
  .mtg-tabs .nav-link.active{background:var(--primary-color);color:#fff}
  .mtg-tabs .nav-link:not(.active){background:color-mix(in oklab, var(--primary-color) 8%, var(--surface));color:var(--ink);border:1px solid var(--line-soft)}
  .mtg-tabpane{margin-top:12px}

  @media (max-width: 768px){
    .mtg-formrow .fg{min-width: 100%;}
    .mtg-table{min-width:980px}
  }
</style>
@endpush

@section('content')
@php
  $mtUid = 'mtg_' . \Illuminate\Support\Str::random(8);

  $apiMetaTypes        = url('/api/meta-tags/types');
  $apiMetaList         = url('/api/meta-tags');
  $apiMetaBulkSave     = url('/api/meta-tags/bulk');
  $apiMetaDeleteById   = url('/api/meta-tags');
@endphp

<div class="mtg-wrap" id="{{ $mtUid }}">

  {{-- ✅ Tabs --}}
  <ul class="nav nav-pills mtg-tabs mb-3" id="{{ $mtUid }}_tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="{{ $mtUid }}_tabbtn_meta" data-bs-toggle="tab"
              data-bs-target="#{{ $mtUid }}_tab_meta" type="button" role="tab"
              aria-controls="{{ $mtUid }}_tab_meta" aria-selected="true">
        <i class="fa fa-tags me-1"></i> Meta Tags
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="{{ $mtUid }}_tabbtn_media" data-bs-toggle="tab"
              data-bs-target="#{{ $mtUid }}_tab_media" type="button" role="tab"
              aria-controls="{{ $mtUid }}_tab_media" aria-selected="false">
        <i class="fa fa-photo-film me-1"></i> Media
      </button>
    </li>
  </ul>

  <div class="tab-content">
    {{-- ===================== TAB 1: META TAGS ===================== --}}
    <div class="tab-pane fade show active mtg-tabpane" id="{{ $mtUid }}_tab_meta" role="tabpanel" aria-labelledby="{{ $mtUid }}_tabbtn_meta">

      <div id="globalLoading" class="loading-overlay">
        @include('partials.overlay')
      </div>

      <div class="mtg-panel mb-3">
        <div class="mtg-toolbar">
          <div class="left">
            <div class="fw-semibold"><i class="fa fa-tags me-2"></i>Meta Tags Manager</div>
            <span class="count-badge" id="tagBadge">—</span>
          </div>
          <div class="right">
            <button id="btnLoad" class="btn btn-light">
              <i class="fa fa-download me-1"></i>Load
            </button>
            <button id="btnAddRow" class="btn btn-light">
              <i class="fa fa-plus me-1"></i>Add tag
            </button>
            <button id="btnSaveAll" class="btn btn-primary">
              <i class="fa fa-floppy-disk me-1"></i>Save all
            </button>
          </div>
        </div>

        <div class="mtg-formrow mt-3">
          <div class="fg">
            <label>Page link</label>
            <input id="pageLink" type="text" placeholder="e.g. / , /home , /courses/123 , https://yourdomain.com/page" />
            <div class="text-mini mt-2">
              Tip: Enter a page link → click <b>Load</b> to fetch existing tags → add/edit multiple tags → <b>Save all</b>.
            </div>
          </div>

          <div class="fg" style="min-width:260px;max-width:360px;">
            <label>Quick presets</label>
            <select id="presetSelect">
              <option value="">— Select preset to add —</option>
              <option value="seo_basic">SEO Basic (description + robots)</option>
              <option value="social_basic">Social Basic (og:title/desc + twitter card)</option>
              <option value="charset_viewport">Charset + Viewport</option>
            </select>
            <div class="text-mini mt-2">Adds multiple tags at once for this page link.</div>
          </div>
        </div>

        <div class="text-mini mt-3" id="summaryText">—</div>
      </div>

      <div class="card mtg-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="fw-semibold"><i class="fa fa-layer-group me-2"></i>Tags for this Page</div>
          <div class="small text-muted">Meta Tag Type → Attribute → Content. Charset disables Attribute and defaults Content to UTF-8 (editable).</div>
        </div>

        <div class="card-body">
          <div id="emptyState" class="empty-state">
            <i class="fa fa-circle-info"></i>
            <div class="title">No tags loaded</div>
            <div class="subtitle">Enter a page link and click <b>Load</b>, or click <b>Add tag</b> to start.</div>
          </div>

          <div id="tableWrap" class="mtg-table-wrap" style="display:none;">
            <table class="table mtg-table">
              <thead>
                <tr>
                  <th style="width:200px;">Meta Tag Type</th>
                  <th style="width:260px;">Attribute</th>
                  <th>Content</th>
                  <th style="width:360px;">Preview</th>
                  <th style="width:170px;text-align:right;">Actions</th>
                </tr>
              </thead>
              <tbody id="tbodyRows"></tbody>
            </table>
          </div>

          <div class="text-mini mt-3">
            <i class="fa fa-shield-halved me-1" style="opacity:.8"></i>
            Saved tags are stored against the <b>page link</b>. You can add/update multiple tags at once.
          </div>
        </div>
      </div>

    </div>

    {{-- ===================== TAB 2: MEDIA ===================== --}}
    <div class="tab-pane fade mtg-tabpane" id="{{ $mtUid }}_tab_media" role="tabpanel" aria-labelledby="{{ $mtUid }}_tabbtn_media">
      @include('modules.media.manageMedia')
    </div>
  </div>

</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const PAGE_KEY = "__META_TAGS_PAGE_V1__";
  if (window[PAGE_KEY]) return;
  window[PAGE_KEY] = true;

  const $ = (id) => document.getElementById(id);
  const rootId = @json($mtUid);

  const API = {
    types: () => @json($apiMetaTypes),
    list: (pageLink) => @json($apiMetaList) + '?page_link=' + encodeURIComponent(pageLink || ''),
    saveBulk: () => @json($apiMetaBulkSave),
    delete: (id) => @json($apiMetaDeleteById) + '/' + encodeURIComponent(String(id)),
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }
  function cleanStr(v){
    return (v === null || v === undefined) ? '' : String(v).trim();
  }
  function pickArray(v){
    if (Array.isArray(v)) return v;
    if (v === null || v === undefined) return [];
    if (typeof v === 'string'){ try{ const d = JSON.parse(v); return Array.isArray(d) ? d : []; }catch(_){ return []; } }
    return [];
  }
  function normalizeList(js){
    if (!js) return [];
    if (Array.isArray(js)) return js;
    if (Array.isArray(js.data)) return js.data;
    if (Array.isArray(js.types)) return js.types; // ✅
    if (js.data && Array.isArray(js.data.data)) return js.data.data;
    if (Array.isArray(js.items)) return js.items;
    if (Array.isArray(js.tags)) return js.tags;
    return [];
  }

  const token = () => (sessionStorage.getItem('token') || localStorage.getItem('token') || '');
  function authHeaders(extra={}){
    return Object.assign({
      'Authorization': 'Bearer ' + token(),
      'Accept': 'application/json'
    }, extra);
  }
  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }
  function showLoading(on){ $('globalLoading')?.classList.toggle('is-show', !!on); }

  const toastOk  = $('toastSuccess') ? new bootstrap.Toast($('toastSuccess')) : null;
  const toastErr = $('toastError') ? new bootstrap.Toast($('toastError')) : null;
  const ok  = (m) => { $('toastSuccessText').textContent = m || 'Done'; toastOk && toastOk.show(); };
  const err = (m) => { $('toastErrorText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

  // ✅ UPDATED: feature image properties + lots of commonly used meta tags/properties
  const FALLBACK_TYPE_DEFS = [
    { typeKey:'charset',   label:'Charset',                attrName:'charset',   attributes:[] },

    { typeKey:'standard',  label:'Standard (name)',        attrName:'name',      attributes:[
      'description','keywords','robots','googlebot','bingbot',
      'viewport','theme-color','color-scheme','referrer','format-detection',
      'application-name','generator',
      'apple-mobile-web-app-title','apple-mobile-web-app-capable','apple-mobile-web-app-status-bar-style',
      'mobile-web-app-capable',
      'google-site-verification','msvalidate.01','yandex-verification','baidu-site-verification',
      'facebook-domain-verification','p:domain_verify','norton-safeweb-site-verification',
      'author','publisher','copyright','rating','distribution','revisit-after','language',
      'HandheldFriendly','MobileOptimized',
      'msapplication-TileColor','msapplication-config','msapplication-navbutton-color','msapplication-starturl'
    ]},

    { typeKey:'opengraph', label:'Open Graph (property)',  attrName:'property',  attributes:[
      'og:title','og:description','og:url','og:type','og:site_name','og:locale',
      'og:locale:alternate','og:determiner','og:updated_time','og:see_also',

      // ✅ Feature Image (OG)
      'og:image','og:image:secure_url','og:image:url',
      'og:image:type','og:image:width','og:image:height','og:image:alt',

      // Audio / Video
      'og:video','og:video:secure_url','og:video:type','og:video:width','og:video:height',
      'og:audio','og:audio:secure_url','og:audio:type',

      // Facebook
      'fb:app_id','fb:admins',

      // Article
      'article:published_time','article:modified_time','article:expiration_time',
      'article:author','article:section','article:tag',

      // Product
      'product:price:amount','product:price:currency'
    ]},

    { typeKey:'twitter',   label:'Twitter (name)',         attrName:'name',      attributes:[
      'twitter:card','twitter:title','twitter:description','twitter:site','twitter:creator',

      // ✅ Feature Image (Twitter)
      'twitter:image','twitter:image:alt',

      // URL helpers
      'twitter:url','twitter:domain',

      // Player
      'twitter:player','twitter:player:width','twitter:player:height',

      // App cards
      'twitter:app:name:iphone','twitter:app:id:iphone','twitter:app:url:iphone',
      'twitter:app:name:ipad','twitter:app:id:ipad','twitter:app:url:ipad',
      'twitter:app:name:googleplay','twitter:app:id:googleplay','twitter:app:url:googleplay',

      // Summary labels
      'twitter:label1','twitter:data1','twitter:label2','twitter:data2'
    ]},

    { typeKey:'http',      label:'HTTP-Equiv',             attrName:'http-equiv',attributes:[
      'refresh','content-security-policy','x-ua-compatible',
      'cache-control','expires','pragma','default-style'
    ]},
  ];

  const state = {
    typeDefs: [...FALLBACK_TYPE_DEFS],
    pageLink: '',
    rows: [], // { _k, id?, typeKey, attribute, content, _err? }
    loadedOnce: false,
  };

  function randKey(){
    return Math.random().toString(16).slice(2) + '_' + Date.now().toString(16);
  }

  function getTypeDef(typeKey){
    return state.typeDefs.find(t => String(t.typeKey) === String(typeKey)) || null;
  }

  // ✅ map older server styles -> UI typeKey
  function normalizeTypeKey(rawType, rawAttr){
    const t = cleanStr(rawType).toLowerCase();
    const a = cleanStr(rawAttr).toLowerCase();

    if (t === 'charset' || a === 'charset') return 'charset';
    if (t === 'standard') return 'standard';
    if (t === 'opengraph' || t === 'open_graph' || t === 'og') return 'opengraph';
    if (t === 'twitter') return 'twitter';
    if (t === 'http' || t === 'http-equiv' || t === 'http_equiv') return 'http';

    // old style storing meta attribute name as type
    if (t === 'name') return (a.startsWith('twitter:') ? 'twitter' : 'standard');
    if (t === 'property') return 'opengraph';

    // infer from attribute prefix
    if (a.startsWith('og:') || a.startsWith('fb:') || a.startsWith('article:') || a.startsWith('product:')) return 'opengraph';
    if (a.startsWith('twitter:')) return 'twitter';

    return t || 'standard';
  }

  function buildMetaPreview(row){
    const def = getTypeDef(row.typeKey);
    if (!def) return '';

    const content = (row.content ?? '').toString().trim();

    if (def.attrName === 'charset' || row.typeKey === 'charset'){
      const cs = content || 'UTF-8';
      return `<meta charset="${esc(cs)}">`;
    }

    const attrVal = (row.attribute ?? '').toString().trim();
    if (!attrVal) return '';

    return `<meta ${def.attrName}="${esc(attrVal)}" content="${esc(content)}">`;
  }

  function updateTopSummary(){
    const link = (state.pageLink || '').trim();
    const cnt = state.rows.length;
    $('tagBadge').textContent = (cnt ? `Tags: ${cnt}` : '—');

    if (!link){
      $('summaryText').textContent = 'Enter a page link to manage its meta tags.';
      return;
    }

    $('summaryText').textContent =
      state.loadedOnce
        ? `Loaded ${cnt} tag(s) for: ${link}`
        : `Ready to manage tags for: ${link}`;
  }

  function syncEmptyState(){
    const hasRows = state.rows.length > 0;
    $('emptyState').style.display = hasRows ? 'none' : '';
    $('tableWrap').style.display = hasRows ? '' : 'none';
  }

  function setRowError(k, msg){
    const r = state.rows.find(x => x._k === k);
    if (r) r._err = msg || '';
  }
  function clearAllErrors(){
    state.rows.forEach(r => r._err = '');
  }

  // ✅ Track last focused content input so media URL can be inserted (optional helper)
  let lastContentInput = null;
  document.addEventListener('focusin', (e) => {
    const inp = e.target?.closest?.('input.js-content[data-k]');
    if (inp) lastContentInput = inp;
  });

  // ✅ Global helper: Media module (or any script) can call:
  // window.__MTG_INSERT_MEDIA_URL('https://.../image.jpg')
  window.__MTG_INSERT_MEDIA_URL = function(url){
    const u = (url ?? '').toString().trim();
    if (!u) return;

    // ensure meta tab visible (optional)
    try{
      const metaBtn = document.getElementById(@json($mtUid . '_tabbtn_meta'));
      if (metaBtn && metaBtn.classList && !metaBtn.classList.contains('active')){
        metaBtn.click();
      }
    }catch(_){}

    const target = lastContentInput || document.querySelector(`#${CSS.escape(rootId)} input.js-content[data-k]`);
    if (target){
      target.focus();
      target.value = u;
      target.dispatchEvent(new Event('input', { bubbles:true }));
      ok('Inserted media URL');
    } else {
      ok('Copied URL (paste into Content)');
    }
  };

  function renderRows(){
    const tb = $('tbodyRows');
    tb.innerHTML = state.rows.map((row) => {
      const def = getTypeDef(row.typeKey);
      const isCharset = (row.typeKey === 'charset' || def?.attrName === 'charset');
      const attrs = def ? (def.attributes || []) : [];
      const preview = buildMetaPreview(row);

      const rowAttr = cleanStr(row.attribute);
      const hasAttrInList = rowAttr && attrs.includes(rowAttr);

      // ✅ if attribute exists but not in list, inject it so dropdown shows selected
      const customOpt = (!isCharset && rowAttr && !hasAttrInList)
        ? `<option value="${esc(rowAttr)}" selected>${esc(rowAttr)}</option>`
        : '';

      const errMsg = row._err ? `<div class="mtg-err"><i class="fa fa-triangle-exclamation me-1"></i>${esc(row._err)}</div>` : '';

      return `
        <tr class="${row._err ? 'row-error' : ''}" data-k="${esc(row._k)}">
          <td>
            <select class="js-type" data-k="${esc(row._k)}">
              ${state.typeDefs.map(t => `
                <option value="${esc(t.typeKey)}" ${String(t.typeKey)===String(row.typeKey) ? 'selected' : ''}>${esc(t.label)}</option>
              `).join('')}
            </select>
            ${row.id ? `<div class="mt-2"><span class="id-badge"><i class="fa fa-check"></i>ID #${esc(String(row.id))}</span></div>` : ``}
          </td>

          <td>
            <select class="js-attr" data-k="${esc(row._k)}" ${isCharset ? 'disabled' : ''}>
              <option value="">— Select attribute —</option>
              ${customOpt}
              ${attrs.map(a => `<option value="${esc(a)}" ${String(a)===String(rowAttr) ? 'selected' : ''}>${esc(a)}</option>`).join('')}
            </select>
            ${isCharset ? `<div class="text-mini mt-2"><i class="fa fa-circle-info me-1"></i>Charset has no attribute dropdown.</div>` : ``}
          </td>

          <td>
            <input class="js-content" data-k="${esc(row._k)}" type="text"
              placeholder="${isCharset ? 'UTF-8' : 'Enter content'}"
              value="${esc(row.content ?? '')}"
            />
            ${isCharset ? `<div class="text-mini mt-2"><i class="fa fa-wand-magic-sparkles me-1"></i>Auto set to UTF-8 (editable).</div>` : ``}
            ${errMsg}
          </td>

          <td>
            <div class="code-pill">
              <code class="js-preview" data-k="${esc(row._k)}">${esc(preview || '—')}</code>
              <button class="code-copy js-copy" type="button" data-k="${esc(row._k)}" title="Copy">
                <i class="fa-regular fa-copy"></i>
              </button>
            </div>
            <div class="text-mini mt-2">
              <i class="fa fa-link me-1" style="opacity:.8"></i>
              Attribute name: <b>${esc(def?.attrName || '—')}</b>
            </div>
          </td>

          <td>
            <div class="mtg-row-actions">
              <button class="icon-btn js-dup" type="button" data-k="${esc(row._k)}" title="Duplicate row">
                <i class="fa fa-clone"></i>
              </button>
              ${row.id ? `
                <button class="icon-btn danger js-del-db" type="button" data-k="${esc(row._k)}" title="Delete from database">
                  <i class="fa fa-trash"></i>
                </button>
              ` : `
                <button class="icon-btn danger js-del-local" type="button" data-k="${esc(row._k)}" title="Remove row">
                  <i class="fa fa-xmark"></i>
                </button>
              `}
            </div>
          </td>
        </tr>
      `;
    }).join('');

    syncEmptyState();
    updateTopSummary();
  }

  function addRow(partial={}){
    const k = randKey();

    const row = Object.assign({
      _k: k,
      id: null,
      typeKey: 'standard',
      attribute: 'description',
      content: '',
      _err: '',
    }, partial);

    if (row.typeKey === 'charset' && !cleanStr(row.content)){
      row.content = 'UTF-8';
      row.attribute = '';
    }

    state.rows.push(row);
    renderRows();
  }

  function duplicateRow(k){
    const r = state.rows.find(x => x._k === k);
    if (!r) return;
    addRow({
      id: null,
      typeKey: r.typeKey,
      attribute: r.attribute,
      content: r.content,
    });
  }

  function removeLocalRow(k){
    state.rows = state.rows.filter(x => x._k !== k);
    renderRows();
  }

  function setPageLink(v){
    state.pageLink = cleanStr(v);
    updateTopSummary();
  }

  function validateAll(){
    clearAllErrors();

    const link = cleanStr(state.pageLink);
    if (!link){
      err('Page link is required.');
      return false;
    }
    if (!state.rows.length){
      err('Add at least one meta tag.');
      return false;
    }

    let firstBadKey = null;

    state.rows.forEach(r => {
      const def = getTypeDef(r.typeKey);
      const isCharset = (r.typeKey === 'charset' || def?.attrName === 'charset');

      const content = cleanStr(r.content);
      const attrVal = cleanStr(r.attribute);

      if (isCharset){
        if (!content){
          setRowError(r._k, 'Charset value is required (e.g., UTF-8).');
          if (!firstBadKey) firstBadKey = r._k;
        }
        return;
      }

      if (!attrVal){
        setRowError(r._k, 'Select an attribute.');
        if (!firstBadKey) firstBadKey = r._k;
        return;
      }
      if (!content){
        setRowError(r._k, 'Content is required.');
        if (!firstBadKey) firstBadKey = r._k;
        return;
      }
    });

    renderRows();

    if (firstBadKey){
      const tr = document.querySelector(`#${CSS.escape(rootId)} tr[data-k="${CSS.escape(firstBadKey)}"]`);
      tr?.scrollIntoView({ behavior:'smooth', block:'center' });
      err('Please fix highlighted rows.');
      return false;
    }

    return true;
  }

  function buildPayload(){
    const link = cleanStr(state.pageLink);
    return {
      page_link: link,
      tags: state.rows.map(r => ({
        id: r.id || null,
        tag_type: cleanStr(r.typeKey),
        attribute: cleanStr(r.attribute),
        content: cleanStr(r.content),
      }))
    };
  }

  async function loadTypes(){
    try{
      const res = await fetchWithTimeout(API.types(), { headers: authHeaders() }, 15000);
      if (res.status === 401){ window.location.href='/'; return; }

      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load meta types');

      const list = normalizeList(js);
      if (Array.isArray(list) && list.length){
        state.typeDefs = list.map(t => ({
          typeKey: t.typeKey ?? t.key ?? t.type ?? t.tag_type ?? 'standard',
          label: t.label ?? t.name ?? 'Type',
          attrName: t.attrName ?? t.attr_name ?? t.attribute_name ?? (String((t.typeKey||t.key||'standard')) === 'charset' ? 'charset' : 'name'),
          attributes: pickArray(t.attributes ?? t.attribute_values ?? t.items ?? []),
        }));
      } else {
        state.typeDefs = [...FALLBACK_TYPE_DEFS];
      }
    }catch(_){
      state.typeDefs = [...FALLBACK_TYPE_DEFS];
    }
  }

  // ✅ FIXED: maps BOTH UI keys (attribute/content) AND DB keys (tag_attribute/tag_attribute_value)
  function mapServerTagToRow(t){
    const rawAttr =
      cleanStr(t.attribute ?? t.tag_attribute ?? t.attr_value ?? t.key ?? '');

    const rawType =
      cleanStr(t.tag_type ?? t.type ?? t.meta_tag_type ?? t.typeKey ?? t.key ?? '');

    const typeKey = normalizeTypeKey(rawType, rawAttr);

    let attribute =
      cleanStr(t.attribute ?? t.tag_attribute ?? t.attr_value ?? t.key ?? '');

    let content =
      cleanStr(t.content ?? t.tag_attribute_value ?? t.value ?? t.charset ?? '');

    // heuristic: if attribute empty but content looks like an attribute value
    if (!attribute && content){
      const def = getTypeDef(typeKey);
      const allowed = def?.attributes || [];
      if (allowed.includes(content)){
        attribute = content;
        content = '';
      }
    }

    // charset rule
    if (typeKey === 'charset'){
      attribute = '';
      if (!content) content = 'UTF-8';
    }

    return {
      _k: randKey(),
      id: t.id ?? t.meta_tag_id ?? null,
      typeKey: typeKey || 'standard',
      attribute,
      content,
      _err: '',
    };
  }

  async function loadTagsForLink(link){
    if (!link){ err('Page link is required.'); return; }

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.list(link), { headers: authHeaders() }, 20000);
      if (res.status === 401){ window.location.href='/'; return; }

      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load tags');

      const list = normalizeList(js);

      state.rows = (list || []).map(mapServerTagToRow);
      state.loadedOnce = true;

      renderRows();
      ok('Loaded');
    }catch(ex){
      err(ex?.name === 'AbortError' ? 'Request timed out' : (ex?.message || 'Load failed'));
    }finally{
      showLoading(false);
    }
  }

  async function saveAll(){
    if (!validateAll()) return;

    const payload = buildPayload();

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.saveBulk(), {
        method:'POST',
        headers: authHeaders({ 'Content-Type':'application/json' }),
        body: JSON.stringify(payload)
      }, 25000);

      const js = await res.json().catch(()=> ({}));
      if (res.status === 401){ window.location.href='/'; return; }

      if (!res.ok || js?.success === false){
        throw new Error(js?.message || 'Save failed');
      }

      ok('Saved');
      await loadTagsForLink(state.pageLink);

    }catch(ex){
      err(ex?.name === 'AbortError' ? 'Request timed out' : (ex?.message || 'Save failed'));
    }finally{
      showLoading(false);
    }
  }

  async function deleteRowFromDb(k){
    const r = state.rows.find(x => x._k === k);
    if (!r || !r.id){ removeLocalRow(k); return; }

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.delete(r.id), {
        method:'DELETE',
        headers: authHeaders()
      }, 20000);

      const js = await res.json().catch(()=> ({}));
      if (res.status === 401){ window.location.href='/'; return; }
      if (!res.ok || js?.success === false) throw new Error(js?.message || 'Delete failed');

      ok('Deleted');
      state.rows = state.rows.filter(x => x._k !== k);
      renderRows();

    }catch(ex){
      err(ex?.name === 'AbortError' ? 'Request timed out' : (ex?.message || 'Delete failed'));
    }finally{
      showLoading(false);
    }
  }

  function applyPreset(preset){
    const link = cleanStr(state.pageLink);
    if (!link){
      err('Enter page link first.');
      $('presetSelect').value = '';
      return;
    }

    if (preset === 'seo_basic'){
      addRow({ typeKey:'standard', attribute:'description', content:'' });
      addRow({ typeKey:'standard', attribute:'robots', content:'index,follow' });
      ok('Added SEO preset');
    } else if (preset === 'social_basic'){
      addRow({ typeKey:'opengraph', attribute:'og:title', content:'' });
      addRow({ typeKey:'opengraph', attribute:'og:description', content:'' });
      addRow({ typeKey:'twitter', attribute:'twitter:card', content:'summary_large_image' });
      ok('Added Social preset');
    } else if (preset === 'charset_viewport'){
      addRow({ typeKey:'charset', content:'UTF-8' });
      addRow({ typeKey:'standard', attribute:'viewport', content:'width=device-width, initial-scale=1' });
      ok('Added Charset+Viewport preset');
    }

    $('presetSelect').value = '';
  }

  function copyToClipboard(text){
    const t = (text ?? '').toString();
    if (!t.trim()) return;

    if (navigator.clipboard && navigator.clipboard.writeText){
      navigator.clipboard.writeText(t).then(()=> ok('Copied')).catch(()=> err('Copy failed'));
      return;
    }
    const ta = document.createElement('textarea');
    ta.value = t;
    document.body.appendChild(ta);
    ta.select();
    try{ document.execCommand('copy'); ok('Copied'); }
    catch(_){ err('Copy failed'); }
    finally{ document.body.removeChild(ta); }
  }

  function bindUI(){
    $('pageLink').addEventListener('input', (e) => setPageLink(e.target.value));

    $('btnLoad').addEventListener('click', async () => {
      const link = cleanStr(state.pageLink);
      if (!link){ err('Page link is required.'); return; }
      await loadTagsForLink(link);
    });

    $('btnAddRow').addEventListener('click', () => addRow());
    $('btnSaveAll').addEventListener('click', saveAll);

    $('presetSelect').addEventListener('change', (e) => {
      const v = e.target.value;
      if (!v) return;
      applyPreset(v);
    });

    document.addEventListener('change', (e) => {
      const selType = e.target.closest('select.js-type[data-k]');
      if (selType){
        const k = selType.dataset.k;
        const r = state.rows.find(x => x._k === k);
        if (!r) return;

        r.typeKey = selType.value;

        const def = getTypeDef(r.typeKey);
        const isCharset = (r.typeKey === 'charset' || def?.attrName === 'charset');

        if (isCharset){
          r.attribute = '';
          if (!cleanStr(r.content)) r.content = 'UTF-8';
        } else {
          const list = def?.attributes || [];
          const cur = cleanStr(r.attribute);
          r.attribute = list.includes(cur) ? cur : (list[0] || '');
        }

        r._err = '';
        renderRows();
        return;
      }

      const selAttr = e.target.closest('select.js-attr[data-k]');
      if (selAttr){
        const k = selAttr.dataset.k;
        const r = state.rows.find(x => x._k === k);
        if (!r) return;
        r.attribute = selAttr.value;
        r._err = '';
        renderRows();
        return;
      }
    });

    document.addEventListener('input', (e) => {
      const inp = e.target.closest('input.js-content[data-k]');
      if (!inp) return;
      const k = inp.dataset.k;
      const r = state.rows.find(x => x._k === k);
      if (!r) return;

      r.content = inp.value;

      if (r.typeKey === 'charset'){
        r.attribute = '';
      }

      r._err = '';
      const code = document.querySelector(`#${CSS.escape(rootId)} code.js-preview[data-k="${CSS.escape(k)}"]`);
      if (code){
        const pv = buildMetaPreview(r);
        code.textContent = pv || '—';
      }
    });

    document.addEventListener('click', (e) => {
      const btnCopy = e.target.closest('button.js-copy[data-k]');
      if (btnCopy){
        const k = btnCopy.dataset.k;
        const r = state.rows.find(x => x._k === k);
        if (!r) return;
        copyToClipboard(buildMetaPreview(r));
        return;
      }

      const btnDup = e.target.closest('button.js-dup[data-k]');
      if (btnDup){
        duplicateRow(btnDup.dataset.k);
        return;
      }

      const btnDelLocal = e.target.closest('button.js-del-local[data-k]');
      if (btnDelLocal){
        removeLocalRow(btnDelLocal.dataset.k);
        return;
      }

      const btnDelDb = e.target.closest('button.js-del-db[data-k]');
      if (btnDelDb){
        deleteRowFromDb(btnDelDb.dataset.k);
        return;
      }
    });

    $('pageLink').addEventListener('keydown', async (e) => {
      if (e.key === 'Enter'){
        e.preventDefault();
        const link = cleanStr(state.pageLink);
        if (!link){ err('Page link is required.'); return; }
        await loadTagsForLink(link);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (!token()){ window.location.href='/'; return; }

    showLoading(true);
    try{
      await loadTypes();
      bindUI();
      updateTopSummary();
      syncEmptyState();
    }catch(ex){
      err(ex?.message || 'Initialization failed');
    }finally{
      showLoading(false);
    }
  });

})();
</script>
@endpush
