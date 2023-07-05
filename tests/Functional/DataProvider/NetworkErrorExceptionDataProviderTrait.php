<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\DataProvider;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;

trait NetworkErrorExceptionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function networkErrorExceptionDataProvider(): array
    {
        return [
            'network error' => [
                'httpFixture' => new ConnectException('Exception message', new Request('GET', '/')),
                'expectedExceptionClass' => ClientExceptionInterface::class,
            ],
        ];
    }
}
