<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';
    public $timestamps = true;
    protected $fillabel = [];

    public function attribute()
    {
        return $this->hasMany('App\Models\GoodsAttribute','goods_id');
    }
    public function sku()
    {
        return $this->hasMany("App\Models\Skus",'goods_id');
    }

}
