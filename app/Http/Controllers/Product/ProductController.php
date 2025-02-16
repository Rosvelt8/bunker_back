<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\SalerProduct;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Liste des produits
     */
    public function index()
    {
        $products = Product::with(['subCategory'])->with(['cities'])->get();
        return response()->json($products);
    }

    /**
     * Créer un nouveau produit
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')
            ],
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0|max:1000000',
            'quantity' => 'required|numeric|min:1|max:1000000',

            // Pricing and Promotion
            'originalPrice' => 'nullable|numeric|min:0|max:1000000',
            'discountedPrice' => 'nullable|numeric|min:0|max:1000000',
            'discount' => 'nullable|numeric|min:0|max:100',
            'isPromoted' => 'nullable|boolean',

            // Image Validation
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120' // 5MB max
            ],
            'images.*' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120' // 5MB max
            ],

            // Optional Product Details
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:100',

            // Arrays
            'sizes' => 'nullable|array',
            'sizes.*' => 'string|max:50',
            'colors' => 'nullable|array',
            'colors.*' => 'string|max:50',

            // Additional Attributes
            'material' => 'nullable|string|max:255',
            'dimensions' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0|max:10000',
            'sportType' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:100',

            // Boolean and Numeric Flags
            'isNew' => 'nullable|boolean',
            'inStock' => 'nullable|boolean',
            'salesCount' => 'nullable|integer|min:0',
            'rate' => 'nullable|numeric|min:0|max:5',

            // Date
            'arrivalDate' => 'nullable|date',

            // Subcategory (if applicable)
            'subCategory' => 'required|exists:sub_categories,id',
            'created_by' => 'required|exists:users,id'

        ]);

        // 2. Handle main image upload
        $mainImageUrl = null;
        if ($request->hasFile('image')) {
            $mainFileName = md5(uniqid(rand(), true)) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('images'), $mainFileName);
            $mainImageUrl = url('images/' . $mainFileName);
        }

        // 3. Handle additional images upload
        $additionalImagesUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $fileName = md5(uniqid(rand(), true)) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $fileName);
                $additionalImagesUrls[] = url('images/' . $fileName);
            }
        }

        // 4. Prepare data for product creation
        $productData = $request->except(['image', 'images']);
        $productData['image'] = $mainImageUrl;
        $productData['images'] = count($additionalImagesUrls) > 0 ? $additionalImagesUrls : null;

        // 5. Create the product
        $product = Product::create($productData);
        $sub_category = SubCategory::findOrFail($product->subCategory);
        $sub_category->countProduct = $sub_category->countProduct +  1 ;
        $sub_category->save();

        // 6. Return JSON response
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
        $product = Product::with(['subcategory'])->find($id);
        if($product){

            $product->total_quantity = $product->total_quantity; // Ensure total_quantity is calculated
            return response()->json($product);
        }
        return response()->json([
            'message' => 'Product not found',
        ], 404);
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($id) // Ignore the current product
            ],
            'description' => 'nullable|string|max:5000',
            'price' => 'sometimes|numeric|min:0|max:1000000',

            // Pricing and Promotion
            'originalPrice' => 'nullable|numeric|min:0|max:1000000',
            'discountedPrice' => 'nullable|numeric|min:0|max:1000000',
            'discount' => 'nullable|numeric|min:0|max:100',
            'isPromoted' => 'nullable|boolean',

            // Image Validation
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120' // 5MB max
            ],
            'images.*' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120' // 5MB max
            ],

            // Optional Product Details
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:100',

            // Arrays
            'sizes' => 'nullable|array',
            'sizes.*' => 'string|max:50',
            'colors' => 'nullable|array',
            'colors.*' => 'string|max:50',

            // Additional Attributes
            'material' => 'nullable|string|max:255',
            'dimensions' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0|max:10000',
            'sportType' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:100',

            // Boolean and Numeric Flags
            'isNew' => 'nullable|boolean',
            'inStock' => 'nullable|boolean',
            'salesCount' => 'nullable|integer|min:0',
            'rate' => 'nullable|numeric|min:0|max:5',

            // Date
            'arrivalDate' => 'nullable|date',

            // Subcategory (if applicable)
            'subCategory' => 'sometimes|exists:sub_categories,id',
            'created_by' => 'sometimes|exists:users,id'
        ]);

        // 2. Find the product
        $product = Product::findOrFail($id);

        // 3. Handle main image upload
        if ($request->hasFile('image')) {
            // Delete the old main image if it exists
            if ($product->image) {
                $oldImagePath = public_path(parse_url($product->image, PHP_URL_PATH));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $mainFileName = md5(uniqid(rand(), true)) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('images'), $mainFileName);
            $product->image = url('images/' . $mainFileName);
        }

        // 4. Handle additional images upload
        if ($request->hasFile('images')) {
            // Delete old additional images if they exist
            if ($product->images) {
                $oldImages = json_decode($product->images, true);
                foreach ($oldImages as $oldImage) {
                    $oldImagePath = public_path(parse_url($oldImage, PHP_URL_PATH));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }

            $additionalImagesUrls = [];
            foreach ($request->file('images') as $file) {
                $fileName = md5(uniqid(rand(), true)) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $fileName);
                $additionalImagesUrls[] = url('images/' . $fileName);
            }
            $product->images = $additionalImagesUrls;
        }

        // 5. Update the product
        $product->fill($request->except(['image', 'images']));
        $product->save();
        // $sub_category = SubCategory::findOrFail($product->subCategory);
        // $sub_category->countProduct = $sub_category->countProduct +  $product->quantity ;
        // $sub_category->save();

        // 6. Return JSON response
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    /**
     * Mettre à jour un produit
    */
    public function updateInStock(Request $request, $id) {
        $request->validate(['inStock' => 'nullable|boolean',]);
        $product = Product::findOrFail($id);
        if($request->inStock === true && $product->quantity < 1) {
            return response()->json([
                'message' => 'Product quantity insuffisant',
                'product' => $product,
            ], 400);
        }
        $product->inStock = $request->inStock ;
        $product->save();
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }


    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // 2. Delete the main image if it exists
        if ($product->image) {
            $mainImagePath = public_path(parse_url($product->image, PHP_URL_PATH));
            if (file_exists($mainImagePath)) {
                unlink($mainImagePath);
            }
        }

        // 3. Delete additional images if they exist
        if ($product->images) {
            // Ensure $product->images is treated as an array
            $additionalImages = is_array($product->images) ? $product->images : json_decode($product->images, true);

            if (is_array($additionalImages)) {
                foreach ($additionalImages as $additionalImage) {
                    $imagePath = public_path(parse_url($additionalImage, PHP_URL_PATH));
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
        }

        // 4. Delete the product from the database
        $product->delete();

        // 5. Return JSON response
        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }


    /**
     * Ajoute ou met à jour un produit pour un vendeur.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upsert(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'saler_id' => 'required|exists:users,id',
        ]);

        try {
            // Recherche d'un enregistrement existant
            $salerProduct = SalerProduct::where('product_id', $validated['product_id'])
                ->where('saler_id', $validated['saler_id'])
                ->first();
                // dd($salerProduct);

                if ($salerProduct) {
                // Mise à jour si l'enregistrement existe
                $salerProduct->update([
                    'quantity' => $validated['quantity'],
                ]);

                $message = 'Produit mis à jour avec succès.';
            } else {
                // Création si l'enregistrement n'existe pas
                $salerProduct = SalerProduct::create($validated);
                $message = 'Produit ajouté avec succès.';
            }

            return response()->json([
                'message' => $message,
                'data' => $salerProduct,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l’ajout ou de la mise à jour du produit.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function promoteProduct(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'originalPrice' => 'required|numeric|min:0',
            'discountedPrice' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100',
            'isPromoted' => 'required|boolean',
        ]);

        try {
            // Recherche d'un enregistrement existant
            $product = Product::find($validated['product_id']);

            if ($product) {
                // Mise à jour des champs spécifiés
                $product->update([
                    'originalPrice' => $validated['originalPrice'],
                    'discountedPrice' => $validated['discountedPrice'],
                    'discount' => $validated['discount'],
                    'isPromoted' => $validated['isPromoted'],
                ]);

                $message = 'Produit mis à jour avec succès.';
            } else {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            return response()->json([
                'message' => $message,
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred.'], 500);
        }
    }


    public function deleteSalerProduct(Request $request, $id)
    {
        $product_id= $id;
        $saler_id = $request->user()->id;

        try {
            // Recherche d'un enregistrement existant
            $salerProduct = SalerProduct::where('product_id', $product_id)
                ->where('saler_id', $saler_id)
                ->first();

            if ($salerProduct) {
                $salerProduct->delete();

                $message = 'Produit supprimé avec succès.';

                return response()->json([
                    'message' => $message,
                ], 200);
            }
            return response()->json([
                'message' => 'aucun produit trouvé',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du produit.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOneSalerProduct(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'saler_id' => 'required|exists:users,id',
        ]);

        try {
            // Recherche d'un enregistrement existant
            $salerProduct = SalerProduct::where('product_id', $validated['product_id'])
                ->where('saler_id', $validated['saler_id'])
                ->first();

            if ($salerProduct) {

                return response()->json([
                    'data' => $salerProduct,
                ], 200);
            }
            return response()->json([
                'message' => 'aucun produit trouvé',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liste les produits ajoutés par un vendeur spécifique.
     *
     * @param int $saler_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listBySaler($saler_id)
    {
        try {
            // Récupérer les produits liés au vendeur
            $salerProducts = SalerProduct::with(['product.subcategory'])
                ->where('saler_id', $saler_id)
                ->get();

            if ($salerProducts->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun produit trouvé pour ce vendeur.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Liste des produits du vendeur récupérée avec succès.',
                'data' => $salerProducts,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des produits.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listBySubCategory($sub_category_id)
    {
        try {
            // Récupérer les produits liés au vendeur
            $products = Product::where('subCategory', $sub_category_id)->with(['subCategory'])->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun produit trouvé pour cette sous-catégorie.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Liste des produits de la sous-catégorie récupérée avec succès.',
                'data' => $products,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des produits.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listTop3SellingProducts()
    {
        $topSellingProducts = Product::orderBy('salesCount', 'desc')->with(['subCategory'])->take(3)->get();

        return response()->json($topSellingProducts);
    }

    public function listTopSellingProducts()
    {
        $topSellingProducts = Product::orderBy('salesCount', 'desc')->with(['subCategory'])->take(8)->get();

        return response()->json($topSellingProducts);
    }

    public function listPromotedProducts()
    {
        $promotedProducts = Product::where('isPromoted', true)->with(['subCategory'])->get();

        return response()->json($promotedProducts);
    }

    public function listNewProducts()
    {
        $newProducts = Product::where('isNew', true)->orderBy('created_at', 'desc')->with(['subCategory'])->take(10)->get();

        return response()->json($newProducts);
    }

    /**
     * Liste les produits ajoutés par un vendeur spécifique.
     *
     * @param int $seller_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listSellersByProduct($product_id)
    {
        try {
            $sellers = SalerProduct::where('product_id', $product_id)
                                   ->with('saler')
                                   ->get();

            if ($sellers->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun vendeur trouvé pour ce produit.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Liste des vendeurs pour le produit récupérée avec succès.',
                'data' => $sellers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des vendeurs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
