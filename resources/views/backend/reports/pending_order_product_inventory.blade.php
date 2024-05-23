@extends('backend.layouts.app')

@section('content')
@php
$refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp

<div class="card">
    <form  id="culexpo" class="" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ translate('Pending Order Product Stock Report') }}</h5>
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
                <button class="btn btn-sm btn-primary" onclick="submitForm ('{{ route('pendingOrderProductStockReport.index') }}')">{{ translate('Filter') }}</button>
                    <button class="btn btn-sm btn-info" onclick="submitForm('{{ route('sales_pending_order_product_stock_ledger_export') }}')">Excel</button>
                    <button class="btn btn-sm btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>                    
                </div>
            </div>
        </div>
    </form>
    <div class="card-body printArea">
    <style>
th{
    text-align:center;
}
</style>
    <h3 style="text-align:center;">{{translate('Pending Order Product Stock Report')}}</h3>
        <table class="table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SL</th>
                    
                    <th data-breakpoints="md">{{ translate('Product Name') }}</th>
                    <th data-breakpoints="md">{{ translate('Order Qty') }}</th>
                    <th data-breakpoints="md">{{ translate('Stock Qty') }}</th>
                    
                   
                  
                </tr>
            </thead>
            <tbody>
                @php 
                $i = 0;
                @endphp
            @foreach ($products as $key => $order)
              @php 
              $i++;

			  $total_stock_info = \App\ProductStock::where(['product_id'=>$order->product_id])
                                ->select(
                                    DB::raw('sum(qty) AS total_stock_quantity'),
                                                                      
                                    )
                                ->get();

				
				
              @endphp
                
            <tr>
                    <td>
                        {{ ($i) }}
                    </td>

                    <td style="text-align:left;">
                        {{ $order->product_name }}
                    </td>
                   
                    <td style="text-align:right;">
                        {{ $order->quantity }}
                    </td>
                   
                    <td style="text-align:right;">
                        {{ $order->qty }}
                    </td>
                   
                </tr>
            @endforeach
            </tbody>
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