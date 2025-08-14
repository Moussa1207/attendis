@extends('dashboard.master')

@section('contenu')
<div class="page-wrapper">
    <!-- Top Bar Start -->
    <div class="topbar">
        <nav class="navbar-custom">
            {{-- LEFT: Hamburger --}}
            <ul class="list-unstyled topbar-nav mb-0">
                <li>
                    <button class="nav-link button-menu-mobile">
                        <i data-feather="menu" class="align-self-center topbar-icon"></i>
                    </button>
                </li>
            </ul>

            {{-- RIGHT: User menu --}}
            <ul class="list-unstyled topbar-nav mb-0">
                <li class="dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                       aria-haspopup="false" aria-expanded="false">
                        <span class="ml-1 nav-user-name hidden-sm">{{ Auth::user()->username }}</span>
                        <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" alt="profile-user" class="rounded-circle" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-header">
                            <h6 class="text-dark mb-0">{{ Auth::user()->getTypeName() }}</h6>
                            <small class="text-muted">{{ Auth::user()->email }}</small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('layouts.app-users') }}"><i data-feather="home" class="align-self-center icon-xs icon-dual mr-1"></i> Dashboard</a>
                        <a class="dropdown-item" href="{{ route('layouts.app-users') }}"><i data-feather="activity" class="align-self-center icon-xs icon-dual mr-1"></i> Mon espace</a>
                        <div class="dropdown-divider mb-0"></div>
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Déconnexion
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
    <!-- Top Bar End -->

    <!-- Page Content-->
    <div class="page-content">
        <div class="container-fluid">

            <!-- ✅ 6 rectangles en ligne -->
            <div class="called-clients-grid" id="called-grid" data-api="/api/accueil/called-clients">
                <div class="row no-gutters">
                    @for ($i = 1; $i <= 6; $i++)
                        <div class="col-2 px-1">
                            <div class="client-card" id="client-slot-{{ $i }}" data-slot="{{ $i }}">
                                <div class="client-card-body">
                                    <div class="client-placeholder loading-pulse">
                                        <i data-feather="user-plus" class="placeholder-icon"></i>
                                        <span class="placeholder-text">En attente</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* --------- Base & layout --------- */
