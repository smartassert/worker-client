<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Model;

class Event
{
    /**
     * @param positive-int             $sequenceNumber
     * @param non-empty-string         $type
     * @param array<mixed>             $body
     * @param null|ResourceReference[] $relatedReferences
     */
    public function __construct(
        public readonly int $sequenceNumber,
        public readonly string $type,
        public readonly ResourceReference $resourceReference,
        public readonly array $body,
        public readonly ?array $relatedReferences = null,
    ) {
    }
}
