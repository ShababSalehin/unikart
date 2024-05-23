@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Top Customers report')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <div class="card-body">
                <form id="culexpo" action="{{ route('topCustomersReport.index') }}" method="GET">
                    <div class="form-group row">
                    
                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Top')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker" name="top">
                                <option @php if($top==100) echo 'selected' ; @endphp value="100">100</option>
                                <option @php if($top==75) echo 'selected' ; @endphp value="75">75</option>
                                <option @php if($top==50) echo 'selected' ; @endphp value="50">50</option>
                                <option @php if($top==25) echo 'selected' ; @endphp value="25">25</option>
                                <option @php if($top==10) echo 'selected' ; @endphp value="10">10</option>
                            </select>
                        </div>


                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Single customer ')}} :</label>
                           
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
                   
                        <h3 style="text-align:center;">{{translate('Top Customers report')}}</h3>
                   
                    <table class="table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%">SL</th>
                               
                                    <th style="width:30%">{{ translate('Customer Name') }}</th>
                                    <th style="width:15%">{{ translate('Total Amount') }}</th>
                                    
                                
                                    <th style="width:10%">{{ translate('Product QTY') }}</th>
                                    <th style="width:10%">{{ translate('View Details') }}</th>
                            

                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0;$qty = 1;$total_qty=0; @endphp
                            @foreach ($products as $key => $product)

                            @php
                            $total_qty = $total_qty+($product->quantity);
							$net_profit=$product->total_unikart_earning-($product->total_shipping_cost);
                            @endphp

                            <tr>
                                <td>{{ ($key+1)}}</td>
                                    <td>test</td>
                                    <td style="text-align:center;">0.00</td>
                                    <td style="text-align:right;">0.00</td>
                                    <td style="text-align: center;"><a class="btn btn-soft-primary btn-icon btn-circle btn-sm" target="blank" href=""
                                     target="blank" title="{{ translate('TopSale Details') }}">
                                <i class="las la-eye"></i>
                            </a></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td style="text-align:right;" colspan="3"><b>Total</b></td>
                                <td style="text-align:center;"><b>0.00</b></td>
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