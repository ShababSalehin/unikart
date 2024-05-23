@extends('frontend.layouts.app')

@section('content')

<section class="mb-4 pt-3">
@if (get_setting('home_banner4_images') != null)
<div class="row">     
                    <div class="col-xl-12">
                
                        <a href="{{ json_decode(get_setting('home_banner4_links'), true)[0] }}">
                            <img class="img-fit lazyload mx-auto" src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset(json_decode(get_setting('home_banner4_images'), true)[0]) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </a>
                        </div>
                        </div><br>
                        @endif
    <div class="container sm-px-0">


        @if (get_setting('home_banner4_images') != null)
                @foreach (json_decode(get_setting('home_banner4_images'), true) as $key => $value)
                    @if($key!=0)
                        <div class="row">  
                            <div class="col-xl-12">
                            
                            <a href="{{ json_decode(get_setting('home_banner4_links'), true)[$key] }}">
                                <img class="img-fit lazyload mx-auto" src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset(json_decode(get_setting('home_banner4_images'), true)[$key]) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                            </a>
                            </div>
                        </div>
                    @endif
                    @endforeach
                @endif
           
    </div>
</section>

@endsection

@section('script')

@endsection