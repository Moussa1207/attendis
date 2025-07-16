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
                                <input type="search" name="search" class="from-control top-search mb-0" placeholder="Rechercher dans mes services...">
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
                                        <i data-feather="briefcase" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Création de service</h6>
                                        <small class="text-muted mb-0">Prêt à créer un nouveau service</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <a href="{{ route('service.service-list') }}" class="dropdown-item text-center text-primary">
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
                        <a class="btn btn-sm btn-soft-primary waves-effect" href="{{ route('service.service-list') }}" role="button">
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
                                    <i data-feather="briefcase" class="mr-2"></i>Créer un nouveau service
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('service.service-list') }}">Services</a></li>
                                    <li class="breadcrumb-item active">Créer</li>
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

            <!-- Informations importantes -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card animate__animated animate__fadeInUp">
                        <div class="card-body bg-soft-info">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i data-feather="info" class="icon-lg text-info"></i>
                                </div>
                                <div class="col">
                                    <h6 class="text-info mb-1 font-weight-semibold">Informations Importantes</h6>
                                    <p class="text-muted mb-0">
                                        • Le service sera créé avec le <strong>statut que vous choisissez</strong><br>
                                        • Une <strong>lettre unique</strong> sera générée automatiquement si non fournie<br>
                                        • La <strong>description est optionnelle</strong> mais recommandée<br>
                                        • Vous serez enregistré comme <strong>créateur</strong> de ce service<br>
                                        • La <strong>lettre doit être unique</strong> dans le système
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de création -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="200">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="edit-3" class="mr-2"></i>Informations du service
                                    </h4>
                                    <p class="text-muted mb-0">Saisissez les informations du nouveau service</p>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <span class="badge badge-soft-primary font-12">
                                        <i data-feather="briefcase" class="icon-xs mr-1"></i>Création d'un service
                                    </span>
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->
                        <div class="card-body">
                            <div class="tab-pane px-3 pt-3" id="Create_Service_Tab" role="tabpanel">
                                <form method="POST" action="{{ route('services.store') }}" class="form-horizontal auth-form my-4" id="createServiceForm">
                                    @csrf

                                    <div class="form-group">
                                        <label for="service_nom">Nom du service <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">
                                                    <i data-feather="tag" class="icon-xs"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('nom') is-invalid @enderror" name="nom" id="service_nom" value="{{ old('nom') }}" placeholder="ex: Marketing" required>
                                            @error('nom')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="service_letter">Lettre de service <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">
                                                    <i data-feather="type" class="icon-xs"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('letter_of_service') is-invalid @enderror" name="letter_of_service" id="service_letter" value="{{ old('letter_of_service') }}" placeholder="ex: M (première lettre du nom)" maxlength="5" style="text-transform: uppercase;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary" id="checkAvailabilityBtn">
                                                    <i data-feather="search" class="icon-xs"></i> Vérifier
                                                </button>
                                            </div>
                                            @error('letter_of_service')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <small class="text-muted">
                                            <i data-feather="info" class="icon-xs mr-1"></i>
                                            <span id="letterInfo">Génération automatique depuis la première lettre du nom</span>
                                        </small>
                                        <div id="availabilityStatus" class="mt-2" style="display: none;"></div>
                                    </div>

                                    <div class="form-group">
                                       <label for="service_statut">Statut <span class="text-danger">*</span></label>
                                      <div class="input-group mb-3">
                                             <div class="input-group-prepend">
                                               <span class="input-group-text bg-light">
                                                <i data-feather="activity" class="icon-xs"></i>
                                               </span>
                                            </div>
                                            <select class="form-control @error('statut') is-invalid @enderror" 
                                                    name="statut" 
                                                    id="service_statut" 
                                                    required>
                                                <option value="">-- Sélectionnez le statut --</option>
                                                <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>
                                                    ✅ Actif
                                                </option>
                                                <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>
                                                    ⏸️ Inactif
                                                </option>
                                            </select>
                                            @error('statut')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <small class="text-muted">
                                            <i data-feather="info" class="icon-xs mr-1"></i>
                                            Définissez si le service est opérationnel
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="service_description">Description</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">
                                                    <i data-feather="file-text" class="icon-xs"></i>
                                                </span>
                                            </div>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      name="description" 
                                                      id="service_description" 
                                                      rows="4" 
                                                      placeholder="Description détaillée du service (optionnel)">{{ old('description') }}</textarea>
                                            @error('description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <small class="text-muted">
                                            <i data-feather="info" class="icon-xs mr-1"></i>
                                            Maximum 1000 caractères
                                        </small>
                                    </div>

                                    <!-- Informations automatiques -->
                                    <div class="form-group">
                                        <label class="text-muted font-weight-semibold">Paramètres automatiques</label>
                                        <div class="alert alert-light border">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="user" class="icon-xs text-info mr-2"></i>
                                                        <span class="text-muted">Créé par: <strong class="text-info">{{ Auth::user()->username }}</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="calendar" class="icon-xs text-success mr-2"></i>
                                                        <span class="text-muted">Date: <strong class="text-success">{{ now()->format('d/m/Y H:i') }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 row">
                                        <div class="col-12 mt-2">
                                            <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">
                                                Créer le service <i class="fas fa-plus ml-1"></i>
                                            </button>
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
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Création de services par {{ Auth::user()->username }}</span>
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
                    <i data-feather="help-circle" class="icon-xs mr-2"></i>Aide - Création de Service
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>📋 Processus de Création</h6>
                <ol class="pl-3">
                    <li>Saisissez le nom du service (obligatoire)</li>
                    <li>Vérifiez la lettre de service générée automatiquement</li>
                    <li>Modifiez la lettre manuellement si nécessaire</li>
                    <li>Choisissez le statut initial (actif/inactif)</li>
                    <li>Ajoutez une description détaillée (optionnel)</li>
                    <li>Validez la création</li>
                </ol>
                
                <h6 class="mt-3">🏷️ À propos de la lettre de service</h6>
                <ul class="pl-3">
                    <li>La lettre doit être <strong>unique</strong> dans le système</li>
                    <li>Elle est générée automatiquement depuis la première lettre du nom</li>
                    <li>Vous pouvez la <strong>modifier manuellement</strong> si nécessaire</li>
                    <li>Maximum 5 caractères autorisés</li>
                    <li>Exemples : <code>M</code> pour Marketing, <code>C</code> pour Comptabilité</li>
                </ul>

                <h6 class="mt-3">⚙️ Statuts disponibles</h6>
                <ul class="pl-3">
                    <li><span class="badge badge-success">✅ Actif</span> : Le service est opérationnel</li>
                    <li><span class="badge badge-warning">⏸️ Inactif</span> : Le service est temporairement arrêté</li>
                </ul>

                <h6 class="mt-3">📝 Description</h6>
                <ul class="pl-3">
                    <li>Optionnelle mais recommandée pour une meilleure identification</li>
                    <li>Maximum 1000 caractères</li>
                    <li>Décrivez le rôle et les responsabilités du service</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">
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
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
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

.bg-soft-info {
    background-color: rgba(23, 162, 184, 0.1) !important;
}

.bg-soft-primary {
    background-color: rgba(0, 123, 255, 0.1) !important;
}

.text-info {
    color: #17a2b8 !important;
}

/* Compteur de caractères pour description */
#service_description {
    resize: vertical;
    min-height: 100px;
}

/* Animation du formulaire */
.form-group {
    transition: all 0.3s ease;
}

.form-group:focus-within {
    transform: translateY(-2px);
}

/* Style pour les badges dans les options */
option {
    padding: 8px;
}

/* Style pour le statut de disponibilité */
.availability-success {
    color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
    border: 1px solid rgba(40, 167, 69, 0.2);
    padding: 8px 12px;
    border-radius: 4px;
}

.availability-error {
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.2);
    padding: 8px 12px;
    border-radius: 4px;
}

