<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
    public function index()
    {
        $unitTypes = UnitType::with('units')->get();
        return response()->json($unitTypes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:unit_types,name|max:255'
        ]);

        $unitType = UnitType::create($request->only(['name']));

        return response()->json([
            'message' => 'Unit type created successfully',
            'unitType' => $unitType
        ], 201);
    }

    public function show($id)
    {
        $unitType = UnitType::with('units')->findOrFail($id);
        return response()->json($unitType);
    }

    public function update(Request $request, $id)
    {
        $unitType = UnitType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:unit_types,name,' . $id . '|max:255'
        ]);

        $unitType->update($request->only(['name']));

        return response()->json([
            'message' => 'Unit type updated successfully',
            'unitType' => $unitType
        ]);
    }

    public function destroy($id)
    {
        $unitType = UnitType::findOrFail($id);
        $unitType->delete();

        return response()->json([
            'message' => 'Unit type deleted successfully'
        ]);
    }
}
