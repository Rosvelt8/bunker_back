<?php

namespace App\Http\Controllers\Order;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\SalerProduct;
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

        foreach ($paidOrders as $order) {
            $items = OrderProducts::where('order_id', $order->id)->get();
            foreach ($items as $item) {
                $salerProduct = SalerProduct::where('saler_id', $request->user()->id)
                                            ->where('product_id', $item->product_id)
                                            ->first();
                if ($salerProduct) {
                    $orderItems[] = $item;
                }
            }
        }

        return response()->json($orderItems);
    }

    public function assignSalerToOrderProduct(Request $request)
    {
        $orderProduct = OrderProducts::find($request->orderProductId);

        if ($orderProduct) {
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
        $assignedItems = OrderProducts::where('saler_id', $salerId)->get();

        return response()->json($assignedItems);
    }

    public function validateAssignedOrderItems(Request $request)
    {
        $orderProduct = OrderProducts::find($request->orderProductId);

        if ($orderProduct->status=='pending') {
            $orderProduct->status = 'ready';
            $orderProduct->saler_id = $request->user()->id;
            $orderProduct->save();

            return response()->json(['message' => 'Order product status updated successfully.']);
        }

        return response()->json(['message' => 'Order product not found.'], 404);
    }

    public function historyOrderItemsBySaler(Request $request)
    {
        $salerId = $request->user()->id;
        $readyItems = OrderProducts::where('saler_id', $salerId)
                                    ->where('status', 'ready')
                                    ->get();

        return response()->json($readyItems);
    }

}
