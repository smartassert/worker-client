<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationException;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class CreateJobTest extends AbstractIntegrationTest
{
    public function testCreateJobSourceTestMissing(): void
    {
        $jobCreationProperties = new JobCreationProperties(
            manifestPaths: ['test1.yml', 'test2.yml'],
            sources: new ArrayCollection([YamlFile::create('/test1.yml', 'file content')])
        );

        try {
            $this->makeCreateJobCall($jobCreationProperties);
        } catch (JobCreationException $e) {
            self::assertEquals(
                new JobCreationException('source/test/missing', ['path' => 'test2.yml']),
                $e
            );
        }
    }

    public function testCreateJobJobAlreadyExists(): void
    {
        $jobCreationProperties = new JobCreationProperties(
            manifestPaths: ['test1.yml'],
            sources: new ArrayCollection([YamlFile::create('/test1.yml', 'file content')])
        );

        try {
            $this->makeCreateJobCall($jobCreationProperties);
            $this->makeCreateJobCall($jobCreationProperties);
        } catch (JobCreationException $e) {
            self::assertEquals(
                new JobCreationException('job/already_exists', []),
                $e
            );
        }
    }

    /**
     * @dataProvider createJobSuccessDataProvider
     */
    public function testCreateJobSuccess(JobCreationProperties $jobCreationProperties, Job $expected): void
    {
        self::assertEquals($expected, $this->makeCreateJobCall($jobCreationProperties));
    }

    /**
     * @return array<mixed>
     */
    public function createJobSuccessDataProvider(): array
    {
        $singleTestJobCreationProperties = new JobCreationProperties(
            manifestPaths: ['test1.yml'],
            sources: new ArrayCollection([YamlFile::create('test1.yml', 'file content')])
        );

        $multipleTestJobCreationProperties = new JobCreationProperties(
            manifestPaths: ['test1.yml', 'test2.yml', 'test3.yml'],
            sources: new ArrayCollection([
                YamlFile::create('test1.yml', 'test 1 content'),
                YamlFile::create('test2.yml', 'test 2 content'),
                YamlFile::create('test3.yml', 'test 3 content'),
                YamlFile::create('page.yml', 'page content'),
            ])
        );

        return [
            'single test, no additional sources' => [
                'jobCreationProperties' => $singleTestJobCreationProperties,
                'expected' => new Job(
                    new ResourceReference(
                        $singleTestJobCreationProperties->label,
                        md5($singleTestJobCreationProperties->label)
                    ),
                    $singleTestJobCreationProperties->eventDeliveryUrl,
                    $singleTestJobCreationProperties->maximumDurationInSeconds,
                    $singleTestJobCreationProperties->manifestPaths,
                    ['test1.yml'],
                    [],
                    [
                        new ResourceReference(
                            'test1.yml',
                            md5($singleTestJobCreationProperties->label . 'test1.yml')
                        ),
                    ],
                    [1, 2]
                )
            ],
            'multiple tests, has additional sources' => [
                'jobCreationProperties' => $multipleTestJobCreationProperties,
                'expected' => new Job(
                    new ResourceReference(
                        $multipleTestJobCreationProperties->label,
                        md5($multipleTestJobCreationProperties->label)
                    ),
                    $multipleTestJobCreationProperties->eventDeliveryUrl,
                    $multipleTestJobCreationProperties->maximumDurationInSeconds,
                    $multipleTestJobCreationProperties->manifestPaths,
                    ['test1.yml', 'test2.yml', 'test3.yml', 'page.yml'],
                    [],
                    [
                        new ResourceReference(
                            'test1.yml',
                            md5($multipleTestJobCreationProperties->label . 'test1.yml')
                        ),
                        new ResourceReference(
                            'test2.yml',
                            md5($multipleTestJobCreationProperties->label . 'test2.yml')
                        ),
                        new ResourceReference(
                            'test3.yml',
                            md5($multipleTestJobCreationProperties->label . 'test3.yml')
                        ),
                    ],
                    [1, 2]
                )
            ],
        ];
    }
}
