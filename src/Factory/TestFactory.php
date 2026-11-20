<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Factory;

use SmartAssert\WorkerClient\Model\Test;

class TestFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?Test
    {
        $browser = $data['browser'] ?? null;
        $browser = is_string($browser) && '' !== $browser ? $browser : null;

        $url = $data['url'] ?? null;
        $url = is_string($url) && '' !== $url ? $url : null;

        $source = $data['source'] ?? null;
        $source = is_string($source) && '' !== $source ? $source : null;

        $target = $data['target'] ?? null;
        $target = is_string($target) && '' !== $target ? $target : null;

        $stepNames = $this->getNonEmptyStringArray($data, 'step_names');

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

    /**
     * @param array<mixed> $data
     *
     * @return non-empty-string[]
     */
    private function getNonEmptyStringArray(array $data, string $key): array
    {
        $values = $data[$key] ?? [];
        $values = is_array($values) ? $values : [];

        $filteredValues = [];

        foreach ($values as $value) {
            if (is_string($value) && '' !== $value) {
                $filteredValues[] = $value;
            }
        }

        return $filteredValues;
    }
}
