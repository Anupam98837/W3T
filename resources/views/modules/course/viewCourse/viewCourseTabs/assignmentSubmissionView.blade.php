<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Assignment — Student Documents</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
     <!-- Link to your main.css file -->
<link rel="stylesheet" href="/assets/css/common/main.css">
  <link rel="stylesheet" href="/assets/css/common/extraStylings.css">

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: var(--font-sans);
      background: var(--bg-body);
      color: var(--text-color);
      margin: 0;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      line-height: 1.5;
    }

    .page-wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 18px;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .title {
      font-family: var(--font-head);
      font-weight: 700;
      font-size: var(--fs-20);
      color: var(--ink);
      margin: 0;
    }

    .hint {
      color: var(--muted-color);
      font-size: var(--fs-14);
    }

    .container {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 18px;
    }

    .sidebar {
      background: var(--surface);
      border-radius: var(--radius-1);
      padding: 18px;
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-1);
      /* height: fit-content; */
    }

    .sidebar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .sidebar-header strong {
      font-family: var(--font-head);
      font-weight: 600;
      color: var(--ink);
    }

    .students-count {
      color: var(--muted-color);
      font-size: var(--fs-13);
    }

    .assignment-details {
      background: linear-gradient(135deg, rgba(149,30,170,0.05) 0%, rgba(149,30,170,0.02) 100%);
      border: 1px solid rgba(149,30,170,0.12);
      border-radius: var(--radius-1);
      padding: 16px;
      margin-bottom: 20px;
    }

    .assignment-details h3 {
      font-family: var(--font-head);
      font-weight: 600;
      color: var(--ink);
      margin: 0 0 12px 0;
      font-size: var(--fs-15);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .assignment-details h3 i {
      color: var(--primary-color);
    }

    .detail-item {
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    .detail-label {
      font-size: var(--fs-13);
      color: var(--muted-color);
      font-weight: 500;
    }

    .detail-value {
      font-size: var(--fs-13);
      color: var(--ink);
      font-weight: 500;
      text-align: right;
      max-width: 60%;
    }

    .due-date {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: rgba(149,30,170,0.08);
      color: var(--primary-color);
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
      font-size: var(--fs-12);
    }

    .student-pill {
      display: flex;
      gap: 12px;
      align-items: center;
      padding: 12px;
      border-radius: 8px;
      cursor: pointer;
      transition: var(--transition);
      border: 1px solid transparent;
    }

    .student-pill:hover {
      background: color-mix(in oklab, var(--primary-color) 4%, transparent);
    }

    .student-pill.active {
      background: linear-gradient(90deg, rgba(149,30,170,0.06), rgba(149,30,170,0.03));
      border: 1px solid rgba(149,30,170,0.12);
    }

    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 999px;
      background: linear-gradient(135deg, #f1e5f4, #f9f0fc);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: var(--primary-color);
      font-family: var(--font-head);
      flex-shrink: 0;
    }

    .student-info {
      flex: 1;
      min-width: 0; /* Allow text truncation */
    }

    .student-name {
      font-weight: 600;
      color: var(--ink);
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .student-email {
      color: var(--muted-color);
      font-size: var(--fs-13);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .submission-count {
      color: var(--muted-color);
      font-size: var(--fs-13);
      flex-shrink: 0;
    }
    /* Attempt marks badge */
.attempt-marks {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 8px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.82rem;
  background: color-mix(in oklab, var(--primary-color) 10%, transparent);
  color: var(--primary-color);
  border: 1px solid color-mix(in oklab, var(--primary-color) 12%, transparent);
  margin-left: 10px;
}
html.theme-dark .attempt-marks {
  background: color-mix(in oklab, var(--primary-color) 10%, var(--surface));
  color: var(--surface);
  border-color: color-mix(in oklab, var(--primary-color) 20%, var(--line-strong));
}


    .content {
      background: var(--surface);
      border-radius: var(--radius-1);
      padding: 18px;
      border: 1px solid var(--line-strong);
      box-shadow: var(--shadow-1);
    }

    .student-detail-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 18px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--line-soft);
    }

    .student-name-large {
      font-family: var(--font-head);
      font-weight: 700;
      color: var(--ink);
      margin: 0;
      font-size: var(--fs-20);
    }

    .student-email-large {
      color: var(--muted-color);
      font-size: var(--fs-14);
      margin: 4px 0 0 0;
    }

    .student-stats {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .submission-badge {
      background: linear-gradient(135deg, var(--primary-color), color-mix(in oklab, var(--primary-color) 80%, #8a2be2));
      color: white;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: var(--fs-13);
      display: flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 2px 8px rgba(149,30,170,0.2);
      flex-shrink: 0;
    }

    /* NEW: Give Marks Button */
    .give-marks-btn {
      background: linear-gradient(135deg, var(--success-color), color-mix(in oklab, var(--success-color) 80%, #27ae60));
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      font-weight: 600;
      font-size: var(--fs-13);
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 2px 8px rgba(39, 174, 96, 0.2);
      flex-shrink: 0;
    }

    .give-marks-btn:hover {
      filter: brightness(1.1);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }

    .give-marks-btn:active {
      transform: translateY(0);
    }

    .documents-section {
      margin-top: 18px;
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .section-title {
      font-family: var(--font-head);
      font-weight: 600;
      color: var(--ink);
      margin: 0;
    }

    .attempt-section {
      margin-bottom: 24px;
    }

   /* tighten attempt header spacing and group actions on the right */
.attempt-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;           /* small gap between left and right groups */
}

/* left title should not take unnecessary space (keeps it compact) */
.attempt-title {
  font-weight: 600;
  color: var(--ink);
   display: flex;
  align-items: center;
  gap: 12px;          /* space between Attempt text and View Marks */
  margin-bottom: 12px; /* bottom spacing you wanted */
  margin: 0;
  margin-right: 8px;
  white-space: nowrap;
}



/* right-side action group (View Marks + submitted date) */
.attempt-right {
  display: flex;
  align-items: center;
  gap: 12px;           /* gap between View Marks and submitted date */
  white-space: nowrap;
}

/* smaller/more compact submitted text */
.attempt-submitted {
  color: var(--muted-color);
  font-size: 0.9rem;
  font-weight: 600;
}

/* make View Marks button sit tightly next to Attempt */
.view-marks-btn {
  margin: 0;            /* remove default margin that can push things apart */
  padding: 6px 10px;
}

    .attempt-title {
      font-weight: 600;
      color: var(--ink);
      margin: 0;
    }

    .attempt-meta {
      color: var(--muted-color);
      font-size: var(--fs-13);
    }

    .doc-grid {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .doc-card {
      border-radius: var(--radius-1);
      margin-top:12px;
      padding: 16px;
      border: 1px solid var(--line-strong);
      background: var(--surface);
      box-shadow: var(--shadow-1);
      transition: var(--transition);
    }

    .doc-card:hover {
      box-shadow: var(--shadow-2);
      transform: translateY(-1px);
    }

    .doc-card-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .doc-info {
      display: flex;
      align-items: center;
      gap: 16px;
      flex: 1;
      min-width: 0; /* Allow text truncation */
    }

    .doc-icon {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--bg-body);
      border: 1px solid var(--line-soft);
      flex-shrink: 0;
    }

    .doc-icon i {
      font-size: 20px;
    }

    .icon-pdf {
      color: var(--danger-color);
    }

    .icon-image {
      color: var(--success-color);
    }

    .icon-doc {
      color: var(--info-color);
    }

    .icon-default {
      color: var(--muted-color);
    }

    .doc-content {
      flex: 1;
      min-width: 0; /* Allow text truncation */
    }

    .doc-name {
      font-weight: 600;
      color: var(--ink);
      margin-bottom: 4px;
      white-space: normal;
      word-wrap: break-word;
      word-break: break-word;
      overflow-wrap: break-word;
    }

    .doc-meta {
      color: var(--muted-color);
      font-size: var(--fs-13);
    }

    .doc-actions {
      display: flex;
      gap: 8px;
      flex-shrink: 0;
      margin-left: auto;
    }

    .btn-icon {
      width: 36px;
      height: 36px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
      cursor: pointer;
      border: 1px solid;
      font-size: 14px;
      text-decoration: none;
    }

    .btn-icon-primary {
      background: var(--primary-color);
      border-color: var(--primary-color);
      color: #fff;
    }

    .btn-icon-primary:hover {
      filter: brightness(.96);
      transform: translateY(-1px);
    }

    .btn-icon-outline {
      background: transparent;
      border-color: var(--line-strong);
      color: var(--text-color);
    }

    .btn-icon-outline:hover {
      background: var(--line-soft);
      transform: translateY(-1px);
    }

    .view-ui-link {
      background: transparent;
      border: 1px dashed rgba(149,30,170,0.16);
      padding: 6px 10px;
      border-radius: 8px;
      color: var(--primary-color);
      cursor: pointer;
      font-size: var(--fs-13);
      transition: var(--transition);
    }

    .view-ui-link:hover {
      background: color-mix(in oklab, var(--primary-color) 8%, transparent);
    }

    /* Fullscreen Viewer */
    .as-fullscreen {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.85);
      z-index: 2147483647;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 18px;
    }

    .fs-inner {
      width: 100%;
      height: 100%;
      max-width: 1400px;
      /* max-height: 92vh; */
      background: var(--surface);
      border-radius: var(--radius-1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shadow-3);
    }

    .fs-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      border-bottom: 1px solid var(--line-strong);
      background: var(--bg-body);
    }

    .fs-title {
      font-weight: 700;
      font-size: var(--fs-16);
      color: var(--ink);
    }

    .fs-close {
      border: 0;
      background: transparent;
      font-size: 18px;
      cursor: pointer;
      padding: 6px 10px;
      color: var(--muted-color);
      transition: var(--transition);
    }

    .fs-close:hover {
      color: var(--ink);
    }

    .fs-body {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 10px;
      background: var(--surface);
    }

    .as-fullscreen iframe,
    .as-fullscreen img,
    .as-fullscreen video {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border: 0;
    }

    .as-empty {
      border: 1px dashed var(--line-strong);
      border-radius: var(--radius-1);
      padding: 32px;
      background: transparent;
      color: var(--muted-color);
      text-align: center;
    }

    .as-loader {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--muted-color);
      justify-content: center;
      padding: 20px;
    }

    .fa-spin {
      animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* NEW: Grade Modal Styles */
    .grade-modal {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      z-index: 2147483647;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .grade-modal-inner {
      width: 100%;
      max-width: 800px;
      max-height: 95vh;
      background: var(--surface);
      border-radius: var(--radius-1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shadow-3);
      border: 1px solid var(--line-strong);
    }

    .grade-modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 24px;
      border-bottom: 1px solid var(--line-strong);
      background: var(--bg-body);
    }

    .grade-modal-title {
      font-family: var(--font-head);
      font-weight: 700;
      font-size: var(--fs-18);
      color: var(--ink);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .grade-modal-title i {
      color: var(--success-color);
    }

    .grade-modal-close {
      border: 0;
      background: transparent;
      font-size: 18px;
      cursor: pointer;
      padding: 6px 10px;
      color: var(--muted-color);
      transition: var(--transition);
      border-radius: 4px;
    }

    .grade-modal-close:hover {
      color: var(--ink);
      background: var(--line-soft);
    }

    .grade-modal-body {
      flex: 1;
      overflow-y: auto;
      padding: 24px;
    }

    .grade-modal-footer {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 12px;
      padding: 20px 24px;
      border-top: 1px solid var(--line-strong);
      background: var(--bg-body);
    }

    /* Form Styles */
    .grade-form-group {
      margin-bottom: 20px;
    }

    .grade-form-label {
      display: block;
      font-weight: 600;
      color: var(--ink);
      margin-bottom: 8px;
      font-size: var(--fs-14);
    }

    .grade-form-input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid var(--line-strong);
      border-radius: var(--radius-1);
      background: var(--surface);
      color: var(--text-color);
      font-size: var(--fs-14);
      transition: var(--transition);
    }

    .grade-form-input:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(149, 30, 170, 0.1);
    }

    .grade-form-textarea {
      min-height: 80px;
      resize: vertical;
    }

    .grade-form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    /* Submissions List */
    .submissions-list {
      max-height: 200px;
      overflow-y: auto;
      border: 1px solid var(--line-strong);
      border-radius: var(--radius-1);
      margin-bottom: 20px;
    }

    .submission-item {
      padding: 12px;
      border-radius: 10px;
      border-bottom: 1px solid var(--line-soft);
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .submission-item:last-child {
      border-bottom: none;
    }

    .submission-item:hover {
      background: var(--bg-body);
    }

    .submission-item.selected {
      background: color-mix(in oklab, var(--success-color) 8%, transparent);
      border-left: 3px solid var(--success-color);
    }

    .submission-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 8px;
    }

    .submission-attempt {
      font-weight: 600;
      color: var(--ink);
    }

    .submission-date {
      color: var(--muted-color);
      font-size: var(--fs-12);
    }

    .submission-meta {
      display: flex;
      gap: 12px;
      font-size: var(--fs-12);
      color: var(--muted-color);
    }

    .submission-meta span {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .submission-select-btn {
      background: transparent;
      border: 1px solid var(--line-strong);
      color: var(--text-color);
      padding: 6px 12px;
      border-radius: 4px;
      font-size: var(--fs-12);
      cursor: pointer;
      transition: var(--transition);
    }

    .submission-select-btn:hover {
      background: var(--line-soft);
    }

    .submission-select-btn.selected {
      background: var(--success-color);
      border-color: var(--success-color);
      color: white;
    }

    /* Alert Styles */
    .grade-alert {
      padding: 12px 16px;
      border-radius: var(--radius-1);
      margin-bottom: 20px;
      font-size: var(--fs-14);
      display: none;
    }

    .grade-alert.error {
      background: color-mix(in oklab, var(--danger-color) 10%, transparent);
      border: 1px solid color-mix(in oklab, var(--danger-color) 20%, transparent);
      color: var(--danger-color);
    }

    .grade-alert.success {
      background: color-mix(in oklab, var(--success-color) 10%, transparent);
      border: 1px solid color-mix(in oklab, var(--success-color) 20%, transparent);
      color: var(--success-color);
    }

    /* Loading State */
    .grade-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 20px;
      color: var(--muted-color);
    }

    /* Button Styles */
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: var(--radius-1);
      font-weight: 600;
      font-size: var(--fs-14);
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .btn-primary {
      background: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      filter: brightness(1.1);
      transform: translateY(-1px);
    }

    .btn-secondary {
      background: var(--line-strong);
      color: var(--text-color);
    }

    .btn-secondary:hover {
      background: var(--line-strong);
      filter: brightness(0.95);
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none !important;
    }

    /* Fix: consistent sizing + alignment for two-column grade form */
    .grade-form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      align-items: start;     /* ensure both columns align at top */
    }

    /* ensure form groups stretch to full column height and align children */
    .grade-form-row .grade-form-group {
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      gap: 8px;
    }

    /* Unified box model so width/height calculations are predictable */
    .grade-form-input,
    select.grade-form-input,
    .grade-form-input[type="number"],
    .grade-form-input[type="text"] {
      box-sizing: border-box; /* <- important */
      width: 100%;
      min-height: 44px;       /* fixed visual height for inputs */
      line-height: 1.25;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--line-strong);
      background: var(--surface);
      font-size: 14px;
      vertical-align: middle;
    }

    /* textarea stays taller */
    .grade-form-textarea {
      box-sizing: border-box;
      min-height: 92px;
      padding: 12px;
      border-radius: 10px;
      resize: vertical;
    }

    /* ensure select inherits same height & alignment */
    select.grade-form-input { min-height: 44px; }

    /* Back pill (matches provided image) */
    .back-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 10px;
      border: 2px solid rgba(149,30,170,0.95);
      background: transparent;
      color: rgba(149,30,170,0.95);
      font-weight: 600;
      font-size: var(--fs-14);
      line-height: 1;
      cursor: pointer;
      min-height: 20px;
      transition: background 160ms ease, transform 120ms ease, box-shadow 120ms ease;
    }

    /* arrow icon sizing */
    .back-pill .back-icon {
      font-size: var(--fs-14);
      display: inline-block;
      margin-left: -2px; /* tighten space to match image */
    }

    /* hover / focus */
    .back-pill:hover {
      background: color-mix(in oklab, rgba(149,30,170,0.12) 12%, transparent);
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(149,30,170,0.06);
    }

    .back-pill:active { transform: translateY(0); }
    .back-pill:focus { outline: 0; box-shadow: 0 0 0 4px rgba(149,30,170,0.08); border-color: rgba(149,30,170,1); }

    /* base pill (use on #assignment_status or .due-date) */
    #assignment_status {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 0.85rem;
      line-height: 1;
      color: var(--muted-color);      /* default text color (overridden by variants) */
      background: transparent;        /* default bg */
      border: 1px solid rgba(0,0,0,0.06);
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      text-transform: capitalize;
      min-width: 64px;
      justify-content: center;
      white-space: nowrap;
    }

    /* variant colors — add one of these classes to the element */
    #assignment_status.status-published,
    .due-date.status-published {
      background: linear-gradient(90deg,#6f42c1,#9b59ff);
      color: #fff;
      border-color: rgba(149,30,170,0.12);
    }

    #assignment_status.status-draft,
    .due-date.status-draft {
      background: linear-gradient(90deg,#6c757d,#495057);
      color: #f3e7e7ff;
      border-color: rgba(0,0,0,0.06);
    }

    #assignment_status.status-due-soon,
    .due-date.status-due-soon {
      background: linear-gradient(90deg,#ffb84d,#ff8a00);
      color: #111;
      border-color: rgba(255,138,0,0.12);
    }

    #assignment_status.status-overdue,
    .due-date.status-overdue {
      background: linear-gradient(90deg,#ff6b6b,#ff4757);
      color: #fff;
      border-color: rgba(255,71,87,0.12);
    }

    #assignment_status.status-closed {
      background: linear-gradient(90deg,#7f8fa6,#4b5860);
      color: #fff;
      border-color: rgba(0,0,0,0.06);
    }

    /* ============================================== */
    /* RESPONSIVE DESIGN - MEDIA QUERIES */
    /* ============================================== */

    /* Large tablets and small desktops */
    @media (max-width: 1024px) {
      .page-wrap {
        padding: 16px;
      }
      
      .container {
        gap: 16px;
      }
      
      .sidebar, .content {
        padding: 16px;
      }
    }

    /* Tablets */
    @media (max-width: 900px) {
      .container {
        grid-template-columns: 1fr;
      }
      
      .sidebar {
        height: auto;
      }
      
      .student-detail-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }
      
      .student-stats {
        width: 100%;
        justify-content: space-between;
      }
      
      .doc-card-inner {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }
      
      .doc-info {
        width: 100%;
      }
      
      .doc-actions {
        width: 100%;
        justify-content: flex-end;
        margin-left: 0;
      }
      
      .grade-form-row {
        grid-template-columns: 1fr;
      }
    }

    /* Large phones */
    @media (max-width: 768px) {
      .page-wrap {
        padding: 14px;
      }
      
      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }
      
      .title {
        font-size: var(--fs-18);
      }
      
      .sidebar, .content {
        padding: 14px;
      }
      
      .student-name-large {
        font-size: var(--fs-18);
      }
      
      .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }
      
      .grade-modal-footer {
        flex-direction: column;
      }
      
      .grade-modal-footer .btn {
        width: 100%;
        justify-content: center;
      }
      
      .student-pill {
        padding: 10px;
      }
      
      .avatar {
        width: 36px;
        height: 36px;
      }
      
      .assignment-details {
        padding: 14px;
      }
    }

    /* Medium phones */
    @media (max-width: 600px) {
      .page-wrap {
        padding: 12px;
      }
      
      .sidebar, .content {
        padding: 12px;
      }
      
      .student-stats {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }
      
      .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
      }
      
      .detail-value {
        text-align: left;
        max-width: 100%;
      }
      
      .attempt-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
      }
      
      .doc-icon {
        width: 42px;
        height: 42px;
      }
      
      .doc-icon i {
        font-size: 18px;
      }
      
      .back-pill { 
        font-size: 13px; 
        min-height: 18px; 
        padding: 5px 10px;
      }
      
      .back-pill .back-icon { 
        font-size: 13px; 
      }
      
      #assignment_status, .due-date {
        padding: 5px 8px;
        font-size: 0.78rem;
        min-width: 54px;
      }
      
      .submission-meta {
        flex-wrap: wrap;
        gap: 8px;
      }
    }

    /* Small phones */
    @media (max-width: 480px) {
      .page-wrap {
        padding: 10px;
      }
      
      .sidebar, .content {
        padding: 10px;
      }
      
      .student-name-large {
        font-size: var(--fs-16);
      }
      
      .student-email-large {
        font-size: var(--fs-13);
      }
      
      .submission-badge,
      .give-marks-btn {
        font-size: var(--fs-12);
        padding: 5px 10px;
      }
      
      .doc-card {
        padding: 12px;
      }
      
      .doc-info {
        gap: 12px;
      }
      
      .btn-icon {
        width: 32px;
        height: 32px;
        font-size: 12px;
      }
      
      .grade-modal-header,
      .grade-modal-body,
      .grade-modal-footer {
        padding: 16px;
      }
      
      .grade-modal-title {
        font-size: var(--fs-16);
      }
      
      .fs-header {
        padding: 10px 12px;
      }
      
      .fs-title {
        font-size: var(--fs-14);
      }
    }

    /* Very small phones */
    @media (max-width: 360px) {
      .page-wrap {
        padding: 8px;
      }
      
      .sidebar, .content {
        padding: 10px;
      }
      
      .assignment-details {
        padding: 12px;
      }
      
      .student-pill {
        gap: 8px;
      }
      
      .avatar {
        width: 32px;
        height: 32px;
        font-size: var(--fs-12);
      }
      
      .student-name {
        font-size: var(--fs-14);
      }
      
      .student-email {
        font-size: var(--fs-12);
      }
      
      .submission-count {
        font-size: var(--fs-12);
      }
      
      .doc-icon {
        width: 36px;
        height: 36px;
      }
      
      .doc-icon i {
        font-size: 16px;
      }
      
      .doc-name {
        font-size: var(--fs-14);
      }
      
      .doc-meta {
        font-size: var(--fs-12);
      }
    }
    /* ---------- Dark-mode tweaks — rely on main.css tokens only (no :root redefinitions) ---------- */
