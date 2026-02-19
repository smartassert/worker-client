<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

readonly class Job
{
    /**
     * @param positive-int        $maximumDurationInSeconds
     * @param non-empty-string[]  $testPaths
     * @param non-empty-string[]  $sources
     * @param Test[]              $tests
     * @param ResourceReference[] $relatedReferences
     * @param positive-int[]      $eventIds
     */
    public function __construct(
        public ResourceReference $reference,
        public int $maximumDurationInSeconds,
        public array $testPaths,
        public array $sources,
        public array $tests,
        public array $relatedReferences,
        public array $eventIds,
    ) {}
}
