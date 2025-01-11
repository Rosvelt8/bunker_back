<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Électronique',
                'description' => 'Produits électroniques, smartphones, tablettes et accessoires',
                'img' => 'categories/electronic.jpg'
            ],
            [
                'name' => 'Mode Homme',
                'description' => 'Vêtements, chaussures et accessoires pour homme',
                'img' => 'categories/men-fashion.jpg'
            ],
            [
                'name' => 'Mode Femme',
                'description' => 'Vêtements, chaussures et accessoires pour femme',
                'img' => 'categories/women-fashion.jpg'
            ],
            [
                'name' => 'Alimentation',
                'description' => 'Produits alimentaires et boissons',
                'img' => 'categories/food.jpg'
            ],
            [
                'name' => 'Maison',
                'description' => 'Meubles et décoration d\'intérieur',
                'img' => 'categories/home.jpg'
            ],
            [
                'name' => 'Sport',
                'description' => 'Équipements et vêtements de sport',
                'img' => 'categories/sport.jpg'
            ],
            [
                'name' => 'Livres',
                'description' => 'Livres, magazines et publications',
                'img' => 'categories/books.jpg'
            ],
            [
                'name' => 'Jouets',
                'description' => 'Jouets et jeux pour enfants',
                'img' => 'categories/toys.jpg'
            ],
            [
                'name' => 'Beauté',
                'description' => 'Produits de beauté et soins personnels',
                'img' => 'categories/beauty.jpg'
            ],
            [
                'name' => 'Santé',
                'description' => 'Produits de santé et bien-être',
                'img' => 'categories/health.jpg'
            ],
            [
                'name' => 'Jardin',
                'description' => 'Outils et accessoires de jardinage',
                'img' => 'categories/garden.jpg'
            ],
            [
                'name' => 'Auto-Moto',
                'description' => 'Pièces et accessoires automobiles',
                'img' => 'categories/auto.jpg'
            ],
            [
                'name' => 'Informatique',
                'description' => 'Ordinateurs et accessoires informatiques',
                'img' => 'categories/computer.jpg'
            ],
            [
                'name' => 'Musique',
                'description' => 'Instruments de musique et équipements',
                'img' => 'categories/music.jpg'
            ],
            [
                'name' => 'Bricolage',
                'description' => 'Outils et matériel de bricolage',
                'img' => 'categories/diy.jpg'
            ],
            [
                'name' => 'Bébé',
                'description' => 'Articles pour bébés et jeunes enfants',
                'img' => 'categories/baby.jpg'
            ],
            [
                'name' => 'Animalerie',
                'description' => 'Produits pour animaux de compagnie',
                'img' => 'categories/pets.jpg'
            ],
            [
                'name' => 'Art',
                'description' => 'Fournitures d\'art et artisanat',
                'img' => 'categories/art.jpg'
            ],
            [
                'name' => 'Bijoux',
                'description' => 'Bijoux et accessoires de mode',
                'img' => 'categories/jewelry.jpg'
            ],
            [
                'name' => 'Voyages',
                'description' => 'Équipements et accessoires de voyage',
                'img' => 'categories/travel.jpg'
            ]
        ];

        // On récupère un admin pour created_by
        $admin = User::where('status', 'admin')->first();
        
        // Si pas d'admin, on prend n'importe quel utilisateur
        if (!$admin) {
            $admin = User::first();
        }

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'description' => $category['description'],
                'img' => $category['img'],
                'created_by' => $admin->id
            ]);
        }
    }
}