/* Place this at the end of your stylesheet (after main.css) */

html.theme-dark {
  /* nothing defined here — we rely on tokens from main.css */
}

/* Assignment card subtle tint in dark */
html.theme-dark .assignment-details {
  background: color-mix(in oklab, var(--primary-color) 5%, var(--surface));
  border-color: color-mix(in oklab, var(--primary-color) 10%, var(--line-strong));
}

/* Avatar: keep contrast but use token-driven tints */
html.theme-dark .avatar {
  background: color-mix(in oklab, var(--accent-color) 6%, var(--surface));
  color: var(--accent-color);
  border: 1px solid color-mix(in oklab, var(--accent-color) 8%, var(--line-strong));
}

/* Student pill hover in dark */
html.theme-dark .student-pill:hover {
  background: color-mix(in oklab, var(--primary-color) 6%, transparent);
}

/* Student-pill active */
html.theme-dark .student-pill.active {
  background: color-mix(in oklab, var(--primary-color) 8%, var(--surface));
  border-color: color-mix(in oklab, var(--primary-color) 12%, var(--line-strong));
}

/* Submission badge: use tokens for gradient + softer shadow */
html.theme-dark .submission-badge {
  background: linear-gradient(135deg,
    color-mix(in oklab, var(--primary-color) 24%, transparent) 0%,
    color-mix(in oklab, var(--accent-color) 14%, transparent) 100%);
  box-shadow: 0 6px 18px color-mix(in oklab, var(--primary-color) 8%, transparent);
  color: var(--surface);
}

