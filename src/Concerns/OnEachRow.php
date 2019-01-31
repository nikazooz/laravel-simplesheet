<?php

namespace Nikazooz\Simplesheet\Concerns;

interface OnEachRow
{
    /**
     * @param  array  $row
     * @return void
     */
    public function onRow(array $row);
}
