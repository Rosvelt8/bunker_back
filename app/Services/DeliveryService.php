<?php

namespace App\Services;

use Illuminate\Http\Request;
use InvalidArgumentException;

class DeliveryService
{
    // Bases according to delivery type
    protected $bases = [
        'domicile' => 2000,
        'point_relais' => 1500
    ];
    /**
     * DeliveryService constructor.
     */

    public function __construct()
    {
        // Constructor can be used for initialization if needed
    }

    /**
     * Calculate delivery cost based on cart and mode
     *
     * @param array $cart
     * @param string $mode
     * @return array
     */
    public function calculateDelivery(array $cart, string $mode)
    {
        if (!array_key_exists($mode, $this->bases)) {
            throw new InvalidArgumentException("Mode de livraison inconnu. Utilise 'domicile' ou 'point_relais'.");
        }

        $base = $this->bases[$mode];
        $total = 0;
        $calculationDetails = [];

        foreach ($cart as $product => $quantity) {
            $coeff = $product['coefficient'] ?? 1.0;
            $cost = $base * $coeff * sqrt($quantity);
            $calculationDetails[] = [
                'product' => $product,
                'base' => $base,
                'coefficient' => $coeff,
                'quantity' => $quantity,
                'cost' => round($cost, 2)
            ];
            $total += $cost;
        }

        $voucher = $this->calculateVoucher($total);

        return [
            'status' => 'success',
            'mode' => $mode,
            'calculations' => $calculationDetails,
            'total_delivery' => round($total, 2),
            'voucher' => round($voucher, 2)
        ];
    }

    /**
     * Calculate voucher (85% of total)
     *
     * @param float $total
     * @return float
     */
    protected function calculateVoucher(float $total): float
    {
        return $total * 0.85;
    }
}
