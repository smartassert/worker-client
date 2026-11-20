<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

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
        $sequenceNumber = $data['sequence_number'] ?? null;
        $sequenceNumber = is_int($sequenceNumber) && $sequenceNumber > 0 ? $sequenceNumber : null;

        $type = $data['type'] ?? null;
        $type = is_string($type) && '' !== $type ? $type : null;

        $resourceReference = $this->resourceReferenceFactory->create($data);

        $relatedReferencesData = $data['related_references'] ?? [];
        $relatedReferencesData = is_array($relatedReferencesData) ? $relatedReferencesData : [];

        $body = $data['body'] ?? [];
        $body = is_array($body) ? $body : [];

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
