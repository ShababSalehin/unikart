@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Top sales report')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <div class="card-body">
                <form id="culexpo" action="{{ route('topSalesReport.index') }}" method="GET">
                    <div class="form-group row">


                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker" name="shop_or_product">

                                <option @php if($shop_or_product=="Shop" ) echo 'selected' ; @endphp value="Shop">Shop</option>
                                <option @php if($shop_or_product=="Product" ) echo 'selected' ; @endphp value="Product">Product</option>

                            </select>
                        </div>


                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Top')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker" name="top">

                                <option @php if($top==3) echo 'selected' ; @endphp value="3">3</option>
                                <option @php if($top==5) echo 'selected' ; @endphp value="5">5</option>
                                <option @php if($top==10) echo 'selected' ; @endphp value="10">10</option>
                                <option @php if($top==15) echo 'selected' ; @endphp value="15">15</option>
                                <option @php if($top==20) echo 'selected' ; @endphp value="20">20</option>

                            </select>
                        </div>


                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker" name="order_by">

                                <option @php if($order_by=="quantity" ) echo 'selected' ; @endphp value="quantity">Quantity</option>
                                <option @php if($order_by=="price" ) echo 'selected' ; @endphp value="price">Profit</option>

                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Area')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker" name="city_id" data-live-search="true">
                                <option value=''>All</option>
                                @foreach (\App\City::all() as $key => $city)
                                <option @php if($city_id==$city->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $city->id }}">{{ $city->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Date Range :</label>
                            <div class="col-md-12">
                                <input type="date" name="start_date" class="form-control" value="{{$start_date}}">
                            </div>
                            <div class="col-md-12">
                                <input type="date" name="end_date" class="form-control" value="{{$end_date}}">
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <br>
                            <button class="btn btn-primary" onclick="submitForm('{{ route('topSalesReport.index') }}')">{{ translate('Filter') }}</button>
                            <button class="btn btn-primary" onclick="submitForm('{{ route('top_sale_download') }}')">{{ translate('Excel') }}</button>
                            <button class="btn btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>
                        </div>
                    </div>
                </form>

                <div class="printArea">
                    <style>
                        th {
                            text-align: center;
                        }
                    </style>
                    <?php if ($order_by == "quantity") { ?>
                        <h3 style="text-align:center;">{{translate('Qty wise Top '.$shop_or_product.' sales report')}}</h3>
                    <?php } else { ?>
                        <h3 style="text-align:center;">{{translate('Profit wise Top '.$shop_or_product.' sales report')}}</h3>
                    <?php } ?>

                    <table class="table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%">SL</th>
                                <?php if ($shop_or_product == "Shop") { ?>
                                    <th style="width:30%">{{ translate('Shop Name') }}</th>
                                    <th style="width:15%">{{ translate('Contact Person') }}</th>
                                    <th style="width:15%">{{ translate('Contact Number') }}</th>
                                <?php } else { ?>
                                    <th style="width:30%">{{ translate('Product Name') }}</th>
                                <?php } ?>
                                <?php if ($order_by == "quantity") { ?>
                                    <th style="width:10%">{{ translate('Qty') }}</th>
                                    <th style="width:10%">{{ translate('View Details') }}</th>
                                <?php } else { ?>
                                    <th style="width:10%">{{ translate('Profit') }}</th>
                                    <th style="width:10%">{{ translate('View Details') }}</th>
                                <?php } ?>

                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0;$qty = 1;$total_qty=0; @endphp
                            @foreach ($products as $key => $product)
                            @php
                            
                            $total_qty = $total_qty+($product->quantity);
                            //$total = $total+($product->price);
							$net_profit=$product->total_unikart_earning-($product->total_shipping_cost);
                           // $total = $total+$net_profit;
                          
                            @endphp

                            <?php if (!empty($product->quantity)) {
                                $qty = $product->quantity;
                            } else {
                                $qty = 1;
                            }
                            ?>
                            <tr>
                                <td>{{ ($key+1)}}</td>
                                <?php if ($shop_or_product == "Shop") { ?>
                                    <td>{{ $product->getTranslation('shop_name') }}</td>
                                    <td style="text-align:center;">{{ $product->getTranslation('contact_person') }}</td>
                                    <td style="text-align:center;">{{ $product->getTranslation('contact_number') }}</td>
                                <?php } else { ?>
                                    <td>{{ $product->getTranslation('product_name') }}</td>
                                <?php } ?>
                                <?php if ($order_by == "quantity") { ?>
                                    <td style="text-align:right;">{{ $product->getTranslation('quantity') }}</td>
                                    <td style="text-align: center;"><a class="btn btn-soft-primary btn-icon btn-circle btn-sm" target="blank" href="{{route('topsaledetails.show',['shopuserid'=>$product->user_id,'profit'=>$order_by])}}"
                                     target="blank" title="{{ translate('TopSale Details') }}">
                                <i class="las la-eye"></i>
                            </a></td>
                                <?php } else { ?>
                                    <td style="text-align:right;">{{ $net_profit }}</td>
                                    <td style="text-align: center;"><a class="btn btn-soft-primary btn-icon btn-circle btn-sm" target="blank" href="{{route('topsaledetails.show',['shopuserid'=>$product->user_id,'profit'=>$order_by])}}"
                                    <i class="las la-eye"></i>
                                    </a></td>
                                <?php } ?>

                            </tr>
                            @endforeach
                            <tr>
                                <td style="text-align:right;" colspan="4"><b>Total</b></td>
                                <?php if ($order_by == "quantity") { ?>
                                    <td style="text-align:right;"><b>{{$total_qty}}</b></td>
                                <?php } else { ?>
                                    <td style="text-align:right;"><b>{{single_price($total)}}</b></td>
                                <?php } ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function submitForm(url){
   $('#culexpo').attr('action',url);
   $('#culexpo').submit();
}
</script>

@endsection