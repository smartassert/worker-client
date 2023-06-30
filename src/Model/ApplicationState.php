<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class ApplicationState
{
    public function __construct(
        public readonly ComponentState $applicationState,
        public readonly ComponentState $compilationState,
        public readonly ComponentState $executionState,
        public readonly ComponentState $eventDeliveryState,
    ) {
    }
}
