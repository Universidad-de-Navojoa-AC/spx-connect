<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class ProductServiceTest extends TestCase
{
    protected ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::setToken('test-token');
        $this->productService = new ProductService();
    }

    public function testSearchReturnsProducts(): void
    {
        $expectedProducts = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/products*' => Http::response([
                'response' => $expectedProducts,
            ], 200),
        ]);

        $result = $this->productService->search('Product');

        $this->assertEquals($expectedProducts, $result);
    }

    public function testSearchSendsCorrectPayload(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/products*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->productService->search('Test Product');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'search=Test+Product')
                || str_contains($request->url(), 'search=Test%20Product');
        });
    }

    public function testSearchReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/products*' => Http::response([
                'error' => 'Internal Server Error',
            ], 500),
        ]);

        $result = $this->productService->search('Test');

        $this->assertEquals([], $result);
    }

    public function testSearchReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/products*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->productService->search('Test');

        $this->assertEquals([], $result);
    }

    public function testSearchIncludesAuthToken(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/products*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->productService->search('Test');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function testSetUserIdChangesContext(): void
    {
        TokenManager::setToken('user-specific-token', userId: 'user-123');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/products*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->productService->setUserId('user-123');
        $this->productService->search('Test');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer user-specific-token');
        });
    }

    public function testSearchReturnsEmptyArrayWhenResponseKeyMissing(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/products*' => Http::response([
                'data' => 'wrong key',
            ], 200),
        ]);

        $result = $this->productService->search('Test');

        $this->assertEquals([], $result);
    }
}
