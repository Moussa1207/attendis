
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
            </ul><!--end topbar-nav-->

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
                                    <i data-feather="user-plus" class="mr-2"></i>Créer un nouvel utilisateur
                                </h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('user.users-list') }}">Utilisateurs</a></li>
                                    <li class="breadcrumb-item active">Créer</li>
                                </ol>
                            </div><!--end col-->
                        </div><!--end row-->                                                              
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->

            <!-- Formulaire de création -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card animate__animated animate__fadeInUp">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i data-feather="user-plus" class="mr-2"></i>Informations de l'utilisateur
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Messages d'erreur/succès -->
                            @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i data-feather="check-circle" class="mr-2"></i>{{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif

                            @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i data-feather="alert-circle" class="mr-2"></i>{{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif

                            <form id="createUserForm" method="POST" action="{{ route('admin.users.store') }}">
                                @csrf
                                    <!-- Email -->
                                    <div class="form-group">
                                                <label for="email">E-mail</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}" placeholder="Votre e-mail" required>
                                                    @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                    <div class="form-group">
                                                <label for="register_username">Nom </label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" id="register_username" value="{{ old('username') }}" placeholder="ex: Awa Konan" required>
                                                    @error('username')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>
                                    
                                            <div class="form-group">
                                                <label for="mo_number">Téléphone</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" name="mobile_number" id="mo_number" value="{{ old('mobile_number') }}" placeholder="ex: 0707000000" required>
                                                    @error('mobile_number')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>


                                 <div class="form-group">
                                                <label for="register_password">Mot de passe</label>
                                                <div class="input-group mb-3 password-toggle">
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="register_password" placeholder="Votre mot de passe" required>
                                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('register_password', this)">
                                                        <i class="fas fa-eye" id="toggleIcon-register_password"></i>
                                                    </button>
                                                    @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="password-confirm">Confirmer le mot de passe</label>
                                                <div class="input-group mb-3 password-toggle">
                                                    <input type="password" class="form-control" name="password_confirmation" id="password-confirm" placeholder="Confirmer votre mot de passe" required>
                                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password-confirm', this)">
                                                        <i class="fas fa-eye" id="toggleIcon-password-confirm"></i>
                                                    </button>
                                                </div>
                                            </div>

                                <!-- Informations supplémentaires -->
                                <div class="alert alert-info">
                                    <i data-feather="info" class="mr-2"></i>
                                    <strong>Note :</strong> L'utilisateur sera créé avec le statut <strong>"Actif"</strong> par défaut et pourra se connecter immédiatement.
                                </div>

                                <!-- Boutons d'action -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group text-center">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-3" id="submitBtn">
                                                <i data-feather="user-plus" class="icon-xs mr-1"></i>
                                                <span class="btn-text">Créer l'utilisateur</span>
                                                <span class="loading-spinner" style="display:none;">
                                                    <i class="mdi mdi-loading mdi-spin"></i> Création...
                                                </span>
                                            </button>
                                            <a href="{{ route('user.users-list') }}" class="btn btn-secondary waves-effect">
                                                <i data-feather="x" class="icon-xs mr-1"></i>Annuler
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- container -->

        <footer class="footer text-center text-sm-left">
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Création d'utilisateurs</span>
        </footer><!--end footer-->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<script>
// Fonction pour basculer l'affichage du mot de passe
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById('toggleIcon-' + inputId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    
    // Réinitialiser Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }



// Gestion du formulaire
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const spinner = submitBtn.querySelector('.loading-spinner');
    
    // Afficher le loading
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    spinner.style.display = 'inline-block';
});

// Validation en temps réel
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (confirmation && password !== confirmation) {
        this.setCustomValidity('Les mots de passe ne correspondent pas');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Initialiser Feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

@endsection