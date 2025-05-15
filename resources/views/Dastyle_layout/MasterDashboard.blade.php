<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>@yield('title') </title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="frontend/assets/images/favicon.ico">

        <!-- jvectormap -->
        <link href="frontend/plugins/jvectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet">

        <!-- App css -->
        <link href="frontend/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="frontend/assets/css/jquery-ui.min.css" rel="stylesheet">
        <link href="frontend/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="frontend/assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
        <link href="frontend/plugins/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
        <link href="frontend/assets/css/app.min.css" rel="stylesheet" type="text/css" />

    </head>

    <body class="dark-sidenav">

        @include('Dastyle_layout.left-sidenavD')
        <div class="page-wrapper">
           @include('Dastyle_layout.topbarD')

           @yield('contenu')
        </div>
          <!-- end page-wrapper -->
        











    <!-- jQuery  -->
    <script src="frontend/assets/js/jquery.min.js"></script>
        <script src="frontend/assets/js/bootstrap.bundle.min.js"></script>
        <script src="frontend/assets/js/metismenu.min.js"></script>
        <script src="frontend/assets/js/waves.js"></script>
        <script src="frontend/assets/js/feather.min.js"></script>
        <script src="frontend/assets/js/simplebar.min.js"></script>
        <script src="frontend/assets/js/jquery-ui.min.js"></script>
        <script src="frontend/assets/js/moment.js"></script>
        <script src="frontend/plugins/daterangepicker/daterangepicker.js"></script>

        <script src="frontend/plugins/apex-charts/apexcharts.min.js"></script>
        <script src="frontend/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js"></script>
        <script src="frontend/plugins/jvectormap/jquery-jvectormap-us-aea-en.js"></script>
        <script src="frontend/assets/pages/jquery.analytics_dashboard.init.js"></script>

        <!-- App js -->
        <script src="frontend/assets/js/app.js"></script>
        
    </body>

</html>