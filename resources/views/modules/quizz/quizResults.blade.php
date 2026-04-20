@php
  $initialQuizKey = $initialQuizKey ?? null;
  $initialBatchKey = $initialBatchKey ?? null;
@endphp

@push('styles')
<style>
  .qzr-shell {
    display: grid;
    gap: 1rem;
  }

  .qzr-hero,
  .qzr-panel,
  .qzr-table-card,
  .qzr-summary-card {
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: 18px;
    box-shadow: var(--shadow-1);
  }

  .qzr-hero {
    padding: 1.25rem;
    background:
      radial-gradient(circle at top right, rgba(13, 148, 136, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(15, 23, 42, 0.02), rgba(13, 148, 136, 0.05));
  }

  .qzr-title {
    margin: 0;
    font-family: var(--font-head);
    font-size: 1.45rem;
    color: var(--ink);
  }

  .qzr-subtitle {
    margin: 0.35rem 0 0;
    color: var(--muted-color);
    max-width: 72ch;
  }

  .qzr-panel {
    padding: 1rem;
  }

  .qzr-filter-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.9rem;
    align-items: end;
  }

  .qzr-label {
    display: block;
    margin-bottom: 0.4rem;
    font-size: 0.83rem;
    font-weight: 600;
    color: var(--muted-color);
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .qzr-hint {
    margin-top: 0.75rem;
    color: var(--muted-color);
    font-size: 0.92rem;
  }

  .qzr-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.9rem;
  }

  .qzr-summary-card {
    padding: 1rem;
  }

  .qzr-summary-label {
    color: var(--muted-color);
    font-size: 0.85rem;
  }

  .qzr-summary-value {
    margin-top: 0.35rem;
    font-family: var(--font-head);
    font-size: 1.6rem;
    color: var(--ink);
    line-height: 1;
  }

  .qzr-table-card {
    overflow: hidden;
  }

  .qzr-table-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1rem 1rem 0;
  }

  .qzr-table-title {
    margin: 0;
    font-family: var(--font-head);
    font-size: 1.05rem;
    color: var(--ink);
  }

  .qzr-table-sub {
    color: var(--muted-color);
    font-size: 0.9rem;
  }

  .qzr-empty {
    padding: 2.2rem 1rem;
    text-align: center;
    color: var(--muted-color);
  }

  .qzr-empty i {
    font-size: 1.8rem;
    margin-bottom: 0.75rem;
    opacity: 0.75;
  }

  .qzr-name {
    font-weight: 600;
    color: var(--ink);
  }

  .qzr-email {
    color: var(--muted-color);
    font-size: 0.85rem;
  }

  .qzr-actions .btn {
    border-radius: 10px;
  }

  @media (max-width: 992px) {
    .qzr-filter-grid,
    .qzr-summary-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 640px) {
    .qzr-filter-grid,
    .qzr-summary-grid {
      grid-template-columns: 1fr;
    }

    .qzr-table-head {
      flex-direction: column;
      align-items: flex-start;
    }
  }
</style>
@endpush

