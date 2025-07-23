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
                        <span class="badge badge-danger badge-pill noti-icon-badge" id="ticketsWaitingCount">{{ $fileStats['tickets_en_attente'] ?? 0 }}</span>
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

            <!-- Statistiques de la file d'attente - AMÉLIORÉES -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card card-waiting">
                        <div class="stats-icon bg-warning">
                            <i data-feather="clock" class="text-white"></i>
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
                        <div class="stats-icon bg-info">
                            <i data-feather="phone-call" class="text-white"></i>
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
                        <div class="stats-icon bg-success">
                            <i data-feather="check-circle" class="text-white"></i>
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
                        <div class="stats-icon bg-primary">
                            <i data-feather="trending-down" class="text-white"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number text-primary" id="averageWaitTime">{{ $defaultWaitTime ?? 15 }}min</h3>
                            <p class="stats-label">Temps configuré</p>
                            <small class="stats-desc">Par l'admin</small>
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
                                         File d'attente FIFO
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
                                            <i data-feather="check" class="mr-1"></i>Traiter
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
                    <i data-feather="check" class="mr-1"></i>Traiter
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

<!-- Styles CSS améliorés -->
<style>
/* ===== Variables CSS ===== */
:root {
    --primary-color: #007bff;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --danger-color: #dc3545;
    --light-bg: #f8fafc;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 8px rgba(0,0,0,0.12);
    --transition: all 0.3s ease;
    --border-radius-sm: 4px;
    --border-radius-md: 6px;
}

/* ===== Layout général ===== */
.page-content {
    background: var(--light-bg);
    min-height: 100vh;
}

/* ===== Cards sans bords arrondis ===== */
.card {
    border: none;
    border-radius: var(--border-radius-sm);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    overflow: hidden;
}

.card:hover {
    box-shadow: var(--shadow-md);
}

.card-header {
    background: #fff;
    border-bottom: 1px solid #eef2f7;
    padding: 1.25rem 1.5rem;
    border-radius: var(--border-radius-sm) var(--border-radius-sm) 0 0;
}

/* ===== Stats Cards améliorées - VERSION ÉQUILIBRÉE ===== */
.stats-card {
    background: white;
    border-radius: var(--border-radius-sm);
    padding: 0.875rem 1.125rem;
    display: flex;
    align-items: center;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    border: 1px solid #e9ecef;
    min-height: 75px;
}

.stats-card:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.stats-icon {
    width: 30px;
    height: 30px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.stats-icon i {
    width: 14px;
    height: 14px;
}

.stats-content {
    flex: 1;
    min-width: 0;
}

.stats-number {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.125rem;
    line-height: 1.1;
}

.stats-label {
    font-weight: 500;
    color: #2d3748;
    margin-bottom: 0.125rem;
    font-size: 0.8rem;
    line-height: 1.2;
}

.stats-desc {
    color: #718096;
    font-size: 0.7rem;
    line-height: 1.2;
    font-weight: 400;
}

/* ===== Queue Card améliorée ===== */
.queue-card {
    background: white;
    box-shadow: var(--shadow-md);
    border-radius: var(--border-radius-sm);
}

.queue-container {
    max-height: 500px;
    overflow-y: auto;
}

.tickets-list {
    min-height: 200px;
}

/* ===== Ticket Items - Style plus soft avec correction chevauchement ===== */
.ticket-item {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
    background: white;
    margin-bottom: 1px;
    /* ✅ CORRECTION: Ajout de padding-right pour éviter le chevauchement */
    padding-right: 4.5rem; /* Espace pour le badge de position */
}

.ticket-item:last-child {
    border-bottom: none;
}

.ticket-item:hover {
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.02) 0%, rgba(0, 123, 255, 0.04) 100%);
    border-left: 3px solid var(--primary-color);
    padding-left: calc(1.5rem - 3px);
}

.ticket-item.urgent {
    border-left: 4px solid var(--danger-color);
    background: rgba(220, 53, 69, 0.02);
    padding-left: calc(1.5rem - 4px);
}

.ticket-item.priority {
    border-left: 4px solid var(--warning-color);
    background: rgba(255, 193, 7, 0.02);
    padding-left: calc(1.5rem - 4px);
}

/* ✅ CORRECTION: Badge de position repositionné pour éviter le chevauchement */
.ticket-position {
    position: absolute;
    top: 0.75rem;
    right: 1rem;
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10; /* S'assurer qu'il est au-dessus */
    min-width: 35px;
    text-align: center;
}

