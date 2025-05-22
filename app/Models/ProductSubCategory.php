<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSubCategory extends Model
{
    use HasFactory;

    protected $table = 'product_sub_category';

    protected $fillable = [
        'product_id',
        'sub_category_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
