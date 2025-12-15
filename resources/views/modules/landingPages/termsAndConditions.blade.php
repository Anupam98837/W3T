{{-- resources/views/global/terms.blade.php --}}
@section('title', 'Terms & Conditions')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

<style>
    .terms-page-wrapper {
        background-color: #fafafa;
        min-height: 100vh;
        padding: 60px 20px;
    }

    /* Header Section - Centered */
    .terms-header {
        text-align: center;
        margin-bottom: 50px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }
.terms-title {
    font-size: 2.2rem; /* updated size */
    font-weight: 700; /* keep bold */
    font-family: var(--font-head); /* your custom heading font */
    margin-bottom: 12px;
    letter-spacing: -0.5px;

    /* Gradient Text */
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--ink) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;

    /* Optional for smoother appearance */
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

    /* Content Container - Left Aligned with Max Width */
    .terms-content-container {
        max-width: 1200px;
        
        margin: 0 auto;
        border-radius: 12px;
        padding: 50px 60px;
    }

    /* Table of Contents */
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
        transition: color 0.2s;
    }

    .terms-toc-list a:hover {
        color: #3730a3;
        text-decoration: underline;
    }

    /* Content Sections */
    .terms-content {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, sans-serif;
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
        margin-top: 0;
    }

    .terms-content h3 {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1f2937;
        margin-top: 1.5rem;
        margin-bottom: 12px;
    }

    .terms-content p {
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .terms-content ul,
    .terms-content ol {
        padding-left: 2rem;
        margin-bottom: 1rem;
    }

    .terms-content li {
        margin-bottom: 0.6rem;
    }

    /* Footer Important Box */
    .terms-footer-box {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 25px;
        margin-top: 40px;
    }

    .terms-footer-box p {
        margin-bottom: 10px;
        font-size: 0.95rem;
        color: #4b5563;
    }

    .terms-footer-box p:last-child {
        margin-bottom: 0;
    }

    .terms-footer-box strong {
        color: #1f2937;
    }

    /* Loading State */
    .terms-loading-state {
        text-align: center;
        padding: 100px 20px;
    }

    .terms-loading-state i {
        font-size: 3rem;
        color: #9ca3af;
        margin-bottom: 20px;
    }

    .terms-loading-state p {
        color: #6b7280;
        font-size: 1.1rem;
    }

    /* Empty State */
    .terms-empty-state {
        text-align: center;
        padding: 100px 20px;
        max-width: 600px;
        margin: 0 auto;
    }

    .terms-empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 24px;
    }

    .terms-empty-state p {
        color: #6b7280;
        font-size: 1.05rem;
        margin-bottom: 12px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .terms-content-container {
            padding: 40px 40px;
        }
    }

    @media (max-width: 768px) {
        .terms-page-wrapper {
            padding: 40px 16px;
        }

        .terms-title {
            font-size: 2.2rem;
        }

        .terms-content-container {
            padding: 30px 24px;
        }

        .terms-content h2 {
            font-size: 1.5rem;
        }

        .terms-content h3 {
            font-size: 1.15rem;
        }

        .terms-toc {
            padding: 20px;
        }
    }
</style>

<div class="terms-page-wrapper">
    {{-- Loading State --}}
    <div id="termsLoading" class="terms-loading-state">
        <i class="fa-solid fa-spinner fa-spin-pulse"></i>
        <p>Loading Terms and Conditions...</p>
    </div>

    {{-- Main Content --}}
    <div id="termsContent" style="display:none;">
        <!-- Centered Header -->
        <header class="terms-header">
            <h1 class="terms-title" id="termsTitle">Terms & Conditions</h1>
            <p class="terms-subtitle">Please read these terms carefully before using our services</p>
            <p class="terms-updated" id="termsUpdated"></p>
        </header>

        <!-- Content Container -->
        <div class="terms-content-container">
            <!-- Table of Contents -->
            <nav id="termsTOC" class="terms-toc" style="display:none;">
                <div class="terms-toc-title">Table of Contents</div>
                <ul class="terms-toc-list" id="termsTOCList"></ul>
            </nav>

            <!-- Main Content -->
            <div class="terms-content" id="termsHTML"></div>

            <!-- Footer Info Box -->
            <div class="terms-footer-box">
                <p><strong>Effective Date:</strong> <span id="termsEffectiveDate"></span></p>
                <p><strong>Contact:</strong> For questions regarding these Terms, please contact us at <span id="termsContactEmail"></span></p>
            </div>
        </div>
    </div>

    {{-- Empty State --}}
    <div id="termsEmpty" class="terms-empty-state" style="display:none;">
        <i class="fa-solid fa-file-circle-question"></i>
        <p>We are currently updating our Terms and Conditions.</p>
        <p>Please check back later or contact our support team for assistance.</p>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const titleEl = document.getElementById("termsTitle");
        const updatedEl = document.getElementById("termsUpdated");
        const effectiveDateEl = document.getElementById("termsEffectiveDate");
        const contactEmailEl = document.getElementById("termsContactEmail");
        const htmlEl = document.getElementById("termsHTML");
        const tocEl = document.getElementById("termsTOC");
        const tocListEl = document.getElementById("termsTOCList");
        const loadingEl = document.getElementById("termsLoading");
        const contentEl = document.getElementById("termsContent");
        const emptyEl = document.getElementById("termsEmpty");

        fetch("/api/terms")
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                loadingEl.style.display = "none";

                if (!data.success || !data.terms) {
                    emptyEl.style.display = "block";
                    return;
                }

                const terms = data.terms;

                titleEl.textContent = terms.title || "Terms & Conditions";
                
                if (terms.updated_at) {
                    updatedEl.textContent = `Last Updated: ${terms.updated_at}`;
                }
                
                effectiveDateEl.textContent = terms.effective_date || terms.updated_at || "N/A";
                contactEmailEl.textContent = terms.contact_email || "legal@yourcompany.com";

                htmlEl.innerHTML = terms.content;

                // Build Table of Contents
                const h2Headings = htmlEl.querySelectorAll('h2');
                if (h2Headings.length > 1) {
                    tocListEl.innerHTML = '';
                    h2Headings.forEach((heading, index) => {
                        if (!heading.id) {
                            heading.id = 'section-' + index;
                        }
                        const listItem = document.createElement('li');
                        const link = document.createElement('a');
                        link.href = '#' + heading.id;
                        link.textContent = heading.textContent;
                        listItem.appendChild(link);
                        tocListEl.appendChild(listItem);
                    });
                    tocEl.style.display = 'block';
                }

                contentEl.style.display = "block";
            })
            .catch(err => {
                console.error("Error loading terms:", err);
                loadingEl.style.display = "none";
                emptyEl.style.display = "block";
            });
    });
</script>