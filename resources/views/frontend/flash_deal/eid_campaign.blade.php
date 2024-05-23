@extends('frontend.layouts.app')
@section('content')
<div class="home-banner-area mb-2">
    <div class="container">
        <div class="col-lg-12 col-xs-12 slider-box" style="padding-left: 0;">
            @if (get_setting('home_slider_images') != null)
            <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-arrows="true" data-dots="true" data-autoplay="true" data-infinite="true">
                @php $slider_images = json_decode(get_setting('home_slider_images'), true); @endphp
                @foreach ($slider_images as $key => $value)
                <div class="carousel-box" data-color="{{ !empty(json_decode(get_setting('home_slider_color'), true)[$key]) ? json_decode(get_setting('home_slider_color'), true)[$key] : '' }}">
                    @if(!empty(json_decode(get_setting('home_slider_links'), true)[$key]))
                    <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                        <img
                                            class="d-block mw-100 img-fit  shadow-sm overflow-hidden"
                                            src="{{ uploaded_asset($slider_images[$key]) }}"
                                            alt="{{ env('APP_NAME')}} promo"
                                            height="350"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                    @else
                    <a href="https://unikart.com.bd/">
                        <img class="d-block mw-100 img-fit  shadow-sm overflow-hidden"
                        src="{{ uploaded_asset($slider_images[$key]) }}"
                        alt="{{ env('APP_NAME')}} promo" height="350"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<div class="mb-2">
    <div class="container">
        <h4 class="text-center my-3">Brand Campaign</h4>
        <div class="row">
            @foreach ($flash_deals as $key => $flash_deal)
            <div class="figure col-md-4 px-2 py-2 text-center">
                <div class="mb-3 mb-lg-0">
                    <a href="{{ url('campaign/' . $flash_deal->slug) }}" class="d-block text-reset">
                        <img src="{{ uploaded_asset($flash_deal->banner) }}" data-src="{{ uploaded_asset($flash_deal->banner) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                        <h5 class="mt-1 mb-1">{{ $flash_deal->getTranslation('title') }}</h5>
                            <p >{{ date('d-m-Y', $flash_deal->start_date) }} <span class="text-danger">to</span> {{ date('d-m-Y', $flash_deal->end_date) }}</p>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection