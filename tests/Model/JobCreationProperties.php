<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Model;

use SmartAssert\ResultsClient\Model\Job as ResultsJob;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\ProviderInterface;

class JobCreationProperties
{
    private ResultsJob $resultsJob;

    /**
     * @var positive-int
     */
    private int $maximumDurationInSeconds;

    /**
     * @var non-empty-string[]
     */
    private array $manifestPaths;
    private ProviderInterface $sources;

    /**
     * @param non-empty-string[] $manifestPaths
     */
    public function __construct(ResultsJob $resultsJob, array $manifestPaths)
    {
        $this->resultsJob = $resultsJob;
        $this->maximumDurationInSeconds = rand(1, 600);
        $this->manifestPaths = $manifestPaths;
        $this->sources = new ArrayCollection([]);
    }

    public function withSources(ProviderInterface $sources): self
    {
        $clone = clone $this;
        $clone->sources = $sources;

        return $clone;
    }

    /**
     * @param positive-int $maximumDurationInSeconds
     */
    public function withMaximumDurationInSeconds(int $maximumDurationInSeconds): self
    {
        $clone = clone $this;
        $clone->maximumDurationInSeconds = $maximumDurationInSeconds;

        return $clone;
    }

    public function getResultsJob(): ResultsJob
    {
        return $this->resultsJob;
    }

    /**
     * @return positive-int
     */
    public function getMaximumDurationInSeconds(): int
    {
        return $this->maximumDurationInSeconds;
    }

    public function getSources(): ProviderInterface
    {
        return $this->sources;
    }

    /**
     * @return non-empty-string[]
     */
    public function getManifestPaths(): array
    {
        return $this->manifestPaths;
    }
}
