<?php


namespace App\Http\Controllers\Api\V2;

use App\CombinedOrder;
use Illuminate\Http\Request;

class BkashController extends Controller
{
    private $base_url;
    public function __construct()
    {
        if (get_setting('bkash_sandbox', 1)) {
            $this->base_url = "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/";
        } else {
            $this->base_url = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/";
        }
    
    }

    public function begin(Request $request)
    {
       
        $payment_type = $request->payment_type;
        $combined_order_id = $request->combined_order_id;
        $amount = $request->amount;
        $user_id = $request->user_id;

        try {
            $token = $this->getToken();
            
            if ($payment_type == 'cart_payment') {
                $combined_order = CombinedOrder::find($combined_order_id);
                $amount = $combined_order->grand_total;
            }
            if (
                $payment_type == 'wallet_payment' ||
                $payment_type == 'seller_package_payment' ||
                $payment_type == 'customer_package_payment'
            ) {
                $amount = $request->amount;
            }

            return response()->json([
                'token' => $token,
                'result' => true,
                'url' => route('api.bkash.webpage', ["token" => $token, "amount" => $amount, "order_id" => $combined_order_id]),
                'message' => translate('Payment page is found')
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'token' => '',
                'result' => false,
                'url' => '',
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function webpage($token, $amount, $order_id)
    {
        
        return view('frontend.payment.bkash_app', compact('token', 'amount', 'order_id'));
    }

    public function checkout($token, $amount, $order_id)
    {
        $auth = $token;
        
        
        $callbackURL = route('home');
        $requestbody = array(
            'mode' => '0011',
            'payerReference' => ' ',
            'callbackURL' => route('api.bkash.callback',["token" => $token, "order_id"=>$order_id]),
            'amount' => $amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => "Inv" . Date('YmdH') . rand(1000, 10000)
        );
        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:' . env('BKASH_CHECKOUT_APP_KEY')
        );

        $url = curl_init($this->base_url . 'checkout/create');
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        return redirect(json_decode($resultdata)->bkashURL);
    }

    public function callback($token, $order_id, Request $request)
    {
       
        $allRequest = $request->all();
        if (isset($allRequest['status']) && $allRequest['status'] == 'failure') {
            return $this->failed("Payment Failed");
        } else if (isset($allRequest['status']) && $allRequest['status'] == 'cancel') {
            return $this->failed("Payment Cancelled");
        } else {
            // return response()->json([
            //     "result" => true,
            //     "paymentID" => $allRequest['paymentID']
            // ]);
        return $this->payment_success($token, $order_id, $allRequest['paymentID']);
        }
    }



    public function  payment_success($token, $order_id, $paymentID )
    {
        
        $resultdata = $this->execute($token, $paymentID);
        $result_data_array = json_decode($resultdata, true);
        if (array_key_exists("statusCode", $result_data_array) && $result_data_array['statusCode'] != '0000') {
            return $this->failed($result_data_array['statusMessage']);
        } else if (array_key_exists("message", $result_data_array)) {

            // if execute api failed to response
            sleep(1);
            $resultdata = $this->query($token, $paymentID);
        }
        return $this->process($order_id, $result_data_array);
    }



    public function getToken()
    {
        $request_data = array('app_key' => env('BKASH_CHECKOUT_APP_KEY'), 'app_secret' => env('BKASH_CHECKOUT_APP_SECRET'));
        $request_data_json = json_encode($request_data);

        $header = array(
            'Content-Type:application/json',
            'username:' . env('BKASH_CHECKOUT_USER_NAME'),
            'password:' . env('BKASH_CHECKOUT_PASSWORD')
        );

        $url = curl_init($this->base_url . 'checkout/token/grant');
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $resultdata = curl_exec($url);
        curl_close($url);

        $token = json_decode($resultdata)->id_token;
        return $token;
    }

    public function execute($token, $paymentID)
    {
       
        $auth = $token;

        $requestbody = array(
            'paymentID' => $paymentID
        );
        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:' . env('BKASH_CHECKOUT_APP_KEY')
        );

        $url = curl_init($this->base_url . 'checkout/execute');
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        return $resultdata;
    }

    public function query($token, $paymentID){
    
        $auth = $token;
        
         $requestbody = array(
            'paymentID' => $paymentID
        );
        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.env('BKASH_CHECKOUT_APP_KEY')
        );

        $url = curl_init($this->base_url.'checkout/payment/status');
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);
        
        return $resultdata;
    }

    public function process($order_id,$data)
    {
        
        try {

            checkout_done($order_id, json_encode($data));
            return response()->json(['result' => true, 'message' => translate("Payment is successful")]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fail(Request $request)
    {
        return response()->json([
            'result' => false,
            'message' => translate('Payment Failed'),
            'payment_details' => $request->payment_details
        ]);
    }
}
