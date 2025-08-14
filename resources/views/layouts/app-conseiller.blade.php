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
                                <input type="search" name="search" class="form-control top-search mb-0" placeholder="Rechercher un ticket...">
                                <button type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                    </div>
                </li>                      

                <!-- Notifications -->
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect position-relative" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-primary badge-pill noti-icon-badge" id="ticketsWaitingCount">{{ $fileStats['tickets_en_attente'] ?? 0 }}</span>
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
                        <button id="callNextBtn" class="btn btn-success btn-sm btn-call-next" onclick="advisorInterface.callNextTicket()">
    <i data-feather="phone-call" class="mr-2"></i>
    <span class="btn-text">Appeler premier</span>
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
                                    <li class="breadcrumb-item active">File d'attente FIFO</li>
                                </ol>
                            </div>
                            <div class="col-auto align-self-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="advisorInterface.refreshTickets()" id="refreshButton">
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
                    <div class="stats-card card-waiting clickable-card" onclick="advisorInterface.showWaitingTickets()">
                        <div class="stats-icon bg-soft-warning">
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
                    <div class="stats-card card-processing clickable-card" onclick="advisorInterface.showCurrentTicket()">
                        <div class="stats-icon bg-soft-info">
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
                    <div class="stats-card card-completed clickable-card" onclick="advisorInterface.showCompletedTickets()">
                        <div class="stats-icon bg-soft-success">
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
                        <div class="stats-icon bg-soft-primary">
                            <i data-feather="users" class="text-primary"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number text-primary" id="averageWaitTime">{{ $defaultWaitTime ?? 15 }}min</h3>
                            <p class="stats-label">Temps moyen</p>
                            <small class="stats-desc">Estimation</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File d'attente pleine largeur -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card queue-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">
                                         File d'attente 
                                        <span class="badge badge-light ml-2" id="queueCountBadge">{{ $fileStats['tickets_en_attente'] ?? 0 }} tickets</span>
                                    </h5>
                                    <small class="text-muted">Premier arrivé, premier servi - Les tickets "new" (reçus) ont la priorité absolue</small>
                                </div>
                                <div>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-success" onclick="advisorInterface.callNextTicket()">
                                            <i data-feather="phone-call" class="mr-1"></i>Appeler premier
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="advisorInterface.refreshTickets()">
                                            <i data-feather="refresh-cw"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- En-têtes de colonnes avec nouvelle colonne Transféré -->
                            <div class="queue-header">
                                <div class="row align-items-center py-3 px-4 bg-light border-bottom">
                                    <div class="col-2">
                                        <strong class="text-dark">Code Ticket</strong>
                                    </div>
                                    <div class="col-2">
                                        <strong class="text-dark">Nom Client</strong>
                                    </div>
                                    <div class="col-2">
                                       <strong class="text-dark">Trans. Par</strong>
                                    </div>
                                    <div class="col-2">
                                        <strong class="text-dark">Service</strong>
                                    </div>
                                    <div class="col-1 text-center">
                                        <strong class="text-dark">Trans.</strong>
                                    </div>
                                    <div class="col-2 text-center">
                                        <strong class="text-dark">Durée d'attente</strong>
                                    </div>
                                    <div class="col-1 text-center">
                                        <strong class="text-dark">Action</strong>
                                    </div>
                                </div>
                            </div>
                            
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
                                        Appeler premier
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
            &copy; {{ date('Y') }} Attendis - File FIFO
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

<!-- Modal Ticket En Cours -->
<div class="modal fade" id="currentTicketModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content current-ticket-modal">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i data-feather="phone-call" class="mr-2"></i>
                    <span id="modalTicketTitle">Client en cours</span>
                </h5>
                <div class="d-flex align-items-center">
                    <div class="modal-timer mr-3">
                        <span class="badge badge-light" id="modalTicketDuration">0min</span>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer" onclick="advisorInterface.closeCurrentTicketModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="current-ticket-banner">
                    <div class="container-fluid">
                        <div class="row align-items-center py-4">
                            <div class="col-md-8">
                                <div class="ticket-info-display">
                                    <div class="ticket-number-large" id="modalTicketNumber">M001</div>
                                    <div class="client-details">
                                        <h4 class="client-name mb-1" id="modalClientName">Nom du client</h4>
                                        <p class="client-phone mb-2" id="modalClientPhone">+225 XX XX XX XX</p>
                                        <span class="service-badge" id="modalServiceBadge">Service</span>
                                        <div id="modalTransferBadge" class="mt-2" style="display: none;">
                                            <span class="badge badge-soft-warning transfer-badge">
                                                <i data-feather="share" class="mr-1" style="width: 12px; height: 12px;"></i>
                                                Ticket transféré
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="call-status-indicator">
                                    <div class="pulse-indicator">
                                        <div class="pulse-ring"></div>
                                        <div class="pulse-dot"></div>
                                    </div>
                                    <p class="status-text mt-3">Appel en cours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="container-fluid py-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-semibold text-primary mb-3">Informations détaillées</h6>
                            <div class="info-list">
                                <div class="info-item">
                                    <span class="info-label">Heure d'appel :</span>
                                    <span class="info-value" id="modalCallTime">--:--</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Temps d'attente initial :</span>
                                    <span class="info-value" id="modalWaitTime">-- min</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Date de demande :</span>
                                    <span class="info-value" id="modalRequestDate">--/--/----</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="modalCommentSection" class="comment-section" style="display: none;">
                                <h6 class="font-weight-semibold text-primary mb-3">Commentaire client</h6>
                                <div class="comment-display">
                                    <i data-feather="message-circle" class="mr-2 text-info"></i>
                                    <span id="modalComment">Commentaire du client...</span>
                                </div>
                            </div>
                            <div id="modalTransferInfo" class="transfer-info-section" style="display: none;">
                                <h6 class="font-weight-semibold text-warning mb-3">Informations de transfert</h6>
                                <div class="transfer-display">
                                    <div class="info-item">
                                        <span class="info-label">Transféré par :</span>
                                        <span class="info-value" id="modalTransferredBy">--</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Motif :</span>
                                        <span class="info-value" id="modalTransferReason">--</span>
                                    </div>
                                    <div class="info-item" id="modalTransferNotesItem" style="display: none;">
                                        <span class="info-label">Notes :</span>
                                        <span class="info-value" id="modalTransferNotes">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="w-100">
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-danger btn-block" onclick="advisorInterface.showFinalConfirmationModal('refuser')">
                                <i data-feather="x" class="mr-1"></i>Refuser le ticket
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning btn-block" onclick="advisorInterface.showTransferModal()">
                                <i data-feather="share" class="mr-1"></i>Transférer
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-success btn-block" onclick="advisorInterface.showFinalConfirmationModal('traiter')">
                                <i data-feather="check" class="mr-1"></i>Traiter le ticket
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <button type="button" class="btn btn-danger" onclick="advisorInterface.showFinalConfirmationModal('refuser')">
                    <i data-feather="x" class="mr-1"></i>Refuser
                </button>
                <button type="button" class="btn btn-warning" onclick="advisorInterface.showTransferModal()">
                    <i data-feather="share" class="mr-1"></i>Transférer
                </button>
                <button type="button" class="btn btn-success" onclick="advisorInterface.showFinalConfirmationModal('traiter')">
                    <i data-feather="check" class="mr-1"></i>Traiter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation finale -->
<div class="modal fade" id="finalConfirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" id="finalConfirmationModalHeader">
                <h5 class="modal-title" id="finalConfirmationModalTitle">
                    <i data-feather="check-circle" class="mr-2"></i>Confirmation de traitement
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Résumé du ticket -->
                <div class="ticket-summary-card mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-semibold text-primary mb-3">Ticket à traiter</h6>
                            <p><strong>Numéro :</strong> <span id="finalTicketNumber">--</span></p>
                            <p><strong>Client :</strong> <span id="finalClientName">--</span></p>
                            <p><strong>Service :</strong> <span id="finalServiceName">--</span></p>
                            <p><strong>Téléphone :</strong> <span id="finalClientPhone">--</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-semibold text-primary mb-3">Informations de traitement</h6>
                            <p><strong>Prise en charge :</strong> <span id="finalCallTime">--</span></p>
                            <p><strong>Durée actuelle :</strong> <span id="finalDuration">--</span></p>
                            <p><strong>Temps d'attente initial :</strong> <span id="finalWaitTime">--</span></p>
                        </div>
                    </div>
                    
                    <!-- Commentaire initial du client si présent -->
                    <div id="finalCommentSection" class="mt-3" style="display: none;">
                        <h6 class="font-weight-semibold text-primary">Commentaire initial du client</h6>
                        <div class="alert alert-light">
                            <i data-feather="message-circle" class="mr-2"></i>
                            <span id="finalComment">--</span>
                        </div>
                    </div>
                </div>

                <!-- Description de l'action -->
                <div class="action-description mb-4" id="finalActionDescription">
                    <h6 class="font-weight-semibold mb-2">
                        <i data-feather="check" class="mr-2"></i>
                        Action : <span id="finalActionLabel">Traiter le ticket</span>
                    </h6>
                    <p class="mb-0" id="finalActionText">Vous allez marquer ce ticket comme traité avec succès.</p>
                </div>

                <!-- Formulaire de commentaire -->
                <div class="form-group">
                    <label for="finalResolutionComment" class="font-weight-semibold">
                        Commentaire de résolution
                        <span class="required-indicator" id="finalCommentRequired" style="display: none;">*</span>
                    </label>
                    <textarea 
                        class="form-control" 
                        id="finalResolutionComment" 
                        rows="4" 
                        placeholder="Commentaire sur le traitement du ticket..."
                        maxlength="500"
                    ></textarea>
                    <small class="form-text text-muted" id="finalCommentHelp">
                        Ce commentaire est optionnel pour un traitement réussi.
                    </small>
                    <div id="finalCommentError" class="text-danger mt-2" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="finalConfirmButton" onclick="advisorInterface.confirmFinalResolution()">
                    <i data-feather="check" class="mr-1"></i>Confirmer le traitement
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

