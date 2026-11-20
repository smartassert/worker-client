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
}
