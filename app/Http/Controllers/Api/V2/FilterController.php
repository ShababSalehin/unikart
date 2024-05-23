<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\BrandCollection;
use App\Http\Resources\V2\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Brand;
use App\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function categories()
    {
        //if you want to show base categories
        return new CategoryCollection(Category::where('parent_id', 0)->orderBy('order_level', 'desc')->get());

        //if you want to show featured categories
        //return new CategoryCollection(Category::where('featured', 1)->get());
    }

    public function find_brands(Request $request){
        $brand_ids = Product::select('brand_id')->whereIn('category_id',$request->category_id)
        ->groupBy('brand_id')->get()->whereNotNull('brand_id')->pluck('brand_id');
        return new BrandCollection(Brand::whereIn('id',$brand_ids)->orderby('id','asc')->get());
        
    }

    public function brands()
    {
        $homepageBrands = BusinessSetting::where('type', 'top10_brands')->first();
        $homepageBrands = json_decode($homepageBrands->value);
        return new BrandCollection(Brand::whereIn('id',$homepageBrands)->orderby('id','asc')->get());
    }

 


}
