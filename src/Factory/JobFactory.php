<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\ArrayInspector\ArrayInspector;
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
        $dataInspector = new ArrayInspector($data);

        $reference = $this->resourceReferenceFactory->create($data);
        $maximumDurationInSeconds = $dataInspector->getPositiveInteger('maximum_duration_in_seconds');

        if (null === $reference || null === $maximumDurationInSeconds) {
            return null;
        }

        return new Job(
            $reference,
            $maximumDurationInSeconds,
            $dataInspector->getNonEmptyStringArray('test_paths'),
            $dataInspector->getNonEmptyStringArray('sources'),
            $this->createTests($dataInspector->getArray('tests')),
            $this->createRelatedReferences($dataInspector->getArray('references')),
            $dataInspector->getPositiveIntegerArray('event_ids'),
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
                $testDataInspector = new ArrayInspector($testData);
                $test = $this->testFactory->create($testDataInspector);

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
