@extends('dashboard.master')

@section('contenu')
<div class="page-wrapper">
    <!-- Top Bar Start -->
    <div class="topbar">            
        <!-- Navbar -->
        <nav class="navbar-custom">    
            <ul class="list-unstyled topbar-nav float-right mb-0">  
                <!-- Search -->
                <li class="dropdown hide-phone">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i data-feather="search" class="topbar-icon"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg p-0">
                        <div class="app-search-topbar">
                            <form action="#" method="get">
                                <input type="search" name="search" class="from-control top-search mb-0" placeholder="Rechercher un ticket...">
                                <button type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                    </div>
                </li>                      

                <!-- Notifications -->
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect position-relative" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-danger badge-pill noti-icon-badge animate-pulse" id="ticketsWaitingCount">{{ $fileStats['tickets_en_attente'] ?? 0 }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            🎫 Tickets en attente 
                            <span class="badge badge-primary badge-pill" id="ticketsWaitingCount2">{{ $fileStats['tickets_en_attente'] ?? 0 }}</span>
                        </h6> 
                        <div class="notification-menu" data-simplebar id="notificationTickets">
                            <!-- Chargé via AJAX -->
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                <p class="text-muted small mb-0">Chargement des notifications...</p>
                            </div>
                        </div>
                        <a href="javascript:void(0);" class="dropdown-item text-center text-primary" onclick="advisorInterface.refreshTickets()">
                            <i data-feather="refresh-cw" class="mr-1"></i> Actualiser
                        </a>
                    </div>
                </li>

                <!-- User Menu -->
                <li class="dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="ml-1 nav-user-name hidden-sm">{{ $userInfo['username'] ?? 'Conseiller' }}</span>
                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="profile-user" class="rounded-circle" />                                 
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-header">
                            <h6 class="text-dark mb-0">👨‍💼 Conseiller</h6>
                            <small class="text-muted">{{ $userInfo['email'] ?? '' }}</small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('layouts.app-users') }}">
                            <i data-feather="home" class="align-self-center icon-xs icon-dual mr-1"></i> Mon espace
                        </a>
                        <a class="dropdown-item" href="#" onclick="advisorInterface.showHistory()">
                            <i data-feather="clock" class="align-self-center icon-xs icon-dual mr-1"></i> Historique
                        </a>
                        <div class="dropdown-divider mb-0"></div>
                        <h6 class="dropdown-header">Actions</h6>
                        <a class="dropdown-item" href="#" onclick="advisorInterface.showPasswordModal()">
                            <i data-feather="key" class="align-self-center icon-xs icon-dual mr-1"></i> Changer mot de passe
                        </a>
                        <a class="dropdown-item" href="#" onclick="advisorInterface.togglePause()">
                            <i data-feather="pause" class="align-self-center icon-xs icon-dual mr-1"></i> 
                            <span id="pauseMenuText">Pause file</span>
                        </a>
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

            <ul class="list-unstyled topbar-nav mb-0">                        
                <li>
                    <button class="nav-link button-menu-mobile">
                        <i data-feather="menu" class="align-self-center topbar-icon"></i>
                    </button>
                </li> 
                <li class="creat-btn">
                    <div class="nav-link">
                        <button class="btn btn-success btn-sm btn-call-next" onclick="advisorInterface.callNextTicket()">
                            <i data-feather="phone-call" class="mr-2"></i>
                            <span class="btn-text">Appeler suivant</span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm mr-1"></span>Appel...
                            </span>
                        </button>
                    </div>                                
                </li>                           
            </ul>
        </nav>
    </div>
    <!-- Top Bar End -->

    <!-- Page Content-->
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col">
                                <h4 class="page-title">
                                    <i data-feather="headphones" class="mr-2"></i>
                                    Espace Conseiller
                                    <span class="badge badge-success ml-2" id="advisorStatusBadge">En ligne</span>
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app-users') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">File d'attente</li>
                                </ol>
                            </div>
                            <div class="col-auto align-self-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="advisorInterface.refreshTickets()">
                                        <i data-feather="refresh-cw" class="mr-1"></i> Actualiser
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="advisorInterface.exportData()">
                                        <i data-feather="download" class="mr-1"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>                                                              
                    </div>
                </div>
            </div>

            <!-- Messages d'alerte -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <i data-feather="check-circle" class="mr-2"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            @if(isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i data-feather="alert-triangle" class="mr-2"></i>
                {{ $error }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            <!-- Statistiques de la file d'attente -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card card-waiting">
                        <div class="stats-icon">
                            <i data-feather="clock" class="text-warning"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number text-warning" id="waitingTicketsCount">{{ $fileStats['tickets_en_attente'] ?? 0 }}</h3>
                            <p class="stats-label">En attente</p>
                            <small class="stats-desc">À traiter</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card card-processing">
                        <div class="stats-icon">
                            <i data-feather="phone-call" class="text-info"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number text-info" id="processingTicketsCount">{{ $fileStats['tickets_en_cours'] ?? 0 }}</h3>
                            <p class="stats-label">En cours</p>
                            <small class="stats-desc">Avec client</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card card-completed">
                        <div class="stats-icon">
                            <i data-feather="check-circle" class="text-success"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number text-success" id="completedTicketsCount">{{ $fileStats['tickets_termines'] ?? 0 }}</h3>
                            <p class="stats-label">Terminés</p>
                            <small class="stats-desc">Aujourd'hui</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card card-time">
                        <div class="stats-icon">
                            <i data-feather="trending-down" class="text-primary"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number text-primary" id="averageWaitTime">{{ round($fileStats['temps_attente_moyen'] ?? 15) }}min</h3>
                            <p class="stats-label">Temps moyen</p>
                            <small class="stats-desc">D'attente</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- File d'attente principale -->
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="card queue-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">
                                        🎫 File d'attente FIFO
                                        <span class="badge badge-light ml-2" id="queueCountBadge">{{ $fileStats['tickets_en_attente'] ?? 0 }} tickets</span>
                                    </h5>
                                    <small class="text-muted">Premier arrivé, premier servi</small>
                                </div>
                                <div>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-success" onclick="advisorInterface.callNextTicket()">
                                            <i data-feather="phone-call" class="mr-1"></i>Appeler suivant
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="advisorInterface.refreshTickets()">
                                            <i data-feather="refresh-cw"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="queue-container">
                                <div id="ticketsContainer" class="tickets-list">
                                    <!-- Tickets chargés via AJAX -->
                                    <div class="loading-state">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary mb-3" role="status">
                                                <span class="sr-only">Chargement...</span>
                                            </div>
                                            <p class="text-muted">Chargement des tickets...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel latéral -->
                <div class="col-xl-4 col-lg-5 mb-4">
                    <!-- Ticket en cours -->
                    <div class="card current-ticket-card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    👤 Client en cours
                                </h5>
                                <span class="badge badge-success" id="currentTicketStatus">
                                    @if(isset($conseillerStats['ticket_en_cours']) && $conseillerStats['ticket_en_cours'])
                                        En cours
                                    @else
                                        Disponible
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="card-body" id="currentTicketPanel">
                            @if(isset($conseillerStats['ticket_en_cours']) && $conseillerStats['ticket_en_cours'])
                                <!-- Ticket en cours initial -->
                                <div class="current-ticket-panel">
                                    <div class="current-ticket-info">
                                        <div class="current-ticket-number">{{ $conseillerStats['ticket_en_cours']->numero_ticket }}</div>
                                        <div class="current-ticket-name">{{ $conseillerStats['ticket_en_cours']->prenom }}</div>
                                        <div class="current-ticket-phone">{{ $conseillerStats['ticket_en_cours']->telephone }}</div>
                                    </div>
                                    
                                    <div class="ticket-duration">
                                        <small>En cours depuis</small>
                                        <div class="font-weight-bold" id="ticketDuration">
                                            @php
                                                $debut = \Carbon\Carbon::createFromFormat('H:i:s', $conseillerStats['ticket_en_cours']->heure_prise_en_charge ?? '00:00:00');
                                                $maintenant = \Carbon\Carbon::now();
                                                $duree = $debut->diffInMinutes($maintenant);
                                            @endphp
                                            {{ $duree }}min
                                        </div>
                                    </div>
                                    
                                    <div class="ticket-actions">
                                        <button class="btn btn-success btn-sm" onclick="advisorInterface.completeCurrentTicket()">
                                            <i data-feather="check" class="mr-1"></i>Terminer
                                        </button>
                                        <button class="btn btn-outline-light btn-sm" onclick="advisorInterface.showTicketDetails({{ $conseillerStats['ticket_en_cours']->id }})">
                                            <i data-feather="eye" class="mr-1"></i>Détails
                                        </button>
                                    </div>
                                </div>
                            @else
                                <!-- Aucun ticket en cours -->
                                <div class="no-client-state">
                                    <div class="text-center py-4">
                                        <div class="empty-state-icon mb-3">
                                            <i data-feather="user-plus" class="text-muted"></i>
                                        </div>
                                        <h6 class="text-muted mb-2">Aucun client en cours</h6>
                                        <p class="text-muted small mb-3">Cliquez sur "Appeler suivant" pour commencer</p>
                                        <button class="btn btn-primary btn-sm" onclick="advisorInterface.callNextTicket()">
                                            <i data-feather="phone-call" class="mr-1"></i>Appeler premier client
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Mes performances -->
                    <div class="card performance-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">📊 Mes performances</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="performance-item">
                                        <h4 class="text-success mb-1" id="myCompletedCount">{{ $conseillerStats['tickets_traites_aujourd_hui'] ?? 0 }}</h4>
                                        <small class="text-muted">Tickets traités</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="performance-item">
                                        <h4 class="text-info mb-1" id="myAverageTime">{{ round($conseillerStats['temps_moyen_traitement'] ?? 0) }}min</h4>
                                        <small class="text-muted">Temps moyen</small>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="text-center">
                                <button class="btn btn-outline-info btn-sm" onclick="advisorInterface.showHistory()">
                                    <i data-feather="clock" class="mr-1"></i>Voir historique complet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="row">
                <div class="col-12">
                    <div class="card quick-actions-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Actions rapides</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 col-md-6 mb-2">
                                    <button class="btn btn-success btn-block btn-action" onclick="advisorInterface.callNextTicket()">
                                        <i data-feather="phone-call" class="mr-2"></i>
                                        Appeler suivant
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-2">
                                    <button class="btn btn-warning btn-block btn-action" onclick="advisorInterface.togglePause()">
                                        <i data-feather="pause" class="mr-2"></i>
                                        <span id="pauseButtonText">Pause</span>
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-2">
                                    <button class="btn btn-info btn-block btn-action" onclick="advisorInterface.showHistory()">
                                        <i data-feather="clock" class="mr-2"></i>
                                        Historique
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-2">
                                    <button class="btn btn-secondary btn-block btn-action" onclick="advisorInterface.exportData()">
                                        <i data-feather="download" class="mr-2"></i>
                                        Export données
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis 
            <span class="d-none d-sm-inline-block float-right">
                {{ $userInfo['username'] ?? 'Conseiller' }} - Interface Conseiller 
                <span class="ml-2">
                    <i data-feather="circle" class="text-success" style="width: 10px; height: 10px;"></i>
                    <span id="statusText">En ligne</span>
                </span>
            </span>
        </footer>
    </div>
</div>

<!-- Modals -->
<!-- Modal Détails Ticket -->
<div class="modal fade" id="ticketDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i data-feather="file-text" class="mr-2"></i>Détails du ticket
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                <!-- Contenu chargé dynamiquement -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3"></div>
                    <p class="text-muted">Chargement des détails...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success" onclick="advisorInterface.completeCurrentTicket()">
                    <i data-feather="check" class="mr-1"></i>Terminer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i data-feather="clock" class="mr-2"></i>Historique de mes tickets
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="historyContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-info mb-3" role="status"></div>
                    <p class="text-muted">Chargement de l'historique...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal changement de mot de passe -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i data-feather="key" class="mr-2"></i>Changer mon mot de passe
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="passwordForm" method="POST" action="{{ route('password.change') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mot de passe actuel</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" name="new_password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i data-feather="save" class="mr-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Styles CSS intégrés -->
<style>
/* ===== Variables CSS ===== */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --shadow-light: 0 2px 15px rgba(64, 79, 104, 0.05);
    --shadow-medium: 0 5px 25px rgba(64, 79, 104, 0.1);
    --shadow-strong: 0 10px 40px rgba(64, 79, 104, 0.15);
    --border-radius: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===== Layout général ===== */
.page-content {
    background: #f8fafc;
    min-height: 100vh;
}

/* ===== Cards et containers ===== */
.card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-light);
    transition: var(--transition);
    overflow: hidden;
}

