<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\WorkerClient\Model\JobCreationException;

readonly class JobCreationExceptionFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?JobCreationException
    {
        $data = new ArrayInspector($data);

        $errorState = $data->getNonEmptyString('error_state');
        $payload = $data->getArray('payload');

        return null === $errorState ? null : new JobCreationException($errorState, $payload);
    }
}
