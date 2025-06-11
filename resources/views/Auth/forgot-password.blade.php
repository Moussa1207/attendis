<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Mot de passe oublié|Attendis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Récupération de mot de passe" name="description" />
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

    <!-- Recover-pw page -->
    <div class="container">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="row">
                    <div class="col-lg-5 mx-auto">
                        <div class="card">
                            <div class="card-body p-0 auth-header-box">
                                <div class="text-center p-3">
                                    <a href="{{ route('login') }}" class="logo logo-admin">
                                        <img src="{{ asset('frontend/assets/images/logo-sm.png') }}" height="50" alt="logo" class="auth-logo">
                                    </a>
                                    <h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">Récupération de mot de passe</h4>   
                                    <p class="text-muted mb-0">Saisissez votre email et nous vous enverrons un lien de récupération !</p>  
                                </div>
                            </div>
                            <div class="card-body">
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
                                    <i data-feather="alert-circle" class="mr-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                @endif

                                <!-- AMÉLIORATION 4 : Affichage lien de développement si disponible -->
                                @if(session('reset_link_for_dev'))
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <h6><i data-feather="code" class="mr-2"></i>Mode Développement</h6>
                                    <p class="mb-2">Lien de récupération généré :</p>
                                    <div class="d-flex align-items-center">
                                        <code class="bg-light p-2 rounded flex-grow-1 text-wrap" style="word-break: break-all;">{{ session('reset_link_for_dev') }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-info ml-2" onclick="copyResetLink()">
                                            <i data-feather="copy" class="icon-xs"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i data-feather="clock" class="icon-xs mr-1"></i>Ce lien expire dans 1 heure
                                    </small>
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                @endif

                                <form class="form-horizontal auth-form my-4" method="POST" action="{{ route('password.email') }}">
                                    @csrf
                                    
                                    <div class="form-group">
                                        <label for="email">
                                            <i data-feather="mail" class="icon-xs mr-1"></i>Email
                                        </label>
                                        <div class="input-group mb-3">                                                                                         
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email"
                                                   value="{{ old('email') }}"
                                                   placeholder="Saisissez votre adresse email"
                                                   required>
                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>                                    
                                    </div><!--end form-group--> 
        
                                    <div class="form-group mb-0 row">
                                        <div class="col-12 mt-2">
                                            <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">
                                                <i data-feather="send" class="mr-2"></i>Envoyer le lien de récupération
                                            </button>
                                        </div><!--end col--> 
                                    </div> <!--end form-group-->                           
                                </form><!--end form-->
                                
                                <div class="text-center mt-4">
                                    <p class="text-muted mb-0">
                                         Souvenez-vous de votre mot de passe ? 
                                        <a href="{{ route('login') }}" class="text-primary ml-2">
                                            <i data-feather="arrow-left" class="icon-xs mr-1"></i>Retour à la connexion
                                        </a>
                                    </p>
                                </div>

                                <!-- AMÉLIORATION 4 : Section d'aide -->
                                <div class="mt-4 p-3 bg-light rounded">
                                    <h6 class="text-primary mb-2">
                                        <i data-feather="help-circle" class="icon-xs mr-1"></i>Aide
                                    </h6>
                                    <ul class="text-muted mb-0 font-13">
                                        <li>Saisissez l'email de votre compte</li>
                                        <li>Un lien de récupération sera généré</li>
                                        <li>Contactez un administrateur si nécessaire</li>
                                        <li>Le lien expire après 1 heure</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body bg-light-alt text-center">
                                <span class="text-muted d-none d-sm-inline-block">Attendis © {{ date('Y') }} - Sécurisé</span>                                            
                            </div>
                        </div><!--end card-->
                    </div><!--end col-->
                </div><!--end row-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->
    <!-- End Recover-pw page -->

    <!-- jQuery  -->
    <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/waves.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/feather.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/simplebar.min.js') }}"></script>

    <script>
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });

        // AMÉLIORATION 4 : Fonction pour copier le lien de récupération (développement)
        function copyResetLink() {
            const resetLink = "{{ session('reset_link_for_dev', '') }}";
            if (resetLink) {
                navigator.clipboard.writeText(resetLink).then(() => {
                    showToast('Succès', 'Lien copié dans le presse-papier !', 'success');
                }).catch(() => {
                    // Fallback pour navigateurs plus anciens
                    const tempInput = document.createElement('input');
                    tempInput.value = resetLink;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    showToast('Succès', 'Lien copié !', 'success');
                });
            }
        }

        // Toast simple pour notifications
        function showToast(title, message, type = 'info') {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            }[type] || 'alert-info';
            
            const toast = document.createElement('div');
            toast.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <strong>${title}:</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto-remove après 4 secondes
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 4000);
        }
    </script>
</body>

</html>