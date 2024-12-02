<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;


class AuthService
{
    /**
     * Inscription d'un nouvel utilisateur
     *
     * @param array $data
     * @return User
     */
    public function register(array $data)

    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Vérification des identifiants pour la connexion
     *
     * @param array $data
     * @return User|null
     */
    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if ($user && Hash::check($data['password'], $user->password)) {
            return $user;
        }

        return null;
    }

    public function sendPasswordResetLink(array $data)
    {
        return Password::sendResetLink($data);
    }

    /**
     * Réinitialisation du mot de passe
     */
    public function resetPassword(array $data)
    {
        return Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        });
    }
}
