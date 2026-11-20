<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\DataExtractor\DataExtractor;
use SmartAssert\WorkerClient\Model\ComponentState;

readonly class ComponentStateFactory
{
    public function __construct(
        private ComponentMetaStateFactory $metaStateFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ComponentState
    {
        $state = DataExtractor::getNonEmptyString($data, 'state');
        $metaStateData = DataExtractor::getArray($data, 'meta_state');

        $metaState = $this->metaStateFactory->create($metaStateData);
        if (null === $state || null === $metaState) {
            return null;
        }

        return new ComponentState($state, $metaState);
    }
}
