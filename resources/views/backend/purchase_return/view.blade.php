@extends('backend.layouts.app')
@section('content')

<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <span class="pull-left" style="margin-left: 10px;margin-bottom: 10px;"><a href="{{route('purchase_return.index')}}"><i class="fa fa-fast-backward"></i> Back</a></span>
                <span class="pull-right" style="margin-right: 10px;margin-bottom: 10px;">

                <button class="btn btn-sm btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button> 
                </span>
            </div>

            <div class="clearfix"></div>
            <div class="col-lg-12" id="print_contents">
                <section class="panel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Return Information')}}</h5>

                        </div>

                    </div>
                    <div class="card-body printArea">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <table id="add_line" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Return ID</th>
                                            <th>Product Name</th>
                                            <th>Product Varient</th>
                                            <th>Qty</th>
                                            <th class="text-center">Unit Price</th>
                                            <th class="text-right">Amount(Tk)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $total_qty = 0;
                                        $total_unit_price = 0;
                                        $total_amount = 0;
                                        ?>

                                        @foreach ($data_item_rows as $key => $value)
                                        @php
                                        $total_qty += $value->return_qty;
                                        $total_unit_price +=$value->unite_price;
                                        $total_amount += $value->total_amount;
                                        @endphp

                                        <tr id="item_row_1" class="global_item_row">
                                            <td>{{$i++}}</td>
                                            <td>{{$value->return_id}}</td>
                                            <td>{{$value->name}}</td>
                                            <td>{{$value->product_verient}}</td>
                                            <td class="text-center">{{$value->return_qty}}</td>
                                            <td class="text-center">{{$value->unite_price}}</td>
                                            <td class="text-center">{{$value->total_amount}}</td>
                                        </tr>
                                        @endforeach

                                        <tr>
                                            <td class="text-right fwb" colspan="4">Total:</td>
                                            <td class="fwb text-center">{{ ($total_qty) }}</td>
                                            <td class="fwb text-center">{{ $total_unit_price }}</td>
                                            <td id="total_due" class="fwb text-center">{{$total_amount }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                            <div class="clearfix float-right">


                            </div>
                        </div>

                    </div>
                </section>
            </div>

        </div>

        </div>

    </section>
</section>
<!--main content end-->
@section('script')
<script>

</script>
@endsection
@stop