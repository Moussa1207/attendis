@extends('dashboard.master')

@section('title', 'Statistiques Système')

@section('contenu')
<div class="page-wrapper stats-scope">
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
                        <a class="dropdown-item" href="#"><i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Profil</a>
                        <a class="dropdown-item" href="{{ route('layouts.setting') }}"><i data-feather="settings" class="align-self-center icon-xs icon-dual mr-1"></i> Paramètres</a>
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
                {{-- Bouton "Nouvel Utilisateur" retiré sur cette page --}}
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
                                <h4 class="page-title">Statistiques Système</h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Statistiques</li>
                                </ol>
                            </div>
                            <div class="col-auto align-self-center">
                                <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                    <span class="ay-name" id="Day_Name">Aujourd'hui:</span>&nbsp;
                                    <span id="Select_date">{{ now()->format('d M') }}</span>
                                    <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary" id="refreshBtn" onclick="refreshStats(undefined, false, true)" title="Rafraîchir">
                                    <i data-feather="refresh-cw" class="align-self-center icon-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Globaux -->
            <div class="row align-items-stretch kpi-row mb-4">
                <div class="col-md-6 col-lg-3 d-flex">
                    <div class="card kpi-card flex-grow-1">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="kpi-label">Tickets Aujourd'hui</p>
                                    <h3 class="kpi-value" id="totalTickets">—</h3>
                                    <p class="kpi-sub">
                                        <span class="text-success" id="ticketsTrend"><i class="mdi mdi-trending-up"></i></span> vs hier
                                    </p>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="list" class="kpi-icon"></i>
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
                                    <p class="kpi-label">Temps d'Attente Moyen</p>
                                    <h3 class="kpi-value" id="avgWaitTime">—</h3>
                                    <p class="kpi-sub">
                                        <span class="text-success" id="waitTimeTrend"><i class="mdi mdi-trending-down"></i></span> Amélioration
                                    </p>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="clock" class="kpi-icon"></i>
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
                                    <p class="kpi-label">Taux de Résolution</p>
                                    <h3 class="kpi-value" id="resolutionRate">—</h3>
                                    <p class="kpi-sub">
                                        <span class="text-success" id="resolutionTrend"><i class="mdi mdi-trending-up"></i></span> Performance
                                    </p>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="check-circle" class="kpi-icon"></i>
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
                                    <p class="kpi-label">Conseillers Actifs</p>
                                    <h3 class="kpi-value" id="activeAdvisors">—</h3>
                                    <p class="kpi-sub">
                                        <span class="text-success" id="advisorsTrend"><i class="mdi mdi-trending-up"></i></span> qu'hier
                                    </p>
                                </div>
                                <div class="kpi-icon-wrap">
                                    <i data-feather="users" class="kpi-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Performance Conseillers -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card h-100">
                        <div class="card-header header-flat">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title mb-1">Performance des Conseillers</h4>
                                    <p class="text-muted mb-0">Suivi en temps réel des performances individuelles</p>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown">
                                            Aujourd'hui<i class="las la-angle-down ml-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#" onclick="changeConseillerPeriod('today')">Aujourd'hui</a>
                                            <a class="dropdown-item" href="#" onclick="changeConseillerPeriod('week')">Cette semaine</a>
                                            <a class="dropdown-item" href="#" onclick="changeConseillerPeriod('month')">Ce mois</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row mb-4 text-center kpi-mini">
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <div>
                                        <h3 class="mini-value text-success" id="conseillerTicketsTraites">—</h3>
                                        <p class="text-muted mb-0">Tickets Traités</p>
                                        <small class="text-muted" id="conseillerMoyenne">Moy: —</small>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <div>
                                        <h3 class="mini-value text-primary" id="conseillerTempsMoyen">—</h3>
                                        <p class="text-muted mb-0">Temps Moyen</p>
                                        <small class="text-success">Objectif: ≤ 15 min</small>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <div>
                                        <h3 class="mini-value text-success" id="conseillerTauxResolution">—</h3>
                                        <p class="text-muted mb-0">Taux Résolution</p>
                                        <small class="text-success">Objectif: ≥ 85%</small>
                                    </div>
                                </div>
                                <!-- 4e KPI supprimé volontairement -->
                            </div>

                            <div class="table-responsive">
                                <table class="table mb-0 table-flat">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="border-top-0">Conseiller</th>
                                            <th class="border-top-0">Statut</th>
                                            <th class="border-top-0">Traités</th>
                                            <th class="border-top-0">Résolus/Refusés</th>
                                            <th class="border-top-0">Temps Moyen</th>
                                            <th class="border-top-0">Performance</th>
                                            <th class="border-top-0">Dernier Ticket</th>
                                        </tr>
                                    </thead>
                                    <tbody id="conseillerTableBody">
                                        <!-- rendu dynamique -->
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Services et File d'attente -->
            <div class="row align-items-stretch mb-4">
                <div class="col-lg-6 d-flex">
                    <div class="card h-100 flex-grow-1">
                        <div class="card-header header-flat">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title mb-0">Performance par Service</h4>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown">
                                            Aujourd'hui<i class="las la-angle-down ml-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#" onclick="refreshStats('today')">Aujourd'hui</a>
                                            <a class="dropdown-item" href="#" onclick="refreshStats('week')">Cette semaine</a>
                                            <a class="dropdown-item" href="#" onclick="refreshStats('month')">Ce mois</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div id="servicesChart" class="apex-charts" style="height: 320px;"></div>

                            <div class="table-responsive mt-3">
                                <table class="table table-sm mb-0 table-flat">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Service</th>
                                            <th>Tickets</th>
                                            <th>Résolution</th>
                                            <th>Attente Moy.</th>
                                        </tr>
                                    </thead>
                                    <tbody data-services-table-body>
                                        <!-- rendu dynamique -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-flex">
                    <div class="card h-100 flex-grow-1">
                        <div class="card-header header-flat">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title mb-0">File d'Attente (FIFO)</h4>
                                </div>
                                <div class="col-auto">
                                    <span class="badge badge-soft-info no-radius">
                                        <i class="mdi mdi-clock-outline mr-1"></i>Temps réel
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="row text-center mb-3">
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <h4 class="text-warning" id="ticketsEnAttente">—</h4>
                                    <p class="text-muted mb-0">En Attente</p>
                                </div>
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <h4 class="text-info" id="ticketsEnCours">—</h4>
                                    <p class="text-muted mb-0">En Cours</p>
                                </div>
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <h4 class="text-success" id="ticketsTermines">—</h4>
                                    <p class="text-muted mb-0">Terminés</p>
                                </div>
                                <div class="col-6 col-md-3">
                                    <h4 class="text-primary" id="tempsAttenteEstime">—</h4>
                                    <p class="text-muted mb-0">Attente Est.</p>
                                </div>
                            </div>

                            <div id="queueChart" class="apex-charts" style="height: 240px;"></div>

                            <div class="mt-3">
                                <div class="alert alert-soft-info mb-0">
                                    <h6 class="mb-1"><i class="mdi mdi-information-outline mr-1"></i>File d'attente chronologique</h6>
                                    <p class="mb-0">Premier arrivé, premier servi avec résolution binaire</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tendances + Alertes -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header header-flat d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Tendances Hebdomadaires</h4>
                            <div class="dropdown">
                                <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown">
                                   Cette semaine<i class="las la-angle-down ml-1"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#" onclick="refreshStats('week')">Cette semaine</a>
                                    <a class="dropdown-item" href="#" onclick="refreshStats('lastweek')">Semaine dernière</a>
                                    <a class="dropdown-item" href="#" onclick="refreshStats('month')">Ce mois</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="trendsChart" class="apex-charts" style="height: 320px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header header-flat d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Alertes Système</h4>
                            <span class="badge badge-soft-warning no-radius" id="alertCount">0</span>
                        </div>
                        <div class="card-body" id="alertsContainer">
                            <!-- rendu dynamique -->
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis - Système de Gestion de File d'Attente
        </footer>
    </div>
