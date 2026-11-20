<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\DataExtractor\DataExtractor;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Model\Test;

readonly class JobFactory
{
    public function __construct(
        private ResourceReferenceFactory $resourceReferenceFactory,
        private TestFactory $testFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?Job
    {
        $reference = $this->resourceReferenceFactory->create($data);
        $maximumDurationInSeconds = DataExtractor::getPositiveInteger($data, 'maximum_duration_in_seconds');
        if (null === $reference || null === $maximumDurationInSeconds) {
            return null;
        }

        $testsData = DataExtractor::getArray($data, 'tests');
        $referencesData = DataExtractor::getArray($data, 'references');

        return new Job(
            $reference,
            $maximumDurationInSeconds,
            DataExtractor::getNonEmptyStringArray($data, 'test_paths'),
            DataExtractor::getNonEmptyStringArray($data, 'sources'),
            $this->createTests($testsData),
            $this->createRelatedReferences($referencesData),
            DataExtractor::getPositiveIntegerArray($data, 'event_ids'),
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return Test[]
     */
    private function createTests(array $data): array
    {
        $tests = [];
        foreach ($data as $testData) {
            if (is_array($testData)) {
                $test = $this->testFactory->create($testData);

                if ($test instanceof Test) {
                    $tests[] = $test;
                }
            }
        }

        return $tests;
    }

    /**
     * @param array<mixed> $data
     *
     * @return ResourceReference[]
     */
    private function createRelatedReferences(array $data): array
    {
        $relatedReferences = [];
        foreach ($data as $relatedReferenceData) {
            if (is_array($relatedReferenceData)) {
                $relatedReference = $this->resourceReferenceFactory->create($relatedReferenceData);
                if ($relatedReference instanceof ResourceReference) {
                    $relatedReferences[] = $relatedReference;
                }
            }
        }

        return $relatedReferences;
    }
}
