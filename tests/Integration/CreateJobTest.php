<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\JobCreationError;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class CreateJobTest extends AbstractIntegrationTest
{
    public function testCreateManifestEmpty(): void
    {
        self::expectExceptionObject(InvalidManifestException::createForEmptyContent());

        $this->makeCreateJobCall(JobCreationProperties::create());
    }

    public function testCreateJobSourceTestMissing(): void
    {
        $jobCreationProperties = JobCreationProperties::create()
            ->withManifestPaths(['test1.yml', 'test2.yml'])
            ->withSources(new ArrayCollection([YamlFile::create('/test1.yml', 'file content')]))
        ;

        self::assertEquals(
            new JobCreationError('source/test/missing', ['path' => 'test2.yml']),
            $this->makeCreateJobCall($jobCreationProperties)
        );
    }

    public function testCreateJobJobAlreadyExists(): void
    {
        $jobCreationProperties = JobCreationProperties::create()
            ->withManifestPaths(['test1.yml'])
            ->withSources(new ArrayCollection([YamlFile::create('/test1.yml', 'file content')]))
        ;

        $this->makeCreateJobCall($jobCreationProperties);
        self::assertEquals(
            new JobCreationError('job/already_exists', []),
            $this->makeCreateJobCall($jobCreationProperties),
        );
    }

    public function testCreateJobSuccess(): void
    {
        $jobCreationProperties = JobCreationProperties::create()
            ->withManifestPaths(['test1.yml'])
            ->withSources(new ArrayCollection([YamlFile::create('/test1.yml', 'file content')]))
        ;

        self::assertNull(
            $this->makeCreateJobCall($jobCreationProperties)
        );
    }

    private function makeCreateJobCall(JobCreationProperties $jobCreationProperties): ?JobCreationError
    {
        return self::$client->createJob(
            $jobCreationProperties->label,
            $jobCreationProperties->eventDeliveryUrl,
            $jobCreationProperties->maximumDurationInSeconds,
            $jobCreationProperties->manifestPaths,
            $jobCreationProperties->sources,
        );
    }
}
