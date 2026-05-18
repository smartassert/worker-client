<?php

declare(strict_types=1);

namespace Integration;

use SmartAssert\WorkerClient\Tests\Integration\AbstractIntegrationTestCase;

class IsReadyTest extends AbstractIntegrationTestCase
{
    public function testIsReadySuccess(): void
    {
        self::assertTrue(self::$client->isReady());
    }
}
