<?php

namespace Nikazooz\Simplesheet\Concerns;

interface FromQuery
{
    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query();
}