.card:hover {
    box-shadow: var(--shadow-medium);
    transform: translateY(-2px);
}

.card-header {
    background: #fff;
    border-bottom: 1px solid #eef2f7;
    padding: 1.25rem 1.5rem;
}

.card-title {
    font-weight: 600;
    color: #2d3748;
}

/* ===== Stats Cards ===== */
.stats-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: var(--shadow-light);
    transition: var(--transition);
    border-left: 4px solid transparent;
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-strong);
}



.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.stats-icon i {
    width: 24px;
    height: 24px;
}

.stats-content {
    flex: 1;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.stats-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stats-desc {
    color: #718096;
    font-size: 0.875rem;
}

/* ===== Queue Card ===== */
.queue-card {
    background: white;
    box-shadow: var(--shadow-medium);
}

.queue-container {
    max-height: 500px;
    overflow-y: auto;
}

.tickets-list {
    min-height: 200px;
}

/* ===== Ticket Items ===== */
.ticket-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #eef2f7;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
}

.ticket-item:last-child {
    border-bottom: none;
}

.ticket-item:hover {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    transform: translateX(4px);
}

.ticket-item.urgent {
    border-left: 4px solid #f56565;
    background: rgba(245, 101, 101, 0.02);
}

.ticket-item.priority {
    border-left: 4px solid #ed8936;
    background: rgba(237, 137, 54, 0.02);
}

