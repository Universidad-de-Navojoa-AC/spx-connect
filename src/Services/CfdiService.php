<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Unav\SpxConnect\BaseApiService;

class CfdiService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Method for stamping an invoice
     *
     * @param string $xml
     * @param array|string $emailsSending
     * @param bool $useEmailTemplate
     * @param string|null $htmlEmailOptional
     * @param bool $generateFolio
     * @return string|null
     */
    public function stamp(string $xml, array|string $emailsSending, bool $useEmailTemplate = false, string $htmlEmailOptional = null, bool $generateFolio = true): ?string
    {
        if (empty($xml)) {
            return null;
        }

        if (!empty($emailsSending) && is_array($emailsSending)) {
            $emailsSending = implode(',', $emailsSending);
        } else if (is_string($emailsSending)) {
            $emailsSending = trim($emailsSending);
        } else {
            $emailsSending = '';
        }

        try {
            return $this->request('post', 'timbra-comprobante', [
                'comprobante' => $xml,
                'emailsSending' => $emailsSending,
                'useEmailTemplate' => $useEmailTemplate,
                'htmlEmailOptional' => $htmlEmailOptional,
                'generateFolio' => $generateFolio,
            ])
                ->throw()
                ->json('response', null);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al timbrar el comprobante', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
        }

        return null;
    }
}