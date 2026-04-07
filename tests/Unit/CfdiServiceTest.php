<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\CfdiService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class CfdiServiceTest extends TestCase
{
    protected CfdiService $cfdiService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::setToken('test-token');
        $this->cfdiService = new CfdiService();
    }

    // Stamp Tests
    public function testStampSuccess(): void
    {
        $xml = '<cfdi:Comprobante>...</cfdi:Comprobante>';
        $expectedStampedXml = '<cfdi:Comprobante><tfd:TimbreFiscalDigital>...</tfd:TimbreFiscalDigital></cfdi:Comprobante>';

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'response' => $expectedStampedXml,
            ], 200),
        ]);

        $result = $this->cfdiService->stamp($xml, 'test@example.com');

        $this->assertEquals($expectedStampedXml, $result);
    }

    public function testStampWithArrayEmails(): void
    {
        $xml = '<cfdi:Comprobante>...</cfdi:Comprobante>';
        $emails = ['email1@test.com', 'email2@test.com'];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'response' => 'stamped-xml',
            ], 200),
        ]);

        $this->cfdiService->stamp($xml, $emails);

        Http::assertSent(function (Request $request) {
            return $request['emailsSending'] === 'email1@test.com,email2@test.com';
        });
    }

    public function testStampWithStringEmail(): void
    {
        $xml = '<cfdi:Comprobante>...</cfdi:Comprobante>';

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'response' => 'stamped-xml',
            ], 200),
        ]);

        $this->cfdiService->stamp($xml, 'single@test.com');

        Http::assertSent(function (Request $request) {
            return $request['emailsSending'] === 'single@test.com';
        });
    }

    public function testStampReturnsNullWithEmptyXml(): void
    {
        $result = $this->cfdiService->stamp('', 'test@example.com');

        $this->assertNull($result);
    }

    public function testStampSendsCorrectPayload(): void
    {
        $xml = '<cfdi:Comprobante>Test</cfdi:Comprobante>';

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'response' => 'stamped',
            ], 200),
        ]);

        $this->cfdiService->stamp($xml, 'email@test.com', true, '<html>Custom Email</html>', false);

        Http::assertSent(function (Request $request) use ($xml) {
            return $request['comprobante'] === $xml
                && $request['emailsSending'] === 'email@test.com'
                && $request['useEmailTemplate'] === true
                && $request['htmlEmailOptional'] === '<html>Custom Email</html>'
                && $request['generateFolio'] === false;
        });
    }

    public function testStampReturnsNullOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'error' => 'Stamp error',
            ], 500),
        ]);

        $result = $this->cfdiService->stamp('<xml>test</xml>', 'test@test.com');

        $this->assertNull($result);
    }

    // Verify Tests
    public function testVerifySuccess(): void
    {
        $rfc = 'TEST123456ABC';
        $uuidList = ['uuid-1', 'uuid-2'];
        $expectedResponse = [
            'uuid-1' => true,
            'uuid-2' => false,
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/verifica-UUID' => Http::response([
                'response' => $expectedResponse,
            ], 200),
        ]);

        $result = $this->cfdiService->verify($rfc, $uuidList);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testVerifyReturnsEmptyWithEmptyRfc(): void
    {
        $result = $this->cfdiService->verify('', ['uuid-1']);

        $this->assertEquals([], $result);
    }

    public function testVerifyReturnsEmptyWithEmptyUuidList(): void
    {
        $result = $this->cfdiService->verify('RFC123', []);

        $this->assertEquals([], $result);
    }

    public function testVerifySendsCorrectPayload(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/verifica-UUID' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->cfdiService->verify('TEST123456ABC', ['uuid-1', 'uuid-2']);

        Http::assertSent(function (Request $request) {
            return $request['rfc'] === 'TEST123456ABC'
                && $request['uuidList'] === ['uuid-1', 'uuid-2'];
        });
    }

    // Link Tests
    public function testLinkSuccess(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/vincula-comprobante' => Http::response([], 200),
        ]);

        $result = $this->cfdiService->link(
            journalNumber: 12345,
            rfc: 'TEST123',
            uuidList: ['uuid-1'],
            extensionFiles: ['xml'],
            amounts: [1000.00],
            line: 1
        );

        $this->assertTrue($result);
    }

    public function testLinkSendsCorrectPayload(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/vincula-comprobante' => Http::response([], 200),
        ]);

        $this->cfdiService->link(
            journalNumber: 12345,
            rfc: 'TEST123',
            uuidList: ['uuid-1', 'uuid-2'],
            extensionFiles: ['xml', 'pdf'],
            amounts: [500.00, 600.00],
            line: 2,
            unlink: true
        );

        Http::assertSent(function (Request $request) {
            return $request['journalNumber'] === 12345
                && $request['journalLine'] === 2
                && $request['rfc'] === 'TEST123'
                && $request['comprobantes'] === ['uuid-1', 'uuid-2']
                && $request['extensionFiles'] === ['xml', 'pdf']
                && $request['importeAVincular'] === [500.00, 600.00]
                && $request['desvincular'] === true;
        });
    }

    public function testLinkReturnsFalseOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/vincula-comprobante' => Http::response([
                'error' => 'Link error',
            ], 500),
        ]);

        $result = $this->cfdiService->link(
            journalNumber: 12345,
            rfc: 'TEST123',
            uuidList: ['uuid-1'],
            extensionFiles: ['xml'],
            amounts: [1000.00],
            line: 1
        );

        $this->assertFalse($result);
    }

    // MultiLink Tests
    public function testMultiLinkSuccess(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/vincula-comprobante-lineas' => Http::response([], 200),
        ]);

        $lines = [
            ['line' => 1, 'uuid' => 'uuid-1', 'amount' => 500],
            ['line' => 2, 'uuid' => 'uuid-2', 'amount' => 600],
        ];

        $result = $this->cfdiService->multiLink(12345, 'RFC123', $lines);

        $this->assertTrue($result);
    }

    public function testMultiLinkSendsCorrectPayload(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/vincula-comprobante-lineas' => Http::response([], 200),
        ]);

        $lines = [
            ['line' => 1, 'uuid' => 'uuid-1', 'amount' => 500],
        ];

        $this->cfdiService->multiLink(12345, 'RFC123', $lines, true);

        Http::assertSent(function (Request $request) use ($lines) {
            return $request['journalNumber'] === 12345
                && $request['rfc'] === 'RFC123'
                && $request['requestDataVinculaComprobantes'] === $lines
                && $request['desvincular'] === true;
        });
    }

    // Upload Tests
    public function testUploadSuccess(): void
    {
        $expectedResponse = ['uploaded' => 2, 'failed' => 0];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/upload-comprobantes' => Http::response([
                'response' => $expectedResponse,
            ], 200),
        ]);

        $result = $this->cfdiService->upload('RFC123', ['<xml1>', '<xml2>']);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testUploadSendsCorrectPayload(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/upload-comprobantes' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $xmlFiles = ['<xml1>content</xml1>', '<xml2>content</xml2>'];
        $this->cfdiService->upload('TESTRFC123', $xmlFiles);

        Http::assertSent(function (Request $request) use ($xmlFiles) {
            return $request['rfc'] === 'TESTRFC123'
                && $request['comprobantes'] === $xmlFiles;
        });
    }

    // GetXml Tests
    public function testGetXmlSuccess(): void
    {
        $expectedXml = '<cfdi:Comprobante>Full XML Content</cfdi:Comprobante>';

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/comprobante*' => Http::response([
                'response' => $expectedXml,
            ], 200),
        ]);

        $result = $this->cfdiService->getXml('test-uuid-123');

        $this->assertEquals($expectedXml, $result);
    }

    public function testGetXmlReturnsNullWithEmptyUuid(): void
    {
        $result = $this->cfdiService->getXml('');

        $this->assertNull($result);
    }

    public function testGetXmlSendsCorrectUuid(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/comprobante*' => Http::response([
                'response' => 'xml-content',
            ], 200),
        ]);

        $this->cfdiService->getXml('uuid-to-fetch');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'uuid=uuid-to-fetch');
        });
    }

    public function testGetXmlReturnsNullOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/comprobante*' => Http::response([
                'error' => 'Not found',
            ], 404),
        ]);

        $result = $this->cfdiService->getXml('non-existent-uuid');

        $this->assertNull($result);
    }

    // Connection Error Tests
    public function testStampReturnsNullOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->cfdiService->stamp('<xml>test</xml>', 'test@test.com');

        $this->assertNull($result);
    }

    public function testVerifyReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/verifica-UUID' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->cfdiService->verify('RFC123', ['uuid-1']);

        $this->assertEquals([], $result);
    }

    public function testLinkReturnsFalseOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/vincula-comprobante' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->cfdiService->link(123, 'RFC', ['uuid'], ['xml'], [100], 1);

        $this->assertFalse($result);
    }
}
