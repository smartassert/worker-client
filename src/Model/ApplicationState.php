<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

readonly class ApplicationState
{
    public function __construct(
        public ComponentState $applicationState,
        public ComponentState $compilationState,
        public ComponentState $executionState,
        public ComponentState $eventDeliveryState,
    ) {
    }
}
