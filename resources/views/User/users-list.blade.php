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
                        <a class="btn btn-sm btn-soft-success waves-effect" href="{{ route('User.user-create') }}" role="button">
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
                                                    <span class="badge badge-light-info px-3 py-1">{{ $user->company ?? 'Non renseigné' }}</span>
                                                      @if($user->agency)
                                                         <br>
                                                       <small class="text-muted">
                                                           <i data-feather="home" class="icon-xs"></i> {{ $user->agency->name }}
                                                        </small>
                                                     @endif
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
        
        {{-- ✅ AMÉLIORATION : Bouton "Modifier" conditionné pour les utilisateurs non-admin créés par l'admin connecté --}}
        @if(!$user->isAdmin() && in_array($user->id, $myCreatedUserIds))
        <a href="{{ route('User.user-edit', $user->id) }}" 
           class="btn btn-soft-primary waves-effect" 
           title="Modifier (utilisateur que vous avez créé)">
            <i data-feather="edit-2" class="icon-xs"></i>
        </a>
        @endif
        
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
<!-- MODALES PROFESSIONNELLES  -->
<!-- ==================================================================================== -->

<!--  Modal Confirmation Universelle avec z-index fixé -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
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

<!--  Modal Nouveau Mot de Passe avec scroll et taille adaptée -->
<div class="modal fade" id="newPasswordModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content modal-password-content">
            <div class="modal-header bg-gradient-success text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i data-feather="key" class="icon-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">🔐 Nouveau mot de passe généré</h5>
                        <small class="text-white-50">Identifiants de connexion pour l'utilisateur</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <!--  Corps avec scroll activé -->
            <div class="modal-body modal-password-body p-0">
                <!-- En-tête utilisateur -->
                <div class="user-header-section bg-light border-bottom">
                    <div class="container-fluid p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-large mr-4">
                                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="Avatar" class="rounded-circle shadow" width="60" height="60">
                                    </div>
                                    <div>
                                        <h5 class="mb-1 font-weight-bold" id="newPasswordUserName">Utilisateur</h5>
                                        <p class="text-muted mb-1" id="newPasswordUserEmail">email@example.com</p>
                                        <span class="badge badge-primary" id="newPasswordUserType">Type</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-right">
                                <div class="text-muted">
                                    <small><i data-feather="clock" class="icon-xs mr-1"></i>Réinitialisé le</small>
                                    <p class="font-weight-bold mb-0" id="newPasswordResetDate">--</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="container-fluid p-4">
                    <!-- Alerte importante -->
                    <div class="alert alert-warning">
                        <div class="d-flex align-items-start">
                            <i data-feather="alert-triangle" class="icon-sm text-warning mr-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="text-warning mb-1">⚠️ Important</h6>
                                <p class="mb-0">
                                    Communiquez ces identifiants à l'utilisateur pour sa prochaine connexion. 
                                    L'utilisateur devra changer ce mot de passe lors de sa première connexion.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de connexion -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="info-card">
                                <h6 class="card-title text-primary mb-3">
                                    <i data-feather="user" class="icon-sm mr-2"></i>Informations utilisateur
                                </h6>
                                <div class="info-list">
                                    <div class="info-item">
                                        <span class="info-label">👤 Nom:</span>
                                        <span class="info-value" id="credentialsUsername">--</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">📧 Email:</span>
                                        <span class="info-value" id="credentialsEmail">--</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">📱 Téléphone:</span>
                                        <span class="info-value" id="credentialsPhone">--</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">🏢 Entreprise :</span>
                                        <span class="info-value" id="credentialsCompany">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="info-card bg-light">
                                <h6 class="card-title text-success mb-3">
                                    <i data-feather="key" class="icon-sm mr-2"></i>Nouveau mot de passe
                                </h6>
                                <div class="password-display-container">
                                    <div class="password-box p-3 bg-white border rounded">
                                        <div class="password-content">
                                            <div class="password-label-section">
                                                <small class="text-muted d-block mb-2">Mot de passe temporaire :</small>
                                            </div>
                                            <div class="password-display-section">
                                                <div class="password-wrapper">
                                                    <code class="password-display" id="newPasswordDisplay">Chargement...</code>
                                                </div>
                                                <div class="password-copy-btn">
                                                    <!--  Protection contre double clic + positionnement -->
                                                    <button class="btn btn-sm btn-outline-primary copy-password-btn" onclick="copyNewPassword(this)" title="Copier le mot de passe">
                                                        <i data-feather="copy" class="icon-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <!--  Protection contre double clic -->
                                        <button class="btn btn-success btn-sm btn-block copy-all-btn" onclick="copyAllNewCredentials(this)">
                                            <i data-feather="clipboard" class="icon-xs mr-1"></i>Copier tous les identifiants
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations supplémentaires -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="text-info mb-2">
                                    <i data-feather="info" class="icon-xs mr-1"></i>Informations de réinitialisation
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-0 text-muted">
                                            <li>Réinitialisé par : <strong id="resetByAdmin">--</strong></li>
                                            <li>Format : <strong>Sécurisé (8 caractères)</strong></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-0 text-muted">
                                            <li>Date : <strong id="resetAtDate">--</strong></li>
                                            <li>Changement obligatoire : <strong>Oui</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  Footer sticky bien positionné -->
            <div class="modal-footer modal-password-footer border-top bg-light">
                <div class="d-flex justify-content-between w-100 align-items-center flex-wrap">
                    <div class="footer-info mb-2 mb-md-0">
                        <small class="text-muted">
                            <i data-feather="shield" class="icon-xs mr-1"></i>
                            Connexion sécurisée requise
                        </small>
                    </div>
                    <div class="footer-actions d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="printCredentials()">
                            <i data-feather="printer" class="icon-xs mr-1"></i>Imprimer
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                            <i data-feather="check" class="icon-xs mr-1"></i>Compris
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--  Modal Détails Utilisateur - Taille, centrage et footer améliorés -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <!--  Meilleure taille et centrage -->
    <div class="modal-dialog modal-enhanced modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content modal-content-enhanced">
            <!-- En-tête du modal -->
            <div class="modal-header bg-gradient-primary text-white border-0 modal-header-enhanced">
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="user-avatar-modal mr-3">
                        <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="Avatar" class="rounded-circle" width="50" height="50">
                        <div class="user-status-indicator" id="userStatusIndicator"></div>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modalUserName">
                            <i data-feather="user" class="icon-sm mr-2"></i>Chargement...
                        </h5>
                        <small class="text-white-50" id="modalUserRole">Informations utilisateur</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="mr-3" id="modalQuickActions">
                        <!-- Actions rapides ajoutées dynamiquement -->
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!--  Corps du modal avec hauteur optimisée -->
            <div class="modal-body modal-body-enhanced p-0" id="userDetailsContent">
                <!-- État de chargement -->
                <div class="loading-state text-center py-5" id="loadingState">
                    <div class="spinner-grow text-primary mb-3" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                    <h6 class="text-muted">Chargement des informations...</h6>
                    <p class="text-muted">Veuillez patienter</p>
                </div>

                <!-- Contenu principal (masqué pendant le chargement) -->
                <div class="user-details-content" id="userDetailsContentMain" style="display: none;">
                    <!-- Section d'en-tête utilisateur -->
                    <div class="user-header-section bg-light border-bottom">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-large mr-4">
                                            <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="Avatar" class="rounded-circle shadow" width="80" height="80">
                                            <div class="status-badge" id="userStatusBadge"></div>
                                        </div>
                                        <div>
                                            <h4 class="mb-1 font-weight-bold" id="userFullName">Nom:</h4>
                                            <p class="text-muted mb-2" id="userEmail">email@example.com</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge mr-2" id="userTypeBadge">Type</span>
                                                <span class="badge" id="userStatusBadgeText">Statut</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-lg-right">
                                    <div class="user-stats">
                                        <div class="stat-item">
                                            <h6 class="text-muted mb-0">Inscription</h6>
                                            <p class="font-weight-bold mb-0" id="userRegistrationDate">--</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu en onglets -->
                    <div class="container-fluid p-4">
                        <!-- Navigation des onglets -->
                        <ul class="nav nav-pills nav-pills-enhanced mb-4" id="userDetailsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab">
                                    <i data-feather="user" class="icon-xs mr-1"></i>Informations Générales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="pill" href="#contact" role="tab">
                                    <i data-feather="phone" class="icon-xs mr-1"></i>Contact
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="security-tab" data-toggle="pill" href="#security" role="tab">
                                    <i data-feather="shield" class="icon-xs mr-1"></i>Sécurité
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="activity-tab" data-toggle="pill" href="#activity" role="tab">
                                    <i data-feather="activity" class="icon-xs mr-1"></i>Activité
                                </a>
                            </li>
                        </ul>

                        <!-- Contenu des onglets -->
                        <div class="tab-content" id="userDetailsTabsContent">
                            <!-- Onglet Informations Générales -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="info-card">
                                            <h6 class="card-title text-primary">
                                                <i data-feather="user" class="icon-sm mr-2"></i>Identité
                                            </h6>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Nom:</span>
                                                    <span class="info-value" id="detailUserName">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Identifiant:</span>
                                                    <span class="info-value" id="detailUserId">#--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Type d'utilisateur:</span>
                                                    <span class="info-value" id="detailUserType">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Statut du compte:</span>
                                                    <span class="info-value" id="detailUserStatus">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="info-card">
                                            <h6 class="card-title text-success">
                                                <i data-feather="calendar" class="icon-sm mr-2"></i>Informations Temporelles
                                            </h6>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Date de création:</span>
                                                    <span class="info-value" id="detailCreatedAt">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Dernière modification:</span>
                                                    <span class="info-value" id="detailUpdatedAt">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Temps d'existence:</span>
                                                    <span class="info-value" id="detailAccountAge">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Contact -->
                            <div class="tab-pane fade" id="contact" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="info-card">
                                            <h6 class="card-title text-info">
                                                <i data-feather="mail" class="icon-sm mr-2"></i>Informations de contact
                                            </h6>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Email:</span>
                                                    <span class="info-value">
                                                        <a href="#" id="detailEmailLink" class="text-decoration-none">
                                                            <i data-feather="mail" class="icon-xs mr-1"></i>
                                                            <span id="detailEmail">--</span>
                                                        </a>
                                                    </span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Téléphone:</span>
                                                    <span class="info-value">
                                                        <a href="#" id="detailPhoneLink" class="text-decoration-none">
                                                            <i data-feather="phone" class="icon-xs mr-1"></i>
                                                            <span id="detailPhone">--</span>
                                                        </a>
                                                    </span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Entreprise:</span>
                                                    <span class="info-value" id="detailCompany">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="info-card bg-light">
                                            <h6 class="card-title text-warning">
                                                <i data-feather="message-circle" class="icon-sm mr-2"></i>Actions Rapides
                                            </h6>
                                            <div class="quick-actions">
                                                <button class="btn btn-outline-primary btn-sm btn-block mb-2" onclick="copyUserEmail()">
                                                    <i data-feather="copy" class="icon-xs mr-1"></i>Copier Email
                                                </button>
                                                <button class="btn btn-outline-success btn-sm btn-block mb-2" onclick="callUser()">
                                                    <i data-feather="phone-call" class="icon-xs mr-1"></i>Appeler
                                                </button>
                                                <button class="btn btn-outline-info btn-sm btn-block" onclick="sendMessage()">
                                                    <i data-feather="message-square" class="icon-xs mr-1"></i>Message
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Sécurité -->
                            <div class="tab-pane fade" id="security" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="info-card">
                                            <h6 class="card-title text-danger">
                                                <i data-feather="shield" class="icon-sm mr-2"></i>Sécurité du Compte
                                            </h6>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Statut de sécurité:</span>
                                                    <span class="info-value">
                                                        <span class="badge badge-success">
                                                            <i data-feather="check" class="icon-xs mr-1"></i>Sécurisé
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Dernière connexion:</span>
                                                    <span class="info-value" id="detailLastLogin">Jamais connecté</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Mot de passe modifié:</span>
                                                    <span class="info-value" id="detailPasswordChanged">À la création</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Tentatives de connexion:</span>
                                                    <span class="info-value" id="detailLoginAttempts">
                                                        <span class="badge badge-light">0 échecs récents</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="info-card">
                                            <h6 class="card-title text-warning">
                                                <i data-feather="key" class="icon-sm mr-2"></i>Actions de Sécurité
                                            </h6>
                                            <div class="security-actions">
                                                <!--  BOUTON FONCTIONNEL -->
                                                <button class="btn btn-outline-warning btn-sm btn-block mb-2" onclick="resetUserPassword()">
                                                    <i data-feather="key" class="icon-xs mr-1"></i>Réinitialiser mot de passe
                                                </button>
                                                <button class="btn btn-outline-info btn-sm btn-block mb-2" onclick="sendPasswordEmail()">
                                                    <i data-feather="mail" class="icon-xs mr-1"></i>Envoyer nouveau mot de passe
                                                </button>
                                                <div class="mt-3 p-3 bg-light rounded">
                                                    <small class="text-muted">
                                                        <i data-feather="info" class="icon-xs mr-1"></i>
                                                        Dernière activité de sécurité : <span id="lastSecurityActivity">Aucune action récente</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Activité -->
                            <div class="tab-pane fade" id="activity" role="tabpanel">
                                <div class="info-card">
                                    <h6 class="card-title text-primary">
                                        <i data-feather="activity" class="icon-sm mr-2"></i>Historique d'activité
                                    </h6>
                                    <div class="activity-timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success">
                                                <i data-feather="user-plus" class="icon-xs text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Compte créé</h6>
                                                <p class="timeline-description text-muted">
                                                    L'utilisateur a été créé dans le système
                                                </p>
                                                <small class="timeline-time text-muted" id="activityCreationDate">--</small>
                                            </div>
                                        </div>

                                        <div class="timeline-item" id="activityFirstLogin" style="display: none;">
                                            <div class="timeline-marker bg-info">
                                                <i data-feather="log-in" class="icon-xs text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Première connexion</h6>
                                                <p class="timeline-description text-muted">
                                                    Première connexion au système
                                                </p>
                                                <small class="timeline-time text-muted">--</small>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-warning">
                                                <i data-feather="clock" class="icon-xs text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">En attente d'activation</h6>
                                                <p class="timeline-description text-muted">
                                                    L'utilisateur n'a pas encore activé son compte
                                                </p>
                                                <small class="timeline-time text-muted">Statut actuel</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--  Footer bien positionné et responsive -->
            <div class="modal-footer modal-footer-enhanced border-top bg-light">
                <div class="d-flex justify-content-between w-100 align-items-center flex-wrap">
                    <div class="footer-left mb-2 mb-md-0">
                        <small class="text-muted">
                            <i data-feather="clock" class="icon-xs mr-1"></i>
                            Dernière mise à jour : <span id="modalLastUpdate">Maintenant</span>
                        </small>
                    </div> 
                    <div class="footer-right d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="refreshUserDetails()">
                            <i data-feather="refresh-cw" class="icon-xs mr-1"></i>Actualiser
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                            <i data-feather="x" class="icon-xs mr-1"></i>Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast notifications -->
<div class="position-fixed top-0 right-0 p-3" style="z-index: 2000; right:0; top:0;">
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

/*  STYLES POUR LES CARTES CLIQUABLES */
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

/*  Style uniforme pour les badges de type utilisateur */
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
/*  CORRECTIONS MAJEURES POUR LES MODALES */
/* ==================================================================================== */

/*  Z-INDEX HIÉRARCHIQUE  */
#userDetailsModal {
    z-index: 1050 !important; /* Modal de base */
}

#confirmationModal {
    z-index: 1060 !important; /* Modal de confirmation au-dessus */
}

