<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['username','password','mobile'];
    public $hidden = ['password','created_at','updated_at'];
}
