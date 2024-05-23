@extends('frontend.layouts.sellerapp')

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
            <form action="{{ route('seller_income_report.details', Auth::user()->id) }}" method="GET">
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
<table class="table table-bordered aiz-table mb-0 data-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Order Id') }}</th>
                            <th>{{ translate('Total Sale') }}</th>
                            <th>{{ translate('Unikart Charge') }}</th>
                            <th>{{ translate('Total Receivable') }}</th>
                            
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
                            $total_sale = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered','refund_status'=>0])
                                                           ->where('created_at', '>=', $date_range1[0])
                                                           ->where('created_at', '<=', $date_range1[1])
                                                           ->sum('price');
                            $total_due = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered','refund_status'=>0])
                                                           ->where('created_at', '>=', $date_range1[0])
                                                           ->where('created_at', '<=', $date_range1[1])
                                                           ->sum('due_to_seller');
                           
                            }else{
                            $total_sale = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered','refund_status'=>0])->sum('price');
                           
                            $total_due = \App\OrderDetail::where(['order_id'=>$seller->id,'delivery_status'=>'delivered','refund_status'=>0])->sum('due_to_seller');
                           
                        }
                            
                            
                            $netincome =  $total_sale - $total_due;
                            $gtotalsale +=$total_sale;
                            $gtotal_due  +=$total_due;
                            $gnatincome  +=$netincome;
                            @endphp
                                <tr>
                                    <td>{{ $seller->code }}</td>
                                    <td>
                                        {{ single_price($total_sale)}}
                                    </td>
                                    <td>{{ single_price($netincome) }}</td>
                                    <td>
                                        {{ single_price($total_due) }}
                                    </td>
                                  
                                   
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border">
                            <th>Total</th>
                            <th>{{single_price($gtotalsale)}}</th>
                            <th>{{single_price($gnatincome)}}</th>
                            <th>{{single_price($gtotal_due)}}</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $sellers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
