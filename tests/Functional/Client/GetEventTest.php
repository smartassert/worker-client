<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Functional\Client;

use SmartAssert\WorkerClient\Model\Event;

class GetEventTest extends AbstractClientTest
{
    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->getEvent(1);
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Event::class;
    }
}
