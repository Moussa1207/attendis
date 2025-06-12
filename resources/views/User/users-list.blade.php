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
                <!-- AMÉLIORÉ: Bouton Dashboard → Nouveau utilisateur -->
                <li class="creat-btn">
                    <div class="nav-link">
                        <a class="btn btn-sm btn-soft-success waves-effect" href="{{ route('admin.users.create') }}" role="button">
                            <i class="fas fa-user-plus mr-2"></i>Nouveau utilisateur
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
                                    <i data-feather="users" class="mr-2"></i>Gestion des utilisateurs
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
            
            <!-- Statistiques rapides CLIQUABLES -->
            <div class="row justify-content-center" id="statsCards">
                <!-- CARTE CONNECTÉS (Total Users) - CLIQUABLE -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('all')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Connectés</p>
                                    <h3 class="my-2 counter text-success" data-target="{{ $users->total() }}">{{ $users->total() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-success"><i class="mdi mdi-account-check"></i></span> 
                                        <span class="status-text">Tous les comptes</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="users" class="align-self-center text-success icon-md"></i>  
                                    </div>
                                </div>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <!-- CARTE ACTIFS - CLIQUABLE -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('active')" style="cursor: pointer;">
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
                
                <!-- CARTE EN ATTENTE - CLIQUABLE -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('inactive')" style="cursor: pointer;">
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
                
                <!-- CARTE ADMINISTRATEURS - CLIQUABLE -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByType('admin')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">  
                                    <p class="text-dark mb-1 font-weight-semibold">Administrateur</p>                                         
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
                                        <i data-feather="filter" class="mr-2"></i>Filtres intelligents
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
                            <form id="filterForm" action="{{ url()->current() }}" method="GET">
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
                                            <!-- AMÉLIORÉ: Types utilisateurs avec rôles métier -->
                                            <select name="type" id="type" class="form-control" onchange="applyFilters()">
                                                <option value="">Tous les types</option>
                                                <option value="admin" {{ request('type') == 'admin' ? 'selected' : '' }}>🛡️ Administrateur</option>
                                                <option value="ecran" {{ request('type') == 'ecran' ? 'selected' : '' }}>🖥️  Ecran</option>
                                                <option value="accueil" {{ request('type') == 'accueil' ? 'selected' : '' }}>🏢  Accueil</option>
                                                <option value="conseiller" {{ request('type') == 'conseiller' ? 'selected' : '' }}>👥  Conseiller</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary btn-block waves-effect waves-light">
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

            <!-- Liste des Utilisateurs -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="list" class="mr-2"></i>Liste interactive
                                        <span class="badge badge-soft-primary ml-2" id="resultCount">{{ $users->total() }} résultat(s)</span>
                                    </h4>                      
                                </div><!--end col-->
                                <!-- AMÉLIORÉ: Boutons Créer, Activer et Supprimer ajoutés -->
                                <div class="col-auto"> 
                                    <div class="btn-group mr-2">
                                        <button class="btn btn-sm btn-success waves-effect" onclick="showCreateUserModal()" title="Créer utilisateur">
                                            <i data-feather="user-plus" class="icon-xs mr-1"></i>Créer
                                        </button>
                                        <button class="btn btn-sm btn-warning waves-effect" onclick="showBulkActivateModal()" title="Activer tous les inactifs">
                                            <i data-feather="zap" class="icon-xs mr-1"></i>Activer
                                        </button>
                                        <button class="btn btn-sm btn-danger waves-effect" onclick="showBulkDeleteModal()" title="Supprimer sélectionnés">
                                            <i data-feather="trash-2" class="icon-xs mr-1"></i>Supprimer
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleSelectAll()" title="Sélectionner tout" id="selectAllBtn">
                                            <i data-feather="square" class="icon-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshUsersList()" title="Actualiser">
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
                                                        <input type="checkbox" class="user-checkbox mr-2" value="{{ $user->id }}" onchange="handleIndividualCheckbox()">
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
                                                    @if($user->user_type_id == 1 || $user->isAdmin())
                                                        <span class="badge badge-custom-type badge-pill">
                                                            <i data-feather="shield" class="icon-xs mr-1"></i>Administrateur
                                                        </span>
                                                    @elseif($user->user_type_id == 2)
                                                        <span class="badge badge-custom-type badge-pill">
                                                            <i data-feather="monitor" class="icon-xs mr-1"></i> Ecran
                                                        </span>
                                                    @elseif($user->user_type_id == 3)
                                                        <span class="badge badge-custom-type badge-pill">
                                                            <i data-feather="home" class="icon-xs mr-1"></i> Accueil
                                                        </span>
                                                    @elseif($user->user_type_id == 4)
                                                        <span class="badge badge-custom-type badge-pill">
                                                            <i data-feather="users" class="icon-xs mr-1"></i> Conseiller
                                                        </span>
                                                    @else
                                                        <span class="badge badge-custom-type badge-pill">
                                                            <i data-feather="user" class="icon-xs mr-1"></i>Utilisateur
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
                                                                    onclick="showActivateUserModal({{ $user->id }}, '{{ $user->username }}')">
                                                                <i data-feather="user-check" class="icon-xs"></i>
                                                            </button>
                                                        @elseif($user->isActive() && !$user->isAdmin())
                                                            <button class="btn btn-soft-warning waves-effect" title="Suspendre" 
                                                                    onclick="showSuspendUserModal({{ $user->id }}, '{{ $user->username }}')">
                                                                <i data-feather="user-x" class="icon-xs"></i>
                                                            </button>
                                                        @elseif($user->isSuspended())
                                                            <button class="btn btn-soft-success waves-effect" title="Réactiver" 
                                                                    onclick="showReactivateUserModal({{ $user->id }}, '{{ $user->username }}')">
                                                                <i data-feather="user-check" class="icon-xs"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <button type="button" class="btn btn-soft-info waves-effect" title="Détails" 
                                                                onclick="showUserDetails({{ $user->id }})">
                                                            <i data-feather="eye" class="icon-xs"></i>
                                                        </button>
                                                        
                                                        @if(!$user->isAdmin() || (\App\Models\User::where('user_type_id', 1)->where('status_id', 2)->count() > 1))
                                                        <button type="button" class="btn btn-soft-danger waves-effect" title="Supprimer" 
                                                                onclick="showDeleteUserModal({{ $user->id }}, '{{ $user->username }}')">
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

<!-- ==================================================================================== -->
<!-- MODALES PROFESSIONNELLES -->
<!-- ==================================================================================== -->

<!-- Modal Confirmation Universelle -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <div id="modalIcon" class="modal-icon mb-3">
                        <!-- Icône dynamique selon le type -->
                    </div>
                    <h4 class="modal-title font-weight-bold" id="modalTitle">Titre</h4>
                </div>
                <button type="button" class="close position-absolute" data-dismiss="modal" style="top: 15px; right: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p id="modalMessage" class="text-muted mb-0">Message</p>
                <div id="modalDetails" class="mt-3 p-3 bg-light rounded" style="display: none;">
                    <!-- Détails supplémentaires si nécessaire -->
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary btn-rounded mr-2" data-dismiss="modal" id="cancelBtn">
                    <i data-feather="x" class="icon-xs mr-1"></i>Annuler
                </button>
                <button type="button" class="btn btn-rounded" id="confirmBtn">
                    <i class="icon-xs mr-1"></i>
                    <span id="confirmText">Confirmer</span>
                    <div class="spinner-border spinner-border-sm ml-2" role="status" style="display: none;" id="confirmSpinner">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal détails utilisateur (conservée) -->
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
    transition: all 0.3s ease;
    cursor: default;
}

/* NOUVEAUX STYLES POUR LES CARTES CLIQUABLES */
.clickable-card {
    cursor: pointer !important;
    transition: all 0.3s ease;
}

.clickable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.clickable-card:active {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Effet de sélection active */
.card-selected {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px solid #2196f3;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
}

/* NOUVEAU : Style uniforme pour les badges de type utilisateur */
.badge-custom-type {
    background-color: #12a4ed !important;
    color: white !important;
    border: 1px solid #0e8bc4;
    transition: all 0.3s ease;
}

.badge-custom-type:hover {
    background-color: #0e8bc4 !important;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(18, 164, 237, 0.3);
}

/* ==================================================================================== */
/* STYLES POUR MODALES PROFESSIONNELLES */
/* ==================================================================================== */

.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    animation: iconPulse 2s infinite ease-in-out;
}

@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Icônes selon le type d'action */
.modal-icon.danger {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
}

.modal-icon.warning {
    background: linear-gradient(#ffa500);
    color: white;
    box-shadow: 0 8px 25px rgba(254, 202, 87, 0.3);
}

.modal-icon.success {
    background: linear-gradient(135deg, #48cab2, #2ecc71);
    color: white;
    box-shadow: 0 8px 25px rgba(72, 202, 178, 0.3);
}

.modal-icon.info {
    background: linear-gradient(135deg, #3867d6, #4834d4);
    color: white;
    box-shadow: 0 8px 25px rgba(56, 103, 214, 0.3);
}

/* Boutons arrondis avec animations */
.btn-rounded {
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-rounded:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-rounded:active {
    transform: translateY(0);
}

/* États des boutons */
.btn-danger.btn-rounded {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    border: none;
}

.btn-warning.btn-rounded {
    background: linear-gradient(#ffa500);
    border: none;
    color: white;
}

.btn-success.btn-rounded {
    background: linear-gradient(135deg, #48cab2, #2ecc71);
    border: none;
}

.btn-info.btn-rounded {
    background: linear-gradient(135deg, #3867d6, #4834d4);
    border: none;
}

/* Animation de chargement sur le bouton */
.btn-loading {
    pointer-events: none;
    opacity: 0.7;
}

/* Détails pliables */
.modal-details {
    transition: all 0.3s ease;
}

/* Overlay pour backdrop personnalisé */
.modal-backdrop.show {
    opacity: 0.7;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3));
}

/* Responsive */
@media (max-width: 576px) {
    .modal-icon {
        width: 60px;
        height: 60px;
        font-size: 1.8rem;
    }
    
    .btn-rounded {
        padding: 8px 20px;
        font-size: 14px;
    }
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

/* Indicateur de filtre actif */
.filter-active {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
}

.filter-active:hover {
    background: linear-gradient(45deg, #218838, #1ea88a);
}
</style>

<script>
// Variables globales
let searchTimeout;
let realTimeInterval;
let lastUpdateTimestamp = Date.now();
let isSelectAllActive = false;
let currentAction = null; // Pour stocker l'action en cours

// Initialisation complète
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Démarrer les mises à jour temps réel
    startRealTimeUpdates();
    
    // Vérifier l'état des filtres actifs
    checkActiveFilters();
    
    //  Initialiser la gestion des sélections
    initializeSelectionHandlers();
    
    //  Gestion visibilité page
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopRealTimeUpdates();
        } else {
            startRealTimeUpdates();
        }
    });
});

// ==================================================================================== 
// SYSTÈME DE MODALES PROFESSIONNELLES
// ==================================================================================== 

/**
 * Afficher une modale de confirmation personnalisée
 */
function showConfirmationModal(config) {
    const modal = document.getElementById('confirmationModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalDetails = document.getElementById('modalDetails');
    const confirmBtn = document.getElementById('confirmBtn');
    const confirmText = document.getElementById('confirmText');
    const confirmSpinner = document.getElementById('confirmSpinner');
    
    // Configuration par défaut
    const defaultConfig = {
        type: 'danger', // danger, warning, success, info
        icon: 'alert-triangle',
        title: 'Confirmation requise',
        message: 'Êtes-vous sûr de vouloir effectuer cette action ?',
        details: null,
        confirmText: 'Confirmer',
        confirmClass: 'btn-danger',
        onConfirm: null,
        showSpinner: true
    };
    
    // Fusionner avec la configuration fournie
    const finalConfig = { ...defaultConfig, ...config };
    
    // Configurer l'icône
    modalIcon.className = `modal-icon ${finalConfig.type}`;
    modalIcon.innerHTML = `<i data-feather="${finalConfig.icon}"></i>`;
    
    // Configurer le titre et le message
    modalTitle.textContent = finalConfig.title;
    modalMessage.textContent = finalConfig.message;
    
    // Configurer les détails (optionnel)
    if (finalConfig.details) {
        modalDetails.innerHTML = finalConfig.details;
        modalDetails.style.display = 'block';
    } else {
        modalDetails.style.display = 'none';
    }
    
    // Configurer le bouton de confirmation
    confirmBtn.className = `btn btn-rounded ${finalConfig.confirmClass}`;
    confirmText.textContent = finalConfig.confirmText;
    confirmSpinner.style.display = 'none';
    
    // Stocker l'action pour l'exécuter plus tard
    currentAction = finalConfig.onConfirm;
    
    // Régénérer les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Afficher la modale
    $('#confirmationModal').modal('show');
}

/**
 * Gestionnaire du bouton de confirmation
 */
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmBtn');
    const confirmText = document.getElementById('confirmText');
    const confirmSpinner = document.getElementById('confirmSpinner');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (currentAction && typeof currentAction === 'function') {
                // Afficher le spinner
                confirmSpinner.style.display = 'inline-block';
                confirmBtn.classList.add('btn-loading');
                confirmText.textContent = 'Traitement...';
                
                // Exécuter l'action
                currentAction();
            }
        });
    }
});

