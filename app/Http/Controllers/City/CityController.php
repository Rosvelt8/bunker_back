<?php

namespace App\Http\Controllers\City;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Liste des villes
     */
    public function index()
    {
        $cities = City::with('country')->get();
        return response()->json($cities);
    }

    /**
     * Créer une nouvelle ville
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        $city = City::create($request->only(['name', 'country_id']));

        return response()->json([
            'message' => 'City created successfully',
            'city' => $city,
        ], 201);
    }

    /**
     * Afficher les détails d'une ville
     */
    public function show($id)
    {
        $city = City::with('country')->findOrFail($id);
        return response()->json($city);
    }

    /**
     * Mettre à jour une ville
     */
    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        $city->update($request->only(['name', 'country_id']));

        return response()->json([
            'message' => 'City updated successfully',
            'city' => $city,
        ]);
    }

    /**
     * Supprimer une ville
     */
    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return response()->json(['message' => 'City deleted successfully']);
    }
}