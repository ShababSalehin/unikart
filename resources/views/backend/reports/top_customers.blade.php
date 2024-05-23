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
                            <label class="col-form-label">{{translate('Sort by')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker" name="order_by">

                                <option @php if($order_by=="noOfOrder" ) echo 'selected' ; @endphp value="noOfOrder">No of Order</option>
                                <option @php if($order_by=="amount" ) echo 'selected' ; @endphp value="amount">Amount</option>

                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Top')}} :</label>
                            <select id="demo-ease" class="aiz-selectpicker" name="top">
                                <option @php if($top==100) echo 'selected' ; @endphp value="100">100</option>
                                <option @php if($top==75) echo 'selected' ; @endphp value="75">75</option>
                                <option @php if($top==50) echo 'selected' ; @endphp value="50">50</option>
                                <option @php if($top==25) echo 'selected' ; @endphp value="25">25</option>
                                <option @php if($top==10) echo 'selected' ; @endphp value="10">10</option>
                                <option @php if($top==5) echo 'selected' ; @endphp value="5">5</option>
                            </select>
                        </div>
                        

                        <div class="col-md-3">
                            <label class="col-form-label">{{translate('Sort by Single customer ')}} :</label>
                            <input type="text" class="form-control" value="<?php echo $phone; ?>" name="phone" placeholder="Enter Phone No.">
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
                            <button class="btn btn-primary" onclick="submitForm('{{ route('topCustomersReport.index') }}')">{{ translate('Filter') }}</button>
                            <button class="btn btn-primary" onclick="submitForm('{{ route('top_customer_download') }}')">{{ translate('Excel') }}</button>
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
                                    <th style="width:15%">{{ translate('Customer Name') }}</th>
                                    <th style="width:15%">{{ translate('Customer Phone') }}</th>
                                    <th style="width:15%">{{ translate('Total Order') }}</th>
                                    <th style="width:15%">{{ translate('Date') }}</th>
                                    <th style="width:10%">{{ translate('Product QTY') }}</th>
                                    <th style="width:15%">{{ translate('Total Amount') }}</th>
                                    <th style="width:10%">{{ translate('View Details') }}</th>
                               </tr>
                        </thead>
                        <tbody>
                            @php $total = 0;$qty = 1;$total_qty=0;$total_amount=0; @endphp
                            @foreach ($topcustomers as $key => $topcustomer)
                                @php 
                                    $total_amount=$total_amount+$topcustomer->amount;
                                @endphp
                            <tr>
                                <td>{{ ($key+1)}}</td>
                                    <td>{{$topcustomer->customer_name}}</td>
                                    <td>{{$topcustomer->customer_phone}}</td>
                                    <td>{{$topcustomer->totalorder}}</td>
                                    @if(!empty($start_date))
                                    <td>{{$start_date}} To {{$end_date}}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td style="text-align:center;">{{$topcustomer->productquantity}}</td>
                                    <td style="text-align:right;">{{$topcustomer->amount}}</td>
                                    @if(!empty($start_date))
                                    <td style="text-align: center;"><a class="btn btn-soft-primary btn-icon btn-circle btn-sm" target="blank" href="{{ route('topcustomerdetails.show',['customerid'=>$topcustomer->userid,'start_date'=>$start_date,'end_date'=>$end_date]) }}"
                                     target="blank" title="{{ translate('TopSale Details') }}">
                                     @else
                                    
                                    <td style="text-align: center;"><a class="btn btn-soft-primary btn-icon btn-circle btn-sm" target="blank" href="{{ route('topcustomerdetails.show',['customerid'=>$topcustomer->userid,'start_date'=>0,'end_date'=>0]) }}"
                                     target="blank" title="{{ translate('TopSale Details') }}">
                                     @endif
                                	<i class="las la-eye"></i>
                            </a></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td style="text-align:right;" colspan="6"><b>Total</b></td>
                                <td style="text-align:right;"><b>{{$total_amount}}</b></td>
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