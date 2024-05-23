<?php
namespace App\Http\Resources\V2;
use Illuminate\Http\Resources\Json\ResourceCollection;
class FollowShopCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'      => (int) $data->id,
                    'shop_id' => (int) $data->shop_id,
                    'name' =>  $data->name,
                    'logo' => api_asset($data->logo),
                    'user_id' => (int) $data->user_id,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
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
