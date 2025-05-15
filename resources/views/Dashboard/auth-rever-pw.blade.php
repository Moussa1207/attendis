@extends('Dastyle_layout.MasterRegister')
@section ('title')
Auth Recover pw
@endsection
@section('contenu')

        <!-- Recover-pw page -->
        <div class="container">
            <div class="row vh-100 d-flex justify-content-center">
                <div class="col-12 align-self-center">
                    <div class="row">
                        <div class="col-lg-5 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 auth-header-box">
                                    <div class="text-center p-3">
                                        <a href="index.html" class="logo logo-admin">
                                            <img src="frontend/assets/images/logo-sm.png" height="50" alt="logo" class="auth-logo">
                                        </a>
                                        <h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">Reset Password For Dastyle</h4>   
                                        <p class="text-muted  mb-0">Enter your Email and instructions will be sent to you!</p>  
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form class="form-horizontal auth-form my-4" action="index.html">
                
                                        <div class="form-group">
                                            <label for="username">Email</label>
                                            <div class="input-group mb-3">                                                                                         
                                                <input type="email" class="form-control"  id="email" placeholder="Enter Email">
                                            </div>                                    
                                        </div><!--end form-group--> 
            
                                        <div class="form-group mb-0 row">
                                            <div class="col-12 mt-2">
                                                <button class="btn btn-primary btn-block waves-effect waves-light" type="button">Reset <i class="fas fa-sign-in-alt ml-1"></i></button>
                                            </div><!--end col--> 
                                        </div> <!--end form-group-->                           
                                    </form><!--end form-->
                                    <p class="text-muted mb-0">Remember It ?  <a href="auth-register.html" class="text-primary ml-2">Sign in here</a></p>
                                </div>
                                <div class="card-body bg-light-alt text-center">
                                    <span class="text-muted d-none d-sm-inline-block">Mannatthemes © 2020</span>                                            
                                </div>
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end col-->
            </div><!--end row-->
        </div><!--end container-->
        <!-- End Recover-pw page -->

@endsection