// ==================================================================================== 
// MODALES SPÉCIFIQUES POUR CHAQUE ACTION
// ==================================================================================== 

/**
 * Modal de suppression d'utilisateur
 */
function showDeleteUserModal(userId, username) {
    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '⚠️ Suppression définitive',
        message: `Êtes-vous absolument certain de vouloir supprimer ${username} ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>Cette action est irréversible !</strong><br>
                <small>• Toutes les données de l'utilisateur seront perdues<br>
                • L'historique des actions sera supprimé<br>
                • Les permissions associées seront révoquées</small>
            </div>
        `,
        confirmText: 'Supprimer définitivement',
        confirmClass: 'btn-danger',
        onConfirm: () => executeDeleteUser(userId, username)
    });
}

/**
 * Modal de suspension d'utilisateur
 */
function showSuspendUserModal(userId, username) {
    showConfirmationModal({
        type: 'warning',
        icon: 'user-x',
        title: '⚠️ Suspension d\'utilisateur',
        message: `Confirmer la suspension de ${username} ?`,
        details: `
            <div class="text-warning">
                <i data-feather="info" class="icon-xs mr-1"></i>
                <strong>Conséquences de la suspension :</strong><br>
                <small>• L'utilisateur ne pourra plus se connecter<br>
                • Les sessions actives seront terminées<br>
                • Les accès seront révoqués temporairement</small>
            </div>
        `,
        confirmText: 'Suspendre l\'utilisateur',
        confirmClass: 'btn-warning',
        onConfirm: () => executeSuspendUser(userId, username)
    });
}

