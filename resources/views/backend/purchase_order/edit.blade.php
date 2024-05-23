@extends('backend.layouts.app')
<style>
.product_select {
  min-width: 200px !important;
  max-width: 350px !important;
}
</style>
@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Edit Purchase')}}</h5>
</div>
<div class="">
    <div class="">
        <form class="form form-horizontal mar-top" action="{{route('puracher_edit_store')}}" method="POST" enctype="multipart/form-data" id="choice_form">
            @csrf
            <input type="hidden" name="added_by" value="admin">
            <input type="hidden" name="po_id" value="{{$purchase->id}}">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Supplier Information')}}</h5>
                </div>
                <div class="card-body">

                <div class="col-md-6 pull-left">
                        <label>{{translate('Supplier Name')}} <span class="text-danger">*</span></label>

                        <select class="form-control aiz-selectpicker" name="supplier_id" id="supplier_id" data-live-search="true" required>
                            @foreach ($supplier as $supp)
                            <option <?php if($purchase->supplier_id == $supp->id) echo 'selected';?> value="{{ $supp->id }}">{{ $supp->name }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-6 pull-left">
                        <label>{{translate('Purchase Date')}} <span class="text-danger">*</span></label>

                        <input type="date" class="form-control" name="purchase_date" placeholder="{{ translate('Purchase Date') }}" value="{{$purchase->date}}" onchange="update_sku()" required>

                    </div>
                    <div class="col-md-6 pull-left">
                        <label>{{translate('Purchase No')}} <span class="text-danger">*</span></label>

                        <input type="text" readonly class="form-control" name="purchase_no" placeholder="{{ translate('Purchase No') }}" value="{{$purchase->purchase_no}}" onchange="update_sku()" required>

                    </div>

                    <div class="col-md-6 pull-left">
                        <label>{{translate('Batch No')}} <span class="text-danger">*</span></label>
                        <input type="text"  class="form-control" id="batch_no" name="batch_no" placeholder="{{ translate('Batch No') }}" value="{{$purchase->purchase_no}}"  required>

                    </div>

                    <div class="col-md-6 pull-left">
                        <label>{{translate('Remarks')}} <span class="text-danger">*</span></label>

                        <input type="text" class="form-control" name="remarks" placeholder="{{ translate('Remarks') }}" value="{{$purchase->remarks}}" onchange="update_sku()" required>

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
                                <th style="width:30%;">Product</th>
                                <th style="width:20%;">Description</th>
                                <th style="width:10%;">Stock</th>
                                <th style="width:10%;">Quantity</th>
                                <th style="width:15%;">Unit Price</th>
                                <th style="width:10%;">Total</th>
                                <th style="width:5%;"><a href="javascript:" onclick="addItemRow()" class="btn btn-sm btn-primary"><i class="las la-plus"></i></a></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                            $gtotal =0;
                            @endphp

                            @if(count(array($purchase_item)) > 0)
                            @foreach ($purchase_item as $key=>$pro_row)
                            @php 
                            $key++;
                            $gtotal += $pro_row->qty*$pro_row->price;
                            @endphp
                            <tr id="row_{{$key}}">
                                <td>
                                    <select class="form-control aiz-selectpicker product_select" onchange="changeProduct(this)" name="product[]" id="product_{{$key}}" data-live-search="true">
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option <?php if($pro_row->product_id == $product->id) echo 'selected';?> data-qty="{{ $product->current_stock }}" data-price="{{ $product->purchase_price }}" value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" id="desc_{{$key}}" name="desc[]" value="{{$pro_row->desc}}" class="form-control">
                                </td>
                                <td>
                                    <input disabled type="text" id="stock_{{$key}}" name="stock[]"  class="form-control">
                                </td>
                                <td>
                                    <input type="number" id="qty_{{$key}}" onchange="changePrice(this)" name="qty[]" value="{{$pro_row->qty}}" class="form-control">
                                    <input type="hidden"  name="oldqty[]" value="{{$pro_row->qty}}" class="form-control">
                                </td>
                                <td>
                                    <input type="text" id="price_{{$key}}" onchange="changePrice(this)" name="price[]" value="{{$pro_row->price}}"  class="form-control">
                                </td>
                                <td id="total_{{$key}}">
                                    {{$pro_row->qty*$pro_row->price}}

                                </td>
                                <td>
                                    <a href="javascript:" onclick="removeItemRow({{$key}})" class="btn btn-sm btn-danger"><i class="las la-minus"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr id="row_1">
                                <td>
                                    <select class="form-control aiz-selectpicker product_select" onchange="changeProduct(this)" name="product[]" id="product_1" data-live-search="true" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option data-qty="{{ $product->current_stock }}" data-price="{{ $product->purchase_price }}" value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" id="desc_1" name="desc[]" class="form-control">
                                </td>
                                <td>
                                    <input disabled type="text" id="stock_1" name="stock[]" class="form-control">
                                </td>
                                <td>
                                    <input type="number" id="qty_1" onchange="changePrice(this)" name="qty[]" class="form-control">
                                </td>
                                <td>
                                    <input type="text" id="price_1" onchange="changePrice(this)" name="price[]" class="form-control">
                                </td>
                                <td id="total_1">
                                
                            </td>
                                <td>
                                    <a href="javascript:" onclick="removeItemRow(1)" class="btn btn-sm btn-danger"><i class="las la-minus"></i></a>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" style="text-align:right">Total
                                <input type="hidden" name="total" id="total_input" value="{{$gtotal}}">
                                </th>
                                <th id="total">{{$gtotal}}</th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>

            <div class="mb-3 text-right">
                <button type="submit" name="button" class="btn btn-primary">{{ translate('Save Purchase') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection

@section('script')

<script type="text/javascript">
    function changeProduct(e){
        var id = $(e).attr('id').split('_')[1];
        var product_id = $('#product_'+id).val();
        
        var price = Number($(e).find('option:selected').data('price'));
        var qty = Number($('#qty_' + id).val());
       
       $.ajax({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               url: "{{route('purchase_orders.get_puracher_product')}}",
               type: 'POST',
               data: {
                product_id: product_id,
                wearhouse_id: wearhouse_id
               },
               //dataType: 'html',
               success: function(data) {
                
                var total = price * 1;
        $('#qty_' + id).val(qty);
        $('#price_' + id).val(price);
        $('#stock_' + id).val(data.qty);
        $('#total_' + id).html(total);
                   
               }
           }); 
       
      }
    // function changeProduct(e) {
    //     var id = $(e).attr('id').split('_')[1];
    //     var price = Number($(e).find('option:selected').data('price'));
    //     var stock = $(e).find('option:selected').data('qty');
    //     var qty = Number($('#qty_' + id).val());
    //     if (!qty)
    //         qty = 1;
    //     var total = price * qty;
    //     $('#qty_' + id).val(qty);
    //     $('#price_' + id).val(price);
    //     $('#stock_' + id).val(stock);
    //     $('#total_' + id).html(total);
    //     calculateTotal();
    // }

    function changePrice(e) {
        var id = $(e).attr('id').split('_')[1];
        var price = Number($('#price_' + id).val());
        var qty = Number($('#qty_' + id).val());
        if (!qty)
            qty = 1;
        var total = price * qty;
        $('#total_' + id).html(total);
        calculateTotal();
    }

    function calculateTotal() {
        var total = 0;
        $('#item_table').find('tbody>tr').each(function() {
            var id = $(this).attr('id').split('_')[1];
            total += Number($('#total_' + id).html());
        });
        $('#total').html(total);
        $('#total_input').val(total);
    }

    function addItemRow() {
        var row = $('#item_table').find('tbody>tr').length;
        row++;
        var str = "<tr id='row_" + row + "'>";
        str += '<td><select class="form-control aiz-selectpicker"  onchange="changeProduct(this)" name="product[]" id="product_' + row + '" data-live-search="true" required><option value="">Select Product</option>';
        @foreach($products as $product)
        str += '<option data-qty="{{ $product->current_stock }}" data-price="{{ $product->purchase_price }}" value="{{ $product->id }}">{{ $product->name }}</option>';
        @endforeach
        str += '</select> </td>';
        str += '<td> <input type="text" id="desc_' + row + '" name="desc[]" class="form-control"> </td>';
        str += '<td><input disabled type="text" id="stock_' + row + '" name="stock[]" class="form-control"></td>';
        str += ' <td><input type="number" id="qty_' + row + '" onchange="changePrice(this)" name="qty[]" class="form-control"></td>';
        str += '<td><input type="text" id="price_' + row + '" onchange="changePrice(this)" name="price[]" class="form-control"></td>';
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