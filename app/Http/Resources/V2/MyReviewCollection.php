<?php
namespace App\Http\Resources\V2;
use Illuminate\Http\Resources\Json\ResourceCollection;
class MyReviewCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($review, $noreview) {
                return [
                    'review'=>[
                        'id'=> $review->id,
                        'name'=> $review->name,
                        'created_at'=> $review->created_at,
                    ],
                    'noreview'=>[
                    ],
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
