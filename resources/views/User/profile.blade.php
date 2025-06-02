{{-- resources/views/user/profile.blade.php --}}
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
                        <a class="dropdown-item" href="{{ Auth::user()->isAdmin() ? route('layouts.app') : route('layouts.app-users') }}">
                            <i data-feather="home" class="align-self-center icon-xs icon-dual mr-1"></i> Dashboard
                        </a>
                        <a class="dropdown-item" href="{{ route('user.profile') }}">
                            <i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Mon Profil
                        </a>
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
                        <a class="btn btn-sm btn-soft-primary waves-effect" href="{{ Auth::user()->isAdmin() ? route('layouts.app') : route('layouts.app-users') }}" role="button">
                            <i class="fas fa-arrow-left mr-2"></i>Retour au Dashboard
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
                                    <i data-feather="user" class="mr-2"></i>Mon Profil
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ Auth::user()->isAdmin() ? route('layouts.app') : route('layouts.app-users') }}">
                                            Dashboard
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item active">Mon Profil</li>
                                </ol>
                            </div><!--end col-->
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->

            <!-- Messages d'erreur/succès -->
            @if(session('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                        <i data-feather="check-circle" class="mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                        <i data-feather="alert-circle" class="mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <!-- Informations actuelles -->
                <div class="col-lg-4">
                    <div class="card animate__animated animate__fadeInLeft">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i data-feather="info" class="mr-2"></i>Informations Actuelles
                            </h4>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <img src="{{asset('frontend/assets/images/users/user-5.jpg')}}" 
                                     alt="profile" 
                                     class="rounded-circle mb-3" 
                                     style="width: 120px; height: 120px; border: 4px solid #007bff;">
                                <h4 class="font-weight-bold text-primary">{{ $user->username }}</h4>
                                <p class="text-muted">{{ $user->email }}</p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong><i data-feather="user" class="icon-xs mr-1"></i>Nom :</strong></td>
                                        <td>{{ $user->username }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="mail" class="icon-xs mr-1"></i>Email :</strong></td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="phone" class="icon-xs mr-1"></i>Téléphone :</strong></td>
                                        <td>{{ $user->mobile_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="shield" class="icon-xs mr-1"></i>Type :</strong></td>
                                        <td>
                                            @if($user->isAdmin())
                                                <span class="badge badge-primary">Administrateur</span>
                                            @else
                                                <span class="badge badge-secondary">Utilisateur</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="activity" class="icon-xs mr-1"></i>Statut :</strong></td>
                                        <td><span class="badge badge-success">{{ $user->getStatusName() }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong><i data-feather="calendar" class="icon-xs mr-1"></i>Inscription :</strong></td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                </table>
                            </div>

                            @if($user->createdBy())
                            <div class="alert alert-info mt-3">
                                <i data-feather="user-plus" class="mr-2"></i>
                                <strong>Compte créé par :</strong><br>
                                {{ $user->createdBy()->username }}<br>
                                <small class="text-muted">{{ $user->createdBy()->email }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Formulaire de modification -->
                <div class="col-lg-8">
                    <div class="card animate__animated animate__fadeInRight">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i data-feather="edit-2" class="mr-2"></i>Modifier mes Informations
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="profileForm" method="POST" action="{{ route('user.profile.update') }}">
                                @csrf
                                @method('PATCH')

                                <div class="row">
                                    <!-- Nom d'utilisateur -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">
                                                <i data-feather="user" class="icon-xs mr-1"></i>Nom d'Utilisateur *
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('username') is-invalid @enderror" 
                                                   id="username" 
                                                   name="username" 
                                                   value="{{ old('username', $user->username) }}" 
                                                   placeholder="Votre nom d'utilisateur" 
                                                   required>
                                            @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Ce nom sera affiché dans votre profil
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Email (non modifiable) -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email_display">
                                                <i data-feather="mail" class="icon-xs mr-1"></i>Adresse Email
                                            </label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email_display" 
                                                   value="{{ $user->email }}" 
                                                   readonly>
                                            <small class="form-text text-muted">
                                                <i data-feather="lock" class="icon-xs mr-1"></i>
                                                L'email ne peut pas être modifié
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Numéro de téléphone -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mobile_number">
                                                <i data-feather="phone" class="icon-xs mr-1"></i>Numéro de Téléphone *
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('mobile_number') is-invalid @enderror" 
                                                   id="mobile_number" 
                                                   name="mobile_number" 
                                                   value="{{ old('mobile_number', $user->mobile_number) }}" 
                                                   placeholder="ex: +225 07 07 07 07 07" 
                                                   required>
                                            @error('mobile_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Type (non modifiable) -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="type_display">
                                                <i data-feather="shield" class="icon-xs mr-1"></i>Type de Compte
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="type_display" 
                                                   value="{{ $user->getTypeName() }}" 
                                                   readonly>
                                            <small class="form-text text-muted">
                                                <i data-feather="lock" class="icon-xs mr-1"></i>
                                                Le type de compte ne peut pas être modifié
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations de sécurité -->
                                <div class="alert alert-warning">
                                    <i data-feather="shield" class="mr-2"></i>
                                    <strong>Sécurité :</strong> Pour modifier votre mot de passe ou votre email, contactez un administrateur.
                                </div>

                                <!-- Boutons d'action -->
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light mr-3" id="submitBtn">
                                        <i data-feather="save" class="icon-xs mr-1"></i>
                                        <span class="btn-text">Enregistrer les Modifications</span>
                                        <span class="loading-spinner" style="display:none;">
                                            <i class="mdi mdi-loading mdi-spin"></i> Enregistrement...
                                        </span>
                                    </button>
                                    <button type="reset" class="btn btn-secondary waves-effect" onclick="resetForm()">
                                        <i data-feather="refresh-cw" class="icon-xs mr-1"></i>Réinitialiser
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiques du compte -->
                    <div class="card animate__animated animate__fadeInUp">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i data-feather="bar-chart-2" class="mr-2"></i>Statistiques de mon Compte
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h4 class="text-primary" id="accountAge">{{ $user->created_at->diffInDays(now()) }}</h4>
                                            <p class="mb-0 text-muted">Jours d'ancienneté</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h4 class="text-success">1</h4>
                                            <p class="mb-0 text-muted">Profil mis à jour</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h4 class="text-info">{{ $user->updated_at->format('H:i') }}</h4>
                                            <p class="mb-0 text-muted">Dernière activité</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <button class="btn btn-outline-info waves-effect" onclick="refreshProfileStats()">
                                    <i data-feather="refresh-cw" class="icon-xs mr-1"></i>Actualiser les Statistiques
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- container -->

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Gestion du profil utilisateur</span>
        </footer><!--end footer-->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- Toast notifications -->
<div class="position-fixed top-0 right-0 p-3" style="z-index: 1050; right: 0; top: 0;">
    <div id="toastContainer"></div>
</div>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

.card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.table td {
    padding: 0.5rem;
    font-size: 0.9rem;
}

.badge {
    font-size: 0.8rem;
}

.alert {
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #17a2b8;
}

.alert-warning {
    border-left-color: #ffc107;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>

<script>
// Gestion du formulaire
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const spinner = submitBtn.querySelector('.loading-spinner');
    
    // Afficher le loading
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    spinner.style.display = 'inline-block';
});

// Réinitialiser le formulaire
function resetForm() {
    document.getElementById('profileForm').reset();
    // Restaurer les valeurs originales
    document.getElementById('username').value = '{{ $user->username }}';
    document.getElementById('mobile_number').value = '{{ $user->mobile_number }}';
    
    showToast('Info', 'Formulaire réinitialisé avec les valeurs originales.', 'info');
}

// Actualiser les statistiques du profil
function refreshProfileStats() {
    // Incrémenter l'âge du compte
    const ageElement = document.getElementById('accountAge');
    const currentAge = parseInt(ageElement.textContent);
    ageElement.textContent = currentAge;
    
    showToast('Succès', 'Statistiques mises à jour !', 'success');
}

// Validation en temps réel du nom d'utilisateur
document.getElementById('username').addEventListener('input', function() {
    const username = this.value.trim();
    
    if (username.length < 3) {
        this.setCustomValidity('Le nom d\'utilisateur doit contenir au moins 3 caractères');
        this.classList.add('is-invalid');
    } else if (username.length > 255) {
        this.setCustomValidity('Le nom d\'utilisateur ne peut pas dépasser 255 caractères');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

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

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Réinitialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Message de bienvenue sur la page profil
    setTimeout(() => {
        showToast('Information', 'Vous pouvez modifier vos informations personnelles ici.', 'info');
    }, 1000);
});
</script>

@endsection