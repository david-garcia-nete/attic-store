<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = ['product_id','sku','option_summary','price','weight_oz','length_in','width_in','height_in','is_active'];

    public function product(){ return $this->belongsTo(Product::class); }
    public function inventory(){ return $this->hasOne(Inventory::class, 'product_variant_id'); }

    public function priceEffective(): float {
        return (float)($this->price ?? $this->product->price);
    }
}
