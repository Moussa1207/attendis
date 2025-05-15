@extends('Dastyle_layout.MasterDashboard')
@section('title')
UI videos
@endsection
@section('contenu')
<!-- Page Content-->
<div class="page-content">
                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="row">
                                    <div class="col">
                                        <h4 class="page-title">Videos</h4>
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">Dastyle</a></li>
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">UI Kit</a></li>
                                            <li class="breadcrumb-item active">Videos</li>
                                        </ol>
                                    </div><!--end col-->
                                    <div class="col-auto align-self-center">
                                        <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                            <span class="day-name" id="Day_Name">Today:</span>&nbsp;
                                            <span class="" id="Select_date">Jan 11</span>
                                            <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i data-feather="download" class="align-self-center icon-xs"></i>
                                        </a>
                                    </div><!--end col-->  
                                </div><!--end row-->                                                              
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                    </div><!--end row-->
                    <!-- end page title end breadcrumb -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Responsive embed video 16:9</h4>
                                    <p class="text-muted mb-0">Aspect ratios can be customized with modifier classes.</p>
                                </div><!--end card-header-->
                                <div class="card-body">        
                                    <!-- 16:9 aspect ratio -->
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/VVlfF2nbNxc"></iframe>
                                    </div>
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div> <!-- end col -->
    
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Responsive embed video 21:9</h4>
                                    <p class="text-muted mb-0">Aspect ratios can be customized with modifier classes.</p>
                                </div><!--end card-header-->
                                <div class="card-body">
                                    <!-- 21:9 aspect ratio -->
                                    <div class="embed-responsive embed-responsive-21by9">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/VVlfF2nbNxc"></iframe>
                                    </div>    
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div> <!-- end col -->    
                    </div> <!-- end row -->
    
                    <div class="row">    
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Responsive embed video 4:3</h4>
                                    <p class="text-muted mb-0">Aspect ratios can be customized with modifier classes.</p>
                                </div><!--end card-header-->
                                <div class="card-body">    
                                    <!-- 4:3 aspect ratio -->
                                    <div class="embed-responsive embed-responsive-4by3">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/VVlfF2nbNxc"></iframe>
                                    </div>
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div> <!-- end col -->
    
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Responsive embed video 1:1</h4>
                                    <p class="text-muted mb-0">Aspect ratios can be customized with modifier classes.</p>
                                </div><!--end card-header-->
                                <div class="card-body">
                                    <!-- 1:1 aspect ratio -->
                                    <div class="embed-responsive embed-responsive-1by1">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/VVlfF2nbNxc"></iframe>
                                    </div>    
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div> <!-- end col -->    
                    </div> <!-- end row -->    

                </div><!-- container -->

                <footer class="footer text-center text-sm-left">
                    &copy; 2020 Dastyle <span class="d-none d-sm-inline-block float-right">Crafted with <i class="mdi mdi-heart text-danger"></i> by Mannatthemes</span>
                </footer><!--end footer-->
            </div>
            <!-- end page content -->

@endsection