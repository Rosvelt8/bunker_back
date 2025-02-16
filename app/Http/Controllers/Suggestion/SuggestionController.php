<?php

namespace App\Http\Controllers\Suggestion;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use App\Models\Order;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    /**
     * Create a new suggestion
     */
    public function createSuggestion(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,idorder',
            'content' => 'required|string|max:5000',
        ]);

        $suggestion = Suggestion::create([
            'order_id' => $request->order_id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Suggestion created successfully',
            'suggestion' => $suggestion,
        ], 201);
    }

    /**
     * Display suggestions with order number and user information
     */
    public function listSuggestions()
    {
        
        $suggestions = Suggestion::with(['order', 'user'])->get();

        $suggestions = $suggestions->map(function ($suggestion) {
            $suggestion->order_number = $suggestion->order->order_number;
            return $suggestion;
        });

        return response()->json($suggestions);
    }
}
