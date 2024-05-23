<?php
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\ClubPointController;
use App\Http\Controllers\AffiliateController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CommissionController;
use App\Utility\CategoryUtility;
use App\Utility\SendSMSUtility;
use App\Utility\NotificationUtility;
use App\FlashDeal;
use App\Currency;
use App\Product;
use App\BusinessSetting;
use App\ProductStock;
use App\Address;
use App\CustomerPackage;
use App\Upload;
use App\Translation;
use App\City;
use App\Wallet;
use App\CombinedOrder;
use App\User;
use App\Addon;
use App\FlashDealProduct;
use App\Seller;

if (!function_exists('sendSMS')) {
    function sendSMS($to, $from, $text, $template_id=false)
    {
        return SendSMSUtility::sendSMS($to, $from, $text, $template_id=false);
    }
}

//highlights the selected navigation on admin panel
if (!function_exists('areActiveRoutes')) {
    function areActiveRoutes(array $routes, $output = "active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }
    }
}

//highlights the selected navigation on frontend
if (!function_exists('areActiveRoutesHome')) {
    function areActiveRoutesHome(array $routes, $output = "active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }
    }
}

//highlights the selected navigation on frontend
if (!function_exists('default_language')) {
    function default_language()
    {
        return env("DEFAULT_LANGUAGE");
    }
}

/**
 * Save JSON File
 * @return Response
 */
if (!function_exists('convert_to_usd')) {
    function convert_to_usd($amount)
    {
        $business_settings = get_setting('system_default_currency');
        if ($business_settings != null) {
            $currency = Currency::find($business_settings->value);
            return (floatval($amount) / floatval($currency->exchange_rate)) * Currency::where('code', 'USD')->first()->exchange_rate;
        }
    }
}

if (!function_exists('convert_to_kes')) {
    function convert_to_kes($amount)
    {
        $business_settings = get_setting('system_default_currency');
        if ($business_settings != null) {
            $currency = Currency::find($business_settings->value);
            return (floatval($amount) / floatval($currency->exchange_rate)) * Currency::where('code', 'KES')->first()->exchange_rate;
        }
    }
}

//filter products based on vendor activation system
if (!function_exists('filter_products')) {
    function filter_products($products)
    {
        $verified_sellers = verified_sellers_id();
        if (get_setting('vendor_system_activation') == 1) {
            return $products->where('approved', '1')->where('published', '1')->where('auction_product', 0)->orderBy('products.created_at', 'desc')->where(function ($p) use ($verified_sellers) {
                $p->where('added_by', 'admin')->orWhere(function ($q) use ($verified_sellers) {
                    $q->whereIn('user_id', $verified_sellers);
                });
            });
        } else {
            return $products->where('published', '1')->where('auction_product', 0)->where('added_by', 'admin');
        }
    }
}

//cache products based on category
if (!function_exists('get_cached_products')) {
    function get_cached_products($category_id = null)
    {
        $products = Product::where('published', 1)->where('approved', '1');
        $verified_sellers = verified_sellers_id();
        if (get_setting('vendor_system_activation') == 1) {
            $products = $products->where(function ($p) use ($verified_sellers) {
                $p->where('added_by', 'admin')->orWhere(function ($q) use ($verified_sellers) {
                    $q->whereIn('user_id', $verified_sellers);
                });
            });
        } else {
            $products = $products->where('added_by', 'admin');
        }
        if ($category_id != null) {
            return Cache::remember('products-category-' . $category_id, 86400, function () use ($category_id, $products) {
                $category_ids = CategoryUtility::children_ids($category_id);
                $category_ids[] = $category_id;
                return $products->whereIn('category_id', $category_ids)->orderByRaw("RAND()")->take(6)->get();
            });
        } else {
            return Cache::remember('products', 86400, function () use ($products) {
                return $products->orderByRaw("RAND()")->take(6)->get();
            });
        }
    }
}

