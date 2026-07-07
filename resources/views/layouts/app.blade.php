<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') — NetCORE</title>
{{-- ═══ ASSETS LOKAL — tidak butuh internet ═══ --}}
<link rel="stylesheet" href="/fonts/inter/fonts.css">
<link rel="stylesheet" href="/fonts/jetbrains/fonts.css">
<link rel="stylesheet" href="/fonts/fa/all.min.css">
<script defer src="/js/alpine.min.js"></script>
<script src="/js/chart.min.js"></script>
<style>
/* ═══════════════════════════════════════════════════════
   NETCORE — DESIGN SYSTEM (Professional Slate Theme)
   ═══════════════════════════════════════════════════════ */
:root {
  /* Background layers */
  --bg-base:     #0f172a;   /* slate-900 */
  --bg-surface:  #1e293b;   /* slate-800 */
  --bg-elevated: #334155;   /* slate-700 */
  --bg-hover:    #3f4f65;
  --bg-input:    #162032;

  /* Borders */
  --border:       #334155;  /* slate-700 */
  --border-light: #475569;  /* slate-600 */
  --border-focus: #6366f1;  /* indigo-500 */

  /* Accent — Indigo */
  --indigo:      #6366f1;
  --indigo-dark: #4f46e5;
  --indigo-glow: rgba(99,102,241,0.15);
  --indigo-dim:  rgba(99,102,241,0.08);

  /* Semantic colors */
  --green:   #10b981;  /* emerald-500 */
  --green-d: rgba(16,185,129,0.12);
  --amber:   #f59e0b;  /* amber-500 */
  --amber-d: rgba(245,158,11,0.12);
  --red:     #ef4444;  /* red-500 */
  --red-d:   rgba(239,68,68,0.12);
  --sky:     #0ea5e9;  /* sky-500 */
  --sky-d:   rgba(14,165,233,0.12);
  --purple:  #a855f7;
  --purple-d:rgba(168,85,247,0.12);

  /* Text */
  --text-1:  #f1f5f9;   /* slate-100 */
  --text-2:  #94a3b8;   /* slate-400 */
  --text-3:  #64748b;   /* slate-500 */
  --text-4:  #475569;   /* slate-600 */

  /* Layout */
  --sidebar-w: 256px;
  --header-h:  58px;
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

/* ── Scrollbar ── */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: var(--bg-base); }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--border-light); }

/* ═══════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════ */
.app-layout { display: flex; height: 100vh; overflow: hidden; }

/* ── Sidebar ── */
.sidebar {
  width: var(--sidebar-w);
  height: 100vh;
  background: var(--bg-surface);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  overflow: hidden;
}

.sidebar-logo {
  padding: 18px 20px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--indigo-dark), var(--indigo));
  border-radius: var(--radius);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; color: white; flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(99,102,241,0.4);
}

.logo-text .app-name {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-1);
  letter-spacing: 0.3px;
}

