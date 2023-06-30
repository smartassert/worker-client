<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\ApplicationState;
use SmartAssert\WorkerClient\Model\ComponentState;

class GetApplicationStateTest extends AbstractIntegrationTestCase
{
    public function testGetSuccess(): void
    {
        $applicationState = self::$client->getApplicationState();
        self::assertInstanceOf(ApplicationState::class, $applicationState);

        self::assertEquals(
            new ComponentState('awaiting-job', false),
            $applicationState->applicationState
        );

        self::assertEquals(
            new ComponentState('awaiting', false),
            $applicationState->compilationState
        );

        self::assertEquals(
            new ComponentState('awaiting', false),
            $applicationState->executionState
        );

        self::assertEquals(
            new ComponentState('awaiting', false),
            $applicationState->eventDeliveryState
        );
    }
}
