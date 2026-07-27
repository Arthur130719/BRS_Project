<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') — BRS</title>
<meta name="format-detection" content="telephone=no">
{{-- ═══ ASSETS LOKAL — tidak butuh internet ═══ --}}
<link rel="stylesheet" href="/fonts/inter/fonts.css">
<link rel="stylesheet" href="/fonts/jetbrains/fonts.css">
<link rel="stylesheet" href="/fonts/fa/all.min.css">
<script defer src="/js/alpine.min.js"></script>
<script src="/js/chart.min.js"></script>

{{-- ═══ PWA META TAGS ═══ --}}
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0f172a">
<link rel="apple-touch-icon" href="/img/logo.png">

<style>
/* ═══════════════════════════════════════════
   VARIABLES & RESET
   ═══════════════════════════════════════════ */
:root {
  /* Backgrounds */
  --bg-base:     #0f172a;   /* slate-900 */
  --bg-surface:  #1e293b;   /* slate-800 */
  --bg-elevated: #334155;   /* slate-700 */
  --bg-input:    #0f172a;
  
  /* Borders */
  --border:       rgba(255, 255, 255, 0.08);
  --border-light: rgba(255, 255, 255, 0.15);
  --border-focus: rgba(99, 102, 241, 0.5);

  /* Accents */
  --indigo:      #6366f1;  /* indigo-500 */
  --indigo-dark: #4f46e5;
  --indigo-dim:  rgba(99,102,241,0.15);
  --indigo-glow: rgba(99,102,241,0.25);
  
  --green:       #10b981;  /* emerald-500 */
  --green-d:     rgba(16,185,129,0.12);
  --amber:       #f59e0b;  /* amber-500 */
  --amber-d:     rgba(245,158,11,0.12);
  --red:         #ef4444;  /* red-500 */
  --red-d:       rgba(239,68,68,0.12);
  --sky:         #0ea5e9;  /* sky-500 */
  --sky-d:       rgba(14,165,233,0.12);
  --purple:      #a855f7;
  --purple-d:    rgba(168,85,247,0.12);

  /* Text */
  --text-1:  #f1f5f9;   /* slate-100 */
  --text-2:  #94a3b8;   /* slate-400 */
  --text-3:  #64748b;   /* slate-500 */
  --text-4:  #475569;   /* slate-600 */

  /* Additional Header Variables */
  --bg-header: rgba(30, 41, 59, 0.85);
  --bg-search: rgba(0,0,0,0.2);
  --logo-gradient: linear-gradient(90deg, #ffffff, #a5b4fc);
  --logo-sub: #94a3b8;
  --logout-btn: #fca5a5;

  /* Layout */
  --radius-sm: 6px;
  --radius:    8px;
  --radius-lg: 12px;
  --radius-xl: 16px;

  /* Shadows */
  --shadow-sm:  0 1px 3px rgba(0,0,0,0.3);
  --shadow:     0 4px 16px rgba(0,0,0,0.4);
  --shadow-lg:  0 8px 32px rgba(0,0,0,0.5);
  --shadow-ind: 0 0 0 3px var(--indigo-glow);

  --transition: 0.15s ease;
}

body.light-theme {
  --bg-base:     #f8fafc;
  --bg-surface:  #ffffff;
  --bg-elevated: #f1f5f9;
  --bg-input:    #ffffff;
  --border:       rgba(0, 0, 0, 0.1);
  --border-light: rgba(0, 0, 0, 0.15);
  --border-focus: rgba(37, 99, 235, 0.5);
  --indigo:      #3b82f6;
  --indigo-dark: #2563eb;
  --indigo-dim:  rgba(59,130,246,0.15);
  --indigo-glow: rgba(59,130,246,0.25);
  --green:       #10b981;
  --green-d:     rgba(16,185,129,0.12);
  --amber:       #f59e0b;
  --amber-d:     rgba(245,158,11,0.12);
  --red:         #ef4444;
  --red-d:       rgba(239,68,68,0.12);
  --sky:         #0ea5e9;
  --sky-d:       rgba(14,165,233,0.12);
  --purple:      #a855f7;
  --purple-d:    rgba(168,85,247,0.12);
  --text-1:  #0f172a;
  --text-2:  #334155;
  --text-3:  #475569;
  --text-4:  #64748b;
  --bg-header: rgba(255, 255, 255, 0.95);
  --bg-search: rgba(0,0,0,0.05);
  --logo-gradient: linear-gradient(90deg, #0f172a, #3b82f6);
  --logo-sub: #64748b;
  --logout-btn: #ef4444;
  --shadow-sm:  0 1px 2px rgba(0,0,0,0.05);
  --shadow:     0 4px 12px rgba(0,0,0,0.08);
  --shadow-lg:  0 8px 24px rgba(0,0,0,0.12);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; }
body {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  background: var(--bg-base);
  color: var(--text-1);
  min-height: 100vh;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

/* Scrollbar */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: var(--bg-base); }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--border-light); }

/* ═══════════════════════════════════════════
   LAYOUT (TOP NAVIGATION)
   ═══════════════════════════════════════════ */
.app-layout { 
  display: flex; 
  flex-direction: column;
  min-height: 100vh; 
}

/* ── Top Header ── */
.top-header {
  background: var(--bg-header);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  flex-direction: column;
}

