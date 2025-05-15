@extends('Dastyle_layout.MasterDashboard')
@section('title')
Apps Contact
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
                                        <h4 class="page-title">Cantacts</h4>
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">Dastyle</a></li>
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                                            <li class="breadcrumb-item active">Cantacts</li>
                                        </ol>
                                    </div><!--end col-->

                                    <div class="col-md">
                                        <ul class="nav nav-pills mb-0 d-inline-flex" id="pills-tab" role="tablist">
                                            <li class="nav-item mr-1">
                                              <a class="nav-link active" id="pills-grid-tab" data-toggle="pill" href="#Grid_Style" role="tab" aria-controls="pills-grid" aria-selected="true">
                                                  <i class="las la-border-all"></i>
                                              </a>
                                            </li>
                                            <li class="nav-item">
                                              <a class="nav-link" id="pills-list-tab" data-toggle="pill" href="#List_style" role="tab" aria-controls="pills-list" aria-selected="false">
                                                <i class="las la-list-ul"></i>
                                              </a>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="col-auto align-self-center">                                        
                                        <div class="d-inline-block">
                                            <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                                <span class="day-name" id="Day_Name">Today:</span>&nbsp;
                                                <span class="" id="Select_date">Jan 11</span>
                                                <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                <i data-feather="download" class="align-self-center icon-xs"></i>
                                            </a>
                                        </div>
                                        
                                    </div><!--end col-->  
                                </div><!--end row-->                                                              
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                    </div><!--end row-->
                    <!-- end page title end breadcrumb -->
                    <div class="row">
                        <div class="col-12">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="Grid_Style" role="tabpanel" aria-labelledby="pills-grid-tab">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-8.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class="client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-4.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class=" client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-5.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class=" client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-8.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class=" client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->                                            
                                    </div><!--end row--> 
                
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-1.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class=" client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-2.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class=" client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-4.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class=" client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->  
                                        <div class="col-lg-3">
                                            <div class="card client-card">                               
                                                <div class="card-body text-center">                                    
                                                    <img src="frontend/assets/images/users/user-1.jpg" alt="user" class="rounded-circle thumb-xl">
                                                    <h5 class=" client-name">Wendy Keen</h5> 
                                                    <span class="text-muted mr-3"><i class="dripicons-location mr-2 text-dark"></i>New York, USA</span>
                                                    <span  class="text-muted"><i class="dripicons-phone mr-2 text-dark"></i>+1 123 456 789</span>
                                                    <div class="text-muted text-center my-3">
                                                        <span class="badge badge-light">HTML</span>
                                                        <span class="badge badge-light">CSS</span>
                                                        <span class="badge badge-light">JAVASCRIPT</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-soft-primary">View</button>
                                                </div><!--end card-body-->                                                                     
                                            </div><!--end card-->
                                        </div><!--end col-->                                                   
                                    </div><!--end row--> 
                                </div><!--end tab-pene-->
                                <div class="tab-pane fade" id="List_style" role="tabpanel" aria-labelledby="pills-list-tab">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <div class="row align-items-center">
                                                        <div class="col">                      
                                                            <h4 class="card-title">List</h4>                      
                                                        </div><!--end col-->
                                                        <div class="col-auto"> 
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-outline-light">
                                                                   <i class="las la-user-plus"></i> Add Contact
                                                                </button>
                                                            </div>       
                                                        </div><!--end col-->
                                                    </div>  <!--end row-->                                  
                                                </div><!--end card-header-->
                                                <div class="card-body">
                                                    <div class="table-responsive-sm">
                                                        <table class="table mb-0">
                                                            <caption>List of users</caption>
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">User Name</th>                                                    
                                                                    <th scope="col">Location</th>
                                                                    <th scope="col">Phone No.</th>
                                                                    <th scope="col">Tags</th>
                                                                    <th scope="col" class="text-right">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <div class="media">
                                                                            <img src="frontend/assets/images/users/user-2.jpg" height="40" class="mr-3 align-self-center rounded" alt="...">
                                                                            <div class="media-body align-self-center"> 
                                                                                <h6 class="m-0">Merri Diamond</h6>
                                                                                <p class="mb-0 text-muted font-11">UI/UX Designer</p>                                                                                           
                                                                            </div><!--end media body-->
                                                                        </div>
                                                                    </td>
                                                                    <td><i class="dripicons-location mr-2 text-dark"></i>Surat, India</td>
                                                                    <td><i class="dripicons-phone mr-2 text-dark"></i>+91 2345-6789</td>
                                                                    <td>
                                                                        <span class="badge badge-light">HTML</span>
                                                                        <span class="badge badge-light">css</span>
                                                                        <span class="badge badge-light">javascript</span>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <a href="#"><i class="las la-external-link-alt text-primary font-18"></i></a>
                                                                        <a href="#"><i class="las la-pen text-secondary font-18"></i></a>
                                                                        <a href="#"><i class="las la-trash-alt text-danger font-18"></i></a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="media">
                                                                            <img src="frontend/assets/images/users/user-3.jpg" height="40" class="mr-3 align-self-center rounded" alt="...">
                                                                            <div class="media-body align-self-center"> 
                                                                                <h6 class="m-0">Paul Schmidt</h6>
                                                                                <p class="mb-0 text-muted font-11">UI/UX Designer</p>                                                                                           
                                                                            </div><!--end media body-->
                                                                        </div>
                                                                    </td>
                                                                    <td><i class="dripicons-location mr-2 text-dark"></i>Tokyo, Japan</td>
                                                                    <td><i class="dripicons-phone mr-2 text-dark"></i>+81 2345-6789</td>
                                                                    <td>
                                                                        <span class="badge badge-light">Python</span>
                                                                        <span class="badge badge-light">Java</span>
                                                                        <span class="badge badge-light">c++</span>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <a href="#"><i class="las la-external-link-alt text-primary font-18"></i></a>
                                                                        <a href="#"><i class="las la-pen text-secondary font-18"></i></a>
                                                                        <a href="#"><i class="las la-trash-alt text-danger font-18"></i></a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="media">
                                                                            <img src="frontend/assets/images/users/user-1.jpg" height="40" class="mr-3 align-self-center rounded" alt="...">
                                                                            <div class="media-body align-self-center"> 
                                                                                <h6 class="m-0">Harry McCall</h6>
                                                                                <p class="mb-0 text-muted font-11">UI/UX Designer</p>                                                                                           
                                                                            </div><!--end media body-->
                                                                        </div>
                                                                    </td>
                                                                    <td><i class="dripicons-location mr-2 text-dark"></i>Tel Aviv, Israel</td>
                                                                    <td><i class="dripicons-phone mr-2 text-dark"></i>+972 2345-6789</td>
                                                                    <td>
                                                                        <span class="badge badge-light">ruby</span>
                                                                        <span class="badge badge-light">flutter</span>
                                                                        <span class="badge badge-light">react</span>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <a href="#"><i class="las la-external-link-alt text-primary font-18"></i></a>
                                                                        <a href="#"><i class="las la-pen text-secondary font-18"></i></a>
                                                                        <a href="#"><i class="las la-trash-alt text-danger font-18"></i></a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="media">
                                                                            <img src="frontend/assets/images/users/user-4.jpg" height="40" class="mr-3 align-self-center rounded" alt="...">
                                                                            <div class="media-body align-self-center"> 
                                                                                <h6 class="m-0">Wendy Keen</h6>
                                                                                <p class="mb-0 text-muted font-11">UI/UX Designer</p>                                                                                           
                                                                            </div><!--end media body-->
                                                                        </div>
                                                                    </td>
                                                                    <td><i class="dripicons-location mr-2 text-dark"></i>New York, USA</td>
                                                                    <td><i class="dripicons-phone mr-2 text-dark"></i>+1 2345-6789</td>
                                                                    <td>
                                                                        <span class="badge badge-light">android</span>
                                                                        <span class="badge badge-light">java</span>                                                        
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <a href="#"><i class="las la-external-link-alt text-primary font-18"></i></a>
                                                                        <a href="#"><i class="las la-pen text-secondary font-18"></i></a>
                                                                        <a href="#"><i class="las la-trash-alt text-danger font-18"></i></a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="media">
                                                                            <img src="frontend/assets/images/users/user-5.jpg" height="40" class="mr-3 align-self-center rounded" alt="...">
                                                                            <div class="media-body align-self-center"> 
                                                                                <h6 class="m-0">Merri Diamond</h6>
                                                                                <p class="mb-0 text-muted font-11">UI/UX Designer</p>                                                                                           
                                                                            </div><!--end media body-->
                                                                        </div>
                                                                    </td>
                                                                    <td><i class="dripicons-location mr-2 text-dark"></i>London, England</td>
                                                                    <td><i class="dripicons-phone mr-2 text-dark"></i>+44 2345-6789</td>
                                                                    <td>
                                                                        <span class="badge badge-light">python</span>
                                                                        <span class="badge badge-light">HTML</span>
                                                                        
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <a href="#"><i class="las la-external-link-alt text-primary font-18"></i></a>
                                                                        <a href="#"><i class="las la-pen text-secondary font-18"></i></a>
                                                                        <a href="#"><i class="las la-trash-alt text-danger font-18"></i></a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="media">
                                                                            <img src="frontend/assets/images/users/user-6.jpg" height="40" class="mr-3 align-self-center rounded" alt="...">
                                                                            <div class="media-body align-self-center"> 
                                                                                <h6 class="m-0">Paul Schmidt</h6>
                                                                                <p class="mb-0 text-muted font-11">UI/UX Designer</p>                                                                                           
                                                                            </div><!--end media body-->
                                                                        </div>
                                                                    </td>
                                                                    <td><i class="dripicons-location mr-2 text-dark"></i>Sydney, Australia</td>
                                                                    <td><i class="dripicons-phone mr-2 text-dark"></i>+61 2345-6789</td>
                                                                    <td>
                                                                        <span class="badge badge-light">react netiv</span>
                                                                        <span class="badge badge-light">C++</span>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <a href="#"><i class="las la-external-link-alt text-primary font-18"></i></a>
                                                                        <a href="#"><i class="las la-pen text-secondary font-18"></i></a>
                                                                        <a href="#"><i class="las la-trash-alt text-danger font-18"></i></a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table><!--end /table-->
                                                    </div><!--end /tableresponsive-->
                                                </div><!--end card-body-->
                                            </div><!--end card-->
                                        </div><!--end col-->
                                    </div><!--end row-->                                        
                                </div><!--end tab-pene-->
                            </div><!--end tab-content-->
                        </div><!--end col-->
                    </div><!--end row-->

                </div><!-- container -->

                <footer class="footer text-center text-sm-left">
                    &copy; 2020 Dastyle <span class="d-none d-sm-inline-block float-right">Crafted with <i class="mdi mdi-heart text-danger"></i> by Mannatthemes</span>
                </footer><!--end footer-->
            </div>
@endsection