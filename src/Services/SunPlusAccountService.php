<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Support\Facades\Http;

class SunPlusAccountService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://api.sunplusxtra.mx/api/spxtra';
    }

    public function getAll(): array
    {
        $response = Http::withToken(TokenManager::getToken())
            ->get("$this->baseUrl/account-list");

        return $response->successful() ? $response->json() : [];
    }

    public function findByCode(string $accountCode): array
    {
        $response = Http::withToken(TokenManager::getToken())
            ->get("$this->baseUrl/account-list", [
                'part' => $accountCode,
                'code' => 1,
            ]);

        return $response->successful() ? $response->json() : [];
    }

    public function search(string $query): array
    {
        $response = Http::withToken(TokenManager::getToken())
            ->get("$this->baseUrl/account-list", [
                'part' => $query,
                'code' => 0,
            ]);

        return $response->successful() ? $response->json() : [];
    }
}