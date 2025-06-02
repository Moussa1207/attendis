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
                <a href="{{asset('dashboard/crm-index.html')}}" class="logo">
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
                    <li class="menu-label mt-0">Menu</li>
                    <li>
                        <a href="javascript: void(0);"> <i data-feather="home" class="align-self-center menu-icon"></i><span>Dashboard</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li class="nav-item"><a class="nav-link" href="{{asset(url('dashboard/app'))}}"><i class="ti-control-record"></i>Analytics</a></li>
                        </ul>
                    </li>
    
                    
    
                    <hr class="hr-dashed hr-menu">
                    <li class="menu-label my-2">Paramètres</li>
    
                    <li>
                          <a href="javascript: void(0);"><i data-feather="box" class="align-self-center menu-icon"></i><span>Utilisateurs</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                          <ul class="nav-second-level" aria-expanded="false">
                             <li>
                               <a href="{{ route('user.users-list') }}"><i class="ti-control-record"></i>Liste</a>                           
                            </li> 
                          </ul>                        
                    </li>
    
                    
    
                               
                </ul>
    
                
            </div>
        </div>
        <!-- end left-sidenav-->

                      @yield('contenu')

        
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
        
    </body>

</html>