<div class="modal-body p-4 added-to-cart">
    <div class="text-center text-success mb-4">
        <i class="las la-check-circle la-3x"></i>
        <h3>{{ translate('Item added to your cart!')}}</h3>
    </div>
    <div class="media mb-4">
        <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($product->thumbnail_img) }}" class="mr-3 lazyload size-100px img-fit rounded" alt="Product Image">
        <div class="media-body pt-3 text-left">
            <h6 class="fw-600">
                {{  $product->getTranslation('name')  }}
            </h6>
            <div class="row mt-3">
                <div class="col-sm-2 opacity-60">
                    <div>{{ translate('Price')}}:</div>
                </div>
                <div class="col-sm-10">
                    <div class="h6 text-primary">
                        <strong>
                            {{ single_price(($data['price'] + $data['tax']) * $data['quantity']) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center">
        <button class="btn btn-outline-primary mb-3 mb-sm-0" data-dismiss="modal">{{ translate('Back to shopping')}}</button>
        <a href="{{ route('cart') }}" class="btn btn-primary mb-3 mb-sm-0">{{ translate('Proceed to Checkout')}}</a>
    </div>
</div>
                        
  <script type = "text/javascript">
        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
        	event	: "add_to_cart",
        	ecommerce: {
            	items: [{
                    item_name 	: "{{$product->name}}", // Name or ID is required.
                    item_id   	: {{$product->id}},
                    price     	: "{{$product->unit_price}}",
   	                item_brand	: "{{$product->brand->name ?? ""}}",
                    item_category : "{{$product->category->name ?? ""}}",
                    item_category2: "",
                    item_category3: "",
                    item_category4: "",
                    item_variant  : "",
                    item_list_name: "",  // If associated with a list selection.
                    item_list_id  : "",  // If associated with a list selection.
                    index     	: 0,  // If associated with a list selection.
                    quantity  	: 1,
            	}]
        	}
        });

</script>
