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

            <!-- ✅ CORRIGÉ: Gestion des Agences avec les bonnes routes -->
            <li>
                <a href="javascript: void(0);">
                    <i data-feather="home" class="align-self-center menu-icon"></i>
                    <span>Agence</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <!-- ✅ CORRIGÉ: Route création agence -->
                    <li>
                        <a href="{{ route('agencies.create') }}">
                            <i class="ti-control-record"></i>Nouveau
                        </a>
                    </li>
                    <!-- ✅ CORRIGÉ: Route liste agences -->
                    <li>
                        <a href="{{ route('agencies.index') }}">
                            <i class="ti-control-record"></i>Liste
                        </a>                           
                    </li>
                </ul>                        
            </li>

            <!-- Statistiques Admin -->
            <li>
                <a href="{{ route('layouts.app') }}">
                    <i data-feather="bar-chart-2" class="align-self-center menu-icon"></i>
                    <span>Statistiques</span>
                </a>
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
</script>

</body>
</html>