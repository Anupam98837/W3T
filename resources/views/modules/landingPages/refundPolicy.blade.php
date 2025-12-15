{{-- resources/views/global/refund.blade.php --}}
@section('title', 'Refund Policy')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

<style>
    /* ===== SAME CSS AS TERMS & PRIVACY ===== */
    .terms-page-wrapper {
        background-color: #fafafa;
        min-height: 100vh;
        padding: 60px 20px;
    }

    .terms-header {
        text-align: center;
        margin-bottom: 50px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .terms-title {
        font-size: 2.2rem;
        font-weight: 700;
        font-family: var(--font-head);
        margin-bottom: 12px;
        letter-spacing: -0.5px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--ink) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    .terms-subtitle {
        font-size: 1.05rem;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .terms-updated {
        font-size: 0.9rem;
        color: #9ca3af;
    }

    .terms-content-container {
        max-width: 1200px;
        margin: 0 auto;
        border-radius: 12px;
        padding: 50px 60px;
    }

    .terms-toc {
        background-color: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 40px;
    }

    .terms-toc-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: #1f2937;
    }

    .terms-toc-list {
        list-style: none;
        padding-left: 0;
    }

    .terms-toc-list li {
        margin-bottom: 10px;
    }

    .terms-toc-list a {
        color: #4f46e5;
        text-decoration: none;
        font-size: 0.98rem;
    }

    .terms-content {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #374151;
        line-height: 1.8;
    }

    .terms-content-section {
        margin-bottom: 3rem;
        scroll-margin-top: 30px;
    }

    .terms-content h2 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 16px;
    }

    .terms-content h3 {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1f2937;
        margin-top: 1.5rem;
        margin-bottom: 12px;
    }

    .terms-footer-box {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 25px;
        margin-top: 40px;
    }

    .terms-loading-state,
    .terms-empty-state {
        text-align: center;
        padding: 100px 20px;
    }

    @media (max-width: 768px) {
        .terms-content-container {
            padding: 30px 24px;
        }
    }
</style>

<div class="terms-page-wrapper">

    {{-- Loading --}}
    <div id="refundLoading" class="terms-loading-state">
        <i class="fa-solid fa-spinner fa-spin-pulse"></i>
        <p>Loading Refund Policy...</p>
    </div>

    {{-- Content --}}
    <div id="refundContent" style="display:none;">
        <header class="terms-header">
            <h1 class="terms-title" id="refundTitle">Refund Policy</h1>
            <p class="terms-subtitle">
                Please review our refund and cancellation rules carefully.
            </p>
            <p class="terms-updated" id="refundUpdated"></p>
        </header>

        <div class="terms-content-container">

            {{-- TOC --}}
            <nav id="refundTOC" class="terms-toc" style="display:none;">
                <div class="terms-toc-title">Table of Contents</div>
                <ul class="terms-toc-list" id="refundTOCList"></ul>
            </nav>

            {{-- Main Content --}}
            <div class="terms-content" id="refundHTML"></div>

            {{-- Footer --}}
            <div class="terms-footer-box">
                <p><strong>Effective Date:</strong> <span id="refundEffectiveDate"></span></p>
                <p><strong>Contact:</strong> <span id="refundContactEmail"></span></p>
            </div>
        </div>
    </div>

    {{-- Empty --}}
    <div id="refundEmpty" class="terms-empty-state" style="display:none;">
        <i class="fa-solid fa-rotate-left"></i>
        <p>Refund policy is currently unavailable.</p>
        <p>Please check back later.</p>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const titleEl   = document.getElementById("refundTitle");
    const updatedEl = document.getElementById("refundUpdated");
    const effEl     = document.getElementById("refundEffectiveDate");
    const emailEl   = document.getElementById("refundContactEmail");
    const htmlEl    = document.getElementById("refundHTML");

    const tocEl     = document.getElementById("refundTOC");
    const tocListEl = document.getElementById("refundTOCList");

    const loadingEl = document.getElementById("refundLoading");
    const contentEl = document.getElementById("refundContent");
    const emptyEl   = document.getElementById("refundEmpty");

    fetch("/api/refund-policy")
    .then(res => res.json())
    .then(data => {
        
        
        loadingEl.style.display = "none";

        // Handle both refund and refund_policy keys
        const refundData = data.refund_policy || data.refund;
        
        if (!data.success || !refundData) {
            emptyEl.style.display = "block";
            return;
        }

        const r = refundData;

        titleEl.textContent   = r.title || "Refund Policy";
        updatedEl.textContent = r.updated_at ? `Last Updated: ${r.updated_at}` : "";
        effEl.textContent     = r.effective_date || r.updated_at || "N/A";
        emailEl.textContent   = r.contact_email || "refunds@yourcompany.com";

        htmlEl.innerHTML = r.content;

        const h2s = htmlEl.querySelectorAll("h2");
        if (h2s.length > 1) {
            tocListEl.innerHTML = "";
            h2s.forEach((h, i) => {
                if (!h.id) h.id = `refund-sec-${i}`;
                const li = document.createElement("li");
                li.innerHTML = `<a href="#${h.id}">${h.textContent}</a>`;
                tocListEl.appendChild(li);
            });
            tocEl.style.display = "block";
        }

        contentEl.style.display = "block";
    })
    .catch((error) => {
        console.error("Fetch error:", error);
        loadingEl.style.display = "none";
        emptyEl.style.display = "block";
    });
});
</script>
