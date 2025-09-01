@extends('dashboard.master')

@section('title', 'Historique des Tickets')

@section('contenu')
<div class="page-wrapper stats-scope">
  <!-- Top Bar -->
  <div class="topbar">
    <nav class="navbar-custom">
      <ul class="list-unstyled topbar-nav float-right mb-0">
        <li class="dropdown">
          <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#">
            <span class="ml-1 nav-user-name hidden-sm">{{ Auth::user()->username }}</span>
            <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" alt="profile-user" class="rounded-circle" />
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('layouts.setting') }}">
              <i data-feather="settings" class="icon-xs mr-1"></i> Paramètres
            </a>
            <div class="dropdown-divider mb-0"></div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf
              <button type="submit" class="dropdown-item">
                <i data-feather="power" class="icon-xs mr-1"></i> Déconnexion
              </button>
            </form>
          </div>
        </li>
      </ul>
      <ul class="list-unstyled topbar-nav mb-0">
        <li>
          <button class="nav-link button-menu-mobile">
            <i data-feather="menu" class="topbar-icon"></i>
          </button>
        </li>
      </ul>
    </nav>
  </div>
  <!-- /Top Bar -->

  <div class="page-content">
    <div class="container-fluid">
      <!-- Titre -->
      <div class="row">
        <div class="col-sm-12">
          <div class="page-title-box">
            <div class="row">
              <div class="col">
                <h4 class="page-title">Historiques</h4>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                  <li class="breadcrumb-item active">Historique des Tickets</li>
                </ol>
              </div>
              <div class="col-auto align-self-center">
                <a href="#" class="btn btn-sm btn-outline-primary" onclick="histLoad(1,true)">
                  <i data-feather="refresh-cw" class="icon-xs"></i>
                </a>
                <a href="#" class="btn btn-sm btn-outline-success" onclick="histExport()">
                  <i data-feather="download" class="icon-xs"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ====== Tableau Historique ====== -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card h-100">
            <div class="card-header header-flat d-flex justify-content-between align-items-center">
              <h4 class="card-title mb-0"><i data-feather="clock" class="mr-2"></i>Tickets</h4>
            </div>

            <div class="card-body">
              <form id="histFilterForm" class="mb-3">
                <div class="form-row">
                  <div class="form-group col-md-2">
                    <label class="mb-1">Du</label>
                    <input type="date" name="date_from" class="form-control no-radius">
                  </div>
                  <div class="form-group col-md-2">
                    <label class="mb-1">Au</label>
                    <input type="date" name="date_to" class="form-control no-radius">
                  </div>

                  <div class="form-group col-md-2">
                    <label class="mb-1">Statut</label>
                    <select name="status" class="form-control no-radius">
                      <option value="">Tous</option>
                      <option value="termine">Traité</option>
                      <option value="refuse">Refusé</option>
                      <option value="transfere">Transféré</option>
                      <option value="en_cours">En cours</option>
                      <option value="en_attente">En attente</option>
                    </select>
                  </div>

                  <div class="form-group col-md-2">
                    <label class="mb-1">Résolu</label>
                    <select name="resolu" class="form-control no-radius">
                      <option value="">Tous</option>
                      <option value="1">Oui</option>
                      <option value="0">Non</option>
                    </select>
                  </div>

                  <div class="form-group col-md-2">
                    <label class="mb-1">Service</label>
                    <select name="service_id" class="form-control no-radius">
                      <option value="">Tous</option>
                    </select>
                  </div>
                  <div class="form-group col-md-2">
                    <label class="mb-1">Agence</label>
                    <select name="agency_id" class="form-control no-radius">
                      <option value="">Toutes</option>
                    </select>
                  </div>
                </div>

                <div class="form-row align-items-end">
                  <div class="form-group col-md-6">
                    <label class="mb-1">Recherche</label>
                    <input type="text" name="search" class="form-control no-radius" placeholder="Code ticket, client, conseiller...">
                  </div>
                  <div class="form-group col-md-6 text-right">
                    <button type="button" class="btn btn-primary no-radius mr-2" onclick="histLoad(1,true)">
                      <i data-feather="filter" class="icon-xs mr-1"></i>Filtrer
                    </button>
                    <button type="button" class="btn btn-outline-secondary no-radius" onclick="histReset()">
                      <i data-feather="x-circle" class="icon-xs mr-1"></i>Réinitialiser
                    </button>
                  </div>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table table-flat mb-0" id="histTable">
                  <thead class="thead-light">
                    <tr>
                      <th>Pris en charge</th>
                      <th>Agence</th>
                      <th>Service</th>
                      <th>Conseiller</th>
                      <th>Statut</th>
                      <th>Code</th>
                      <th>Résolu</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted" id="histMeta">—</small>
                <div>
                  <button class="btn btn-sm btn-outline-secondary no-radius" onclick="histPage('prev')">Préc.</button>
                  <button class="btn btn-sm btn-outline-secondary no-radius" onclick="histPage('next')">Suiv.</button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>

    <footer class="footer text-center text-sm-left">
      &copy; {{ date('Y') }} Attendis
    </footer>
  </div>
