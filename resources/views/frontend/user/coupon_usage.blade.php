@extends('frontend.layouts.app')

@section('content')

<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-start">
            @include('frontend.inc.user_side_nav')
            <div class="aiz-user-panel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Coupon Usages History') }}</h5>
                    </div>
                    @if (count($coupons) > 0)
                        <div class="card-body">
                            <table class="table aiz-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Order Code')}}</th>
                                        <th>{{ translate('Coupon Code')}}</th>
                                        <th data-breakpoints="md">{{ translate('Date')}}</th>
                                        <th>{{ translate('Amount')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php 
                                    $total = 0;
                                @endphp
                                    @foreach ($coupons as $key => $coupon)
                                    @php
                                        $total+=$coupon->coupon_discount;
                                    @endphp
                                            <tr>
                                                <td>
                                                    <a href="#{{ $coupon->code }}" onclick="show_purchase_history_details({{ $coupon->id }})">{{ $coupon->code }}</a>
                                                    
                                                </td>
                                                <td>{{ $coupon->coupon_code }}</td>
                                                <td>{{ date('d-m-Y', $coupon->date) }}</td>
                                                <td style="text-align: right;">
                                                    {{ single_price($coupon->coupon_discount) }}
                                                </td>
                                                
                                            </tr>
                                    @endforeach
                                    <tr>
                                                <td colspan="3" style="text-align:right">
                                                    Total
                                                </td>
                                                <td  style="text-align: right;">
                                                    {{ single_price($total) }}
                                                </td>
                                                
                                            </tr>
                                </tbody>
                            </table>
                            
                        </div>
                    @endif
                </div>
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