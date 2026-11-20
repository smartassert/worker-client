<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\ArrayInspector\ArrayInspector;
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
        $dataInspector = new ArrayInspector($data);

        $sequenceNumber = $dataInspector->getPositiveInteger('sequence_number');
        $type = $dataInspector->getNonEmptyString('type');
        $resourceReference = $this->resourceReferenceFactory->create($data);
        $relatedReferencesData = $dataInspector->getArray('related_references');
        $body = $dataInspector->getArray('body');

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