.logo-text .app-sub {
  font-size: 10px;
  color: var(--text-3);
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

/* Sidebar user card */
.sidebar-user {
  margin: 12px 12px 4px;
  padding: 10px 12px;
  background: var(--bg-elevated);
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  width: 34px; height: 34px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
  flex-shrink: 0;
}

.user-avatar.admin    { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.user-avatar.kasir    { background: linear-gradient(135deg, #10b981, #059669); }
.user-avatar.teknisi  { background: linear-gradient(135deg, #f59e0b, #d97706); }

.user-name   { font-size: 13px; font-weight: 600; color: var(--text-1); }
.user-role   {
  font-size: 10px; padding: 1px 6px; border-radius: 4px;
  font-weight: 500; display: inline-block; margin-top: 2px;
  text-transform: uppercase; letter-spacing: 0.5px;
}
.user-role.admin    { background: rgba(99,102,241,0.2); color: #a5b4fc; }
.user-role.kasir    { background: rgba(16,185,129,0.2); color: #6ee7b7; }
.user-role.teknisi  { background: rgba(245,158,11,0.2); color: #fcd34d; }

/* Sidebar nav */
.sidebar-nav { flex: 1; overflow-y: auto; padding: 8px 10px; }

.nav-section-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 1px;
  color: var(--text-4);
  text-transform: uppercase;
  padding: 12px 8px 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 10px;
  border-radius: var(--radius);
  cursor: pointer;
  color: var(--text-2);
  font-size: 13.5px;
  font-weight: 500;
  text-decoration: none;
  transition: all var(--transition);
  margin-bottom: 1px;
  position: relative;
}

.nav-item:hover {
  background: var(--bg-elevated);
  color: var(--text-1);
}

.nav-item.active {
  background: var(--indigo-glow);
  color: #a5b4fc;
  border: 1px solid rgba(99,102,241,0.25);
}

.nav-item .nav-icon {
  width: 16px;
  text-align: center;
  font-size: 13px;
  flex-shrink: 0;
}

.nav-badge {
  margin-left: auto;
  font-size: 10px;
  font-family: 'JetBrains Mono', monospace;
  padding: 1px 6px;
  border-radius: 100px;
  font-weight: 600;
}

.nav-badge.red    { background: var(--red-d);   color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
.nav-badge.amber  { background: var(--amber-d); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
.nav-badge.green  { background: var(--green-d); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }

/* Sidebar footer */
.sidebar-footer {
  padding: 10px 14px;
  border-top: 1px solid var(--border);
}

.status-indicators {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.status-dot {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 10px;
  color: var(--text-3);
  font-family: 'JetBrains Mono', monospace;
}

.dot {
  width: 6px; height: 6px;
  border-radius: 50%;
}

.dot.online  { background: var(--green); box-shadow: 0 0 5px var(--green); animation: blink 2s infinite; }
.dot.offline { background: var(--red);   box-shadow: 0 0 5px var(--red); }
.dot.warn    { background: var(--amber); box-shadow: 0 0 5px var(--amber); animation: blink 2s infinite 0.5s; }

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.35; }
}

/* ── Main content ── */
.main-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  min-width: 0;
}

/* ── Topbar ── */
.topbar {
  height: var(--header-h);
  background: var(--bg-surface);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  flex-shrink: 0;
  gap: 12px;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.page-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-1);
  white-space: nowrap;
}

.breadcrumb-sep { color: var(--text-4); font-size: 14px; }
.breadcrumb-sub { color: var(--text-3); font-size: 13px; white-space: nowrap; }

.topbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-input);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 0 10px;
  height: 34px;
  transition: border-color var(--transition);
}

.search-wrap:focus-within { border-color: var(--border-focus); }

.search-wrap input {
  background: none;
  border: none;
  outline: none;
  color: var(--text-1);
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  width: 180px;
}

.search-wrap input::placeholder { color: var(--text-3); }
.search-wrap i { color: var(--text-3); font-size: 11px; }

.icon-btn {
  width: 34px; height: 34px;
  border-radius: var(--radius);
  background: var(--bg-input);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  color: var(--text-2);
  transition: all var(--transition);
  position: relative;
  text-decoration: none;
}

.icon-btn:hover { border-color: var(--border-light); color: var(--text-1); }

.notif-badge {
  position: absolute;
  top: -4px; right: -4px;
  background: var(--red);
  color: white;
  width: 16px; height: 16px;
  border-radius: 50%;
  font-size: 9px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
  border: 2px solid var(--bg-surface);
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 12px;
  height: 34px;
  border-radius: var(--radius);
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.25);
  color: #fca5a5;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition);
  text-decoration: none;
}

.logout-btn:hover { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); }

/* ── Page content ── */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
  background: var(--bg-base);
}

/* ═══════════════════════════════════════════
   COMPONENTS
   ═══════════════════════════════════════════ */

/* ── Page header ── */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 12px;
}

.page-header-title h1 {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-1);
}

.page-header-title p {
  font-size: 13px;
  color: var(--text-3);
  margin-top: 2px;
}

.page-header-actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ── Stat cards ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px;
  margin-bottom: 20px;
}

.stat-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 18px;
  position: relative;
  overflow: hidden;
  transition: transform var(--transition), border-color var(--transition);
}

.stat-card:hover { transform: translateY(-1px); border-color: var(--border-light); }