.header-main {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.03);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.logo-wrap { display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 4px 8px 4px 0; transition: transform 0.2s; }
.logo-wrap:hover { transform: scale(1.02); }
.logo-wrap img { 
  width: 38px; 
  height: 38px; 
  object-fit: contain; 
  background: #ffffff; /* White background prevents clashing if logo has dark text/shapes */
  padding: 4px; 
  border-radius: 8px; 
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.logo-text { display: flex; flex-direction: column; justify-content: center; }
.logo-title { 
  font-size: 18px; 
  font-weight: 800; 
  background: var(--logo-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: 0.5px; 
  line-height: 1.1;
}
.logo-sub { 
  font-size: 10px; 
  color: var(--logo-sub); 
  font-weight: 600; 
  text-transform: uppercase;
  letter-spacing: 1px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-search);
  border: 1px solid var(--border);
  border-radius: 100px;
  padding: 0 14px;
  height: 36px;
  transition: all var(--transition);
}
.search-wrap:focus-within { border-color: var(--indigo); background: rgba(0,0,0,0.4); box-shadow: 0 0 0 2px var(--indigo-glow); }
.search-wrap input { background: none; border: none; outline: none; color: var(--text-1); font-family: 'JetBrains Mono', monospace; font-size: 12px; width: 220px; }
.search-wrap input::placeholder { color: var(--text-4); }
.search-wrap i { color: var(--text-3); font-size: 12px; }

.icon-btn {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  color: var(--text-2);
  transition: all var(--transition);
  position: relative;
  text-decoration: none;
}
.icon-btn:hover { border-color: var(--border-light); color: var(--text-1); background: rgba(255,255,255,0.08); }

.notif-badge {
  position: absolute;
  top: -2px; right: -2px;
  background: var(--red);
  color: white;
  width: 16px; height: 16px;
  border-radius: 50%;
  font-size: 9px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
  border: 2px solid var(--bg-surface);
}

.header-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 12px 4px 4px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border);
  border-radius: 100px;
}
.user-avatar {
  width: 28px; height: 28px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700;
}
.user-avatar.admin    { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.user-avatar.kasir    { background: linear-gradient(135deg, #10b981, #059669); }
.user-avatar.teknisi  { background: linear-gradient(135deg, #f59e0b, #d97706); }
.user-info { display: flex; flex-direction: column; }
.user-name { font-size: 12px; font-weight: 600; color: var(--text-1); line-height: 1; }
.user-role { font-size: 9px; color: var(--text-3); font-weight: 600; text-transform: uppercase; margin-top: 3px; }

.logout-btn {
  color: var(--logout-btn);
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  font-size: 14px;
  transition: color var(--transition);
}
.logout-btn:hover { color: var(--red); }

/* ── Top Navigation Bar ── */
.top-nav {
  display: flex;
  align-items: center;
  padding: 0 24px 10px;
  gap: 6px;
  flex-wrap: wrap;
}

/* ── Mobile Hamburger Menu ── */
.hamburger-btn {
  display: none;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 8px;
  color: #fff;
  padding: 8px 12px;
  cursor: pointer;
  font-size: 16px;
  align-items: center;
  gap: 6px;
}
.mobile-nav-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.65);
  z-index: 1000;
  backdrop-filter: blur(2px);
}
.mobile-nav-panel {
  display: none;
  position: fixed;
  top: 0; left: 0; right: 0;
  background: #1a1f2e;
  border-bottom: 2px solid rgba(59,130,246,0.3);
  z-index: 1001;
  max-height: 85vh;
  overflow-y: auto;
  padding: 0;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
.mobile-nav-panel.open { display: block; }
.mob-nav-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 16px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  background: #141824;
}
.mob-nav-header span { font-weight: 700; color: #fff; font-size: 15px; }
.mob-nav-header button { background:none;border:none;color:#aaa;font-size:24px;cursor:pointer;line-height:1;padding:0; }
.mobile-nav-panel a {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 13px 20px;
  color: #c9d1e0;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  border-bottom: 1px solid rgba(255,255,255,0.04);
  transition: background 0.15s, color 0.15s;
}
.mobile-nav-panel a:hover { background: rgba(255,255,255,0.06); color: #fff; }
.mobile-nav-panel a.active { background: rgba(59,130,246,0.15); color: #60a5fa; border-left: 3px solid #3b82f6; }
.mobile-nav-panel a i { width: 18px; text-align: center; font-size: 14px; }
.mob-section-title {
  font-size: 10px;
  font-weight: 700;
  color: rgba(255,255,255,0.3);
  text-transform: uppercase;
  letter-spacing: 1.5px;
  padding: 14px 20px 6px;
  background: rgba(0,0,0,0.2);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 16px;
  color: var(--text-2);
  font-size: 13.5px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;
  position: relative;
  border-bottom: 2px solid transparent;
  cursor: pointer;
}
.nav-item:hover { color: var(--text-1); }
.nav-item.active {
  color: var(--indigo);
  border-bottom-color: var(--indigo);
  font-weight: 600;
}
.nav-item i { font-size: 14px; transition: transform 0.2s ease; }
.nav-item:hover i { transform: scale(1.1); color: var(--indigo); }

.nav-badge {
  font-size: 10px;
  font-family: 'JetBrains Mono', monospace;
  padding: 1px 6px;
  border-radius: 100px;
  font-weight: 600;
  margin-left: 4px;
}
.nav-badge.red    { background: var(--red-d);   color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
.nav-badge.green  { background: var(--green-d); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }

/* Dropdown Menu */
.dropdown-wrap {
  position: relative;
}
.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  min-width: 200px;
  background: var(--bg-elevated);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  padding: 8px;
  z-index: 100;
  display: flex;
  flex-direction: column;
  gap: 2px;
  
  /* CSS transition for dropdown */
  opacity: 0;
  visibility: hidden;
  transform: translateY(10px);
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  pointer-events: none;
}

.dropdown-wrap:hover .dropdown-menu,
.dropdown-wrap:focus .dropdown-menu,
.dropdown-wrap:focus-within .dropdown-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
  pointer-events: auto;
}
.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  color: var(--text-1);
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  border-radius: var(--radius);
  transition: all var(--transition);
}
.dropdown-item:hover {
  background: rgba(255,255,255,0.06);
  padding-left: 18px;
  color: var(--indigo);
}
.dropdown-item.active {
  background: var(--indigo-dim);
  color: #a5b4fc;
}
.dropdown-item i { width: 16px; text-align: center; }

/* ── Main Content Area ── */
.page-content-wrapper {
  flex: 1;
  padding: 32px 30px 60px; /* left/right padding to align with navbar */
  background: var(--bg-base);
}

@keyframes pageTransition {
  from { opacity: 0; }
  to { opacity: 1; }
}

.page-content {
  max-width: 1300px;
  margin: 0 auto;
  width: 100%;
  animation: pageTransition 0.2s ease-out forwards;
}

/* ═══════════════════════════════════════════
   COMPONENTS
   ═══════════════════════════════════════════ */

/* ── Page header ── */
.page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 12px;
}
.page-header-title h1 {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-1);
  letter-spacing: -0.5px;
}
.page-header-title p {
  font-size: 13px;
  color: var(--text-3);
  margin-top: 4px;
}
.page-header-actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ── Stat cards ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.stat-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 20px;
  position: relative;
  overflow: hidden;
  transition: transform var(--transition), border-color var(--transition);
  box-shadow: var(--shadow-sm);
}
.stat-card:hover { transform: translateY(-3px); border-color: var(--border-light); box-shadow: var(--shadow); }
.stat-card::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}
.stat-card.indigo::after { background: linear-gradient(90deg, var(--indigo), transparent); }
.stat-card.green::after  { background: linear-gradient(90deg, var(--green), transparent); }
.stat-card.amber::after  { background: linear-gradient(90deg, var(--amber), transparent); }
.stat-card.red::after    { background: linear-gradient(90deg, var(--red), transparent); }
.stat-card.sky::after    { background: linear-gradient(90deg, var(--sky), transparent); }

.stat-icon {
  width: 42px; height: 42px;
  border-radius: var(--radius-lg);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  margin-bottom: 16px;
}
.stat-icon.indigo { background: var(--indigo-dim); color: #a5b4fc; }
.stat-icon.green  { background: var(--green-d);    color: #6ee7b7; }
.stat-icon.amber  { background: var(--amber-d);    color: #fcd34d; }
.stat-icon.red    { background: var(--red-d);      color: #fca5a5; }
.stat-icon.sky    { background: var(--sky-d);      color: #7dd3fc; }

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: var(--text-1);
  line-height: 1;
  font-family: 'JetBrains Mono', monospace;
}
.stat-label {
  font-size: 13px;
  color: var(--text-3);
  margin-top: 6px;
  font-weight: 500;
}
.stat-sub {
  font-size: 11px;
  margin-top: 10px;
  font-family: 'JetBrains Mono', monospace;
  display: flex;
  align-items: center;
  gap: 4px;
}
.stat-sub.up   { color: var(--green); }
.stat-sub.down { color: var(--red); }
.stat-sub.mute { color: var(--text-4); }

/* ── Card ── */
.card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}
.card-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-1);
}
.card-subtitle {
  font-size: 11px;
  color: var(--text-3);
  margin-top: 2px;
}
.card-body { padding: 20px; }
.card-body-flush { padding: 0; }

/* ── Buttons ── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: var(--radius);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition);
  border: 1px solid transparent;
  text-decoration: none;
  white-space: nowrap;
}
.btn-sm  { padding: 6px 12px; font-size: 12px; }
.btn-xs  { padding: 4px 8px;  font-size: 11px; }

.btn-primary { background: var(--indigo); color: white; border-color: var(--indigo); }
.btn-primary:hover { background: var(--indigo-dark); border-color: var(--indigo-dark); box-shadow: 0 0 12px rgba(99,102,241,0.3); transform: translateY(-1px); }

.btn-ghost { background: transparent; border-color: var(--border); color: var(--text-2); }
.btn-ghost:hover { background: var(--bg-elevated); border-color: var(--border-light); color: var(--text-1); }

.btn-success { background: var(--green-d); border-color: rgba(16,185,129,0.35); color: #6ee7b7; }
.btn-success:hover { background: rgba(16,185,129,0.2); }

.btn-danger { background: var(--red-d); border-color: rgba(239,68,68,0.35); color: #fca5a5; }
.btn-danger:hover { background: rgba(239,68,68,0.2); }

.btn-warning { background: var(--amber-d); border-color: rgba(245,158,11,0.35); color: #fcd34d; }
.btn-warning:hover { background: rgba(245,158,11,0.2); }

.btn-sky { background: var(--sky-d); border-color: rgba(14,165,233,0.35); color: #7dd3fc; }
.btn-sky:hover { background: rgba(14,165,233,0.2); }

/* ── Forms ── */
.form-label { display: block; font-size: 11px; font-weight: 600; color: var(--text-3); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 6px; }
.form-control {
  width: 100%; padding: 10px 14px; background: rgba(0,0,0,0.15); border: 1px solid var(--border);
  border-radius: var(--radius); color: var(--text-1); font-size: 13px; font-family: 'Inter', sans-serif;
  transition: all var(--transition); outline: none;
}
.form-control:focus { border-color: var(--indigo); box-shadow: var(--shadow-ind); background: rgba(0,0,0,0.25); }
.form-control::placeholder { color: var(--text-4); }
select.form-control { cursor: pointer; }
select.form-control option { background: var(--bg-surface); }
.form-control-mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; }
.form-group { margin-bottom: 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-error { font-size: 11px; color: #fca5a5; margin-top: 4px; }
.form-hint { font-size: 11px; color: var(--text-4); margin-top: 4px; }

/* ── Table ── */
.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table thead th {
  padding: 12px 16px; text-align: left; font-size: 10px; font-weight: 700;
  letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-4);
  border-bottom: 1px solid var(--border); white-space: nowrap; background: rgba(0,0,0,0.1);
}
.table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.03); transition: background var(--transition); }
.table tbody tr:hover { background: rgba(255,255,255,0.02); }
.table tbody tr:last-child { border-bottom: none; }
.table td { padding: 14px 16px; font-size: 13px; vertical-align: middle; }

