<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;


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
    public function becomeDeliver(Request $request){
        $request->validate([
            'documents.*.document_type' => 'required|string|in:id_card, location_map, tax_identifier',
            'documents.*.file' => 'required|file|max:2048'
        ]);

        $user = $request->user();

        // Mettre à jour le statut de requête
        $user->update(['is_delivery_request' => true]);

        foreach ($request->documents as $doc) {
            $filePath = $doc['file']->store('documents');

            Document::create([
                'user_id' => $user->id,
                'document_type' => $doc['document_type'],
                'file_path' => $filePath,
                'status' => false,
            ]);
        }

        return response()->json([
            'message' => 'Request submitted successfully. Awaiting approval.'
        ]);
    }

    /**
     * Devenir vendeur
     */
    public function becomeSaler(Request $request){
        $request->validate([
            'documents.*.document_type' => 'required|string|in:id_card, location_map, tax_identifier',
            'documents.*.file' => 'required|file|max:2048'
        ]);

        $user = $request->user();

        // Mettre à jour le statut de requête
        $user->update(['is_saler_request' => true]);

        foreach ($request->documents as $doc) {
            $filePath = $doc['file']->store('documents');

            Document::create([
                'user_id' => $user->id,
                'document_type' => $doc['document_type'],
                'file_path' => $filePath,
                'status' => false,
            ]);
        }

        return response()->json([
            'message' => 'Request submitted successfully. Awaiting approval.'
        ]);
    }

    /**
     * Visualiser toutes les requêtes
     */
    public function listRequests()
    {
        $requests = User::where('is_delivery_request', true)
                        ->orWhere('is_saler_request', true)
                        ->with('documents')
                        ->get();

        return response()->json($requests);
    }

    /**
     * Approuver un livreur
     */

    public function approveRequestDeliver($userId, Request $request)
    {
        $request->validate([
            'approve' => 'required|boolean',
        ]);

        $user = User::findOrFail($userId);

        if ($request->approve) {
            $user->update([
                'is_delivery_request' => false,
                'status' => 'deliver'
            ]);

            return response()->json([
                'message' => 'User approved as delivery person.'
            ]);
        } else {
            $user->update(['is_delivery_request' => false]);

            return response()->json([
                'message' => 'User request rejected.'
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
        ]);

        $user = User::findOrFail($userId);

        if ($request->approve) {
            $user->update([
                'is_saler_request' => false,
                'status' => 'saler'
            ]);

            return response()->json([
                'message' => 'User approved as saler person.'
            ]);
        } else {
            $user->update(['is_saler_request' => false]);

            return response()->json([
                'message' => 'User request rejected.'
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
