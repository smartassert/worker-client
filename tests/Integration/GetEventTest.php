<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Tests\Services\WorkerEventFactory;

/**
 * @phpstan-type SerializedResourceReference array{
 *     label: non-empty-string,
 *     reference: non-empty-string
 * }
 * @phpstan-type SerializedEvent array{
 *     scope: non-empty-string,
 *     outcome: non-empty-string,
 *     state: non-empty-string,
 *     payload: array<mixed>
 * }
 */
class GetEventTest extends AbstractIntegrationTestCase
{
    protected static WorkerEventFactory $workerEventFactory;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$workerEventFactory = new WorkerEventFactory(self::$dataRepository);
    }

    /**
     * @dataProvider getEventDataProvider
     *
     * @phpstan-param SerializedResourceReference $eventReferenceData
     * @phpstan-param SerializedResourceReference[] $relatedReferenceDataCollection
     * @phpstan-param SerializedEvent $eventData
     */
    public function testGetEventSuccess(
        array $eventReferenceData,
        array $relatedReferenceDataCollection,
        array $eventData,
        Event $expected,
    ): void {
        $referenceId = self::$workerEventFactory->createWorkerEventReference(
            $eventReferenceData['label'],
            $eventReferenceData['reference']
        );

        $relatedReferenceIds = [];
        foreach ($relatedReferenceDataCollection as $relatedReferenceData) {
            $relatedReferenceIds[] = self::$workerEventFactory->createWorkerEventReference(
                $relatedReferenceData['label'],
                $relatedReferenceData['reference']
            );
        }

        $workerEventId = self::$workerEventFactory->createWorkerEvent(
            $referenceId,
            $eventData['scope'],
            $eventData['outcome'],
            $eventData['payload'],
            $eventData['state'],
            $relatedReferenceIds
        );

        $event = self::$client->getEvent($workerEventId);

        self::assertEquals($expected, $event);
    }

    /**
     * @return array<mixed>
     */
    public static function getEventDataProvider(): array
    {
        $jobLabel = md5((string) rand());
        $jobReference = md5($jobLabel);

        return [
            'job/started' => [
                'eventReferenceData' => [
                    'label' => $jobLabel,
                    'reference' => $jobReference,
                ],
                'relatedReferenceDataCollection' => [
                    [
                        'label' => 'Test/chrome-open-index.yml',
                        'reference' => md5($jobLabel . 'Test/chrome-open-index.yml'),
                    ],
                    [
                        'label' => 'Test/chrome-open-index-compilation-failure.yml',
                        'reference' => md5(
                            $jobLabel . 'Test/chrome-open-index-compilation-failure.yml'
                        ),
                    ],
                ],
                'eventData' => [
                    'scope' => 'job',
                    'outcome' => 'started',
                    'state' => 'awaiting',
                    'payload' => [
                        'tests' => [
                            'Test/chrome-open-index.yml',
                            'Test/chrome-open-index-compilation-failure.yml',
                        ],
                    ],
                ],
                'expected' => new Event(
                    1,
                    'job/started',
                    new ResourceReference($jobLabel, $jobReference),
                    [
                        'tests' => [
                            'Test/chrome-open-index.yml',
                            'Test/chrome-open-index-compilation-failure.yml',
                        ],
                    ],
                    [
                        new ResourceReference(
                            'Test/chrome-open-index.yml',
                            md5($jobLabel . 'Test/chrome-open-index.yml')
                        ),
                        new ResourceReference(
                            'Test/chrome-open-index-compilation-failure.yml',
                            md5($jobLabel . 'Test/chrome-open-index-compilation-failure.yml')
                        ),
                    ],
                ),
            ],
            'job/compilation/started' => [
                'eventReferenceData' => [
                    'label' => $jobLabel,
                    'reference' => $jobReference,
                ],
                'relatedReferenceDataCollection' => [],
                'eventData' => [
                    'scope' => 'job/compilation',
                    'outcome' => 'started',
                    'state' => 'awaiting',
                    'payload' => [],
                ],
                'expected' => new Event(
                    1,
                    'job/compilation/started',
                    new ResourceReference($jobLabel, $jobReference),
                    [],
                    null,
                ),
            ],
        ];
    }
}
