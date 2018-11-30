<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSku extends Model
{
    protected $table = 'orders_skus';
    public $timestamps = false;
    protected $fillable = ['order_id','sku_id','goods_id','price','count'];

    public function order()
    {
        return $this->belongsTo('App\Models\Order','order_id');
    }
}
