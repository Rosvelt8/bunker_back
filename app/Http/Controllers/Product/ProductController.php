<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
        $products = Product::with(['subcategory'])->get();
        return response()->json($products);
    }

    /**
     * Créer un nouveau produit
     */
    public function store(Request $request)
    {
        // 1. Validate the request data
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
        $product = Product::with(['subcategory'])->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, $id)
    {
        // 1. Validate the request data
        $request->validate([
            'name' => [
                'sometimes', 
                'string', 
                'max:255',
                Rule::unique('products', 'name')->ignore($id) // Ignore the current product
            ],
            'description' => 'nullable|string|max:5000',
            'price' => 'sometimes|numeric|min:0|max:1000000',
            'quantity' => 'sometimes|numeric|min:1|max:1000000',
            
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
    
        // 6. Return JSON response
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
}
