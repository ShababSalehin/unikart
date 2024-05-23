@extends('frontend.layouts.app')
@section('content')
    @php $banner_1_imags = json_decode(get_setting('home_banner1_images')); @endphp
    <div class="jumbotron text-center" style="background: url({{ uploaded_asset($banner_1_imags[0]) }});">
    </div>
    <div class="container my-5 ">
        <div class="container">
            <h4 class="text-center my-3">Top Deals</h4>
            <div class="row">
                @foreach ($flash_deals as $key => $flash_deal)
                    <div class="figure col-md-4 px-2 py-2 text-center ">
                        <a href="{{ url('campaign/' . $flash_deal->slug) }}" class=" d-block text-reset ">
                            <img src="{{ uploaded_asset($flash_deal->banner) }}"
                                data-src="{{ uploaded_asset($flash_deal->banner) }}" alt="promo"
                                class="img-fluid lazyload w-100 border rounded hov-shadow-md" style="height: 300px;">
                            <h5 class="mt-1 mb-1">{{ $flash_deal->getTranslation('title') }}</h5>
                            <p >{{ date('d-m-Y', $flash_deal->start_date) }} <span class="text-danger">to</span> {{ date('d-m-Y', $flash_deal->end_date) }}</p>
                       </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
   
</div>
@endsection
