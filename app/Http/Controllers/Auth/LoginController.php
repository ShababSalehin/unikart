<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\OTPVerificationController;
use Socialite;
use App\User;
use App\Customer;
use App\Cart;
use Cookie;
use Session;
use Illuminate\Http\Request;
use CoreComponentRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    /*protected $redirectTo = '/';*/


    /**
      * Redirect the user to the Google authentication page.
      *
      * @return \Illuminate\Http\Response
      */
    public function redirectToProvider($provider)
  
    {
    	$link = $url = request()->headers->get('referer');
    	$lastSegment = basename(parse_url($url, PHP_URL_PATH));
    	if($lastSegment!='cart'){
                $link = route('home');
                session(['link' =>  route('home')]);
        }else{
        	session(['link' =>  route('cart')]);
        }
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback_old(Request $request, $provider)
    {
        try {
            if($provider == 'twitter'){
                $user = Socialite::driver('twitter')->user();
            }
            else{
                $user = Socialite::driver($provider)->stateless()->user();
                //dd($user);
                
            }
        } catch (\Exception $e) {
            flash("Something Went wrong. Please try again.")->error();
            return redirect()->route('user.login');
        }

        // check if they're an existing user
        $existingUser = User::where('provider_id', $user->id)->first();

        if($existingUser){
            // log them in
            auth()->login($existingUser, true);
        } else {
        $existingeUser = User::where('email', $user->email)->first();
        
        if($existingeUser){
        // log them in
            auth()->login($existingeUser, true);
        }else{
            // create a new user
            $newUser                  = new User;
            $newUser->name            = $user->name;
            $newUser->email           = $user->email;
            $newUser->registered_by   = 'Web';
            $newUser->email_verified_at = date('Y-m-d H:m:s');
            $newUser->provider_id     = $user->id;
            $newUser->save();
            $customer = new Customer;
            $customer->user_id = $newUser->id;
            $customer->save();
            auth()->login($newUser, true);
        }
        }

        if(auth()->user()->user_type == 'customer'){
         
        	//if(session('link') != null){
            	return redirect(session('link'));
        	//}
        //	else{
           // 	return redirect()->route('home');
        //	}
        }else if(auth()->user()->user_type == 'seller'){
            
            return redirect()->route('dashboard');
        }else{
            return redirect(session('link'));
        }

        // if(session('link') != null){
        //     return redirect(session('link'));
        // }
        // else{
        //     return redirect()->route('home');
        // }
    }



    public function handleProviderCallback(Request $request, $provider)
    {
       
        $user = Socialite::driver($provider)->stateless()->user();
        

        $existingUser = User::where('provider_id', $user->id)->first();

        if(!empty($existingUser)){
            auth()->login($existingUser, true);
        }else{

            $newUser                  = new User;
            $newUser->name            = $user->name;
            $newUser->email           = $user->email;
            $newUser->registered_by   = 'Web';
            $newUser->email_verified_at = date('Y-m-d H:m:s');
            $newUser->provider_id     = $user->id;
            $newUser->save();

            $customer = new Customer;
            $customer->user_id = $newUser->id;
            $customer->save();

            auth()->login($newUser, true);
        
        }

        if(auth()->user()->user_type == 'customer'){
         
        return redirect(session('link'));
       
        }else if(auth()->user()->user_type == 'seller'){
            
            return redirect()->route('dashboard');
        }else{
            return redirect(session('link'));
        }

    }

    /**
        * Get the needed authorization credentials from the request.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return array
        */
       protected function credentials(Request $request)
       {
           if(filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)){
               return $request->only($this->username(), 'password');
           }
           return ['phone'=>$request->get('email'),'password'=>$request->get('password')];
       }

    /**
     * Check user's role and redirect user based on their role
     * @return
     */
    public function authenticated()
    {
        if(session('temp_user_id') != null){
            Cart::where('temp_user_id', session('temp_user_id'))
                    ->update(
                            [
                                'user_id' => auth()->user()->id,
                                'temp_user_id' => null
                            ]
            );

            Session::forget('temp_user_id');
        }
        
        if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
        {
            CoreComponentRepository::instantiateShopRepository();
            return redirect()->route('admin.dashboard');
        } else {

            if(session('link') != null){
                return redirect(session('link'));
            }
            else{
                if(auth()->user()->user_type == 'customer'){
                    return redirect()->route('home');
                }else{
                    return redirect()->route('dashboard');
                }
            }
        }
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        flash(translate('Invalid email or password'))->error();
        return back();
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        if(auth()->user() != null && (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')){
            $redirect_route = 'login';
        }
        else{
            $redirect_route = 'home';
        }
        
        //User's Cart Delete
        // if(auth()->user()){
        //     Cart::where('user_id', auth()->user()->id)->delete();
        // }
        
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect()->route($redirect_route);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login_with_otp(Request $request)
    {
       
    	if(!empty($request->phone)){

             $phone = $request->phone;

                if(strlen($request->phone)<11 || strlen($request->phone)>11){
                    flash(translate('Invalid Phone Number.'));
                    return back();
                }else{

                    $user = User::where('phone', $request->phone)->where('user_type','customer')->first();
                
                    if(!empty($user)){
                        $verification_code =  rand(100000, 999999);
                        $user->verification_code = $verification_code;
                        $user->email_verified_at = null;
                        $user->save();

                    }else{
                        
                        return view('frontend.user_otp_reg_login',compact('phone'));
                    }

                    if(session('temp_user_id') != null){
                        Cart::where('temp_user_id', session('temp_user_id'))
                                ->update([
                                    'user_id' => $user->id,
                                    'temp_user_id' => null
                        ]);
                        Session::forget('temp_user_id');
                    }

                    if(Cookie::has('referral_code')){
                        $referral_code = Cookie::get('referral_code');
                        $referred_by_user = User::where('referral_code', $referral_code)->first();
                        if($referred_by_user != null){
                            $user->referred_by = $referred_by_user->id;
                            $user->save();
                        }
                    }

                    if (addon_is_activated('otp_system')){
                        $otpController = new OTPVerificationController;
                        $otpController->send_code($user);
                        $this->guard()->login($user);
                        return $this->registered($request, $user);
                    }
                   
                }

        }

       
    }

    public function registation_login_with_otp(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'registered_by' => 'Web',
            'phone' => $request->phone,
            'verification_code' => rand(100000, 999999)
        ]);

        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();


        if(session('temp_user_id') != null){
            Cart::where('temp_user_id', session('temp_user_id'))
                    ->update([
                        'user_id' => $user->id,
                        'temp_user_id' => null
            ]);
            Session::forget('temp_user_id');
        }

        if(Cookie::has('referral_code')){
            $referral_code = Cookie::get('referral_code');
            $referred_by_user = User::where('referral_code', $referral_code)->first();
            if($referred_by_user != null){
                $user->referred_by = $referred_by_user->id;
                $user->save();
            }
        }

        if (addon_is_activated('otp_system')){
            $otpController = new OTPVerificationController;
            $otpController->send_code($user);
            $this->guard()->login($user);
            return $this->registered($request, $user);
        }

       
    }

    protected function registered(Request $request, $user)
    {
        if ($user->phone == !null) {
            return redirect()->route('verification');
        }elseif(session('link') != null){
            return redirect()->route('cart');
        }else {
            return redirect()->route('home');
        }
    }
}
