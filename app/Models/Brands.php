<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    use HasFactory;
    protected $fillable = [ 
        "name",
        "slug",
        "image",
    ];
    public function product(){
        return $this->hasMany(Product::class,);
    }

    public function products(){
        return $this->hasMany(Product::class,"brand_id");
    }
}
