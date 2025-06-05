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
            
            <!-- Statistiques rapides -->
            <div class="row justify-content-center" id="statsCards">
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Total Affiché</p>
                                    <h3 class="my-2 counter" data-target="{{ $users->total() }}">{{ $users->total() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-info"><i class="mdi mdi-filter"></i></span> 
                                        <span class="status-text">Résultats filtrés</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="users" class="align-self-center text-muted icon-md"></i>  
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
                                    <p class="text-dark mb-1 font-weight-semibold">Actifs</p>
                                    <h3 class="my-2 text-success counter" data-target="{{ $users->where('status_id', 2)->count() }}">{{ $users->where('status_id', 2)->count() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-success"><i class="mdi mdi-check-circle"></i></span> 
                                        <span class="progress-text">Comptes actifs</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="user-check" class="align-self-center text-success icon-md"></i>  
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
                                    <p class="text-dark mb-1 font-weight-semibold">En Attente</p>
                                    <h3 class="my-2 text-warning counter" data-target="{{ $users->where('status_id', 1)->count() }}">{{ $users->where('status_id', 1)->count() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-warning"><i class="mdi mdi-clock-outline"></i></span> 
                                        <span class="pending-text">À activer</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="user-plus" class="align-self-center text-warning icon-md"></i>  
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
                                    <p class="text-dark mb-1 font-weight-semibold">Administrateurs</p>                                         
                                    <h3 class="my-2 text-primary counter" data-target="{{ $users->where('user_type_id', 1)->count() }}">{{ $users->where('user_type_id', 1)->count() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-primary"><i class="mdi mdi-shield-check"></i></span> 
                                        <span class="admin-text">Admins</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="shield" class="align-self-center text-primary icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                               
            </div><!--end row-->

            <!-- Filtres -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
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
                                                   placeholder="Nom, email, téléphone, entreprise..." 
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
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <button class="btn btn-outline-info btn-block" onclick="filterByStatus('inactive')">
                                        <i data-feather="clock" class="mr-2"></i>Demandes
                                        <span class="badge badge-warning ml-2" id="pendingBadge">{{ $users->where('status_id', 1)->count() }}</span>
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-success btn-block" onclick="filterByStatus('active')">
                                        <i data-feather="user-check" class="mr-2"></i>Actifs
                                        <span class="badge badge-success ml-2" id="activeBadge">{{ $users->where('status_id', 2)->count() }}</span>
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-primary btn-block" onclick="filterByType('admin')">
                                        <i data-feather="shield" class="mr-2"></i>Admins
                                        <span class="badge badge-primary ml-2" id="adminBadge">{{ $users->where('user_type_id', 1)->count() }}</span>
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-warning btn-block" onclick="bulkActivateAll()">
                                        <i data-feather="zap" class="mr-2"></i>Activer Tout
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-secondary btn-block" onclick="showCreateUserModal()">
                                        <i data-feather="user-plus" class="mr-2"></i>Créer
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-danger btn-block" onclick="bulkDeleteSelected()">
                                        <i data-feather="trash-2" class="mr-2"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des Utilisateurs -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
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
                                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleSelectAll()">
                                            <i data-feather="check-square" class="icon-xs"></i>
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
                                                <th class="border-top-0">Entreprise</th>
                                                <th class="border-top-0">Type</th>
                                                <th class="border-top-0">Statut</th>
                                                <th class="border-top-0">Inscription</th>
                                                <th class="border-top-0">Actions</th>
                                            </tr><!--end tr-->
                                        </thead>
                                        <tbody id="usersTableBody">
                                            @foreach($users as $index => $user)
                                            <tr class="user-row" data-user-id="{{ $user->id }}">                                                        
                                                <td>
                                                    <div class="media">
                                                        <input type="checkbox" class="user-checkbox mr-2" value="{{ $user->id }}">
                                                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="" class="rounded-circle thumb-md mr-3">
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
                                                    <span class="badge badge-light-info">{{ $user->company ?? 'Non renseigné' }}</span>
                                                </td>
                                                <td>
                                                    @if($user->isAdmin())
                                                        <span class="badge badge-primary badge-pill">
                                                            <i data-feather="shield" class="icon-xs mr-1"></i>Admin
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary badge-pill">
                                                            <i data-feather="user" class="icon-xs mr-1"></i>User
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->isActive())
                                                        <span class="badge badge-success badge-pill">
                                                            <i data-feather="check-circle" class="icon-xs mr-1"></i>Actif
                                                        </span>
                                                    @elseif($user->isInactive())
                                                        <span class="badge badge-warning badge-pill">
                                                            <i data-feather="clock" class="icon-xs mr-1"></i>En attente
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger badge-pill">
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
                                                        <!-- ACTIONS SELON LE STATUT -->
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
                                                                    onclick="reactivateUser({{ $user->id }}, '{{ $user->username }}')">
                                                                <i data-feather="user-check" class="icon-xs"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <button type="button" class="btn btn-soft-info waves-effect" title="Détails" 
                                                                onclick="showUserDetails({{ $user->id }})">
                                                            <i data-feather="eye" class="icon-xs"></i>
                                                        </button>
                                                        
                                                        @if(!$user->isAdmin() || (\App\Models\User::where('user_type_id', 1)->where('status_id', 2)->count() > 1))
                                                        <button type="button" class="btn btn-soft-danger waves-effect" title="Supprimer" 
                                                                onclick="deleteUser({{ $user->id }}, '{{ $user->username }}')">
                                                            <i data-feather="trash-2" class="icon-xs"></i>
                                                        </button>
                                                        @endif
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
                                    <div>
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

<style>
/* CSS existant + améliorations temps réel */
.card {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.report-card {
    transition: none;
    cursor: default;
}

.counter {
    font-size: 2rem;
    font-weight: bold;
    transition: all 0.3s ease;
}

.user-row {
    transition: all 0.3s ease;
}

.user-row:hover {
    background-color: #f8f9fa;
}

.btn {
    transition: all 0.3s ease;
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

/* NOUVELLES ANIMATIONS TEMPS RÉEL */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.highlight-change {
    background-color: rgba(0, 123, 255, 0.1) !important;
    transition: background-color 0.3s ease;
}

.status-changing {
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.status-updated {
    background-color: rgba(40, 167, 69, 0.1);
    transition: background-color 0.3s ease;
}

.badge {
    transition: all 0.3s ease;
}

.updating {
    opacity: 0.8;
    transform: scale(0.98);
}
</style>

<script>
// Variables globales
let searchTimeout;
let realTimeInterval;
let lastUpdateTimestamp = Date.now();

// Initialisation complète
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // NOUVEAU : Démarrer les mises à jour temps réel
    startRealTimeUpdates();
    
    // NOUVEAU : Gestion visibilité page
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopRealTimeUpdates();
        } else {
            startRealTimeUpdates();
        }
    });
});

// =====================================================
// NOUVELLES FONCTIONS TEMPS RÉEL
// =====================================================

function startRealTimeUpdates() {
    if (realTimeInterval) {
        clearInterval(realTimeInterval);
    }
    
    realTimeInterval = setInterval(function() {
        updateRealTimeStats();
    }, 15000); // Toutes les 15 secondes
    
    console.log('⚡ Mises à jour temps réel démarrées');
}

function stopRealTimeUpdates() {
    if (realTimeInterval) {
        clearInterval(realTimeInterval);
        realTimeInterval = null;
    }
    console.log('⏸️ Mises à jour temps réel arrêtées');
}

function updateRealTimeStats() {
    fetch('/admin/api/stats', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsCards(data.stats);
            updateNotificationsBadges(data.stats);
            showUpdateIndicator();
        }
    })
    .catch(error => {
        console.warn('⚠️ Erreur mise à jour temps réel:', error);
    });
}

function updateStatsCards(stats) {
    const statsMapping = {
        'totalUsers': stats.total_users,
        'activeBadge': stats.active_users,
        'pendingBadge': stats.inactive_users,
        'adminBadge': stats.admin_users,
        'pendingCount': stats.inactive_users,
        'pendingCount2': stats.inactive_users,
        'resultCount': `${stats.total_users} résultat(s)`
    };
    
    Object.entries(statsMapping).forEach(([elementId, newValue]) => {
        const element = document.getElementById(elementId);
        if (element) {
            const currentValue = element.textContent.trim();
            const newValueStr = String(newValue);
            
            if (currentValue !== newValueStr) {
                animateValueChange(element, newValueStr);
                highlightChange(element);
            }
        }
    });
}

function updateNotificationsBadges(stats) {
    const pendingCount = stats.inactive_users;
    
    ['pendingCount', 'pendingCount2'].forEach(badgeId => {
        const badge = document.getElementById(badgeId);
        if (badge) {
            const oldValue = parseInt(badge.textContent) || 0;
            
            badge.textContent = pendingCount;
            
            if (pendingCount === 0) {
                badge.style.display = 'none';
            } else {
                badge.style.display = 'inline-block';
                
                if (oldValue < pendingCount) {
                    badge.style.animation = 'pulse 1s ease-in-out';
                    setTimeout(() => {
                        badge.style.animation = '';
                    }, 1000);
                }
            }
        }
    });
}

function animateValueChange(element, newValue) {
    element.style.transition = 'all 0.3s ease';
    element.style.transform = 'scale(1.1)';
    element.style.color = '#007bff';
    
    setTimeout(() => {
        element.textContent = newValue;
        element.style.transform = 'scale(1)';
        element.style.color = '';
    }, 150);
}

function highlightChange(element) {
    const parentCard = element.closest('.card');
    if (parentCard) {
        parentCard.style.backgroundColor = 'rgba(0, 123, 255, 0.05)';
        parentCard.style.transition = 'background-color 0.3s ease';
        
        setTimeout(() => {
            parentCard.style.backgroundColor = '';
        }, 2000);
    }
}

function showUpdateIndicator() {
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        const icon = refreshBtn.querySelector('i[data-feather="refresh-cw"]');
        if (icon) {
            icon.style.animation = 'spin 0.5s linear';
            setTimeout(() => {
                icon.style.animation = '';
            }, 500);
        }
    }
    
    console.log('🔄 Stats mises à jour -', new Date().toLocaleTimeString());
}

// =====================================================
// FONCTIONS EXISTANTES AMÉLIORÉES
// =====================================================

function reactivateUser(userId, username) {
    if (confirm(`Êtes-vous sûr de vouloir réactiver ${username} ?`)) {
        showLoading();
        
        fetch(`/admin/users/${userId}/activate`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showToast('Succès', `${username} a été réactivé avec succès !`, 'success');
                updateUserRow(userId, 'active');
                // NOUVEAU : Mise à jour immédiate des stats
                setTimeout(() => updateRealTimeStats(), 1000);
            } else {
                showToast('Erreur', data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showToast('Erreur', 'Erreur lors de la réactivation', 'error');
        });
    }
}

function deleteUser(userId, username) {
    if (confirm(`⚠️ ATTENTION ⚠️\n\nÊtes-vous sûr de vouloir SUPPRIMER définitivement ${username} ?\n\nCette action est IRRÉVERSIBLE !`)) {
        if (confirm(`Dernière confirmation :\n\nVoulez-vous vraiment supprimer ${username} ?\n\nToutes ses données seront perdues.`)) {
            showLoading();
            
            fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showToast('Succès', `${username} a été supprimé définitivement.`, 'success');
                    const row = document.querySelector(`[data-user-id="${userId}"]`);
                    if (row) {
                        row.remove();
                    }
                    // NOUVEAU : Mise à jour immédiate des stats
                    setTimeout(() => updateRealTimeStats(), 1000);
                } else {
                    showToast('Erreur', data.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showToast('Erreur', 'Erreur lors de la suppression', 'error');
            });
        }
    }
}

function activateUser(userId, username) {
    if (confirm(`Êtes-vous sûr de vouloir activer ${username} ?`)) {
        showLoading();
        
        fetch(`/admin/users/${userId}/activate`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showToast('Succès', `${username} a été activé avec succès !`, 'success');
                updateUserRow(userId, 'active');
                // NOUVEAU : Mise à jour immédiate des stats
                setTimeout(() => updateRealTimeStats(), 1000);
            } else {
                showToast('Erreur', data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showToast('Erreur', 'Erreur lors de l\'activation', 'error');
        });
    }
}

function suspendUser(userId, username) {
    if (confirm(`Êtes-vous sûr de vouloir suspendre ${username} ?`)) {
        showLoading();
        
        fetch(`/admin/users/${userId}/suspend`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showToast('Succès', `${username} a été suspendu.`, 'warning');
                updateUserRow(userId, 'suspended');
                // NOUVEAU : Mise à jour immédiate des stats
                setTimeout(() => updateRealTimeStats(), 1000);
            } else {
                showToast('Erreur', data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showToast('Erreur', 'Erreur lors de la suspension', 'error');
        });
    }
}

function bulkDeleteSelected() {
    const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
    
    if (selectedUsers.length === 0) {
        showToast('Attention', 'Veuillez sélectionner au moins un utilisateur à supprimer.', 'warning');
        return;
    }
    
    if (confirm(`⚠️ SUPPRESSION EN MASSE ⚠️\n\nVoulez-vous supprimer ${selectedUsers.length} utilisateur(s) sélectionné(s) ?\n\nCette action est IRRÉVERSIBLE !`)) {
        if (confirm(`DERNIÈRE CONFIRMATION :\n\n${selectedUsers.length} utilisateur(s) seront supprimés définitivement.\n\nContinuer ?`)) {
            showLoading();
            
            const userIds = Array.from(selectedUsers).map(checkbox => checkbox.value);
            
            fetch('/admin/users/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_ids: userIds })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showToast('Succès', `${data.deleted_count} utilisateur(s) supprimé(s) avec succès !`, 'success');
                    // NOUVEAU : Mise à jour immédiate des stats
                    setTimeout(() => {
                        updateRealTimeStats();
                        window.location.reload();
                    }, 2000);
                } else {
                    showToast('Erreur', data.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showToast('Erreur', 'Erreur lors de la suppression en masse', 'error');
            });
        }
    }
}

// Redirection vers création d'utilisateur
function showCreateUserModal() {
    window.location.href = "{{ route('admin.users.create') }}";
}

// Fonctions utilitaires
function updateUserRow(userId, newStatus) {
    console.log(`Mise à jour utilisateur ${userId} vers ${newStatus}`);
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

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

// Autres fonctions existantes conservées
function refreshStats() {
    updateRealTimeStats();
}

function applyFilters() {
    console.log('Filtres appliqués');
}

function resetFilters() {
    console.log('Filtres réinitialisés');
}

function filterByStatus(status) {
    console.log('Filtrage par statut:', status);
}

function filterByType(type) {
    console.log('Filtrage par type:', type);
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function bulkActivateAll() {
    console.log('Activation en masse');
}

function refreshUsersList() {
    window.location.reload();
}

function showUserDetails(userId) {
    $('#userDetailsModal').modal('show');
}

function liveSearch() {
    console.log('Recherche en temps réel');
}

function quickSearchUsers() {
    console.log('Recherche rapide');
}

function clearQuickSearch() {
    document.getElementById('quickSearch').value = '';
}

function exportUsers() {
    window.location.href = "{{ route('admin.users.export') }}";
}
</script>
@endsection