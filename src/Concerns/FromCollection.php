<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Support\Collection;

interface FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection();
}