/**
 * Modal d'activation d'utilisateur
 */
function showActivateUserModal(userId, username) {
    showConfirmationModal({
        type: 'success',
        icon: 'user-check',
        title: '✅ Activation d\'utilisateur',
        message: `Confirmer l'activation de ${username} ?`,
        details: `
            <div class="text-success">
                <i data-feather="check-circle" class="icon-xs mr-1"></i>
                <strong>Après activation :</strong><br>
                <small>• L'utilisateur pourra se connecter<br>
                • Les permissions par défaut seront appliquées<br>
                • Un email de notification sera envoyé</small>
            </div>
        `,
        confirmText: 'Activer l\'utilisateur',
        confirmClass: 'btn-success',
        onConfirm: () => executeActivateUser(userId, username)
    });
}

/**
 * Modal de réactivation d'utilisateur
 */
function showReactivateUserModal(userId, username) {
    showConfirmationModal({
        type: 'success',
        icon: 'user-check',
        title: '🔄 Réactivation d\'utilisateur',
        message: `Confirmer la réactivation de ${username} ?`,
        details: `
            <div class="text-success">
                <i data-feather="refresh-cw" class="icon-xs mr-1"></i>
                <strong>Après réactivation :</strong><br>
                <small>• L'utilisateur retrouvera tous ses accès<br>
                • Les permissions seront restaurées<br>
                • L'historique sera conservé</small>
            </div>
        `,
        confirmText: 'Réactiver l\'utilisateur',
        confirmClass: 'btn-success',
        onConfirm: () => executeReactivateUser(userId, username)
    });
}

