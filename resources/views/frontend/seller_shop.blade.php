@extends('frontend.layouts.app')

@section('meta_title'){{ $shop->meta_title }}@stop

@section('meta_description'){{ $shop->meta_description }}@stop

@section('meta')
<!-- Schema.org markup for Google+ -->
<meta itemprop="name" content="{{ $shop->meta_title }}">
<meta itemprop="description" content="{{ $shop->meta_description }}">
<meta itemprop="image" content="{{ uploaded_asset($shop->logo) }}">

<!-- Twitter Card data -->
<meta name="twitter:card" content="website">
<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:title" content="{{ $shop->meta_title }}">
<meta name="twitter:description" content="{{ $shop->meta_description }}">
<meta name="twitter:creator" content="@author_handle">
<meta name="twitter:image" content="{{ uploaded_asset($shop->meta_img) }}">

<!-- Open Graph data -->
<meta property="og:title" content="{{ $shop->meta_title }}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ route('shop.visit', $shop->slug) }}" />
<meta property="og:image" content="{{ uploaded_asset($shop->logo) }}" />
<meta property="og:description" content="{{ $shop->meta_description }}" />
<meta property="og:site_name" content="{{ $shop->name }}" />
@endsection

@section('content')
<section class="  mb-4 bg-white">
    <div class="container">
        <div class="row">

            @if ($shop->sliders != null)
            @php
            $slider = '';
            $sliders = explode(',',$shop->sliders);
            if(count($sliders)>0){
            $slider = uploaded_asset($sliders[0]);
            }
            @endphp

            @if(!empty($slider))
            <div class="col-12" style="min-height:150px;background-image:url('{{ $slider }}')">

                @else
                <div class="col-12" style="min-height:150px;background-image:url('{{ static_asset('assets/img/placeholder-rect.jpg') }}')">
                    @endif
                    @else
                    <div class="col-12" style="min-height:150px;background-image: url('{{ static_asset('assets/img/placeholder-rect.jpg') }}')'">
                        @endif


                        <div class=" mt-3 col-lg-6 col-md-12 col-xs-12 bg-light" style="min-height:130px">

                            <div class="col-md-12 mx-auto">
                                <div class="d-flex justify-content-center  ">
                                    <img height="120" class="lazyload" src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="@if ($shop->logo !== null) {{ uploaded_asset($shop->logo) }} @else {{ static_asset('assets/img/placeholder.jpg') }} @endif" alt="{{ $shop->name }}">


                                    <div class="pl-5 text-left mt-4">
                                        <h1 class="fw-300 h5 mb-0">{{ $shop->name }}
                                            @if ($shop->user->seller->verification_status == 1)
                                            <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                            @else
                                            <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                            @endif
                                        </h1>
                                        <?php
                                        if (Auth::check()) {
                                            $isfollow = App\FollowShop::where(['user_id' => Auth::user()->id, 'shop_id' => $shop->id])->get();
                                        } else {
                                            $isfollow = array();
                                        }

                                        $totalfollow = App\FollowShop::where(['shop_id' => $shop->id])->get(); ?>

                                        <div class="location opacity-60">{{ count($totalfollow)}} Followers
                                            @if(count($isfollow) > 0)

                                            @endif

                                            <div class="rating rating-sm mb-1">
                                                {{ renderStarRating($shop->user->seller->rating) }}
                                                {{get_positive_seller_ratting($shop->user->seller->user_id)}}% Positive Seller Ratings
                                            </div>
                                        </div>
                                        <div>

                                        </div>


                                    </div>


                                    <div class="pl-6 text-left mt-4" style="text-align: center;">
                                        <div class="location opacity-80" style="text-align: center;">

                                            <a href='javascript:' onclick="show_chat_modal(event)"><i class="la la-comments la-2x"></i>
                                                <br>ChatNow </a>
                                        </div>

                                    </div>

                                    <div class="pl-4 text-left mt-4">
                                        <h1 class="fw-300 h5 mb-0">

                                        </h1>

                                        <div>
                                            {{ renderStarRating($shop->user->seller->rating) }}
                                        </div>

                                        <?php
                                        if (Auth::check()) {
                                            $isfollow = App\FollowShop::where(['user_id' => Auth::user()->id, 'shop_id' => $shop->id])->get();
                                        } else {
                                            $isfollow = array();
                                        }

                                        $totalfollow = App\FollowShop::where(['shop_id' => $shop->id])->get(); ?>
                                        <div class="location opacity-80" style="text-align: center;">
                                            @if(count($isfollow) > 0)

                                            <a class="ml-7" href="{{route('unfollow.shop',$shop->id)}}"><i class="la la-plus la-2x"></i><br>Unfollow</a>
                                            @else
                                            <a class="ml-7" href="{{route('shop.following',$shop->id)}}"><i class="la la-plus la-2x"></i><br>Follow</a>
                                            @endif
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="border-bottom"></div>
                <div class="row align-items-center">
                    <div class="col-lg-6 order-2 order-lg-0">

                        <ul class="list-inline mb-0 text-center text-lg-left">
                            <li class="list-inline-item ">
                                <a class="text-reset d-inline-block fw-600 fs-15 p-3 @if(!isset($type)) border-bottom border-primary border-width-2 @endif" href="{{ route('shop.visit', $shop->slug) }}">{{ translate('Store Home')}}</a>
                            </li>
                            <li class="list-inline-item ">
                                <a class="text-reset d-inline-block fw-600 fs-15 p-3 @if(isset($type) && $type == 'top_selling') border-bottom border-primary border-width-2 @endif" href="{{ route('shop.visit.type', ['slug'=>$shop->slug, 'type'=>'top_selling']) }}">{{ translate('Top Selling')}}</a>
                            </li>
                            <li class="list-inline-item ">
                                <a class="text-reset d-inline-block fw-600 fs-15 p-3 @if(isset($type) && $type == 'all_products') border-bottom border-primary border-width-2 @endif" href="{{ route('shop.visit.type', ['slug'=>$shop->slug, 'type'=>'all_products']) }}">{{ translate('All Products')}}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-6 order-1 order-lg-0"><br>
                        <form action="{{ route('shop.visit.type', ['slug'=>$shop->slug, 'type'=>'all_products']) }}" method="GET">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search In Store" value="{{request()->keyword}}" name="keyword" aria-label="Recipient's username" aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button style="background:#fc5994;color:#fff" class="input-group-text btn" id="basic-addon2">search</button>
                                </div>
                            </div>
                        </form>

                        <ul class="text-center text-lg-right mt-4 mt-lg-0 social colored list-inline mb-0">

                            @if ($shop->facebook != null)
                            <li class="list-inline-item">
                                <a href="{{ $shop->facebook }}" class="facebook" target="_blank">
                                    <i class="lab la-facebook-f"></i>
                                </a>
                            </li>
                            @endif
                            @if ($shop->twitter != null)
                            <li class="list-inline-item">
                                <a href="{{ $shop->twitter }}" class="twitter" target="_blank">
                                    <i class="lab la-twitter"></i>
                                </a>
                            </li>
                            @endif
                            @if ($shop->google != null)
                            <li class="list-inline-item">
                                <a href="{{ $shop->google }}" class="google-plus" target="_blank">
                                    <i class="lab la-google"></i>
                                </a>
                            </li>
                            @endif
                            @if ($shop->youtube != null)
                            <li class="list-inline-item">
                                <a href="{{ $shop->youtube }}" class="youtube" target="_blank">
                                    <i class="lab la-youtube"></i>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>



            </div>
