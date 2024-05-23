<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    public function purchase_return_details()
    {
        return $this->hasMany(PurchaseReturnDetail::class);
    }
}
