<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnDetail extends Model
{
    public function purchase_returns()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
}
