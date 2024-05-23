@extends('frontend.layouts.app')
@section('content')
    {{-- Categories , Sliders . Today's deal --}}
    <div class="home-banner-area mb-2 h-330px">
        <div class="container">
            <div class="row  position-relative">
                <div class="col-lg-2 position-static d-none d-lg-block" style="padding-right: 0;">
                    @include('frontend.partials.category_menu')
                </div>
                @php
                    $num_todays_deal = count($todays_deal_products);
                @endphp
                <div class="col-lg-10 col-xs-12 slider-box" style="padding-left: 0;">
                    @if (get_setting('home_slider_images') != null)
                        <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-arrows="true" data-dots="true" data-autoplay="true" data-infinite="true">
                            @php $slider_images = json_decode(get_setting('home_slider_images'), true);  @endphp
                            @foreach ($slider_images as $key => $value)
                            <div class="carousel-box" data-color="{{ !empty(json_decode(get_setting('home_slider_color'), true)[$key]) ? json_decode(get_setting('home_slider_color'), true)[$key] : '' }}">
                                @if(!empty(json_decode(get_setting('home_slider_links'), true)[$key]))
                                    <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                        <img
                                            class="d-block mw-100 img-fit  shadow-sm overflow-hidden"
                                            src="{{ uploaded_asset($slider_images[$key]) }}"
                                            alt="{{ env('APP_NAME')}} promo"
                                            @if(count($featured_categories) == 0)
                                            height="330"
                                            @else
                                            height="330"
                                            @endif
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                                    @else
                                    <a href="https://unikart.com.bd/">
                                        <img
                                            class="d-block mw-100 img-fit  shadow-sm overflow-hidden"
                                            src="{{ uploaded_asset($slider_images[$key]) }}"
                                            alt="{{ env('APP_NAME')}} promo"
                                            @if(count($featured_categories) == 0)
                                            height="330"
                                            @else
                                            height="330"
                                            @endif
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    {{-- Banner section 1 --}}
    @if (get_setting('home_banner1_images') != null)
    <div class="mb-2">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_1_imags = json_decode(get_setting('home_banner1_images')); @endphp
                @foreach ($banner_1_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_1_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Flash Deal --}}

    @php
        $flash_deal = \App\FlashDeal::where('status', 1)->where('featured', 1)->first();
    @endphp
    @if($flash_deal != null && strtotime(date('Y-m-d H:i:s')) >= $flash_deal->start_date && strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date)

    @endif

    {{-- Category wise Products --}}

    @php $home_categories = json_decode(get_setting('home_categories')); @endphp
    @foreach ($home_categories as $key => $value)
    @php $category = \App\Category::find($value); @endphp
    <section class="mb-2">
        <div class="container">
        <a href="{{ route('products.category', $category->slug) }}" class="d-block text-reset">
                <h2 class="mb-0"><img src="{{ uploaded_asset($category->banner) }}" data-src="" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100"></h2>
        </a>
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                <div class="aiz-carousel gutters-10 half-outside-arrow px-2" data-items="6" data-xl-items="5" data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                    @foreach (get_cached_products($category->id) as $key => $product)
                    <div class="carousel-box">
                        @include('frontend.partials.product_box_1',['product' => $product])
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
   </section>
   @endforeach


    {{-- Banner Section 2 --}}
    @if (get_setting('home_banner2_images') != null)
    <div class="mb-2">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
                @foreach ($banner_2_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_2_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- End Shop Location  -->

    {{-- Featured Section --}}
    <div id="section_featured">

    </div>

    {{-- Best Selling  --}}
    <div id="section_best_selling">

    </div>


    {{-- Classified Product --}}
    @if(get_setting('classified_product') == 1)
        @php
            $classified_products = \App\CustomerProduct::where('status', '1')->where('published', '1')->take(10)->get();
        @endphp
           @if (count($classified_products) > 0)
               <section class="mb-2">
                   <div class="container">
                       <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                            <div class="d-flex mb-3 align-items-baseline border-bottom">
                                <h3 class="h5 fw-700 mb-0">
                                    <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Classified Ads') }}</span>
                                </h3>
                                <a href="{{ route('customer.products') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View More') }}</a>
                            </div>
                           <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                               @foreach ($classified_products as $key => $classified_product)
                                   <div class="carousel-box">
                                        <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                            <div class="position-relative">
                                                <a href="{{ route('customer.product', $classified_product->slug) }}" class="d-block">
                                                    <img
                                                        class="img-fit lazyload mx-auto h-140px h-md-210px"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($classified_product->thumbnail_img) }}"
                                                        alt="{{ $classified_product->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </a>
                                                <div class="absolute-top-left pt-2 pl-2">
                                                    @if($classified_product->conditon == 'new')
                                                       <span class="badge badge-inline badge-success">{{translate('new')}}</span>
                                                    @elseif($classified_product->conditon == 'used')
                                                       <span class="badge badge-inline badge-danger">{{translate('Used')}}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="p-md-3 p-2 text-left">
                                                <div class="fs-15 mb-1">
                                                    <span class="fw-700 text-primary">{{ single_price($classified_product->unit_price) }}</span>
                                                </div>
                                                <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
                                                    <a href="{{ route('customer.product', $classified_product->slug) }}" class="d-block text-reset">{{ $classified_product->getTranslation('name') }}</a>
                                                </h3>
                                            </div>
                                       </div>
                                   </div>
                               @endforeach
                           </div>
                       </div>
                   </div>
               </section>
           @endif
       @endif

    {{-- Banner Section 3 --}}
    @if (get_setting('home_banner3_images') != null)
    <div class="mb-2">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
                @foreach ($banner_3_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_3_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Top 10 categories and Brands --}}
    @if (get_setting('top10_categories') != null || get_setting('top10_brands') != null)
    <section class="mb-2">
        <div class="container">
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
            @if (get_setting('top10_brands') != null)
                    <div class="col-lg-12">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h2 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Top Brands') }}</span>
                            </h2>
                            <a href="{{ route('brands.all') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View All Brands') }}</a>
                        </div>
                        <div class="row gutters-5">
                            @php $top10_brands = json_decode(get_setting('top10_brands')); @endphp
                            @foreach ($top10_brands as $key => $value)
                                @php $brand = \App\Brand::find($value); @endphp
                                @if ($brand != null)
                                    <div class="col-4 col-sm-2">
                                        <a href="{{ route('products.brand', $brand->slug) }}" class="bg-white border d-block text-reset rounded p-2 hov-shadow-md mb-2">
                                            <div class="row align-items-center no-gutters">
                                                <div class="col-12 text-center">
                                                    <img
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($brand->logo) }}"
                                                        alt="{{ $brand->getTranslation('name') }}"
                                                        class="img-fluid img lazyload h-60px"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                                    >
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
    @endif


      {{-- blog section --}}
        <section class="pb-4">
            <div class="container">
                <h4>Top Blogs</h4>
                <div class="row">
                    @php
                        $count = 0;
                    @endphp
                    @foreach($blogs as $blog)
                        @php
                            $count++;
                        @endphp
                        @if($count <= 4)
                            <div class="col-lg-3 col-md-6">
                                <div class="card mb-3 overflow-hidden shadow-sm">
                                    <a href="{{ url("blog").'/'. $blog->slug }}" class="text-reset d-block" target="_blank">
                                        <div class="position-relative">
                                            <div class="position-absolute top-0 left-0 p-2">
                                                <div class="bg-white rounded text-black px-3 py-2 shadow hover:bg-pink-500">
                                                    <span class="font-weight-bold">{{ $blog->created_at->format('d') ? $blog->created_at->format('d'): '' }}</span><br>
                                                    <span class="font-weight-bold">{{ $blog->created_at->format('M')? $blog->created_at->format('M'): '' }}</span>
                                                </div>
                                            </div>
                                            <img
                                                src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                                data-src="{{ uploaded_asset($blog->banner) }}"
                                                alt="{{ $blog->title }}"
                                                class="img-fluid lazyload"
                                            >
                                        </div>
                                    </a>
                                    <div class="p-4">
                                        <h2 class="fs-18 fw-600 mb-1">
                                            <a href="{{ url("blog").'/'. $blog->slug }}" class="text-reset" target="_blank">
                                                {{ $blog->title }}
                                            </a>
                                        </h2>
                                        @if($blog->category != null)
                                            <div class="mb-2 opacity-50">
                                                <i>{{ $blog->category->category_name }}</i>
                                            </div>
                                        @endif
                                        <p class="opacity-70 mb-4">
                                            {{ $blog->short_description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

   
    {{-- Product For You  --}}
    <div id="section_product_for_you">

    </div>

    {{-- SEO Description --}}
    @if (get_setting('home_page_description') != null)
    <section class="mb-2">
        <div class="container">
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
            
                    <div class="col-lg-12">
                     {!!get_setting('home_page_description')!!}
                     </div>
                </div>
        </div>
    </section>
    @endif

    <section>
       <div class="container">
         <div class="row no-gutters border py-2 bg-white">
            <div class="col-md-7 subscribe-right">
                <form class="form-inline form-row px-3" method="POST" action="{{ route('subscribers.store') }}">
                        @csrf
                        <div class="form-group mb-0">
                            <label class="h5 fw-700 d-none d-lg-block">Stay up to date : </label>
                            <input style="margin-left: 5px;border-top-right-radius: 0;border-bottom-right-radius: 0;" type="email" class="form-control" placeholder="{{ translate('Your Email Address') }}" name="email" required>
                        </div>
                        <button style="border-top-left-radius: 0;border-bottom-left-radius: 0;" type="submit" class="btn btn-primary">
                            {{ translate('Subscribe') }}
                        </button>
                    </form>
                </div>

            <div class="col-md-5">
                <div class="form-group mb-0 form-inline form-row">
                    <div class="d-flex">
                    <label class="h6 fw-700 w-50 ml-3">Download </label>
                    <a href="https://play.google.com/store/apps/details?id=com.fouraxiz.unikartapp&hl=en" class="mr-n3">
                    <img style="width:70%" src="{{url('public/assets/img/play.png')}}"></a>
                    <a href="https://apps.apple.com/us/app/unikart/id1672257076" class="mr-3"><img style="width:70%" src="{{url('public/assets/img/app.png')}}"></a>
                    </div>
                </div>        
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $.post('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_featured').html(data);
                AIZ.plugins.slickCarousel();
            });

            $.post('{{ route('home.section.product_for_you') }}', { _token: '{{ csrf_token() }}'}, function(data) {$('#section_product_for_you').html(data);
                AIZ.plugins.slickCarousel();
            });

            $.post('{{ route('home.section.best_selling') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_best_selling').html(data);
                AIZ.plugins.slickCarousel();
            });
        });
    </script>
@endsection
