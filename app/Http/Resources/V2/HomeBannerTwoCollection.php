<?php
namespace App\Http\Resources\V2;
use Illuminate\Http\Resources\Json\ResourceCollection;
class HomeBannerTwoCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data,$key) {
                return [
                    'image_url' => api_asset(json_decode(get_setting('home_banner2_images'), true)[$key]),
                    'image_link_type' => json_decode(get_setting('home_banner2_images_type'))[$key],
                    'item_id' => json_decode(get_setting('banner2_images_type_ids'))[$key],
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
