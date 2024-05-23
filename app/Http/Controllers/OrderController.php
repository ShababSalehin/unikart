<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\OTPVerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\ClubPointController;
use App\Order;
use App\Cart;
use App\FlashDeal; //added by alauddin
use App\City; //added by alauddin
use App\Address;
use App\Product;
use App\ProductStock;
use App\CommissionHistory;
use App\Color;
use App\OrderDetail;
use App\CouponUsage;
use App\Coupon;
use App\OtpConfiguration;
use App\User;
use App\BusinessSetting;
use App\CombinedOrder;
use App\SmsTemplate;
use Session;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Utility\NotificationUtility;
use CoreComponentRepository;
use App\Utility\SmsUtility;
use Xenon\MultiCourier\Provider\Redx;
use Xenon\MultiCourier\Courier;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource to seller.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $orders = DB::table('orders')
            ->orderBy('id', 'desc')
            //->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('seller_id', Auth::user()->id)
            ->select('orders.id')
            ->distinct();

        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }

        $orders = $orders->paginate(15);

        foreach ($orders as $key => $value) {
            $order = \App\Order::find($value->id);
            $order->viewed = 1;
            $order->save();
        }

        return view('frontend.user.seller.orders', compact('orders', 'payment_status', 'delivery_status', 'sort_search'));
    }


    public function orders_successfull(Request $request)
    {
    
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $orders = DB::table('orders')
            ->orderBy('id', 'desc')
            //->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('seller_id', Auth::user()->id)
            ->where('delivery_status', 'Delivered')
            ->select('orders.id')
            ->distinct();

        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }

        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }

        $orders = $orders->paginate(15);

        foreach ($orders as $key => $value) {
            $order = \App\Order::find($value->id);
            $order->viewed = 1;
            $order->save();
        }

        return view('frontend.user.seller.orders', compact('orders', 'payment_status', 'delivery_status', 'sort_search'));
    }

    // All Orders
    public function all_orders(Request $request)
    {

      
        $from_id = 10454;
        $to_id = 11894;

    //     $products = Product::get();
    // //dd($products);
	// 	foreach($products as $prod){
    //     	$comission = get_setting('vendor_commission');
    //     	$mrp = $prod->unit_price;
    //     	if($prod->discount_type=='amount'){
    //     		$mrp = $mrp-$prod->discount;
    // 		}else{
    //     		$mrp = $mrp-($mrp*$prod->discount)/100;
    // 		}
    //     	$unikart_earning = $mrp*$comission/100;
    //     	$trade_price = $mrp-$unikart_earning;
        
    //     	$prod->unikart_earning = $unikart_earning;
    //     	$prod->trade_price = $trade_price;
    //     	$prod->save();
    //     }

        CoreComponentRepository::instantiateShopRepository();

        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;

        $orders = Order::orderBy('id', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }else{
        	//$orders = $orders->where('delivery_status','!=','cancelled');
        	//$orders = $orders->where('delivery_status','=','pending');
        	if (empty($request->has('search'))) {
                $orders = $orders->where('delivery_status','=','pending');
            }else{
            	
            }
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }
        $orders = $orders->paginate(15);
    	
    
        return view('backend.sales.all_orders.index', compact('orders', 'sort_search', 'delivery_status', 'date'));
    }

    public function all_orders_show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = '';
        // $delivery_boys = User::where('city', $order_shipping_address->city)
        //     ->where('user_type', 'delivery_boy')
        //     ->get();

        return view('backend.sales.all_orders.show', compact('order', 'delivery_boys'));
    }


// Added by alauddin start
    public function all_combined_orders_show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = '';
        $combined_order = CombinedOrder::findOrFail($order->combined_order_id);
        // $delivery_boys = User::where('city', $order_shipping_address->city)
        //     ->where('user_type', 'delivery_boy')
        //     ->get();

        return view('backend.sales.all_orders.combined_show', compact('order', 'delivery_boys','combined_order'));
    }
