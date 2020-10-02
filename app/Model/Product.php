<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    public $timestamps = false;

    public function getCoverImageAttribute($value)
    {
        return env('APP_URL', 'http://api.suibian.ink/') . $value;
    }
}
