<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Integration;

use SmartAssert\WorkerClient\Model\ComponentState;
use SmartAssert\WorkerClient\Model\MetaState;

class GetApplicationStateTest extends AbstractIntegrationTestCase
{
    public function testGetSuccess(): void
    {
        $applicationState = self::$client->getApplicationState();

        self::assertEquals(
            new ComponentState('awaiting-job', new MetaState(false, false)),
            $applicationState->applicationState
        );

        self::assertEquals(
            new ComponentState('awaiting', new MetaState(false, false)),
            $applicationState->compilationState
        );

        self::assertEquals(
            new ComponentState('awaiting', new MetaState(false, false)),
            $applicationState->executionState
        );

        self::assertEquals(
            new ComponentState('awaiting', new MetaState(false, false)),
            $applicationState->eventDeliveryState
        );
    }
}
