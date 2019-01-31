<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Support\Collection;

interface ToCollection
{
    /**
     * @param  \Illuminate\Support\Collection  $collection
     * @return void
     */
    public function collection(Collection $collection);
}
