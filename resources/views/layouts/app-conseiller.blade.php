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
                            <i data-feather="users" class="text-white"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number text-primary" id="averageWaitTime">{{ $defaultWaitTime ?? 15 }}min</h3>
                            <p class="stats-label">Temps moyen</p>
                            <small class="stats-desc">Estimation</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ NOUVEAU : File d'attente pleine largeur -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card queue-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">
                                        🎯 File d'attente FIFO
                                        <span class="badge badge-light ml-2" id="queueCountBadge">{{ $fileStats['tickets_en_attente'] ?? 0 }} tickets</span>
                                    </h5>
                                    <small class="text-muted">Premier arrivé, premier servi - Seul le premier peut être appelé</small>
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
                            <!-- ✅ NOUVEAU : En-têtes de colonnes plus spacieux -->
                            <div class="queue-header">
                                <div class="row align-items-center py-3 px-4 bg-light border-bottom">
                                    <div class="col-2">
                                        <strong class="text-dark">Code Ticket</strong>
                                    </div>
                                    <div class="col-3">
                                        <strong class="text-dark">Nom Client</strong>
                                    </div>
                                    <div class="col-2">
                                        <strong class="text-dark">Téléphone</strong>
                                    </div>
                                    <div class="col-2">
                                        <strong class="text-dark">Service</strong>
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

<!-- ✅ NOUVEAU : Modal Ticket En Cours (remplace le panel) -->
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
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="w-100">
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-secondary btn-block" onclick="advisorInterface.showTicketDetails()">
                                <i data-feather="eye" class="mr-1"></i>Voir détails
                            </button>
                        </div>
                        <div class="col-md-4">
                            <!-- ✅ MODIFIÉ : Appel direct à showFinalConfirmationModal -->
                            <button type="button" class="btn btn-success btn-block" onclick="advisorInterface.showFinalConfirmationModal('traiter')">
                                <i data-feather="check" class="mr-1"></i>Traiter le ticket
                            </button>
                        </div>
                        <div class="col-md-4">
                            <!-- ✅ MODIFIÉ : Appel direct à showFinalConfirmationModal -->
                            <button type="button" class="btn btn-danger btn-block" onclick="advisorInterface.showFinalConfirmationModal('refuser')">
                                <i data-feather="x" class="mr-1"></i>Refuser le ticket
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals existants -->
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
                <!-- ✅ MODIFIÉ : Appel direct -->
                <button type="button" class="btn btn-success" onclick="advisorInterface.showFinalConfirmationModal('traiter')">
                    <i data-feather="check" class="mr-1"></i>Traiter
                </button>
                <button type="button" class="btn btn-danger" onclick="advisorInterface.showFinalConfirmationModal('refuser')">
                    <i data-feather="x" class="mr-1"></i>Refuser
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ NOUVEAU : Modal de confirmation finale SIMPLIFIÉ -->
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

<!-- ✅ NOUVEAUX STYLES pour l'interface améliorée -->
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

/* ===== ✅ NOUVEAU : Queue Card pleine largeur ===== */
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

.queue-container {
    max-height: 600px; /* Plus de hauteur */
    overflow-y: auto;
}

.tickets-list {
    min-height: 300px;
}

/* ===== ✅ NOUVEAU : Ticket Items moins condensés ===== */
.ticket-item {
    padding: 1.25rem 1.5rem; /* Plus d'espacement */
    border-bottom: 1px solid #f1f3f4;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
    background: white;
    margin-bottom: 0;
}

.ticket-item:last-child {
    border-bottom: none;
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

.ticket-item.first-in-queue:hover {
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.08) 0%, rgba(40, 167, 69, 0.12) 100%);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.15);
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

/* ===== ✅ NOUVEAU : Colonnes du tableau plus spacieuses ===== */
.queue-col-code {
    padding-right: 1rem;
}

.queue-col-client {
    padding-right: 1rem;
}

.queue-col-phone {
    padding-right: 1rem;
}

.queue-col-service {
    padding-right: 1rem;
}

.queue-col-waiting {
    padding-right: 1rem;
}

.queue-col-action {
    padding-left: 0.5rem;
}

.ticket-number {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: #2d3748;
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
}

.client-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
}

.client-phone {
    color: #718096;
    font-size: 0.75rem;
}