</section>

@if (!isset($type))
<section class="mb-1">
    <div class="container">

    </div>
</section>
<section class="mb-4">
    <div class="container">
        <div class="mb-4">
            <h3 class="h3 fw-600 border-bottom">
                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Featured Products')}}</span>
            </h3>
        </div>
        <div class="row">
            <div class="col">
                <div class="aiz-carousel gutters-10" data-items="6" data-xl-items="5" data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-autoplay='true' data-infinute="true" data-dots="true">
                    @foreach ($shop->user->products->where('published', 1)->where('approved', 1)->where('seller_featured', 1) as $key => $product)
                    <div class="carousel-box">
                        @include('frontend.partials.product_box_1',['product' => $product])
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<section class="mb-4">
    <div class="container">
        <div class="mb-4">
            <h3 class="h3 fw-600 border-bottom">
                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                    @if (!isset($type))
                    {{ translate('New Arrival Products')}}
                    @elseif ($type == 'top_selling')
                    {{ translate('Top Selling')}}
                    @elseif ($type == 'all_products')
                    {{ translate('All Products')}}
                    @endif
                </span>
            </h3>
        </div>
        <div class="row gutters-5 row-cols-xxl-5 row-cols-lg-4 row-cols-md-3 row-cols-2">
            @php
            if (!isset($type)){
            $products = \App\Product::where('user_id', $shop->user->id)->where('published', 1)->where('approved', 1)->orderBy('created_at', 'desc')->paginate(24);
            }
            elseif ($type == 'top_selling'){
            $products = \App\Product::where('user_id', $shop->user->id)->where('published', 1)->where('approved', 1)->orderBy('num_of_sale', 'desc')->paginate(24);
            }
            elseif ($type == 'all_products'){
            $products = \App\Product::where('user_id', $shop->user->id)->where('published', 1)->where('approved', 1);

            $query = request()->keyword;
            if ($query != null) {


            //$query = implode('%',str_split(str_replace(" ","",$query)));
           $products =$products->leftJoin('product_translations','product_translations.product_id','=','products.id')
            ->where('products.published', 1)
            ->where(function ($q) use ($query) {
                foreach (explode(' ', trim($query)) as $word) {
                    $q->where('products.name', 'like', '%' . $word . '%')
                    ->orWhere('product_translations.name', 'like', '%' . $word . '%')
                    ->orWhere('products.name', 'like', '' . $word . '%')
                    ->orWhere('product_translations.name', 'like', '' . $word . '%')
                    ->orWhere('products.name', 'like', '%' . $word . '')
                    ->orWhere('product_translations.name', 'like', '%' . $word . '')
                    ->orWhere('products.name', 'like', '' . $word . '')
                    ->orWhere('product_translations.name', 'like', '' . $word . '')
                    ->orWhere('products.tags', 'like', '%' . $word . '%')
                    ->orWhere('products.tags', 'like', '%' . $word . '')
                    ->orWhere('products.tags', 'like', '' . $word . '%')
                    ->orWhere('products.tags', 'like', '' . $word . '');
                }
            })
            ->groupBy('products.id')
            ->orderByRaw("CASE WHEN products.name LIKE '".$query."' OR product_translations.name LIKE '".$query."' THEN 1 WHEN products.name LIKE '".$query."%' OR product_translations.name LIKE '".$query."%' THEN 2 WHEN products.name LIKE '%".$query."' OR product_translations.name LIKE '%".$query."' THEN 4 ELSE 3 END");
            }
            $products = $products->paginate(25);
            }
            @endphp
            @foreach ($products as $key => $product)
            <div class="col mb-3">
                @include('frontend.partials.product_box_1',['product' => $product])
            </div>
            @endforeach
        </div>
        <div class="aiz-pagination aiz-pagination-center mb-4">
            {{ $products->links() }}
        </div>
    </div>
