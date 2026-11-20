<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

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

        $maximumDurationInSeconds = $data['maximum_duration_in_seconds'] ?? null;
        $maximumDurationInSeconds = is_int($maximumDurationInSeconds) && 0 < $maximumDurationInSeconds
            ? $maximumDurationInSeconds
            : null;

        if (null === $reference || null === $maximumDurationInSeconds) {
            return null;
        }

        $testsData = $data['tests'] ?? [];

        $referencesData = $data['references'] ?? [];
        $referencesData = is_array($referencesData) ? $referencesData : [];

        $testsData = is_array($testsData) ? $testsData : [];

        return new Job(
            $reference,
            $maximumDurationInSeconds,
            $this->getNonEmptyStringArray($data, 'test_paths'),
            $this->getNonEmptyStringArray($data, 'sources'),
            $this->createTests($testsData),
            $this->createRelatedReferences($referencesData),
            $this->getPositiveIntegerArray($data, 'event_ids'),
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

    /**
     * @param array<mixed> $data
     *
     * @return non-empty-string[]
     */
    private function getNonEmptyStringArray(array $data, string $key): array
    {
        $values = $data[$key] ?? [];
        $values = is_array($values) ? $values : [];

        $filteredValues = [];

        foreach ($values as $value) {
            if (is_string($value) && '' !== $value) {
                $filteredValues[] = $value;
            }
        }

        return $filteredValues;
    }

    /**
     * @param array<mixed> $data
     *
     * @return positive-int[]
     */
    private function getPositiveIntegerArray(array $data, string $key): array
    {
        $values = $data[$key] ?? [];
        $values = is_array($values) ? $values : [];

        $filteredValues = [];

        foreach ($values as $value) {
            if (is_int($value) && $value > 0) {
                $filteredValues[] = $value;
            }
        }

        return $filteredValues;
    }
}
