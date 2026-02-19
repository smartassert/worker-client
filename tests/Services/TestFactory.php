<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use SmartAssert\WorkerClient\Model\Test;

class TestFactory
{
    public function __construct(
        private readonly DataRepository $dataRepository,
    ) {}

    public function createFromModel(Test $test): int
    {
        return $this->create(
            $test->browser,
            $test->url,
            $test->source,
            $test->target,
            $test->stepNames,
            $test->position,
            $test->state
        );
    }

    /**
     * @param non-empty-string   $browser
     * @param non-empty-string   $url
     * @param non-empty-string   $source
     * @param non-empty-string   $target
     * @param non-empty-string[] $stepNames
     * @param positive-int       $position
     * @param non-empty-string   $state
     */
    public function create(
        string $browser,
        string $url,
        string $source,
        string $target,
        array $stepNames,
        int $position,
        string $state,
    ): int {
        $statement = $this->dataRepository->getConnection()->prepare('
            INSERT INTO test (
                browser, url, source, target, step_names, position, state
            ) VALUES (
                :browser, :url, :source, :target, :step_names, :position, :state
            )
        ');

        $statement->execute([
            'browser' => $browser,
            'url' => $url,
            'source' => $source,
            'target' => $target,
            'step_names' => implode(', ', $stepNames),
            'position' => $position,
            'state' => $state,
        ]);

        return (int) $this->dataRepository->getConnection()->lastInsertId();
    }
}