/* Back pill: remove hard-coded purple and use tokens */
html.theme-dark .back-pill {
  border-color: color-mix(in oklab, var(--primary-color) 95%, var(--line-strong));
  color: var(--primary-color);
  background: transparent;
  box-shadow: none;
}
html.theme-dark .back-pill:hover {
  background: color-mix(in oklab, var(--primary-color) 8%, transparent);
  box-shadow: 0 6px 18px color-mix(in oklab, var(--primary-color) 6%, transparent);
}

/* Doc card / icons contrast in dark */
html.theme-dark .doc-card {
  background: color-mix(in oklab, var(--surface) 100%, var(--surface));
  border-color: var(--line-strong);
}
html.theme-dark .doc-icon { background: color-mix(in oklab, var(--line-strong) 8%, var(--surface)); border-color: var(--line-strong); }

/* Fullscreen / viewer header background */
html.theme-dark .fs-header {
  background: var(--surface);
  border-bottom-color: var(--line-strong);
}
html.theme-dark .fs-close { color: var(--muted-color); }
html.theme-dark .fs-close:hover { color: var(--ink); }

/* Grade modal: use tokens for overlays + inner bg */
html.theme-dark .grade-modal { background: rgba(0,0,0,0.6); }
html.theme-dark .grade-modal-inner { background: var(--surface); border-color: var(--line-strong); box-shadow: var(--shadow-3); }
html.theme-dark .grade-modal-header,
html.theme-dark .grade-modal-footer { background: var(--bg-body); border-color: var(--line-strong); }

/* Buttons and controls: ensure hover states use token-driven tones */
html.theme-dark .btn-primary { background: var(--primary-color); border-color: var(--primary-color); color: var(--surface); }
html.theme-dark .btn-secondary { background: var(--line-strong); color: var(--text-color); }

/* RTE / editor & toolbar were already token-driven; give small dark tweak */
html.theme-dark .tool { background: var(--surface); border-color: var(--line-strong); color: var(--text-color); }
html.theme-dark .rte { background: var(--surface); color: var(--text-color); border-color: var(--line-strong); box-shadow: none; }

/* Empty / loader states */
html.theme-dark .as-empty, html.theme-dark .as-loader { background: transparent; color: var(--muted-color); border-color: var(--line-strong); }

/* Fine tune small hard-coded rgba usages in your styles that referenced specific hex — convert with tokens */
html.theme-dark .avatar { box-shadow: 0 1px 2px color-mix(in oklab, var(--line-strong) 30%, transparent); }
html.theme-dark .doc-card:hover { box-shadow: var(--shadow-2); transform: translateY(-1px); }

/* Accessibility: increase placeholder contrast slightly in dark mode */
html.theme-dark .rte-ph { color: color-mix(in oklab, var(--muted-color) 110%, var(--text-color)); }

/* Keep overlay darkness strong in dark-mode */
html.theme-dark .as-fullscreen { background: rgba(0,0,0,0.9); }
/* ---------- Marks Modal + View Marks button ---------- */
.view-marks-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.82rem;
  cursor: pointer;
  border: 1px solid color-mix(in oklab, var(--primary-color) 14%, transparent);
  background: color-mix(in oklab, var(--primary-color) 6%, transparent);
  color: var(--primary-color);
  transition: transform 120ms ease, box-shadow 120ms ease, filter 120ms ease;
}
.view-marks-btn:hover { transform: translateY(-2px); filter: brightness(1.03); box-shadow: 0 8px 24px color-mix(in oklab, var(--primary-color) 8%, transparent); }
.view-marks-btn:active { transform: translateY(0); }

.marks-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  z-index: 2147483650;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 18px;
}
.marks-modal-inner {
  width: 100%;
  max-width: 680px;
  border-radius: 14px;
  overflow: hidden;
  background: var(--surface);
  box-shadow: 0 20px 50px rgba(2,6,23,0.45);
  border: 1px solid var(--line-strong);
  display: flex;
  flex-direction: column;
}
.marks-modal-header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:18px 20px;
  background: linear-gradient(90deg, color-mix(in oklab, var(--primary-color) 6%, transparent), transparent);
  border-bottom: 1px solid var(--line-strong);
}
.marks-modal-title {
  font-weight:700;
  font-size:1.05rem;
  margin:0;
  color:var(--ink);
}
.marks-modal-close {
  background:transparent;
  border:0;
  font-size:18px;
  cursor:pointer;
  color:var(--muted-color);
  padding:8px;
  border-radius:8px;
}
.marks-modal-close:hover { background: var(--line-soft); color:var(--ink); }

.marks-modal-body {
  padding: 18px 20px;
  display:grid;
  grid-template-columns: 1fr;
  gap: 12px;
  color: var(--text-color);
}

.marks-grid {
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:12px;
}
.marks-row {
  background: color-mix(in oklab, var(--surface) 100%, var(--surface));
  border-radius:10px;
  padding:12px;
  border:1px solid var(--line-strong);
}
.marks-label {
  font-size:0.82rem;
  color:var(--muted-color);
  font-weight:600;
  margin-bottom:6px;
}
.marks-value {
  font-size:1.05rem;
  font-weight:800;
  color:var(--ink);
  word-break:break-word;
}

.marks-note {
  margin-top:6px;
  font-size:0.95rem;
  color:var(--text-color);
  line-height:1.4;
  white-space:pre-wrap;
  background: transparent;
}

/* footer */
.marks-modal-footer {
  display:flex;
  justify-content:flex-end;
  gap:12px;
  padding:14px 20px;
  border-top:1px solid var(--line-strong);
  background: var(--bg-body);
}

/* responsive */
@media (max-width:600px) {
  .marks-grid { grid-template-columns: 1fr; }
  .marks-modal { padding: 12px; }
  .marks-modal-inner { max-width: 100%; border-radius: 12px; }
}

/* end dark-mode tweaks */

  </style>
</head>
<body>
  <div class="page-wrap">
    <div class="header" style="display:none">
      <h1 class="title">Assignment Submissions</h1>
      <div class="hint" id="assignment_hint" style="display:none">Loading assignment...</div>
    </div>

    <div class="container">
      <aside class="sidebar">
        <!-- Assignment details -->
        <div class="assignment-details">
          <h3><i class="fas fa-book"></i> Assignment Details</h3>

          <div class="detail-item">
            <span class="detail-label">Assignment:</span>
            <span class="detail-value" id="assignment_name">Loading...</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Total marks:</span>
            <span class="detail-value" id="assignment_total_marks">Loading...</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Passing marks:</span>
            <span class="detail-value" id="assignment_passing_marks">—</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Late penalty:</span>
            <span class="detail-value" id="assignment_late_penalty">Loading...</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Status:</span>
            <span class="detail-value">
              <!-- status badge — update its text via JS by targeting #assignment_status -->
              <span id="assignment_status" class="due-date" style="background:transparent;color:var(--muted-color);font-weight:600;padding:4px 8px;border-radius:8px;">
                Loading...
              </span>
            </span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Deadline:</span>
            <span class="detail-value" id="due_date">
              <span class="due-date"><i class="fas fa-clock"></i> Loading...</span>
            </span>
          </div>
        </div>

        <div class="sidebar-header">
          <strong>Submitted Students</strong>
          <small class="students-count" id="students_count"></small>
        </div>
        <div id="students_host">
          <div class="as-loader"><i class="fa fa-spinner fa-spin"></i> Loading students...</div>
        </div>
      </aside>

      <main class="content">
        <div class="student-detail-header">
          <div>
            <div class="student-name-large" id="student_name">Select a student</div>
            <div class="student-email-large" id="student_email"></div>
          </div>
          <div class="student-stats" id="student_stats">
            <!-- Back pill -->
            <button id="back_btn" class="back-pill" type="button" aria-label="Go back">
              <i class="fas fa-arrow-left back-icon" aria-hidden="true"></i>
              <span class="back-text">Back</span>
            </button>
            <div class="submission-badge">
              <i class="fas fa-file-upload"></i>
              <span id="submission_count">0</span> Submissions
            </div>
          </div>
        </div>

        <section class="documents-section">
          <div class="section-header">
            <h3 class="section-title">Documents</h3>
            <div class="d-flex align-items-center gap-2">
              <button id="give_marks_btn" class="give-marks-btn d-inline-flex align-items-center">
                <i class="fas fa-check-circle"></i> Give Marks
              </button>
              <button id="open_in_ui" class="view-ui-link d-inline-flex align-items-center" style="display:none !important;">
                <i class="fas fa-external-link-alt"></i>
              </button>
            </div>
          </div>
          <div id="documents_host">
            <div class="as-empty">
              <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i>
              <p>Select a student from the left to view their submitted documents.</p>
            </div>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Fullscreen viewer -->
  <div id="fs_viewer" class="as-fullscreen" style="display:none">
    <div class="fs-inner">
      <div class="fs-header">
        <div class="fs-title" id="fs_title">Document</div>
        <div><button class="fs-close" id="fs_close"><i class="fa fa-times"></i></button></div>
      </div>
      <div class="fs-body" id="fs_body"></div>
    </div>
  </div>
  <!-- Marks Modal -->
