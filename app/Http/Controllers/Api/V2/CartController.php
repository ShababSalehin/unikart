<?php

namespace App\Http\Controllers\Api\V2;
use App\Utility\NagadUtility;
use App\FlashDeal; 
use App\Order; 
use App\Models\Cart;
use App\Product;
use App\Models\ProductStock;
use App\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function summary($user_id)
    {
        $items = Cart::where('user_id', $user_id)->get();

        if ($items->isEmpty()) {
            return response()->json([
                'sub_total' => format_price(0.00),
                'tax' => format_price(0.00),
                'shipping_cost' => format_price(0.00),
                'discount' => format_price(0.00),
            	'coupon_discount' => format_price(0.00),
                'grand_total' => format_price(0.00),
                'grand_total_value' => 0.00,
                'coupon_code' => "",
                'coupon_applied' => false,
            ]);
        }

        $sum = 0.00;
        $subtotal = 0.00;
        $discount = 0.00;
    	$coupon_discount=0.0;
    	$shipping_cost=0;
        $grand_total=0;
        foreach ($items as $cartItem) {

            $product = Product::find($cartItem['product_id']);
            $subtotal += $product->unit_price * $cartItem['quantity'];
           //	$subtotal += $cartItem['price'] * $cartItem['quantity']; //added by alauddin

            if($cartItem['offer_status']==1){
                            
                $discount +=($cartItem['offer_discount_amount']+$cartItem['unicart_discount_amount']);
            }else{
                $discount += ($product->unit_price-$cartItem['price']) * $cartItem['quantity'];
            }
        
        	$coupon_discount +=$cartItem->discount;// added by alauddin        
        	$shipping_cost +=$cartItem->shipping_cost; //added by alauddin

            $item_sum = 0;
            $item_sum += ($cartItem->price + $cartItem->tax) * $cartItem->quantity;
            $item_sum += $cartItem->shipping_cost - $cartItem->discount;
        	//$item_sum += $cartItem->shipping_cost;
            $sum +=  $item_sum  ;   //// 'grand_total' => $request->g
        
        	
            
        }
    
    	 $grand_total=$subtotal+$shipping_cost-$discount-$coupon_discount;

        return response()->json([
            'sub_total' => format_price($subtotal),
            // 'tax' => format_price($items->sum('tax')),
            'discount' => format_price($discount),
        	'coupon_discount' => format_price($coupon_discount),
            'shipping_cost' => format_price($items->sum('shipping_cost')),
            'grand_total' => format_price($grand_total),
            'grand_total_value' => convert_price($grand_total),
            'coupon_code' => $items[0]->coupon_code,
            'coupon_applied' => $items[0]->coupon_applied == 1,
        ]);

    }

    public function getList($user_id)
    {
        $owner_ids = Cart::where('user_id', $user_id)->select('owner_id')->groupBy('owner_id')->pluck('owner_id')->toArray();
        $currency_symbol = currency_symbol();
        $shops = [];
        if (!empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = array();
                $shop_items_raw_data = Cart::where('user_id', $user_id)->where('owner_id', $owner_id)->get()->toArray();
                $shop_items_data = array();
                if (!empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {
                        $product = Product::where('id', $shop_items_raw_data_item["product_id"])->first();

                        $shop_items_data_item["id"] = intval($shop_items_raw_data_item["id"]) ;
                        $shop_items_data_item["owner_id"] =intval($shop_items_raw_data_item["owner_id"]) ;
                        $shop_items_data_item["user_id"] =intval($shop_items_raw_data_item["user_id"]) ;
                        $shop_items_data_item["product_id"] =intval($shop_items_raw_data_item["product_id"]) ;
                        $shop_items_data_item["product_name"] = $product->name ? $product->name : "";
                       
                        if($shop_items_raw_data_item['variation']!=null){
                            
                            $img_id = ProductStock::where('product_id',$shop_items_raw_data_item["product_id"])
                            ->where('variant',$shop_items_raw_data_item['variation'])->select('image')->pluck('image');
                          
                            if(!empty($img_id[0])){
                                $shop_items_data_item["product_thumbnail_image"] = api_asset($img_id[0]);
                            }else{
                                $shop_items_data_item["product_thumbnail_image"] = api_asset($product->thumbnail_img);
                            }
                        }else{

                            $shop_items_data_item["product_thumbnail_image"] = api_asset($product->thumbnail_img);
                        }
                        
                        $shop_items_data_item["variation"] = $shop_items_raw_data_item["variation"];
                        $shop_items_data_item["price"] =round($shop_items_raw_data_item["price"]);
                    	$shop_items_data_item["unit_price"] =(double) $shop_items_raw_data_item["unit_price"];
                        $shop_items_data_item["currency_symbol"] = $currency_symbol;
                        $shop_items_data_item["tax"] =(double) $shop_items_raw_data_item["tax"];
                        $shop_items_data_item["shipping_cost"] =(double) $shop_items_raw_data_item["shipping_cost"];
                        $shop_items_data_item["quantity"] =intval($shop_items_raw_data_item["quantity"]) ;
                        $shop_items_data_item["lower_limit"] = intval($product->min_qty) ;
                        //$shop_items_data_item["upper_limit"] = intval($product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first()->qty) ;
                    	$shop_items_data_item["upper_limit"] = intval($product->max_qty) ;

                        $shop_items_data[] = $shop_items_data_item;

                    }
                }


                $shop_data = Shop::where('user_id', $owner_id)->first();
                if ($shop_data) {
                    $shop['name'] = $shop_data->name;
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                } else {
                    $shop['name'] = "Inhouse";
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                }
                $shops[] = $shop;
            
               
            }
           
        }

        //dd($shops);

        return response()->json($shops);
    }


    public function temp_getList($user_id)
    {
        $owner_ids = Cart::where('temp_user_id', $user_id)->select('owner_id')->groupBy('owner_id')->pluck('owner_id')->toArray();
        $currency_symbol = currency_symbol();
        $shops = [];
        if (!empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = array();
                $shop_items_raw_data = Cart::where('temp_user_id', $user_id)->where('owner_id', $owner_id)->get()->toArray();
                $shop_items_data = array();
                if (!empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {
                        $product = Product::where('id', $shop_items_raw_data_item["product_id"])->first();

                        $shop_items_data_item["id"] = intval($shop_items_raw_data_item["id"]) ;
                        $shop_items_data_item["owner_id"] =intval($shop_items_raw_data_item["owner_id"]) ;
                        $shop_items_data_item["temp_user_id"] =$shop_items_raw_data_item["temp_user_id"] ;
                        $shop_items_data_item["product_id"] =intval($shop_items_raw_data_item["product_id"]) ;
                        $shop_items_data_item["product_name"] = $product->name;
                       
                        if($shop_items_raw_data_item['variation']!=null){
                            
                            $img_id = ProductStock::where('product_id',$shop_items_raw_data_item["product_id"])
                            ->where('variant',$shop_items_raw_data_item['variation'])->select('image')->pluck('image');
                          
                            if(!empty($img_id[0])){
                                $shop_items_data_item["product_thumbnail_image"] = api_asset($img_id[0]);
                            }else{
                                $shop_items_data_item["product_thumbnail_image"] = api_asset($product->thumbnail_img);
                            }
                        }else{

                            $shop_items_data_item["product_thumbnail_image"] = api_asset($product->thumbnail_img);
                        }
                        
                        $shop_items_data_item["variation"] = $shop_items_raw_data_item["variation"];
                        $shop_items_data_item["price"] =round($shop_items_raw_data_item["price"]);
                    	$shop_items_data_item["unit_price"] =(double) $shop_items_raw_data_item["unit_price"];
                        $shop_items_data_item["currency_symbol"] = $currency_symbol;
                        $shop_items_data_item["tax"] =(double) $shop_items_raw_data_item["tax"];
                        $shop_items_data_item["shipping_cost"] =(double) $shop_items_raw_data_item["shipping_cost"];
                        $shop_items_data_item["quantity"] =intval($shop_items_raw_data_item["quantity"]) ;
                        $shop_items_data_item["lower_limit"] = intval($product->min_qty) ;
                        //$shop_items_data_item["upper_limit"] = intval($product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first()->qty) ;
                    	$shop_items_data_item["upper_limit"] = intval($product->max_qty) ;

                        $shop_items_data[] = $shop_items_data_item;

                    }
                }


                $shop_data = Shop::where('user_id', $owner_id)->first();
                if ($shop_data) {
                    $shop['name'] = $shop_data->name;
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                } else {
                    $shop['name'] = "Inhouse";
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                }
                $shops[] = $shop;
            
               
            }
           
        }

        return response()->json($shops);
    }



	public function add(Request $request)
    {
        $product = Product::findOrFail($request->id);
    
    
    	$existing_cart_info = Cart::where('product_id', $request->id)
            ->where('user_id', $request->user_id)
            ->get();
        
        if(!empty($existing_cart_info) && isset($existing_cart_info[0]['quantity'])){
            $total_qty=$existing_cart_info[0]['quantity']+$request->quantity;
            if($total_qty>$product->max_qty){
                return response()->json(['result' => false, 'message' => "Maximum purchase limit over"], 200);
            }
        }    

        $variant = $request->variant;   
        $confirm_status=0;
        $offer_status=0;
        $tax = 0;
    	$offer_discount_amount=0;
		$unicart_discount_amount=0;

    	 $price = $product->unit_price; //added by alauddin
    	 $unit_price=$product->unit_price; //added by alauddin 	

        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
            //Start added by alauddin 
            $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->where('flash_deals.campaign_type',"First Order") 
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

            if(!empty($flashDeal)){
                if($flashDeal->campaign_type=="First Order"){
                    $cart_check = Cart::where('user_id', $request->user_id)->where('offer_status',1)->get();
                    if(count($cart_check) == 0){
                        $order_check = Order::where('user_id',$request->user_id)
                                       ->where('delivery_status','!=','cancelled')
                                       ->get();
                        if (count($order_check) == 0){
                            $total=0;
                            $confirm_check=0;
                            $coupon_discount_check=0; //added by alauddin
                            $total=$product->unit_price*$request->quantity;
                            $carts = Cart::where('user_id',$request->user_id)->get();
                            if(!empty($carts)){
                                foreach ($carts as $key => $cartItem){ 
                                    if($cartItem['confirm_status']==1){
                                        $confirm_check=1;
                                    }                                    
                                    if($cartItem['discount']>0){
                                        $coupon_discount_check=1;
                                    }                                    
                                    $total = $total + ($cartItem['price'] * $cartItem['quantity']);
                                }

                                if($coupon_discount_check==0){

                                    if($total>=$flashDeal->minimum_amount){

                                        if($confirm_check==0){
                                            $confirm_status=1;
                                            $offer_status=1;                                            
                                            $discount_applicable = true;
                                        }else{

                                        }
                                    }
                                }else{
                                    $confirm_status=0;
                                    $offer_status=0;
                                    
                                }

                            }

                        }
                                                     
                    }    
                }    
            }else{
                $discount_applicable = true;
            }    

            //end added by alauddin 

        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
             //Start added by alauddin 
             $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
             ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
             ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
             ->where('flash_deal_products.product_id',$product->id)  
             ->where('flash_deals.campaign_type',"First Order") 
             ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

            if(!empty($flashDeal)){
                if($flashDeal->campaign_type=="First Order"){
                    $cart_check = Cart::where('user_id', $request->user_id)->where('offer_status',1)->get();
                    if(count($cart_check) == 0){
                        $order_check = Order::where('user_id',$request->user_id)
                                        ->where('delivery_status','!=','cancelled')
                                        ->get();
                        if (count($order_check) == 0){
                            $total=0;
                            $confirm_check=0;
                            $coupon_discount_check=0; //added by alauddin
                            $total=$product->unit_price*$request->quantity;
                            $carts = Cart::where('user_id',$request->user_id)->get();
                            if(!empty($carts)){
                                foreach ($carts as $key => $cartItem){ 
                                    if($cartItem['confirm_status']==1){
                                        $confirm_check=1;
                                    }                                    
                                    if($cartItem['discount']>0){
                                        $coupon_discount_check=1;
                                    }                                    
                                    $total = $total + ($cartItem['price'] * $cartItem['quantity']);
                                }

                                if($coupon_discount_check==0){

                                    if($total>=$flashDeal->minimum_amount){

                                        if($confirm_check==0){
                                            $confirm_status=1;
                                            $offer_status=1;                                            
                                            $discount_applicable = true;
                                        }else{

                                        }
                                    }
                                }else{
                                    $confirm_status=0;
                                    $offer_status=0;
                                    
                                }

                            }

                        }
                                                    
                    }    
                }    
            }

      
       }else{
            //Start added by alauddin 
            $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
            ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
            ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
            ->where('flash_deal_products.product_id',$product->id)  
            ->where('flash_deals.campaign_type',"First Order") 
            ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

           if(!empty($flashDeal)){
               if($flashDeal->campaign_type=="First Order"){
                   $cart_check = Cart::where('user_id', $request->user_id)->where('offer_status',1)->get();
                   if(count($cart_check) == 0){
                       $order_check = Order::where('user_id',$request->user_id)
                                       ->where('delivery_status','!=','cancelled')
                                       ->get();
                       if (count($order_check) == 0){
                           $total=0;
                           $confirm_check=0;
                           $coupon_discount_check=0; //added by alauddin
                           $total=$product->unit_price*$request->quantity;
                           $carts = Cart::where('user_id',$request->user_id)->get();
                           if(!empty($carts)){
                               foreach ($carts as $key => $cartItem){ 
                                   if($cartItem['confirm_status']==1){
                                       $confirm_check=1;
                                   }                                    
                                   if($cartItem['discount']>0){
                                       $coupon_discount_check=1;
                                   }                                    
                                   $total = $total + ($cartItem['price'] * $cartItem['quantity']);
                               }

                               if($coupon_discount_check==0){

                                   if($total>=$flashDeal->minimum_amount){

                                       if($confirm_check==0){
                                           $confirm_status=1;
                                           $offer_status=1;                                            
                                           $discount_applicable = true;
                                       }else{

                                       }
                                   }
                               }else{
                                   $confirm_status=0;
                                   $offer_status=0;
                                   
                               }

                           }

                       }
                                                   
                   }    
               }    
           }
       }

		$app_discount_applicable_status=1;

        if ($discount_applicable){
            if( isset($flashDeal->campaign_type) && ($flashDeal->campaign_type=="First Order")){
                    if($offer_status==1){
                    	$app_discount_applicable_status=0;
                        if($flashDeal->discount_type == 'percent'){
                            $price -= ($price*$flashDeal->discount)/100;
                            $offer_discount_amount= ($price*$flashDeal->discount)/100;
                            $price -= ($product->unit_price*$flashDeal->unikart_discount)/100;
                            $unicart_discount_amount=($product->unit_price*$flashDeal->unikart_discount)/100;
                        }
                        elseif($flashDeal->discount_type == 'amount'){
                            $price -= $flashDeal->discount;
                            $offer_discount_amount=$flashDeal->discount;
                            $price -= $flashDeal->unikart_discount;
                            $unicart_discount_amount=$flashDeal->unikart_discount;
                        }
                    }else{
                        if($product->discount_type == 'percent'){
                            $price -= ($price*$product->discount)/100;
                            $offer_discount_amount=0;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->discount;
                            $offer_discount_amount=0;
                        }
                    
                    //added by alauddin start
                        if($product->discount_type == 'percent'){
                            $price -= ($product->unit_price*$product->unikart_discount)/100;
                            $unicart_discount_amount=($product->unit_price*$product->unikart_discount)/100;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->unikart_discount;
                            $unicart_discount_amount=$product->unikart_discount;
                        }
                        
                //added by alauddin end
                    }    
            }else{
                        if($product->discount_type == 'percent'){
                            $price -= ($price*$product->discount)/100;
                            $offer_discount_amount=0;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->discount;
                            $offer_discount_amount=0;
                        }
                    
                    //added by alauddin start
                        if($product->discount_type == 'percent'){
                            $price -= ($product->unit_price*$product->unikart_discount)/100;
                            $unicart_discount_amount=($product->unit_price*$product->unikart_discount)/100;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->unikart_discount;
                            $unicart_discount_amount=$product->unikart_discount;
                        }
                        
                //added by alauddin end
            }    
        }

        //Start App discount Calculation

    		$appdiscount=0;
			if($app_discount_applicable_status==1){
            	if ($product->app_discount_type == 'percent') {                
                	$appdiscount=($price * $product->app_price) / 100; //added by alauddin
            	} elseif ($product->app_discount_type == 'amount') {            
                	$appdiscount= $product->app_price; //added by alauddin
            	}
            }else{
            	$appdiscount=0;
            }
            	
            $price -=$appdiscount;

        

        //End App discount Calculation

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        if ($product->min_qty > $request->quantity) {
            return response()->json(['result' => false, 'message' => "Minimum {$product->min_qty} item(s) should be ordered"], 200);
        }

        
    	$stock = 5; //added by alauddin

        $variant_string = $variant != null && $variant != "" ? "for ($variant)" : "";
        if($stock < $request->quantity){
            if($stock == 0){
                return response()->json(['result' => false, 'message' => "Stock out"], 200);
            } else {
                return response()->json(['result' => false, 'message' => "Only {$stock} item(s) are available {$variant_string}"], 200);
            }
        }
    
    
        Cart::updateOrCreate([
            'user_id' => $request->user_id,
            'owner_id' => $product->user_id,
            'product_id' => $request->id,
            'variation' => $variant
        ], [
            'price' => $price,
            'unit_price'=>$unit_price,
            'offer_status'=>$offer_status,
            'confirm_status'=>$confirm_status,
            'offer_discount_amount' =>$offer_discount_amount,
            'unicart_discount_amount' =>$unicart_discount_amount,
            'app_discount_amount' =>$appdiscount,
            'tax' => $tax,
            'shipping_cost' => 0,
            'quantity' => DB::raw("quantity + $request->quantity")
        ]);

        if(\App\Utility\NagadUtility::create_balance_reference($request->cost_matrix) == false){
            return response()->json(['result' => false, 'message' => 'Cost matrix error' ]);
        }
		
        	return response()->json([
            	'result' => true,
            	'message' => 'Product added to cart successfully'
        	]);
       
    }








    public function add_25_09_2022(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $variant = $request->variant;
        $tax = 0;
        $show_price = 0;
        if($product->app_price > 0){
            $show_price = $product->app_price;
        }else{
            $show_price = $product->unit_price;
        }
        if ($variant == '')
            $price = $show_price;
        else {
            $product_stock = $product->stocks->where('variant', $variant)->first();
            $price = $product_stock->price;
        }

        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        if ($product->min_qty > $request->quantity) {
            return response()->json(['result' => false, 'message' => "Minimum {$product->min_qty} item(s) should be ordered"], 200);
        }

        $stock = $product->stocks->where('variant', $variant)->first()->qty;

        $variant_string = $variant != null && $variant != "" ? "for ($variant)" : "";
        if ($stock < $request->quantity) {
            if ($stock == 0) {
                return response()->json(['result' => false, 'message' => "Stock out"], 200);
            } else {
                return response()->json(['result' => false, 'message' => "Only {$stock} item(s) are available {$variant_string}"], 200);
            }
        }

        Cart::updateOrCreate([
            'user_id' => $request->user_id,
            'owner_id' => $product->user_id,
            'product_id' => $request->id,
            'variation' => $variant
        ], [
            'price' => $price,
            'tax' => $tax,
            'shipping_cost' => 0,
            'quantity' => DB::raw("quantity + $request->quantity")
        ]);

        if(\App\Utility\NagadUtility::create_balance_reference($request->cost_matrix) == false){
            return response()->json(['result' => false, 'message' => 'Cost matrix error' ]);
        }

        return response()->json([
            'result' => true,
            'message' => 'Product added to cart successfully'
        ]);
    }

    public function changeQuantity(Request $request)
    {
        $cart = Cart::find($request->id);
        if ($cart != null) {

            if ($cart->product->stocks->where('variant', $cart->variation)->first()->qty >= $request->quantity) {
                $cart->update([
                    'quantity' => $request->quantity
                ]);

                return response()->json(['result' => true, 'message' => 'Cart updated'], 200);
            } else {
                return response()->json(['result' => false, 'message' => 'Maximum available quantity reached'], 200);
            }
        }

        return response()->json(['result' => false, 'message' => 'Something went wrong'], 200);
    }

    public function process(Request $request)
    {
        $cart_ids = explode(",", $request->cart_ids);
        $cart_quantities = explode(",", $request->cart_quantities);

        if (!empty($cart_ids)) {
            $i = 0;
            foreach ($cart_ids as $cart_id) {
                $cart_item = Cart::where('id', $cart_id)->first();
                $product = Product::where('id', $cart_item->product_id)->first();

                if ($product->min_qty > $cart_quantities[$i]) {
                    return response()->json(['result' => false, 'message' => "Minimum {$product->min_qty} item(s) should be ordered for {$product->name}"], 200);
                }

                $stock = $cart_item->product->stocks->where('variant', $cart_item->variation)->first()->qty;
                $variant_string = $cart_item->variation != null && $cart_item->variation != "" ? " ($cart_item->variation)" : "";
                if ($stock >= $cart_quantities[$i]) {
                    $cart_item->update([
                        'quantity' => $cart_quantities[$i]
                    ]);

                } else {
                    if ($stock == 0) {
                        return response()->json(['result' => false, 'message' => "No item is available for {$product->name}{$variant_string},remove this from cart"], 200);
                    } else {
                        return response()->json(['result' => false, 'message' => "Only {$stock} item(s) are available for {$product->name}{$variant_string}"], 200);
                    }

                }

                $i++;
            }

            return response()->json(['result' => true, 'message' => 'Cart updated'], 200);

        } else {
            return response()->json(['result' => false, 'message' => 'Cart is empty'], 200);
        }


    }



    public function add_for_guest(Request $request)
    {
        $product = Product::findOrFail($request->id);
        
    	$existing_cart_info = Cart::where('product_id', $request->id)
            ->where('temp_user_id', $request->temp_user_id)
            ->get();
            
        
        if(!empty($existing_cart_info) && isset($existing_cart_info[0]['quantity'])){
            $total_qty=$existing_cart_info[0]['quantity']+$request->quantity;
            if($total_qty>$product->max_qty){
                return response()->json(['result' => false, 'message' => "Maximum purchase limit over"], 200);
            }
        }    

        $variant = $request->variant;   
        $confirm_status=0;
        $offer_status=0;
        $tax = 0;
    	$offer_discount_amount=0;
		$unicart_discount_amount=0;

    	 $price = $product->unit_price; //added by alauddin
    	 $unit_price = $product->unit_price; //added by alauddin 	


        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
            //Start added by alauddin 
            $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->where('flash_deals.campaign_type',"First Order") 
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

            if(!empty($flashDeal)){
                if($flashDeal->campaign_type=="First Order"){
                    
                }    
            }else{
                $discount_applicable = true;
            }    

            //end added by alauddin 

            
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
             //Start added by alauddin 
             $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
             ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
             ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
             ->where('flash_deal_products.product_id',$product->id)  
             ->where('flash_deals.campaign_type',"First Order") 
             ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

            if(!empty($flashDeal)){
                if($flashDeal->campaign_type=="First Order"){
                    
                }    
            }

      
       }else{
            //Start added by alauddin 
            $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
            ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
            ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
            ->where('flash_deal_products.product_id',$product->id)  
            ->where('flash_deals.campaign_type',"First Order") 
            ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

           if(!empty($flashDeal)){
               if($flashDeal->campaign_type=="First Order"){
                   
               }    
           }
       }

		$app_discount_applicable_status=1;

        if ($discount_applicable) {


            if( isset($flashDeal->campaign_type) && ($flashDeal->campaign_type=="First Order")){
                    if($offer_status==1){
                    	$app_discount_applicable_status=0;
                        if($flashDeal->discount_type == 'percent'){
                            $price -= ($price*$flashDeal->discount)/100;
                            $offer_discount_amount= ($price*$flashDeal->discount)/100;
                            $price -= ($product->unit_price*$flashDeal->unikart_discount)/100;
                            $unicart_discount_amount=($product->unit_price*$flashDeal->unikart_discount)/100;

                        }
                        elseif($flashDeal->discount_type == 'amount'){
                            $price -= $flashDeal->discount;
                            $offer_discount_amount=$flashDeal->discount;
                            $price -= $flashDeal->unikart_discount;
                            $unicart_discount_amount=$flashDeal->unikart_discount;
                        }
                    }else{
                        if($product->discount_type == 'percent'){
                            $price -= ($price*$product->discount)/100;
                            $offer_discount_amount=0;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->discount;
                            $offer_discount_amount=0;
                        }
                    
                    //added by alauddin start
                        if($product->discount_type == 'percent'){
                            $price -= ($product->unit_price*$product->unikart_discount)/100;
                            $unicart_discount_amount=($product->unit_price*$product->unikart_discount)/100;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->unikart_discount;
                            $unicart_discount_amount=$product->unikart_discount;
                        }
                        
                //added by alauddin end
                    }    


                    

            }else{
                        if($product->discount_type == 'percent'){
                            $price -= ($price*$product->discount)/100;
                            $offer_discount_amount=0;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->discount;
                            $offer_discount_amount=0;
                        }
                    
                    //added by alauddin start
                        if($product->discount_type == 'percent'){
                            $price -= ($product->unit_price*$product->unikart_discount)/100;
                            $unicart_discount_amount=($product->unit_price*$product->unikart_discount)/100;
                        }
                        elseif($product->discount_type == 'amount'){
                            $price -= $product->unikart_discount;
                            $unicart_discount_amount=$product->unikart_discount;
                        }
                        
                //added by alauddin end
            }    


        }


        //Start App discount Calculation

    		$appdiscount=0;
        
			if($app_discount_applicable_status==1){
            	if ($product->app_discount_type == 'percent') {                
                	$appdiscount=($price * $product->app_price) / 100; //added by alauddin
            	} elseif ($product->app_discount_type == 'amount') {            
                	$appdiscount= $product->app_price; //added by alauddin
            	}
            }else{
            	$appdiscount=0;
            }
            	
            $price -=$appdiscount;

        

        //End App discount Calculation



        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        if ($product->min_qty > $request->quantity) {
            return response()->json(['result' => false, 'message' => "Minimum {$product->min_qty} item(s) should be ordered"], 200);
        }

        //$stock = $product->stocks->where('variant', $variant)->first()->qty;
    	$stock = 5; //added by alauddin

        $variant_string = $variant != null && $variant != "" ? "for ($variant)" : "";
        if($stock < $request->quantity){
            if($stock == 0){
                return response()->json(['result' => false, 'message' => "Stock out"], 200);
            } else {
                return response()->json(['result' => false, 'message' => "Only {$stock} item(s) are available {$variant_string}"], 200);
            }
        }
    
    
    
        Cart::updateOrCreate([
            'user_id' => null,
            'temp_user_id' => $request->temp_user_id,
            'owner_id' => $product->user_id,
            'product_id' => $request->id,
            'variation' => $variant
        ], [
            'price' => $price,
            'unit_price'=>$unit_price,
            'offer_status'=>$offer_status,
            'confirm_status'=>$confirm_status,
            'offer_discount_amount' =>$offer_discount_amount,
            'unicart_discount_amount' =>$unicart_discount_amount,
            'app_discount_amount' =>$appdiscount,
            'tax' => $tax,
            'shipping_cost' => 0,
            'quantity' => DB::raw("quantity + $request->quantity")
        ]);

        if(NagadUtility::create_balance_reference($request->cost_matrix) == false){
            return response()->json(['result' => false, 'message' => 'Cost matrix error' ]);
        }
		
        	return response()->json([
            	'result' => true,
            	'message' => 'Product added to cart successfully without login'
        	]);
       
    }


    public function destroy($id)
    {
        Cart::destroy($id);
        return response()->json(['result' => true, 'message' => 'Product is successfully removed from your cart'], 200);
    }

    public function temp_user_destroy(Request $request){

        $temp_user = Cart::findOrfail($request->cart_id);
        $temp_user->delete();

        return response()->json(['result' => true,
         'message' => 'Product is successfully removed from your cart'], 200);
    }
}
