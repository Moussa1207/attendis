<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Dashboard|Attendis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <!-- CSRF Token pour requêtes AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{asset('frontend/assets/images/favicon.ico')}}">

    <!-- jvectormap -->
    <link href="{{asset('frontend/plugins//jvectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet">

    <!-- App css -->
    <link href="{{asset('frontend/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/assets/css/jquery-ui.min.css')}}" rel="stylesheet">
    <link href="{{asset('frontend/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/assets/css/metisMenu.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/plugins//daterangepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('frontend/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />
</head>

<body class="dark-sidenav">
<!-- Left Sidenav -->
<div class="left-sidenav">
    <!-- LOGO -->
    <div class="brand">
        <a href="{{ route('dashboard') }}" class="logo">
            <span>
                <img src="{{asset('frontend/assets/images/logo-sm.png')}}" alt="logo-small" class="logo-sm">
            </span>
            <span>
                <img src="{{asset('frontend/assets/images/logo.png')}}" alt="logo-large" class="logo-lg logo-light">
                <img src="{{asset('frontend/assets/images/logo-dark.png')}}" alt="logo-large" class="logo-lg logo-dark">
            </span>
        </a>
    </div>
    <!--end logo-->
    
    <div class="menu-content h-100" data-simplebar>
        <ul class="metismenu left-sidenav-menu">
            @if(Auth::user()->isEcranUser())
                {{-- ========================================== --}}
                {{-- MENU SIMPLE POUR POSTE ECRAN --}}
                {{-- ========================================== --}}
                
                <li class="menu-label mt-0">MENU</li>
                
                <!-- Menu unique : Écran -->
                <li>
                    <a href="{{ route('layouts.app-users') }}"> 
                        <i data-feather="monitor" class="align-self-center menu-icon"></i>
                        <span>Écran</span>
                    </a>
                </li>

            @elseif(Auth::user()->isAccueilUser())
                {{-- ========================================== --}}
                {{-- MENU SIMPLE POUR POSTE ACCUEIL --}}
                {{-- ========================================== --}}
                
                <li class="menu-label mt-0">MENU</li>
                
                <!-- Menu unique : Accueil -->
                <li>
                    <a href="{{ route('layouts.app-users') }}"> 
                        <i data-feather="users" class="align-self-center menu-icon"></i>
                        <span>Accueil</span>
                    </a>
                </li>

            @else
                {{-- ========================================== --}}
                {{-- SIDEBAR COMPLÈTE POUR AUTRES UTILISATEURS --}}
                {{-- ========================================== --}}

                <li class="menu-label mt-0">Menu</li>

                <!-- Dashboard Section -->
                <li>
                    <a href="javascript: void(0);"> 
                        <i data-feather="home" class="align-self-center menu-icon"></i>
                        <span>Dashboard</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('layouts.app') }}">
                                    <i class="ti-control-record"></i>Statistiques
                                </a>
                                <a class="nav-link" href="{{ route('layouts.history') }}">
                                  <i class="ti-control-record"></i>Historique
                                </a>

                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('layouts.app-users') }}">
                                    <i class="ti-control-record"></i>Mon Espace
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                @if(Auth::user()->isAdmin())
                <!-- Section Admin uniquement -->
                <hr class="hr-dashed hr-menu">
                <li class="menu-label my-2">Administration</li>

                <!-- Gestion des Utilisateurs -->
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="users" class="align-self-center menu-icon"></i>
                        <span>Utilisateurs</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li>
                            <a href="{{ route('User.user-create') }}">
                                <i class="ti-control-record"></i>Nouveau
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('user.users-list') }}">
                                <i class="ti-control-record"></i>Liste
                            </a>                           
                        </li>
                    </ul>                        
                </li>

                <!-- Gestion des Agences -->
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="home" class="align-self-center menu-icon"></i>
                        <span>Agence</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li>
                            <a href="{{ route('agency.agence-create') }}">
                                <i class="ti-control-record"></i>Nouveau
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('agency.agence') }}">
                                <i class="ti-control-record"></i>Liste
                            </a>                           
                        </li>
                    </ul>                        
                </li>

                <!-- Service -->
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="briefcase" class="align-self-center menu-icon"></i>
                        <span>Service</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li>
                            <a href="{{ route('service.service-create') }}">
                                <i class="ti-control-record"></i>Nouveau
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('service.service-list') }}">
                                <i class="ti-control-record"></i>Liste
                            </a>                           
                        </li>
                    </ul>                        
                </li>

                <!-- Paramètres -->
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="settings" class="align-self-center menu-icon"></i>
                        <span>Paramètre</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li>
                            <a href="{{ route('layouts.setting') }}">
                                <i class="ti-control-record"></i>Général
                            </a>
                        </li>
                    </ul>
                </li>
                @else
                <!-- Section Utilisateur Normal (Accueil/Conseiller) -->
                <hr class="hr-dashed hr-menu">
                <li class="menu-label my-2">Mon Compte</li>

                <!-- Profil utilisateur -->
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="user" class="align-self-center menu-icon"></i>
                        <span>Mon Profil</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li>
                            <a href="{{ route('layouts.app-users') }}">
                                <i class="ti-control-record"></i>Tableau de bord
                            </a>                           
                        </li>
                        <li>
                            <a href="javascript: void(0);" onclick="showPasswordModal()">
                                <i class="ti-control-record"></i>Changer mot de passe
                            </a>
                        </li>
                    </ul>                        
                </li>

                <!-- Aide utilisateur -->
                <li>
                    <a href="javascript: void(0);" onclick="showSupportModal()">
                        <i data-feather="help-circle" class="align-self-center menu-icon"></i>
                        <span>Aide & Support</span>
                    </a>
                </li>
                @endif

                <!-- Section commune -->
                <hr class="hr-dashed hr-menu">
                <li class="menu-label my-2">Système</li>

                <!-- Informations compte -->
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="info" class="align-self-center menu-icon"></i>
                        <span>Informations</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li>
                            <a href="javascript: void(0);" onclick="showAccountInfo()">
                                <i class="ti-control-record"></i>Mon Compte
                            </a>
                        </li>
                        @if(Auth::user()->wasCreatedByAdmin())
                        <li>
                            <a href="javascript: void(0);" onclick="showCreatorInfo()">
                                <i class="ti-control-record"></i>Créé par
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- Déconnexion pour autres utilisateurs -->
                <li>
                    <a href="javascript: void(0);" onclick="confirmLogout()">
                        <i data-feather="power" class="align-self-center menu-icon"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