<div id="marks_modal" class="marks-modal" style="display:none" aria-hidden="true">
  <div class="marks-modal-inner" role="dialog" aria-modal="true" aria-labelledby="marks_modal_title">
    <div class="marks-modal-header">
      <h3 class="marks-modal-title" id="marks_modal_title">View Marks</h3>
      <button class="marks-modal-close" id="marks_modal_close" aria-label="Close"><i class="fa fa-times"></i></button>
    </div>

    <div class="marks-modal-body">
      <div class="marks-grid">
        <div class="marks-row">
          <div class="marks-label">Marks</div>
          <div class="marks-value" id="mm_marks">—</div>
        </div>

        <div class="marks-row">
          <div class="marks-label">Grade</div>
          <div class="marks-value" id="mm_grade">—</div>
        </div>

        <div class="marks-row" style="grid-column: 1 / -1;">
          <div class="marks-label">Grade note</div>
          <div class="marks-note" id="mm_note">—</div>
        </div>

        <div class="marks-row" style="grid-column: 1 / -1;">
          <div class="marks-label">Graded by</div>
          <div class="marks-value" id="mm_graded_by">—</div>
        </div>
      </div>
    </div>

    <div class="marks-modal-footer">
      <button class="btn btn-secondary" id="mm_close_btn">Close</button>
    </div>
  </div>
</div>

  <!-- Grade Modal -->
  <div id="grade_modal" class="grade-modal" style="display:none">
    <div class="grade-modal-inner">
      <div class="grade-modal-header">
        <h3 class="grade-modal-title"><i class="fas fa-check-circle"></i> <span id="grade_modal_title">Grade Submission</span></h3>
        <button class="grade-modal-close" id="grade_modal_close"><i class="fas fa-times"></i></button>
      </div>

      <div class="grade-modal-body">
        <div id="grade_alert" class="grade-alert error" style="display:none"></div>

        <div id="grade_loading" class="grade-loading" style="display:none"><i class="fa fa-spinner fa-spin"></i> <span>Loading submissions...</span></div>

        <div id="grade_content" style="display:none">
          <div class="submissions-list" id="submissions_list"></div>

          <form id="grade_form">
            <div class="grade-form-row">
              <div class="grade-form-group">
                <label class="grade-form-label">Marks *</label>
                <input type="number" step="0.01" min="0" class="grade-form-input" name="marks" required placeholder="Enter marks">
              </div>
              <div class="grade-form-group">
                <label class="grade-form-label">Grade Letter</label>
                <input type="text" class="grade-form-input" name="grade_letter" placeholder="e.g., A, B+, etc.">
              </div>
            </div>

            <div class="grade-form-group">
              <label class="grade-form-label">Apply Late Penalty</label>
              <select class="grade-form-input" name="apply_late_penalty">
                <option value="true">Yes (apply automatic penalty)</option>
                <option value="false">No (override penalty)</option>
              </select>
            </div>

            <div class="grade-form-group">
              <label class="grade-form-label">Feedback / Notes</label>
              <textarea class="grade-form-input grade-form-textarea" name="grader_note" placeholder="Add feedback for the student..."></textarea>
            </div>

            <input type="hidden" name="submission_id" value="">
            <input type="hidden" name="submission_uuid" value="">
          </form>
        </div>

        <div id="grade_empty" class="as-empty" style="display:none">
          <i class="fas fa-file" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i>
          <p>No submissions found for grading.</p>
        </div>
      </div>

      <div class="grade-modal-footer">
        <button class="btn btn-secondary" id="grade_cancel_btn">Cancel</button>
        <button class="btn btn-primary" id="grade_submit_btn" disabled><i class="fas fa-check"></i> Save Marks</button>
      </div>
    </div>
  </div>

  <script>
/* ---------- AUTH + API HELPERS ---------- */
//const role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
let role = '';

const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
const getMyRole = async token => {
  if (!token) return '';
  const res = await fetch('/api/auth/my-role', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    }
  });
  if (!res.ok) return '';
  const data = await res.json().catch(() => null);
  if (data?.status === 'success' && data?.role) {
    return String(data.role).trim().toLowerCase();
  }
  return '';
};

const apiBase = '/api/assignments';
const defaultHeaders = {
  'Authorization': TOKEN ? ('Bearer ' + TOKEN) : '',
  'Accept': 'application/json'
};
document.documentElement.classList.add('theme-dark'); // enable
document.documentElement.classList.remove('theme-dark'); // disable

async function apiFetch(url, opts = {}) {
  let finalUrl = url;
  if (!String(url).startsWith('/api')) {
    if (String(url).startsWith('/')) finalUrl = apiBase + url;
    else finalUrl = apiBase + '/' + url;
  }
  const headers = Object.assign({}, defaultHeaders, opts.headers || {});
  const finalOpts = Object.assign({}, opts, { headers });
  const res = await fetch(finalUrl, finalOpts);
  if (res.status === 401 || res.status === 403) {
    const errText = await (res.clone().text().catch(()=>'')) || '';
    const err = new Error('UNAUTHORIZED');
    err.status = res.status;
    err.body = errText;
    throw err;
  }
  return res;
}

