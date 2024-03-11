<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

interface JobInterface
{
    public function getReference(): ResourceReference;

    /**
     * @return positive-int
     */
    public function getMaximumDurationInSeconds(): int;

    /**
     * @return non-empty-string[]
     */
    public function getTestPaths(): array;

    /**
     * @return non-empty-string[]
     */
    public function getSources(): array;

    /**
     * @return Test[]
     */
    public function getTests(): array;

    /**
     * @return ResourceReference[]
     */
    public function getRelatedReferences(): array;

    /**
     * @return positive-int[]
     */
    public function getEventIds(): array;
}