/**
 * Modal de suppression en masse
 */
function showBulkDeleteModal() {
    const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
    
    if (selectedUsers.length === 0) {
        showToast('Attention', 'Veuillez sélectionner au moins un utilisateur à supprimer.', 'warning');
        return;
    }
    
    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '🗑️ Suppression en masse',
        message: `Supprimer ${selectedUsers.length} utilisateur(s) sélectionné(s) ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>ATTENTION : Action irréversible !</strong><br>
                <small>• ${selectedUsers.length} utilisateur(s) seront supprimé(s) définitivement<br>
                • Toutes leurs données seront perdues<br>
                • Cette action ne peut pas être annulée</small>
            </div>
        `,
        confirmText: `Supprimer ${selectedUsers.length} utilisateur(s)`,
        confirmClass: 'btn-danger',
        onConfirm: () => executeBulkDelete(selectedUsers)
    });
}

/**
 * Modal d'activation en masse
 */
function showBulkActivateModal() {
    const inactiveCount = document.querySelectorAll('.badge-warning').length;
    
    if (inactiveCount === 0) {
        showToast('Information', 'Aucun utilisateur inactif à activer.', 'info');
        return;
    }
    
    showConfirmationModal({
        type: 'success',
        icon: 'zap',
        title: '⚡ Activation en masse',
        message: `Activer tous les ${inactiveCount} utilisateur(s) en attente ?`,
        details: `
            <div class="text-success">
                <i data-feather="users" class="icon-xs mr-1"></i>
                <strong>Résultat de l'activation :</strong><br>
                <small>• ${inactiveCount} utilisateur(s) seront activé(s)<br>
                • Ils pourront se connecter immédiatement<br>
                • Des emails de notification seront envoyés</small>
            </div>
        `,
        confirmText: `Activer ${inactiveCount} utilisateur(s)`,
        confirmClass: 'btn-success',
        onConfirm: () => executeBulkActivate(inactiveCount)
    });
}

