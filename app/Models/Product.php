<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
     protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'main_image',
        'original_price',
        'price',
        'qty',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'price' => 'decimal:2',
            'qty' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