/* ---------- small helpers ---------- */
function escapeHtml(s){ if (s==null) return ''; return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function looksLikeUuid(s){ if (!s || typeof s !== 'string') return false; return /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(s); }
function formatBytes(bytes, decimals = 2) { if (!bytes || bytes === 0) return '0 B'; const k = 1024; const dm = decimals < 0 ? 0 : decimals; const sizes = ['B','KB','MB','GB','TB']; const i = Math.floor(Math.log(bytes)/Math.log(k)) || 0; return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + (sizes[i]||'B'); }

/* ---------- state & elements ---------- */
const studentsHost = document.getElementById('students_host');
const studentsCountEl = document.getElementById('students_count');
const assignmentHint = document.getElementById('assignment_hint');
const openInUiBtn = document.getElementById('open_in_ui');
const giveMarksBtn = document.getElementById('give_marks_btn');
const courseNameEl = document.getElementById('course_name');
const assignmentNameEl = document.getElementById('assignment_name');
const dueDateEl = document.getElementById('due_date');
const submissionCountEl = document.getElementById('submission_count');

let studentsData = [];
let selectedStudent = null;
let currentAssignment = { id: null, uuid: null, title: null, due_at: null, course_name: null };
window.__ASSIGNMENT_KEY__ = window.__ASSIGNMENT_KEY__ || (location.pathname.split('/').filter(Boolean).includes('assignments') ? location.pathname.split('/').filter(Boolean)[location.pathname.split('/').filter(Boolean).indexOf('assignments')+1] : '13');
window.__PRESELECT_STUDENT__ = window.__PRESELECT_STUDENT__ || null;

/* ---------- UI alert helpers ---------- */
function showAlertInModal(message, type) {
  const alertBox = document.getElementById('grade_alert');
  if (!alertBox) { window.alert(message); return; }
  alertBox.textContent = message;
  alertBox.className = `grade-alert ${type}`;
  alertBox.style.display = 'block';
}
function showAlert(message, type='error') { showAlertInModal(message, type); }

// Robust role getter — reads storage and normalizes variants
function getCurrentRole() {
  return role;
}


/* ---------- fetch student-status and render ---------- */
async function fetchStudentStatus(){
  try{
    const assignmentKey = window.__ASSIGNMENT_KEY__;
    const candidates = [
      `/${assignmentKey}/student-status`,
      `/${assignmentKey}/students/status`,
      `/${assignmentKey}/student-submission-status`,
      `/${assignmentKey}/students/documents`
    ];

    let j = null;
    for(const u of candidates){
      try{
        const r = await apiFetch(u);
        if (!r.ok) continue;
        const candidateJson = await r.json().catch(()=>null);
        if (!candidateJson) continue;
        if (Array.isArray(candidateJson) || candidateJson.data || candidateJson.submitted || candidateJson.students) {
          j = candidateJson;
          break;
        }
      } catch(e){
        if (e && e.status && (e.status === 401 || e.status === 403)) throw e;
      }
    }

    if (!j) throw new Error('Failed to fetch submitted students');

    let arr = [];
    if (j.data && Array.isArray(j.data.submitted)) arr = j.data.submitted;
    else if (j.submitted && Array.isArray(j.submitted)) arr = j.submitted;
    else if (j.data && Array.isArray(j.data.students)) arr = j.data.students;
    else if (Array.isArray(j)) arr = j;
    else if (j.data && Array.isArray(j.data)) arr = j.data;
    else if (j.students && Array.isArray(j.students)) arr = j.students;
    else arr = [];

    if (arr.length === 0 && j.data && j.data.student) arr = [ j.data.student ];

    if (j.data && j.data.assignment) setAssignmentInfo(j.data.assignment);
    else if (j.assignment) setAssignmentInfo(j.assignment);

    studentsData = arr.map(s => ({
      student_id: s.student_id ?? s.id ?? s.studentId ?? s.user_id ?? null,
      student_uuid: s.student_uuid ?? s.uuid ?? s.user_uuid ?? s.userUuid ?? null,
      name: s.student_name ?? s.name ?? s.full_name ?? '',
      email: s.student_email ?? s.email ?? '',
      submission_count: s.submission_count ?? s.total_submissions ?? s.documents_count ?? 1,
      raw: s
    }));

    renderStudents();
  }catch(err){
    if (err && err.status && (err.status === 401 || err.status === 403)) {
      studentsHost.innerHTML = '<div class="as-empty">Access denied. You are not authorized to view submissions.</div>';
      console.warn('Auth error fetching student status', err);
      return;
    }
    console.warn('fetchStudentStatus err',err);
    studentsHost.innerHTML = '<div class="as-empty">Failed to load students.</div>';
  }
}

/* ---------- new: fetch assignment details from GET /assignments/{assignment} ---------- */
async function fetchAssignmentDetails(assignmentKey) {
  if (!assignmentKey) return;

  const endpoints = [
    `/${assignmentKey}`,
    `/${assignmentKey}/show`,
    `/${assignmentKey}/details`
  ];

  for (const ep of endpoints) {
    try {
      const res = await apiFetch(ep);
      if (!res.ok) continue;
      const json = await res.json().catch(()=>null);
      if (!json) continue;

      const assignmentData = json.data?.assignment ?? json.assignment ?? json.data ?? json;

      if (assignmentData && (assignmentData.id || assignmentData.uuid || assignmentData.title)) {
        setAssignmentInfo(assignmentData);

        // render some quick fields (kept for backward compatibility)
        const totalMarks = assignmentData.total_marks ?? assignmentData.max_marks ?? assignmentData.marks_max ?? assignmentData.maximum_marks ?? assignmentData.totalMarks ?? null;
        const latePenaltyRaw = assignmentData.late_penalty ?? assignmentData.late_penalty_percent ?? assignmentData.late_penalty_percentage ?? assignmentData.late_penalty_rate ?? assignmentData.latePenalty ?? assignmentData.latePenaltyPercent ?? null;

        const tmEl = document.getElementById('assignment_total_marks');
        if (tmEl) {
          tmEl.textContent = (totalMarks !== null && totalMarks !== undefined && totalMarks !== '') ? String(totalMarks) : '—';
        }

        const lpEl = document.getElementById('assignment_late_penalty');
        if (lpEl) {
          if (latePenaltyRaw === null || latePenaltyRaw === undefined || latePenaltyRaw === '') {
            lpEl.textContent = '—';
          } else {
            const n = Number(latePenaltyRaw);
            if (!isNaN(n)) {
              if (n > 0 && n <= 1) {
                lpEl.textContent = (n * 100).toFixed(2).replace(/\.00$/,'') + '%';
              } else if (n >= 1 && n <= 100) {
                lpEl.textContent = n.toString().replace(/\.00$/,'') + '%';
              } else {
                lpEl.textContent = String(latePenaltyRaw);
              }
            } else {
              lpEl.textContent = String(latePenaltyRaw);
            }
          }
        }

        return;
      }
    } catch (err) {
      console.warn('fetchAssignmentDetails try failed for', ep, err);
    }
  }

  const tmEl = document.getElementById('assignment_total_marks');
  if (tmEl) tmEl.textContent = '—';
  const lpEl = document.getElementById('assignment_late_penalty');
  if (lpEl) lpEl.textContent = '—';
}

/* ---------- setAssignmentInfo (updated) ---------- */
function setAssignmentInfo(data){
  if (!data) return;

  // merge core properties
  currentAssignment.id = data.id ?? currentAssignment.id;
  currentAssignment.uuid = data.uuid ?? currentAssignment.uuid ?? (looksLikeUuid(data.id? String(data.id):'') ? data.id : currentAssignment.uuid);
  currentAssignment.title = data.title ?? data.name ?? currentAssignment.title;
  currentAssignment.due_at = data.due_at ?? data.dueAt ?? data.due ?? currentAssignment.due_at;
  currentAssignment.course_name = data.course_name ?? data.course ?? data.courseName ?? currentAssignment.course_name;

  // status
  const status = data.status ?? data.assignment_status ?? data.state ?? data.status_text ?? 'Unknown';

  // total marks candidates
  const totalMarksCandidates = [
    'total_marks','totalMarks','max_marks','maxMarks','marks_max','maximum_marks','maximumMarks',
    'marks','full_marks','fullMarks'
  ];
  let totalMarks = null;
  for (const k of totalMarksCandidates) {
    if (typeof data[k] !== 'undefined' && data[k] !== null && data[k] !== '') {
      totalMarks = data[k];
      break;
    }
  }

  // late penalty candidates
  let latePenaltyRaw = null;
  const lateKeys = [
    'late_penalty_percent','late_penalty_percentage','late_penalty_rate',
    'late_penalty_per_day','late_penalty_per_hour','late_penalty_value','latePenaltyPercent','penalty','late_penalty'
  ];
  for (const k of lateKeys) {
    if (typeof data[k] !== 'undefined' && data[k] !== null && data[k] !== '') {
      latePenaltyRaw = data[k];
      break;
    }
  }
  if (!latePenaltyRaw && data.late && typeof data.late === 'object') {
    if (typeof data.late.value !== 'undefined') latePenaltyRaw = data.late;
    else if (typeof data.late.penalty !== 'undefined') latePenaltyRaw = data.late.penalty;
  }
  if (!latePenaltyRaw && data.late_policy) latePenaltyRaw = data.late_policy;

  // passing marks detection
  const passingCandidates = ['passing_marks','passingMarks','pass_marks','passMarks','passing_mark','passing_mark_percent','passing_percentage','pass_percent','passing_percent','minimum_passing_marks','minimum_pass_marks','pass_mark'];
  let passingRaw = null;
  for (const k of passingCandidates) {
    if (typeof data[k] !== 'undefined' && data[k] !== null && data[k] !== '') {
      passingRaw = data[k];
      break;
    }
  }
  if (!passingRaw && data.passing && typeof data.passing === 'object') {
    passingRaw = data.passing.value ?? data.passing.percent ?? passingRaw;
  }

  // helper: format total marks
  function formatTotalMarks(m) {
    if (m === null || typeof m === 'undefined' || m === '') return '—';
    if (typeof m === 'object') {
      if (typeof m.out_of !== 'undefined') return String(m.out_of) + ' pts';
      if (typeof m.max !== 'undefined') return String(m.max) + ' pts';
      return JSON.stringify(m);
    }
    const n = Number(m);
    if (!isNaN(n)) {
      return (Number.isInteger(n) ? n : n.toFixed(2)) + ' pts';
    }
    return String(m);
  }

  // helper: format late penalty
  function formatLatePenalty(lp) {
    if (lp === null || typeof lp === 'undefined' || lp === '') return '—';
    if (typeof lp === 'object') {
      const type = (lp.type || lp.kind || '').toString().toLowerCase();
      const val = (typeof lp.value !== 'undefined') ? lp.value : (lp.amount ?? lp.penalty ?? null);
      const per = lp.per ?? lp.period ?? lp.unit ?? null;
      if (val !== null && val !== undefined) {
        const num = Number(val);
        if (!isNaN(num)) {
          if (type === 'percent' || type === 'percentage' || String(val).includes('%') || (num > 0 && num <= 1)) {
            const percent = (num > 0 && num <= 1) ? (num * 100) : num;
            return `${String(percent).replace(/\.00$/,'')}%${per ? ` ${per}` : ''}`;
          } else {
            return `${num}${per ? ` ${per}` : ''} (points)`;
          }
        }
        return `${String(val)}${per ? ` ${per}` : ''}`;
      }
      if (lp.description) return lp.description;
      return JSON.stringify(lp);
    }
    if (typeof lp === 'string') return lp;
    const n = Number(lp);
    if (!isNaN(n)) {
      if (n > 0 && n <= 1) return (n * 100).toFixed(2).replace(/\.00$/,'') + '%';
      else if (n >= 1 && n <= 100) return String(n).replace(/\.00$/,'') + '%';
      else return String(n);
    }
    return String(lp);
  }

  // helper: format passing marks (plain text)
  function formatPassing(m) {
    if (m === null || typeof m === 'undefined' || m === '') return '—';
    if (typeof m === 'string' && m.trim().endsWith('%')) return m.trim();
    const n = Number(m);
    if (!isNaN(n)) {
      if (n > 0 && n <= 1) return (n * 100).toFixed(0).replace(/\.0$/,'') + '%';
      if (n >= 1 && n <= 100) return (Number.isInteger(n) ? n : n.toFixed(2)) + '%';
      return (Number.isInteger(n) ? n : n.toFixed(2)) + ' pts';
    }
    return String(m);
  }

  // update assignmentHint + DOMs
  assignmentHint.textContent = currentAssignment.title ? `${currentAssignment.title} — due ${currentAssignment.due_at||'N/A'}` : '';
  document.getElementById('assignment_name').textContent = currentAssignment.title || 'Loading...';
  document.getElementById('due_date').innerHTML = currentAssignment.due_at ? `<span class="due-date"><i class="fas fa-clock"></i> ${escapeHtml(currentAssignment.due_at)}</span>` : '<span class="due-date"><i class="fas fa-clock"></i> No due date</span>';

  // status pill handling
  const statusEl = document.getElementById('assignment_status');
  if (statusEl) {
    // clear inline styles so CSS controls the look
    statusEl.style.cssText = '';
    // reset classes that we may use for colors
    statusEl.classList.remove('status-draft','status-due-soon','status-overdue','status-closed');
    const st = String(status || '').toLowerCase();
    if (st.includes('draft')) statusEl.classList.add('status-draft');
    else if (st.includes('overdue') || st.includes('late')) statusEl.classList.add('status-overdue');
    else if (st.includes('due') || st.includes('due soon') || st.includes('upcoming')) statusEl.classList.add('status-due-soon');
    else if (st.includes('closed') || st.includes('archived')) statusEl.classList.add('status-closed');
    // fallback style class (if you want a purple default, keep existing CSS)
    statusEl.textContent = String(status);
  }

  // update course name if present
  const courseEl = document.getElementById('course_name');
  if (courseEl) courseEl.textContent = currentAssignment.course_name ?? '—';

  // render total marks
  const tmEl = document.getElementById('assignment_total_marks');
  if (tmEl) tmEl.textContent = formatTotalMarks(totalMarks);

  // render late penalty
  const lpEl = document.getElementById('assignment_late_penalty');
  if (lpEl) lpEl.textContent = formatLatePenalty(latePenaltyRaw);

  // render passing marks into plain text element (create if missing)
  let passEl = document.getElementById('assignment_passing_marks');
  if (!passEl) {
    // try to insert it after total marks row for visual parity
    const totalRow = document.getElementById('assignment_total_marks')?.closest('.detail-item') ?? null;
    if (totalRow) {
      const wrapper = document.createElement('div');
      wrapper.className = 'detail-item';
      wrapper.innerHTML = `<span class="detail-label">Passing marks:</span><span class="detail-value" id="assignment_passing_marks">—</span>`;
      totalRow.parentNode.insertBefore(wrapper, totalRow.nextSibling);
    } else {
      // fallback: append to assignment-details container
      const details = document.querySelector('.assignment-details');
      if (details) details.insertAdjacentHTML('beforeend', `<div class="detail-item"><span class="detail-label">Passing marks:</span><span class="detail-value" id="assignment_passing_marks">—</span></div>`);
    }
    passEl = document.getElementById('assignment_passing_marks');
  }
  if (passEl) passEl.textContent = formatPassing(passingRaw);

  // store on currentAssignment
  currentAssignment.total_marks = totalMarks ?? currentAssignment.total_marks;
  currentAssignment.late_penalty = latePenaltyRaw ?? currentAssignment.late_penalty;
  currentAssignment.passing_marks = passingRaw ?? currentAssignment.passing_marks;
}

/* ---------- students rendering & documents (unchanged) ---------- */
function renderStudents(){
  if (!studentsData || studentsData.length === 0){
    studentsHost.innerHTML = '<div class="as-empty"><i class="fas fa-users" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i><p>No students have submitted yet.</p></div>';
    studentsCountEl.textContent = '';
    return;
  }
  studentsCountEl.textContent = `${studentsData.length}`;
  const html = studentsData.map(s => {
    const initials = (s.name || s.email || '#').split(' ').map(x=>x[0]).slice(0,2).join('').toUpperCase();
    return `
      <div class="student-pill" data-sid="${encodeURIComponent(s.student_id||'')}" data-suuid="${encodeURIComponent(s.student_uuid||'')}">
        <div class="avatar">${initials}</div>
        <div class="student-info">
          <div class="student-name">${escapeHtml(s.name||'Student')}</div>
          <div class="student-email">${escapeHtml(s.email||'')}</div>
        </div>
        <div class="submission-count">${s.submission_count||1} sub</div>
      </div>
    `;
  }).join('');
  studentsHost.innerHTML = html;

  studentsHost.querySelectorAll('.student-pill').forEach(el => {
    el.addEventListener('click', async () => {
      const sid = decodeURIComponent(el.getAttribute('data-sid')||'');
      const suuid = decodeURIComponent(el.getAttribute('data-suuid')||'');
      const student = studentsData.find(x => (String(x.student_id) === String(sid)) || (x.student_uuid && String(x.student_uuid) === String(suuid)));
      if (!student) return;
      document.querySelectorAll('.student-pill').forEach(p=>p.classList.remove('active'));
      el.classList.add('active');
      await loadStudentDocuments(student);
    });
  });

  const pre = window.__PRESELECT_STUDENT__;
  let pick = null;
  if (pre) {
    const decoded = decodeURIComponent(pre);
    pick = studentsData.find(s => (s.student_uuid && String(s.student_uuid) === String(pre)) || (s.student_uuid && String(s.student_uuid) === String(decoded)) || String(s.student_id) === String(pre) || String(s.student_id) === String(decoded));
  }
  if (!pick) pick = studentsData[0];
  if (pick) {
    const selector = Array.from(studentsHost.querySelectorAll('.student-pill')).find(el => {
      const su = decodeURIComponent(el.getAttribute('data-suuid')||'');
      const si = decodeURIComponent(el.getAttribute('data-sid')||'');
      return (pick.student_uuid && String(pick.student_uuid) === String(su)) || (String(pick.student_id) === String(si));
    });
    if (selector) { selector.classList.add('active'); loadStudentDocuments(pick); }
  }
}

/* ---------- load student documents (unchanged) ---------- */
// 2) loadStudentDocuments: updated view/given marks logic
async function loadStudentDocuments(student){
  selectedStudent = student;
  document.getElementById('student_name').textContent = student.name || 'Student';
  document.getElementById('student_email').textContent = student.email || '';
  submissionCountEl.textContent = student.submission_count || 0;
  updateGiveMarksButton();

  if (currentAssignment.uuid && student.student_uuid) {
    openInUiBtn.style.display = 'inline-flex';
    openInUiBtn.onclick = () => {
      const uiPath = `/assignments/${encodeURIComponent(currentAssignment.uuid)}/students/${encodeURIComponent(student.student_uuid)}/documents`;
      window.open(uiPath, '_blank');
    };
  } else {
    openInUiBtn.style.display = 'none';
    openInUiBtn.onclick = null;
  }

  const docsHost = document.getElementById('documents_host');
  docsHost.innerHTML = '<div class="as-loader"><i class="fa fa-spinner fa-spin"></i> Loading documents...</div>';

  const aKey = currentAssignment.uuid || window.__ASSIGNMENT_KEY__;
  const candidates = [];
  if (aKey && student.student_uuid) candidates.push(`/${aKey}/students/${student.student_uuid}/documents`);
  if (window.__ASSIGNMENT_KEY__ && student.student_uuid) candidates.push(`/${window.__ASSIGNMENT_KEY__}/students/${student.student_uuid}/documents`);
  if (window.__ASSIGNMENT_KEY__ && student.student_id) {
    candidates.push(`/${window.__ASSIGNMENT_KEY__}/students/${student.student_id}/documents`);
    candidates.push(`/${window.__ASSIGNMENT_KEY__}/student/${student.student_id}/documents`);
  }
  if (student.email) candidates.push(`/${window.__ASSIGNMENT_KEY__}/students/${encodeURIComponent(student.email)}/documents`);

  let resJson = null;
  for (const u of candidates){
    try{
      const resp = await apiFetch(u);
      if (!resp.ok) continue;
      const parsed = await resp.json().catch(()=>null);
      if (!parsed) continue;
      if ((parsed.data && (parsed.data.submissions || Array.isArray(parsed.data))) || Array.isArray(parsed.submissions) || (parsed.data && parsed.data.assignment)) {
        resJson = parsed;
        break;
      }
    } catch(e){
      if (e && e.status && (e.status === 401 || e.status === 403)) {
        docsHost.innerHTML = '<div class="as-empty">Access denied. You are not authorized to view these documents.</div>';
        console.warn('Auth error loading student documents', e);
        return;
      }
    }
  }

  if (!resJson) {
    try {
      const alt = `/${window.__ASSIGNMENT_KEY__}/submissions`;
      const r2 = await apiFetch(alt);
      if (r2.ok) {
        const j2 = await r2.json().catch(()=>null);
        const items = (j2 && j2.data && j2.data.submissions) ? j2.data.submissions : (j2 && j2.submissions) ? j2.submissions : (Array.isArray(j2)? j2 : []);
        const filtered = items.filter(it => {
          if (!it) return false;
          if (student.student_uuid && (it.student_uuid === student.student_uuid || it.uuid === student.student_uuid)) return true;
          if (student.student_id && (String(it.student_id) === String(student.student_id) || String(it.user_id) === String(student.student_id))) return true;
          return false;
        });
        if (filtered.length) resJson = { data: { submissions: filtered } };
      }
    } catch(e){
      if (e && e.status && (e.status === 401 || e.status === 403)) {
        docsHost.innerHTML = '<div class="as-empty">Access denied. You are not authorized to view these documents.</div>';
        return;
      }
    }
  }

  if (!resJson || !resJson.data) {
    docsHost.innerHTML = '<div class="as-empty"><i class="fas fa-file" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i><p>No documents found for this student.</p></div>';
    return;
  }

  const submissions = resJson.data.submissions || (Array.isArray(resJson.data) ? resJson.data : []);
  if (!Array.isArray(submissions) || submissions.length === 0){
    docsHost.innerHTML = '<div class="as-empty"><i class="fas fa-file" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i><p>No documents found for this student.</p></div>';
    return;
  }

  if (resJson.data.assignment) setAssignmentInfo(resJson.data.assignment);

  const graderRoles = ['admin', 'instructor', 'super_admin', 'superadmin'];

  const submissionsHtml = submissions.map((sub, subIndex) => {
    const attach = sub.all_attachments || sub.attachments || sub.attachments_json || sub.attachmentsJson || [];
    let attachments = [];
    if (Array.isArray(attach)) attachments = attach;
    else if (typeof attach === 'string' && attach.trim()) {
      try { attachments = JSON.parse(attach); } catch(e){ attachments = []; }
    }

    // Marks detection (show best candidates)
    const marksCandidates = ['total_marks','marks','score','obtained_marks','obtainedMarks','grade_value','points'];
    let marksRaw = null;
    for (const k of marksCandidates) {
      if (typeof sub[k] !== 'undefined' && sub[k] !== null && sub[k] !== '') { marksRaw = sub[k]; break; }
    }
    if (marksRaw === null && sub.grade && typeof sub.grade === 'object') {
      marksRaw = sub.grade.marks ?? sub.grade.value ?? sub.grade.score ?? null;
    }
    let marksText = 'Not graded';
    if (marksRaw !== null && typeof marksRaw !== 'undefined' && marksRaw !== '') {
      if (typeof marksRaw === 'object') {
        if (typeof marksRaw.obtained !== 'undefined' && typeof marksRaw.out_of !== 'undefined') {
          marksText = `${marksRaw.obtained}/${marksRaw.out_of}`;
        } else if (typeof marksRaw.value !== 'undefined') {
          marksText = String(marksRaw.value);
        } else {
          marksText = JSON.stringify(marksRaw);
        }
      } else {
        marksText = String(marksRaw);
      }
    }

    // grade / note / graded_by candidates
    const gradeCandidates = ['grade','grade_letter','gradeLetter','grade_value','letter'];
    let gradeRaw = null;
    for (const k of gradeCandidates) {
      if (typeof sub[k] !== 'undefined' && sub[k] !== null && sub[k] !== '') { gradeRaw = sub[k]; break; }
    }
    if (!gradeRaw && sub.grade && typeof sub.grade === 'object') gradeRaw = sub.grade.letter ?? sub.grade.value ?? null;
    const gradeText = gradeRaw !== null && gradeRaw !== undefined && gradeRaw !== '' ? String(gradeRaw) : '—';

    const noteCandidates = ['grader_note','grade_note','note','feedback','comments'];
    let noteRaw = null;
    for (const k of noteCandidates) {
      if (typeof sub[k] !== 'undefined' && sub[k] !== null && sub[k] !== '') { noteRaw = sub[k]; break; }
    }
    if (!noteRaw && sub.grade && typeof sub.grade === 'object') noteRaw = sub.grade.note ?? sub.grade.feedback ?? null;
    const noteText = noteRaw ? String(noteRaw) : 'No notes';

    const gradedByCandidates = ['graded_by','graded_by_name','gradedBy','grader','grader_name','graded_by_user'];
    let gradedByRaw = null;
    for (const k of gradedByCandidates) {
      if (typeof sub[k] !== 'undefined' && sub[k] !== null && sub[k] !== '') { gradedByRaw = sub[k]; break; }
    }
    if (!gradedByRaw && sub.grade && typeof sub.grade === 'object') gradedByRaw = sub.grade.by ?? sub.grade.graded_by ?? null;
    const gradedByText = gradedByRaw ? String(gradedByRaw) : '—';

    const attachmentsHtml = attachments.map(a => {
      const url = a.url || a.path || '#';
      const safeName = a.name || (url.split('/').pop() || 'file');
      const ext = (safeName.split('.').pop()||'').toLowerCase();
      let iconClass = 'icon-default';
      let iconType = 'fa-file';
      if (ext === 'pdf') { iconClass = 'icon-pdf'; iconType = 'fa-file-pdf'; }
      else if (['jpg','jpeg','png','gif','svg','bmp'].includes(ext)) { iconClass = 'icon-image'; iconType = 'fa-file-image'; }
      else if (['doc','docx'].includes(ext)) { iconClass = 'icon-doc'; iconType = 'fa-file-word'; }
      else if (['xls','xlsx'].includes(ext)) { iconType = 'fa-file-excel'; }
      else if (['ppt','pptx'].includes(ext)) { iconType = 'fa-file-powerpoint'; }
      else if (['zip','rar','7z'].includes(ext)) { iconType = 'fa-file-archive'; }

      return `
        <div class="doc-card">
          <div class="doc-card-inner">
            <div class="doc-info">
              <div class="doc-icon ${iconClass}"><i class="fas ${iconType}"></i></div>
              <div class="doc-content">
                <div class="doc-name">${escapeHtml(safeName)}</div>
                <div class="doc-meta">${escapeHtml(a.mime||'')} • ${formatBytes(a.size||0)}</div>
              </div>
            </div>
            <div class="doc-actions">
              <button class="btn-icon btn-icon-primary" data-url="${escapeHtml(a.url||a.path||'#')}" data-name="${escapeHtml(safeName)}" title="View"><i class="fa fa-eye"></i></button>
              <a class="btn-icon btn-icon-outline" href="${escapeHtml(a.url||a.path||'#')}" target="_blank" rel="noopener" title="Download" style="display:none"><i class="fa fa-download"></i></a>
            </div>
          </div>
        </div>
      `;
    }).join('');

    const attemptNo = escapeHtml(String(sub.attempt_no || sub.attempt || sub.attemptNo || (subIndex+1) || '—'));
    const attemptStatus = escapeHtml(sub.status || '');
    const submittedAt = escapeHtml(sub.submitted_at || sub.submittedAt || sub.created_at || '');

    // show View/Given Marks only if marks/grade/note exist
    const hasMarks = (marksRaw !== null && typeof marksRaw !== 'undefined' && marksRaw !== '') ||
                     (gradeRaw !== null && typeof gradeRaw !== 'undefined' && gradeRaw !== '') ||
                     (noteRaw !== null && typeof noteRaw !== 'undefined' && noteRaw !== '');

const viewLabel = graderRoles.includes(getCurrentRole())
  ? 'Given Marks'
  : 'View Marks';

    const viewMarksBtnHtml = hasMarks ? `
      <button class="view-marks-btn"
        data-marks="${escapeHtml(marksText)}"
        data-grade="${escapeHtml(gradeText)}"
        data-note="${escapeHtml(noteText)}"
        data-graded-by="${escapeHtml(gradedByText)}"
        data-submission-id="${escapeHtml(String(sub.id||sub.submission_id||sub.uuid||''))}">
        ${escapeHtml(viewLabel)}
      </button>` : '';

    return `
      <div class="attempt-section">
        <div class="attempt-header">
          <div class="attempt-title">Attempt ${attemptNo}  ${viewMarksBtnHtml}</div>

          <div class="attempt-right">
            <div class="attempt-submitted">Submitted: ${submittedAt}</div>
          </div>
        </div>

        <div class="doc-grid">${attachmentsHtml}</div>
      </div>
    `;
  }).join('');

  docsHost.innerHTML = submissionsHtml;

  // Attach view handlers to the view buttons we render above
  docsHost.querySelectorAll('.btn-icon[data-url]').forEach(btn => {
    btn.addEventListener('click', (ev) => {
      ev.preventDefault();
      const u = btn.getAttribute('data-url');
      if (!u) { console.warn('View button has no data-url'); return; }
      const name = btn.getAttribute('data-name') || 'Document';
      openViewer(u, name);
    });
  });

  // Attach view marks handlers (only exists if button was rendered)
  const viewMarksButtons = docsHost.querySelectorAll('.view-marks-btn');
  if (viewMarksButtons && viewMarksButtons.length) {
    viewMarksButtons.forEach(btn => {
      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        const m = btn.getAttribute('data-marks') || 'Not graded';
        const g = btn.getAttribute('data-grade') || '—';
        const n = btn.getAttribute('data-note') || 'No notes';
        const by = btn.getAttribute('data-graded-by') || '—';
        const sid = btn.getAttribute('data-submission-id') || '';

        openMarksModal({ marks: m, grade: g, note: n, graded_by: by, submission_id: sid });
      });
    });
  }
}

