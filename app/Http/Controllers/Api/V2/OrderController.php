<?php

namespace App\Http\Controllers\Api\V2;
use App\FlashDeal; //added by alauddin
use App\City; //added by alauddin
use App\Address;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\OrderDetail;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\User;
use Illuminate\Support\Facades\DB;
use \App\Utility\NotificationUtility;
use App\CombinedOrder;
use App\Http\Controllers\AffiliateController;

class OrderController extends Controller
{



	public function store(Request $request, $set_paid = false)
    {

        DB::beginTransaction();
        try { 
            //$order_check = Order::where('user_id', Auth::user()->id)->get(); //added by alauddin
            $carts =Cart::where('user_id',$request->user_id)->get();
                
            
                if ($carts->isEmpty()) {
                    return response()->json([
                        'combined_order_id' => 0,
                        'result' => false,
                        'message' => 'Cart is Empty'
                    ]);
                }

            $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

            $user = User::find($request->user_id);
            $shipping_info->name = $user->name;
            $shipping_info->email = $user->email;
            if ($shipping_info->latitude || $shipping_info->longitude) {
                $shipping_info->lat_lang = $shipping_info->latitude . ',' . $shipping_info->longitude;
            }

            $combined_order = new CombinedOrder;
            $combined_order->user_id =$request->user_id;
            $combined_order->shipping_address = json_encode($shipping_info);
            $combined_order->save();

            $seller_products = array();
            foreach ($carts as $cartItem){
                $product_ids = array();
                $product = Product::find($cartItem['product_id']);
                if(isset($seller_products[$product->user_id])){
                    $product_ids = $seller_products[$product->user_id];
                }
                array_push($product_ids, $cartItem);
                $seller_products[$product->user_id] = $product_ids;
            }
            
            $order_net_total=0; //added by alauddin
            $total_shipping = 0; //added by alauddin 
            $seller_count=count($seller_products); //added by alauddin

            $net_total_shipping = 0; //added by alauddin 
            $net_total_tax=0; //added by alauddin
            $net_total_coupon_discount=0; //added by alauddin
            
            
            foreach ($seller_products as $seller_product) {
                $all_orders = Order::get();
                $order = new Order;
                $order->combined_order_id = $combined_order->id;
                $order->user_id =$request->user_id;
                $order->shipping_address = json_encode($shipping_info);

                $order->payment_type = $request->payment_type;
                $order->delivery_viewed = '0';
                $order->payment_status_viewed = '0';
            //  $order->code = date('Ymd-His') . rand(10, 99);
            //  $order->code = date('Ymd') .'-'.(count( $all_orders)+1);
                $order->date = strtotime('now');
                $order->admin_shipping_cost = get_setting('flat_rate_shipping_cost');

                if($request->ios == 1){
                    $order_from = 'IOS';
                   }else{
                    $order_from = 'Android';
                } 

                $order->order_from = $order_from;

                $first_order_check = Order::where('user_id',$request->user_id)
                ->where('delivery_status','!=','cancelled')->get();

                    if(count($first_order_check) == 0){
                        $is_first_order = 'Yes';
                    }else{
                        $is_first_order = 'No';
                    }

                $order->is_first_order = $is_first_order;
                $order->save();

                $subtotal = 0;
                $tax = 0;
                $shipping = 0;
                $coupon_discount = 0;
                $uni_coupon_discount=0;
                $seller_coupon_discount=0;
                $item_discount = 0;
                $total_shipping=0;
                $unicart_total_shipping=0;

                //Order Details Storing
                foreach ($seller_product as $cartItem) {
                    $product = Product::find($cartItem['product_id']);
                    $disc = 0;
                    $unic_dis=0;
                    if(
                        strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                        strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
                    ) {

                        
                        
                        $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                        ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deal_products.product_id',$product->id)  
                        ->where('flash_deals.campaign_type',"First Order") 
                        ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

                        if(!empty($flashDeal)){
                            if($flashDeal->campaign_type=="First Order"){
                                if($cartItem['offer_status']==1){
                                
                                        $disc = $cartItem['offer_discount_amount'];
                                        $unic_dis = $cartItem['unicart_discount_amount'];                        
                                        
                                }
                            }else{
                                    if ($product->discount_type == 'percent') {
                                        $disc = (($product->discount) / 100) * $cartItem['quantity'];
                                    } elseif ($product->discount_type == 'amount') {
                                        $disc = $product->discount * $cartItem['quantity'];
                                    }
                                            // $item_discount+=$disc;
                
                                    //added by alauddin start

                                    if ($product->discount_type == 'percent') {
                                        $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                                    } elseif ($product->discount_type == 'amount') {
                                        $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                                    }
                            }
                        }else{
                            if ($product->discount_type == 'percent') {
                                $disc = (($product->discount) / 100) * $cartItem['quantity'];
                            } elseif ($product->discount_type == 'amount') {
                                $disc = $product->discount * $cartItem['quantity'];
                            }
                                        // $item_discount+=$disc;

                            //added by alauddin start

                            if ($product->discount_type == 'percent') {
                                $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                            } elseif ($product->discount_type == 'amount') {
                                $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                            }
                        }        


                        
                    }else if ($product->discount_start_date == null) {
                        
                        $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                        ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                        ->where('flash_deal_products.product_id',$product->id)  
                        ->where('flash_deals.campaign_type',"First Order") 
                        ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

                        if(!empty($flashDeal)){
                            if($flashDeal->campaign_type=="First Order"){
                                if($cartItem['offer_status']==1){
                                
                                        $disc = $cartItem['offer_discount_amount'];
                                        $unic_dis = $cartItem['unicart_discount_amount'];                        
                                        
                                }
                            }else{
                                    if ($product->discount_type == 'percent') {
                                                $disc = (($product->discount) / 100) * $cartItem['quantity'];
                                    } elseif ($product->discount_type == 'amount') {
                                        $disc = $product->discount * $cartItem['quantity'];
                                    }
                                            // $item_discount+=$disc;
                
                                    //added by alauddin start

                                    if ($product->discount_type == 'percent') {
                                        $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                                    } elseif ($product->discount_type == 'amount') {
                                        $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                                    }
                            }
                        }else{
                            if ($product->discount_type == 'percent') {
                                $disc = (($product->discount) / 100) * $cartItem['quantity'];
                            } elseif ($product->discount_type == 'amount') {
                                $disc = $product->discount * $cartItem['quantity'];
                            }
                                        // $item_discount+=$disc;

                            //added by alauddin start

                            if ($product->discount_type == 'percent') {
                                $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                            } elseif ($product->discount_type == 'amount') {
                                $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                            }
                        }   
                        
                    }else{
                        


                        if($cartItem['offer_status']==1){
                                
                            $disc = $cartItem['offer_discount_amount'];
                            $unic_dis = $cartItem['unicart_discount_amount'];                        
                            
                        }



                        
                        //Added by alauddin end  
                        
                        
                        
                    }

                    
                    //added by alauddin end


                    //app discount start

                    $app_discount = $cartItem['app_discount_amount']* $cartItem['quantity'];   

                    //app discount end

                
                    $item_discount+=$disc+$unic_dis;

                    
                    if($cartItem['offer_status']==1){
                        $subtotal += ($product->unit_price* $cartItem['quantity'])-($disc+$unic_dis); //added by alauddin
                        $order_net_total+=($product->unit_price* $cartItem['quantity'])-($disc+$unic_dis); //added by alauddin
                    }else{
                        $subtotal +=($cartItem['price'] * $cartItem['quantity']);
                        $order_net_total+=($cartItem['price'] * $cartItem['quantity']);
                    }

                    $tax += $cartItem['tax'] * $cartItem['quantity'];
                    $net_total_tax +=$cartItem['tax'] * $cartItem['quantity']; //added by alauddin
                    $net_total_shipping +=$cartItem['shipping_cost']; //added by alauddin
                    $total_shipping +=$cartItem['shipping_cost']; //added by alauddin

                    $unicart_total_shipping +=$cartItem['unicart_shipping_cost']; //added by alauddin

                    $coupon_discount += $cartItem['discount'];

                    if(!empty($cartItem['discount'])){
                        if($cartItem['coupon_discount_by']=="Unikart"){
                            $uni_coupon_discount += $cartItem['discount'];
                        }else{
                            $seller_coupon_discount += $cartItem['discount'];
                        }

                    }

                    $net_total_coupon_discount +=$cartItem['discount'];//added by alauddin

                    $product_variation = $cartItem['variation'];

                    $product_stock = $product->stocks->where('variant', $product_variation)->first();
                    if (!empty($product_stock)  && $product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                        
                        $order->delete();
                        $combined_order->delete();
                        return response()->json([
                            'combined_order_id' => 0,
                            'result' => false,
                            'message' => 'The requested quantity is not available for ' . $product->name
                        ]);
                    
                    } elseif (!empty($product_stock)  && $product->digital != 1) {
                        $product_stock->qty -= $cartItem['quantity'];
                        $product_stock->save();
                    }

                    $order_detail = new OrderDetail;
                    $order_detail->order_id = $order->id;
                    $order_detail->seller_id = $product->user_id;
                    $order_detail->product_id = $product->id;
                    $order_detail->variation = $product_variation;
                    if($cartItem['offer_status']==1){
                        $order_detail->price = ($product->unit_price* $cartItem['quantity'])-($disc+$unic_dis); //added by alauddin
                    }else{
                        $order_detail->price = ($cartItem['price'] * $cartItem['quantity']);
                    }
                    $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                    $order_detail->shipping_type = $cartItem['shipping_type'];
                    $order_detail->product_referral_code = $cartItem['product_referral_code'];
                    $order_detail->shipping_cost = $cartItem['shipping_cost'];
                    $order_detail->unicart_shipping_cost = $cartItem['unicart_shipping_cost']; //added by alauddin

                    $order_detail->discount =$disc+$unic_dis+$app_discount;
                    $order_detail->total_discount = $disc+$unic_dis+$app_discount;

                    $order_detail->product_unit_price =$product->unit_price; //added by alauddin
                    $order_detail->unikart_discount =$unic_dis; //added by alauddin
                    $order_detail->app_discount =$app_discount; //added by alauddin

                    //$order_detail->due_to_seller = $product->trade_price*$cartItem['quantity'];
                    // $order_detail->unikart_earning = $product->unikart_earning*$cartItem['quantity'];

                    //added by alauddin start
                    
                
                    $t_mp_price=$product->unit_price*$cartItem['quantity'];
                    $sale_price=$t_mp_price-$disc;
                    $unicart_commission=($sale_price*$product->comission)/100;
                
                    $trade_price=$sale_price-$unicart_commission;
                    
                    $unikart_earning=$unicart_commission-($unic_dis+$app_discount);
                    
                    $order_detail->due_to_seller =$trade_price; //added by alauddin
                    $order_detail->unikart_earning =$unikart_earning; //added by alauddin

                // added by alauddin end
                    
                    
                    $shipping += $order_detail->shipping_cost;

                    if ($cartItem['shipping_type'] == 'pickup_point') {
                        $order_detail->pickup_point_id = $cartItem['pickup_point'];
                    }
                    //End of storing shipping cost

                    $order_detail->quantity = $cartItem['quantity'];
                    $order_detail->save();

                    $product->num_of_sale += $cartItem['quantity'];
                    $product->save();

                    $order->seller_id = $product->user_id;

                    if ($product->added_by == 'seller' && $product->user->seller != null){
                        $seller = $product->user->seller;
                        $seller->num_of_sale += $cartItem['quantity'];
                        $seller->save();
                    }

                    $shipping_skip_total = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost_total')->first()->value;
                    if($shipping_skip_total>$subtotal){
                        //$shipping = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
                    	 //added by alauddin start

                        if (get_setting('shipping_type') == 'flat_rate') {
                            $shipping = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
                        }else{
                            
                            $city = City::where('id', $shipping_info->city_id)->first(); //added by alauddin
                            if ($city != null) {
                                $shipping = $city->cost;
                            }else{
                                $shipping =0;
                            }
                        }

                        //added by alauddin end
                    }else{
                    $shipping = 0;
                    }

                    if (addon_is_activated('affiliate_system')) {
                        if ($order_detail->product_referral_code) {
                            $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                            $affiliateController = new AffiliateController;
                            $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                        }
                    }
                }

                if($seller_count>1){
                    $order->grand_total = $subtotal + $tax+$total_shipping;
                }else{
                    $order->grand_total = $subtotal + $tax + $shipping;
                }

                if ($seller_product[0]->coupon_code != null) {
                    // if (Session::has('club_point')) {
                    //     $order->club_point = Session::get('club_point');
                    // }
                    $order->coupon_discount = $coupon_discount;
                    $order->unikart_coupon_discount =$uni_coupon_discount; //added by alauddin
                    $order->seller_coupon_discount = $seller_coupon_discount; //added by alauddin    


                    $order->grand_total -= $coupon_discount;

                    $coupon_usage = new CouponUsage;
                    $coupon_usage->user_id = $request->user_id;
                    $coupon_usage->order_id = $order->id;
                    $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                    $coupon_usage->save();
                }
                $order->item_discount = $item_discount;
                $combined_order->grand_total += $order->grand_total;
                $order->total_unicart_shipping_cost =$unicart_total_shipping;//added by alauddin
                $order->code = date('Ymd') .'-'.$order->id; //added by alauddin
                $order->save();
            }

            //added by alauddin start

            // $shipping_skip_total = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost_total')->first()->value;
            
            // if($shipping_skip_total>$order_net_total){
            //     $shipping_cost = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
            //     $combined_order->shipping_cost= $shipping_cost; 
            //     $combined_order->unicart_shipping_cost=0;             
            // }else{
            //     $shipping_cost = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
            //     $combined_order->shipping_cost=0; 
            //     $combined_order->unicart_shipping_cost=$shipping_cost;  
            // }

            // $combined_order->grand_total =$order_net_total+$net_total_tax+$shipping_cost;

            //added by alauddin end


            $combined_order->save();

            Cart::where('user_id', $request->user_id)->delete();


            if (
                $request->payment_type == 'cash_on_delivery'
                || $request->payment_type == 'wallet'
                || strpos($request->payment_type, "manual_payment_") !== false // if payment type like  manual_payment_1 or  manual_payment_25 etc
            ) {
                //event(new OrderMailSMS($request,$order));
                //NotificationUtility::sendOrderPlacedNotificationForApi($order, $request);
            }

            DB::commit();

        }catch(\Exception $e){
            DB::rollback();
            // something went wrong
        }        

        return response()->json([
            'combined_order_id' => $combined_order->id,
            'order_code' => $order->code,
            'result' => true,
            'message' => translate('Your order has been placed successfully')
        ]);

    }