</div>

<!-- ====== MODAL DÉTAILS TICKET - TAILLE RÉDUITE ====== -->
<div class="modal fade" id="ticketDetailsModal" tabindex="-1" role="dialog" aria-labelledby="ticketDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-gradient-primary text-white">
        <h4 class="modal-title" id="ticketDetailsModalLabel">
          <i data-feather="file-text" class="mr-2"></i>Détails du Ticket
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="ticketDetailsContent">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Chargement...</span>
          </div>
          <p class="mt-2 text-muted">Récupération des détails...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i data-feather="x" class="icon-xs mr-1"></i>Fermer
        </button>
        <button type="button" class="btn btn-primary" onclick="printTicketDetails()">
          <i data-feather="printer" class="icon-xs mr-1"></i>Imprimer
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// =================== ROUTES ===================
window.APP = {
  routes: {
    historyIndex: "{{ route('layouts.history') }}",
    historyApi:   "{{ route('history.tickets') }}",          // JSON (liste & filtres)
    historyExport:"{{ route('history.tickets.export') }}",   // CSV
    historyDetails: "{{ route('history.ticket.details', ['ticketId' => 'TICKET_ID']) }}"
  }
};

// =================== UTILS ===================
async function histFetch(url){
  const r = await fetch(url, {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'}});
  if(!r.ok) throw new Error('HTTP '+r.status);
  return r.json();
}

function esc(s){
  if(s==null) return '';
  return String(s).replace(/[&<>"'`=\/]/g, m=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
  }[m]));
}

// =================== DATES / STATUT ===================
function fmtPriseEnCharge(t){
  if (t?.prise_en_charge_at) return t.prise_en_charge_at;

  const fallbackOrder = ['treated_at', 'shared_at', 'transferred_at', 'created_at'];
  for (const field of fallbackOrder) {
    if (t?.[field]) {
      if (typeof t[field] === 'string' && /\d{2}\/\d{2}\/\d{4}/.test(t[field])) return t[field];
      try {
        const d = new Date(t[field]);
        if (!isNaN(d.getTime())) {
          return d.toLocaleDateString('fr-FR', {
            day:'2-digit', month:'2-digit', year:'numeric',
            hour:'2-digit', minute:'2-digit'
          });
        }
      } catch(e){}
    }
  }

  const status = normalizeStatus(t);
  switch(status.key) {
    case 'en_cours':
      return '<span class="text-info"><i data-feather="clock" class="icon-xs mr-1"></i>En cours</span>';
    case 'en_attente':
      return '<span class="text-muted"><i data-feather="clock" class="icon-xs mr-1"></i>En attente</span>';
    case 'termine':
    case 'refuse':
    case 'transfere':
      return '<span class="text-warning"><i data-feather="help-circle" class="icon-xs mr-1"></i>Heure inconnue</span>';
    default:
      return '<span class="text-muted"><i data-feather="clock" class="icon-xs mr-1"></i>En attente</span>';
  }
}

function normalizeStatus(t){
  if (t.refused_at)     return { label:'Refusé', key:'refuse',    icon:'x-circle',    cls:'badge-status-refused' };
  if (t.shared_at || t.transferred_at) 
                        return { label:'Transféré', key:'transfere', icon:'send',     cls:'badge-status-transferred' };
  if (t.treated_at)     return { label:'Traité', key:'termine',   icon:'check-circle', cls:'badge-status-treated' };

  const sg = (t.statut_global || '').toLowerCase();
  switch(sg){
    case 'refuse':     return { label:'Refusé',   key:'refuse',    icon:'x-circle',     cls:'badge-status-refused' };
    case 'transfere':  return { label:'Transféré',key:'transfere', icon:'send',         cls:'badge-status-transferred' };
    case 'termine':    return { label:'Traité',   key:'termine',   icon:'check-circle', cls:'badge-status-treated' };
    case 'en_cours':   return { label:'En cours', key:'en_cours',  icon:'clock',        cls:'badge-status-progress' };
    case 'en_attente':
    default:           return { label:'En attente', key:'en_attente', icon:'clock',     cls:'badge-secondary' };
  }
}

// =================== ÉTAT ===================
let histState = { page:1, last:1, total:0, per:25 };

function histQuery(page=1){
  const f = document.getElementById('histFilterForm');
  const p = new URLSearchParams();
  p.set('page', page);
  p.set('per_page', histState.per);
  ['date_from','date_to','status','service_id','agency_id','search','resolu'].forEach(n=>{
    const v = f.elements[n]?.value?.trim();
    if (v) p.set(n, v);
  });
  return p.toString();
}

// =================== RENDU ===================
function clearAndFillSelect(sel, items, mapLabel='name', mapValue='id'){
  if (!sel) return;
  const first = sel.options[0];
  sel.innerHTML = '';
  if (first) sel.add(first);
  if (Array.isArray(items)){
    const seen = new Set();
    items.forEach(item=>{
      const v = item[mapValue];
      if (v!=null && !seen.has(v)){
        seen.add(v);
        sel.add(new Option(item[mapLabel], v));
      }
    });
  }
}
function histRenderOptions(f){
  clearAndFillSelect(document.querySelector('#histFilterForm select[name="service_id"]'), f.services||[]);
  clearAndFillSelect(document.querySelector('#histFilterForm select[name="agency_id"]'),  f.agencies||[]);
}

function histRenderRows(rows){
  const tb = document.querySelector('#histTable tbody');
  tb.innerHTML = '';

  if (!rows.length){
    tb.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">
      <i data-feather="inbox" class="mr-1"></i>Aucun ticket
    </td></tr>`;
    return;
  }

  rows.forEach((t, index) => {
    if (index < 1 || (typeof window !== 'undefined' && window.location.search.includes('debug=1'))) {
      console.group(`🔍 Ticket ${t?.code || t?.id}`);
      console.log({ prise_en_charge_at:t?.prise_en_charge_at, statut:t?.statut_global, resolu:t?.resolu, shared_at:t?.shared_at, treated_at:t?.treated_at });
      console.groupEnd();
    }

    let agencyName = '—';
    if (t.advisor?.agency?.name)      agencyName = t.advisor.agency.name;
    else if (t.agency?.name)          agencyName = t.agency.name;

    const datePriseEnCharge = fmtPriseEnCharge(t);
    const status = normalizeStatus(t);

    // Résolution : utilise t.resolu si présent, sinon déduit du statut
    let resoluVal = (typeof t.resolu !== 'undefined') ? Number(t.resolu) : null;
    if (resoluVal === null) {
      if (t.refused_at) resoluVal = 0;
      else if (t.treated_at && !t.refused_at) resoluVal = 1;
    }
    const resolutionBadge = resoluVal === 1
      ? '<span class="badge badge-success badge-sm">Oui</span>'
      : resoluVal === 0
        ? '<span class="badge badge-secondary badge-sm">Non</span>'
        : '<span class="text-muted">—</span>';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="prise-en-charge-cell">${datePriseEnCharge}</td>
      <td>
        ${agencyName !== '—'
          ? `<span class="badge badge-light-info px-2 py-1">
               <i data-feather="home" class="icon-xs mr-1"></i>${esc(agencyName)}
             </span>`
          : '<span class="text-muted">—</span>'
        }
      </td>
      <td>${esc(t.service?.name || '—')}</td>
      <td>${esc(t.advisor?.username || t.conseiller_nom || '—')}</td>
      <td>
        <span class="badge badge-pill ${status.cls}" title="${esc(status.key)}">
          <i data-feather="${status.icon}" class="icon-xs mr-1"></i>${status.label}
        </span>
      </td>
      <td><strong>${esc(t.code || ('#'+t.id))}</strong></td>
      <td>${resolutionBadge}</td>
      <td>
        <button class="btn btn-sm btn-outline-info" onclick="showTicketDetails(${t.id})" title="Voir les détails">
          <i data-feather="eye" class="icon-xs"></i>
        </button>
      </td>
    `;

    // Highlight "récemment pris en charge" (<= 5 min)
    if (t?.prise_en_charge_at && !/En (cours|attente)/.test(t.prise_en_charge_at)) {
      try {
        const [d,m,yTime] = t.prise_en_charge_at.split('/');
        const [y,time] = (yTime||'').split(' ');
        const djs = new Date(`${y}-${m}-${d}T${time||'00:00'}:00`);
        const now = new Date();
        const diffMinutes = (now - djs) / (1000 * 60);
        if (diffMinutes <= 5) tr.classList.add('recent-takeup');
      } catch(e){}
    }

    tb.appendChild(tr);
  });
}

