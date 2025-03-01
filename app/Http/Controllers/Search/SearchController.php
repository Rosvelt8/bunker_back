<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Search;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // function to pour ajouter une recherche dans la base de données
    public function addSearch(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
        ]);
        // verifier si la recherche existe déjà
        $search = Search::where('search', $request->search)->first();
        if ($search) {
            return response()->json(['message' => 'Search already exists'], 400);
        }
        $search = Search::create([
            'search' => $request->search,
        ]);

        return response()->json(['message' => 'Search added successfully', 'search' => $search], 201);
    }
}
