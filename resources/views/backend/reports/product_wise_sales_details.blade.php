@extends('backend.layouts.app')

@section('content')


<div class="card">

<form  id="culexpo" class="" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ translate('Product Wise Sales Details') }}</h5>
            </div>
           
                <div class="col-md-3">
                    <label class="col-form-label">{{translate('Sort by Product')}} :</label>
                    <select id="demo-ease" class="aiz-selectpicker select2" name="product_id" data-live-search="true">
                        <option value=''>All</option>
                        @foreach (DB::table('products')->select('id','name')->get(); as $key => $prod)
                        <option @php if($pro_sort_by == $prod->id)
                            echo 'selected';
                            @endphp
                            value="{{ $prod->id }}">{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <input type="date" class="form-control " id="start_date" name="start_date" @isset($start_date) value="{{ $start_date }}" @endisset placeholder="{{ translate('From')}}">
                </div><br>
                <div class="form-group mb-0">
                    <input type="date" class="form-control" id="end_date" name="end_date" @isset($end_date) value="{{ $end_date }}" @endisset placeholder="{{ translate('To ')}}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                <button class="btn btn-sm btn-primary" onclick="submitForm ('{{ route('product_wise_sales_details') }}')">{{ translate('Filter') }}</button>
                    <button class="btn btn-sm btn-info" onclick="submitForm('{{ route('product_wise_sales_details_download') }}')">Excel</button>
                    <button class="btn btn-sm btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>                    
                </div>
            </div>
        </div>
    </form>

    <div class="card-body printArea">
        <style>
            th {
                text-align: center;
            }
        </style>
        <h3 style="text-align:center;">{{translate('Product Wise Sales Details')}}</h3>
        <table class="table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SL</th>
        			<th>{{ translate('Order Id') }}</th>
                    <th>Order Code</th>
                    <th>{{ translate('Order Date') }}</th>
                    <th>{{ translate('Product Name') }}</th>
                    <th>{{ translate('Customer Name') }}</th>
                    <th>{{ translate('Customer Phone') }}</th>
                    <th>{{ translate('Qty') }}</th>
                    <th>{{ translate('Seller') }}</th>
                    <th>{{ translate('Invoice') }}</th>
                    <th>{{ translate('SSL') }}</th>
                    <th>{{ translate('App Coupon') }}</th>
                    <th>{{ translate('Unikart Coupon') }}</th>
                    <th>{{ translate('Seller Coupon') }}</th>
                </tr>
            </thead>
            <tbody>
                @php      
                $totalamount = 0;
                $totalqty = 0;
                $total_invoice=0;
                $total_ssl=0;
                $total_app_coupon=0;
                $total_unikart_coupon=0;
                $total_seller_coupon=0;
                $i=0;
                @endphp
                @foreach ($orders as $key => $order)
                    @php
                        $total_invoice=$total_invoice+$order->grand_total;
                        $app_coupon=$order->orderDetails->sum('app_discount');
                        $total_app_coupon=$total_app_coupon+$order->orderDetails->sum('app_discount');
                        $total_unikart_coupon=$total_unikart_coupon+$order->unikart_coupon_discount;
                        $total_seller_coupon=$total_seller_coupon+$order->seller_coupon_discount;
                        $m=0;

                        //$orderDetails=\App\OrderDetail::
                        //    leftjoin('products','products.id','=','order_details.product_id')
                        //    ->select('products.name as productname','order_details.quantity','order_details.price')->get();
                    
                    @endphp
                    @foreach($order->orderDetails as $key1=>$value)
                    @php
                        $m++;
                        $i++;
                        $totalqty=$totalqty+$value->quantity; 
                    @endphp
                <tr>
                    <td>
                        {{ ($i) }}
                    </td>
                    <td>{{$order->id}}</td>
                    <td>{{$order->code}}</td>
                    <td>
                        {{ $order->created_at }}
                    </td>
                    
                    <td>
                        @php 
                            $product = json_decode($value->product);
                            if(!empty($product)){
                                echo $product->name;
                            }else{
                                
                            }
                        @endphp
                        
                    </td>
                    <td>{{$order->username}}</td>
                    <td>{{$order->phone}}</td>
                    <td style="text-align:right;">
                        @php                    
                            echo $value->quantity;                        
                        @endphp
                    </td>
                    <td>{{$order->seller_name}}</td>
                    @if($m==1)
                            
                        <td style="text-align:right;">{{$order->grand_total}}</td>
                        @if($order->payment_type=="sslcommerz")
                            @php
                            $total_ssl=$total_ssl+$order->grand_total; 
                            @endphp
                            <td style="text-align:right;">{{$order->grand_total}}</td>
                        @else
                            <td style="text-align:right;"></td>     
                        @endif
                        <td style="text-align:right;">{{$app_coupon}}</td>
                        <td style="text-align:right;">{{$order->unikart_coupon_discount}}</td>
                        <td style="text-align:right;">{{$order->seller_coupon_discount}}</td>
                    @else    
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>    
                    @endif
                  
                </tr>
                @endforeach
             
                @endforeach
             
            </tbody>
            <tr>
                    <td style="text-align:right;" colspan="7"><b>Total</b></td>

                    <td style="text-align: right;">
                    @php                        
                        echo $totalqty;                        
                    @endphp
                      
                    </td>
                    
                    <td></td>
                    <td style="text-align: right;">{{$total_invoice}}</td>
                    <td style="text-align: right;">{{$total_ssl}}</td>
                    <td style="text-align: right;">{{$total_app_coupon}}</td>
                    <td style="text-align: right;">{{$total_unikart_coupon}}</td>
                    <td style="text-align: right;">{{$total_seller_coupon}}</td>
                </tr>
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