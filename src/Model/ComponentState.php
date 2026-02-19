<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

readonly class ComponentState
{
    /**
     * @param non-empty-string $state
     */
    public function __construct(
        public string $state,
        public MetaState $metaState,
    ) {}
}
