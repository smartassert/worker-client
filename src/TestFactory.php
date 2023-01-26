<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\WorkerClient\Model\Test;

class TestFactory
{
    public function create(ArrayInspector $data): ?Test
    {
        $browser = $data->getNonEmptyString('browser');
        $url = $data->getNonEmptyString('url');
        $source = $data->getNonEmptyString('source');
        $target = $data->getNonEmptyString('target');
        $stepNames = $data->getNonEmptyStringArray('step_names');
        $state = $data->getNonEmptyString('state');
        $position = $data->getPositiveInteger('position');

        if (
            null === $browser
            || null === $url
            || null === $source
            || null === $target
            || null === $state
            || null === $position
        ) {
            return null;
        }

        return new Test($browser, $url, $source, $target, $stepNames, $state, $position);
    }
}
