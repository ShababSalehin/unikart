<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase_order extends Model
{
    protected $table = 'purchase_order';
    protected $fillable = ['po_id','supplier_id','date','total_value','purchase_no'];
    public function purchase_order_item()
    {
        return $this->hasMany(Purchase_order_item::class);
    }

}
