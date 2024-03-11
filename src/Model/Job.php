<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

readonly class Job implements JobInterface
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
    ) {
    }

    public function getReference(): ResourceReference
    {
        return $this->reference;
    }

    public function getMaximumDurationInSeconds(): int
    {
        return $this->maximumDurationInSeconds;
    }

    public function getTestPaths(): array
    {
        return $this->testPaths;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function getTests(): array
    {
        return $this->tests;
    }

    public function getRelatedReferences(): array
    {
        return $this->relatedReferences;
    }

    public function getEventIds(): array
    {
        return $this->eventIds;
    }
}
