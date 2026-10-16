<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
        ]);

        return $this->issueToken($user);
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new DomainException('Invalid credentials.', 401);
        }

        return $this->issueToken($user);
    }

    private function issueToken(User $user): array
    {
        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }
}
