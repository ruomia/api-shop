<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    public $timestamps = true;
    protected $fillable = ['number','user_id','name','tel','province','city','area','address','real_payment','status'];

    public function skus()
    {
        return $this->hasMany('App\Models\OrderSku','order_id');
    }
}
