<?php

namespace App\Models\Shop;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use SoftDeletes,HasFactory;
    public $timestamps = false;
    protected $hidden = ['pivot'];

    protected $fillable = [
        'customer_id',
        'address_id'
    ];
    
    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'customer_addresses');
    }
    public function customers()
    {
        return $this->belongsToMany(Address::class, 'customer_addresses');
    }
}
