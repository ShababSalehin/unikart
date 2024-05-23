@if(get_setting('home_categories') != null)
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
@endif