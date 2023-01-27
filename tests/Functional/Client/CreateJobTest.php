<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class CreateJobTest extends AbstractClientTest
{
    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->createJob(
                'job label',
                'event delivery url',
                300,
                ['test.yml'],
                new ArrayCollection([YamlFile::create('test.yml', 'test content')])
            );
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
