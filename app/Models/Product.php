<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'quantity', 'originalPrice', 'discountedPrice', 'discount', 
        'isPromoted', 'subCategory', 'image', 'images', 'description', 
        'brand', 'model', 'storage', 'sizes', 'colors', 'material', 
        'dimensions', 'weight', 'sportType', 'level', 'rate', 
        'isNew', 'salesCount', 'inStock', 'arrivalDate', 'created_by'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'originalPrice' => 'decimal:2',
        'discountedPrice' => 'decimal:2',
        'discount' => 'decimal:2',
        'isPromoted' => 'boolean',
        'images' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        'weight' => 'decimal:2',
        'rate' => 'decimal:2',
        'isNew' => 'boolean',
        'inStock' => 'boolean',
        'arrivalDate' => 'date'
    ];

    /**
     * Relation avec SubCategory
     */
    public function subCategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Relation avec User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
