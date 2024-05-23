@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h1 class="h2 fs-16 mb-0">{{ translate('Combined Order Details') }}</h1>
    </div>
    <div class="card-body">
        <div class="row gutters-5">
            <div class="col text-center text-md-left">
            </div>
            @php
            $delivery_status = $order->delivery_status;
            $payment_status = $order->payment_status;
            @endphp

            <!--Assign Delivery Boy-->
            @if (\App\Addon::where('unique_identifier', 'delivery_boy')->first() != null &&
                \App\Addon::where('unique_identifier', 'delivery_boy')->first()->activated)
                <div class="col-md-3 ml-auto">
                    <label for="assign_deliver_boy">{{translate('Assign Deliver Boy')}}</label>
                    @if($delivery_status == 'pending' || $delivery_status == 'confirmed' || $delivery_status == 'picked_up')
                    <select class="form-control aiz-selectpicker" data-live-search="true" data-minimum-results-for-search="Infinity" id="assign_deliver_boy">
                        <option value="">{{translate('Select Delivery Boy')}}</option>
                        @foreach($delivery_boys as $delivery_boy)
                        <option value="{{ $delivery_boy->id }}" @if($order->assign_delivery_boy == $delivery_boy->id) selected @endif>
                            {{ $delivery_boy->name }}
                        </option>
                        @endforeach
                    </select>
                    @else
                        <input type="text" class="form-control" value="{{ optional($order->delivery_boy)->name }}" disabled>
                    @endif
                </div>
            @endif

           
        </div>
        <div class="row gutters-5">
            <div class="col text-center text-md-left">
                <address>
                    <strong class="text-main">{{ json_decode($order->shipping_address)->name }}</strong><br>
                    {{ json_decode($order->shipping_address)->email }}<br>
                    {{ json_decode($order->shipping_address)->phone }}<br>
                    {{ json_decode($order->shipping_address)->address }},{{ get_city_name(json_decode($order->shipping_address)->city_id)[0] }},{{ get_state_name(json_decode($order->shipping_address)->state_id)[0]}}<br>
                    {{ json_decode($order->shipping_address)->country }}
                </address>
                @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                <br>
                <strong class="text-main">{{ translate('Payment Information') }}</strong><br>
                {{ translate('Name') }}: {{ json_decode($order->manual_payment_data)->name }}, {{ translate('Amount') }}: {{ single_price(json_decode($order->manual_payment_data)->amount) }}, {{ translate('TRX ID') }}: {{ json_decode($order->manual_payment_data)->trx_id }}
                <br>
                <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" target="_blank"><img src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" alt="" height="100"></a>
                @endif
            </div>
            <div class="col-md-4 ml-auto">
                <table>
                    <tbody>
                        <!--
                        <tr>
                            <td class="text-main text-bold">{{translate('Order #')}}</td>
                            <td class="text-right text-info text-bold">	{{ $order->code }}</td>
                        </tr>
                        -->
                        <tr>
                            <td class="text-main text-bold">{{translate('Order Status')}}</td>
                            <td class="text-right">
                                @if($delivery_status == 'delivered')
                                <span class="badge badge-inline badge-success">{{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                @else
                                <span class="badge badge-inline badge-info">{{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{translate('Order Date')}}	</td>
                            <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">
                                {{translate('Total amount')}}
                            </td>
                            <td class="text-right">
                                {{ single_price($combined_order->grand_total) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{translate('Payment method')}}</td>
                            <td class="text-right">{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <hr class="new-section-sm bord-no">
        <div class="row">
        @php
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
        </div>    

        <div class="row">
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered aiz-table invoice-summary">
                    <thead>
                        <tr class="bg-trans-dark">
                            <th class="min-col">#</th>
                            <th width="10%">{{translate('Photo')}}</th>
                            <th class="text-uppercase">{{translate('Description')}}</th>
                            <th class="text-uppercase">{{translate('Delivery Type')}}</th>
                            <th class="min-col text-center text-uppercase">{{translate('Qty')}}</th>
                            <th class="min-col text-center text-uppercase">{{translate('Price')}}</th>
                            <th class="min-col text-right text-uppercase">{{translate('Total')}}</th>
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
                    <div class="col-xl-5 col-md-6 ml-auto mr-0">
                        <table class="table ">
                            <tbody>
                                <tr>
                                    <th>{{ translate('Subtotal')}}</th>
                                    <td class="text-right">
                                        <span class="fw-600">{{ single_price($order->orderDetails->sum('price')+$order->orderDetails->sum('discount')) }}</span>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach   
        <div class="row">
                        <div class="col-xl-5 col-md-6 ml-auto mr-0">
                            <table class="table ">
                                <tbody>
                                    <tr>
                                        <th>{{ translate('Grand Total')}}</th>
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
                                        <th><span class="fw-600">{{ translate('Net Total')}}</span></th>
                                        <td class="text-right">
                                            <strong><span>{{ single_price($subtotal+$shipping-$discount-$cdiscount) }}</span></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

    </div>
    <div class="text-right no-print">
                <a target="_blank" href="{{ route('combined.invoice.download', $order->id) }}" type="button" class="btn btn-icon text-info" style="font-size:30px;margin-right:20px;"><i class="las la-print"></i></a>
    </div>
</div>
@endsection

@section('modal')
  <div class="modal fade reject_refund_request" id="cancel_modal">
    	<div class="modal-dialog">
    		<div class="modal-content">
            <form class="form-horizontal member-block" action="{{ route('reason_cancel_order')}}" method="POST">
                @csrf
                <input type="hidden" name="code" id="code" value={{$order->code}}>
                <div class="modal-header">
                    <h5 class="modal-title h6">{{translate('Cancel Order !')}}</h5>
                    <button type="button" class="close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Cancel Reason')}}</label>
                        <div class="col-md-9">
                            <textarea type="text" name="cancel_reason" id="	cancel_reason" rows="5" class="form-control" placeholder="{{translate('Cancel Reason')}}" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-success">{{translate('Submit')}}</button>
                </div>
            </form>
      	</div>
    	</div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        // $('#assign_deliver_boy').on('change', function(){
        //     var order_id = {{ $order->id }};
        //     var delivery_boy = $('#assign_deliver_boy').val();
        //     $.post('{{ route('orders.delivery-boy-assign') }}', {
        //         _token          :'{{ @csrf_token() }}',
        //         order_id        :order_id,
        //         delivery_boy    :delivery_boy
        //     }, function(data){
        //         AIZ.plugins.notify('success', '{{ translate('Delivery boy has been assigned') }}');
        //     });
        // });

        $('#update_delivery_status').on('change', function(){

            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            
            if(status== 'cancelled'){
                $('#cancel_modal').modal("show");
                
            }
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token:'{{ @csrf_token() }}',
                order_id:order_id,
                status:status
            }, function(data){
                AIZ.plugins.notify('success', '{{ translate('Delivery status has been updated') }}');
            });
        });

        $('#update_payment_status').on('change', function(){
            var order_id = {{ $order->id }};
            var status = $('#update_payment_status').val();
            $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                AIZ.plugins.notify('success', '{{ translate('Payment status has been updated') }}');
            });
        });  
    </script>
@endsection
