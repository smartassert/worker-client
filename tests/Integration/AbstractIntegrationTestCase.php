<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ResultsClient\Client as ResultsClient;
use SmartAssert\ResultsClient\EventFactory;
use SmartAssert\ResultsClient\ResourceReferenceFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use SmartAssert\UsersClient\Client as UsersClient;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\Tests\Services\ClientFactory;
use SmartAssert\WorkerClient\Tests\Services\DataRepository;
use Symfony\Component\Uid\Ulid;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected static Client $client;
    protected static ?ResultsClient $resultsClient = null;

    /**
     * @var null|non-empty-string
     */
    protected static ?string $apiToken = null;
    protected static ?ServiceClient $serviceClient = null;

    /**
     * @var null|non-empty-string
     */
    protected static ?string $jobLabel = null;
    protected static DataRepository $dataRepository;

    public static function setUpBeforeClass(): void
    {
        self::$client = ClientFactory::create('http://localhost:9082');

        self::$dataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=worker-db;user=postgres;password=password!'
        );
    }

    protected function setUp(): void
    {
        self::$dataRepository->removeAllData();
    }

    protected static function getResultsClient(): ResultsClient
    {
        if (null === self::$resultsClient) {
            $eventFactory = new EventFactory(new ResourceReferenceFactory());

            self::$resultsClient = new ResultsClient('http://localhost:9081', self::getServiceClient(), $eventFactory);
        }

        return self::$resultsClient;
    }

    /**
     * @return non-empty-string
     */
    protected static function getJobLabel(): string
    {
        if (null === self::$jobLabel) {
            $jobLabel = (string) new Ulid();
            \assert('' !== $jobLabel);
            self::$jobLabel = $jobLabel;
        }

        return self::$jobLabel;
    }

    /**
     * @return non-empty-string
     */
    protected static function getApiToken(): string
    {
        if (null === self::$apiToken) {
            $usersClient = new UsersClient('http://localhost:9080', self::getServiceClient());
            $frontendTokenProvider = new FrontendTokenProvider(['user@example.com' => 'password'], $usersClient);
            $apiTokenProvider = new ApiTokenProvider($usersClient, $frontendTokenProvider);
            self::$apiToken = $apiTokenProvider->get('user@example.com');
        }

        return self::$apiToken;
    }

    protected static function getServiceClient(): ServiceClient
    {
        if (null === self::$serviceClient) {
            $httpFactory = new HttpFactory();
            $httpClient = new HttpClient();
            $responseFactory = ResponseFactory::createFactory();
            self::$serviceClient = new ServiceClient($httpFactory, $httpFactory, $httpClient, $responseFactory);
        }

        return self::$serviceClient;
    }
}