	 public function store_old(Request $request, $set_paid = false)
    {
        //$order_check = Order::where('user_id', Auth::user()->id)->get(); //added by alauddin
        $carts =Cart::where('user_id',$request->user_id)->get();
            
        
            if ($carts->isEmpty()) {
                return response()->json([
                    'combined_order_id' => 0,
                    'result' => false,
                    'message' => 'Cart is Empty'
                ]);
            }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        $user = User::find($request->user_id);
        $shipping_info->name = $user->name;
        $shipping_info->email = $user->email;
        if ($shipping_info->latitude || $shipping_info->longitude) {
            $shipping_info->lat_lang = $shipping_info->latitude . ',' . $shipping_info->longitude;
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id =$request->user_id;
        $combined_order->shipping_address = json_encode($shipping_info);
        $combined_order->save();

        $seller_products = array();
        foreach ($carts as $cartItem){
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if(isset($seller_products[$product->user_id])){
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }
        
        $order_net_total=0; //added by alauddin
        $total_shipping = 0; //added by alauddin 
        $seller_count=count($seller_products); //added by alauddin

        $net_total_shipping = 0; //added by alauddin 
        $net_total_tax=0; //added by alauddin
        $net_total_coupon_discount=0; //added by alauddin
        
        
        foreach ($seller_products as $seller_product) {
            $all_orders = Order::get();
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id =$request->user_id;
            $order->shipping_address = json_encode($shipping_info);

            $order->payment_type = $request->payment_type;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
          //  $order->code = date('Ymd-His') . rand(10, 99);
          //  $order->code = date('Ymd') .'-'.(count( $all_orders)+1);
            $order->date = strtotime('now');
            $order->admin_shipping_cost = get_setting('flat_rate_shipping_cost');
            $order->order_from ="App";
            $first_order_check = Order::where('user_id',$request->user_id)
            ->where('delivery_status','!=','cancelled')->get();

                if(count($first_order_check) == 0){
                    $is_first_order = 'Yes';
                }else{
                    $is_first_order = 'No';
                }

            $order->is_first_order = $is_first_order;
            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;
            $uni_coupon_discount=0;
            $seller_coupon_discount=0;
            $item_discount = 0;
            $total_shipping=0;
            $unicart_total_shipping=0;

            //Order Details Storing
            foreach ($seller_product as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $disc = 0;
                $unic_dis=0;
                if(
                    strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                    strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
                ) {

                     
                    
                    $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                    ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                    ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                    ->where('flash_deal_products.product_id',$product->id)  
                    ->where('flash_deals.campaign_type',"First Order") 
                    ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

                    if(!empty($flashDeal)){
                        if($flashDeal->campaign_type=="First Order"){
                            if($cartItem['offer_status']==1){
                            
                                     $disc = $cartItem['offer_discount_amount'];
                                     $unic_dis = $cartItem['unicart_discount_amount'];                        
                                       
                            }
                        }else{
                                if ($product->discount_type == 'percent') {
                                    $disc = (($product->discount) / 100) * $cartItem['quantity'];
                                } elseif ($product->discount_type == 'amount') {
                                    $disc = $product->discount * $cartItem['quantity'];
                                }
                                         // $item_discount+=$disc;
            
                                //added by alauddin start

                                if ($product->discount_type == 'percent') {
                                    $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                                } elseif ($product->discount_type == 'amount') {
                                    $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                                }
                        }
                    }else{
                        if ($product->discount_type == 'percent') {
                            $disc = (($product->discount) / 100) * $cartItem['quantity'];
                        } elseif ($product->discount_type == 'amount') {
                            $disc = $product->discount * $cartItem['quantity'];
                        }
                                    // $item_discount+=$disc;

                        //added by alauddin start

                        if ($product->discount_type == 'percent') {
                            $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                        } elseif ($product->discount_type == 'amount') {
                            $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                        }
                    }        


                    
                }else if ($product->discount_start_date == null) {
                    
                    $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                    ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                    ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                    ->where('flash_deal_products.product_id',$product->id)  
                    ->where('flash_deals.campaign_type',"First Order") 
                    ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

                    if(!empty($flashDeal)){
                        if($flashDeal->campaign_type=="First Order"){
                            if($cartItem['offer_status']==1){
                            
                                     $disc = $cartItem['offer_discount_amount'];
                                     $unic_dis = $cartItem['unicart_discount_amount'];                        
                                       
                            }
                        }else{
                                if ($product->discount_type == 'percent') {
                                             $disc = (($product->discount) / 100) * $cartItem['quantity'];
                                } elseif ($product->discount_type == 'amount') {
                                    $disc = $product->discount * $cartItem['quantity'];
                                }
                                         // $item_discount+=$disc;
            
                                //added by alauddin start

                                if ($product->discount_type == 'percent') {
                                    $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                                } elseif ($product->discount_type == 'amount') {
                                    $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                                }
                        }
                    }else{
                        if ($product->discount_type == 'percent') {
                            $disc = (($product->discount) / 100) * $cartItem['quantity'];
                        } elseif ($product->discount_type == 'amount') {
                            $disc = $product->discount * $cartItem['quantity'];
                        }
                                    // $item_discount+=$disc;

                        //added by alauddin start

                        if ($product->discount_type == 'percent') {
                            $unic_dis = (($product->unikart_discount) / 100) * $cartItem['quantity'];
                        } elseif ($product->discount_type == 'amount') {
                            $unic_dis = $product->unikart_discount * $cartItem['quantity'];
                        }
                    }   
                    
                }else{
                       


                    if($cartItem['offer_status']==1){
                            
                        $disc = $cartItem['offer_discount_amount'];
                        $unic_dis = $cartItem['unicart_discount_amount'];                        
                           
                    }



                    
                    //Added by alauddin end  
                    
                    
                    
                }

                
                //added by alauddin end


                //app discount start

                $app_discount = $cartItem['app_discount_amount']* $cartItem['quantity'];   

                //app discount end

               
                $item_discount+=$disc+$unic_dis;

                
                if($cartItem['offer_status']==1){
                    $subtotal += ($product->unit_price* $cartItem['quantity'])-($disc+$unic_dis); //added by alauddin
                    $order_net_total+=($product->unit_price* $cartItem['quantity'])-($disc+$unic_dis); //added by alauddin
                }else{
                    $subtotal +=($cartItem['price'] * $cartItem['quantity']);
                    $order_net_total+=($cartItem['price'] * $cartItem['quantity']);
                }

                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $net_total_tax +=$cartItem['tax'] * $cartItem['quantity']; //added by alauddin
                $net_total_shipping +=$cartItem['shipping_cost']; //added by alauddin
                $total_shipping +=$cartItem['shipping_cost']; //added by alauddin

                $unicart_total_shipping +=$cartItem['unicart_shipping_cost']; //added by alauddin

                $coupon_discount += $cartItem['discount'];

                if(!empty($cartItem['discount'])){
                    if($cartItem['coupon_discount_by']=="Unikart"){
                        $uni_coupon_discount += $cartItem['discount'];
                    }else{
                        $seller_coupon_discount += $cartItem['discount'];
                    }

                }

                $net_total_coupon_discount +=$cartItem['discount'];//added by alauddin

                $product_variation = $cartItem['variation'];

                $product_stock = $product->stocks->where('variant', $product_variation)->first();
                if (!empty($product_stock)  && $product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                    
                    $order->delete();
                    $combined_order->delete();
                    return response()->json([
                        'combined_order_id' => 0,
                        'result' => false,
                        'message' => 'The requested quantity is not available for ' . $product->name
                    ]);
                   
                } elseif (!empty($product_stock)  && $product->digital != 1) {
                    $product_stock->qty -= $cartItem['quantity'];
                    $product_stock->save();
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $product_variation;
                if($cartItem['offer_status']==1){
                    $order_detail->price = ($product->unit_price* $cartItem['quantity'])-($disc+$unic_dis); //added by alauddin
                }else{
                    $order_detail->price = ($cartItem['price'] * $cartItem['quantity']);
                }
                $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = $cartItem['shipping_cost'];
                $order_detail->unicart_shipping_cost = $cartItem['unicart_shipping_cost']; //added by alauddin

                $order_detail->discount =$disc+$unic_dis+$app_discount;
                $order_detail->total_discount = $disc+$unic_dis+$app_discount;

                $order_detail->product_unit_price =$product->unit_price; //added by alauddin
                $order_detail->unikart_discount =$unic_dis; //added by alauddin
                $order_detail->app_discount =$app_discount; //added by alauddin

                //$order_detail->due_to_seller = $product->trade_price*$cartItem['quantity'];
                // $order_detail->unikart_earning = $product->unikart_earning*$cartItem['quantity'];

                //added by alauddin start
                
               
                $t_mp_price=$product->unit_price*$cartItem['quantity'];
                $sale_price=$t_mp_price-$disc;
                $unicart_commission=($sale_price*$product->comission)/100;
               
                $trade_price=$sale_price-$unicart_commission;
                
                $unikart_earning=$unicart_commission-($unic_dis+$app_discount);
                
                $order_detail->due_to_seller =$trade_price; //added by alauddin
                $order_detail->unikart_earning =$unikart_earning; //added by alauddin

               // added by alauddin end
                
                
                $shipping += $order_detail->shipping_cost;

                if ($cartItem['shipping_type'] == 'pickup_point') {
                    $order_detail->pickup_point_id = $cartItem['pickup_point'];
                }
                //End of storing shipping cost

                $order_detail->quantity = $cartItem['quantity'];
                $order_detail->save();

                $product->num_of_sale += $cartItem['quantity'];
                $product->save();

                $order->seller_id = $product->user_id;

                if ($product->added_by == 'seller' && $product->user->seller != null){
                    $seller = $product->user->seller;
                    $seller->num_of_sale += $cartItem['quantity'];
                    $seller->save();
                }

                $shipping_skip_total = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost_total')->first()->value;
                if($shipping_skip_total>$subtotal){
                    $shipping = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
                }else{
                $shipping = 0;
                }

                if (addon_is_activated('affiliate_system')) {
                    if ($order_detail->product_referral_code) {
                        $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                    }
                }
            }

            if($seller_count>1){
                $order->grand_total = $subtotal + $tax+$total_shipping;
            }else{
                $order->grand_total = $subtotal + $tax + $shipping;
            }

            if ($seller_product[0]->coupon_code != null) {
                // if (Session::has('club_point')) {
                //     $order->club_point = Session::get('club_point');
                // }
                $order->coupon_discount = $coupon_discount;
                $order->unikart_coupon_discount =$uni_coupon_discount; //added by alauddin
                $order->seller_coupon_discount = $seller_coupon_discount; //added by alauddin    


                $order->grand_total -= $coupon_discount;

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = $request->user_id;
                $coupon_usage->order_id = $order->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }
            $order->item_discount = $item_discount;
            $combined_order->grand_total += $order->grand_total;
            $order->total_unicart_shipping_cost =$unicart_total_shipping;//added by alauddin
            $order->code = date('Ymd') .'-'.$order->id; //added by alauddin
            $order->save();
        }

        //added by alauddin start

        // $shipping_skip_total = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost_total')->first()->value;
        
        // if($shipping_skip_total>$order_net_total){
        //     $shipping_cost = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
        //     $combined_order->shipping_cost= $shipping_cost; 
        //     $combined_order->unicart_shipping_cost=0;             
        // }else{
        //     $shipping_cost = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
        //     $combined_order->shipping_cost=0; 
        //     $combined_order->unicart_shipping_cost=$shipping_cost;  
        // }

        // $combined_order->grand_total =$order_net_total+$net_total_tax+$shipping_cost;

        //added by alauddin end


        $combined_order->save();

        Cart::where('user_id', $request->user_id)->delete();

        if (
            $request->payment_type == 'cash_on_delivery'
            || $request->payment_type == 'wallet'
            || strpos($request->payment_type, "manual_payment_") !== false // if payment type like  manual_payment_1 or  manual_payment_25 etc
        ) {
            NotificationUtility::sendOrderPlacedNotificationForApi($order, $request);
        }


        return response()->json([
            'combined_order_id' => $combined_order->id,
            'order_code' => $order->code,
            'result' => true,
            'message' => translate('Your order has been placed successfully')
        ]);

    }




    public function store_25_09_2022(Request $request, $set_paid = false)
    {
        $cartItems = Cart::where('user_id', $request->user_id)->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => 'Cart is Empty'
            ]);
        }

        $user = User::find($request->user_id);
    
        $address = Address::where('id', $cartItems->first()->address_id)->first();
       
        $shippingAddress = [];
        if ($address != null) {
            $shippingAddress['name']        = $user->name;
            $shippingAddress['email']       = $user->email;
            $shippingAddress['address']     = $address->address;
            $shippingAddress['country']     = $address->country;
            $shippingAddress['city']        = $address->city;
            $shippingAddress['postal_code'] = $address->postal_code;
            $shippingAddress['phone']       = $address->phone;
            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = $user->id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        $seller_products = array();
        foreach ($cartItems as $cartItem) {
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }

          $get_orders =[];
        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = $user->id;
            $order->shipping_address = json_encode($shippingAddress);

            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;

            //Order Details Storing
            foreach ($seller_product as $cartItem) {
                $product = Product::find($cartItem['product_id']);

                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $coupon_discount += $cartItem['discount'];

                $product_variation = $cartItem['variation'];

                $product_stock = $product->stocks->where('variant', $product_variation)->first();
                if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                    $order->delete();
                    $combined_order->delete();
                    return response()->json([
                        'combined_order_id' => 0,
                        'result' => false,
                        'message' => 'The requested quantity is not available for ' . $product->name
                    ]);
                } elseif ($product->digital != 1) {
                    $product_stock->qty -= $cartItem['quantity'];
                    $product_stock->save();
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $product_variation;
                $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = $cartItem['shipping_cost'];

                $shipping += $order_detail->shipping_cost;

                if ($cartItem['shipping_type'] == 'pickup_point') {
                    $order_detail->pickup_point_id = $cartItem['pickup_point'];
                }
                //End of storing shipping cost

                $order_detail->quantity = $cartItem['quantity'];
                $order_detail->save();

                $product->num_of_sale = $product->num_of_sale + $cartItem['quantity'];
                $product->save();

                $order->seller_id = $product->user_id;

                if (addon_is_activated('affiliate_system')) {
                    if ($order_detail->product_referral_code) {
                        $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                    }
                }
            }

            $order->grand_total = $subtotal + $tax + $shipping;

            if ($seller_product[0]->coupon_code != null) {
                // if (Session::has('club_point')) {
                //     $order->club_point = Session::get('club_point');
                // }
                $order->coupon_discount = $coupon_discount;
                $order->grand_total -= $coupon_discount;

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = $user->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }

            $combined_order->grand_total += $order->grand_total;

            if (strpos($request->payment_type, "manual_payment_") !== false) { // if payment type like  manual_payment_1 or  manual_payment_25 etc)

                $order->manual_payment = 1;
                $order->save();

            }

            $order->save();
             
        }
        $combined_order->save();

      
        

        Cart::where('user_id', $request->user_id)->where('owner_id', $request->owner_id)->delete();

        if (
            $request->payment_type == 'cash_on_delivery'
            || $request->payment_type == 'wallet'
            || strpos($request->payment_type, "manual_payment_") !== false // if payment type like  manual_payment_1 or  manual_payment_25 etc
        ) {
            NotificationUtility::sendOrderPlacedNotificationForApi($order, $request);
        }


        return response()->json([
            'combined_order_id' => $combined_order->id,
            'order_code' => $order->code,
            'result' => true,
            'message' => translate('Your order has been placed successfully')
        ]);
    }




