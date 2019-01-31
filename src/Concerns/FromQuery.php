<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Database\Query\Builder;

interface FromQuery
{
    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query();
}