</div>

<!-- Expose les routes au JS -->
<script>
window.APP = {
  csrf: "{{ csrf_token() }}",
  routes: {
    stats: "{{ route('admin.api.stats') }}",
    advancedStats: "{{ route('admin.api.advanced-stats') }}"
  },
  debug: {{ config('app.debug') ? 'true' : 'false' }}
};
</script>

<!-- Scripts -->
<script>
// ========= Configuration et variables globales =========
let servicesChart, queueChart, trendsChart;
let currentPeriod = 'today';
let lastUpdateTimestamp = Date.now();
let syncManager = null;
let isPageInitialized = false;

// ========= Gestion des notifications =========
function showNotification(message, type = 'info', duration = 2500) {
    try {
        const alertTypes = { 
            'success': 'alert-success', 
            'error': 'alert-danger', 
            'warning': 'alert-warning', 
            'info': 'alert-info' 
        };
        const icons = { 
            'success': 'mdi-check-circle', 
            'error': 'mdi-alert-circle', 
            'warning': 'mdi-alert', 
            'info': 'mdi-information' 
        };
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertTypes[type]} alert-dismissible fade show`;
        alert.style.cssText = `position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 420px;`;
        alert.innerHTML = `
            <i class="mdi ${icons[type]} mr-2"></i>${escapeHtml(message)}
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        document.body.appendChild(alert);
        setTimeout(() => { 
            if (alert && alert.parentElement) alert.remove(); 
        }, duration);
    } catch (e) {
        console.error('Erreur notification:', e);
    }
}

