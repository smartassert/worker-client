<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\DataExtractor;

class DataExtractor
{
    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public static function getArray(array $data, string $key): array
    {
        $value = $data[$key] ?? [];

        return is_array($value) ? $value : [];
    }

    /**
     * @param array<mixed> $data
     */
    public static function getBool(array $data, string $key): ?bool
    {
        $value = $data[$key] ?? null;

        return is_bool($value) ? $value : null;
    }

    /**
     * @param array<mixed> $data
     *
     * @return ?non-empty-string
     */
    public static function getNonEmptyString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && '' !== $value ? $value : null;
    }
}
