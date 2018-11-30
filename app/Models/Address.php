<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';
    public $timestamps = false;
    protected $fillable = ['user_id','name','province','city','area','address','default','tel'];
}
