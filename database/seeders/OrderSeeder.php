<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les utilisateurs qui ne sont pas vendeurs ou livreurs
        $users = User::whereIn('status', ['customer', 'admin'])->get();

        if ($users->isEmpty()) {
            throw new \Exception('Aucun client trouvé dans la base de données. Veuillez d\'abord créer des utilisateurs.');
        }

        $statuses = ['on_hold', 'paid', 'in_progress', 'ready', 'depot', 'in_delivery', 'booked'];
        
        $locations = [
            'Dakar Médina',
            'Dakar Plateau',
            'Dakar Point E',
            'Dakar Almadies',
            'Dakar Sacré-Coeur',
            'Dakar Ouakam',
            'Dakar Mermoz',
            'Dakar Yoff',
            'Dakar Ngor',
            'Dakar Pikine'
        ];

        $instructions = [
            'Appeler avant la livraison',
            'Sonner à l\'interphone',
            'Livrer le matin uniquement',
            'Laisser chez le gardien',
            'Fragile, manipuler avec précaution',
            null
        ];

        for ($i = 0; $i < 100; $i++) {
            try {
                Order::create([
                    'user_id' => $users->random()->id,
                    'total_price' => fake()->randomFloat(2, 50, 5000),
                    'delivery_cost' => fake()->randomElement([10, 15, 20, 25, 30]),
                    'delivery_location' => fake()->randomElement($locations),
                    'instructions' => fake()->randomElement($instructions),
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => fake()->dateTimeBetween('-6 months', 'now')
                ]);
            } catch (\Exception $e) {
                echo "Erreur lors de la création de la commande {$i}: " . $e->getMessage() . "\n";
                continue;
            }
        }
    }
}