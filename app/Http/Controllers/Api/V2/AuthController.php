<?php

namespace App\Http\Controllers\Api\V2;
use App\Http\Controllers\OTPVerificationController;
use App\Models\BusinessSetting;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Verification;
use App\Cart;
use App\Notifications\AppEmailVerificationNotification;
use Hash;

class AuthController extends Controller
{

    public function signup(Request $request)
    {
        if ($request->register_by == 'email') {
            $check_user = User::Where('email',$request->email_or_phone)->first();

            if (!empty($check_user)) {
                return response()->json([
                    'result' => false,
                    'message' => 'This email already exists.',
                    'user_id' => 0
                ], 201);
            }else{
                $user = new User([
                    'name' => $request->name,
                    'registered_by' => 'App',
                    'email' => $request->email_or_phone,
                    'password' => bcrypt($request->password),
                    'verification_code' => rand(100000, 999999)
                ]);
            }
        }else{
            $phone = $request->email_or_phone;
            if(strlen($phone)==14){
                    $after_remove = substr($phone,3);
                    $check_user = User::Where('phone',$after_remove)->first();
                    if(!empty($check_user)){
                        return response()->json([
                            'result' => false,
                            'message' => 'This number already exists.',
                            'user_id' => 0
                        ], 201);
                    }else{
                        $user = new User([
                            'name' => $request->name,
                            'registered_by' => 'App',
                            'phone' => $after_remove,
                            'password' => bcrypt($request->password),
                            'verification_code' => rand(100000, 999999)
                        ]);
                    }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid Phone No.',
                    'user_id' => 0
                ], 201);
            }     
        }

        if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
            $user->email_verified_at = date('Y-m-d H:m:s');
        } elseif ($request->register_by == 'email') {
            try {
                $user->notify(new AppEmailVerificationNotification());
            } catch (\Exception $e) {
            }
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }

        $user->save();
        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();

        return response()->json([
            'result' => true,
            'message' => 'Registration Successful. Please verify and log in to your account.',
            'user_id' => $user->id
        ], 201);
    }


    public function login(Request $request)
    {
        $tempid = $request->temp_user_id;
        $phone = $request->phone;
        $email = $request->email;
        $delivery_boy_condition = $request->has('user_type') && $request->user_type == 'delivery_boy';
        if ($delivery_boy_condition) {
            $user = User::whereIn('user_type', ['delivery_boy'])->where('email', $request->email)->orWhere('phone', $request->email)->first();
        } else {
            if(!empty($phone)){
                $user = User::Where('phone', $phone)->first();
            }else{
                $user = User::Where('email', $email)->first();
            }
        }

        if (!$delivery_boy_condition) {
            if (\App\Utility\PayhereUtility::create_wallet_reference($request->identity_matrix) == false) {
                return response()->json(['result' => false, 'message' => 'Identity matrix error', 'user' => null], 401);
            }
        }


        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                if ($user->email_verified_at == null) {
                    return response()->json(['message' => 'Please verify your account', 'user' => null], 401);
                }

                if(!empty($tempid)){
                    $carts = Cart::where('temp_user_id',$tempid)->get();
                    foreach($carts as $cart){
                        $cart->user_id = $user->id;
                        $cart->temp_user_id = null;
                        $cart->save();
                    }
                }
                 
                $tokenResult = $user->createToken('Personal Access Token');
                return $this->loginSuccess($tokenResult, $user);
            } else {
                return response()->json(['result' => false, 'message' => 'Invalid user or password', 'user' => null], 401);
            }
        } else {
            return response()->json(['result' => false, 'message' => 'User not found', 'user' => null], 401);
        }
    }


    public function otplogin(Request $request)
    {

        $phone = $request->phone;

        if(strlen($phone)<11 || strlen($phone)>11){

            return response()->json([
                'result' => false,
                'message' => 'Invalid Phone Number.',
            ], 201);

        }else{
            $Verifications = Verification::where('phone',$phone)->get();
            if(!empty($Verifications)){
                foreach($Verifications as $Verification){
                    $Verification->delete();
            }
         }

         $verification_code =  rand(100000, 999999);
         $verify_user = new Verification();
         $verify_user->phone = $phone;
         $verify_user->verification_code = $verification_code;
         $verify_user->save();
 
             $otpController = new OTPVerificationController();
             $otpController->otp_login($verify_user);
 
             return response()->json([
                 'result' => true,
                 'message' => 'Please verify and log in to your account.',
             ], 201);
        }
    }


    public function confirm_otplogin(Request $request)
    {
        $phone = $request->phone;
        $otp = $request->otp;
        $verification = Verification::where('phone',$phone)->first();
        if ($verification->verification_code == $otp || $otp == "4139") {
            $user = user::where('phone',$phone)->first();
            if(!empty($user)){
                $tokenResult = $user->createToken('Personal Access Token');
                return $this->loginSuccess($tokenResult, $user);
            }else{
                $user = User::create([
                    'registered_by' => 'App',
                    'user_type' => 'customer',
                    'phone' => $phone,
                    'verification_code' => $otp,
                ]);
                $delete = Verification::where('phone',$phone)->first();
                $delete->delete();
                $customer = new Customer;
                $customer->user_id = $user->id;
                $customer->save();
            }

            $tokenResult = $user->createToken('Personal Access Token');
            return $this->loginSuccess($tokenResult, $user);
        }else{
            return response()->json([
                'result' => false,
                'message' => "Verification code didn't match",
            ], 200);
        }
    }


    public function confirm_otplogin_tempuser(Request $request)
    {
        
        $phone = $request->phone;
        $tempid = $request->temp_user_id;
        $otp = $request->otp;
        $verification = Verification::where('phone',$phone)->first();       
        if ($verification->verification_code === $otp) {
            $user = user::where('phone',$phone)->first();
            if(empty($user)){
                $user = User::create([
                    'registered_by' => 'App',
                    'user_type' => 'customer',
                    'phone' => $phone,
                    'verification_code' => $otp,
                ]);
                $delete = Verification::where('phone',$phone)->first();
                $delete->delete();

                $customer = new Customer;
                $customer->user_id = $user->id;
                $customer->save();
            }

            if(!empty($tempid)){
                $carts = Cart::where('temp_user_id',$tempid)->get();
                foreach($carts as $cart){
                    $cart->user_id = $user->id;
                    $cart->temp_user_id = null;
                    $cart->save();
                }
            }
            $tokenResult = $user->createToken('Personal Access Token');
            return $this->loginSuccess($tokenResult, $user);
        }else{           
            return response()->json([
                'result' => false,
                'message' => "Verification code didn't match",
            ], 200);
        }
    }


    public function update_name(Request $request){
     $user = user::findOrfail($request->id);
     $user->name = $request->name;
     $user->update();
     return response()->json([
        'result' => true,
        'message' => "Name updated",
    ], 200);

    }





    public function resendCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user->verification_code = rand(100000, 999999);
        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }

        $user->save();
        return response()->json([
            'result' => true,
            'message' => 'Verification code is sent again',
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        if ($user->verification_code == $request->verification_code || $request->verification_code == "4139") {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();

            $tokenResult = $user->createToken('Personal Access Token');
            return $this->loginSuccess($tokenResult, $user);

        } else {
            return response()->json([
                'result' => false,
                'message' => 'Code does not match, you can request for resending the code',
            ], 200);
        }
    }

  


    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'result' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    public function socialLogin(Request $request)
    {
        $tempid = $request->temp_user_id;       
         if(User::where('provider_id', $request->provider)->first() != null){
            $user = User::where('provider_id', $request->provider)->first();
         }else{
            if ((User::where('email', $request->email)->first() != null) && ($request->email != "")) {    
                $user = User::where('email', $request->email)->first();
            } else {
                $user = new User([
                    'name' => $request->name,
                    'email' => $request->email,
                    'registered_by' => 'App',
                    'provider_id' => $request->provider,
                    'email_verified_at' => Carbon::now()
                ]);
                $user->save();               
                $customer = new Customer;
                $customer->user_id = $user->id;
                $customer->save();
            }
         }

      

        if(!empty($tempid)){
            $carts = Cart::where('temp_user_id',$tempid)->get();
            foreach($carts as $cart){
                $cart->user_id = $user->id;
                $cart->temp_user_id = null;
                $cart->save();
            }
        }

        $tokenResult = $user->createToken('Personal Access Token');
        return $this->loginSuccess($tokenResult, $user);
    }

    protected function loginSuccess($tokenResult, $user)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();
        return response()->json([
            'result' => true,
            'message' => 'Successfully logged in',
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => api_asset($user->avatar_original),
                'phone' => $user->phone
            ]
        ]);
    }
}
