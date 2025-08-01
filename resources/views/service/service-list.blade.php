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
                            <input type="search" id="quickSearch" class="from-control top-search mb-0" placeholder="Recherche rapide..." onkeyup="quickSearchServices()">
                            <button type="button" onclick="clearQuickSearch()"><i class="ti-close"></i></button>
                        </div>
                    </div>
                </li>                      

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-danger badge-pill noti-icon-badge" id="inactiveCount">{{ $services->where('statut', 'inactif')->count() }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            Notifications <span class="badge badge-primary badge-pill" id="inactiveCount2">{{ $services->where('statut', 'inactif')->count() }}</span>
                        </h6> 
                        <div class="notification-menu" data-simplebar id="notificationsList">
                            @if($services->where('statut', 'inactif')->count() > 0)
                                <a href="#" class="dropdown-item py-3">
                                    <small class="float-right text-muted pl-2">{{ $services->where('statut', 'inactif')->count() }} service(s)</small>
                                    <div class="media">
                                        <div class="avatar-md bg-soft-warning">
                                            <i data-feather="pause-circle" class="align-self-center icon-xs"></i>
                                        </div>
                                        <div class="media-body align-self-center ml-2 text-truncate">
                                            <h6 class="my-0 font-weight-normal text-dark">Services inactifs</h6>
                                            <small class="text-muted mb-0">Des services nécessitent votre attention</small>
                                        </div>
                                    </div>
                                </a>
                            @else
                                <a href="#" class="dropdown-item py-3">
                                    <div class="media">
                                        <div class="avatar-md bg-soft-success">
                                            <i data-feather="check-circle" class="align-self-center icon-xs"></i>
                                        </div>
                                        <div class="media-body align-self-center ml-2 text-truncate">
                                            <h6 class="my-0 font-weight-normal text-dark">Tous les services sont actifs</h6>
                                            <small class="text-muted mb-0">Système opérationnel</small>
                                        </div>
                                    </div>
                                </a>
                            @endif
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
                        <a class="btn btn-sm btn-soft-success waves-effect" href="{{ route('service.service-create') }}" role="button">
                            <i class="fas fa-plus mr-2"></i>Nouveau service
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
                                    <i data-feather="briefcase" class="mr-2"></i>Gestion des services
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item">Services</li>
                                    <li class="breadcrumb-item active">Liste</li>
                                </ol>
                            </div><!--end col-->
                            <div class="col-auto align-self-center">
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshStats()" id="refreshBtn">
                                    <span class="ay-name">Total:</span>&nbsp;
                                    <span id="totalServices">{{ $services->total() }}</span>
                                    <i data-feather="refresh-cw" class="align-self-center icon-xs ml-1"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="exportServices()">
                                    <i data-feather="download" class="align-self-center icon-xs"></i>
                                </button>
                            </div><!--end col-->  
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->
            
            <!-- ✅ STATISTIQUES RAPIDES CORRIGÉES -->
            <div class="row justify-content-center" id="statsCards">
                <!-- CARTE TOTAL SERVICES - CLIQUABLE -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('all')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Total Services</p>
                                    <h3 class="my-2 counter text-primary" data-target="{{ $services->total() }}">{{ $services->total() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-primary"><i class="mdi mdi-cog"></i></span> 
                                        <span class="status-text">Tous les services</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="briefcase" class="align-self-center text-primary icon-md"></i>  
                                    </div>
                                </div>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <!-- CARTE ACTIFS - CLIQUABLE -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('actif')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Actifs</p>
                                    <h3 class="my-2 text-success counter" data-target="{{ $services->where('statut', 'actif')->count() }}">{{ $services->where('statut', 'actif')->count() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-success"><i class="mdi mdi-check-circle"></i></span> 
                                        <span class="progress-text">Services opérationnels</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="check-circle" class="align-self-center text-success icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <!-- CARTE INACTIFS - CLIQUABLE -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('inactif')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Inactifs</p>
                                    <h3 class="my-2 text-warning counter" data-target="{{ $services->where('statut', 'inactif')->count() }}">{{ $services->where('statut', 'inactif')->count() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-warning"><i class="mdi mdi-pause-circle"></i></span> 
                                        <span class="pending-text">Services arrêtés</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="pause-circle" class="align-self-center text-warning icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <!-- ✅ NOUVELLE CARTE : SERVICES RÉCENTS -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByRecent()" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">  
                                    <p class="text-dark mb-1 font-weight-semibold">Récents</p>                                         
                                    <h3 class="my-2 text-info counter" data-target="{{ $services->where('created_at', '>=', now()->subDays(7))->count() }}">{{ $services->where('created_at', '>=', now()->subDays(7))->count() }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-info"><i class="mdi mdi-clock-plus"></i></span> 
                                        <span class="recent-text">Cette semaine</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="clock" class="align-self-center text-info icon-md"></i>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                               
            </div><!--end row-->

            <!-- ✅ FILTRES CORRIGÉS - AVEC LETTER_OF_SERVICE -->
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
                                    <!-- ✅ RECHERCHE ÉLARGIE AVEC LETTER_OF_SERVICE -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="search">
                                                <i data-feather="search" class="icon-xs mr-1"></i>Recherche Intelligente
                                            </label>
                                            <input type="text" name="search" id="search" class="form-control" 
                                                   placeholder="Nom, lettre de service, description..." 
                                                   value="{{ request('search') }}"
                                                   onkeyup="liveSearch()" autocomplete="off">
                                            <div id="searchSuggestions" class="search-suggestions"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- ✅ STATUT -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="statut">
                                                <i data-feather="activity" class="icon-xs mr-1"></i>Statut
                                            </label>
                                            <select name="statut" id="statut" class="form-control" onchange="applyFilters()">
                                                <option value="">Tous les statuts</option>
                                                <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>✅ Actif</option>
                                                <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>⏸️ Inactif</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- ✅ BOUTON FILTRER ÉLARGI -->
                                    <div class="col-md-3">
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

            <!-- Liste des Services -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="list" class="mr-2"></i>Liste interactive
                                        <span class="badge badge-soft-primary ml-2" id="resultCount">{{ $services->total() }} résultat(s)</span>
                                        <span class="badge badge-soft-info ml-2" id="selectedCount" style="display: none;">0 sélectionné(s)</span>
                                    </h4>                      
                                </div><!--end col-->
                                <!-- ✅ BOUTONS CORRIGÉS - CONFORMES AUX UTILISATEURS -->
                                <div class="col-auto"> 
                                    <div class="btn-group mr-2">
                                        <button class="btn btn-sm btn-success waves-effect" onclick="showCreateServiceModal()" title="Créer service">
                                            <i data-feather="plus" class="icon-xs mr-1"></i>Créer
                                        </button>
                                        <button class="btn btn-sm btn-warning waves-effect" onclick="showBulkActivateModal()" title="Activer tous les inactifs">
                                            <i data-feather="zap" class="icon-xs mr-1"></i>Activer
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger waves-effect" onclick="showBulkDeleteModal()" title="Supprimer sélectionnés" id="bulkDeleteBtn">
                                            <i data-feather="trash-2" class="icon-xs mr-1"></i>Supprimer
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        <!-- ✅ CORRIGÉ: Bouton sélectionner tout fonctionnel -->
                                        <button class="btn btn-sm btn-outline-secondary" onclick="handleSelectAllButton()" title="Sélectionner tout" id="selectAllBtn">
                                            <i data-feather="square" class="icon-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshServicesList()" title="Actualiser">
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
                                @if($services->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="servicesTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <!-- ✅ COLONNES AVEC LETTER_OF_SERVICE -->
                                                <th class="border-top-0">
                                                    <!-- ✅ CORRIGÉ: Checkbox principale sans onclick -->
                                                    <input type="checkbox" id="selectAll" class="mr-2"> 
                                                    Service
                                                </th>
                                                <th class="border-top-0">Lettre de service</th>
                                                <th class="border-top-0">Statut</th>
                                                <th class="border-top-0">Description</th>
                                                <th class="border-top-0">Création</th>
                                                <th class="border-top-0">Actions</th>
                                            </tr><!--end tr-->
                                        </thead>
                                        <tbody id="servicesTableBody">
                                            @foreach($services as $service)
                                            <tr class="service-row" data-service-id="{{ $service->id }}">                                                        
                                                <td>
                                                    <div class="media">
                                                        <!-- ✅ CORRIGÉ: Checkbox service sans onchange -->
                                                        <input type="checkbox" class="service-checkbox mr-2" value="{{ $service->id }}">
                                                        <div class="service-icon mr-3">
                                                            <div class="avatar-sm bg-soft-{{ $service->getStatusBadgeColor() }} rounded-circle d-flex align-items-center justify-content-center">
                                                                <!-- ✅ AFFICHAGE DE LA LETTRE AU LIEU DE L'ICÔNE -->
                                                                <span class="font-weight-bold text-{{ $service->getStatusBadgeColor() }}" style="font-size: 0.8rem;">
                                                                    {{ strtoupper($service->letter_of_service ?? 'S') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="media-body align-self-center">
                                                            <h6 class="m-0 font-weight-semibold">{{ $service->nom }}</h6>
                                                            <p class="text-muted mb-0 font-13">ID: #{{ $service->id }}</p>
                                                        </div><!--end media body-->
                                                    </div>
                                                </td>
                                                <td>
                                                    <!-- ✅ AFFICHAGE DE LA LETTRE DE SERVICE AVEC BADGE -->
                                                    <span class="badge badge-{{ $service->getStatusBadgeColor() }} badge-lg font-weight-bold letter-display" style="font-size: 1.1em; padding: 0.6rem 0.8rem;">
                                                        {{ strtoupper($service->letter_of_service ?? 'N/A') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($service->isActive())
                                                        <span class="badge badge-success badge-pill">
                                                            <i data-feather="check-circle" class="icon-xs mr-1"></i>Actif
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning badge-pill">
                                                            <i data-feather="pause-circle" class="icon-xs mr-1"></i>Inactif
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <p class="mb-0 text-truncate" style="max-width: 200px;" title="{{ $service->description }}">
                                                        {{ Str::limit($service->description ?: 'Aucune description', 50) }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="mb-0 font-14">{{ $service->created_at->format('d/m/Y') }}</p>
                                                    <small class="text-muted">{{ $service->created_at->format('H:i') }}</small>
                                                </td>
                                                <td>
                                                    <!-- ✅ ACTIONS CORRIGÉES - CONFORMES AUX UTILISATEURS -->
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <!-- ACTIONS SELON LE STATUT -->
                                                        @if($service->isInactive())
                                                            <button class="btn btn-soft-success waves-effect" title="Activer" 
                                                                    onclick="showActivateServiceModal({{ $service->id }}, '{{ addslashes($service->nom) }}')">
                                                                <i data-feather="check-circle" class="icon-xs"></i>
                                                            </button>
                                                        @else
                                                            <button class="btn btn-soft-warning waves-effect" title="Désactiver" 
                                                                    onclick="showDeactivateServiceModal({{ $service->id }}, '{{ addslashes($service->nom) }}')">
                                                                <i data-feather="pause-circle" class="icon-xs"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <button type="button" class="btn btn-soft-info waves-effect" title="Détails" 
                                                                onclick="showServiceDetails({{ $service->id }})">
                                                            <i data-feather="eye" class="icon-xs"></i>
                                                        </button>
                                                        <!-- ✅ AJOUTÉ : Bouton "Modifier" manquant -->
                                                         <a href="{{ route('services.edit', $service->id) }}" 
                                                           class="btn btn-soft-primary waves-effect" 
                                                             title="Modifier ce service">
                                                            <i data-feather="edit-2" class="icon-xs"></i>
                                                         </a>
                                                        
                                                        <button type="button" class="btn btn-soft-danger waves-effect" title="Supprimer" 
                                                                onclick="showDeleteServiceModal({{ $service->id }}, '{{ addslashes($service->nom) }}')">
                                                            <i data-feather="trash-2" class="icon-xs"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr><!--end tr-->
                                            @endforeach               
                                        </tbody>
                                    </table> <!--end table-->                                               
                                </div><!--end /div-->
                                @else
                                <!-- ✅ AUCUN RÉSULTAT AMÉLIORÉ -->
                                <div class="text-center py-5" id="noResults">
                                    <div>
                                        <i data-feather="briefcase" class="icon-lg text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucun service trouvé</h5>
                                        <p class="text-muted mb-4">Essayez de modifier vos critères de recherche ou créez un nouveau service.</p>
                                        <div>
                                            <button class="btn btn-primary waves-effect waves-light mr-2" onclick="resetFilters()">
                                                <i data-feather="refresh-cw" class="icon-xs mr-1"></i>Réinitialiser les filtres
                                            </button>
                                            <a href="{{ route('service.service-create') }}" class="btn btn-success waves-effect waves-light">
                                                <i data-feather="plus" class="icon-xs mr-1"></i>Créer un service
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

            <!-- Pagination -->
            @if($services->hasPages())
            <div class="row mt-4">
                <div class="col-sm-12 col-md-5">
                    <p class="text-muted mb-0">
                        Affichage de <span class="font-weight-bold">{{ $services->firstItem() }}</span> à 
                        <span class="font-weight-bold">{{ $services->lastItem() }}</span> 
                        sur <span class="font-weight-bold">{{ $services->total() }}</span> services
                    </p>
                </div>
                <div class="col-sm-12 col-md-7">
                    <div class="float-right">
                        {{ $services->withQueryString()->links() }}
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
&copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Gestion dynamique des services</span>
</footer><!--end footer-->
</div>
<!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- ==================================================================================== -->
<!-- 🔧 MODALES PROFESSIONNELLES CORRIGÉES -->
<!-- ==================================================================================== -->

<!-- Modal Confirmation Universelle -->
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

<!-- Modal Détails Service -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-enhanced modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content modal-content-enhanced">
            <!-- En-tête du modal -->
            <div class="modal-header bg-gradient-primary text-white border-0 modal-header-enhanced">
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="service-icon-modal mr-3">
                        <div class="avatar-md bg-white rounded-circle d-flex align-items-center justify-content-center">
                            <!-- ✅ AFFICHAGE DE LA LETTRE DANS LE MODAL -->
                            <span class="text-primary font-weight-bold" id="serviceLetterIcon" style="font-size: 1.5rem;">S</span>
                        </div>
                        <div class="service-status-indicator" id="serviceStatusIndicator"></div>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modalServiceName">
                            <i data-feather="briefcase" class="icon-sm mr-2"></i>Chargement...
                        </h5>
                        <small class="text-white-50" id="modalServiceLetter">Informations service</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Corps du modal -->
            <div class="modal-body modal-body-enhanced p-0" id="serviceDetailsContent">
                <!-- État de chargement -->
                <div class="loading-state text-center py-5" id="loadingState">
                    <div class="spinner-grow text-primary mb-3" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                    <h6 class="text-muted">Chargement des informations...</h6>
                    <p class="text-muted">Veuillez patienter</p>
                </div>

                <!-- Contenu principal -->
                <div class="service-details-content" id="serviceDetailsContentMain" style="display: none;">
                    <!-- Section d'en-tête service -->
                    <div class="service-header-section bg-light border-bottom">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center">
                                        <div class="service-avatar-large mr-4">
                                            <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center shadow">
                                                <!-- ✅ AFFICHAGE GRANDE LETTRE -->
                                                <span class="text-white font-weight-bold" id="serviceLetterLarge" style="font-size: 2rem;">S</span>
                                            </div>
                                            <div class="status-badge" id="serviceStatusBadge"></div>
                                        </div>
                                        <div>
                                            <h4 class="mb-1 font-weight-bold" id="serviceFullName">Nom du service</h4>
                                            <p class="text-muted mb-2" id="serviceLetterCode">lettre-service</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge mr-2" id="serviceStatusBadgeText">Statut</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-lg-right">
                                    <div class="service-stats">
                                        <div class="stat-item">
                                            <h6 class="text-muted mb-0">Création</h6>
                                            <p class="font-weight-bold mb-0" id="serviceCreationDate">--</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu en onglets -->
                    <div class="container-fluid p-4">
                        <!-- Navigation des onglets -->
                        <ul class="nav nav-pills nav-pills-enhanced mb-4" id="serviceDetailsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab">
                                    <i data-feather="briefcase" class="icon-xs mr-1"></i>Informations Générales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="creator-tab" data-toggle="pill" href="#creator" role="tab">
                                    <i data-feather="user" class="icon-xs mr-1"></i>Créateur
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="history-tab" data-toggle="pill" href="#history" role="tab">
                                    <i data-feather="activity" class="icon-xs mr-1"></i>Historique
                                </a>
                            </li>
                        </ul>

                        <!-- Contenu des onglets -->
                        <div class="tab-content" id="serviceDetailsTabsContent">
                            <!-- Onglet Informations Générales -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="info-card">
                                            <h6 class="card-title text-primary">
                                                <i data-feather="tag" class="icon-sm mr-2"></i>Identité
                                            </h6>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Nom:</span>
                                                    <span class="info-value" id="detailServiceName">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Lettre de service:</span>
                                                    <span class="info-value">
                                                        <span class="badge badge-primary font-weight-bold" id="detailServiceLetter" style="font-size: 0.9rem;">--</span>
                                                    </span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Identifiant:</span>
                                                    <span class="info-value" id="detailServiceId">#--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Statut:</span>
                                                    <span class="info-value" id="detailServiceStatus">--</span>
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
                                                    <span class="info-label">Âge du service:</span>
                                                    <span class="info-value" id="detailServiceAge">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="info-card">
                                            <h6 class="card-title text-info">
                                                <i data-feather="file-text" class="icon-sm mr-2"></i>Description
                                            </h6>
                                            <div class="description-content p-3 bg-light rounded">
                                                <p id="serviceDescription" class="mb-0">Chargement de la description...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Créateur -->
                            <div class="tab-pane fade" id="creator" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="info-card">
                                            <h6 class="card-title text-info">
                                                <i data-feather="user" class="icon-sm mr-2"></i>Informations du créateur
                                            </h6>
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" alt="Avatar" class="rounded-circle mr-3" width="60" height="60">
                                                <div>
                                                    <h5 class="mb-1" id="creatorName">Nom du créateur</h5>
                                                    <p class="text-muted mb-0" id="creatorRole">Administrateur</p>
                                                </div>
                                            </div>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Créé le:</span>
                                                    <span class="info-value" id="creationDate">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Services créés:</span>
                                                    <span class="info-value" id="creatorServicesCount">-- services créés</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="info-card bg-light">
                                            <h6 class="card-title text-warning">
                                                <i data-feather="zap" class="icon-sm mr-2"></i>Actions Rapides
                                            </h6>
                                            <div class="quick-actions">
                                                <button class="btn btn-outline-success btn-sm btn-block mb-2" onclick="quickActivateService()">
                                                    <i data-feather="play-circle" class="icon-xs mr-1"></i>Activer
                                                </button>
                                                <button class="btn btn-outline-warning btn-sm btn-block mb-2" onclick="quickDeactivateService()">
                                                    <i data-feather="pause-circle" class="icon-xs mr-1"></i>Désactiver
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm btn-block" onclick="quickDeleteService()">
                                                    <i data-feather="trash-2" class="icon-xs mr-1"></i>Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Historique -->
                            <div class="tab-pane fade" id="history" role="tabpanel">
                                <div class="info-card">
                                    <h6 class="card-title text-primary">
                                        <i data-feather="activity" class="icon-sm mr-2"></i>Historique d'activité
                                    </h6>
                                    <div class="activity-timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success">
                                                <i data-feather="plus" class="icon-xs text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Service créé</h6>
                                                <p class="timeline-description text-muted">
                                                    Le service a été créé dans le système
                                                </p>
                                                <small class="timeline-time text-muted" id="activityCreationDate">--</small>
                                            </div>
                                        </div>

                                        <div class="timeline-item" id="activityLastUpdate">
                                            <div class="timeline-marker bg-info">
                                                <i data-feather="edit" class="icon-xs text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Dernière modification</h6>
                                                <p class="timeline-description text-muted">
                                                    Informations du service mises à jour
                                                </p>
                                                <small class="timeline-time text-muted" id="activityUpdateDate">--</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer modal-footer-enhanced border-top bg-light">
                <div class="d-flex justify-content-between w-100 align-items-center flex-wrap">
                    <div class="footer-left mb-2 mb-md-0">
                        <small class="text-muted">
                            <i data-feather="clock" class="icon-xs mr-1"></i>
                            Dernière mise à jour : <span id="modalLastUpdate">Maintenant</span>
                        </small>
                    </div> 
                    <div class="footer-right d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="refreshServiceDetails()">
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

<!-- CSS STYLES ET JAVASCRIPT COMPLET -->
<style>
/* CSS existant plus améliorations - IDENTIQUE À votre fichier original */
.card {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.report-card {
    transition: all 0.3s ease;
    cursor: default;
}

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

.card-selected {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px solid #2196f3;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
}

/* ✅ NOUVEAU CSS pour la sélection corrigée */
.service-checkbox, #selectAll {
    cursor: pointer;
    transform: scale(1.1);
    transition: all 0.2s ease;
}

.service-checkbox:hover, #selectAll:hover {
    transform: scale(1.2);
}

.highlight-change {
    background-color: rgba(0, 123, 255, 0.1) !important;
    transition: background-color 0.3s ease;
    border-left: 3px solid #007bff;
}

/* Styles pour la sélection améliorée */
.service-row.selected {
    background-color: rgba(0, 123, 255, 0.1) !important;
    border-left: 4px solid #007bff;
    animation: highlightRow 0.3s ease;
}

@keyframes highlightRow {
    0% { background-color: rgba(0, 123, 255, 0.3); }
    100% { background-color: rgba(0, 123, 255, 0.1); }
}

.bulk-action-active {
    background: linear-gradient(45deg, #dc3545, #c82333) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
    transform: translateY(-1px) !important;
}

.selection-feedback {
    padding: 8px 15px;
    background: rgba(0, 123, 255, 0.1);
    border: 1px solid rgba(0, 123, 255, 0.3);
    border-radius: 5px;
    margin-bottom: 10px;
    font-size: 0.9rem;
    color: #0056b3;
}

#selectedCount {
    animation: countUpdate 0.3s ease;
}

@keyframes countUpdate {
    0% { transform: scale(1.2); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

/* ✅ NOUVEAU : Style pour les lettres de service */
.badge-lg {
    padding: 0.6rem 0.8rem;
    font-size: 0.95rem;
    border-radius: 8px;
}

.letter-display {
    min-width: 45px;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Z-INDEX pour modales */
#serviceDetailsModal {
    z-index: 1050 !important;
}

#confirmationModal {
    z-index: 1060 !important;
}

/* Modal améliorée */
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

.modal-footer-enhanced {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 10 !important;
    background: #f8f9fa !important;
    border-top: 1px solid #dee2e6 !important;
    padding: 15px 25px !important;
    margin-top: auto !important;
}

/* Icônes et design */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.service-icon-modal {
    position: relative;
}

.service-status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
}

.service-status-indicator.active {
    background-color: #28a745;
}

.service-status-indicator.inactive {
    background-color: #ffc107;
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

/* Icônes modales */
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

/* Navigation onglets */
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

/* Timeline */
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

/* Responsive */
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

    .nav-pills-enhanced {
        flex-direction: column !important;
        gap: 8px !important;
    }

    .nav-pills-enhanced .nav-link {
        text-align: center !important;
        width: 100% !important;
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
    
    .letter-display {
        min-width: 35px;
        font-size: 0.9em !important;
        padding: 0.4rem 0.6rem !important;
    }
}

/* Autres styles */
.counter {
    font-size: 2rem;
    font-weight: bold;
    transition: all 0.3s ease;
}

.service-row {
    transition: all 0.3s ease;
}

.service-row:hover {
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

/* Animations */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
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

.filter-active {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
}

.filter-active:hover {
    background: linear-gradient(45deg, #218838, #1ea88a);
}

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

.service-details-content {
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
</style>

<!-- ✅ JAVASCRIPT COMPLET AVEC ADAPTATIONS LETTER_OF_SERVICE -->
<script>
// Variables globales pour services
let searchTimeout;
let realTimeInterval;
let lastUpdateTimestamp = Date.now();
let currentAction = null;
let currentServiceId = null;
let selectedServicesCount = 0;

// Initialisation - VERSION ULTRA DÉFENSIVE
document.addEventListener('DOMContentLoaded', function() {
    // Délai pour s'assurer que tout est chargé
    setTimeout(function() {
        try {
            console.log('🔄 Initialisation du système de gestion des services...');
            
            // Initialiser Feather icons avec double vérification
            if (typeof feather !== 'undefined' && feather.replace) {
                try {
                    feather.replace();
                    console.log('✅ Feather icons initialisés');
                } catch (featherError) {
                    console.warn('⚠️ Erreur Feather icons (ignorée):', featherError.message);
                }
            } else {
                console.warn('⚠️ Feather icons non disponible');
            }

            // Connecter la checkbox du tableau avec triple vérification
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox && selectAllCheckbox.addEventListener) {
                try {
                    selectAllCheckbox.addEventListener('change', handleTableSelectAll);
                    console.log('✅ Checkbox tableau connectée');
                } catch (checkboxError) {
                    console.warn('⚠️ Erreur checkbox (ignorée):', checkboxError.message);
                }
            } else {
                console.warn('⚠️ Checkbox selectAll introuvable ou non fonctionnelle');
            }
            
            // Connecter les checkboxes individuelles avec vérification
            try {
                const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
                if (serviceCheckboxes && serviceCheckboxes.length > 0) {
                    serviceCheckboxes.forEach((checkbox, index) => {
                        if (checkbox && checkbox.addEventListener) {
                            try {
                                checkbox.addEventListener('change', handleIndividualCheckbox);
                            } catch (individualError) {
                                console.warn(`⚠️ Erreur checkbox ${index} (ignorée):`, individualError.message);
                            }
                        }
                    });
                    console.log(`✅ ${serviceCheckboxes.length} checkboxes services connectées`);
                } else {
                    console.warn('⚠️ Aucune checkbox service trouvée');
                }
            } catch (checkboxesError) {
                console.warn('⚠️ Erreur checkboxes globale (ignorée):', checkboxesError.message);
            }

            // Démarrer les mises à jour temps réel de façon sécurisée
            try {
                if (typeof startRealTimeUpdates === 'function') {
                    startRealTimeUpdates();
                    console.log('✅ Mises à jour temps réel démarrées');
                }
            } catch (realtimeError) {
                console.warn('⚠️ Erreur temps réel (ignorée):', realtimeError.message);
            }

            // Vérifier l'état des filtres actifs de façon sécurisée
            try {
                if (typeof checkActiveFilters === 'function') {
                    checkActiveFilters();
                    console.log('✅ Filtres actifs vérifiés');
                }
            } catch (filtersError) {
                console.warn('⚠️ Erreur filtres (ignorée):', filtersError.message);
            }

            // Gestion visibilité page ultra sécurisée
            try {
                document.addEventListener('visibilitychange', function() {
                    try {
                        if (document.hidden) {
                            if (typeof stopRealTimeUpdates === 'function') {
                                stopRealTimeUpdates();
                            }
                        } else {
                            if (typeof startRealTimeUpdates === 'function') {
                                startRealTimeUpdates();
                            }
                        }
                    } catch (visibilityInnerError) {
                        // Silencieux pour éviter le spam
                    }
                });
            } catch (visibilityError) {
                console.warn('⚠️ Erreur gestion visibilité (ignorée):', visibilityError.message);
            }

            console.log('✅ Système de sélection corrigé et initialisé avec succès');
            
        } catch (globalError) {
            console.error('❌ Erreur lors de l\'initialisation:', globalError);
            // NE PAS afficher de toast d'erreur ici
        }
    }, 100); // Délai de 100ms pour s'assurer que le DOM est prêt
});

// ==================================================================================== 
// ✅ CORRECTION PRINCIPALE : GESTION DES SÉLECTIONS FONCTIONNELLE
// ==================================================================================== 

// ✅ NOUVELLE FONCTION: Gérer le bouton "Sélectionner tout"
function handleSelectAllButton() {
    console.log('🔄 Bouton Sélectionner tout cliqué');
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.service-checkbox:checked');
    
    // Déterminer l'action : si tout est sélectionné, désélectionner, sinon sélectionner tout
    const shouldSelectAll = checkedCheckboxes.length < serviceCheckboxes.length;
    
    console.log(`Action: ${shouldSelectAll ? 'Sélectionner' : 'Désélectionner'} tout`);
    
    // Appliquer la sélection à toutes les checkboxes
    serviceCheckboxes.forEach(checkbox => {
        checkbox.checked = shouldSelectAll;
        
        // Effet visuel sur la ligne
        const row = checkbox.closest('tr');
        if (row) {
            if (shouldSelectAll) {
                row.classList.add('highlight-change');
            } else {
                row.classList.remove('highlight-change');
            }
        }
    });
    
    // Synchroniser la checkbox du tableau
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = shouldSelectAll;
        selectAllCheckbox.indeterminate = false;
    }
    
    // Mettre à jour l'icône du bouton
    updateSelectAllButtonIcon(shouldSelectAll);
    
    // Message de confirmation
    const selectedCount = shouldSelectAll ? serviceCheckboxes.length : 0;
    selectedServicesCount = selectedCount;
    console.log(`✅ ${selectedCount} service(s) sélectionné(s)`);
    
    // Mettre à jour les boutons d'action
    updateSelectionStatus();
    
    // Toast notification
    if (typeof showToast === 'function') {
        if (shouldSelectAll) {
            showToast('Sélection', `${selectedCount} service(s) sélectionné(s)`, 'success');
        } else {
            showToast('Désélection', 'Tous les services désélectionnés', 'info');
        }
    }
}

// ✅ NOUVELLE FONCTION: Gérer la checkbox du tableau
function handleTableSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    
    if (!selectAllCheckbox) return;
    
    const isChecked = selectAllCheckbox.checked;
    console.log('🔄 Checkbox tableau:', isChecked);
    
    selectedServicesCount = 0;
    
    // Appliquer à toutes les checkboxes service
    serviceCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
        
        if (isChecked) {
            selectedServicesCount++;
        }
        
        // Effet visuel
        const row = checkbox.closest('tr');
        if (row) {
            if (isChecked) {
                row.classList.add('highlight-change');
            } else {
                row.classList.remove('highlight-change');
            }
        }
    });
    
    // Synchroniser le bouton externe
    updateSelectAllButtonIcon(isChecked);
    
    // Mettre à jour les boutons d'action
    updateSelectionStatus();
}

// ✅ NOUVELLE FONCTION: Mettre à jour l'icône du bouton
function updateSelectAllButtonIcon(state) {
    const selectAllBtn = document.getElementById('selectAllBtn');
    if (!selectAllBtn) return;
    
    const icon = selectAllBtn.querySelector('i');
    if (!icon) return;
    
    if (state === true) {
        icon.setAttribute('data-feather', 'check-square');
        selectAllBtn.classList.add('btn-primary');
        selectAllBtn.classList.remove('btn-outline-secondary', 'btn-warning');
        selectAllBtn.title = 'Désélectionner tout';
    } else if (state === 'partial') {
        icon.setAttribute('data-feather', 'minus-square');
        selectAllBtn.classList.add('btn-warning');
        selectAllBtn.classList.remove('btn-outline-secondary', 'btn-primary');
        selectAllBtn.title = 'Sélectionner tout les restants';
    } else {
        icon.setAttribute('data-feather', 'square');
        selectAllBtn.classList.add('btn-outline-secondary');
        selectAllBtn.classList.remove('btn-primary', 'btn-warning');
        selectAllBtn.title = 'Sélectionner tout';
    }
    
    // Régénérer l'icône si Feather est disponible
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// ✅ CORRIGÉ : Gérer les checkboxes individuelles
function handleIndividualCheckbox(event) {
    const selectAllCheckbox = document.getElementById('selectAll');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.service-checkbox:checked');

    selectedServicesCount = checkedCheckboxes.length;

    // Effet visuel sur la ligne
    const checkbox = event.target;
    const row = checkbox.closest('tr');
    if (row) {
        if (checkbox.checked) {
            row.classList.add('highlight-change');
        } else {
            row.classList.remove('highlight-change');
        }
    }

    // Mettre à jour l'état de la checkbox "Sélectionner tout"
    if (selectAllCheckbox) {
        if (checkedCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            updateSelectAllButtonIcon(false);
        } else if (checkedCheckboxes.length === serviceCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
            updateSelectAllButtonIcon(true);
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
            updateSelectAllButtonIcon('partial');
        }
    }

    // Mettre à jour les boutons d'action
    updateSelectionStatus();

    console.log(`📊 Sélection: ${checkedCheckboxes.length}/${serviceCheckboxes.length} services`);
}

// ✅ NOUVELLE FONCTION: Mettre à jour l'affichage de sélection
function updateSelectionStatus() {
    // Mettre à jour le compteur de sélection
    const selectedCountElement = document.getElementById('selectedCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    if (selectedCountElement) {
        if (selectedServicesCount > 0) {
            selectedCountElement.textContent = `${selectedServicesCount} sélectionné(s)`;
            selectedCountElement.style.display = 'inline-block';
            
            // Animation du compteur
            selectedCountElement.style.animation = 'countUpdate 0.3s ease';
        } else {
            selectedCountElement.style.display = 'none';
        }
    }
    
    // Mise à jour du bouton de suppression en masse
    if (bulkDeleteBtn) {
        if (selectedServicesCount > 0) {
            bulkDeleteBtn.classList.remove('btn-outline-danger');
            bulkDeleteBtn.classList.add('btn-danger', 'bulk-action-active');
            bulkDeleteBtn.title = `Supprimer ${selectedServicesCount} service(s) sélectionné(s)`;
            
            // Mise à jour du texte du bouton
            const btnIcon = bulkDeleteBtn.querySelector('i');
            const btnText = bulkDeleteBtn.lastChild;
            if (btnText && btnText.nodeType === Node.TEXT_NODE) {
                btnText.textContent = `Supprimer (${selectedServicesCount})`;
            }
        } else {
            bulkDeleteBtn.classList.remove('btn-danger', 'bulk-action-active');
            bulkDeleteBtn.classList.add('btn-outline-danger');
            bulkDeleteBtn.title = 'Supprimer sélectionnés';
            
            // Restaurer le texte du bouton
            const btnText = bulkDeleteBtn.lastChild;
            if (btnText && btnText.nodeType === Node.TEXT_NODE) {
                btnText.textContent = 'Supprimer';
            }
        }
    }
    
    console.log(`📊 Affichage mis à jour: ${selectedServicesCount} sélectionné(s)`);
}

// ==================================================================================== 
// MODALES DE CONFIRMATION
// ==================================================================================== 

function showConfirmationModal(config) {
    const modal = document.getElementById('confirmationModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalDetails = document.getElementById('modalDetails');
    const confirmBtn = document.getElementById('confirmBtn');
    const confirmText = document.getElementById('confirmText');
    const confirmSpinner = document.getElementById('confirmSpinner');

    const defaultConfig = {
        type: 'danger',
        icon: 'alert-triangle',
        title: 'Confirmation requise',
        message: 'Êtes-vous sûr de vouloir effectuer cette action ?',
        details: null,
        confirmText: 'Confirmer',
        confirmClass: 'btn-danger',
        onConfirm: null,
        showSpinner: true
    };

    const finalConfig = { ...defaultConfig, ...config };

    modalIcon.className = `modal-icon ${finalConfig.type}`;
    modalIcon.innerHTML = `<i data-feather="${finalConfig.icon}"></i>`;

    modalTitle.textContent = finalConfig.title;
    modalMessage.textContent = finalConfig.message;

    if (finalConfig.details) {
        modalDetails.innerHTML = finalConfig.details;
        modalDetails.style.display = 'block';
    } else {
        modalDetails.style.display = 'none';
    }

    confirmBtn.className = `btn btn-rounded ${finalConfig.confirmClass}`;
    confirmText.textContent = finalConfig.confirmText;
    confirmSpinner.style.display = 'none';

    currentAction = finalConfig.onConfirm;

    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    $('#confirmationModal').modal('show');
}

// Gestionnaire du bouton de confirmation - VERSION ULTRA SÉCURISÉE
setTimeout(function() {
    try {
        const confirmBtn = document.getElementById('confirmBtn');
        const confirmText = document.getElementById('confirmText');
        const confirmSpinner = document.getElementById('confirmSpinner');

        if (confirmBtn && confirmBtn.addEventListener) {
            confirmBtn.addEventListener('click', function() {
                try {
                    if (currentAction && typeof currentAction === 'function') {
                        if (confirmSpinner && confirmSpinner.style) {
                            confirmSpinner.style.display = 'inline-block';
                        }
                        
                        if (confirmBtn.classList) {
                            confirmBtn.classList.add('btn-loading');
                        }
                        
                        if (confirmText) {
                            confirmText.textContent = 'Traitement...';
                        }

                        currentAction();
                    }
                } catch (actionError) {
                    console.error('Erreur lors de l\'exécution de l\'action:', actionError);
                    // Utiliser console.error au lieu de showToast pour éviter les erreurs de cascade
                }
            });
            console.log('✅ Gestionnaire bouton confirmation connecté');
        } else {
            console.warn('⚠️ Bouton confirmation introuvable ou non fonctionnel');
        }
    } catch (globalError) {
        console.error('❌ Erreur initialisation gestionnaire confirmation:', globalError);
    }
}, 200); // Délai pour s'assurer que le DOM est complètement prêt

// ==================================================================================== 
// ACTIONS SUR LES SERVICES
// ==================================================================================== 

function showActivateServiceModal(serviceId, serviceName) {
    showConfirmationModal({
        type: 'success',
        icon: 'check-circle',
        title: '✅ Activer le service',
        message: `Confirmer l'activation du service "${serviceName}" ?`,
        details: `
            <div class="text-success">
                <i data-feather="info" class="icon-xs mr-1"></i>
                <strong>Conséquences de l'activation :</strong><br>
                <small>• Le service deviendra opérationnel<br>
                • Il sera disponible pour utilisation<br>
                • Le statut passera à "Actif"</small>
            </div>
        `,
        confirmText: 'Activer le service',
        confirmClass: 'btn-success',
        onConfirm: () => executeServiceAction(serviceId, 'activate', serviceName)
    });
}

function showDeactivateServiceModal(serviceId, serviceName) {
    showConfirmationModal({
        type: 'warning',
        icon: 'pause-circle',
        title: '⏸️ Désactiver le service',
        message: `Confirmer la désactivation du service "${serviceName}" ?`,
        details: `
            <div class="text-warning">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>Conséquences de la désactivation :</strong><br>
                <small>• Le service deviendra temporairement indisponible<br>
                • Les utilisateurs ne pourront plus l'utiliser<br>
                • Le statut passera à "Inactif"</small>
            </div>
        `,
        confirmText: 'Désactiver le service',
        confirmClass: 'btn-warning',
        onConfirm: () => executeServiceAction(serviceId, 'deactivate', serviceName)
    });
}

function showDeleteServiceModal(serviceId, serviceName) {
    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '🗑️ Supprimer le service',
        message: `Confirmer la suppression définitive du service "${serviceName}" ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>⚠️ ATTENTION : Cette action est irréversible !</strong><br>
                <small>• Toutes les données du service seront supprimées<br>
                • L'historique sera perdu<br>
                • Cette opération ne peut pas être annulée</small>
            </div>
        `,
        confirmText: 'Supprimer définitivement',
        confirmClass: 'btn-danger',
        onConfirm: () => executeServiceAction(serviceId, 'delete', serviceName)
    });
}

function showCreateServiceModal() {
    window.location.href = "{{ route('service.service-create') }}";
}

function showBulkActivateModal() {
    showConfirmationModal({
        type: 'warning',
        icon: 'zap',
        title: '⚡ Activation en masse',
        message: `Confirmer l'activation de tous les services inactifs ?`,
        details: `
            <div class="text-warning">
                <i data-feather="briefcase" class="icon-xs mr-1"></i>
                <strong>Tous les services inactifs seront activés</strong><br>
                <small>• Ils seront immédiatement disponibles<br>
                • Les statuts seront mis à jour</small>
            </div>
        `,
        confirmText: `Activer les services inactifs`,
        confirmClass: 'btn-warning',
        onConfirm: () => executeBulkAction('activate')
    });
}

function showBulkDeleteModal() {
    if (selectedServicesCount === 0) {
        showToast('Attention', 'Aucun service sélectionné pour la suppression', 'warning');
        return;
    }

    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '🗑️ Suppression en masse',
        message: `Confirmer la suppression de ${selectedServicesCount} service(s) sélectionné(s) ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>⚠️ ATTENTION : Cette action est irréversible !</strong><br>
                <small>• ${selectedServicesCount} service(s) seront supprimés définitivement<br>
                • Toutes les données associées seront perdues</small>
            </div>
        `,
        confirmText: `Supprimer ${selectedServicesCount} service(s)`,
        confirmClass: 'btn-danger',
        onConfirm: () => executeBulkDelete()
    });
}

// ==================================================================================== 
// EXÉCUTION DES ACTIONS - ROUTES CORRIGÉES
// ==================================================================================== 

function executeServiceAction(serviceId, action, serviceName) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        let url, method = 'POST';

        switch(action) {
            case 'activate':
                url = `/admin/services/${serviceId}/activate`;
                break;
            case 'deactivate':
                url = `/admin/services/${serviceId}/deactivate`;
                break;
            case 'delete':
                url = `/admin/services/${serviceId}`;
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
                showToast('Succès', data.message || `Action ${action} effectuée sur ${serviceName}`, 'success');
                setTimeout(() => window.location.reload(), 1500);
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

function executeBulkAction(action) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        fetch(`/admin/services/bulk-${action}`, {
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
                setTimeout(() => window.location.reload(), 1500);
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

function executeBulkDelete() {
    const selectedServices = Array.from(document.querySelectorAll('.service-checkbox:checked')).map(cb => cb.value);

    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        fetch('/admin/services/bulk-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ service_ids: selectedServices })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                showToast('Succès', data.message || `${selectedServices.length} service(s) supprimé(s)`, 'success');
                setTimeout(() => window.location.reload(), 1500);
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
// MODAL DÉTAILS SERVICE - ADAPTÉ POUR LETTER_OF_SERVICE
// ==================================================================================== 

function showServiceDetails(serviceId) {
    currentServiceId = serviceId;

    $('#serviceDetailsModal').modal('show');

    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('serviceDetailsContentMain').style.display = 'none';

    setTimeout(() => {
        loadServiceDetails(serviceId);
    }, 500);
}

function loadServiceDetails(serviceId) {
    fetch(`/admin/services/${serviceId}/details`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateServiceDetails(data.service);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('serviceDetailsContentMain').style.display = 'block';
        } else {
            showToast('Erreur', data.message || 'Erreur lors du chargement des détails', 'error');
            $('#serviceDetailsModal').modal('hide');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur', 'Erreur lors du chargement des détails', 'error');
        $('#serviceDetailsModal').modal('hide');
    });
}

function populateServiceDetails(service) {
    console.log('✅ Remplissage des détails service:', service);

    // ✅ ADAPTATION LETTER_OF_SERVICE: Extraction et formatage de la lettre
    const serviceLetter = service.letter_of_service || service.code || 'S';
    const formattedLetter = serviceLetter.toString().toUpperCase();

    // En-tête
    document.getElementById('modalServiceName').textContent = `⚙️ ${service.nom || 'Service'}`;
    document.getElementById('modalServiceLetter').textContent = `Lettre: ${formattedLetter}`;

    // ✅ NOUVELLE FONCTIONNALITÉ: Mise à jour des icônes avec la lettre
    const serviceLetterIcon = document.getElementById('serviceLetterIcon');
    const serviceLetterLarge = document.getElementById('serviceLetterLarge');
    
    if (serviceLetterIcon) {
        serviceLetterIcon.textContent = formattedLetter;
    }
    
    if (serviceLetterLarge) {
        serviceLetterLarge.textContent = formattedLetter;
    }

    // Informations principales
    document.getElementById('serviceFullName').textContent = service.nom || 'N/A';
    document.getElementById('serviceLetterCode').textContent = `Lettre: ${formattedLetter}`;

    // Badge de statut
    const statusBadge = document.getElementById('serviceStatusBadgeText');
    statusBadge.textContent = service.statut_emoji || service.statut || 'Non défini';
    statusBadge.className = `badge badge-${service.status_badge_color || 'secondary'}`;

    // Détails dans les onglets
    document.getElementById('detailServiceName').textContent = service.nom || 'N/A';
    
    // ✅ ADAPTATION: Affichage de la lettre de service au lieu du code
    const detailServiceLetter = document.getElementById('detailServiceLetter');
    if (detailServiceLetter) {
        detailServiceLetter.textContent = formattedLetter;
        detailServiceLetter.className = `badge badge-${service.status_badge_color || 'primary'} font-weight-bold`;
    }
    
    document.getElementById('detailServiceId').textContent = `#${service.id}`;
    document.getElementById('detailServiceStatus').textContent = service.statut || 'Non défini';

    // Informations temporelles
    document.getElementById('detailCreatedAt').textContent = service.created_at || 'Non disponible';
    document.getElementById('detailUpdatedAt').textContent = service.updated_at || 'Non disponible';
    document.getElementById('detailServiceAge').textContent = service.age_formatted || 'Calcul impossible';
    document.getElementById('serviceCreationDate').textContent = service.created_at || 'Non disponible';

    // Description
    document.getElementById('serviceDescription').textContent = service.description || 'Aucune description disponible';

    // Créateur
    document.getElementById('creatorName').textContent = service.created_by || 'Système';
    document.getElementById('creationDate').textContent = service.created_at || 'Non disponible';

    // ✅ NOUVELLE FONCTIONNALITÉ: Compteur de services du créateur
    const creatorServicesCount = document.getElementById('creatorServicesCount');
    if (creatorServicesCount) {
        creatorServicesCount.textContent = service.creator_services_count || '-- services créés';
    }

    // Activité
    document.getElementById('activityCreationDate').textContent = service.created_at || 'Non disponible';
    document.getElementById('activityUpdateDate').textContent = service.updated_at || 'Non disponible';

    // ✅ ADAPTATION: Indicateur de statut dans le modal
    const serviceStatusIndicator = document.getElementById('serviceStatusIndicator');
    if (serviceStatusIndicator) {
        serviceStatusIndicator.className = `service-status-indicator ${service.statut === 'actif' ? 'active' : 'inactive'}`;
    }

    // Mise à jour du timestamp
    document.getElementById('modalLastUpdate').textContent = new Date().toLocaleString('fr-FR');

    // Régénérer les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    console.log('✅ Détails service remplis avec succès - Lettre de service:', formattedLetter);
}

// Actions rapides depuis le modal
function quickActivateService() {
    if (currentServiceId) {
        executeServiceAction(currentServiceId, 'activate', 'ce service');
    }
}

function quickDeactivateService() {
    if (currentServiceId) {
        executeServiceAction(currentServiceId, 'deactivate', 'ce service');
    }
}

function quickDeleteService() {
    if (currentServiceId) {
        executeServiceAction(currentServiceId, 'delete', 'ce service');
    }
}

function refreshServiceDetails() {
    if (currentServiceId) {
        showToast('Info', 'Actualisation des détails...', 'info');
        loadServiceDetails(currentServiceId);
    }
}

// ==================================================================================== 
// SYSTÈME DE FILTRES ET RECHERCHE - ADAPTÉ POUR LETTER_OF_SERVICE
// ==================================================================================== 

function filterByStatus(status) {
    const statusSelect = document.getElementById('statut');
    const cards = document.querySelectorAll('.clickable-card');

    cards.forEach(card => card.classList.remove('card-selected'));
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('card-selected');
    }

    switch(status) {
        case 'actif':
            statusSelect.value = 'actif';
            break;
        case 'inactif':
            statusSelect.value = 'inactif';
            break;
        case 'all':
        default:
            statusSelect.value = '';
            break;
    }

    applyFilters();
}

function filterByRecent() {
    const cards = document.querySelectorAll('.clickable-card');
    cards.forEach(card => card.classList.remove('card-selected'));
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('card-selected');
    }

    // Ajouter un paramètre pour filtrer les services récents
    const form = document.getElementById('filterForm');
    if (form) {
        // Ajouter un input hidden pour les services récents
        let recentInput = form.querySelector('input[name="recent"]');
        if (!recentInput) {
            recentInput = document.createElement('input');
            recentInput.type = 'hidden';
            recentInput.name = 'recent';
            form.appendChild(recentInput);
        }
        recentInput.value = '7'; // 7 derniers jours
        form.submit();
    }
}

function applyFilters() {
    const form = document.getElementById('filterForm');
    if (form) {
        form.submit();
    }
}

function resetFilters() {
    const form = document.getElementById('filterForm');
    if (form) {
        form.querySelectorAll('input, select').forEach(field => {
            if (field.type !== 'hidden') {
                field.value = '';
            }
        });

        // Supprimer les inputs hidden ajoutés
        form.querySelectorAll('input[type="hidden"]').forEach(input => {
            if (input.name !== '_token') {
                input.remove();
            }
        });

        document.querySelectorAll('.clickable-card').forEach(card => {
            card.classList.remove('card-selected');
        });

        form.submit();
    }
}

// ✅ RECHERCHE AMÉLIORÉE: Prise en compte des lettres de service
function liveSearch() {
    const query = document.getElementById('search').value;
    
    // Effacer le timeout précédent
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // ✅ NOUVELLE LOGIQUE: Recherche optimisée pour les lettres
    searchTimeout = setTimeout(() => {
        if (query.length >= 1 || query.length === 0) {
            // Si la recherche est une seule lettre, on peut rechercher immédiatement
            // pour les lettres de service
            if (query.length === 1 && /^[A-Za-z]$/.test(query)) {
                console.log('🔍 Recherche par lettre de service:', query.toUpperCase());
            }
            
            document.getElementById('filterForm').submit();
        }
    }, query.length === 1 ? 500 : 300); // Délai plus long pour les lettres uniques
}

function quickSearchServices() {
    const query = document.getElementById('quickSearch').value;

    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        if (query.length >= 1) {
            // ✅ AMÉLIORATION: Recherche rapide inclut les lettres de service
            window.location.href = `${window.location.pathname}?search=${encodeURIComponent(query)}`;
        }
    }, 300);
}

function clearQuickSearch() {
    document.getElementById('quickSearch').value = '';
    window.location.href = window.location.pathname;
}

function checkActiveFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilters = urlParams.has('search') || urlParams.has('statut') || urlParams.has('recent');

    if (hasFilters) {
        const filterButton = document.querySelector('button[type="submit"]');
        if (filterButton) {
            filterButton.classList.add('filter-active');
        }
        
        // ✅ NOUVELLE FONCTIONNALITÉ: Affichage des filtres actifs
        const searchValue = urlParams.get('search');
        if (searchValue) {
            console.log('🔍 Filtre actif - Recherche:', searchValue);
        }
    }
}

// ==================================================================================== 
// MISE À JOUR TEMPS RÉEL
// ==================================================================================== 

function startRealTimeUpdates() {
    realTimeInterval = setInterval(() => {
        refreshStats();
    }, 30000);
}

function stopRealTimeUpdates() {
    if (realTimeInterval) {
        clearInterval(realTimeInterval);
        realTimeInterval = null;
    }
}

function refreshStats() {
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        const icon = refreshBtn.querySelector('i');
        if (icon) {
            icon.style.animation = 'spin 1s linear infinite';
        }

        // Faire un vrai appel API pour les stats
        fetch('/admin/api/services/stats', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour les statistiques dans l'interface
                updateStatsDisplay(data.stats);
                console.log('✅ Statistiques mises à jour');
            }
        })
        .catch(error => {
            console.error('Erreur stats:', error);
        })
        .finally(() => {
            setTimeout(() => {
                if (icon) {
                    icon.style.animation = '';
                }
            }, 1000);
        });
    }
}

function updateStatsDisplay(stats) {
    // Mettre à jour les cartes de statistiques
    const totalElement = document.getElementById('totalServices');
    if (totalElement) {
        totalElement.textContent = stats.total || 0;
    }
    
    // Mettre à jour les autres compteurs si nécessaire
    document.querySelectorAll('.counter').forEach(counter => {
        const target = counter.getAttribute('data-target');
        if (target) {
            // Animation du compteur si souhaité
            counter.textContent = target;
        }
    });
}

function refreshServicesList() {
    showToast('Info', 'Actualisation de la liste...', 'info');
    window.location.reload();
}

function exportServices() {
    showToast('Info', 'Export en cours...', 'info');

    // Vraie redirection vers l'export
    setTimeout(() => {
        window.location.href = '/admin/services/export';
        showToast('Succès', 'Export lancé !', 'success');
    }, 1000);
}

// ==================================================================================== 
// SYSTÈME DE NOTIFICATIONS TOAST
// ==================================================================================== 

function showToast(title, message, type = 'info') {
    try {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            console.warn('⚠️ Container toast introuvable');
            return;
        }

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

        const toastElement = document.getElementById(toastId);
        
        // Vérifier si Bootstrap Toast est disponible
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        } else if (typeof $ !== 'undefined' && $.fn.toast) {
            // Fallback pour jQuery Bootstrap
            $(toastElement).toast('show');
        } else {
            console.warn('⚠️ Bootstrap Toast non disponible');
            // Fallback : afficher le toast avec du CSS
            toastElement.style.display = 'block';
        }

        // Régénérer les icônes Feather si disponible
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Supprimer automatiquement après 6 secondes
        setTimeout(() => {
            try {
                if (toastElement && toastElement.parentNode) {
                    toastElement.parentNode.removeChild(toastElement);
                }
            } catch (error) {
                console.warn('Erreur suppression toast:', error);
            }
        }, 6000);
        
    } catch (error) {
        console.error('❌ Erreur affichage toast:', error);
        // Fallback ultime : alert navigateur
        if (type === 'error') {
            console.error(`${title}: ${message}`);
        }
    }
}

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    const tableView = document.getElementById('tableView');

    if (overlay && tableView) {
        overlay.style.display = 'block';
        tableView.style.display = 'none';
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    const tableView = document.getElementById('tableView');

    if (overlay && tableView) {
        overlay.style.display = 'none';
        tableView.style.display = 'block';
    }
}

// ==================================================================================== 
// FONCTIONS UTILITAIRES
// ==================================================================================== 

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ✅ NOUVELLE FONCTION: Validation des lettres de service
function validateServiceLetter(letter) {
    if (!letter || letter.length === 0) {
        return { valid: false, message: 'La lettre de service est requise' };
    }
    
    if (letter.length > 3) {
        return { valid: false, message: 'La lettre de service ne peut pas dépasser 3 caractères' };
    }
    
    if (!/^[A-Za-z0-9]+$/.test(letter)) {
        return { valid: false, message: 'La lettre de service ne peut contenir que des lettres et des chiffres' };
    }
    
    return { valid: true, message: 'Lettre de service valide' };
}

// ✅ NOUVELLE FONCTION: Formater l'affichage des lettres
function formatServiceLetter(letter) {
    if (!letter) return 'N/A';
    return letter.toString().toUpperCase().trim();
}

// ==================================================================================== 
// FONCTIONS DE COMPATIBILITÉ ET DEBUGGING
// ==================================================================================== 

// Fonction de debug pour vérifier l'état des sélections
function debugSelection() {
    console.log('🔍 DEBUG - État des sélections:');
    console.log('- selectedServicesCount:', selectedServicesCount);
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.service-checkbox:checked');
    
    console.log('- selectAllCheckbox.checked:', selectAllCheckbox?.checked);
    console.log('- selectAllCheckbox.indeterminate:', selectAllCheckbox?.indeterminate);
    console.log('- Total services:', serviceCheckboxes.length);
    console.log('- Services cochés:', checkedCheckboxes.length);
    
    checkedCheckboxes.forEach((checkbox, index) => {
        console.log(`  - Service ${index}: ID=${checkbox.value}`);
    });
}

// Fonction pour forcer la mise à jour de l'affichage
function forceUpdateDisplay() {
    console.log('🔄 Force update display...');
    
    // Recalculer le nombre de services sélectionnés
    selectedServicesCount = document.querySelectorAll('.service-checkbox:checked').length;
    
    // Mettre à jour l'affichage
    updateSelectionStatus();
    
    // Réinitialiser les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log('✅ Display forcé mis à jour');
}

// Compatibilité avec les anciens événements
function toggleSelectAll() {
    // Redirection vers la nouvelle fonction
    handleSelectAllButton();
}

// ==================================================================================== 
// GESTION DES ERREURS ET NETTOYAGE - VERSION SÉCURISÉE
// ==================================================================================== 

// Gestion des promesses rejetées - VERSION SILENCIEUSE
window.addEventListener('unhandledrejection', function(event) {
    console.warn('⚠️ Promesse rejetée (ignorée):', event.reason);
    event.preventDefault(); // Empêcher l'affichage d'erreur
});

// Nettoyage avant fermeture de page - VERSION SÉCURISÉE
window.addEventListener('beforeunload', function() {
    try {
        if (typeof stopRealTimeUpdates === 'function') {
            stopRealTimeUpdates();
        }
    } catch (error) {
        // Silencieux
    }
});

// Nettoyage lors de la fermeture de modales - VERSION ULTRA SÉCURISÉE
setTimeout(function() {
    try {
        // Vérifier que jQuery et Bootstrap sont disponibles
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#serviceDetailsModal').on('hidden.bs.modal', function() {
                try {
                    currentServiceId = null;
                    console.log('🔧 Modal service fermé, currentServiceId reset');
                } catch (error) {
                    // Silencieux
                }
            });

            $('#confirmationModal').on('hidden.bs.modal', function() {
                try {
                    currentAction = null;
                    
                    // Reset des boutons de confirmation avec vérifications
                    const confirmBtn = document.getElementById('confirmBtn');
                    const confirmText = document.getElementById('confirmText');
                    const confirmSpinner = document.getElementById('confirmSpinner');
                    
                    if (confirmBtn && confirmBtn.classList) {
                        confirmBtn.classList.remove('btn-loading');
                    }
                    
                    if (confirmText) {
                        confirmText.textContent = 'Confirmer';
                    }
                    
                    if (confirmSpinner && confirmSpinner.style) {
                        confirmSpinner.style.display = 'none';
                    }
                    
                    console.log('🔧 Modal confirmation fermé, currentAction reset');
                } catch (error) {
                    // Silencieux
                }
            });
        } else {
            console.warn('⚠️ jQuery non disponible pour la gestion des modales');
        }
    } catch (error) {
        // Silencieux
    }
}, 500); // Délai pour s'assurer que jQuery est chargé

