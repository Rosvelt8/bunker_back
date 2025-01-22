<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller; // Add this line
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\SalerProduct;
use App\Models\Product;
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
        $paidOrders = Order::where('status', 'paid')->get();
        $orderItems = [];
        // dd($paidOrders);

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
        if ($orderProduct->status=='pending') {
            if($order->saler_code===$request->saler_code){
                $orderProduct->status = 'ready';
                $orderProduct->save();
                
                $order->updateStatusIfAllItemsReady();
                
                return response()->json(['message' => 'Order product status updated successfully.']);
            }else{
                return response()->json(['message' => 'Saler code not valid.'], 400);
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
        $readyOrders = Order::with('items.product')->with('user')->where('status', 'ready')->get();

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

        return response()->json(['message' => 'Order delivered successfully.']);
    }

    public function listInDeliveringOrders(Request $request)
    {
        // Get all orders with status 'delivered'
        $deliveredOrders = Order::with('items.product')->with('user')->where('deliver_id', $request->user()->id)->where('status', 'in_delivery')->get();

        return response()->json($deliveredOrders);
    }

    public function deliverOrder(Request $request, $orderId)
    {
        // Find the order
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Deliver the order
        $order->status = 'booked';
        $order->save();

        return response()->json(['message' => 'Order delivered successfully.']);
    }



}
