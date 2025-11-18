<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Unav\SpxConnect\BaseApiService;
use Unav\SpxConnect\Enums\DimensionType;

class SunPlusDimensionService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function find(DimensionType $dimension): array
    {
        try {
            return $this->request('get', 'dimension', [
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