#newPasswordModal {
    z-index: 1055 !important; /* Modal intermédiaire */
}

/*  FORCER LE SCROLL POUR MODAL MOT DE PASSE */
#newPasswordModal .modal-dialog-scrollable {
    height: calc(100% - 3.5rem);
}

#newPasswordModal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/*  MODAL DÉTAILS AMÉLIORÉE - TAILLE ET CENTRAGE */
.modal-enhanced {
    max-width: 90vw !important;
    width: 90vw !important;
    margin: 2rem auto !important;
}

.modal-content-enhanced {
    max-height: 95vh !important;
    overflow: hidden !important;
    border-radius: 15px !important;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2) !important;
}

.modal-header-enhanced {
    padding: 20px 25px !important;
    min-height: 80px !important;
}

.modal-body-enhanced {
    max-height: calc(95vh - 160px) !important;
    overflow-y: auto !important;
    padding: 0 !important;
}

/*  FOOTER MODAL DÉTAILS BIEN POSITIONNÉ */
.modal-footer-enhanced {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 10 !important;
    background: #f8f9fa !important;
    border-top: 1px solid #dee2e6 !important;
    padding: 15px 25px !important;
    margin-top: auto !important;
}

.footer-left, .footer-right {
    flex-shrink: 0 !important;
}

