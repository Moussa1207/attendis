<!-- resources/views/auth/reset-password.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Réinitialisation|Attendis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Réinitialisation de mot de passe" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('frontend/assets/images/favicon.ico') }}">

    <!-- App css -->
    <link href="{{ asset('frontend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frontend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frontend/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .password-toggle {
            position: relative;
        }
        .password-toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            z-index: 10;
        }
        .password-toggle-btn:hover {
            color: #495057;
        }
        .password-toggle-btn:focus {
            outline: none;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        .password-feedback {
            font-size: 0.8em;
            margin-top: 5px;
        }
        .strength-weak { background-color: #dc3545; width: 25%; }
        .strength-medium { background-color: #ffc107; width: 50%; }
        .strength-strong { background-color: #17a2b8; width: 75%; }
        .strength-very_strong { background-color: #28a745; width: 100%; }
    </style>
</head>

<body class="account-body accountbg">

    <!-- Reset Password page -->
    <div class="container">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="row">
                    <div class="col-lg-5 mx-auto">
                        <div class="card">
                            <div class="card-body p-0 auth-header-box">
                                <div class="text-center p-3">
                                    <a href="{{ url('/') }}" class="logo logo-admin">
                                        <img src="{{ asset('frontend/assets/images/logo-sm.png') }}" height="50" alt="logo" class="auth-logo">
                                    </a>
                                    <h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">Nouveau mot de passe</h4>
                                    <p class="text-muted mb-0">Définissez votre nouveau mot de passe sécurisé</p>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i data-feather="alert-circle" class="mr-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                @endif

                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i data-feather="check-circle" class="mr-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                @endif

                                <!-- Informations utilisateur -->
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" 
                                                 alt="avatar" 
                                                 class="rounded-circle" 
                                                 style="width: 50px; height: 50px;">
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-info font-weight-semibold">{{ $user->username }}</h6>
                                            <p class="mb-0 text-muted">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('password.update') }}" class="form-horizontal auth-form my-4" id="resetForm">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                                    <div class="form-group">
                                        <label for="password" class="font-weight-semibold">
                                            <i data-feather="key" class="icon-xs mr-1"></i>Nouveau mot de passe
                                        </label>
                                        <div class="input-group mb-3 password-toggle">
                                            <input type="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   name="password" 
                                                   id="password" 
                                                   placeholder="Votre nouveau mot de passe" 
                                                   required
                                                   onkeyup="checkPasswordStrength()">
                                            <button type="button" class="password-toggle-btn" onclick="togglePassword('password', this)">
                                                <i class="fas fa-eye" id="toggleIcon-password"></i>
                                            </button>
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        
                                        <!-- Indicateur de force du mot de passe -->
                                        <div class="password-strength bg-light" id="passwordStrength"></div>
                                        <div class="password-feedback text-muted" id="passwordFeedback">
                                            Minimum 8 caractères avec majuscules, minuscules, chiffres et caractères spéciaux
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation" class="font-weight-semibold">
                                            <i data-feather="check-circle" class="icon-xs mr-1"></i>Confirmer le mot de passe
                                        </label>
                                        <div class="input-group mb-3 password-toggle">
                                            <input type="password" 
                                                   class="form-control" 
                                                   name="password_confirmation" 
                                                   id="password_confirmation" 
                                                   placeholder="Confirmer votre mot de passe" 
                                                   required
                                                   onkeyup="checkPasswordMatch()">
                                            <button type="button" class="password-toggle-btn" onclick="togglePassword('password_confirmation', this)">
                                                <i class="fas fa-eye" id="toggleIcon-password_confirmation"></i>
                                            </button>
                                        </div>
                                        <div id="passwordMatch" class="text-muted font-13"></div>
                                    </div>

                                    <!-- Conseils de sécurité -->
                                    <div class="alert alert-light border">
                                        <h6 class="text-dark mb-2">
                                            <i data-feather="shield" class="icon-xs mr-1"></i>Conseils de sécurité
                                        </h6>
                                        <ul class="mb-0 text-muted font-13">
                                            <li>Utilisez au moins 8 caractères</li>
                                            <li>Mélangez majuscules et minuscules</li>
                                            <li>Incluez des chiffres et caractères spéciaux</li>
                                            <li>Évitez les mots du dictionnaire</li>
                                        </ul>
                                    </div>

                                    <div class="form-group mb-0 row">
                                        <div class="col-12 mt-2">
                                            <button class="btn btn-primary btn-block waves-effect waves-light" 
                                                    type="submit" 
                                                    id="submitBtn"
                                                    disabled>
                                                <i class="fas fa-key mr-2"></i>Définir le mot de passe
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <div class="m-3 text-center text-muted">
                                    <p class="mb-0">
                                        <a href="{{ route('login') }}" class="text-primary">
                                            <i data-feather="arrow-left" class="icon-xs mr-1"></i>Retour à la connexion
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="card-body bg-light-alt text-center">
                                <span class="text-muted d-none d-sm-inline-block">Attendis © {{ date('Y') }} - Sécurisé</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Reset Password page -->

    <!-- jQuery  -->
    <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/waves.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/feather.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/simplebar.min.js') }}"></script>

    <script>
        let passwordStrengthData = null;

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

        // Vérifier la force du mot de passe
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            const feedback = document.getElementById('passwordFeedback');
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength bg-light';
                feedback.textContent = 'Minimum 8 caractères avec majuscules, minuscules, chiffres et caractères spéciaux';
                feedback.className = 'password-feedback text-muted';
                updateSubmitButton();
                return;
            }

            // Calculer la force (logique simplifiée côté client)
            let score = 0;
            const feedbackMessages = [];

            // Longueur
            if (password.length >= 8) score += 1;
            else feedbackMessages.push('Au moins 8 caractères');

            if (password.length >= 12) score += 1;

            // Complexité
            if (/[a-z]/.test(password)) score += 1;
            else feedbackMessages.push('Lettres minuscules');

            if (/[A-Z]/.test(password)) score += 1;
            else feedbackMessages.push('Lettres majuscules');

            if (/[0-9]/.test(password)) score += 1;
            else feedbackMessages.push('Chiffres');

            if (/[^a-zA-Z0-9]/.test(password)) score += 1;
            else feedbackMessages.push('Caractères spéciaux');

            // Déterminer le niveau
            let level, color, text;
            if (score < 3) {
                level = 'weak';
                color = 'danger';
                text = 'Faible';
            } else if (score < 5) {
                level = 'medium';
                color = 'warning';
                text = 'Moyen';
            } else if (score < 6) {
                level = 'strong';
                color = 'info';
                text = 'Fort';
            } else {
                level = 'very_strong';
                color = 'success';
                text = 'Très fort';
            }

            // Mettre à jour l'affichage
            strengthBar.className = `password-strength strength-${level}`;
            
            if (feedbackMessages.length > 0) {
                feedback.innerHTML = `<span class="text-${color}">${text}</span> - Manque: ${feedbackMessages.join(', ')}`;
            } else {
                feedback.innerHTML = `<span class="text-${color}"><i class="fas fa-check mr-1"></i>${text}</span>`;
            }
            feedback.className = `password-feedback text-${color}`;

            passwordStrengthData = { level, score, feedbackMessages };
            updateSubmitButton();
        }

        // Vérifier la correspondance des mots de passe
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmation.length === 0) {
                matchDiv.textContent = '';
                updateSubmitButton();
                return;
            }

            if (password === confirmation) {
                matchDiv.innerHTML = '<i class="fas fa-check text-success mr-1"></i>Les mots de passe correspondent';
                matchDiv.className = 'text-success font-13';
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times text-danger mr-1"></i>Les mots de passe ne correspondent pas';
                matchDiv.className = 'text-danger font-13';
            }
            
            updateSubmitButton();
        }

        // Mettre à jour l'état du bouton de soumission
        function updateSubmitButton() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const submitBtn = document.getElementById('submitBtn');
            
            const isPasswordStrong = passwordStrengthData && passwordStrengthData.score >= 4;
            const isPasswordMatch = password === confirmation && confirmation.length > 0;
            const isValid = isPasswordStrong && isPasswordMatch;
            
            submitBtn.disabled = !isValid;
            
            if (isValid) {
                submitBtn.className = 'btn btn-primary btn-block waves-effect waves-light';
                submitBtn.innerHTML = '<i class="fas fa-key mr-2"></i>Définir le mot de passe';
            } else {
                submitBtn.className = 'btn btn-secondary btn-block';
                submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Mot de passe requis';
            }
        }

        // Gestion du formulaire
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            
            if (submitBtn.disabled) {
                e.preventDefault();
                return false;
            }
            
            // Afficher le loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Traitement...';
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            
            // Vérifications initiales
            checkPasswordStrength();
            checkPasswordMatch();
        });
    </script>
</body>
</html>