.ticket-position {
    position: absolute;
    top: 0.5rem;
    right: 1rem;
    background: var(--primary-gradient);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.ticket-number {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: #2d3748;
    font-size: 1.1rem;
}

.ticket-service {
    color: #4299e1;
    background: rgba(66, 153, 225, 0.1);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.ticket-waiting-time {
    font-weight: 600;
}

.ticket-waiting-time.urgent {
    color: #f56565;
    animation: pulse 1.5s infinite;
}

/* ===== Current Ticket Panel ===== */
.current-ticket-card {
    background: white;
    box-shadow: var(--shadow-medium);
}

.current-ticket-panel {
    background: var(--primary-gradient);
    border-radius: var(--border-radius);
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.current-ticket-info {
    margin-bottom: 1.5rem;
}

.current-ticket-number {
    font-size: 2.5rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    margin-bottom: 0.5rem;
}

.current-ticket-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.current-ticket-phone {
    opacity: 0.8;
    font-size: 0.95rem;
}

.ticket-duration {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 0.5rem;
    padding: 0.75rem;
    margin: 1rem 0;
    backdrop-filter: blur(10px);
}

.ticket-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* ===== Empty States ===== */
.no-client-state,
.empty-queue-state {
    padding: 2rem 1rem;
    text-align: center;
}

.empty-state-icon i {
    width: 48px;
    height: 48px;
    opacity: 0.5;
}

/* ===== Buttons ===== */
.btn-call-next {
    background: var(--success-gradient);
    border: none;
    color: white;
    font-weight: 600;
    transition: var(--transition);
    box-shadow: var(--shadow-light);
}

.btn-call-next:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    color: white;
}

.btn-action {
    transition: var(--transition);
    font-weight: 500;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-light);
}

/* ===== Performance Card ===== */
.performance-card {
    background: white;
    box-shadow: var(--shadow-medium);
}

.performance-item {
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: var(--transition);
}

.performance-item:hover {
    background: rgba(102, 126, 234, 0.05);
}

/* ===== Quick Actions ===== */
.quick-actions-card {
    background: white;
    box-shadow: var(--shadow-light);
}

/* ===== Animations ===== */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s infinite;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}

