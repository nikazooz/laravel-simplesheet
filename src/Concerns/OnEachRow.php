<?php

namespace Nikazooz\Simplesheet\Concerns;

interface OnEachRow
{
    /**
     * @param  array  $row
     */
    public function onRow(array $row);
}
