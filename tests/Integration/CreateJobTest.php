<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationException;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerClient\Tests\Services\JobFactory;
use SmartAssert\WorkerClient\Tests\Services\JobSourceSerializerFactory;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class CreateJobTest extends AbstractIntegrationTestCase
{
    private static JobFactory $jobFactory;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$jobFactory = new JobFactory(
            self::$client,
            new JobSourceSerializerFactory(),
        );
    }

    public function testCreateJobSourceTestMissing(): void
    {
        $jobCreationProperties = new JobCreationProperties(
            ['test1.yml', 'test2.yml'],
        )->withSources(
            new ArrayCollection([YamlFile::create('/test1.yml', 'file content')])
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
            ['test1.yml'],
        )->withSources(
            new ArrayCollection([YamlFile::create('/test1.yml', 'file content')])
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
     * @param callable(): JobCreationProperties    $jobCreationPropertiesCreator
     * @param callable(JobCreationProperties): Job $expectedJobCreator
     */
    #[DataProvider('createJobSuccessDataProvider')]
    public function testCreateJobSuccess(
        callable $jobCreationPropertiesCreator,
        callable $expectedJobCreator,
    ): void {
        $jobCreationProperties = $jobCreationPropertiesCreator();

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
                'jobCreationPropertiesCreator' => function () {
                    return new JobCreationProperties(
                        ['test1.yml'],
                    )->withMaximumDurationInSeconds(
                        60
                    )
                        ->withSources(
                            new ArrayCollection([YamlFile::create('test1.yml', 'file content')]),
                        )
                    ;
                },
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) {
                    $label = $jobCreationProperties->getLabel();

                    return new Job(
                        new ResourceReference($label, md5($label)),
                        $jobCreationProperties->getMaximumDurationInSeconds(),
                        $jobCreationProperties->getManifestPaths(),
                        ['test1.yml'],
                        [],
                        [
                            new ResourceReference(
                                'test1.yml',
                                md5($label . 'test1.yml')
                            ),
                        ],
                        [1, 2]
                    );
                },
            ],
            'multiple tests, has additional sources' => [
                'jobCreationPropertiesCreator' => function () {
                    return new JobCreationProperties(
                        ['test1.yml', 'test2.yml', 'test3.yml'],
                    )->withMaximumDurationInSeconds(
                        60
                    )
                        ->withSources(
                            new ArrayCollection([
                                YamlFile::create('test1.yml', 'test 1 content'),
                                YamlFile::create('test2.yml', 'test 2 content'),
                                YamlFile::create('test3.yml', 'test 3 content'),
                                YamlFile::create('page.yml', 'page content'),
                            ]),
                        )
                    ;
                },
                'expectedJobCreator' => function (JobCreationProperties $jobCreationProperties) {
                    $label = $jobCreationProperties->getLabel();

                    return new Job(
                        new ResourceReference($label, md5($label)),
                        $jobCreationProperties->getMaximumDurationInSeconds(),
                        $jobCreationProperties->getManifestPaths(),
                        ['test1.yml', 'test2.yml', 'test3.yml', 'page.yml'],
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
