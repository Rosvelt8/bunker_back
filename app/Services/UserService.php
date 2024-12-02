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
        return User::paginate($perPage);
    }

    /**
     * Obtenir les détails d'un utilisateur
     */
    public function getUserById($id)
    {
        return User::findOrFail($id);
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
        $user->is_active = !$user->is_active;
        $user->save();
        return $user;
    }
}
