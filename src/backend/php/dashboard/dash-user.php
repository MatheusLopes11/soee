<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>SOEE — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
/* ─────────────────────────────────────────
   VARIÁVEIS
───────────────────────────────────────── */
:root {
  --azul-principal: #1e5671;
  --azul-secundario: #2c7da3;
  --laranja-destaque: #ff4d12;
  --laranja-sombra: rgba(255, 77, 18, 0.25);
  --fundo-pagina: #f8fafc;
  --fundo-bloco: #ffffff;
  --texto-principal: #1e293b;
  --texto-secundario: #64748b;
  --branco: #ffffff;
  --vidro: rgba(248, 250, 252, 0.92);
  --sombra-leve: 0 10px 40px -10px rgba(0,0,0,0.08);
  --sombra-hover: 0 24px 48px -12px rgba(0,0,0,0.14);
  --raio-grande: 20px;
  --raio-medio: 10px;
  --transicao: all 0.38s cubic-bezier(0.4, 0, 0.2, 1);
  --borda-sutil: rgba(30,86,113,0.09);
  --sidebar-w: 260px;
  --header-h: 68px;
}
[data-theme="dark"] {
  --fundo-pagina: #060d14;
  --fundo-bloco: #0c1825;
  --texto-principal: #e2e8f0;
  --texto-secundario: #8da8bc;
  --vidro: rgba(6,13,20,0.92);
  --borda-sutil: rgba(44,125,163,0.12);
}

/* ─── RESET ─── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }
body {
  font-family:'DM Sans', sans-serif;
  background:var(--fundo-pagina);
  color:var(--texto-principal);
  line-height:1.65;
  overflow-x:hidden;
}

/* ─── CURSOR ─── */
.cursor-dot {
  position:fixed; width:8px; height:8px;
  background:var(--laranja-destaque); border-radius:50%;
  pointer-events:none; z-index:9999;
  transform:translate(-50%,-50%); transition:transform 0.1s;
}
.cursor-ring {
  position:fixed; width:36px; height:36px;
  border:2px solid rgba(255,77,18,0.4); border-radius:50%;
  pointer-events:none; z-index:9998;
  transform:translate(-50%,-50%);
  transition:width 0.25s, height 0.25s, border-color 0.25s, transform 0.12s;
}
body:has(a:hover) .cursor-ring,
body:has(button:hover) .cursor-ring { width:56px; height:56px; border-color:rgba(255,77,18,0.7); }

/* ─── LOADER ─── */
#loader {
  position:fixed; inset:0;
  background:var(--azul-principal);
  display:flex; align-items:center; justify-content:center;
  z-index:10000; transition:opacity 0.6s, visibility 0.6s;
}
#loader.hide { opacity:0; visibility:hidden; }
.loader-inner { text-align:center; color:white; }
.loader-logo-text { font-family:'Playfair Display',serif; font-size:2.2rem; font-weight:800; letter-spacing:0.08em; }
.loader-logo-sub { font-size:0.75rem; letter-spacing:0.18em; text-transform:uppercase; opacity:0.65; margin-top:4px; }
.loader-bar { width:200px; height:3px; background:rgba(255,255,255,0.2); border-radius:999px; margin:16px auto 0; overflow:hidden; }
.loader-bar-fill { height:100%; background:var(--laranja-destaque); border-radius:999px; animation:loadBar 1.4s cubic-bezier(0.4,0,0.2,1) forwards; }
@keyframes loadBar { from{width:0} to{width:100%} }

/* ─── LAYOUT ─── */
.dash-wrapper { display:flex; min-height:100vh; }

/* ─── SIDEBAR ─── */
.sidebar {
  width:var(--sidebar-w);
  background:var(--fundo-bloco);
  border-right:1px solid var(--borda-sutil);
  display:flex; flex-direction:column;
  position:fixed; top:0; left:0; bottom:0;
  z-index:100; transition:var(--transicao);
  overflow:hidden;
}
.sidebar-logo {
  padding:22px 24px 18px;
  border-bottom:1px solid var(--borda-sutil);
  display:flex; align-items:center; gap:12px;
}
.sidebar-logo-icon {
  width:40px; height:40px; border-radius:12px;
  background:linear-gradient(135deg,var(--azul-principal),var(--azul-secundario));
  display:flex; align-items:center; justify-content:center;
  font-size:1.2rem; color:white;
  box-shadow:0 4px 14px rgba(30,86,113,0.3);
}
.sidebar-logo-text { font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:800; color:var(--azul-principal); }

.sidebar-sport-badge {
  margin:16px 16px 0;
  background:linear-gradient(135deg,rgba(255,77,18,0.1),rgba(255,77,18,0.04));
  border:1px solid rgba(255,77,18,0.2);
  border-radius:var(--raio-medio);
  padding:14px 16px;
  display:flex; align-items:center; gap:12px;
}
.sport-icon {
  width:38px; height:38px; border-radius:10px;
  background:var(--laranja-destaque);
  display:flex; align-items:center; justify-content:center;
  font-size:1.1rem; color:white;
  box-shadow:0 4px 12px var(--laranja-sombra);
}
.sport-info { flex:1; }
.sport-label { font-size:0.65rem; text-transform:uppercase; letter-spacing:0.12em; color:var(--texto-secundario); }
.sport-name { font-size:0.92rem; font-weight:700; color:var(--texto-principal); }

