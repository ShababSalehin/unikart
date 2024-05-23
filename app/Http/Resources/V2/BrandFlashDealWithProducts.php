<?php
namespace App\Http\Resources\V2;

use App\FlashDeal;
use App\Models\FlashDealProduct;
use Illuminate\Http\Resources\Json\ResourceCollection;
class BrandFlashDealWithProducts extends ResourceCollection
{
    public function toArray($request)
    {

        return[

        'data' => $this->collection->map(function($data){

        $flash_deal = FlashDeal::find($data->id);
        $products = FlashDealProduct::leftjoin('products','flash_deal_products.product_id','products.id')
        ->select('products.id','products.name','unit_price','rating','num_of_sale','thumbnail_img','app_discount_type')
        ->where('flash_deal_products.flash_deal_id',$flash_deal->id)
        ->orderBy('flash_deal_products.id','desc')->get();

                foreach($products as $product){
                    $prods = array();
                    $prods['id'] = $product->id;
                    $prods['name'] = $product->name;
                    $prods['thumbnail_image'] = api_asset($product->thumbnail_img);
                    $prods['has_discount'] = home_base_price($product, false) != home_discounted_base_price($product, false,1);
                    $prods['stroked_price'] = home_base_price($product);
                    $prods['main_price'] = home_discounted_base_price($product,true,1);
                    $prods['rating'] = (double)$product->rating;
                    $prods['sales'] =(integer)$product->num_of_sale;
                    $prods['links'] = ['details' => route('products.show', $product->id)];
                    $datas[]=$prods;
                   }

                   return [
                    'id' => $data->id,
                    'title' => $data->title,
                    'date' => (int) $data->end_date,
                    'banner' => api_asset($data->banner),
                    'data' => $datas,
                ];
             
            })
        ];

        
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
