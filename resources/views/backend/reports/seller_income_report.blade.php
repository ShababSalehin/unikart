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
            <form id="culexpo" action="{{ route('income_report.index') }}" method="GET">
    <div class="card-header row gutters-5">
        <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ translate('Commission History') }}</h5>
        </div>
        @if(Auth::user()->user_type != 'seller')
        <div class="col-md-3 ml-auto">
            <select id="demo-ease" class="form-control select2 form-control-sm aiz-selectpicker mb-2 mb-md-0" name="seller_id" data-live-search="true">
                <option value="">{{ translate('Choose Seller') }}</option>
                @foreach (\App\Seller::all() as $key => $seller)
               
                    @if(isset($seller->user->id))
                    <option value="{{ $seller->user->id }}" @if($seller->user->id == $seller_id) selected @endif >
                        {{ $seller->user->name }}
                    </option>
                    @endif
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-3">
            <div class="form-group mb-0">
                <input type="text" class="form-control form-control-sm aiz-date-range" id="search" name="date_range"@isset($date_range) value="{{ $date_range }}" @endisset placeholder="{{ translate('Daterange') }}">
            </div>
        </div>
        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <br>
                            <button class="btn btn-primary" onclick="submitForm('{{ route('income_report.index') }}')">{{ translate('Filter') }}</button>
                            <button class="btn btn-primary" onclick="submitForm('{{ route('income_report_download') }}')">{{ translate('Excel') }}</button>
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
                <table class="table table-bordered aiz-table mb-0 data-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Seller Name') }}</th>
                            <th>{{ translate('Total Sale') }}</th>
                            <th>{{ translate('Due To Seller') }}</th>
                            <th>{{ translate('Unicart Commission') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                        if($date_range){
                                $date_range1 = explode(" / ", $date_range);
                        }
                        $gtotalsale = 0;
                        $gtotal_due = 0;
                        $gearning = 0;
                        $gnatincome = 0;
                        @endphp
                        @foreach ($sellers as $key => $seller)
                            @if($seller->user != null)
                            @php
                            if($date_range){
                            $total_sale = \App\OrderDetail::where(['seller_id'=>$seller->user_id,'delivery_status'=>'delivered','refund_status'=>0])
                                                           ->where('created_at', '>=', $date_range1[0])
                                                           ->where('created_at', '<=', $date_range1[1])
                                                           ->select('refund_status',DB::raw('sum(order_details.price) AS total_price'),
                                                            DB::raw('sum(quantity) AS total_quantity'),
                                                            DB::raw('sum(due_to_seller) AS total_due_to_seller'),
                                                            DB::raw('sum(unikart_earning) AS total_unikart_earning'),
                                                            DB::raw('sum(shipping_cost) AS total_shipping_cost')
                                   
                                    )
                                    ->get();
                           
                          
                            }else{
                                $total_sale = \App\OrderDetail::where(['seller_id'=>$seller->user_id,'delivery_status'=>'delivered','refund_status'=>0])
                                ->select('refund_status',DB::raw('sum(order_details.price) AS total_price'),
                                DB::raw('sum(quantity) AS total_quantity'),
                                DB::raw('sum(due_to_seller) AS total_due_to_seller'),
                                DB::raw('sum(unikart_earning) AS total_unikart_earning'),
                                DB::raw('sum(shipping_cost) AS total_shipping_cost')
                                )
                                ->get();
                            
                            }

							
                            $total_sale_amount=0;
                            $total_seller_income=0;
                            $netincome=0;

                            if(isset($total_sale[0]['total_unikart_earning'])){
                                $total_sale_amount=$total_sale[0]['total_price'];
                                $total_seller_income=$total_sale[0]['total_due_to_seller'];
                                $netincome = $total_sale[0]['total_unikart_earning'];
                                $gtotalsale +=$total_sale[0]['total_price'];
                                $gtotal_due +=$total_sale[0]['total_due_to_seller'];
                            }


                            $gnatincome  +=$netincome;
                            
                          //  $gtotalsale +=$total_sale;
                          //  $gtotal_due +=$total_due;
                         //   $gnatincome  +=$netincome;
                            @endphp
                                <tr>
                                    <td><a target="_blank" href="{{ route('income_report.details', $seller->user->id) }}">{{ $seller->user->name }}</a></td>
                                    <td>
                                        {{ single_price($total_sale_amount) }}
                                    </td>
                                    <td>
                                        {{ single_price($total_seller_income) }}
                                    </td>
                                 <td>{{ single_price($netincome) }}</td>

                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border">
                            <th>Total</th>
                            <th>{{single_price($gtotalsale)}}</th>
                            <th>{{single_price($gtotal_due)}}</th>
                            <th>{{single_price($gnatincome)}}</th>
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
<script type="text/javascript">
function submitForm(url){
   $('#culexpo').attr('action',url);
   $('#culexpo').submit();
}
</script>
@endsection
