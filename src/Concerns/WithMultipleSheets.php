<?php

namespace Nikazooz\Simplesheet\Concerns;

interface WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array;
}
