{{-- resources/views/solve/index.blade.php --}}
@push('styles')
<style>
  /* ===== Page shell ===== */
  .container.py-4 {
    max-width: 1140px;
  }

  .container.py-4 h3 {
    font-size: 1.35rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: 1.25rem;
  }
  .container.py-4 h3 i {
    color: #6366f1;
  }

  /* ===== Left question list ===== */
  #qList.list-group{
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 30px rgba(15,23,42,.12);
    background: radial-gradient(circle at top left, rgba(129,140,248,.10), transparent 55%), #ffffff;
    max-height: 70vh;
    display: flex;
    flex-direction: column;
  }

  #qList .p-3.text-muted{
    font-size: .875rem;
  }

  #qList .list-group-item{
    border: 0;
    border-bottom: 1px solid rgba(148,163,184,.35);
    font-size: .875rem;
    padding: .6rem .75rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    cursor: pointer;
    background: transparent;
    transition: background .12s ease, color .12s ease, padding-left .08s ease;
  }
  #qList .list-group-item:last-child{
    border-bottom: 0;
  }

  #qList .list-group-item::before{
    content: "▹";
    font-size: .7rem;
    color: #9ca3af;
    transition: transform .12s ease, color .12s ease;
  }

  #qList .list-group-item:hover{
    background: rgba(129,140,248,.08);
    color: #111827;
    padding-left: .95rem;
  }
  #qList .list-group-item:hover::before{
    transform: translateX(2px);
    color: #6366f1;
  }

  /* ===== Right solve panel ===== */
  #solveArea{
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    background: radial-gradient(circle at top left, rgba(59,130,246,.12), transparent 55%), #ffffff;
    box-shadow: 0 18px 45px rgba(15,23,42,.12);
    padding: 1.25rem 1.5rem 1.4rem 1.5rem;
  }

  #qTitle{
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: .25rem;
  }

  #qDesc{
    font-size: .875rem;
    color: #6b7280 !important;
    margin-bottom: .9rem;
    white-space: pre-line;
  }

  /* ===== Language select ===== */
  #langBox{
    margin-bottom: .75rem;
  }
  #langBox .form-label{
    font-size: .8rem;
    font-weight: 500;
    color: #4b5563;
    margin-bottom: .2rem;
  }
  #langSelect{
    font-size: .85rem;
    border-radius: 999px;
    padding: .35rem .9rem;
  }
  #langSelect:focus{
    border-color: rgba(99,102,241,.7);
    box-shadow: 0 0 0 2px rgba(129,140,248,.25);
  }

  /* ===== Code editor ===== */
  #codeEditor{
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: .8rem;
    line-height: 1.45;
    border-radius: 12px;
    border: 1px solid #0f172a;
    background: #020617;
    color: #e5e7eb;
    padding: .75rem .85rem;
    resize: vertical;
    min-height: 260px;
    box-shadow: 0 14px 28px rgba(15,23,42,.8);
  }
  #codeEditor:focus{
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79,70,229,.8);
  }

  /* ===== Run button ===== */
  #btnRun{
    border-radius: 999px;
    font-size: .9rem;
    padding: .5rem 1.2rem;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    border: 0;
    box-shadow: 0 10px 25px rgba(79,70,229,.45);
    transition: transform .08s ease, box-shadow .1s ease, filter .1s ease;
  }
  #btnRun i{
    font-size: .8rem;
  }
  #btnRun:hover{
    filter: brightness(1.05);
    transform: translateY(-1px);
    box-shadow: 0 14px 30px rgba(79,70,229,.55);
  }
  #btnRun:active{
    transform: translateY(0);
    box-shadow: 0 8px 18px rgba(79,70,229,.5);
  }

  /* ===== Results box ===== */
  #resultsBox{
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    padding: .8rem .9rem;
    box-shadow: 0 8px 20px rgba(15,23,42,.08);
  }
  #resultsBox h6{
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: .6rem;
    display:flex;
    align-items:center;
    gap:.4rem;
  }
  #resultsBox h6::before{
    content:'';
    width:4px;
    height:14px;
    border-radius:999px;
    background:#22c55e;
  }

  /* Cards for each test result (uses what your JS already outputs) */
  #results > div{
    font-size: .8rem;
    border-radius: 10px !important;
    padding: .6rem .7rem !important;
  }
  #results > div strong{
    font-weight: 600;
  }
  #results > div small{
    font-size: .75rem;
  }
  #results > div.border-success{
    border-color: #16a34a !important;
    background: #ecfdf3 !important;
  }
  #results > div.border-danger{
    border-color: #dc2626 !important;
    background: #fef2f2 !important;
  }

  /* ===== Mobile tweaks ===== */
  @media (max-width: 767.98px){
    #solveArea{
      margin-top: 1rem;
      padding: 1rem;
    }
    #qList.list-group{
      max-height: 40vh;
    }
  }

  /* ===== Optional dark theme (if you use html.theme-dark) ===== */
  html.theme-dark #qList.list-group{
    background: radial-gradient(circle at top left, rgba(129,140,248,.22), transparent 55%), #020617;
    border-color: rgba(148,163,184,.6);
    box-shadow: 0 16px 40px rgba(0,0,0,1);
  }
  html.theme-dark #qList .list-group-item{
    color:#e5e7eb;
    border-bottom-color: rgba(51,65,85,.9);
  }
  html.theme-dark #qList .list-group-item:hover{
    background: rgba(30,64,175,.8);
    color:#f9fafb;
  }

  html.theme-dark #solveArea{
    background: radial-gradient(circle at top left, rgba(59,130,246,.35), transparent 55%), #020617;
    border-color: rgba(148,163,184,.7);
    box-shadow: 0 22px 60px rgba(0,0,0,1);
  }
  html.theme-dark #qTitle{
    color:#e5e7eb;
  }
  html.theme-dark #qDesc{
    color:#9ca3af !important;
  }
  html.theme-dark #resultsBox{
    background:#020617;
    border-color:rgba(148,163,184,.7);
  }
  html.theme-dark #results > div.border-success{
    background: rgba(22,163,74,.16) !important;
  }
  html.theme-dark #results > div.border-danger{
    background: rgba(220,38,38,.16) !important;
  }
