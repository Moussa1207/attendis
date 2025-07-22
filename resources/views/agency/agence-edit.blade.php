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
                        <span class="badge badge-danger badge-pill noti-icon-badge">1</span>
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
                                        <i data-feather="edit-2" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Modification d'agence</h6>
                                        <small class="text-muted mb-0">Modifier les informations de l'agence</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <a href="{{ route('agency.agence') }}" class="dropdown-item text-center text-primary">
                            Retour à la liste <i class="fi-arrow-right"></i>
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
                        <a class="btn btn-sm btn-soft-primary waves-effect" href="{{ route('agency.agence') }}" role="button">
                            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
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
                                    <i data-feather="edit-2" class="mr-2"></i>Modifier l'agence "{{ $agency->name }}"
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('agency.agence') }}">Agences</a></li>
                                    <li class="breadcrumb-item active">Modifier</li>
                                </ol>
                            </div><!--end col-->
                            <div class="col-auto align-self-center">
                                <button class="btn btn-sm btn-outline-info" onclick="showHelp()">
                                    <i data-feather="help-circle" class="align-self-center icon-xs mr-1"></i>Aide
                                </button>
                            </div><!--end col-->  
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->

            <!-- Messages d'alerte -->
            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert" style="font-family: monospace; font-size: 14px; white-space: pre-line;">
                  <i data-feather="check-circle" class="mr-2"></i>
                    {{ session('success') }}
                  <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                  </button>
              </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                <i data-feather="alert-circle" class="mr-2"></i>
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            <!-- Informations sur l'agence -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card animate__animated animate__fadeInUp">
                        <div class="card-body bg-soft-warning">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i data-feather="info" class="icon-lg text-warning"></i>
                                </div>
                                <div class="col">
                                    <h6 class="text-warning mb-1 font-weight-semibold">Informations de modification</h6>
                                    <p class="text-muted mb-0">
                                        • Agence créée le <strong>{{ $agency->created_at->format('d/m/Y à H:i') }}</strong><br>
                                        • Statut actuel : <strong>{{ $agency->isActive() ? 'Active' : 'Inactive' }}</strong><br>
                                        • Dernière modification : <strong>{{ $agency->updated_at->format('d/m/Y à H:i') }}</strong><br>
                                        • Vous pouvez modifier <strong>toutes les informations</strong> à l'exception du statut<br>
                                        • Les modifications seront <strong>sauvegardées immédiatement</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="200">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="edit-3" class="mr-2"></i>Informations de l'agence
                                    </h4>
                                    <p class="text-muted mb-0">Modifiez les informations de l'agence "{{ $agency->name }}"</p>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <span class="badge badge-soft-warning font-12">
                                        <i data-feather="edit-2" class="icon-xs mr-1"></i>Modification d'une agence
                                    </span>
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->
                        <div class="card-body">
                            <div class="tab-pane px-3 pt-3" id="Edit_Agency_Tab" role="tabpanel">
                                <form method="POST" action="{{ route('agencies.update', $agency->id) }}" class="form-horizontal auth-form my-4" id="editAgencyForm">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="agency_name">Nom <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-light">
                                                            <i data-feather="home" class="icon-xs"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="agency_name" value="{{ old('name', $agency->name) }}" placeholder="ex: Agence Plateau" required>
                                                    @error('name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="agency_phone">Téléphone <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-light">
                                                            <i data-feather="phone" class="icon-xs"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="agency_phone" value="{{ old('phone', $agency->phone) }}" placeholder="ex: 0707000000" required>
                                                    @error('phone')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="address_1">Adresse principale <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">
                                                    <i data-feather="map-pin" class="icon-xs"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('address_1') is-invalid @enderror" name="address_1" id="address_1" value="{{ old('address_1', $agency->address_1) }}" placeholder="ex: Boulevard de la République" required>
                                            @error('address_1')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="address_2">Adresse complémentaire <small class="text-muted">(optionnel)</small></label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">
                                                    <i data-feather="plus" class="icon-xs"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('address_2') is-invalid @enderror" name="address_2" id="address_2" value="{{ old('address_2', $agency->address_2) }}" placeholder="ex: Immeuble CCIA, 3ème étage">
                                            @error('address_2')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city">Ville <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-light">
                                                            <i data-feather="map" class="icon-xs"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city', $agency->city) }}" placeholder="ex: Abidjan" required>
                                                    @error('city')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="country">Pays <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-light">
                                                            <i data-feather="globe" class="icon-xs"></i>
                                                        </span>
                                                    </div>
                                                    <select class="form-control @error('country') is-invalid @enderror" name="country" id="country" required>
                                                        <option value="">-- Sélectionnez le pays --</option>
                                                        <option value="Côte d'Ivoire" {{ old('country', $agency->country) == "Côte d'Ivoire" ? 'selected' : '' }}> Côte d'Ivoire</option>
                                                        <option value="Burkina Faso" {{ old('country', $agency->country) == 'Burkina Faso' ? 'selected' : '' }}> Burkina Faso</option>
                                                        <option value="Mali" {{ old('country', $agency->country) == 'Mali' ? 'selected' : '' }}> Mali</option>
                                                        <option value="Sénégal" {{ old('country', $agency->country) == 'Sénégal' ? 'selected' : '' }}> Sénégal</option>
                                                        <option value="Ghana" {{ old('country', $agency->country) == 'Ghana' ? 'selected' : '' }}> Ghana</option>
                                                        <option value="Niger" {{ old('country', $agency->country) == 'Niger' ? 'selected' : '' }}> Niger</option>
                                                        <option value="Guinée" {{ old('country', $agency->country) == 'Guinée' ? 'selected' : '' }}> Guinée</option>
                                                        <option value="Bénin" {{ old('country', $agency->country) == 'Bénin' ? 'selected' : '' }}> Bénin</option>
                                                        <option value="Togo" {{ old('country', $agency->country) == 'Togo' ? 'selected' : '' }}> Togo</option>
                                                        <option value="France" {{ old('country', $agency->country) == 'France' ? 'selected' : '' }}> France</option>
                                                        <option value="Autre" {{ old('country', $agency->country) == 'Autre' ? 'selected' : '' }}> Autre</option>
                                                    </select>
                                                    @error('country')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informations automatiques -->
                                    <div class="form-group">
                                        <label class="text-muted font-weight-semibold">Informations système</label>
                                        <div class="alert alert-light border">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="calendar" class="icon-xs text-info mr-2"></i>
                                                        <span class="text-muted">Créée: <strong class="text-info">{{ $agency->created_at->format('d/m/Y') }}</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="{{ $agency->isActive() ? 'check-circle' : 'pause-circle' }}" class="icon-xs {{ $agency->isActive() ? 'text-success' : 'text-warning' }} mr-2"></i>
                                                        <span class="text-muted">Statut: <strong class="{{ $agency->isActive() ? 'text-success' : 'text-warning' }}">{{ $agency->isActive() ? 'Active' : 'Inactive' }}</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="user" class="icon-xs text-primary mr-2"></i>
                                                        <span class="text-muted">Créé par: <strong class="text-primary">{{ $agency->creator ? $agency->creator->username : 'Système' }}</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="edit-3" class="icon-xs text-warning mr-2"></i>
                                                        <span class="text-muted">Modifiée: <strong class="text-warning">{{ $agency->updated_at->format('d/m/Y') }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-4">
                                        <div class="col-sm-12">
                                            <div class="custom-control custom-switch switch-warning">
                                                <input type="checkbox" class="custom-control-input" id="customSwitchWarning" checked disabled>
                                                <label class="custom-control-label text-muted" for="customSwitchWarning">Les modifications seront <strong>sauvegardées immédiatement</strong> dans la base de données</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 row">
                                        <div class="col-12 mt-2">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <a href="{{ route('agency.agence') }}" class="btn btn-secondary btn-block waves-effect waves-light">
                                                        <i class="fas fa-arrow-left mr-1"></i>Retour à la liste
                                                    </a>
                                                </div>
                                                <div class="col-md-6">
                                                    <button class="btn btn-warning btn-block waves-effect waves-light" type="submit">
                                                        <i data-feather="save" class="mr-1"></i>Sauvegarder les modifications
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                               
            </div><!--end row-->

        </div><!-- container -->

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Modification d'agences par {{ Auth::user()->username }}</span>
        </footer><!--end footer-->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- Modal d'aide -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="help-circle" class="icon-xs mr-2"></i>Aide - Modification d'Agence
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>📝 Processus de Modification</h6>
                <ol class="pl-3">
                    <li>Modifiez les champs que vous souhaitez mettre à jour</li>
                    <li>Vérifiez que les informations obligatoires sont correctes</li>
                    <li>Cliquez sur "Sauvegarder les modifications"</li>
                    <li>Les changements seront appliqués immédiatement</li>
                    <li>Vous serez redirigé vers la liste des agences</li>
                </ol>
                
                <h6 class="mt-3">🏢 Champs modifiables</h6>
                <ul class="pl-3">
                    <li><strong>Nom :</strong> Nom unique de l'agence</li>
                    <li><strong>Téléphone :</strong> Numéro principal de contact</li>
                    <li><strong>Adresse 1 :</strong> Adresse principale obligatoire</li>
                    <li><strong>Adresse 2 :</strong> Complément d'adresse (optionnel)</li>
                    <li><strong>Ville et Pays :</strong> Localisation géographique</li>
                </ul>

                <h6 class="mt-3">⚠️ Informations importantes</h6>
                <ul class="pl-3">
                    <li>Le <strong>statut</strong> ne peut pas être modifié ici (utilisez les actions de la liste)</li>
                    <li>Le <strong>nom</strong> doit rester unique parmi vos agences</li>
                    <li>Les modifications sont <strong>sauvegardées immédiatement</strong></li>
                    <li>Un historique des modifications est conservé</li>
                </ul>

                <h6 class="mt-3">🌍 Pays disponibles</h6>
                <p class="text-muted">Liste pré-configurée avec les pays d'Afrique de l'Ouest et la France. Sélectionnez "Autre" si votre pays n'est pas listé.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning waves-effect" data-dismiss="modal">
                    <i data-feather="check" class="icon-xs mr-1"></i>Compris
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="position-fixed top-0 right-0 p-3" style="z-index: 1050; right: 0; top: 0;">
    <div id="toastContainer"></div>
</div>

<!-- CSS personnalisé -->
<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

.card {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

.form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255,193,7,.25);
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.was-validated .form-control:valid,
.form-control.is-valid {
    border-color: #28a745;
    background-image: none;
}

.was-validated .form-control:invalid,
.form-control.is-invalid {
    border-color: #dc3545;
    background-image: none;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.alert {
    border: none;
    border-radius: 8px;
}

.badge {
    font-size: 0.8em;
}

.icon-xs {
    width: 16px;
    height: 16px;
}

.icon-lg {
    width: 48px;
    height: 48px;
}

.bg-soft-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-soft-primary {
    background-color: rgba(0, 123, 255, 0.1) !important;
}  

.text-warning {
    color: #ffc107 !important;
}

/* Validation visuelle améliorée */
.form-control.is-valid:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Style pour le select de pays */
select.form-control {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

/* Animation pour les modifications */
.form-control.modified {
    border-color: #ffc107 !important;
    background-color: rgba(255, 193, 7, 0.1) !important;
    transition: all 0.3s ease;
}

/* Style pour les champs modifiés */
.field-changed {
    position: relative;
}

.field-changed::after {
    content: "✓";
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #ffc107;
    font-weight: bold;
}

/* Mise en évidence des changements */
.change-highlight {
    background: linear-gradient(45deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.2));
    border-left: 4px solid #ffc107;
    padding: 15px;
    border-radius: 8px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}
</style>

<!-- JavaScript -->
<script>
// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les icônes Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Validation en temps réel
    initializeFormValidation();
    
    // Surveillance des changements
    trackFormChanges();
});

// Afficher l'aide
function showHelp() {
    $('#helpModal').modal('show');
}

// Initialiser la validation du formulaire
function initializeFormValidation() {
    const form = document.getElementById('editAgencyForm');
    const requiredFields = ['name', 'phone', 'address_1', 'city', 'country'];
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName.replace('agency_', ''));
        if (field) {
            field.addEventListener('blur', function() {
                validateField(this);
            });
            
            field.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
                markFieldAsModified(this);
            });
        }
    });
    
    // Validation du téléphone
    const phoneField = document.getElementById('agency_phone');
    if (phoneField) {
        phoneField.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s]/g, '');
            markFieldAsModified(this);
        });
    }
    
    // Suggestions automatiques pour la ville selon le pays
    const countryField = document.getElementById('country');
    const cityField = document.getElementById('city');
    
    if (countryField && cityField) {
        countryField.addEventListener('change', function() {
            suggestCityForCountry(this.value, cityField);
            markFieldAsModified(this);
        });
    }
}

