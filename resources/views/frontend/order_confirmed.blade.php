@extends('frontend.layouts.app')

@section('content')
    <section class="pt-5 mb-4">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    <div class="row aiz-steps arrow-divider">
                        <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-shopping-cart"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('1. My Cart')}}</h3>
                            </div>
                        </div>
                        <!-- <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-map"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Shipping info')}}</h3>
                            </div>
                        </div> -->
                        <!-- <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-truck"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('3. Delivery info')}}</h3>
                            </div>
                        </div> -->
                        <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-credit-card"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Payment')}}</h3>
                            </div>
                        </div>
                        <div class="col active">
                            <div class="text-center text-primary">
                                <i class="la-3x mb-2 las la-check-circle"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('3. Confirmation')}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-4">
        <div class="container text-left">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    @php
                        $first_order = $combined_order->orders->first()
                    @endphp
                    <div class="text-center py-4 mb-4">
                        <i class="la la-check-circle la-3x text-success mb-3"></i>
                        <h1 class="h3 mb-3 fw-600">{{ translate('Thank You for Your Order!')}}</h1>
                        <p class="opacity-70 font-italic">{{  translate('A copy or your order summary has been sent to') }} {{ json_decode($first_order->shipping_address)->email }}</p>
                    </div>
                    <div class="mb-4 bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-600 mb-3 fs-17 pb-2">{{ translate('Order Summary')}}</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Order date')}}:</td>
                                        <td>{{ date('d-m-Y H:i A', $first_order->date) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Name')}}:</td>
                                        <td>{{ json_decode($first_order->shipping_address)->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Email')}}:</td>
                                        <td>{{ json_decode($first_order->shipping_address)->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Shipping address')}}:</td>
                                        <td>{{ json_decode($first_order->shipping_address)->address }}, {{ get_city_name(json_decode($first_order->shipping_address)->city_id)[0] }}, {{ get_country_name(json_decode($first_order->shipping_address)->country_id)[0] }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Order status')}}:</td>
                                        <td>{{ translate(ucfirst(str_replace('_', ' ', $first_order->delivery_status))) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Total order amount')}}:</td>
                                        <td>{{ single_price($combined_order->grand_total) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Shipping')}}:</td>
                                        <td>{{ translate('Flat shipping rate')}}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Payment method')}}:</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $first_order->payment_type)) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    @php
                            $subtotal = 0;
                            $discount = 0;
                            $cdiscount = 0;
                            $shipping = 0;
                    @endphp
                    @foreach ($combined_order->orders as $key=>$order)
                        @php
                           // $subtotal += $order->orderDetails->sum('price')+$order->orderDetails->sum('discount');
                            $discount += $order->orderDetails->sum('discount');
                            $shipping += $order->orderDetails->sum('shipping_cost');
                            $cdiscount += $order->coupon_discount;
                            $o_total=0;
                        @endphp
                        <div class="card shadow-sm border-0 rounded">
                            <div class="card-body">
                                <div class="py-4 mb-4">
                                    @if(count($combined_order->orders)>1)
                                    <h2 class="h5"><span class="fw-700 text-primary">
                                        Package : {{($key+1)}} <br>
                                        @php
                                            $seller = \App\Shop::where('user_id', $order->seller_id)->first();
                                        @endphp
                                        @if(!empty($order->seller_id) && !empty($seller))
                                            Seller : {{ $seller->name }}<br>
                                        @else
                                            Seller : In House<br>
                                        @endif
                                        Order Code : {{ $order->code }}</span></h2>
                                    @else 
                                    <h2 class="h5">Order Code :<span class="fw-700 text-primary">{{ $order->code }}</span></h2>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="fw-600 mb-3 fs-17 pb-2">{{ translate('Order Details')}}</h5>
                                    <div>
                                        <table class="table table-responsive-md">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th width="30%">{{ translate('Product')}}</th>
                                                    <th>{{ translate('Variation')}}</th>
                                                    <th>{{ translate('Quantity')}}</th>
                                                    <th>{{ translate('Delivery Type')}}</th>
                                                    <th class="text-right">{{ translate('Price')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->orderDetails as $key => $orderDetail)
                                                    @php 
                                                    $o_total+=($orderDetail->quantity*$orderDetail->product_unit_price);
                                                    $subtotal +=($orderDetail->quantity*$orderDetail->product_unit_price);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>
                                                            @if ($orderDetail->product != null)
                                                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-reset">
                                                                    {{ $orderDetail->product->getTranslation('name') }}
                                                                    @php
                                                                        if($orderDetail->combo_id != null) {
                                                                            $combo = \App\ComboProduct::findOrFail($orderDetail->combo_id);

                                                                            echo '('.$combo->combo_title.')';
                                                                        }
                                                                    @endphp
                                                                </a>
                                                            @else
                                                                <strong>{{  translate('Product Unavailable') }}</strong>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $orderDetail->variation }}
                                                        </td>
                                                        <td>
                                                            {{ $orderDetail->quantity }}
                                                        </td>
                                                        <td>
                                                            @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                                                {{  translate('Home Delivery') }}
                                                            @elseif ($orderDetail->shipping_type == 'pickup_point')
                                                                @if ($orderDetail->pickup_point != null)
                                                                    {{ $orderDetail->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="text-right">{{ single_price($orderDetail->product_unit_price) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if(count($combined_order->orders)>1)
                                    <div class="row">
                                        <div class="col-xl-5 col-md-6 ml-auto mr-0">
                                            <table class="table ">
                                                <tbody>
                                                    <tr>
                                                        <th>{{ translate('Subtotal')}}</th>
                                                        <td class="text-right">
                                                            <span class="fw-600">{{ single_price($o_total) }}</span>
                                                        </td>
                                                    </tr>
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="row">
                        <div class="col-xl-5 col-md-6 ml-auto mr-0">
                            <table class="table ">
                                <tbody>
                                    <tr>
                                        <th>{{ translate('Subtotal')}}</th>
                                        <td class="text-right">
                                            <span class="fw-600">{{ single_price($subtotal) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('Shipping')}}</th>
                                        <td class="text-right">
                                            <span class="font-italic">{{ single_price($shipping) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('Discount')}}</th>
                                        <td class="text-right">
                                           <strong> <span class="font-italic">{{ single_price($discount) }}</span></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('Coupon Discount')}}</th>
                                        <td class="text-right">
                                            <span class="font-italic">{{ single_price($cdiscount) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-600">{{ translate('Total')}}</span></th>
                                        <td class="text-right">
                                            <strong><span>{{ single_price($subtotal+$shipping-$discount-$cdiscount) }}</span></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
@endsection
@section('script')
    <script type="text/javascript">
                                                                
       function sendNotificationAjax(){                                     
               $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('sendNotificationAjax')}}",
                type: 'get',
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                   console.log(response);
                }
            });
        }
		sendNotificationAjax();
      </script>
      @foreach ($combined_order->orders as $key=>$order)
      <script type = "text/javascript">
        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
        	event	: "purchase",
 	       ecommerce: {
                transaction_id: "{{$order->code}}",
                affiliation   : "",
                value     	: "{{$order->grand_total}}",
                tax       	: "0",
                shipping  	: "{{$order->admin_shipping_cost}}",
               
                coupon    	: "{{$order->coupon_code}}",
                items     	: [@foreach ($order->orderDetails as $orderProduct){
            	    item_name    : "{{$orderProduct->product->name}}",
                    item_id  	: "{{$orderProduct->id}}",
                    price    	: "{{$orderProduct->price}}",
                    item_brand   : "{{$orderProduct->product->brand->name ?? ""}}",
                	item_category: "{{$orderProduct->product->category->name ?? ""}}",
                    item_variant : "{{$orderProduct->variation ?? ""}}",
                    quantity 	: {{$orderProduct->quantity}}
                },@endforeach]
        	}
    	});
	</script>
@endforeach
      @endsection