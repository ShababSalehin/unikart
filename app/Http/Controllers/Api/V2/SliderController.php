<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\SliderCollection;
use App\Models\Brand;
use App\Models\Category;
use App\Shop;
class SliderController extends Controller
{
    public function index_old()
    {
        return new SliderCollection(json_decode(get_setting('app_slider_images'), true));
    }

    public function index()
    {
        
      if (get_setting('app_slider_images') != null){

           $slider_images = json_decode(get_setting('app_slider_images'), true);
           
            $lists = [];
            foreach ($slider_images as $key => $value){
             
              $item_id = json_decode(get_setting('app_slider_ids'), true)[$key];
              $item_type = json_decode(get_setting('app_slider_type'), true)[$key];

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
            
            $lists[] = array(
            'photo'=> api_asset($slider_images[$key]) ? : 'null', 
            'item_id'=> json_decode(get_setting('app_slider_ids'), true)[$key] ? : 'null', 
            'item_type'=> json_decode(get_setting('app_slider_type'), true)[$key] ? : 'null',
            'item_name'=> $name ? : 'null');
            }

           }
              
           $data = array(
            'data' => $lists,
            'success'=> true,
	          'status' => 200
          );
     
      return response()->json($data);
        
    }
}
