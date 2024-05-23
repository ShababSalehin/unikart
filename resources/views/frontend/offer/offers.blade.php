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
                        <div class="col">
                                    @include('frontend.partials.product_box_1',['product' => $product])
                                </div>
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
