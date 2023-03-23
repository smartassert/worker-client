<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class JobCreationException extends \Exception
{
    /**
     * @param non-empty-string $errorState
     * @param array<mixed>     $payload
     */
    public function __construct(
        public readonly string $errorState,
        public readonly array $payload,
    ) {
        parent::__construct($this->errorState);
    }
}
