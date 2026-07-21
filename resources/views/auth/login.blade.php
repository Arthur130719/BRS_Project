<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — BRS Management</title>
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="/fonts/inter/fonts.css">
<link rel="stylesheet" href="/fonts/jetbrains/fonts.css">
<link rel="stylesheet" href="/fonts/fa/all.min.css">
<script defer src="/js/alpine.min.js"></script>

<style>
  :root {
    --bg-dark: #0f172a;
    --bg-panel: #1e293b;
    --bg-left: linear-gradient(135deg, #0f172a 0%, #020617 100%);
    --bg-right: #0f172a;
    --primary: #3b82f6;
    --primary-glow: rgba(59, 130, 246, 0.4);
    --text-main: #f8fafc;
    --text-title: #ffffff;
    --text-muted: #94a3b8;
    --border-color: rgba(255, 255, 255, 0.1);
    --input-bg: #0f172a;
    --input-border: #334155;
    --shadow-card: rgba(0,0,0,0.4);
  }

  body.light-theme {
    --bg-dark: #f8fafc;
    --bg-panel: #ffffff;
    --bg-left: linear-gradient(135deg, #e2e8f0 0%, #f8fafc 100%);
    --bg-right: #f1f5f9;
    --primary: #2563eb;
    --primary-glow: rgba(37, 99, 235, 0.3);
    --text-main: #1e293b;
    --text-title: #0f172a;
    --text-muted: #64748b;
    --border-color: rgba(0, 0, 0, 0.1);
    --input-bg: #ffffff;
    --input-border: #cbd5e1;
    --shadow-card: rgba(0,0,0,0.05);
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', sans-serif;
    background: var(--bg-dark);
    color: var(--text-main);
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    transition: background-color 0.4s, color 0.4s;
  }

  .split-layout {
    display: flex; width: 100%; min-height: 100vh; position: relative;
  }

  /* ═══ LEFT PANEL: PROFESSIONAL CORPORATE ═══ */
  .split-left {
    flex: 1.2;
    position: relative;
    display: none;
    flex-direction: column;
    justify-content: space-between;
    padding: 60px 80px;
    background: var(--bg-left);
    overflow: hidden;
    z-index: 1;
    border-right: 1px solid var(--border-color);
  }

  @media (min-width: 1024px) {
    .split-left { display: flex; }
  }

  /* Corporate Abstract Background */
  .bg-abstract {
    position: absolute; inset: 0; z-index: -1;
    background-image: 
      radial-gradient(circle at 15% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
      radial-gradient(circle at 85% 30%, rgba(59, 130, 246, 0.04) 0%, transparent 50%);
  }
  .bg-grid {
    position: absolute; inset: 0; z-index: -1; opacity: 0.3;
    background-image: linear-gradient(var(--border-color) 1px, transparent 1px),
                      linear-gradient(90deg, var(--border-color) 1px, transparent 1px);
    background-size: 60px 60px;
    mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
  }

  /* Header */
  .left-header { display: flex; align-items: center; gap: 16px; margin-bottom: 40px; }
  .left-logo-icon { font-size: 32px; color: var(--primary); }
  .left-logo-text h1 { font-size: 32px; font-weight: 800; letter-spacing: -1px; line-height: 1; color: var(--text-title); }
  .left-logo-text p { font-size: 11px; font-weight: 600; letter-spacing: 2px; color: var(--text-muted); text-transform: uppercase; margin-top: 4px; }

  /* Interactive Content Area */
  .content-area {
    flex-grow: 1;
    display: flex; align-items: center;
  }
  .tab-content h2 { font-size: 36px; font-weight: 700; margin-bottom: 20px; line-height: 1.2; letter-spacing: -0.5px; color: var(--text-title); }
  .tab-content p { font-size: 16px; color: var(--text-muted); line-height: 1.7; max-width: 500px; }

  /* Elegant Tabs */
  .smart-tabs {
    display: flex; gap: 40px;
    border-top: 1px solid var(--border-color);
    padding-top: 30px;
  }
  .smart-tab {
    cursor: pointer; position: relative; padding-bottom: 10px;
    transition: all 0.3s;
    opacity: 0.5;
  }
  .smart-tab h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--text-title); }
  .smart-tab::after {
    content: ''; position: absolute; bottom: 0; left: 0; width: 0%; height: 2px;
    background: var(--primary); transition: width 0.3s ease;
  }
  
  .smart-tab:hover { opacity: 0.8; }
  .smart-tab.active { opacity: 1; }
  .smart-tab.active::after { width: 100%; }


  /* ═══ RIGHT PANEL: CLEAN LOGIN ═══ */
  .split-right {
    flex: 1;
    position: relative;
    display: flex; align-items: center; justify-content: center;
    background: var(--bg-right);
    padding: 20px;
    z-index: 2;
  }

  .login-wrap { width: 100%; max-width: 400px; position: relative; z-index: 10; }

  /* Right Logo Area */
  .right-logo { text-align: center; margin-bottom: 40px; }
  .right-logo img { width: 64px; margin-bottom: 16px; }
  .right-logo h2 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; color: var(--text-title);}

  /* Form Container */
  .login-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 20px 40px var(--shadow-card);
  }

  .greeting {
    font-size: 16px; font-weight: 600; color: var(--text-title); margin-bottom: 24px;
  }
  .greeting span { color: var(--text-muted); font-size: 14px; font-weight: 400; display: block; margin-top: 4px; }

  /* Floating Labels - Professional */
  .form-group { position: relative; margin-bottom: 24px; }
  .form-input {
    width: 100%; background: var(--input-bg);
    border: 1px solid var(--input-border); border-radius: 8px;
    padding: 20px 16px 8px 16px; 
    color: var(--text-main); font-size: 14px; font-family: inherit;
    transition: all 0.2s; outline: none;
  }
  .form-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-glow);
  }
  
  .floating-label {
    position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); font-size: 14px; pointer-events: none; 
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .form-input:focus ~ .floating-label,
  .form-input:not(:placeholder-shown) ~ .floating-label {
    top: 14px; font-size: 10px; color: var(--primary); font-weight: 600;
  }

  .toggle-password {
    position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
    color: #64748b; font-size: 14px; cursor: pointer; transition: 0.2s;
  }
  .toggle-password:hover { color: white; }

  /* Corporate Button */
  .btn-login {
    width: 100%; padding: 14px;
    border: none; border-radius: 8px;
    background: var(--primary);
    color: white; font-size: 14px; font-weight: 600; letter-spacing: 0.5px;
    cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .btn-login:hover { background: #2563eb; transform: translateY(-1px); }
  .btn-login:active { transform: translateY(0); }

  .field-error { color: #ef4444; font-size: 12px; margin-top: 6px; }

  .footer { text-align: center; margin-top: 30px; font-size: 12px; color: var(--text-muted); }

  /* Theme Toggle Button */
  .theme-toggle {
    position: absolute;
    top: 30px; right: 40px;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.3s;
    z-index: 50;
    box-shadow: 0 4px 12px var(--shadow-card);
  }
  .theme-toggle:hover { color: var(--primary); border-color: var(--primary); }

</style>
</head>
<body :class="isDark ? '' : 'light-theme'" x-data="loginExperience()">

<div class="split-layout">
  
  <button class="theme-toggle" @click="toggleTheme()" title="Ubah Tema">
    <i class="fas" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
  </button>
  {{-- ═══ LEFT PANEL: PROFESSIONAL CORPORATE ═══ --}}
  <div class="split-left">
    <div class="bg-abstract"></div>
    <div class="bg-grid"></div>

    <div class="left-header">
      <div style="background: white; border-radius: 8px; padding: 8px; display: inline-flex;">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" style="height: 24px;">
      </div>
      <div class="left-logo-text">
        <h1>BRS</h1>
        <p>PT. Bina Raja Solusi</p>
      </div>
    </div>

    <!-- Content Area (Single dynamic element avoids transition glitches) -->
    <div class="content-area">
      <div class="tab-content">
        <h2 x-text="tabs[activeTab].title"></h2>
        <p x-text="tabs[activeTab].desc"></p>
      </div>
    </div>

    <!-- Elegant Tabs -->
    <div class="smart-tabs">
      <div class="smart-tab" :class="activeTab === 1 ? 'active' : ''" @click="activeTab = 1">
        <h3>Instalasi</h3>
      </div>
      <div class="smart-tab" :class="activeTab === 2 ? 'active' : ''" @click="activeTab = 2">
        <h3>Infrastruktur</h3>
      </div>
      <div class="smart-tab" :class="activeTab === 3 ? 'active' : ''" @click="activeTab = 3">
        <h3>Solusi Integrasi</h3>
      </div>
    </div>
  </div>

  {{-- ═══ RIGHT PANEL: CLEAN LOGIN ═══ --}}
  <div class="split-right">
    <div class="login-wrap">
      
      <div class="right-logo">
        <h2>Sistem Manajemen</h2>
      </div>

      <div class="login-card">
        <div class="greeting">
          Masuk ke Akun Anda
          <span>Masukkan kredensial Anda untuk melanjutkan</span>
        </div>

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="form-group">
            <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus placeholder=" ">
            <label class="floating-label" for="email">Alamat Email</label>
            @error('email')<div class="field-error">{{ $message }}</div>@enderror
          </div>

          <div class="form-group" x-data="{ show: false }">
            <input id="password" :type="show ? 'text' : 'password'" name="password" class="form-input" required placeholder=" ">
            <label class="floating-label" for="password">Kata Sandi</label>
            <i class="toggle-password fas" :class="show ? 'fa-eye' : 'fa-eye-slash'" @click="show = !show"></i>
            @error('password')<div class="field-error">{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="btn-login">
            Login ke Sistem
          </button>
        </form>
      </div>

      <div class="footer">
        BRS Management System &copy; {{ date('Y') }}
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('loginExperience', () => ({
    activeTab: 1,
    isDark: true,
    tabs: {
      1: {
        title: 'Jasa Instalasi Jaringan Wi-Fi',
        desc: 'Menghadirkan konektivitas stabil dan berkecepatan tinggi untuk kebutuhan residensial maupun bisnis komersial dengan standar operasional profesional.'
      },
      2: {
        title: 'Infrastruktur Jaringan Andal',
        desc: 'Membangun pondasi jaringan yang kuat dengan perangkat keras kualitas enterprise, memastikan tingkat keamanan maksimal dan ketersediaan layanan yang tinggi.'
      },
      3: {
        title: 'Solusi Manajemen Terintegrasi',
        desc: 'Kami menawarkan solusi menyeluruh mulai dari eksekusi fisik, perawatan infrastruktur, hingga sistem manajemen dan tagihan terpadu.'
      }
    },
    init() {
      // Check local storage for theme preference
      if (localStorage.getItem('theme') === 'light') {
        this.isDark = false;
      }
      
      setInterval(() => {
        this.activeTab = this.activeTab < 3 ? this.activeTab + 1 : 1;
      }, 8000);
    },
    toggleTheme() {
      this.isDark = !this.isDark;
      localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
    }
  }));
});
</script>
</body>
</html>
