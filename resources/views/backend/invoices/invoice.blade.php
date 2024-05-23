<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{  translate('INVOICE') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
	<style media="all">
        @page {
			margin: 0;
			padding:0;
		}
		body{
			font-size: 0.875rem;
            font-family: '<?php echo  $font_family ?>';
            font-weight: normal;
            direction: <?php echo  $direction ?>;
            text-align: <?php echo  $text_align ?>;
			padding:0;
			margin:0; 
		}
		.gry-color *,
		.gry-color{
			color:#000;
		}

	.badge {
    display: -webkit-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    height: 20px;
    width: 25px;
    font-size: 1.5rem;
    font-weight: 500;
    line-height: unset;
}

	.bg-dark {
       background-color: #343a40!important;
      }

	  .text-white {
      color: #fff!important;
     }

	 .badge-inline {
    width: auto;
   }

		table{
			width: 100%;
		}
		table th{
			font-weight: normal;
		}
		table.padding th{
			padding: .25rem .7rem;
		}
		table.padding td{
			padding: .25rem .7rem;
		}
		table.sm-padding td{
			padding: .1rem .7rem;
		}
		.border-bottom td,
		.border-bottom th{
			border-bottom:1px solid #eceff4;
		}
		.text-left{
			text-align:<?php echo  $text_align ?>;
		}
		.text-right{
			text-align:<?php echo  $not_text_align ?>;
		}
	</style>
</head>
<body>
	<div>

		@php
			$logo = get_setting('header_logo');
		@endphp
 		@php
            $payment_status = $order->payment_status;
            @endphp
		<div style="background: #eceff4;padding: 1rem;">
			<table>
				<tr>
					<td>
						@if($logo != null)
							<img src="{{ uploaded_asset($logo) }}" height="30" style="display:inline-block;">
						@else
							<img src="{{ static_asset('assets/img/logo.png') }}" height="30" style="display:inline-block;">
						@endif
					</td>

					<td style="font-size: 1.5rem;" class="text-right strong">
					@if((json_decode($order->shipping_address)->state_id) == '348')
				   <span  class="badge bg-dark text-white badge-inline">{{translate('ISD')}}</span>
				   @else
				   <span class="badge bg-dark text-white badge-inline">{{translate('OSD')}}</span>
				   @endif
					{{  translate('INVOICE') }}
				</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1rem;" class="strong">{{ get_setting('site_name') }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{ get_setting('contact_address') }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Email') }}: {{ get_setting('contact_email') }}</td>
					<td class="text-right small"><span class="gry-color small">{{  translate('Order ID') }}:</span> <span class="strong">{{ $order->code }}</span></td>
				</tr>
				<tr>
					@php
					$seller = \App\Shop::where('user_id', $order->seller_id)->first();
					@endphp
					@if(!empty($order->seller_id) && !empty($seller))
					<td class="gry-color small">{{  translate('Seller') }}: {{ $seller->name }}</td>
					@else
					<td class="gry-color small">{{  translate('Seller') }}: In House</td>
					@endif
					<td class="text-right small"><span class="gry-color small">{{  translate('Order Date') }}:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span></td>
				</tr>
			</table>

		</div>

		<div style="padding: 1rem;padding-bottom: 0"><div style="width:65%;float:left;">
            <table>
				@php
					$shipping_address = json_decode($order->shipping_address);
					$city = !empty($shipping_address->city_id) ? \App\City::where('id',$shipping_address->city_id)->first()->name : $shipping_address->city;
				@endphp
				<tr><td class="strong small gry-color">{{ translate('Bill to') }}:</td></tr>
				<tr><td class="strong">{{ $shipping_address->name }}</td></tr>
				<tr><td class="gry-color small">{{ $shipping_address->address }}, {{ get_city_name(json_decode($order->shipping_address)->city_id)[0] }},{{ get_state_name(json_decode($order->shipping_address)->state_id)[0]}}</td></tr>
                <tr><td class="gry-color small">{{ translate('Mobile') }}: {{ $shipping_address->phone }}</td></tr>
				<tr><td class="gry-color small">{{ translate('Email') }}: {{ $shipping_address->email }}</td></tr>
			</table>
                </div>
                @if($payment_status=='paid')
                <div style="width:15%;border:2px solid green;padding:10px;float:right;text-align:center;text-transform:uppercase">
                	<span><b>{{translate('PAID')}} </b></span>
                </div>
                @else
                <div style="width:15%;border:2px solid red;padding:10px;float:right;">
                	<span>{{translate('Payment method')}} : <br><b>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</b></span>
                </div>
                @endif
		</div>

	    <div style="padding: 1rem;">
			<table class="padding text-left small border-bottom">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
	                    <th width="35%" class="text-left">{{ translate('Product Name') }}</th>
	                    <th width="35%" class="text-left">{{ translate('Product Image') }}</th>
						<th width="15%" class="text-left">{{ translate('Delivery Type') }}</th>
	                    <th width="10%" class="text-left">{{ translate('Qty') }}</th>
	                    <th width="15%" class="text-left">{{ translate('Unit Price') }}</th>
	                    <!-- <th width="10%" class="text-left">{{ translate('Discount') }}</th> -->
	                    <th width="15%" class="text-right">{{ translate('Total') }}</th>
	                </tr>
				</thead>
				<tbody class="strong">
	                @foreach ($order->orderDetails as $key => $orderDetail)
		                @if ($orderDetail->product != null)
							<tr class="">
								<td>{{ $orderDetail->product->name }} @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif</td>
								<td>
                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                    <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset( getVarianImage($orderDetail['product_id'],$orderDetail->variation))}}"></a>
                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                    <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset( getVarianImage($orderDetail['product_id'],$orderDetail->variation))}}"></a>
                                @else
                                    <strong>{{ translate('N/A') }}</strong>
                                @endif
                            </td>
								<td>
									@if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
										{{ translate('Home Delivery') }}
									@elseif ($orderDetail->shipping_type == 'pickup_point')
										@if ($orderDetail->pickup_point != null)
											{{ $orderDetail->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
										@endif
									@endif
								</td>
								<td class="">{{ $orderDetail->quantity }}</td>
								<td class="currency">{{ single_price(($orderDetail->price/$orderDetail->quantity)+$orderDetail->discount/$orderDetail->quantity) }}</td>
								<!-- <td class="currency">{{ single_price($orderDetail->discount) }}</td> -->
			                    <td class="text-right currency">{{ single_price($orderDetail->price+$orderDetail->discount) }}</td>
							</tr>
		                @endif
					@endforeach
	            </tbody>
			</table>
		</div>

	    <div style="padding:0 1.5rem;">
	        <table class="text-right sm-padding small strong">
	        	<thead>
	        		<tr>
	        			<th width="60%"></th>
	        			<th width="40%"></th>
	        		</tr>
	        	</thead>
		        <tbody>
			        <tr>
			            <td>
			            </td>
			            <td>
					        <table class="text-right sm-padding small strong">
						        <tbody>
							        <tr>
							            <th class="gry-color text-left">{{ translate('Sub Total') }}</th>
							            <td class="currency">{{ single_price($order->orderDetails->sum('price')+$order->orderDetails->sum('discount')) }}</td>
							        </tr>
							        <tr>
							            <th class="gry-color text-left">{{ translate('Shipping Cost') }}</th>
							            <td class="currency">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</td>
							        </tr>
							        <tr class="border-bottom">
							            <th class="gry-color text-left">{{ translate('Total Discount') }}</th>
							            <td class="currency">{{ single_price($order->orderDetails->sum('discount')) }}</td>
							        </tr>
				                    <tr class="border-bottom">
							            <th class="gry-color text-left">{{ translate('Coupon Discount') }}</th>
							            <td class="currency">{{ single_price($order->coupon_discount) }}</td>
							        </tr>
							        <tr>
							            <th class="text-left strong">{{ translate('Grand Total') }}</th>
							            <td class="currency">{{ single_price($order->grand_total) }}</td>
							        </tr>
						        </tbody>
						    </table>
			            </td>
			        </tr>
		        </tbody>
		    </table>
	    </div>

	</div>
	<footer>
	    <div style="padding:2.5rem;">
	<table style="width:100%;text-align:left;">
		        <tbody>
				@if(!empty($order->special_instruction))
						<tr><td><span style="font-size:15px;">Note: {{ ($order->special_instruction) }}</span></td>
			        </tr>
					@endif
					</tbody>
		    </table>
		    	</div>
        </footer>
</body>
</html>
