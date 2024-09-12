<?php

namespace JWebb\Unleash\Tests\Functional\Entities\Api;

use Exception;
use GuzzleHttp\Client;
use JWebb\Unleash\Entities\Api\Feature;
use JWebb\Unleash\Tests\Functional\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class FeatureTest extends FunctionalTestCase
{
    #[DataProvider('getActiveProvider')]
    public function testGetActive(bool $requestFails)
    {
        $clientMock = $this->createMock(Client::class);

        $responseStreamMock = $this->createMock(StreamInterface::class);
        $responseStreamMock->method('__toString')->willReturn($this->getResponseBody());

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getBody')->willReturn($responseStreamMock);

        $requestMethodMock = $clientMock->method('get');

        if ($requestFails) {
            $requestMethodMock->willThrowException(new Exception('Request failed'));
            $this->expectException(Exception::class);
        } else {
            $requestMethodMock->willReturn($responseMock);
        }

        $feature = new Feature($clientMock);
        $features = $feature->getActive();

        $this->assertCount($requestFails ? 0 : 1, $features);

        if (!$requestFails) {
            $this->assertSame('Active feature', $features[0]->name);
        }
    }

    public static function getActiveProvider()
    {
        return [
            'Request fails' => [true],
            'Request succeeds' => [false],
        ];
    }

    private function getResponseBody(): string
    {
        return json_encode([
            'features' => [
                [
                    'name' => 'Active feature',
                    'enabled' => true,
                    'strategies' => [],
                ],
                [
                    'name' => 'Inactive feature',
                    'enabled' => false,
                    'strategies' => [],
                ],
            ],
        ]);
    }
}