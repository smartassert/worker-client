<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\WorkerClient\Model\ResourceReference;

class ResourceReferenceFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ResourceReference
    {
        $data = new ArrayInspector($data);

        $label = $data->getNonEmptyString('label');
        $reference = $data->getNonEmptyString('reference');

        return null === $label || null === $reference ? null : new ResourceReference($label, $reference);
    }
}
