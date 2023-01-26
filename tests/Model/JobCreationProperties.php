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
    public readonly string $label;

    /**
     * @var non-empty-string
     */
    public readonly string $eventDeliveryUrl;

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
     * @param non-empty-string   $label
     * @param non-empty-string   $eventDeliveryUrl
     * @param positive-int       $maximumDurationInSeconds
     * @param non-empty-string[] $manifestPaths
     */
    public function __construct(
        ?string $label = null,
        ?string $eventDeliveryUrl = null,
        ?int $maximumDurationInSeconds = null,
        array $manifestPaths = [],
        ?ProviderInterface $sources = null,
    ) {
        if (null === $label) {
            $label = md5((string) rand());
        }

        if (null === $eventDeliveryUrl) {
            $eventDeliveryUrl = 'https://example.com:' . rand(8000, 9000) . '/event_delivery_url';
        }

        if (null === $maximumDurationInSeconds) {
            $maximumDurationInSeconds = rand(1, 600);
        }

        if (null === $sources) {
            $sources = new ArrayCollection([]);
        }

        $this->label = $label;
        $this->eventDeliveryUrl = $eventDeliveryUrl;
        $this->maximumDurationInSeconds = $maximumDurationInSeconds;
        $this->manifestPaths = $manifestPaths;
        $this->sources = $sources;
    }
}
