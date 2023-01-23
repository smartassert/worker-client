<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\JobCreationError;
use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class CreateJobTest extends AbstractIntegrationTest
{
    public function testCreateManifestEmpty(): void
    {
        $label = md5((string) rand());
        $eventDeliveryUrl = 'http://example.com/event_delivery_url';
        $maximumDurationInSeconds = 300;
        $manifestPaths = [];
        $sources = new ArrayCollection([]);

        self::expectExceptionObject(InvalidManifestException::createForEmptyContent());

        self::$client->createJob($label, $eventDeliveryUrl, $maximumDurationInSeconds, $manifestPaths, $sources);
    }

    public function testCreateJobSourceTestMissing(): void
    {
        $label = md5((string) rand());
        $eventDeliveryUrl = 'http://example.com/event_delivery_url';
        $maximumDurationInSeconds = 300;
        $manifestPaths = [
            'test1.yml',
            'test2.yml',
        ];

        $sources = new ArrayCollection([
            YamlFile::create('/test1.yml', 'file content'),
        ]);

        self::assertEquals(
            new JobCreationError('source/test/missing', ['path' => 'test2.yml']),
            self::$client->createJob($label, $eventDeliveryUrl, $maximumDurationInSeconds, $manifestPaths, $sources)
        );
    }

    public function testCreateJobJobAlreadyExists(): void
    {
        $label = md5((string) rand());
        $eventDeliveryUrl = 'http://example.com/event_delivery_url';
        $maximumDurationInSeconds = 300;
        $manifestPaths = [
            'test1.yml',
        ];

        $sources = new ArrayCollection([
            YamlFile::create('/test1.yml', 'file content'),
        ]);

        self::$client->createJob($label, $eventDeliveryUrl, $maximumDurationInSeconds, $manifestPaths, $sources);
        self::assertEquals(
            new JobCreationError('job/already_exists', []),
            self::$client->createJob($label, $eventDeliveryUrl, $maximumDurationInSeconds, $manifestPaths, $sources)
        );
    }

    public function testCreateJobSuccess(): void
    {
        $label = md5((string) rand());
        $eventDeliveryUrl = 'http://example.com/event_delivery_url';
        $maximumDurationInSeconds = 300;
        $manifestPaths = [
            'test1.yml',
        ];

        $sources = new ArrayCollection([
            YamlFile::create('/test1.yml', 'file content'),
        ]);

        self::assertNull(
            self::$client->createJob($label, $eventDeliveryUrl, $maximumDurationInSeconds, $manifestPaths, $sources)
        );
    }
}
