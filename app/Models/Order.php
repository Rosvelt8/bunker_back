<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;
    protected $primaryKey = 'idorder';

    protected $fillable = ['user_id', 'total_price', 'status', 'saler_code', 'delivery_location','amount_paid', 'instructions'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->saler_code = self::generateUniqueSalerCode();
        });
    }

    public static function generateUniqueSalerCode()
    {
        do {
            $code = Str::random(7);
        } while (self::where('saler_code', $code)->exists());

        return $code;
    }

    public function items()
    {
        return $this->hasMany(OrderProducts::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for order number
    public function getOrderNumberAttribute()
    {
        return 'ORD-' . $this->idorder . '-' . $this->created_at->format('Ymd');
    }

    public function updateStatusIfAllItemsReady()
    {
        $allItemsReady = $this->items->every(function ($item) {
            return $item->status === 'ready';
        });

        if ($allItemsReady) {
            $this->status = 'ready';
            $this->save();
        }
    }
}