/* ── Badges ── */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.badge::before { content: '●'; font-size: 7px; }
.badge-active  { background: rgba(16,185,129,0.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.25); }
.badge-suspend { background: rgba(245,158,11,0.12); color: #fcd34d; border: 1px solid rgba(245,158,11,0.25); }
.badge-inactive{ background: rgba(239,68,68,0.12);  color: #fca5a5; border: 1px solid rgba(239,68,68,0.25); }
.badge-paid    { background: rgba(16,185,129,0.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.25); }
.badge-unpaid  { background: rgba(239,68,68,0.12);  color: #fca5a5; border: 1px solid rgba(239,68,68,0.25); }
.badge-partial { background: rgba(245,158,11,0.12); color: #fcd34d; border: 1px solid rgba(245,158,11,0.25); }
.badge-online  { background: rgba(16,185,129,0.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.25); }
.badge-offline { background: rgba(239,68,68,0.12);  color: #fca5a5; border: 1px solid rgba(239,68,68,0.25); }
.badge-weak    { background: rgba(245,158,11,0.12); color: #fcd34d; border: 1px solid rgba(245,158,11,0.25); }
.badge-auto    { background: rgba(99,102,241,0.12); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.25); }
.badge-manual  { background: rgba(14,165,233,0.12); color: #7dd3fc; border: 1px solid rgba(14,165,233,0.25); }

/* ── Mono text ── */
.mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #7dd3fc; }
.mono-mute { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--text-3); }

/* ── Table toolbar ── */
.table-toolbar { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--border); gap: 12px; flex-wrap: wrap; }
.toolbar-search { display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.15); border: 1px solid var(--border); border-radius: var(--radius); padding: 0 12px; height: 34px; transition: border-color var(--transition); }
.toolbar-search:focus-within { border-color: var(--indigo); }
.toolbar-search input { background: none; border: none; outline: none; color: var(--text-1); font-size: 12px; font-family: 'JetBrains Mono', monospace; width: 180px; }
.toolbar-search input::placeholder { color: var(--text-4); }
.toolbar-search i { color: var(--text-4); font-size: 12px; }
.toolbar-right { display: flex; gap: 10px; align-items: center; }

/* ── Modal ── */
.modal-overlay { position: fixed; inset: 0; z-index: 600; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal { background: var(--bg-surface); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-xl); width: 100%; max-width: 520px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); }
.modal-lg { max-width: 680px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.1); border-radius: var(--radius-xl) var(--radius-xl) 0 0; flex-shrink: 0;}
.modal-title { font-size: 16px; font-weight: 600; color: var(--text-1); }
.modal-close { width: 30px; height: 30px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.05); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-3); transition: all var(--transition); font-size: 14px; }
.modal-close:hover { color: var(--red); border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.1); }
.modal-body { padding: 24px; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; background: rgba(0,0,0,0.1); border-radius: 0 0 var(--radius-xl) var(--radius-xl); flex-shrink: 0;}
.modal > form { display: flex; flex-direction: column; flex: 1; min-height: 0; }

