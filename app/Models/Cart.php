<?php

namespace App\Models;

use App\User;
use App\Address;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $guarded = [];
    protected $fillable = ['address_id','price','tax','shipping_cost','discount','coupon_code','coupon_applied','quantity','user_id','owner_id','product_id','variation','offer_status','offer_discount_amount','unicart_discount_amount','confirm_status','app_discount_amount','unit_price','temp_user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
