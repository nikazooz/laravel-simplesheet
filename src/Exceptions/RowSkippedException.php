<?php

namespace Nikazooz\Simplesheet\Exceptions;

use Exception;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Validators\Failure;

class RowSkippedException extends Exception
{
    /**
     * @var \Nikazooz\Simplesheet\Validators\Failure[]
     */
    private $failures;

    /**
     * @param \Nikazooz\Simplesheet\Validators\Failure ...$failures
     */
    public function __construct(Failure ...$failures)
    {
        $this->failures = $failures;

        parent::__construct();
    }

    /**
     * @return \Nikazooz\Simplesheet\Validators\Failure[]|\Illuminate\Support\Collection
     */
    public function failures(): Collection
    {
        return new Collection($this->failures);
    }

    /**
     * @return int[]
     */
    public function skippedRows(): array
    {
        return $this->failures()->map->row()->all();
    }
}