	public function cancel(Request $request)
    {
        
        $find_user = User::find($request->user_id);
      
        if(!empty($find_user)){

            $order_info = Order::findOrFail($request->order_id);


            $orders=array(); 
            $orders = Order::Where('combined_order_id',$order_info->combined_order_id)->get();

            foreach($orders as $or ){
                $order = Order::findOrFail($or->id);
                if ($order != null) {
                    foreach ($order->orderDetails as $key => $orderDetail) {
                        try {
        
                            $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $orderDetail->variation)->first();
                            if ($product_stock != null) {
                                $product_stock->qty += $orderDetail->quantity;
                                $product_stock->save();
                            }
        
                        } catch (\Exception $e) {
        
                        }
                        $orderDetail->delivery_status = 'cancelled';
                        $orderDetail->save();
                    }
                    $order->delivery_status = 'cancelled';
                    $order->save();
        
                
                }
            }    

            return response()->json([
                'result' => true,
                'message' => translate('Your order has been cancelled successfully')
            ]);
        }
      
    }



    public function cancel_22_12_2022(Request $request)
    {
        
        $find_user = User::find($request->user_id);
      
        if(!empty($find_user)){
            $order = Order::findOrFail($request->order_id);
            if ($order != null) {
                foreach ($order->orderDetails as $key => $orderDetail) {
                    try {
    
                        $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $orderDetail->variation)->first();
                        if ($product_stock != null) {
                            $product_stock->qty += $orderDetail->quantity;
                            $product_stock->save();
                        }
    
                    } catch (\Exception $e) {
    
                    }
                    $orderDetail->delivery_status = 'cancelled';
                    $orderDetail->save();
                }
                $order->delivery_status = 'cancelled';
                $order->save();
    
               return response()->json([
                'result' => true,
                'message' => translate('Your order has been cancelled successfully')
               ]);
            }
        }
      
    }

    public function sendsmsmsilpushno(Request $combined_order_id){

        $order = Order::where('combined_order_id',$combined_order_id->combined_order_id)
        ->select('combined_order_id','user_id','shipping_address',
        'payment_type','delivery_viewed','payment_status_viewed','date',
        'admin_shipping_cost','order_from','updated_at','created_at','id',
        'seller_id','grand_total','item_discount','total_unicart_shipping_cost','code')->first();

        
        NotificationUtility::sendOrderPlacedNotificationForApi($order, $combined_order_id);

        return response()->json([
            'result' => true,
            'message' => translate('order sms mail has been send successfully')
           ]);
    }
}
