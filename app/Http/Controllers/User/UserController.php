<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\Document;
use Illuminate\Support\Facades\Mail;
use App\Models\User;


class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $users = $this->userService->listUsers($perPage);

        return response()->json($users);
    }

    /**
     * Détails d'un utilisateur
     */
    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        return response()->json($user);
    }

    /**
     * Devenir livreur
     */
    public function BecomeDeliver(Request $request)
    {
    
        // Validation des champs
        $request->validate([
            'documents.*.document_type' => 'required|string|in:id_card,location_map,tax_identifier',
            'documents.*.file' => 'required|file|max:2048', // Taille max 2MB
        ]);
        
    
        $user = $request->user();
    
        // Mettre à jour le statut de requête
        $user->is_delivery_request = true;
    
        // Sauvegarder chaque document
        foreach ($request->documents as $doc) {
            // Générer un nom unique pour chaque fichier
            $fileName = md5(uniqid(rand(), true)) . '.' . $doc['file']->getClientOriginalExtension();
    
            // Déplacer le fichier vers le dossier public/documents
            $doc['file']->move(public_path('documents'), $fileName);
    
            // Construire l'URL complète
            $fileUrl = url('documents/' . $fileName);
    
            // Enregistrer dans la base de données
            Document::create([
                'user_id' => $user->id,
                'document_type' => $doc['document_type'],
                'document_path' => $fileUrl, // URL complète
                'status' => false, // Par défaut non validé
            ]);
        }
        $user->save();
    
        return response()->json([
            'message' => 'Request submitted successfully. Awaiting approval.',
        ]);
    }
    

    /**
     * Devenir vendeur
     */
    public function BecomeSaler(Request $request)
    {
        $request->validate([
            'documents.*.document_type' => 'required|string|in:id_card,location_map,tax_identifier',
            'documents.*.file' => 'required|file|max:2048'
        ]);

        $user = $request->user();
    
        // Mettre à jour le statut de requête
        $user->is_saler_request = true;
    
        // Sauvegarder chaque document
        foreach ($request->documents as $doc) {
            // Générer un nom unique pour chaque fichier
            $fileName = md5(uniqid(rand(), true)) . '.' . $doc['file']->getClientOriginalExtension();
    
            // Déplacer le fichier vers le dossier public/documents
            $doc['file']->move(public_path('documents'), $fileName);
    
            // Construire l'URL complète
            $fileUrl = url('documents/' . $fileName);
    
            // Enregistrer dans la base de données
            Document::create([
                'user_id' => $user->id,
                'document_type' => $doc['document_type'],
                'document_path' => $fileUrl, // URL complète
                'status' => false, // Par défaut non validé
            ]);
        }
        $user->save();
        return response()->json([
            'message' => 'Request submitted successfully. Awaiting approval.'
        ]);
    }

    /**
     * Visualiser toutes les requêtes
     */
    public function listRequests(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $users = $this->userService->listUsers($perPage);

        return response()->json($users);
    }

    /**
     * Approuver un livreur
     */

     public function approveRequestDeliver($userId, Request $request)
     {
        $request->validate([
            'approve' => 'required|boolean',
            'reason' => 'required_if:approve,false|string|max:500',
        ]);
    
        $user = User::findOrFail($userId);
    
        if ($request->approve) {
            $user->is_validated = true;
            $user->status = 'delivery_person' ;
    
            // Envoi de l'email pour approbation
            Mail::to($user->email)->send(new \App\Notifications\RequestApprovedMail($user, "livreur"));
            $user->save();
            return response()->json([
                'message' => 'User approved as delivery person. Email sent.'
            ]);
        } else {
            $user->is_validated = true;
            $user->is_delivery_request = false ;
    
            // Envoi de l'email pour rejet
            Mail::to($user->email)->send(new \App\Notifications\RequestRejectedMail($user, $request->reason, 'livreur'));
            $user->save();

            return response()->json([
                'message' => 'User request rejected. Email sent.'
            ]);
        }
     }

    /**
     * Approuver un vendeur
     */

    public function approveRequestSaler($userId, Request $request)
    {
        $request->validate([
            'approve' => 'required|boolean',
            'reason' => 'required_if:approve,false|string|max:500',
        ]);
    
        $user = User::findOrFail($userId);
    
        if ($request->approve) {
            $user->is_validated = true;
            $user->status = 'seller' ;
    
            // Envoi de l'email pour approbation
            Mail::to($user->email)->send(new \App\Notifications\RequestApprovedMail($user, "vendeur"));
            $user->save();
            return response()->json([
                'message' => 'User approved as seller person. Email sent.'
            ]);
        } else {
            $user->is_validated = true;
            $user->is_saler_request = false ;
    
            // Envoi de l'email pour rejet
            Mail::to($user->email)->send(new \App\Notifications\RequestRejectedMail($user, $request->reason, 'vendeur'));
            $user->save();

            return response()->json([
                'message' => 'User request rejected. Email sent.'
            ]);
        }
    }


    /**
     * Mise à jour d'un utilisateur
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user = $this->userService->updateUser($id, $validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Suppression d'un utilisateur
     */
    public function destroy($id)
    {
        $this->userService->deleteUser($id);

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggle($id)
    {
        $user = $this->userService->toggleUserStatus($id);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user,
        ]);
    }

}
