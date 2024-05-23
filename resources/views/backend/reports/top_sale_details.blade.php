@extends('backend.layouts.app')

@section('content')


<div class="card">

    <div class="card-body printArea">
        <style>
            th {
                text-align: center;
            }
        </style>
        <h3 style="text-align:center;">{{translate('Top Sales Details')}}</h3>
        <table class="table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>SL</th>
        			<th>{{ translate('Order Id') }}</th>
                    <th>Order Code</th>
                    <th>{{ translate('Order Date') }}</th>
                    <th>{{ translate('Product Name') }}</th>
                    <th>{{ translate('Customer Name') }}</th>
                    <th>{{ translate('Customer Phone') }}</th>
                    <th>
                    @php
                        if($profit == 'quantity'){
                        echo 'QTY';
                        }else{
                        echo 'Amount';
                        }
                        @endphp
                    </th>
                </tr>
            </thead>
            <tbody>
                @php      
                $totalamount = 0;
                $totalqty = 0;
                @endphp
                @foreach ($orders as $key => $order)
                @php
                $totalamount =  $totalamount+($order->price);
                $totalqty =   $totalqty+($order->quantity);
                @endphp
                <tr>
                    <td>
                        {{ ($key+1) }}
                    </td>
                    <td>{{$order->id}}</td>
                    <td>{{$order->code}}</td>
                    <td>
                        {{ $order->created_at }}
                    </td>
                    <td>{{$order->productname}}</td>
                    <td>{{$order->username}}</td>
                    <td>{{$order->phone}}</td>
                  <td>
                  @php
                        if($profit == 'quantity'){
                            echo $order->quantity;
                        }else{

                        echo $order->price;
                        }
                        @endphp
                  </td>
                  
                </tr>
             
                @endforeach
             
            </tbody>
            <tr>
                    <td style="text-align:right;" colspan="6"><b>Total</b></td>

                    <td style="text-align: center;">
                    @php
                        if($profit == 'quantity'){
                            echo $totalqty;
                        }else{

                            echo $totalamount;
                        }
                        @endphp
                      
                    </td>
                </tr>
        </table>

    </div>
</div>

@endsection

@section('modal')
@include('modals.delete_modal')
@endsection

@section('script')


@endsection