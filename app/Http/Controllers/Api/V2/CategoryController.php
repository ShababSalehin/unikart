<?php
namespace App\Http\Controllers\Api\V2;
use App\Http\Resources\V2\CategoryCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use App\Shop;
use App\Utility\CategoryUtility;
class CategoryController extends Controller
{

    public function index($parent_id = 0)
    {
        if(request()->has('parent_id') && is_numeric (request()->get('parent_id'))){
          $parent_id = request()->get('parent_id');
        }
        return new CategoryCollection(Category::where('parent_id', $parent_id)->orderBy('order_level', 'desc')->get());
    }

    public function featured()
    {
        return new CategoryCollection(Category::where('featured', 1)->get());
    }

    public function home()
    {
        $homepageCategories = BusinessSetting::where('type', 'home_categories')->first();
        $homepageCategories = json_decode($homepageCategories->value);
        return new CategoryCollection(Category::whereIn('id', $homepageCategories)->get());
    }

    public function top()
    {
        $homepageCategories = BusinessSetting::where('type', 'home_categories')->first();
        $homepageCategories = json_decode($homepageCategories->value);
        return new CategoryCollection(Category::whereIn('id', $homepageCategories)->limit(20)->get());
    }

    public function categories_top()
    {
        $homepageCategories = BusinessSetting::where('type', 'home_categories')->first();
        $homepageCategories = json_decode($homepageCategories->value);
        $homepageCategories = array_reverse($homepageCategories);

        $lists = [];
        $item_id = 0;
        $item_type = '';
        $item_name = '';
        $name = '';
        foreach($homepageCategories as $homeCat){
            $categoryInfo = Category::where('id', $homeCat)->get();
            
            $category_ids = CategoryUtility::children_ids($homeCat);
            
            $category_ids[] = $homeCat;

            $item_id = $categoryInfo[0]['appbanner_link'];
            $item_type = $categoryInfo[0]['appbanner_type'];

            if($item_type == 'Shop'){
                $item_name = Shop::where('id',$item_id)->first();
                if(!empty($item_name)){
                    $name = $item_name->name;
                    }else{
                        $name = "null";
                    }
            }elseif($item_type == 'Brand'){
                $item_name = Brand::where('id',$item_id)->first();
                if(!empty($item_name)){
                $name = $item_name->name;
                }else{
                    $name = "null";
                }
                
            }elseif($item_type == 'Category'){
                $item_name = Category::where('id',$item_id)->first();
                if(!empty($item_name)){
                    $name = $item_name->name;
                    }else{
                        $name = "null";
                    }
            }
   
            $catProducts = Product::leftjoin('product_stocks','products.id','product_stocks.product_id')
            ->select('products.*','product_stocks.qty')
            ->where('product_stocks.qty','>',0)->whereIn('category_id',$category_ids)->where('published',1)
            ->groupBy('products.id')
            ->orderBy('discount_percent','desc');

            $lists[$homeCat] = array('id'=> $categoryInfo[0]['id'], 
                    'name'=> $categoryInfo[0]['name'], 
                    'shop_banner'=> api_asset($categoryInfo[0]['shop_banner']), 
                    'item_id'=> $categoryInfo[0]['appbanner_link'], 
                    'item_type'=> ($categoryInfo[0]['appbanner_type']) ? : 'null',
                    'item_name'=> $name, 
                    'products'=> new ProductMiniCollection($catProducts->inRandomOrder()->take(6)->get()),
                );
            
        }
        $lists = array_reverse($lists);
      return response()->json($lists);
     }
}
