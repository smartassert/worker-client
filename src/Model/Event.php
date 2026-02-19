<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

readonly class Event
{
    /**
     * @param positive-int             $sequenceNumber
     * @param non-empty-string         $type
     * @param array<mixed>             $body
     * @param null|ResourceReference[] $relatedReferences
     */
    public function __construct(
        public int $sequenceNumber,
        public string $type,
        public ResourceReference $resourceReference,
        public array $body,
        public ?array $relatedReferences = null,
    ) {}
}