<!-- Modal de transfert avec données dynamiques -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i data-feather="share" class="mr-2"></i>Transférer le ticket
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Résumé du ticket à transférer -->
                <div class="transfer-ticket-card mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-semibold text-warning mb-3">Ticket à transférer</h6>
                            <p><strong>Numéro :</strong> <span id="transferTicketNumber">--</span></p>
                            <p><strong>Client :</strong> <span id="transferClientName">--</span></p>
                            <p><strong>Service actuel :</strong> <span id="transferCurrentService">--</span></p>
                            <p><strong>Téléphone :</strong> <span id="transferClientPhone">--</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-semibold text-warning mb-3">Informations de transfert</h6>
                            <p><strong>Prise en charge :</strong> <span id="transferCallTime">--</span></p>
                            <p><strong>Durée actuelle :</strong> <span id="transferDuration">--</span></p>
                            <p><strong>Temps d'attente initial :</strong> <span id="transferWaitTime">--</span></p>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de transfert -->
                <div class="transfer-form">
                    <!-- Choix du type de transfert -->
                    <div class="form-group">
                        <label class="font-weight-semibold">Type de transfert</label>
                        <div class="transfer-type-selector d-flex align-items-center flex-nowrap overflow-auto">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transferType" id="transferToService" value="service" checked>
                                <label class="form-check-label" for="transferToService">
                                    <i data-feather="layers" class="mr-1"></i>Vers un service
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transferType" id="transferToAdvisor" value="advisor">
                                <label class="form-check-label" for="transferToAdvisor">
                                    <i data-feather="user" class="mr-1"></i>Vers un conseiller
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transferType" id="transferToBoth" value="both">
                                <label class="form-check-label" for="transferToBoth">
                                    <i data-feather="users" class="mr-1"></i>Service + Conseiller
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Sélection du service -->
                    <div class="form-group" id="serviceSelectionGroup">
                        <label for="transferService" class="font-weight-semibold">
                            Transférer vers le service <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="transferService">
                            <option value="">-- Chargement des services... --</option>
                        </select>
                        <small class="form-text text-muted">
                            <i data-feather="info" class="mr-1" style="width: 14px; height: 14px;"></i>
                            Seuls les services actifs créés par votre administrateur sont affichés
                        </small>
                    </div>

                    <!-- Sélection du conseiller -->
                    <div class="form-group" id="advisorSelectionGroup" style="display: none;">
                        <label for="transferAdvisor" class="font-weight-semibold">
                            Transférer vers le conseiller <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="transferAdvisor">
                            <option value="">-- Chargement des conseillers... --</option>
                        </select>
                        <small class="form-text text-muted" id="advisorSelectionHelp">
                            <i data-feather="info" class="mr-1" style="width: 14px; height: 14px;"></i>
                            Conseillers actifs de votre équipe
                        </small>
                        <!-- Informations sur la charge de travail -->
                        <div id="advisorWorkloadInfo" class="mt-2" style="display: none;">
                            <div class="alert alert-info alert-sm">
                                <strong>Charge de travail :</strong> <span id="advisorWorkloadText">--</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="transferReason" class="font-weight-semibold">
                            Motif du transfert <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="transferReason" 
                            rows="3" 
                            placeholder="Expliquez pourquoi vous transférez ce ticket (obligatoire)..."
                            maxlength="300"
                            required
                        ></textarea>
                        <small class="form-text text-muted">
                            Ce motif sera visible par le service/conseiller destinataire.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="transferNotes" class="font-weight-semibold">
                            Notes additionnelles (optionnel)
                        </label>
                        <textarea 
                            class="form-control" 
                            id="transferNotes" 
                            rows="2" 
                            placeholder="Informations supplémentaires pour le destinataire..."
                            maxlength="200"
                        ></textarea>
                    </div>

                    <div id="transferError" class="alert alert-danger" style="display: none;">
                        <i data-feather="alert-circle" class="mr-2"></i>
                        <span id="transferErrorText">Erreur</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="confirmTransferButton" onclick="advisorInterface.confirmTransfer()">
                    <i data-feather="share" class="mr-1"></i>Confirmer le transfert
                </button>
            </div>
        </div>
    </div>
</div>

<!-- STYLES AMÉLIORÉS avec couleurs douces bleu ciel -->
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
    --notification-blue: #87ceeb;
    --notification-blue-light: #b8dcf0;
    --notification-blue-dark: #5fa8d3;
    --notification-bg: rgba(135, 206, 235, 0.95);
    --notification-shadow: 0 8px 25px rgba(135, 206, 235, 0.3);
}

/* ===== Layout général ===== */
.page-content {
    background: var(--light-bg);
    min-height: 100vh;
}

/* ===== Cards ===== */
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

/* ===== Stats Cards ===== */
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

.stats-card.clickable-card {
    cursor: pointer;
    position: relative;
}

.stats-card.clickable-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}

.stats-card.clickable-card:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: var(--border-radius-sm);
}

.stats-card.clickable-card:hover:before {
    opacity: 1;
}

.bg-soft-warning {
    background: rgba(255, 193, 7, 0.1) !important;
}

.bg-soft-info {
    background: rgba(23, 162, 184, 0.1) !important;
}

.bg-soft-success {
    background: rgba(40, 167, 69, 0.1) !important;
}

.bg-soft-primary {
    background: rgba(0, 123, 255, 0.1) !important;
}

.bg-soft-light {
    background: rgba(248, 249, 250, 0.8) !important;
}

.badge-soft-primary {
    background: rgba(0, 123, 255, 0.1);
    color: var(--primary-color);
    border: 1px solid rgba(0, 123, 255, 0.2);
}

.badge-soft-light {
    background: rgba(248, 249, 250, 0.8);
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.stats-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--border-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

/* ===== Modal ticket en cours ===== */
.modal-timer .badge {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.current-ticket-banner {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 1px solid #dee2e6;
}

.ticket-info-display {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.ticket-number-large {
    font-family: 'Courier New', monospace;
    font-size: 3rem;
    font-weight: 900;
    color: var(--primary-color);
    line-height: 1;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    min-width: 120px;
    text-align: center;
}

.service-badge {
    background: linear-gradient(135deg, var(--info-color), #138496);
    color: white;
    padding: 0.5rem 1.25rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.call-status-indicator {
    text-align: center;
}

.pulse-indicator {
    position: relative;
    display: inline-block;
    margin: 0 auto;
}

.pulse-ring {
    width: 80px;
    height: 80px;
    border: 3px solid var(--success-color);
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: pulse-ring 2s ease-out infinite;
}

.pulse-dot {
    width: 40px;
    height: 40px;
    background: var(--success-color);
    border-radius: 50%;
    position: relative;
    margin: 20px;
}

.status-text {
    color: var(--success-color);
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
}

.info-list {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #718096;
    font-size: 0.9rem;
}

.info-value {
    font-weight: 600;
    color: #2d3748;
    font-size: 1rem;
}

.comment-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.comment-display {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border-left: 4px solid var(--info-color);
    font-style: italic;
    color: #495057;
}

/* ===== Modal de confirmation finale ===== */
.ticket-summary-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
}

.action-description {
    background: rgba(40, 167, 69, 0.1);
    border-radius: 8px;
    padding: 1rem;
    border-left: 4px solid var(--success-color);
}

.action-description.action-refuser {
    background: rgba(220, 53, 69, 0.1);
    border-left-color: var(--danger-color);
}

.required-indicator {
    color: var(--danger-color);
    font-weight: 700;
}

/* ===== Modal de transfert ===== */
.transfer-ticket-card {
    background: #fff3cd;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #ffeaa7;
    border-left: 4px solid var(--warning-color);
}

.transfer-form {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.transfer-form .form-control:focus {
    border-color: var(--warning-color);
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.transfer-type-selector {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.transfer-type-selector .form-check-inline {
    margin-right: 2rem;
}

.transfer-type-selector .form-check-label {
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    transition: var(--transition);
    font-weight: 500;
    background: white;
    border: 1px solid #dee2e6;
    margin-left: 0.5rem;
}

.transfer-type-selector .form-check-input:checked + .form-check-label {
    background: var(--warning-color);
    color: #212529;
    border-color: var(--warning-color);
    font-weight: 600;
}

.transfer-type-selector .form-check-label:hover {
    background: #fff3cd;
    border-color: var(--warning-color);
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

/* ===== Commentaires dans l'historique ===== */
.comment-tooltip {
    max-width: 300px;
    word-wrap: break-word;
}

.comment-preview {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: help;
}

.comment-modal {
    max-width: 600px;
}

.comment-full-text {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border-left: 4px solid var(--info-color);
    font-style: italic;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.queue-card {
    background: white;
    box-shadow: var(--shadow-md);
    border-radius: var(--border-radius-sm);
}

.queue-header {
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* ===== Pulse indicators ===== */
.pulse-ring {
    border-color: var(--success-color);
    animation: pulse-ring 2s ease-out infinite;
}

.pulse-dot {
    background: var(--success-color);
}

@keyframes pulse-ring {
    0% {
        transform: translate(-50%, -50%) scale(0.5);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.3);
        opacity: 0;
    }
}

/* ===== Scrollbar personnalisé ===== */
.queue-container::-webkit-scrollbar {
    width: 8px;
}

.queue-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.queue-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.queue-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* ===== Ticket Items avec priorité transfert ===== */
.ticket-item {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
    background: white;
    margin-bottom: 0;
}

.ticket-item:hover {
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.02) 0%, rgba(0, 123, 255, 0.04) 100%);
    border-left: 3px solid var(--primary-color);
    padding-left: calc(1.5rem - 3px);
}

.ticket-item.first-in-queue {
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.05) 0%, rgba(40, 167, 69, 0.08) 100%);
    border-left: 4px solid var(--success-color);
    padding-left: calc(1.5rem - 4px);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.1);
}

.ticket-item.transferred-priority {
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.08) 0%, rgba(40, 167, 69, 0.12) 100%);
    border-left: 4px solid var(--success-color);
    padding-left: calc(1.5rem - 4px);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
    position: relative;
}

.ticket-item.transferred-priority:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--success-color), #20c997);
    animation: priority-glow 2s ease-in-out infinite alternate;
}

.ticket-item.transferred-away {
    background: linear-gradient(90deg, rgba(108, 117, 125, 0.05) 0%, rgba(108, 117, 125, 0.08) 100%);
    border-left: 3px solid #6c757d;
    padding-left: calc(1.5rem - 3px);
    opacity: 0.8;
    position: relative;
}

.ticket-item.transferred-away:hover {
    background: linear-gradient(90deg, rgba(108, 117, 125, 0.08) 0%, rgba(108, 117, 125, 0.12) 100%);
    opacity: 0.9;
    cursor: default;
}

.ticket-item.transferred-away .btn-call-ticket {
    opacity: 0.5;
    cursor: not-allowed;
}

.ticket-item.blocked {
    opacity: 0.6;
    background: #f8f9fa;
    cursor: not-allowed;
}

.ticket-item.blocked:hover {
    background: #f8f9fa;
    border-left: none;
    padding-left: 1.5rem;
}

/* ===== Badge transfert ===== */
.transfer-badge {
    background: rgba(255, 193, 7, 0.15);
    color: #856404;
    border: 1px solid rgba(255, 193, 7, 0.3);
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    font-weight: 500;
}

