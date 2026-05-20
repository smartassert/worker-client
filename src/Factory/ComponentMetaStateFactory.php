<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\Model\MetaState;

readonly class ComponentMetaStateFactory
{
    /**
     * @param array<mixed> $metaStateData
     */
    public function create(array $metaStateData): ?MetaState
    {
        $ended = $metaStateData['ended'] ?? null;
        $ended = is_bool($ended) ? $ended : null;
        if (null === $ended) {
            return null;
        }

        $succeeded = $metaStateData['succeeded'] ?? null;
        $succeeded = is_bool($succeeded) ? $succeeded : null;
        if (null === $succeeded) {
            return null;
        }

        $pending = $metaStateData['pending'] ?? null;
        $pending = is_bool($pending) ? $pending : null;
        if (null === $pending) {
            return null;
        }

        return new MetaState(
            ended: $ended,
            succeeded: $succeeded,
            pending: $pending
        );
    }
}