// Valider un champ
function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;
    
    // Réinitialiser les classes
    field.classList.remove('is-valid', 'is-invalid');
    
    if (field.required && !value) {
        field.classList.add('is-invalid');
        return false;
    }
    
    // Validations spécifiques
    switch(fieldName) {
        case 'name':
            if (value.length < 2) {
                field.classList.add('is-invalid');
                return false;
            }
            break;
            
        case 'phone':
            if (value.length < 8) {
                field.classList.add('is-invalid');
                return false;
            }
            break;
    }
    
    if (value) {
        field.classList.add('is-valid');
    }
    
    return true;
}

// Marquer un champ comme modifié
function markFieldAsModified(field) {
    field.classList.add('modified');
    
    // Ajouter une indication visuelle
    const container = field.closest('.form-group');
    if (container && !container.querySelector('.change-indicator')) {
        const indicator = document.createElement('small');
        indicator.className = 'change-indicator text-warning font-weight-bold';
        indicator.innerHTML = '<i data-feather="edit-3" class="icon-xs mr-1"></i>Modifié';
        container.appendChild(indicator);
        
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

// Surveiller les changements dans le formulaire
function trackFormChanges() {
    const form = document.getElementById('editAgencyForm');
    let hasChanges = false;
    
    // Surveiller tous les champs
    form.addEventListener('input', function() {
        hasChanges = true;
        updateSaveButton(true);
    });
    
    form.addEventListener('change', function() {
        hasChanges = true;
        updateSaveButton(true);
    });
    
    // Alerte si l'utilisateur quitte sans sauvegarder
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
        }
    });
    
    // Réinitialiser l'indicateur de changements lors de la soumission
    form.addEventListener('submit', function() {
        hasChanges = false;
    });
}

