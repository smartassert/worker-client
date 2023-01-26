<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Model\Test;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\YamlFile;

class GetJobTest extends AbstractIntegrationTest
{
    public function testGetJobJobNotFound(): void
    {
        self::assertNull(self::$client->getJob());
    }

    /**
     * @dataProvider getJobSuccessDataProvider
     *
     * @param Test[] $tests
     */
    public function testGetJobSuccess(
        JobCreationProperties $jobCreationProperties,
        array $tests,
        Job $expected
    ): void {
        $this->makeCreateJobCall($jobCreationProperties);

        foreach ($tests as $test) {
            self::$testFactory->createFromModel($test);
        }

        self::assertEquals($expected, self::$client->getJob());
    }

    /**
     * @return array<mixed>
     */
    public function getJobSuccessDataProvider(): array
    {
        $hasTestsJobCreationProperties = new JobCreationProperties(
            manifestPaths: ['test1.yml', 'test2.yml', 'test3.yml'],
            sources: new ArrayCollection([
                YamlFile::create('test1.yml', 'test 1 content'),
                YamlFile::create('test2.yml', 'test 2 content'),
                YamlFile::create('test3.yml', 'test 3 content'),
                YamlFile::create('page.yml', 'page content'),
            ])
        );

        $noTestsJobCreationProperties = new JobCreationProperties(
            manifestPaths: ['test1.yml', 'test2.yml'],
            sources: new ArrayCollection([
                YamlFile::create('test1.yml', 'test 1 content'),
                YamlFile::create('test2.yml', 'test 2 content'),
            ])
        );

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
                'jobCreationProperties' => $noTestsJobCreationProperties,
                'tests' => [],
                'expected' => new Job(
                    $this->createResourceReference(
                        $noTestsJobCreationProperties->label,
                        [$noTestsJobCreationProperties->label]
                    ),
                    $noTestsJobCreationProperties->eventDeliveryUrl,
                    $noTestsJobCreationProperties->maximumDurationInSeconds,
                    $noTestsJobCreationProperties->manifestPaths,
                    ['test1.yml', 'test2.yml'],
                    [],
                    [
                        $this->createResourceReference(
                            'test1.yml',
                            [$noTestsJobCreationProperties->label, 'test1.yml']
                        ),
                        $this->createResourceReference(
                            'test2.yml',
                            [$noTestsJobCreationProperties->label, 'test2.yml']
                        ),
                    ],
                    [1, 2]
                ),
            ],
            'has tests' => [
                'jobCreationProperties' => $hasTestsJobCreationProperties,
                'tests' => $tests,
                'expected' => new Job(
                    $this->createResourceReference(
                        $hasTestsJobCreationProperties->label,
                        [$hasTestsJobCreationProperties->label]
                    ),
                    $hasTestsJobCreationProperties->eventDeliveryUrl,
                    $hasTestsJobCreationProperties->maximumDurationInSeconds,
                    $hasTestsJobCreationProperties->manifestPaths,
                    ['test1.yml', 'test2.yml', 'test3.yml', 'page.yml'],
                    $tests,
                    [
                        $this->createResourceReference(
                            'test1.yml',
                            [$hasTestsJobCreationProperties->label, 'test1.yml']
                        ),
                        $this->createResourceReference(
                            'test2.yml',
                            [$hasTestsJobCreationProperties->label, 'test2.yml']
                        ),
                        $this->createResourceReference(
                            'test3.yml',
                            [$hasTestsJobCreationProperties->label, 'test3.yml']
                        ),
                    ],
                    [1, 2]
                ),
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
