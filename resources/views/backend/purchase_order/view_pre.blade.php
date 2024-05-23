@extends('backend.layouts.app')
@section('content') 

<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <span class="pull-left" style="margin-left: 10px;margin-bottom: 10px;"><a href="{{route('purchase_orders.index')}}"><i class="fa fa-fast-backward"></i> Back</a></span>
                <span class="pull-right" style="margin-right: 10px;margin-bottom: 10px;">
                    
                    <a class="btn btn-circle btn-info" href="Javascript:" onclick="printDiv('print_contents')"><i class="fa fa-print"></i> Print</a>
                </span>
            </div>
            <div class="clearfix"></div>
            <div class="col-lg-12" id="print_contents">
                <section class="panel">
                <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Supplier Information')}}</h5>
                </div>
                <div class="card-body">

                    <div class="col-md-6 pull-left">
                        <label>{{translate('Supplier Name')}} <span>:</span> {{$purchase[0]->name}}</label>

                        

                    </div>
                    <div class="col-md-6 pull-left">
                        <label>{{translate('Purchase Date')}} <span >*</span></label>

                        {{$purchase[0]->date}}

                    </div>
                    <div class="col-md-6 pull-left">
                        <label>{{translate('Purchase No')}} <span >:</span></label>

                        {{$purchase[0]->purchase_no}}

                    </div>
                    <div class="col-md-6 pull-left">
                        <label>{{translate('Remarks')}} <span >:</span></label>

                        {{$purchase[0]->remarks}}

                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
                </div>
                <div class="card-body">
                        <div class="form-group">
                            <table id="add_line" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Product</th>
                                        <th>Description</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Unit Price</th>
                                        <th class="text-right">Amount(Tk)</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $total_qty = array();
                                    $total_mrp = array();
                                    $total_mrp_sum = array();
                                    $total_mrp_discounted = array();
                                    $amount = array();
                                    if ($data_item_rows) {

                                        foreach ($data_item_rows as $value) {
                                            ?>
                                            <tr id="item_row_1" class="global_item_row">
                                                <td>{{{$i++}}}</td>
                                                <td>{{{$value->name}}}</td>
                                                <td>{{{$value->decs}}}</td>
                                                <td class="text-center">{{{$total_qty[] = $value->qty}}}</td>
                                                <td class="text-right">{{{$total_mrp[] = $value->price}}}</td>
                                                
                                                <td class="text-right">{{{$amount[] = $value->amount}}}</td>
                                               
                                                
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-right fwb" colspan="3">Total:</td>
                                        <td class="fwb text-center">{{ array_sum($total_qty) }}</td>
                                        <td class="text-right">{{ array_sum($total_mrp) }}</td>
                                       
                                        <td class="text-right">{{ array_sum($amount) }}</td>
                                        
                                    </tr>
                                </tbody>
                            </table>

                            

                        </div>

                    </div>
                    </div>
                </section>
            </div>

        </div>

    </section>
</section>
<!--main content end-->
@stop