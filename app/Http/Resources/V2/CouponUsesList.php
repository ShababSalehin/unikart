<?php
namespace App\Http\Resources\V2;
use Illuminate\Http\Resources\Json\ResourceCollection;
class CouponUsesList extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'      => (int) $data->id,
                    'user_id' => (int) $data->user_id,
                    'coupon_id' => (int) $data->coupon_id,
                    'coupon_code' => $data->code,
                    'coupon_type' => $data->type,
                    'coupon_details' => $data->details,
                    'coupon_discount' => $data->discount,
                    'discount_type' => $data->discount_type,
                    'validity' => (date("d M,Y",$data->end_date)),
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                    'order_id' => (int) $data->order_id,
                    'order_code' => $data->ordercode,
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
