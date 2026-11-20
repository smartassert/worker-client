<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\DataExtractor\DataExtractor;
use SmartAssert\WorkerClient\Model\Test;

class TestFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?Test
    {
        $browser = DataExtractor::getNonEmptyString($data, 'browser');
        $url = DataExtractor::getNonEmptyString($data, 'url');
        $source = DataExtractor::getNonEmptyString($data, 'source');
        $target = DataExtractor::getNonEmptyString($data, 'target');
        $stepNames = DataExtractor::getNonEmptyStringArray($data, 'step_names');

        $state = $data['state'] ?? null;
        $state = is_string($state) && '' !== $state ? $state : null;

        $position = $data['position'] ?? null;
        $position = is_int($position) && $position > 0 ? $position : null;

        if (
            null === $browser
            || null === $url
            || null === $source
            || null === $target
            || null === $state
            || null === $position
        ) {
            return null;
        }

        return new Test($browser, $url, $source, $target, $stepNames, $state, $position);
    }
}