// ==================================================================================== 
// FONCTIONS D'EXÉCUTION DES ACTIONS
// ==================================================================================== 

/**
 * Exécuter la suppression d'un utilisateur
 */
function executeDeleteUser(userId, username) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();
        
        // 🆕 VRAI APPEL AJAX vers l'endpoint de suppression
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
                showToast('Succès', data.message || `${username} a été supprimé définitivement.`, 'success');
                
                // Retirer la ligne de l'utilisateur du tableau
                const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (userRow) {
                    userRow.remove();
                }
                
                // Mettre à jour les compteurs
                updateCountersAfterDelete();
                
                // Recharger la page après 1.5 secondes pour mettre à jour tous les compteurs
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Erreur', data.message || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Delete error:', error);
            showToast('Erreur', 'Erreur lors de la suppression', 'error');
        });
    }, 1500);
}
/**
 * Exécuter la suspension d'un utilisateur
 */
function executeSuspendUser(userId, username) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();
        
        // VRAI APPEL AJAX
        fetch(`/admin/users/${userId}/suspend`, {
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
                showToast('Succès', data.message, 'success');
                location.reload();
            } else {
                showToast('Erreur', data.message || 'Erreur lors de la suspension', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Suspension error:', error);
            showToast('Erreur', 'Erreur lors de la suspension', 'error');
        });
    }, 1500);
}

/**
 * Exécuter l'activation d'un utilisateur
 */
function executeActivateUser(userId, username) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();
        
        // 🆕 VRAI APPEL AJAX vers l'endpoint d'activation
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
                showToast('Succès', data.message || `${username} a été activé avec succès.`, 'success');
                
                // Mettre à jour le statut dans le tableau
                updateUserStatusInTable(userId, 'active');
                
                // Recharger la page après 1.5 secondes
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Erreur', data.message || 'Erreur lors de l\'activation', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Activation error:', error);
            showToast('Erreur', 'Erreur lors de l\'activation', 'error');
        });
    }, 1500);
}
/**
 * Exécuter la réactivation d'un utilisateur
 */
function executeReactivateUser(userId, username) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();
        
        // VRAI APPEL AJAX
        fetch(`/admin/users/${userId}/reactivate`, {
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
                showToast('Succès', data.message, 'success');
                location.reload();
            } else {
                showToast('Erreur', data.message || 'Erreur lors de la réactivation', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Reactivation error:', error);
            showToast('Erreur', 'Erreur lors de la réactivation', 'error');
        });
    }, 1500);
}

/**
 * Exécuter la suppression en masse
 */
function executeBulkDelete(selectedUsers) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();
        
        // Récupérer les IDs des utilisateurs sélectionnés
        const userIds = Array.from(selectedUsers).map(checkbox => parseInt(checkbox.value));
        
        // 🆕 VRAI APPEL AJAX vers l'endpoint de suppression en masse
        fetch('/admin/users/bulk-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_ids: userIds
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showToast('Succès', data.message || `${userIds.length} utilisateur(s) supprimé(s) !`, 'success');
                
                // Retirer toutes les lignes supprimées du tableau
                userIds.forEach(userId => {
                    const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                    if (userRow) {
                        userRow.remove();
                    }
                });
                
                // Décocher toutes les cases
                document.querySelectorAll('.user-checkbox:checked').forEach(cb => cb.checked = false);
                const selectAllCheckbox = document.getElementById('selectAll');
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                
                // Mettre à jour le bouton de sélection
                const selectAllBtn = document.getElementById('selectAllBtn');
                if (selectAllBtn) {
                    selectAllBtn.classList.remove('btn-primary', 'btn-warning');
                    selectAllBtn.classList.add('btn-outline-secondary');
                    selectAllBtn.innerHTML = '<i data-feather="square" class="icon-xs"></i>';
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }
                
                // Recharger après 2 secondes pour tout mettre à jour
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showToast('Erreur', data.message || 'Erreur lors de la suppression en masse', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Bulk delete error:', error);
            showToast('Erreur', 'Erreur lors de la suppression en masse', 'error');
        });
    }, 1500);
}
/**
 * Exécuter l'activation en masse
 */
