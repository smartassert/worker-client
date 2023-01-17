<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class ResourceReference
{
    /**
     * @param non-empty-string $label
     * @param non-empty-string $reference
     */
    public function __construct(
        public readonly string $label,
        public readonly string $reference,
    ) {
    }
}
