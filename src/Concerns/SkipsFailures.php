<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Validators\Failure;

trait SkipsFailures
{
    /**
     * @var \Nikazooz\Simplesheet\Validators\Failure[]
     */
    protected $failures = [];

    /**
     * @param  \Nikazooz\Simplesheet\Validators\Failure  ...$failures
     */
    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * @return \Nikazooz\Simplesheet\Validators\Failure[]|\Illuminate\Support\Collection
     */
    public function failures(): Collection
    {
        return new Collection($this->failures);
    }
}