// ========= Gestion sécurisée des icônes Feather =========
function sanitizeFeatherIcons() {
    try {
        if (!window.feather || !feather.icons) return;
        const validIcons = Object.keys(feather.icons);
        document.querySelectorAll('[data-feather]').forEach(el => {
            const iconName = el.getAttribute('data-feather');
            if (!iconName || !validIcons.includes(iconName)) {
                el.setAttribute('data-feather', 'alert-circle');
            }
        });
    } catch (e) {
        // Silencieux - erreur non critique
    }
}

function safeFeatherReplace() {
    try {
        if (window.feather && typeof feather.replace === 'function') {
            sanitizeFeatherIcons();
            feather.replace();
        }
    } catch (e) {
        // Silencieux - erreur non critique
    }
}

// ========= Gestion des graphiques avec fallback =========
function createNoOpChart() {
    return {
        updateOptions: function() { return this; },
        updateSeries: function() { return this; },
        render: function() { return this; },
        destroy: function() { return this; }
    };
}

function initializeCharts() {
    if (!window.ApexCharts) {
        servicesChart = createNoOpChart();
        queueChart = createNoOpChart();
        trendsChart = createNoOpChart();
        return;
    }

    try {
        // Services Chart
        const servicesElement = document.querySelector("#servicesChart");
        if (servicesElement) {
            servicesChart = new ApexCharts(servicesElement, {
                chart: { type: 'donut', height: 320, foreColor: '#6b7280' },
                series: [],
                labels: [],
                colors: ['#28a745', '#ffc107', '#17a2b8', '#6f42c1', '#007bff', '#20c997'],
                legend: { position: 'bottom' },
                dataLabels: { 
                    enabled: true, 
                    dropShadow: { enabled: false }, 
                    formatter: function(val) { return val.toFixed(1) + '%'; }
                },
                stroke: { width: 0 },
                noData: { text: 'Aucune donnée disponible' }
            });
            servicesChart.render();
        } else {
            servicesChart = createNoOpChart();
        }

        // Queue Chart
        const queueElement = document.querySelector("#queueChart");
        if (queueElement) {
            queueChart = new ApexCharts(queueElement, {
                chart: { 
                    type: 'area', 
                    height: 240, 
                    sparkline: { enabled: true }, 
                    foreColor: '#6b7280' 
                },
                series: [{ name: 'En attente', data: [] }],
                colors: ['#ffc107'],
                fill: { opacity: 0.25 },
                stroke: { curve: 'smooth', width: 2 },
                tooltip: { 
                    x: { show: false }, 
                    y: { title: { formatter: function() { return 'Tickets: '; } } }
                },
                noData: { text: 'Aucune donnée disponible' }
            });
            queueChart.render();
        } else {
            queueChart = createNoOpChart();
        }

        // Trends Chart
        const trendsElement = document.querySelector("#trendsChart");
        if (trendsElement) {
            trendsChart = new ApexCharts(trendsElement, {
                chart: { type: 'line', height: 320, foreColor: '#6b7280' },
                series: [
                    { name: 'Tickets traités', data: [] }, 
                    { name: 'Taux résolution (%)', data: [] }
                ],
                colors: ['#6f42c1', '#28a745'],
                xaxis: { categories: [] },
                yaxis: [
                    { title: { text: 'Tickets traités' } },
                    { 
                        opposite: true, 
                        title: { text: 'Taux résolution (%)' }, 
                        max: 100 
                    }
                ],
                stroke: { curve: 'smooth', width: 3 },
                legend: { position: 'top' },
                grid: { borderColor: '#eef2f7' },
                noData: { text: 'Aucune donnée disponible' }
            });
            trendsChart.render();
        } else {
            trendsChart = createNoOpChart();
        }

    } catch (e) {
        console.error('Erreur initialisation charts:', e);
        servicesChart = createNoOpChart();
        queueChart = createNoOpChart();
        trendsChart = createNoOpChart();
    }
}

