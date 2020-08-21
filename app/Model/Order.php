<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = ['user_id', 'address', 'pay_at', 'no',
        'created_at',];

    public function product()
    {
        return $this->hasMany('App\Model\OrderProduct', 'order_id', 'id');
    }

    public $timestamps = false;
}