/* ── Alert / Toast ── */
.alert { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border-radius: var(--radius); border: 1px solid; font-size: 13px; margin-bottom: 16px; }
.alert-success { background: var(--green-d); border-color: rgba(16,185,129,0.3); color: #6ee7b7; }
.alert-danger  { background: var(--red-d);   border-color: rgba(239,68,68,0.3);  color: #fca5a5; }
.alert-warning { background: var(--amber-d); border-color: rgba(245,158,11,0.3); color: #fcd34d; }
.alert-info    { background: var(--sky-d);   border-color: rgba(14,165,233,0.3); color: #7dd3fc; }

/* ── Misc utilities ── */
.text-mute  { color: var(--text-3); }
.text-right { text-align: right; }
.text-center{ text-align: center; }
.font-mono  { font-family: 'JetBrains Mono', monospace; font-size: 12px; }
.mb-4 { margin-bottom: 16px; }
.mb-6 { margin-bottom: 24px; }
.gap-4 { gap: 16px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
.flex    { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.w-full { width: 100%; }

/* Info rows */
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-row .key { color: var(--text-3); }
.info-row .val { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--text-2); }

/* Activity feed */
.feed-item { display: flex; gap: 14px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
.feed-item:last-child { border-bottom: none; }
.feed-icon { width: 32px; height: 32px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.feed-icon.indigo { background: var(--indigo-dim); color: #a5b4fc; }
.feed-icon.green  { background: var(--green-d);    color: #6ee7b7; }
.feed-icon.red    { background: var(--red-d);      color: #fca5a5; }
.feed-icon.amber  { background: var(--amber-d);    color: #fcd34d; }
.feed-body { flex: 1; min-width: 0; }
.feed-title { font-size: 13px; font-weight: 500; color: var(--text-1); }
.feed-desc  { font-size: 11px; color: var(--text-3); margin-top: 2px; font-family: 'JetBrains Mono', monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.feed-time  { font-size: 11px; color: var(--text-4); flex-shrink: 0; font-family: 'JetBrains Mono', monospace; }

/* Charts */
.chart-container { position: relative; height: 200px; }

/* OLT ports */
.port-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 6px; }
.olt-port { aspect-ratio: 1; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-family: 'JetBrains Mono', monospace; cursor: pointer; transition: transform var(--transition); border: 1px solid; }
.olt-port:hover { transform: scale(1.1); box-shadow: var(--shadow); }
.olt-port.up     { background: var(--green-d); border-color: rgba(16,185,129,0.3); color: #6ee7b7; }
.olt-port.down   { background: var(--red-d);   border-color: rgba(239,68,68,0.3);  color: #fca5a5; }
.olt-port.unused { background: rgba(255,255,255,0.03); border-color: var(--border); color: var(--text-4); }

/* Signal bars */
.signal-bars { display: flex; gap: 3px; align-items: flex-end; }
.signal-bar { width: 4px; border-radius: 2px; }
.signal-bar.filled.excellent { background: var(--green); }
.signal-bar.filled.good      { background: #7dd3fc; }
.signal-bar.filled.weak      { background: var(--amber); }
.signal-bar.filled.poor      { background: var(--red); }
.signal-bar:not(.filled)     { background: var(--border); }

/* Progress bar */
.progress-bar { height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden; margin-top: 6px; }
.progress-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
.progress-fill.indigo { background: linear-gradient(90deg, var(--indigo-dark), var(--indigo)); box-shadow: 0 0 8px rgba(99,102,241,0.5);}
.progress-fill.green  { background: linear-gradient(90deg, #059669, var(--green)); box-shadow: 0 0 8px rgba(16,185,129,0.5);}
.progress-fill.amber  { background: linear-gradient(90deg, #d97706, var(--amber)); box-shadow: 0 0 8px rgba(245,158,11,0.5);}
.progress-fill.red    { background: linear-gradient(90deg, #dc2626, var(--red)); box-shadow: 0 0 8px rgba(239,68,68,0.5);}

/* ── Laravel pagination override ── */
nav[aria-label="Pagination"] { display: flex; align-items: center; justify-content: flex-end; gap: 6px; padding: 14px 20px; border-top: 1px solid var(--border); }
nav[aria-label="Pagination"] a, nav[aria-label="Pagination"] span, nav[aria-label="Pagination"] button { display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 30px; padding: 0 10px; border-radius: var(--radius-sm); font-size: 13px; background: rgba(0,0,0,0.15); border: 1px solid var(--border); color: var(--text-2); text-decoration: none; transition: all var(--transition); cursor: pointer; }
nav[aria-label="Pagination"] a:hover { border-color: var(--indigo); color: var(--text-1); background: rgba(0,0,0,0.3); }
nav[aria-label="Pagination"] span[aria-current="page"] { background: var(--indigo); border-color: var(--indigo); color: white; box-shadow: 0 0 10px rgba(99,102,241,0.3); }
nav[aria-label="Pagination"] span[aria-disabled="true"] { opacity: 0.4; cursor: not-allowed; }

/* ── Empty state ── */
.empty-state { padding: 60px 20px; text-align: center; }
.empty-state i { font-size: 42px; color: var(--text-4); margin-bottom: 16px; }
.empty-state h3 { font-size: 16px; color: var(--text-2); font-weight: 600; }
.empty-state p { font-size: 13px; color: var(--text-4); margin-top: 6px; }

/* ── Live Search Dropdown ── */
.search-wrap-container { position: relative; }
.search-spinner { color: var(--indigo); font-size: 14px; margin-left: 8px; }
.live-search-results {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 320px;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  z-index: 200;
  max-height: 400px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.live-search-group { padding: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.live-search-group:last-child { border-bottom: none; }
.live-search-title { font-size: 11px; font-weight: 600; color: var(--text-3); margin-bottom: 6px; padding: 0 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.live-search-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px; border-radius: var(--radius-sm); text-decoration: none;
  transition: background 0.2s;
}
.live-search-item:hover { background: rgba(255,255,255,0.05); }
.ls-main { font-size: 13px; color: var(--text-1); font-weight: 500; }
.ls-sub { font-size: 11px; color: var(--text-4); font-family: 'JetBrains Mono', monospace; }


/* ── Mobile Responsive ── */
@media (max-width: 768px) {
  .header-main { 
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
  }
  .header-left { flex: 1; }
  .header-right { display: contents; }
  
  /* Ikon-ikon di header berjejer kanan */
  button[title="Ubah Tema"] { order: 2; }
  .notif-icons-group { order: 3; display:flex; align-items:center; gap:6px; }
  .header-user { order: 4; }
  .logout-btn { order: 5; }
  
  .search-wrap-container { order: 6; width: 100%; flex-basis: 100%; }
  .search-wrap { width: 100%; }
  
  .user-info { display: none; }
  .logo-sub { display: none; }

  /* Sembunyikan top-nav di mobile, ganti hamburger */
  .top-nav { display: none !important; }
  .hamburger-btn { display: flex; align-items: center; justify-content: center; }
  
  .page-content-wrapper { padding: 16px 12px 30px; }
  .page-header { flex-direction: column; align-items: flex-start; }
  
  /* Sembunyikan elemen desktop-only di mobile */
  .desktop-only { display: none !important; }
  
  /* Dropdowns adjustments for mobile centering */
  .dropdown-menu { left: 50%; transform: translateX(-50%) translateY(10px); min-width: 180px; }
  .dropdown-wrap:hover .dropdown-menu,
  .dropdown-wrap:focus .dropdown-menu,
  .dropdown-wrap:focus-within .dropdown-menu {
    transform: translateX(-50%) translateY(0);
  }
  
  /* Stack grid layouts */
  .stats-grid, .grid-2, .grid-3 { grid-template-columns: 1fr !important; }
  
  /* Tables */
  .table-responsive, .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; display: block; }
}
</style>
@stack('styles')
</head>
<body x-data="globalTheme()" :class="isDark ? '' : 'light-theme'">

<div class="app-layout">
  
  <!-- ── TOP NAVIGATION HEADER ── -->
  <header class="top-header">
    <div class="header-main">
      <div class="header-left">
        <a href="{{ route('company.profile') }}" class="logo-wrap">
          <img src="{{ asset('img/logo.png') }}" alt="Logo">
          <div class="logo-text">
            <span class="logo-title">BRS</span>
            <span class="logo-sub">Bina Raja Solusi</span>
          </div>
        </a>
      </div>
      
      <div class="header-right">
        <div class="search-wrap-container">
          <form action="{{ route('search') }}" method="GET" class="search-wrap" id="searchForm">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" id="searchInput" value="{{ request('q') }}" placeholder="Cari pelanggan, IP, invoice..." autocomplete="off">
            <div class="search-spinner" id="searchSpinner" style="display: none;"><i class="fas fa-circle-notch fa-spin"></i></div>
          </form>
          <!-- Live Search Dropdown -->
          <div class="live-search-results" id="liveSearchResults" style="display: none;"></div>
        </div>
        
        <button @click="toggleTheme()" class="icon-btn" title="Ubah Tema" style="margin-right: 8px;">
          <i class="fas" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
        </button>
        
        <div class="notif-icons-group" style="display:flex; align-items:center; gap:6px; margin-right:4px;">
          <a href="{{ route('notifikasi.index', ['type' => 'chat']) }}" class="icon-btn" id="chat-icon-btn">
            <i class="fas fa-comments"></i>
            @if(isset($chatUnreadCount) && $chatUnreadCount > 0)
              <span class="notif-badge" style="background-color: #3b82f6;">{{ $chatUnreadCount > 99 ? '99' : $chatUnreadCount }}</span>
            @endif
          </a>
          <a href="{{ route('notifikasi.index') }}" class="icon-btn" id="bell-icon-btn">
            <i class="fas fa-bell"></i>
            @if(isset($notifUnreadCount) && $notifUnreadCount > 0)
              <span class="notif-badge">{{ $notifUnreadCount > 99 ? '99' : $notifUnreadCount }}</span>
            @endif
          </a>
        </div>{{-- end notif-icons-group --}}

        {{-- Hamburger button (mobile only) --}}
        <button class="hamburger-btn" id="hamburger-btn" onclick="toggleMobileNav()" title="Menu">
          <i class="fas fa-bars"></i>
        </button>
        
        <div class="header-user">
          <div class="user-avatar {{ auth()->user()->role }}">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </div>
          <div class="user-info">
            <span class="user-name">{{ auth()->user()->name }}</span>
            <span class="user-role">{{ auth()->user()->role }}</span>
          </div>
        </div>
        
        <form method="POST" action="{{ route('logout') }}" style="display:contents">
          @csrf
          <button type="submit" class="logout-btn" title="Logout">
            <i class="fas fa-right-from-bracket"></i>
          </button>
        </form>
      </div>
    </div>

    <!-- Navigation Bar -->
    <nav class="top-nav">
      <!-- Utama -->
      <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-gauge-high"></i> Dashboard
      </a>
      @if(auth()->user()->role === 'admin' || auth()->user()->role === 'kasir')
      <a href="{{ route('pelanggan.index') }}" class="nav-item {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> Pelanggan
      </a>
      <a href="{{ route('permohonan.index') }}" class="nav-item {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">
        <i class="fas fa-user-plus"></i> Permohonan Baru
        @php $pendingCount = \App\Models\Permohonan::where('status', 'pending')->count(); @endphp
        @if($pendingCount) <span class="nav-badge red" style="margin-left: 6px; padding: 2px 6px; border-radius: 4px; background: rgba(239,68,68,0.2); color: #fca5a5; font-size: 10px; font-weight: bold;">{{ $pendingCount }}</span> @endif
      </a>
      @endif
      
      <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i> Job Order
        @php
            $ticketQ = \App\Models\Ticket::where('status', 'Pending');
            if (auth()->user()->role === 'teknisi') $ticketQ->where('teknisi_id', auth()->id());
            $pTicketCount = $ticketQ->count();
        @endphp
        @if($pTicketCount) <span class="nav-badge orange ticket-badge" style="margin-left: 6px; padding: 2px 6px; border-radius: 4px; background: rgba(245,158,11,0.2); color: #fcd34d; font-size: 10px; font-weight: bold;">{{ $pTicketCount }}</span> @endif
      </a>

      @if(auth()->user()->hasRole(['admin','kasir']))
      <a href="{{ route('support-tickets.index') }}" class="nav-item {{ request()->routeIs('support-tickets.*') ? 'active' : '' }}">
        <i class="fas fa-headset"></i> Aduan Pelanggan
        @php $aduanCount = \App\Models\SupportTicket::where('status', 'open')->count(); @endphp
        @if($aduanCount) <span class="nav-badge red" style="margin-left: 6px; padding: 2px 6px; border-radius: 4px; background: rgba(239,68,68,0.2); color: #fca5a5; font-size: 10px; font-weight: bold;">{{ $aduanCount }}</span> @endif
      </a>
      @endif

      @if(auth()->user()->hasRole(['admin','teknisi']))
      <!-- Jaringan -->
      <div class="dropdown-wrap" tabindex="0">
        <div class="nav-item {{ request()->routeIs('radius.*', 'olt.*', 'nas.*') ? 'active' : '' }}">
          <i class="fas fa-network-wired"></i> Jaringan <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
        </div>
        <div class="dropdown-menu">
          <a href="{{ route('radius.index') }}" class="dropdown-item {{ request()->routeIs('radius.*') ? 'active' : '' }}">
            <i class="fas fa-circle-dot"></i> RADIUS
            @php $onlineCount = \App\Models\RadiusSession::count(); @endphp
            @if($onlineCount) <span class="nav-badge green">{{ $onlineCount }}</span> @endif
          </a>

          <a href="{{ route('nas.index') }}" class="dropdown-item {{ request()->routeIs('nas.*') ? 'active' : '' }}">
            <i class="fas fa-server"></i> NAS Router
          </a>
        </div>
      </div>
      @endif

      @if(auth()->user()->hasRole(['admin','kasir']))
      <!-- Keuangan -->
      <div class="dropdown-wrap" tabindex="0">
        <div class="nav-item {{ request()->routeIs('invoice.*', 'pembayaran.*', 'isolir.*') ? 'active' : '' }}">
          <i class="fas fa-wallet"></i> Keuangan <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
        </div>
        <div class="dropdown-menu">
          <a href="{{ route('invoice.index') }}" class="dropdown-item {{ request()->routeIs('invoice.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Invoice
            @php $unpaid = \App\Models\Invoice::where('status','unpaid')->count(); @endphp
            @if($unpaid) <span class="nav-badge red">{{ $unpaid }}</span> @endif
          </a>
          <a href="{{ route('pembayaran.index') }}" class="dropdown-item {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-transfer"></i> Pembayaran
          </a>
          <a href="{{ route('isolir.log') }}" class="dropdown-item {{ request()->routeIs('isolir.*') ? 'active' : '' }}">
            <i class="fas fa-lock"></i> Log Isolir
          </a>
        </div>
      </div>
      @endif

      <!-- Sistem -->
      <div class="dropdown-wrap" tabindex="0">
        <div class="nav-item {{ request()->routeIs('notifikasi.*', 'pengguna.*', 'paket.*', 'pengaturan.*', 'activity_log.*', 'sistem.*', 'bantuan.*') ? 'active' : '' }}">
          <i class="fas fa-gear"></i> Sistem <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
        </div>
        <div class="dropdown-menu">
          <a href="{{ route('notifikasi.index') }}" class="dropdown-item {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> Notifikasi
          </a>
          <a href="{{ route('bantuan.index') }}" class="dropdown-item {{ request()->routeIs('bantuan.*') ? 'active' : '' }}">
            <i class="fas fa-circle-question"></i> Bantuan
          </a>
          
          @if(auth()->user()->isAdmin())
          <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 6px 0;"></div>
          <a href="{{ route('pengguna.index') }}" class="dropdown-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
            <i class="fas fa-users-gear"></i> Pengguna
          </a>
          <a href="{{ route('paket.index') }}" class="dropdown-item {{ request()->routeIs('paket.*') ? 'active' : '' }}">
            <i class="fas fa-boxes-stacked"></i> Paket Layanan
          </a>
          <a href="{{ route('pengaturan.index') }}" class="dropdown-item {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
            <i class="fas fa-sliders"></i> Pengaturan
          </a>
          <a href="{{ route('activity_log.index') }}" class="dropdown-item {{ request()->routeIs('activity_log.*') ? 'active' : '' }}">
            <i class="fas fa-timeline"></i> Activity Log
          </a>
          <a href="{{ route('sistem.db_info') }}" class="dropdown-item {{ request()->routeIs('sistem.*') ? 'active' : '' }}">
            <i class="fas fa-database"></i> Info Database
          </a>
          @endif
        </div>
      </div>

      <!-- Tombol Pengaturan Suara (hidden on mobile) -->
      <div class="dropdown-wrap desktop-only" tabindex="0" style="margin-left: auto;" id="notif-sound-dropdown">
        <div class="nav-item" style="color: #60a5fa; border: 1px solid #3b82f6; border-radius: 6px; padding: 4px 10px;">
          <i class="fas fa-volume-up"></i> Suara <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
        </div>
        <div class="dropdown-menu" style="right: 0; left: auto; min-width: 220px;">
          <a href="javascript:void(0)" onclick="window.setNotificationSound('mute')" class="dropdown-item sound-option" data-sound="mute">
            <i class="fas fa-volume-xmark" style="width: 20px; color: #ef4444;"></i> Mute (Hening) <i class="fas fa-check check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 6px 0;"></div>
          <a href="javascript:void(0)" onclick="window.setNotificationSound('glass')" class="dropdown-item sound-option" data-sound="glass">
            <i class="fas fa-bell" style="width: 20px;"></i> Glass (iOS Modern) <i class="fas fa-check check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setNotificationSound('tritone')" class="dropdown-item sound-option" data-sound="tritone">
            <i class="fas fa-music" style="width: 20px;"></i> Tri-tone (iOS Klasik) <i class="fas fa-check check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setNotificationSound('pop')" class="dropdown-item sound-option" data-sound="pop">
            <i class="fas fa-comment-dots" style="width: 20px;"></i> Soft Pop (Minimalis) <i class="fas fa-check check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setNotificationSound('chime')" class="dropdown-item sound-option" data-sound="chime">
            <i class="fas fa-wand-magic-sparkles" style="width: 20px;"></i> Magic Chime <i class="fas fa-check check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setNotificationSound('pluck')" class="dropdown-item sound-option" data-sound="pluck">
            <i class="fas fa-guitar" style="width: 20px;"></i> String Pluck <i class="fas fa-check check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
        </div>
      </div>

      <!-- Tombol Pengaturan Suara Chat (hidden on mobile) -->
      <div class="dropdown-wrap desktop-only" tabindex="0" style="margin-left: 10px;" id="chat-sound-dropdown">
        <div class="nav-item" style="color: #4ade80; border: 1px solid #22c55e; border-radius: 6px; padding: 4px 10px;">
          <i class="fas fa-comment-dots"></i> Suara Chat <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
        </div>
        <div class="dropdown-menu" style="right: 0; left: auto; min-width: 220px;">
          <a href="javascript:void(0)" onclick="window.setChatSound('mute')" class="dropdown-item chat-sound-option" data-sound="mute">
            <i class="fas fa-volume-xmark" style="width: 20px; color: #ef4444;"></i> Mute (Hening) <i class="fas fa-check chat-check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 6px 0;"></div>
          <a href="javascript:void(0)" onclick="window.setChatSound('glass')" class="dropdown-item chat-sound-option" data-sound="glass">
            <i class="fas fa-bell" style="width: 20px;"></i> Glass (iOS Modern) <i class="fas fa-check chat-check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setChatSound('tritone')" class="dropdown-item chat-sound-option" data-sound="tritone">
            <i class="fas fa-music" style="width: 20px;"></i> Tri-tone (iOS Klasik) <i class="fas fa-check chat-check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setChatSound('pop')" class="dropdown-item chat-sound-option" data-sound="pop">
            <i class="fas fa-comment-dots" style="width: 20px;"></i> Soft Pop (Minimalis) <i class="fas fa-check chat-check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setChatSound('chime')" class="dropdown-item chat-sound-option" data-sound="chime">
            <i class="fas fa-wand-magic-sparkles" style="width: 20px;"></i> Magic Chime <i class="fas fa-check chat-check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
          <a href="javascript:void(0)" onclick="window.setChatSound('pluck')" class="dropdown-item chat-sound-option" data-sound="pluck">
            <i class="fas fa-guitar" style="width: 20px;"></i> String Pluck <i class="fas fa-check chat-check-icon" style="display:none; float:right; margin-top: 4px; color: var(--primary);"></i>
          </a>
        </div>
      </div>
    </nav>
  </header>

  {{-- ── MOBILE NAV PANEL (hidden on desktop) ── --}}
  <div class="mobile-nav-overlay" id="mobile-nav-overlay" onclick="closeMobileNav()"></div>
  <div class="mobile-nav-panel" id="mobile-nav-panel">
    <div class="mob-nav-header">
      <span><i class="fas fa-bars" style="margin-right:8px; color:#3b82f6;"></i>Menu Navigasi</span>
      <button onclick="closeMobileNav()">&times;</button>
    </div>

    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-gauge-high" style="width:20px"></i> Dashboard</a>

    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'kasir')
    <a href="{{ route('pelanggan.index') }}" class="{{ request()->routeIs('pelanggan.*') ? 'active' : '' }}"><i class="fas fa-users" style="width:20px"></i> Pelanggan</a>
    <a href="{{ route('permohonan.index') }}" class="{{ request()->routeIs('permohonan.*') ? 'active' : '' }}"><i class="fas fa-user-plus" style="width:20px"></i> Permohonan Baru</a>
    @endif

    <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list" style="width:20px"></i> Job Order</a>

    @if(auth()->user()->hasRole(['admin','kasir']))
    <a href="{{ route('support-tickets.index') }}" class="{{ request()->routeIs('support-tickets.*') ? 'active' : '' }}"><i class="fas fa-headset" style="width:20px"></i> Aduan Pelanggan</a>
    @endif

    @if(auth()->user()->hasRole(['admin','teknisi']))
    <div class="mob-section-title">Jaringan</div>
    <a href="{{ route('radius.index') }}" class="{{ request()->routeIs('radius.*') ? 'active' : '' }}"><i class="fas fa-circle-dot" style="width:20px"></i> RADIUS</a>

    <a href="{{ route('nas.index') }}" class="{{ request()->routeIs('nas.*') ? 'active' : '' }}"><i class="fas fa-server" style="width:20px"></i> NAS Router</a>
    @endif

    @if(auth()->user()->hasRole(['admin','kasir']))
    <div class="mob-section-title">Keuangan</div>
    <a href="{{ route('invoice.index') }}" class="{{ request()->routeIs('invoice.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar" style="width:20px"></i> Invoice</a>
    <a href="{{ route('pembayaran.index') }}" class="{{ request()->routeIs('pembayaran.*') ? 'active' : '' }}"><i class="fas fa-money-bill-transfer" style="width:20px"></i> Pembayaran</a>
    <a href="{{ route('isolir.log') }}" class="{{ request()->routeIs('isolir.*') ? 'active' : '' }}"><i class="fas fa-lock" style="width:20px"></i> Log Isolir</a>
    @endif

    <div class="mob-section-title">Sistem</div>
    <a href="{{ route('notifikasi.index') }}" class="{{ request()->routeIs('notifikasi.*') ? 'active' : '' }}"><i class="fas fa-bell" style="width:20px"></i> Notifikasi</a>
    <a href="{{ route('bantuan.index') }}" class="{{ request()->routeIs('bantuan.*') ? 'active' : '' }}"><i class="fas fa-circle-question" style="width:20px"></i> Bantuan</a>

    @if(auth()->user()->isAdmin())
    <a href="{{ route('pengguna.index') }}" class="{{ request()->routeIs('pengguna.*') ? 'active' : '' }}"><i class="fas fa-users-gear" style="width:20px"></i> Pengguna</a>
    <a href="{{ route('paket.index') }}" class="{{ request()->routeIs('paket.*') ? 'active' : '' }}"><i class="fas fa-boxes-stacked" style="width:20px"></i> Paket Layanan</a>
    <a href="{{ route('pengaturan.index') }}" class="{{ request()->routeIs('pengaturan.*') ? 'active' : '' }}"><i class="fas fa-sliders" style="width:20px"></i> Pengaturan</a>
    <a href="{{ route('activity_log.index') }}" class="{{ request()->routeIs('activity_log.*') ? 'active' : '' }}"><i class="fas fa-list-check" style="width:20px"></i> Log Aktivitas</a>
    @endif
  </div>

  <script>
    function toggleMobileNav() {
      const panel = document.getElementById('mobile-nav-panel');
      const overlay = document.getElementById('mobile-nav-overlay');
      panel.classList.toggle('open');
      overlay.style.display = panel.classList.contains('open') ? 'block' : 'none';
    }
    function closeMobileNav() {
      document.getElementById('mobile-nav-panel').classList.remove('open');
      document.getElementById('mobile-nav-overlay').style.display = 'none';
    }
  </script>

  <!-- ── MAIN CONTENT ── -->
  <div class="page-content-wrapper">
    <main class="page-content">
      
      {{-- Page Header inside content --}}
      <div class="page-header">
        <div class="page-header-title">
          <h1>@yield('title', 'Dashboard')</h1>
          <p>@yield('breadcrumb', 'BRS')</p>
        </div>
      </div>

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="alert alert-success" x-data x-init="setTimeout(()=>$el.remove(), 4000)">
          <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger" x-data x-init="setTimeout(()=>$el.remove(), 5000)">
          <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
        </div>
      @endif

      @yield('content')
    </main>
  </div> <!-- .app-layout -->

<script>
document.addEventListener('DOMContentLoaded', function() {
  const input = document.getElementById('searchInput');
  const results = document.getElementById('liveSearchResults');
  const spinner = document.getElementById('searchSpinner');
  let timeout = null;

  if(!input) return;

  // Close when clicking outside
  document.addEventListener('click', (e) => {
    if(!e.target.closest('.search-wrap-container')) {
      results.style.display = 'none';
    }
  });

  input.addEventListener('input', function() {
    clearTimeout(timeout);
    const q = this.value.trim();
    
    if (q.length < 2) {
      results.style.display = 'none';
      return;
    }

    spinner.style.display = 'block';
    
    timeout = setTimeout(() => {
      fetch(`/search/live?q=${encodeURIComponent(q)}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(res => res.json())
      .then(data => {
        spinner.style.display = 'none';
        renderResults(data);
      })
      .catch(err => {
        spinner.style.display = 'none';
        console.error(err);
      });
    }, 100); // 100ms debounce for lightning speed
  });

  function renderResults(data) {
    let html = '';
    
    if (data.pelanggan.length > 0) {
      html += `<div class="live-search-group">
                 <div class="live-search-title">Pelanggan</div>`;
      data.pelanggan.forEach(p => {
        html += `<a href="/pelanggan/${p.id}" class="live-search-item">
                   <div>
                     <div class="ls-main">${p.nama}</div>
                     <div class="ls-sub">${p.username_pppoe} &bull; ${p.ip_address || '-'}</div>
                   </div>
                 </a>`;
      });
      html += `</div>`;
    }

    if (data.invoices.length > 0) {
      html += `<div class="live-search-group">
                 <div class="live-search-title">Invoice</div>`;
      data.invoices.forEach(i => {
        let pName = i.pelanggan ? i.pelanggan.nama : 'Terhapus';
        html += `<a href="/invoice/${i.id}" class="live-search-item">
                   <div>
                     <div class="ls-main">${i.no_invoice}</div>
                     <div class="ls-sub">${pName}</div>
                   </div>
                 </a>`;
      });
      html += `</div>`;
    }

    if (html === '') {
      html = `<div style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">Tidak ada hasil ditemukan</div>`;
    }

    results.innerHTML = html;
    results.style.display = 'flex';
  }
});
</script>

@stack('scripts')
<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('globalTheme', () => ({
    isDark: true,
    init() {
      if (localStorage.getItem('theme') === 'light') {
        this.isDark = false;
      }
    },
    toggleTheme() {
      this.isDark = !this.isDark;
      localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
    }
  }));
});

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => console.log('PWA ServiceWorker registered', registration.scope))
      .catch(err => console.log('PWA ServiceWorker failed', err));
  });
}

// ═══ AUTO LOGOUT (IDLE TIMER) ═══
(function() {
  let idleTime = 0;
  const idleTimeout = 30 * 60; // 30 menit (dalam detik)
  const warningTime = 29 * 60; // Tampilkan peringatan di menit ke-29

  function resetIdleTimer() {
    idleTime = 0;
    const warningEl = document.getElementById('idle-warning');
    if (warningEl) warningEl.style.display = 'none';
  }

  // Deteksi aktivitas user
  window.onload = resetIdleTimer;
  window.onmousemove = resetIdleTimer;
  window.onmousedown = resetIdleTimer;
  window.ontouchstart = resetIdleTimer;
  window.onclick = resetIdleTimer;
  window.onkeydown = resetIdleTimer;
  window.addEventListener('scroll', resetIdleTimer, true);

  setInterval(function() {
    idleTime++;
    if (idleTime >= idleTimeout) {
      // Set flag for login page
      localStorage.setItem('auto_logout_msg', 'Sesi Anda telah habis secara otomatis karena tidak ada aktivitas selama 30 menit. Silakan login kembali.');
      
      // Auto logout form submit
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '{{ route("logout") }}';
      
      const csrf = document.createElement('input');
      csrf.type = 'hidden';
      csrf.name = '_token';
      csrf.value = document.querySelector('meta[name="csrf-token"]').content;
      
      form.appendChild(csrf);
      document.body.appendChild(form);
      form.submit();
    } else if (idleTime >= warningTime) {
      let warningEl = document.getElementById('idle-warning');
      if (!warningEl) {
        warningEl = document.createElement('div');
        warningEl.id = 'idle-warning';
        warningEl.style.cssText = 'position:fixed;top:0;left:0;width:100%;background:var(--red);color:white;text-align:center;padding:12px;z-index:99999;font-weight:bold;font-family:sans-serif;box-shadow:0 4px 12px rgba(0,0,0,0.3);animation: slideDown 0.3s ease-out;';
        document.body.appendChild(warningEl);
      }
      warningEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Sesi Anda akan berakhir dalam ${idleTimeout - idleTime} detik karena tidak ada aktivitas. Gerakkan mouse atau sentuh layar untuk membatalkan.`;
      warningEl.style.display = 'block';
    }
  }, 1000);
})();
// ═══ LIVE UPDATES (AJAX POLLING) ═══
(function() {
  let lastNotifId = null;
  let lastTicketTime = null;
  let lastPermohonanTime = null;
  const isTicketsPage = window.location.pathname.includes('/tickets');
  const isPermohonanPage = window.location.pathname.includes('/permohonan');

  // Insert keyframes for toast
  const style = document.createElement('style');
  style.innerHTML = `@keyframes slideUpToast { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }`;
  document.head.appendChild(style);

  // ═══ WEB AUDIO API SYNTHESIZER (MODERN POP) ═══
  const AudioContext = window.AudioContext || window.webkitAudioContext;
  let audioCtx = new AudioContext();

  const resumeAudioCtx = function() {
    if (audioCtx && audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
  };

  ['click', 'touchstart', 'keydown', 'mousemove', 'scroll'].forEach(evt => {
    document.body.addEventListener(evt, resumeAudioCtx, { once: true, passive: true });
  });

  window.setNotificationSound = function(type) {
    localStorage.setItem('brs_notif_sound', type);
    
    // Update indikator UI (tanda centang)
    document.querySelectorAll('.sound-option .check-icon').forEach(el => el.style.display = 'none');
    const activeItem = document.querySelector(`.sound-option[data-sound="${type}"] .check-icon`);
    if (activeItem) activeItem.style.display = 'block';
    
    window.playNotificationSound(type);
    
    // Beri tahu user bahwa berhasil diganti
    if (typeof showToast === 'function') {
      showToast("Nada dering berhasil diubah!", "success", "", "mute");
    }
  };

  window.setChatSound = function(type) {
    localStorage.setItem('brs_chat_sound', type);
    
    // Update indikator UI (tanda centang)
    document.querySelectorAll('.chat-sound-option .chat-check-icon').forEach(el => el.style.display = 'none');
    const activeItem = document.querySelector(`.chat-sound-option[data-sound="${type}"] .chat-check-icon`);
    if (activeItem) activeItem.style.display = 'block';
    
    window.playNotificationSound(type);
    
    // Beri tahu user bahwa berhasil diganti
    if (typeof showToast === 'function') {
      showToast("Nada dering chat berhasil diubah!", "success", "", "mute");
    }
  };

  // Set initial checkmark saat halaman dimuat
  document.addEventListener('DOMContentLoaded', () => {
    const currentSound = localStorage.getItem('brs_notif_sound') || 'glass';
    const activeItem = document.querySelector(`.sound-option[data-sound="${currentSound}"] .check-icon`);
    if (activeItem) activeItem.style.display = 'block';
    
    const currentChatSound = localStorage.getItem('brs_chat_sound') || 'pop';
    const activeChatItem = document.querySelector(`.chat-sound-option[data-sound="${currentChatSound}"] .chat-check-icon`);
    if (activeChatItem) activeChatItem.style.display = 'block';
  });

  window.playNotificationSound = function(forceType = null) {
    try {
      const soundType = forceType || localStorage.getItem('brs_notif_sound') || 'glass';
      
      // Jika mode Mute terpilih, jangan bunyikan apa-apa
      if (soundType === 'mute') return;
      
      const requestTime = Date.now();
      
      const playNow = () => {
          const playNote = (freq, startTime, duration, type='sine') => {
              const osc = audioCtx.createOscillator();
              const gain = audioCtx.createGain();
              osc.type = type;
              osc.frequency.value = freq;
              gain.gain.setValueAtTime(0, audioCtx.currentTime + startTime);
              gain.gain.linearRampToValueAtTime(0.5, audioCtx.currentTime + startTime + 0.01);
              gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + startTime + duration);
              osc.connect(gain); gain.connect(audioCtx.destination);
              osc.start(audioCtx.currentTime + startTime); osc.stop(audioCtx.currentTime + startTime + duration);
          };

          if (soundType === 'tritone') {
              // Tri-tone klasik iPhone
              playNote(987.77,  0.00, 0.3); // B5
              playNote(1318.51, 0.15, 0.3); // E6
              playNote(1108.73, 0.30, 0.6); // C#6
              
          } else if (soundType === 'pop') {
              // Soft Pop singkat
              playNote(800, 0, 0.1);
              const osc = audioCtx.createOscillator();
              const gain = audioCtx.createGain();
              osc.type = 'sine';
              osc.frequency.setValueAtTime(800, audioCtx.currentTime);
              osc.frequency.exponentialRampToValueAtTime(300, audioCtx.currentTime + 0.1);
              gain.gain.setValueAtTime(0, audioCtx.currentTime);
              gain.gain.linearRampToValueAtTime(1, audioCtx.currentTime + 0.02);
              gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);
              osc.connect(gain); gain.connect(audioCtx.destination);
              osc.start(audioCtx.currentTime); osc.stop(audioCtx.currentTime + 0.2);
              
          } else if (soundType === 'chime') {
              // Magic Chime (4 nada menaik)
              playNote(523.25, 0.00, 0.3); // C5
              playNote(659.25, 0.10, 0.3); // E5
              playNote(783.99, 0.20, 0.3); // G5
              playNote(1046.50, 0.30, 0.6); // C6
              
          } else if (soundType === 'pluck') {
              // String Pluck pendek
              const osc = audioCtx.createOscillator();
              const gain = audioCtx.createGain();
              osc.type = 'triangle';
              osc.frequency.value = 600;
              osc.frequency.exponentialRampToValueAtTime(200, audioCtx.currentTime + 0.1);
              gain.gain.setValueAtTime(0, audioCtx.currentTime);
              gain.gain.linearRampToValueAtTime(1, audioCtx.currentTime + 0.01);
              gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.2);
              osc.connect(gain); gain.connect(audioCtx.destination);
              osc.start(audioCtx.currentTime); osc.stop(audioCtx.currentTime + 0.2);
              
          } else {
              // Glass / Bell (default)
              const playBell = (freq, startTime, duration) => {
                  const gain = audioCtx.createGain();
                  const osc1 = audioCtx.createOscillator();
                  const osc2 = audioCtx.createOscillator();
                  const osc3 = audioCtx.createOscillator();
                  osc1.type = 'sine'; osc2.type = 'sine'; osc3.type = 'sine';
                  osc1.frequency.value = freq;
                  osc2.frequency.value = freq * 2.01;
                  osc3.frequency.value = freq * 3.02;
                  const gain1 = audioCtx.createGain(); gain1.gain.value = 1.0;
                  const gain2 = audioCtx.createGain(); gain2.gain.value = 0.3;
                  const gain3 = audioCtx.createGain(); gain3.gain.value = 0.1;
                  osc1.connect(gain1); gain1.connect(gain);
                  osc2.connect(gain2); gain2.connect(gain);
                  osc3.connect(gain3); gain3.connect(gain);
                  gain.gain.setValueAtTime(0, audioCtx.currentTime + startTime);
                  gain.gain.linearRampToValueAtTime(0.6, audioCtx.currentTime + startTime + 0.01);
                  gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + startTime + duration);
                  gain.connect(audioCtx.destination);
                  osc1.start(audioCtx.currentTime + startTime); osc1.stop(audioCtx.currentTime + startTime + duration);
                  osc2.start(audioCtx.currentTime + startTime); osc2.stop(audioCtx.currentTime + startTime + duration);
                  osc3.start(audioCtx.currentTime + startTime); osc3.stop(audioCtx.currentTime + startTime + duration);
              };
              playBell(880.00,  0.0, 0.5); 
              playBell(1108.73, 0.1, 0.8);
          }
      };

      if (audioCtx.state === 'running') {
          playNow();
      } else {
          // Coba resume (Browser mungkin memblokir ini jika tidak ada klik dari user)
          audioCtx.resume().then(() => {
              // Jika berhasil resume dalam waktu kurang dari 2 detik, bunyikan!
              // Jika lebih dari 2 detik (misal user baru klik menu beberapa menit kemudian), batalkan agar tidak "meledak" telat.
              if (Date.now() - requestTime < 2000) {
                  playNow();
              } else {
                  console.log('Suara notifikasi dibatalkan karena jeda autoplay terlalu lama.');
              }
          }).catch(e => console.log('Gagal resume audio', e));
      }

    } catch (e) {
      console.log('Error memutar synthesizer', e);
    }
  }

  function showToast(message, type = 'info', actionHtml = '', forceSound = null, toastId = null) {
    playNotificationSound(forceSound);

    const container = document.getElementById('live-toast-container') || (function() {
      const c = document.createElement('div');
      c.id = 'live-toast-container';
      c.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;max-width:350px;';
      document.body.appendChild(c);
      return c;
    })();

    if (toastId) {
      const existing = document.getElementById(toastId);
      if (existing) existing.remove();
    }

    // Limit max 3 toasts to prevent screen clutter
    while (container.children.length >= 3) {
      container.removeChild(container.firstChild);
    }

    const toast = document.createElement('div');
    if (toastId) toast.id = toastId;
    const bg = type === 'warning' ? '#f59e0b' : (type === 'danger' ? '#ef4444' : '#3b82f6');
    toast.style.cssText = `background:${bg};color:white;padding:12px 16px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.3);animation:slideUpToast 0.3s ease-out;display:flex;align-items:center;gap:10px;font-family:sans-serif;font-size:13px;line-height:1.4;`;
    toast.innerHTML = `<span style="flex:1;">${message}</span> ${actionHtml}`;
    container.appendChild(toast);

    // Tombol close (silang) selalu ada
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = 'background:transparent;border:none;color:white;font-size:20px;cursor:pointer;opacity:0.7;margin-left:8px;padding:0 4px;';
    closeBtn.onclick = () => { toast.remove(); };
    toast.appendChild(closeBtn);

    // Semua toast hilang otomatis dalam 6 detik
    setTimeout(() => {
      if (toast.parentNode) {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s';
        setTimeout(() => {
          if (toast.parentNode) toast.remove();
        }, 400);
      }
    }, 6000);
  }

  function fetchUpdates() {
    fetch('{{ route("live-updates") }}', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(res => res.json())
    .then(data => {
      // Update badge bell
      const bellIconBtn = document.getElementById('bell-icon-btn') || document.querySelector('.icon-btn[href*="notifikasi"]:not([href*="type="])');
      let bellBadge = bellIconBtn ? bellIconBtn.querySelector('.notif-badge') : null;
      
      if (data.unread_count > 0) {
        if (bellBadge) {
          bellBadge.textContent = data.unread_count > 99 ? '99' : data.unread_count;
        } else if (bellIconBtn) {
          bellIconBtn.innerHTML += `<span class="notif-badge">${data.unread_count > 99 ? '99' : data.unread_count}</span>`;
        }
      } else if (bellBadge) {
        bellBadge.remove();
      }

      // Update badge chat
      const chatIconBtn = document.getElementById('chat-icon-btn');
      let chatBadge = chatIconBtn ? chatIconBtn.querySelector('.notif-badge') : null;
      
      if (data.chat_unread_count > 0) {
        if (chatBadge) {
          chatBadge.textContent = data.chat_unread_count > 99 ? '99' : data.chat_unread_count;
        } else if (chatIconBtn) {
          chatIconBtn.innerHTML += `<span class="notif-badge" style="background-color: #3b82f6;">${data.chat_unread_count > 99 ? '99' : data.chat_unread_count}</span>`;
        }
      } else if (chatBadge) {
        chatBadge.remove();
      }

      // Check new bell notification
      if (typeof window.lastNotifTime === 'undefined') {
        window.lastNotifTime = data.latest_notif_time;
      } else if (data.latest_notif_time > window.lastNotifTime) {
        window.lastNotifTime = data.latest_notif_time;
        let actionBtn = data.latest_notif_url ? `<button onclick="window.location.href='${data.latest_notif_url}'" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:5px 10px;border-radius:4px;cursor:pointer;font-weight:bold;margin-left:auto;">Buka</button>` : '';
        showToast(`🔔 Notifikasi Baru: ${data.latest_notif_title}`, data.latest_notif_type, actionBtn, null, 'toast-bell');
      }
      
      // Check new chat notification
      if (typeof window.lastChatTime === 'undefined') {
        window.lastChatTime = data.latest_chat_time;
      } else if (data.latest_chat_time > window.lastChatTime) {
        window.lastChatTime = data.latest_chat_time;
        let actionBtn = data.latest_chat_url ? `<button onclick="window.location.href='${data.latest_chat_url}'" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:5px 10px;border-radius:4px;cursor:pointer;font-weight:bold;margin-left:auto;">Balas</button>` : '';
        const chatSound = localStorage.getItem('brs_chat_sound') || 'pop';
        showToast(`💬 ${data.latest_chat_title}`, 'info', actionBtn, chatSound, 'toast-chat');
      }

      // ── Update Permohonan Badge & Notify ──
      const permohonanLinks = document.querySelectorAll('.top-nav a[href*="permohonan"], .sidebar-menu a[href*="permohonan"]');
      permohonanLinks.forEach(link => {
        let badge = link.querySelector('.nav-badge');
        if (data.pending_permohonan_count > 0) {
          if (badge) {
            badge.textContent = data.pending_permohonan_count;
          } else {
            link.innerHTML += `<span class="nav-badge red" style="margin-left: 6px; padding: 2px 6px; border-radius: 4px; background: rgba(239,68,68,0.2); color: #fca5a5; font-size: 10px; font-weight: bold;">${data.pending_permohonan_count}</span>`;
          }
        } else if (badge) {
          badge.remove();
        }
      });

      if (lastPermohonanTime === null) {
        lastPermohonanTime = data.latest_permohonan_time;
      } else if (data.latest_permohonan_time > 0 && data.latest_permohonan_time > lastPermohonanTime) {
        lastPermohonanTime = data.latest_permohonan_time;
        showToast(`👤 Ada Permohonan Pemasangan Baru!`, 'info', `<button onclick="window.location.href='/permohonan'" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:5px 10px;border-radius:4px;cursor:pointer;font-weight:bold;">Lihat</button>`);
        if (isPermohonanPage) setTimeout(() => window.location.reload(), 2500);
      }

      // ── Update Job Order Badge ──
      const ticketLinks = document.querySelectorAll('.top-nav a[href*="tickets"], .sidebar-menu a[href*="tickets"]');
      ticketLinks.forEach(link => {
        let badge = link.querySelector('.nav-badge');
        if (data.pending_ticket_count > 0) {
          if (badge) {
            badge.textContent = data.pending_ticket_count;
          } else {
            link.innerHTML += `<span class="nav-badge orange ticket-badge" style="margin-left: 6px; padding: 2px 6px; border-radius: 4px; background: rgba(245,158,11,0.2); color: #fcd34d; font-size: 10px; font-weight: bold;">${data.pending_ticket_count}</span>`;
          }
        } else if (badge) {
          badge.remove();
        }
      });

      // ── Check new Job Order (Ticket) update globally ──
      if (lastTicketTime === null) {
        lastTicketTime = data.latest_ticket_time;
      } else if (data.latest_ticket_time > 0 && data.latest_ticket_time > lastTicketTime) {
        lastTicketTime = data.latest_ticket_time;
        showToast(`🛠️ Ada Job Order Baru / Diperbarui!`, 'warning', `<button onclick="window.location.href='/tickets'" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:5px 10px;border-radius:4px;cursor:pointer;font-weight:bold;">Lihat</button>`);
        if (isTicketsPage) setTimeout(() => window.location.reload(), 2500);
      }
      
      // ── Update Aduan Pelanggan Badge ──
      let lastAduanTime = window.lastAduanTime || null;
      const aduanLinks = document.querySelectorAll('.top-nav a[href*="support-tickets"], .sidebar-menu a[href*="support-tickets"]');
      aduanLinks.forEach(link => {
        let badge = link.querySelector('.nav-badge');
        if (data.pending_aduan_count > 0) {
          if (badge) {
            badge.textContent = data.pending_aduan_count;
          } else {
            link.innerHTML += `<span class="nav-badge red" style="margin-left: 6px; padding: 2px 6px; border-radius: 4px; background: rgba(239,68,68,0.2); color: #fca5a5; font-size: 10px; font-weight: bold;">${data.pending_aduan_count}</span>`;
          }
        } else if (badge) {
          badge.remove();
        }
      });
      
      if (window.lastAduanTime === undefined) {
        window.lastAduanTime = data.latest_aduan_time;
      } else if (data.latest_aduan_time > 0 && data.latest_aduan_time > window.lastAduanTime) {
        window.lastAduanTime = data.latest_aduan_time;
        showToast(`🎧 Ada Aduan Pelanggan Baru / Diperbarui!`, 'danger', `<button onclick="window.location.href='/support-tickets'" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:5px 10px;border-radius:4px;cursor:pointer;font-weight:bold;">Lihat</button>`);
        if (window.location.pathname.includes('/support-tickets')) setTimeout(() => window.location.reload(), 2500);
      }
    })
    .catch(err => console.error('Live Updates Error:', err));
  }

  // Fetch every 5 seconds
  setInterval(fetchUpdates, 5000);
  // Initial fetch delayed slightly to let page load completely
  setTimeout(fetchUpdates, 1000);
})();
</script>
</body>
</html>
