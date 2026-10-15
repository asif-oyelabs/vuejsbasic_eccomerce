<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'sort_order',
        'status',
    ];
    
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