<!-- end left-sidenav-->

@yield('contenu')

<!-- Form de déconnexion caché -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

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
<script src="{{asset('frontend/plugins//apex-charts/apexcharts.min.js')}}"></script>
<script src="{{asset('frontend/plugins//jvectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
<script src="{{asset('frontend/plugins//jvectormap/jquery-jvectormap-us-aea-en.js')}}"></script>
<script src="{{asset('frontend/assets/pages/jquery.analytics_dashboard.init.js')}}"></script>
<script src="{{asset('frontend/assets/js/app.js')}}"></script>

<!-- Scripts globaux pour la navigation -->
<script>
// Configuration CSRF pour requêtes AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Fonctions globales pour la navigation
function confirmLogout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        document.getElementById('logout-form').submit();
    }
}

// Fonction pour afficher les informations du compte
function showAccountInfo() {
    var userType = '{{ Auth::user()->getUserRole() }}';
    var username = '{{ Auth::user()->username }}';
    var email = '{{ Auth::user()->email ?? "" }}';
    var typeName = '{{ Auth::user()->getTypeName() }}';
    var statusName = '{{ Auth::user()->getStatusName() }}';
    var createdAt = '{{ Auth::user()->created_at->format("d/m/Y") }}';
    
    if (userType === 'ecran') {
        alert('Informations de votre poste:\n' +
              '- Nom: ' + username + '\n' +
              '- Type: ' + typeName + '\n' +
              '- Statut: ' + statusName + '\n' +
              '- Poste configuré le: ' + createdAt + '\n' +
              '- Interface: Prise de ticket automatisée');
    } else {
        alert('Informations de votre compte:\n' +
              '- Nom: ' + username + '\n' +
              '- Email: ' + email + '\n' +
              '- Type: ' + typeName + '\n' +
              '- Statut: ' + statusName + '\n' +
              '- Inscription: ' + createdAt);
    }
}

