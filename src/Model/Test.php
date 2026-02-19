<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

readonly class Test
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
        public string $browser,
        public string $url,
        public string $source,
        public string $target,
        public array $stepNames,
        public string $state,
        public int $position,
    ) {}
}
