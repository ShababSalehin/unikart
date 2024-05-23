<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FlashDeal; //added by alauddin
use App\Order; //added by alauddin
use App\Product;
use App\SubSubCategory;
use App\Category;
use App\Cart;
use Auth;
use Session;
use App\Color;
use Cookie;

class CartController extends Controller
{
    public function index(Request $request)
    {
        //dd($cart->all());
        $categories = Category::all();
        if(auth()->user() != null) {
            $user_id = Auth::user()->id;
            if($request->session()->get('temp_user_id')) {
                Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
                        ->update(
                                [
                                    'user_id' => $user_id,
                                    'temp_user_id' => null
                                ]
                );

                Session::forget('temp_user_id');
            }
            $carts = Cart::where('user_id', $user_id)->get();
        	
        	//added by alauddin start
            $carts_offer_check =array();
            $carts_offer_check = Cart::where('user_id', $user_id)->where('offer_status',1)->get(); //added by alauddin
            
           
            if(count($carts_offer_check)==0){
                $order_check = Order::where('user_id', Auth::user()->id)
                ->where('delivery_status','!=','canceled')
                ->get();
                if(count($order_check) == 0){
                    $total=0;
                    $carts = Cart::where('user_id',Auth::user()->id)->get();
                    
                    if(!empty($carts)){
                        foreach ($carts as $key => $cartItem){ 
                            $total = $total + ($cartItem['price'] * $cartItem['quantity']);
                        }
                    }
                    foreach ($carts as $key => $cartItem){
                       
                        $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                        ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deal_products.product_id',$cartItem['product_id'])  
                        ->where('flash_deals.title',"New Customer Offer")     
                        ->select('flash_deals.title','flash_deals.minimum_amount','flash_deal_products.*')->first();
                        
                        if(!empty($flashDeal)){
                            
                            if($total>=$flashDeal->minimum_amount){
                               
                                $object = Cart::findOrFail($cartItem['id']);
                                
                                $object['offer_status']=1;
                                $object['offer_discount_amount']=$flashDeal->discount;
                                $object->save();
                                break;
                                
                            }
                        }
                    }
                	$carts = Cart::where('user_id', $user_id)->get();
                }
            }

            //added by alauddin end
        
        } else {
            $temp_user_id = $request->session()->get('temp_user_id');
            // $carts = Cart::where('temp_user_id', $temp_user_id)->get();
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [] ;
        }

        return view('frontend.view_cart', compact('categories', 'carts'));
    }

    public function showCartModal(Request $request)
    {
        $product = Product::find($request->id);
        return view('frontend.partials.addToCart', compact('product'));
    }

    public function showCartModalAuction(Request $request)
    {
        $product = Product::find($request->id);
        return view('auction.frontend.addToCartAuction', compact('product'));
    }

