<?php

namespace Unav\SpxConnect\Tests\Security;

use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\CfdiService;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class InputValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::setToken('test-token');
    }

    public function testCfdiServiceRejectsEmptyXml(): void
    {
        $cfdiService = new CfdiService();

        $result = $cfdiService->stamp('', 'test@email.com');

        $this->assertNull($result);
    }

    public function testCfdiServiceRejectsEmptyRfcForVerify(): void
    {
        $cfdiService = new CfdiService();

        $result = $cfdiService->verify('', ['uuid-1']);

        $this->assertEquals([], $result);
    }

    public function testCfdiServiceRejectsEmptyUuidListForVerify(): void
    {
        $cfdiService = new CfdiService();

        $result = $cfdiService->verify('RFC123', []);

        $this->assertEquals([], $result);
    }

    public function testCfdiServiceRejectsEmptyUuidForGetXml(): void
    {
        $cfdiService = new CfdiService();

        $result = $cfdiService->getXml('');

        $this->assertNull($result);
    }

    public function testClientServiceHandlesSpecialCharactersInSearch(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $clientService = new ClientService();

        // These should not cause errors
        $result1 = $clientService->search("'; DROP TABLE clients; --");
        $result2 = $clientService->search('<script>alert("xss")</script>');
        $result3 = $clientService->search('test%00null');

        $this->assertEquals([], $result1);
        $this->assertEquals([], $result2);
        $this->assertEquals([], $result3);
    }

    public function testProductServiceHandlesSpecialCharactersInSearch(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $productService = new ProductService();

        // These should not cause errors
        $result = $productService->search('<img src=x onerror=alert(1)>');

        $this->assertEquals([], $result);
    }

    public function testCfdiServiceHandlesXmlWithMaliciousContent(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => null,
            ], 400),
        ]);

        $cfdiService = new CfdiService();

        // Attempt XXE attack - this should be handled by the API
        $maliciousXml = '<?xml version="1.0"?><!DOCTYPE foo [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><cfdi>&xxe;</cfdi>';

        // The service should not crash and should handle this gracefully
        $result = $cfdiService->stamp($maliciousXml, 'test@email.com');

        // We expect null or the API's rejection
        $this->assertNull($result);
    }

    public function testTokenManagerHandlesLongUserIds(): void
    {
        $longUserId = str_repeat('a', 1000);

        // Should not throw an exception
        TokenManager::setToken('token', userId: $longUserId);
        $token = TokenManager::getToken($longUserId);

        $this->assertEquals('token', $token);
    }

    public function testTokenManagerHandlesSpecialCharactersInUserId(): void
    {
        $specialUserId = 'user<>&"\'/\\';

        // Should not throw an exception
        TokenManager::setToken('special-token', userId: $specialUserId);
        $token = TokenManager::getToken($specialUserId);

        $this->assertEquals('special-token', $token);
    }

    public function testCfdiServiceHandlesArrayEmailsCorrectly(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'response' => 'stamped',
            ], 200),
        ]);

        $cfdiService = new CfdiService();

        // Array with potential injection attempts
        $emails = ['test@test.com', 'test2@test.com; rm -rf /', '<script>'];

        // Should not crash
        $result = $cfdiService->stamp('<xml>test</xml>', $emails);

        // The emails should be joined as a string
        Http::assertSent(function ($request) {
            $emailsSending = $request['emailsSending'];
            return is_string($emailsSending);
        });
    }

    public function testCfdiServiceHandlesStringEmailWithSpaces(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'response' => 'stamped',
            ], 200),
        ]);

        $cfdiService = new CfdiService();

        // Email with leading/trailing spaces
        $email = '   test@test.com   ';

        $cfdiService->stamp('<xml>test</xml>', $email);

        Http::assertSent(function ($request) {
            return $request['emailsSending'] === 'test@test.com';
        });
    }

    public function testCfdiLinkHandlesNullLine(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/vincula-comprobante' => Http::response([], 200),
        ]);

        $cfdiService = new CfdiService();

        // Should not crash with null line
        $result = $cfdiService->link(
            journalNumber: 123,
            rfc: 'RFC123',
            uuidList: ['uuid'],
            extensionFiles: ['xml'],
            amounts: [100],
            line: null
        );

        $this->assertTrue($result);
    }

    public function testAuthServiceHandlesLongCredentials(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'token',
            ], 200),
        ]);

        $authService = new \Unav\SpxConnect\Services\AuthService();

        // Very long credentials
        $longUsername = str_repeat('a', 10000);
        $longPassword = str_repeat('b', 10000);
        $longEmail = str_repeat('c', 1000) . '@test.com';

        // Should not throw an exception
        $result = $authService->login($longUsername, $longPassword, $longEmail);

        $this->assertTrue($result);
    }

    public function testServicesHandleNullTokenGracefully(): void
    {
        // Clear all tokens
        TokenManager::clearToken();

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $clientService = new ClientService();

        // Should not crash even without a token
        $result = $clientService->search('test');

        $this->assertEquals([], $result);
    }
}
