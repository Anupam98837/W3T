{{-- resources/views/solve/index.blade.php --}}

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
      const res = await fetch('/api/questions?per_page=50');
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
      const res = await fetch('/api/questions/' + id);
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
