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
                                <i class="ti-control-record"></i>Analytics (Admin)
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

            <!-- Gestion des Utilisateurs - AMÉLIORÉ: Ordre inversé -->
            <li>
                <a href="javascript: void(0);">
                    <i data-feather="users" class="align-self-center menu-icon"></i>
                    <span>Utilisateurs</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <!-- AMÉLIORÉ: Nouveau en premier -->
                    <li>
                        <a href="{{ route('admin.users.create') }}">
                            <i class="ti-control-record"></i>Nouveau
                        </a>
                    </li>
                    <!-- AMÉLIORÉ: Liste en second -->
                    <li>
                        <a href="{{ route('user.users-list') }}">
                            <i class="ti-control-record"></i>Liste
                        </a>                           
                    </li>
                </ul>                        
            </li>

            <!--  Gestion des Agences avec les bonnes routes -->
            <li>
                <a href="javascript: void(0);">
                    <i data-feather="home" class="align-self-center menu-icon"></i>
                    <span>Agence</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <!--  Route création agence -->
                    <li>
                        <a href="{{ route('agency.agence-create') }}">
                            <i class="ti-control-record"></i>Nouveau
                        </a>
                    </li>
                    <!--  Route liste agences -->
                    <li>
                        <a href="{{ route('agency.agence') }}">
                            <i class="ti-control-record"></i>Liste
                        </a>                           
                    </li>
                </ul>                        
            </li>

            <!-- ✅ CORRECTION: Service avec icône "briefcase" -->
            <li>
                <a href="javascript: void(0);">
                    <i data-feather="briefcase" class="align-self-center menu-icon"></i>
                    <span>Service</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <!-- NOUVEAU : Route création service -->
                    <li>
                        <a href="{{ route('service.service-create') }}">
                            <i class="ti-control-record"></i>Nouveau
                        </a>
                    </li>
                    <!-- NOUVEAU : Route liste services -->
                    <li>
                        <a href="{{ route('service.service-list') }}">
                            <i class="ti-control-record"></i>Liste
                        </a>                           
                    </li>
                </ul>                        
            </li>

            <!--  Paramètres avec icône "settings" -->
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
            <!-- Section Utilisateur Normal -->
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

            <!-- Déconnexion -->
            <li>
                <a href="javascript: void(0);" onclick="confirmLogout()">
                    <i data-feather="power" class="align-self-center menu-icon"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
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

function showAccountInfo() {
    alert(`
Informations de votre compte:
- Nom: {{ Auth::user()->username }}
- Email: {{ Auth::user()->email }}
- Type: {{ Auth::user()->getTypeName() }}
- Statut: {{ Auth::user()->getStatusName() }}
- Inscription: {{ Auth::user()->created_at->format('d/m/Y') }}
    `);
}

@if(Auth::user()->wasCreatedByAdmin())
function showCreatorInfo() {
    @php
        $creator = Auth::user()->getCreator();
    @endphp
    alert(`
Votre compte a été créé par:
- Administrateur: {{ $creator ? $creator->username : 'Inconnu' }}
- Date de création: {{ Auth::user()->created_at->format('d/m/Y à H:i') }}
    `);
}
@endif

@if(!Auth::user()->isAdmin())
function showPasswordModal() {
    // Cette fonction sera définie dans app-users.blade.php
    if (typeof showPasswordModal !== 'undefined') {
        showPasswordModal();
    } else {
        alert('Fonction de changement de mot de passe non disponible sur cette page.');
    }
}

function showSupportModal() {
    alert(`
Support Attendis:
- Pour toute question, contactez votre administrateur
- Email support: support@attendis.com
- Les administrateurs peuvent vous aider avec:
  * Modification de profil
  * Problèmes de connexion
  * Questions générales
    `);
}
@endif

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

class AttendisSessionChecker {
    constructor(options = {}) {
        this.options = {
            checkInterval: options.checkInterval || 30000, // 30 secondes
            warningTime: options.warningTime || 300000,    // 5 minutes avant fermeture
            enableDebug: options.enableDebug || false,
            ...options
        };
        
        this.isChecking = false;
        this.warningShown = false;
        this.lastCheck = null;
        
        this.init();
    }

    /**
     * Initialiser le vérificateur
     */
    init() {
        // Vérifier si l'utilisateur est connecté
        if (!this.isAuthenticated()) {
            return;
        }

        this.log('SessionChecker initialized');
        
        // Démarrer la vérification périodique
        this.startPeriodicCheck();
        
        // Écouter les événements de focus/blur pour optimiser
        this.setupWindowEvents();
        
        // Vérification initiale
        this.checkSession();
    }

    /**
     * Vérifier si l'utilisateur est authentifié
     */
    isAuthenticated() {
        // Vérifier la présence d'un token CSRF (indique une session Laravel)
        return document.querySelector('meta[name="csrf-token"]') !== null;
    }

