<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = ['order_id','carrier','service','tracking','label_url','shipped_at'];
    protected $casts = ['shipped_at'=>'datetime'];
    public function order(){ return $this->belongsTo(Order::class); }
}
