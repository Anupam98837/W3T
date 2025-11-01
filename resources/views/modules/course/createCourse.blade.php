{{-- resources/views/modules/courses/createCourse.blade.php --}}
@section('title','Create Course')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  /* ===== Shell ===== */
  .crs-wrap{max-width:1100px;margin:14px auto 40px}
  .wizard.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:hidden}
  .wizard .card-header{background:var(--surface);border-bottom:1px solid var(--line-strong);padding:16px 18px}
  .wizard-head{display:flex;align-items:center;gap:10px;margin-bottom:12px}
  .wizard-head i{color:var(--accent-color)}
  .wizard-head strong{color:var(--ink);font-family:var(--font-head);font-weight:700}
  .wizard-head .hint{color:var(--muted-color);font-size:var(--fs-13)}

  /* ===== Stepper (inside header) ===== */
  .wizard-steps{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
  .step-btn{
    display:flex;gap:10px;align-items:center;justify-content:flex-start;
    padding:12px;border:1px solid var(--line-strong);border-radius:12px;background:var(--surface-2, #fff);
    cursor:pointer;transition:transform .06s ease, box-shadow .18s ease, border-color .18s ease;
  }
  .step-btn:hover{transform:translateY(-1px);box-shadow:var(--shadow-2)}
  .step-btn .num{
    width:28px;height:28px;border-radius:999px;display:flex;align-items:center;justify-content:center;
    font-weight:700;border:1px solid var(--line-strong);background:#fff;color:#111827;flex:0 0 28px
  }
  .step-btn .txt{display:flex;flex-direction:column}
  .step-btn .lbl{font-weight:600;color:var(--ink);line-height:1}
  .step-btn .sub{font-size:12px;color:#6b7280;line-height:1.2}
  .step-btn.active{border-color:var(--accent-color);box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 18%, transparent)}
  .step-btn.active .num{background:var(--accent-color);color:#fff;border-color:var(--accent-color)}
  .step-btn.done{opacity:.95}

  /* Progress track */
  .wizard-track{height:4px;background:var(--line-soft);border-radius:999px;margin-top:12px;overflow:hidden}
  .wizard-track .bar{height:100%;width:0;background:var(--accent-color);transition:width .25s ease}

  /* ===== Section titles ===== */
  .section-title{font-weight:600;color:var(--ink);font-family:var(--font-head);margin:10px 2px 14px}
  .divider-soft{height:1px;background:var(--line-soft);margin:10px 0 16px}

  /* ===== Editor ===== */
  .toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
  .tool{border:1px solid var(--line-strong);border-radius:10px;background:#fff;padding:6px 9px;cursor:pointer}
  .tool:hover{background:var(--page-hover)}
  .rte-wrap{position:relative}
  .rte{
    min-height:300px;max-height:600px;overflow:auto;
    border:1px solid var(--line-strong);border-radius:12px;background:#fff;padding:12px;line-height:1.6;outline:none
  }
  .rte:focus{box-shadow:var(--ring);border-color:var(--accent-color)}
  .rte-ph{position:absolute;top:12px;left:12px;color:#9aa3b2;pointer-events:none;font-size:var(--fs-14)}
  .rte.has-content + .rte-ph{display:none}

  /* ===== Chips ===== */
  .chip-row{display:flex;flex-wrap:wrap;gap:8px}
  .chip{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px dashed var(--line-strong);border-radius:999px;background:#fff}
  .chip button{border:none;background:transparent;color:#a1a1aa;cursor:pointer}
  .chip button:hover{color:#ef4444}

  /* ===== Helpers ===== */
  .err{font-size:12px;color:var(--danger-color);display:none;margin-top:6px}
  .err:not(:empty){display:block}
  .dim{position:absolute;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.06);z-index:2}
  .dim.show{display:flex}
  .spin{width:18px;height:18px;border:3px solid #0001;border-top-color:var(--accent-color);border-radius:50%;animation:rot 1s linear infinite}
  @keyframes rot{to{transform:rotate(360deg)}}

  .tiny{font-size:12px;color:#6b7280}

  /* Inputs polish */
  .form-control:focus, .form-select:focus{box-shadow:0 0 0 3px color-mix(in oklab, var(--accent-color) 20%, transparent);border-color:var(--accent-color)}
  .input-group-text{background:var(--surface);border-color:var(--line-strong)}

  /* ================= Reference-style Stepper (match Jobs page) ================= */
.wizard-steps{
  display:flex !important;
  gap:12px;
  flex-wrap:wrap;
  margin:0 0 12px;
}
.step-btn{
  flex:1 1 220px;
  min-width:220px;
  display:flex;
  align-items:center;
  gap:12px;
  padding:16px;
  border:1px solid var(--line-strong);
  border-radius:13px;
  background:var(--surface);
  cursor:pointer;
  transition: box-shadow .18s ease, transform .08s ease, border-color .18s ease, background .18s ease;
}
.step-btn:hover{ box-shadow:var(--shadow-1); transform:translateY(-1px); }

/* Number pill like reference */
.step-btn .num{
  width:32px; height:32px; flex:0 0 32px;
  border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font:700 14px/1 var(--font-head);
  border:1px solid var(--line-strong);
  background:var(--surface);
  color:var(--text-color);
}

/* Two-line label like reference */
.step-btn .txt{ display:flex; flex-direction:column; line-height:1.15; }
.step-btn .lbl{ font-weight:600; color:var(--text-color); font-size:var(--fs-14); }
.step-btn .sub{ font-size:12px; color:var(--muted-color); margin-top:2px; }

/* Active/done states mirror Jobs page */
.step-btn.active{ border-color:var(--primary-color); }
.step-btn.active .num{ background:var(--primary-color); color:#fff; border-color:var(--primary-color); }
.step-btn.done{ opacity:.95; }

/* Hide progress bar to match reference look */
.wizard-track{ display:none; }

/* Dark mode parity */
html.theme-dark .step-btn{ background:#0f172a; border-color:var(--line-strong); }
html.theme-dark .step-btn .num{ background:#0f172a; border-color:var(--line-strong); color:var(--text-color); }
html.theme-dark .step-btn.active .num{ background:var(--primary-color); border-color:var(--primary-color); color:#fff; }


  /* Dark tweaks */
  html.theme-dark .step-btn{background:#0f172a;border-color:var(--line-strong);color:var(--text-color)}
  html.theme-dark .step-btn .num{background:#20324f;border-color:#32507f;color:#e5e7eb}
  html.theme-dark .rte{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .tool{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
  html.theme-dark .chip{background:#0f172a;border-color:var(--line-strong);color:#e5e7eb}
</style>
@endpush


@section('content')
<div class=" crs-wrap">
  <div class="card wizard">
    <div class="card-header">
      <div class="wizard-head">
        <i class="fa-solid fa-graduation-cap"></i>
        <strong>Create Course</strong>
        <span class="hint" id="hint">— Fill the details & pricing.</span>
      </div>

      <div class="wizard-steps" role="tablist" aria-label="Create course">
        <button type="button" class="step-btn active" data-step="1" aria-selected="true">
          <div class="num">1</div>
          <div class="txt"><span class="lbl">Details & Pricing</span><span class="sub">Basics, price</span></div>
        </button>
        <button type="button" class="step-btn" data-step="2" aria-selected="false">
          <div class="num">2</div>
          <div class="txt"><span class="lbl">Course Settings</span><span class="sub">Feature, level</span></div>
        </button>
        <button type="button" class="step-btn" data-step="3" aria-selected="false">
          <div class="num">3</div>
          <div class="txt"><span class="lbl">Metadata</span><span class="sub">Tags & categories</span></div>
        </button>
      </div>
      <div class="wizard-track"><div class="bar" id="wizBar" style="width:33%"></div></div>
    </div>

    <div class="card-body position-relative">
      <div class="dim" id="busy"><div class="spin" aria-label="Saving…"></div></div>

      {{-- STEP 1 --}}
      <div id="S1">
        <h3 class="section-title">Details & Pricing</h3>
        <div class="divider-soft"></div>

        <div class="mb-3">
          <label class="form-label">Course Title <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
            <input id="title" class="form-control" type="text" maxlength="255" placeholder="e.g., Mastering MERN Stack">
          </div>
          <div class="err" data-for="title"></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Short Description</label>
          <input id="short_description" class="form-control" type="text" placeholder="Concise summary (≈150 chars)">
          <div class="err" data-for="short_description"></div>
        </div>

        <div class="mb-2">
          <label class="form-label d-block">Full Description</label>
          <div class="toolbar">
            <button class="tool" type="button" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
            <button class="tool" type="button" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
            <button class="tool" type="button" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
            <button class="tool" type="button" data-format="H1">H1</button>
            <button class="tool" type="button" data-format="H2">H2</button>
            <button class="tool" type="button" data-format="H3">H3</button>
            <button class="tool" type="button" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
            <button class="tool" type="button" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
            <button class="tool" type="button" id="btnLink"><i class="fa-solid fa-link"></i></button>
            <span class="tiny">Images aren’t needed here.</span>
          </div>
          <div class="rte-wrap">
            <div id="editor" class="rte" contenteditable="true" spellcheck="true"></div>
            <div class="rte-ph">Write the full course description here…</div>
          </div>
          <div class="err" data-for="full_description"></div>
        </div>

        <div class="row g-3 mt-2">
          <div class="col-md-6">
            <label class="form-label">Course Type</label>
            <select id="course_type" class="form-select">
              <option value="paid" selected>Paid</option>
              <option value="free">Free</option>
            </select>
            <div class="tiny mt-1">Pricing fields show only for <b>Paid</b> courses.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select id="status" class="form-select">
              <option value="draft" selected>Draft</option>
              <option value="published">Published</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>

        <div id="priceBlock" class="mt-3">
          <div class="divider-soft"></div>
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Price</label>
              <div class="input-group">
                <span class="input-group-text">₹</span>
                <input id="price_amount" class="form-control" type="number" min="0" step="0.01" placeholder="4999">
              </div>
              <div class="err" data-for="price_amount"></div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Discount (%)</label>
              <div class="input-group">
                <input id="discount_percent" class="form-control" type="number" min="0" max="100" step="0.01" placeholder="10">
                <span class="input-group-text">%</span>
              </div>
            </div>
            <!-- Discount Amount (₹) removed by request -->
            <div class="col-md-3">
              <label class="form-label">Currency</label>
              <select id="price_currency" class="form-select">
                <option value="INR" selected>INR</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Final</label>
              <input id="final_price" class="form-control" type="text" value="0.00" readonly>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <a id="cancel1" class="btn btn-light" href="#">Cancel</a>
          <button id="to2" class="btn btn-primary" type="button" disabled>Proceed</button>
        </div>
      </div>

      {{-- STEP 2 --}}
      <div id="S2" class="d-none">
        <h3 class="section-title">Course Settings</h3>
        <div class="divider-soft"></div>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="is_featured">
          <label class="form-check-label" for="is_featured">Mark as Featured</label>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Featured Rank</label>
            <input id="featured_rank" type="number" min="0" class="form-control" placeholder="1">
          </div>
          <div class="col-md-4">
            <label class="form-label">Display Order</label>
            <input id="order_no" type="number" min="0" class="form-control" placeholder="100">
          </div>
          <div class="col-md-4">
            <label class="form-label">Difficulty</label>
            <select id="level" class="form-select">
              <option value="" selected>Choose…</option>
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
            </select>
          </div>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-md-4">
            <label class="form-label">Language</label>
            <select id="language" class="form-select">
              <option value="" selected>Choose…</option>
              <option value="EN">English (EN)</option>
              <option value="HN">Hindi (HN)</option>
              <option value="BN">Bengali (BN)</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Publish At</label>
            <input id="publish_at" class="form-control" type="datetime-local">
          </div>
          <div class="col-md-4">
            <label class="form-label">Unpublish At</label>
            <input id="unpublish_at" class="form-control" type="datetime-local">
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <button id="back1" class="btn btn-light" type="button">&larr; Back</button>
          <button id="to3" class="btn btn-primary" type="button">Proceed</button>
        </div>
      </div>

      {{-- STEP 3 --}}
      <div id="S3" class="d-none">
        <h3 class="section-title">Metadata</h3>
        <div class="divider-soft"></div>

        <div class="mb-3">
          <label class="form-label d-block">Tags</label>
          <div class="row g-2 align-items-center mb-2">
            <div class="col"><input id="tagIn" class="form-control" type="text" placeholder="Add a tag (e.g., Programming)"></div>
            <div class="col-auto"><button id="addTag" type="button" class="btn btn-outline-primary"><i class="fa fa-plus me-1"></i>Add</button></div>
          </div>
          <div id="tagRow" class="chip-row"></div>
        </div>

        <div class="mb-3">
          <label class="form-label d-block">Categories</label>
          <div class="row g-2 align-items-center mb-2">
            <div class="col"><input id="catIn" class="form-control" type="text" placeholder="Add a category (e.g., Web Dev)"></div>
            <div class="col-auto"><button id="addCat" type="button" class="btn btn-outline-primary"><i class="fa fa-plus me-1"></i>Add</button></div>
          </div>
          <div id="catRow" class="chip-row"></div>
        </div>

        <div class="mb-3">
          <label class="form-label d-block">Keywords</label>
          <div class="row g-2 align-items-center mb-2">
            <div class="col"><input id="keyIn" class="form-control" type="text" placeholder="Add a keyword (e.g., learn to code)"></div>
            <div class="col-auto"><button id="addKey" type="button" class="btn btn-outline-primary"><i class="fa fa-plus me-1"></i>Add</button></div>
          </div>
          <div id="keyRow" class="chip-row"></div>
        </div>

        <div class="mb-2">
          <label class="form-label d-block">Custom Properties</label>
          <div class="row g-2 mb-2">
            <div class="col"><input id="propName" class="form-control" type="text" placeholder="Property (e.g., Prerequisites)"></div>
            <div class="col"><input id="propVal" class="form-control" type="text" placeholder="Value (e.g., Basic JS)"></div>
            <div class="col-auto"><button id="addProp" type="button" class="btn btn-outline-primary"><i class="fa fa-plus me-1"></i>Add</button></div>
          </div>
          <div id="propRow" class="chip-row"></div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <a id="cancel3" class="btn btn-light" href="#">Cancel</a>
          <div class="d-flex gap-2">
            <button id="btnDraft" type="button" class="btn btn-outline-primary">Save Draft</button>
            <button id="btnPublish" type="button" class="btn btn-primary">Publish Course</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
    <div id="okToast" class="toast text-bg-success border-0">
      <div class="d-flex"><div id="okMsg" class="toast-body">Done</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>
    </div>
    <div id="errToast" class="toast text-bg-danger border-0 mt-2">
      <div class="d-flex"><div id="errMsg" class="toast-body">Something went wrong</div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>
    </div>
  </div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  /* ===== helpers ===== */
  const $ = id => document.getElementById(id);
  const role  = sessionStorage.getItem('role') || localStorage.getItem('role') || '';
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';

  const okToast  = new bootstrap.Toast($('okToast'));
  const errToast = new bootstrap.Toast($('errToast'));
  const ok  = (m)=>{ $('okMsg').textContent  = m||'Done'; okToast.show(); };
  const err = (m)=>{ $('errMsg').textContent = m||'Something went wrong'; errToast.show(); };

  const baseList = (role === 'superadmin' || role === 'super_admin') ? '/super_admin/courses' : '/admin/courses';
  ['cancel1','cancel3'].forEach(id => { const a=$(id); if(a && !a.getAttribute('href')) a.setAttribute('href', baseList); });

  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  /* ===== stepper ===== */
  const steps = Array.from(document.querySelectorAll('.step-btn'));
  const sections = [ $('S1'), $('S2'), $('S3') ];
  const bar = $('wizBar'), hint = $('hint');

  function setStep(n){
    steps.forEach((b,i)=>{ b.classList.toggle('active',i===n); b.classList.toggle('done',i<n); b.setAttribute('aria-selected', i===n?'true':'false'); });
    sections.forEach((s,i)=> s.classList.toggle('d-none', i!==n));
    bar.style.width = ( (n+1)/steps.length*100 ) + '%';
    hint.textContent = ['— Fill the details & pricing.','— Configure course settings.','— Add tags, categories & properties.'][n];
  }
  steps.forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const idx = Number(btn.dataset.step)-1;
      if(idx>0 && !gateOK()){ Swal.fire('Missing info','Enter a title (and price for paid course) to proceed.','info'); return; }
      setStep(idx);
    });
  });

  /* ===== editor ===== */
  const editor = $('editor');
  const ph = editor.nextElementSibling; // .rte-ph
  const hasContent = () => (editor.textContent || '').trim().length > 0;
  function togglePh(){ editor.classList.toggle('has-content', hasContent()); }
  ['input','keyup','paste','blur'].forEach(ev => editor.addEventListener(ev, togglePh));
  togglePh();

  document.querySelectorAll('.tool[data-cmd]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand(b.dataset.cmd,false,null); editor.focus(); togglePh(); }));
  document.querySelectorAll('.tool[data-format]').forEach(b=> b.addEventListener('click',()=>{ document.execCommand('formatBlock',false,b.dataset.format); editor.focus(); togglePh(); }));
  $('btnLink').addEventListener('click',()=>{
    const u = prompt('Enter URL (https://…)'); if(u && /^https?:\/\//i.test(u)){ document.execCommand('createLink',false,u); editor.focus(); }
  });

  /* ===== fields / pricing ===== */
  const title=$('title'), shortDesc=$('short_description'), ctype=$('course_type'), status=$('status');
  const price=$('price_amount'), dPct=$('discount_percent'), curr=$('price_currency'), final=$('final_price'), priceBlock=$('priceBlock');
  const isFeat=$('is_featured'), fRank=$('featured_rank'), ord=$('order_no'), lvl=$('level'), lang=$('language'), pub=$('publish_at'), unpub=$('unpublish_at');

  function money(v){ const n=Number(v); return isFinite(n)&&n>0?n:0; }
  function pct(v){ const n=Number(v); return isFinite(n)&&n>0?n:0; }
  function recalc(){
    if(ctype.value!=='paid'){ final.value='0.00'; return; }
    const p = money(price.value), pr = p*(pct(dPct.value)/100);
    final.value = Math.max(0, p - pr).toFixed(2);
  }
  [price,dPct].forEach(el=> el.addEventListener('input', recalc));

  function togglePricing(){
    const on = (ctype.value==='paid');
    priceBlock.style.display = on ? 'block' : 'none';
    [price,dPct,curr].forEach(el=> el.disabled = !on);
    recalc(); gateBtn();
  }
  ctype.addEventListener('change', togglePricing);
  togglePricing();

  function gateOK(){
    if(!title.value.trim()) return false;
    if(ctype.value==='paid' && !(money(price.value)>0)) return false;
    return true;
  }
  function gateBtn(){ $('to2').disabled = !gateOK(); }
  ['input','change'].forEach(ev => { title.addEventListener(ev, gateBtn); price.addEventListener(ev, gateBtn); ctype.addEventListener(ev, gateBtn); });
  gateBtn();

  $('to2').onclick=()=> setStep(1);
  $('back1').onclick=()=> setStep(0);
  $('to3').onclick=()=> setStep(2);

  /* ===== chips ===== */
  const tagRow=$('tagRow'), catRow=$('catRow'), keyRow=$('keyRow');
  const tagIn=$('tagIn'),   catIn=$('catIn'),   keyIn=$('keyIn');
  const propRow=$('propRow'), pName=$('propName'), pVal=$('propVal');

  function chip(text){
    const s=document.createElement('span'); s.className='chip'; s.textContent=text+' ';
    const x=document.createElement('button'); x.type='button'; x.innerHTML='<i class="fa fa-xmark"></i>'; x.onclick=()=>s.remove();
    s.appendChild(x); return s;
  }
  const add=(row,input)=>{ const v=(input.value||'').trim(); if(!v) return; row.appendChild(chip(v)); input.value=''; };
  $('addTag').onclick=()=>add(tagRow,tagIn);
  $('addCat').onclick=()=>add(catRow,catIn);
  $('addKey').onclick=()=>add(keyRow,keyIn);
  [tagIn,catIn,keyIn].forEach(inp=> inp.onkeydown=(e)=>{ if(e.key==='Enter'){ e.preventDefault(); (inp===tagIn?$('addTag'):inp===catIn?$('addCat'):$('addKey')).click(); }});
  $('addProp').onclick=()=>{
    const k=(pName.value||'').trim(), v=(pVal.value||'').trim(); if(!k||!v) return;
    propRow.appendChild(chip(`${k}: ${v}`)); pName.value=''; pVal.value=''; pName.focus();
  };
  const collect=row=> Array.from(row.querySelectorAll('.chip')).map(c=> (c.childNodes[0].nodeValue||'').trim()).filter(Boolean);
  function collectProps(){ const o={}; collect(propRow).forEach(t=>{ const i=t.indexOf(':'); if(i>0) o[t.slice(0,i).trim()] = t.slice(i+1).trim(); }); return o; }

  /* ===== errors ===== */
  function fErr(field,msg){ const el=document.querySelector(`.err[data-for="${field}"]`); if(el){ el.textContent=msg||''; el.style.display=msg?'block':'none'; } }
  function clrErr(){ document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }

  /* ===== payload & submit ===== */
  function payload(statusOverride){
    const paid = (ctype.value==='paid');
    return {
      title: (title.value||'').trim(),
      short_description: (shortDesc.value||'') || null,
      full_description: (editor.innerHTML||'').trim() || null,
      status: statusOverride || (status.value||'draft'),
      course_type: ctype.value,
      price_amount:    paid ? money(price.value) : 0,
      price_currency:  paid ? (curr.value||'INR') : 'INR',
      discount_percent: paid && dPct.value ? pct(dPct.value) : null,
      discount_amount:  null, // flat amount removed from UI
      is_featured: isFeat.checked ? 1 : 0,
      featured_rank: Number(fRank.value||0),
      order_no: Number(ord.value||0),
      level: (lvl.value||null),
      language: (lang && lang.value) ? lang.value : null,
      publish_at: (pub.value||null),
      unpublish_at: (unpub.value||null),
      metadata: { tags: collect(tagRow), categories: collect(catRow), keywords: collect(keyRow), properties: collectProps() }
    };
  }

  async function submit(statusOverride){
    clrErr();
    if(!gateOK()){
      setStep(0);
      fErr('title', !title.value.trim() ? 'Title is required.' : '');
      if(ctype.value==='paid' && !(money(price.value)>0)) fErr('price_amount','Price must be > 0 for paid courses.');
      return;
    }

    $('busy').classList.add('show');
    try{
      const res = await fetch('/api/courses', {
        method:'POST',
        headers:{ 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json', 'Content-Type':'application/json' },
        body: JSON.stringify(payload(statusOverride))
      });
      const json = await res.json().catch(()=> ({}));

      if(res.ok){
        Swal.fire({icon:'success',title:'Saved',text:'Course created successfully',timer:900,showConfirmButton:false});
        setTimeout(()=> location.replace(baseList), 900);
        return;
      }
      if(res.status===422){
        const e = json.errors || json.fields || {};
        Object.entries(e).forEach(([k,v])=> fErr(k, Array.isArray(v)? v[0] : String(v)));
        setStep(0); err(json.message || 'Please fix the highlighted fields.'); return;
      }
      if(res.status===403){
        Swal.fire({icon:'error',title:'Unauthorized',html:'If you are <b>Super Admin</b>, ensure API accepts <code>super_admin</code> for <code>POST /api/courses</code>.'});
        return;
      }
      Swal.fire('Save failed', json.message || ('HTTP '+res.status), 'error');
    }catch(ex){
      console.error(ex); Swal.fire('Network error','Please check your connection and try again.','error');
    }finally{
      $('busy').classList.remove('show');
    }
  }

  $('btnDraft').onclick = ()=> submit('draft');
  $('btnPublish').onclick = ()=> submit('published');

  // init
  setStep(0);
  recalc();
})();
</script>
@endpush
