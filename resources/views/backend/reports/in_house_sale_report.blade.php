@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">Note : Included with (Pending + Confirmed + PickedUp + OnTheWay + Delivered)</h1>
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
            <div class="card-body">
                <form id="culexpo" action="{{ route('in_house_sale_report.index') }}" method="GET">
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
                            <button class="btn btn-primary" onclick="submitForm('{{ route('in_house_sale_report.index') }}')">{{ translate('Filter') }}</button>
                            <button class="btn btn-primary" onclick="submitForm('{{ route('productswise_sale_export') }}')">{{ translate('Excel') }}</button>
                            <button class="btn btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>
                        </div>
                    </div>
                </form>

                <div class="printArea">
                <style>
th{text-align:center;}
</style>
                    <h3 style="text-align:center;">{{translate('Product wise sales report')}}</h3>
                    <table class="table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%">SL</th>
                                <th style="width:5%">{{ translate('Product Id') }}</th>
                                <th style="width:15%">{{ translate('Product Name') }}</th>
                                <th style="width:10%">{{ translate('Category') }}</th>
                                <th style="width:10%">{{ translate('Shop Name') }}</th>
                                <th style="width:10%">{{ translate('Brand') }}</th>
                                <th style="width:10%">{{ translate('Sales Qty') }}</th>
                                <th style="width:10%">{{ translate('Unit Price') }}</th>
                                <th style="width:10%">{{ translate('Amount') }}</th>
                                <th style="width:10%">{{ translate('Total Order') }}</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                        @php $total = 0;$qty = 1; @endphp
                            @foreach ($products as $key => $product)
                            @php $total = $total+($product->price); @endphp
                        
                        <?php if(!empty($product->quantity)){
                        $qty = $product->quantity;
                        }else{
                        $qty = 1;
                        }
                        ?>                   
                            <tr>
                                <td>{{ ($key+1)}}</td>
                                <td>{{ $product->getTranslation('product_id') }}</td>
                                <td>{{ $product->getTranslation('product_name') }}</td>
                                <td>{{ $product->getTranslation('category_name') }}</td>
                                <td>{{ $product->getTranslation('shopname') }}</td>
                                <td>{{ $product->getTranslation('brand_name') }}</td>
                                <td style="text-align:right;">{{ $product->getTranslation('quantity') }}</td>
                                <td style="text-align:right;">{{ single_price($product->getTranslation('price')/ $qty) }}</td>
                                <td style="text-align:right;">{{ single_price($product->price) }}</td>
                                <td style="text-align:center;">{{ $product->num_of_sale }}</td>
                            </tr>
                            @endforeach
                            <tr>
                    <td style="text-align:right;" colspan="8"><b>Total</b></td>
                    <td style="text-align:right;"><b>{{single_price($total)}}</b></td>
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