@section('content')
<div class="qzr-shell">
  <section class="qzr-hero">
    <h1 class="qzr-title">Quiz Results</h1>
    <p class="qzr-subtitle">
      Select a batch, then choose an exam to see every submitted student result in one place. Each row includes a quick action to open the full result view.
    </p>
  </section>

  <section class="qzr-panel">
    <div class="qzr-filter-grid">
      <div>
        <label class="qzr-label" for="qzrBatch">Batch</label>
        <select id="qzrBatch" class="form-select">
          <option value="">Select a batch</option>
        </select>
      </div>

      <div>
        <label class="qzr-label" for="qzrExam">Exam</label>
        <select id="qzrExam" class="form-select" disabled>
          <option value="">Select an exam</option>
        </select>
      </div>

      <div>
        <label class="qzr-label" for="qzrSearch">Student Name</label>
        <input id="qzrSearch" type="text" class="form-control" placeholder="Search student name">
      </div>

      <div>
        <button id="qzrReset" type="button" class="btn btn-primary w-100">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
      </div>
    </div>

    <div id="qzrHint" class="qzr-hint">Loading batches...</div>
  </section>

  <section class="qzr-summary-grid">
    <article class="qzr-summary-card">
      <div class="qzr-summary-label">Submitted Results</div>
      <div id="qzrSubmittedCount" class="qzr-summary-value">0</div>
    </article>

    <article class="qzr-summary-card">
      <div class="qzr-summary-label">Pending Students</div>
      <div id="qzrPendingCount" class="qzr-summary-value">0</div>
    </article>

    <article class="qzr-summary-card">
      <div class="qzr-summary-label">Total Enrolled</div>
      <div id="qzrEnrolledCount" class="qzr-summary-value">0</div>
    </article>
  </section>

  <section class="qzr-table-card">
    <div class="qzr-table-head">
      <div>
        <h2 class="qzr-table-title">Student Results</h2>
        <div id="qzrTableMeta" class="qzr-table-sub">Choose a batch and exam to load results.</div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 56px;">#</th>
            <th>Student</th>
            <th style="width: 110px;">Attempt</th>
            <th style="width: 170px;">Marks</th>
            <th style="width: 120px;">Status</th>
            <th style="width: 180px;">Submitted</th>
            <th class="text-end" style="width: 96px;">Action</th>
          </tr>
        </thead>
        <tbody id="qzrRows">
          <tr>
            <td colspan="7" class="qzr-empty">
              <div>
                <i class="fa fa-square-poll-vertical"></i>
                <div>Choose a batch and exam to load results.</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const token = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const batchSelect = document.getElementById('qzrBatch');
  const examSelect = document.getElementById('qzrExam');
  const searchInput = document.getElementById('qzrSearch');
  const resetButton = document.getElementById('qzrReset');
  const hintEl = document.getElementById('qzrHint');
  const tableMetaEl = document.getElementById('qzrTableMeta');
  const rowsEl = document.getElementById('qzrRows');
  const submittedCountEl = document.getElementById('qzrSubmittedCount');
  const pendingCountEl = document.getElementById('qzrPendingCount');
  const enrolledCountEl = document.getElementById('qzrEnrolledCount');

  let batches = [];
  let exams = [];
  let submittedResults = [];
  let summary = { total_submitted: 0, total_not_submitted: 0, total_enrolled: 0 };
  let pendingQuizKey = @json($initialQuizKey);
  let pendingBatchKey = @json($initialBatchKey);

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatDateTime(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString();
  }

  function statusBadge(status) {
    const value = String(status || '').toLowerCase();
    let cls = 'secondary';
    let label = status || '—';

    if (value === 'pass' || value === 'submitted') {
      cls = 'success';
      label = value === 'submitted' ? 'Submitted' : 'Pass';
    } else if (value === 'fail') {
      cls = 'danger';
      label = 'Fail';
    } else if (value === 'auto_submitted') {
      cls = 'warning';
      label = 'Auto Submitted';
    }

    return `<span class="badge bg-${cls}-subtle text-${cls} border border-${cls}-subtle">${escapeHtml(label)}</span>`;
  }

  function authHeaders() {
    return {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    };
  }

  async function fetchJson(url) {
    const response = await fetch(url, { headers: authHeaders() });
    const json = await response.json().catch(() => ({}));

    if (!response.ok || json.success === false) {
      throw new Error(json.message || json.error || `Request failed with ${response.status}`);
    }

    return json;
  }

  function setHint(message) {
    if (hintEl) hintEl.textContent = message;
  }

  function setSummary(nextSummary) {
    summary = Object.assign({
      total_submitted: 0,
      total_not_submitted: 0,
      total_enrolled: 0,
    }, nextSummary || {});

    submittedCountEl.textContent = String(summary.total_submitted || 0);
    pendingCountEl.textContent = String(summary.total_not_submitted || 0);
    enrolledCountEl.textContent = String(summary.total_enrolled || 0);
  }

  function renderEmpty(message) {
    rowsEl.innerHTML = `
      <tr>
        <td colspan="7" class="qzr-empty">
          <div>
            <i class="fa fa-square-poll-vertical"></i>
            <div>${escapeHtml(message)}</div>
          </div>
        </td>
      </tr>`;
  }

  function selectedExamOption() {
    return examSelect.options[examSelect.selectedIndex] || null;
  }

  function renderResults() {
    const term = String(searchInput.value || '').trim().toLowerCase();
    const rows = [...submittedResults]
      .sort((a, b) => {
        const nameA = String(a.student_name || '').toLowerCase();
        const nameB = String(b.student_name || '').toLowerCase();
        if (nameA !== nameB) return nameA.localeCompare(nameB);
        return Number(a.attempt_number || 0) - Number(b.attempt_number || 0);
      })
      .filter((row) => {
        if (!term) return true;
        const name = String(row.student_name || '').toLowerCase();
        const email = String(row.student_email || '').toLowerCase();
        return name.includes(term) || email.includes(term);
      });

    tableMetaEl.textContent = rows.length
      ? `${rows.length} student result(s)`
      : 'No student results match the current filters.';

    if (!rows.length) {
      renderEmpty('No submitted student results found.');
      return;
    }

    rowsEl.innerHTML = rows.map((row, index) => {
      const result = row.result || {};
      const resultId = result.result_id || row.result_id || '';
      const scoreText = result.result_id
        ? `${result.marks_obtained ?? 0}/${result.total_marks ?? 0} (${Number(result.percentage ?? 0).toFixed(1)}%)`
        : '—';
      const submittedAt = formatDateTime(row.finished_at || row.started_at);

      return `
        <tr>
          <td class="text-muted">${index + 1}</td>
          <td>
            <div class="qzr-name">${escapeHtml(row.student_name || '—')}</div>
            <div class="qzr-email">${escapeHtml(row.student_email || '')}</div>
          </td>
          <td class="text-muted">#${escapeHtml(row.attempt_number || 0)}</td>
          <td>${escapeHtml(scoreText)}</td>
          <td>${statusBadge(result.result_status || row.status)}</td>
          <td class="text-muted">${escapeHtml(submittedAt)}</td>
          <td class="text-end qzr-actions">
            ${resultId ? `
              <div class="dropdown">
                <button class="btn btn-light btn-sm dd-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="/exam/results/${encodeURIComponent(resultId)}/view">
                      <i class="fa fa-eye"></i> View Result
                    </a>
                  </li>
                </ul>
              </div>` : '<span class="text-muted small">—</span>'}
          </td>
        </tr>`;
    }).join('');
  }

  function populateBatchOptions() {
    batchSelect.innerHTML = '<option value="">Select a batch</option>';

    if (!batches.length) {
      setHint('No batches are available for quiz results.');
      return;
    }

    const fragment = document.createDocumentFragment();
    batches.forEach((batch) => {
      const option = document.createElement('option');
      option.value = String(batch.uuid || batch.id);
      option.textContent = batch.course_title
        ? `${batch.badge_title} • ${batch.course_title}`
        : batch.badge_title;
      fragment.appendChild(option);
    });

    batchSelect.appendChild(fragment);

    const preferred = pendingBatchKey
      && batches.some((batch) => String(batch.uuid || batch.id) === String(pendingBatchKey))
        ? String(pendingBatchKey)
        : (batches.length === 1 ? String(batches[0].uuid || batches[0].id) : '');

    if (preferred) {
      batchSelect.value = preferred;
      pendingBatchKey = null;
    }

    setHint('Select a batch to load its assigned exams.');
  }

  function populateExamOptions() {
    examSelect.disabled = !exams.length;
    examSelect.innerHTML = '<option value="">Select an exam</option>';

    if (!exams.length) {
      return;
    }

    const fragment = document.createDocumentFragment();
    exams.forEach((exam) => {
      const option = document.createElement('option');
      option.value = String(exam.uuid || exam.id);
      option.textContent = exam.title || exam.quiz_name || 'Untitled exam';
      option.dataset.batchQuiz = String(exam.batch_quizzes_uuid || exam.batch_quiz_uuid || '');
      fragment.appendChild(option);
    });
    examSelect.appendChild(fragment);

    const preferred = pendingQuizKey
      && exams.some((exam) => String(exam.uuid || exam.id) === String(pendingQuizKey))
        ? String(pendingQuizKey)
        : (exams.length === 1 ? String(exams[0].uuid || exams[0].id) : '');

    if (preferred) {
      examSelect.value = preferred;
      pendingQuizKey = null;
    }
  }

  function resetResults(message) {
    submittedResults = [];
    setSummary(null);
    tableMetaEl.textContent = message || 'Choose a batch and exam to load results.';
    renderEmpty(message || 'Choose a batch and exam to load results.');
  }

  async function loadBatches() {
    setHint('Loading batches...');
    resetResults('Choose a batch and exam to load results.');

    const params = new URLSearchParams();
    if (pendingQuizKey) params.set('quiz', pendingQuizKey);

    try {
      const url = '/api/exam/results/batches' + (params.toString() ? `?${params.toString()}` : '');
      const json = await fetchJson(url);
      batches = Array.isArray(json.data) ? json.data : [];
      populateBatchOptions();

      if (batchSelect.value) {
        await loadExams(batchSelect.value);
      }
    } catch (error) {
      batches = [];
      setHint(error.message || 'Failed to load batches.');
      renderEmpty(error.message || 'Failed to load batches.');
    }
  }

  async function loadExams(batchKey) {
    exams = [];
    populateExamOptions();
    resetResults('Choose an exam to load results.');

    if (!batchKey) {
      setHint('Select a batch to load its assigned exams.');
      return;
    }

    setHint('Loading exams...');

    try {
      const json = await fetchJson(`/api/batch/${encodeURIComponent(batchKey)}/quizzes?per_page=200`);
      exams = Array.isArray(json.data) ? json.data : [];
      populateExamOptions();

      if (!exams.length) {
        setHint('No exams are assigned to this batch.');
        renderEmpty('No exams are assigned to this batch.');
        return;
      }

      setHint('Select an exam to load submitted student results.');

      if (examSelect.value) {
        await loadResults();
      }
    } catch (error) {
      setHint(error.message || 'Failed to load exams.');
      renderEmpty(error.message || 'Failed to load exams.');
    }
  }

  async function loadResults() {
    const option = selectedExamOption();
    const quizKey = option?.value || '';
    const batchQuizKey = option?.dataset?.batchQuiz || '';

    resetResults('Loading results...');

    if (!quizKey || !batchQuizKey) {
      setHint('Select an exam to load submitted student results.');
      resetResults('Choose an exam to load results.');
      return;
    }

    try {
      const json = await fetchJson(
        `/api/exam/quizzes/${encodeURIComponent(quizKey)}/results?batch_quiz=${encodeURIComponent(batchQuizKey)}`
      );

      submittedResults = Array.isArray(json.submitted) ? json.submitted : [];
      setSummary(json.summary || null);
      setHint(`Showing results for ${json.quiz?.name || option.textContent || 'the selected exam'}.`);
      renderResults();
    } catch (error) {
      setHint(error.message || 'Failed to load results.');
      resetResults(error.message || 'Failed to load results.');
    }
  }

  batchSelect.addEventListener('change', async () => {
    pendingBatchKey = null;
    pendingQuizKey = null;
    await loadExams(batchSelect.value);
  });

  examSelect.addEventListener('change', async () => {
    pendingQuizKey = null;
    await loadResults();
  });

  searchInput.addEventListener('input', () => {
    renderResults();
  });

  resetButton.addEventListener('click', async () => {
    batchSelect.value = '';
    examSelect.innerHTML = '<option value="">Select an exam</option>';
    examSelect.disabled = true;
    searchInput.value = '';
    pendingQuizKey = @json($initialQuizKey);
    pendingBatchKey = @json($initialBatchKey);
    await loadBatches();
  });

  loadBatches();
})();
</script>
@endpush
