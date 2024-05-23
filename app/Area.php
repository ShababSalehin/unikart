<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App;

class Area extends Model
{
    public function citi()
    {
        return $this->belongsTo(City::class);
    } 
   
}
