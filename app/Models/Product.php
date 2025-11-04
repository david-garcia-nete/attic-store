<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['name','slug','description','price','cost','is_active'];

    public function variants() { return $this->hasMany(ProductVariant::class); }
    public function categories() { return $this->belongsToMany(ProductCategory::class, 'category_product','product_id','product_category_id'); }
}
