<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Support\Collection;
use Throwable;

trait SkipsErrors
{
    /**
     * @var \Nikazooz\Simplesheet\Validators\Failure[]
     */
    protected $errors = [];

    /**
     * @param  \Throwable  $e
     * @return void
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