.availability-checking {
    color: #6c757d;
    background-color: rgba(108, 117, 125, 0.1);
    border: 1px solid rgba(108, 117, 125, 0.2);
    padding: 8px 12px;
    border-radius: 4px;
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

    // Génération automatique de la lettre depuis le nom
    const nomInput = document.getElementById('service_nom');
    const letterInput = document.getElementById('service_letter');

    if (nomInput && letterInput) {
        nomInput.addEventListener('input', function() {
            if (!letterInput.value || letterInput.dataset.autoGenerated) {
                const firstLetter = this.value.trim().charAt(0).toUpperCase();
                
                if (firstLetter) {
                    letterInput.value = firstLetter;
                    letterInput.dataset.autoGenerated = 'true';
                    
                    // Vérifier la disponibilité automatiquement
                    checkLetterAvailability(firstLetter);
                } else {
                    letterInput.value = '';
                    hideAvailabilityStatus();
                }
            }
        });

        // Marquer la lettre comme modifiée manuellement
        letterInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            if (this.value !== '') {
                this.dataset.autoGenerated = 'false';
                
                // Vérifier la disponibilité après un délai
                clearTimeout(this.checkTimeout);
                this.checkTimeout = setTimeout(() => {
                    checkLetterAvailability(this.value);
                }, 500);
            } else {
                hideAvailabilityStatus();
            }
        });
    }

    // Bouton vérification manuelle
    const checkBtn = document.getElementById('checkAvailabilityBtn');
    if (checkBtn) {
        checkBtn.addEventListener('click', function() {
            const letter = letterInput.value.trim();
            if (letter) {
                checkLetterAvailability(letter);
            } else {
                showToast('Attention', 'Veuillez saisir une lettre à vérifier', 'warning');
            }
        });
    }

    // Compteur de caractères pour la description
    const descriptionTextarea = document.getElementById('service_description');
    if (descriptionTextarea) {
        const maxLength = 1000;
        const counterElement = document.createElement('small');
        counterElement.className = 'text-muted float-right';
        descriptionTextarea.parentNode.appendChild(counterElement);

        function updateCounter() {
            const remaining = maxLength - descriptionTextarea.value.length;
            counterElement.textContent = `${descriptionTextarea.value.length}/${maxLength} caractères`;
            
            if (remaining < 100) {
                counterElement.className = 'text-warning float-right';
            } else if (remaining < 50) {
                counterElement.className = 'text-danger float-right';
            } else {
                counterElement.className = 'text-muted float-right';
            }
        }

        descriptionTextarea.addEventListener('input', updateCounter);
        updateCounter(); // Initialiser
    }
});

