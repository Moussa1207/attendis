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
                                        <i data-feather="user-plus" class="align-self-center icon-xs"></i>
                                    </div>
                                    <div class="media-body align-self-center ml-2 text-truncate">
                                        <h6 class="my-0 font-weight-normal text-dark">Création d'utilisateur</h6>
                                        <small class="text-muted mb-0">Prêt à créer un nouvel utilisateur</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <a href="{{ route('user.users-list') }}" class="dropdown-item text-center text-primary">
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

            <!-- ALERTE PRINCIPALE POUR LE MOT DE PASSE -->
            @if(isset($temporaryPassword))
            <div class="row">
                <div class="col-lg-12">
                    <div class="alert alert-warning alert-dismissible fade show animate__animated animate__pulse" role="alert">
                        <h5><i data-feather="key" class="mr-2"></i>Identifiants générés pour l'utilisateur</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                @if(isset($newUser))
                                    <p class="mb-2"><strong>👤 Utilisateur :</strong> {{ $newUser->username }}</p>
                                    <p class="mb-2"><strong>📧 Email :</strong> {{ $newUser->email }}</p>
                                    <p class="mb-2"><strong>📱 Téléphone :</strong> {{ $newUser->mobile_number }}</p>
                                    <!-- AMÉLIORATION 3 : SUPPRESSION affichage entreprise -->
                                @else
                                    <p class="mb-2"><strong>👤 Utilisateur :</strong> Utilisateur créé</p>
                                    <p class="mb-2"><strong>📧 Email :</strong> Voir dans la liste</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>🔐 Mot de passe temporaire :</strong></p>
                                <div class="d-flex align-items-center">
                                    <code style="font-size: 18px; background: #f8f9fa; padding: 8px 15px; border: 2px solid #dee2e6; border-radius: 4px; font-family: monospace; letter-spacing: 2px; font-weight: bold;">{{ $temporaryPassword }}</code>
                                    <button class="btn btn-sm btn-outline-secondary ml-3" onclick="copyPassword()" title="Copier le mot de passe">
                                        <i data-feather="copy" class="mr-1"></i>Copier
                                    </button>
                                    @if(isset($newUser))
                                    <button class="btn btn-sm btn-outline-info ml-2" onclick="copyAllCredentials()" title="Copier tous les identifiants">
                                        <i data-feather="clipboard" class="mr-1"></i>Tout copier
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr>
                        <small class="text-muted">
                            <i data-feather="info" class="mr-1"></i>
                            <strong>⚠️ Important :</strong> Communiquez ces identifiants à l'utilisateur pour sa première connexion. L'utilisateur devra changer son mot de passe lors de sa première connexion.
                        </small>
                        <button type="button" class="close" data-dismiss="alert" title="Fermer (après avoir noté les identifiants)">
                            <span>&times;</span>
                        </button>
                    </div>
                </div>
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
                                        • L'utilisateur sera créé avec le <strong>statut actif</strong> par défaut<br>
                                        • Un <strong>mot de passe temporaire simple</strong> sera généré automatiquement (format : cvc123)<br>
                                        • L'utilisateur pourra <strong>modifier son mot de passe</strong> lors de sa première connexion<br>
                                        • Vous serez enregistré comme <strong>créateur</strong> de cet utilisateur<br>
                                        <!-- AMÉLIORATION 3 : SUPPRESSION mention entreprise -->
                                        • Les <strong>notes de création</strong> vous aideront à identifier cet utilisateur
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
                                        <i data-feather="edit-3" class="mr-2"></i>Informations utilisateur
                                    </h4>
                                    <p class="text-muted mb-0">Saisissez les informations du nouvel utilisateur</p>                      
                                </div><!--end col-->
                                <div class="col-auto"> 
                                    <span class="badge badge-soft-primary font-12">
                                        <i data-feather="shield" class="icon-xs mr-1"></i>Création d'un utilisateur
                                    </span>
                                </div><!--end col-->
                            </div>  <!--end row-->                                  
                        </div><!--end card-header-->
                        <div class="card-body">
                            <div class="tab-pane px-3 pt-3" id="Create_User_Tab" role="tabpanel">
                                <form method="POST" action="{{ route('admin.users.store') }}" class="form-horizontal auth-form my-4" id="createUserForm">
                                    @csrf

                                    <div class="form-group">
                                        <label for="useremail">Email</label>
                                        <div class="input-group mb-3">
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="useremail" value="{{ old('email') }}" placeholder="Votre e-mail" required>
                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="register_username">Nom</label>
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

                                    <!-- AMÉLIORATION 3 : SUPPRESSION DU CHAMP ENTREPRISE -->

                                    <div class="form-group">
                                       <label for="user_role">Type d'utilisateur <span class="text-danger">*</span></label>
                                      <div class="input-group mb-3">
                                             <div class="input-group-prepend">
                                               <span class="input-group-text bg-light">
                                                <i data-feather="briefcase" class="icon-xs"></i>
                                               </span>
                                            </div>
                                <select class="form-control @error('user_role') is-invalid @enderror" 
                                name="user_role" 
                                id="user_role" 
                                required>
            <option value="">-- Sélectionnez le type --</option>
            <option value="ecran" {{ old('user_role') == 'ecran' ? 'selected' : '' }}>
                🖥️ Ecran
            </option>
            <option value="accueil" {{ old('user_role') == 'accueil' ? 'selected' : '' }}>
                🏢 Accueil
            </option>
            <option value="conseiller" {{ old('user_role') == 'conseiller' ? 'selected' : '' }}>
                👥 Conseiller
            </option>
            
        </select>
        @error('user_role')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
                     @enderror
                   </div>
                      <small class="text-muted">
                          <i data-feather="info" class="icon-xs mr-1"></i>
                           Sélectionnez le poste de travail de cet utilisateur
                      </small>
                   </div>

                                    <!-- Informations automatiques -->
                                    <div class="form-group">
                                        <label class="text-muted font-weight-semibold">Paramètres automatiques</label>
                                        <div class="alert alert-light border">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="check-circle" class="icon-xs text-success mr-2"></i>
                                                        <span class="text-muted">Statut: <strong class="text-success">Actif</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="key" class="icon-xs text-warning mr-2"></i>
                                                        <span class="text-muted">Mot de passe: <strong class="text-warning">Format simple</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="user" class="icon-xs text-info mr-2"></i>
                                                        <span class="text-muted">Créé par: <strong class="text-info">{{ Auth::user()->username }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-4">
                                        <div class="col-sm-12">
                                            <div class="custom-control custom-switch switch-success">
                                                <input type="checkbox" class="custom-control-input" id="customSwitchSuccess" checked disabled>
                                                <label class="custom-control-label text-muted" for="customSwitchSuccess">L'utilisateur accepte automatiquement<a href="#" class="text-primary"> les conditions d'utilisation</a></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 row">
                                        <div class="col-12 mt-2">
                                            <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">Créer l'utilisateur <i class="fas fa-user-plus ml-1"></i></button>
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
            &copy; {{ date('Y') }} Attendis <span class="d-none d-sm-inline-block float-right">Création d'utilisateurs par {{ Auth::user()->username }}</span>
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
                    <i data-feather="help-circle" class="icon-xs mr-2"></i>Aide - Création d'Utilisateur
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>📋 Processus de Création</h6>
                <ol class="pl-3">
                    <li>Saisissez les informations obligatoires (email, nom, téléphone)</li>
                    <li>Ajoutez des notes de création pour identifier l'utilisateur</li>
                    <li>Un mot de passe simple sera généré automatiquement (format : consonne-voyelle-consonne + 3 chiffres)</li>
                    <li>Les identifiants s'afficheront après la création</li>
                    <li>L'utilisateur pourra modifier son mot de passe à la première connexion</li>
                </ol>
                
                <h6 class="mt-3">🔐 Format du mot de passe</h6>
                <ul class="pl-3">
                    <li>Format simple : <strong>cvc123</strong> (consonne-voyelle-consonne + 3 chiffres)</li>
                    <li>Exemple : <code>bad457</code>, <code>kot892</code></li>
                    <li>Facile à retenir et à communiquer</li>
                </ul>

                <!-- AMÉLIORATION 3 : SUPPRESSION section entreprise -->

                <h6 class="mt-3">📝 Notes de création</h6>
                <ul class="pl-3">
                    <li>Optionnelles mais recommandées pour identifier l'utilisateur</li>
                    <li>Exemples : "Service comptabilité", "Stagiaire été 2025", "Client VIP"</li>
                    <li>Visibles dans la liste de vos utilisateurs créés</li>
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

/* Style pour le mot de passe */
code {
    color: #e83e8c !important;
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
});

