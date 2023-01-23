<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\EventFactory;
use SmartAssert\WorkerClient\ResourceReferenceFactory;
use SmartAssert\WorkerClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\WorkerClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\WorkerClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\Factory\YamlFileFactory;
use SmartAssert\WorkerJobSource\JobSourceSerializer;
use SmartAssert\YamlFile\Collection\Serializer;
use SmartAssert\YamlFile\FileHashes\Serializer as FileHashesSerializer;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

abstract class AbstractClientTest extends TestCase
{
    use CommonNonSuccessResponseDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    protected MockHandler $mockHandler;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        $httpFactory = new HttpFactory();

        $handlerStack = HandlerStack::create($this->mockHandler);

        $yamlDumper = new Dumper();
        $yamlParser = new Parser();

        $this->client = new Client(
            'https://worker.example.com',
            new ServiceClient(
                $httpFactory,
                $httpFactory,
                new HttpClient(['handler' => $handlerStack]),
            ),
            new EventFactory(
                new ResourceReferenceFactory(),
            ),
            new JobSourceSerializer(
                new Serializer(new FileHashesSerializer($yamlDumper)),
                new YamlFileFactory($yamlDumper),
            ),
            new JobSourceFactory($yamlDumper, $yamlParser),
        );
    }

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testClientActionThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        ($this->createClientActionCallable())();
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testClientActionThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            ($this->createClientActionCallable())();

            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testClientActionThrowsInvalidModelDataException(): void
    {
        $responsePayload = ['key' => 'value'];
        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($responsePayload));

        $this->mockHandler->append($response);

        try {
            ($this->createClientActionCallable())();
            self::fail(InvalidModelDataException::class . ' not thrown');
        } catch (InvalidModelDataException $e) {
            self::assertSame($this->getExpectedModelClass(), $e->class);
            self::assertSame($response, $e->response);
            self::assertSame($responsePayload, $e->payload);
        }
    }

    abstract protected function createClientActionCallable(): callable;

    /**
     * @return class-string
     */
    abstract protected function getExpectedModelClass(): string;
}
