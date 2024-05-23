@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{translate('Seller Based Selling Report')}}</h1>
	</div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
            <form action="{{ route('income_report.details', $seller_id) }}" method="GET">
    <div class="card-header row gutters-5">
        <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ translate('Income Details Report') }}</h5>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-0">
                <input type="text" class="form-control form-control-sm aiz-date-range" id="search" name="date_range"@isset($date_range) value="{{ $date_range }}" @endisset placeholder="{{ translate('Daterange') }}">
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-md btn-primary" type="submit">
                {{ translate('Filter') }}
            </button>
        </div>
    </div>
</form>
           <div class="printArea">
                    <style>
                        th {
                            text-align: center;
                        }
                    </style>
                <table class="table table-bordered aiz-table mb-0 data-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Order Id') }}</th>
                            <th>{{ translate('Total Sale') }}</th>
                            <th>{{ translate('Refund') }}</th>
                            <th>{{ translate('Net Sale') }}</th>
                            <th>{{ translate('Commission') }}</th>
                            <th>{{ translate('Shipping Fee By Customer') }}</th>
                            <th>{{ translate('Shipping Fee By Seller') }}</th>
                            <th>{{ translate('Shipping Fee By Unicart') }}</th>
                            <th>{{ translate('AppDiscount') }}</th>
                            <th>{{ translate('Unikart Coupon') }}</th>
                            <th>{{ translate('Seller Coupon') }}</th>
                            <th>{{ translate('Claims') }}</th>
                            <th>{{ translate('Seller Income') }}</th>
                            <th>{{ translate('Unicart Income') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php

                        if($date_range){
                                $date_range1 = explode(" / ", $date_range);
                        }

                        $gtotalsale = 0;
                        $gtotal_netsale=0;
                        $gtotal_commission=0;
                        $gtotal_feeshipping_by_customer=0;
                        $gtotal_feeshipping_by_seller=0;
                        $gtotal_feeshipping_by_unicart=0;
                        $gtotal_coupon=0;
                        $gtotal_seller_coupon=0;
                        $gtotal_refunds=0;
                        $gtotal_claims=0;
                        $gtotal_seller_income=0;
                        $gtotal_unicart_income=0;
                        $gtotal_due = 0;
                        $gearning = 0;
                        $gnatincome = 0;
                        $total_app_discount = 0;
                        @endphp

                        @foreach ($sellers as $key => $seller)
                            @if($seller->user != null)
                            @php
                            $total_sale=0;
                            $net_sale=0;
                            $commission=0;
                            $shipping_fee_by_customer=0;
                            $shipping_fee_by_seller=0;
                            $claims=0;
                            $refund_amount=0;
                            $seller_income=0;
                            $unicart_income=0;
                            $app_discount = 0;


                            if($date_range){
                                    $total_sale_info = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered'])
                                    ->where('created_at', '>=', $date_range1[0])
                                    ->where('created_at', '<=', $date_range1[1])
                                    ->select('refund_status',DB::raw('sum(order_details.price) AS total_price'),
                                    DB::raw('sum(quantity) AS total_quantity'),
                                    DB::raw('sum(due_to_seller) AS total_due_to_seller'),
                                    DB::raw('sum(unikart_earning) AS total_unikart_earning'),
                                    DB::raw('sum(shipping_cost) AS total_shipping_cost'),
                                    DB::raw('sum(app_discount) AS total_app_discount')
                                   
                                    )
                                ->get();


                                $total_return_sale_info = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered'])
                                    ->where('created_at', '>=', $date_range1[0])
                                    ->where('created_at', '<=', $date_range1[1])
                                    ->where('refund_status', '=', 1)
                                    ->select('refund_status',DB::raw('sum(order_details.price) AS total_price'),
                                    DB::raw('sum(quantity) AS total_quantity'),
                                    DB::raw('sum(due_to_seller) AS total_due_to_seller'),
                                    DB::raw('sum(unikart_earning) AS total_unikart_earning'),
                                    DB::raw('sum(shipping_cost) AS total_shipping_cost'),
                                    DB::raw('sum(app_discount) AS total_app_discount')
                                   
                                    )
                                ->get();
                            
                           
                            }else{
                                $total_sale_info = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered'])    
                                ->select('refund_status',DB::raw('sum(order_details.price) AS total_price'),
                                    DB::raw('sum(quantity) AS total_quantity'),
                                    DB::raw('sum(due_to_seller) AS total_due_to_seller'),
                                    DB::raw('sum(unikart_earning) AS total_unikart_earning'),
                                    DB::raw('sum(shipping_cost) AS total_shipping_cost'),
                                    DB::raw('sum(app_discount) AS total_app_discount')
                                   
                                    )
                                ->get();


                                $total_return_sale_info = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered'])
                                ->where('refund_status', '=', 1)
                                ->select('refund_status',DB::raw('sum(order_details.price) AS total_price'),
                                    DB::raw('sum(quantity) AS total_quantity'),
                                    DB::raw('sum(due_to_seller) AS total_due_to_seller'),
                                    DB::raw('sum(unikart_earning) AS total_unikart_earning'),
                                    DB::raw('sum(shipping_cost) AS total_shipping_cost'),
                                    DB::raw('sum(app_discount) AS total_app_discount')
                                   
                                    )
                                ->get();
                            
                           
                            
                           
                            }

                            $app_discount = $total_sale_info[0]['total_app_discount'];
                            $total_app_discount += $app_discount;
                            $total_sale=$total_sale_info[0]['total_price'];
                           
                            $gtotalsale=$gtotalsale+$total_sale;

                            $net_sale=$total_sale_info[0]['total_price']-$total_return_sale_info[0]['total_price'];
                            $gtotal_netsale=$gtotal_netsale+$net_sale;

                            $commission=$total_sale_info[0]['total_unikart_earning']-$total_return_sale_info[0]['total_unikart_earning'];   
                            $gtotal_commission=$gtotal_commission+$commission;

                            $shipping_fee_by_customer=$total_sale_info[0]['total_shipping_cost'];
                            $gtotal_feeshipping_by_customer=$gtotal_feeshipping_by_customer+$shipping_fee_by_customer;

                            $shipping_fee_by_seller=0;
                            $gtotal_feeshipping_by_seller=$gtotal_feeshipping_by_seller+$shipping_fee_by_seller;

                            if($shipping_fee_by_customer==0){
                                $gtotal_feeshipping_by_unicart=$gtotal_feeshipping_by_unicart+$seller->admin_shipping_cost;
                            }

                           // $gtotal_coupon=$gtotal_coupon+$seller->coupon_discount;
                            $gtotal_coupon=$gtotal_coupon+$seller->unikart_coupon_discount;
                            $gtotal_seller_coupon=$gtotal_seller_coupon+$seller->seller_coupon_discount;

                          //  if($total_sale_info[0]['refund_status']==1){
                         //       $refund_amount=$total_sale_info[0]['total_price'];
                          //  }

                            $refund_amount=$total_return_sale_info[0]['total_price'];

                            $gtotal_refunds=$gtotal_refunds+$refund_amount;

                            $gtoral_claims=$gtotal_claims+$claims;

                        //    if($total_sale_info[0]['refund_status']==1){
                        //        $seller_income=0-$shipping_fee_by_seller;
                        //    }else{
                        //        $seller_income=$total_sale-($commission+$shipping_fee_by_seller+$claims+$refund_amount);
                        //    }


                            $seller_income=$net_sale-($commission+$shipping_fee_by_seller+$seller->seller_coupon_discount);
                            
                            $gtotal_seller_income=$gtotal_seller_income+$seller_income;
                           
                            

                        //    if($total_sale_info[0]['refund_status']==1){
                        //        if($shipping_fee_by_customer==0){
                        //            $unicart_income=0-$seller->admin_shipping_cost;
                        //        }
                        //    }else{
                        //        if($shipping_fee_by_customer==0){
                        //            $unicart_income=($commission+$claims)-($seller->admin_shipping_cost+$seller->coupon_discount);
                        //        }else{
                        //            $unicart_income=($commission+$claims)-($seller->coupon_discount); 
                        //        }
                        //    }

                        

                        //    if($shipping_fee_by_customer==0){
                        //        $unicart_income=($commission+$claims)-($seller->admin_shipping_cost+$seller->coupon_discount);
                        //    }else{
                        //        $unicart_income=($commission+$claims)-($seller->coupon_discount); 
                        //    }

                            if($shipping_fee_by_customer==0){
                                $unicart_income=($commission+$claims)-($seller->admin_shipping_cost+$seller->unikart_coupon_discount)-($app_discount);
                            }else{
                                $unicart_income=($commission+$claims)-($seller->unikart_coupon_discount) - ($app_discount); 
                            }
                            
                            $gtotal_unicart_income=$gtotal_unicart_income+$unicart_income;
                            
                       
                        
                            @endphp
                                <tr>
                                    <td><a target="_blank" href="{{ route('income_order_report.details', $seller->id) }}">{{ $seller->code }}</a></td>
                                    <td>
                                        {{ $total_sale}}{{get_only_currency_symbol()}}
                                    </td>

                                    <td>
                                        {{ $refund_amount}}{{get_only_currency_symbol()}}
                                    </td>

                                    <td>
                                        {{ $net_sale}}{{get_only_currency_symbol()}}
                                    </td>

                                    <td>
                                        {{ $commission}}{{get_only_currency_symbol()}}
                                    </td>
                                    <td>
                                        {{ $shipping_fee_by_customer}}{{get_only_currency_symbol()}}
                                    </td>
                                    <td>
                                        {{ $shipping_fee_by_seller}}{{get_only_currency_symbol()}}
                                    </td>
                                  <?php if($shipping_fee_by_customer>0){ ?>  
                                        <td>
                                        {{ single_price(0)}} 
                                        </td>
                                  <?php }else{ ?>  
                                    <td>
                                        {{ $seller->admin_shipping_cost}}{{get_only_currency_symbol()}}
                                    </td>
                                  <?php } ?>
                                  
                                  <td>
                                        {{ $app_discount }}
                                    </td>

                                <td>
                                        {{ $seller->unikart_coupon_discount}}{{get_only_currency_symbol()}}
                                </td>
                                    
                                <td>
                                        {{ $seller->seller_coupon_discount}}{{get_only_currency_symbol()}}
                                </td>    

                                    
                                    <td>
                                        {{ $claims }}{{get_only_currency_symbol()}}
                                    </td>
                                  
                                    <td>{{ $seller_income}}{{get_only_currency_symbol()}}</td>
                                    <td>{{ $unicart_income }}{{get_only_currency_symbol()}}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border">
                            <th>Total</th>
                            <th>{{$gtotalsale}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_refunds}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_netsale}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_commission}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_feeshipping_by_customer}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_feeshipping_by_seller}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_feeshipping_by_unicart}}{{get_only_currency_symbol()}}</th>
                            <th>{{$total_app_discount}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_coupon}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_seller_coupon}}{{get_only_currency_symbol()}}</th>
                            
                            <th>{{$gtotal_claims}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_seller_income}}{{get_only_currency_symbol()}}</th>
                            <th>{{$gtotal_unicart_income}}{{get_only_currency_symbol()}}</th>
                        </tr>
                    </tfoot>
                </table>
                </div>
                <div class="aiz-pagination mt-4">
                    {{ $sellers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
