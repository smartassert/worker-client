<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;

class ServiceClientFactory
{
    public function create(): ServiceClient
    {
        $httpFactory = new HttpFactory();
        $httpClient = new HttpClient();
        $responseFactory = ResponseFactory::createFactory();

        return new ServiceClient($httpFactory, $httpFactory, $httpClient, $responseFactory);
    }
}