if (!function_exists('verified_sellers_id')) {
    function verified_sellers_id()
    {
        return Cache::rememberForever('verified_sellers_id', function () {
            return Seller::where('verification_status', 1)->pluck('user_id')->toArray();
        });
    }
}

if (!function_exists('get_system_default_currency')) {
    function get_system_default_currency()
    {
        return Cache::remember('system_default_currency', 86400, function () {
            return Currency::findOrFail(get_setting('system_default_currency'));
        });
    }
}

//converts currency to home default currency
if (!function_exists('convert_price')) {
    function convert_price($price)
    {
        if (Session::has('currency_code') && (Session::get('currency_code') != get_system_default_currency()->code)) {
            $price = floatval($price) / floatval(get_system_default_currency()->exchange_rate);
            $price = floatval($price) * floatval(Session::get('currency_exchange_rate'));
        }
        return $price;
    }
}

//gets currency symbol
if (!function_exists('currency_symbol')) {
    function currency_symbol()
    {
        if (Session::has('currency_symbol')) {
            return Session::get('currency_symbol');
        }
        return get_system_default_currency()->symbol;
    }
}

//formats currency
if (!function_exists('format_price')) {
    function format_price($price)
    {
        if (get_setting('decimal_separator') == 1) {
            $fomated_price = number_format($price, get_setting('no_of_decimals'));
        } else {
            $fomated_price = number_format($price, get_setting('no_of_decimals'), ',', ' ');
        }

        if (get_setting('symbol_format') == 1) {
            return currency_symbol() . $fomated_price;
        } else if (get_setting('symbol_format') == 3) {
            return currency_symbol() . ' ' . $fomated_price;
        } else if (get_setting('symbol_format') == 4) {
            return $fomated_price . ' ' . currency_symbol();
        }
        return $fomated_price . ' ' . currency_symbol();
    }
}

//formats price to home default price with convertion
if (!function_exists('single_price')) {
    function single_price($price)
    {
        return format_price(convert_price($price));
    }
}

//only currency symbol
if (!function_exists('get_only_currency_symbol')) {
    function get_only_currency_symbol()
    {
        return currency_symbol();
    }
}

//Shows Price on page based on low to high
if (!function_exists('home_price')) {
    function home_price($product, $formatted = true)
    {
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;
    	$unit_price=$product->unit_price;
        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if ($lowest_price > $stock->price) {
                    $lowest_price = $stock->price;
                }
                if ($highest_price < $stock->price) {
                    $highest_price = $stock->price;
                }
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $lowest_price += ($lowest_price * $product_tax->tax) / 100;
                $highest_price += ($highest_price * $product_tax->tax) / 100;
            	$unit_price +=($unit_price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $lowest_price += $product_tax->tax;
                $highest_price += $product_tax->tax;
            	$unit_price +=$product_tax->tax;
            }
        }

        if ($formatted) {
            if ($lowest_price == $highest_price) {
                return format_price(convert_price($lowest_price));
            } else {
                return format_price(convert_price($lowest_price)) . ' - ' . format_price(convert_price($highest_price));
            }
        } else {
            return $lowest_price . ' - ' . $highest_price;
        }
    }
}

