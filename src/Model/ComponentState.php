<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class ComponentState
{
    /**
     * @param non-empty-string $state
     */
    public function __construct(
        public readonly string $state,
        public readonly bool $isEndState,
    ) {
    }
}