</section>



@endsection


@section('modal')
<div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
        <div class="modal-content position-relative">
            <div class="modal-header">
                <h5 class="modal-title fw-600 h5">{{ translate('Any query about this Seller')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="" action="{{ route('conversations.messstore') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body gry-bg px-3 pt-3">
                    <div class="form-group">
                        <input type="text" class="form-control mb-3" name="title" placeholder="{{ translate('Your Name') }}" required>
                        <input type="hidden" class="form-control mb-3" name="user_id" value="{{$shop->user_id}}">
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" rows="8" name="message" required placeholder="{{ translate('Your Question') }}"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary fw-600">{{ translate('Send')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-zoom" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600">{{ translate('Login')}}</h6>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="p-3">
                    <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            @if (addon_is_activated('otp_system'))
                            <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone')}}" name="email" id="email">
                            @else
                            <input type="email" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                            @endif
                            @if (addon_is_activated('otp_system'))
                            <span class="opacity-60">{{ translate('Use country code before number') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <input type="password" name="password" class="form-control h-auto form-control-lg" placeholder="{{ translate('Password')}}">
                        </div>

                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <span class=opacity-60>{{ translate('Remember Me') }}</span>
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                            <div class="col-6 text-right">
                                <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ translate('Forgot password?')}}</a>
                            </div>
                        </div>

                        <div class="mb-5">
                            <button type="submit" class="btn btn-primary btn-block fw-600">{{ translate('Login') }}</button>
                        </div>
                    </form>

                    <div class="text-center mb-3">
                        <p class="text-muted mb-0">{{ translate('Dont have an account?')}}</p>
                        <a href="{{ route('user.registration') }}">{{ translate('Register Now')}}</a>
                    </div>
                    @if(get_setting('google_login') == 1 ||
                    get_setting('facebook_login') == 1 ||
                    get_setting('twitter_login') == 1)
                    <div class="separator mb-3">
                        <span class="bg-white px-3 opacity-60">{{ translate('Or Login With')}}</span>
                    </div>
                    <ul class="list-inline social colored text-center mb-5">
                        @if (get_setting('facebook_login') == 1)
                        <li class="list-inline-item">
                            <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                <i class="lab la-facebook-f"></i>
                            </a>
                        </li>
                        @endif
                        @if(get_setting('google_login') == 1)
                        <li class="list-inline-item">
                            <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                <i class="lab la-google"></i>
                            </a>
                        </li>
                        @endif
                        @if (get_setting('twitter_login') == 1)
                        <li class="list-inline-item">
                            <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                <i class="lab la-twitter"></i>
                            </a>
                        </li>
                        @endif
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        getVariantPrice();
    });

    function CopyToClipboard(e) {
        var url = $(e).data('url');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(url).select();
        try {
            document.execCommand("copy");
            AIZ.plugins.notify('success', '{{ translate("Link copied to clipboard ") }}');
        } catch (err) {
            AIZ.plugins.notify('danger', '{{ translate("Oops, unable to copy ") }}');
        }
        $temp.remove();
        // if (document.selection) {
        //     var range = document.body.createTextRange();
        //     range.moveToElementText(document.getElementById(containerid));
        //     range.select().createTextRange();
        //     document.execCommand("Copy");

        // } else if (window.getSelection) {
        //     var range = document.createRange();
        //     document.getElementById(containerid).style.display = "block";
        //     range.selectNode(document.getElementById(containerid));
        //     window.getSelection().addRange(range);
        //     document.execCommand("Copy");
        //     document.getElementById(containerid).style.display = "none";

        // }
        // AIZ.plugins.notify('success', 'Copied');
    }

    function show_chat_modal() {

        @if(Auth::check())
        $('#chat_modal').modal('show');
        @else
        $('#login_modal').modal('show');
        @endif
    }
</script>
@endsection