<?php

namespace App\Http\Controllers\Api\V2;

use App\Mail\SecondEmailVerifyMailManager;
use Mail;
use App\Order;
use App\Subscriber;
use App\Upload;
use App\User;
use App\Wishlist;
use Illuminate\Http\Request;
use App\Models\Cart;
use Hash;
use Illuminate\Support\Facades\File;
use Storage;

class ProfileController extends Controller
{
    public function counters($user_id)
    {
        return response()->json([
            'cart_item_count' => Cart::where('user_id', $user_id)->count(),
            'wishlist_item_count' => Wishlist::where('user_id', $user_id)->count(),
            'order_count' => Order::where('user_id', $user_id)->count(),
        ]);
    }

    public function update_basic(Request $request)
    {
        $user = User::find($request->id);
        $user->name = $request->name;

        if ($request->phone != "") {
            $user->phone =$request->phone;
     }

        $user->update();
        return response()->json([
            'result' => true,
            'message' => "Profile information updated"
        ]);
    }


    public function update_password(Request $request)
    {
        $user = User::find($request->id);
    
        if($request->new_password != null && ($request->new_password == $request->confirm_password)){
            $user->password = Hash::make($request->new_password);
            $user->update();
        }

        return response()->json([
            'result' => true,
            'message' => "Password updated"
        ]);
    }


    public function new_verify(Request $request)
    {
        
        $email = $request->email;
        if(isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = 'Email already exists!';
            return json_encode($response);
        }

        $response = $this->send_email_verification_mail($request, $email);
        return json_encode($response);
    }

    public function send_email_verification_mail($request, $email)
    {

        $response['status'] = 0;
        $response['message'] = 'Unknown';
        $verification_code = rand();
        $array['subject'] = 'Email Verification';
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'your account is valid now you can apply for change or update';
        // $array['link'] = route('email_change.callback').'?new_email_verificiation_code='.$verification_code.'&email='.$email;
        $array['details'] = "Email Second";
       

        try {
            Mail::to($email)->queue(new SecondEmailVerifyMailManager($array));

            $response['status'] = 1;
            $response['message'] = translate("Your verification mail has been Sent to your email.");

        } catch (\Exception $e) {
            // return $e->getMessage();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }


    // Form request
    public function update_email(Request $request)
    {
        $email = $request->email;
        $user = User::find($request->id);
        
        if(isUnique($email)) {
        $user = User::find($request->id);
        $this->send_email_change_verification_mail($request, $email,$user);
           
            
        }

        return response()->json([
            'result' => true,
            'message' => "A verification mail has been sent to the mail you provided us with"
        ]);
    }



    public function send_email_change_verification_mail($request, $email,$user)
    {

        $response['status'] = 0;
        $response['message'] = 'Unknown';
        $verification_code = rand();
        $array['subject'] = 'Email Verification';
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Verify your account';
        $array['link'] = route('email_change.callback').'?new_email_verificiation_code='.$verification_code.'&email='.$email;
        $array['sender'] = $user->name;
        $array['details'] = "Email Second";
        $user->new_email_verificiation_code = $verification_code;
        $user->save();

        try {
            Mail::to($email)->queue(new SecondEmailVerifyMailManager($array));

            $response['status'] = 1;
            $response['message'] = translate("Your verification mail has been Sent to your email.");

        } catch (\Exception $e) {
            // return $e->getMessage();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function email_change_callback(Request $request){
        if($request->has('new_email_verificiation_code') && $request->has('email')) {
            $verification_code_of_url_param =  $request->input('new_email_verificiation_code');
            $user = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if($user != null) {
                $user->email = $request->input('email');
                $user->new_email_verificiation_code = null;
                $user->save();
                auth()->login($user, true);

                flash(translate('Email Changed successfully'))->success();
                return redirect()->route('dashboard');
            }
        }

        flash(translate('Email was not verified. Please resend your mail!'))->error();
        return redirect()->route('dashboard');

    }

    public function update_payment_info(Request $request){
        $user = User::find($request->id);
  
        $customer = $user->customer;
        if(empty($customer->bkash_ac)){
            $customer->bkash_ac = $request->bkash_ac;
            $customer->bank_name = $request->bank_name;
            $customer->bank_acc_name = $request->bank_acc_name;
            $customer->bank_acc_no = $request->bank_acc_no;
            $customer->bank_branch_name = $request->bank_branch_name;
            $customer->save();
        }else{
            $customer->bkash_ac = $request->bkash_ac;
            $customer->bank_name = $request->bank_name;
            $customer->bank_acc_name = $request->bank_acc_name;
            $customer->bank_acc_no = $request->bank_acc_no;
            $customer->bank_branch_name = $request->bank_branch_name;
            $customer->update();
        }
      

        return response()->json([
            'result' => true,
            'message' => "Payment Information Updated SuccessFully"
        ]);

    }

    public function subscribe(Request $request)
    {
        $subscriber = Subscriber::where('email', $request->email)->first();
        if($subscriber == null){
            $subscriber = new Subscriber;
            $subscriber->email = $request->email;
            $subscriber->save();
        }
        
        
        return response()->json([
            'result' => true,
            'message' => "You have subscribed successfully"
        ]);
    }


    public function update_device_token(Request $request)
    {
        $user = User::find($request->id);
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'result' => true,
            'message' => "device token updated"
        ]);
    }

    public function updateImage(Request $request)
    {

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => ""
                ]);
            }


            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => ""
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => ""
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $request->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            $user  = User::find($request->id);
            $user->avatar_original = $upload->id;
            $user->save();



            return response()->json([
                'result' => true,
                'message' => "Image updated",
                'path' => api_asset($upload->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => ""
            ]);
        }
    }

    // not user profile image but any other base 64 image through uploader
    public function imageUpload(Request $request)
    {

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $request->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            return response()->json([
                'result' => true,
                'message' => "Image updated",
                'path' => api_asset($upload->id),
                'upload_id' => $upload->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => "",
                'upload_id' => 0
            ]);
        }
    }

    public function checkIfPhoneAndEmailAvailable(Request $request)
    {


        $phone_available = false;
        $email_available = false;
        $phone_available_message = "User phone number not found";
        $email_available_message = "User email  not found";

        $user = User::find($request->user_id);

        if ($user->phone != null || $user->phone != "") {
            $phone_available = true;
            $phone_available_message = "User phone number found";
        }

        if ($user->email != null || $user->email != "") {
            $email_available = true;
            $email_available_message = "User email found";
        }
        return response()->json(
            [
                'phone_available' => $phone_available,
                'email_available' => $email_available,
                'phone_available_message' => $phone_available_message,
                'email_available_message' => $email_available_message,
            ]
        );
    }
}
