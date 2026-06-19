<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;
     protected $fillable = ['group', 'name', 'locked', 'payload'];
    
    protected $casts = [
        'locked' => 'boolean',
        'payload' => 'array',
    ];
    
}
