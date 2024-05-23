<?php

namespace App\Utility;

use App\Mail\InvoiceEmailManager;
use App\User;
use App\SmsTemplate;
use App\Http\Controllers\OTPVerificationController;
use Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderNotification;
use App\FirebaseNotification;

class NotificationUtility
{
    public static function sendOrderPlacedNotification($order, $request = null)
    {        
        if (addon_is_activated('otp_system') && (\App\OtpConfiguration::where('type', 'otp_for_order')->first()->value == 1)) {
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_order_code($order);
            } catch (\Exception $e) {


            }
        }

        //sends Notifications to user
        self::sendNotification($order, 'placed');
        if ($request !=null && get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order placed !";
            $request->text = "An order {$order->code} has been placed";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            self::sendFirebaseNotification($request);
        }

        //sends email to customer with the invoice pdf attached
        if (env('MAIL_USERNAME') != null) {
        
        
//             $array['view'] = 'emails.combineinvoice';
//             $array['subject'] = translate('Your order has been placed') . ' - ' . $order->code;
//             $array['from'] = env('MAIL_FROM_ADDRESS');
//             $array['order'] = $order;
//             try {
//                 Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
//             	$array['subject'] = translate('New order has been placed') . ' - ' . $order->code;
//                 Mail::to(User::where('id', $order->seller_id)->first()->email)->queue(new InvoiceEmailManager($array));
//             	Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
//             } catch (\Exception $e) {

//             }
        
        	if($request==1){    
                
                $array['subject'] = translate('Your order has been placed') . ' - ' . $order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;
                try {
                    $array['view'] = 'emails.combineinvoice';
                    Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
                    $array['subject'] = translate('New order has been placed') . ' - ' . $order->code;
                    Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                    $array['view'] = 'emails.invoice';
                   
                    Mail::to(User::where('id', $order->seller_id)->first()->email)->queue(new InvoiceEmailManager($array));
                    
                } catch (\Exception $e) {

                }
            }else{
                $array['view'] = 'emails.invoice';
               
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;
                try {
                    
                    $array['subject'] = translate('New order has been placed') . ' - ' . $order->code;
                    Mail::to(User::where('id', $order->seller_id)->first()->email)->queue(new InvoiceEmailManager($array));
                   
                } catch (\Exception $e) {

                }

            }
        
        
        
        }
    }

     // for api order start

    public static function sendOrderPlacedNotificationForApi($order, $request)
    {        
       
        if (addon_is_activated('otp_system') && (\App\OtpConfiguration::where('type', 'otp_for_order')->first()->value == 1)) {
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_order_code($order);
            } catch (\Exception $e) {


            }
        }

        
        //sends Notifications to user
        self::sendNotification($order, 'placed');
        if ($request != null && get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order placed !";
            $request->text = "An order {$order->code} has been placed";
            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

          
            self::sendFirebaseNotification($request);
        }

        //sends email to customer with the invoice pdf attached
        if (env('MAIL_USERNAME') != null) {
        
        	if($request != null){    
                
                $array['subject'] = translate('Your order has been placed') . ' - ' . $order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;
              
                try {
                    $array['view'] = 'emails.combineinvoice';
                    Mail::to($order->user->email)->queue(new InvoiceEmailManager($array));
                    $array['subject'] = translate('New order has been placed') . ' - ' . $order->code;
                    Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                    $array['view'] = 'emails.invoice';
                   
                   $resultm = Mail::to(User::where('id', $order->seller_id)->first()->email)->queue(new InvoiceEmailManager($array));
                  
                } catch (\Exception $e) {
                   
                }
            }else{
                $array['view'] = 'emails.invoice';
               
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;
                try {
                   
                    $array['subject'] = translate('New order has been placed') . ' - ' . $order->code;
                   $RESULTG = Mail::to(User::where('id', $order->seller_id)->first()->email)->queue(new InvoiceEmailManager($array));
                  

                } catch (\Exception $e) {

                }

            }
        
        
        
        }
    }

    // for api order end

    public static function sendNotification($order, $order_status)
    {        
        if ($order->seller_id == \App\User::where('user_type', 'admin')->first()->id) {
            $users = User::findMany([$order->user->id, $order->seller_id]);
        } else {
            $users = User::findMany([$order->user->id, $order->seller_id, \App\User::where('user_type', 'admin')->first()->id]);
        }

        $order_notification = array();
        $order_notification['order_id'] = $order->id;
        $order_notification['order_code'] = $order->code;
        $order_notification['user_id'] = $order->user_id;
        $order_notification['seller_id'] = $order->seller_id;
        $order_notification['status'] = $order_status;

        Notification::send($users, new OrderNotification($order_notification));
    }



    public static function CustomPushNotification_old($request)
    {  
        if($request){

            //$users = User::where('phone','=','01775794472')->get();
            
            $users_device_token = User::whereNotNull('device_token')->groupBy('device_token')
            ->select('device_token')->where('phone','=','01877367453')
            ->get()->pluck('device_token')->toArray();
             
            // $users_device_token = User::whereNotNull('device_token')->groupBy('device_token')
            // ->select('device_token')->limit(999)->pluck('device_token')->toarray();
              
                if (get_setting('google_firebase') == 1) {
                    $data = array(
                      'device_token' => $users_device_token,
                      'title' => $request->title,
                      'text' => $request->text,
                      'type' => $request->type,
                      'image_link' => $request->image_link,
                    );
                   
                    $result = self::sendFirebaseNotification($data);
                }
                
        
        
       }
    }

    public static function sendFirebaseNotification_old($req)
    { 
        
        $url = 'https://fcm.googleapis.com/fcm/send';

        $data = array
        (
            'registration_ids' => $req['device_token'],
            'notification' => [
                'body' => $req['text'],
                'title' => $req['title'],
                'sound' => 'default',
                'image' => $req['image_link']
            ],
            'data' => [
                'item_type' => $req['type'],
                'image' => $req['image_link'],
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        );

        $dataString = json_encode($data);
       
        $headers = array(
            'Authorization: key=' .env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        );
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $result = curl_exec($ch);
        curl_close($ch);

    }

    public static function CustomPushNotification($request)
    {  
        if($request){
            // $tokens = User::whereNotNull('device_token')->groupBy('device_token')
            // ->select('device_token')->where('phone','=','01877367453')
            // ->get()->pluck('device_token')->toArray();
             
            $tokens = User::whereNotNull('device_token')->groupBy('device_token')
            ->select('device_token')->get()->pluck('device_token')->toarray();

              if (get_setting('google_firebase') == 1) {

                  $message = array(
                      'title' => $request->title,
                      'text' => $request->text,
                      'type' => $request->type,
                      'image' => $request->image_link,
                    );

                  $regIdChunk = array_chunk($tokens,999);

                  foreach($regIdChunk as $RegId){
                    
                  $result = self::sendFirebaseNotification($RegId, $message);
                  }
                   
              }
       }
    }


    public static function sendFirebaseNotification($RegId,$message)
    { 

       //dd($req);
        $url = 'https://fcm.googleapis.com/fcm/send';
       
        $data = array
        (
            'registration_ids' => $RegId,
            'notification' => [
                'body' =>$message['text'],
                'title' => $message['title'],
                'sound' => 'default', /*Default sound*/
                'image' =>$message['image']
            ],
            'data' => [
                'item_type' => $message['type'],
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        );
      
        $dataString = json_encode($data);
        
        $headers = array(
            'Authorization: key=' .env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$dataString);

        $result = curl_exec($ch);
        
        curl_close($ch);

    }

}
