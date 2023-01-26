<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Model;

use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\ProviderInterface;

class JobCreationProperties
{
    /**
     * @param non-empty-string   $label
     * @param non-empty-string   $eventDeliveryUrl
     * @param positive-int       $maximumDurationInSeconds
     * @param non-empty-string[] $manifestPaths
     */
    public function __construct(
        public readonly string $label,
        public readonly string $eventDeliveryUrl,
        public readonly int $maximumDurationInSeconds,
        public readonly array $manifestPaths,
        public readonly ProviderInterface $sources,
    ) {
    }

    public static function create(): JobCreationProperties
    {
        return new JobCreationProperties(
            md5((string) rand()),
            'https://example.com:' . rand(8000, 9000) . '/event_delivery_url',
            rand(1, 600),
            [],
            new ArrayCollection([])
        );
    }

    /**
     * @param non-empty-string[] $manifestPaths
     */
    public function withManifestPaths(array $manifestPaths): JobCreationProperties
    {
        return new JobCreationProperties(
            $this->label,
            $this->eventDeliveryUrl,
            $this->maximumDurationInSeconds,
            $manifestPaths,
            $this->sources,
        );
    }

    public function withSources(ProviderInterface $sources): JobCreationProperties
    {
        return new JobCreationProperties(
            $this->label,
            $this->eventDeliveryUrl,
            $this->maximumDurationInSeconds,
            $this->manifestPaths,
            $sources,
        );
    }
}
