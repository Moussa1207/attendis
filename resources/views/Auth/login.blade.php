<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login|Attendis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
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

        /*  SUPPRIMÉ : Les styles qui interfèrent avec Bootstrap */
        /*  GARDÉ : Seulement la transition fluide pour les onglets */
        .tab-content .tab-pane {
            transition: opacity 0.3s ease-in-out;
        }

        /*  Style pour les liens de bascule */
        .tab-switch-link {
            cursor: pointer;
            transition: color 0.3s ease;
            text-decoration: none;
        }
        
        .tab-switch-link:hover {
            color: #0056b3 !important;
            text-decoration: underline;
        }
    </style>
</head>

<body class="account-body accountbg">

    <!-- Register page -->
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
                                    <h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">Gérez vos attentes</h4>
                                    <p class="text-muted mb-0">Connectez-vous pour continuer.</p>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif

                                @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif

                                @if(session('info'))
                                <div class="alert alert-info">
                                    {{ session('info') }}
                                </div>
                                @endif
                                
                                <!--  ONGLETS BOOTSTRAP -->
                                <ul class="nav-border nav nav-pills" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active font-weight-semibold" data-toggle="tab" href="#LogIn_Tab" role="tab" aria-controls="LogIn_Tab" aria-selected="true">Connexion</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link font-weight-semibold" data-toggle="tab" href="#Register_Tab" role="tab" aria-controls="Register_Tab" aria-selected="false">Inscription</a>
                                    </li>
                                </ul>
                                
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div class="tab-pane active p-3 pt-3" id="LogIn_Tab" role="tabpanel" aria-labelledby="LogIn_Tab">
                                        <form method="POST" action="{{ route('login.post') }}" class="form-horizontal auth-form my-4">
                                            @csrf

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
                                                <label for="userpassword">Mot de passe</label>
                                                <div class="input-group mb-3 password-toggle">
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="userpassword" placeholder="Votre mot de passe" required>
                                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('userpassword', this)">
                                                        <i class="fas fa-eye" id="toggleIcon-userpassword"></i>
                                                    </button>
                                                    @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-6">
                                                    <div class="custom-control custom-switch switch-success">
                                                        <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                                        <label class="custom-control-label text-muted" for="remember">Se souvenir de moi</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 text-right">
                                                    <a href="{{ route('password.forgot') }}" class="text-muted font-13">
                                                        <i class="dripicons-lock"></i> Mot de passe oublié?
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 row">
                                                <div class="col-12 mt-2">
                                                    <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">Connexion <i class="fas fa-sign-in-alt ml-1"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="m-3 text-center text-muted">
                                            <!--  Lien avec JavaScript proper -->
                                            <p class="">Vous n'avez pas encore de compte ? 
                                                <span class="tab-switch-link text-primary ml-2" onclick="switchToRegister(event)">S'inscrire</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="tab-pane px-3 pt-3" id="Register_Tab" role="tabpanel" aria-labelledby="Register_Tab">
                                        <form method="POST" action="{{ route('register.post') }}" class="form-horizontal auth-form my-4">
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

                                            <div class="form-group">
                                                <label for="company">Entreprise</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @error('company') is-invalid @enderror" name="company" id="company" value="{{ old('company') }}" placeholder="ex: Attendis Corp" required>
                                                    @error('company')
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

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12">
                                                    <div class="custom-control custom-switch switch-success">
                                                        <input type="checkbox" class="custom-control-input" id="customSwitchSuccess" required>
                                                        <label class="custom-control-label text-muted" for="customSwitchSuccess">J'accepte<a href="#" class="text-primary"> les conditions d'utilisation</a></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 row">
                                                <div class="col-12 mt-2">
                                                    <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">S'inscrire <i class="fas fa-sign-in-alt ml-1"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                        <!--  Lien avec JavaScript proper -->
                                        <p class="mb-0 text-muted">Vous avez déjà un compte ? 
                                            <span class="tab-switch-link text-primary ml-2" onclick="switchToLogin(event)">Se connecter</span>
                                        </p>                                                    
                                    </div>
                                </div>
                            </div>
                            <div class="card-body bg-light-alt text-center">
                                <span class="text-muted d-none d-sm-inline-block">Attendis © {{ date('Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Register page -->

    <!-- jQuery  -->
    <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/waves.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/feather.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/simplebar.min.js') }}"></script>

    <script>
        //  Fonction pour basculer l'affichage du mot de passe
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

        //  Fonctions pour changer d'onglet avec le VRAI système Bootstrap
        function switchToRegister(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
            }
            
            // Utiliser la méthode Bootstrap MAIS empêcher le scroll
            const currentScroll = window.pageYOffset;
            $('a[href="#Register_Tab"]').tab('show');
            window.scrollTo(0, currentScroll);
        }

        function switchToLogin(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
            }
            
            // Utiliser la méthode Bootstrap MAIS empêcher le scroll
            const currentScroll = window.pageYOffset;
            $('a[href="#LogIn_Tab"]').tab('show');
            window.scrollTo(0, currentScroll);
        }

        
        $(document).ready(function() {
            // Empêcher le scroll automatique sur les changements d'onglets
            $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
                const currentScroll = window.pageYOffset;
                setTimeout(() => {
                    window.scrollTo(0, currentScroll);
                }, 0);
            });

            // Activer l'onglet inscription si erreurs d'inscription
            @if ($errors->has('username') || $errors->has('company') || $errors->has('mobile_number'))
                $('a[href="#Register_Tab"]').tab('show');
            @else
                // Sinon, s'assurer que l'onglet connexion est actif
                $('a[href="#LogIn_Tab"]').tab('show');
            @endif
        });
    </script>
</body>
</html>