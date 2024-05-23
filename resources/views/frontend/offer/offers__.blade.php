@extends('frontend.layouts.app')

@if (isset($category_id))
@php
$meta_title = \App\Category::find($category_id)->meta_title;
$meta_description = \App\Category::find($category_id)->meta_description;
@endphp
@elseif (isset($brand_id))
@php
$meta_title = \App\Brand::find($brand_id)->meta_title;
$meta_description = \App\Brand::find($brand_id)->meta_description;
@endphp
@else
@php
$meta_title = get_setting('meta_title');
$meta_description = get_setting('meta_description');
@endphp
@endif

@section('meta_title'){{ $meta_title }}@stop
@section('meta_description'){{ $meta_description }}@stop

@section('meta')
@php 
$cart = Session::get('cart');
$cartIds = array();
$cartqty = array();
$keys = array();
if (is_array($cart) || is_object($cart))
{
foreach($cart as $key => $cartItem){
$cartIds[] = $cartItem['id'];
$cartqty[$cartItem['id']] = $cartItem['quantity'];
$keys[$cartItem['id']] = $key;
}
}
@endphp
<!-- Schema.org markup for Google+ -->
<meta itemprop="name" content="{{ $meta_title }}">
<meta itemprop="description" content="{{ $meta_description }}">

<!-- Twitter Card data -->
<meta name="twitter:title" content="{{ $meta_title }}">
<meta name="twitter:description" content="{{ $meta_description }}">

<!-- Open Graph data -->
<meta property="og:title" content="{{ $meta_title }}" />
<meta property="og:description" content="{{ $meta_description }}" />
@endsection

@section('content')

<section class="mb-4 pt-3">
    <div class="container sm-px-0">
   
    <div class="row mb-2">
    <div class="col-md-4"><hr style="background:#AE3C86;height:2px;"></div>
    <div class="col-md-4" style="font-size:24px;font-weight:bold;text-align:center;">Discounted Product</div>
    <div class="col-md-4"><hr style="background:#AE3C86;height:2px;"></div>
    <div class="clearfix"></div>
</div>
       
            <div class="row">
            
                
                <div class="col-xl-12">

                    <input type="hidden" name="min_price" value="">
                    <input type="hidden" name="max_price" value="">
                    <div class="row gutters-5 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-sm-3 row-cols-2">
                        @foreach ($products as $key => $product)
                        <form class="pro_list_block" id="option-choice-form_{{ $product->id }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <div class="col mb-3">
                                <div class="aiz-card-box h-100 border border-light rounded shadow-sm hov-shadow-md has-transition bg-white">
                                    <div class="position-relative">
                                        <a style="text-align:center;" href="{{ route('product', $product->slug) }}" class="d-block">
                                            <img class="lazyload mx-auto h-160px" src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($product->thumbnail_img) }}" alt="{{  $product->getTranslation('name')  }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </a>
                                        <div class="absolute-top-right aiz-p-hov-icon">
                                            <a href="javascript:void(0)" onclick="addToWishList({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                                                <i class="la la-heart-o"></i>
                                            </a>
                                            <!-- <a href="javascript:void(0)" onclick="addToCompare({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left">
                                                <i class="las la-sync"></i>
                                            </a> -->
                                            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to cart') }}" data-placement="left">
                                                <i class="las la-shopping-cart"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="p-md-3 p-2 text-center">
                                        <div class="fs-15">
                                            @if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                            <del class="fw-600 opacity-50 mr-1">{{ home_base_price($product->id) }}</del>
                                            @endif
                                            <span class="fw-700 text-primary">{{ home_discounted_base_price($product->id) }}</span>
                                        </div>
                                        <div class="rating rating-sm mt-1">
                                            {{ renderStarRating($product->rating) }}
                                        </div>
										@if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                <h3 class="fw-600 fs-13 lh-1-4 mb-0" style="color:red">
									@if($product->discount_type=='amount')
										{{$product->discount}} TK Save
									@else 
										{{$product->discount}} % Off
									@endif		
								<h3>
								@else
									<h3 class="fw-600 fs-13 lh-1-4 mb-0">&nbsp;</h3>		
								@endif
                                        <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0">
                                            <a href="{{ route('product', $product->slug) }}" class="d-block text-reset">{{ $product->getTranslation('name') }}</a>
                                        </h3>

                                        @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated)
                                        <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                            {{ translate('Club Point') }}:
                                            <span class="fw-700 float-right">{{ $product->earn_point }}</span>
                                        </div>
                                        @endif
                                        <div class="mt-3">
                                        @php 
                                        if(in_array($product->id,$cartIds)==true){
                                            $pkey = $keys[$product->id];
                                       $procartqty = $cartqty[$product->id];
                                       $showt = 'block';
                                       $showf = 'none';

                                        }elseif(in_array($product->id,$cartIds)==false){
                                            $pkey = '';
                                        $procartqty = '';
                                        $showf = 'block';
                                        $showt = 'none';
                                        
                                        }
                                        

                                        @endphp
                                        @if ($product->outofstock==0)
                                        <div id="pro_cart_in_{{$product->id}}" style="display: {{$showt}};">
                                        <button type="button" onclick="updateQuantityM({{ $pkey }}, {{$procartqty}},{{ $product->id }})" style="width:15%;padding: 5px 4px;background-color: #dba9c9;border: 1px solid #dba9c9;border-top-right-radius: 0;border-bottom-right-radius: 0;" class="btn btn-primary buy-now fw-600 " >
                                                <i class="la la-minus"></i></button>
                                                <button type="button" style="width:65%;padding:5px 10px;border-radius: 0;" class="btn btn-primary buy-now fw-600 add-to-cart" onclick="directAdd({{ $product->id }},1)">
                                                <i class="la la-shopping-cart"></i>
                                                <span class="d-none d-md-inline-block"><span id="productlist_{{$product->id}}">{{$procartqty}}</span> in cart</span>
                                            </button>
                                            <button type="button" onclick="updateQuantityP({{ $pkey }}, {{$procartqty}},{{ $product->id }})" style="width:15%;padding: 5px 4px;background-color: #dba9c9;border: 1px solid #dba9c9;border-top-left-radius: 0;border-bottom-left-radius: 0;" class="btn btn-primary buy-now fw-600 ">
                                                <i class="la la-plus"></i></button></div>
                                     
                                            <button id="pro_add_to_cart_{{$product->id}}"  style="padding:5px 10px;width:100%;display: {{$showf}};" type="button" style="width:100%;padding:5px 10px" class="btn btn-primary buy-now fw-600 add-to-cart" onclick="directAdd({{ $product->id }})">
                                                <i class="la la-shopping-cart"></i>
                                                <span class="d-none d-md-inline-block"> Add to cart</span>
                                            </button>
                                            
                                            @else
                                    <button type="button" class="btn btn-secondary fw-600" disabled>
                                        <i class="la la-cart-arrow-down"></i> {{ translate('Out of Stock')}}
                                    </button>
                                @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endforeach
                    </div>
                    <div class="aiz-pagination aiz-pagination-center mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
       
    </div>
</section>

@endsection

@section('script')
<script type="text/javascript">
    function filter() {
        $('#search-form').submit();
    }

    function rangefilter(arg) {
        $('input[name=min_price]').val(arg[0]);
        $('input[name=max_price]').val(arg[1]);
        filter();
    }
</script>
@endsection
