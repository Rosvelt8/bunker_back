<?php

namespace App\Http\Controllers\SubCategory;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * Liste des sous-catégories
     */
    public function index()
    {
        $subCategories = SubCategory::with('category')->get();
        return response()->json($subCategories);
    }

    /**
     * Créer une nouvelle sous-catégorie
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:sub_categories,name|max:255',
            'category_id' => 'required|exists:categories,id',
            'created_by' => 'required|exists:users,id'
        ]);

        $subCategory = SubCategory::create($request->only(['name', 'category_id', 'created_by']));

        return response()->json([
            'message' => 'SubCategory created successfully',
            'subCategory' => $subCategory,
        ], 201);
    }

    /**
     * Afficher les détails d'une sous-catégorie
     */
    public function show($id)
    {
        $subCategory = SubCategory::with('category')->findOrFail($id);
        return response()->json($subCategory);
    }

    /**
     * Mettre à jour une sous-catégorie
     */
    public function update(Request $request, $id)
    {
        $subCategory = SubCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:sub_categories,name,' . $id . '|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory->update($request->only(['name', 'category_id']));

        return response()->json([
            'message' => 'SubCategory updated successfully',
            'subCategory' => $subCategory,
        ]);
    }

    /**
     * Supprimer une sous-catégorie
     */
    public function destroy($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->delete();

        return response()->json(['message' => 'SubCategory deleted successfully']);
    }
}
