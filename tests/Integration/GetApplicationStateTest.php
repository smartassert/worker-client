<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\ApplicationState;

class GetApplicationStateTest extends AbstractIntegrationTest
{
    public function testGetSuccess(): void
    {
        $applicationState = self::$client->getApplicationState();
        self::assertInstanceOf(ApplicationState::class, $applicationState);

        self::assertSame('awaiting-job', $applicationState->applicationState);
        self::assertSame('awaiting', $applicationState->compilationState);
        self::assertSame('awaiting', $applicationState->executionState);
        self::assertSame('awaiting', $applicationState->eventDeliveryState);
    }
}