// Fonction pour vérifier la disponibilité d'une lettre
function checkLetterAvailability(letter) {
    if (!letter || letter.length === 0) return;
    
    const statusDiv = document.getElementById('availabilityStatus');
    const letterInfo = document.getElementById('letterInfo');
    
    // Afficher le statut de vérification
    showAvailabilityStatus('checking', 'Vérification en cours...');
    
    // Faire l'appel AJAX pour vérifier la disponibilité
    fetch('/admin/services/check-letter-availability', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ letter: letter })
    })
    .then(response => response.json())
    .then(data => {
        if (data.available) {
            showAvailabilityStatus('success', `✅ La lettre "${letter}" est disponible`);
        } else {
            showAvailabilityStatus('error', `❌ La lettre "${letter}" est déjà utilisée`);
            
            // Proposer des alternatives si disponibles
            if (data.suggestions && data.suggestions.length > 0) {
                const suggestions = data.suggestions.slice(0, 3).join(', ');
                showAvailabilityStatus('error', `❌ La lettre "${letter}" est déjà utilisée. Suggestions: ${suggestions}`);
            }
        }
    })
    .catch(error => {
        console.error('Erreur lors de la vérification:', error);
        showAvailabilityStatus('error', '❌ Erreur lors de la vérification');
    });
}

// Afficher le statut de disponibilité
function showAvailabilityStatus(type, message) {
    const statusDiv = document.getElementById('availabilityStatus');
    statusDiv.style.display = 'block';
    statusDiv.className = `availability-${type}`;
    statusDiv.innerHTML = `<small><i data-feather="${type === 'success' ? 'check' : type === 'error' ? 'x' : 'loader'}" class="icon-xs mr-1"></i>${message}</small>`;
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Masquer le statut de disponibilité
function hideAvailabilityStatus() {
    const statusDiv = document.getElementById('availabilityStatus');
    statusDiv.style.display = 'none';
}

// Afficher l'aide
function showHelp() {
    $('#helpModal').modal('show');
}

// Validation côté client
document.getElementById('createServiceForm').addEventListener('submit', function(e) {
    const nom = document.getElementById('service_nom').value.trim();
    const letter = document.getElementById('service_letter').value.trim();
    const statut = document.getElementById('service_statut').value;

    if (!nom) {
        e.preventDefault();
        showToast('Erreur', 'Le nom du service est obligatoire', 'error');
        return false;
    }

    if (!letter) {
        e.preventDefault();
        showToast('Erreur', 'La lettre de service est obligatoire', 'error');
        return false;
    }

    if (!statut) {
        e.preventDefault();
        showToast('Erreur', 'Veuillez sélectionner un statut', 'error');
        return false;
    }

    // Afficher un message de traitement
    showToast('Info', 'Création du service en cours...', 'info');
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