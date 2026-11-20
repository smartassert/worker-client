<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

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
        $applicationData = $data['application'] ?? [];
        $applicationData = is_array($applicationData) ? $applicationData : [];

        $compilationData = $data['compilation'] ?? [];
        $compilationData = is_array($compilationData) ? $compilationData : [];

        $executionData = $data['execution'] ?? [];
        $executionData = is_array($executionData) ? $executionData : [];

        $eventDeliveryData = $data['event_delivery'] ?? [];
        $eventDeliveryData = is_array($eventDeliveryData) ? $eventDeliveryData : [];

        $applicationState = $this->componentStateFactory->create($applicationData);
        $compilationState = $this->componentStateFactory->create($compilationData);
        $executionState = $this->componentStateFactory->create($executionData);
        $eventDeliveryState = $this->componentStateFactory->create($eventDeliveryData);

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
