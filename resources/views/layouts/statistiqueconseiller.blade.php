@extends('dashboard.master')
@section('title', 'Mes Statistiques Conseiller')
@section('contenu')
<div class="page-wrapper stats-conseiller-scope">
    <!-- Top Bar Start -->
    <div class="topbar">
        <nav class="navbar-custom">
            <ul class="list-unstyled topbar-nav float-right mb-0">
                <li class="dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="ml-1 nav-user-name hidden-sm">{{ Auth::user()->username }}</span>
                        <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" alt="profile-user" class="rounded-circle" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('layouts.app-conseiller') }}">
                            <i data-feather="headphones" class="align-self-center icon-xs icon-dual mr-1"></i> Interface Conseiller
                        </a>
                        <div class="dropdown-divider mb-0"></div>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Déconnexion
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
            <ul class="list-unstyled topbar-nav mb-0">
                <li>
                    <button class="nav-link button-menu-mobile">
                        <i data-feather="menu" class="align-self-center topbar-icon"></i>
                    </button>
                </li>
                <li class="creat-btn">
                    <div class="nav-link">
                        <a href="{{ route('layouts.app-conseiller') }}" class="btn btn-primary btn-sm">
                            <i data-feather="arrow-left" class="mr-2"></i>Retour Interface
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
    <!-- End Top Bar -->

    <!-- Page Content-->
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col">
                                <h4 class="page-title">
                                    <i data-feather="bar-chart-2" class="mr-2"></i>
                                    Mes Statistiques de Performance
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app-conseiller') }}">Interface Conseiller</a></li>
                                    <li class="breadcrumb-item active">Mes Statistiques</li>
                                </ol>
                            </div>
                            <div class="col-auto align-self-center">
                                <div class="btn-group" role="group">
                                    <a href="#" class="btn btn-sm btn-outline-primary" id="refreshBtn" onclick="refreshConseillerStats()" title="Actualiser">
                                        <i data-feather="refresh-cw" class="align-self-center icon-xs"></i>
                                    </a>
                                    <a href="{{ route('conseiller.export') }}" class="btn btn-sm btn-outline-secondary" title="Exporter mes données">
                                        <i data-feather="download" class="align-self-center icon-xs mr-1"></i>Export
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Personnels Conseiller -->
            <div class="row align-items-stretch kpi-row mb-4">
                <div class="col-md-6 col-lg-3 d-flex">
                    <div class="card kpi-card flex-grow-1">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="kpi-label text-success">Tickets Traités Aujourd'hui</p>
                                    <h3 class="kpi-value text-success" id="ticketsTraitesAujourdhui">—</h3>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="check-circle" class="kpi-icon text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex">
                    <div class="card kpi-card flex-grow-1">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="kpi-label text-info">Taux de Résolution</p>
                                    <h3 class="kpi-value text-info" id="tauxResolutionAujourdhui">—</h3>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="target" class="kpi-icon text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex">
                    <div class="card kpi-card flex-grow-1">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="kpi-label text-warning">Temps Moyen</p>
                                    <h3 class="kpi-value text-warning" id="tempsMoyenTraitement">—</h3>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="clock" class="kpi-icon text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex">
                    <div class="card kpi-card flex-grow-1">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="kpi-label text-primary">Tickets Collaboratifs</p>
                                    <h3 class="kpi-value text-primary" id="ticketsTransferts">—</h3>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="share" class="kpi-icon text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activité En Cours et Performance -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header header-flat">
                            <h4 class="card-title mb-0">
                                <i data-feather="activity" class="mr-2"></i>Mon Activité En Cours
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="border-right">
                                        <h4 class="mb-1" id="statutConseiller">
                                            <i data-feather="circle" class="mr-1 text-success" style="width: 12px; height: 12px;"></i>
                                            <span class="text-success">Disponible</span>
                                        </h4>
                                        <p class="text-muted mb-0">Statut Actuel</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info mb-1" id="tempsConnexion">—</h4>
                                    <p class="text-muted mb-0">Session Actuelle</p>
                                </div>
                            </div>
                            
                            <div id="ticketEnCoursSection" class="d-none">
                                <div class="alert alert-info">
                                    <h6 class="mb-2">
                                        <i data-feather="phone-call" class="mr-1"></i>
                                        Ticket En Cours : <span id="numeroTicketEnCours">—</span>
                                    </h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p class="mb-1"><strong>Client:</strong> <span id="clientEnCours">—</span></p>
                                            <p class="mb-0"><strong>Service:</strong> <span id="serviceEnCours">—</span></p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="mb-1"><strong>Durée:</strong> <span id="dureeEnCours">—</span></p>
                                            <p class="mb-0"><strong>Début:</strong> <span id="heurePriseEnCharge">—</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="ticketDisponibleSection">
                                <div class="alert alert-soft-success">
                                    <h6 class="mb-1">
                                        <i data-feather="check-circle" class="mr-1"></i>
                                        Prêt pour le prochain ticket
                                    </h6>
                                    <p class="mb-0">File d'attente FIFO : Premier arrivé, premier servi avec priorité transferts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header header-flat">
                            <h4 class="card-title mb-0">
                                <i data-feather="pie-chart" class="mr-2"></i>Répartition Résolution Aujourd'hui
                            </h4>
                        </div>
                        <div class="card-body">
                            <div id="repartitionChart" class="apex-charts" style="height: 200px;"></div>
                            <div class="row text-center mt-3">
                                <div class="col-4">
                                    <div class="border-right">
                                        <h5 class="text-success mb-1" id="ticketsResolusAujourdhui">—</h5>
                                        <p class="text-muted mb-0 small">Résolus</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-right">
                                        <h5 class="text-danger mb-1" id="ticketsRefusesAujourdhui">—</h5>
                                        <p class="text-muted mb-0 small">Refusés</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                        <h5 class="text-info mb-1" id="ticketsTransferesEmisAujourdhui">—</h5>
                                        <p class="text-muted mb-0 small">Transférés </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance & Score -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header header-flat d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                <i data-feather="trending-up" class="mr-2"></i>Ma Performance 
                            </h4>
                            <div class="dropdown">
                                <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown" id="performancePeriodBtn">
                                    Aujourd'hui<i class="las la-angle-down ml-1"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#" onclick="changePerformancePeriod('today', this, event)">Aujourd'hui</a>
                                    <a class="dropdown-item" href="#" onclick="changePerformancePeriod('week', this, event)">Cette semaine</a>
                                    <a class="dropdown-item" href="#" onclick="changePerformancePeriod('month', this, event)">Ce mois</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="performanceSection">
                            <div class="row text-center mb-3 mini-stats">
                                <div class="col-md-3">
                                    <h6 class="text-success" id="totalPeriodeTickets">—</h6>
                                    <p class="text-muted mb-0 small">Total Traités</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-info" id="tauxResolutionPeriode">—</h6>
                                    <p class="text-muted mb-0 small">Taux Résolution</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-warning" id="tempsMoyenPeriode">—</h6>
                                    <p class="text-muted mb-0 small">Temps Moyen</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-primary" id="transfertsPeriode">—</h6>
                                    <p class="text-muted mb-0 small">Collaboratifs</p>
                                </div>
                            </div>
                            
                            <div class="performance-details bg-light p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-2">Détails Résolution</h6>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Tickets résolus:</small>
                                            <small class="text-success" id="detailsResolus">—</small>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Tickets refusés:</small>
                                            <small class="text-danger" id="detailsRefuses">—</small>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small>Avec commentaire:</small>
                                            <small class="text-info" id="detailsCommentaires">—</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-2">Détails Collaboration</h6>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Tickets transférés reçus:</small>
                                            <small class="text-primary" id="detailsTransfertsRecus">—</small>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Tickets normaux:</small>
                                            <small class="text-secondary" id="detailsTicketsNormaux">—</small>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small>% Collaboratifs:</small>
                                            <small class="text-warning" id="detailsPourcentageCollab">—</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header header-flat">
                            <h4 class="card-title mb-0">
                                <i data-feather="award" class="mr-2"></i>Score Performance Global
                            </h4>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center">
                            <div class="text-center">
                                <div id="scoreGauge" class="apex-charts" style="height: 180px;"></div>
                                <div class="mt-3">
                                    <h3 class="text-primary mb-1" id="scorePerformance">—</h3>
                                    <p class="text-muted mb-2">Score Global (/100)</p>
                                    <span class="badge badge-soft-success" id="badgePerformance">Calculé en temps réel</span>
                                </div>
                                <div class="mt-3">
                                    <div class="performance-breakdown bg-light p-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Résolution :</small>
                                            <small class="text-success" id="scoreResolution">—</small>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Rapidité :</small>
                                            <small class="text-info" id="scoreRapidite">—</small>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Volume :</small>
                                            <small class="text-warning" id="scoreVolume">—</small>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small>Collaboration :</small>
                                            <small class="text-primary" id="scoreCollaboration">—</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique Récent (Pagination par 8) -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card h-100">
                        <div class="card-header header-flat d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                <i data-feather="list" class="mr-2"></i>Mon Historique Récent
                            </h4>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary active" onclick="filterHistorique('all', this)">Tous</button>
                                <button class="btn btn-outline-success" onclick="filterHistorique('traiter', this)">Résolus</button>
                                <button class="btn btn-outline-danger" onclick="filterHistorique('refuser', this)">Refusés</button>
                                <button class="btn btn-outline-info" onclick="filterHistorique('transferer', this)">Transférés</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 table-flat">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="border-top-0">Ticket</th>
                                            <th class="border-top-0">Client</th>
                                            <th class="border-top-0">Service</th>
                                            <th class="border-top-0">Résolution</th>
                                            <th class="border-top-0">Temps</th>
                                            <th class="border-top-0">Origine</th>
                                            <th class="border-top-0">Date/Heure</th>
                                        </tr>
                                    </thead>
                                    <tbody id="historiqueTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">
                                                <i data-feather="loader" class="mr-1"></i>Chargement de l'historique...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small" id="historiquePageInfo">Page 1 / 1</div>
                                <div>
                                    <button class="btn btn-outline-secondary btn-sm mr-2" id="prevPageBtn" onclick="gotoHistoriquePage(currentPage-1)" disabled>« Précédent</button>
                                    <button class="btn btn-outline-secondary btn-sm" id="nextPageBtn" onclick="gotoHistoriquePage(currentPage+1)" disabled>Suivant »</button>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="alert alert-soft-info">
                                    <h6 class="mb-1">
                                        <i data-feather="info" class="mr-1"></i>
                                        Système de File FIFO 
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis - Tous droits réservés.
        </footer>
    </div>
