<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Support\Collection;

interface ToCollection
{
    /**
     * @param  \Illuminate\Support\Collection  $collection
     */
    public function collection(Collection $collection);
}