function histRenderMeta(m){
  histState.page  = m.current_page || 1;
  histState.last  = m.last_page    || 1;
  histState.total = m.total        || 0;
  histState.per   = m.per_page     || 25;

  const first = (histState.page-1)*histState.per + 1;
  const last  = Math.min(histState.page*histState.per, histState.total);
  document.getElementById('histMeta').textContent =
    histState.total ? `Affichage de ${first} à ${last} sur ${histState.total}` : '—';
}

// =================== NAVIGATION / ACTIONS ===================
async function histLoad(page=1, toast=false){
  try{
    const data = await histFetch(`${APP.routes.historyApi}?${histQuery(page)}`);
    histRenderOptions(data.filters||{});
    histRenderRows(data.data||[]);
    histRenderMeta(data.meta||{});
    if (toast) histToast('Résultats mis à jour.','success');
  }catch(e){
    histToast('Erreur de chargement.','danger');
    console.error('❌ Erreur chargement historique:', e);
  }finally{
    if (window.feather?.replace) feather.replace();
  }
}
function histPage(dir){
  let p = histState.page;
  if (dir==='prev') p = Math.max(1, p-1);
  if (dir==='next') p = Math.min(histState.last, p+1);
  if (p!==histState.page) histLoad(p,false);
}
function histReset(){
  document.getElementById('histFilterForm').reset();
  histLoad(1,true);
}
function histExport(){
  window.open(`${APP.routes.historyExport}?${histQuery(histState.page)}`, '_blank');
}
function histToast(message, type='info'){
  const el = document.createElement('div');
  el.className = `alert alert-${type} alert-dismissible fade show`;
  el.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;min-width:300px';
  el.innerHTML = `${message}<button type="button" class="close" onclick="this.parentElement.remove()"><span>&times;</span></button>`;
  document.body.appendChild(el);
  setTimeout(()=>el.remove(),2500);
}

