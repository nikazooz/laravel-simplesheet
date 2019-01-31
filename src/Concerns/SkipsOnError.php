<?php

namespace Nikazooz\Simplesheet\Concerns;

use Throwable;

interface SkipsOnError
{
    /**
     * @param  \Throwable  $e
     * @return void
     */
    public function onError(Throwable $e);
}
