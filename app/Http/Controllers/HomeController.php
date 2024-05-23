<?php

namespace App\Http\Controllers;

use App\Address;
use Illuminate\Http\Request;
use Auth;
use Hash;
use DB;
use App\Blog;
use App\Category;
use App\FlashDeal;
use App\Brand;
use App\Product;
use App\PickupPoint;
use App\CustomerPackage;
use App\User;
use App\Seller;
use App\Shop;
use App\FollowShop;
use App\Order;
use App\CouponUsage;
use App\BusinessSetting;
use App\Area;
use App\City;
use Cookie;
use Illuminate\Support\Str;
use App\Mail\SecondEmailVerifyMailManager;
use App\Models\FlashDealProduct;
use Mail;
use Illuminate\Auth\Events\PasswordReset;
use Cache;


class HomeController extends Controller
{
    /**
     * Show the application frontend home.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $featured_categories = Cache::rememberForever('featured_categories', function () {
            return Category::where('featured', 1)->get();
        });

        $todays_deal_products = Cache::rememberForever('todays_deal_products', function () {
            return filter_products(Product::where('published', 1)->where('todays_deal', '1'))->get();
        });

        $blogs = Blog::orderBy('created_at', 'desc')->paginate(4);
        return view('frontend.index', compact('featured_categories', 'todays_deal_products', 'blogs'));
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('frontend.user_login');
    }

    public function login_with_email()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('frontend.user_email_login');
    }

    public function registration(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        if (
            $request->has('referral_code') &&
            \App\Addon::where('unique_identifier', 'affiliate_system')->first() != null &&
            \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated
        ) {

            try {
                $affiliate_validation_time = \App\AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24;
                if ($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }

                Cookie::queue('referral_code', $request->referral_code, $cookie_minute);
                $referred_by_user = User::where('referral_code', $request->product_referral_code)->first();

                $affiliateController = new AffiliateController;
                $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            } catch (\Exception $e) {
            }
        }
        return view('frontend.user_registration');
    }

    public function cart_login(Request $request)
    {
        $user = User::whereIn('user_type', ['customer', 'seller'])->where('email', $request->email)->orWhere('phone', $request->email)->first();
        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                if ($request->has('remember')) {
                    auth()->login($user, true);
                } else {
                    auth()->login($user, false);
                }
            } else {
                flash(translate('Invalid email or password!'))->warning();
            }
        }
        return back();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the customer/seller dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        if (Auth::user()->user_type == 'seller') {


            $today = strtotime(date('Y-m-d H:i:s'));

            $data['all_flash_deals'] = FlashDeal::where('status', 1)
                ->where('seller_joinstart_date', "<=", $today)
                ->where('seller_joinend_date', ">=", $today)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('frontend.user.seller.dashboard', $data);
        } elseif (Auth::user()->user_type == 'customer') {
            return view('frontend.user.customer.dashboard');
        } elseif (Auth::user()->user_type == 'delivery_boy') {
            return view('delivery_boys.frontend.dashboard');
        } else {
            abort(404);
        }
    }

    public function campaign()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $data['all_flash_deals'] = FlashDeal::where('status', 1)
            //  ->where('seller_joinstart_date', "<=", $today)
            // ->where('seller_joinend_date', ">=", $today)
            // ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.user.seller.campaign', $data);
    }

    public function profile(Request $request)
    {
        if (Auth::user()->user_type == 'customer') {
            return view('frontend.user.customer.profile');
        } elseif (Auth::user()->user_type == 'delivery_boy') {
            return view('delivery_boys.frontend.profile');
        } elseif (Auth::user()->user_type == 'seller') {
            return view('frontend.user.seller.profile');
        }
    }


    //Added by alauddin start
    public function customer_update_profile(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->name = $request->name;
        //$user->phone = $request->phone;
        $user->avatar_original = $request->photo;

        if ($user->save()) {
            flash(translate('Your Profile has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }



    public function customer_change_password(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();

        if ($request->new_password != null && ($request->new_password == $request->confirm_password)) {
            $user->password = Hash::make($request->new_password);
        }

        if ($user->save()) {
            flash(translate('Your Password has been changed successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }


    public function customer_change_payment_method(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $customer = $user->customer;
        $customer->bkash_ac = $request->bkash_ac;
        $customer->bank_name = $request->bank_name;
        $customer->bank_acc_name = $request->bank_acc_name;
        $customer->bank_acc_no = $request->bank_acc_no;
        $customer->bank_branch_name = $request->bank_branch_name;

        if ($customer->save()) {
            flash(translate('Your Payment method has been changed successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }



    //Added by alauddin end






    public function customer_update_profile_12_09_2022(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone = $request->phone;

        if ($request->new_password != null && ($request->new_password == $request->confirm_password)) {
            $user->password = Hash::make($request->new_password);
        }
        $user->avatar_original = $request->photo;

        $customer = $user->customer;
        $customer->bkash_ac = $request->bkash_ac;
        $customer->bank_name = $request->bank_name;
        $customer->bank_acc_name = $request->bank_acc_name;
        $customer->bank_acc_no = $request->bank_acc_no;
        $customer->bank_branch_name = $request->bank_branch_name;

        if ($user->save() && $customer->save()) {
            flash(translate('Your Profile has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function user_accounts(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->bkash_ac = $request->bkash_ac;
        $user->bank_ac = $request->bank_ac;

        if ($user->save()) {
            flash(translate('Your Payment method has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }


    public function seller_update_profile(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone = $request->phone;

        if ($request->new_password != null && ($request->new_password == $request->confirm_password)) {
            $user->password = Hash::make($request->new_password);
        }
        $user->avatar_original = $request->photo;

        $seller = $user->seller;
        $seller->cash_on_delivery_status = $request->cash_on_delivery_status;
        $seller->bank_payment_status = $request->bank_payment_status;
        $seller->bank_name = $request->bank_name;
        $seller->bank_acc_name = $request->bank_acc_name;
        $seller->bank_acc_no = $request->bank_acc_no;
        $seller->bank_routing_no = $request->bank_routing_no;

        if ($user->save() && $seller->save()) {
            flash(translate('Your Profile has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function all_flash_deals()
    {
        $today = strtotime(date('Y-m-d H:i:s'));

        $data['all_flash_deals'] = FlashDeal::where('status', 1)
            ->where('start_date', ">", $today)
            ->where('end_date', ">", $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view("frontend.flash_deal.all_flash_deal_list", $data);
    }

    public function flash_deal_details_old($slug)
    {
        $flash_deal = FlashDeal::where('slug', $slug)->first();
        if ($flash_deal != null)
            return view('frontend.flash_deal_details', compact('flash_deal'));
        else {
            abort(404);
        }
    }

    public function flash_deal_details($slug)
    {
        $flash_deal = FlashDeal::where('slug', $slug)->orderBy('id', 'desc')->first();
        $products = FlashDealProduct::leftjoin('products', 'flash_deal_products.product_id', 'products.id')
            ->select('products.*')->where('flash_deal_products.flash_deal_id', $flash_deal->id)
            ->orderBy('flash_deal_products.id', 'desc')
            ->paginate(36);
        if ($products)
            return view('frontend.flash_deal_details', compact('flash_deal', 'products'));
        else {
            abort(404);
        }
    }


    public function all_new_customers_offers()
    {
        $today = strtotime(date('Y-m-d H:i:s'));


        $flash_deal = FlashDeal::where('status', 1)
            ->where('campaign_type', 'First Order')
            ->first();
        if ($flash_deal != null)
            return view("frontend.flash_deal.all_new_customers_offer_list", compact('flash_deal'));
        else {
            abort(404);
        }
    }




    public function load_featured_section()
    {
        return view('frontend.partials.featured_products_section');
    }

    public function load_product_for_you_section()
    {
        $product_for_you = DB::table('products')->where('published', 1)->where('product_for_you', 1)
            ->orderBy('id', 'DESC')->get();
        return view('frontend.partials.product_for_you_section', compact('product_for_you'));
    }

    public function load_best_selling_section()
    {
        return view('frontend.partials.best_selling_section');
    }

    public function load_auction_products_section()
    {
        if (!addon_is_activated('auction')) {
            return;
        }
        return view('auction.frontend.auction_products_section');
    }

    public function load_home_categories_section()
    {
        return view('frontend.partials.home_categories_section');
    }

    public function load_best_sellers_section()
    {
        return view('frontend.partials.best_sellers_section');
    }

    public function trackOrder(Request $request)
    {
        if ($request->has('order_code')) {

            $order = Order::where('code', $request->order_code)
                ->where('user_id', Auth()->user()->id)
                ->first();
            if ($order != null) {
                return view('frontend.track_order', compact('order'));
            } else {
                flash(translate('Order not found.'))->warning();
                return back();
            }
        }
        return view('frontend.track_order');
    }


    public function product(Request $request, $slug)
    {

        if (preg_match('/[A-Z]/', $slug)) {
            return redirect()->route('product', strtolower($slug), 301);
        }

        $detailedProduct  = Product::with('reviews', 'brand', 'stocks', 'user', 'user.shop')->where('slug', $slug)->where('approved', 1)->first();
        if ($detailedProduct == null) {
            $detailedProduct  = Product::with('reviews', 'brand', 'stocks', 'user', 'user.shop')->where('slug_old', $slug)->where('approved', 1)->first();
            return redirect()->route('product', strtolower($detailedProduct->slug), 301);
        }

        if ($detailedProduct != null && $detailedProduct->published) {
            if (
                $request->has('product_referral_code') &&
                \App\Addon::where('unique_identifier', 'affiliate_system')->first() != null &&
                \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated
            ) {

                $affiliate_validation_time = \App\AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24;
                if ($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }
                Cookie::queue('product_referral_code', $request->product_referral_code, $cookie_minute);
                Cookie::queue('referred_product_id', $detailedProduct->id, $cookie_minute);

                $referred_by_user = User::where('referral_code', $request->product_referral_code)->first();

                $affiliateController = new AffiliateController;
                $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            }
            if ($detailedProduct->digital == 1) {
                return view('frontend.digital_product_details', compact('detailedProduct'));
            } else {
                return view('frontend.product_details', compact('detailedProduct'));
            }
        }
        return redirect()->route('home');
    }

    public function shop($slug)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if ($shop != null) {
            $seller = Seller::where('user_id', $shop->user_id)->first();
            if ($seller->verification_status != 0) {
                return view('frontend.seller_shop', compact('shop'));
            } else {
                return view('frontend.seller_shop_without_verification', compact('shop', 'seller'));
            }
        }
        abort(404);
    }

    public function filter_shop($slug, $type)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if ($shop != null && $type != null) {
            return view('frontend.seller_shop', compact('shop', 'type'));
        }
        abort(404);
    }

    public function all_categories(Request $request)
    {
        //        $categories = Category::where('level', 0)->orderBy('name', 'asc')->get();
        $categories = Category::where('level', 0)->orderBy('order_level', 'desc')->get();
        return view('frontend.all_category', compact('categories'));
    }
    public function coupon_usage()
    {
        $coupons = CouponUsage::join('coupons', 'coupons.id', '=', 'coupon_usages.coupon_id')->join('orders', 'orders.id', '=', 'coupon_usages.order_id')->where('coupon_usages.user_id', Auth::user()->id)->select('coupons.code as coupon_code', 'coupon_usages.coupon_id', 'orders.*')->orderBy('orders.date', 'desc')->get();
        return view('frontend.user.coupon_usage', compact('coupons'));
    }
    public function all_brands(Request $request)
    {
        $categories = Category::all();
        return view('frontend.all_brand', compact('categories'));
    }

    public function show_product_upload_form(Request $request)
    {
        if (addon_is_activated('seller_subscription')) {
            if (Auth::user()->seller->remaining_uploads > 0) {
                $categories = Category::where('parent_id', 0)
                    ->where('digital', 0)
                    ->with('childrenCategories')
                    ->get();
                return view('frontend.user.seller.product_upload', compact('categories'));
            } else {
                flash(translate('Upload limit has been reached. Please upgrade your package.'))->warning();
                return back();
            }
        }
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        return view('frontend.user.seller.product_upload', compact('categories'));
    }

    public function show_product_edit_form(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('frontend.user.seller.product_edit', compact('product', 'categories', 'tags', 'lang'));
    }

    public function seller_product_list(Request $request)
    {
        $col_name = null;
        $query = null;
        $search = null;
        $sort_type = null;

        $products = Product::where('user_id', Auth::user()->id)->where('digital', 0)->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $search = $request->search;
            $products = $products->where('name', 'like', '%' . $search . '%');
        }


        if ($request->type != null) {
            $col_name = $request->type;
            $products = $products->where('approved', $col_name, $query);
            $sort_type = $request->type;
        }


        $products = $products->paginate(10);
        return view('frontend.user.seller.products', compact('products', 'search', 'sort_type', 'col_name', 'query'));
    }

    public function admin_approved_product(Request $request)
    {
        $col_name = null;
        $query = null;
        $search = null;
        $sort_type = null;

        $products = Product::where('user_id', Auth::user()->id)
            ->where('digital', 0)
            ->where('approved', '1')
            ->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $search = $request->search;
            $products = $products->where('name', 'like', '%' . $search . '%');
        }


        if ($request->type != null) {
            $col_name = $request->type;
            $products = $products->where('approved', $col_name, $query);
            $sort_type = $request->type;
        }


        $products = $products->paginate(10);
        return view('frontend.user.seller.products', compact('products', 'search', 'sort_type', 'col_name', 'query'));
    }
    public function seller_pending_product(Request $request)
    {
        $col_name = null;
        $query = null;
        $search = null;
        $sort_type = null;

        $products = Product::where('user_id', Auth::user()->id)
            ->where('digital', 0)
            ->where('approved', '0')
            ->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $search = $request->search;
            $products = $products->where('name', 'like', '%' . $search . '%');
        }


        if ($request->type != null) {
            $col_name = $request->type;
            $products = $products->where('approved', $col_name, $query);
            $sort_type = $request->type;
        }


        $products = $products->paginate(10);
        return view('frontend.user.seller.products', compact('products', 'search', 'sort_type', 'col_name', 'query'));
    }


    public function home_settings(Request $request)
    {
        return view('home_settings.index');
    }

    public function top_10_settings(Request $request)
    {
        foreach (Category::all() as $key => $category) {
            if (is_array($request->top_categories) && in_array($category->id, $request->top_categories)) {
                $category->top = 1;
                $category->save();
            } else {
                $category->top = 0;
                $category->save();
            }
        }

        foreach (Brand::all() as $key => $brand) {
            if (is_array($request->top_brands) && in_array($brand->id, $request->top_brands)) {
                $brand->top = 1;
                $brand->save();
            } else {
                $brand->top = 0;
                $brand->save();
            }
        }

        flash(translate('Top 10 categories and brands have been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }

    public function variant_price(Request $request)
    {
        $product = Product::find($request->id);
        $str = '';
        $quantity = 0;
        $tax = 0;
        $max_limit = $product->max_qty;

        if ($request->has('color')) {
            $str = $request['color'];
        }

        if (json_decode($product->choice_options) != null) {
            foreach (json_decode($product->choice_options) as $key => $choice) {
                if ($str != null) {
                    $str .= '-' . str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                } else {
                    $str .= str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                }
            }
        }


        //added by alauddin start
        if ($request->attribute) {
            $str = $request->attribute;
        }
        // added by alauddin end

        // if (($product->max_qty) < $request->quantity) {
        //     $msg = 'You Can not add more than ' . ($product->max_qty) . ' Quantity for this product';
        //     return array('status' => false, 'msg' => $msg, 'quantity' => $product->max_qty);
        // }

        $product_stock = $product->stocks->where('variant', $str)->first();
        $price = $product_stock->price;
        $quantity = $product_stock->qty;
        //$max_limit = $product_stock->qty;
        //        if($str != null && $product->variant_product){
        //        }
        //        else{
        //            $price = $product->unit_price;
        //            $quantity = $product->current_stock;
        //        }

        if ($quantity >= 1 && $product->min_qty <= $quantity) {
            $in_stock = 1;
        } else {
            $in_stock = 0;
        }

        //Product Stock Visibility
        if ($product->stock_visibility_state == 'text') {
            if ($quantity >= 1 && $product->min_qty < $quantity) {
                $quantity = translate('In Stock');
            } else {
                $quantity = translate('Out Of Stock');
            }
        }

        //discount calculation
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

            //added by alauddin start

            if ($product->discount_type == 'percent') {
                $price -= ($product->unit_price * $product->unikart_discount) / 100; //added by alauddin
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->unikart_discount; //added by alauddin
            }

            //added by alauddin end
        }


        // taxes
        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        $price += $tax;

        return array(
            'price' => single_price($price * $request->quantity),
            'single_price' => single_price($price),
            'quantity' => $quantity,
            'digital' => $product->digital,
            'variation' => $str,
            'max_limit' => $max_limit,
            'in_stock' => $in_stock
        );
    }

    public function sellerpolicy()
    {
        return view("frontend.policies.sellerpolicy");
    }

    public function returnpolicy()
    {
        return view("frontend.policies.returnpolicy");
    }

    public function supportpolicy()
    {
        return view("frontend.policies.supportpolicy");
    }

    public function terms()
    {
        return view("frontend.policies.terms");
    }

    public function privacypolicy()
    {
        return view("frontend.policies.privacypolicy");
    }

    public function get_pick_up_points(Request $request)
    {
        $pick_up_points = PickupPoint::all();
        return view('frontend.partials.pick_up_points', compact('pick_up_points'));
    }

    public function get_category_items(Request $request)
    {
        $category = Category::findOrFail($request->id);
        return view('frontend.partials.category_elements', compact('category'));
    }

    public function premium_package_index()
    {
        $customer_packages = CustomerPackage::all();
        return view('frontend.user.customer_packages_lists', compact('customer_packages'));
    }

    public function seller_digital_product_list(Request $request)
    {
        $products = Product::where('user_id', Auth::user()->id)->where('digital', 1)->orderBy('created_at', 'desc')->paginate(10);
        return view('frontend.user.seller.digitalproducts.products', compact('products'));
    }
    public function show_digital_product_upload_form(Request $request)
    {
        if (addon_is_activated('seller_subscription')) {
            if (Auth::user()->seller->remaining_digital_uploads > 0) {
                $business_settings = BusinessSetting::where('type', 'digital_product_upload')->first();
                $categories = Category::where('digital', 1)->get();
                return view('frontend.user.seller.digitalproducts.product_upload', compact('categories'));
            } else {
                flash(translate('Upload limit has been reached. Please upgrade your package.'))->warning();
                return back();
            }
        }

        $business_settings = BusinessSetting::where('type', 'digital_product_upload')->first();
        $categories = Category::where('digital', 1)->get();
        return view('frontend.user.seller.digitalproducts.product_upload', compact('categories'));
    }

    public function show_digital_product_edit_form(Request $request, $id)
    {
        $categories = Category::where('digital', 1)->get();
        $lang = $request->lang;
        $product = Product::find($id);
        return view('frontend.user.seller.digitalproducts.product_edit', compact('categories', 'product', 'lang'));
    }

    // Ajax call
    public function new_verify(Request $request)
    {
        //         $email = $request->email;
        //         if(isUnique($email) == '0') {
        //             $response['status'] = 2;
        //             $response['message'] = 'Email already exists!';
        //             return json_encode($response);
        //         }

        //         $response = $this->send_email_change_verification_mail($request, $email);
        //         return json_encode($response);

        $email = $request->email;
        if (!empty($email)) {
            if (isUnique($email) == '0') {
                $response['status'] = 2;
                $response['message'] = 'Email already exists!';
                return json_encode($response);
            }
        } else {
            $response['status'] = 2;
            $response['message'] = 'Pleas fill email field!';
            return json_encode($response);
        }

        $response = $this->send_email_change_verification_mail($request, $email);
        return json_encode($response);
    }


    // Form request
    public function update_email(Request $request)
    {
        //         $email = $request->email;
        //         if(isUnique($email)) {
        //             $this->send_email_change_verification_mail($request, $email);
        //             flash(translate('A verification mail has been sent to the mail you provided us with.'))->success();
        //             return back();
        //         }

        //         flash(translate('Email already exists!'))->warning();
        //         return back();

        $email = $request->email;

        if (isUnique($email)) {
            $this->send_email_change_verification_mail($request, $email);
            flash(translate('A verification mail has been sent to the mail you provided us with.'))->success();
            return back();
        } else {
            if (!empty($email)) {
                flash(translate('Email already exists!'))->warning();
            } else {
                flash(translate('Please fill email field!'))->warning();
            }
        }

        return back();
    }

    public function send_email_change_verification_mail($request, $email)
    {
        $response['status'] = 0;
        $response['message'] = 'Unknown';

        $verification_code = Str::random(32);

        $array['subject'] = 'Email Verification';
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Verify your account';
        $array['link'] = route('email_change.callback') . '?new_email_verificiation_code=' . $verification_code . '&email=' . $email;
        $array['sender'] = Auth::user()->name;
        $array['details'] = "Email Second";

        $user = Auth::user();
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

    public function email_change_callback(Request $request)
    {
        if ($request->has('new_email_verificiation_code') && $request->has('email')) {
            $verification_code_of_url_param =  $request->input('new_email_verificiation_code');
            $user = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if ($user != null) {

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

    public function reset_password_with_code(Request $request)
    {
        if (($user = User::where('email', $request->email)->where('verification_code', $request->code)->first()) != null) {
            if ($request->password == $request->password_confirmation) {
                $user->password = Hash::make($request->password);
                $user->email_verified_at = date('Y-m-d h:m:s');
                $user->save();
                event(new PasswordReset($user));
                auth()->login($user, true);

                flash(translate('Password updated successfully'))->success();

                if (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff') {
                    return redirect()->route('admin.dashboard');
                }
                return redirect()->route('home');
            } else {
                flash("Password and confirm password didn't match")->warning();
                return redirect()->route('password.request');
            }
        } else {
            flash("Verification code mismatch")->error();
            return redirect()->route('password.request');
        }
    }

    public function all_seller(Request $request)
    {
        $shops = Shop::whereIn('user_id', verified_sellers_id())
            ->paginate(15);

        return view('frontend.shop_listing', compact('shops'));
    }

    // Store Search
    public function store_search(Request $request)
    {
        $city = $request->city;
        $area = $request->area;
        $areaname = Area::where('id', $area)->first();
        $shops = Shop::where('area_id', $area)->whereIn('user_id', verified_sellers_id())
            ->paginate(15);

        return view('frontend.shop_search', compact('shops', 'areaname'));
    }
    public function singel_area($id)
    {

        $areaname = Area::where('id', $id)->first();
        $shops = Shop::where('area_id', $id)->whereIn('user_id', verified_sellers_id())
            ->paginate(15);

        return view('frontend.shop_search', compact('shops', 'areaname'));
    }

    function loadArea(Request $request)
    {

        $states = Area::where('citi_id', $request->id)->get();
        //you can handle output in different ways, I just use a custom filled array. you may pluck data and directly output your data.

        $output = '<option value="">Select Area</option>';
        foreach ($states as $state) {
            $output .= '<option value="' . $state->id . '">' . $state->name . '</option>';
        }
        return $output;
    }
    public function save_more_app()
    {


        return view('frontend.save_more_app');
    }
    public function go_live_chat()
    {


        return view('frontend.go_live_chat');
    }
    public function shop_following($id)
    {

        if (Auth::check()) {
            $user_id = Auth::user()->id;
            $followShop = new FollowShop;
            $followShop->user_id = $user_id;
            $followShop->shop_id = $id;
            $followShop->save();

            flash(translate('You successfully followed this shop'))->success();
            return back();
        } else {
            return redirect()->route('user.login');
        }
    }
    public function followed_shop()
    {

        $user_id = Auth::user()->id;
        $shops = FollowShop::join('shops', 'shops.id', 'follow_shops.shop_id')->where('follow_shops.user_id', Auth::user()->id)->get();
        return view('frontend.user.followed_shop', compact('shops'));
    }

    public function my_review()
    {
        $review = Product::join('reviews', 'reviews.product_id', '=', 'products.id')
            ->where('reviews.user_id', Auth::user()->id)->get();
        $ids = $review->pluck('product_id');
        $noreview = Order::where('orders.user_id', Auth::user()->id)
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.delivery_status', 'delivered')
            ->whereNotIn('order_details.product_id', $ids)->get();

        //dd($noreview);
        return view('frontend.user.customer.myreviews', compact('review', 'noreview'));
    }

    public function unfollow_shop()
    {
        $unfollowshop = FollowShop::where('user_id', Auth::user()->id)->delete();
        flash(translate('You successfully unfollowed this shop'))->success();
        return back();
    }

    public function offers(Request $request, $category_id = null, $brand_id = null)
    {
        $currentdate = strtotime(date('Y-m-d h:i:sa'));
        $query = $request->q;
        $sort_by = $request->sort_by;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $seller_id = $request->seller_id;

        $conditions = ['published' => 1];
        $products = Product::where($conditions);
        $products->whereRaw('net_total_discount > 0')->where('discount_end_date', '=', '0')->inRandomOrder();
        $products = filter_products($products)->paginate(40)->appends(request()->query());
    
        return view("frontend.offer.offers", compact('products','query', 'category_id', 'brand_id', 'sort_by', 'seller_id', 'min_price', 'max_price'));
    }
    public function fornew_customer()
    {
        return view("frontend.offer.fornew_customer");
    }
    public function income_details_report(Request $request, $id)
    {
        $date_range = null;
        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
        }

        $sellers = Order::where(['seller_id' => $id, 'delivery_status' => 'delivered'])
            ->orderBy('created_at', 'desc');
        $sellers = $sellers->paginate(10);
        //dd( $sellers);
        $seller_id = $id;
        return view('frontend.user.seller.seller_income_details_report', compact('sellers', 'date_range', 'seller_id'));
    }

    public function get_shipping_cost(Request $request)
    {
        $address = Address::where('id', $request->city_id)->first();
        $shipping_cost = City::where('id', $address->city_id)->select('cost')->first();
        return $shipping_cost;
    }
}
