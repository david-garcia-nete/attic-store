<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['product_variant_id','qty_on_hand','qty_reserved','bin_location'];
    public function variant(){ return $this->belongsTo(ProductVariant::class,'product_variant_id'); }

    public function reserve(int $qty): void {
        if (($this->qty_on_hand - $this->qty_reserved) < $qty) {
            throw new \RuntimeException('Insufficient stock');
        }
        $this->qty_reserved += $qty;
        $this->save();
    }
    public function commit(int $qty): void {
        $this->qty_on_hand -= $qty;
        $this->qty_reserved -= $qty;
        if ($this->qty_on_hand < 0 || $this->qty_reserved < 0) {
            throw new \RuntimeException('Inventory would go negative');
        }
        $this->save();
    }
}