/*  NAVIGATION ONGLETS AMÉLIORÉE */
.nav-pills-enhanced {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 10px;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 5px !important;
}

.nav-pills-enhanced .nav-link {
    border-radius: 8px !important;
    padding: 10px 15px !important;
    margin: 0 !important;
    font-size: 0.9rem !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
    white-space: nowrap !important;
}

.nav-pills-enhanced .nav-link:hover {
    background-color: rgba(102, 126, 234, 0.1) !important;
    transform: translateY(-1px) !important;
}

.nav-pills-enhanced .nav-link.active {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
    transform: translateY(-1px) !important;
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

/* ==================================================================================== */
/*  STYLES SPÉCIFIQUES MODAL NOUVEAU MOT DE PASSE - CORRIGÉE POUR SCROLL */
/* ==================================================================================== */

/* Modal nouveau mot de passe - Taille et scroll */
.modal-password-content {
    max-height: 90vh !important;
    overflow: hidden !important;
    border-radius: 15px !important;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2) !important;
}

.modal-password-body {
    max-height: calc(90vh - 160px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 0 !important;
}

.modal-password-footer {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 10 !important;
    background: #f8f9fa !important;
    border-top: 1px solid #dee2e6 !important;
    padding: 15px 20px !important;
    margin-top: auto !important;
    flex-shrink: 0 !important;
}

.footer-info, .footer-actions {
    flex-shrink: 0 !important;
}

.footer-actions {
    gap: 8px !important;
}

/* Scroll personnalisé pour la modal mot de passe */
.modal-password-body::-webkit-scrollbar {
    width: 6px;
}

.modal-password-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-password-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.modal-password-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/*  RESPONSIVE MODAL MOT DE PASSE */
@media (max-width: 992px) {
    .modal-password-content {
        max-height: 95vh !important;
    }
    
    .modal-password-body {
        max-height: calc(95vh - 140px) !important;
    }
}

@media (max-width: 768px) {
    #newPasswordModal .modal-dialog {
        max-width: 100vw !important;
        width: 100vw !important;
        margin: 0 !important;
        height: 100vh !important;
    }
    
    .modal-password-content {
        height: 100vh !important;
        max-height: 100vh !important;
        border-radius: 0 !important;
    }
    
    .modal-password-body {
        max-height: calc(100vh - 140px) !important;
    }
    
    .modal-password-footer {
        padding: 12px 15px !important;
    }
    
    .modal-password-footer .d-flex {
        flex-direction: column !important;
        gap: 12px !important;
    }
    
    .footer-actions {
        width: 100% !important;
        justify-content: center !important;
    }
    
    .footer-actions .btn {
        flex: 1 !important;
        margin: 0 5px !important;
    }
    
    /* Réduction padding sur mobile */
    #newPasswordModal .container-fluid {
        padding: 15px !important;
    }
    
    #newPasswordModal .info-card {
        padding: 15px !important;
        margin-bottom: 15px !important;
    }
}

@media (max-width: 576px) {
    .modal-password-footer .footer-actions .btn {
        font-size: 0.875rem !important;
        padding: 8px 12px !important;
    }
}

