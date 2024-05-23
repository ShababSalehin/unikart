@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">Note : Included with (Pending + Confirmed + PickedUp + OnTheWay + Delivered)</h1>
    </div>
</div>

@php
$refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp

<div class="card">
    <form  id="culexpo" class="" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ translate('Sales Report') }}</h5>
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
   
            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code & Hit Enter')}}">
                </div><br>
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search2" name="search2" @isset($sort_search2) value="{{ $sort_search2 }}" @endisset placeholder="{{ translate('Type Customer Phone & Hit Enter')}}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                <button class="btn btn-sm btn-primary" onclick="submitForm ('{{ route('salesReport.index') }}')">{{ translate('Filter') }}</button>
                    <button class="btn btn-sm btn-info" onclick="submitForm('{{ route('sales_ledger_export') }}')">Excel</button>
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
    <h3 style="text-align:center;">{{translate('Sales Report')}}</h3>
        <table class="table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>{{ translate('Date') }}</th>
                    <th>{{ translate('Order Code') }}</th>
                    <th data-breakpoints="md">{{ translate('Customer Name') }}</th>
                    <th data-breakpoints="md">{{ translate('Customer Phone') }}</th>
                    <th data-breakpoints="md">{{ translate('Customer Address') }}</th>
                    <th data-breakpoints="md">{{ translate('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                $total = 0;
                $totalpaid = 0;
                $totaldue = 0;
            	$i = 0;

                @endphp
                @foreach ($orders as $key => $order)
                @php
                
             $delivery_status = $order->orderDetails->first();
            $error = 0;
            if(empty($delivery_status)){
             $error = 1;
            continue;
            }else{
             if($delivery_status->delivery_status=='cancel' || $delivery_status->delivery_status=='pending'){
             $error = 1;
                    continue;
                }
            }
            
            
            
            if(!empty(\App\Customer::where('user_id', $order->user_id)->first()))
            $customer_id = \App\Customer::where('user_id', $order->user_id)->first()->customer_id;
            else
            $customer_id = '';
                $payment_details = json_decode($order->payment_details);

                if(!empty($payment_details) && !empty($payment_details->status) && ($payment_details->status=='VALID')){
                    $totalpaid+=$payment_details->amount;
                    $paid =$payment_details->amount;
                    $totaldue+=($order->grand_total-$paid);
                    $due = $order->grand_total-$paid;
                }else{
                    $totaldue+=$order->grand_total;
                    $due = $order->grand_total;
                    $paid = 0;
                }

                $total+=$order->grand_total;
                @endphp

            @if( $error == 0)
            
            @php
            $shipping = json_decode($order->shipping_address);
            $i++;
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
                 
                    <td>
                        @if ($order->user != null)
                        {{ $order->user->name }}
                        @else
							@php 
								$shipping = json_decode($order->shipping_address);
								if(!empty($shipping)){
									echo $shipping->name;
								}else{
									echo 'Guest';
								}
							@endphp
                        @endif
                    </td>
                   
                    <td style="text-align:center;">
                        {{$shipping->phone }}
                    </td>
                    <td style="text-align:center;">
							@php 
								$shipping = json_decode($order->shipping_address);
								if(!empty($shipping)){
									echo $shipping->address;
								}else{
									echo 'Guest';
								}
							@endphp
                    </td>
                    <td style="text-align:right;">
                        {{ single_price($order->grand_total) }}
                    </td>
                </tr>
            @endif

            
                @endforeach
                <tr>
                    <td style="text-align:right;" colspan="6"><b>Total</b></td>
                    <td style="text-align:right;"><b>{{single_price($total)}}</b></td>
                    
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