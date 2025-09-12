<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://api.sunplusxtra.mx/api/spxtra';
    }

    public function search(string $query): array
    {
        try {
            return Http::withToken(TokenManager::getToken())
                ->get("$this->baseUrl/clientes", [
                    'search' => $query
                ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al buscar clientes', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
        }

        return [];
    }
}