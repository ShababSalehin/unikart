@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Product wise stock report')}}</h1>
    </div>
</div>
@php
$categories = \App\Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get(); 
@endphp
<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <!--card body-->
            <div class="card-body">
                <form id="culexpo" action="{{ route('stock_report.index') }}" method="GET">
                    <div class="form-group row">
                        
                    <div class="col-md-3">
                        <label class="col-form-label">{{translate('Sort by Category')}} :</label>
                        <select id="demo-ease " class="select2 form-control aiz-selectpicker" name="category_id" data-live-search="true">
                                <option value=''>Chose Category</option>
                                @foreach ($categories as $category)
                                <option @php if($sort_by==$category->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                    @foreach ($category->childrenCategories as $childCategory)
                                    @include('categories.child_category', ['child_category' => $childCategory,'value'=>$category->getTranslation('name').'/'])
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                        <label class="col-form-label">{{translate('Sort by Brand')}} :</label>
                            <select id="demo-ease" class="from-control aiz-selectpicker" name="brand_id" data-live-search="true">
                                <option value=''>All</option>
                                @foreach (\App\Brand::all() as $key => $brand)
                                <option @php if($sort_by==$brand->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                        <label class="col-form-label">{{translate('Sort by Shop')}} :</label>
                            <select id="demo-ease" class="from-control aiz-selectpicker" name="shop_id" data-live-search="true">
                                <option value=''>All</option>
                                @foreach (\App\Shop::all() as $key => $shop)
                                <option @php if($sort_by==$shop->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $shop->id }}">{{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Product')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker select2" name="product_id" data-live-search="true">
                                <option value=''>All</option>
                                @foreach (DB::table('products')->select('id','name')->get(); as $key => $prod)
                                <option @php if($pro_sort_by==$prod->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $prod->id }}">{{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mt-4">
                            <button class="btn btn-primary" onclick="submitForm ('{{ route('stock_report.index') }}')">{{ translate('Filter') }}</button>
                            <button class="btn btn-info" onclick="submitForm('{{ route('products_stock_export') }}')">Excel</button>
                            <button class="btn btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>
                        </div>
                    </div>
                </form>
                <div class="printArea">
                <style>
th{text-align:center;}
</style>
                <h3 style="text-align:center;">{{translate('Product wise stock report')}}</h3>
                    <table class="table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%">{{ translate('SL') }}</th>
                                <th style="width:5%">{{ translate('Product ID') }}</th>
                                <th style="width:35%">{{ translate('Product Name') }}</th>
                                <th style="width:7%">{{ translate('Status') }}</th>
                                <th style="width:15%">{{ translate('Shop Name') }}</th>
                                <th style="width:15%">{{ translate('Total Sales QTY') }}</th>
                                <th style="width:10%">{{ translate('Unit Price') }}</th>
                                <th style="width:10%">{{ translate('Stock') }}</th>
                                <th style="width:10%">{{ translate('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php $total = 0; @endphp

                            @foreach ($products as $key => $product)
                            
                            @php
                                $qty = 0;
                                foreach ($product->stocks as $stock) {
                                    $qty += $stock->qty;
                                }
                                 $amount = $qty * $product->unit_price * 0.7;
                              $total += $amount;
                            @endphp

                            @php $total = $total+($qty*$product->purchase_price); @endphp
                            <tr>
                                <td>{{ ($key+1) }}</td>
                                <td>{{ $product->getTranslation('id') }}</td>
                                <td>{{ $product->getTranslation('name') }}</td>

                                <td>
                                    @php 
                                    if($product->published == 1){
                                        $pstatus = "Published";
                                    }else{
                                        $pstatus = "UnPublished";
                                    }
                                  @endphp
                                  {{$pstatus}}
                                </td>


                                <td>
                                    @php 
                                    if(!empty($product->shopsname)){
                                        echo $product->shopsname;
                                    }else{
                                       echo Null;
                                    }
                                  @endphp
                                </td>
                                <td style="text-align:center;">{{ $product->sales_quantity }}</td>
                                <td style="text-align:center;">{{ $product->unit_price *.7 }}</td>
                               
                                <td style="text-align:center;">{{ $qty }}</td>
                                <td style="text-align:center;">{{ single_price($qty*$product->unit_price *.7) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                    <td style="text-align:center;" colspan="8"><b>Total</b></td>
                    <td style="text-align:center;"><b>{{single_price($total)}}</b></td>
                </tr>
                        </tbody>
                    </table>

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