@extends('backend.layouts.app')

@section('content')
<style>
    #item_table .form-control{
        padding: 2px;
    }
</style>

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Add Return')}}</h5>
</div>
<div class="">
    <div class="">
        <form class="form form-horizontal mar-top" action="{{route('purchase_return.store')}}" method="POST" enctype="multipart/form-data" id="choice_form">
            @csrf
            <input type="hidden" name="added_by" value="admin">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Return Information')}}</h5>
                </div>
                <div class="card-body">

                    <div class="col-md-6 pull-left">
                        <label>{{translate('Purchase No')}} <span class="text-danger">*</span></label>

                        <select class="form-control aiz-selectpicker" name="purchase_id" id="purchase_id" data-live-search="true" required onchange="changePurchaseId()">
                        <option value="">Select Purchase No</option>
                            @foreach ($purchases as $purchase)
                            <option value="{{ $purchase->id }}">{{ $purchase->purchase_no}}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-6 pull-left">
                        <label>{{translate('Return Date')}} <span class="text-danger">*</span></label>

                        <input type="date" class="form-control" name="return_date" placeholder="{{ translate('Return Date') }}"  required>

                    </div>

                    
                    <div class="col-md-6 pull-left">
                        <label>{{translate('Return No')}} <span class="text-danger">*</span></label>

                        <input type="text" readonly class="form-control" name="return_no" placeholder="{{ translate('return_no No') }}" value="{{$return_no}}"  required>

                    </div>

                    <!-- <div class="col-md-6 pull-left">
                        <label>{{translate('Batch No')}} <span class="text-danger">*</span></label>
                        <input type="text"  class="form-control" id="batch_no" name="batch_no" placeholder="{{ translate('Batch No') }}" value=""  required>

                    </div> -->

                    <div class="col-md-6 pull-left">
                        <label>{{translate('Remarks')}} <span class="text-danger">*</span></label>

                        <input type="text" class="form-control" name="remarks" placeholder="{{ translate('Remarks') }}"  required>

                    </div>
                   
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
                </div>
                <div class="card-body">
                    <table id="item_table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                            <th style="width:42%;">Product</th>
                                <th style="width:15%;">Variant</th>
                                <th style="width:9%;">Quantity</th>
                                <th style="width:10%;">Unit Price</th>
                                <th style="width:5%;">Total</th>

                                <th style="width:5%;"><a href="javascript:" onclick="addItemRow()" class="btn btn-sm btn-primary"><i class="las la-plus"></i></a></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="row_1">
                                <td>
                                    <select class="form-control aiz-selectpicker" onchange="changeProduct(this)" name="product[]" id="product_1" data-live-search="true" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option data-qty="{{ $product->current_stock }}" data-price="{{ $product->purchase_price }}" value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>

                                   <input type="text" id="desc_1" name="desc[]" class="form-control" readonly>

                                </td>

                               
                                <td>
                                    <input type="number" id="qty_1" onchange="changePrice(this)" name="qty[]" class="form-control">
                                </td>
                                <td>
                                    <input type="text" id="price_1" onchange="changePrice(this)" name="price[]" class="form-control" readonly>
                                </td>
                                <td id="total_1">

                                </td>
                                <td>
                                    <a href="javascript:" onclick="removeItemRow(1)" class="btn btn-sm btn-danger"><i class="las la-minus"></i></a>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" style="text-align:right">Total
                                <input type="hidden" name="total" id="total_input">
                                </th>
                                <th id="total"></th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>

            <div class="mb-3 text-right">
                <button type="submit" name="button" class="btn btn-primary">{{ translate('Save Return') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    function changePurchaseId(){
        let purchase_id = $('#purchase_id').val();  
        $.ajax({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               url: "{{route('find_purchase_order_item')}}",
               type: 'POST',
               data: {
                purchase_id: purchase_id
               },
               success: function(data) {
                let obj = JSON.parse(data);
                if (obj != '') {
                    $('#product_1').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
                         
               }
           }); 
    }

    function changeProduct(e){
        let id = $(e).attr('id').split('_')[1];
        let product_id = $('#product_'+id).val();
        let purchase_id = $('#purchase_id').val();  
        let price = Number($(e).find('option:selected').data('price'));
       $.ajax({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               url: "{{route('get_puracher_details')}}",
               type: 'POST',
               data: {
                product_id: product_id,
                purchase_id: purchase_id
               },
               success: function(data) {
                var total = price * 1;
                $('#qty_' + id).val(1);
                $('#price_' + id).val(data.price);
                $('#desc_' + id).val(data.variant);
                $('#stock_' + id).val(dat.qty);
                $('#total_' + id).html(total);
               }
           }); 
      }
    
    function changePrice(e) {
        let id = $(e).attr('id').split('_')[1];
        let price = Number($('#price_' + id).val());
        let qty = Number($('#qty_' + id).val());
        if (!qty)
            qty = 1;
        var total = price * qty;
        $('#total_' + id).html(total);
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        $('#item_table').find('tbody>tr').each(function() {
            var id = $(this).attr('id').split('_')[1];
            total += Number($('#total_' + id).html());
        });
        $('#total').html(total);
        $('#total_input').val(total);
    }

    function addItemRow() {
        let itemstr=$('#product_1').html();
        let itedesc=$('#desc_1').html();
        let row = $('#item_table').find('tbody>tr').length;
        row++;
        let str = "<tr id='row_" + row + "'>";
        str += '<td><select class="form-control aiz-selectpicker"  onchange="changeProduct(this)" name="product[]" id="product_' + row + '" data-live-search="true" required>'+itemstr;
        str += '</select> </td>';
        str += '<td><input type="text" id="desc_' + row + '" name="desc[]" class="form-control" readonly></td>';
        str += ' <td><input type="number" id="qty_' + row + '" onchange="changePrice(this)" name="qty[]" class="form-control"></td>';
        str += '<td><input type="text" id="price_' + row + '" onchange="changePrice(this)" name="price[]" class="form-control" readonly></td>';
        str += '<td id="total_' + row + '"></td>';
        str += ' <td><a href="javascript:" onclick="removeItemRow(' + row + ')" class="btn btn-sm btn-danger"><i class="las la-minus"></i></a></td>';
        str += "</tr>";
        $('#item_table').find('tbody').append(str);
        $('.aiz-selectpicker').selectpicker();
        calculateTotal();
    }

    function removeItemRow(id) {
        if (confirm('Are you sure to Remove ? ') == true) {
            $('#row_' + id).remove();
            calculateTotal();
        }
    }
</script>

@endsection