</div>

<!-- Expose les routes au JS -->
<script>
    window.CONSEILLER = {
        csrf: "{{ csrf_token() }}",
        routes: {
            resolutionStats: "{{ route('conseiller.resolution-stats') }}",
            resolutionHistory: "{{ route('conseiller.resolution-history') }}",
            liveStats: "{{ route('api.conseiller.live-resolution-stats') }}",
            currentTicket: "{{ route('conseiller.current-ticket') }}",
            export: "{{ route('conseiller.export') }}"
        },
        user: {
            id: {{ Auth::id() }},
            username: "{{ Auth::user()->username }}",
            email: "{{ Auth::user()->email }}"
        },
        debug: {{ config('app.debug') ? 'true' : 'false' }}
    };
</script>

<!-- Scripts -->
<script>
    // ========= Configuration et variables globales =========
    let repartitionChart, scoreGauge;
    let currentPerformancePeriod = 'today';
    let currentFilter = 'all';
    let isPageInitialized = false;

    // Historique (client-side pagination par 8)
    let historiqueAll = [];
    let historiqueFiltered = [];
    let currentPage = 1;
    const pageSize = 8;

    let scrollPosition = 0;

    // ========= Gestion du défilement =========
    function saveScrollPosition() { scrollPosition = window.scrollY || document.documentElement.scrollTop; }
    function restoreScrollPosition() { window.scrollTo(0, scrollPosition); }

    // ========= Labels période =========
    const PERIOD_LABELS = { 'today': "Aujourd'hui", 'week': 'Cette semaine', 'lastweek': 'Semaine dernière', 'month': 'Ce mois' };
    function setDropdownLabel(buttonId, period) {
        const button = document.getElementById(buttonId);
        if (!button) return;
        const label = PERIOD_LABELS[period] || period;
        button.innerHTML = `${label}<i class="las la-angle-down ml-1"></i>`;
    }

    // ========= Notifications =========
    function showNotification(message, type = 'info', duration = 3000) {
        try {
            const alertTypes = { 'success': 'alert-success', 'error': 'alert-danger', 'warning': 'alert-warning', 'info': 'alert-info' };
            const icons = { 'success': 'mdi-check-circle', 'error': 'mdi-alert-circle', 'warning': 'mdi-alert', 'info': 'mdi-information' };
            const alert = document.createElement('div');
            alert.className = `alert ${alertTypes[type]} alert-dismissible fade show`;
            alert.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 450px;';
            alert.innerHTML = `
                <i class="mdi ${icons[type]} mr-2"></i>${escapeHtml(message)}
                <button type="button" class="close" onclick="this.parentElement.remove()">
                    <span aria-hidden="true">&times;</span>
                </button>`;
            document.body.appendChild(alert);
            setTimeout(() => { if (alert && alert.parentElement) alert.remove(); }, duration);
        } catch (e) { console.error('Erreur notification:', e); }
    }

    // ========= Feather icons =========
    function safeFeatherReplace() {
        try { if (window.feather && typeof feather.replace === 'function') { feather.replace(); } } catch (e) {}
    }

    // ========= Graphiques =========
    function initializeConseillerCharts() {
        if (!window.ApexCharts) { console.warn('ApexCharts non disponible'); return; }
        try {
            // Donut Répartition (3 segments : Résolus / Refusés / Transférés émis)
            const repartitionElement = document.querySelector("#repartitionChart");
            if (repartitionElement) {
                repartitionChart = new ApexCharts(repartitionElement, {
                    chart: { type: 'donut', height: 200, foreColor: '#6b7280' },
                    series: [0, 0, 0],
                    labels: ['Résolus', 'Refusés', 'Transférés '],
                    colors: ['#28a745', '#dc3545', '#17a2b8'],
                    legend: { show: true, position: 'bottom', fontSize: '12px' },
                    dataLabels: {
                        enabled: true,
                        formatter: function(_, opts) {
                            return opts.w.config.series[opts.seriesIndex];
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    name: { show: false },
                                    value: { show: true, fontSize: '18px', fontWeight: 'bold', color: '#333' },
                                    total: { show: true, showAlways: true, label: 'Total', fontSize: '11px', color: '#999' }
                                }
                            }
                        }
                    },
                    noData: { text: 'Aucune donnée disponible' }
                });
                repartitionChart.render();
            }

            // Gauge score
            const scoreElement = document.querySelector("#scoreGauge");
            if (scoreElement) {
                scoreGauge = new ApexCharts(scoreElement, {
                    chart: { type: 'radialBar', height: 180 },
                    plotOptions: { radialBar: {
                        startAngle: -90, endAngle: 90,
                        dataLabels: { name: { show: false }, value: { fontSize: '18px', fontWeight: 'bold', formatter: v => v + '%' } }
                    }},
                    colors: ['#007bff'],
                    series: [0],
                    labels: ['Performance'],
                    noData: { text: 'Calcul en cours...' }
                });
                scoreGauge.render();
            }
        } catch (e) { console.error('Erreur init charts:', e); }
    }

    // ========= Fetch JSON =========
    async function fetchJson(url) {
        const response = await fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        return response.json();
    }

    // ========= Loading section UI =========
    function showSectionLoading(id){ const el = document.getElementById(id); if (el){ el.style.opacity='0.6'; el.style.pointerEvents='none'; el.classList.add('section-loading'); } }
    function hideSectionLoading(id){ const el = document.getElementById(id); if (el){ el.style.opacity=''; el.style.pointerEvents=''; el.classList.remove('section-loading'); } }

    // ========= Rafraîchissement principal =========
    async function refreshConseillerStats(silent = false) {
        if (!isPageInitialized) return;
        saveScrollPosition();
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) refreshBtn.classList.add('spinning');

        try {
            // Stats
            const urlResolution = `${CONSEILLER.routes.resolutionStats}?period=${currentPerformancePeriod}&t=${Date.now()}`;
            const resolutionData = await fetchJson(urlResolution);
            if (resolutionData && resolutionData.success) {
                updateConseillerKPIs(resolutionData.resolution_stats);
                updateRepartitionChart(resolutionData.resolution_stats);
                updatePerformanceDetails(resolutionData.resolution_stats);
            }

            // Temps réel
            const liveData = await fetchJson(`${CONSEILLER.routes.liveStats}?t=${Date.now()}`);
            if (liveData && liveData.success) updateLiveStats(liveData.live_resolution_stats);

            // Ticket en cours
            await checkCurrentTicket();

            // Historique
            await loadRecentHistory();

            if (!silent) showNotification('Statistiques mises à jour', 'success');
        } catch (e) {
            console.error('Erreur refreshConseillerStats:', e);
            if (!silent) showNotification('Erreur lors du chargement des statistiques', 'error');
        } finally {
            const refreshBtn2 = document.getElementById('refreshBtn');
            if (refreshBtn2) refreshBtn2.classList.remove('spinning');
            safeFeatherReplace();
            restoreScrollPosition();
        }
    }

    // ========= KPIs =========
    function updateConseillerKPIs(s) {
        if (!s) return;
        setText('ticketsTraitesAujourdhui', (s.total_traites || 0), true);
        setText('tauxResolutionAujourdhui', ((s.taux_resolution || 0).toFixed(1) + '%'));
        setText('tempsMoyenTraitement', ((s.avg_processing_time_min || 0).toFixed(1) + ' min'));
        setText('ticketsTransferts', (s.tickets_recus_traites || 0), true); // transférés reçus (collaboration)

        const resolus = s.tickets_resolus || 0;
        const nonResolus = s.tickets_non_resolus || 0;
        const transferesEmis = (
            s.tickets_transferes_par_conseiller ??
            s.tickets_transferes ??
            s.transferts_emis ??
            s.tickets_transferes_emis ??
            s.tickets_transfers_out ??
            s.outgoing_transfers ?? 0
        );

        setText('ticketsResolusAujourdhui', resolus, true);
        setText('ticketsRefusesAujourdhui', nonResolus, true);
        setText('ticketsTransferesEmisAujourdhui', transferesEmis, true);

        updateConnectionTime();
        const scorePerf = calculatePerformanceScore(s);
        updatePerformanceScore(scorePerf, s);
    }

    // ========= Détails Performance =========
    function updatePerformanceDetails(s) {
        if (!s) return;
        setText('totalPeriodeTickets', (s.total_traites || 0), true);
        setText('tauxResolutionPeriode', ((s.taux_resolution || 0).toFixed(1) + '%'));
        setText('tempsMoyenPeriode', ((s.avg_processing_time_min || 0).toFixed(1) + ' min'));
        setText('transfertsPeriode', (s.tickets_recus_traites || 0), true);

        setText('detailsResolus', (s.tickets_resolus || 0), true);
        setText('detailsRefuses', (s.tickets_non_resolus || 0), true);

        const avecCommentaire = (s.tickets_non_resolus || 0) + Math.round((s.tickets_resolus || 0) * 0.2);
        setText('detailsCommentaires', avecCommentaire, true);

        const transfertsRecus = s.tickets_recus_traites || 0;
        const ticketsNormaux = s.tickets_normaux_traites || 0;
        const totalTickets = transfertsRecus + ticketsNormaux;

        setText('detailsTransfertsRecus', transfertsRecus, true);
        setText('detailsTicketsNormaux', ticketsNormaux, true);
        setText('detailsPourcentageCollab', totalTickets > 0 ? ((transfertsRecus / totalTickets) * 100).toFixed(1) + '%' : '0%');
    }

    // ========= Live =========
    function updateLiveStats(liveStats) {
        if (!liveStats) return;
        updateConnectionTime();
        if (liveStats.conseiller_info) {
            const hasTicketEnCours = liveStats.ticket_en_cours || false;
            updateConseillerStatus(hasTicketEnCours ? 'busy' : 'available');
        }
    }

    // ========= Donut =========
    function updateRepartitionChart(s) {
        if (!repartitionChart || !repartitionChart.updateSeries) return;

        const resolus = s?.tickets_resolus ?? 0;
        const refuses = s?.tickets_non_resolus ?? 0;
        const transferesEmis = (
            s?.tickets_transferes_par_conseiller ??
            s?.tickets_transferes ??
            s?.transferts_emis ??
            s?.tickets_transferes_emis ??
            s?.tickets_transfers_out ??
            s?.outgoing_transfers ?? 0
        );

        repartitionChart.updateSeries([resolus, refuses, transferesEmis]);
    }

    // ========= Score =========
    function calculatePerformanceScore(stats) {
        if (!stats) return 0;
        let score = 0;
        const tauxResolution = stats.taux_resolution || 0;
        if (tauxResolution >= 95) score += 40;
        else if (tauxResolution >= 90) score += 38;
        else if (tauxResolution >= 85) score += 35;
        else if (tauxResolution >= 80) score += 30;
        else if (tauxResolution >= 75) score += 25;
        else if (tauxResolution >= 60) score += 15;
        else score += 5;

        const tempsTraitement = stats.avg_processing_time_min || 0;
        if (tempsTraitement <= 8) score += 30;
        else if (tempsTraitement <= 12) score += 28;
        else if (tempsTraitement <= 15) score += 25;
        else if (tempsTraitement <= 20) score += 20;
        else if (tempsTraitement <= 25) score += 15;
        else if (tempsTraitement <= 30) score += 10;
        else score += 5;

        const totalTraites = stats.total_traites || 0;
        if (totalTraites >= 25) score += 20;
        else if (totalTraites >= 20) score += 18;
        else if (totalTraites >= 15) score += 16;
        else if (totalTraites >= 10) score += 14;
        else if (totalTraites >= 5) score += 10;
        else score += 5;

        const transfertsRecus = stats.tickets_recus_traites || 0; // collaboration (reçus)
        if (transfertsRecus >= 8) score += 10;
        else if (transfertsRecus >= 5) score += 8;
        else if (transfertsRecus >= 3) score += 6;
        else if (transfertsRecus >= 1) score += 4;
        else score += 2;

        return Math.min(100, Math.round(score));
    }

    function updatePerformanceScore(score, stats) {
        setText('scorePerformance', score);
        if (scoreGauge && scoreGauge.updateSeries) scoreGauge.updateSeries([score]);
        updatePerformanceBadge(score);
        updateScoreBreakdown(stats);
    }

    function updatePerformanceBadge(score) {
        const badge = document.getElementById('badgePerformance');
        if (!badge) return;
        if (score >= 90) { badge.className = 'badge badge-success'; badge.textContent = 'Excellence'; }
        else if (score >= 80) { badge.className = 'badge badge-info'; badge.textContent = 'Très bien'; }
        else if (score >= 70) { badge.className = 'badge badge-warning'; badge.textContent = 'Bien'; }
        else if (score >= 60) { badge.className = 'badge badge-warning'; badge.textContent = 'Correct'; }
        else { badge.className = 'badge badge-danger'; badge.textContent = 'À améliorer'; }
    }

    function updateScoreBreakdown(s) {
        if (!s) return;
        const tr = s.taux_resolution || 0;
        const tm = s.avg_processing_time_min || 0;
        const tt = s.total_traites || 0;
        const tf = s.tickets_recus_traites || 0;

        let r = (tr >= 95) ? 40 : (tr >= 90) ? 38 : (tr >= 85) ? 35 : (tr >= 75) ? 25 : 15;
        setText('scoreResolution', Math.round((r/40)*100) + '%');

        let ra = (tm <= 8) ? 30 : (tm <= 15) ? 25 : (tm <= 25) ? 15 : 5;
        setText('scoreRapidite', Math.round((ra/30)*100) + '%');

        let v = (tt >= 25) ? 20 : (tt >= 15) ? 16 : (tt >= 10) ? 14 : 10;
        setText('scoreVolume', Math.round((v/20)*100) + '%');

        let c = (tf >= 8) ? 10 : (tf >= 5) ? 8 : (tf >= 1) ? 4 : 2;
        setText('scoreCollaboration', Math.round((c/10)*100) + '%');
    }

    // ========= Ticket en cours =========
    async function checkCurrentTicket() {
        try {
            const result = await fetchJson(CONSEILLER.routes.currentTicket);
            const ticketEnCoursSection = document.getElementById('ticketEnCoursSection');
            const ticketDisponibleSection = document.getElementById('ticketDisponibleSection');

            if (result.success && result.has_current_ticket) {
                const t = result.current_ticket || {};
                setText('numeroTicketEnCours', t.numero_ticket || '—');
                setText('clientEnCours', t.client_name || t.prenom || '—');
                setText('serviceEnCours', t.service_name || '—');
                setText('heurePriseEnCharge', t.heure_prise_en_charge || '—');

                if (t.heure_prise_en_charge) {
                    const now = new Date();
                    const hp = new Date();
                    const parts = String(t.heure_prise_en_charge).split(':');
                    hp.setHours(parseInt(parts[0]||0), parseInt(parts[1]||0), parseInt(parts[2]||0));
                    const diffMin = Math.floor((now - hp) / (1000*60));
                    setText('dureeEnCours', diffMin + ' min');
                } else setText('dureeEnCours', '—');

                updateConseillerStatus('busy');
                if (ticketEnCoursSection) ticketEnCoursSection.classList.remove('d-none');
                if (ticketDisponibleSection) ticketDisponibleSection.classList.add('d-none');
            } else {
                updateConseillerStatus('available');
                if (ticketEnCoursSection) ticketEnCoursSection.classList.add('d-none');
                if (ticketDisponibleSection) ticketDisponibleSection.classList.remove('d-none');
            }
        } catch (e) { console.error('Erreur current ticket:', e); }
    }

    function updateConseillerStatus(status) {
        const el = document.getElementById('statutConseiller'); if (!el) return;
        if (status === 'available' || status === 'disponible') {
            el.innerHTML = `<i data-feather="circle" class="mr-1 text-success" style="width: 12px; height: 12px;"></i><span class="text-success">Disponible</span>`;
        } else if (status === 'busy' || status === 'occupe') {
            el.innerHTML = `<i data-feather="phone" class="mr-1 text-warning" style="width: 12px; height: 12px;"></i><span class="text-warning">En communication</span>`;
        } else if (status === 'pause') {
            el.innerHTML = `<i data-feather="pause-circle" class="mr-1 text-info" style="width: 12px; height: 12px;"></i><span class="text-info">En pause</span>`;
        } else {
            el.innerHTML = `<i data-feather="help-circle" class="mr-1 text-muted" style="width: 12px; height: 12px;"></i><span class="text-muted">Statut inconnu</span>`;
        }
        safeFeatherReplace();
    }

    function updateConnectionTime() {
        const t = document.getElementById('tempsConnexion'); if (!t) return;
        const now = new Date(); const start = new Date(); start.setHours(8,0,0,0);
        if (now > start) {
            const diff = now - start; const h = Math.floor(diff / 36e5); const m = Math.floor((diff % 36e5) / 6e4);
            t.textContent = h > 0 ? `${h}h${String(m).padStart(2,'0')}` : `${m} min`;
        } else t.textContent = '—';
    }

    // ========= Historique (pas de filtre date + fallback + pagination) =========
    function extractTicketsPayload(result) {
        if (!result) return [];
        const candidates = [
            result.tickets, result.data, result.items, result.history, result.historique,
            result.records, result.rows, result.list, result.result
        ].filter(Boolean);

        let arr = [];
        candidates.forEach(v => {
            if (Array.isArray(v)) arr = arr.concat(v);
            else if (Array.isArray(v?.data)) arr = arr.concat(v.data);
        });

        // Clés possibles pour “pris en charge / assignés”
        const extraKeys = [
            'tickets_pris_en_charge','tickets_assignes','tickets_en_charge',
            'assigned','handled','prise_en_charge','pris_en_charge'
        ];
        extraKeys.forEach(k => { const v = result[k]; if (Array.isArray(v)) arr = arr.concat(v); });

        return arr;
    }

    function normalizeTicket(t) {
        const resoluRaw = t.resolu ?? t.is_resolved ?? t.status_resolu ?? (t.action_performed === 'traiter' ? 1 : (t.action_performed === 'refuser' ? 0 : undefined));
        const resolu = typeof resoluRaw === 'string' ? (resoluRaw === '1' ? 1 : (resoluRaw === '0' ? 0 : undefined)) : (Number.isFinite(resoluRaw) ? Number(resoluRaw) : undefined);

        const received = (t.transferer === 'new') || (t.origin === 'reçu') || (t.origin === 'transféré') || (t.is_transferred_in === true);
        const transferredOut = (t.action_performed === 'transferer') || (t.transferred_out === true) || (t.type === 'transfer_out') || (t.sens === 'out');

        const duree = t.duree_minutes ?? t.duree_traitement ?? (t.heure_de_fin && t.heure_prise_en_charge ? t.duree_minutes : null);

        return {
            id: t.id ?? t.ticket_id ?? t.numero_ticket ?? JSON.stringify(t),
            numero_ticket: t.numero_ticket ?? t.reference ?? t.code ?? '#'+(t.id ?? ''),
            client_name: t.client_name ?? t.client ?? t.nom ?? t.prenom ?? '—',
            service_name: t.service_name ?? t.service ?? '—',
            resolu: (resolu === 0 || resolu === 1) ? resolu : (t.action_performed === 'traiter' ? 1 : (t.action_performed === 'refuser' ? 0 : null)),
            // infos transfert
            is_transfer_received: !!received,
            is_transfer_out: !!transferredOut,
            duree_minutes: Number.isFinite(duree) ? duree : null,
            updated_at: t.date_traitement ?? t.updated_at ?? t.created_at ?? null
        };
    }

    async function loadRecentHistory() {
        try {
            const baseUrl = `${CONSEILLER.routes.resolutionHistory}`;
            let list = [];

            // 1) sans filtre de date
            try {
                const r1 = await fetchJson(`${baseUrl}?limit=200&t=${Date.now()}`);
                list = extractTicketsPayload(r1);
            } catch(e){}

            // 2) fallback /all si vide
            if ((!list || list.length === 0) && !/\/all(\?|$)/.test(baseUrl)) {
                try {
                    const r2 = await fetchJson(`${baseUrl}/all?limit=200&t=${Date.now()}`);
                    list = extractTicketsPayload(r2);
                } catch(e){}
            }

            if (!list || list.length === 0) {
                historiqueAll = [];
            } else {
                // Normalisation + dédup + tri
                const map = new Map();
                list.forEach(raw => {
                    const n = normalizeTicket(raw);
                    if (!map.has(n.id)) map.set(n.id, n);
                });
                historiqueAll = Array.from(map.values()).sort((a,b) => (new Date(b.updated_at||0)) - (new Date(a.updated_at||0)));
            }

            applyHistoriqueFilters();
            renderHistoriquePage();
        } catch (error) {
            console.error('Erreur chargement historique:', error);
            const tbody = document.getElementById('historiqueTableBody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger py-3">
                            <i data-feather="alert-circle" class="mr-1"></i>Erreur de chargement de l'historique
                        </td>
                    </tr>`;
            }
        }
    }

    function applyHistoriqueFilters() {
        let arr = [...historiqueAll];
        switch (currentFilter) {
            case 'traiter':
            case 'resolu':
            case 'résolus':
                arr = arr.filter(t => t.resolu === 1);
                break;
            case 'refuser':
            case 'refuse':
            case 'refusés':
                arr = arr.filter(t => t.resolu === 0);
                break;
            case 'transferer':
            case 'transfere':
            case 'transférés':
                // inclut transferts émis OU reçus (pour ne rien rater)
                arr = arr.filter(t => t.is_transfer_out || t.is_transfer_received);
                break;
            case 'all':
            default:
                break;
        }
        historiqueFiltered = arr;
        currentPage = 1;
        updatePaginationControls();
    }

    function renderHistoriquePage() {
        const tbody = document.getElementById('historiqueTableBody');
        if (!tbody) return;

        if (!Array.isArray(historiqueFiltered) || historiqueFiltered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i data-feather="inbox" class="mr-1"></i>Aucun ticket à afficher
                    </td>
                </tr>`;
            updatePaginationControls();
            safeFeatherReplace();
            return;
        }

        const start = (currentPage - 1) * pageSize;
        const pageItems = historiqueFiltered.slice(start, start + pageSize);

        tbody.innerHTML = '';
        pageItems.forEach(t => {
            const resolutionBadge = (t.resolu === 1)
                ? '<span class="badge badge-success no-radius">Résolu</span>'
                : (t.resolu === 0)
                    ? '<span class="badge badge-danger no-radius">Refusé</span>'
                    : '<span class="badge badge-secondary no-radius">—</span>';

            let origineBadge;
            if (t.is_transfer_out) {
                origineBadge = '<span class="badge badge-info no-radius">Transfert émis</span>';
            } else if (t.is_transfer_received) {
                origineBadge = '<span class="badge badge-primary no-radius">Transfert reçu</span>';
            } else {
                origineBadge = '<span class="badge badge-secondary no-radius">Normal</span>';
            }

            const duree = Number.isFinite(t.duree_minutes) ? (t.duree_minutes + ' min') : '—';
            const dateTraitement = formatDateTime(t.updated_at);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${escapeHtml(t.numero_ticket)}</strong></td>
                <td>${escapeHtml(t.client_name)}</td>
                <td><small>${escapeHtml(t.service_name)}</small></td>
                <td>${resolutionBadge}</td>
                <td><small>${escapeHtml(duree)}</small></td>
                <td>${origineBadge}</td>
                <td><small>${escapeHtml(dateTraitement)}</small></td>`;
            tbody.appendChild(tr);
        });

        updatePaginationControls();
        safeFeatherReplace();
    }

    function updatePaginationControls() {
        const total = historiqueFiltered.length;
        const totalPages = Math.max(1, Math.ceil(total / pageSize));
        currentPage = Math.min(currentPage, totalPages);

        const info = document.getElementById('historiquePageInfo');
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');

        if (info) info.textContent = `Page ${totalPages ? currentPage : 1} / ${totalPages}`;

        if (prevBtn) prevBtn.disabled = (currentPage <= 1 || totalPages <= 1);
        if (nextBtn) nextBtn.disabled = (currentPage >= totalPages || totalPages <= 1);
    }

    function gotoHistoriquePage(page) {
        const totalPages = Math.max(1, Math.ceil(historiqueFiltered.length / pageSize));
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderHistoriquePage();
    }

    // ========= Filtrage (boutons) =========
    function filterHistorique(action, btn) {
        currentFilter = action || 'all';
        document.querySelectorAll('.btn-group.btn-group-sm .btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        applyHistoriqueFilters();
        renderHistoriquePage();
    }

    // ========= Période performance =========
    async function refreshPerformanceSection(period = currentPerformancePeriod) {
        currentPerformancePeriod = period;
        showSectionLoading('performanceSection');
        try {
            const urlStats = `${CONSEILLER.routes.resolutionStats}?period=${encodeURIComponent(period)}&t=${Date.now()}`;
            const statsPayload = await fetchJson(urlStats);
            if (statsPayload && statsPayload.success) {
                updatePerformanceDetails(statsPayload.resolution_stats);
            }
        } catch (e) { console.error('Erreur rafraîchissement performance:', e); }
        finally { hideSectionLoading('performanceSection'); }
    }

    function changePerformancePeriod(period, clickedElement, event) {
        if (event) event.preventDefault();
        setDropdownLabel('performancePeriodBtn', period);
        refreshPerformanceSection(period);
        if (clickedElement) {
            const dd = clickedElement.closest('.dropdown');
            if (dd && window.$ && $.fn.dropdown) { $(dd).find('.dropdown-toggle').dropdown('hide'); }
        }
    }

    // ========= Utils =========
    function setText(id, value, isNumber = false) {
        const el = document.getElementById(id); if (!el) return;
        if (isNumber && typeof value === 'number') el.textContent = value.toLocaleString('fr-FR'); else el.textContent = (value ?? '—');
    }
    function escapeHtml(str) { if (str === null || str === undefined) return ''; const div = document.createElement('div'); div.textContent = String(str); return div.innerHTML; }
    function formatDateTime(dt) {
        if (!dt) return '—';
        try {
            const d = new Date(dt);
            // j/m/année hh:mm
            return d.toLocaleString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
        } catch(e) { return String(dt); }
    }

    // ========= Auto-refresh =========
    function startConseillerAutoRefresh() {
        let delay = 30000; let errors = 0;
        const loop = async () => {
            if (!isPageInitialized) return setTimeout(loop, delay);
            try {
                await refreshConseillerStats(true);
                await checkCurrentTicket();
                errors = 0; delay = 30000;
            } catch (e) {
                errors++; delay = Math.min(300000, 30000 * Math.pow(1.5, errors));
            }
            setTimeout(loop, delay);
        };
        setTimeout(loop, delay);
    }

    // ========= Init =========
    document.addEventListener('DOMContentLoaded', function() {
        try {
            safeFeatherReplace();
            initializeConseillerCharts();
            setDropdownLabel('performancePeriodBtn', 'today');
            isPageInitialized = true;

            Promise.all([
                refreshConseillerStats(true),
                refreshPerformanceSection('today'),
                checkCurrentTicket(),
                loadRecentHistory()
            ]).then(() => {
                showNotification('Statistiques personnelles chargées', 'success', 1500);
            }).catch(() => {
                showNotification('Erreur lors du chargement initial', 'error');
            });

            startConseillerAutoRefresh();
        } catch (e) {
            console.error('Erreur critique init:', e);
            showNotification('Erreur critique lors de l\'initialisation', 'error');
        }
    });

    // ========= Styles dynamiques =========
    const conseillerSpinCSS = `
        #refreshBtn.spinning svg { animation: spin 0.8s linear infinite; transform-origin: 50% 50%; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .section-loading { position: relative; pointer-events: none; }
        .section-loading::before { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 10; }
        .section-loading::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 20px; height: 20px; border: 2px solid #007bff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 0.8s linear infinite; z-index: 11; }
        .btn-group .btn.active { background-color: #007bff; color: #fff; border-color: #007bff; }
    `;
    document.head.insertAdjacentHTML('beforeend', `<style>${conseillerSpinCSS}</style>`);
</script>

<style>
    /* ========= CONFIG GLOBALE ========= */
    .stats-conseiller-scope .container-fluid > .row { margin-bottom: 1.25rem; }
    .stats-conseiller-scope .row.align-items-stretch > [class*="col-"] { margin-bottom: 1rem; }

    /* ✅ Suppression des bordures colorées (bords des "rectangles") */
    .stats-conseiller-scope .card,
    .stats-conseiller-scope .btn,
    .stats-conseiller-scope .badge,
    .stats-conseiller-scope .alert,
    .stats-conseiller-scope .dropdown-menu,
    .stats-conseiller-scope .form-control,
    .stats-conseiller-scope .table,
    .stats-conseiller-scope .page-title-box,
    .stats-conseiller-scope .thead-light th { border-radius: 0 !important; }

    /* Neutralisation d'éventuelles classes "border-left-*" */
    .border-left-success, .border-left-info, .border-left-warning, .border-left-primary { border-left: 0 !important; }

    /* Cartes */
    .stats-conseiller-scope .card { border: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #fff; transition: all 0.2s ease; }
    .stats-conseiller-scope .card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.08); }

    .header-flat { background: #f8f9fa; border-bottom: 1px solid #e5e7eb; padding: 1rem 1.25rem; }

    /* KPI */
    .kpi-card { min-height: 148px; transition: all 0.3s ease; }
    .kpi-label { color: #495057; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; }
    .kpi-value { font-weight: 700; font-size: 1.75rem; margin: 0.25rem 0 0.5rem; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .kpi-sub { color: #6c757d; font-size: 0.75rem; margin: 0; }
    .kpi-icon-wrap { width: 64px; display: flex; align-items: center; justify-content: center; opacity: 0.15; }
    .kpi-icon { width: 48px; height: 48px; }

    /* Alerts */
    .stats-conseiller-scope .alert { margin-bottom: 0; font-size: 0.875rem; }
    .stats-conseiller-scope .alert h6 { font-weight: 600; margin-bottom: 0.75rem; }
    .alert-soft-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .alert-soft-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }

    /* Tableau */
    .table-flat td, .table-flat th { border-top: 1px solid #eef2f7; padding: 0.75rem; font-size: 0.875rem; vertical-align: middle; }
    .table-flat thead th { background: #f8f9fa; font-weight: 600; color: #495057; }
    .table-flat tbody tr:hover { background-color: #f8f9fa; }
    .table-flat small { color: #6c757d; }

    /* Badges */
    .badge { font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.5rem; }
    .badge.no-radius { border-radius: 0 !important; }
    .badge-soft-success { color: #16a34a; background-color: rgba(22,163,74,0.1); }
    .badge-soft-info { color: #0ea5e9; background-color: rgba(14,165,233,0.1); }
    .badge-soft-warning { color: #f59e0b; background-color: rgba(245,158,11,0.1); }

    /* Performance */
    .performance-details { background: #f8f9fa; border: 1px solid #e9ecef; }
    .performance-details h6 { color: #495057; font-weight: 600; font-size: 0.875rem; }
    .performance-details .d-flex { margin-bottom: 0.25rem; }
    .performance-breakdown { background: #f8f9fa; border: 1px solid #e9ecef; font-size: 0.8rem; }

    /* Mini stats */
    .mini-stats h6 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; }
    .mini-stats p { font-size: 0.75rem; margin-bottom: 0; }

    /* Dropdowns & Buttons */
    .dropdown-menu { border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); min-width: 160px; }
    .dropdown-item { padding: 0.5rem 1rem; font-size: 0.875rem; }
    .dropdown-item:hover { background-color: #f3f4f6; color: #495057; }
    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
    .btn-group .btn + .btn { margin-left: -1px; }
    .btn-outline-primary { color: #007bff; border-color: #007bff; }
    .btn-outline-primary:hover { color: #fff; background-color: #007bff; border-color: #007bff; }
    .btn-outline-success { color: #28a745; border-color: #28a745; }
    .btn-outline-success:hover { color: #fff; background-color: #28a745; border-color: #28a745; }
    .btn-outline-danger { color: #dc3545; border-color: #dc3545; }
    .btn-outline-danger:hover { color: #fff; background-color: #dc3545; border-color: #dc3545; }
    .btn-outline-info { color: #17a2b8; border-color: #17a2b8; }
    .btn-outline-info:hover { color: #fff; background-color: #17a2b8; border-color: #17a2b8; }

    /* Footer */
    .footer { border-top: 1px solid #e5e7eb; background: #f8f9fa; color: #6c757d; margin-top: 2rem; padding: 1rem 0; font-size: 0.875rem; }

    /* Utilitaires */
    .text-success { color: #28a745 !important; }
    .text-info { color: #17a2b8 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-primary { color: #007bff !important; }
    .text-danger { color: #dc3545 !important; }
    .text-muted { color: #6c757d !important; }
    .text-secondary { color: #6c757d !important; }
    .border-right { border-right: 1px solid #dee2e6 !important; }
    .h-100 { height: 100% !important; }
    .flex-grow-1 { flex-grow: 1 !important; }

    /* Responsive */
    @media (max-width: 768px) {
        .kpi-row .col-md-6 { margin-bottom: 1rem; }
        .kpi-card { min-height: 120px; }
        .kpi-icon-wrap { width: 48px; }
        .kpi-icon { width: 32px; height: 32px; }
        .kpi-value { font-size: 1.5rem; }
        .mini-stats h6 { font-size: 1rem; }
        .card-title { font-size: 1rem; }
    }
    @media (max-width: 576px) {
        .stats-conseiller-scope .container-fluid { padding-left: 10px; padding-right: 10px; }
        .btn-group { flex-wrap: wrap; }
        .btn-group .btn { margin-bottom: 0.25rem; font-size: 0.75rem; }
        .kpi-value { font-size: 1.25rem; }
        .performance-breakdown { font-size: 0.7rem; }
        .table-flat td, .table-flat th { padding: 0.5rem; font-size: 0.8rem; }
    }
</style>
@endsection
