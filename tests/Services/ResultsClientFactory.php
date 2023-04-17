<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use SmartAssert\ResultsClient\Client as ResultsClient;
use SmartAssert\ResultsClient\EventFactory;
use SmartAssert\ResultsClient\ResourceReferenceFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;

class ResultsClientFactory
{
    public function __construct(
        private readonly ServiceClient $serviceClient,
    ) {
    }

    public function create(): ResultsClient
    {
        $eventFactory = new EventFactory(new ResourceReferenceFactory());

        return new ResultsClient('http://localhost:9081', $this->serviceClient, $eventFactory);
    }
}