.ticket-service {
    color: var(--info-color);
    background: rgba(23, 162, 184, 0.1);
    padding: 0.375rem 0.875rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.ticket-waiting-time {
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    text-align: center;
    min-width: 60px;
    display: inline-block;
}

.ticket-waiting-time.urgent {
    color: white;
    background: var(--danger-color);
}

.ticket-waiting-time.warning {
    color: #856404;
    background: #fff3cd;
}

.ticket-waiting-time.normal {
    color: #155724;
    background: #d4edda;
}

.first-badge {
    position: absolute;
    top: 0.1rem;
    right: 1rem;
    background: linear-gradient(45deg, var(--success-color), #218838);
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-call-ticket {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
    transition: var(--transition);
}

.btn-call-ticket:hover:not(.blocked) {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
}

.btn-call-ticket.blocked {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ===== ✅ NOUVEAU : Modal Ticket En Cours ===== */
.current-ticket-modal .modal-dialog {
    max-width: 900px;
    margin: 1.75rem auto;
}

.current-ticket-modal .modal-content {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
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

.modal-timer .badge {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* ===== Centrage responsive amélioré ===== */
@media (min-width: 576px) {
    .current-ticket-modal .modal-dialog {
        max-width: 540px;
        margin: 1.75rem auto;
    }
}

@media (min-width: 768px) {
    .current-ticket-modal .modal-dialog {
        max-width: 700px;
        margin: 1.75rem auto;
    }
}

@media (min-width: 992px) {
    .current-ticket-modal .modal-dialog {
        max-width: 850px;
        margin: 1.75rem auto;
    }
}

@media (min-width: 1200px) {
    .current-ticket-modal .modal-dialog {
        max-width: 900px;
        margin: 1.75rem auto;
    }
}

/* ===== Centrage vertical parfait ===== */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
    transform: translate(0, -50px);
}

.modal.show .modal-dialog {
    transform: none;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

@media (min-width: 576px) {
    .modal-dialog-centered {
        min-height: calc(100% - 3.5rem);
    }
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

.client-details {
    flex: 1;
}

.client-name {
    font-size: 1.rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.client-phone {
    font-size: 0.75rem;
    color: #718096;
    margin-bottom: 1rem;
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

@keyframes pulse-ring {
    0% {
        transform: translate(-50%, -50%) scale(0.5);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0;
    }
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

/* ===== ✅ NOUVEAU : Modal de confirmation finale ===== */
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

/* ===== ✅ NOUVEAU : Styles pour les commentaires dans l'historique ===== */
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

/* ===== Empty States ===== */
.empty-queue-state {
    padding: 3rem 1rem;
    text-align: center;
}

.empty-state-icon i {
    width: 64px;
    height: 64px;
    opacity: 0.4;
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

/* ===== Responsive ===== */
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
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }
    
    .ticket-item .row {
        width: 100%;
        margin: 0;
    }
    
    .ticket-item .col-2,
    .ticket-item .col-3 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .first-badge {
        right: 0.5rem;
        top: 0.5rem;
        padding: 0.25rem 0.75rem;
        font-size: 0.65rem;
    }
    
    .ticket-number-large {
        font-size: 2rem;
        min-width: 80px;
        padding: 0.75rem;
    }
    
    .client-name {
        font-size: 1.25rem;
    }
    
    .ticket-info-display {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
}

@media (max-width: 576px) {
    .queue-header .row {
        display: none; /* Masquer les en-têtes sur mobile */
    }
    
    .ticket-item {
        border-radius: 8px;
        margin: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

/* ===== Notifications ===== */
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

<!-- ✅ NOUVEAU JavaScript CORRIGÉ -->
<script>
// ===== Interface Conseiller FIFO avec Flow Simplifié CORRIGÉ =====
class AdvisorInterface {
    constructor() {
        this.currentTicket = null;
        this.currentActionType = null; // 'traiter' ou 'refuser'
        this.ticketsData = [];
        this.refreshInterval = null;
        this.isPaused = false;
        this.isInitialized = false;
        this.waitingTimeUpdateInterval = null;
        this.ticketTimer = null;
        this.isProcessingResolution = false; // ✅ AJOUT : Éviter les doubles soumissions
        
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
            this.startWaitingTimeUpdater();
            this.isInitialized = true;
            
            this.showNotification('success', 'Interface FIFO prête', 'Flow simplifié activé avec corrections');
            console.log('✅ AdvisorInterface FIFO with simplified flow (FIXED) initialized successfully');
            
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

        // ✅ NOUVEAU : Événement pour la validation en temps réel du commentaire
        $(document).on('input', '#finalResolutionComment', () => {
            this.validateResolutionComment();
        });
    }

    // ✅ NOUVELLE MÉTHODE : Validation en temps réel du commentaire
    validateResolutionComment() {
        if (!this.currentActionType) return;

        const commentaire = document.getElementById('finalResolutionComment').value.trim();
        const errorDiv = document.getElementById('finalCommentError');
        const confirmBtn = document.getElementById('finalConfirmButton');
        
        if (this.currentActionType === 'refuser' && !commentaire) {
            errorDiv.textContent = 'Le commentaire est obligatoire pour refuser un ticket';
            errorDiv.style.display = 'block';
            confirmBtn.disabled = true;
            confirmBtn.classList.add('disabled');
        } else {
            errorDiv.style.display = 'none';
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('disabled');
        }
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
        const ticketElements = document.querySelectorAll('.ticket-item');
        ticketElements.forEach((element, index) => {
            const ticket = this.ticketsData[index];
            if (ticket) {
                const waitingTime = this.calculateRealWaitingTime(ticket.heure_d_enregistrement || ticket.created_at);
                const timeElement = element.querySelector('.ticket-waiting-time');
                if (timeElement) {
                    timeElement.textContent = waitingTime + 'min';
                    
                    timeElement.classList.remove('normal', 'warning', 'urgent');
                    if (waitingTime > 30) {
                        timeElement.classList.add('urgent');
                    } else if (waitingTime > 15) {
                        timeElement.classList.add('warning');
                    } else {
                        timeElement.classList.add('normal');
                    }
                }
            }
        });
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

        const firstTicket = this.ticketsData[0];
        await this.callTicket(firstTicket.id);
    }

    async callTicket(ticketId) {
        if (this.ticketsData.length > 0 && this.ticketsData[0].id !== ticketId) {
            this.showNotification('warning', 'Restriction FIFO', 'Vous ne pouvez appeler que le premier ticket de la file');
            return;
        }

        try {
            this.setButtonLoading('.btn-call-next', true);
            
            const response = await this.apiCall('POST', this.config.apiRoutes.callTicket, {
                ticket_id: ticketId
            });
            
            if (response.success) {
                this.currentTicket = response.ticket;
                // ✅ NOUVEAU : Afficher le modal au lieu du panel
                this.showCurrentTicketModal(response.ticket);
                await this.refreshTickets();
                this.showNotification('success', 'Premier client appelé', `Ticket ${response.ticket.numero_ticket}`);
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

    // ===== ✅ NOUVELLE MÉTHODE : Fermer le modal ticket en cours =====
    closeCurrentTicketModal() {
        // Confirmation avant fermeture si un ticket est en cours
        if (this.currentTicket) {
            if (confirm('⚠️ Un ticket est en cours. Voulez-vous vraiment fermer cette fenêtre ?\n\nLe ticket restera actif mais vous devrez le traiter depuis les actions rapides.')) {
                $('#currentTicketModal').modal('hide');
                this.stopModalTimer();
                this.showNotification('info', 'Modal fermé', 'Le ticket reste actif. Traitez-le via les actions rapides.');
            }
        } else {
            $('#currentTicketModal').modal('hide');
            this.stopModalTimer();
        }
    }

    // ===== ✅ NOUVELLE MÉTHODE : Afficher le modal du ticket en cours =====
    showCurrentTicketModal(ticket) {
        const validatedTicket = this.validateTicketData(ticket);
        
        // Remplir les informations du modal
        document.getElementById('modalTicketTitle').textContent = `Client ${validatedTicket.numero_ticket} en cours`;
        document.getElementById('modalTicketNumber').textContent = validatedTicket.numero_ticket;
        document.getElementById('modalClientName').textContent = validatedTicket.prenom;
        document.getElementById('modalClientPhone').textContent = validatedTicket.telephone;
        document.getElementById('modalServiceBadge').textContent = validatedTicket.service;
        document.getElementById('modalCallTime').textContent = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('modalRequestDate').textContent = validatedTicket.date;
        
        // Temps d'attente initial
        const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
        document.getElementById('modalWaitTime').textContent = `${waitingTime} min`;
        
        // Commentaire (si présent)
        const commentSection = document.getElementById('modalCommentSection');
        if (validatedTicket.commentaire && validatedTicket.commentaire.trim() !== '') {
            document.getElementById('modalComment').textContent = validatedTicket.commentaire;
            commentSection.style.display = 'block';
        } else {
            commentSection.style.display = 'none';
        }
        
        // Démarrer le timer
        this.startModalTimer();
        
        // Afficher le modal
        $('#currentTicketModal').modal('show');
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    // ===== ✅ NOUVELLE MÉTHODE : Timer du modal =====
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

    // ===== ✅ MÉTHODE CORRIGÉE : Afficher directement le modal de confirmation finale =====
    showFinalConfirmationModal(actionType) {
        try {
            if (!this.currentTicket) {
                this.showNotification('error', 'Erreur', 'Aucun ticket en cours');
                return;
            }

            this.currentActionType = actionType;
            this.isProcessingResolution = false; // ✅ Réinitialiser
            
            // Configurer le modal selon l'action
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

            // ✅ Modifier l'apparence selon l'action
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
                confirmButton.disabled = false; // ✅ Toujours actif pour traiter
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
                confirmButton.disabled = true; // ✅ Désactivé par défaut pour refus
                commentTextarea.placeholder = 'Expliquez pourquoi le ticket est refusé (obligatoire)...';
                commentTextarea.required = true;
            }

            // ✅ Remplir les informations du ticket
            document.getElementById('finalTicketNumber').textContent = this.currentTicket.numero_ticket;
            document.getElementById('finalClientName').textContent = this.currentTicket.prenom || this.currentTicket.client_name;
            document.getElementById('finalServiceName').textContent = this.currentTicket.service;
            document.getElementById('finalClientPhone').textContent = this.currentTicket.telephone;
            document.getElementById('finalCallTime').textContent = this.currentTicket.heure_prise_en_charge || '--:--';
            
            // Calculer la durée actuelle
            const currentDuration = this.calculateCurrentDuration();
            document.getElementById('finalDuration').textContent = currentDuration + ' min';
            
            // Temps d'attente initial
            const waitingTime = this.calculateRealWaitingTime(this.currentTicket.heure_d_enregistrement || this.currentTicket.created_at);
            document.getElementById('finalWaitTime').textContent = waitingTime + ' min';
            
            // Commentaire initial du client
            const commentSection = document.getElementById('finalCommentSection');
            if (this.currentTicket.commentaire && this.currentTicket.commentaire.trim() !== '') {
                document.getElementById('finalComment').textContent = this.currentTicket.commentaire;
                commentSection.style.display = 'block';
            } else {
                commentSection.style.display = 'none';
            }

            // ✅ Effacer le commentaire précédent et masquer les erreurs
            commentTextarea.value = '';
            document.getElementById('finalCommentError').style.display = 'none';

            // ✅ Valider immédiatement l'état du bouton
            this.validateResolutionComment();

            // ✅ Afficher directement le modal de confirmation finale
            $('#finalConfirmationModal').modal('show');
            
            // ✅ Focus sur le textarea si c'est un refus
            if (!isTraiter) {
                setTimeout(() => {
                    commentTextarea.focus();
                }, 500);
            }
            
            if (typeof feather !== 'undefined') feather.replace();

            console.log('✅ Modal de confirmation finale affiché', {
                action: actionType,
                ticket: this.currentTicket.numero_ticket,
                commentaire_obligatoire: !isTraiter,
                button_enabled: !confirmButton.disabled
            });

        } catch (error) {
            console.error('❌ Error showing final confirmation modal:', error);
            this.showNotification('error', 'Erreur', 'Impossible d\'afficher le modal de confirmation');
        }
    }

    // ===== ✅ MÉTHODE CORRIGÉE : Confirmer la résolution finale =====
    async confirmFinalResolution() {
        try {
            // ✅ Éviter les doubles soumissions
            if (this.isProcessingResolution) {
                console.log('⚠️ Résolution déjà en cours, ignorée');
                return;
            }

            if (!this.currentTicket || !this.currentActionType) {
                this.showNotification('error', 'Erreur', 'Informations de résolution manquantes');
                return;
            }

            const commentaire = document.getElementById('finalResolutionComment').value.trim();
            const errorDiv = document.getElementById('finalCommentError');
            
            // ✅ Validation stricte pour refus
            if (this.currentActionType === 'refuser' && !commentaire) {
                errorDiv.textContent = 'Le commentaire est obligatoire pour refuser un ticket';
                errorDiv.style.display = 'block';
                document.getElementById('finalResolutionComment').focus();
                return;
            }

            // ✅ Masquer les erreurs et marquer comme en cours
            errorDiv.style.display = 'none';
            this.isProcessingResolution = true;

            // ✅ Désactiver le bouton avec état visuel clair
            const confirmBtn = document.getElementById('finalConfirmButton');
            const originalHTML = confirmBtn.innerHTML;
            const originalClasses = confirmBtn.className;
            
            confirmBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm mr-2"></span>
                ${this.currentActionType === 'traiter' ? 'Traitement...' : 'Refus...'}
            `;
            confirmBtn.disabled = true;
            confirmBtn.className = 'btn btn-secondary'; // Couleur neutre pendant traitement

            console.log('🔄 Envoi de la résolution:', {
                action: this.currentActionType,
                ticket: this.currentTicket.numero_ticket,
                has_comment: commentaire.length > 0,
                comment_length: commentaire.length
            });

            // ✅ Envoyer la résolution avec timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 secondes

            const response = await this.apiCall('POST', this.config.apiRoutes.completeTicket, {
                action: this.currentActionType,
                commentaire_resolution: commentaire,
                ticket_id: this.currentTicket.id // ✅ S'assurer d'envoyer l'ID
            }, controller.signal);

            clearTimeout(timeoutId);

            if (response.success) {
                // ✅ Fermer tous les modals
                $('#finalConfirmationModal').modal('hide');
                $('#currentTicketModal').modal('hide');
                this.stopModalTimer();
                
                // ✅ Réinitialiser les variables
                const actionText = this.currentActionType === 'traiter' ? 'traité' : 'refusé';
                const ticketNumber = this.currentTicket.numero_ticket;
                
                this.currentTicket = null;
                this.currentActionType = null;
                this.isProcessingResolution = false;
                
                // ✅ Rafraîchir les données
                await Promise.all([
                    this.refreshTickets(),
                    this.loadMyStats()
                ]);

                this.showNotification('success', 'Résolution confirmée', `Ticket ${ticketNumber} ${actionText} avec succès`);
                
                console.log('✅ Ticket resolved successfully:', {
                    ticket: ticketNumber,
                    action: actionText,
                    has_comment: commentaire.length > 0,
                    response: response
                });
                
            } else {
                throw new Error(response.message || 'Erreur lors de la résolution');
            }

        } catch (error) {
            console.error('❌ Error confirming final resolution:', error);
            this.isProcessingResolution = false;
            
            // ✅ Afficher l'erreur spécifique
            if (error.name === 'AbortError') {
                this.showNotification('error', 'Timeout', 'La requête a pris trop de temps');
            } else {
                this.showNotification('error', 'Erreur', error.message);
            }
        } finally {
            // ✅ Réactiver le bouton dans tous les cas
            const confirmBtn = document.getElementById('finalConfirmButton');
            if (confirmBtn && this.currentActionType) {
                const isTraiter = this.currentActionType === 'traiter';
                confirmBtn.innerHTML = isTraiter ? 
                    '<i data-feather="check" class="mr-1"></i>Confirmer le traitement' : 
                    '<i data-feather="x" class="mr-1"></i>Confirmer le refus';
                confirmBtn.className = isTraiter ? 'btn btn-success' : 'btn btn-danger';
                
                // ✅ Révalider l'état du bouton
                this.validateResolutionComment();
                
                if (typeof feather !== 'undefined') feather.replace();
            }
        }
    }

    // ===== ✅ MÉTHODE : Calculer la durée actuelle =====
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
                this.renderHistoryModal(response.tickets, response.summary);
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

    // ===== ✅ NOUVEAU : Mise à jour liste tickets pleine largeur =====
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
                const validatedTicket = this.validateTicketData(ticket);
                const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
                
                let statusClass = 'normal';
                if (waitingTime > 30) {
                    statusClass = 'urgent';
                } else if (waitingTime > 15) {
                    statusClass = 'warning';
                }
                
                const isFirst = index === 0;
                const itemClass = isFirst ? 'ticket-item first-in-queue' : 'ticket-item blocked';
                
                html += `
                    <div class="${itemClass}" 
                         onclick="advisorInterface.showTicketDetails(${validatedTicket.id})" 
                         style="animation-delay: ${index * 0.1}s">
                        
                        ${isFirst ? '<div class="first-badge">PREMIER</div>' : ''}
                        
                        <div class="d-flex align-items-center">
                            <div class="col-2 queue-col-code">
                                <div class="ticket-number">${validatedTicket.numero_ticket}</div>
                                <small class="text-muted">${validatedTicket.date}</small>
                            </div>
                            
                            <div class="col-3 queue-col-client">
                                <div class="client-name">${validatedTicket.prenom}</div>
                            </div>
                            
                            <div class="col-2 queue-col-phone">
                                <div class="client-phone">${validatedTicket.telephone}</div>
                            </div>
                            
                            <div class="col-2 queue-col-service">
                                <span class="ticket-service">${validatedTicket.service}</span>
                            </div>
                            
                            <div class="col-2 queue-col-waiting text-center">
                                <div class="ticket-waiting-time ${statusClass}">${waitingTime}min</div>
                            </div>
                            
                            <div class="col-1 queue-col-action text-center">
                                <button class="btn btn-success btn-sm btn-call-ticket ${!isFirst ? 'blocked' : ''}" 
                                        onclick="event.stopPropagation(); advisorInterface.callTicket(${validatedTicket.id})"
                                        ${!isFirst ? 'disabled title="Seul le premier ticket peut être appelé"' : ''}>
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

    validateTicketData(ticket) {
        console.log('🔍 DEBUG Ticket data:', ticket);

        let clientName = 'Nom non renseigné';
        const nameFields = ['prenom', 'nom', 'nom_complet', 'client_name', 'name'];
        
        for (const field of nameFields) {
            if (ticket[field] && ticket[field].toString().trim() !== '') {
                clientName = ticket[field].toString().trim();
                console.log(`✅ Nom trouvé dans le champ: ${field} = "${clientName}"`);
                break;
            }
        }
        
        if (clientName === 'Nom non renseigné') {
            console.warn('⚠️ ATTENTION: Aucun nom trouvé dans les champs:', nameFields);
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
                const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
                
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
                                <small class="text-muted">${validatedTicket.prenom} - ${waitingTime}min</small>
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

    updateMyStats(stats) {
        // Implémenter si nécessaire
    }

    // ===== Modal Methods =====
    showTicketDetails(ticketId) {
        const ticket = this.ticketsData.find(t => t.id === ticketId) || this.currentTicket;
        if (!ticket) {
            this.showNotification('error', 'Erreur', 'Ticket non trouvé');
            return;
        }

        const validatedTicket = this.validateTicketData(ticket);
        const waitingTime = this.calculateRealWaitingTime(validatedTicket.heure_d_enregistrement || validatedTicket.created_at);
        
        let statusBadge = '';
        let statusText = '';
        
        if (waitingTime > 30) {
            statusBadge = '<span class="badge badge-danger">Urgent</span>';
            statusText = 'Temps d\'attente élevé';
        } else if (waitingTime > 15) {
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
                            <tr><td class="font-weight-semibold">Attente réelle:</td><td>${waitingTime}min</td></tr>
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
                </div>
            </div>
        `;
        
        $('#ticketDetailsModal').modal('show');
        if (typeof feather !== 'undefined') feather.replace();
    }

    // ===== ✅ MÉTHODE CORRIGÉE : Rendu de l'historique avec commentaires =====
    renderHistoryModal(tickets, summary) {
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
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">${summary.total_tickets_traites || 0}</h4>
                            <small class="text-muted">Total traités</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">${summary.tickets_resolus || 0}</h4>
                            <small class="text-muted">Résolus</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-danger">${summary.tickets_non_resolus || 0}</h4>
                            <small class="text-muted">Non résolus</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">${summary.taux_resolution || 0}%</h4>
                            <small class="text-muted">Taux résolution</small>
                        </div>
                    </div>
                </div>
                
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
                                <th>Résolution</th>
                                <th>Commentaire</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            tickets.forEach((ticket, index) => {
                const validatedTicket = this.validateTicketData(ticket);
                const resolutionDetails = ticket.resolution_details || {};
                
                // ✅ CORRECTION : Gestion appropriée des commentaires
                let commentCell = '<span class="text-muted">-</span>';
                
                // Chercher le commentaire dans plusieurs endroits possibles
                const commentSource = 
                    ticket.commentaire_resolution || 
                    resolutionDetails.commentaire_resolution || 
                    resolutionDetails.commentaire || 
                    ticket.commentaire || 
                    null;
                
                if (commentSource && commentSource.trim() !== '') {
                    const commentText = commentSource.trim();
                    const shortComment = commentText.length > 50 ? 
                        commentText.substring(0, 50) + '...' : 
                        commentText;
                    
                    commentCell = `
                        <span class="comment-preview" 
                              title="${this.escapeHtml(commentText)}"
                              onclick="advisorInterface.showCommentModal('${this.escapeHtml(commentText)}', '${validatedTicket.numero_ticket}')"
                              style="cursor: pointer; color: #007bff;">
                            <i data-feather="message-circle" class="mr-1" style="width: 14px; height: 14px;"></i>
                            ${this.escapeHtml(shortComment)}
                        </span>
                    `;
                }
                
                // ✅ Déterminer le statut de résolution
                let resolutionBadge = '<span class="badge badge-secondary">Inconnu</span>';
                if (resolutionDetails.resolu !== undefined) {
                    if (resolutionDetails.resolu === 1 || resolutionDetails.resolu === '1' || resolutionDetails.resolu === true) {
                        resolutionBadge = '<span class="badge badge-success">Résolu</span>';
                    } else {
                        resolutionBadge = '<span class="badge badge-danger">Non résolu</span>';
                    }
                } else if (resolutionDetails.resolu_libelle) {
                    const isResolved = resolutionDetails.resolu_libelle.toLowerCase().includes('résolu') || 
                                     resolutionDetails.resolu_libelle.toLowerCase().includes('traite');
                    resolutionBadge = `<span class="badge badge-${isResolved ? 'success' : 'danger'}">${resolutionDetails.resolu_libelle}</span>`;
                }
                
                html += `
                    <tr>
                        <td><strong>${validatedTicket.numero_ticket}</strong></td>
                        <td>${validatedTicket.prenom}</td>
                        <td><span class="badge badge-info">${validatedTicket.service}</span></td>
                        <td>${ticket.debut_traitement || ticket.heure_prise_en_charge || '--:--'}</td>
                        <td>${ticket.fin_traitement || ticket.heure_fin_traitement || '--:--'}</td>
                        <td><span class="badge badge-light">${ticket.duree_traitement || 0}min</span></td>
                        <td>${resolutionBadge}</td>
                        <td>${commentCell}</td>
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

    // ===== ✅ NOUVELLE MÉTHODE : Afficher le modal des commentaires =====
    showCommentModal(commentText, ticketNumber) {
        // Créer un modal dynamique pour afficher le commentaire complet
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
        
        // Ajouter le modal au DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Afficher le modal
        $(`#${modalId}`).modal('show');
        
        // Nettoyer le modal après fermeture
        $(`#${modalId}`).on('hidden.bs.modal', function () {
            $(this).remove();
        });
        
        if (typeof feather !== 'undefined') feather.replace();
    }

    // ===== ✅ MÉTHODE UTILITAIRE : Échapper HTML =====
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
    async apiCall(method, url, data = null, signal = null) {
        const options = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        };

        // ✅ Ajouter le signal d'abort si fourni
        if (signal) {
            options.signal = signal;
        }

        if (data) {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await response.json();
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
        if (this.waitingTimeUpdateInterval) {
            clearInterval(this.waitingTimeUpdateInterval);
        }
        if (this.ticketTimer) {
            clearInterval(this.ticketTimer);
        }
        console.log('AdvisorInterface FIFO with simplified flow destroyed');
    }
}

// ===== Initialisation =====
let advisorInterface;

document.addEventListener('DOMContentLoaded', function() {
    advisorInterface = new AdvisorInterface();
    
    // ✅ NOUVEAU : Gérer la fermeture du modal lors de la résolution
    $('#currentTicketModal').on('hidden.bs.modal', function () {
        if (advisorInterface && advisorInterface.ticketTimer) {
            advisorInterface.stopModalTimer();
        }
    });
    
    // ✅ NOUVEAU : Gérer les raccourcis clavier
    $(document).on('keydown', function(e) {
        // Échap pour fermer le modal du ticket en cours
        if (e.key === 'Escape' && $('#currentTicketModal').hasClass('show')) {
            advisorInterface.closeCurrentTicketModal();
        }
    });
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log('🎯 Interface conseiller FIFO avec flow simplifié ready - Version CORRIGÉE avec commentaires');
});

window.addEventListener('beforeunload', function() {
    if (advisorInterface) {
        advisorInterface.destroy();
    }
}); 
</script>

@endsection