<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\WorkerClient\Model\JobCreationException;

readonly class JobCreationExceptionFactory
{
    public function create(ArrayInspector $data): ?JobCreationException
    {
        $errorState = $data->getNonEmptyString('error_state');
        $payload = $data->getArray('payload');

        return null === $errorState ? null : new JobCreationException($errorState, $payload);
    }
}