// Fonction pour afficher les informations du créateur (si applicable)
@if(Auth::user()->wasCreatedByAdmin())
function showCreatorInfo() {
    @php
        $creator = Auth::user()->getCreator();
    @endphp
    var creatorName = '{{ $creator ? $creator->username : "Inconnu" }}';
    var createdAt = '{{ Auth::user()->created_at->format("d/m/Y à H:i") }}';
    
    alert('Votre compte a été créé par:\n' +
          '- Administrateur: ' + creatorName + '\n' +
          '- Date de création: ' + createdAt);
}
@endif

// Fonctions spécifiques selon le type d'utilisateur
var userRole = '{{ Auth::user()->getUserRole() }}';

if (userRole !== 'ecran' && userRole !== 'admin') {
    function showPasswordModal() {
        if (typeof showPasswordModal !== 'undefined') {
            showPasswordModal();
        } else {
            alert('Fonction de changement de mot de passe non disponible sur cette page.');
        }
    }

    function showSupportModal() {
        alert('Support Attendis:\n' +
              '- Pour toute question, contactez votre administrateur\n' +
              '- Email support: support@attendis.com\n' +
              '- Les administrateurs peuvent vous aider avec:\n' +
              '  * Modification de profil\n' +
              '  * Problèmes de connexion\n' +
              '  * Questions générales');
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Marquer l'élément de menu actif
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.nav-link');
    
    menuLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
            // Ouvrir le menu parent si nécessaire
            const parentMenu = link.closest('.nav-second-level');
            if (parentMenu) {
                parentMenu.classList.add('show');
                parentMenu.setAttribute('aria-expanded', 'true');
            }
        }
    });
});

// Système de vérification de session (conservé depuis l'original)
class AttendisSessionChecker {
    constructor(options = {}) {
        this.options = {
            checkInterval: options.checkInterval || 30000,
            warningTime: options.warningTime || 300000,
            enableDebug: options.enableDebug || false,
            ...options
        };
        
        this.isChecking = false;
        this.warningShown = false;
        this.lastCheck = null;
        
        this.init();
    }

    init() {
        if (!this.isAuthenticated()) {
            return;
        }

        this.log('SessionChecker initialized');
        this.startPeriodicCheck();
        this.setupWindowEvents();
        this.checkSession();
    }

    isAuthenticated() {
        return document.querySelector('meta[name="csrf-token"]') !== null;
    }

    startPeriodicCheck() {
        this.intervalId = setInterval(() => {
            if (document.hasFocus() || !this.lastCheck || 
                (Date.now() - this.lastCheck) > this.options.checkInterval * 2) {
                this.checkSession();
            }
        }, this.options.checkInterval);
    }

