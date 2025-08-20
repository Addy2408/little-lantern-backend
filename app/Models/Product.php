<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'common_name',
        'scientific_name',
        'description',
        'price',
    ];
}
