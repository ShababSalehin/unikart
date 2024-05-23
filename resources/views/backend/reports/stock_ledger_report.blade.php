@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Product wise stock ledger report')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <!--card body-->
            <div class="card-body">
                <form action="{{ route('stock_ledger_report') }}" method="GET">
                    <div class="form-group row">
                        
                       
                        
                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Product')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker select2" name="product_id">
                                <option value=''>All</option>
                                @foreach (DB::table('products')->select('id','name')->get(); as $key => $prod)
                                <option @php if($pro_sort_by==$prod->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $prod->id }}">{{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                        <label class="col-form-label">{{translate('Sort by Shop')}} :</label>
                            <select id="demo-ease" class="from-control aiz-selectpicker" name="shop_id" data-live-search="true">
                                <option value=''>All</option>
                                @foreach (\App\Shop::all() as $key => $shop)
                                <option @php if($pro_sort_by==$shop->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $shop->id }}">{{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Date Range :</label>
                            <div class="col-md-12">
                                <input type="date" name="from_date" class="form-control" value="{{$from_date}}">
                            </div>
                            <div class="col-md-12">
                                <input type="date" name="to_date" class="form-control" value="{{$to_date}}">
                            </div>
                            <div class="clearfix"></div>
                        </div>


                        <div class="col-md-4 mt-4">
                            <button class="btn btn-primary" type="submit">{{ translate('Filter') }}</button>
                            <button class="btn btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>
                        </div>
                    </div>
                </form>
                <div class="printArea">
                <style>
th{text-align:center;}
</style>
                <h3 style="text-align:center;">{{translate('Product wise stock ledger report')}}</h3>
                    <table class="table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%">{{ translate('SL') }}</th>
                                <th style="width:42%">{{ translate('Product Name') }}</th>
                                <th style="width:15%">{{ translate('Shop Name') }}</th>
                                <th style="width:8%">{{ translate('Opening Stock Qty') }}</th>
                                
                                <th style="width:10%">{{ translate('Opening Stock Amount') }}</th>
                                <th style="width:10%">{{ translate('Purchase Qty') }}</th>
                                <th style="width:10%">{{ translate('Purchase Amount') }}</th>
                                <th style="width:10%">{{ translate('Sale Qty') }}</th>
                                <th style="width:10%">{{ translate('Sale Amount') }}</th>
                                
                                <th style="width:10%">{{ translate('Closing Qty') }}</th>
                                <th style="width:10%">{{ translate('Closing Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php $total = 0; @endphp
                            @foreach ($products as $key => $product)
                            
                            @php
                            $qty = 0;
                            
                            @endphp
                            @php $total = $total+($qty*$product->purchase_price); @endphp
                            <tr>
                                <td>{{ ($key+1) }}</td>
                                <td>{{ $product->getTranslation('name') }}</td>

                                <td>
                                    @php 
                                        if(!empty($product->shopsname)){
                                            echo $product->shopsname;
                                        }else{
                                        echo Null;
                                        }
                                    @endphp
                                </td>  

                                <td>{{ $product->opening_stock_qty }}</td>
                                
                                <td style="text-align:right;">{{ single_price($product->opening_stock_amount) }}</td>
                                <td style="text-align:right;">{{ $product->purchase_qty }}</td>
                                <td style="text-align:right;">{{ single_price($product->purchase_amount) }}</td>
                                <td style="text-align:right;">{{ $product->sale_qty }}</td>
                                <td style="text-align:right;">{{ single_price($product->sale_amount) }}</td>
                                <td style="text-align:right;">{{ $product->closing_qty }}</td>
                                <td style="text-align:right;">{{ single_price($product->closing_amount) }}</td>
                            </tr>
                            @endforeach
                           
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>


    @endsection