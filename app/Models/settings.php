<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;
    protected $fillable = ['settings'];

    public function getOrderAmount($amount){
        $setting= Self::first();

        $rate= $setting->rate_pay_order;
        $orderAmount= $amount * $rate /100;

        return $orderAmount;

    }

    public function getDeliveryAmount($amount){
        $setting= Self::first();

        $rate= $setting->rate_pay_delivery;
        $deliveryAmount= $amount * $rate /100;

        return $deliveryAmount;

    }


}
