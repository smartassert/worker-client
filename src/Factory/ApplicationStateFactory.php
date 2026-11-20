<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\ArrayInspector\ArrayInspector;
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
        $data = new ArrayInspector($data);

        $applicationState = $this->componentStateFactory->create($data->getArray('application'));
        $compilationState = $this->componentStateFactory->create($data->getArray('compilation'));
        $executionState = $this->componentStateFactory->create($data->getArray('execution'));
        $eventDeliveryState = $this->componentStateFactory->create($data->getArray('event_delivery'));

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
