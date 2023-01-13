<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use SmartAssert\ServiceClient\Client as ServiceClient;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
    ) {
    }
}
