<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class EducationLevelServiceTest extends TestCase
{
    protected EducationLevelService $educationLevelService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::setToken('test-token');
        $this->educationLevelService = new EducationLevelService();
    }

    public function testGetAllReturnsEducationLevels(): void
    {
        $expectedLevels = [
            ['id' => 1, 'name' => 'Preparatoria'],
            ['id' => 2, 'name' => 'Licenciatura'],
            ['id' => 3, 'name' => 'Maestría'],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/education-levels/all' => Http::response([
                'response' => $expectedLevels,
            ], 200),
        ]);

        $result = $this->educationLevelService->getAll();

        $this->assertEquals($expectedLevels, $result);
    }

    public function testGetAllCallsCorrectEndpoint(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/education-levels/all' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->educationLevelService->getAll();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.sunplusxtra.mx/api/spxtra/education-levels/all';
        });
    }

    public function testGetAllReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/education-levels/all' => Http::response([
                'error' => 'Server error',
            ], 500),
        ]);

        $result = $this->educationLevelService->getAll();

        $this->assertEquals([], $result);
    }

    public function testGetAllReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/education-levels/all' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->educationLevelService->getAll();

        $this->assertEquals([], $result);
    }

    public function testGetAllIncludesAuthToken(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/education-levels/all' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->educationLevelService->getAll();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function testGetAllReturnsEmptyArrayWhenResponseKeyMissing(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/education-levels/all' => Http::response([
                'data' => 'wrong key',
            ], 200),
        ]);

        $result = $this->educationLevelService->getAll();

        $this->assertEquals([], $result);
    }
}
