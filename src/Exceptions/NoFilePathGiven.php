<?php

namespace Nikazooz\Simplesheet\Exceptions;

use Throwable;
use InvalidArgumentException;

class NoFilePathGiven extends InvalidArgumentException implements Throwable
{
     /**
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(
        $message = 'A filepath needs to be passed in order to store the export',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
