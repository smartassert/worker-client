<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\JobCreationException;
use SmartAssert\WorkerClient\Model\JobInterface;

interface ClientInterface
{
    /**
     * @throws InvalidModelDataException
     * @throws NonSuccessResponseException
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function getApplicationState(): ApplicationState;

    /**
     * @param positive-int $id
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function getEvent(int $id): ?Event;

    /**
     * @param non-empty-string $label
     * @param positive-int     $maximumDurationInSeconds
     *
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws JobCreationException
     * @throws UnauthorizedException
     */
    public function createJob(
        string $label,
        string $resultsToken,
        int $maximumDurationInSeconds,
        string $serializedJobSource
    ): JobInterface;

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function getJob(): ?JobInterface;
}
