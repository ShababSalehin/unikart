@extends('backend.layouts.app')
@section('content')

<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <span class="pull-left" style="margin-left: 10px;margin-bottom: 10px;"><a href="{{route('purchase_orders.index')}}"><i class="fa fa-fast-backward"></i> Back</a></span>
                <span class="pull-right" style="margin-right: 10px;margin-bottom: 10px;">

                    <a class="btn btn-circle btn-info" href="Javascript:" onclick="printDivd('print_contents')"><i class="fa fa-print"></i> Print</a>
                </span>
            </div>
            <div class="col-md-12">
                <select style="width:300px" class="form-control" id="update_payment_status" @if ($purchase[0]->payment_status == 3) disabled @endif>
                    <option value="">{{translate('Change Payment Status')}}</option>
                    <option <?php if ($purchase[0]->payment_status == 3) echo 'selected'; ?> value="3">{{translate('Paid')}}</option>
                    <!-- <option value="unpaid">{{translate('Unpaid')}}</option> -->
                    <option <?php if ($purchase[0]->payment_status == 2) echo 'selected'; ?> value="2">{{translate('Partial Payment')}}</option>
                </select>
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
                                <label>{{translate('Purchase Date')}} <span>*</span></label>

                                {{$purchase[0]->date}}

                            </div>
                            <div class="col-md-6 pull-left">
                                <label>{{translate('Purchase No')}} <span>:</span></label>

                                {{$purchase[0]->purchase_no}}

                            </div>

                            <div class="col-md-6 pull-left">
                                <label>{{translate('Batch No')}} <span>:</span></label>
                                {{$purchase[0]->batch_no}}

                            </div>

                            <div class="col-md-6 pull-left">
                                <label>{{translate('Remarks')}} <span>:</span></label>

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
                                                    <td>{{$i++}}</td>
                                                    <td>
                                                        <strong><a href="{{ route('product', $value->slug) }}" target="_blank" class="text-muted">{{ $value->name ." ". ($value->desc ? $value->desc : '')}}</a></strong>
                                                    </td>
                                                    <td>{{$value->decs}}</td>
                                                    <td class="text-center">{{$total_qty[] = $value->qty}}</td>
                                                    <td class="text-right">{{$total_mrp[] = $value->price}}</td>
                                                    <td class="text-right">{{$amount[] = $value->amount}}</td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-right fwb" colspan="3">Total:</td>
                                            <td class="fwb text-center">{{ array_sum($total_qty) }}</td>
                                            <td class="text-right">{{ array_sum($total_mrp) }}</td>

                                            <td id="total_due" class="text-right">{{ array_sum($amount) }}</td>

                                        </tr>
                                    </tbody>
                                </table>



                            </div>
                            <div class="clearfix float-right">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong class="text-muted">{{translate('Paid Amount')}} :</strong>
                                            </td>
                                            <td class="text-muted h5">

                                                {{ $purchase[0]->payment_amount}}

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <strong class="text-muted">{{translate('Due Amount')}} :</strong>
                                            </td>
                                            <td class="text-muted h5">
                                                {{ array_sum($amount) - $purchase[0]->payment_amount}}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>

                            </div>
                        </div>





                    </div>
                </section>
            </div>

        </div>

        <div class="modal fade" id="payment-modal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-zoom" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel">{{ translate('Payment')}}</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2">
                                    <label>{{ translate('Amount')}}</label>
                                </div>
                                <div class="col-md-10">
                                    <input type="number" id="payment_amount" value="{{ array_sum($amount) }}" class="form-control textarea-autogrow mb-3" placeholder="{{ translate('Amount')}}" rows="1" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label>{{ translate('Payment Date')}}</label>
                                </div>
                                <div class="col-md-10">
                                    <input type="date" id="payment_date" value="" class="form-control textarea-autogrow mb-3" placeholder="{{ translate('Date')}}" rows="1" name="date" required>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="save_payment" onclick="save_payment()" class="btn btn-primary">{{ translate('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>

    </section>
</section>
<!--main content end-->
@section('script')
<script>
    $('#update_payment_status').on('change', function(){
            var order_id = {{ $purchase[0]->id }};
            var dueAmount = parseInt($('#total_due').text());
            
            var status = $('#update_payment_status').val();
            if(status==3){
              $('#payment-modal').modal('show');
              $('#payment_amount').val(dueAmount);
              $('#payment_amount').attr('disabled', true);
            }else if(status==2){              
              $('#payment-modal').modal('show');
              $('#payment_amount').val('');
              $('#payment_amount').val(dueAmount);
              $('#payment_amount').attr('disabled', false);
            }else if(status=='unpaid'){
              $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                AIZ.plugins.notify('success', '{{ translate('Payment status has been updated') }}');
                location.reload().setTimeOut(500);
            });
            }
            
        });

        function save_payment(){
          $('#save_payment').attr('disabled', true);
          var dueAmount = parseInt($('#total_due').val());
          
          var purchase_id = {{ $purchase[0]->id }};
            var status = $('#update_payment_status').val();
            var payment_amount = parseInt($('#payment_amount').val());
            var payment_date = $('#payment_date').val();

            if(payment_amount > dueAmount){
                alert('Paid amount must be less than or equal from due amount');
                $('#payment_amount').val('');
                $('#save_payment').attr('disabled', false);
                return false;
            }
            
            if(payment_date == ''){
                alert('Please enter the date');
                $('#save_payment').attr('disabled', false);
                return false;
            }
            
            $.post('{{ route('orders.purchase_update_payment_status') }}', {_token:'{{ @csrf_token() }}',payment_amount:payment_amount,payment_date:payment_date,purchase_id:purchase_id,status:status}, function(data){
                AIZ.plugins.notify('success', '{{ translate('Payment status has been updated') }}');
                $('#payment-modal').modal('hide')
                location.reload().setTimeOut(500);
            });
        }

        function printDivd(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }

</script>
@endsection
@stop