/* ---------- Grade modal (unchanged) ---------- */
function updateGiveMarksButton() {
  // try to find button if not defined globally
  const btn = (typeof giveMarksBtn !== 'undefined' && giveMarksBtn) ? giveMarksBtn : document.getElementById('give_marks_btn');
  if (!btn) return;

  const r = getCurrentRole();
  const graderRoles = ['admin', 'instructor', 'super_admin', 'superadmin'];

  // If a student — enforce hidden state with !important and disable tab index/clicks
  if (r === 'student') {
    try {
      btn.style.setProperty('display', 'none', 'important'); // correct way to use !important
      btn.setAttribute('aria-hidden', 'true');
      btn.setAttribute('disabled', 'true');
      btn.tabIndex = -1;
      // remove event listeners by cloning (safe)
      btn.replaceWith(btn.cloneNode(true));
    } catch (err) {
      // If anything fails, remove from DOM as last resort
      try { btn.remove(); } catch(e) {}
    }
    return;
  }

  // For graders: only show if a student is selected
  if (graderRoles.includes(r) && selectedStudent && (selectedStudent.student_id || selectedStudent.student_uuid)) {
    // ensure visible
    btn.style.removeProperty('display');
    btn.style.setProperty('display', 'flex');
    btn.removeAttribute('aria-hidden');
    btn.removeAttribute('disabled');
    btn.tabIndex = 0;
  } else {
    // hide otherwise
    btn.style.setProperty('display', 'none', 'important');
    btn.setAttribute('aria-hidden', 'true');
    btn.setAttribute('disabled', 'true');
    btn.tabIndex = -1;
  }
}



