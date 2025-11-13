<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\WorkerClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\WorkerClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\WorkerClient\Tests\Services\ClientFactory;

abstract class AbstractClientTestCase extends TestCase
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
        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->client = ClientFactory::create('https://worker.example.com', ['handler' => $handlerStack]);
    }

    /**
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    #[DataProvider('networkErrorExceptionDataProvider')]
    #[DataProvider('invalidJsonResponseExceptionDataProvider')]
    public function testClientActionThrowsException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        ($this->createClientActionCallable())();
    }

    #[DataProvider('commonNonSuccessResponseDataProvider')]
    public function testClientActionThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            ($this->createClientActionCallable())();

            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->getHttpResponse());
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
            self::assertSame($response, $e->getHttpResponse());
            self::assertSame($responsePayload, $e->payload);
        }
    }

    abstract protected function createClientActionCallable(): callable;

    /**
     * @return class-string
     */
    abstract protected function getExpectedModelClass(): string;
}
