<?php
namespace App\Http\Controllers\Api\V2;
use App\City;
use App\Country;
use App\Http\Resources\V2\AddressCollection;
use App\Address;
use App\Http\Resources\V2\CitiesCollection;
use App\Http\Resources\V2\StatesCollection;
use App\Http\Resources\V2\CountriesCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\State;
use App\User;

class AddressController extends Controller
{
    public function addresses($id)
    {
        return new AddressCollection(Address::where('user_id', $id)->get());
    }



	public function createShippingAddress(Request $request)
    {
    	DB::beginTransaction();
        try {
        	$address = new Address;
        	$address->user_id = $request->user_id;
        	$address->address = $request->address;
        	$address->country_id = $request->country_id;
        	$address->state_id = $request->state_id;
        	$address->city_id = $request->city_id;
        	//$address->postal_code = $request->postal_code;
        	$address->phone = $request->phone;

        	if(strlen($request->phone)<11 || strlen($request->phone)>11){
            	return response()->json([
                	'result' => false,
                	'message' => 'Invalid Phone No.'
            	]);  
        	}else{
            	$user = User::find($request->user_id);


            	if(empty($user->phone)){


                	$profile_phone_info = User::
                	where('phone',$request->phone)->get();
                	$phone_exists_check=count($profile_phone_info);

                	if( $phone_exists_check==0){

                    	$user ->phone = $request->phone;
                    	$user->save();
                	}else{
                    	return response()->json([
                        	'result' => false,
                        	'message' => 'Phone number already exists'
                    	]);  
                	}    

            	} 

        	}    

        	$address->save();
        	DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            // something went wrong
        }   

        return response()->json([
            'result' => true,
            'message' => 'Shipping information has been added successfully'
        ]);
    }



    public function updateShippingAddress(Request $request)
    {
    	DB::beginTransaction();
    	try {
        	$address = Address::find($request->id);
        	$address->address = $request->address;
        	$address->country_id = $request->country_id;
        	$address->state_id = $request->state_id;
        	$address->city_id = $request->city_id;
        	//$address->postal_code = $request->postal_code;
        	$address->phone = $request->phone;
        	$address->save();
        	DB::commit();
		}catch(\Exception $e){
            DB::rollback();
            // something went wrong
        }  
        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been updated successfully')
        ]);
    }

    public function updateShippingAddressLocation(Request $request)
    {
    	DB::beginTransaction();
        try {
        	$address = Address::find($request->id);
        	$address->latitude = $request->latitude;
        	$address->longitude = $request->longitude;
        	$address->save();
        	DB::commit();
		}catch(\Exception $e){
            DB::rollback();
            // something went wrong
        }    
        return response()->json([
            'result' => true,
            'message' => translate('Shipping location in map updated successfully')
        ]);
    }


    public function deleteShippingAddress($id)
    {
        $address = Address::find($id);
        $address->delete();
        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been deleted')
        ]);
    }

    public function makeShippingAddressDefault(Request $request)
    {
        Address::where('user_id', $request->user_id)->update(['set_default' => 0]); //make all user addressed non default first

        $address = Address::find($request->id);
        $address->set_default = 1;
        $address->save();
        return response()->json([
            'result' => true,
            'message' => translate('Default shipping information has been updated')
        ]);
    }

    public function updateAddressInCart(Request $request)
    {
        try {
            Cart::where('user_id', $request->user_id)->update(['address_id' => $request->address_id]);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => translate('Could not save the address')
            ]);
        }
        return response()->json([
            'result' => true,
            'message' => translate('Address is saved')
        ]);


    }

    public function getCities()
    {
        return new CitiesCollection(City::where('status', 1)->get());
    }

    public function getStates()
    {
        return new StatesCollection(State::where('status', 1)->get());
    }

    public function getCountries(Request $request)
    {
        $country_query = Country::where('status', 1);
        if ($request->name != "" || $request->name != null) {
             $country_query->where('name', 'like', '%' . $request->name . '%');
        }
        $countries = $country_query->get();
        
        return new CountriesCollection($countries);
    }

    public function getCitiesByState($state_id,Request $request)
    {
        $city_query = City::where('status', 1)->where('state_id',$state_id);
        if ($request->name != "" || $request->name != null) {
             $city_query->where('name', 'like', '%' . $request->name . '%');
        }
        $cities = $city_query->get();
        return new CitiesCollection($cities);
    }

    public function getStatesByCountry($country_id,Request $request)
    {
        $state_query = State::where('status', 1)->where('country_id',$country_id);
        if ($request->name != "" || $request->name != null) {
            $state_query->where('name', 'like', '%' . $request->name . '%');
       }
        $states = $state_query->get();
        return new StatesCollection($states);
    }
}
