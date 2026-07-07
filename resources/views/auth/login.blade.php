<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — NetCORE ISP</title>
<link rel="stylesheet" href="/fonts/inter/fonts.css">
<link rel="stylesheet" href="/fonts/jetbrains/fonts.css">
<link rel="stylesheet" href="/fonts/fa/all.min.css">
<script defer src="/js/alpine.min.js"></script>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', sans-serif;
    background: #0f172a;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    -webkit-font-smoothing: antialiased;
    position: relative;
    overflow: hidden;
  }

  /* Animated background grid */
  body::before {
    content: '';
    position: fixed; inset: 0;
    background-image:
      linear-gradient(rgba(99,102,241,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(99,102,241,0.04) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
  }

  /* Glow blobs */
  body::after {
    content: '';
    position: fixed;
    top: 20%; left: 15%;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(99,102,241,0.06) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .login-wrap {
    position: relative; z-index: 1;
    width: 100%; max-width: 420px;
  }

  /* Logo area */
  .login-logo {
    text-align: center;
    margin-bottom: 28px;
  }

  .logo-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px; height: 56px;
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(99,102,241,0.4);
    margin-bottom: 14px;
  }

  .logo-icon-wrap i { font-size: 24px; color: white; }

  .logo-name {
    font-size: 28px;
    font-weight: 800;
    color: #f1f5f9;
    letter-spacing: 2px;
  }

  .logo-sub {
    font-size: 11px;
    letter-spacing: 3px;
    color: #64748b;
    text-transform: uppercase;
    margin-top: 4px;
    font-family: 'JetBrains Mono', monospace;
  }

  /* Card */
  .login-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    padding: 36px 32px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(99,102,241,0.1);
  }

  /* Demo accounts */
  .demo-label {
    font-size: 10px;
    letter-spacing: 2px;
    color: #475569;
    text-align: center;
    margin-bottom: 10px;
    text-transform: uppercase;
  }

  .demo-pills {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 24px;
  }

  .demo-pill {
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 8px 6px;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 500;
  }

  .demo-pill:hover {
    border-color: #6366f1;
    color: #a5b4fc;
    background: rgba(99,102,241,0.08);
  }

  .demo-pill .pill-icon { font-size: 16px; margin-bottom: 4px; display: block; }
  .demo-pill .pill-name { font-size: 11px; font-weight: 600; }
  .demo-pill .pill-email { font-size: 9px; color: #475569; font-family: 'JetBrains Mono', monospace; margin-top: 2px; }

  /* Divider */
  .divider {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 20px;
    color: #475569; font-size: 11px; letter-spacing: 1px;
  }
  .divider::before, .divider::after {
    content: ''; flex: 1;
    height: 1px; background: #334155;
  }

  /* Form */
  .form-group { margin-bottom: 16px; }

  .form-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 6px;
  }

  .input-wrap {
    position: relative;
  }

  .input-icon {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: #475569; font-size: 13px;
    pointer-events: none;
  }

  .form-input {
    width: 100%;
    padding: 10px 12px 10px 36px;
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    color: #f1f5f9;
    font-size: 13px;
    font-family: 'JetBrains Mono', monospace;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
  }

  .form-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
  }

  .form-input::placeholder { color: #334155; }

  /* Error */
  .error-box {
    background: rgba(239,68,68,0.08);
    border: 1px solid rgba(239,68,68,0.25);
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 16px;
    font-size: 12px;
    color: #fca5a5;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .field-error { font-size: 11px; color: #fca5a5; margin-top: 4px; }

  /* Submit */
  .btn-login {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .btn-login:hover {
    box-shadow: 0 8px 20px rgba(99,102,241,0.35);
    transform: translateY(-1px);
  }

  .btn-login:active { transform: translateY(0); }

  /* Footer */
  .login-footer {
    text-align: center;
    margin-top: 24px;
    font-size: 11px;
    color: #334155;
    font-family: 'JetBrains Mono', monospace;
  }
</style>
</head>
<body>
<div class="login-wrap" x-data="{
  fillDemo(email, role) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
  }
}">
  <div class="login-logo">
    <div class="logo-icon-wrap">
      <i class="fas fa-network-wired"></i>
    </div>
    <div class="logo-name">NetCORE</div>
    <div class="logo-sub">ISP Management System</div>
  </div>

  <div class="login-card">
    <!-- Demo Accounts -->
    <div class="demo-label">— Akun Demo —</div>
    <div class="demo-pills">
      <button type="button" class="demo-pill" @click="fillDemo('admin@netcore.id', 'admin')">
        <span class="pill-icon">👑</span>
        <div class="pill-name">Admin</div>
        <div class="pill-email">admin@netcore.id</div>
      </button>
      <button type="button" class="demo-pill" @click="fillDemo('kasir@netcore.id', 'kasir')">
        <span class="pill-icon">💰</span>
        <div class="pill-name">Kasir</div>
        <div class="pill-email">kasir@netcore.id</div>
      </button>
      <button type="button" class="demo-pill" @click="fillDemo('teknisi@netcore.id', 'teknisi')">
        <span class="pill-icon">🔧</span>
        <div class="pill-name">Teknisi</div>
        <div class="pill-email">teknisi@netcore.id</div>
      </button>
    </div>

    <div class="divider">LOGIN</div>

    <!-- Session error -->
    @if (session('status'))
      <div class="error-box" style="border-color:rgba(16,185,129,0.3);background:rgba(16,185,129,0.08);color:#6ee7b7;">
        <i class="fas fa-circle-check"></i> {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <div class="input-wrap">
          <i class="input-icon fas fa-envelope"></i>
          <input id="email" type="email" name="email" class="form-input"
                 value="{{ old('email') }}" required autofocus
                 placeholder="nama@netcore.id">
        </div>
        @error('email')
          <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-wrap">
          <i class="input-icon fas fa-lock"></i>
          <input id="password" type="password" name="password" class="form-input"
                 required placeholder="••••••••">
        </div>
        @error('password')
          <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn-login">
        <i class="fas fa-right-to-bracket"></i> MASUK
      </button>
    </form>
  </div>

  <div class="login-footer">
    v3.2.1 &mdash; NetCORE &copy; {{ date('Y') }} &mdash; All rights reserved
  </div>
</div>
</body>
</html>
