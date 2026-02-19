<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

class WorkerEventFactory
{
    public function __construct(
        private readonly DataRepository $dataRepository,
    ) {}

    public function createWorkerEventReference(string $label, string $reference): string
    {
        $id = md5($label . ':' . $reference);

        $statement = $this->dataRepository->getConnection()->prepare(
            'INSERT INTO worker_event_reference(label, reference, id) VALUES(:label, :reference, :id)'
        );

        $statement->execute([
            'label' => $label,
            'reference' => $reference,
            'id' => $id,
        ]);

        return $id;
    }

    public function createWorkerEventReferenceRelation(int $workerEventId, string $referenceId): int
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
     * @param string[]     $relatedReferenceIds
     *
     * @return positive-int
     */
    public function createWorkerEvent(
        string $referenceId,
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
        if ($workerEventId < 1) {
            throw new \RuntimeException('Invalid worker event id: ' . $workerEventId);
        }

        foreach ($relatedReferenceIds as $relatedReferenceId) {
            $this->createWorkerEventReferenceRelation($workerEventId, $relatedReferenceId);
        }

        return $workerEventId;
    }
}
