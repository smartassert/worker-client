<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationError;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerClient\Tests\Services\ClientFactory;
use SmartAssert\WorkerClient\Tests\Services\DataRepository;
use SmartAssert\WorkerClient\Tests\Services\TestFactory;
use SmartAssert\WorkerClient\Tests\Services\WorkerEventFactory;

abstract class AbstractIntegrationTest extends TestCase
{
    protected static Client $client;
    protected static DataRepository $dataRepository;
    protected static WorkerEventFactory $workerEventFactory;
    protected static TestFactory $testFactory;

    public static function setUpBeforeClass(): void
    {
        self::$client = ClientFactory::create('http://localhost:9080');

        self::$dataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=worker-db;user=postgres;password=password!'
        );

        self::$workerEventFactory = new WorkerEventFactory(self::$dataRepository);
        self::$testFactory = new TestFactory(self::$dataRepository);
    }

    protected function setUp(): void
    {
        self::$dataRepository->removeAllData();
    }

    protected function makeCreateJobCall(JobCreationProperties $jobCreationProperties): JobCreationError|Job
    {
        return self::$client->createJob(
            $jobCreationProperties->label,
            $jobCreationProperties->eventDeliveryUrl,
            $jobCreationProperties->maximumDurationInSeconds,
            $jobCreationProperties->manifestPaths,
            $jobCreationProperties->sources,
        );
    }
}
