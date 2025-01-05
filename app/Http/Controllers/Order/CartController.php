<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Ajouter un produit au panier
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => DB::raw('quantity + ' . $request->quantity),
            ]
        );

        return response()->json(['message' => 'Product added to cart', 'cartItem' => $cartItem], 201);
    }

    /**
     * Retirer un produit du panier
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cartItem = CartItem::where('user_id', $request->user()->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($cartItem) {
            $cartItem->delete();
            return response()->json(['message' => 'Product removed from cart']);
        }

        return response()->json(['message' => 'Product not found in cart'], 404);
    }

    /**
     * Valider la commande
     */
    public function checkout(Request $request)
    {
        $userId = $request->user()->id;
        $cartItems = CartItem::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // Calcul du total
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->product->price * $item->quantity;
        }

        // Traitement du paiement
        $paymentResult = $this->paymentService->processPayment($totalPrice, 'XAF');

        if ($paymentResult['status'] !== 'success') {
            return response()->json(['message' => 'Payment failed', 'error' => $paymentResult['message']], 400);
        }

        // Créer la commande et les items associés
        DB::transaction(function () use ($userId, $cartItems, $totalPrice, $paymentResult) {
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'status' => 'paid', // Commande validée
                'payment_status' => 'completed', // Paiement réussi
                'transaction_reference' => $paymentResult['transaction_reference'],
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // Vider le panier
            CartItem::where('user_id', $userId)->delete();
        });

        return response()->json([
            'message' => 'Order placed successfully',
            'transaction_reference' => $paymentResult['transaction_reference'],
        ], 201);
    }
}
