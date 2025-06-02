@extends('dashboard.master')

@section('contenu')
<div class="page-wrapper">
    <!-- Top Bar Start -->
    <div class="topbar">            
        <!-- Navbar -->
        <nav class="navbar-custom">    
            <ul class="list-unstyled topbar-nav float-right mb-0">  
                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-info badge-pill noti-icon-badge">1</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            Notifications <span class="badge badge-primary badge-pill">1</span>
                        </h6> 
                        <div class="notification-menu" data-simplebar>
                            <a href="#" class="dropdown-item py-3">
                                <small class="float-right text-muted pl-2">Maintenant</small>
                                <div class="media">
                                    <div class="avatar-md bg-soft-primary">
                                        <i data-feather="user-check" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Compte activé</h6>
                                        <small class="text-muted mb-0">Votre compte utilisateur est maintenant actif.</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <a href="#" class="dropdown-item text-center text-primary">
                            Voir tout <i class="fi-arrow-right"></i>
                        </a>
                    </div>
                </li>

                <li class="dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <span class="ml-1 nav-user-name hidden-sm">{{ Auth::user()->username }}</span>
                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="profile-user" class="rounded-circle" />                                 
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('user.profile') }}"><i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Mon Profil</a>
                        <a class="dropdown-item" href="#"><i data-feather="settings" class="align-self-center icon-xs icon-dual mr-1"></i> Paramètres</a>
                        <div class="dropdown-divider mb-0"></div>
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Déconnexion
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
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
                        <a class="btn btn-sm btn-soft-primary" href="#" role="button" onclick="refreshUserInfo()">
                            <i class="fas fa-sync mr-2"></i>Actualiser
                        </a>
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
                                <h4 class="page-title animate__animated animate__fadeInDown">
                                    <i data-feather="home" class="mr-2"></i>Tableau de Bord Utilisateur
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item active">Dashboard Utilisateur</li>
                                </ol>
                            </div><!--end col-->
                            <div class="col-auto align-self-center">
                                <span class="badge badge-soft-success p-2">
                                    <i data-feather="check-circle" class="mr-1"></i>Compte Actif
                                </span>
                            </div><!--end col-->  
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->

            <!-- Message de bienvenue -->
            @if(session('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                        <i data-feather="smile" class="mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Informations du compte -->
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card report-card animate__animated animate__fadeInUp">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col text-center">
                                    <div class="mb-4">
                                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" 
                                             alt="profile" 
                                             class="rounded-circle" 
                                             style="width: 120px; height: 120px; border: 4px solid #007bff;">
                                    </div>
                                    <h3 class="font-weight-bold text-primary">{{ Auth::user()->username }}</h3>
                                    <p class="text-muted mb-3">{{ Auth::user()->email }}</p>
                                    
                                    <div class="row text-center mt-4">
                                        <div class="col-4">
                                            <h4 class="font-weight-bold text-info" id="accountAge">{{ $userStats['account_age_days'] ?? 0 }}</h4>
                                            <p class="text-muted mb-0">Jours</p>
                                            <small class="text-muted">Depuis l'activation</small>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="font-weight-bold text-success">Actif</h4>
                                            <p class="text-muted mb-0">Statut</p>
                                            <small class="text-muted">Compte opérationnel</small>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="font-weight-bold text-warning">User</h4>
                                            <p class="text-muted mb-0">Type</p>
                                            <small class="text-muted">Utilisateur normal</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
            </div><!--end row-->

            <!-- Informations détaillées -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card animate__animated animate__fadeInLeft">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i data-feather="user" class="mr-2"></i>Informations Personnelles
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong><i data-feather="user" class="icon-xs mr-2"></i>Nom d'utilisateur :</strong></td>
                                        <td>{{ Auth::user()->username }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="mail" class="icon-xs mr-2"></i>Email :</strong></td>
                                        <td>{{ Auth::user()->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="phone" class="icon-xs mr-2"></i>Téléphone :</strong></td>
                                        <td>{{ Auth::user()->mobile_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="calendar" class="icon-xs mr-2"></i>Inscription :</strong></td>
                                        <td>{{ Auth::user()->created_at->format('d/m/Y à H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="shield" class="icon-xs mr-2"></i>Type :</strong></td>
                                        <td><span class="badge badge-secondary">{{ Auth::user()->getTypeName() }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="activity" class="icon-xs mr-2"></i>Statut :</strong></td>
                                        <td><span class="badge badge-success">{{ Auth::user()->getStatusName() }}</span></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="{{ route('user.profile') }}" class="btn btn-primary waves-effect waves-light">
                                    <i data-feather="edit-2" class="icon-xs mr-1"></i>Modifier mon Profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card animate__animated animate__fadeInRight">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i data-feather="info" class="mr-2"></i>Informations sur le Compte
                            </h4>
                        </div>
                        <div class="card-body">
                            @if(isset($userStats['created_by_admin']) && $userStats['created_by_admin'])
                            <div class="alert alert-info">
                                <i data-feather="user-plus" class="mr-2"></i>
                                <strong>Compte créé par :</strong><br>
                                <strong>{{ $userStats['created_by_admin']->username }}</strong><br>
                                <small class="text-muted">{{ $userStats['created_by_admin']->email }}</small>
                            </div>
                            @endif

                            <div class="mb-3">
                                <h6><i data-feather="clock" class="icon-xs mr-2"></i>Historique du Compte</h6>
                                <ul class="list-unstyled ml-3">
                                    <li class="mb-2">
                                        <span class="text-success"><i data-feather="user-plus" class="icon-xs mr-1"></i></span>
                                        Compte créé le {{ Auth::user()->created_at->format('d/m/Y à H:i') }}
                                    </li>
                                    <li class="mb-2">
                                        <span class="text-primary"><i data-feather="check-circle" class="icon-xs mr-1"></i></span>
                                        Compte activé automatiquement
                                    </li>
                                    <li class="mb-2">
                                        <span class="text-info"><i data-feather="log-in" class="icon-xs mr-1"></i></span>
                                        Dernière connexion : {{ $userStats['last_login'] ?? 'Maintenant' }}
                                    </li>
                                </ul>
                            </div>

                            <div class="alert alert-success">
                                <i data-feather="shield-check" class="mr-2"></i>
                                <strong>Accès sécurisé :</strong> Votre compte bénéficie de toutes les mesures de sécurité d'Attendis.
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end row-->

            <!-- Actions rapides -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card animate__animated animate__fadeInUp">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i data-feather="zap" class="mr-2"></i>Actions Rapides
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="{{ route('user.profile') }}" class="btn btn-outline-primary btn-block waves-effect">
                                        <i data-feather="user" class="mr-2"></i>Mon Profil
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-info btn-block waves-effect" onclick="refreshUserInfo()">
                                        <i data-feather="refresh-cw" class="mr-2"></i>Actualiser
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-success btn-block waves-effect" onclick="showUserInfo()">
                                        <i data-feather="info" class="mr-2"></i>Mes Infos
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-danger btn-block waves-effect" onclick="confirmLogout()">
                                        <i data-feather="log-out" class="mr-2"></i>Déconnexion
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- container -->

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Dashboard Utilisateur</span>
        </footer><!--end footer-->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- Modal infos utilisateur -->
<div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="info" class="icon-xs mr-2"></i>Mes Informations Complètes
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="userInfoContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">
                    <i data-feather="x" class="icon-xs mr-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

.report-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.85em;
}

.table td {
    padding: 0.75rem 0.5rem;
    border-top: 1px solid #eee;
}

.alert {
    border-left: 4px solid;
}

.alert-success {
    border-left-color: #28a745;
}

.alert-info {
    border-left-color: #17a2b8;
}
</style>

<script>
// Actualiser les informations utilisateur
function refreshUserInfo() {
    showToast('Info', 'Actualisation des informations...', 'info');
    
    // Simuler une actualisation
    setTimeout(() => {
        const ageElement = document.getElementById('accountAge');
        const currentAge = parseInt(ageElement.textContent);
        ageElement.textContent = currentAge;
        
        showToast('Succès', 'Informations mises à jour !', 'success');
    }, 1000);
}

// Afficher les informations détaillées
function showUserInfo() {
    const modal = document.getElementById('userInfoModal');
    const content = document.getElementById('userInfoContent');
    
    $(modal).modal('show');
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Chargement...</span>
            </div>
        </div>
    `;
    
    setTimeout(() => {
        content.innerHTML = `
            <div class="row">
                <div class="col-md-4 text-center mb-3">
                    <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" class="rounded-circle" style="width: 120px; height: 120px;">
                    <h5 class="mt-3">{{ Auth::user()->username }}</h5>
                    <span class="badge badge-success">Utilisateur Actif</span>
                </div>
                <div class="col-md-8">
                    <h6><i data-feather="user" class="icon-xs mr-2"></i>Informations Personnelles</h6>
                    <table class="table table-borderless table-sm mb-3">
                        <tr><td><strong>ID :</strong></td><td>#{{ Auth::user()->id }}</td></tr>
                        <tr><td><strong>Email :</strong></td><td>{{ Auth::user()->email }}</td></tr>
                        <tr><td><strong>Téléphone :</strong></td><td>{{ Auth::user()->mobile_number }}</td></tr>
                        <tr><td><strong>Type :</strong></td><td>{{ Auth::user()->getTypeName() }}</td></tr>
                        <tr><td><strong>Statut :</strong></td><td>{{ Auth::user()->getStatusName() }}</td></tr>
                    </table>
                    
                    <h6><i data-feather="clock" class="icon-xs mr-2"></i>Historique</h6>
                    <table class="table table-borderless table-sm">
                        <tr><td><strong>Inscription :</strong></td><td>{{ Auth::user()->created_at->format('d/m/Y à H:i') }}</td></tr>
                        <tr><td><strong>Âge du compte :</strong></td><td>{{ $userStats['account_age_days'] ?? 0 }} jours</td></tr>
                        <tr><td><strong>Dernière activité :</strong></td><td>Maintenant</td></tr>
                    </table>
                </div>
            </div>
        `;
        
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 800);
}

// Confirmer la déconnexion
function confirmLogout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        document.getElementById('logout-form').submit();
    }
}

// Afficher une notification toast
function showToast(title, message, type = 'info') {
    const toastId = 'toast_' + Date.now();
    const colorClass = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    }[type] || 'bg-info';
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast ${colorClass} text-white`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="toast-header">
            <i data-feather="bell" class="mr-2"></i>
            <strong class="mr-auto">${title}</strong>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">
                <span>&times;</span>
            </button>
        </div>
        <div class="toast-body">${message}</div>
    `;
    
    document.body.appendChild(toast);
    
    $(toast).toast({ delay: 4000 }).toast('show');
    
    $(toast).on('hidden.bs.toast', function() {
        this.remove();
    });
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    setTimeout(() => {
        showToast('Bienvenue', 'Votre dashboard utilisateur est prêt !', 'success');
    }, 1000);
});
</script>

@endsection