/* Background gradient pour header */
.bg-gradient-success {
    background: linear-gradient(135deg, #48cab2 0%, #2ecc71 100%) !important;
}

/*  Styles pour éviter le chevauchement du mot de passe */
.password-content {
    width: 100%;
}

.password-label-section {
    margin-bottom: 8px;
}

.password-display-section {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: nowrap;
}

.password-wrapper {
    flex: 1;
    min-width: 0; /* Permet la troncature si nécessaire */
}

.password-display {
    font-size: 16px !important;
    font-weight: bold !important;
    color: #e83e8c !important;
    letter-spacing: 1px !important;
    background: #f8f9fa !important;
    padding: 8px 12px !important;
    border-radius: 4px !important;
    border: 1px solid #dee2e6 !important;
    display: block !important;
    width: 100% !important;
    word-break: break-all !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.password-copy-btn {
    flex-shrink: 0;
}

.password-copy-btn .btn {
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-display-container {
    animation: passwordPulse 2s ease-in-out infinite;
}

@keyframes passwordPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.password-box {
    transition: all 0.3s ease;
    min-width: 0; /* Évite le débordement */
}

.password-box:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

/*  RESPONSIVE POUR MOT DE PASSE */
@media (max-width: 576px) {
    .password-display-section {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .password-copy-btn {
        width: 100%;
    }
    
    .password-copy-btn .btn {
        width: 100%;
        justify-content: center;
    }
    
    .password-display {
        font-size: 14px !important;
        letter-spacing: 0.5px !important;
        white-space: normal !important;
        word-break: break-word !important;
        text-overflow: unset !important;
        overflow: visible !important;
    }
}

/* ==================================================================================== */
/* NOUVEAUX STYLES POUR LE MODAL DÉTAILS UTILISATEUR */
/* ==================================================================================== */

/* En-tête du modal avec dégradé */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Avatar utilisateur avec indicateur de statut */
.user-avatar-modal {
    position: relative;
}

.user-status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
}

.user-status-indicator.active {
    background-color: #28a745;
}

.user-status-indicator.inactive {
    background-color: #ffc107;
}

.user-status-indicator.suspended {
    background-color: #dc3545;
}

/* Avatar grande taille */
.user-avatar-large {
    position: relative;
}

.status-badge {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 3px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-badge.active {
    background-color: #28a745;
}

.status-badge.inactive {
    background-color: #ffc107;
}

.status-badge.suspended {
    background-color: #dc3545;
}

/* Cartes d'information */
.info-card {
    background: white;
    border-radius: 15px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #f1f3f4;
}

.info-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.info-card .card-title {
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    font-size: 1.1rem;
}

/* Liste d'informations */
.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f8f9fa;
    flex-wrap: wrap;
    gap: 8px;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.95rem;
    min-width: 120px;
    flex-shrink: 0;
}

.info-value {
    font-weight: 600;
    color: #495057;
    text-align: right;
    word-break: break-word;
    flex: 1;
}

/* Actions rapides */
.quick-actions .btn {
    transition: all 0.3s ease;
}

.quick-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Actions de sécurité */
.security-actions .btn {
    transition: all 0.3s ease;
}

.security-actions .btn:hover {
    transform: translateY(-2px);
}

/* Timeline d'activité */
.activity-timeline {
    position: relative;
    padding-left: 30px;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border: 3px solid white;
}

.timeline-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f3f4;
}

.timeline-title {
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
}

.timeline-description {
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.timeline-time {
    font-size: 0.85rem;
}

/* Statistiques utilisateur */
.user-stats {
    text-align: center;
}

.stat-item {
    margin-bottom: 15px;
}

/*  RESPONSIVE AMÉLIORÉ POUR MODALES */
@media (max-width: 1200px) {
    .modal-enhanced {
        max-width: 95vw !important;
        width: 95vw !important;
        margin: 1rem auto !important;
    }
}

@media (max-width: 768px) {
    .modal-enhanced {
        max-width: 100vw !important;
        width: 100vw !important;
        margin: 0 !important;
        height: 100vh !important;
    }

    .modal-content-enhanced {
        height: 100vh !important;
        max-height: 100vh !important;
        border-radius: 0 !important;
    }

    .modal-body-enhanced {
        max-height: calc(100vh - 160px) !important;
    }

    .modal-header-enhanced {
        padding: 15px !important;
        min-height: 70px !important;
    }

    .modal-header-enhanced .d-flex {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px !important;
    }

    .modal-footer-enhanced {
        padding: 15px !important;
        flex-direction: column !important;
        gap: 15px !important;
    }

    .modal-footer-enhanced .d-flex {
        flex-direction: column !important;
        width: 100% !important;
        gap: 15px !important;
    }

    .modal-footer-enhanced .btn {
        width: 100% !important;
        margin: 0 !important;
    }

    .nav-pills-enhanced {
        flex-direction: column !important;
        gap: 8px !important;
    }

    .nav-pills-enhanced .nav-link {
        text-align: center !important;
        width: 100% !important;
    }

    .user-header-section .row {
        flex-direction: column !important;
        gap: 15px !important;
    }

    .info-item {
        flex-direction: column !important;
        align-items: flex-start !important;
        text-align: left !important;
    }

    .info-value {
        text-align: left !important;
        width: 100% !important;
    }

    .info-label {
        min-width: auto !important;
        width: 100% !important;
    }

    .user-header-section .container-fluid {
        padding: 15px !important;
    }

    .info-card {
        padding: 15px !important;
        margin-bottom: 15px !important;
    }
}

/* Autres styles existants conservés */
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

/* Animation de chargement */
.loading-state {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.spinner-grow {
    width: 3rem;
    height: 3rem;
}

/* Effets de transition pour le contenu */
.user-details-content {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/*  PROTECTION CONTRE LES DOUBLES CLICS */
.copy-password-btn.btn-copying,
.copy-all-btn.btn-copying {
    pointer-events: none !important;
    opacity: 0.6 !important;
}
</style>

<script>
// Variables globales
let searchTimeout;
let realTimeInterval;
let lastUpdateTimestamp = Date.now();
let isSelectAllActive = false;
let currentAction = null; // Pour stocker l'action en cours
let currentUserId = null; // Pour stocker l'ID de l'utilisateur affiché dans le modal
let newPasswordData = null; //  Pour stocker les données du nouveau mot de passe

//  Protection contre les appels multiples
let copyInProgress = false;
let copyAllInProgress = false;

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

    //  S'assurer que la modal de nouveau mot de passe permet le scroll
    $('#newPasswordModal').on('shown.bs.modal', function() {
        // Forcer l'activation du scroll
        const modalBody = this.querySelector('.modal-password-body');
        if (modalBody) {
            modalBody.style.overflowY = 'auto';
            modalBody.style.maxHeight = 'calc(90vh - 160px)';
            
            // Activer le scroll tactile pour mobile
            modalBody.style.webkitOverflowScrolling = 'touch';
            modalBody.style.overscrollBehavior = 'contain';
        }
        
        console.log(' Modal nouveau mot de passe - Scroll activé');
    });

    //  Gestion des modales avec z-index
    setupModalHierarchy();
    
    //  Scroll tactile et trackpad
    enhanceModalScroll();
});

// ✅  Améliorer le scroll tactile et trackpad
function enhanceModalScroll() {
    // Activer le scroll fluide pour toutes les modales
    document.querySelectorAll('.modal-body, .modal-password-body').forEach(modalBody => {
        // Scroll tactile pour mobile
        modalBody.style.webkitOverflowScrolling = 'touch';
        modalBody.style.overscrollBehavior = 'contain';
        
        // Améliorer le scroll sur PC/trackpad
        modalBody.addEventListener('wheel', function(e) {
            if (this.scrollHeight > this.clientHeight) {
                e.stopPropagation();
            }
        }, { passive: true });
        
        // Support tactile amélioré
        let startY = 0;
        modalBody.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
        }, { passive: true });
        
        modalBody.addEventListener('touchmove', function(e) {
            const currentY = e.touches[0].clientY;
            const deltaY = startY - currentY;
            
            // Permettre le scroll vertical
            if (this.scrollHeight > this.clientHeight) {
                this.scrollTop += deltaY * 0.5; // Scroll plus fluide
                e.preventDefault();
            }
            
            startY = currentY;
        }, { passive: false });
    });
    
    console.log(' Scroll amélioré pour toutes les modales');
}

//  Gestion hiérarchique des modales
function setupModalHierarchy() {
    // Gérer l'ordre d'affichage des modales
    $('#userDetailsModal').on('show.bs.modal', function() {
        $(this).css('z-index', 1050);
    });

    $('#confirmationModal').on('show.bs.modal', function() {
        $(this).css('z-index', 1060);
    });

    $('#newPasswordModal').on('show.bs.modal', function() {
        $(this).css('z-index', 1055);
    });

    // S'assurer que la modal de confirmation apparaît au-dessus
    $('#confirmationModal').on('shown.bs.modal', function() {
        $(this).find('.modal-dialog').addClass('modal-dialog-top');
    });
}

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

    //  S'assurer que la modal apparaît au-dessus
    $('#confirmationModal').modal('show');
    setTimeout(() => {
        $('#confirmationModal').css('z-index', 1060);
    }, 100);
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
//  RÉINITIALISATION MOT DE PASSE DEPUIS MODAL DÉTAILS
// ==================================================================================== 

/**
 *  Réinitialiser le mot de passe de l'utilisateur (NOUVELLE FONCTIONNALITÉ)
 * Appelée depuis l'onglet "Sécurité" du modal de détails utilisateur
 */
function resetUserPassword() {
    if (!currentUserId) {
        showToast('Erreur', 'Aucun utilisateur sélectionné pour la réinitialisation', 'error');
        return;
    }

    // Récupérer les infos utilisateur depuis le modal actuel
    const userName = document.getElementById('modalUserName')?.textContent?.replace('🛡️', '').replace('👤', '').trim() || 'cet utilisateur';

    // Afficher modal de confirmation
    showConfirmationModal({
        type: 'warning',
        icon: 'key',
        title: '🔐 Réinitialisation de mot de passe',
        message: `Confirmer la réinitialisation du mot de passe de ${userName} ?`,
        details: `
            <div class="text-warning">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>Conséquences de la réinitialisation :</strong><br>
                <small>• Un nouveau mot de passe temporaire sera généré<br>
                • L'ancien mot de passe sera écrasé en base de données<br>
                • L'utilisateur devra changer ce mot de passe à sa prochaine connexion<br>
                • Vous recevrez le nouveau mot de passe pour le communiquer</small>
            </div>
        `,
        confirmText: 'Réinitialiser le mot de passe',
        confirmClass: 'btn-warning',
        onConfirm: () => executePasswordReset(currentUserId, userName)
    });
}

/**
 * Exécuter la réinitialisation du mot de passe
 */
function executePasswordReset(userId, userName) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        console.log(' Réinitialisation mot de passe pour utilisateur ID:', userId);

        //  APPEL AJAX VERS LA NOUVELLE ROUTE
        fetch(`/admin/users/${userId}/reset-password`, {
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
                // Stocker les données du nouveau mot de passe
                newPasswordData = data;

                // Afficher le toast de succès
                showToast('Succès', data.message || `Mot de passe réinitialisé pour ${userName}`, 'success');

                //  AFFICHER LA MODAL AVEC LE NOUVEAU MOT DE PASSE
                showNewPasswordModal(data);

                // Mettre à jour l'activité de sécurité dans le modal de détails si ouvert
                updateSecurityActivity('Mot de passe réinitialisé', data.reset_info?.reset_at || 'Maintenant');

                console.log('✅ Mot de passe réinitialisé avec succès:', data);
            } else {
                showToast('Erreur', data.message || 'Erreur lors de la réinitialisation du mot de passe', 'error');
                console.error('❌ Erreur réinitialisation:', data);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('❌ Erreur réseau réinitialisation:', error);
            showToast('Erreur', 'Erreur lors de la réinitialisation du mot de passe', 'error');
        });
    }, 1500);
}

// ==================================================================================== 
//  MODAL POUR AFFICHAGE DU NOUVEAU MOT DE PASSE
// ==================================================================================== 

/**
 *  Afficher la modal avec le nouveau mot de passe généré
 */
function showNewPasswordModal(data) {
    console.log('🔐 Affichage modal nouveau mot de passe:', data);

    // Remplir les informations utilisateur
    document.getElementById('newPasswordUserName').textContent = data.user?.username || 'Utilisateur';
    document.getElementById('newPasswordUserEmail').textContent = data.user?.email || 'email@example.com';
    document.getElementById('newPasswordUserType').textContent = data.user?.type || 'Utilisateur';
    document.getElementById('newPasswordResetDate').textContent = data.reset_info?.reset_at || new Date().toLocaleString('fr-FR');

    // Remplir les credentials
    document.getElementById('credentialsUsername').textContent = data.user?.username || '--';
    document.getElementById('credentialsEmail').textContent = data.user?.email || '--';
    document.getElementById('credentialsPhone').textContent = data.user?.mobile_number || '--';
    document.getElementById('credentialsCompany').textContent = data.user?.company || 'Non renseigné';

    // 🔐 AFFICHER LE NOUVEAU MOT DE PASSE
    document.getElementById('newPasswordDisplay').textContent = data.new_password || 'Erreur';

    // Informations de réinitialisation
    document.getElementById('resetByAdmin').textContent = data.reset_info?.reset_by || 'Administrateur';
    document.getElementById('resetAtDate').textContent = data.reset_info?.reset_at || new Date().toLocaleString('fr-FR');

    //  Réinitialiser les états des boutons
    copyInProgress = false;
    copyAllInProgress = false;
    
    const copyBtn = document.querySelector('.copy-password-btn');
    const copyAllBtn = document.querySelector('.copy-all-btn');
    
    if (copyBtn) {
        copyBtn.classList.remove('btn-copying');
        copyBtn.disabled = false;
    }
    if (copyAllBtn) {
        copyAllBtn.classList.remove('btn-copying');
        copyAllBtn.disabled = false;
    }

    // Afficher la modal
    $('#newPasswordModal').modal('show');
    
    //  Forcer le bon dimensionnement après affichage
    $('#newPasswordModal').on('shown.bs.modal', function() {
        // Ajuster la hauteur selon l'écran
        const modalContent = this.querySelector('.modal-password-content');
        const modalBody = this.querySelector('.modal-password-body');
        
        if (modalContent && modalBody) {
            const windowHeight = window.innerHeight;
            const maxHeight = Math.min(windowHeight * 0.9, 800); // Max 90% de l'écran ou 800px
            
            modalContent.style.maxHeight = maxHeight + 'px';
            modalBody.style.maxHeight = (maxHeight - 160) + 'px';
            
            console.log('✅ Modal redimensionnée:', { windowHeight, maxHeight });
        }
        
        // Activer le scroll amélioré
        enhanceModalScroll();
    });

    // Régénérer les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    console.log('✅ Modal nouveau mot de passe affichée');
}

