@include('Dastyle_layout.MasterDashboard')
@section('title')
Apps Calandar
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
                                        <h4 class="page-title">Calendar</h4>
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">Dastyle</a></li>
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                                            <li class="breadcrumb-item active">Calendar</li>
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
                        <div class="col-12">
                            <div class="card calendar-cta">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-auto">
                                            <img src="frontend/assets/images/widgets/calendar.svg" alt="" class="" height="150">
                                        </div><!--end col-->
                                        <div class="col">
                                            <h5 class="font-20">Welcome to your theme Calendar!</h5>
                                            <P>Hi, You can see all your meetings, events and create new ones.</P>
                                            <p>There are many variations of passages of Lorem Ipsum available, 
                                                but the majority have suffered alteration in some form, by injected humour, 
                                                or randomised words which don't look even slightly believable. 
                                            </p>
                                            <button type="button" class="btn btn-outline-primary">All Time chedule</button>
                                        </div><!--end col-->
                                        
                                    </div><!--end row-->
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!-- End row -->
                    <div class="row">  
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="dash-datepick">
                                        <input type="hidden" id="light_datepick"/>
                                    </div>
                                </div><!--end card-body-->
                            </div><!--end card-->
                            <div class="card">
                                <div class="card-body">
                                    <ul class="list-group">
                                        <li class="list-group-item align-items-center d-flex">
                                            <div class="media">
                                                <img src="frontend/assets/images/widgets/project1.jpg" class="mr-3 thumb-sm align-self-center rounded-circle" alt="...">
                                                <div class="media-body align-self-center"> 
                                                    <h5 class="m-0 font-13">Meeting with UI/UX Designers</h5>
                                                    <p class="text-muted mb-0">Today 07:30 AM</p>                                                                                             
                                                </div><!--end media body-->
                                            </div>
                                        </li>
                                        <li class="list-group-item align-items-center ">
                                            <div class="media">
                                                <img src="frontend/assets/images/users/user-5.jpg" class="mr-3 thumb-sm align-self-center rounded-circle" alt="...">
                                                <div class="media-body align-self-center"> 
                                                    <h5 class="m-0 font-13">Lunch with my friend</h5>
                                                    <p class="text-muted mb-0">Today 12:30 PM</p>                                                                                            
                                                </div><!--end media body-->
                                            </div>
                                        </li>
                                        <li class="list-group-item align-items-center">
                                            <div class="media">
                                                <img src="frontend/assets/images/widgets/project3.jpg" class="mr-3 thumb-sm align-self-center rounded-circle" alt="...">
                                                <div class="media-body align-self-center"> 
                                                    <h5 class="m-0 font-13">Call for payment Project ID : #254136</h5>
                                                    <p class="text-muted mb-0">Tomorrow 10:30 AM</p>                                                                                           
                                                </div><!--end media body-->
                                            </div>
                                        </li>
                                        <li class="list-group-item align-items-center ">
                                            <div class="media">
                                                <img src="frontend/assets/images/users/user-4.jpg" class="mr-3 thumb-sm align-self-center rounded-circle" alt="...">
                                                <div class="media-body align-self-center"> 
                                                    <h5 class="m-0 font-13">Picnic with my Family</h5>
                                                    <p class="text-muted mb-0">01 June 2019 - 09:30 AM</p>                                                                                           
                                                </div><!--end media body-->
                                            </div>
                                        </li>
                                        <li class="list-group-item align-items-center">
                                            <div class="media">
                                                <img src="frontend/assets/images/widgets/project4.jpg" class="mr-3 thumb-sm align-self-center rounded-circle" alt="...">
                                                <div class="media-body align-self-center"> 
                                                    <h5 class="m-0 font-13">Meeting with Developers</h5>
                                                    <p class="text-muted mb-0">04 June 2019 - 07:30 AM</p>                                                                                            
                                                </div><!--end media body-->
                                            </div>
                                        </li>
                                    </ul> 
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->                      
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <div id='calendar'></div>
                                    <div style='clear:both'></div>
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!-- End row -->

                </div><!-- container -->

                <footer class="footer text-center text-sm-left">
                    &copy; 2020 Dastyle <span class="d-none d-sm-inline-block float-right">Crafted with <i class="mdi mdi-heart text-danger"></i> by Mannatthemes</span>
                </footer><!--end footer-->
            </div>
            <!-- end page content -->
@endsection