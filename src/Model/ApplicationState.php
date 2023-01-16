<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class ApplicationState
{
    /**
     * @param non-empty-string $applicationState
     * @param non-empty-string $compilationState
     * @param non-empty-string $executionState
     * @param non-empty-string $eventDeliveryState
     */
    public function __construct(
        public readonly string $applicationState,
        public readonly string $compilationState,
        public readonly string $executionState,
        public readonly string $eventDeliveryState,
    ) {
    }
}
