@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Pick Up Reports')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <div class="card-body">
                <form id="culexpo" action="{{ route('picked_up_report') }}" method="GET">
                    <div class="form-group row">
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
                            <button class="btn btn-primary" onclick="submitForm('{{ route('picked_up_report') }}')">{{ translate('Filter') }}</button>
                            <button class="btn btn-primary" onclick="submitForm('{{ route('tobepickup_report') }}')">{{ translate('Excel') }}</button>
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
                    <h3 style="text-align:center;">{{translate('Pick Up Reports Details')}}</h3>
                    <table class="table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%">SL</th>
                                <th style="width:5%">Order Id</th>
                                <th style="width:30%">{{ translate('Product Name') }}</th>
                                <th style="width:20%">{{ translate('Product Photo') }}</th>
                                <th style="width:10%">{{ translate('Qty') }}</th>
                                <th style="width:10%">{{ translate('Shop Name') }}</th>
                                <th style="width:10%">{{ translate('Shop Address') }}</th>
                                <th style="width:10%">{{ translate('Shop contact No') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($products as $key => $product)

                            <?php if (!empty($product->quantity)) {
                                $qty = $product->quantity;
                            } else {
                                $qty = 1;
                            }
                            ?>
                            <tr>
                                <td>{{ ($key+1)}}</td>
                                <td>{{$product->code}}</td>
                                <td>{{ $product->getTranslation('product_name') }}</td>
                                <td> <span class="avatar avatar-md mr-3">
                                        <img class="lazyload" src="{{ uploaded_asset($product->thumbnail_img) }}" onerror="this.onerror=null;this.src='{{ uploaded_asset($product->thumbnail_img) }}';">
                                    </span></td>
                                <td style="text-align:center;">{{ $product->getTranslation('quantity') }}</td>
                                <td>{{ $product->getTranslation('name') }}</td>
                                <td>{{ $product->getTranslation('address') }}</td>
                                <td style="text-align:center;">{{ $product->getTranslation('phone') }}</td>


                            </tr>
                            @endforeach

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