.transfer-indicator {
    background: var(--warning-color);
    color: #212529;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
    text-align: center;
    min-width: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.transfer-indicator i {
    width: 10px !important;
    height: 10px !important;
}

/* ===== Styles pour les nouveaux indicateurs de transfert ===== */
.transfer-success {
    background: rgba(40, 167, 69, 0.15);
    color: #155724;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.transfer-info {
    background: rgba(23, 162, 184, 0.15);
    color: #0c5460;
    border: 1px solid rgba(23, 162, 184, 0.3);
}

.transfer-muted {
    background: transparent;
    color: #6c757d;
    border: none;
}

/* Badge priorité pour les tickets reçus */
.priority-received {
    background: linear-gradient(45deg, var(--success-color), #218838);
    animation: gentle-pulse 2s infinite;
    color: white;
}

/* Badge priorité normal */
.priority-badge {
    position: absolute;
    top: 0.5rem;
    right: 1rem;
    background: linear-gradient(45deg, var(--success-color), #218838);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    animation: gentle-pulse 2s infinite;
}

/* Badge priorité pour les tickets transférés */
.ticket-item.transferred-priority .priority-badge {
    background: linear-gradient(45deg, var(--warning-color), #e0a800);
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
}

@keyframes gentle-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes subtle-pulse {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 1; }
}

/* ===== Boutons ===== */
.btn-primary {
    background: var(--primary-color);
    border: none;
    color: white;
    font-weight: 500;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    border-radius: var(--border-radius-sm);
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    color: white;
}

.btn-success {
    background: var(--success-color);
    border: none;
    color: white;
    font-weight: 500;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    border-radius: var(--border-radius-sm);
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    color: white;
}

.btn-outline-primary {
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
    background: transparent;
}

.btn-outline-primary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-1px);
}

/* Bouton d'appel spécifique */
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

/* ===== Modal styles ===== */
.modal-content {
    border: none;
    border-radius: var(--border-radius-sm);
    overflow: hidden;
}

.current-ticket-modal .modal-dialog {
    max-width: 900px;
    margin: 1.75rem auto;
}

.current-ticket-modal .modal-content {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.current-ticket-modal .modal-header {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.current-ticket-modal .modal-header .close {
    padding: 0;
    margin: 0;
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: white;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

.current-ticket-modal .modal-header .close:hover {
    opacity: 1;
    color: white;
}

/* ===== NOTIFICATIONS SUBTILES BLEU CIEL AGRÉABLE ===== */
.notification-toast {
    position: fixed;
    top: 90px;
    right: 20px;
    z-index: 9999;
    min-width: 320px;
    max-width: 400px;
    border-radius: var(--border-radius-md);
    box-shadow: var(--notification-shadow);
    border: 1px solid var(--notification-blue-light);
    backdrop-filter: blur(15px);
    transition: var(--transition);
    overflow: hidden;
    background: var(--notification-bg);
    color: white;
}

.notification-toast.toast-success,
.notification-toast.toast-info,
.notification-toast.toast-warning,
.notification-toast.toast-error {
    background: var(--notification-bg);
    color: white;
    border-left: 4px solid var(--notification-blue-dark);
}

.notification-toast .toast-header {
    background: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    padding: 0.75rem 1rem;
    color: white;
}

.notification-toast .toast-body {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    line-height: 1.4;
    color: white;
}

.notification-toast .close {
    color: white;
    opacity: 0.8;
    transition: opacity 0.2s ease;
    text-shadow: none;
}

.notification-toast .close:hover {
    opacity: 1;
    color: white;
}

.notification-toast i[data-feather] {
    color: white !important;
}

@keyframes notificationSlideIn {
    from {
        opacity: 0;
        transform: translateX(100%) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

.notification-toast {
    animation: notificationSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
        padding: 1rem;
    }
    
    .notification-toast {
        right: 10px;
        left: 10px;
        min-width: auto;
    }
    
    .ticket-item {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }
}

/* ===== Styles pour la nouvelle colonne Trans. Par ===== */
.queue-col-transferred-by {
    padding: 0.5rem;
}

.transferred-by {
    font-size: 0.9rem;
    font-weight: 500;
}

.transferred-by .text-dark {
    color: #495057 !important;
}

.transferred-by .text-muted {
    color: #6c757d !important;
    font-style: italic;
}

[title]:hover {
    position: relative;
}

/* Style pour les badges dans l'historique */
.badge-success {
    background-color: #28a745;
}

.badge-info {
    background-color: #17a2b8;
}

.badge-muted {
    background-color: #6c757d;
}

/* Responsive pour la nouvelle colonne */
@media (max-width: 768px) {
    .queue-col-transferred-by {
        padding: 0.25rem;
    }
    
    .transferred-by {
        font-size: 0.8rem;
    }
}

/* ===== Transitions douces ===== */
* {
    transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
}

.topbar *, 
.navbar-custom *, 
.dropdown-menu *,
.topbar-nav *,
.nav-link,
.dropdown-toggle {
    transition: none !important;
}

.nav-link:hover,
.dropdown-item:hover {
    transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
}

/* ===== Focus states ===== */
.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* ===== Loading states ===== */
.btn-refresh-loading {
    position: relative;
    pointer-events: none;
}

.btn-refresh-loading .fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
/* --- Forcer l'alignement sur une ligne pour "Type de transfert" --- */
.transfer-type-selector {
  display: flex;             /* aligne horizontalement */
  align-items: center;
  gap: 12px;                 /* espace entre options */
  flex-wrap: nowrap;         /* une seule ligne */
  white-space: nowrap;       /* empêche le retour à la ligne interne */
  overflow-x: auto;          /* scroll horizontal sur petits écrans */
  -webkit-overflow-scrolling: touch;
}

.transfer-type-selector .form-check-inline {
  display: inline-flex;      /* évite un fallback block éventuel */
  align-items: center;
  margin-right: 12px;
  margin-bottom: 0;          /* pas d'espacement vertical */
}

.transfer-type-selector .form-check-input {
  margin-top: 0;             /* aligne le radio avec le texte/icone */
}

.transfer-type-selector .form-check-label {
  display: inline-flex;      /* pour centrer l’icône + texte */
  align-items: center;
}

</style>

<!-- JAVASCRIPT AMÉLIORÉ avec gestion priorité transfert et notifications subtiles -->
<script>
// ===== Interface Conseiller FIFO Améliorée avec Transfert Priorité =====
class AdvisorInterface {
    constructor() {
        this.currentTicket = null;
        this.currentActionType = null;
        this.ticketsData = [];
        this.refreshInterval = null;
        this.isPaused = false;
        this.isInitialized = false;
        this.waitingTimeUpdateInterval = null;
        this.ticketTimer = null;
        this.isProcessingResolution = false;
        this.isRefreshing = false;
        
        // Données pour transfert dynamique
        this.availableServices = [];
        this.availableAdvisors = [];
        this.transferData = {
            type: 'service',
            selectedService: null,
            selectedAdvisor: null
        };
        
        this.config = {
            refreshInterval: 30000,
            apiRoutes: {
                tickets: '{{ route("conseiller.tickets") }}',
                callTicket: '{{ route("conseiller.call-ticket") }}', 
                completeTicket: '{{ route("conseiller.complete-ticket") }}',
                transferTicket: '{{ route("conseiller.transfer-ticket") }}',
                myStats: '{{ route("conseiller.my-stats") }}',
                // ✅ nouvelles routes pour l’historique fiable
                history: '{{ route("conseiller.resolution-history") }}',
                historyStats: '{{ route("conseiller.resolution-stats") }}',               
                availableServices: '{{ route("api.conseiller.available-services") }}',
                availableAdvisors: '{{ route("api.conseiller.available-advisors") }}',
                advisorWorkload: '{{ route("api.conseiller.advisor-workload", ["advisorId" => "__ID__"]) }}',
                currentTicket: '{{ route("conseiller.current-ticket") }}'
            }
        };
        
        this.init();
    }

    async init() {
        try {
            this.setupAjax();
            this.bindEvents();
            await this.loadInitialData();
            await this.loadCurrentTicketFromServer(true);
            await this.loadTransferData();
            this.startWaitingTimeUpdater();
            this.isInitialized = true;
            
            this.showSubtleNotification('info', 'Interface prête', 'FIFO avec transfert priorité activé');
            console.log('✅ AdvisorInterface FIFO with priority transfer initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize AdvisorInterface:', error);
            this.showSubtleNotification('error', 'Erreur d\'initialisation', error.message);
        }
    }

    /**
     * Obtenir le nom du conseiller qui a transféré le ticket
     */
    getTransferredByName(ticket) {
        // Priorité aux données enrichies du backend
        if (ticket.transfer_info && ticket.transfer_info.transferred_by_name) {
            return ticket.transfer_info.transferred_by_name;
        }
        
        // Vérifier les différentes sources possibles
        if (ticket.conseiller_transfert_name) {
            return ticket.conseiller_transfert_name;
        }
        
        if (ticket.transferred_by_name) {
            return ticket.transferred_by_name;
        }
        
        if (ticket.conseiller_transfert) {
            return `Conseiller #${ticket.conseiller_transfert}`;
        }
        
        // Si le ticket a été transféré mais qu'on n'a pas d'info
        if (ticket.transferer && ticket.transferer !== 'No' && ticket.transferer !== 'no') {
            return 'Conseiller inconnu';
        }
        
        return '-';
    }

    /**
     * Obtenir le statut de transfert du ticket
     */
    getTransferStatus(ticket) {
  const raw = (ticket.transferer || ticket.statut_transfert || '').toString().toLowerCase().trim();
  if (['new','reçu','recu'].includes(raw)) return 'new';
  if (['transfere','transféré','transferé','yes'].includes(raw)) return 'transfere';
  if (['no','non','-',''].includes(raw)) return '-';
  return '-';
}


    /**
     * Obtenir la classe CSS pour le statut de transfert
     */
    getTransferStatusClass(ticket) {
        const status = this.getTransferStatus(ticket);
        
        switch (status) {
            case 'new':
                return 'transfer-success';
            case 'transfere':
                return 'transfer-info';
            default:
                return 'transfer-muted';
        }
    }

    /**
     * Vérifier si un ticket est transféré vers le conseiller actuel
     */
    isTransferredToMe(ticket) {
        return this.getTransferStatus(ticket) === 'new';
    }

    /**
     * Vérifier si le conseiller actuel a transféré ce ticket
     */
    didITransferThis(ticket) {
        const status = this.getTransferStatus(ticket);
        return status === 'transfere';
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

        $(document).on('input', '#finalResolutionComment', () => {
            this.validateResolutionComment();
        });

        $(document).on('change', 'input[name="transferType"]', (e) => {
            this.handleTransferTypeChange(e.target.value);
        });

        $(document).on('change', '#transferService', () => {
            this.validateTransferForm();
        });

        $(document).on('change', '#transferAdvisor', (e) => {
            this.handleAdvisorSelection(e.target.value);
            this.validateTransferForm();
        });

        $(document).on('input', '#transferReason', () => {
            this.validateTransferForm();
        });
    }

    /**
     * Tri des tickets avec nouvelle logique transfert
     */
    sortTicketsWithTransferPriority(tickets) {
        return tickets.sort((a, b) => {
            const statusA = this.getTransferStatus(a);
            const statusB = this.getTransferStatus(b);
            
            // 1. Les tickets "new" (reçus) sont TOUJOURS prioritaires
            if (statusA === 'new' && statusB !== 'new') {
                return -1;
            }
            if (statusB === 'new' && statusA !== 'new') {
                return 1;
            }
            
            // 2. Entre plusieurs tickets "new", tri par temps d'attente
            if (statusA === 'new' && statusB === 'new') {
                const timeA = this.calculateRealWaitingTime(a.heure_d_enregistrement || a.created_at);
                const timeB = this.calculateRealWaitingTime(b.heure_d_enregistrement || b.created_at);
                return timeB - timeA;
            }
            
            // 3. Les tickets "transferé" passent après les normaux
            if (statusA === 'transfere' && statusB === '-') {
                return 1;
            }
            if (statusB === 'transfere' && statusA === '-') {
                return -1;
            }
            
            // 4. Pour le reste, tri FIFO classique
            const timeA = new Date(a.heure_d_enregistrement || a.created_at);
            const timeB = new Date(b.heure_d_enregistrement || b.created_at);
            return timeA - timeB;
        });
    }

    async loadTransferData() {
        try {
            console.log('🔄 Chargement des données de transfert...');
            
            const [servicesResponse, advisorsResponse] = await Promise.all([
                this.apiCall('GET', this.config.apiRoutes.availableServices),
                this.apiCall('GET', this.config.apiRoutes.availableAdvisors)
            ]);

            if (servicesResponse.success) {
                this.availableServices = servicesResponse.services || [];
                console.log(`✅ ${this.availableServices.length} services chargés`);
            }

            if (advisorsResponse.success) {
                this.availableAdvisors = advisorsResponse.advisors || [];
                console.log(`✅ ${this.availableAdvisors.length} conseillers chargés`);
            }

        } catch (error) {
            console.error('❌ Erreur chargement données transfert:', error);
            this.showSubtleNotification('warning', 'Transfert limité', 'Certaines fonctionnalités peuvent être indisponibles');
        }
    }

    showTransferModal() {
        try {
            if (!this.currentTicket) {
                this.showSubtleNotification('error', 'Erreur', 'Aucun ticket en cours à transférer');
                return;
            }

            // Remplir les informations du ticket
            document.getElementById('transferTicketNumber').textContent = this.currentTicket.numero_ticket;
            document.getElementById('transferClientName').textContent = this.currentTicket.prenom || this.currentTicket.client_name;
            document.getElementById('transferCurrentService').textContent = this.currentTicket.service;
            document.getElementById('transferClientPhone').textContent = this.currentTicket.telephone;
            document.getElementById('transferCallTime').textContent = this.currentTicket.heure_prise_en_charge || '--:--';
            
            const currentDuration = this.calculateCurrentDuration();
            document.getElementById('transferDuration').textContent = currentDuration + ' min';
            
            const waitingTime = this.calculateRealWaitingTime(this.currentTicket.heure_d_enregistrement || this.currentTicket.created_at);
            document.getElementById('transferWaitTime').textContent = waitingTime + ' min';

            this.resetTransferForm();
            this.populateTransferSelects();
            $('#transferModal').modal('show');
            
            if (typeof feather !== 'undefined') feather.replace();

        } catch (error) {
            console.error('❌ Error showing transfer modal:', error);
            this.showSubtleNotification('error', 'Erreur', 'Impossible d\'afficher le modal de transfert');
        }
    }

    resetTransferForm() {
        document.querySelector('input[name="transferType"][value="service"]').checked = true;
        document.getElementById('transferService').value = '';
        document.getElementById('transferAdvisor').value = '';
        document.getElementById('transferReason').value = '';
        document.getElementById('transferNotes').value = '';
        document.getElementById('transferError').style.display = 'none';
        
        this.handleTransferTypeChange('service');
        document.getElementById('confirmTransferButton').disabled = true;
        document.getElementById('advisorWorkloadInfo').style.display = 'none';
    }

    populateTransferSelects() {
        // Peupler le select des services
        const serviceSelect = document.getElementById('transferService');
        serviceSelect.innerHTML = '<option value="">-- Sélectionnez un service --</option>';
        
        this.availableServices.forEach(service => {
            const option = document.createElement('option');
            option.value = service.id;
            option.textContent = service.display_name;
            serviceSelect.appendChild(option);
        });

        // Peupler le select des conseillers
        const advisorSelect = document.getElementById('transferAdvisor');
        advisorSelect.innerHTML = '<option value="">-- Sélectionnez un conseiller --</option>';

        this.availableAdvisors.forEach(advisor => {
            const option = document.createElement('option');
            option.value = advisor.id;
            option.textContent = `${advisor.username} (${advisor.has_current_ticket ? 'Occupé' : 'Disponible'})`;
            option.style.color = advisor.has_current_ticket ? '#dc3545' : '#28a745';
            advisorSelect.appendChild(option);
        });
    }

    handleTransferTypeChange(transferType) {
        const serviceGroup = document.getElementById('serviceSelectionGroup');
        const advisorGroup = document.getElementById('advisorSelectionGroup');
        
        this.transferData.type = transferType;
        
        switch (transferType) {
            case 'service':
                serviceGroup.style.display = 'block';
                advisorGroup.style.display = 'none';
                document.getElementById('transferService').required = true;
                document.getElementById('transferAdvisor').required = false;
                break;
                
            case 'advisor':
                serviceGroup.style.display = 'none';
                advisorGroup.style.display = 'block';
                document.getElementById('transferService').required = false;
                document.getElementById('transferAdvisor').required = true;
                break;
                
            case 'both':
                serviceGroup.style.display = 'block';
                advisorGroup.style.display = 'block';
                document.getElementById('transferService').required = true;
                document.getElementById('transferAdvisor').required = true;
                break;
        }
        
        this.validateTransferForm();
    }

    async handleAdvisorSelection(advisorId) {
        const workloadInfo = document.getElementById('advisorWorkloadInfo');
        const workloadText = document.getElementById('advisorWorkloadText');
        
        if (!advisorId) {
            workloadInfo.style.display = 'none';
            return;
        }

        try {
            workloadText.textContent = 'Chargement...';
            workloadInfo.style.display = 'block';
            
            const workloadUrl = this.config.apiRoutes.advisorWorkload.replace('__ID__', encodeURIComponent(advisorId));
            const response = await this.apiCall('GET', workloadUrl);
            
            if (response.success) {
                const workload = response.workload;
                const recommendation = response.recommendation;
                
                const statusClass = workload.today_stats.current_ticket ? 'alert-warning' : 'alert-success';
                const statusIcon = workload.today_stats.current_ticket ? 'clock' : 'check-circle';
                
                workloadInfo.className = `mt-2 alert ${statusClass} alert-sm`;
                workloadText.innerHTML = `
                    <i data-feather="${statusIcon}" class="mr-1" style="width: 14px; height: 14px;"></i>
                    <strong>${workload.advisor_info.username}</strong> - 
                    ${workload.today_stats.tickets_completed} tickets traités aujourd'hui - 
                    ${recommendation}
                `;
                
                if (typeof feather !== 'undefined') feather.replace();
                
            } else {
                workloadText.textContent = 'Impossible de charger la charge de travail';
                workloadInfo.className = 'mt-2 alert alert-danger alert-sm';
            }
            
        } catch (error) {
            console.error('❌ Erreur charge de travail:', error);
            workloadText.textContent = 'Erreur lors du chargement';
            workloadInfo.className = 'mt-2 alert alert-danger alert-sm';
        }
    }

    validateTransferForm() {
        const transferType = document.querySelector('input[name="transferType"]:checked').value;
        const service = document.getElementById('transferService').value.trim();
        const advisor = document.getElementById('transferAdvisor').value.trim();
        const reason = document.getElementById('transferReason').value.trim();
        const confirmButton = document.getElementById('confirmTransferButton');
        const errorDiv = document.getElementById('transferError');
        
        let isValid = false;
        let errorMessage = '';
        
        switch (transferType) {
            case 'service':
                isValid = service && reason;
                if (!service) errorMessage = 'Veuillez sélectionner un service.';
                else if (!reason) errorMessage = 'Le motif est obligatoire.';
                break;
                
            case 'advisor':
                isValid = advisor && reason;
                if (!advisor) errorMessage = 'Veuillez sélectionner un conseiller.';
                else if (!reason) errorMessage = 'Le motif est obligatoire.';
                break;
                
            case 'both':
                isValid = service && advisor && reason;
                if (!service) errorMessage = 'Veuillez sélectionner un service.';
                else if (!advisor) errorMessage = 'Veuillez sélectionner un conseiller.';
                else if (!reason) errorMessage = 'Le motif est obligatoire.';
                break;
        }
        
        if (isValid) {
            confirmButton.disabled = false;
            errorDiv.style.display = 'none';
        } else {
            confirmButton.disabled = true;
            if (errorMessage) {
                document.getElementById('transferErrorText').textContent = errorMessage;
                errorDiv.style.display = 'block';
            }
        }
    }

    async confirmTransfer() {
        try {
            if (!this.currentTicket) {
                this.showSubtleNotification('error', 'Erreur', 'Aucun ticket à transférer');
                return;
            }

            const transferType = document.querySelector('input[name="transferType"]:checked').value;
            const serviceId = document.getElementById('transferService').value.trim();
            const advisorId = document.getElementById('transferAdvisor').value.trim();
            const reason = document.getElementById('transferReason').value.trim();
            const notes = document.getElementById('transferNotes').value.trim();

            // Validation finale
            let isValid = false;
            switch (transferType) {
                case 'service': isValid = serviceId && reason; break;
                case 'advisor': isValid = advisorId && reason; break;
                case 'both': isValid = serviceId && advisorId && reason; break;
            }

            if (!isValid) {
                this.validateTransferForm();
                return;
            }

            const confirmBtn = document.getElementById('confirmTransferButton');
            const originalHTML = confirmBtn.innerHTML;
            
            confirmBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm mr-2"></span>
                Transfert...
            `;
            confirmBtn.disabled = true;

            const transferData = {
                ticket_id: this.currentTicket.id,
                transfer_reason: reason,
                transfer_notes: notes
            };

            if (transferType === 'service' || transferType === 'both') {
                transferData.to_service = parseInt(serviceId);
            }

            if (transferType === 'advisor' || transferType === 'both') {
                transferData.to_advisor = parseInt(advisorId);
            }

            const response = await this.apiCall('POST', this.config.apiRoutes.transferTicket, transferData);

            if (response.success) {
                $('#transferModal').modal('hide');
                $('#currentTicketModal').modal('hide');
                this.stopModalTimer();
                
                const ticketNumber = this.currentTicket.numero_ticket;
                this.currentTicket = null;
                
                await Promise.all([
                    this.refreshTickets(),
                    this.loadMyStats(),
                    this.loadTransferData()
                ]);

                this.showSubtleNotification('success', 'Transfert réussi', `Ticket ${ticketNumber} transféré avec priorité conservée`);
                
            } else {
                throw new Error(response.message || 'Erreur lors du transfert');
            }

        } catch (error) {
            console.error('❌ Error confirming transfer:', error);
            this.showSubtleNotification('error', 'Erreur de transfert', error.message);
        } finally {
            const confirmBtn = document.getElementById('confirmTransferButton');
            if (confirmBtn) {
                confirmBtn.innerHTML = '<i data-feather="share" class="mr-1"></i>Confirmer le transfert';
                this.validateTransferForm();
                if (typeof feather !== 'undefined') feather.replace();
            }
        }
    }

    async refreshTickets() {
        if (this.isRefreshing) return;

        try {
            this.isRefreshing = true;
            this.setRefreshButtonLoading(true);
            
            const response = await this.apiCall('GET', this.config.apiRoutes.tickets);
            
            if (response.success) {
                this.ticketsData = response.tickets || [];
                this.updateUI(response);
                if (this.ticketsData.length > 0) {
                    this.showSubtleNotification('info', 'Actualisation', `${this.ticketsData.length} tickets chargés`, 2000);
                }
            } else {
                throw new Error(response.message || 'Erreur lors du chargement');
            }
        } catch (error) {
            console.error('❌ Error refreshing tickets:', error);
            this.showSubtleNotification('error', 'Erreur', 'Impossible de charger les tickets');
        } finally {
            this.isRefreshing = false;
            this.setRefreshButtonLoading(false);
        }
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
                        <p class="text-muted small">Aucun ticket en attente</p>
                    </div>
                </div>
            `;
        } else {
            // Appliquer le tri avec priorité transfert
            const sortedTickets = this.sortTicketsWithTransferPriority(tickets);
            
            let html = '';
            sortedTickets.forEach((ticket, index) => {
                const validatedTicket = this.validateTicketData(ticket);
                const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
                
                // Utiliser les nouvelles méthodes conformes
                const transferredByName = this.getTransferredByName(ticket);
                const transferStatus = this.getTransferStatus(ticket);
                const transferStatusClass = this.getTransferStatusClass(ticket);
                
                let statusClass = 'normal';
                if (waitingTime > 30) {
                    statusClass = 'urgent';
                } else if (waitingTime > 15) {
                    statusClass = 'warning';
                }
                
                // Déterminer le statut du ticket
                const isFirst = index === 0;
                const isNewTransfer = transferStatus === 'new';
                const isTransferredAway = transferStatus === 'transfere';
                
                let itemClass = 'ticket-item';
                let canBeCalled = false;
                
                if (isNewTransfer) {
                    itemClass += ' transferred-priority';
                    canBeCalled = isFirst;
                } else if (isTransferredAway) {
                    itemClass += ' transferred-away';
                    canBeCalled = false;
                } else if (isFirst) {
                    itemClass += ' first-in-queue';
                    canBeCalled = true;
                } else {
                    itemClass += ' blocked';
                    canBeCalled = false;
                }
                
                // Indicateur de transfert
                let transferIndicator = '';
                if (transferStatus === 'new') {
                    transferIndicator = `
                        <span class="transfer-indicator ${transferStatusClass}">
                            <i data-feather="arrow-down-left" style="width: 10px; height: 10px;"></i>
                            ${transferStatus}
                        </span>
                    `;
                } else if (transferStatus === 'transfere') {
                    transferIndicator = `
                        <span class="transfer-indicator ${transferStatusClass}">
                            <i data-feather="share" style="width: 10px; height: 10px;"></i>
                            ${transferStatus}
                        </span>
                    `;
                } else {
                    transferIndicator = `<span class="text-muted">${transferStatus}</span>`;
                }
                
                // Badge de priorité
                let priorityBadge = '';
                if (isNewTransfer) {
                    priorityBadge = '<div class="priority-badge priority-new">NOUVEAU (PRIORITÉ)</div>';
                } else if (isTransferredAway) {
                    priorityBadge = '<div class="priority-badge priority-transferred">TRANSFÉRÉ</div>';
                } else if (isFirst) {
                    priorityBadge = '<div class="priority-badge">PREMIER</div>';
                }
                
                html += `
                    <div class="${itemClass}" data-id="${validatedTicket.id}" 
                         onclick="advisorInterface.showTicketDetails(${validatedTicket.id})" 
                         style="animation-delay: ${index * 0.05}s">
                        
                        ${priorityBadge}
                        
                        <div class="d-flex align-items-center">
                            <div class="col-2 queue-col-code">
                                <div class="ticket-number">${validatedTicket.numero_ticket}</div>
                                <small class="text-muted">${validatedTicket.date}</small>
                            </div>
                            
                            <div class="col-2 queue-col-client">
                                <div class="client-name">${validatedTicket.prenom}</div>
                            </div>
                            
                            <div class="col-2 queue-col-transferred-by">
                                <div class="transferred-by">
                                    <span class="text-${transferredByName === '-' ? 'muted' : 'dark'}" 
                                          title="${transferredByName !== '-' ? 'Transféré par: ' + transferredByName : 'Ticket normal'}">
                                        ${transferredByName}
                                    </span>
                                    
                                </div>
                            </div>
                            
                            <div class="col-2 queue-col-service">
                                <span class="ticket-service">${validatedTicket.service}</span>
                            </div>
                            
                            <div class="col-1 queue-col-transfer text-center">
                                ${transferIndicator}
                            </div>
                            
                            <div class="col-2 queue-col-waiting text-center">
                                <div class="ticket-waiting-time ${statusClass}">${waitingTime}min</div>
                            </div>
                            
                            <div class="col-1 queue-col-action text-center">
                                <button class="btn btn-success btn-sm btn-call-ticket ${!canBeCalled ? 'blocked' : ''}" 
                                        onclick="event.stopPropagation(); advisorInterface.callTicket(${validatedTicket.id})"
                                        ${!canBeCalled ? 'disabled' : ''}
                                        title="${!canBeCalled ? (isTransferredAway ? 'Ticket transféré - ne peut plus être appelé' : 'Seul le premier ticket peut être appelé') : 'Appeler ce ticket'}">
                                    <i data-feather="phone-call" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        const queueCount = tickets ? tickets.length : 0;
        document.getElementById('queueCountBadge').textContent = queueCount + (queueCount === 1 ? ' ticket' : ' tickets');
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    showCurrentTicketModal(ticket) {
        const validatedTicket = this.validateTicketData(ticket);
        
        document.getElementById('modalTicketTitle').textContent = `Client ${validatedTicket.numero_ticket} en cours`;
        document.getElementById('modalTicketNumber').textContent = validatedTicket.numero_ticket;
        document.getElementById('modalClientName').textContent = validatedTicket.prenom;
        document.getElementById('modalClientPhone').textContent = validatedTicket.telephone;
        document.getElementById('modalServiceBadge').textContent = validatedTicket.service;
        document.getElementById('modalCallTime').textContent = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('modalRequestDate').textContent = validatedTicket.date;
        
        const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
        document.getElementById('modalWaitTime').textContent = `${waitingTime} min`;
        
        // Afficher les informations de transfert si applicable
        const transferBadge = document.getElementById('modalTransferBadge');
        const transferInfo = document.getElementById('modalTransferInfo');
        const transferStatus = this.getTransferStatus(ticket);
        
        if (transferStatus === 'new') {
            transferBadge.style.display = 'block';
            transferBadge.innerHTML = `
                <span class="badge badge-success transfer-badge">
                    <i data-feather="arrow-down-left" class="mr-1" style="width: 12px; height: 12px;"></i>
                    Ticket reçu (Priorité)
                </span>
            `;
            
            if (ticket.transfer_info) {
                transferInfo.style.display = 'block';
                document.getElementById('modalTransferredBy').textContent = ticket.transfer_info.transferred_by_name || '--';
                document.getElementById('modalTransferReason').textContent = ticket.transfer_info.transfer_reason || '--';
                
                const notesItem = document.getElementById('modalTransferNotesItem');
                if (ticket.transfer_info.transfer_notes) {
                    document.getElementById('modalTransferNotes').textContent = ticket.transfer_info.transfer_notes;
                    notesItem.style.display = 'block';
                } else {
                    notesItem.style.display = 'none';
                }
            }
        } else if (transferStatus === 'transfere') {
            transferBadge.style.display = 'block';
            transferBadge.innerHTML = `
                <span class="badge badge-secondary transfer-badge">
                    <i data-feather="share" class="mr-1" style="width: 12px; height: 12px;"></i>
                    Transféré par moi
                </span>
            `;
            transferInfo.style.display = 'none';
        } else {
            transferBadge.style.display = 'none';
            transferInfo.style.display = 'none';
        }
        
        const commentSection = document.getElementById('modalCommentSection');
        if (validatedTicket.commentaire && validatedTicket.commentaire.trim() !== '') {
            document.getElementById('modalComment').textContent = validatedTicket.commentaire;
            commentSection.style.display = 'block';
        } else {
            commentSection.style.display = 'none';
        }
        
        this.startModalTimer();
        $('#currentTicketModal').modal('show');
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    showSubtleNotification(type, title, message, duration = 3000) {
        if (this.shouldSkipNotification(type, title)) {
            return;
        }

        const toastId = 'toast_' + Date.now();
        const toast = document.createElement('div');
        
        toast.id = toastId;
        toast.className = `notification-toast toast-${type}`;
        toast.setAttribute('role', 'alert');
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%) scale(0.95)';
        toast.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        
        const icons = {
            'success': 'check-circle',
            'error': 'alert-circle',
            'warning': 'alert-triangle',
            'info': 'info'
        };
        
        toast.innerHTML = `
            <div class="toast-header">
                <i data-feather="${icons[type]}" class="mr-2" style="width: 18px; height: 18px;"></i>
                <strong class="mr-auto">${title}</strong>
                <button type="button" class="close" onclick="advisorInterface.removeNotification('${toastId}')">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        requestAnimationFrame(() => {
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0) scale(1)';
            }, 50);
        });
        
        if (typeof feather !== 'undefined') feather.replace();
        
        setTimeout(() => {
            this.removeNotification(toastId);
        }, duration);
    }

    removeNotification(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%) scale(0.95)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 400);
        }
    }

    shouldSkipNotification(type, title) {
        const skipPatterns = [
            'Actualisation',
            'Modal fermé',
            'File d\'attente'
        ];
        
        return skipPatterns.some(pattern => title.includes(pattern));
    }

    async loadInitialData() {
        await Promise.all([
            this.refreshTickets(),
            this.loadMyStats()
        ]);
    }

    startWaitingTimeUpdater() {
        this.waitingTimeUpdateInterval = setInterval(() => {
            this.updateWaitingTimes();
        }, 60000);
    }

    updateWaitingTimes() {
  document.querySelectorAll('.ticket-item[data-id]').forEach(el => {
    const id = parseInt(el.getAttribute('data-id'), 10);
    const t = this.ticketsData.find(x => x.id === id);
    if (!t) return;
    const mins = this.calculateRealWaitingTime(t.heure_d_enregistrement || t.created_at);
    const timeEl = el.querySelector('.ticket-waiting-time');
    if (!timeEl) return;
    timeEl.textContent = mins + 'min';
    timeEl.classList.remove('normal','warning','urgent');
    timeEl.classList.add(mins > 30 ? 'urgent' : mins > 15 ? 'warning' : 'normal');
  });
}


    async callNextTicket() {
        if (this.currentTicket) {
  this.showCurrentTicketModal(this.currentTicket);
  this.showSubtleNotification('info', 'Déjà en cours', 'Vous avez déjà un ticket actif.');
  return;
}
  if (this.isPaused) {
    this.showSubtleNotification('warning', 'Service en pause', 'Reprenez votre service d\'abord');
    return;
  }
  try {
    if (typeof this.setButtonLoading === 'function') this.setButtonLoading('#callNextBtn', true);

    const res = await this.apiCall('POST', this.config.apiRoutes.callTicket);

    // ✅ Cas normal : on vient d’attribuer un ticket
    if (res.ok && res.success && res.ticket) {
      this.currentTicket = res.ticket;
      this.showCurrentTicketModal(res.ticket);
      if (typeof this.refreshTickets === 'function') await this.refreshTickets();
      this.showSubtleNotification('success', 'Ticket attribué', res.message || 'Vous pouvez traiter le client');
      this.playNotificationSound?.();
      return;
    }

    // ✅ Cas 400 avec ticket en cours (backend te le renvoie dans current_ticket)
    if (!res.ok && res.current_ticket) {
      this.currentTicket = res.current_ticket;
      this.showCurrentTicketModal(res.current_ticket);
      this.showSubtleNotification('warning', 'Déjà en cours', res.message || 'Un ticket est déjà en cours');
      return;
    }

    // file vide / autre message serveur
    if (res.success === false) {
      this.showSubtleNotification('info', 'Aucun ticket', res.message || 'File vide');
      return;
    }

    this.showSubtleNotification('error', 'Erreur', 'Réponse inattendue du serveur');
  } catch (e) {
    this.showSubtleNotification('error', 'Erreur', e.message || 'Impossible d’appeler le prochain ticket');
  } finally {
    if (typeof this.setButtonLoading === 'function') this.setButtonLoading('#callNextBtn', false);
  }
}


    async callTicket(ticketId) {
        const sortedTickets = this.sortTicketsWithTransferPriority(this.ticketsData);
        const targetTicket = sortedTickets.find(t => t.id === ticketId);
        
        if (!targetTicket) {
            this.showSubtleNotification('error', 'Erreur', 'Ticket non trouvé');
            return;
        }
        
        const transferStatus = this.getTransferStatus(targetTicket);
        const isFirst = sortedTickets[0].id === ticketId;
        
        if (transferStatus === 'transfere') {
            this.showSubtleNotification('warning', 'Ticket transféré', 'Ce ticket a été transféré et ne peut plus être appelé');
            return;
        }
        
        if (transferStatus !== 'new' && !isFirst) {
            this.showSubtleNotification('warning', 'Restriction FIFO', 'Vous ne pouvez appeler que le premier ticket de la file');
            return;
        }

        try {
            this.setButtonLoading('.btn-call-next', true);
            
            const response = await this.apiCall('POST', this.config.apiRoutes.callTicket, {
                ticket_id: ticketId
            });
            
            if (response.success) {
                this.currentTicket = response.ticket;
                this.showCurrentTicketModal(response.ticket);
                await this.refreshTickets();
                
                let message = `Ticket ${response.ticket.numero_ticket}`;
                if (transferStatus === 'new') {
                    message += ' (nouveau - priorité)';
                }
                    
                this.showSubtleNotification('success', 'Client appelé', message);
                this.playNotificationSound();
            } else {
                throw new Error(response.message || 'Erreur lors de l\'appel');
            }
        } catch (error) {
            console.error('❌ Error calling ticket:', error);
            this.showSubtleNotification('error', 'Erreur d\'appel', error.message);
        } finally {
            this.setButtonLoading('.btn-call-next', false);
        }
    }

    closeCurrentTicketModal() {
        if (this.currentTicket) {
            if (confirm('⚠️ Un ticket est en cours. Voulez-vous vraiment fermer cette fenêtre ?\n\nLe ticket restera actif.')) {
                $('#currentTicketModal').modal('hide');
                this.stopModalTimer();
            }
        } else {
            $('#currentTicketModal').modal('hide');
            this.stopModalTimer();
        }
    }

    startModalTimer() {
        const start = new Date();
        this.ticketTimer = setInterval(() => {
            const now = new Date();
            const diff = Math.floor((now - start) / (1000 * 60));
            const element = document.getElementById('modalTicketDuration');
            if (element) {
                element.textContent = diff + 'min';
            } else {
                this.stopModalTimer();
            }
        }, 60000);
    }

    stopModalTimer() {
        if (this.ticketTimer) {
            clearInterval(this.ticketTimer);
            this.ticketTimer = null;
        }
    }

    showFinalConfirmationModal(actionType) {
        try {
            if (!this.currentTicket) {
                this.showSubtleNotification('error', 'Erreur', 'Aucun ticket en cours');
                return;
            }

            this.currentActionType = actionType;
            this.isProcessingResolution = false;
            
            const isTraiter = actionType === 'traiter';
            const modal = document.getElementById('finalConfirmationModal');
            const header = document.getElementById('finalConfirmationModalHeader');
            const title = document.getElementById('finalConfirmationModalTitle');
            const actionLabel = document.getElementById('finalActionLabel');
            const actionText = document.getElementById('finalActionText');
            const actionDescription = document.querySelector('.action-description');
            const commentRequired = document.getElementById('finalCommentRequired');
            const commentHelp = document.getElementById('finalCommentHelp');
            const confirmButton = document.getElementById('finalConfirmButton');
            const commentTextarea = document.getElementById('finalResolutionComment');

            if (isTraiter) {
                header.className = 'modal-header bg-success text-white';
                title.innerHTML = '<i data-feather="check-circle" class="mr-2"></i>Confirmation de traitement';
                actionLabel.textContent = 'Traiter le ticket avec succès';
                actionText.textContent = 'Vous allez marquer ce ticket comme traité avec succès.';
                actionDescription.className = 'action-description mb-4';
                commentRequired.style.display = 'none';
                commentHelp.textContent = 'Ce commentaire est optionnel pour un traitement réussi.';
                confirmButton.className = 'btn btn-success';
                confirmButton.innerHTML = '<i data-feather="check" class="mr-1"></i>Confirmer le traitement';
                confirmButton.disabled = false;
                commentTextarea.placeholder = 'Commentaire optionnel sur le traitement...';
                commentTextarea.required = false;
            } else {
                header.className = 'modal-header bg-danger text-white';
                title.innerHTML = '<i data-feather="x-circle" class="mr-2"></i>Confirmation de refus';
                actionLabel.textContent = 'Refuser le ticket';
                actionText.textContent = 'Vous allez marquer ce ticket comme non résolu. Un commentaire est obligatoire.';
                actionDescription.className = 'action-description action-refuser mb-4';
                commentRequired.style.display = 'inline';
                commentHelp.textContent = 'Ce commentaire est obligatoire pour justifier le refus.';
                confirmButton.className = 'btn btn-danger';
                confirmButton.innerHTML = '<i data-feather="x" class="mr-1"></i>Confirmer le refus';
                confirmButton.disabled = true;
                commentTextarea.placeholder = 'Expliquez pourquoi le ticket est refusé (obligatoire)...';
                commentTextarea.required = true;
            }

            // Remplir les informations du ticket
            document.getElementById('finalTicketNumber').textContent = this.currentTicket.numero_ticket;
            document.getElementById('finalClientName').textContent = this.currentTicket.prenom || this.currentTicket.client_name;
            document.getElementById('finalServiceName').textContent = this.currentTicket.service;
            document.getElementById('finalClientPhone').textContent = this.currentTicket.telephone;
            document.getElementById('finalCallTime').textContent = this.currentTicket.heure_prise_en_charge || '--:--';
            
            const currentDuration = this.calculateCurrentDuration();
            document.getElementById('finalDuration').textContent = currentDuration + ' min';
            
            const waitingTime = this.calculateRealWaitingTime(this.currentTicket.heure_d_enregistrement || this.currentTicket.created_at);
            document.getElementById('finalWaitTime').textContent = waitingTime + ' min';
            
            const commentSection = document.getElementById('finalCommentSection');
            if (this.currentTicket.commentaire && this.currentTicket.commentaire.trim() !== '') {
                document.getElementById('finalComment').textContent = this.currentTicket.commentaire;
                commentSection.style.display = 'block';
            } else {
                commentSection.style.display = 'none';
            }

            commentTextarea.value = '';
            document.getElementById('finalCommentError').style.display = 'none';

            this.validateResolutionComment();
            $('#finalConfirmationModal').modal('show');
            
            if (!isTraiter) {
                setTimeout(() => {
                    commentTextarea.focus();
                }, 500);
            }
            
            if (typeof feather !== 'undefined') feather.replace();

        } catch (error) {
            console.error('❌ Error showing final confirmation modal:', error);
            this.showSubtleNotification('error', 'Erreur', 'Impossible d\'afficher le modal de confirmation');
        }
    }

    async confirmFinalResolution() {
        try {
            if (this.isProcessingResolution) {
                console.log('⚠️ Résolution déjà en cours, ignorée');
                return;
            }

            if (!this.currentTicket || !this.currentActionType) {
                this.showSubtleNotification('error', 'Erreur', 'Informations de résolution manquantes');
                return;
            }

            const commentaire = document.getElementById('finalResolutionComment').value.trim();
            const errorDiv = document.getElementById('finalCommentError');
            
            if (this.currentActionType === 'refuser' && !commentaire) {
                errorDiv.textContent = 'Le commentaire est obligatoire pour refuser un ticket';
                errorDiv.style.display = 'block';
                document.getElementById('finalResolutionComment').focus();
                return;
            }

            errorDiv.style.display = 'none';
            this.isProcessingResolution = true;

            const confirmBtn = document.getElementById('finalConfirmButton');
            const originalHTML = confirmBtn.innerHTML;
            const originalClasses = confirmBtn.className;
            
            confirmBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm mr-2"></span>
                ${this.currentActionType === 'traiter' ? 'Traitement...' : 'Refus...'}
            `;
            confirmBtn.disabled = true;
            confirmBtn.className = 'btn btn-secondary';

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);

            const response = await this.apiCall('POST', this.config.apiRoutes.completeTicket, {
                action: this.currentActionType,
                commentaire_resolution: commentaire,
                ticket_id: this.currentTicket.id
            }, controller.signal);

            clearTimeout(timeoutId);

            if (response.success) {
                $('#finalConfirmationModal').modal('hide');
                $('#currentTicketModal').modal('hide');
                this.stopModalTimer();
                
                const actionText = this.currentActionType === 'traiter' ? 'traité' : 'refusé';
                const ticketNumber = this.currentTicket.numero_ticket;
                
                this.currentTicket = null;
                this.currentActionType = null;
                this.isProcessingResolution = false;
                
                await Promise.all([
                    this.refreshTickets(),
                    this.loadMyStats()
                ]);

                this.showSubtleNotification('success', 'Ticket ' + actionText, `${ticketNumber} traité avec succès`);
                
            } else {
                throw new Error(response.message || 'Erreur lors de la résolution');
            }

        } catch (error) {
            console.error('❌ Error confirming final resolution:', error);
            this.isProcessingResolution = false;
            
            if (error.name === 'AbortError') {
                this.showSubtleNotification('error', 'Timeout', 'La requête a pris trop de temps');
            } else {
                this.showSubtleNotification('error', 'Erreur', error.message);
            }
        } finally {
            const confirmBtn = document.getElementById('finalConfirmButton');
            if (confirmBtn && this.currentActionType) {
                const isTraiter = this.currentActionType === 'traiter';
                confirmBtn.innerHTML = isTraiter ? 
                    '<i data-feather="check" class="mr-1"></i>Confirmer le traitement' : 
                    '<i data-feather="x" class="mr-1"></i>Confirmer le refus';
                confirmBtn.className = isTraiter ? 'btn btn-success' : 'btn btn-danger';
                
                this.validateResolutionComment();
                if (typeof feather !== 'undefined') feather.replace();
            }
        }
    }

    validateResolutionComment() {
        if (!this.currentActionType) return;

        const commentaire = document.getElementById('finalResolutionComment').value.trim();
        const errorDiv = document.getElementById('finalCommentError');
        const confirmBtn = document.getElementById('finalConfirmButton');
        
        if (this.currentActionType === 'refuser' && !commentaire) {
            errorDiv.textContent = 'Le commentaire est obligatoire pour refuser un ticket';
            errorDiv.style.display = 'block';
            confirmBtn.disabled = true;
        } else {
            errorDiv.style.display = 'none';
            confirmBtn.disabled = false;
        }
    }

    calculateCurrentDuration() {
        if (!this.currentTicket || !this.currentTicket.heure_prise_en_charge) {
            return 0;
        }
        
        const now = new Date();
        const callTime = new Date();
        const timeParts = this.currentTicket.heure_prise_en_charge.split(':');
        callTime.setHours(parseInt(timeParts[0]), parseInt(timeParts[1]), parseInt(timeParts[2] || 0));
        
        return Math.floor((now - callTime) / (1000 * 60));
    }

    showWaitingTickets() {
        const queueCard = document.querySelector('.queue-card');
        if (queueCard) {
            queueCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            queueCard.style.border = '2px solid var(--primary-color)';
            setTimeout(() => {
                queueCard.style.border = '1px solid rgba(109, 180, 254, 0.15)';
            }, 2000);
        }
        if (this.ticketsData.length > 0) {
            this.showSubtleNotification('info', 'File d\'attente', `${this.ticketsData.length} tickets visibles`);
        }
    }

    async showCurrentTicket() {
  if (this.currentTicket) {
    this.showCurrentTicketModal(this.currentTicket);
    return;
  }
  const found = await this.loadCurrentTicketFromServer(true);
  if (!found) {
    this.showSubtleNotification('info', 'Aucun ticket', 'Appelez le premier ticket de la file');
  }
}


    showCompletedTickets() {
        this.showHistory();
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

    updateMyStats(stats) {
        // Implémenter si nécessaire selon vos besoins
    }

 async showHistory() {
  $('#historyModal').modal('show');
  try {
    const [statsRes, histRes] = await Promise.all([
      this.apiCall('GET', this.config.apiRoutes.historyStats),
      this.apiCall('GET', this.config.apiRoutes.history + '?origin=all') // all|received|normal
    ]);

    if (!statsRes.success || !histRes.success) {
      throw new Error('Chargement incomplet');
    }

    const s = statsRes.resolution_stats || {};
    const summary = {
      total_traites: s.total_traites || 0,
      total_refuses: s.tickets_non_resolus || 0,
      total_recus_traites: s.tickets_recus_traites || 0,
      avg_minutes: s.avg_processing_time_min ?? 0
    };

    this.renderHistoryModal(histRes.tickets || [], summary);
  } catch (e) {
    document.getElementById('historyContent').innerHTML = `
      <div class="text-center py-4">
        <i data-feather="alert-circle" class="text-danger mb-2" style="width:48px;height:48px;"></i>
        <p class="text-muted">Erreur lors du chargement de l'historique</p>
      </div>`;
    if (typeof feather !== 'undefined') feather.replace();
  }
}



   renderHistoryModal(tickets, summary) {
  const avg = (summary.avg_minutes || 0).toFixed(1);

  let html = `
    <div class="history-summary mb-4">
      <div class="row text-center">
        <div class="col-md-3"><h4 class="text-success">${summary.total_traites}</h4><p class="text-muted mb-0">Tickets traités</p></div>
        <div class="col-md-3"><h4 class="text-danger">${summary.total_refuses}</h4><p class="text-muted mb-0">Tickets refusés</p></div>
        <div class="col-md-3"><h4 class="text-warning">${summary.total_recus_traites}</h4><p class="text-muted mb-0">Reçus (traités)</p></div>
        <div class="col-md-3"><h4 class="text-info">${avg}min</h4><p class="text-muted mb-0">Temps moyen</p></div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Code</th>
            <th>Client</th>
            <th>Service</th>
            <th>Origine</th> <!-- 👈 nouveau -->
            <th>Statut</th>
            <th>Date/Heure</th>
            <th>Durée</th>
            <th>Commentaire</th>
          </tr>
        </thead>
        <tbody>
  `;

  if (tickets.length === 0) {
    html += `<tr><td colspan="8" class="text-center text-muted py-4">Aucun ticket dans l'historique</td></tr>`;
  } else {
    tickets.forEach(t => {
      const badgeStatut = t.statut_traiter === 'traité'
        ? '<span class="badge badge-success">Traité</span>'
        : '<span class="badge badge-danger">Refusé</span>';

      const badgeOrigin = t.origin === 'reçu'
        ? '<span class="badge badge-success">Reçu</span>'
        : '<span class="badge badge-secondary">Normal</span>';

      const comment = (t.commentaire_resolution || '').trim();
      const preview = comment ? `${this.escapeHtml(comment.substring(0, 30))}${comment.length > 30 ? '…' : ''}` : '<span class="text-muted">-</span>';
      const more = (comment && comment.length > 30)
        ? `<a href="#" class="text-primary ml-1" onclick="advisorInterface.showCommentModal('${this.escapeHtml(comment)}', '${t.numero_ticket}');return false;"><i data-feather="eye" style="width:14px;height:14px;"></i></a>`
        : '';

      html += `
        <tr>
          <td><strong>${t.numero_ticket}</strong></td>
          <td>${t.client_name || 'N/A'}</td>
          <td>${t.service || 'N/A'}</td>
          <td>${badgeOrigin}</td>
          <td>${badgeStatut}</td>
          <td>${t.date_traitement || 'N/A'}</td>
          <td>${t.duree_traitement || 'N/A'}</td>
          <td>${preview} ${more}</td>
        </tr>
      `;
    });
  }

  html += `</tbody></table></div>`;

  document.getElementById('historyContent').innerHTML = html;
  if (typeof feather !== 'undefined') feather.replace();
}


    getStatusBadge(status) {
        switch(status) {
            case 'Yes':
            case 'yes':
            case 'traité':
                return '<span class="badge badge-success">Traité</span>';
            case 'No':
            case 'no':
            case 'refusé':
                return '<span class="badge badge-danger">Refusé</span>';
            case 'transferé':
                return '<span class="badge badge-warning">Transféré</span>';
            default:
                return '<span class="badge badge-secondary">En cours</span>';
        }
    }

    updateUI(response) {
        this.updateTicketsList(response.tickets);
        this.updateStats(response.stats);
        this.updateNotifications(response.tickets);
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
                
                if (oldValue !== value.toString() && Math.abs(parseInt(oldValue) - value) > 0) {
                    element.style.transform = 'scale(1.05)';
                    element.style.transition = 'transform 0.3s ease';
                    setTimeout(() => {
                        element.style.transform = 'scale(1)';
                    }, 300);
                }
            }
        });
    }

    updateNotifications(tickets) {
        const container = document.getElementById('notificationTickets');
        
        if (!tickets || tickets.length === 0) {
            container.innerHTML = `
                <div class="text-center py-3">
                    <i data-feather="check-circle" class="text-primary mb-2"></i>
                    <p class="text-muted mb-0 small">File d'attente vide</p>
                </div>
            `;
        } else {
            let html = '';
            const displayTickets = this.sortTicketsWithTransferPriority(tickets).slice(0, 5);
            
            displayTickets.forEach((ticket, index) => {
                const validatedTicket = this.validateTicketData(ticket);
                const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
                const transferStatus = this.getTransferStatus(ticket);
                
                let avatarBg, iconName, badgeClass, badgeText;
                
                if (transferStatus === 'new') {
                    avatarBg = 'bg-success';
                    iconName = 'arrow-down-left';
                    badgeClass = 'badge-success';
                    badgeText = 'New';
                } else if (transferStatus === 'transfere') {
                    avatarBg = 'bg-secondary';
                    iconName = 'share';
                    badgeClass = 'badge-secondary';
                    badgeText = 'T';
                } else {
                    avatarBg = 'bg-primary';
                    iconName = 'user';
                    badgeClass = 'badge-light';
                    badgeText = '';
                }
                
                html += `
                    <a href="#" class="dropdown-item py-3" onclick="advisorInterface.callTicket(${validatedTicket.id})">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm ${avatarBg} rounded-circle d-flex align-items-center justify-content-center">
                                    <i data-feather="${iconName}" class="text-white" style="width: 16px; height: 16px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h6 class="mb-0 font-weight-normal">
                                    ${validatedTicket.numero_ticket}
                                    ${badgeText ? `<span class="badge ${badgeClass} ml-1">${badgeText}</span>` : ''}
                                </h6>
                                <small class="text-muted">${validatedTicket.prenom} - ${waitingTime}min</small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge ${transferStatus === 'new' ? 'badge-success' : 'badge-light'}">#${index + 1}</span>
                            </div>
                        </div>
                    </a>
                `;
            });
            container.innerHTML = html;
        }
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    validateTicketData(ticket) {
        console.log('🔍 DEBUG Ticket data:', ticket);

        let clientName = 'Nom non renseigné';
        const nameFields = ['prenom', 'nom', 'nom_complet', 'client_name', 'name'];
        
        for (const field of nameFields) {
            if (ticket[field] && ticket[field].toString().trim() !== '') {
                clientName = ticket[field].toString().trim();
                break;
            }
        }

        return {
            id: ticket.id || 0,
            numero_ticket: ticket.numero_ticket || 'N/A',
            prenom: clientName,
            telephone: ticket.telephone && ticket.telephone.toString().trim() !== '' ? ticket.telephone.toString().trim() : 'Non renseigné',
            date: ticket.date || 'Non définie',
            service: ticket.service || 'Service non défini',
            heure_d_enregistrement: ticket.heure_d_enregistrement,
            created_at: ticket.created_at,
            commentaire: ticket.commentaire || null
        };
    }

    calculateRealWaitingTime(registrationTime) {
        if (!registrationTime) return 0;
        
        const now = new Date();
        let arrival;
        
        try {
            if (registrationTime.match(/^\d{1,2}:\d{2}(:\d{2})?$/)) {
                const today = now.toISOString().split('T')[0];
                const timeString = registrationTime.length === 5 ? registrationTime + ':00' : registrationTime;
                arrival = new Date(`${today}T${timeString}`);
            } else {
                arrival = new Date(registrationTime);
            }
            
            if (!isNaN(arrival.getTime())) {
                const diffMinutes = Math.floor((now - arrival) / (1000 * 60));
                return Math.max(0, diffMinutes);
            }
        } catch (e) {
            console.log('Erreur calcul temps d\'attente:', e);
        }
        
        return 0;
    }

    showTicketDetails(ticketId) {
        const ticket = this.ticketsData.find(t => t.id === ticketId) || this.currentTicket;
        if (!ticket) {
            this.showSubtleNotification('error', 'Erreur', 'Ticket non trouvé');
            return;
        }

        const validatedTicket = this.validateTicketData(ticket);
        const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
        
        const transferredByName = this.getTransferredByName(ticket);
        const transferStatus = this.getTransferStatus(ticket);
        
        let statusBadge = '';
        let statusText = '';
        
        if (transferStatus === 'new') {
            statusBadge = '<span class="badge badge-success">Nouveau (Priorité)</span>';
            statusText = 'Ticket reçu par transfert - priorité maximale';
        } else if (transferStatus === 'transfere') {
            statusBadge = '<span class="badge badge-secondary">Transféré par moi</span>';
            statusText = 'Ticket que j\'ai transféré - ne peut plus être appelé';
        } else if (waitingTime > 30) {
            statusBadge = '<span class="badge badge-danger">Urgent</span>';
            statusText = 'Temps d\'attente élevé';
        } else if (waitingTime > 15) {
            statusBadge = '<span class="badge badge-warning">Moyen</span>';
            statusText = 'Temps d\'attente modéré';
        } else {
            statusBadge = '<span class="badge badge-primary">Normal</span>';
            statusText = 'Ticket normal FIFO';
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
                            <tr><td class="font-weight-semibold">Attente réelle:</td><td>${waitingTime}min</td></tr>
                            <tr><td class="font-weight-semibold">Transféré par:</td><td>${transferredByName}</td></tr>
                            <tr><td class="font-weight-semibold">Statut transfert:</td><td>${transferStatus}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-semibold mb-3">Informations client</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tr><td class="font-weight-semibold">Prénom:</td><td>${validatedTicket.prenom}</td></tr>
                            <tr><td class="font-weight-semibold">Téléphone:</td><td>${validatedTicket.telephone}</td></tr>
                            <tr><td class="font-weight-semibold">Date de demande:</td><td>${validatedTicket.date}</td></tr>
                        </table>
                    </div>
                    
                    ${validatedTicket.commentaire ? `
                        <h6 class="font-weight-semibold mb-2">Commentaire</h6>
                        <div class="alert alert-light">
                            <i data-feather="message-circle" class="mr-2"></i>
                            ${validatedTicket.commentaire}
                        </div>
                    ` : ''}
                    
                    ${transferStatus === 'new' && ticket.transfer_info ? `
                        <h6 class="font-weight-semibold mb-2 text-success">Ticket reçu par transfert (Priorité)</h6>
                        <div class="alert alert-success">
                            <small><strong>Transféré par:</strong> ${transferredByName}</small><br>
                            <small><strong>Motif:</strong> ${ticket.transfer_info.transfer_reason || '--'}</small>
                            ${ticket.transfer_info.transfer_notes ? `<br><small><strong>Notes:</strong> ${ticket.transfer_info.transfer_notes}</small>` : ''}
                        </div>
                    ` : ''}
                    
                    ${transferStatus === 'transfere' && ticket.transfer_info ? `
                        <h6 class="font-weight-semibold mb-2 text-secondary">Ticket que j'ai transféré</h6>
                        <div class="alert alert-secondary">
                            <small><strong>Transféré vers:</strong> ${ticket.transfer_info.transferred_to || '--'}</small><br>
                            <small><strong>Motif:</strong> ${ticket.transfer_info.transfer_reason || '--'}</small>
                            ${ticket.transfer_info.transfer_notes ? `<br><small><strong>Notes:</strong> ${ticket.transfer_info.transfer_notes}</small>` : ''}
                            <br><small class="text-warning"><strong>⚠️ Ce ticket ne peut plus être appelé</strong></small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        $('#ticketDetailsModal').modal('show');
        if (typeof feather !== 'undefined') feather.replace();
    }

    showCommentModal(commentText, ticketNumber) {
        const modalId = 'commentModal_' + Date.now();
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog comment-modal" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i data-feather="message-circle" class="mr-2"></i>
                                Commentaire - Ticket ${ticketNumber}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="comment-full-text">
                                ${this.escapeHtml(commentText)}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        $(`#${modalId}`).modal('show');
        $(`#${modalId}`).on('hidden.bs.modal', function () {
            $(this).remove();
        });
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

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
            this.showSubtleNotification('warning', 'Pause activée', 'Vous ne recevrez plus de tickets');
        } else {
            statusBadge.textContent = 'En ligne';
            statusBadge.className = 'badge badge-success ml-2';
            pauseButton.textContent = 'Pause';
            pauseMenu.textContent = 'Pause file';
            statusText.textContent = 'En ligne';
            this.showSubtleNotification('success', 'Service repris', 'Vous êtes de nouveau disponible');
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
                this.showSubtleNotification('success', 'Mot de passe modifié', 'Changement effectué avec succès');
            } else {
                throw new Error(result.message || 'Erreur lors du changement');
            }
        } catch (error) {
            console.error('❌ Error changing password:', error);
            this.showSubtleNotification('error', 'Erreur', error.message);
        }
    }

    exportData() {
        this.showSubtleNotification('info', 'Export en cours', 'Génération du fichier...', 2000);
        
        setTimeout(() => {
            window.open('{{ route("conseiller.export") }}', '_blank');
            this.showSubtleNotification('success', 'Export terminé', 'Fichier téléchargé');
        }, 1500);
    }
async apiCall(method, url, data = null, signal = null) {
  const options = {
    method,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  };
  if (signal) options.signal = signal;
  if (data) {
    if (data instanceof FormData) options.body = data;
    else { options.headers['Content-Type'] = 'application/json'; options.body = JSON.stringify(data); }
  }

  const response = await fetch(url, options);
  let payload = null;
  try { payload = await response.json(); } catch(_) { payload = {}; }
  return { http_status: response.status, ok: response.ok, ...payload };
}
 
 async loadCurrentTicketFromServer(showModalIfFound = false) {
  const res = await this.apiCall('GET', this.config.apiRoutes.currentTicket);
  if (res.success && res.ticket) {
    this.currentTicket = res.ticket;
    if (showModalIfFound) this.showCurrentTicketModal(this.currentTicket);
    return true;
  }
  return false;
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

    setRefreshButtonLoading(isLoading) {
        const refreshButtons = document.querySelectorAll('#refreshButton, .btn-outline-primary');
        
        refreshButtons.forEach(button => {
            const icon = button.querySelector('i[data-feather="refresh-cw"]');
            
            if (isLoading) {
                button.disabled = true;
                button.classList.add('btn-refresh-loading');
                if (icon) {
                    icon.style.animation = 'gentle-spin 2s linear infinite';
                }
            } else {
                button.disabled = false;
                button.classList.remove('btn-refresh-loading');
                if (icon) {
                    icon.style.animation = '';
                }
            }
        });
    }

    playNotificationSound() {
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBT2Y2/LDjCUIQ4PY7tjwO');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        } catch (error) {
            // Son optionnel, ne pas bloquer si erreur
        }
    }

    destroy() {
        if (this.waitingTimeUpdateInterval) {
            clearInterval(this.waitingTimeUpdateInterval);
        }
        if (this.ticketTimer) {
            clearInterval(this.ticketTimer);
        }
        console.log('AdvisorInterface FIFO with priority transfer destroyed');
    }
}

// Initialisation
let advisorInterface;

document.addEventListener('DOMContentLoaded', function() {
    advisorInterface = new AdvisorInterface();
    
    $('#currentTicketModal').on('hidden.bs.modal', function () {
        if (advisorInterface && advisorInterface.ticketTimer) {
            advisorInterface.stopModalTimer();
        }
    });
    
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#currentTicketModal').hasClass('show')) {
            advisorInterface.closeCurrentTicketModal();
        }
    });
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log('🎯 Interface conseiller FIFO avec transfert priorité initialisée');
});

window.addEventListener('beforeunload', function() {
    if (advisorInterface) {
        advisorInterface.destroy();
    }
});      
</script>

@endsection