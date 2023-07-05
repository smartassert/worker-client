<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\EventFactory;
use SmartAssert\WorkerClient\JobFactory;
use SmartAssert\WorkerClient\ResourceReferenceFactory;
use SmartAssert\WorkerClient\TestFactory;

class ClientFactory
{
    /**
     * @param non-empty-string $baseUrl
     * @param array<mixed>     $httpClientConfig
     */
    public static function create(string $baseUrl, array $httpClientConfig = []): Client
    {
        $httpFactory = new HttpFactory();

        return new Client(
            $baseUrl,
            new ServiceClient(
                $httpFactory,
                $httpFactory,
                new HttpClient($httpClientConfig),
                ResponseFactory::createFactory(),
                new CurlExceptionFactory(),
            ),
            new EventFactory(
                new ResourceReferenceFactory(),
            ),
            new JobFactory(
                new ResourceReferenceFactory(),
                new TestFactory(),
            )
        );
    }
}