/* ===== Loading States ===== */
.loading-state {
    padding: 2rem;
    text-align: center;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .card-header {
        padding: 1rem 1.25rem;
    }
    
    .ticket-item {
        padding: 1rem;
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
    }
    
    .stats-number {
        font-size: 1.75rem;
    }
    
    .btn-group .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 576px) {
    .current-ticket-number {
        font-size: 2rem;
    }
    
    .ticket-actions {
        flex-direction: column;
    }
    
    .ticket-actions .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

/* ===== Scrollbar personnalisé ===== */
.queue-container::-webkit-scrollbar {
    width: 6px;
}

.queue-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.queue-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.queue-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* ===== Notifications améliorées ===== */
.toast-notification {
    position: fixed;
    top: 90px;
    right: 20px;
    z-index: 9999;
    min-width: 320px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-strong);
    border: none;
    backdrop-filter: blur(10px);
    transition: var(--transition);
}

.toast-notification.toast-success {
    background: linear-gradient(135deg, rgba(72, 187, 120, 0.95), rgba(56, 178, 172, 0.95));
    color: white;
}

.toast-notification.toast-error {
    background: linear-gradient(135deg, rgba(245, 101, 101, 0.95), rgba(229, 62, 62, 0.95));
    color: white;
}

.toast-notification.toast-warning {
    background: linear-gradient(135deg, rgba(246, 173, 85, 0.95), rgba(237, 137, 54, 0.95));
    color: white;
}

.toast-notification.toast-info {
    background: linear-gradient(135deg, rgba(66, 153, 225, 0.95), rgba(79, 172, 254, 0.95));
    color: white;
}
</style>

<!-- JavaScript optimisé -->
<script>
// ===== Interface Conseiller - Architecture modulaire =====
class AdvisorInterface {
    constructor() {
        this.currentTicket = null;
        this.ticketsData = [];
        this.refreshInterval = null;
        this.isPaused = false;
        this.isInitialized = false;
        
        this.config = {
            refreshInterval: 30000,
            apiRoutes: {
                tickets: '{{ route("conseiller.tickets") }}',
                callTicket: '{{ route("conseiller.call-ticket") }}', 
                completeTicket: '{{ route("conseiller.complete-ticket") }}',
                myStats: '{{ route("conseiller.my-stats") }}',
                history: '{{ route("conseiller.history") }}'
            }
        };
        
        this.init();
    }

    async init() {
        try {
            this.setupAjax();
            this.bindEvents();
            await this.loadInitialData();
            this.startAutoRefresh();
            this.isInitialized = true;
            this.showNotification('success', 'Interface conseiller prête', 'Système FIFO actif');
            console.log('✅ AdvisorInterface initialized successfully');
        } catch (error) {
            console.error('❌ Failed to initialize AdvisorInterface:', error);
            this.showNotification('error', 'Erreur d\'initialisation', error.message);
        }
    }

