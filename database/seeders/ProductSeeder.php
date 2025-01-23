<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            'Samsung', 'Apple', 'Nike', 'Adidas', 'Sony', 'LG', 'HP', 'Dell', 'Zara', 'H&M',
            'Puma', 'Asus', 'Lenovo', 'Canon', 'Philips'
        ];

        $colors = ['Rouge', 'Bleu', 'Noir', 'Blanc', 'Gris', 'Vert', 'Jaune', 'Rose', 'Orange', 'Violet'];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $levels = ['Débutant', 'Intermédiaire', 'Avancé', 'Professionnel'];
        $storage = ['64GB', '128GB', '256GB', '512GB', '1TB'];
        $materials = ['Coton', 'Polyester', 'Cuir', 'Nylon', 'Laine', 'Soie', 'Aluminium', 'Plastique'];

        // Récupérer toutes les sous-catégories
        $subCategories = SubCategory::all();
        // Récupérer un vendeur (ou admin par défaut)
        $seller = User::where('status', 'seller')->first() ?? User::where('status', 'admin')->first();

        for ($i = 0; $i < 50; $i++) {
            $subCategory = $subCategories->random();
            $price = fake()->randomFloat(2, 10, 1000);
            $discount = fake()->boolean(30) ? fake()->randomFloat(2, 5, 50) : null;
            $discountedPrice = $discount ? $price * (1 - $discount/100) : null;

            $product = Product::create([
                'name' => fake()->words(3, true),
                'quantity' => fake()->numberBetween(0, 100),
                'price' => $price,
                'originalPrice' => $price,
                'discountedPrice' => $discountedPrice,
                'discount' => $discount,
                'isPromoted' => fake()->boolean(20),
                'created_by' => $seller->id,
                'subCategory' => $subCategory->id,
                'image' => 'products/default.jpg',
                'images' => json_encode(['products/img1.jpg', 'products/img2.jpg']),
                'description' => fake()->paragraphs(2, true),
                'brand' => fake()->randomElement($brands),
                'model' => fake()->bothify('##??-####'),
                'storage' => in_array($subCategory->name, ['Smartphones', 'Tablettes', 'Ordinateurs portables']) 
                    ? fake()->randomElement($storage) 
                    : null,
                'sizes' => in_array($subCategory->name, ['T-shirts', 'Pantalons', 'Robes', 'Chaussures homme', 'Chaussures femme']) 
                    ? json_encode(fake()->randomElements($sizes, fake()->numberBetween(3, 6))) 
                    : null,
                'colors' => json_encode(fake()->randomElements($colors, fake()->numberBetween(2, 5))),
                'material' => fake()->randomElement($materials),
                'dimensions' => fake()->numberBetween(10, 100) . 'x' . fake()->numberBetween(10, 100) . 'x' . fake()->numberBetween(10, 100),
                'weight' => fake()->randomFloat(2, 0.1, 20),
                'sportType' => $subCategory->category_id === 6 ? fake()->randomElement(['Football', 'Basketball', 'Tennis', 'Fitness']) : null,
                'level' => $subCategory->category_id === 6 ? fake()->randomElement($levels) : null,
                'rate' => fake()->randomFloat(2, 3.5, 5),
                'isNew' => fake()->boolean(30),
                'salesCount' => fake()->numberBetween(0, 1000),
                'inStock' => fake()->boolean(80),
                'arrivalDate' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            ]);
        }
    }
}