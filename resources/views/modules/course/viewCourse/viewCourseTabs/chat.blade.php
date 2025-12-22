{{-- resources/views/modules/course/viewCourse/viewCourseTabs/chat.blade.php --}}

<style>
  /* ===== Batch Chat Shell ===== */
  .vc-chat-panel{
    border-radius: 16px;
    border: 1px solid var(--line-strong);
    box-shadow: var(--shadow-2);
    background: var(--surface);
  }
  html.theme-dark .vc-chat-panel{
    background: var(--surface);
    border-color: var(--line-strong);
  }

  .vc-chat-shell{
    display:flex;
    flex-direction:column;
    height:420px;
    max-height: calc(100dvh - 260px);
  }

  .vc-chat-body{
    flex:1;
    border-radius:14px;
    border:1px solid var(--line-strong);
    padding:10px 12px;
    background: var(--surface);
    overflow-y:auto;
    position:relative;
  }
  html.theme-dark .vc-chat-body{
    background:#0f172a;
  }

  .chat-loading,
  .chat-empty,
  .chat-error{
    text-align:center;
    margin-top:30px;
    color:var(--muted-color);
    font-size:var(--fs-13);
  }

  .chat-empty i{
    font-size:32px;
    margin-bottom:8px;
    opacity:.4;
  }

  .chat-load-more{
    text-align:center;
    margin-bottom:8px;
  }
  .chat-load-more button{
    border:none;
    background:transparent;
    font-size:var(--fs-13);
    color:var(--secondary-color);
    padding:4px 10px;
    border-radius:999px;
    cursor:pointer;
  }
  .chat-load-more button:hover{
    background:var(--page-hover);
  }

  .chat-messages{
    display:flex;
    flex-direction:column;
    gap:8px;
  }

  /* ===== Message bubbles ===== */
  .msg-row{
    display:flex;
    flex-direction:column;
    max-width:82%;
    margin-bottom:4px;
  }
  .msg-row.mine{
    margin-left:auto;
    align-items:flex-end;
  }
  .msg-row.other{
    margin-right:auto;
    align-items:flex-start;
  }

  .msg-meta{
    font-size:var(--fs-12);
    color:var(--muted-color);
    margin-bottom:2px;
  }

  .msg-bubble{
    border-radius:14px;
    padding:8px 10px;
    box-shadow:var(--shadow-1);
    background:#f4e8ff;
    color:var(--ink);
  }
  html.theme-dark .msg-bubble{
    background:#1b1030;
  }

  .msg-row.mine .msg-bubble{
    background:var(--accent-color);
    color:#0b1324;
  }

  .msg-text{
    white-space:pre-wrap;
    word-wrap:break-word;
    font-size:var(--fs-14);
  }

  .msg-attachments{
    margin-top:6px;
    padding-left:0;
    list-style:none;
    display:flex;
    flex-direction:column;
    gap:4px;
  }
  .msg-attachments a{
    font-size:var(--fs-13);
    display:inline-flex;
    align-items:center;
    gap:6px;
    color:inherit;
  }
  .msg-attachments a i{
    font-size:13px;
  }

  .msg-footer{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:6px;
    margin-top:4px;
    font-size:var(--fs-12);
    color:rgba(0,0,0,.65);
  }
  .msg-row.other .msg-footer{
    justify-content:flex-start;
  }
  html.theme-dark .msg-footer{
    color:rgba(248,250,252,.75);
  }

  .msg-time{ opacity:.8; }
  .msg-seen{
    display:inline-flex;
    align-items:center;
    gap:3px;
  }
  .msg-seen i{ font-size:11px; }

  /* ===== Footer / Input row ===== */
.vc-chat-footer{
  margin-top:10px;
  border-radius:14px;
  border:1px solid var(--line-strong);
  padding:8px;
  background:var(--surface);
}
html.theme-dark .vc-chat-footer{
  background:#0f172a;
}

