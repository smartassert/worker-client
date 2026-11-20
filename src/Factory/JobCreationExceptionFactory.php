<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\DataExtractor\DataExtractor;
use SmartAssert\WorkerClient\Model\JobCreationException;

readonly class JobCreationExceptionFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?JobCreationException
    {
        $errorState = DataExtractor::getNonEmptyString($data, 'error_state');
        $payload = DataExtractor::getArray($data, 'payload');

        return null === $errorState ? null : new JobCreationException($errorState, $payload);
    }
}
