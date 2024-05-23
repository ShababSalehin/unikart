<?php

namespace App\Http\Controllers\Api\V2;
use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ShopCollection;
use App\Http\Resources\V2\ShopDetailsCollection;
use App\Http\Resources\V2\FollowShopCollection;
use App\Http\Resources\V2\CouponUsesList;
use App\Models\Product;
use App\Models\Shop;
use App\FirebaseNotification;
use App\Order;
use App\Coupon;
use App\FollowShop;
use App\Models\BusinessSetting;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use App\Utility\CategoryUtility;


class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shop_query = Shop::query();

        if ($request->name != null && $request->name != "") {
            $shop_query->where("name", 'like', "%{$request->name}%");
            SearchUtility::store($request->name);
        }

        return new ShopCollection($shop_query->whereIn('user_id', verified_sellers_id())->paginate(30));

        //remove this , this is for testing
        //return new ShopCollection($shop_query->paginate(10));
    }

    public function info($id)
    {
        return new ShopDetailsCollection(Shop::where('id', $id)->get());
    }

    public function shopOfUser($id)
    {
        return new ShopCollection(Shop::where('user_id', $id)->get());
    }

    public function allProducts($id)
    {
        $shop = Shop::findOrFail($id);
        return new ProductCollection(Product::where('user_id', $shop->user_id)->where('published',1)->latest()->paginate(10));
    }

    public function topSellingProducts($id)
    {
        $shop = Shop::findOrFail($id);
        return new ProductMiniCollection(Product::where('user_id', $shop->user_id)->where('published',1)->orderBy('num_of_sale', 'desc')->limit(10)->get());
    }

    public function featuredProducts($id)
    {
        $shop = Shop::findOrFail($id);
        return new ProductMiniCollection(Product::where(['user_id' => $shop->user_id, 'featured' => 1])->where('published',1)->latest()->limit(10)->get());
    }

    public function newProducts($id)
    {
        $shop = Shop::findOrFail($id);
        return new ProductMiniCollection(Product::where('user_id', $shop->user_id)->where('published',1)->orderBy('created_at', 'desc')->limit(10)->get());
    }

    public function brands($id)
    {

    }

    public function my_followed_shop(Request $request)
    {
        $shops = FollowShop::join('shops', 'follow_shops.shop_id', '=','shops.id')
        ->where('follow_shops.user_id', $request->user_id)->get();
        return new FollowShopCollection($shops);
    }

    public function applyed_coupon_code(Request $request)
    {

        $coupon_applyed = CouponUsage::join('coupons','coupon_usages.coupon_id','=','coupons.id')
        ->join('orders','coupon_usages.order_id','orders.id')
        ->select('orders.code as ordercode','coupons.*' ,'coupon_usages.*')
        ->where('coupon_usages.user_id', $request->user_id)->get();
        
        return new CouponUsesList($coupon_applyed);
    }

    public function new_coupon_list()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $new_coupon_list = CouponUsage::select('')
        ->whereRaw('end_date >=' .$today)->get();
        
        return new CouponUsesList($$new_coupon_list);
    }

    

    public function my_review(Request $request){

        $review = Product::join('reviews', 'reviews.product_id','=','products.id')
        ->where('reviews.user_id',$request->user_id)->get();
    
        $ids = $review->pluck('product_id');

        $noreview = Order::where('orders.user_id', $request->user_id)
        ->join('order_details','order_details.order_id', '=', 'orders.id')
        ->join('products','order_details.product_id', '=', 'products.id')
        ->where('orders.delivery_status','delivered')
        ->whereNotIn('order_details.product_id',$ids)->get();

        $rv = array();
                $nrv = array();

                foreach($review as $r){
                    $rv[] = [
                        'id'=> $r->id,
                        'name'=> $r->name,
                        'thumbnail_img'=> api_asset($r->thumbnail_img),
                        'created_at'=> $r->created_at,
                    ];
                }

                foreach($noreview as $r){
                    $nrv[] = [
                        'id'=> $r->id,
                        'name'=> $r->name,
                        'thumbnail_img'=> api_asset($r->thumbnail_img),
                        'created_at'=> $r->created_at,
                    ];
                }
                                  
        return response()->json(
            array('data'=>array('review'=>$rv,'noreview'=>$nrv),
        'success' => true,
        'status' => 200));
    }

    public function old_my_review(Request $request){

        $review = Product::join('reviews', 'reviews.product_id','=','products.id')
        ->where('reviews.user_id',$request->user_id)->get();
    
        $ids = $review->pluck('product_id');
        $noreview = Order::where('orders.user_id', $request->user_id)
        ->join('order_details','order_details.order_id', '=', 'orders.id')
        ->join('products','order_details.product_id', '=', 'products.id')
        ->where('orders.delivery_status','delivered')
        ->whereNotIn('order_details.product_id',$ids)->get();
        
        return response()->json(
            array('data'=>array('review'=>$review,'noreview'=>$noreview),
        'success' => true,
        'status' => 200));
    }


    public function follow_shop(Request $request){

             $followShop = new FollowShop;
             $followShop->user_id = $request->user_id;
             $followShop->shop_id = $request->shop_id;
             $followShop->save();
             
             return response()->json([
                 'result' => true,
                 'message' => 'You successfully following this shop'
             ]);
 
    }

    public function check_followshop(Request $request){
        $check_followshop = FollowShop::where('user_id','=',$request->user_id)
        ->where('shop_id','=',$request->shop_id)->first();

        return response()->json([
            'result' => !empty($check_followshop) ? true : false,
            'status' => 200
        ]);
    
        }


    public function unfollow_shop(Request $request)
    {
       
        FollowShop::where('user_id','=',$request->user_id)
        ->where('shop_id','=',$request->shop_id)->delete();
         return response()->json([
             'result' => true,
             'message' => 'You successfully unfollow this shop'
         ]);
         
        } 
        
        public function sortby_price(Request $request){

            $conditions = ['published' => 1];
            $category_id = $request->category_id;
            $products = Product::where($conditions);
            //dd($request->sortby);
            if($request->sortby == 'low_to_high'){

                if ($category_id != null) {
                    $category_ids = CategoryUtility::children_ids($category_id);
                    $category_ids[] = $category_id;
                    $products->whereIn('category_id', $category_ids)
                    ->orderBy('unit_price', 'asc');
                }
            }else{

                if ($category_id != null) {
                    $category_ids = CategoryUtility::children_ids($category_id);
                    $category_ids[] = $category_id;
                    $products->whereIn('category_id', $category_ids)
                    ->orderBy('unit_price', 'deasc');
                  
                }
            }

            $products = filter_products($products)->get();
           return response()->json($products);
        }


        public function filterby_category(Request $request){
            $conditions = ['published' => 1];
            $category_id = $request->category_id;
            $products = Product::where($conditions);
            if ($category_id != null) {
                $category_ids = CategoryUtility::children_ids($category_id);
                $category_ids[] = $category_id;
                $products->whereIn('category_id', $category_ids);
            }
            $products = filter_products($products)->get();
           return response()->json($products);
        }

        public function filterby_features(Request $request, $min_price=null,$max_price=null, $brand_id = null,$category_id = null){

            $discount = $request->discount;
            $convertinarrays= preg_split("/[,]/",$discount);
            $min_price = $request->min_price;
            $max_price = $request->max_price;
            $conditions = ['published' => 1];
            $category_id = $request->category_id;
            $brand_id = $request->brand_id;
            
            $products = Product::where($conditions);

            if ($category_id != null) {
                $category_ids = CategoryUtility::children_ids($category_id);
                $category_ids[] = $category_id;
                $products->whereIn('category_id', $category_ids);
            }

            if ($min_price != null && $max_price != null) {
                $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
            }

            if (!empty($convertinarrays)) {
                $disWhere = " (";
                foreach ($convertinarrays as $convertinarray) {
                    if ($convertinarray != null && $convertinarray != null) {
                        if ($convertinarray == 2) {
                            $disWhere .= " (discount>=2 and discount <=5) or ";
                        } elseif ($convertinarray == 6) {
                            $disWhere .= " (discount>=6 and discount <=10) or ";
                        } elseif ($convertinarray == 11) {
                            $disWhere .= " (discount>=11 and discount <=20) or ";
                        } elseif ($convertinarray == 21) {
                            $disWhere .= " (discount>=21 and discount <=30) or ";
                        } else {
                            $disWhere .= " (discount>=31) or ";
                        }
                    }
                }
                $disWhere = substr($disWhere, 0, -3).')';
                $products->whereRaw($disWhere);
                $products->where('discount_type', 'percent');
            }

            $brand_ids = array();
            if ($request->has('brand_id')) {
                $brand_ids = explode(',',$request->brand_id);
                $products->whereIn('brand_id',$brand_ids);
            }
         
            $products = filter_products($products)->get();
           return response()->json($products);
        }

        public function get_all_order_code(Request $request)
        {
           
             $orders_codes = Order::where('combined_order_id',$request->combined_order_id)
            ->select('code')->get();
             return response()->json([
                'data' => $orders_codes,
                 'result' => true,
                 'message' => 'You successfully get all order code'
             ]);
            
           }
           
           
       public function  get_firebase_notification(Request $request)
       {
        $firebase_notification = FirebaseNotification::where(['receiver_id' => $request->receiver_id, 'item_type' => $request->type ])->get();
            return response()->json([
               'data' => $firebase_notification,
                'result' => true,
                'message' => 'You successfully get firebase notification'
            ]);
           
          }  

          public function new_coupon(Request $request)
          {
              $today = strtotime(date('Y-m-d'));
              $new_coupon = Coupon::where('type','cart_base')
              ->where('user_id',9)
              ->where('start_date',"<=", $today)
              ->where('end_date', '>=', $today)->get();
             
              return new CouponUsesList($new_coupon);
          }


          public function app_footer(){

            $footer_logo = BusinessSetting::where('type','app_footer')->first();
            return Response()->json([
                'app_footer' => api_asset($footer_logo->value),
                'result' => true,
                'message' => 'You are Successfully get app footer logo'
            ]);
          }

}
