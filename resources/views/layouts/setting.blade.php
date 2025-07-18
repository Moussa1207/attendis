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
                        <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" alt="profile-user" class="rounded-circle" />                                 
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#"><i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Profil</a>
                        <a class="dropdown-item" href="{{ route('layouts.setting') }}"><i data-feather="settings" class="align-self-center icon-xs icon-dual mr-1"></i> Paramètres</a>
                        <div class="dropdown-divider mb-0"></div>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="dropdown-item"><i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Déconnexion</button>
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
                        <a class="btn btn-sm btn-soft-primary" href="{{ route('User.user-create') }}" role="button">
                            <i class="fas fa-plus mr-2"></i>Nouvel Utilisateur
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
                                <h4 class="page-title">Paramètre Général</h4>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('layouts.app') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Paramètre Général</li>
                                </ol>
                            </div>
                            <div class="col-auto align-self-center">
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="resetSettings()">
                                    <i data-feather="refresh-cw" class="align-self-center icon-xs mr-1"></i>
                                    Réinitialiser
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="clearCache()">
                                    <i data-feather="database" class="align-self-center icon-xs mr-1"></i>
                                    Vider Cache
                                </button>
                                
                            </div>
                        </div>                                                              
                    </div>
                </div>
            </div>

            <!-- Messages d'alerte -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert mr-2"></i>{{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle mr-2"></i>
                    <strong>Erreurs de validation :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <!-- Formulaire des paramètres -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Gestion des Utilisateurs</h4>
                            <p class="text-muted mb-0">Configurez le comportement des différents types d'utilisateurs</p>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm" method="POST" action="{{ route('layouts.setting.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Colonne unique avec tous les paramètres alignés verticalement -->
                                    <div class="col-md-8 mx-auto">
                                        <!-- 1. Détection automatique des conseillers -->
                                        <div class="form-group mb-4">
                                            <!-- Input hidden pour garantir l'envoi de la valeur -->
                                            <input type="hidden" name="auto_detect_available_advisors" value="0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="auto_detect_available_advisors"
                                                       name="auto_detect_available_advisors"
                                                       value="1"
                                                       {{ (isset($userManagementSettings['auto_detect_available_advisors']) && $userManagementSettings['auto_detect_available_advisors']->formatted_value) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="auto_detect_available_advisors">
                                                    <strong>Détection automatique des conseillers disponibles</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Lorsqu'activée, cette option permet de détecter automatiquement les conseillers disponibles dès leur connexion à la plateforme. Cela facilite une meilleure répartition des services.
                                            </small>
                                        </div>

                                        <!-- 2. Attribution automatique des services -->
                                        <div class="form-group mb-4">
                                            <!-- Input hidden pour garantir l'envoi de la valeur -->
                                            <input type="hidden" name="auto_assign_all_services_to_advisors" value="0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="auto_assign_all_services_to_advisors"
                                                       name="auto_assign_all_services_to_advisors"
                                                       value="1"
                                                       {{ (isset($userManagementSettings['auto_assign_all_services_to_advisors']) && $userManagementSettings['auto_assign_all_services_to_advisors']->formatted_value) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="auto_assign_all_services_to_advisors">
                                                    <strong>Attribution automatique de tous les services à tous les conseillers</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Si cette option est activée, chaque conseiller aura accès à tous les services par défaut. Sinon, les services devront être attribués manuellement.
                                            </small>
                                        </div>

                                        <!-- 3. Fermeture automatique des sessions -->
                                        <div class="form-group mb-4">
                                            <!-- Input hidden pour garantir l'envoi de la valeur -->
                                            <input type="hidden" name="enable_auto_session_closure" value="0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="enable_auto_session_closure"
                                                       name="enable_auto_session_closure"
                                                       value="1"
                                                       onchange="toggleSessionClosureTime()"
                                                       {{ (isset($userManagementSettings['enable_auto_session_closure']) && $userManagementSettings['enable_auto_session_closure']->formatted_value) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="enable_auto_session_closure">
                                                    <strong>Fermeture automatique des sessions</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Permet d'activer la fermeture automatique des sessions utilisateurs après une certaine heure.
                                            </small>

                                            <!-- Champ conditionnel pour l'heure directement en dessous -->
                                            <div id="session_closure_time_group" 
                                                 class="mt-3" 
                                                 style="display: {{ (isset($userManagementSettings['enable_auto_session_closure']) && $userManagementSettings['enable_auto_session_closure']->formatted_value) ? 'block' : 'none' }};">
                                                <div class="card bg-light">
                                                    <div class="card-body py-3">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-5">
                                                                <label for="auto_session_closure_time" class="form-label mb-0">
                                                                    <i class="mdi mdi-clock-outline mr-2"></i>
                                                                    <strong>Heure de fermeture</strong>
                                                                </label>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="mdi mdi-clock"></i></span>
                                                                    </div>
                                                                    <input type="time" 
                                                                           class="form-control @error('auto_session_closure_time') is-invalid @enderror"
                                                                           id="auto_session_closure_time"
                                                                           name="auto_session_closure_time"
                                                                           value="{{ isset($userManagementSettings['auto_session_closure_time']) ? $userManagementSettings['auto_session_closure_time']->value : '18:00' }}"
                                                                           {{ (isset($userManagementSettings['enable_auto_session_closure']) && $userManagementSettings['enable_auto_session_closure']->formatted_value) ? 'required' : '' }}>
                                                                </div>
                                                                @error('auto_session_closure_time')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-md-5">
                                                                <small class="form-text text-muted mb-0">
                                                                    <i class="mdi mdi-information-outline mr-1"></i>
                                                                    Heure à laquelle toutes les sessions utilisateurs seront automatiquement fermées (format 24h).
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ✅ 4. NOUVEAU PARAMÈTRE : Temps d'attente configurable (File d'attente unique) -->
                                        <div class="form-group mb-4">
                                            <div class="card bg-light border-info">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0">
                                                        <i class="mdi mdi-clock-outline mr-2"></i>
                                                        <strong>Configuration de la file d'attente unique</strong>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-5">
                                                            <label for="default_waiting_time_minutes" class="form-label mb-0">
                                                                <strong>Temps d'attente par défaut (minutes)</strong>
                                                            </label>
                                                            <small class="form-text text-muted">
                                                                Durée estimée entre chaque ticket dans la file d'attente unique
                                                            </small>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input type="number" 
                                                                       class="form-control @error('default_waiting_time_minutes') is-invalid @enderror"
                                                                       id="default_waiting_time_minutes"
                                                                       name="default_waiting_time_minutes"
                                                                       value="{{ isset($userManagementSettings['default_waiting_time_minutes']) ? $userManagementSettings['default_waiting_time_minutes']->value : '5' }}"
                                                                       min="1"
                                                                       max="60"
                                                                       step="1"
                                                                       required>
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">min</span>
                                                                </div>
                                                            </div>
                                                            @error('default_waiting_time_minutes')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="alert alert-info mb-0 py-2">
                                                                <small>
                                                                    <i class="mdi mdi-information-outline mr-1"></i>
                                                                    <strong>Important :</strong> Ce paramètre détermine le temps d'attente calculé pour chaque position dans la file unique.
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Boutons d'action - Style moderne -->
                                <div class="form-group mt-4 pt-3">
                                    <div class="row">
                                        <div class="col-md-8 mx-auto">
                                            <!-- Bouton Enregistrer - Style principal -->
                                            <div class="mb-3">
                                                <button type="submit" class="btn btn-modern-primary w-100 py-1" id="saveBtn">
                                                    <span class="btn-text-modern">Enregistrer les paramètres</span>
                                                    <i class="mdi mdi-content-save ml-2"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Bouton Retour - Style secondaire -->
                                            <div class="mb-2">
                                                <button type="button" class="btn btn-modern-secondary w-100 py-1" onclick="window.history.back()">
                                                    <span class="btn-text-modern">Retour </span>
                                                    <i class="mdi mdi-arrow-left ml-2"></i>
                                                </button>
                                            </div>
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
            &copy; 2024 Système de Gestion
        </footer>
    </div>
</div>

<style>
/* Styles pour les boutons modernes */
.btn-modern-primary {
    background: linear-gradient(135deg, #4f7cf7 0%, #4263eb 100%);
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(79, 124, 247, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-modern-primary:hover {
    background: linear-gradient(135deg, #3b66f0 0%, #3451d9 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 124, 247, 0.4);
    color: white;
}

.btn-modern-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(79, 124, 247, 0.3);
}

.btn-modern-primary:focus {
    box-shadow: 0 0 0 3px rgba(79, 124, 247, 0.3);
    outline: none;
    color: white;
}

.btn-modern-secondary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #dee2e6;
    border-radius: 12px;
    color: #495057;
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.btn-modern-secondary:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    border-color: #adb5bd;
    color: #343a40;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-modern-secondary:active {
    transform: translateY(0);
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.1);
}

.btn-modern-secondary:focus {
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
    outline: none;
    color: #343a40;
}

.btn-text-modern {
    display: inline-block;
    position: relative;
}

/* Animation de chargement pour le bouton Enregistrer */
.btn-modern-primary.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-modern-primary.loading .btn-text-modern {
    opacity: 0.7;
}

.btn-modern-primary.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Animation d'apparition */
.btn-modern-primary,
.btn-modern-secondary {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .btn-modern-primary,
    .btn-modern-secondary {
        font-size: 14px;
        padding: 12px 20px !important;
    }
    
    .btn-text-modern {
        font-size: 14px;
    }
}

/* Effet de ripple au clic */
.btn-modern-primary::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-modern-primary:active::before {
    width: 300px;
    height: 300px;
}
</style>

<script>
// Variables globales
let isSubmitting = false;

// Toggle l'affichage du champ d'heure de fermeture avec animation améliorée
function toggleSessionClosureTime() {
    const checkbox = document.getElementById('enable_auto_session_closure');
    const timeGroup = document.getElementById('session_closure_time_group');
    const timeInput = document.getElementById('auto_session_closure_time');
    
    if (checkbox.checked) {
        timeGroup.style.display = 'block';
        timeGroup.style.opacity = '0';
        timeGroup.style.transform = 'translateY(-10px)';
        timeInput.setAttribute('required', 'required');
        
        setTimeout(() => {
            timeGroup.style.transition = 'all 0.4s ease-in-out';
            timeGroup.style.opacity = '1';
            timeGroup.style.transform = 'translateY(0)';
        }, 10);
    } else {
        timeGroup.style.transition = 'all 0.4s ease-in-out';
        timeGroup.style.opacity = '0';
        timeGroup.style.transform = 'translateY(-10px)';
        timeInput.removeAttribute('required');
        
        setTimeout(() => {
            timeGroup.style.display = 'none';
            timeGroup.style.transform = 'translateY(0)';
            // Réinitialiser à la valeur par défaut
            timeInput.value = '18:00';
        }, 400);
    }
}

// Validation côté client améliorée
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }

    const sessionClosure = document.getElementById('enable_auto_session_closure').checked;
    const closureTime = document.getElementById('auto_session_closure_time').value;
    
    // ✅ NOUVEAU : Validation du temps d'attente
    const waitingTime = document.getElementById('default_waiting_time_minutes').value;
    const waitingTimeInt = parseInt(waitingTime);
    
    if (!waitingTime || waitingTimeInt < 1 || waitingTimeInt > 60) {
        e.preventDefault();
        showAlert('Le temps d\'attente doit être entre 1 et 60 minutes.', 'warning');
        document.getElementById('default_waiting_time_minutes').focus();
        return false;
    }
    
    // Validation de l'heure si la fermeture auto est activée
    if (sessionClosure && !closureTime) {
        e.preventDefault();
        showAlert(' Veuillez définir une heure de fermeture automatique quand cette option est activée.', 'warning');
        document.getElementById('auto_session_closure_time').focus();
        return false;
    }

    // Validation du format de l'heure
    if (sessionClosure && closureTime && !/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/.test(closureTime)) {
        e.preventDefault();
        showAlert(' L\'heure doit être au format HH:MM (24h). Exemple: 18:00', 'warning');
        document.getElementById('auto_session_closure_time').focus();
        return false;
    }

    // Ajouter une indication visuelle lors de la soumission - Style moderne
    isSubmitting = true;
    const submitBtn = document.getElementById('saveBtn');
    const originalText = submitBtn.innerHTML;
    
    // Ajouter la classe loading et changer le contenu
    submitBtn.classList.add('loading');
    submitBtn.innerHTML = '<span class="btn-text-modern">Enregistrement en cours...</span><i class="mdi mdi-loading ml-2"></i>';
    submitBtn.disabled = true;
    
    // Restaurer le bouton après 5 secondes si pas de redirection
    setTimeout(() => {
        if (isSubmitting) {
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            isSubmitting = false;
        }
    }, 5000);
});

// Réinitialiser les paramètres
function resetSettings() {
    if (confirm(' Voulez-vous vraiment réinitialiser tous les paramètres aux valeurs par défaut ?\n\nCette action est irréversible.')) {
        const resetBtn = document.getElementById('resetBtn');
        const originalText = resetBtn.innerHTML;
        resetBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> <span class="btn-text">Réinitialisation...</span>';
        resetBtn.disabled = true;

        // Obtenir le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;

        if (!csrfToken) {
            showAlert('Erreur: Token CSRF manquant', 'error');
            resetBtn.innerHTML = originalText;
            resetBtn.disabled = false;
            return;
        }

        fetch('{{ route("layouts.setting.reset") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('Erreur: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('Erreur lors de la réinitialisation', 'error');
        })
        .finally(() => {
            resetBtn.innerHTML = originalText;
            resetBtn.disabled = false;
        });
    }
}

// Vider le cache
function clearCache() {
    const cacheBtn = document.getElementById('cacheBtn');
    const originalText = cacheBtn.innerHTML;
    cacheBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> <span class="btn-text">Vidage...</span>';
    cacheBtn.disabled = true;

    // Obtenir le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;

    if (!csrfToken) {
        showAlert('Erreur: Token CSRF manquant', 'error');
        cacheBtn.innerHTML = originalText;
        cacheBtn.disabled = false;
        return;
    }

    fetch('{{ route("layouts.setting.clear-cache") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
        } else {
            showAlert('Erreur: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors du vidage du cache', 'error');
    })
    .finally(() => {
        cacheBtn.innerHTML = originalText;
        cacheBtn.disabled = false;
    });
}

// Fonction pour afficher des alertes
function showAlert(message, type = 'info') {
    const alertTypes = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const icons = {
        'success': 'mdi-check-circle',
        'error': 'mdi-alert-circle',
        'warning': 'mdi-alert',
        'info': 'mdi-information'
    };

    const alert = document.createElement('div');
    alert.className = `alert ${alertTypes[type]} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="mdi ${icons[type]} mr-2"></i>${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    // Insérer au début du container
    const container = document.querySelector('.container-fluid');
    const firstRow = container.querySelector('.row');
    container.insertBefore(alert, firstRow);
    
    // Auto-dismiss après 5 secondes
    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}

// Animation des switches
document.querySelectorAll('.custom-control-input').forEach(input => {
    input.addEventListener('change', function() {
        const label = this.nextElementSibling;
        if (this.checked) {
            label.style.transition = 'color 0.3s ease-in-out';
            label.style.color = '#28a745';
            setTimeout(() => {
                label.style.color = '';
            }, 1000);
        }
    });
});

// Initialisation lors du chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAGE PARAMÈTRES CHARGÉE ===');
    console.log('État initial des checkboxes:');
    
    // Logger l'état initial avec le nouveau paramètre
    ['auto_detect_available_advisors', 'auto_assign_all_services_to_advisors', 'enable_auto_session_closure'].forEach(id => {
        const checkbox = document.getElementById(id);
        console.log(`${id}: ${checkbox.checked ? 'checked' : 'unchecked'}`);
    });
    
    console.log('Heure de fermeture:', document.getElementById('auto_session_closure_time').value);
    console.log('Temps d\'attente configuré:', document.getElementById('default_waiting_time_minutes').value + ' minutes');

    // Vérifier que le token CSRF est présent
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        console.error(' Token CSRF manquant ! Les requêtes AJAX pourraient échouer.');
        showAlert('Attention: Token CSRF manquant. Rechargez la page.', 'warning');
    }

    // S'assurer que l'affichage initial du champ d'heure est correct
    toggleSessionClosureTime();
});

// Gestion des erreurs globales
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
});

// Prévenir la double soumission
window.addEventListener('beforeunload', function() {
    if (isSubmitting) {
        return 'Une sauvegarde est en cours...';
    }
});
</script>

@endsection