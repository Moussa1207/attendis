@extends('dashboard.master')

@section('contenu')
<div class="page-wrapper">
    <!-- Top Bar Start -->
    <div class="topbar">            
        <!-- Navbar -->
        <nav class="navbar-custom">    
            <ul class="list-unstyled topbar-nav float-right mb-0">  
                <li class="dropdown hide-phone">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <i data-feather="search" class="topbar-icon"></i>
                    </a>
                    
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg p-0">
                        <!-- Top Search Bar -->
                        <div class="app-search-topbar">
                            <form action="#" method="get">
                                <input type="search" name="search" class="from-control top-search mb-0" placeholder="Rechercher un service...">
                                <button type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                    </div>
                </li>                      

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-danger badge-pill noti-icon-badge">{{ isset($services) ? $services->count() : '0' }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                    
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            Services <span class="badge badge-primary badge-pill">{{ isset($services) ? $services->count() : '0' }}</span>
                        </h6> 
                        <div class="notification-menu" data-simplebar>
                            @if(isset($services) && $services->count() > 0)
                                @foreach($services->take(4) as $service)
                                <!-- item-->
                                <a href="#" class="dropdown-item py-3" onclick="selectService({{ $service->id }}, '{{ $service->nom }}', '{{ $service->statut }}')">
                                    <div class="media">
                                        <div class="avatar-md bg-soft-primary">
                                            <i data-feather="briefcase" class="align-self-center icon-xs"></i>
                                        </div>
                                        <div class="media-body align-self-center ml-2 text-truncate">
                                            <h6 class="my-0 font-weight-normal text-dark">{{ $service->nom }}</h6>
                                            <small class="text-muted mb-0">Service disponible</small>
                                        </div><!--end media-body-->
                                    </div><!--end media-->
                                </a><!--end-item-->
                                @endforeach
                            @else
                                <a href="#" class="dropdown-item py-3">
                                    <div class="media">
                                        <div class="avatar-md bg-soft-primary">
                                            <i data-feather="info" class="align-self-center icon-xs"></i>
                                        </div>
                                        <div class="media-body align-self-center ml-2 text-truncate">
                                            <h6 class="my-0 font-weight-normal text-dark">Aucun service</h6>
                                            <small class="text-muted mb-0">Pas de services configurés</small>
                                        </div><!--end media-body-->
                                    </div><!--end media-->
                                </a><!--end-item-->
                            @endif
                        </div>
                        <!-- All-->
                        <a href="javascript:void(0);" class="dropdown-item text-center text-primary" onclick="refreshServices()">
                            Actualiser <i class="fi-arrow-right"></i>
                        </a>
                    </div>
                </li>

                <li class="dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <span class="ml-1 nav-user-name hidden-sm">{{ $userInfo['username'] ?? 'Poste Écran' }}</span>
                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="profile-user" class="rounded-circle" />                                 
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#"><i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Profil</a>
                        <a class="dropdown-item" href="#" onclick="refreshServices()"><i data-feather="refresh-cw" class="align-self-center icon-xs icon-dual mr-1"></i> Actualiser</a>
                        <div class="dropdown-divider mb-0"></div>
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Déconnexion</a>
                    </div>
                </li>
            </ul><!--end topbar-nav-->

            <ul class="list-unstyled topbar-nav mb-0">                        
                <li>
                    <button class="nav-link button-menu-mobile">
                        <i data-feather="menu" class="align-self-center topbar-icon"></i>
                    </button>
                </li> 
                <li class="creat-btn">
                    <div class="nav-link">
                        <a class=" btn btn-sm btn-soft-primary" href="#" role="button" onclick="refreshServices()"><i class="fas fa-plus mr-2"></i>Actualiser Services</a>
                    </div>                                
                </li>                           
            </ul>
        </nav>
        <!-- end navbar-->
    </div>
    <!-- Top Bar End -->

    <!-- Page Content-->
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col">
                                <h4 class="page-title">Poste Écran</h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Attendis</a></li>
                                    <li class="breadcrumb-item active">Interface Écran</li>
                                </ol>
                            </div><!--end col-->
                            <div class="col-auto align-self-center">
                                <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date" onclick="refreshServices()">
                                    <span class="ay-name" id="Day_Name">Aujourd'hui:</span>&nbsp;
                                    <span class="" id="Select_date">{{ date('d M') }}</span>
                                    <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="refreshServices()">
                                    <i data-feather="refresh-cw" class="align-self-center icon-xs"></i>
                                </a>
                            </div><!--end col-->  
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->
            <!-- end page title end breadcrumb -->
            
            <!-- ✅ SERVICES AVEC ALIGNEMENT CORRECT -->
            <div class="row">
                @if(isset($services) && $services->count() > 0)
                    @foreach($services as $service)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                        <div class="card report-card" onclick="selectService({{ $service->id }}, '{{ $service->nom }}', '{{ $service->statut }}')" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="row d-flex justify-content-center">
                                    <div class="col">
                                        <p class="text-dark mb-1 font-weight-semibold service-name">{{ $service->nom }}</p>
                                    </div>
                                    <div class="col-auto align-self-center">
                                        <div class="report-main-icon bg-light-alt" 
                                             style="background-image: url('{{ asset('images/services/' . $service->id . '.jpg') }}'); 
                                                    background-size: cover; 
                                                    background-position: center;
                                                    background-repeat: no-repeat;">
                                            <i data-feather="briefcase" class="align-self-center text-muted icon-md service-fallback-icon"></i>  
                                        </div>
                                    </div>
                                </div>
                            </div><!--end card-body--> 
                        </div><!--end card--> 
                    </div> <!--end col--> 
                    @endforeach
                @else
                    <!-- État vide -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                @if(isset($error))
                                    <div class="mb-3">
                                        <i data-feather="alert-triangle" class="text-warning" style="width: 48px; height: 48px;"></i>
                                    </div>
                                    <h5 class="text-warning">Erreur de chargement</h5>
                                    <p class="text-muted">{{ $error }}</p>
                                @elseif(isset($noCreator))
                                    <div class="mb-3">
                                        <i data-feather="user-x" class="text-info" style="width: 48px; height: 48px;"></i>
                                    </div>
                                    <h5 class="text-info">Compte non configuré</h5>
                                    <p class="text-muted">Votre compte n'est pas lié à un administrateur.<br>Contactez le support technique.</p>
                                @else
                                    <div class="mb-3">
                                        <i data-feather="package" class="text-muted" style="width: 48px; height: 48px;"></i>
                                    </div>
                                    <h5 class="text-muted">Aucun service disponible</h5>
                                    <p class="text-muted">Aucun service n'a encore été configuré par votre administrateur.</p>
                                @endif
                                <button class="btn btn-outline-primary" onclick="refreshServices()">
                                    <i data-feather="refresh-cw" class="mr-1"></i> Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div><!--end row-->

            <footer class="footer text-center text-sm-left">
                &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Interface Poste Écran - File d'attente chronologique</span>
            </footer><!--end footer-->
        </div><!-- container -->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- ==================================================================================== -->
