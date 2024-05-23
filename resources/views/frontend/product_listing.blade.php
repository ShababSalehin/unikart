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
        $meta_title         = get_setting('meta_title');
        $meta_description   = get_setting('meta_description');
    @endphp
@endif

@section('meta_title'){{ $meta_title }}@stop
@section('meta_description'){{ $meta_description }}@stop

@section('meta')
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

@php
$discountar= array('2'=>'2-5%','6'=>'6-10%','11'=>'11-20%','21'=>'21-30%','30'=>'30%- Avobe');
@endphp

    <section class="mb-4 pt-3">
        <div class="container sm-px-0">
            <form class="" id="search-form" action="" method="GET">
                <div class="row">
                    <div class="col-xl-2 p-0">
                        <div class="aiz-filter-sidebar collapse-sidebar-wrap sidebar-xl sidebar-right z-1035">
                            <div class="overlay overlay-fixed dark c-pointer" data-toggle="class-toggle" data-target=".aiz-filter-sidebar" data-same=".filter-sidebar-thumb"></div>
                            <div class="collapse-sidebar c-scrollbar-light text-left">
                                <div class="d-flex d-xl-none justify-content-between align-items-center pl-3 border-bottom">
                                    <p class="h6 mb-0 fw-600">{{ translate('Filters') }}</p>
                                    <button type="button" class="btn btn-sm p-2 filter-sidebar-thumb" data-toggle="class-toggle" data-target=".aiz-filter-sidebar" >
                                        <i class="las la-times la-2x"></i>
                                    </button>
                                </div>

                                <div class="filter_by_price bg-white shadow-sm rounded mb-3">
                                <div class="fs-15 fw-600 p-3 border-bottom">
                                    {{ translate('Filter by price')}}
                                </div>
                                <div class="p-3">
                                    <div class="row mt-2 px-2">
                                        <input class="form-control col-4"  value="@if(!empty(request()->min_price)){{request()->min_price}} @endif" name="min_price"  placeholder="Min" >
                                        <div class="col-1" style="padding: 0;text-align: center;line-height: 27px;">-</div>
                                        <input class="form-control col-4" name="max_price"  value="@if(!empty(request()->max_price)){{request()->max_price}} @endif"  placeholder="Max" >
                                        <button class="btn btn-primary buy-now fw-600 add-to-cart col-2 ml-2"><span class="las la-play"></span></button>
                                    </div>
                                    
                                </div>
                            </div>


                            @php
                                // Get all the brand IDs of the searched products
                                $searchedBrandIds = $products->pluck('brand_id')->unique();
                            @endphp

                            <div class="bg-white shadow-sm rounded mb-3">
                                <div class="fs-15 fw-600 p-3 border-bottom">
                                    {{ translate('Filter by Brand') }}
                                </div>
                                <div class="p-3">
                                    <div class="aiz-radio-inline" id="brand_list">
                                        @foreach (\App\Brand::whereIn('id', $searchedBrandIds)->get() as $brand)
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="brand_id[]" class="custom-control-input" value="{{ $brand->id }}" @if (in_array($brand->id, $brand_ids)) checked @endif onchange="filter()">
                                                <label class="custom-control-label" for="brand">{{ $brand->getTranslation('name') }}</label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div id="loadMore">View More</div>
                                    <div id="showLess">View Less</div>
                                </div>
                            </div>




                            <div class="bg-white shadow-sm rounded mb-3">
                                <div class="fs-15 fw-600 p-3 border-bottom">
                                    {{ translate('Filter by Discount')}}
                                </div>
                                <div class="p-3">
                                    <div class="aiz-checkbox-list">
                                        @foreach ($discountar as $key => $discount)
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" name="discount[]" class="custom-control-input" value="{{ $key }}" @if(in_array($key,$discounts)) checked @endif onchange="filter()">
                                            <span style="margin-top:-4px;" class="aiz-square-check"></span>
                                            <span for="discountar">{{ $discount  }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white shadow-sm rounded mb-3">
                                <div class="fs-15 fw-600 p-3 border-bottom">
                                    {{ translate('Filter by Rating')}}
                                    <input type="hidden" id="product_review" name="product_review">
                                </div>
                                <div class="p-3">
                                    <div class="aiz-radio-inline">
                                        <div class="custom-control custom-checkbox mr-sm-2" style="padding-left:0px !important;">
                                            <a href="javascript:" onclick="filter_review(5)">
                                                <span class="rating rating-sm">
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                </span>
                                                </a>
                                        </div>

                                        <div class="custom-control custom-checkbox mr-sm-2" style="padding-left:0px !important;">
                                        <a href="javascript:" onclick="filter_review(4)">
                                                <span class="rating rating-sm">
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                </span>
                                                </a>
                                            
                                        </div>
                                        <div class="custom-control custom-checkbox mr-sm-2" style="padding-left:0px !important;">
                                        <a href="javascript:" onclick="filter_review(3)">
                                        <span class="rating rating-sm">
  
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                </span>
                                                </a>
                                        </div>

                                        <div class="custom-control custom-checkbox mr-sm-2" style="padding-left:0px !important;">
                                        <a href="javascript:" onclick="filter_review(2)">
                                        <span class="rating rating-sm">
                                           
                                                    <i class="las la-star active"></i>
                                                    <i class="las la-star active"></i>
                                                    
                                                </span></label>
                                                </a>
                                        </div>
                                        <div class="custom-control custom-checkbox mr-sm-2" style="padding-left:0px !important;">
                                        <a href="javascript:" onclick="filter_review(4)">
                                                <span class="rating rating-sm">
                                                    <i class="las la-star active"></i>
                                                </span>
                                                </a>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    @php
                    $count = 0; 
                    @endphp
                                
                                @foreach ($attributes as $attribute)
                                
                                    <div class="bg-white shadow-sm rounded mb-3">
                                        <div class="fs-15 fw-600 p-3 border-bottom">
                                            {{ translate('Filter by') }} {{ $attribute->getTranslation('name') }}
                                        </div>
                                        <div class="p-3">
                                            <div class="aiz-checkbox-list others-attributes" id="attribute_list-{{$attribute->id}}" >
                                           
                                                @foreach ($attribute->attribute_values as $attribute_value)
                                                    <label class="aiz-checkbox">
                                                        <input
                                                            type="checkbox"
                                                            name="selected_attribute_values[]"
                                                            value="{{ $attribute_value->value }}" @if (in_array($attribute_value->value, $selected_attribute_values)) checked @endif
                                                            onchange="filter()"
                                                        >
                                                        <span style="margin-top:-2px;" class="aiz-square-check"></span>
                                                        <span>{{ $attribute_value->value }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="ViewMore" id="ViewMore-{{$attribute->id}}" onclick="ViewMore({{$attribute->id}})">View More</div>
                                    <div class="ViewLess" id="ViewLess-{{$attribute->id}}" onclick="ViewLess({{$attribute->id}})">View Less</div>
                                        </div>
                                    </div>
                                @endforeach

                                

                                {{-- <button type="submit" class="btn btn-styled btn-block btn-base-4">Apply filter</button> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-10">

                        <ul class="breadcrumb bg-transparent p-0">
                            <li class="breadcrumb-item opacity-50">
                                <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                            </li>
                            @if(!isset($category_id))
                                <li class="breadcrumb-item fw-600  text-dark">
                                    <a class="text-reset" href="{{ route('search') }}">"{{ translate('All Categories')}}"</a>
                                </li>
                            @else
                                <li class="breadcrumb-item opacity-50">
                                    <a class="text-reset" href="{{ route('search') }}">{{ translate('All Categories')}}</a>
                                </li>
                            @endif
                            @if(isset($category_id))
                                <li class="text-dark fw-600 breadcrumb-item">
                                    <a class="text-reset" href="{{ route('products.category', \App\Category::find($category_id)->slug) }}">"{{ \App\Category::find($category_id)->getTranslation('name') }}"</a>
                                </li>
                            @endif
                        </ul>

                        <div class="text-left">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h1 class="h6 fw-600 text-body">
                                        @if(isset($category_id))
                                            {{ \App\Category::find($category_id)->getTranslation('name') }}
                                        @elseif(isset($query))
                                            {{ translate('Search result for ') }}"{{ $query }}"
                                        @else
                                            {{ translate('All Products') }}
                                        @endif
                                    </h1>
                                    <input type="hidden" name="keyword" value="{{ $query }}">
                                </div>
                                <!-- <div class="form-group ml-auto mr-0 w-200px d-none d-xl-block">
                                    <label class="mb-0 opacity-50">{{ translate('Brands')}}</label>
                                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="brand" onchange="filter()">
                                        <option value="">{{ translate('All Brands')}}</option>
                                        @foreach (\App\Brand::all() as $brand)
                                            <option value="{{ $brand->slug }}" @isset($brand_id) @if ($brand_id == $brand->id) selected @endif @endisset>{{ $brand->getTranslation('name') }}</option>
                                        @endforeach
                                    </select>
                                </div> -->
                                <div class="form-group ml-auto mr-0 w-200px d-none d-xl-block">
                                    <label class="mb-0 opacity-50">{{ translate('Sort by')}}</label>
                                    <select class="form-control form-control-sm aiz-selectpicker" name="sort_by" onchange="filter()">
                                        <!-- <option value="newest" @isset($sort_by) @if ($sort_by == 'newest') selected @endif @endisset>{{ translate('Newest')}}</option>
                                        <option value="oldest" @isset($sort_by) @if ($sort_by == 'oldest') selected @endif @endisset>{{ translate('Oldest')}}</option> -->
                                        <option value="price-asc" @isset($sort_by) @if ($sort_by == 'price-asc') selected @endif @endisset>{{ translate('Price low to high')}}</option>
                                        <option value="price-desc" @isset($sort_by) @if ($sort_by == 'price-desc') selected @endif @endisset>{{ translate('Price high to low')}}</option>
                                    </select>
                                </div>
                                <div class="d-xl-none ml-auto ml-xl-3 mr-0 form-group align-self-end">
                                    <button type="button" class="btn btn-icon p-0" data-toggle="class-toggle" data-target=".aiz-filter-sidebar">
                                        <i class="la la-filter la-2x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- <input type="hidden" name="min_price" value="">
                        <input type="hidden" name="max_price" value=""> -->
                        <div class="row gutters-5 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-2">
                            @foreach ($products as $key => $product)
                                <div class="col">
                                    @include('frontend.partials.product_box_1',['product' => $product])
                                </div>
                            @endforeach
                        </div>
                        <div class="aiz-pagination aiz-pagination-center mt-4">
                            {{ $products->appends(request()->input())->links() }}
                        </div>
                        @if($categorydata)
                        <div class="px-2 py-4 px-md-4 py-md-3 mt-5 bg-white shadow-sm rounded">
                        {!! $categorydata->description !!}
                        </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        function filter(){
            $('#search-form').submit();
        }

        // function rangefilter(arg){
        //     $('input[name=min_price]').val(arg[0]);
        //     $('input[name=max_price]').val(arg[1]);
        //     filter();
        // }
        
        function filter_review(arg) {
        $('#product_review').val(arg);
        filter();
    }

        $(document).ready(function () {
        var size_li = $("#brand_list").find(".custom-checkbox").length;
        size_l = size_li;
        var x = 5;
        if(size_li <= 5){
            $('#loadMore').hide();  
        }
        $('#brand_list .custom-checkbox:gt('+(x-1)+')').hide();

        $('#loadMore').click(function () {
        
        $('#brand_list .custom-checkbox:lt('+size_l+')').show();
        $('#showLess').show();
        $('#loadMore').hide();
        
    });

    $('#showLess').click(function () {
        
        $('#brand_list .custom-checkbox').not(':lt('+x+')').hide();
        $('#showLess').hide();
        $('#loadMore').show();
        
    });

    });

    //  for all attribute view more

    $(document).ready(function () {
        $(".others-attributes").each(function(i,j){
            var id = $(this).attr('id').split('-')[1];
            var dd =0;
            dd = $('#attribute_list-'+id).find(".aiz-checkbox").length;
            var d = dd;
        var y = 5;
        if(dd > 5){
                $('#ViewLess-'+id).hide();  
            }else{
                $('#ViewMore-'+id).hide();  
                $('#ViewLess-'+id).hide();  
            }
            $('#attribute_list-'+id+' .aiz-checkbox:gt(4)').hide();

        })
      
    });

function ViewMore(id){
    var aa = $('#attribute_list-'+id).find(".aiz-checkbox").length;
    $('#attribute_list-'+id+' .aiz-checkbox:lt('+aa+')').show();
        $('#ViewLess-'+id).show();
        $('#ViewMore-'+id).hide();
}

function ViewLess(id){
    $('#attribute_list-'+id+' .aiz-checkbox').not(':lt(4)').hide();
    $('#ViewLess-'+id).hide();
        $('#ViewMore-'+id).show();
}


  

</script>
@endsection
