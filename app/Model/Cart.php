<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{

    public function product()
    {
        return $this->belongsTo('App\Model\Product');
    }

    public $timestamps = false;
}
