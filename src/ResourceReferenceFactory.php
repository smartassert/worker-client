<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\WorkerClient\Model\ResourceReference;

class ResourceReferenceFactory
{
    public function create(ArrayInspector $data): ?ResourceReference
    {
        $label = $data->getNonEmptyString('label');
        $reference = $data->getNonEmptyString('reference');

        return null === $label || null === $reference ? null : new ResourceReference($label, $reference);
    }
}
