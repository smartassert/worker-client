<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\WorkerClient\Model\ApplicationState;

class GetApplicationStateTest extends AbstractClientTestCase
{
    /**
     * @dataProvider getApplicationStateDataProvider
     *
     * @param array{
     *     application: non-empty-string,
     *     compilation: non-empty-string,
     *     execution: non-empty-string,
     *     event_delivery: non-empty-string
     * } $responseData
     */
    public function testGetApplicationState(array $responseData, ApplicationState $expected): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($responseData)
        ));

        self::assertEquals($expected, $this->client->getApplicationState());
    }

    /**
     * @return array<mixed>
     */
    public function getApplicationStateDataProvider(): array
    {
        return [
            'new job' => [
                'responseData' => [
                    'application' => 'awaiting-job',
                    'compilation' => 'awaiting',
                    'execution' => 'awaiting',
                    'event_delivery' => 'awaiting',
                ],
                'expected' => new ApplicationState('awaiting-job', 'awaiting', 'awaiting', 'awaiting'),
            ],
            'compiling' => [
                'responseData' => [
                    'application' => 'compiling',
                    'compilation' => 'running',
                    'execution' => 'awaiting',
                    'event_delivery' => 'running',
                ],
                'expected' => new ApplicationState('compiling', 'running', 'awaiting', 'running'),
            ],
            'executing' => [
                'responseData' => [
                    'application' => 'executing',
                    'compilation' => 'complete',
                    'execution' => 'running',
                    'event_delivery' => 'running',
                ],
                'expected' => new ApplicationState('executing', 'complete', 'running', 'running'),
            ],
            'complete, awaiting event delivery completion' => [
                'responseData' => [
                    'application' => 'completing-event-delivery',
                    'compilation' => 'complete',
                    'execution' => 'complete',
                    'event_delivery' => 'running',
                ],
                'expected' => new ApplicationState('completing-event-delivery', 'complete', 'complete', 'running'),
            ],
            'complete' => [
                'responseData' => [
                    'application' => 'complete',
                    'compilation' => 'complete',
                    'execution' => 'complete',
                    'event_delivery' => 'complete',
                ],
                'expected' => new ApplicationState('complete', 'complete', 'complete', 'complete'),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->getApplicationState();
        };
    }

    protected function getExpectedModelClass(): string
    {
        return ApplicationState::class;
    }
}
