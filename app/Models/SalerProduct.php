<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalerProduct extends Model
{
    use HasFactory;
    protected $primaryKey = 'idsalerproduct';

    protected $fillable = ['saler_id', 'product_id', 'quantity', 'idsalerproduct'];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function saler()
    {
        return $this->belongsTo(User::class, 'saler_id', 'id');
    }
}
