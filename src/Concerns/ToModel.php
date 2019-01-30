<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Database\Eloquent\Model;

interface ToModel
{
    /**
     * @param  array  $row
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|null
     */
    public function model(array $row);
}