/* ✅ CORRECTION: Ajustement des colonnes pour éviter le chevauchement */
.ticket-item .col-md-2 {
    padding-right: 0.75rem; /* Moins de padding à droite */
}

.ticket-number {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: #2d3748;
    font-size: 1.1rem;
}

.ticket-service {
    color: var(--info-color);
    background: rgba(23, 162, 184, 0.1);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.ticket-waiting-time {
    font-weight: 600;
    font-size: 0.9rem;
}

.ticket-waiting-time.urgent {
    color: var(--danger-color);
}

.ticket-waiting-time.warning {
    color: var(--warning-color);
}

.ticket-waiting-time.normal {
    color: var(--success-color);
}

/* ===== Current Ticket Panel ===== */
.current-ticket-card {
    background: white;
    box-shadow: var(--shadow-md);
    border-radius: var(--border-radius-sm);
}

.current-ticket-panel {
    background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
    border-radius: var(--border-radius-md);
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
    border-radius: var(--border-radius-md);
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
    background: var(--success-color);
    border: none;
    color: white;
    font-weight: 600;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    border-radius: var(--border-radius-sm);
}

.btn-call-next:hover {
    background: #218838;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    color: white;
}

.btn-action {
    transition: var(--transition);
    font-weight: 500;
    border-radius: var(--border-radius-sm);
}

.btn-action:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

/* ===== Quick Actions ===== */
.quick-actions-card {
    background: white;
    box-shadow: var(--shadow-sm);
    border-radius: var(--border-radius-sm);
}

/* ===== Loading States ===== */
.loading-state {
    padding: 2rem;
    text-align: center;
}

/* ===== Badges de statut avec couleurs appropriées ===== */
.status-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    border-radius: 12px;
    font-weight: 600;
}

.status-nouveau {
    background: #d4edda;
    color: #155724;
}

.status-moyen {
    background: #fff3cd;
    color: #856404;
}

.status-urgent {
    background: #f8d7da;
    color: #721c24;
}

/* ===== Responsive amélioré ===== */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
        padding: 1rem;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    .ticket-item {
        padding: 1rem;
        padding-right: 4rem; /* ✅ Ajustement mobile */
    }
    
    .ticket-position {
        right: 0.5rem;
        padding: 0.2rem 0.6rem;
        font-size: 0.7rem;
        min-width: 30px;
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
    }
    
    .stats-number {
        font-size: 1.75rem;
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
    
    .ticket-item {
        padding-right: 3.5rem; /* ✅ Encore moins d'espace sur très petit écran */
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
    border-radius: var(--border-radius-md);
    box-shadow: var(--shadow-md);
    border: none;
    backdrop-filter: blur(10px);
    transition: var(--transition);
}

.toast-notification.toast-success {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.95), rgba(40, 167, 69, 0.9));
    color: white;
}

.toast-notification.toast-error {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.95), rgba(220, 53, 69, 0.9));
    color: white;
}

.toast-notification.toast-warning {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.95), rgba(255, 193, 7, 0.9));
    color: #212529;
}

.toast-notification.toast-info {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.95), rgba(23, 162, 184, 0.9));
    color: white;
}
</style>

