<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class Test
{
    /**
     * @param non-empty-string   $browser
     * @param non-empty-string   $url
     * @param non-empty-string   $source
     * @param non-empty-string   $target
     * @param non-empty-string[] $stepNames
     * @param non-empty-string   $state
     * @param positive-int       $position
     */
    public function __construct(
        public readonly string $browser,
        public readonly string $url,
        public readonly string $source,
        public readonly string $target,
        public readonly array $stepNames,
        public readonly string $state,
        public readonly int $position,
    ) {
    }
}
