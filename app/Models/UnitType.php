<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
