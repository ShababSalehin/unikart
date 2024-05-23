<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\ProductTag;
use App\ProductTranslation;
use App\ProductStock;
use App\Category;
use App\FlashDealProduct;
use App\ProductTax;
use App\Attribute; //added by alauddin
use App\AttributeValue;
use App\Cart;
use App\Slug;
use Auth;
use Carbon\Carbon;
use Combinations;
use CoreComponentRepository;
use Illuminate\Support\Str;
use Artisan;
use Cache;
use App\Upload;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_products(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $type = 'In House';
        $col_name = null;
        $query = null;
        $sort_search = null;

        $products = Product::where('added_by', 'admin')->where('auction_product', 0);

        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }
        if ($request->search != null) {
            // $products = $products
            //             ->where('name', 'like', '%'.$request->search.'%');

            $products = $products->leftJoin('product_translations', 'product_translations.product_id', '=', 'products.id')->where('products.published', 1)->where('products.name', 'like', '%' . $request->search . '%')->orWhere('product_translations.name', 'like', '%' . $request->search . '%')->groupBy('products.id')
                ->orderByRaw("CASE WHEN products.name LIKE '" . $request->search . "' OR product_translations.name LIKE '" . $request->search . "' THEN 1 WHEN products.name LIKE '" . $request->search . "%' OR product_translations.name LIKE '" . $request->search . "%' THEN 2 WHEN products.name LIKE '%" . $request->search . "' OR product_translations.name LIKE '%" . $request->search . "' THEN 4 ELSE 3 END");

            $sort_search = $request->search;
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'sort_search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function seller_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::where('added_by', 'seller')->where('auction_product', 0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $products = $products
                ->where('name', 'like', '%' . $request->search . '%');
            $sort_search = $request->search;
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);
        $type = 'Seller';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    public function all_products(Request $request)
    {


        //       $products = Product::get();
        //     //dd($products);
        // 		foreach($products as $prod){
        //         	$comission = get_setting('vendor_commission');
        //         	$mrp = $prod->unit_price;
        //         	if($prod->discount_type=='amount'){
        //         		$mrp = $mrp-$prod->discount;
        //     		}else{
        //         		$mrp = $mrp-($mrp*$prod->discount)/100;
        //     		}
        //         	$unikart_earning = $mrp*$comission/100;
        //         	$trade_price = $mrp-$unikart_earning;

        //         	$prod->unikart_earning = $unikart_earning;
        //         	$prod->trade_price = $trade_price;
        //         	$prod->save();
        //         }


        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $lowstock = null;
        $from_date = strtotime(date('Y-m-d'));
        $to_date = strtotime("+1 week");
        $todate30 = strtotime("+1 month");

        $products = Product::orderBy('discount_end_date', 'desc')
            ->where('auction_product', 0);

        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }

        if ($request->search != null) {
            $products = $products->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%')
                ->orWhere('slug', 'like', '%' . $request->search . '%');
            $sort_search = $request->search;
        }

        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = Product::orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        if ($request->discount_in != null) {
            $value = $request->discount_in;
            if ($value == 7) {
                $products = Product::whereBetween('discount_end_date', [$from_date, $to_date]);
            } else {
                $products = Product::whereBetween('discount_end_date', [$to_date, $todate30]);
            }
        }

        if ($request->lowstock != null) {
            $lowstock = $request->lowstock;
            $products = [];
            $stocks = ProductStock::groupBy('product_id')
                ->whereBetween('qty', [0, 5])->orderBy('qty', 'asc')->get();

            foreach ($stocks as $key => $stock) {
                $products[] = Product::where('id', $stock->product_id)->first();
            }
        }

        if (!empty($lowstock)) {
            $products;
        } else {
            $products = $products->paginate(30);
            //dd($products);
        }

        $type = 'All';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search', 'lowstock'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        CoreComponentRepository::initializeCache();

        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        return view('backend.product.products.create', compact('categories'));
    }

    public function add_more_choice_option(Request $request)
    {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->value . '</option>';
        }

        echo json_encode($html);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */



    public function store(Request $request)
    {
        $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();

        $product = new Product;
        $product->name = $request->name;
        $product->beauty_features = $request->beauty_features;

        $product->added_by = $request->added_by;

        if (Auth::user()->user_type == 'seller') {
            $product->user_id = Auth::user()->id;
            if (get_setting('product_approve_by_admin') == 1) {
                $product->approved = 1;
            }
        } else {
            $product->user_id = \App\User::where('user_type', 'admin')->first()->id;
        }
        $product->approved = 1;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->barcode = $request->barcode;

        if ($refund_request_addon != null && $refund_request_addon->activated == 1) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            } else {
                $product->refundable = 0;
            }
        }
        $product->photos = $request->photos;
        $product->thumbnail_img = $request->thumbnail_img;
        $product->unit = $request->unit;
        $product->min_qty = $request->min_qty;
        $product->max_qty = $request->max_qty;

        $product->low_stock_quantity = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags = implode(',', $tags);

        $product->description = $request->description;
        $product->video_provider = $request->video_provider;
        $product->video_link = $request->video_link;
        $product->unit_price = $request->unit_price;
        $product->comission = $request->comission;
        $product->trade_price = $request->trade_price;
        $product->unikart_discount = $request->unikart_discount;
        $product->unikart_earning = $request->unikart_earning;
        $product->app_price = $request->app_price;
        $product->app_discount_type = $request->app_discount_type; //added by alauddin
        $product->discount = $request->discount;
        $product->net_total_discount = $request->discount + $request->unikart_discount; //added by alauddin
        $product->discount_type = $request->discount_type;
        if (!empty($product->net_total_discount)) {
            $discount_percent = ($product->net_total_discount * 100) / $request->unit_price;
        } else {
            $discount_percent = 0;
        }
        $product->discount_percent = $discount_percent;

        if ($request->date_range != null) {
            $date_var               = explode(" TO ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (
            \App\Addon::where('unique_identifier', 'club_point')->first() != null &&
            \App\Addon::where('unique_identifier', 'club_point')->first()->activated
        ) {
            if ($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if ($request->shipping_type == 'free') {
                $product->shipping_cost = 0;
            } elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            } elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }
        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }

        $product->meta_title = $request->meta_title;
        $product->meta_description = $request->meta_description;

        if ($request->has('meta_img')) {
            $product->meta_img = $request->meta_img;
        } else {
            $product->meta_img = $product->thumbnail_img;
        }

        if ($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if ($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if ($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        if ($request->hasFile('pdf')) {
            $product->pdf = $request->pdf->store('uploads/products/pdf');
        }

        $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name));

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = json_encode($request->colors);
        } else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                //$attr = Attribute::where('id',$no)->get(); //added by alauddin               
                //if($attr[0]['attr_include_status']==1){ //added by alauddin
                $str = 'choice_options_' . $no;

                $item['attribute_id'] = $no;

                $data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                foreach ($request[$str] as $key => $eachValue) {
                    // array_push($data, $eachValue->value);
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
                // }
            }
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        } else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        $product->published = 0;
        if ($request->button == 'unpublish' || $request->button == 'draft') {
            $product->published = 0;
        }

        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }
        if ($request->has('featured')) {
            $product->featured = 1;
        }
        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }
        $product->cash_on_delivery = 0;
        if ($request->cash_on_delivery) {
            $product->cash_on_delivery = 1;
        }
        //$variations = array();

        $product->save();

        if (!empty($product->id)) {
            $product_tag = new ProductTag();
            $product_tag->product_id = $product->id;
            $product_tag->tag = $product->name;
            $product_tag->save();
        }

        //VAT & Tax
        if ($request->tax_id) {
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax;
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }
        if (count($tags) > 0) {
            foreach ($tags as $key => $val) {
                $product_tag = ProductTag::where('tag', $val)->first();
                if ($product_tag == null) {
                    $product_tag = new ProductTag;
                    $product_tag->product_id = $product->id;
                    $product_tag->tag = $val;
                    $product_tag->save();
                }
            }
        }
        //Flash Deal
        if ($request->flash_deal_id) {
            $flash_deal_product = new FlashDealProduct;
            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->save();
        }

        //combinations start
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                $attr = Attribute::where('id', $no)->get(); //added by alauddin               
                if ($attr[0]['attr_include_status'] == 1) { //added by alauddin
                    $name = 'choice_options_' . $no;
                    $data = array();
                    foreach ($request[$name] as $key => $eachValue) {
                        array_push($data, $eachValue);
                    }
                    array_push($options, $data);
                }
            }
        }

        //Generates the combinations of customer choice options
        $combinations = Combinations::makeCombinations($options);
        if (count($combinations[0]) > 0) {
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $key => $item) {
                    if ($key > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = \App\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if ($product_stock == null) {
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }

                $product_stock->variant = $str;
                $product_stock->price = !empty($request['price_' . str_replace('.', '_', $str)]) ? $request['price_' . str_replace('.', '_', $str)] : $request->unit_price;
                $product_stock->sku = !empty($request['sku_' . str_replace('.', '_', $str)]) ? $request['sku_' . str_replace('.', '_', $str)] : $request->sku;
                $product_stock->qty = !empty($request['qty_' . str_replace('.', '_', $str)]) ? $request['qty_' . str_replace('.', '_', $str)] : $request->current_stock;
                $product_stock->image = !empty($request['img_' . str_replace('.', '_', $str)]) ? $request['img_' . str_replace('.', '_', $str)] : '';
                $product_stock->save();
            }
        } else {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product->id;
            $product_stock->variant     = '';
            $product_stock->price       = $request->unit_price;
            $product_stock->sku         = $request->sku;
            $product_stock->qty         = $request->current_stock;
            $product_stock->save();
        }
        //combinations end

        $product->save();

        // Product Translations
        $product_translation = ProductTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'product_id' => $product->id]);
        $product_translation->name = $request->name;
        $product_translation->unit = $request->unit;
        $product_translation->description = $request->description;
        $product_translation->save();

        flash(translate('Product has been inserted successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return redirect()->route('products.admin');
        } else {
            if (addon_is_activated('seller_subscription')) {
                $seller = Auth::user()->seller;
                $seller->remaining_uploads -= 1;
                $seller->save();
            }
            return redirect()->route('seller.products');
        }
    }


    public function all_slugs()
    {

        if (request('search')) {

            $slug = Slug::where('new_slug', 'like', '%' . request('search') . '%')
                ->orWhere('old_slug', 'like', '%' . request('search') . '%')->get();
        } else {
            $slug = Slug::orderBy('id', 'desc')->get();
        }

        return view('backend.slugs.index', compact('slug'));
    }

    public function add_slug(Request $request)
    {
        $request->validate([
            'old_slug' => 'required',
            'new_slug' => 'required',
        ]);

        $product = Product::where('slug', $request->old_slug)->first();

        if ($product) {
            $slug = new Slug;
            $slug->old_slug = $request['old_slug'];
            $slug->new_slug = $request['new_slug'];
            $slug->redirection_code = $request['redirection_code'];
            $slug->product_id = $product->id;
            if ($slug->save()) {
                $product->slug_old = $request['old_slug'];
                $product->slug = $request['new_slug'];
                $product->save();
            }
            flash(translate('Slug Changed successfully'))->success();
            return back();
        } else {
            flash(translate('Product does not found'))->warning();
            return back();
        }
    }

    public function edit($id)
    {
        $slug = Slug::find($id);
        return view('backend.slugs.edit_slugs', compact('slug'));
    }

    public function update_slug($id, Request $request)
    {

        $request->validate([
            'old_slug' => 'required',
            'new_slug' => 'required',
        ]);
        $product = Product::where('slug_old', $request->old_slug)->first();
        if ($product) {
            $slug = Slug::where('product_id', $product->id)->first();
            $slug->old_slug = $request['old_slug'];
            $slug->new_slug = $request['new_slug'];
            $slug->redirection_code = $request['redirection_code'];
            if ($slug->save()) {
                $product->slug_old = $request['old_slug'];
                $product->slug = $request['new_slug'];
                $product->save();
            }
            flash(translate('Slug Changed successfully'))->success();
            return redirect()->route('slugs.all_slugs');
        } else {
            flash(translate('Product does not found'))->warning();
            return back();
        }
    }


    public function store_backup_01_06_2022(Request $request)
    {
        $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();

        $product = new Product;
        $product->name = $request->name;
        $product->beauty_features = $request->beauty_features;

        $product->added_by = $request->added_by;

        if (Auth::user()->user_type == 'seller') {
            $product->user_id = Auth::user()->id;
            if (get_setting('product_approve_by_admin') == 1) {
                $product->approved = 1;
            }
        } else {
            $product->user_id = \App\User::where('user_type', 'admin')->first()->id;
        }
        $product->approved = 1;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->barcode = $request->barcode;

        if ($refund_request_addon != null && $refund_request_addon->activated == 1) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            } else {
                $product->refundable = 0;
            }
        }
        $product->photos = $request->photos;
        $product->thumbnail_img = $request->thumbnail_img;
        $product->unit = $request->unit;
        $product->min_qty = $request->min_qty;
        $product->low_stock_quantity = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags = implode(',', $tags);

        $product->description = $request->description;
        $product->video_provider = $request->video_provider;
        $product->video_link = $request->video_link;
        $product->unit_price = $request->unit_price;
        $product->comission = $request->comission;
        $product->trade_price = $request->trade_price;
        $product->unikart_earning = $request->unikart_earning;
        $product->app_price = $request->app_price;
        $product->discount = $request->discount;
        $product->discount_type = $request->discount_type;

        if ($request->date_range != null) {
            $date_var               = explode(" TO ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (
            \App\Addon::where('unique_identifier', 'club_point')->first() != null &&
            \App\Addon::where('unique_identifier', 'club_point')->first()->activated
        ) {
            if ($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if ($request->shipping_type == 'free') {
                $product->shipping_cost = 0;
            } elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            } elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }
        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }

        $product->meta_title = $request->meta_title;
        $product->meta_description = $request->meta_description;

        if ($request->has('meta_img')) {
            $product->meta_img = $request->meta_img;
        } else {
            $product->meta_img = $product->thumbnail_img;
        }

        if ($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if ($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if ($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        if ($request->hasFile('pdf')) {
            $product->pdf = $request->pdf->store('uploads/products/pdf');
        }

        $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name));

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = json_encode($request->colors);
        } else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;

                $item['attribute_id'] = $no;

                $data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                foreach ($request[$str] as $key => $eachValue) {
                    // array_push($data, $eachValue->value);
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        } else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        $product->published = 0;
        if ($request->button == 'unpublish' || $request->button == 'draft') {
            $product->published = 0;
        }

        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }
        if ($request->has('featured')) {
            $product->featured = 1;
        }
        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }
        $product->cash_on_delivery = 0;
        if ($request->cash_on_delivery) {
            $product->cash_on_delivery = 1;
        }
        //$variations = array();

        $product->save();

        //VAT & Tax
        if ($request->tax_id) {
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax;
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }
        if (count($tags) > 0) {
            foreach ($tags as $key => $val) {
                $product_tag = ProductTag::where('tag', $val)->first();
                if ($product_tag == null) {
                    $product_tag = new ProductTag;
                    $product_tag->product_id = $product->id;
                    $product_tag->tag = $val;
                    $product_tag->save();
                }
            }
        }
        //Flash Deal
        if ($request->flash_deal_id) {
            $flash_deal_product = new FlashDealProduct;
            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->save();
        }

        //combinations start
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                foreach ($request[$name] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }
                array_push($options, $data);
            }
        }

        //Generates the combinations of customer choice options
        $combinations = Combinations::makeCombinations($options);
        if (count($combinations[0]) > 10000) {
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $key => $item) {
                    if ($key > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = \App\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if ($product_stock == null) {
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }

                $product_stock->variant = $str;
                $product_stock->price = !empty($request['price_' . str_replace('.', '_', $str)]) ? $request['price_' . str_replace('.', '_', $str)] : $request->unit_price;
                $product_stock->sku = !empty($request['sku_' . str_replace('.', '_', $str)]) ? $request['sku_' . str_replace('.', '_', $str)] : $request->sku;
                $product_stock->qty = !empty($request['qty_' . str_replace('.', '_', $str)]) ? $request['qty_' . str_replace('.', '_', $str)] : $request->current_stock;
                $product_stock->image = !empty($request['img_' . str_replace('.', '_', $str)]) ? $request['img_' . str_replace('.', '_', $str)] : '';
                $product_stock->save();
            }
        } else {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product->id;
            $product_stock->variant     = '';
            $product_stock->price       = $request->unit_price;
            $product_stock->sku         = $request->sku;
            $product_stock->qty         = $request->current_stock;
            $product_stock->save();
        }
        //combinations end

        $product->save();

        // Product Translations
        $product_translation = ProductTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'product_id' => $product->id]);
        $product_translation->name = $request->name;
        $product_translation->unit = $request->unit;
        $product_translation->description = $request->description;
        $product_translation->save();

        flash(translate('Product has been inserted successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return redirect()->route('products.admin');
        } else {
            if (addon_is_activated('seller_subscription')) {
                $seller = Auth::user()->seller;
                $seller->remaining_uploads -= 1;
                $seller->save();
            }
            return redirect()->route('seller.products');
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
    public function admin_product_edit(Request $request, $id)
    {
        CoreComponentRepository::initializeCache();

        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }

        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function seller_product_edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::all();
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
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
        $refund_request_addon       = \App\Addon::where('unique_identifier', 'refund_request')->first();
        $product                    = Product::findOrFail($id);
        $product->category_id       = $request->category_id;
        $product->brand_id          = $request->brand_id;
        $product->barcode           = $request->barcode;
        $product->cash_on_delivery = 0;
        $product->featured = 0;
        $product->todays_deal = 0;
        $product->is_quantity_multiplied = 0;

        if ($refund_request_addon != null && $refund_request_addon->activated == 1) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            } else {
                $product->refundable = 0;
            }
        }

        if ($request->lang == env("DEFAULT_LANGUAGE")) {
            $product->name          = $request->name;
            $product->beauty_features = $request->beauty_features;
            $product->unit          = $request->unit;
            $product->description   = $request->description;
            $product->slug          = strtolower($request->slug);
        }

        $product->photos                 = $request->photos;
        $product->thumbnail_img          = $request->thumbnail_img;
        $product->min_qty                = $request->min_qty;
        $product->max_qty                = $request->max_qty;
        $product->low_stock_quantity     = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags           = implode(',', $tags);

        $product->video_provider = $request->video_provider;
        $product->video_link     = $request->video_link;
        $product->unit_price     = $request->unit_price;
        $product->comission = $request->comission;
        $product->trade_price = $request->trade_price;
        $product->unikart_discount = $request->unikart_discount;
        $product->unikart_earning = $request->unikart_earning;
        $product->app_price     = $request->app_price;
        $product->app_discount_type = $request->app_discount_type; //added by alauddin
        $product->discount       = $request->discount;
        $product->net_total_discount = $request->discount + $request->unikart_discount; //added by alauddin
        $product->discount_type     = $request->discount_type;
        if (!empty($product->net_total_discount)) {
            $discount_percent = ($product->net_total_discount * 100) / $request->unit_price;
        } else {
            $discount_percent = 0;
        }
        $product->discount_percent = $discount_percent;

        if ($request->date_range != null) {
            $date_var               = explode(" TO ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type  = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (
            \App\Addon::where('unique_identifier', 'club_point')->first() != null &&
            \App\Addon::where('unique_identifier', 'club_point')->first()->activated
        ) {
            if ($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if ($request->shipping_type == 'free') {
                $product->shipping_cost = 0;
            } elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            } elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }

        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }
        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }

        if ($request->has('featured')) {
            $product->featured = 1;
        }

        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }

        $product->meta_title        = $request->meta_title;
        $product->meta_description  = $request->meta_description;
        $product->meta_img          = $request->meta_img;

        if ($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if ($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if ($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        $product->pdf = $request->pdf;

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = json_encode($request->colors);
        } else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                //$attr = Attribute::where('id',$no)->get(); //added by alauddin               
                //if($attr[0]['attr_include_status']==1){ //added by alauddin
                $str = 'choice_options_' . $no;

                $item['attribute_id'] = $no;

                $data = array();
                foreach ($request[$str] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
                //}
            }
        }

        foreach ($product->stocks as $key => $stock) {
            // $stock->delete();
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        } else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);


        //combinations start
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                $attr = Attribute::where('id', $no)->get(); //added by alauddin               
                if ($attr[0]['attr_include_status'] == 1) { //added by alauddin
                    $name = 'choice_options_' . $no;
                    $data = array();
                    foreach ($request[$name] as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
            }
        }

        $combinations = Combinations::makeCombinations($options);
        if (count($combinations[0]) > 0) {
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $key => $item) {
                    if ($key > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = \App\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }

                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if ($product_stock == null) {
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }
                if (isset($request['price_' . str_replace('.', '_', $str)])) {
                    $product_stock->variant = $str;
                    $product_stock->price = $request['price_' . str_replace('.', '_', $str)];
                    $product_stock->sku = $request['sku_' . str_replace('.', '_', $str)];
                    $product_stock->qty = $request['qty_' . str_replace('.', '_', $str)];
                    $product_stock->image = $request['img_' . str_replace('.', '_', $str)];

                    $product_stock->save();
                } else {
                    $product_stock->variant     = '';
                    $product_stock->price       = $request->unit_price;
                    $product_stock->sku         = $request->sku;
                    $product_stock->qty         = $request->current_stock;
                    $product_stock->save();
                }
            }
        }
        if (!empty($product->stocks)) {
            // Do not delete existing stocks, and do not update quantity
        } else {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product->id;
            $product_stock->variant     = '';
            $product_stock->price       = $request->unit_price;
            $product_stock->sku         = $request->sku;
            $product_stock->qty         = $request->current_stock;
            $product_stock->save();
        }

        $product->save();

        // Tag start
        $oldtags = ProductTag::where('product_id', $product->id)->get();
        if ($oldtags) {
            foreach ($oldtags as $key => $tag) {
                $tag->delete();
            }
        }

        if (count($tags) > 0) {
            foreach ($tags as $key => $val) {
                $product_tag = new ProductTag();
                $product_tag->product_id = $product->id;
                $product_tag->tag = $val;
                $product_tag->save();
            }
        }

        $product_tag = new ProductTag();
        $product_tag->product_id = $product->id;
        $product_tag->tag = $product->name;
        $product_tag->save();
        // Tag Close

        //Flash Deal
        if ($request->flash_deal_id) {
            if ($product->flash_deal_product) {
                $flash_deal_product = FlashDealProduct::findOrFail($product->flash_deal_product->id);
                if (!$flash_deal_product) {
                    $flash_deal_product = new FlashDealProduct;
                }
            } else {
                $flash_deal_product = new FlashDealProduct;
            }

            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->discount = $request->flash_discount;
            $flash_deal_product->discount_type = $request->flash_discount_type;
            $flash_deal_product->save();
            //            dd($flash_deal_product);
        }

        //VAT & Tax
        if ($request->tax_id) {
            ProductTax::where('product_id', $product->id)->delete();
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax;
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }

        // Product Translations
        $product_translation                = ProductTranslation::firstOrNew(['lang' => $request->lang, 'product_id' => $product->id]);
        $product_translation->name          = $request->name;
        $product_translation->unit          = $request->unit;
        $product_translation->description   = $request->description;
        $product_translation->save();

        flash(translate('Product has been updated successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back();
    }

    public function update_old(Request $request, $id)
    {
        $refund_request_addon       = \App\Addon::where('unique_identifier', 'refund_request')->first();
        $product                    = Product::findOrFail($id);
        $product->category_id       = $request->category_id;
        $product->brand_id          = $request->brand_id;
        $product->barcode           = $request->barcode;
        $product->cash_on_delivery = 0;
        $product->featured = 0;
        $product->todays_deal = 0;
        $product->is_quantity_multiplied = 0;

        if ($refund_request_addon != null && $refund_request_addon->activated == 1) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            } else {
                $product->refundable = 0;
            }
        }

        if ($request->lang == env("DEFAULT_LANGUAGE")) {
            $product->name          = $request->name;
            $product->beauty_features = $request->beauty_features;
            $product->unit          = $request->unit;
            $product->description   = $request->description;
            $product->slug          = strtolower($request->slug);
        }

        $product->photos                 = $request->photos;
        $product->thumbnail_img          = $request->thumbnail_img;
        $product->min_qty                = $request->min_qty;
        $product->max_qty                = $request->max_qty;
        $product->low_stock_quantity     = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags           = implode(',', $tags);

        $product->video_provider = $request->video_provider;
        $product->video_link     = $request->video_link;
        $product->unit_price     = $request->unit_price;
        $product->comission = $request->comission;
        $product->trade_price = $request->trade_price;
        $product->unikart_discount = $request->unikart_discount;
        $product->unikart_earning = $request->unikart_earning;
        $product->app_price     = $request->app_price;
        $product->app_discount_type = $request->app_discount_type; //added by alauddin
        $product->discount       = $request->discount;
        $product->net_total_discount = $request->discount + $request->unikart_discount; //added by alauddin
        $product->discount_type     = $request->discount_type;
        if (!empty($product->net_total_discount)) {
            $discount_percent = ($product->net_total_discount * 100) / $request->unit_price;
        } else {
            $discount_percent = 0;
        }
        $product->discount_percent = $discount_percent;

        if ($request->date_range != null) {
            $date_var               = explode(" TO ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type  = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (
            \App\Addon::where('unique_identifier', 'club_point')->first() != null &&
            \App\Addon::where('unique_identifier', 'club_point')->first()->activated
        ) {
            if ($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if ($request->shipping_type == 'free') {
                $product->shipping_cost = 0;
            } elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            } elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }

        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }
        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }

        if ($request->has('featured')) {
            $product->featured = 1;
        }

        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }

        $product->meta_title        = $request->meta_title;
        $product->meta_description  = $request->meta_description;
        $product->meta_img          = $request->meta_img;

        if ($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if ($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if ($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        $product->pdf = $request->pdf;

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = json_encode($request->colors);
        } else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                //$attr = Attribute::where('id',$no)->get(); //added by alauddin               
                //if($attr[0]['attr_include_status']==1){ //added by alauddin
                $str = 'choice_options_' . $no;

                $item['attribute_id'] = $no;

                $data = array();
                foreach ($request[$str] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
                //}
            }
        }

        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        } else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);


        //combinations start
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                $attr = Attribute::where('id', $no)->get(); //added by alauddin               
                if ($attr[0]['attr_include_status'] == 1) { //added by alauddin
                    $name = 'choice_options_' . $no;
                    $data = array();
                    foreach ($request[$name] as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
            }
        }

        $combinations = Combinations::makeCombinations($options);
        if (count($combinations[0]) > 0) {
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $key => $item) {
                    if ($key > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = \App\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }

                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if ($product_stock == null) {
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }
                if (isset($request['price_' . str_replace('.', '_', $str)])) {
                    $product_stock->variant = $str;
                    $product_stock->price = $request['price_' . str_replace('.', '_', $str)];
                    $product_stock->sku = $request['sku_' . str_replace('.', '_', $str)];
                    $product_stock->qty = $request['qty_' . str_replace('.', '_', $str)];
                    $product_stock->image = $request['img_' . str_replace('.', '_', $str)];

                    $product_stock->save();
                } else {
                    $product_stock->variant     = '';
                    $product_stock->price       = $request->unit_price;
                    $product_stock->sku         = $request->sku;
                    $product_stock->qty         = $request->current_stock;
                    $product_stock->save();
                }
            }
        } else {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product->id;
            $product_stock->variant     = '';
            $product_stock->price       = $request->unit_price;
            $product_stock->sku         = $request->sku;
            $product_stock->qty         = $request->current_stock;
            $product_stock->save();
        }
        $product->save();

        // Tag start
        $oldtags = ProductTag::where('product_id', $product->id)->get();
        if ($oldtags) {
            foreach ($oldtags as $key => $tag) {
                $tag->delete();
            }
        }

        if (count($tags) > 0) {
            foreach ($tags as $key => $val) {
                $product_tag = new ProductTag();
                $product_tag->product_id = $product->id;
                $product_tag->tag = $val;
                $product_tag->save();
            }
        }

        $product_tag = new ProductTag();
        $product_tag->product_id = $product->id;
        $product_tag->tag = $product->name;
        $product_tag->save();
        // Tag Close

        //Flash Deal
        if ($request->flash_deal_id) {
            if ($product->flash_deal_product) {
                $flash_deal_product = FlashDealProduct::findOrFail($product->flash_deal_product->id);
                if (!$flash_deal_product) {
                    $flash_deal_product = new FlashDealProduct;
                }
            } else {
                $flash_deal_product = new FlashDealProduct;
            }

            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->discount = $request->flash_discount;
            $flash_deal_product->discount_type = $request->flash_discount_type;
            $flash_deal_product->save();
            //            dd($flash_deal_product);
        }

        //VAT & Tax
        if ($request->tax_id) {
            ProductTax::where('product_id', $product->id)->delete();
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax;
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }

        // Product Translations
        $product_translation                = ProductTranslation::firstOrNew(['lang' => $request->lang, 'product_id' => $product->id]);
        $product_translation->name          = $request->name;
        $product_translation->unit          = $request->unit;
        $product_translation->description   = $request->description;
        $product_translation->save();

        flash(translate('Product has been updated successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back();
    }

    public function update_01_06_2022(Request $request, $id)
    {
        $refund_request_addon       = \App\Addon::where('unique_identifier', 'refund_request')->first();
        $product                    = Product::findOrFail($id);
        $product->category_id       = $request->category_id;
        $product->brand_id          = $request->brand_id;
        $product->barcode           = $request->barcode;
        $product->cash_on_delivery = 0;
        $product->featured = 0;
        $product->todays_deal = 0;
        $product->is_quantity_multiplied = 0;

        if ($refund_request_addon != null && $refund_request_addon->activated == 1) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            } else {
                $product->refundable = 0;
            }
        }

        if ($request->lang == env("DEFAULT_LANGUAGE")) {
            $product->name          = $request->name;
            $product->beauty_features = $request->beauty_features;
            $product->unit          = $request->unit;
            $product->description   = $request->description;
            $product->slug          = strtolower($request->slug);
        }

        $product->photos                 = $request->photos;
        $product->thumbnail_img          = $request->thumbnail_img;
        $product->min_qty                = $request->min_qty;
        $product->low_stock_quantity     = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags           = implode(',', $tags);

        $product->video_provider = $request->video_provider;
        $product->video_link     = $request->video_link;
        $product->unit_price     = $request->unit_price;
        $product->comission = $request->comission;
        $product->trade_price = $request->trade_price;
        $product->unikart_earning = $request->unikart_earning;
        $product->app_price     = $request->app_price;
        $product->discount       = $request->discount;
        $product->discount_type     = $request->discount_type;

        if ($request->date_range != null) {
            $date_var               = explode(" TO ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type  = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (
            \App\Addon::where('unique_identifier', 'club_point')->first() != null &&
            \App\Addon::where('unique_identifier', 'club_point')->first()->activated
        ) {
            if ($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if ($request->shipping_type == 'free') {
                $product->shipping_cost = 0;
            } elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            } elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }

        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }
        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }

        if ($request->has('featured')) {
            $product->featured = 1;
        }

        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }

        $product->meta_title        = $request->meta_title;
        $product->meta_description  = $request->meta_description;
        $product->meta_img          = $request->meta_img;

        if ($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if ($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if ($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        $product->pdf = $request->pdf;

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = json_encode($request->colors);
        } else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;

                $item['attribute_id'] = $no;

                $data = array();
                foreach ($request[$str] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
        }

        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        } else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);


        //combinations start
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = Combinations::makeCombinations($options);
        if (count($combinations[0]) > 1000) {
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $key => $item) {
                    if ($key > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = \App\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }

                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if ($product_stock == null) {
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }
                if (isset($request['price_' . str_replace('.', '_', $str)])) {
                    $product_stock->variant = $str;
                    $product_stock->price = $request['price_' . str_replace('.', '_', $str)];
                    $product_stock->sku = $request['sku_' . str_replace('.', '_', $str)];
                    $product_stock->qty = $request['qty_' . str_replace('.', '_', $str)];
                    $product_stock->image = $request['img_' . str_replace('.', '_', $str)];

                    $product_stock->save();
                } else {
                    $product_stock->variant     = '';
                    $product_stock->price       = $request->unit_price;
                    $product_stock->sku         = $request->sku;
                    $product_stock->qty         = $request->current_stock;
                    $product_stock->save();
                }
            }
        } else {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product->id;
            $product_stock->variant     = '';
            $product_stock->price       = $request->unit_price;
            $product_stock->sku         = $request->sku;
            $product_stock->qty         = $request->current_stock;
            $product_stock->save();
        }
        $product->save();
        if (count($tags) > 0) {
            foreach ($tags as $key => $val) {
                $product_tag = ProductTag::where('tag', $val)->first();
                if ($product_tag == null) {
                    $product_tag = new ProductTag;
                    $product_tag->product_id = $product->id;
                    $product_tag->tag = $val;
                    $product_tag->save();
                }
            }
        }
        //Flash Deal
        if ($request->flash_deal_id) {
            if ($product->flash_deal_product) {
                $flash_deal_product = FlashDealProduct::findOrFail($product->flash_deal_product->id);
                if (!$flash_deal_product) {
                    $flash_deal_product = new FlashDealProduct;
                }
            } else {
                $flash_deal_product = new FlashDealProduct;
            }

            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->discount = $request->flash_discount;
            $flash_deal_product->discount_type = $request->flash_discount_type;
            $flash_deal_product->save();
            //            dd($flash_deal_product);
        }

        //VAT & Tax
        if ($request->tax_id) {
            ProductTax::where('product_id', $product->id)->delete();
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax;
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }

        // Product Translations
        $product_translation                = ProductTranslation::firstOrNew(['lang' => $request->lang, 'product_id' => $product->id]);
        $product_translation->name          = $request->name;
        $product_translation->unit          = $request->unit;
        $product_translation->description   = $request->description;
        $product_translation->save();

        flash(translate('Product has been updated successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy_multyple($id)
    {

        $products = Product::where('is_delete', '=', 'yes')->get();
        //dd($products);

        foreach ($products as $product) {

            $product = Product::findOrFail($product->id);
            // dd($product);
            Cart::where('product_id', $product->id)->delete();
            // dd($product);

            foreach ($product->product_translations as $key => $product_translations) {
                $product_translations->delete();
            }

            foreach ($product->stocks as $key => $stock) {
                $stock->delete();
            }

            $oldtags = ProductTag::where('product_id', $product->id)->get();
            if ($oldtags) {
                foreach ($oldtags as $key => $tag) {
                    $tag->delete();
                }
            }


            if (!empty($product->photos)) {
                if (strpos($product->photos, ',')) {

                    $uploadIDs = explode(',', $product->photos);
                    foreach ($uploadIDs as $uploadID) {

                        $upload = Upload::find($uploadID);

                        if ($upload->file_name) {
                            File::delete(public_path($upload->file_name));
                        }

                        if ($upload->forceDelete()) {
                        }
                    }
                } else {

                    $upload = Upload::find($product->photos);
                    if (!empty($upload)) {
                        if (!empty($upload->file_name)) {
                            File::delete(public_path($upload->file_name));
                            $upload->forceDelete();
                        } else {
                        }
                    } else {
                        // dd('Error');
                    }
                }
            }

            if (Product::destroy($product->id)) {

                flash(translate('Product has been deleted successfully'))->success();

                Artisan::call('view:clear');
                Artisan::call('cache:clear');
            } else {
                flash(translate('Something went wrong'))->error();
            }
        }



        flash(translate('Product has been deleted successfully'))->success();
    }















    public function destroy($id)
    {

        $product = Product::findOrFail($id);
        Cart::where('product_id', $id)->delete();

        if ($product->num_of_sale != 0) {
            $product->published = 0;
            $product->save();

            $oldtags = ProductTag::where('product_id', $product->id)->get();
            if ($oldtags) {
                foreach ($oldtags as $tag) {
                    $tag->status = 0;
                    $tag->save();
                }
            }
            flash(translate('Product has been unpublish successfully'))->warning();
            return back();
        } else {
            foreach ($product->product_translations as $key => $product_translations) {
                $product_translations->delete();
            }

            foreach ($product->stocks as $key => $stock) {
                $stock->delete();
            }

            $oldtags = ProductTag::where('product_id', $product->id)->get();
            if ($oldtags) {
                foreach ($oldtags as $key => $tag) {
                    $tag->delete();
                }
            }
            if (!empty($product->photos)) {
                if (strpos($product->photos, ',')) {

                    $uploadIDs = explode(',', $product->photos);
                    foreach ($uploadIDs as $uploadID) {

                        $upload = Upload::where('id', $uploadID)->first();

                        if (File::exists(public_path($upload->file_name))) {
                            File::delete(public_path($upload->file_name));
                        }
                        $upload->forceDelete();
                    }
                    flash(translate('Photos deleted successfully'))->success();
                } else {

                    $upload = Upload::where('id', $product->photos)->first();
                    if (File::exists(public_path($upload->file_name))) {
                        File::delete(public_path($upload->file_name));
                    }
                    $upload->forceDelete();
                    flash(translate('Photo deleted successfully'))->success();
                }
            } else {
                flash(translate('No photos found'))->error();
            }

            if (Product::destroy($id)) {
                Cart::where('product_id', $id)->delete();

                flash(translate('Product has been deleted successfully'))->success();

                Artisan::call('view:clear');
                Artisan::call('cache:clear');

                return back();
            } else {
                flash(translate('Something went wrong'))->error();
                return back();
            }
        }
    }

    public function bulk_product_delete(Request $request)
    {

        // if($request->id) {
        //     foreach ($request->id as $product_id) {
        //         $this->destroy($product_id);
        //     }
        // }

        // return 1;

        flash(translate('Something went wrong please contact with support team'))->error();
        return back();
    }

    /**
     * Duplicates the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request, $id)
    {
        $product = Product::find($id);
        $product_new = $product->replicate();
        $product_new->slug = $product_new->slug . '-' . Str::random(5);

        if ($product_new->save()) {
            foreach ($product->stocks as $key => $stock) {
                $product_stock              = new ProductStock;
                $product_stock->product_id  = $product_new->id;
                $product_stock->variant     = $stock->variant;
                $product_stock->price       = $stock->price;
                $product_stock->sku         = $stock->sku;
                $product_stock->qty         = $stock->qty;
                $product_stock->save();
            }

            flash(translate('Product has been duplicated successfully'))->success();
            if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
                if ($request->type == 'In House')
                    return redirect()->route('products.admin');
                elseif ($request->type == 'Seller')
                    return redirect()->route('products.seller');
                elseif ($request->type == 'All')
                    return redirect()->route('products.all');
            } else {
                if (
                    \App\Addon::where('unique_identifier', 'seller_subscription')->first() != null &&
                    \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated
                ) {
                    $seller = Auth::user()->seller;
                    $seller->remaining_uploads -= 1;
                    $seller->save();
                }
                return redirect()->route('seller.products');
            }
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function get_products_by_brand(Request $request)
    {
        $products = Product::where('brand_id', $request->brand_id)->get();
        return view('partials.product_select', compact('products'));
    }

    public function updateTodaysDeal(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->todays_deal = $request->status;
        $product->save();
        Cache::forget('todays_deal_products');
        return 1;
    }

    public function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        Cart::where('product_id', $request->id)->delete();
        $product->published = $request->status;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription')) {
            $seller = $product->user->seller;
            if ($seller->invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0) {
                return 0;
            }
        }

        $product->save();

        $tags = ProductTag::where('product_id', $request->id)->get();
        if ($tags) {
            foreach ($tags as $tag) {
                $tag->status = $request->status;
                $tag->save();
            }
        }

        return 1;
    }

    public function updateProductApproval(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->approved = $request->approved;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription')) {
            $seller = $product->user->seller;
            if ($seller->invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0) {
                return 0;
            }
        }

        $product->save();
        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public function updateProduct_for_you(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->product_for_you = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public function updateSellerFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->seller_featured = $request->status;
        if ($product->save()) {
            return 1;
        }
        return 0;
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                $attr = Attribute::where('id', $no)->get(); //added by alauddin               
                if ($attr[0]['attr_include_status'] == 1) { //added by alauddin
                    $name = 'choice_options_' . $no;
                    $data = array();
                    // foreach (json_decode($request[$name][0]) as $key => $item) {
                    foreach ($request[$name] as $key => $item) {
                        // array_push($data, $item->value);
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
            }
        }

        $combinations = Combinations::makeCombinations($options);
        return view('backend.product.products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'));
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $attr = array();
                $attr = Attribute::where('id', $no)->get(); //added by alauddin               
                if ($attr[0]['attr_include_status'] == 1) { //added by alauddin
                    $name = 'choice_options_' . $no;
                    $data = array();
                    // foreach (json_decode($request[$name][0]) as $key => $item) {
                    foreach ($request[$name] as $key => $item) {
                        // array_push($data, $item->value);
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
            }
        }

        $combinations = Combinations::makeCombinations($options);
        return view('backend.product.products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product'));
    }

    public function discount_percentage()
    {
        $products = Product::get();
        foreach ($products as $product) {
            if (home_base_price($product) != home_discounted_base_price($product)) {
                $baseprices = (home_base_price($product));
                $basepri = preg_replace('/[^A-Za-z0-9\-]/', '', $baseprices);
                $discountprices = (home_discounted_base_price($product));
                $discountpri = preg_replace('/[^A-Za-z0-9\-]/', '', $discountprices);
                $discountadount = ($basepri - $discountpri);
                $discountpercentage = round($discountadount * 100 / $basepri);
                $product->discount_percent = $discountpercentage;
                $product->save();
            }
        }
        flash(translate('Discount Percentage Added Successfully'))->success();
        return back();
    }
}
