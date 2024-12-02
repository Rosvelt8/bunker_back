<?php

namespace App\Http\Controllers\Country;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Liste des pays
     */
    public function index()
    {
        $countries = Country::all();
        return response()->json($countries);
    }

    /**
     * Créer un nouveau pays
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:countries,name|max:255',
        ]);

        $country = Country::create($request->only(['name']));

        return response()->json([
            'message' => 'Country created successfully',
            'country' => $country,
        ], 201);
    }

    /**
     * Afficher les détails d'un pays
     */
    public function show($id)
    {
        $country = Country::findOrFail($id);
        return response()->json($country);
    }

    /**
     * Mettre à jour un pays
     */
    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:countries,name,' . $id . '|max:255',
        ]);

        $country->update($request->only(['name']));

        return response()->json([
            'message' => 'Country updated successfully',
            'country' => $country,
        ]);
    }

    /**
     * Supprimer un pays
     */
    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return response()->json(['message' => 'Country deleted successfully']);
    }
}