/**
 *  Copier le nouveau mot de passe avec protection double clic
 */
function copyNewPassword(buttonElement) {
    //  Empêcher les appels multiples
    if (copyInProgress) {
        console.log('🔒 Copie déjà en cours, ignorant...');
        return;
    }

    const password = document.getElementById('newPasswordDisplay').textContent;
    if (!password || password === 'Erreur' || password === 'Chargement...') {
        showToast('Erreur', 'Aucun mot de passe à copier', 'error');
        return;
    }

    // ✅ Marquer comme en cours et désactiver le bouton
    copyInProgress = true;
    buttonElement.classList.add('btn-copying');
    buttonElement.disabled = true;
    
    console.log('📋 Début copie mot de passe:', password);

    navigator.clipboard.writeText(password).then(() => {
        console.log('✅ Mot de passe copié avec succès');
        showToast('Succès', 'Mot de passe copié dans le presse-papier !', 'success');

        // Animation sur le bouton
        const originalHTML = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i data-feather="check" class="icon-xs"></i>';
        buttonElement.classList.add('btn-success');
        buttonElement.classList.remove('btn-outline-primary');

        setTimeout(() => {
            if (buttonElement) {
                buttonElement.innerHTML = originalHTML;
                buttonElement.classList.remove('btn-success', 'btn-copying');
                buttonElement.classList.add('btn-outline-primary');
                buttonElement.disabled = false;
                copyInProgress = false; // ✅ Libérer le verrou
                
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        }, 2000);

    }).catch((error) => {
        console.error('❌ Erreur copie mot de passe:', error);
        showToast('Erreur', 'Impossible de copier le mot de passe', 'error');
        
        // ✅ Libérer le verrou en cas d'erreur
        buttonElement.classList.remove('btn-copying');
        buttonElement.disabled = false;
        copyInProgress = false;
    });
}

/**
 * ✅ CORRIGÉ : Copier tous les nouveaux identifiants avec protection double clic
 */
function copyAllNewCredentials(buttonElement) {
    // ✅ Empêcher les appels multiples
    if (copyAllInProgress) {
        console.log('🔒 Copie complète déjà en cours, ignorant...');
        return;
    }

    if (!newPasswordData) {
        showToast('Erreur', 'Aucunes données à copier', 'error');
        return;
    }

    // ✅ Marquer comme en cours et désactiver le bouton
    copyAllInProgress = true;
    buttonElement.classList.add('btn-copying');
    buttonElement.disabled = true;

    console.log('📋 Début copie complète des identifiants');

    const credentials = `Identifiants de connexion réinitialisés pour ${newPasswordData.user?.username}:

👤 Nom: ${newPasswordData.user?.username}
📧 Email: ${newPasswordData.user?.email}
📱 Téléphone: ${newPasswordData.user?.mobile_number}
🏢 Entreprise: ${newPasswordData.user?.company || 'Non renseigné'}
🔐 Nouveau mot de passe: ${newPasswordData.new_password}

⚠️ Important: L'utilisateur doit changer ce mot de passe lors de sa prochaine connexion.

Réinitialisé le ${newPasswordData.reset_info?.reset_at} par ${newPasswordData.reset_info?.reset_by}
URL de connexion: ${window.location.origin}/login`;

    navigator.clipboard.writeText(credentials).then(() => {
        console.log('✅ Tous les identifiants copiés avec succès');
        showToast('Succès', 'Tous les identifiants copiés !', 'success');

        // Animation sur le bouton
        const originalHTML = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i data-feather="check" class="icon-xs mr-1"></i>Copié !';
        buttonElement.classList.add('btn-success');

        setTimeout(() => {
            if (buttonElement) {
                buttonElement.innerHTML = originalHTML;
                buttonElement.classList.remove('btn-success', 'btn-copying');
                buttonElement.disabled = false;
                copyAllInProgress = false; // ✅ Libérer le verrou
                
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        }, 3000);
    }).catch((error) => {
        console.error('❌ Erreur copie complète:', error);
        showToast('Erreur', 'Impossible de copier les identifiants', 'error');
        
        // ✅ Libérer le verrou en cas d'erreur
        buttonElement.classList.remove('btn-copying');
        buttonElement.disabled = false;
        copyAllInProgress = false;
    });
}

/**
 * 🆕 Imprimer les identifiants
 */
function printCredentials() {
    if (!newPasswordData) {
        showToast('Erreur', 'Aucunes données à imprimer', 'error');
        return;
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
    <title>Identifiants de connexion</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .credential-box { border: 2px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .password { font-size: 18px; font-weight: bold; color: #e83e8c; letter-spacing: 2px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>🔐 Identifiants de connexion réinitialisés</h2>
        <p>Généré le: ${new Date().toLocaleString('fr-FR')}</p>
    </div>

    <div class="credential-box">
        <h3>👤 Informations utilisateur</h3>
        <p><strong>Nom:</strong> ${newPasswordData.user?.username}</p>
        <p><strong>Email:</strong> ${newPasswordData.user?.email}</p>
        <p><strong>Téléphone:</strong> ${newPasswordData.user?.mobile_number}</p>
        <p><strong>Entreprise:</strong> ${newPasswordData.user?.company || 'Non renseigné'}</p>

        <h3>🔐 Nouveau mot de passe temporaire</h3>
        <p class="password">${newPasswordData.new_password}</p>
    </div>

    <div class="warning">
        <h4>⚠️ Important</h4>
        <ul>
            <li>L'utilisateur doit changer ce mot de passe lors de sa prochaine connexion</li>
            <li>Ce document contient des informations sensibles</li>
            <li>Détruisez-le après communication à l'utilisateur</li>
        </ul>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Imprimer</button>
        <button onclick="window.close()">❌ Fermer</button>
    </div>
</body>
</html>
    `);
    printWindow.document.close();
    printWindow.focus();
}

/**
 * Envoyer un email avec le nouveau mot de passe
 */
function sendPasswordEmail() {
    if (!currentUserId) {
        showToast('Erreur', 'Aucun utilisateur sélectionné', 'error');
        return;
    }

    showToast('Info', 'Fonctionnalité d\'envoi d\'email en cours de développement', 'info');
}

// ==================================================================================== 
// MODALES DES ACTIONS UTILISATEURS
// ==================================================================================== 

/**
 * Afficher la modal d'activation d'utilisateur
 */
function showActivateUserModal(userId, userName) {
    showConfirmationModal({
        type: 'success',
        icon: 'user-check',
        title: '✅ Activer l\'utilisateur',
        message: `Confirmer l'activation du compte de ${userName} ?`,
        details: `
            <div class="text-success">
                <i data-feather="info" class="icon-xs mr-1"></i>
                <strong>Conséquences de l'activation :</strong><br>
                <small>• L'utilisateur pourra se connecter à la plateforme<br>
                • Toutes les fonctionnalités lui seront accessibles<br>
                • Il recevra une notification d'activation</small>
            </div>
        `,
        confirmText: 'Activer le compte',
        confirmClass: 'btn-success',
        onConfirm: () => executeUserAction(userId, 'activate', userName)
    });
}

/**
 * Afficher la modal de suspension d'utilisateur
 */
function showSuspendUserModal(userId, userName) {
    showConfirmationModal({
        type: 'warning',
        icon: 'user-x',
        title: '⚠️ Suspendre l\'utilisateur',
        message: `Confirmer la suspension du compte de ${userName} ?`,
        details: `
            <div class="text-warning">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>Conséquences de la suspension :</strong><br>
                <small>• L'utilisateur ne pourra plus se connecter<br>
                • Ses sessions actives seront terminées<br>
                • Il recevra une notification de suspension</small>
            </div>
        `,
        confirmText: 'Suspendre le compte',
        confirmClass: 'btn-warning',
        onConfirm: () => executeUserAction(userId, 'suspend', userName)
    });
}

/**
 * Afficher la modal de suppression d'utilisateur
 */
function showDeleteUserModal(userId, userName) {
    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '🗑️ Supprimer l\'utilisateur',
        message: `Confirmer la suppression définitive du compte de ${userName} ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>⚠️ ATTENTION : Cette action est irréversible !</strong><br>
                <small>• Toutes les données de l'utilisateur seront supprimées<br>
                • L'historique de ses actions sera perdu<br>
                • Cette opération ne peut pas être annulée</small>
            </div>
        `,
        confirmText: 'Supprimer définitivement',
        confirmClass: 'btn-danger',
        onConfirm: () => executeUserAction(userId, 'delete', userName)
    });
}

/**
 * Afficher la modal de réactivation d'utilisateur
 */
function showReactivateUserModal(userId, userName) {
    showConfirmationModal({
        type: 'success',
        icon: 'user-check',
        title: '🔄 Réactiver l\'utilisateur',
        message: `Confirmer la réactivation du compte de ${userName} ?`,
        details: `
            <div class="text-success">
                <i data-feather="info" class="icon-xs mr-1"></i>
                <strong>Conséquences de la réactivation :</strong><br>
                <small>• L'utilisateur pourra à nouveau se connecter<br>
                • Ses accès seront restaurés<br>
                • Il recevra une notification de réactivation</small>
            </div>
        `,
        confirmText: 'Réactiver le compte',
        confirmClass: 'btn-success',
        onConfirm: () => executeUserAction(userId, 'reactivate', userName)
    });
}

// ==================================================================================== 
// MODALES D'ACTIONS GROUPÉES
// ==================================================================================== 

/**
 * Afficher la modal de création d'utilisateur
 */
function showCreateUserModal() {
    window.location.href = "{{ route('User.user-create') }}";
}

/**
 * Afficher la modal d'activation en masse
 */
function showBulkActivateModal() {
    const inactiveCount = document.querySelector('.counter[data-target]')?.getAttribute('data-target') || 0;

    if (inactiveCount == 0) {
        showToast('Info', 'Aucun utilisateur inactif à activer', 'info');
        return;
    }

    showConfirmationModal({
        type: 'warning',
        icon: 'zap',
        title: '⚡ Activation en masse',
        message: `Confirmer l'activation de tous les utilisateurs en attente ?`,
        details: `
            <div class="text-warning">
                <i data-feather="users" class="icon-xs mr-1"></i>
                <strong>${inactiveCount} utilisateur(s) seront activés</strong><br>
                <small>• Tous les comptes inactifs deviendront actifs<br>
                • Ces utilisateurs pourront se connecter immédiatement<br>
                • Des notifications seront envoyées</small>
            </div>
        `,
        confirmText: `Activer ${inactiveCount} utilisateur(s)`,
        confirmClass: 'btn-warning',
        onConfirm: () => executeBulkAction('activate')
    });
}

/**
 * Afficher la modal de suppression en masse
 */
function showBulkDeleteModal() {
    const selectedUsers = document.querySelectorAll('.user-checkbox:checked');

    if (selectedUsers.length === 0) {
        showToast('Attention', 'Aucun utilisateur sélectionné pour la suppression', 'warning');
        return;
    }

    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '🗑️ Suppression en masse',
        message: `Confirmer la suppression de ${selectedUsers.length} utilisateur(s) sélectionné(s) ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>⚠️ ATTENTION : Cette action est irréversible !</strong><br>
                <small>• ${selectedUsers.length} compte(s) seront supprimés définitivement<br>
                • Toutes les données associées seront perdues<br>
                • Cette opération ne peut pas être annulée</small>
            </div>
        `,
        confirmText: `Supprimer ${selectedUsers.length} utilisateur(s)`,
        confirmClass: 'btn-danger',
        onConfirm: () => executeBulkDelete()
    });
}

