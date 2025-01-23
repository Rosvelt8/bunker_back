<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        // Récupérer toutes les commandes
        $orders = Order::all();

        if ($orders->isEmpty()) {
            throw new \Exception('Aucune commande trouvée. Veuillez d\'abord exécuter OrderSeeder.');
        }

        $paymentMethods = ['cinetpay', 'credit_card', 'paypal'];
        $statuses = ['pending', 'completed', 'failed'];

        foreach ($orders as $order) {
            // Génération d'un ID de transaction unique selon la méthode de paiement
            $paymentMethod = fake()->randomElement($paymentMethods);
            $transactionId = match ($paymentMethod) {
                'cinetpay' => 'CNET-' . strtoupper(Str::random(10)),
                'credit_card' => 'CC-' . strtoupper(Str::random(12)),
                'paypal' => 'PP-' . strtoupper(Str::random(14)),
                default => 'TX-' . strtoupper(Str::random(10)),
            };

            // Si la commande est 'paid', le paiement est forcément 'completed'
            $status = $order->status === 'paid' ? 'completed' : fake()->randomElement($statuses);

            try {
                Payment::create([
                    'order_id' => $order->idorder,
                    'amount' => $order->total_price + $order->delivery_cost,
                    'payment_method' => $paymentMethod,
                    'status' => $status,
                    'transaction_id' => $transactionId,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at
                ]);
            } catch (\Exception $e) {
                echo "Erreur lors de la création du paiement pour la commande {$order->idorder}: " . $e->getMessage() . "\n";
                continue;
            }
        }
    }
}