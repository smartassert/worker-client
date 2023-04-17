<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use Symfony\Component\Uid\Ulid;

class JobLabelFactory
{
    /**
     * @return non-empty-string
     */
    public function create(): string
    {
        $jobLabel = (string) new Ulid();
        \assert('' !== $jobLabel);

        return $jobLabel;
    }
}
