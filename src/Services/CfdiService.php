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

    /**
     * Verify if one or more CFDI exist in the Database of SunPlusXtra, returns a map for each UUID indicating if it exists or not
     *
     * @param string $rfc
     * @param array $uuidList
     * @return array
     */
    public function verify(string $rfc, array $uuidList): array
    {
        if (empty($rfc) || empty($uuidList)) {
            return [];
        }

        try {
            return $this->request('post', 'verifica-UUID', [
                'rfc' => $rfc,
                'uuidList' => $uuidList,
            ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al verificar los comprobantes', [
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
     * Link or unlink CFDI to a journal entry.
     *
     * @param int $journalNumber
     * @param string $rfc
     * @param array $uuidList
     * @param array $extensionFiles
     * @param array $amounts
     * @param int|null $line
     * @param bool $unlink
     * @return bool
     */
    public function link(int $journalNumber, string $rfc, array $uuidList, array $extensionFiles, array $amounts, ?int $line, bool $unlink = false): bool
    {
        try {
            return $this->request('post', 'vincula-comprobante', [
                'journalNumber' => $journalNumber,
                'journalLine' => $line,
                'rfc' => $rfc,
                'comprobantes' => $uuidList,
                'extensionFiles' => $extensionFiles,
                'importeAVincular' => $amounts,
                'desvincular' => $unlink,
            ])
                ->throw()
                ->ok();
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $e->response?->body();

            Log::error('Error al ligar los comprobantes', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
        }

        return false;
    }

    /**
     * Link or unlink multiple CFDIs to a journal entry.
     *
     * @param int $journalNumber
     * @param string $rfc
     * @param array $lines
     * @param bool $unlink
     * @return bool
     */
    public function multiLink(int $journalNumber, string $rfc, array $lines, bool $unlink = false): bool
    {
        try {
            return $this->request('post', 'vincula-comprobante-lineas', [
                'journalNumber' => $journalNumber,
                'rfc' => $rfc,
                'requestDataVinculaComprobantes' => $lines,
                'desvincular' => $unlink,
            ])
                ->throw()
                ->ok();
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $e->response?->body();

            Log::error('Error al ligar los comprobantes', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
        }

        return false;
    }

    /**
     * Upload a single or multiple CFDI XML files to SunPlusXtra.
     *
     * @param string $rfc
     * @param array $xmlFiles
     * @return array
     */
    public function upload(string $rfc, array $xmlFiles): array
    {
        try {
            return $this->request('post', 'upload-comprobantes', [
                'rfc' => $rfc,
                'comprobantes' => $xmlFiles,
            ])
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al subir los comprobantes a SunPlusXtra', [
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
     * Get the XML of a stamped CFDI by its UUID.
     *
     * @param string $uuid
     * @return string|null
     */
    public function getXml(string $uuid): ?string
    {
        if (empty($uuid)) {
            return null;
        }

        try {
            return $this->request('get', 'comprobante', [
                'uuid' => $uuid,
            ])
                ->throw()
                ->json('response', null);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al obtener el XML del comprobante', [
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