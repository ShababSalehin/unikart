@extends('frontend.layouts.app')

@section('content')

<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-start">
            @include('frontend.inc.user_side_nav')
            <div class="aiz-user-panel">
            <h5 class="mb-3 h6">{{ translate('My Followed Shop') }}</h5>
            @if (count($shops) > 0)
            @foreach($shops as $shop)
                <div class="card">
                    <div class="card-header">
                    <h5 class="mb-3 h6">
                        @if($shop->logo)
                        
                      <img height="60" class="lazyload" src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="@if ($shop->logo !== null) {{ uploaded_asset($shop->logo) }} @else {{ static_asset('assets/img/placeholder.jpg') }} @endif" alt="{{ $shop->name }}">
                        
                        @else
                        <img style="width: 60px;" src="{{static_asset('assets/img/placeholder.jpg')}}">
                        @endif

                    {{ $shop->name }}
                </h5>
                    </div>
                    
                        <div class="card-body">
                            
                            
                        </div>
                    
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</section>

@endsection

@section('modal')
    @include('modals.delete_modal')

    <div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div id="order-details-modal-body">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div id="payment_modal_body">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        $('#order_details').on('hidden.bs.modal', function () {
            location.reload();
        })
    </script>

@endsection