    setupWindowEvents() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.log('Window hidden, pausing checks');
            } else {
                this.log('Window visible, resuming checks');
                this.checkSession();
            }
        });

        window.addEventListener('focus', () => {
            this.checkSession();
        });
    }

    async checkSession() {
        if (this.isChecking) {
            return;
        }

        this.isChecking = true;
        this.lastCheck = Date.now();

        try {
            const closureResponse = await this.fetchSessionClosure();
            
            if (closureResponse.should_logout) {
                this.handleForcedLogout(closureResponse);
                return;
            }

            const sessionInfo = await this.fetchSessionInfo();
            
            if (!sessionInfo.authenticated) {
                this.handleSessionExpired();
                return;
            }

            this.handleRequiredActions(sessionInfo.required_actions || []);
            this.checkClosureWarning(sessionInfo.session_settings);
            this.log('Session check completed', sessionInfo);

        } catch (error) {
            this.log('Session check failed', error);
            
            if (error.status === 401 || error.status === 403) {
                this.handleSessionExpired();
            }
        } finally {
            this.isChecking = false;
        }
    }

    async fetchSessionClosure() {
        const response = await fetch('/api/session/check-closure', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw { status: response.status, message: response.statusText };
        }

        return await response.json();
    }

    async fetchSessionInfo() {
        const response = await fetch('/api/session/info', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw { status: response.status, message: response.statusText };
        }

        return await response.json();
    }

    handleForcedLogout(response) {
        this.stopPeriodicCheck();
        this.showNotification('warning', response.message || 'Votre session a été fermée automatiquement.');
        setTimeout(() => {
            window.location.href = response.redirect_url || '/login';
        }, 3000);
    }

    handleSessionExpired() {
        this.stopPeriodicCheck();
        this.showNotification('error', 'Votre session a expiré. Redirection vers la page de connexion...');
        setTimeout(() => {
            window.location.href = '/login';
        }, 3000);
    }

    handleRequiredActions(actions) {
        actions.forEach(action => {
            switch (action.type) {
                case 'password_change':
                    this.showNotification('info', action.message, { persistent: true });
                    break;
                case 'session_closure':
                    this.showClosureWarning(action.message);
                    break;
                case 'security_warning':
                    this.showNotification('warning', action.message);
                    break;
            }
        });
    }

    checkClosureWarning(settings) {
        if (!settings.auto_closure_enabled || settings.should_close_now) {
            return;
        }

        const closureTime = settings.closure_time;
        if (!closureTime) {
            return;
        }

        const now = new Date();
        const [hours, minutes] = closureTime.split(':').map(Number);
        const closureDate = new Date();
        closureDate.setHours(hours, minutes, 0, 0);

        const timeUntilClosure = closureDate.getTime() - now.getTime();

        if (timeUntilClosure > 0 && timeUntilClosure <= this.options.warningTime && !this.warningShown) {
            const minutesLeft = Math.floor(timeUntilClosure / 60000);
            this.showClosureWarning(`Votre session se fermera automatiquement dans ${minutesLeft} minute(s) (${closureTime}).`);
            this.warningShown = true;
        }
    }

    showClosureWarning(message) {
        this.showNotification('warning', message, { 
            persistent: true,
            actionButton: {
                text: 'Prolonger',
                action: () => this.requestSessionExtension()
            }
        });
    }

    async requestSessionExtension() {
        try {
            this.showNotification('info', 'Demande d\'extension envoyée...');
        } catch (error) {
            this.showNotification('error', 'Impossible de prolonger la session.');
        }
    }

    showNotification(type, message, options = {}) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show session-notification`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
        `;
        
        notification.innerHTML = `
            <i class="mdi mdi-${this.getIcon(type)} mr-2"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
            ${options.actionButton ? `
                <button type="button" class="btn btn-sm btn-outline-${type} ml-2" onclick="this.parentNode.remove(); (${options.actionButton.action})()">
                    ${options.actionButton.text}
                </button>
            ` : ''}
        `;

        document.body.appendChild(notification);

        if (!options.persistent) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    }

    getIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'alert-circle',
            'warning': 'alert',
            'info': 'information'
        };
        return icons[type] || 'information';
    }

    log(message, data = null) {
        if (this.options.enableDebug) {
            console.log(`[AttendisSessionChecker] ${message}`, data);
        }
    }

    destroy() {
        this.stopPeriodicCheck();
        this.log('SessionChecker destroyed');
    }

    stopPeriodicCheck() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }
}

// Auto-initialisation si jQuery est disponible
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('meta[name="csrf-token"]')) {
        window.attendisSessionChecker = new AttendisSessionChecker({
            enableDebug: false,
            checkInterval: 30000,
            warningTime: 300000
        });
        
        console.log('Attendis Session Checker initialized');
    }
});

// Nettoyer lors du déchargement de la page
window.addEventListener('beforeunload', function() {
    if (window.attendisSessionChecker) {
        window.attendisSessionChecker.destroy();
    }
});
</script>

</body>
</html>