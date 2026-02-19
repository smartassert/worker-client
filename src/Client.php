<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\ComponentState;
use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationException;
use SmartAssert\WorkerClient\Model\MetaState;

readonly class Client
{
    public function __construct(
        private string $baseUrl,
        private ServiceClient $serviceClient,
        private EventFactory $eventFactory,
        private JobFactory $jobFactory,
    ) {}

    /**
     * @throws InvalidModelDataException
     * @throws NonSuccessResponseException
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function getApplicationState(): ApplicationState
    {
        $response = $this->serviceClient->sendRequestForJson(
            new Request('GET', $this->createUrl('/application_state'))
        );

        $responseDataInspector = new ArrayInspector($response->getData());

        $applicationState = $this->createApplicationStateModel($responseDataInspector);
        if (null === $applicationState) {
            throw InvalidModelDataException::fromJsonResponse(ApplicationState::class, $response);
        }

        return $applicationState;
    }

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
    public function getEvent(int $id): ?Event
    {
        $response = $this->serviceClient->sendRequestForJson(
            new Request('GET', $this->createUrl('/event/' . $id))
        );

        $event = $this->eventFactory->create(new ArrayInspector($response->getData()));
        if (null === $event) {
            throw InvalidModelDataException::fromJsonResponse(Event::class, $response);
        }

        return $event;
    }

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
    ): Job {
        try {
            $response = $this->serviceClient->sendRequestForJson(
                (new Request('POST', $this->createUrl('/job')))
                    ->withPayload(new UrlEncodedPayload([
                        'label' => $label,
                        'results_token' => $resultsToken,
                        'maximum_duration_in_seconds' => $maximumDurationInSeconds,
                        'source' => $serializedJobSource,
                    ]))
            );
        } catch (NonSuccessResponseException $e) {
            $response = $e->getResponse();

            if (400 === $e->getStatusCode()) {
                if (!$response instanceof JsonResponse) {
                    throw InvalidResponseTypeException::create($response, JsonResponse::class);
                }

                $jobCreationError = $this->createJobCreationErrorModel(new ArrayInspector($response->getData()));
                if (null === $jobCreationError) {
                    throw InvalidModelDataException::fromJsonResponse(JobCreationException::class, $response);
                }

                throw $jobCreationError;
            }

            throw $e;
        }

        $job = $this->jobFactory->create(new ArrayInspector($response->getData()));
        if (null === $job) {
            throw InvalidModelDataException::fromJsonResponse(Job::class, $response);
        }

        return $job;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function getJob(): ?Job
    {
        try {
            $response = $this->serviceClient->sendRequestForJson(
                new Request('GET', $this->createUrl('/job'))
            );
        } catch (NonSuccessResponseException $e) {
            if (400 === $e->getStatusCode()) {
                return null;
            }

            throw $e;
        }

        $job = $this->jobFactory->create(new ArrayInspector($response->getData()));
        if (null === $job) {
            throw InvalidModelDataException::fromJsonResponse(Job::class, $response);
        }

        return $job;
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private function createUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . $path;
    }

    private function createApplicationStateModel(ArrayInspector $data): ?ApplicationState
    {
        $applicationState = $this->createComponentState($data->getArray('application'));
        $compilationState = $this->createComponentState($data->getArray('compilation'));
        $executionState = $this->createComponentState($data->getArray('execution'));
        $eventDeliveryState = $this->createComponentState($data->getArray('event_delivery'));

        if (
            null === $applicationState
            || null === $compilationState
            || null === $executionState
            || null === $eventDeliveryState
        ) {
            return null;
        }

        return new ApplicationState($applicationState, $compilationState, $executionState, $eventDeliveryState);
    }

    private function createJobCreationErrorModel(ArrayInspector $data): ?JobCreationException
    {
        $errorState = $data->getNonEmptyString('error_state');
        $payload = $data->getArray('payload');

        return null === $errorState ? null : new JobCreationException($errorState, $payload);
    }

    /**
     * @param array<mixed> $data
     */
    private function createComponentState(array $data): ?ComponentState
    {
        $state = $data['state'] ?? null;
        $state = is_string($state) ? $state : null;
        $state = '' !== $state ? $state : null;

        if (null === $state) {
            return null;
        }

        $metaStateData = $data['meta_state'] ?? null;
        $metaStateData = is_array($metaStateData) ? $metaStateData : null;

        if (null === $metaStateData) {
            return null;
        }

        $metaStateEnded = $metaStateData['ended'] ?? null;
        $metaStateEnded = is_bool($metaStateEnded) ? $metaStateEnded : null;

        if (null === $metaStateEnded) {
            return null;
        }

        $metaStateSucceeded = $metaStateData['succeeded'] ?? null;
        $metaStateSucceeded = is_bool($metaStateSucceeded) ? $metaStateSucceeded : null;

        if (null === $metaStateSucceeded) {
            return null;
        }

        return new ComponentState($state, new MetaState($metaStateEnded, $metaStateSucceeded));
    }
}
