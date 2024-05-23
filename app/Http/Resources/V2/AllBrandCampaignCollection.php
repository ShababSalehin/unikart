<?php
namespace App\Http\Resources\V2;
use Illuminate\Http\Resources\Json\ResourceCollection;
class AllBrandCampaignCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => ($data->id),
                    'photo' => api_asset($data->banner),
                    
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
