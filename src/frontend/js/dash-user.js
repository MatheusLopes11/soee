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
