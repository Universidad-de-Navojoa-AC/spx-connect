<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Unav\SpxConnect\Services\CacheManager;
use Unav\SpxConnect\Tests\TestCase;

class CacheManagerTest extends TestCase
{
    protected CacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->cacheManager = new CacheManager();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createMockUser(int|string $id): Authenticatable
    {
        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        return $user;
    }

    public function testPutAndGetWithoutUser(): void
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->cacheManager->put($key, $value);

        $this->assertEquals($value, $this->cacheManager->get($key));
    }

    public function testPutAndGetWithUser(): void
    {
        $key = 'user_key';
        $value = 'user_value';
        $user = $this->createMockUser(123);

        $this->cacheManager->put($key, $value, $user);

        $this->assertEquals($value, $this->cacheManager->get($key, $user));
    }

    public function testGetReturnsNullWhenKeyDoesNotExist(): void
    {
        $this->assertNull($this->cacheManager->get('nonexistent_key'));
    }

    public function testGetReturnsNullWhenKeyDoesNotExistForUser(): void
    {
        $user = $this->createMockUser(456);
        $this->assertNull($this->cacheManager->get('nonexistent_key', $user));
    }

    public function testForgetWithoutUser(): void
    {
        $key = 'forget_key';
        $this->cacheManager->put($key, 'value');

        $this->assertNotNull($this->cacheManager->get($key));

        $this->cacheManager->forget($key);

        $this->assertNull($this->cacheManager->get($key));
    }

    public function testForgetWithUser(): void
    {
        $key = 'user_forget_key';
        $user = $this->createMockUser(789);

        $this->cacheManager->put($key, 'user_value', $user);
        $this->assertNotNull($this->cacheManager->get($key, $user));

        $this->cacheManager->forget($key, $user);
        $this->assertNull($this->cacheManager->get($key, $user));
    }

    public function testDifferentUsersHaveSeparateCache(): void
    {
        $key = 'shared_key';
        $user1 = $this->createMockUser(1);
        $user2 = $this->createMockUser(2);

        $this->cacheManager->put($key, 'value_for_user_1', $user1);
        $this->cacheManager->put($key, 'value_for_user_2', $user2);

        $this->assertEquals('value_for_user_1', $this->cacheManager->get($key, $user1));
        $this->assertEquals('value_for_user_2', $this->cacheManager->get($key, $user2));
    }

    public function testPutWithCustomTtl(): void
    {
        $key = 'ttl_key';
        $value = 'ttl_value';
        $ttl = 7200;

        // Use the actual cache manager and verify it works
        $this->cacheManager->put($key, $value, null, $ttl);
        $result = $this->cacheManager->get($key);

        $this->assertEquals($value, $result);
    }

    public function testPutComplexData(): void
    {
        $key = 'complex_key';
        $value = ['name' => 'Test', 'items' => [1, 2, 3], 'nested' => ['a' => 'b']];

        $this->cacheManager->put($key, $value);

        $retrieved = $this->cacheManager->get($key);
        $this->assertEquals($value, $retrieved);
    }

    public function testUserCacheKeyFormat(): void
    {
        $key = 'format_key';
        $value = 'format_value';
        $userId = 'user-abc-123';
        $user = $this->createMockUser($userId);

        // Store the value and verify it can be retrieved with the user
        $this->cacheManager->put($key, $value, $user);
        $retrievedValue = $this->cacheManager->get($key, $user);

        $this->assertEquals($value, $retrievedValue);
    }
}
