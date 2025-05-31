<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('unitType')->get();
        return response()->json($units);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'symbol' => 'nullable|string|max:10',
            'unit_type_id' => 'required|exists:unit_types,id'
        ]);

        $unit = Unit::create($request->only(['name', 'symbol', 'unit_type_id']));

        return response()->json([
            'message' => 'Unit created successfully',
            'unit' => $unit
        ], 201);
    }

    public function show($id)
    {
        $unit = Unit::with('unitType')->findOrFail($id);
        return response()->json($unit);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50',
            'symbol' => 'nullable|string|max:10',
        ]);

        $unit->update($request->only(['name', 'symbol', 'unit_type_id']));

        return response()->json([
            'message' => 'Unit updated successfully',
            'unit' => $unit
        ]);
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully'
        ]);
    }
}
