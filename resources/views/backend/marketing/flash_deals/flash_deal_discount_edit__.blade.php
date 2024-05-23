@if(count($product_ids) > 0)
<table class="table table-bordered aiz-table">
    <thead>
      <tr>
        <td width="50%">
            <span>{{translate('Product')}}</span>
        </td>
        <td data-breakpoints="lg" width="20%">
            <span>{{translate('Base Price')}}</span>
        </td>
        <td data-breakpoints="lg" width="20%">
            <span>{{translate('Discount')}}</span>
        </td>
        <td data-breakpoints="lg" width="10%">
            <span>{{translate('Discount Type')}}</span>
        </td>
      </tr>
    </thead>
    <tbody>
        @foreach ($product_ids as $key => $id)
            @php
            $test='';
              $product = \App\Product::findOrFail($id);
              $flash_deal_product = \App\FlashDealProduct::where('flash_deal_id', $flash_deal_id)->where('product_id', $id)->first();
            @endphp
            <tr>
                <td>
                  <div class="form-group row">
                      <div class="col-auto">
                          <img src="{{ uploaded_asset($product->thumbnail_img)}}" class="size-60px img-fit" >
                      </div>
                      <div class="col">
                          <span>{{  $product->getTranslation('name')  }}</span>
                      </div>
                  </div>
                </td>
                <td>
                    <span>{{ $product->unit_price }}</span>
                </td>
                <td>
                <?php if(isset($flash_deal_product->discount_type)){ ?>
                    <input type="number" lang="en" name="discount_{{ $id }}" value="{{ $flash_deal_product->discount }}" min="0" step="1" class="form-control" required>
                <?php }else{ ?> 
                    <input type="number" lang="en" name="discount_{{ $id }}" value="" min="0" step="1" class="form-control" required>
                <?php } ?>       
                </td>
                <?php if(isset($flash_deal_product->discount_type)){ ?>
                <td>
                    <select class="aiz-selectpicker" name="discount_type_{{ $id }}">
                        <option value="amount" <?php if($flash_deal_product->discount_type == 'amount') echo "selected";?> >{{ translate('Flat') }}</option>
                        <option value="percent" <?php if($flash_deal_product->discount_type == 'percent') echo "selected";?> >{{ translate('Percent') }}</option>
                    </select>
                </td>
                <?php }else{ ?>
                    <td>
                    <select class="aiz-selectpicker" name="discount_type_{{ $id }}">
                        <option value="amount" <?php //if($flash_deal_product->discount_type == 'amount') echo "selected";?> >{{ translate('Flat') }}</option>
                        <option value="percent" <?php //if($flash_deal_product->discount_type == 'percent') echo "selected";?> >{{ translate('Percent') }}</option>
                    </select>
                </td>
                <?php } ?>    
            </tr>
        @endforeach
    </tbody>
</table>
@endif