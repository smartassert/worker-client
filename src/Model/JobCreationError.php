<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class JobCreationError
{
    /**
     * @param non-empty-string $errorState
     * @param array<mixed>     $payload
     */
    public function __construct(
        public readonly string $errorState,
        public readonly array $payload,
    ) {
    }
}
