@extends('backend.layouts.app')
@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">Note : This Reports All Data Depend On Only Delivered Orders</h1>
    </div>
</div>
    <style>
        th {
            border-color: black !important;
            border-width: 1px;
            border-style: solid;
        }

        td {
            border-color: black !important;
            border-width: 1px;
            border-style: solid;
        }
    </style>

    <div class="container my-5">


        <table class="table">
            <thead>
                <tr style="border-color: black !important; border-width: 2px; border-style: solid;">
                    <th colspan="7" class="text-center" ><h6>First Order Repeated Customers</h6></th>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">Total Customers:</td>
                    <td colspan="4" class="text-center">{{ $total_user }} </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">Total First Orders:</td>
                    <td colspan="4" class="text-center">{{ $Total_First_Orders }} </td>

                </tr>
                <tr>
                    <td class="text-center" style=" padding: 20px;">Total First Order Half <br> Price Customer</td>
                    <td class="text-center" style=" padding: 20px;">{{$first_order_half_price}}</td>
                    <td class="text-center"></td>
                    <td colspan="3" class="text-center" style=" padding: 20px;">Total First Order C20 Customers</td>
                    <td class="text-center" style=" padding: 20px;">{{ $first_order_c20 }}</td>
                </tr>

                <tr>
                    <td class="text-center" style=" padding: 25px;">Other First Orders</td>
                    <td class="text-center" style=" padding: 25px;">{{ $Total_First_Orders - $first_order_half_price - $first_order_c20 }}</td>
                
                    <td colspan="5" class="text-center"></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">Total First Order Repeated Customers</td>
                    <td class="text-center">{{$duplicate_order}}</td>
                </tr>  
                <tr>
                    <td colspan="2" class="text-center">% of First Order Repeated Customers</td>
                    <td class="text-center">{{  number_format((($duplicate_order * 100) / $Total_First_Orders),2) }}%</td>
                </tr>

                <tr>
                    <td colspan="2" class="text-center">Total First Order Half Price Repeated Customers</td>
                    <td class="text-center">{{$duplicates_first_order}}</td>
                </tr>  
                <tr>
                    <td colspan="2" class="text-center">% of First Order Half Price Repeated Customers</td>
                    <td class="text-center">{{  number_format((($duplicates_first_order * 100) / $first_order_half_price),2) }}%</td>
                </tr>
                <tr>
                    <td colspan="7" style="border: none;"></td>
                </tr>
                <tr style="border-color: black !important; border-width: 2px; border-style: solid;">
                    <th scope="col">Serial No.</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Total Order</th>
                    <th scope="col">Producy Qty.</th>
                    <th scope="col">Total Amount</th>
                    <th scope="col">View Details</th>

                </tr>
            </thead>

            <tbody >
                <tr>
                    <th scope="row">1</th>
                    <td>Mark</td>
                    <td>Otto</td>
                    <td>@mdo</td>

                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Jacob</td>
                    <td>Thornton</td>
                    <td>@fat</td>

                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <th scope="row">3</th>
                    <td>Larry the Bird</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>@twitter</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
