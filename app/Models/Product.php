<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'quantity', 'originalPrice', 'discountedPrice', 'discount',
        'isPromoted', 'image', 'images', 'description', 'delay', 'delay_promo','coefficient', 
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
    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class, 'product_sub_category', 'product_id', 'sub_category_id');
    }

    /**
     * Relation avec User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec les unités
     */
    public function units()
    {
        return $this->belongsToMany(Unit::class, 'product_unit', 'product_id', 'unit_id')->withPivot('value');
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, User::class, 'id', 'id', 'id', 'city_id');
    }

    /**
     * Accessor for total quantity
     */
    public function getTotalQuantityAttribute()
    {
        return $this->hasMany(SalerProduct::class, 'product_id')->sum('quantity');
    }
}