async function openGradeModal() {
  if (!selectedStudent) { showAlert('Please select a student first.', 'error'); return; }
  const modal = document.getElementById('grade_modal');
  const title = document.getElementById('grade_modal_title');
  const loading = document.getElementById('grade_loading');
  const content = document.getElementById('grade_content');
  const empty = document.getElementById('grade_empty');
  const alertBox = document.getElementById('grade_alert');
  const submitBtn = document.getElementById('grade_submit_btn');

  alertBox.style.display = 'none';
  content.style.display = 'none';
  empty.style.display = 'none';
  loading.style.display = 'flex';
  if (submitBtn) submitBtn.disabled = true;

  title.textContent = `Grade: ${selectedStudent.name || 'Student'}`;
  modal.style.display = 'flex';

  try {
    const submissions = await fetchSubmissionsForGrading();
    loading.style.display = 'none';
    if (!submissions || submissions.length === 0) { empty.style.display = 'block'; return; }
    renderSubmissionsList(submissions);
    content.style.display = 'block';
  } catch (error) {
    loading.style.display = 'none';
    showAlertInModal('Failed to load submissions: ' + (error.message || error), 'error');
  }
}

async function fetchSubmissionsForGrading() {
  const assignmentKey = currentAssignment.uuid || window.__ASSIGNMENT_KEY__;
  const studentKey = selectedStudent.student_uuid || selectedStudent.student_id;
  if (!assignmentKey || !studentKey) throw new Error('Missing assignment or student information');

  const candidates = [];
  if (currentAssignment.uuid && selectedStudent.student_uuid) candidates.push(`/${currentAssignment.uuid}/students/${selectedStudent.student_uuid}/documents`);
  candidates.push(`/${assignmentKey}/students/${studentKey}/documents`);
  candidates.push(`/${assignmentKey}/student/${studentKey}/documents`);

  for (const endpoint of candidates) {
    try {
      const response = await apiFetch(endpoint);
      if (!response.ok) continue;
      const data = await response.json();
      const submissions = data?.data?.submissions || data?.submissions || [];
      if (submissions.length > 0) return submissions;
    } catch (error) {
      console.warn(`Failed to fetch from ${endpoint}:`, error);
    }
  }

  throw new Error('No submissions found');
}
// Replace your existing renderSubmissionsList with this function
function renderSubmissionsList(submissions) {
  const container = document.getElementById('submissions_list');
  const form = document.getElementById('grade_form');
  const submitBtn = document.getElementById('grade_submit_btn');

  // Sort newest first
  submissions.sort((a, b) => (b.attempt_no || 0) - (a.attempt_no || 0));

  const submissionsHtml = submissions.map((submission, index) => {
    const attempt = submission.attempt_no || submission.attempt || index + 1;
    const submittedAt = submission.submitted_at || submission.created_at || '';
    const isLate = submission.is_late || submission.late || false;
    const currentMarks = submission.total_marks || submission.marks || 'Not graded';
    const submissionId = submission.id || submission.submission_id || '';
    const submissionUuid = submission.submission_uuid || submission.uuid || '';

    // attachments resolution
    const attach = submission.all_attachments || submission.attachments || submission.attachments_json || submission.attachmentsJson || [];
    let attachments = [];
    if (Array.isArray(attach)) attachments = attach;
    else if (typeof attach === 'string' && attach.trim()) {
      try { attachments = JSON.parse(attach); } catch(e){ attachments = []; }
    }
    const filesCount = attachments.length || 0;

    // create a unique id for the select so we can wire handlers reliably
    const selectId = `attachments_select_${index}`;

    // If multiple files we will render a compact select, else show simple preview text.
    let fileControlHtml = '';
    if (filesCount === 0) {
      fileControlHtml = `<div style="color:var(--muted-color)">No files</div>`;
    } else if (filesCount === 1) {
      fileControlHtml = `<div style="color:var(--muted-color)">1 file</div>`;
    } else {
      // build <select> options
      const opts = attachments.map((a, i) => {
        const url = a.url || a.path || a.file || a.link || '';
        const name = a.name || (url.split('/').pop()||`file-${i+1}`);
        return `<option value="${escapeHtml(url)}">${escapeHtml(name)}</option>`;
      }).join('');
      fileControlHtml = `<select id="${escapeHtml(selectId)}" class="grade-form-input" style="min-width:220px">${opts}</select>`;
    }

    return `
      <div class="submission-item ${index === 0 ? 'selected' : ''}" data-index="${index}" data-submission-id="${escapeHtml(submissionId)}" data-submission-uuid="${escapeHtml(submissionUuid)}">
        <div class="submission-header">
          <div class="submission-attempt">Attempt ${escapeHtml(String(attempt))}</div>
          <div class="submission-date">${submittedAt ? new Date(submittedAt).toLocaleString() : ''}</div>
        </div>

        <div class="submission-meta">
          <span><i class="fas ${isLate ? 'fa-clock' : 'fa-check'}"></i> ${isLate ? 'Late' : 'On Time'}</span>
          <span><i class="fas fa-file"></i> ${filesCount} files</span>
          <span><i class="fas fa-star"></i> ${escapeHtml(String(currentMarks))}</span>
        </div>

        <div style="margin-top: 8px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <button type="button" class="submission-select-btn ${index === 0 ? 'selected' : ''}" onclick="selectSubmission(this, '${escapeHtml(submissionId)}', '${escapeHtml(submissionUuid)}')">${index === 0 ? 'Selected' : 'Select for Grading'}</button>

          <!-- compact file control (select for multiple files) -->
          <div style="display:flex;align-items:center;gap:8px;">
            ${fileControlHtml}
            <!-- Preview (opens fullscreen) -->
            <button type="button" class="submission-preview-btn btn btn-outline-primary" style="display:none;"data-index="${index}" ${filesCount===0 ? 'disabled' : ''}>Preview</button>
            <!-- Open in new tab -->
            <button type="button" class="submission-open-tab btn btn-secondary" data-index="${index}" ${filesCount===0 ? 'disabled' : ''}><i class="fas fa-external-link-alt"></i></button>
          </div>

          <div style="display:none;margin-left:auto;color:var(--muted-color);font-size:0.95rem;">${submittedAt ? new Date(submittedAt).toLocaleString() : ''}</div>
        </div>
      </div>
    `;
  }).join('');

  container.innerHTML = submissionsHtml;

  // pre-fill form with first submission (existing behaviour)
  if (submissions.length > 0) {
    const firstSubmission = submissions[0];
    const submissionId = firstSubmission.id || firstSubmission.submission_id || '';
    const submissionUuid = firstSubmission.submission_uuid || firstSubmission.uuid || '';
    const sidField = form.querySelector('input[name="submission_id"]');
    const suuidField = form.querySelector('input[name="submission_uuid"]');
    if (sidField) sidField.value = submissionId;
    if (suuidField) suuidField.value = submissionUuid;
    if (submitBtn) submitBtn.disabled = false;
  } else {
    if (submitBtn) submitBtn.disabled = true;
  }

  // Helper: resolve attachments array for a given submission
  function resolveAttachmentsFor(sub) {
    const attach = sub.all_attachments || sub.attachments || sub.attachments_json || sub.attachmentsJson || [];
    let attachments = [];
    if (Array.isArray(attach)) attachments = attach;
    else if (typeof attach === 'string' && attach.trim()) {
      try { attachments = JSON.parse(attach); } catch(e){ attachments = []; }
    }
    return attachments || [];
  }

  // Ensure the viewer is appended to body and has a high z-index
  function ensureViewerOnTop() {
    const fs = document.getElementById('fs_viewer');
    if (!fs) return;
    if (fs.parentNode !== document.body) document.body.appendChild(fs);
    // higher than other overlays; adjust if you use different values
    fs.style.zIndex = '2147483680';
  }

  // wire Preview buttons (opens selected file in fullscreen viewer)
  container.querySelectorAll('.submission-preview-btn').forEach(btn => {
    btn.addEventListener('click', (ev) => {
      ev.preventDefault();
      const idx = Number(btn.getAttribute('data-index'));
      const sub = submissions[idx];
      if (!sub) { showAlertInModal('Submission not found', 'error'); return; }

      const attachments = resolveAttachmentsFor(sub);
      if (!attachments || attachments.length === 0) {
        showAlertInModal('No attachments for this attempt.', 'error');
        return;
      }

      // determine selected url:
      let selectedUrl = '';
      if (attachments.length === 1) {
        selectedUrl = attachments[0].url || attachments[0].path || attachments[0].file || attachments[0].link || '';
      } else {
        // multiple -> find the select that was rendered for this index
        const sel = document.getElementById(`attachments_select_${idx}`);
        if (sel && sel.value) selectedUrl = sel.value;
        else selectedUrl = attachments[0].url || attachments[0].path || attachments[0].file || attachments[0].link || '';
      }

      if (!selectedUrl) { showAlertInModal('Selected file URL missing', 'error'); return; }

      // Ensure viewer on top, then open using your openViewer helper
      ensureViewerOnTop();
      openViewer(selectedUrl, (attachments.length === 1 ? (attachments[0].name || '') : (document.getElementById(`attachments_select_${idx}`)?.selectedOptions?.[0]?.text || 'Document')));
    });
  });

  // wire "Open in new tab" buttons
  container.querySelectorAll('.submission-open-tab').forEach(btn => {
    btn.addEventListener('click', (ev) => {
      ev.preventDefault();
      const idx = Number(btn.getAttribute('data-index'));
      const sub = submissions[idx];
      if (!sub) { showAlertInModal('Submission not found', 'error'); return; }

      const attachments = resolveAttachmentsFor(sub);
      if (!attachments || attachments.length === 0) {
        showAlertInModal('No attachments for this attempt.', 'error');
        return;
      }

      let selectedUrl = '';
      if (attachments.length === 1) {
        selectedUrl = attachments[0].url || attachments[0].path || attachments[0].file || attachments[0].link || '';
      } else {
        const sel = document.getElementById(`attachments_select_${idx}`);
        if (sel && sel.value) selectedUrl = sel.value;
        else selectedUrl = attachments[0].url || attachments[0].path || attachments[0].file || attachments[0].link || '';
      }

      if (!selectedUrl) { showAlertInModal('Selected file URL missing', 'error'); return; }
      window.open(selectedUrl, '_blank', 'noopener');
    });
  });
}