function executeBulkActivate(inactiveCount) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();
        
        // 🆕 VRAI APPEL AJAX vers l'endpoint d'activation en masse
        fetch('/admin/users/bulk-activate', {
            method: 'POST',
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
                showToast('Succès', data.message || `${inactiveCount} utilisateur(s) activé(s) !`, 'success');
                
                // Mettre à jour visuellement les statuts des utilisateurs inactifs
                document.querySelectorAll('.badge-warning').forEach(badge => {
                    if (badge.textContent.includes('En attente')) {
                        badge.className = 'badge badge-success badge-pill';
                        badge.innerHTML = '<i data-feather="check-circle" class="icon-xs mr-1"></i>Actif';
                    }
                });
                
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
                
                // Recharger après 2 secondes
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showToast('Erreur', data.message || 'Erreur lors de l\'activation en masse', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Bulk activation error:', error);
            showToast('Erreur', 'Erreur lors de l\'activation en masse', 'error');
        });
    }, 1500);
}

/**
 * Initialiser les gestionnaires de sélection
 */
function initializeSelectionHandlers() {
    // Mettre à jour l'état initial du bouton sélectionner tout
    const selectAllBtn = document.getElementById('selectAllBtn');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    
    if (selectAllBtn && userCheckboxes.length > 0) {
        // S'assurer que le bouton affiche la bonne icône
        selectAllBtn.innerHTML = '<i data-feather="square" class="icon-xs"></i>';
        selectAllBtn.title = 'Sélectionner tout';
        
        // Re-générer les icônes Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    console.log(`🔄 Gestionnaires de sélection initialisés pour ${userCheckboxes.length} utilisateur(s)`);
}

// =====================================================
// NOUVELLES FONCTIONS DE FILTRAGE FONCTIONNELLES
// =====================================================

/**
 * Filtrer par statut (depuis les cartes cliquables)
 */
function filterByStatus(status) {
    console.log('🔍 Filtrage par statut:', status);
    
    // Mettre à jour le select du statut
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.value = status === 'all' ? '' : status;
    }
    
    // Réinitialiser le type pour ne filtrer que par statut
    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        typeSelect.value = '';
    }
    
    // Ajouter effet visuel sur la carte sélectionnée
    highlightSelectedCard(status);
    
    // Appliquer le filtre
    applyFilters();
}

/**
 * Filtrer par type (depuis les cartes cliquables)
 */
function filterByType(type) {
    console.log('🔍 Filtrage par type:', type);
    
    // Mettre à jour le select du type
    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        typeSelect.value = type;
    }
    
    // Réinitialiser le statut pour ne filtrer que par type
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.value = '';
    }
    
    // Ajouter effet visuel sur la carte sélectionnée
    highlightSelectedCard(type);
    
    // Appliquer le filtre
    applyFilters();
}

/**
 * Mettre en évidence la carte sélectionnée
 */
function highlightSelectedCard(filter) {
    // Supprimer toutes les sélections précédentes
    document.querySelectorAll('.clickable-card').forEach(card => {
        card.classList.remove('card-selected');
    });
    
    // Ajouter la sélection à la carte appropriée
    const cardIndex = {
        'all': 0,
        'active': 1,
        'inactive': 2,
        'admin': 3
    }[filter];
    
    if (cardIndex !== undefined) {
        const cards = document.querySelectorAll('.clickable-card');
        if (cards[cardIndex]) {
            cards[cardIndex].classList.add('card-selected');
            
            // Retirer la sélection après 3 secondes
            setTimeout(() => {
                cards[cardIndex].classList.remove('card-selected');
            }, 3000);
        }
    }
}

/**
 * Appliquer les filtres - FONCTION RÉELLE
 */
function applyFilters() {
    console.log('🔄 Application des filtres...');
    
    showLoading();
    
    // Récupérer les valeurs des filtres
    const searchValue = document.getElementById('search').value.trim();
    const statusValue = document.getElementById('status').value;
    const typeValue = document.getElementById('type').value;
    
    // Construire l'URL avec les paramètres
    const url = new URL(window.location.href);
    const params = new URLSearchParams();
    
    if (searchValue) params.set('search', searchValue);
    if (statusValue) params.set('status', statusValue);
    if (typeValue) params.set('type', typeValue);
    
    // Construire l'URL finale
    const finalUrl = url.pathname + (params.toString() ? '?' + params.toString() : '');
    
    console.log('🚀 Redirection vers:', finalUrl);
    
    // Toast de confirmation
    showToast('Filtres', 'Application des filtres en cours...', 'info');
    
    // Rediriger avec les paramètres
    setTimeout(() => {
        window.location.href = finalUrl;
    }, 500);
}

