@extends('dashboard.master')

@section('contenu')
<div class="page-wrapper">
    <!-- Top Bar Start -->
    <div class="topbar">            
        <!-- Navbar -->
        <nav class="navbar-custom">    
            <ul class="list-unstyled topbar-nav float-right mb-0">  
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
            </ul>

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
    </div>

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
                                    <i data-feather="edit" class="mr-2"></i>Modifier le service
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('service.service-list') }}">Services</a></li>
                                    <li class="breadcrumb-item active">Modifier</li>
                                </ol>
                            </div>
                        </div>                                                              
                    </div>
                </div>
            </div>

            <!-- Messages d'alerte -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i data-feather="check-circle" class="mr-2"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i data-feather="x-circle" class="mr-2"></i>
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
                        <div class="card-body bg-soft-warning">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i data-feather="info" class="icon-lg text-warning"></i>
                                </div>
                                <div class="col">
                                    <h6 class="text-warning mb-1 font-weight-semibold">Informations de modification</h6>
                                    <p class="text-muted mb-0">
                                        • Vous modifiez le service : <strong>{{ $service->nom }}</strong><br>
                                        • Vous pouvez <strong>changer son nom</strong>, sa <strong>lettre de service</strong>, son <strong>statut</strong> et sa <strong>description</strong><br>
                                        • Les changements sont <strong>appliqués immédiatement</strong><br>
                                        • La <strong>lettre de service doit rester unique</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card animate__animated animate__fadeInUp">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">                      
                                    <h4 class="card-title">
                                        <i data-feather="edit-3" class="mr-2"></i>Informations du service
                                    </h4>
                                    <p class="text-muted mb-0">Modifiez les informations du service</p>                      
                                </div>
                                <div class="col-auto"> 
                                    <span class="badge badge-soft-primary font-12">
                                        <i data-feather="briefcase" class="icon-xs mr-1"></i>ID: #{{ $service->id }}
                                    </span>
                                </div>
                            </div>                                  
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('services.update', $service->id) }}" class="form-horizontal">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label for="nom">Nom du service <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="tag" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                               name="nom" id="nom" value="{{ old('nom', $service->nom) }}" required>
                                        @error('nom')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="letter_of_service">Lettre de service <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="type" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control @error('letter_of_service') is-invalid @enderror" 
                                               name="letter_of_service" id="letter_of_service" 
                                               value="{{ old('letter_of_service', $service->letter_of_service) }}" 
                                               maxlength="5" style="text-transform: uppercase;" required>
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
                                        Lettre actuelle : <strong class="text-primary">{{ $service->letter_of_service }}</strong> - 
                                        La lettre doit être unique parmi vos services
                                    </small>
                                    <div id="availabilityStatus" class="mt-2" style="display: none;"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="statut">Statut <span class="text-danger">*</span></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light">
                                                        <i data-feather="activity" class="icon-xs"></i>
                                                    </span>
                                                </div>
                                                <select class="form-control @error('statut') is-invalid @enderror" 
                                                        name="statut" id="statut" required>
                                                    <option value="actif" {{ old('statut', $service->statut) == 'actif' ? 'selected' : '' }}>
                                                        ✅ Actif
                                                    </option>
                                                    <option value="inactif" {{ old('statut', $service->statut) == 'inactif' ? 'selected' : '' }}>
                                                        ⏸️ Inactif
                                                    </option>
                                                </select>
                                                @error('statut')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-muted font-weight-semibold">État actuel</label>
                                            <div class="alert alert-light border">
                                                <div class="d-flex align-items-center">
                                                    @if($service->statut == 'actif')
                                                        <i data-feather="check-circle" class="icon-xs text-success mr-2"></i>
                                                        <span class="text-muted">Service: <strong class="text-success">Opérationnel</strong></span>
                                                    @else
                                                        <i data-feather="pause-circle" class="icon-xs text-warning mr-2"></i>
                                                        <span class="text-muted">Service: <strong class="text-warning">Temporairement arrêté</strong></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="file-text" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  name="description" id="description" rows="4" 
                                                  placeholder="Description détaillée du service">{{ old('description', $service->description) }}</textarea>
                                        @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <small class="text-muted">
                                        <i data-feather="info" class="icon-xs mr-1"></i>
                                        Maximum 1000 caractères - 
                                        <span id="charCount">{{ strlen($service->description ?? '') }}</span>/1000
                                    </small>
                                </div>

                                <!-- Informations de création -->
                                <div class="form-group">
                                    <label class="text-muted font-weight-semibold">Informations système</label>
                                    <div class="alert alert-light border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i data-feather="calendar" class="icon-xs text-info mr-2"></i>
                                                    <span class="text-muted">Créé le: <strong class="text-info">{{ $service->created_at->format('d/m/Y à H:i') }}</strong></span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="user" class="icon-xs text-primary mr-2"></i>
                                                    <span class="text-muted">Créé par: <strong class="text-primary">{{ $service->creator->username ?? 'Système' }}</strong></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i data-feather="clock" class="icon-xs text-warning mr-2"></i>
                                                    <span class="text-muted">Modifié le: <strong class="text-warning">{{ $service->updated_at->format('d/m/Y à H:i') }}</strong></span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="briefcase" class="icon-xs text-success mr-2"></i>
                                                    <span class="text-muted">Âge: <strong class="text-success">{{ $service->created_at->diffForHumans() }}</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <div class="row">
                                        <div class="col-6">
                                            <a href="{{ route('service.service-list') }}" class="btn btn-secondary btn-block">
                                                <i data-feather="x" class="icon-xs mr-1"></i>Annuler
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-primary btn-block" type="submit">
                                                <i data-feather="save" class="icon-xs mr-1"></i>Enregistrer les modifications
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div>                               
            </div>

        </div>

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Modification de service par {{ Auth::user()->username }}</span>
        </footer>
    </div>
