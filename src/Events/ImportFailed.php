<?php

namespace Nikazooz\Simplesheet\Events;

use Throwable;

class ImportFailed
{
    /**
     * @var \Throwable
     */
    public $e;

    /**
     * @param  \Throwable  $e
     * @return void
     */
    public function __construct(Throwable $e)
    {
        $this->e = $e;
    }

    /**
     * @return \Throwable
     */
    public function getException(): Throwable
    {
        return $this->e;
    }
}
