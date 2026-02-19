<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

class DataRepository
{
    private static ?\PDO $connection = null;

    public function __construct(
        private readonly string $databaseDsn,
    ) {}

    public function removeAllData(): void
    {
        $tableNames = [
            'job',
            'test',
            'source',
            'worker_event',
            'worker_event_worker_event_reference',
            'worker_event_reference',
        ];

        $sequenceNames = [
            'worker_event_id_seq',
            'worker_event_reference_id_seq',
        ];

        foreach ($tableNames as $tableName) {
            $this->getConnection()->query('TRUNCATE TABLE ' . $tableName . ' CASCADE');
        }

        foreach ($sequenceNames as $sequenceName) {
            $this->getConnection()->query('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 1');
        }
    }

    /**
     * @return array<mixed>
     */
    public function read(string $query): array
    {
        $statement = self::getConnection()->query($query);

        return false === $statement
            ? []
            : $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getConnection(): \PDO
    {
        if (null === self::$connection) {
            self::$connection = new \PDO($this->databaseDsn);
        }

        return self::$connection;
    }
}
