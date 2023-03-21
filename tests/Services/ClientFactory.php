<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\EventFactory;
use SmartAssert\WorkerClient\JobFactory;
use SmartAssert\WorkerClient\ResourceReferenceFactory;
use SmartAssert\WorkerClient\TestFactory;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\Factory\YamlFileFactory;
use SmartAssert\WorkerJobSource\JobSourceSerializer;
use SmartAssert\YamlFile\Collection\Serializer;
use SmartAssert\YamlFile\FileHashes\Serializer as FileHashesSerializer;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class ClientFactory
{
    /**
     * @param non-empty-string $baseUrl
     * @param array<mixed>     $httpClientConfig
     */
    public static function create(string $baseUrl, array $httpClientConfig = []): Client
    {
        $httpFactory = new HttpFactory();
        $yamlDumper = new Dumper();
        $yamlParser = new Parser();

        return new Client(
            $baseUrl,
            new ServiceClient(
                $httpFactory,
                $httpFactory,
                new HttpClient($httpClientConfig),
                ResponseFactory::createFactory(),
            ),
            new EventFactory(
                new ResourceReferenceFactory(),
            ),
            new JobSourceSerializer(
                new Serializer(new FileHashesSerializer($yamlDumper)),
                new YamlFileFactory($yamlDumper),
            ),
            new JobSourceFactory($yamlDumper, $yamlParser),
            new JobFactory(
                new ResourceReferenceFactory(),
                new TestFactory(),
            )
        );
    }
}