    public function addToCart(Request $request)
    {
        $product = Product::find($request->id);
        $carts = array();
        $data = array();
    	$flashDeal=array(); //added by alauddin

        if(auth()->user() != null) {
        	if(Auth::user()->user_type=='seller'){
        		 	return array(
                            'status' => 0,
                            'cart_count' => count($carts),
                            'modal_view' => view('frontend.partials.sellerCannotAddedCart')->render(),
                            'nav_cart_view' => view('frontend.partials.cart')->render(),
                        );
        	}
            $user_id = Auth::user()->id;
            $data['user_id'] = $user_id;
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            if($request->session()->get('temp_user_id')) {
                $temp_user_id = $request->session()->get('temp_user_id');
            } else {
                $temp_user_id = bin2hex(random_bytes(10));
                $request->session()->put('temp_user_id', $temp_user_id);
            }
            $data['temp_user_id'] = $temp_user_id;
            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

        $data['product_id'] = $product->id;
        $data['owner_id'] = $product->user_id;

        $str = '';
        $tax = 0;
        if($product->auction_product == 0){
            if($product->digital != 1 && $request->quantity < $product->min_qty) {
                return array(
                    'status' => 0,
                    'cart_count' => count($carts),
                    'modal_view' => view('frontend.partials.minQtyNotSatisfied', [ 'min_qty' => $product->min_qty ])->render(),
                    'nav_cart_view' => view('frontend.partials.cart')->render(),
                );
            }

            //check the color enabled or disabled for the product
            if($request->has('color')) {
                $str = $request['color'];
            }

            if ($product->digital != 1) {
                //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
                foreach (json_decode(Product::find($request->id)->choice_options) as $key => $choice) {
                    if($str != null){
                        $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                    }
                    else{
                        $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                    }
                }
            }

        	//added by alauddin start
                if($request->attribute){
                    $str=$request->attribute;
                }
            // added by alauddin end
        
            $data['variation'] = $str;

            if($str != null && $product->variant_product){
                $product_stock = $product->stocks->where('variant', $str)->first();
            	if($product_stock){
                $price = $product_stock->price;
                $quantity = $product_stock->qty;
                if($quantity < $request['quantity']){
                    return array(
                        'status' => 0,
                        'cart_count' => count($carts),
                        'modal_view' => view('frontend.partials.outOfStockCart')->render(),
                        'nav_cart_view' => view('frontend.partials.cart')->render(),
                    );
                }
                }else{
                 $price = $product->unit_price;
                }
                
            }

            else{
                $price = $product->unit_price;
            }

            //discount calculation
            $discount_applicable = false;

            if ($product->discount_start_date == null) {
                $discount_applicable = true;
            }
            elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
                $discount_applicable = true;            
            }else{
            	//Added by alauddin start  
                
               
                
                $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->where('flash_deals.title',"New Customer Offer")     
                   ->select('flash_deals.title','flash_deals.minimum_amount','flash_deal_products.*')->first();
                if(!empty($flashDeal)){
                    if($flashDeal->title=="New Customer Offer"){
                        if(auth()->user() != null) {
                            $cart_check = Cart::where('user_id', Auth::user()->id)->where('offer_status',1)->get();
                            if(count($cart_check) == 0){

                                $order_check = Order::where('user_id', Auth::user()->id)
                                ->where('delivery_status','!=','canceled')
                                ->get();
                                if(count($order_check) == 0)
                                {
                                    if($flashDeal->title=="New Customer Offer"){
                                        $total=0;
                                        $carts = Cart::where('user_id',Auth::user()->id)->get();
                                        if(!empty($carts)){
                                            foreach ($carts as $key => $cartItem){ 
                                                $total = $total + ($cartItem['price'] * $cartItem['quantity']);
                                            }
                                        }
                                    
                                        if($total>=$flashDeal->minimum_amount){
                                            $data['offer_status'] =1;
                                            $discount_applicable = true;
                                        }
                                        
                                    }

                                } 

                            }       
                        }
                    }else{                        
                        $discount_applicable = true;  
                    }        

                }else{
                    $discount_applicable = true;
                }    

                //Added by alauddin end 
            }

            if ($discount_applicable) {
            	if( isset($flashDeal->title) && ($flashDeal->title=="New Customer Offer")){
                    if($flashDeal->discount_type == 'percent'){
                        $price -= ($price*$flashDeal->discount)/100;
                        $data['offer_discount_amount'] = ($price*$flashDeal->discount)/100;
                    }
                    elseif($flashDeal->discount_type == 'amount'){
                        $price -= $flashDeal->discount;
                        $data['offer_discount_amount'] =$flashDeal->discount;
                    }
                }else{
                	if($product->discount_type == 'percent'){
                    	$price -= ($price*$product->discount)/100;
                	}
                	elseif($product->discount_type == 'amount'){
                    	$price -= $product->discount;
                	}
                }
            
            	//added by alauddin start

            	if($product->discount_type == 'percent'){
               	 	$price -= ($product->unit_price*$product->unikart_discount)/100;
            	}
            	elseif($product->discount_type == 'amount'){
                	$price -= $product->unikart_discount;
            	}

            	//added by alauddin end
            
            
            }

        	
        
        
            //calculation of taxes
            foreach ($product->taxes as $product_tax) {
            if($product_tax->tax_type == 'percent'){
                $tax += ($price * $product_tax->tax) / 100;
            }
            elseif($product_tax->tax_type == 'amount'){
                $tax += $product_tax->tax;
            }
        }

            $data['quantity'] = $request['quantity'];
            $data['price'] = $price;
            $data['tax'] = $tax;
            //$data['shipping'] = 0;
            $data['shipping_cost'] = 0;
            $data['product_referral_code'] = null;
            $data['cash_on_delivery'] = $product->cash_on_delivery;
            $data['digital'] = $product->digital;

            if ($request['quantity'] == null){
                $data['quantity'] = 1;
            }

            if(Cookie::has('referred_product_id') && Cookie::get('referred_product_id') == $product->id) {
                $data['product_referral_code'] = Cookie::get('product_referral_code');
            }

            if($carts && count($carts) > 0){
                $foundInCart = false;

                foreach ($carts as $key => $cartItem){
                    $cart_product = Product::where('id', $cartItem['product_id'])->first();
                    if($cart_product->auction_product == 1){
                        return array(
                            'status' => 0,
                            'cart_count' => count($carts),
                            'modal_view' => view('frontend.partials.auctionProductAlredayAddedCart')->render(),
                            'nav_cart_view' => view('frontend.partials.cart')->render(),
                        );
                    }

                    if($cartItem['product_id'] == $request->id) {
                        $product_stock = $product->stocks->where('variant', $str)->first();
                    	if($product_stock)
                        	$quantity = $product_stock->qty;
						else
                        	$quantity = 100;

                        if (($product->max_qty) <= $cartItem['quantity']) {
                            $msg = 'You Can not add more than '.($product->max_qty).' Quantity for this product';
                            $status = 0;
                            }
                            
                        if($quantity < $cartItem['quantity'] + $request['quantity']){
                            return array(
                                'status' => 0,
                                'cart_count' => count($carts),
                                'modal_view' => view('frontend.partials.outOfStockCart')->render(),
                                'nav_cart_view' => view('frontend.partials.cart')->render(),
                            );
                        }
                        if(($str != null && $cartItem['variation'] == $str) || $str == null){
                            $foundInCart = true;

                            $cartItem['quantity'] += $request['quantity'];
                            $cartItem->save();
                        }
                    }
                }
                if (!$foundInCart) {
                    Cart::create($data);
                }
            }
            else{
                Cart::create($data);
            }

            if(auth()->user() != null) {
                $user_id = Auth::user()->id;
                $carts = Cart::where('user_id', $user_id)->get();
            	//added by alauddin start
            	$carts_offer_check =array();
            	$carts_offer_check = Cart::where('user_id', $user_id)->where('offer_status',1)->get(); //added by alauddin


            	if(count($carts_offer_check)==0){
                	$order_check = Order::where('user_id', Auth::user()->id)
                    ->where('delivery_status','!=','canceled')
                    ->get();
                	if(count($order_check) == 0){
                    	$total=0;
                    	$carts = Cart::where('user_id',Auth::user()->id)->get();

                    	if(!empty($carts)){
                        	foreach ($carts as $key => $cartItem){ 
                            	$total = $total + ($cartItem['price'] * $cartItem['quantity']);
                        	}
                    	}

                    	foreach ($carts as $key => $cartItem){

                        	$flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                        	->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                        	->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                        	->where('flash_deal_products.product_id',$cartItem['product_id'])  
                       	 	->where('flash_deals.title',"New Customer Offer")     
                        	->select('flash_deals.title','flash_deals.minimum_amount','flash_deal_products.*')->first();

                        	if(!empty($flashDeal)){

                            	if($total>=$flashDeal->minimum_amount){

                                	$object = Cart::findOrFail($cartItem['id']);

                                	$object['offer_status']=1;
                                	$object['offer_discount_amount']=$flashDeal->discount;
                                	$object->save();
                                	break;

                            	}
                        	}
                    	}
                    	$carts = Cart::where('user_id', $user_id)->get();
                	}
            	}

            	//added by alauddin end
            } else {
                $temp_user_id = $request->session()->get('temp_user_id');
                $carts = Cart::where('temp_user_id', $temp_user_id)->get();
            }
            return array(
                'status' => 1,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.partials.addedToCart', compact('product', 'data'))->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
            );
        }
        else{

            $price = $product->bids->max('amount');

            foreach ($product->taxes as $product_tax) {
                if($product_tax->tax_type == 'percent'){
                    $tax += ($price * $product_tax->tax) / 100;
                }
                elseif($product_tax->tax_type == 'amount'){
                    $tax += $product_tax->tax;
                }
            }

            $data['quantity'] = 1;
            $data['price'] = $price;
            $data['tax'] = $tax;
            $data['shipping_cost'] = 0;
            $data['product_referral_code'] = null;
            $data['cash_on_delivery'] = $product->cash_on_delivery;
            $data['digital'] = $product->digital;

            if(count($carts) == 0){
                Cart::create($data);
            }
            if(auth()->user() != null) {
                $user_id = Auth::user()->id;
                $carts = Cart::where('user_id', $user_id)->get();
            	//added by alauddin start
            	$carts_offer_check =array();
            	$carts_offer_check = Cart::where('user_id', $user_id)->where('offer_status',1)->get(); //added by alauddin


            	if(count($carts_offer_check)==0){
                	$order_check = Order::where('user_id', Auth::user()->id)
                    ->where('delivery_status','!=','canceled')
                    ->get();
                	if(count($order_check) == 0){
                    	$total=0;
                    	$carts = Cart::where('user_id',Auth::user()->id)->get();

                    	if(!empty($carts)){
                        	foreach ($carts as $key => $cartItem){ 
                            	$total = $total + ($cartItem['price'] * $cartItem['quantity']);
                        	}
                    	}

                    	foreach ($carts as $key => $cartItem){

                        	$flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                        	->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                        	->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                        	->where('flash_deal_products.product_id',$cartItem['product_id'])  
                       	 	->where('flash_deals.title',"New Customer Offer")     
                        	->select('flash_deals.title','flash_deals.minimum_amount','flash_deal_products.*')->first();

                        	if(!empty($flashDeal)){

                            	if($total>=$flashDeal->minimum_amount){

                                	$object = Cart::findOrFail($cartItem['id']);

                                	$object['offer_status']=1;
                                	$object['offer_discount_amount']=$flashDeal->discount;
                                	$object->save();
                                	break;

                            	}
                        	}
                    	}
                    	$carts = Cart::where('user_id', $user_id)->get();
                	}
            	}

            	//added by alauddin end
            } else {
                $temp_user_id = $request->session()->get('temp_user_id');
                $carts = Cart::where('temp_user_id', $temp_user_id)->get();
            }
            return array(
                'status' => 1,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.partials.addedToCart', compact('product', 'data'))->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
            );
        }
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        Cart::destroy($request->id);
        if(auth()->user() != null) {
            $user_id = Auth::user()->id;
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

        return array(
            'cart_count' => count($carts),
            'cart_view' => view('frontend.partials.cart_details', compact('carts'))->render(),
            'nav_cart_view' => view('frontend.partials.cart')->render(),
        );
    }

    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        $object = Cart::findOrFail($request->id);

        if($object['id'] == $request->id){
            $product = \App\Product::find($object['product_id']);
            $product_stock = $product->stocks->where('variant', $object['variation'])->first();
            $quantity = $product_stock->qty;

            if($quantity >= $request->quantity) {
                if($request->quantity >= $product->min_qty){
                    $object['quantity'] = $request->quantity;
                }
            }

            $object->save();
        }

        if(auth()->user() != null) {
            $user_id = Auth::user()->id;
            $carts = Cart::where('user_id', $user_id)->get();
        
        	//added by alauddin start
            $carts_offer_check = Cart::where('user_id', $user_id)->where('offer_status',1)->get(); //added by alauddin
            

            if(count($carts_offer_check)==0){
                $order_check = Order::where('user_id', Auth::user()->id)
                ->where('delivery_status','!=','canceled')
                ->get();
                if(count($order_check) == 0){
                    $total=0;
                    $carts = Cart::where('user_id',Auth::user()->id)->get();
                    if(!empty($carts)){
                        foreach ($carts as $key => $cartItem){ 
                            $total = $total + ($cartItem['price'] * $cartItem['quantity']);
                        }
                    }
                    foreach ($carts as $key => $cartItem){
                        $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                        ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deal_products.product_id',$cartItem['product_id'])  
                        ->where('flash_deals.title',"New Customer Offer")     
                        ->select('flash_deals.title','flash_deals.minimum_amount','flash_deal_products.*')->first();
                        
                        if(!empty($flashDeal)){
                            if($total>=$flashDeal->minimum_amount){
                                $object = Cart::findOrFail($cartItem['product_id']);
                                $object['offer_status']=1;
                                $object['offer_discount_amount']=$flashDeal->discount;
                                $object->save();
                                break;
                                
                            }
                        }
                    }
                	$carts = Cart::where('user_id', $user_id)->get();
                }
            }

            //added by alauddin end
        
        } else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

        return array(
            'cart_count' => count($carts),
            'cart_view' => view('frontend.partials.cart_details', compact('carts'))->render(),
            'nav_cart_view' => view('frontend.partials.cart')->render(),
        );
    }
}
