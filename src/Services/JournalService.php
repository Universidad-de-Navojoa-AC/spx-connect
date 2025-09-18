<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Unav\SpxConnect\BaseApiService;
use Unav\SpxConnect\Enums\JournalFileType;

class JournalService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function postJournalEntry(array $payload): array
    {
        try {
            return $this->request('post', 'journal', $payload)
                ->throw()
                ->json('response', []);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al contabilizar el diario', [
                'status' => $status,
                'body' => $body,
                'ex' => $e,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Conexión fallida a SunPlusXtra', ['ex' => $e]);
        }

        return [];
    }

    public function downloadJournalFile(int $journalNumber, string $book, JournalFileType $journalFileType, ?string $logo): ?string
    {
        try {
            $payload = empty($logo) ? [
                'journalNumber' => $journalNumber,
                'book' => $book,
                'fileType' => $journalFileType->value,
            ] : [
                'journalNumber' => $journalNumber,
                'book' => $book,
                'fileType' => $journalFileType->value,
                'logoOption' => [
                    'logoSource' => $logo
                ],
            ];

            return $this->request('post', 'journal/print', $payload)
                ->throw()
                ->json('response.response', null);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();

            Log::error('Error al descargar el archivo del diario', [
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