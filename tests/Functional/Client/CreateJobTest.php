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
     * @param non-empty-string  $label
     * @param positive-int      $maximumDurationInSeconds
     * @param ?non-empty-string $stateNotifyUrl
     * @param array<mixed>      $expectedRequestPayload
     */
    #[DataProvider('requestPropertiesDataProvider')]
    public function testRequestProperties(
        string $label,
        string $eventDeliveryUrl,
        int $maximumDurationInSeconds,
        string $serializedJobSource,
        ?string $stateNotifyUrl,
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
            $eventDeliveryUrl,
            $maximumDurationInSeconds,
            $serializedJobSource,
            $stateNotifyUrl,
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
            'without state notify url' => [
                'label' => $label,
                'eventDeliveryUrl' => 'https://localhost/1/event',
                'maximumDurationInSeconds' => 600,
                'serializedJobSource' => 'serialized job source 01',
                'stateNotifyUrl' => null,
                'expectedRequestPayload' => [
                    'label' => $label,
                    'event_add_url' => 'https://localhost/1/event',
                    'maximum_duration_in_seconds' => '600',
                    'source' => 'serialized job source 01',
                ],
            ],
            'with state notify url' => [
                'label' => $label,
                'eventDeliveryUrl' => 'https://localhost/2/event',
                'maximumDurationInSeconds' => 500,
                'serializedJobSource' => 'serialized job source 02',
                'stateNotifyUrl' => 'https://localhost/2/state_notify',
                'expectedRequestPayload' => [
                    'label' => $label,
                    'event_add_url' => 'https://localhost/2/event',
                    'maximum_duration_in_seconds' => '500',
                    'source' => 'serialized job source 02',
                    'state_notify_url' => 'https://localhost/2/state_notify',
                ],
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->createJob(
                'job label',
                'event delivery url',
                300,
                'serialized job source'
            );
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