</style>
@endpush

@section('content')
<div class="container py-4">
  <h3 class="mb-3"><i class="fa fa-terminal me-2"></i>Solve Problems</h3>

  <div class="row">
    {{-- LEFT: Question list --}}
    <div class="col-md-4">
      <div class="list-group" id="qList">
        <div class="p-3 text-muted">Loading…</div>
      </div>
    </div>

    {{-- RIGHT: Solve panel --}}
    <div class="col-md-8">
      <div id="solveArea" style="display:none">
        <h5 id="qTitle"></h5>
        <p id="qDesc" class="text-muted"></p>

        <div id="langBox" class="mb-2">
          <label class="form-label">Language</label>
          <select id="langSelect" class="form-select"></select>
        </div>

        <label class="form-label">Code</label>
        <textarea id="codeEditor" class="form-control" rows="12" spellcheck="false"></textarea>

        <button id="btnRun" class="btn btn-primary mt-3">
          <i class="fa fa-play me-1"></i>Run Code
        </button>

        <div id="resultsBox" class="mt-4" style="display:none">
          <h6>Results</h6>
          <div id="results"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(async function(){
  const qList = document.getElementById('qList');
  const solveArea = document.getElementById('solveArea');
  const qTitle = document.getElementById('qTitle');
  const qDesc = document.getElementById('qDesc');
  const langBox = document.getElementById('langBox');
  const langSelect = document.getElementById('langSelect');
  const codeEditor = document.getElementById('codeEditor');
  const btnRun = document.getElementById('btnRun');
  const resultsBox = document.getElementById('resultsBox');
  const results = document.getElementById('results');

  let allQuestions = [];
  let currentQuestion = null;
  let currentLanguages = [];
  let currentSnippets = [];

  // ===== Load all questions =====
  async function loadQuestions(){
    try {
      const res = await fetch('/api/coding_questions?per_page=50');
      const data = await res.json();
      allQuestions = data.data?.data || data.data || [];
      renderList();
    } catch(e) {
      qList.innerHTML = `<div class="p-3 text-danger">Failed to load questions</div>`;
    }
  }

  function renderList(){
    qList.innerHTML = '';
    if(!allQuestions.length){
      qList.innerHTML = `<div class="p-3 text-muted">No questions found</div>`;
      return;
    }
    allQuestions.forEach(q=>{
      const a = document.createElement('a');
      a.href = 'javascript:void(0)';
      a.className = 'list-group-item list-group-item-action';
      a.textContent = q.title;
      a.onclick = ()=> loadQuestion(q.id);
      qList.appendChild(a);
    });
  }

  // ===== Load single question =====
  async function loadQuestion(id){
    try {
      const res = await fetch('/api/coding_questions/' + id);
      const data = await res.json();
      const q = data.data || data;

      currentQuestion = q;
      currentLanguages = q.languages || [];
      currentSnippets = q.snippets || [];

      qTitle.textContent = q.title;
      qDesc.textContent = q.description || '';
      solveArea.style.display = 'block';
      resultsBox.style.display = 'none';

      // Populate language selector
      langSelect.innerHTML = '';
      if(currentLanguages.length === 1){
        langBox.style.display = 'none';
        const L = currentLanguages[0];
        langSelect.innerHTML = `<option value="${L.language_key}" selected>${L.language_key}</option>`;
      } else {
        langBox.style.display = 'block';
        currentLanguages.forEach(L=>{
          const opt = document.createElement('option');
          opt.value = L.language_key;
          opt.textContent = L.language_key;
          langSelect.appendChild(opt);
        });
      }

      // Initial snippet
      setSnippetForLanguage(langSelect.value);

      // Change language → update snippet
      langSelect.onchange = ()=>{
        setSnippetForLanguage(langSelect.value);
      };

    } catch(e) {
      solveArea.style.display = 'none';
      alert("Failed to load question");
    }
  }

  function setSnippetForLanguage(lang){
    const snip = currentSnippets.find(s=>s.language_key===lang && s.is_default)
              || currentSnippets.find(s=>s.language_key===lang)
              || null;
    codeEditor.value = snip ? snip.template : '';
  }

  // ===== Run code =====
  btnRun.onclick = async ()=>{
    if(!currentQuestion) return;
    const lang = langSelect.value;
    const code = codeEditor.value;

    resultsBox.style.display = 'block';
    results.innerHTML = `<div class="text-muted">Running…</div>`;

    const payload = {
      question_id: currentQuestion.id,
      language: lang,
      code: code
    };

    try {
      const res = await fetch('/api/judge/execute', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      renderResults(data);
    } catch(e) {
      results.innerHTML = `<div class="text-danger">Error running code</div>`;
    }
  };

  // ===== Render results =====
  function renderResults(data){
    if(!data || data.status!=='success'){
      results.innerHTML = `<div class="text-danger">${data.message||'Error'}</div>`;
      return;
    }

    const tests = data.results || [];
    results.innerHTML = '';
    tests.forEach((t,i)=>{
      const div = document.createElement('div');
      div.className = 'p-2 mb-2 border rounded ' + (t.pass ? 'border-success bg-success-subtle' : 'border-danger bg-danger-subtle');
      div.innerHTML = `
        <strong>Test #${i+1}</strong> — ${t.pass?'✅ Passed':'❌ Failed'}<br>
        <small><b>Input:</b> ${escapeHTML(t.input||'')}<br>
        <b>Expected:</b> ${escapeHTML(t.expected||'')}<br>
        <b>Got:</b> ${escapeHTML(t.output||'')}</small>
      `;
      results.appendChild(div);
    });
  }

  function escapeHTML(str){
    return (str||'').toString().replace(/[&<>"']/g, c=>({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'
    }[c]));
  }

  // Boot
  loadQuestions();
})();
</script>
@endpush