//Shows Price on page based on low to high with discount
if (!function_exists('home_discounted_price')) {
    function home_discounted_price($product, $formatted = true)
    {
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;
    	$unit_price=$product->unit_price;
        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if ($lowest_price > $stock->price) {
                    $lowest_price = $stock->price;
                }
                if ($highest_price < $stock->price) {
                    $highest_price = $stock->price;
                }
            }
        }

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $lowest_price -= ($lowest_price * $product->discount) / 100;
                $highest_price -= ($highest_price * $product->discount) / 100;
            	$unit_price -=($unit_price * $product->discount) / 100; //added by alauddin
            } elseif ($product->discount_type == 'amount') {
                $lowest_price -= $product->discount;
                $highest_price -= $product->discount;
            	$unit_price -= $product->discount; //added by alauddin
            }
        
        
           //added by alauddin start
        	if ($product->discount_type == 'percent') {
            	$lowest_price -= ($product->unit_price * $product->unikart_discount) / 100;
            	$highest_price -= ($product->unit_price * $product->unikart_discount) / 100;
            	$unit_price -=($product->unit_price * $product->unikart_discount) / 100; //added by alauddin
        	} elseif ($product->discount_type == 'amount') {
            	$lowest_price -= $product->unikart_discount;
            	$highest_price -= $product->unikart_discount;
            	$unit_price -= $product->unikart_discount;
        	}
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $lowest_price += ($lowest_price * $product_tax->tax) / 100;
                $highest_price += ($highest_price * $product_tax->tax) / 100;
            	$unit_price +=($unit_price * $product_tax->tax) / 100; //added by alauddin
            } elseif ($product_tax->tax_type == 'amount') {
                $lowest_price += $product_tax->tax;
                $highest_price += $product_tax->tax;
            	$unit_price +=$product_tax->tax; //added by alauddin
            }
        }

        if ($formatted) {
            if ($lowest_price == $highest_price) {
                return format_price(convert_price($lowest_price));
            } else {
                return format_price(convert_price($lowest_price)) . ' - ' . format_price(convert_price($highest_price));
            }
        } else {
            return $lowest_price . ' - ' . $highest_price;
        }
    }
}

//Shows Base Price
if (!function_exists('home_base_price_by_stock_id')) {
    function home_base_price_by_stock_id($id)
    {
        $product_stock = ProductStock::findOrFail($id);
        $price = $product_stock->price;
        $tax = 0;

        foreach ($product_stock->product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }
        $price += $tax;
        return format_price(convert_price($price));
    }
}

if (!function_exists('home_base_price')){
    function home_base_price($product, $formatted = true)
    {
        $price = $product->unit_price;
        return $formatted ? format_price(convert_price($price)) : $price;
    }
}

//Shows Base Price with discount
if (!function_exists('home_discounted_base_price_by_stock_id')) {
    function home_discounted_base_price_by_stock_id($id)
    {
        $product_stock = ProductStock::findOrFail($id);
        $product = $product_stock->product;
        $price = $product_stock->price;
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }
        return format_price(convert_price($price));
    }
}

//Shows Base Price with discount
if (!function_exists('home_discounted_base_price')) {
function home_discounted_base_price($product, $formatted = true,$app_check=false)
    {
        $price = $product->unit_price;
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($app_check==1){
                $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.campaign_type',"First Order") 
                   ->where('flash_deals.status',1) 
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();              
                if(isset($flashDeal->campaign_type) && $flashDeal->campaign_type=="First Order"){
                    if($flashDeal->discount_type == 'percent'){
                        $price -= ($price*$flashDeal->discount)/100;                   
                        $price -= ($product->unit_price*$flashDeal->unikart_discount)/100;
                    }
                    elseif($flashDeal->discount_type == 'amount'){
                        $price -= $flashDeal->discount;                
                        $price -= $flashDeal->unikart_discount;
                        
                    }
                }else{
                    $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.status',1) 
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

                    if ($product->discount_type == 'percent') {
                        $price -= ($price * $product->discount) / 100;
                    }elseif($product->discount_type == 'amount') {
                        $price -= $product->discount;
                    }
                
                    //added by alauddin start
        
                    if ($product->discount_type == 'percent') {                
                        $price -=($product->unit_price * $product->unikart_discount) / 100; //added by alauddin
                    } elseif ($product->discount_type == 'amount') {            
                        $price -= $product->unikart_discount; //added by alauddin
                    }
        
                   //Start App Price Calculation
                	if(isset($flashDeal->campaign_type) && ($flashDeal->campaign_type=="First Order") ){
                    
                    }else{
                    	if ($product->app_discount_type == 'percent') {                
                        	$appdiscount=($price * $product->app_price) / 100;
                    	} elseif ($product->discount_type == 'amount') {            
                        	$appdiscount= $product->app_price; 
                    	}

                    	$price -= $appdiscount;
                    }
                }
            }else{
                if ($product->discount_type == 'percent') {
                    $price -= ($price * $product->discount) / 100;
                }elseif($product->discount_type == 'amount') {
                    $price -= $product->discount;
                }
            
                //added by alauddin start
                if ($product->discount_type == 'percent') {                
                    $price -=($product->unit_price * $product->unikart_discount) / 100;
                } elseif ($product->discount_type == 'amount') {            
                    $price -= $product->unikart_discount;
                }
            }
        }else{
            //Start App Price Calculation
            if($app_check==1){
                $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.campaign_type',"First Order") 
                   ->where('flash_deals.status',1) 
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();
                if(isset($flashDeal->campaign_type) && $flashDeal->campaign_type=="First Order"){
                    if($flashDeal->discount_type == 'percent'){
                        $price -= ($price*$flashDeal->discount)/100;                   
                        $price -= ($product->unit_price*$flashDeal->unikart_discount)/100;
                    }
                    elseif($flashDeal->discount_type == 'amount'){
                        $price -= $flashDeal->discount;                
                        $price -= $flashDeal->unikart_discount;
                    }
                }else{
                	if ($product->app_discount_type == 'percent') {                
                        $appdiscount=($price * $product->app_price) / 100;
                    } elseif ($product->discount_type == 'amount') {            
                        $appdiscount= $product->app_price;
                    }
                    
                    $price -=$appdiscount;
                }
            }
        }
        return $formatted ? format_price(convert_price($price)) : $price;
    }

}

