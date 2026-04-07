<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Enums\JournalFileType;
use Unav\SpxConnect\Services\JournalService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class JournalServiceTest extends TestCase
{
    protected JournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::setToken('test-token');
        $this->journalService = new JournalService();
    }

    public function testGetJournalTypesReturnsTypes(): void
    {
        $expectedTypes = [
            ['code' => 'PGP', 'name' => 'Póliza de Gobierno'],
            ['code' => 'PR', 'name' => 'Recibo'],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/type-list' => Http::response([
                'response' => $expectedTypes,
            ], 200),
        ]);

        $result = $this->journalService->getJournalTypes();

        $this->assertEquals($expectedTypes, $result);
    }

    public function testGetJournalTypesReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/type-list' => Http::response([
                'error' => 'Server error',
            ], 500),
        ]);

        $result = $this->journalService->getJournalTypes();

        $this->assertEquals([], $result);
    }

    public function testPostJournalEntrySuccess(): void
    {
        $payload = [
            'date' => '2024-01-15',
            'description' => 'Test journal entry',
            'lines' => [
                ['account' => '1001', 'debit' => 1000, 'credit' => 0],
                ['account' => '2001', 'debit' => 0, 'credit' => 1000],
            ],
        ];

        $expectedResponse = ['journalNumber' => 12345];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal' => Http::response([
                'response' => $expectedResponse,
            ], 200),
        ]);

        $result = $this->journalService->postJournalEntry($payload);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testPostJournalEntrySendsCorrectPayload(): void
    {
        $payload = [
            'date' => '2024-01-15',
            'description' => 'Test entry',
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->journalService->postJournalEntry($payload);

        Http::assertSent(function (Request $request) use ($payload) {
            return $request->method() === 'POST'
                && $request['date'] === $payload['date']
                && $request['description'] === $payload['description'];
        });
    }

    public function testPostJournalEntryReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal' => Http::response([
                'error' => 'Validation error',
            ], 422),
        ]);

        $result = $this->journalService->postJournalEntry(['invalid' => 'data']);

        $this->assertEquals([], $result);
    }

    public function testDownloadJournalFileSuccess(): void
    {
        $expectedFileContent = base64_encode('PDF content here');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/print' => Http::response([
                'response' => $expectedFileContent,
            ], 200),
        ]);

        $result = $this->journalService->downloadJournalFile(
            journalNumber: 12345,
            book: 'BOOK1',
            journalFileType: JournalFileType::PGP
        );

        $this->assertEquals($expectedFileContent, $result);
    }

    public function testDownloadJournalFileWithLogo(): void
    {
        $logo = 'https://example.com/logo.png';

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/print' => Http::response([
                'response' => 'file-content',
            ], 200),
        ]);

        $this->journalService->downloadJournalFile(
            journalNumber: 123,
            book: 'BOOK1',
            journalFileType: JournalFileType::PR,
            logo: $logo
        );

        Http::assertSent(function (Request $request) use ($logo) {
            return $request['logoOption']['logoSource'] === $logo;
        });
    }

    public function testDownloadJournalFileWithoutLogo(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/print' => Http::response([
                'response' => 'file-content',
            ], 200),
        ]);

        $this->journalService->downloadJournalFile(
            journalNumber: 123,
            book: 'BOOK1',
            journalFileType: JournalFileType::PC
        );

        Http::assertSent(function (Request $request) {
            return !isset($request['logoOption']);
        });
    }

    public function testDownloadJournalFileReturnsNullOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/print' => Http::response([
                'error' => 'File not found',
            ], 404),
        ]);

        $result = $this->journalService->downloadJournalFile(
            journalNumber: 99999,
            book: 'BOOK1',
            journalFileType: JournalFileType::PGP
        );

        $this->assertNull($result);
    }

    public function testGetJournalTypesReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/type-list' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->journalService->getJournalTypes();

        $this->assertEquals([], $result);
    }

    public function testPostJournalEntryReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->journalService->postJournalEntry(['test' => 'data']);

        $this->assertEquals([], $result);
    }

    public function testDownloadJournalFileReturnsNullOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/print' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->journalService->downloadJournalFile(
            journalNumber: 123,
            book: 'BOOK1',
            journalFileType: JournalFileType::PGP
        );

        $this->assertNull($result);
    }

    public function testSetUserIdChangesContext(): void
    {
        TokenManager::setToken('user-token', userId: 'user-abc');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/journal/type-list' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->journalService->setUserId('user-abc');
        $this->journalService->getJournalTypes();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer user-token');
        });
    }
}
