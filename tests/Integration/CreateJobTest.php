<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\Job as ResultsJob;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationException;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerClient\Tests\Services\ApiTokenFactory;
use SmartAssert\WorkerClient\Tests\Services\JobFactory;
use SmartAssert\WorkerClient\Tests\Services\JobLabelFactory;
use SmartAssert\WorkerClient\Tests\Services\ResultsClientFactory;
use SmartAssert\WorkerClient\Tests\Services\ServiceClientFactory;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class CreateJobTest extends AbstractIntegrationTestCase
{
    private static JobFactory $jobFactory;
    private static ResultsJob $resultsJob;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$jobFactory = new JobFactory(self::$client);

        $serviceClient = (new ServiceClientFactory())->create();
        $resultsClient = (new ResultsClientFactory($serviceClient))->create();
        $apiTokenFactory = new ApiTokenFactory();
        $jobLabelFactory = new JobLabelFactory();

        self::$resultsJob = $resultsClient->createJob($apiTokenFactory->create(), $jobLabelFactory->create());
    }

    public function testCreateJobSourceTestMissing(): void
    {
        $jobCreationProperties = new JobCreationProperties(
            resultsJob: self::$resultsJob,
            manifestPaths: ['test1.yml', 'test2.yml'],
            sources: new ArrayCollection([YamlFile::create('/test1.yml', 'file content')])
        );

        try {
            self::$jobFactory->create($jobCreationProperties);
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
            resultsJob: self::$resultsJob,
            manifestPaths: ['test1.yml'],
            sources: new ArrayCollection([YamlFile::create('/test1.yml', 'file content')])
        );

        try {
            self::$jobFactory->create($jobCreationProperties);
            self::$jobFactory->create($jobCreationProperties);
        } catch (JobCreationException $e) {
            self::assertEquals(
                new JobCreationException('job/already_exists', []),
                $e
            );
        }
    }

    /**
     * @dataProvider createJobSuccessDataProvider
     *
     * @param callable(ResultsJob): JobCreationProperties $jobCreationPropertiesCreator
     * @param callable(JobCreationProperties): Job        $expectedJobCreator
     */
    public function testCreateJobSuccess(
        callable $jobCreationPropertiesCreator,
        callable $expectedJobCreator,
    ): void {
        $jobCreationProperties = $jobCreationPropertiesCreator(self::$resultsJob);

        $expectedJob = $expectedJobCreator($jobCreationProperties);

        self::assertEquals($expectedJob, self::$jobFactory->create($jobCreationProperties));
    }

    /**
     * @return array<mixed>
     */
    public static function createJobSuccessDataProvider(): array
    {
        return [
            'single test, no additional sources' => [
                'jobCreationPropertiesCreator' => function (ResultsJob $resultsJob) {
                    return new JobCreationProperties(
                        resultsJob: $resultsJob,
                        maximumDurationInSeconds: 60,
                        manifestPaths: ['test1.yml'],
                        sources: new ArrayCollection([YamlFile::create('test1.yml', 'file content')])
                    );
                },
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) {
                    $resultsJob = $jobCreationProperties->resultsJob;

                    return new Job(
                        new ResourceReference($resultsJob->label, md5($resultsJob->label)),
                        $jobCreationProperties->maximumDurationInSeconds,
                        $jobCreationProperties->manifestPaths,
                        ['test1.yml'],
                        [],
                        [
                            new ResourceReference(
                                'test1.yml',
                                md5($resultsJob->label . 'test1.yml')
                            ),
                        ],
                        [1, 2]
                    );
                },
            ],
            'multiple tests, has additional sources' => [
                'jobCreationPropertiesCreator' => function (ResultsJob $resultsJob) {
                    return new JobCreationProperties(
                        resultsJob: $resultsJob,
                        maximumDurationInSeconds: 60,
                        manifestPaths: ['test1.yml', 'test2.yml', 'test3.yml'],
                        sources: new ArrayCollection([
                            YamlFile::create('test1.yml', 'test 1 content'),
                            YamlFile::create('test2.yml', 'test 2 content'),
                            YamlFile::create('test3.yml', 'test 3 content'),
                            YamlFile::create('page.yml', 'page content'),
                        ])
                    );
                },
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) {
                    $resultsJob = $jobCreationProperties->resultsJob;

                    return new Job(
                        new ResourceReference($resultsJob->label, md5($resultsJob->label)),
                        $jobCreationProperties->maximumDurationInSeconds,
                        $jobCreationProperties->manifestPaths,
                        ['test1.yml', 'test2.yml', 'test3.yml', 'page.yml'],
                        [],
                        [
                            new ResourceReference(
                                'test1.yml',
                                md5($resultsJob->label . 'test1.yml')
                            ),
                            new ResourceReference(
                                'test2.yml',
                                md5($resultsJob->label . 'test2.yml')
                            ),
                            new ResourceReference(
                                'test3.yml',
                                md5($resultsJob->label . 'test3.yml')
                            ),
                        ],
                        [1, 2]
                    );
                },
            ],
        ];
    }
}
