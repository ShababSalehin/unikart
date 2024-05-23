@extends('frontend.layouts.sellerapp')

@section('content')
<div class="row">
   
 <div class="card">
          <div class="card-header">
                  <h5 class="mb-0 h6">{{ translate('Campaign') }}</h5>
              </div>

              <div class="card-body row gutters-5">
              @foreach($all_flash_deals as $single)
              <div class="col-md-4">
                  <div class="border aiz-titlebar">
                  <a href="{{ route('flash-deal-details', $single->slug) }}" class="d-block text-reset">
                        <img
                            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($single->banner) }}"
                            alt="{{ $single->title }}"
                            class="img-fluid lazyload rounded w-100">
                    </a>
                      <div class="p-3">

                      <div class="text-center my-4 text-{{ $single->text_color }}">
                    <h1 class="h2 fw-600">{{ $single->title }}</h1>
                    <h4 class="h2 fw-400">Seller Join End Date</h4>
                    <div class="aiz-count-down aiz-count-down-lg ml-3 align-items-center justify-content-center" data-date="{{ date('Y/m/d H:i:s', $single->seller_joinend_date) }}">
      
                    </div>
                    <p style="font-size: .875rem" class="mb-0">Campaign Start Date:{{ date('d/m/Y', $single->start_date) }}</p>
                    <p style="font-size: .875rem" class="mb-0">Campaign End Date:{{ date('d/m/Y', $single->end_date) }}</p> <a href="{{route('flash_deals.edit', ['id'=>$single->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}">
                    <button type="button" class="btn btn-info btn-lg">Join</button>
                   </a>
                 </div>
                      </div>
                      

                  </div>
              
              </div>
              @endforeach
          
          </div>
        </div>
        
    </div>

    

    

@endsection
