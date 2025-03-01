<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller; // Add this line
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\SalerProduct;
use App\Models\Product;
use App\Models\Settings;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {

        $this->paymentService = $paymentService;
    }

    public function listPaidOrderItems(Request $request)
    {

        $sellerCity = $request->user()->city;
        $paidOrders = Order::where('status', 'paid')
                        ->whereHas('user', function ($query) use ($sellerCity) {
                            $query->where('city', $sellerCity);
                        })
                        ->get();
        $orderItems = [];

        foreach ($paidOrders as $order) {
            $items = OrderProducts::where('order_id', $order->idorder)->where('status', 'available')->with('product')->get();
            foreach ($items as $item) {
                $salerProduct = SalerProduct::where('saler_id', $request->user()->id)
                                            ->where('product_id', $item->product_id)
                                            ->first();
                if ($salerProduct) {
                    $orderItems[] = $item;
                }
            }
        }

        return response()->json($orderItems, 200);
    }

    public function assignSalerToOrderProduct(Request $request)
    {
        $orderProduct = OrderProducts::find($request->orderProductId);

        if ($orderProduct) {
            $salerProduct= Product::find($orderProduct->product_id);
            $salerProduct->quantity -= $orderProduct->quantity;
            $orderProduct->status = 'pending';
            $orderProduct->saler_id = $request->user()->id;
            $orderProduct->save();

            addNotification($request->user()->id, "Vous avez une commande à traiter");

            return response()->json(['message' => 'Order product status updated and saler assigned successfully.']);
        }

        return response()->json(['message' => 'Order product not found.'], 404);
    }

    public function listAssignedOrderItems(Request $request)
    {
        $salerId = $request->user()->id;
        $assignedItems = OrderProducts::where('saler_id', $salerId)->where('status', 'pending')->with('product')->get();

        return response()->json($assignedItems);
    }

    public function validateAssignedOrderItems(Request $request)
    {
        $orderProduct = OrderProducts::find($request->orderProductId);
        $order= Order::find($orderProduct->order_id);
        // Check if the saler code is valid  and if the user is admin
        if ($request->user()->status == 'admin') {
            $orderProduct->status = 'ready';
            $orderProduct->save();

                $order->updateStatusIfAllItemsReady();
                addNotification($request->user()->id, "Nouvelle commande prête à être déposée");


                return response()->json(['message' => 'Order product status updated successfully.']);
            }else{
                if ($orderProduct->status=='pending') {

                if($order->saler_code===$request->saler_code ){
                    $orderProduct->status = 'ready';
                    $orderProduct->save();

                    $order->updateStatusIfAllItemsReady();
                    addNotification($request->user()->id, "Dépôt effectué avec succès");

                    return response()->json(['message' => 'Order product status updated successfully.']);
                }else{
                    return response()->json(['message' => 'Saler code not valid.'], 400);
                }
            }

        }

        return response()->json(['message' => 'Order product not found.'], 404);
    }

    public function historyOrderItemsBySaler(Request $request)
    {
        $salerId = $request->user()->id;
        $readyItems = OrderProducts::where('saler_id', $salerId)
                                    ->where('status', 'ready')
                                    ->with('product')
                                    ->get();

        return response()->json($readyItems);
    }

    /**
     * List all orders of a specific user
     */
    public function listUserOrders(Request $request)
    {
        $userId = $request->user()->id;
        $orders = Order::where('user_id', $userId)->with('items.product')->get();

        return response()->json($orders);
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Request $request, $orderId)
    {

        // Find the order
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Cancel the order
        $order->status = 'cancelled';
        $order->save();
        addNotification($order->user_id, "Votre commande ". $order->getOrderNumberAttribute() ." a été annulée");


        return response()->json(['message' => 'Order cancelled successfully.']);
    }

    /**
     * List all orders
     */
    public function listAllOrders(Request $request)
    {

        // Get all orders
        $orders = Order::with('items.product')->with('user')->get();

        return response()->json($orders);
    }

    public function listReadyOrdersForDeliver(Request $request)
    {
        // Get all orders with status 'ready'
        $sellerCity = $request->user()->city;
        $readyOrders = Order::with('items.product')
                        ->with('user')
                        ->where('status', 'ready')
                        ->whereHas('user', function ($query) use ($sellerCity) {
                            $query->where('city', $sellerCity);
                        })
                        // si la livraison est true
                        ->where('delivery', true)
                        ->get();

        return response()->json($readyOrders);
    }

    public function assignOrderToDeliver(Request $request, $orderId)
    {
        // Find the order
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Deliver the order
        $order->status = 'in_delivery';
        $order->deliver_id= $request->user()->id;
        $order->save();

        addNotification($order->user_id, "Votre commande ". $order->getOrderNumberAttribute() ." est en cours de livraison");

        return response()->json(['message' => 'Order delivered successfully.']);
    }

    public function listInDeliveringOrders(Request $request)
    {
        // Get all orders with status 'delivered'
        $deliveredOrders = Order::with('items.product')->with('user')->where('deliver_id', $request->user()->id)->where('status', 'in_delivery')->get();

        return response()->json($deliveredOrders);
    }

    public function deliverOrder(Request $request)
    {

        // Find the order
        $order = Order::find($request->order);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        $userId = $request->user()->id;
        if($order->user_id != $userId){
            return response()->json(['message' => 'You are not the customer of this order.'], 403);
        }
        $toPaid= Settings::getDeliveryAmount($order->total_price)+$order->delivery_cost;
        // Traitement du paiement
            $paymentResult = $this->paymentService->processPayment((int)$toPaid, "paiement à la livraison", 'XAF', [
                'verify' => false, // Disable SSL verification
            ]);

            if ($paymentResult['status'] !== 'success') {
                return response()->json(['message' => 'Payment failed', 'error' => $paymentResult['message']], 400);
            }
            addNotification($order->user_id, "Votre commande ". $order->getOrderNumberAttribute() ." est en attente du dernier paiement");

        DB::transaction(function () use ($order, $toPaid , $paymentResult) {
            // Deliver the order
            $order->save();

            Payment::create([
                'order_id' => $order->idorder,
                'amount' => $toPaid,
                'payment_method' => 'monetbil',
                'status' => 'pending',
                'transaction_id' => $paymentResult['transaction_reference'],
            ]);

        });

        return response()->json([
            'message' => 'Delivery placed successfully',
            'transaction_reference' => $paymentResult['transaction_reference'],
            'payment_url' => $paymentResult['payment_url'],
        ], 201);
    }

    public function deliverHistory(Request $request)
    {
        // Get all orders with status 'delivered'
        $deliveredOrders = Order::with('items.product')->with('user')->where('deliver_id', $request->user()->id)->where('status', 'booked')->get();

        return response()->json($deliveredOrders);
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        // Find the order
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        $allowedStatus = ['on_hold','paid', 'in_progress', 'ready','in_delivery', 'booked', 'cancelled'];
        // verify if the status is allowed
        addNotification($order->user_id, "Votre commande ". $order->getOrderNumberAttribute() ." a changé de statut");

        if (!in_array($request->status, $allowedStatus)) {
            return response()->json(['message' => 'Status not allowed.'], 400);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated successfully.']);
    }

    // update order delivery cost by admin only and delivery location
    public function updateOrderDeliveryInfos(Request $request, $orderId)
    {
        // Find the order
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->delivery = true;
        $order->delivery_cost = $request->delivery_cost;
        $order->delivery_location = $request->delivery_location;
        $order->save();

        return response()->json(['message' => 'Order delivery cost updated successfully.']);
    }





}
