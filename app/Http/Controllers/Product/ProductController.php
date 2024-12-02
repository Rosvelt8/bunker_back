<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Liste des produits
     */
    public function index()
    {
        $products = Product::with(['subcategory', 'creator'])->get();
        return response()->json($products);
    }

    /**
     * Créer un nouveau produit
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'subcategory_id' => 'required|exists:sub_categories,idsubcategory',
        ]);

        // Upload de l'image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products');
        }

        $product = Product::create([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'image' => $imagePath,
            'description' => $request->description,
            'subcategory_id' => $request->subcategory_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    /**
     * Afficher les détails d'un produit
     */
    public function show($id)
    {
        $product = Product::with(['subcategory', 'creator'])->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'subcategory_id' => 'required|exists:sub_categories,id',
        ]);

        // Upload de l'image si elle est mise à jour
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products');
        }

        $product->update([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'image' => $imagePath,
            'description' => $request->description,
            'subcategory_id' => $request->subcategory_id,
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