if (!function_exists('get_app_price')) {
    function get_app_price($product)
    {
        $price = $product->unit_price;
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
                $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.campaign_type',"First Order") 
                   ->where('flash_deals.status',1) 
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();
                
                if(isset($flashDeal->campaign_type) && $flashDeal->campaign_type=="First Order"){
                    if($flashDeal->discount_type == 'percent'){
                        $price -= ($price*$flashDeal->discount)/100;                   
                        $price -= ($product->unit_price*$flashDeal->unikart_discount)/100;
                    }
                    elseif($flashDeal->discount_type == 'amount'){
                        $price -= $flashDeal->discount;                
                        $price -= $flashDeal->unikart_discount;
                    }
                }else{
                    $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.status',1) 
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();

                    if ($product->discount_type == 'percent') {
                        $price -= ($price * $product->discount) / 100;
                    }elseif($product->discount_type == 'amount') {
                        $price -= $product->discount;
                    }
                
                    //added by alauddin start
        
                    if ($product->discount_type == 'percent') {                
                        $price -=($product->unit_price * $product->unikart_discount) / 100;
                    } elseif ($product->discount_type == 'amount') {            
                        $price -= $product->unikart_discount;
                    }
        
                    
                	//Start App Price Calculation
                	if(isset($flashDeal->campaign_type) && ($flashDeal->campaign_type=="First Order") ){
                    }else{
                    	if ($product->app_discount_type == 'percent') {                
                        	$appdiscount=($price * $product->app_price) / 100;
                    	} elseif ($product->discount_type == 'amount') {            
                        	$appdiscount= $product->app_price;
                    	}

                    	$price -=$appdiscount;
                    }
                }
        }else{
            //Start App Price Calculation
                $flashDeal = FlashDeal::join('flash_deal_products', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')  
                   ->where('flash_deals.start_date','<=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.end_date','>=',strtotime(date('d-m-Y H:i:s')) )
                   ->where('flash_deals.campaign_type',"First Order") 
                   ->where('flash_deals.status',1) 
                   ->where('flash_deal_products.product_id',$product->id)  
                   ->select('flash_deals.campaign_type','flash_deals.minimum_amount','flash_deal_products.*')->first();
                if(isset($flashDeal->campaign_type) && $flashDeal->campaign_type=="First Order"){
                    if($flashDeal->discount_type == 'percent'){
                        $price -= ($price*$flashDeal->discount)/100;                   
                        $price -= ($product->unit_price*$flashDeal->unikart_discount)/100;
                    }
                    elseif($flashDeal->discount_type == 'amount'){
                        $price -= $flashDeal->discount;                
                        $price -= $flashDeal->unikart_discount;
                    }
                }else{
                	if ($product->app_discount_type == 'percent') {                
                        $appdiscount=($price * $product->app_price) / 100;
                    } elseif ($product->discount_type == 'amount') {            
                        $appdiscount= $product->app_price;
                    }
                    
                    $price -=$appdiscount;
                }
        }
        return round($price);
    }
}