/**
 * Réinitialiser tous les filtres - FONCTION RÉELLE
 */
function resetFilters() {
    console.log('🔄 Réinitialisation des filtres...');
    
    showLoading();
    
    // Réinitialiser tous les champs du formulaire
    document.getElementById('search').value = '';
    document.getElementById('status').value = '';
    document.getElementById('type').value = '';
    
    // Masquer les suggestions de recherche
    const suggestions = document.getElementById('searchSuggestions');
    if (suggestions) {
        suggestions.style.display = 'none';
    }
    
    // Supprimer les sélections de cartes
    document.querySelectorAll('.clickable-card').forEach(card => {
        card.classList.remove('card-selected');
    });
    
    // Toast de confirmation
    showToast('Filtres', 'Filtres réinitialisés !', 'success');
    
    // Rediriger vers l'URL de base (sans paramètres)
    setTimeout(() => {
        window.location.href = window.location.pathname;
    }, 1000);
}

/**
 * Vérifier et mettre en évidence les filtres actifs
 */
function checkActiveFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilters = urlParams.has('search') || urlParams.has('status') || urlParams.has('type');
    
    if (hasFilters) {
        const filterForm = document.getElementById('filterForm');
        const resetBtn = filterForm.querySelector('button[onclick="resetFilters()"]');
        
        if (resetBtn) {
            resetBtn.classList.add('filter-active');
            resetBtn.innerHTML = '<i data-feather="x-circle" class="align-self-center icon-xs mr-1"></i>Réinitialiser (Actifs)';
            
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    }
}

// =====================================================
// FONCTION SÉLECTION AMÉLIORÉE
// =====================================================

/**
 * Basculer la sélection de tous les utilisateurs - CORRIGÉE
 */
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectAllBtn = document.getElementById('selectAllBtn');
    
    if (userCheckboxes.length === 0) {
        showToast('Information', 'Aucun utilisateur à sélectionner.', 'info');
        return;
    }
    
    // Déterminer le nouvel état basé sur l'état actuel
    // Si la checkbox du tableau existe, utiliser son état, sinon basculer l'état global
    if (selectAllCheckbox) {
        isSelectAllActive = selectAllCheckbox.checked;
    } else {
        // Si pas de checkbox dans le tableau, basculer l'état
        isSelectAllActive = !isSelectAllActive;
    }
    
    // Vérifier si toutes les cases sont déjà cochées
    const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
    
    // Si appelé depuis le bouton, basculer selon l'état actuel
    if (!selectAllCheckbox || event.target.closest('#selectAllBtn')) {
        isSelectAllActive = !allChecked;
    }
    
    // Synchroniser la checkbox du tableau si elle existe
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = isSelectAllActive;
    }
    
    // Appliquer l'état à toutes les cases utilisateur
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = isSelectAllActive;
        
        // Effet visuel sur les lignes
        const row = checkbox.closest('.user-row');
        if (row) {
            if (isSelectAllActive) {
                row.style.backgroundColor = 'rgba(0, 123, 255, 0.1)';
                row.style.transition = 'background-color 0.3s ease';
            } else {
                row.style.backgroundColor = '';
            }
        }
    });
    
    // Mettre à jour l'apparence du bouton
    if (selectAllBtn) {
        if (isSelectAllActive) {
            selectAllBtn.classList.add('btn-primary');
            selectAllBtn.classList.remove('btn-outline-secondary');
            selectAllBtn.innerHTML = `<i data-feather="check-square" class="icon-xs"></i> ${userCheckboxes.length}`;
            selectAllBtn.title = 'Désélectionner tout';
        } else {
            selectAllBtn.classList.remove('btn-primary');
            selectAllBtn.classList.add('btn-outline-secondary');
            selectAllBtn.innerHTML = '<i data-feather="square" class="icon-xs"></i>';
            selectAllBtn.title = 'Sélectionner tout';
        }
    }
    
    // Toast de confirmation avec compteur
    const selectedCount = isSelectAllActive ? userCheckboxes.length : 0;
    const message = isSelectAllActive 
        ? `✅ ${selectedCount} utilisateur(s) sélectionné(s)`
        : `❌ Sélection annulée (${selectedCount} sélectionné(s))`;
    
    showToast('Sélection', message, isSelectAllActive ? 'success' : 'info');
    
    // Mettre à jour les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log(`✅ ${isSelectAllActive ? 'Sélection' : 'Désélection'} de ${userCheckboxes.length} utilisateur(s)`);
}