    /**
     * Démarrer la vérification périodique
     */
    startPeriodicCheck() {
        this.intervalId = setInterval(() => {
            if (document.hasFocus() || !this.lastCheck || 
                (Date.now() - this.lastCheck) > this.options.checkInterval * 2) {
                this.checkSession();
            }
        }, this.options.checkInterval);
    }

    /**
     * Arrêter la vérification périodique
     */
    stopPeriodicCheck() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    /**
     * Configurer les événements de fenêtre
     */
    setupWindowEvents() {
        // Pause quand la fenêtre n'est pas active
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.log('Window hidden, pausing checks');
            } else {
                this.log('Window visible, resuming checks');
                this.checkSession();
            }
        });

        // Vérifier lors du retour de focus
        window.addEventListener('focus', () => {
            this.checkSession();
        });
    }

    /**
     * Vérifier l'état de la session
     */
    async checkSession() {
        if (this.isChecking) {
            return;
        }

        this.isChecking = true;
        this.lastCheck = Date.now();

        try {
            // Vérifier la fermeture automatique
            const closureResponse = await this.fetchSessionClosure();
            
            if (closureResponse.should_logout) {
                this.handleForcedLogout(closureResponse);
                return;
            }

            // Obtenir les informations de session détaillées
            const sessionInfo = await this.fetchSessionInfo();
            
            if (!sessionInfo.authenticated) {
                this.handleSessionExpired();
                return;
            }

            // Vérifier les actions requises
            this.handleRequiredActions(sessionInfo.required_actions || []);
            
            // Vérifier les avertissements de fermeture
            this.checkClosureWarning(sessionInfo.session_settings);
            
            this.log('Session check completed', sessionInfo);

        } catch (error) {
            this.log('Session check failed', error);
            
            // En cas d'erreur 401/403, considérer comme déconnecté
            if (error.status === 401 || error.status === 403) {
                this.handleSessionExpired();
            }
        } finally {
            this.isChecking = false;
        }
    }

    /**
     * Faire une requête pour vérifier la fermeture automatique
     */
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

    /**
     * Obtenir les informations détaillées de session
     */
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

    /**
     * Gérer une déconnexion forcée
     */
    handleForcedLogout(response) {
        this.stopPeriodicCheck();
        
        this.showNotification('warning', response.message || 'Votre session a été fermée automatiquement.');
        
        setTimeout(() => {
            window.location.href = response.redirect_url || '/login';
        }, 3000);
    }

    /**
     * Gérer une session expirée
     */
    handleSessionExpired() {
        this.stopPeriodicCheck();
        
        this.showNotification('error', 'Votre session a expiré. Redirection vers la page de connexion...');
        
        setTimeout(() => {
            window.location.href = '/login';
        }, 3000);
    }

    /**
     * Gérer les actions requises
     */
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

    /**
     * Vérifier et afficher l'avertissement de fermeture
     */
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

        // Afficher un avertissement 5 minutes avant la fermeture
        if (timeUntilClosure > 0 && timeUntilClosure <= this.options.warningTime && !this.warningShown) {
            const minutesLeft = Math.floor(timeUntilClosure / 60000);
            this.showClosureWarning(`Votre session se fermera automatiquement dans ${minutesLeft} minute(s) (${closureTime}).`);
            this.warningShown = true;
        }
    }

    /**
     * Afficher un avertissement de fermeture
     */
    showClosureWarning(message) {
        this.showNotification('warning', message, { 
            persistent: true,
            actionButton: {
                text: 'Prolonger',
                action: () => this.requestSessionExtension()
            }
        });
    }

    /**
     * Demander une extension de session (pour les admins)
     */
    async requestSessionExtension() {
        try {
            // Cette fonctionnalité pourrait être ajoutée pour les admins
            this.showNotification('info', 'Demande d\'extension envoyée...');
        } catch (error) {
            this.showNotification('error', 'Impossible de prolonger la session.');
        }
    }

    /**
     * Afficher une notification
     */
    showNotification(type, message, options = {}) {
        // Utiliser votre système de notifications existant
        // Ici, j'utilise une implémentation simple
        
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

        // Auto-dismiss sauf si persistant
        if (!options.persistent) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    }

    /**
     * Obtenir l'icône selon le type de notification
     */
    getIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'alert-circle',
            'warning': 'alert',
            'info': 'information'
        };
        return icons[type] || 'information';
    }

    /**
     * Log de débogage
     */
    log(message, data = null) {
        if (this.options.enableDebug) {
            console.log(`[AttendisSessionChecker] ${message}`, data);
        }
    }

    /**
     * Destructor
     */
    destroy() {
        this.stopPeriodicCheck();
        this.log('SessionChecker destroyed');
    }
}

// Auto-initialisation si jQuery est disponible
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser seulement si l'utilisateur est connecté
    if (document.querySelector('meta[name="csrf-token"]')) {
        window.attendisSessionChecker = new AttendisSessionChecker({
            enableDebug: false, // Passer à true pour le debug
            checkInterval: 30000, // Vérifier toutes les 30 secondes
            warningTime: 300000   // Avertir 5 minutes avant
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