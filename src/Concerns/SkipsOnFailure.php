<?php

namespace Nikazooz\Simplesheet\Concerns;

use Nikazooz\Simplesheet\Validators\Failure;

interface SkipsOnFailure
{
    /**
     * @param  \Nikazooz\Simplesheet\Validators\Failure[]  $failures
     * @return void
     */
    public function onFailure(Failure ...$failures);
}
