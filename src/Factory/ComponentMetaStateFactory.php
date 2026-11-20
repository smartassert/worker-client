<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\DataExtractor\DataExtractor;
use SmartAssert\WorkerClient\Model\MetaState;

readonly class ComponentMetaStateFactory
{
    /**
     * @param array<mixed> $metaStateData
     */
    public function create(array $metaStateData): ?MetaState
    {
        $ended = DataExtractor::getBool($metaStateData, 'ended');
        $succeeded = DataExtractor::getBool($metaStateData, 'succeeded');
        $pending = DataExtractor::getBool($metaStateData, 'pending');

        if (null === $ended || null === $succeeded || null === $pending) {
            return null;
        }

        return new MetaState(
            ended: $ended,
            succeeded: $succeeded,
            pending: $pending
        );
    }
}