if (!function_exists('renderStarRating')) {
    function renderStarRating($rating, $maxRating = 5)
    {
        $fullStar = "<i class = 'las la-star active'></i>";
        $halfStar = "<i class = 'las la-star half'></i>";
        $emptyStar = "<i class = 'las la-star'></i>";
        $rating = $rating <= $maxRating ? $rating : $maxRating;

        $fullStarCount = (int)$rating;
        $halfStarCount = ceil($rating) - $fullStarCount;
        $emptyStarCount = $maxRating - $fullStarCount - $halfStarCount;

        $html = str_repeat($fullStar, $fullStarCount);
        $html .= str_repeat($halfStar, $halfStarCount);
        $html .= str_repeat($emptyStar, $emptyStarCount);
        echo '';
    }
}

function translate($key, $lang = null)
{
    if($lang == null){
        $lang = App::getLocale();
    }

    $lang_key = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($key)));
    $translations_default = Cache::rememberForever('translations-'.env('DEFAULT_LANGUAGE', 'en'), function () {
        return Translation::where('lang', env('DEFAULT_LANGUAGE', 'en'))->pluck('lang_value', 'lang_key')->toArray();
    });

    if(!isset($translations_default[$lang_key])){
        $translation_def = new Translation;
        $translation_def->lang = env('DEFAULT_LANGUAGE', 'en');
        $translation_def->lang_key = $lang_key;
        $translation_def->lang_value = $key;
        $translation_def->save();
        Cache::forget('translations-'.env('DEFAULT_LANGUAGE', 'en'));
    }

    $translation_locale = Cache::rememberForever('translations-'.$lang, function () use ($lang) {
        return Translation::where('lang', $lang)->pluck('lang_value', 'lang_key')->toArray();
    });

    //Check for session lang
    if(isset($translation_locale[$lang_key])){
        return $translation_locale[$lang_key];
    }
    elseif(isset($translations_default[$lang_key])){
        return $translations_default[$lang_key];
    }
    else{
        return $key;
    }
}

function remove_invalid_charcaters($str)
{
    $str = str_ireplace(array("\\"), '', $str);
    return str_ireplace(array('"'), '\"', $str);
}