.sidebar-nav { flex:1; padding:20px 12px; overflow-y:auto; }
.nav-section-label {
  font-size:0.62rem; font-weight:700; letter-spacing:0.16em; text-transform:uppercase;
  color:var(--texto-secundario); opacity:0.6;
  padding:8px 12px 6px; margin-top:8px;
}
.nav-item {
  display:flex; align-items:center; gap:12px;
  padding:10px 14px; border-radius:var(--raio-medio);
  cursor:pointer; transition:var(--transicao);
  margin-bottom:2px; text-decoration:none; color:var(--texto-principal);
  font-size:0.88rem; font-weight:500; position:relative;
  border:1px solid transparent;
}
.nav-item:hover {
  background:rgba(255,77,18,0.06);
  color:var(--laranja-destaque);
  border-color:rgba(255,77,18,0.1);
}
.nav-item.active {
  background:linear-gradient(135deg,rgba(255,77,18,0.1),rgba(255,77,18,0.04));
  color:var(--laranja-destaque); font-weight:700;
  border-color:rgba(255,77,18,0.18);
}
.nav-item.active::before {
  content:'';
  position:absolute; left:0; top:4px; bottom:4px;
  width:3px; background:var(--laranja-destaque);
  border-radius:0 3px 3px 0;
}
.nav-item i { width:20px; text-align:center; font-size:0.9rem; }
.nav-badge {
  margin-left:auto; background:var(--laranja-destaque); color:white;
  font-size:0.6rem; font-weight:700; padding:2px 7px;
  border-radius:999px; letter-spacing:0.04em;
}

