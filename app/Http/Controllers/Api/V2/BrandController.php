<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\BrandCollection;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;


class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brand_query = Brand::query();
        if($request->name != "" || $request->name != null){
            $brand_query->where('name', 'like', '%'.$request->name.'%');
            SearchUtility::store($request->name);
        }
        if($request->page=='-1')
        return new BrandCollection($brand_query->get());
    else
        return new BrandCollection($brand_query->paginate(30));
    }

    public function top()
    {
        return new BrandCollection(Brand::where('top', 1)->get());
    }
}
