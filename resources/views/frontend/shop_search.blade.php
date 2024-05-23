@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container">
    <div class="row py-4" style="background-color: #DA2785;color: #fff;text-transform: uppercase;">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">WELCOME TO THE EXPRESS SHOP {{$areaname->name}}</h1>
            </div>
            
        </div>
    </div>
</section>
<section class="mb-2">
    <div class="container">
            <div class="row gutters-10 row-cols-xxl-6 row-cols-xl-6 row-cols-lg-2 row-cols-md-2 row-cols-1">
                @foreach ($shops as $key => $shop)
                    @if($shop->user !=null && $shop->user->seller != null)
                        <div class="col">
                            <div class="row no-gutters bg-white align-items-center border border-light rounded hov-shadow-md mb-3 has-transition">
                                <div class="col-12 border-left">
                                    <a href="{{ route('shop.visit', $shop->slug) }}" class="d-block p-3" tabindex="0">
                                        <img
                                            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                            data-src="{{ uploaded_asset($shop->logo) }}"
                                            alt="{{ $shop->name }}"
                                            class="img-fluid lazyload"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                                </div>
                                <!-- <div class="col-8 border-left border-light">
                                    <div class="p-3 text-left">
                                        <h2 class="h6 fw-600 text-truncate">
                                            <a href="{{ route('shop.visit', $shop->slug) }}" class="text-reset" tabindex="0">{{ $shop->name }}</a>
                                        </h2>
                                        <div class="rating rating-sm mb-2">
                                            {{ renderStarRating($shop->user->seller->rating) }}
                                        </div>
                                        <a href="{{ route('shop.visit', $shop->slug) }}" class="btn btn-soft-primary btn-sm" tabindex="0">
                                            {{ translate('Visit Store') }}
                                            <i class="las la-angle-right"></i>
                                        </a>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="aiz-pagination aiz-pagination-center mt-4">
                {{ $shops->links() }}
            </div>
        </div>
    </section>

@endsection