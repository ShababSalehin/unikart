<?php

namespace App\Http\Controllers;

use App\Utility\PayfastUtility;
use Illuminate\Http\Request;
use Auth;
use User;
use App\Category;
use App\Cart;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\InstamojoController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PublicSslCommerzPaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaytmController;
use App\Order;
use App\Coupon;
use App\CouponUsage;
use App\Address;
use App\CombinedOrder;
use App\CommissionHistory;
use App\User as AppUser;
use Session;
use App\Utility\PayhereUtility;
use App\Utility\NotificationUtility;

class CheckoutController extends Controller
{

    public function __construct()
    {
        //
    }

    //check the selected payment gateway and redirect to that controller accordingly
    public function checkout(Request $request)
    {
        if (($request->data) == false) {
            flash(translate("Please add shipping address"))->warning();
            return back();
        }

        $carts = Cart::where('user_id', Auth::user()->id)->get();

        foreach ($carts as $key => $cartItem) {
            $cartItem->address_id = $request->data['address_id']['address_key'];
            $cartItem->save();
        }
        if ($request->payment_option != null) {
            $orderController = new OrderController;
            $orderController->store($request);

            $request->session()->put('payment_type', 'cart_payment');

            if ($request->session()->get('combined_order_id') != null) {
                if ($request->payment_option == 'paypal') {
                    $paypal = new PaypalController;
                    return $paypal->getCheckout();
                } elseif ($request->payment_option == 'stripe') {
                    $stripe = new StripePaymentController;
                    return $stripe->stripe();
                } elseif ($request->payment_option == 'sslcommerz') {
                    $sslcommerz = new PublicSslCommerzPaymentController;
                    return $sslcommerz->index($request);
                } elseif ($request->payment_option == 'instamojo') {
                    $instamojo = new InstamojoController;
                    return $instamojo->pay($request);
                } elseif ($request->payment_option == 'razorpay') {
                    $razorpay = new RazorpayController;
                    return $razorpay->payWithRazorpay($request);
                } elseif ($request->payment_option == 'proxypay') {
                    //$proxy = new ProxypayController;
                    //return $proxy->create_reference($request);
                } elseif ($request->payment_option == 'paystack') {
                    if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null &&
                        \App\Addon::where('unique_identifier', 'otp_system')->first()->activated &&
                        !Auth::user()->email) {
                        flash(translate('Your email should be verified before order'))->warning();
                        return redirect()->route('cart')->send();
                    }
                    $paystack = new PaystackController;
                    return $paystack->redirectToGateway($request);
                } elseif ($request->payment_option == 'voguepay') {
                    $voguePay = new VoguePayController;
                    return $voguePay->customer_showForm();
                } elseif ($request->payment_option == 'payhere') {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));

                    $combined_order_id = $combined_order->id;
                    $amount = $combined_order->grand_total;
                    $first_name = json_decode($combined_order->shipping_address)->name;
                    $last_name = 'X';
                    $phone = json_decode($combined_order->shipping_address)->phone;
                    $email = json_decode($combined_order->shipping_address)->email;
                    $address = json_decode($combined_order->shipping_address)->address;
                    $city = json_decode($combined_order->shipping_address)->city;

                    return PayhereUtility::create_checkout_form($combined_order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
                } elseif ($request->payment_option == 'payfast') {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));

                    $combined_order_id = $combined_order->id;
                    $amount = $combined_order->grand_total;

