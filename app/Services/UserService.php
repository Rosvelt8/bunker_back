<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Récupérer la liste des utilisateurs avec pagination
     */
    public function listUsers($perPage = 10)
    {
        return User::with(['documents', 'city'])
        ->where('id', '!=', auth()->user()->id)
        ->where(function ($query) {
            $query->where('is_saler_request', true)
                  ->orWhere('is_delivery_request', true);
        })
        ->where('status', 'customer')
        ->get();
    }

    /**
     * Obtenir les détails d'un utilisateur
     */
    public function getUserById($id)
    {
        return User::with(['documents', 'cities'])->find($id);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser($id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return true;
    }

    /**
     * Activer ou désactiver un utilisateur
     */
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_validated = !$user->is_validated;
        $user->save();
        return $user;
    }
}