<!-- JavaScript amélioré -->
<script>
// ===== Interface Conseiller - Version améliorée =====
class AdvisorInterface {
    constructor() {
        this.currentTicket = null;
        this.ticketsData = [];
        this.refreshInterval = null;
        this.isPaused = false;
        this.isInitialized = false;
        this.autoRefreshEnabled = false;
        
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
            this.isInitialized = true;
            this.showNotification('success', 'Interface conseiller prête', 'Utilisez "Actualiser" pour rafraîchir');
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
        document.addEventListener('visibilitychange', () => {
            console.log('🔄 Page visibility changed - no auto refresh');
        });

        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                this.callNextTicket();
            }
            if (e.altKey && e.key === 'r') {
                e.preventDefault();
                this.refreshTickets();
            }
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
            }
        });

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

    toggleAutoRefresh() {
        if (this.autoRefreshEnabled) {
            this.stopAutoRefresh();
            this.showNotification('info', 'Auto-refresh désactivé', 'Utilisez le bouton pour actualiser');
        } else {
            this.startAutoRefresh();
            this.showNotification('info', 'Auto-refresh activé', 'Actualisation toutes les 30s');
        }
    }

    startAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        this.autoRefreshEnabled = true;
        this.refreshInterval = setInterval(() => {
            if (!document.hidden && this.autoRefreshEnabled) {
                this.refreshTickets();
            }
        }, this.config.refreshInterval);
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
        this.autoRefreshEnabled = false;
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

    // ✅ CORRECTION: Fonction pour formater l'heure de prise de tickets
    formatArrivalTime(ticket) {
        // ✅ PRIORITÉ 1: Utiliser heure_d_enregistrement (heure exacte de prise de ticket à l'accueil)
        if (ticket.heure_d_enregistrement && 
            ticket.heure_d_enregistrement !== '--:--' && 
            ticket.heure_d_enregistrement !== null &&
            ticket.heure_d_enregistrement.trim() !== '') {
            
            // Formatter l'heure si elle est au format H:i:s
            try {
                // Si c'est déjà au bon format H:i, le retourner tel quel
                if (ticket.heure_d_enregistrement.match(/^\d{2}:\d{2}$/)) {
                    return ticket.heure_d_enregistrement;
                }
                
                // Si c'est au format H:i:s, extraire H:i
                if (ticket.heure_d_enregistrement.match(/^\d{2}:\d{2}:\d{2}$/)) {
                    return ticket.heure_d_enregistrement.substring(0, 5);
                }
                
                // Essayer de parser comme une date/heure complète
                const time = new Date(`1970-01-01T${ticket.heure_d_enregistrement}`);
                if (!isNaN(time.getTime())) {
                    return time.toLocaleTimeString('fr-FR', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                }
                
                return ticket.heure_d_enregistrement;
            } catch (e) {
                console.log('Erreur formatage heure_d_enregistrement:', e);
                return ticket.heure_d_enregistrement;
            }
        }
        
        // ✅ PRIORITÉ 2: Fallback sur created_at si heure_d_enregistrement n'est pas disponible
        if (ticket.created_at) {
            try {
                const date = new Date(ticket.created_at);
                if (!isNaN(date.getTime())) {
                    return date.toLocaleTimeString('fr-FR', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                }
            } catch (e) {
                console.log('Erreur formatage created_at:', e);
            }
        }
        
        // ✅ PRIORITÉ 3: Fallback final sur le champ date
        if (ticket.date) {
            try {
                const date = new Date(ticket.date);
                if (!isNaN(date.getTime())) {
                    return date.toLocaleTimeString('fr-FR', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                }
            } catch (e) {
                console.log('Erreur formatage date:', e);
            }
        }
        
        // Fallback ultime - ne devrait jamais arriver
        return '--:--';
    }

    // ⚠️ IMPORTANT: Vérifier côté serveur (route conseiller.tickets)
    // Les champs suivants doivent être inclus dans la réponse JSON :
    // - prenom (depuis la base de données, ne doit jamais être vide)
    // - date (date de création du ticket)
    // - telephone (numéro du client)
    // - heure_d_enregistrement (heure exacte de prise de ticket)
    // - service (nom du service)
    // Si ces champs sont vides, vérifier la requête SQL côté serveur !

    // ✅ NOUVELLE FONCTION: Validation et nettoyage des données ticket
    validateTicketData(ticket) {
        return {
            id: ticket.id || 0,
            numero_ticket: ticket.numero_ticket || 'N/A',
            prenom: ticket.prenom && ticket.prenom.trim() !== '' ? ticket.prenom.trim() : 'Nom non renseigné',
            telephone: ticket.telephone && ticket.telephone.trim() !== '' ? ticket.telephone.trim() : 'Téléphone non renseigné',
            date: ticket.date || 'Date non définie', // ✅ Date du jour de la demande
            service: ticket.service || 'Service non défini',
            temps_attente_estime: ticket.temps_attente_estime || 0, // ✅ Temps d'attente estimé depuis la DB
            commentaire: ticket.commentaire || null
        };
    }

    // ✅ CORRECTION: Fonction updateTicketsList modifiée
    updateTicketsList(tickets) {
        const container = document.getElementById('ticketsContainer');
        
        // ✅ Debug: Vérifier les données reçues
        if (tickets && tickets.length > 0) {
            console.log('📊 Données tickets reçues:', tickets[0]);
            console.log('🔍 Champs importants:', {
                prenom: tickets[0].prenom,
                date: tickets[0].date,
                telephone: tickets[0].telephone,
                service: tickets[0].service,
                temps_attente_estime: tickets[0].temps_attente_estime,
                created_at: tickets[0].created_at
            });
        }
        
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
                // ✅ Valider et nettoyer les données du ticket
                const validatedTicket = this.validateTicketData(ticket);
                
                // ✅ Utiliser temps_attente_estime depuis la base de données
                const estimatedWaitTime = validatedTicket.temps_attente_estime;
                
                // ✅ Logique de statut basée sur le temps d'attente estimé de la DB
                let statusClass = 'normal';
                let statusText = 'Nouveau';
                let statusColor = 'text-success';
                
                if (estimatedWaitTime > 30) {
                    statusClass = 'urgent';
                    statusText = 'Urgent';
                    statusColor = 'text-danger';
                } else if (estimatedWaitTime > 15) {
                    statusClass = 'warning';
                    statusText = 'Moyen';
                    statusColor = 'text-warning';
                }
                
                html += `
                    <div class="ticket-item fade-in-up ${statusClass}" 
                         onclick="advisorInterface.showTicketDetails(${validatedTicket.id})" 
                         style="animation-delay: ${index * 0.1}s">
                        <div class="ticket-position">#${index + 1}</div>
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="ticket-number">${validatedTicket.numero_ticket}</div>
                                <small class="text-muted">Date: ${validatedTicket.date}</small>
                            </div>
                            <div class="col-md-4">
                                <strong class="d-block">${validatedTicket.prenom}</strong>
                                <small class="text-muted">${validatedTicket.telephone}</small>
                            </div>
                            <div class="col-md-3">
                                <span class="ticket-service">${validatedTicket.service}</span>
                                <div class="mt-1">
                                    <span class="status-badge status-${statusClass} ${statusColor}">${statusText}</span>
                                </div>
                            </div>
                            <div class="col-md-2 text-right">
                                <div class="ticket-waiting-time ${statusClass}">${estimatedWaitTime}min</div>
                                <div class="mt-2">
                                    <button class="btn btn-success btn-sm" onclick="event.stopPropagation(); advisorInterface.callTicket(${validatedTicket.id})">
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
            'waitingTicketsCount': stats.total_en_attente || 0,
            'processingTicketsCount': stats.total_en_cours || 0,
            'completedTicketsCount': stats.total_termines || 0,
            'ticketsWaitingCount': stats.total_en_attente || 0,
            'ticketsWaitingCount2': stats.total_en_attente || 0
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                const oldValue = element.textContent;
                element.textContent = value;
                
                if (oldValue !== value.toString()) {
                    element.style.transform = 'scale(1.1)';
                    element.style.transition = 'transform 0.2s ease';
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
                const validatedTicket = this.validateTicketData(ticket);
                const estimatedWaitTime = validatedTicket.temps_attente_estime;
                
                html += `
                    <a href="#" class="dropdown-item py-3" onclick="advisorInterface.callTicket(${validatedTicket.id})">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i data-feather="user" style="width: 16px; height: 16px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h6 class="mb-0 font-weight-normal">${validatedTicket.numero_ticket}</h6>
                                <small class="text-muted">${validatedTicket.prenom} - ${estimatedWaitTime}min</small>
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
            // ✅ Valider les données du ticket en cours
            const validatedTicket = this.validateTicketData(ticket);
            
            panel.innerHTML = `
                <div class="current-ticket-panel">
                    <div class="current-ticket-info">
                        <div class="current-ticket-number">${validatedTicket.numero_ticket}</div>
                        <div class="current-ticket-name">${validatedTicket.prenom}</div>
                        <div class="current-ticket-phone">${validatedTicket.telephone}</div>
                        <small class="text-muted">Date: ${validatedTicket.date}</small>
                    </div>
                    
                    <div class="ticket-duration">
                        <small>En cours depuis</small>
                        <div class="font-weight-bold" id="ticketDuration">0min</div>
                    </div>
                    
                    <div class="ticket-actions">
                        <button class="btn btn-success btn-sm" onclick="advisorInterface.completeCurrentTicket()">
                            <i data-feather="check" class="mr-1"></i>Traiter
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="advisorInterface.showTicketDetails(${validatedTicket.id})">
                            <i data-feather="eye" class="mr-1"></i>Détails
                        </button>
                    </div>
                </div>
            `;
            
            status.textContent = 'En cours';
            status.className = 'badge badge-warning';
            
            this.startTicketTimer(ticket.called_at || new Date().toISOString());
        }
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    updateMyStats(stats) {
        if (!stats && !stats.aujourdhui) return;
        
        const elements = document.getElementsByClassName('my-stats');
        if (elements.length > 0) {
            // Logique de mise à jour des stats personnelles
        }
    }

    // ===== Modal Methods =====
    showTicketDetails(ticketId) {
        const ticket = this.ticketsData.find(t => t.id === ticketId) || this.currentTicket;
        if (!ticket) {
            this.showNotification('error', 'Erreur', 'Ticket non trouvé');
            return;
        }

        // ✅ Valider les données du ticket
        const validatedTicket = this.validateTicketData(ticket);
        
        // ✅ Utiliser temps_attente_estime depuis la base de données
        const estimatedWaitTime = validatedTicket.temps_attente_estime;
        
        let statusBadge = '';
        let statusText = '';
        
        if (estimatedWaitTime > 30) {
            statusBadge = '<span class="badge badge-danger">Urgent</span>';
            statusText = 'Temps d\'attente élevé';
        } else if (estimatedWaitTime > 15) {
            statusBadge = '<span class="badge badge-warning">Moyen</span>';
            statusText = 'Temps d\'attente modéré';
        } else {
            statusBadge = '<span class="badge badge-success">Nouveau</span>';
            statusText = 'Client récent';
        }
        
        const content = document.getElementById('ticketDetailsContent');
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-semibold mb-3">Informations du ticket</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tr><td class="font-weight-semibold">Numéro:</td><td>${validatedTicket.numero_ticket}</td></tr>
                            <tr><td class="font-weight-semibold">Service:</td><td>${validatedTicket.service}</td></tr>
                            <tr><td class="font-weight-semibold">Statut:</td><td>${statusBadge}</td></tr>
                            <tr><td class="font-weight-semibold">Priorité:</td><td>${statusText}</td></tr>
                            <tr><td class="font-weight-semibold">Attente estimée:</td><td>${estimatedWaitTime}min</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-semibold mb-3">Informations client</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tr><td class="font-weight-semibold">Prénom:</td><td>${ticket.prenom || 'Non renseigné'}</td></tr>
                            <tr><td class="font-weight-semibold">Téléphone:</td><td>${ticket.telephone || 'Non renseigné'}</td></tr>
                            <tr><td class="font-weight-semibold">Date de demande:</td><td>${ticket.date || 'Non définie'}</td></tr>
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
                const validatedTicket = this.validateTicketData(ticket);
                
                html += `
                    <tr>
                        <td><strong>${validatedTicket.numero_ticket}</strong></td>
                        <td>${validatedTicket.prenom}</td>
                        <td><span class="badge badge-info">${validatedTicket.service}</span></td>
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
            window.open('{{ route("conseiller.export") }}', '_blank');
            this.showNotification('success', 'Export terminé', 'Fichier téléchargé avec succès');
        }, 1500);
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
        
        // ✅ CORRECTION: Utiliser heure_d_enregistrement en priorité (heure exacte de prise de ticket)
        if (heureEnregistrement && heureEnregistrement !== '--:--' && heureEnregistrement.trim() !== '') {
            try {
                const today = now.toISOString().split('T')[0];
                
                // Gérer différents formats d'heure
                let timeString = heureEnregistrement;
                
                // Si format H:i:s, garder tel quel
                if (timeString.match(/^\d{1,2}:\d{2}:\d{2}$/)) {
                    arrival = new Date(`${today}T${timeString}`);
                }
                // Si format H:i, ajouter les secondes
                else if (timeString.match(/^\d{1,2}:\d{2}$/)) {
                    arrival = new Date(`${today}T${timeString}:00`);
                }
                
                if (arrival && !isNaN(arrival.getTime())) {
                    return Math.max(0, Math.floor((now - arrival) / (1000 * 60)));
                }
            } catch (e) {
                console.log('Erreur calcul temps d\'attente avec heure_d_enregistrement:', e);
            }
        }
        
        // ✅ FALLBACK: Utiliser created_at si heure_d_enregistrement n'est pas disponible
        if (createdAt) {
            try {
                arrival = new Date(createdAt);
                if (!isNaN(arrival.getTime())) {
                    return Math.max(0, Math.floor((now - arrival) / (1000 * 60)));
                }
            } catch (e) {
                console.log('Erreur calcul temps d\'attente avec created_at:', e);
            }
        }
        
        return 0;
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
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBT2Y2/LDjCUIQ4PY7tjwO');
            audio.play().catch(() => {});
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
        
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        if (typeof feather !== 'undefined') feather.replace();
        
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

    destroy() {
        this.stopAutoRefresh();
        console.log('AdvisorInterface destroyed');
    }
}

// ===== Initialisation =====
let advisorInterface;

document.addEventListener('DOMContentLoaded', function() {
    advisorInterface = new AdvisorInterface();
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log('🎯 Interface conseiller FIFO ready - Version corrigée');
});

window.addEventListener('beforeunload', function() {
    if (advisorInterface) {
        advisorInterface.destroy();
    }
}); 
</script>

@endsection