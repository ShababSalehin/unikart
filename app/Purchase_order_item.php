<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase_order_item extends Model
{
    protected $table = 'purchase_order_item';
    public function purchase_order()
    {
        return $this->belongsTo(Purchase_order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function refund_request()
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }
}