function selectSubmission(button, submissionId, submissionUuid) {
  document.querySelectorAll('.submission-item').forEach(item => item.classList.remove('selected'));
  document.querySelectorAll('.submission-select-btn').forEach(btn => { btn.classList.remove('selected'); btn.textContent = 'Select for Grading'; });

  button.classList.add('selected');
  button.textContent = 'Selected';
  button.closest('.submission-item').classList.add('selected');

  const form = document.getElementById('grade_form');
  form.querySelector('input[name="submission_id"]').value = submissionId;
  form.querySelector('input[name="submission_uuid"]').value = submissionUuid;
  const submitBtn = document.getElementById('grade_submit_btn');
  if (submitBtn) submitBtn.disabled = false;
}

/* ---------- submitGrade (unchanged) ---------- */
window.submitGrade = async function submitGrade(e) {
  if (e && e.preventDefault) e.preventDefault();
  const submitBtn = document.getElementById('grade_submit_btn');
  const form = document.getElementById('grade_form');
  if (!form) { console.error('grade_form not found'); return; }

  const marksVal = form.querySelector('input[name="marks"]')?.value;
  const gradeLetter = form.querySelector('input[name="grade_letter"]')?.value;
  const applyLatePenalty = form.querySelector('select[name="apply_late_penalty"]')?.value;
  const grader_note = form.querySelector('textarea[name="grader_note"]')?.value;
  const submission_id = form.querySelector('input[name="submission_id"]')?.value;
  const submission_uuid = form.querySelector('input[name="submission_uuid"]')?.value;

  if (!submission_id && !submission_uuid) {
    showAlertInModal('No submission selected for grading.', 'error'); return;
  }
  if (!marksVal || isNaN(Number(marksVal))) {
    showAlertInModal('Please enter valid marks.', 'error'); return;
  }

  const origHtml = submitBtn ? submitBtn.innerHTML : null;
  if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...'; }

  try {
    const payload = {
      marks: Number(marksVal),
      grade: gradeLetter || null,
      apply_late_penalty: String(applyLatePenalty) === 'true',
      grader_note: grader_note || null
    };

    const subKey = submission_id || submission_uuid;
    let gradeEndpoint = `/submissions/${encodeURIComponent(subKey)}/grade`;
    let res;
    try {
      res = await apiFetch(gradeEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
    } catch (err) {
      console.warn('Primary grade endpoint failed, trying fallback PATCH on submission resource', err);
      gradeEndpoint = `/submissions/${encodeURIComponent(subKey)}`;
      res = await apiFetch(gradeEndpoint, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ grader_note: payload.grader_note, marks: payload.marks, grade: payload.grade })
      });
    }

    if (!res.ok) {
      const txt = await res.text().catch(()=>null);
      throw new Error(txt || `Server returned ${res.status}`);
    }

    showAlertInModal('Marks saved successfully.', 'success');

    setTimeout(() => {
      closeGradeModal();
      if (selectedStudent) loadStudentDocuments(selectedStudent).catch(()=>{});
      else fetchStudentStatus().catch(()=>{});
    }, 700);

  } catch (err) {
    console.error('submitGrade error', err);
    showAlertInModal('Failed to save marks: ' + (err.message || err), 'error');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      if (origHtml) submitBtn.innerHTML = origHtml;
    }
  }
};

function closeGradeModal() {
  const modal = document.getElementById('grade_modal');
  modal.style.display = 'none';
  const form = document.getElementById('grade_form');
  if (form) form.reset();
  try {
    form.querySelector('input[name="submission_id"]').value = '';
    form.querySelector('input[name="submission_uuid"]').value = '';
  } catch (e) {}
  const alertBox = document.getElementById('grade_alert');
  if (alertBox) alertBox.style.display = 'none';
}

/* ---------- viewer ---------- */
function openViewer(url, name){
  const fs = document.getElementById('fs_viewer');
  const title = document.getElementById('fs_title');
  const body = document.getElementById('fs_body');
  title.textContent = name || 'Document';
  const ext = (url.split('.').pop().split('?')[0]||'').toLowerCase();
  if (['jpg','jpeg','png','gif','svg','bmp'].includes(ext)){
    body.innerHTML = `<img src="${escapeHtml(url)}" alt="${escapeHtml(name)}"/>`;
  } else if (ext === 'pdf'){
    body.innerHTML = `<iframe src="${escapeHtml(url)}" style="width:100%;height:100%;border:0"></iframe>`;
  } else if (['mp4','webm','ogg'].includes(ext)){
    body.innerHTML = `<video controls src="${escapeHtml(url)}" style="width:100%;height:100%"></video>`;
  } else {
    body.innerHTML = `<iframe src="${escapeHtml(url)}" style="width:100%;height:100%;border:0"></iframe>`;
  }
  fs.style.display = 'flex';
}

/* ---------- initial load ---------- */
(async ()=>{
  try {
    role = await getMyRole(TOKEN);
    console.log('[Resolved role]', role);
  } catch (e) {
    console.warn('Failed to fetch role', e);
    role = '';
  }

  if (looksLikeUuid(window.__ASSIGNMENT_KEY__)) {
    currentAssignment.uuid = window.__ASSIGNMENT_KEY__;
  }

  await fetchStudentStatus();

  try {
    await fetchAssignmentDetails(currentAssignment.uuid || window.__ASSIGNMENT_KEY__);
  } catch (e) {
    console.warn('fetchAssignmentDetails failed', e);
  }

  // IMPORTANT: update role-based UI AFTER role is known
  updateGiveMarksButton();

  if (!currentAssignment.uuid && studentsData.length) {
    try { await loadStudentDocuments(studentsData[0]); } catch(e){}
  }
})();

/* ---------- event listeners ---------- */
document.getElementById('fs_close').addEventListener('click', () => {
  const fs = document.getElementById('fs_viewer');
  fs.style.display = 'none';
  document.getElementById('fs_body').innerHTML = '';
});

document.getElementById('give_marks_btn').addEventListener('click', openGradeModal);
document.getElementById('grade_modal_close').addEventListener('click', closeGradeModal);
document.getElementById('grade_cancel_btn').addEventListener('click', closeGradeModal);
document.getElementById('grade_submit_btn').addEventListener('click', window.submitGrade);

document.getElementById('grade_modal').addEventListener('click', (e) => { if (e.target.id === 'grade_modal') closeGradeModal(); });
(function(){
  const backBtn = document.getElementById('back_btn');
  if (!backBtn) return;

  backBtn.addEventListener('click', (ev) => {
    ev.preventDefault();
    const backUrl = window.__BACK_URL__ || document.referrer || '/assignments';
    try {
      if (window.history && window.history.length > 1) {
        window.history.back();
        return;
      }
    } catch (e) {}
    window.location.href = backUrl;
  });
  
})();
// Opens marks modal and populates fields. If submission_id given and data seems missing, you could optionally fetch details here.
function openMarksModal(payload) {
  const modal = document.getElementById('marks_modal');
  const mm_marks = document.getElementById('mm_marks');
  const mm_grade = document.getElementById('mm_grade');
  const mm_note = document.getElementById('mm_note');
  const mm_graded_by = document.getElementById('mm_graded_by');

  mm_marks.textContent = payload.marks ?? 'Not graded';
  mm_grade.textContent = payload.grade ?? '—';
  mm_note.textContent = payload.note ?? 'No notes';
  mm_graded_by.textContent = payload.graded_by ?? '—';

  modal.style.display = 'flex';
  modal.setAttribute('aria-hidden', 'false');

  // trap focus optionally or set focus to close button
  setTimeout(()=>{ document.getElementById('marks_modal_close')?.focus(); }, 50);
}

function closeMarksModal() {
  const modal = document.getElementById('marks_modal');
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden', 'true');
}

// wire modal close buttons
document.getElementById('marks_modal_close')?.addEventListener('click', closeMarksModal);
document.getElementById('mm_close_btn')?.addEventListener('click', closeMarksModal);
// clicking on overlay closes modal
document.getElementById('marks_modal')?.addEventListener('click', (e) => { if (e.target && e.target.id === 'marks_modal') closeMarksModal(); });

</script>

</body>
</html>
