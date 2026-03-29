<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Unav\SpxConnect\BaseApiService;
use Unav\SpxConnect\Enums\DimensionType;
use Unav\SpxConnect\Contracts\SunPlusDimensionServiceInterface;

class SunPlusDimensionService extends BaseApiService implements SunPlusDimensionServiceInterface
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

    /**
     * Create a new dimension on Sunplus
     *
     * @param DimensionType $dimension
     * @param string $code
     * @param string $lookup
     * @param string $name
     * @param int|null $prohibitPosting
     * @return array
     * @throws LockTimeoutException
     */
    public function create(DimensionType $dimension, string $code, string $lookup, string $name, ?int $prohibitPosting = 0): array
    {
        try {
            return $this->request('post', 'dimension', [
                'catId' => $dimension->value,
                'code' => $code,
                'lookup' => $lookup,
                'name' => $name,
                'prohibitPosting' => $prohibitPosting,
            ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al crear la dimensión en SunPlus', [
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
