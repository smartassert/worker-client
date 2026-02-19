<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\ComponentState;
use SmartAssert\WorkerClient\Model\MetaState;

class GetApplicationStateTest extends AbstractClientTestCase
{
    /**
     * @param array{
     *     application: non-empty-string,
     *     compilation: non-empty-string,
     *     execution: non-empty-string,
     *     event_delivery: non-empty-string
     * } $responseData
     */
    #[DataProvider('getApplicationStateDataProvider')]
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
    public static function getApplicationStateDataProvider(): array
    {
        return [
            'new job' => [
                'responseData' => [
                    'application' => [
                        'state' => 'awaiting-job',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'compilation' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'execution' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'event_delivery' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('awaiting-job', false, new MetaState(false, false)),
                    new ComponentState('awaiting', false, new MetaState(false, false)),
                    new ComponentState('awaiting', false, new MetaState(false, false)),
                    new ComponentState('awaiting', false, new MetaState(false, false)),
                ),
            ],
            'compiling' => [
                'responseData' => [
                    'application' => [
                        'state' => 'compiling',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'compilation' => [
                        'state' => 'running',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'execution' => [
                        'state' => 'awaiting',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'event_delivery' => [
                        'state' => 'running',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('compiling', false, new MetaState(false, false)),
                    new ComponentState('running', false, new MetaState(false, false)),
                    new ComponentState('awaiting', false, new MetaState(false, false)),
                    new ComponentState('running', false, new MetaState(false, false)),
                ),
            ],
            'executing' => [
                'responseData' => [
                    'application' => [
                        'state' => 'executing',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'compilation' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ],
                    'execution' => [
                        'state' => 'running',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'event_delivery' => [
                        'state' => 'running',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('executing', false, new MetaState(false, false)),
                    new ComponentState('complete', true, new MetaState(true, true)),
                    new ComponentState('running', false, new MetaState(false, false)),
                    new ComponentState('running', false, new MetaState(false, false)),
                ),
            ],
            'complete, awaiting event delivery completion' => [
                'responseData' => [
                    'application' => [
                        'state' => 'completing-event-delivery',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                    'compilation' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ],
                    'execution' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ],
                    'event_delivery' => [
                        'state' => 'running',
                        'is_end_state' => false,
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('completing-event-delivery', false, new MetaState(false, false)),
                    new ComponentState('complete', true, new MetaState(true, true)),
                    new ComponentState('complete', true, new MetaState(true, true)),
                    new ComponentState('running', false, new MetaState(false, false)),
                ),
            ],
            'complete' => [
                'responseData' => [
                    'application' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ],
                    'compilation' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ],
                    'execution' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ],
                    'event_delivery' => [
                        'state' => 'complete',
                        'is_end_state' => true,
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ],
                ],
                'expected' => new ApplicationState(
                    new ComponentState('complete', true, new MetaState(true, true)),
                    new ComponentState('complete', true, new MetaState(true, true)),
                    new ComponentState('complete', true, new MetaState(true, true)),
                    new ComponentState('complete', true, new MetaState(true, true)),
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
