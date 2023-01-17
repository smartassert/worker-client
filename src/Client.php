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
use SmartAssert\ServiceClient\Request;
use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\Event;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
        private readonly EventFactory $eventFactory,
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
}
