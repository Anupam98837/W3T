<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Coding Result</title>

<style>
/* =============================
   BASE
============================= */
* {
  box-sizing: border-box;
}

body {
  font-family: "Inter", Arial, sans-serif;
  font-size: 12.5px;
  line-height: 1.55;
  color: #0f172a;
  margin: 0;
  padding: 24px;
  background: #fff;
}

h1 {
  font-size: 22px;
  margin-bottom: 6px;
}

h3 {
  font-size: 15px;
  margin-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 4px;
}

p {
  margin: 4px 0;
}

/* =============================
   LAYOUT
============================= */
.box {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px;
  margin-bottom: 14px;
  page-break-inside: avoid;
}

.row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* =============================
   BADGES
============================= */
.badge-pass,
.badge-fail {
  font-weight: 700;
  font-size: 11px;
  padding: 6px 12px;
  border-radius: 999px;
  letter-spacing: .4px;
}

.badge-pass {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #86efac;
}

.badge-fail {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* =============================
   CODE BLOCK
============================= */
pre {
  background: #020617;
  color: #e5e7eb;
  padding: 14px;
  border-radius: 8px;
  font-size: 11.5px;
  overflow-wrap: break-word;
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.4;
  page-break-inside: avoid;
}

/* =============================
   TABLE
============================= */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 8px;
  font-size: 11.5px;
}

th {
  background: #f8fafc;
  font-weight: 700;
}

th,
td {
  border: 1px solid #e5e7eb;
  padding: 8px;
  text-align: left;
  vertical-align: top;
}

tbody tr:nth-child(even) {
  background: #fafafa;
}

/* =============================
   PRINT OPTIMIZATION
============================= */
@media print {
  body {
    padding: 16px;
  }

  h1 {
    page-break-after: avoid;
  }

  .box {
    page-break-inside: avoid;
  }

  table {
    page-break-inside: auto;
  }

  tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }

  thead {
    display: table-header-group;
  }

  pre {
    page-break-inside: avoid;
  }
}
</style>

</head>

<body>

<h1>Coding Result</h1>
<p><strong>Generated at:</strong> {{ $generated_at }}</p>

<div class="box">
  <h3>Student</h3>
  <p><strong>Name:</strong> {{ $student['name'] }}</p>
  <p><strong>Email:</strong> {{ $student['email'] }}</p>
</div>

<div class="box">
  <h3>Question</h3>
  <p><strong>Title:</strong> {{ $question['title'] }}</p>
  <p><strong>Difficulty:</strong> {{ ucfirst($question['difficulty']) }}</p>
  <div>{!! $question['description'] !!}</div>
</div>

<div class="box">
  <h3>Result Summary</h3>
  <div class="row">
    <div>
      <p><strong>Score:</strong> {{ $result['marks_obtained'] }} / {{ $result['marks_total'] }}</p>
      <p><strong>Percentage:</strong> {{ number_format($result['percentage'], 2) }}%</p>
      <p><strong>Tests:</strong> {{ $result['passed_tests'] }} / {{ $result['total_tests'] }}</p>
    </div>
    <div>
      @if($result['all_pass'])
        <span class="badge-pass">PASSED</span>
      @else
        <span class="badge-fail">FAILED</span>
      @endif
    </div>
  </div>
</div>

<div class="box">
  <h3>Timing</h3>
  <p><strong>Started:</strong> {{ $timing['started_at'] }}</p>
  <p><strong>Finished:</strong> {{ $timing['finished_at'] }}</p>
  <p><strong>Total Time:</strong> {{ $timing['total_time_ms'] }} ms</p>
</div>

<div class="box">
  <h3>Submitted Code ({{ $submission['language'] }})</h3>
  <pre>{{ $submission['code'] }}</pre>
</div>

<div class="box">
  <h3>Test Case Results</h3>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Status</th>
        <th>Score</th>
        <th>Time (ms)</th>
        <th>Failure Reason</th>
      </tr>
    </thead>
    <tbody>
      @foreach($testcases as $tc)
        <tr>
          <td>{{ $tc['test_id'] }}</td>
          <td>{{ strtoupper($tc['status']) }}</td>
          <td>{{ $tc['earned_score'] }} / {{ $tc['score'] }}</td>
          <td>{{ $tc['time_ms'] }}</td>
          <td>{{ $tc['failure_reason'] ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

@if(!empty($print))
<script>
  window.onload = () => {
    window.print();
  };
</script>
@endif

</body>
</html>
