<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'idproduct'; // Clé primaire personnalisée
    public $incrementing = true; // Auto-incrément activé
    protected $keyType = 'int'; // Type de la clé primaire
    protected $fillable = ['name', 'quantity', 'price', 'image', 'description', 'subcategory_id', 'created_by'];

    /**
     * Relation avec SubCategory
     */
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Relation avec User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