                    return PayfastUtility::create_checkout_form($combined_order_id, $amount);
                } else if ($request->payment_option == 'ngenius') {
                    $ngenius = new NgeniusController();
                    return $ngenius->pay();
                } else if ($request->payment_option == 'iyzico') {
                    $iyzico = new IyzicoController();
                    return $iyzico->pay();
                } else if ($request->payment_option == 'nagad') {
                    $nagad = new NagadController;
                    return $nagad->getSession();
                } else if ($request->payment_option == 'bkash') {
                    $bkash = new BkashController;
                    return $bkash->pay();
                } else if ($request->payment_option == 'aamarpay') {
                    $aamarpay = new AamarpayController;
                    return $aamarpay->index();
                } else if ($request->payment_option == 'flutterwave') {
                    $flutterwave = new FlutterwaveController();
                    return $flutterwave->pay();
                } else if ($request->payment_option == 'mpesa') {
                    $mpesa = new MpesaController();
                    return $mpesa->pay();
                } elseif ($request->payment_option == 'paytm') {
                    if (Auth::user()->phone == null) {
                        flash('Please add phone number to your profile')->warning();
                        return redirect()->route('profile');
                    }

                    $paytm = new PaytmController;
                    return $paytm->index();
                } elseif ($request->payment_option == 'cash_on_delivery') {
                    flash(translate("Your order has been placed successfully"))->success();
                    return redirect()->route('order_confirmed');
                } elseif ($request->payment_option == 'wallet') {
                    $user = Auth::user();
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                    if ($user->balance >= $combined_order->grand_total) {
                        $user->balance -= $combined_order->grand_total;
                        $user->save();
                        return $this->checkout_done($request->session()->get('combined_order_id'), null);
                    }
                } else {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                    foreach ($combined_order->orders as $order) {
                        $order->manual_payment = 1;
                        $order->save();
                    }
                    flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
                    return redirect()->route('order_confirmed');
                }
            }
        } else {
            flash(translate('Select Payment Option.'))->warning();
            return back();
        }
    }

    //redirects to this method after a successfull checkout
    public function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();

            calculateCommissionAffilationClubPoint($order);
        }

        Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('order_confirmed');
    }

    public function get_shipping_info(Request $request)
    {
        $carts = Cart::where('user_id', Auth::user()->id)->get();
//        if (Session::has('cart') && count(Session::get('cart')) > 0) {
        if ($carts && count($carts) > 0) {
            $categories = Category::all();
            return view('frontend.shipping_info', compact('categories', 'carts'));
        }
        flash(translate('Your cart is empty'))->success();
        return back();
    }

    public function store_shipping_info_old(Request $request)
    {
        if ($request->address_id == null) {
            flash(translate("Please add shipping address"))->warning();
            return back();
        }

        $carts = Cart::where('user_id', Auth::user()->id)->get();

        foreach ($carts as $key => $cartItem) {
            $cartItem->address_id = $request->address_id;
            $cartItem->save();
        }

        return view('frontend.delivery_info', compact('carts'));
        // return view('frontend.payment_select', compact('total'));
    }

                  // store_delivery_info rename
    public function store_shipping_info(Request $request)
    {

        // if ($request->address_id == null) {
        //     flash(translate("Please add shipping address"))->warning();
        //     return back();
        // }

        // $carts = Cart::where('user_id', Auth::user()->id)->get();

        // foreach ($carts as $key => $cartItem) {
        //     $cartItem->address_id = $request->address_id;
        //     $cartItem->save();
        // }


        $carts = Cart::where('user_id', Auth::user()->id)
                ->get();

        if($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $shipping_info = '';//Address::where('id', $carts[0]['address_id'])->first();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        if ($carts && count($carts) > 0) {
            foreach ($carts as $key => $cartItem) {
                $product = \App\Product::find($cartItem['product_id']);
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $subtotal += $cartItem['price'] * $cartItem['quantity'];

                if ($request['shipping_type_' . $product->user_id] == 'pickup_point') {
                    $cartItem['shipping_type'] = 'pickup_point';
                    $cartItem['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
                } else {
                    $cartItem['shipping_type'] = 'home_delivery';
                }
                $cartItem['shipping_cost'] = 0;
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                	$cartItem['unicart_shipping_cost'] = getUnikartShippingCost($carts, $key);// added by alauddin
                }

                if(isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {

                    // foreach(json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                    //     if($shipping_info['city'] == $shipping_region) {
                    //         $cartItem['shipping_cost'] = (double)($val);
                    //         break;
                    //     } else {
                            $cartItem['shipping_cost'] = 0;
                    //     }
                    // }
                } else {
                    if (!$cartItem['shipping_cost'] ||
                            $cartItem['shipping_cost'] == null ||
                            $cartItem['shipping_cost'] == 'null') {

                        $cartItem['shipping_cost'] = 0;
                    }
                }

                $shipping += $cartItem['shipping_cost'];
                $cartItem->save();

            }

            $shipping_skip_total = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost_total')->first()->value;
            if($shipping_skip_total>$subtotal){
                $shipping = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
            }else{
                    $shipping = 0;
                    }
                    
            $total = $subtotal + $tax + $shipping;
            $categories = Category::all();
            return view('frontend.payment_select', compact('carts', 'shipping_info', 'total','categories'));

        } else {
            flash(translate('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }


    
public function apply_coupon_code(Request $request)
{
    if($request->areawiseshipping){
        $areawiseshipping = $request->areawiseshipping;
    }else{
        $areawiseshipping = 0;
    }

    $coupon = Coupon::where('code', $request->code)->first();
    $seller = AppUser::where('id',$coupon->user_id)->first();

    $response_message = array();

    if ($coupon != null) {
        if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
            if (CouponUsage::where('user_id', Auth::user()->id)->where('coupon_id', $coupon->id)->first() == null) {
                $coupon_details = json_decode($coupon->details);

                 $carts = Cart::where('user_id', Auth::user()->id)->get();
                 
                 $seller_coupons = Cart::where('owner_id',$coupon->user_id)->where('user_id',Auth::user()->id)->first();
                 //dd($seller_coupons);

                if ($coupon->type == 'cart_base') {
                   
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                         //unikart coupon or seller coupon
                    if(($coupon->user_id == 9) || ($seller_coupons == !null)){
                      
                        if($coupon->user_id == 9){
                             //unikart coupon apply
                            foreach ($carts as $key => $cartItem) {
                                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                                $tax += $cartItem['tax'] * $cartItem['quantity'];
                                $shipping += $cartItem['shipping_cost'];
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
                                $response_message['response'] = 'success';
                                $response_message['message'] = translate('Coupon has been applied');
    
                            }else{
                                $response_message['response'] = 'warning';
                                $response_message['message'] = translate('Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon');
                            }
                        }else{

                          // seller coupon apply
                            $sellercarts = Cart::where('owner_id',$coupon->user_id)->where('user_id',Auth::user()->id)->get();

                            foreach ($sellercarts as $key => $cartItem) {
                                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                                $tax += $cartItem['tax'] * $cartItem['quantity'];
                                $shipping += $cartItem['shipping_cost'];
                            }
                            $sum = $subtotal;
                            
                            if ($sum >= $coupon_details->min_buy) {
                                if ($coupon->discount_type == 'percent') {
                                    $coupon_discount = ($sum * $coupon->discount) / 100;
                                    if ($coupon_discount > $coupon_details->max_discount) {
                                        $coupon_discount = $coupon_details->max_discount;
                                    }
                                }elseif ($coupon->discount_type == 'amount') {
                                    $coupon_discount = $coupon->discount;
                                }

                                $response_message['response'] = 'success';
                                $response_message['message'] = translate('Coupon has been applied');
    
                            }else{
                                $response_message['response'] = 'warning';
                                $response_message['message'] = translate('Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon');
                            }
                        }

                    }else{
                        $response_message['response'] = 'warning';
                        $response_message['message'] = translate('Sorry this coupon is applicable for'.''.$seller->name);
                    }


                }elseif($coupon->type == 'first_order_base'){
                    $carts_offer_check = Cart::where('user_id', Auth::user()->id)->where('offer_status',1)->get(); //added by alauddin
                    if(count($carts_offer_check) == 1){
                        $response_message['response'] = 'danger';
                        $response_message['message'] = translate('Use coupon for regular items only');
                      
                        $carts = Cart::where('user_id', Auth::user()->id)->get();
                        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        
                      $returnHTML = view('frontend.partials.cart_summary', compact('areawiseshipping','coupon', 'carts', 'shipping_info'))->render();
                   return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
                    }else{

                        $first_order_check = Order::where('user_id', Auth::user()->id)
                        ->where('delivery_status','!=','cancelled')->get();

                        if(count($first_order_check) == 0)
                        {$subtotal = 0;
                            $tax = 0;
                            $shipping = 0;
                            foreach ($carts as $key => $cartItem) {
                                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                                $tax += $cartItem['tax'] * $cartItem['quantity'];
                                $shipping += $cartItem['shipping_cost'];
                            }
                            $sum = $subtotal + $tax ;
                            //dd($sum);
                            if ($sum >= $coupon_details->min_buy) {
                                if ($coupon->discount_type == 'percent') {
                                    $coupon_discount = ($sum * $coupon->discount) / 100;
                                    if ($coupon_discount > $coupon_details->max_discount) {
                                        $coupon_discount = $coupon_details->max_discount;
                                    }
                                } elseif ($coupon->discount_type == 'amount') {
                                    $coupon_discount = $coupon->discount;
                               
                                }
                                $response_message['response'] = 'success';
                                $response_message['message'] = translate('Coupon has been applied');
                            }else{
                                $response_message['response'] = 'warning';
                                $response_message['message'] = translate('Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon');
                            } 
                        }else{
                            $response_message['response'] = 'danger';
                            $response_message['message'] = translate('Coupon has been used');
                            $carts = Cart::where('user_id', Auth::user()->id)
                                            ->get();
                            $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

                            $returnHTML = view('frontend.partials.cart_summary', compact('areawiseshipping','coupon', 'carts', 'shipping_info'))->render();
                            return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
                        }
                    }
                   

                } elseif($coupon->type == 'random_coupon'){
                   
                    $use_check = CouponUsage::where('coupon_id', $coupon->id)->get();
                    if (count($use_check) <=200) {

                        $subtotal = 0;
                        $tax = 0;
                        
                       //dd($areawiseshipping);
                        foreach ($carts as $key => $cartItem) {
                            $subtotal += $cartItem['price'] * $cartItem['quantity'];
                            $tax += $cartItem['tax'] * $cartItem['quantity'];
                            
                        }
                        $sum = $subtotal + $tax;
                        
                        if ($sum >= $coupon_details->min_buy) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            }elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                           
                            }


                            $response_message['response'] = 'success';
                            $response_message['message'] = translate('Coupon has been applied');
                        }else{
                            $response_message['response'] = 'warning';
                            $response_message['message'] = translate('Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon');
                        }
                                 

                    }else{
                        
                        $response_message['response'] = 'warning';
                        $response_message['message'] = translate('Coupon has been used');
                        $carts = Cart::where('user_id', Auth::user()->id)->get();
                        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
                       
                 $returnHTML = view('frontend.partials.cart_summary', compact('areawiseshipping','coupon', 'carts', 'shipping_info'))->render();
                 return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
                    }

                }elseif ($coupon->type == 'product_base') {
                   // $coupon_discount = 0;
                    $coupon_discount = '';
                    foreach ($carts as $key => $cartItem) {
                        foreach ($coupon_details as $key => $coupon_detail) {
                            if ($coupon_detail->product_id == $cartItem['product_id']) {
                                $coupon_disc=0;
                                if ($coupon->discount_type == 'percent') {
                                    //$coupon_discount += ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                    $coupon_disc= ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                } elseif ($coupon->discount_type == 'amount') {
                                    $coupon_disc= $coupon->discount * $cartItem['quantity'];
                                }

                                Cart::where('user_id', Auth::user()->id)->where('id',$cartItem['id'])->update(
                                    [
                                        'discount' =>$coupon_disc,
                                        'coupon_code' => $request->code,
                                        'coupon_discount_by' =>"Seller",
                                        'coupon_applied' => 1
                                    ]
                                );

                            }
                        }
                    }

                    $response_message['response'] = 'success';
                    $response_message['message'] = translate('Coupon has been applied');

                }
                  
                if(!empty($coupon_discount)){
                    if($coupon->user_id == 9){
                        Cart::where('user_id', Auth::user()->id)->update([
                                'discount' => $coupon_discount / count($carts),
                                'coupon_code' => $request->code,
                                'coupon_discount_by' =>"Unikart",
                                'coupon_applied' => 1
                            ]);

                    }else{
                            Cart::where('owner_id', $coupon->user_id)->update([
                                'discount' => $coupon_discount / count($sellercarts),
                                'coupon_code' => $request->code,
                                'coupon_discount_by' => $seller->name,
                                'coupon_applied' => 1
                            ]);
                    }

                }
               
            }else{
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('Coupon has been used');
            }
        }else{
            $response_message['response'] = 'warning';
            $response_message['message'] = translate('Coupon expired!');
        }
     }else{
        
        $response_message['response'] = 'danger';
        $response_message['message'] = translate('Invalid coupon!');
        
     }

     $carts = Cart::where('user_id', Auth::user()->id)->get();
     $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

     $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info','areawiseshipping'))->render();
     return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
}



public function apply_coupon_code_old(Request $request)
    {

        if($request->areawiseshipping){
            $areawiseshipping = $request->areawiseshipping;
        }else{
            $areawiseshipping = 0;
        }

        $coupon = Coupon::where('code', $request->code)->first();
        $response_message = array();

        if ($coupon != null) {
            if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                if (CouponUsage::where('user_id', Auth::user()->id)->where('coupon_id', $coupon->id)->first() == null) {
                    $coupon_details = json_decode($coupon->details);

                    $carts = Cart::where('user_id', Auth::user()->id)->get();
                    if ($coupon->type == 'cart_base') {

                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach ($carts as $key => $cartItem) {
                            $subtotal += $cartItem['price'] * $cartItem['quantity'];
                            $tax += $cartItem['tax'] * $cartItem['quantity'];
                            $shipping += $cartItem['shipping_cost'];
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
                            $response_message['response'] = 'success';
                            $response_message['message'] = translate('Coupon has been applied');
                        }else{
                            $response_message['response'] = 'warning';
                            $response_message['message'] = translate('Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon');
                        }

                    } elseif($coupon->type == 'first_order_base'){
                        $carts_offer_check = Cart::where('user_id', Auth::user()->id)->where('offer_status',1)->get(); //added by alauddin
                        if(count($carts_offer_check) == 1){
                            $response_message['response'] = 'danger';
                            $response_message['message'] = translate('Use coupon for regular items only');
                          
                            $carts = Cart::where('user_id', Auth::user()->id)
                            ->get();
                    $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
            
                    $returnHTML = view('frontend.partials.cart_summary', compact('areawiseshipping','coupon', 'carts', 'shipping_info'))->render();
                    return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
                        }else{

                            $first_order_check = Order::where('user_id', Auth::user()->id)
                            ->where('delivery_status','!=','cancelled')->get();

                            if(count($first_order_check) == 0)
                            {$subtotal = 0;
                                $tax = 0;
                                $shipping = 0;
                                foreach ($carts as $key => $cartItem) {
                                    $subtotal += $cartItem['price'] * $cartItem['quantity'];
                                    $tax += $cartItem['tax'] * $cartItem['quantity'];
                                    $shipping += $cartItem['shipping_cost'];
                                }
                                $sum = $subtotal + $tax ;
                                //dd($sum);
                                if ($sum >= $coupon_details->min_buy) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount = ($sum * $coupon->discount) / 100;
                                        if ($coupon_discount > $coupon_details->max_discount) {
                                            $coupon_discount = $coupon_details->max_discount;
                                        }
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount = $coupon->discount;
                                   
                                    }
                                    $response_message['response'] = 'success';
                                    $response_message['message'] = translate('Coupon has been applied');
                                }else{
                                    $response_message['response'] = 'warning';
                                    $response_message['message'] = translate('Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon');
                                } 
                            }else{
                                $response_message['response'] = 'danger';
                                $response_message['message'] = translate('Coupon has been used');
                                $carts = Cart::where('user_id', Auth::user()->id)
                                                ->get();
                                $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

                                $returnHTML = view('frontend.partials.cart_summary', compact('areawiseshipping','coupon', 'carts', 'shipping_info'))->render();
                                return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
                            }
                        }
                       

                    } elseif($coupon->type == 'random_coupon'){
                          $use_check = CouponUsage::where('coupon_id', $coupon->id)->get();
                          //dd($use_check);
                        if (count($use_check) <=200) {
    
                           $subtotal = 0;
                            $tax = 0;
                            $shipping = 0;
                            //dd($carts);
                            foreach ($carts as $key => $cartItem) {
                                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                                $tax += $cartItem['tax'] * $cartItem['quantity'];
                                $shipping += $cartItem['shipping_cost'];
                            }
                            $sum = $subtotal + $tax;
                            //dd($sum);
                            if ($sum >= $coupon_details->min_buy) {
                                if ($coupon->discount_type == 'percent') {
                                    $coupon_discount = ($sum * $coupon->discount) / 100;
                                    if ($coupon_discount > $coupon_details->max_discount) {
                                        $coupon_discount = $coupon_details->max_discount;
                                    }
                                } elseif ($coupon->discount_type == 'amount') {
                                    $coupon_discount = $coupon->discount;
                               
                                }


                                $response_message['response'] = 'success';
                                $response_message['message'] = translate('Coupon has been applied');
                            }else{
                                $response_message['response'] = 'warning';
                                $response_message['message'] = translate('Minimum purchase is TK '.$coupon_details->min_buy.' to avail this coupon');
                            }
                        }else{
                            $response_message['response'] = 'warning';
                            $response_message['message'] = translate('Coupon has been used');
                            $carts = Cart::where('user_id', Auth::user()->id)
                            ->get();
                    $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
            
                    $returnHTML = view('frontend.partials.cart_summary', compact('areawiseshipping','coupon', 'carts', 'shipping_info'))->render();
                    return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
                        }

                    }elseif ($coupon->type == 'product_base') {
                       // $coupon_discount = 0;
                        $coupon_discount = '';
                        foreach ($carts as $key => $cartItem) {
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    $coupon_disc=0;
                                    if ($coupon->discount_type == 'percent') {
                                        //$coupon_discount += ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                        $coupon_disc= ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_disc= $coupon->discount * $cartItem['quantity'];
                                    }

                                    Cart::where('user_id', Auth::user()->id)->where('id',$cartItem['id'])->update(
                                        [
                                            'discount' =>$coupon_disc,
                                            'coupon_code' => $request->code,
                                            'coupon_discount_by' =>"Seller",
                                            'coupon_applied' => 1
                                        ]
                                    );

                                }
                            }
                        }

                        $response_message['response'] = 'success';
                        $response_message['message'] = translate('Coupon has been applied');

                    }
                      
                    if(!empty($coupon_discount)){
                        Cart::where('user_id', Auth::user()->id)->update(
                            [
                                'discount' => $coupon_discount / count($carts),
                                'coupon_code' => $request->code,
                                'coupon_discount_by' =>"Unikart",
                                'coupon_applied' => 1
                            ]
                            );
                    }
                   
                   // $response_message['response'] = 'success';
                   // $response_message['message'] = translate('Coupon has been applied');
                } else {
                    $response_message['response'] = 'warning';
                    $response_message['message'] = translate('Coupon has been used');
                }
            } else {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('Coupon expired!');
            }
        } else {
            $response_message['response'] = 'danger';
            $response_message['message'] = translate('Invalid coupon!');
        }

        $carts = Cart::where('user_id', Auth::user()->id)
                ->get();
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        $returnHTML = view('frontend.partials.cart_summary', compact('areawiseshipping','coupon', 'carts', 'shipping_info'))->render();
        return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
    }


    public function remove_coupon_code(Request $request)
    {
        Cart::where('user_id', Auth::user()->id)
                ->update(
                        [
                            'discount' => 0.00,
                            'coupon_code' => '',
                            'coupon_applied' => 0
                        ]
        );

        $coupon = Coupon::where('code', $request->code)->first();
        $carts = Cart::where('user_id', Auth::user()->id)
                ->get();

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        return view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'));
    }

    public function apply_club_point(Request $request) {
        if (addon_is_activated('club_point')){

            $point = $request->point;

            if(Auth::user()->point_balance >= $point) {
                $request->session()->put('club_point', $point);
                flash(translate('Point has been redeemed'))->success();
            }
            else {
                flash(translate('Invalid point!'))->warning();
            }
        }
        return back();
    }

    public function remove_club_point(Request $request) {
        $request->session()->forget('club_point');
        return back();
    }

    public function order_confirmed()
    {
        $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

        Cart::where('user_id', $combined_order->user_id)
                ->delete();

        //Session::forget('club_point');
        //Session::forget('combined_order_id');
        $m=0;
        // foreach($combined_order->orders as $order){
        // 	$m++;
        //     NotificationUtility::sendOrderPlacedNotification($order,$m);
        // }

        return view('frontend.order_confirmed', compact('combined_order'));
    }
public function sendNotificationAjax()
    {
		$combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

		if($combined_order->notification_sent==0){
        	$combined_order->notification_sent = 1;
        	$combined_order->save();
        	$m=0;
        	foreach($combined_order->orders as $order){
            	$m++;
            	if($m==1)
            		NotificationUtility::sendOrderPlacedNotification($order,$m);
       		}
        	
        }
		echo 'Success';
    }
}