// ==================================================================================== 
// EXÉCUTION DES ACTIONS
// ==================================================================================== 

/**
 * Exécuter une action sur un utilisateur
 */
function executeUserAction(userId, action, userName) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        let url, method = 'POST';

        switch(action) {
            case 'activate':
                url = `/admin/users/${userId}/activate`;
                break;
            case 'suspend':
                url = `/admin/users/${userId}/suspend`;
                break;
            case 'reactivate':
                url = `/admin/users/${userId}/activate`;
                break;
            case 'delete':
                url = `/admin/users/${userId}`;
                method = 'DELETE';
                break;
            default:
                showToast('Erreur', 'Action non reconnue', 'error');
                hideLoading();
                return;
        }

        fetch(url, {
            method: method,
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
                showToast('Succès', data.message || `Action ${action} effectuée sur ${userName}`, 'success');

                // Rafraîchir la liste
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast('Erreur', data.message || `Erreur lors de l'action ${action}`, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Erreur:', error);
            showToast('Erreur', `Erreur lors de l'action ${action}`, 'error');
        });
    }, 1500);
}

/**
 * Exécuter une action groupée
 */
function executeBulkAction(action) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        fetch(`/admin/users/bulk-${action}`, {
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
                showToast('Succès', data.message || `Action ${action} en masse effectuée`, 'success');

                // Rafraîchir la page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast('Erreur', data.message || `Erreur lors de l'action ${action} en masse`, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Erreur:', error);
            showToast('Erreur', `Erreur lors de l'action ${action} en masse`, 'error');
        });
    }, 1500);
}

/**
 * Exécuter la suppression en masse
 */
function executeBulkDelete() {
    const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);

    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        fetch('/admin/users/bulk-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_ids: selectedUsers })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                showToast('Succès', data.message || `${selectedUsers.length} utilisateur(s) supprimé(s)`, 'success');

                // Rafraîchir la page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast('Erreur', data.message || 'Erreur lors de la suppression en masse', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Erreur:', error);
            showToast('Erreur', 'Erreur lors de la suppression en masse', 'error');
        });
    }, 1500);
}

// ==================================================================================== 
// MODAL DÉTAILS UTILISATEUR
// ==================================================================================== 

/**
 * Afficher les détails d'un utilisateur
 */
function showUserDetails(userId) {
    currentUserId = userId;

    // Afficher la modal
    $('#userDetailsModal').modal('show');

    // Afficher l'état de chargement
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('userDetailsContentMain').style.display = 'none';

    // Charger les données utilisateur
    setTimeout(() => {
        loadUserDetails(userId);
    }, 500);
}

/**
 * ✅ CORRIGÉ : Charger les détails d'un utilisateur avec toutes les données
 */
function loadUserDetails(userId) {
    fetch(`/admin/users/${userId}/details`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateUserDetails(data.user);

            // Masquer le chargement et afficher le contenu
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('userDetailsContentMain').style.display = 'block';
        } else {
            showToast('Erreur', data.message || 'Erreur lors du chargement des détails', 'error');
            $('#userDetailsModal').modal('hide');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur', 'Erreur lors du chargement des détails', 'error');
        $('#userDetailsModal').modal('hide');
    });
}

/**
 * ✅ CORRIGÉ : Remplir les détails de l'utilisateur dans la modal avec toutes les données
 */
