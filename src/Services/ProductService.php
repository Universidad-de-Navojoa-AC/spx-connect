<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Unav\SpxConnect\BaseApiService;

class ProductService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function search(string $query): array
    {
        try {
            return $this->request('get', 'products', [
                'search' => $query
            ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al buscar producto', [
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