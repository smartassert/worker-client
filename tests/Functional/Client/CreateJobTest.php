<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use SmartAssert\WorkerClient\Model\Job;

class CreateJobTest extends AbstractClientTest
{
    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->createJob(
                'job label',
                'event delivery url',
                300,
                'serialized job source'
            );
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