/* Row: input + send button on same line */
.chat-input-row{
  display:flex;
  align-items:center;      /* center vertically */
  gap:8px;
}

.chat-input-main{
  flex:1;
}

/* Textarea */
#chatMessageInput{
  resize:none;
  min-height:40px;
  max-height:96px;
  padding-right:32px;
  display:block;
}

/* Attach icon inside textarea */
.chat-attach-wrapper{
  position:relative;
}
.chat-attach-btn{
  position:absolute;
  right:10px;
  bottom:7px;
  border:none;
  background:transparent;
  color:var(--muted-color);
  cursor:pointer;
}

/* Selected file chips */
.chat-files-chips{
  margin-top:6px;
  display:flex;
  flex-wrap:wrap;
  gap:6px;
}
.chat-file-chip{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:4px 8px;
  border-radius:999px;
  background:var(--page-hover);
  font-size:var(--fs-12);
  color:var(--muted-color);
}
.chat-file-chip button{
  border:none;
  background:transparent;
  padding:0;
  color:inherit;
  cursor:pointer;
}

/* Send button wrapper */
.chat-send-btn{
  display:flex;
  align-items:center;
}

/* Send button itself */
.chat-send-btn .btn{
  position:relative;               /* for centered spinner */
  min-width:90px;
  height:42px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  margin-top:-1px;                 /* tiny nudge up to align with textarea */
}

/* Spinner – perfectly centered */
.btn-spinner{
  position:absolute;
  left:50%;
  top:50%;
  transform:translate(-50%, -50%);
  width:14px;
  height:14px;
  border:2px solid #0001;
  border-top-color:#fff;
  border-radius:50%;
  animation:chatspin .8s linear infinite;
  opacity:0;                       /* hidden by default */
  pointer-events:none;
}

/* Loading state: show spinner, fade label */
.btn-loading .btn-spinner{
  opacity:1;
}
.btn-loading .btn-label{
  opacity:0;
}

/* keep your existing keyframes */
@keyframes chatspin{ to{ transform:rotate(360deg); } }



  @keyframes chatspin{ to{ transform:rotate(360deg); } }

  @media (max-width: 768px){
    .vc-chat-shell{
      height:360px;
    }
  }
</style>

<div class="panel vc-chat-panel">
  <div class="panel-head">
    <div>
      <h3 class="panel-title">
        <i class="fa-regular fa-comments me-1"></i>
        Batch Chat
      </h3>
      <div class="panel-sub">
        Chat with your instructor and classmates for this batch.
      </div>
    </div>
  </div>

  <div id="batchChatShell" class="vc-chat-shell">
    {{-- Messages area --}}
    <div class="vc-chat-body" id="chatBody">
      <div id="chatLoading" class="chat-loading">
        <div class="spinner-border text-primary" style="width:2rem;height:2rem;" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Loading messages…</div>
      </div>

      <div id="chatError" class="chat-error" style="display:none;"></div>

      <div id="chatEmpty" class="chat-empty" style="display:none;">
        <i class="fa-regular fa-comment-dots"></i>
        <div>No messages yet. Say hello 👋</div>
      </div>

      <div id="chatLoadMoreWrap" class="chat-load-more" style="display:none;">
        <button type="button" id="chatLoadMoreBtn">
          <i class="fa fa-arrow-up me-1"></i> Load previous messages
        </button>
      </div>

      <div id="chatMessages" class="chat-messages"></div>
    </div>

    {{-- Input area --}}
    <div class="vc-chat-footer">
      <form class="chat-input-row" onsubmit="return false;">
        <div class="chat-input-main">
          <div class="chat-attach-wrapper">
            <textarea
              id="chatMessageInput"
              class="form-control"
              rows="1"
              placeholder="Type a message… (Enter to send, Shift+Enter for new line)"
            ></textarea>

            <button type="button" id="chatAttachBtn" class="chat-attach-btn" title="Attach files">
              <i class="fa fa-paperclip"></i>
            </button>

            <input type="file" id="chatFileInput" multiple style="display:none;">
          </div>

          <div id="chatFilesChips" class="chat-files-chips"></div>
        </div>

        <div class="chat-send-btn">
          <button type="button" id="chatSendBtn" class="btn btn-primary">
            <span class="btn-spinner"></span>
            <span class="btn-label">
              <i class="fa fa-paper-plane"></i> Send
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
/**
 * Chat tab script (corrected)
 * ✅ Works with your dynamic tab loader (NOT dependent on DOMContentLoaded)
 * ✅ Pulls role from in-memory cache + waits for auth:role-ready (no storage-based role)
 * ✅ Prevents double-init when switching tabs
 */