// ========= Récupération des données avec gestion d'erreurs robuste =========
async function fetchJson(url) {
    try {
        const response = await fetch(url, { 
            credentials: 'same-origin', 
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        return data;
    } catch (e) {
        console.error('Erreur fetch:', url, e.message);
        throw e;
    }
}

// ========= Rafraîchissement principal (version robuste) =========
async function refreshStats(period = currentPeriod, silent = true, forceSync = false) {
    if (!isPageInitialized) {
        console.warn('Page non initialisée, ignorant le rafraîchissement');
        return;
    }

    currentPeriod = period || currentPeriod;
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) refreshBtn.classList.add('spinning');

    try {
        // Appel API stats principal
        const urlStats = `${APP.routes.stats}?period=${encodeURIComponent(currentPeriod)}&t=${Date.now()}${forceSync ? '&sync=1' : ''}`;
        const statsPayload = await fetchJson(urlStats);
        const stats = statsPayload.stats || statsPayload;

        // Mise à jour des KPIs (SAUF activeAdvisors qui sera calculé depuis le tableau)
        setText('totalTickets', stats.my_tickets_today, true);
        setText('avgWaitTime', (toNumber(stats.my_average_wait_time)).toFixed(1) + ' min');
        setText('resolutionRate', (toNumber(stats.my_resolution_rate_today)).toFixed(1) + '%');
        
        // Mise à jour file d'attente
        setText('ticketsEnAttente', stats.my_tickets_waiting ?? 0, true);
        setText('ticketsEnCours', stats.my_tickets_processing ?? 0, true);
        setText('ticketsTermines', stats.my_tickets_completed ?? 0, true);
        setText('tempsAttenteEstime', Math.round(toNumber(stats.my_average_wait_time)) + ' min');

        // Mise à jour sections
        updateServicesSection(stats.service_breakdown_today ?? []);
        updateQueueChart(stats.queue_sparkline, stats.my_tickets_waiting);
        updateTrendsChart(stats.trends_week);
        renderAlerts(stats.alerts || []);

        // Appel API conseillers
        const urlConseillers = `${APP.routes.advancedStats}?period=${encodeURIComponent(currentPeriod)}&t=${Date.now()}${forceSync ? '&sync=1' : ''}`;
        const conseillerData = await fetchJson(urlConseillers);
        
        if (conseillerData && conseillerData.success !== false) {
            updateConseillerSummary(conseillerData.summary || {}, conseillerData.conseillers || []);
            renderConseillers(conseillerData.conseillers || []); // Ceci calcule automatiquement activeAdvisors
        } else {
            // En cas d'échec des données conseillers, conserver le compteur existant
            console.warn('Échec récupération données conseillers');
        }

        lastUpdateTimestamp = Date.now();
        
        if (!silent) {
            showNotification('Données mises à jour' + (forceSync ? ' (synchronisées)' : ''), 'success');
        }

    } catch (e) {
        console.error('Erreur refreshStats:', e);
        
        // Gestion spécifique des erreurs sans spam de notifications
        if (!silent) {
            const errorMessage = e.message.includes('HTTP') ? 
                'Erreur de connexion au serveur' : 
                'Erreur lors du chargement des données';
            showNotification(errorMessage, 'error');
        }
        
    } finally {
        const refreshBtn2 = document.getElementById('refreshBtn');
        if (refreshBtn2) refreshBtn2.classList.remove('spinning');
        
        updateTime();
        safeFeatherReplace();
    }
}

// ========= Fonctions utilitaires =========
function setText(elementId, value, isNumber = false) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    if (isNumber) {
        const numValue = toNumber(value);
        element.textContent = numValue.toLocaleString('fr-FR');
    } else {
        element.textContent = value ?? '—';
    }
}

function toNumber(value) {
    const num = parseFloat(value);
    return isNaN(num) ? 0 : num;
}

