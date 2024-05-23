@extends('frontend.layouts.app')

@section('content')

<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('Flash Deals')}}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">
                            {{ translate('Home')}}
                        </a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('flash-deals') }}">
                            "{{ translate('Flash Deals') }}"
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="mb-4">
    <div class="container">
        <div class="row row-cols-1 row-cols-lg-2 gutters-10">
            @foreach($all_flash_deals as $single)
          
            <div class="col">
                <div class="bg-white rounded shadow-sm mb-3">
                    <a href="{{ route('flash-deal-details', $single->slug) }}" class="d-block text-reset">
                        <img
                            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($single->banner) }}"
                            alt="{{ $single->title }}"
                            class="img-fluid lazyload rounded w-100">
                    </a>
                </div>
                
                <div class="text-center my-4 text-{{ $single->text_color }}">
                    <h1 class="h2 fw-600">{{ $single->title }}</h1>
                    <h4 class="h2 fw-400">Seller Join End Date</h4>
                    <div class="aiz-count-down aiz-count-down-lg ml-3 align-items-center justify-content-center" data-date="{{ date('Y/m/d H:i:s', $single->seller_joinend_date) }}">
      
                    </div>
                    <p style="font-size: .875rem" class="mb-0">Start Date:{{ date('d/m/Y', $single->start_date) }}</p>
                    <p style="font-size: .875rem" class="mb-0">End Date:{{ date('d/m/Y', $single->end_date) }}</p>
                    <a href="{{route('flash_deals.edit', ['id'=>$single->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}">
                    <button type="button" class="btn btn-info btn-lg">Join</button>
                   </a>
                 </div>
               </div>
            @endforeach
         </div>
    </div>
</section>
@endsection
