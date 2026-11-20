<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\DataExtractor\DataExtractor;
use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\ResourceReference;

readonly class EventFactory
{
    public function __construct(
        private ResourceReferenceFactory $resourceReferenceFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?Event
    {
        $sequenceNumber = DataExtractor::getPositiveInteger($data, 'sequence_number');
        $type = DataExtractor::getNonEmptyString($data, 'type');
        $resourceReference = $this->resourceReferenceFactory->create($data);
        $relatedReferencesData = DataExtractor::getArray($data, 'related_references');
        $body = DataExtractor::getArray($data, 'body');

        $references = [];
        foreach ($relatedReferencesData as $relatedReferenceData) {
            if (is_array($relatedReferenceData)) {
                $reference = $this->resourceReferenceFactory->create($relatedReferenceData);

                if ($reference instanceof ResourceReference) {
                    $references[] = $reference;
                }
            }
        }

        if (null === $sequenceNumber || null === $type || null === $resourceReference) {
            return null;
        }

        $references = count($references) > 0 ? $references : null;

        return new Event($sequenceNumber, $type, $resourceReference, $body, $references);
    }
}
