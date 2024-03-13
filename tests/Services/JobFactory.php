<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\WorkerClient\Client;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationException;
use SmartAssert\WorkerClient\Tests\Model\JobCreationProperties;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\YamlFile\Exception\Collection\SerializeException;

class JobFactory
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * @throws JobCreationException
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     * @throws SerializeException
     */
    public function create(JobCreationProperties $jobCreationProperties): Job
    {
        $jobSource = new JobSource(
            new Manifest($jobCreationProperties->manifestPaths),
            $jobCreationProperties->sources
        );

        $jobSourceSerializer = (new JobSourceSerializerFactory())->create();

        $serializedSource = $jobSourceSerializer->serialize($jobSource);

        return $this->client->createJob(
            $jobCreationProperties->resultsJob->label,
            $jobCreationProperties->resultsJob->token,
            $jobCreationProperties->maximumDurationInSeconds,
            $serializedSource,
        );
    }
}
