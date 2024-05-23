<?php
namespace App\Http\Resources\V2;
use Illuminate\Http\Resources\Json\ResourceCollection;
class HomeBannerThreeCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data,$key) {
                return [
                    'image_url' => api_asset(json_decode(get_setting('home_banner3_images'), true)[$key]),
                    'image_link_type' => json_decode(get_setting('home_banner3_images_type'),true)[$key],
                    'item_id' => json_decode(get_setting('banner3_images_type_ids'),true)[$key],
                    'item_name'=> 'YC'
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
