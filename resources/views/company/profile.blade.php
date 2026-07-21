@extends('layouts.app')

@push('styles')
<style>
  /* ── Premium Company Profile UI ── */
  .profile-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    animation: fadeIn 0.5s ease-out;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Hero Banner */
  .hero-banner {
    position: relative;
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: 60px 40px;
    text-align: center;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    margin-bottom: 32px;
  }
  
  /* Decorative Glow */
  .hero-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -10%;
    width: 60%;
    height: 200%;
    background: radial-gradient(circle, rgba(165, 180, 252, 0.15) 0%, transparent 70%);
    pointer-events: none;
  }

  .company-logo {
    width: 80px;
    height: 80px;
    background: #fff;
    padding: 10px;
    border-radius: 20px;
    object-fit: contain;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    margin-bottom: 24px;
    position: relative;
    z-index: 1;
  }

  .company-title {
    font-size: 32px;
    font-weight: 900;
    background: linear-gradient(90deg, #ffffff, #a5b4fc);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
    letter-spacing: 1px;
    position: relative;
    z-index: 1;
  }

  .company-tagline {
    font-size: 16px;
    color: var(--text-3);
    font-weight: 500;
    position: relative;
    z-index: 1;
  }

  /* Content Grid */
  .profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
  }

  .profile-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 30px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s, box-shadow 0.3s;
  }

  .profile-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    border-color: rgba(165, 180, 252, 0.3);
  }

  .card-icon {
    width: 48px;
    height: 48px;
    background: rgba(165, 180, 252, 0.1);
    color: #a5b4fc;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 20px;
  }

  .card-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-1);
    margin-bottom: 12px;
  }

  .card-text {
    font-size: 14px;
    color: var(--text-3);
    line-height: 1.7;
  }

  .partner-highlight {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(74, 222, 128, 0.1);
    color: #4ade80;
    border: 1px solid rgba(74, 222, 128, 0.2);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 16px;
  }

  /* Contact Section */
  .contact-section {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 32px;
    display: flex;
    align-items: center;
    gap: 40px;
  }

  .contact-info {
    flex: 1;
  }

  .contact-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-1);
    margin-bottom: 24px;
  }

  .contact-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
  }

  .contact-item:last-child {
    margin-bottom: 0;
  }

  .contact-item i {
    color: #a5b4fc;
    font-size: 18px;
    margin-top: 4px;
  }

  .contact-detail h4 {
    font-size: 15px;
    font-weight: 700;
    color: #f1f5f9; /* Changed to bright white */
    margin-bottom: 4px;
  }

  .contact-detail p {
    font-size: 14px;
    color: #cbd5e1; /* Changed to light gray */
    line-height: 1.6;
  }

  .contact-map {
    flex: 1;
    height: 280px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
    position: relative;
  }
  
  @media (max-width: 768px) {
    .profile-grid { grid-template-columns: 1fr; }
    .contact-section { flex-direction: column; gap: 24px; }
    .contact-map { width: 100%; height: 250px; }
    .hero-banner { padding: 40px 20px; }
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Profil Perusahaan</h1>
    <p class="page-subtitle">Informasi detail mengenai Bina Raja Solusi</p>
  </div>
</div>

<div class="profile-container">
  
  <!-- Hero Section -->
  <div class="hero-banner">
    <img src="{{ asset('img/logo.png') }}" alt="Logo BRS" class="company-logo">
    <h2 class="company-title">PT. BINA RAJA SOLUSI</h2>
    <p class="company-tagline">Solusi Jaringan Masa Depan, Tercepat dan Terpercaya.</p>
  </div>

  <!-- Profil & Kemitraan -->
  <div class="profile-grid">
    <div class="profile-card">
      <div class="card-icon">
        <i class="fas fa-network-wired"></i>
      </div>
      <h3 class="card-title">Tentang Kami</h3>
      <p class="card-text">
        PT. Bina Raja Solusi (BRS) adalah perusahaan teknologi yang berfokus pada penyediaan jasa instalasi dan infrastruktur jaringan Wi-Fi. Kami berdedikasi untuk memberikan akses internet berkecepatan tinggi yang andal bagi masyarakat luas dan pelaku bisnis di wilayah Tangerang dan sekitarnya.
      </p>
    </div>

    <div class="profile-card">
      <div class="card-icon">
        <i class="fas fa-handshake"></i>
      </div>
      <h3 class="card-title">Kemitraan Strategis</h3>
      <p class="card-text">
        Demi menghadirkan performa dan keandalan <i>bandwidth</i> tingkat tinggi, kami menjalin kemitraan resmi yang di-support by <strong>PT. Media Cepat Indonesia</strong>. Sinergi ini memastikan bahwa layanan kami di-<i>back up</i> oleh infrastruktur berskala nasional yang tangguh dan stabil tanpa kompromi.
      </p>
      <div class="partner-highlight">
        <i class="fas fa-check-circle" style="margin-right: 4px;"></i> Verified Partner of PT Media Cepat Indonesia
      </div>
    </div>
  </div>

  <!-- Kontak & Alamat -->
  <div class="contact-section">
    <div class="contact-info">
      <h3 class="contact-title">Hubungi Kami</h3>
      
      <div class="contact-item">
        <i class="fas fa-map-marker-alt"></i>
        <div class="contact-detail">
          <h4>Alamat Kantor</h4>
          <p>Jl. Raya Permata No.11, Saga,<br>Kec. Balaraja, Kabupaten Tangerang,<br>Banten 15610</p>
        </div>
      </div>
      
      <div class="contact-item">
        <i class="fas fa-envelope"></i>
        <div class="contact-detail">
          <h4>Email Support</h4>
          <p>ptbinarajasolusi12345@gmail.com</p>
        </div>
      </div>
      
      <div class="contact-item">
        <i class="fas fa-phone-alt"></i>
        <div class="contact-detail">
          <h4>Telepon / WhatsApp</h4>
          <p>0877 - 6120 - 5991</p>
        </div>
      </div>
    </div>
    
    <div class="contact-map">
      <iframe src="https://maps.google.com/maps?q=-6.18102,106.462558&hl=id&z=16&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>

</div>
@endsection