function populateUserDetails(user) {
    console.log('✅ Remplissage des détails utilisateur:', user);

    // En-tête
    document.getElementById('modalUserName').textContent = `${user.type_emoji || '👤'} ${user.username || 'Utilisateur'}`;
    document.getElementById('modalUserRole').textContent = user.type || 'Type utilisateur';

    // Informations générales
    document.getElementById('userFullName').textContent = user.username || 'N/A';
    document.getElementById('userEmail').textContent = user.email || 'N/A';

    // Badge de type et statut
    const typeBadge = document.getElementById('userTypeBadge');
    const statusBadge = document.getElementById('userStatusBadgeText');

    typeBadge.textContent = user.type || 'Non défini'; // ✅ CORRIGÉ
    typeBadge.className = `badge badge-${user.type_badge_color || 'secondary'}`;

    statusBadge.textContent = user.status || 'Non défini'; // ✅ CORRIGÉ
    statusBadge.className = `badge badge-${user.status_badge_color || 'secondary'}`;

    // Détails dans les onglets - ✅ CORRIGÉ : Toutes les données
    document.getElementById('detailUserName').textContent = user.username || 'N/A';
    document.getElementById('detailUserId').textContent = `#${user.id}`;
    document.getElementById('detailUserType').textContent = user.type || 'Non défini'; // ✅ CORRIGÉ
    document.getElementById('detailUserStatus').textContent = user.status || 'Non défini'; // ✅ CORRIGÉ

    // ✅ CORRIGÉ : Informations temporelles avec formatage correct
    document.getElementById('detailCreatedAt').textContent = user.created_at || 'Non disponible';
    document.getElementById('detailUpdatedAt').textContent = user.updated_at || 'Non disponible';
    document.getElementById('detailAccountAge').textContent = user.account_age_formatted || 'Calcul impossible';
    document.getElementById('userRegistrationDate').textContent = formatDateSimple(user.created_at_iso) || 'Non disponible';

    // Contact - ✅ CORRIGÉ
    document.getElementById('detailEmail').textContent = user.email || 'N/A';
    document.getElementById('detailPhone').textContent = user.mobile_number || 'N/A';
    document.getElementById('detailCompany').textContent = user.company || 'Non renseigné'; // ✅ CORRIGÉ

    // Liens contact
    document.getElementById('detailEmailLink').href = `mailto:${user.email}`;
    document.getElementById('detailPhoneLink').href = `tel:${user.mobile_number}`;

    // ✅ CORRIGÉ : Sécurité avec vraies données
    document.getElementById('detailLastLogin').textContent = user.last_login_at || 'Jamais connecté';
    document.getElementById('detailPasswordChanged').textContent = user.last_password_change || 'A la création'; // ✅ CORRIGÉ
    const attemptsElement = document.getElementById('detailLoginAttempts');
    if (attemptsElement) {
        const attempts = user.failed_login_attempts || 0;
        attemptsElement.innerHTML = `<span class="badge badge-${attempts > 0 ? 'warning' : 'light'}">${attempts} échec(s) récent(s)</span>`;
    }

    // Activité
    document.getElementById('activityCreationDate').textContent = user.created_at || 'Non disponible';

    // Mise à jour du timestamp
    document.getElementById('modalLastUpdate').textContent = new Date().toLocaleString('fr-FR');

    // Régénérer les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    console.log('✅ Détails utilisateur remplis avec succès');
}

// ==================================================================================== 
// FONCTIONS UTILITAIRES
// ==================================================================================== 

/**
 * Obtenir le texte du type d'utilisateur
 */
function getUserTypeText(typeId) {
    const types = {
        1: 'Administrateur',
        2: 'Écran',
        3: 'Accueil',
        4: 'Conseiller'
    };
    return types[typeId] || 'Utilisateur';
}

/**
 * Obtenir la couleur du type d'utilisateur
 */
function getUserTypeColor(typeId) {
    const colors = {
        1: 'primary',
        2: 'info',
        3: 'warning',
        4: 'success'
    };
    return colors[typeId] || 'secondary';
}

/**
 * Obtenir le texte du statut utilisateur
 */
function getUserStatusText(statusId) {
    const statuses = {
        1: 'En attente',
        2: 'Actif',
        3: 'Suspendu'
    };
    return statuses[statusId] || 'Inconnu';
}

/**
 * Obtenir la couleur du statut utilisateur
 */
function getUserStatusColor(statusId) {
    const colors = {
        1: 'warning',
        2: 'success',
        3: 'danger'
    };
    return colors[statusId] || 'secondary';
}

/**
 *  Calculer l'âge du compte avec gestion d'erreurs
 */
function getAccountAge(createdDate) {
    try {
        const now = new Date();
        const created = new Date(createdDate);

        if (isNaN(created.getTime())) {
            return 'Date invalide';
        }
        
        const diff = now - created;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (days < 1) return 'Moins d\'un jour';
        if (days === 1) return '1 jour';
        if (days < 30) return `${days} jours`;
        if (days < 365) return `${Math.floor(days / 30)} mois`;
        return `${Math.floor(days / 365)} an(s)`;
    } catch (error) {
        console.error('Erreur calcul âge compte:', error);
        return 'Calcul impossible';
    }
}

/**
 * Mettre à jour l'activité de sécurité
 */
function updateSecurityActivity(activity, timestamp) {
    const element = document.getElementById('lastSecurityActivity');
    if (element) {
        element.textContent = `${activity} - ${timestamp}`;
    }
}

/**
 * Actualiser les détails utilisateur
 */
function refreshUserDetails() {
    if (currentUserId) {
        showToast('Info', 'Actualisation des détails...', 'info');
        loadUserDetails(currentUserId);
    }
}

/**
 * Copier l'email de l'utilisateur
 */
function copyUserEmail() {
    const email = document.getElementById('detailEmail').textContent;
    if (email && email !== 'N/A') {
        navigator.clipboard.writeText(email).then(() => {
            showToast('Succès', 'Email copié dans le presse-papier !', 'success');
        }).catch(() => {
            showToast('Erreur', 'Impossible de copier l\'email', 'error');
        });
    }
}

/**
 * Appeler l'utilisateur
 */
function callUser() {
    const phone = document.getElementById('detailPhone').textContent;
    if (phone && phone !== 'N/A') {
        window.location.href = `tel:${phone}`;
    } else {
        showToast('Erreur', 'Aucun numéro de téléphone disponible', 'error');
    }
}

/**
 * Envoyer un message à l'utilisateur
 */
function sendMessage() {
    showToast('Info', 'Fonctionnalité de messagerie en cours de développement', 'info');
}

// ==================================================================================== 
// SYSTÈME DE FILTRES ET RECHERCHE
// ==================================================================================== 

/**
 * Filtrer par statut depuis les cartes statistiques
 */
function filterByStatus(status) {
    const statusSelect = document.getElementById('status');
    const cards = document.querySelectorAll('.clickable-card');

    // Retirer la sélection de toutes les cartes
    cards.forEach(card => card.classList.remove('card-selected'));

    // Ajouter la sélection à la carte cliquée
    event.currentTarget.classList.add('card-selected');

    // Définir la valeur du select selon le statut
    switch(status) {
        case 'active':
            statusSelect.value = 'active';
            break;
        case 'inactive':
            statusSelect.value = 'inactive';
            break;
        case 'all':
        default:
            statusSelect.value = '';
            break;
    }

    // Appliquer les filtres
    applyFilters();
}

/**
 * Filtrer par type depuis les cartes statistiques
 */
function filterByType(type) {
    const typeSelect = document.getElementById('type');
    const cards = document.querySelectorAll('.clickable-card');

    // Retirer la sélection de toutes les cartes
    cards.forEach(card => card.classList.remove('card-selected'));

    // Ajouter la sélection à la carte cliquée
    event.currentTarget.classList.add('card-selected');

    // Définir la valeur du select selon le type
    typeSelect.value = type;

    // Appliquer les filtres
    applyFilters();
}

/**
 * Appliquer les filtres
 */
function applyFilters() {
    const form = document.getElementById('filterForm');
    if (form) {
        form.submit();
    }
}

/**
 * Réinitialiser les filtres
 */
function resetFilters() {
    const form = document.getElementById('filterForm');
    if (form) {
        // Vider tous les champs
        form.querySelectorAll('input, select').forEach(field => {
            field.value = '';
        });

        // Retirer la sélection des cartes
        document.querySelectorAll('.clickable-card').forEach(card => {
            card.classList.remove('card-selected');
        });

        // Soumettre le formulaire vide
        form.submit();
    }
}

/**
 * Recherche en temps réel
 */
function liveSearch() {
    const query = document.getElementById('search').value;

    // Annuler le timeout précédent
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // Programmer une nouvelle recherche
    searchTimeout = setTimeout(() => {
        if (query.length >= 2 || query.length === 0) {
            // Soumettre le formulaire de recherche
            document.getElementById('filterForm').submit();
        }
    }, 500);
}

/**
 * Recherche rapide dans l'en-tête
 */
function quickSearchUsers() {
    const query = document.getElementById('quickSearch').value;

    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        if (query.length >= 2) {
            // Rediriger avec la recherche
            window.location.href = `${window.location.pathname}?search=${encodeURIComponent(query)}`;
        }
    }, 300);
}

