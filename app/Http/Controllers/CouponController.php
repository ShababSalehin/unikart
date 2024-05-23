<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coupon;
use App\User;
use Auth;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $coupon_type = null;
      $coupons = Coupon::where('user_id', User::where('user_type', 'admin')->first()->id)->orderBy('id','desc')->get();
      if($request->coupon_type !=null){
          $coupons = $coupons->where('type', $request->coupon_type);
          $coupon_type = $request->$coupon_type;
      }
        return view('backend.marketing.coupons.index', compact('coupons'));
    }

    public function sellerIndex()
    {
        $coupons = Coupon::where('user_id', Auth::user()->id)->orderBy('id','desc')->get();
        return view('frontend.user.seller.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.marketing.coupons.create');
    }

    public function sellerCreate()
    {
        return view('frontend.user.seller.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(count(Coupon::where('code', $request->coupon_code)->get()) > 0){
            flash(translate('Coupon already exist for this coupon code'))->error();
            return back();
        }
        $coupon_amount = (int)($request->coupon_amount);
        if(empty($coupon_amount)){
                $coupon = new Coupon;
                $coupon->user_id = User::where('user_type', 'admin')->first()->id;
                $coupon = $this->setCouponData($request, $coupon);
                $coupon->save();
            
        }else{
            for ($i=0; $i<$coupon_amount;$i++ ){
                $coupon = new Coupon;
                $coupon->user_id = User::where('user_type', 'admin')->first()->id;
                $coupon = $this->setCouponData($request, $coupon);
                $coupon->save();
            }
        }
        flash(translate('Coupon has been saved successfully'))->success();
        return redirect()->route('coupon.index');
    }

    public function sellerStore(Request $request)
    {
        if(count(Coupon::where('code', $request->coupon_code)->get()) > 0){
            flash(translate('Coupon already exist for this coupon code'))->error();
            return back();
        }

        $coupon = new Coupon;
        $coupon->user_id = Auth::user()->id;
        $coupon = $this->setCouponData($request, $coupon);
        $coupon->save();

        flash(translate('Coupon has been saved successfully'))->success();
        return redirect()->route('seller.coupon.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail(decrypt($id));
        return view('backend.marketing.coupons.edit', compact('coupon'));
    }

    public function sellerEdit($id)
    {
        $coupon = Coupon::findOrFail(decrypt($id));
        return view('frontend.user.seller.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        if(count(Coupon::where('id', '!=' , $id)->where('code', $request->coupon_code)->get()) > 0){
            flash(translate('Coupon already exist for this coupon code'))->error();
            return back();
        }

        $coupon = Coupon::findOrFail($id);
        $this->setCouponData($request, $coupon);
        $coupon->save();

        flash(translate('Coupon has been updated successfully'))->success();
        return redirect()->route('coupon.index');
    }

    public function sellerUpdate(Request $request, $id)
    {
        if(count(Coupon::where('id', '!=' , $id)->where('code', $request->coupon_code)->get()) > 0){
            flash(translate('Coupon already exist for this coupon code'))->error();
            return back();
        }

        $coupon = Coupon::findOrFail($id);
        $this->setCouponData($request, $coupon);
        $coupon->save();
        
        flash(translate('Coupon has been updated successfully'))->success();
        return redirect()->route('seller.coupon.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Coupon::destroy($id);
        flash(translate('Coupon has been deleted successfully'))->success();
        return redirect()->route('coupon.index');
    }

    public function sellerDestroy($id)
    {
        Coupon::destroy($id);
        flash(translate('Coupon has been deleted successfully'))->success();
        return redirect()->route('seller.coupon.index');
    }

    public function setCouponData($request, $coupon){
        if ($request->coupon_type == "product_base") {
            $coupon->type = $request->coupon_type;
            $coupon->code = $request->coupon_code;
            $coupon->discount = $request->discount;
            $coupon->discount_type = $request->discount_type;
            $date_var                 = explode(" - ", $request->date_range);
            $coupon->start_date       = strtotime($date_var[0]);
            $coupon->end_date         = strtotime( $date_var[1]);
            $coupon->remarks         = $request->remarks;
            $cupon_details = array();
            foreach($request->product_ids as $product_id) {
                $data['product_id'] = $product_id;
                array_push($cupon_details, $data);
            }
            $coupon->details = json_encode($cupon_details);
            return $coupon;

        } elseif ($request->coupon_type == "cart_base") {
            $coupon->type             = $request->coupon_type;
            $coupon->code             = $request->coupon_code;
            $coupon->discount         = $request->discount;
            $coupon->discount_type    = $request->discount_type;
            $date_var                 = explode(" - ", $request->date_range);
            $coupon->start_date       = strtotime($date_var[0]);
            $coupon->end_date         = strtotime( $date_var[1]);
            $coupon->remarks         = $request->remarks;
            $data                     = array();
            $data['min_buy']          = $request->min_buy;
            $data['max_discount']     = $request->max_discount;
            $coupon->details          = json_encode($data);
            return $coupon;
        }elseif ($request->coupon_type == "first_order_base") {
            $coupon->type             = $request->coupon_type;
            $coupon->code             = $request->coupon_code;
            $coupon->discount         = $request->discount;
            $coupon->discount_type    = $request->discount_type;
            $date_var                 = explode(" - ", $request->date_range);
            $coupon->start_date       = strtotime($date_var[0]);
            $coupon->end_date         = strtotime( $date_var[1]);
            $coupon->remarks         = $request->remarks;
            $data                     = array();
            $data['min_buy']          = $request->min_buy;
            $data['max_discount']     = $request->max_discount;
            $coupon->details          = json_encode($data);
            return $coupon;

        } elseif ($request->coupon_type == "random_coupon") {

            $coupon->type             = $request->coupon_type;

            if(!empty($request->coupon_code)){
                $coupon_code = $request->coupon_code;
            }else{
                $coupon_code = Str::random(8);
            }

            $coupon->code = $coupon_code;
            $coupon->discount         = $request->discount;
            $coupon->discount_type    = $request->discount_type;
            $date_var                 = explode(" - ", $request->date_range);
            $coupon->start_date       = strtotime($date_var[0]);
            $coupon->end_date         = strtotime( $date_var[1]);
            $coupon->remarks         = $request->remarks;
            $data                     = array();
            $data['max_times']          = $request->max_times;
            $data['min_buy']          = $request->min_buy;
            $data['max_discount']     = $request->max_discount;
            $coupon->details          = json_encode($data);
            return $coupon;
        }

       

        
    }

    public function get_coupon_form(Request $request)
    {
        if($request->coupon_type == "product_base") {
            if(Auth::user()->user_type == 'seller') {
                $products = filter_products(\App\Product::where('user_id', Auth::user()->id))->get();
            } else {
                $admin_id = \App\User::where('user_type', 'admin')->first()->id;
                $products = filter_products(\App\Product::where('user_id', $admin_id))->get();
            }

            return view('partials.coupons.product_base_coupon', compact('products'));
        }
        elseif($request->coupon_type == "cart_base"){
            return view('partials.coupons.cart_base_coupon');
        }
        
        elseif($request->coupon_type == "first_order_base"){
            
            return view('partials.coupons.first_order_coupon');

        }elseif($request->coupon_type == "random_coupon"){
            return view('partials.coupons.random_base_coupon');
        }
    }

    public function get_coupon_form_edit(Request $request)
    {
        if($request->coupon_type == "product_base") {
            $coupon = Coupon::findOrFail($request->id);

            if(Auth::user()->user_type == 'seller') {
                $products = filter_products(\App\Product::where('user_id', Auth::user()->id))->get();
            } else {
                $admin_id = \App\User::where('user_type', 'admin')->first()->id;
                $products = filter_products(\App\Product::where('user_id', $admin_id))->get();
            }

            return view('partials.coupons.product_base_coupon_edit',compact('coupon', 'products'));
        }
        elseif($request->coupon_type == "cart_base"){
            $coupon = Coupon::findOrFail($request->id);
            return view('partials.coupons.cart_base_coupon_edit',compact('coupon'));
        }elseif($request->coupon_type == "first_order_base"){
            
            $coupon = Coupon::findOrFail($request->id);
            return view('partials.coupons.first_order_coupon_edit',compact('coupon'));
        }elseif($request->coupon_type == "random_coupon"){
            
            $coupon = Coupon::findOrFail($request->id);
            return view('partials.coupons.random_coupon_edit',compact('coupon'));
        }
    }

}