function getShippingCost($carts, $index)
{
    $admin_products = array();
    $seller_products = array();
    $calculate_shipping = 0;
    $calculate_total = 0;
	$shippingcalculate_total = 0;
    foreach ($carts as $key => $cartItem) {
        $product = Product::find($cartItem['product_id']);
        $calculate_total+=($product->unit_price*$cartItem['quantity']);
    	$shippingcalculate_total+=($cartItem['price']*$cartItem['quantity']);
        if ($product->added_by == 'admin') {
            array_push($admin_products, $cartItem['product_id']);
        } else {
            $product_ids = array();
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem['product_id']);
            $seller_products[$product->user_id] = $product_ids;
        }
    }

    $shipping_skip_total = BusinessSetting::where('type', 'flat_rate_shipping_cost_total')->first()->value;
    if($shipping_skip_total<=$shippingcalculate_total){
        return 0;
    }

    //Calculate Shipping Cost
  if (get_setting('shipping_type') == 'flat_rate') {
        $calculate_shipping = get_setting('flat_rate_shipping_cost');
    } elseif (get_setting('shipping_type') == 'seller_wise_shipping') {
        if (!empty($admin_products)) {
            $calculate_shipping = get_setting('shipping_cost_admin');
        }
        if (!empty($seller_products)) {
            foreach ($seller_products as $key => $seller_product) {
                $calculate_shipping += \App\Shop::where('user_id', $key)->first()->shipping_cost;
            }
        }
    } elseif (get_setting('shipping_type') == 'area_wise_shipping') {
        if($carts[0]['address_id']>0){
            $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
           $city = City::where('id', $shipping_info->city_id)->first();
            if ($city != null) {
                $calculate_shipping = $city->cost;
            }
        }else{
            $calculate_shipping = get_setting('flat_rate_shipping_cost');
        }    
    }

    $cartItem = $carts[$index];
    $product = Product::find($cartItem['product_id']);

    if ($product->digital == 1) {
        return $calculate_shipping = 0;
    }
   $today = strtotime(date('Y-m-d H:i:s'));
   $flash_deal = FlashDeal::where('freeshipping_check','1')->where('start_date', "<=", $today)
        ->where('end_date', ">=", $today)
        ->where('status', 1)->get();
    if(count($flash_deal)>0){
        
        $prod_exists = FlashDealProduct::where('flash_deal_id',$flash_deal[0]->id)
        ->where('product_id',$cartItem['product_id'])
        ->get();
        if(count($prod_exists)>0){
            return $calculate_shipping = 0;
        }
    }

    if (get_setting('shipping_type') == 'flat_rate') {
        return number_format((float)$calculate_shipping) / count($carts);
    } elseif (get_setting('shipping_type') == 'seller_wise_shipping') {
        if ($product->added_by == 'admin') {
            return get_setting('shipping_cost_admin') / count($admin_products);
        } else {
            return \App\Shop::where('user_id', $product->user_id)->first()->shipping_cost / count($seller_products[$product->user_id]);
        }
    } elseif (get_setting('shipping_type') == 'area_wise_shipping') {
        if ($product->added_by == 'admin') {
            return $calculate_shipping / count($admin_products);
        } else {
           return $calculate_shipping / count($carts);
        }
    } else {
        if($product->is_quantity_multiplied && get_setting('shipping_type') == 'product_wise_shipping') {
            return  $product->shipping_cost * $cartItem['quantity'];
        }
        return $product->shipping_cost;
    }
}

//added by alauddin start
function getUnikartShippingCost($carts, $index)
{
    $admin_products = array();
    $seller_products = array();
    $calculate_shipping = 0;
    $calculate_total = 0;
	$shippingcalculate_total = 0;
    foreach ($carts as $key => $cartItem) {
        $product = \App\Product::find($cartItem['product_id']);
        $calculate_total+=($product->unit_price*$cartItem['quantity']);
        $shippingcalculate_total+=($cartItem['price']*$cartItem['quantity']);
        if ($product->added_by == 'admin') {
            array_push($admin_products, $cartItem['product_id']);
        } else {
            $product_ids = array();
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem['product_id']);
            $seller_products[$product->user_id] = $product_ids;
        }
    }

    $shipping_skip_total = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost_total')->first()->value;
    if($shippingcalculate_total>$shipping_skip_total){
        if (get_setting('shipping_type') == 'flat_rate') {
            $calculate_shipping =get_setting('flat_rate_shipping_cost');
            return number_format((float)$calculate_shipping) / count($carts);
            
        }elseif (get_setting('shipping_type') == 'area_wise_shipping') {

            if($carts[0]['address_id']>0){
                $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
                $city = City::where('id', $shipping_info->city_id)->first(); //added by alauddin
                if ($city != null) {
                    $calculate_shipping = $city->cost;
                }
            }else{
                $calculate_shipping = get_setting('flat_rate_shipping_cost');
            }

            if ($product->added_by == 'admin') {
                return $calculate_shipping / count($admin_products);
            } else {
               return $calculate_shipping / count($carts);
            }
        }else{
            return 0;
        }
    }

}


//added by alauddin end

function timezones()
{
    return Timezones::timezonesToArray();
}

if (!function_exists('app_timezone')) {
    function app_timezone()
    {
        return config('app.timezone');
    }
}

if (!function_exists('api_asset')) {
    function api_asset($id)
    {
        if (($asset = \App\Upload::find($id)) != null) {
            return $asset->file_name;
        }
        return "";
    }
}

