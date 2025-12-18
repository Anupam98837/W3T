{{-- resources/views/modules/coding/codingResult.blade.php --}}
@section('title','Coding Result')

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Coding Result</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
body{
  background:#f1f5f9;
  font-family:Inter,system-ui;
}
.wrap{
  max-width:1200px;
  margin:24px auto;
  padding:0 12px;
  display:grid;
  grid-template-columns:1.3fr .7fr;
  gap:16px;
}
.panel{
  background:#fff;
  border-radius:16px;
  border:1px solid #e5e7eb;
  box-shadow:0 6px 16px rgba(0,0,0,.08);
  padding:18px;
}
.hero{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:20px;
  background:linear-gradient(135deg,#eef2ff,#f8fafc);
  border-radius:16px;
  padding:18px;
  border:1px solid #e5e7eb;
}
.hero-left{ flex:1; }
.title{ font-size:20px; font-weight:800; }
.sub{ font-size:13px; color:#64748b; margin-top:4px; }

.verdict{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:6px 12px;
  border-radius:999px;
  font-weight:700;
  margin-top:10px;
}
.verdict.pass{ background:#ecfdf5; color:#065f46; }
.verdict.fail{ background:#fef2f2; color:#991b1b; }

/* score ring */
.ring{
  --p:0;
  width:96px;height:96px;border-radius:50%;
  background:conic-gradient(#4f46e5 calc(var(--p)*1%),#e5e7eb 0);
  display:grid;place-items:center;
}
.ring .inner{
  width:72px;height:72px;border-radius:50%;
  background:#fff;display:grid;place-items:center;
  font-weight:900;
}

/* KPI */
.kpis{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:12px;
  margin-top:16px;
}
.kpi{
  border:1px solid #e5e7eb;
  border-radius:14px;
  padding:12px;
}
.kpi .label{ font-size:12px; color:#64748b; }
.kpi .value{ font-size:20px; font-weight:800; margin-top:6px; }

/* sections */
.section{ margin-top:18px; }
.section h3{ font-size:16px; font-weight:800; margin-bottom:10px; }

/* code */
pre{
  background:#0f172a;
  color:#e5e7eb;
  padding:14px;
  border-radius:12px;
  overflow:auto;
  font-size:13px;
}

/* testcases */
.testcase{
  padding:12px;
  border:1px solid #e5e7eb;
  border-radius:12px;
  margin-bottom:10px;
}
.testcase.pass{ background:#f0fdf4; border-color:#86efac; }
.testcase.fail{ background:#fef2f2; border-color:#fecaca; }

.tc-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:6px;
}
.tc-left{
  display:flex;
  align-items:center;
  gap:8px;
  font-weight:700;
}
.tc-time{ font-size:12px; color:#64748b; }

.tc-block{ margin-top:8px; }
.tc-label{ font-size:12px; color:#64748b; margin-bottom:4px; }
.tc-code{
  background:#0f172a;
  color:#e5e7eb;
  padding:8px;
  border-radius:8px;
  font-size:13px;
  white-space:pre-wrap;
}

/* student box */
.student-box{
  border:1px solid #e5e7eb;
  border-radius:14px;
  padding:12px;
  margin-bottom:16px;
  background:#f8fafc;
}
.student-label{ font-size:12px; color:#64748b; }
.student-name{ font-size:16px; font-weight:800; margin-top:4px; }
.student-sub{ font-size:13px; color:#475569; margin-top:2px; }

/* summary */
.summary p{
  display:flex;
  justify-content:space-between;
  margin:8px 0;
}
.error{
  background:#fef2f2;
  border:1px solid #fecaca;
  padding:12px;
  border-radius:12px;
  color:#7f1d1d;
}
.hide{display:none;}
</style>
</head>

<body>
<div class="wrap">

<!-- LEFT -->
<div class="panel">
  <div class="hero">
    <div class="hero-left">
      <div class="title" id="quizTitle">Coding Result</div>
      <div class="sub" id="quizSub">Loading…</div>
      <div id="verdictBadge"></div>
    </div>
    <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
  <div class="ring" id="scoreRing">
    <div class="inner" id="ringPct">0%</div>
  </div>

  <button id="exportPdfBtn"
    style="
      padding:8px 14px;
      border-radius:10px;
      border:1px solid #4f46e5;
      background:#4f46e5;
      color:#fff;
      font-weight:700;
      font-size:13px;
      cursor:pointer;
      display:flex;
      align-items:center;
      gap:6px;
    ">
    <i class="fa-solid fa-file-pdf"></i>
    Export PDF
  </button>
</div>

  </div>

  <div class="kpis">
    <div class="kpi"><div class="label">Score</div><div class="value" id="kScore">—</div></div>
    <div class="kpi"><div class="label">Accuracy</div><div class="value" id="kAcc">—</div></div>
    <div class="kpi"><div class="label">Passed Tests</div><div class="value" id="kPass">—</div></div>
    <div class="kpi"><div class="label">Execution Time</div><div class="value" id="kTime">—</div></div>
  </div>

  <div class="section">
    <h3>Submitted Code</h3>
    <pre><code id="submittedCode">// loading…</code></pre>
  </div>

  <div class="section">
    <h3>Test Case Results</h3>
    <div id="testcaseList"></div>
  </div>
</div>

<!-- RIGHT -->
<div class="panel summary">

  <!-- STUDENT INFO -->
  <div class="student-box">
    <div class="student-label">Submitted By</div>
    <div class="student-name" id="studentName">—</div>
    <div class="student-sub" id="studentEmail"></div>
  </div>

  <h3>Attempt Summary</h3>
  <p><span>Status</span><strong id="sStatus">—</strong></p>
  <p><span>Started</span><strong id="sStart">—</strong></p>
  <p><span>Finished</span><strong id="sFinish">—</strong></p>
  <p><span>Total Tests</span><strong id="sQ">—</strong></p>
  <p><span>Passed</span><strong id="sC">—</strong></p>
  <p><span>Failed</span><strong id="sW">—</strong></p>
  <p><span>Total Time</span><strong id="sT">—</strong></p>
</div>

</div>

<script>
(() => {
  const api = `/api`;
  const token = localStorage.getItem('token') || sessionStorage.getItem('token');

  const pathParts = window.location.pathname.split('/').filter(Boolean);
  const resultUuid = pathParts[pathParts.length - 2];

  const el = id => document.getElementById(id);
  const fmtMs = ms => ms < 1000 ? `${ms} ms` : `${(ms/1000).toFixed(2)} s`;
// Export PDF
document.getElementById('exportPdfBtn')?.addEventListener('click', () => {
  if (!resultUuid) {
    alert('Result not found');
    return;
  }

  const url = `/api/coding/results/${resultUuid}/export?format=pdf`;

  // open in new tab so print dialog doesn't block UI
  window.open(url, '_blank');
});

  async function load(){
    console.log('Fetching from:', `${api}/coding/results/${resultUuid}/details`);
    
    const res = await fetch(`${api}/coding/results/${resultUuid}/details`, {
      headers:{ Authorization:`Bearer ${token}` }
    });

    const data = await res.json();
    console.log('Full API Response:', data);
    
    if(!data.success) {
      console.error('API Error:', data);
      return;
    }

    const {question, submission, result, timing, testcases, student} = data;

    el('quizTitle').textContent = question.title;
    el('quizSub').textContent = `Language: ${submission.language}`;

    el('scoreRing').style.setProperty('--p', result.percentage);
    el('ringPct').textContent = result.percentage.toFixed(2)+'%';

    el('kScore').textContent = `${result.marks_obtained}/${result.marks_total}`;
    el('kAcc').textContent = result.percentage.toFixed(2)+'%';
    el('kPass').textContent = `${result.passed_tests}/${result.total_tests}`;
    el('kTime').textContent = fmtMs(timing.total_time_ms);

    el('submittedCode').textContent = submission.submitted_code;

    el('verdictBadge').innerHTML =
      `<div class="verdict ${result.all_pass?'pass':'fail'}">
        <i class="fa-solid ${result.all_pass?'fa-check':'fa-xmark'}"></i>
        ${result.all_pass?'Accepted':'Failed'}
      </div>`;

    el('sStatus').textContent = result.all_pass ? 'PASSED' : 'FAILED';
    el('sStart').textContent = timing.started_at;
    el('sFinish').textContent = timing.finished_at;
    el('sQ').textContent = result.total_tests;
    el('sC').textContent = result.passed_tests;
    el('sW').textContent = result.failed_tests;
    el('sT').textContent = fmtMs(timing.total_time_ms);

    if(student){
      el('studentName').textContent = student.name || '—';
      el('studentEmail').textContent = student.email || '';
    }

    el('testcaseList').innerHTML = testcases.map(tc => {
      console.log('Test Case:', tc); // Debug log
      
      const isPassed = tc.status === 'passed';
      const isSample = tc.visibility === 'sample';
      
      return `
        <div class="testcase ${isPassed ? 'pass':'fail'}">
          <div class="tc-head">
            <div class="tc-left">
              <i class="fa-solid ${isPassed ? 'fa-check':'fa-xmark'}"></i>
              Test Case #${tc.test_id} - ${tc.status}
            </div>
            <div class="tc-time">${fmtMs(tc.time_ms)}</div>
          </div>

          ${!isPassed && tc.failure_reason ? `
            <div class="tc-block">
              <div class="tc-label">Error</div>
              <div style="color:#991b1b;font-weight:600;font-size:13px;">${tc.failure_reason}</div>
            </div>
          ` : ''}

          ${isSample && tc.input ? `
            <div class="tc-block">
              <div class="tc-label">Input</div>
              <pre class="tc-code">${tc.input}</pre>
            </div>
          ` : ''}

          ${isSample && tc.expected ? `
            <div class="tc-block">
              <div class="tc-label">Expected Output</div>
              <pre class="tc-code">${tc.expected}</pre>
            </div>
          ` : ''}

          ${isSample && tc.output !== null && tc.output !== undefined ? `
            <div class="tc-block">
              <div class="tc-label">Your Output</div>
              <pre class="tc-code">${tc.output || '(empty)'}</pre>
            </div>
          ` : ''}

          ${!isSample ? `
            <div style="margin-top:8px;font-size:12px;color:#64748b;font-style:italic;">
              Hidden test case
            </div>
          ` : ''}
        </div>
      `;
    }).join('');
  }

  load();
})();
</script>

</body>
</html>