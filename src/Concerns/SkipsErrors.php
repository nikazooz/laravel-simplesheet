<?php

namespace Nikazooz\Simplesheet\Concerns;

use Throwable;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Validators\Failure;

trait SkipsErrors
{
    /**
     * @var \Nikazooz\Simplesheet\Validators\Failure[]
     */
    protected $errors = [];

    /**
     * @param  \Throwable  $e
     */
    public function onError(Throwable $e)
    {
        $this->errors[] = $e;
    }

    /**
     * @return \Throwable[]|\Illuminate\Support\Collection
     */
    public function errors(): Collection
    {
        return new Collection($this->errors);
    }
}
