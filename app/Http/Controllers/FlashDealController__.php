<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FlashDeal;
use App\FlashDealTranslation;
use App\FlashDealProduct;
use App\Product;
use Auth;
use App\Shop;
use DB;
use Illuminate\Support\Str;

class FlashDealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $flash_deals = FlashDeal::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $flash_deals = $flash_deals->where('title', 'like', '%'.$sort_search.'%');
        }
        
        $flash_deals = $flash_deals->paginate(15);
        return view('backend.marketing.flash_deals.index', compact('flash_deals', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.marketing.flash_deals.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $flash_deal = new FlashDeal;
        $flash_deal->title = $request->title;
        $flash_deal->text_color = $request->text_color;

        $date_var               = explode(" to ", $request->date_range);
        $flash_deal->start_date = strtotime($date_var[0]);
        $flash_deal->end_date   = strtotime( $date_var[1]);
        
        $date_var2                 = explode(" to ", $request->seller_range);
        //dd($date_var2[0]);
        $flash_deal->seller_joinstart_date = strtotime( $date_var2   [0]);
        $flash_deal->seller_joinend_date   = strtotime( $date_var2   [1]);

        $flash_deal->background_color = $request->background_color;
    	$flash_deal->minimum_amount = $request->minimum_amount;
        $flash_deal->slug = strtolower(str_replace(' ', '-', $request->title).'-'.Str::random(5));
        $flash_deal->banner = $request->banner;
        if($flash_deal->save()){
        if(!empty($request->products)){
            foreach ($request->products as $key => $product) {
                $flash_deal_product = new FlashDealProduct;
                $flash_deal_product->flash_deal_id = $flash_deal->id;
                $flash_deal_product->product_id = $product;
            	$flash_deal_product->discount = $request['discount_'.$product]; //added by alauddin
                $flash_deal_product->discount_type = $request['discount_type_'.$product]; //added by alauddin
                $flash_deal_product->save();
				if($flash_deal->title!="New Customer Offer"){
                	$root_product = Product::findOrFail($product);
                	$root_product->discount = $request['discount_'.$product];
                	$root_product->discount_type = $request['discount_type_'.$product];
                	$root_product->discount_start_date = strtotime($date_var[0]);
                	$root_product->discount_end_date   = strtotime( $date_var[1]);
                	$root_product->save();
                }
            }
        }

            $flash_deal_translation = FlashDealTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'flash_deal_id' => $flash_deal->id]);
            $flash_deal_translation->title = $request->title;
            $flash_deal_translation->save();

            flash(translate('Flash Deal has been inserted successfully'))->success();
            return redirect()->route('flash_deals.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
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
    public function edit(Request $request, $id)
    {
        $lang           = $request->lang;
        $flash_deal = FlashDeal::findOrFail($id);
        if(Auth::user()->user_type == 'admin') {
        return view('backend.marketing.flash_deals.edit', compact('flash_deal','lang'));
        }

        if(Auth::user()->user_type == 'seller') {
            return view('frontend.user.seller.flash_deals.edit', compact('flash_deal','lang'));
        }

    }

  

    public function sellerfedit(Request $request, $id)
    {
        $lang           = $request->lang;
        $flash_deal = FlashDeal::findOrFail($id);

        return view('frontend.user.seller.flash_deals.edit', compact('flash_deal','lang'));
        
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
        if(Auth::user()->user_type == 'admin') {

        $flash_deal = FlashDeal::findOrFail($id);
        //
        //dd($flash_deal);

        $flash_deal->text_color = $request->text_color;

        $date_var               = explode(" to ", $request->date_range);
        $flash_deal->start_date = strtotime($date_var[0]);
        $flash_deal->end_date   = strtotime( $date_var[1]);

        $date_var2                 = explode(" to ", $request->seller_range);
        $flash_deal->seller_joinstart_date = strtotime( $date_var2   [0]);
        $flash_deal->seller_joinend_date   = strtotime( $date_var2   [1]);

        $flash_deal->background_color = $request->background_color;
        $flash_deal->minimum_amount = $request->minimum_amount;

        if($request->lang == env("DEFAULT_LANGUAGE")){
          $flash_deal->title = $request->title;
          if (($flash_deal->slug == null) || ($flash_deal->title != $request->title)) {
              $flash_deal->slug = strtolower(str_replace(' ', '-', $request->title) . '-' . Str::random(5));
          }
        }

        $flash_deal->banner = $request->banner;
        foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product) {
            $flash_deal_product->delete();
        }

        if($flash_deal->save()){
        if(!empty($request->products)){
            foreach ($request->products as $key => $product) {
                $flash_deal_product = new FlashDealProduct;
                $flash_deal_product->flash_deal_id = $flash_deal->id;
                $flash_deal_product->product_id = $product;
            	$flash_deal_product->discount = $request['discount_'.$product]; //added by alauddin
                $flash_deal_product->discount_type = $request['discount_type_'.$product]; //added by alauddin
                $flash_deal_product->save();
				if($flash_deal->title!="New Customer Offer"){
                	$root_product = Product::findOrFail($product);
                	$root_product->discount = $request['discount_'.$product];
                	$root_product->discount_type = $request['discount_type_'.$product];
                	$root_product->discount_start_date = strtotime($date_var[0]);
                	$root_product->discount_end_date   = strtotime( $date_var[1]);
                	$root_product->save();
                }
            }
        }

            $sub_category_translation = FlashDealTranslation::firstOrNew(['lang' => $request->lang, 'flash_deal_id' => $flash_deal->id]);
            $sub_category_translation->title = $request->title;
            $sub_category_translation->save();

            flash(translate('Flash Deal has been updated successfully'))->success();
            return back();
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }

     }

            if(Auth::user()->user_type == 'seller') {
              $flash_deal = FlashDeal::findOrFail($id);
          
            foreach ($request->products as $key => $product) {
                $check_product_id = FlashDealProduct::where('product_id', $product)
                ->where('flash_deal_id',$id)
                ->first();

                if(!empty($check_product_id)){
                    $check_product_id->flash_deal_id= $flash_deal->id;
                    $check_product_id->product_id = $product;
                    $check_product_id->discount = $request['discount_'.$product]; //added by alauddin
                    $check_product_id->discount_type = $request['discount_type_'.$product]; //added by alauddin
                    $check_product_id->update();
                    
                }else{
                    $check_product_id = new FlashDealProduct;
                    $check_product_id->flash_deal_id= $flash_deal->id;
                    $check_product_id->product_id = $product;
                    $check_product_id->discount = $request['discount_'.$product]; //added by alauddin
                    $check_product_id->discount_type = $request['discount_type_'.$product]; //added by alauddin
                    $check_product_id->save();
                }
             
				if($flash_deal->title!="New Customer Offer"){
                	$root_product = Product::findOrFail($product);
                	$root_product->discount = $request['discount_'.$product];
                	$root_product->discount_type = $request['discount_type_'.$product];
					
                	$root_product->save();
                }
            }

        flash(translate('Flash Deal has been updated successfully'))->success();
            return back();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $flash_deal = FlashDeal::findOrFail($id);
        foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product) {
            $flash_deal_product->delete();
        }

        foreach ($flash_deal->flash_deal_translations as $key => $flash_deal_translation) {
            $flash_deal_translation->delete();
        }

        FlashDeal::destroy($id);
        flash(translate('FlashDeal has been deleted successfully'))->success();
        return redirect()->route('flash_deals.index');
    }

    public function reports(Request $request){

        $products = FlashDealProduct::where('flash_deal_id',$request->id)
        ->join('products','products.id', '=', 'flash_deal_products.product_id')
        ->join('shops','shops.user_id', '=', 'products.user_id')
        ->groupBy('products.user_id')
        ->select('products.user_id','shops.name','shops.id','flash_deal_products.flash_deal_id',DB::raw('COUNT(products.id) as noproduct'))->get();
        //dd($products);

        $ids = $products->pluck('user_id');
        $not_join_yeat = Shop::join('users','users.id','=', 'shops.user_id')
        ->whereNotIn('shops.user_id',$ids)
        ->select('shops.name','shops.phone','users.email')
        ->get();
        return view('backend.marketing.flash_deals.flash_deals_reports', compact('products','not_join_yeat'));
    }

    public function view_fd_tails($ids, $fdid){
    
        $flash_deals = FlashDealProduct::join('products','products.id', '=', 'flash_deal_products.product_id')
        ->join('shops','shops.user_id', '=', 'products.user_id')
        ->where('shops.id',$ids)
        ->where('flash_deal_id',$fdid)
        ->select('products.name','products.thumbnail_img','shops.name as shopname')
        ->get();
          return view('backend.marketing.flash_deals.shop_campaign_products', compact('flash_deals'));

    }

    public function update_status(Request $request)
    {
        $flash_deal = FlashDeal::findOrFail($request->id);
        $flash_deal->status = $request->status;
        if($flash_deal->save()){
            flash(translate('Flash deal status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function update_featured(Request $request)
    {
        foreach (FlashDeal::all() as $key => $flash_deal) {
            $flash_deal->featured = 0;
            $flash_deal->save();
        }
        $flash_deal = FlashDeal::findOrFail($request->id);
        $flash_deal->featured = $request->featured;
        if($flash_deal->save()){
            flash(translate('Flash deal status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function update_freeshipping(Request $request)
    {
        $flash_deal = FlashDeal::findOrFail($request->id);
        $flash_deal->freeshipping_check = $request->freeshipping_check;
        if($flash_deal->save()){
            flash(translate('Flash deal status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function product_discount(Request $request){
        $product_ids = $request->product_ids;
        return view('backend.marketing.flash_deals.flash_deal_discount', compact('product_ids'));
    }

    public function product_discount_edit(Request $request){
        $product_ids = $request->product_ids;
        $flash_deal_id = $request->flash_deal_id;
        return view('backend.marketing.flash_deals.flash_deal_discount_edit', compact('product_ids', 'flash_deal_id'));
    }
}
