<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Unav\SpxConnect\BaseApiService;

class SunPlusAccountService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll(): array
    {
        try {
            return $this->request('get', 'account-list')
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al obtener la lista de cuentas de SunPlus', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
        }

        return [];
    }

    public function findByCode(string $accountCode): array
    {
        try {
            return $this->request('get', 'account-list', [
                'part' => $accountCode,
                'code' => 1,
            ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al buscar cuenta de SunPlus por código', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
        }

        return [];
    }

    public function search(string $query): array
    {
        try {
            return $this->request('get', 'account-list', [
                'part' => $query,
                'code' => 0,
            ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al buscar cuenta de SunPlus por nombre', [
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