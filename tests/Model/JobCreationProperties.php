<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Model;

use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\ProviderInterface;
use SmartAssert\ResultsClient\Model\Job as ResultsJob;

class JobCreationProperties
{
    public readonly ResultsJob $resultsJob;

    /**
     * @var positive-int
     */
    public readonly int $maximumDurationInSeconds;

    /**
     * @var non-empty-string[]
     */
    public readonly array $manifestPaths;
    public readonly ProviderInterface $sources;

    /**
     * @param positive-int       $maximumDurationInSeconds
     * @param non-empty-string[] $manifestPaths
     */
    public function __construct(
        ResultsJob $resultsJob,
        ?int $maximumDurationInSeconds = null,
        array $manifestPaths = [],
        ?ProviderInterface $sources = null,
    ) {
        if (null === $maximumDurationInSeconds) {
            $maximumDurationInSeconds = rand(1, 600);
        }

        if (null === $sources) {
            $sources = new ArrayCollection([]);
        }

        $this->resultsJob = $resultsJob;
        $this->maximumDurationInSeconds = $maximumDurationInSeconds;
        $this->manifestPaths = $manifestPaths;
        $this->sources = $sources;
    }
}