// Afficher l'aide
function showHelp() {
    $('#helpModal').modal('show');
}

// Copier le mot de passe uniquement
function copyPassword() {
    const password = "{{ isset($temporaryPassword) ? $temporaryPassword : '' }}";
    if (password) {
        navigator.clipboard.writeText(password).then(() => {
            showToast('Succès', 'Mot de passe copié dans le presse-papier !', 'success');
        }).catch(() => {
            // Fallback pour les navigateurs plus anciens
            const tempInput = document.createElement('input');
            tempInput.value = password;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            showToast('Succès', 'Mot de passe copié !', 'success');
        });
    }
}

// Copier tous les identifiants (SANS company)
function copyAllCredentials() {
    const email = "{{ isset($newUser) ? $newUser->email : '' }}";
    const password = "{{ isset($temporaryPassword) ? $temporaryPassword : '' }}";
    const username = "{{ isset($newUser) ? $newUser->username : '' }}";
    const phone = "{{ isset($newUser) ? $newUser->mobile_number : '' }}";
    
    if (email && password) {
        const credentials = `Identifiants de connexion pour ${username}:

👤 Nom: ${username}
📧 Email: ${email}
📱 Téléphone: ${phone}
🔐 Mot de passe temporaire: ${password}

⚠️ Important: L'utilisateur doit changer ce mot de passe lors de sa première connexion.

Créé le {{ isset($newUser) ? $newUser->created_at->format('d/m/Y à H:i') : '' }} par {{ Auth::user()->username }}`;
        
        navigator.clipboard.writeText(credentials).then(() => {
            showToast('Succès', 'Tous les identifiants copiés !', 'success');
        }).catch(() => {
            showToast('Erreur', 'Impossible de copier automatiquement', 'error');
        });
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
</script>

@endsection