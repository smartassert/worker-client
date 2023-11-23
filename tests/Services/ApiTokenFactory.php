<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use SmartAssert\UsersClient\Client as UsersClient;

class ApiTokenFactory
{
    public function __construct(
        private readonly ServiceClient $serviceClient,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function create(): string
    {
        $usersClient = new UsersClient('http://localhost:9080', $this->serviceClient);
        $frontendTokenProvider = new FrontendTokenProvider(['user@example.com' => 'password'], $usersClient);
        $apiKeyProvider = new ApiKeyProvider($usersClient, $frontendTokenProvider);
        $apiTokenProvider = new ApiTokenProvider($usersClient, $apiKeyProvider);

        return $apiTokenProvider->get('user@example.com');
    }
}
