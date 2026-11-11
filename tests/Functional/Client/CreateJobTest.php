<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\RequestInterface;
use SmartAssert\WorkerClient\Model\Job;
use Symfony\Component\Uid\Ulid;

class CreateJobTest extends AbstractClientTestCase
{
    /**
     * @param non-empty-string $label
     * @param positive-int     $maximumDurationInSeconds
     * @param non-empty-string $stateNotifyUrl
     * @param array<mixed>     $expectedRequestPayload
     */
    #[DataProvider('requestPropertiesDataProvider')]
    public function testRequestProperties(
        string $label,
        string $eventNotifyUrl,
        string $eventNotifyToken,
        string $stateNotifyUrl,
        string $stateNotifyToken,
        int $maximumDurationInSeconds,
        string $serializedJobSource,
        array $expectedRequestPayload,
    ): void {
        $responsePayload = [
            'label' => $label,
            'reference' => md5($label),
            'maximum_duration_in_seconds' => $maximumDurationInSeconds,
        ];

        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($responsePayload));
        $this->mockHandler->append($response);

        $this->client->createJob(
            $label,
            $eventNotifyUrl,
            $eventNotifyToken,
            $stateNotifyUrl,
            $stateNotifyToken,
            $maximumDurationInSeconds,
            $serializedJobSource,
        );

        $request = $this->httpHistoryContainer->getTransactions()->getRequests()->getLast();
        \assert($request instanceof RequestInterface);

        self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));

        $requestPayload = [];
        parse_str($request->getBody()->getContents(), $requestPayload);

        self::assertSame($expectedRequestPayload, $requestPayload);
    }

    /**
     * @return array<mixed>
     */
    public static function requestPropertiesDataProvider(): array
    {
        $label = (string) new Ulid();

        return [
            'with state notify url' => [
                'label' => $label,
                'eventNotifyUrl' => 'https://localhost/event-notify',
                'eventNotifyToken' => 'event_notify_token',
                'stateNotifyUrl' => 'https://localhost/state-notify',
                'stateNotifyToken' => 'state_notify_token',
                'maximumDurationInSeconds' => 500,
                'serializedJobSource' => 'serialized job source',
                'expectedRequestPayload' => [
                    'label' => $label,
                    'event_notify_url' => 'https://localhost/event-notify',
                    'event_notify_token' => 'event_notify_token',
                    'state_notify_url' => 'https://localhost/state-notify',
                    'state_notify_token' => 'state_notify_token',
                    'maximum_duration_in_seconds' => '500',
                    'source' => 'serialized job source',
                ],
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->createJob(
                'job label',
                'event notify url',
                'event notify token',
                'state notify url',
                'state notify token',
                300,
                'serialized job source',
            );
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
