<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Unav\SpxConnect\Enums\DimensionType;

class SunPlusDimensionService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://api.sunplusxtra.mx/api/spxtra';
    }

    public function find(DimensionType $dimension): array
    {
        try {
            return Http::withToken(TokenManager::getToken())
                ->get("$this->baseUrl/dimension", [
                    'catID' => $dimension->value,
                ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al obtener la lista de dimensiones de SunPlus', [
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
