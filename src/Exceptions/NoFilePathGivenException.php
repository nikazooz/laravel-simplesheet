<?php

namespace Nikazooz\Simplesheet\Exceptions;

use Throwable;
use InvalidArgumentException;

class NoFilePathGivenException extends InvalidArgumentException implements Throwable
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

    /**
     * @return \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    public static function import()
    {
        return new static('A filepath needs to be passed in order to perform the import.');
    }
}