/**
 * Vider la recherche rapide
 */
function clearQuickSearch() {
    document.getElementById('quickSearch').value = '';
    window.location.href = window.location.pathname;
}

/**
 * Vérifier les filtres actifs
 */
function checkActiveFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilters = urlParams.has('search') || urlParams.has('status') || urlParams.has('type');

    if (hasFilters) {
        // Marquer visuellement qu'il y a des filtres actifs
        const filterButton = document.querySelector('button[type="submit"]');
        if (filterButton) {
            filterButton.classList.add('filter-active');
        }
    }
}

// ==================================================================================== 
// GESTION DES SÉLECTIONS
// ==================================================================================== 

/**
 * Initialiser la gestion des sélections
 */
function initializeSelectionHandlers() {
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', toggleSelectAll);
    }

    // Initialiser les checkboxes individuelles
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', handleIndividualCheckbox);
    });
}

/**
 * Basculer la sélection de tous les utilisateurs
 */
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectAllBtn = document.getElementById('selectAllBtn');

    isSelectAllActive = selectAllCheckbox ? selectAllCheckbox.checked : !isSelectAllActive;

    userCheckboxes.forEach(checkbox => {
        checkbox.checked = isSelectAllActive;

        // Animation visuelle
        const row = checkbox.closest('tr');
        if (row) {
            if (isSelectAllActive) {
                row.classList.add('highlight-change');
            } else {
                row.classList.remove('highlight-change');
            }
        }
    });

    // Mettre à jour l'icône du bouton
    if (selectAllBtn) {
        const icon = selectAllBtn.querySelector('i');
        if (icon) {
            icon.setAttribute('data-feather', isSelectAllActive ? 'check-square' : 'square');
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    }

    // Mettre à jour le statut de sélection
    updateSelectionStatus();
}

/**
 * Gérer les checkboxes individuelles
 */
function handleIndividualCheckbox() {
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const selectAllCheckbox = document.getElementById('selectAll');

    // Mettre à jour l'état du "Sélectionner tout"
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedCheckboxes.length === userCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < userCheckboxes.length;
    }

    // Animation sur la ligne
    const row = event.target.closest('tr');
    if (row) {
        if (event.target.checked) {
            row.classList.add('highlight-change');
        } else {
            row.classList.remove('highlight-change');
        }
    }

    updateSelectionStatus();
}

/**
 * Mettre à jour le statut de sélection
 */
function updateSelectionStatus() {
    const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;

    // Mettre à jour les boutons d'action selon la sélection
    const bulkDeleteBtn = document.querySelector('button[onclick="showBulkDeleteModal()"]');
    if (bulkDeleteBtn) {
        if (checkedCount > 0) {
            bulkDeleteBtn.classList.remove('btn-outline-danger');
            bulkDeleteBtn.classList.add('btn-danger');
            bulkDeleteBtn.title = `Supprimer ${checkedCount} utilisateur(s) sélectionné(s)`;
        } else {
            bulkDeleteBtn.classList.remove('btn-danger');
            bulkDeleteBtn.classList.add('btn-outline-danger');
            bulkDeleteBtn.title = 'Supprimer sélectionnés';
        }
    }
}

// ==================================================================================== 
// MISE À JOUR TEMPS RÉEL
// ==================================================================================== 

/**
 * Démarrer les mises à jour temps réel
 */
function startRealTimeUpdates() {
    // Mettre à jour les statistiques toutes les 30 secondes
    realTimeInterval = setInterval(() => {
        refreshStats();
    }, 30000);
}

/**
 * Arrêter les mises à jour temps réel
 */
function stopRealTimeUpdates() {
    if (realTimeInterval) {
        clearInterval(realTimeInterval);
        realTimeInterval = null;
    }
}

/**
 * Actualiser les statistiques
 */
function refreshStats() {
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        // Animation de rotation sur l'icône
        const icon = refreshBtn.querySelector('i');
        if (icon) {
            icon.style.animation = 'spin 1s linear infinite';
        }

        // Simuler une mise à jour des stats
        setTimeout(() => {
            if (icon) {
                icon.style.animation = '';
            }

            // Mettre à jour le timestamp
            lastUpdateTimestamp = Date.now();
        }, 1000);
    }
}

/**
 * Actualiser la liste des utilisateurs
 */
function refreshUsersList() {
    showToast('Info', 'Actualisation de la liste...', 'info');
    window.location.reload();
}

/**
 * Exporter les utilisateurs
 */
function exportUsers() {
    showToast('Info', 'Export en cours...', 'info');

    // Simuler un export
    setTimeout(() => {
        showToast('Succès', 'Export terminé !', 'success');

        // Créer un lien de téléchargement factice
        const link = document.createElement('a');
        link.href = '/admin/users/export';
        link.download = `utilisateurs_${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }, 2000);
}

// ==================================================================================== 
// SYSTÈME DE NOTIFICATIONS TOAST
// ==================================================================================== 

/**
 * Afficher une notification toast
 */
function showToast(title, message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;

    const toastId = 'toast_' + Date.now();
    const icons = {
        success: 'check-circle',
        error: 'x-circle',
        warning: 'alert-triangle',
        info: 'info'
    };

    const colors = {
        success: 'success',
        error: 'danger',
        warning: 'warning',
        info: 'primary'
    };

    const toastHTML = `
        <div class="toast fade show" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
            <div class="toast-header bg-${colors[type]} text-white">
                <i data-feather="${icons[type]}" class="icon-sm mr-2"></i>
                <strong class="mr-auto">${title}</strong>
                <small class="text-white-50">${new Date().toLocaleTimeString('fr-FR')}</small>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>`;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    // Initialiser le toast Bootstrap
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    // Régénérer les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        if (toastElement && toastElement.parentNode) {
            toastElement.parentNode.removeChild(toastElement);
        }
    }, 6000);
}

/**
 * Afficher un overlay de chargement
 */
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    const tableView = document.getElementById('tableView');

    if (overlay && tableView) {
        overlay.style.display = 'block';
        tableView.style.display = 'none';
    }
}

/**
 *  Formater une date en français avec gestion d'erreurs
 */
function formatDateFr(dateString) {
    if (!dateString) return 'Non disponible';

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Date invalide';

        return date.toLocaleString('fr-FR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    } catch (error) {
        console.error('Erreur formatage date:', error);
        return 'Format invalide';
    }
}

/**
 * Formater une date simple avec gestion d'erreurs
 */
function formatDateSimple(dateString) {
    if (!dateString) return 'Non disponible';

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Date invalide';

        return date.toLocaleDateString('fr-FR');
    } catch (error) {
        console.error('Erreur formatage date simple:', error);
        return 'Format invalide';
    }
}

/**
 *  Calculer le temps relatif avec gestion d'erreurs
 */
function getRelativeTime(dateString) {
    if (!dateString) return 'Inconnu';

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Date invalide';

        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor(diff / (1000 * 60));

        if (minutes < 1) return 'À l\'instant';
        if (minutes < 60) return `Il y a ${minutes} minute(s)`;
        if (hours < 24) return `Il y a ${hours} heure(s)`;
        if (days < 30) return `Il y a ${days} jour(s)`;
        if (days < 365) return `Il y a ${Math.floor(days / 30)} mois`;
        return `Il y a ${Math.floor(days / 365)} an(s)`;
    } catch (error) {
        console.error('Erreur temps relatif:', error);
        return 'Calcul impossible';
    }
}

/**
 * Masquer l'overlay de chargement
 */
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    const tableView = document.getElementById('tableView');

    if (overlay && tableView) {
        overlay.style.display = 'none';
        tableView.style.display = 'block';
    }
}

// ==================================================================================== 
// GESTION DES ERREURS ET CLEANUP
// ==================================================================================== 

/**
 * Nettoyage avant fermeture de page
 */
window.addEventListener('beforeunload', function() {
    stopRealTimeUpdates();
});

/**
 * Gestion des erreurs globales
 */
window.addEventListener('error', function(event) {
    console.error('Erreur JavaScript:', event.error);
    showToast('Erreur', 'Une erreur inattendue s\'est produite', 'error');
});

/**
 * Support pour les anciennes versions de navigateurs
 */
if (!window.fetch) {
    showToast('Attention', 'Votre navigateur n\'est pas entièrement supporté. Veuillez le mettre à jour.', 'warning');
}

// Initialisation finale
console.log(' Système de gestion des utilisateurs initialisé avec succès');
</script>

@endsection