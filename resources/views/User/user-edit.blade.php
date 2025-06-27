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
                        <a class="btn btn-sm btn-soft-primary waves-effect" href="{{ route('user.users-list') }}" role="button">
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
                                    <i data-feather="edit" class="mr-2"></i>Modifier l'utilisateur
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('user.users-list') }}">Utilisateurs</a></li>
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
                                        • Vous modifiez l'utilisateur : <strong>{{ $user->username }}</strong><br>
                                        • Vous pouvez <strong>changer son type</strong>, son <strong>statut</strong> et ses <strong>informations</strong><br>
                                        • Les changements sont <strong>appliqués immédiatement</strong><br>
                                        • Le mot de passe n'est <strong>pas modifié</strong> ici
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
                                        <i data-feather="edit-3" class="mr-2"></i>Informations utilisateur
                                    </h4>
                                    <p class="text-muted mb-0">Modifiez les informations de l'utilisateur</p>                      
                                </div>
                                <div class="col-auto"> 
                                    <span class="badge badge-soft-primary font-12">
                                        <i data-feather="user" class="icon-xs mr-1"></i>ID: #{{ $user->id }}
                                    </span>
                                </div>
                            </div>                                  
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('User.user.update', $user->id) }}" class="form-horizontal">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="mail" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" id="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="username">Nom</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="user" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                               name="username" id="username" value="{{ old('username', $user->username) }}" required>
                                        @error('username')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="mobile_number">Téléphone</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="phone" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" 
                                               name="mobile_number" id="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}" required>
                                        @error('mobile_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="company">Entreprise <small class="text-muted">(optionnel)</small></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="briefcase" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control @error('company') is-invalid @enderror" 
                                               name="company" id="company" value="{{ old('company', $user->company) }}" 
                                               placeholder="Nom de l'entreprise">
                                        @error('company')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="user_type_display">Type d'utilisateur</label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light">
                                                        <i data-feather="shield" class="icon-xs"></i>
                                                    </span>
                                                </div>
                                                <!-- ✅ CORRIGÉ : Affichage en lecture seule du type -->
                                                <input type="text" 
                                                       class="form-control bg-light" 
                                                       id="user_type_display" 
                                                       value="{{ $user->isAdmin() ? '🛡️ Administrateur' : ($user->user_type_id == 2 ? '🖥️ Ecran' : ($user->user_type_id == 3 ? '🏢 Accueil' : ($user->user_type_id == 4 ? '👥 Conseiller' : '👤 Utilisateur'))) }}" 
                                                       readonly>
                                                
                                                <!-- Champ caché pour envoyer le type actuel -->
                                                <input type="hidden" name="user_role" value="{{ $user->user_type_id == 2 ? 'ecran' : ($user->user_type_id == 3 ? 'accueil' : ($user->user_type_id == 4 ? 'conseiller' : 'admin')) }}">
                                            </div>
                                            <small class="text-muted">
                                                <i data-feather="info" class="icon-xs mr-1"></i>
                                                Le type d'utilisateur est défini lors de la création. 
                                                @if(!$user->isAdmin())
                                                <a href="#" class="text-primary" onclick="toggleUserTypeEdit()">Cliquez ici pour le modifier</a>
                                                @endif
                                            </small>
                                            
                                            @if(!$user->isAdmin())
                                            <!-- ✅ NOUVEAU : Section modification de type (masquée par défaut) -->
                                            <div id="user_type_edit_section" style="display: none;" class="mt-3 p-3 border border-warning rounded bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i data-feather="alert-triangle" class="icon-xs text-warning mr-2"></i>
                                                    <strong class="text-warning">Modification du type d'utilisateur</strong>
                                                </div>
                                                <p class="text-muted mb-3">
                                                    <strong>Attention :</strong> Changer le type d'utilisateur peut affecter ses permissions et accès.
                                                </p>
                                                <select class="form-control" name="new_user_role" id="new_user_role">
                                                    <option value="ecran" {{ $user->user_type_id == 2 ? 'selected' : '' }}>
                                                        🖥️ Ecran 
                                                    </option>
                                                    <option value="accueil" {{ $user->user_type_id == 3 ? 'selected' : '' }}>
                                                        🏢 Accueil 
                                                    </option>
                                                    <option value="conseiller" {{ $user->user_type_id == 4 ? 'selected' : '' }}>
                                                        👥 Conseiller 
                                                    </option>
                                                </select>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="cancelUserTypeEdit()">
                                                        <i data-feather="x" class="icon-xs mr-1"></i>Annuler
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="confirmUserTypeEdit()">
                                                        <i data-feather="check" class="icon-xs mr-1"></i>Confirmer le changement
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Statut</label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light">
                                                        <i data-feather="activity" class="icon-xs"></i>
                                                    </span>
                                                </div>
                                                <select class="form-control @error('status') is-invalid @enderror" 
                                                        name="status" id="status" required>
                                                    <option value="active" {{ $user->status_id == 2 ? 'selected' : '' }}>
                                                        ✅ Actif
                                                    </option>
                                                    <option value="inactive" {{ $user->status_id == 1 ? 'selected' : '' }}>
                                                        ⏳ Inactif
                                                    </option>
                                                    <option value="suspended" {{ $user->status_id == 3 ? 'selected' : '' }}>
                                                        ❌ Suspendu
                                                    </option>
                                                </select>
                                                @error('status')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ✅ AJOUTÉ : Champ Agence (optionnel) -->
                                <div class="form-group">
                                    <label for="agency_id">
                                        <i data-feather="home" class="icon-xs mr-1"></i>
                                        Agence assignée <small class="text-muted">(optionnel)</small>
                                    </label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="home" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <select class="form-control @error('agency_id') is-invalid @enderror" 
                                                name="agency_id" id="agency_id">
                                            <option value=""> Aucune agence</option>
                                            @forelse($agencies as $agency)
                                                <option value="{{ $agency->id }}" 
                                                        {{ old('agency_id', $user->agency_id) == $agency->id ? 'selected' : '' }}>
                                                    {{ $agency->name }} - {{ $agency->city }}
                                                </option>
                                            @empty
                                                <option value="" disabled>Aucune agence disponible</option>
                                            @endforelse
                                        </select>
                                        @error('agency_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <small class="text-muted">
                                        <i data-feather="info" class="icon-xs mr-1"></i>
                                        @if($user->agency)
                                            Actuellement assigné à : <strong class="text-primary">{{ $user->agency->name }}</strong>
                                        @else
                                            Aucune agence actuellement assignée
                                        @endif
                                        @if($agencies->isEmpty())
                                            <br>⚠️ Aucune agence disponible dans le système.
                                        @else
                                            <br>Sélectionnez l'agence où travaille cet utilisateur
                                        @endif
                                    </small>
                                </div>

                                <!-- ✅ SUPPRIMÉ : Le champ agence n'est plus nécessaire pour les admins -->

                                <!-- Informations de création -->
                                <div class="form-group">
                                    <label class="text-muted font-weight-semibold">Informations système</label>
                                    <div class="alert alert-light border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i data-feather="calendar" class="icon-xs text-info mr-2"></i>
                                                    <span class="text-muted">Créé le: <strong class="text-info">{{ $user->created_at->format('d/m/Y à H:i') }}</strong></span>
                                                </div>
                                                @if($user->wasCreatedByAdmin())
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="user" class="icon-xs text-primary mr-2"></i>
                                                    <span class="text-muted">Créé par: <strong class="text-primary">{{ $user->getCreator()->username ?? 'Admin' }}</strong></span>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i data-feather="clock" class="icon-xs text-warning mr-2"></i>
                                                    <span class="text-muted">Modifié le: <strong class="text-warning">{{ $user->updated_at->format('d/m/Y à H:i') }}</strong></span>
                                                </div>
                                                @if($user->last_login_at)
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="log-in" class="icon-xs text-success mr-2"></i>
                                                    <span class="text-muted">Dernière connexion: <strong class="text-success">{{ $user->last_login_at->diffForHumans() }}</strong></span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <div class="row">
                                        <div class="col-6">
                                            <a href="{{ route('user.users-list') }}" class="btn btn-secondary btn-block">
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
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Modification d'utilisateur par {{ Auth::user()->username }}</span>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// ✅ NOUVEAU : Gestion de la modification du type d'utilisateur
function toggleUserTypeEdit() {
    const editSection = document.getElementById('user_type_edit_section');
    const currentDisplay = editSection.style.display;
    
    if (currentDisplay === 'none' || currentDisplay === '') {
        editSection.style.display = 'block';
        editSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        editSection.style.display = 'none';
    }
}

function cancelUserTypeEdit() {
    const editSection = document.getElementById('user_type_edit_section');
    editSection.style.display = 'none';
    
    // Remettre la valeur par défaut
    const select = document.getElementById('new_user_role');
    const hiddenInput = document.querySelector('input[name="user_role"]');
    select.value = hiddenInput.value;
}

function confirmUserTypeEdit() {
    const newRole = document.getElementById('new_user_role').value;
    const hiddenInput = document.querySelector('input[name="user_role"]');
    const displayInput = document.getElementById('user_type_display');
    
    // Confirmer le changement
    if (confirm('Êtes-vous sûr de vouloir changer le type de cet utilisateur ?')) {
        // Mettre à jour les valeurs
        hiddenInput.value = newRole;
        
        // Mettre à jour l'affichage
        const roleLabels = {
            'ecran': '🖥️ Ecran',
            'accueil': '🏢 Accueil', 
            'conseiller': '👥 Conseiller'
        };
        
        displayInput.value = roleLabels[newRole] || newRole;
        
        // Masquer la section d'édition
        document.getElementById('user_type_edit_section').style.display = 'none';
        
        // Afficher un message de confirmation
        showNotification('Type d\'utilisateur modifié. N\'oubliez pas de sauvegarder !', 'warning');
    }
}

// ✅ FONCTION UTILITAIRE : Afficher une notification
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