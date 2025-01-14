<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Country\CountryController;
use App\Http\Controllers\Order\CartController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\City\CityController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\SubCategory\SubCategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Auth\AuthController;


Route::get('images/{filename}', function ($filename) {
    $path = public_path('images/' . $filename);

    // Vérifier si le fichier existe
    if (!file_exists($path)) {
        return response()->json(['message' => 'Image not found'], 404);
    }

    // Retourner l'image avec le type MIME correct
    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return Response::make($file, 200)->header("Content-Type", $type);
});

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/password/reset/{token}', function ($token) {
        return response()->json([
            'message' => 'Password reset URL',
            'token' => $token,
        ]);
    })->name('password.reset');
    Route::post('/password/forgot', [AuthController::class, 'sendResetLinkEmail']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // --------------------------------------------------------------------
        // -------> USER MANAGEMENT
        // --------------------------------------------------------------------

        Route::get('/users', [AuthController::class, 'user']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);

        Route::post('/beDeliver', [UserController::class, 'BecomeDeliver']);
        Route::post('/beSaler', [UserController::class, 'BecomeSaler']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/sub-categories', [SubCategoryController::class, 'index']);


        Route::middleware('seller')->group(function () {

            // *********** PRODUCT MANAGEMENT********************

            Route::post('/saler/upsert', [ProductController::class, 'upsert']);
            Route::delete('/salerproduct/delete', [ProductController::class, 'deleteSalerProduct']);
            Route::get('/salerproduct/getOne', [ProductController::class, 'getOneSalerProduct']);
            Route::get('/salerproduct/{saler_id}/listBySaler', [ProductController::class, 'listBySaler']);
            Route::get('/categories-with-sub', [CategoryController::class, 'indexWithSubCategories']);

            // ************ ORDER MANAGEMENT *********************
            Route::get('/orders/available', [OrderController::class, 'listPaidOrderItems']);
            Route::post('/orders/subscribe', [OrderController::class, 'assignSalerToOrderProduct']);
            Route::get('/orders/subscribed', [OrderController::class, 'listAssignedOrderItems']);
            Route::post('/orders/validate', [OrderController::class, 'validateAssignedOrderItems']);
            Route::get('/orders/history', [OrderController::class, 'historyOrderItemsBySaler']);



        });

        Route::middleware(['admin', 'seller'])->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/getRequests', [UserController::class, 'listRequests']);
            Route::post('/admin/delivery/requests/{user_id}/approveDeliver', [UserController::class, 'approveRequestDeliver']);
            Route::post('/admin/delivery/requests/{user_id}/approveSaler', [UserController::class, 'approveRequestSaler']);
            Route::post('/users/{id}/toggle', [UserController::class, 'toggle']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // --------------------------------------------------------------------
        // -------> ADMINISTRATION MANAGEMENT
        // --------------------------------------------------------------------

            // ****************CRUD COUNTRY
            Route::get('/countries', [CountryController::class, 'index']);
            Route::post('/countries', [CountryController::class, 'store']);
            Route::get('/countries/{id}', [CountryController::class, 'show']);
            Route::put('/countries/{id}', [CountryController::class, 'update']);
            Route::delete('/countries/{id}', [CountryController::class, 'destroy']);

            // ****************CRUD CITY
            Route::get('/cities', [CityController::class, 'index']);
            Route::post('/cities', [CityController::class, 'store']);
            Route::get('/cities/{id}', [CityController::class, 'show']);
            Route::put('/cities/{id}', [CityController::class, 'update']);
            Route::delete('/cities/{id}', [CityController::class, 'destroy']);

            // ****************CRUD CATEGORY
            Route::get('/categories', [CategoryController::class, 'index']);
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::get('/categories/{id}', [CategoryController::class, 'show']);
            Route::post('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            // ****************CRUD SUB CATEGORY
            Route::get('/sub-categories', [SubCategoryController::class, 'index']);
            Route::post('/sub-categories', [SubCategoryController::class, 'store']);
            Route::get('/sub-categories/{id}', [SubCategoryController::class, 'show']);
            Route::put('/sub-categories/{id}', [SubCategoryController::class, 'update']);
            Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'destroy']);

            // ****************CRUD PRODUCT
            Route::post('/products', [ProductController::class, 'store']);
            Route::get('/products/{id}', [ProductController::class, 'show']);
            Route::post('/products/{id}', [ProductController::class, 'update']);
            Route::put('/products/{id}', [ProductController::class, 'updateInStock']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);


        });

        // --------------------------------------------------------------------
        // -------> SALERS MANAGEMENT
        // --------------------------------------------------------------------

        // **************** Gestion du panier utilisateur
        Route::post('/addTocart', [CartController::class, 'addToCart']);
        Route::delete('/removeFromCart', [CartController::class, 'removeFromCart']);
        Route::post('/checkout', [CartController::class, 'checkout']);

        

        Route::get('/products/top3-selling', [ProductController::class, 'listTop3SellingProducts']);
        Route::get('/products/top-selling', [ProductController::class, 'listTopSellingProducts']);
        Route::get('/categories/popular', [CategoryController::class, 'listPopularCategories']);
        Route::get('/products/promoted', [ProductController::class, 'listPromotedProducts']);
        Route::get('/products/new', [ProductController::class, 'listNewProducts']);

    });
});


