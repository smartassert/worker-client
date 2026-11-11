<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerClient\Tests\Services\JobFactory;
use SmartAssert\WorkerClient\Tests\Services\JobSourceSerializerFactory;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class GetEventTest extends AbstractIntegrationTestCase
{
    public function testGetEventSuccess(): void
    {
        $jobLabel = 'job-label';
        $jobReference = md5($jobLabel);

        $jobCreationProperties = new JobCreationProperties(
            ['test1.yml'],
        )
            ->withMaximumDurationInSeconds(60)
            ->withSources(
                new ArrayCollection([YamlFile::create('test1.yml', 'file content')]),
            )
            ->withLabel($jobLabel)
        ;

        $jobFactory = new JobFactory(self::$client, new JobSourceSerializerFactory());
        $jobFactory->create($jobCreationProperties);

        $event = self::$client->getEvent(1);

        $expected = new Event(
            1,
            'job/started',
            new ResourceReference($jobLabel, $jobReference),
            [
                'tests' => [
                    'test1.yml',
                ],
            ],
            [
                new ResourceReference(
                    'test1.yml',
                    md5($jobLabel . 'test1.yml')
                ),
            ],
        );

        self::assertEquals($expected, $event);
    }
}
