<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Search;
use App\Product;
use App\ProductTag;
use App\Category;
use App\FlashDeal;
use App\Brand;
use App\Color;
use App\Shop;
use App\Attribute;
use App\AttributeCategory;
use App\Utility\CategoryUtility;

class SearchController extends Controller
{
   
    public function index(Request $request, $category_id = null, $brand_id = null)
    {

        //dd(preg_replace('~(\?|&)=[^&]*~', '$1', $request->keyword));
        $discount = $request->discount;
        $discounts = !empty($discount) ? $discount : array();
        $query = $request->keyword;
        $sort_by = $request->sort_by;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $seller_id = $request->seller_id;
        $product_review = $request->product_review;
        $attributes = Attribute::where('name', '!=', 'color')->get();
        $selected_attribute_values = array();
        $categorydata = array();
        $colors = Color::all();
        $selected_color = null;
        $conditions = ['published' => 1];

        if ($seller_id != null) {
            $conditions = array_merge($conditions, ['user_id' => Seller::findOrFail($seller_id)->user->id]);
        }

        $products = Product::where($conditions);

        if ($category_id != null) {
            $categorydata = Category::where('id',$category_id)->first();
            $category_ids = CategoryUtility::children_ids($category_id);
            $category_ids[] = $category_id;
            $products->whereIn('category_id', $category_ids);
        } 

        if ($min_price != null && $max_price != null) {
            $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
        }

        if (!empty($discounts)) {
            $disWhere = " (";
            foreach ($discounts as $discount) {
                if ($discount != null && $discount != null) {
                    if ($discount == 2) {
                        $disWhere .= " (discount>=2 and discount <=5) or ";
                    } elseif ($discount == 6) {
                        $disWhere .= " (discount>=6 and discount <=10) or ";
                    } elseif ($discount == 11) {
                        $disWhere .= " (discount>=11 and discount <=20) or ";
                    } elseif ($discount == 21) {
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
        
    	switch ($sort_by) {
            case 'newest':
                $products->orderBy('products.created_at', 'desc');
                break;
            case 'oldest':
                $products->orderBy('products.created_at', 'asc');
                break;
            case 'price-asc':
                $products->orderBy('unit_price', 'asc');
                break;
            case 'price-desc':
                $products->orderBy('unit_price', 'desc');
                break;
        }

        if ($query != null) {
            $searchController = new SearchController;
            $searchController->store($request);
         $query1 = str_replace( array( '\''), '', $query);
         $query = implode('%',str_split(str_replace(" ","",$query)));
        $q = explode(' ',$query1);	
          $products->leftJoin('product_translations','product_translations.product_id','=','products.id')
          ->where('products.name', 'like', '%'.$query.'%')
          ->where('product_translations.name', 'like', '%'.$query.'%')
          ->where(function ($q) use ($query) {
                foreach (explode(' ', trim($query)) as $word) {
                    $q->where('products.name', 'like', '%' . $word . '%')
                    ->orWhere('product_translations.name', 'like', '%' . $word . '%')
                    ->orWhere('products.name', 'like', '' . $word . '%')
                    ->orWhere('product_translations.name', 'like', '' . $word . '%')
                    ->orWhere('products.name', 'like', '%' . $word . '')
                    ->orWhere('product_translations.name', 'like', '%' . $word . '')
                    ->orWhere('products.name', 'like', '' . $word . '')
                    ->orWhere('product_translations.name', 'like', '' . $word . '')
                    ->orWhere('products.tags', 'like', '%' . $word . '%')
                    ->orWhere('products.tags', 'like', '%' . $word . '')
                    ->orWhere('products.tags', 'like', '' . $word . '%')
                    ->orWhere('products.tags', 'like', '' . $word . '');
                }
            })
          ->groupBy('products.id')
          ->orderByRaw("CASE WHEN products.name LIKE '%".$query1."%'
           THEN 1 WHEN products.name LIKE '".$q[0]."' 
           THEN 2 WHEN products.name LIKE '".$q[0]."%' 
           THEN 3 WHEN products.name LIKE '%".$q[0]."' 
           THEN 5 ELSE 4 END");
    	 $products = $products->select('products.*');
        }

        if ($request->has('color')) {
            $str = '"' . $request->color . '"';
            $products->where('colors', 'like', '%' . $str . '%');
            $selected_color = $request->color;
            
        }  
        if (!empty($product_review)) {
            $products->where('rating','>=', $request->product_review);
        }

        if ($request->has('selected_attribute_values')) {
            $selected_attribute_values = $request->selected_attribute_values;
            foreach ($selected_attribute_values as $key => $value) {
                $str = '"' . $value . '"';
                if ($key == 0)
                    $products->where('choice_options', 'like', '%' . $str . '%');
                else
                    $products->orWhere('choice_options', 'like', '%' . $str . '%');
            }
        }
        $brand_ids = array();
        if ($request->has('brand_id')) {
            $brand_ids = $request->brand_id;
            $products->whereIn('brand_id', $request->brand_id);
        }
    
        $products = $products->paginate(20);
		$query = $request->keyword;

        return view('frontend.product_listing', compact('products', 'query', 'category_id', 'brand_id', 'sort_by', 'seller_id', 'min_price', 'max_price', 'attributes', 'selected_attribute_values', 'colors', 'selected_color', 'discounts', 'brand_ids', 'categorydata'));
    }

    public function listing(Request $request)
    {
        return $this->index($request);
    }

    public function listingByCategory(Request $request, $category_slug)
    {
   
    if(preg_match('/[A-Z]/', $category_slug)){
    return redirect()->route('products.category',strtolower($category_slug),301);
}
        $category = Category::where('slug', $category_slug)->first();
    if($category == null){
     $category = Category::where('slug_old', $category_slug)->first();
    return redirect()->route('products.category',strtolower($category->slug),301);
    }
        if ($category != null) {
            return $this->index($request, $category->id);
        }
        abort(404);
    }

    public function listingByBrand(Request $request, $brand_slug)
    {
        $brand = Brand::where('slug', $brand_slug)->first();
        if ($brand != null) {
        	return redirect()->route('search',['min_price'=>'','min_price'=>'','keyword'=>'','sort_by'=>'price-asc','brand_id[]'=>$brand->id]);
           // return $this->index($request, null, $brand->id);
        }
        abort(404);
    }
 //Suggestional Search
    public function ajax_search(Request $request)
    {

         $keywords = array();
         $query = implode('%',str_split(str_replace(" ","",$request->search)));
         $tags = ProductTag::where('status',1)->where('tag', 'like', '%'.$request->search.'%')               
        ->orderByRaw("CASE WHEN tag LIKE '".$request->search."' THEN 1 WHEN tag LIKE '".$request->search."%' THEN 2 WHEN tag LIKE '%".$request->search."' THEN 3 ELSE 4 END")->get()->take(10);

        $shops = Shop::whereIn('user_id', verified_sellers_id())->where('name', 'like', '%' . $request->search . '%')->get()->take(3);
        $search_str = $request->search;                            
        if (sizeof($keywords) > 0 || sizeof($tags) > 0 || sizeof($shops) > 0) {
            return view('frontend.partials.search_content', compact('tags', 'keywords', 'shops','search_str'));
        }
        return '0';
        
    }
    //Suggestional Search



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $search = Search::where('query', $request->keyword)->first();
        if ($search != null) {
            $search->count = $search->count + 1;
            $search->save();
        } else {
            $search = new Search;
            $search->query = $request->keyword;
            $search->save();
        }
    }
}
