
@section('title','Contact Us')

<style>
:root{
    --primary-color:   #951eaa;
    --secondary-color: #5e1570;
    --accent-color:    #c94ff0;
}

/* ===== Wrapper ===== */
.contact-section{
    max-width:1200px;
    margin: 10px auto;
    padding:0 20px;
}

/* ===== Heading ===== */
.contact-head small{
    color:var(--primary-color);
    font-weight:600;
    display:flex;
    align-items:center;
    gap:8px;
}

.contact-head h1{
    font-size:3rem;
    font-weight:800;
    line-height:1.2;
    margin:12px 0;
}

.contact-head h1 span{
    color:var(--secondary-color);
}

.contact-head p{
    max-width:420px;
    color:#555;
}

/* ===== Layout ===== */
.contact-grid{
    display:grid;
    grid-template-columns:420px 1fr;
    gap:60px;
    margin-top:60px;
}

/* ===== Info Cards ===== */
.info-card{
    background:#faf5ff;
    padding:24px;
    border-radius:16px;
    display:flex;
    gap:16px;
    align-items:flex-start;
    margin-bottom:24px;
}

.info-card i{
    font-size:28px;
    color:var(--primary-color);
}

.info-card h4{
    margin:0;
    font-weight:700;
}

.info-card p{
    margin:4px 0 0;
    color:#555;
}

/* ===== Form ===== */
.contact-form label{
    font-weight:600;
    margin-bottom:6px;
    display:block;
}

.contact-form input,
.contact-form textarea{
    width:100%;
    padding:12px 14px;
    border:1px solid #ddd;
    border-radius:8px;
    margin-bottom:18px;
    font-size:14px;
}

.contact-form textarea{
    min-height:120px;
    resize:vertical;
}

.contact-form input:focus,
.contact-form textarea:focus{
    outline:none;
    border-color:var(--accent-color);
    box-shadow:0 0 0 3px rgba(201,79,240,.15);
}

/* ===== Button ===== */
.contact-form button{
    background:var(--primary-color);
    color:#fff;
    border:none;
    padding:12px 30px;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
}

.contact-form button:hover{
    background:var(--secondary-color);
}

/* ===== Map ===== */
.map-preview{
    margin-top:50px;
    border-radius:16px;
    overflow:hidden;
    border:1px solid #eee;
}

.map-preview iframe{
    width:100%;
    height:320px;
    border:0;
}

/* ===== Responsive ===== */
@media(max-width:900px){
    .contact-grid{
        grid-template-columns:1fr;
    }
    .contact-head h1{
        font-size:2.4rem;
    }
}
</style>

<div class="contact-section">

    {{-- Heading --}}
    <div class="contact-head">
        <small>Contact us →</small>

        <h1>
            Learn. Build. Grow. <br>
            <span>With the Right Tech Guidance</span>
        </h1>

        <p>
            Our learning platform is designed to help students and professionals
            gain practical technology skills through structured learning,
            hands-on projects, and expert guidance.
        </p>
    </div>

    {{-- Grid --}}
    <div class="contact-grid">

        {{-- LEFT --}}
        <div>
            <div class="info-card">
                <i class="fa-solid fa-location-dot"></i>
                <div>
                    <h4>Learning Hub</h4>
                    <p>
                        Webel Tower II, Salt Lake<br>
                        Sector V, Kolkata, India
                    </p>
                </div>
            </div>

            <div class="info-card">
                <i class="fa-solid fa-phone"></i>
                <div>
                    <h4>Support Number</h4>
                    <p>
                        +91 80088 93093
                    </p>
                </div>
            </div>

            <div class="info-card">
                <i class="fa-solid fa-envelope"></i>
                <div>
                    <h4>Email Support</h4>
                    <p>
                        support@w3t.com
                    </p>
                </div>
            </div>
        </div>

        {{-- RIGHT FORM --}}
        <div>
            <form id="contactForm" class="contact-form">

                <label>Full Name *</label>
                <input type="text" id="name" placeholder="Enter your full name" required>

                <label>Email Address *</label>
                <input type="email" id="email" placeholder="Enter your email address" required>

                <label>Phone Number *</label>
                <input type="text" id="phone" placeholder="Enter your phone number" required>

                <label>Send us a message *</label>
                <textarea
                    id="message"
                    placeholder="Ask about courses, learning paths, certifications, or career guidance"
                    required>
                </textarea>

                <button type="submit">Send Message</button>

            </form>
        </div>

    </div>

    {{-- MAP (PREVIEW ONLY) --}}
    <div class="map-preview">
        <iframe
            src="https://www.google.com/maps?q=Webel%20Tower%20II%20Salt%20Lake%20Kolkata&output=embed"
            loading="lazy">
        </iframe>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('contactForm').addEventListener('submit', async function(e){
    e.preventDefault();

    const name    = document.getElementById('name').value.trim();
    const email   = document.getElementById('email').value.trim();
    const phone   = document.getElementById('phone').value.trim();
    const message = document.getElementById('message').value.trim();

    if(!name || !email || !message){
        Swal.fire('Error','Please fill all required fields','error');
        return;
    }

    const payload = { name, email, phone, message };

    const res = await fetch('/api/contact-us', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if(res.ok){
        Swal.fire('Success','Message sent successfully','success');
        this.reset();
    }else{
        Swal.fire('Error', data.message || 'Validation failed', 'error');
        console.error(data);
    }
});

</script>
