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
                                        • Vous pouvez <strong>changer son type</strong>, son <strong>statut</strong> et son <strong>agence</strong><br>
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
                            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="form-horizontal">
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
                                            <label for="user_role">Type d'utilisateur</label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light">
                                                        <i data-feather="shield" class="icon-xs"></i>
                                                    </span>
                                                </div>
                                                <select class="form-control @error('user_role') is-invalid @enderror" 
                                                        name="user_role" id="user_role" required>
                                                    @if($user->isAdmin())
                                                        <option value="admin" selected>🛡️ Administrateur</option>
                                                    @endif
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
                                                @error('user_role')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
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

                                <!-- NOUVEAU CHAMP : Agence -->
                                <div class="form-group">
                                    <label for="agency_id">Agence <small class="text-muted">(optionnel)</small></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i data-feather="home" class="icon-xs"></i>
                                            </span>
                                        </div>
                                        <select class="form-control @error('agency_id') is-invalid @enderror" 
                                                name="agency_id" id="agency_id">
                                            <option value="">-- Aucune agence assignée --</option>
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
                                        @if($agencies->isEmpty())
                                            Aucune agence n'a été créée.
                                        @else
                                            Sélectionnez l'agence où travaille cet utilisateur
                                        @endif
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
</script>
@endsection