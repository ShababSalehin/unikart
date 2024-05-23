<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slug extends Model
{
    

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $table = 'slug';
    protected $fillable = [
         'product_id', 'new_slug', 'old_slug','redirection_code', 
    ];
}

