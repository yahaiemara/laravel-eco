<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
 protected $fillable = [
    "name", 
    "slug",
    "short_description",
    "description",
    "regular_price",
    "sale_price",
    "SKU",
    "stock_status",
    "quantity",
    "featured",
    "image",
    "images",
    "category_id",
    "brand_id"    
 ];
    public function category(){
        return $this->belongsTo(Category::class,"category_id");
    }
    public function brand(){
        return $this->belongsTo(Brands::class,"brand_id");
    }
}
