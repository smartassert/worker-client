<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\EventFactory;
use SmartAssert\WorkerClient\JobFactory;
use SmartAssert\WorkerClient\Model\JobCreationError;
use SmartAssert\WorkerClient\ResourceReferenceFactory;
use SmartAssert\WorkerClient\TestFactory as TestModelFactory;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerClient\Tests\Services\DataRepository;
use SmartAssert\WorkerClient\Tests\Services\TestFactory;
use SmartAssert\WorkerClient\Tests\Services\WorkerEventFactory;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\Factory\YamlFileFactory;
use SmartAssert\WorkerJobSource\JobSourceSerializer;
use SmartAssert\YamlFile\Collection\Serializer;
use SmartAssert\YamlFile\FileHashes\Serializer as FileHashesSerializer;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

abstract class AbstractIntegrationTest extends TestCase
{
    protected static Client $client;
    protected static DataRepository $dataRepository;
    protected static WorkerEventFactory $workerEventFactory;
    protected static TestFactory $testFactory;

    public static function setUpBeforeClass(): void
    {
        $yamlDumper = new Dumper();
        $yamlParser = new Parser();

        $resourceReferenceFactory = new ResourceReferenceFactory();

        self::$client = new Client(
            'http://localhost:9080',
            self::createServiceClient(),
            new EventFactory($resourceReferenceFactory),
            new JobSourceSerializer(
                new Serializer(new FileHashesSerializer($yamlDumper)),
                new YamlFileFactory($yamlDumper),
            ),
            new JobSourceFactory($yamlDumper, $yamlParser),
            new JobFactory(
                $resourceReferenceFactory,
                new TestModelFactory(),
            ),
        );

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

    protected function makeCreateJobCall(JobCreationProperties $jobCreationProperties): ?JobCreationError
    {
        return self::$client->createJob(
            $jobCreationProperties->label,
            $jobCreationProperties->eventDeliveryUrl,
            $jobCreationProperties->maximumDurationInSeconds,
            $jobCreationProperties->manifestPaths,
            $jobCreationProperties->sources,
        );
    }

    private static function createServiceClient(): ServiceClient
    {
        $httpFactory = new HttpFactory();

        return new ServiceClient($httpFactory, $httpFactory, new HttpClient());
    }
}
