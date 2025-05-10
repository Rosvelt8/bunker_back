<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProducts;
use Illuminate\Database\Seeder;

class OrderProductSeeder extends Seeder
{
    public function run()
    {
        // Récupérer toutes les commandes et produits
        $orders = Order::all();
        $products = Product::all();

        if ($orders->isEmpty()) {
            throw new \Exception('Aucune commande trouvée. Veuillez d\'abord exécuter OrderSeeder.');
        }

        if ($products->isEmpty()) {
            throw new \Exception('Aucun produit trouvé. Veuillez d\'abord exécuter ProductSeeder.');
        }

        // Pour chaque commande, créer entre 1 et 5 lignes de commande
        foreach ($orders as $order) {
            // Nombre aléatoire de produits pour cette commande
            $numberOfProducts = fake()->numberBetween(1, 5);
            
            // Sélectionner des produits aléatoires uniques pour cette commande
            $selectedProducts = $products->random($numberOfProducts);

            foreach ($selectedProducts as $product) {
                OrderProducts::create([
                    'order_id' => $order->idorder,
                    'product_id' => $product->id,
                    'quantity' => fake()->numberBetween(1, 10)
                ]);
            }
        }
    }
}