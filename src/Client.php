<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\ComponentState;
use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationException;
use SmartAssert\WorkerClient\Model\JobInterface;

readonly class Client implements ClientInterface
{
    public function __construct(
        private string $baseUrl,
        private ServiceClient $serviceClient,
        private EventFactory $eventFactory,
        private JobFactory $jobFactory,
    ) {
    }

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

    public function createJob(
        string $label,
        string $resultsToken,
        int $maximumDurationInSeconds,
        string $serializedJobSource
    ): JobInterface {
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

    public function getJob(): ?JobInterface
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

        $isEndState = $data['is_end_state'] ?? null;
        $isEndState = is_bool($isEndState) ? $isEndState : null;

        if (null === $isEndState) {
            return null;
        }

        return new ComponentState($state, $isEndState);
    }
}
