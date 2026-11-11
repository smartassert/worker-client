<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Model\Test;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerClient\Tests\Services\JobFactory;
use SmartAssert\WorkerClient\Tests\Services\JobSourceSerializerFactory;
use SmartAssert\WorkerClient\Tests\Services\TestFactory;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class GetJobTest extends AbstractIntegrationTestCase
{
    private static JobFactory $jobFactory;
    private static TestFactory $testFactory;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$jobFactory = new JobFactory(
            self::$client,
            new JobSourceSerializerFactory(),
        );
        self::$testFactory = new TestFactory(self::$dataRepository);
    }

    public function testGetJobJobNotFound(): void
    {
        self::assertNull(self::$client->getJob());
    }

    /**
     * @param callable(): JobCreationProperties    $jobCreationPropertiesCreator
     * @param callable(JobCreationProperties): Job $expectedJobCreator
     * @param Test[]                               $tests
     */
    #[DataProvider('getJobSuccessDataProvider')]
    public function testGetJobSuccess(
        callable $jobCreationPropertiesCreator,
        array $tests,
        callable $expectedJobCreator,
    ): void {
        $jobCreationProperties = $jobCreationPropertiesCreator();

        self::$jobFactory->create($jobCreationProperties);

        foreach ($tests as $test) {
            self::$testFactory->createFromModel($test);
        }

        $expectedJob = $expectedJobCreator($jobCreationProperties);
        $job = self::$client->getJob();
        self::assertInstanceOf(Job::class, $job);

        self::assertEquals($expectedJob->reference, $job->reference);
        self::assertSame($expectedJob->maximumDurationInSeconds, $job->maximumDurationInSeconds);
        self::assertEquals($expectedJob->sources, $job->sources);
        self::assertEquals($expectedJob->tests, $job->tests);
        self::assertEquals($expectedJob->relatedReferences, $job->relatedReferences);
        self::assertSame($expectedJob->eventIds, array_intersect($expectedJob->eventIds, $job->eventIds));
    }

    /**
     * @return array<mixed>
     */
    public static function getJobSuccessDataProvider(): array
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
                'jobCreationPropertiesCreator' => function () {
                    return new JobCreationProperties(
                        ['test1.yml', 'test2.yml'],
                    )->withSources(
                        new ArrayCollection([
                            YamlFile::create('test1.yml', 'test 1 content'),
                            YamlFile::create('test2.yml', 'test 2 content'),
                        ]),
                    );
                },
                'tests' => [],
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) {
                    $label = $jobCreationProperties->getLabel();

                    return new Job(
                        new ResourceReference($label, md5($label)),
                        $jobCreationProperties->getMaximumDurationInSeconds(),
                        $jobCreationProperties->getManifestPaths(),
                        ['test1.yml', 'test2.yml'],
                        [],
                        [
                            new ResourceReference(
                                'test1.yml',
                                md5($label . 'test1.yml')
                            ),
                            new ResourceReference(
                                'test2.yml',
                                md5($label . 'test2.yml')
                            ),
                        ],
                        [1, 2]
                    );
                },
            ],
            'has tests' => [
                'jobCreationPropertiesCreator' => function () {
                    return new JobCreationProperties(
                        ['test1.yml', 'test2.yml', 'test3.yml'],
                    )->withSources(
                        new ArrayCollection([
                            YamlFile::create('test1.yml', 'test 1 content'),
                            YamlFile::create('test2.yml', 'test 2 content'),
                            YamlFile::create('test3.yml', 'test 3 content'),
                            YamlFile::create('page.yml', 'page content'),
                        ]),
                    );
                },
                'tests' => $tests,
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) use ($tests) {
                    $label = $jobCreationProperties->getLabel();

                    return new Job(
                        new ResourceReference($label, md5($label)),
                        $jobCreationProperties->getMaximumDurationInSeconds(),
                        $jobCreationProperties->getManifestPaths(),
                        ['test1.yml', 'test2.yml', 'test3.yml', 'page.yml'],
                        $tests,
                        [
                            new ResourceReference(
                                'test1.yml',
                                md5($label . 'test1.yml')
                            ),
                            new ResourceReference(
                                'test2.yml',
                                md5($label . 'test2.yml')
                            ),
                            new ResourceReference(
                                'test3.yml',
                                md5($label . 'test3.yml')
                            ),
                        ],
                        [1, 2]
                    );
                },
            ],
        ];
    }
}
