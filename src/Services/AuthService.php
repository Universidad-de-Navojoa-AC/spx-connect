<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected string $baseUrl;

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

    public function hasValidToken(): bool
    {
        $token = TokenManager::getToken();

        try {
            $response = Http::withToken($token)
                ->get("$this->baseUrl/company")
                ->throw();

            switch ($response->status()) {
                case 200:
                    return true;
                case 220:
                    Log::error('Token inválido', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    break;
                default:
                    Log::error('Error al validar el token', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    break;
            }
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al validar el token', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
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