.stat-card::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 2px;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.stat-card.indigo::after { background: linear-gradient(90deg, var(--indigo), transparent); }
.stat-card.green::after  { background: linear-gradient(90deg, var(--green), transparent); }
.stat-card.amber::after  { background: linear-gradient(90deg, var(--amber), transparent); }
.stat-card.red::after    { background: linear-gradient(90deg, var(--red), transparent); }
.stat-card.sky::after    { background: linear-gradient(90deg, var(--sky), transparent); }

.stat-icon {
  width: 38px; height: 38px;
  border-radius: var(--radius);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px;
  margin-bottom: 12px;
}

.stat-icon.indigo { background: var(--indigo-dim); color: #a5b4fc; }
.stat-icon.green  { background: var(--green-d);    color: #6ee7b7; }
.stat-icon.amber  { background: var(--amber-d);    color: #fcd34d; }
.stat-icon.red    { background: var(--red-d);      color: #fca5a5; }
.stat-icon.sky    { background: var(--sky-d);      color: #7dd3fc; }

.stat-value {
  font-size: 26px;
  font-weight: 700;
  color: var(--text-1);
  line-height: 1;
  font-family: 'JetBrains Mono', monospace;
}

.stat-label {
  font-size: 12px;
  color: var(--text-3);
  margin-top: 4px;
}

.stat-sub {
  font-size: 11px;
  margin-top: 8px;
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
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
}

.card-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-1);
}

.card-subtitle {
  font-size: 11px;
  color: var(--text-3);
  margin-top: 1px;
}

.card-body { padding: 18px; }
.card-body-flush { padding: 0; }

/* ── Buttons ── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: var(--radius);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition);
  border: 1px solid transparent;
  text-decoration: none;
  white-space: nowrap;
}

.btn-sm  { padding: 5px 10px; font-size: 12px; }
.btn-xs  { padding: 3px 8px;  font-size: 11px; }

.btn-primary {
  background: var(--indigo);
  color: white;
  border-color: var(--indigo);
}
.btn-primary:hover { background: var(--indigo-dark); border-color: var(--indigo-dark); box-shadow: 0 0 12px rgba(99,102,241,0.3); }

.btn-ghost {
  background: transparent;
  border-color: var(--border);
  color: var(--text-2);
}
.btn-ghost:hover { background: var(--bg-elevated); border-color: var(--border-light); color: var(--text-1); }

.btn-success {
  background: var(--green-d);
  border-color: rgba(16,185,129,0.35);
  color: #6ee7b7;
}
.btn-success:hover { background: rgba(16,185,129,0.2); }

.btn-danger {
  background: var(--red-d);
  border-color: rgba(239,68,68,0.35);
  color: #fca5a5;
}
.btn-danger:hover { background: rgba(239,68,68,0.2); }

.btn-warning {
  background: var(--amber-d);
  border-color: rgba(245,158,11,0.35);
  color: #fcd34d;
}
.btn-warning:hover { background: rgba(245,158,11,0.2); }

.btn-sky {
  background: var(--sky-d);
  border-color: rgba(14,165,233,0.35);
  color: #7dd3fc;
}
.btn-sky:hover { background: rgba(14,165,233,0.2); }

/* ── Forms ── */
.form-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-3);
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-bottom: 5px;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  background: var(--bg-input);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  color: var(--text-1);
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  transition: border-color var(--transition), box-shadow var(--transition);
  outline: none;
}

.form-control:focus {
  border-color: var(--indigo);
  box-shadow: var(--shadow-ind);
}

.form-control::placeholder { color: var(--text-4); }

select.form-control { cursor: pointer; }
select.form-control option { background: var(--bg-surface); }

.form-control-mono {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
}

.form-group { margin-bottom: 14px; }

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-error { font-size: 11px; color: #fca5a5; margin-top: 4px; }

.form-hint { font-size: 11px; color: var(--text-4); margin-top: 4px; }

/* ── Table ── */
.table-wrap {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead th {
  padding: 10px 14px;
  text-align: left;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: var(--text-4);
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
  background: var(--bg-base);
}

.table tbody tr {
  border-bottom: 1px solid rgba(51,65,85,0.4);
  transition: background var(--transition);
}

.table tbody tr:hover { background: rgba(255,255,255,0.02); }

.table tbody tr:last-child { border-bottom: none; }

.table td {
  padding: 12px 14px;
  font-size: 13px;
  vertical-align: middle;
}

/* ── Badges ── */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

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
.mono {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: #7dd3fc;
}

.mono-mute {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text-3);
}

/* ── Table toolbar ── */
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  gap: 10px;
  flex-wrap: wrap;
}

.toolbar-search {
  display: flex;
  align-items: center;
  gap: 7px;
  background: var(--bg-input);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 0 10px;
  height: 32px;
  transition: border-color var(--transition);
}

.toolbar-search:focus-within { border-color: var(--indigo); }
.toolbar-search input { background: none; border: none; outline: none; color: var(--text-1); font-size: 12px; font-family: 'JetBrains Mono', monospace; width: 160px; }
.toolbar-search input::placeholder { color: var(--text-4); }
.toolbar-search i { color: var(--text-4); font-size: 11px; }

.toolbar-right { display: flex; gap: 8px; align-items: center; }

/* ── Modal ── */
.modal-overlay {
  position: fixed; inset: 0; z-index: 600;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(3px);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}

.modal {
  background: var(--bg-surface);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  display: flex; flex-direction: column;
  box-shadow: var(--shadow-lg);
}

.modal-lg { max-width: 680px; }

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}