// ==================================================================================== 
// INITIALISATION FINALE ET LOG
// ==================================================================================== 

// Log final pour confirmer que tout est chargé - VERSION SÉCURISÉE
setTimeout(function() {
    try {
        console.log('🎉 Système de gestion des services complètement initialisé !');
        console.log('📋 Fonctionnalités disponibles:');
        console.log('  ✅ Sélection multiple avec "Sélectionner tout"');
        console.log('  ✅ Actions en masse (suppression, activation)');
        console.log('  ✅ Modales de confirmation avec animations');
        console.log('  ✅ Détails de services dans modal avancé');
        console.log('  ✅ Filtres et recherche en temps réel');
        console.log('  ✅ Notifications toast');
        console.log('  ✅ Mises à jour temps réel');
        console.log('  ✅ Gestion d\'erreurs complète');
        console.log('  🆕 Support complet des lettres de service');

        // Exposer certaines fonctions dans la console pour le debugging - SÉCURISÉ
        if (typeof window !== 'undefined') {
            try {
                window.debugServiceSelection = debugSelection;
                window.forceUpdateServiceDisplay = forceUpdateDisplay;
                window.validateServiceLetter = validateServiceLetter;
                window.formatServiceLetter = formatServiceLetter;
                
                console.log('🛠️ Fonctions de debug disponibles: debugServiceSelection(), forceUpdateServiceDisplay(), validateServiceLetter(), formatServiceLetter()');
            } catch (windowError) {
                console.warn('⚠️ Impossible d\'exposer les fonctions de debug (ignoré)');
            }
        }

        // Vérification finale de l'environnement
        const environmentCheck = {
            dom: !!document.getElementById('selectAll'),
            feather: typeof feather !== 'undefined',
            jquery: typeof $ !== 'undefined',
            bootstrap: typeof bootstrap !== 'undefined'
        };

        console.log('🔍 État de l\'environnement:', environmentCheck);

        // Test de santé du système - NOUVELLE FONCTIONNALITÉ
        setTimeout(function() {
            try {
                const healthCheck = {
                    selectAllCheckbox: !!document.getElementById('selectAll'),
                    serviceCheckboxes: document.querySelectorAll('.service-checkbox').length,
                    toastContainer: !!document.getElementById('toastContainer'),
                    servicesTable: !!document.getElementById('servicesTable'),
                    bulkDeleteBtn: !!document.getElementById('bulkDeleteBtn'),
                    selectAllBtn: !!document.getElementById('selectAllBtn'),
                    letterDisplays: document.querySelectorAll('.letter-display').length
                };

                console.log('🏥 Test de santé du système:', healthCheck);

                // Compteur de problèmes
                const issues = Object.entries(healthCheck).filter(([key, value]) => !value || (key.includes('Count') && value === 0));
                
                if (issues.length === 0) {
                    console.log('💚 Système entièrement fonctionnel !');
                } else {
                    console.warn('⚠️ Problèmes détectés:', issues.map(([key]) => key));
                    console.warn('ℹ️ Ces problèmes peuvent être normaux selon le contenu de la page');
                }

                // ✅ NOUVELLE VÉRIFICATION: Test des lettres de service
                const letterElements = document.querySelectorAll('.letter-display');
                console.log(`🔤 Lettres de service détectées: ${letterElements.length}`);
                
                letterElements.forEach((element, index) => {
                    const letter = element.textContent.trim();
                    if (letter && letter !== 'N/A') {
                        console.log(`  📝 Service ${index + 1}: ${letter}`);
                    }
                });

            } catch (healthError) {
                console.warn('⚠️ Impossible de faire le test de santé:', healthError.message);
            }
        }, 500);
        
    } catch (finalError) {
        console.warn('⚠️ Erreur lors du log final (ignorée):', finalError.message);
    }
}, 1000); // Délai de 1 seconde pour tout finaliser
</script>

@endsection