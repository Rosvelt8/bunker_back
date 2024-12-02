<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\AuthController;


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

        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        
        Route::post('/beDeliver', [UserController::class, 'BecomeDeliver']);
        Route::post('/beSaler', [UserController::class, 'BecomeSaler']);
        
        Route::middleware('admin')->group(function () {
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
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            // ****************CRUD SUB CATEGORY
            Route::get('/sub-categories', [SubCategoryController::class, 'index']);
            Route::post('/sub-categories', [SubCategoryController::class, 'store']);
            Route::get('/sub-categories/{id}', [SubCategoryController::class, 'show']);
            Route::put('/sub-categories/{id}', [SubCategoryController::class, 'update']);
            Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'destroy']);
            
            // ****************CRUD PRODUCT
            Route::get('/products', [ProductController::class, 'index']);
            Route::post('/products', [ProductController::class, 'store']);
            Route::get('/products/{id}', [ProductController::class, 'show']);
            Route::put('/products/{id}', [ProductController::class, 'update']); 
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        });


    });
});


