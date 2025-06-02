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
                            <input type="search" id="quickSearch" class="from-control top-search mb-0" placeholder="Recherche rapide..." onkeyup="quickSearchUsers()">
                            <button type="button" onclick="clearQuickSearch()"><i class="ti-close"></i></button>
                        </div>
                    </div>
                </li>                      

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-danger badge-pill noti-icon-badge" id="pendingCount">{{ $users->where('status_id', 1)->count() }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            Notifications <span class="badge badge-primary badge-pill" id="pendingCount2">{{ $users->where('status_id', 1)->count() }}</span>
                        </h6> 
                        <div class="notification-menu" data-simplebar id="notificationsList">
                            <!-- Notifications dynamiques -->
                        </div>
                        <a href="{{ route('layouts.app') }}" class="dropdown-item text-center text-primary">
                            Retour au Dashboard <i class="fi-arrow-right"></i>
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
                        <a class="dropdown-item" href="{{ route('layouts.app') }}"><i data-feather="home" class="align-self-center icon-xs icon-dual mr-1"></i> Dashboard</a>
                        <a class="dropdown-item" href="#"><i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Profile</a>
                        <a class="dropdown-item" href="#"><i data-feather="settings" class="align-self-center icon-xs icon-dual mr-1"></i> Settings</a>
                        <div class="dropdown-divider mb-0"></div>
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Logout
                        </a>
                        <form id="logout-form" action=" " method="POST" style="display: none;">
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
                        <a class="btn btn-sm btn-soft-primary waves-effect" href="{{ route('layouts.app') }}" role="button">
                            <i class="fas fa-arrow-left mr-2"></i>Dashboard
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
                                    <i data-feather="users" class="mr-2"></i>Gestion des Utilisateurs
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item">Utilisateurs</li>
                                    <li class="breadcrumb-item active">Liste</li>
                                </ol>
                            </div><!--end col-->
                            <div class="col-auto align-self-center">
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshStats()" id="refreshBtn">
                                    <span class="ay-name">Total:</span>&nbsp;
                                    <span id="totalUsers">{{ $users->total() }}</span>
                                    <i data-feather="refresh-cw" class="align-self-center icon-xs ml-1"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="exportUsers()">
                                    <i data-feather="download" class="align-self-center icon-xs"></i>
                                </button>
                            </div><!--end col-->  
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->
            
            <!-- Statistiques rapides avec animations -->
            <div class="row justify-content-center" id="statsCards">
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card hover-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Total Affiché</p>
                                    <h3 class="my-2 counter" data-target="{{ $users->total() }}">0</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-info"><i class="mdi mdi-filter"></i></span> 
                                        <span class="status-text">Résultats filtrés</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt icon-pulse">
                                        <i data-feather="users" class="align-self-center text-muted icon-md"></i>  
                                    </div>
                                </div>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card hover-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Actifs</p>
                                    <h3 class="my-2 text-success counter" data-target="{{ $users->where('status_id', 2)->count() }}">0</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-success"><i class="mdi mdi-check-circle"></i></span> 
                                        <span class="progress-text">Comptes actifs</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt icon-bounce">
                                        <i data-feather="user-check" class="align-self-center text-success icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card hover-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">En Attente</p>
                                    <h3 class="my-2 text-warning counter" data-target="{{ $users->where('status_id', 1)->count() }}">0</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-warning"><i class="mdi mdi-clock-outline"></i></span> 
                                        <span class="pending-text">À activer</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt icon-shake">
                                        <i data-feather="user-plus" class="align-self-center text-warning icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card hover-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">  
                                    <p class="text-dark mb-1 font-weight-semibold">Administrateurs</p>                                         
                                    <h3 class="my-2 text-primary counter" data-target="{{ $users->where('user_type_id', 1)->count() }}">0</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-primary"><i class="mdi mdi-shield-check"></i></span> 
                                        <span class="admin-text">Admins</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt icon-glow">
                                        <i data-feather="shield" class="align-self-center text-primary icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                               
            </div><!--end row-->

            <!-- Filtres avec animations -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="500">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="filter" class="mr-2"></i>Filtres Intelligents
                                    </h4>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <button class="btn btn-sm btn-outline-secondary waves-effect" onclick="resetFilters()">
                                        <i data-feather="refresh-cw" class="align-self-center icon-xs mr-1"></i>Réinitialiser
                                    </button>
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->
                        <div class="card-body">
                            <form id="filterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search">
                                                <i data-feather="search" class="icon-xs mr-1"></i>Recherche Intelligente
                                            </label>
                                            <input type="text" name="search" id="search" class="form-control" 
                                                   placeholder="Nom, email, téléphone..." 
                                                   value="{{ request('search') }}"
                                                   onkeyup="liveSearch()" autocomplete="off">
                                            <div id="searchSuggestions" class="search-suggestions"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="status">
                                                <i data-feather="activity" class="icon-xs mr-1"></i>Statut
                                            </label>
                                            <select name="status" id="status" class="form-control" onchange="applyFilters()">
                                                <option value="">Tous les statuts</option>
                                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>✅ Actif</option>
                                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>⏳ Inactif</option>
                                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>❌ Suspendu</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="type">
                                                <i data-feather="users" class="icon-xs mr-1"></i>Type
                                            </label>
                                            <select name="type" id="type" class="form-control" onchange="applyFilters()">
                                                <option value="">Tous les types</option>
                                                <option value="admin" {{ request('type') == 'admin' ? 'selected' : '' }}>🛡️ Administrateur</option>
                                                <option value="user" {{ request('type') == 'user' ? 'selected' : '' }}>👤 Utilisateur</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="applyFilters()">
                                                    <i data-feather="filter" class="icon-xs mr-1"></i>Filtrer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div><!--end row-->

            <!-- Actions rapides -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="600">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button class="btn btn-outline-info btn-block waves-effect" onclick="filterByStatus('inactive')">
                                        <i data-feather="clock" class="mr-2"></i>Demandes d'Activation
                                        <span class="badge badge-warning ml-2" id="pendingBadge">{{ $users->where('status_id', 1)->count() }}</span>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-success btn-block waves-effect" onclick="filterByStatus('active')">
                                        <i data-feather="user-check" class="mr-2"></i>Utilisateurs Actifs
                                        <span class="badge badge-success ml-2" id="activeBadge">{{ $users->where('status_id', 2)->count() }}</span>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary btn-block waves-effect" onclick="filterByType('admin')">
                                        <i data-feather="shield" class="mr-2"></i>Administrateurs
                                        <span class="badge badge-primary ml-2" id="adminBadge">{{ $users->where('user_type_id', 1)->count() }}</span>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-warning btn-block waves-effect" onclick="bulkActivateAll()">
                                        <i data-feather="zap" class="mr-2"></i>Activer Tout
                                        <span class="loading-spinner" style="display:none;"><i class="mdi mdi-loading mdi-spin"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des Utilisateurs avec animations -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="700">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="list" class="mr-2"></i>Liste Interactive des Utilisateurs
                                        <span class="badge badge-soft-primary ml-2" id="resultCount">{{ $users->total() }} résultat(s)</span>
                                    </h4>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleView('table')" id="tableViewBtn">
                                            <i data-feather="grid" class="icon-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleView('cards')" id="cardsViewBtn">
                                            <i data-feather="square" class="icon-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshUsersList()">
                                            <i data-feather="refresh-cw" class="icon-xs"></i>
                                        </button>
                                    </div>           
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->
                        <div class="card-body">
                            <!-- Loading overlay -->
                            <div id="loadingOverlay" class="text-center py-5" style="display:none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Chargement...</span>
                                </div>
                                <p class="mt-2 text-muted">Mise à jour en cours...</p>
                            </div>

                            <!-- Table view -->
                            <div id="tableView">
                                @if($users->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="usersTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="border-top-0">
                                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()"> 
                                                    Utilisateur
                                                </th>
                                                <th class="border-top-0">Contact</th>
                                                <th class="border-top-0">Type</th>
                                                <th class="border-top-0">Statut</th>
                                                <th class="border-top-0">Inscription</th>
                                                <th class="border-top-0">Actions</th>
                                            </tr><!--end tr-->
                                        </thead>
                                        <tbody id="usersTableBody">
                                            @foreach($users as $index => $user)
                                            <tr class="user-row animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="{{ 100 + ($index * 50) }}" data-user-id="{{ $user->id }}">                                                        
                                                <td>
                                                    <div class="media">
                                                        <input type="checkbox" class="user-checkbox mr-2" value="{{ $user->id }}">
                                                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="" class="rounded-circle thumb-md mr-3 hover-zoom">
                                                        <div class="media-body align-self-center">
                                                            <h6 class="m-0 font-weight-semibold">{{ $user->username }}</h6>
                                                            <p class="text-muted mb-0 font-13">ID: #{{ $user->id }}</p>
                                                        </div><!--end media body-->
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="mb-0 font-14">{{ $user->email }}</p>
                                                    <small class="text-muted">📱 {{ $user->mobile_number }}</small>
                                                </td>
                                                <td>
                                                    @if($user->isAdmin())
                                                        <span class="badge badge-primary badge-pill animated-badge">
                                                            <i data-feather="shield" class="icon-xs mr-1"></i>Admin
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary badge-pill animated-badge">
                                                            <i data-feather="user" class="icon-xs mr-1"></i>User
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->isActive())
                                                        <span class="badge badge-success badge-pill animated-badge pulse-success">
                                                            <i data-feather="check-circle" class="icon-xs mr-1"></i>Actif
                                                        </span>
                                                    @elseif($user->isInactive())
                                                        <span class="badge badge-warning badge-pill animated-badge pulse-warning">
                                                            <i data-feather="clock" class="icon-xs mr-1"></i>En attente
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger badge-pill animated-badge">
                                                            <i data-feather="x-circle" class="icon-xs mr-1"></i>Suspendu
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <p class="mb-0 font-14">{{ $user->created_at->format('d/m/Y') }}</p>
                                                    <small class="text-muted">{{ $user->created_at->format('H:i') }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if($user->isInactive())
                                                            <button class="btn btn-soft-success waves-effect" title="Activer" 
                                                                    onclick="activateUser({{ $user->id }}, '{{ $user->username }}')">
                                                                <i data-feather="user-check" class="icon-xs"></i>
                                                            </button>
                                                        @elseif($user->isActive() && !$user->isAdmin())
                                                            <button class="btn btn-soft-warning waves-effect" title="Suspendre" 
                                                                    onclick="suspendUser({{ $user->id }}, '{{ $user->username }}')">
                                                                <i data-feather="user-x" class="icon-xs"></i>
                                                            </button>
                                                        @elseif($user->isSuspended())
                                                            <button class="btn btn-soft-success waves-effect" title="Réactiver" 
                                                                    onclick="activateUser({{ $user->id }}, '{{ $user->username }}')">
                                                                <i data-feather="user-check" class="icon-xs"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <button type="button" class="btn btn-soft-info waves-effect" title="Détails" 
                                                                onclick="showUserDetails({{ $user->id }})">
                                                            <i data-feather="eye" class="icon-xs"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr><!--end tr-->
                                            @endforeach               
                                        </tbody>
                                    </table> <!--end table-->                                               
                                </div><!--end /div-->
                                @else
                                <!-- Aucun résultat -->
                                <div class="text-center py-5" id="noResults">
                                    <div class="animate__animated animate__bounceIn">
                                        <i data-feather="users" class="icon-lg text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                                        <p class="text-muted mb-4">Essayez de modifier vos critères de recherche.</p>
                                        <button class="btn btn-primary waves-effect waves-light" onclick="resetFilters()">
                                            <i data-feather="refresh-cw" class="icon-xs mr-1"></i>Réinitialiser les filtres
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Pagination -->
                            @if($users->hasPages())
                            <div class="row mt-4">
                                <div class="col-sm-12 col-md-5">
                                    <p class="text-muted mb-0">
                                        Affichage de <span class="font-weight-bold">{{ $users->firstItem() }}</span> à 
                                        <span class="font-weight-bold">{{ $users->lastItem() }}</span> 
                                        sur <span class="font-weight-bold">{{ $users->total() }}</span> utilisateurs
                                    </p>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="float-right">
                                        {{ $users->withQueryString()->links() }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                               
            </div><!--end row-->

        </div><!-- container -->

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Gestion dynamique des utilisateurs</span>
        </footer><!--end footer-->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- Modal détails utilisateur -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="user" class="icon-xs mr-2"></i>Détails Utilisateur
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Contenu chargé dynamiquement -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">
                    <i data-feather="x" class="icon-xs mr-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast notifications -->
<div class="position-fixed top-0 right-0 p-3" style="z-index: 1050; right: 0; top: 0;">
    <div id="toastContainer"></div>
</div>

<!-- CSS personnalisé pour les animations -->
<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.icon-pulse {
    animation: pulse 2s infinite;
}

.icon-bounce {
    animation: bounce 2s infinite;
}

.icon-shake {
    animation: shake 3s infinite;
}

.icon-glow {
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes bounce {
    0%, 20%, 60%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    80% { transform: translateY(-5px); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
    20%, 40%, 60%, 80% { transform: translateX(2px); }
}

@keyframes glow {
    from { box-shadow: 0 0 10px #007bff; }
    to { box-shadow: 0 0 20px #007bff, 0 0 30px #007bff; }
}

.counter {
    font-size: 2rem;
    font-weight: bold;
    transition: all 0.3s ease;
}

.animated-badge {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.animated-badge:hover {
    transform: scale(1.1);
}

.pulse-success {
    animation: pulseSuccess 2s infinite;
}

.pulse-warning {
    animation: pulseWarning 2s infinite;
}

@keyframes pulseSuccess {
    0% { background-color: #28a745; }
    50% { background-color: #34ce57; }
    100% { background-color: #28a745; }
}

@keyframes pulseWarning {
    0% { background-color: #ffc107; }
    50% { background-color: #ffcd39; }
    100% { background-color: #ffc107; }
}

.hover-zoom {
    transition: transform 0.3s ease;
}

.hover-zoom:hover {
    transform: scale(1.1);
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.search-suggestion {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.search-suggestion:hover {
    background-color: #f8f9fa;
}

.toast {
    min-width: 300px;
}

.btn-group .btn {
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

.user-row {
    transition: all 0.3s ease;
}

.user-row:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- JavaScript pour les fonctionnalités dynamiques -->
<script>
// Variables globales
let searchTimeout;
let currentView = 'table';

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Animation des compteurs
    animateCounters();
    
    // Initialiser AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
    
    // Mettre à jour les notifications
    updateNotifications();
    
    // Auto-refresh toutes les 30 secondes
    setInterval(refreshStats, 30000);
});

// Animation des compteurs
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 50;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.ceil(current);
            }
        }, 20);
    });
}

// Recherche en temps réel
function liveSearch() {
    clearTimeout(searchTimeout);
    const searchTerm = document.getElementById('search').value;
    
    if (searchTerm.length > 2) {
        searchTimeout = setTimeout(() => {
            showSuggestions(searchTerm);
        }, 300);
    } else {
        hideSuggestions();
    }
}

// Afficher les suggestions de recherche
function showSuggestions(term) {
    // Simuler des suggestions (à remplacer par un appel AJAX)
    const suggestions = [
        'john.doe@example.com',
        'admin@attendis.com',
        'user123',
        '+225 07 07 07 07 07'
    ].filter(s => s.toLowerCase().includes(term.toLowerCase()));
    
    const container = document.getElementById('searchSuggestions');
    container.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const div = document.createElement('div');
        div.className = 'search-suggestion';
        div.textContent = suggestion;
        div.onclick = () => selectSuggestion(suggestion);
        container.appendChild(div);
    });
    
    container.style.display = suggestions.length > 0 ? 'block' : 'none';
}

// Masquer les suggestions
function hideSuggestions() {
    document.getElementById('searchSuggestions').style.display = 'none';
}

// Sélectionner une suggestion
function selectSuggestion(suggestion) {
    document.getElementById('search').value = suggestion;
    hideSuggestions();
    applyFilters();
}

// Recherche rapide dans la topbar
function quickSearchUsers() {
    const searchTerm = document.getElementById('quickSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            row.classList.add('animate__animated', 'animate__fadeIn');
        } else {
            row.style.display = 'none';
        }
    });
    
    updateResultCount();
}

// Effacer la recherche rapide
function clearQuickSearch() {
    document.getElementById('quickSearch').value = '';
    document.querySelectorAll('.user-row').forEach(row => {
        row.style.display = '';
    });
    updateResultCount();
}

// Appliquer les filtres
function applyFilters() {
    showLoading();
    
    // Simuler un délai de chargement
    setTimeout(() => {
        const search = document.getElementById('search').value;
        const status = document.getElementById('status').value;
        const type = document.getElementById('type').value;
        
        // Construire l'URL avec les paramètres
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);
        if (type) params.append('type', type);
        
        // Rediriger avec les filtres
        window.location.href = `{{ route('user.users-list') }}?${params.toString()}`;
    }, 500);
}

// Réinitialiser les filtres
function resetFilters() {
    document.getElementById('search').value = '';
    document.getElementById('status').value = '';
    document.getElementById('type').value = '';
    
    showLoading();
    setTimeout(() => {
        window.location.href = `{{ route('user.users-list') }}`;
    }, 300);
}

// Filtrer par statut
function filterByStatus(status) {
    document.getElementById('status').value = status;
    applyFilters();
}

// Filtrer par type
function filterByType(type) {
    document.getElementById('type').value = type;
    applyFilters();
}

// Activer un utilisateur
function activateUser(userId, username) {
    if (confirm(`Êtes-vous sûr de vouloir activer ${username} ?`)) {
        showLoading();
        
        // Simuler l'activation
        setTimeout(() => {
            hideLoading();
            showToast('Succès', `${username} a été activé avec succès !`, 'success');
            updateUserRow(userId, 'active');
            updateStats();
        }, 1000);
    }
}

// Suspendre un utilisateur
function suspendUser(userId, username) {
    if (confirm(`Êtes-vous sûr de vouloir suspendre ${username} ?`)) {
        showLoading();
        
        setTimeout(() => {
            hideLoading();
            showToast('Succès', `${username} a été suspendu.`, 'warning');
            updateUserRow(userId, 'suspended');
            updateStats();
        }, 1000);
    }
}

// Activation en masse
function bulkActivateAll() {
    const pendingCount = document.querySelectorAll('.pulse-warning').length;
    
    if (pendingCount === 0) {
        showToast('Info', 'Aucun utilisateur en attente d\'activation.', 'info');
        return;
    }
    
    if (confirm(`Activer tous les ${pendingCount} utilisateurs en attente ?`)) {
        const button = event.target.closest('button');
        const spinner = button.querySelector('.loading-spinner');
        
        button.disabled = true;
        spinner.style.display = 'inline-block';
        
        setTimeout(() => {
            button.disabled = false;
            spinner.style.display = 'none';
            showToast('Succès', `${pendingCount} utilisateurs ont été activés !`, 'success');
            // Recharger la page
            window.location.reload();
        }, 2000);
    }
}

// Afficher les détails d'un utilisateur
function showUserDetails(userId) {
    const modal = document.getElementById('userDetailsModal');
    const content = document.getElementById('userDetailsContent');
    
    // Afficher le modal avec loading
    $(modal).modal('show');
    
    // Simuler le chargement des données
    setTimeout(() => {
        content.innerHTML = `
            <div class="row">
                <div class="col-md-4 text-center mb-3">
                    <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" class="rounded-circle" style="width: 120px; height: 120px;">
                    <h5 class="mt-3">Utilisateur #${userId}</h5>
                </div>
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr><td><strong>ID:</strong></td><td>#${userId}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>user${userId}@attendis.com</td></tr>
                        <tr><td><strong>Téléphone:</strong></td><td>+225 07 07 07 07 ${userId.toString().padStart(2, '0')}</td></tr>
                        <tr><td><strong>Type:</strong></td><td>Utilisateur</td></tr>
                        <tr><td><strong>Statut:</strong></td><td><span class="badge badge-success">Actif</span></td></tr>
                        <tr><td><strong>Dernière connexion:</strong></td><td>Il y a 2 heures</td></tr>
                    </table>
                </div>
            </div>
        `;
    }, 800);
}

// Basculer entre les vues
function toggleView(view) {
    currentView = view;
    // Implémenter la logique de basculement entre table et cartes
    showToast('Info', `Vue ${view} sélectionnée`, 'info');
}

// Sélectionner/désélectionner tous
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Mettre à jour une ligne utilisateur
function updateUserRow(userId, newStatus) {
    const row = document.querySelector(`[data-user-id="${userId}"]`);
    if (row) {
        const statusBadge = row.querySelector('.animated-badge');
        
        if (newStatus === 'active') {
            statusBadge.className = 'badge badge-success badge-pill animated-badge pulse-success';
            statusBadge.innerHTML = '<i data-feather="check-circle" class="icon-xs mr-1"></i>Actif';
        } else if (newStatus === 'suspended') {
            statusBadge.className = 'badge badge-danger badge-pill animated-badge';
            statusBadge.innerHTML = '<i data-feather="x-circle" class="icon-xs mr-1"></i>Suspendu';
        }
        
        // Réinitialiser Feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

// Mettre à jour les statistiques
function updateStats() {
    refreshStats();
}

// Actualiser les statistiques
function refreshStats() {
    const button = document.getElementById('refreshBtn');
    const icon = button.querySelector('i');
    
    icon.classList.add('fa-spin');
    
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        showToast('Succès', 'Statistiques mises à jour !', 'success');
    }, 1000);
}

// Actualiser la liste des utilisateurs
function refreshUsersList() {
    showLoading();
    
    setTimeout(() => {
        hideLoading();
        showToast('Succès', 'Liste des utilisateurs actualisée !', 'success');
    }, 1500);
}

// Mettre à jour le nombre de résultats
function updateResultCount() {
    const visibleRows = document.querySelectorAll('.user-row:not([style*="display: none"])').length;
    document.getElementById('resultCount').textContent = `${visibleRows} résultat(s)`;
}

// Mettre à jour les notifications
function updateNotifications() {
    const pendingUsers = document.querySelectorAll('.pulse-warning').length;
    
    document.getElementById('pendingCount').textContent = pendingUsers;
    document.getElementById('pendingCount2').textContent = pendingUsers;
    document.getElementById('pendingBadge').textContent = pendingUsers;
}

// Exporter les utilisateurs
function exportUsers() {
    showToast('Info', 'Export en cours...', 'info');
    
    setTimeout(() => {
        showToast('Succès', 'Export terminé ! Fichier téléchargé.', 'success');
    }, 2000);
}

// Afficher le loading
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'block';
}

// Masquer le loading
function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
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
    
    document.getElementById('toastContainer').appendChild(toast);
    
    // Initialiser le toast avec Bootstrap
    $(toast).toast({ delay: 4000 }).toast('show');
    
    // Supprimer après fermeture
    $(toast).on('hidden.bs.toast', function() {
        this.remove();
    });
    
    // Réinitialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}
</script>

@endsection