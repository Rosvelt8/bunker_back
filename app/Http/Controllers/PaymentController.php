<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function notify(Request $request)
    {
        $result = $this->paymentService->handleNotification($request);

        if ($result['status'] === 'success') {
            // Handle successful notification
            return response()->json(['message' => 'Notification handled successfully'], 200);
        }

        // Handle failed notification
        return response()->json(['message' => 'Notification handling failed', 'error' => $result['message']], 400);
    }

    public function callback(Request $request)
    {
        $result = $this->paymentService->handleCallback($request);

        if ($result['status'] === 'success') {
            // Handle successful callback
            return response()->json(['message' => 'Callback handled successfully'], 200);
        }

        // Handle failed callback
        return response()->json(['message' => 'Callback handling failed', 'error' => $result['message']], 400);
    }
}
