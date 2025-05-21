<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Connexion</title>
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
                                    <h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">Let's Get Started</h4>
                                    <p class="text-muted mb-0">Sign in to continue.</p>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                                
                                <ul class="nav-border nav nav-pills" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active font-weight-semibold" data-toggle="tab" href="#LogIn_Tab" role="tab">Connexion</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link font-weight-semibold" data-toggle="tab" href="#Register_Tab" role="tab">Inscription</a>
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div class="tab-pane active p-3 pt-3" id="LogIn_Tab" role="tabpanel">
                                        <form method="POST" action="{{ route('login.post') }}" class="form-horizontal auth-form my-4">
                                            @csrf

                                            <div class="form-group">
                                                <label for="username">Nom d'utilisateur</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" id="username" value="{{ old('username') }}" placeholder="Entrez votre nom d'utilisateur" required>
                                                    @error('username')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="userpassword">Mot de passe</label>
                                                <div class="input-group mb-3">
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="userpassword" placeholder="Entrez votre mot de passe" required>
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
                                                    <a href="" class="text-muted font-13"><i class="dripicons-lock"></i> Mot de passe oublié?</a>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 row">
                                                <div class="col-12 mt-2">
                                                    <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">Connexion <i class="fas fa-sign-in-alt ml-1"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane px-3 pt-3" id="Register_Tab" role="tabpanel">
                                        <form method="POST" action="{{ route('register.post') }}" class="form-horizontal auth-form my-4">
                                            @csrf

                                            <div class="form-group">
                                                <label for="register_username">Nom d'utilisateur</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" id="register_username" value="{{ old('username') }}" placeholder="Entrez votre nom d'utilisateur" required>
                                                    @error('username')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="useremail">Email</label>
                                                <div class="input-group mb-3">
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="useremail" value="{{ old('email') }}" placeholder="Entrez votre email" required>
                                                    @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="register_password">Mot de passe</label>
                                                <div class="input-group mb-3">
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="register_password" placeholder="Entrez votre mot de passe" required>
                                                    @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="password-confirm">Confirmer le mot de passe</label>
                                                <div class="input-group mb-3">
                                                    <input type="password" class="form-control" name="password_confirmation" id="password-confirm" placeholder="Confirmez votre mot de passe" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="mo_number">Numéro de téléphone</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" name="mobile_number" id="mo_number" value="{{ old('mobile_number') }}" placeholder="Entrez votre numéro de téléphone" required>
                                                    @error('mobile_number')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12">
                                                    <div class="custom-control custom-switch switch-success">
                                                        <input type="checkbox" class="custom-control-input" id="customSwitchSuccess" required>
                                                        <label class="custom-control-label text-muted" for="customSwitchSuccess">Vous acceptez les conditions d'utilisation</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 row">
                                                <div class="col-12 mt-2">
                                                    <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">S'inscrire <i class="fas fa-sign-in-alt ml-1"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body bg-light-alt text-center">
                                <span class="text-muted d-none d-sm-inline-block">© {{ date('Y') }}</span>
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
        // Code pour activer l'onglet approprié si des erreurs de validation sont présentes
        $(document).ready(function() {
            @if ($errors->has('username') || $errors->has('password'))
                $('#LogIn_Tab').tab('show');
            @endif
            
            @if ($errors->has('email') || $errors->has('mobile_number'))
                $('#Register_Tab').tab('show');
            @endif
        });
    </script>
</body>
</html>