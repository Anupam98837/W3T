{{-- resources/views/global/about-us.blade.php --}}
@section('title','About Us')

<style>
.about-section {
  margin-top:120px;
  margin-bottom:120px;
  padding: 80px 0;
}
.about-container {
  max-width: 1200px;
  margin: auto;
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  align-items: flex-start;   /* 🔥 CHANGED */
  gap: 60px;
}


/* LEFT */
.about-title {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 20px;
  color:#1c1324;
}

.about-title span {
  color: #c94ff0;
}

.about-text {
  font-size: 1.05rem;
  color: #555;
  max-width: 520px;
}

/* RIGHT */
.about-right {
  position: relative;
}

/* IMAGE */
.image-wrapper {
  position: relative;
  z-index: 2;
  border-radius: 16px;
  overflow: hidden;
  width: 85%;
  margin-left: -40px;
}

.image-wrapper img {
  width: 100%;
  display: block;
}

/* RING */
.ring-bg {
  position: absolute;
  width: 430px;
  height: 430px;
  border-radius: 50%;
  border: 36px solid #951eaa;
  top: 50%;
  left: 55%;
  z-index: 1;
  animation: ringFloat 7s ease-in-out infinite;
}

@keyframes ringFloat {
  0% { transform: translate(-50%, -50%); }
  50% { transform: translate(-50%, -58%); }
  100% { transform: translate(-50%, -50%); }
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .about-container {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .image-wrapper {
    width: 100%;
    margin-left: 0;
  }

  .ring-bg {
    display: none;
  }

  .about-title {
    font-size: 2.4rem;
  }

  .about-text {
    max-width: 100%;
    margin: auto;
  }
}
</style>

<section class="about-section">

  {{-- Loading --}}
  <div id="aboutLoading" style="text-align:center;padding:120px 20px;">
    <p>Loading About Us...</p>
  </div>

  {{-- Content --}}
  <div id="aboutContent" style="display:none;">
    <div class="about-container">

      <!-- LEFT -->
      <div class="about-left">
        <h1 class="about-title" id="aboutTitle"></h1>
        <p class="about-text" id="aboutText"></p>
      </div>

      <!-- RIGHT -->
      <div class="about-right">
        <div class="ring-bg"></div>
        <div class="image-wrapper">
          <img id="aboutImage" src="" alt="About Us">
        </div>
      </div>

    </div>
  </div>

  {{-- Empty --}}
  <div id="aboutEmpty" style="display:none;text-align:center;padding:120px 20px;">
    <p>About information is currently unavailable.</p>
  </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', () => {

  const loading = document.getElementById('aboutLoading');
  const content = document.getElementById('aboutContent');
  const empty   = document.getElementById('aboutEmpty');
  const img     = document.getElementById('aboutImage');

  fetch('/api/about-us')
    .then(res => res.json())
    .then(data => {

      loading.style.display = 'none';

      if (!data.success || !data.about) {
        empty.style.display = 'block';
        return;
      }

      const a = data.about;

      /* Title from API */
      document.getElementById('aboutTitle').innerHTML = a.title || '';

      /* Content from API */
      document.getElementById('aboutText').innerHTML = a.content || '';

      /* Image */
      if (a.image) {
        const imageUrl = a.image + '?v=' + Date.now();
        img.onload = () => img.style.display = 'block';
        img.onerror = () => img.style.display = 'none';
        img.src = imageUrl;
      } else {
        img.style.display = 'none';
      }

      content.style.display = 'block';
    })
    .catch(() => {
      loading.style.display = 'none';
      empty.style.display = 'block';
    });
});
</script>
