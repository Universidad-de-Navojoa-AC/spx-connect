<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Support\Facades\Http;

class AuthService
{
    protected string $baseUrl ;

    public function __construct()
    {
        $this->baseUrl = 'https://api.sunplusxtra.mx/api/spxtra';
    }

    public function login(string $username, string $password, string $email): bool
    {
        $response = Http::post("$this->baseUrl/login", [
            'idusuario' => $username,
            'password' => $password,
            'email' => $email,
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'] ?? null;

            if ($token) {
                TokenManager::setToken($token);
                TokenManager::setCredentials($username, $password, $email);
                return true;
            }
        }

        return false;
    }

    public function refreshAccessToken(): bool
    {
        $credentials = TokenManager::getCredentials();

        if ($credentials) {
            return $this->login($credentials['username'], $credentials['password'], $credentials['email']);
        }

        return false;
    }
}
