<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['addressable_id','addressable_type','type','name','line1','line2','city','state','postal_code','country','phone'];
    public function addressable(){ return $this->morphTo(); }
}