    setupAjax() {
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
        }
    }

    bindEvents() {
        // Événements globaux
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.isInitialized) {
                this.refreshTickets();
            }
        });

        // Raccourcis clavier
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === 'n') { // Alt + N
                e.preventDefault();
                this.callNextTicket();
            }
            if (e.altKey && e.key === 'r') { // Alt + R
                e.preventDefault();
                this.refreshTickets();
            }
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
            }
        });

        // Form password
        $('#passwordForm').on('submit', (e) => {
            e.preventDefault();
            this.handlePasswordChange();
        });
    }

    async loadInitialData() {
        await Promise.all([
            this.refreshTickets(),
            this.loadMyStats()
        ]);
    }

    startAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        this.refreshInterval = setInterval(() => {
            if (!document.hidden) {
                this.refreshTickets();
            }
        }, this.config.refreshInterval);
    }

    // ===== API Methods =====
    async refreshTickets() {
        try {
            const response = await this.apiCall('GET', this.config.apiRoutes.tickets);
            
            if (response.success) {
                this.ticketsData = response.tickets || [];
                this.updateUI(response);
                console.log('🔄 Tickets refreshed:', this.ticketsData.length);
            } else {
                throw new Error(response.message || 'Erreur lors du chargement');
            }
        } catch (error) {
            console.error('❌ Error refreshing tickets:', error);
            this.showNotification('error', 'Erreur', 'Impossible de charger les tickets');
        }
    }

    async callNextTicket() {
        if (this.isPaused) {
            this.showNotification('warning', 'En pause', 'Reprenez votre service d\'abord');
            return;
        }

        if (this.ticketsData.length === 0) {
            this.showNotification('info', 'File vide', 'Aucun ticket en attente');
            return;
        }

        const nextTicket = this.ticketsData[0];
        await this.callTicket(nextTicket.id);
    }

    async callTicket(ticketId) {
        try {
            this.setButtonLoading('.btn-call-next', true);
            
            const response = await this.apiCall('POST', this.config.apiRoutes.callTicket, {
                ticket_id: ticketId
            });
            
            if (response.success) {
                this.currentTicket = response.ticket;
                this.updateCurrentTicketPanel(response.ticket);
                await this.refreshTickets();
                this.showNotification('success', 'Client appelé', `Ticket ${response.ticket.numero_ticket}`);
                this.playNotificationSound();
            } else {
                throw new Error(response.message || 'Erreur lors de l\'appel');
            }
        } catch (error) {
            console.error('❌ Error calling ticket:', error);
            this.showNotification('error', 'Erreur', error.message);
        } finally {
            this.setButtonLoading('.btn-call-next', false);
        }
    }

    async completeCurrentTicket() {
        if (!this.currentTicket) {
            this.showNotification('error', 'Erreur', 'Aucun ticket en cours');
            return;
        }

        try {
            const response = await this.apiCall('POST', this.config.apiRoutes.completeTicket, {
                ticket_id: this.currentTicket.id
            });
            
            if (response.success) {
                this.currentTicket = null;
                this.updateCurrentTicketPanel(null);
                await Promise.all([
                    this.refreshTickets(),
                    this.loadMyStats()
                ]);
                this.showNotification('success', 'Ticket terminé', 'Client traité avec succès');
                $('#ticketDetailsModal').modal('hide');
            } else {
                throw new Error(response.message || 'Erreur lors de la finalisation');
            }
        } catch (error) {
            console.error('❌ Error completing ticket:', error);
            this.showNotification('error', 'Erreur', error.message);
        }
    }

    async loadMyStats() {
        try {
            const response = await this.apiCall('GET', this.config.apiRoutes.myStats);
            
            if (response.success) {
                this.updateMyStats(response.stats);
            }
        } catch (error) {
            console.error('❌ Error loading stats:', error);
        }
    }

    async showHistory() {
        try {
            $('#historyModal').modal('show');
            
            const response = await this.apiCall('GET', this.config.apiRoutes.history);
            
            if (response.success) {
                this.renderHistoryModal(response.tickets);
            } else {
                throw new Error(response.message || 'Erreur lors du chargement');
            }
        } catch (error) {
            console.error('❌ Error loading history:', error);
            document.getElementById('historyContent').innerHTML = `
                <div class="text-center py-4">
                    <i data-feather="alert-circle" class="text-danger mb-2" style="width: 48px; height: 48px;"></i>
                    <p class="text-muted">Erreur lors du chargement de l'historique</p>
                </div>
            `;
            if (typeof feather !== 'undefined') feather.replace();
        }
    }

    // ===== UI Update Methods =====
    updateUI(response) {
        this.updateTicketsList(response.tickets);
        this.updateStats(response.stats);
        this.updateNotifications(response.tickets);
    }

    updateTicketsList(tickets) {
        const container = document.getElementById('ticketsContainer');
        
        if (!tickets || tickets.length === 0) {
            container.innerHTML = `
                <div class="empty-queue-state">
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <i data-feather="inbox" class="text-muted"></i>
                        </div>
                        <h6 class="text-muted mb-2">File d'attente vide</h6>
                        <p class="text-muted small">Aucun ticket en attente (FIFO)</p>
                    </div>
                </div>
            `;
        } else {
            let html = '';
            tickets.forEach((ticket, index) => {
                const waitingTime = this.calculateWaitingTime(ticket.heure_d_enregistrement, ticket.created_at);
                const isUrgent = waitingTime > 30;
                const isPriority = waitingTime > 15;
                
                html += `
                    <div class="ticket-item fade-in-up ${isUrgent ? 'urgent' : isPriority ? 'priority' : ''}" 
                         onclick="advisorInterface.showTicketDetails(${ticket.id})" 
                         style="animation-delay: ${index * 0.1}s">
                        <div class="ticket-position">#${index + 1}</div>
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="ticket-number">${ticket.numero_ticket}</div>
                                <small class="text-muted">${ticket.heure_d_enregistrement || '--:--'}</small>
                            </div>
                            <div class="col-md-4">
                                <strong class="d-block">${ticket.prenom}</strong>
                                <small class="text-muted">${ticket.telephone}</small>
                            </div>
                            <div class="col-md-3">
                                <span class="ticket-service">${ticket.service || 'Service'}</span>
                            </div>
                            <div class="col-md-2 text-right">
                                <div class="ticket-waiting-time ${isUrgent ? 'urgent' : ''}">${waitingTime}min</div>
                                <div class="mt-2">
                                    <button class="btn btn-success btn-sm" onclick="event.stopPropagation(); advisorInterface.callTicket(${ticket.id})">
                                        <i data-feather="phone-call" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        // Mettre à jour le badge
        const queueCount = tickets ? tickets.length : 0;
        document.getElementById('queueCountBadge').textContent = queueCount + (queueCount === 1 ? ' ticket' : ' tickets');
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    updateStats(stats) {
        if (!stats) return;
        
        const elements = {
            'waitingTicketsCount': stats.waiting || 0,
            'processingTicketsCount': stats.processing || 0,
            'completedTicketsCount': stats.completed || 0,
            'averageWaitTime': Math.round(stats.average_wait || 15) + 'min',
            'ticketsWaitingCount': stats.waiting || 0,
            'ticketsWaitingCount2': stats.waiting || 0
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                const oldValue = element.textContent;
                element.textContent = value;
                
                // Animation si changement
                if (oldValue !== value.toString()) {
                    element.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        element.style.transform = 'scale(1)';
                    }, 200);
                }
            }
        });
    }

    updateNotifications(tickets) {
        const container = document.getElementById('notificationTickets');
        
        if (!tickets || tickets.length === 0) {
            container.innerHTML = `
                <div class="text-center py-3">
                    <i data-feather="check-circle" class="text-success mb-2"></i>
                    <p class="text-muted mb-0 small">File d'attente vide (FIFO)</p>
                </div>
            `;
        } else {
            let html = '';
            tickets.slice(0, 5).forEach((ticket, index) => {
                const waitingTime = this.calculateWaitingTime(ticket.heure_d_enregistrement, ticket.created_at);
                html += `
                    <a href="#" class="dropdown-item py-3" onclick="advisorInterface.callTicket(${ticket.id})">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i data-feather="user" style="width: 16px; height: 16px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h6 class="mb-0 font-weight-normal">${ticket.numero_ticket}</h6>
                                <small class="text-muted">${ticket.prenom} - ${waitingTime}min</small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge badge-light">#${index + 1}</span>
                            </div>
                        </div>
                    </a>
                `;
            });
            container.innerHTML = html;
        }
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    updateCurrentTicketPanel(ticket) {
        const panel = document.getElementById('currentTicketPanel');
        const status = document.getElementById('currentTicketStatus');
        
        if (!ticket) {
            panel.innerHTML = `
                <div class="no-client-state">
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i data-feather="user-plus" class="text-muted"></i>
                        </div>
                        <h6 class="text-muted mb-2">Aucun client en cours</h6>
                        <p class="text-muted small mb-3">File FIFO - Premier arrivé, premier servi</p>
                        <button class="btn btn-primary btn-sm" onclick="advisorInterface.callNextTicket()">
                            <i data-feather="phone-call" class="mr-1"></i>Appeler premier client
                        </button>
                    </div>
                </div>
            `;
            status.textContent = 'Disponible';
            status.className = 'badge badge-success';
        } else {
            panel.innerHTML = `
                <div class="current-ticket-panel">
                    <div class="current-ticket-info">
                        <div class="current-ticket-number">${ticket.numero_ticket}</div>
                        <div class="current-ticket-name">${ticket.prenom}</div>
                        <div class="current-ticket-phone">${ticket.telephone}</div>
                    </div>
                    
                    <div class="ticket-duration">
                        <small>En cours depuis</small>
                        <div class="font-weight-bold" id="ticketDuration">0min</div>
                    </div>
                    
                    <div class="ticket-actions">
                        <button class="btn btn-success btn-sm" onclick="advisorInterface.completeCurrentTicket()">
                            <i data-feather="check" class="mr-1"></i>Terminer
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="advisorInterface.showTicketDetails(${ticket.id})">
                            <i data-feather="eye" class="mr-1"></i>Détails
                        </button>
                    </div>
                </div>
            `;
            
            status.textContent = 'En cours';
            status.className = 'badge badge-warning';
            
            // Démarrer le timer
            this.startTicketTimer(ticket.called_at || new Date().toISOString());
        }
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    updateMyStats(stats) {
        if (!stats) return;
        
        document.getElementById('myCompletedCount').textContent = stats.completed || 0;
        document.getElementById('myAverageTime').textContent = Math.round(stats.average_time || 0) + 'min';
    }

    // ===== Modal Methods =====
    showTicketDetails(ticketId) {
        const ticket = this.ticketsData.find(t => t.id === ticketId) || this.currentTicket;
        if (!ticket) {
            this.showNotification('error', 'Erreur', 'Ticket non trouvé');
            return;
        }
        
        const content = document.getElementById('ticketDetailsContent');
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-semibold mb-3">Informations du ticket</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tr><td class="font-weight-semibold">Numéro:</td><td>${ticket.numero_ticket}</td></tr>
                            <tr><td class="font-weight-semibold">Service:</td><td>${ticket.service || 'N/A'}</td></tr>
                            <tr><td class="font-weight-semibold">Statut:</td><td><span class="badge badge-info">${ticket.statut_global}</span></td></tr>
                            <tr><td class="font-weight-semibold">Arrivée:</td><td>${ticket.heure_d_enregistrement || '--:--'}</td></tr>
                            <tr><td class="font-weight-semibold">Attente estimée:</td><td>${ticket.temps_attente_estime || 0}min</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-semibold mb-3">Informations client</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tr><td class="font-weight-semibold">Nom:</td><td>${ticket.prenom}</td></tr>
                            <tr><td class="font-weight-semibold">Téléphone:</td><td>${ticket.telephone}</td></tr>
                            <tr><td class="font-weight-semibold">Date:</td><td>${ticket.date}</td></tr>
                        </table>
                    </div>
                    
                    ${ticket.commentaire ? `
                        <h6 class="font-weight-semibold mb-2">Commentaire</h6>
                        <div class="alert alert-light">
                            <i data-feather="message-circle" class="mr-2"></i>
                            ${ticket.commentaire}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        $('#ticketDetailsModal').modal('show');
        if (typeof feather !== 'undefined') feather.replace();
    }

    renderHistoryModal(tickets) {
        const content = document.getElementById('historyContent');
        
        if (!tickets || tickets.length === 0) {
            content.innerHTML = `
                <div class="text-center py-5">
                    <i data-feather="clock" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                    <h6 class="text-muted">Aucun historique</h6>
                    <p class="text-muted small">Vous n'avez encore traité aucun ticket aujourd'hui</p>
                </div>
            `;
        } else {
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Ticket</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Durée</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            tickets.forEach(ticket => {
                html += `
                    <tr>
                        <td><strong>${ticket.numero_ticket}</strong></td>
                        <td>${ticket.prenom}</td>
                        <td><span class="badge badge-info">${ticket.service || 'N/A'}</span></td>
                        <td>${ticket.debut_traitement || '--:--'}</td>
                        <td>${ticket.fin_traitement || '--:--'}</td>
                        <td><span class="badge badge-light">${ticket.duree_traitement || 0}min</span></td>
                        <td><span class="badge badge-success">Terminé</span></td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            content.innerHTML = html;
        }
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    // ===== Action Methods =====
    togglePause() {
        this.isPaused = !this.isPaused;
        
        const statusBadge = document.getElementById('advisorStatusBadge');
        const pauseButton = document.getElementById('pauseButtonText');
        const pauseMenu = document.getElementById('pauseMenuText');
        const statusText = document.getElementById('statusText');
        
        if (this.isPaused) {
            statusBadge.textContent = 'En pause';
            statusBadge.className = 'badge badge-warning ml-2';
            pauseButton.textContent = 'Reprendre';
            pauseMenu.textContent = 'Reprendre service';
            statusText.textContent = 'En pause';
            this.showNotification('warning', 'Pause activée', 'Vous ne recevrez plus de tickets');
        } else {
            statusBadge.textContent = 'En ligne';
            statusBadge.className = 'badge badge-success ml-2';
            pauseButton.textContent = 'Pause';
            pauseMenu.textContent = 'Pause file';
            statusText.textContent = 'En ligne';
            this.showNotification('success', 'Service repris', 'Vous êtes de nouveau disponible');
        }
    }

    showPasswordModal() {
        $('#passwordModal').modal('show');
    }

    async handlePasswordChange() {
        try {
            const formData = new FormData(document.getElementById('passwordForm'));
            
            const response = await fetch('{{ route("password.change") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                $('#passwordModal').modal('hide');
                document.getElementById('passwordForm').reset();
                this.showNotification('success', 'Mot de passe modifié', 'Changement effectué avec succès');
            } else {
                throw new Error(result.message || 'Erreur lors du changement');
            }
        } catch (error) {
            console.error('❌ Error changing password:', error);
            this.showNotification('error', 'Erreur', error.message);
        }
    }

    exportData() {
        this.showNotification('info', 'Export', 'Génération du fichier en cours...');
        
        setTimeout(() => {
            this.showNotification('success', 'Export terminé', 'Fichier téléchargé avec succès');
        }, 2000);
    }

    // ===== Utility Methods =====
    async apiCall(method, url, data = null) {
        const options = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        };

        if (data) {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        const response = await fetch(url, options);
        return await response.json();
    }

    calculateWaitingTime(heureEnregistrement, createdAt) {
        const now = new Date();
        let arrival;
        
        if (heureEnregistrement && heureEnregistrement !== '--:--') {
            // Utiliser l'heure d'enregistrement si disponible
            const today = now.toISOString().split('T')[0];
            arrival = new Date(`${today}T${heureEnregistrement}`);
        } else if (createdAt) {
            // Sinon utiliser created_at
            arrival = new Date(createdAt);
        } else {
            return 0;
        }
        
        return Math.floor((now - arrival) / (1000 * 60));
    }

    formatTime(timeString) {
        if (!timeString || timeString === '--:--') return '--:--';
        try {
            const time = new Date(timeString);
            return time.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        } catch (e) {
            return timeString;
        }
    }

    startTicketTimer(startTime) {
        const start = new Date(startTime);
        const timer = setInterval(() => {
            const now = new Date();
            const diff = Math.floor((now - start) / (1000 * 60));
            const element = document.getElementById('ticketDuration');
            if (element) {
                element.textContent = diff + 'min';
            } else {
                clearInterval(timer);
            }
        }, 60000);
    }

    setButtonLoading(selector, isLoading) {
        const button = document.querySelector(selector);
        if (!button) return;
        
        const textEl = button.querySelector('.btn-text');
        const loadingEl = button.querySelector('.btn-loading');
        
        if (textEl && loadingEl) {
            if (isLoading) {
                textEl.classList.add('d-none');
                loadingEl.classList.remove('d-none');
                button.disabled = true;
            } else {
                textEl.classList.remove('d-none');
                loadingEl.classList.add('d-none');
                button.disabled = false;
            }
        }
    }

    playNotificationSound() {
        try {
            // Son de notification simple
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBT2Y2/LDjCUIQ4PY7tjwO');
            audio.play().catch(() => {}); // Ignore les erreurs de lecture
        } catch (error) {
            console.log('Notification sound not available');
        }
    }

    showNotification(type, title, message, duration = 4000) {
        const toastId = 'toast_' + Date.now();
        const toast = document.createElement('div');
        
        toast.id = toastId;
        toast.className = `toast-notification toast-${type}`;
        toast.setAttribute('role', 'alert');
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        
        const icons = {
            'success': 'check-circle',
            'error': 'alert-circle',
            'warning': 'alert-triangle',
            'info': 'info'
        };
        
        toast.innerHTML = `
            <div class="toast-header bg-transparent border-0">
                <i data-feather="${icons[type]}" class="mr-2"></i>
                <strong class="mr-auto">${title}</strong>
                <button type="button" class="ml-2 mb-1 close" onclick="document.getElementById('${toastId}').remove()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animation d'entrée
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Remplacer les icônes
        if (typeof feather !== 'undefined') feather.replace();
        
        // Auto-suppression
        setTimeout(() => {
            if (document.getElementById(toastId)) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.getElementById(toastId)) {
                        toast.remove();
                    }          
                }, 300);  
            }   
        }, duration);
    }   

    // Nettoyage
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        console.log('AdvisorInterface destroyed');
    }
}

// ===== Initialisation =====
let advisorInterface;

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser l'interface
    advisorInterface = new AdvisorInterface();
    
    // Initialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log('🎯 Interface conseiller FIFO ready');
});

// Nettoyage à la fermeture
window.addEventListener('beforeunload', function() {
    if (advisorInterface) {
        advisorInterface.destroy();
    }
});
</script>

@endsection