<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Enums\DimensionType;
use Unav\SpxConnect\Services\SunPlusDimensionService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class SunPlusDimensionServiceTest extends TestCase
{
    protected SunPlusDimensionService $dimensionService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::setToken('test-token');
        $this->dimensionService = new SunPlusDimensionService();
    }

    public function testFindReturnsDimensions(): void
    {
        $expectedDimensions = [
            ['id' => '01-001', 'name' => 'Resource 1'],
            ['id' => '01-002', 'name' => 'Resource 2'],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => Http::response([
                'response' => $expectedDimensions,
            ], 200),
        ]);

        $result = $this->dimensionService->find(DimensionType::RESOURCE);

        $this->assertEquals($expectedDimensions, $result);
    }

    public function testFindSendsCorrectCatId(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->dimensionService->find(DimensionType::FUND);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'catID=03');
        });
    }

    public function testFindWithDifferentDimensionTypes(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $dimensionTypes = [
            [DimensionType::RESOURCE, '01'],
            [DimensionType::TFWW, '02'],
            [DimensionType::FUND, '03'],
            [DimensionType::FUNCTION, '04'],
            [DimensionType::RESTRICTION, '05'],
            [DimensionType::ORGID, '06'],
            [DimensionType::WHO, '07'],
            [DimensionType::FLAG, '08'],
            [DimensionType::PROJECT, '09'],
            [DimensionType::DETAIL, '10'],
        ];

        foreach ($dimensionTypes as [$type, $expectedValue]) {
            $this->dimensionService->find($type);

            Http::assertSent(function (Request $request) use ($expectedValue) {
                return str_contains($request->url(), 'catID=' . $expectedValue);
            });
        }
    }

    public function testFindReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => Http::response([
                'error' => 'Server error',
            ], 500),
        ]);

        $result = $this->dimensionService->find(DimensionType::PROJECT);

        $this->assertEquals([], $result);
    }

    public function testFindReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->dimensionService->find(DimensionType::FLAG);

        $this->assertEquals([], $result);
    }

    public function testFindIncludesAuthToken(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->dimensionService->find(DimensionType::WHO);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function testSetUserIdChangesContext(): void
    {
        TokenManager::setToken('user-token', userId: 'user-xyz');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->dimensionService->setUserId('user-xyz');
        $this->dimensionService->find(DimensionType::ORGID);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer user-token');
        });
    }

    public function testFindReturnsEmptyArrayWhenResponseKeyMissing(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/dimension*' => Http::response([
                'data' => 'wrong key',
            ], 200),
        ]);

        $result = $this->dimensionService->find(DimensionType::DETAIL);

        $this->assertEquals([], $result);
    }
}