function updateTime() {
    const dateElement = document.getElementById('Select_date');
    if (dateElement) {
        const now = new Date();
        dateElement.textContent = now.toLocaleDateString('fr-FR', { 
            day: '2-digit', 
            month: 'short' 
        });
    }
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

// ========= Mise à jour des sections spécialisées =========
function updateServicesSection(serviceBreakdown) {
  if (!Array.isArray(serviceBreakdown)) return;

  const labels = serviceBreakdown.map(r => String(r.label ?? r.name ?? 'Service inconnu'));
  const series = serviceBreakdown.map(r => toNumber(r.tickets ?? r.count ?? 0));

  try {
    servicesChart?.updateOptions?.({ labels });
    servicesChart?.updateSeries?.(series.length ? series : [0]);
  } catch (e) { console.error('Erreur MAJ services chart:', e); }

  const tbody = document.querySelector('[data-services-table-body]');
  if (!tbody) return;
  tbody.innerHTML = '';

  serviceBreakdown.forEach(s => {
    const resolution = toNumber(s.resolution ?? s.resolution_rate ?? 0);
    const waitAvg = toNumber(s.wait_avg ?? s.avg_wait ?? 0);
    const resolutionClass = resolution >= 85 ? 'text-success' : resolution >= 75 ? 'text-warning' : 'text-danger';
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><strong>${escapeHtml(String(s.label ?? s.name ?? 'Service inconnu'))}</strong></td>
      <td><span class="badge badge-primary no-radius">${toNumber(s.tickets ?? s.count ?? 0)}</span></td>
      <td><span class="${resolutionClass}">${resolution.toFixed(1)}%</span></td>
      <td>${waitAvg.toFixed(1)} min</td>`;
    tbody.appendChild(tr);
  });
}

function updateQueueChart(sparklineData, currentWaiting) {
  try {
    const queueData = Array.isArray(sparklineData) ? sparklineData : [toNumber(currentWaiting ?? 0)];
    queueChart?.updateSeries?.([{ name: 'En attente', data: queueData }]);
  } catch (e) { console.error('Erreur mise à jour queue chart:', e); }
}

function updateTrendsChart(trendsData) {
    if (!trendsData) return;
    
    try {
        if (trendsChart && trendsChart.updateOptions && trendsData.labels) {
            trendsChart.updateOptions({ 
                xaxis: { categories: trendsData.labels } 
            });
        }
        
        if (trendsChart && trendsChart.updateSeries) {
            trendsChart.updateSeries([
                { name: 'Tickets traités', data: trendsData.tickets || [] },
                { name: 'Taux résolution (%)', data: trendsData.resolution || [] }
            ]);
        }
    } catch (e) {
        console.error('Erreur mise à jour trends chart:', e);
    }
}

function renderAlerts(alerts) {
    const container = document.getElementById('alertsContainer');
    const counter = document.getElementById('alertCount');
    
    if (!container) return;
    
    container.innerHTML = '';
    if (counter) counter.textContent = alerts.length;

    if (!alerts.length) {
        container.innerHTML = `
            <div class="alert alert-soft-success mb-0">
                <h6 class="mb-1"><i class="mdi mdi-check-circle mr-1"></i>Aucune alerte</h6>
                <p class="mb-0">Tout fonctionne normalement</p>
            </div>
        `;
        return;
    }

    alerts.forEach(alert => {
        const typeClass = alert.type === 'warning' ? 'alert-soft-warning' : 
                         alert.type === 'error' ? 'alert-danger' : 'alert-soft-info';
        const icon = alert.type === 'warning' ? 'mdi-alert' : 
                    alert.type === 'error' ? 'mdi-alert-circle' : 'mdi-information';
        
        const alertElement = document.createElement('div');
        alertElement.className = `alert ${typeClass} mb-2`;
        alertElement.innerHTML = `
            <h6 class="mb-1"><i class="mdi ${icon} mr-1"></i>${escapeHtml(alert.title || 'Alerte')}</h6>
            <p class="mb-0">${escapeHtml(alert.message || '')}</p>
            ${alert.detail ? `<small class="d-block mt-1">${escapeHtml(alert.detail)}</small>` : ''}
        `;
        container.appendChild(alertElement);
    });
}

// ========= Gestion des conseillers =========
function updateConseillerSummary(summary, items) {
    setText('conseillerTicketsTraites', summary.tickets_traites ?? 0, true);
    setText('conseillerTempsMoyen', (toNumber(summary.temps_moyen_min)).toFixed(1) + ' min');
    setText('conseillerTauxResolution', (toNumber(summary.taux_resolution)).toFixed(1) + '%');
    
    const moyenne = toNumber(summary.moyenne_par_conseiller ?? summary.moyenne ?? 0);
    setText('conseillerMoyenne', 'Moy: ' + moyenne.toFixed(1) + ' par conseiller');

    // Le calcul des conseillers actifs sera fait après le rendu du tableau
    // pour garantir la cohérence avec les données affichées
}

function renderConseillers(items) {
    const tbody = document.getElementById('conseillerTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';

    if (!Array.isArray(items) || items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i data-feather="inbox" class="mr-1"></i>Aucun conseiller disponible
                </td>
            </tr>
        `;
        // Pas de conseillers = 0/0
        setText('activeAdvisors', '0/0');
        return;
    }

    items.forEach(conseiller => {
        const statusInfo = getConseillerStatusInfo(conseiller);
        const statusBadge = getRealUserStatusBadge(statusInfo.id, statusInfo.label);

        const traites = toNumber(conseiller.tickets_traites ?? conseiller.traites ?? 0);
        const resolus = toNumber(conseiller.resolus ?? conseiller.tickets_resolus ?? 0);
        const refuses = toNumber(conseiller.refuses ?? conseiller.tickets_refuses ?? 0);
        const tempsMoyen = toNumber(conseiller.temps_moyen_min ?? conseiller.temps_moyen ?? 0);
        const performance = toNumber(conseiller.performance ?? conseiller.taux_resolution ?? 0);

        const perfBadge = getPerformanceBadge(performance);
        const conseillerName = conseiller.name || conseiller.username || 
                              conseiller.user?.username || 'Conseiller inconnu';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${escapeHtml(conseillerName)}</strong></td>
            <td>${statusBadge}</td>
            <td><span class="badge badge-primary no-radius">${traites}</span></td>
            <td><span class="text-success">${resolus}</span> / <span class="text-danger">${refuses}</span></td>
            <td>${tempsMoyen.toFixed(1)} min</td>
            <td>${perfBadge}</td>
            <td>${escapeHtml(conseiller.dernier_ticket || '—')}</td>
        `;
        tbody.appendChild(tr);
    });

    // Calculer depuis le DOM maintenant que le tableau est rendu
    setTimeout(() => {
        recalcConseillersFromDOM();
    }, 50);
}

function getConseillerStatusInfo(conseiller) {
    const rawId = (conseiller.user?.status_id != null) ? conseiller.user.status_id :
                 (conseiller.user_status_id != null ? conseiller.user_status_id :
                 (conseiller.status_id != null ? conseiller.status_id : undefined));

    const rawText = conseiller.user?.status_text || conseiller.user?.status || 
                   conseiller.user_status_text || conseiller.status_text || conseiller.status;

    return normalizeStatusIdOrText(rawId, rawText);
}

function getPerformanceBadge(performance) {
    if (performance >= 90) return '<span class="badge badge-success no-radius">Excellent</span>';
    if (performance >= 75) return '<span class="badge badge-info no-radius">Bon</span>';
    if (performance >= 50) return '<span class="badge badge-warning no-radius">Moyen</span>';
    return '<span class="badge badge-danger no-radius">Faible</span>';
}

// ========= Gestion des statuts (normalisation) =========
function normalizeStatusIdOrText(valueId, valueText) {
    // Priorité à l'ID numérique
    let id = valueId;
    if (id !== undefined && id !== null && id !== '') {
        const numId = Number(id);
        if (!isNaN(numId) && [1, 2, 3].includes(numId)) {
            return { id: numId, label: getStatusTextFromId(numId) };
        }
    }
    
    // Fallback sur le texte
    const text = String(valueText || '').trim().toLowerCase();
    const isActif = /(actif|active|enabled|enable|on)\b/.test(text);
    const isAttente = /(en ?attente|attente|pending|wait|waiting|inactif|inactive|off)\b/.test(text);
    const isSuspendu = /(suspendu|suspend|suspended|disabled|bloqu|blocked|ban|banned)\b/.test(text);

    if (isActif) return { id: 2, label: 'Actif' };
    if (isSuspendu) return { id: 3, label: 'Suspendu' };
    if (isAttente) return { id: 1, label: 'En attente' };

    return { id: undefined, label: valueText || 'Inconnu' };
}

function getStatusTextFromId(statusId) {
    const statusMap = { 1: 'En attente', 2: 'Actif', 3: 'Suspendu' };
    return statusMap[Number(statusId)] || 'Inconnu';
}

function getRealUserStatusBadge(statusId, statusText) {
    const id = Number(statusId);
    
    if (id === 2) {
        return '<span class="badge badge-success no-radius"><i class="mdi mdi-check-circle mr-1"></i>Actif</span>';
    }
    if (id === 1) {
        return '<span class="badge badge-warning no-radius"><i class="mdi mdi-clock-outline mr-1"></i>En attente</span>';
    }
    if (id === 3) {
        return '<span class="badge badge-danger no-radius"><i class="mdi mdi-close-circle mr-1"></i>Suspendu</span>';
    }

    // Fallback basé sur le texte
    const text = (statusText || '').toLowerCase();
    if (/(actif|active|enabled|on)\b/.test(text)) {
        return '<span class="badge badge-success no-radius"><i class="mdi mdi-check-circle mr-1"></i>Actif</span>';
    }
    if (/(en ?attente|attente|pending|wait|inactive|inactif|off)\b/.test(text)) {
        return '<span class="badge badge-warning no-radius"><i class="mdi mdi-clock-outline mr-1"></i>En attente</span>';
    }
    if (/(suspendu|suspend|suspended|disabled|bloqu|blocked|ban|banned)\b/.test(text)) {
        return '<span class="badge badge-danger no-radius"><i class="mdi mdi-close-circle mr-1"></i>Suspendu</span>';
    }

    return `<span class="badge badge-secondary no-radius">${escapeHtml(statusText || 'Inconnu')}</span>`;
}

function calcActifsTotalsFromItems(items) {
    if (!Array.isArray(items)) return { actifs: 0, total: 0 };
    
    let actifs = 0;
    items.forEach(conseiller => {
        const statusInfo = getConseillerStatusInfo(conseiller);
        if (Number(statusInfo.id) === 2) actifs++;
    });
    
    return { actifs, total: items.length };
}

function recalcConseillersFromDOM() {
  const allRows = Array.from(document.querySelectorAll('#conseillerTableBody tr'));
  const rows = allRows.filter(r => !r.querySelector('td[colspan]'));
  if (!rows.length) { setText('activeAdvisors', '0/0'); return; }
  let actifs = 0;
  rows.forEach(row => {
    const badge = row.querySelector('td:nth-child(2) .badge');
    if (!badge) return;
    const txt = (badge.textContent || '').toLowerCase();
    const isActif = txt.includes('actif') || txt.includes('active') || badge.classList.contains('badge-success');
    if (isActif) actifs++;
  });
  setText('activeAdvisors', `${actifs}/${rows.length}`);
}


// ========= Gestionnaire de synchronisation entre pages =========
class ConseillerSyncManager {
    constructor() {
        this.eventChannel = null;
        try {
            if (typeof BroadcastChannel !== 'undefined') {
                this.eventChannel = new BroadcastChannel('conseiller_status_sync');
            }
        } catch (e) {
            // BroadcastChannel non supporté - utilisation du localStorage uniquement
        }
        this.setupEventListeners();
    }

    setupEventListeners() {
        if (this.eventChannel) {
            this.eventChannel.addEventListener('message', (event) => {
                if (event.data?.type === 'status_changed') {
                    this.handleStatusChange(event.data);
                }
            });
        }

        // Fallback avec localStorage
        window.addEventListener('storage', (e) => {
            if (e.key === 'conseiller_status_update' && e.newValue) {
                try {
                    const data = JSON.parse(e.newValue);
                    if (data.type === 'status_changed') {
                        this.handleStatusChange(data);
                    }
                } catch (err) {
                    // Erreur parsing - ignorée
                }
            }
        });
    }

    handleStatusChange(data) {
        const { userId, userName, newStatus, newStatusId, userTypeId } = data || {};
        
        if (userTypeId === 4) { // Conseillers seulement
            this.updateConseillerInTable(userId, userName, newStatus, newStatusId);
            // Recalculer depuis le DOM après mise à jour du statut
            setTimeout(() => {
                recalcConseillersFromDOM();
            }, 100);
            showNotification(`Statut de ${userName} mis à jour: ${newStatus}`, 'info');
            
            // Rafraîchissement différé pour synchroniser les autres données
            setTimeout(() => {
                refreshStats(currentPeriod, true, true);
            }, 400);
        }
    }

    updateConseillerInTable(userId, userName, newStatus, newStatusId) {
        const tableBody = document.getElementById('conseillerTableBody');
        if (!tableBody) return;

        const rows = tableBody.querySelectorAll('tr');
        for (let row of rows) {
            const nameCell = row.querySelector('td:first-child strong');
            if (nameCell && nameCell.textContent.trim() === userName) {
                const statusCell = row.querySelector('td:nth-child(2)');
                if (statusCell) {
                    statusCell.innerHTML = getRealUserStatusBadge(newStatusId, newStatus);
                    row.classList.add('status-updated');
                    setTimeout(() => row.classList.remove('status-updated'), 1500);
                }
                break;
            }
        }
    }

    emitStatusChange(userId, userName, newStatus, newStatusId, userTypeId) {
        const data = { 
            type: 'status_changed', 
            userId, 
            userName, 
            newStatus, 
            newStatusId, 
            userTypeId, 
            timestamp: Date.now() 
        };

        if (this.eventChannel) {
            this.eventChannel.postMessage(data);
        }

        // Fallback localStorage
        try {
            localStorage.setItem('conseiller_status_update', JSON.stringify(data));
            setTimeout(() => {
                localStorage.removeItem('conseiller_status_update');
            }, 1200);
        } catch (e) {
            // localStorage non disponible - ignoré
        }
    }
}

// ========= Fonctions publiques pour les événements onclick =========
function changeConseillerPeriod(period) {
    currentPeriod = period;
    refreshStats(currentPeriod, true, true);
}

// Fonction globale pour notifier les changements de statut (utilisée par d'autres pages)
window.notifyStatusChange = function(userId, userName, newStatus, newStatusId, userTypeId) {
    if (syncManager) {
        syncManager.emitStatusChange(userId, userName, newStatus, newStatusId, userTypeId);
    }
    setTimeout(() => refreshStats(currentPeriod, true, true), 400);
};

// ========= Auto-refresh intelligent =========
function startAutoRefresh() {
    let refreshInterval = 30000; // 30 secondes
    let consecutiveErrors = 0;
    
    const refreshLoop = async () => {
        if (!isPageInitialized) {
            setTimeout(refreshLoop, refreshInterval);
            return;
        }
        
        try {
            const shouldSync = (Date.now() - lastUpdateTimestamp) > 120000; // 2 minutes
            await refreshStats(currentPeriod, true, shouldSync);
            
            // Reset en cas de succès
            consecutiveErrors = 0;
            refreshInterval = 30000;
            
        } catch (e) {
            consecutiveErrors++;
            // Backoff exponentiel en cas d'erreurs répétées
            refreshInterval = Math.min(300000, 30000 * Math.pow(1.5, consecutiveErrors));
        }
        
        setTimeout(refreshLoop, refreshInterval);
    };
    
    setTimeout(refreshLoop, refreshInterval);
}

// ========= Initialisation de la page =========
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Initialisation du gestionnaire de sync
        syncManager = new ConseillerSyncManager();
        
        // Initialisation des icônes et graphiques
        safeFeatherReplace();
        initializeCharts();
        
        // Mise à jour de l'heure
        updateTime();
        
        // Marquer comme initialisé avant le premier chargement
        isPageInitialized = true;
        
        // Premier chargement des données
        refreshStats('today', true, true).then(() => {
            console.log('Application initialisée avec succès');
        }).catch((e) => {
            console.error('Erreur lors de l\'initialisation:', e);
        });
        
        // Démarrage de l'auto-refresh
        startAutoRefresh();
        
    } catch (e) {
        console.error('Erreur critique lors de l\'initialisation:', e);
    }
});

// Styles CSS pour l'animation du bouton refresh
const spinCSS = `
#refreshBtn.spinning svg { 
    animation: spin 0.8s linear infinite; 
    transform-origin: 50% 50%; 
}
@keyframes spin { 
    100% { transform: rotate(360deg); } 
}
`;
document.head.insertAdjacentHTML('beforeend', `<style>${spinCSS}</style>`);
</script>

<style>
/* ========= CONFIGURATION GÉNÉRALE ========= */
.stats-scope .container-fluid > .row { 
    margin-bottom: 1.25rem; 
}

.stats-scope .row.align-items-stretch > [class*="col-"] { 
    margin-bottom: 1rem; 
}

/* Suppression des border-radius */
.stats-scope .card,
.stats-scope .btn,
.stats-scope .badge,
.stats-scope .alert,
.stats-scope .dropdown-menu,
.stats-scope .form-control,
.stats-scope .table,
.stats-scope .page-title-box,
.stats-scope .thead-light th { 
    border-radius: 0 !important; 
}

/* ========= CARTES ET LAYOUT ========= */
.stats-scope .card { 
    border: 1px solid #e5e7eb; 
    box-shadow: none; 
    background: #fff; 
}

.header-flat { 
    background: #fafafa; 
    border-bottom: 1px solid #e5e7eb; 
}

/* ========= KPI CARDS ========= */
.kpi-card { 
    min-height: 148px; 
}

.kpi-label { 
    color: #111827; 
    font-weight: 600; 
    margin-bottom: 0.25rem; 
}

.kpi-value { 
    color: #0f172a; 
    font-weight: 700; 
    margin: 0.25rem 0 0.5rem; 
}

.kpi-sub { 
    color: #9ca3af; 
    margin: 0; 
}

.kpi-icon-wrap { 
    width: 64px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    opacity: 0.2;
}

.kpi-icon { 
    width: 48px; 
    height: 48px; 
    color: #6b7280; 
}

/* ========= MINI KPI CONSEILLERS ========= */
.kpi-mini .mini-value { 
    font-weight: 700; 
}

/* ========= TABLEAUX ========= */
.table-flat td, .table-flat th { 
    border-top: 1px solid #eef2f7; 
}

.table-flat thead th { 
    background: #f9fafb; 
}

/* ========= BADGES ========= */
.badge-soft-info { 
    color: #0ea5e9; 
    background-color: rgba(14,165,233,0.12); 
}

.badge-soft-warning { 
    color: #f59e0b; 
    background-color: rgba(245,158,11,0.12); 
}

.badge-soft-success { 
    color: #16a34a; 
    background-color: rgba(22,163,74,0.12); 
}

.no-radius { 
    border-radius: 0 !important; 
}

/* ========= ALERTES ========= */
.alert-soft-info { 
    color: #0c5460; 
    background-color: #d1ecf1; 
    border-color: #bee5eb; 
}

.alert-soft-success { 
    color: #155724; 
    background-color: #d4edda; 
    border-color: #c3e6cb; 
}

.alert-soft-warning { 
    color: #92400e; 
    background-color: #fef3c7; 
    border-color: #fde68a; 
}

/* ========= ANIMATIONS ========= */
.card { 
    animation: fadeInUp 0.4s ease-out; 
}

@keyframes fadeInUp { 
    from { 
        opacity: 0; 
        transform: translateY(6px); 
    } 
    to { 
        opacity: 1; 
        transform: translateY(0); 
    } 
}

/* Effet de mise à jour des statuts */
.status-updated { 
    background-color: rgba(40, 167, 69, 0.1) !important; 
    transition: background-color 0.3s ease; 
}

/* ========= RESPONSIVE ========= */
@media (max-width: 768px) { 
    .kpi-icon-wrap { 
        display: none; 
    }
}
</style>
@endsection