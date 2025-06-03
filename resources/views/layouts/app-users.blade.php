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
                                <input type="search" name="search" class="from-control top-search mb-0" placeholder="Rechercher dans mes données...">
                                <button type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                    </div>
                </li>                      

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-info badge-pill noti-icon-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            Mes Notifications <span class="badge badge-primary badge-pill">3</span>
                        </h6> 
                        <div class="notification-menu" data-simplebar>
                            <a href="#" class="dropdown-item py-3">
                                <small class="float-right text-muted pl-2">2 min ago</small>
                                <div class="media">
                                    <div class="avatar-md bg-soft-primary">
                                        <i data-feather="user-check" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Bienvenue !</h6>
                                        <small class="text-muted mb-0">Votre compte est maintenant actif</small>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="#" class="dropdown-item py-3">
                                <small class="float-right text-muted pl-2">1 hr ago</small>
                                <div class="media">
                                    <div class="avatar-md bg-soft-success">
                                        <i data-feather="key" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Sécurité</h6>
                                        <small class="text-muted mb-0">Pensez à modifier votre mot de passe</small>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="#" class="dropdown-item py-3">
                                <small class="float-right text-muted pl-2">2 hrs ago</small>
                                <div class="media">
                                    <div class="avatar-md bg-soft-info">
                                        <i data-feather="info" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Information</h6>
                                        <small class="text-muted mb-0">Découvrez votre espace personnel</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <a href="javascript:void(0);" class="dropdown-item text-center text-primary">
                            Voir toutes <i class="fi-arrow-right"></i>
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
                        <a class="dropdown-item" href="{{ route('layouts.app-users') }}"><i data-feather="home" class="align-self-center icon-xs icon-dual mr-1"></i> Dashboard</a>
                        <a class="dropdown-item" href="{{ route('layouts.app-users') }}"><i data-feather="activity" class="align-self-center icon-xs icon-dual mr-1"></i> Mon espace</a>
                        <div class="dropdown-divider mb-0"></div>
                        <h6 class="dropdown-header">Mon Profil</h6>
                        <a class="dropdown-item" href="#" onclick="showPasswordModal()"><i data-feather="key" class="align-self-center icon-xs icon-dual mr-1"></i> Changer mot de passe</a>
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
                        <a class="btn btn-sm btn-soft-primary" href="#" onclick="showPasswordModal()" role="button">
                            <i data-feather="key" class="mr-2"></i>Changer mot de passe
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
                                <h4 class="page-title">Mon Espace</h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app-users') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Mon espace</li>
                                </ol>
                            </div><!--end col-->
                            <div class="col-auto align-self-center">
                                <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                    <span class="ay-name" id="Day_Name">Aujourd'hui:</span>&nbsp;
                                    <span class="" id="Select_date">{{ now()->format('d M') }}</span>
                                    <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="exportMyData()">
                                    <i data-feather="download" class="align-self-center icon-xs"></i>
                                </a>
                            </div><!--end col-->  
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->

            <!-- Messages d'accueil -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <i data-feather="check-circle" class="mr-2"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            <!-- Statistiques personnelles -->
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Jours d'activité</p>
                                    <h3 class="my-2">{{ Auth::user()->created_at->diffInDays(now()) }}</h3>
                                    <p class="mb-0 text-truncate text-muted"><span class="text-success"><i class="mdi mdi-trending-up"></i>8.5%</span> Depuis l'inscription</p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="calendar" class="align-self-center text-muted icon-md"></i>  
                                    </div>
                                </div>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Statut compte</p>
                                    <h3 class="my-2 text-success">{{ Auth::user()->getStatusName() }}</h3>
                                    <p class="mb-0 text-truncate text-muted"><span class="text-success"><i class="mdi mdi-trending-up"></i>1.5%</span> Compte opérationnel</p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="check-circle" class="align-self-center text-muted icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Type utilisateur</p>
                                    <h3 class="my-2">{{ Auth::user()->getTypeName() }}</h3>
                                    <p class="mb-0 text-truncate text-muted"><span class="text-info"><i class="mdi mdi-trending-down"></i>35%</span> Accès standard</p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="user" class="align-self-center text-muted icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">  
                                    <p class="text-dark mb-1 font-weight-semibold">Connexions</p>                                         
                                    <h3 class="my-2">1</h3>
                                    <p class="mb-0 text-truncate text-muted"><span class="text-success"><i class="mdi mdi-trending-up"></i>10.5%</span> Aujourd'hui</p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="activity" class="align-self-center text-muted icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                               
            </div><!--end row-->

            <div class="row">
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">Aperçu de mon activité</h4>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                           Cette année<i class="las la-angle-down ml-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#">Aujourd'hui</a>
                                            <a class="dropdown-item" href="#">Cette semaine</a>
                                            <a class="dropdown-item" href="#">Ce mois</a>
                                            <a class="dropdown-item" href="#">Cette année</a>
                                        </div>
                                    </div>               
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->
                        <div class="card-body">
                            <div class="">
                                <div id="ana_dash_1" class="apex-charts"></div>
                            </div> 
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->  
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">Mes données</h4>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                           Direct<i class="las la-angle-down ml-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#">Profil</a>
                                            <a class="dropdown-item" href="#">Activité</a>
                                            <a class="dropdown-item" href="#">Paramètres</a>
                                        </div>
                                    </div>               
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->
                        <div class="card-body">
                            <div class="my-5">
                                <div id="ana_1" class="apex-charts d-block w-90 mx-auto"></div>
                                <hr class="hr-dashed w-25 mt-0">                                                                            
                            </div>    
                            <div class="text-center">
                                <h4>{{ Auth::user()->created_at->diffInDays(now()) }} jours d'activité</h4>
                                <p class="text-muted mt-2">Votre compte est actif depuis {{ Auth::user()->created_at->format('d/m/Y') }}</p>
                                <button type="button" class="btn btn-sm btn-outline-primary px-3 mt-2" onclick="showPasswordModal()">Changer mot de passe</button>
                           </div>                                    
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                                        
            </div><!--end row-->

            <div class="row">  
                <div class="col-lg-6">
                    <div class="card">                                
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">Mes informations personnelles</h4>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Profil<i class="las la-angle-down ml-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#" onclick="showPasswordModal()">Changer mot de passe</a>
                                            <a class="dropdown-item" href="#">Mes paramètres</a>
                                        </div>
                                    </div>          
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->                                 
                        <div class="card-body">  
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="font-weight-semibold" style="width: 200px;">
                                                <i data-feather="user" class="icon-xs mr-2"></i>Nom d'utilisateur:
                                            </td>
                                            <td>{{ Auth::user()->username }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-semibold">
                                                <i data-feather="mail" class="icon-xs mr-2"></i>Email:
                                            </td>
                                            <td>{{ Auth::user()->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-semibold">
                                                <i data-feather="phone" class="icon-xs mr-2"></i>Téléphone:
                                            </td>
                                            <td>{{ Auth::user()->mobile_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-semibold">
                                                <i data-feather="shield" class="icon-xs mr-2"></i>Type de compte:
                                            </td>
                                            <td><span class="badge badge-info">{{ Auth::user()->getTypeName() }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-semibold">
                                                <i data-feather="calendar" class="icon-xs mr-2"></i>Date d'inscription:
                                            </td>
                                            <td>{{ Auth::user()->created_at->format('d/m/Y à H:i') }}</td>
                                        </tr>
                                        @if(Auth::user()->wasCreatedByAdmin())
                                        <tr>
                                            <td class="font-weight-semibold">
                                                <i data-feather="user-plus" class="icon-xs mr-2"></i>Créé par:
                                            </td>
                                            <td>{{ Auth::user()->getCreator()->username ?? 'Administrateur' }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <button class="btn btn-primary btn-sm waves-effect" onclick="showPasswordModal()">
                                    <i data-feather="key" class="icon-xs mr-1"></i>Changer mon mot de passe
                                </button>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card-body-->
                </div> <!--end col-->
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">Mon activité récente</h4>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Tout<i class="las la-angle-down ml-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#">Connexions</a>
                                            <a class="dropdown-item" href="#">Modifications</a>
                                        </div>
                                    </div>          
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->                                              
                        <div class="card-body"> 
                            <div class="analytic-dash-activity" data-simplebar>
                                <div class="activity">
                                    <div class="activity-info">
                                        <div class="icon-info-activity">
                                            <i class="mdi mdi-login bg-soft-success"></i>
                                        </div>
                                        <div class="activity-info-text">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-muted mb-0 font-13 w-75">
                                                    <span class="text-dark font-weight-semibold">Connexion réussie</span><br>
                                                    Vous vous êtes connecté à votre compte
                                                </p>
                                                <small class="text-muted">Maintenant</small>
                                            </div>    
                                        </div>
                                    </div>   

                                    @if(Auth::user()->created_at->diffInHours(now()) < 24)
                                    <div class="activity-info">
                                        <div class="icon-info-activity">
                                            <i class="mdi mdi-account-plus bg-soft-primary"></i>
                                        </div>
                                        <div class="activity-info-text">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-muted mb-0 font-13 w-75">
                                                    <span class="text-dark font-weight-semibold">Compte créé</span><br>
                                                    Votre compte a été créé par {{ Auth::user()->getCreator()->username ?? 'un administrateur' }}
                                                </p>
                                                <small class="text-muted">{{ Auth::user()->created_at->diffForHumans() }}</small>
                                            </div>    
                                        </div>
                                    </div>
                                    @endif

                                    <div class="activity-info">
                                        <div class="icon-info-activity">
                                            <i class="mdi mdi-check-circle bg-soft-success"></i>
                                        </div>
                                        <div class="activity-info-text">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-muted mb-0 font-13 w-75">
                                                    <span class="text-dark font-weight-semibold">Compte activé</span><br>
                                                    Votre compte est maintenant opérationnel
                                                </p>
                                                <small class="text-muted">{{ Auth::user()->created_at->diffForHumans() }}</small>
                                            </div>    
                                        </div>
                                    </div>

                                    @if(Auth::user()->created_at->diffInDays(now()) < 7)
                                    <div class="activity-info">
                                        <div class="icon-info-activity">
                                            <i class="mdi mdi-alert-outline bg-soft-warning"></i>
                                        </div>
                                        <div class="activity-info-text">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-muted mb-0 font-13 w-75">
                                                    <span class="text-dark font-weight-semibold">Recommandation sécurité</span><br>
                                                    Pensez à changer votre mot de passe
                                                </p>
                                                <small class="text-muted">{{ Auth::user()->created_at->addDay()->diffForHumans() }}</small>
                                            </div>    
                                        </div>
                                    </div>
                                    @endif
                                </div><!--end activity-->
                            </div><!--end analytics-dash-activity-->
                        </div>  <!--end card-body-->                                     
                    </div><!--end card--> 
                </div><!--end col--> 
            </div><!--end row-->

        </div><!-- container -->

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">{{ Auth::user()->username }} - Espace personnel</span>
        </footer><!--end footer-->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- Modal changement de mot de passe -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="key" class="icon-xs mr-2"></i>Changer mon mot de passe
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            
            
        </div>
    </div>
</div>

<!-- Toast notifications -->
<div class="position-fixed top-0 right-0 p-3" style="z-index: 1050; right: 0; top: 0;">
    <div id="toastContainer"></div>
</div>

<!-- CSS -->
<style>
.icon-xs {
    width: 16px;
    height: 16px;
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.dropdown-header {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.activity .activity-info {
    display: flex;
    margin-bottom: 1.5rem;
}

.activity .icon-info-activity {
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity .icon-info-activity i {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 16px;
}

.bg-soft-success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.bg-soft-primary {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.bg-soft-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}
</style>

<!-- JavaScript -->
<script>
// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// Afficher le modal de changement de mot de passe
function showPasswordModal() {
    $('#passwordModal').modal('show');
}

// Basculer l'affichage du mot de passe
function togglePasswordField(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById('toggleIcon-' + fieldId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-feather', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-feather', 'eye');
    }
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Exporter mes données
function exportMyData() {
    showToast('Info', 'Export de vos données en cours...', 'info');
    
    setTimeout(() => {
        showToast('Succès', 'Vos données ont été exportées !', 'success');
    }, 2000);
}

// Gestion du formulaire de mot de passe
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirmation').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        showToast('Erreur', 'Les mots de passe ne correspondent pas', 'error');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        showToast('Erreur', 'Le mot de passe doit contenir au moins 6 caractères', 'error');
        return false;
    }
});

// Fonction toast
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
    
    document.getElementById('toastContainer').appendChild(toast);
    
    $(toast).toast({ delay: 4000 }).toast('show');
    
    $(toast).on('hidden.bs.toast', function() {
        this.remove();
    });
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}
</script>

@endsection