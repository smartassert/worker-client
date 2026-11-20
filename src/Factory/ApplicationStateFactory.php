<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\DataExtractor\DataExtractor;
use SmartAssert\WorkerClient\Model\ApplicationState;

readonly class ApplicationStateFactory
{
    public function __construct(
        private ComponentStateFactory $componentStateFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ApplicationState
    {
        $applicationState = $this->componentStateFactory->create(DataExtractor::getArray($data, 'application'));
        $compilationState = $this->componentStateFactory->create(DataExtractor::getArray($data, 'compilation'));
        $executionState = $this->componentStateFactory->create(DataExtractor::getArray($data, 'execution'));
        $eventDeliveryState = $this->componentStateFactory->create(DataExtractor::getArray($data, 'event_delivery'));

        if (
            null === $applicationState
            || null === $compilationState
            || null === $executionState
            || null === $eventDeliveryState
        ) {
            return null;
        }

        return new ApplicationState($applicationState, $compilationState, $executionState, $eventDeliveryState);
    }
}