//return file uploaded via uploader
if (!function_exists('uploaded_asset')) {
    function uploaded_asset($id)
    {
        if (($asset = \App\Upload::find($id)) != null) {
            return my_asset($asset->file_name);
        }
        return null;
    }
}

if (!function_exists('my_asset')) {
 
    function my_asset($path, $secure = null)
    {
        if (env('FILESYSTEM_DRIVER') == 's3') {
            return Storage::disk('s3')->url($path);
        } else {
            return app('url')->asset('public/' . $path, $secure);
        }
    }
}

if (!function_exists('static_asset')) {
    function static_asset($path, $secure = null)
    {
        return app('url')->asset('public/' . $path, $secure);
    }
}

if (!function_exists('getBaseURL')) {
    function getBaseURL()
    {
        $root = '//' . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        return $root;
    }
}


if (!function_exists('getFileBaseURL')) {
    function getFileBaseURL()
    {
        if (env('FILESYSTEM_DRIVER') == 's3') {
            return env('AWS_URL') . '/';
        } else {
            return getBaseURL() . 'public/';
        }
    }
}


if (!function_exists('isUnique')) {
    function isUnique($email)
    {
        $user = User::where('email', $email)->first();
        if ($user == null) {
            return '1'; // $user = null means we did not get any match with the email provided by the user inside the database
        } else {
            return '0';
        }
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null, $lang = false)
    {
        $settings = Cache::remember('business_settings', 86400, function () {
            return BusinessSetting::all();
        });

        if ($lang == false) {
            $setting = BusinessSetting::where('type', $key)->first();
        } else {
            $setting = $settings->where('type', $key)->where('lang', $lang)->first();
            $setting = !$setting ? $settings->where('type', $key)->first() : $setting;
        }
        return $setting == null ? $default : $setting->value;
    }
}

function hex2rgba($color, $opacity = false)
{
    return Colorcodeconverter::convertHexToRgba($color, $opacity);
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        if (Auth::check() && (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff')) {
            return true;
        }
        return false;
    }
}

if (!function_exists('isSeller')) {
    function isSeller()
    {
        if (Auth::check() && Auth::user()->user_type == 'seller') {
            return true;
        }
        return false;
    }
}