<!-- ✅ MODALS POUR PRISE DE TICKET (CONSERVÉS) -->
<!-- ==================================================================================== -->

<!-- Modal Formulaire Ticket -->
<div class="modal fade" id="ticketModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="ticket" class="mr-2"></i>Prise de Ticket
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i data-feather="info" class="mr-2"></i>
                    Service sélectionné: <strong id="modalServiceName">--</strong>
                </div>
                
                <form id="ticketForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom complet *</label>
                                <input type="text" name="full_name" class="form-control" required placeholder="Votre nom complet">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Téléphone *</label>
                                <input type="tel" name="phone" class="form-control" required placeholder="Votre numéro de téléphone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Commentaire (optionnel)</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Précisez votre demande ou laissez vide..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="generateTicketBtn" onclick="generateTicket()">
                    <i data-feather="ticket" class="mr-1"></i> Générer mon ticket
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Affichage Ticket -->
<div class="modal fade" id="ticketDisplayModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="check-circle" class="mr-2"></i>Votre Ticket
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="ticket-display">
                    <div class="ticket-number" id="displayTicketNumber">A001</div>
                    <div class="ticket-info">
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Service:</strong> <span id="displayServiceName">--</span></p>
                                <p><strong>Date:</strong> <span id="displayTicketDate">--</span></p>
                                <p><strong>Heure:</strong> <span id="displayTicketTime">--</span></p>
                            </div>
                            <div class="col-6">
                                <div class="queue-status">
                                    <div class="queue-position">Position: <span id="displayQueuePosition">--</span></div>
                                    <div class="estimated-time">Temps estimé: <span id="displayEstimatedTime">--</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success mt-3">
                        <i data-feather="info" class="mr-2"></i>
                        <strong>Important:</strong> Conservez ce ticket et restez à proximité.
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadTicket()">
                    <i data-feather="download" class="mr-1"></i> Télécharger
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="printTicket()">
                    <i data-feather="printer" class="mr-1"></i> Imprimer
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="closeTicketDisplay()">
                    <i data-feather="check" class="mr-1"></i> Terminé
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form de déconnexion caché -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- ==================================================================================== -->
<!-- ✅ STYLES POUR MODALS ET TICKETS -->
<!-- ==================================================================================== -->
<style>
    /* Styles pour cards services - identiques à app.blade.php */
    .card.report-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .card.report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 123, 255, 0.15);
        border-color: #007bff;
    }

    /* ✅ Nom du service sur une seule ligne */
    .service-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    /* Gestion fallback icône si pas d'image dans report-main-icon */
    .report-main-icon .service-fallback-icon {
        display: block;
    }

    /* Masquer l'icône si image présente */
    .report-main-icon[style*="background-image"] .service-fallback-icon {
        display: none;
    }

    /* Styles pour modals */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.7);
    }

    .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border-radius: 8px 8px 0 0;
        border-bottom: none;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    /* Styles pour ticket */
    .ticket-display {
        background: white;
        border: 2px dashed #007bff;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        margin: 20px 0;
    }

    .ticket-number {
        font-size: 3rem;
        font-weight: 700;
        color: #007bff;
        margin-bottom: 20px;
        font-family: 'Courier New', monospace;
    }

    .queue-status {
        background: rgba(0, 123, 255, 0.1);
        border-radius: 6px;
        padding: 15px;
        margin: 15px 0;
    }

    .queue-position {
        font-size: 1.5rem;
        font-weight: 600;
        color: #007bff;
        margin-bottom: 5px;
    }

    .estimated-time {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- ==================================================================================== -->
<!-- ✅ JAVASCRIPT COMPLET CONSERVÉ -->
<!-- ==================================================================================== -->
<script>
    // Variables globales
    let currentService = null;
    let currentTicketData = null;

    // Configuration CSRF pour AJAX
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
        }
    });

    function refreshServices() {
        console.log('🔄 Actualisation des services...');
        showToast('Services', 'Actualisation en cours...', 'info');
        setTimeout(() => {
            location.reload();
        }, 800);
    }

    function selectService(serviceId, serviceName, statut) {
        console.log('🎫 Service sélectionné:', serviceId, serviceName);

        currentService = {
            id: serviceId,
            name: serviceName,
            statut: statut
        };

        $('#ticketModal').modal('show');
        document.getElementById('modalServiceName').textContent = serviceName;
    }

    function generateTicket() {
        console.log('🎫 Génération ticket pour service:', currentService);
        
        if (!currentService) {
            showToast('Erreur', 'Aucun service sélectionné', 'error');
            return;
        }

        const form = document.getElementById('ticketForm');
        const formData = new FormData(form);
        
        const fullName = formData.get('full_name');
        const phone = formData.get('phone');
        
        if (!fullName || fullName.trim() === '') {
            showToast('Erreur', 'Le nom est obligatoire', 'error');
            document.querySelector('input[name="full_name"]').focus();
            return;
        }
        
        if (!phone || phone.trim() === '') {
            showToast('Erreur', 'Le téléphone est obligatoire', 'error');
            document.querySelector('input[name="phone"]').focus();
            return;
        }
        
        formData.append('service_id', currentService.id);

        const submitBtn = document.getElementById('generateTicketBtn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Génération...';
        submitBtn.disabled = true;

        $.ajax({
            url: '{{ route("ecran.generate-ticket") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('✅ Ticket généré:', response);
                
                if (response.success) {
                    displayTicket(response.ticket);
                    showToast('Succès', response.message, 'success');
                } else {
                    showToast('Erreur', response.message || 'Erreur lors de la génération', 'error');
                }
            },
            error: function(xhr) {
                console.error('❌ Erreur génération ticket:', xhr);
                
                let errorMessage = 'Impossible de générer le ticket';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const firstError = Object.values(errors)[0];
                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                }
                
                showToast('Erreur', errorMessage, 'error');
            },
            complete: function() {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    function displayTicket(ticketData) {
        currentTicketData = ticketData;
        
        $('#ticketModal').modal('hide');
        
        document.getElementById('displayTicketNumber').textContent = ticketData.number;
        document.getElementById('displayServiceName').textContent = ticketData.service;
        document.getElementById('displayTicketDate').textContent = ticketData.date;
        document.getElementById('displayTicketTime').textContent = ticketData.time;
        document.getElementById('displayQueuePosition').textContent = ticketData.position;
        document.getElementById('displayEstimatedTime').textContent = `${ticketData.estimated_time} minutes`;
        
        $('#ticketDisplayModal').modal('show');
        
        console.log('🎫 Ticket affiché:', ticketData);
    }

    function printTicket() {
        if (!currentTicketData) {
            showToast('Erreur', 'Aucun ticket à imprimer', 'error');
            return;
        }

        const ticketContent = generateTicketHTML(currentTicketData);
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Ticket ${currentTicketData.number}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; margin: 0; background: white; }
                    .ticket-display { border: 2px dashed #007bff; padding: 30px; text-align: center; max-width: 400px; margin: 0 auto; }
                    .ticket-number { font-size: 48px; font-weight: bold; color: #007bff; margin: 20px 0; font-family: 'Courier New', monospace; }
                    .ticket-info p { margin: 10px 0; font-size: 14px; }
                    .queue-status { background: rgba(0, 123, 255, 0.1); padding: 15px; margin: 15px 0; border-radius: 6px; }
                    .queue-position { font-size: 18px; font-weight: bold; color: #007bff; margin-bottom: 5px; }
                    .estimated-time { color: #6c757d; font-size: 14px; }
                    @media print { body { margin: 0; padding: 10px; } .ticket-display { margin: 0; } }
                </style>
            </head>
            <body>${ticketContent}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    function downloadTicket() {
        if (!currentTicketData) {
            showToast('Erreur', 'Aucun ticket à télécharger', 'error');
            return;
        }

        try {
            const ticketContent = generateTicketTextContent(currentTicketData);
            const blob = new Blob([ticketContent], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `Ticket_${currentTicketData.number}_${currentTicketData.date.replace(/\//g, '-')}.txt`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
            showToast('Téléchargement', `Ticket ${currentTicketData.number} téléchargé`, 'success');
        } catch (error) {
            console.error('Erreur lors du téléchargement:', error);
            showToast('Erreur', 'Impossible de télécharger le ticket', 'error');
        }
    }

    function generateTicketHTML(ticketData) {
        return `
            <div class="ticket-display">
                <div class="ticket-number">${ticketData.number}</div>
                <div class="ticket-info">
                    <p><strong>Service:</strong> ${ticketData.service}</p>
                    <p><strong>Date:</strong> ${ticketData.date}</p>
                    <p><strong>Heure:</strong> ${ticketData.time}</p>
                    <p><strong>Client:</strong> ${ticketData.fullName}</p>
                    <p><strong>Téléphone:</strong> ${ticketData.phone}</p>
                    ${ticketData.comment ? `<p><strong>Commentaire:</strong> ${ticketData.comment}</p>` : ''}
                </div>
                <div class="queue-status">
                    <div class="queue-position">Position dans la file: ${ticketData.position}</div>
                    <div class="estimated-time">Temps d'attente estimé: ${ticketData.estimated_time} minutes</div>
                </div>
                <div style="margin-top: 20px; font-size: 12px; color: #6c757d;">
                    <strong>Important:</strong> Conservez ce ticket et restez à proximité.<br>
                    Vous serez appelé par votre numéro.
                </div>
            </div>
        `;
    }

    function generateTicketTextContent(ticketData) {
        return `
═══════════════════════════════════════
           ATTENDIS - TICKET
═══════════════════════════════════════

NUMÉRO DE TICKET: ${ticketData.number}

SERVICE: ${ticketData.service}
DATE: ${ticketData.date}
HEURE: ${ticketData.time}

CLIENT: ${ticketData.fullName}
TÉLÉPHONE: ${ticketData.phone}
${ticketData.comment ? `COMMENTAIRE: ${ticketData.comment}` : ''}

═══════════════════════════════════════
         INFORMATIONS DE FILE
═══════════════════════════════════════

POSITION DANS LA FILE: ${ticketData.position}
TEMPS D'ATTENTE ESTIMÉ: ${ticketData.estimated_time} minutes

═══════════════════════════════════════
              IMPORTANT
═══════════════════════════════════════

Conservez ce ticket et restez à proximité.
Vous serez appelé par votre numéro.

Merci de votre patience.

═══════════════════════════════════════
        Généré le ${new Date().toLocaleString('fr-FR')}
═══════════════════════════════════════
        `;
    }

    function closeTicketDisplay() {
        $('#ticketDisplayModal').modal('hide');
        currentService = null;
        currentTicketData = null;
        document.getElementById('ticketForm').reset();
    }

    function showToast(title, message, type = 'info') {
        try {
            const colors = {
                'success': 'bg-success',
                'error': 'bg-danger',  
                'warning': 'bg-warning',
                'info': 'bg-info'
            };
            
            const toastId = 'toast_' + Date.now();
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `toast ${colors[type]} text-white position-fixed`;
            toast.style.cssText = 'top: 90px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.setAttribute('role', 'alert');
            
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="mr-auto text-white">${title}</strong>
                    <button type="button" class="ml-2 mb-1 close text-white" onclick="document.getElementById('${toastId}').remove()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">${message}</div>
            `;
            
            document.body.appendChild(toast);
            
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                toast.style.opacity = '1';
            }, 100);
            
            setTimeout(() => {
                if (document.getElementById(toastId)) {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        if (document.getElementById(toastId)) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, 4000);
            
        } catch (error) {
            console.error('Erreur toast:', error);
        }
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔄 Initialisation Interface Poste Ecran...');
        
        if (typeof feather !== 'undefined') {
            feather.replace();
            console.log('✅ Feather icons initialisés');
        }

        // Gestion des images de services
        const serviceIcons = document.querySelectorAll('.report-main-icon');
        serviceIcons.forEach(function(icon) {
            const backgroundImage = icon.style.backgroundImage;
            
            if (backgroundImage && backgroundImage !== 'none') {
                const imageUrl = backgroundImage.replace(/^url\(['"]?/, '').replace(/['"]?\)$/, '');
                const img = new Image();
                img.onload = function() {
                    const fallbackIcon = icon.querySelector('.service-fallback-icon');
                    if (fallbackIcon) {
                        fallbackIcon.style.display = 'none';
                    }
                };
                img.onerror = function() {
                    icon.style.backgroundImage = 'none';
                    const fallbackIcon = icon.querySelector('.service-fallback-icon');
                    if (fallbackIcon) {
                        fallbackIcon.style.display = 'block';
                    }
                };
                img.src = imageUrl;
            }
        });

        // Animation des cartes au chargement
        const serviceCards = document.querySelectorAll('.card.report-card');
        serviceCards.forEach((card, index) => {
            card.classList.add('animate-fade-in');
            card.style.animationDelay = `${index * 0.1}s`;
        });

        console.log('✅ Interface Poste Ecran initialisée avec succès');
        
        setTimeout(() => {
            showToast('Bienvenue', 'Interface de prise de ticket chargée', 'success');
        }, 1000);
    });

    // Gestion des touches clavier
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            $('#ticketModal').modal('hide');
            $('#ticketDisplayModal').modal('hide');
        }
    });
</script>
@endsection