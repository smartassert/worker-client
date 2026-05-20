<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

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
        $state = $data['state'] ?? null;
        $state = is_string($state) ? $state : null;
        $state = '' !== $state ? $state : null;
        if (null === $state) {
            return null;
        }

        $metaStateData = $data['meta_state'] ?? null;
        $metaStateData = is_array($metaStateData) ? $metaStateData : null;
        if (null === $metaStateData) {
            return null;
        }

        $metaState = $this->metaStateFactory->create($metaStateData);
        if (null === $metaState) {
            return null;
        }

        return new ComponentState($state, $metaState);
    }
}
