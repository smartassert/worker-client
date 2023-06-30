<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\ComponentState;

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
                    'application' => [
                        'state' => 'awaiting-job',
                        'is_end_state' => false,
                    ],
                    'compilation' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                    ],
                    'execution' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                    ],
                    'event_delivery' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('awaiting-job', false),
                    new ComponentState('awaiting', false),
                    new ComponentState('awaiting', false),
                    new ComponentState('awaiting', false),
                ),
            ],
            'compiling' => [
                'responseData' => [
                    'application' => [
                        'state' => 'compiling',
                        'is_end_state' => false,
                    ],
                    'compilation' => [
                        'state' => 'running',
                        'is_end_state' => false,
                    ],
                    'execution' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                    ],
                    'event_delivery' => [
                        'state' => 'running',
                        'is_end_state' => false,
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('compiling', false),
                    new ComponentState('running', false),
                    new ComponentState('awaiting', false),
                    new ComponentState('running', false),
                ),
            ],
            'executing' => [
                'responseData' => [
                    'application' => [
                        'state' => 'executing',
                        'is_end_state' => false,
                    ],
                    'compilation' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                    ],
                    'execution' => [
                        'state' => 'running',
                        'is_end_state' => false,
                    ],
                    'event_delivery' => [
                        'state' => 'running',
                        'is_end_state' => false,
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('executing', false),
                    new ComponentState('complete', true),
                    new ComponentState('running', false),
                    new ComponentState('running', false),
                ),
            ],
            'complete, awaiting event delivery completion' => [
                'responseData' => [
                    'application' => [
                        'state' => 'completing-event-delivery',
                        'is_end_state' => false,
                    ],
                    'compilation' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                    ],
                    'execution' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                    ],
                    'event_delivery' => [
                        'state' => 'running',
                        'is_end_state' => false,
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('completing-event-delivery', false),
                    new ComponentState('complete', true),
                    new ComponentState('complete', true),
                    new ComponentState('running', false),
                ),
            ],
            'complete' => [
                'responseData' => [
                    'application' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                    ],
                    'compilation' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                    ],
                    'execution' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                    ],
                    'event_delivery' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('complete', true),
                    new ComponentState('complete', true),
                    new ComponentState('complete', true),
                    new ComponentState('complete', true),
                ),
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
