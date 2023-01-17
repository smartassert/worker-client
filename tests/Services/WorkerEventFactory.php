<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

class WorkerEventFactory
{
    public function __construct(
        private readonly DataRepository $dataRepository,
    ) {
    }

    public function createWorkerEventReference(string $label, string $reference): int
    {
        $statement = $this->dataRepository->getConnection()->prepare(
            'INSERT INTO worker_event_reference(label, reference) VALUES(:label, :reference)'
        );

        $statement->execute(['label' => $label, 'reference' => $reference]);

        return (int) $this->dataRepository->getConnection()->lastInsertId();
    }

    public function createWorkerEventReferenceRelation(int $workerEventId, int $referenceId): int
    {
        $statement = $this->dataRepository->getConnection()->prepare('
            INSERT INTO worker_event_worker_event_reference (
                worker_event_id, worker_event_reference_id
            ) VALUES (
                :workerEventId, :referenceId
            )
        ');

        $statement->execute(['workerEventId' => $workerEventId, 'referenceId' => $referenceId]);

        return (int) $this->dataRepository->getConnection()->lastInsertId();
    }

    /**
     * @param array<mixed> $payload
     * @param array<int>   $relatedReferenceIds
     */
    public function createWorkerEvent(
        int $referenceId,
        string $scope,
        string $outcome,
        array $payload,
        string $state,
        array $relatedReferenceIds,
    ): int {
        $statement = $this->dataRepository->getConnection()->prepare('
            INSERT INTO worker_event (
                reference_id, scope, outcome, payload, state
            ) VALUES (
                :referenceId, :scope, :outcome, :payload, :state
            )
        ');

        $statement->execute([
            'referenceId' => $referenceId,
            'scope' => $scope,
            'outcome' => $outcome,
            'payload' => json_encode($payload),
            'state' => $state,
        ]);

        $workerEventId = (int) $this->dataRepository->getConnection()->lastInsertId();

        foreach ($relatedReferenceIds as $relatedReferenceId) {
            $this->createWorkerEventReferenceRelation($workerEventId, $relatedReferenceId);
        }

        return $workerEventId;
    }
}
