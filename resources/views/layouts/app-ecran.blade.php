<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <title>Poste Ecran | Attendis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Interface Poste Ecran - Prise de Ticket" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <!-- CSRF Token pour requêtes AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{asset('frontend/assets/images/favicon.ico')}}">

    <!-- App css -->
    <link href="{{asset('frontend/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/assets/css/jquery-ui.min.css')}}" rel="stylesheet">
    <link href="{{asset('frontend/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/assets/css/metisMenu.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/plugins//daterangepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />

    <style>
        /* ==================================================================================== */
        /* ✅ STYLES ORIGINAUX CONSERVÉS + AMÉLIORATIONS MÉTIER */
        /* ==================================================================================== */

        body {
            background-color: #f8f9fa !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .page-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
            transition: all 0.3s ease;
        }

        /* ✅ TOPBAR CLASSIQUE CONSERVÉ */
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .page-content {
            margin-top: 70px;
            padding: 20px;
            min-height: calc(100vh - 70px);
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        /* ✅ MODE COMPACT CONSERVÉ */
        .compact-mode .topbar {
            height: 50px !important;
            padding: 0 15px !important;
        }

        .compact-mode .navbar-custom {
            min-height: 50px !important;
        }

        .compact-mode .topbar-nav .nav-link {
            padding: 8px 10px !important;
        }

        .compact-mode .page-content {
            margin-top: 50px !important;
            padding: 15px !important;
        }

        .compact-mode .welcome-banner {
            padding: 15px !important;
            margin-bottom: 15px !important;
        }

        .compact-mode .welcome-banner h3 {
            font-size: 1.3rem !important;
            margin-bottom: 8px !important;
        }

        .compact-mode .welcome-banner p {
            font-size: 0.85rem !important;
        }

        .compact-mode .section-header {
            padding: 15px !important;
            margin-bottom: 15px !important;
        }

        .compact-mode .section-title {
            font-size: 1.2rem !important;
            margin-bottom: 5px !important;
        }

        .compact-mode .section-subtitle {
            font-size: 0.85rem !important;
        }

        .compact-mode .services-grid {
            gap: 15px !important;
        }

        .compact-mode .card-img-top {
            height: 120px !important;
        }

        .compact-mode .card-img-top i {
            font-size: 2rem !important;
        }

        .compact-mode .card-header {
            padding: 15px 15px 10px !important;
        }

        .compact-mode .card-body {
            padding: 0 15px 15px !important;
        }

        .compact-mode .card-title {
            font-size: 1.1rem !important;
        }

        .compact-mode .card-text {
            font-size: 0.8rem !important;
            margin-bottom: 12px !important;
        }

        /* Masquer certains éléments en mode compact */
        .compact-mode .dropdown.hide-phone,
        .compact-mode .dropdown.notification-list,
        .compact-mode .creat-btn {
            display: none !important;
        }

        .compact-mode .nav-user-name {
            display: none !important;
        }

        /* ==================================================================================== */
        /* ✅ BANNIÈRE DE BIENVENUE CONSERVÉE */
        /* ==================================================================================== */

        .welcome-banner {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007bff, #6c757d);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        /* ==================================================================================== */
        /* ✅ CARTES DE SERVICES AMÉLIORÉES - MAXIMUM 4 PAR LIGNE */
        /* ==================================================================================== */

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
            max-width: 100%;
        }

        /* ✅ FORCER MAXIMUM 4 COLONNES SUR GRANDS ÉCRANS */
        @media (min-width: 1400px) {
            .services-grid {
                grid-template-columns: repeat(4, 1fr);
                max-width: 1320px;
                margin: 20px auto 0;
            }
        }

        @media (min-width: 1200px) and (max-width: 1399px) {
            .services-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 992px) and (max-width: 1199px) {
            .services-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 767px) {
            .services-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        .service-card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            border-color: #007bff;
        }

        .card-img-top {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #dee2e6;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow: hidden;
        }

        .card-img-top i {
            font-size: 3rem;
            color: #6c757d;
            opacity: 0.7;
            z-index: 2;
            position: relative;
        }

        /* ✅ OVERLAY POUR AMÉLIORER LA LISIBILITÉ DES ICÔNES SUR LES IMAGES */
        .card-img-top::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .card-img-top.has-image::before {
            opacity: 1;
        }

        .card-img-top.has-image i {
            color: white !important;
            opacity: 1;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.7);
        }

        /* ✅ NOUVEAUX ÉTATS VISUELS POUR LES SERVICES */
        .service-card.actif .card-img-top {
            border-left: 4px solid #28a745;
        }

        .service-card.actif .card-img-top:not(.has-image) {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .service-card.actif .card-img-top:not(.has-image) i {
            color: #28a745;
        }

        .service-card.inactif .card-img-top {
            border-left: 4px solid #ffc107;
        }

        .service-card.inactif .card-img-top:not(.has-image) {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        }

        .service-card.inactif .card-img-top:not(.has-image) i {
            color: #ffc107;
        }

        .service-card.ferme {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .service-card.ferme .card-img-top {
            border-left: 4px solid #dc3545;
        }

        .service-card.ferme .card-img-top:not(.has-image) {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }

        .service-card.ferme .card-img-top:not(.has-image) i {
            color: #dc3545;
        }

        .card-header {
            background: transparent;
            border: none;
            padding: 20px 20px 15px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0;
        }

        .card-body {
            padding: 0 20px 20px;
        }

        .card-text {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        /* ✅ NOUVELLES STATISTIQUES DE FILE D'ATTENTE */
        .service-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .service-stat {
            text-align: center;
            flex: 1;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ✅ BOUTON DE PRISE DE TICKET */
        .take-ticket-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .take-ticket-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .take-ticket-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .take-ticket-btn.success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .take-ticket-btn.warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
        }

        /* ==================================================================================== */
        /* ✅ SECTION TITRE CONSERVÉE */
        /* ==================================================================================== */

        .section-header {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            color: #343a40;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .section-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        /* ==================================================================================== */
        /* ✅ MODAL POUR PRISE DE TICKET */
        /* ==================================================================================== */

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

        .form-group label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* ==================================================================================== */
        /* ✅ TICKET GÉNÉRÉ */
        /* ==================================================================================== */

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

        .ticket-info {
            margin-bottom: 20px;
        }

        .ticket-info p {
            margin-bottom: 8px;
            color: #495057;
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

        /* ==================================================================================== */
        /* ✅ ÉTATS VIDES ET LOADING CONSERVÉS */
        /* ==================================================================================== */

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .loading-shimmer {
            background: linear-gradient(90deg, #f8f9fa 25%, #e9ecef 50%, #f8f9fa 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* ==================================================================================== */
        /* ✅ RESPONSIVE DESIGN AMÉLIORÉ POUR 4 COLONNES MAX */
        /* ==================================================================================== */

        @media (max-width: 576px) {
            .welcome-banner {
                padding: 16px;
            }

            .user-avatar {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .section-header {
                padding: 16px;
            }

            .section-title {
                font-size: 1.25rem;
            }

            .page-content {
                padding: 10px;
            }

            .card-img-top {
                height: 120px;
            }

            .card-img-top i {
                font-size: 2rem;
            }
        }

        /* ==================================================================================== */
        /* ✅ ANIMATIONS CONSERVÉES */
        /* ==================================================================================== */

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

        .btn {
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        /* ==================================================================================== */
        /* ✅ FOOTER CONSERVÉ */
        /* ==================================================================================== */

        .footer {
            background: #ffffff;
            border-top: 1px solid #dee2e6;
            padding: 15px 0;
            margin-top: 40px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* ==================================================================================== */
        /* ✅ MODES D'AFFICHAGE CONSERVÉS */
        /* ==================================================================================== */

        .button-menu-mobile {
            transition: all 0.3s ease;
        }

        .button-menu-mobile:hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
            border-radius: 6px;
        }

        .extended-mode-active {
            background-color: rgba(40, 167, 69, 0.15) !important;
            border-radius: 6px;
        }
    </style>

    <!-- ==================================================================================== -->
    <!-- ✅ JAVASCRIPT AMÉLIORÉ AVEC TÉLÉCHARGEMENT -->
    <!-- ==================================================================================== -->
    <script>
        // ==================================================================================== 
        // ✅ VARIABLES GLOBALES
        // ==================================================================================== 
        let refreshInterval;
        let isRefreshing = false;
        let isExtendedMode = false;
        let currentService = null;
        let currentTicketData = null;

        // Configuration CSRF pour AJAX
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration CSRF
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            }
        });

        // ==================================================================================== 
        // ✅ FONCTIONS EXISTANTES CONSERVÉES
        // ==================================================================================== 

        function toggleFullscreen() {
            console.log('🔄 toggleFullscreen() appelée');
            if (!isExtendedMode) {
                setExtendedMode();
            } else {
                setCompactMode();
            }
        }

        function setCompactMode() {
            const pageWrapper = document.querySelector('.page-wrapper');
            const menuBtn = document.querySelector('.button-menu-mobile');
            const menuIcon = document.getElementById('fullscreen-icon');

            if (pageWrapper) pageWrapper.classList.add('compact-mode');
            if (menuBtn) {
                menuBtn.classList.remove('extended-mode-active');
                menuBtn.title = 'Basculer en mode étendu (interface complète)';
            }
            if (menuIcon) menuIcon.setAttribute('data-feather', 'menu');
            
            isExtendedMode = false;
            
            try {
                localStorage.setItem('ecran_mode', 'compact');
            } catch(e) {}
            
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            showToast('Mode Écran', 'Mode compact activé', 'info');
            console.log('📱 Mode compact activé');
        }

        function setExtendedMode() {
            const pageWrapper = document.querySelector('.page-wrapper');
            const menuBtn = document.querySelector('.button-menu-mobile');
            const menuIcon = document.getElementById('fullscreen-icon');

            if (pageWrapper) pageWrapper.classList.remove('compact-mode');
            if (menuBtn) {
                menuBtn.classList.add('extended-mode-active');
                menuBtn.title = 'Revenir en mode compact';
            }
            if (menuIcon) menuIcon.setAttribute('data-feather', 'menu');
            
            isExtendedMode = true;
            
            try {
                localStorage.setItem('ecran_mode', 'extended');
            } catch(e) {}
            
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            showToast('Mode Écran', 'Mode étendu activé - Interface complète', 'success');
            console.log('🖥️ Mode étendu activé');
        }

        function refreshServices() {
            console.log('🔄 refreshServices() appelée - Actualisation simple');
            if (isRefreshing) {
                console.log('⏸️ Refresh déjà en cours, ignoré');
                return;
            }
            
            isRefreshing = true;
            showRefreshIndicator();
            
            showToast('Services', 'Actualisation en cours...', 'info');
            
            setTimeout(() => {
                location.reload();
            }, 800);
        }

        function showRefreshIndicator() {
            const indicator = document.getElementById('refresh-indicator');
            if (indicator) {
                indicator.classList.add('show');
            }
        }

        function hideRefreshIndicator() {
            const indicator = document.getElementById('refresh-indicator');
            if (indicator) {
                indicator.classList.remove('show');
            }
        }

        // ==================================================================================== 
        // ✅ NOUVELLES FONCTIONS POUR PRISE DE TICKET
        // ==================================================================================== 

        function selectService(serviceId, serviceName, statut) {
            console.log('🎫 selectService() appelée pour:', serviceId, serviceName, statut);
            
            if (statut !== 'actif') {
                showToast('Service', 'Ce service n\'est pas disponible actuellement', 'warning');
                return;
            }

            currentService = {
                id: serviceId,
                name: serviceName,
                statut: statut
            };

            // Ouvrir le modal de prise de ticket
            $('#ticketModal').modal('show');
            document.getElementById('modalServiceName').textContent = serviceName;
        }

        function generateTicket() {
            console.log('🎫 generateTicket() appelée pour service:', currentService);
            
            if (!currentService) {
                showToast('Erreur', 'Aucun service sélectionné', 'error');
                return;
            }

            const form = document.getElementById('ticketForm');
            const formData = new FormData(form);
            
            // ✅ VALIDATION DES CHAMPS OBLIGATOIRES
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
            
            // Animation de loading
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Génération...';
            submitBtn.disabled = true;

            // TODO: Remplacer par un vrai appel AJAX vers votre backend
            // $.ajax({
            //     url: '/ecran/generate-ticket',
            //     method: 'POST',
            //     data: formData,
            //     processData: false,
            //     contentType: false,
            //     success: function(response) {
            //         displayTicket(response.ticket);
            //     },
            //     error: function(xhr) {
            //         showToast('Erreur', 'Impossible de générer le ticket', 'error');
            //     },
            //     complete: function() {
            //         submitBtn.innerHTML = originalText;
            //         submitBtn.disabled = false;
            //     }
            // });

            // Simulation pour l'instant
            setTimeout(() => {
                const ticketData = {
                    number: generateTicketNumber(),
                    service: currentService.name,
                    position: Math.floor(Math.random() * 10) + 1,
                    estimated_time: Math.floor(Math.random() * 20) + 5,
                    date: new Date().toLocaleDateString('fr-FR'),
                    time: new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}),
                    fullName: fullName,
                    phone: phone,
                    comment: formData.get('comment') || ''
                };
                
                displayTicket(ticketData);
                
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        }

        function generateTicketNumber() {
            const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const letter = letters[Math.floor(Math.random() * letters.length)];
            const number = String(Math.floor(Math.random() * 999) + 1).padStart(3, '0');
            return letter + number;
        }

        function displayTicket(ticketData) {
            // Sauvegarder les données du ticket pour téléchargement
            currentTicketData = ticketData;
            
            // Fermer le modal de formulaire
            $('#ticketModal').modal('hide');
            
            // Remplir le modal de ticket
            document.getElementById('displayTicketNumber').textContent = ticketData.number;
            document.getElementById('displayServiceName').textContent = ticketData.service;
            document.getElementById('displayTicketDate').textContent = ticketData.date;
            document.getElementById('displayTicketTime').textContent = ticketData.time;
            document.getElementById('displayQueuePosition').textContent = ticketData.position;
            document.getElementById('displayEstimatedTime').textContent = `${ticketData.estimated_time} minutes`;
            
            // Afficher le modal de ticket
            $('#ticketDisplayModal').modal('show');
            
            showToast('Ticket', `Ticket ${ticketData.number} généré avec succès`, 'success');
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
                        body { 
                            font-family: Arial, sans-serif; 
                            padding: 20px;
                            margin: 0;
                            background: white;
                        }
                        .ticket-display { 
                            border: 2px dashed #007bff; 
                            padding: 30px; 
                            text-align: center; 
                            max-width: 400px;
                            margin: 0 auto;
                        }
                        .ticket-number { 
                            font-size: 48px; 
                            font-weight: bold; 
                            color: #007bff; 
                            margin: 20px 0; 
                            font-family: 'Courier New', monospace; 
                        }
                        .ticket-info p { 
                            margin: 10px 0;
                            font-size: 14px;
                        }
                        .queue-status { 
                            background: rgba(0, 123, 255, 0.1); 
                            padding: 15px; 
                            margin: 15px 0; 
                            border-radius: 6px; 
                        }
                        .queue-position {
                            font-size: 18px;
                            font-weight: bold;
                            color: #007bff;
                            margin-bottom: 5px;
                        }
                        .estimated-time {
                            color: #6c757d;
                            font-size: 14px;
                        }
                        @media print {
                            body { margin: 0; padding: 10px; }
                            .ticket-display { margin: 0; }
                        }
                    </style>
                </head>
                <body>${ticketContent}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // ✅ NOUVELLE FONCTION DE TÉLÉCHARGEMENT
        function downloadTicket() {
            if (!currentTicketData) {
                showToast('Erreur', 'Aucun ticket à télécharger', 'error');
                return;
            }

            try {
                // Créer le contenu du ticket pour téléchargement
                const ticketContent = generateTicketTextContent(currentTicketData);
                
                // Créer un blob avec le contenu
                const blob = new Blob([ticketContent], { type: 'text/plain;charset=utf-8' });
                
                // Créer un lien de téléchargement
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `Ticket_${currentTicketData.number}_${currentTicketData.date.replace(/\//g, '-')}.txt`;
                
                // Déclencher le téléchargement
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Nettoyer l'URL
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
            
            // Actualiser les statistiques ou la page
            setTimeout(() => {
                refreshServices();
            }, 1000);
        }

        // ==================================================================================== 
        // ✅ FONCTION TOAST CONSERVÉE
        // ==================================================================================== 

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
                if (type === 'error') {
                    console.error(`${title}: ${message}`);
                } else {
                    console.log(`${title}: ${message}`);
                }
            }
        }

        // ==================================================================================== 
        // ✅ INITIALISATION ET AUTO-REFRESH CONSERVÉS
        // ==================================================================================== 

        function startAutoRefresh() {
            console.log('⏰ Auto-refresh démarré (2 minutes)');
            refreshInterval = setInterval(() => {
                console.log('⏰ Auto-refresh déclenché');
                refreshServices();
            }, 120000); // 2 minutes pour une borne
        }

        function stopAutoRefresh() {
            console.log('⏹️ Auto-refresh arrêté');
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }

        function updateLastUpdateTime() {
            const lastUpdateElement = document.getElementById('last-update');
            if (lastUpdateElement) {
                lastUpdateElement.textContent = new Date().toLocaleTimeString();
            }
        }

        function initializeDisplayMode() {
            try {
                const savedMode = localStorage.getItem('ecran_mode');
                
                if (savedMode === 'compact') {
                    setCompactMode();
                    console.log('🔄 Mode compact restauré depuis les préférences');
                } else {
                    setExtendedMode();
                    console.log('🖥️ Mode étendu par défaut');
                }
            } catch(e) {
                setExtendedMode();
                console.log('🖥️ Mode étendu par défaut (localStorage indisponible)');
            }
        }

        // ==================================================================================== 
        // ✅ ÉVÉNEMENTS GLOBAUX
        // ==================================================================================== 

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                $('#ticketModal').modal('hide');
                $('#ticketDisplayModal').modal('hide');
            }
        });

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });

        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
    </script>
</head>

<body class="dark-sidenav">
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
                            <div class="app-search-topbar">
                                <form action="#" method="get">
                                    <input type="search" name="search" class="from-control top-search mb-0" placeholder="Rechercher un service...">
                                    <button type="submit"><i class="ti-search"></i></button>
                                </form>
                            </div>
                        </div>
                    </li>

                    <li class="dropdown">
                        <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false">
                            <span class="ml-1 nav-user-name hidden-sm">{{ $userInfo['username'] ?? 'Poste Écran' }}</span>
                            <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="profile-user" class="rounded-circle" />                                 
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-header">
                                <h6 class="text-dark mb-0">{{ $userInfo['type_info']['name'] ?? 'Poste Ecran' }}</h6>
                                <small class="text-muted">{{ $userInfo['email'] ?? '' }}</small>
                            </div>
                            <div class="dropdown-divider"></div>
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
                        <button class="nav-link button-menu-mobile" onclick="toggleFullscreen()" title="Mode compact activé - Cliquer pour étendre" id="fullscreen-btn">
                            <i data-feather="menu" class="align-self-center topbar-icon" id="fullscreen-icon"></i>
                        </button>
                    </li>                         
                </ul>
            </nav>
        </div>
        <!-- Top Bar End -->

        <!-- Page Content-->
        <div class="page-content">
            <div class="container-fluid">
                
                

                

                <!-- ✅ GRILLE DES SERVICES (DYNAMIQUE) AVEC EXEMPLES D'IMAGES - MAXIMUM 4 PAR LIGNE -->
                <div class="services-grid animate-fade-in" id="services-container">
                    @if(isset($services) && $services->count() > 0)
                        @foreach($services as $service)
                        <div class="card service-card {{ $service->statut }}" onclick="selectService({{ $service->id }}, '{{ $service->nom }}', '{{ $service->statut }}')">
                            <!-- ✅ Image d'en-tête avec liens d'images -->
                            <div class="card-img-top img-fluid bg-light-alt has-image"
                                style="background-image: url('https://via.placeholder.com/400x200/007bff/ffffff?text={{ urlencode($service->nom) }}')">
                                <i data-feather="{{ $service->statut === 'actif' ? 'check-circle' : ($service->statut === 'inactif' ? 'pause-circle' : 'x-circle') }}"></i>
                            </div>
                            
                            <!-- Header -->
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">                      
                                        <h4 class="card-title">{{ $service->nom }}</h4>               
                                    </div>
                                    <div class="col-auto">
                                        @if($service->statut === 'actif')
                                            <span class="badge badge-success">Disponible</span>
                                        @elseif($service->statut === 'inactif')
                                            <span class="badge badge-warning">Très demandé</span>
                                        @else
                                            <span class="badge badge-danger">Fermé</span>
                                        @endif            
                                    </div>                                                                         
                                </div>                                 
                            </div>
                            
                            <!-- Body -->
                            <div class="card-body">

                                <!-- ✅ BOUTON PRISE DE TICKET -->
                                @if($service->statut === 'actif')
                                    
                                @elseif($service->statut === 'inactif')
                                    <button class="take-ticket-btn warning">
                                        <i data-feather="clock" class="mr-1"></i> File d'attente longue
                                    </button>
                                @else
                                    <button class="take-ticket-btn" disabled>
                                        <i data-feather="x-circle" class="mr-1"></i> Service fermé
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <!-- État vide -->
                        <div class="col-12">
                            <div class="empty-state">
                                @if(isset($error))
                                    <div class="empty-state-icon">
                                        <i data-feather="alert-triangle" class="text-warning"></i>
                                    </div>
                                    <h5 class="text-warning">Erreur de chargement</h5>
                                    <p>{{ $error }}</p>
                                @elseif(isset($noCreator))
                                    <div class="empty-state-icon">
                                        <i data-feather="user-x" class="text-info"></i>
                                    </div>
                                    <h5 class="text-info">Compte non configuré</h5>
                                    <p>Votre compte n'est pas lié à un administrateur.<br>Contactez le support technique.</p>
                                @else
                                    <div class="empty-state-icon">
                                        <i data-feather="package" class="text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">Aucun service disponible</h5>
                                    <p>Aucun service n'a encore été configuré par votre administrateur.</p>
                                @endif
                                <button class="btn btn-outline-primary" onclick="refreshServices()">
                                    <i data-feather="refresh-cw" class="mr-1"></i> Actualiser
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <footer class="footer text-center text-sm-left">
                    <div>
                        &copy; {{ date('Y') }} Attendis - Système de Gestion de File d'Attente
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- ==================================================================================== -->
    <!-- ✅ MODALS POUR PRISE DE TICKET -->
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
                                    <label>Nom *</label>
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
        <div class="modal-dialog modal-lg" role="document">
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
                            <p><strong>Service:</strong> <span id="displayServiceName">--</span></p>
                            <p><strong>Date:</strong> <span id="displayTicketDate">--</span></p>
                            <p><strong>Heure:</strong> <span id="displayTicketTime">--</span></p>
                        </div>
                        
                        <div class="queue-status">
                            <div class="queue-position">Position dans la file: <span id="displayQueuePosition">--</span></div>
                            <div class="estimated-time">Temps d'attente estimé: <span id="displayEstimatedTime">--</span></div>
                        </div>
                        
                        <div class="alert alert-success mt-3">
                            <i data-feather="info" class="mr-2"></i>
                            <strong>Important:</strong> Conservez ce ticket et restez à proximité. Vous serez appelé par votre numéro.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="downloadTicket()">
                        <i data-feather="download" class="mr-1"></i> Télécharger
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="printTicket()">
                        <i data-feather="printer" class="mr-1"></i> Imprimer
                    </button>
                    <button type="button" class="btn btn-success" onclick="closeTicketDisplay()">
                        <i data-feather="check" class="mr-1"></i> Terminé
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{asset('frontend/assets/js/jquery.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/metismenu.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/waves.js')}}"></script>
    <script src="{{asset('frontend/assets/js/feather.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/simplebar.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/moment.js')}}"></script>
    <script src="{{asset('frontend/plugins/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{asset('frontend/assets/js/app.js')}}"></script>

    <script>
        // ==================================================================================== 
        // ✅ INITIALISATION FINALE
        // ==================================================================================== 

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔄 DOM chargé, initialisation Interface Poste Ecran...');
            
            // Initialiser Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
                console.log('✅ Feather icons initialisés');
            }

            // Initialiser le mode d'affichage
            initializeDisplayMode();
            
            // Démarrer l'auto-refresh (2 minutes pour une borne)
            startAutoRefresh();
            
            // Mettre à jour l'heure toutes les minutes
            setInterval(updateLastUpdateTime, 60000);

            console.log('✅ Interface Poste Ecran initialisée avec succès');
            
            // Message de bienvenue
            setTimeout(() => {
                showToast('Bienvenue', 'Interface de prise de ticket chargée', 'success');
            }, 1000);
        });
    </script>
</body>
</html>