<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Liste des catégories
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function indexWithSubCategories()
    {
        $categories = Category::with('subCategories')->get();
        return response()->json($categories);
    }

    /**
     * Créer une nouvelle catégorie
     */
    public function store(Request $request)
    {
        // 1. Valider les données de la requête
        $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
            'description' => 'nullable|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);
    
        // 2. Gérer le fichier img (si présent)
        $imgUrl = null;
        if ($request->hasFile('img')) {
            // Générer un nom de fichier unique et chiffré
            $fileName = md5(uniqid(rand(), true)) . '.' . $request->file('img')->getClientOriginalExtension();
    
            // Sauvegarder le fichier dans le dossier public/images
            $request->file('img')->move(public_path('images'), $fileName);
    
            // Générer l'URL complète
            $imgUrl = url('images/' . $fileName);
        }
    
        // 3. Créer la catégorie
        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $request->created_by,
            'img' => $imgUrl, // Stocker l'URL complète dans la base de données
        ]);
    
        // 4. Retourner une réponse JSON
        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }
    

    /**
     * Afficher les détails d'une catégorie
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    /**
     * Mettre à jour une catégorie
     */
    public function update(Request $request, $id)
    {
        // Valider les données
        $request->validate([
            'name' => 'nullable|string|unique:categories,name,' . $id . '|max:255',
            'description' => 'nullable|string',
            'img' => [
            'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) && !request()->file('img')) {
                        $fail('The ' . $attribute . ' must be a valid string or an image file.');
                    }
                    if (request()->file('img')) {
                        $file = request()->file('img');
                        $allowedMimeTypes = ['jpeg', 'png', 'jpg', 'gif'];
                        if (!in_array($file->getClientOriginalExtension(), $allowedMimeTypes)) {
                            $fail('The ' . $attribute . ' must be a file of type: jpeg, png, jpg, gif.');
                        }
                        if ($file->getSize() > 2048 * 1024) {
                            $fail('The ' . $attribute . ' may not be greater than 2MB.');
                        }
                    }
                },
            ],
        ]);

        // Trouver la catégorie par son ID
        $category = Category::findOrFail($id);

        $url = "" ;
        // Traiter l'image si elle est présente
        if ($request->hasFile('img')) {
            // Supprimer l'ancienne image si elle existe
            if ($category->img) {
                $oldImagePath = public_path('images/' . basename($category->img));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Générer un nouveau nom d'image
            $fileName = md5(uniqid(rand(), true)) . '.' . $request->file('img')->getClientOriginalExtension();

            // Déplacer l'image dans le dossier public/images
            $request->file('img')->move(public_path('images'), $fileName);

            // Mettre à jour l'URL de l'image
            $url = url('images/' . $fileName);
        }
        else {
            $url = $category->img ;
        }

        // Mettre à jour les autres champs
        $category->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            "img" => $url
        ]);

        // Retourner une réponse JSON
        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }



    /**
     * Supprimer une catégorie
     */
    public function destroy($id)
    {
        // 1. Trouver la catégorie par son ID
        $category = Category::find($id);
    
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
    
        // 2. Supprimer l'image associée si elle existe
        if ($category->img) {
            $imagePath = public_path('images/' . basename($category->img));
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    
        // 3. Supprimer la catégorie
        $category->delete();
    
        // 4. Retourner une réponse JSON
        return response()->json([
            'message' => 'Category deleted successfully',
        ], 200);
    }
    
}
