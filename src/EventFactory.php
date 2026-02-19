<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\ResourceReference;

readonly class EventFactory
{
    public function __construct(
        private ResourceReferenceFactory $resourceReferenceFactory,
    ) {}

    public function create(ArrayInspector $data): ?Event
    {
        $sequenceNumber = $data->getPositiveInteger('sequence_number');
        $type = $data->getNonEmptyString('type');
        $resourceReference = $this->resourceReferenceFactory->create($data);
        $relatedReferencesData = $data->getArray('related_references');
        $body = $data->getArray('body');

        $references = [];
        foreach ($relatedReferencesData as $relatedReferenceData) {
            if (is_array($relatedReferenceData)) {
                $reference = $this->resourceReferenceFactory->create(new ArrayInspector($relatedReferenceData));

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