// =================== MODAL DÉTAILS TICKET ===================
async function showTicketDetails(ticketId) {
  try {
    $('#ticketDetailsModal').modal('show');
    document.getElementById('ticketDetailsContent').innerHTML = `
      <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status"><span class="sr-only">Chargement...</span></div>
        <p class="mt-2 text-muted">Récupération des détails...</p>
      </div>
    `;

    const url = APP.routes.historyDetails.replace('TICKET_ID', encodeURIComponent(ticketId));
    const response = await histFetch(url);

    if (!response || response.success === false) {
      throw new Error(response?.message || 'Erreur lors de la récupération des détails');
    }

    renderTicketDetailsModal(response);
  } catch (error) {
    console.error('Erreur récupération détails ticket:', error);
    document.getElementById('ticketDetailsContent').innerHTML = `
      <div class="alert alert-danger">
        <i data-feather="alert-circle" class="mr-2"></i>
        <strong>Erreur :</strong> ${esc(error.message || 'Impossible de charger les détails.')}
      </div>
    `;
    if (window.feather?.replace) feather.replace();
  }
}

function renderTicketDetailsModal(data) {
  const { ticket = {}, timeline = [], durations = {}, collaborative_info = { was_transferred:false } } = data;

  const content = `
    <!-- En-tête ticket compact -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card border-primary">
          <div class="card-header bg-light border-primary">
            <h6 class="mb-0">
              <i data-feather="hash" class="mr-2 text-primary"></i>
              Ticket ${esc(ticket.numero_ticket || ticket.code || ('#'+(ticket.id||'')))}
            </h6>
          </div>
          <div class="card-body py-2">
            <div class="row">
              <div class="col-sm-6">
                <small class="text-muted d-block">Client</small>
                <strong>${esc(ticket?.client_info?.prenom || ticket?.client_name || 'N/A')}</strong>
                <br><small class="text-muted">${esc(ticket?.client_info?.telephone || ticket?.telephone || 'N/A')}</small>
              </div>
              <div class="col-sm-6">
                <small class="text-muted d-block">Service</small>
                <span class="badge badge-primary">
                  ${esc(ticket?.service?.letter || ticket?.service?.letter_of_service || '')}
                  ${ticket?.service?.letter || ticket?.service?.letter_of_service ? '-' : ''} 
                  ${esc(ticket?.service?.nom || ticket?.service?.name || 'N/A')}
                </span>
              </div>
            </div>
            ${ticket?.client_info?.commentaire_initial || ticket?.commentaire ? `
            <div class="row mt-2">
              <div class="col-12">
                <small class="text-muted d-block">Commentaire initial</small>
                <p class="mb-0 small text-muted">${esc(ticket?.client_info?.commentaire_initial || ticket?.commentaire)}</p>
              </div>
            </div>` : ''}
          </div>
        </div>
      </div>
    </div>

    <!-- Statut & Durées compacts -->
    <div class="row mb-3">
      <div class="col-sm-6">
        <div class="card border-${getStatusColor(ticket?.statut?.statut_global || ticket?.statut_global || 'en_attente')}">
          <div class="card-header bg-${getStatusColor(ticket?.statut?.statut_global || ticket?.statut_global || 'en_attente')} text-white py-2">
            <h6 class="mb-0"><i data-feather="${getStatusIcon(ticket?.statut?.statut_global || ticket?.statut_global || 'en_attente')}" class="mr-2"></i>Statut</h6>
          </div>
          <div class="card-body py-2">
            <p class="mb-1">
              <span class="badge badge-${getStatusColor(ticket?.statut?.statut_global || ticket?.statut_global || 'en_attente')}">
                ${esc(ticket?.statut?.statut_libelle || ticket?.statut_libelle || 'En attente')}
              </span>
            </p>
            <p class="mb-1">
              <strong>Résolu :</strong> 
              <span class="badge badge-${Number(ticket?.statut?.resolu ?? ticket?.resolu) === 1 ? 'success' : 'secondary'}">
                ${esc(ticket?.statut?.resolu_libelle || (Number(ticket?.statut?.resolu ?? ticket?.resolu) === 1 ? 'Oui' : 'Non'))}
              </span>
            </p>
            ${ticket?.statut?.commentaire_resolution || ticket?.commentaire_resolution ? `
            <small class="text-muted d-block">Commentaire</small>
            <p class="mb-0 small">${esc(ticket?.statut?.commentaire_resolution || ticket?.commentaire_resolution)}</p>` : ''}
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="card border-info">
          <div class="card-header bg-info text-white py-2">
            <h6 class="mb-0"><i data-feather="clock" class="mr-2"></i>Durées</h6>
          </div>
          <div class="card-body py-2">
            <div class="small">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Attente:</span>
                <strong class="text-info">${esc(durations?.temps_attente || 'N/A')}</strong>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Traitement:</span>
                <strong class="text-info">${esc(durations?.temps_traitement || 'N/A')}</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-muted font-weight-bold">Total:</span>
                <strong class="text-primary">${esc(durations?.temps_total || 'N/A')}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline compacte -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header py-2">
            <h6 class="mb-0"><i data-feather="clock" class="mr-2"></i>Chronologie</h6>
          </div>
          <div class="card-body py-2">
            <div class="timeline-container-compact">${renderTimelineCompact(timeline)}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transfert compact -->
    ${collaborative_info?.was_transferred ? `
    <div class="row">
      <div class="col-12">
        <div class="card border-warning">
          <div class="card-header bg-warning text-dark py-2">
            <h6 class="mb-0"><i data-feather="shuffle" class="mr-2"></i>Transfert</h6>
          </div>
          <div class="card-body py-2">
            <div class="row">
              <div class="col-sm-6">
                <small class="text-muted d-block">Type</small>
                <span class="badge badge-warning">${esc(ticket?.transfer_info?.transfer_status || 'Transféré')}</span>
              </div>
              <div class="col-sm-6">
                <small class="text-muted d-block">Priorité</small>
                <span class="badge badge-${ticket?.transfer_info?.priority_level === 'high' ? 'success' : 'secondary'}">${ticket?.transfer_info?.priority_level === 'high' ? 'Haute' : 'Normal'}</span>
              </div>
            </div>
            ${ticket?.transfer_info?.transfer_reason ? `
            <div class="mt-2">
              <small class="text-muted d-block">Raison</small>
              <p class="mb-0 small">${esc(ticket.transfer_info.transfer_reason)}</p>
            </div>` : ''}
          </div>
        </div>
      </div>
    </div>` : ''}

    <!-- Conseillers compact -->
    ${ticket?.conseillers?.conseiller_principal || ticket?.conseillers?.conseiller_transfert ? `
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header py-2">
            <h6 class="mb-0"><i data-feather="users" class="mr-2"></i>Conseillers</h6>
          </div>
          <div class="card-body py-2">
            <div class="row">
              ${ticket?.conseillers?.conseiller_principal ? `
              <div class="col-sm-6">
                <div class="d-flex align-items-center">
                  <div class="mr-2">
                    <span class="badge badge-success badge-sm"><i data-feather="user" class="icon-xs"></i></span>
                  </div>
                  <div>
                    <small class="text-muted d-block">Principal</small>
                    <strong class="small">${esc(ticket.conseillers.conseiller_principal.username)}</strong>
                  </div>
                </div>
              </div>` : ''}
              ${ticket?.conseillers?.conseiller_transfert ? `
              <div class="col-sm-6">
                <div class="d-flex align-items-center">
                  <div class="mr-2">
                    <span class="badge badge-warning badge-sm"><i data-feather="shuffle" class="icon-xs"></i></span>
                  </div>
                  <div>
                    <small class="text-muted d-block">Transfert</small>
                    <strong class="small">${esc(ticket.conseillers.conseiller_transfert.username)}</strong>
                  </div>
                </div>
              </div>` : ''}
            </div>
          </div>
        </div>
      </div>
    </div>` : ''}
  `;
  document.getElementById('ticketDetailsContent').innerHTML = content;
  if (window.feather?.replace) feather.replace();
}

