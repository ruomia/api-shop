<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skus extends Model
{
    protected $table = 'skus';
    public $timestamps = false;
    protected $fillabel = [];
    public function goods()
    {
        return $this->belongsTo('App\Models\Goods','goods_id');
    }
}