// Added by alauddin end

    public function reason_cancel_order(Request $request){

        $order = Order::where('code','=',$request->code)->first();
        $order->cancel_reason = $request->cancel_reason;
      if ($order->save() ) {
            $array['view'] = 'emails.invoice';
            $array['subject'] = ('You order is Cancelled for') . ' - ' . $order->cancel_reason . ' - ' . $order->code;
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['order'] = $order;
            

            try {
                Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
            } catch (\Exception $e) {

            }
        }
        return back();
}

    // Inhouse Orders
    public function admin_orders(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $date = $request->date;
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        $orders = Order::orderBy('id', 'desc')
                        ->where('seller_id', $admin_user_id);

        if ($request->payment_type != null) {
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($date != null) {
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        $orders = $orders->paginate(15);
        return view('backend.sales.inhouse_orders.index', compact('orders', 'payment_status', 'delivery_status', 'sort_search', 'admin_user_id', 'date'));
    }

    public function show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', $order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        $order->viewed = 1;
        $order->save();
        return view('backend.sales.inhouse_orders.show', compact('order', 'delivery_boys'));
    }

    // Seller Orders
    public function seller_orders(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $date = $request->date;
        $seller_id = $request->seller_id;
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        $orders = Order::orderBy('code', 'desc')
            ->where('orders.seller_id', '!=', $admin_user_id);

        if ($request->payment_type != null) {
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($date != null) {
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }
        if ($seller_id) {
            $orders = $orders->where('seller_id', $seller_id);
        }

        $orders = $orders->paginate(15);
        return view('backend.sales.seller_orders.index', compact('orders', 'payment_status', 'delivery_status', 'sort_search', 'admin_user_id', 'seller_id', 'date'));
    }

    public function seller_orders_show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order->viewed = 1;
        $order->save();
        return view('backend.sales.seller_orders.show', compact('order'));
    }


    // Pickup point orders
    public function pickup_point_order_index(Request $request)
    {
        $date = $request->date;
        $sort_search = null;

        if (Auth::user()->user_type == 'staff' && Auth::user()->staff->pick_up_point != null) {
            $orders = DB::table('orders')
                ->orderBy('code', 'desc')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->where('order_details.pickup_point_id', Auth::user()->staff->pick_up_point->id)
                ->select('orders.id')
                ->distinct();

            if ($request->has('search')) {
                $sort_search = $request->search;
                $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            }
            if ($date != null) {
                $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
            }

            $orders = $orders->paginate(15);

            return view('backend.sales.pickup_point_orders.index', compact('orders', 'sort_search', 'date'));
        } else {
            $orders = DB::table('orders')
                ->orderBy('code', 'desc')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->where('order_details.shipping_type', 'pickup_point')
                ->select('orders.id')
                ->distinct();

            if ($request->has('search')) {
                $sort_search = $request->search;
                $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            }
            if ($date != null) {
                $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
            }

            $orders = $orders->paginate(15);

            return view('backend.sales.pickup_point_orders.index', compact('orders', 'sort_search', 'date'));
        }
    }

    public function pickup_point_order_sales_show($id)
    {
        if (Auth::user()->user_type == 'staff') {
            $order = Order::findOrFail(decrypt($id));
            $order_shipping_address = json_decode($order->shipping_address);
            $delivery_boys = User::where('city', $order_shipping_address->city)
                ->where('user_type', 'delivery_boy')
                ->get();

            return view('backend.sales.pickup_point_orders.show', compact('order', 'delivery_boys'));
        } else {
            $order = Order::findOrFail(decrypt($id));
            $order_shipping_address = json_decode($order->shipping_address);
            $delivery_boys = User::where('city', $order_shipping_address->city)
                ->where('user_type', 'delivery_boy')
                ->get();

            return view('backend.sales.pickup_point_orders.show', compact('order', 'delivery_boys'));
        }
    }

    /**
     * Display a single sale to admin.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */


public function store(Request $request)
    {
		DB::beginTransaction();
        try {
        //$order_check = Order::where('user_id', Auth::user()->id)->get(); //added by alauddin



		// Saving shipping cost start    
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();
        foreach ($carts as $key => $cartItem) {
            $product = \App\Product::find($cartItem['product_id']);
            

            if ($request['shipping_type_' . $product->user_id] == 'pickup_point') {
                $cartItem['shipping_type'] = 'pickup_point';
                $cartItem['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
            } else {
                $cartItem['shipping_type'] = 'home_delivery';
            }
            
            $cartItem['shipping_cost'] = 0;
            if ($cartItem['shipping_type'] == 'home_delivery') {
                $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                $cartItem['unicart_shipping_cost'] = getUnikartShippingCost($carts, $key); //added by alauddin
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

            
            $cartItem->save();

        }


// Saving shipping cost end   


		// Saving shipping cost start    
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();
        foreach ($carts as $key => $cartItem) {
            $product = \App\Product::find($cartItem['product_id']);
            

            if ($request['shipping_type_' . $product->user_id] == 'pickup_point') {
                $cartItem['shipping_type'] = 'pickup_point';
                $cartItem['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
            } else {
                $cartItem['shipping_type'] = 'home_delivery';
            }
            
            $cartItem['shipping_cost'] = 0;
            if ($cartItem['shipping_type'] == 'home_delivery') {
                $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                $cartItem['unicart_shipping_cost'] = getUnikartShippingCost($carts, $key); //added by alauddin
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

            
            $cartItem->save();

        }


// Saving shipping cost end   




        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();
        
        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $shipping_info->name = Auth::user()->name;
        $shipping_info->email = Auth::user()->email;
        if ($shipping_info->latitude || $shipping_info->longitude) {
            $shipping_info->lat_lang = $shipping_info->latitude . ',' . $shipping_info->longitude;
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = Auth::user()->id;
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
            $order = new Order();
            $order->combined_order_id = $combined_order->id;
            $order->user_id = Auth::user()->id;
            $order->shipping_address = json_encode($shipping_info);
            $order->special_instruction = $request->special_instruction;
            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
          //  $order->code = date('Ymd-His') . rand(10, 99);
          //  $order->code = date('Ymd') .'-'.(count( $all_orders)+1);
            $order->date = strtotime('now');
            $order->admin_shipping_cost = get_setting('flat_rate_shipping_cost');
            $order->order_from ="Web";

            $first_order_check = Order::where('user_id',Auth::user()->id)
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
        
            $coupon_code_check=''; //added by alauddin

            //Order Details Storing
            foreach ($seller_product as $cartItem) {
            
            	if(!empty($cartItem['coupon_code'])){
                        $coupon_code_check=$cartItem['coupon_code'];
                }
            
            
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
                    flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                    $order->delete();
                    return redirect()->route('cart')->send();
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

                $order_detail->discount =$disc+$unic_dis;
                $order_detail->total_discount = $disc+$unic_dis;

                $order_detail->product_unit_price =$product->unit_price; //added by alauddin
                $order_detail->unikart_discount =$unic_dis; //added by alauddin

                //$order_detail->due_to_seller = $product->trade_price*$cartItem['quantity'];
                // $order_detail->unikart_earning = $product->unikart_earning*$cartItem['quantity'];

                //added by alauddin start
                
               
                $t_mp_price=$product->unit_price*$cartItem['quantity'];
                $sale_price=$t_mp_price-$disc;
                $unicart_commission=($sale_price*$product->comission)/100;
               
                $trade_price=$sale_price-$unicart_commission;
                
                $unikart_earning=$unicart_commission-$unic_dis;
                
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

        	if(!empty($coupon_code_check)) {  //added by alauddin
            	//if ($seller_product[0]->coupon_code != null) {
                // if (Session::has('club_point')) {
                //     $order->club_point = Session::get('club_point');
                // }
                $order->coupon_discount = $coupon_discount;
                $order->unikart_coupon_discount =$uni_coupon_discount; //added by alauddin
                $order->seller_coupon_discount = $seller_coupon_discount; //added by alauddin    


                $order->grand_total -= $coupon_discount;

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->order_id = $order->id;
                //$coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
            	$coupon_usage->coupon_id = Coupon::where('code',$coupon_code_check)->first()->id; //added by alauddin
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
        // $array['view'] = 'emails.invoice';
        // $array['subject'] = translate('Your order has been placed. Order code') . ' - ' . $order->code;
        // $array['from'] = env('MAIL_FROM_ADDRESS');
        // $array['order'] = $order;

        // try {
        //     Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
        //     Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
        // } catch (\Exception $e) {

        // }
        $request->session()->put('combined_order_id', $combined_order->id);
        
        DB::commit();
        
       }catch(\Exception $e){
            DB::rollback();
            // something went wrong
       }       

    }

public function store_20_19_2022(Request $request)
    {
        //$order_check = Order::where('user_id', Auth::user()->id)->get(); //added by alauddin
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();
        
        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $shipping_info->name = Auth::user()->name;
        $shipping_info->email = Auth::user()->email;
        if ($shipping_info->latitude || $shipping_info->longitude) {
            $shipping_info->lat_lang = $shipping_info->latitude . ',' . $shipping_info->longitude;
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = Auth::user()->id;
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
            $order->user_id = Auth::user()->id;
            $order->shipping_address = json_encode($shipping_info);

            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            //$order->code = date('Ymd-His') . rand(10, 99);
        	//$order->code = date('Ymd') .'-'.(count( $all_orders)+1);
            $order->date = strtotime('now');
            $order->admin_shipping_cost = get_setting('flat_rate_shipping_cost');
            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;
            $uni_coupon_discount=0;
            $seller_coupon_discount=0;
            $item_discount = 0;
            $total_shipping=0;

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
                    flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                    $order->delete();
                    return redirect()->route('cart')->send();
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

                $order_detail->discount =$disc+$unic_dis;
                $order_detail->total_discount = $disc+$unic_dis;

                $order_detail->product_unit_price =$product->unit_price; //added by alauddin
                $order_detail->unikart_discount =$unic_dis; //added by alauddin

                //$order_detail->due_to_seller = $product->trade_price*$cartItem['quantity'];
                // $order_detail->unikart_earning = $product->unikart_earning*$cartItem['quantity'];

                //added by alauddin start
                
               
                $t_mp_price=$product->unit_price*$cartItem['quantity'];
                $sale_price=$t_mp_price-$disc;
                $unicart_commission=($sale_price*$product->comission)/100;
               
                $trade_price=$sale_price-$unicart_commission;
                
                $unikart_earning=$unicart_commission-$unic_dis;
                
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
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->order_id = $order->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }
            $order->item_discount = $item_discount;
            $combined_order->grand_total += $order->grand_total;
        
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
        // $array['view'] = 'emails.invoice';
        // $array['subject'] = translate('Your order has been placed. Order code') . ' - ' . $order->code;
        // $array['from'] = env('MAIL_FROM_ADDRESS');
        // $array['order'] = $order;

        // try {
        //     Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
        //     Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
        // } catch (\Exception $e) {

        // }
        $request->session()->put('combined_order_id', $combined_order->id);

    }






    public function store_14_09_2022(Request $request)
    {
        //$order_check = Order::where('user_id', Auth::user()->id)->get(); //added by alauddin
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();
        
        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $shipping_info->name = Auth::user()->name;
        $shipping_info->email = Auth::user()->email;
        if ($shipping_info->latitude || $shipping_info->longitude) {
            $shipping_info->lat_lang = $shipping_info->latitude . ',' . $shipping_info->longitude;
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = Auth::user()->id;
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

        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = Auth::user()->id;
            $order->shipping_address = json_encode($shipping_info);

            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            $order->admin_shipping_cost = get_setting('flat_rate_shipping_cost');
            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;
            $item_discount = 0;

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
                    //Added by alauddin start  
                
               
                
                  


                    if($cartItem['offer_status']==1){
                            
                        $disc = $cartItem['offer_discount_amount'];
                        $unic_dis = $cartItem['unicart_discount_amount'];                        
                           
                    }



                    
                    //Added by alauddin end  
                    
                    
                    
                }

                
                //added by alauddin end

               
                $item_discount+=$disc+$unic_dis;

                
                if($cartItem['offer_status']==1){
                    $subtotal += ($product->unit_price* $cartItem['quantity'])-($disc+$unic_dis); //added by alauddin
                }else{
                    $subtotal +=($cartItem['price'] * $cartItem['quantity']);
                }

                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $coupon_discount += $cartItem['discount'];

                $product_variation = $cartItem['variation'];

                $product_stock = $product->stocks->where('variant', $product_variation)->first();
                if (!empty($product_stock)  && $product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                    flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                    $order->delete();
                    return redirect()->route('cart')->send();
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

                $order_detail->discount =$disc+$unic_dis;
                $order_detail->total_discount =$disc+$unic_dis;

                $order_detail->product_unit_price =$product->unit_price; //added by alauddin
                $order_detail->unikart_discount =$unic_dis; //added by alauddin

                //$order_detail->due_to_seller = $product->trade_price*$cartItem['quantity'];
                // $order_detail->unikart_earning = $product->unikart_earning*$cartItem['quantity'];

                //added by alauddin start
                $t_mp_price=$product->unit_price*$cartItem['quantity'];
                $sale_price=$t_mp_price-$disc;
                $unicart_commission=($sale_price*$product->comission)/100;
               
                $trade_price=$sale_price-$unicart_commission;
                
                $unikart_earning=$unicart_commission-$unic_dis;
                
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

            $order->grand_total = $subtotal + $tax + $shipping;

            if ($seller_product[0]->coupon_code != null) {
                // if (Session::has('club_point')) {
                //     $order->club_point = Session::get('club_point');
                // }
                $order->coupon_discount = $coupon_discount;
                $order->grand_total -= $coupon_discount;

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }
            $order->item_discount = $item_discount;
            $combined_order->grand_total += $order->grand_total;

            $order->save();
        }

        $combined_order->save();
    // dd($order);
        // $array['view'] = 'emails.invoice';
        // $array['subject'] = translate('Your order has been placed. Order code') . ' - ' . $order->code;
        // $array['from'] = env('MAIL_FROM_ADDRESS');
        // $array['order'] = $order;

        // try {
        //     Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
        //     Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
        // } catch (\Exception $e) {

        // }
        $request->session()->put('combined_order_id', $combined_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
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

                $orderDetail->delete();
            }
            $order->delete();
            flash(translate('Order has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }
    

	public function cancel($id)
    {

        $order_info = Order::findOrFail($id);


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
                
            } else {
                //flash(translate('Something went wrong'))->error();
            }

        }    
        flash(translate('Order has been deleted successfully'))->success();
        return back();
    }

    public function cancel_02_11_2022($id)
    {
        $order = Order::findOrFail($id);
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
            flash(translate('Order has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }

    public function bulk_order_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->destroy($order_id);
            }
        }

        return 1;
    }

    public function order_details(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->save();
        return view('frontend.user.seller.order_details_seller', compact('order'));
    }


    public function update_delivery_status(Request $request){

        $orders = Order::where('combined_order_id', $request->combined_order_id)->get();
        
        if($orders){

                foreach($orders as $order){

                    if($request->status == 'delivered'){
                        $order->payment_status = 'paid';
                        $order->save();
                    }

                    $order->delivery_viewed = '0';
                    $order->delivery_status = $request->status;
                    if($order->save()){
                       
                        foreach ($order->orderDetails as $key => $orderDetail) {

                            $orderDetail->delivery_status = $request->status;
                            $orderDetail->save();
            
                            if ($request->status == 'cancelled') {
                                $variant = $orderDetail->variation;
                                if ($orderDetail->variation == null) {
                                    $variant = '';
                                }
            
                                $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                                    ->where('variant', $variant)
                                    ->first();
            
                                if ($product_stock != null) {
                                    $product_stock->qty += $orderDetail->quantity;
                                    $product_stock->save();
                                }

                                $product = Product::where('id',$orderDetail->product_id)->first();
                                if ($product != null){
                                   $product->num_of_sale - 1;
                                   $product->save();
                               }
                            }

                            if($request->status == 'delivered'){
                                $orderDetail->payment_status = 'paid';
                                $orderDetail->save();
                            }
                
                          
                        if (env('MAIL_USERNAME') != null && $request->status == 'confirmed') {
                        $array['view'] = 'emails.invoice';
                        $array['subject'] = translate('Your order is confirmed. Order code') . ' - ' . $order->code;
                        $array['from'] = env('MAIL_FROM_ADDRESS');
                        $array['order'] = $order;
            
                        try {
                            Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
                            Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                        } catch (\Exception $e) {
            
                        }
                    }
                        
                    if (env('MAIL_USERNAME') != null && $request->status == 'delivered') {
                        $array['view'] = 'emails.invoice';
                        $array['subject'] = translate('Your order is delivered. Order code') . ' - ' . $order->code;
                        $array['from'] = env('MAIL_FROM_ADDRESS');
                        $array['order'] = $order;
            
                        try {
                            Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
                        } catch (\Exception $e) {
            
                        }
                    }
                
                    
                    if (addon_is_activated('otp_system') && (\App\OtpConfiguration::where('type', 'otp_for_delivery_status')->first()->value == 1)) {
                        try {
                            $otpController = new OTPVerificationController;
                            $otpController->send_delivery_status($order);
                        } catch (\Exception $e) {
            
                        }
                    }
                
                        //sends Notifications to user
                        NotificationUtility::sendNotification($order, $request->status);
                        // if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
                        //     $request->device_token = $order->user->device_token;
                        //     $request->title = "Order updated !";
                        //     $status = str_replace("_", "", $order->delivery_status);
                        //     $request->text = " Your order {$order->code} has been {$status}";
                        //     $request->type = "order";
                        //     $request->id = $order->id;
                        //     $request->user_id = $order->user->id;
                        //     NotificationUtility::sendFirebaseNotification($request);
                        // }
                    }
                }

         }
       }

      return 1;

    }

    public function update_delivery_status_old(Request $request)
    {
        $order = Order::findOrFail($request->order_id);

        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        if ($request->status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User::where('id', $order->user_id)->first();
            $user->balance += $order->grand_total;
            $user->save();
        }

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {

                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }

                if (addon_is_activated('affiliate_system')) {
                    if (($request->status == 'delivered' || $request->status == 'cancelled') &&
                        $orderDetail->product_referral_code) {

                        $no_of_delivered = 0;
                        $no_of_canceled = 0;

                        if ($request->status == 'delivered') {
                            $no_of_delivered = $orderDetail->quantity;
                        }
                        if ($request->status == 'cancelled') {
                            $no_of_canceled = $orderDetail->quantity;
                        }

                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                    }
                }
            }
        }

        if (env('MAIL_USERNAME') != null && $request->status == 'confirmed') {
            $array['view'] = 'emails.invoice';
            $array['subject'] = translate('Your order is confirmed. Order code') . ' - ' . $order->code;
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['order'] = $order;

            try {
                Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
                Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
            } catch (\Exception $e) {

            }
        }
        
        if (env('MAIL_USERNAME') != null && $request->status == 'delivered') {
            $array['view'] = 'emails.invoice';
            $array['subject'] = translate('Your order is delivered. Order code') . ' - ' . $order->code;
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['order'] = $order;

            try {
                Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
            } catch (\Exception $e) {

            }
        }

    
        if (addon_is_activated('otp_system') && (\App\OtpConfiguration::where('type', 'otp_for_delivery_status')->first()->value == 1)) {
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_delivery_status($order);
            } catch (\Exception $e) {

            }
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->delivery_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('delivery_boy')) {
            if (Auth::user()->user_type == 'delivery_boy') {
                $deliveryBoyController = new DeliveryBoyController;
                $deliveryBoyController->store_delivery_history($order);
            }
        }

        return 1;
    }

//    public function bulk_order_status(Request $request) {
////        dd($request->all());
//        if($request->id) {
//            foreach ($request->id as $order_id) {
//                $order = Order::findOrFail($order_id);
//                $order->delivery_viewed = '0';
//                $order->save();
//
//                $this->change_status($order, $request);
//            }
//        }
//
//        return 1;
//    }

    public function update_payment_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if($request->status=='paid')
        $paid = $order->grand_total;
        else
        $paid = 0;
   // }
    if ($paid >= $order->grand_total) {
        $status = 'paid';
    } else {
        $status = $request->status;
    }

       

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $oVal = (object)[
            'amount' => $paid,
            'status' => 'VALID',
            'error' => null
        ];
        $order->payment_details = json_encode($oVal);
        //}
        $order->save();
        




        if ($order->payment_status == 'paid' && $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('otp_system') && (\App\OtpConfiguration::where('type', 'otp_for_paid_status')->first()->value == 1)) {
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_payment_status($order);
            } catch (\Exception $e) {

            }
        }
        return 1;
    }

    public function assign_delivery_boy(Request $request)
    {
        if (addon_is_activated('delivery_boy')) {

            $order = Order::findOrFail($request->order_id);
            $order->assign_delivery_boy = $request->delivery_boy;
            $order->delivery_history_date = date("Y-m-d H:i:s");
            $order->save();

            $delivery_history = \App\DeliveryHistory::where('order_id', $order->id)
                ->where('delivery_status', $order->delivery_status)
                ->first();

            if (empty($delivery_history)) {
                $delivery_history = new \App\DeliveryHistory;

                $delivery_history->order_id = $order->id;
                $delivery_history->delivery_status = $order->delivery_status;
                $delivery_history->payment_type = $order->payment_type;
            }
            $delivery_history->delivery_boy_id = $request->delivery_boy;

            $delivery_history->save();

            if (env('MAIL_USERNAME') != null && get_setting('delivery_boy_mail_notification') == '1') {
                $array['view'] = 'emails.invoice';
                $array['subject'] = translate('You are assigned to delivery an order. Order code') . ' - ' . $order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;

                try {
                    Mail::to($order->delivery_boy->email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {

                }
            }

            if (addon_is_activated('otp_system')) {
                try {
                    SmsUtility::assign_delivery_boy($order->delivery_boy->phone, $order->code);
                } catch (\Exception $e) {

                }
            }
        }

        return 1;
    }

    public function product_wize_report(Request $request)
    {

         $getorders = Order::whereIn('orders.id',explode(',',$request->ids))
        ->join('order_details', 'order_details.order_id', '=', 'orders.id')
        ->join('products', 'products.id', '=', 'order_details.product_id')
        ->groupBy('products.name')
        ->select('products.name',DB::raw('SUM(order_details.quantity) as noproduct'))
        ->get();
        
        $str = '<table class="table table-bordered mb-0">';

        $str .= '<tr><th>Product Name</th><th>No of Product</th></tr>';
        foreach($getorders as $order){
            $str .= '<tr><td>'.$order->name.'</td><td>'.$order->noproduct.'</td></tr>';
        }

        $str .= '</table>';

        return $str;
    }

    public function updateStatus(Request $request)
    {
        
        $combined_orders = Order::whereIn('id', $request->id)->select('combined_order_id')->groupBy('combined_order_id')->get();
        if ($combined_orders) {
            foreach ($combined_orders as $c_order_id) {
                $orders = array();
                $orders = Order::Where('combined_order_id', $c_order_id->combined_order_id)->get();
                foreach($orders as $order){
                
                	$pre_order_status=$order->delivery_status;  //added by alauddin
                    if($request->order_status == 'delivered'){
                        $order->payment_status = 'paid';
                        $order->save();
                    }
                    $order->delivery_viewed = '0';
                    $order->delivery_status = $request->order_status;
                    if($order->save()){
                        foreach ($order->orderDetails as $key => $orderDetail) {
                            $orderDetail->delivery_status = $request->order_status;
                            $orderDetail->save();
                            if ($request->order_status == 'cancelled') {
                                $variant = $orderDetail->variation;
                                if ($orderDetail->variation == null) {
                                    $variant = '';
                                }
                                $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                                    ->where('variant', $variant)
                                    ->first();
                                if ($product_stock != null) {
                                    $product_stock->qty += $orderDetail->quantity;
                                    $product_stock->save();
                                }
                            }
                        
                        
                        	//Added by alauddin start        
                            if ($pre_order_status == 'cancelled') {

                                $variant = $orderDetail->variation;
                                if ($orderDetail->variation == null) {
                                    $variant = '';
                                }
            
                                $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                                    ->where('variant', $variant)
                                    ->first();
            
                                if ($product_stock != null) {
                                    $product_stock->qty -= $orderDetail->quantity;
                                    $product_stock->save();
                                }
                            }

                        //Added by alauddin end 
                        
                        
                            if($request->order_status == 'delivered'){
                                $orderDetail->payment_status = 'paid';
                                $orderDetail->save();
                            }
                
                    //     if (env('MAIL_USERNAME') != null && $request->order_status == 'confirmed') {
                    //     $array['view'] = 'emails.invoice';
                    //     $array['subject'] = translate('Your order is confirmed. Order code') . ' - ' . $order->code;
                    //     $array['from'] = env('MAIL_FROM_ADDRESS');
                    //     $array['order'] = $order;
            
                    //        try {
                    //         Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
                    //         Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                    //       } catch (\Exception $e) {
            
                    //       }
                    //    }
                        
                    // if (env('MAIL_USERNAME') != null && $request->order_status == 'delivered') {
                    //     $array['view'] = 'emails.invoice';
                    //     $array['subject'] = translate('Your order is delivered. Order code') . ' - ' . $order->code;
                    //     $array['from'] = env('MAIL_FROM_ADDRESS');
                    //     $array['order'] = $order;
            
                    //     try {
                    //         Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
                    //     } catch (\Exception $e) {
            
                    //     }
                    // }
                
                    
                    if (addon_is_activated('otp_system') && (\App\OtpConfiguration::where('type', 'otp_for_delivery_status')->first()->value == 1)) {
                        try {
                            $otpController = new OTPVerificationController;
                            $otpController->send_delivery_status($order);
                        } catch (\Exception $e) {
            
                        }
                    }
                
                        //sends Notifications to user
                        // NotificationUtility::sendNotification($order, $request->order_status);
                        // if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
                        //     $request->device_token = $order->user->device_token;
                        //     $request->title = "Order updated !";
                        //     $status = str_replace("_", "", $order->delivery_status);
                        //     $request->text = " Your order {$order->code} has been {$status}";
                        //     $request->type = "order";
                        //     $request->id = $order->id;
                        //     $request->user_id = $order->user->id;
                        //     NotificationUtility::sendFirebaseNotification($request);
                        // }
                      }
                   }
               }
             }
           }
        return 1;
   }

public function sendToCurier(Request $request) {
    if($request->courier_type=='redx'){
        $courier = Courier::getInstance();
        $courier->setProvider(Redx::class, 'production'); /* local/production */
        $courier->setConfig([
            'API-ACCESS-TOKEN' => env('REDX_API_TOKEN')
        ]);
    }
    $combined_orders = Order::whereIn('id',$request->id)->select('combined_order_id')->groupBy('combined_order_id')->get();
   // dd($combined_orders);
       if($request->id) {
        //   foreach ($request->id as $order_id) {
        foreach ($combined_orders as $c_order_id) {    
            //   $order = Order::findOrFail($order_id);
               
               $orders=array(); 
               $orders = Order::Where('combined_order_id',$c_order_id->combined_order_id)->get();
              
               $details = array();
        	   $order_grand_total=0;
               foreach($orders as $or ){
                    
                    $order = Order::findOrFail($or->id);
                    foreach ($order->orderDetails as $key => $orderDetail) {
                            $details[] = array(
                                'name'=>$orderDetail->product->name,
                                'category'=>$orderDetail->product->category->name,
                                'value'=>$orderDetail->price
                            );
                    }
               		$order_grand_total=$order_grand_total+$order->grand_total;
               }
               
               $shipping = json_decode($order->shipping_address);
               $postcode = $shipping->postal_code;
               $courier->setParams(array('post_code'=>$postcode));
               $res = json_decode($courier->getAreas()->response);
               if(!empty($res->areas))
                $area_id = $res->areas[0]->id;
               else
                $area_id = 1;
                $courier = Courier::getInstance();
                $courier->setProvider(Redx::class, 'production'); /* local/production */
                $courier->setConfig([
                    'API-ACCESS-TOKEN' => env('REDX_API_TOKEN')
                ]);
                $courier->setParams(
                    [
                        'customer_name'=>$shipping->name,
                        'customer_phone'=>$shipping->phone,
                        'delivery_area'=>$shipping->address,
                        'delivery_area_id'=>$area_id,
                        'customer_address'=>$shipping->address,
                        'merchant_invoice_id'=>$order->code,
                        'cash_collection_amount'=>($order->payment_type=='cash_on_delivery') ? $order_grand_total : '0',
                        'parcel_weight'=>$request->parcel_weight,
                        'instruction'=>'',
                        'value'=>$order_grand_total,
                        'parcel_details_json'=>$details,
                    ]
                );
                $place = $courier->placeOrder(); 
                $res = json_decode($place->response);
                $tracking_id = $res->tracking_id;
                // $order->parcel_tracking_id = $tracking_id;
                // $order->delivery_status = 'picked_up';
                // $order->save();

                Order::whereIn('id',$request->id)->update(array('parcel_tracking_id'=>$tracking_id,'delivery_status'=>'picked_up'));
                
           }
       }

       return 1;
   }

public function sendToCurier_20_07_2022(Request $request) {
    if($request->courier_type=='redx'){
        $courier = Courier::getInstance();
        $courier->setProvider(Redx::class, 'production'); /* local/production */
        $courier->setConfig([
            'API-ACCESS-TOKEN' => env('REDX_API_TOKEN')
        ]);
    }

       if($request->id) {
           foreach ($request->id as $order_id) {
               $order = Order::findOrFail($order_id);
               $details = array();
               foreach ($order->orderDetails as $key => $orderDetail) {
                    $details[] = array(
                        'name'=>$orderDetail->product->name,
                        'category'=>$orderDetail->product->category->name,
                        'value'=>$orderDetail->price
                    );
               }
               $shipping = json_decode($order->shipping_address);
               $postcode = $shipping->postal_code;
               $courier->setParams(array('post_code'=>$postcode));
               $res = json_decode($courier->getAreas()->response);
               if(!empty($res->areas))
                $area_id = $res->areas[0]->id;
               else
                $area_id = 1;
               $courier = Courier::getInstance();
        $courier->setProvider(Redx::class, 'production'); /* local/production */
        $courier->setConfig([
            'API-ACCESS-TOKEN' => env('REDX_API_TOKEN')
        ]);
                $courier->setParams(
                    [
                        'customer_name'=>$shipping->name,
                        'customer_phone'=>$shipping->phone,
                        'delivery_area'=>$shipping->address,
                        'delivery_area_id'=>$area_id,
                        'customer_address'=>$shipping->address,
                        'merchant_invoice_id'=>$order->code,
                        'cash_collection_amount'=>($order->payment_type=='cash_on_delivery') ? $order->grand_total : '0',
                        'parcel_weight'=>$request->parcel_weight,
                        'instruction'=>'',
                        'value'=>$order->grand_total,
                        'parcel_details_json'=>$details,
                    ]
                );
                $place = $courier->placeOrder(); 
                $res = json_decode($place->response);
                $tracking_id = $res->tracking_id;
                $order->parcel_tracking_id = $tracking_id;
                $order->delivery_status = 'picked_up';
                $order->save();
           }
       }

       return 1;
   }

   public function update_payment_method(Request $request){
    $order = Order::findOrFail($request->order_id);
    $order->payment_type = $request->payment_method;
    $order->save();
    
   }

}

