<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\ResourceReference;
use SmartAssert\WorkerClient\Model\Test;

class JobFactory
{
    public function __construct(
        private readonly ResourceReferenceFactory $resourceReferenceFactory,
        private readonly TestFactory $testFactory,
    ) {
    }

    public function create(ArrayInspector $data): ?Job
    {
        $reference = $this->resourceReferenceFactory->create($data);
        $eventDeliveryUrl = $data->getNonEmptyString('event_delivery_url');
        $maximumDurationInSeconds = $data->getPositiveInteger('maximum_duration_in_seconds');

        if (null === $reference || null === $eventDeliveryUrl || null === $maximumDurationInSeconds) {
            return null;
        }

        return new Job(
            $reference,
            $eventDeliveryUrl,
            $maximumDurationInSeconds,
            $data->getNonEmptyStringArray('test_paths'),
            $data->getNonEmptyStringArray('sources'),
            $this->createTests($data->getArray('tests')),
            $this->createRelatedReferences($data->getArray('references')),
            $data->getPositiveIntegerArray('event_ids'),
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
                $relatedReferenceDataInspector = new ArrayInspector($relatedReferenceData);

                $relatedReference = $this->resourceReferenceFactory->create($relatedReferenceDataInspector);
                if ($relatedReference instanceof ResourceReference) {
                    $relatedReferences[] = $relatedReference;
                }
            }
        }

        return $relatedReferences;
    }
}
