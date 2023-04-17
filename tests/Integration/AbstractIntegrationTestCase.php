<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\Tests\Services\ClientFactory;
use SmartAssert\WorkerClient\Tests\Services\DataRepository;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected static Client $client;
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
}