function renderTimelineCompact(timeline=[]) {
  if (!timeline.length) return '<p class="text-muted small">Aucun événement enregistré</p>';
  return `<div class="timeline-compact">${timeline.map(ev => `
    <div class="timeline-item-compact ${esc(ev.status || 'secondary')}">
      <div class="timeline-dot"></div>
      <div class="timeline-content-compact">
        <div class="d-flex justify-content-between align-items-start">
          <strong class="small">${esc(ev.title || '')}</strong>
          <small class="text-muted ml-2">${esc(ev.timestamp || '')}</small>
        </div>
        ${ev.description ? `<p class="mb-0 small text-muted">${esc(ev.description)}</p>` : ''}
      </div>
    </div>
  `).join('')}</div>`;
}

function getStatusColor(status) {
  switch ((status||'').toLowerCase()) {
    case 'termine': return 'success';
    case 'en_cours': return 'info';
    case 'en_attente': return 'secondary';
    case 'transfere': return 'warning';
    case 'refuse': return 'danger';
    default: return 'secondary';
  }
}
function getStatusIcon(status) {
  switch ((status||'').toLowerCase()) {
    case 'termine': return 'check-circle';
    case 'en_cours': return 'clock';
    case 'en_attente': return 'clock';
    case 'transfere': return 'shuffle';
    case 'refuse': return 'x-circle';
    default: return 'help-circle';
  }
}
function printTicketDetails() {
  const modalContent = document.getElementById('ticketDetailsContent').innerHTML;
  const w = window.open('', '_blank');
  w.document.write(`
    <html>
      <head>
        <title>Détails du Ticket</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; font-size: 14px; }
          .card { border: 1px solid #ddd; margin-bottom: 15px; }
          .card-header { background: #f8f9fa; padding: 8px 12px; font-weight: bold; font-size: 13px; }
          .card-body { padding: 12px; }
          .badge { padding: 2px 6px; border-radius: 10px; font-size: 0.75em; }
          .badge-primary { background: #007bff; color: white; }
          .badge-success { background: #28a745; color: white; }
          .badge-warning { background: #ffc107; color: black; }
          .badge-info { background: #17a2b8; color: white; }
          .badge-secondary { background: #6c757d; color: white; }
          .timeline-compact { font-size: 13px; }
          .timeline-item-compact { margin-bottom: 10px; padding-left: 20px; border-left: 2px solid #ddd; position: relative; }
          .timeline-dot { position: absolute; left: -4px; top: 3px; width: 6px; height: 6px; border-radius: 50%; background: #ddd; }
          .small { font-size: 12px; }
          @media print { .no-print { display: none; } }
        </style>
      </head>
      <body>${modalContent}</body>
    </html>
  `);
  w.document.close();
  w.print();
}

