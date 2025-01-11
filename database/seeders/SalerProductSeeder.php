<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalerProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalerProductSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les vendeurs
        $salers = User::where('status', 'seller')->get();
        
        // Récupérer tous les produits
        $products = Product::all();

        // Si nous n'avons pas de vendeurs, créons-en un
        if ($salers->isEmpty()) {
            $saler = User::create([
                'name' => 'Vendeur Test',
                'email' => 'vendeur@test.com',
                'password' => bcrypt('password'),
                'status' => 'seller',
                'is_validated' => true
            ]);
            $salers = collect([$saler]);
        }

        // Créer 10 associations produit-vendeur
        for ($i = 0; $i < 10; $i++) {
            $product = $products->random();
            $saler = $salers->random();

            SalerProduct::create([
                'product_id' => $product->id,
                'quantity' => fake()->numberBetween(1, 50),
                'saler_id' => $saler->id
            ]);
        }
    }
}