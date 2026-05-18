<?php

declare(strict_types=1);

namespace Functional\Client;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as HttpResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Response\Response as ServiceClientResponse;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Tests\Functional\Client\AbstractClientTestCase;

class IsReadyTest extends AbstractClientTestCase
{
    #[DataProvider('isReadyDataProvider')]
    public function testIsReady(ResponseInterface|\Throwable $response, bool $expected): void
    {
        $this->mockHandler->append($response);

        self::assertEquals($expected, $this->client->isReady());
    }

    /**
     * @return array<mixed>
     */
    public static function isReadyDataProvider(): array
    {
        return [
            'cURL exception, not yet responding to http requests' => [
                'response' => new CurlException(
                    new Request('GET', 'https://example.com/'),
                    7,
                    'Failed to connect() to host or proxy.'
                ),
                'expected' => false,
            ],
            'HTTP exception, internal server error, application not fully started' => [
                'response' => new NonSuccessResponseException(
                    new ServiceClientResponse(
                        new HttpResponse(500),
                    )
                ),
                'expected' => false,
            ],
            'ready' => [
                'response' => new HttpResponse(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode([
                        'application' => [
                            'state' => 'awaiting-job',
                            'meta_state' => [
                                'ended' => false,
                                'succeeded' => false,
                            ],
                        ],
                        'compilation' => [
                            'state' => 'awaiting',
                            'meta_state' => [
                                'ended' => false,
                                'succeeded' => false,
                            ],
                        ],
                        'execution' => [
                            'state' => 'awaiting',
                            'meta_state' => [
                                'ended' => false,
                                'succeeded' => false,
                            ],
                        ],
                        'event_delivery' => [
                            'state' => 'awaiting',
                            'meta_state' => [
                                'ended' => false,
                                'succeeded' => false,
                            ],
                        ],
                    ])
                ),
                'expected' => true,
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->getApplicationState();
        };
    }

    protected function getExpectedModelClass(): string
    {
        return ApplicationState::class;
    }
}