// =================== Auto-refresh ===================
let autoRefreshInterval = null;
function startAutoRefresh() {
  if (autoRefreshInterval) clearInterval(autoRefreshInterval);
  autoRefreshInterval = setInterval(() => histLoad(histState.page, false), 120000);
}
function stopAutoRefresh() {
  if (autoRefreshInterval) { clearInterval(autoRefreshInterval); autoRefreshInterval = null; }
}
document.addEventListener('DOMContentLoaded', () => {
  histLoad(1, false);
  startAutoRefresh();
  document.addEventListener('visibilitychange', () => document.hidden ? stopAutoRefresh() : startAutoRefresh());
});
</script>

<style>
.stats-scope .no-radius{border-radius:0!important}
.stats-scope .table-flat td,.stats-scope .table-flat th{border-top:1px solid #eef2f7}
.stats-scope .thead-light th{background:#f9fafb}

/* Badges de statut */
.badge-status-treated{background:rgba(40,167,69,.1)!important;color:#28a745!important;border:1px solid rgba(40,167,69,.2);padding:3px 8px!important;font-size:.7rem!important;font-weight:500!important;display:inline-flex!important;align-items:center!important;gap:3px!important;border-radius:12px!important}
.badge-status-refused{background:rgba(220,53,69,.1)!important;color:#dc3545!important;border:1px solid rgba(220,53,69,.2);padding:3px 8px!important;font-size:.7rem!important;font-weight:500!important;display:inline-flex!important;align-items:center!important;gap:3px!important;border-radius:12px!important}
.badge-status-transferred{background:rgba(111,66,193,.1)!important;color:#6f42c1!important;border:1px solid rgba(111,66,193,.2);padding:3px 8px!important;font-size:.7rem!important;font-weight:500!important;display:inline-flex!important;align-items:center!important;gap:3px!important;border-radius:12px!important}
.badge-status-progress{background:rgba(23,162,184,.1)!important;color:#17a2b8!important;border:1px solid rgba(23,162,184,.2);padding:3px 8px!important;font-size:.7rem!important;font-weight:500!important;display:inline-flex!important;align-items:center!important;gap:3px!important;border-radius:12px!important}
.badge-secondary{background:rgba(108,117,125,.1)!important;color:#6c757d!important;border:1px solid rgba(108,117,125,.2);padding:3px 8px!important;font-size:.7rem!important;font-weight:500!important;display:inline-flex!important;align-items:center!important;gap:3px!important;border-radius:12px!important}
.badge-status-treated:hover{background:rgba(40,167,69,.15)!important;border-color:rgba(40,167,69,.3)!important;transition:all .2s}
.badge-status-refused:hover{background:rgba(220,53,69,.15)!important;border-color:rgba(220,53,69,.3)!important;transition:all .2s}
.badge-status-transferred:hover{background:rgba(111,66,193,.15)!important;border-color:rgba(111,66,193,.3)!important;transition:all .2s}
.badge-status-progress:hover{background:rgba(23,162,184,.15)!important;border-color:rgba(23,162,184,.3)!important;transition:all .2s}

/* Badge agence */
.badge-light-info{background:rgba(23,162,184,.08)!important;color:#17a2b8!important;border:1px solid rgba(23,162,184,.15)!important;font-size:.7rem!important;font-weight:500!important;display:inline-flex!important;align-items:center!important;gap:3px!important;padding:2px 6px!important;border-radius:10px!important}

/* Colonne prise en charge */
.prise-en-charge-cell{font-weight:500!important;color:#6c757d!important;font-size:.8rem!important;line-height:1.3}
.prise-en-charge-cell .text-info,
.prise-en-charge-cell .text-muted,
.prise-en-charge-cell .text-warning{font-size:.75rem!important;font-weight:500!important;padding:2px 6px;border-radius:8px;background:rgba(108,117,125,.08);border:1px solid rgba(108,117,125,.15)}
.prise-en-charge-cell .text-info{background:rgba(23,162,184,.08)!important;border-color:rgba(23,162,184,.15)!important;color:#17a2b8!important}
.prise-en-charge-cell .text-warning{background:rgba(255,193,7,.08)!important;border-color:rgba(255,193,7,.15)!important;color:#ffc107!important}

/* Badge Résolu */
.badge-success.badge-sm{background:rgba(40,167,69,.1)!important;color:#28a745!important;border:1px solid rgba(40,167,69,.2);padding:2px 6px!important;font-size:.7rem!important;border-radius:10px!important}
.badge-secondary.badge-sm{background:rgba(108,117,125,.1)!important;color:#6c757d!important;border:1px solid rgba(108,117,125,.2);padding:2px 6px!important;font-size:.7rem!important;border-radius:10px!important}

/* Highlight récent */
.recent-takeup{background:linear-gradient(90deg,rgba(40,167,69,.05) 0%,rgba(40,167,69,.02) 100%)!important;border-left:3px solid #28a745!important;animation:recentGlow 3s ease-out!important}
@keyframes recentGlow{0%{background:rgba(40,167,69,.15)!important;transform:scale(1.01)}100%{background:linear-gradient(90deg,rgba(40,167,69,.05) 0%,rgba(40,167,69,.02) 100%)!important;transform:scale(1)}}
.text-muted .icon-xs{width:12px!important;height:12px!important}

/* ====== Modal détails COMPACT ====== */
.modal-lg{max-width:800px!important} /* ✅ CORRECTION : Taille réduite de 1200px à 800px */
.bg-gradient-primary{background:linear-gradient(45deg,#007bff,#0056b3)!important}

/* Timeline compacte */
.timeline-container-compact{position:relative}
.timeline-compact{border-left:2px solid #e9ecef;padding-left:15px}
.timeline-item-compact{position:relative;margin-bottom:12px;padding-left:20px}
.timeline-item-compact.success{border-left-color:#28a745}
.timeline-item-compact.info{border-left-color:#17a2b8}
.timeline-item-compact.warning{border-left-color:#ffc107}
.timeline-item-compact.danger{border-left-color:#dc3545}
.timeline-item-compact.secondary{border-left-color:#6c757d}
.timeline-dot{position:absolute;left:-4px;top:3px;width:6px;height:6px;border-radius:50%;background:#e9ecef}
.timeline-item-compact.success .timeline-dot{background:#28a745}
.timeline-item-compact.info .timeline-dot{background:#17a2b8}
.timeline-item-compact.warning .timeline-dot{background:#ffc107}
.timeline-item-compact.danger .timeline-dot{background:#dc3545}
.timeline-item-compact.secondary .timeline-dot{background:#6c757d}
.timeline-content-compact{background:#f8f9fa;border-radius:6px;padding:8px 12px;font-size:13px}

/* Responsive modal */
@media (max-width:768px){
  .modal-lg{max-width:95%!important;margin:10px auto!important} /* ✅ Mobile : 95% au lieu de 1200px */
  .card-body .row{margin:0}
  .card-body .col-sm-6{padding:5px}
  .timeline-content-compact{font-size:12px;padding:6px 10px}
  .badge{font-size:0.65rem!important;padding:1px 4px!important}
}

/* Cartes compactes dans le modal */
.modal-body .card{margin-bottom:10px!important}
.modal-body .card-header{padding:6px 12px!important;font-size:13px!important}
.modal-body .card-body{padding:8px 12px!important;font-size:13px!important}
.modal-body .badge-sm{font-size:0.7rem!important;padding:2px 6px!important}

/* Animation du modal */
#histTable tbody tr{animation:fadeIn .3s ease-out}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.alert{border:none;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15)}
.alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
.alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
.alert-info{background:#cce7ff;color:#004085;border-left:4px solid #007bff}
.modal.fade .modal-dialog{transition:transform .3s ease-out}
.modal.show .modal-dialog{transform:none}
.btn-outline-info:hover{transform:translateY(-1px);box-shadow:0 3px 8px rgba(23,162,184,.3)}
.spinner-border{animation:spinner-border 1s linear infinite}
@keyframes spinner-border{to{transform:rotate(360deg)}}
</style>
@endsection