</div>

<style>
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

.bg-soft-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.icon-lg {
    width: 48px;
    height: 48px;
}

/* Style pour les statuts de disponibilité */
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

/* Compteur de caractères */
#charCount {
    font-weight: bold;
    color: #6c757d;
}

#charCount.text-warning {
    color: #ffc107 !important;
}

#charCount.text-danger {
    color: #dc3545 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Compteur de caractères pour la description
    const descriptionTextarea = document.getElementById('description');
    const charCountElement = document.getElementById('charCount');
    const maxLength = 1000;

    if (descriptionTextarea && charCountElement) {
        function updateCharCount() {
            const currentLength = descriptionTextarea.value.length;
            charCountElement.textContent = currentLength;
            
            // Changer la couleur selon le nombre de caractères
            charCountElement.className = '';
            if (currentLength > maxLength * 0.9) {
                charCountElement.classList.add('text-danger');
            } else if (currentLength > maxLength * 0.75) {
                charCountElement.classList.add('text-warning');
            } else {
                charCountElement.classList.add('text-muted');
            }
        }

        descriptionTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initialiser
    }

    // Gestion de la lettre de service
    const letterInput = document.getElementById('letter_of_service');
    const checkBtn = document.getElementById('checkAvailabilityBtn');

    if (letterInput) {
        letterInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            
            // Vérifier automatiquement après un délai
            clearTimeout(this.checkTimeout);
            this.checkTimeout = setTimeout(() => {
                if (this.value.trim() !== '') {
                    checkLetterAvailability(this.value.trim());
                } else {
                    hideAvailabilityStatus();
                }
            }, 500);
        });
    }

    if (checkBtn) {
        checkBtn.addEventListener('click', function() {
            const letter = letterInput.value.trim();
            if (letter) {
                checkLetterAvailability(letter);
            } else {
                showNotification('Veuillez saisir une lettre à vérifier', 'warning');
            }
        });
    }
});

// Fonction pour vérifier la disponibilité d'une lettre
function checkLetterAvailability(letter) {
    if (!letter || letter.length === 0) return;
    
    const statusDiv = document.getElementById('availabilityStatus');
    const currentServiceId = {{ $service->id }};
    
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
        body: JSON.stringify({ 
            letter: letter,
            exclude_id: currentServiceId 
        })
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

// Validation côté client
document.querySelector('form').addEventListener('submit', function(e) {
    const nom = document.getElementById('nom').value.trim();
    const letter = document.getElementById('letter_of_service').value.trim();
    const statut = document.getElementById('statut').value;

    if (!nom) {
        e.preventDefault();
        showNotification('Le nom du service est obligatoire', 'error');
        return false;
    }

    if (!letter) {
        e.preventDefault();
        showNotification('La lettre de service est obligatoire', 'error');
        return false;
    }

    if (!statut) {
        e.preventDefault();
        showNotification('Veuillez sélectionner un statut', 'error');
        return false;
    }

    // Afficher un message de traitement
    showNotification('Modification du service en cours...', 'info');
});

// Fonction notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove();">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-suppression après 5 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection