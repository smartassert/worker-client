<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\Model\JobCreationException;

readonly class JobCreationExceptionFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?JobCreationException
    {
        $errorState = $data['error_state'] ?? null;
        $errorState = is_string($errorState) && '' !== $errorState ? $errorState : null;

        $payload = $data['payload'] ?? [];
        $payload = is_array($payload) ? $payload : [];

        return null === $errorState ? null : new JobCreationException($errorState, $payload);
    }
}
