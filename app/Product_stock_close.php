<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_stock_close extends Model
{
    protected $table = 'product_stock_close';
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    
}