.modal-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-1);
}

.modal-close {
  width: 28px; height: 28px;
  border-radius: var(--radius-sm);
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  color: var(--text-3);
  transition: all var(--transition);
  font-size: 13px;
}

.modal-close:hover { color: var(--red); border-color: rgba(239,68,68,0.3); }

.modal-body { padding: 20px; overflow-y: auto; }

.modal-footer {
  padding: 14px 20px;
  border-top: 1px solid var(--border);
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

/* ── Alert / Toast ── */
.alert {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 14px;
  border-radius: var(--radius);
  border: 1px solid;
  font-size: 13px;
  margin-bottom: 14px;
}

.alert-success { background: var(--green-d); border-color: rgba(16,185,129,0.3); color: #6ee7b7; }
.alert-danger  { background: var(--red-d);   border-color: rgba(239,68,68,0.3);  color: #fca5a5; }
.alert-warning { background: var(--amber-d); border-color: rgba(245,158,11,0.3); color: #fcd34d; }
.alert-info    { background: var(--sky-d);   border-color: rgba(14,165,233,0.3); color: #7dd3fc; }

/* Flash toast */
.toast-wrap {
  position: fixed;
  bottom: 20px; right: 20px;
  z-index: 900;
  display: flex; flex-direction: column; gap: 8px;
}

.toast {
  display: flex; align-items: center; gap: 10px;
  background: var(--bg-surface);
  border: 1px solid var(--border-light);
  border-radius: var(--radius);
  padding: 12px 16px;
  font-size: 13px;
  min-width: 260px;
  box-shadow: var(--shadow);
  border-left: 3px solid;
}

.toast-success { border-left-color: var(--green); }
.toast-error   { border-left-color: var(--red); }
.toast-info    { border-left-color: var(--indigo); }

/* ── Access denied ── */
.access-denied {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 12px;
  height: 300px; text-align: center;
}

.access-denied i { font-size: 40px; color: var(--text-4); }
.access-denied h3 { font-size: 16px; font-weight: 600; color: var(--text-2); }
.access-denied p { font-size: 13px; color: var(--text-3); }

/* ── Pagination ── */
.pagination {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 12px 16px;
  border-top: 1px solid var(--border);
  justify-content: flex-end;
}

/* ── Empty state ── */
.empty-state {
  padding: 48px 20px;
  text-align: center;
}

.empty-state i { font-size: 36px; color: var(--text-4); margin-bottom: 12px; }
.empty-state h3 { font-size: 14px; color: var(--text-2); font-weight: 600; }
.empty-state p { font-size: 12px; color: var(--text-4); margin-top: 4px; }

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
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(51,65,85,0.4);
  font-size: 13px;
}

.info-row:last-child { border-bottom: none; }
.info-row .key { color: var(--text-3); }
.info-row .val { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--text-2); }

/* Activity feed */
.feed-item {
  display: flex;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(51,65,85,0.3);
}

.feed-item:last-child { border-bottom: none; }

.feed-icon {
  width: 30px; height: 30px;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; flex-shrink: 0;
}

.feed-icon.indigo { background: var(--indigo-dim); color: #a5b4fc; }
.feed-icon.green  { background: var(--green-d);    color: #6ee7b7; }
.feed-icon.red    { background: var(--red-d);      color: #fca5a5; }
.feed-icon.amber  { background: var(--amber-d);    color: #fcd34d; }

.feed-body { flex: 1; min-width: 0; }
.feed-title { font-size: 13px; font-weight: 500; color: var(--text-1); }
.feed-desc  { font-size: 11px; color: var(--text-3); margin-top: 1px; font-family: 'JetBrains Mono', monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.feed-time  { font-size: 11px; color: var(--text-4); flex-shrink: 0; font-family: 'JetBrains Mono', monospace; }

/* Charts */
.chart-container { position: relative; height: 200px; }

/* OLT ports */
.port-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 5px; }

.olt-port {
  aspect-ratio: 1;
  border-radius: 5px;
  display: flex; align-items: center; justify-content: center;
  font-size: 9px;
  font-family: 'JetBrains Mono', monospace;
  cursor: pointer;
  transition: transform var(--transition);
  border: 1px solid;
}

.olt-port:hover { transform: scale(1.1); }
.olt-port.up     { background: var(--green-d); border-color: rgba(16,185,129,0.3); color: #6ee7b7; }
.olt-port.down   { background: var(--red-d);   border-color: rgba(239,68,68,0.2);  color: #fca5a5; }
.olt-port.unused { background: var(--bg-elevated); border-color: var(--border); color: var(--text-4); }

/* Signal bars */
.signal-bars { display: flex; gap: 2px; align-items: flex-end; }
.signal-bar {
  width: 4px;
  border-radius: 1px;
}

.signal-bar.filled.excellent { background: var(--green); }
.signal-bar.filled.good      { background: #7dd3fc; }
.signal-bar.filled.weak      { background: var(--amber); }
.signal-bar.filled.poor      { background: var(--red); }
.signal-bar:not(.filled)     { background: var(--border); }

/* Progress bar */
.progress-bar {
  height: 4px;
  background: var(--bg-elevated);
  border-radius: 2px;
  overflow: hidden;
  margin-top: 4px;
}

.progress-fill {
  height: 100%;
  border-radius: 2px;
  transition: width 0.5s ease;
}

.progress-fill.indigo { background: linear-gradient(90deg, var(--indigo-dark), var(--indigo)); }
.progress-fill.green  { background: linear-gradient(90deg, #059669, var(--green)); }
.progress-fill.amber  { background: linear-gradient(90deg, #d97706, var(--amber)); }
.progress-fill.red    { background: linear-gradient(90deg, #dc2626, var(--red)); }

/* ── Laravel pagination override ── */
nav[aria-label="Pagination"] {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
  padding: 12px 16px;
  border-top: 1px solid var(--border);
}

nav[aria-label="Pagination"] a,
nav[aria-label="Pagination"] span,
nav[aria-label="Pagination"] button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 28px;
  padding: 0 8px;
  border-radius: var(--radius-sm);
  font-size: 12px;
  background: var(--bg-input);
  border: 1px solid var(--border);
  color: var(--text-2);
  text-decoration: none;
  transition: all var(--transition);
  cursor: pointer;
}

nav[aria-label="Pagination"] a:hover { border-color: var(--indigo); color: var(--text-1); }
nav[aria-label="Pagination"] span[aria-current="page"] { background: var(--indigo); border-color: var(--indigo); color: white; }
nav[aria-label="Pagination"] span[aria-disabled="true"] { opacity: 0.4; cursor: not-allowed; }
</style>
@stack('styles')
</head>
<body>
<div class="app-layout">
  <!-- ── SIDEBAR ── -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon"><i class="fas fa-network-wired"></i></div>
      <div class="logo-text">
        <div class="app-name">NetCORE</div>
        <div class="app-sub">ISP Management</div>
      </div>
    </div>

    <div class="sidebar-user">
      <div class="user-avatar {{ auth()->user()->role }}">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
      </div>
      <div>
        <div class="user-name">{{ auth()->user()->name }}</div>
        <span class="user-role {{ auth()->user()->role }}">{{ strtoupper(auth()->user()->role) }}</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Utama</div>
      <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-gauge-high"></i> Dashboard
      </a>
      <a href="{{ route('pelanggan.index') }}" class="nav-item {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i> Pelanggan
      </a>

      @if(auth()->user()->hasRole(['admin','teknisi']))
      <div class="nav-section-label">Jaringan</div>
      <a href="{{ route('radius.index') }}" class="nav-item {{ request()->routeIs('radius.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-circle-dot"></i> RADIUS
        @php $onlineCount = \App\Models\RadiusSession::count(); @endphp
        @if($onlineCount) <span class="nav-badge green">{{ $onlineCount }}</span> @endif
      </a>
      <a href="{{ route('olt.index') }}" class="nav-item {{ request()->routeIs('olt.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-sitemap"></i> OLT & ONU
      </a>
      <a href="{{ route('nas.index') }}" class="nav-item {{ request()->routeIs('nas.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-server"></i> NAS Router
      </a>
      @endif

      @if(auth()->user()->hasRole(['admin','kasir']))
      <div class="nav-section-label">Keuangan</div>
      <a href="{{ route('invoice.index') }}" class="nav-item {{ request()->routeIs('invoice.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice-dollar"></i> Invoice
        @php $unpaid = \App\Models\Invoice::where('status','unpaid')->count(); @endphp
        @if($unpaid) <span class="nav-badge red">{{ $unpaid }}</span> @endif
      </a>
      <a href="{{ route('pembayaran.index') }}" class="nav-item {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-bill-transfer"></i> Pembayaran
      </a>
      <a href="{{ route('isolir.log') }}" class="nav-item {{ request()->routeIs('isolir.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-lock"></i> Log Isolir
      </a>
      @endif

      <div class="nav-section-label">Sistem</div>
      <a href="{{ route('notifikasi.index') }}" class="nav-item {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bell"></i> Notifikasi
        @if(!empty($notifUnreadCount))
          <span class="nav-badge red">{{ $notifUnreadCount }}</span>
        @endif
      </a>
      <a href="{{ route('bantuan.index') }}" class="nav-item {{ request()->routeIs('bantuan.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-circle-question"></i> Bantuan
      </a>

      @if(auth()->user()->isAdmin())
      <a href="{{ route('pengguna.index') }}" class="nav-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users-gear"></i> Pengguna
      </a>
      <a href="{{ route('paket.index') }}" class="nav-item {{ request()->routeIs('paket.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-boxes-stacked"></i> Paket Layanan
      </a>
      <a href="{{ route('pengaturan.index') }}" class="nav-item {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-sliders"></i> Pengaturan
      </a>
      <a href="{{ route('activity_log.index') }}" class="nav-item {{ request()->routeIs('activity_log.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-timeline"></i> Activity Log
      </a>
      <a href="{{ route('sistem.db_info') }}" class="nav-item {{ request()->routeIs('sistem.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-database"></i> Info Database
      </a>
      @endif
    </nav>

    <div class="sidebar-footer">
      <div class="status-indicators">
        <span class="status-dot"><span class="dot online"></span>RADIUS</span>
        <span class="status-dot"><span class="dot online"></span>DB</span>
        <span class="status-dot"><span class="dot warn"></span>OLT-1</span>
        <span class="status-dot"><span class="dot online"></span>Billing</span>
      </div>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="main-wrap">
    <header class="topbar">
      <div class="topbar-left">
        <span class="page-title">@yield('page-title', 'Dashboard')</span>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-sub">@yield('breadcrumb', 'NetCORE')</span>
      </div>
      <div class="topbar-right">
        <div class="search-wrap">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Cari pelanggan, IP, invoice...">
        </div>
        <a href="{{ route('notifikasi.index') }}" class="icon-btn">
          <i class="fas fa-bell"></i>
          @if(!empty($notifUnreadCount))
            <span class="notif-badge">{{ min($notifUnreadCount, 99) }}</span>
          @endif
        </a>
        <form method="POST" action="{{ route('logout') }}" style="display:contents">
          @csrf
          <button type="submit" class="logout-btn">
            <i class="fas fa-right-from-bracket"></i> Logout
          </button>
        </form>
      </div>
    </header>

    <main class="page-content">
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
  </div>
</div>

@stack('scripts')
</body>
</html>
