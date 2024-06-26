<?php
namespace App\Http\Resources\V2;
use Illuminate\Http\Resources\Json\ResourceCollection;
class RefundRequestCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id' => (int)$data->id,
                    'user_id' => (int)$data->user_id,
                    'order_id' => $data->order_id,
                    'order_code' => $data->order == null ? "" : $data->order->code,
                    'refund_status' => $data->refund_status,
                    'refund_label' => $data->refund_status == 1 ? 'Approved' : 'PENDING',
                    'date' => date('d-m-Y', strtotime($data->created_at)),
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
