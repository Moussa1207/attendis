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
                                <input type="search" name="search" class="from-control top-search mb-0" placeholder="Rechercher dans mon espace...">
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
                                        <small class="text-muted mb-0">Votre poste {{ Auth::user()->getTypeName() }} est configuré</small>
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
                                        <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">{{ Auth::user()->getTypeName() }}</h6>
                                        <small class="text-muted mb-0">{{ $typeSpecificData['type_description'] ?? 'Votre poste est opérationnel' }}</small>
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
                        <div class="dropdown-header">
                            <h6 class="text-dark mb-0">{{ Auth::user()->getTypeName() }}</h6>
                            <small class="text-muted">{{ Auth::user()->email }}</small>
                        </div>
                        <div class="dropdown-divider"></div>
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
                                <h4 class="page-title">
                                    {{ Auth::user()->getTypeEmoji() }} Mon Espace {{ Auth::user()->getTypeName() }}
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app-users') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">{{ Auth::user()->getTypeName() }}</li>
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

            <!-- 🆕 Bannière spécifique au type d'utilisateur -->
            @if(isset($typeSpecificData) && !empty($typeSpecificData['type_description']))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="icon-md text-info"></i>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h6 class="alert-heading mb-1">{{ Auth::user()->getTypeName() }}</h6>
                        <p class="mb-0">{{ $typeSpecificData['type_description'] }}</p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            <!-- Statistiques personnelles adaptées -->
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Jours d'activité</p>
                                    <h3 class="my-2">{{ $userStats['days_since_creation'] ?? Auth::user()->created_at->diffInDays(now()) }}</h3>
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
                                    <p class="text-dark mb-1 font-weight-semibold">Statut poste</p>
                                    <h3 class="my-2 text-success">{{ Auth::user()->getStatusName() }}</h3>
                                    <p class="mb-0 text-truncate text-muted"><span class="text-success"><i class="mdi mdi-trending-up"></i>1.5%</span> Poste opérationnel</p>
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
                                    <p class="text-dark mb-1 font-weight-semibold">Type de poste</p>
                                    <h3 class="my-2">{{ Auth::user()->getTypeEmoji() }}</h3>
                                    <p class="mb-0 text-truncate text-muted"><span class="text-info"><i class="mdi mdi-trending-down"></i>35%</span> {{ Auth::user()->getTypeShortDescription() }}</p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="align-self-center text-muted icon-md"></i>  
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
                                    <h4 class="card-title">Aperçu de mon activité - {{ Auth::user()->getTypeName() }}</h4>                      
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
                                <h4>{{ $userStats['days_since_creation'] ?? Auth::user()->created_at->diffInDays(now()) }} jours d'activité</h4>
                                <p class="text-muted mt-2">Votre poste est actif depuis {{ Auth::user()->created_at->format('d/m/Y') }}</p>
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
                                    <h4 class="card-title">Mes informations - {{ Auth::user()->getTypeName() }}</h4>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Profil<i class="las la-angle-down ml-1"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#" onclick="showPasswordModal()">Changer mot de passe</a>
                                            <a class="dropdown-item" href="#" onclick="showTypeGuide()">Guide du {{ Auth::user()->getTypeName() }}</a>
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
                                                <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="icon-xs mr-2"></i>Type de poste:
                                            </td>
                                            <td><span class="badge badge-{{ Auth::user()->getTypeBadgeColor() }}">{{ Auth::user()->getTypeName() }}</span></td>
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
                                        @if(Auth::user()->agency)
                                        <tr>
                                            <td class="font-weight-semibold">
                                                <i data-feather="home" class="icon-xs mr-2"></i>Agence:
                                            </td>
                                            <td>{{ Auth::user()->agency->name }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <button class="btn btn-primary btn-sm waves-effect" onclick="showPasswordModal()">
                                    <i data-feather="key" class="icon-xs mr-1"></i>Changer mon mot de passe
                                </button>
                                @if(isset($typeSpecificData) && !empty($typeSpecificData['type_features']))
                                <button class="btn btn-info btn-sm waves-effect ml-2" onclick="showTypeGuide()">
                                    <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="icon-xs mr-1"></i>Guide {{ Auth::user()->getTypeName() }}
                                </button>
                                @endif
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
                                            <a class="dropdown-item" href="#">{{ Auth::user()->getTypeName() }}</a>
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
                                                    <span class="text-dark font-weight-semibold">Connexion {{ Auth::user()->getTypeName() }}</span><br>
                                                    Vous vous êtes connecté à votre poste
                                                </p>
                                                <small class="text-muted">Maintenant</small>
                                            </div>    
                                        </div>
                                    </div>   

                                    @if($userStats['is_recently_created'] ?? Auth::user()->created_at->diffInHours(now()) < 24)
                                    <div class="activity-info">
                                        <div class="icon-info-activity">
                                            <i class="mdi mdi-account-plus bg-soft-primary"></i>
                                        </div>
                                        <div class="activity-info-text">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-muted mb-0 font-13 w-75">
                                                    <span class="text-dark font-weight-semibold">Poste {{ Auth::user()->getTypeName() }} créé</span><br>
                                                    Votre poste a été configuré par {{ Auth::user()->getCreator()->username ?? 'un administrateur' }}
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
                                                    <span class="text-dark font-weight-semibold">Poste activé</span><br>
                                                    Votre {{ Auth::user()->getTypeName() }} est maintenant opérationnel
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

                                    @if(isset($typeSpecificData) && !empty($typeSpecificData['type_recommendations']))
                                    <div class="activity-info">
                                        <div class="icon-info-activity">
                                            <i class="mdi mdi-lightbulb-outline bg-soft-info"></i>
                                        </div>
                                        <div class="activity-info-text">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-muted mb-0 font-13 w-75">
                                                    <span class="text-dark font-weight-semibold">Conseil {{ Auth::user()->getTypeName() }}</span><br>
                                                    {{ $typeSpecificData['type_recommendations'][0] ?? 'Consultez le guide de votre poste' }}
                                                </p>
                                                <small class="text-muted">Recommandation</small>
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

            <!-- 🆕 Section spécifique au type d'utilisateur -->
            @if(isset($typeSpecificData) && (!empty($typeSpecificData['type_features']) || !empty($typeSpecificData['type_recommendations'])))
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title">
                                        <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="mr-2"></i>
                                        Guide {{ Auth::user()->getTypeName() }}
                                    </h4>
                                </div>
                                <div class="col-auto">
                                    <span class="badge badge-{{ Auth::user()->getTypeBadgeColor() }} badge-pill">
                                        {{ Auth::user()->getTypeEmoji() }} {{ Auth::user()->getTypeName() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(!empty($typeSpecificData['type_features']))
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i data-feather="check-square" class="mr-2"></i>Fonctionnalités de votre poste
                                    </h6>
                                    <ul class="list-unstyled">
                                        @foreach($typeSpecificData['type_features'] as $feature)
                                        <li class="mb-2">
                                            <i data-feather="check" class="text-success mr-2 icon-xs"></i>
                                            {{ $feature }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                @if(!empty($typeSpecificData['type_recommendations']))
                                <div class="col-md-6">
                                    <h6 class="text-info mb-3">
                                        <i data-feather="lightbulb" class="mr-2"></i>Recommandations
                                    </h6>
                                    <ul class="list-unstyled">
                                        @foreach($typeSpecificData['type_recommendations'] as $recommendation)
                                        <li class="mb-2">
                                            <i data-feather="arrow-right" class="text-info mr-2 icon-xs"></i>
                                            {{ $recommendation }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>

                            <div class="text-center mt-4">
                                <button class="btn btn-outline-primary btn-sm" onclick="showTypeGuide()">
                                    <i data-feather="book-open" class="mr-1"></i>
                                    Guide complet {{ Auth::user()->getTypeName() }}
                                </button>
                                <button class="btn btn-outline-success btn-sm ml-2" onclick="showPasswordModal()">
                                    <i data-feather="key" class="mr-1"></i>
                                    Sécuriser mon poste
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div><!-- container -->

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">{{ Auth::user()->username }} - {{ Auth::user()->getTypeName() }}</span>
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
            <form id="passwordForm" method="POST" action="{{ route('password.change') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mot de passe actuel</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="current_password" id="current_password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('current_password')">
                                    <i data-feather="eye" id="toggleIcon-current_password"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password" id="new_password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('new_password')">
                                    <i data-feather="eye" id="toggleIcon-new_password"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le nouveau mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password_confirmation" id="new_password_confirmation" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('new_password_confirmation')">
                                    <i data-feather="eye" id="toggleIcon-new_password_confirmation"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="mr-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 🆕 Modal Guide du poste -->
<div class="modal fade" id="typeGuideModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="mr-2"></i>
                    Guide {{ Auth::user()->getTypeName() }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar-lg mx-auto mb-3" style="background: linear-gradient(135deg, #667eea, #764ba2); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i data-feather="{{ Auth::user()->getTypeIcon() }}" class="text-white" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h4>{{ Auth::user()->getTypeName() }}</h4>
                    <p class="text-muted">{{ $typeSpecificData['type_description'] ?? Auth::user()->getTypeShortDescription() }}</p>
                </div>

                @if(isset($typeSpecificData) && !empty($typeSpecificData['type_features']))
                <div class="mb-4">
                    <h6 class="text-primary">
                        <i data-feather="check-square" class="mr-2"></i>Vos responsabilités
                    </h6>
                    <div class="row">
                        @foreach($typeSpecificData['type_features'] as $index => $feature)
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-soft-primary mr-2">{{ $index + 1 }}</span>
                                {{ $feature }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($typeSpecificData) && !empty($typeSpecificData['type_recommendations']))
                <div class="mb-4">
                    <h6 class="text-success">
                        <i data-feather="lightbulb" class="mr-2"></i>Bonnes pratiques
                    </h6>
                    @foreach($typeSpecificData['type_recommendations'] as $index => $recommendation)
                    <div class="alert alert-soft-success d-flex align-items-center" role="alert">
                        <i data-feather="check-circle" class="text-success mr-2"></i>
                        {{ $recommendation }}
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="alert alert-info" role="alert">
                    <div class="d-flex align-items-center">
                        <i data-feather="info" class="text-info mr-2"></i>
                        <div>
                            <strong>Besoin d'aide ?</strong><br>
                            Contactez votre administrateur {{ Auth::user()->getCreator()->username ?? 'système' }} pour toute question.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <i data-feather="check" class="mr-1"></i> Compris
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast notifications -->
<div class="position-fixed top-0 right-0 p-3" style="z-index: 1050; right: 0; top: 0;">
    <div id="toastContainer"></div>
</div>

<!-- CSS personnalisé -->
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

.bg-soft-info {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.alert-soft-success {
    background-color: rgba(40, 167, 69, 0.1);
    border-color: rgba(40, 167, 69, 0.2);
    color: #155724;
}

.badge-soft-primary {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

/* Animation pour les cartes */
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

/* Styles pour les badges de type */
.badge-info { background-color: #17a2b8; }
.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-primary { background-color: #007bff; }
</style>

<!-- JavaScript -->
<script>
// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    console.log('Interface {{ Auth::user()->getTypeName() }} initialisée');
});

// Afficher le modal de changement de mot de passe
function showPasswordModal() {
    $('#passwordModal').modal('show');
}

// Afficher le guide du type d'utilisateur
function showTypeGuide() {
    $('#typeGuideModal').modal('show');
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
    showToast('Info', 'Export de vos données {{ Auth::user()->getTypeName() }} en cours...', 'info');
    
    setTimeout(() => {
        showToast('Succès', 'Vos données ont été exportées !', 'success');
    }, 2000);
}

// Gestion du formulaire de mot de passe
$('#passwordForm').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const newPassword = $('#new_password').val();
    const confirmPassword = $('#new_password_confirmation').val();
    
    if (newPassword !== confirmPassword) {
        showToast('Erreur', 'Les mots de passe ne correspondent pas', 'error');
        return false;
    }
    
    if (newPassword.length < 6) {
        showToast('Erreur', 'Le mot de passe doit contenir au moins 6 caractères', 'error');
        return false;
    }

    const formData = form.serialize();
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#passwordModal').modal('hide');
                showToast('Succès', 'Mot de passe modifié avec succès', 'success');
                form[0].reset();
            } else {
                showToast('Erreur', response.message || 'Erreur lors du changement de mot de passe', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response?.message || 'Erreur lors du changement de mot de passe';
            showToast('Erreur', message, 'error');
        }
    });
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

// Configuration CSRF pour requêtes AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>

@endsection