body{background:#f8f9fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
.page-wrapper{min-height:100vh}
.page-content{width:100%;padding:20px;margin-top:0;border-top:0!important}
.container-fluid{max-width:1400px;margin:0 auto}

/* ===== Topbar plein largeur, propre ===== */
.topbar{
  background:#fff;
  height:60px;
  padding:0 15px;
  z-index:1040;
  border-bottom:0!important;
  /* trait fin sur toute la largeur */
  box-shadow: inset 0 -1px 0 #e9edf3;
}
.topbar .navbar-custom{
  display:flex;
  align-items:center;
  justify-content:space-between; /* gauche <-> droite */
  height:60px;
  padding:0;
  background:transparent;
  border-bottom:0!important;
  box-shadow:none!important;
}
.topbar .navbar-custom > ul{display:flex;align-items:center;margin:0}
.topbar .button-menu-mobile{
  display:flex;align-items:center;justify-content:center;
  height:60px;line-height:60px;padding:0 12px;
  background:none;border:none;color:#6c757d;font-size:18px;
}
.topbar .nav-user{display:flex;align-items:center;height:60px;padding:0}
.icon-xs{width:16px;height:16px}

/* --------- Grille d'appel --------- */
.called-clients-grid{padding:20px 10px;margin-top:10px}

.client-card{
  background:#fff;border:1px solid #e9ecef;padding:10px;height:120px;
  display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;
  transition:transform .20s ease,border-color .20s ease,box-shadow .20s ease
}
.client-card:hover{transform:translateY(-3px);border-color:#0d6efd;box-shadow:0 6px 18px rgba(13,110,253,.12)}
.client-card.occupied{}
.client-card-body{text-align:center;width:100%}

/* ✅ Case la plus récente (mise en valeur) */
.client-card.active{
  background:#d1fae5;border-color:#34c38f;
  box-shadow:0 0 0 2px rgba(52,195,143,.18) inset, 0 8px 20px rgba(52,195,143,.12);
}

/* ✅ Affichage du code uniquement */
.client-ticket{
  font-size:32px;font-weight:900;color:#0d6efd;letter-spacing:.5px;font-family:'Courier New',monospace;
}

/* Placeholder */
.client-placeholder{opacity:.8;color:#6c757d}
.placeholder-icon{width:24px;height:24px;color:#adb5bd;margin-bottom:6px}
.placeholder-text{font-size:10px;font-weight:500;color:#6c757d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* Animations soft */
@keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.client-card.soft-swap{animation:softFlash 800ms ease}
.client-card.soft-ink{opacity:.85}
@keyframes softFlash{0%{background:#fffbe6}100%{background:#fff}}

.loading-pulse{animation:pulse 1.2s ease-in-out infinite}
@keyframes pulse{0%{opacity:1}50%{opacity:.55}100%{opacity:1}}

/* Divers */
.footer,.breadcrumb,.page-title-box{display:none!important}

/* Responsive */
@media (max-width:1200px){.client-card{height:110px}}
@media (max-width:992px){.client-card{height:100px}}
@media (max-width:768px){
  .client-card{height:90px}
  .client-ticket{font-size:24px}
}
</style>

<script>
/* ================== CONFIG ================== */
const SLOTS = 6;
const STORAGE = {
  slots : 'accueil.v5.slots',
  lastO : 'accueil.v5.lastOrder',
  lastR : 'accueil.v5.lastRang',
  day   : 'accueil.v5.day'
};

// URL API (peut être surchargée via data-api)
const gridEl = document.getElementById('called-grid');
const API_CALLED = (gridEl && gridEl.dataset.api) ? gridEl.dataset.api : '/api/accueil/called-clients';

/* ================== STATE ================== */
let slots = new Array(SLOTS).fill(null);
let lastSeenOrder = 0;
let lastSeenRang  = 0;
let pollTimer = null;

/* ================== UTILS ================== */
function truncateText(t,n){if(!t)return'';return t.length<=n?t:(t.substring(0,n-1)+'…')}
function todayStr(){return new Date().toISOString().slice(0,10)}
function parseTime(t){if(!t)return-1;const p=t.split(':').map(Number);if(p.length<2||p.some(isNaN))return-1;const[h,m,s=0]=p;return h*3600+m*60+s}
function callOrder(item){const t=parseTime(item.called_at||item.heure_prise_en_charge);const id=Number(item.id)||0;return (t>=0?t*1000000:0)+id}
function normalize(it){
  const rangRaw = it.rang ?? it.rank ?? it.call_rank ?? it.position_file ?? null;
  const rang = rangRaw !== null ? parseInt(rangRaw,10) : null;
  return {
    id: it.id,
    ticket_number: it.ticket_number || it.numero_ticket || '—',
    called_at: it.called_at || it.heure_prise_en_charge || '--:--',
    advisor_name: it.advisor_name || it.conseiller_name || it.conseiller || it.conseiller_client_username || '—',
    rang: Number.isFinite(rang)? rang : null,
    _order: callOrder(it)
  };
}

/* ================== PERSISTENCE ================== */
function loadState(){
  try{
    const savedDay = localStorage.getItem(STORAGE.day);
    if(savedDay && savedDay !== todayStr()){
      localStorage.removeItem(STORAGE.slots);
      localStorage.removeItem(STORAGE.lastO);
      localStorage.removeItem(STORAGE.lastR);
    }
    localStorage.setItem(STORAGE.day, todayStr());
    const s = JSON.parse(localStorage.getItem(STORAGE.slots) || '[]');
    if(Array.isArray(s) && s.length===SLOTS) slots = s;
    lastSeenOrder = parseInt(localStorage.getItem(STORAGE.lastO) || '0', 10);
    lastSeenRang  = parseInt(localStorage.getItem(STORAGE.lastR) || '0', 10);
  }catch(_){}
}
function saveState(){
  try{
    localStorage.setItem(STORAGE.slots, JSON.stringify(slots));
    localStorage.setItem(STORAGE.lastO, String(lastSeenOrder));
    localStorage.setItem(STORAGE.lastR, String(lastSeenRang));
    localStorage.setItem(STORAGE.day, todayStr());
  }catch(_){}
}

/* ================== RENDU ================== */
function ensureSkeleton(slotEl){
  if(slotEl.dataset.ready === '1') return;
  slotEl.innerHTML = `
    <div class="client-card-body">
      <div class="client-info">
        <div class="client-ticket"></div>
      </div>
    </div>`;
  slotEl.dataset.ready = '1';
  if(typeof feather!=='undefined') feather.replace();
}
function resetPlaceholder(slotEl){
  delete slotEl.dataset.id;
  slotEl.className = 'client-card';
  slotEl.innerHTML = `
    <div class="client-card-body">
      <div class="client-placeholder">
        <i data-feather="user-plus" class="placeholder-icon"></i>
        <span class="placeholder-text">En attente</span>
      </div>
    </div>`;
  if(typeof feather!=='undefined') feather.replace();
}
function renderSlot(index, soft=false){
  const el = document.getElementById(`client-slot-${index+1}`);
  if(!el) return;
  const data = slots[index];
  if(!data){ resetPlaceholder(el); return; }

  const changedId = (el.dataset.id !== String(data.id));
  if(changedId){
    el.className = 'client-card occupied';
    ensureSkeleton(el);
    el.dataset.id = String(data.id);
    el.classList.add('soft-swap'); setTimeout(()=>el.classList.remove('soft-swap'), 800);
  }else if(soft){
    el.classList.add('soft-ink'); setTimeout(()=>el.classList.remove('soft-ink'), 300);
  }

  const ticket = el.querySelector('.client-ticket');
  if(ticket) ticket.textContent = data.ticket_number;
}
function mostRecentIndex(){
  for(let i=SLOTS-1;i>=0;i--) if(slots[i]) return i;
  return -1;
}
function highlightMostRecent(){
  const last = mostRecentIndex();
  for(let i=0;i<SLOTS;i++){
    const el = document.getElementById(`client-slot-${i+1}`);
    if(!el) continue;
    if(i === last && slots[i]) el.classList.add('active'); else el.classList.remove('active');
  }
}
function renderAll(){
  for(let i=0;i<SLOTS;i++) renderSlot(i);
  highlightMostRecent();
}

/* ================== LOGIQUE DE GLISSEMENT ================== */
function indexOfId(id){
  for(let i=0;i<SLOTS;i++){ if(slots[i] && String(slots[i].id)===String(id)) return i; }
  return -1;
}
function placeNew(item){
  const firstEmpty = slots.findIndex(x => !x);
  if (firstEmpty !== -1) {
    slots[firstEmpty] = item;
  } else {
    slots = slots.slice(1).concat(item);
  }
}

/* ================== MERGE DOUX (rang prioritaire) ================== */
function mergeCalls(list){
  if(!Array.isArray(list) || !list.length) return;

  list.sort((a,b)=>{
    const ar = (a.rang ?? null), br = (b.rang ?? null);
    if(Number.isFinite(ar) && Number.isFinite(br)) return ar - br;
    return callOrder(a) - callOrder(b);
  });

  const toAdd = [];
  list.forEach(raw=>{
    const it = normalize(raw);

    const pos = indexOfId(it.id);
    if(pos >= 0){
      const prev = slots[pos];
      const changed = (prev.ticket_number !== it.ticket_number);
      if(changed){ slots[pos] = it; renderSlot(pos, true); saveState(); }
      if(it._order > lastSeenOrder) lastSeenOrder = it._order;
      if(Number.isFinite(it.rang) && it.rang > lastSeenRang) lastSeenRang = it.rang;
      return;
    }

    if(Number.isFinite(it.rang)){
      if(it.rang > lastSeenRang) toAdd.push(it);
    }else{
      if((it._order || 0) > lastSeenOrder) toAdd.push(it);
    }
  });

  if(!toAdd.length) return;

  toAdd.forEach(it=>{
    placeNew(it);
    if(it._order > lastSeenOrder) lastSeenOrder = it._order;
    if(Number.isFinite(it.rang) && it.rang > lastSeenRang) lastSeenRang = it.rang;
  });

  renderAll();
  saveState();
}

/* ================== POLLING DOUX ================== */
function scheduleNextPoll(){
  const delay = 3500 + Math.floor(Math.random()*2500); // 3.5s → 6s
  pollTimer = setTimeout(fetchCalled, delay);
}
function fetchCalled(){
  if (typeof $ === 'undefined') { scheduleNextPoll(); return; }
  $.ajax({
    url: API_CALLED, method: 'GET', dataType: 'json', cache: false, timeout: 10000
  }).done(res=>{
    const data = res?.data ?? res?.tickets ?? (Array.isArray(res) ? res : []);
    if(Array.isArray(data)) mergeCalls(data);
  }).always(()=>{ scheduleNextPoll(); });
}

/* ================== INIT ================== */
document.addEventListener('DOMContentLoaded', ()=>{
  if(typeof $!=='undefined'){
    const meta = document.querySelector('meta[name="csrf-token"]');
    if(meta) $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': meta.content }});
  }
  if(typeof feather!=='undefined') feather.replace();

  loadState();
  renderAll();
  fetchCalled();
  window.addEventListener('focus', fetchCalled);
});
window.addEventListener('beforeunload', ()=>{ if(pollTimer) clearTimeout(pollTimer); });
</script>
@endsection
