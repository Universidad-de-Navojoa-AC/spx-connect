<?php

namespace Unav\SpxConnect;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\TokenManager;

class BaseApiService
{
    protected string $baseUrl;
    protected AuthService $auth;
    protected string $userId = 'global';

    public function __construct()
    {
        $this->baseUrl = 'https://api.sunplusxtra.mx/api/spxtra';
        $this->auth = new AuthService();
    }

    /**
     * @throws LockTimeoutException
     */
    public function request(string $method, string $endpoint, array $data = [], array $headers = [])
    {
        $client = fn() => Http::withToken(TokenManager::getToken($this->userId))
            ->withHeaders($headers)
            ->connectTimeout(10)
            ->timeout(90);

        $response = $client->$method("$this->baseUrl/$endpoint", $data);

        if ($this->isAuthFailure($response)) {
            Cache::lock("token-refresh-{$this->userId}", 10)->block(5, function () {
                $this->auth->refreshAccessToken();
            });

            $response = $client()->$method("$this->baseUrl/$endpoint", $data);
        }

        return $response;
    }

    public function get(string $endpoint, array $data = [], array $headers = [])
    {
        return $this->request('get', $endpoint, $data, $headers);
    }

    public function setUserId(?string $userId = 'global'): BaseApiService
    {
        $this->userId = $userId;
        return $this;
    }

    private function isAuthFailure($response): bool
    {
        if ($response->status() === 401) {
            return true;
        }

        if ($response->status() === 404) {
            $message = data_get($response->json(), 'message');
            return $message === 'Unauthenticated.';
        }

        return false;
    }
}