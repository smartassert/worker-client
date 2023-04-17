<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\Tests\Services\ClientFactory;
use SmartAssert\WorkerClient\Tests\Services\DataRepository;
use Symfony\Component\Uid\Ulid;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected static Client $client;
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
