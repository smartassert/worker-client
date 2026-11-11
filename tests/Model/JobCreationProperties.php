<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Model;

use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\ProviderInterface;

class JobCreationProperties
{
    /**
     * @var non-empty-string
     */
    private string $label;
    private string $eventNotifyUrl;
    private string $eventNotifyToken;

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
    public function __construct(array $manifestPaths)
    {
        $this->label = md5((string) rand());
        $this->eventNotifyUrl = 'https://example.com/' . md5((string) rand()) . '/event-notify';
        $this->eventNotifyToken = md5((string) rand());
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

    /**
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function getEventNotifyUrl(): string
    {
        return $this->eventNotifyUrl;
    }

    public function getEventNotifyToken(): string
    {
        return $this->eventNotifyToken;
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
