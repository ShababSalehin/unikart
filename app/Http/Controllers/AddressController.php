<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Address;
use Auth;
use App\City;
use App\State;
use App\Coupon;
use App\Order;
use App\User;
use DB; //added by alauddin

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


	public function store(Request $request)
     {

        DB::beginTransaction();
        try {

            $order_check = Order::where('user_id', Auth::user()->id)->get();
            $currentdate = strtotime(date('Y-m-d'));
            $coupon = Coupon::where('type', 'first_order_base')
            ->whereRaw('start_date <= ' . $currentdate . ' and end_date >= ' . $currentdate)->get();
    
            $user = Auth::user();
            $address = new Address;
            
            if($request->has('customer_id')){
                $address->user_id = $request->customer_id;
            }
            else{
                $address->user_id = Auth::user()->id;
            }
            
            $address->address = $request->address;
            $address->country_id= $request->country_id;
            $address->state_id = $request->state_id;
            $address->city_id = $request->city_id;
            $address->longitude = $request->longitude;
            $address->latitude = $request->latitude;
            // $address->postal_code = $request->postal_code;
            $address->phone = $request->phone;
            if(strlen($request->phone)<11 || strlen($request->phone)>11){
                flash(translate('Invalid Phone No.'))->warning(); 
                return back();
            }else{
                
                $pattern = "/^[0-9]/";
                
                if(preg_match($pattern, $request->phone)==0) {        
                    flash(translate('Invalid Phone No.'))->warning(); 
                    return back();
                }    
            
                if(empty(Auth::user()->phone)){
                    $profile_phone_info = User::
                    where('phone',$request->phone)->get();
                    $phone_exists_check=count($profile_phone_info);
                
                    if( $phone_exists_check==0){
                        $user ->phone = $request->phone;
                        if(($user->save()) && (count($order_check) == 0 ) && (!empty($coupon[0]->code))){
                        sendSMS($user->phone, env('APP_NAME'),"Congratulations! Your registration is successful.Please visit 'New Customer Offer' to get attractive deals.Or Use coupon code ".$coupon[0]->code ." on your first order to get 20% discount on regular items. For more details visit unikart.com.bd . **Condition Applied");
                        }
                        flash('We send a coupon code on your phone  for your first order')->success();
                        }else{
                    
                        flash(translate('Phone number already exists'))->warning(); 
                        return back();
                    
                    }
                }
                
            }    
            $address->save();
            DB::commit();
            return back();
        }catch(\Exception $e){
            DB::rollback();
            // something went wrong
        }    
         
     }


    public function store_26_12_2022(Request $request)
    {
        $order_check = Order::where('user_id', Auth::user()->id)->get();
        $currentdate = strtotime(date('Y-m-d'));
        $coupon = Coupon::where('type', 'first_order_base')
        ->whereRaw('start_date <= ' . $currentdate . ' and end_date >= ' . $currentdate)->get();

        $user = Auth::user();
        $address = new Address;
       
        if($request->has('customer_id')){
            $address->user_id = $request->customer_id;
        }
        else{
            $address->user_id = Auth::user()->id;
        }
        $address->address = $request->address;
        $address->country_id= $request->country_id;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        $address->longitude = $request->longitude;
        $address->latitude = $request->latitude;
        // $address->postal_code = $request->postal_code;
        $address->phone = $request->phone;
        if(strlen($request->phone)<11 || strlen($request->phone)>11){
        	flash(translate('Invalid Phone No.'))->warning(); 
            return back();
        }else{
        	
        	$pattern = "/^[0-9]/";
            
            if(preg_match($pattern, $request->phone)==0) {        
                flash(translate('Invalid Phone No.'))->warning(); 
                return back();
            }    
        
        	if(empty(Auth::user()->phone)){
        		$profile_phone_info = User::
            	where('phone',$request->phone)->get();
            	$phone_exists_check=count($profile_phone_info);
        	
            	if( $phone_exists_check==0){
            		$user ->phone = $request->phone;
            		if(($user->save()) && (count($order_check) == 0 ) && (!empty($coupon[0]->code))){
                	sendSMS($user->phone, env('APP_NAME'),"Congratulations! Your registration is successful.Please visit 'New Customer Offer' to get attractive deals.Or Use coupon code ".$coupon[0]->code ." on your first order to get 20% discount on regular items. For more details visit unikart.com.bd . **Condition Applied");
            		}
            		flash('We send a coupon code on your phone  for your first order')->success();
           	 	}else{
               
                	flash(translate('Phone number already exists'))->warning(); 
                	return back();
                
            	}
        	}
            
        }    
        $address->save();
        return back();
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
        $data['address_data'] = Address::findOrFail($id);


        $data['states'] = State::where('status', 1)->where('country_id', $data['address_data']->country_id)->get();
        $data['cities'] = City::where('status', 1)->where('state_id', $data['address_data']->state_id)->get();
        
        $returnHTML = view('frontend.user.address.edit_address_modal', $data)->render();
        return response()->json(array('data' => $data, 'html'=>$returnHTML));
//        return ;
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
      
        $address = Address::findOrFail($id);
        $address->address       = $request->address;
        $address->country_id    = $request->country_id;
        $address->state_id      = $request->state_id;
        $address->city_id       = $request->city_id;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        // $address->postal_code   = $request->postal_code;
        $address->phone         = $request->phone;

        $address->save();

        flash(translate('Address info updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $address = Address::findOrFail($id);
        if(!$address->set_default){
            $address->delete();
            return back();
        }
        flash(translate('Default address can not be deleted'))->warning();
        return back();
    }

    public function set_default($id){
        foreach (Auth::user()->addresses as $key => $address) {
            $address->set_default = 0;
            $address->save();
        }
        $address = Address::findOrFail($id);
        $address->set_default = 1;
        $address->save();

        return back();
    }

    public function getStates(Request $request) {
        $states = State::where('status', 1)->where('country_id', $request->country_id)->get();
        $html = '<option value="">'.translate("Select State").'</option>';
        
        foreach ($states as $state) {
            $html .= '<option value="' . $state->id . '">' . $state->name . '</option>';
        }
        
        echo json_encode($html);
    }
    
    public function getCities(Request $request) {
        $cities = City::where('status', 1)->where('state_id', $request->state_id)->get();
        $html = '<option value="">'.translate("Select City").'</option>';
        
        foreach ($cities as $row) {
            $html .= '<option value="' . $row->id . '">' . $row->getTranslation('name') . '</option>';
        }
        
        echo json_encode($html);
    }
}
