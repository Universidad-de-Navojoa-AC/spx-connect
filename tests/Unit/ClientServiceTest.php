<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class ClientServiceTest extends TestCase
{
    protected ClientService $clientService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::setToken('test-token');
        $this->clientService = new ClientService();
    }

    public function testSearchReturnsClients(): void
    {
        $expectedClients = [
            ['id' => 1, 'name' => 'Client A'],
            ['id' => 2, 'name' => 'Client B'],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/clients*' => Http::response([
                'response' => $expectedClients,
            ], 200),
        ]);

        $result = $this->clientService->search('Client');

        $this->assertEquals($expectedClients, $result);
    }

    public function testSearchSendsCorrectQuery(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/clients*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->clientService->search('Test Query');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'search=Test+Query')
                || str_contains($request->url(), 'search=Test%20Query');
        });
    }

    public function testSearchReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/clients*' => Http::response([
                'error' => 'Server error',
            ], 500),
        ]);

        $result = $this->clientService->search('Query');

        $this->assertEquals([], $result);
    }

    public function testSearchReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/clients*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->clientService->search('Query');

        $this->assertEquals([], $result);
    }

    public function testSearchIncludesAuthToken(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/clients*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->clientService->search('Test');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function testSearchWithEmptyQuery(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/clients*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $result = $this->clientService->search('');

        $this->assertEquals([], $result);
    }

    public function testSearchWithSpecialCharacters(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/clients*' => Http::response([
                'response' => [['id' => 1, 'name' => 'Client & Co.']],
            ], 200),
        ]);

        $result = $this->clientService->search('Client & Co.');

        $this->assertNotEmpty($result);
    }
}
