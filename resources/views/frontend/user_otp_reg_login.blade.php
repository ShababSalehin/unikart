@extends('frontend.layouts.app')
@section('content')
    <section class="gry-bg py-5">
     
            <div class="container">
                <div class="row">
                    <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 mx-auto">
                        <div class="card">
                        <div class="text-center pt-5">
                        <li class="list-inline-item">
                       
                            </div>
                               <div class="text-center pt-4">
                                <h1 class="h4 fw-600">
                                    {{('Enter your full name')}}
                                </h1>
                            </div>

                            <div class="px-4 py-3 py-lg-4">
                                <div class="">
                                    <form class="form-default" role="form" action="{{ route('otpreg') }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                        <input type="hidden" id="phone" name="phone" value={{$phone}}>
                                        <input type="text" class="form-control" placeholder="Enter Your Full Name" name="name" id="name">
                
                                        </div>

                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Next') }}</button>
                                        </div>
                                    </form>
                                </div>
                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
    </section>
@endsection

