<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Settings;
use App\Models\OrderProducts;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;

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
        $cartItem = CartItem::where('user_id', $request->user()->id)
        ->where('product_id', $request->product_id)
        ->first();


        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

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

        $request->validate([
            'delivery' => 'required|boolean',
            'city' => 'nullable|exists:cities,id',
            'delivery_location' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $cartItems = CartItem::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // Calcul du total
        $totalPrice = 0;
        $deliveryCost = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->product->price * $item->quantity;
        }

        if($request->delivery){
            $deliveryCost = 2000;
        }
        $totalPrice+=$deliveryCost;
        $toPaid= Settings::getOrderAmount($totalPrice);
        // dd('here');
        // Traitement du paiement
        $paymentResult = $this->paymentService->processPayment((int)$toPaid, "Paiement à la commande", 'XAF', [
            'verify' => false, // Disable SSL verification
        ]);

        if ($paymentResult['status'] !== 'success') {
            return response()->json(['message' => 'Payment failed', 'error' => $paymentResult['message']], 400);
        }

        // Créer la commande et les items associés
        DB::transaction(function () use ($userId, $request,$deliveryCost, $cartItems, $totalPrice ,$toPaid , $paymentResult) {
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'amount_paid' => 0,
                'status' => 'on_hold',
                'delivery_cost' =>$deliveryCost,
                'delivery_location'=> $request->delivery_location,
                'city'=> $request->city,
                'delivery'=> $request->delivery,
                'instructions'=> $request->instructions,
                'transaction_reference' => $paymentResult['transaction_reference'],
            ]);

            foreach ($cartItems as $item) {
                OrderProducts::create([
                    'order_id' => $order->idorder,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // dd($totalPrice);
            // Enregistrer la transaction dans la table payment
            Payment::create([
                'order_id' => $order->idorder,
                'amount' => $totalPrice,
                'payment_method' => 'cinetpay',
                'status' => 'pending',
                'transaction_id' => $paymentResult['transaction_reference'],
            ]);

            // Vider le panier
            CartItem::where('user_id', $userId)->delete();
        });

        return response()->json([
            'message' => 'Order placed successfully',
            'transaction_reference' => $paymentResult['transaction_reference'],
            'payment_url' => $paymentResult['payment_url'],
        ], 201);
    }

    public function getCart(Request $request)
    {
        $cartItems = CartItem::where('user_id', $request->user()->id)->get();

        return response()->json(['cartItems' => $cartItems]);
    }

    public function validateCheckout(Request $request){

    }

    public function handlePaymentNotification(Request $request)
    {
        $transactionId = $request->input('payment_ref');
        $amount = $request->input('amount');
        $status = $request->input('status');
        $payment_id = $request->input('paymentId');
        // dd($request->input());
        // Verify the transaction status with CinetPay
        // $verificationResult = $this->paymentService->verifyTransaction($payment_id, [
        //     'verify' => false, // Disable SSL verification
        // ]);

        if ($status === 'success') {
            // Update the payment status in the database
            $payment = Payment::where('transaction_id', $transactionId)->first();
            if ($payment && $payment->status !== 'completed') {
                $payment->status = 'completed';
                $payment->save();

                // Update the order status
                $order = Order::find($payment->order_id);
                if ($order) {
                    if($order->status == 'on_hold'){
                        $order->status = 'paid';
                    }
                    if($order->status == 'in_delivery'){
                        $order->status = 'booked';
                    }
                    $order->amount_paid+=$payment->amount;
                    $order->save();
                }
            }
        } else {
            // Handle failed payment
            $payment = Payment::where('transaction_id', $transactionId)->first();
            if ($payment && $payment->status !== 'failed') {
                $payment->status = 'failed';
                $payment->save();

                // Update the order status
                $order = Order::find($payment->order_id);
                if ($order) {
                     if($order->status == 'on_hold'){
                        $order->status = 'unpaid';
                    }
                    $order->save();
                }
            }
        }

        return response()->json(['message' => 'Notification received'], 200);
    }
}
