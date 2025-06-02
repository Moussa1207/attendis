<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title> Dashboard|Attendis </title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset('frontend/assets/images/favicon.ico')}}">

        <!-- jvectormap -->
        <link href="{{asset('frontend/plugins//jvectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet">

        <!-- App css -->
        <link href="{{asset('frontend/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('frontend/assets/css/jquery-ui.min.css')}}" rel="stylesheet">
        <link href="{{asset('frontend/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('frontend/assets/css/metisMenu.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('frontend/plugins//daterangepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('frontend/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />

    </head>

    <body class="dark-sidenav">
    <!-- Left Sidenav -->
        <div class="left-sidenav">
            <!-- LOGO -->
            <div class="brand">
                <a href="{{ Auth::user()->isAdmin() ? route('layouts.app') : route('layouts.app-users') }}" class="logo">
                    <span>
                        <img src="{{asset('frontend/assets/images/logo-sm.png')}}" alt="logo-small" class="logo-sm">
                    </span>
                    <span>
                        <img src="{{asset('frontend/assets/images/logo.png')}}" alt="logo-large" class="logo-lg logo-light">
                        <img src="{{asset('frontend/assets/images/logo-dark.png')}}" alt="logo-large" class="logo-lg logo-dark">
                    </span>
                </a>
            </div>
            <!--end logo-->
            <div class="menu-content h-100" data-simplebar>
                <ul class="metismenu left-sidenav-menu">
                    <li class="menu-label mt-0">Menu Principal</li>
                    
                    <!-- Dashboard -->
                    <li>
                        <a href="javascript: void(0);"> 
                            <i data-feather="home" class="align-self-center menu-icon"></i>
                            <span>Dashboard</span>
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @if(Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('layouts.app') }}">
                                    <i class="ti-control-record"></i>Dashboard Admin
                                </a>
                            </li>
                            @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('layouts.app-users') }}">
                                    <i class="ti-control-record"></i>Mon Dashboard
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>

                    @if(Auth::user()->isAdmin())
                    <!-- MENU ADMINISTRATEUR UNIQUEMENT -->
                    <hr class="hr-dashed hr-menu">
                    <li class="menu-label my-2">Administration</li>

                    <!-- Gestion des Utilisateurs -->
                    <li>
                        <a href="javascript: void(0);">
                            <i data-feather="users" class="align-self-center menu-icon"></i>
                            <span>Utilisateurs</span>
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li>
                                <a href="{{ route('user.users-list') }}">
                                    <i class="ti-control-record"></i>Liste des Utilisateurs
                                    <span class="badge badge-soft-primary ml-2" id="totalUsersBadge">
                                        {{ App\Models\User::count() }}
                                    </span>
                                </a>                           
                            </li>
                            <li>
                                <a href="{{ route('admin.users.create') }}">
                                    <i class="ti-control-record"></i>Créer un Utilisateur
                                    <span class="badge badge-soft-success ml-2">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                </a>                           
                            </li>
                            <li>
                                <a href="#" onclick="showMyCreatedUsers()">
                                    <i class="ti-control-record"></i>Mes Utilisateurs
                                    <span class="badge badge-soft-info ml-2" id="myUsersBadge">
                                        {{ Auth::user()->createdUsers()->count() }}
                                    </span>
                                </a>                           
                            </li>
                        </ul>                        
                    </li>

                    <!-- Statistiques -->
                    <li>
                        <a href="javascript: void(0);">
                            <i data-feather="bar-chart-2" class="align-self-center menu-icon"></i>
                            <span>Statistiques</span>
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li>
                                <a href="#" onclick="showGlobalStats()">
                                    <i class="ti-control-record"></i>Statistiques Globales
                                </a>                           
                            </li>
                            <li>
                                <a href="#" onclick="showMyStats()">
                                    <i class="ti-control-record"></i>Mes Statistiques
                                </a>                           
                            </li>
                        </ul>                        
                    </li>

                    @else
                    <!-- MENU UTILISATEUR NORMAL -->
                    <hr class="hr-dashed hr-menu">
                    <li class="menu-label my-2">Mon Compte</li>

                    <!-- Profil -->
                    <li>
                        <a href="{{ route('user.profile') }}">
                            <i data-feather="user" class="align-self-center menu-icon"></i>
                            <span>Mon Profil</span>
                        </a>
                    </li>

                    <!-- Informations -->
                    <li>
                        <a href="#" onclick="showUserInfo()">
                            <i data-feather="info" class="align-self-center menu-icon"></i>
                            <span>Mes Informations</span>
                        </a>
                    </li>
                    @endif
            </div>
        </div>
        <!-- end left-sidenav-->

        @yield('contenu')

        <!-- Modals pour les fonctionnalités sidebar -->
        
        <!-- Modal Mes Utilisateurs Créés -->
        <div class="modal fade" id="myUsersModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i data-feather="users" class="icon-xs mr-2"></i>Mes Utilisateurs Créés
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="myUsersContent">
                        <!-- Contenu chargé dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i data-feather="x" class="icon-xs mr-1"></i>Fermer
                        </button>
                        <button type="button" class="btn btn-primary" onclick="refreshMyUsers()">
                            <i data-feather="refresh-cw" class="icon-xs mr-1"></i>Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Statistiques -->
        <div class="modal fade" id="statsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statsModalTitle">
                            <i data-feather="bar-chart-2" class="icon-xs mr-2"></i>Statistiques
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="statsContent">
                        <!-- Contenu chargé dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i data-feather="x" class="icon-xs mr-1"></i>Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Informations Utilisateur -->
        <div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i data-feather="info" class="icon-xs mr-2"></i>Mes Informations
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="userInfoContent">
                        <!-- Contenu chargé dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i data-feather="x" class="icon-xs mr-1"></i>Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery  -->
        <script src="{{asset('frontend/assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('frontend/assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('frontend/assets/js/metismenu.min.js')}}"></script>
        <script src="{{asset('frontend/assets/js/waves.js')}}"></script>
        <script src="{{asset('frontend/assets/js/feather.min.js')}}"></script>
        <script src="{{asset('frontend/assets/js/simplebar.min.js')}}"></script>
        <script src="{{asset('frontend/assets/js/jquery-ui.min.js')}}"></script>
        <script src="{{asset('frontend/assets/js/moment.js')}}"></script>
        <script src="{{asset('frontend/plugins/daterangepicker/daterangepicker.js')}}"></script>

        <script src="{{asset('frontend/plugins//apex-charts/apexcharts.min.js')}}"></script>
        <script src="{{asset('frontend/plugins//jvectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
        <script src="{{asset('frontend/plugins//jvectormap/jquery-jvectormap-us-aea-en.js')}}"></script>
        <script src="{{asset('frontend/assets/pages/jquery.analytics_dashboard.init.js')}}"></script>

        <!-- App js -->
        <script src="{{asset('frontend/assets/js/app.js')}}"></script>

        <!-- Scripts pour les fonctionnalités sidebar -->
        <script>
        // Variables globales
        const isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
        const userId = {{ Auth::id() }};

        // Afficher mes utilisateurs créés (admin uniquement)
        function showMyCreatedUsers() {
            if (!isAdmin) return;
            
            const modal = $('#myUsersModal');
            const content = $('#myUsersContent');
            
            modal.modal('show');
            content.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement de vos utilisateurs...</p>
                </div>
            `);
            
            // Charger les données via AJAX
            $.get('{{ route("api.admin.my-users") }}', function(response) {
                if (response.success) {
                    let html = `
                        <div class="alert alert-info">
                            <i data-feather="info" class="mr-2"></i>
                            Vous avez créé <strong>${response.total}</strong> utilisateur(s).
                        </div>
                    `;
                    
                    if (response.users.length > 0) {
                        html += `
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Utilisateur</th>
                                            <th>Email</th>
                                            <th>Type</th>
                                            <th>Statut</th>
                                            <th>Créé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        response.users.forEach(user => {
                            const statusClass = user.is_active ? 'success' : 'warning';
                            const typeClass = user.is_admin ? 'primary' : 'secondary';
                            
                            html += `
                                <tr>
                                    <td>
                                        <strong>${user.username}</strong><br>
                                        <small class="text-muted">#${user.id}</small>
                                    </td>
                                    <td>${user.email}</td>
                                    <td><span class="badge badge-${typeClass}">${user.type}</span></td>
                                    <td><span class="badge badge-${statusClass}">${user.status}</span></td>
                                    <td>${user.created_at}</td>
                                </tr>
                            `;
                        });
                        
                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="text-center py-4">
                                <i data-feather="users" class="icon-lg text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun utilisateur créé</h5>
                                <p class="text-muted">Vous n'avez pas encore créé d'utilisateur.</p>
                                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                    <i data-feather="user-plus" class="icon-xs mr-1"></i>Créer un utilisateur
                                </a>
                            </div>
                        `;
                    }
                    
                    content.html(html);
                    feather.replace();
                } else {
                    content.html(`
                        <div class="alert alert-danger">
                            <i data-feather="alert-circle" class="mr-2"></i>
                            Erreur : ${response.message}
                        </div>
                    `);
                }
            }).fail(function() {
                content.html(`
                    <div class="alert alert-danger">
                        <i data-feather="alert-circle" class="mr-2"></i>
                        Erreur lors du chargement des données.
                    </div>
                `);
            });
        }

        // Actualiser la liste des utilisateurs créés
        function refreshMyUsers() {
            showMyCreatedUsers();
        }

        // Afficher les statistiques globales
        function showGlobalStats() {
            if (!isAdmin) return;
            
            const modal = $('#statsModal');
            const content = $('#statsContent');
            const title = $('#statsModalTitle');
            
            title.html('<i data-feather="bar-chart-2" class="icon-xs mr-2"></i>Statistiques Globales');
            modal.modal('show');
            
            content.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </div>
            `);
            
            $.get('{{ route("api.stats") }}', function(response) {
                if (response.success) {
                    const stats = response.stats;
                    content.html(`
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary">${stats.total_users}</h3>
                                        <p class="mb-0">Total Utilisateurs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-success">${stats.active_users}</h3>
                                        <p class="mb-0">Utilisateurs Actifs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary">${stats.admin_users}</h3>
                                        <p class="mb-0">Administrateurs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-info">${stats.normal_users}</h3>
                                        <p class="mb-0">Utilisateurs Normaux</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Afficher mes statistiques personnelles
        function showMyStats() {
            if (!isAdmin) return;
            
            const modal = $('#statsModal');
            const content = $('#statsContent');
            const title = $('#statsModalTitle');
            
            title.html('<i data-feather="user" class="icon-xs mr-2"></i>Mes Statistiques');
            modal.modal('show');
            
            content.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </div>
            `);
            
            $.get('{{ route("api.admin.my-stats") }}', function(response) {
                if (response.success) {
                    const stats = response.stats;
                    content.html(`
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary">${stats.total_created}</h3>
                                        <p class="mb-0">Utilisateurs Créés</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-success">${stats.active_created}</h3>
                                        <p class="mb-0">Actifs Créés</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary">${stats.admin_created}</h3>
                                        <p class="mb-0">Admins Créés</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-info">${stats.user_created}</h3>
                                        <p class="mb-0">Users Créés</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Afficher les informations utilisateur
        function showUserInfo() {
            const modal = $('#userInfoModal');
            const content = $('#userInfoContent');
            
            modal.modal('show');
            content.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </div>
            `);
            
            $.get('{{ route("api.user.info") }}', function(response) {
                if (response.success) {
                    const user = response.user;
                    let createdByHtml = '';
                    
                    if (user.created_by) {
                        createdByHtml = `
                            <tr>
                                <td><strong>Créé par :</strong></td>
                                <td>${user.created_by.username} (${user.created_by.email})</td>
                            </tr>
                        `;
                    }
                    
                    content.html(`
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <img src="{{ asset('frontend/assets/images/users/user-5.jpg') }}" class="rounded-circle" style="width: 120px; height: 120px;">
                                <h5 class="mt-3">${user.username}</h5>
                                <span class="badge badge-success">${user.status}</span>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless">
                                    <tr><td><strong>ID :</strong></td><td>#${user.id}</td></tr>
                                    <tr><td><strong>Email :</strong></td><td>${user.email}</td></tr>
                                    <tr><td><strong>Téléphone :</strong></td><td>${user.mobile_number}</td></tr>
                                    <tr><td><strong>Type :</strong></td><td>${user.type}</td></tr>
                                    <tr><td><strong>Statut :</strong></td><td>${user.status}</td></tr>
                                    <tr><td><strong>Inscription :</strong></td><td>${user.created_at}</td></tr>
                                    <tr><td><strong>Âge du compte :</strong></td><td>${user.account_age_days} jours</td></tr>
                                    ${createdByHtml}
                                </table>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Afficher les paramètres
        function showSettings() {
            alert('Fonctionnalité des paramètres à implémenter.');
        }

        // Afficher l'aide
        function showHelp() {
            alert('Fonctionnalité d\'aide à implémenter.');
        }

        // Confirmer la déconnexion
        function confirmLogout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                // Créer un formulaire de déconnexion dynamique
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("logout") }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Mettre à jour les badges du menu en temps réel
            if (isAdmin) {
                setInterval(function() {
                    updateMenuBadges();
                }, 30000); // Toutes les 30 secondes
            }
        });

        // Mettre à jour les badges du menu
        function updateMenuBadges() {
            if (!isAdmin) return;
            
            $.get('{{ route("api.stats") }}', function(response) {
                if (response.success) {
                    $('#totalUsersBadge').text(response.stats.total_users);
                    $('#myUsersBadge').text(response.stats.my_created_total || 0);
                }
            });
        }
        </script>
        
    </body>

</html>