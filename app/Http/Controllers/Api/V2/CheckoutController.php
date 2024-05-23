<?php


namespace App\Http\Controllers\Api\V2;

use App\Coupon;
use App\CouponUsage;
use App\Order;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\User;
use Auth;
class CheckoutController
{


	public function apply_coupon_code(Request $request)
    {
        $cart_items = Cart::where('user_id', $request->user_id)->get();
        $coupon = Coupon::where('code', $request->coupon_code)->first();
        $seller = User::where('id',$coupon->user_id)->first();

        if ($cart_items->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => 'Cart is empty'
            ]);
        }

        if ($coupon == null) {
            return response()->json([
                'result' => false,
                'message' => 'Invalid coupon!'
            ]);
        }

        $in_range = strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date;

        if (!$in_range) {
            return response()->json([
                'result' => false,
                'message' => 'Coupon expired!'
            ]);
        }

        $is_used = CouponUsage::where('user_id', $request->user_id)->where('coupon_id', $coupon->id)->first() != null;

      
    
    	//added by alauddin start
        if ($coupon->type == 'random_coupon') {
            $obj = json_decode($coupon->details);
            $use_check = CouponUsage::where('user_id', $request->user_id)->where('coupon_id', $coupon->id)->get();
            if (count($use_check) > ($obj->max_times)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Coupon has been used!'
                ]);
            }    
        }else if ($coupon->type == 'product_base') {

        }else if ($coupon->type == 'cart_base') {

        }else{
            if ($is_used) {
                     return response()->json([
                         'result' => false,
                         'message' => 'Coupon has been used!'
                     ]);
            }
        }     
        
       //added by alauddin end 


        $coupon_details = json_decode($coupon->details);
        $seller_coupons = Cart::where('owner_id',$coupon->user_id)
        ->where('user_id',Auth::user()->id)->first();

        if ($coupon->type == 'cart_base') {

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;

          if(($coupon->user_id == 9) || ($seller_coupons == !null)){

            if($coupon->user_id == 9){
                // Unikart coupon calculation
                foreach ($cart_items as $key => $cartItem) {
                    $subtotal += $cartItem['price'] * $cartItem['quantity'];
                    $tax += $cartItem['tax'] * $cartItem['quantity'];
                    $shipping += $cartItem['shipping'] * $cartItem['quantity'];
                }
                $sum = $subtotal + $tax;
    
                if ($sum >= $coupon_details->min_buy) {
                    if ($coupon->discount_type == 'percent') {
                        $coupon_discount = ($sum * $coupon->discount) / 100;
                        if ($coupon_discount > $coupon_details->max_discount) {
                            $coupon_discount = $coupon_details->max_discount;
                        }
                    } elseif ($coupon->discount_type == 'amount') {
                        $coupon_discount = $coupon->discount;
                    }
    
                    Cart::where('user_id', $request->user_id)->update([
                        'discount' => $coupon_discount / count($cart_items),
                        'coupon_code' => $request->coupon_code,
                        'coupon_discount_by' =>"Unikart",
                        'coupon_applied' => 1
                    ]);
    
                    return response()->json([
                        'result' => true,
                        'message' => 'Coupon has been applied'
                    ]);
    
                }else{
                    return response()->json([
                        'result' => false,
                        'message' => 'Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon'
                    ]);
                }
                
            }else{

                // Seller coupon calculation
                $sellercarts = Cart::where('owner_id',$coupon->user_id)->where('user_id',Auth::user()->id)->get();

                foreach ($sellercarts as $key => $cartItem) {
                    $subtotal += $cartItem['price'] * $cartItem['quantity'];
                    $tax += $cartItem['tax'] * $cartItem['quantity'];
                    $shipping += $cartItem['shipping'] * $cartItem['quantity'];
                }
                $sum = $subtotal + $tax;
    
                if ($sum >= $coupon_details->min_buy) {
                    if ($coupon->discount_type == 'percent') {
                        $coupon_discount = ($sum * $coupon->discount) / 100;
                        if ($coupon_discount > $coupon_details->max_discount) {
                            $coupon_discount = $coupon_details->max_discount;
                        }
                    } elseif ($coupon->discount_type == 'amount') {
                        $coupon_discount = $coupon->discount;
                    }
    
                    Cart::where('owner_id', $coupon->user_id)->update([
                        'discount' => $coupon_discount / count($sellercarts),
                        'coupon_code' => $request->coupon_code,
                        'coupon_discount_by' => $seller->name,
                        'coupon_applied' => 1
                    ]);
    
                    return response()->json([
                        'result' => true,
                        'message' => 'Coupon has been applied'
                    ]);
    
                }else{

                    return response()->json([
                        'result' => false,
                        'message' => 'Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon'
                    ]);

                }
            }
            
          }else{
            return response()->json([
                'result' => false,
                'message' => 'Sorry this coupon is applicable for '.''.$seller->name,
            ]);

          }

       
        }elseif($coupon->type == 'first_order_base'){ 
            $carts_offer_check = Cart::where('user_id',$request->user_id)->where('offer_status',1)->get(); //added by alauddin
            if(count($carts_offer_check) == 1){
                return response()->json([
                    'result' => false,
                    'message' => 'Use coupon for regular items only'
                ]);
            }else{

                $first_order_check = Order::where('user_id',$request->user_id)
                ->where('delivery_status','!=','cancelled')->get();

                if(count($first_order_check) == 0){
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    foreach ($cart_items as $key => $cartItem) {
                        $subtotal += $cartItem['price'] * $cartItem['quantity'];
                        $tax += $cartItem['tax'] * $cartItem['quantity'];
                        $shipping += $cartItem['shipping_cost'];
                    }
                    $sum = $subtotal + $tax ;
                    if ($sum >= $coupon_details->min_buy) {
                        
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                           
                            }

                            Cart::where('user_id', $request->user_id)->update([
                                'discount' => $coupon_discount / count($cart_items),
                                'coupon_code' => $request->coupon_code,
                                'coupon_discount_by' =>"Unikart",
                                'coupon_applied' => 1
                            ]);

                            return response()->json([
                                'result' => true,
                                'message' => 'Coupon has been applied'
                            ]);
                            
                        
                      
                    }else{
                        return response()->json([
                            'result' => false,
                            'message' => 'Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon'
                        ]);
                       
                    }    



                }else{
                    return response()->json([
                        'result' => false,
                        'message' => 'Coupon has been used'
                    ]);
                }
            }    
        
        }elseif ($coupon->type == 'product_base') {
           // $coupon_discount = 0;
            $coupon_disc=0;
            foreach ($cart_items as $key => $cartItem) {
                foreach ($coupon_details as $key => $coupon_detail) {
                    if ($coupon_detail->product_id == $cartItem['product_id']) {
                        if ($coupon->discount_type == 'percent') {
                           // $coupon_discount += $cartItem['price'] * $coupon->discount / 100;
                           $coupon_disc= ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                        } elseif ($coupon->discount_type == 'amount') {
                           // $coupon_discount += $coupon->discount;
                           $coupon_disc= $coupon->discount * $cartItem['quantity'];
                        }
                    	Cart::where('user_id', $request->user_id)->where('id',$cartItem['id'])->update(
                        [
                          'discount' =>$coupon_disc,
                          'coupon_code' => $request->coupon_code,
                          'coupon_discount_by' =>"Seller",
                          'coupon_applied' => 1
                        ]
                        );
                    }
                }
            }

			Cart::where('user_id', $request->user_id)->update(
                [     
                	'coupon_code' => $request->coupon_code, 
                    'coupon_applied' => 1
                ]
            );	
            

            return response()->json([
                'result' => true,
                'message' => 'Coupon has been applied'
            ]);

        }elseif($coupon->type == 'random_coupon'){

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            foreach ($cart_items as $key => $cartItem) {
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $shipping += $cartItem['shipping'] * $cartItem['quantity'];
            }
            $sum = $subtotal + $tax + $shipping;

            if ($sum >= $coupon_details->min_buy) {
                if ($coupon->discount_type == 'percent') {
                    $coupon_discount = ($sum * $coupon->discount) / 100;
                    if ($coupon_discount > $coupon_details->max_discount) {
                        $coupon_discount = $coupon_details->max_discount;
                    }
                } elseif ($coupon->discount_type == 'amount') {
                    $coupon_discount = $coupon->discount;
                }

                Cart::where('user_id', $request->user_id)->update([
                    'discount' => $coupon_discount / count($cart_items),
                    'coupon_code' => $request->coupon_code,
                    'coupon_discount_by' =>"Unikart",
                    'coupon_applied' => 1
                ]);

                return response()->json([
                    'result' => true,
                    'message' => 'Coupon has been applied'
                ]);


            }


        }


    }




    public function apply_coupon_code_24_09_2022(Request $request)
    {
        $cart_items = Cart::where('user_id', $request->user_id)->get();
        $coupon = Coupon::where('code', $request->coupon_code)->first();

        if ($cart_items->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => 'Cart is empty'
            ]);
        }

        if ($coupon == null) {
            return response()->json([
                'result' => false,
                'message' => 'Invalid coupon code!'
            ]);
        }

        $in_range = strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date;

        if (!$in_range) {
            return response()->json([
                'result' => false,
                'message' => 'Coupon expired!'
            ]);
        }

        $is_used = CouponUsage::where('user_id', $request->user_id)->where('coupon_id', $coupon->id)->first() != null;

        if ($is_used) {
            return response()->json([
                'result' => false,
                'message' => 'You already used this coupon!'
            ]);
        }


        $coupon_details = json_decode($coupon->details);

        if ($coupon->type == 'cart_base') {
            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            foreach ($cart_items as $key => $cartItem) {
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $shipping += $cartItem['shipping'] * $cartItem['quantity'];
            }
            $sum = $subtotal + $tax + $shipping;

            if ($sum >= $coupon_details->min_buy) {
                if ($coupon->discount_type == 'percent') {
                    $coupon_discount = ($sum * $coupon->discount) / 100;
                    if ($coupon_discount > $coupon_details->max_discount) {
                        $coupon_discount = $coupon_details->max_discount;
                    }
                } elseif ($coupon->discount_type == 'amount') {
                    $coupon_discount = $coupon->discount;
                }

                Cart::where('user_id', $request->user_id)->update([
                    'discount' => $coupon_discount / count($cart_items),
                    'coupon_code' => $request->coupon_code,
                    'coupon_applied' => 1
                ]);

                return response()->json([
                    'result' => true,
                    'message' => 'Coupon Applied'
                ]);


            }
        } elseif ($coupon->type == 'product_base') {
            $coupon_discount = 0;
            foreach ($cart_items as $key => $cartItem) {
                foreach ($coupon_details as $key => $coupon_detail) {
                    if ($coupon_detail->product_id == $cartItem['product_id']) {
                        if ($coupon->discount_type == 'percent') {
                            $coupon_discount += $cartItem['price'] * $coupon->discount / 100;
                        } elseif ($coupon->discount_type == 'amount') {
                            $coupon_discount += $coupon->discount;
                        }
                    }
                }
            }


            Cart::where('user_id', $request->user_id)->update([
                'discount' => $coupon_discount / count($cart_items),
                'coupon_code' => $request->coupon_code,
                'coupon_applied' => 1
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Coupon Applied'
            ]);

        }


    }

    public function remove_coupon_code(Request $request)
    {
        Cart::where('user_id', $request->user_id)->update([
            'discount' => 0.00,
            'coupon_code' => "",
            'coupon_applied' => 0
        ]);

        return response()->json([
            'result' => true,
            'message' => 'Coupon Removed'
        ]);
    }

  
}
