<?php

namespace App\Http\Controllers\Publicites;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Publicite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PubliciteController extends Controller
{
    // Ajouter une publicité avec upload de fichier
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:image,video',
            'source' => 'required|file|mimes:jpeg,png,jpg,mp4|max:10240', // Max 10MB
            'title' => 'required|string',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date|after:today', // Optionnelle mais doit être future
        ]);

        // Stocker le fichier et récupérer le chemin
        $filePath = $request->file('source')->store('publicites');

        $publicite = new Publicite();
        $publicite->type = $request->type;
        $publicite->source = $filePath;
        $publicite->title = $request->title;
        $publicite->description= $request->description;
        $publicite->expires_at = $request->expires_at ? Carbon::parse($request->expires_at) : null;
        $publicite->save();
        // dd($request->title);

        return response()->json($publicite, 201);
    }

    // Lister toutes les publicités actives
    public function index()
    {
        $now = Carbon::now();
        $publicites = Publicite::where(function ($query) use ($now) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', $now);
        })->get();

        $publicites->map(function ($publicite) {
            $publicite->source = Storage::url($publicite->source);
            return $publicite;
        });

        return response()->json($publicites, 200);
    }

    // Supprimer une publicité
    public function destroy($id)
    {
        $publicite = Publicite::find($id);
        if (!$publicite) {
            return response()->json(['message' => 'Publicité non trouvée'], 404);
        }

        // Supprimer le fichier associé
        Storage::delete($publicite->source);
        $publicite->delete();

        return response()->json(['message' => 'Publicité supprimée'], 200);
    }
}
