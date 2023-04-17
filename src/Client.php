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
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\Job;
use SmartAssert\WorkerClient\Model\JobCreationException;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
        private readonly EventFactory $eventFactory,
        private readonly JobFactory $jobFactory,
    ) {
    }

    /**
     * @throws InvalidModelDataException
     * @throws NonSuccessResponseException
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     */
    public function getApplicationState(): ApplicationState
    {
        $response = $this->serviceClient->sendRequest(
            new Request('GET', $this->createUrl('/application_state'))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

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
     */
    public function getEvent(int $id): ?Event
    {
        $response = $this->serviceClient->sendRequest(
            new Request('GET', $this->createUrl('/event/' . $id))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

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
     */
    public function createJob(
        string $label,
        string $resultsToken,
        int $maximumDurationInSeconds,
        string $serializedJobSource
    ): Job {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/job')))
                ->withPayload(new UrlEncodedPayload([
                    'label' => $label,
                    'results_token' => $resultsToken,
                    'maximum_duration_in_seconds' => $maximumDurationInSeconds,
                    'source' => $serializedJobSource,
                ]))
        );

        if (400 === $response->getStatusCode()) {
            if (!$response instanceof JsonResponse) {
                throw InvalidResponseTypeException::create($response, JsonResponse::class);
            }

            $jobCreationError = $this->createJobCreationErrorModel(new ArrayInspector($response->getData()));
            if (null === $jobCreationError) {
                throw InvalidModelDataException::fromJsonResponse(JobCreationException::class, $response);
            }

            throw $jobCreationError;
        }

        if (200 !== $response->getStatusCode()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
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
     */
    public function getJob(): ?Job
    {
        $response = $this->serviceClient->sendRequest(
            new Request('GET', $this->createUrl('/job'))
        );

        if (200 !== $response->getStatusCode()) {
            if (400 === $response->getStatusCode()) {
                return null;
            }

            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
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
        $application = $data->getNonEmptyString('application');
        $compilation = $data->getNonEmptyString('compilation');
        $execution = $data->getNonEmptyString('execution');
        $eventDelivery = $data->getNonEmptyString('event_delivery');

        return null === $application || null === $compilation || null === $execution || null === $eventDelivery
            ? null
            : new ApplicationState($application, $compilation, $execution, $eventDelivery);
    }

    private function createJobCreationErrorModel(ArrayInspector $data): ?JobCreationException
    {
        $errorState = $data->getNonEmptyString('error_state');
        $payload = $data->getArray('payload');

        return null === $errorState ? null : new JobCreationException($errorState, $payload);
    }
}
