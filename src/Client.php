<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseContentException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\Event;
use SmartAssert\WorkerClient\Model\JobCreationError;
use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\JobSourceSerializer;
use SmartAssert\YamlFile\Collection\ProviderInterface;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
        private readonly EventFactory $eventFactory,
        private readonly JobSourceSerializer $jobSourceSerializer,
        private readonly JobSourceFactory $jobSourceFactory,
    ) {
    }

    /**
     * @throws InvalidModelDataException
     * @throws NonSuccessResponseException
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function getApplicationState(): ApplicationState
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            new Request('GET', $this->createUrl('/application_state'))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
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
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     */
    public function getEvent(int $id): ?Event
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            new Request('GET', $this->createUrl('/event/' . $id))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        $event = $this->eventFactory->create(new ArrayInspector($response->getData()));
        if (null === $event) {
            throw InvalidModelDataException::fromJsonResponse(Event::class, $response);
        }

        return $this->eventFactory->create(new ArrayInspector($response->getData()));
    }

    /**
     * @param non-empty-string        $label
     * @param non-empty-string        $eventDeliveryUrl
     * @param positive-int            $maximumDurationInSeconds
     * @param array<non-empty-string> $manifestPaths
     *
     * @throws ClientExceptionInterface
     * @throws InvalidManifestException
     * @throws InvalidModelDataException
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function createJob(
        string $label,
        string $eventDeliveryUrl,
        int $maximumDurationInSeconds,
        array $manifestPaths,
        ProviderInterface $sources
    ): ?JobCreationError {
        $jobSource = $this->jobSourceFactory->createFromManifestPathsAndSources($manifestPaths, $sources);
        $source = $this->jobSourceSerializer->serialize($jobSource);

        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/job')))
                ->withPayload(new UrlEncodedPayload([
                    'label' => $label,
                    'event_delivery_url' => $eventDeliveryUrl,
                    'maximum_duration_in_seconds' => $maximumDurationInSeconds,
                    'source' => $source,
                ]))
        );

        if (200 !== $response->getStatusCode()) {
            $jobCreationError = $this->createJobCreationErrorModel(new ArrayInspector($response->getData()));
            if (null === $jobCreationError) {
                throw InvalidModelDataException::fromJsonResponse(JobCreationError::class, $response);
            }

            return $jobCreationError;
        }

        // todo: return job model in #16
        return null;
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

    private function createJobCreationErrorModel(ArrayInspector $data): ?JobCreationError
    {
        $errorState = $data->getNonEmptyString('error_state');
        $payload = $data->getArray('payload');

        return null === $errorState ? null : new JobCreationError($errorState, $payload);
    }
}