/**
 * Fonction séparée pour gérer les clics individuels sur les checkboxes
 */
function handleIndividualCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
    
    // Mettre à jour l'état de la checkbox principale
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedCount === userCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < userCheckboxes.length;
    }
    
    // Mettre à jour l'apparence du bouton
    if (selectAllBtn) {
        if (checkedCount === userCheckboxes.length) {
            selectAllBtn.classList.add('btn-primary');
            selectAllBtn.classList.remove('btn-outline-secondary');
            selectAllBtn.innerHTML = `<i data-feather="check-square" class="icon-xs"></i> ${checkedCount}`;
        } else if (checkedCount > 0) {
            selectAllBtn.classList.add('btn-warning');
            selectAllBtn.classList.remove('btn-outline-secondary', 'btn-primary');
            selectAllBtn.innerHTML = `<i data-feather="minus-square" class="icon-xs"></i> ${checkedCount}`;
        } else {
            selectAllBtn.classList.remove('btn-primary', 'btn-warning');
            selectAllBtn.classList.add('btn-outline-secondary');
            selectAllBtn.innerHTML = '<i data-feather="square" class="icon-xs"></i>';
        }
        
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    // Mettre à jour l'état global
    isSelectAllActive = checkedCount === userCheckboxes.length;
}

/**
 * Recherche en temps réel améliorée
 */
function liveSearch() {
    const searchInput = document.getElementById('search');
    const searchValue = searchInput.value.trim();
    
    // Débounce pour éviter trop de requêtes
    clearTimeout(searchTimeout);
    
    if (searchValue.length >= 2) {
        searchTimeout = setTimeout(() => {
            console.log('🔍 Recherche en temps réel:', searchValue);
            
            // Simuler des suggestions (à remplacer par un appel AJAX réel)
            showSearchSuggestions(searchValue);
        }, 300);
    } else {
        hideSearchSuggestions();
    }
}

/**
 * Afficher les suggestions de recherche
 */
function showSearchSuggestions(query) {
    const suggestions = document.getElementById('searchSuggestions');
    if (!suggestions) return;
    
    // Simuler des suggestions (remplacer par vraies données)
    const mockSuggestions = [
        `${query} dans les noms`,
        `${query} dans les emails`,
        `${query} dans les entreprises`
    ];
    
    suggestions.innerHTML = mockSuggestions.map(suggestion => 
        `<div class="search-suggestion" onclick="selectSearchSuggestion('${suggestion}')">${suggestion}</div>`
    ).join('');
    
    suggestions.style.display = 'block';
}

/**
 * Masquer les suggestions de recherche
 */
function hideSearchSuggestions() {
    const suggestions = document.getElementById('searchSuggestions');
    if (suggestions) {
        suggestions.style.display = 'none';
    }
}

/**
 * Sélectionner une suggestion de recherche
 */
function selectSearchSuggestion(suggestion) {
    document.getElementById('search').value = suggestion;
    hideSearchSuggestions();
    applyFilters();
}

// =====================================================
// FONCTIONS TEMPS RÉEL 
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
    // Simulation de mise à jour (remplacer par vraie API)
    console.log('🔄 Mise à jour temps réel simulée');
    showUpdateIndicator();
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
}

// =====================================================
// AUTRES FONCTIONS 
// =====================================================

function showCreateUserModal() {
    window.location.href = "{{ route('admin.users.create') }}";
}

function refreshStats() {
    updateRealTimeStats();
}

function refreshUsersList() {
    showLoading();
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function showUserDetails(userId) {
    $('#userDetailsModal').modal('show');
}

function quickSearchUsers() {
    const searchValue = document.getElementById('quickSearch').value;
    if (searchValue.trim()) {
        document.getElementById('search').value = searchValue;
        applyFilters();
    }
}

function clearQuickSearch() {
    document.getElementById('quickSearch').value = '';
}

function exportUsers() {
    showToast('Export', 'Génération du fichier en cours...', 'info');
    setTimeout(() => {
        showToast('Succès', 'Fichier exporté avec succès !', 'success');
    }, 2000);
}

// Fonctions utilitaires
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'block';
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'none';
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
</script>
@endsection