.sidebar-user {
  border-top:1px solid var(--borda-sutil);
  padding:16px;
  display:flex; align-items:center; gap:12px;
}
.user-avatar {
  width:38px; height:38px; border-radius:50%;
  background:linear-gradient(135deg,var(--laranja-destaque),#c2410c);
  display:flex; align-items:center; justify-content:center;
  color:white; font-weight:700; font-size:0.9rem;
  flex-shrink:0;
}
.user-info { flex:1; min-width:0; }
.user-name { font-size:0.85rem; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.user-role { font-size:0.7rem; color:var(--texto-secundario); }
.user-menu-btn {
  background:none; border:none; cursor:pointer;
  color:var(--texto-secundario); font-size:0.8rem;
  padding:4px; border-radius:6px; transition:var(--transicao);
}
.user-menu-btn:hover { color:var(--laranja-destaque); }

/* ─── MAIN AREA ─── */
.dash-main {
  margin-left:var(--sidebar-w);
  flex:1; display:flex; flex-direction:column;
  min-height:100vh;
}

/* ─── TOP BAR ─── */
.topbar {
  height:var(--header-h);
  background:var(--vidro);
  backdrop-filter:blur(16px) saturate(1.6);
  border-bottom:1px solid var(--borda-sutil);
  display:flex; align-items:center;
  padding:0 32px; gap:16px;
  position:sticky; top:0; z-index:50;
}
.topbar-title {
  font-family:'Playfair Display',serif;
  font-size:1.25rem; font-weight:800; color:var(--azul-principal);
  flex:1;
}
.topbar-title span { color:var(--laranja-destaque); }
.topbar-search {
  position:relative;
}
.topbar-search input {
  background:var(--fundo-pagina);
  border:1px solid var(--borda-sutil);
  border-radius:var(--raio-medio);
  padding:8px 16px 8px 38px;
  font-family:'DM Sans',sans-serif;
  font-size:0.85rem; color:var(--texto-principal);
  width:220px; transition:var(--transicao);
  outline:none;
}
.topbar-search input:focus {
  border-color:var(--azul-secundario);
  box-shadow:0 0 0 3px rgba(44,125,163,0.12);
  width:260px;
}
.topbar-search i {
  position:absolute; left:12px; top:50%; transform:translateY(-50%);
  color:var(--texto-secundario); font-size:0.8rem;
}
.botao-icone {
  background:none; border:1px solid var(--borda-sutil); cursor:pointer;
  width:38px; height:38px; border-radius:var(--raio-medio);
  display:flex; align-items:center; justify-content:center;
  font-size:0.95rem; color:var(--texto-principal);
  transition:var(--transicao);
}
.botao-icone:hover {
  background:var(--fundo-bloco); transform:rotate(15deg) scale(1.1);
  border-color:var(--laranja-destaque); color:var(--laranja-destaque);
}
.notif-btn { position:relative; }
.notif-dot {
  position:absolute; top:6px; right:6px;
  width:8px; height:8px; border-radius:50%;
  background:var(--laranja-destaque);
  border:2px solid var(--fundo-bloco);
}

/* ─── PAGE CONTENT ─── */
.dash-content { padding:32px; flex:1; }

/* ─── PAGE VIEW ─── */
.page-view { display:none; animation:fadeUp 0.5s ease both; }
.page-view.active { display:block; }
@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

/* ─── WELCOME BANNER ─── */
.welcome-banner {
  background:linear-gradient(135deg,#0a2d3d 0%,#1e5671 50%,#0f3447 100%);
  border-radius:var(--raio-grande);
  padding:36px 40px;
  position:relative; overflow:hidden;
  margin-bottom:28px;
}
.welcome-banner::before {
  content:'';
  position:absolute; inset:0;
  background:radial-gradient(ellipse 60% 80% at 80% 50%, rgba(255,77,18,0.18), transparent);
}
.welcome-banner::after {
  content:'SOEE';
  position:absolute; right:-20px; top:50%; transform:translateY(-50%);
  font-family:'Playfair Display',serif;
  font-size:8rem; font-weight:800;
  color:rgba(255,255,255,0.04);
  pointer-events:none;
}
.welcome-content { position:relative; z-index:1; }
.welcome-greeting { font-size:0.75rem; text-transform:uppercase; letter-spacing:0.16em; color:rgba(255,255,255,0.55); margin-bottom:6px; }
.welcome-name { font-family:'Playfair Display',serif; font-size:2rem; font-weight:800; color:white; margin-bottom:8px; }
.welcome-name span { color:var(--laranja-destaque); }
.welcome-sub { color:rgba(255,255,255,0.65); font-size:0.92rem; }
.welcome-stats { display:flex; gap:32px; margin-top:24px; }
.w-stat strong {
  display:block; font-family:'Playfair Display',serif;
  font-size:1.8rem; font-weight:800; color:var(--laranja-destaque);
}
.w-stat span { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em; color:rgba(255,255,255,0.5); }

/* ─── STATS CARDS ─── */
.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:28px; }
.stat-card {
  background:var(--fundo-bloco);
  border:1px solid var(--borda-sutil);
  border-radius:var(--raio-grande);
  padding:24px 26px;
  box-shadow:var(--sombra-leve);
  transition:var(--transicao);
  position:relative; overflow:hidden;
}
.stat-card:hover { transform:translateY(-6px); box-shadow:var(--sombra-hover); }
.stat-card-icon {
  width:48px; height:48px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  font-size:1.2rem; margin-bottom:16px;
}
.stat-card-icon.orange { background:rgba(255,77,18,0.12); color:var(--laranja-destaque); }
.stat-card-icon.blue   { background:rgba(30,86,113,0.12); color:var(--azul-principal); }
.stat-card-icon.green  { background:rgba(34,197,94,0.12); color:#16a34a; }
.stat-card-icon.purple { background:rgba(139,92,246,0.12); color:#7c3aed; }
.stat-card-value { font-family:'Playfair Display',serif; font-size:2rem; font-weight:800; color:var(--texto-principal); }
.stat-card-label { font-size:0.78rem; color:var(--texto-secundario); margin-top:4px; text-transform:uppercase; letter-spacing:0.08em; }
.stat-card-change { position:absolute; top:20px; right:20px; font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:999px; }
.change-up   { background:rgba(34,197,94,0.12); color:#16a34a; }
.change-down { background:rgba(239,68,68,0.12);  color:#dc2626; }

/* ─── SECTION HEADER ─── */
.section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.section-title { font-family:'Playfair Display',serif; font-size:1.4rem; font-weight:800; color:var(--azul-principal); }
.section-tag {
  display:inline-block; font-size:0.65rem; font-weight:700;
  letter-spacing:0.16em; text-transform:uppercase;
  color:var(--laranja-destaque); background:rgba(255,77,18,0.08);
  padding:4px 12px; border-radius:999px; margin-left:10px;
  border:1px solid rgba(255,77,18,0.18);
}
.ver-mais {
  font-size:0.78rem; color:var(--azul-secundario); text-decoration:none;
  font-weight:600; display:inline-flex; align-items:center; gap:6px;
  transition:var(--transicao);
}
.ver-mais:hover { color:var(--laranja-destaque); gap:10px; }

/* ─── TEAMS GRID ─── */
.teams-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:20px; }
.team-card {
  background:var(--fundo-bloco);
  border:1px solid var(--borda-sutil);
  border-radius:var(--raio-grande);
  padding:28px 24px;
  box-shadow:var(--sombra-leve);
  transition:var(--transicao);
  cursor:pointer; text-align:center; position:relative; overflow:hidden;
}
.team-card::after {
  content:'';
  position:absolute; bottom:0; left:0; right:0; height:3px;
  background:linear-gradient(90deg,var(--laranja-destaque),var(--azul-secundario));
  transform:scaleX(0); transform-origin:left; transition:transform 0.4s cubic-bezier(0.4,0,0.2,1);
}
.team-card:hover { transform:translateY(-8px); box-shadow:var(--sombra-hover); border-color:rgba(255,77,18,0.2); }
.team-card:hover::after { transform:scaleX(1); }
.team-card.my-team { border-color:rgba(255,77,18,0.3); background:linear-gradient(135deg,rgba(255,77,18,0.04),var(--fundo-bloco)); }
.my-team-badge {
  position:absolute; top:12px; right:12px;
  background:var(--laranja-destaque); color:white;
  font-size:0.58rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase;
  padding:3px 8px; border-radius:999px;
}
.team-logo {
  width:64px; height:64px; border-radius:50%;
  background:linear-gradient(135deg,var(--azul-principal),var(--azul-secundario));
  display:flex; align-items:center; justify-content:center;
  font-size:1.8rem; margin:0 auto 16px;
  box-shadow:0 8px 24px rgba(30,86,113,0.25);
  transition:var(--transicao);
}
.team-card:hover .team-logo { transform:scale(1.1) rotate(6deg); }
.team-name { font-weight:700; font-size:1rem; color:var(--texto-principal); margin-bottom:4px; }
.team-meta { font-size:0.78rem; color:var(--texto-secundario); }
.team-points-badge {
  margin-top:14px; display:inline-block;
  background:rgba(30,86,113,0.08); color:var(--azul-principal);
  font-size:0.78rem; font-weight:700; padding:5px 14px; border-radius:999px;
  border:1px solid rgba(30,86,113,0.12);
}

/* ─── RANKING TABLE ─── */
.ranking-card {
  background:var(--fundo-bloco);
  border:1px solid var(--borda-sutil);
  border-radius:var(--raio-grande);
  box-shadow:var(--sombra-leve);
  overflow:hidden;
}
.ranking-table { width:100%; border-collapse:collapse; }
.ranking-table th {
  padding:14px 20px; font-size:0.68rem; font-weight:700;
  letter-spacing:0.12em; text-transform:uppercase;
  color:var(--texto-secundario); text-align:left;
  border-bottom:1px solid var(--borda-sutil);
  background:var(--fundo-pagina);
}
.ranking-table td { padding:14px 20px; font-size:0.88rem; border-bottom:1px solid var(--borda-sutil); }
.ranking-table tr:last-child td { border-bottom:none; }
.ranking-table tr { transition:var(--transicao); }
.ranking-table tbody tr:hover { background:rgba(255,77,18,0.03); }
.ranking-table tr.highlight-row { background:linear-gradient(90deg,rgba(255,77,18,0.06),transparent); }
.rank-pos {
  width:30px; height:30px; border-radius:8px;
  display:inline-flex; align-items:center; justify-content:center;
  font-weight:800; font-size:0.85rem;
}
.rank-pos.gold   { background:rgba(234,179,8,0.15); color:#ca8a04; }
.rank-pos.silver { background:rgba(148,163,184,0.15); color:#64748b; }
.rank-pos.bronze { background:rgba(180,83,9,0.15); color:#b45309; }
.rank-pos.normal { background:var(--fundo-pagina); color:var(--texto-secundario); }
.rank-pos.my     { background:rgba(255,77,18,0.15); color:var(--laranja-destaque); }
.team-row-logo {
  width:32px; height:32px; border-radius:50%;
  background:linear-gradient(135deg,var(--azul-principal),var(--azul-secundario));
  display:inline-flex; align-items:center; justify-content:center;
  font-size:0.9rem; margin-right:10px; vertical-align:middle;
  box-shadow:0 2px 8px rgba(30,86,113,0.2);
}
.rank-pts { font-weight:700; color:var(--azul-principal); }
.rank-form { display:flex; gap:4px; align-items:center; }
.form-dot { width:20px; height:20px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.6rem; font-weight:800; }
.form-w { background:rgba(34,197,94,0.15); color:#16a34a; }
.form-l { background:rgba(239,68,68,0.15); color:#dc2626; }
.form-d { background:rgba(148,163,184,0.15); color:#64748b; }

/* ─── MEU TIME ─── */
.my-team-hero {
  background:linear-gradient(135deg,var(--azul-principal),var(--azul-secundario));
  border-radius:var(--raio-grande);
  padding:36px; color:white; margin-bottom:24px;
  position:relative; overflow:hidden;
}
.my-team-hero::before {
  content:'';
  position:absolute; inset:0;
  background:radial-gradient(ellipse 70% 70% at 80% 50%,rgba(255,77,18,0.2),transparent);
}
.my-team-hero-content { position:relative; z-index:1; display:flex; align-items:center; gap:28px; }
.my-team-big-logo {
  width:90px; height:90px; border-radius:50%;
  background:rgba(255,255,255,0.15);
  backdrop-filter:blur(10px);
  display:flex; align-items:center; justify-content:center;
  font-size:3rem; flex-shrink:0;
  border:2px solid rgba(255,255,255,0.2);
  box-shadow:0 10px 30px rgba(0,0,0,0.2);
}
.my-team-info h2 { font-family:'Playfair Display',serif; font-size:1.8rem; font-weight:800; margin-bottom:6px; }
.my-team-info p { opacity:0.7; font-size:0.9rem; }
.my-team-stats { display:flex; gap:24px; margin-top:16px; }
.mt-stat strong { display:block; font-size:1.4rem; font-weight:800; color:var(--laranja-destaque); }
.mt-stat span { font-size:0.65rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6; }

.players-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px; }
.player-card {
  background:var(--fundo-bloco);
  border:1px solid var(--borda-sutil);
  border-radius:16px; padding:20px;
  box-shadow:var(--sombra-leve);
  transition:var(--transicao);
  text-align:center;
}
.player-card:hover { transform:translateY(-6px); box-shadow:var(--sombra-hover); }
.player-avatar {
  width:52px; height:52px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:1.3rem; font-weight:700; margin:0 auto 12px;
  color:white; position:relative;
}
.player-avatar::after {
  content:''; position:absolute; inset:-2px; border-radius:50%;
  background:linear-gradient(135deg,var(--laranja-destaque),var(--azul-secundario));
  z-index:-1;
}
.player-name { font-weight:700; font-size:0.88rem; color:var(--texto-principal); margin-bottom:4px; }
.player-pos { font-size:0.7rem; color:var(--texto-secundario); text-transform:uppercase; letter-spacing:0.1em; }
.player-num {
  display:inline-block; margin-top:10px;
  background:rgba(30,86,113,0.08); color:var(--azul-principal);
  font-weight:800; font-size:0.8rem;
  padding:3px 12px; border-radius:999px;
}
.captain-badge {
  display:inline-flex; align-items:center; gap:4px;
  background:rgba(234,179,8,0.15); color:#ca8a04;
  font-size:0.62rem; font-weight:700; padding:2px 8px;
  border-radius:999px; margin-top:6px; margin-left:4px;
}

/* ─── REVEAL ─── */
.reveal { opacity:0; transform:translateY(28px); transition:opacity 0.7s ease, transform 0.7s ease; }
.reveal.visible { opacity:1; transform:translateY(0); }
.reveal-delay-1 { transition-delay:0.1s; }
.reveal-delay-2 { transition-delay:0.2s; }
.reveal-delay-3 { transition-delay:0.3s; }
.reveal-delay-4 { transition-delay:0.4s; }

/* ─── RESPONSIVE ─── */
@media (max-width:1100px) {
  .stats-grid { grid-template-columns:repeat(2,1fr); }
}
@media (max-width:860px) {
  :root { --sidebar-w:0px; }
  .sidebar { transform:translateX(-260px); }
  .sidebar.open { transform:translateX(0); width:260px; }
  .dash-main { margin-left:0; }
  .cursor-dot,.cursor-ring { display:none; }
  .topbar-search input { width:160px; }
}
@media (max-width:560px) {
  .stats-grid { grid-template-columns:1fr 1fr; }
  .welcome-stats { gap:20px; }
  .dash-content { padding:20px; }
}
</style>
</head>
<body>

<!-- CURSOR -->
<div class="cursor-dot"  id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- LOADER -->
<div id="loader">
  <div class="loader-inner">
    <div class="loader-logo-text">SOEE</div>
    <div class="loader-logo-sub">Plataforma Esportiva</div>
    <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  </div>
</div>

<!-- LAYOUT -->
<div class="dash-wrapper">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon"><i class="fa-solid fa-trophy"></i></div>
      <span class="sidebar-logo-text">SOEE</span>
    </div>

    <div class="sidebar-sport-badge">
      <div class="sport-icon" id="sportIcon"><i class="fa-solid fa-futbol"></i></div>
      <div class="sport-info">
        <div class="sport-label">Esporte ativo</div>
        <div class="sport-name" id="sportName">Futebol</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Principal</div>
      <a class="nav-item active" data-page="overview" onclick="navigate('overview',this)">
        <i class="fa-solid fa-house"></i> Visão Geral
      </a>
      <a class="nav-item" data-page="times" onclick="navigate('times',this)">
        <i class="fa-solid fa-shield-halved"></i> Times
        <span class="nav-badge" id="teamsBadge">8</span>
      </a>
      <a class="nav-item" data-page="classificacao" onclick="navigate('classificacao',this)">
        <i class="fa-solid fa-ranking-star"></i> Classificação
      </a>
      <a class="nav-item" data-page="meutime" onclick="navigate('meutime',this)">
        <i class="fa-solid fa-people-group"></i> Meu Time
      </a>

      <div class="nav-section-label" style="margin-top:16px">Conta</div>
      <a class="nav-item" data-page="perfil" onclick="navigate('perfil',this)">
        <i class="fa-solid fa-user"></i> Perfil
      </a>
      <a class="nav-item" onclick="openSportPicker()">
        <i class="fa-solid fa-sliders"></i> Trocar Esporte
      </a>
    </nav>

    <div class="sidebar-user">
      <div class="user-avatar" id="userAvatarSidebar">M</div>
      <div class="user-info">
        <div class="user-name" id="userNameSidebar">Mariana Costa</div>
        <div class="user-role">Atleta</div>
      </div>
      <button class="user-menu-btn" id="toggle-theme" title="Alternar tema">
        <i class="fa-solid fa-moon"></i>
      </button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dash-main">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-title" id="pageTitle">Visão <span>Geral</span></div>
      <div class="topbar-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Buscar times, jogadores…" />
      </div>
      <button class="botao-icone notif-btn" title="Notificações">
        <i class="fa-solid fa-bell"></i>
        <span class="notif-dot"></span>
      </button>
    </header>

    <!-- CONTENT -->
    <div class="dash-content">

      <!-- ──── OVERVIEW ──── -->
      <div class="page-view active" id="page-overview">

        <div class="welcome-banner reveal">
          <div class="welcome-content">
            <div class="welcome-greeting">Bem-vinda de volta 👋</div>
            <div class="welcome-name">Olá, <span id="heroName">Mariana</span>!</div>
            <div class="welcome-sub" id="heroSub">Acompanhe o desempenho do seu time e a classificação do campeonato.</div>
            <div class="welcome-stats">
              <div class="w-stat"><strong id="heroRank">3°</strong><span>Posição</span></div>
              <div class="w-stat"><strong id="heroGames">12</strong><span>Jogos</span></div>
              <div class="w-stat"><strong id="heroPoints">28</strong><span>Pontos</span></div>
            </div>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card reveal reveal-delay-1">
            <div class="stat-card-change change-up">▲ 2</div>
            <div class="stat-card-icon orange"><i class="fa-solid fa-fire"></i></div>
            <div class="stat-card-value" id="sc1">7</div>
            <div class="stat-card-label">Vitórias</div>
          </div>
          <div class="stat-card reveal reveal-delay-2">
            <div class="stat-card-change change-down">▼ 1</div>
            <div class="stat-card-icon blue"><i class="fa-solid fa-handshake"></i></div>
            <div class="stat-card-value" id="sc2">3</div>
            <div class="stat-card-label">Empates</div>
          </div>
          <div class="stat-card reveal reveal-delay-3">
            <div class="stat-card-change change-down">▼ 1</div>
            <div class="stat-card-icon green"><i class="fa-solid fa-bullseye"></i></div>
            <div class="stat-card-value" id="sc3">24</div>
            <div class="stat-card-label" id="sc3Label">Gols Marcados</div>
          </div>
          <div class="stat-card reveal reveal-delay-4">
            <div class="stat-card-change change-up">▲ 5%</div>
            <div class="stat-card-icon purple"><i class="fa-solid fa-chart-line"></i></div>
            <div class="stat-card-value">82%</div>
            <div class="stat-card-label">Aproveitamento</div>
          </div>
        </div>

        <div class="section-header reveal">
          <div>
            <span class="section-title">Times do Campeonato</span>
            <span class="section-tag" id="sportTagOverview">Futebol</span>
          </div>
          <a class="ver-mais" onclick="navigate('times',null)">Ver todos <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="teams-grid" id="teamsGridOverview"></div>
      </div>

      <!-- ──── TIMES ──── -->
      <div class="page-view" id="page-times">
        <div class="section-header reveal">
          <div>
            <span class="section-title">Todos os Times</span>
            <span class="section-tag" id="sportTagTimes">Futebol</span>
          </div>
        </div>
        <div class="teams-grid" id="teamsGridFull"></div>
      </div>

      <!-- ──── CLASSIFICAÇÃO ──── -->
      <div class="page-view" id="page-classificacao">
        <div class="section-header reveal">
          <div>
            <span class="section-title">Classificação Geral</span>
            <span class="section-tag" id="sportTagClass">Futebol</span>
          </div>
        </div>
        <div class="ranking-card reveal">
          <table class="ranking-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Time</th>
                <th>PJ</th>
                <th>V</th>
                <th>E</th>
                <th>D</th>
                <th>PTS</th>
                <th>Forma</th>
              </tr>
            </thead>
            <tbody id="rankingBody"></tbody>
          </table>
        </div>
      </div>

      <!-- ──── MEU TIME ──── -->
      <div class="page-view" id="page-meutime">
        <div class="my-team-hero reveal">
          <div class="my-team-hero-content">
            <div class="my-team-big-logo" id="myTeamBigLogo">⚽</div>
            <div class="my-team-info">
              <h2 id="myTeamName">Estrelas FC</h2>
              <p id="myTeamSport">Futebol • Campeonato Regional</p>
              <div class="my-team-stats">
                <div class="mt-stat"><strong id="mtPos">3°</strong><span>Posição</span></div>
                <div class="mt-stat"><strong id="mtPts">28</strong><span>Pontos</span></div>
                <div class="mt-stat"><strong id="mtPlayers">15</strong><span>Jogadores</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="section-header reveal">
          <span class="section-title">Elenco</span>
        </div>
        <div class="players-grid reveal" id="playersGrid"></div>
      </div>

    </div><!-- dash-content -->
  </main>
</div>

<!-- SPORT PICKER MODAL -->
<div id="sportModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:5000;display:none;align-items:center;justify-content:center;">
  <div style="background:var(--fundo-bloco);border-radius:var(--raio-grande);padding:40px;max-width:420px;width:90%;box-shadow:0 40px 80px rgba(0,0,0,0.25);">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;color:var(--azul-principal);margin-bottom:8px;">Escolha seu esporte</h2>
    <p style="color:var(--texto-secundario);font-size:0.88rem;margin-bottom:24px;">O dashboard se adaptará ao esporte selecionado.</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" id="sportPickerGrid"></div>
    <button onclick="closeSportPicker()" style="margin-top:20px;width:100%;background:none;border:1px solid var(--borda-sutil);border-radius:var(--raio-medio);padding:10px;cursor:pointer;color:var(--texto-secundario);font-family:'DM Sans',sans-serif;">Cancelar</button>
  </div>
</div>

<script>
/* ──────────── DATA ──────────── */
const SPORTS = {
  futebol: {
    name:'Futebol', icon:'fa-solid fa-futbol', emoji:'⚽',
    scoreStat:'Gols Marcados',
    teams:[
      {name:'Estrelas FC',   emoji:'⭐', pts:28, pj:12, v:7, e:7, d:0, form:['V','E','V','V','E'], myTeam:true},
      {name:'Trovões SC',    emoji:'⚡', pts:32, pj:12, v:9, e:5, d:2, form:['V','V','V','E','V']},
      {name:'Águias EC',     emoji:'🦅', pts:26, pj:12, v:7, e:5, d:3, form:['V','D','V','E','V']},
      {name:'Leões FC',      emoji:'🦁', pts:23, pj:12, v:6, e:5, d:4, form:['E','V','D','V','E']},
      {name:'Falcões SC',    emoji:'🦆', pts:20, pj:12, v:5, e:5, d:5, form:['D','V','E','D','V']},
      {name:'Dragões EC',    emoji:'🐉', pts:17, pj:12, v:4, e:5, d:6, form:['D','D','V','E','D']},
      {name:'Onças FC',      emoji:'🐆', pts:14, pj:12, v:3, e:5, d:7, form:['D','E','D','V','D']},
      {name:'Lobos SC',      emoji:'🐺', pts:10, pj:12, v:2, e:4, d:8, form:['D','D','D','E','D']},
    ],
    players:[
      {name:'Mariana Costa',   pos:'Atacante',   num:10, captain:true, color:'#1e5671'},
      {name:'João Silva',      pos:'Goleiro',     num:1,  captain:false,color:'#7c3aed'},
      {name:'Ana Oliveira',    pos:'Defensora',   num:3,  captain:false,color:'#16a34a'},
      {name:'Pedro Mendes',    pos:'Meia',        num:8,  captain:false,color:'#ca8a04'},
      {name:'Carla Souza',     pos:'Lateral Dir.', num:2, captain:false,color:'#dc2626'},
      {name:'Lucas Ferreira',  pos:'Zagueiro',    num:4,  captain:false,color:'#0891b2'},
      {name:'Beatriz Lima',    pos:'Atacante',    num:11, captain:false,color:'#ff4d12'},
      {name:'Rafael Santos',   pos:'Lateral Esq.', num:6, captain:false,color:'#1e5671'},
      {name:'Camila Rocha',    pos:'Meia',        num:7,  captain:false,color:'#7c3aed'},
      {name:'Thiago Nunes',    pos:'Volante',     num:5,  captain:false,color:'#16a34a'},
    ]
  },
  basquete: {
    name:'Basquete', icon:'fa-solid fa-basketball', emoji:'🏀',
    scoreStat:'Pontos Marcados',
    teams:[
      {name:'Rockets SP',    emoji:'🚀', pts:30, pj:12, v:9, e:0, d:3, form:['V','V','D','V','V'], myTeam:true},
      {name:'Bulls RJ',      emoji:'🐂', pts:28, pj:12, v:8, e:0, d:4, form:['V','D','V','V','D']},
      {name:'Hawks MG',      emoji:'🦅', pts:24, pj:12, v:7, e:0, d:5, form:['V','V','D','D','V']},
      {name:'Bears SC',      emoji:'🐻', pts:20, pj:12, v:6, e:0, d:6, form:['D','V','V','D','V']},
      {name:'Wolves BA',     emoji:'🐺', pts:16, pj:12, v:5, e:0, d:7, form:['D','D','V','V','D']},
      {name:'Sharks PE',     emoji:'🦈', pts:12, pj:12, v:4, e:0, d:8, form:['D','D','D','V','D']},
    ],
    players:[
      {name:'Mariana Costa',   pos:'Armadora',    num:3,  captain:true, color:'#dc2626'},
      {name:'Alex Torres',     pos:'Ala',         num:23, captain:false,color:'#1e5671'},
      {name:'Juliana Freitas', pos:'Pivô',        num:5,  captain:false,color:'#7c3aed'},
      {name:'Diego Alves',     pos:'Ala-Pivô',    num:10, captain:false,color:'#16a34a'},
      {name:'Renata Gomes',    pos:'Armadora',    num:7,  captain:false,color:'#ca8a04'},
      {name:'Paulo Vieira',    pos:'Pivô',        num:12, captain:false,color:'#0891b2'},
      {name:'Luana Barros',    pos:'Ala',         num:8,  captain:false,color:'#ff4d12'},
      {name:'Marcos Lima',     pos:'Armador',     num:1,  captain:false,color:'#1e5671'},
    ]
  },
  volei: {
    name:'Vôlei', icon:'fa-solid fa-volleyball', emoji:'🏐',
    scoreStat:'Sets Vencidos',
    teams:[
      {name:'Tempestade VC', emoji:'⚡', pts:24, pj:10, v:8, e:0, d:2, form:['V','V','V','D','V']},
      {name:'Sol Nascente',  emoji:'☀️', pts:21, pj:10, v:7, e:0, d:3, form:['V','D','V','V','V'], myTeam:true},
      {name:'Mar Azul',      emoji:'🌊', pts:18, pj:10, v:6, e:0, d:4, form:['V','V','D','D','V']},
      {name:'Montanha',      emoji:'⛰️', pts:15, pj:10, v:5, e:0, d:5, form:['D','V','E','V','D']},
      {name:'Terra Firme',   emoji:'🌿', pts:12, pj:10, v:4, e:0, d:6, form:['D','D','V','V','D']},
      {name:'Vento Norte',   emoji:'🌪️', pts:9,  pj:10, v:3, e:0, d:7, form:['D','D','D','V','D']},
    ],
    players:[
      {name:'Mariana Costa',   pos:'Levantadora',   num:1,  captain:true, color:'#f59e0b'},
      {name:'Tatiane Melo',    pos:'Oposta',         num:4,  captain:false,color:'#1e5671'},
      {name:'Fernanda Cruz',   pos:'Ponteira',       num:7,  captain:false,color:'#7c3aed'},
      {name:'Roberta Neto',    pos:'Central',        num:10, captain:false,color:'#16a34a'},
      {name:'Giovana Pinto',   pos:'Libero',         num:6,  captain:false,color:'#ff4d12'},
      {name:'Aline Ramos',     pos:'Ponteira',       num:9,  captain:false,color:'#0891b2'},
      {name:'Cláudia Dias',    pos:'Central',        num:11, captain:false,color:'#ca8a04'},
      {name:'Marina Souza',    pos:'Oposta',         num:3,  captain:false,color:'#dc2626'},
    ]
  }
};

const SPORT_PICKER = [
  {key:'futebol', label:'Futebol', emoji:'⚽', color:'#16a34a'},
  {key:'basquete',label:'Basquete',emoji:'🏀', color:'#dc2626'},
  {key:'volei',   label:'Vôlei',   emoji:'🏐', color:'#0891b2'},
];

let currentSport = 'futebol';
let currentPage  = 'overview';

/* ──────────── LOADER ──────────── */
function esconderLoader() {
  const l = document.getElementById('loader');
  if (l) l.classList.add('hide');
}
window.addEventListener('load', ()=> setTimeout(esconderLoader,1500));
document.addEventListener('DOMContentLoaded', ()=> setTimeout(esconderLoader,1600));
setTimeout(esconderLoader, 3000);

/* ──────────── CURSOR ──────────── */
const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
let mouseX=0, mouseY=0, ringX=0, ringY=0;
document.addEventListener('mousemove', e => {
  mouseX = e.clientX; mouseY = e.clientY;
  dot.style.left = mouseX+'px'; dot.style.top = mouseY+'px';
});
function animateRing(){
  ringX += (mouseX - ringX)*0.12;
  ringY += (mouseY - ringY)*0.12;
  ring.style.left = ringX+'px'; ring.style.top = ringY+'px';
  requestAnimationFrame(animateRing);
}
animateRing();

/* ──────────── THEME ──────────── */
const toggleTheme = document.getElementById('toggle-theme');
function setTheme(t){
  document.documentElement.setAttribute('data-theme',t);
  localStorage.setItem('theme',t);
  toggleTheme.querySelector('i').className = t==='dark'?'fa-solid fa-sun':'fa-solid fa-moon';
}
setTheme(localStorage.getItem('theme')||'light');
toggleTheme.addEventListener('click',()=>{
  const cur = document.documentElement.getAttribute('data-theme');
  setTheme(cur==='dark'?'light':'dark');
});

/* ──────────── REVEAL ──────────── */
function setupReveal(){
  const reveals = document.querySelectorAll('.reveal:not(.visible)');
  const obs = new IntersectionObserver(entries=>{
    entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); }});
  },{threshold:0.08});
  reveals.forEach(el=>obs.observe(el));
}

/* ──────────── NAVIGATE ──────────── */
function navigate(page, el){
  currentPage = page;
  document.querySelectorAll('.page-view').forEach(p=>p.classList.remove('active'));
  document.getElementById('page-'+page).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  if(el) el.classList.add('active');
  else {
    document.querySelectorAll('.nav-item').forEach(n=>{ if(n.dataset.page===page) n.classList.add('active'); });
  }
  const titles = {overview:'Visão <span>Geral</span>',times:'Times',classificacao:'Classificação',meutime:'Meu Time',perfil:'Perfil'};
  document.getElementById('pageTitle').innerHTML = titles[page]||page;
  setTimeout(setupReveal, 50);
}

/* ──────────── RENDER ──────────── */
function renderDashboard(){
  const sp = SPORTS[currentSport];

  // labels
  document.getElementById('sportName').textContent = sp.name;
  document.getElementById('sportIcon').innerHTML = `<i class="${sp.icon}"></i>`;
  ['sportTagOverview','sportTagTimes','sportTagClass'].forEach(id=>{
    document.getElementById(id).textContent = sp.name;
  });
  document.getElementById('sc3Label').textContent = sp.scoreStat;

  // hero
  const myTeam = sp.teams.find(t=>t.myTeam) || sp.teams[0];
  document.getElementById('heroName').textContent = 'Mariana';
  document.getElementById('heroSub').textContent  = `${sp.name} • ${myTeam.name}`;
  const sorted = [...sp.teams].sort((a,b)=>b.pts-a.pts);
  const pos = sorted.indexOf(myTeam)+1;
  document.getElementById('heroRank').textContent  = pos+'°';
  document.getElementById('heroGames').textContent = myTeam.pj;
  document.getElementById('heroPoints').textContent= myTeam.pts;
  document.getElementById('sc1').textContent = myTeam.v;
  document.getElementById('sc2').textContent = myTeam.e;
  document.getElementById('sc3').textContent = 24;

  // teams grid (overview — show 4)
  renderTeamsGrid(document.getElementById('teamsGridOverview'), sorted.slice(0,4));
  renderTeamsGrid(document.getElementById('teamsGridFull'), sorted);

  // ranking
  renderRanking(sorted);

  // my team
  renderMyTeam(myTeam, pos, sp);

  setTimeout(setupReveal, 80);
}

function renderTeamsGrid(container, teams){
  container.innerHTML = teams.map(t=>`
    <div class="team-card ${t.myTeam?'my-team':''} reveal" onclick="navigate('meutime',null)">
      ${t.myTeam?'<div class="my-team-badge">Meu Time</div>':''}
      <div class="team-logo">${t.emoji}</div>
      <div class="team-name">${t.name}</div>
      <div class="team-meta">${t.pj} jogos disputados</div>
      <div class="team-points-badge">${t.pts} pts</div>
    </div>`).join('');
}

function renderRanking(sorted){
  const medals = ['gold','silver','bronze'];
  const myTeam = SPORTS[currentSport].teams.find(t=>t.myTeam);
  document.getElementById('rankingBody').innerHTML = sorted.map((t,i)=>{
    const cls   = medals[i] || (t.myTeam?'my':'normal');
    const rowCls= t.myTeam?'highlight-row':'';
    const form  = t.form.map(f=>`<span class="form-dot form-${f.toLowerCase()}">${f}</span>`).join('');
    return `<tr class="${rowCls}">
      <td><span class="rank-pos ${cls}">${i+1}</span></td>
      <td><span class="team-row-logo">${t.emoji}</span>${t.name}${t.myTeam?'&nbsp;<span style="font-size:0.7rem;color:var(--laranja-destaque);font-weight:700">(Você)</span>':''}</td>
      <td>${t.pj}</td><td>${t.v}</td><td>${t.e}</td><td>${t.d}</td>
      <td><span class="rank-pts">${t.pts}</span></td>
      <td><div class="rank-form">${form}</div></td>
    </tr>`;
  }).join('');
}

function renderMyTeam(myTeam, pos, sp){
  document.getElementById('myTeamBigLogo').textContent = myTeam.emoji;
  document.getElementById('myTeamName').textContent    = myTeam.name;
  document.getElementById('myTeamSport').textContent   = `${sp.name} • Campeonato Regional`;
  document.getElementById('mtPos').textContent         = pos+'°';
  document.getElementById('mtPts').textContent         = myTeam.pts;
  document.getElementById('mtPlayers').textContent     = sp.players.length;

  document.getElementById('playersGrid').innerHTML = sp.players.map(p=>`
    <div class="player-card reveal">
      <div class="player-avatar" style="background:${p.color}">${p.name.split(' ').map(n=>n[0]).join('').slice(0,2)}</div>
      <div class="player-name">${p.name}</div>
      <div class="player-pos">${p.pos}</div>
      <div class="player-num">#${p.num}</div>
      ${p.captain?'<div class="captain-badge"><i class="fa-solid fa-star"></i> Capitã</div>':''}
    </div>`).join('');
}

/* ──────────── SPORT PICKER ──────────── */
function openSportPicker(){
  const modal = document.getElementById('sportModal');
  modal.style.display='flex';
  document.getElementById('sportPickerGrid').innerHTML = SPORT_PICKER.map(s=>`
    <button onclick="selectSport('${s.key}')" style="
      background:${s.key===currentSport?'rgba(255,77,18,0.1)':'var(--fundo-pagina)'};
      border:1.5px solid ${s.key===currentSport?'var(--laranja-destaque)':'var(--borda-sutil)'};
      border-radius:var(--raio-medio); padding:20px 16px; cursor:pointer;
      display:flex; flex-direction:column; align-items:center; gap:8px;
      transition:all 0.3s; font-family:'DM Sans',sans-serif; color:var(--texto-principal);
    ">
      <span style="font-size:2rem">${s.emoji}</span>
      <span style="font-weight:700;font-size:0.9rem">${s.label}</span>
    </button>`).join('');
}
function closeSportPicker(){
  document.getElementById('sportModal').style.display='none';
}
function selectSport(key){
  currentSport = key;
  closeSportPicker();
  renderDashboard();
  navigate('overview', document.querySelector('[data-page="overview"]'));
}

/* ──────────── INIT ──────────── */
document.addEventListener('DOMContentLoaded', ()=>{
  renderDashboard();
  setupReveal();
});
</script>
</body>
</html>