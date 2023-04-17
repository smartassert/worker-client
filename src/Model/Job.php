<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class Job
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
        public readonly ResourceReference $reference,
        public readonly int $maximumDurationInSeconds,
        public readonly array $testPaths,
        public readonly array $sources,
        public readonly array $tests,
        public readonly array $relatedReferences,
        public readonly array $eventIds,
    ) {
    }
}
