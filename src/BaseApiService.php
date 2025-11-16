<?php

namespace Unav\SpxConnect;

use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\TokenManager;

class BaseApiService
{
    protected string $baseUrl;
    protected AuthService $auth;
    protected string $userId = 'default';

    public function __construct()
    {
        $this->baseUrl = 'https://api.sunplusxtra.mx/api/spxtra';
        $this->auth = new AuthService();
    }

    public function request(string $method, string $endpoint, array $data = [], array $headers = [])
    {
        $response = Http::withToken(TokenManager::getToken($this->userId))
            ->withHeaders($headers)
            ->$method("$this->baseUrl/$endpoint", $data);

        if ($response->status() === 401 && $this->auth->refreshAccessToken()) {
            $response = Http::withToken(TokenManager::getToken($this->userId))
                ->$method("$this->baseUrl/$endpoint", $data);
        }

        return $response;
    }

    public function get(string $endpoint, array $data = [], array $headers = [])
    {
        return $this->request('get', $endpoint, $data, $headers);
    }

    public function setUserId(string $userId): BaseApiService
    {
        $this->userId = $userId;
        return $this;
    }
}