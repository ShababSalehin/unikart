<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
	<style media="all">
		@font-face {
            font-family: 'Roboto';
            src: url("{{ static_asset('fonts/Roboto-Regular.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        *{
            margin: 0;
            padding: 0;
            line-height: 1.3;
            font-family: 'Roboto';
            color: #333542;
        }
		body{
			font-size: .875rem;
		}
		.gry-color *,
		.gry-color{
			color:#878f9c;
		}
		table{
			width: 100%;
		}
		table th{
			font-weight: normal;
		}
		table.padding th{
			padding: .5rem .7rem;
		}
		table.padding td{
			padding: .7rem;
		}
		table.sm-padding td{
			padding: .2rem .7rem;
		}
		.border-bottom td,
		.border-bottom th{
			border-bottom:1px solid #eceff4;
		}
		.text-left{
			text-align:left;
		}
		.text-right{
			text-align:right;
		}
		.small{
			font-size: .85rem;
		}
		.currency{

		}
	</style>
</head>
<body>
	<div>
		@php
			$logo = get_setting('header_logo');
		@endphp
		<div style="background: #eceff4;padding: 1.5rem;">
			<table>
				<tr>
					<td>
						@if($logo != null)
							<img loading="lazy"  src="{{ uploaded_asset($logo) }}" height="40" style="display:inline-block;">
						@else
							<img loading="lazy"  src="{{ static_asset('assets/img/logo.png') }}" height="40" style="display:inline-block;">
						@endif
					</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1.2rem;" class="strong">{{ get_setting('site_name') }}</td>
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

		<div style="padding: 1.5rem;padding-bottom: 0">
            <table>
				@php
					$shipping_address = json_decode($order->shipping_address);
				@endphp
				<tr><td class="strong small gry-color">{{ translate('Bill to') }}:</td></tr>
				<tr><td class="strong">{{ $shipping_address->name }}</td></tr>
				<tr><td class="gry-color small">{{ $shipping_address->address }}, {{ get_city_name(json_decode($order->shipping_address)->city_id)[0] }}, {{ get_country_name(json_decode($order->shipping_address)->country_id)[0] }}</td></tr>
				<tr><td class="gry-color small">{{ translate('Email') }}: {{ $shipping_address->email }}</td></tr>
				
			</table>
		</div>

         <!-- combined order start -->
         <div class="row">
            @php
            $combined_order = \App\CombinedOrder::findOrFail($order->combined_order_id);
            $subtotal = 0;
            $discount = 0;
            $cdiscount = 0;
            $shipping = 0;
            @endphp
            @foreach ($combined_order->orders as $key=>$order)
            @php
            $subtotal += $order->orderDetails->sum('price')+$order->orderDetails->sum('discount');
            $discount += $order->orderDetails->sum('discount');
            $shipping += $order->orderDetails->sum('shipping_cost');
            $cdiscount += $order->coupon_discount;
            @endphp
            <div style="padding: 1.5rem;">
                @if(count($combined_order->orders)>1)
                <h6 class="h5"><span class="fw-700 text-primary">
                        Package : {{($key+1)}} <br>
                        @php
                        $seller = \App\Shop::where('user_id', $order->seller_id)->first();
                        @endphp
                        @if(!empty($order->seller_id) && !empty($seller))
                        Seller : {{ $seller->name }}<br>
                        @else
                        Seller : In House<br>
                        @endif
                        Order Code : {{ $order->code }}</span></h6>
                @else
                <h6 class="h6">Order ID :<span class="fw-700 text-primary">{{ $order->code }}</span></h6>
                @endif
            </div>
        </div>

        <div class="row">
        <div style="padding: 1.5rem;">
            <table class="padding text-left small border-bottom">
                    <thead>
                    <tr class="gry-color" style="background: #eceff4;">
                            <th class="min-col">#</th>
                            <th width="15%">{{translate('Product Image')}}</th>
                            <th width="30%">{{translate('Product Name')}}</th>
                            <th width="10%">{{ translate('Review') }}</th>
                            <th width="15%">{{translate('Delivery Type')}}</th>
                            <th width="10%">{{translate('Qty')}}</th>
                            <th width="10%">{{translate('Price')}}</th>
                            <th width="10%">{{translate('Total')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderDetails as $key => $orderDetail)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>
                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                @else
                                <strong>{{ translate('N/A') }}</strong>
                                @endif
                            </td>
                            <td>
                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                <strong><a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                <small>{{ $orderDetail->variation }}</small>
                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                <strong><a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                @else
                                <strong>{{ translate('Product Unavailable') }}</strong>
                                @endif
                            </td>
                            @if(!empty($orderDetail->product_id) && $orderDetail->delivery_status == 'delivered')
							
							<td>
						<a class="btn btn-primary btn-md" href="{{route('make_review',$orderDetail->product_id)}}">Write A Review</a>
							</a>
							</td>
							@else
							<td>Not Allow</td>
                            @endif
                            <td>
                                @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                {{ translate('Home Delivery') }}
                                @elseif ($orderDetail->shipping_type == 'pickup_point')

                                @if ($orderDetail->pickup_point != null)
                                {{ $orderDetail->pickup_point->getTranslation('name') }} ({{ translate('Pickup Point') }})
                                @else
                                {{ translate('Pickup Point') }}
                                @endif
                                @endif
                            </td>
                            <td class="text-center">{{ $orderDetail->quantity }}</td>
                            <td class="text-center">{{ single_price(($orderDetail->price/$orderDetail->quantity)+$orderDetail->discount/$orderDetail->quantity) }}</td>
                            <td class="text-right">{{ single_price($orderDetail->price+$orderDetail->discount) }}</td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>
        @if(count($combined_order->orders)>1)
        <div class="row">
        <div style="padding:0 1.5rem;">
                <table style="width: 60%;margin-left:auto;" class="text-right sm-padding small strong">
                    <tbody>
                        <tr>
                            <th>{{ translate('Subtotal')}}</th>
                            <td class="currency">
                        {{ single_price($order->orderDetails->sum('price')+$order->orderDetails->sum('discount')) }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endforeach
        <div class="row">
        <div style="padding:0 1.5rem;">
        <table style="width: 60%;margin-left:auto;" class="text-right sm-padding small strong">
                    <tbody>
                        <tr>
                            <th>{{ translate('Sub Total')}}</th>
                            <td class="currency">
                            {{ single_price($subtotal) }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ translate('Shipping')}}</th>
                            <td class="currency">
                            {{ single_price($shipping) }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ translate('Discount')}}</th>
                            <td class="currency">
                            {{ single_price($discount) }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ translate('Coupon Discount')}}</th>
                            <td class="currency">
                                {{ single_price($cdiscount) }}
                            </td>
                        </tr>
                        <tr>
                        <th>{{ translate('Grand Total') }}</th>
                            <td  class="currency">{{ single_price($subtotal+$shipping-$discount-$cdiscount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
        <!-- combined order end -->
	</div>
</body>
</html>
