<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\Tests\Services\DataRepository;
use SmartAssert\WorkerClient\Tests\Services\WorkerEventFactory;

abstract class AbstractIntegrationTest extends TestCase
{
    protected static Client $client;
    protected static DataRepository $dataRepository;
    protected static WorkerEventFactory $workerEventFactory;

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client(
            'http://localhost:9080',
            self::createServiceClient(),
        );

        self::$dataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=worker-db;user=postgres;password=password!'
        );

        self::$dataRepository->removeAllData();

        self::$workerEventFactory = new WorkerEventFactory(self::$dataRepository);
    }

    private static function createServiceClient(): ServiceClient
    {
        $httpFactory = new HttpFactory();

        return new ServiceClient($httpFactory, $httpFactory, new HttpClient());
    }
}