if (!function_exists('isCustomer')) {
    function isCustomer()
    {
        if (Auth::check() && Auth::user()->user_type == 'customer') {
            return true;
        }
        return false;
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// duplicates m$ excel's ceiling function
if (!function_exists('ceiling')) {
    function ceiling($number, $significance = 1)
    {
        return (is_numeric($number) && is_numeric($significance)) ? (ceil($number / $significance) * $significance) : false;
    }
}

if (!function_exists('get_images')) {
    function get_images($given_ids, $with_trashed = false)
    {
        if (is_array($given_ids)) {
            $ids = $given_ids;
        } elseif ($given_ids == null) {
            $ids = [];
        } else {
            $ids = explode(",", $given_ids);
        }
        return $with_trashed
            ? Upload::withTrashed()->whereIn('id', $ids)->get()
            : Upload::whereIn('id', $ids)->get();
    }
}

//for api
if (!function_exists('get_images_path')) {
    function get_images_path($given_ids, $with_trashed = false)
    {
        $paths = [];
        $images = get_images($given_ids, $with_trashed);
        if (!$images->isEmpty()) {
            foreach ($images as $image) {
                $paths[] = !is_null($image) ? $image->file_name : "";
            }
        }
        return $paths;
    }
}

//for api
if (!function_exists('checkout_done')) {
    function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::find($combined_order_id);
        foreach ($combined_order->orders as $key => $order) {
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();
            try {
                NotificationUtility::sendOrderPlacedNotification($order);
                calculateCommissionAffilationClubPoint($order);
            } catch (\Exception $e) {
               
            }
        }
    }
}

//for api
if (!function_exists('wallet_payment_done')) {
    function wallet_payment_done($user_id, $amount, $payment_method, $payment_details)
    {
        $user = User::find($user_id);
        $user->balance = $user->balance + $amount;
        $user->save();
        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $amount;
        $wallet->payment_method = $payment_method;
        $wallet->payment_details = $payment_details;
        $wallet->save();
    }
}

if (!function_exists('purchase_payment_done')) {
    function purchase_payment_done($user_id, $package_id)
    {
        $user = User::findOrFail($user_id);
        $user->customer_package_id = $package_id;
        $customer_package = CustomerPackage::findOrFail($package_id);
        $user->remaining_uploads += $customer_package->product_upload;
        $user->save();
        return 'success';
    }
}

//Commission Calculation
if (!function_exists('calculateCommissionAffilationClubPoint')) {
    function calculateCommissionAffilationClubPoint($order)
    {
        $commissionController = new CommissionController();
        $commissionController->calculateCommission($order);

        if (addon_is_activated('affiliate_system')) {
            $affiliateController = new AffiliateController;
            $affiliateController->processAffiliatePoints($order);
        }

        if (addon_is_activated('club_point')) {
            if ($order->user != null) {
                $clubpointController = new ClubPointController;
                $clubpointController->processClubPoints($order);
            }
        }
        $order->commission_calculated = 1;
        $order->save();
    }
}

// Addon Activation Check
if (!function_exists('addon_is_activated')) {
    function addon_is_activated($identifier, $default = null)
    {
        $addons = Cache::remember('addons', 86400, function () {
            return Addon::all();
        });
        $activation = $addons->where('unique_identifier', $identifier)->where('activated', 1)->first();
        return $activation == null ? false : true;
    }
}
if (!function_exists('get_positive_seller_ratting')) {
    function get_positive_seller_ratting($sellerId)
    {
        $reviews = DB::table('reviews')
        ->orderBy('id', 'desc')
        ->join('products', 'reviews.product_id', '=', 'products.id')
        ->where('products.user_id', $sellerId)
        ->average('reviews.rating');
        return round(($reviews*100)/5);
    }
}

if (!function_exists('get_city_name')) {
    function get_city_name($cityId){
        $city_name = DB::table('cities')
        ->select('name')
        ->where('id', $cityId)
        ->pluck('name');
        return $city_name;
    }
}

if (!function_exists('get_country_name')) {
    function get_country_name($countryId){
        $country_name = DB::table('countries')
        ->select('name')
        ->where('id', $countryId)
        ->pluck('name');
        return $country_name;
    }
}

if (!function_exists('get_state_name')) {
    function get_state_name($stateId){
        $state_name = DB::table('states')
        ->select('name')
        ->where('id', $stateId)
        ->pluck('name');
        return $state_name;
    }
}

function offerCount()
{
	$net_offer=0;
	$currentdate=strtotime(date('Y-m-d h:i:sa'));
    $conditions = ['published' => 1];
    $products = Product::where($conditions);
    $products->whereRaw('net_total_discount > 0')
    ->where('discount_end_date','=','0')->get();
    if(!empty($products)){
    	$net_offer=$net_offer+$products->count();
    }
	return $net_offer;
}

function getVarianImage($product_id,$variation=''){
    $varient_image = ProductStock::where('product_stocks.product_id',$product_id)
    ->where('product_stocks.variant',$variation)->first();
    if($varient_image && !empty($variation)){
        return $varient_image->image;
    }else{
        $product = Product::find($product_id);
        return !empty($product) ? $product->thumbnail_img : '';
    }
}

function isJoinFlashDeas($falsh_id){
    return FlashDealProduct::Join('products','products.id','=','flash_deal_products.product_id')
    ->where('flash_deal_id',$falsh_id)
    ->where('products.user_id',Auth::user()->id)->count();
}

function daysDiff($date){
    $expdate = date('Y-m-d',$date);
    $date1 = new DateTime($expdate);
    $date2 = new DateTime(date('Y-m-d'));
    return $date1->diff($date2)->format('%a').' Days';
}