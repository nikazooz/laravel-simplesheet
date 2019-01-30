<?php

namespace Nikazooz\Simplesheet\Concerns;

use Nikazooz\Simplesheet\Validators\Failure;

interface SkipsOnFailure
{
    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures);
}