(function () {
  // --- Guards: this tab partial can be injected multiple times via your pane caching
  // Make sure we only initialize once per page lifecycle.
  if (window.__VC_CHAT_INITED__) return;
  window.__VC_CHAT_INITED__ = true;

  const TOKEN = sessionStorage.getItem('token') || localStorage.getItem('token') || '';

  // ===== In-memory role (global cache, async-safe) =====
  let USER_ROLE = '';

  const getRoleNow = () => {
    const r = (window.__AUTH_CACHE__ && typeof window.__AUTH_CACHE__.role === 'string')
      ? window.__AUTH_CACHE__.role
      : '';
    return String(r || '').trim().toLowerCase();
  };

  // Case 1: role already resolved
  USER_ROLE = getRoleNow();
  if (USER_ROLE) {
    console.log('Role (immediate):', USER_ROLE);
  }

  // Case 2: wait for role to resolve (if not ready yet)
  if (!USER_ROLE) {
    document.addEventListener(
      'auth:role-ready',
      (e) => {
        USER_ROLE = String(e?.detail?.role || '').trim().toLowerCase();
        console.log('Role (async):', USER_ROLE);

        // If actor exists already, keep it in sync
        try {
          if (window.__VC_CHAT_ACTOR__) window.__VC_CHAT_ACTOR__.role = USER_ROLE;
        } catch (err) {}
      },
      { once: true }
    );
  }

  function deriveBatchKey() {
    const parts = window.location.pathname.split('/').filter(Boolean);
    const lastIndex = parts.length - 1;
    const last = (parts[lastIndex] || '').toLowerCase();
    if (last === 'view' && parts.length >= 2) {
      return parts[lastIndex - 1];
    }
    return parts[lastIndex];
  }

  const batchKey = deriveBatchKey();

  // ---------- Init function (can be triggered on vc:tab-changed) ----------
  function initChatIfPresent() {
    const shell = document.getElementById('batchChatShell');
    if (!shell) return;

    // Prevent re-binding handlers if tab is re-shown
    if (shell.dataset.chatBound === '1') return;
    shell.dataset.chatBound = '1';

    const els = {
      body: document.getElementById('chatBody'),
      messagesWrap: document.getElementById('chatMessages'),
      loadMoreWrap: document.getElementById('chatLoadMoreWrap'),
      loadMoreBtn: document.getElementById('chatLoadMoreBtn'),
      loading: document.getElementById('chatLoading'),
      empty: document.getElementById('chatEmpty'),
      error: document.getElementById('chatError'),
      msgInput: document.getElementById('chatMessageInput'),
      fileInput: document.getElementById('chatFileInput'),
      filesChips: document.getElementById('chatFilesChips'),
      sendBtn: document.getElementById('chatSendBtn'),
      attachBtn: document.getElementById('chatAttachBtn'),
    };

    const API_BASE = '/api/batches/' + encodeURIComponent(batchKey);

    let messages = [];
    let hasMoreBefore = false;
    let isLoadingInitial = false;
    let isLoadingMore = false;
    let isSending = false;

    // actor must exist BEFORE we attach role-ready listeners that use it
    let actor = { id: null, role: null };
    window.__VC_CHAT_ACTOR__ = actor;

    let selectedFiles = [];

    function setSendLoading(on) {
      isSending = on;
      els.sendBtn.disabled = on;
      if (on) els.sendBtn.classList.add('btn-loading');
      else els.sendBtn.classList.remove('btn-loading');
    }

    function showState({ loading = null, empty = null, error = null }) {
      if (loading !== null) els.loading.style.display = loading ? 'block' : 'none';
      if (empty !== null) els.empty.style.display = empty ? 'block' : 'none';
      if (error !== null) els.error.style.display = error ? 'block' : 'none';
    }

    function renderFilesChips() {
      els.filesChips.innerHTML = '';
      if (!selectedFiles.length) return;

      selectedFiles.forEach((file, idx) => {
        const chip = document.createElement('div');
        chip.className = 'chat-file-chip';

        const sizeKB = Math.round(file.size / 1024);
        chip.innerHTML = `
          <span><i class="fa fa-paperclip"></i> ${file.name}</span>
          <span class="text-muted">${sizeKB} KB</span>
          <button type="button" data-idx="${idx}" aria-label="Remove file">
            <i class="fa fa-times"></i>
          </button>
        `;

        chip.querySelector('button').addEventListener('click', (e) => {
          const i = parseInt(e.currentTarget.dataset.idx, 10);
          selectedFiles.splice(i, 1);
          renderFilesChips();
        });

        els.filesChips.appendChild(chip);
      });
    }

    function buildMessageHTML(msg) {
      const row = document.createElement('div');
      row.className = 'msg-row ' + (msg.is_mine ? 'mine' : 'other');

      const senderLabel = msg.is_mine ? 'You' : (msg.sender_name || msg.sender_role || 'User');
      const timeLabel = msg.created_at_time || '';

      const meta = document.createElement('div');
      meta.className = 'msg-meta';
      meta.textContent = senderLabel + (timeLabel ? ' • ' + timeLabel : '');
      row.appendChild(meta);

      const bubble = document.createElement('div');
      bubble.className = 'msg-bubble';

      if (msg.message_text) {
        const text = document.createElement('div');
        text.className = 'msg-text';
        text.textContent = msg.message_text;
        bubble.appendChild(text);
      }

      if (Array.isArray(msg.attachments) && msg.attachments.length) {
        const ul = document.createElement('ul');
        ul.className = 'msg-attachments';

        msg.attachments.forEach(att => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = att.url || '#';
          a.target = '_blank';

          const ext = (att.ext || '').toLowerCase();
          const icon = document.createElement('i');

          if (['jpg','jpeg','png','gif','webp','svg'].includes(ext)) {
            icon.className = 'fa fa-image';
          } else if (ext === 'pdf') {
            icon.className = 'fa fa-file-pdf';
          } else if (['doc','docx'].includes(ext)) {
            icon.className = 'fa fa-file-word';
          } else if (['xls','xlsx','csv'].includes(ext)) {
            icon.className = 'fa fa-file-excel';
          } else {
            icon.className = 'fa fa-paperclip';
          }

          a.appendChild(icon);
          const span = document.createElement('span');
          span.textContent = att.name || 'Attachment';
          a.appendChild(span);

          li.appendChild(a);
          ul.appendChild(li);
        });

        bubble.appendChild(ul);
      }

      const footer = document.createElement('div');
      footer.className = 'msg-footer';

      const time = document.createElement('span');
      time.className = 'msg-time';
      time.textContent = msg.created_at_time || '';
      footer.appendChild(time);

      // Seen indicator only for own messages
      if (msg.is_mine && typeof msg.seen_by_total === 'number') {
        const seen = document.createElement('span');
        seen.className = 'msg-seen';

        const icon = document.createElement('i');
        icon.className = msg.seen_by_total > 1 ? 'fa fa-check-double' : 'fa fa-check';
        seen.appendChild(icon);

        const txt = document.createElement('span');
        if (msg.seen_by_total > 1) {
          txt.textContent = 'Seen by ' + msg.seen_by_total;
        } else {
          txt.textContent = msg.is_seen_by_me ? 'Sent' : '';
        }
        seen.appendChild(txt);

        footer.appendChild(seen);
      }

      bubble.appendChild(footer);
      row.appendChild(bubble);
      return row;
    }

    function renderAllMessages() {
      els.messagesWrap.innerHTML = '';

      if (!messages.length) {
        els.empty.style.display = hasMoreBefore ? 'none' : 'block';
        return;
      }

      els.empty.style.display = 'none';

      const frag = document.createDocumentFragment();
      messages.forEach(msg => {
        frag.appendChild(buildMessageHTML(msg));
      });
      els.messagesWrap.appendChild(frag);
    }

    function scrollToBottom() {
      requestAnimationFrame(() => {
        els.body.scrollTop = els.body.scrollHeight + 50;
      });
    }

    async function fetchMessages(params = {}) {
      const qs = new URLSearchParams();
      if (params.limit) qs.set('limit', params.limit);
      if (params.beforeId) qs.set('before_id', params.beforeId);
      if (params.afterId) qs.set('after_id', params.afterId);

      const url = API_BASE + '/messages' + (qs.toString() ? ('?' + qs.toString()) : '');
      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'Authorization': TOKEN ? 'Bearer ' + TOKEN : '',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });

      if (!res.ok) {
        let msg = 'Failed to load messages (HTTP ' + res.status + ')';
        try {
          const data = await res.json();
          if (data && data.error) msg = data.error;
        } catch (e) {}
        throw new Error(msg);
      }

      const json = await res.json();
      return json.data || json;
    }

    async function markReadUpTo(id) {
      if (!id) return;
      try {
        await fetch(API_BASE + '/messages/read', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Authorization': TOKEN ? 'Bearer ' + TOKEN : '',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ up_to_id: id }),
        });
      } catch (err) {
        console.warn('chat.markReadUpTo', err);
      }
    }

    async function loadInitial() {
      if (isLoadingInitial) return;
      isLoadingInitial = true;
      showState({ loading: true, error: false });

      try {
        const data = await fetchMessages({ limit: 50 });

        actor = data.actor || actor;

        // ✅ ensure role comes from in-memory role cache
        const roleNow = getRoleNow();
        actor.role = actor.role || roleNow || USER_ROLE || null;
        window.__VC_CHAT_ACTOR__ = actor;

        messages = Array.isArray(data.messages) ? data.messages : [];
        hasMoreBefore = !!(data.meta && data.meta.has_more_before);

        renderAllMessages();
        els.loadMoreWrap.style.display = hasMoreBefore ? 'block' : 'none';

        showState({ loading: false, empty: messages.length === 0 && !hasMoreBefore });

        if (messages.length) {
          scrollToBottom();
          const newestId = messages[messages.length - 1].id;
          markReadUpTo(newestId);
        }
      } catch (err) {
        console.error('chat.initial', err);
        showState({ loading: false, error: true });
        els.error.textContent = err.message || 'Failed to load messages.';
      } finally {
        isLoadingInitial = false;
      }
    }

    async function loadOlder() {
      if (isLoadingMore || !hasMoreBefore || !messages.length) return;

      isLoadingMore = true;
      els.loadMoreBtn.disabled = true;
      els.loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading…';

      const prevScrollHeight = els.body.scrollHeight;
      const prevScrollTop = els.body.scrollTop;

      try {
        const oldestId = messages[0].id;
        const data = await fetchMessages({ beforeId: oldestId, limit: 50 });
        const newMsgs = Array.isArray(data.messages) ? data.messages : [];
        hasMoreBefore = !!(data.meta && data.meta.has_more_before);

        messages = newMsgs.concat(messages);
        renderAllMessages();
        els.loadMoreWrap.style.display = hasMoreBefore ? 'block' : 'none';

        const newScrollHeight = els.body.scrollHeight;
        const diff = newScrollHeight - prevScrollHeight;
        els.body.scrollTop = prevScrollTop + diff;
      } catch (err) {
        console.error('chat.loadOlder', err);
      } finally {
        isLoadingMore = false;
        els.loadMoreBtn.disabled = false;
        els.loadMoreBtn.textContent = 'Load previous messages';
      }
    }

    async function pollNewMessages() {
      if (!messages.length) return;
      try {
        const lastId = messages[messages.length - 1].id;
        const data = await fetchMessages({ afterId: lastId, limit: 50 });
        const newMsgs = Array.isArray(data.messages) ? data.messages : [];
        if (!newMsgs.length) return;

        messages = messages.concat(newMsgs);
        renderAllMessages();
        scrollToBottom();
        markReadUpTo(messages[messages.length - 1].id);
      } catch (err) {
        console.warn('chat.poll', err.message || err);
      }
    }

    async function sendMessage() {
      if (isSending) return;
      const text = (els.msgInput.value || '').trim();
      if (!text && !selectedFiles.length) {
        els.msgInput.focus();
        return;
      }

      const fd = new FormData();
      if (text) fd.append('message', text);
      selectedFiles.forEach(f => fd.append('attachments[]', f));

      setSendLoading(true);

      try {
        const res = await fetch(API_BASE + '/messages', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Authorization': TOKEN ? 'Bearer ' + TOKEN : '',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: fd,
        });

        if (!res.ok) {
          if (res.status === 422) {
            const data = await res.json();
            const msg = (data.errors && data.errors.message && data.errors.message[0]) || 'Validation failed.';
            throw new Error(msg);
          }
          throw new Error('Failed to send message (HTTP ' + res.status + ')');
        }

        const json = await res.json();
        const msg = (json.data && json.data.message) ? json.data.message : null;
        if (msg) {
          messages.push(msg);
          renderAllMessages();
          scrollToBottom();
          markReadUpTo(msg.id);
        }

        els.msgInput.value = '';
        selectedFiles = [];
        renderFilesChips();
      } catch (err) {
        console.error('chat.send', err);
        alert(err.message || 'Failed to send message.');
      } finally {
        setSendLoading(false);
      }
    }

    // ===== Wire events =====
    els.loadMoreBtn.addEventListener('click', () => loadOlder());

    els.fileInput.addEventListener('change', (e) => {
      const files = Array.from(e.target.files || []);
      if (files.length) {
        selectedFiles = selectedFiles.concat(files);
        renderFilesChips();
      }
      e.target.value = '';
    });

    els.attachBtn.addEventListener('click', () => {
      els.fileInput.click();
    });

    els.sendBtn.addEventListener('click', (e) => {
      e.preventDefault();
      sendMessage();
    });

    els.msgInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    // mark read when user is near bottom
    let readThrottle = null;
    els.body.addEventListener('scroll', () => {
      if (!messages.length) return;
      const nearBottom = (els.body.scrollHeight - els.body.scrollTop - els.body.clientHeight) < 40;
      if (nearBottom) {
        if (readThrottle) return;
        readThrottle = setTimeout(() => {
          const lastId = messages[messages.length - 1].id;
          markReadUpTo(lastId);
          readThrottle = null;
        }, 800);
      }
    });

    // light polling every 15s (single interval per page)
    if (!window.__VC_CHAT_POLL__) {
      window.__VC_CHAT_POLL__ = setInterval(pollNewMessages, 15000);
    }

    // initial load
    loadInitial();
  }

  // Run once now (works if chat is initial tab)
  initChatIfPresent();

  // Also run when your tab system switches to chat (works when loaded dynamically)
  document.addEventListener('vc:tab-changed', (e) => {
    try {
      if (e && e.detail && e.detail.tab === 'chat') {
        initChatIfPresent();
      }
    } catch (err) {}
  });
})();
</script>
