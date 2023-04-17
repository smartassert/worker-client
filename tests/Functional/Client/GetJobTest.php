<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use SmartAssert\WorkerClient\Model\Job;

class GetJobTest extends AbstractClientTestCase
{
    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->getJob();
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
