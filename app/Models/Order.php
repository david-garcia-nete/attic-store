<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['number','user_id','subtotal','discount_total','shipping_total','tax_total','grand_total','status'];
    public function items(){ return $this->hasMany(OrderItem::class); }
    public function payments(){ return $this->hasMany(Payment::class); }
    public function addresses(){ return $this->morphMany(Address::class, 'addressable'); }
    public function shipments(){ return $this->hasMany(Shipment::class); }
}
