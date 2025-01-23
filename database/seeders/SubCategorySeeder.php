<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    public function run()
    {
        $subCategories = [
            'Électronique' => [
                'Smartphones',
                'Tablettes',
                'Ordinateurs portables',
                'Accessoires électroniques'
            ],
            'Mode Homme' => [
                'T-shirts',
                'Pantalons',
                'Chaussures homme',
                'Accessoires homme'
            ],
            'Mode Femme' => [
                'Robes',
                'Jupes',
                'Chaussures femme',
                'Sacs'
            ],
            'Alimentation' => [
                'Fruits et légumes',
                'Viandes',
                'Boissons',
                'Snacks'
            ],
            'Maison' => [
                'Salon',
                'Chambre',
                'Cuisine',
                'Salle de bain'
            ],
            'Sport' => [
                'Fitness',
                'Football',
                'Basketball',
                'Running'
            ],
            'Livres' => [
                'Romans',
                'Sciences',
                'Histoire',
                'Cuisine'
            ],
            'Beauté' => [
                'Maquillage',
                'Soins visage',
                'Parfums',
                'Soins cheveux'
            ],
            'Informatique' => [
                'PC de bureau',
                'Périphériques',
                'Logiciels',
                'Composants'
            ],
            'Bricolage' => [
                'Outillage',
                'Peinture',
                'Électricité',
                'Jardinage'
            ]
        ];

        // Récupération d'un admin ou d'un utilisateur pour created_by
        $admin = User::where('status', 'admin')->first() ?? User::first();

        foreach ($subCategories as $categoryName => $subs) {
            $category = Category::where('name', $categoryName)->first();
            if ($category) {
                foreach ($subs as $subName) {
                    SubCategory::create([
                        'name' => $subName,
                        'countProduct' => 0, // Valeur par défaut
                        'category_id' => $category->id,
                        'created_by' => $admin->id
                    ]);
                }
            }
        }
    }
}