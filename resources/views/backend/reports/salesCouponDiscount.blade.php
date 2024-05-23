@extends('backend.layouts.app')

@section('content')
@php
$refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp

<div class="card">
    <form  id="culexpo" class="" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ translate('Sales Coupon Discount Report') }}</h5>
            </div>
            <div class="col-md-3">
                        <label class="col-form-label">{{translate('Sort by Shop')}} :</label>
                            <select id="seller" class="from-control aiz-selectpicker" name="seller_id">
                                <option value=''>All</option>
                                @foreach (\App\Shop::all() as $key => $seller)
                                <option @php if($filter_seller ==$seller->user_id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $seller->user_id }}">{{ $seller->name }}</option>
                                @endforeach
                            </select>
                        </div>
            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <!-- <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ translate('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off"> -->
                    <label>Date Range :</label>
                            <input type="date" name="start_date" class="form-control" value="{{$start_date}}">
                            <input type="date" name="end_date" class="form-control" value="{{$end_date}}">
                </div>
            </div>
   
            
            <div class="col-auto">
                <div class="form-group mb-0">
                <button class="btn btn-sm btn-primary" onclick="submitForm ('{{ route('salesCouponDiscountReport.index') }}')">{{ translate('Filter') }}</button>
                    <button class="btn btn-sm btn-info" onclick="submitForm('{{ route('sales_coupon_discount_ledger_export') }}')">Excel</button>
                    <button class="btn btn-sm btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>                    
                </div>
            </div>
        </div>
    </form>
    <div class="card-body printArea">
    <style>
th{
    text-align:center;
}
</style>
    <h3 style="text-align:center;">{{translate('Sales Coupon Discount Report')}}</h3>
        <table class="table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>{{ translate('Date') }}</th>
                    <th>{{ translate('Order Code') }}</th>
                    <th data-breakpoints="md">{{ translate('Seller Discount') }}</th>
                    <th data-breakpoints="md">{{ translate('Unikart Discount') }}</th>
                    
                </tr>
            </thead>
            <tbody>
                @php
                $total_seller_coupon_discount = 0;
                $total_unikart_coupon_discount = 0;
                
            	$i = 0;

                @endphp
                @foreach ($orders as $key => $order)
                @php
                    
                    if($order->seller_coupon_discount<=0 && $order->unikart_coupon_discount<=0){
                        continue;
                    }
                    $i++;
                    $total_seller_coupon_discount+=$order->seller_coupon_discount;
                    $total_unikart_coupon_discount+=$order->unikart_coupon_discount;
                @endphp

            
            
          
                <tr>
                    <td>
                        {{ ($i) }}
                    </td>
                    <td>
                        {{ date('d-m-Y',$order->date) }}
                    </td>
                    <td style="text-align:center;">
                    <a href="{{route('all_orders.show', encrypt($order->id))}}" target="_blank" title="{{ translate('View') }}">{{ $order->code }}</a>
                    </td>
                 
                    <td style="text-align:right;">
                        {{$order->seller_coupon_discount }}
                    </td>
                   
                    <td style="text-align:right;">
                        {{$order->unikart_coupon_discount }}
                    </td>
                   
                </tr>
            @endforeach

            
                
                <tr>
                    <td style="text-align:right;" colspan="3"><b>Total</b></td>
                    <td style="text-align:right;"><b>{{single_price($total_seller_coupon_discount)}}</b></td>
                    <td style="text-align:right;"><b>{{single_price($total_unikart_coupon_discount)}}</b></td>
                    
                </tr>
            </tbody>
        </table>

    </div>
</div>

<script type="text/javascript">
 function submitForm(url){
    $('#culexpo').attr('action',url);
    $('#culexpo').submit();
 }
</script>

@endsection

@section('modal')
@include('modals.delete_modal')
@endsection

@section('script')


@endsection