// Mettre à jour le bouton de sauvegarde
function updateSaveButton(hasChanges) {
    const saveBtn = document.querySelector('button[type="submit"]');
    if (saveBtn) {
        if (hasChanges) {
            saveBtn.classList.add('btn-warning');
            saveBtn.classList.remove('btn-secondary');
            saveBtn.innerHTML = '<i data-feather="save" class="mr-1"></i>Sauvegarder les modifications';
            
            // Animation pulsée
            saveBtn.style.animation = 'pulse 2s infinite';
        } else {
            saveBtn.classList.remove('btn-warning');
            saveBtn.classList.add('btn-secondary');
            saveBtn.innerHTML = '<i data-feather="check" class="mr-1"></i>Pas de modifications';
            saveBtn.style.animation = '';
        }
        
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

// Suggérer des villes selon le pays
function suggestCityForCountry(country, cityField) {
    const citySuggestions = {
        "Côte d'Ivoire": ["Abidjan", "Bouaké", "Daloa", "Yamoussoukro", "San-Pédro", "Korhogo", "Man"],
        "Burkina Faso": ["Ouagadougou", "Bobo-Dioulasso", "Koudougou", "Banfora", "Ouahigouya"],
        "Mali": ["Bamako", "Sikasso", "Mopti", "Koutiala", "Kayes", "Ségou"],
        "Sénégal": ["Dakar", "Thiès", "Kaolack", "Ziguinchor", "Saint-Louis", "Diourbel"],
        "Ghana": ["Accra", "Kumasi", "Tamale", "Sekondi-Takoradi", "Ashaiman"],
        "France": ["Paris", "Lyon", "Marseille", "Toulouse", "Nice", "Nantes", "Montpellier"]
    };
    
    if (citySuggestions[country] && cityField.value === '') {
        // Animation subtle
        cityField.style.backgroundColor = '#fff3cd';
        cityField.placeholder = `ex: ${citySuggestions[country][0]}`;
        
        setTimeout(() => {
            cityField.style.backgroundColor = '';
        }, 1000);
    }
}

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

// Prévisualisation des modifications
function previewChanges() {
    const form = document.getElementById('editAgencyForm');
    const formData = new FormData(form);
    
    let changes = [];
    
    // Comparer avec les valeurs originales
    const originalValues = {
        name: "{{ $agency->name }}",
        phone: "{{ $agency->phone }}",
        address_1: "{{ $agency->address_1 }}",
        address_2: "{{ $agency->address_2 }}",
        city: "{{ $agency->city }}",
        country: "{{ $agency->country }}"
    };
    
    for (let [key, value] of formData.entries()) {
        if (originalValues[key] !== undefined && originalValues[key] !== value) {
            changes.push({
                field: key,
                old: originalValues[key],
                new: value
            });
        }
    }
    
    if (changes.length > 0) {
        console.log('Modifications détectées:', changes);
        return changes;
    } else {
        console.log('Aucune modification détectée');
        return [];
    }
}
</script>

@endsection