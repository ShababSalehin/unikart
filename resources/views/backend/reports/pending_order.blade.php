@extends('backend.layouts.app')

@section('content')
@php
$refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp

<div class="card">
    <form  id="culexpo" class="" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ translate('Confirm Order Report') }}</h5>
            </div>
           

            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="from_order_no" name="from_order_no" @isset($from_order_no) value="{{ $from_order_no }}" @endisset placeholder="{{ translate('From Order No')}}">
                </div><br>
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="to_order_no" name="to_order_no" @isset($to_order_no) value="{{  $to_order_no }}" @endisset placeholder="{{ translate('To Order No')}}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                <button class="btn btn-sm btn-primary" onclick="submitForm ('{{ route('orderReport.index') }}')">{{ translate('Filter') }}</button>
                    <button class="btn btn-sm btn-info" onclick="submitForm('{{ route('sales_pending_order_ledger_export') }}')">Excel</button>
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
    <h3 style="text-align:center;">{{translate('Confirm Order Report')}}</h3>
        <table class="table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SL</th>
                    <th data-breakpoints="md">{{ translate('Order No') }}</th>
                    <th>{{ translate('Invoice') }}</th>
                    
                    <th data-breakpoints="md">{{ translate('Customer Name') }}</th>
                    <th data-breakpoints="md">{{ translate('Contact No.') }}</th>
                    <th data-breakpoints="md">{{ translate('Customer Address') }}</th>
                    <th data-breakpoints="md">{{ translate('District') }}</th>
                    <th data-breakpoints="md">{{ translate('Area') }}</th>
                    <th data-breakpoints="md">{{ translate('Price') }}</th>
                    <th data-breakpoints="md">{{ translate('Product Selling Price') }}</th>
                    <th data-breakpoints="md">{{ translate('Weight') }}</th>
                    <th data-breakpoints="md">{{ translate('Instruction') }}</th>
                   
                  
                </tr>
            </thead>
            <tbody>
                @php 
                $i = 0;
                @endphp
            @foreach ($orders as $key => $order)
                @php 
                    $shipping = json_decode($order->shipping_address);
                    $state_name = \App\State::where('id', $shipping->state_id)->first()->name;                            
                    $city_name = \App\City::where('id', $shipping->city_id)->first()->name;

                    $all_order=\App\Order::where('combined_order_id',$order->combined_order_id)
                    ->whereNotNull('combined_order_id')
                    ->get();
                    
                    //print_r($all_order);
                   // exit;

                    $subtotal = 0;
                    $discount = 0;
                    $cdiscount = 0;
                    $shipping_cost = 0;

                    $i++;
                @endphp
                
                @foreach($all_order as $key1=>$value)
                    @php 
                        $subtotal += $value->orderDetails->sum('price')+$value->orderDetails->sum('discount');
                        $discount += $value->orderDetails->sum('discount');
                        $shipping_cost += $value->orderDetails->sum('shipping_cost');
                        $cdiscount += $value->coupon_discount;
                    @endphp
                  
                @endforeach
                @php 
                	if($order->payment_status=='unpaid'){
                    	$net_total=$subtotal+$shipping_cost-$discount-$cdiscount;
                	}else{
                    	$net_total=0;
                	}
                @endphp
                
            <tr>
                    <td>
                        {{ ($i) }}
                    </td>

                    <td style="text-align:right;">
                        {{ $order->id }}
                    </td>
                   
                    <td style="text-align:right;">
                        {{ $order->code }}
                    </td>
                    <td>
                        @if ($order->user != null)
                        {{ $order->user->name }}
                        @else
							@php 
                            
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
									echo $shipping->address .'-'.$city_name .'-'. $state_name;
								}else{
									echo 'Guest';
								}
							@endphp
                      
                    </td>
                    <td>{{ $state_name }}</td>
                    <td>{{ $city_name }}</td>

                    <td style="text-align:right;">
                        {{ $net_total }}
                    </td>

                    <td style="text-align:right;">
                    {{ $subtotal }} 
                    </td>
                    <td style="text-align:right;">500</td>
                    <td></td> 
                    
                   
                </tr>
            @endforeach
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