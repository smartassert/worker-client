<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\Job as ResultsJob;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Model\Test;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class GetJobTest extends AbstractIntegrationTest
{
    private static ResultsJob $resultsJob;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$resultsJob = self::getResultsClient()->createJob(self::getApiToken(), self::getJobLabel());
    }

    public function testGetJobJobNotFound(): void
    {
        self::assertNull(self::$client->getJob());
    }

    /**
     * @dataProvider getJobSuccessDataProvider
     *
     * @param callable(ResultsJob): JobCreationProperties $jobCreationPropertiesCreator
     * @param callable(JobCreationProperties): Job        $expectedJobCreator
     * @param Test[]                                      $tests
     */
    public function testGetJobSuccess(
        callable $jobCreationPropertiesCreator,
        array $tests,
        callable $expectedJobCreator,
    ): void {
        $jobCreationProperties = $jobCreationPropertiesCreator(self::$resultsJob);

        $this->makeCreateJobCall($jobCreationProperties);

        foreach ($tests as $test) {
            self::$testFactory->createFromModel($test);
        }

        self::assertEquals($expectedJobCreator($jobCreationProperties), self::$client->getJob());
    }

    /**
     * @return array<mixed>
     */
    public function getJobSuccessDataProvider(): array
    {
        $tests = [
            new Test(
                'chrome',
                'https://example.com/chrome',
                'test1.yml',
                'GeneratedTest1.php',
                ['test 1 step 1', 'test 1 step 2'],
                'complete',
                1
            ),
            new Test(
                'firefox',
                'https://example.com/firefox',
                'test2.yml',
                'GeneratedTest2.php',
                ['test 2 step 1', 'test 2 step 2'],
                'awaiting',
                2
            ),
        ];

        return [
            'no tests' => [
                'jobCreationPropertiesCreator' => function (ResultsJob $resultsJob) {
                    return new JobCreationProperties(
                        resultsJob: $resultsJob,
                        manifestPaths: ['test1.yml', 'test2.yml'],
                        sources: new ArrayCollection([
                            YamlFile::create('test1.yml', 'test 1 content'),
                            YamlFile::create('test2.yml', 'test 2 content'),
                        ])
                    );
                },
                'tests' => [],
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) {
                    $resultsJob = $jobCreationProperties->resultsJob;

                    return new Job(
                        new ResourceReference($resultsJob->label, md5($resultsJob->label)),
                        $jobCreationProperties->maximumDurationInSeconds,
                        $jobCreationProperties->manifestPaths,
                        ['test1.yml', 'test2.yml'],
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
                        ],
                        [1, 2]
                    );
                },
            ],
            'has tests' => [
                'jobCreationPropertiesCreator' => function (ResultsJob $resultsJob) {
                    return new JobCreationProperties(
                        resultsJob: $resultsJob,
                        manifestPaths: ['test1.yml', 'test2.yml', 'test3.yml'],
                        sources: new ArrayCollection([
                            YamlFile::create('test1.yml', 'test 1 content'),
                            YamlFile::create('test2.yml', 'test 2 content'),
                            YamlFile::create('test3.yml', 'test 3 content'),
                            YamlFile::create('page.yml', 'page content'),
                        ])
                    );
                },
                'tests' => $tests,
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) use ($tests) {
                    $resultsJob = $jobCreationProperties->resultsJob;

                    return new Job(
                        new ResourceReference($resultsJob->label, md5($resultsJob->label)),
                        $jobCreationProperties->maximumDurationInSeconds,
                        $jobCreationProperties->manifestPaths,
                        ['test1.yml', 'test2.yml', 'test3.yml', 'page.yml'],
                        $tests,
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

    /**
     * @param non-empty-string   $label
     * @param non-empty-string[] $components
     */
    private function createResourceReference(string $label, array $components): ResourceReference
    {
        return new ResourceReference($label, md5(implode('', $components)));
    }
}
