<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\Model\ResourceReference;

class ResourceReferenceFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ResourceReference
    {
        $label = $data['label'] ?? null;
        $label = is_string($label) && '' !== $label ? $label : null;

        $reference = $data['reference'] ?? null;
        $reference = is_string($reference) && '' !== $reference ? $reference : null;

        return null === $label || null === $reference ? null : new ResourceReference($label, $reference);
    }
}
