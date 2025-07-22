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
                            <input type="search" id="quickSearch" class="from-control top-search mb-0" placeholder="Recherche rapide..." onkeyup="quickSearchAgencies()">
                            <button type="button" onclick="clearQuickSearch()"><i class="ti-close"></i></button>
                        </div>
                    </div>
                </li>                      

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="align-self-center topbar-icon"></i>
                        <span class="badge badge-danger badge-pill noti-icon-badge" id="inactiveCount">{{ $stats['inactive'] }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            Notifications <span class="badge badge-primary badge-pill" id="inactiveCount2">{{ $stats['inactive'] }}</span>
                        </h6> 
                        <div class="notification-menu" data-simplebar id="notificationsList">
                            @if($stats['inactive'] > 0)
                            <a href="#" class="dropdown-item py-3" onclick="filterByStatus('inactive')">
                                <small class="float-right text-muted pl-2">Maintenant</small>
                                <div class="media">
                                    <div class="avatar-md bg-soft-warning">
                                        <i data-feather="home" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Agences inactives</h6>
                                        <small class="text-muted mb-0">{{ $stats['inactive'] }} agence(s) à activer</small>
                                    </div>
                                </div>
                            </a>
                            @else
                            <a href="#" class="dropdown-item py-3">
                                <small class="float-right text-muted pl-2">✅</small>
                                <div class="media">
                                    <div class="avatar-md bg-soft-success">
                                        <i data-feather="check" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Tout est à jour</h6>
                                        <small class="text-muted mb-0">Toutes les agences sont actives</small>
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
                        <a class="btn btn-sm btn-soft-success waves-effect" href="{{ route('agency.agence-create') }}" role="button">
                            <i class="fas fa-home mr-2"></i>Nouvelle agence
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
                                    <i data-feather="home" class="mr-2"></i>Gestion des agences
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item">Agences</li>
                                    <li class="breadcrumb-item active">Liste</li>
                                </ol>
                            </div><!--end col-->
                            <div class="col-auto align-self-center">
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshStats()" id="refreshBtn">
                                    <span class="ay-name">Total:</span>&nbsp;
                                    <span id="totalAgencies">{{ $agencies->total() }}</span>
                                    <i data-feather="refresh-cw" class="align-self-center icon-xs ml-1"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="exportAgencies()">
                                    <i data-feather="download" class="align-self-center icon-xs"></i>
                                </button>
                            </div><!--end col-->  
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->
            
            <!-- Statistiques rapides CLIQUABLES -->
            <div class="row justify-content-center" id="statsCards">
                <!-- CARTE TOTAL -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('all')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Total agences</p>
                                    <h3 class="my-2 counter text-primary" data-target="{{ $stats['total'] }}">{{ $stats['total'] }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-primary"><i class="mdi mdi-home-group"></i></span> 
                                        <span class="status-text">Toutes les agences</span>
                                    </p>
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <i data-feather="home" class="align-self-center text-primary icon-md"></i>  
                                    </div>
                                </div>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                
                <!-- CARTE ACTIVES -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('active')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Actives</p>
                                    <h3 class="my-2 text-success counter" data-target="{{ $stats['active'] }}">{{ $stats['active'] }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-success"><i class="mdi mdi-check-circle"></i></span> 
                                        <span class="progress-text">Agences opérationnelles</span>
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
                
                <!-- CARTE INACTIVES -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByStatus('inactive')" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-1 font-weight-semibold">Inactives</p>
                                    <h3 class="my-2 text-warning counter" data-target="{{ $stats['inactive'] }}">{{ $stats['inactive'] }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-warning"><i class="mdi mdi-pause-circle"></i></span> 
                                        <span class="pending-text">À activer</span>
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
                
                <!-- CARTE RÉCENTES -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card clickable-card" onclick="filterByRecent()" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">  
                                    <p class="text-dark mb-1 font-weight-semibold">Récentes</p>                                         
                                    <h3 class="my-2 text-info counter" data-target="{{ $stats['recent'] }}">{{ $stats['recent'] }}</h3>
                                    <p class="mb-0 text-truncate text-muted">
                                        <span class="text-info"><i class="mdi mdi-clock-outline"></i></span> 
                                        <span class="recent-text">7 derniers jours</span>
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="search">
                                                <i data-feather="search" class="icon-xs mr-1"></i>Recherche Intelligente
                                            </label>
                                            <input type="text" name="search" id="search" class="form-control" 
                                                   placeholder="Nom, téléphone, adresse, ville..." 
                                                   value="{{ request('search') }}"
                                                   onkeyup="liveSearch()" autocomplete="off">
                                            <div id="searchSuggestions" class="search-suggestions"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="status">
                                                <i data-feather="activity" class="icon-xs mr-1"></i>Statut
                                            </label>
                                            <select name="status" id="status" class="form-control" onchange="applyFilters()">
                                                <option value="">Tous les statuts</option>
                                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>✅ Active</option>
                                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>⏸️ Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="country">
                                                <i data-feather="globe" class="icon-xs mr-1"></i>Pays
                                            </label>
                                            <select name="country" id="country" class="form-control" onchange="applyFilters()">
                                                <option value="">Tous les pays</option>
                                                <option value="Côte d'Ivoire" {{ request('country') == "Côte d'Ivoire" ? 'selected' : '' }}>🇨🇮 Côte d'Ivoire</option>
                                                <option value="Burkina Faso" {{ request('country') == 'Burkina Faso' ? 'selected' : '' }}>🇧🇫 Burkina Faso</option>
                                                <option value="Mali" {{ request('country') == 'Mali' ? 'selected' : '' }}>🇲🇱 Mali</option>
                                                <option value="Sénégal" {{ request('country') == 'Sénégal' ? 'selected' : '' }}>🇸🇳 Sénégal</option>
                                                <option value="Ghana" {{ request('country') == 'Ghana' ? 'selected' : '' }}>🇬🇭 Ghana</option>
                                                <option value="France" {{ request('country') == 'France' ? 'selected' : '' }}>🇫🇷 France</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="city">
                                                <i data-feather="map" class="icon-xs mr-1"></i>Ville
                                            </label>
                                            <input type="text" name="city" id="city" class="form-control" 
                                                   placeholder="ex: Abidjan, Ouagadougou..." 
                                                   value="{{ request('city') }}"
                                                   onkeyup="liveSearch()">
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

            <!-- Liste des Agences -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="list" class="mr-2"></i>Liste interactive
                                        <span class="badge badge-soft-primary ml-2" id="resultCount">{{ $agencies->total() }} résultat(s)</span>
                                        <!-- ✅ NOUVEAU: Badge de sélection -->
                                        <span class="badge badge-soft-info ml-2" id="selectedCount" style="display: none;">0 sélectionné(s)</span>
                                    </h4>                      
                                </div><!--end col-->
                                <!-- ✅ BOUTONS CORRIGÉS - CONFORMES AUX FICHIERS DE RÉFÉRENCE -->
                                <div class="col-auto"> 
                                    <div class="btn-group mr-2">
                                        <button class="btn btn-sm btn-success waves-effect" onclick="showCreateAgencyModal()" title="Créer agence">
                                            <i data-feather="home" class="icon-xs mr-1"></i>Créer
                                        </button>
                                        <button class="btn btn-sm btn-warning waves-effect" onclick="showBulkActivateModal()" title="Activer toutes les inactives">
                                            <i data-feather="zap" class="icon-xs mr-1"></i>Activer
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger waves-effect" onclick="showBulkDeleteModal()" title="Supprimer sélectionnées" id="bulkDeleteBtn">
                                            <i data-feather="trash-2" class="icon-xs mr-1"></i>Supprimer
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        <!-- ✅ CORRIGÉ: Bouton sélectionner tout fonctionnel -->
                                        <button class="btn btn-sm btn-outline-secondary" onclick="handleSelectAllButton()" title="Sélectionner tout" id="selectAllBtn">
                                            <i data-feather="square" class="icon-xs"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshAgenciesList()" title="Actualiser">
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
                                @if($agencies->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="agenciesTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="border-top-0">
                                                    <!-- ✅ CORRIGÉ: Checkbox principale sans onclick -->
                                                    <input type="checkbox" id="selectAll" class="mr-2"> 
                                                    Agence
                                                </th>
                                                <th class="border-top-0">Contact</th>
                                                <th class="border-top-0">Localisation</th>
                                                <th class="border-top-0">Statut</th>
                                                <th class="border-top-0">Création</th>
                                                <th class="border-top-0">Actions</th>
                                            </tr><!--end tr-->
                                        </thead>
                                        <tbody id="agenciesTableBody">
                                            @foreach($agencies as $index => $agency)
                                            <tr class="agency-row" data-agency-id="{{ $agency->id }}">                                                        
                                                <td>
                                                    <div class="media">
                                                        <!-- ✅ CORRIGÉ: Checkbox agence sans onchange -->
                                                        <input type="checkbox" class="agency-checkbox mr-2" value="{{ $agency->id }}">
                                                        <div class="agency-avatar mr-3">
                                                            <div class="agency-icon text-primary">
                                                                <i data-feather="home" class="icon-xs"></i>
                                                            </div>
                                                        </div>
                                                        <div class="media-body align-self-center">
                                                            <h6 class="m-0 font-weight-semibold">{{ $agency->name }}</h6>
                                                            <p class="text-muted mb-0 font-13">ID: #{{ $agency->id }}</p>
                                                        </div><!--end media body-->
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="mb-0 font-14">
                                                        <i data-feather="phone" class="icon-xs mr-1"></i>
                                                        {{ $agency->phone }}
                                                    </p>
                                                    <small class="text-muted">{{ $agency->address_1 }}</small>
                                                </td>
                                                <td>
                                                    <p class="mb-0 font-14">
                                                        <i data-feather="map-pin" class="icon-xs mr-1"></i>
                                                        {{ $agency->city }}
                                                    </p>
                                                    <small class="text-muted">{{ $agency->country }}</small>
                                                </td>
                                                <td>
                                                    @if($agency->isActive())
                                                        <span class="badge badge-success badge-pill">
                                                            <i data-feather="check-circle" class="icon-xs mr-1"></i>Active
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning badge-pill">
                                                            <i data-feather="pause-circle" class="icon-xs mr-1"></i>Inactive
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <p class="mb-0 font-14">{{ $agency->created_at->format('d/m/Y') }}</p>
                                                    <small class="text-muted">{{ $agency->created_at->format('H:i') }}</small>
                                                </td>
                                                <td>
                                                    <!-- ✅ ACTIONS CORRIGÉES - CONFORMES AUX FICHIERS DE RÉFÉRENCE -->
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <!-- ACTIONS SELON LE STATUT -->
                                                        @if($agency->isInactive())
                                                            <button class="btn btn-soft-success waves-effect" title="Activer" 
                                                                    onclick="showActivateAgencyModal({{ $agency->id }}, '{{ $agency->name }}')">
                                                                <i data-feather="play" class="icon-xs"></i>
                                                            </button>
                                                        @else
                                                            <button class="btn btn-soft-warning waves-effect" title="Désactiver" 
                                                                    onclick="showDeactivateAgencyModal({{ $agency->id }}, '{{ $agency->name }}')">
                                                                <i data-feather="pause" class="icon-xs"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <button type="button" class="btn btn-soft-info waves-effect" title="Détails" 
                                                                onclick="showAgencyDetails({{ $agency->id }})">
                                                            <i data-feather="eye" class="icon-xs"></i>
                                                        </button>
                                                         <!-- ✅ AJOUTÉ : Bouton "Modifier" manquant -->
                                                         <a href="{{ route('agency.agence-edit', $agency->id) }}" 
                                                           class="btn btn-soft-primary waves-effect" 
                                                             title="Modifier ce service">
                                                            <i data-feather="edit-2" class="icon-xs"></i>
                                                         </a>
                                                        
                                                        <button type="button" class="btn btn-soft-danger waves-effect" title="Supprimer" 
                                                                onclick="showDeleteAgencyModal({{ $agency->id }}, '{{ $agency->name }}')">
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
                                        <i data-feather="home" class="icon-lg text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucune agence trouvée</h5>
                                        <p class="text-muted mb-4">Essayez de modifier vos critères de recherche ou créez une nouvelle agence.</p>
                                        <div>
                                            <button class="btn btn-primary waves-effect waves-light mr-2" onclick="resetFilters()">
                                                <i data-feather="refresh-cw" class="icon-xs mr-1"></i>Réinitialiser les filtres
                                            </button>
                                            <a href="{{ route('agency.agence-create') }}" class="btn btn-success waves-effect waves-light">
                                                <i data-feather="plus" class="icon-xs mr-1"></i>Créer une agence
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

            <!-- Pagination -->
            @if($agencies->hasPages())
            <div class="row mt-4">
                <div class="col-sm-12 col-md-5">
                    <p class="text-muted mb-0">
                        Affichage de <span class="font-weight-bold">{{ $agencies->firstItem() }}</span> à 
                        <span class="font-weight-bold">{{ $agencies->lastItem() }}</span> 
                        sur <span class="font-weight-bold">{{ $agencies->total() }}</span> agences
                    </p>
                </div>
                <div class="col-sm-12 col-md-7">
                    <div class="float-right">
                        {{ $agencies->withQueryString()->links() }}
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
&copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Gestion dynamique des agences</span>
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

<!-- Modal Détails Agence -->
<div class="modal fade" id="agencyDetailsModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-enhanced modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content modal-content-enhanced">
            <!-- En-tête du modal -->
            <div class="modal-header bg-gradient-primary text-white border-0 modal-header-enhanced">
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="agency-avatar-modal mr-3">
                        <div class="agency-icon-large bg-white text-primary">
                            <i data-feather="home" class="icon-lg"></i>
                        </div>
                        <div class="agency-status-indicator" id="agencyStatusIndicator"></div>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modalAgencyName">
                            <i data-feather="home" class="icon-xs mr-2"></i>Chargement...
                        </h5>
                        <small class="text-white-50" id="modalAgencyRole">Informations agence</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Corps du modal -->
            <div class="modal-body modal-body-enhanced p-0" id="agencyDetailsContent">
                <!-- État de chargement -->
                <div class="loading-state text-center py-5" id="loadingState">
                    <div class="spinner-grow text-primary mb-3" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                    <h6 class="text-muted">Chargement des informations...</h6>
                    <p class="text-muted">Veuillez patienter</p>
                </div>

                <!-- Contenu principal -->
                <div class="agency-details-content" id="agencyDetailsContentMain" style="display: none;">
                    <!-- Section d'en-tête agence -->
                    <div class="agency-header-section bg-light border-bottom">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center">
                                        <div class="agency-avatar-large mr-4">
                                            <div class="agency-icon-xl bg-primary text-white">
                                                <i data-feather="home" class="icon-xl"></i>
                                            </div>
                                            <div class="status-badge" id="agencyStatusBadge"></div>
                                        </div>
                                        <div>
                                            <h4 class="mb-1 font-weight-bold" id="agencyFullName">Nom agence</h4>
                                            <p class="text-muted mb-2" id="agencyLocation">Localisation</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge mr-2" id="agencyStatusBadgeText">Statut</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-lg-right">
                                    <div class="agency-stats">
                                        <div class="stat-item">
                                            <h6 class="text-muted mb-0">Création</h6>
                                            <p class="font-weight-bold mb-0" id="agencyCreationDate">--</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu en onglets -->
                    <div class="container-fluid p-4">
                        <!-- Navigation des onglets -->
                        <ul class="nav nav-pills nav-pills-enhanced mb-4" id="agencyDetailsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab">
                                    <i data-feather="home" class="icon-xs mr-1"></i>Informations Générales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="location-tab" data-toggle="pill" href="#location" role="tab">
                                    <i data-feather="map-pin" class="icon-xs mr-1"></i>Localisation
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="pill" href="#contact" role="tab">
                                    <i data-feather="phone" class="icon-xs mr-1"></i>Contact
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="activity-tab" data-toggle="pill" href="#activity" role="tab">
                                    <i data-feather="activity" class="icon-xs mr-1"></i>Activité
                                </a>
                            </li>
                        </ul>

                        <!-- Contenu des onglets -->
                        <div class="tab-content" id="agencyDetailsTabsContent">
                            <!-- Onglet Informations Générales -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="info-card">
                                            <h6 class="card-title text-primary">
                                                <i data-feather="home" class="icon-xs mr-2"></i>Identité
                                            </h6>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Nom:</span>
                                                    <span class="info-value" id="detailAgencyName">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Identifiant:</span>
                                                    <span class="info-value" id="detailAgencyId">#--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Statut:</span>
                                                    <span class="info-value" id="detailAgencyStatus">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="info-card">
                                            <h6 class="card-title text-success">
                                                <i data-feather="calendar" class="icon-xs mr-2"></i>Informations Temporelles
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
                                                    <span class="info-label">Âge de l'agence:</span>
                                                    <span class="info-value" id="detailAgencyAge">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Localisation -->
                            <div class="tab-pane fade" id="location" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="info-card">
                                            <h6 class="card-title text-info">
                                                <i data-feather="map-pin" class="icon-xs mr-2"></i>Adresse complète
                                            </h6>
                                            <div class="info-list">
                                                <div class="info-item">
                                                    <span class="info-label">Adresse principale:</span>
                                                    <span class="info-value" id="detailAddress1">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Adresse complémentaire:</span>
                                                    <span class="info-value" id="detailAddress2">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Ville:</span>
                                                    <span class="info-value" id="detailCity">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Pays:</span>
                                                    <span class="info-value" id="detailCountry">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Adresse complète:</span>
                                                    <span class="info-value" id="detailFullAddress">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="info-card bg-light">
                                            <h6 class="card-title text-warning">
                                                <i data-feather="navigation" class="icon-xs mr-2"></i>Actions Rapides
                                            </h6>
                                            <div class="quick-actions">
                                                <button class="btn btn-outline-primary btn-sm btn-block mb-2" onclick="copyAgencyAddress()">
                                                    <i data-feather="copy" class="icon-xs mr-1"></i>Copier Adresse
                                                </button>
                                                <button class="btn btn-outline-success btn-sm btn-block mb-2" onclick="openMap()">
                                                    <i data-feather="map" class="icon-xs mr-1"></i>Voir sur la carte
                                                </button>
                                                <button class="btn btn-outline-info btn-sm btn-block" onclick="getDirections()">
                                                    <i data-feather="navigation" class="icon-xs mr-1"></i>Itinéraire
                                                </button>
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
                                            <h6 class="card-title text-success">
                                                <i data-feather="phone" class="icon-xs mr-2"></i>Informations de contact
                                            </h6>
                                            <div class="info-list">
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
                                                    <span class="info-label">Créé par:</span>
                                                    <span class="info-value" id="detailCreatedBy">--</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">Notes:</span>
                                                    <span class="info-value" id="detailNotes">--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="info-card bg-light">
                                            <h6 class="card-title text-warning">
                                                <i data-feather="message-circle" class="icon-xs mr-2"></i>Actions Contact
                                            </h6>
                                            <div class="quick-actions">
                                                <button class="btn btn-outline-success btn-sm btn-block mb-2" onclick="callAgency()">
                                                    <i data-feather="phone-call" class="icon-xs mr-1"></i>Appeler
                                                </button>
                                                <button class="btn btn-outline-primary btn-sm btn-block mb-2" onclick="copyAgencyPhone()">
                                                    <i data-feather="copy" class="icon-xs mr-1"></i>Copier Téléphone
                                                </button>
                                                <button class="btn btn-outline-info btn-sm btn-block" onclick="shareAgencyInfo()">
                                                    <i data-feather="share-2" class="icon-xs mr-1"></i>Partager infos
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Activité -->
                            <div class="tab-pane fade" id="activity" role="tabpanel">
                                <div class="info-card">
                                    <h6 class="card-title text-primary">
                                        <i data-feather="activity" class="icon-xs mr-2"></i>Historique d'activité
                                    </h6>
                                    <div class="activity-timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success">
                                                <i data-feather="home" class="icon-xs text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Agence créée</h6>
                                                <p class="timeline-description text-muted">
                                                    L'agence a été créée dans le système
                                                </p>
                                                <small class="timeline-time text-muted" id="activityCreationDate">--</small>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary">
                                                <i data-feather="check" class="icon-xs text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Statut actuel</h6>
                                                <p class="timeline-description text-muted">
                                                    L'agence est actuellement active
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

            <!-- Footer du modal -->
            <div class="modal-footer modal-footer-enhanced border-top bg-light">
                <div class="d-flex justify-content-between w-100 align-items-center flex-wrap">
                    <div class="footer-left mb-2 mb-md-0">
                        <small class="text-muted">
                            <i data-feather="clock" class="icon-xs mr-1"></i>
                            Dernière mise à jour : <span id="modalLastUpdate">Maintenant</span>
                        </small>
                    </div> 
                    <div class="footer-right d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="refreshAgencyDetails()">
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

<!-- ✅ CSS STYLES ET JAVASCRIPT COMPLET - ARCHITECTURE IDENTIQUE AUX FICHIERS DE RÉFÉRENCE -->
<style>
/* CSS existant + améliorations pour agences - IDENTIQUE À users-list.blade.php */
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
.agency-checkbox, #selectAll {
    cursor: pointer;
    transform: scale(1.1);
    transition: all 0.2s ease;
}

.agency-checkbox:hover, #selectAll:hover {
    transform: scale(1.2);
}

.highlight-change {
    background-color: rgba(0, 123, 255, 0.1) !important;
    transition: background-color 0.3s ease;
    border-left: 3px solid #007bff;
}

/* Styles pour la sélection améliorée */
.agency-row.selected {
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

/* Styles spécifiques pour les agences */
.agency-avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.agency-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.agency-icon-large {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.agency-icon-xl {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
}

.agency-avatar-modal {
    position: relative;
}

.agency-status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
}

.agency-status-indicator.active {
    background-color: #28a745;
}

.agency-status-indicator.inactive {
    background-color: #ffc107;
}

.agency-avatar-large {
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

/* Z-INDEX pour modales */
#agencyDetailsModal {
    z-index: 1050 !important;
}

#confirmationModal {
    z-index: 1060 !important;
}

/* Styles modales (identiques à users-list) */
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

.footer-left, .footer-right {
    flex-shrink: 0 !important;
}

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

/* Styles confirmations modales */
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

.btn-loading {
    pointer-events: none;
    opacity: 0.7;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Info cards dans les modales */
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

.quick-actions .btn {
    transition: all 0.3s ease;
}

.quick-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

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

/* Autres styles */
.counter {
    font-size: 2rem;
    font-weight: bold;
    transition: all 0.3s ease;
}

.agency-row {
    transition: all 0.3s ease;
}

.agency-row:hover {
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

.agency-details-content {
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

/* Responsive */
@media (max-width: 992px) {
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

    .modal-footer-enhanced {
        padding: 15px !important;
        flex-direction: column !important;
        gap: 15px !important;
    }

    .nav-pills-enhanced {
        flex-direction: column !important;
        gap: 8px !important;
    }

    .nav-pills-enhanced .nav-link {
        text-align: center !important;
        width: 100% !important;
    }

    .agency-header-section .row {
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

    .agency-header-section .container-fluid {
        padding: 15px !important;
    }

    .info-card {
        padding: 15px !important;
        margin-bottom: 15px !important;
    }
}
</style>

<!-- ✅ JAVASCRIPT COMPLET CORRIGÉ - ARCHITECTURE IDENTIQUE AUX FICHIERS DE RÉFÉRENCE -->
<script>
// Variables globales pour agences - CONFORMES AUX FICHIERS DE RÉFÉRENCE
let searchTimeout;
let realTimeInterval;
let lastUpdateTimestamp = Date.now();
let currentAction = null;
let currentAgencyId = null;
let selectedAgenciesCount = 0;

// Initialisation - VERSION ULTRA DÉFENSIVE IDENTIQUE AUX FICHIERS DE RÉFÉRENCE
document.addEventListener('DOMContentLoaded', function() {
    // Délai pour s'assurer que tout est chargé
    setTimeout(function() {
        try {
            console.log('🔄 Initialisation du système de gestion des agences...');
            
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
                const agencyCheckboxes = document.querySelectorAll('.agency-checkbox');
                if (agencyCheckboxes && agencyCheckboxes.length > 0) {
                    agencyCheckboxes.forEach((checkbox, index) => {
                        if (checkbox && checkbox.addEventListener) {
                            try {
                                checkbox.addEventListener('change', handleIndividualCheckbox);
                            } catch (individualError) {
                                console.warn(`⚠️ Erreur checkbox ${index} (ignorée):`, individualError.message);
                            }
                        }
                    });
                    console.log(`✅ ${agencyCheckboxes.length} checkboxes agences connectées`);
                } else {
                    console.warn('⚠️ Aucune checkbox agence trouvée');
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
// ✅ CORRECTION PRINCIPALE : GESTION DES SÉLECTIONS FONCTIONNELLE - IDENTIQUE AUX RÉFÉRENCES
// ==================================================================================== 

// ✅ NOUVELLE FONCTION: Gérer le bouton "Sélectionner tout"
function handleSelectAllButton() {
    console.log('🔄 Bouton Sélectionner tout cliqué');
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const agencyCheckboxes = document.querySelectorAll('.agency-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.agency-checkbox:checked');
    
    // Déterminer l'action : si tout est sélectionné, désélectionner, sinon sélectionner tout
    const shouldSelectAll = checkedCheckboxes.length < agencyCheckboxes.length;
    
    console.log(`Action: ${shouldSelectAll ? 'Sélectionner' : 'Désélectionner'} tout`);
    
    // Appliquer la sélection à toutes les checkboxes
    agencyCheckboxes.forEach(checkbox => {
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
    const selectedCount = shouldSelectAll ? agencyCheckboxes.length : 0;
    selectedAgenciesCount = selectedCount;
    console.log(`✅ ${selectedCount} agence(s) sélectionnée(s)`);
    
    // Mettre à jour les boutons d'action
    updateSelectionStatus();
    
    // Toast notification
    if (typeof showToast === 'function') {
        if (shouldSelectAll) {
            showToast('Sélection', `${selectedCount} agence(s) sélectionnée(s)`, 'success');
        } else {
            showToast('Désélection', 'Toutes les agences désélectionnées', 'info');
        }
    }
}

// ✅ NOUVELLE FONCTION: Gérer la checkbox du tableau
function handleTableSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const agencyCheckboxes = document.querySelectorAll('.agency-checkbox');
    
    if (!selectAllCheckbox) return;
    
    const isChecked = selectAllCheckbox.checked;
    console.log('🔄 Checkbox tableau:', isChecked);
    
    selectedAgenciesCount = 0;
    
    // Appliquer à toutes les checkboxes agence
    agencyCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
        
        if (isChecked) {
            selectedAgenciesCount++;
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
    const agencyCheckboxes = document.querySelectorAll('.agency-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.agency-checkbox:checked');

    selectedAgenciesCount = checkedCheckboxes.length;

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
        } else if (checkedCheckboxes.length === agencyCheckboxes.length) {
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

    console.log(`📊 Sélection: ${checkedCheckboxes.length}/${agencyCheckboxes.length} agences`);
}

// ✅ NOUVELLE FONCTION: Mettre à jour l'affichage de sélection
function updateSelectionStatus() {
    // Mettre à jour le compteur de sélection
    const selectedCountElement = document.getElementById('selectedCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    if (selectedCountElement) {
        if (selectedAgenciesCount > 0) {
            selectedCountElement.textContent = `${selectedAgenciesCount} sélectionnée(s)`;
            selectedCountElement.style.display = 'inline-block';
            
            // Animation du compteur
            selectedCountElement.style.animation = 'countUpdate 0.3s ease';
        } else {
            selectedCountElement.style.display = 'none';
        }
    }
    
    // Mise à jour du bouton de suppression en masse
    if (bulkDeleteBtn) {
        if (selectedAgenciesCount > 0) {
            bulkDeleteBtn.classList.remove('btn-outline-danger');
            bulkDeleteBtn.classList.add('btn-danger', 'bulk-action-active');
            bulkDeleteBtn.title = `Supprimer ${selectedAgenciesCount} agence(s) sélectionnée(s)`;
            
            // Mise à jour du texte du bouton
            const btnIcon = bulkDeleteBtn.querySelector('i');
            const btnText = bulkDeleteBtn.lastChild;
            if (btnText && btnText.nodeType === Node.TEXT_NODE) {
                btnText.textContent = `Supprimer (${selectedAgenciesCount})`;
            }
        } else {
            bulkDeleteBtn.classList.remove('btn-danger', 'bulk-action-active');
            bulkDeleteBtn.classList.add('btn-outline-danger');
            bulkDeleteBtn.title = 'Supprimer sélectionnées';
            
            // Restaurer le texte du bouton
            const btnText = bulkDeleteBtn.lastChild;
            if (btnText && btnText.nodeType === Node.TEXT_NODE) {
                btnText.textContent = 'Supprimer';
            }
        }
    }
    
    console.log(`📊 Affichage mis à jour: ${selectedAgenciesCount} sélectionnée(s)`);
}

// ==================================================================================== 
// MODALES DE CONFIRMATION - IDENTIQUES AUX FICHIERS DE RÉFÉRENCE
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
    if (confirmSpinner) confirmSpinner.style.display = 'none';

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
                }
            });
            console.log('✅ Gestionnaire bouton confirmation connecté');
        } else {
            console.warn('⚠️ Bouton confirmation introuvable ou non fonctionnel');
        }
    } catch (globalError) {
        console.error('❌ Erreur initialisation gestionnaire confirmation:', globalError);
    }
}, 200);

// ==================================================================================== 
// ACTIONS SUR LES AGENCES - IDENTIQUES AUX FICHIERS DE RÉFÉRENCE
// ==================================================================================== 

function showActivateAgencyModal(agencyId, agencyName) {
    showConfirmationModal({
        type: 'success',
        icon: 'play',
        title: '✅ Activer l\'agence',
        message: `Confirmer l'activation de l'agence "${agencyName}" ?`,
        details: `
            <div class="text-success">
                <i data-feather="info" class="icon-xs mr-1"></i>
                <strong>Conséquences de l'activation :</strong><br>
                <small>• L'agence deviendra opérationnelle<br>
                • Elle sera visible dans les listes actives<br>
                • Toutes les fonctionnalités seront accessibles</small>
            </div>
        `,
        confirmText: 'Activer l\'agence',
        confirmClass: 'btn-success',
        onConfirm: () => executeAgencyAction(agencyId, 'activate', agencyName)
    });
}

function showDeactivateAgencyModal(agencyId, agencyName) {
    showConfirmationModal({
        type: 'warning',
        icon: 'pause',
        title: '⏸️ Désactiver l\'agence',
        message: `Confirmer la désactivation de l'agence "${agencyName}" ?`,
        details: `
            <div class="text-warning">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>Conséquences de la désactivation :</strong><br>
                <small>• L'agence ne sera plus opérationnelle<br>
                • Elle sera masquée des listes actives<br>
                • Les fonctionnalités seront limitées</small>
            </div>
        `,
        confirmText: 'Désactiver l\'agence',
        confirmClass: 'btn-warning',
        onConfirm: () => executeAgencyAction(agencyId, 'deactivate', agencyName)
    });
}

function showDeleteAgencyModal(agencyId, agencyName) {
    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '🗑️ Supprimer l\'agence',
        message: `Confirmer la suppression définitive de l'agence "${agencyName}" ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>⚠️ ATTENTION : Cette action est irréversible !</strong><br>
                <small>• Toutes les données de l'agence seront supprimées<br>
                • L'historique sera perdu<br>
                • Cette opération ne peut pas être annulée</small>
            </div>
        `,
        confirmText: 'Supprimer définitivement',
        confirmClass: 'btn-danger',
        onConfirm: () => executeAgencyAction(agencyId, 'delete', agencyName)
    });
}

function showCreateAgencyModal() {
    window.location.href = "{{ route('agency.agence-create') }}";
}

function showBulkActivateModal() {
    const inactiveCountElement = document.getElementById('inactiveCount');
    const inactiveCount = inactiveCountElement ? parseInt(inactiveCountElement.textContent) : 0;

    if (inactiveCount == 0) {
        showToast('Info', 'Aucune agence inactive à activer', 'info');
        return;
    }

    showConfirmationModal({
        type: 'warning',
        icon: 'zap',
        title: '⚡ Activation en masse',
        message: `Confirmer l'activation de toutes les agences inactives ?`,
        details: `
            <div class="text-warning">
                <i data-feather="home" class="icon-xs mr-1"></i>
                <strong>${inactiveCount} agence(s) seront activées</strong><br>
                <small>• Toutes les agences inactives deviendront actives<br>
                • Elles seront immédiatement opérationnelles<br>
                • Cette action s'applique à toutes les agences inactives</small>
            </div>
        `,
        confirmText: `Activer ${inactiveCount} agence(s)`,
        confirmClass: 'btn-warning',
        onConfirm: () => executeBulkAction('activate')
    });
}

function showBulkDeleteModal() {
    if (selectedAgenciesCount === 0) {
        showToast('Attention', 'Aucune agence sélectionnée pour la suppression', 'warning');
        return;
    }

    showConfirmationModal({
        type: 'danger',
        icon: 'trash-2',
        title: '🗑️ Suppression en masse',
        message: `Confirmer la suppression de ${selectedAgenciesCount} agence(s) sélectionnée(s) ?`,
        details: `
            <div class="text-danger">
                <i data-feather="alert-triangle" class="icon-xs mr-1"></i>
                <strong>⚠️ ATTENTION : Cette action est irréversible !</strong><br>
                <small>• ${selectedAgenciesCount} agence(s) seront supprimées définitivement<br>
                • Toutes les données associées seront perdues</small>
            </div>
        `,
        confirmText: `Supprimer ${selectedAgenciesCount} agence(s)`,
        confirmClass: 'btn-danger',
        onConfirm: () => executeBulkDelete()
    });
}

// ==================================================================================== 
// EXÉCUTION DES ACTIONS - ROUTES CORRIGÉES
// ==================================================================================== 

function executeAgencyAction(agencyId, action, agencyName) {
    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        let url, method = 'POST';

        switch(action) {
            case 'activate':
                url = `/admin/agencies/${agencyId}/activate`;
                break;
            case 'deactivate':
                url = `/admin/agencies/${agencyId}/deactivate`;
                break;
            case 'delete':
                url = `/admin/agencies/${agencyId}`;
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
                showToast('Succès', data.message || `Action ${action} effectuée sur l'agence "${agencyName}"`, 'success');
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

        fetch(`/admin/agencies/bulk-${action}`, {
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
    const selectedAgencies = Array.from(document.querySelectorAll('.agency-checkbox:checked')).map(cb => cb.value);

    setTimeout(() => {
        $('#confirmationModal').modal('hide');
        showLoading();

        fetch('/admin/agencies/bulk-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ agency_ids: selectedAgencies })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                showToast('Succès', data.message || `${selectedAgencies.length} agence(s) supprimée(s)`, 'success');
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
// MODAL DÉTAILS AGENCE
// ==================================================================================== 

function showAgencyDetails(agencyId) {
    currentAgencyId = agencyId;

    $('#agencyDetailsModal').modal('show');

    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('agencyDetailsContentMain').style.display = 'none';

    setTimeout(() => {
        loadAgencyDetails(agencyId);
    }, 500);
}

function loadAgencyDetails(agencyId) {
    fetch(`/admin/agencies/${agencyId}/details`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateAgencyDetails(data.agency);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('agencyDetailsContentMain').style.display = 'block';
        } else {
            showToast('Erreur', data.message || 'Erreur lors du chargement des détails', 'error');
            $('#agencyDetailsModal').modal('hide');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur', 'Erreur lors du chargement des détails', 'error');
        $('#agencyDetailsModal').modal('hide');
    });
}

function populateAgencyDetails(agency) {
    console.log('✅ Remplissage des détails agence:', agency);

    // Helper function pour remplir un élément de façon sécurisée
    function setElementText(id, text) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = text || 'N/A';
        }
    }

    // En-tête
    setElementText('modalAgencyName', `🏢 ${agency.name || 'Agence'}`);
    setElementText('modalAgencyRole', agency.country || 'Localisation');

    // Informations générales
    setElementText('agencyFullName', agency.name);
    setElementText('agencyLocation', `${agency.city || 'N/A'}, ${agency.country || 'N/A'}`);

    // Badge de statut
    const statusBadge = document.getElementById('agencyStatusBadgeText');
    if (statusBadge) {
        statusBadge.textContent = agency.status_name || 'Non défini';
        statusBadge.className = `badge badge-${agency.status_badge_color || 'secondary'}`;
    }

    // Détails dans les onglets
    setElementText('detailAgencyName', agency.name);
    setElementText('detailAgencyId', `#${agency.id}`);
    setElementText('detailAgencyStatus', agency.status_name);

    // Informations temporelles
    setElementText('detailCreatedAt', agency.created_at);
    setElementText('detailUpdatedAt', agency.updated_at);
    setElementText('detailAgencyAge', agency.age_formatted);
    setElementText('agencyCreationDate', formatDateSimple(agency.created_at_iso));

    // Localisation
    setElementText('detailAddress1', agency.address_1);
    setElementText('detailAddress2', agency.address_2 || 'Non renseigné');
    setElementText('detailCity', agency.city);
    setElementText('detailCountry', agency.country);
    setElementText('detailFullAddress', agency.full_address);

    // Contact
    setElementText('detailPhone', agency.phone);
    const phoneLink = document.getElementById('detailPhoneLink');
    if (phoneLink && agency.phone) {
        phoneLink.href = `tel:${agency.phone}`;
    }
    setElementText('detailCreatedBy', agency.creator_name);
    setElementText('detailNotes', agency.notes);

    // Activité
    setElementText('activityCreationDate', agency.created_at);

    // Mise à jour du timestamp
    setElementText('modalLastUpdate', new Date().toLocaleString('fr-FR'));

    // Régénérer les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    console.log('✅ Détails agence remplis avec succès');
}

// ==================================================================================== 
// SYSTÈME DE FILTRES ET RECHERCHE
// ==================================================================================== 

function filterByStatus(status) {
    const statusSelect = document.getElementById('status');
    const cards = document.querySelectorAll('.clickable-card');

    cards.forEach(card => card.classList.remove('card-selected'));
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('card-selected');
    }

    if (statusSelect) {
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
    }

    applyFilters();
}

function filterByRecent() {
    const cards = document.querySelectorAll('.clickable-card');
    cards.forEach(card => card.classList.remove('card-selected'));
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('card-selected');
    }

    const form = document.getElementById('filterForm');
    if (form) {
        let recentInput = form.querySelector('input[name="recent"]');
        if (!recentInput) {
            recentInput = document.createElement('input');
            recentInput.type = 'hidden';
            recentInput.name = 'recent';
            form.appendChild(recentInput);
        }
        recentInput.value = '7';
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

// Fonction de recherche optimisée
const optimizedLiveSearch = debounce(function() {
    const query = document.getElementById('search').value;
    if (query.length >= 2 || query.length === 0) {
        document.getElementById('filterForm').submit();
    }
}, 300);

function liveSearch() {
    optimizedLiveSearch();
}

function quickSearchAgencies() {
    const query = document.getElementById('quickSearch').value;

    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        if (query.length >= 2) {
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
    const hasFilters = urlParams.has('search') || urlParams.has('status') || urlParams.has('country') || urlParams.has('city') || urlParams.has('recent');

    if (hasFilters) {
        const filterButton = document.querySelector('button[type="submit"]');
        if (filterButton) {
            filterButton.classList.add('filter-active');
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

        fetch('/admin/api/agencies/stats', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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
    const totalElement = document.getElementById('totalAgencies');
    if (totalElement) {
        totalElement.textContent = stats.total || 0;
    }
    
    document.querySelectorAll('.counter').forEach(counter => {
        const target = counter.getAttribute('data-target');
        if (target) {
            counter.textContent = target;
        }
    });
}

function refreshAgenciesList() {
    showToast('Info', 'Actualisation de la liste...', 'info');
    window.location.reload();
}

function exportAgencies() {
    showToast('Info', 'Export en cours...', 'info');

    setTimeout(() => {
        window.location.href = '/admin/agencies/export';
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
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        } else if (typeof $ !== 'undefined' && $.fn.toast) {
            $(toastElement).toast('show');
        } else {
            console.warn('⚠️ Bootstrap Toast non disponible');
            toastElement.style.display = 'block';
        }

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

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

// Actions pour les détails d'agence
function refreshAgencyDetails() {
    if (currentAgencyId) {
        showToast('Info', 'Actualisation des détails...', 'info');
        loadAgencyDetails(currentAgencyId);
    }
}

function copyAgencyAddress() {
    const addressElement = document.getElementById('detailFullAddress');
    if (addressElement) {
        const address = addressElement.textContent;
        if (address && address !== 'N/A') {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(address).then(() => {
                    showToast('Succès', 'Adresse copiée dans le presse-papier !', 'success');
                }).catch(() => {
                    showToast('Erreur', 'Impossible de copier l\'adresse', 'error');
                });
            } else {
                showToast('Erreur', 'Fonction de copie non supportée', 'error');
            }
        }
    }
}

function openMap() {
    const addressElement = document.getElementById('detailFullAddress');
    if (addressElement) {
        const address = addressElement.textContent;
        if (address && address !== 'N/A') {
            const encodedAddress = encodeURIComponent(address);
            window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank');
        } else {
            showToast('Erreur', 'Aucune adresse disponible pour la carte', 'error');
        }
    }
}

function getDirections() {
    const addressElement = document.getElementById('detailFullAddress');
    if (addressElement) {
        const address = addressElement.textContent;
        if (address && address !== 'N/A') {
            const encodedAddress = encodeURIComponent(address);
            window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodedAddress}`, '_blank');
        } else {
            showToast('Erreur', 'Aucune adresse disponible pour l\'itinéraire', 'error');
        }
    }
}

function callAgency() {
    const phoneElement = document.getElementById('detailPhone');
    if (phoneElement) {
        const phone = phoneElement.textContent;
        if (phone && phone !== 'N/A') {
            window.location.href = `tel:${phone}`;
        } else {
            showToast('Erreur', 'Aucun numéro de téléphone disponible', 'error');
        }
    }
}

function copyAgencyPhone() {
    const phoneElement = document.getElementById('detailPhone');
    if (phoneElement) {
        const phone = phoneElement.textContent;
        if (phone && phone !== 'N/A') {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(phone).then(() => {
                    showToast('Succès', 'Téléphone copié dans le presse-papier !', 'success');
                }).catch(() => {
                    showToast('Erreur', 'Impossible de copier le téléphone', 'error');
                });
            } else {
                showToast('Erreur', 'Fonction de copie non supportée', 'error');
            }
        }
    }
}

function shareAgencyInfo() {
    const agencyNameElement = document.getElementById('detailAgencyName');
    const agencyPhoneElement = document.getElementById('detailPhone');
    const agencyAddressElement = document.getElementById('detailFullAddress');

    if (agencyNameElement && agencyPhoneElement && agencyAddressElement) {
        const agencyName = agencyNameElement.textContent;
        const agencyPhone = agencyPhoneElement.textContent;
        const agencyAddress = agencyAddressElement.textContent;

        const shareText = `Informations agence "${agencyName}":
📞 Téléphone: ${agencyPhone}
📍 Adresse: ${agencyAddress}`;

        if (navigator.share) {
            navigator.share({
                title: `Agence ${agencyName}`,
                text: shareText
            });
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(shareText).then(() => {
                showToast('Succès', 'Informations copiées dans le presse-papier !', 'success');
            }).catch(() => {
                showToast('Erreur', 'Impossible de partager les informations', 'error');
            });
        } else {
            showToast('Erreur', 'Fonction de partage non supportée', 'error');
        }
    }
}

// ==================================================================================== 
// FONCTIONS DE COMPATIBILITÉ ET DEBUGGING
// ==================================================================================== 

function debugSelection() {
    console.log('🔍 DEBUG - État des sélections:');
    console.log('- selectedAgenciesCount:', selectedAgenciesCount);
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const agencyCheckboxes = document.querySelectorAll('.agency-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.agency-checkbox:checked');
    
    console.log('- selectAllCheckbox.checked:', selectAllCheckbox?.checked);
    console.log('- selectAllCheckbox.indeterminate:', selectAllCheckbox?.indeterminate);
    console.log('- Total agences:', agencyCheckboxes.length);
    console.log('- Agences cochées:', checkedCheckboxes.length);
    
    checkedCheckboxes.forEach((checkbox, index) => {
        console.log(`  - Agence ${index}: ID=${checkbox.value}`);
    });
}

function forceUpdateDisplay() {
    console.log('🔄 Force update display...');
    
    selectedAgenciesCount = document.querySelectorAll('.agency-checkbox:checked').length;
    updateSelectionStatus();
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log('✅ Display forcé mis à jour');
}

function toggleSelectAll() {
    handleSelectAllButton();
}

// ==================================================================================== 
// GESTION DES ERREURS ET NETTOYAGE - VERSION SÉCURISÉE
// ==================================================================================== 

window.addEventListener('unhandledrejection', function(event) {
    console.warn('⚠️ Promesse rejetée (ignorée):', event.reason);
    event.preventDefault();
});

window.addEventListener('beforeunload', function() {
    try {
        if (typeof stopRealTimeUpdates === 'function') {
            stopRealTimeUpdates();
        }
    } catch (error) {
        // Silencieux
    }
});

setTimeout(function() {
    try {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#agencyDetailsModal').on('hidden.bs.modal', function() {
                try {
                    currentAgencyId = null;
                    console.log('🔧 Modal agence fermé, currentAgencyId reset');
                } catch (error) {
                    // Silencieux
                }
            });

            $('#confirmationModal').on('hidden.bs.modal', function() {
                try {
                    currentAction = null;
                    
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
}, 500);

// ==================================================================================== 
// INITIALISATION FINALE ET LOG
// ==================================================================================== 

setTimeout(function() {
    try {
        console.log('🎉 Système de gestion des agences complètement initialisé !');
        console.log('📋 Fonctionnalités disponibles:');
        console.log('  ✅ Sélection multiple avec "Sélectionner tout"');
        console.log('  ✅ Actions en masse (suppression, activation)');
        console.log('  ✅ Modales de confirmation avec animations');
        console.log('  ✅ Détails d\'agences dans modal avancé');
        console.log('  ✅ Filtres et recherche en temps réel');
        console.log('  ✅ Notifications toast');
        console.log('  ✅ Mises à jour temps réel');
        console.log('  ✅ Gestion d\'erreurs complète');

        if (typeof window !== 'undefined') {
            try {
                window.debugAgencySelection = debugSelection;
                window.forceUpdateAgencyDisplay = forceUpdateDisplay;
                
                console.log('🛠️ Fonctions de debug disponibles: debugAgencySelection(), forceUpdateAgencyDisplay()');
            } catch (windowError) {
                console.warn('⚠️ Impossible d\'exposer les fonctions de debug (ignoré)');
            }
        }

        const environmentCheck = {
            dom: !!document.getElementById('selectAll'),
            feather: typeof feather !== 'undefined',
            jquery: typeof $ !== 'undefined',
            bootstrap: typeof bootstrap !== 'undefined'
        };

        console.log('🔍 État de l\'environnement:', environmentCheck);

        setTimeout(function() {
            try {
                const healthCheck = {
                    selectAllCheckbox: !!document.getElementById('selectAll'),
                    agencyCheckboxes: document.querySelectorAll('.agency-checkbox').length,
                    toastContainer: !!document.getElementById('toastContainer'),
                    agenciesTable: !!document.getElementById('agenciesTable'),
                    bulkDeleteBtn: !!document.getElementById('bulkDeleteBtn'),
                    selectAllBtn: !!document.getElementById('selectAllBtn')
                };

                console.log('🏥 Test de santé du système:', healthCheck);

                const issues = Object.entries(healthCheck).filter(([key, value]) => !value || (key === 'agencyCheckboxes' && value === 0));
                
                if (issues.length === 0) {
                    console.log('💚 Système entièrement fonctionnel !');
                } else {
                    console.warn('⚠️ Problèmes détectés:', issues.map(([key]) => key));
                    console.warn('ℹ️ Ces problèmes peuvent être normaux selon le contenu de la page');
                }

            } catch (healthError) {
                console.warn('⚠️ Impossible de faire le test de santé:', healthError.message);
            }
        }, 500);
        
    } catch (finalError) {
        console.warn('⚠️ Erreur lors du log final (ignorée):', finalError.message);
    }
}, 1000);
</script>

@endsection