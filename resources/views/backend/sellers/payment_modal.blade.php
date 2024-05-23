@php
$totalpay =0;
$total_sale = \App\OrderDetail::where(['seller_id'=>$seller->user->id,'delivery_status'=>'delivered','refund_status'=>0])->sum('due_to_seller');
//dd($seller);
$total_payment = \App\Payment::where(['seller_id'=>$seller->user->id])->sum('amount');

$totalpay = $total_sale-$total_payment;

@endphp
<form action="{{ route('commissions.pay_to_seller') }}" method="POST">
    @csrf
    <input type="hidden" name="seller_id" value="{{ $seller->id }}">
    <div class="modal-header">
    	<h5 class="modal-title h6">{{translate('Pay to seller')}}</h5>
    	<button type="button" class="close" data-dismiss="modal">
    	</button>
    </div>
    <div class="modal-body">
      <table class="table table-striped table-bordered" >
          <tbody>
              <tr>
                  <!-- @if($seller->admin_to_pay >= 0)
                      <td>{{ translate('Due to seller') }}</td>
                      <td>{{ single_price($totalpay) }}</td>
                  @else
                      <td>{{ translate('Due to Seller') }}</td>
                      <td>{{ single_price(abs($totalpay)) }}</td>
                  @endif -->
                  <td>{{ translate('Due to Seller') }}</td>
                  <td>{{ single_price(abs($totalpay)) }}</td>
              </tr>
              @if ($seller->bank_payment_status == 1)
                  <tr>
                      <td>{{ translate('Bank Name') }}</td>
                      <td>{{ $seller->bank_name }}</td>
                  </tr>
                  <tr>
                      <td>{{ translate('Bank Account Name') }}</td>
                      <td>{{ $seller->bank_acc_name }}</td>
                  </tr>
                  <tr>
                      <td>{{ translate('Bank Account Number') }}</td>
                      <td>{{ $seller->bank_acc_no }}</td>
                  </tr>
                  <tr>
                      <td>{{ translate('Bank Branch Name') }}</td>
                      <td>{{ $seller->bank_routing_no }}</td>
                  </tr>
              @endif
          </tbody>
      </table>

      @if ($seller->admin_to_pay > 0)
          <div class="form-group row">
              <label class="col-md-3 col-from-label" for="amount">{{translate('Amount')}}</label>
              <div class="col-md-9">
                  <input type="number" lang="en" min="0" step="0.01" name="amount" id="amount" value="{{ $totalpay }}" class="form-control" required>
              </div>
          </div>

          <!-- <div class="form-group row">
              <label class="col-md-3 col-from-label" for="payment_option">{{translate('Payment Method')}}</label>
              <div class="col-md-9">
                  <select name="payment_option" id="payment_option" class="form-control aiz-selectpicker" required>
                      <option value="">{{translate('Select Payment Method')}}</option>
                      @if($seller->cash_on_delivery_status == 1)
                      //dd($seller->cash_on_delivery_status);
                          <option value="cash">{{translate('Cash')}}</option>
                      @endif
                      @if($seller->bank_payment_status == 1)
                          <option value="bank_payment">{{translate('Bank Payment')}}</option>
                      @endif
                  </select>
              </div>
          </div> -->
          <div class="form-group row" id="txn_div">
              <label class="col-md-3 col-from-label" for="txn_code">{{translate('Txn Code')}}</label>
              <div class="col-md-9">
                  <input type="text" name="txn_code" id="txn_code" class="form-control">
              </div>
          </div>
      @else
          <div class="form-group row">
              <label class="col-md-3 col-from-label" for="amount">{{translate('Amount')}}</label>
              <div class="col-md-9">
                  <input type="number" lang="en" min="0" step="0.01" name="amount" id="amount" value="{{ $totalpay }}" class="form-control" required>
              </div>
          </div>
          <div class="form-group row" id="txn_div">
              <label class="col-md-3 col-from-label" for="txn_code">{{translate('Txn Code')}}</label>
              <div class="col-md-9">
                  <input type="text" name="txn_code" id="txn_code" class="form-control">
              </div>
          </div>
      @endif
    </div>
    <div class="modal-footer">
      @if ($seller->admin_to_pay > 0)
          <button type="submit" class="btn btn-primary">{{translate('Pay')}}</button>
      @else
          <button type="submit" class="btn btn-primary">{{translate('Clear due')}}</button>
      @endif
      <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
    </div>
</form>

<script>
  $(document).ready(function(){
      $('#payment_option').on('change', function() {
        if ( this.value == 'bank_payment')
        {
          $("#txn_div").show();
        }
        else
        {
          $("#txn_div").hide();
        }
      });
      $("#txn_div").hide();
      AIZ.plugins.bootstrapSelect('refresh');
  });
</script>
